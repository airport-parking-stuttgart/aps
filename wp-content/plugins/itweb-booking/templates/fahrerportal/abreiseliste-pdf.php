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


$locations = Database::getInstance()->getLocations();
foreach ($result as $key => $r) {
	if ($r->Status == "wc-cancelled") {
        $abreisezeit = date('H:i', strtotime("23:59"));
    } else {
        $abreisezeit = date('H:i', strtotime($r->Uhrzeit_bis));
    }
	
	if ($r->Status == "wc-cancelled")
		continue;
	
	if($r->RuckflugnummerEdit != null && $r->RuckflugnummerEdit != '-')
		$rueckflug_nr = $r->RuckflugnummerEdit;
	elseif($r->Ruckflugnummer != null && $r->Ruckflugnummer != '-')
		$rueckflug_nr = $r->Ruckflugnummer;
	else
		$rueckflug_nr = "";
	
	$flugnummer2[$key]['nr'] = strtoupper($rueckflug_nr);
	$flugnummer2[$key]['zeit'] = $abreisezeit;
			
}

foreach($flugnummer2 as $k => $v){
	$zeit[$flugnummer2[$k]['nr']][$flugnummer2[$k]['zeit']]++;
	$zeit_less[$flugnummer2[$k]['nr']]++;
}


foreach ($result as $key => $r) {
	if ($r->Status == "wc-cancelled") {
        $abreisezeit = date('H:i', strtotime("23:59"));
    } else {
        $abreisezeit = date('H:i', strtotime($r->Uhrzeit_bis));
    }
	//if ($r->Status == "wc-cancelled")
	//	continue;
	
	if($r->RuckflugnummerEdit != null && $r->RuckflugnummerEdit != '-')
		$rueckflug_nr = $r->RuckflugnummerEdit;
	elseif($r->Ruckflugnummer != null && $r->Ruckflugnummer != '-')
		$rueckflug_nr = $r->Ruckflugnummer;
	else
		$rueckflug_nr = "";
	
	if($zeit[strtoupper($rueckflug_nr)][$abreisezeit] >= 3){
		foreach($locations as $location){
			if($location->id == 2)
				continue;
			if($location->id == $r->location_id)
				$sum_pseronen[strtoupper($rueckflug_nr)][$abreisezeit][$r->location_id] += $r->Personenanzahl;
			else
				$sum_pseronen[strtoupper($rueckflug_nr)][$abreisezeit][$location->id] += 0;			
		}		
	}
}

$c = 1;
foreach ($result as $key => $r) {
	if ($r->Status == "wc-cancelled") {
        $abreisezeit = date('H:i', strtotime("23:59"));
    } else {
        $abreisezeit = date('H:i', strtotime($r->Uhrzeit_bis));
    }
	
	//if ($r->Status == "wc-cancelled")
	//	continue;
	
	if($r->RuckflugnummerEdit != null && $r->RuckflugnummerEdit != '-')
		$rueckflug_nr = $r->RuckflugnummerEdit;
	elseif($r->Ruckflugnummer != null && $r->Ruckflugnummer != '-')
		$rueckflug_nr = $r->Ruckflugnummer;
	else
		$rueckflug_nr = "";
	
	if($zeit[strtoupper($rueckflug_nr)][$abreisezeit] < 3){
		
		foreach($locations as $location){
			if($location->id == 2)
				continue;
			if($location->id == $r->location_id)
				$free[$c][$r->location_id] += $r->Personenanzahl;
			else
				$free[$c][$location->id] += 0;			
		}
			
	}
	$c++;
}
$c = 1;
$d = 1;
foreach ($result as $key => $r) {
	if ($r->Status == "wc-cancelled")
		$cancelled++;
}
$total_rows = count($result);
$last_entery = $total_rows - $cancelled + 1;

foreach ($result as $key => $r) {
	if ($r->Status == "wc-cancelled") {
        $abreisezeit = date('H:i', strtotime("23:59"));
    } else {
        $abreisezeit = date('H:i', strtotime($r->Uhrzeit_bis));
    }
	
	//if ($r->Status == "wc-cancelled")
	//	continue;
	
	if($r->RuckflugnummerEdit != null && $r->RuckflugnummerEdit != '-')
		$rueckflug_nr = $r->RuckflugnummerEdit;
	elseif($r->Ruckflugnummer != null && $r->Ruckflugnummer != '-')
		$rueckflug_nr = $r->Ruckflugnummer;
	else
		$rueckflug_nr = "";
	
	if($zeit[strtoupper($rueckflug_nr)][$abreisezeit] >= 3 || $c == $last_entery){
		
		if($stop == 0){
			for($i = $d; $i < $c; $i++){
				foreach($locations as $location){
					if($location->id == 2)
						continue;
					$pers[$c-1][$location->id] += $free[$i][$location->id];	
				}
			}
			$stop = 1;			
		}
		$stop2 = 0;		
	}
	else{		
		$stop = 0;
		if($stop2 == 0){
			$d = $c;
			$stop2 = 1;	
		}	
	}
	$c++;
}
//

