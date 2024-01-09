<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly ?>
<?php
require_once(__DIR__ . '/../../../plugins/parking-custom-functions/Helper.php');

$operators = Helper::getInstance()->getOperators();
$oid = null;
if (Helper::getInstance()->isAdmin()) {
    $oid = isset($_GET['oid']) ? $_GET['oid'] : null;
} else {
    $oid = get_current_user_id();
}
$oid = $_SESSION['oid'];
$_month = $_SESSION['month'];

//if(!Helper::getInstance()->isAdmin() && isset($_GET['oid'])){
//    die('You are not authorized to access this page!');
//}
$isMonthly = $oid && $_month;
if ($isMonthly) {
    global $wpdb;
    $settings = $wpdb->get_row("select * from {$wpdb->prefix}options where option_name = 'wpo_wcpdf_documents_settings_invoice'");
    $settings = unserialize($settings->option_value)['number_format'];
    $month = $_month;
//        $oid = $oid;
    $date = date('Y') . '-' . $month . '-' . '1';
    $row = $wpdb->get_row("select * from {$wpdb->prefix}monthly_invoices where operator_id = {$oid} and month(date) = {$month}");
    if (!$row) {
        $data = $wpdb->insert("{$wpdb->prefix}monthly_invoices", [
            'operator_id' => $oid,
            'date' => $date,
            'date_created' => date('Y-m-d H:i:s')
        ]);
        $row = $wpdb->get_row("select * from {$wpdb->prefix}monthly_invoices where operator_id = {$oid} and month(date) = {$month}");
    }
    $id = sprintf("%0{$settings['padding']}d", $row->id);
    $invMumber = $settings['prefix'] . $id . $settings['suffix'];
    $invDate = date_format(date_create($row->date), 'd.m.Y');
    $lastMonth = date('n') - 1; // current month
//        if ($_month && $_month > $lastMonth) {
//            die('You are not authorized to access this page!');
//        }
}

switch ($_month) {
  case 1:
    $stringMonth = 'Januar';
    break;
  case 2:
    $stringMonth = 'Februar';
    break;
  case 3:
    $stringMonth = 'März';
    break;
  case 4:
    $stringMonth = 'April';
    break;
  case 5:
    $stringMonth = 'Mai';
    break;
  case 6:
    $stringMonth = 'Juni';
    break;
  case 7:
    $stringMonth = 'Juli';
    break;
  case 8:
    $stringMonth = 'August';
    break;
  case 9:
    $stringMonth = 'September';
    break;
  case 10:
    $stringMonth = 'Oktober';
    break;
  case 11:
    $stringMonth = 'November';
    break;
  case 12:
    $stringMonth = 'Dezember';
    break;
  default:
    $stringMonth = '';
} 

?>


<table class="order-data-addresses">
    <tr>
        <td class="address billing-address">
            <div>
                <?php if (get_theme_mod('itweb24_theme_logo')) : ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>">
                        <img src="<?php echo esc_url(get_theme_mod('itweb24_theme_logo')); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
                    </a>
                <?php else : ?>
                    <div class="shop-name"><h3><?php echo get_bloginfo(); ?></h3></div>
                <?php endif; ?>
            </div>
        </td>
    </tr>
    <tr>
<td>
    <div style="visibility: hidden">APG</div>
    <div class="shop-address"><?php echo get_field('firmenname', 'user_' . $oid) ?></div>
    <div>
        <?php //echo $currentUser->first_name . ' ' . $currentUser->last_name ?>
    </div>
    <div>
        <?php echo get_field('strasse', 'user_' . $oid) ?>
    </div>
    <div>
        <?php echo get_field('plz_ort', 'user_' . $oid) ?>
    </div>
