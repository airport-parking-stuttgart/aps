<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );

if ( !function_exists( 'chld_thm_cfg_parent_css' ) ):
    function chld_thm_cfg_parent_css() {
        wp_enqueue_style( 'chld_thm_cfg_parent', trailingslashit( get_template_directory_uri() ) . 'style.css', array( 'wp-bootstrap-starter-bootstrap-css','wp-bootstrap-starter-fontawesome-cdn' ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'chld_thm_cfg_parent_css', 10 );

// Shortcode to output custom PHP in Elementor
function wpc_elementor_shortcode( $atts ) {
    echo "This is my custom PHP output in Elementor!";
  echo "<h1>test</h1>";
}
add_shortcode( 'my_elementor_php_output', 'wpc_elementor_shortcode');
// END ENQUEUE PARENT ACTION



// Remove personal Information in Profile
add_action( 'personal_options', array ( 'T5_Hide_Profile_Bio_Box', 'start' ) );
/**
 * Captures the part with the biobox in an output buffer and removes it.
 *
 * @author Thomas Scholz, <info@toscho.de>
 *
 */
class T5_Hide_Profile_Bio_Box
{
    /**
     * Called on 'personal_options'.
     *
     * @return void
     */
    public static function start()
    {
        $action = ( IS_PROFILE_PAGE ? 'show' : 'edit' ) . '_user_profile';
        add_action( $action, array ( __CLASS__, 'stop' ) );
        ob_start();
    }

    /**
     * Strips the bio box from the buffered content.
     *
     * @return void
     */
    public static function stop()
    {
        $html = ob_get_contents();
        ob_end_clean();

        // remove the headline
        $headline = __( IS_PROFILE_PAGE ? 'About Yourself' : 'About the user' );
        $html = str_replace( '<h2>' . $headline . '</h2>', '', $html );

        // remove the table row
        $html = preg_replace( '~<tr class="user-description-wrap">\s*<th><label for="description".*</tr>~imsUu', '', $html );
		$html = preg_replace( '~<tr class="user-profile-picture">\s*<th>.*</tr>~imsUu', '', $html );
		$html = preg_replace( '~<tr class="user-facebook-wrap">\s*<th>.*</tr>~imsUu', '', $html );
		$html = preg_replace( '~<tr class="user-instagram-wrap">\s*<th>.*</tr>~imsUu', '', $html );
		$html = preg_replace( '~<tr class="user-linkedin-wrap">\s*<th>.*</tr>~imsUu', '', $html );
		$html = preg_replace( '~<tr class="user-myspace-wrap">\s*<th>.*</tr>~imsUu', '', $html );
		$html = preg_replace( '~<tr class="user-pinterest-wrap">\s*<th>.*</tr>~imsUu', '', $html );
		$html = preg_replace( '~<tr class="user-soundcloud-wrap">\s*<th>.*</tr>~imsUu', '', $html );
		$html = preg_replace( '~<tr class="user-tumblr-wrap">\s*<th>.*</tr>~imsUu', '', $html );
		$html = preg_replace( '~<tr class="user-twitter-wrap">\s*<th>.*</tr>~imsUu', '', $html );
		$html = preg_replace( '~<tr class="user-youtube-wrap">\s*<th>.*</tr>~imsUu', '', $html );
		$html = preg_replace( '~<tr class="user-wikipedia-wrap">\s*<th>.*</tr>~imsUu', '', $html );
        print $html;
    }
}

/* Disable toolbar in frontend
add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar() {
	if (!current_user_can('administrator') && !is_admin()) {
	  show_admin_bar(false);
	}
}*/

add_filter( 'woocommerce_customer_meta_fields', 'xbs_remove_shipping_fields' );
function xbs_remove_shipping_fields( $show_fields ) {
    unset( $show_fields['shipping'] );
    return $show_fields;
}

// Redirect to Statistics after log in.
function loginRedirect( $redirect_to, $request, $user ){
	if( is_array( $user->roles ) ) { // check if user has a role
		return "/wp-admin/admin.php?page=statistics";
    }
}
add_filter("login_redirect", "loginRedirect", 10, 3);

// update toolbar
function update_adminbar($wp_adminbar) {

  /// add SitePoint menu item
    $wp_adminbar->add_node([
    'id' => 'apm_logo',
    'title' => '<img src="http://airport-parking-stuttgart.de/wp-content/uploads/2021/04/logo-e.png" />',
    'href' => '#',
    'meta' => [
      'target' => 'apm'
    ]
  ]);
  $wp_adminbar->add_node([
    'id' => 'apm',
    'title' => 'Airport Parking Management',
    'href' => '#',
    'meta' => [
      'target' => 'apm'
    ]
  ]);
}

// admin_bar_menu hook
add_action('admin_bar_menu', 'update_adminbar', 999);



function collectiveray_load_js_script() {
  wp_enqueue_script('main-js', get_stylesheet_directory_uri() . '/main.js', array('jquery'), '', false);
}

add_action('wp_enqueue_scripts', 'collectiveray_load_js_script');
//umleitung login seite für kunde

function custom_login_redirect_it_web24()
{
    if (!is_user_logged_in()) {
        if (strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false || strpos($_SERVER['REQUEST_URI'], 'wp-login') !== false) {
            wp_redirect(home_url('/login'));
            exit();
        }
    }
}
add_action('login_init', 'custom_login_redirect_it_web24');


