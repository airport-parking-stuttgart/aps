<?php
global $wpdb;
$importDIR = ABSPATH . 'wp-content/plugins/itweb-booking/templates/dev/hex_import';
$files = scandir($importDIR);


$handle = fopen ($importDIR . '/' . $files[2],'r');


while (($csv_array = fgetcsv ($handle, 1000, ',', '"')) !== FALSE ) {

  
	$code = explode(" ", ($csv_array[3]));
	
  
	if($code[0] == 'STR4' || $code[0] == 'STR8' || $code[0] == 'STB2' || $code[0] == 'STB1'){
		$data['product'] = 621;
		$hex_betrag = number_format($csv_array[8] / 70 * 100, 2, ".", ".");
		$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 30, 2, ".", ".");
	}
	
	if($code[0] == 'STRH'){
		$data['product'] = 683;
		$hex_betrag = number_format($csv_array[8] / 70 * 100, 2, ".", ".");
		$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 30, 2, ".", ".");
	}
	
	if($code[0] == 'STR2'){
		$data['product'] = 621;
		$hex_betrag = number_format($csv_array[8] / 75 * 100, 2, ".", ".");
		$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 25, 2, ".", ".");
	}
	
	if($code[0] == 'STR6' || $code[0] == 'STR7' || $code[0] == 'STRD'){
		$data['product'] = 624;
		$hex_betrag = number_format($csv_array[8] / 70 * 100, 2, ".", ".");
		$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 30, 2, ".", ".");
	}
	if($code[0] == 'STR0'){
		$data['product'] = 624;
		$hex_betrag = number_format($csv_array[8] / 75 * 100, 2, ".", ".");
		$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 25, 2, ".", ".");
	}
  
	if($code[0] == 'STR1' ||$code[0] == 'STR9'){
		$data['product'] = 901;
		$hex_betrag = number_format($csv_array[8] / 70 * 100, 2, ".", ".");
		$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 30, 2, ".", ".");
	}
	if($code[0] == 'STRW'){
		$data['product'] = 901;
		$hex_betrag = number_format($csv_array[8] / 75 * 100, 2, ".", ".");
		$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 25, 2, ".", ".");
	}
	
	if($code[0] == 'ST10' || $code[0] == 'ST11'){
		$data['product'] = 24261;
		$hex_betrag = number_format($csv_array[8] / 70 * 100, 2, ".", ".");
		$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 30, 2, ".", ".");
	}
	if($code[0] == 'ST12'){
		$data['product'] = 24261;
		$hex_betrag = number_format($csv_array[8] / 75 * 100, 2, ".", ".");
		$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 25, 2, ".", ".");
	}
	
	if($code[0] == 'ST13' || $code[0] == 'ST14'){
		$data['product'] = 24263;
		$hex_betrag = number_format($csv_array[8] / 70 * 100, 2, ".", ".");
		$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 30, 2, ".", ".");
	}
	if($code[0] == 'ST15'){
		$data['product'] = 24263;
		$hex_betrag = number_format($csv_array[8] / 75 * 100, 2, ".", ".");
		$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 25, 2, ".", ".");
	}
	
	if($code[0] == 'ST16'){
		$data['product'] = 24609;
		$hex_betrag = number_format($csv_array[8] / 70 * 100, 2, ".", ".");
		$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 30, 2, ".", ".");
	}
	
	function getMonth($dateTime){
		switch ($dateTime) {
			case "Jan":
				$arrMonth = "01";
				break;
			case "Feb":
				$arrMonth = "02";
				break;
			case "Mär":
				$arrMonth = "03";
				break;
			case "Apr":
				$arrMonth = "04";
				break;
			case "Mai":
				$arrMonth = "05";
				break;
			case "Jun":
				$arrMonth = "06";
				break;
			case "Jul":
				$arrMonth = "07";
				break;
			case "Aug":
				$arrMonth = "08";
				break;
			case "Sep":
				$arrMonth = "09";
				break;
			case "Okt":
				$arrMonth = "10";
				break;
			case "Nov":
				$arrMonth = "11";
				break;
			case "Dez":
				$arrMonth = "12";
				break;
			default:
				echo "error";
		}
		return $arrMonth;
	}
	
	$data['internalADPrefix'] = $code[0];
	$data['totalParkingCosts'] = $hex_betrag;
	$commission = $hex_provision;
	
	$parklot = new Parklot($data['product']);
	$productSQL = Database::getInstance()->getParklotByProductId($data['product']);
	
	$product = wc_get_product($data['product']);
	// set new product price
	$product->set_price($data['totalParkingCosts']);

	$order = Orders::createWCOrder($product);
	$order_id = $order->get_id();
	
	add_post_meta($order_id, '_transaction_id', 'm', true);
	add_post_meta($order_id, '_payment_method_title', 'MasterCard', true);
	
	$barzahlung = 0;
	$mv = $data['totalParkingCosts'];
	
	$str = trim($csv_array[0]);
	$arrDateTime = explode("-", $str);
	$str = trim($csv_array[1]);
	$desDateTime = explode("-", $str);
	$arrDay = $arrDateTime[0];
	$arrMonth = getMonth($arrDateTime[1]);
	$arrYear = "20" . trim($arrDateTime[2]);
	$arrDate = $arrYear . "-" . $arrMonth . "-" . $arrDay;
	
	$desDay = $desDateTime[0];
	$desMonth = getMonth($desDateTime[1]);
	$desYear = "20" . trim($desDateTime[2]);
	$desDate = $desYear . "-" . $desMonth . "-" . $desDay;

	$output = str_split($arrDateTime[3], 3);
	$arrTime = $output[0] . ":" . $output[1];
	$output = str_split($desDateTime[3], 3);
	$desTime = $output[0] . ":" . $output[1];
	
	$str = trim($csv_array[4]);
	$bookingDate = explode("-", $str);
	$bookingDay = $bookingDate[0];
	$bookingMonth = getMonth($bookingDate[1]);
	$bookingYear = "20" . trim($bookingDate[2]);
	$bookingDate = $bookingYear . "-" . $bookingMonth . "-" . $bookingDay . " " . $arrTime . ":00";

	$data["arrivalDate"] = trim($arrDate);
	$data["departureDate"] = trim($desDate);
	$data["arrivalTime"] = trim($arrTime);
	$data["departureTime"] = trim($desTime);
	$data["countTravellers"] = $csv_array[2];

	
	add_post_meta($order_id, '_order_total', '', true);
	add_post_meta($order_id, '_customer_user', 6, true);
	add_post_meta($order_id, '_completed_date', '', true);
	add_post_meta($order_id, '_order_currency', '', true);
	add_post_meta($order_id, '_paid_date', '', true);

	add_post_meta($order_id, '_billing_address_1', '', true);
	add_post_meta($order_id, '_billing_city', '', true);
	add_post_meta($order_id, '_billing_state', '', true);
	add_post_meta($order_id, '_billing_postcode', '', true);
	add_post_meta($order_id, '_billing_company', '', true);
	add_post_meta($order_id, '_billing_country', '', true);
	add_post_meta($order_id, '_billing_email', '', true);
	add_post_meta($order_id, '_billing_first_name', '', true);
	add_post_meta($order_id, '_billing_last_name', $csv_array[5], true);
	add_post_meta($order_id, '_billing_phone', '', true);

	add_post_meta($order_id, 'Anreisedatum', dateFormat($data["arrivalDate"]), true);
	add_post_meta($order_id, 'first_anreisedatum', dateFormat($data["arrivalDate"]), true);
	add_post_meta($order_id, 'Abreisedatum', dateFormat($data["departureDate"]), true);
	add_post_meta($order_id, 'first_abreisedatum', dateFormat($data["departureDate"]), true);
	add_post_meta($order_id, 'Uhrzeit von', $data["arrivalTime"], true);
	add_post_meta($order_id, 'Uhrzeit bis', $data["departureTime"], true);
	add_post_meta($order_id, 'Hinflugnummer', '', true);
	add_post_meta($order_id, 'Rückflugnummer', '', true);
	add_post_meta($order_id, 'Personenanzahl', $data["countTravellers"], true);
	add_post_meta($order_id, 'Kennzeichen', '', true);		
	add_post_meta($order_id, 'Fahrzeughersteller', '', true);      
	add_post_meta($order_id, 'Fahrzeugmodell', '', true);
	add_post_meta($order_id, 'Fahrzeugfarbe', '', true);
	add_post_meta($order_id, 'token', $code[1], true);
	
	$wpdb->insert($wpdb->prefix . 'itweb_orders', [
            'date_from' => date('Y-m-d H:i', strtotime($data["arrivalDate"] . ' ' . $data["arrivalTime"])),
            'date_to' => date('Y-m-d H:i', strtotime($data["departureDate"] . ' ' . $data["departureTime"])),
            'product_id' => $data['product'],
            'order_id' => $order_id,
			'b_total' => 0,
			'm_v_total' => $mv,
            'out_flight_number' => '',
            'return_flight_number' => '',
            'nr_people' => $data['countTravellers'],
			'code' => $code[0]
        ]);
		
	$wpdb->update($wpdb->prefix . 'posts', [
            'post_date' => date('Y-m-d H:i', strtotime($bookingDate)),
            'post_date_gmt' => date('Y-m-d H:i', strtotime($bookingDate))
			
        ], ['ID' => $order_id]);
		
	Database::getInstance()->saveBookingMeta($order_id, 'provision', $commission);
	
	echo "<pre>"; print_r($csv_array); echo "</pre>";
	echo "<pre>"; print_r($data); echo "</pre>";
}

fclose($handle);
unlink($importDIR . '/' . $files[2]);

?>
