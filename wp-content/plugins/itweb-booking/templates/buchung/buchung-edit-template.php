<?php
require_once get_template_directory() . '/lib/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;
global $wpdb;

$db = Database::getInstance();
$current_user = wp_get_current_user();
$roles = ( array ) $current_user->roles;

$order_id = $_GET["edit"];
$order = wc_get_order($order_id);
$parklot = Database::getInstance()->getParklotByProductId(count($order->get_items()) > 0 ? array_values($order->get_items())[0]->get_product_id() : '');
$client_lots = Database::getInstance()->getClientLots();
$broker_id = Database::getInstance()->getBrokerByOrderId($order_id);
if($broker_id->broker_id)
	$broker_lots = Database::getInstance()->getBrokerLotsById($broker_id->broker_id);
$editLog = Database::getInstance()->getEditBookingLog($order_id);
$orderSQL = Orders::getOrderByOrderId($order_id);

if($orderSQL == null)
	$orderSQL = HotelTransfers::getHotelTransferByOrderId($order_id);

if($order == null || $order == "")
	die("Buchung nicht vorhanden. <button onclick='history.go(-1);'>Zurück</button>");

$booking['token'] = $orderSQL->token;
$booking['dateCreated'] = dateFormat($orderSQL->post_date, 'de');
$booking['price'] = number_format($orderSQL->order_price, 2, ".", ".");
$booking['dateFrom'] = dateFormat($orderSQL->Anreisedatum, 'de');
$booking['timeFrom'] = date('H:i', strtotime($orderSQL->Uhrzeit_von));
$booking['dateTo'] = dateFormat($orderSQL->Abreisedatum, 'de');
$booking['timeTo'] = date('H:i', strtotime($orderSQL->Uhrzeit_bis));
$booking['product'] = count($order->get_items()) > 0 ? array_values($order->get_items())[0]->get_name() : '';
$booking['productId'] = count($order->get_items()) > 0 ? array_values($order->get_items())[0]->get_product_id() : '';
$booking['person'] = $orderSQL->nr_people;
$booking['flightTo'] = $orderSQL->out_flight_number;
$booking['flightFrom'] = $orderSQL->return_flight_number;
$booking['plate'] = $orderSQL->Kennzeichen;
$rabatt = $order->get_meta('Rabatt');


$productAdditionalServices = $db->getProductAdditionalServices($booking['productId']);
$additionalServicesPrice = $orderSQL->service_price;
$servicePrice = $orderSQL->service_price;

if($booking['product'] == null || $booking['product'] == "")
	die("Produkt zu der Buchung ist nicht vorhanden. <button onclick='history.go(-1);'>Zurück</button>");

if($parklot->is_for != 'hotel'){
	$past = getDaysBetween2Dates(new DateTime($booking['dateTo']), new DateTime(date("Y-m-d")), $a = false) - 1;
		if($past < 0 || $roles[0] == 'fahrer'){
			$disabled = 'readonly="true"';
			$disabledBtn = "disabled";
		}			
		else{
			$disabled = "";
			$disabledBtn = "";
		}			
	}
else{
	$disabled = "";
	$disabledBtn = "";
}

if($parklot->type != 'valet' && $roles[0] == 'fahrer')
	$updateBtn = "disabled";
else
	$updateBtn = "";

$tax = 19.00;
$lotType = $parklot->type;

if($parklot->is_for != 'hotel'){
	if(isset($_GET["dateFrom"]) && isset($_GET["dateTo"])){
		$newCosts = Pricelist::calculate($booking['productId'], dateFormat($_GET["dateFrom"]), dateFormat($_GET["dateTo"]));
		if($newCosts == null || $newCosts == "")
			die("Bei der Ermittlung des Preises ist ein Fehler aufgetreten. <button onclick='history.go(-1);'>Zurück</button>");
		elseif($newCosts == '0.00')
			die("Parkplätze mit dem gewählten Datum nicht vorhanden. <button onclick='history.go(-1);'>Zurück</button>");
	}
	if(isset($_GET["dateFrom"]) && isset($_GET["dateTo"])){
		$days = getDaysBetween2Dates(new DateTime($_GET["dateFrom"]), new DateTime($_GET["dateTo"]));
		$priceList = Pricelist::calculate($booking['productId'], dateFormat($_GET['dateFrom']), dateFormat($_GET['dateTo']));
	}
	else {
		$days = getDaysBetween2Dates(new DateTime($order->get_meta('Anreisedatum')), new DateTime($order->get_meta('Abreisedatum')));
		$priceList = $booking['price']; //Pricelist::calculate($booking['productId'], dateFormat($booking['dateFrom']), dateFormat($booking['dateFrom']));
	}
}
else {
	$priceList = $booking['price'];
}


