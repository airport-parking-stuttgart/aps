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

<?php
if($_GET['inv-year'] >= date('Y')){
	if($_GET['inv-year'] > date('Y') || ($year == date('Y') && $_GET['month'] >= date('m')))
		die('Zugriff nicht erlaubt.');
}

if(isset($_GET['tr']))
	$user_id = $_GET['tr'];
else
	$user_id = get_current_user_id();
$product = HotelTransfers::getTransferProduct($user_id);

if($product->is_for != 'hotel')
	die('Zugriff nicht erlaubt.');

$anschrift = explode(",", $product->adress);

//$hotelTransfers = HotelTransfers::getHotelTransfersForInvioce($_GET['month'], $_GET['inv-year']);
$hotelTransfers_hib = HotelTransfers::getHotelTransfersForInvioce_Hin($_GET['month'], $_GET['inv-year'], $user_id);
$hotelTransfers_zurück = HotelTransfers::getHotelTransfersForInvioce_Zurück($_GET['month'], $_GET['inv-year'], $user_id);
$hotelTransfers_beide = HotelTransfers::getHotelTransfersForInvioce_Beide($_GET['month'], $_GET['inv-year'], $user_id);

$sumPersonen_hin = 0;
$preis_hin = 0;
$mwst = 0;
$sumPersonen_zurück = 0;
$preis_zurück = 0;
$sumPersonen_beide = 0;
$preis_beide = 0;

if(count($hotelTransfers_hib) > 0){
	foreach($hotelTransfers_hib as $b){		
		$order = wc_get_order($b->Buchung);
		$variation = new WC_Product_Variation($b->variation_id);
		$name = $variation->get_name();
		$personen = explode(' - ', $name)[1];
		$personen = filter_var($personen, FILTER_SANITIZE_NUMBER_INT);
		$sumPersonen_hin += $personen;
		$preis_hin += round(($b->Betrag / 119 * 100),2);
		$mwst += round(($b->Betrag / 119 * 19),2);
		
	}
}
if(count($hotelTransfers_zurück) > 0){
	foreach($hotelTransfers_zurück as $b){		
		$order = wc_get_order($b->Buchung);
		$variation = new WC_Product_Variation($b->variation_id);
		$name = $variation->get_name();
		$personen = explode(' - ', $name)[1];
		$personen = filter_var($personen, FILTER_SANITIZE_NUMBER_INT);
		$sumPersonen_zurück += $personen;
		$preis_zurück += round(($b->Betrag / 119 * 100),2);
		$mwst += round(($b->Betrag / 119 * 19),2);
		
	}
}
if(count($hotelTransfers_beide) > 0){
	foreach($hotelTransfers_beide as $b){		
		$order = wc_get_order($b->Buchung);
		$variation = new WC_Product_Variation($b->variation_id);
		$name = $variation->get_name();
		$personen = explode(' - ', $name)[1];
		$personen = filter_var($personen, FILTER_SANITIZE_NUMBER_INT);
		$sumPersonen_beide += $personen;
		$preis_beide += round(($b->Betrag / 119 * 100),2);
		$mwst += round(($b->Betrag / 119 * 19),2);
		
	}
}
if(isset($_GET['tr']))
	$transferlist = HotelTransfers::getHotelTransfers_ForBackend($user_id, $_GET['inv-year']."-".$_GET['month']."-01", $_GET['inv-year']."-".$_GET['month']."-".cal_days_in_month(CAL_GREGORIAN, $_GET['month'], $_GET['inv-year']));
else
	$transferlist = HotelTransfers::getHotelTransfers($_GET['inv-year']."-".$_GET['month']."-01", $_GET['inv-year']."-".$_GET['month']."-".cal_days_in_month(CAL_GREGORIAN, $_GET['month'], $_GET['inv-year']));

$summeNetto = $preis_hin + $preis_zurück + $preis_beide;
$personenGes = $sumPersonen_hin + $sumPersonen_zurück + ($sumPersonen_beide * 2);

?>

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
    <tr>
		<td>
			<div style="visibility: hidden">APG</div>
			<div class="shop-address"><?php echo $product->parklot ?></div>
			<div>
				<?php //echo $currentUser->first_name . ' ' . $currentUser->last_name ?>
			</div>
			<div>
				<?php echo $anschrift[0] ?>
			</div>
			<div>
				<?php echo $anschrift[1] ?>
			</div>
		</td>
		<td class="order-data">
            <table>
				<tr class="invoice-number">
					<th style="text-align: right;padding-right: 10px;"><?php echo "Rechnungs-Nr."; ?></th>
					<td><?php echo $product->parklot_short."-".$_GET['month']."-".$_GET['inv-year']; ?></td>
				</tr>
				<tr class="order-date">
					<th style="text-align: right;padding-right: 10px;"><?php echo 'Rechnungsdatum:'; ?></th>
					<td><?php echo "1. ".$monate[$_GET['month']]." ".$_GET['inv-year']; ?></td>
				</tr>
				<tr class="order-date">
					<th style="text-align: right;padding-right: 10px;"><?php echo 'Leistungszeitraum:'; ?></th>
					<td><?php echo $monate[$_GET['month']]." ".$_GET['inv-year']; ?></td>
				</tr>
            </table>
        </td>
    </tr>
