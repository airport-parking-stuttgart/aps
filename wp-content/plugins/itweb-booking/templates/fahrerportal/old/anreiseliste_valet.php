<?php
//$products = Database::getInstance()->getProducts();
// Get All Orders

$filter['list'] = 1;

$dateFrom = isok($_GET, 'dateFrom') ? dateFormat($_GET['dateFrom']) : date('Y-m-d');
$dateTo = isok($_GET, 'dateTo') ? dateFormat($_GET['dateTo']) : date('Y-m-d');
$filter['datum_von'] = $dateFrom;
$filter['datum_bis'] = $dateTo;
$filter['type'] = "valet";

$allorders = Database::getInstance()->getBookings($filter);

$data = [];
foreach ($allorders as $booking) {
	if($booking->Status == "wc-cancelled"){
		$anreisezeit = date('H:i', strtotime("23:59"));
		$color = "#ff0000";
	}
	else{
		$anreisezeit = date('H:i', strtotime($booking->Uhrzeit_von));
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
        'PCode.' => $booking->Code, //get_field('prefix', array_values($order->get_items())[0]->get_product_id()),
        'Buchung' => $booking->Token,
        'Kunde' => '</span> <span class="lname">' . $customor . '</span>',
        'Anreisedatum' => dateFormat($booking->Anreisedatum, 'de'),
		'Anreisezeit' => $anreisezeit,
        //'Personen' => $booking->Personenanzahl,
        'Parkplatz' => $booking->Parkplatz,
        'Abreisedatum' => $booking->AbreisedatumEdit,
		'AbreisedatumPH' => $booking->Abreisedatum,
        'Rückflugnummer' => $booking->RückflugnummerEdit,
        'Landung' => $booking->Uhrzeit_bis_Edit,
        'Betrag' => $booking->Betrag,// . ' ' . $order->get_currency(),
        'Fahrer' => $booking->FahrerAn,
        'Sonstiges' => $booking->Sonstige_1,
		'Service' => $additionalPrice,
		'Auto' => $booking->Fahrzeughersteller,
		'Kennzeichen' => $booking->Kennzeichen,
        //'Status' => $booking->Status,
		'Bearbeitet' => $booking->editByArr,
        'Aktion' => '',
		'Color' => $color
    ];
}

// Obtain a list of columns
foreach ($data as $key => $row) {
    $bDate[$key] = $row['Anreisedatum'];
	$arr[$key]  = $row['Anreisezeit'];
    $name[$key] = $row['Kunde'];
}

// Sort the data with volume descending, edition ascending
array_multisort($bDate, SORT_ASC, $arr, SORT_ASC, $name, SORT_ASC, $data);

$user = wp_get_current_user();

if($user->user_login == 'aras' || $user->user_login == 'cakir' || $user->user_login == 'sergej')
	$editOK = "";
else
	$editOK = "readonly";


