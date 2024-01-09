<?php



$order_id = 71527;
$product_id = 537;
$price = 53.00;
$order = wc_get_order($order_id);
$order_items = $order->get_items();
foreach ( $order_items as $order_item_id => $order_item) {
	wc_delete_order_item($order_item_id);
}

$order->calculate_taxes();
$order->calculate_totals();
$order->save();

$order = wc_get_order($order_id);
$order->add_product( wc_get_product($product_id), 1, [
		//'subtotal'     => $price, // e.g. 32.95
		'total'        => $price, // e.g. 32.95
	] );

$order->calculate_taxes();
$order->calculate_totals();
$order->save();



?>