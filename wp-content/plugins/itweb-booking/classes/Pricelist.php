<?php

class Pricelist
{
    static function calculate($proId, $datefrom, $dateto): float
    {
        global $wpdb;
        $datefrom = new DateTime($datefrom);
        $dateto = new DateTime($dateto);
        $sql = "select events.datefrom, prices.* from {$wpdb->prefix}itweb_events events, {$wpdb->prefix}itweb_prices prices
        where Date(datefrom) = Date('" . $datefrom->format('Y-m-d H:i:s') . "') 
        and events.price_id = prices.id and events.product_id = {$proId};";
        $row = $wpdb->get_row($sql);
        $row = json_decode(json_encode($row), true);
        $days = ($datefrom->diff($dateto)->days) + 1;
		
		if($days > 30){
			$sql = "select extraPrice_perDay from {$wpdb->prefix}itweb_parklots where product_id = {$proId};";
			$extraDays = $days - 30;
			$extraPrice = $wpdb->get_row($sql);
			$sumPrice = $extraPrice->extraPrice_perDay * $extraDays;
			$price = (float)$row['day_' . '30'] + $sumPrice;
			if((float)$row['day_' . '30'] == null)
				$price = 0;
		}
		else
			$price = (float)$row['day_' . $days];
		
		$sql = "select events.dateto, prices.* from {$wpdb->prefix}itweb_events events, {$wpdb->prefix}itweb_prices prices
        where Date(dateto) = Date('" . $dateto->format('Y-m-d H:i:s') . "') 
        and events.price_id = prices.id and events.product_id = {$proId};";
        $row = $wpdb->get_row($sql);
		
		if($row == null)
			$price = 0;
		
        return $price;
    }

    static function calculateAndDiscount($proId, $datefrom, $dateto)
    {
        $price = self::calculate($proId, $datefrom, $dateto);
        return Discounts::checkDiscounts($proId, $price, dateFormat($datefrom), dateFormat($dateto));
    }
}