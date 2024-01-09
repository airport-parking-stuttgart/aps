<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-logo">
		<img class="adm-logo" src="<?php echo home_url(); ?>/wp-content/uploads/2021/05/logo-e1596314559277.png" alt="" width="300" height="200">
	</div>
</div>
<?php
$products = Database::getInstance()->getProducts();

function getDateDiff($date1, $date2, $diff){
    $datetime1 = new DateTime($date1);

    $datetime2 = new DateTime($date2);

    $difference = $datetime1->diff($datetime2);

    return $difference->$diff;
}

// anresiseliste template
// Get All Orders
$data = array(
//    'status' => 'processing',
    'type' => 'shop_order',
    'email' => '',
    'limit' => -1,
    'return' => 'ids',
);

$date = isset($_GET['date']) ? dateFormat($_GET['date']) : date('Y-m-d');
//$date = date('Y-m-d', strtotime('2021-01-01'));

$data['meta_query']['date_clause'] = array(
    'key' => 'Anreisedatum',
    'value' => $date,
    'compare' => '='
);
$allorders = wc_get_orders($data);

$data = [];
foreach ($allorders as $order_id) {
    $order = wc_get_order($order_id);
	$items = $order->get_items();
	foreach ( $items as $item ) {
		$product_id = $item->get_product_id();
	}
	$product = Database::getInstance()->getParklotByProductId($product_id); 
    //$moreDays = dateFormat($order->get_meta('Anreisedatum'), 'de');
    if(getDateDiff($order->get_meta('first_anreisedatum'), $order->get_meta('Anreisedatum'), 'd') > 0){
        $moreDays = getDateDiff($order->get_meta('first_anreisedatum'), $order->get_meta('Anreisedatum'), 'd');
    }
    
	if($order->get_status() == "cancelled"){
		$anreisezeit = date('H:i', strtotime("23:59"));
		$color = "#ff0000";
	}
	else{
		$anreisezeit = date('H:i', strtotime($order->get_meta('Uhrzeit von')));
		$color= $product->color;
	}
	
	$additionalPrice = "0.00";
	$services = Database::getInstance()->getBookingMetaAsResults($order_id, 'additional_services');
	if(count($services) > 0){
		foreach($services as $v){
			$s = Database::getInstance()->getAdditionalService($v->meta_value);
			$additionalPrice += $s->price;
		}
	}
	if($order->get_billing_first_name() == null)
		$customor = $order->get_billing_last_name();
	elseif($order->get_billing_last_name() == null)
		$customor = $order->get_billing_first_name();
	elseif(strlen( $order->get_billing_last_name()) < 2)
		$customor =$order->get_billing_first_name();
	elseif(strlen( $order->get_billing_last_name()) > 2)
		$customor = $order->get_billing_last_name();
	else
		$customor = $order->get_billing_last_name();
	
    $data[] = [
        'Nr.' => $order->get_id(),
        'P.-Code.' => $product->parklot_short, // get_field('parkplatz_code', array_values($order->get_items())[0]->get_product_id()),
		'Typ' => $product->type,
        'Buchungscode' => $order->get_meta('token'),
        //'Datum' => $dateFrom,
        'Kunde' => $customor,
        'Anreisedatum' => dateFormat($order->get_meta('Anreisedatum'), 'de'),
		'Anreisezeit' => $anreisezeit,
        'Personen' => $order->get_meta('Personenanzahl'),
        'Parkplatz' => $order->get_meta('Parkplatz'),
        'Abreisedatum' => dateFormat($order->get_meta('Abreisedatum'), 'de'),
        'Rückflug' => $order->get_meta('Rückflugnummer'),
        'Landung' => date('H:i', strtotime($order->get_meta('Uhrzeit bis'))),
        'Betrag' => $order->get_total(),
        'Fahrer' => $order->get_meta('FahrerAn'),
        'Sonstiges' => $order->get_meta('Sonstige 1'),
		'Service' => $additionalPrice,
		'Color' => $color
    ];
}

// Obtain a list of columns
foreach ($data as $key => $row) {
    $arr[$key]  = $row['Anreisezeit'];
    $name_a[$key] = $row['Kunde'];
}

