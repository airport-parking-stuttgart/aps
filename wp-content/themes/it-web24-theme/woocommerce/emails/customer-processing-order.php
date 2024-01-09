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


// pdf
if(!file_exists(ABSPATH . 'wp-content/uploads/qrcodes')){
    mkdir(ABSPATH . 'wp-content/uploads/qrcodes');
}




	ob_start();
	if (get_template_directory() . '/invoices/email-booking.php') {
		include(get_template_directory() . '/invoices/email-booking.php');
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
		$fileName = date('d-m-Y') . '-' . $order_id;
	if(!file_exists(ABSPATH . 'wp-content/uploads/new-order-invoices')){
		mkdir(ABSPATH . 'wp-content/uploads/new-order-invoices');
	}
	$filePath = ABSPATH . 'wp-content/uploads/new-order-invoices/' . $fileName . '.pdf';
	$pdf = fopen($filePath, 'w');
	fwrite($pdf, $file);
	fclose($pdf);
	// end pdf

//}


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
                <p>Test</p>
            </div>
            <div class="clear"></div>
        </div>
    </div>
	
    <div class="container">
        <div class="content">
			<p>Test</p>
        </div>
    </div>
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