<?php
if($order->get_total() != "0.00"){
	$order_price = $order->get_total();
}
elseif(get_post_meta($order_id, '_order_total', true) != null){
	$order_price = get_post_meta($order_id, '_order_total', true);
}
else{
	$order_price = Pricelist::calculateAndDiscount($parklot->product_id, dateFormat(get_post_meta($order_id, 'Anreisedatum', true)), dateFormat(get_post_meta($order_id, 'Abreisedatum', true)));
}
$methode = get_post_meta($order_id, '_payment_method_title', true);
?>
<style>
	*{
		font-family: Arial, Helvetica, sans-serif;
	}
    .header {
        padding: 20px 0;
        border-bottom: 3px solid #0172dd;
        margin-bottom: 0px;
    }

    .qr-code > div:first-child img {
        max-width: 100%;
        width: 100%;
    }

    .qr-code > div:first-child {
        padding: 10px;
        box-sizing: border-box;
        border: 1px solid black;
        margin-right: 20px;
        float: left;
        width: 150px;
    }

    .qr-code > div:last-child {
        float: right;
        width: calc(100% - 320px);
    }

    .row .col-item {
        width: 25%;
        padding: 0 10px 0 0;
        float: left;
        box-sizing: border-box;
        margin: 0px 0;
    }
	
	.row .col-item p{
		font-size: 11px;
	}

    .col-item:first-child > p:first-child {
        margin-top: 0px;
    }

    p, h1, h3 {
        margin: 0;
    }

    h1, h2, h3, h4, h5, h6 {
        color: #1285de;
    }

    .text-content h4 {
        margin-top: 10px;
    }
	
    .text-content p {
        font-size: 11px;
    }
	
	.footer-p {
        font-size: 10px !important;
    }
	
    .text-content p {
		margin-top: -10px;
    }

    .order-data {
        margin-top: 10px;
    }

    .order-data h1, .order-data h2 {
        color: #c1c1c1;
    }

    .clear {
        clear: both;
    }
	
	.hcol-right {
		float: right;
		font-size: 11px;
		border: 1px solid;
		padding: 10px;
		border-left: none;
    }
	.hcol-left {
		float: right;
		font-size: 11px;
		border: 1px solid;
		padding: 10px;
		border-right: none;
    }
	
	.logo{
		width: 200px !important;
		height: auto;
	}
	
	.p_infos p{
		font-size: 12px;
	}

</style>

<!--<img src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=http%3A%2F%2Fwww.google.com%2F&choe=UTF-8" title="Link to Google.com" />-->
<div class="header">
	<a href="https://airport-parking-stuttgart.de/">
            <img class="logo" src="https://airport-parking-stuttgart.de/wp-content/uploads/2021/12/cropped-APS-Logo-1.png"
                 alt="https://airport-parking-stuttgart.de">
    </a>

	<div class="hcol-right">
		<?php echo "<strong>Buchungsnummer</strong> <br>" . get_post_meta($order_id, 'token', true); ?>
	</div>
	<div class="hcol-left">
		<?php echo "<strong>Reisender</strong> <br>" . get_post_meta($order_id, '_billing_first_name', true) . " " . get_post_meta($order_id, '_billing_last_name', true)?>
	</div>
</div>

<div class="qr-code">
	<div>
		<img src="<?php echo url() . '/wp-content/uploads/qrcodes/' . basename($filenameQR) ?>" alt="">
    </div>
	<div  class="p_infos">
		<h2>Parkplatzticket - Airport Parking Stuttgart</h2>
	    <?php if(($parklot->parkhaus == "Parkhaus überdacht" || $parklot->parkhaus == "Parkplatz überdacht") && $lotType == 'shuttle')
			echo "<p>überdacht - Shuttle Service inkl.</p>";
        else if(($parklot->parkhaus == "Parkhaus nicht überdacht" || $parklot->parkhaus == "Parkplatz nicht überdacht") && $lotType == 'shuttle')
			echo "<p>nicht überdacht - Shuttle Service inkl.</p>";
		else if($lotType == 'valet')
			echo "<p>Valet Service inkl.</p>";
		$rabatt = get_post_meta($order_id, 'Rabatt', true);
		if($rabatt){
			$can = get_post_meta($order_id, 'Nicht_stornierbar', true);
			echo "<p>".$rabatt;
			if($can)
				echo " - Nicht_stornierbar";
			echo "</p>";
		}
		?>
		<p><strong><?php echo $parklot->parklot; ?></strong></p>
		<p><?php echo $parklot->adress;?></p>
		<p>
			<?php if($lotType == 'shuttle') : ?>
						<strong>Shuttle-Service-Hotline</strong>
			<?php else: ?>
						<strong>Valet-Service-Hotline</strong>
			<?php endif; ?>
        </p>
        <p><a href="tel: <?php echo $parklot->phone; ?>"><?php echo $parklot->phone; ?></a></p>
		<?php if($lotType == 'shuttle') : ?>
			<p>Shuttlezeiten von 03:00 bis zum letzten Flieger.</p>
		<?php endif; ?>
	</div>
    <div class="clear"></div>
