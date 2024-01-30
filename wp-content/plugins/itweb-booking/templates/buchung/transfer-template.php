<?php
require_once get_template_directory() . '/lib/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;
ob_start();
$web_company = Database::getInstance()->getSiteCompany();
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
				<a href="https://<?php echo $_SERVER['HTTP_HOST'] ?>/">
					<?php echo get_custom_logo(); ?>
				</a>
			</div>
		</td>
	</tr>
</table>
<h3>SHUTTLE-SERVICE-HOTLINE<br><?php echo $web_company->phone ?></h3>
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
	<p>vielen Dank für Ihre Buchung über <?php echo $web_company->name ?>.</p>
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
<p>Sobals Sie abholbereit sind, rufen Sie bitte die Shuttle-Hotline an.</p>
<p>Sie haben noch Fragen? Schreiben Sie uns einfach eine E-Mail an
<a href='mailto:<?php echo $web_company->email ?>'><?php echo $web_company->email ?></a> oder rufen Sie uns unter <a href='tel:<?php echo $web_company->phone ?>'><?php echo $web_company->phone ?></a> an. <br>
Montag bis Freitag von 07:00 bis 20:00 Uhr. Aus dem dt. Festnetz zum Ortstarif. Mobilfunkkosten abweichend.</p>
<p>Mit freundlichen Grüßen</p>
<p><?php echo $web_company->name ?></p>
<br>
<table class="footer">
	<tbody>
		<tr>
			<td>
			<?php echo $web_company->name ?><br>
			Geschäftsführer: <?php echo $web_company->owner ?> <br>
			Sitz des Unternehmens: <?php echo $web_company->location ?> <br>
			</td>
			<td>
				Mail: <?php echo $web_company->email ?> <br>
				Telefon: <?php echo $web_company->phone ?> <br>
				<?php echo $web_company->street ?> <br>
				<?php echo $web_company->zip ?> <?php echo $web_company->location ?> <br>
				USt-IdNr.: <?php echo $web_company->ust_id ?> 
			</td>
			<td>
				<?php echo $web_company->bank ?> <br>
				IBAN: <?php echo $web_company->iban ?> <br>
				BIC/SWIFT Code: <?php echo $web_company->bic ?> <br>
				Kontoinhaber: <br>
				<?php echo $web_company->name ?>
			</td>
		</tr>
	</tbody>
</table>
<?php
$content = ob_get_clean();
if($email != null){
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
	wp_mail( $email, 'Ihre Transferbuchung', $content, $headers, $filePath );
}
?>