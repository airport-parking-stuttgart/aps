<?php

$base_url = $_SERVER['HTTP_HOST'];
if($base_url == "airport-parking-stuttgart.de"){
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



		$d = date('Y-m-d', strtotime($d . ' -1 day'));
		foreach($mails as $val){
			if($val->dateto == $d){
				if($val->sent_reviewmail == 0){
					
					$wpdb->update($wpdb->prefix . 'itweb_orders', array(
						'sent_reviewmail' => 1
					), array(
						'order_id' => $val->order_id
					));
								
					$to = array(get_post_meta($val->order_id, '_billing_email', true));
					$subject = '[APS] Ihre Erfahrung ist uns wichtig';
					$body = "<h3>Teilen Sie uns bitte Ihre Erfahrung mit.</h3>
					<p>Sehr geehrte Damen und Herren,,</p>
					<p>wie war Ihre Erfahrung bei Airport-Parking-Stuttgart GmbH?</p>
					<p>Wir versuchen ständig, uns zu verbessern und freuen uns, wenn Sie uns Ihr Feedback mitteilen könnten. 
					Es wäre toll, wenn Sie sich kurz die Zeit nehmen, um eine Google-Bewertung zu schreiben, denn damit helfen Sie uns und auch anderen Kunden.</p>
					<p><a href='https://maps.app.goo.gl/ZAVb1KkHqWz5rgfu8'>Jetzt bewerten</a> <br><br>Vielen Dank im Voraus für Ihre Bewertung!</p>
					<p>Sie haben noch Fragen? Schreiben Sie uns einfach eine E-Mail an
						<a href='mailto:info@airport-parking-stuttgart.de'>info@airport-parking-stuttgart.de</a> oder rufen Sie uns
						unter <a href='tel:+49(0) 711 22 051 245'>+49(0) 711 22 051 245</a> an.<br>
					Montag bis Freitag von 11:00 bis 19:00 Uhr.
					   Aus dem dt. Festnetz zum Ortstarif. Mobilfunkkosten abweichend.</p>
					<p>Mit freundlichen Grüßen</p>
					<p>APS Airport-Parking-Stuttgart GmbH<br>Raiffeisenstraße 18, 70794 Filderstadt<br>
					<a href='https://airport-parking-stuttgart.de/'>https://airport-parking-stuttgart.de/</a></p>";
					
					$headers = array('Content-Type: text/html; charset=UTF-8');
					 
					wp_mail( $to, $subject, $body, $headers );
				}
			}
		}
	}
}
?>