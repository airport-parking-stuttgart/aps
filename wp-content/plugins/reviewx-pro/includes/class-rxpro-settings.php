<?php

class ReviewXPro_Settings {

    public function __construct() {
        add_filter( 'rx_general_settings_builder_tabs', array( $this, 'pro_settings' ) );
    }
    
    public function pro_settings( $settings ) {
        $settings['go_admin_notification_tab'] = array(
            'title'         => __('Admin Notification', 'reviewx-pro'),
            'icon'          => 'overview.png',
            'sections'      => apply_filters('rx_source_tab_sections', array(
                'license_field' => array(
                   'title'  => __('Admin Notification', 'reviewx-pro'),
                   'view'   => 'ReviewXPro_Settings::admin_notification'
                ),
            ))                        
        );

        $settings['go_license_tab'] = array(
            'title'         => __('License', 'reviewx-pro'),
            'icon'          => 'overview.png',
            'sections'      => apply_filters('rx_source_tab_sections', array(
                'license_field' => array(
                   'title'  => __('License', 'reviewx-pro'),
                   'view'   => 'ReviewXPro_Settings::license'
                ),
            ))                        
        );
        return $settings;
    }

    public static function license() {
        include REVIEWX_PARTIALS_PATH . 'admin/rx-settings-sidebar.php';
    }
    public static function admin_notification() {
        include REVIEWX_PARTIALS_PATH . 'admin/rx-admin-notification.php';
    }

}

new ReviewXPro_Settings;