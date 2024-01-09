<?php

/**
 * Fired during plugin activation
 *
 * @version 1.0.0
 */
class ReviewX_Pro_Activator {

    /**
     * work when plugin is activated
     */
    public static function activate() {
		/**
		 * Free installer 
		 * @since 1.0.0
		 */
		require_once REVIEWX_PRO_ROOT_DIR_PATH . 'includes/class-install-reviewx.php';
		new ReviewX_Installer();

        /**
         * Reqrite the rules on activation.
         */
        flush_rewrite_rules();
    }
}
