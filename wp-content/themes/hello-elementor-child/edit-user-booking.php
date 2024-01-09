<?php
if(isset($_SERVER['HTTPS'])){
	$protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
}
else{
	$protocol = 'http';
}
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'];

unset($_SESSION["token"]); 
unset($_SESSION["price"]);
unset($_SESSION['order_time_from']);
unset($_SESSION['order_time_to']);
unset($_SESSION['tage']);
unset($_SESSION['order_id']);
	
global $wpdb;
$user_id = get_current_user_id();
$args = array(
    'customer_id' => $user_id,
    'limit' => -1, // to retrieve _all_ orders by this user
);
$orders = wc_get_orders($args);

if(isset($_GET["arr"]) && isset($_GET["ret"])){
	$c_dateFrom = date('Y-m-d', strtotime($_GET["arr"]));
	$c_dateTo = date('Y-m-d', strtotime($_GET["ret"]));
	
	if ($c_dateTo <= $c_dateFrom) {
		die("Abreisedatum ist kleiner als Anreisedatum. <button onclick='history.go(-1);'>Zurück</button>");
	}
}

$checkBooking = "";
foreach ($orders as $order){
	if($_GET['edit_booking'] == $order->get_id())
		$checkBooking = "ok";
}

if($checkBooking != ''){
	$order_id = $_GET['edit_booking'];
	$woo_order = wc_get_order($order_id);
	
	$sql = "SELECT @num := @num + 1 AS position,
		p.ID AS order_id,
		DATE(p.post_date) AS date_created,
		p.post_status AS Status,
		o.datezfrom AS datefrom, o.dateto AS dateto,
		pl.parklotname AS parklotname,
		pl.id AS lot_id,
		pl.operator_id,
		o.proid,
		type.type,
		MAX(CASE WHEN pm.meta_key = 'token' THEN pm.meta_value END) AS Token,
		MAX(CASE WHEN pm.meta_key = '_order_total' THEN pm.meta_value END) AS Betrag,
		MAX(CASE WHEN pm.meta_key = '_payment_method_title' THEN pm.meta_value END) AS Bezahlmethode
					
		FROM {$wpdb->prefix}posts p
		JOIN (SELECT @num := 0 FROM DUAL) AS n ON 1=1
		LEFT JOIN {$wpdb->prefix}postmeta pm ON pm.post_id = p.ID
		INNER JOIN {$wpdb->prefix}itweb_orders o ON o.order_id = p.ID
		INNER JOIN {$wpdb->prefix}itweb_parklots pl ON pl.id = o.parklot_id
		INNER JOIN {$wpdb->prefix}users user ON user.ID = pl.operator_id
		INNER JOIN {$wpdb->prefix}itweb_types type ON type.id = pl.type_id
		WHERE
		p.post_type = 'shop_order' AND o.order_id = ".$order_id."			
		GROUP BY p.ID 
		ORDER BY position DESC, date_created DESC
		";
	$booking = $wpdb->get_row($sql);
	
	$bookingDate = date_create($booking->date_created);
	$bookingDate = date_format($bookingDate, 'Y-m-d');
	$datefrom = date_create($booking->datefrom);
	$timein = date_format($datefrom, 'H:i');
	$datefrom = date_format($datefrom, 'd.m.Y');
	$dateto = date_create($booking->dateto);
	$timeback = date_format($dateto, 'H:i');
	$dateto = date_format($dateto, 'd.m.Y');
	
	$pastDateFrom = getDaysBetween2Dates(new DateTime($datefrom), new DateTime(date("Y-m-d")), $a = false) - 1;
	if($pastDateFrom < 0)
		$disabled = 'readonly="true"';
	else
		$disabled = "";
	
	$pastDateTo = getDaysBetween2Dates(new DateTime($dateto), new DateTime(date("Y-m-d")), $a = false) - 1;
	if($pastDateTo < 0)
		$disabledDesDate = 'readonly="true"';
	else
		$disabledDesDate = "";
	
	$isShuttle = !empty(get_post_meta($order_id, '_persons_nr', true));
	
	if(isset($_GET["arr"]) && isset($_GET["ret"])){
		$newCosts = getParklotPrice($booking->proid, $_GET["arr"], $_GET["ret"]);
		
		if($newCosts == null || $newCosts == "")
			die("Bei der Ermittlung des Preises ist ein Fehler aufgetreten. Bitte wenden Sie sich an den Kundenservice. <button onclick='history.go(-1);'>Zurück</button>");
		elseif($newCosts == '0.00')
			die("Parkplätze mit dem gewählten Datum nicht vorhanden. <button onclick='history.go(-1);'>Zurück</button>");
	}
	else
		$newCosts = $booking->Betrag;
	
	
	if(isset($_POST["update"])){
		
		$orig_tax = get_post_meta($order_id, '_order_tax', true);
		$orig_price = get_post_meta($order_id, '_order_total', true);
		
		foreach($_POST as $key => $val){
			$update_data[$key] = $val;
		}
		
		if(isset($_POST["toPay"]) && $_POST["toPay"] == "addPay")
			$orig_data['toPay'] = "addPay";
		
		$orig_data['company'] = get_post_meta($order_id, '_billing_company', true);
		$orig_data['first_name'] = get_post_meta($order_id, '_billing_first_name', true);
		$orig_data['last_name'] = get_post_meta($order_id, '_billing_last_name', true);
		$orig_data['phone_number'] = get_post_meta($order_id, '_billing_phone', true);
		$orig_data['mail_adress'] = get_post_meta($order_id, '_billing_email', true);
		$orig_data['lot_name'] = $_POST["lot_name"];
		$orig_data['booking_date'] = $_POST["booking_date"];
		$orig_data['booking_token'] = $_POST["booking_token"];
		$orig_data['start-date'] = $datefrom;
		$orig_data['ar_time'] = $timein;
		$orig_data['end-date'] = $dateto;
		$orig_data['de_time'] = $timeback;
		$orig_data['count_person'] = get_post_meta($order_id, '_persons_nr', true);
		$orig_data['flight_departure'] = get_post_meta($order_id, '_hinflug', true);
		$orig_data['flight_outbound'] = get_post_meta($order_id, '_ruckflug', true);
		$orig_data['license_plate'] = get_post_meta($order_id, '_kfz_nr', true);
		$orig_data['parking_days'] = $_POST["parking_days"];
		$orig_data['costs_costs'] = $_POST["costs_costs"];
		$orig_data['parking_update'] = number_format($gross,2,".",".");
		//if(isset($_POST["send_mail"]))
		$orig_data['send_mail'] = "mail";
		$orig_data['update'] = 1;
		
		if($update_data['start-date'] != $orig_data['start-date'] || $update_data['end-date'] != $orig_data['end-date']){
			if(get_post_meta($order_id, '_billing_addPay', true) != null)
				delete_post_meta($order_id, '_billing_addPay' );
		}
		
		//if(isset($_POST["send_mail"])){
			$wpdb->insert($wpdb->prefix . "itweb_edit_booking_log", [
					'order_id' => $order_id,
					'user_id' => $user,
					'date' => $log_date,
					'key' => 'sent_mail',
					'value' => 1
			]);
		//}
		
		if(isset($_POST["toPay"]) && $_POST["toPay"] == "addPay" && get_post_meta($order_id, '_billing_addPay', true) == 0){
			$wpdb->insert($wpdb->prefix . "itweb_edit_booking_log", [
					'order_id' => $order_id,
					'user_id' => $user,
					'date' => $log_date,
					'key' => 'addPay',
					'value' => 1
			]);
			update_post_meta($order_id, '_billing_addPay', 1);
		}
		elseif($_POST["toPay"] == null && get_post_meta($order_id, '_billing_addPay', true) == 1){
			$wpdb->insert($wpdb->prefix . "itweb_edit_booking_log", [
					'order_id' => $order_id,
					'user_id' => $user,
					'date' => $log_date,
					'key' => 'addPay',
					'value' => 0
			]);	
			update_post_meta($order_id, '_billing_addPay', 0);
		}
		
		$intern_token = get_post_meta($order_id, 'token', true);
		// Set first booking price
		if(get_post_meta($order_id, '_order_original_total', true) == null){
			update_post_meta($order_id, '_order_original_tax', $orig_tax);
			update_post_meta($order_id, '_order_original_total', $orig_price);
		}
		$fEDate = date_create($_POST["end-date-user"]);
		$fEDate = date_format($fEDate, 'Y-m-d');
		$fSDate = date_create($_POST["start-date-user"]);
		$fSDate = date_format($fSDate, 'Y-m-d');
		$datefrom_sql = $fSDate . " " . $_POST["ar_time"] . ":00";
		$wpdb->update($wpdb->prefix . "itweb_orders", array( 'datefrom' => $datefrom_sql),array('order_id'=>$order_id));
		$dateto_sql = $fEDate . " " . $_POST["de_time"] . ":00";
		$wpdb->update($wpdb->prefix . "itweb_orders", array( 'dateto' => $dateto_sql),array('order_id'=>$order_id));
		
		// Update order/produce meta
		update_post_meta($order_id, '_billing_company', $_POST["company"]);
		update_post_meta($order_id, '_billing_first_name', $_POST["first_name"]);
		update_post_meta($order_id, '_billing_last_name', $_POST["last_name"]);
		update_post_meta($order_id, '_billing_phone', $_POST["phone_number"]);
		update_post_meta($order_id, '_billing_email', $_POST["mail_adress"]);	
		if($isShuttle)
			update_post_meta($order_id, '_persons_nr', $_POST["count_person"]);	
		update_post_meta($order_id, '_hinflug', $_POST["flight_departure"]);
		update_post_meta($order_id, '_ruckflug', $_POST["flight_outbound"]);
		update_post_meta($order_id, '_kfz_nr', $_POST["license_plate"]);
		update_post_meta($order_id, '_order_time_from', $_POST["ar_time"]);
		update_post_meta($order_id, '_order_time_to', $_POST["de_time"]);
		

		if($woo_order->get_status() == 'processing' || $woo_order->get_status() == 'on-hold'){ 
			if(get_post_meta($woo_order->get_id(), '_order_original_total', true) != null && get_post_meta($woo_order->get_id(), '_order_original_total', true) < get_post_meta($woo_order->get_id(), '_order_total', true)){
				if(get_post_meta($order_id, '_billing_addPay', true) == null)
					update_post_meta($order_id, '_billing_addPay', 0);
			}

			else if(get_post_meta($woo_order->get_id(), '_order_original_total', true) != null && get_post_meta($woo_order->get_id(), '_order_original_total', true) == get_post_meta($woo_order->get_id(), '_order_total', true)){
				if(get_post_meta($order_id, '_billing_addPay', true) != null)
					delete_post_meta($order_id, '_billing_addPay' );
				
			}
		}
		
		$woo_order = wc_get_order($order_id);
		$order_items = $woo_order->get_items();
		foreach ( $order_items as $key => $value ) {
			$order_item_id = $key;
			   $product_value = $value->get_data();
			   $product_id    = $product_value['product_id']; 
		}
		
		$product = wc_get_product( $product_id );
		$price = number_format((float)$newCosts / 119 * 100,4, '.', '');
		$order_items[ $order_item_id ]->set_total( $price );
		$woo_order->calculate_taxes();
		$woo_order->calculate_totals();
		$woo_order->save();
		
		/// Create CSV from booking
		$tab = "\t";
			
		$days = getDaysBetween2Dates(
				new DateTime($fSDate),
				new DateTime($fEDate));
		$timeTo = date('H:i:s', strtotime($_POST["ar_time"]));
		$timeFrom = date('H:i:s', strtotime($_POST["de_time"]));
		
		if($booking->operator_id == 7 || $booking->operator_id == 561 || $booking->operator_id == 594){
			$csv = $booking->operator_id . $tab . "-" . $tab . $_POST["first_name"] . $tab . $_POST["last_name"] . $tab . $_POST["phone_number"] . $tab . 
			$_POST["mail_adress"] . $tab . get_post_meta($order_id, 'token', true) . $tab . $fSDate . $tab . $timeTo . $tab . $fEDate . $tab .
			$timeFrom . $tab . $days . $tab . $_POST["count_person"] . $tab . $_POST["license_plate"] . $tab .  
			$_POST["flight_departure"] . $tab . $_POST["flight_outbound"] . $tab . $_POST['parking_update'] . $tab . "*AMED*"; 
			$fileName = $intern_token;
		}
		else{
			$csv = $booking->operator_id . $tab . "-" . $tab . $_POST["first_name"] . $tab . $_POST["last_name"] . $tab . $_POST["phone_number"] . $tab . 
			get_post_meta($order_id, 'token', true) . $tab . $fSDate . $tab . $timeTo . $tab . $fEDate . $tab .
			$timeFrom . $tab . $days . $tab . $_POST["count_person"] . $tab . $_POST["license_plate"] . $tab .  
			$_POST["flight_departure"] . $tab . $_POST["flight_outbound"] . $tab . $_POST['parking_update'] . $tab . "*AMED*"; 
			$fileName = $intern_token;
		}

			if(!file_exists(ABSPATH . 'wp-content/themes/IT_Web24_Theme/export/' . $parklot_id)){
				mkdir(ABSPATH . 'wp-content/themes/IT_Web24_Theme/export/' . $parklot_id);
			}
			
		$filePath = ABSPATH . 'wp-content/themes/IT_Web24_Theme/export/'. $parklot_id .'/'. $fileName . '.csv';
		$file = fopen($filePath, 'w');
		fwrite($file, $csv);
		fclose($file);
		
		//if(isset($_POST["send_mail"])){
			$mailer = WC()->mailer();
			$mails = $mailer->get_emails();
			if ( ! empty( $mails ) ) {                
				foreach ( $mails as $mail ) {
					if ( $mail->id == 'customer_processing_order' || $mail->id == 'customer_on_hold_order' ){
						$mail->trigger( $order_id );                    
					}
				}            
			}
			WC()->mailer()->get_emails()['WC_Email_New_Order']->trigger( $order_id );
		//}
		
		// Save Booking to APM
		/////////////////////
		if($booking->operator_id == 7){
			if($externCode == null || $externCode == ''){
				$url = "https://airport-parking-stuttgart.de?request=apm&pw=apg_req54894135&update=1";
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS,
				http_build_query($_POST));
				// Receive server response ...
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$server_output = curl_exec($ch);
				curl_close($ch);
			}
		}
		if($booking->operator_id == 561 || $booking->operator_id == 594){
			if($externCode == null || $externCode == ''){
				$url = "https://airport-parking-frankfurt.com?request=apf&pw=apg_req54894135&update=1";
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS,
				http_build_query($_POST));
				// Receive server response ...
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$server_output = curl_exec($ch);
				curl_close($ch);
			}
		}
		/////////////////////
		
		// Sende Mail bei Preisänderung
		$bCode = ($externCode) ? $externCode : get_post_meta($order_id, 'token', true);
			
		if(get_post_meta($order_id, '_order_original_total', true) != null && get_post_meta($order_id, '_order_original_total', true) < get_post_meta($order_id, '_order_total', true))
			$body = "Buchung " . $bCode . " wurde geändert.<br>
			Preis ursprünglich: ".get_post_meta($order_id, '_order_original_total', true)."€<br>
			Enderung: " . number_format(get_post_meta($order_id, '_order_total', true) - get_post_meta($order_id, '_order_original_total', true),2,".",".") . " €.<br>
			Gesamtpreis: " . get_post_meta($order_id, '_order_total', true)." €.<br>";
		/*
		elseif(get_post_meta($order_id, '_order_original_total', true) != null && get_post_meta($order_id, '_order_original_total', true) > get_post_meta($order_id, '_order_total', true))
			$body = "Buchung " . $bCode . " wurde geändert.<br>
			Durch die Änderung entsteht eine Gutschrift von " . number_format(abs(get_post_meta($order_id, '_order_total', true) - get_post_meta($order_id, '_order_original_total', true)),2,".",".") . " €.";
		*/
		else
			$body = null;
		if($body){
			$headers = array('Content-Type: text/html; charset=UTF-8');
			$subject = "[APG] Kundenbuchung " . $bCode . " wurde geändert";
			wp_mail( 'noreply@a-p-germany.de', $subject, $body, $headers );
		}
		
		//////////////////////
		
		unset($_SESSION["extern"]);
		unset($_SESSION['showEdit']);
		echo("<script>location.href = '/buchungen/';</script>");
	}
}

