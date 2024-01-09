<?php

global $wpdb;


$parklots = Database::getInstance()->getAllLotsNoTransfer();

$aktuellesDatum = date('Y-m-d');

$today = date('Y-m-d', strtotime('+1 month', strtotime($aktuellesDatum)));
$de_today = date('d.m.Y', strtotime('+1 month', strtotime($aktuellesDatum)));
$date = [
        $today,
        $today
    ];

$allContingent = Database::getInstance()->getAllContingent($date);

foreach($allContingent as $ac){
	$set_con[$ac->date."_".$ac->product_id] = $ac->contingent;
}

$c_parklots = count($parklots);

$c = 1;
$sql = "SELECT '".$today."' AS date, ";

foreach ($parklots as $parklot){
	$sql .="
		(SELECT pl.parklot_short FROM ".$wpdb->prefix."itweb_parklots pl WHERE pl.parklot_short = '".$parklot->parklot_short."') AS 'parklot_short_".$parklot->parklot_short."',
		(SELECT pl.group_id FROM ".$wpdb->prefix."itweb_parklots pl WHERE pl.parklot_short = '".$parklot->parklot_short."') AS 'group_id_".$parklot->parklot_short."',
		(SELECT COUNT(orders.id) FROM ".$wpdb->prefix."itweb_orders orders
		LEFT JOIN ".$wpdb->prefix."posts s ON s.ID = orders.order_id
		LEFT JOIN ".$wpdb->prefix."itweb_parklots pl ON orders.product_id = pl.product_id
		WHERE date('".$today."') BETWEEN date(orders.date_from) AND date(orders.date_to) AND orders.product_id = pl.product_id
		AND orders.deleted = 0 AND s.post_status = 'wc-processing' AND pl.parklot_short = '".$parklot->parklot_short."'
		) AS 'used ".$parklot->parklot_short."'
	";
	if($c < $c_parklots)
		$sql .= ",";
	$c++;
}
$row[$today] = $wpdb->get_row($sql);


$product_groups = Database::getInstance()->getProductGroups();
foreach ($product_groups as $group){
	$child_product_groups = Database::getInstance()->getChildProductGroupsByPerentId($group->id);
	if(count($child_product_groups) > 0){
		foreach ($child_product_groups as $child_group){
			$groups[$child_group->id] = Database::getInstance()->getParklotIdsByChildProductGroupId($child_group->id);
		}
	}
	else{
		$groups[$group->id] = Database::getInstance()->getParklotIdsByChildProductGroupId($group->id);
	}
}

