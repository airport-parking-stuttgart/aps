<?php

use ReviewX\Controllers\Admin\Email\EmailSettings;

/**
 * Thing need to process once the reviewx plugin activation is done and loaded.
 * 
 * @return void
 */
function reviewx_pro_get_started() {
	// Check if WooCommerce installed and activated
    if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		add_action( 'admin_notices', 'reviewx_pro_woocommerce_missing_wc_notice' );
	}
}

add_action( 'admin_init', 'reviewx_pro_get_started' );

/**
 * Admin notice if WooCommerce is missing
 * 
 * @return void
 */
function reviewx_pro_woocommerce_missing_wc_notice() {
    $screen = get_current_screen();
    if( ( $screen->base == 'reviewx_page_reviewx-review-email' || $screen->base == 'reviewx_page_rx-wc-settings' || $screen->base == 'reviewx_page_reviewx-quick-setup'  ) ){
        $reviewx_notice = sprintf(
            __( 'ReviewX Pro requires WooCommerce to be installed and active to working properly. %s', 'reviewx-pro' ),
            '<a href="' . esc_url( admin_url( 'plugin-install.php?s=WooCommerce&tab=search&type=term' ) ) . '">' . __( 'Please click on this link and install WooCommerce', 'reviewx-pro' ) . '</a>'
        );
        printf( '<div class="error notice notice-warning is-dismissible"><p style="padding: 5px 0">%s</p></div>', $reviewx_notice );
    }
}

add_filter( 'rx_load_filter_review_template', 'load_filter_review_template' );

/**
* Add button plugin setting page link
*
* @return void
*/
add_filter( 'plugin_action_links_' . REVIEWX_PRO_BASENAME, 'reviewx_pro_admin_settings_link', 10, 2 );

function reviewx_pro_admin_settings_link( $links ) {
    if( class_exists('WooCommerce') ) {               
        $links[] = '<a href="'.esc_url('https://reviewx.io/support/').'">'.esc_html__('Premium Support', 'reviewx-pro' ).'</a>';
    }
    return $links;    
}

/**
 * Load admin notice if reviewx
 */
function reviewx_install_core_notice() {

	$has_installed = get_plugins();
	$button_text = isset( $has_installed['reviewx/reviewx.php'] ) ? __( 'Activate Now!', 'reviewx-pro' ) : __( 'Install Now!', 'reviewx-pro' );

	if( ! class_exists( 'ReviewX' ) ) :
	?>
		<div class="error notice is-dismissible">
			<p><strong><?php esc_html_e( 'ReviewX Pro', 'reviewx-pro' ); ?></strong> <?php esc_html_e( 'requires', 'reviewx-pro' ); ?> <strong><?php esc_html_e( 'ReviewX', 'reviewx-pro' ); ?></strong> <?php esc_html_e( 'core plugin to be installed. Please get the plugin now!', 'reviewx-pro' ); ?> <button id="reviewx-install-core" class="button button-primary"><?php echo esc_html( $button_text ); ?></button></p>
		</div>
	<?php
	endif;
}

add_action( 'admin_notices', 'reviewx_install_core_notice' );


/**
 * Load template
 * 
 * @return void
 */
