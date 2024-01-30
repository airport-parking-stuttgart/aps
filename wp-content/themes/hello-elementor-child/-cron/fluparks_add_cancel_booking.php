<?php


$para = array(
	'X-ROOSH-API-KEY: bKTdk2YWE1gH4zVY3hjfXYwxsg9jcglJTVHhWtcuJMF5ZHLwtx1ZvXNC1EyGw9B7',
	'X-ROOSH-CLIENT-ID: 2PY7TUoiWkTRH8JiDvYOEMwR3VO',
);

$start = date('Y-m-d');
$end = date('Y-m-d', strtotime($start . ' +1 day'));

$url = "https://api.roosh.online/provider/v1/bookings/findByModification?start=".$start."&end=".$end;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Set the custom headers
curl_setopt($ch, CURLOPT_HTTPHEADER, $para);

$server_output = curl_exec($ch);
curl_close($ch);
$bookings = json_decode($server_output);
//echo "<pre>"; print_r($bookings); echo "</pre>";

/*
$url = "https://api.roosh.online/provider/v1/services/";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Set the custom headers
curl_setopt($ch, CURLOPT_HTTPHEADER, $para);

$server_output = curl_exec($ch);
curl_close($ch);
$services = json_decode($server_output);
echo "<pre>"; print_r($services); echo "</pre>";
*/

$broker_id = Database::getInstance()->getBrokerIdByApi('fluparks');
$id = $broker_id->id;
$api_codes = Database::getInstance()->getAPICodesById($id);
foreach($bookings->bookingsByModification as $booking){		
	foreach($api_codes as $api_code){
		if($api_code->code == $booking->service_id){
			if($api_code->type == "indoor"){
				$data->product = $api_code->product_id;
				$data->type = "indoor";
			}
			elseif($api_code->type == "outdoor"){
				$data->product = $api_code->product_id;
				$data->type = "outdoor";
			}
		}					
	}
	
	$data->code = $booking->reference;
	
	$dateString = $booking->created_at;
	$dateTime = new DateTime($dateString);
	$data->booking_date = $dateTime->format('Y-m-d H:i');
	
	$dateString = $booking->start;
	$dateTime = new DateTime($dateString);
	$dateTime->modify('+1 hour');
	$data->arrival_date = $dateTime->format('Y-m-d');
	$data->arrival_time = $dateTime->format('H:i');
	
	$dateString = $booking->end;
	$dateTime = new DateTime($dateString);
	$dateTime->modify('+1 hour');
	$data->departure_date = $dateTime->format('Y-m-d');
	$data->departure_time = $dateTime->format('H:i');

	$data->persons = $booking->journey->travelers;
	$data->flight_departure_nr = $booking->journey->departure_flight_number;
	$data->flight_return_nr = $booking->journey->arrival_flight_number;
	$data->car_brand_model = $booking->journey->car->model != null ? $booking->journey->car->model : "";
	$data->car_color = $booking->journey->car->color != null ? $booking->journey->car->color : "";
	$data->car_license_plate = $booking->journey->car->license_plate;
	$data->vorname = $booking->customer->first_name;
	$data->nachname = $booking->customer->last_name;
	$data->email = $booking->customer->email;
	$data->phone = $booking->customer->phone;
	$data->status = $booking->status;
	
	if($booking->status == 'completed'){
		$orderSQL = Orders::getOrderByToken($booking->reference);		
		if($orderSQL){
			$data->total_price = number_format($booking->price->amount / 119 * 100, 2, ".", ".");
			$data->total_price_brutto = number_format($booking->price->amount, 2, ".", ".");
			Database::getInstance()->updateOrderFomFluparks($data);
		}
		else{
			Database::getInstance()->saveOrderFomFluparks($data);
		}			
	}		
	elseif($booking->status == 'cancelled'){
		Database::getInstance()->cancelOrderFomFluparks($data);
	}
}
?>