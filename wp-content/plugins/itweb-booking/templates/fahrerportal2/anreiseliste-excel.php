<?php

session_start();

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
  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k++, 1, 'Anreisedatum');
  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k++, 1, 'Anreisezeit');
  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k++, 1, 'Personen');
  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k++, 1, 'Parkplatz');
  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k++, 1, 'Abreisedatum');
  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k++, 1, 'Rückflugnummer');
  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k++, 1, 'Landung');
  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k++, 1, 'Betrag');
  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k++, 1, 'Fahrer');
  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k++, 1, 'Sonstiges');

  $objPHPExcel->getActiveSheet()->getStyle("A1:R1")->getFont()->setBold(true);

  $rowCount  =  2;
  $j = 0;
  $ord = 0;
  foreach ($result as $r) {
    $j = 0;
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
      $anreisezeit = date('H:i', strtotime("23:59"));
    } else {
      $anreisezeit = date('H:i', strtotime($r->Uhrzeit_von));
    }
	
	if ($r->Bezahlmethode == "Barzahlung")
		$betrag = $r->Betrag;
	else
		$betrag = "-";

    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper( ++$ord, 'UTF-8'));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper($r->Code, 'UTF-8'));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper($r->Token, 'UTF-8'));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper($customor, 'UTF-8'));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper(date("d.m.Y",  strtotime($r->Anreisedatum)), 'UTF-8'));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper($anreisezeit, 'UTF-8'));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper($r->Personenanzahl, 'UTF-8'));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper($r->Parkplatz, 'UTF-8'));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper($r->AbreisedatumEdit, 'UTF-8'));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper($r->RückflugnummerEdit, 'UTF-8'));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper($r->Uhrzeit_bis_Edit, 'UTF-8'));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper($betrag, 'UTF-8'));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper($r->FahrerAn, 'UTF-8'));
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $rowCount, mb_strtoupper($r->Sonstige_1, 'UTF-8'));
    $rowCount++;
  }

  $objWriter  =  new PHPExcel_Writer_Excel2007($objPHPExcel);
  header('Content-Type: application/vnd.ms-excel'); //mime type
  header('Content-Disposition: attachment;filename="Anreiseliste.xlsx"'); //tell browser what's the file name
  header('Cache-Control: max-age=0'); //no cache
  $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
  $objWriter->save('php://output');
  header('Location: ' . $_SERVER['HTTP_REFERER']);
}
else {
  header('Location: ' . $_SERVER['HTTP_REFERER']);
}
