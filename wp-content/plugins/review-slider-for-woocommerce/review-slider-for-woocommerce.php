<?php
/**
 * The plugin bootstrap file
 *
 * Review Slider for WooCommerce
 * review-slider-for-woocommerce
 * Summary_Reviews
 * Summary_Reviews_for_WooCommerce
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wpreviewslider.com/
 * @since             1.0
 * @package           Summary_Reviews_for_WooCommerce
 *
 * @wordpress-plugin
 * Plugin Name: 	  Review Slider for WooCommerce
 * Plugin URI:        https://wpreviewslider.com/
 * Description:       Easily display all your WooCommerce reviews at once in a cool summary review slider or grid on your homepage or any other page on your site.
 * Version:           1.4
 * Author:            LJ Apps
 * Author URI:        https://wpreviewslider.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       review-slider-for-woocommerce
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-review-slider-for-woocommerce-activator.php
 */
function srfw_activate_Summary_Reviews( $networkwide )
{
	//save time activated
	$newtime=time();
	update_option( 'srfw_activated_time_woo', $newtime );
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-review-slider-for-woocommerce-activator.php';
    Summary_Reviews_Activator::activate_all( $networkwide );
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-review-slider-for-woocommerce-deactivator.php
 */
function srfw_deactivate_Summary_Reviews()
{
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-review-slider-for-woocommerce-deactivator.php';
    Summary_Reviews_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'srfw_activate_Summary_Reviews' );
register_deactivation_hook( __FILE__, 'srfw_deactivate_Summary_Reviews' );
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-review-slider-for-woocommerce.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function srfw_run_Summary_Reviews()
{
    //define plugin location constant

    define( 'srfw_plugin_dir', plugin_dir_path( __FILE__ ) );
    define( 'srfw_plugin_url', plugins_url( '', __FILE__ ) );


    $plugin = new Summary_Reviews();
    $plugin->run();
}

//for running the cron job
add_action('srfw_daily_event', 'srfw_do_this_daily');

function srfw_do_this_daily() {

		
	require_once plugin_dir_path( __FILE__ ) . 'admin/class-review-slider-for-woocommerce-admin.php';
	$plugin_admin = new Summary_Reviews_Admin( 'review-slider-for-woocommerce', '1.4' );
	$plugin_admin->srfw_download_zillow_master();
	
}

//start the plugin-------------
srfw_run_Summary_Reviews();