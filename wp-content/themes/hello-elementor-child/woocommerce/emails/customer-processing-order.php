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
require_once ABSPATH . 'wp-content/plugins/itweb-booking/lib/phpqrcode/phpqrcode.php';
require_once get_template_directory() . '/lib/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

//if (!$_SESSION["extern"] || $_SESSION['output']['@attributes']['Result'] == 'OK'){

global $wpdb;
$db = Database::getInstance();
$order_id = $order->get_id();
$web_company = Database::getInstance()->getSiteCompany();
$items = $order->get_items();
foreach ( $items as $item ) {
    $product_id = $item['product_id'];
}
$parklot = $db->getParklotByProductId($product_id);
$image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'single-post-thumbnail');
$lotType = $parklot->type;

$additionalPrice = "0.00";
$services = Database::getInstance()->getBookingMetaAsResults($order_id, 'additional_services');
if(count($services) > 0){
	foreach($services as $v){
		$s = Database::getInstance()->getAdditionalService($v->meta_value);
		$additionalPrice += $s->price;
	}
}

if(!file_exists(ABSPATH . 'wp-content/uploads/qrcodes')){
    mkdir(ABSPATH . 'wp-content/uploads/qrcodes');
}
$qr_content = get_post_meta($order_id, 'token', true) . "\n" . $parklot->parklot . "\n" . $parklot->adress;
$filenameQR = ABSPATH . 'wp-content/uploads/qrcodes/' . get_post_meta($order_id, 'token', true) . '.png';
if(!file_exists($filenameQR)){
	QRcode::png($qr_content, $filenameQR, 'L', 4, 2);
}
ob_start();
	if (get_template_directory() . '/invoices/email-booking.php') {
		require_once get_template_directory() . '/invoices/email-booking.php';
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
		$fileName = date('d-m-Y') . '-' . get_post_meta($order_id, 'token', true);
	if(!file_exists(ABSPATH . 'wp-content/uploads/new-order-invoices')){
		mkdir(ABSPATH . 'wp-content/uploads/new-order-invoices');
	}
	$filePath = ABSPATH . 'wp-content/uploads/new-order-invoices/' . $fileName . '.pdf';
	$pdf = fopen($filePath, 'w');
	fwrite($pdf, $file);
	fclose($pdf);
	// end pdf

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
            max-width: 800px;
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
	
	<div class="container">
		<div class="content">
			<h1>Buchungsbestätigung! Ihr Parkplatz wurde für Sie reserviert!</h1>
			<hr>
            <p>
				<?php if(get_post_meta($order_id, 'Anrede', true) == "Herr"): ?>
				Sehr geehrter Herr <?php echo $order->get_formatted_billing_full_name() ?>,<br/>
				<?php elseif(get_post_meta($order_id, 'Anrede', true) == "Frau"):?>
				Sehr geehrte Frau <?php echo $order->get_formatted_billing_full_name() ?>,<br/>
				<?php else: ?>
				Sehr geehrte Damen und Herren,<br/>
				<?php endif;?>
                <?php if($web_company->name != null): ?>
                vielen Dank, dass Sie sich für <?php echo $web_company->name ?> entschieden haben.
				<?php else: ?>
				vielen Dank für Ihre Buchung.
				<?php endif; ?>
            </p>
            <p><strong>Ihre Buchungsnummer lautet: 
					<?php echo get_post_meta($order_id, 'token', true); ?> 
			</strong><br/><br/></p>
			<p>
			<p>
                Im Anhang dieser E-Mail finden Sie Ihr elektronisches Parkplatzticket. Drucken Sie Ihr Parkplatzticket
                aus
                oder nehmen Sie es digital auf Ihrem Smartphone/Tablet zum Parkplatz mit und zeigen Sie es einem unserer
                Mitarbeiter.
            </p>
            <p>
                Das angehängte Parkplatzticket können Sie mit dem kostenlosen Acrobat Reader öffnen. Falls Sie dieses
                Dokument nicht öffnen können, laden Sie <a target="_blank" href="https://get.adobe.com/de/reader/">hier</a>
                den kostenlosen Adobe Acrobat Reader herunter.
            </p>
            <br>
            <br>			
		</div>

		<div class="content">
			<div class="col">
				<div class="left parklot-img">
					<?php if ($image) : ?>
						<img src="<?php echo $image[0] ?>" alt="">
					<?php endif; ?>
				</div>
				<div class="right">
                    <h1>Anschrift Parkplatz</h1>
                    <hr>
                    <strong>
                        <?php echo $parklot->parklot; ?>
                    </strong><br/>
                    <?php echo $parklot->adress . "<br>"; ?>
					<?php if($lotType == 'shuttle') : ?>
						<strong>Shuttle-Service-Hotline</strong><br/>
					<?php else: ?>
								<strong>Valet-Service-Hotline</strong><br/>
					<?php endif; ?>
					<a href="tel: <?php echo $parklot->phone; ?>"><?php echo $parklot->phone; ?></a>
                </div>
                <div class="clear"></div>
			</div>			
		</div>
		<?php if($web_company->email != null && $web_company->phone != null): ?>
		<div class="content wir-sind">
            <h1>Wir sind für Sie da!</h1>
            <hr>
            <p>
                Sie haben noch Fragen? Schreiben Sie uns einfach eine E-Mail an
                <a href="mailto:<?php echo $web_company->email ?>"><?php echo $web_company->email ?></a> oder rufen Sie uns
                unter <a href="tel:<?php echo $web_company->phone ?>"><?php echo $web_company->phone ?></a> an.
            </p>
            <p>
                Montag bis Freitag von 11:00 bis 19:00 Uhr.
                Aus dem dt. Festnetz zum Ortstarif. Mobilfunkkosten abweichend.
            </p>
        </div>
		<?php endif; ?>
		<div class="footer">
			<div class="content">
				<div class="col">
					<div class="left">
						© 2020 Airport Parking Management<br/>
						Raiffeisenstraße 18, 70794 Filderstadt
					</div>
					<div class="right">
						<a href="https://<?php echo $_SERVER['HTTP_HOST'] ?>/impressum/">Impressum</a> | <a href="https://<?php echo $_SERVER['HTTP_HOST'] ?>">www.<?php echo $_SERVER['HTTP_HOST'] ?></a>
					</div>
					<div class="clear"></div>
				</div>
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