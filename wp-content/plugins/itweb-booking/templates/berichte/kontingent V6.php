<?php

global $wpdb;

if(!empty($_POST)){
	foreach($_POST as $key => $val){
		if($val == null)
			continue;
		if (str_contains($key, 'basic')){
			$basic_str = explode("_", $key);
			$basic_p = $basic_str[1];
			$basic_val = $val;
			
			Database::getInstance()->updateBasicContingent($basic_p, $basic_val);
		}
		else{
			$post_key = explode("_", $key);
			$sql_date = $post_key[0];
			$sql_p_id = $post_key[1];
		
			Database::getInstance()->saveContingent($sql_date, $sql_p_id, $val);
		}

	}
	header('Location: /wp-admin/admin.php?page=kontingent');
}

$parklots = Database::getInstance()->getAllLotsNoTransfer();
$clients = Database::getInstance()->getAllClients();

foreach($clients as $client){
	$client_products = Database::getInstance()->getClientProducts($client->id);
	
	foreach($client_products as $client_product){
		$sum_client_used[$client_product->product_id] = 0;
		$sum_client_free[$client_product->product_id] = 0;
	}
}

$brokers = Database::getInstance()->getBrokers();

foreach($brokers as $broker){
	$broker_products = Database::getInstance()->getBrokerProducts($broker->id);
	
	foreach($broker_products as $broker_product){
		$sum_broker_used[$broker_product->product_id] = 0;
		$sum_broker_free[$broker_product->product_id] = 0;
	}
}

$sum_all_used = $sum_client_used + $sum_broker_used;
$sum_all_free = $sum_client_free + $sum_broker_free;
$complete_used = $complete_free = $complete_cont = 0; 

if (isset($_GET["date"])) {
    $date = (explode(" - ", $_GET["date"]));
    $date[0] = date('Y-m-d', strtotime($date[0]));
    $date[1] = date('Y-m-d', strtotime($date[1]));
    $period = new DatePeriod(
        new DateTime($date[0]),
        new DateInterval('P1D'),
        new DateTime($date[1] . '+1 day')
    );

} else {
    $today = date('Y-m-d');
    $period = new DatePeriod(
        new DateTime($today),
        new DateInterval('P1D'),
        new DateTime($today . '+31 day')
    );

    $date = [
        date('Y-m-d'),
        date('Y-m-d', strtotime(date('Y-m-d') . "+30 day"))
    ];
}
$wochentage = array("So.", "Mo.", "Di.", "Mi.", "Do.", "Fr.", "Sa.");
$allContingent = Database::getInstance()->getAllContingent($date);

foreach($allContingent as $ac){
	$set_con[$ac->date."_".$ac->product_id] = $ac->contingent;
}

$c_parklots = count($parklots);
$i = 0;


foreach ($period as $key => $value){
	$c = 1;
	$sql = "SELECT '".$value->format('Y-m-d')."' AS date, ";
	
	foreach ($parklots as $parklot){
		$sql .="
			(SELECT pl.parklot_short FROM ".$wpdb->prefix."itweb_parklots pl WHERE pl.parklot_short = '".$parklot->parklot_short."') AS 'parklot_short_".$parklot->parklot_short."',
			(SELECT pl.group_id FROM ".$wpdb->prefix."itweb_parklots pl WHERE pl.parklot_short = '".$parklot->parklot_short."') AS 'group_id_".$parklot->parklot_short."',
			(SELECT COUNT(orders.id) FROM ".$wpdb->prefix."itweb_orders orders
			LEFT JOIN ".$wpdb->prefix."posts s ON s.ID = orders.order_id
			LEFT JOIN ".$wpdb->prefix."itweb_parklots pl ON orders.product_id = pl.product_id
			WHERE date('".$value->format('Y-m-d')."') BETWEEN date(orders.date_from) AND date(orders.date_to) AND orders.product_id = pl.product_id
			AND orders.deleted = 0 AND s.post_status = 'wc-processing' AND pl.parklot_short = '".$parklot->parklot_short."'
			) AS 'used ".$parklot->parklot_short."'
		";
		if($c < $c_parklots)
			$sql .= ",";
		$c++;
	}
	$row[$value->format('Y-m-d')] = $wpdb->get_row($sql);
}


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
$sum_rows = 0;
foreach ($period as $key => $value){
	$sum_rows++;
}
	
