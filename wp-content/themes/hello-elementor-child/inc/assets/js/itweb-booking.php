<?php

/**
 * Plugin Name: IT-Web24 Booking
 * Plugin URI: https://it-web24.com/
 * Description: IT-Web24 Booking
 * Version: 1.0
 * Author: it-web24
 * Author URI: https://it-web24.com/
 * License: GPLv2 or later
 */
define('itweb', plugin_dir_url(__FILE__));

//global $wpdb;
//$hotelTransfersTB = $wpdb->prefix . 'itweb_hotel_transfers';
//$charset_collate = $wpdb->get_charset_collate();
//$wpdb->query("CREATE TABLE $hotelTransfersTB (
//        `id` int(11) NOT NULL AUTO_INCREMENT,
//		`product_id` int(11) not null,
//		`user_id` int(11) default null,
//		`order_id` int(11) not null,
//		`datefrom` date default null,
//		`dateto` date default null,
//		`transfer_vom_hotel` time default null,
//		`ankunftszeit_ruckflug` time default null,
//		`hinflug_nummer` varchar(20) not null,
//		`ruckflug_nummer` varchar(20) not null,
//		`token` varchar(10) not null,
//		PRIMARY KEY (`id`)
//	) $charset_collate;");

//require_once plugin_dir_path(__FILE__) . '\classes\Database.php';
spl_autoload_register(function ($class_name) {
    $file_path = plugin_dir_path(__FILE__) . "/classes/" . $class_name . ".php";
    if (file_exists($file_path)) {
        require_once $file_path;
    }
});

/**
 *
 * include custom functions
 *
 */
require_once('functions.php');

function itweb_session()
{
    session_start();
}

add_action('wp_loaded', 'itweb_session');

register_activation_hook(__FILE__, 'itweb_install');
global $wnm_db_version;
$wnm_db_version = "1.0";

function itweb_install()
{
    global $wpdb;
    global $wnm_db_version;
    require_once 'install.php';

    add_option("wnm_db_version", $wnm_db_version);
}

add_action('admin_menu', 'itweb_admin_menu');

