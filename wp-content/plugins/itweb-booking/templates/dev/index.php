<?php
/*
$start = date('2024-01-23');
$end = date('2024-01-24');

// LIVE
$para = array(
	'X-ROOSH-API-KEY: bKTdk2YWE1gH4zVY3hjfXYwxsg9jcglJTVHhWtcuJMF5ZHLwtx1ZvXNC1EyGw9B7',
	'X-ROOSH-CLIENT-ID: 2PY7TUoiWkTRH8JiDvYOEMwR3VO',
);

$url = "https://api.roosh.online/provider/v1/bookings/findByModification?start=".$start."&end=".$end;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Set the custom headers
curl_setopt($ch, CURLOPT_HTTPHEADER, $para);

$server_output = curl_exec($ch);
curl_close($ch);
$bookings = json_decode($server_output);
echo "<pre>"; print_r($bookings); echo "</pre>";
*/
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
/*
$broker_id = Database::getInstance()->getBrokerIdByApi('fluparks');
$id = $broker_id->id;
$api_codes = Database::getInstance()->getAPICodesById($id);
//echo "<pre>"; print_r($api_codes); echo "</pre>";
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

	$data->total_price = number_format($booking->price->amount, 2, ".", ".");
	$data->persons = $booking->journey->travelers;
	$data->flight_departure_nr = $booking->journey->departure_flight_number;
	$data->flight_return_nr = $booking->journey->arrival_flight_number;
	$data->car_license_plate = $booking->journey->car->license_plate;
	$data->vorname = $booking->customer->first_name;
	$data->nachname = $booking->customer->last_name;
	$data->email = $booking->customer->email;
	$data->phone = $booking->customer->phone;
	$data->status = $booking->status;
	
	/*
	if($booking->status == 'completed'){
		$orderSQL = Orders::getOrderByToken($booking->reference);		
		if($orderSQL){
			Database::getInstance()->updateOrderFomFluparks($data);
		}
		else{
			Database::getInstance()->saveOrderFomFluparks($data);
		}			
	}		
	elseif($booking->status == 'cancelled'){
		Database::getInstance()->cancelOrderFomFluparks($data);
	}
	echo "<pre>"; print_r($data); echo "</pre>";
	*/
/*
}
*/



$para = array(
	'username' => 'it@airport-parking-stuttgart.de', 
	'password' => 'Sergej#22Aps',
	'grant_type' => 'password',
	'client_id' => '1461',
	'client_secret' => 'zQMwpHdXMEqd5e4WBvLoWT7OHx0Hz5K0HjKhiJw4'	
	);

$url = "https://api.parkos.com/oauth/token";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,
http_build_query($para));
// Receive server response ...
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$server_output = curl_exec($ch);
curl_close($ch);

$client = json_decode($server_output);

//echo "<pre>"; print_R($client); echo "</pre>";
$merchant_id = 1741;
$from = date('2024-01-21');
$till = date('2024-01-21');
$url = "https://api.parkos.com/v1/reservations?merchant_id=".$merchant_id."&period_type=created_at&from=".$from."&till=".$till;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: ' . $client->token_type . ' ' . $client->access_token,
    'Accepts: application/json'
));
// Receive server response ...
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$server_output = curl_exec($ch);
curl_close($ch);

$bookings = json_decode($server_output);

$broker_id = Database::getInstance()->getBrokerIdByApi('parkos');
$id = $broker_id->id;
$api_codes = Database::getInstance()->getAPICodesById($id);
foreach($bookings->data as $booking){
	if(count($booking) > 0){		
		foreach($api_codes as $code){
			if($booking->merchant_id == $code->code && $booking->location_type == $code->type){
				$booking->c_product = $code->product_id;
			}
		}
		if($booking->c_product != null){
			Database::getInstance()->saveOrderFomParkos($booking);
		}
	}
}

echo "<pre>"; print_r($bookings); echo "</pre>";


?>