function load_filter_review_template( $args = null ) {

    if( $args['post_type'] == 'product' ) {
        $settings 	     = \ReviewX\Controllers\Admin\Core\ReviewxMetaBox::get_option_settings();
        $template_style  = $settings->template_style;

    } else if( \ReviewX_Helper::check_post_type_availability( $args['post_type'] ) == TRUE ) {
       $reviewx_id       = \ReviewX_Helper::get_reviewx_post_type_id( $args['post_type'] );   
       $settings         = \ReviewX\Controllers\Admin\Core\ReviewxMetaBox::get_metabox_settings( $reviewx_id );  
       $template_style   = $settings->template_style;            
    }

    $rx_elementor_controller  = apply_filters( 'rx_load_elementor_style_controller', '' );
    $rx_elementor_template    = isset($rx_elementor_controller['rx_template_type']) ? $rx_elementor_controller['rx_template_type'] : null;

    $rx_oxygen_controller  = apply_filters( 'rx_load_oxygen_style_controller', '' );
    $rx_oxygen_template    = isset($rx_oxygen_controller['rx_template_type']) ? $rx_oxygen_controller['rx_template_type'] : null;

    //Check elementor template 
    if( ! empty($rx_elementor_template) ) {

        switch ( $rx_elementor_template ) {
            case 'template_style_two':
                include REVIEWX_PRO_PUBLIC_PATH . 'partials/single-review/filter/style-two.php';
            break;
            default:
                include REVIEWX_PRO_PUBLIC_PATH . 'partials/single-review/filter/style-one.php';
        }
        
    } else if( ! empty($rx_oxygen_template) ) {

        switch ( $rx_oxygen_template ) {
            case 'box':
                include REVIEWX_PRO_PUBLIC_PATH . 'partials/single-review/filter/style-two.php';
            break;
            default:
                include REVIEWX_PRO_PUBLIC_PATH . 'partials/single-review/filter/style-one.php';
        }        

    } else if( ! empty($rx_oxygen_template) ) {

        switch ( $rx_oxygen_template ) {
            case 'box':
                include REVIEWX_PRO_PUBLIC_PATH . 'partials/single-review/filter/style-two.php';
            break;
            default:
                include REVIEWX_PRO_PUBLIC_PATH . 'partials/single-review/filter/style-one.php';
        }

    } else {
        //Serve local template	
        switch ( $template_style ) {
            case 'template_style_two':
                include REVIEWX_PRO_PUBLIC_PATH . 'partials/single-review/filter/style-two.php';
            break;
            default:
                include REVIEWX_PRO_PUBLIC_PATH . 'partials/single-review/filter/style-one.php';
        }

    } 
    
    reviewx_review_filter_query_html( $args );
}


/**
 * Add gravatar class
 * 
 * @return void
 */
function rx_gravatar_class($class = null) {
    $class = str_replace('class="avatar', 'class="avatar img-fluid', $class);
    return $class;
}

add_filter('get_avatar','rx_gravatar_class');

/**
 * Get total reviewer
 * @param int
 * @return void
 */
function rx_total_reviewer( $post_id = null, $post_type = 'product' ) {

    global $wpdb;
    $rx_comment_table = $wpdb->prefix . 'comments';
    if( !empty($post_id) ){
        $data = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT comment_author_email FROM $rx_comment_table WHERE comment_approved = '1' AND comment_author_email !='' AND comment_post_ID = %d AND comment_parent = %d", (int) $post_id, 0 ) );        
    } else {
        if( empty($post_type) ){
            $post_type = 'product';
        }
        $data = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT comment_author_email FROM $rx_comment_table WHERE comment_post_ID in (
            SELECT ID FROM $wpdb->posts WHERE post_type = '$post_type' AND post_status = 'publish')
            AND comment_approved = '1' AND comment_author_email !=''" 
        ) );
    }

    if( $data && count($data ) ) {
        return count($data);
    }
    return 0;

}

/**
 * Get comment author id
 * @param int
 * @return int
 */
function get_comment_author_id( $comment_id = null ) {

    global $wpdb;
    $rx_comment_table = $wpdb->prefix . 'comments';
    $data = $wpdb->get_results( $wpdb->prepare( "SELECT user_id FROM $rx_comment_table WHERE comment_id = %d", $comment_id ) );
    if( $data && !empty($data[0]->user_id) ) {
        return $data[0]->user_id;
    }
    return 0; 
    
}

/**
 * Get comment parent author id
 * @param int
 * @return int
 */
function check_comment_parent_id( $comment_id = null ) {

    global $wpdb;
    $rx_comment_table = $wpdb->prefix . 'comments';
    $data = $wpdb->get_results( $wpdb->prepare( "SELECT comment_parent FROM $rx_comment_table WHERE comment_id = %d", $comment_id ) );
    if( $data && !empty($data[0]->comment_parent) ) {
        return $data[0]->comment_parent;
    }
    return 0; 

}

/**
 * Get product in the review ajax filtering
 * @param int
 * @return int
 */
function get_review_product_id( $comment_id = null ) {
    global $wpdb;
    $rx_comment_table = $wpdb->prefix . 'comments';
    $data             = $wpdb->get_results( $wpdb->prepare( "SELECT comment_post_ID FROM $rx_comment_table WHERE comment_id = %d", $comment_id ) );
    if( $data && !empty($data[0]->comment_post_ID) ) {
        return $data[0]->comment_post_ID;
    }
    return 0; 
}

/**
 * Check comment has child
 * @param int
 * @return void
 */
function check_review_has_child( $comment_id = null ) {
    return get_comments( [ 'parent' => $comment_id, 'count' => true ] ) > 0;
}

/**
 * Retrieve child comment
 * @param int
 * @return array
 */
