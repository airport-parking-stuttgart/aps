<?php

class Discounts
{
    static function checkDiscounts($id, $price, $dateFrom, $dateTo)
    {
        $discounts = self::getAvailableDiscounts($id, $dateFrom, $dateTo);
		//die(print_r($price, true));
		
        foreach ($discounts as $discount) {
            $orders = Orders::getOrdersByProductId($id, $dateFrom, $dateTo);
            if (count($orders) > $discount->discount_contigent) {
                continue;
            }
            if ($discount->type === 'fix') {
                if ($price < $discount->value) {
                    $price = 0;
                } else {
                    $price -= $discount->value;
                }
            } else {
                $price = $price - ($price * $discount->value / 100);
            }
			break;
        }
        return number_format($price, 0, ".", ".");
    }
	
	static function getDiscounts($id, $price, $dateFrom, $dateTo)
    {
        $discounts = self::getAvailableDiscounts($id, $dateFrom, $dateTo);
        $data = [];

        foreach ($discounts as $discount) {
            $orders = Orders::getOrdersByProductId($id, $dateFrom, $dateTo);
            if (count($orders) > $discount->discount_contigent) {
                continue;
            }
            if ($discount->type === 'fix') {
                if ($price < $discount->value) {
                    $price = 0;
                } else {
                    $price -= $discount->value;
                }
				$data[0] = $discount->value . "€";
            } else {
				$data[0] = $discount->value . "%";
                $price = number_format($price - ($price * $discount->value / 100), 0, ".", ".");
            }
			$data[1] = $discount->name;
            $data[2] = $discount->message;
			$data[3] = $discount->cancel;
			break;
        }
        return $data;
    }

    static function getAvailableDiscounts($id, $dateFrom, $dateTo){
        global $wpdb;
        $dateFromFormatted = dateFormat($dateFrom);
        $dateToFormatted = dateFormat($dateTo);

        $sql = "select * from {$wpdb->prefix}itweb_discounts where product_id = $id
        and DATEDIFF('$dateFromFormatted', now()) >= `days_before`
        and (
            '$dateFromFormatted'  between interval_from and interval_to
            || '$dateToFormatted'  between interval_from and interval_to
        )
        ";

        return $wpdb->get_results($sql);
    }
}