//echo "<pre>"; print_r($booking); echo "</pre>";
?>

<style>
.itweb_adminpage_head{
	background-color: #3b8ae3;
	color: white;
}
</style>

<script src="/wp-content/plugins/itweb-parking-booking/bootstrap-4.5.3-dist/js/popper.min.js"></script>
<script src="/wp-content/plugins/itweb-parking-booking/bootstrap-4.5.3-dist/js/bootstrap.min.js"></script>

<?php if($checkBooking == ''): ?>
<div class="body-cover">
    <div class="container">
		<div class="row">
			<div class="col-sm-12 col-md-12 col-lg-12">
				<p>Buchung nicht gefunden.</p>
			</div>
		</div>
		<br>
	</div>
</div>
<?php else: ?>

<div class="body-cover">
    <div class="container">
		<div class="row">
			<div class="col-sm-12 col-md-2 col-lg-2">
				<a class="btn btn-primary" href="/kundenkonto/">Kundenkonto</a>
			</div>
			<div class="col-sm-12 col-md-2 col-lg-2">
				<a class="btn btn-primary" href="/search-result/?country=&start-date=<?php echo $startDate->format('d.m.Y'); ?>&end-date=<?php echo $endDate->format('d.m.Y'); ?>" target="_blank">Jetzt buchen</a>
			</div>
		</div>
		<br>
		<form class="update-price" action="<?php echo basename($_SERVER['REQUEST_URI']); ?>" method="POST">
			
			<?php
			if(isset($_GET["ret"])){
				$startDateForm = date_create($_GET['arr']);
				$checkoutDateFrom = date_format($startDateForm, 'Y-m-d');
				$startDateForm = date_format($startDateForm, 'd.m.Y');
				$endDateForm = date_create($_GET['ret']);
				$checkoutDateTo =  date_format($endDateForm, 'Y-m-d');
				$endDateForm = date_format($endDateForm, 'd.m.Y');
			}
			?>
			
			<div class="row">
				<div class="col-sm-12 col-md-12 col-lg-12 itweb_adminpage_head">
					<h3> Buchung bearbeiten - <?php echo get_post_meta($order_id, 'token', true); ?></h3>
				</div>
				<div class="col-sm-12 col-md-12 col-lg-12">
					
					<?php if(isset($_GET["ret"])): ?>
						<?php
						$checkNewStart = date_create($_GET["arr"]);
						$checkNewStart = date_format($checkNewStart, 'Y-m-d');
						$checkNewEnd = date_create($_GET["ret"]);
						$checkNewEnd = date_format($checkNewEnd, 'Y-m-d');
						$days = getDaysBetween2Dates(new DateTime($checkNewStart), new DateTime($checkNewEnd)) - getDaysBetween2Dates(new DateTime($datefrom), new DateTime($dateto));			
						?>
						<?php if($days > 0 && date('Y-m-d', strtotime($_GET["arr"])) < date('Y-m-d')): ?>
							<h4 class="editBooking-cancelError">Durch die Verlängerung der Parkdauer entstehen Mehrkosten von <?php echo $days * 10 ?>.00€, die bei Anreise bar bezahlt werden müssen.</h4>
						<?php endif; ?>
					<?php endif; ?>
					
					<h3 class="itweb_add_head">Kunden Details</h3>
					<table>
						<tr>
							<td><label for="company">Firma</label><br> <!-- -->
							<input type="text" class="form-control" name="company" value="<?php echo get_post_meta($order_id, '_billing_company', true) ?>" <?php echo $disabled; ?>></td>
							<td><label for="first_name">Vorname</label><br> <!-- -->
							<input type="text" class="form-control" name="first_name" value="<?php echo get_post_meta($order_id, '_billing_first_name', true) ?>" <?php echo $disabled; ?>></td>
							<td><label for="last_name">Nachname</label><br> <!-- -->
							<input type="text" class="form-control" name="last_name" value="<?php echo get_post_meta($order_id, '_billing_last_name', true) ?>" <?php echo $disabled; ?>></td>
							<td><label for="phone_number">Mobilnummer</label><br> <!-- -->
							<input type="text" class="form-control" name="phone_number" value="<?php echo get_post_meta($order_id, '_billing_phone', true) ?>" <?php echo $disabled; ?>></td>
							<td><label for="mail_adress">E-Mail</label><br> <!-- -->
							<input type="email" class="form-control" name="mail_adress" size="30" value="<?php echo get_post_meta($order_id, '_billing_email', true) ?>" required <?php echo $disabled; ?>></td>
						</tr>
					</table>
					<hr>	
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12 col-md-12 col-lg-12">
					<h3 class="itweb_add_head">Buchungsinformationen</h3>
					<table>
						<tr>
							<td><label for="lot_name">Parkplatz</label><br>
							<input type="text" class="form-control" name="lot_name" size="30" value="<?php echo $booking->parklotname ?>" readonly="true"></td>
							<td><label for="booking_date">Buchungstag</label><br>
							<input type="date" class="form-control" name="booking_date" value="<?php echo date( $booking->date_created) ?>" readonly="true"></td>
							<td><label for="booking_token">Buchungsnummer</label><br>
							<input type="text" class="form-control" name="booking_token" size="10" value="<?php echo ($externCode) ? $externCode : $booking->Token;?>" readonly="true">
						</tr>
						<tr>
							<input type="hidden" class="form-control" name="start-date" value="<?php if(isset($_GET["arr"])) echo date($startDateForm); else echo date($datefrom) ?>">					
							
							<td><label for="start-date">Anreisedatum</label><br> <!-- -->
							<input type="text" class="form-control" name="start-date-user" style="<?php echo $disabled != '' ? 'pointer-events: none;' : '' ?>" class="edit-start-date-user" id="edit-start-date-user" value="<?php if(isset($_GET["arr"])) echo date($startDateForm); else echo date($datefrom) ?>" required <?php echo $disabled; ?>></td>
							<td><label for="ar_time">Genaue Anreisezeit</label><br> <!-- -->
							<input type="time" class="form-control" name="ar_time" style="<?php echo $disabled != '' ? 'pointer-events: none;' : '' ?>" class="edit-ar-time" value="<?php echo $timein ?>" required <?php echo $disabled; ?>></td>
							<td><label for="flight_departure">Flugnummer Hinflug</label><br> <!-- -->
							<input type="text" class="form-control" name="flight_departure" value="<?php echo get_post_meta($order_id, '_hinflug', true) ?>" required <?php echo $disabled; ?>></td>
							<?php if($isShuttle): ?>
								<td><label for="count_person">Anzahl Reisende</label><br> <!-- -->
								<input type="number" size="5" min="1" max="99" name="count_person" value="<?php echo get_post_meta($order_id, '_persons_nr', true) ?>" required <?php echo $disabled; ?>></td>
							<?php endif; ?>
						</tr>
						<tr>
							<input type="text" class="form-control" name="end-date" value="<?php if(isset($_GET["ret"])) echo date($endDateForm); else echo date($dateto) ?>">
							
							<td><label for="end-date">Abreisedatum</label><br> <!-- -->
							<input type="text" class="form-control" name="end-date-user" style="<?php echo $disabledDesDate != '' ? 'pointer-events: none;' : '' ?>" class="edit-end-date-user" id="edit-end-date-user" value="<?php if(isset($_GET["ret"])) echo date($endDateForm); else echo date($dateto) ?>" required <?php echo $disabledDesDate; ?>></td>
							<td><label for="de_time">Genaue Abreisezeit</label><br> <!-- -->
							<input type="time" class="form-control" name="de_time" style="<?php echo $disabledDesDate != '' ? 'pointer-events: none;' : '' ?>" class="edit-de-time" value="<?php echo $timeback ?>" required <?php echo $disabledDesDate; ?>></td>
							<td><label for="flight_outbound">Flugnummer Rückflug</label><br> <!-- -->
							<input type="text" class="form-control" name="flight_outbound" value="<?php echo get_post_meta($order_id, '_ruckflug', true) ?>" required <?php echo $disabledDesDate; ?>></td>
							<td><label for="license_plate">KFZ-Kennzeichen</label><br> <!-- -->
							<input type="text" class="form-control" name="license_plate" value="<?php echo get_post_meta($order_id, '_kfz_nr', true) ?>" required <?php echo $disabled; ?>></td>
						</tr>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12 col-md-12 col-lg-12">
					<h3 class="itweb_add_head">Parkgebühren</h3>
					<table>
						<tr>
							<?php 
								if(isset($_GET["arr"]) && isset($_GET["ret"])){
									$newDays = getDaysBetween2Dates(new DateTime($_GET["arr"]), new DateTime($_GET["ret"]));
									$newDiffDays = $newDays - getDaysBetween2Dates(new DateTime($datefrom), new DateTime($dateto));
								}
								else{
									$newDiffDays = 0;
								}
							?>
							<td><label for="parking_days">Parkdauer</label><br>
							<input type="text" size="5" min="1" name="parking_days" value="<?php echo getDaysBetween2Dates(new DateTime($datefrom), new DateTime($dateto)); ?>" readonly="true"></td>
							<?php if(isset($_GET["arr"]) && isset($_GET["ret"])): ?>
							<td><label for="costs_costs_old">Parkkosten in €</label><br>
							<input type="text" size="10" name="costs_costs_old" value="<?php echo number_format($booking->Betrag,2,".","."); ?>" readonly="true" />
							<td><label for="parking_days_new">Parkdauer neu</label><br>
							<input type="text" size="5" min="1" name="parking_days_new" value="<?php echo getDaysBetween2Dates(new DateTime($_GET["arr"]), new DateTime($_GET["ret"])) ?>" readonly="true"></td>
							<td><label for="costs_costs">Parkkosten neu in €</label><br>
							<?php else: ?>
							<td><label for="costs_costs">Parkkosten in €</label><br>
							<?php endif; ?>	
							<?php 
											
								if(isset($_GET["arr"]) && isset($_GET["ret"])){
									$price = $newCosts;
									$priceDiff = number_format($price - $booking->Betrag,2,".",".");
								}									
								else{									
									$price = number_format($booking->Betrag,2,".",".");
									$priceDiff = 0;
								}									
								
							?>
							<input type="hidden" size="10" name="parking_update" value="<?php echo $price; ?>"/>
							<input type="text" size="10" name="costs_costs" value="<?php echo $price; ?>" readonly="true" />
							<?php
								if($newDiffDays > 0 && $priceDiff > 0){
									echo "inkl. + " . $newDiffDays; echo $newDiffDays > 1 ? " Tage " : " Tag "; echo "( + " . $priceDiff . "€ ) ";
								}
								
							?>
							</td>
						</tr>
					</table>
					<?php if (!$woo_order->has_status('cancelled') && pastDateTo >= 0 ):?>
						<hr>
						<!--<div>
							<br><input type="radio" id="send_mail" name="send_mail" value="mail" <?php echo $disabledDesDate; ?>>
							<label for="send_mail">Buchungsbestätigung senden</label><br>
						</div>
						<br>-->						
						<?php if(($newDiffDays > 0 && $priceDiff > 0) /*&& get_post_meta($order_id, '_payment_method', true) != 'cod'*/): ?>
						<button data-pid="<?php echo $booking->proid ?>" 
								data-dateFrom="<?php if(isset($_GET["arr"])) echo $checkoutDateFrom; else echo date($co_datefrom) ?>" 
								data-dateTo="<?php if(isset($_GET["ret"])) echo $checkoutDateTo; else echo date($co_dateto) ?>"
								data-token="<?php echo ($externCode) ? $externCode : get_post_meta($order_id, 'token', true) ?>"
								data-price="<?php echo number_format($priceDiff,2,".",".") ?>"
								data-orderid="<?php echo $order_id ?>"
								data-tage="<?php echo $newDiffDays ?>"
								class="btn btn-primary edit-order-btn btn-order-parklot-customer <?php echo $class4 ?>" >
							<?php echo number_format($priceDiff,2,".",".") ?>
							€ - Zur Nachzahlung
						</button>
						<?php else: ?>
						<div>
							<input type="hidden" name="update" value="1">
							<input class="btn btn-primary edit-order-btn" style="<?php echo $disabledDesDate != 'pointer-events: none;' ? '' : '' ?>" id="edit-order-btn" type="submit" value="Buchung aktualisieren" <?php echo $disabledDesDate; ?>>
						</div>
						<?php endif; ?>
					<?php endif;?>
				</div>
			</div>
		</form>
	</div>
