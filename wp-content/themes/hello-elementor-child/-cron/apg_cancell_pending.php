<?php
$url = "https://airport-parking-germany.de/curl/?request=apm_bookings&pw=apm_orders_req54894135";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,
http_build_query($data));
// Receive server response ...
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$server_output = curl_exec($ch);
curl_close($ch);

$server_output = unserialize($server_output);

global $wpdb;
foreach($server_output as $booking){
	$order_id = $wpdb->get_row("
	SELECT pm.post_id
	FROM 59hkh_postmeta pm 
	WHERE pm.meta_key = 'token' and pm.meta_value = '" . $booking->token . "'
	");
	
	if($order_id->post_id){
		$woo_order = wc_get_order($order_id->post_id);
		if ($woo_order->has_status( 'processing' )){
			$woo_order = wc_get_order($order_id->post_id);
			$woo_order->update_status( 'cancelled' );
		}
	}
}

?>