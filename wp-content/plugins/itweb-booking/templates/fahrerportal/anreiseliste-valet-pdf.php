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
$seiten_bis = ceil($total_rows / 28);
$seiten_bis = $seiten_bis == 0 ? 1 : $seiten_bis;

$html .= "<style>@page {
			 margin-left, margin-right: 30px;
			 margin-top: 10px; 
			}</style>";
foreach ($result as $r) {

	if($zeile - ($umbruch + 1) == 0){
		$html .= "<h4 style='text-align:center'>Anreiseliste ".$_SESSION['service_type']." | " . $date . " | Seite " . $seite . " von " . $seiten_bis . "</h4>";

		$html .= "<table style=' font-size: 12px; border-collapse:collapse; width: 100%'>
			<tr>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Nr.</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>PCode.</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Buchung</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Kunde</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Anreisedatum</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>An-Z</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Parkplatz</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Kennzeichen</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Abreisedatum</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; min-width: 80px'>Rückflug</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Landung</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Betrag</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Service</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Fahrer</th>
				<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; min-width: 100px;'>Sonstiges</th>
			</tr>";
	}

	$color = $r->Color;
	
	$betrag = '-';
	if (($r->Bezahlmethode == 'Barzahlung' || $r->Produkt == 6772) && $r->Status != "wc-cancelled")
		$betrag = number_format($r->Preis, 2, ".", ".");
	else
		$betrag = '-';
	
	if($r->Parkplatz != 0 || $r->Parkplatz != null)
		$parkplatz = $r->Parkplatz;
	elseif($r->Parkplatz == null || $r->Parkplatz == 0)
		$parkplatz = "";
	else
		$parkplatz = "";

    if ($r->Vorname == null)
		$customor = $r->Vorname;
	elseif ($r->Nachname == null)
		$customor = $r->Vorname;
	elseif (strlen($r->Nachname) < 2)
		$customor = $r->Vorname;
	elseif (strlen($r->Nachname) > 2)
		$customor = $r->Nachname;
	else
		$customor = $r->Nachname;

    if ($r->Status == "wc-cancelled") {
        $anreisezeit = date('H:i', strtotime("23:59"));
        $color = '#ff0000';
    } else {
        $anreisezeit = date('H:i', strtotime($r->Uhrzeit_von));
    }
	
	if($r->AbreisedatumEdit != null)
		$abreisedatum = date("d.m.Y",  strtotime($r->AbreisedatumEdit));
	else
		$abreisedatum = "";
	
	if($r->Service != 0 && $r->Service != null){
		$service = number_format($r->Service, 2, '.', '');
	}
	else
		$service = "-";

    $html .= "<tr>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'>" . ++$ord . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap; background-color:" . $color . "'>" . $r->Code . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'>" . $r->Token . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'>" . $customor . "<br>" . $r->Telefon . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'>" . date("d.m.Y",  strtotime($r->Anreisedatum)) . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'>" . $anreisezeit . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'>" . $parkplatz . "</td>";
	$html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'>" . $r->Kennzeichen . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'>" . $abreisedatum . "</td>";
	$html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'></td>";
	$html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'></td>";
	$html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'>" . $betrag . "</td>";
	$html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'>" . $service . "</td>";
	$html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'>" . $r->FahrerAn . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'>" . $r->Sonstige_1 . "</td>";
    $html .= "</tr>";
	
	if($zeile == 28 || $zeile - $umbruch == 28){
		$html .= "</table>";
		$html .= "<div style='page-break-before: always;'></div>";
		$umbruch += 28;
		$seite++;
	}
	
	$zeile++;
}

$filename = "Anreiseliste Valet " . $_SESSION['dateFrom'] . " - " . $_SESSION['dateTo'];

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
$filePath = ABSPATH . 'wp-content/uploads/anreiseliste_valet.pdf';
$pdf = fopen($filePath, 'w');
fwrite($pdf, $file);
fclose($pdf);

$pdf_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
echo "<script>location.href = '".$pdf_url."/wp-content/uploads/anreiseliste_valet.pdf';</script>";

?>