// Sort the data with volume descending, edition ascending
array_multisort($arr, SORT_ASC, $name_a, SORT_ASC, $data);


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
						<!--
						<div class="col-2">
							<a href="#" class="anreise-valet-btn-template btn btn-primary">Anreiseliste Valet</a>
						</div>
						<div class="col-2">
							<a href="#" class="abreise-valet-btn-template btn btn-primary">Abreiseliste Valet</a>
						</div>
						-->
					</div>
				</div>
			</div>
        </form>
        <table class="dataTable no-footer" id="arrivalBooking">
            <thead>
                <tr>
                    <?php if (count($data) > 0) : ?>
                        <?php foreach ($data[0] as $key => $value) : ?>
							<?php if($key == "Color" || $key == "Typ") continue; ?>
                            <th><?php echo $key ?></th>
                        <?php endforeach; ?>
						<th><?php echo "Aktion" ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($data as $order) :?> 
					<?php if($order['Typ'] == "valet") continue; ?>
					<?php $tmpOrder = wc_get_order($order['Nr.']); ?>
                    <tr class="<?php echo $tmpOrder->get_meta('mark_as_done') == 'done' ? 'mark_done' : '' ?> row<?php echo $i % 2; ?>" export-color="<?php echo $order['Color'] ?>">
                        <input type="hidden" class="order-nr" value="<?php echo $order['Nr.'] ?>">
						<td class="nr" export-color="<?php echo $order['Color'] ?>">
							<?php echo $i ?>
						</td>
                        <td class="order-pcode" style="background-color: <?php echo $order['Color'] ?> !important">
                            <?php echo $order['P.-Code.'] ?>
                        </td>
                        <td class="order-token">
                            <?php echo $order['Buchungscode'] ?>
                        </td>
                        <!--<td>
                            <?php echo $order['Datum'] ?>
                        </td>-->
                        <td class="order-kunde">
                            <?php echo $order['Kunde'] ?>
                        </td>
						<td class="order-dateto">
							<?php if($order['Anreisedatum'] != null && $order['Anreisedatum'] != '01.01.1970') echo $order['Anreisedatum']; else echo ""; ?>
                        </td>
                        <td class="order-timefrom">
                            <?php if($order['Anreisezeit'] != null && $order['Anreisedatum'] != '01.01.1970') echo $order['Anreisezeit']; else echo ""; ?>
                        </td>
                        <td class="order-persons">
                            <?php echo $order['Personen'] ? $order['Personen'] : "" ?>
                        </td>
                        <td class="order-parkplatz">
                            <?php echo $order['Parkplatz'] ? $order['Parkplatz'] : "" ?>
                        </td>
                        <td class="order-dateto">
                            <span style="display: none;"><?php //if($order['Abreisedatum'] != null && $order['Abreisedatum'] != '01.01.1970') echo $order['Abreisedatum']; else echo ""; ?></span>
                        </td>
                        <td class="order-ruckflug">
                            <?php echo $order['Rückflug'] ? $order['Rückflug'] : "" ?>
                        </td>
                        <td class="order-landung"><span style="display: none;"><?php //if($order['Landung'] != null && $order['Abreisedatum'] != '01.01.1970') echo $order['Landung']; else echo ""; ?></span></td>
                        
						<?php if(get_post_meta($order['Nr.'], '_transaction_id')[0] == "barzahlung"): ?>
							<td class="order-betrag"><?php echo $order['Betrag']; if($order['Service'] != '0.00') echo " " . number_format($order['Service'], 2, '.', ''); ?></td>
						<?php else: ?>
							<td class="order-betrag"><?php if($order['Service'] != '0.00') echo number_format($order['Service'], 2, '.', ''); else echo '-'; ?></td>
						<?php endif; ?>
                        <td class="order-fahrer"><?php echo $order['Fahrer'] ?></td>
                        <td class="order-sonstiges"><?php echo $order['Sonstiges'] ?></td>
                        <td class="order-service"><?php if($order['Service'] != '0.00') echo number_format($order['Service'], 2, '.', ''); else echo '-'; ?></td>
						<!--<td>

                            <?php if ($tmpOrder->get_meta('mark_as_done') !== 'done') : ?>
                                <form action="/wp-content/plugins/itweb-booking/classes/Helper.php" method="POST">
                                    <input type="hidden" value="<?php echo $order['Nr.'] ?>" name="id">
                                    <input type="hidden" name="task" value="mark_as_done_anreise_item">
                                    <button class="btn btn-sm btn-primary" type="submit">erledigt</button>
                                </form>
                            <?php endif; ?>
                            <form action="/wp-content/plugins/itweb-booking/classes/Helper.php" method="POST" class="m10">
                                <input type="hidden" value="<?php echo $order['Nr.'] ?>" name="id">
                                <input type="hidden" name="task" value="add_one_day">
                                <input type="hidden" name="list" value="Anreisedatum">
                                <button class="btn btn-sm btn-primary" type="submit">vertagen</button>
                            </form>
                        </td>-->
						<td>-</td>
                    </tr>
                <?php $i++; endforeach; ?>
            </tbody>
        </table>
        <?php if (count($allorders) <= 0) : ?>
            <p>Keine Ergebnisse gefunden!</p>
        <?php endif; ?>
    </div>
