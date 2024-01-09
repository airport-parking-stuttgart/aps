<?php
global $wpdb;

/*
$pm = array(




);

foreach($pm as $val){
	$order_id = $wpdb->get_row("
	SELECT pm.meta_id as tk, pm.meta_value, date(orders.date_from) as date_from, date(orders.date_to) as date_to
	FROM 59hkh_postmeta pm
	INNER JOIN 59hkh_itweb_orders orders ON orders.order_id = pm.post_id
	WHERE pm.meta_key = 'token' and pm.meta_value = '".$val[meta_value]."'
	");
	//echo "<pre>"; print_r($order_id->tk); echo "</pre>";
	if($order_id->tk != null && ($val['datefrom'] != $order_id->date_from || $val['dateto'] != $order_id->date_to))
		echo $val['meta_value'] . " APG: " . $val['post_id'] . " " . $val['datefrom'] . " " . $val['dateto'] . " APS: " . $order_id->tk . " " . $order_id->date_from . " " . $order_id->date_to . "<br>";


}
*/

$datefrom = get_post_meta(31770, 'Anreisedatum', true);
$dateTo = get_post_meta(31770, 'Abreisedatum', true);
$dayso = getDaysBetween2Dates(new DateTime($datefrom), new DateTime($dateTo)) . "<br>";

$ndateTo = '18.12.2022';
$ndateTo = date('Y-m-d', strtotime($ndateTo));
$daysn = getDaysBetween2Dates(new DateTime($datefrom), new DateTime($ndateTo)) . "<br>";
if($daysn > $dayso){
	echo $daysn - $dayso;
}
?>
