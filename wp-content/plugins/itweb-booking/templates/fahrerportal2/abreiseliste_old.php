<?php
//$products = Database::getInstance()->getProducts();
// Get All Orders
$filter['list'] = 1;

$dateFrom = isok($_GET, 'dateFrom') ? dateFormat($_GET['dateFrom']) : date('Y-m-d');
$dateTo = isok($_GET, 'dateTo') ? dateFormat($_GET['dateTo']) : date('Y-m-d');
$filter['datum_von_Ad'] = $dateFrom;
$filter['datum_bis_Ad'] = $dateTo;
$filter['type'] = "shuttle";

$allorders = Database::getInstance()->getBookings($filter);

$data = [];
foreach($allorders as $booking) {
	
	// filter by product id
	if(isset($_GET['product']) && !empty($_GET['product'])){
		if($_GET['product'] == phg && ($booking->Produkt != 537 && $booking->Produkt != 595 && $booking->Produkt != 621 && $booking->Produkt != 683 && $booking->Produkt != 592 && $booking->Produkt != 3080 && $booking->Produkt != 624 && 
				$booking->Produkt != 41453 && $booking->Produkt != 41577 && $booking->Produkt != 41466 && $booking->Produkt != 41581)){
			continue;
		}
		elseif($_GET['product'] == ph && ($booking->Produkt != 537 && $booking->Produkt != 595 && $booking->Produkt != 621 && $booking->Produkt != 683 && $booking->Produkt != 41453 && $booking->Produkt != 41577)){
			continue;
		}
		elseif($_GET['product'] == pho && ($booking->Produkt != 592 && $booking->Produkt != 3080 && $booking->Produkt != 624 && $booking->Produkt != 41466 && $booking->Produkt != 41581)){
			continue;
		}
		elseif($_GET['product'] == ppb && ($booking->Produkt != 619 && $booking->Produkt != 3081 && $booking->Produkt != 24609 && $booking->Produkt != 41468 && $booking->Produkt != 41584)){
			continue;
		}
		elseif($_GET['product'] == pps && ($booking->Produkt != 873 && $booking->Produkt != 3082 && $booking->Produkt != 901 && $booking->Produkt != 41470 && $booking->Produkt != 41582 && $booking->Produkt != 45856)){
			continue;
		}
		elseif($_GET['product'] == ost && ($booking->Produkt != 24222 && $booking->Produkt != 24224 && $booking->Produkt != 24261 && $booking->Produkt != 24226 && $booking->Produkt != 24228 && $booking->Produkt != 24263 && 
				$booking->Produkt != 41472 && $booking->Produkt != 41580 && $booking->Produkt != 41474 && $booking->Produkt != 41585 && $booking->Produkt != 41402 && $booking->Produkt != 41403)){
			continue;
		}
		elseif($_GET['product'] == ostph && ($booking->Produkt != 24222 && $booking->Produkt != 24224 && $booking->Produkt != 24261 && $booking->Produkt != 41472 && $booking->Produkt != 41580 && $booking->Produkt != 41402)){
			continue;
		}
		elseif($_GET['product'] == ostnu && ($booking->Produkt != 24226 && $booking->Produkt != 24228 && $booking->Produkt != 24263 && $booking->Produkt != 41474 && $booking->Produkt != 41585 && $booking->Produkt != 41403)){
			continue;
		}
	}
	
	if($booking->Status == "wc-cancelled"){
		$abreisezeit = date('H:i', strtotime("23:59"));
		$color = "#ff0000";
	}
	else{
		$abreisezeit = date('H:i', strtotime($booking->Uhrzeit_bis));
		$color= $booking->Color;
	}
	
	if($booking->Vorname == null)
		$customor = $booking->Nachname;
	elseif($booking->Nachname == null)
		$customor = $booking->Vorname;
	elseif(strlen($booking->Nachname) < 2)
		$customor = $booking->Vorname;
	elseif(strlen($booking->Nachname) > 2)
		$customor = $booking->Nachname;
	else
		$customor = $booking->Nachname;
	
	$additionalPrice = "0.00";
	$services = Database::getInstance()->getBookingMetaAsResults($booking->order_id, 'additional_services');
	if(count($services) > 0){
		foreach($services as $v){
			$s = Database::getInstance()->getAdditionalService($v->meta_value);
			$additionalPrice += $s->price;
		}
	}
		
    $data[] = [
        'Nr.' => $booking->order_id,
        'PCode.' => $booking->Code, // get_field('parkplatz_code', array_values($order->get_items())[0]->get_product_id()),
        'Buchung' => $booking->Token,
        'Kunde' => '</span> <span class="lname">' . $customor . '</span>',
        'Abreisedatum' => dateFormat($booking->Abreisedatum, 'de'),
		'Abreisezeit' => $abreisezeit,
        'Personen' => $booking->Personenanzahl,
        'Rückflug' => $booking->Rückflugnummer,
        'Parkplatz' => $booking->Parkplatz,
        'Fahrer' => $booking->FahrerAb,
		'Sonstige 1' => $booking->Sonstige_1,
        'Sonstige 2' => $booking->Sonstige_2,		
        'Betrag' => $booking->Betrag,// . ' ' . $order->get_currency(),
		'Service' => $additionalPrice,
		'Status' => $booking->Status,
        'Bearbeitet' => $booking->editByRet,
		'Aktion' => '',
		'Color' => $color,
		'is_for' => $booking->is_for
    ];
}

