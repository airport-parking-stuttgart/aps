<style>
th, .order-token{
	white-space: nowrap;
}
</style>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-logo">
		<img class="adm-logo" src="<?php echo home_url(); ?>/wp-content/uploads/2021/05/logo-e1596314559277.png" alt="" width="300" height="200">
	</div>
</div>
<?php
ini_set("memory_limit", "1024M");
$filter['list'] = 1;
$db = Database::getInstance();
$product_groups = Database::getInstance()->getProductGroups();
$dateFrom = isok($_GET, 'date') ? dateFormat($_GET['date']) : date('Y-m-d');
$dateTo = isok($_GET, 'date') ? dateFormat($_GET['date']) : date('Y-m-d');
$year = isok($_GET, 'date') ? date("Y", strtotime($_GET['date'])) : date('Y');
$filter['datum_von'] = $dateFrom;
$filter['datum_bis'] = $dateTo;
$filter['filter_product'] = $_GET['product'];

$_SESSION['dateFrom'] = $dateFrom;
$_SESSION['dateTo'] = $dateTo;

global $wpdb;

//$orders = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix . "itweb_orders WHERE DATE(date_from) >= '".date('Y-m-d')."' AND TIME(date_from) = '00:00' AND TIME(date_to) = '00:00'");
$orders = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix . "itweb_orders WHERE DATE(date_from) BETWEEN '".$dateFrom."' AND '".$dateTo."' AND TIME(date_from) = '00:00' AND TIME(date_to) = '00:00'");

foreach($orders as $order){
	
	$sql = "UPDATE ".$wpdb->prefix."itweb_orders
			SET date_from = concat(date(date_from), ' ".get_post_meta($order->order_id, 'Uhrzeit von', true).":00'), date_to = concat(date(date_to), ' ".get_post_meta($order->order_id, 'Uhrzeit bis', true).":00')
			WHERE order_id = '".$order->order_id."'";
	
	$wpdb->query($sql);
}

$allorders = Database::getInstance()->get_anreiseliste($filter);

$user = wp_get_current_user();

if ($user->user_login == 'aras' || $user->user_login == 'cakir' || $user->user_login == 'sergej')
	$editOK = "";
else
	$editOK = "readonly";

$datum = strtotime($dateFrom);
$kw = date("W", $datum);
$wochentage = array("so", "mo", "di", "mi", "do", "fr", "sa");
$w = $wochentage[date("w", strtotime($dateFrom))];
$fahrer = $db->getEinsatzplanOfDay($kw, $year, $w);

?>