//echo "<pre>"; print_r($allorders); echo "</pre>";
?>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Anreiseliste Valet</h3>
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
						<div class="col-sm-12 col-md-1">
							<button class="btn btn-primary d-block w-100" type="submit">Filter</button>					
						</div>
						<div class="col-sm-12 col-md-2">                    
							<a href="<?php echo '/wp-admin/admin.php?page=anreiseliste' ?>" class="btn btn-secondary d-block w-100" >Zurücksetzen</a>
						</div>
					</div><br>
					<div class="row">
						<div class="col-sm-12 col-md-3 col-lg-2">                    
							<a href="<?php echo '/wp-admin/admin.php?page=anreiseliste' ?>" class="btn btn-primary d-block w-100" >Anreiseliste Shuttle</a>
						</div>
						<div class="col-sm-12 col-md-3 col-lg-2">                    
							<a href="<?php echo '/wp-admin/admin.php?page=abreiseliste' ?>" class="btn btn-primary d-block w-100" >Abreiseliste Shuttle</a>
						</div>
						<div class="col-sm-12 col-md-3 col-lg-2">                    
							<a href="<?php echo '/wp-admin/admin.php?page=abreiseliste-valet' ?>" class="btn btn-primary d-block w-100" >Abreiseliste Valet</a>
						</div>
					</div>
				</div>
            </div>
        </form>
        <table class="table-responsive" id="arrivalBooking_valet">
            <thead>
            <tr>
                <?php if (count($data) > 0) : ?>
                    <?php foreach ($data[0] as $key => $value) : ?>
                        <?php if($key == "Color" || $key == "AbreisedatumPH") continue; ?>
						<?php //if(($user->user_login != 'aras' || $user->user_login != 'cakir' || $user->user_login != 'sergej') && $key == "Bearbeitet") continue; ?>
						<th><?php echo $key ?></th>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php $i = 1; foreach ($data as $order) : ?>
			
                <tr style="background-color: <?php echo $order['Color'] ?> !important" export-color="<?php echo $order['Color'] ?>" class="row<?php echo $i % 2; ?>">
                    <input type="hidden" class="order-nr" value="<?php echo $order['Nr.'] ?>">
					<td class="nr" export-color="<?php echo $order['Color'] ?>">
                        <?php echo $i ?>
                    </td>
                    <td class="order-pcode">
                        <?php echo $order['PCode.']; ?>
                    </td>
                    <td class="order-token">
                        <u><a href= "<?php echo '/wp-admin/admin.php?page=buchung-bearbeiten&edit=' . $order['Nr.'] . '&anv=1&an=1'?>"><?php echo $order['Buchung'] ?></a></u>
                    </td>
                    <td class="order-kunde">
                        <input type="text" style="width:150px;" value="<?php echo trim(strip_tags($order['Kunde'])) ?>" class="transparent-input"><span style="display: none;"><?php echo trim(strip_tags($order['Kunde'])) ?></span>
                    </td>
					<td class="order-datefrom">
                        <input type="text" style="width:115px;" class="transparent-input single-datepicker" value="<?php echo $order['Anreisedatum'] ?>" readonly><span style="display: none;"><?php echo $order['Anreisedatum'] ?></span>
                    </td>
                    <td class="order-timefrom">
                        <input type="time" style="width:100px;" value="<?php echo $order['Anreisezeit'] ?>" class="transparent-input" placeholder="00:00"><span style="display: none;"><?php echo $order['Anreisezeit'] ?></span>
                    </td>
                    <!--<td class="order-persons">
                        <input type="text" style="width:70px;" value="<?php echo $order['Personen'] ?>" class="transparent-input"><span style="display: none;"><?php echo $order['Personen'] ?></span>
                    </td>-->
                    <td class="order-parkplatz">
                        <input type="text" style="width:90px;" value="<?php echo $order['Parkplatz'] ?>" class="transparent-input"><span style="display: none;"><?php echo $order['Parkplatz'] ?></span>
                    </td>
                    <td class="order-dateto" style="">
                        <input type="text" style="width:115px; <?php if((int)$order['Anreisezeit'] >= 0 && (int)$order['Anreisezeit'] <= 2) echo 'background-color: rgba(255, 119, 51, 0.5) !important;'; ?>" value="<?php if($order['Abreisedatum'] != "") echo date('d.m.Y', strtotime($order['Abreisedatum'])); else echo ""; ?>" placeholder="<?php if($order['AbreisedatumPH'] != null) echo date('d.m.Y', strtotime($order['AbreisedatumPH'])); else echo ""; ?>" class="transparent-input single-datepicker" readonly><span style="display: none;"><?php //if($order['Abreisedatum'] != "") echo date('d.m.Y', strtotime($order['Abreisedatum'])); else echo ""; ?></span>
                    </td>
                    <td class="order-ruckflug">
                        <input type="text" style="width:100px;" value="<?php if($order['Rückflugnummer'] != "") echo $order['Rückflugnummer']; else echo ""; ?>" class="transparent-input"><span style="display: none;"><?php if($order['Rückflugnummer'] != "") echo $order['Rückflugnummer']; else echo ""; ?></span>
                    </td>
                    <td class="order-landung">
                        <input type="time" style="width:100px;" value="<?php if($order['Landung'] != "") echo $order['Landung']; else echo ""; ?>" class="transparent-input" placeholder="00:00"><span style="display: none;"><?php //if($order['Landung'] != "") echo $order['Landung']; else echo ""; ?></span>
                    </td>
                    <td class="order-betrag" style="position: relative;">
                        <?php
                            //$betrag = explode(' ', $order['Betrag']);
                            //$price = $betrag[0];
                            //$currency = $betrag[1];
                        ?>
						<?php if(get_post_meta($order['Nr.'], '_transaction_id')[0] == "barzahlung"): ?>
							<input type="text" <?php echo $editOK; ?> style="width:100px; background: rgba(255, 255, 255, 0.5) !important" value="<?php echo $order['Betrag'] ?>" class="form-control transparent-input"><span style="display: none;"><?php echo $order['Betrag']; if($order['Service'] != '0.00') echo " " . number_format($order['Service'], 2, '.', ''); ?></span> 
						<?php else: ?>
							<input type="text" <?php echo $editOK; ?> style="width:100px; background: rgba(255, 255, 255, 0.5) !important" value="-" class="form-control transparent-input"><span style="display: none;"><?php if($order['Service'] != '0.00') echo number_format($order['Service'], 2, '.', ''); else echo '-'; ?></span>
						<?php endif; ?>
					</td>				
                    <td class="order-fahrer">
                        <input type="text" style="width:150px;" value="<?php echo $order['Fahrer'] ?>" class="transparent-input"><span style="display: none;"><?php echo $order['Fahrer'] ?></span>
                    </td>
                    <td class="order-sonstiges">
                        <input type="text" style="width:150px;" value="<?php echo $order['Sonstiges'] ?>" class="transparent-input"><span style="display: none;"><?php echo $order['Sonstiges'] ?></span>
                    </td>
					<td class="order-service">
                        <input type="text" style="width:100px;" value="<?php if($order['Service'] != '0.00') echo number_format($order['Service'], 2, '.', ''); else echo '-'; ?>" class="transparent-input" placeholder="0.00" readonly>
                    </td>
					<td class="order-model">
                        <input type="text" style="width:100px;" value="<?php echo $order['Auto']; ?>" class="transparent-input" placeholder="" readonly><span style="display: none;"><?php echo $order['Auto'] ?></span>
                    </td>
					<td class="order-model">
                        <input type="text" style="width:100px;" value="<?php echo $order['Kennzeichen']; ?>" class="transparent-input" placeholder="" readonly><span style="display: none;"><?php echo $order['Kennzeichen'] ?></span>
                    </td>
                    <!--<td class="order-status">
                        <select class="transparent-input">
                            
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
<!--						<?php //if($order['Status'] == 'processing' && get_post_meta($order['Nr.'], '_transaction_id', true) != "")
//								echo "bezahlt";
//							  elseif($order['Status'] == 'processing' && get_post_meta($order['Nr.'], '_transaction_id', true) == "")
//								echo "Barzahlung";
//							  elseif($order['Status'] == 'cancelled')
//								echo "storniert";
//					?>
                    </td>-->
					<?php if($user->user_login == 'aras' || $user->user_login == 'cakir' || $user->user_login == 'sergej'): ?>
						<td class="order-edit">
							<input type="text" readonly style="width:150px;" value="<?php echo $order['Bearbeitet'] ?>" class="transparent-input">
						</td>
					<?php else: ?>
						<td class="order-edit">
							<input type="text" readonly style="width:150px;" value="-" class="transparent-input">
						</td>
					<?php endif; ?>
                    <td>
<!--                        <a href="#" class="anreiseliste-modal" data-order="--><?php //echo $order['Nr.'] ?><!--"-->
<!--                           data-target="#editListModal">-->
<!--                            Edit-->
<!--                        </a>-->
                        <a href="#" class="save-anreiseliste-row">
                            Speichern
                        </a>
                    </td>
				</tr>
            <?php $i++; endforeach; ?>
            </tbody>
        </table>
        <?php if (count($allorders) <= 0) : ?>
            <p>Keine Ergebnisse gefunden!</p>
        <?php endif; ?>
    </div>
</div>