// Obtain a list of columns
foreach ($data as $key => $row) {
	$bDate[$key] = $row['Abreisedatum'];
    $ret[$key]  = $row['Abreisezeit'];
    $name[$key] = $row['Kunde'];
}

// Sort the data with volume descending, edition ascending
array_multisort($bDate, SORT_ASC, $ret, SORT_ASC, $name, SORT_ASC, $data);

$user = wp_get_current_user();
if($user->user_login == 'aras' || $user->user_login == 'cakir' || $user->user_login == 'sergej')
	$editOK = "";
else
	$editOK = "readonly";

?>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Abreiseliste Shuttle</h3>
    </div>
    <div class="page-body">
        <form class="form-filter">
            <div class="row ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Nach Datum filtern</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-md-1 ui-lotdata-date">
							<input type="text" id="dateFrom" name="dateFrom" placeholder="Datum von" class="form-item form-control single-datepicker"
								   value="<?php echo dateFormat($dateFrom, 'de') ?>">
						</div>
						<div class="col-sm-12 col-md-1 ui-lotdata-date">
							<input type="text" id="dateTo" name="dateTo" placeholder="Datum bis" class="form-item form-control single-datepicker"
								   value="<?php echo dateFormat($dateTo, 'de') ?>">
						</div>
						<div class="col-sm-12 col-md-2">
							<select name="product" class="form-item form-control">
								<option value="">Standort</option>
								<option value="phg" <?php echo (isset($_GET['product']) && $_GET['product'] == 'phg') ? ' selected' : '' ?>>
									<?php echo 'PH/O' ?>
								</option>
								<option value="ph" <?php echo (isset($_GET['product']) && $_GET['product'] == 'ph') ? ' selected' : '' ?>>
									<?php echo ' - PH' ?>
								</option>
								<option value="pho" <?php echo (isset($_GET['product']) && $_GET['product'] == 'pho') ? ' selected' : '' ?>>
									<?php echo ' - PHO' ?>
								</option>
								<option value="ppb" <?php echo (isset($_GET['product']) && $_GET['product'] == 'ppb') ? ' selected' : '' ?>>
									<?php echo 'PPB' ?>
								</option>
								<option value="pps" <?php echo (isset($_GET['product']) && $_GET['product'] == 'pps') ? ' selected' : '' ?>>
									<?php echo 'PPS' ?>
								</option>
								<option value="ost" <?php echo (isset($_GET['product']) && $_GET['product'] == 'ost') ? ' selected' : '' ?>>
									<?php echo 'OST' ?>
								</option>
								<option value="ostph" <?php echo (isset($_GET['product']) && $_GET['product'] == 'ostph') ? ' selected' : '' ?>>
									<?php echo ' - OSTPH' ?>
								</option>
								<option value="ostnu" <?php echo (isset($_GET['product']) && $_GET['product'] == 'ostnu') ? ' selected' : '' ?>>
									<?php echo ' - OSTNÜ' ?>
								</option>
							</select>
						</div>
						<div class="col-sm-12 col-md-1">
							<button class="btn btn-primary d-block w-100" type="submit">Filter</button>
						</div>
						<div class="col-sm-12 col-md-2">                    
							<a href="<?php echo '/wp-admin/admin.php?page=abreiseliste' ?>" class="btn btn-secondary d-block w-100" >Zurück setzen</a>
						</div>
					</div><br>
					<div class="row">
						<div class="col-sm-12 col-md-3 col-lg-2">                    
							<a href="<?php echo '/wp-admin/admin.php?page=anreiseliste' ?>" class="btn btn-primary d-block w-100" >Anreiseliste Shuttle</a>
						</div>
						<!--
						<div class="col-sm-12 col-md-3 col-lg-2">                    
							<a href="<?php echo '/wp-admin/admin.php?page=anreiseliste-valet' ?>" class="btn btn-primary d-block w-100" >Anreiseliste Valet</a>
						</div>
						<div class="col-sm-12 col-md-3 col-lg-2">                    
							<a href="<?php echo '/wp-admin/admin.php?page=abreiseliste-valet' ?>" class="btn btn-primary d-block w-100" >Abreiseliste Valet</a>
						</div>
						-->
					</div>
				</div>
            </div>
        </form>
        <table class="table-responsive" id="returnBooking">
            <thead>
            <tr>
                <?php if(count($data) > 0) : ?>
                <?php foreach ($data[0] as $key => $value) : ?>
					<?php if($key == "Color" || $key == "is_for") continue; ?>
                    <th><?php echo $key ?></th>
                <?php endforeach; ?>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php $i = 1; foreach ($data as $order) : ?>
                <tr style="background-color: <?php echo $order['Color'] ?> !important" export-color="<?php echo $order['Color'] ?>" class="row<?php echo $i % 2; ?>">
                    <input type="hidden" class="order-nr" value="<?php echo $order['Nr.'] ?>">
					<td class="order-nr" export-color="<?php echo $order['Color'] ?>">
                        <?php echo $i ?>
                    </td>
                    <td class="order-pcode">
                        <?php echo $order['PCode.'] ?>
                    </td>
                    <td class="order-token">
                        <?php echo $order['Buchung'] ?>
                    </td>
                    <td class="order-kunde">
                        <input type="text" style="width:150px;" value="<?php echo trim(strip_tags($order['Kunde'])) ?>" class="transparent-input"><span style="display: none;"><?php echo trim(strip_tags($order['Kunde'])) ?></span>
                    </td>
					<td class="order-dateto">
                        <input type="text" style="width:115px;" value="<?php echo $order['Abreisedatum'] ?>" class="transparent-input single-datepicker" readonly><span style="display: none;"><?php echo $order['Abreisedatum'] ?></span>
                    </td>
                    <td class="order-timeto">
                        <input type="time" style="width:100px;" value="<?php echo $order['Abreisezeit'] ?>" class="transparent-input" placeholder="00:00"><span style="display: none;"><?php echo $order['Abreisezeit'] ?></span>
                    </td>
                    <td class="order-persons">
                        <input type="text" style="width:70px;" value="<?php echo $order['Personen'] ?>" class="transparent-input"><span style="display: none;"><?php echo $order['Personen'] ?></span>
                    </td>
                    <td class="order-ruckflug">
                        <input type="text" style="width:100px;" value="<?php echo $order['Rückflug'] ?>" class="transparent-input"><span style="display: none;"><?php echo $order['Rückflug'] ?></span>
                    </td>
                    <td class="order-parkplatz">
                        <input type="text" style="width:90px;" value="<?php echo $order['Parkplatz'] ?>" class="transparent-input"><span style="display: none;"><?php echo $order['Parkplatz'] ?></span>
                    </td>
					<td class="order-fahrer">
                        <input type="text" style="width:150px;" value="<?php echo $order['Fahrer'] ?>" class="transparent-input"><span style="display: none;"><?php echo $order['Fahrer'] ?></span>
                    </td>
                    <td class="order-sonstige1">
                        <input type="text" style="width:150px;" value="<?php echo $order['Sonstige 1'] ?>" class="transparent-input"><span style="display: none;"><?php echo $order['Sonstige 1'] ?></span>
                    </td>
                    <td class="order-sonstige2">
                        <input type="text" style="width:150px;" value="<?php echo $order['Sonstige 2'] ?>" class="transparent-input" readonly><span style="display: none;"><?php echo $order['Sonstige 2'] ?></span>
                    </td>
                    <td class="order-betrag" style="position:relative;">
                        <?php
                        //$betrag = explode(' ', $order['Betrag']);
                        //$price = $betrag[0];
                        //$currency = $betrag[1];
                        ?>
                        <?php if(get_post_meta($order['Nr.'], '_transaction_id')[0] == "barzahlung"): ?>
							<input type="text" <?php echo $editOK; ?> style="width:100px; background: rgba(255, 255, 255, 0.5) !important" value="<?php echo $order['Betrag']; if($order['Service'] != '0.00') echo " " . number_format($order['Service'], 2, '.', ''); ?>" class="form-control transparent-input"><span style="display: none;"><?php echo $order['Betrag'] ?></span> 
						<?php else: ?>
							<input type="text" <?php echo $editOK; ?> style="width:100px; background: rgba(255, 255, 255, 0.5) !important" value="-" class="form-control transparent-input"><span style="display: none;"><?php if($order['Service'] != '0.00') echo number_format($order['Service'], 2, '.', ''); else echo '-'; ?></span>
						<?php endif; ?>
                    </td>
					<td class="order-service">
                        <input type="text" style="width:100px;" value="<?php if($order['Service'] != '0.00') echo number_format($order['Service'], 2, '.', ''); else echo '-'; ?>" class="transparent-input" placeholder="0.00" readonly>
                    </td>
					<td class="order-status">
                        <select class="transparent-input">
                            <option value=""></option>
                            <?php foreach (wc_get_order_statuses() as $key => $value) : ?>
                                <?php 
									if($key == 'wc-processing' || 
										$key == 'wc-cancelled' ||
										$key == 'wc-refunded' ||
										$key == 'wc-pending'):
								?>
								<option value="<?php echo $key ?>" <?php echo $key == $order['Status'] ? 'selected' : '' ?>>
                                    <?php 
										if($key == 'wc-processing') echo "abgeschlossen";
										elseif($key == 'wc-cancelled') echo "storniert";
										elseif($key == 'wc-refunded') echo "erstattet";
										elseif($key == 'wc-pending') echo "nicht bezahlt";
									?>
                                </option>
									<?php else: continue; endif; ?>
                            <?php endforeach; ?>
                        </select>