</div>
<div class="clear"></div>
<br>
<div class="order-data row">
    <div class="col-item">
		<?php if($lotType == 'shuttle') : ?>
			<p><strong>Anreise Parkplatz</strong></p>
		<?php else: ?>
			<p><strong>Anreise Flughafen</strong></p>
		<?php endif; ?>               
        <p><?php echo date_format(date_create(get_post_meta($order_id, 'Anreisedatum', true) . " " . get_post_meta($order_id, 'Uhrzeit von', true)), 'd.m.Y H:i') ?></p>
    </div>
    <div class="col-item">
        <p><strong>Rückkehr Flughafen</strong></p>
        <p><?php echo date_format(date_create(get_post_meta($order_id, 'Abreisedatum', true) . " " . get_post_meta($order_id, 'Uhrzeit bis', true)), 'd.m.Y H:i') ?></p>
    </div>
	<?php if ($lotType == 'shuttle') : ?>
		<div class="col-item">
			<p><strong>Shuttle Service</strong></p>     
				<p>4 Personen inkl.</p>
		</div>
	<?php else: ?>
		<div class="col-item">
			<p><strong>Telefon</strong></p>     
			<p><?php echo get_post_meta($order_id, '_billing_phone', true) ?></p>
		</div>
	<?php endif; ?>
	<div class="col-item">
        <?php if ($lotType == 'shuttle') : ?>
			<p><strong>Reisende</strong></p>
			<p><?php echo get_post_meta($order_id, 'Personenanzahl', true) ?> Personen</p>
		<?php endif; ?>
    </div>
    <div class="clear"></div>
</div>
<div class="order-data row">
	<div class="col-item">
		<p><strong>Hinflug</strong></p>
		<p><?php echo get_post_meta($order_id, 'Hinflugnummer', true); ?></p>
	</div>
	<div class="col-item">
		<p><strong>Rückflug</strong></p>
		<p><?php echo get_post_meta($order_id, 'Rückflugnummer', true); ?></p>
	</div>
	<div class="col-item">
		<p><strong>KFZ Kennzeichen</strong></p>
		<p><?php echo get_post_meta($order_id, 'Kennzeichen', true); ?></p>
	</div>
    <div class="col-item">
        <p><strong>Buchungsdatum</strong></p->
        <p><?php echo date_format(date_create($order->order_date), 'd.m.Y') ?></p>
    </div>
	<div class="clear"></div>
</div>
<?php if($lotType == 'valet'): ?>
<div class="order-data row">
	<div class="col-item">
		<p><strong>Model</strong></p>
		<p><?php echo get_post_meta($order_id, 'Fahrzeughersteller', true); ?></p>
	</div>
	<div class="col-item">
		<p><strong>Typ</strong></p>
		<p><?php echo get_post_meta($order_id, 'Fahrzeugmodell', true); ?></p>
	</div>
	<div class="col-item">
		<p><strong>Farbe</strong></p>
		<p><?php echo get_post_meta($order_id, 'Fahrzeugfarbe', true); ?></p>
	</div>
	<div class="clear"></div>