</div>

<!--abresiseliste template-->
<?php
// Get All Orders
$data = array(
//    'status' => 'processing',
    'type' => 'shop_order',
    'email' => '',
    'limit' => -1,
    'return' => 'ids',
);

$date = isset($_GET['date']) ? dateFormat($_GET['date_to']) : date('Y-m-d');

$data['meta_query']['date_clause'] = array(
    'key' => 'Abreisedatum',
    'value' => $date,
    'compare' => '='
);
$allorders = wc_get_orders($data);

$data = [];
foreach ($allorders as $order_id) {
    $order = wc_get_order($order_id);
	$items = $order->get_items();
	foreach ( $items as $item ) {
		$product_id = $item->get_product_id();
	}
	$product = Database::getInstance()->getParklotByProductId($product_id); 
    //$dateTo = dateFormat($order->get_meta('Abreisedatum'), 'de');
    if(getDateDiff($order->get_meta('first_abreisedatum'), $order->get_meta('Abreisedatum'), 'd') > 0){
        $moreDays = getDateDiff($order->get_meta('first_abreisedatum'), $order->get_meta('Abreisedatum'), 'd');
    }
	
	if($order->get_status() == "cancelled"){
		$anreisezeit = date('H:i', strtotime("23:59"));
		$color = "#ff0000";
	}
	else{
		$anreisezeit = date('H:i', strtotime($order->get_meta('Uhrzeit bis')));
		$color= $product->color;
	}
	
	if($order->get_billing_first_name() == null)
		$customor = $order->get_billing_last_name();
	elseif($order->get_billing_last_name() == null)
		$customor = $order->get_billing_first_name();
	elseif(strlen( $order->get_billing_last_name()) < 2)
		$customor =$order->get_billing_first_name();
	elseif(strlen( $order->get_billing_last_name()) > 2)
		$customor = $order->get_billing_last_name();
	else
		$customor = $order->get_billing_last_name();
    
    $data[] = [
        'Nr.' => $order->get_id(),
        'P.-Code.' => $product->parklot_short, // get_field('parkplatz_code', array_values($order->get_items())[0]->get_product_id()),
        'Typ' => $product->type,
		'Buchungscode' => $order->get_meta('token'),
        //'Datum' => $dateTo,
        'Kunde' => $customor,
        'Abreisedatum' => dateFormat($order->get_meta('Abreisedatum'), 'de'),
		'Abreisezeit' => $anreisezeit,
        'Personen' => $order->get_meta('Personenanzahl'),
        'Rückflug' => $order->get_meta('Rückflugnummer'),
        'Parkplatz' => $order->get_meta('Parkplatz'),
        'Fahrer' => $order->get_meta('FahrerAb'),
		'Sonstige 1' => $order->get_meta('Sonstige 1'),
        'Sonstige 2' => $order->get_meta('Sonstige 2'),
        'Betrag' => $order->get_total() . ' ' . $order->get_currency(),
		'Color' => $color
    ];
}

// Obtain a list of columns
foreach ($data as $key => $row) {
    $des[$key]  = $row['Abreisezeit'];
    $name_d[$key] = $row['Kunde'];
}

