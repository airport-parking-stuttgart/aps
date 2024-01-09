<?php

/**
 * This class is responsible for making pro feature enabled in    
 */
 
class ReviewXPro_Features {

    private $plugin_name;
    private $version;
    public $theme;
	private $product_title;
	private $product_url;
	private $product_img; 
    public $links;  
    public $class;    

    public function __construct( $plugin_name, $version ) { 

        $this->plugin_name  = $plugin_name;
        $this->version      = $version; 
        
        add_action( 'wp_print_scripts', array( $this, 'denqueue_scripts') );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'public_enqueue_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'public_enqueue_scripts' ) );     
        add_filter( 'rx_allow_video_url', array( $this, 'load_video_url' ) );
        add_filter( 'rx_allow_anonymouse_user', array( $this, 'load_anonymouser_user_checkbox' ) );     
        add_filter( 'rx_multiple_image_upload', array( $this, 'multiple_image_upload' ) ); 
        add_filter( 'rx_save_extra_post_data', array( $this, 'save_review_data' ) );
        add_filter( 'rx_form_field_image', array( $this, 'review_form_field_image' ) );
        add_filter( 'rx_review_edit_button', array( $this, 'load_edit_review_button' ) );
        add_action( 'edit_comment', array( $this, 'reviewx_comment_meta_content_update' ) );
        add_filter( 'print_reviewer_name', array( $this, 'print_reviewer_name' ) );	 
        add_filter( 'rx_pro_feature_overview', array( $this, 'rx_pro_feature_overview' ) );	

        // Display edit review data
        add_action( 'wp_ajax_display_review_content', array( $this, 'run_display_review_content' ) );
        add_action( 'wp_ajax_nopriv_display_review_content', array( $this, 'run_display_review_content' ) ); 
        
        //Update review data
        add_action( 'wp_ajax_update_review_content', array( $this, 'run_update_review_content' ) );
        add_action( 'wp_ajax_nopriv_update_review_content', array( $this, 'run_update_review_content' ) ); 
        add_action( 'wp_head', array( $this, 'reviewx_apply_custom_css'), 100 );
        
        add_filter( 'rx_upvoted_downvoted', array( $this, 'reviewx_upvoted_downvoted_html' ) );
        
        //Update like
        add_action( 'wp_ajax_reviewx_upvoted_downvoted', array( $this, 'reviewx_upvoted_downvoted' ) );
        add_action( 'wp_ajax_nopriv_reviewx_upvoted_downvoted', array( $this, 'reviewx_upvoted_downvoted' ) );

        //Update Dislike
        add_action( 'wp_ajax_reviewx_downvoted', array( $this, 'reviewx_downvoted' ) );
        add_action( 'wp_ajax_nopriv_reviewx_downvoted', array( $this, 'reviewx_downvoted' ) );

        add_filter( 'rx_load_review_graph_template', array( $this, 'load_review_graph_template' ) );	
        add_filter( 'rx_load_review_templates', array( $this, 'load_review_templates' ) );	
        add_filter( 'rx_edit_review_form', array( $this, 'load_edit_review_form' ) );

		// Add Facebook OpenGraph attributes to the HTML tag
		add_filter( 'language_attributes', array( $this, 'add_opengraph_doctype')  );

        if( get_option('_rx_option_allow_share_review') == 1 ) {
            // // Add Twitter Card to the header
            add_action( 'wp_head', array( $this, 'insert_twitter_card'), 5 ); 
            // Add Facebook Meta(s) to the header
		    add_action( 'wp_head', array( $this, 'insert_fb_in_head'), 5  );  
        }        
                
        add_filter( 'rx_social_sharing', array( $this, 'display_social_btns' ) );
        add_filter( 'rx_load_product_rating_type', array( $this, 'load_product_rating_type' ) );
        add_filter( 'comment_form_submit_button', array( $this, 'reviewx_anonymouse_field' ), 10, 2 );

        // Marked as highlighted
        add_action( 'wp_ajax_marked_as_highlight', array( $this, 'run_marked_as_highlight' ) );
        add_action( 'wp_ajax_nopriv_marked_as_highlight', array( $this, 'run_marked_as_highlight' ) ); 

        add_action( 'wp_ajax_review_admin_reply', array( $this, 'run_review_admin_reply' ) );
        add_action( 'wp_ajax_nopriv_review_admin_reply', array( $this, 'run_review_admin_reply' ) );         

        add_action( 'wp_ajax_update_review_admin_reply', array( $this, 'run_update_review_admin_reply' ) );
        add_action( 'wp_ajax_nopriv_update_review_admin_reply', array( $this, 'run_update_review_admin_reply' ) ); 
        
        add_action( 'wp_ajax_delete_review_admin_reply', array( $this, 'run_delete_review_admin_reply' ) );
        add_action( 'wp_ajax_nopriv_delete_review_admin_reply', array( $this, 'run_delete_review_admin_reply' ) );  
        
        add_action( 'wp_loaded', array( $this, 'rx_review_criteria_update') );
        add_filter( 'rx_admin_highlight_comment', array( $this, 'run_reviewx_highlight_comment' ) );

        add_filter( 'rx_admin_manual_verify', array( $this, 'run_rx_admin_manual_verify' ) ); 

        add_action( 'wp_ajax_rx_admin_remove_image', array( $this, 'rx_admin_remove_image') );
        add_action( 'wp_ajax_nopriv_rx_admin_remove_image', array( $this, 'rx_admin_remove_image') );

        add_action( 'wp_ajax_rx_admin_add_image', array( $this, 'rx_admin_add_image') );
        add_action( 'wp_ajax_nopriv_rx_admin_add_image', array( $this, 'rx_admin_add_image') );

        add_action( 'wp_ajax_rx_admin_remove_video', array( $this, 'rx_admin_remove_video') );
        add_action( 'wp_ajax_nopriv_rx_admin_remove_video', array( $this, 'rx_admin_remove_video') );      
        
        add_filter( 'rx_load_shortcode_graph_template', array( $this, 'load_shortcode_graph_template' ) ); 
        add_filter( 'rx_load_shortcode_review_templates', array( $this, 'load_shortcode_review_templates' ), 1, 2 );  
        
        add_action( 'wp_ajax_reviewx_manual_review', array( $this, 'reviewx_manual_review') );
        add_action( 'wp_ajax_load_post_type_wise_criteria', array( $this, 'load_post_type_wise_criteria') );

        add_action( 'wp_ajax_rx_guest_review_video', array( $this, 'rx_guest_review_video') );
        add_action( 'wp_ajax_nopriv_rx_guest_review_video', array( $this, 'rx_guest_review_video') ); 
    }

	/**
	 * Dequeue free js
	 *
	 * @since    1.0.0
	 * @access   public
	 */	
	public function denqueue_scripts() {
		wp_dequeue_script( 'reviewx-select2-js' );
        wp_deregister_script( 'reviewx-select2-js' ); 
        wp_dequeue_script( 'reviewx-admin-script' );
        wp_deregister_script( 'reviewx-admin-script' ); 
	}    

    /**
    * Enqueue pro js for admin
    *
    * @param none
    * @return void
    **/    
    public function admin_enqueue_scripts( $hook ) {
        $is_enable = (
            ! in_array($hook, [
                'toplevel_page_rx-admin',
                'post-new.php',
                'post.php',
                'reviewx_page_rx-wc-settings',
                'reviewx_page_reviewx-all',
                'reviewx_page_reviewx-quick-setup',
                'reviewx_page_reviewx-review-email',   
                'reviewx_page_rx-settings',
                'reviewx_page_rx-add-review',
                'reviewx_page_rx-external-review',
                'reviewx_page_rx-review-import-history',
            ])
        );

        if( $is_enable ){
            return;
        }

        wp_enqueue_style( $this->plugin_name . '-admin-css', REVIEWX_PRO_ADMIN_URL. 'assets/css/reviewx-pro-admin.css', array(), $this->version);
        wp_enqueue_style( $this->plugin_name . '-select2-css', REVIEWX_PRO_ADMIN_URL. 'assets/css/select2.min.css', array(), $this->version);
        wp_enqueue_script($this->plugin_name . '-select2-js', REVIEWX_PRO_ADMIN_URL . 'assets/js/select2.min.js', array('jquery'), $this->version, true);
		wp_enqueue_script( $this->plugin_name . '-admin-script', REVIEWX_PRO_ADMIN_URL . 'assets/js/reviewx-admin.js', array( 'jquery' ), $this->version . time(), true );
		
        $js_filter = $this->wp_localize_script_args($hook);
        wp_localize_script( $this->plugin_name . '-admin-script', 'ajax_admin', $js_filter );	
        
    }

    /**
    * Enqueue pro css for public
    *
    * @param none
    * @return void
    **/     
    public function public_enqueue_styles() {
        wp_enqueue_style( $this->plugin_name, REVIEWX_PRO_PUBLIC_URL . 'assets/css/'.$this->plugin_name.'.css', array(), $this->version, 'all' );        
    }    

    /**
    * Enqueue pro js for public
    *
    * @param none
    * @return void
    **/   
    public function public_enqueue_scripts() {
        wp_enqueue_script( $this->plugin_name . '-script', REVIEWX_PRO_PUBLIC_URL . 'assets/js/'.$this->plugin_name .'-script.js', array(), $this->version, true );
        wp_localize_script( $this->plugin_name . '-script', 'rx_ajax_data',
            array(
                'ajax_url'  => admin_url('admin-ajax.php'),               
            )
        );        
        wp_enqueue_script( 'comment-reply' );
    }   
    
    /**
    * Show video url input field
    *
    * @param array
    * @return array
    **/    
    public function load_video_url( $data ) {
        
        $echo = '';
        if( is_array($data) && isset($data['is_allow_video']) && $data['is_allow_video'] == 1 ) {

            if( $data['signature'] == 1 ) {
                
                $echo .='<div class="rx-form-group rx-margin-bottom10 rx-flex-grid-100">';
                    $echo .='<div class="rx-form-video-element">';
                            $both_display = '';
                            if( $data['video_source'] == 'both' ) {
                                $both_display = 'style="display:block"';
                            } else {
                                $both_display = 'style="display:none"';
                            }
                            
                            $self_selected = $hosted_selected = '';
     
                            if( $data['video_source'] == 'self' ) {
                                $self_selected = 'selected';
                            } else  if( $data['video_source'] == 'external' ) {
                                $hosted_selected = 'selected';
                            }
                            
                            $upload_file_label      = ! empty( get_theme_mod('reviewx_video_upload_first_label') ) ? get_theme_mod('reviewx_video_upload_first_label') : esc_html__( 'Upload File', 'reviewx-pro' );
                            $videl_label            = ! empty( get_theme_mod('reviewx_video_upload_second_label') ) ? get_theme_mod('reviewx_video_upload_second_label') : esc_html__( 'External Link', 'reviewx-pro' );
                            $external_link_url      = ! empty( get_theme_mod('reviewx_video_external_link') ) ? get_theme_mod('reviewx_video_external_link') : 'https://www.youtube.com/watch?v=HhBUmxEOfpc';

                        $echo .='<div class="rx-video-field" '.$both_display.'>
                                    <select id="rx-video-source-control" name="rx-video-source-control" class="form-control rx-margin-bottom10">                                    
                                        <option value="self" '.$self_selected.'>'.$upload_file_label.'</option>
                                        <option value="external" '.$hosted_selected.'>'.$videl_label.'</option>
                                    </select>	
                                    <span class="rx-selection-arrow"><b></b></span>
                                </div>';
                            $echo .='<div class="rx-video-field">';
                                $self_display = '';
                                if( $data['video_source'] == 'self' || $data['video_source'] == 'both' ) {
                                    $self_display = 'style="display:block"';
                                } else {
                                    $self_display = 'style="display:none"';
                                }                             
                                $echo .='<a class="rx-popup-video" id="rx-show-video-preview" href="">
                                        <img src="'.esc_url( assets("storefront/images/video-icon.png") ).'" alt="'.esc_attr__('ReviewX', 'reviewx-pro' ).'">
                                    </a>   
                                    <div class="rx-self-video" id="rx-self-video" '.$self_display.'>
                                        <label class="rx_upload_video">
                                            <input name="rx-upload-video" id="rx-upload-video" class="submit rx-form-btn rx-form-primary-btn rv-btn" onclick="rx_upload_video()" class="" value="Upload Video" type="button">		
                                            <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 66 37" style="enable-background:new 0 0 66 37;" xml:space="preserve"> <path class="st0" d="M63.8,1.9l-16.5,9.9V5.2C47.3,2.3,45,0,42.1,0H5.2C2.3,0,0,2.3,0,5.2v26.7C0,34.7,2.3,37,5.2,37h36.9 c2.9,0,5.2-2.3,5.2-5.2v-6.2l16.5,9.5c0.9,0.6,2-0.1,2-1.1V3.1C65.8,2,64.7,1.4,63.8,1.9z"/></svg>
                                            <span>'.esc_html__( 'Upload a video', 'reviewx-pro').'</span>
                                        </label>
                                    </div>';
                                    $ext_display = '';
                                    if( $data['video_source'] == 'external' ){
                                        $ext_display = 'style="display:block;margin-top: 10px;"';
                                    }                                    								
                                $echo .='<div class="rx-external-video-url" id="rx-external-video-url" '.$ext_display.'>
                                            <input type="text" name="rx-set-video-url" id="rx-set-video-url" class="form-control rx-margin-bottom10 review-box" placeholder="'.esc_attr__('Video URL', 'reviewx-pro' ).'" title="'.esc_attr__('E.g.: ', 'reviewx-pro' ).esc_url('https://www.youtube.com/watch?v=HhBUmxEOfpc').'">
                                        </div>';						
                                $echo .='<input type="hidden" name="rx-video-url" id="rx-video-url">';
                            $echo .='</div>';
                        $echo .='</div>';	
                    $echo .='<div class="rx-note-video" id="rx-note-external-video" '.$ext_display.'>'.esc_html__('E.g.: ', 'reviewx-pro' ).esc_url($external_link_url).'</div>';                    
                $echo .='</div>';
            } elseif ( $data['signature'] == 2 ) {

                $echo .='<div class="rx-form-group rx-margin-bottom10 rx-flex-grid-100">';                                      
                    $echo .='<div class="rx-form-video-element">';
                    
                    $both_display = '';
                    if( $data['video_source'] == 'both' ) {
                        $both_display = 'style="display:block"';
                    } else {
                        $both_display = 'style="display:none"';
                    } 
                    
                    $self_selected = $hosted_selected = '';
  
                    if( $data['video_source'] == 'self' ) {
                        $self_selected = 'selected';
                    } else  if( $data['video_source'] == 'external' ) {
                        $hosted_selected = 'selected';
                    } 
                    
                    $upload_file_label      = ! empty( get_theme_mod('reviewx_video_upload_first_label') ) ? get_theme_mod('reviewx_video_upload_first_label') : esc_html__( 'Upload File', 'reviewx-pro' );
                    $videl_label            = ! empty( get_theme_mod('reviewx_video_upload_second_label') ) ? get_theme_mod('reviewx_video_upload_second_label') : esc_html__( 'External Link', 'reviewx-pro' );
                    $external_link_url      = ! empty( get_theme_mod('reviewx_video_external_link') ) ? get_theme_mod('reviewx_video_external_link') : 'https://www.youtube.com/watch?v=HhBUmxEOfpc';                    

                    $echo .='<div class="rx-video-field" '.$both_display.'>
                                <select id="rx-edit-video-source-control" name="rx-edit-video-source-control" class="form-control rx-margin-bottom10">
                                    <option value="self" '.$self_selected.'>'.$upload_file_label.'</option>
                                    <option value="external" '.$hosted_selected.'>'.$videl_label.'</option>                                    
                                </select>
                                <span class="rx-selection-arrow"><b></b></span>
                            </div>';
                        $echo .='<div class="rx-video-field">';
                        $self_display = '';
                        if( $data['video_source'] == 'self' || $data['video_source'] == 'both' ) {
                            $self_display = 'style="display:block"';
                        } else {
                            $self_display = 'style="display:none"';
                        }                     
                        $echo .='<a class="rx-popup-video" id="rx-edit-video-preview" href="">
                                    <img src="'.esc_url( assets('storefront/images/video-icon.png' ) ).'" style="margin:0" alt="'.esc_attr__('ReviewX', 'reviewx-pro' ).'">
                                </a>  
                                <div class="rx-edit-self-video" id="rx-edit-self-video" '.$self_display.'>
                                    <label class="rx_upload_video">
                                        <input name="rx-edit-upload-video" id="rx-edit-upload-video" class="rx-form-btn rx-form-primary-btn rv-btn" onclick="rx_edit_upload_video()"class="" value="Upload Video" type="button">		
                                        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 66 37" style="enable-background:new 0 0 66 37;" xml:space="preserve"><path class="st0" d="M63.8,1.9l-16.5,9.9V5.2C47.3,2.3,45,0,42.1,0H5.2C2.3,0,0,2.3,0,5.2v26.7C0,34.7,2.3,37,5.2,37h36.9 c2.9,0,5.2-2.3,5.2-5.2v-6.2l16.5,9.5c0.9,0.6,2-0.1,2-1.1V3.1C65.8,2,64.7,1.4,63.8,1.9z"/></svg>
                                        <span>'.esc_html__( 'Upload a video', 'reviewx-pro').'</span>
                                    </label>
                                </div>';
                                $ext_display = '';
                                if( $data['video_source'] == 'external' ){
                                    $ext_display = 'style="display:block;margin-top:0px;"';
                                }                                   								
                        $echo .='<div class="rx-edit-external-video-url" id="rx-edit-external-video-url" '.$ext_display.'>
                                    <input type="text" name="rx-set-edit-video-url" id="rx-set-edit-video-url" class="form-control rx-margin-bottom10 review-box" placeholder="'.esc_attr__('Video URL', 'reviewx-pro' ).'" title="'.esc_attr__('E.g.: ', 'reviewx-pro' ).esc_url('https://www.youtube.com/watch?v=HhBUmxEOfpc').'">                                    
                                </div>';
                        $echo .='<input type="hidden" name="rx-edit-video-url" id="rx-edit-video-url">';
                    $echo .='</div>';
                    $echo .='</div>';                        
                    $echo .='<div class="rx-note-video" id="rx-note-edit-external-video" '.$ext_display.'>'.esc_html__('E.g.: ', 'reviewx-pro' ).esc_url($external_link_url).'</div>';
                $echo .='</div>';

            } elseif( $data['signature'] == 3 ) {

				$echo .='<div class="rx-form-group rx-margin-bottom10 rx-flex-grid-100">';                                       
                    $echo .='<div class="rx-form-video-element">';
                        $both_display = '';
                        if( $data['video_source'] == 'both' ) {
                            $both_display = 'style="display:block"';
                        } else {
                            $both_display = 'style="display:none"';
                        }
                        $self_selected = $hosted_selected = '';
                        
                        if( $data['video_source'] == 'self' ) {
                            $self_selected = 'selected';
                        } else  if( $data['video_source'] == 'external' ) {
                            $hosted_selected = 'selected';
                        } 

                        $upload_file_label      = ! empty( get_theme_mod('reviewx_video_upload_first_label') ) ? get_theme_mod('reviewx_video_upload_first_label') : esc_html__( 'Upload File', 'reviewx-pro' );
                        $videl_label            = ! empty( get_theme_mod('reviewx_video_upload_second_label') ) ? get_theme_mod('reviewx_video_upload_second_label') : esc_html__( 'External Link', 'reviewx-pro' );
                        $external_link_url      = ! empty( get_theme_mod('reviewx_video_external_link') ) ? get_theme_mod('reviewx_video_external_link') : 'https://www.youtube.com/watch?v=HhBUmxEOfpc';                          

                        $echo .='<div class="rx-video-field" '.$both_display.'>
                                    <select id="rx-video-source-control" name="rx-video-source-control" class="form-control rx-margin-bottom10">
                                        <option value="self" '.$self_selected.'>'.$upload_file_label.'</option>
                                        <option value="external" '.$hosted_selected.'>'.$videl_label.'</option>                                           
                                    </select>	
                                    <span class="rx-selection-arrow"><b></b></span>
                                </div>';
                        $echo .='<div class="rx-video-field">';
                            $self_display = '';
                            if( $data['video_source'] == 'self' || $data['video_source'] == 'both' ) {
                                $self_display = 'style="display:block"';
                            } else {
                                $self_display = 'style="display:none"';
                            }
                            $echo .='<a class="rx-popup-video" id="rx-show-video-preview" href="">
                                        <img src="'.esc_url( assets('storefront/images/video-icon.png' ) ).'" style="margin:0" alt="'.esc_attr__('ReviewX', 'reviewx-pro' ).'">
                                    </a>';                             
                            $echo .='<div class="rx-self-video" id="rx-self-video" '.$self_display.'>
                                        <label class="rx_upload_video">';
                                        if( is_user_logged_in() && current_user_can('edit_posts')) {                                        
                                            $echo .='<input name="rx-upload-video" id="rx-upload-video" class="submit rx-form-btn rx-form-primary-btn rv-btn" onclick="rx_upload_video()" value="Upload Video" type="button">';
                                        } else {
                                            $echo .='<input type="file" name="non-logged-rx-upload-video" id="non-logged-rx-upload-video" accept="video/mp4,video/x-m4v,video/*" value="Upload Video">';    
                                        }
                                        $echo .='<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 66 37" style="enable-background:new 0 0 66 37;" xml:space="preserve"><path class="st0" d="M63.8,1.9l-16.5,9.9V5.2C47.3,2.3,45,0,42.1,0H5.2C2.3,0,0,2.3,0,5.2v26.7C0,34.7,2.3,37,5.2,37h36.9 c2.9,0,5.2-2.3,5.2-5.2v-6.2l16.5,9.5c0.9,0.6,2-0.1,2-1.1V3.1C65.8,2,64.7,1.4,63.8,1.9z"/></svg>
                                            <span>'.esc_html__( 'Upload a video', 'reviewx-pro').'</span>
                                        </label>
                                    </div>';
                            if( !is_user_logged_in() ) {
                                $echo .= '<p class="rx-guest-attachment-error"></p>';
                            }                            
                            $echo .='<div class="rx_content_loader">
                                        <div class="rx_image_upload_spinner">
                                            <div></div>
                                            <div>
                                                <div></div>
                                            </div>
                                        </div>
                                    </div>';        
                                                                    
                            $ext_display = '';
                            if( $data['video_source'] == 'external' ){
                                $ext_display = 'style="display:block"';
                            }        
                            $echo .='<div class="rx-external-video-url" id="rx-external-video-url" '.$ext_display.'>
                                        <input type="text" name="rx-set-video-url" id="rx-set-video-url" class="form-control rx-margin-bottom10 review-box" placeholder="'.esc_attr__('Video URL', 'reviewx-pro' ).'" title="'.esc_attr__('E.g.: ', 'reviewx-pro' ).esc_url('https://www.youtube.com/watch?v=HhBUmxEOfpc').'">
                                    </div>';						
                            $echo .='<input type="hidden" name="rx-video-url" id="rx-video-url">';
                        $echo .='</div>';                        
                $echo .='</div>';	
                $echo .='<div class="rx-note-video" id="rx-note-external-video" '.$ext_display.'>'.esc_html__('E.g.: ', 'reviewx-pro' ).esc_url($external_link_url).'</div>';
                $echo .='</div>';

            }
        }
        echo $echo;        

    }

