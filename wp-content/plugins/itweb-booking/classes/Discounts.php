<?php

class Discounts
{
    static function checkDiscounts($id, $price, $dateFrom, $dateTo)
    {
		$parklot = Database::getInstance()->getParklotByProductId($id);
        $discounts = self::getAvailableDiscounts($id, $dateFrom, $dateTo);
		//die(print_r($price, true));
		
        foreach ($discounts as $discount) {
            $orders = Orders::getOrdersByProductId($id, $dateFrom, $dateTo, $discount->id);
            if (count($orders) >= $discount->discount_contigent) {
                continue;
            }
			
			if($parklot->parkhaus == 'Parkhaus überdacht' || $parklot->parkhaus == 'Parkplatz überdacht')
				$value = $discount->value_ud;
			else
				$value = $discount->value_pp;
			
            if ($discount->type === 'fix') {
                if ($price < $value) {
                    $price = 0;
                } else {
                    $price -= $value;
                }
            } else {
                $price = $price - ($price * $value / 100);
            }
			break;
        }
        return number_format($price, 0, ".", ".");
    }
	
	static function getDiscounts($id, $price, $dateFrom, $dateTo)
    {
		$parklot = Database::getInstance()->getParklotByProductId($id);
        $discounts = self::getAvailableDiscounts($id, $dateFrom, $dateTo);
        $data = [];

        foreach ($discounts as $discount) {
            $orders = Orders::getOrdersByProductId($id, $dateFrom, $dateTo, $discount->id);
            if (count($orders) >= $discount->discount_contigent) {
                continue;
            }
			if($parklot->parkhaus == 'Parkhaus überdacht' || $parklot->parkhaus == 'Parkplatz überdacht')
				$value = $discount->value_ud;
			else
				$value = $discount->value_pp;
			
            if ($discount->type === 'fix') {
                if ($price < $value) {
                    $price = 0;
                } else {
                    $price -= $value;
                }
				$data[0] = $value . "€";
            } else {
				$data[0] = $value . "%";
                $price = number_format($price - ($price * $value / 100), 0, ".", ".");
            }
			$data[1] = $discount->name;
            $data[2] = $discount->message;
			$data[3] = $discount->cancel;
            $data[4] = $discount->id;
			break;
        }
        return $data;
    }

    static function getAvailableDiscounts($id, $dateFrom, $dateTo){
        global $wpdb;
        $dateFromFormatted = dateFormat($dateFrom);
        $dateToFormatted = dateFormat($dateTo);
		
        $sql = "select * from {$wpdb->prefix}itweb_discounts where product_id LIKE '%$id%'
        and DATEDIFF('$dateFromFormatted', now()) >= `days_before`
		and ((DATEDIFF('$dateToFormatted', '$dateFromFormatted')+1) >= `min_days` OR `min_days` IS NULL OR `min_days` = 0)
		and ((DATEDIFF('$dateToFormatted', '$dateFromFormatted')+1) <= `max_days` OR `max_days` IS NULL OR `max_days` = 0)
		and (
            '$dateFromFormatted'  between interval_from and interval_to
            || '$dateToFormatted'  between interval_from and interval_to
        )
        ";

        return $wpdb->get_results($sql);
    }
}