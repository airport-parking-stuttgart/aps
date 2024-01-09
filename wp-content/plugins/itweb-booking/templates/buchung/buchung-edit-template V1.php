<?php
require_once get_template_directory() . '/lib/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

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
if($order == null || $order == "")
	die("Buchung nicht vorhanden. <button onclick='history.go(-1);'>Zurück</button>");
global $wpdb;
$db = Database::getInstance();
$booking['token'] = $order->get_meta('token');
$booking['dateCreated'] = dateFormat($order->order_date, 'de');
$booking['price'] = $order->get_total();
$booking['dateFrom'] = dateFormat($order->get_meta('Anreisedatum'), 'de');
$booking['timeFrom'] = date('H:i', strtotime($order->get_meta('Uhrzeit von')));
$booking['dateTo'] = dateFormat($order->get_meta('Abreisedatum'), 'de');
$booking['timeTo'] = date('H:i', strtotime($order->get_meta('Uhrzeit bis')));
$booking['product'] = count($order->get_items()) > 0 ? array_values($order->get_items())[0]->get_name() : '';
$booking['productId'] = count($order->get_items()) > 0 ? array_values($order->get_items())[0]->get_product_id() : '';
$booking['person'] = $order->get_meta('Personenanzahl');
$booking['flightTo'] = $order->get_meta('Hinflugnummer');
$booking['flightFrom'] = $order->get_meta('Rückflugnummer');
$booking['plate'] = $order->get_meta('Kennzeichen');
$rabatt = $order->get_meta('Rabatt');
if($parklot->type == 'valet'){
	$booking['model'] = $order->get_meta('Fahrzeughersteller');
	$booking['type'] = $order->get_meta('Fahrzeugmodell');
	$booking['color'] = $order->get_meta('Fahrzeugfarbe');
	$booking['kilometerstand'] = $order->get_meta('Kilometerstand');
	$booking['tankstand'] = $order->get_meta('Tankstand');
	$booking['merkmale'] = $order->get_meta('Merkmale');
	$booking['a-m-date'] = $order->get_meta('Annahmedatum MA');
	$booking['a-m-time'] = $order->get_meta('Annahmezeit MA');
	$booking['a-m-user'] = $order->get_meta('Annahme MA');
	$booking['u-kunde-date'] = $order->get_meta('Übergabedatum K');
	$booking['u-kunde-time'] = $order->get_meta('Übergabezeit K');
	$booking['u-kunde'] = $order->get_meta('Übergabe K');
	$booking['u-date'] = $order->get_meta('Übergabedatum Ende');
	$booking['u-unterschrift-ma'] = $order->get_meta('Unterschrift MA');
	$booking['u-unterschrift-k'] = $order->get_meta('Unterschrift K');
}
$productAdditionalServices = $db->getProductAdditionalServices($booking['productId']);
$additionalServicesPrice = $db->getBookingServices($order_id, $booking['productId']);
$car_images = $db->getValetCarImage($order_id);
$car_videos = $db->getValetCarVideos($order_id);

$servicePrice = 0;
if(count($additionalServicesPrice) > 0){
	foreach($additionalServicesPrice as $k){
		$servicePrice += $k->price;
	}
}

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

if(file_exists(ABSPATH . 'wp-content/uploads/valet-protokolle/u_m-' . get_post_meta($order_id, 'token', true) . ".png") || $_POST["cm_hidden"] != null){
		$unterschrift_m = 1;
	}
	else
		$unterschrift_m = 0;
if(file_exists(ABSPATH . 'wp-content/uploads/valet-protokolle/u_k-' . get_post_meta($order_id, 'token', true) . ".png") || $_POST["ck_hidden"] != null){
		$unterschrift_k = 1;
	}
	else
		$unterschrift_k = 0;

