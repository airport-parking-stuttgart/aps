<?php


global $wpdb;


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
$merchant_id =  569;
$from = date('2023-03-01');
$till = date('2023-03-06');
$url = "https://api.parkos.com/v1/reservations?merchant_id=".$merchant_id."&period_type=updated_at&from=".$from."&till=".$till;
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

foreach($bookings->data as $booking){
	//if(count($booking) > 0)
	//	Database::getInstance()->updateOrderFomParkos($booking);
}



//echo "<pre>"; print_R($bookings); echo "</pre>";



?>
