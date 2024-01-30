<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-logo">
		<img class="adm-logo" src="<?php echo home_url(); ?>/wp-content/uploads/2021/12/APS-Logo-klein.png" alt="" width="300" height="200">
	</div>

<?php
// products list
$products = Database::getInstance()->getAllLots();
// payment methods list
$payment_methods = get_enabled_payment_methods();

$dateto = date('Y-m-d', strtotime(date('Y-m-d')));
$datefrom = date('Y-m-d', strtotime($dateto . '-2 day'));
if($_GET['anreiseVon'])
	$anreiseVon = date('Y-m-d', strtotime($_GET['anreiseVon']));
if($_GET['anreiseBis'])
	$anreiseBis = date('Y-m-d', strtotime($_GET['anreiseBis']));



if(isok($_GET, 'token')){
	$datefrom = $dateto = $anreise = $abreise = "";
	$token = $_GET['token'];
	unset($_GET['date_from']);
	unset($_GET['date_to']);
	unset($_GET['anreiseVon']);
	unset($_GET['anreiseBis']);
	unset($_GET['payment_method']);
	$filter['datum_von'] = $filter['datum_bis'] = $filter['buchung_von'] = $filter['buchung_bis'] = "";
	$filter['token'] = $token;
	$allorders = Database::getInstance()->getBookings($filter);
}

if((isok($_GET, 'anreiseVon') && isok($_GET, 'anreiseBis')) || ($anreiseVon != "" && $anreiseBis)){
	$token = $datefrom = $dateto = "";
	unset($_GET['date_from']);
	unset($_GET['date_to']);
	unset($_GET['token']);
	$filter['token'] = $filter['buchung_von'] = $filter['buchung_bis'] = "";
	$filter['datum_von'] = $anreiseVon;
	$filter['datum_bis'] = $anreiseBis;
	$filter['orderBy'] = "Anreisedatum";
	$allorders = Database::getInstance()->getBookings($filter);
}

if(isok($_GET, 'payment_method')){
	$token = "";
	unset($_GET['token']);
}

if((isok($_GET, 'date_from') && isok($_GET, 'date_to')) || ($datefrom != "" && $dateto != "")){
	if($_GET['date_from'])
		$datefrom = date('Y-m-d', strtotime($_GET['date_from']));
	if($_GET['date_to'])
		$dateto = date('Y-m-d', strtotime($_GET['date_to']));
	$token = $anreise = $abreise = "";
	unset($_GET['anreiseVon']);
	unset($_GET['anreiseBis']);
	unset($_GET['token']);
	$filter['datum_von'] = $filter['datum_bis'] = $filter['token'] = "";
	$filter['buchung_von'] = $datefrom;
	$filter['buchung_bis'] = date('Y-m-d', strtotime($dateto . '+1 day'));
	$filter['orderBy'] = "Buchungsdatum";
	$allorders = Database::getInstance()->getBookings($filter);
}

