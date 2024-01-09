<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Summary_Reviews
 * @subpackage Summary_Reviews/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Summary_Reviews
 * @subpackage Summary_Reviews/includes
 * @author     Your Name <email@example.com>
 */
class Summary_Reviews_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

	//unschdule Zillow review download
	//first find the next schedule callback time
    $time_next_firing = wp_next_scheduled("srfw_daily_event");

    //use this function to unschedule it by passing the time and event name
    wp_unschedule_event($time_next_firing, "srfw_daily_event");
	wp_clear_scheduled_hook('srfw_daily_event');
	
	}

}