function itweb_admin_menu()
{
    // Buchungen Template
    add_menu_page('Buchungen', 'Buchungen', 'read', 'buchungen', function () {
        require_once plugin_dir_path(__FILE__) . '/templates/buchung/index.php';
    });
    add_submenu_page('buchungen', 'Buchung Erstellen', 'Buchung Erstellen', 'read', 'buchung-erstellen', function () {
        require_once plugin_dir_path(__FILE__) . '/templates/buchung/erstellen.php';
    }, 110);
    add_submenu_page('buchungen', 'Buchung Bearbeiten', 'Buchung Bearbeiten', 'read', 'buchung-bearbeiten', function () {
        require_once plugin_dir_path(__FILE__) . '/templates/buchung/bearbeiten.php';
    }, 110);
	add_submenu_page('buchungen', 'Stornos', 'Stornos', 'read', 'stornos', function () {
        require_once plugin_dir_path(__FILE__) . '/templates/buchung/stornos.php';
    }, 110);
    add_submenu_page('buchungen', 'Abgestellte PKW', 'Abgestellte PKW', 'read', 'buchung-abgestellte-pkw', function () {
        require_once plugin_dir_path(__FILE__) . '/templates/buchung/abgestellte-pkw.php';
    }, 110);

    // Fahrerportal Template
    add_menu_page('Fahrerportal', 'Fahrerportal', 'read', 'fahrerportal', function () {
        require_once plugin_dir_path(__FILE__) . '/templates/fahrerportal/index.php';
    });
    add_submenu_page('fahrerportal', 'Anreiseliste', 'Anreiseliste', 'read', 'anreiseliste', function () {
        require_once plugin_dir_path(__FILE__) . '/templates/fahrerportal/anreiseliste.php';
    }, 110);
    add_submenu_page('fahrerportal', 'Abreiseliste', 'Abreiseliste', 'read', 'abreiseliste', function () {
        require_once plugin_dir_path(__FILE__) . '/templates/fahrerportal/abreiseliste.php';
    }, 110);
    add_submenu_page('fahrerportal', 'Tagesabschluss', 'Tagesabschluss', 'read', 'tagesabschluss', function () {
        require_once plugin_dir_path(__FILE__) . '/templates/fahrerportal/tagesabschluss.php';
    }, 110);

    // Fahrerportal Template
    add_menu_page('Produkte', 'Produkte', 'read', 'produkte', function () {
        require_once plugin_dir_path(__FILE__) . '/templates/produkte/index.php';
    });
    add_submenu_page('produkte', 'Betreiber', 'Betreiber', 'read', 'betreiber', function () {
        require_once plugin_dir_path(__FILE__) . '/templates/produkte/betreiber.php';
    }, 110);
    add_submenu_page('produkte', 'Neuanlage', 'Neuanlage', 'read', 'produkte-neuanlage', function () {
        require_once plugin_dir_path(__FILE__) . '/templates/produkte/neuanlage.php';
    }, 110);
    add_submenu_page('produkte', 'Produkte Bearbeiten', 'Produkte Bearbeiten', 'read', 'produkte-bearbeiten', function () {
        require_once plugin_dir_path(__FILE__) . '/templates/produkte/produkte-bearbeiten.php';
    }, 110);

    add_submenu_page('produkte', 'Preisschienen', 'Preisschienen', 'read', 'prices', function () {
        require_once plugin_dir_path(__FILE__) . '/templates/produkte/prices/index.php';
    }, 110);

    add_submenu_page('produkte', 'Zusatzleistungen', 'Zusatzleistungen', 'read', 'zusatzleistungen', function () {
        require_once plugin_dir_path(__FILE__) . '/templates/produkte/zusatzleistungen-bearbeitung.php';
    }, 110);

    // Vermittler Template
    add_menu_page('Vermittler', 'Vermittler', 'read', 'vermittler', function () {
        require_once plugin_dir_path(__FILE__) . '/templates/vermittler/index.php';
    });
    add_submenu_page('vermittler', 'Neuanlage', 'Neuanlage', 'read', 'vermittler-neuanlage', function () {
        require_once plugin_dir_path(__FILE__) . '/templates/vermittler/neuanlage.php';
    }, 110);;
    add_submenu_page('vermittler', 'Bearbeiten', 'Bearbeiten', 'read', 'vermittler-bearbeiten', function () {
        require_once plugin_dir_path(__FILE__) . '/templates/vermittler/bearbeiten.php';
    }, 110);
	
	// Berichte Template
    add_menu_page('Berichte', 'Berichte', 'read', 'berichte', function () {
        require_once plugin_dir_path(__FILE__) . '/templates/berichte/index.php';
    });
    add_submenu_page('berichte', 'Umsatz', 'Umsatz', 'read', 'umsatz', function () {
        require_once plugin_dir_path(__FILE__) . '/templates/berichte/umsatz.php';
    }, 110);
	add_submenu_page('berichte', 'Umsatz Parkplätze', 'Umsatz Parkplätze', 'read', 'umsatz-lots', function () {
        require_once plugin_dir_path(__FILE__) . '/templates/berichte/lots.php';
    }, 110);
	add_submenu_page('berichte', 'Kontingent', 'Kontingent', 'read', 'kontingent', function () {
        require_once plugin_dir_path(__FILE__) . '/templates/berichte/kontingent.php';
    }, 110);

    // Statistics Template
    add_menu_page('Statistics', 'Statistics', 'read', 'statistics', function () {
        require_once plugin_dir_path(__FILE__) . '/templates/statistics/index.php';
    });
}

