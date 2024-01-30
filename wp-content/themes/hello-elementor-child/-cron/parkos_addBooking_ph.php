<?php


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
$merchant_id = 569;
$from = date('Y-m-d');
$till = date('Y-m-d');
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
		if($booking->c_product != null)
			Database::getInstance()->saveOrderFomParkos($booking);
	}
}



//echo "<pre>"; print_R($bookings); echo "</pre>";
?>