<div class="page container-fluid anreise-template <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Anreiseliste Shuttle</h3>
    </div>
    <div class="page-body">
        <form class="form-filter m10">
			<div class="row ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Filtern</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row">
						<div class="col-12 col-md-1 ui-lotdata-date">
							<input type="text" placeholder="Datum" name="date"
								   value="<?php echo $_GET['date'] ?>" class="single-datepicker form-control form-item">
						</div>
						<div class="col-sm-12 col-md-2">
							<select name="product" class="form-item form-control">
								<option value="">Standort</option>
								<?php foreach ($product_groups as $group) : ?>						
									<option value="<?php echo $group->id ?>" <?php echo $group->id == $_GET['product'] ? "selected" : "" ?>><?php echo $group->name ?></option>						
									<?php $child_product_groups = Database::getInstance()->getChildProductGroupsByPerentId($group->id); ?>
									<?php if(count($child_product_groups) > 0): ?>
										<?php foreach ($child_product_groups as $child_group) : ?>
										<option value="<?php echo $child_group->id ?>" <?php echo $child_group->id == $_GET['product'] ? "selected" : "" ?>><?php echo " - " . $child_group->name ?></option>
										<?php endforeach; ?>
									<?php endif; ?>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-12 col-md-1">
							<button class="btn btn-primary d-block w-100" type="submit">Filter</button>
						</div>
						<div class="col-sm-12 col-md-2">                    
							<a href="<?php echo '/wp-admin/admin.php?page=fahrerportal' ?>" class="btn btn-secondary d-block w-100" >Zurücksetzen</a>
						</div>
					</div><br>
					<div class="row">
						<div class="col-2">
							<a href="#" class="abreise-btn-template btn btn-primary">Abreiseliste Shuttle</a>
						</div>
					</div>
				</div>
			</div>
        </form>
		<div class="btn">
			<form action="<?= get_site_url() . '/wp-content/plugins/itweb-booking/templates/fahrerportal/anreiseliste-pdf.php'; ?>" method="post">
				<button type="submit" id="btnExport" value="Export to PDF" class="btn btn-success">Anreiseliste exportieren</button>
			</form>
		</div>
		<br><br>
		<table class="table-responsive" id="arrivalBooking">
			<thead>
				<tr>
					<th>Nr.</th>
					<th>PCode</th>
					<th>Buchung</th>
					<th>Kunde</th>
					<th>Anreisedatum</th>
					<th>Anreisezeit</th>
					<th>Personen</th>
					<th>Parkplatz</th>
					<th>Abreisedatum</th>
					<th>Rückflugnummer</th>
					<th>Landung</th>
					<th>Betrag</th>
					<th>Fahrer</th>
					<th>Sonstiges</th>
					<th>Service</th>
			</thead>
			<tbody>
				<?php $i = 1;
				foreach ($allorders as $order) : ?>
					<?php
					if ($order->Status == "wc-cancelled") {
						$anreisezeit = date('H:i', strtotime("23:59"));
						$color = "#ff0000";
					} else {
						$anreisezeit = date('H:i', strtotime(get_post_meta($order->order_id, 'Uhrzeit von', true)));
						$color = $order->Color;
					}

					if (get_post_meta($order->order_id, '_billing_first_name', true) == null)
						$customor = get_post_meta($order->order_id, '_billing_last_name', true);
					elseif (get_post_meta($order->order_id, '_billing_last_name', true) == null)
						$customor = get_post_meta($order->order_id, '_billing_first_name', true);
					elseif (strlen(get_post_meta($order->order_id, '_billing_last_name', true)) < 2)
						$customor = get_post_meta($order->order_id, '_billing_first_name', true);
					elseif (strlen(get_post_meta($order->order_id, '_billing_last_name', true)) > 2)
						$customor = get_post_meta($order->order_id, '_billing_last_name', true);
					else
						$customor = get_post_meta($order->order_id, '_billing_last_name', true);


					$additionalPrice = "0.00";
					$services = Database::getInstance()->getBookingMetaAsResults($order->order_id, 'additional_services');
					if (count($services) > 0) {
						foreach ($services as $v) {
							$s = Database::getInstance()->getAdditionalService($v->meta_value);
							$additionalPrice += $s->price;
						}
					}

					$_SESSION['additionalPrice'] = $additionalPrice;
					?>
				

					<tr style="background-color: <?php echo $color ?> !important" export-color="<?php echo $color ?>" class="row<?php echo $i % 2; ?>">
						<input type="hidden" class="order-nr" value="<?php echo $order->order_id ?>">
						<td class="nr" export-color="<?php echo $order->Color ?>">
							<?php echo $i ?>
						</td>
						<td class="order-pcode">
							<?php echo $order->Code; ?>
						</td>
						<td class="order-token">
							<?php echo get_post_meta($order->order_id, 'token', true); ?>
						</td>
						<td class="order-kunde">
							<?php echo strip_tags($customor) ?>
						</td>
						<td class="order-datefrom">
							<?php echo dateFormat(get_post_meta($order->order_id, 'Anreisedatum', true), 'de') ?>
						</td>
						<td class="order-timefrom">
							<?php echo $anreisezeit ?>
						</td>
						<td class="order-persons">
							<?php echo get_post_meta($order->order_id, 'Personenanzahl', true) ?>
						</td>
						<td class="order-parkplatz">
							<?php echo get_post_meta($order->order_id, 'Parkplatz', true) ?>
						</td>
						<td class="order-dateto" style="">
							<?php if (get_post_meta($order->order_id, 'AbreisedatumEdit', true) != "") echo date('d.m.Y', strtotime(get_post_meta($order->order_id, 'AbreisedatumEdit', true))); else echo ""; ?>
						</td>
						<td class="order-ruckflug">
							<?php echo get_post_meta($order->order_id, 'RückflugnummerEdit', true) != "" ? get_post_meta($order->order_id, 'RückflugnummerEdit', true) : ""; ?>
						</td>
						<td class="order-landung">
							<?php if (get_post_meta($order->order_id, 'Uhrzeit bis Edit', true) != "") echo get_post_meta($order->order_id, 'Uhrzeit bis Edit', true); else echo ""; ?>
						</td>
						<td class="order-betrag" style="position: relative;">
							<?php if (get_post_meta($order->order_id, '_transaction_id', true) == "barzahlung") : ?>
								<?php echo get_post_meta($order->order_id, '_order_total', true) ?>
							<?php else : ?>
								-
							<?php endif; ?>
						</td>
						<td class="order-fahrer">
							<?php //$zeit = date('H', strtotime($anreisezeit))*1; ?>
							<?php							
								foreach($fahrer as $ma){
									if (str_contains($ma->$w, '-')){
										//$plan = explode("-", $ma->$w);
										//if($zeit >= ($plan[0]*1) && $zeit < ($plan[1]*1)){
										//	$found = 1;
										//	break;
										//}											
										//else
										//	$found = 0;
										$found = 1;
										break;
									}
									else
										$found = 0;
								}								
							?>
							<?php if(count($fahrer) > 0 && $found == 1): ?>							
								<?php echo get_post_meta($order->order_id, 'FahrerAn', true) == get_user_meta( $ma->user_id, 'short_name', true ) ? get_user_meta( $ma->user_id, 'short_name', true ) : "" ?>	
							<?php else: ?>
								<?php echo get_post_meta($order->order_id, 'FahrerAn', true) ?>
							<?php endif; ?>
						</td>
						<td class="order-sonstiges">
							<?php echo get_post_meta($order->order_id, 'Sonstige 1', true) ?>
						</td>
						<td class="order-service">
							<?php echo $additionalPrice != '0.00' ? number_format($additionalPrice, 2, '.', '') : '-'; ?>
						</td>
					</tr>
				<?php $i++;
				endforeach; ?>
			</tbody>
		</table>
        <?php if (count($allorders) <= 0) : ?>
            <p>Keine Ergebnisse gefunden!</p>
        <?php endif; ?>
    </div>
