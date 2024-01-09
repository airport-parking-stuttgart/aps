<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Summary_Reviews
 * @subpackage Summary_Reviews/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Summary_Reviews
 * @subpackage Summary_Reviews/admin
 * @author     Your Name <email@example.com>
 */
class Summary_Reviews_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugintoken    The ID of this plugin.
	 */
	private $plugintoken;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugintoken       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugintoken, $version ) {

		$this->_token = $plugintoken;
		$this->version = $version;
		//for testing==============
		$this->version = time();
		//===================
				

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Summary_Reviews_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Summary_Reviews_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		//only load for this plugin srfw-settings-pricing
		if(isset($_GET['page'])){
			if($_GET['page']=="srfw-reviews" || $_GET['page']=="srfw-templates_posts" || $_GET['page']=="srfw-get_woo" || $_GET['page']=="srfw-get_pro"){
			wp_enqueue_style( $this->_token, plugin_dir_url( __FILE__ ) . 'css/srfw_admin.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->_token."_srfw_w3", plugin_dir_url( __FILE__ ) . 'css/srfw_w3.css', array(), $this->version, 'all' );
			}
			//load template styles for srfw-templates_posts page
			if($_GET['page']=="srfw-templates_posts"|| $_GET['page']=="srfw-get_pro"){
				//enque template styles for preview
				wp_enqueue_style( $this->_token."_style1", plugin_dir_url(dirname(__FILE__)) . 'public/css/srfw-public_template1.css', array(), $this->version, 'all' );
			}
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Summary_Reviews_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Summary_Reviews_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		

		//scripts for all pages in this plugin
		if(isset($_GET['page'])){
			if($_GET['page']=="srfw-reviews" || $_GET['page']=="srfw-templates_posts" || $_GET['page']=="srfw-get_woo" || $_GET['page']=="srfw-get_pro"){
				//pop-up script
				wp_register_script( 'simple-popup-js',  plugin_dir_url( __FILE__ ) . 'js/srfw_simple-popup.min.js' , '', $this->version, false );
				wp_enqueue_script( 'simple-popup-js' );
				
			}
		}
		
	
		//scripts for review list page
		if(isset($_GET['page'])){
			if($_GET['page']=="srfw-reviews"){
				//admin js
				wp_enqueue_script('srfw_review_list_page-js', plugin_dir_url( __FILE__ ) . 'js/srfw_review_list_page.js', array( 'jquery','media-upload','thickbox' ), $this->version, false );
				//used for ajax
				wp_localize_script('srfw_review_list_page-js', 'adminjs_script_vars', 
					array(
					'srfw_nonce'=> wp_create_nonce('randomnoncestring')
					)
				);
				
 				wp_enqueue_script('thickbox');
				wp_enqueue_style('thickbox');
		 
				wp_enqueue_script('media-upload');
				wp_enqueue_script('wptuts-upload');

			}
			
			//scripts for templates posts page
			if($_GET['page']=="srfw-templates_posts"){
			
				//admin js
				wp_enqueue_script('srfw_templates_posts_page-js', plugin_dir_url( __FILE__ ) . 'js/srfw_templates_posts_page.js', array( 'jquery' ), $this->version, false );
				//used for ajax
				wp_localize_script('srfw_templates_posts_page-js', 'adminjs_script_vars', 
					array(
					'srfw_nonce'=> wp_create_nonce('randomnoncestring'),
					'pluginsUrl' => srfw_plugin_url
					)
				);
 				wp_enqueue_script('thickbox');
				wp_enqueue_style('thickbox');
				
				//add color picker here
				wp_enqueue_style( 'wp-color-picker' );
				//enque alpha color add-on wpzillow-wp-color-picker-alpha.js
				wp_enqueue_script( 'wp-color-picker-alpha', plugin_dir_url( __FILE__ ) . 'js/wpzillow-wp-color-picker-alpha.js', array( 'wp-color-picker' ), '2.1.2', false );

			}
		}
		
	}
	
	public function add_menu_pages() {

		/**
		 * adds the menu pages to wordpress
		 */

		$page_title = 'Review Slider for WooCommerce : Settings';
		$menu_title = 'Review Slider';
		$capability = 'manage_options';
		$menu_slug = 'srfw-get_woo';
		
		// Now add the submenu page for the actual reviews list
		$submenu_page_title = 'Review Slider for WooCommerce : Settings';
		$submenu_title = 'WooCommerce Reviews';
		$submenu_slug = 'srfw-get_woo';
		
		add_menu_page($page_title, $menu_title, $capability, $menu_slug, array($this,'srfw_getwoo'),'dashicons-star-half');
		
		add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, array($this,'srfw_getwoo'));
		
		
		// Now add the submenu page for review list
		$submenu_page_title = 'Review Slider for WooCommerce : Reviews List';
		$submenu_title = 'Reviews List';
		$submenu_slug = 'srfw-reviews';
		add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, array($this,'srfw_reviews'));

		
		// Now add the submenu page for the reviews templates
		$submenu_page_title = 'Review Slider for WooCommerce : Templates';
		$submenu_title = 'Templates';
		$submenu_slug = 'srfw-templates_posts';
		add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, array($this,'srfw_templates_posts'));
		
		// Now add the submenu page for the reviews templates
		$submenu_page_title = 'Review Slider for WooCommerce : Upgrade';
		$submenu_title = 'Get Pro';
		$submenu_slug = 'srfw-get_pro';
		add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, array($this,'srfw_getpro'));
	

	}
	
	public function srfw_reviews() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/review_list.php';
	}
	
	public function srfw_templates_posts() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/templates_posts.php';
	}
	public function srfw_getwoo() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/get_woo.php';
	}
	public function srfw_getpro() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/get_pro.php';
	}
	
	/**
	 * custom option and settings on woo page
	 */
	 //===========start woo page settings===========================================================
	public function srfw_woo_settings_init()
	{
	
		// register a new setting for "srfw-get_woo" page
		register_setting('srfw-get_woo', 'srfw_woo_settings');
		
		// register a new section in the "srfw-get_woo" page
		add_settings_section(
			'srfw_woo_section_developers',
			'',
			array($this,'srfw_woo_section_developers_cb'),
			'srfw-get_woo'
		);
		

		//Turn on woo Reviews cron
		add_settings_field("woo_radio_sync", __( 'Sync WooCommerce:', 'review-slider-for-woocommerce' ), array($this,'woo_radio_display_sync'), "srfw-get_woo", "srfw_woo_section_developers",
			[
				'label_for'         => 'woo_radio_sync',
				'class'             => 'srfw_row',
				'srfw_custom_data' => 'custom',
			]); 
			
		//add sync type select
		add_settings_field(
			'woo_sync_all', 
			__( 'Sync Type:', 'review-slider-for-woocommerce' ),
			array($this,'srfw_field_woo_sync'),
			'srfw-get_woo',
			'srfw_woo_section_developers',
			array(
				'label_for'         => 'woo_sync_all',
				'class'             => 'srfw_row wooburlmore'
			)
		);
		//add name option
		add_settings_field(
			'woo_name_options', 
			__( 'Name Options:', 'review-slider-for-woocommerce' ),
			array($this,'srfw_field_woo_name'),
			'srfw-get_woo',
			'srfw_woo_section_developers',
			array(
				'label_for'         => 'woo_name_options',
				'class'             => 'srfw_row'
			)
		);
		//add rating option
		add_settings_field(
			'woo_rating_options', 
			__( 'Default Rating:', 'review-slider-for-woocommerce' ),
			array($this,'srfw_field_woo_rating'),
			'srfw-get_woo',
			'srfw_woo_section_developers',
			array(
				'label_for'         => 'woo_rating_options',
				'class'             => 'srfw_row'
			)
		);

	}
	//==== developers section cb ====
		public function srfw_field_woo_rating($args)
	{
		$options = get_option('srfw_woo_settings');
		if(!isset($options[$args['label_for']])){
			$options[$args['label_for']]='all';
		}
		
		?>
		<select id="<?php echo  esc_attr($args['label_for']); ?>" name="srfw_woo_settings[<?php echo  esc_attr($args['label_for']); ?>]">
			<option value="blank" <?php selected( $options[$args['label_for']], 'blank' ); ?>><?php echo esc_attr__( 'Leave Blank', 'review-slider-for-woocommerce' ); ?></option>
			</option>
			<option value="5" <?php selected( $options[$args['label_for']], '5' ); ?>><?php echo esc_attr__( '5', 'review-slider-for-woocommerce' ); ?></option>
			<option value="4" <?php selected( $options[$args['label_for']], '4' ); ?>><?php echo esc_attr__( '4', 'review-slider-for-woocommerce' ); ?></option>
			<option value="3" <?php selected( $options[$args['label_for']], '3' ); ?>><?php echo esc_attr__( '3', 'review-slider-for-woocommerce' ); ?></option>
			<option value="2" <?php selected( $options[$args['label_for']], '2' ); ?>><?php echo esc_attr__( '2', 'review-slider-for-woocommerce' ); ?></option>
			<option value="1" <?php selected( $options[$args['label_for']], '1' ); ?>><?php echo esc_attr__( '1', 'review-slider-for-woocommerce' ); ?></option>
		</select>		
		<p class="description">
			<?php echo  esc_html__('Use this to assign a rating value if the review/comment does not have one.', 'review-slider-for-woocommerce'); ?>
		</p>
		<?php
		
	}
	
	public function srfw_woo_section_developers_cb($args)
	{

		echo "<h3>".__('Welcome!', 'review-slider-for-woocommerce')."</h3>";
		echo "<p>".__('Thank you for giving my plugin a try. If you run in to any trouble or have any questions feel free to contact me. If you would like more features, please check out the Pro version ', 'review-slider-for-woocommerce')."<a href='https://wpreviewslider.com/' target='_blank'>".__('here', 'review-slider-for-woocommerce')."</a></p>";
		echo "<h3>".__('Getting Started:', 'review-slider-for-woocommerce')."</h3>";
		echo "<p>".__('- Use this page to sync your WooCommerce product reviews with this plugin.<br>- They will be listed under the Review List tab. <br>- Go to the Templates tab to create a review template to add them to your site.', 'review-slider-for-woocommerce')."</p><p><b>".__('Notes:', 'review-slider-for-woocommerce')."</b><br>".__('- When you approve of a new WooCommerce review, it will automatically be pulled in to this plugin. <br>- If you delete or unapproved the review then it will be removed from this plugin. <br>- This does not work in reverse, e.g., if you delete a review from this plugin, it does not delete the original WooCommerce review.', 'review-slider-for-woocommerce')."</p>";
	}


	public function woo_radio_display_sync($args)
		{
		$options = get_option('srfw_woo_settings');
		if(!isset($options['woo_radio_sync'])){
			$options['woo_radio_sync']='no';
		}

		   ?>
				<input type="radio" name="srfw_woo_settings[<?php echo esc_attr($args['label_for']); ?>]" value="yes" <?php checked('yes', $options[$args['label_for']], true); ?>><?php echo  esc_html__('Reviews', 'review-slider-for-woocommerce'); ?>&nbsp;&nbsp;&nbsp;
				<input type="radio" name="srfw_woo_settings[<?php echo esc_attr($args['label_for']); ?>]" value="com" <?php checked('com', $options[$args['label_for']], true); ?>><?php echo  esc_html__('Comments', 'review-slider-for-woocommerce'); ?>&nbsp;&nbsp;&nbsp;
				<input type="radio" name="srfw_woo_settings[<?php echo esc_attr($args['label_for']); ?>]" value="rc" <?php checked('rc', $options[$args['label_for']], true); ?>><?php echo  esc_html__('Reviews & Comments', 'review-slider-for-woocommerce'); ?>&nbsp;&nbsp;&nbsp;
				<input type="radio" name="srfw_woo_settings[<?php echo esc_attr($args['label_for']); ?>]" value="no" <?php checked('no', $options[$args['label_for']], true); ?>><?php echo  esc_html__('None', 'review-slider-for-woocommerce'); ?>
				<p class="description">
				<?php
				echo __('Tells the plugin what type of comment to sync. ', 'review-slider-for-woocommerce');
				?>
				</p>
		   <?php
		}
		
	public function srfw_field_woo_sync($args)
	{
		$options = get_option('srfw_woo_settings');
		if(!isset($options[$args['label_for']])){
			$options[$args['label_for']]='all';
		}
		
		?>
		<select id="<?php echo  esc_attr($args['label_for']); ?>" name="srfw_woo_settings[<?php echo  esc_attr($args['label_for']); ?>]">
			<option value="approved" <?php selected( $options[$args['label_for']], 'approved' ); ?>><?php echo esc_attr__( 'Approved Only', 'review-slider-for-woocommerce' ); ?></option>
			</option>
			<option value="all" <?php selected( $options[$args['label_for']], 'all' ); ?>><?php echo esc_attr__( 'All Comments', 'review-slider-for-woocommerce' ); ?></option>
		</select>		
		<p class="description">
			<?php echo  esc_html__('Use this if you want to leave Unapproved WooCommerce reviews out of this plugin.', 'review-slider-for-woocommerce'); ?>
		</p>
		<?php
		
	}
	public function srfw_field_woo_name($args)
	{
		// get the value of the setting we've registered with register_setting()
		$options = get_option('srfw_woo_settings');
		if(!isset($options[$args['label_for']])){
			$options[$args['label_for']]='author';
		}
		//print_r($options);
		// output the field
		?>
				<input type="radio" name="srfw_woo_settings[<?php echo esc_attr($args['label_for']); ?>]" value="author" <?php checked('author', $options[$args['label_for']], true); ?>>Username&nbsp;&nbsp;&nbsp;
				<input type="radio" name="srfw_woo_settings[<?php echo esc_attr($args['label_for']); ?>]" value="first" <?php checked('first', $options[$args['label_for']], true); ?>>First Name&nbsp;&nbsp;&nbsp;
				<input type="radio" name="srfw_woo_settings[<?php echo esc_attr($args['label_for']); ?>]" value="last" <?php checked('last', $options[$args['label_for']], true); ?>>Last Name&nbsp;&nbsp;&nbsp;
				<input type="radio" name="srfw_woo_settings[<?php echo esc_attr($args['label_for']); ?>]" value="firstlast" <?php checked('firstlast', $options[$args['label_for']], true); ?>>First & Last Name&nbsp;&nbsp;&nbsp;
				<p class="description">
			<?php echo  esc_html__('Set this to change the way the name is saved in your database. If the first or last name can\'t be found, then the username will be used.', 'review-slider-for-woocommerce'); ?>
		</p>
		<?php
	}
	//=======end woo page settings========================================================



	/**
	 * sync woocommerce reviews when clicking the save button on WooCommerce page
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
	public function srfw_download_woo() {
      global $pagenow;
	  //echo "here1:".$pagenow;
      if (isset($_GET['settings-updated']) && $pagenow=='admin.php' && current_user_can('export') && $_GET['page']=='srfw-get_woo') {
		$this->srfw_download_woo_master();
      }
    }

	/**
	 * download woocommerce reviews
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
	public function srfw_download_woo_master() {
		//echo "here";
		$options = get_option('srfw_woo_settings');
		//print_r($options);
		//Array([woo_radio_sync] => yes,[woo_sync_all] => all)

		if($options['woo_radio_sync']!='no'){
			//grab all woocommerce reviews depending on settings
			global $wpdb;
			
			if($options['woo_radio_sync']=='yes'){		//sync reviews only
				if($options['woo_sync_all']=='all'){
					$args = array(
						'type__in'  => 'review',
						 'parent'      => 0,	//don't get responses yet
						 'status' => 'all',
					);
				} else if($options['woo_sync_all']=='approved'){
					$args = array(
						'type__in'  => 'review',
						 'parent'      => 0,	//don't get responses yet
						 'status' => 'approve',
					);
				}
			} else if($options['woo_radio_sync']=='com'){		//sync reviews only
				if($options['woo_sync_all']=='all'){
					$args = array(
						'type__in'  => 'comment',
						 'parent'      => 0,	//don't get responses yet
						 'status' => 'all',
					);
				} else if($options['woo_sync_all']=='approved'){
					$args = array(
						'type__in'  => 'comment',
						 'parent'      => 0,	//don't get responses yet
						 'status' => 'approve',
					);
				}
			} else if($options['woo_radio_sync']=='rc'){		//sync reviews only
				if($options['woo_sync_all']=='all'){
					$args = array(
						'type__in'  => array('review','comment'),
						 'parent'      => 0,	//don't get responses yet
						 'status' => 'all',
					);
				} else if($options['woo_sync_all']=='approved'){
					$args = array(
						'type__in'  => array('review','comment'),
						 'parent'      => 0,	//don't get responses yet
						 'status' => 'approve',
					);
				}
			}
				
			$comments = get_comments( $args );
			
			//print_r($comments);
			
			//echo get_avatar( 'jgwhite33@hotmail.com', 32 );
			//echo get_avatar_url( 'jgwhite33@hotmail.com', 32 );
			
			//loop through the comments, find the avatar, and the rating, date, product image, product title, cat id, prod id, text, etc...
			foreach ($comments as $comment) {
					// Output comments etc here
									
					$table_name = $wpdb->prefix . 'srfw_reviews';
					//add check to see if already in db, skip if it is and end loop
					$reviewindb = 'no';
					$unixtimestamp = $this->myStrtotime($comment->comment_date);
					$checkrow = $wpdb->get_var( "SELECT id FROM ".$table_name." WHERE created_time_stamp = '".$unixtimestamp."' " );
					
					$tempreviewarray = $this->srfw_returncommentinfoarray($comment,$options['woo_name_options']);
						
					if( empty( $checkrow ) ){
						$reviewindb = 'no';
						$reviews['add'][] = $tempreviewarray;
					} else {
						$reviewindb = 'yes';
						$reviews['update'][] = $tempreviewarray;
					}
					unset($tempreviewarray);
					
			}
			
			//insert or update array in to reviews table.
			if(isset($reviews['add']) && count($reviews['add'])>0){
				foreach ( $reviews['add'] as $stat ){
					$insertnum = $wpdb->insert( $table_name, $stat );
					$this->my_print_error();
					//update badge totals
					//$this->updatetotalavgreviews('woocommerce', $stat['pageid'], '','',$stat['pagename']);
				}
				$this->errormsg = count($reviews['add']).' '.esc_html__('added to database.', 'wp-review-slider-pro');
				
				//send $reviews array to function to send email if turned on.
				//$this->sendnotificationemail($reviews['add'], "woocommerce");
			}
			if(isset($reviews['update']) && count($reviews['update'])>0){
				foreach ( $reviews['update'] as $stat ){
					$tempreviewid = $stat['reviewer_id'];
					$insertnum = $wpdb->update( $table_name, $stat,array( 'reviewer_id' => $tempreviewid ) );
					$this->my_print_error();
					//update badge totals
					//$this->updatetotalavgreviews('woocommerce', $stat['pageid'], '','',$stat['pagename']);
				}
				$this->errormsg = count($reviews['update']).' '.esc_html__('updated in database.', 'wp-review-slider-pro');
			}

			//we also need to hook in to when a new comment is added, deleted, approved, unapproved
			
		}
		
	}
	
	public function my_print_error(){

		global $wpdb;

		if($wpdb->last_error !== '') :

			$str   = htmlspecialchars( $wpdb->last_error, ENT_QUOTES );
			$str2   = htmlspecialchars( $wpdb->last_result, ENT_QUOTES );
			$query = htmlspecialchars( $wpdb->last_query, ENT_QUOTES );

			print "<div id='error'>
			<p class='wpdberror'><strong>WordPress database error:</strong> [$str]<br />[$str2]<br />
			<code>$query</code></p>
			</div>";

		endif;

	}
	
	/**
	 * ran when a new comment is inserted, deleted (or spam), or updated (approved, unapproved)
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
	public function srfw_returncommentinfoarray($comment,$nameoption = 'author'){
		//print_r($comment);
		$options = get_option('srfw_woo_settings');
		$user_name = $comment->comment_author;
		$results['reviewer_name'] = $user_name;
		$results['reviewer_id'] = 'woo_'.str_replace(' ','',$user_name)."_".$comment->user_id."_".$comment->comment_ID;
			//if we need first or last name find it here
			if($nameoption!='author'){
				$user = get_user_by( 'email', $comment->comment_author_email );
				if ( ! empty( $user ) ) {
				//echo ‘User is ‘ . $user->first_name . ‘ ‘ . $user->last_name;
					if($nameoption=='first' && isset($user->first_name) && $user->first_name!=''){
						$results['reviewer_name'] = $user->first_name;
					} else if($nameoption=='last' && isset($user->last_name) && $user->last_name!=''){
						$results['reviewer_name'] = $user->last_name;
					} else if($nameoption=='firstlast'){
						if((isset($user->last_name) && $user->last_name!='') || (isset($user->first_name) && $user->first_name!='')){
						$results['reviewer_name'] = $user->first_name.' '.$user->last_name;
						}
					}
				} else {
					//this was left by a non-member try to explode name if possible
					//make sure php mb extension is loaded
					if (extension_loaded('mbstring')) {
						$words = mb_split("\s", $user_name);
					} else {
						$words = explode(" ", $user_name);
					}
					if($nameoption=='first'){
						$results['reviewer_name'] = $words[0];
					} else if($nameoption=='last'){
						$results['reviewer_name'] = end($words);
					}
				}
			}
		
		$pageid = $comment->comment_post_ID;
		$results['pageid'] = $pageid;
		$post = get_post( $pageid ); 
		//print_r($post);
		$results['pagename'] = $post->post_title;	//use for the product title
		$results['from_url'] = get_permalink($pageid);
		$results['userpic'] = get_avatar_url( $comment->comment_author_email, 80 );
		$results['rating'] = get_comment_meta( $comment->comment_ID, 'rating', true );
		//if rating not set then we need to use default value here.
		if(!isset($results['rating'])){
			$results['rating'] = '';
		}
		if($results['rating']=='' || $results['rating']==0 || $results['rating']==false){
			if($options['woo_rating_options']!='blank'){
				$results['rating'] = $options['woo_rating_options'];
			}
		}
				
		$unixtimestamp = $this->myStrtotime($comment->comment_date);
		$results['created_time_stamp'] = $unixtimestamp;
		$results['created_time'] = date("Y-m-d H:i:s", $unixtimestamp);
		$results['review_text'] = $comment->comment_content;
		$results['review_length'] = str_word_count($results['review_text']);
		if (extension_loaded('mbstring')) {
			$results['review_length_char'] = mb_strlen($results['review_text']);
		} else {
			$results['review_length_char'] = strlen($results['review_text']);
		}
					
		$hideme = $comment->comment_approved;
		if($hideme==0){
			$results['hide'] = 'yes';
		} else {
			$results['hide'] = 'no';
		}
		//["-107-"],["-18-","-25-"]
		$posts = array();
		$posts[] = "-".intval($comment->comment_post_ID)."-";	//encoding here so we can add more later
		$results['posts'] = json_encode($posts);
		//find cats
		$catidarray = array();
		//woocommerce check 
		$categories = get_the_terms( $pageid, 'product_cat');
		if(is_array($categories)){
			$arrlength = count($categories);
			if($arrlength>0 && $categories){
				for($x = 0; $x < $arrlength; $x++) {
					$catidarray[] = "-".$categories[$x]->term_id."-";	//array containing just the cat_IDs that this post belongs to, dashes added so we can use like search
				}
			}
		}
		$results['categories'] = json_encode($catidarray);
		$results['type'] = 'WooCommerce';
		
		//product image
		$productimage = wp_get_attachment_image_src( get_post_thumbnail_id( $pageid ), 'thumbnail' );
		if(!$productimage){
			$productimage[0] = '';
		}
		$results['miscpic'] = $productimage[0];
					
		//print_r($results);
		return $results;
	}	
	 
	public function srfw_woo_deletecomment($comment){
		global $wpdb;
		$table_name = $wpdb->prefix . 'srfw_reviews';
		$commentinfoarray = $this->srfw_returncommentinfoarray($comment);
		$wpdb->delete( $table_name, array( 'reviewer_id' => $commentinfoarray['reviewer_id'] ) );
		//update badge totals
		//$this->updatetotalavgreviews('woocommerce', $commentinfoarray['pageid'], '','',$commentinfoarray['pagename']);
	}
	
	public function srfw_woo_changestatus($new_status,$old_status,$comment){
		if($new_status=='spam' || $new_status=='trash' || $new_status=='hold'){
			$this->srfw_woo_deletecomment($comment);
		} else{
			//comment approved or unapproved via ajax
			$comment_id = $comment->comment_ID;
			$this->srfw_woo_iud_comment($comment_id);
		}
	}
	
	
	public function srfw_woo_iud_comment($comment_ID,$info=''){

		//echo "comment inserted or updated, get info and insert or update reviews table";
		$comment = get_comment( $comment_ID );
			if(is_object($comment)){
				if($comment->comment_type=="review"){
					global $wpdb;
					$table_name = $wpdb->prefix . 'srfw_reviews';
					
					//get comment data and insert or update below.
					$commentinfoarray = $this->srfw_returncommentinfoarray($comment);

					//if marked as spam, trash or hold them remove from wpprorev db
					if($comment->comment_approved!=1){
						$this->srfw_woo_deletecomment($comment);
					}
					
					//if radio option is set to Approved only and this comment is unapproved then do nothing, $options['woo_sync_all']=='approved'
					$options = get_option('srfw_woo_settings');
					if($options['woo_sync_all']=='approved' && $comment->comment_approved!=1){
						//don't do anything since not syncing unapproved comments
					} else {
						//find out if we need to update or insert
						$checkrow = $wpdb->get_var( "SELECT id FROM ".$table_name." WHERE reviewer_id = '".$commentinfoarray['reviewer_id']."' " );
						if( empty( $checkrow ) ){
							//not in db, insert it
							$insertnum = $wpdb->insert( $table_name, $commentinfoarray );
						} else {
							//is in db, update it.
							$insertnum = $wpdb->update( $table_name, $commentinfoarray,array( 'reviewer_id' => $commentinfoarray['reviewer_id'] ) );
						}
						//update badge totals
						//$this->updatetotalavgreviews('woocommerce', $commentinfoarray['pageid'], '','',$commentinfoarray['pagename']);
					}
				}
			}
	}
	public function srfw_woo_iud_comment_delete($comment_ID,$comment){
		//comment being deleted, delete from our db as well
		$this->srfw_woo_deletecomment($comment);
	}
	//============end woocommerce=========================
	
		
	//fix stringtotime for other languages
	private function myStrtotime($date_string) { 
		$monthnamearray = array(
		'janvier'=>'jan',
		'février'=>'feb',
		'mars'=>'march',
		'avril'=>'apr',
		'mai'=>'may',
		'juin'=>'jun',
		'juillet'=>'jul',
		'août'=>'aug',
		'septembre'=>'sep',
		'octobre'=>'oct',
		'novembre'=>'nov',
		'décembre'=>'dec',
		'gennaio'=>'jan',
		'febbraio'=>'feb',
		'marzo'=>'march',
		'aprile'=>'apr',
		'maggio'=>'may',
		'giugno'=>'jun',
		'luglio'=>'jul',
		'agosto'=>'aug',
		'settembre'=>'sep',
		'ottobre'=>'oct',
		'novembre'=>'nov',
		'dicembre'=>'dec',
		'janeiro'=>'jan',
		'fevereiro'=>'feb',
		'março'=>'march',
		'abril'=>'apr',
		'maio'=>'may',
		'junho'=>'jun',
		'julho'=>'jul',
		'agosto'=>'aug',
		'setembro'=>'sep',
		'outubro'=>'oct',
		'novembro'=>'nov',
		'dezembro'=>'dec',
		'enero'=>'jan',
		'febrero'=>'feb',
		'marzo'=>'march',
		'abril'=>'apr',
		'mayo'=>'may',
		'junio'=>'jun',
		'julio'=>'jul',
		'agosto'=>'aug',
		'septiembre'=>'sep',
		'octubre'=>'oct',
		'noviembre'=>'nov',
		'diciembre'=>'dec',
		'januari'=>'jan',
		'februari'=>'feb',
		'maart'=>'march',
		'april'=>'apr',
		'mei'=>'may',
		'juni'=>'jun',
		'juli'=>'jul',
		'augustus'=>'aug',
		'september'=>'sep',
		'oktober'=>'oct',
		'november'=>'nov',
		'december'=>'dec',
		' de '=>'',
		'dezember'=>'dec',
		'januar '=>'jan ',
		'stycznia'=>'jan',
		'lutego'=>'feb',
		'februar'=>'feb',
		'marca'=>'march',
		'märz'=>'march',
		'kwietnia'=>'apr',
		'maja'=>'may',
		'czerwca'=>'jun',
		'lipca'=>'jul',
		'sierpnia'=>'aug',
		'września'=>'sep',
		'października'=>'oct',
		'listopada'=>'nov',
		'grudnia'=>'dec',
		'february'=>'feb',
		'января'=>'jan',
		'февраля'=>'feb',
		'марта'=>'march',
		'апреля'=>'apr',
		'мая'=>'may',
		'июня'=>'jun',
		'июля'=>'jul',
		'августа'=>'aug',
		'сентября'=>'sep',
		'октября'=>'oct',
		'ноября'=>'nov',
		'декабря'=>'dec',
		'tháng 1,'=>'jan',
		'tháng 2,'=>'feb',
		'tháng 3,'=>'march',
		'tháng 4,'=>'apr',
		'tháng 5,'=>'may',
		'tháng 6,'=>'jun',
		'tháng 7,'=>'jul',
		'tháng 8,'=>'aug',
		'tháng 9,'=>'sep',
		'tháng 10,'=>'oct',
		'tháng 11,'=>'nov',
		'tháng 12,'=>'dec',
		'augusti'=>'aug',
		'Ιανουαρίου'=>'jan',
		'Φεβρουαρίου'=>'feb',
		'Μαρτίου'=>'march',
		'Απριλίου'=>'apr',
		'Μαΐου'=>'may',
		'Ιουνίου'=>'jun',
		'Ιουλίου'=>'jul',
		'Αυγούστου'=>'aug',
		'Σεπτεμβρίου'=>'sep',
		'Οκτωβρίου'=>'oct',
		'Νοεμβρίου'=>'nov',
		'Δεκεμβρίου'=>'dec',
		);
		//echo strtr(strtolower($date_string), $monthnamearray);
		return strtotime(strtr(strtolower($date_string), $monthnamearray)); 
	}
	
	
	/**
	 * displays message in admin if it's been longer than 30 days.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function wprp_admin_notice__success () {

		$activatedtime = get_option('srfw_activated_time_woo');
		//if this is an old install then use 23 days ago
		if($activatedtime==''){
			$activatedtime= time() - (86400*23);
			update_option( 'srfw_activated_time_woo', $activatedtime );
		}
		$thirtydaysago = time() - (86400*30);
		
		//check if an option was clicked on
		if (isset($_GET['srfwnotice'])) {
		  $srfwnotice = sanitize_text_field($_GET['srfwnotice']);
		} else {
		  //Handle the case where there is no parameter
		   $srfwnotice = '';
		}
		if($srfwnotice=='mlater_srfw'){		//hide the notice for another 30 days
			update_option( 'srfw_notice_hide_woo', 'later' );
			$newtime = time() - (86400*21);
			update_option( 'srfw_activated_time_woo', $newtime );
			$activatedtime = $newtime;
			
		} else if($srfwnotice=='notagain_srfw'){		//hide the notice forever
			update_option( 'srfw_notice_hide_woo', 'never' );
		}
		
		$wprev_notice_hide = get_option('srfw_notice_hide_woo');

		if($activatedtime<$thirtydaysago && $wprev_notice_hide!='never'){
		
			$urltrimmedtab = remove_query_arg( array('taction', 'tid', 'sortby', 'sortdir', 'opt') );
			$urlmayberlater = esc_url( add_query_arg( 'srfwnotice', 'mlater_srfw',$urltrimmedtab ) );
			$urlnotagain = esc_url( add_query_arg( 'srfwnotice', 'notagain_srfw',$urltrimmedtab ) );
			
			$temphtml = '<p>Hey, I noticed you\'ve been using my <b>Review Slider for WooCommerce</b> plugin for a while now – that’s awesome! Could you please do me a BIG favor and give it a 5-star rating on WordPress? <br>
			Thanks!<br>
			~ Josh W.<br></p>
			<ul>
			<li><a href="https://wordpress.org/support/plugin/review-slider-for-woocommerce/reviews/#new-post" target="_blank">Ok, you deserve it</a></li>
			<li><a href="'.$urlmayberlater.'">Not right now, maybe later</a></li>
			<li><a href="'.$urlnotagain.'">Don\'t remind me again</a></li>
			</ul>
			<p>P.S. If you\'ve been thinking about upgrading to the <a href="https://wpreviewslider.com/" target="_blank">Pro</a> version, here\'s a 10% off coupon code you can use! ->  <b>wprevpro10off</b></p>';
			$temphtml = wp_kses_post($temphtml);
			?>
			<div class="notice notice-info">
				<div class="wprevpro_admin_notice" style="color: #007500;">
				<?php _e( $temphtml, $this->_token ); ?>
				</div>
			</div>
			<?php
		}

	}
	

 
}