</div>

<link rel="stylesheet" type="text/css" href="<?php echo $base_url . '/wp-content/plugins/parking-custom-functions/assets/rangepicker/lightpick.css';?>">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js"></script>
<script src="<?php echo $base_url . '/wp-content/plugins/parking-custom-functions/assets/rangepicker/lightpick.js';?>"></script>
<script>
var startDate = new Date();
startDate.setDate(startDate.getDate() + 1);
var path = new URL(window.location.href);
var dateFrom = new Lightpick({
    field: document.getElementById('edit-start-date-user'),
    singleDate: true,
	lang: 'de',
	minDate: startDate,
	format: 'DD.MM.YYYY',
	onSelect: function(start){
		start = new Date(start);
		start = start.getFullYear() + "-" + ("0"+(start.getMonth()+1)).slice(-2) + "-" + ("0" + start.getDate()).slice(-2);
		//document.getElementById('edit-order-btn').disabled = true;
		//document.getElementById('edit-end-date-user').value = "";
		endDate = new Date(start);
		endDate.setDate(endDate.getDate() + 1);
		var path = new URL(window.location.href);
		path.searchParams.set('arr', document.getElementById('edit-start-date-user').value);
		path.searchParams.set('ret', document.getElementById('edit-end-date-user').value);
		window.location.href = path.href;
		var dateTo = new Lightpick({
			field: document.getElementById('edit-end-date-user'),
			singleDate: true,
			lang: 'de',
			minDate: endDate,
			format: 'DD.MM.YYYY',
			onSelect: function(start){
				start = new Date(start);
				start = start.getFullYear() + "-" + ("0"+(start.getMonth()+1)).slice(-2) + "-" + ("0" + start.getDate()).slice(-2);
				var path = new URL(window.location.href);
				document.getElementById('edit-order-btn').disabled = false;				
				if (path.href.indexOf('edit_booking') > -1) {
					path.searchParams.delete('arr');
					path.searchParams.delete('ret');
				}
				if(document.getElementById('edit-end-date-user').value != null){
					path.searchParams.set('arr', document.getElementById('edit-start-date-user').value);
					path.searchParams.set('ret', document.getElementById('edit-end-date-user').value);
					window.location.href = path.href;
				}
			}
		});
	}
});