function reviewx_get_comment_by_id( $comment_id = null ) {

    global $wpdb;
    $rx_comment_table = $wpdb->prefix . 'comments';
    $data = $wpdb->get_results( $wpdb->prepare( "SELECT comment_id FROM $rx_comment_table WHERE comment_parent = %d", $comment_id ) );

    if( $data && !empty($data[0]->comment_id) ) {
        $child_comment_id = isset( $data[0]->comment_id ) ? $data[0]->comment_id : 0;
        $comment = get_comment(  $child_comment_id );
        if ( ! empty( $comment ) ) {
            return $comment;
        } else {
            return '';
        }
    }

}

/**
 * Check video type
 * @param string
 * @return void
 */
function determine_video_url_type( $url = null ) {

    $yt_rx = '/^((?:https?:)?\/\/)?((?:www|m)\.)?((?:youtube\.com|youtu.be))(\/(?:[\w\-]+\?v=|embed\/|v\/)?)([\w\-]+)(\S+)?$/';
    $has_match_youtube = preg_match($yt_rx, $url, $yt_matches);

    $vm_rx = '/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([‌​0-9]{6,11})[?]?.*/';
    $has_match_vimeo = preg_match($vm_rx, $url, $vm_matches);

    //Then we want the video id which is:
    if($has_match_youtube) {
        $video_id = $yt_matches[5]; 
        $type = 'youtube';
    }
    elseif($has_match_vimeo) {
        $video_id = $vm_matches[5];
        $type = 'vimeo';
    }
    else {
        $video_id = 0;
        $type = 'none';
    }

    $data['video_id'] = $video_id;
    $data['video_type'] = $type;

    return $data;
    
}

/**
 * Retrieve vimeo video thumb
 * @param string
 * @return void
 */
function get_vimeo_video_thumb( $id = null ) {

    $data = file_get_contents("http://vimeo.com/api/v2/video/$id.json");
    $data = json_decode($data);
    return $data[0]->thumbnail_medium;

}

function reviewx_check_divi_active() {
    if( get_option('template') == 'Divi' ) {
        return true;
    }
    return false;
}

function shortcode_divi_review_list( $post_id = null, $reviewx_shortcode = null ) {

    if( ! empty( $post_id ) ) {
        $divi_settings = get_post_meta( $post_id, '_rx_option_divi_settings', true );
    }

    // if( empty( $divi_settings ) ) {
    //     return;
    // }
    if( ! empty( $divi_settings ) && reviewx_check_divi_active() && $divi_settings['rvx_review_list'] != 'off'  ) { 
        return true;
    } else if( isset($reviewx_shortcode['rx_list']) && $reviewx_shortcode['rx_list'] =='on' ) {
        return true;
    } else {
        if( ( !isset($reviewx_shortcode) || $reviewx_shortcode['rx_product_id']=='' ) && reviewx_check_divi_active() ) {
            return true;            
        } else {
            return true;
        }

    }
    
}

/**
 * Check shortcode and divi review filter
 *  
 * @param array
 * @return void
 */
function shortcode_divi_review_filter($post_id, $reviewx_shortcode) {
    
    $divi_settings = get_post_meta( $post_id, '_rx_option_divi_settings', true );

    if( ! empty( $divi_settings ) ) {
        if( reviewx_check_divi_active() && $divi_settings['rvx_review_filter'] != 'off'  ) {
            return true;
        } 
    }

    else if( isset( $reviewx_shortcode[ 'rx_filter' ] ) && $reviewx_shortcode[ 'rx_filter' ] =='off' ) {
        return true;         
    }  
    else {
        if( ( !isset($reviewx_shortcode) || empty($reviewx_shortcode['rx_product_id']) ) && reviewx_check_divi_active() ) {            
            return true;            
        } else {
            return true;
        }
    } 

}

function shortcode_divi_review_form($post_id, $reviewx_shortcode) {

    $divi_settings = get_post_meta( $post_id, '_rx_option_divi_settings', true );

    if( ! empty( $divi_settings ) ) {
        if( reviewx_check_divi_active() && $divi_settings['rvx_review_form'] != 'off'  ) {
            return true;
        }
    }
     
    else if( isset($reviewx_shortcode['rx_form']) && $reviewx_shortcode['rx_form'] =='on' ) {
        return true;           
    }  
    else {
        if( ( !isset($reviewx_shortcode) || empty($reviewx_shortcode['rx_product_id']) ) && !reviewx_check_divi_active() ) {            
            return true;            
        } else {
            return true;
        }
    } 

}