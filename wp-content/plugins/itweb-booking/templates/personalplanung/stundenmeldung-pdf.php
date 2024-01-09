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

$html .= "<style>
tr:nth-child(even) {background-color: #dae5f0;}
</style>";

$html .= $_SESSION['stundenmeldung'];


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
$filePath = ABSPATH . 'wp-content/uploads/stundenmeldung.pdf';
$pdf = fopen($filePath, 'w');
fwrite($pdf, $file);
fclose($pdf);
if(isset($_POST['mail'])){
	$headers = array('Content-Type: text/html; charset=UTF-8');
	wp_mail('info@airport-parking-stuttgart.de', '[APS] Stundenmeldung ' . $_POST['date'], 'Stundenmeldung',$headers, $filePath);
	echo "<script>window.history.back();</script>";
}
else{
	$pdf_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
	echo "<script>location.href = '".$pdf_url."/wp-content/uploads/stundenmeldung.pdf';</script>";
}

?>