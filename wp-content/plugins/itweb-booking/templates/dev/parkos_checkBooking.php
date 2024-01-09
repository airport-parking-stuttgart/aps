<?php
global $wpdb;
// indoor = 41402;			
// outdoor = 41403;

$set = 0;


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

$merchant_id = 569;
$from = '2022-11-10';
$till = '2022-11-30';
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



foreach($bookings->data as $booking){
	if($booking->location_type == 'outdoor'){
		$token = $booking->code;
		$price = number_format($booking->total_price / 119 * 100, 2, ".", ".");
		$sql = "SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_value = '".$token."'";
		$order_id = $wpdb->get_row($sql);
		
		$sql = "SELECT product_id FROM {$wpdb->prefix}itweb_orders WHERE order_id = '".$order_id->post_id."' AND product_id = 41402";
		$product = $wpdb->get_row($sql);	
		$woo_order = wc_get_order($order_id->post_id);
		if($set == 0 && $product->product_id != null){
			$order_items = $woo_order->get_items();
			foreach ( $order_items as $order_item_id => $order_item) {
				wc_delete_order_item($order_item_id);
			}

			$woo_order->calculate_taxes();
			$woo_order->calculate_totals();
			$woo_order->save();
			
			$woo_order = wc_get_order($order_id->post_id);
			$woo_order->add_product( wc_get_product(41403), 1, [
					//'subtotal'     => $price, // e.g. 32.95
					'total'        => $price, // e.g. 32.95
				] );

			$woo_order->calculate_taxes();
			$woo_order->calculate_totals();
			$woo_order->save();
			
			$wpdb->update($wpdb->prefix . "itweb_orders", array( 'product_id' => 41403),array('order_id'=>$order_id->post_id));	
			
			echo $order_id->post_id . "<br>";
		}
	}
		
}

?>