</table>
<br><br>
<div class="top-table-info">
	<h3>Rechnung Monat <?php echo $monate[$_GET['month']]." ".$_GET['inv-year']; ?></h3>
	<p>Sehr geehrte Damen und Herren,</p>
	<p>im Leistungszeitraum vom <strong>1.<?php echo " " . $monate[$_GET['month']] ?><?php echo " ".$_GET['inv-year'] ?> bis zum 
			<?php echo cal_days_in_month(CAL_GREGORIAN, $_GET['month'], $_GET['inv-year']) . '. ' . $monate[$_GET['month']] . ' ' . $_GET['inv-year'] ?>
			</strong>haben wir in Ihrem Auftrag
			folgende Kunden befördert. Am Ende des Dokumentes sind die Buchungen im Einzelnen aufgelistet. 
			Die Abrechnung erfolgt auf Basis der vermittelten Kunden im angegebenen Leistungszeitraum.</p>

</div>

<table border="1" class="orders-info">
	<thead>
	<tr>
		<th>Transfer</th>
		<th>Personen</th>
		<th>Betrag</th>
	</tr>
	</thead>
	<tbody>
		<tr>
			<td><?php echo "Zum Flughafen"; ?></td>
			<td><?php echo $sumPersonen_hin ?></td>
			<td class="text-right"><?php echo $preis_hin > 0 ? number_format($preis_hin, 2, '.', '') : "0.00" ?> €</td>
		</tr>
		<tr>
			<td><?php echo "Rücktransfer"; ?></td>
			<td><?php echo $sumPersonen_zurück ?></td>
			<td class="text-right"><?php echo $preis_zurück > 0 ? number_format($preis_zurück, 2, '.', '') : "0.00" ?> €</td>
		</tr>
		<tr>
			<td><?php echo "Hin- und Rücktransfer"; ?></td>
			<td><?php echo $sumPersonen_beide * 2 ?></td>
			<td class="text-right"><?php echo $preis_beide > 0 ? number_format($preis_beide, 2, '.', '') : "0.00" ?> €</td>
		</tr>
		<tr>
			<td>&nbsp; </td>
			<td>&nbsp; </td>
			<td>&nbsp; </td>
		</tr>
		<tr>
			<td><?php echo "Netto"; ?></td>
			<td><?php echo $personenGes > 0 ? $personenGes : "0"; ?></td>
			<td class="text-right"><?php echo $summeNetto > 0 ? number_format($summeNetto, 2, '.', '') : "0.00" ?> €</td>
		</tr>
		<tr>
			<td><?php echo "+19% MwSt."; ?></td>
			<td></td>
			<td class="text-right"><?php echo $mwst > 0 ? number_format($mwst, 2, '.', '') : "0.00" ?> €</td>
		</tr>
		<tr>
			<td><strong><?php echo "Rechnungsbetrag"; ?></strong></td>
			<td></td>
			<td class="text-right"><strong><?php echo $summeNetto > 0 ? number_format($summeNetto + $mwst, 2, '.', '') : "0.00" ?> €</strong></td>
		</tr>
	</tbody>
</table>
<?php if($summeNetto > 0): ?>
<p>
    Der Rechnungsbetrag in Höhe von <strong><?php echo number_format($summeNetto + $mwst, 2, '.', '') ?> €</strong> bitten wir Sie in den nächsten Tagen auf die
	angegebene Bankverbindung zu überweisen.
</p>
<?php else: ?>
<p>
    Es ist kein Rechnungsbetrag entstanden.
</p>
<?php endif;?>
<p>Mit freundlichen Grüßen</p>
<p>APS-Airport-Parking-Stuttgart GmbH</p>
<br><br><br><br><br><br><br><br><br><br><br><br><br><br>
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
<?php if(count($transferlist) > 0): ?>
	<h3>Transferliste <?php echo $monate[$_GET['month']]." ".$_GET['inv-year']; ?></h3>

	<table border="1" class="orders-info">
		<thead>
			<tr>
				<th>B.Nr.</th>
				<th>Kunde</th>
				<th>Transfer FH</th>
				<th></th>
				<th>Rücktransfer</th>
				<th></th>
				<th>Personen</th>
				<th>Betrag</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($transferlist as $transfer): ?>
		<?php
			$order = wc_get_order($transfer->order_id);
			$variation = new WC_Product_Variation($transfer->variation_id);
			$name = $variation->get_name();
			
			if($order){
				if ($order->get_status() == 'completed' || $order->get_status() == 'processing') : ?>
				<tr>
					<td><?php echo $transfer->token ?></td>
					<td><?php echo $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ?></td>
					<td><?php echo $transfer->datefrom ? date('d.m.Y', strtotime($transfer->datefrom)) : '-' ?></td>
					<td><?php echo $transfer->transfer_vom_hotel ? date('H:i', strtotime($transfer->transfer_vom_hotel)) : '-' ?></td>
					<td><?php echo /*$userName . ' - ' . */($transfer->dateto ? date('d.m.Y', strtotime($transfer->dateto)) : '-') ?></td>
					<td><?php echo $transfer->ankunftszeit_ruckflug ? date('H:i', strtotime($transfer->ankunftszeit_ruckflug)) : '-' ?></td>
					<td><?php echo explode(' - ', $name)[1]; ?></td>
					<td><?php echo get_post_meta($transfer->order_id, '_order_total', true); ?><</td>
				</tr>
				<?php endif; ?>
			<?php } ?>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>