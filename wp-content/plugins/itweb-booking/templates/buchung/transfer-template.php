<?php
require_once get_template_directory() . '/lib/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;
ob_start();
?>

<style>
* {
	font-size: 13px;
}
	table {
		width: 100%;
		border-collapse: collapse;
		text-align: left;
	}

	table.orders-info {
		margin-bottom: 50px;
		border: 1px solid black;
		width: 75%;
	}

	table.orders-info th,
	table.orders-info td {
		padding: 5px;
	}

	table.total-info tr td:last-child {
		text-align: right;
	}


	table.footer {
		width: 100%;
	}

	table.footer td{
		font-size: 11px;
	}

	.text-right {
		text-align: right;
	}
	.bookings table th{
		text-align: left;
	}
	.site-logo a img{
		width: 180px;
		height: auto;
	}
</style>
<table class="order-data-addresses">
	<tr>
		<td class="address billing-address">
			<div class="site-logo">
				<a href="https://airport-parking-stuttgart.de/">
					<img src="https://airport-parking-stuttgart.de/wp-content/uploads/2021/12/APS-Logo.png">
				</a>
			</div>
		</td>
	</tr>
</table>
<h3>SHUTTLE-SERVICE-HOTLINE<br>+49 176 10 031 148</h3>
<br><br><br>
<table class="order-data-addresses">
	<tr>
		<td class="address billing-address">
			<div><?php echo $vorname . " " . $nachname ?></div>
		</td>
		<td class="order-data">
			<table>				
				<tr class="order-date">
					<th style="text-align: right;padding-right: 10px;"><?php echo 'Buchungsdatum:'; ?></th>
					<td><?php echo date('d.m.Y'); ?></td>
				</tr>
				<tr class="order-date">
					<th style="text-align: right;padding-right: 10px;"><?php echo 'Buchungs-Nr:'; ?></th>
					<td><?php echo $token; ?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<br><br>
<div class="top-table-info">
	<h3>Buchungsbestätigung</h3>
	<p>Sehr geehrte Damen und Herren,</p>
	<p>vielen Dank für Ihre Buchung über APS.</p>
</div>
<table border="1" class="orders-info">
	<thead>
	<tr>
		<th>Hintransfer</th>
		<th>Rücktransfer</th>
		<th>Personen</th>
		<th>Betrag</th>
	</tr>
	</thead>
	<tbody>
		<tr>
			<td><?php echo $datefrom ? date('d.m.Y', strtotime($datefrom)) . " " . $transfer_vom_hotel : "-" ?></td>
			<td><?php echo $dateto ? date('d.m.Y', strtotime($dateto)) . " " . $ankunftszeit_ruckflug : "-" ?></td>
			<td><?php echo $personen ?></td>
			<td><?php echo $order->get_total() ?> €</td>
		</tr>
		
	</tbody>
</table>
<p>Die Parkflächen befinden sich in der Raiffeisenstraße 18, 70794 Filderstadt. Sobals Sie abholbereit sind, rufen Sie bitte die Shuttle-Hotline an.</p>
<p>Sie haben noch Fragen? Schreiben Sie uns einfach eine E-Mail an
<a href='mailto:info@airport-parking-stuttgart.de'>info@airport-parking-stuttgart.de</a> oder rufen Sie uns unter <a href='tel:+49 711 22 051 245'>+49 711 22 051 245</a> an. <br>
Montag bis Freitag von 07:00 bis 20:00 Uhr. Aus dem dt. Festnetz zum Ortstarif. Mobilfunkkosten abweichend.</p>
<p>Mit freundlichen Grüßen</p>
<p>APS-Airport-Parking-Stuttgart GmbH</p>
<br>
<table class="footer">
	<tbody>
		<tr>
			<td>
			APS-Airport-Parking-Stuttgart GmbH<br>
			Geschäftsführer: Erdem Aras <br>
			Sitz des Unternehmens: Filderstadt <br>
			Registergericht: Amtsgericht Stuttgart
			</td>
			<td>
				Mail: info@airport-parking-stuttgart.de <br>
				Telefon: +49 (0) 711 22 051 245 <br>
				Raiffeisenstraße 18 <br>
				70794 Filderstadt <br>
				USt-IdNr.: DE313061031 
			</td>
			<td>
				Sparkasse Esslingen <br>
				IBAN: DE08 6115 0020 0102 8060 23 <br>
				BIC/SWIFT Code: ESSLDE66XXX <br>
				Kontoinhaber: <br>
				APS-Airport-Parking-Stuttgart GmbH
			</td>
		</tr>
	</tbody>
</table>
<?php
$content = ob_get_clean();
if($user_id == 26 && $email != null){
	// instantiate and use the dompdf class
	$options = new Options();
	$options->set('isRemoteEnabled', true);
	$dompdf = new Dompdf($options);
	$dompdf->loadHtml($content);

	// (Optional) Setup the paper size and orientation
	$dompdf->setPaper('A4', 'landscape');

	// Render the HTML as PDF
	$dompdf->render();

	$file = $dompdf->output();
		$fileName = $token;
	if(!file_exists(ABSPATH . 'wp-content/uploads/transfere-invoices')){
		mkdir(ABSPATH . 'wp-content/uploads/transfere-invoices');
	}
	$filePath = ABSPATH . 'wp-content/uploads/transfere-invoices/' . $fileName . '.pdf';
	$pdf = fopen($filePath, 'w');
	fwrite($pdf, $file);
	fclose($pdf);			

	$headers = array('Content-Type: text/html; charset=UTF-8');
	wp_mail( $email, '[APS] Ihre Transferbuchung', $content, $headers, $filePath );
}
?>