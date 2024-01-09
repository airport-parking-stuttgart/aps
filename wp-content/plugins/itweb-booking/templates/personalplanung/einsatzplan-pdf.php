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

if($_POST['table'] == 1)
	$html .= $_SESSION['einsatzplan_1'];
elseif($_POST['table'] == 2)
	$html .= $_SESSION['einsatzplan_2'];
elseif($_POST['table'] == 3)
	$html .= $_SESSION['einsatzplan_3'];
elseif($_POST['table'] == 4)
	$html .= $_SESSION['einsatzplan_4'];
elseif($_POST['table'] == 5)
	$html .= $_SESSION['einsatzplan_5'];
elseif($_POST['table'] == 6)
	$html .= $_SESSION['einsatzplan_6'];
else
	$html .= "";


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
$filePath = ABSPATH . 'wp-content/uploads/einsatzplan.pdf';
$pdf = fopen($filePath, 'w');
fwrite($pdf, $file);
fclose($pdf);

$pdf_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
echo "<script>location.href = '".$pdf_url."/wp-content/uploads/einsatzplan.pdf';</script>";
?>