// Sort the data with volume descending, edition ascending
array_multisort($des, SORT_ASC, $name_d, SORT_ASC, $data);

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
						<!--
						<div class="col-2">
							<a href="#" class="anreise-valet-btn-template btn btn-primary">Anreiseliste Valet</a>
						</div>
						<div class="col-2">
							<a href="#" class="abreise-valet-btn-template btn btn-primary">Abreiseliste Valet</a>
						</div>
						-->
					</div>
				</div>
			</div>
        </form>
        <table class="dataTable no-footer" id="returnBooking">
            <thead>
                <tr>
                    <?php if (count($data) > 0) : ?>
                        <?php foreach ($data[0] as $key => $value) : ?>
							<?php if($key == "Color" || $key == "Betrag" || $key == "Typ") continue; ?>
                            <th><?php echo $key ?></th>
                        <?php endforeach; ?>
						<th><?php echo "Aktion" ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($data as $order) : ?>
					<?php if($order['Typ'] == "valet") continue; ?>
					<? $tmpOrder = wc_get_order($order['Nr.']); ?>
                    <tr class="<?php echo $tmpOrder->get_meta('mark_as_done') == 'done' ? 'mark_done' : '' ?> row<?php echo $i % 2; ?>" export-color="<?php echo $order['Color'] ?>">
                        <input type="hidden" class="order-nr" value="<?php echo $order['Nr.'] ?>">
						<td class="nr" export-color="<?php echo $order['Color'] ?>">
							<?php echo $i ?>
						</td>
                        <td class="order-pcode" style="background-color: <?php echo $order['Color'] ?> !important">
                            <?php echo $order['P.-Code.'] ?>
                        </td>
                        <td class="order-token">
                            <?php echo $order['Buchungscode'] ?>
                        </td>
                        <!--<td>
                            <?php echo $order['Datum'] ?>
                        </td>-->
                        <td class="order-kunde">
                            <?php echo $order['Kunde'] ?>
                        </td>
                        <td class="order-dateto">
                            <?php if($order['Abreisedatum'] != null && $order['Abreisedatum'] != '01.01.1970') echo $order['Abreisedatum']; else echo ""; ?>
                        </td>
						<td class="order-timeto">
                            <?php if($order['Abreisezeit'] != null && $order['Abreisedatum'] != '01.01.1970') echo $order['Abreisezeit']; else echo ""; ?>
                        </td>
                        <td class="order-persons">
                           <?php echo $order['Personen'] ? $order['Personen'] : "" ?>
                        </td>
                        <td class="order-ruckflug">
                           <?php echo $order['Rückflug'] ? $order['Rückflug'] : "" ?>
                        </td>
                        <td class="order-parkplatz">
                            <?php echo $order['Parkplatz'] ? $order['Parkplatz'] : "" ?>
                        </td>
						<td class="order-fahrer"><?php echo $order['Fahrer'] ?></td>
                        <td class="order-sonstige1"><?php echo $order['Sonstige 1'] ?></td>
                        <td class="order-sonstige2"><?php echo $order['Sonstige 2'] ?></td>
                        <!--<td>
                            <?php if ($tmpOrder->get_meta('mark_as_done') !== 'done') : ?>
                                <form action="/wp-content/plugins/itweb-booking/classes/Helper.php" method="POST">
                                    <input type="hidden" value="<?php echo $order['Nr.'] ?>" name="id">
                                    <input type="hidden" name="task" value="mark_as_done_anreise_item">
                                    <button class="btn btn-sm btn-primary" type="submit">erledigt</button>
                                </form>
                            <?php endif; ?>
                            <form action="/wp-content/plugins/itweb-booking/classes/Helper.php" method="POST" class="m10">
                                <input type="hidden" value="<?php echo $order['Nr.'] ?>" name="id">
                                <input type="hidden" name="task" value="add_one_day">
                                <input type="hidden" name="list" value="Abreisedatum">
                                <button class="btn btn-sm btn-primary" type="submit">vertagen</button>
                            </form>
                        </td>-->
						<td>-</td>
                    </tr>
                <?php $i++; endforeach; ?>
            </tbody>
        </table>
        <?php if (count($allorders) <= 0) : ?>
            <p>Keine Ergebnisse gefunden!</p>
        <?php endif; ?>
    </div>
</div>