function itweb_options_page()
{
    ?>
    <form action='options.php' method='post'>
        <h2>IT-Web24 Booking</h2>
        <?php
        settings_fields('pluginitwebPage');
        do_settings_sections('pluginitwebPage');
        submit_button();
        ?>
    </form>
    <?php
}

add_action('admin_init', 'smartcms_itweb_settings_init');
function smartcms_itweb_settings_init()
{
    register_setting('pluginitwebPage', 'smartcms_itweb_settings');
    add_settings_section('smartcms_pluginPage_section', __('', 'wordpress'), '', 'pluginitwebPage');
    add_settings_field('', '', 'smartcms_itweb_parameters', 'pluginitwebPage', 'smartcms_pluginPage_section');
}

add_action('wp_loaded', 'register_all_scripts');
function register_all_scripts()
{
    // register styles
    wp_enqueue_style('bootstrap-css', '/wp-content/plugins/itweb-booking/assets/bootstrap-4.5.3-dist/css/bootstrap.min.css');
    wp_enqueue_style('airdatepicker-css', '/wp-content/plugins/itweb-booking/assets/air-datepicker/air-datepicker.min.css');
    wp_enqueue_style('airdatepicker-css', '/wp-content/plugins/itweb-booking/assets/air-datepicker/air-datepicker.min.css');
    wp_enqueue_style('timepicker-css', '/wp-content/plugins/itweb-booking/assets/timepicker/timepicker.min.css');
    wp_enqueue_style('timepicker-css', '/wp-content/plugins/itweb-booking/assets/timepicker/timepicker.min.css');
    wp_enqueue_style('fullcalendar-core', '/wp-content/plugins/itweb-booking/assets/fullcalendar/packages/core/main.css');
    wp_enqueue_style('fullcalendar-daygrid', '/wp-content/plugins/itweb-booking/assets/fullcalendar/packages/daygrid/main.css');
    wp_enqueue_style('fullcalendar-timegrid', '/wp-content/plugins/itweb-booking/assets/fullcalendar/packages/timegrid/main.css');
    wp_enqueue_style('fullcalendar-main', '/wp-content/plugins/itweb-booking/assets/fullcalendar/packages/list/main.css');
    wp_enqueue_style('admin-css', '/wp-content/plugins/itweb-booking/assets/css/admin.css');
	wp_enqueue_style('datatables-css', '/wp-content/plugins/itweb-booking/assets/datatables/datatables.min.css');

    // register scripts
    wp_enqueue_script('popper-js', '/wp-content/plugins/itweb-booking/assets/bootstrap-4.5.3-dist/js/popper.min.js', 0, 1, true);
    wp_enqueue_script('bootstrap-js', '/wp-content/plugins/itweb-booking/assets/bootstrap-4.5.3-dist/js/bootstrap.min.js', 0, 1, true);
    wp_enqueue_script('airdatepicker-js', '/wp-content/plugins/itweb-booking/assets/air-datepicker/air-datepicker.min.js', 0, 1, true);
    wp_enqueue_script('airdatepicker-de', '/wp-content/plugins/itweb-booking/assets/air-datepicker/i18n/datepicker.de.js', 0, 1, true);
    wp_enqueue_script('timepicker-js', '/wp-content/plugins/itweb-booking/assets/timepicker/timepicker.js', 0, 1, true);
    wp_enqueue_script('fullcalendar-core', '/wp-content/plugins/itweb-booking/assets/fullcalendar/packages/core/main.js');
    wp_enqueue_script('fullcalendar-interactionmain', '/wp-content/plugins/itweb-booking/assets/fullcalendar/packages/interaction/main.js');
    wp_enqueue_script('fullcalendar-daygrid', '/wp-content/plugins/itweb-booking/assets/fullcalendar/packages/daygrid/main.js');
    wp_enqueue_script('fullcalendar-timegrid', '/wp-content/plugins/itweb-booking/assets/fullcalendar/packages/timegrid/main.js');
    wp_enqueue_script('fullcalendar-list', '/wp-content/plugins/itweb-booking/assets/fullcalendar/packages/list/main.js');
    wp_enqueue_script('admin-js', '/wp-content/plugins/itweb-booking/assets/js/admin.js', 0, 1, true);
	wp_enqueue_script('datatables-js', '/wp-content/plugins/itweb-booking/assets/datatables/datatables.min.js', 0, 1, true);
	
	wp_enqueue_script('buttons1-js', 'https://cdn.datatables.net/buttons/1.1.2/js/dataTables.buttons.min.js', 0, 1, true);
	wp_enqueue_script('expert-libs-js', '//cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js', 0, 1, true);
	wp_enqueue_script('pdfmaker1-js', '//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js', 0, 1, true);
	wp_enqueue_script('pdfmaker2-js', '//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js', 0, 1, true);
	wp_enqueue_script('buttons2-js', '//cdn.datatables.net/buttons/1.1.2/js/buttons.html5.min.js', 0, 1, true);
	wp_enqueue_script('buttons3-js', '//cdn.datatables.net/buttons/1.1.2/js/buttons.print.min.js', 0, 1, true);
	wp_enqueue_script('buttons4-js', '//cdn.datatables.net/buttons/1.1.2/js/buttons.colVis.min.js', 0, 1, true);
}

