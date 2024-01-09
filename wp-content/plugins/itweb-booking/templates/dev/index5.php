<?php
global $wpdb;

$csvFilePath = ABSPATH . 'wp-content/plugins/itweb-booking/templates/dev/hex_import/b.csv';
$csvData = file($csvFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$resultArray = [];


foreach ($csvData as $csvLine) {
    $dataArray = str_getcsv($csvLine, ';');
    $resultArray[] = $dataArray;
}

foreach($resultArray as $booking){
	$temp = explode(" ", $booking[3]);
	$code = $temp[0];
	$booking_ref = $temp[1];		
	
	$order_id = $wpdb->get_row("
		SELECT pm.post_id as order_id
		FROM 59hkh_postmeta pm 
		WHERE pm.meta_key = 'token' and pm.meta_value = '" . $booking_ref . "'
	");
	
	if($order_id->order_id == null){
		echo $booking_ref . "<br>";
		/*
		$betrag = str_replace(',', '.', $booking[10]);
	
		$str = trim($booking[0]);
		$arrDateTime = explode("-", $str);
		$arr = explode(" ", $arrDateTime[0]);
		
		$str = trim($booking[1]);
		$desDateTime = explode("-", $str);
		$des = explode(" ", $desDateTime[0]);
		
		$arrDay = $arr[0];
		$arrMonth = getMonth($arr[1]);
		$arrYear = $arr[2];
		$arrDate = $arrYear . "-" . $arrMonth . "-" . $arrDay;
		$arrTime = $arrDateTime[1];
		
		$desDay = $des[0];
		$desMonth = getMonth($des[1]);
		$desYear = $des[2];
		$desDate = $desYear . "-" . $desMonth . "-" . $desDay;
		$desTime = $desDateTime[1];
		
		$str = trim($booking[4]);
		$bookingDateStr = explode(".", $str);
		$bookingDay = $bookingDateStr[0];
		$bookingMonth = $bookingDateStr[1];
		$bookingYear = "20" . trim($bookingDateStr[2]);
		$bookingDate = date('Y-m-d H:i', strtotime($bookingYear . "-" . $bookingMonth . "-" . $bookingDay . " 08:00"));

		$data["arrivalDate"] = trim($arrDate);
		$data["departureDate"] = trim($desDate);
		$data["arrivalTime"] = trim($arrTime);
		$data["departureTime"] = trim($desTime);
		$data["countTravellers"] = $booking[2];
		$data["bookingDate"] = $bookingDate;
		$data["countTravellers"] = $booking[2];
		
		if($code == 'STR4' || $code == 'STR8' || $code == 'STB2' || $code == 'STB1'){
			$data['product'] = 621;
			$hex_betrag = number_format($betrag / 70 * 100, 2, ".", ".");
			$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 30, 2, ".", ".");
		}
		
		if($code == 'STRH'){
			$data['product'] = 683;
			$hex_betrag = number_format($betrag / 70 * 100, 2, ".", ".");
			$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 30, 2, ".", ".");
		}
		
		if($code == 'STR2'){
			$data['product'] = 621;
			$hex_betrag = number_format(($betrag / 75) * 100, 2, ".", ".");
			$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 25, 2, ".", ".");
		}
		
		if($code == 'STR6' || $code == 'STR7' || $code == 'STRD'){
			$data['product'] = 624;
			$hex_betrag = number_format($betrag / 70 * 100, 2, ".", ".");
			$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 30, 2, ".", ".");
		}
		if($code == 'STR0'){
			$data['product'] = 624;
			$hex_betrag = number_format($betrag / 75 * 100, 2, ".", ".");
			$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 25, 2, ".", ".");
		}
	  
		if($code == 'STR1' ||$code == 'STR9'){
			$data['product'] = 901;
			$hex_betrag = number_format($betrag / 70 * 100, 2, ".", ".");
			$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 30, 2, ".", ".");
		}
		if($code == 'STRW'){
			$data['product'] = 901;
			$hex_betrag = number_format($betrag / 75 * 100, 2, ".", ".");
			$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 25, 2, ".", ".");
		}
		
		if($code == 'ST10' || $code == 'ST11'){
			$data['product'] = 24261;
			$hex_betrag = number_format($betrag / 70 * 100, 2, ".", ".");
			$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 30, 2, ".", ".");
		}
		if($code == 'ST12'){
			$data['product'] = 24261;
			$hex_betrag = number_format($betrag / 75 * 100, 2, ".", ".");
			$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 25, 2, ".", ".");
		}
		
		if($code == 'ST13' || $code == 'ST14'){
			$data['product'] = 24263;
			$hex_betrag = number_format($betrag / 70 * 100, 2, ".", ".");
			$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 30, 2, ".", ".");
		}
		if($code == 'ST15'){
			$data['product'] = 24263;
			$hex_betrag = number_format($betrag / 75 * 100, 2, ".", ".");
			$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 25, 2, ".", ".");
		}
		
		if($code == 'ST16'){
			$data['product'] = 24609;
			$hex_betrag = number_format($betrag / 70 * 100, 2, ".", ".");
			$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 30, 2, ".", ".");
		}
		
		$data['internalADPrefix'] = $code;
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
		

		add_post_meta($order_id, '_customer_user', 6, true);
		add_post_meta($order_id, '_billing_address_1', '', true);
		add_post_meta($order_id, '_billing_city', '', true);
		add_post_meta($order_id, '_billing_state', '', true);
		add_post_meta($order_id, '_billing_postcode', '', true);
		add_post_meta($order_id, '_billing_company', '', true);
		add_post_meta($order_id, '_billing_country', '', true);
		add_post_meta($order_id, '_billing_email', '', true);
		add_post_meta($order_id, '_billing_first_name', strtoupper($booking[6]), true);
		add_post_meta($order_id, '_billing_last_name', strtoupper($booking[7]), true);
		add_post_meta($order_id, '_billing_phone', $booking[12], true);

		add_post_meta($order_id, 'Anreisedatum', dateFormat($data["arrivalDate"]), true);
		add_post_meta($order_id, 'first_anreisedatum', dateFormat($data["arrivalDate"]), true);
		add_post_meta($order_id, 'Abreisedatum', dateFormat($data["departureDate"]), true);
		add_post_meta($order_id, 'first_abreisedatum', dateFormat($data["departureDate"]), true);
		add_post_meta($order_id, 'Uhrzeit von', $data["arrivalTime"], true);
		add_post_meta($order_id, 'Uhrzeit bis', $data["departureTime"], true);
		add_post_meta($order_id, 'Hinflugnummer', $booking[13], true);
		add_post_meta($order_id, 'Rückflugnummer', $booking[14], true);
		add_post_meta($order_id, 'Personenanzahl', $data["countTravellers"], true);
		add_post_meta($order_id, 'Kennzeichen', $booking[17], true);		
		add_post_meta($order_id, 'Fahrzeughersteller', '', true);      
		add_post_meta($order_id, 'Fahrzeugmodell', '', true);
		add_post_meta($order_id, 'Fahrzeugfarbe', '', true);
		add_post_meta($order_id, 'token', $booking_ref, true);
		
		$wpdb->insert($wpdb->prefix . 'itweb_orders', [
			'date_from' => date('Y-m-d H:i', strtotime($data["arrivalDate"] . ' ' . $data["arrivalTime"])),
			'date_to' => date('Y-m-d H:i', strtotime($data["departureDate"] . ' ' . $data["departureTime"])),
			'product_id' => $data['product'],
			'order_id' => $order_id,
			'b_total' => 0,
			'm_v_total' => $mv,
			'out_flight_number' => $booking[13],
			'return_flight_number' => $booking[14],
			'nr_people' => $data['countTravellers'],
			'code' => $code
		]);
		
	$wpdb->update($wpdb->prefix . 'posts', [
			'post_date' => date('Y-m-d H:i', strtotime($data["bookingDate"])),
			'post_date_gmt' => date('Y-m-d H:i', strtotime($data["bookingDate"]))				
		], ['ID' => $order_id]);
		
		*/
	}
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

//echo "<pre>"; print_r($data); echo "</pre>";
//echo "<pre>"; print_r($resultArray); echo "</pre>";



//unlink($importDIR . '/' . $files[2]);

?>