<?
// anresiseliste valet template
// Get All Orders
$data = array(
//    'status' => 'processing',
    'type' => 'shop_order',
    'email' => '',
    'limit' => -1,
    'return' => 'ids',
);

$date = isset($_GET['date']) ? dateFormat($_GET['date']) : date('Y-m-d');
//$date = date('Y-m-d', strtotime('2021-01-01'));

$data['meta_query']['date_clause'] = array(
    'key' => 'Anreisedatum',
    'value' => $date,
    'compare' => '='
);
$allorders = wc_get_orders($data);

$data = [];
foreach ($allorders as $order_id) {
    $order = wc_get_order($order_id);
	$items = $order->get_items();
	foreach ( $items as $item ) {
		$product_id = $item->get_product_id();
	}
	$product = Database::getInstance()->getParklotByProductId($product_id); 
    //$moreDays = dateFormat($order->get_meta('Anreisedatum'), 'de');
    if(getDateDiff($order->get_meta('first_anreisedatum'), $order->get_meta('Anreisedatum'), 'd') > 0){
        $moreDays = getDateDiff($order->get_meta('first_anreisedatum'), $order->get_meta('Anreisedatum'), 'd');
    }
    
	if($order->get_status() == "cancelled"){
		$anreisezeit = date('H:i', strtotime("23:59"));
		$color = "#ff0000";
	}
	else{
		$anreisezeit = date('H:i', strtotime($order->get_meta('Uhrzeit von')));
		$color= $product->color;
	}
	
	$additionalPrice = "0.00";
	$services = Database::getInstance()->getBookingMetaAsResults($order_id, 'additional_services');
	if(count($services) > 0){
		foreach($services as $v){
			$s = Database::getInstance()->getAdditionalService($v->meta_value);
			$additionalPrice += $s->price;
		}
	}
	
	if($order->get_billing_first_name() == null)
		$customor = $order->get_billing_last_name();
	elseif($order->get_billing_last_name() == null)
		$customor = $order->get_billing_first_name();
	elseif(strlen( $order->get_billing_last_name()) < 2)
		$customor =$order->get_billing_first_name();
	elseif(strlen( $order->get_billing_last_name()) > 2)
		$customor = $order->get_billing_last_name();
	else
		$customor = $order->get_billing_last_name();
	
    $data[] = [
        'Nr.' => $order->get_id(),
        'P.-Code.' => $product->parklot_short, // get_field('parkplatz_code', array_values($order->get_items())[0]->get_product_id()),
		'Typ' => $product->type,
        'Buchungscode' => $order->get_meta('token'),
        //'Datum' => $dateFrom,
        'Kunde' => $customor,
        'Anreisedatum' => dateFormat($order->get_meta('Anreisedatum'), 'de'),
		'Anreisezeit' => $anreisezeit,
        'Personen' => $order->get_meta('Personenanzahl'),
        'Parkplatz' => $order->get_meta('Parkplatz'),
        'Abreisedatum' => dateFormat($order->get_meta('Abreisedatum'), 'de'),
        'Rückflug' => $order->get_meta('Rückflugnummer'),
        'Landung' => date('H:i', strtotime($order->get_meta('Uhrzeit bis'))),
        'Betrag' => $order->get_total(),
        'Fahrer' => $order->get_meta('FahrerAn'),
        'Sonstiges' => $order->get_meta('Sonstige 1'),
		'Service' => $additionalPrice,
		'Color' => $color
    ];
}

// Obtain a list of columns
foreach ($data as $key => $row) {
    $arr[$key]  = $row['Anreisezeit'];
    $name_a[$key] = $row['Kunde'];
}

// Sort the data with volume descending, edition ascending
array_multisort($arr, SORT_ASC, $name_a, SORT_ASC, $data);


?>

