<?php
if ( !defined('ABSPATH') ) {
    //If wordpress isn't loaded load it up.
    $path = $_SERVER['DOCUMENT_ROOT'];
    include_once $path . '/wp-load.php';
}
require_once get_template_directory() . '/lib/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;
session_start();
// if (isset($_SESSION['allorders'])) {

$result =  $_SESSION['allorders'];

$date = "";
if ($_SESSION['dateFrom'] == $_SESSION['dateTo']) {
    $date = date('d.m.Y', strtotime($_SESSION['dateFrom']));
}else {
    $date = date('d.m.Y', strtotime($_SESSION['dateFrom'])) . " - " . date('d.m.Y', strtotime($_SESSION['dateTo']));
}

$zeile = 1;
$umbruch = 0;
$seite = 1;
$total_rows = $_SESSION['total_rows'];
$html .= "<style>@page {
			 margin-left, margin-right: 30px;
			 margin-top: 10px; 
			}</style>";
foreach ($result as $r) {

	if($zeile - ($umbruch + 1) == 0){
		$html .= "<h4 style='text-align:center'>Anreiseliste Shuttle | " . $date . " | Seite " . $seite . " von " . ceil($total_rows / 28) . "</h4>";

		$html .= "<table style=' font-size: 12px; border-collapse:collapse; width: 100%'>
			<tr>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Nr.</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>PCode.</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Buchung</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Kunde</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Anreisedatum</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>An-Z</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Personen</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Parkplatz</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Abreisedatum</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; min-width: 80px'>Rückflug</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Landung</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Betrag</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Service</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Fahrer</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; min-width: 100px;'>Sonstiges</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>SG</th>
			</tr>";
	}

	$color = $r->Color;
	
	$betrag = '-';
	if ((get_post_meta($r->order_id, '_payment_method_title', true) == 'Barzahlung' || $r->Produkt == 6772) && $r->Status != "wc-cancelled")
		$betrag = get_post_meta($r->order_id, '_order_total', true);
	else
		$betrag = '-';
	
	if(get_post_meta($r->order_id, 'Parkplatz', true) != 0 || get_post_meta($r->order_id, 'Parkplatz', true) != null)
		$parkplatz = get_post_meta($r->order_id, 'Parkplatz', true);
	elseif(get_post_meta($r->order_id, 'Parkplatz', true) == null || get_post_meta($r->order_id, 'Parkplatz', true) == 0)
		$parkplatz = "";
	else
		$parkplatz = "";

    if (get_post_meta($r->order_id, '_billing_first_name', true) == null)
		$customor = get_post_meta($r->order_id, '_billing_last_name', true);
	elseif (get_post_meta($r->order_id, '_billing_last_name', true) == null)
		$customor = get_post_meta($r->order_id, '_billing_first_name', true);
	elseif (strlen(get_post_meta($r->order_id, '_billing_last_name', true)) < 2)
		$customor = get_post_meta($r->order_id, '_billing_first_name', true);
	elseif (strlen(get_post_meta($r->order_id, '_billing_last_name', true)) > 2)
		$customor = get_post_meta($r->order_id, '_billing_last_name', true);
	else
		$customor = get_post_meta($r->order_id, '_billing_last_name', true);

    if ($r->Status == "wc-cancelled") {
        $anreisezeit = date('H:i', strtotime("23:59"));
        $color = '#ff0000';
    } else {
        $anreisezeit = date('H:i', strtotime(get_post_meta($r->order_id, 'Uhrzeit von', true)));
    }
	
	if(get_post_meta($r->order_id, 'AbreisedatumEdit', true) != null)
		$abreisedatum = date("d.m.Y",  strtotime(get_post_meta($r->order_id, 'AbreisedatumEdit', true)));
	else
		$abreisedatum = "";
	
	if(get_post_meta($r->order_id, 'Sperrgepack', true) == '1')
		$spgp = "X";
	else
		$spgp = "";
	
	if($_SESSION["as-".$r->order_id] != 0){
		$service = number_format($_SESSION["as-".$r->order_id], 2, '.', '');
	}
	else
		$service = "-";

    $html .= "<tr>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'>" . ++$ord . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap; background-color:" . $color . "'>" . $r->Code . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'>" . get_post_meta($r->order_id, 'token', true) . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'>" . $customor . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'>" . date("d.m.Y",  strtotime(get_post_meta($r->order_id, 'Anreisedatum', true))) . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'>" . $anreisezeit . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'>" . get_post_meta($r->order_id, 'Personenanzahl', true) . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'>" . $parkplatz . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'>" . $abreisedatum . "</td>";
	$html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'></td>";
	$html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'></td>";
	$html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'>" . $betrag . "</td>";
	$html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'>" . $service . "</td>";
	$html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'>" . get_post_meta($r->order_id, 'FahrerAn', true) . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'>" . get_post_meta($r->order_id, 'Sonstige 1', true) . "</td>";
	$html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'>" . $spgp . "</td>";
    $html .= "</tr>";
	
	if($zeile == 28 || $zeile - $umbruch == 28){
		$html .= "</table>";
		$html .= "<div style='page-break-before: always;'></div>";
		$umbruch += 28;
		$seite++;
	}
	
	$zeile++;
}
$filename = "Anreiseliste Shuttle  " . $_SESSION['dateFrom'] . " - " . $_SESSION['dateTo'];

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'landscape');

// Render the HTML as PDF
$dompdf->render();

$file = $dompdf->output();
if(!file_exists(ABSPATH . 'wp-content/uploads')){
	mkdir(ABSPATH . 'wp-content/uploads');
}
$filePath = ABSPATH . 'wp-content/uploads/anreiseliste.pdf';
$pdf = fopen($filePath, 'w');
fwrite($pdf, $file);
fclose($pdf);

$pdf_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
echo "<script>location.href = '".$pdf_url."/wp-content/uploads/anreiseliste.pdf';</script>";

/*
// include autoloader
// require_once 'dompdf/autoload.inc.php';
include('../../packagist/dompdf/autoload.inc.php');


// reference the Dompdf namespace
use Dompdf\Dompdf;

// instantiate and use the dompdf class
$dompdf = new Dompdf();

$dompdf->loadHtml($html);

// (Optional) Setup the paper size and orientation
// $dompdf->setPaper('A4', 'landscape');
$dompdf->set_paper('letter', 'landscape');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$dompdf->stream($filename);
header('Location: ' . $_SERVER['HTTP_REFERER']);

// }
*/