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

$date = isset($_GET['date_from']) ? dateFormat($_GET['date_from']) : date('Y-m-d');
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
    $dateFrom = dateFormat($order->get_meta('Anreisedatum'), 'de');
    if(getDateDiff($order->get_meta('first_anreisedatum'), $order->get_meta('Anreisedatum'), 'd') > 0){
        $dateFrom .= ' (+'.getDateDiff($order->get_meta('first_anreisedatum'), $order->get_meta('Anreisedatum'), 'd') . ')';
    }
    
    $data[] = [
        'Nr.' => $order->get_id(),
        'P.-Code.' => $product->prefix, //  get_field('parkplatz_code', array_values($order->get_items())[0]->get_product_id()),
        'Buchungscode' => $order->get_meta('token'),
        'Datum' => $dateFrom,
        'Kunde' => '<span class="fname">' . $order->get_billing_first_name() . '</span> <span class="lname">' . $order->get_billing_last_name() . '</span>',
        'Anreisezeit' => $order->get_meta('Uhrzeit von'),
        'Personen' => $order->get_meta('Personenanzahl'),
        'Parkplatz' => count($order->get_items()) > 0 ? array_values($order->get_items())[0]->get_name() : '',
        'Abreisedatum' => dateFormat($order->get_meta('Abreisedatum'), 'de'),
        'Rückflug Nr.' => $order->get_meta('Rückflugnummer'),
        'Landung' => $order->get_meta('Uhrzeit bis'),
        'Betrag' => $order->get_total() . ' ' . $order->get_currency(),
        'Fahrer' => $order->get_meta('Fahrer'),
        'Sonstiges' => $order->get_meta('Sonstiges'),
        'Kunde gefahren' => '',
		'Color' => $product->color
    ];
}
?>

<div class="container-fluid m10">
    <div class="row">
        <div class="col-1">
            <a href="#" class="anreise-btn-template btn btn-primary">Anreise</a>
        </div>
        <div class="col-1">
            <a href="#" class="abreise-btn-template btn btn-primary">Abreise</a>
        </div>
    </div>
</div>
<div class="page container-fluid anreise-template <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Anreiseliste</h3>
    </div>
    <div class="page-body">
        <form class="form-filter m10">
            <div class="row">
                <div class="col-12 col-md-1">
                    <input type="text" placeholder="Datum" name="date_from"
                           value="<?php echo $_GET['date_from'] ?>" class="single-datepicker form-control form-item">
                </div>
                <div class="col-12 col-md-1">
                    <button class="btn btn-primary d-block w-100" type="submit">Filter</button>
                </div>
            </div>
        </form>
        <table class="" id="arrivalBooking">
            <thead>
                <tr>
                    <?php if (count($data) > 0) : ?>
                        <?php foreach ($data[0] as $key => $value) : ?>
							<?php if($key == "Color") continue; ?>
                            <th><?php echo $key ?></th>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $order) : $tmpOrder = wc_get_order($order['Nr.']); ?>
                    <tr class="<?php echo $tmpOrder->get_meta('mark_as_done') == 'done' ? 'mark_done' : '' ?> row<?php echo $i % 2; ?>" export-color="<?php echo $order['Color'] ?>">
                        <td class="order-nr" export-color="<?php echo $order['Color'] ?>">
                            <?php echo $order['Nr.'] ?>
                        </td>
                        <td class="order-pcode" style="background-color: <?php echo $order['Color'] ?> !important">
                            <?php echo $order['P.-Code.'] ?>
                        </td>
                        <td class="order-token">
                            <?php echo $order['Buchungscode'] ?>
                        </td>
                        <td>
                            <?php echo $order['Datum'] ?>
                        </td>
                        <td class="order-kunde">
                            <?php echo $order['Kunde'] ?>
                        </td>
                        <td class="order-timefrom">
                            <?php echo $order['Anreisezeit'] ?>
                        </td>
                        <td class="order-persons">
                            <?php echo $order['Personen'] ?>
                        </td>
                        <td class="order-parkplatz">
                            <?php echo $order['Parkplatz'] ?>
                        </td>
                        <td class="order-dateto">
                            <?php echo $order['Abreisedatum'] ?>
                        </td>
                        <td class="order-ruckflug">
                            <?php echo $order['Rückflug Nr.'] ?>
                        </td>
                        <td class="order-landung"><?php echo $order['Landung'] ?></td>
                        <td class="order-betrag"><?php echo $order['Betrag'] ?></td>
                        <td class="order-fahrer"><?php echo $order['Fahrer'] ?></td>
                        <td class="order-sonstiges"><?php echo $order['Sonstiges'] ?></td>
                        <td>

                            <?php if ($tmpOrder->get_meta('mark_as_done') !== 'done') : ?>
                                <form action="/wp-content/plugins/itweb-booking/classes/Helper.php" method="POST">
                                    <input type="hidden" value="<?php echo $order['Nr.'] ?>" name="id">
                                    <input type="hidden" name="task" value="mark_as_done_anreise_item">
                                    <button class="btn btn-sm btn-primary" type="submit">erl.</button>
                                </form>
                            <?php endif; ?>
                            <form action="/wp-content/plugins/itweb-booking/classes/Helper.php" method="POST" class="m10">
                                <input type="hidden" value="<?php echo $order['Nr.'] ?>" name="id">
                                <input type="hidden" name="task" value="add_one_day">
                                <input type="hidden" name="list" value="Anreisedatum">
                                <button class="btn btn-sm btn-primary" type="submit">vertagen</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
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

