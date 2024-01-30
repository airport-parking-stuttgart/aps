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
	
	.header img{
		width: 200px !important;
		height: auto;
	}
	
	.p_infos p{
		font-size: 12px;
	}

</style>

<!--<img src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=http%3A%2F%2Fwww.google.com%2F&choe=UTF-8" title="Link to Google.com" />-->
<div class="header">
	<a href="https://<?php echo $_SERVER['HTTP_HOST'] ?>/">
            <?php echo get_custom_logo(); ?>
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
		<?php if($web_company->name): ?>
		<h2>Parkplatzticket - <?php echo $web_company->name ?></h2>
	    <?php else: ?>
		<h2>Parkplatzticket</h2>
		<?php endif; ?>
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
			$service += 20;
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
		<h4>Hinweis</h4>
		<p>
		<?php echo $parklot->confirmation_note; ?>
		</p>
	<?php endif; ?>

    <?php if($web_company->name != null && $web_company->email != null && $web_company->phone != null): ?>
    <h4><?php echo $web_company->name ?></h4>
    <p>
        Falls Sie Fragen haben bezüglich Ihrer Parkplatzbuchung, zögern Sie bitte nicht, uns per E-Mail unter
        <?php echo $web_company->email ?> oder telefonisch von Montag bis Freitag von 11:00 bis 19:00 Uhr unter <?php echo $web_company->phone ?> zu kontaktieren.
    </p>
	<?php endif; ?>
</div>
<div class="clear"></div>
<?php if($lotType == 'shuttle'): ?>
<?php else: ?>

<?php endif; ?>
<br><br><br>
<div class="footer">
    <div class="row">
        <div class="col-item">
			<?php if($web_company->street): ?>
            <p class="footer-p"><?php echo $web_company->street ?></p>
			<?php endif; ?>
			<?php if($web_company->zip != null && $web_company->location != null): ?>
            <p class="footer-p"><?php echo $web_company->zip . " " . $web_company->location ?></p>
			<?php endif; ?>
			<?php if($web_company->phone): ?>
            <p class="footer-p">Telefon: <?php echo $web_company->phone ?></p>
			<?php endif; ?>
        </div>
        <div class="col-item">
			<?php if($web_company->email): ?>
            <p class="footer-p"><?php echo $web_company->email ?></p>
			<?php endif; ?>
            <p class="footer-p">www.<?php echo $_SERVER['HTTP_HOST'] ?></p>
			<p></p>
        </div>
		<?php if($web_company->bank != null && $web_company->iban != null && $web_company->bic != null): ?>
        <div class="col-item">
            <p class="footer-p"><?php echo $web_company->bank ?></p>
            <p class="footer-p">IBAN: <?php echo $web_company->iban ?></p>
            <p class="footer-p">BIC/SWIFT Code: <?php echo $web_company->bic ?></p>
        </div>
		<?php endif; ?>
        <?php if($web_company->name): ?>
		<div class="col-item">
            <p class="footer-p">Mit <?php echo $web_company->name ?></p>
            <p class="footer-p">günstig und sicher am Flughafen</p>
        </div>
		<?php endif; ?>
		<div class="clear"></div>		
    </div>
</div>