foreach($sum_pseronen as $f => $c){	
	foreach($sum_pseronen[$f] as $c2 => $v){		
		$numItems = count($v);
		$index = 0;
		foreach($v as $loc){
			if(++$index === $numItems)
				$str_pers[$f][$c2] .= $loc;
			else
				$str_pers[$f][$c2] .= $loc . " <span style='font-weight: 900;'>/</span> ";
		}				
	}	
}

foreach($pers as $c => $v){		
	$numItems = count($v);
	$index = 0;
	foreach($v as $loc){
		if(++$index === $numItems)
			$str_pers_f[$c] .= $loc;
		else
			$str_pers_f[$c] .= $loc . " <span style='font-weight: 900;'>/</span> ";
	}				
}	

$row = 1;
foreach ($result as $key => $r) {
	if ($r->Status == "wc-cancelled") {
        $abreisezeit = date('H:i', strtotime("23:59"));
        $color = '#ff0000';
    } else {
        $abreisezeit = date('H:i', strtotime($r->Uhrzeit_bis));
    }
	
	if($r->RuckflugnummerEdit != null && $r->RuckflugnummerEdit != '-')
		$rueckflug_nr = $r->RuckflugnummerEdit;
	elseif($r->Ruckflugnummer != null && $r->Ruckflugnummer != '-')
		$rueckflug_nr = $r->Ruckflugnummer;
	else
		$rueckflug_nr = "";
	
	if($zeit[strtoupper($rueckflug_nr)][$abreisezeit] >= 3){
		$border_left = "border-left: 3px solid black !important; ";
		$border_right = "border-right: 3px solid black !important; ";
		if($border_top == null && $first_row[strtoupper($rueckflug_nr)][$abreisezeit] == 0){
			$border_top = "border-top: 3px solid black !important; ";
			$first_row[strtoupper($rueckflug_nr)][$abreisezeit] = 1;
		}
		else{
			$border_top = "";
		}
		$last_row[strtoupper($rueckflug_nr)][$abreisezeit]++;

		if($last_row[strtoupper($rueckflug_nr)][$abreisezeit] == $zeit[strtoupper($rueckflug_nr)][$abreisezeit]){
			$border_bottom = "border-bottom: 3px solid black !important; ";
			$tbl_sum_person[strtoupper($rueckflug_nr)][$abreisezeit] = $str_pers[strtoupper($rueckflug_nr)][$abreisezeit];
		}			
		else{
			$border_bottom = "";
		}			
	}
	else{
		$border_left = "";
		$border_right = "";
		$border_top = "";
		$border_bottom = "";
		$first_row[strtoupper($rueckflug_nr)][$abreisezeit] = 0;
	}
	
	if($zeile - ($umbruch + 1) == 0){
		$html .= "<h4 style='text-align:center'>Abreiseliste Shuttle | " . $date . " | Seite " . $seite . " von " . ceil($total_rows / 28) . "</h4>";

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
        $abreisezeit = date('H:i', strtotime("23:59"));
        $color = '#ff0000';
    } else {
        $abreisezeit = date('H:i', strtotime($r->Uhrzeit_bis));
    }
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
	
	if($str_pers_f[$row] == null)
		$show_person = $tbl_sum_person[strtoupper($rueckflug_nr)][$abreisezeit];
	else
		$show_person = $str_pers_f[$row];
	
	if($show_person != null && $r->Sonstige_2 != null)
		$trenner = " / ";
	else
		$trenner = "";
	
	$html .= "<tr>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap; ". $border_left . $border_top . $border_bottom . "'>" . ++$ord . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap; " . $border_top . $border_bottom . " background-color:" . $color . "'>" . $r->Code . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap; " . $border_top . $border_bottom . "'>" . $r->Token . "</td>";	
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap; " . $border_top . $border_bottom . "'>" . $customor . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap; " . $border_top . $border_bottom . "'>" . date("d.m.Y",  strtotime($r->Abreisedatum)) . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap; " . $border_top . $border_bottom . "'>" . $abreisezeit . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap; " . $border_top . $border_bottom . "'>" . $r->Personenanzahl . "</td>";
	$html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap; " . $border_top . $border_bottom . "'>" . $rueckflug_nr . "</td>";
    $html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap; " . $border_top . $border_bottom . "'>" . $parkplatz . "</td>";
	$html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap; " . $border_top . $border_bottom . "'>" . $r->FahrerAb . "</td>";
	$html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; " . $border_top . $border_bottom . "'>" . $r->Sonstige_1 . "</td>";
	$html .= "<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap; " . $border_top . $border_bottom . $border_right . "'>" . $show_person . $trenner . $r->Sonstige_2 . "</td>";
    $html .= "</tr>";
	
	if($zeile == 28 || $zeile - $umbruch == 28){
		$html .= "</table>";
		$html .= "<div style='page-break-before: always;'></div>";
		$umbruch += 28;
		$seite++;
	}
	
	$zeile++;
	$row++;
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

?>