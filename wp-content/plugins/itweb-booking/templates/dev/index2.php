<?php


/*
$aps_parkinglot_bookings = array(


);

global $wpdb;
foreach($aps_parkinglot_bookings as $k){
        
		if($k['meta_value'] == 'Barzahlung'){
			$barzahlung = $k['Betrag'];
			$mv = 0;
		}			
		else{
			$barzahlung = 0;
			$mv = $k['Betrag'];
		}	
		
		$wpdb->update('59hkh_itweb_orders', [
			'b_total' => $barzahlung,
			'm_v_total' => $mv
        ], ['order_id' => $k['post_id']]);
	
}

/*
global $wpdb;
foreach($aps_parkinglot_bookings as $k){
	if($k['order_to'] != $k['meta_to']){
		if($k['meta_to'] != null){
			//$wpdb->update('59hkh_itweb_orders', [
			//    'date_from' => date('Y-m-d H:i', strtotime($k['meta_from'] . ' ' . $k['meta_time_from'])),
			//	'date_to' => date('Y-m-d H:i', strtotime($k['meta_to'] . ' ' . $k['meta_time_to']))            
			//	], ['order_id' => $k['order_id']]);
				echo "array('order_id' => '".$k['order_id']."','order_from' => '".$k['order_from']."','order_to' => '".$k['order_to']."','order_time_from' => '".$k['order_time_from']."','order_time_to' => '".$k['order_time_to']."','meta_from' => '".$k['meta_from']."','meta_to' => '".$k['meta_to']."','meta_time_from' => '".$k['meta_time_from']."','meta_time_to' => '".$k['meta_time_to']."'), <br>";
		}
	}
	
}


/*
global $wpdb;
foreach($aps_parkinglot_bookings as $k){
	global $wpdb;
	$order_id = $wpdb->get_row("
	SELECT pm.post_id
	FROM 59hkh_postmeta pm 
	WHERE pm.meta_key = 'token' and pm.meta_value = '" . $k['meta_value'] . "'
	");
	
	if($order_id != null){
		$woo_order = wc_get_order($order_id->post_id);
		
		if ( $woo_order->has_status( 'cancelled' ) ){

			echo $k['meta_value'] . "<br>";
		}
		
		//Database::getInstance()->importOrder($k);
	}
}

/*
foreach($aps_parkinglot_bookings as $k){
	global $wpdb;
	$order_id = $wpdb->get_row("
	SELECT pm.post_id
	FROM 59hkh_postmeta pm 
	WHERE pm.meta_key = 'token' and pm.meta_value = '" . $k['meta_value'] . "'");

	//Database::getInstance()->importOrder($k);
}

/*
foreach($aps_parkinglot_bookings as $k){
	global $wpdb;
	$order_id = $wpdb->get_row("
	SELECT pm.post_id
	FROM 59hkh_postmeta pm 
	WHERE pm.meta_key = 'token' and pm.meta_value = '" . $k['meta_value'] . "'");
	
	
	if($order_id->post_id != null){
		$woo_order = wc_get_order($order_id->post_id);
		
		if ( $woo_order->has_status( 'processing' ) ) {
			$woo_order->update_status( 'cancelled' );
			echo $k['meta_value'] . "<br>";
		}
	}
	
}




/*
  
  
global $wpdb;  
foreach($aps_parkinglot_bookings as $k){
	$woo_order = wc_get_order($k['order_id']);
		$order_items = $woo_order->get_items();
		foreach ( $order_items as $key => $value ) {
			$order_item_id = $key;
			   $product_value = $value->get_data();
			   $product_id    = $product_value['product_id']; 
		}
		$product = wc_get_product( $product_id );
		$price = number_format((float)$k['parkingCosts'] / 70 * 100, 2, '.', '');
		$amount = number_format((float)$price / 100 * 30, 2, '.', '');
		$price = number_format((float)$price / 119 * 100, 2, '.', '');
		$order_items[ $order_item_id ]->set_total( $price );
		$woo_order->calculate_taxes();
		$woo_order->calculate_totals();
		$woo_order->save();
		
		Database::getInstance()->updateBookingMeta($k['order_id'], 'provision', $amount);
		$wpdb->update('59hkh_wc_order_product_lookup', ['product_net_revenue' => number_format((float)$price, 2, '.', ''), 'product_gross_revenue' => number_format((float)($price / 100 * 119), 2, '.', ''), 'tax_amount' => number_format((float)($price / 100 * 19), 2, '.', '')], ['order_id' => $k['order_id']]);
}
*/