<div class="page container-fluid d-none anreise-valet-template <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Anreiseliste Valet</h3>
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
						<div class="col-2">
							<a href="#" class="abreise-btn-template btn btn-primary">Abreiseliste Shuttle</a>
						</div>
						<div class="col-2">
							<a href="#" class="abreise-valet-btn-template btn btn-primary">Abreiseliste Valet</a>
						</div>
					</div>
				</div>
			</div>
        </form>
        <table class="dataTable no-footer" id="arrivalBooking_valet">
            <thead>
                <tr>
                    <?php if (count($data) > 0) : ?>
                        <?php foreach ($data[0] as $key => $value) : ?>
							<?php if($key == "Color" || $key == "Typ") continue; ?>
                            <th><?php echo $key ?></th>
                        <?php endforeach; ?>
						<th><?php echo "Aktion" ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($data as $order) :?> 
					<?php if($order['Typ'] == "shuttle") continue; ?>
					<? $tmpOrder = wc_get_order($order['Nr.']); ?>
                    <tr class="<?php echo $tmpOrder->get_meta('mark_as_done') == 'done' ? 'mark_done' : '' ?> row<?php echo $i % 2; ?>" export-color="<?php echo $order['Color'] ?>">
                        <input type="hidden" class="order-nr" value="<?php echo $order['Nr.'] ?>">
						<td class="nr" export-color="<?php echo $order['Color'] ?>">
							<?php echo $i ?>
						</td>
                        <td class="order-pcode" style="background-color: <?php echo $order['Color'] ?> !important">
                            <?php echo $order['P.-Code.'] ?>
                        </td>
                        <td class="order-token">
                            <u><a href="<?php echo '/wp-admin/admin.php?page=buchung-bearbeiten&edit=' . $order['Nr.'] . '&an=1' ?>" target="_blank"><?php echo $order['Buchungscode'] ?></a></u>
                        </td>
                        <!--<td>
                            <?php echo $order['Datum'] ?>
                        </td>-->
                        <td class="order-kunde">
                            <?php echo $order['Kunde'] ?>
                        </td>
						<td class="order-dateto">
							<?php if($order['Anreisedatum'] != null && $order['Anreisedatum'] != '01.01.1970') echo $order['Anreisedatum']; else echo ""; ?>
                        </td>
                        <td class="order-timefrom">
                            <?php if($order['Anreisezeit'] != null && $order['Anreisedatum'] != '01.01.1970') echo $order['Anreisezeit']; else echo ""; ?>
                        </td>
                        <td class="order-persons">
                            <?php echo $order['Personen'] ? $order['Personen'] : "" ?>
                        </td>
                        <td class="order-parkplatz">
                            <?php echo $order['Parkplatz'] ? $order['Parkplatz'] : "" ?>
                        </td>
                        <td class="order-dateto">
                            <span style="display: none;"><?php //if($order['Abreisedatum'] != null && $order['Abreisedatum'] != '01.01.1970') echo $order['Abreisedatum']; else echo ""; ?></span>
                        </td>
                        <td class="order-ruckflug">
                            <?php echo $order['Rückflug'] ? $order['Rückflug'] : "" ?>
                        </td>
                        <td class="order-landung"><span style="display: none;"><?php //if($order['Landung'] != null && $order['Abreisedatum'] != '01.01.1970') echo $order['Landung']; else echo ""; ?></span></td>
                        
						<?php if(get_post_meta($order['Nr.'], '_transaction_id')[0] == "barzahlung"): ?>
							<td class="order-betrag"><?php echo $order['Betrag']; if($order['Service'] != '0.00') echo " " . number_format($order['Service'], 2, '.', ''); ?></td>
						<?php else: ?>
							<td class="order-betrag"><?php if($order['Service'] != '0.00') echo number_format($order['Service'], 2, '.', ''); else echo '-'; ?></td>
						<?php endif; ?>
                        <td class="order-fahrer"><?php echo $order['Fahrer'] ?></td>
                        <td class="order-sonstiges"><?php echo $order['Sonstiges'] ?></td>
                        <td class="order-service"><?php if($order['Service'] != '0.00') echo number_format($order['Service'], 2, '.', ''); else echo '-'; ?></td>
						<!--<td>

                            <?php if ($tmpOrder->get_meta('mark_as_done') !== 'done') : ?>
                                <form action="/wp-content/plugins/itweb-booking/classes/Helper.php" method="POST">
                                    <input type="hidden" value="<?php echo $order['Nr.'] ?>" name="id">
                                    <input type="hidden" name="task" value="mark_as_done_anreise_item">
                                    <button class="btn btn-sm btn-primary" type="submit">erledigt</button>
                                </form>
                            <?php endif; ?>
                            <form action="/wp-content/plugins/itweb-booking/classes/Helper.php" method="POST" class="m10">
                                <input type="hidden" value="<?php echo $order['Nr.'] ?>" name="id">
                                <input type="hidden" name="task" value="add_one_day">
                                <input type="hidden" name="list" value="Anreisedatum">
                                <button class="btn btn-sm btn-primary" type="submit">vertagen</button>
                            </form>
                        </td>-->
						<td>-</td>
                    </tr>
                <?php $i++; endforeach; ?>
            </tbody>
        </table>
        <?php if (count($allorders) <= 0) : ?>
            <p>Keine Ergebnisse gefunden!</p>
        <?php endif; ?>
    </div>