$body1 = $body2 = $body3 = "";
foreach($groups as $key => $group){
	$count = count($group);
	foreach($group as $parklot){ 
		if(str_contains($parklot->parklot_short, 'APG') || str_contains($parklot->parklot_short, 'APS')){ 
			continue;
		}
		if(str_contains($parklot->parklot_short, 'PA')){ 
			continue;
		}

		$lot = Database::getInstance()->getParklotByProductId($parklot->product_id);
		$selector = "used " . $parklot->parklot_short; 
		$used = $row[$today]->$selector;
 
		if(str_contains($parklot->parklot_short, 'APS')){
			$selector = "used " . str_replace("APS", "APG", $parklot->parklot_short);
			$used_apg = $row[$today]->$selector;
		}
		else
			$used_apg = 0;
		if($parklot->product_id == 621){
			$selector = "used HX PH";
			$used_hx = $row[$today]->$selector;
		}
		else
			$used_hx = 0;
		
		
		$contintent = $set_con[$today."_".$parklot->product_id] != null ? $set_con[$today."_".$parklot->product_id] : $parklot->contigent;
		$free = $contintent - $used - $used_apg - $used_hx;
		
		$per_used = $contintent != 0 ? number_format(($used + $used_hx) / $contintent * 100, 2,".",".") : "0.00";
		$per_used_apg = $contintent != 0 ? number_format($used_apg / $contintent * 100, 2,".",".") : "0.00";
		$per_used_all = $contintent != 0 ? number_format(($used + $used_apg + $used_hx) / $contintent * 100, 2,".",".") : "0.00";
		$per_free = $contintent != 0 ? number_format($free / $contintent * 100, 2,".",".") : "0.00";
																
		if($contintent != 0){
			$headers = array('Content-Type: text/html; charset=UTF-8');
			$to = "tobias.neher@holidayextras.com";
			if(number_format(($used + $used_apg + $used_hx) / $contintent * 100, 2,".",".") >= 30 && number_format(($used + $used_apg + $used_hx) / $contintent * 100, 2,".",".") < 50){
				$data[$parklot->parklot_short]['date'] = $de_today;
				$data[$parklot->parklot_short]['name'] = $lot->parklot;
				$data[$parklot->parklot_short]['contingent'] = $contintent;
				$data[$parklot->parklot_short]['used'] = ($used + $used_apg + $used_hx);				
				$data[$parklot->parklot_short]['free'] = $free;
				$data[$parklot->parklot_short]['prozent'] = $per_used_all;
				
				$betreff = $data[$parklot->parklot_short]['name'] . " zu " . $data[$parklot->parklot_short]['prozent'] . "% belegt - " . $data[$parklot->parklot_short]['date'];
				$body1 .= "<br>" . 
						"Datum: " . $data[$parklot->parklot_short]['date'] . "<br>" . 
						"Produkt: " . $data[$parklot->parklot_short]['name'] . "<br>" . 
						"Kontingent: " . $data[$parklot->parklot_short]['contingent'] . "<br>" . 
						"<span style='color: #37ad10'>Belegt: " . $data[$parklot->parklot_short]['used'] . ", in %: " . $data[$parklot->parklot_short]['prozent'] . "</span><br>" . 
						"Frei: " . $data[$parklot->parklot_short]['free'] . "<br>";
				//wp_mail($to, $betreff, $body, $headers);		
						
			}
			elseif(number_format(($used + $used_apg + $used_hx) / $contintent * 100, 2,".",".") >= 50 && number_format(($used + $used_apg + $used_hx) / $contintent * 100, 2,".",".") < 70){
				$data[$parklot->parklot_short]['date'] = $de_today;
				$data[$parklot->parklot_short]['name'] = $lot->parklot;
				$data[$parklot->parklot_short]['contingent'] = $contintent;
				$data[$parklot->parklot_short]['used'] = ($used + $used_apg + $used_hx);
				$data[$parklot->parklot_short]['free'] = $free;
				$data[$parklot->parklot_short]['prozent'] = $per_used_all;
				
				$betreff = $data[$parklot->parklot_short]['name'] . " zu " . $data[$parklot->parklot_short]['prozent'] . "% belegt - " . $data[$parklot->parklot_short]['date'];
				$body2 .= "<br>" . 
						"Datum: " . $data[$parklot->parklot_short]['date'] . "<br>" . 
						"Produkt: " . $data[$parklot->parklot_short]['name'] . "<br>" . 
						"Kontingent: " . $data[$parklot->parklot_short]['contingent'] . "<br>" . 
						"<span style='color: #ab910f'>Belegt: " . $data[$parklot->parklot_short]['used'] . ", in %: " . $data[$parklot->parklot_short]['prozent'] . "</span><br>" . 
						"Frei: " . $data[$parklot->parklot_short]['free'] . "<br>";
				//wp_mail($to, $betreff, $body, $headers);		
			}
			elseif(number_format(($used + $used_apg + $used_hx) / $contintent * 100, 2,".",".") >= 70){
				$data[$parklot->parklot_short]['date'] = $de_today;
				$data[$parklot->parklot_short]['name'] = $lot->parklot;
				$data[$parklot->parklot_short]['contingent'] = $contintent;
				$data[$parklot->parklot_short]['used'] = ($used + $used_apg + $used_hx);
				$data[$parklot->parklot_short]['free'] = $free;
				$data[$parklot->parklot_short]['prozent'] = $per_used_all;
				
				$betreff = $data[$parklot->parklot_short]['name'] . " zu " . $data[$parklot->parklot_short]['prozent'] . "% belegt - " . $data[$parklot->parklot_short]['date'];
				$body3 .= "<br>" . 
						"Datum: " . $data[$parklot->parklot_short]['date'] . "<br>" . 
						"Produkt: " . $data[$parklot->parklot_short]['name'] . "<br>" . 
						"Kontingent: " . $data[$parklot->parklot_short]['contingent'] . "<br>" . 
						"<span style='color: #9c170e'>Belegt: " . $data[$parklot->parklot_short]['used'] . ", in %: " . $data[$parklot->parklot_short]['prozent'] . "</span><br>" . 
						"Frei: " . $data[$parklot->parklot_short]['free'] . "<br>";
				//wp_mail($to, $betreff, $body, $headers);		
			}
		}							
	}							
}
//wp_mail($to, "[APS] HEX Auslastung " . $de_today, $body1 . $body2 . $body3 . "<br>", $headers);