if(isset($_POST["update"])){
	//die(print_r($_POST, true));
	$orig_tax = get_post_meta($order_id, '_order_tax', true);
	$orig_price = get_post_meta($order_id, '_order_total', true);
	
	// get original data to compare with updated
	
	if(isset($_POST["toPay"]) && $_POST["toPay"] == "addPay")
		$orig_data['toPay'] = "addPay";
	if(isset($_POST["toPay"]) && $_POST["toPay"] == "credit")
		$orig_data['toPay'] = "credit";
	
	//$orig_data['anrede'] = get_post_meta($order_id, 'Anrede', true);
	$orig_data['firmenname'] = get_post_meta($order_id, '_billing_company', true);
	$orig_data['first_name'] = get_post_meta($order_id, '_billing_first_name', true);
	$orig_data['last_name'] = get_post_meta($order_id, '_billing_last_name', true);
	$orig_data['phone_number'] = get_post_meta($order_id, '_billing_phone', true);
	$orig_data['mail_adress'] = get_post_meta($order_id, '_billing_email', true);
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
	if($parklot->type == 'valet'){
		$orig_data['model'] = $booking['model'];
		$orig_data['type'] = $booking['type'];
		$orig_data['color'] = $booking['color'];
		$orig_data['kilometerstand'] = $booking['kilometerstand'];
		$orig_data['tankstand'] = $booking['tankstand'];
		$orig_data['merkmale'] = $booking['merkmale'];	
		$orig_data['a-m-date'] = $booking['a-m-date'];
		$orig_data['a-m-time'] = $booking['a-m-time'];
		$orig_data['a-m-user'] = $booking['a-m-user'];
		$orig_data['u-kunde-date'] = $booking['u-kunde-date'];
		$orig_data['u-kunde-time'] = $booking['u-kunde-time'];
		$orig_data['u-kunde'] = $booking['u-kunde'];
		$orig_data['u-date'] = $booking['u-date'];
		$orig_data['u-unterschrift-ma'] = $booking['u-unterschrift-ma'];
		$orig_data['u-unterschrift-k'] = $booking['u-unterschrift-k'];
	}
	$orig_data['days'] = $days;
	$orig_data['price'] = $booking['price'];
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
	
	if($update_data['dateFrom'] != $orig_data['dateFrom'] || $update_data['dateTo'] != $orig_data['dateTo']){
		if(get_post_meta($order_id, '_billing_addPay', true) != null)
			delete_post_meta($order_id, '_billing_addPay' );
		if(get_post_meta($order_id, '_billing_credit', true) != null)
			delete_post_meta($order_id, '_billing_credit' );
	}
	
	if(isset($_POST["send_mail"]))
		Database::getInstance()->addEditBookingLog($order_id, 'sent_mail', 1);
	if(isset($_POST["send_protocol_mail"]))
		Database::getInstance()->addEditBookingLog($order_id, 'send_protocol_mail', 1);
	
	if(isset($_POST["toPay"]) && $_POST["toPay"] == "addPay" && get_post_meta($order_id, '_billing_addPay', true) == 0){
		Database::getInstance()->addEditBookingLog($order_id, 'addPay', 1);
		update_post_meta($order_id, '_billing_addPay', 1);
	}
	elseif($_POST["toPay"] == null && get_post_meta($order_id, '_billing_addPay', true) == 1){
		Database::getInstance()->addEditBookingLog($order_id, 'addPay', 0);
		update_post_meta($order_id, '_billing_addPay', 0);
	}
	
	if(isset($_POST["toPay"]) && $_POST["toPay"] == "credit" && get_post_meta($order_id, '_billing_credit', true) == 0){
		Database::getInstance()->addEditBookingLog($order_id, 'credit', 1);
		update_post_meta($order_id, '_billing_credit', 1);
	}
	elseif($_POST["toPay"] == null && get_post_meta($order_id, '_billing_credit', true) == 1){
		Database::getInstance()->addEditBookingLog($order_id, 'credit', 0);
		update_post_meta($order_id, '_billing_credit', 0);
	}
	/*echo "<pre>";
	print_r($orig_data);
	echo "</pre>";
	echo "<pre>";
	print_r($update_data);
	echo "</pre>";*/
	
	// Set first booking price
	if(get_post_meta($order_id, '_order_original_total', true) == null){
		update_post_meta($order_id, '_order_original_tax', $orig_tax);
		update_post_meta($order_id, '_order_original_total', $orig_price);
	}
	if($order->get_status() == 'processing'){ 
		if(get_post_meta($order_id, '_order_original_total', true) != null && get_post_meta($order_id, '_order_original_total', true) + $servicePrice < get_post_meta($order_id, '_order_total', true) + $servicePrice){
			if(get_post_meta($order_id, '_billing_addPay', true) == null)
				update_post_meta($order_id, '_billing_addPay', 0);
			if(get_post_meta($order_id, '_billing_credit', true) != null)
				delete_post_meta($order_id, '_billing_credit' );
		}
		else if(get_post_meta($order_id, '_order_original_total', true) != null && get_post_meta($order_id, '_order_original_total', true) + $servicePrice > get_post_meta($order_id, '_order_total', true) + $servicePrice){
			if(get_post_meta($order_id, '_billing_credit', true) == null)	
				update_post_meta($order_id, '_billing_credit', 0);
			if(get_post_meta($order_id, '_billing_addPay', true) != null)
				delete_post_meta($order_id, '_billing_addPay' );
		}
		else if(get_post_meta($order_id, '_order_original_total', true) != null && get_post_meta($order_id, '_order_original_total', true) + $servicePrice == get_post_meta($order_id, '_order_total', true) + $servicePrice){
			if(get_post_meta($order_id, '_billing_addPay', true) != null)
				delete_post_meta($order_id, '_billing_addPay' );
			if(get_post_meta($order_id, '_billing_credit', true) != null)
				delete_post_meta($order_id, '_billing_credit' );
		}
	}
	if(isset($_POST["toPay"]) && $_POST["toPay"] == "addPay" && get_post_meta($order_id, '_billing_addPay', true) == 0){
		update_post_meta($order_id, '_billing_addPay', 1);
	}
	elseif($_POST["toPay"] == null && get_post_meta($order_id, '_billing_addPay', true) == 1){
		update_post_meta($order_id, '_billing_addPay', 0);
	}
	if(isset($_POST["toPay"]) && $_POST["toPay"] == "credit" && get_post_meta($order_id, '_billing_credit', true) == 0){
		update_post_meta($order_id, '_billing_credit', 1);
	}
	elseif($_POST["toPay"] == null && get_post_meta($order_id, '_billing_credit', true) == 1){
		update_post_meta($order_id, '_billing_credit', 0);
	}
	
	Database::getInstance()->deleteBookingMetaByKey($order_id, 'additional_services');
	
	foreach($_POST['add_ser_id'] as $service){
		if($service)
			Database::getInstance()->saveBookingMeta($order_id, 'additional_services', $service);
	}
	
	// update Data
	$db->updateOrder($order_id, $_POST);
	
	if(!file_exists(ABSPATH . 'wp-content/uploads/valet-car-images')){
		mkdir(ABSPATH . 'wp-content/uploads/valet-car-images');
	}
	$images = $_FILES['car_images'];
	if(count($images['name']) > 0)
		Database::getInstance()->saveValetCarImage($order_id, $images['name']);
	for ($i = 0; $i < count($images['name']); $i++) {
		$target_file = ABSPATH . 'wp-content/uploads/valet-car-images/' . basename($images['name'][$i]);
		move_uploaded_file($images["tmp_name"][$i], $target_file);
	}
	
	if(!file_exists(ABSPATH . 'wp-content/uploads/valet-car-videos')){
		mkdir(ABSPATH . 'wp-content/uploads/valet-car-videos');
	}
	
	$videos = $_FILES['car_videos'];
	if(count($videos['name']) > 0)
		Database::getInstance()->saveValetCarVideo($order_id, $videos['name']);
	
	for ($k = 0; $k < count($videos['name']); $k++) {
		$target_file = ABSPATH . 'wp-content/uploads/valet-car-videos/' . basename($videos['name'][$k]);
		move_uploaded_file($videos["tmp_name"][$k], $target_file);
	}
	
	
	if(isset($_POST["cm_hidden"]) && $_POST["cm_hidden"] != null){
		if(strlen($_POST["cm_hidden"]) > 1300){
			$img = $_POST["cm_hidden"];
			$img = str_replace('data:image/png;base64,', '', $img);
			$img = str_replace(' ', '+', $img);
			$data = base64_decode($img);
			$fileName = "u_m-" . get_post_meta($order_id, 'token', true) . ".png";
			if(!file_exists(ABSPATH . 'wp-content/uploads/valet-protokolle')){
				mkdir(ABSPATH . 'wp-content/uploads/valet-protokolle');
			}
			$upload_dir = ABSPATH . 'wp-content/uploads/valet-protokolle/';
			$file = $upload_dir . $fileName;
			file_put_contents($file, $data);
		}
	}
	
	if(isset($_POST["ck_hidden"]) && $_POST["ck_hidden"] != null){
		if(strlen($_POST["ck_hidden"]) > 1300){
			$img = $_POST["ck_hidden"];
			$img = str_replace('data:image/png;base64,', '', $img);
			$img = str_replace(' ', '+', $img);
			$data = base64_decode($img);
			$fileName = "u_k-" . get_post_meta($order_id, 'token', true) . ".png";
			if(!file_exists(ABSPATH . 'wp-content/uploads/valet-protokolle')){
				mkdir(ABSPATH . 'wp-content/uploads/valet-protokolle');
			}
			$upload_dir = ABSPATH . 'wp-content/uploads/valet-protokolle/';
			$file = $upload_dir . $fileName;
			file_put_contents($file, $data);
		}
	}
	
	if(isset($_POST["send_protocol_mail"]) && $_POST["update"] == 1 && $_GET["updated"] == 0 && $lotType == 'valet'){
		
		ob_start();
		?>
		<style>
		table{
			border-collapse: collapse
		}
		td, th{
			border:1px solid black;
		}
		.valet-car-image{
			float: left; margin-right: 5px;
		}
		.clear {
			clear: left;
		}

		.u-k-table{
			float: left; margin-right: 10px;
		}

		.page_break { page-break-before: always; }

		</style>
		<table>
			<tr>
				<td style="width: 700px; border: none;">
					<img style="max-height: 150px; width: auto" src="<?php echo get_home_url() . '/wp-content/uploads/2021/05/logo-e1596314559277.png' ?>" alt="">
				</td>
				<td style="width: 100px; border: none; text-align: right;">
					<p style="">APS Airport-Parking-Stuttgart GmbH<br>
					Raiffeisenstraße 18 – 70794 Filderstadt</p>
					<p>Buchung: <?php echo get_post_meta($order_id, 'token', true) ?><br>
					Seite 1 - 3</p>
				</td>
			</tr>
		</table>
		<br><br>

		<div class="col-12 m60">
		<h3>Valet-Service Annahme Protokoll zur Buchung <?php echo get_post_meta($order_id, 'token', true) ?></h3>
		</div>

		<div class="col-12 m60">
			<h5>Kunden Details</h5>
			<table>
				<tr>
					<?php //if(get_post_meta($order_id, '_billing_company', true)): ?>
					<!--<th style="width: 250px">Firma</th>-->
					<?php //endif; ?>
					<th style="width: 250px">Name, Nachname</th>
					<th style="width: 125px">Telefon</th>
					<th style="width: 250px">E-Mail</th>
				</tr>
				<tr>
					<?php //if(get_post_meta($order_id, '_billing_company', true)): ?>
					<!--<td><?php echo get_post_meta($order_id, '_billing_company', true) ?></td>-->
					<?php //endif; ?>
					<td><?php echo get_post_meta($order_id, '_billing_first_name', true) . " " . get_post_meta($order_id, '_billing_last_name', true) ?></td>
					<td><?php echo get_post_meta($order_id, '_billing_phone', true) ?></td>
					<td><?php echo get_post_meta($order_id, '_billing_email', true) ?></td>
				</tr>
			</table>

			<h5>Buchungsinformationen</h5>
			<table>
				<tr>
					<th style="width: 125px">Anreisedatum</th>
					<th style="width: 125px">Anreisezeit</th>
					<th style="width: 125px">Abreisedatum</th>
					<th style="width: 125px">Abreisezeit</th>
					<th style="width: 125px">Hinflug-Nr.</th>
					<th style="width: 125px">Rückflug-Nr.</th>
					<th style="width: 125px">Park-Nr.</th>
				</tr>
				<tr>
					<td><?php echo dateFormat(get_post_meta($order_id, 'Anreisedatum', true), 'de') ?></td>
					<td><?php echo get_post_meta($order_id, 'Uhrzeit von', true) ?></td>
					<td><?php echo dateFormat(get_post_meta($order_id, 'Abreisedatum', true), 'de') ?></td>
					<td><?php echo get_post_meta($order_id, 'Uhrzeit bis', true) ?></td>
					<td><?php echo get_post_meta($order_id, 'Hinflugnummer', true) ?></td>
					<td><?php echo get_post_meta($order_id, 'Rückflugnummer', true) ?></td>
					<td><?php echo get_post_meta($order_id, 'Parkplatz', true) ?></td>
				</tr>
				<tr>
					<td colspan="6">Adresse: <?php echo $parklot->adress ?></td>
				</tr>
			</table>
			<h5>Fahrzeuginformationen</h5>
			<table>
				<tr>
					<th style="width: 250px">Hersteller</th>
					<th style="width: 125px">Typ</th>
					<th style="width: 125px">Farbe</th>
					<th style="width: 125px">Kennzeichen</th>
					
					<th style="width: 125px">Kilometerstand</th>
					<th style="width: 125px">Tankfüllung</th>
				</tr>
				<tr>
					<td><?php echo get_post_meta($order_id, 'Fahrzeughersteller', true) ?></td>
					<td><?php echo get_post_meta($order_id, 'Fahrzeugmodell', true) ?></td>
					<td><?php echo get_post_meta($order_id, 'Fahrzeugfarbe', true) ?></td>
					<td><?php echo get_post_meta($order_id, 'Kennzeichen', true) ?></td>
					
					<td><?php echo get_post_meta($order_id, 'Kilometerstand', true) ?></td>
					<td><?php echo 'Ca. ' . get_post_meta($order_id, 'Tankstand', true) . '%' ?></td>
				</tr>
				<tr>
					<td colspan="6">Sonstige Merkmale: <?php echo get_post_meta($order_id, 'Merkmale', true) ?></td>
				</tr>
			</table>
		</div>

		<table class="page_break">
			<tr>
				<td style="width: 700px; border: none;">
					<img style="max-height: 150px; width: auto" src="<?php echo get_home_url() . '/wp-content/uploads/2021/05/logo-e1596314559277.png' ?>" alt="">
				</td>
				<td style="width: 100px; border: none; text-align: right;">
					<p style="">APS Airport-Parking-Stuttgart GmbH<br>
					Raiffeisenstraße 18 – 70794 Filderstadt</p>
					<p>Buchung: <?php echo get_post_meta($order_id, 'token', true) ?><br>
					Seite 2 - 3</p>
				</td>
			</tr>
		</table>
		<br><br>

		<div class="col-12 m60">
			<h5>Vor Ort aufgenommenen Bilder</h5>
				<?php $i = $g = 1; ?>
				<?php foreach ($car_images as $image): ?>
					<?php if($image->image_file == null) continue; ?>
					<div class="valet-car-image">
						<img style="max-height: 175px; width: auto" src="<?php echo get_home_url() . '/wp-content/uploads/valet-car-images/' . basename($image->image_file) ?>" alt="">
					</div>
					<?php if($i == 3){
							echo "<br><br><br><br><br><br><br><br><br><br><br>";
						} ?>
					<?php $i++; $g++; ?>
					<?php if($i > 3) $i = 1; ?>
					<?php if($g == 6) break; ?>
				<?php endforeach; ?>
		</div>
		<div class="clear"></div>

		<table class="page_break">
			<tr>
				<td style="width: 700px; border: none; ">
					<img style="max-height: 150px; width: auto" src="<?php echo get_home_url() . '/wp-content/uploads/2021/05/logo-e1596314559277.png' ?>" alt="">
				</td>
				<td style="width: 100px; border: none; text-align: right;">
					<p style="">APS Airport-Parking-Stuttgart GmbH<br>
					Raiffeisenstraße 18 – 70794 Filderstadt</p>
					<p>Buchung: <?php echo get_post_meta($order_id, 'token', true) ?><br>
					Seite 3 - 3</p>
				</td>
			</tr>
		</table>
		<br><br><br><br>

		<div class="col-12 m60">
			<div class="u-k-table">
				<table>
					<tr>
						<th style="border:1px solid black;" colspan="3">Annahme vom Mitarbeiter</th>
						<th style="width: 100px; border: none;">&nbsp;</th>
						<th style="border:1px solid black;" colspan="3">Abgabe vom Kunde</th>
					</tr>
					<tr>				
						<td style="width: 100px; text-align: center;"><strong>Datum</strong></td>
						<td style="width: 100px; text-align: center;"><strong>Uhrzeit</strong></td>
						<td style="width: 200px; text-align: center;"><strong>Mitarbeiter</strong></td>
						<td style="width: 100px; border: none;">&nbsp;</td>
						<td style="width: 100px; text-align: center;"><strong>Datum</strong></td>
						<td style="width: 100px; text-align: center;"><strong>Uhrzeit</strong></td>
						<td style="width: 200px; text-align: center;"><strong>Kunde</strong></td>
					</tr>
					<tr>
						<td><?php echo get_post_meta($order_id, 'Annahmedatum MA', true) ? dateFormat(get_post_meta($order_id, 'Annahmedatum MA', true), 'de') : "&nbsp;" ?></td>
						<td><?php echo get_post_meta($order_id, 'Annahmezeit MA', true) ?></td>
						<td><?php echo get_post_meta($order_id, 'Annahme MA', true) ?></td>
						<td style="border: none;">&nbsp;</td>
						<td><?php echo get_post_meta($order_id, 'Übergabedatum K', true) ? dateFormat(get_post_meta($order_id, 'Übergabedatum K', true), 'de') : "&nbsp;" ?></td>
						<td><?php echo get_post_meta($order_id, 'Übergabezeit K', true) ?></td>
						<td><?php echo get_post_meta($order_id, 'Übergabe K', true) ?></td>
					</tr>
				</table>
			</div>
		</div>


		<div class="col-4 m60 clear">
			<h5 class="clear">Fahrzeugübergabe an Kunde</h5>
			<table>
				<tr>
					<th style="width: 125px">Übergabe am</th>
					<th style="width: 250px">Übergebender</th>
					<th style="width: 250px">Fahrzeug wie bei Übergabe erhalten</th>
				</tr>
				<tr>
					<td><?php echo get_post_meta($order_id, 'Übergabedatum Ende', true) ? dateFormat(get_post_meta($order_id, 'Übergabedatum Ende', true), 'de') : "&nbsp;" ?></td>
					<?php if($unterschrift_m): ?>
					<td><img src="<?php echo get_home_url() . '/wp-content/uploads/valet-protokolle/u_m-' . get_post_meta($order_id, 'token', true) . ".png" ?>"></td>
					<?php else: ?>
					<td><?php echo "&nbsp;<br>&nbsp;<br>&nbsp;" ?></td>
					<?php endif;?>
					<?php if($unterschrift_k): ?>
					<td><img src="<?php echo get_home_url() . '/wp-content/uploads/valet-protokolle/u_k-' . get_post_meta($order_id, 'token', true) . ".png" ?>"></td>
					<?php else: ?>
					<td><?php echo "&nbsp;<br>&nbsp;<br>&nbsp;" ?></td>
					<?php endif;?>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>Unterschrift Mitarbeiter</td>
					<td>Unterschrift Kunde</td>
				</tr>
			</table>
		</div>
		<?php
		$content = ob_get_clean();
			// instantiate and use the dompdf class
			$options = new Options();
			$options->set('isRemoteEnabled', true);
			$dompdf = new Dompdf($options);
			$dompdf->loadHtml($content);

			// (Optional) Setup the paper size and orientation
			$dompdf->setPaper('A4', 'landscape');

			// Render the HTML as PDF
			$dompdf->render();

			$file = $dompdf->output();
				$fileName = get_post_meta($order_id, 'token', true);
			if(!file_exists(ABSPATH . 'wp-content/uploads/valet-protokolle')){
				mkdir(ABSPATH . 'wp-content/uploads/valet-protokolle');
			}
			$filePath = ABSPATH . 'wp-content/uploads/valet-protokolle/' . $fileName . '.pdf';
			$pdf = fopen($filePath, 'w');
			fwrite($pdf, $file);
			fclose($pdf);
		$headers = array('Content-Type: text/html; charset=UTF-8');
		$body = "<strong>Hallo " . get_post_meta($order_id, '_billing_first_name', true) . " " . get_post_meta($order_id, '_billing_last_name', true) . "</strong><br><br>
				Im Anhang erhalten Sie das Annahmeprotokoll zu Ihrer Parkplatzbuchung.<br><br>
				Wir wünschen Ihnen eine gute Reise.<br><br>
				Viele Grüßen und bis bald.<br>
				Ihr <a href='www.airport-parking-stuttgart.de'>airport-parking-stuttgart.de</a><br><br>
				Tel: +49(0) 711 22 051 245<br>Web: <a href='www.airport-parking-stuttgart.de'>www.airport-parking-stuttgart.de</a><br><br>
				Geschäftsanschrift:<br>
				APS Airport-Parking-Stuttgart GmbH<br>Raiffeisenstrasse 18<br>70794 Filderstadt<br>
				Inhaber: Erdem Aras<br>Steuernummer: 99008/07242<br>";
		wp_mail(get_post_meta($order_id, '_billing_email', true), '[APS] Annahmeprotokoll - ' . $booking['token'], $body, $headers, $filePath);
	}
	
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
						
			<?php if(isset($_GET["updated"])): ?>
				<?php if(empty($_GET['anv']) && empty($_GET['uev'])): ?>
					<?php if(get_post_meta($order_id, '_payment_method_title', true) != 'Barzahlung'): ?>
						<div class="row m60 ui-lotdata-block">
							<h5 class="ui-lotdata-title">Preisänderung</h5>
							<div class="col-sm-12 col-md-12 ui-lotdata">
								<div class="row">
									<div class="col-12">
										<?php if(get_post_meta($order_id, '_order_original_total', true) != null && get_post_meta($order_id, '_order_original_total', true) + $servicePrice < get_post_meta($order_id, '_order_total', true) + $servicePrice): ?>
											<h4>Die Buchung wurde aktualisiert.</h4>
											<p>Durch die Änderung entstehen Mehrkosten der Parkgebühren von <?php echo number_format(get_post_meta($order_id, '_order_total', true) - get_post_meta($order_id, '_order_original_total', true) + $servicePrice,2,".",".") ?> €.</p>
										<?php elseif(get_post_meta($order_id, '_order_original_total', true) != null && get_post_meta($order_id, '_order_original_total', true) > get_post_meta($order_id, '_order_total', true)): ?>
											<h4>Die Buchung wurde aktualisiert.</h4>
											<hp>Durch die Änderung entsteht eine Gutschrift der Parkgebühren von <?php echo number_format(abs(get_post_meta($order_id, '_order_total', true) - get_post_meta($order_id, '_order_original_total', true)) + $servicePrice,2,".",".") ?> €.</p>
										<?php else: ?>
											<h4>Die Buchung wurde aktualisiert.</h4>
											<p>Preislich der Parkgebühren ist keine Änderung entstanden</p>
										<?php endif; ?>
										<?php if(to_float($servicePrice) > 0): ?>
											<p>Die Kosten der Zusatzleistungen belaufen sich auf <?php echo to_float($servicePrice) . " €." ?></p>
										<?php else: ?>
											<p>Die Kosten der Zusatzleistungen belaufen sich auf 0.00 €.</p>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>
				<?php if($roles[0] != 'fahrer'): ?>
				<?php
				if($_GET['dateFrom'] == null || $_GET['dateTo'] == null){
					if($order->get_status() == 'processing'){ 
						if(get_post_meta($order->get_id(), '_order_original_total', true) != null && get_post_meta($order->get_id(), '_order_original_total', true) < get_post_meta($order->get_id(), '_order_total', true)){
							$addPay = number_format(get_post_meta($order->get_id(), '_order_total', true) - get_post_meta($order->get_id(), '_order_original_total', true) + $servicePrice,2,".",".");
							if(get_post_meta($order_id, '_billing_addPay', true) == 1) $ack = "checked"; else $ack = "";
							echo '<div class="row m60">';
								echo '<div class="col-12">';
									echo '<input type="checkbox" id="toPay" name="toPay" value="addPay" ' . $ack . '>';
									echo '<label for="toPay"> Nachzahlung von ' . $addPay . ' € wurde vom Kunden überwiesen</label><br>';
								echo '</div>';
							echo '</div>';
						}
						else if(get_post_meta($order->get_id(), '_order_original_total', true) != null && get_post_meta($order->get_id(), '_order_original_total', true) > get_post_meta($order->get_id(), '_order_total', true)){
							$credit = number_format(abs(get_post_meta($order->get_id(), '_order_total', true) - get_post_meta($order->get_id(), '_order_original_total', true)) + $servicePrice,2,".",".");
							if(get_post_meta($order_id, '_billing_credit', true) == 1) $cck = "checked"; else $cck = "";
							echo '<div class="row m60">';
								echo '<div class="col-12">';
									echo '<input type="checkbox" id="toPay" name="toPay" value="credit" ' . $cck . '>';
									echo '<label for="toPay"> Gutschrift von ' . $credit . ' € wurde an Kunden überwiesen</label><br>';
								echo '</div>';
							echo '</div>';
						}
					}
				}
				?>
				<?php endif; ?>
			<!-- Stornostatus -->
			<?php if($past < 0): ?>
				<div class="row m60">
					<div class="col-12">
						
					</div>
				</div>
			<?php endif; ?>
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
						<!--<div class="col-sm-12 col-md-1 ">
							<label for="">Anrede</label>
							<select name="anrede" class="form-control">
								<option value="Herr" <?php echo get_post_meta($order_id, 'Anrede', true) == "Herr" ? "selected" : "" ?>>Herr</option>
								<option value="Frau" <?php echo get_post_meta($order_id, 'Anrede', true) == "Frau" ? "selected" : "" ?>>Frau</option>
							</select>
						</div>-->
						<?php if(get_post_meta($order_id, '_billing_company', true) != ''): ?>
						<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2 ">
							<label for="firmenname">Firma</label>
							<input type="text" name="firmenname" placeholder="" class="" value="<?php echo get_post_meta($order_id, '_billing_company', true) ?>" <?php echo $disabled; ?>>
						</div>
						<?php endif; ?>
						<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
							<label for="first_name">Vorname</label>
							<input type="text" name="first_name" placeholder="" class="" value="<?php echo get_post_meta($order_id, '_billing_first_name', true) ?>" <?php echo $disabled; ?>>
						</div>
						<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
							<label for="last_name">Nachname</label>
							<input type="text" name="last_name" placeholder="" class="" value="<?php echo get_post_meta($order_id, '_billing_last_name', true) ?>" <?php echo $disabled; ?>>
						</div>
						<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
							<label for="phone_number">Mobilnummer</label>
							<input type="text" name="phone_number" placeholder="" class="" value="<?php echo get_post_meta($order_id, '_billing_phone', true) ?>" <?php echo $disabled; ?>>
						</div>
						<?php if($parklot->is_for != 'hotel'): ?>
						<div class="col-sm-12 col-xs-12 col-md-6 col-lg-2">
							<label for="mail_adress">E-Mail</label>
							<input type="email" name="mail_adress" placeholder="" class="" value="<?php echo get_post_meta($order_id, '_billing_email', true) ?>" <?php echo $disabled; ?>>
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
						<?php if(empty($_GET['anv']) && empty($_GET['uev'])): ?>
						<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
							<label for="booking_date">Buchungstag</label>
							<input type="text" name="booking_date" placeholder="" class="" value="<?php echo $booking['dateCreated'] ?>" readonly="true">
						</div>
						<?php endif; ?>
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
												<tr class="check-row <?php foreach($additionalServicesPrice as $k){if($k->add_ser_id == $service->id) echo "mark_done";} ?>" data-id="<?php echo $service->id; ?>">
													<td>
														<input type="hidden" name="add_ser_id[]" value="<?php foreach($additionalServicesPrice as $k){if($k->add_ser_id == $service->id) echo $k->add_ser_id;} ?>">
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
			<?php if(empty($_GET['anv']) && empty($_GET['uev'])): ?>
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
								<input type="text" name="service" placeholder="" class="" value="<?php echo to_float($servicePrice) ?>" <?php echo $disabled; ?>>
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
			<?php else:?>
			<input type="hidden" name="price" placeholder="" class="" value="<?php echo to_float($priceList) ?>" <?php echo $disabled; ?>>
			<input type="hidden" name="service" placeholder="" class="" value="<?php echo to_float($servicePrice) ?>" <?php echo $disabled; ?>>
			<input type="hidden" name="totalPrice" placeholder="" class="current-price" value="<?php echo to_float($priceList + $servicePrice) ?>" <?php echo $disabled; ?>>
			<?php endif; ?>
			<?php if($lotType == 'valet'): ?>
				<div class="row ui-lotdata-block ui-lotdata-block-next">
					<h5 class="ui-lotdata-title">Fahrzeug Übernahmeprotokoll</h5>
					<div class="col-sm-12 col-md-12 col-lg-12 ui-lotdata">
						<div class="row">												
							<div class="col-sm-12 col-xs-12 col-md-4 col-lg-2">
								<label for="model">Model</label>
								<input type="text" name="model" placeholder="" class="" value="<?php echo $booking['model'] ?>" >
							</div>
							<div class="col-sm-12 col-xs-12 col-md-4 col-lg-2">
								<label for="type">Typ</label>
								<input type="text" name="type" placeholder="" class="" value="<?php echo $booking['type'] ?>" >
							</div>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="color">Farbe</label>
								<input type="text" name="color" placeholder="" class="" value="<?php echo $booking['color'] ?>" >
							</div>
							<div class="col-sm-12 col-xs-12 col-md-4 col-lg-2">
								<label for="kilometerstand">Kilometerstand</label>
								<input type="number" name="kilometerstand" placeholder="" class="" value="<?php echo $booking['kilometerstand'] ?>" >
							</div>
							<div class="col-sm-12 col-xs-12 col-md-5 col-lg-4">
								<label for="tankstand">Tankfüllung</label><br>
								<input type="range" step="10" min="0" max="101" name="tankstand" id="tankstand" value="<?php echo $booking['tankstand'] ? $booking['tankstand'] : '50'?>"  
								oninput="set_tankVal(this.value)" onchange="set_tankVal(this.value)">
								<span id="tankstand_val"><?php echo "  "; echo $booking['tankstand'] ? $booking['tankstand'] : '50';?></span>%
							</div>					
						</div><br>
						<div class="row">
							<div class="col-sm-12 col-md-6">
								<label for="merkmale">Sonstige Merkmale</label><br>
								<textarea name="merkmale" rows="4" cols="50"><?php echo $booking['merkmale'] ?></textarea>
							</div>
						</div><br>
						<div class="row">
							<div class="col-6">
								<label for="">Fahrzeugbilder</label>
								<input type="file" name="car_images[]" accept="image/x-png,image/gif,image/jpeg" multiple>
							</div>
							<div class="col-12 m60 gallery-images">
								<div class="row">
									<?php foreach ($car_images as $image): ?>
										<?php if($image->image_file == null) continue; ?>
										<div class="col-12 col-sm-6 col-md-6 col-lg-3 valet-car-image">
											<span class="del-valet-img"
												  data-id="<?php echo $image->id ?>" data-name="<?php echo $image->image_file ?>">X</span>
											<img style="max-height: 300px; width: auto" src="<?php echo get_home_url() . '/wp-content/uploads/valet-car-images/' . basename($image->image_file) ?>" alt="">
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-6">
								<label for="">Videos</label>
								<input type="file" name="car_videos[]" accept="video/*"
									   multiple>
							</div>
							<div class="col-12 m60 gallery-images">
								<div class="row">
									<?php foreach ($car_videos as $video): ?>
										<?php if($video->video_file == null) continue; ?>
										<div class="col-12 col-sm-6 col-md-6 col-lg-3 valet-car-video">
											<span class="del-valet-vid"
												  data-id="<?php echo $video->id ?>" data-name="<?php echo $video->video_file ?>">X</span>										
											<video class="video-scr" width="300" height="auto" controls>
												<source src="<?php echo get_home_url() . '/wp-content/uploads/valet-car-videos/' . basename($video->video_file) ?>">
											</video>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-6 col-md-6">
						<div class="row ui-lotdata-block ui-lotdata-block-next">
							<h5 class="ui-lotdata-title">Annahme Mitarbeiter</h5>
							<div class="col-sm-12 col-md-12 col-lg-12">
								<div class="row">
									<div class="col-sm-12 col-md-3 col-lg-3">
										<label for="a-m-date">Datum</label>
										<input type="text" name="a-m-date" placeholder="" class="single-datepicker" value="<?php echo $booking['a-m-date'] ? $booking['a-m-date'] : ""; ?>">
									</div>						
									<div class="col-sm-12 col-md-3 col-lg-3">
										<label for="a-m-time">Uhrzeit</label>
										<input type="time" name="a-m-time" placeholder="" class="" value="<?php echo $booking['a-m-time'] ? $booking['a-m-time'] : ""; ?>" >
									</div>
									<div class="col-sm-12 col-md-4 col-lg-4">
										<label for="a-m-user">Mitarbeiter</label>
										<input type="text" name="a-m-user" placeholder="" class="" value="<?php echo $booking['a-m-user'] ? $booking['a-m-user'] : ""; ?>">
									</div>
								</div><br>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-12 col-lg-6">
						<div class="row ui-lotdata-block ui-lotdata-block-next">
							<h5 class="ui-lotdata-title">Abgabe Kunde</h5>
							<div class="col-sm-12 col-md-12 ui-lotdata">
								<div class="row">
									<div class="col-sm-12 col-md-3 col-lg-3">
										<label for="u-kunde-date">Datum</label>
										<input type="text" name="u-kunde-date" placeholder="" class="single-datepicker" value="<?php echo $booking['u-kunde-date'] ? $booking['u-kunde-date'] : ""; ?>">
									</div>						
									<div class="col-sm-12 col-md-3 col-lg-3">
										<label for="u-kunde-time">Uhrzeit</label>
										<input type="time" name="u-kunde-time" placeholder="" class="" value="<?php echo $booking['u-kunde-time'] ? $booking['u-kunde-time'] : ""; ?>">
									</div>
									<div class="col-sm-12 col-md-4 col-lg-4">
										<label for="u-kunde">Kunde</label>
										<input type="text" name="u-kunde" placeholder="" class="" value="<?php echo $booking['u-kunde'] ? $booking['u-kunde'] : ""; ?>" >
									</div>
								</div><br>
							</div>
						</div>
					</div>
				</div>
				
				<div class="row ui-lotdata-block ui-lotdata-block-next">
					<h5 class="ui-lotdata-title">Übergabe an Kunden</h5>
					<div class="col-sm-12 col-md-12 col-lg-12">
						<div class="row">
							<div class="col-sm-12 col-md-12 col-lg-12">
								<label for="u-m-date">Übergabe am</label>
								<input type="text" name="u-date" placeholder="" class="single-datepicker ui-lotdata-date" value="<?php echo $booking['u-date'] ? $booking['u-date'] : ""; ?>" >
							</div>
						</div><br>
						<div class="row">
							<div class="col-sm-12 col-md-6">
								<label for="">Übergebender</label><br>
								<?php if($unterschrift_m): ?>
								<img src="<?php echo get_home_url() . '/wp-content/uploads/valet-protokolle/u_m-' . get_post_meta($order_id, 'token', true) . ".png" ?>">
								<?php else: ?>
								<input type="hidden" name="cm_hidden" id="cm_hidden">
								<canvas style="border:1px solid black;" id="canvas-ma" width="300" height="100"></canvas><br>
								<a class="btn" id="cm_clear" onclick="clear_cm()">löschen</a>
								<?php endif; ?>
								<p>Unterschrift Mitarbeiter</p>
							</div>
							<div class="col-sm-12 col-md-6">
								<label for="">Fahrzeug wie bei Übergabe erhalten</label><br>
								<?php if($unterschrift_k): ?>
								<img src="<?php echo get_home_url() . '/wp-content/uploads/valet-protokolle/u_k-' . get_post_meta($order_id, 'token', true) . ".png" ?>">
								<?php else: ?>
								<input type="hidden" name="ck_hidden" id="ck_hidden">
								<canvas style="border:1px solid black;" id="canvas-k" width="300" height="100"></canvas><br>
								<a class="btn" id="ck_clear" onclick="clear_ck()">löschen</a>
								<?php endif; ?>
								<p>Unterschrift Kunde</p>
							</div>
						</div><br>
					</div>
				</div>
			<?php endif; ?>
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
								<?php if($lotType == 'valet'): ?>
								<div class="col-sm-12 col-md-12 col-lg-12">
									<input type="radio" id="send_protocol_mail" name="send_protocol_mail" value="mail_p" >
									<label for="send_protocol_mail">Übernahmeprotokoll an Kunden senden</label><br>
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
						<?php if($lotType == 'valet' && $_GET['anv'] == 1): ?>
							<div class="col-sm-12 col-md-3 col-lg-2">                    
								<a href="<?php echo '/wp-admin/admin.php?page=anreiseliste-valet' ?>" class="btn btn-secondary d-block w-100" >Schließen</a>
							</div>
						<?php elseif($lotType == 'valet' && $_GET['uev'] == 1): ?>
							<div class="col-sm-12 col-md-3 col-lg-2">                    
								<a href="<?php echo '/wp-admin/admin.php?page=abreiseliste-valet' ?>" class="btn btn-secondary d-block w-100" >Schließen</a>
							</div>
						<?php elseif($_GET['an'] == 1): ?>
							<div class="col-sm-12 col-md-3 col-lg-2">                    
								<a href="<?php echo '/wp-admin/admin.php?page=anreiseliste' ?>" class="btn btn-secondary d-block w-100" >Schließen</a>
							</div>
						<?php elseif($_GET['ue'] == 1): ?>
							<div class="col-sm-12 col-md-3 col-lg-2">                    
								<a href="<?php echo '/wp-admin/admin.php?page=abreiseliste' ?>" class="btn btn-secondary d-block w-100" >Schließen</a>
							</div>
						<?php else: ?>
							<div class="col-sm-12 col-md-3 col-lg-2">                    
								<a href="<?php echo '/wp-admin/admin.php?page=buchung-bearbeiten' ?>" class="btn btn-secondary d-block w-100" >Schließen</a>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<?php if(empty($_GET['anv']) && empty($_GET['uev'])): ?>
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
											case 'addPay':
												echo 'Nachzahlung';
												break;
											case 'credit':
												echo 'Gutschrift';
												break;
											case 'anrede':
												echo 'Anrede';
												break;
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
											case 'model':
												echo 'Fahrzeughersteller';
												break;
											case 'type':
												echo 'Fahrzeugmodell';
												break;
											case 'color':
												echo 'Fahrzeugfarbe';
												break;
											case 'kilometerstand':
												echo 'Kilometerstand';
												break;
											case 'tankstand':
												echo 'Tankstand';
												break;
											case 'merkmale':
												echo 'Merkmale';
												break;
											case 'a-m-date':
												echo 'Annahmedatum MA';
												break;
											case 'a-m-time':
												echo 'Annahmezeit MA';
												break;
											case 'a-m-user':
												echo 'Annahme MA';
												break;
											case 'u-kunde-date':
												echo 'Übergabedatum K';
												break;
											case 'u-kunde-time':
												echo 'Übergabezeit K';
												break;
											case 'u-kunde':
												echo 'Übergabe K';
												break;
											case 'u-date':
												echo 'Übergabedatum Ende';
												break;
											case 'u-unterschrift-ma':
												echo 'Unterschrift MA';
												break;
											case 'u-unterschrift-k':
												echo 'Unterschrift K';
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
											case 'send_protocol_mail':
												echo 'Übergabeprotokoll';
												break;
											case 'cancel':
												echo 'Buchung';
												break;
											case 'refund':
												echo 'Zahlung';
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
											elseif($val->log_key == 'refund')
												echo "erstattet";
											elseif($val->log_key == 'addPay' && $val->log_value == 1)
												echo "Zahlung bestätigt";
											elseif($val->log_key == 'addPay' && $val->log_value == 0)
												echo "Zahlung nicht bestätigt";
											elseif($val->log_key == 'credit' && $val->log_value == 1)
												echo "Zahlung bestätigt";
											elseif($val->log_key == 'credit' && $val->log_value == 0)
												echo "Zahlung nicht bestätigt";
											elseif($val->log_key == 'u-unterschrift-ma')
												echo "unterschrieben";
											elseif($val->log_key == 'u-unterschrift-k')
												echo "unterschrieben";
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


function set_tankVal(e){
	document.getElementById("tankstand_val").innerHTML = e
}

const cm = document.getElementById("canvas-ma");
const ck = document.getElementById("canvas-k");


if(cm){
	cm.addEventListener("mousedown", setLastCoords_m); // fires before mouse left btn is released
	cm.addEventListener("mousemove", freeForm_m);
	cm.addEventListener("mouseleave", setData_m);
	
	const ctx_m = cm.getContext("2d");
	
	function setLastCoords_m(e) {
		const {x, y} = cm.getBoundingClientRect();
		lastX_m = e.clientX - x;
		lastY_m = e.clientY - y;
	}
	
	function freeForm_m(e) {
		if (e.buttons !== 1) return; // left button is not pushed yet
		penTool_m(e);
	}
	
	function penTool_m(e) {
		const {x, y} = cm.getBoundingClientRect();
		const newX = e.clientX - x;
		const newY = e.clientY - y;

		ctx_m.beginPath();
		ctx_m.lineWidth = 2;
		ctx_m.moveTo(lastX_m, lastY_m);
		ctx_m.lineTo(newX, newY);
		ctx_m.strokeStyle = 'black';
		ctx_m.stroke();
		ctx_m.closePath();

		lastX_m = newX;
		lastY_m = newY;
	}
	function setData_m(e) {			
		document.getElementById('cm_hidden').value = cm.toDataURL('image/png');
	}
}

if(ck){    
	ck.addEventListener("mousedown", setLastCoords_k); // fires before mouse left btn is released
	ck.addEventListener("mousemove", freeForm_k);
	ck.addEventListener("mouseleave", setData_k);
	
	const ctx_k = ck.getContext("2d");
 
	function setLastCoords_k(e) {
		const {x, y} = ck.getBoundingClientRect();
		lastX_k = e.clientX - x;
		lastY_k = e.clientY - y;
	}
  
	function freeForm_k(e) {
		if (e.buttons !== 1) return; // left button is not pushed yet
		penTool_k(e);
	}
	
	function penTool_k(e) {
		const {x, y} = ck.getBoundingClientRect();
		const newX = e.clientX - x;
		const newY = e.clientY - y;

		ctx_k.beginPath();
		ctx_k.lineWidth = 2;
		ctx_k.moveTo(lastX_k, lastY_k);
		ctx_k.lineTo(newX, newY);
		ctx_k.strokeStyle = 'black';
		ctx_k.stroke();
		ctx_k.closePath();

		lastX_k = newX;
		lastY_k = newY;
	}
	
	function setData_k(e) {
		document.getElementById('ck_hidden').value = ck.toDataURL('image/png');
	}
}

let lastX_m = 0;
let lastY_m = 0;
let lastX_k = 0;
let lastY_k = 0;
	
function clear_cm(){
	const cm = document.getElementById("canvas-ma");
	ctx = cm.getContext("2d");
	ctx.clearRect(0, 0, cm.width, cm.height);
}

function clear_ck(){
	const ck = document.getElementById("canvas-k");
	ctx = ck.getContext("2d");
	ctx.clearRect(0, 0, ck.width, ck.height);
}
</script>

<script>
(function() {
	
	// Get a regular interval for drawing to the screen
	window.requestAnimFrame = (function (callback) {
		return window.requestAnimationFrame || 
					window.webkitRequestAnimationFrame ||
					window.mozRequestAnimationFrame ||
					window.oRequestAnimationFrame ||
					window.msRequestAnimaitonFrame ||
					function (callback) {
						window.setTimeout(callback, 1000/60);
					};
	})();

const cmt = document.getElementById("canvas-ma");
const cm_clear = document.getElementById("cm_clear");
const ckt = document.getElementById("canvas-k");
const ck_clear = document.getElementById("ck_clear");
if(cmt){
	// Set up the canvas
	var ctx_mt = cmt.getContext("2d");
	ctx_mt.strokeStyle = "#222222";
	ctx_mt.lineWith = 2;

	// Set up mouse events for drawing
	var drawing_mt = false;
	var mousePos_mt = { x:0, y:0 };
	var lastPos_mt = mousePos_mt;
	cmt.addEventListener("mousedown", function (e) {
		drawing_mt = true;
		lastPos_mt = getMousePos_mt(cmt, e);
	}, false);
	cmt.addEventListener("mouseup", function (e) {
		drawing_mt = false;
	}, false);
	cmt.addEventListener("mousemove", function (e) {
		mousePos_mt = getMousePos_mt(cmt, e);
		document.getElementById('cm_hidden').value = cmt.toDataURL('image/png');
	}, false);

	// Set up touch events for mobile, etc
	cmt.addEventListener("touchstart", function (e) {
		mousePos_mt = getTouchPos_mt(cmt, e);
		var touch_mt = e.touches[0];
		var mouseEvent_mt = new MouseEvent("mousedown", {
			clientX: touch_mt.clientX,
			clientY: touch_mt.clientY
		});
		cmt.dispatchEvent(mouseEvent_mt);
		disableScroll();
	}, false);
	cmt.addEventListener("touchend", function (e) {
		var mouseEvent_mt = new MouseEvent("mouseup", {});
		document.getElementById('cm_hidden').value = cmt.toDataURL('image/png');
		cmt.dispatchEvent(mouseEvent_mt);
		enableScroll();
	}, false);
	cmt.addEventListener("touchmove", function (e) {
		var touch_mt = e.touches[0];
		var mouseEvent_mt = new MouseEvent("mousemove", {
			clientX: touch_mt.clientX,
			clientY: touch_mt.clientY
		});
		//disableScroll();
		cmt.dispatchEvent(mouseEvent_mt);
	}, false);
	
	cm_clear.addEventListener("click", function (e) {
		cmt.width = cmt.width;
		ctx_mt.clearRect(0, 0, cmt.width, cmt.height);
	}, false);
	
	// Prevent scrolling when touching the canvas
		document.body.addEventListener("touchstart", function (e) {
			if (e.target == cmt) {
				e.preventDefault();
			}
		}, false);
		document.body.addEventListener("touchend", function (e) {
			if (e.target == cmt) {
				e.preventDefault();
			}
		}, false);
		document.body.addEventListener("touchmove", function (e) {
			if (e.target == cmt) {
				e.preventDefault();
			}
	}, false);
}

if(ckt){
	// Set up the canvas
	var ctx_kt = ckt.getContext("2d");
	ctx_kt.strokeStyle = "#222222";
	ctx_kt.lineWith = 2;

	// Set up mouse events for drawing
	var drawing_kt = false;
	var mousePos_kt = { x:0, y:0 };
	var lastPos_kt = mousePos_kt;
	ckt.addEventListener("mousedown", function (e) {
		drawing_kt = true;
		lastPos_kt = getMousePos_kt(ckt, e);
	}, false);
	ckt.addEventListener("mouseup", function (e) {
		drawing_kt = false;
		
	}, false);
	ckt.addEventListener("mousemove", function (e) {
		mousePos_kt = getMousePos_kt(ckt, e);
	}, false);

	// Set up touch events for mobile, etc
	ckt.addEventListener("touchstart", function (e) {
		mousePos_kt = getTouchPos_kt(ckt, e);
		var touch_kt = e.touches[0];
		var mouseEvent_kt = new MouseEvent("mousedown", {
			clientX: touch_kt.clientX,
			clientY: touch_kt.clientY
		});
		ckt.dispatchEvent(mouseEvent_kt);
		disableScroll();
	}, false);
	ckt.addEventListener("touchend", function (e) {
		var mouseEvent_kt = new MouseEvent("mouseup", {});
		document.getElementById('ck_hidden').value = ckt.toDataURL('image/png');
		ckt.dispatchEvent(mouseEvent_kt);
		enableScroll();
	}, false);
	ckt.addEventListener("touchmove", function (e) {
		var touch_kt = e.touches[0];
		var mouseEvent_kt = new MouseEvent("mousemove", {
			clientX: touch_kt.clientX,
			clientY: touch_kt.clientY
		});
		//disableScroll();
		ckt.dispatchEvent(mouseEvent_kt);
	}, false);
	
	ck_clear.addEventListener("click", function (e) {
		ckt.width = ckt.width;
		ctx_kt.clearRect(0, 0, ckt.width, ckt.height);
	}, false);
	
	// Prevent scrolling when touching the canvas
		document.body.addEventListener("touchstart", function (e) {
			if (e.target == ckt) {
				e.preventDefault();
			}
		}, false);
		document.body.addEventListener("touchend", function (e) {
			if (e.target == ckt) {
				e.preventDefault();
			}
		}, false);
		document.body.addEventListener("touchmove", function (e) {
			if (e.target == ckt) {
				e.preventDefault();
			}
	}, false);
}	



	// Get the position of the mouse relative to the canvas
	function getMousePos_mt(canvasDom, mouseEvent) {
		var rect_mt = canvasDom.getBoundingClientRect();
		return {
			x: mouseEvent.clientX - rect_mt.left,
			y: mouseEvent.clientY - rect_mt.top
		};
	}
	function getMousePos_kt(canvasDom, mouseEvent) {
		var rect_kt = canvasDom.getBoundingClientRect();
		return {
			x: mouseEvent.clientX - rect_kt.left,
			y: mouseEvent.clientY - rect_kt.top
		};
	}

	// Get the position of a touch relative to the canvas
	function getTouchPos_mt(canvasDom, touchEvent_mt) {
		var rect_mt = canvasDom.getBoundingClientRect();
		return {
			x: touchEvent_mt.touches[0].clientX - rect_mt.left,
			y: touchEvent_mt.touches[0].clientY - rect_mt.top
		};
	}
	function getTouchPos_kt(canvasDom, touchEvent_kt) {
		var rect_kt = canvasDom.getBoundingClientRect();
		return {
			x: touchEvent_kt.touches[0].clientX - rect_kt.left,
			y: touchEvent_kt.touches[0].clientY - rect_kt.top
		};
	}

	// Draw to the canvas
	function renderCanvas_mt() {
		if (drawing_mt) {
			ctx_mt.moveTo(lastPos_mt.x, lastPos_mt.y);
			ctx_mt.lineTo(mousePos_mt.x, mousePos_mt.y);
			ctx_mt.stroke();
			lastPos_mt = mousePos_mt;
			
		}
	}
	function renderCanvas_kt() {
		if (drawing_kt) {
			ctx_kt.moveTo(lastPos_kt.x, lastPos_kt.y);
			ctx_kt.lineTo(mousePos_kt.x, mousePos_kt.y);
			ctx_kt.stroke();
			lastPos_kt = mousePos_kt;
		}
	}
	
	function preventDefault(e) {
	  e.preventDefault();
	}

	// modern Chrome requires { passive: false } when adding event
	var supportsPassive = false;
	try {
	  window.addEventListener("test", null, Object.defineProperty({}, 'passive', {
		get: function () { supportsPassive = true; } 
	  }));
	} catch(e) {}

	var wheelOpt = supportsPassive ? { passive: false } : false;
	var wheelEvent = 'onwheel' in document.createElement('div') ? 'wheel' : 'mousewheel';


	function disableScroll() {
	  window.addEventListener('DOMMouseScroll', preventDefault, false); // older FF
	  window.addEventListener(wheelEvent, preventDefault, wheelOpt); // modern desktop
	  window.addEventListener('touchmove', preventDefault, wheelOpt); // mobile
	  window.addEventListener('keydown', preventDefaultForScrollKeys, false);
	}

	function enableScroll() {
	  window.removeEventListener('DOMMouseScroll', preventDefault, false);
	  window.removeEventListener(wheelEvent, preventDefault, wheelOpt); 
	  window.removeEventListener('touchmove', preventDefault, wheelOpt);
	  window.removeEventListener('keydown', preventDefaultForScrollKeys, false);
	}

	// Allow for animation
	(function drawLoop () {
		requestAnimFrame(drawLoop);
		renderCanvas_mt();
		renderCanvas_kt();
	})();

})();
	
</script>


