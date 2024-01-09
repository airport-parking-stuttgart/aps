<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @version 1.0.0
 */
class ReviewXPro {

	protected $loader;
    public $plugin_name;
    private $type = 'reviewx';
    private $extension_ids = [];	

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'REVIEWX_PRO_VERSION' ) ) {
			$this->version = REVIEWX_PRO_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'reviewx-pro';
		$this->load_admin_dependencies();
		$this->set_locale();
		add_action( 'plugins_loaded', array( $this, 'define_pro_admin_hooks' ) );
		add_action( 'reviewx_load_depedencies', array( $this, 'load_dependencies' ) );
		add_action( 'rx_extensions_init', array( $this, 'inject_features' ) );
		add_action( 'rx_active_reviewx', array( $this, 'active_extension'), 11 );
		add_action( 'admin_init', array( $this, 'redirect' ) );

	}

    /**
     * Redirect to setting page when WooCommerce plugin is activated
     */
    public function redirect() {
        // Bail if no activation transient is set.
        if ( ! get_transient( '_rx_plugin_activation' ) ) {
            return;
        }
        // Delete the activation transient.
        delete_transient( '_rx_plugin_activation' );

        wp_safe_redirect( add_query_arg( array(
            'page'		=> 'rx-wc-settings'
        ), admin_url( 'admin.php' ) ) );
    }

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once REVIEWX_PRO_ROOT_DIR_PATH . 'includes/class-rx-pro-loader.php';

        /**
         * Extension Files
         */
		require_once REVIEWX_PRO_ROOT_DIR_PATH . 'includes/features/class-rxpro-field-options.php';
		require_once REVIEWX_PRO_ROOT_DIR_PATH . 'includes/class-rxpro-settings.php';		

		$this->loader = new ReviewX_Pro_Loader();

		/**
		 * The class responsible for internationalization
		 * core plugin.
		 */		
		// require_once REVIEWX_PRO_ROOT_DIR_PATH . 'includes/class-rx-pro-i18n.php';
		
		// $plugin_i18n = new ReviewX_Pro_i18n();

		// $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}	

	public function load_admin_dependencies() {
		require_once REVIEWX_PRO_ADMIN_DIR_PATH . "class-import-review.php";
		require_once REVIEWX_PRO_ADMIN_DIR_PATH . 'class-rxpro-admin.php';
	}

	public function define_pro_admin_hooks() {
		$pro_admin = new ReviewXPro_Admin( $this->get_plugin_name(), $this->get_version() );
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the ReviewX_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		/**
		 * The class responsible for internationalization
		 * core plugin.
		 */		
		require_once REVIEWX_PRO_ROOT_DIR_PATH . 'includes/class-rx-pro-i18n.php';
		
		$plugin_i18n = new ReviewX_Pro_i18n();

		add_action( 'plugins_loaded', [$plugin_i18n, 'load_plugin_textdomain'] );

	}
	
	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		return $this;
	}

	public function inject_features() {
		// Initiating the above Class as an object
		if(class_exists("ReviewXPro_Features")){
			new ReviewXPro_Features( $this->plugin_name, $this->version );
		}
		if (!class_exists("ReviewXPro_Features")) {
			error_log('Class ReviewXPro_Features does not exist');
		}
		

	}	

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    ReviewX_Pro_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