</div>

<!--abresiseliste valet template-->
<?php
// Get All Orders
$data = array(
//    'status' => 'processing',
    'type' => 'shop_order',
    'email' => '',
    'limit' => -1,
    'return' => 'ids',
);

$date = isset($_GET['date']) ? dateFormat($_GET['date_to']) : date('Y-m-d');

$data['meta_query']['date_clause'] = array(
    'key' => 'Abreisedatum',
    'value' => $date,
    'compare' => '='
);
$allorders = wc_get_orders($data);

$data = [];
foreach ($allorders as $order_id) {
    $order = wc_get_order($order_id);
	$items = $order->get_items();
	foreach ( $items as $item ) {
		$product_id = $item->get_product_id();
	}
	$product = Database::getInstance()->getParklotByProductId($product_id); 
    //$dateTo = dateFormat($order->get_meta('Abreisedatum'), 'de');
    if(getDateDiff($order->get_meta('first_abreisedatum'), $order->get_meta('Abreisedatum'), 'd') > 0){
        $moreDays = getDateDiff($order->get_meta('first_abreisedatum'), $order->get_meta('Abreisedatum'), 'd');
    }
	
	if($order->get_status() == "cancelled"){
		$anreisezeit = date('H:i', strtotime("23:59"));
		$color = "#ff0000";
	}
	else{
		$anreisezeit = date('H:i', strtotime($order->get_meta('Uhrzeit bis')));
		$color= $product->color;
	}
    
	if($order->get_billing_first_name() == null)
		$customor = $order->get_billing_last_name();
	elseif($order->get_billing_last_name() == null)
		$customor = $order->get_billing_first_name();
	elseif(strlen( $order->get_billing_last_name()) < 2)
		$customor =$order->get_billing_first_name();
	elseif(strlen( $order->get_billing_last_name()) > 2)
		$customor = $order->get_billing_last_name();
	else
		$customor = $order->get_billing_last_name();
	
    $data[] = [
        'Nr.' => $order->get_id(),
        'P.-Code.' => $product->parklot_short, // get_field('parkplatz_code', array_values($order->get_items())[0]->get_product_id()),
        'Typ' => $product->type,
		'Buchungscode' => $order->get_meta('token'),
        //'Datum' => $dateTo,
        'Kunde' => $customor,
        'Abreisedatum' => dateFormat($order->get_meta('Abreisedatum'), 'de'),
		'Abreisezeit' => $anreisezeit,
        'Personen' => $order->get_meta('Personenanzahl'),
        'Rückflug' => $order->get_meta('Rückflugnummer'),
        'Parkplatz' => $order->get_meta('Parkplatz'),
        'Fahrer' => $order->get_meta('FahrerAb'),
		'Sonstige 1' => $order->get_meta('Sonstige 1'),
        'Sonstige 2' => $order->get_meta('Sonstige 2'),
        'Betrag' => $order->get_total() . ' ' . $order->get_currency(),
		'Color' => $color
    ];
}

// Obtain a list of columns
foreach ($data as $key => $row) {
    $des[$key]  = $row['Abreisezeit'];
    $name_d[$key] = $row['Kunde'];
}

// Sort the data with volume descending, edition ascending
array_multisort($des, SORT_ASC, $name_d, SORT_ASC, $data);

?>

