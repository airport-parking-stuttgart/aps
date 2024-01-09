<?php

global $wpdb;
$aps_parkinglot_bookings = array(

  
 
);

foreach($aps_parkinglot_bookings as $k){
	$o = $wpdb->get_row('select post_status from 59hkh_posts where ID = ' . $k['order_id']);
	
	if($o->post_status == 'wc-processing')
		$c = 1;
	else
		$c = 0;
	
	if($c == 1){
		$data['product'] = 621;
		$data['totalParkingCosts'] = number_format($k['m_v_total'] / 100 * 70, 2, ".", ".");
		//$data['totalParkingCosts'] = $data['totalParkingCosts'] / 70 * 100;
		$commission = number_format(($data['totalParkingCosts'] / 119 * 100) / 100 * 25, 2, ".", ".");
		$price = number_format((float)$data['totalParkingCosts'] / 119 * 100, 4, '.', '');
		
		$woo_order = wc_get_order($k['order_id']);
		$order_items = $woo_order->get_items();
		foreach ( $order_items as $order_item_id => $order_item) {
			wc_delete_order_item($order_item_id);
		}
		
		$woo_order->calculate_taxes();
		$woo_order->calculate_totals();
		$woo_order->save();
		
		$woo_order = wc_get_order($k['order_id']);
		$woo_order->add_product( wc_get_product($data['product']), 1, [
				//'subtotal'     => $price, // e.g. 32.95
				'total'        => $price, // e.g. 32.95
			] );
		
		$woo_order->calculate_taxes();
		$woo_order->calculate_totals();
		$woo_order->save();
		
		$wpdb->update($wpdb->prefix . 'wc_order_product_lookup', [
				'product_net_revenue' => ($data['totalParkingCosts'] / 119 * 100),
				'product_gross_revenue' => ($data['totalParkingCosts']),
				'tax_amount' => ($data['totalParkingCosts'] / 119 * 19)
			], ['order_id' => $k['order_id']]);
			
		$wpdb->update($wpdb->prefix . 'itweb_orders', [
			//'product_id' => $data['product'],
			'm_v_total' => $data['totalParkingCosts']
		], ['order_id' => $k['order_id']]);
		
		$wpdb->update($wpdb->prefix . 'itweb_orders_meta', [                               
			'meta_value' => $commission				
		], ['order_id' => $k['order_id'], 'meta_key' => 'provision']);
	}
}
?>