if(isset($_POST["update"])){
	//die(print_r($_POST, true));
	$orig_tax = get_post_meta($order_id, '_order_tax', true);
	$orig_price = get_post_meta($order_id, '_order_total', true);
	
	// get original data to compare with updated
	
	//$orig_data['anrede'] = get_post_meta($order_id, 'Anrede', true);
	$orig_data['firmenname'] = $orderSQL->company;
	$orig_data['first_name'] = $orderSQL->first_name;
	$orig_data['last_name'] = $orderSQL->last_name;
	$orig_data['phone_number'] = $orderSQL->phone;
	$orig_data['mail_adress'] = $orderSQL->email;
	$orig_data['lot_name'] = $booking['product'];
	$orig_data['productId'] = $booking['productId'];
	$orig_data['booking_date'] = $booking['dateCreated'];
	$orig_data['booking_token'] = $booking['token'];
	$orig_data['dateFrom'] = $booking['dateFrom'];
	$orig_data['timeFrom'] = $booking['timeFrom'];
	$orig_data['dateTo'] = $booking['dateTo'];
	$orig_data['timeTo'] = $booking['timeTo'];
	$orig_data['person'] = $booking['person'];
	$orig_data['flightTo'] = $booking['flightTo'];
	$orig_data['flightFrom'] = $booking['flightFrom'];
	$orig_data['plate'] = $booking['plate'];
	$orig_data['days'] = $days;
	$orig_data['price'] = number_format($booking['price'], 2, ".", ".");
	$orig_data['service'] = $servicePrice;
	
	if(isset($_POST["send_mail"]))
		$orig_data['send_mail'] = "mail";
	if(isset($_POST["send_protocol_mail"]))
		$orig_data['send_protocol_mail'] = "mail_p";
	$orig_data['update'] = 1;
	
	// get updated data to compare with updated
	foreach($_POST as $key => $val){
		if($key == 'service') continue;
		$update_data[$key] = $val;
	}
	foreach($update_data['add_ser_id'] as $val){
		if($val){
			$sv = Database::getInstance()->getAdditionalService($val);
			$update_data['service'] += $sv->price;
		}		
	}
	if($update_data['service'] == null)
		$update_data['service'] = 0;
	
	
	// Check of data changes and save
	foreach($update_data as $key => $val){
		if($key == 'add_ser_id' || $key == 'totalPrice') continue;
		if($orig_data[$key] != $val)
			Database::getInstance()->addEditBookingLog($order_id, $key, $orig_data[$key] . " -> " . $val);
	}
	
	if(isset($_POST["send_mail"]))
		Database::getInstance()->addEditBookingLog($order_id, 'sent_mail', 1);
	
	foreach($_POST['add_ser_id'] as $service){
		if($service)
			Database::getInstance()->saveBookingMeta($order_id, 'additional_services', $service);
	}
	
	// update Data
	$db->updateOrder($order_id, $_POST);
	
	if(isset($_POST["send_mail"]) && $_POST["update"] == 1 && $_GET["updated"] == 0){
		$mailer = WC()->mailer();
		$mails = $mailer->get_emails();
		if ( ! empty( $mails ) ) {                
			foreach ( $mails as $mail ) {
				if ( $mail->id == 'customer_processing_order' /*|| $mail->id == 'customer_on_hold_order' */ ){
					$mail->trigger( $order_id ); 
					break;
				}
			}            
		}
		//WC()->mailer()->get_emails()['WC_Email_New_Order']->trigger( $order_id );
	}
	
	echo("<script>location.href = '/wp-admin/admin.php?page=buchung-bearbeiten&edit=". $order_id . "&updated=1';</script>");
	//echo "<pre>"; print_r($_POST); echo "</pre>";
}
//echo "<pre>"; print_r($broker_id); echo "</pre>";
?>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Buchung bearbeiten - <?php echo $booking['token'] ?> <?php echo $rabatt != null || $rabatt != 0 ? " - " . $rabatt : "" ?></h3>
    </div>
	<div class="page-body">
		<form class="update-price" action="<?php echo basename($_SERVER['REQUEST_URI']); ?>" method="POST" enctype="multipart/form-data">
						
			<!-- Stornostatus -->
			<?php 
				if(empty($_GET["dateFrom"]))
					$from = $booking['dateFrom'];
				else 
					$from = $_GET["dateFrom"];
				
				if(isset($_GET["dateFrom"]) && $_GET["dateTo"] == '')
					$to = "";
				elseif(isset($_GET["dateFrom"]) && $_GET["dateTo"] != '')
					$to = $_GET["dateTo"];									
				else 
					$to = $booking['dateTo'];
								
			?>
			<div class="row m60 ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Kunden Details</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row">						
						<?php if($orderSQL->company != ''): ?>
						<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2 ">
							<label for="firmenname">Firma</label>
							<input type="text" name="firmenname" placeholder="" class="" value="<?php echo $orderSQL->company ?>" <?php echo $disabled; ?>>
						</div>
						<?php endif; ?>
						<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
							<label for="first_name">Vorname</label>
							<input type="text" name="first_name" placeholder="" class="" value="<?php echo $orderSQL->first_name ?>" <?php echo $disabled; ?>>
						</div>
						<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
							<label for="last_name">Nachname</label>
							<input type="text" name="last_name" placeholder="" class="" value="<?php echo $orderSQL->last_name ?>" <?php echo $disabled; ?>>
						</div>
						<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
							<label for="phone_number">Mobilnummer</label>
							<input type="text" name="phone_number" placeholder="" class="" value="<?php echo $orderSQL->phone ?>" <?php echo $disabled; ?>>
						</div>
						<?php if($parklot->is_for != 'hotel'): ?>
						<div class="col-sm-12 col-xs-12 col-md-6 col-lg-2">
							<label for="mail_adress">E-Mail</label>
							<input type="email" name="mail_adress" placeholder="" class="" value="<?php echo $orderSQL->email ?>" <?php echo $disabled; ?>>
						</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<div class="row ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Buchungsinformationen</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row">
						<?php if($parklot->is_for != 'hotel' && ($parklot->is_for != 'vermittler' || $broker_id->broker_id == 6)): ?>
							<div class="col-sm-12 col-xs-12 col-md-4 col-lg-3">
								<label for="lot_name">Produkt</label><br>
								<select name="productId" class="form-item form-control" 
								id="select_Product">
									<?php foreach($client_lots as $client_lot) : ?>
										<option value="<?php echo $client_lot->product_id ?>"
											<?php echo $client_lot->product_id == $booking['productId'] ? ' selected' : '' ?>>
											<?php echo $client_lot->parklot ?>
										</option>
									<?php endforeach; ?>
									<?php if($broker_id->broker_id == 6): ?>
										<?php foreach($broker_lots as $broker_lot) : ?>
											<option value="<?php echo $broker_lot->product_id ?>"
												<?php echo $broker_lot->product_id == $booking['productId'] ? ' selected' : '' ?>>
												<?php echo $broker_lot->parklot ?>
											</option>
										<?php endforeach; ?>
									<?php endif; ?>
								</select>
							</div>
						<?php else: ?>
							<input type="hidden" name="productId" value="<?php echo $booking['productId'] ?>">
							<div class="col-sm-12 col-xs-12 col-md-4 col-lg-3">
								<label for="lot_name">Produkt</label><br>
								<input type="text" name="lot_name" size="35" placeholder="" class="" value="<?php echo $booking['product'] ?>" readonly="true">
							</div>
						<?php endif; ?>
						<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
							<label for="booking_date">Buchungstag</label>
							<input type="text" name="booking_date" placeholder="" class="" value="<?php echo $booking['dateCreated'] ?>" readonly="true">
						</div>
						<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
							<label for="booking_token">Buchungsnummer</label>
							<input type="text" name="booking_token" placeholder="" class="" value="<?php echo $booking['token'] ?>" readonly="true">
						</div>
					</div>
					<hr>
					<div class="row">
						<?php if($parklot->is_for != 'hotel' || ($parklot->is_for == 'hotel' && $booking['dateFrom'] != null && $booking['dateFrom'] != '01.01.1970')): ?>
							
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="dateFrom">Anreisedatum</label>								
								<?php if($roles[0] == 'fahrer'): ?>
								<input type="text" name="dateFrom" placeholder="" class="" value="<?php echo $from;?>" required <?php echo $disabled; ?>>
								<?php else: ?>
								<input type="text" name="dateFrom" placeholder="" class="single-datepicker editBookingDateFrom" value="<?php echo $from;?>" required <?php echo $disabled; ?>>
								<?php endif;?>
							</div>

						<?php endif;?>
						<?php if($parklot->is_for != 'hotel' || ($parklot->is_for == 'hotel' && $booking['timeFrom'] != null && $booking['dateFrom'] != '01.01.1970')): ?>
							
							<div class="col-sm-12 col-xs-12 col-md-2 col-lg-2">
								<label for="timeFrom">Anreisezeit</label>
								<?php if($roles[0] == 'fahrer'): ?>
								<input type="text" name="timeFrom" placeholder="" class="" value="<?php echo $booking['timeFrom'] ?>" required <?php echo $disabled; ?>>
								<?php else: ?>
								<input type="text" name="timeFrom" placeholder="" class="timepicker" value="<?php echo $booking['timeFrom'] ?>" required <?php echo $disabled; ?>>
								<?php endif; ?>								
							</div>

						<?php endif; ?>
						<?php if($parklot->is_for != 'hotel' || ($parklot->is_for == 'hotel' && $booking['dateTo'] != null && $booking['dateTo'] != '01.01.1970')): ?>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="dateTo">Abreisedatum</label>
								<?php if($roles[0] == 'fahrer'): ?>
								<input type="text" name="dateTo" placeholder="" class="" value="<?php echo $to ?>" required <?php echo $disabled; ?>>
								<?php else: ?>
								<input type="text" name="dateTo" placeholder="" class="single-datepicker editBookingDateTo" value="<?php echo $to ?>" required <?php echo $disabled; ?>>
								<?php endif; ?>
							</div>
						<?php endif; ?>
						<?php if($parklot->is_for != 'hotel' || ($parklot->is_for == 'hotel' && $booking['timeTo'] != null && $booking['dateTo'] != '01.01.1970')): ?>
							<div class="col-sm-12 col-xs-12 col-md-2 col-lg-2">
								<label for="timeTo">Abreisezeit</label>
								<?php if($roles[0] == 'fahrer'): ?>
								<input type="text" name="timeTo" placeholder="" class="" value="<?php echo $booking['timeTo'] ?>" required <?php echo $disabled; ?>>
								<?php else: ?>
								<input type="text" name="timeTo" placeholder="" class="timepicker" value="<?php echo $booking['timeTo'] ?>" required <?php echo $disabled; ?>>
								<?php endif; ?>
							</div>
						<?php endif; ?>
						<?php if($parklot->is_for != 'hotel'): ?>
							<?php if(empty($_GET['anv']) && empty($_GET['uev'])): ?>
							<div class="col-sm-12 col-xs-12 col-md-2 col-lg-1">
								<label for="days">Parkdauer</label>
								<input type="text" name="days" placeholder="" class="" value="<?php echo $days ?>" readonly="true">
							</div>
							<?php endif; ?>
						<?php endif; ?>
					</div>
					<hr>
					<div class="row">
						<?php if($lotType == 'shuttle'): ?>
							<div class="col-sm-12 col-xs-12 col-md-2 col-lg-2">
								<label for="person">Personen</label>
								<input type="text" name="person" placeholder="" class="" value="<?php echo $booking['person'] ?>" required <?php echo $disabled; ?>>
							</div>
						<?php endif; ?>
						<?php if($parklot->is_for != 'hotel' || ($parklot->is_for == 'hotel' && $booking['flightTo'] != null)): ?>
					
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="flightTo">Hinflug-Nr.</label>
								<input type="text" name="flightTo" placeholder="" class="" value="<?php echo $booking['flightTo'] ?>" <?php echo $disabled; ?>>
							</div>

						<?php endif; ?>
						<?php if($parklot->is_for != 'hotel' || ($parklot->is_for == 'hotel' && $booking['flightFrom'] != null)): ?>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="flightFrom">Rückflug-Nr.</label>
								<input type="text" name="flightFrom" placeholder="" class="" value="<?php echo $booking['flightFrom'] ?>" <?php echo $disabled; ?>>
							</div>
						<?php endif; ?>
						<?php if($parklot->is_for != 'hotel'): ?>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="plate">Kennzeichen</label>
								<input type="text" name="plate" placeholder="" class="" value="<?php echo $booking['plate'] ?>" <?php echo $disabled; ?>>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<?php if(count($productAdditionalServices) > 0 && $parklot->is_for != 'hotel'): ?>
				<div class="row ui-lotdata-block ui-lotdata-block-next">
					<h5 class="ui-lotdata-title">Zusatzleistungen</h5>
					<div class="col-sm-12 col-xs-12 col-md-12 col-lg-12 ui-lotdata">
						<div class="row">
							<div class="col-12">
								<div class="row">
									<div class="col-sm-6 col-md-6 col-lg-6">
										<table class="table table-sm add-ser-check">
											<tbody>
											<?php foreach ($productAdditionalServices as $service) : ?>
												<tr class="check-row <?php echo $additionalServicesPrice == $service->price ? "mark_done" : ""; ?>" data-id="<?php echo $service->id; ?>">
													<td>
														<input type="hidden" name="add_ser_id[]" value="<?php echo $additionalServicesPrice == $service->price ? $service->id : "" ?>">
														<?php echo $service->name ?>
													</td>
													<td class="text-right"><?php echo number_format($service->price,2,".","."); ?></td>
												</tr>
											<?php endforeach; ?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>
			<div class="row ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Buchungskosten</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">					
					<div class="row">
						<?php if($parklot->is_for != 'hotel'): ?>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="price">Parkplatz</label>
								<input type="text" name="price" placeholder="" class="" value="<?php echo to_float($priceList) ?>" <?php //echo $disabled; ?>>
							</div>						
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2 total-order-price">
								<label for="service">Service</label>
								<input type="text" name="service" placeholder="" class="" value="<?php echo to_float($servicePrice) ?>" readonly>
							</div>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2 total-order-price">
								<label for="totalPrice">Gesamtpreis</label>
								<input type="text" name="totalPrice" placeholder="" class="current-price" value="<?php echo to_float($priceList + $servicePrice) ?>" <?php echo $disabled; ?>>
							</div>
						<?php else: ?>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="price">Gesamtpreis</label>
								<input type="text" name="price" placeholder="" class="" value="<?php echo to_float($priceList) ?>" <?php //echo $disabled; ?>>
							</div>	
						<?php endif; ?>
						<div class="col-sm-12 col-xs-12 col-md-12 col-lg-12">							
							<h5 style="color: red" id="preiseanderung"></h5>
						</div>						
					</div>
				</div>
			</div>
			<div class="row ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Buchung</h5>
				<div class="col-sm-12 col-md-12 col-lg-12 ui-lotdata">	
					<div class="row">
						<?php if($parklot->is_for != 'hotel'): ?>
							<?php if($roles[0] != 'fahrer'): ?>
							<div class="col-sm-12 col-md-12 col-lg-12">
								<input type="radio" id="send_mail" name="send_mail" value="mail" <?php echo $disabledBtn; ?>>
								<label for="send_mail">Buchungsbestätigung senden</label><br>
							</div>
							<?php endif; ?>
						<?php endif; ?>
						<div class="col-sm-12 col-md-4 col-lg-2">
							<input type="hidden" name="update" value="1">
							<input class="btn btn-primary edit-order-btn" type="submit" value="Buchung aktualisieren" <?php echo $updateBtn; ?>>
						</div>
						<?php if($roles[0] != 'fahrer'): ?>
						<div class="col-sm-12 col-md-4 col-lg-2">
							<input type="hidden" id="order_id" value="<?php echo $order_id; ?>">
							<a class="btn btn-primary" id="editBooking_cancelBtn" <?php echo $disabledBtn; ?>>Buchung stornieren</a>
						</div>
						<?php endif; ?>
							<div class="col-sm-12 col-md-3 col-lg-2">                    
								<a href="<?php echo '/wp-admin/admin.php?page=buchung-bearbeiten' ?>" class="btn btn-secondary d-block w-100" >Schließen</a>
							</div>
					</div>
				</div>
			</div>
			<?php if(count($editLog) > 0): ?>
				<hr>					
				<div class="row ui-lotdata-block ui-lotdata-block-next">
					<h5 class="ui-lotdata-title">Änderungslog</h5>
					<div class="col-sm-12 col-md-12 ui-lotdata">
						<div class="row">
							<table class="editBookingLog-table">
								<thead>
									<tr>
										<th>Datum</th>
										<th>Benutzer</th>
										<th>Feld</th>
										<th>Wert</th>
									</tr>
								</thead>
								<tbody>										
								<?php foreach($editLog as $key => $val): ?>
									<tr>
										<td><?php echo $val->date ?></td>
										<td><?php echo $val->display_name ?></td>
										<td><?php 
										switch ($val->log_key) {											
											case 'firmenname':
												echo 'Firma';
												break;
											case 'first_name':
												echo 'Vorname';
												break;
											case 'last_name':
												echo 'Nachname';
												break;
											case 'phone_number':
												echo 'Mobilnummer';
												break;
											case 'mail_adress':
												echo 'E-Mail';
												break;
											case 'dateFrom':
												echo 'Anreisedatum';
												break;
											case 'timeFrom':
												echo 'Anreisezeit';
												break;
											case 'dateTo':
												echo 'Abreisedatum';
												break;
											case 'timeTo':
												echo 'Abreisezeit';
												break;
											case 'productId':
												echo 'Produkt-ID';
												break;
											case 'person':
												echo 'Anzahl Reisende';
												break;
											case 'flightTo':
												echo 'Flugnummer Hinflug';
												break;
											case 'flightFrom':
												echo 'Flugnummer Rückflug';
												break;
											case 'plate':
												echo 'KFZ-Kennzeichen';
												break;											
											case 'price':
												echo 'Parkkosten';
												break;
											case 'service':
												echo 'Zusatzleistung';
												break;
											case 'sent_mail':
												echo 'Buchungsbestätigung';
												break;
											case 'cancel':
												echo 'Buchung';
												break;										
											 default:
											echo $val->log_key;
										}
										
										
										?></td>
										<td><?php 
											if($val->log_key == 'sent_mail')
												echo "versendet";
											if($val->log_key == 'send_protocol_mail')
												echo "versendet";
											elseif($val->log_key == 'cancel')
												echo "storniert";											
											else
												echo $val->log_value
										?></td>
									</tr>
								<?php endforeach; ?>										
								</tbody>
							</table>
						</div>
					</div>
				</div>			
			<?php endif; ?>
		</form>
	</div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>  
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>  
<script>
function selectProduct(order_id, discount_id, dateFrom, dateTo) {
  var selectElement = document.getElementById("select_Product");
  var preiseanderung = document.getElementById("preiseanderung");
  var ausgewählteOption = selectElement.options[selectElement.selectedIndex].value;
  var price_field = document.getElementsByName("price");
  var helperUrl = '/wp-content/plugins/itweb-booking/classes/Helper.php';
	$.ajax({  
		url: helperUrl,  
		type: 'POST',
		data: {
			task: 'produktPreisAbfrage',
			order_id: order_id,
			product_id: ausgewählteOption,
			discount_id: discount_id,
			dateFrom: dateFrom,
			dateTo: dateTo
		},  
		success:function(price){
			if(parseFloat(price_field[0].value) != parseFloat(price)){
				preiseanderung.innerHTML = "Durch die Produktänderung liegt der Preis bei " + price + "€. <a class='btn btn-primary' id='set_price'>Preis übernehmen</a>";
				var set_price = document.getElementById("set_price");
				set_price.addEventListener("click", function() {				
					var service_field = document.getElementsByName("service");
					var totalPrice_field = document.getElementsByName("totalPrice");
					price_field[0].value = price;
					var ges_preis = parseFloat(price_field[0].value) + parseFloat(service_field[0].value)
					totalPrice_field[0].value = ges_preis.toFixed(2);
				});
			}
			else{
				preiseanderung.innerHTML = "";
			}
		}  
	});
}
var selectElement = document.getElementById("select_Product");
selectElement.addEventListener("change", function() {
  selectProduct(<?php echo json_encode($order_id); ?>, <?php echo json_encode($orderSQL->discount_id); ?>, <?php echo json_encode($from); ?>, <?php echo json_encode($to); ?>);
});
</script>