<div class="page container-fluid d-none abreise-valet-template <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Abreiseliste Valet</h3>
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
						<div class="col-2">
							<a href="#" class="abreise-btn-template btn btn-primary">Abreiseliste Shuttle</a>
						</div>
						<div class="col-2">
							<a href="#" class="anreise-valet-btn-template btn btn-primary">Anreiseliste Valet</a>
						</div>
					</div>
				</div>
			</div>
        </form>
        <table class="dataTable no-footer" id="returnBooking_valet">
            <thead>
                <tr>
                    <?php if (count($data) > 0) : ?>
                        <?php foreach ($data[0] as $key => $value) : ?>
							<?php if($key == "Color" || $key == "Betrag" || $key == "Typ") continue; ?>
                            <th><?php echo $key ?></th>
                        <?php endforeach; ?>
						<th><?php echo "Aktion" ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($data as $order) : ?>
					<?php if($order['Typ'] == "shuttle") continue; ?>
					<? $tmpOrder = wc_get_order($order['Nr.']); ?>
                    <tr class="<?php echo $tmpOrder->get_meta('mark_as_done') == 'done' ? 'mark_done' : '' ?> row<?php echo $i % 2; ?>" export-color="<?php echo $order['Color'] ?>">
                        <input type="hidden" class="order-nr" value="<?php echo $order['Nr.'] ?>">
						<td class="nr" export-color="<?php echo $order['Color'] ?>">
							<?php echo $i ?>
						</td>
                        <td class="order-pcode" style="background-color: <?php echo $order['Color'] ?> !important">
                            <?php echo $order['P.-Code.'] ?>
                        </td>
                        <td class="order-token">
                            <u><a href="<?php echo '/wp-admin/admin.php?page=buchung-bearbeiten&edit=' . $order['Nr.'] . '&ue=1' ?>" target="_blank"><?php echo $order['Buchungscode'] ?></a></u>
                        </td>
                        <!--<td>
                            <?php echo $order['Datum'] ?>
                        </td>-->
                        <td class="order-kunde">
                            <?php echo $order['Kunde'] ?>
                        </td>
                        <td class="order-dateto">
                            <?php if($order['Abreisedatum'] != null && $order['Abreisedatum'] != '01.01.1970') echo $order['Abreisedatum']; else echo ""; ?>
                        </td>
						<td class="order-timeto">
                            <?php if($order['Abreisezeit'] != null && $order['Abreisedatum'] != '01.01.1970') echo $order['Abreisezeit']; else echo ""; ?>
                        </td>
                        <td class="order-persons">
                           <?php echo $order['Personen'] ? $order['Personen'] : "" ?>
                        </td>
                        <td class="order-ruckflug">
                           <?php echo $order['Rückflug'] ? $order['Rückflug'] : "" ?>
                        </td>
                        <td class="order-parkplatz">
                            <?php echo $order['Parkplatz'] ? $order['Parkplatz'] : "" ?>
                        </td>
						<td class="order-fahrer"><?php echo $order['Fahrer'] ?></td>
                        <td class="order-sonstige1"><?php echo $order['Sonstige 1'] ?></td>
                        <td class="order-sonstige2"><?php echo $order['Sonstige 2'] ?></td>
                        <!--<td>
                            <?php if ($tmpOrder->get_meta('mark_as_done') !== 'done') : ?>
                                <form action="/wp-content/plugins/itweb-booking/classes/Helper.php" method="POST">
                                    <input type="hidden" value="<?php echo $order['Nr.'] ?>" name="id">
                                    <input type="hidden" name="task" value="mark_as_done_anreise_item">
                                    <button class="btn btn-sm btn-primary" type="submit">erledigt</button>
                                </form>
                            <?php endif; ?>
                            <form action="/wp-content/plugins/itweb-booking/classes/Helper.php" method="POST" class="m10">
                                <input type="hidden" value="<?php echo $order['Nr.'] ?>" name="id">
                                <input type="hidden" name="task" value="add_one_day">
                                <input type="hidden" name="list" value="Abreisedatum">
                                <button class="btn btn-sm btn-primary" type="submit">vertagen</button>
                            </form>
                        </td>-->
						<td>-</td>
                    </tr>
                <?php $i++; endforeach; ?>
            </tbody>
        </table>
        <?php if (count($allorders) <= 0) : ?>
            <p>Keine Ergebnisse gefunden!</p>
        <?php endif; ?>
    </div>
</div>