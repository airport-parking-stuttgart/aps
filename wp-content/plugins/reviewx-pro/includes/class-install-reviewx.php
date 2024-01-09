<?php 
/**
 * This class is responsible for making Free Plugin available for pro!
 */
class ReviewX_Installer {

    public function __construct() {
        $this->migrator();
    }

    public function migrator() {
        /**
		 * Check Free ReviewX is There or not!
		 * @since 1.1.3
		 */
		$plugins = get_option('active_plugins');	
		if( ! isset( $plugins['reviewx/reviewx.php'] )) {
            if ( $this->make_reviewx_ready() ) {
                // redirect to plugin dashboard
                wp_safe_redirect( "admin.php?page=rx-wc-settings" );
            }
        }
    }

    protected function make_reviewx_ready() {
        $basename = 'reviewx/reviewx.php';
        $is_plugin_installed = $this->get_installed_plugin_data( $basename );
        $plugin_data = $this->get_plugin_data( 'reviewx', $basename );

        if( $is_plugin_installed ) {
            // upgrade plugin - attempt for once
            if( isset( $plugin_data->version ) && $is_plugin_installed['Version'] != $plugin_data->version ) {
                $this->upgrade_or_install_plugin( $basename );
            }

            // activate plugin
            if ( is_plugin_active( $basename )) {
                return delete_transient( 'rx_free_version' );
            } else {
                activate_plugin( $this->safe_path( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $basename ), '', false, true );
                return delete_transient( 'rx_free_version' );
            }
        } else {
            // install & activate plugin
            $download_link = isset( $plugin_data->download_link ) ? $plugin_data->download_link : REVIEWX_FREE_PLUGIN;
            if( $this->upgrade_or_install_plugin( $download_link, false ) ) {
                return delete_transient( 'rx_free_version' );
            }
        }
        return false;
    }

    protected function get_plugin_data( $slug = '', $basename = '' ){
        if( empty( $slug ) ) {
            return false;
        }
        $installed_plugin = false;
        if( $basename ) {
            $installed_plugin = $this->get_installed_plugin_data( $basename );
        }

        if( $installed_plugin ) {
            return $installed_plugin;
        }

        $args = array(
            'slug' => $slug,
            'fields' => array(
                'version' => false,
            ),
        );

        $response = wp_remote_post(
            esc_url('http://api.wordpress.org/plugins/info/1.0/'),
            array(
                'body' => array(
                    'action' => 'plugin_information',
                    'request' => serialize((object) $args),
                ),
            )
        );

        if ( is_wp_error( $response ) ) {
            return false;
        } else {
            $response = unserialize( wp_remote_retrieve_body( $response ) );

            if( $response ) {
                return $response;
            } else {
                return false;
            }
        }
    }

    public function get_installed_plugin_data( $basename = '' ) {
        if( empty( $basename ) ) {
            return false;
        }
        if( ! function_exists( 'get_plugins' ) ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugins = get_plugins();
        return isset( $plugins[ $basename ] ) ? $plugins[ $basename ] : false;
    }

    public function upgrade_or_install_plugin( $basename = '', $upgrade = true ) {
        if( empty( $basename ) ) {
            return false;
        }
        include_once ABSPATH . 'wp-admin/includes/file.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/class-automatic-upgrader-skin.php';

        $skin = new \Automatic_Upgrader_Skin;
        $upgrader = new \Plugin_Upgrader( $skin );
        if( $upgrade == true ) {
            $upgrader->upgrade( $basename );
        } else {
            $upgrader->install( $basename );
            activate_plugin( $upgrader->plugin_info(), '', false, true );
        }
        return $skin->result;
    }

    public function safe_path( $path ) {
        $path = str_replace(['//', '\\\\'], ['/', '\\'], $path);
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }
}