/**
 *
 * insert product.js file only on shop and product page
 *
 */
function product_js_script()
{
    if (is_shop() || is_product()) {
        wp_enqueue_script('product-js', '/wp-content/plugins/itweb-booking/assets/js/product.js', 0, 1, true);
    }
}

add_action('wp_enqueue_scripts', 'product_js_script');

function smartcms_itweb_parameters()
{
    $options = get_option('smartcms_itweb_settings');
    global $wpdb, $categories;
}

add_action('widgets_init', 'itweb_widgets');
function itweb_widgets()
{
    register_widget('itweb_class');
}

add_action('plugins_loaded', 'itweb_load');
function itweb_load()
{
    global $mfpd;
    $mfpd = new itweb_class();
}

class itweb_class extends WP_Widget
{

    function __construct()
    {
        parent::__construct('itweb_id', 'IT-Web24 Booking', array(
            'description' => ''
        ));
        add_action('add_meta_boxes', array(
            $this,
            'itweb_add_tab_admin_product'
        ), 10, 2);
    }

    function itweb_add_tab_admin_product($post_type, $post)
    {
        global $wp_meta_boxes;
        $wp_meta_boxes['product']['normal']['core']['itweb']['title'] = "IT-Web24 Booking";
        $wp_meta_boxes['product']['normal']['core']['itweb']['id'] = "itweb";
        $wp_meta_boxes['product']['normal']['core']['itweb']['callback'] = "itweb_add_tab_admin_product_display";
    }
}

function itweb_add_tab_admin_product_display()
{
    global $wpdb;

    $postId = isset($_GET['post']) ? $_GET['post'] : null;

    if ($postId) {
        wp_register_script('itweb-product-script', itweb . 'js/product.js');
        wp_enqueue_script('itweb-product-script');
        wp_register_style('itweb-product-css', itweb . 'css/product.css');
        wp_enqueue_style('itweb-product-css');

        ?>
        <div>
            <h1>Template</h1>
        </div>
        <?php
    }
}

add_action('woocommerce_product_meta_start', 'itweb_fontend_single');

function itweb_fontend_single()
{
    global $product;
    global $wpdb;
    $proId = $product->get_id();
}

