<?php

session_start();
if ( !defined('ABSPATH') ) {
    //If wordpress isn't loaded load it up.
    $path = $_SERVER['DOCUMENT_ROOT'];
    include_once $path . '/wp-load.php';
}
if (isset($_SESSION['allorders'])) {

  $result =  $_SESSION['allorders'];
  // include('./Classes/PHPExcel.php');
  include('../../packagist/Classes/PHPExcel.php');
  $objPHPExcel  =  new  PHPExcel();
  $objPHPExcel->setActiveSheetIndex(0);

  $k = 0;

  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k++, 1, 'Nr.');
  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k++, 1, 'PCode.');
  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k++, 1, 'Buchung');
  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k++, 1, 'Kunde');
  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k++, 1, 'Abreisedatum');
  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k++, 1, 'Abreisezeit');
  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k++, 1, 'Personen');
  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k++, 1, 'Rückflugnummer');
  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k++, 1, 'Parkplatz');
  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k++, 1, 'Fahrer');
  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k++, 1, 'Sonstiges1');
  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k++, 1, 'Sonstiges2');
  $objPHPExcel->getActiveSheet()->getStyle("A1:R1")->getFont()->setBold(true);

  $rowCount  =  2;
  $j = 0;
  $ord = 0;
  foreach ($result as $r) {
    $j = 0;
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
        $abreisezeit = date('H:i', strtotime("23:59"));
    } else {
        $abreisezeit = date('H:i', strtotime(get_post_meta($r->order_id, 'Uhrzeit bis', true)));
    }
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

    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper( ++$ord, 'UTF-8'));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper($r->Code, 'UTF-8'));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper(get_post_meta($r->order_id, 'token', true), 'UTF-8'));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper($customor, 'UTF-8'));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper(date("d.m.Y",  strtotime(get_post_meta($r->order_id, 'Abreisedatum', true))), 'UTF-8'));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper($abreisezeit, 'UTF-8'));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper(get_post_meta($r->order_id, 'Personenanzahl', true), 'UTF-8'));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper(get_post_meta($r->order_id, 'RückflugnummerEdit', true), 'UTF-8'));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper($parkplatz, 'UTF-8'));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper(get_post_meta($r->order_id, 'FahrerAb', true), 'UTF-8'));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper(get_post_meta($r->order_id, 'Sonstige 1', true), 'UTF-8'));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper(get_post_meta($r->order_id, 'Sonstige 2', true), 'UTF-8'));
    $rowCount++;
  }

  $objWriter  =  new PHPExcel_Writer_Excel2007($objPHPExcel);
  header('Content-Type: application/vnd.ms-excel'); //mime type
  header('Content-Disposition: attachment;filename="Abreiseliste.xlsx"'); //tell browser what's the file name
  header('Cache-Control: max-age=0'); //no cache
  $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
  $objWriter->save('php://output');
  //	header('Location: ' . $_SERVER['HTTP_REFERER']);
}
else {
  header('Location: ' . $_SERVER['HTTP_REFERER']);
}