</td>
        <td class="order-data">
            <table>
                <?php if ($isMonthly) : ?>
                    <tr class="order-date">
                        <th style="text-align: right;padding-right: 10px;"><?php echo 'USt.ID Kunde:'; ?></th>
                        <td><?php echo get_field('ust_id', 'user_' . $oid) ?></td>
                    </tr>
                    <tr class="invoice-number">
                        <th style="text-align: right;padding-right: 10px;"><?php _e('Invoice Number:', 'woocommerce-pdf-invoices-packing-slips'); ?></th>
                        <td><?php echo $invMumber; ?></td>
                    </tr>
                    <tr class="order-date">
                        <th style="text-align: right;padding-right: 10px;"><?php echo 'Rechnungsratum:'; ?></th>
                        <td><?php echo $invDate; ?></td>
                    </tr>
                    <tr class="order-date">
                        <th style="text-align: right;padding-right: 10px;"><?php echo 'Leistungszeitraum:'; ?></th>
                        <td><?php echo $stringMonth . ' ' . $curYear; ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        </td>
    </tr>
</table>

<?php

$curYear = date('Y'); // current year
$curMonth = date('n'); // current month
$curDay = date('j'); // current month

//    if ($_month && $_month > $curMonth) {
//    die('You are not authorized to access this page!');
//    }
$monthSql = $_month ? " /*and year(post.post_date) = year(curdate()) and month(post.post_date) = '" . $_month . "' */and month(ito.datefrom) = '".$_month."'/* and now() >= date(ito.datefrom)*/" : '';
$user = wp_get_current_user();
$userMeta = get_user_meta($oid);
$operator = $wpdb->get_row("select * from {$wpdb->prefix}users where id = {$oid}");
$provision = (int)get_field('operator_provision', 'user_' . $oid);
$provisions = $wpdb->get_results("select 
    distinct parklots.parklotname, parklots.provision
    from {$wpdb->prefix}postmeta postmeta
    join {$wpdb->prefix}posts post on post.id = postmeta.post_id
    join {$wpdb->prefix}wc_order_product_lookup op on post.id = op.order_id
    join {$wpdb->prefix}itweb_orders ito ON ito.order_id = op.order_id
    join {$wpdb->prefix}itweb_products products on op.product_id = products.productid
    join {$wpdb->prefix}itweb_parklots parklots on products.lotid = parklots.id
    join {$wpdb->prefix}users users on parklots.operator_id = users.id
    where (post.post_status = 'wc-completed' or post.post_status = 'wc-processing') and ito.deleted = 0 and users.id = " . $oid . " and postmeta.meta_key = '_order_total'" . $monthSql);

$total = $wpdb->get_row("select 
    sum(postmeta.meta_value/(1+0.19)) total_payments,
    count(postmeta.meta_value) total_orders
    from {$wpdb->prefix}postmeta postmeta
    join {$wpdb->prefix}posts post on post.id = postmeta.post_id
    join {$wpdb->prefix}wc_order_product_lookup op on post.id = op.order_id
    join {$wpdb->prefix}itweb_orders ito ON ito.order_id = op.order_id
    join {$wpdb->prefix}itweb_products products on op.product_id = products.productid
    join {$wpdb->prefix}itweb_parklots parklots on products.lotid = parklots.id
    join {$wpdb->prefix}users users on parklots.operator_id = users.id
    where (post.post_status = 'wc-completed' or post.post_status = 'wc-processing') and ito.deleted = 0 and users.id = " . $oid . " and postmeta.meta_key = '_order_total'" . $monthSql);
foreach($provisions as $key=>$value){
    $provisions[$key]->price = (float)$total->total_payments * (float)$value->provision / 100;
}
$parklots = $wpdb->get_results("select * from {$wpdb->prefix}itweb_parklots where deleted = 0");
$orders = [];
foreach ($parklots as $p) {
    $sql = "select parklots.id,
    parklots.parklotname,
    count(postmeta.meta_value) total_orders,
    /*sum(postmeta.meta_value) total_payments,*/
    sum(postmeta.meta_value/(1+0.19)) total_payments
    from {$wpdb->prefix}postmeta postmeta
    join {$wpdb->prefix}posts post on post.id = postmeta.post_id
    join {$wpdb->prefix}wc_order_product_lookup op on post.id = op.order_id
    join {$wpdb->prefix}itweb_orders ito ON ito.order_id = op.order_id
    join {$wpdb->prefix}itweb_products products on op.product_id = products.productid
    join {$wpdb->prefix}itweb_parklots parklots on products.lotid = parklots.id
    join {$wpdb->prefix}users users on parklots.operator_id = users.id
    join {$wpdb->prefix}itweb_types types on types.id = parklots.type_id
    where (post.post_status = 'wc-completed' or post.post_status = 'wc-processing') and ito.deleted = 0 and users.id = " . $oid . " and postmeta.meta_key = '_order_total' and parklots.id = " . $p->id . $monthSql;
//die($sql);
    $row = $wpdb->get_row($sql);
    $orders[] = $row;
}

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
</style>
<!--    <select name="" id="invMonth">-->
<!--        <option value="">Choose month</option>-->
<!--        --><?php //foreach ($months as $key => $month) : ?>
<!--            --><?php //if ($curMonth > $key) : ?>
<!--                <option value="--><?php //echo $key ?><!--">--><?php //echo $month ?><!--</option>-->
<!--            --><?php //elseif ($curMonth == $key && $curDay >= 10): ?>
<!--                <option value="--><?php //echo $key ?><!--">--><?php //echo $month ?><!--</option>-->
<!--            --><?php //endif; ?>
<!--        --><?php //endforeach; ?>
<!--    </select>-->
<?php if ($_month) : ?>
	<?php $daysInMonth = date("t", mktime(0, 0, 0, $month, 1, $year));  ?>
    <div class="top-table-info">
        <h3>Abrechnung Monat <?php echo $stringMonth . " " . $curYear; ?></h3>
        <p>Sehr geehrte Damen und Herren,</p>
        <p>im Leistungszeitraum vom <strong>1.<?php echo $_month ?>.<?php echo $curYear ?> bis zum 
                <?php echo $daysInMonth . '.' . $_month . '.' . $curYear ?>
                </strong>haben wir in Ihrem Auftrag
				folgende Kundengelder laut beigefügter Einzelabrechnung vereinnahmt. Ab Seite 2 des Dokumentes sind die Buchungen im Einzelnen aufgelistet. 
				Die Abrechnung erfolgt auf Basis der vermittelten angereisten Kunden im angegebenen Leistungszeitraum.</p>

    </div>
<?php endif; ?>
<?php if ($orders && count($orders) > 0) : ?>
    <table border="1" class="orders-info">
        <thead>
        <tr>
            <th>Produkt</th>
            <th>Umsätze</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order) : ?>
			<?php if($order->parklotname == "" || $order->parklotname == null) continue; ?>
            <tr>
                <td><?php echo $order->parklotname; ?></td>
                <td class="text-right"><?php echo number_format((float)$order->total_payments > 0 ? ($order->total_payments * 1.19) : 0, 2, '.', ''); ?> €</td>
            </tr>
        <?php endforeach; ?>
        <?php
        $bruttoPrice = number_format((float)$total->total_payments * 0.19, 2, '.', '');
        ?>
		<tr>
			<td>&nbsp; </td>
			<td>&nbsp; </td>
		</tr>
        <tr>
            <td class=""><strong>Gesamt Brutto Umsatz</strong></td>
            <td class="text-right"><strong><?php echo number_format((float)$total->total_payments + (float)$bruttoPrice, 2, '.', '');?> €</strong></td>
		</tr>
		<tr>
			<td>&nbsp; </td>
			<td>&nbsp; </td>
		</tr>
		<tr>
            <td><strong>Gesamt Netto Umsatz</strong></td>
            <td class="text-right"><strong><?php echo number_format((float)$total->total_payments, 2, '.', '') ?> €</strong></td>
        </tr>
		<tr>
			<td>&nbsp; </td>
			<td>&nbsp; </td>
		</tr>
<?php endif; ?>
<?php
$provisionPrice = $provisions[0]->price;
//foreach($provisions as $p){
//    $provisionPrice += $p->price;
//}
$taxPrice = $provisionPrice * 19 / 100;
$adminPrice = $provisionPrice + $taxPrice;
$operatorPrice = $total->total_payments + $bruttoPrice - $adminPrice;
?>

        <tr>
            <td><strong>Provision APG</strong></td>
            <td class="text-right"><strong><?php echo number_format((float)$provisionPrice, 2, '.', '') ?> €</strong></td>
        </tr>

    <tr>
        <td><strong>zzgl Ust (19%)</strong></td>
        <td class="text-right"><strong><?php echo number_format((float)$taxPrice, 2, '.', '') ?> €</strong></td>
    </tr>
    <tr class="adm-price">
        <td><strong>Provison APG Brutto</strong></td>
        <td class="text-right"><strong><?php echo number_format((float)$adminPrice, 2, '.', '') ?> €</strong></td>
    </tr>
		<tr>
			<td>&nbsp; </td>
			<td>&nbsp; </td>
		</tr>
    <tr>
        <td><strong>Auszahlungsbetrag</strong></td>
        <td class="text-right"><strong><?php echo number_format((float)$operatorPrice, 2, '.', '') ?></strong></td>
    </tr>
    </tbody>
</table>

<p>
    Der Auszahlungsbetrag i.H.v. <strong><?php echo number_format((float)$operatorPrice, 2, '.', '') ?> €</strong> wird Ihnen in den nächsten Tagen auf Ihrer
	angegebenen Bankverbindung gut geschrieben.
</p>
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
		HRNr.: B 775876 <br>
		Registergericht: Amtsgericht Stuttgart
		</td>
		<td>
			Mail: info@a-p-germany.de <br>
			Telefon: +49 711 2205 1247 <br>
			Raiffeisenstraße 18 <br>
			70794 Filderstadt <br>
			Ust.ID: DE337098816 
		</td>
		<td>
			Kreissparkasse Esslingen-Nürtingen <br>
			IBAN: DE50 6115 0020 0103 5053 16 <br>
			BIC: ESSLDE66XXX <br>
			Kontoinhaber: <br>
			APG-Airport-Parking-Germany GmbH
		</td>
	</tr>
</tbody>
</table>

	<div class="bookings">
	<?php if($oid == 21):?>
	<br><br><br><br>
	<? endif;?>
	<br><h2>Buchungen <?php echo $stringMonth; ?></h2>
	
	<?php foreach ($orders as $order) : ?>
		<?php 
		if($order->parklotname == "" || $order->parklotname == null) continue; 
		$sql = "SELECT o.order_id, DATE(o.datefrom) AS datefrom, TIME(o.datefrom) AS timefrom, DATE(o.dateto) AS dateto, TIME(o.dateto) AS timeto, os.status
			FROM {$wpdb->prefix}itweb_orders o
			INNER JOIN {$wpdb->prefix}wc_order_stats os ON os.order_id = o.order_id
			WHERE o.parklot_id = ".$order->id." and o.deleted = 0 and os.status = 'wc-processing' and month(o.datefrom) = '".$_month."'";
		$bookings = $wpdb->get_results($sql);
		?>
			<br><h3><?php echo $order->parklotname ?></h3>
		<?php if(count($bookings) >= 1):?>
			<table>
				<tr>
					<th>Buchungs-Nr.</th>
					<th>Anreisedatum</th>
					<th>Anreisezeit</th>
					<th>Abreisedatum</th>
					<th>Abreisezeit</th>
					<th>Parkdauer</th>					
					<th>Personen</th>
					<th>Preis</th>
				</tr>

					<?php foreach ($bookings as $booking) : ?>
						<?php if($booking->status == "wc-processing"): ?>
						<tr>
							<td><?php echo get_post_meta($booking->order_id, 'token', true) ?></td>
							<td><?php echo $booking->datefrom ?></td>
							<td><?php echo $booking->timefrom ?></td>
							<td><?php echo $booking->dateto ?></td>
							<td><?php echo $booking->timeto ?></td>
							<td><?php echo getDaysBetween2Dates(	
											new DateTime($booking->datefrom),
											new DateTime($booking->dateto));?></td>
							<td><?php if(get_post_meta($booking->order_id, '_persons_nr', true) != null) echo get_post_meta($booking->order_id, '_persons_nr', true); else echo "-" ?></td>
							<td><?php echo get_post_meta($booking->order_id, '_order_total', true) ?></td>
						</tr>
						<?php endif; ?>
					<?php endforeach; ?>

			</table>
		<?php else:?>
			<p> Buchungen nicht vorhanden.</p>
		<?php endif; ?>
	<?php endforeach; ?>
	</div>
