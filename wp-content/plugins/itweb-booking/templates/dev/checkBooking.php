<?php
global $wpdb;

$today = date('Y.m.d');
$orders = $wpdb->get_results("
	SELECT ID FROM 59hkh_posts p WHERE 
	p.post_type = 'shop_order' AND
	p.post_date BETWEEN DATE('2022-12-01') AND DATE('".$today."')
	");

foreach($orders as $order){
	$eintrag = $wpdb->get_row("
	SELECT order_id FROM 59hkh_itweb_orders o 
	WHERE o.order_id = $order->ID

	UNION ALL

	SELECT order_id FROM 59hkh_itweb_hotel_transfers o 
	WHERE o.order_id = $order->ID
	");
	
	if($eintrag->order_id == null){
		
		$order = new WC_Order($order->ID);
		$items = $order->get_items();
		
		foreach ($items as $item) {
			$product_id = $item['product_id'];
		}
		
		if($order->get_payment_method_title() == "Barzahlung"){
			add_post_meta($order_id, '_transaction_id', 'barzahlung', true);
			add_post_meta($order_id, '_payment_method_title', 'Barzahlung', true);
			$barzahlung = $order->get_total();
			$mv = 0;
		}
		else{
			$barzahlung = 0;
			$mv = $order->get_total();
		}
		
		echo $order->ID . " " . $product_id . " " . $barzahlung . " " . $mv . "<br>";
		
		$dateFrom = get_post_meta($order->ID, 'Anreisedatum', true);
		$timeFrom = get_post_meta($order->ID, 'Uhrzeit von', true);
		$dateTo = get_post_meta($order->ID, 'Abreisedatum', true);
		$timeTo = get_post_meta($order->ID, 'Uhrzeit bis', true);
		$person = get_post_meta($order->ID, 'Personenanzahl', true);
		$hin_nr = get_post_meta($order->ID, 'Hinflugnummer', true);
		$zur_nr = get_post_meta($order->ID, 'Rückflugnummer', true);
		
		
		$wpdb->insert($wpdb->prefix . "itweb_orders", [
            'date_from' => date('Y-m-d H:i', strtotime($dateFrom . ' ' . $timeFrom)),
            'date_to' => date('Y-m-d H:i', strtotime($dateTo . ' ' . $timeTo)),
            'product_id' => (int)$product_id,
            'order_id' => $order->ID,
			'b_total' => $barzahlung,
			'm_v_total' => $mv,
            'nr_people' => $person != null ? $person : 1,
            'out_flight_number' => $hin_nr != null ? $hin_nr : '',
            'return_flight_number' => $zur_nr != null ? $zur_nr : ''
        ]);
		echo "<pre>"; print_r($eintrag); echo "</pre>";
	}
}

?>