var tmp = document.getElementById('edit-start-date-user').value;
var t = tmp.split(".");
var d = t[2] + "-" + t[1] + "-" + t[0];
var endDate = new Date(d);
endDate.setDate(endDate.getDate() + 1);

//endDate.setDate(endDate.getDate() + 1);
var dateTo = new Lightpick({
    field: document.getElementById('edit-end-date-user'),
    singleDate: true,
	lang: 'de',
	minDate: endDate,
	format: 'DD.MM.YYYY',
	onSelect: function(start){
		start = new Date(start);
		start = start.getFullYear() + "-" + ("0"+(start.getMonth()+1)).slice(-2) + "-" + ("0" + start.getDate()).slice(-2);
		var path = new URL(window.location.href);
		document.getElementById('edit-order-btn').disabled = false;
		if (path.href.indexOf('edit_booking') > -1) {
			path.searchParams.delete('arr');
			path.searchParams.delete('ret');
		}
		if(document.getElementById('edit-end-date-user').value != null){
			path.searchParams.set('arr', document.getElementById('edit-start-date-user').value);
			path.searchParams.set('ret', document.getElementById('edit-end-date-user').value);
			window.location.href = path.href;
		}
	}
});

history.pushState(null, document.title, location.href);
window.addEventListener('popstate', function (event)
{
  history.pushState(null, document.title, location.href);
  window.location.href = '/buchungen/';
});
</script>

<?php endif; ?>