$date = isset($_GET['date_to']) ? dateFormat($_GET['date_to']) : date('Y-m-d');

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
    $dateTo = dateFormat($order->get_meta('Abreisedatum'), 'de');
    if(getDateDiff($order->get_meta('first_abreisedatum'), $order->get_meta('Abreisedatum'), 'd') > 0){
        $dateTo .= ' (+'.getDateDiff($order->get_meta('first_abreisedatum'), $order->get_meta('Abreisedatum'), 'd') . ')';
    }
    
    $data[] = [
        'Nr.' => $order->get_id(),
        'P.-Code.' => $product->prefix, //  get_field('parkplatz_code', array_values($order->get_items())[0]->get_product_id()),
        'Buchungscode' => $order->get_meta('token'),
        'Datum' => $dateTo,
        'Kunde' => '<span class="fname">' . $order->get_billing_first_name() . '</span> <span class="lname">' . $order->get_billing_last_name() . '</span>',
        'Abreisezeit' => $order->get_meta('Uhrzeit bis'),
        'Personen' => $order->get_meta('Personenanzahl'),
        'Rückflug Nr.' => $order->get_meta('Rückflugnummer'),
        'Parkplatz' => count($order->get_items()) > 0 ? array_values($order->get_items())[0]->get_name() : '',
        'Sonstige 1' => $order->get_meta('Sonstige 1'),
        'Sonstige 2' => $order->get_meta('Sonstige 2'),
        'Kunde gefahren' => '',
		'Color' => $product->color
    ];
}

if (isset($_POST['export'])) {
    array_csv_download('Abreiseliste', dateFormat($date, 'de'), $data, 'abreiseliste-' . dateFormat($date, 'de') . '.csv');
    header('Location: /wp-admin/admin.php?page=anreiseliste');
}
?>

<div class="page container-fluid d-none abreise-template <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Abreiseliste</h3>
    </div>
    <div class="page-body">
        <form class="form-filter m10">
            <div class="row">
                <div class="col-12 col-md-1">
                    <input placeholder="Datum" type="text" name="date_to"
                           value="<?php echo $_GET['date_to'] ?>" class="single-datepicker form-control form-item">
                </div>
                <div class="col-12 col-md-1">
                    <button class="btn btn-primary d-block w-100" type="submit">Filter</button>
                </div>
            </div>
        </form>
        <table class="" id="returnBooking">
            <thead>
                <tr>
                    <?php if (count($data) > 0) : ?>
                        <?php foreach ($data[0] as $key => $value) : ?>
							<?php if($key == "Color") continue; ?>
                            <th><?php echo $key ?></th>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $order) : $tmpOrder = wc_get_order($order['Nr.']); ?>
                    <tr class="<?php echo $tmpOrder->get_meta('mark_as_done') == 'done' ? 'mark_done' : '' ?> row<?php echo $i % 2; ?>" export-color="<?php echo $order['Color'] ?>">
                        <td class="order-nr" export-color="<?php echo $order['Color'] ?>">
                            <?php echo $order['Nr.'] ?>
                        </td>
                        <td class="order-pcode" style="background-color: <?php echo $order['Color'] ?> !important">
                            <?php echo $order['P.-Code.'] ?>
                        </td>
                        <td class="order-token">
                            <?php echo $order['Buchungscode'] ?>
                        </td>
                        <td>
                            <?php echo $order['Datum'] ?>
                        </td>
                        <td class="order-kunde">
                            <?php echo $order['Kunde'] ?>
                        </td>
                        <td class="order-timeto">
                            <?php echo $order['Abreisezeit'] ?>
                        </td>
                        <td class="order-persons">
                            <?php echo $order['Personen'] ?>
                        </td>
                        <td class="order-ruckflug">
                            <?php echo $order['Rückflug Nr.'] ?>
                        </td>
                        <td class="order-parkplatz">
                            <?php echo $order['Parkplatz'] ?>
                        </td>
                        <td class="order-sonstige1"><?php echo $order['Sonstige 1'] ?></td>
                        <td class="order-sonstige2"><?php echo $order['Sonstige 2'] ?></td>
                        <td>
                            <?php if ($tmpOrder->get_meta('mark_as_done') !== 'done') : ?>
                                <form action="/wp-content/plugins/itweb-booking/classes/Helper.php" method="POST">
                                    <input type="hidden" value="<?php echo $order['Nr.'] ?>" name="id">
                                    <input type="hidden" name="task" value="mark_as_done_anreise_item">
                                    <button class="btn btn-sm btn-primary" type="submit">erl.</button>
                                </form>
                            <?php endif; ?>
                            <form action="/wp-content/plugins/itweb-booking/classes/Helper.php" method="POST" class="m10">
                                <input type="hidden" value="<?php echo $order['Nr.'] ?>" name="id">
                                <input type="hidden" name="task" value="add_one_day">
                                <input type="hidden" name="list" value="Abreisedatum">
                                <button class="btn btn-sm btn-primary" type="submit">vertagen</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (count($allorders) <= 0) : ?>
            <p>Keine Ergebnisse gefunden!</p>
        <?php endif; ?>
    </div>
</div>