//echo "<pre>"; print_r($row); echo "</pre>";
?>

<style>
th, td{
	white-space: nowrap;
	padding-bottom: 0.01rem;
}
.th1{
	background: gainsboro;
	background-clip: padding-box;
	border-top: 2px solid black !important;
}
.th_date{
	background: #ddeef4;
	background-clip: padding-box;
}

.th_produkt{
	background: #f2d9d3;
	background-clip: padding-box;
}

.th2, .td2{
	min-width: 85px;
	border: 1px solid black !important;
}

.purple{
	color: #9900ff;
}
.green{
	background: #ccffcc;
	background-clip: padding-box;
}
.yellow{
	background: #ffffcc;
	background-clip: padding-box;
}
.red{
	color: #990000;
}

.ausen-border-left{
	border-left: 2px solid black !important;
}
.ausen-border-top{
	border-top: 2px solid black !important;
}
.ausen-border-bottom{
	border-bottom: 2px solid black !important;
}
.ausen-border-right{
	border-right: 2px solid black !important;
}
.produkt-trenner{
	border-left: 1.5px solid black;
}

.tr-datum-0, .tr-lots-0{
	background: #ffffff;
	background-clip: padding-box;
}

.tr-datum-1{
	background: #f4f9fb;
	background-clip: padding-box;
}
.tr-lots-1{
	background: #fbf0ee;
	background-clip: padding-box;
}

.table_div {
  width: 100%;
  max-height: 950px;
  overflow: scroll;
  position: relative;
  height: 950px;
}

.main_table{
	border-collapse: separate;
	border-spacing: 0;
	border-left: 2px solid black;
	border-right: 2px solid black;
	border-bottom: 2px solid black;
}

table {
  position: relative;
  border-collapse: collapse;
}

thead {
  position: -webkit-sticky; /* for Safari */
  position: sticky;
  top: 0;
  z-index: 1;
}

tbody {
      position: sticky;
      left: 0;
      overflow-y: scroll;
    }

th:first-child, td:first-child {
  position: sticky;
  left: 0;
  z-index: 2;
}