add_filter('woocommerce_cart_item_price', 'itweb_change_product_price_display', 10, 3);
function itweb_change_product_price_display($price, $product)
{
    global $wpdb;
    $proId = $product["product_id"];

    $datefrom = $_SESSION["parklots"][$proId]["datefrom"];
    $timefrom = $_SESSION["parklots"][$proId]["timefrom"];
    $dateto = $_SESSION["parklots"][$proId]["dateto"];
    $timeto = $_SESSION["parklots"][$proId]["timeto"];
    $checked_services = $_SESSION['parklots'][$proId]["checked_services"];

    $customString = "";

    if ($datefrom) {
        $customString .= "<br>From: " . dateFormat($datefrom, 'de') . ' ' . $timefrom;
    }
    if ($dateto) {
        $customString .= "<br>To: " . dateFormat($dateto, 'de') . ' ' . $timeto;
    }
    if($checked_services){
        $customString .= '<br/><br/><strong>Additional Services:</strong>';
        foreach(explode(',', $checked_services) as $service_id){
            $service = Database::getInstance()->getAdditionalService($service_id);
            $customString .= '<br/>' . $service->name;
        }
    }
    return $price . $customString;
}

/**
 * Handle a custom 'customvar' query var to get orders with the 'customvar' meta.
 * @param array $query - Args for WP_Query.
 * @param array $query_vars - Query vars from WC_Order_Query.
 * @return array modified $query
 */
function handle_custom_query_var($query, $query_vars)
{
    if (!empty($query_vars['meta_query'])) {
        foreach ($query_vars['meta_query'] as $item) {
            $query['meta_query'][] = array(
                'key' => $item['key'],
                'value' => $item['value'],
                'compare' => $item['compare']
            );
        }
    }

    return $query;
}

add_filter('woocommerce_order_data_store_cpt_get_orders_query', 'handle_custom_query_var', 10, 2);




// create Fahrer Role
add_role('fahrer', 'Fahrer', [
    'edit_posts' => 1,
    //    'publish_posts' => 1,
    'read' => 1,
    'export' => 1,
    'edit_others_products' => 1,
    'edit_published_products' => 1,
]);

