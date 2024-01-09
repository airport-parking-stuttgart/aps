
<style>
	*{
		font-family: Arial, Helvetica, sans-serif;
	}
    .header {
        padding: 20px 0;
        border-bottom: 3px solid #0172dd;
        margin-bottom: 20px;
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
    table.info tr td:last-child {
        text-align: right;
    }

    .row .col-item {
        width: 25%;
        padding: 0 10px 0 0;
        float: left;
        box-sizing: border-box;
        margin: 10px 0;
    }

    .col-item:first-child > p:first-child {
        margin-top: 10px;
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
	
    .text-content p, .footer p {
        font-size: 12px;
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
		font-size: 12px;
		border: 1px solid;
		padding: 10px;
		border-left: none;
    }
	.hcol-left {
		float: right;
		font-size: 12px;
		border: 1px solid;
		padding: 10px;
		border-right: none;
    }
	.footer p{
		font-size: 10px;
	}
    table.footer {
        width: 100%;
    }

    table.footer td{
        font-size: 11px;
    }
</style>
<!--<img src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=http%3A%2F%2Fwww.google.com%2F&choe=UTF-8" title="Link to Google.com" />-->
<div class="header">
    <?php if (get_theme_mod('itweb24_theme_logo')) : ?>
        <a href="<?php echo esc_url(home_url('/')); ?>">
            <img src="<?php echo esc_url(get_theme_mod('itweb24_theme_logo')); ?>"
                 alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
        </a>
    <?php else: ?>
        <div class="shop-name"><h3><?php $this->shop_name(); ?></h3></div>
    <?php endif; ?>

</div>

<table class="order-data-addresses">
	<tr>
		<td>
			<div style="visibility: hidden">APG</div>
			<div class="shop-address"><?php echo get_post_meta($order_id, '_billing_first_name', true) . " " . get_post_meta($order_id, '_billing_last_name', true) ?></div>
			<div>
				<?php //echo $currentUser->first_name . ' ' . $currentUser->last_name ?>
			</div>
			<div>
				<?php echo get_post_meta($order_id, '_billing_address_1', true) ?>
			</div>
			<div>
				<?php echo get_post_meta($order_id, '_billing_postcode', true) . " " . get_post_meta($order_id, '_billing_city', true) ?>
			</div>
		</td>
        <td class="order-data">
            <table>
                    <tr class="invoice-number">
                        <th style="text-align: right;padding-right: 10px;"><?php _e('Invoice Number:', 'woocommerce-pdf-invoices-packing-slips'); ?></th>
                        <td><?php echo date('d.m.Y') . "-". $order_id; ?></td>
                    </tr>
                    <tr class="order-date">
                        <th style="text-align: right;padding-right: 10px;"><?php echo 'Rechnungsratum:'; ?></th>
                        <td><?php echo date('d.m.Y') ?></td>
                    </tr>
            </table>
        </td>
    </tr>
</table>

<div class="top-table-info">
	<br><br><h3>Rechnung: <?php echo date('d.m.Y') . "-". $order_id; ?></h3><br><br>
				<?php if(get_post_meta($order_id, '_billing_grander', true) == "male"): ?>
				Sehr geehrter Herr <?php echo $order->get_formatted_billing_full_name() ?>,<br/><br/>
				<?php else:?>
				Sehr geehrte Frau <?php echo $order->get_formatted_billing_full_name() ?>,<br/><br/>
				<?php endif;?>
	<p>vielen Dank für Ihre Buchung und das damit verbundene Vertrauen.</p>
	<p>Gemäß Ihrer Buchung erlauben wir uns hiermit Ihnen diese Rechnung zu stellen. Die Reservierung des Parkplätzes erfolgt nach Geldeingang.</p>
	<p>Anschließend erhalten Sie von uns eine Buchungsbestätigung.</p><br><br>
</div>

<table border="1" class="info">
	<tbody>
		<tr>
			<td>Produkt</td>
			<td><?php if($_SESSION["extern"])
						echo $_SESSION["extern_name"];
					else
						echo $parklot->parklotname; ?>
			</td>
		</tr>
		<tr>
			<td>Anreise</td>
			<td><?php if($_SESSION["extern"]) : ?>
					<?php echo date_format(date_create($_SESSION['output']['CarPark']['ArrivalDate']), 'd.m.Y') . " " . $_POST['order_time_to'] ?>
				<?php else: ?>                
					<?php echo date_format(date_create($parklot->datefrom), 'd.m.Y H:i') ?>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td>Rückkehr</td>
			<td><?php if($_SESSION["extern"]) : ?>
					<?php echo date_format(date_create($_SESSION['output']['CarPark']['DepartDate']), 'd.m.Y'). " " . $_POST['order_time_from']?>
				<?php else: ?> 
					<?php echo date_format(date_create($parklot->dateto), 'd.m.Y H:i') ?>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td>Parkdauer</td>
			<td><?php echo getDaysBetween2Dates(new DateTime($parklot->datefrom), new DateTime($parklot->dateto)) ?> Tage</td>
		</tr>
		<?php if ($isShuttle) :?>
		<tr>
			<td>Reisende <?php if(!$_SESSION["extern"]) : ?>
			<?php if ($isShuttle) : ?>   
					(4 Personen inkl.)
			<?php endif; ?>
			<?php else: ?>
				<?php if(!$_SESSION['produkt']->valet_included && $_SESSION['produkt']->passengers_transfer != null): ?>
						(<?php echo $_SESSION['produkt']->passengers_transfer ?> Personen inkl.)
				<?php endif; ?>
			<?php endif; ?>
			</td>
			<td><?php echo get_post_meta($order_id, '_persons_nr', true) ?> Personen</td>
		</tr>
		<?php endif;?>
		<tr>
			<td>Parkdauer</td>
			<td><?php echo getDaysBetween2Dates(new DateTime($parklot->datefrom), new DateTime($parklot->dateto)) ?> Tage</td>
		</tr>
		<tr>
			<td>&nbsp; </td>
			<td>&nbsp; </td>
		</tr>
		<tr>
			<td><strong>Gesamtpries inkl. MwSt</strong></td>
			<td><strong><?php echo $order->get_total() ?> €</strong></td>
		</tr>
	</tbody>
</table><br>
	
<p>Bitte überweisen Sie den Gesamtbetrag von <strong><?php echo $order->get_total() ?> €</strong> auf das u. a. Bankkonto.</p><br>
<p>Mit freundlichen Grüßen</p>
<p>APG-Airport-Parking-Germany GmbH</p>
<br><br><br>

<table class="footer">
<tbody>
	<tr>
		<td>
			APG- Airport-Parking-Germany GmbH<br>
			Geschäftsführer: Erdem Aras <br>
			Sitz der Gesellschaft: Filderstadt <br>
			HRNr.: 000000000 <br>
			Registergericht: Amtsgericht Stuttgart
		</td>
		<td>
			Mail: info@a-p-germany.de <br>
			Telefon: +49 711 2205 1247 <br>
			Raiffeisenstraße 18 <br>
			70794 Filderstadt <br>
			Ust.ID: 000000 
		</td>
		<td>
			Sparkasse Esslingen-Nürtingen <br>
			IBAN: DE55 6115 0020 0103 5053 23 <br>
			BIC: ESSLXXXXX <br>
			Kontoinhaber: <br>
			APG-Airport-Parking-Germany GmbH
		</td>
	</tr>
</tbody>
</table>