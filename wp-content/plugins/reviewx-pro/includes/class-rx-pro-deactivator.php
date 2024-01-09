<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://jouleslabs.com
 * @since      1.0.0
 *
 * @package    ReviewX
 * @subpackage ReviewX/includes
 */

class ReviewX_Pro_Deactivator {

	/**
	 * this function work plugin deactivation
	 * @since    1.0.0
	 */
	public static function deactivate() {
        flush_rewrite_rules();
	}

}