</div>
<?php endif; ?>
<div class="order-data row">
    <div class="col-item">
        <p><strong>Parkdauer</strong></p>
			<p><?php echo getDaysBetween2Dates(new DateTime(date_format(date_create(get_post_meta($order_id, 'Anreisedatum', true)), 'Y-m-d')), new DateTime(date_format(date_create(get_post_meta($order_id, 'Abreisedatum', true)), 'Y-m-d'))) ?> Tage</p>
	</div>
    <div class="col-item">
        <p><strong>Parkgebühren</strong></p->
        <p>€ <?php echo number_format($order_price, 2, '.', '') ?> <strong>(<?php echo $methode ?>)</strong></p>
    </div>
	<?php if($additionalPrice != '0.00'): ?>
    <?php
		$service = 0.00;
		if($additionalPrice != '0.00')
			$service += $additionalPrice;
		if(get_post_meta($order_id, 'Sperrgepack', true) == "1"){
			$year = date('Y', strtotime(get_post_meta($order_id, 'Anreisedatum', true)));
			if($year == "2023")
				$service += 5;
			else
				$service += 10;
		}
	?>
	<div class="col-item">
        <p><strong>Service</strong></p->
        <p>€ <?php echo number_format($service, 2, '.', '') ?> (Barzahlung)</p>
    </div>
	<?php endif; ?>
    <div class="col-item">
        <p><strong>Gesamtpreis</strong></p->
        <p>€ <?php echo number_format($order_price + $service, 2, '.', '') ?></p>
    </div>
    <div class="clear"></div>
</div>
<div class="order-data row">
	<br><p><?php echo 'Gewählte Bezahlmethode: ' . $order->get_payment_method_title() ?></p>
</div>
<div class="clear"></div>

<div class="text-content">
	<?php if($parklot->confirmation_byArrival != ""): ?>
		<h4>Bei der Anreise</h4>
		<p>
		<?php echo $parklot->confirmation_byArrival; ?>
		</p>
	<?php endif; ?>
	<?php if($parklot->confirmation_byDeparture != ""): ?>
		<h4>Bei der Rückkehr</h4>
		<p>
		<?php echo $parklot->confirmation_byDeparture; ?>
		</p>
	<?php endif; ?>
	<?php if($parklot->confirmation_note != ""): ?>
		<?php
		if(date_format(date_create(get_post_meta($order_id, 'Anreisedatum', true)), 'Y-m-d') < '2023-12-31')
			$hinweis = "Ab der 5. Person wird ein Aufschlag in Höhe von 5,00 Euro pro Fahrt und Person erhoben.
						Bei einer verspäteten Abreise werden 10,00 EUR für jeden zusätzlichen Tag berechnet.
						Bei Sperrgepäck entsteht ein Aufpreis von 5,00 Euro pro Fahrt.";
		elseif(date_format(date_create(get_post_meta($order_id, 'Anreisedatum', true)), 'Y-m-d') > '2024-01-01')
			$hinweis = "Ab der 5. Person wird ein Aufschlag in Höhe von 10,00 Euro pro Fahrt und Person erhoben.
						Bei einer verspäteten Abreise werden 15,00 EUR für jeden zusätzlichen Tag berechnet.
						Bei Sperrgepäck entsteht ein Aufpreis von 10,00 Euro pro Fahrt.";
		else
			$hinweis = "";
		?>
		<h4>Hinweis</h4>
		<p>
		<?php echo $hinweis . " " .  $parklot->confirmation_note; ?>
		</p>
	<?php endif; ?>

    <h4>Airport Parking Stuttgart</h4>
    <p>
        Falls Sie Fragen haben bezüglich Ihrer Parkplatzbuchung, zögern Sie bitte nicht, uns per E-Mail unter
        info@airport-parking-stuttgart.de oder telefonisch von Montag bis Freitag von 11:00 bis 19:00 Uhr unter +49(0) 711 22 051 245 zu kontaktieren.
    </p>
</div>
<div class="clear"></div>
<?php if($lotType == 'shuttle'): ?>
<?php else: ?>

<?php endif; ?>
<br><br><br>
<div class="footer">
    <div class="row">
        <div class="col-item">
            <p class="footer-p">Raiffeisenstraße 18</p>
            <p class="footer-p">70794 Filderstadt</p>
            <p class="footer-p">Telefon: +49 711 22 051 245</p>
        </div>
        <div class="col-item">
            <p class="footer-p">info@airport-parking-stuttgart.de</p>
            <p class="footer-p">www.airport-parking-stuttgart.de</p>
			<p></p>
        </div>
        <div class="col-item">
            <p class="footer-p">Sparkasse Esslingen</p>
            <p class="footer-p">IBAN: DE08 6115 0020 0102 8060 23</p>
            <p class="footer-p">BIC/SWIFT Code: ESSLDE66XXX</p>
        </div>
        <div class="col-item">
            <p class="footer-p">Mit Airport Parking Stuttgart</p>
            <p class="footer-p">günstig und sicher am Flughafen</p>
        </div>
		<div class="clear"></div>
		
    </div>
</div>