</div>

<!--abresiseliste template-->
<?php
$filter['list'] = 1;

if(isok($_GET, 'filter')){
	unset($_GET['step']);
	unset($_GET['filter']);
}
$db = Database::getInstance();
$product_groups = Database::getInstance()->getProductGroups();
$dateFrom = isok($_GET, 'date') ? dateFormat($_GET['date']) : date('Y-m-d');
$dateTo = isok($_GET, 'date') ? dateFormat($_GET['date']) : date('Y-m-d');
$year = isok($_GET, 'date') ? date("Y", strtotime($_GET['date'])) : date('Y');
$filter['datum_von_Ad'] = $dateFrom;
$filter['datum_bis_Ad'] = $dateTo;
$filter['type'] = "shuttle";
$filter['filter_product'] = $_GET['product'];

$_SESSION['dateFrom'] = $dateFrom;
$_SESSION['dateTo'] = $dateTo;


$allorders = Database::getInstance()->get_abreiseliste($filter);

$user = wp_get_current_user();
if ($user->user_login == 'aras' || $user->user_login == 'cakir' || $user->user_login == 'sergej')
	$editOK = "";
else
	$editOK = "readonly";

$datum = strtotime($dateFrom);
$kw = date("W", $datum);
$wochentage = array("so", "mo", "di", "mi", "do", "fr", "sa");
$w = $wochentage[date("w", strtotime($dateFrom))];
$fahrer = $db->getEinsatzplanOfDay($kw, $year, $w);

