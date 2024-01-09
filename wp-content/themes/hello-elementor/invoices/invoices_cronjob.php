<?php
require_once __DIR__ . '/../lib/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

global $wpdb;
define('_THEME_URL', get_template_directory(), true);
$users = get_users(array(
    'role' => 'operator',
    'order' => 'ASC'
));
$i = 0;
foreach ($users as $operator) {
    $i++;
	$sent = false;
    $curYear = date('Y'); // current year
    $lastMonth = date('n') - 1; // current month
    $curDay = (int)date('j'); // current day
    $invDate = (int)get_field('operator_invoice_date', 'user_' . $operator->ID);
    $invSent = $wpdb->get_row("select * from {$wpdb->prefix}itweb_sent_invoices where year(`date`) = {$curYear} and month(`date`) = {$lastMonth} and operator_id = {$operator->ID}");
    if($curDay < $invDate || $invSent){
        continue;
    }

    session_start();
    $_SESSION['oid'] = $operator->ID;
    $_SESSION['month'] = $lastMonth;
    ob_start();
    if (file_exists(__DIR__ . '/template.php')) {
        include(__DIR__ . '/template.php');
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
    $fileName = date('d.m-Y') . '-' . $operator->ID;
    if(!file_exists(ABSPATH . 'wp-content/uploads/monthly-invoices')){
        mkdir(ABSPATH . 'wp-content/uploads/monthly-invoices');
    }
    $filePath = ABSPATH . 'wp-content/uploads/monthly-invoices/' . $fileName . '.pdf';
    $pdf = fopen($filePath, 'w');
    fwrite($pdf, $file);
    fclose($pdf);
	
        $msg = "<h3>Abrechnung Monat $stringMonth</h3>
				Sehr geehrte Damen und Herren, <br><br>
				im Leistungszeitraum vom $stringMonth $curYear haben wir in Ihrem Auftrag folgende Kundengelder laut beigefügter
				Provisionsrechnung vereinnahmt.<br>
				Die Abrechnung erfolgt auf Basis der vermittelten angereisten Kunden im angegebenen Leistungszeitraum.<br>
				<p>Sie haben noch Fragen? Schreiben Sie uns einfach eine E-Mail an
					<a href='mailto:info@a-p-germany.de'>info@a-p-germany.de</a> oder rufen Sie uns
					unter <a href='tel:+49 (0) 711 22 051 247'>+49 (0) 711 22 051 247</a> an.</p>
					<p>Montag bis Freitag von 11:00 bis 19:00 Uhr.
					Aus dem dt. Festnetz zum Ortstarif. Mobilfunkkosten abweichend.</p>
					<p>Mit freundlichen Grüßen</p>
					<p>Airport Parking Germany GmbH<br>Raiffeisenstraße 18, 70794 Filderstadt<br>
					<a href='www.airport-parking-germany.de'>www.airport-parking-germany.de</a></p>";
		

    if(wp_mail($operator->user_email, 'Provisionsgutschrift - ' . date('d.m.Y'), $msg, array('Content-Type: text/html; charset=UTF-8'), $filePath)){
        //wp_mail('info@a-p-germany.de', 'Provisionsgutschrift - ' . date('d.m.Y'), $msg, array('Content-Type: text/html; charset=UTF-8'), $filePath)
		$wpdb->insert("{$wpdb->prefix}itweb_sent_invoices", [
            'date'=>date_format(date_create('01-'.$lastMonth.'-'.$curYear), 'Y-m-d'),
            'operator_id'=>$operator->ID
        ]);
    }
    unset($dompdf);

}