?>


    <div class="page-title itweb_adminpage_head">
        <h3>Buchungen</h3>
    </div>
    <div class="page-body">
        <form class="form-filter">
            <div class="row my-2 ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Buchungen filtern</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-md-3 col-lg-2">
							<input type="text" name="token" placeholder="Buchung" class="form-item form-control" value="<?php if($token != "") echo $token; else echo ''; ?>">
						</div>
						<div class="col-sm-12 col-md-1 col-lg-1 ui-lotdata-date">
							<input type="text" id="dateFrom" name="date_from" placeholder="Buchung von" class="form-item form-control single-datepicker" value="<?php if($datefrom != "") echo date('d.m.Y', strtotime($datefrom)); else echo ''; ?>">
						</div>
						<div class="col-sm-12 col-md-1 col-lg-1 ui-lotdata-date">
							<input type="text" id="dateTo" name="date_to" placeholder="Buchung bis" class="form-item form-control single-datepicker" value="<?php if($dateto != "") echo date('d.m.Y', strtotime($dateto)); else echo ''; ?>">
						</div>
						<div class="col-sm-12 col-md-1 col-lg-1 ui-lotdata-date">
							<input type="text" id="arrivaldateFrom" name="anreiseVon" placeholder="Anreise von" class="form-item form-control single-datepicker" value="<?php if($anreiseVon != "") echo date('d.m.Y', strtotime($anreiseVon)); else echo ''; ?>">
						</div>
						<div class="col-sm-12 col-md-1 col-lg-1 ui-lotdata-date">
							<input type="text" id="arrivaldateTo" name="anreiseBis" placeholder="Anreise bis" class="form-item form-control single-datepicker" value="<?php if($anreiseBis != "") echo date('d.m.Y', strtotime($anreiseBis)); else echo ''; ?>">
						</div>
					</div>

					<div class="row my-2">
						<div class="col-sm-12 col-md-3 col-lg-2">
							<select name="product" class="form-item form-control">
								<option value="">Produkt</option>
								<?php foreach($products as $product) : ?>
									<option value="<?php echo $product->product_id ?>"
										<?php echo (isset($_GET['product']) && $_GET['product'] == $product->product_id) ? ' selected' : '' ?>>
										<?php echo $product->parklot ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						
						<div class="col-sm-12 col-md-3 col-lg-2">
							<select name="betreiber" class="form-item form-control">
								<option value="">Betreiber</option>
								<option value="aps" <?php echo (isset($_GET['betreiber']) && $_GET['betreiber'] == 'aps') ? ' selected' : '' ?>>
									<?php echo 'APS' ?>
								</option>
								<option value="apg" <?php echo (isset($_GET['betreiber']) && $_GET['betreiber'] == 'apg') ? ' selected' : '' ?>>
									<?php echo 'APG' ?>
								</option>
								<option value="hex" <?php echo (isset($_GET['betreiber']) && $_GET['betreiber'] == 'hex') ? ' selected' : '' ?>>
									<?php echo 'HEX' ?>
								</option>
								<option value="parkos" <?php echo (isset($_GET['betreiber']) && $_GET['betreiber'] == 'parkos') ? ' selected' : '' ?>>
									<?php echo 'Parkos' ?>
								</option>
								<option value="amh" <?php echo (isset($_GET['betreiber']) && $_GET['betreiber'] == 'amh') ? ' selected' : '' ?>>
									<?php echo 'AMH' ?>
								</option>
								<option value="hma" <?php echo (isset($_GET['betreiber']) && $_GET['betreiber'] == 'hma') ? ' selected' : '' ?>>
									<?php echo 'HMA' ?>
								</option>
								<option value="iaps" <?php echo (isset($_GET['betreiber']) && $_GET['betreiber'] == 'iaps') ? ' selected' : '' ?>>
									<?php echo 'IAPS' ?>
								</option>
							</select>
						</div>
						
						<div class="col-sm-12 col-md-3 col-lg-2">
							<select name="payment_method" class="form-item form-control">
								<option value="">Zahlungsart</option>
									<option value="Barzahlung"
										<?php echo (isset($_GET['payment_method']) && $_GET['payment_method'] == "Barzahlung") ? 'selected' : '' ?>>
										Barzahlung
									</option>
									<option value="MasterCard"
										<?php echo (isset($_GET['payment_method']) && $_GET['payment_method'] == "MasterCard") ? 'selected' : '' ?>>
										MasterCard
									</option>
									<option value="Visa"
										<?php echo (isset($_GET['payment_method']) && $_GET['payment_method'] == "Visa") ? 'selected' : '' ?>>
										Visa
									</option>
									<option value="PayPal"
										<?php echo (isset($_GET['payment_method']) && $_GET['payment_method'] == "PayPal") ? 'selected' : '' ?>>
										PayPal
									</option>
							</select>
						</div>
						<div class="col-sm-12 col-md-3 col-lg-2">
							<button class="btn btn-primary d-block w-100" type="submit">Filter</button>
						</div>
						<div class="col-sm-12 col-md-2">                    
							<a href="<?php echo '/wp-admin/admin.php?page=buchungen' ?>" class="btn btn-secondary d-block w-100" >Zurücksetzen</a>
						</div>
					</div>
				</div>
            </div>
        </form>
		<br><br>
		<?php if($datefrom != "" && $dateto != ""):?>
			<table class="bookings_bd_datatable table-responsive">
		<? else: ?>
			<table class="bookings_datatable table-responsive">
		<?php endif; ?>
            <thead>
            <tr>
				<th>Nr.</th>
				<th style="display: none;">Datum</th>
				<th>Datum</th>
                <th>Produkt</th>
                <th>Buchungsnummer</th>
                <th>Name</th>
                <th>Anreisedatum</th>
                <th>Uhrzeit</th>
                <th>Abreisedatum</th>
                <th>Uhrzeit</th>
				<th>Parkdauer</th>
				<th>Personen</th>
                <th>E-Mail</th>
				<!--<th>Status</th>-->
				<th>Zahlungsart</th>
                <th>Gebühren</th>
				<th>Service</th>
                <th>Rechnung</th>
            </tr>
            </thead>
            <tbody>
            <?php $nr = 1; foreach ($allorders as $booking) : ?>
                <?php 
					// filter by payment method
                    if(isset($_GET['payment_method']) && !empty($_GET['payment_method'])){                       
                        if($booking->Bezahlmethode != $_GET['payment_method']){
                            continue;
                        }
                    }
					// filter by product id
                    if(isset($_GET['product']) && !empty($_GET['product'])){
                        if($booking->Produkt != $_GET['product']){
                            continue;
                        }
                    }
					
					if(isset($_GET['betreiber'])){
						if($_GET['betreiber'] == 'aps'){
							if($booking->Produkt != 537 && $booking->Produkt != 592 && $booking->Produkt != 619 && $booking->Produkt != 873 && $booking->Produkt != 24222 && $booking->Produkt != 28881 && $booking->Produkt != 24226){
								continue;
							}
						}
						elseif($_GET['betreiber'] == 'apg'){
							if($booking->Produkt != 595 && $booking->Produkt != 3080 && $booking->Produkt != 3081 && $booking->Produkt != 3082 && $booking->Produkt != 24224 && $booking->Produkt != 24228){
								continue;
							}
						}
						elseif($_GET['betreiber'] == 'hex'){
							if($booking->Produkt != 621 && $booking->Produkt != 683 && $booking->Produkt != 624 && $booking->Produkt != 24609 && $booking->Produkt != 901 && $booking->Produkt != 24261 && $booking->Produkt != 28878 && $booking->Produkt != 24263){
								continue;
							}
						}
						elseif($_GET['betreiber'] == 'parkos'){
							if($booking->Produkt != 41402 && $booking->Produkt != 41403){
								continue;
							}
						}
						elseif($_GET['betreiber'] == 'amh'){
							if($booking->Produkt != 3851){
								continue;
							}
						}
						elseif($_GET['betreiber'] == 'hma'){
							if($booking->Produkt != 6762){
								continue;
							}
						}
						elseif($_GET['betreiber'] == 'iaps'){
							if($booking->Produkt != 6772){
								continue;
							}
						}
                    }
				
                    $hasGeneratedInvoice = count(get_post_meta($booking->order_id, 'generated_invoice')) > 0;
					$additionalPrice = "0.00";
					$services = Database::getInstance()->getBookingMetaAsResults($booking->order_id, 'additional_services');
					if(count($services) > 0){
						foreach($services as $v){
							$s = Database::getInstance()->getAdditionalService($v->meta_value);
							$additionalPrice += $s->price;
						}
					}
                                       
                ?>
                <tr class="<?php echo $hasGeneratedInvoice ? 'mark_done' : '' ?>">
					<td>
                        <?php echo $nr ?>
                    </td>
					<?php if($datefrom != "" && $dateto != ""):?>
						<td style="display: none;">
							<?php echo date('Y-m-d', strtotime($booking->Buchungsdatum)); ?>
						</td>
					<? elseif($anreiseVon != "" && $anreiseBis): ?>
						<td style="display: none;">
							<?php echo date('Y-m-d', strtotime($booking->Anreisedatum)); ?>
						</td>
					<?php endif; ?>
					<td>
                        <?php echo date('d.m.Y', strtotime($booking->Buchungsdatum)); ?>
                    </td>
                    <td style="background-color: <?php echo $booking->Color ?> !important">
                        <?php echo $booking->Code ?>
                    </td>
                    <td>
                        <?php echo $booking->Token ?>
                    </td>
                    <td>
                        <?php echo $booking->Vorname . ' ' . $booking->Nachname ?>
                    </td>
                    <td>
                        <?php if($booking->Anreisedatum): ?>
							<?php echo date('d.m.Y', strtotime($booking->Anreisedatum)) ?>
						<?php else: echo "-" ?>
						<?php endif; ?>
                    </td>
                    <td>
                        <?php if($booking->Uhrzeit_von): ?>
							<?php echo date('H:i', strtotime($booking->Uhrzeit_von)) ?>
						<?php else: echo "-" ?>
						<?php endif; ?>
                    </td>
                    <td>
                        <?php if($booking->Abreisedatum): ?>
							<?php echo date('d.m.Y', strtotime($booking->Abreisedatum)) ?>
						<?php else: echo "-" ?>
						<?php endif; ?>
                    </td>
                    <td>
                        <?php if($booking->Uhrzeit_bis): ?>
							<?php echo date('H:i', strtotime($booking->Uhrzeit_bis)) ?>
						<?php else: echo "-" ?>
						<?php endif; ?>
                    </td>
                    <td>
                        <?php if($booking->Anreisedatum != null && $booking->Abreisedatum != null && $booking->is_for != 'hotel'): ?>
							<?php echo getDaysBetween2Dates(new DateTime($booking->Anreisedatum), new DateTime($booking->Abreisedatum)) ?>
						<?php else: echo "-" ?>
						<?php endif; ?>
                    </td>
					<td>
                        <?php if($booking->Personenanzahl): ?>
							<?php echo $booking->Personenanzahl ?>
						<?php else: echo "-" ?>
						<?php endif; ?>	
                    </td>
                    <td>
                        <?php if($booking->Email): ?>
							<?php echo $booking->Email ?>
						<?php else: echo "-" ?>
						<?php endif; ?>	
                    </td>
                    <!--<td>
                    <?php 
						if($booking->Status == 'wc-processing') 
							echo "abgeschlossen";
						elseif($booking->Status == 'wc-cancelled') 
							echo "storniert";
					?>
                    </td>-->
					<td>
                        <?php echo $booking->Bezahlmethode ?>
                    </td>
                    <td>
                        <?php 
						
						if($booking->Bezahlmethode == "Barzahlung")
							echo $booking->Betrag /*. ' ' . $order->get_currency()*/;
						else
							echo "bezahlt";
						?>
                    </td>
					<td>
                        <?php if($additionalPrice != '0.00') echo number_format($additionalPrice, 2, '.', ''); else echo '-' ?>
                    </td>
                    <td>
                        <a target="_blank"
                           href="/wp-admin/admin-ajax.php?action=generate_wpo_wcpdf&document_type=invoice&order_ids=<?php echo $booking->order_id ?>&_wpnonce=<?php echo wp_create_nonce('generate_wpo_wcpdf') ?>">PDF</a>
                    </td>
                </tr>
            <?php $nr++; endforeach; ?>
            </tbody>
        </table>
    </div>
</div>