    /**
    * Show anonymouse user checkbox field
    *
    * @param array
    * @return array
    **/    
    public function load_anonymouser_user_checkbox( $data ) {

        $echo = '';
        if( is_array($data) && $data['is_anonymouse'] == 1 ){
        
            if( $data['signature'] == 1 ) { //add review
            
                $echo .='<div class="rx-flex-grid-100 rx_add_review rx_anonymouse_section">';
                    $echo .='<label class="review_anonymouse_label">';
					$echo .='<input type="checkbox" name="rx-anonymouse-user" id="rx-anonymouse-user" value=1>';
					$echo .='<span class="review_anonymouse_icon"></span>';
                    $echo .= !empty(get_theme_mod('reviewx_anonymously_label') ) ? get_theme_mod('reviewx_anonymously_label') : esc_html__( 'Review anonymously ', 'reviewx-pro' );
                    $echo .='</label>';
                $echo .='</div>';	
            
            } elseif( $data['signature'] == 2 ) { //edit review
            
                $echo .='<div class="rx-flex-grid-100 rx_edit_review rx_anonymouse_section">';
                    $echo .='<label class="review_anonymouse_label">';
					$echo .='<input type="checkbox" name="rx-edit-anonymouse-user" id="rx-edit-anonymouse-user" value=1>';
					$echo .='<span class="review_anonymouse_icon"></span>';
                    $echo .= !empty(get_theme_mod('reviewx_anonymously_label') ) ? get_theme_mod('reviewx_anonymously_label') : esc_html__( 'Review anonymously ', 'reviewx-pro' );
                    $echo .='</label>';
                    
                $echo .='</div>';	
            
            } elseif( $data['signature'] == 3 ) { //public review
                $echo .='<div class="review_anonymouse rx_anonymouse_section">';
                    $echo .='<label class="review_anonymouse_label">';
                        $echo .='<input type="checkbox" name="rx-anonymouse-user" id="rx-anonymouse-user" value=1>';
                        $echo .='<span class="review_anonymouse_icon"></span>';
                        $echo .= !empty(get_theme_mod('reviewx_anonymously_label') ) ? get_theme_mod('reviewx_anonymously_label') : esc_html__( 'Review anonymously ', 'reviewx-pro' );
                    $echo .='</label>';
                $echo .='</div>';              
            } 

        }

        if( isset($data['allow_media_compliance']) && ( $data['allow_media_compliance'] == 1 ) ){
            $echo .='<div class="review_media_compliance">';
                $echo .='<label class="review_media_compliance_label">';                     
                $echo .='<label class="review_media_compliance_label">';                      

                    $echo .='<input type="checkbox" name="rx_allow_media_compliance" id="rx_allow_media_compliance" value=1>';
                    $echo .='<span class="review_media_compliance_icon"></span>';
                    $echo .= !empty(get_theme_mod('reviewx_media_compliance') ) ? html_entity_decode(get_theme_mod('reviewx_media_compliance')) : esc_html__( 'I agree to the terms of services.', 'reviewx-pro' );
                    $echo .='<label id="rx_allow_media_compliance-error" class="error" for="rx_allow_media_compliance" style="display: none;"></label>';
                $echo .='</label>';
                $echo .='<span class="rx-error" id="rx-media-compliance-error"></span>';
            $echo .='</div>';          
        }
        
        echo $echo; 
    }
    
    /**
    * Enable multiple image upload
    *
    * @param array
    * @return void
    **/     
    public function multiple_image_upload( $data ) {
        
        if( $data == 1 ) {
            return 'data-multiple="true"';
        } else {
            return 'multiple';
        } 
               
    }

    /**
    * Save post data
    *
    * @param array
    * @return void
    **/             
    public function save_review_data( $posts ) {

        if( is_array( $posts ) ) {
            $comment_id     = intval($posts['comment_id']);
            $post           = $posts['post_data'];
            $signature      = $posts['signature'];
            
            $controller = $video_url = '';
            if( 1 === $signature ) { // This request is comming from my order

                for( $i=0; $i < count($post); $i++ ) {
                    if( $post[$i]['name'] == 'rx-anonymouse-user' ):
                        add_comment_meta( $comment_id, 'reviewx_anonymouse_user', sanitize_text_field( $post[$i]['value'] ), false );
                    endif;
    
                    if( $post[$i]['name'] == 'rx-video-url' ):                    
                        $video_url = esc_url( $post[$i]['value'] );
                    endif;
                    if( $post[$i]['name'] == 'rx-video-source-control' ):  
                        $controller =   sanitize_text_field( $post[$i]['value'] ); 
                        
                        if( empty($controller) ) {
                            $controller = 'self';
                        }
                        
                        if( !empty($video_url) ) {
                            add_comment_meta( $comment_id, 'reviewx_video_url', $video_url, false );
                            add_comment_meta( $comment_id, 'reviewx_video_source_control', $controller, false );
                        }                                       
                    endif;
                }                 

            } else if( 2 === $signature ) { // This request is comming from frontend (guest/logged in frontend)

                if( isset( $post[ 'rx-anonymouse-user' ] ) ) {
                    add_comment_meta( $comment_id, 'reviewx_anonymouse_user', sanitize_text_field( $post[ 'rx-anonymouse-user' ] ), false );
                }

                if( isset( $post[ 'rx-video-source-control' ]) ) {
                    $controller = $post[ 'rx-video-source-control' ];
                }

                if( isset( $post[ 'rx-video-url' ]) ) {
                    $video_url = $post[ 'rx-video-url' ];
                }

                if( empty($controller) ) {
                    $controller = 'self';
                }

                if( !empty($video_url) ) {
                    update_comment_meta( $comment_id, 'reviewx_video_url', $video_url, false );
                    update_comment_meta( $comment_id, 'reviewx_video_source_control', $controller, false );                    
                }

            } else if( 3 == $signature ){

                for( $i=0; $i < count($post); $i++ ) {
                    //Manual review
                    if( $post[$i]['name'] == 'manual_review_video_url' ):                    
                        $video_url = esc_url( $post[$i]['value'] );
                    endif;

                    //Manual review
                    if( $post[$i]['name'] == 'manual_review_video_source' ):                    
                        $controller =   sanitize_text_field( $post[$i]['value'] ); 
                        
                        if( empty($controller) ) {
                            $controller = 'self';
                        }
                        
                        if( !empty($video_url) ) {
                            add_comment_meta( $comment_id, 'reviewx_video_url', $video_url, false );
                            add_comment_meta( $comment_id, 'reviewx_video_source_control', $controller, false );
                        }    
                    endif;
                }
            }	            
        }

    }
    
    /**
    * Enable multiple image upload in my order table
    *
    * @param int
    * @return void
    **/     
    public function review_form_field_image( $data ) {
        
        $echo = '';
        if( is_array($data) && isset( $data['is_allow_img'] ) && 1 == $data['is_allow_img'] ) {

            if( $data['signature'] == 1 ) {

                $echo .= '<div class="rx-images rx-flex-grid-100" id="rx-images">';
                    $echo .= '<div class="rx-comment-form-attachment">';
                        $echo .= '<label class="rx_upload_file rx-form-btn" for="rx-upload-photo">';
                            $echo .= '<input name="rx-upload-photo" id="rx-upload-photo" class="submit rx-form-btn rx-form-primary-btn rv-btn" type="button" data-multiple="true">';
                            $echo .= '<img src="'.esc_url(plugins_url( '/', __FILE__ ) . '../../public/assets/images/image.svg' ).'" class="img-fluid">';
                            $echo .= '<span>'.esc_html__( 'Upload images', 'reviewx-pro').'</span>';
                        $echo .= '</label>';
                    $echo .= '</div>';
                $echo .= '</div>';

                
            } else if( $data['signature'] == 2 ) {

                $echo .= '<div class="rx-flex-grid-100 rx-edit-images" id="rx-edit-images">';
                    $echo .= '<div class="rx-comment-form-attachment">';
                        $echo .= '<label class="rx_upload_file rx-form-btn">';
                        $echo .= '<input name="rx-edit-photo" id="rx-edit-photo" class="submit rx-form-btn rx-form-primary-btn rv-btn" value="'.esc_html__( 'Upload Photo', 'reviewx-pro' ).'" type="button" data-multiple="true">';
                        $echo .= '<img src="'.esc_url(plugins_url( '/', __FILE__ ) . '../../public/assets/images/image.svg' ).'" class="img-fluid">';
                        $echo .= '<span>'.esc_html__( 'Upload images', 'reviewx-pro').'</span>';
                        $echo .= '</label>';
                    $echo .= '</div>';                
                $echo .= '</div>';
                
            }

        }
        echo $echo;

    }
    
    /**
    * Load review edit form
    *
    * @param array
    * @return void
    **/     
    public function load_edit_review_button( $data ) {
        
        $echo = '';
        if( is_array( $data ) ) {
            $order              = $data['order'];
            $prod_id            = $data['prod_id'];
            $order_status       = $data['order_status'];
            $order_id           = $data['order_id'];
            $order_url          = $data['order_url'];
            $prod_url           = $data['prod_url'];
            $prod_img           = $data['prod_img'];
            $prod_name          = $data['prod_name'];
            $prod_qty           = $data['prod_qty'];
            $prod_img           = $data['prod_img'];
            $prod_price         = $data['prod_price'];
            $review_id          = $data['review_id'];

            $echo .='<p class="padding_5">';            
                $echo .='<a class="rx_my_account_edit_review rx-btn btn-primary btn-sm rx-form-primary-btn btn-review review-off btn-info rx-order-btn rx-edit-btn" product_id ='.$prod_id.' order='.$order.' order_status="'.$order_status.'" order_id='.$order_id.' order_url='.esc_url( $order_url ).' product_url='.$prod_url.' product_img='.$prod_img.' product_name='.$prod_name.' product_quantity='.$prod_qty.' product_price='.$prod_price.' review_id='.$review_id.'>'.esc_html__('Edit review', 'reviewx-pro').'</a>';
            $echo .='</p>';
        }         
        echo $echo; 

    }

    /**
    * Display edit review data
    *
    * @param array
    * @return void
    **/  
    public function run_display_review_content(){

        global $wpdb;
        $data 			        = array();
        $img_arr     	        = array();
        $rx_comment 	        = $wpdb->prefix .'comments';
        $review_id 		        = $_POST['review_id'];

        $rx_comment_content     = $wpdb->prepare("SELECT comment_ID, comment_content FROM $rx_comment WHERE comment_ID = %d",$review_id);
        $query_edit_review 		= $wpdb->get_results($rx_comment_content, ARRAY_A);
        $get_comment_id         = $query_edit_review[0]['comment_ID'];

        $data['message'] 		= $query_edit_review[0]['comment_content'];        
        $data['rx_title']       = get_comment_meta( $get_comment_id, 'reviewx_title', true );
        
        $video_source           = get_option( '_rx_option_video_source' );
        if( isset( $video_source ) && $video_source == 'both' ) {
            $data['rx_video_source_control'] = get_comment_meta( $get_comment_id, 'reviewx_video_source_control', true );
        } else if( ! isset( $video_source ) ) {
            $data['rx_video_source_control'] = get_comment_meta( $get_comment_id, 'reviewx_video_source_control', true );
        } else {
            $data['rx_video_source_control'] = $video_source;
        }
        
        $data['video_url']      = get_comment_meta( $get_comment_id, 'reviewx_video_url', true );
        $data['is_recommended'] = get_comment_meta( $get_comment_id, 'reviewx_recommended', true );
        $data['is_anonymously'] = get_comment_meta( $get_comment_id, 'reviewx_anonymouse_user', true );
        $get_attachment_meta    = (array)get_comment_meta( $get_comment_id, 'reviewx_attachments', true );
        $data['review_status']  = wp_get_comment_status( $get_comment_id );
        if($get_attachment_meta){
        foreach ( $get_attachment_meta['images'] as $comment_gallery_id_val ) {
            $img_url            = wp_get_attachment_image_src($comment_gallery_id_val);
            $full_img_url       = wp_get_attachment_image_src($comment_gallery_id_val, 'full');
            $image = '
                    <div class="rx-edit-image">
                        <img class="disp1" src="'.esc_url( $img_url[0] ).'" alt="">
                            <a href="javascript:void(0);" class="remove_edit_image" title="'.esc_attr__('Remove Image', 'reviewx-pro').'">
                                <svg style="width: 15px" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="times-circle" class="svg-inline--fa fa-times-circle fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm121.6 313.1c4.7 4.7 4.7 12.3 0 17L338 377.6c-4.7 4.7-12.3 4.7-17 0L256 312l-65.1 65.6c-4.7 4.7-12.3 4.7-17 0L134.4 338c-4.7-4.7-4.7-12.3 0-17l65.6-65-65.6-65.1c-4.7-4.7-4.7-12.3 0-17l39.6-39.6c4.7-4.7 12.3-4.7 17 0l65 65.7 65.1-65.6c4.7-4.7 12.3-4.7 17 0l39.6 39.6c4.7 4.7 4.7 12.3 0 17L312 256l65.6 65.1z"></path></svg>
                            </a>
                        <input type="hidden" name="rx-edit-image" value="'.esc_attr($comment_gallery_id_val).'"></div>';
            array_push($img_arr, $image);
            $data['images']	 = $img_arr;
        }
    }
        
        // check multicriteria enable
        if( \ReviewX_Helper::is_multi_criteria( 'product' ) ) {
            $get_rating = get_comment_meta( $get_comment_id, 'reviewx_rating', true );
        } else {
            $get_rating = get_comment_meta( $get_comment_id, 'rating', true );
        }

        $rx_reting = '
        <table class="rx-criteria-table reviewx-rating">
            <tbody>';
                $rx_reting .= $this->get_rating_data($get_rating);
                $rx_reting .='
            </tbody>
        </table>';
        $data['rating'] = $rx_reting;        
        wp_send_json($data);
    }
    
    /**
    * Return criteria label by criteria key
    *
    * @param array
    * @return string
    **/    
    public function get_rating_key_label( $criteria, $unikey ) {
         
        if( is_array( $criteria ) ) {
            foreach( $criteria as $key => $val ) {
                if( $unikey == $key ) {
                    return $val;
                }
            }
        }
    }
    
