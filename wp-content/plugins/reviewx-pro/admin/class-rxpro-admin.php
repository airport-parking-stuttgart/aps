<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 *
 */

use ReviewX\Controllers\Admin\Core\ReviewxAdmin;
use ReviewX\Controllers\Admin\Core\ReviewxMetaBox;

if( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! class_exists( 'ReviewXPro_Admin' ) ) { 
	class ReviewXPro_Admin {

		/**
		 * The ID of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $plugin_name    The ID of this plugin.
		 */
		private $plugin_name;

		/**
		 * The plugin option
		 *
		 * @since    1.0.0
		 * @access   public
		 * @var string option.
		 */
		public static $prefix = 'rx_option_';

		/**
		 * The plugin settings
		 *
		 * @since    1.0.0
		 * @access   public
		 * @var string settings.
		 */		
		public static $settings;

		/**
		 * The version of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $version    The current version of this plugin.
		 */
		private $version;

		private $menuPosition;

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    1.0.0
		 * @param      string    $plugin_name       The name of this plugin.
		 * @param      string    $version    The version of this plugin.
		 */
		public function __construct( $plugin_name, $version ) {

			$this->plugin_name 	= $plugin_name;
            $this->version 		= $version;

            $this->menuPosition = [
				'all_reviews',
				'rx_add_review',
                'settings',
                'review_email',
				'quick_setup',
				'reviewx_discount',
				'rx_import_export',
				'rx_import_history',
				'rx_setting',
				'google_reviews',
				'facebook_reviews',
            ];

			$this->beforeLoaded();

			add_filter( 'rx_add_manual_review', [ $this, 'reviewx_add_new_review' ] );

			new ReviewXPro_Import_Review();
		}

		protected function beforeLoaded()
        {
            $this->loadSubmenu();
        }

        private function loadSubmenu()
        {			
			if( class_exists('ReviewX_Helper') ){
				$wc_is_enabled = \ReviewX_Helper::check_wc_is_enabled();
			} else {
				$wc_is_enabled = false;
			}
			
            add_filter('rx_admin_submenu', function ($fields) use ($wc_is_enabled) {
				if(defined('REVIEWX_PLUGIN_NAME')){
					$old_callback = array((new ReviewxAdmin(REVIEWX_PLUGIN_NAME, "1.0.2")), 'general_settings');
				}else{
					$old_callback = array((new ReviewxAdmin(PLUGIN_NAME, "1.0.2")), 'general_settings');
				}
				$sum_menu = array(
					'rx_add_review' => [
						'parent_slug'   => 'rx-admin',
						'page_title'    => __( 'Add New Review', 'reviewx-pro' ),
						'menu_title'    => __( 'Add New Review', 'reviewx-pro' ),
						'capability'    => 'manage_options',
						'menu_slug'     => 'rx-add-review',
						'callback'      =>  array( $this, 'addReview' )
					],
					
					'rx_setting' => [
						'parent_slug'   => 'rx-admin',
						'page_title'    => __('Settings', 'reviewx-pro'),
						'menu_title'    => __('Settings', 'reviewx-pro'),
						'capability'    => 'manage_options',
						'menu_slug'     => 'rx-settings',
						'callback'      =>  $old_callback
					],
					'rx_import_export' => [
						'parent_slug'   => 'rx-admin',
						'page_title'    => __('Review Import', 'reviewx-pro'),
						'menu_title'    => __('Review Import', 'reviewx-pro'),
						'capability'    => 'manage_options',
						'menu_slug'     => 'rx-external-review',
						'callback'      =>  array( $this, 'external_review' )
					],
					'rx_import_history' => [
						'parent_slug'   => 'rx-admin',
						'page_title'    => __('Import History', 'reviewx-pro'),
						'menu_title'    => __('Import History', 'reviewx-pro'),
						'capability'    => 'manage_options',
						'menu_slug'     => 'rx-review-import-history',
						'callback'      =>  array( $this, 'external_review_history' )
					],
				);
				
				if( $wc_is_enabled && class_exists('WooCommerce') ) {
					$sum_menu = array_merge( $sum_menu, [
						'review_email' => [
							'parent_slug'   => 'rx-admin',
							'page_title'    => __( 'WC Review Email', 'reviewx-pro' ),
							'menu_title'    => __( 'WC Review Email', 'reviewx-pro' ),
							'capability'    => 'manage_options',
							'menu_slug'     => 'reviewx-review-email',
							'callback'      =>  array( $this, 'callReviewEmail' )
						],
					] );
					$fields = array_merge($fields, $sum_menu );
				} else {
					$fields = array_merge( $fields, $sum_menu );
				}

                $rearrange = [];
                foreach ($this->menuPosition as $position) {
					if( isset($fields[$position]) ) {
						$rearrange[$position] = $fields[$position];
					}
                }

                return $rearrange;
            });
		}
		
		public function callReviewEmail()
		{
			if(defined('REVIEWX_PLUGIN_NAME')){
				$reviewAdmin = (new ReviewxAdmin(REVIEWX_PLUGIN_NAME, "1.0.2"));
			}else{
				$reviewAdmin = (new ReviewxAdmin(PLUGIN_NAME, "1.0.2"));
			}
            
            $reviewAdmin->reviewEmailFields();
			return $reviewAdmin->review_email();
		}

		public function external_review() {
			require_once REVIEWX_PRO_ADMIN_DIR_PATH . 'partials/external_review/import-export.php';
		}

		public function external_review_history() {
			require_once REVIEWX_PRO_ADMIN_DIR_PATH . 'partials/external_review/import-history.php';
		}

		public function reviewx_add_new_review($data){

			echo '<a title="Add New Review" href="'.esc_url(admin_url( 'admin.php?page=rx-add-review' )).'" class="rx-manual-review-pro">
					<button class="rx-common-form-button rx-common-form-button-free" type="button">
						'.__('Add New Review', 'reviewx').'
					</button>
				</a>';
				
		}

		public static function reviewx_available_cpt(){
			$data = [];
			$post_args = array(
                'post_type'         => 'reviewx',
                'numberposts'       => -1,
			);
			$post_args              = array_merge( $post_args, 
										array( 
											'meta_query' => array(
												array(
													'key'     => '_rx_meta_active_check',
													'value'   => 1,
													'compare' => '=',
												),
											)
										)
									);
			$reviewx = new \WP_Query( $post_args );									
			if( $reviewx->have_posts() ) :
				$i = 0;
				while( $reviewx->have_posts() ) : $reviewx->the_post(); 
					$post_id 	= get_the_ID();
					$data[$i]	= get_post_meta($post_id, '_rx_meta_custom_post_types', true);
					$i++;
				endwhile;
			endif;	
			return $data;
		}

		public static function reviewx_available_cpt_posts(){
			global $wpdb;
			$data 	= [];
			if( ! empty( self::reviewx_available_cpt() ) ) {
				$cpt 	= count( self::reviewx_available_cpt() );
			} else {
				$cpt = 1; 
			}
			$table 	= $wpdb->prefix . 'posts'; 			
			$sql 	= $wpdb->prepare("SELECT ID, `post_title` FROM $table WHERE `post_type` IN (".implode(', ', array_fill(0, $cpt, '%s')).") AND `post_status`= 'publish' ORDER BY post_title", $cpt);
			$data 	= $wpdb->get_results($sql, ARRAY_A);
			return $data;			
		}

		public static function reviewx_products_posts(){
			global $wpdb;
			$data 	= [];
			$table 	= $wpdb->prefix . 'posts'; 
			$sql 	= $wpdb->prepare("SELECT ID, `post_title` FROM $table WHERE `post_type` = %s AND `post_status`= 'publish' ORDER BY post_title", 'product');
			if( get_option( '_rx_wc_active_check' ) == 1 ) {
				$data 	= $wpdb->get_results($sql, ARRAY_A);
			}
			
			if(!empty(self::reviewx_available_cpt_posts())){
				return array_merge($data, self::reviewx_available_cpt_posts());
			} else {
				return $data;
			}			
		}

		public static function reviewx_users(){
			$args = array(
				'orderby' => 'user_nicename',
				'order' => 'ASC'
			);
			return $users = get_users($args);
		}

		public function addReview(){
			include REVIEWX_PARTIALS_PATH . 'admin/setting-header.php';
			return include REVIEWX_PRO_ADMIN_DIR_PATH . 'partials/add-manual-review.php';
		}

	}
}