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
$ord = 0;

$html .= "<style>@page {
			 margin-left, margin-right: 30px;
			 margin-top: 10px; 
			}</style>";

foreach ($result as $r) {
	
	if($zeile - ($umbruch + 1) == 0){
		$html .= "<h4 style='text-align:center'>Abreiseliste Shuttle | " . $date . " | Seite " . $seite . " von " . ceil($total_rows / 25) . "</h4>";

		$html .= "<table style=' font-size: 12px; border-collapse:collapse; width: 100%'>
			<tr>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Nr.</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>PCode.</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Buchung</th>		
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Kunde</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Abreisedatum</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Ab-Z</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Personen</th>		
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; min-width: 80px'>Rückflug</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Parkplatz</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Fahrer</th>        
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; min-width: 100px;'>Sonstiges1</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; min-width: 100px;'>Sonstiges2</th>
			</tr>";
	}

	$color = $r->Color;

    if ($r->Vorname == null)
        $customor = $r->Nachname;
    elseif ($r->Nachname == null)
        $customor = $r->Vorname;
    elseif (strlen($r->Nachname) < 2)
        $customor = $r->Vorname;
    elseif (strlen($r->Nachname) > 2)
        $customor = $r->Nachname;
    else
        $customor = $r->Nachname;

    if ($r->Status == "wc-cancelled") {
        $abreisezeit = date('H:i', strtotime("23:59"));
        $color = '#ff0000';
    } else {
        $abreisezeit = date('H:i', strtotime($r->Uhrzeit_bis));
    }
	if ($r->Bezahlmethode == "Barzahlung")
		$betrag = $r->Betrag;
	else
		$betrag = "-";

    $html .= "<tr>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px;'>" . ++$ord . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; background-color:" . $color . "'>" . $r->Code . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px;'>" . $r->Token . "</td>";	
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px;'>" . $customor . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px;'>" . date("d.m.Y",  strtotime($r->Abreisedatum)) . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px;'>" . $abreisezeit . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px;'>" . $r->Personenanzahl . "</td>";
	$html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px;'>" . $r->RückflugnummerEdit . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px;'>" . $r->Parkplatz . "</td>";
	$html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px;'>" . $r->FahrerAb . "</td>";
	$html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px;'>" . $r->Sonstige_1 . "</td>";
	$html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px;'>" . $r->Sonstige_2 . "</td>";
    $html .= "</tr>";
	
	if($zeile == 28 || $zeile - $umbruch == 28){
		$html .= "</table>";
		$html .= "<div style='page-break-before: always;'></div>";
		$umbruch += 28;
		$seite++;
	}
	
	$zeile++;
}

$html .= "</table>";

$filename = "Abreiseliste Shuttle  " . $_SESSION['dateFrom'] . " - " . $_SESSION['dateTo'];

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
$filePath = ABSPATH . 'wp-content/uploads/abreiseliste.pdf';
$pdf = fopen($filePath, 'w');
fwrite($pdf, $file);
fclose($pdf);

$pdf_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
echo "<script>location.href = '".$pdf_url."/wp-content/uploads/abreiseliste.pdf';</script>";

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