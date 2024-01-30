<?php

class Orders
{
    static function getOrdersByProductId($id, $dateFrom, $dateTo, $discountId = null)
    {
        global $wpdb;
        $ordersTable = $wpdb->prefix . 'itweb_orders';
        $sql = "select * from $ordersTable where product_id = $id and deleted = 0
        and (
            '$dateFrom' between date_from and date_to
            || '$dateTo' between date_from and date_to
        )";

        if($discountId){
            $sql .= " and discount_id = $discountId";
        }

        return $wpdb->get_results($sql);
    }

    static function cancel($order_id)
    {
        $order = wc_get_order($order_id); // The WC_Order object instance
        $itweb_order = self::getByOrderId($order_id);
        $hourdiff = round((strtotime($itweb_order->date_from) - strtotime(date('Y-m-d H:i')))/3600, 1);

        // Loop through Order items ("line_item" type)
        foreach ($order->get_items() as $item_id => $item) {
            $product = wc_get_product($item->get_product_id());
            $new_product_price = (double)$item->get_total(); // A static replacement product price
            $product_quantity = (int)$item->get_quantity(); // product Quantity

            $orderCancellation = Database::getInstance()->getOrderCancellationByProductId($product->get_id());

            if($hourdiff > (int)$orderCancellation->hours_before){
                $new_product_price = 0;
            }else{
                if ($orderCancellation->type === 'fix') {
                    if ($orderCancellation < $orderCancellation->value) {
                        $new_product_price = 0;
                    } else {
                        $new_product_price -= $orderCancellation->value;
                    }
                } else {
                    $tmp = $new_product_price - ($new_product_price * $orderCancellation->value / 100);
                    $new_product_price = (double)$item->get_total() - $tmp;
                }
            }
            // The new line item price
            $new_line_item_price = $new_product_price * $product_quantity;

            // Set the new price
            $item->set_subtotal($new_line_item_price);
            $item->set_total($new_line_item_price);

            // Make new taxes calculations
            $item->calculate_taxes();

            $item->save(); // Save line item data
        }
        // Make the calculations  for the order and SAVE
        $order->calculate_totals();
    }

    static function updateOrder($data, $condition)
    {
        global $wpdb;
        return $wpdb->update($wpdb->prefix . 'itweb_orders', $data, $condition);
    }

    static function getByOrderId($id){
        global $wpdb;
        return $wpdb->get_row("select * from {$wpdb->prefix}itweb_orders where order_id = $id");
    }
	
	static function getOrderByOrderId($id){
        global $wpdb;
        return $wpdb->get_row("select * from {$wpdb->prefix}itweb_orders where order_id = $id");
    }
	
	static function getOrderByToken($token){
        global $wpdb;
        return $wpdb->get_row("select * from {$wpdb->prefix}itweb_orders where token = '$token'");
    }

    static function deleteWCOrders($ids){
        foreach($ids as $id){
            wp_delete_post($id, true);
        }
    }

    static function createWCOrder($product){
        $order_userid = get_current_user_id();
        // build order data
        $order_data = array(
            'post_name' => 'order-' . time(), //'order-jun-19-2014-0648-pm'
            'post_type' => 'shop_order',
            'post_title' => 'Order &ndash; ' . time(), //'June 19, 2014 @ 07:19 PM'
            'post_status' => 'wc-processing',
            'ping_status' => 'Processing',
            'post_excerpt' => '',
            'post_author' => $order_userid,
            'post_password' => uniqid('order_'),   // Protects the post just in case
            'post_date' => date('Y-m-d H:i:s e'), //'order-jun-19-2014-0648-pm'
            'comment_status' => 'open'
        );

        // create order
        $order_id = wp_insert_post($order_data, true);

        $order = wc_get_order($order_id);

        // Add the product to the order
        $order->add_product($product, 1);
        $order->calculate_totals(); // updating totals

        $order->save(); // Save the order data
        return $order;
    }
}