    /**
    * Load criteria in edit review form
    *
    * @param array
    * @return void
    **/      
    public function get_rating_data( $get_rating ) {
        $multicriteria = get_option( '_rx_option_review_criteria' );
        $rating_type   = get_option( '_rx_option_rating_style' );
        
        $html = "";

        if( !\ReviewX_Helper::is_multi_criteria( 'product' ) ) {
            $star5 = $get_rating == 5 ? 'checked="checked"' : '';
            $star4 = $get_rating == 4 ? 'checked="checked"' : '';
            $star3 = $get_rating == 3 ? 'checked="checked"' : '';
            $star2 = $get_rating == 2 ? 'checked="checked"' : '';
            $star1 = $get_rating == 1 ? 'checked="checked"' : '';
            
            $html .= "<tr>";
                $html .= "<td></td>";
                $html .= "<td>";
                    $html .= "<div class='reviewx-star-rating rx-star-edit-default'>";
                        $html .= "<fieldset class='rx_star_rating'>";
                            $html .= '<input type="radio" id="rx-edit-default-star5" name="rx-edit-default-star" value="5" '.$star5.'/>';
                            $html .= '<label for="rx-edit-default-star5" title="'.esc_attr__('Outstanding', 'reviewx-pro' ).'">';
                                $html .= '<svg class="icon icon-star">';
                                    $html .= '<use xlink:href="#rx_starIcon" />';
                                $html .= '</svg>';
                            $html .= '</label>';
                            $html .= '<input type="radio" id="rx-edit-default-star4" name="rx-edit-default-star" value="4" '.$star4.'/>';
                            $html .= '<label for="rx-edit-default-star4" title="'.esc_attr__('Very Good', 'reviewx-pro' ).'">';
                                $html .= '<svg class="icon icon-star">';
                                    $html .= '<use xlink:href="#rx_starIcon" />';
                                $html .= '</svg>';
                            $html .= '</label>';
                            $html .= '<input type="radio" id="rx-edit-default-star3" name="rx-edit-default-star" value="3" '.$star3.'/>';
                            $html .= '<label for="rx-edit-default-star3" title="'.esc_attr__('Good', 'reviewx-pro' ).'">';
                                $html .= '<svg class="icon icon-star">';
                                    $html .= '<use xlink:href="#rx_starIcon" />';
                                $html .= '</svg>';
                            $html .= '</label>';
                            $html .= '<input type="radio" id="rx-edit-default-star2" name="rx-edit-default-star" value="2" '.$star2.'/>';
                            $html .= '<label for="rx-edit-default-star2" title="'.esc_attr__('Poor', 'reviewx-pro' ).'">';
                                $html .= '<svg class="icon icon-star">';
                                    $html .= '<use xlink:href="#rx_starIcon" />';
                                $html .= '</svg>';
                            $html .= '</label>';
                            $html .= '<input type="radio" id="rx-edit-default-star1" name="rx-edit-default-star" value="1" '.$star1.'/>';
                            $html .= '<label for="rx-edit-default-star1" title="'.esc_attr__('Very Poor', 'reviewx-pro' ).'">';
                                $html .= '<svg class="icon icon-star">';
                                    $html .= '<use xlink:href="#rx_starIcon" />';
                                $html .= '</svg>';
                            $html .= '</label>';                         
                        $html .= "</fieldset>";
                    $html .= "</div>";
                    $html .="<script type='application/javascript'>";
                        $html .="jQuery(document).ready(function () {";
                            $html .="jQuery(document).on('change', '.rx-star-edit-default input', function(){";                                 
                                $html .="jQuery(this).attr('checked', true);";
                            $html .="});";
                        $html .="});";
                    $html .= "</script>";
                $html .= "</td>";
            $html .= "</tr>"; 
        } else {
            if( $rating_type == "rating_style_one" ) {

                foreach( $multicriteria as $key => $label ) {
                    $value = $get_rating[$key];
                    $key = esc_attr( $key );
                    $html .= "<tr>";
                    $html .= "<td>{$label}</td>";
                    $html .= "<td>";
                    $html .= "<div class='reviewx-star-rating rx-star-{$key}'>";
                    $html .= "<fieldset class='rx_star_rating'>";
    
                    for($ratingUpperValue = 5; $ratingUpperValue > 0; $ratingUpperValue--) {
                        $id = "{$key}{$ratingUpperValue}";
                        $checked = ($value == $ratingUpperValue) ? 'checked="checked"' : '';
    
                        $html .="<svg xmlns='http://www.w3.org/2000/svg' style='display: none;'>
                                    <symbol id='rx_starIcon' viewBox='0 0 24 24'>
                                        <polygon stroke='#ffb439' points='12,0 15.708,7.514 24,8.718 18,14.566 19.416,22.825 12,18.926 4.583,22.825 6,14.566 0,8.718 8.292,7.514'/>
                                    </symbol>
                                </svg>";
                        $html .= "<input type='radio' id='{$id}' name='{$key}' value='{$ratingUpperValue}' $checked />";
                        $html .= "<label for='{$id}'><svg class='icon icon-star'><use xlink:href='#rx_starIcon' /></svg></label>";
                    }
    
                    $html .= "</fieldset>";
                    $html .= "</div>";
                    $html .="<script type='application/javascript'>";
                    $html .="jQuery(document).ready(function () {";
                    $html .="jQuery('.rx-star-{$key} input').change(function () {";
                    $html .="var radio = jQuery(this);";
                    $html .="jQuery('.rx-star-{$key} .selected').removeClass('selected');";
                    $html .="radio.closest('label').addClass('selected');";
                    $html .="jQuery(this).attr('checked', true);";
                    $html .="});";
                    $html .="});";
                    $html .= "</script>";
                    $html .= "</td>";
                    $html .= "</tr>";
                }
    
            } else if( $rating_type == "rating_style_two" ) {
                foreach( $multicriteria as $key => $label ) {
                    $value = $get_rating_criteria[$key];
                    $key = esc_attr( $key );
                    $html .= "<tr>";
                    $html .= "<td>{$label}</td>";
                    $html .= "<td>";
                    $html .= "<div class='reviewx-thumbs-rating rx-star-{$key}'>";
                    $html .= "<fieldset>";
    
                    $checked = ($value == 5) ? 'checked="checked"' : ''; 
                    $fas2 = ($value == 5) ? 'fas' : ''; 
                    $html .="<div class='rx_thumbs_up_icon'>";
                        $html .= "<input type='radio' id='{$key}-edit-thumbs-up5' name='{$key}' value='5' $checked/>";
                        $html .= "<label for='{$key}-edit-thumbs-up5'>";
                            $html .= "<svg width='25px' height='25px' version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px'
                                         viewBox='0 0 80 80' style='enable-background:new 0 0 80 80;' xml:space='preserve'>
                                        <g>
                                            <g>
                                                <path d='M5.8,77.2h8.3c3.2,0,5.8-2.6,5.8-5.8V33.1c0-3.2-2.6-5.8-5.8-5.8H5.8c-3.2,0-5.8,2.6-5.8,5.8v38.3
                                                    C0,74.6,2.6,77.2,5.8,77.2L5.8,77.2z M5.8,77.2'/>
                                                <path d='M42.6,3.1c-3.3,0-5,1.7-5,10c0,7.9-7.7,14.3-12.6,17.6v41.3c5.3,2.5,16,6.1,32.6,6.1h5.3c6.5,0,12-4.7,13.1-11.1l3.7-21.7
                                                    c1.4-8.2-4.9-15.6-13.1-15.6H50.9c0,0,2.5-5,2.5-13.3C53.4,6.4,45.9,3.1,42.6,3.1L42.6,3.1z M42.6,3.1'/>
                                            </g>
                                        </g>
                                    </svg>";
                        $html .= "</label>";
                    $html .='</div>';                
    
                    $checked = ($value == 1) ? 'checked="checked"' : '';
                    $fas = ($value == 1) ? 'fas' : '';
                    $html .="<div class='rx-rating-type-display'>";
                        $html .= "<input type='radio' id='{$key}-edit-thumbs-down1' name='{$key}' value='1' $checked/>";
                        $html .= "<label for='{$key}-edit-thumbs-down1'>";
                            $html .= "<svg width='25px' height='25px' viewBox='0 0 82 77' version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'>
                                            <g id='Page-1' stroke='none' stroke-width='1' fill='none' fill-rule='evenodd'>
                                                <g id='rx_dislike' transform='translate(41.000000, 38.000000) scale(-1, 1) translate(-41.000000, -38.000000) translate(1.000000, 0.000000)' stroke='#000000'>
                                                    <g transform='translate(40.000000, 38.000000) scale(-1, 1) rotate(-180.000000) translate(-40.000000, -38.000000) '>
                                                        <path d='M5.8,74.2 L14.1,74.2 C17.3,74.2 19.9,71.6 19.9,68.4 L19.9,30.1 C19.9,26.9 17.3,24.3 14.1,24.3 L5.8,24.3 C2.6,24.3 0,26.9 0,30.1 L0,68.4 C0,71.6 2.6,74.2 5.8,74.2 Z' id='Path' fill='#000000'></path>
                                                        <path d='M42.6,0.1 C39.3,0.1 37.6,1.8 37.6,10.1 C37.6,18 29.9,24.4 25,27.7 L25,69 C30.3,71.5 41,75.1 57.6,75.1 L62.9,75.1 C69.4,75.1 74.9,70.4 76,64 L79.7,42.3 C81.1,34.1 74.8,26.7 66.6,26.7 L50.9,26.7 C50.9,26.7 53.4,21.7 53.4,13.4 C53.4,3.4 45.9,0.1 42.6,0.1 Z' id='Path'></path>
                                                    </g>
                                                </g>
                                            </g>
                                        </svg>";
                        $html .= "</label>";  
                    $html .='</div>'; 
    
                    $html .= "</fieldset>";
                    $html .= "</div>";
                    $html .="<script type='application/javascript'>";
                    $html .="jQuery(document).ready(function () {";
                        $html .="jQuery('#{$key}-edit-thumbs-up5').click(function () {";
                                                                                    
                            $html .="jQuery('#{$key}-edit-thumbs-down1').removeAttr('checked');";
                            $html .="jQuery('#{$key}-edit-thumbs-down1').siblings().find('i').removeClass('fas');";
                            $html .="let radio = jQuery(this);";
                            $html .="jQuery(this).attr('checked', true);";
                            $html .="jQuery(this).siblings().find('i').toggleClass('fas');"; 
    
                        $html .="});"; 
                        $html .="jQuery('#{$key}-edit-thumbs-down1').click(function () {";                                        
                            
                            $html .="jQuery('#{$key}-edit-thumbs-up5').removeAttr('checked');";
                            $html .="jQuery('#{$key}-edit-thumbs-up5').siblings().find('i').removeClass('fas');";
                            $html .="let radio = jQuery(this);";
                            $html .="jQuery(this).attr('checked', true);";
                            $html .="jQuery(this).siblings().find('i').toggleClass('fas');"; 
    
                            $html .="});"; 
                    $html .="});";
                    $html .= "</script>";
                    $html .= "</td>";
                    $html .= "</tr>";
                }
            } else if( $rating_type == "rating_style_three" ) {
                foreach( $multicriteria as $key => $label ) {
                    $value = $get_rating_criteria[$key];
                    $key = esc_attr( $key );
                    $html .= "<tr>";
                    $html .= "<td>{$label}</td>";
                    $html .= "<td>";
                    $html .= "<div class='reviewx-face-rating rx-star-{$key}'>";
                    $html .= "<fieldset>";
    
                    $checked = ($value == 5) ? 'checked="checked"' : '';
                    $fas2 = ($value == 5) ? 'fas' : '';  
                    $html .="<div class='rx-rating-type-display'>";                                
                        $html .= "<input type='radio' id='{$key}-edit-happy5' name='{$key}' value='5' $checked/>";
                        $html .= "<label for='{$key}-edit-happy5'>";
                            $html .="<svg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px'
                                         viewBox='0 0 80 80' style='enable-background:new 0 0 80 80; width: 35px' xml:space='preserve'>
                                        <style type='text/css'>
                                            .happy_st0{fill:#D0D6DC;}
                                            .happy_st1{fill:#6D6D6D;}
                                        </style>
                                        <g>
                                            <radialGradient id='SVGID_1_' cx='40' cy='40' r='40' gradientUnits='userSpaceOnUse'>
                                                <stop  offset='0' style='stop-color:#62E2FF'/>
                                                <stop  offset='0.9581' style='stop-color:#3593FF'/>
                                            </radialGradient>
                                            <path class='happy_st0 rx_happy' d='M40,0C18,0,0,18,0,40c0,22,18,40,40,40s40-18,40-40C80,18,62,0,40,0z M54,24c3.2,0,6,2.8,6,6c0,3.2-2.8,6-6,6
                                            c-3.2,0-6-2.8-6-6C48,26.8,50.8,24,54,24z M26,24c3.2,0,6,2.8,6,6c0,3.2-2.8,6-6,6c-3.2,0-6-2.8-6-6C20,26.8,22.8,24,26,24z M40,64
                                            c-10.4,0-19.2-6.8-22.4-16h44.8C59.2,57.2,50.4,64,40,64z'/>
                                            <path class='happy_st1' d='M54,36c3.2,0,6-2.8,6-6c0-3.2-2.8-6-6-6c-3.2,0-6,2.8-6,6C48,33.2,50.8,36,54,36z'/>
                                            <path class='happy_st1' d='M26,36c3.2,0,6-2.8,6-6c0-3.2-2.8-6-6-6c-3.2,0-6,2.8-6,6C20,33.2,22.8,36,26,36z'/>
                                            <path class='happy_st1' d='M40,64c10.4,0,19.2-6.8,22.4-16H17.6C20.8,57.2,29.6,64,40,64z'/>
                                        </g>
                                    </svg>";
                        $html .= "</label>"; 
                    $html .='</div>'; 
                    
                    $checked = ($value == 1) ? 'checked="checked"' : '';
                    $fas = ($value == 1) ? 'fas' : '';
                    $html .="<div class='rx-rating-type-display'>";
                        $html .= "<input type='radio' id='{$key}-edit-sad1' name='{$key}' value='1' $checked/>";
                        $html .= "<label for='{$key}-edit-sad1'>";
                            $html .="<svg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px'
                                         viewBox='0 0 80 80' style='enable-background:new 0 0 80 80; width: 35px' xml:space='preserve'>
                                        <style type='text/css'>
                                            .st0{fill:#6D6D6D;}
                                            .st1{fill:#D1D7DD;}
                                        </style>
                                        <g>
                                            <path class='st0 sad_st0' d='M54,36c3.2,0,6-2.8,6-6c0-3.2-2.8-6-6-6c-3.2,0-6,2.8-6,6C48,33.2,50.8,36,54,36z'/>
                                            <path class='st0 sad_st0' d='M26,36c3.2,0,6-2.8,6-6c0-3.2-2.8-6-6-6c-3.2,0-6,2.8-6,6C20,33.2,22.8,36,26,36z'/>
                                            <path class='st1' d='M40,0C18,0,0,18,0,40c0,22,18,40,40,40s40-18,40-40C80,18,62,0,40,0z M54,24c3.2,0,6,2.8,6,6c0,3.2-2.8,6-6,6
                                            c-3.2,0-6-2.8-6-6C48,26.8,50.8,24,54,24z M26,24c3.2,0,6,2.8,6,6c0,3.2-2.8,6-6,6c-3.2,0-6-2.8-6-6C20,26.8,22.8,24,26,24z'/>
                                            <path class='st0 sad_st0' d='M40,42.8c-9.5,0-17.5,6.2-20.4,14.6h40.8C57.5,49,49.5,42.8,40,42.8z'/>
                                        </g>
                                    </svg>";
                        $html .= "</label>";
                    $html .='</div>';                  
    
                    $html .= "</fieldset>";
                    $html .= "</div>";
                    $html .="<script type='application/javascript'>";
                    $html .="jQuery(document).ready(function () {";
                        $html .="jQuery('#{$key}-edit-happy5').click(function () {";
                                                                                    
                            $html .="jQuery('#{$key}-edit-sad1').removeAttr('checked');";
                            $html .="jQuery('#{$key}-edit-sad1').siblings().find('i').removeClass('fas');";
                            $html .="let radio = jQuery(this);";
                            $html .="jQuery(this).attr('checked', true);";
                            $html .="jQuery(this).siblings().find('i').toggleClass('fas');"; 
    
                        $html .="});"; 
                        $html .="jQuery('#{$key}-edit-sad1').click(function () {";                                        
                            
                            $html .="jQuery('#{$key}-edit-happy5').removeAttr('checked');";
                            $html .="jQuery('#{$key}-edit-happy5').siblings().find('i').removeClass('fas');";
                            $html .="let radio = jQuery(this);";
                            $html .="jQuery(this).attr('checked', true);";
                            $html .="jQuery(this).siblings().find('i').toggleClass('fas');"; 
    
                            $html .="});";
                    $html .="});";
                    $html .= "</script>";
                    $html .= "</td>";
                    $html .= "</tr>";
                }
            } else {
                foreach( $multicriteria as $key => $label ) {
                    $value = $get_rating_criteria[$key];
                    $key = esc_attr( $key );
                    $html .= "<tr>";
                    $html .= "<td>{$label}</td>";
                    $html .= "<td>";
                    $html .= "<div class='reviewx-star-rating rx-star-{$key}'>";
                    $html .= "<fieldset>";
        
                    for($ratingUpperValue = 5; $ratingUpperValue > 0; $ratingUpperValue--) {
                        $id = "{$key}{$ratingUpperValue}";
                        $checked = ($value == $ratingUpperValue) ? 'checked="checked"' : '';
                        $html .= "<input type='radio' id='{$id}' name='{$key}' value='{$ratingUpperValue}'  $checked />";
                        $html .= "<label for='{$id}'></label>";
                    }
        
                    $html .= "</fieldset>";
                    $html .= "</div>";
                    $html .="<script type='application/javascript'>";
                    $html .="jQuery(document).ready(function () {";
                    $html .="jQuery('.rx-star-{$key} input').change(function () {";
                    $html .="var radio = jQuery(this);";
                    $html .="jQuery('.rx-star-{$key} .selected').removeClass('selected');";
                    $html .="radio.closest('label').addClass('selected');";
                    $html .="jQuery(this).attr('checked', true);";
                    $html .="});";
                    $html .="});";
                    $html .= "</script>";
                    $html .= "</td>";
                    $html .= "</tr>";
                }
            }
        }

        return $html;
    }
    
    /**
    * Load criteria in edit review form
    *
    * @param array
    * @return void
    **/          
    public function run_update_review_content() {
            
        check_ajax_referer( 'special-string', 'security' );
        $photos 				= array();
        $attachments 			= array();
        $prod_id                = $default_rating = '';
        $user_id                = wp_get_current_user();
        $rx_review_text 		= sanitize_textarea_field($_POST['rx_edit_text']);
        $forminput 				= wp_unslash($_POST['forminput']);

        for( $i = 0; $i < count( $forminput ); $i++ ){
            if( $forminput[$i]['name'] == "rx-review-id" ):
                $review_id = sanitize_text_field( $forminput[$i]['value'] );
            endif; 

            if( $forminput[$i]['name'] == "rx-edit-user-id" ):
                $user_id = sanitize_text_field( $forminput[$i]['value'] );
            endif;

            if( $forminput[$i]['name'] == "rx-edit-product-id" ):
                $prod_id = sanitize_text_field( $forminput[$i]['value'] );
            endif;

            if( $forminput[$i]['name'] == "rx-edit-image" ):
                array_push( $photos, $forminput[$i]['value'] );
            endif;

            if( $forminput[$i]['name'] == "rx-edit-title" ):
                $reviewx_text = sanitize_text_field( $forminput[$i]['value'] );
            endif;

            if( $forminput[$i]['name'] == 'rx-edit-video-source-control' ):   
                $video_source = sanitize_text_field( $forminput[$i]['value'] );
            endif;              

            if( $forminput[$i]['name'] == "rx-edit-video-url" ):
                $video_url = sanitize_text_field( $forminput[$i]['value'] );
            endif;

            if( $forminput[$i]['name'] == "rx-recommend-status" ):
                $recommend_status = sanitize_text_field( $forminput[$i]['value'] );
            endif;

            if( $forminput[$i]['name'] == "rx-edit-anonymouse-user" ):
                $anonymously = sanitize_text_field( $forminput[$i]['value'] );
            endif;

            if( $forminput[$i]['name'] == "rx-edit-default-star" ):
                $default_rating = sanitize_text_field($forminput[$i]['value']);
            endif;

        }

        $attachments['images'] 		=  $photos;
        $criteria 					= array();
        if( \ReviewX_Helper::is_multi_criteria( 'product' ) ) {
            $criteria 					= $this->get_review_criteria($forminput);
            $total_rating_sum 			= array_sum(array_values($criteria));
            $rating 					= round( $total_rating_sum/count($criteria) );
        } else {
            $criteria 					= \ReviewX_Helper::set_criteria_default_rating($default_rating, 'product');
            $rating 					= round( $default_rating ); 
        }

        $update_comment_data        = array(
            'comment_ID' =>         $review_id,
            'comment_date'          => date( 'Y-m-d H:i:s' ),
            'comment_date_gmt'      => date( 'Y-m-d H:i:s' ),
            'comment_content'       => $rx_review_text,
            'user_id'               => $user_id,
        );

        if( empty( $attachments['images'] ) ) {
            $attachments = '';
        }

        $id = wp_update_comment( $update_comment_data );

        if( $id ) {
            $this->minus_rating_data( $prod_id, $review_id ); // minus old review rating
            $this->update_rating_data( $prod_id, $criteria, $rating ); // update review by new rating
    
            update_comment_meta( $review_id, 'rating', $rating );
            update_comment_meta( $review_id, 'reviewx_rating', $criteria );
            update_comment_meta( $review_id, 'reviewx_recommended', $recommend_status );
            update_comment_meta( $review_id, 'reviewx_attachments', $attachments );   
            
            if( get_option('_rx_option_allow_review_title') == 1 ){
                update_comment_meta( $review_id, 'reviewx_title', $reviewx_text );
            }
    
            update_comment_meta( $review_id, 'reviewx_video_source_control', $video_source );
            update_comment_meta( $review_id, 'reviewx_video_url', $video_url );
            update_comment_meta( $review_id, 'reviewx_anonymouse_user', $anonymously ); 
    
            update_post_meta( $prod_id, '_wc_average_rating', \ReviewX_Helper::get_average_rating_for_product( $prod_id ) );
            
            \ReviewX\Controllers\Admin\Criteria\CriteriaController::updateCriteria($review_id, $criteria);
        }

        $success_status = ( $id ) ? true : false;
        $return = array(
            'success' => $success_status
        );
        wp_send_json($return);
    }

    public function minus_rating_data( $prod_id, $review_id ) {

        $ratingOption = get_option('_rx_product_' . $prod_id . '_rating') ? : [];

        //check old review data
        if( count($ratingOption) ) {

            $old_rating = get_comment_meta( $review_id, 'rating', true );
            $old_criteria = get_comment_meta( $review_id, 'reviewx_rating', true );

            $old_criData = [];
            foreach ($old_criteria as $key => $value) {
                $old_criData[$key] = _get($ratingOption[$key], 0) - $value;
            }
            update_option('_rx_product_' . $prod_id . '_rating', array_merge([
                'total_review' =>  _get($ratingOption['total_review'], 0),
                'total_rating' =>  _get($ratingOption['total_rating'], 0) - $old_rating
            ], $old_criData));
        }

    }

    public function update_rating_data( $prod_id, $criteria, $rating ) {

        $ratingOption = get_option('_rx_product_' . $prod_id . '_rating') ? : [];

        $criData = [];
        foreach ($criteria as $key => $value) {
            $criData[$key] = _get($ratingOption[$key], 0) + $value;
        }
        update_option('_rx_product_' . $prod_id . '_rating', array_merge([
            'total_review' =>  _get($ratingOption['total_review'], 0),
            'total_rating' =>  _get($ratingOption['total_rating'], 0) + $rating
        ], $criData));

    }

    public function get_review_criteria( $postdata = array() ) {
        $data 						= array();
        $settings 					= \ReviewX\Controllers\Admin\Core\ReviewxMetaBox::get_option_settings();
        $review_criteria 			= $settings->review_criteria;

        if( (is_array($postdata) && count($postdata) > 0 ) && (is_array($review_criteria) && count($review_criteria) > 0) ):
            for( $i=0; $i<count($postdata); $i++ ):
                if( array_key_exists( $postdata[$i]['name'], $review_criteria ) ):
                    $data[$postdata[$i]['name']] = $postdata[$i]['value'];
                endif;
            endfor;
        endif;
        return $data;
    }

    /**
    * Add highlighted comment meta field
    *
    * @param array
    * @return void
    **/     
    public function reviewx_add_comment_meta_content( $comment_it ) {
        ?>
        <label class="switch">
            <?php $field_id_value = get_comment_meta($comment_it->comment_ID, 'reviewx_highlight', true);
            $field_id_checked  = '';
            if($field_id_value == "reviewx_highlight_comment") $field_id_checked = 'checked="checked"'; ?>
            <input type="checkbox" name="reviewx_highlight" value="reviewx_highlight_comment" <?php echo $field_id_checked; ?> >
            <span class="slider round"></span>
        </label>

        <label class="switch">
            <?php $field_id_value = get_comment_meta($comment_it->comment_ID, 'verified', true);
            $field_id_checked  = '';
            if( $field_id_value == 1 ) $field_id_checked = 'checked="checked"'; ?>
            <input type="checkbox" name="reviewx_verified" value="1" <?php echo $field_id_checked; ?> >
            <span class="slider round"></span>
        </label>
        <?php
    }

    /**
    * Update comment meta
    *
    * @param array
    * @return void
    **/         
    public function reviewx_comment_meta_content_update( $comment_it ) {
        update_comment_meta( $comment_it, "reviewx_highlight", sanitize_text_field( $_POST['reviewx_highlight'] ) );
        update_comment_meta( $comment_it, "verified", sanitize_text_field( $_POST['reviewx_verified'] ) );
    }

    /**
    * Load voted/downvoted html
    *
    * @param int
    * @return void
    **/    
    public function reviewx_upvoted_downvoted_html( $comment_id ) {
        
        $comment_like    = get_comment_meta( $comment_id, 'comment_like', true );
        $userID          = get_current_user_id();
        $product_id      = $this->get_review_product_id( $comment_id );
        
        echo '<div class="rx_review_vote_icon">';

            if( in_array($userID, $comment_like) )  {
                $yes_class = ' vote_selected';
            }

           if( current_user_can('manage_options') && $this->check_review_has_child( $comment_id ) == 0 ){ 
           echo '<span class="rx-admin-reply" data-review-id='.esc_attr($comment_id).' data-product-id='.esc_attr($product_id).'>
                 '.esc_html__( 'Reply', 'reviewx-pro' ).' 
            </span>';
           } else {
            echo '<span class="rx-admin-reply" style="display:none;" data-review-id='.esc_attr($comment_id).' data-product-id='.esc_attr($product_id).'>
                    '.esc_html__( 'Reply', 'reviewx-pro' ).' 
                </span>';               
           }

			echo '<p>'.! empty(get_theme_mod('reviewx_helpful_label') ) ? get_theme_mod('reviewx_helpful_label') : esc_html__( 'Helpful?', 'reviewx-pro' ).'</p>';
            echo '<button type="button" class="reviewx_like like" like_id="'.esc_attr($comment_id).'">
                   
                </button>';
				
			echo '<div class="reviewx_like_val-'.esc_attr($comment_id).' like_dislike_val">';
				if( ! empty( $comment_like ) ) {				
					$result = sizeof($comment_like);
					print $result;
				}
			echo '</div>';

        echo '<div class="user_log_in user_log_in-'.esc_attr($comment_id).'">';
            echo '<h5>'.esc_html__( 'Only login user can', 'reviewx-pro' ).'</h5>';
        echo '</div>';

        echo '<input type="hidden" class="rx_comment_id" value="'.esc_attr($comment_id).'">';
        echo '<input type="hidden" name="rx-voted-nonce" id="rx-voted-nonce" value="'.wp_create_nonce( "special-string" ).'">';
        echo '</div>';
    }
    
    /**
    * Check review has child
    *
    * @param int
    * @return int
    **/    
    public function check_review_has_child( $comment_id ) {
        return get_comments( [ 'parent' => $comment_id, 'count' => true ] ) > 0;
    }

    /**
    * Retrieve product id by review id
    *
    * @param int
    * @return int
    **/     
    public function get_review_product_id( $comment_id ) {
        global $wpdb;
        $rx_comment_table = $wpdb->prefix . 'comments';
        $data             = $wpdb->get_results( $wpdb->prepare( "SELECT comment_post_ID FROM $rx_comment_table WHERE comment_id = %d", $comment_id ) );
        if( $data && !empty($data[0]->comment_post_ID) ) {
            return $data[0]->comment_post_ID;
        }
        return 0; 
    }
        
    
    /**
    * Update upvoted downvoted
    *
    * @param array
    * @return void
    **/
    public function reviewx_upvoted_downvoted() {

        check_ajax_referer( 'special-string', 'security' );
        $commentId  = wp_unslash($_POST['rx_comment_id']);
        $userID     = get_current_user_id();

        if( is_user_logged_in() ) {
           
            $comment_dislike = get_comment_meta( $commentId, 'comment_dislike', true );
            if( in_array($userID, array($comment_dislike) ) ) {
                $update_comment_dislike = $this->removeFromArr( $comment_dislike, $userID );
                update_comment_meta( $commentId, 'comment_dislike', $update_comment_dislike );
            }

            $comment_like    = get_comment_meta( $commentId, 'comment_like', true);
            if( empty($comment_like) ) {
                $comment_like = array();
            }

            if( in_array( $userID, $comment_like ) ) {
                
                if (($key = array_search($userID, $comment_like)) !== false) {
                    unset($comment_like[$key]);
                    array_values($comment_like);
                    update_comment_meta( $commentId, 'comment_like', $comment_like );
                }

            } else {
                array_push( $comment_like, $userID );
                update_comment_meta($commentId, 'comment_like', $comment_like);
            }

            $comment_like_count         = 0;
            $comment_dislike_count      = 0;

            if( !empty( $comment_like ) ) {
                $comment_like_count = count( $comment_like );
            }

            if( !empty( $comment_dislike ) ) {
                $comment_dislike_count = count( $comment_dislike );
            }

            $data = array(
                'total_like'        => $comment_like_count,
                'total_dislike'     => $comment_dislike_count,           
                'success'           => true
            );
            wp_send_json($data);

        } else {

            $data = array(
                'success' => false
            );
            wp_send_json($data);
        }

    }

    /**
    * Remove form attribute
    *
    * @param array
    * @return void
    **/    
    public function removeFromArr( $comment_like, $userID ) {
        unset($comment_like[array_search( $userID, $comment_like )]);
        return array_values( $comment_like );
    }

    /**
    * Remove form attribute
    *
    * @param array
    * @return void
    **/
    public function reviewx_downvoted() {

        check_ajax_referer( 'special-string', 'security' );
        $commentId      = wp_unslash( $_POST['rx_comment_id'] );
        $userID         = get_current_user_id();
        $comment_like   = get_comment_meta( $commentId, 'comment_like', true );

        if( is_user_logged_in() ) {

            if( in_array($userID, $comment_like ) ) {
                $update_comment_like = $this->removeFromArr( $comment_like, $userID );
                update_comment_meta( $commentId, 'comment_like', $update_comment_like );
            }

            $comment_dislike = get_comment_meta( $commentId, 'comment_dislike', true );
            if( empty($comment_dislike) ) {
                $comment_dislike = array();
            }
            if( in_array($userID, $comment_dislike ) ) {

            } else {
                array_push( $comment_dislike, $userID );
                update_comment_meta( $commentId, 'comment_dislike', $comment_dislike );
            }

            $comment_like_count = $comment_dislike_count = 0;
            $comment_like       = get_comment_meta( $commentId, 'comment_like', true);
            if( ! empty( $comment_like ) ) {
                $comment_like_count = count( $comment_like );
            }

            $comment_dislike    = get_comment_meta( $commentId, 'comment_dislike', true);
            if( !empty( $comment_dislike ) ) {
                $comment_dislike_count = count( $comment_dislike );
            }

            $data = array(
                'total_like'        => $comment_like_count,
                'total_dislike'     => $comment_dislike_count,
                'success'           => true
            );
            wp_send_json($data);

        } else {

            $data = array(
                'success' => false
            );
            wp_send_json($data);
            
        }
    }

    /**
    * Load review pro pie, bar, graph chart
    *
    * @param array
    * @return void
    **/    
    public function load_review_graph_template() {

        global $reviewx_shortcode;
        $rx_criteria_color        = $rx_elementor_graph = $rx_divi_graph = '' ;
        $pie_chart_color_array    = array();
        if( !isset($reviewx_shortcode) ) {
            $reviewx_shortcode['rx_product_id'] = ''; 
        }
      
        if( \ReviewX_Helper::check_post_type_availability( get_post_type( $reviewx_shortcode['rx_product_id'] ) ) == TRUE ) {  
            $reviewx_id = \ReviewX_Helper::get_reviewx_post_type_id( get_post_type() );
            $settings = \ReviewX\Controllers\Admin\Core\ReviewxMetaBox::get_metabox_settings( $reviewx_id );
        } else if( get_post_type( $reviewx_shortcode['rx_product_id'] ) == 'product' ) {                        
            $settings = \ReviewX\Controllers\Admin\Core\ReviewxMetaBox::get_option_settings();
        }

        $graph_style              = $settings->graph_style;
        $review_criteria          = $settings->review_criteria;
        
        $rx_elementor_controller = apply_filters( 'rx_load_elementor_style_controller', '' );
        if( is_array($rx_elementor_controller) && array_key_exists( 'rx_graph_type', $rx_elementor_controller ) ) {
            $rx_elementor_graph  = $rx_elementor_controller['rx_graph_type'];
        } 
        
        $rx_oxygen_controller = apply_filters( 'rx_load_oxygen_style_controller', '' );
        if( is_array($rx_oxygen_controller) && array_key_exists( 'rx_graph_type', $rx_oxygen_controller ) ) {
            $rx_oxygen_graph  = $rx_oxygen_controller['rx_graph_type'];
        }
        
        if( get_option('template') == 'Divi' ) {
            //Divi
            $rx_divi_settings = get_post_meta( get_the_ID(), '_rx_option_divi_settings', true );
            if( is_array($rx_divi_settings) && array_key_exists( 'rvx_review_graph_criteria', $rx_divi_settings ) ) {
                $rx_divi_graph = $rx_divi_settings['rvx_review_graph_criteria'];
            }
        }

        // retrieve dynamic pie chart color from reviewx addon
        // foreach( $review_criteria as $key => $single_criteria ) {
        //     $rx_criteria_color = 'rx_criteria_color_'.$key.'';
        //     if( is_array( $rx_elementor_controller ) ) {
        //         array_push( $pie_chart_color_array, $rx_elementor_controller[$rx_criteria_color] );
        //     }
        // }

        if( ! empty($rx_elementor_graph) ) {

            switch ( $rx_elementor_graph ) {

                case "graph_style_one":
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/review-summery/style-one.php';
                break;
                case "graph_style_two_free":
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/review-summery/style-two-free.php';
                break;
                case "graph_style_two":
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/review-summery/style-two.php';
                break;
                case "graph_style_three":
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/review-summery/style-three.php';
                break;
                default:
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/review-summery/style-default.php';
            }

        } else if( ! empty($rx_oxygen_graph) ) {

            switch ( $rx_oxygen_graph ) {

                case "graph_style_one":
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/review-summery/style-one.php';
                break;
                case "graph_style_two_free":
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/review-summery/style-two-free.php';
                break;
                case "graph_style_two":
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/review-summery/style-two.php';
                break;
                case "graph_style_three":
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/review-summery/style-three.php';
                break;
                default:
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/review-summery/style-default.php';
            }
            
        } else if( ! empty($rx_divi_graph) ) {            
            if( \ReviewX_Helper::shortcode_divi_review_summary(get_the_ID()) ){
                switch ( $rx_divi_graph ) {

                    case "graph_style_one":
                        include REVIEWX_PRO_PUBLIC_PATH . 'partials/review-summery/style-one.php';
                    break;
                    case "graph_style_two_free":
                        include REVIEWX_PRO_PUBLIC_PATH . 'partials/review-summery/style-two-free.php';
                    break;
                    case "graph_style_two":
                        include REVIEWX_PRO_PUBLIC_PATH . 'partials/review-summery/style-two.php';
                    break;
                    case "graph_style_three":
                        include REVIEWX_PRO_PUBLIC_PATH . 'partials/review-summery/style-three.php';
                    break;
                    default:
                        include REVIEWX_PRO_PUBLIC_PATH . 'partials/review-summery/style-default.php';
                    
                }
            }

        } else {
            
            switch ( $graph_style ) {
                case "graph_style_one":
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/review-summery/style-one.php';
                break;
                case "graph_style_two_free":
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/review-summery/style-two-free.php';
                break;
                case "graph_style_two":
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/review-summery/style-two.php';
                break;
                case "graph_style_three":
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/review-summery/style-three.php';
                break;
                default:
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/review-summery/style-default.php';
            }
            
        }

    }
    
    /**
    * Load review pro template
    *
    * @param array
    * @return void
    **/    
    public function load_review_templates( $data ) {


        global $reviewx_shortcode; 
        $rx_elementor_template = '';

        if( get_post_type( get_the_ID() ) == 'product' ) {
            $settings           = \ReviewX\Controllers\Admin\Core\ReviewxMetaBox::get_option_settings();
            $template_style     = $settings->template_style;
        } else if( \ReviewX_Helper::check_post_type_availability( get_post_type( get_the_ID() ) ) == TRUE ) {
           $reviewx_id          = \ReviewX_Helper::get_reviewx_post_type_id( get_post_type( get_the_ID() ) );   
           $settings            = \ReviewX\Controllers\Admin\Core\ReviewxMetaBox::get_metabox_settings( $reviewx_id );  
           $template_style      = $settings->template_style;            
        } 

        // Check elementor template
        $rx_elementor_controller    = apply_filters( 'rx_load_elementor_style_controller', '' );
        if( is_array($rx_elementor_controller) && array_key_exists('rx_template_type', $rx_elementor_controller) ) {
            $rx_elementor_template      = $rx_elementor_controller['rx_template_type'];
        }
        
        $rx_oxygen_controller  = apply_filters( 'rx_load_oxygen_style_controller', '' );
        $rx_oxygen_template    = isset($rx_oxygen_controller['rx_template_type']) ? $rx_oxygen_controller['rx_template_type'] : null;        
       
        if( ! empty($rx_elementor_template) ) {

            switch ( $rx_elementor_template ) {
                case "template_style_two":
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/single-review/style-two.php';
                break;
                default:
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/single-review/style-one.php';
            }

        } else if( ! empty($rx_oxygen_template) ) {

            switch ( $rx_oxygen_template ) {
                case "box":
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/single-review/style-two.php';
                break;
                default:
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/single-review/style-one.php';
            }

        } else if( get_option('template') == 'Divi' ) {
            //Divi
            $divi_settings = get_post_meta( get_the_ID(), '_rx_option_divi_settings', true );            

            if( ! empty( $divi_settings ) ) {
                switch ( $divi_settings['rvx_template_type'] ) {
                    case "template_style_two":
                        include REVIEWX_PRO_PUBLIC_PATH . 'partials/single-review/style-two.php';
                        break;
                    default:
                        include REVIEWX_PRO_PUBLIC_PATH . 'partials/single-review/style-one.php';
                }
            }
            else {
                include REVIEWX_PRO_PUBLIC_PATH . 'partials/single-review/style-one.php';
            } 

        } else {

            //Serve local template
            switch ( $template_style ) {
                case "template_style_two":
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/single-review/style-two.php';
                break;
                default:                
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/single-review/style-one.php';
            }

        }

    }
    
    /**
    * Include custom css file for dynamic style
    *
    * @param none
    * @return void
    **/     
    public function reviewx_apply_custom_css() {
        include_once REVIEWX_PRO_PUBLIC_PATH . 'partials/reviewx-custom-css.php';
    }

    /**
    * Load edit review form 
    *
    * @param array
    * @return void
    **/    
    public function load_edit_review_form( $settings ) {
        $settings                   = \ReviewX\Controllers\Admin\Core\ReviewxMetaBox::get_option_settings();
        include REVIEWX_PRO_PUBLIC_PATH . 'partials/my-account/edit-review.php';
    }

    /**
    * Load edit review form 
    *
    * @param array
    * @return void
    **/    
    public function print_reviewer_name( $data ) {
        if( is_array( $data ) ) {
            $anonymouser = get_comment_meta( $data['comment_id'], 'reviewx_anonymouse_user', true );
            $revier_censored_name    = get_option('_rx_option_allow_reviewer_name_censor');
            if( isset($anonymouser) && $anonymouser == 1 ) {
                echo esc_html__( 'Anonymous', 'reviewx-pro' ) ;
            } else {
                if(empty($data['author'])){
                    echo esc_html__( 'Anonymous', 'reviewx-pro' ) ;
                } elseif($revier_censored_name == 1) {
                    $first_name= '';
                    $last_name = '';
                    $words = preg_split('/\s+/', $data['author'], -1, PREG_SPLIT_NO_EMPTY);
                    $first_name = mb_substr($words[0], 0, 2) . preg_replace('/[^ ]/', '*', mb_substr($words[0], 1));
                    // if($words[1]) {
                    //     $last_name = mb_substr($words[1], 0, 2) . preg_replace('/[^ ]/', '*', mb_substr($words[1], 1));
                    // }
                    isset($words[1]) ? $last_name = mb_substr($words[1], 0, 2) . preg_replace('/[^ ]/', '*', mb_substr($words[1], 1)) : ' ';
                    echo $first_name . ' ' . $last_name;
                }else{
                     echo $data['author'];
                }
                
            }
        }
    }

    /**
    * Add opengraph 
    *
    * @param array
    * @return void
    **/    
    public function add_opengraph_doctype( $output ) {
        return $output . ' xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml"';
    }

    /**
    * Add facebook card meta 
    *
    * @param array
    * @return void
    **/
    public function insert_fb_in_head() {

	    global $wp_query;

	    $site_name  = get_bloginfo('name');
	    if ( function_exists('is_product_category') ) {
	    	if ( is_product_category() ) {

		    	$cat = $wp_query->get_queried_object();
		    	$thumbnail_id = get_term_meta( $cat->term_id, 'thumbnail_id', true );;
		    	$image = wp_get_attachment_url( $thumbnail_id );

		        echo '<meta property="og:title" content="' . $cat->name . '"/>';
		        echo '<meta property="og:type" content="object"/>';
		        echo '<meta property="og:url" content="' . $this->get_permalink() . '"/>';
		        echo '<meta property="og:site_name" content="'.$site_name.'"/>';
		    	
		    	if ( $image ) {
		    		echo '<meta property="og:image" content="' . $image . '"/>';
		    		echo '<meta property="og:image:secure_url" content="' . $image . '">';
		    	}
	    	}

	    } else if ( is_search() ) {
	    	global $wp;

			$permalink = add_query_arg( $wp->query_vars, home_url( $wp->request ) );
			echo '<meta property="og:title" content="' . get_the_title() . '"/>';
	        echo '<meta property="og:type" content="product"/>';
	        echo '<meta property="og:url" content="' . $permalink . '"/>';
	        echo '<meta property="og:site_name" content="'.$wp->query_vars['s'].'"/>';

	        $image = wp_get_attachment_url( get_post_thumbnail_id() );
	    	
	    	if ( $image ) {
	    		echo '<meta property="og:image" content="' . $image . '"/>';
	    		echo '<meta property="og:image:secure_url" content="' . $image . '">';
	    	}
	    } else if ( function_exists('is_product') ) {
	    	if ( is_product() ) {
	    		echo '<meta property="og:title" content="' . get_the_title() . '"/>';
		        echo '<meta property="og:type" content="product"/>';
		        echo '<meta property="og:url" content="' . $this->get_permalink() . '"/>';
		        echo '<meta property="og:site_name" content="'.$site_name.'"/>';

		        $image = wp_get_attachment_url( get_post_thumbnail_id() );
		    	
		    	if ( $image ) {
		    		echo '<meta property="og:image" content="' . $image . '"/>';
		    		echo '<meta property="og:image:secure_url" content="' . $image . '">';
		    	}
	    	}
	    } else {
	    	echo '<meta property="og:title" content="' . get_the_title() . '"/>';
	        echo '<meta property="og:type" content="website"/>';
	        echo '<meta property="og:url" content="' . $this->get_permalink() . '"/>';
	        echo '<meta property="og:site_name" content="'.$site_name.'"/>';

	        $image = wp_get_attachment_url( get_post_thumbnail_id() );
	    	
	    	if ( $image ) {
	    		echo '<meta property="og:image" content="' . $image . '"/>';
	    		echo '<meta property="og:image:secure_url" content="' . $image . '">';
	    	}
	    }
    }
    
    /**
    * Add twitter card meta 
    *
    * @param none
    * @return void
    **/     
    public function insert_twitter_card() {

		$image = wp_get_attachment_url( get_post_thumbnail_id() );
		echo '<meta name="twitter:card" content="summary_large_image" />';
		echo '<meta name="twitter:title" content="' . get_the_title() .'" />';
		
		if ( $image ) {
    		echo '<meta name="twitter:image" content="' . $image . '"/>';
    	}
        echo '<meta name="twitter:url" content="' . $this->get_permalink() . '" />';
        
    }

    /**
    * Get product permalink
    *
    * @param none
    * @return void
    **/     
	public function get_permalink() {

		if ( is_archive() ) {
			if ( is_tax() ) {
				$taxObject = get_queried_object();
				if ( in_array($taxObject->taxonomy, array('product_cat', 'product_tag')) ) {
					return get_term_link( $taxObject->term_id, $taxObject->taxonomy );
				}
			}
		}

		if ( is_search() ) {
			global $wp;
			return add_query_arg( $wp->query_vars, home_url( $wp->request ) );
		}

		return get_permalink();
    }
    
    /**
    * Display social button 
    *
    * @param none
    * @return void
    **/     
    public function display_social_btns( $data ) {

		$theme                  = 'default-theme';
		$this->theme            = $theme;
		$this->product_title 	= get_the_title();

		// // If is a search results page alter the title to reflect this
		if ( is_search() ) {
			global $wp;
			$this->product_title = $wp->query_vars['s'];
		}

		// // Wether is a taxonomy or a custom post type get the right URL to share
		$this->product_url		= $this->get_permalink();
		$this->product_img		= wp_get_attachment_url( get_post_thumbnail_id() );
		$this->class 			= $theme;
		$this->links            = array();

		// // Building the links with the information to share
		$this->build_links( $data );
        
		// // Based on theme selected display the HTML
		echo $this->rx_display_social_btns();
    }  
    
    /**
    * Button links
    *
    * @param none
    * @return void
    **/     
	public function build_links( $data ) {
		$this->links[]['facebook']['url'] 	= 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($this->product_url) . '&quote=' . $data['review'];
        $this->links[]['twitter']['url']	= 'http://twitter.com/intent/tweet?text=' . rawurlencode( $data['review'] ) . '+ ' . $this->product_url;
    }
    
    /**
    * Display social icons
    *
    * @param none
    * @return void
    **/     
	public function rx_display_social_btns() {

	    $facebook_svg_icon = '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                     viewBox="0 0 80 80" style="enable-background:new 0 0 80 80;" xml:space="preserve">
                                    
                                    <g>
                                        <path class="st0" d="M45,27.5v-10c0-2.8,2.2-5,5-5h5V0H45c-8.3,0-15,6.7-15,15v12.5H20V40h10v40h15V40h10l5-12.5H45z M45,27.5"/>
                                    </g>
                                </svg>';

        $twitter_svg_icon = '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                 viewBox="0 0 80 80" style="enable-background:new 0 0 80 80;" xml:space="preserve">
                                <g>
                                    <path class="st0" d="M80,15.2c-2.9,1.3-6.1,2.2-9.4,2.6c3.4-2,6-5.2,7.2-9.1c-3.2,1.9-6.7,3.2-10.4,4c-3-3.2-7.3-5.2-12-5.2
                                        C46.3,7.5,39,14.9,39,23.9c0,1.3,0.1,2.5,0.4,3.7C25.8,27,13.7,20.4,5.6,10.5c-1.4,2.4-2.2,5.2-2.2,8.2c0,5.7,2.9,10.7,7.3,13.7
                                        c-2.7-0.1-5.2-0.8-7.4-2.1v0.2c0,7.9,5.7,14.6,13.2,16.1c-1.4,0.4-2.8,0.6-4.3,0.6c-1.1,0-2.1-0.1-3.1-0.3
                                        c2.1,6.5,8.1,11.3,15.3,11.4c-5.6,4.4-12.7,7-20.4,7c-1.3,0-2.6-0.1-3.9-0.2c7.3,4.7,15.9,7.4,25.2,7.4c30.2,0,46.7-25,46.7-46.7
                                        l-0.1-2.1C75,21.4,77.8,18.5,80,15.2L80,15.2z M80,15.2"/>
                                </g>
                            </svg>';
        $social_share_icon_svg = '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                 viewBox="0 0 80 80" style="enable-background:new 0 0 80 80;" xml:space="preserve">
                                <path d="M63.3,53.3c-3.9,0-7.4,1.7-9.8,4.3L29.4,43.9c0.4-1.2,0.6-2.5,0.6-3.9c0-1.4-0.2-2.7-0.6-3.9l24.1-13.7
                                    c2.4,2.6,5.9,4.3,9.8,4.3c7.4,0,13.3-6,13.3-13.3C76.7,6,70.7,0,63.3,0S50,6,50,13.3c0,1.1,0.1,2.1,0.4,3.2L25.9,30.4
                                    c-2.4-2.3-5.7-3.8-9.3-3.8c-7.4,0-13.3,6-13.3,13.3c0,7.4,6,13.3,13.3,13.3c3.6,0,6.9-1.4,9.3-3.8l24.4,13.9c-0.2,1-0.4,2.1-0.4,3.2
                                    C50,74,56,80,63.3,80s13.3-6,13.3-13.3C76.7,59.3,70.7,53.3,63.3,53.3z"/>
                                </svg>';

		$count = 0;
		$btns = '<div class="wc_rx_btns ' . $this->class . '">' .
			'<ul>';
        $btns .= '<li>';
        $btns .=''. $social_share_icon_svg .'';
        $btns .='</li>';

        $btns .= '<li>';
        $btns .='<a target="_blank" href="javascript:void(0);" data-href="' . $this->links[0]['facebook']['url'] . '">';
        $btns .=''. $facebook_svg_icon .'';
        $btns .='</a>';
        $btns .='</li>';

        $btns .= '<li>';
        $btns .='<a target="_blank" href="javascript:void(0);" data-href="' . $this->links[1]['twitter']['url'] . '">';
        $btns .=''. $twitter_svg_icon .'';
        $btns .='</a>';
        $btns .='</li>';

		$btns .= '</ul><span class="wc_rx_btns_flex"></span>';
		$btns .= '</div>';

		return $btns;
    }

    /**
    * Display social icons
    *
    * @param none
    * @return void
    **/
    public function load_product_rating_type( $data ) {
        
        $rx_rating_style    = '';
        $rating_style       = $data->rating_style;
        $review_criteria    = $data->review_criteria;        

        // Switch rating style by addon
        $rx_elementor_controller = apply_filters( 'rx_load_elementor_style_controller', '' );
        
        if( is_array($rx_elementor_controller) && array_key_exists('rx_template_type', $rx_elementor_controller) ){
            if( $rx_elementor_controller['rx_template_type'] == 'template_style_two' ) {
                $rx_rating_style      = $rx_elementor_controller['rx_template_two_form_rating_type'];
            }  else if( $rx_elementor_controller['rx_template_type'] == 'template_style_one' ) {
                $rx_rating_style      = $rx_elementor_controller['rx_template_one_form_rating_type'];
            }
        }

        $rx_oxygen_controller  = apply_filters( 'rx_load_oxygen_style_controller', '' );
        $rating_style = isset($rx_oxygen_controller['rx_rating_style']) ? $rx_oxygen_controller['rx_rating_style'] : $rating_style;
        if( get_option('template') == 'Divi' ) {
            //Divi
            $rx_divi_settings = get_post_meta( get_the_ID(), '_rx_option_divi_settings', true );
            if( is_array($rx_divi_settings) && array_key_exists( 'rvx_template_type', $rx_divi_settings ) ) {    
                $rvx_template_type = $rx_divi_settings['rvx_template_type'];
                if( $rvx_template_type == 'template_style_two' ){
                    $rx_rating_style = $rx_divi_settings['rvx_template_two_form_rating_type'];
                } else {
                    $rx_rating_style = $rx_divi_settings['rvx_template_one_form_rating_type'];
                }                
            }
        }

        if( ! empty($rx_rating_style) ) {
            $rating_style = $rx_rating_style;
        }

        // if( !\ReviewX_Helper::is_multi_criteria( get_post_type() ) ) {
           if( $data->allow_multi_criteria != 1 ){ 
            ?>
                <tr>
                    <td class="rx-classic-rating">
                        <?php echo __('Rate Your Satisfaction*', 'reviewx-pro'); ?>
                    </td>
                    <td>
                        <div class="reviewx-star-rating rx-star-default">
                            <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
                                <symbol id="rx_starIcon" viewBox="0 0 24 24">
                                    <polygon points="12,0 15.708,7.514 24,8.718 18,14.566 19.416,22.825 12,18.926 4.583,22.825 6,14.566 0,8.718 8.292,7.514"/>
                                </symbol>
                            </svg>
                            <fieldset class="rx_star_rating">
                                <input type="radio" id="rx-default-star5" name="rx-default-star" value="5" checked="checked" />
                                <label for="rx-default-star5" title="<?php esc_attr_e('Outstanding', 'reviewx-pro' ); ?>">
                                    <svg class="icon icon-star">
                                        <use xlink:href="#rx_starIcon" />
                                    </svg>
                                </label>
                                <input type="radio" id="rx-default-star4" name="rx-default-star" value="4" />
                                <label for="rx-default-star4" title="<?php esc_attr_e('Very Good', 'reviewx-pro' ); ?>">
                                    <svg class="icon icon-star">
                                        <use xlink:href="#rx_starIcon" />
                                    </svg>
                                </label>
                                <input type="radio" id="rx-default-star3" name="rx-default-star" value="3" />
                                <label for="rx-default-star3" title="<?php esc_attr_e('Good', 'reviewx-pro' ); ?>">
                                    <svg class="icon icon-star">
                                        <use xlink:href="#rx_starIcon" />
                                    </svg>
                                </label>
                                <input type="radio" id="rx-default-star2" name="rx-default-star" value="2" />
                                <label for="rx-default-star2" title="<?php esc_attr_e('Poor', 'reviewx-pro' ); ?>">
                                    <svg class="icon icon-star">
                                        <use xlink:href="#rx_starIcon" />
                                    </svg>
                                </label>
                                <input type="radio" id="rx-default-star1" name="rx-default-star" value="1" />
                                <label for="rx-default-star1" title="<?php esc_attr_e('Very Poor', 'reviewx-pro' ); ?>">
                                    <svg class="icon icon-star">
                                        <use xlink:href="#rx_starIcon" />
                                    </svg>
                                </label>
                            </fieldset>
                        </div>

                        <script type="application/javascript">
                            jQuery(document).ready(function () {
                                jQuery('.rx-star-default input').change(function () {
                                    let radio = jQuery(this);
                                    jQuery(this).attr('checked', true);
                                });
                            });
                        </script>
                    </td>
                </tr>
            <?php
            
        } else {
            switch ( $rating_style ) {
                case "rating_style_one":
                    if (is_array($review_criteria) || is_object($review_criteria)) { 
                        foreach ( $review_criteria as $key => $value ) {
                    ?>
                        <tr>
                            <td><?php echo esc_html( $value ); ?></td>
                            <td>
                                <div class="reviewx-star-rating rx-star-<?php echo esc_attr( $key ); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
                                        <symbol id="rx_starIcon" viewBox="0 0 24 24">
                                            <polygon points="12,0 15.708,7.514 24,8.718 18,14.566 19.416,22.825 12,18.926 4.583,22.825 6,14.566 0,8.718 8.292,7.514"/>
                                        </symbol>
                                    </svg>
                                    <fieldset class="rx_star_rating">
                                        <input type="radio" id="<?php echo esc_attr( $key ); ?>-star5" name="<?php echo esc_attr( $key ); ?>" value="5" checked="checked"/>
                                        <label for="<?php echo esc_attr( $key ); ?>-star5" title="<?php esc_attr_e('Outstanding', 'reviewx-pro' ); ?>">
                                            <svg class="icon icon-star">
                                                <use xlink:href="#rx_starIcon" />
                                            </svg>
                                        </label>
                                        <input type="radio" id="<?php echo esc_attr( $key ); ?>-star4" name="<?php echo esc_attr( $key ); ?>" value="4" />
                                        <label for="<?php echo esc_attr( $key ); ?>-star4" title="<?php esc_attr_e('Very Good', 'reviewx-pro' ); ?>">
                                            <svg class="icon icon-star">
                                                <use xlink:href="#rx_starIcon" />
                                            </svg>
                                        </label>
                                        <input type="radio" id="<?php echo esc_attr( $key ); ?>-star3" name="<?php echo esc_attr( $key ); ?>" value="3" />
                                        <label for="<?php echo esc_attr( $key ); ?>-star3" title="<?php esc_attr_e('Good', 'reviewx-pro' ); ?>">
                                            <svg class="icon icon-star">
                                                <use xlink:href="#rx_starIcon" />
                                            </svg>
                                        </label>
                                        <input type="radio" id="<?php echo esc_attr( $key ); ?>-star2" name="<?php echo esc_attr( $key ); ?>" value="2" />
                                        <label for="<?php echo esc_attr( $key ); ?>-star2" title="<?php esc_attr_e('Poor', 'reviewx-pro' ); ?>">
                                            <svg class="icon icon-star">
                                                <use xlink:href="#rx_starIcon" />
                                            </svg>
                                        </label>
                                        <input type="radio" id="<?php echo esc_attr( $key ); ?>-star1" name="<?php echo esc_attr( $key ); ?>" value="1" />
                                        <label for="<?php echo esc_attr( $key ); ?>-star1" title="<?php esc_attr_e('Very Poor', 'reviewx-pro' ); ?>">
                                            <svg class="icon icon-star">
                                                <use xlink:href="#rx_starIcon" />
                                            </svg>
                                        </label>
                                    </fieldset>
                                </div>
    
                                <script type="application/javascript">
                                    jQuery(document).ready(function () {
                                        jQuery('.rx-star-<?php echo $key; ?> input').change(function () {
                                            let radio = jQuery(this);
                                            jQuery('.rx-star-<?php echo $key; ?> .selected').removeClass('selected');
                                            radio.closest('label').addClass('selected');
                                            jQuery(this).attr('checked', true);
                                        });
                                    });
                                </script>
                            </td>
                        </tr>
                <?php  
                        }
                    }
                break;
                case "rating_style_two":
                    foreach ( $review_criteria as $key => $value ) {
                    ?>
                        <tr>
                            <td><?php echo esc_html( $value ); ?></td>
                            <td>
                                <div class="reviewx-thumbs-rating rx-star-<?php echo esc_attr( $key ); ?>">
                                    <fieldset>     
                                        <div class="rx_thumbs_up_icon rx-rating-type-display">
                                            <input type="radio" id="<?php echo esc_attr( $key ); ?>-thumbs-up5" name="<?php echo esc_attr( $key ); ?>" value="5" checked="checked"/>
                                            <label for="<?php echo esc_attr( $key ); ?>-thumbs-up5" title="<?php esc_attr_e('Outstanding', 'reviewx-pro' ); ?>">
                                                <svg width="25px" height="25px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                                     viewBox="0 0 80 80" style="enable-background:new 0 0 80 80;" xml:space="preserve">
                                                    <g>
                                                        <g>
                                                            <path d="M5.8,77.2h8.3c3.2,0,5.8-2.6,5.8-5.8V33.1c0-3.2-2.6-5.8-5.8-5.8H5.8c-3.2,0-5.8,2.6-5.8,5.8v38.3
                                                                C0,74.6,2.6,77.2,5.8,77.2L5.8,77.2z M5.8,77.2"/>
                                                            <path d="M42.6,3.1c-3.3,0-5,1.7-5,10c0,7.9-7.7,14.3-12.6,17.6v41.3c5.3,2.5,16,6.1,32.6,6.1h5.3c6.5,0,12-4.7,13.1-11.1l3.7-21.7
                                                                c1.4-8.2-4.9-15.6-13.1-15.6H50.9c0,0,2.5-5,2.5-13.3C53.4,6.4,45.9,3.1,42.6,3.1L42.6,3.1z M42.6,3.1"/>
                                                        </g>
                                                    </g>
                                                </svg>
                                            </label>
                                        </div>
                                        <div class="rx-rating-type-display">
                                            <input type="radio" id="<?php echo esc_attr( $key ); ?>-thumbs-down1" name="<?php echo esc_attr( $key ); ?>" value="1" />
                                            <label for="<?php echo esc_attr( $key ); ?>-thumbs-down1" title="<?php esc_attr_e('Very Poor', 'reviewx-pro' ); ?>">
                                                <svg width="25px" height="25px" viewBox="0 0 82 77" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                                    <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                        <g id="rx_dislike" transform="translate(41.000000, 38.000000) scale(-1, 1) translate(-41.000000, -38.000000) translate(1.000000, 0.000000)" stroke="#000000">
                                                            <g transform="translate(40.000000, 38.000000) scale(-1, 1) rotate(-180.000000) translate(-40.000000, -38.000000) ">
                                                                <path d="M5.8,74.2 L14.1,74.2 C17.3,74.2 19.9,71.6 19.9,68.4 L19.9,30.1 C19.9,26.9 17.3,24.3 14.1,24.3 L5.8,24.3 C2.6,24.3 0,26.9 0,30.1 L0,68.4 C0,71.6 2.6,74.2 5.8,74.2 Z" id="Path" fill="#6f6f6f"></path>
                                                                <path d="M42.6,0.1 C39.3,0.1 37.6,1.8 37.6,10.1 C37.6,18 29.9,24.4 25,27.7 L25,69 C30.3,71.5 41,75.1 57.6,75.1 L62.9,75.1 C69.4,75.1 74.9,70.4 76,64 L79.7,42.3 C81.1,34.1 74.8,26.7 66.6,26.7 L50.9,26.7 C50.9,26.7 53.4,21.7 53.4,13.4 C53.4,3.4 45.9,0.1 42.6,0.1 Z" id="Path"></path>
                                                            </g>
                                                        </g>
                                                    </g>
                                                </svg>
                                            </label> 
                                        </div>                                          
                                    </fieldset>
                                </div>
    
                                <script type="application/javascript">
                                    jQuery(document).ready(function () {
                                        jQuery('#<?php echo esc_attr( $key ); ?>-thumbs-up5').click(function (event) {
                                                                                    
                                            jQuery('#<?php echo esc_attr( $key ); ?>-thumbs-down1').removeAttr('checked');
                                            jQuery('#<?php echo esc_attr( $key ); ?>-thumbs-down1').siblings().find('i').removeClass('fas');                                        
                                            let radio = jQuery(this);
                                            jQuery(this).attr('checked', true);
                                            jQuery(this).siblings().find('i').toggleClass('fas'); 
    
                                        }); 
                                        jQuery('#<?php echo esc_attr( $key ); ?>-thumbs-down1').click(function (event) {                                        
                                            
                                            jQuery('#<?php echo esc_attr( $key ); ?>-thumbs-up5').removeAttr('checked');
                                            jQuery('#<?php echo esc_attr( $key ); ?>-thumbs-up5').siblings().find('i').removeClass('fas');
                                            let radio = jQuery(this);
                                            jQuery(this).attr('checked', true);
                                            jQuery(this).siblings().find('i').toggleClass('fas'); 
    
                                        });                                    
                                    });
                                </script>
                            </td>
                        </tr>
                    <?php    
                    }
                break;
                case "rating_style_three":
                    foreach ( $review_criteria as $key => $value ) {
                    ?>
                        <tr>
                            <td><?php echo esc_html( $value ); ?></td>
                            <td>
                                <div class="reviewx-face-rating rx-star-<?php echo esc_attr( $key ); ?>">
                                    <fieldset>
                                        <div class="rx-rating-type-display">
                                            <input type="radio" id="<?php echo esc_attr( $key ); ?>-happy5" name="<?php echo esc_attr( $key ); ?>" value="5" checked="checked"/>                                    
                                            <label for="<?php echo esc_attr( $key ); ?>-happy5" title="<?php esc_attr_e('Outstanding', 'reviewx-pro' ); ?>">
                                                <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                                     viewBox="0 0 80 80" style="enable-background:new 0 0 80 80; width: 30px; height: 30px" xml:space="preserve">
                                                    <style type="text/css">
                                                        .happy_st0{fill:#D0D6DC;}
                                                        .happy_st1{fill:#6D6D6D;}
                                                    </style>
                                                    <g>
                                                        <radialGradient id="SVGID_1_" cx="40" cy="40" r="40" gradientUnits="userSpaceOnUse">
                                                            <stop  offset="0" style="stop-color:#62E2FF"/>
                                                            <stop  offset="0.9581" style="stop-color:#3593FF"/>
                                                        </radialGradient>
                                                        <path class="happy_st0 rx_happy" d="M40,0C18,0,0,18,0,40c0,22,18,40,40,40s40-18,40-40C80,18,62,0,40,0z M54,24c3.2,0,6,2.8,6,6c0,3.2-2.8,6-6,6
                                                        c-3.2,0-6-2.8-6-6C48,26.8,50.8,24,54,24z M26,24c3.2,0,6,2.8,6,6c0,3.2-2.8,6-6,6c-3.2,0-6-2.8-6-6C20,26.8,22.8,24,26,24z M40,64
                                                        c-10.4,0-19.2-6.8-22.4-16h44.8C59.2,57.2,50.4,64,40,64z"/>
                                                        <path class="happy_st1" d="M54,36c3.2,0,6-2.8,6-6c0-3.2-2.8-6-6-6c-3.2,0-6,2.8-6,6C48,33.2,50.8,36,54,36z"/>
                                                        <path class="happy_st1" d="M26,36c3.2,0,6-2.8,6-6c0-3.2-2.8-6-6-6c-3.2,0-6,2.8-6,6C20,33.2,22.8,36,26,36z"/>
                                                        <path class="happy_st1" d="M40,64c10.4,0,19.2-6.8,22.4-16H17.6C20.8,57.2,29.6,64,40,64z"/>
                                                    </g>
                                                </svg>
                                            </label>
                                        </div>
                                        <div class="rx-rating-type-display">
                                            <input type="radio" id="<?php echo esc_attr( $key ); ?>-sad1" name="<?php echo esc_attr( $key ); ?>" value="1" />                                    
                                            <label for="<?php echo esc_attr( $key ); ?>-sad1" title="<?php esc_attr_e('Very Poor', 'reviewx-pro' ); ?>">
                                                <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                                     viewBox="0 0 80 80" style="enable-background:new 0 0 80 80; width: 30px; height: 30px" xml:space="preserve">
                                                    <style type="text/css">
                                                        .sad_st0{fill:#6D6D6D;}
                                                        .sad_st1{fill:#D1D7DD;}
                                                    </style>
                                                    <g>
                                                        <path class="st0 sad_st0" d="M54,36c3.2,0,6-2.8,6-6c0-3.2-2.8-6-6-6c-3.2,0-6,2.8-6,6C48,33.2,50.8,36,54,36z"/>
                                                        <path class="st0 sad_st0" d="M26,36c3.2,0,6-2.8,6-6c0-3.2-2.8-6-6-6c-3.2,0-6,2.8-6,6C20,33.2,22.8,36,26,36z"/>
                                                        <path class="st1 sad_st1" d="M40,0C18,0,0,18,0,40c0,22,18,40,40,40s40-18,40-40C80,18,62,0,40,0z M54,24c3.2,0,6,2.8,6,6c0,3.2-2.8,6-6,6
                                                        c-3.2,0-6-2.8-6-6C48,26.8,50.8,24,54,24z M26,24c3.2,0,6,2.8,6,6c0,3.2-2.8,6-6,6c-3.2,0-6-2.8-6-6C20,26.8,22.8,24,26,24z"/>
                                                        <path class="st0 sad_st0" d="M40,42.8c-9.5,0-17.5,6.2-20.4,14.6h40.8C57.5,49,49.5,42.8,40,42.8z"/>
                                                    </g>
                                                </svg>
                                            </label>                                    
                                        </div>                                    
                                    </fieldset>
                                </div>
    
                                <script type="application/javascript">
                                    jQuery(document).ready(function () {
                                        jQuery('#<?php echo esc_attr( $key ); ?>-happy5').click(function (event) {
                                                                                    
                                            jQuery('#<?php echo esc_attr( $key ); ?>-sad1').removeAttr('checked');
                                            jQuery('#<?php echo esc_attr( $key ); ?>-sad1').siblings().find('i').removeClass('fas');                                        
                                            let radio = jQuery(this);
                                            jQuery(this).attr('checked', true);
                                            jQuery(this).siblings().find('i').toggleClass('fas'); 
                                                                                  
                                        }); 
                                        jQuery('#<?php echo esc_attr( $key ); ?>-sad1').click(function (event) {                                        
                                            
                                            jQuery('#<?php echo esc_attr( $key ); ?>-happy5').removeAttr('checked');
                                            jQuery('#<?php echo esc_attr( $key ); ?>-happy5').siblings().find('i').removeClass('fas');
                                            let radio = jQuery(this);
                                            jQuery(this).attr('checked', true);
                                            jQuery(this).siblings().find('i').toggleClass('fas'); 
    
                                        });                                    
                                    });
                                </script>
                            </td>
                        </tr>
                    <?php   
                    }
                break;
                default:
                    foreach ( $review_criteria as $key => $value ) {
                    ?>
                        <tr>
                            <td><?php echo esc_html( $value ); ?></td>
                            <td>
                                <div class="reviewx-star-rating rx-star-<?php echo esc_attr( $key ); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
                                        <symbol id="rx_starIcon" viewBox="0 0 24 24">
                                            <polygon points="12,0 15.708,7.514 24,8.718 18,14.566 19.416,22.825 12,18.926 4.583,22.825 6,14.566 0,8.718 8.292,7.514"/>
                                        </symbol>
                                    </svg>
                                    <fieldset class="rx_star_rating">
                                        <input type="radio" id="<?php echo esc_attr( $key ); ?>-star5" name="<?php echo esc_attr( $key ); ?>" value="5" checked="checked"/>
                                        <label for="<?php echo esc_attr( $key ); ?>-star5" title="<?php esc_attr_e('Outstanding', 'reviewx-pro' ); ?>">
                                            <svg class="icon icon-star">
                                                <use xlink:href="#rx_starIcon" />
                                            </svg>
                                        </label>
                                        <input type="radio" id="<?php echo esc_attr( $key ); ?>-star4" name="<?php echo esc_attr( $key ); ?>" value="4" />
                                        <label for="<?php echo esc_attr( $key ); ?>-star4" title="<?php esc_attr_e('Very Good', 'reviewx-pro' ); ?>">
                                            <svg class="icon icon-star">
                                                <use xlink:href="#rx_starIcon" />
                                            </svg>
                                        </label>
                                        <input type="radio" id="<?php echo esc_attr( $key ); ?>-star3" name="<?php echo esc_attr( $key ); ?>" value="3" />
                                        <label for="<?php echo esc_attr( $key ); ?>-star3" title="<?php esc_attr_e('Good', 'reviewx-pro' ); ?>">
                                            <svg class="icon icon-star">
                                                <use xlink:href="#rx_starIcon" />
                                            </svg>
                                        </label>
                                        <input type="radio" id="<?php echo esc_attr( $key ); ?>-star2" name="<?php echo esc_attr( $key ); ?>" value="2" />
                                        <label for="<?php echo esc_attr( $key ); ?>-star2" title="<?php esc_attr_e('Poor', 'reviewx-pro' ); ?>">
                                            <svg class="icon icon-star">
                                                <use xlink:href="#rx_starIcon" />
                                            </svg>
                                        </label>
                                        <input type="radio" id="<?php echo esc_attr( $key ); ?>-star1" name="<?php echo esc_attr( $key ); ?>" value="1" />
                                        <label for="<?php echo esc_attr( $key ); ?>-star1" title="<?php esc_attr_e('Very Poor', 'reviewx-pro' ); ?>">
                                            <svg class="icon icon-star">
                                                <use xlink:href="#rx_starIcon" />
                                            </svg>
                                        </label>
                                    </fieldset>
                                </div>
    
                                <script type="application/javascript">
                                    jQuery(document).ready(function () {
                                        jQuery('.rx-star-<?php echo $key; ?> input').change(function () {
                                            let radio = jQuery(this);
                                            jQuery('.rx-star-<?php echo $key; ?> .selected').removeClass('selected');
                                            radio.closest('label').addClass('selected');
                                            jQuery(this).attr('checked', true);
                                        });
                                    });
                                </script>
                            </td>
                        </tr>
                    <?php  
                }     
            }
        }

    }

    /**
    * Display anonymouse checkbox
    *
    * @param none
    * @return void
    **/    
    public function reviewx_anonymouse_field( $submit_button, $args ) {

        if( get_post_type() == 'product' ) {

            $button 				        = '';
            $allow_anonymouse               = get_option( '_rx_option_allow_anonymouse' );
            $allow_img                      = get_option( '_rx_option_allow_img' );
            $allow_video                    = get_option( '_rx_option_allow_video' );
            $allow_media_compliance         = get_option( '_rx_option_allow_media_compliance' );
            $anony_data 			        = array();
            $anony_data['is_anonymouse']    = $allow_anonymouse; 
            $anony_data['allow_img']        = $allow_img; 
            $anony_data['allow_video']      = $allow_video;  
            $anony_data['allow_media_compliance']      = $allow_media_compliance;            
            $anony_data['signature']        = 3;
            $button 				        .= apply_filters( 'rx_allow_anonymouse_user', $anony_data );	
            $button 				        .= '<input name="%1$s" type="submit" id="%2$s" class="%3$s" value="%4$s" />';
    
            return sprintf(
                $button,
                esc_attr( $args['name_submit'] ),
                esc_attr( $args['id_submit'] ),
                esc_attr( $args['class_submit'] ),
                esc_attr( $args['label_submit'] )
            );
    
        } else if( \ReviewX_Helper::check_post_type_availability( get_post_type() ) == TRUE ) { 
             
            $button 				        = '';
            $reviewx_id                     = \ReviewX_Helper::get_reviewx_post_type_id( get_post_type() );   
            $allow_anonymouse 		        = \ReviewX_Helper::get_post_meta( $reviewx_id, 'allow_anonymouse', true );
            $allow_img 		                = \ReviewX_Helper::get_post_meta( $reviewx_id, 'allow_img', true );
            $allow_video 		            = \ReviewX_Helper::get_post_meta( $reviewx_id, 'allow_video', true );
            $allow_media_compliance 		= \ReviewX_Helper::get_post_meta( $reviewx_id, 'allow_media_compliance', true );
            $anony_data 			        = array();
            $anony_data['is_anonymouse']    = $allow_anonymouse;
            $anony_data['allow_img']        = $allow_img;
            $anony_data['allow_video']      = $allow_video;
            $anony_data['allow_media_compliance']      = $allow_media_compliance;  
            $anony_data['signature']        = 3;
            $button 				       .= apply_filters( 'rx_allow_anonymouse_user', $anony_data );	
            $button 				       .= '<input name="%1$s" type="submit" id="%2$s" class="%3$s" value="%4$s" />'; 
    
            return sprintf(
                $button,
                esc_attr( $args['name_submit'] ),
                esc_attr( $args['id_submit'] ),
                esc_attr( $args['class_submit'] ),
                esc_attr( $args['label_submit'] )
            ); 
                        
        } else {
    
            $button = '<input name="%1$s" type="submit" id="%2$s" class="%3$s" value="%4$s" />';
    
            return sprintf(
                $button,
                esc_attr( $args['name_submit'] ),
                esc_attr( $args['id_submit'] ),
                esc_attr( $args['class_submit'] ),
                esc_attr( $args['label_submit'] )
            );
    
        }

    }
    
    /**
    * Marked as highlighted
    *
    * @param array
    * @return void
    **/     
    public function run_marked_as_highlight() {

        $data = array();
        $success_status =  true ;
        check_ajax_referer( 'special-string', 'security' );
        $review_id          = sanitize_text_field($_POST['review_id']);
        $exists_highlight   = get_comment_meta( $review_id, "reviewx_highlight", true );
        
        if( $exists_highlight ) {
            delete_comment_meta( $review_id, "reviewx_highlight" );
            $success_status =  false;
        } else {
            update_comment_meta( $review_id, "reviewx_highlight", 'reviewx_highlight_comment' );
            $success_status =  true;
        }
        
        $return = array(
            'success' => $success_status
        );
        wp_send_json($return);

    }

    /**
    * Run admin reply
    *
    * @param array
    * @return void
    **/ 
    public function run_review_admin_reply() {

        check_ajax_referer( 'special-string', 'security' );
        $review_id 	    = sanitize_text_field($_POST['review_id']);
        $product_id     = sanitize_text_field($_POST['product_id']);
        $admin_reply 	= sanitize_textarea_field($_POST['admin_reply']);
        $current_user   = wp_get_current_user();
        $comment_agent  = $_SERVER['HTTP_USER_AGENT'];
        $comment_data = array(
            'comment_post_ID'      => (int)$product_id,
            'comment_author'       => $current_user->user_login,
            'comment_author_email' => $current_user->user_email,
            'comment_author_url'   => '',
            'comment_author_IP'    => $_SERVER['REMOTE_ADDR'],
            'comment_date'         => date( 'Y-m-d H:i:s' ),
            'comment_date_gmt'     => date( 'Y-m-d H:i:s' ),
            'comment_content'      => $admin_reply,
            'comment_karma'        => '',
            'comment_approved'     => 1,
            'comment_agent'        => $comment_agent,
            'comment_type'         => 'review',
            'comment_parent'       => $review_id,
            'user_id'              => $current_user->ID,
        );


        $shop_icon_url = get_option( '_rx_option_icon_upload' );
        if( !empty($shop_icon_url) ) { 
            $shop_icon = '<img src="'.esc_url($shop_icon_url).'" alt="'.__('ReviewX shop icon',  'reviewx-pro').'"/>';
        } else {
            $shop_icon = '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 66 66" style="enable-background:new 0 0 66 66;" xml:space="preserve">
                            <g>
                                <path class="st0" d="M50,21.2c-0.3,2.8-1.5,4.9-3.7,6.3c-1.5,1-3.3,1.5-5.1,1.4c-3.8-0.2-6.5-2.7-7.8-7.4c-0.5,2.9-1.8,5-4.2,6.4 c-1.6,0.9-3.3,1.2-5.1,1c-3.8-0.5-6.6-3.3-7.3-7.5c-0.2,0.6-0.2,1.1-0.4,1.6c-1,3.6-4.3,6.1-8,6c-3.8,0-7.3-2.5-8.2-6 c-0.3-1.1-0.2-2.1,0.4-3.1C3.8,15,6.9,10.1,10,5.1c0.3-0.4,0.6-0.6,1.1-0.6c13.9,0,27.8,0,41.8,0c0.5,0,0.8,0.2,1.1,0.6 c3.8,5.1,7.7,10.2,11.5,15.3c0.5,0.6,0.6,1.2,0.5,1.9c-0.6,3.8-3.5,6.4-7.3,6.7c-3.8,0.3-7.1-2-8.2-5.7C50.3,22.6,50.2,22,50,21.2z"/>
                                <path class="st0" d="M49.6,29.8c-4.8,4.3-11.7,4.2-16.4,0c-4.9,4.4-11.9,4.1-16.4,0c-1.5,1.4-3.3,2.4-5.4,2.9 c-2.1,0.5-4.1,0.4-6.2-0.2v23.1c0,3.3,2.7,6,6,6h13.2c0.2,0,0.4-0.2,0.4-0.4c0,0,0,0,0,0V43.4c0-1,0.8-1.8,1.8-1.8h12.9 c1,0,1.8,0.8,1.8,1.8v17.7c0,0,0,0,0,0c0,0.2,0.2,0.4,0.4,0.4h13.2c3.3,0,6-2.7,6-6V32.6C55.3,33.2,53.7,32.9,49.6,29.8z"/>
                            </g>
                        </svg>';       
        }
        $id             = wp_insert_comment($comment_data);
        $success_status = ($id) ? true : false;
        $string         = '';
        if( $success_status ) {
            $comment = get_comment( intval( $id ) );
            $string = ' <ul class="children">
                            <li class="comment rx_review_block" id="li-comment-'.esc_attr($id).'">
                                <div class="rx_flex rx_review_wrap">
                                    <div class="rx_thumb">                                        
                                        '.$shop_icon.'
                                    </div>
                                    <div class="rx_body">
                                        <div class="rx_flex comment-header">
                                            <div class="rx_flex child-comment-heading">
                                                <h4 class="review_title">'.get_bloginfo( 'name' ).'</h4>
                                                <div class="owner_arrow">
                                                    <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                                         viewBox="0 0 66 55" style="enable-background:new 0 0 66 55;" xml:space="preserve">
                                                        <path class="st0" d="M25.9,40.1C51.4,36.4,62.3,18.2,66-0.1c-9.1,12.8-21.9,18.6-40.1,18.6V3.6L0.4,29.1l25.5,25.5V40.1L25.9,40.1z"
                                                        />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="rx_review_calender">
                                                <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                                     viewBox="-49 141 512 512" style="enable-background:new -49 141 512 512;" xml:space="preserve">
                                                    <g>
                                                        <path class="st0" d="M383,161h-10.8H357h-40h-91h-40H96H56h-7.8H31c-44.1,0-80,35.9-80,80v312c0,44.1,35.9,80,80,80h352
                                                        c42.1,0,76.7-32.7,79.8-74c0.1-1,0.2-2,0.2-3V241C463,196.9,427.1,161,383,161z M423,553c0,22.1-17.9,40-40,40H31
                                                        c-22.1,0-40-17.9-40-40V241c0-22.1,17.9-40,40-40h25v20c0,11,9,20,20,20s20-9,20-20v-20h90v20c0,11,9,20,20,20s20-9,20-20v-20h91
                                                        v20c0,11,9,20,20,20c11,0,20-9,20-20v-20h26c22.1,0,40,17.9,40,40V553z"/>
                                                        <circle class="st0" cx="76" cy="331" r="20"/>
                                                        <circle class="st0" cx="250" cy="331" r="20"/>
                                                        <circle class="st0" cx="337" cy="331" r="20"/>
                                                        <circle class="st0" cx="76" cy="418" r="20"/>
                                                        <circle class="st0" cx="76" cy="505" r="20"/>
                                                        <circle class="st0" cx="163" cy="331" r="20"/>
                                                        <circle class="st0" cx="163" cy="418" r="20"/>
                                                        <circle class="st0" cx="163" cy="505" r="20"/>
                                                        <circle class="st0" cx="250" cy="418" r="20"/>
                                                        <circle class="st0" cx="337" cy="418" r="20"/>
                                                        <circle class="st0" cx="250" cy="505" r="20"/>
                                                    </g>
                                                </svg>
                                                <span>'.date_format( date_create( $comment->comment_date ), "j M Y" ).'</span>
                                            </div>                                            
                                            <div class="rx_flex rx_meta">
                                                <span class="admin-reply-edit-icon" data-review-id="'.esc_attr($id).'">
                                                    <svg height="15px" viewBox="0 -1 401.52289 401" width="15px" xmlns="http://www.w3.org/2000/svg"><path d="m370.589844 250.972656c-5.523438 0-10 4.476563-10 10v88.789063c-.019532 16.5625-13.4375 29.984375-30 30h-280.589844c-16.5625-.015625-29.980469-13.4375-30-30v-260.589844c.019531-16.558594 13.4375-29.980469 30-30h88.789062c5.523438 0 10-4.476563 10-10 0-5.519531-4.476562-10-10-10h-88.789062c-27.601562.03125-49.96875 22.398437-50 50v260.59375c.03125 27.601563 22.398438 49.96875 50 50h280.589844c27.601562-.03125 49.96875-22.398437 50-50v-88.792969c0-5.523437-4.476563-10-10-10zm0 0"/><path d="m376.628906 13.441406c-17.574218-17.574218-46.066406-17.574218-63.640625 0l-178.40625 178.40625c-1.222656 1.222656-2.105469 2.738282-2.566406 4.402344l-23.460937 84.699219c-.964844 3.472656.015624 7.191406 2.5625 9.742187 2.550781 2.546875 6.269531 3.527344 9.742187 2.566406l84.699219-23.464843c1.664062-.460938 3.179687-1.34375 4.402344-2.566407l178.402343-178.410156c17.546875-17.585937 17.546875-46.054687 0-63.640625zm-220.257812 184.90625 146.011718-146.015625 47.089844 47.089844-146.015625 146.015625zm-9.40625 18.875 37.621094 37.625-52.039063 14.417969zm227.257812-142.546875-10.605468 10.605469-47.09375-47.09375 10.609374-10.605469c9.761719-9.761719 25.589844-9.761719 35.351563 0l11.738281 11.734375c9.746094 9.773438 9.746094 25.589844 0 35.359375zm0 0"/></svg>
                                                </span>
                                                <span class="admin-reply-delete-icon" data-review-id="'.esc_attr($id).'">
                                                    <svg viewBox="-57 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="m156.371094 30.90625h85.570312v14.398438h30.902344v-16.414063c.003906-15.929687-12.949219-28.890625-28.871094-28.890625h-89.632812c-15.921875 0-28.875 12.960938-28.875 28.890625v16.414063h30.90625zm0 0"/><path d="m344.210938 167.75h-290.109376c-7.949218 0-14.207031 6.78125-13.566406 14.707031l24.253906 299.90625c1.351563 16.742188 15.316407 29.636719 32.09375 29.636719h204.542969c16.777344 0 30.742188-12.894531 32.09375-29.640625l24.253907-299.902344c.644531-7.925781-5.613282-14.707031-13.5625-14.707031zm-219.863282 312.261719c-.324218.019531-.648437.03125-.96875.03125-8.101562 0-14.902344-6.308594-15.40625-14.503907l-15.199218-246.207031c-.523438-8.519531 5.957031-15.851562 14.472656-16.375 8.488281-.515625 15.851562 5.949219 16.375 14.472657l15.195312 246.207031c.527344 8.519531-5.953125 15.847656-14.46875 16.375zm90.433594-15.421875c0 8.53125-6.917969 15.449218-15.453125 15.449218s-15.453125-6.917968-15.453125-15.449218v-246.210938c0-8.535156 6.917969-15.453125 15.453125-15.453125 8.53125 0 15.453125 6.917969 15.453125 15.453125zm90.757812-245.300782-14.511718 246.207032c-.480469 8.210937-7.292969 14.542968-15.410156 14.542968-.304688 0-.613282-.007812-.921876-.023437-8.519531-.503906-15.019531-7.816406-14.515624-16.335937l14.507812-246.210938c.5-8.519531 7.789062-15.019531 16.332031-14.515625 8.519531.5 15.019531 7.816406 14.519531 16.335937zm0 0"/><path d="m397.648438 120.0625-10.148438-30.421875c-2.675781-8.019531-10.183594-13.429687-18.640625-13.429687h-339.410156c-8.453125 0-15.964844 5.410156-18.636719 13.429687l-10.148438 30.421875c-1.957031 5.867188.589844 11.851562 5.34375 14.835938 1.9375 1.214843 4.230469 1.945312 6.75 1.945312h372.796876c2.519531 0 4.816406-.730469 6.75-1.949219 4.753906-2.984375 7.300781-8.96875 5.34375-14.832031zm0 0"/></svg>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="comment-body">
                                            <div class="comment-content">
                                                <p>'.$comment->comment_content.'</p>
                                            </div>
                                        </div>                                        
                                    </div>                                  
                                </div>
                            </li>
                        </ul>';
        }

        $return = array(
            'success'       => $success_status,
            'admin_reply'   => $string
        );

        wp_send_json($return);
    }

    /**
    * Update review reply
    *
    * @param array
    * @return void
    **/ 
    public function run_update_review_admin_reply() {

        check_ajax_referer( 'special-string', 'security' );
        $review_id 	    = sanitize_text_field( $_POST['review_id'] );
        $admin_reply 	= sanitize_textarea_field( $_POST['admin_reply'] );
        $current_user   = wp_get_current_user();
        $comment_agent  = $_SERVER['HTTP_USER_AGENT'];
        $commentarr = array();
        $commentarr['comment_ID']           = $review_id;
        $commentarr['comment_content']      = $admin_reply;
        $commentarr['comment_author']       = $current_user->user_login;
        $commentarr['comment_author_email'] = $current_user->user_email;
        $commentarr['comment_ID']           = $review_id;
        $commentarr['comment_content']      = $admin_reply;
        $commentarr['comment_agent']        = $comment_agent;
        $commentarr['user_id']              = $current_user->ID;
        $update_success                     = wp_update_comment( $commentarr );
        //$update_success = ($update_success) ? true : false;
        $reply_text     = '';
        if( $update_success ){
            $comment     = get_comment( intval( $review_id ) );
            $reply_text  = $comment->comment_content;
        }

        $return = array(
            'success'       => $update_success,
            'admin_reply'   => $reply_text
        );

        wp_send_json($return);
    }
    
    /**
    * Delete admin reply
    *
    * @param array
    * @return void
    **/     
    public function run_delete_review_admin_reply() {

        check_ajax_referer( 'special-string', 'security' );
        $review_id 	    = sanitize_text_field( $_POST['review_id'] );
        $delete_status  = wp_delete_comment( $review_id, true );
        $return = array(
            'success'       => $delete_status            
        );
        wp_send_json( $return );

    }

    public function rx_pro_feature_overview( $data ) 
    {        
        $settings = array(
            array(
                'id'    => 'action_video_allowed',
                'icon'  => '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"viewBox="0 0 100 100" style="enable-background:new 0 0 100 100;" xml:space="preserve"><path class="st0" d="M21.2,2.2C12.2-3,4.9,1.3,4.9,11.7v76.7c0,10.4,7.3,14.6,16.3,9.5l67-38.4c9-5.2,9-13.6,0-18.7L21.2,2.2z M21.2,2.2"/></svg>',
                'label' =>  __('Allow Video', 'reviewx-pro'),
                'value' => '',
            ),
            array(
                'id'    => 'action_dispaly_location',
                'icon'  => '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"viewBox="0 0 100 100" style="enable-background:new 0 0 100 100;" xml:space="preserve"><path class="st0" d="M21.2,2.2C12.2-3,4.9,1.3,4.9,11.7v76.7c0,10.4,7.3,14.6,16.3,9.5l67-38.4c9-5.2,9-13.6,0-18.7L21.2,2.2z M21.2,2.2"/></svg>',
                'label' =>  __('Display Customer Location', 'reviewx-pro'),
                'value' => '',
            ),
            array(
                'id'    => 'action_anonymouse_allowed',
                'icon'  => '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 100 100" style="enable-background:new 0 0 100 100;" xml:space="preserve"><path class="st0" d="M82.9,70c-4.2-4.2-9.1-7.4-14.5-9.3C67.6,71,59,79.2,48.6,79.2c-10.1,0-18.5-7.6-19.7-17.4 c-4.3,1.9-8.3,4.7-11.8,8.2C9.9,77.3,6,86.9,6,97.2c0,1.6,1.3,2.9,2.9,2.9h82.2c1.6,0,2.9-1.3,2.9-2.9C94,86.9,90.1,77.3,82.9,70z" /><path class="st0" d="M48.9-0.1c-13.2,0-25.2,9.4-25.2,19.9c0,4.3,3.2,6.6,7,6.6c10.5,0,5.1-14.2,18.9-14.2c6.5,0,10.3,3.5,10.3,9.3 c0,10.6-18.9,13.5-18.9,26.3c0,3.4,2.3,7.2,6.9,7.2c7.1,0,6.2-5.3,8.8-9.1c3.5-5.1,19.5-10.4,19.5-24.5C76.3,6.3,62.7-0.1,48.9-0.1z"/></svg>',
                'label' =>  __('Anonymous Review', 'reviewx-pro'),
                'value' => '',
            ),
            array(
                'id'    => 'action_share_review_allowed',
                'icon'  => '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 100 100" style="enable-background:new 0 0 100 100;" xml:space="preserve"><path class="st0" d="M21.1,20.7c6,0.3,10.9,2.6,14.9,6.8c1.3,1.4,2.3,1.6,4,0.8c5.6-2.7,11.3-5.3,17-7.8c1.3-0.6,1.8-1.3,1.6-2.7 c-0.9-10.3,8.3-19,18.7-17.6c7.8,1.1,14.5,7.7,14.3,16.7c-0.2,7.1-3.5,12.3-9.9,15.1c-6.5,2.8-12.7,1.7-17.9-3.2 c-1.2-1.1-2-1.3-3.5-0.6c-5.7,2.7-11.4,5.3-17.2,7.9c-1.4,0.6-1.7,1.4-1.7,2.9c0,2.7-0.2,5.4-0.6,8.1c-0.2,1.3,0.1,2.1,1.2,2.7 c4,2.4,8.1,4.8,11.9,7.4c1.8,1.2,2.9,1,4.4-0.4c6.3-5.9,13.9-7.8,22.3-6c14.1,3,22.7,18,18.2,31.7c-3.8,11.7-14.8,19-26.9,17.3 C56.3,97.6,47,82.6,51.1,68.1c0.5-1.8,0.3-2.8-1.3-3.8c-4.1-2.4-8.1-4.9-12.2-7.4c-1.1-0.7-2-0.7-2.9,0.2 C23.3,67.5,4.1,62.5,0.4,45.3C-1.7,35.2,4.7,25,14.5,22C16.6,21.3,17.9,20.8,21.1,20.7z"/></svg>',
                'label' =>  __('Share Review', 'reviewx-pro'),
                'value' => '',
            ),
            array(
                'id'    => 'action_like_dislike_allowed',
                'icon'  => '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 100 100" style="enable-background:new 0 0 100 100;" xml:space="preserve"> <path class="st0" d="M50,25c0-2.3-1.9-4.2-4.2-4.2H24.2L27,7.6c0.1-0.3,0.1-0.6,0.1-1c0-1.3-0.5-2.5-1.4-3.3L22.4,0L1.8,20.6 C0.7,21.7,0,23.3,0,25v27.1c0,3.5,2.8,6.3,6.2,6.3h28.1c2.6,0,4.8-1.6,5.8-3.8l9.4-22c0.3-0.7,0.4-1.5,0.4-2.3V25z M93.7,41.7H65.6 c-2.6,0-4.8,1.6-5.7,3.8l-9.4,22C50.2,68.2,50,69,50,69.8V75c0,2.3,1.9,4.2,4.2,4.2h21.6L73,92.4c-0.1,0.3-0.1,0.6-0.1,1 c0,1.3,0.5,2.5,1.4,3.3l3.3,3.3l20.6-20.6c1.1-1.1,1.8-2.7,1.8-4.4V47.9C100,44.5,97.2,41.7,93.7,41.7L93.7,41.7z"/></svg>',
                'label' =>  __('Enable like dislike', 'reviewx-pro'),
                'value' => '',
            ),
            array(
                'id'    => 'action_review_auto_approval',
                'icon'  => '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 100 100" style="enable-background:new 0 0 100 100;" xml:space="preserve"><g><g><path class="st0" d="M20.9,61.6c1.1,1.1,1.6,2.6,1.4,4.1l-4.7,26.5c-0.4,2.5,1.2,4.8,3.7,5.3c1,0.2,2,0,2.8-0.4l23.7-12.7 c1.4-0.7,3-0.7,4.4,0l23.7,12.7c2.2,1.2,5,0.4,6.2-1.8c0.5-0.9,0.7-2,0.5-3l-4.7-26.5c-0.3-1.5,0.2-3.1,1.4-4.1l19.4-18.7 c1.8-1.8,1.9-4.7,0.1-6.5c-0.7-0.7-1.7-1.2-2.7-1.4l-26.6-3.7c-1.5-0.2-2.8-1.2-3.5-2.5L54.1,4.6C53,2.4,50.3,1.4,48,2.6 c-0.9,0.4-1.6,1.2-2.1,2.1L34.2,28.9c-0.7,1.4-2,2.3-3.6,2.5L4,35.1c-2.5,0.3-4.3,2.7-3.9,5.2c0.1,1,0.6,2,1.4,2.7L20.9,61.6z"/></g></g></svg>',
                'label' =>  __('Review Auto Approval', 'reviewx-pro'),
                'value' => '',
            ),            
            array(
                'id'    => 'action_multiple_review',
                'icon'  => '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 100 100" style="enable-background:new 0 0 100 100;" xml:space="preserve"><g><g><path class="st0" d="M20.9,61.6c1.1,1.1,1.6,2.6,1.4,4.1l-4.7,26.5c-0.4,2.5,1.2,4.8,3.7,5.3c1,0.2,2,0,2.8-0.4l23.7-12.7 c1.4-0.7,3-0.7,4.4,0l23.7,12.7c2.2,1.2,5,0.4,6.2-1.8c0.5-0.9,0.7-2,0.5-3l-4.7-26.5c-0.3-1.5,0.2-3.1,1.4-4.1l19.4-18.7 c1.8-1.8,1.9-4.7,0.1-6.5c-0.7-0.7-1.7-1.2-2.7-1.4l-26.6-3.7c-1.5-0.2-2.8-1.2-3.5-2.5L54.1,4.6C53,2.4,50.3,1.4,48,2.6 c-0.9,0.4-1.6,1.2-2.1,2.1L34.2,28.9c-0.7,1.4-2,2.3-3.6,2.5L4,35.1c-2.5,0.3-4.3,2.7-3.9,5.2c0.1,1,0.6,2,1.4,2.7L20.9,61.6z"/></g></g></svg>',
                'label' =>  __('Multiple Review', 'reviewx-pro'),
                'value' => '',
            ),              

        );
        return array_merge( $data, $settings );
    }    
    
    /**
     * Update review criteria when admin editing review 
     * 
     * @return void
     */		
    public function rx_review_criteria_update()
    {


        if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'editedcomment' ) {

            $review_id = sanitize_text_field( $_REQUEST['comment_ID'] );
            $post_type = get_post_type($_REQUEST['comment_post_ID']);
            if( \ReviewX_Helper::is_multi_criteria( $post_type ) ) {
                $criteria = $this->reviewx_get_review_criteria($_REQUEST, $post_type);
                if( count($criteria) ) {                
                    update_comment_meta( $review_id, 'reviewx_rating', $criteria, false );
                
                    $total_rating_sum 			= array_sum(array_values($criteria));
                    $rating 					= round( $total_rating_sum/count($criteria) );
                    update_comment_meta( $review_id, 'rating', $rating, false );
                }
            } else {
                $criteria 					    = \ReviewX_Helper::set_criteria_default_rating(sanitize_text_field($_REQUEST['rx-default-star-rating']), $post_type);
                update_comment_meta( $review_id, 'reviewx_rating', $criteria, false );                
                update_comment_meta( $review_id, 'rating', round(sanitize_text_field($_REQUEST['rx-default-star-rating'])), false ); 
            }

            if( isset($_REQUEST['rx_admin_edit_video_source_control']) && $_REQUEST['rx_admin_edit_video_source_control'] == 'external' ) {
                update_comment_meta($review_id, 'reviewx_video_url', esc_url($_REQUEST['rx_admin_edit_review_video_url']), false );       
            } else {
                update_comment_meta($review_id, 'reviewx_video_url', esc_url($_REQUEST['rx_admin_edit_review_video_url_self']), false );    
            }
            
            if( isset($_REQUEST['rx_admin_edit_video_source_control']) ){
                update_comment_meta($review_id, 'reviewx_video_source_control', sanitize_text_field($_REQUEST['rx_admin_edit_video_source_control']), false );
            }

            if( isset($_REQUEST['admin_edit_is_recommended']) ){
                update_comment_meta($review_id, 'reviewx_recommended', sanitize_text_field($_REQUEST['admin_edit_is_recommended']), false );
            }            
                        
        } 			
    }
    
    /**
     * Get review criteria from admin setting
     *
     * @param $postdata
     * @return array
     */
    public function reviewx_get_review_criteria($postdata, $post_type)
    {

        $data 				  = array();
        if( \ReviewX_Helper::check_post_type_availability( $post_type ) == TRUE ) {
            $reviewx_id       = \ReviewX_Helper::get_reviewx_post_type_id( $post_type );   
            $settings         = \ReviewX\Controllers\Admin\Core\ReviewxMetaBox::get_metabox_settings( $reviewx_id );  
            $review_criteria  = $settings->review_criteria;             
         } else {
            $settings 		  = \ReviewX\Controllers\Admin\Core\ReviewxMetaBox::get_option_settings();
            $review_criteria  = $settings->review_criteria;
         }

        if( ( is_array($postdata) && count($postdata ) > 0 ) && ( is_array($review_criteria) && count($review_criteria) > 0 ) ){
            $i=0;            
            foreach( $postdata as $id => $pd ){
                if( array_key_exists( $id, $review_criteria ) ){
                    $data[$id] = $pd;
                }
                if( array_key_exists( $postdata[ $i ]->name, $review_criteria ) ){
                    $data[$postdata[ $i ]->name ] = $postdata[ $i ]->value;
                }
                $i++;
            }
        }

        if( ( is_array($postdata) && count($postdata ) > 0 ) && ( is_array($review_criteria) && count($review_criteria) > 0 ) ):
            for( $i=0; $i<count($postdata); $i++ ):
                if( array_key_exists( $postdata[$i]['name'], $review_criteria ) ):
                    $data[$postdata[$i]['name']] = $postdata[$i]['value'];
                endif;
            endfor;
        endif;

        return $data;
    }
    
    /**
     * Hightlight review from admin edit review
     *
     * @param int
     * @return html
     */    
    public function run_reviewx_highlight_comment( $comment_id ) {
        $field_id_value = '';
        $field_id_value = get_comment_meta($comment_id, 'reviewx_highlight', true);
        $field_id_checked  = ''; 
        if($field_id_value == "reviewx_highlight_comment") $field_id_checked = 'checked="checked"';
        echo '<input type="checkbox" name="reviewx_highlight" class="reviewx_highlight-switcher" value="reviewx_highlight_comment"'.esc_attr( $field_id_checked ).'>
            <span class="slider round"></span>';
    }

    /**
     * Verify review from admin edit review
     *
     * @param int
     * @return html
     */    
    public function run_rx_admin_manual_verify( $comment_id ) {
        $field_id_value = '';
        $field_id_value = get_comment_meta($comment_id, 'verified', true);
        $field_id_checked  = ''; 
        if($field_id_value == 1 ) $field_id_checked = 'checked="checked"';
        echo '<input type="checkbox" name="reviewx_verified" class="reviewx_highlight-switcher" value="1"'.esc_attr( $field_id_checked ).'>
            <span class="slider round"></span>';
    }

    /**
     * Remove review image when admin editing review
     * @return void
     */
    public function rx_admin_remove_image()
    {
        check_ajax_referer( 'special-string', 'security' );
        $review_id 	= sanitize_text_field( $_POST['review_id'] );
        $img_id 	= sanitize_text_field( $_POST['img_id'] );
        $attachments= get_comment_meta( $review_id, 'reviewx_attachments', true );

        if( count($attachments) && array_key_exists('images', $attachments) ){
            $image_array = $attachments['images'];
            if( is_array($image_array) && in_array($img_id, $image_array) ) {
                $update_attachement = $this->removeFromImgArr( $image_array, $img_id );
                $attachments['images'] = $update_attachement;
                update_comment_meta( $review_id, 'reviewx_attachments', $attachments );
            }
            $return = array( 'success'=> true );
        } else {
            $return = array( 'success'=> false );
        }

        wp_send_json($return);
    }

    /**
     * @param $image_array
     * @param $img_id
     * @return array
     */
    public function removeFromImgArr( $image_array, $img_id )
    {
        unset($image_array[array_search( $img_id, $image_array )]);
        return array_values( $image_array );
    }

    /**
     * Add review image when admin editing review 
     * 
     * @return void
     */		
    public function rx_admin_add_image()
    {
        check_ajax_referer( 'special-string', 'security' );
        $review_id 		= sanitize_text_field( $_POST['review_id'] );
        $img_id 		= sanitize_text_field( $_POST['img_id'] );
        $photos         = $image_array = array();
        $attachments	= get_comment_meta( $review_id, 'reviewx_attachments', true );
        if( $attachments ) {
            $image_array = $attachments['images'];
            array_push($image_array,  $img_id);
            $attachments['images'] = $image_array;
            update_comment_meta( $review_id, 'reviewx_attachments', $attachments, false );
            $return = array( 'success' => true, 'attachment' => $attachments );
        } else {
            $attachments = array();
            array_push( $photos, $img_id );
            $attachments['images'] = $photos;
            update_comment_meta( $review_id, 'reviewx_attachments', $attachments, false );
            $return = array( 'success' => true, 'attachment' => $attachments );
        }

        wp_send_json($attachments);
        
    }

    /**
     * Remove review video when admin editing review 
     * 
     * @return void
     */		
    public function rx_admin_remove_video()
    {
        check_ajax_referer( 'special-string', 'security' );
        $review_id 		= sanitize_text_field( $_POST['review_id'] );
        $video_url 	    = get_comment_meta( $review_id, 'reviewx_video_url', false );
        if( $video_url ) {
            delete_comment_meta( $review_id, 'reviewx_video_url' );
            delete_comment_meta( $review_id, 'reviewx_video_source_control' );
            $return = array( 'success' => true );			
        } else {
            $return = array( 'success' => false );
        }
        wp_send_json($return);
    }
    
    /**
     * @param $data
     * @return void
     */    
    public function load_shortcode_graph_template( $data )
    {
        $criteria_arr   = $data['criteria_arr'];
        $criteria_count = $data['criteria_count'];
        ?>
        <div class="vertical">
            <?php 
                if( \ReviewX_Helper::is_multi_criteria( get_post_type( $data['prod_id'] ) ) ) { 
                    foreach ( $data['cri'] as $key => $single_criteria ) {
                    if ( ! empty( $single_criteria ) && ! empty( $criteria_arr ) && ! empty( $criteria_count ) ) {
                        $percentage = intval( round( ($criteria_arr[$key] / $criteria_count[$key])*100/5 ) );
                    }
            ?>
                <div class="progress-bar">
                    <p class="vertical_bar_label">
                        <?php echo esc_html( $single_criteria ); ?>
                    </p>
                    <div class="progress-track">
                        <div class="progress-fill">
                            <?php if( $percentage > 0 ): ?>
                                <span><?php echo intval( round( ($criteria_arr[$key] / $criteria_count[$key])*100/5 ) ); ?>%</span>
                            <?php else : ?>
                                <span><?php esc_html_e( '0%', 'reviewx-pro' ); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php } ?>
            <?php } else { 
                $rating_info = \ReviewX_Helper::total_rating_count($data['prod_id']);
                rsort($rating_info['rating_count']);
                foreach( $rating_info['rating_count'] as $rt ){
                    $percentage = \ReviewX_Helper::get_percentage($rating_info['review_count'][0]->total_review, $rt['total_review']);
                ?>
                    <div class="progress-bar">
                        <p class="vertical_bar_label">
                            <?php printf( __( '%s Star', 'reviewx' ), round( $rt['rating'] ) ); ?>
                        </p>
                        <div class="progress-track">
                            <div class="progress-fill">
                                <span><?php echo esc_attr($percentage); ?>%</span>
                            </div>
                        </div>
                    </div>                    
                <?php    
                }
            } 
            ?>    
        </div>
        <script>
            /*Vertical Bar*/
            jQuery('.vertical .progress-fill span').each(function(){
                var percent = jQuery(this).html();
                var pTop = 100 - ( percent.slice(0, percent.length - 1) ) + "%";
                jQuery(this).parent().css({
                    'height' : percent,
                    'top' : pTop
                });
            });
        </script>                
    <?php    
    }

    /**
     * @param $data
     * @return void
     */     
    public function load_shortcode_review_templates( $data, $args ) {

        $rx_elementor_template = '';
        if( $data['rx_post_type'] == 'product' ) {
            $settings 	     = \ReviewX\Controllers\Admin\Core\ReviewxMetaBox::get_option_settings();
            $template_style  = $settings->template_style;
            if( empty($atts['per_page']) ){
                $data['rx_per_page']       = $settings->review_per_page;                
            }

        } else if( \ReviewX_Helper::check_post_type_availability( $data['rx_post_type'] ) == TRUE ) {
           $reviewx_id       = \ReviewX_Helper::get_reviewx_post_type_id( $data['rx_post_type'] );   
           $settings         = \ReviewX\Controllers\Admin\Core\ReviewxMetaBox::get_metabox_settings( $reviewx_id );  
           $template_style   = $settings->template_style;
           if( empty($atts['per_page']) ){
            $data['rx_per_page']       = $settings->review_per_page;                
           }
        }
        
        // Check elementor template
        $rx_elementor_controller  = apply_filters( 'rx_load_elementor_style_controller', '' );
        $rx_elementor_template    = isset($rx_elementor_controller['rx_template_type']) ? $rx_elementor_controller['rx_template_type'] : null; 
        
        $rx_oxygen_controller  = apply_filters( 'rx_load_oxygen_style_controller', '' );
        $rx_oxygen_template    = isset($rx_oxygen_controller['rx_template_type']) ? $rx_oxygen_controller['rx_template_type'] : null;
       
        if( ! empty($rx_elementor_template) ) {

            switch ( $rx_elementor_template ) {
                case "template_style_two":
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/single-review/shortcode/style-two.php';
                break;
                default:
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/single-review/shortcode/style-one.php';
            }

        } else if( ! empty($rx_oxygen_template) ) {

            switch ( $rx_oxygen_template ) {
                case "box":
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/single-review/shortcode/style-two.php';
                break;
                default:
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/single-review/shortcode/style-one.php';
            }            

        } else {
            //Serve local template
            switch ( $template_style ) {
                case "template_style_two":
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/single-review/shortcode/style-two.php';
                break;
                default:                
                    include REVIEWX_PRO_PUBLIC_PATH . 'partials/single-review/shortcode/style-one.php';
            }

        }         

    }

    /**
     * @param $data
     * @return void
     */      
    public function get_review_summary() {
        
        global $product, $reviewx_shortcode; 
        $data                       = [];
        $criteria_arr 				= [];
        $criteria_count 			= [];
        $total_review_count 		= [];
        $rx_total_rating_sum        = 0;  
        if( isset($reviewx_shortcode) && !empty($reviewx_shortcode['rx_product_id']) ) {
            $post_id                = $reviewx_shortcode['rx_product_id'];
        } else {
            $post_id                = get_the_ID();
        }

        if( \ReviewX_Helper::check_post_type_availability( get_post_type( $post_id ) ) == TRUE ) {       
            $reviewx_id             = \ReviewX_Helper::get_reviewx_post_type_id( get_post_type( $post_id ) );
            $settings               = \ReviewX\Controllers\Admin\Core\ReviewxMetaBox::get_metabox_settings( $reviewx_id );
            $review_criteria 		= $settings->review_criteria;
            $allow_recommendation 	= $settings->allow_recommendation;
            $recommended_icon 		= $settings->recommend_icon_upload;
        } else if( get_post_type( $post_id ) == 'product' ) {
            $settings 				= \ReviewX\Controllers\Admin\Core\ReviewxMetaBox::get_option_settings();
            $review_criteria 		= $settings->review_criteria;
            $allow_recommendation 	= get_option( '_rx_option_allow_recommendation' );
            $recommended_icon       = get_option( '_rx_option_recommend_icon_upload' );            
        }

        $arg = array(
            'post_type' 			=> get_post_type( $post_id ),
            'post_id' 				=> $post_id,
            'type'  				=> array('comment','review'),
            'status'                =>'approve'
        );
        $comment_lists 				= get_comments( $arg );
        $rx_total_rating_count = 0;
        $rx_count_total_rating_avg = 0;

        if ( ! empty( $comment_lists ) ) {
            foreach( $comment_lists as $comment_list ) {

                $get_criteria 			= get_comment_meta($comment_list->comment_ID, 'reviewx_rating', true);
                $criteria_query_obj 	= (object)$get_criteria;
                $get_rating_val 		= get_comment_meta($comment_list->comment_ID, 'rating', true);
                if ( !empty( $get_rating_val ) ){
                    $rx_total_rating_count = array_push( $total_review_count, $get_rating_val );
                    $rx_total_rating_sum += $get_rating_val;
                }

                if( is_array($review_criteria) ) {
                    foreach( $review_criteria as $key => $single_criteria ) {
                        if( !array_key_exists( $key, $criteria_arr ) ){
                            if (isset($criteria_query_obj->$key)) {
                                $criteria_arr[$key] = !empty($criteria_query_obj->$key) ? $criteria_query_obj->$key : 0;
                                $criteria_count[$key] = 1;
                            }
                        } else {
                            if (isset($criteria_query_obj->$key)) {
                                $criteria_arr[$key] += !empty($criteria_query_obj->$key) ? $criteria_query_obj->$key : 0;
                                $criteria_count[$key] += 1;
                            }
                        }						
                    }
                }
            }
        }
        
        if( ! empty ( $rx_total_rating_sum ) ) {
            $rx_count_total_rating_avg = round( $rx_total_rating_sum / $rx_total_rating_count, 2);
            if( is_nan($rx_count_total_rating_avg) ) {
                $rx_count_total_rating_avg = 5;
            }
        }
        
        $data['recommendation']         = $allow_recommendation;
        $data['recommended_icon']       = $recommended_icon;
        $data['total_rating_sum']       = $rx_total_rating_sum;
        $data['total_rating_count']     = $rx_total_rating_count;
        $data['total_rating_average']   = $rx_count_total_rating_avg;
        $data['review_criteria']        = $review_criteria;
        $data['criteria_arr']           = $criteria_arr;
        $data['criteria_count']         = $criteria_count;
        $data['recommendation_count']   = reviewx_product_recommendation_count( $post_id );
        $data['prod_id']                = $post_id;
        $data['hossain']    = $reviewx_shortcode;
        return $data;
        
    }

    /**
     * @param $data
     * @return void
     */      
    public function reviewx_manual_review(){

        global $wpdb;
        check_ajax_referer( 'special-string', 'security' );
        $attachments = $photos 	= array();
        $review_status = 0; 
        $prod_id = $order_id = $review_title = $review_text = $recommend_status = $user_id = $author = $email = $default_rating = $post_type = $custom_author = $custom_author_email = $enable_verified_purchase = $enable_custom_date = $custom_date ='';
        $user_id                    = 0;
        $forminput 					= wp_unslash($_POST['formInput']);
        $comment_agent          	= $_SERVER['HTTP_USER_AGENT'];
        $total_rating_sum 		    = 0;

        for( $i=0; $i < count($forminput); $i++ ) {

            if( $forminput[$i]['name'] == "manual_review_product" ):
                $prod_id = sanitize_text_field($forminput[$i]['value']);
            endif;

            if( $forminput[$i]['name'] == "manual_review_user" ):
                $user_id = sanitize_text_field($forminput[$i]['value']);
            endif;

            if( $forminput[$i]['name'] == "manual_review_author" ):
                $custom_author = sanitize_text_field($forminput[$i]['value']);
            endif;

            if( $forminput[$i]['name'] == "manual_review_author_email" ):
                $custom_author_email = sanitize_email($forminput[$i]['value']);
            endif;

            if( $forminput[$i]['name'] == "manual_review_title" ):
                $review_title = sanitize_text_field($forminput[$i]['value']);
            endif;

            if( $forminput[$i]['name'] == "manual_review_text" ):
                $review_text = sanitize_text_field($forminput[$i]['value']);
            endif;

            if( $forminput[$i]['name'] == "rx-image[]" ):
                array_push( $photos, sanitize_text_field($forminput[$i]['value']) );
            endif;

            if( $forminput[$i]['name'] == "manual_review_recommend_status" ):
                $recommend_status = sanitize_text_field($forminput[$i]['value']);
            endif;

            if( $forminput[$i]['name'] == "rx-default-star" ):
                $default_rating = sanitize_text_field($forminput[$i]['value']);
            endif; 

            if( $forminput[$i]['name'] == "manual_review_verified" ):
                $enable_verified_purchase = sanitize_text_field($forminput[$i]['value']);
            endif;

            if( $forminput[$i]['name'] == "manual_review_date" ):
                $custom_date = sanitize_text_field($forminput[$i]['value']);
            endif;    

            if( $forminput[$i]['name'] == "manual_review_custom_date" ):
                $enable_custom_date = sanitize_text_field($forminput[$i]['value']);
            endif;                    
            
        }

        if(is_numeric($user_id)){
            $user                   = get_userdata($user_id);
            $author 				= $user->display_name;
            $email 					= $user->user_email;
            $user_id 				= $user->ID;
        } else if( $user_id == 'anonymously'){
            $author 				= '';
            $email 					= '';
            $user_id 				= '';
        } else if( $user_id == 'custom'){
            $author 				= $custom_author;
            $email 					= $custom_author_email;
            $user_id 				= '';
        }

        $attachments['images'] 		= $photos;
        $criteria 					= [];
        if( \ReviewX_Helper::is_multi_criteria( get_post_type($prod_id) ) ) {
            $criteria 				= $this->reviewx_get_review_criteria($forminput, get_post_type($prod_id));
            $total_rating_sum 		= array_sum($criteria);
            $rating 				= round( $total_rating_sum/count($criteria) );
        } else {            
            $criteria 				= \ReviewX_Helper::set_criteria_default_rating($default_rating, get_post_type($prod_id));
            $rating 				= round($default_rating); 
        }

        if( \ReviewX_Helper::check_post_type_availability( get_post_type($prod_id) ) == TRUE ) {
            $reviewx_id             = \ReviewX_Helper::get_reviewx_post_type_id( get_post_type($prod_id) );
            $settings               = \ReviewX\Controllers\Admin\Core\ReviewxMetaBox::get_metabox_settings( $reviewx_id );
            $allow_title 	        = \ReviewX_Helper::get_post_meta( $reviewx_id, 'allow_review_title', true );
            if( class_exists('ReviewXPro') ) {
                $auto_approval 		= $settings->disable_auto_approval;
            } else {
                $auto_approval      = 1;
            }   
        } else {
            $allow_title 	        = \ReviewX_Helper::get_option( 'allow_review_title' );   
            $auto_approval 		    = \ReviewX_Helper::get_option( 'disable_auto_approval' );  
        }

        if( $auto_approval == 1 ) {
            $review_status = 1;
        }

        if($enable_custom_date == 1){
            $date = $custom_date;
        } else {
            $date = date( 'Y-m-d H:i:s' );
        }
        
        $comment_data = array(
            'comment_post_ID'      => absint($prod_id),
            'comment_author'       => $author,
            'comment_author_email' => $email,
            'comment_author_url'   => '',
            'comment_author_IP'    => $_SERVER['REMOTE_ADDR'],
            'comment_date'         => $date,
            'comment_date_gmt'     => $date,
            'comment_content'      => $review_text,
            'comment_karma'        => '',
            'comment_agent'        => $comment_agent,
            'comment_type'         => 'review',
            'comment_parent'       => '',
            'user_id'              => $user_id,
        );

        $id = wp_new_comment($comment_data);

        if( $id ) {

            $ratingOption = get_option('_rx_product_' . $prod_id . '_rating') ? : [];
            $criData = [];
            foreach ($criteria as $key => $value) {
                $criData[$key] = _get($ratingOption[$key], 0) + $value;
            }
            update_option('_rx_product_' . $prod_id . '_rating', array_merge([
                'total_review' =>  _get($ratingOption['total_review'], 0) + 1,
                'total_rating' =>  _get($ratingOption['total_rating'], 0) + $rating
            ], $criData));
    
            // Save comment extra data
            $posts = array(
                'comment_id' 	=> $id,
                'post_data' 	=> wp_unslash($_POST['formInput']),
                'signature'		=> 3
            );
            apply_filters( 'rx_save_extra_post_data', $posts );
    
            add_comment_meta( $id, 'rating', $rating, false );
            add_comment_meta( $id, 'reviewx_rating', $criteria, false );
            add_comment_meta( $id, 'reviewx_recommended', $recommend_status, false );
            if( !empty( $enable_verified_purchase ) ) {
                update_comment_meta( $id, 'verified', 1, false );
            } else {

                if( \ReviewX_Helper::check_guest_purchase_verified_badge($email, $prod_id) ){
                    update_comment_meta( $id, 'verified', 1, false );
                } else {
                    update_comment_meta( $id, 'verified', 0, false );
                }
                
            }  
    
            // Check review title is enabled
            if( $allow_title == 1 ) {
                add_comment_meta( $id, 'reviewx_title', $review_title, false );
            }      
            
            if( !empty( $attachments['images'] ) ) {
                add_comment_meta( $id, 'reviewx_attachments', $attachments, false );
            }

            $comment_table = $wpdb->prefix . 'comments';
            $wpdb->update( $comment_table, array( 'comment_approved' => $review_status ), array( 'comment_ID' => $id ), array( '%s' ), array( '%d' ) );

            if ( $prod_id ) {
                self::clear_transients( $prod_id );
            } 

            (new \ReviewX\Controllers\Admin\Criteria\CriteriaController())->storeCriteria($id, $criteria);
            \ReviewX\Modules\Gatekeeper::setLogForUser($comment_data['comment_post_ID'], false, $id);

        }

        $success_status = ($id) ? true : false;
        $return = array(
            'success' => $success_status,
            'status'  => $review_status
        );
        
        wp_send_json($return);
        
    }

    /**
	 * Ensure product average rating and review count is kept up to date.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function clear_transients( $prod_id ) {
            
		if ( 'product' === get_post_type( $prod_id ) ) {
            
			$product = wc_get_product( $prod_id );
			$product->set_rating_counts( \ReviewX_Helper::get_rating_counts_for_product( $prod_id ) );
            $product->set_average_rating( \ReviewX_Helper::get_average_rating_for_product( $prod_id ) );
			$product->set_review_count( \ReviewX_Helper::get_review_count_for_product( $prod_id ) );
            $product->save();
            update_post_meta( $prod_id, '_wc_average_rating', \ReviewX_Helper::get_average_rating_for_product( $prod_id ) );
        }
        
    }
    
    /**
	 * Load post type wise criteria
	 *
	 * @param int $post_id Post ID.
	 */    
    public function load_post_type_wise_criteria(){
        
        check_ajax_referer( 'special-string', 'security' );
        $post_id = sanitize_text_field($_POST['post_id']);
        $wc_is_enabled = \ReviewX_Helper::check_wc_is_enabled();
        if( \ReviewX_Helper::check_post_type_availability( get_post_type($post_id) ) == TRUE ) {
            $reviewx_id 				= \ReviewX_Helper::get_reviewx_post_type_id( get_post_type($post_id) );
            $settings                   = \ReviewX\Controllers\Admin\Core\ReviewxMetaBox::get_metabox_settings( $reviewx_id );
            echo apply_filters( 'rx_load_product_rating_type', $settings );
        } else if( get_post_type($post_id) == 'product' && $wc_is_enabled == 1 ) {
            $settings 				= \ReviewX\Controllers\Admin\Core\ReviewxMetaBox::get_option_settings();
            echo apply_filters( 'rx_load_product_rating_type', $settings );
        }
        wp_die();
        
    }

    /**
	 * Upload video for guest review
	 *
	 * @param array
	 */    
    public function rx_guest_review_video(){
    
        check_ajax_referer( 'special-string', 'security' );
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $attach_url         = $upload_message = '';         
        $uploadedfile       = $_FILES['file'];
        $parent_post_id     = isset( $_POST['post_id'] ) ? sanitize_text_field($_POST['post_id']) : 0;
        $valid_formats      = array("mp4"); 
        $max_file_size      = 1024 * 500000; // in kb
        $wp_upload_dir      = wp_upload_dir();
        $path               = $wp_upload_dir['path'] . '/';            
        $extension          = pathinfo( $_FILES['file']['name'], PATHINFO_EXTENSION );
        $new_filename       = $this->generate_random_code( 20 )  . '.' . $extension;
        if ( $_FILES['file']['error'] == 0 ) {
            if ( $_FILES['file']['size'] > $max_file_size ) {
                $upload_message[] = sprintf(__("%s is too large!.", 'reviewx-pro'), $_FILES['file']['name']);
            } elseif( ! in_array( strtolower( $extension ), $valid_formats ) ){
                $upload_message[] = sprintf(__("%s is not a valid format.", 'reviewx-pro'), $_FILES['file']['name']);
            } else{ 
                if( move_uploaded_file( $_FILES["file"]["tmp_name"], $path.$new_filename ) ) { 
                    $filename = $path.$new_filename;
                    $filetype = wp_check_filetype( basename( $filename ), null );
                    $wp_upload_dir = wp_upload_dir();
                    $attachment = array(
                        'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ), 
                        'post_mime_type' => $filetype['type'],
                        'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                        'post_content'   => '',
                        'post_status'    => 'inherit'
                    );
                    $attach_id      = wp_insert_attachment( $attachment, $filename, $parent_post_id );
                    $attach_data    = wp_generate_attachment_metadata( $attach_id, $filename ); 
                    wp_update_attachment_metadata( $attach_id, $attach_data );
                    $attach_url     = wp_get_attachment_url( $attach_id );
                }
            }
        }
        $data = array('message' => $upload_message, 'url'=>$attach_url);
        wp_send_json($data);

    }

    /**
     * Generated random code 
     *
     * @param integer
     * @return void
     **/ 
    public function generate_random_code($length=10) 
    {
        $string = '';
        $characters = "23456789ABCDEFHJKLMNPRTVWXYZabcdefghijklmnopqrstuvwxyz";      
        for ($p = 0; $p < $length; $p++) {
            $string .= $characters[mt_rand(0, strlen($characters)-1)];
        }
        return $string;
    }

    public function wp_localize_script_args($hook) {
        $rx_save_settings = __('Save', 'reviewx-pro' );

        if ($hook == 'reviewx_page_reviewx-quick-setup') {
            $rx_save_settings = __('Save & Next', 'reviewx-pro' );
            $rx_btn_cancel_next = __('Cancel & Next', 'reviewx-pro' );
        } else {
            $rx_btn_cancel_next = __('Cancel', 'reviewx-pro' );
        }

        $args = apply_filters( 'reviewx_js_filter', array(
            'ajax_admin_url'            =>  admin_url('admin-ajax.php'),
            'ajax_nonce' 				=> wp_create_nonce('special-string'),
		    'rx_review_text_error' 		=> __( 'Review can\'t be empty', 'reviewx-pro' ),
		    'rx_rating_satisfaction' 	=> __( 'Please rate your satisfaction', 'reviewx-pro' ),
		    'review_success_title'		=> __( 'Success', 'reviewx-pro' ),
            'review_success_msg'		=> __( 'Settings saved successfully!', 'reviewx-pro' ),
            'review_sending_msg'		=> __( 'Sent successfully!', 'reviewx-pro' ),
            'review_failed_title'		=> __( 'Error', 'reviewx-pro' ),
            'review_submit_failed'		=> __( 'Something went wrong!', 'reviewx-pro' ),
		    'review_failed_msg'			=> __( 'Settings saved fail!', 'reviewx-pro' ),
		    'rx_name_error'				=> __( 'Name can\'t be empty', 'reviewx-pro' ),
		    'rx_email_error'			=> __( 'Email can\'t be empty', 'reviewx-pro' ),
            'rx_invalid_email_error'	=> __( 'Invalid email', 'reviewx-pro' ),
            'rx_setting_sending'	    => __( 'Sending...', 'reviewx-pro' ),
		    'rx_setting_saving'	        => __( 'Saving...', 'reviewx-pro' ),
		    'rx_save_setting'	        => $rx_save_settings,
            'rx_before_email_sent'      => __( 'Save & Send Email', 'reviewx-pro' ),
            'already_review_msg'		=> __( 'This email has already given review on this product', 'reviewx-pro' ),
            'rx_criteria_error'		    => __( 'Your product review criteria field is empty', 'reviewx-pro' ),
            'rx_finalize_title'			=> __( 'Good job!',  'reviewx-pro' ),
            'rx_finalize_msg'         	=> __( 'Setup is Complete.',  'reviewx-pro' ),
            'rx_remidner_title'	        => __( 'Warning!', 'reviewx-pro' ),
            'rx_remidner_msg'	        => __( 'A review reminder email will be sent out to your customers for their unreviewed order items.', 'reviewx-pro' ),  
            'rx_btn_cancel_next'	    => $rx_btn_cancel_next,
            'rx_btn_email_sent'         => __( 'Send Email', 'reviewx-pro' ),
            'rx_test_email_title'       => __( 'Test Mail', 'reviewx-pro' ),
            'rx_test_email_message'     => __( 'Test mail sent successfully!', 'reviewx-pro' ),
            'rx_test_email_valid'       => __( 'Email is invalid!', 'reviewx-pro' ),
            'rx_mail_sent_msg'		    => __( 'Mail Sent successfully!', 'reviewx-pro' ),
            'rx_mail_default_template'  => '<table class="body" style="border-collapse: collapse; border-spacing: 0; vertical-align: top; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; height: 100% !important; width: 100% !important; min-width: 100%; -moz-box-sizing: border-box; -webkit-box-sizing: border-box; box-sizing: border-box; -webkit-font-smoothing: antialiased !important; -moz-osx-font-smoothing: grayscale !important; background-color: #f1f1f1; color: #444; font-family: Helvetica,Arial,sans-serif; font-weight: normal; padding: 0; margin: 0; text-align: left; font-size: 14px; mso-line-height-rule: exactly; line-height: 140%;" border="0" width="100%" cellspacing="0" cellpadding="0"><tbody><tr style="padding: 0; vertical-align: top; text-align: left;"><td class="body-inner wp-mail-smtp" style="word-wrap: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; border-collapse: collapse !important; vertical-align: top; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444; font-family: Helvetica,Arial,sans-serif; font-weight: normal; padding: 0; margin: 0; font-size: 14px; mso-line-height-rule: exactly; line-height: 140%; text-align: center;" align="center" valign="top"><table class="container" style="border-collapse: collapse; border-spacing: 0; padding: 0; vertical-align: top; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; width: 600px; margin: 0 auto 30px auto; text-align: inherit;" border="0" cellspacing="0" cellpadding="0"><tbody><tr style="padding: 0; vertical-align: top; text-align: left;"><td class="content" style="word-wrap: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; border-collapse: collapse !important; vertical-align: top; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444; font-family: Helvetica,Arial,sans-serif; font-weight: normal; margin: 0; text-align: left; font-size: 14px; mso-line-height-rule: exactly; line-height: 140%; background-color: #ffffff; padding: 60px 75px 45px 75px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd; border-left: 1px solid #ddd; border-top: 3px solid #809eb0;" align="left" valign="top"><div class="success"><p class="text-large" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444; font-family: Helvetica,Arial,sans-serif; font-weight: normal; padding: 0; text-align: left; mso-line-height-rule: exactly; line-height: 140%; margin: 0 0 15px 0; font-size: 14px;">'.__('Hey ,', 'reviewx-pro').'[CUSTOMER_NAME]</p><p class="text-large" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444; font-family: Helvetica,Arial,sans-serif; font-weight: normal; padding: 0; text-align: left; mso-line-height-rule: exactly; line-height: 140%; margin: 0 0 15px 0; font-size: 14px;">'.__('Thank you for purchasing items from the ','reviewx-pro').' [SHOP_NAME].'.__(' We love to know your experiences with the product(s) that you recently purchased.', 'reviewx-pro').'</p><p class="text-large" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444; font-family: Helvetica,Arial,sans-serif; font-weight: normal; padding: 0; text-align: left; mso-line-height-rule: exactly; line-height: 140%; margin: 0 0 15px 0; font-size: 14px;">'.__('You can browse a list of orders from your account page and can submit your feedback based on multiple criteria that we specially designed for you. To browse your orders: ', 'reviewx-pro').'[MY_ORDERS_PAGE]</p><p class="text-large" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444; font-family: Helvetica,Arial,sans-serif; font-weight: normal; padding: 0; text-align: left; mso-line-height-rule: exactly; line-height: 140%; margin: 0 0 15px 0; font-size: 14px;">[ORDER_DATE]'.__(' you placed the order ', 'reviewx-pro').'[ORDER_ID]</p><p class="text-large" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444; font-family: Helvetica,Arial,sans-serif; font-weight: normal; padding: 0; text-align: left; mso-line-height-rule: exactly; line-height: 140%; margin: 0 0 15px 0; font-size: 14px;">[ORDER_ITEMS]</p><p class="text-large" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444; font-family: Helvetica,Arial,sans-serif; font-weight: normal; padding: 0; text-align: left; mso-line-height-rule: exactly; line-height: 140%; margin: 0 0 15px 0; font-size: 14px;">'.__('Your feedback means a lot to us! Thanks for being a loyal ', 'reviewx-pro').'[SHOP_NAME] '.__('customer.', 'reviewx-pro').'</p><p class="text-large" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444; font-family: Helvetica,Arial,sans-serif; font-weight: normal; padding: 0; text-align: left; mso-line-height-rule: exactly; line-height: 140%; margin: 0 0 15px 0; font-size: 14px;">'.__('Regards,', 'reviewx-pro').'</p><p class="text-large" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444; font-family: Helvetica,Arial,sans-serif; font-weight: normal; padding: 0; text-align: left; mso-line-height-rule: exactly; line-height: 140%; margin: 0 0 15px 0; font-size: 14px;">'.__('Team ', 'reviewx-pro').'[SHOP_NAME]</p></div><h6>'.__('If you want to unsubscribe this email please go to this ', 'reviewx-pro').'[UNSUBSCRIBE_LINK]</h6></td></tr></tbody></table></td></tr></tbody></table>',
            'rx_mail_reset_template'    => __( 'Reset Template', 'reviewx-pro'),
            'rx_mail_asked_for_reset'   => __( 'Would you like to reset your email content?', 'reviewx-pro'),
            'rx_mail_reset_confirm_button_text' => __( 'Reset', 'reviewx-pro'),
            'rx_mail_reset_success_text' => __( 'Successfully email template reset!', 'reviewx-pro' ),
            'review_added_sucess'		 => __( 'Review added successfully!', 'reviewx-pro' ),
            'rx_select_product'          => __( 'Select Product/CPT', 'reviewx-pro' ),
            'rx_select_user'		     => __( 'Select User', 'reviewx-pro' ),
        ) );

        return $args;
    }
      
}