// define months
define('_MONTHS', [1 => 'Januar', 2 => 'Februar', 3 => 'März', 4 => 'April', 5 => 'Mai', 6 => 'Juni', 7 => 'Juli', 8 => 'August', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Dezember'], false);


add_action('woocommerce_order_status_changed', 'on_change_order', 10, 3);
function on_change_order($order_id, $old_status, $new_status)
{
    global $wpdb;
    $table = $wpdb->prefix . 'itweb_orders';
    $data = [];
    if ($new_status == 'cancelled') {
        Orders::cancel($order_id);

        $data['deleted'] = 1;
    } else {
        $data['deleted'] = 0;
    }
    $wpdb->update($table, $data, ['order_id' => $order_id]);
}

// add custom field on product page
add_action('elementor/widget/render_content', function ($content, $widget) {
    if ('wc-add-to-cart' === $widget->get_name() || 'woocommerce-product-add-to-cart' === $widget->get_name()) {
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/custom-product-fields.php';

        $content = ob_get_clean() . '<br/>' . $content;
    }

    // if($widget->get_name() == 'button'){
    //     die(var_dump($content));
    // }

    return $content;
}, 10, 2);

/**
 *
 * change product page price html
 *
 */
add_filter('woocommerce_get_price_html', 'change_product_price_html', 9999, 2);
function change_product_price_html($price_html, $product)
{
    $dateFrom = dateFormat($_GET['datefrom']);
    $dateTo = dateFormat($_GET['dateto']);
    $price = Pricelist::calculate($product->get_id(), $dateFrom, $dateTo);
    $price = Discounts::checkDiscounts($product->get_id(), $price, $dateFrom, $dateTo);
    return get_woocommerce_currency_symbol() . '<span class="front-price">' . to_float($price) . '</span>';

}

add_action('woocommerce_before_calculate_totals', 'itweb_add_custom_price', 10, 1);

function itweb_add_custom_price($cart_object)
{
    global $wpdb;

    if (is_admin() && !defined('DOING_AJAX'))
        return;
    session_start();
    foreach ($cart_object->get_cart() as $cart_item) {
        $proId = $cart_item['data']->id;
        unset($_SESSION['parklots'][$proId]);
        $dateFrom = dateFormat($cart_item['datefrom'], 'en');
        $dateTo = dateFormat($cart_item['dateto'], 'en');

        $price = Pricelist::calculate($proId, $dateFrom, $dateTo);
        $price = Discounts::checkDiscounts($proId, $price, $dateFrom, $dateTo);

        $_SESSION['parklots'][$proId] = [
            'product_id' => $proId,
            'datefrom' => dateFormat($cart_item['datefrom'], 'en'),
            'dateto' => dateFormat($cart_item['dateto'], 'en'),
            'timefrom' => $cart_item['timefrom'],
            'timeto' => $cart_item['timeto']
        ];

        if (isset($cart_item['checked_services']) && strlen($cart_item['checked_services']) > 0) {
            foreach (explode(',', $cart_item['checked_services']) as $service_id) {
                $service = Database::getInstance()->getAdditionalService($service_id);
                $price += $service->price;
            }

            $_SESSION['parklots'][$proId]['checked_services'] = $cart_item['checked_services'];
        }

        $_SESSION['parklots'][$proId]['price'] = $price;

        $cart_item['data']->set_price($price);
    }
}

add_filter('woocommerce_add_cart_item_data', 'wdm_add_item_data', 10, 3);
function wdm_add_item_data($cart_item_data, $product_id, $variation_id)
{
    $cart_item_data['datefrom'] = sanitize_text_field($_REQUEST['datefrom']);
    $cart_item_data['dateto'] = sanitize_text_field($_REQUEST['dateto']);
    $cart_item_data['timefrom'] = sanitize_text_field($_REQUEST['timefrom']);
    $cart_item_data['timeto'] = sanitize_text_field($_REQUEST['timeto']);
    $cart_item_data['checked_services'] = sanitize_text_field($_REQUEST['checked_services']);

    return $cart_item_data;
}

//add_filter('woocommerce_get_item_data', 'wdm_add_item_meta', 10, 2);
/**
 * Display information as Meta on Cart page
 * @param  [type] $item_data [description]
 * @param  [type] $cart_item [description]
 * @return [type]            [description]
 */
//function wdm_add_item_meta($item_data, $cart_item)
//{
//    if (array_key_exists('datefrom', $cart_item)) {
//        $custom_details = $cart_item['datefrom'];
//
//        $item_data[] = array(
//            'key' => 'Date_from',
//            'value' => $custom_details
//        );
//    }
//    if (array_key_exists('dateto', $cart_item)) {
//        $custom_details = $cart_item['dateto'];
//
//        $item_data[] = array(
//            'key' => 'Date_to',
//            'value' => $custom_details
//        );
//    }
//    if (array_key_exists('timefrom', $cart_item)) {
//        $custom_details = $cart_item['timefrom'];
//
//        $item_data[] = array(
//            'key' => 'Time_from',
//            'value' => $custom_details
//        );
//    }
//    if (array_key_exists('timeto', $cart_item)) {
//        $custom_details = $cart_item['timeto'];
//
//        $item_data[] = array(
//            'key' => 'Time_to',
//            'value' => $custom_details
//        );
//    }
//
//    return $item_data;
//}

//add_action('woocommerce_checkout_create_order_line_item', 'wdm_add_custom_order_line_item_meta', 10, 4);
//function wdm_add_custom_order_line_item_meta($item, $cart_item_key, $values, $order)
//{
//    if (!array_key_exists('datefrom', $values)) {
//        $item->add_meta_data('Date_from', $values['datefrom']);
//    }
//    if (!array_key_exists('dateto', $values)) {
//        $item->add_meta_data('Date_to', $values['dateto']);
//    }
//    if (!array_key_exists('timefrom', $values)) {
//        $item->add_meta_data('Time_from', $values['timefrom']);
//    }
//    if (!array_key_exists('timeto', $values)) {
//        $item->add_meta_data('Time_to', $values['timeto']);
//    }
//}

add_filter( 'woocommerce_is_sold_individually', 'remove_quantity_fields', 10, 2 );
function remove_quantity_fields( $return, $product ) {
    return true;
}


add_filter('woocommerce_checkout_order_processed', 'itweb_order_complete');
function itweb_order_complete($order_id)
{
    global $wpdb;
    $order = new WC_Order($order_id);
    $items = $order->get_items();
    foreach ($items as $item) {
        $product_id = $item['product_id'];
        $data = $_SESSION['parklots'][$product_id];
//        die(json_encode($_SESSION["parklots"]));

        $wpdb->insert($wpdb->prefix . "itweb_orders", [
            'date_from' => date('Y-m-d H:i', strtotime($data['datefrom'] . ' ' . $data['timefrom'])),
            'date_to' => date('Y-m-d H:i', strtotime($data['dateto'] . ' ' . $data['timeto'])),
            'product_id' => (int)$data['product_id'],
            'order_id' => $item['order_id'],
            'nr_people' => 1,
            'out_flight_number' => '',
            'return_flight_number' => '',
        ]);

        add_post_meta($order_id, 'Anreisedatum', dateFormat($data["datefrom"]), true);
        add_post_meta($order_id, 'first_anreisedatum', dateFormat($data["datefrom"]), true);
        add_post_meta($order_id, 'Abreisedatum', dateFormat($data["dateto"]), true);
        add_post_meta($order_id, 'first_abreisedatum', dateFormat($data["dateto"]), true);
        add_post_meta($order_id, 'Uhrzeit von', $data["timefrom"], true);
        add_post_meta($order_id, 'Uhrzeit bis', $data["timeto"], true);
//        add_post_meta($order_id, 'Hinflugnummer', $data["hinflugnummer"], true);
//        add_post_meta($order_id, 'Rückflugnummer', $data["ruckflugnummer"], true);
        add_post_meta($order_id, 'Personenanzahl', 1, true);
//        add_post_meta($order_id, 'Hersteller', $data["hersteller"], true);
//        add_post_meta($order_id, 'TYP', $data["typ"], true);
//        add_post_meta($order_id, 'Farbe', $data["farbe"], true);
//        add_post_meta($order_id, 'Autoreinigung', $data["autoreinigung"], true);
//        add_post_meta($order_id, 'Werkstattservice', $data["werkstattservice"], true);
//        add_post_meta($order_id, 'Tresor', $data["tresor"], true);

        update_post_meta($order_id, 'token', generateToken(5));

        unset($_SESSION["parklots"][$product_id]);
    }
    unset($_SESSION["parklots"]);
    session_unset();
    session_destroy();
}

if(!function_exists('getDaysBetween2Dates')){
    function getDaysBetween2Dates(DateTime $date1, DateTime $date2, $absolute = true)
    {
        $interval = $date2->diff($date1);
        // if we have to take in account the relative position (!$absolute) and the relative position is negative,
        // we return negatif value otherwise, we return the absolute value
        return ((!$absolute and $interval->invert) ? -$interval->days : $interval->days) + 1;
    }
}

/**
 * Get enabled payment methods
 */
function get_enabled_payment_methods(){
    $gateways = WC()->payment_gateways->get_available_payment_gateways();
    $enabled_gateways = [];

    if( $gateways ) {
        foreach( $gateways as $gateway ) {

            if( $gateway->enabled == 'yes' ) {

                $enabled_gateways[] = $gateway;

            }
        }
    }

    return $enabled_gateways;
}

require_once('rest-routes.php');

/*
  Linking elementor products with itweb product tables
*/
require_once('elementor-itweb-link.php');

/**
 *
 * Product Details
 *
 */
require_once('product-details.php');