?>

<div class="page container-fluid d-none abreise-template <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Abreiseliste Shuttle</h3>
    </div>
    <div class="page-body">
        <form class="form-filter m10">
			<div class="row ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Filtern</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row">
						<div class="col-12 col-md-1 ui-lotdata-date">
							<input placeholder="Datum" type="text" name="date"
								   value="<?php echo $_GET['date'] ?>" class="single-datepicker form-control form-item">
						</div>
						<div class="col-sm-12 col-md-2">
							<select name="product" class="form-item form-control">
								<option value="">Standort</option>
								<?php foreach ($product_groups as $group) : ?>						
									<option value="<?php echo $group->id ?>" <?php echo $group->id == $_GET['product'] ? "selected" : "" ?>><?php echo $group->name ?></option>						
									<?php $child_product_groups = Database::getInstance()->getChildProductGroupsByPerentId($group->id); ?>
									<?php if(count($child_product_groups) > 0): ?>
										<?php foreach ($child_product_groups as $child_group) : ?>
										<option value="<?php echo $child_group->id ?>" <?php echo $child_group->id == $_GET['product'] ? "selected" : "" ?>><?php echo " - " . $child_group->name ?></option>
										<?php endforeach; ?>
									<?php endif; ?>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-12 col-md-1">
							<button class="btn btn-primary d-block w-100" type="submit">Filter</button>
						</div>
						<div class="col-sm-12 col-md-2">                    
							<a href="<?php echo '/wp-admin/admin.php?page=fahrerportal' ?>" class="btn btn-secondary d-block w-100" >Zurücksetzen</a>
						</div>
					</div><br>
					<div class="row">
						<div class="col-2">
							<a href="#" class="anreise-btn-template btn btn-primary">Anreiseliste Shuttle</a>
						</div>
					</div>
				</div>
			</div>
        </form>
		<div class="btn">
			<form action="<?= get_site_url() . '/wp-content/plugins/itweb-booking/templates/fahrerportal/abreiseliste-pdf.php'; ?>" method="post">
				<button type="submit" id="btnExport" value="Export to PDF" class="btn btn-success">Abreiseliste exportieren</button>
			</form>
		</div>
		<br><br>
        <table class="table-responsive" id="returnBooking">
			<thead>
				<tr>
					<th>Nr.</th>
					<th>PCode</th>
					<th>Buchung</th>
					<th>Kunde</th>
					<th>Abreisedatum</th>
					<th>Abreisezeit</th>
					<th>Personen</th>
					<th>Rückflug</th>
					<th>Parkplatz</th>
					<th>Fahrer</th>
					<th>Sonstiges 1</th>
					<th>Sonstiges 2</th>
					<th>Betrag</th>
					<th>Service</th>
				</tr>
			</thead>
			<tbody>
				<?php  $i = 1;
				foreach ($allorders as $order) : ?>
				<?php
					if ($order->Status == "wc-cancelled") {
						$abreisezeit = date('H:i', strtotime("23:59"));
						$color = "#ff0000";
					} else {
						$abreisezeit = date('H:i', strtotime(get_post_meta($order->order_id, 'Uhrzeit bis', true)));
						$color = $order->Color;
					}

					if (get_post_meta($order->order_id, '_billing_first_name', true) == null)
						$customor = get_post_meta($order->order_id, '_billing_last_name', true);
					elseif (get_post_meta($order->order_id, '_billing_last_name', true) == null)
						$customor = get_post_meta($order->order_id, '_billing_first_name', true);
					elseif (strlen(get_post_meta($order->order_id, '_billing_last_name', true)) < 2)
						$customor = get_post_meta($order->order_id, '_billing_first_name', true);
					elseif (strlen(get_post_meta($order->order_id, '_billing_last_name', true)) > 2)
						$customor = get_post_meta($order->order_id, '_billing_last_name', true);
					else
						$customor = get_post_meta($order->order_id, '_billing_last_name', true);


					$additionalPrice = "0.00";
					$services = Database::getInstance()->getBookingMetaAsResults($order->order_id, 'additional_services');
					if (count($services) > 0) {
						foreach ($services as $v) {
							$s = Database::getInstance()->getAdditionalService($v->meta_value);
							$additionalPrice += $s->price;
						}
					}

					$_SESSION['additionalPrice'] = $additionalPrice;
					?>
					<tr style="background-color: <?php echo $color ?> !important" export-color="<?php echo $color ?>" class="row<?php echo $i % 2; ?>">
						<input type="hidden" class="order-nr" value="<?php echo $order->order_id ?>">
						<td class="order-nr" export-color="<?php echo $order->Color ?>">
							<?php echo $i ?>
						</td>
						<td class="order-pcode">
							<?php echo $order->Code ?>
						</td>
						<td class="order-token">
							<?php echo get_post_meta($order->order_id, 'token', true) ?>
						</td>
						<td class="order-kunde">
							<?php echo trim(strip_tags($customor)) ?>
						</td>
						<td class="order-dateto">
							<?php echo dateFormat(get_post_meta($order->order_id, 'Abreisedatum', true), 'de') ?>
						</td>
						<td class="order-timeto">
							<?php echo $abreisezeit ?>
						</td>
						<td class="order-persons">
							<?php echo  get_post_meta($order->order_id, 'Personenanzahl', true) ?>
						</td>
						<td class="order-ruckflug">
							<?php echo get_post_meta($order->order_id, 'Rückflugnummer', true) ?>
						</td>
						<td class="order-parkplatz">
							<?php echo get_post_meta($order->order_id, 'Parkplatz', true) ?>
						</td>
						<td class="order-fahrer">
							<?php //$zeit = date('H', strtotime($abreisezeit))*1; ?>
							<?php
								foreach($fahrer as $ma){
									if (str_contains($ma->$w, '-')){
										//$plan = explode("-", $ma->$w);
										//if($zeit >= ($plan[0]*1) && $zeit < ($plan[1]*1)){
										//	$found = 1;
										//	break;
										//}											
										//else
										//	$found = 0;
										$found = 1;
										break;
									}
									else
										$found = 0;
								}		
							?>
							<?php if(count($fahrer) > 0 && $found == 1): ?>							
								<?php foreach($fahrer as $ma): ?>
									<?php if (str_contains($ma->$w, '-')): ?>
											<?php echo get_post_meta($order->order_id, 'FahrerAb', true) == get_user_meta( $ma->user_id, 'short_name', true ) ? get_user_meta( $ma->user_id, 'short_name', true ) : "" ?>	
									<?php endif; ?>									
								<?php endforeach; ?>
							<?php else: ?>
								<?php echo get_post_meta($order->order_id, 'FahrerAb', true) ?>
							<?php endif; ?>
						</td>
						<td class="order-sonstige1">
							<?php echo get_post_meta($order->order_id, 'Sonstige 1', true) ?>
						</td>
						<td class="order-sonstige2">
							<?php echo get_post_meta($order->order_id, 'Sonstige 2', true) ?>
						</td>
						<td class="order-betrag" style="position:relative;">
							<?php if (get_post_meta($order->order_id, '_transaction_id', true) == "barzahlung") : ?>
								<?php echo get_post_meta($order->order_id, '_order_total', true) ?>
							<?php else : ?>
								-
							<?php endif; ?>
						</td>
						<td class="order-service">
							<?php echo $additionalPrice != '0.00' ? number_format($additionalPrice, 2, '.', '') : '-'; ?>
						</td>
					</tr>
				<?php $i++;
				endforeach; ?>
			</tbody>
		</table>
        <?php if (count($allorders) <= 0) : ?>
            <p>Keine Ergebnisse gefunden!</p>
        <?php endif; ?>
    </div>
</div>