/*
$aps_parkinglot_bookings = array(


);

*/



/*

foreach($aps_parkinglot_bookings as $k){
	global $wpdb;
	$order_id = $wpdb->get_row("
	SELECT pm.post_id
	FROM 59hkh_postmeta pm 
	WHERE pm.meta_key = 'token' and pm.meta_value = '" . $k['bookingCode'] . "'
	");
	$price = $wpdb->get_row("
	SELECT opl.product_gross_revenue
	FROM 59hkh_wc_order_product_lookup opl
	WHERE opl.order_id = '" . $order_id->post_id . "'
	");
	
		echo "array('order_id' => '" . $order_id->post_id . "','parkingCosts' => '" . number_format((float)$k['parkingCosts'], 2, '.', '') . "','product_gross_revenue' => '" . number_format((float)$price->product_gross_revenue, 2, '.', '') . "'), <br>";
}



$aps_parkinglot_bookings = array(

);

foreach($aps_parkinglot_bookings as $k){
	Database::getInstance()->importOrder($k);
}




$aps_parkinglot_bookings = array(

);

$d = 0;
foreach($aps_parkinglot_bookings as $k){
	global $wpdb;
	$order_id[$d] = $wpdb->get_row("
	SELECT pm.post_id, o.date_from, o.date_to
	FROM 59hkh_postmeta pm 
	inner join 59hkh_itweb_orders o on o.order_id = pm.post_id
	WHERE pm.meta_key = 'token' and pm.meta_value = '" . $k['bookingCode'] . "'");
	$d++;
	//if($order_id == null){
		//$d .= $k['bookingCode'] . "<br>";
	//}
}

echo "<pre>";
print_r($order_id);
echo "</pre>";


// Check Provision
global $wpdb;
$provisions = $wpdb->get_results("
SELECT o.order_id, pl.product_net_revenue, round(pl.product_net_revenue / 100 * 20, 2)as cp,
(select round(om.meta_value, 2) from 59hkh_itweb_orders_meta om where om.meta_key = 'provision' and om.order_id = o.order_id) as p
FROM 59hkh_itweb_orders o 
inner join 59hkh_wc_order_product_lookup pl on pl.order_id = o.order_id
inner join 59hkh_wc_order_stats ps on ps.order_id = o.order_id

WHERE ps.status = 'wc-processing' and o.product_id = 595
");


foreach($provisions as $k){
	if($k->cp != $k->p){
		echo $k->order_id . " " . get_post_meta($k->order_id, 'token')[0] . ", cp: " . $k->cp . ", p: ". $k->p . "<br>";
		//if($k->p == '0.00' && $k->cp != '0.00'){
			Database::getInstance()->updateBookingMeta($k->order_id, 'provision', $k->cp);
		//}
		/*
		if($k->cp == '0.00' && $k->p != '0.00'){
			
			$netto = number_format((float)$k->p / 20 *100,4, '.', '');
			
			$woo_order = wc_get_order($k->order_id);
			$order_items = $woo_order->get_items();
			foreach ( $order_items as $key => $value ) {
				$order_item_id = $key;
				   $product_value = $value->get_data();
				   $product_id    = $product_value['product_id']; 
			}
			$product = wc_get_product( $product_id );
			
			$order_items[ $order_item_id ]->set_total( $netto );
			$woo_order->calculate_taxes();
			$woo_order->calculate_totals();
			$woo_order->save();			
		}
		
	}
}
*/
/*
$aps_parkinglot_bookings = array(


);

global $wpdb;
foreach($aps_parkinglot_bookings as $k){
		
		Database::getInstance()->importOrder($k);
	
}*/