<!--                        --><?php //if($order['Status'] == 'processing' && get_post_meta($order['Nr.'], '_transaction_id', true) != "")
//								echo "bezahlt";
//							  elseif($order['Status'] == 'processing' && get_post_meta($order['Nr.'], '_transaction_id', true) == "")
//								echo "Barzahlung";
//							  elseif($order['Status'] == 'cancelled')
//								echo "storniert";
//					    ?>
                    </td>
					<?php if($user->user_login == 'aras' || $user->user_login == 'cakir' || $user->user_login == 'sergej'): ?>
						<td class="order-edit">
							<input type="text" readonly style="width:150px;" value="<?php echo $order['Bearbeitet'] ?>" class="transparent-input">
						</td>
					<?php else: ?>
						<td class="order-edit">
							<input type="text" readonly style="width:150px;" value="-" class="transparent-input">
						</td>
					<?php endif; ?>
                    <?php if($order['is_for'] != 'hotel'): ?>
					<td>
<!--                        <a href="#" class="abreiseliste-modal" data-order="--><?php //echo $order['Nr.'] ?><!--"-->
<!--                           data-target="#editListModal">-->
<!--                            Edit-->
<!--                        </a>-->
                        <a href="#" class="save-abreiseliste-row">
                            Speichern
                        </a>
                    </td>
					<?php else:  ?>
					 <td>
                        <p class="">-</p>
                    </td>
					<?php endif; ?>
                </tr>
            <?php $i++; endforeach; ?>
            </tbody>
        </table>
        <?php if (count($allorders) <= 0) : ?>
            <p>Keine Ergebnisse gefunden!</p>
        <?php endif; ?>
    </div>
</div>
