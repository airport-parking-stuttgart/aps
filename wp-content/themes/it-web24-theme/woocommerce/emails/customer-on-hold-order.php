<?php
/**
 * Admin new order email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/admin-new-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates/Emails/HTML
 * @version 3.7.0
 */
defined('ABSPATH') || exit;
require_once ABSPATH . 'wp-content/plugins/itweb-parking-booking/lib/phpqrcode/phpqrcode.php';
require_once get_template_directory() . '/lib/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

//if (!$_SESSION["extern"] || $_SESSION['output']['@attributes']['Result'] == 'OK'){

global $wpdb;
$order_id = $order->get_id();
$isShuttle = !empty(get_post_meta($order_id, '_persons_nr', true));
$parklot = $wpdb->get_row("
select parklots.*, orders.datefrom, orders.dateto, orders.order_id, country.country
from {$wpdb->prefix}itweb_parklots parklots, {$wpdb->prefix}itweb_orders orders, {$wpdb->prefix}itweb_countries country
where orders.parklot_id = parklots.id and orders.order_id = {$order_id} and country.id = parklots.country_id");
$product = $wpdb->get_row("select productid from {$wpdb->prefix}itweb_products where lotid = {$parklot->id}");
$image = $product ? wp_get_attachment_image_src(get_post_thumbnail_id($product->productid), 'single-post-thumbnail') : null;

// Check if booking is extern
if(get_post_meta($order_id, '_booking_ref', true) == null || get_post_meta($order_id, '_booking_ref', true) == "")
	$_SESSION["extern"] = 0;
else
	$_SESSION["extern"] = 1;

// pdf

ob_start();
if (get_template_directory() . '/invoices/email-invoice.php') {
    include(get_template_directory() . '/invoices/email-invoice.php');
}
$content = ob_get_clean();
// instantiate and use the dompdf class
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($content);

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

$file = $dompdf->output();
	$fileName = date('d-m-Y') . '-Rechnung-' . $order_id;
if(!file_exists(ABSPATH . 'wp-content/uploads/new-order-invoices')){
    mkdir(ABSPATH . 'wp-content/uploads/new-order-invoices');
}
$filePath = ABSPATH . 'wp-content/uploads/new-order-invoices/' . $fileName . '.pdf';
$pdf = fopen($filePath, 'w');
fwrite($pdf, $file);
fclose($pdf);
// end pdf
//}

echo "<pre>";
//print_r($parking);
echo "</pre>";


?>
    <style>
        * {
            color: black !important;
        }

        h1 {
            text-align: center;
        }

        .header {
            margin-top: 30px;
            margin-bottom: 50px;
        }

        .col .left {
            width: 50%;
            float: left;
        }

        .col .right {
            float: right;
            width: 50%;
        }

        .header .right {
            text-align: right;
        }

        .content {
            max-width: 600px;
            margin: 15px auto;
            padding: 10px;
            background: white;
        }

        .container {
            background: #f0f0f0;
            padding: 20px 0;
        }

        .footer {
            background: #262626;
            padding: 15px 0;
        }

        .footer * {
            color: white !important;
        }

        .footer .content {
            background: #262626 !important;
        }

        .wir-sind {
            text-align: center;
        }

        .parklot-img,
        .so-kontakt {
            padding-right: 10px;
            box-sizing: border-box;
        }

        .social-media-icons img {
            width: 50px;
            height: 50px;
        }

        .clear {
            clear: both;
        }

        /* ----------- iPhone 6, 6S, 7 and 8 ----------- */

        /* Portrait */

        @media only screen

        and (max-device-width: 667px){
            .left,
            .right {
                display: block !important;
                float: none !important;
                clear: both !important;
                width: 100% !important;
            }
        }
    </style>
    <div class="header col">
        <div class="content">
            <div class="left">
                <?php if (get_theme_mod('itweb24_theme_logo')) : ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>">
                        <img src="<?php echo esc_url(get_theme_mod('itweb24_theme_logo')); ?>"
                             alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
                    </a>
                <?php else : ?>
                    <div class="shop-name"><h3><?php $this->shop_name(); ?></h3></div>
                <?php endif; ?>
            </div>
            <div class="right">
                <a href="<?php echo url() ?>/kontakt">Kontakt</a> | <a
                        href="<?php echo url() ?>/?cancel-order=<?php echo get_post_meta($order_id, 'token', true) ?>">Parkplatz
                    stornieren</a>
            </div>
            <div class="clear"></div>
        </div>
    </div>
	<?php if (!$_SESSION["extern"] || $_SESSION['output']['@attributes']['Result'] == 'OK'):?>
    <div class="container">
        <div class="content">
            <h1>
                Rechnung
            </h1>
            <hr>
            <p>
				<?php if(get_post_meta($order_id, '_billing_grander', true) == "male"): ?>
				Sehr geehrter Herr <?php echo $order->get_formatted_billing_full_name() ?>,<br/>
				<?php else:?>
				Sehr geehrte Frau <?php echo $order->get_formatted_billing_full_name() ?>,<br/>
				<?php endif;?>
                vielen Dank, dass Sie sich für Airport Parking Germany entschieden haben.
            </p>
            <p>
                Im Anhang dieser E-Mail finden Sie Ihre Rechnung. Ihr Parkplatz wird nach der Bezahlung dieser
				Rechnung für Sie reserviert.
            </p>
            <p>
                Die angehängte Rechnung können Sie mit dem kostenlosen Acrobat Reader öffnen. Falls Sie dieses
                Dokument nicht öffnen können, laden Sie <a target="_blank"
                                                           href="https://get.adobe.com/de/reader/">hier</a>
                den kostenlosen Adobe Acrobat Reader herunter.
            </p>
            <br>
            <br>
        </div>
        <div class="content">
            <div class="col">
                <div class="left parklot-img">
							
						<?php if ($image && !$_SESSION["extern"]) : ?>
							<img src="<?php echo $image[0] ?>" alt="">
						<?php else: ?>
							<?php if (get_theme_mod('itweb24_theme_logo')) : ?>
								<a href="<?php echo esc_url(home_url('/')); ?>">
									<img src="<?php echo esc_url(get_theme_mod('itweb24_theme_logo')); ?>"
										 alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
								</a>
							<?php else : ?>
								<div class="shop-name"><h3><?php $this->shop_name(); ?></h3></div>
							<?php endif; ?>
						<?php endif; ?>
                </div>
                <div class="right">
                    <h1>Anschrift Parkplatz</h1>
                    <hr>
                    <strong>
                        <?php 
							if($_SESSION["extern"])
								echo $_SESSION["extern_name"];
							else
								echo $parklot->parklotname; 
						?>
                    </strong><br/>
                    <?php if($_SESSION["extern"])
								echo $_SESSION["extern_address"];
							else
								echo $parklot->address 
					?>
                </div>
                <div class="clear"></div>
            </div>
        </div>
        <div class="content wir-sind">
            <h1>Wir sind für Sie da!</h1>
            <hr>
            <p>
                Sie haben noch Fragen? Schreiben Sie uns einfach eine E-Mail an
                <a href="mailto:info@a-p-germany.de">info@a-p-germany.de</a> oder rufen Sie uns
                unter <a href="tel:+49 (0) 711 22 051 247">+49 (0) 711 22 051 247</a> an.
            </p>
            <p>
                Montag bis Freitag von 11:00 bis 19:00 Uhr.
                Aus dem dt. Festnetz zum Ortstarif. Mobilfunkkosten abweichend.
            </p>
        </div>
        <!--<div class="content">
            <div class="col">
                <div class="left so-kontakt">
                    <h1>Bleiben Sie in Kontakt mit uns</h1>
                </div>
                <div class="right social-media-icons">
                    <a href="">
                        <img src="<?php echo get_template_directory_uri() . '/inc/assets/images/square-facebook.png' ?>" alt="">
                    </a>
                </div>
                <div class="clear"></div>
            </div>
        </div> -->
    </div>
	<?php else: ?>
	<div class="container">
		<div class="content">
			<p>Sehr geehrte Damen und Herren,<br><br>
			bei Ihrem Buchungsprozess ist leider ein Fehler aufgetreten. Ihre Buchung wurde abgebrochen.<br><br>
			Versuchen Sie es bitte erneut oder rufen Sie uns direkt an.</p>
		</div>
        <div class="content wir-sind">
            <h1>Wir sind für Sie da!</h1>
            <hr>
            <p>
                Sie haben noch Fragen? Schreiben Sie uns einfach eine E-Mail an
                <a href="mailto:info@a-p-germany.de">info@a-p-germany.de</a> oder rufen Sie uns
                unter <a href="tel:+49 (0) 711 22 051 247">+49 (0) 711 22 051 247</a> an.
            </p>
            <p>
                Montag bis Freitag von 11:00 bis 19:00 Uhr.
                Aus dem dt. Festnetz zum Ortstarif. Mobilfunkkosten abweichend.
            </p>
		</div>
    </div>		
	<?php endif;?>
    <div class="footer">
        <div class="content">
            <div class="col">
                <div class="left">
                    © 2020 Airport Parking Germany<br/>
                    Raiffeisenstraße 18, 70794 Filderstadt
                </div>
                <div class="right">
                    <a href="<?php echo url() ?>/impressum">Impressum</a> | <a href="<?php echo url() ?>">www.airport-parking-germany.de</a>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div>
<?php


/**
 * Show user-defined additional content - this is set in each email's settings.
 */
//if ($additional_content) {
//    echo wp_kses_post(wpautop(wptexturize($additional_content))) . '<br/>';
//}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
//do_action('woocommerce_email_footer', $email);