</style>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Kontingentübersicht</h3>
    </div>
    <div class="page-body">
        <form class="form-filter">
            <div class="row ui-lotdata-block ui-lotdata-block-next">
                <h5 class="ui-lotdata-title">Nach Datum filtern</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-md-2">
							<input type="text" class="datepicker-range form-item form-control" name="date"
								   data-multiple-dates-separator=" - " placeholder="" autocomplete="off"
								   data-from="<?php echo $date[0] ? $date[0] : '' ?>" data-to="<?php echo $date[1] ? $date[1] : '' ?>">
						</div>
						<div class="col-sm-12 col-md-1">
							<button class="btn btn-primary d-block w-100" type="submit">Filter</button>
						</div>
					</div>
				</div>
            </div>
        </form>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . "?page=kontingent"; ?>">
			<button class="btn btn-primary d-block" type="submit">Speichern</button>
			<br>
			<div class="row">
				<div class="col-sm-12 col-md-12">
					<div class="table_div">	
						<table class="table table-sm main_table"> <!-- sales-table -->
							<thead>
								<tr>
									<th class="th1">Datum</th>							
									<?php foreach($groups as $group): ?>
										<?php $pos_th1 = 0 ?>
										<?php foreach($group as $parklot): ?>
										<?php 								
											if(str_contains($parklot->parklot_short, 'APG') || str_contains($parklot->parklot_short, 'IAPS') || $parklot->product_id == 683){										
												$pos_th1++;										
												continue;
											}
											else
												$pos_th1++;
										?>
										<th class="th1"><?php echo $parklot->parklot_short ?>
											<input type="text" size="3" name="basic_<?php echo $parklot->product_id ?>" style="width: 60px; float: right; margin-right: 10px;" value="<?php echo $parklot->contigent ?>">
										</th>								
										<?php endforeach; ?>
										<?php if($pos_th1 == count($group)): ?>								
											<th class="th1">
												Gesamt
												<input type="text" size="3" style="width: 60px; float: right; margin-right: 10px; visibility: hidden;">
											</th>
											<?php $pos_th1 = 1; ?>
										<?php endif; ?>
									<?php endforeach; ?>
									<?php foreach($clients as $client) : ?>
										<th class="th1">Summe <?php echo $client->short ?></th>
									<?php endforeach; ?>
									<?php foreach($brokers as $broker) : ?>
										<?php if($broker->short == "APG") continue; ?>
										<th class="th1">Summe <?php echo $broker->short ?></th>
									<?php endforeach; ?>
									<th class="th1">Summe</th>			
								</tr>
								<tr>
									<th class="th_date">&nbsp;</th>
									<?php foreach($groups as $group): ?>
										<?php $pos_th2 = 0 ?>
										<?php foreach($group as $parklot): ?>							
										<?php 
											if(str_contains($parklot->parklot_short, 'APG') || str_contains($parklot->parklot_short, 'IAPS') || $parklot->product_id == 683){ 
												$pos_th2++;
												continue;
											}
											else
												$pos_th2++; 
										?>
										<th class="th_produkt">
											<table>
												<tr>
													<?php if(str_contains($parklot->parklot_short, 'APS')): ?>
													<td class="th2"><?php echo $parklot->parklot_short ?></td>
													<td class="th2"><?php echo str_replace("APS", "APG", $parklot->parklot_short) ?></td>
													<?php endif; ?>
													<td class="th2">Gebucht</td>
													<td class="th2">Frei</td>
													<td class="th2">Gesamt</td>
												</tr>
											</table>
										</th>								
										<?php endforeach; ?>
										<?php if($pos_th2 == count($group)): ?>
											<th class="th_produkt" style="background: <?php echo $group[0]->color ?>">
												<table>
													<tr>
														<td class="th2">Gebucht</td>
														<td class="th2">Frei</td>
														<td class="th2">Gesamt</td>
													</tr>
												</table>
											</th>
											<?php $pos_th2 = 1; ?>
										<?php endif; ?>
									<?php endforeach; ?>
									<?php foreach($clients as $client) : ?>
										<th class="th_produkt" style="background: #ddeef4;">
											<table>
												<tr>
													<td class="th2">Gebucht</td>
													<td class="th2">Frei</td>
													<td class="th2">Gesamt</td>
												</tr>
											</table>
										</th>
									<?php endforeach; ?>
									<?php foreach($brokers as $broker) : ?>
										<?php if($broker->short == "APG") continue; ?>
										<th class="th_produkt" style="background: #ddeef4;">
											<table>
												<tr>
													<td class="th2">Gebucht</td>
													<td class="th2">Frei</td>
													<td class="th2">Gesamt</td>
												</tr>
											</table>
										</th>
									<?php endforeach; ?>
									<th class="th_produkt" style="background: #ddeef4;">
										<table>
											<tr>
												<td class="th2">Gebucht</td>
												<td class="th2">Frei</td>
												<td class="th2">Gesamt</td>
											</tr>
										</table>
									</th>
								</tr>
							</thead>
							<tbody>
								<?php $row_counter = 1 ?>
								<?php foreach ($period as $key => $value): ?>
								<tr>
									<td class="tr-datum-<?php echo $i % 2; ?>  <?php echo $wochentage[date("w", strtotime($value->format('d.m.Y')))] == "Mo." ? "ausen-border-top" : "" ?>"><?php echo $wochentage[date("w", strtotime($value->format('d.m.Y')))] . " " . $value->format('d.m.Y') ?></td>
									<?php foreach($groups as $key => $group): ?>
										<?php $pos = $sum_used = $sum_free = $sum_cont = $complete_used = $complete_free = $complete_cont = 0 ?>
										<?php $count = count($group); ?>
										<?php foreach($group as $parklot): ?>
										<?php 
											if(str_contains($parklot->parklot_short, 'APG') || str_contains($parklot->parklot_short, 'IAPS') || $parklot->product_id == 683){ 
												$pos++;
												continue;
											}
											else
												$pos++;

												
											$selector = "used " . $parklot->parklot_short; 
											$used = $row[$value->format('Y-m-d')]->$selector;
									 
											if(str_contains($parklot->parklot_short, 'APS')){
												$selector = "used " . str_replace("APS", "APG", $parklot->parklot_short);
												$used_apg = $row[$value->format('Y-m-d')]->$selector;
											}
											else
												$used_apg = 0;
											if($parklot->product_id == 621){
												$selector = "used HX PH";
												$used_hx = $row[$value->format('Y-m-d')]->$selector;
											}
											else
												$used_hx = 0;
											
											
											$contintent = $set_con[$value->format('Y-m-d')."_".$parklot->product_id] != null ? $set_con[$value->format('Y-m-d')."_".$parklot->product_id] : $parklot->contigent;
											$free = $contintent - $used - $used_apg - $used_hx;
											
											$per_used = $contintent != 0 ? number_format(($used + $used_hx) / $contintent * 100, 2,".",".") : "0.00";
											$per_used_apg = $contintent != 0 ? number_format($used_apg / $contintent * 100, 2,".",".") : "0.00";
											$per_used_all = $contintent != 0 ? number_format(($used + $used_apg + $used_hx) / $contintent * 100, 2,".",".") : "0.00";
											$per_free = $contintent != 0 ? number_format($free / $contintent * 100, 2,".",".") : "0.00";
																				
											// Zwischensummen
											$sum_used += $used + $used_apg + $used_hx;
											$sum_free += $free;
											$sum_cont += $contintent;
											
											$per_used_sum = $sum_cont != 0 ? number_format($sum_used / $sum_cont * 100, 2,".",".") : "0.00";
											$per_free_sum = $sum_cont != 0 ? number_format($sum_free / $sum_cont * 100, 2,".",".") : "0.00";
											
											// Summe nach Betreiber
											$sum_all_used[$parklot->product_id] = ($used + $used_apg + $used_hx);
											$sum_all_free[$parklot->product_id] = $free;
											$sum_all_con[$parklot->product_id] = $contintent;
											
											
											if($contintent != 0){
												if(number_format(($used + $used_apg + $used_hx) / $contintent * 100, 2,".",".") >= 70 && number_format(($used + $used_apg + $used_hx) / $contintent * 100, 2,".",".") < 85)
													$text_color = "purple";
												elseif(number_format(($used + $used_apg + $used_hx) / $contintent * 100, 2,".",".") >= 85)
													$text_color = "red";										
												else
													$text_color = "";
											}
											else
												$text_color = "";
											
											if($contintent != 0){
												if(number_format(($used + $used_apg + $used_hx) / $contintent * 100, 2,".",".") == 100)
													$background = "green";
												elseif(($used + $used_apg + $used_hx) > $contintent)
													$background = "yellow";
												else
													$background = "";
											}
											else
												$background = "";
											
										?>
										<td class="tr-lots-<?php echo $i % 2; ?> <?php echo $wochentage[date("w", strtotime($value->format('d.m.Y')))] == "Mo." ? "ausen-border-top" : "" ?>">
											<table>
												<tr style="
															<?php //echo $row_counter == 1 ? "border-top: 3px solid black;" : "" ?> 
															<?php //echo $row_counter == $sum_rows ? "border-bottom: 3px solid black;" : "" ?> 
															<?php //echo $pos == 1 ? "border-left: 3px solid black;" : "" ?> 
															<?php //echo $pos == (count($group)-1) ? "border-right: 3px solid black;" : "" ?>
															">
													<?php if(!str_contains($parklot->parklot_short, 'APS')): ?>
													<td class="td2 <?php echo $text_color ?> <?php echo $background ?>"><strong><?php echo $used + $used_hx . "<br>" . $per_used ?></strong></td>
													<?php else: ?>
													<td class="td2"><?php echo $used + $used_hx . "<br>" . $per_used ?></td>
													<?php endif; ?>
													<?php if(str_contains($parklot->parklot_short, 'APS')) : ?>
													<td class="td2"><?php echo $used_apg . "<br>" . $per_used_apg ?></td>
													<td class="td2  <?php echo $text_color ?> <?php echo $background ?>"><strong><?php echo $used + $used_apg + $used_hx . "<br>" . $per_used_all ?></strong></td>
													<?php endif; ?>
													<td class="td2"><?php echo $free . "<br>" . $per_free ?></td>
													<td class="td2"><input type="text" size="3" name="<?php echo $value->format('Y-m-d') . "_" . $parklot->product_id ?>" class="" value="<?php echo $set_con[$value->format('Y-m-d')."_".$parklot->product_id] != null ? $set_con[$value->format('Y-m-d')."_".$parklot->product_id] : "" ?>"></td>
												</tr>
											</table>
										</td>								
										<?php endforeach; ?>
										<?php if($pos == count($group)): ?>								
										<?php
											if($sum_cont != 0){
												if(number_format($sum_used / $sum_cont * 100, 2,".",".") >= 70 && number_format($sum_used / $sum_cont * 100, 2,".",".") < 85)
													$text_color = "purple";
												elseif(number_format($sum_used / $sum_cont * 100, 2,".",".") >= 85)
													$text_color = "red";										
												else
													$text_color = "";
											}
											else
												$text_color = "";
											
											if($sum_cont != 0){
												if(number_format($sum_used / $sum_cont * 100, 2,".",".") == 100)
													$background = "green";
												elseif($sum_used > $sum_cont)
													$background = "yellow";
												else
													$background = "";
											}
											else
												$background = "";
										?>								
										<td class="tr-lots-<?php echo $i % 2; ?> <?php echo $wochentage[date("w", strtotime($value->format('d.m.Y')))] == "Mo." ? "ausen-border-top" : "" ?>" style="<?php echo $i % 2 != 0 ? "background: " . $group[0]->color . "50" : "" ?>">
											<table>
												<tr>
													<td class="td2 <?php echo $text_color ?> <?php echo $background ?>"><strong><?php echo $sum_used . "<br>" . $per_used_sum ?></strong></td>
													<td class="td2"><?php echo $sum_free . "<br>" . $per_free_sum ?></td>
													<td class="td2"><?php echo $sum_cont ?></td>
												</tr>
											</table>
										</td>
										<?php $pos = 1; ?>
										<?php endif; ?>								
									<?php endforeach; ?>
										<?php foreach($clients as $client) : ?>
										<?php
										$all_used = $all_free = $all_cont = 0;
										$client_products = Database::getInstance()->getClientProducts($client->id);
				
										foreach($client_products as $client_product){
											$all_used += $sum_all_used[$client_product->product_id];
											$all_free += $sum_all_free[$client_product->product_id];
											$all_cont += $sum_all_con[$client_product->product_id];
										}
										$all_per_used = $all_cont != 0 ? number_format($all_used / $all_cont * 100, 2,".",".") : "0.00";
										$all_per_free = $all_cont != 0 ? number_format($all_free / $all_cont * 100, 2,".",".") : "0.00";
										
										$complete_used += $all_used;
										$complete_free += $all_free;
										$complete_cont += $all_cont;
										
										
										if($all_cont != 0){
											if(number_format($all_used / $all_cont * 100, 2,".",".") >= 70 && number_format($all_used / $all_cont * 100, 2,".",".") < 85)
												$text_color = "purple";
											elseif(number_format($all_used / $all_cont * 100, 2,".",".") >= 85)
												$text_color = "red";										
											else
												$text_color = "";
										}
										else
											$text_color = "";
										
										if($all_cont != 0){
											if(number_format($all_used / $all_cont * 100, 2,".",".") == 100)
												$background = "green";
											elseif($all_used > $all_cont)
												$background = "yellow";
											else
												$background = "";
										}
										else
											$background = "";
										
										?>
									<td class="tr-datum-<?php echo $i % 2; ?> <?php echo $wochentage[date("w", strtotime($value->format('d.m.Y')))] == "Mo." ? "ausen-border-top" : "" ?>">
										<table>
											<tr>
												<td class="td2 <?php echo $text_color ?> <?php echo $background ?>"><strong><?php echo $all_used . "<br>" . $all_per_used ?></strong></td>
												<td class="td2"><?php echo $all_free . "<br>" . $all_per_free ?></td>
												<td class="td2"><?php echo $all_cont ?></td>
											</tr>
										</table>
									</td>
									<?php endforeach; ?>
									<?php foreach($brokers as $broker) : ?>
										<?php if($broker->short == "APG") continue; ?>
										<?php
										$all_used = $all_free = $all_cont = 0;
										$broker_products = Database::getInstance()->getBrokerProducts($broker->id);
										
										foreach($broker_products as $broker_product){
											$all_used += $sum_all_used[$broker_product->product_id];
											$all_free += $sum_all_free[$broker_product->product_id];
											$all_cont += $sum_all_con[$broker_product->product_id];
										}
										$all_per_used = $all_cont != 0 ? number_format($all_used / $all_cont * 100, 2,".",".") : "0.00";
										$all_per_free = $all_cont != 0 ? number_format($all_free / $all_cont * 100, 2,".",".") : "0.00";
										
										$complete_used += $all_used;
										$complete_free += $all_free;
										$complete_cont += $all_cont;
										
										if($all_cont != 0){
											if(number_format($all_used / $all_cont * 100, 2,".",".") >= 70 && number_format($all_used / $all_cont * 100, 2,".",".") < 85)
												$text_color = "purple";
											elseif(number_format($all_used / $all_cont * 100, 2,".",".") >= 85)
												$text_color = "red";										
											else
												$text_color = "";
										}
										else
											$text_color = "";
										
										if($all_cont != 0){
											if(number_format($all_used / $all_cont * 100, 2,".",".") == 100)
												$background = "green";
											elseif($all_used > $all_cont)
												$background = "yellow";
											else
												$background = "";
										}
										else
											$background = "";
										?>
									<td class="tr-datum-<?php echo $i % 2; ?> <?php echo $wochentage[date("w", strtotime($value->format('d.m.Y')))] == "Mo." ? "ausen-border-top" : "" ?>">
										<table>
											<tr>
												<td class="td2 <?php echo $text_color ?> <?php echo $background ?>"><strong><?php echo $all_used . "<br>" . $all_per_used ?></strong></td>
												<td class="td2"><?php echo $all_free . "<br>" . $all_per_free ?></td>
												<td class="td2"><?php echo $all_cont ?></td>
											</tr>
										</table>
									</td>
									<?php endforeach; ?>
									<?php
									$per_complete_used = $complete_cont != 0 ? number_format($complete_used / $complete_cont * 100, 2,".",".") : "0.00";
									$per_complete_free = $complete_cont != 0 ? number_format($complete_free / $complete_cont * 100, 2,".",".") : "0.00";
									
									if($complete_cont != 0){
											if(number_format($complete_used / $complete_cont * 100, 2,".",".") >= 70 && number_format($complete_used / $complete_cont * 100, 2,".",".") < 85)
												$text_color = "purple";
											elseif(number_format($complete_used / $complete_cont * 100, 2,".",".") >= 85)
												$text_color = "red";										
											else
												$text_color = "";
										}
										else
											$text_color = "";
										
										if($complete_cont != 0){
											if(number_format($complete_used / $complete_cont * 100, 2,".",".") == 100)
												$background = "green";
											elseif($complete_used > $complete_cont)
												$background = "yellow";
											else
												$background = "";
										}
										else
											$background = "";
									?>
									<td class="tr-datum-<?php echo $i % 2; ?> <?php echo $wochentage[date("w", strtotime($value->format('d.m.Y')))] == "Mo." ? "ausen-border-top" : "" ?> ">
										<table>
											<tr>
												<td class="td2 <?php echo $text_color ?> <?php echo $background ?>"><strong><?php echo $complete_used . "<br>" . $per_complete_used ?></strong></td>
												<td class="td2"><?php echo $complete_free . "<br>" . $per_complete_free ?></td>
												<td class="td2"><?php echo $complete_cont ?></td>
											</tr>
										</table>
									</td>
								</tr>
								<?php $i++; $row_counter++;?>						
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</form>
	</div>	
</div>