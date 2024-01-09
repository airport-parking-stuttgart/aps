<?php

global $wpdb;

$months = array('1' => 'Januar', '2' => 'Fabruar', '3' => 'März', '4' => 'April', '5' => 'Mai', '6' => 'Juni',
				'7' => 'Juli', '8' => 'August', '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Dezember');

$clients = Database::getInstance()->getAllClients();
$brokers = Database::getInstance()->getBrokers();

$dateto = date('Y-m-d');
$datefrom = date('Y-m-d');

$dateParklots = Database::getInstance()->getParkotsWithOrdersData($datefrom);

$date[0] = $dateto;
$date[1] = $dateto;
$allContingent = Database::getInstance()->getAllContingent($date);

foreach($allContingent as $ac){
	$set_con[$ac->date."_".$ac->product_id] = $ac->contingent;
}

//echo "<pre>"; print_r($companies); echo "</pre>"; 
?>
<style>
.block{
	border: 1px solid #ccc9c9;
	margin: 0px 5px;
	max-height: 402px;
}

.purple{
	color: #9900ff;
}
.green{
	background: #ccffcc;
}
.yellow{
	background: #ffffcc;
}
.red{
	color: #990000;
}
</style>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">google.charts.load('current', {'packages': ['corechart'], 'language': 'de'});</script>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-title itweb_adminpage_head">
		<h3>Dashboard</h3>
	</div>
	<div class="page-body">
		<div class="row ui-lotdata-block ui-lotdata-block-next">
			<h5 class="ui-lotdata-title">Weitere Statistiken</h5>
			<div class="col-sm-12 col-md-12 ui-lotdata">
				<div class="row">
					<div class="col-sm-12 col-md-2">                    
						<a href="<?php echo '/wp-admin/admin.php?page=bookings' ?>" target="_blank" class="btn btn-primary d-block w-100" >Buchungen</a>
					</div>
				</div>
			</div>
		</div>
		
		<div class="row">					
			<div class="col-sm-12 col-md-4 block">
				<h4>Buchungen heute</h4>
				<table class="table table-sm">
					<thead>
						<tr>
							<th>Vermittler</th>
							<th>Anzahl</th>
							<th>Umsatz</th>
							<th>d.B.U</th>
							<th>d.B.T</th>
							<th>d.B.Pers</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($clients as $client): ?>
						<?php
							$umsatz = $days = $pers = 0;
							$filter['buchung_von'] = $datefrom;
							$filter['buchung_bis'] = date('Y-m-d', strtotime($dateto));
							$filter['orderBy'] = "Buchungsdatum";
							$filter['betreiber'] = strtolower($client->short);
							$allorders = Database::getInstance()->get_bookinglist($filter);
							$sum_orders += count($allorders);						
							foreach($allorders as $order){
								$umsatz +=  get_post_meta($order->order_id, '_order_total', true);								
								$days += getDaysBetween2Dates(new DateTime(get_post_meta($order->order_id, 'Anreisedatum', true)), new DateTime(get_post_meta($order->order_id, 'Abreisedatum', true)));
								$pers +=  get_post_meta($order->order_id, 'Personenanzahl', true);
								$sum_umsatz += get_post_meta($order->order_id, '_order_total', true);;
								$sum_days += getDaysBetween2Dates(new DateTime(get_post_meta($order->order_id, 'Anreisedatum', true)), new DateTime(get_post_meta($order->order_id, 'Abreisedatum', true)));
								$sum_pers += get_post_meta($order->order_id, 'Personenanzahl', true);
								$d_umsatz_heute[$client->short] +=  number_format(get_post_meta($order->order_id, '_order_total', true), 0, ",", ".");
							}
						?>
							<tr>
								<td><?php echo $client->short ?></td>
								<td><?php echo count($allorders) ?></td>
								<td><?php echo number_format($umsatz, 2, ",", ".") ?></td>
								<td><?php echo count($allorders) != 0 ? number_format($umsatz / count($allorders), 2, ",", ".") : "0" ?></td>
								<td><?php echo count($allorders) != 0 ? number_format($days / count($allorders), 1, ",", ".") : "0" ?></td>
								<td><?php echo count($allorders) != 0 ? number_format($pers / count($allorders), 2, ",", ".") : "0" ?></td>
							</tr>
						<?php endforeach; ?>
						<?php foreach($brokers as $broker): ?>
							<?php
							$umsatz = $days = $pers = 0;
							$filter['buchung_von'] = $datefrom;
							$filter['buchung_bis'] = date('Y-m-d', strtotime($dateto));
							$filter['orderBy'] = "Buchungsdatum";
							$filter['betreiber'] = strtolower($broker->short);
							$allorders = Database::getInstance()->get_bookinglist($filter);
							$sum_orders += count($allorders);
							foreach($allorders as $order){
								$umsatz +=  get_post_meta($order->order_id, '_order_total', true);								
								$days += getDaysBetween2Dates(new DateTime(get_post_meta($order->order_id, 'Anreisedatum', true)), new DateTime(get_post_meta($order->order_id, 'Abreisedatum', true)));
								$pers +=  get_post_meta($order->order_id, 'Personenanzahl', true);
								$sum_umsatz += get_post_meta($order->order_id, '_order_total', true);;
								$sum_days += getDaysBetween2Dates(new DateTime(get_post_meta($order->order_id, 'Anreisedatum', true)), new DateTime(get_post_meta($order->order_id, 'Abreisedatum', true)));
								$sum_pers += get_post_meta($order->order_id, 'Personenanzahl', true);
								$d_umsatz_heute[$broker->short] +=  number_format(get_post_meta($order->order_id, '_order_total', true), 0, ",", ".");
							}
							?>						
							<tr>
								<td><?php echo $broker->short ?></td>
								<td><?php echo count($allorders) ?></td>
								<td><?php echo number_format($umsatz, 2, ",", ".") ?></td>
								<td><?php echo count($allorders) != 0 ? number_format($umsatz / count($allorders), 2, ",", ".") : "0" ?></td>
								<td><?php echo count($allorders) != 0 ? number_format($days / count($allorders), 1, ",", ".") : "0" ?></td>
								<td><?php echo count($allorders) != 0 ? number_format($pers / count($allorders), 2, ",", ".") : "0" ?></td>
							</tr>
						<?php endforeach; ?>
						<tr>
							<td><strong>Summe</strong></td>
							<td><strong><?php echo $sum_orders ?></strong></td>
							<td><strong><?php echo number_format($sum_umsatz, 2, ",", ".") ?></strong></td>
							<td><strong><?php echo $sum_orders != 0 ? number_format($sum_umsatz / $sum_orders, 2, ",", ".") : "0" ?></strong></td>
							<td><strong><?php echo $sum_orders != 0 ? number_format($sum_days / $sum_orders, 1, ",", ".") : "0" ?></strong></td>
							<td><strong><?php echo $sum_orders != 0 ? number_format($sum_pers / $sum_orders, 2, ",", ".") : "0" ?></strong></td>
						</tr>
					</tbody>
				</table>
			</div>
			<script type="text/javascript">
				google.charts.setOnLoadCallback(drawVisualization_buchungen_heute);
				function drawVisualization_buchungen_heute() {
					// Some raw data (not necessarily accurate)
					var data = google.visualization.arrayToDataTable([
						['Betreiber', 'Umsatz', {role: 'annotation'}],
						
						<?php
						foreach ($d_umsatz_heute as $key => $val) {
							echo "['{$key}', {$val}, {$val}],";
						}
						?>
					]);

					var options = {
						'height': 400,
						title: 'Umsatz heute',
						vAxis: {title: 'Umsatz', textStyle: {fontSize: 16}},
						seriesType: 'bars',
						legend: 'none',		
						series: {5: {type: 'line'}},
						annotations: {
							textStyle: {
							  fontSize: 16,
							  auraColor: 'none',
							},
							formatOptions: { groupingSymbol: '.' }
						}
					};
				
					var chart = new google.visualization.ComboChart(document.getElementById('buchungen_heute'));
					chart.draw(data, options);
				}
			</script>
			<div class="col-sm-12 col-md-5 block">
				<div class="chart" id="buchungen_heute"></div>
			</div>
		</div>
		<br>
		<div class="row">
			<div class="col-sm-12 col-md-4 block">
				<h4>Kontingent Stand heute</h4>
				<table class="table table-sm">
					<thead>
						<tr>
							<th>Standort</th>
							<th>Soll</th>
							<th>Ist</th>
							<th>Prozent belegt</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Parkhaus PH</td>
							<?php
								$parklot = Database::getInstance()->getParklotByProductId(537);
								if($set_con[date('Y-m-d')."_537"] != null)
									$con_ph += $set_con[date('Y-m-d')."_537"];
								else
									$con_ph += $parklot->contigent;
								$parklot = Database::getInstance()->getParklotByProductId(621);
								if($set_con[date('Y-m-d')."_621"] != null)
									$con_ph += $set_con[date('Y-m-d')."_621"];
								else
									$con_ph += $parklot->contigent;
							?>
							<td><?php echo $con_ph ?></td>
							<?php
							foreach ($dateParklots as $dateParklot){
								if($dateParklot->order_lot == 1 || $dateParklot->order_lot == 3 || $dateParklot->order_lot == 5 || $dateParklot->order_lot == 6)
									$used_ph += $dateParklot->used;
							}
							
							if($con_ph != 0){
								if(number_format($used_ph / $con_ph * 100, 2,".",".") >= 70 && number_format($used_ph / $con_ph * 100, 2,".",".") < 85)
									$text_color = "purple";
								elseif(number_format($used_ph / $con_ph * 100, 2,".",".") >= 85)
									$text_color = "red";										
								else
									$text_color = "";
							}
							else
								$text_color = "";
							if(number_format($used_ph / $con_ph * 100, 2,".",".") == 100)
								$background = "green";
							elseif($used_ph > $con_ph)
								$background = "yellow";
							else
								$background = "";
							
							?>
							<td><?php echo $used_ph ?></td>
							<td class="<?php echo $text_color ?> <?php echo $background ?>"><?php echo $con_ph != 0 ? number_format($used_ph / $con_ph * 100, 2, ",", ".") : "0"?></td>
						</tr>
						<tr>
							<td>Parkhaus OD</td>
							<?php
								$parklot = Database::getInstance()->getParklotByProductId(592);
								if($set_con[date('Y-m-d')."_592"] != null)
									$con_od += $set_con[date('Y-m-d')."_592"];
								else
									$con_od += $parklot->contigent;
								
								$parklot = Database::getInstance()->getParklotByProductId(624);
								if($set_con[date('Y-m-d')."_624"] != null)
									$con_od += $set_con[date('Y-m-d')."_624"];
								else
									$con_od += $parklot->contigent;
							?>
							<td><?php echo $con_od ?></td>
							<?php
							foreach ($dateParklots as $dateParklot){
								if($dateParklot->order_lot == 10 || $dateParklot->order_lot == 12 || $dateParklot->order_lot == 14)
									$used_od += $dateParklot->used;
							}
							
							if($con_od != 0){
								if(number_format($used_od / $con_od * 100, 2,".",".") >= 70 && number_format($used_od / $con_od * 100, 2,".",".") < 85)
									$text_color = "purple";
								elseif(number_format($used_od / $con_od * 100, 2,".",".") >= 85)
									$text_color = "red";										
								else
									$text_color = "";
							}
							else
								$text_color = "";
							if(number_format($used_od / $con_od * 100, 2,".",".") == 100)
								$background = "green";
							elseif($used_od > $con_od)
								$background = "yellow";
							else
								$background = "";
							?>
							<td><?php echo $used_od ?></td>
							<td class="<?php echo $text_color ?> <?php echo $background ?>"><?php echo $con_od != 0 ? number_format($used_od / $con_od * 100, 2, ",", ".") : "0"?></td>
						</tr>
						<tr>
							<td>Sielmingen</td>
							<?php
								$parklot = Database::getInstance()->getParklotByProductId(873);
								if($set_con[date('Y-m-d')."_873"] != null)
									$con_sie += $set_con[date('Y-m-d')."_873"];
								else
									$con_sie += $parklot->contigent;
								$parklot = Database::getInstance()->getParklotByProductId(901);
								if($set_con[date('Y-m-d')."_901"] != null)
									$con_sie += $set_con[date('Y-m-d')."_901"];
								else
									$con_sie += $parklot->contigent;
								$parklot = Database::getInstance()->getParklotByProductId(45856);
								if($set_con[date('Y-m-d')."_45856"] != null)
									$con_sie += $set_con[date('Y-m-d')."_45856"];
								else
									$con_sie += $parklot->contigent;
							?>
							<td><?php echo $con_sie ?></td>
							<?php
							foreach ($dateParklots as $dateParklot){
								if($dateParklot->order_lot == 30 || $dateParklot->order_lot == 32 || $dateParklot->order_lot == 34 || $dateParklot->order_lot == 35)
									$used_sie += $dateParklot->used;
							}
							
							if($con_sie != 0){
								if(number_format($used_sie / $con_sie * 100, 2,".",".") >= 70 && number_format($used_sie / $con_sie * 100, 2,".",".") < 85)
									$text_color = "purple";
								elseif(number_format($used_sie / $con_sie * 100, 2,".",".") >= 85)
									$text_color = "red";										
								else
									$text_color = "";
							}
							else
								$text_color = "";
							if(number_format($used_sie / $con_sie * 100, 2,".",".") == 100)
								$background = "green";
							elseif($used_sie > $con_sie)
								$background = "yellow";
							else
								$background = "";
							?>
							<td><?php echo $used_sie ?></td>
							<td class="<?php echo $text_color ?> <?php echo $background ?>"><?php echo $con_sie != 0 ? number_format($used_sie / $con_sie * 100, 2, ",", ".") : "0"?></td>
						</tr>
						<tr>
							<td>Ostfildern PH</td>
							<?php
								$parklot = Database::getInstance()->getParklotByProductId(24222);
								if($set_con[date('Y-m-d')."_24222"] != null)
									$con_ost += $set_con[date('Y-m-d')."_24222"];
								else
									$con_ost += $parklot->contigent;
								$parklot = Database::getInstance()->getParklotByProductId(24261);
								if($set_con[date('Y-m-d')."_24261"] != null)
									$con_ost += $set_con[date('Y-m-d')."_24261"];
								else
									$con_ost += $parklot->contigent;
								$parklot = Database::getInstance()->getParklotByProductId(41402);
								if($set_con[date('Y-m-d')."_41402"] != null)
									$con_ost += $set_con[date('Y-m-d')."_41402"];
								else
									$con_ost += $parklot->contigent;
							?>
							<td><?php echo $con_ost ?></td>
							<?php
							foreach ($dateParklots as $dateParklot){
								if($dateParklot->order_lot == 40 || $dateParklot->order_lot == 42 || $dateParklot->order_lot == 44 || $dateParklot->order_lot == 47)
									$used_ost += $dateParklot->used;
							}
							
							if($con_ost != 0){
								if(number_format($used_ost / $con_ost * 100, 2,".",".") >= 70 && number_format($used_ost / $con_ost * 100, 2,".",".") < 85)
									$text_color = "purple";
								elseif(number_format($used_ost / $con_ost * 100, 2,".",".") >= 85)
									$text_color = "red";										
								else
									$text_color = "";
							}
							else
								$text_color = "";
							if(number_format($used_ost / $con_ost * 100, 2,".",".") == 100)
								$background = "green";
							elseif($used_ost > $con_ost)
								$background = "yellow";
							else
								$background = "";
							?>
							<td><?php echo $used_ost ?></td>
							<td class="<?php echo $text_color ?> <?php echo $background ?>"><?php echo $con_ost != 0 ? number_format($used_ost / $con_ost * 100, 2, ",", ".") : "0"?></td>
						</tr>
						<tr>
							<td>Ostfildern PP</td>
							<?php
								$parklot = Database::getInstance()->getParklotByProductId(24226);
								if($set_con[date('Y-m-d')."_24226"] != null)
									$con_nu += $set_con[date('Y-m-d')."_24226"];
								else
									$con_nu += $parklot->contigent;
								$parklot = Database::getInstance()->getParklotByProductId(24263);
								if($set_con[date('Y-m-d')."_24263"] != null)
								$con_nu += $set_con[date('Y-m-d')."_24263"];
								else
									$con_nu += $parklot->contigent;
								$parklot = Database::getInstance()->getParklotByProductId(41403);
								if($set_con[date('Y-m-d')."_41403"] != null)
									$con_nu += $set_con[date('Y-m-d')."_41403"];
								else
									$con_nu += $parklot->contigent;
							?>
							<td><?php echo $con_nu ?></td>
							<?php
							foreach ($dateParklots as $dateParklot){
								if($dateParklot->order_lot == 50 || $dateParklot->order_lot == 52 || $dateParklot->order_lot == 54 || $dateParklot->order_lot == 55)
									$used_nu += $dateParklot->used;
							}
							
							if($con_nu != 0){
								if(number_format($used_nu / $con_nu * 100, 2,".",".") >= 70 && number_format($used_nu / $con_nu * 100, 2,".",".") < 85)
									$text_color = "purple";
								elseif(number_format($used_nu / $con_nu * 100, 2,".",".") >= 85)
									$text_color = "red";										
								else
									$text_color = "";
							}
							else
								$text_color = "";
							if(number_format($used_nu / $con_nu * 100, 2,".",".") == 100)
								$background = "green";
							elseif($used_nu > $con_nu)
								$background = "yellow";
							else
								$background = "";
							?>
							<td><?php echo $used_nu ?></td>
							<td class="<?php echo $text_color ?> <?php echo $background ?>"><?php echo $con_nu != 0 ? number_format($used_nu / $con_nu * 100, 2, ",", ".") : "0"?></td>
						</tr>
						<tr>
							<?php $sum_con = $con_ph + $con_od + $con_sie + $con_ost + $con_nu; ?>
							<?php $sum_used = $used_ph + $used_od + $used_sie + $used_ost + $used_nu; ?>
							<?php
							if($sum_con != 0){
								if(number_format($sum_used / $sum_con * 100, 2,".",".") >= 70 && number_format($sum_used / $sum_con * 100, 2,".",".") < 85)
									$text_color = "purple";
								elseif(number_format($sum_used / $sum_con * 100, 2,".",".") >= 85)
									$text_color = "red";										
								else
									$text_color = "";
							}
							else
								$text_color = "";
							if(number_format($sum_used / $sum_con * 100, 2,".",".") == 100)
								$background = "green";
							elseif($sum_used > $sum_con)
								$background = "yellow";
							else
								$background = "";
							?>
							<td><strong>Summe</strong></td>
							<td><strong><?php echo $sum_con ?></strong></td>
							<td><strong><?php echo $sum_used ?></strong></td>
							<td class="<?php echo $text_color ?> <?php echo $background ?>"><strong><?php echo $sum_con != 0 ? number_format($sum_used / $sum_con * 100, 2, ",", ".") : "0"?></strong></td>
						</tr>
					</tbody>
				</table>
			</div>
			<script type="text/javascript">
				google.charts.setOnLoadCallback(drawVisualization_kontingent);
				function drawVisualization_kontingent() {
					// Some raw data (not necessarily accurate)
					var data = google.visualization.arrayToDataTable([
						['Betreiber', 'Gebucht', 'Frei'],
						<?php
						$free_ph = $con_ph - $used_ph;
						if($free_ph <= 0)
							$free_ph = 0;
						$free_od = $con_od - $used_od;
						if($free_od <= 0)
							$free_od = 0;
						$free_sie = $con_sie - $used_sie;
						if($free_sie <= 0)
							$free_sie = 0;
						$free_ost = $con_ost - $used_ost;
						if($free_ost <= 0)
							$free_ost = 0;
						$free_nu = $con_nu - $used_nu;
						if($free_nu <= 0)
							$free_nu = 0;
						echo "['PH', {$used_ph}, {$free_ph}],";
						echo "['OD', {$used_od}, {$free_od}],";
						echo "['SIE', {$used_sie}, {$free_sie}],";
						echo "['OST', {$used_ost}, {$free_ost}],";
						echo "['NÜ', {$used_nu}, {$free_nu}]";
						
						?>
						
					]);

					var options = {
						'height': 400,
						isStacked: 'percent',
						title: 'Kontingent Stand heute',
						vAxis: {title: 'Kontingent', textStyle: {fontSize: 16}},
						seriesType: 'bars',			
						series: {5: {type: 'line'}},
						annotations: {
							textStyle: {
							  fontSize: 16,
							  auraColor: 'none',
							}
						}
					};
					var chart = new google.visualization.ComboChart(document.getElementById('kontingent'));
					chart.draw(data, options);
				}
			</script>
			<div class="col-sm-12 col-md-6 block">				
				<div class="chart" id="kontingent"></div>
			</div>
		</div>
		<br>
		<?php
		$c_month = date('n');
		$c_year = date('Y');
		$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $c_month, $c_year);
		
		$dateto = date('Y-m-d', strtotime(date($c_year."-".$c_month."-".$daysInMonth)));
		$datefrom = date('Y-m-d', strtotime(date($c_year."-".$c_month."-01")));
		?>

		<div class="row">					
			<div class="col-sm-12 col-md-4 block">
				<h4>Buchungen Monat <?php echo $months[$c_month] ?></h4>
				<table class="table table-sm">
					<thead>
						<tr>
							<th>Vermittler</th>
							<th>Anzahl</th>
							<th>Umsatz</th>
							<th>d.B.U</th>
							<th>d.B.T</th>
							<th>d.B.Pers</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($clients as $client): ?>
						<?php
							$umsatz = $days = $pers = 0;
							$filter['buchung_von'] = $datefrom;
							$filter['buchung_bis'] = date('Y-m-d', strtotime($dateto));
							$filter['orderBy'] = "Buchungsdatum";
							$filter['betreiber'] = strtolower($client->short);
							$allorders = Database::getInstance()->get_bookinglist($filter);
							$sum_orders_m += count($allorders);						
							foreach($allorders as $order){
								$umsatz +=  get_post_meta($order->order_id, '_order_total', true);								
								$days += getDaysBetween2Dates(new DateTime(get_post_meta($order->order_id, 'Anreisedatum', true)), new DateTime(get_post_meta($order->order_id, 'Abreisedatum', true)));
								$pers +=  get_post_meta($order->order_id, 'Personenanzahl', true);
								$sum_umsatz_m += get_post_meta($order->order_id, '_order_total', true);;
								$sum_days_m += getDaysBetween2Dates(new DateTime(get_post_meta($order->order_id, 'Anreisedatum', true)), new DateTime(get_post_meta($order->order_id, 'Abreisedatum', true)));
								$sum_pers_m += get_post_meta($order->order_id, 'Personenanzahl', true);
								$d_umsatz[$client->short] +=  number_format(get_post_meta($order->order_id, '_order_total', true), 0, ",", ".");
							}
							if($d_umsatz[$client->short] == null)
								$d_umsatz[$client->short] = 0;
						?>
							<tr>
								<td><?php echo $client->short ?></td>
								<td><?php echo count($allorders) ?></td>
								<td><?php echo number_format($umsatz, 2, ",", ".") ?></td>
								<td><?php echo count($allorders) != 0 ? number_format($umsatz / count($allorders), 2, ",", ".") : "0" ?></td>
								<td><?php echo count($allorders) != 0 ? number_format($days / count($allorders), 1, ",", ".") : "0" ?></td>
								<td><?php echo count($allorders) != 0 ? number_format($pers / count($allorders), 2, ",", ".") : "0" ?></td>
							</tr>
						<?php endforeach; ?>
						<?php foreach($brokers as $broker): ?>
							<?php
							$umsatz = $days = $pers = 0;
							$filter['buchung_von'] = $datefrom;
							$filter['buchung_bis'] = date('Y-m-d', strtotime($dateto));
							$filter['orderBy'] = "Buchungsdatum";
							$filter['betreiber'] = strtolower($broker->short);
							$allorders = Database::getInstance()->get_bookinglist($filter);
							$sum_orders_m += count($allorders);
							foreach($allorders as $order){
								$umsatz +=  get_post_meta($order->order_id, '_order_total', true);								
								$days += getDaysBetween2Dates(new DateTime(get_post_meta($order->order_id, 'Anreisedatum', true)), new DateTime(get_post_meta($order->order_id, 'Abreisedatum', true)));
								$pers +=  get_post_meta($order->order_id, 'Personenanzahl', true);
								$sum_umsatz_m += get_post_meta($order->order_id, '_order_total', true);;
								$sum_days_m += getDaysBetween2Dates(new DateTime(get_post_meta($order->order_id, 'Anreisedatum', true)), new DateTime(get_post_meta($order->order_id, 'Abreisedatum', true)));
								$sum_pers_m += get_post_meta($order->order_id, 'Personenanzahl', true);
								$d_umsatz[$broker->short] +=  number_format(get_post_meta($order->order_id, '_order_total', true), 0, ",", ".");
							}
							if($d_umsatz[$broker->short] == null)
								$d_umsatz[$broker->short] = 0;
							?>						
							<tr>
								<td><?php echo $broker->short ?></td>
								<td><?php echo count($allorders) ?></td>
								<td><?php echo number_format($umsatz, 2, ",", ".") ?></td>
								<td><?php echo count($allorders) != 0 ? number_format($umsatz / count($allorders), 2, ",", ".") : "0" ?></td>
								<td><?php echo count($allorders) != 0 ? number_format($days / count($allorders), 1, ",", ".") : "0" ?></td>
								<td><?php echo count($allorders) != 0 ? number_format($pers / count($allorders), 2, ",", ".") : "0" ?></td>
							</tr>
						<?php endforeach; ?>
						<tr>
							<td><strong>Summe</strong></td>
							<td><strong><?php echo $sum_orders_m ?></strong></td>
							<td><strong><?php echo number_format($sum_umsatz_m, 2, ",", ".") ?></strong></td>
							<td><strong><?php echo $sum_orders_m != 0 ? number_format($sum_umsatz_m / $sum_orders_m, 2, ",", ".") : "0" ?></strong></td>
							<td><strong><?php echo $sum_orders_m != 0 ? number_format($sum_days_m / $sum_orders_m, 1, ",", ".") : "0" ?></strong></td>
							<td><strong><?php echo $sum_orders_m != 0 ? number_format($sum_pers_m / $sum_orders_m, 2, ",", ".") : "0" ?></strong></td>
						</tr>
					</tbody>
				</table>
			</div>
			<script type="text/javascript">
				google.charts.setOnLoadCallback(drawVisualization_umsatz_monat);
				function drawVisualization_umsatz_monat() {
					// Some raw data (not necessarily accurate)
					var data = google.visualization.arrayToDataTable([
						['Betreiber', 'Umsatz', { role: 'annotation'}],
						
						<?php
						foreach ($d_umsatz as $key => $val) {
							
							echo "['{$key}', {$val}, {$val}],";
						}
						?>
					]);

					var options = {
						'height': 400,
						title: 'Umsatz Monat <?php echo $months[$c_month] ?>',
						vAxis: {title: 'Ist-Umsatz', textStyle: {fontSize: 16}},
						seriesType: 'bars',
						legend: 'none',		
						series: {5: {type: 'line'}},
						annotations: {
							textStyle: {
							  fontSize: 16,
							  auraColor: 'none',
							}
						}
					};

					var chart = new google.visualization.ComboChart(document.getElementById('umsatz_monat'));
					chart.draw(data, options);
				}
			</script>
			<div class="col-sm-12 col-md-5 block">
				<div class="chart" id="umsatz_monat"></div>
			</div>
		</div>
		<br>
		<div class="row">					
			<div class="col-sm-12 col-md-4 block">
				<h4>Anreise Monat <?php echo $months[$c_month] ?></h4>
				<table class="table table-sm">
					<thead>
						<tr>
							<th>Vermittler</th>
							<th>Buchungen</th>
							<th>Ist-Umsatz</th>
							<th>d.B.U</th>
							<th>d.B.T</th>
							<th>d.B.Pers</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($clients as $client): ?>
						<?php
							$umsatz = $days = $pers = 0;
							unset($filter['buchung_von']);
							unset($filter['buchung_bis']);
							$filter['datum_von'] = $datefrom;
							$filter['datum_bis'] = $dateto;
							$filter['orderBy'] = "Anreisedatum";
							$filter['betreiber'] = strtolower($client->short);
							$allorders = Database::getInstance()->get_bookinglist($filter);
							$sum_orders_im += count($allorders);						
							foreach($allorders as $order){
								$umsatz +=  get_post_meta($order->order_id, '_order_total', true);								
								$days += getDaysBetween2Dates(new DateTime(get_post_meta($order->order_id, 'Anreisedatum', true)), new DateTime(get_post_meta($order->order_id, 'Abreisedatum', true)));
								$pers +=  get_post_meta($order->order_id, 'Personenanzahl', true);
								$sum_umsatz_im += get_post_meta($order->order_id, '_order_total', true);;
								$sum_days_im += getDaysBetween2Dates(new DateTime(get_post_meta($order->order_id, 'Anreisedatum', true)), new DateTime(get_post_meta($order->order_id, 'Abreisedatum', true)));
								$sum_pers_im += get_post_meta($order->order_id, 'Personenanzahl', true);
								$d_ist_umsatz[$client->short] +=  number_format(get_post_meta($order->order_id, '_order_total', true), 0, ",", ".");
								$d_pers[$client->short] +=  get_post_meta($order->order_id, 'Personenanzahl', true);
								if(get_post_meta($order->order_id, 'Personenanzahl', true) == 1){
									$pers_anz[$client->short]['1 Person']++;
									$d_pers_anz['1 Person']++;
								}									
								if(get_post_meta($order->order_id, 'Personenanzahl', true) == 2){
									$pers_anz[$client->short]['2 Personen']++;
									$d_pers_anz['2 Personen']++;
								}									
								if(get_post_meta($order->order_id, 'Personenanzahl', true) == 3){
									$pers_anz[$client->short]['3 Personen']++;
									$d_pers_anz['3 Personen']++;
								}									
								if(get_post_meta($order->order_id, 'Personenanzahl', true) == 4){
									$pers_anz[$client->short]['4 Personen']++;
									$d_pers_anz['4 Personen']++;
								}									
								if(get_post_meta($order->order_id, 'Personenanzahl', true) > 4){
									$pers_anz[$client->short]['Mehr als 4 Personen']++;
									$d_pers_anz['Mehr als 4 Personen']++;
								}
							}
							if($d_ist_umsatz[$client->short] == null)
								$d_ist_umsatz[$client->short] = 0;
							if($d_pers[$client->short] == null)
								$d_pers[$client->short] = 0;
							if($d_pers_anz['1 Person'] == null)
								$d_pers_anz['1 Person'] = 0;
							if($d_pers_anz['2 Personen'] == null)
								$d_pers_anz['2 Personen'] = 0;
							if($d_pers_anz['3 Personen'] == null)
								$d_pers_anz['3 Personen'] = 0;
							if($d_pers_anz['4 Personen'] == null)
								$d_pers_anz['4 Personen'] = 0;
							if($d_pers_anz['Mehr als 4 Personen'] == null)
								$d_pers_anz['Mehr als 4 Personen'] = 0;
							if($pers_anz[$client->short]['1 Person'] == null)
								$pers_anz[$client->short]['1 Person'] = 0;
							if($pers_anz[$client->short]['2 Personen'] == null)
								$pers_anz[$client->short]['2 Personen'] = 0;
							if($pers_anz[$client->short]['3 Personen'] == null)
								$pers_anz[$client->short]['3 Personen'] = 0;
							if($pers_anz[$client->short]['4 Personen'] == null)
								$pers_anz[$client->short]['4 Personen'] = 0;
							if($pers_anz[$client->short]['Mehr als 4 Personen'] == null)
								$pers_anz[$client->short]['Mehr als 4 Personen'] = 0;
						?>
							<tr>
								<td><?php echo $client->short ?></td>
								<td><?php echo count($allorders) ?></td>
								<td><?php echo number_format($umsatz, 2, ",", ".") ?></td>
								<td><?php echo count($allorders) != 0 ? number_format($umsatz / count($allorders), 2, ",", ".") : "0" ?></td>
								<td><?php echo count($allorders) != 0 ? number_format($days / count($allorders), 1, ",", ".") : "0" ?></td>
								<td><?php echo count($allorders) != 0 ? number_format($pers / count($allorders), 2, ",", ".") : "0" ?></td>
							</tr>
						<?php endforeach; ?>
						<?php foreach($brokers as $broker): ?>
							<?php
							$umsatz = $days = $pers = 0;
							unset($filter['buchung_von']);
							unset($filter['buchung_bis']);
							$filter['datum_von'] = $datefrom;
							$filter['datum_bis'] = $dateto;
							$filter['orderBy'] = "Anreisedatum";
							$filter['betreiber'] = strtolower($broker->short);
							$allorders = Database::getInstance()->get_bookinglist($filter);
							$sum_orders_im += count($allorders);
							foreach($allorders as $order){
								$umsatz +=  get_post_meta($order->order_id, '_order_total', true);								
								$days += getDaysBetween2Dates(new DateTime(get_post_meta($order->order_id, 'Anreisedatum', true)), new DateTime(get_post_meta($order->order_id, 'Abreisedatum', true)));
								$pers +=  get_post_meta($order->order_id, 'Personenanzahl', true);
								$sum_umsatz_im += get_post_meta($order->order_id, '_order_total', true);;
								$sum_days_im += getDaysBetween2Dates(new DateTime(get_post_meta($order->order_id, 'Anreisedatum', true)), new DateTime(get_post_meta($order->order_id, 'Abreisedatum', true)));
								$sum_pers_im += get_post_meta($order->order_id, 'Personenanzahl', true);
								$d_ist_umsatz[$broker->short] +=  number_format(get_post_meta($order->order_id, '_order_total', true), 0, ",", ".");
								$d_pers[$broker->short] +=  get_post_meta($order->order_id, 'Personenanzahl', true);
								if(get_post_meta($order->order_id, 'Personenanzahl', true) == 1){
									$pers_anz[$broker->short]['1 Person']++;
									$d_pers_anz['1 Person']++;
								}									
								if(get_post_meta($order->order_id, 'Personenanzahl', true) == 2){
									$pers_anz[$broker->short]['2 Personen']++;
									$d_pers_anz['2 Personen']++;
								}									
								if(get_post_meta($order->order_id, 'Personenanzahl', true) == 3){
									$pers_anz[$broker->short]['3 Personen']++;
									$d_pers_anz['3 Personen']++;
								}									
								if(get_post_meta($order->order_id, 'Personenanzahl', true) == 4){
									$pers_anz[$broker->short]['4 Personen']++;
									$d_pers_anz['4 Personen']++;
								}									
								if(get_post_meta($order->order_id, 'Personenanzahl', true) > 4){
									$pers_anz[$broker->short]['Mehr als 4 Personen']++;
									$d_pers_anz['Mehr als 4 Personen']++;
								}
									
							}
							if($d_ist_umsatz[$broker->short] == null)
								$d_ist_umsatz[$broker->short] = 0;
							if($d_pers[$broker->short] == null)
								$d_pers[$broker->short] = 0;
							if($d_pers_anz['1 Person'] == null)
								$d_pers_anz['1 Person'] = 0;
							if($d_pers_anz['2 Personen'] == null)
								$d_pers_anz['2 Personen'] = 0;
							if($d_pers_anz['3 Personen'] == null)
								$d_pers_anz['3 Personen'] = 0;
							if($d_pers_anz['4 Personen'] == null)
								$d_pers_anz['4 Personen'] = 0;
							if($d_pers_anz['Mehr als 4 Personen'] == null)
								$d_pers_anz['Mehr als 4 Personen'] = 0;
							if($pers_anz[$broker->short]['1 Person'] == null)
								$pers_anz[$broker->short]['1 Person'] = 0;
							if($pers_anz[$broker->short]['2 Personen'] == null)
								$pers_anz[$broker->short]['2 Personen'] = 0;
							if($pers_anz[$broker->short]['3 Personen'] == null)
								$pers_anz[$broker->short]['3 Personen'] = 0;
							if($pers_anz[$broker->short]['4 Personen'] == null)
								$pers_anz[$broker->short]['4 Personen'] = 0;
							if($pers_anz[$broker->short]['Mehr als 4 Personen'] == null)
								$pers_anz[$broker->short]['Mehr als 4 Personen'] = 0;
							?>						
							<tr>
								<td><?php echo $broker->short ?></td>
								<td><?php echo count($allorders) ?></td>
								<td><?php echo number_format($umsatz, 2, ",", ".") ?></td>
								<td><?php echo count($allorders) != 0 ? number_format($umsatz / count($allorders), 2, ",", ".") : "0" ?></td>
								<td><?php echo count($allorders) != 0 ? number_format($days / count($allorders), 1, ",", ".") : "0" ?></td>
								<td><?php echo count($allorders) != 0 ? number_format($pers / count($allorders), 2, ",", ".") : "0" ?></td>
							</tr>
						<?php endforeach; ?>
						<tr>
							<td><strong>Summe</strong></td>
							<td><strong><?php echo $sum_orders_im ?></strong></td>
							<td><strong><?php echo number_format($sum_umsatz_im, 2, ",", ".") ?></strong></td>
							<td><strong><?php echo $sum_orders_im != 0 ? number_format($sum_umsatz_im / $sum_orders_im, 2, ",", ".") : "0" ?></strong></td>
							<td><strong><?php echo $sum_orders_im != 0 ? number_format($sum_days_im / $sum_orders_im, 1, ",", ".") : "0" ?></strong></td>
							<td><strong><?php echo $sum_orders_im != 0 ? number_format($sum_pers_im / $sum_orders_im, 2, ",", ".") : "0" ?></strong></td>
						</tr>
					</tbody>
				</table>
			</div>
			<script type="text/javascript">
				google.charts.setOnLoadCallback(drawVisualization_ist_umsatz_monat);
				function drawVisualization_ist_umsatz_monat() {
					// Some raw data (not necessarily accurate)
					var data = google.visualization.arrayToDataTable([
						['Betreiber', 'Umsatz', { role: 'annotation'}],
						
						<?php
						foreach ($d_ist_umsatz as $key => $val) {
							
							echo "['{$key}', {$val}, {$val}],";
						}
						?>
					]);

					var options = {
						'height': 400,
						title: 'Ist-Umsatz Monat <?php echo $months[$c_month] ?>',
						vAxis: {title: 'Ist-Umsatz', textStyle: {fontSize: 16}},
						seriesType: 'bars',
						legend: 'none',		
						series: {5: {type: 'line'}},
						annotations: {
							textStyle: {
							  fontSize: 16,
							  auraColor: 'none',
							}
						}
					};

					var chart = new google.visualization.ComboChart(document.getElementById('ist_umsatz_monat'));
					chart.draw(data, options);
				}
			</script>
			<div class="col-sm-12 col-md-5 block">
				<div class="chart" id="ist_umsatz_monat"></div>
			</div>
		</div>
		<br>
		<div class="row">					
			<div class="col-sm-12 col-md-4 block">
				<h4>Buchungen mit Anzahl Personen Anreise Monat <?php echo $months[$c_month] ?></h4>
				<table class="table table-sm">
					<thead>
						<tr>
							<th>Vermittler</th>
							<th>1 Pers.</th>
							<th>2 Pers.</th>
							<th>3 Pers.</th>
							<th>4 Pers.</th>
							<th>Mehr als 4 Pers.</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($clients as $client): ?>
							<tr>
								<td><?php echo $client->short ?></td>
								<td><?php echo $pers_anz[$client->short]['1 Person'] ?></td>
								<td><?php echo $pers_anz[$client->short]['2 Personen'] ?></td>
								<td><?php echo $pers_anz[$client->short]['3 Personen'] ?></td>
								<td><?php echo $pers_anz[$client->short]['4 Personen'] ?></td>
								<td><?php echo $pers_anz[$client->short]['Mehr als 4 Personen'] ?></td>
							</tr>
						<?php endforeach; ?>
						<?php foreach($brokers as $broker): ?>
							<tr>
								<td><?php echo $broker->short ?></td>
								<td><?php echo $pers_anz[$broker->short]['1 Person'] ?></td>
								<td><?php echo $pers_anz[$broker->short]['2 Personen'] ?></td>
								<td><?php echo $pers_anz[$broker->short]['3 Personen'] ?></td>
								<td><?php echo $pers_anz[$broker->short]['4 Personen'] ?></td>
								<td><?php echo $pers_anz[$broker->short]['Mehr als 4 Personen'] ?></td>
							</tr>
						<?php endforeach; ?>
						<tr>
							<td><strong>Summe</strong></td>
							<td><strong><?php echo $d_pers_anz['1 Person'] ?></strong></td>
							<td><strong><?php echo $d_pers_anz['2 Personen'] ?></strong></td>
							<td><strong><?php echo $d_pers_anz['3 Personen'] ?></strong></td>
							<td><strong><?php echo $d_pers_anz['4 Personen'] ?></strong></td>
							<td><strong><?php echo $d_pers_anz['Mehr als 4 Personen'] ?></strong></td>
						</tr>
					</tbody>
				</table>
			</div>
			
			<script type="text/javascript">			
				google.charts.setOnLoadCallback(drawVisualization_personen);
				function drawVisualization_personen() {
					// Some raw data (not necessarily accurate)
					var data = google.visualization.arrayToDataTable([
						['Personen', 'Anzahl', { role: 'annotation'}],
						
						<?php							
							echo "['1 Person', {$d_pers_anz['1 Person']}, {$d_pers_anz['1 Person']}],";
							echo "['2 Personen', {$d_pers_anz['2 Personen']}, {$d_pers_anz['2 Personen']}],";
							echo "['3 Personen', {$d_pers_anz['3 Personen']}, {$d_pers_anz['3 Personen']}],";
							echo "['4 Personen', {$d_pers_anz['4 Personen']}, {$d_pers_anz['4 Personen']}],";
							echo "['Mehr als 4 Personen', {$d_pers_anz['Mehr als 4 Personen']}, {$d_pers_anz['Mehr als 4 Personen']}],";
						?>
					]);

					var options = {
						'height': 400,
						title: 'Buchungen mit Anzahl Personen Anreise Monat <?php echo $months[$c_month] ?>',
						vAxis: {title: 'Personen', textStyle: {fontSize: 16}},
						seriesType: 'bars',
						legend: 'none',		
						series: {5: {type: 'line'}},
						annotations: {
							textStyle: {
							  fontSize: 16,
							  auraColor: 'none',
							}
						}
					};

					var chart = new google.visualization.ComboChart(document.getElementById('personen'));
					chart.draw(data, options);
				}			
			</script>
		
			<div class="col-sm-12 col-md-5 block">
				<div class="chart" id="personen"></div>
			</div>
		</div>
		<br>
		<div class="row">
			<div class="col-sm-12 col-md-4 block">
				<h4>Rentabilität</h4>
				<table class="table table-sm">
					<thead>
						<tr>
							<th>Standort</th>
							<th>Umsatz</th>
							<th>Miete</th>
							<th>PK Kosten</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Parkhaus PH</td>
						</tr>
						<tr>
							<td>Parkhaus DO</td>
						</tr>
						<tr>
							<td>Sielmingen</td>
						</tr>
						<tr>
							<td>Ostfildern PH</td>
						</tr>
						<tr>
							<td>Ostfildern NÜ</td>
						</tr>
						<tr>
							<td><strong>Summe</strong></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
