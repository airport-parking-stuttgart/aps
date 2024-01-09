<?php
/**
 * Plugin Name:       ReviewX Pro
 * Plugin URI:        https://reviewx.io
 * Description:       Advanced Multi-criteria Rating & Reviews for WooCommerce. Turn your customer reviews into sales by collecting and leveraging reviews, ratings with multiple criteria.
 * Version:           1.4.6
 * Author:            WPDeveloper
 * Author URI:        https://wpdeveloper.net
 * Text Domain:       reviewx-pro
 * Domain Path:       /languages
 * Requires PHP:      5.6
 * Requires at least: 4.4
 * Tested up to:      6.1.1
 * WC requires at least: 3.1
 * WC tested up to: 7.2.0 
 * @package     ReviewX_Pro
 * @author      WPDeveloper <support@wpdeveloper.net>
 * @copyright   Copyright (C) 2021 WPDeveloper & JoulesLabs. All rights reserved.
 * @license     GPLv3 or later
 * @since       1.0.0
 * 
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'REVIEWX_PRO_VERSION', '1.4.6' );
define( 'REVIEWX_PRO_ROOT_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'REVIEWX_PRO_ADMIN_DIR_PATH', REVIEWX_PRO_ROOT_DIR_PATH . 'admin/' );
define( 'REVIEWX_PRO_PUBLIC_PATH', REVIEWX_PRO_ROOT_DIR_PATH . 'public/' );
define( 'REVIEWX_PRO_URL', plugins_url( '/', __FILE__ ) );
define( 'REVIEWX_PRO_ADMIN_URL', REVIEWX_PRO_URL . 'admin/' );
define( 'REVIEWX_PRO_PUBLIC_URL', REVIEWX_PRO_URL . 'public/' );
define( 'REVIEWX_PRO_FILE', __FILE__ );
define( 'REVIEWX_PRO_BASENAME', plugin_basename( __FILE__ ) );
define( 'REVIEWX_FREE_PLUGIN', REVIEWX_PRO_ROOT_DIR_PATH . 'assets/library/reviewx.zip' );

// Licensing
define( 'REVIEWX_PRO_STORE_URL', 'https://wpdeveloper.net/' );
define( 'REVIEWX_PRO_SL_ITEM_ID', 462493 );
define( 'REVIEWX_PRO_SL_ITEM_SLUG', 'reviewx-pro' );
define( 'REVIEWX_PRO_SL_ITEM_NAME', 'ReviewX Pro' );

/**
 * rx-function.php require for load plugin self function
 */
require_once ( REVIEWX_PRO_ROOT_DIR_PATH . 'includes/rx-functions.php' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-rx-activator.php
 */
function activate_reviewx_pro() {
    if( class_exists( 'WooCommerce' ) ) {
        require_once REVIEWX_PRO_ROOT_DIR_PATH . 'includes/class-rx-pro-activator.php';
        ReviewX_Pro_Activator::activate();
    }
}
register_activation_hook( REVIEWX_PRO_FILE , 'activate_reviewx_pro' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-rx-pro-deactivator.php
 */
function deactivate_reviewx_pro() {
	require_once REVIEWX_PRO_ROOT_DIR_PATH . 'includes/class-rx-pro-deactivator.php';
    ReviewX_Pro_Deactivator::deactivate();
}
register_deactivation_hook( REVIEWX_PRO_FILE , 'deactivate_reviewx_pro' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once REVIEWX_PRO_ROOT_DIR_PATH . 'includes/class-rxpro.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_reviewx_pro() {

	$plugin = new ReviewXPro();
	$plugin->run();

}
run_reviewx_pro();

// Install Core plugin
include_once REVIEWX_PRO_ROOT_DIR_PATH . '/includes/class-rxpro-core-installer.php';
new ReviewX_Install_Core('');

/**
 * Plugin Licensing
 *
 * @since v1.0.0
 */
function reviewx_plugin_licensing() {

	// Requiring Licensing Class
	require_once REVIEWX_PRO_ROOT_DIR_PATH.'includes/licensing/rxpro-licensing.php';
	if ( is_admin() ) {
		// Setup the settings page and validation
		$licensing = new ReviewX_Licensing(
			REVIEWX_PRO_SL_ITEM_SLUG,
			REVIEWX_PRO_SL_ITEM_NAME,
			'reviewx-pro'
		);
	}

}
reviewx_plugin_licensing();

/**
 * Handles Updates
 *
 * @since 1.0.0
 */
function reviewx_plugin_updater() {

	// Requiring the Updater class
	require_once REVIEWX_PRO_ROOT_DIR_PATH.'includes/licensing/rxpro-updater.php';

	// Disable SSL verification
	add_filter( 'edd_sl_api_request_verify_ssl', '__return_false' );

	// Setup the updater
	$license = get_option( REVIEWX_PRO_SL_ITEM_SLUG . '-license-key' );
	$updater = new ReviewX_Plugin_Updater( REVIEWX_PRO_STORE_URL, __FILE__, array(
			'version'      => REVIEWX_PRO_VERSION,
			'license'      => $license,
			'item_id'      => REVIEWX_PRO_SL_ITEM_ID,
			'author'       => 'WPDeveloper',
		)
	);
}
add_action( 'admin_init', 'reviewx_plugin_updater' );