/*
$datefrom = $dateFrom = date_format(date_create('2021-11-02'), 'Y-m-d');
$dateto = $dateTo = date_format(date_create('2021-11-09'), 'Y-m-d');
$proId = $id = 537;

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
		}
		else
			$price = (float)$row['day_' . $days];
		
		
		global $wpdb;
        $sql = "select * from {$wpdb->prefix}itweb_discounts where product_id = $id
        and DATEDIFF(`interval_from`, now()) > `days_before`
        and (
            '$dateFrom'  between interval_from and interval_to
            || '$dateTo'  between interval_from and interval_to
        )
        ";

        $discounts = $wpdb->get_results($sql);
		$lot = Database::getInstance()->getParklotByProductId($id);
        foreach ($discounts as $discount) {
            $orders = Orders::getOrdersByProductId($id, $dateFrom, $dateTo);
            if (($lot->contigent - count($orders)) < $discount->discount_contigent) {
                echo ($lot->contigent - count($orders)) . " " .$discount->discount_contigent ;
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
        }
 /*
		$content = ob_get_clean();
		// instantiate and use the dompdf class
		$options = new Options();
		$options->set('isRemoteEnabled', true);
		$dompdf = new Dompdf($options);
		$dompdf->loadHtml($content);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A4', 'portrait');

		// Render the HTML as PDF
		$dompdf->render();

		$file = $dompdf->output();
			$fileName = 'amh-rechnung-'.$month.'-'.$year;
		if(!file_exists(ABSPATH . 'wp-content/uploads/transfere-invoices')){
			mkdir(ABSPATH . 'wp-content/uploads/transfere-invoices');
		}
		$filePath = ABSPATH . 'wp-content/uploads/transfere-invoices/' . $fileName . '.pdf';
		$pdf = fopen($filePath, 'w');
		fwrite($pdf, $file);
		fclose($pdf);
		
$headers = array('Content-Type: text/html; charset=UTF-8');
$attachments = array(WP_CONTENT_DIR . '/uploads/transfere-invoices/'. $fileName . '.pdf');
wp_mail('it@a-p-germany.de', $fileName, $content, $headers, $attachments);



global $wpdb;
$send = 1;
if($send == 1){
	$sql = "select date(ito.date_to) as dateto, ito.order_id, pp.post_name, ito.sent_reviewmail, pl.parklot
	from 59hkh_itweb_orders ito
	inner join 59hkh_posts pp on pp.ID = ito.product_id
	inner join 59hkh_posts po on po.ID = ito.order_id
	inner join 59hkh_itweb_parklots pl on pl.product_id = ito.product_id
	where ito.deleted = 0 and ito.sent_reviewmail = 0 and po.post_status = 'wc-processing' 
	AND (ito.product_id = 537 OR ito.product_id = 592 OR ito.product_id = 619 OR ito.product_id = 873 OR ito.product_id = 537)
	AND date(ito.date_to) >= '2021-11-01'";
	$mails = $wpdb->get_results($sql);



	$d = date('Y-m-d', strtotime($d . ' -90 day'));
	$d2 = date('Y-m-d', strtotime($d2 . ' -1 day'));
	foreach($mails as $val){
		if($val->dateto > $d && $val->dateto <= $d2){
			if($val->sent_reviewmail == 0){

				
				
				$wpdb->update($wpdb->prefix . 'itweb_orders', array(
					'sent_reviewmail' => 1
				), array(
					'order_id' => $val->order_id
				));
				
							
				$to = array(get_post_meta($val->order_id, '_billing_email', true));
				$subject = '[APS] Ihre Erfahrung ist uns wichtig';
				$Salutation = "Sehr geehrte Damen und Herren,";
				$body = "<h3>Teilen Sie uns bitte Ihre Erfahrung mit.</h3>
						<p>" . $Salutation . ",</p>
						<p>wie war Ihre Erfahrung bei ".$val->parklot."?</p>
						<p>Wir versuchen ständig, uns zu verbessern und freuen uns, wenn Sie uns Ihr Feedback mitteilen könnten. 
						Es wäre toll, wenn Sie sich kurz die Zeit nehmen, um eine Bewertung bei uns zu schreiben, denn damit helfen Sie uns und auch anderen Kunden.</p>
						<p><a href='https://airport-parking-stuttgart.de/product/" . $val->post_name . "/?rating=1'>Jetzt bewerten</a> <br>Vielen Dank im Voraus für Ihre Bewertung!</p>
						<p>Sie haben noch Fragen? Schreiben Sie uns einfach eine E-Mail an
							<a href='mailto:info@airport-parking-stuttgart.de'>info@airport-parking-stuttgart.de</a> oder rufen Sie uns
							unter <a href='tel:+49(0) 711 22 051 245'>+49(0) 711 22 051 245</a> an.<br>
						Montag bis Freitag von 08:00 bis 18:00 Uhr.
						   Aus dem dt. Festnetz zum Ortstarif. Mobilfunkkosten abweichend.</p>
						<p>Mit freundlichen Grüßen</p>
						<p>APS Airport-Parking-Stuttgart GmbH<br>Raiffeisenstraße 18, 70794 Filderstadt<br>
						<a href='https://airport-parking-stuttgart.de/'>https://airport-parking-stuttgart.de/</a></p>			
					";
				
				$headers = array('Content-Type: text/html; charset=UTF-8');
				 
				wp_mail( $to, $subject, $body, $headers );
			}
		}
	}
}


global $wpdb;
$aps_parkinglot_bookings = array(


);
foreach($aps_parkinglot_bookings as $k){
	
	$order_id = $wpdb->get_row("
	SELECT pm.post_id
	FROM 59hkh_postmeta pm 
	WHERE pm.meta_key = 'token' and pm.meta_value = '" . $k['Buchung'] . "'
	");
	
	if($order_id->post_id){
				
		// Set Processing
		$order = new WC_Order($order_id->post_id);
		$order->update_status( 'processing' );
				
		// Set Price
		$woo_order = wc_get_order($order_id->post_id);		
		$order_items = $woo_order->get_items();
		foreach ( $order_items as $key => $value ) {
			$order_item_id = $key;
			   $product_value = $value->get_data();
			   $product_id    = $product_value['product_id']; 
		}
		$product = wc_get_product( $product_id );
		$price = number_format((float)$k['Netto'],4, '.', '');
		$order_items[ $order_item_id ]->set_total( $price );
		$woo_order->calculate_taxes();
		$woo_order->calculate_totals();
		$woo_order->save();
		
		// Update order Table
		$wpdb->update('59hkh_itweb_orders', [
			'product_id' => $k['PID'],
			'Code' => $k['Code']			
		], ['order_id' => $order_id->post_id]);
		
		// Update wc_order_product_lookup Table
		$wpdb->update('59hkh_wc_order_product_lookup', [
			'product_id' => $k['PID']			
		], ['order_id' => $order_id->post_id]);
		
		// Update woocommerce_order_items Table
		$wpdb->update('59hkh_woocommerce_order_items', [
			'order_item_name' => $k['Name']			
		], ['order_id' => $order_id->post_id]);
		
		// Update Provision Table
		$wpdb->update('59hkh_itweb_orders_meta', [
			'meta_value' => $k['Prov']			
		], ['order_id' => $order_id->post_id,
			'meta_key' => 'provision']);
	}
}
*/

/*

$order = wc_get_order(25293);
$order_items = $order->get_items();
foreach ( $order_items as $order_item_id => $order_item) {
	$product_name = $order_item->get_name();
	$product_id = $order_item->get_product_id();
	$product = $order_item->get_product();
	$product_price = $order_item->get_total() + $order_item->get_total_tax();
	
		wc_delete_order_item($order_item_id);
}
$order->calculate_taxes();
$order->calculate_totals();
$order->save();


$order = wc_get_order(25293);
$price = 73.00 / 119 * 100;
$order->add_product( wc_get_product(3082), 1, [
	//'subtotal'     => $price, // e.g. 32.95
	'total'        => $price, // e.g. 32.95
] );

$order->calculate_taxes();
$order->calculate_totals();
$order->save();
*/
?>