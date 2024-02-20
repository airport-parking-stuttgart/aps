<?php
require_once get_template_directory() . '/lib/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$parklots = Database::getInstance()->getAllLots();
$companies = Database::getInstance()->getAllCompaniesNoTransfer();
$product_groups = Database::getInstance()->getProductGroups();
$wochentage = array("So.", "Mo.", "Di.", "Mi.", "Do.", "Fr.", "Sa.");


$clients = Database::getInstance()->getAllClients();
$brokers = Database::getInstance()->getBrokers();

if($_GET["product_vermittler"] != ''){
	$_GET["betreiber"] = null;
	$_GET["product"] = null;
}
if($_GET["product"] != ''){
	$_GET["product_vermittler"] = null;
	$_GET["betreiber"] = null;
}
if($_GET["betreiber"] != ''){
	$_GET["product_vermittler"] = null;
	$_GET["product"] = null;
}

	


if(isset($_GET["betreiber"]) && $_GET["product_vermittler"] == '' && $_GET["product"] == ''){
	$cb = 0;
	foreach($clients as $client){	
		if($_GET["betreiber"] == strtolower($client->short)){
			$client_products = Database::getInstance()->getClientProducts($client->id);
			foreach($client_products as $p){
				$products[$cb] = Database::getInstance()->getParklotByProductId($p->product_id);
				$cb++;				
			}			
		}
			
	}
	foreach($brokers as $broker){	
		if($_GET["betreiber"] == strtolower($broker->short)){
			$broker_products = Database::getInstance()->getBrokerLotsById($broker->id);
			foreach($broker_products as $p){
				$products[$cb] = Database::getInstance()->getParklotByProductId($p->product_id);
				$cb++;
			}			
		}	
	}
	$selected['betreiber'] = $_GET["betreiber"];
}
	
elseif(isset($_GET["product"]) && $_GET["product_vermittler"] == ''){
	$childs = Database::getInstance()->getChildProductGroupsByPerentId($_GET["product"]);
	$lots = array();
	if(count($childs) > 0){
		
		foreach($childs as $child){
			$lots[] = $child->id;
			
		}
	}
	else
		$lots[] =  $_GET["product"];
	
	$i = 0;
	foreach($lots as $lot){
		$pls = Database::getInstance()->getParklotIdsByChildProductGroupId($lot);
		
		foreach($pls as $pl){
			$products[$i] = Database::getInstance()->getParklotByProductId($pl->product_id);	
			$i++;
		}						
	}
	$selected['product'] = $_GET["product"];
}
	

elseif(isset($_GET["product_vermittler"]) && $_GET["product"] == ''){
	$products[0] = Database::getInstance()->getParklotByProductId($_GET["product_vermittler"]);
	$selected['product_vermittler'] = $_GET["product_vermittler"];
}


else{
	$selected['betreiber'] = $selected['product'] = $selected['product_vermittler'] = null;
	$products = Database::getInstance()->getAllLots();
}

if(isset($_GET["date"])){
	$date = (explode(" - ",$_GET["date"]));
	$date[0] = date('Y-m-d', strtotime($date[0]));
	$date[1] = date('Y-m-d', strtotime($date[1]));
	$dateW1 = $date[0];
	$dateW2 = $date[1];
	$booiingsMonth = Database::getInstance()->getAllSalesBookingsV2($date[0], $date[1], $selected, "month");
	
	foreach($clients as $client){		
		$selected['vergleich_c'] = $client->id;		
		$buchungen_clients[$client->id] = Database::getInstance()->getAllSalesBookingsV2($date[0], $date[1], $selected, "month");
	}
	$selected['vergleich_c'] = null;
	foreach($brokers as $broker){
		$selected['vergleich_b'] = $broker->id;
		$buchungen_brokers[$broker->id] = Database::getInstance()->getAllSalesBookingsV2($date[0], $date[1], $selected, "month");
	}
	$selected['vergleich_b'] = null;
}
else{
	$date1 = date("Y-m-d");
	$date2 = date("Y-m-d", strtotime($date1 . ' +4 day'));
	$dateW1 = $date1;
	$dateW2 = $date2;
	$booiingsMonth = Database::getInstance()->getAllSalesBookingsV2($date1, $date2, $selected, "month");
	
	foreach($clients as $client){
		$selected['vergleich_c'] = $client->id;	
		$buchungen_clients[$client->id] = Database::getInstance()->getAllSalesBookingsV2($date1, $date2, $selected, "month");
	}
	$selected['vergleich_c'] = null;
	foreach($brokers as $broker){
		$selected['vergleich_b'] = $broker->id;
		$buchungen_brokers[$broker->id] = Database::getInstance()->getAllSalesBookingsV2($date1, $date2, $selected, "month");
	}
	$selected['vergleich_b'] = null;
}

// Monat
foreach ($booiingsMonth as $bookings){
	$sumOrders += $bookings->Buchungen;
	$sumGross_b += $bookings->Brutto_b;
	$sumNet_b += $bookings->Netto_b;
	$sumGross_k += $bookings->Brutto_k;
	$sumNet_k += $bookings->Netto_k;
	$sumGross += ($bookings->Brutto_b + $bookings->Brutto_k);
	$sumNet += ($bookings->Netto_b + $bookings->Netto_k);
	$sumProvB += $bookings->Provision;
}


$gesBuchungen = 0;
foreach($buchungen_clients as $key => $bookings){
	foreach($bookings as $booking){
		$sumOrders_clients[$key] += $booking->Buchungen;
		$gesBuchungen += $booking->Buchungen;
	}
}
foreach($buchungen_brokers as $key => $bookings){
	foreach($bookings as $booking){
		$sumOrders_brokers[$key] += $booking->Buchungen;
		$gesBuchungen += $booking->Buchungen;
	}
	
}

foreach($clients as $client){
	$anteil[$client->id] = number_format($sumOrders_clients[$client->id] / $gesBuchungen * 100, 0);
}

foreach($brokers as $broker){
	$anteil[$broker->id] = number_format($sumOrders_brokers[$broker->id] / $gesBuchungen * 100, 0);
}



//echo "<pre>"; print_r($selected); echo "</pre>";	

?>
<style>
tr:nth-child(even) {
	background-color: #f2f2f2 !important;
}
.bottom_line{
	border-bottom: 4px solid black;
}
.umsatz_table{
	border-collapse: collapse !important;
}
</style>
<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Ist-Tagesumsätze</h3>
    </div>
	<div class="page-body">
		<form class="form-filter">
			<div class="row ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Umsätze filtern</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-md-2">
							<input type="text" class="datepicker-range form-item form-control" name="date" data-multiple-dates-separator=" - " placeholder="" value="<?php echo $_GET["date"] ? $_GET["date"] : date('d.m.Y', strtotime($date1)) . " - " . date('d.m.Y', strtotime($date2)) ?>">
						</div>
						<div class="col-sm-12 col-md-2">
							<select name="betreiber" class="form-item form-control">
								<option value="">Betreiber</option>
								<?php foreach($clients as $client) : ?>
								<option value="<?php echo strtolower($client->short) ?>" <?php echo (isset($_GET['betreiber']) && $_GET['betreiber'] == strtolower($client->short)) ? ' selected' : '' ?>>
									<?php echo $client->short ?>
								</option>
								<?php endforeach; ?>
								<?php foreach($brokers as $broker) : ?>
								<option value="<?php echo strtolower($broker->short) ?>" <?php echo (isset($_GET['betreiber']) && $_GET['betreiber'] == strtolower($broker->short)) ? ' selected' : '' ?>>
									<?php echo $broker->short ?>
								</option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-sm-12 col-md-2">
							<select name="product" class="form-item form-control">
								<option value="">Standort</option>
								<?php foreach ($product_groups as $group) : ?>						
									<option value="<?php echo $group->id ?>" <?php echo $group->id == $_GET['product'] ? "selected" : "" ?>><?php echo $group->name ?></option>						
									<?php $child_product_groups = Database::getInstance()->getChildProductGroupsByPerentId($group->id); ?>
									<?php if(count($child_product_groups) > 0): ?>
										<?php foreach ($child_product_groups as $child_group) : ?>
										<option value="<?php echo $child_group->id ?>" <?php echo $child_group->id == $_GET['product'] ? "selected" : "" ?>><?php echo " - " . $child_group->name ?></option>
										<?php endforeach; ?>
									<?php endif; ?>
								<?php endforeach; ?>
							</select>
						</div>	
						<div class="col-sm-12 col-md-2">
							<select name="product_vermittler" class="form-item form-control">
								<option value="">Vermittler / Produkt</option>
								<?php foreach($parklots as $product) : ?>
									<?php if($product->is_for == 'hotel') continue; ?>
									<option value="<?php echo $product->product_id ?>"
										<?php echo (isset($_GET['product_vermittler']) && $_GET['product_vermittler'] == $product->product_id) ? ' selected' : '' ?>>
										<?php echo $product->parklot ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>											
						<div class="col-sm-12 col-md-1">
							<button class="btn btn-primary d-block w-100" type="submit">Filter</button>
						</div>
						<div class="col-sm-12 col-md-2">                    
							<a href="<?php echo '/wp-admin/admin.php?page=umsatz-lots' ?>" class="btn btn-secondary d-block w-100" >Zurücksetzen</a>
						</div>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-sm-12 col-md-12">
					<details open="open">
						<summary class="itweb_add_head-summary">Gesamt Ist-Umsätze <?php echo $_GET["date"] ? $_GET["date"] : date('d.m.Y', strtotime($date1)) . " - " . date('d.m.Y', strtotime($date2)) ?> 
						<?php echo $_GET['betreiber'] ? " | " . strtoupper($_GET['betreiber']) : "" ?></summary>
						<br><br>
						<table class="table table-sm"> <!-- sales-table -->
							<thead>
								<tr>
									<th>Datum</th>
									<th>Buchungen</th>
									<th>Umsatz bar brutto</th>
									<th>Umsatz bar netto</th>
									<th>Umsatz online brutto</th>
									<th>Umsatz online netto</th>
									<th>Umsatz brutto</th>
									<th>Umsatz netto</th>
									<th>&#216; netto</th>
									<th>Provision netto</th>
									<th>Umsatz NN</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><strong><?php echo isset($_GET['date']) ? $_GET['date'] : date("d.m.Y", strtotime($date1)) . " - " . date("d.m.Y", strtotime($date2)); ?></strong></td>
									<td><strong><?php echo $sumOrders != null ? $sumOrders : "0" ?></strong></td>
									<td><strong><?php echo $sumGross_b != null ? number_format($sumGross_b,2,".",".") : "0.00" ?></strong></td>
									<td><strong><?php echo $sumNet_b != null ? number_format($sumNet_b,2,".",".") : "0.00" ?></strong></td>
									<td><strong><?php echo $sumGross_k != null ? number_format($sumGross_k,2,".",".") : "0.00" ?></strong></td>
									<td><strong><?php echo $sumNet_k != null ? number_format($sumNet_k,2,".",".") : "0.00" ?></strong></td>
									<td><strong><?php echo $sumGross != null ? number_format($sumGross,2,".",".") : "0.00" ?></strong></td>
									<td><strong><?php echo $sumNet != null ? number_format($sumNet,2,".",".") : "0.00" ?></strong></td>
									<td><strong><?php echo (($sumNet_b + $sumNet_k) != 0.00 ? number_format(round((floatval($sumNet_b) + floatval($sumNet_k)) / floatval($sumOrders),2),2,".",".") : '0.00') ?></strong></td>
									<td><strong><?php echo $sumProvB != null ? number_format($sumProvB,2,".",".") : "0.00" ?></strong></td>
									<td><strong><?php echo $sumNet != null ? number_format($sumNet - $sumProvB,2,".",".") : "0.00" ?></strong></td>
								</tr>
							</tbody>
						</table>
						<br>
					</details>
					<?php if($_GET['betreiber'] == null && $_GET['product'] == null && $_GET['product_vermittler'] == null): ?>
					<details open="open">
						<summary class="itweb_add_head-summary">Anteile Buchungen gesamt <?php echo $_GET["date"] ? $_GET["date"] : date('d.m.Y', strtotime($date1)) . " - " . date('d.m.Y', strtotime($date2)) ?></summary>
						<table class="table table-sm"> <!-- sales-table -->
							<thead>
								<tr>
									<th></th>
									<?php foreach($clients as $client): ?>
									<th><?php echo $client->short ?></th>
									<?php endforeach; ?>
									<?php foreach($brokers as $broker): ?>
									<th><?php echo $broker->short ?></th>
									<?php endforeach; ?>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><strong>Buchungen</strong></td>
									<?php foreach($clients as $client): ?>
									<td><strong><?php echo $sumOrders_clients[$client->id] != null ? $sumOrders_clients[$client->id] : "0" ?></strong></td>
									<?php endforeach; ?>
									<?php foreach($brokers as $broker): ?>
									<td><strong><?php echo $sumOrders_brokers[$broker->id] != null ? $sumOrders_brokers[$broker->id] : "0" ?></strong></td>
									<?php endforeach; ?>
								</tr>
								<tr>
									<td><strong>Anteil</strong></td>
									<?php foreach($clients as $client): ?>
									<td><strong><?php echo $anteil[$client->id] != null ? number_format($anteil[$client->id],0,".",".") : "0" ?>%</strong></td>
									<?php endforeach; ?>
									<?php foreach($brokers as $broker): ?>
									<td><strong><?php echo $anteil[$broker->id] != null ? number_format($anteil[$broker->id],0,".",".") : "0" ?>%</strong></td>
									<?php endforeach; ?>
								</tr>
							</tbody>
						</table>
					</details>
					<?php endif; ?>
					<br><br><br>
				</div>
			</div>
			<?php if(empty($_GET['pdf'])): ?>
			<div class="row">
				<div class="col-sm-12 col-md-12">
				<details open="open">
					<summary class="itweb_add_head-summary">Tagesumsätze Parkplätze <?php echo $_GET["date"] ? $_GET["date"] : date('d.m.Y', strtotime($date1)) . " - " . date('d.m.Y', strtotime($date2)) ?>
					<?php echo $_GET['betreiber'] ? " | " . strtoupper($_GET['betreiber']) : "" ?></summary>
					<br><br>
					<div class="row">
						<div class="col-sm-12 col-md-2">
							<a href="<?php echo basename($_SERVER['REQUEST_URI']) . "&pdf=1" ?>" target="_blank" class="btn btn-primary d-block w-100">PDF-Export</a>
						</div>
					</div>
					<br><br>
					<?php foreach ($products as $lot){
						if($lot->is_for == 'hotel') continue;
						if(isset($_GET["date"])){
							$date = (explode(" - ",$_GET["date"]));
							$date[0] = date('Y-m-d', strtotime($date[0]));
							$date[1] = date('Y-m-d', strtotime($date[1]));
							$dateW1 = $date[0];
							$dateW2 = $date[1];							
						}
						else{
							//$days = cal_days_in_month(CAL_GREGORIAN,date('m'),date('Y'));
							$date1 = date("Y-m-d");
							$date2 = date("Y-m-d", strtotime($date1 . ' +4 day'));
							$dateW1 = $date1;
							$dateW2 = $date2;							
						}						
						?>
								
						<table class="table table-sm" id="_ist_daySales">				
							<thead>
								<tr>
									<th style="min-width: 90px;">Datum</th>
									<th style="min-width: 320px;">Parkplatzname</th>
									<th>Anreisen</th>
									<th>bar brutto</th>
									<th>bar netto</th>
									<th>online brutto</th>
									<th>online netto</th>
									<th>ges. brutto</th>
									<th>ges. netto</th>
									<th>Ø netto</th>
									<th>Provision netto</th>
									<th>Umsatz NN</th>
								</tr>
							</thead>
							<tbody>								
								<?php while($dateW1 != date('Y-m-d', strtotime($dateW2 . '+1 day'))) : ?>	
									<?php $operator = Database::getInstance()->getSalesLotsV2($dateW1, $lot->product_id); ?>
									<?php if($operator): ?>
										<tr class="<?php echo $wochentage[date("w", strtotime($dateW1))] == "So." ? "bottom_line" : "" ?>">
											<td><?php echo $wochentage[date("w", strtotime($dateW1))] . " " . date('d.m.Y', strtotime($dateW1)) ?></td>
											<td><?php echo $operator->Produkt ?></td>									
											<td><?php echo $operator->Buchungen; $sumBuchungen += $operator->Buchungen;?></td>
											<td><?php echo number_format($operator->Brutto_b,2,".","."); $sumBrutto_b += $operator->Brutto_b; ?></td>
											<td><?php echo number_format($operator->Netto_b,2,".","."); $sumNetto_b += $operator->Netto_b; ?></td>
											<td><?php echo number_format($operator->Brutto_k,2,".","."); $sumBrutto_k += $operator->Brutto_k; ?></td>
											<td><?php echo number_format($operator->Netto_k,2,".","."); $sumNetto_k += $operator->Netto_k; ?></td>
											<td><?php echo number_format($operator->Brutto_b + $operator->Brutto_k,2,".","."); $sumBrutto += ($operator->Brutto_b + $operator->Brutto_k); ?></td>
											<td><?php echo number_format($operator->Netto_b + $operator->Netto_k,2,".","."); $sumNetto += ($operator->Netto_b + $operator->Netto_k); ?></td>
											<td><?php echo ($operator->Netto_b + $operator->Netto_k) != 0 ? number_format(round((floatval($operator->Netto_b) + floatval($operator->Netto_k)) / floatval($operator->Buchungen),2),2,".",".") : '0.00' ?></td>
											<td><?php 
												if($lot->is_for == "vermittler"){
													if($lot->product_id == 595 || $lot->product_id == 3080 || $lot->product_id == 3081 || $lot->product_id == 3082 || $lot->product_id == 24224 || $lot->product_id == 24228 || 
														$lot->product_id == 41577 || $lot->product_id == 41581 || $lot->product_id == 41584 || $lot->product_id == 41582 || $lot->product_id == 41580 || $lot->product_id == 41585)
														echo "-";
													else
														echo number_format($operator->Provision,2,".","."); $sumProv += $operator->Provision; 
												}
												else 
													echo "-"; ?></td>
											<td><?php 
												if($lot->is_for == "vermittler"){
													if($lot->product_id == 595 || $lot->product_id == 3080 || $lot->product_id == 3081 || $lot->product_id == 3082 || $lot->product_id == 24224 || $lot->product_id == 24228 ||
														$lot->product_id == 41577 || $lot->product_id == 41581 || $lot->product_id == 41584 || $lot->product_id == 41582 || $lot->product_id == 41580 || $lot->product_id == 41585)
														{echo number_format($operator->Netto_b + $operator->Netto_k,2,".",".");}
													else
														echo number_format(($operator->Netto_b + $operator->Netto_k) - $operator->Provision,2,".",".");
												}
												else 
													echo number_format($operator->Netto_b + $operator->Netto_k,2,".","."); ?></td>
										</tr>
									<?php else: ?>
										<tr class="<?php echo $wochentage[date("w", strtotime($dateW1))] == "So." ? "bottom_line" : "" ?>">
											<td><?php echo $wochentage[date("w", strtotime($dateW1))] . " " . date('d.m.Y', strtotime($dateW1)) ?></td>
											<td><?php echo $lot->parklot ?></td>									
											<td><?php echo 0;?></td>
											<td><?php echo number_format(0.00,2,".","."); ?></td>
											<td><?php echo number_format(0.00,2,".","."); ?></td>
											<td><?php echo number_format(0.00,2,".","."); ?></td>
											<td><?php echo number_format(0.00,2,".","."); ?></td>
											<td><?php echo number_format(0.00,2,".","."); ?></td>
											<td><?php echo number_format(0.00,2,".","."); ?></td>
											<td><?php echo number_format(0.00,2,".","."); ?></td>
											<td><?php 
												if($lot->is_for == "vermittler"){
													if($lot->product_id == 595 || $lot->product_id == 3080 || $lot->product_id == 3081 || $lot->product_id == 3082 || $lot->product_id == 24224 || $lot->product_id == 24228 || 
														$lot->product_id == 41577 || $lot->product_id == 41581 || $lot->product_id == 41584 || $lot->product_id == 41582 || $lot->product_id == 41580 || $lot->product_id == 41585)
														echo "-";
													else
														echo number_format(0.00,2,".","."); } 
												
												else echo "-"; ?></td>
											<td><?php echo number_format(0.00,2,".","."); ?></td>
										</tr>		
									<?php endif; ?>
								<?php $dateW1 = date('Y-m-d', strtotime($dateW1 . '+1 day')); endwhile; ?>
									<tr style="background: #aed8fd !important">
										<td><strong><?php echo "Summe" ?></strong></td>
										<td><strong><?php echo $lot->parklot ?></strong></td>									
										<td><strong><?php echo $sumBuchungen;?></strong></td>
										<td><strong><?php echo number_format($sumBrutto_b,2,".",".") ?></strong></td>
										<td><strong><?php echo number_format($sumNetto_b,2,".",".") ?></strong></td>
										<td><strong><?php echo number_format($sumBrutto_k,2,".",".") ?></strong></td>
										<td><strong><?php echo number_format($sumNetto_k,2,".",".") ?></strong></td>
										<td><strong><?php echo number_format($sumBrutto,2,".",".") ?></strong></td>
										<td><strong><?php echo number_format($sumNetto,2,".",".") ?></strong></td>
										<td><strong><?php echo ($sumNetto_b + $sumNetto_k) != 0 ? number_format(round((floatval($sumNetto_b) + floatval($sumNetto_k)) / floatval($sumBuchungen),2),2,".",".") : '0.00' ?></strong></td>
										<td><strong><?php if($lot->is_for == "vermittler"){
													if($lot->product_id == 595 || $lot->product_id == 3080 || $lot->product_id == 3081 || $lot->product_id == 3082 || $lot->product_id == 24224 || $lot->product_id == 24228 || 
														$lot->product_id == 41577 || $lot->product_id == 41581 || $lot->product_id == 41584 || $lot->product_id == 41582 || $lot->product_id == 41580 || $lot->product_id == 41585)
														echo "-";
													else
														echo number_format($sumProv,2,".",".");}
												else echo "-"; ?></strong></td>
										<td><strong><?php if($lot->is_for == "vermittler"){
													if($lot->product_id == 595 || $lot->product_id == 3080 || $lot->product_id == 3081 || $lot->product_id == 3082 || $lot->product_id == 24224 || $lot->product_id == 24228 || 
														$lot->product_id == 41577 || $lot->product_id == 41581 || $lot->product_id == 41584 || $lot->product_id == 41582 || $lot->product_id == 41580 || $lot->product_id == 41585)
														echo number_format($sumNetto,2,".",".");
													else
														echo number_format($sumNetto - $sumProv,2,".",".");}
												else echo number_format($sumNetto,2,".","."); ?></strong></td>
									</tr>
							</tbody>
						</table>
						
						<br>				
						<?php 
						$sumBuchungen = $sumBrutto_b = $sumBrutto_k = $sumNetto_b = $sumNetto_k = $sumBrutto = $sumNetto = $sumProv = 0;
					}; ?>
				</details>
				<br><br><br>
				</div>
			</div>
			<?php else: ?>
			<?php ob_start();?>
			<style>
				.ubs {font-size: 14px;}		
				table {border-collapse: collapse;}
				td, th {font-size: 14px;}
				td, th {border: 1px solid black;}
				td, th {border-collapse: collapse;}
				.page_break { page-break-before: always; }
			
			</style>
				<?php $z = 1; foreach ($products as $lot){
						if($lot->is_for == 'hotel') continue;
						if(isset($_GET["date"])){
							$date = (explode(" - ",$_GET["date"]));
							$date[0] = date('Y-m-d', strtotime($date[0]));
							$date[1] = date('Y-m-d', strtotime($date[1]));
							$dateW1 = $date[0];
							$dateW2 = $date[1];							
						}
						else{
							//$days = cal_days_in_month(CAL_GREGORIAN,date('m'),date('Y'));
							$date1 = date("Y-m-d");
							$date2 = date("Y-m-d", strtotime($date1 . ' +4 day'));
							$dateW1 = $date1;
							$dateW2 = $date2;							
						}						
						?>
						
						 <?php if($z > 1): ?>
						<div class="page_break"></div>
						<?php endif; ?>
						
						<strong><span class="ubs">
							<?php echo 'Ist-Umsätze: ' ?>
							<?php echo $_GET["date"] ? $_GET["date"] : date('d.m.Y', strtotime($date1)) . " - " . date('d.m.Y', strtotime($date2)) ?>
							<?php $operator = Database::getInstance()->getSalesLotsV2($dateW2, $lot->product_id); ?>
							<?php if($operator)
									echo " - " . $operator->Produkt;
								else
									echo " - " . $lot->parklot;
							?>
						</span></strong>
						<table class="table table-sm" id="_ist_daySales">				
							<thead>
								<tr>
									<th style="min-width: 90px;">Datum</th>
									<!--<th>Betreiber / Vermittler</th>-->
									<th style="min-width: 320px;">Parkplatzname</th>
									<th>Anreisen</th>
									<th>bar brutto</th>
									<th>bar netto</th>
									<th>online brutto</th>
									<th>online netto</th>
									<th>ges. brutto</th>
									<th>ges. netto</th>
									<th>Ø netto</th>
									<th>Provision netto</th>
									<th>Umsatz NN</th>
								</tr>
							</thead>
							<tbody>								
								<?php while($dateW1 != date('Y-m-d', strtotime($dateW2 . '+1 day'))) : ?>	
									<?php $operator = Database::getInstance()->getSalesLotsV2($dateW1, $lot->product_id); ?>
									<?php if($operator): ?>
										<tr>
											<td><?php echo $wochentage[date("w", strtotime($dateW1))] . " " . date('d.m.Y', strtotime($dateW1)) ?></td>
											<!--<td><?php echo $operator->Betreiber != "" ? "Betreiber: " . $operator->Betreiber : "Vermittler: " . $operator->Vermittler ?></td>-->
											<td><?php echo $operator->Produkt ?></td>									
											<td><?php echo $operator->Buchungen; $sumBuchungen += $operator->Buchungen;?></td>
											<td><?php echo number_format($operator->Brutto_b,2,".","."); $sumBrutto_b += $operator->Brutto_b; ?></td>
											<td><?php echo number_format($operator->Netto_b,2,".","."); $sumNetto_b += $operator->Netto_b; ?></td>
											<td><?php echo number_format($operator->Brutto_k,2,".","."); $sumBrutto_k += $operator->Brutto_k; ?></td>
											<td><?php echo number_format($operator->Netto_k,2,".","."); $sumNetto_k += $operator->Netto_k; ?></td>
											<td><?php echo number_format($operator->Brutto_b + $operator->Brutto_k,2,".","."); $sumBrutto += ($operator->Brutto_b + $operator->Brutto_k); ?></td>
											<td><?php echo number_format($operator->Netto_b + $operator->Netto_k,2,".","."); $sumNetto += ($operator->Netto_b + $operator->Netto_k); ?></td>
											<td><?php echo ($operator->Netto_b + $operator->Netto_k) != 0 ? number_format(round((floatval($operator->Netto_b) + floatval($operator->Netto_k)) / floatval($operator->Buchungen),2),2,".",".") : '0.00' ?></td>
											<td><?php if($operator->Vermittler != ""){
															if($operator->product_id == 595 || $operator->product_id == 3080 || $operator->product_id == 3081 || $operator->product_id == 3082 || $operator->product_id == 24224 || $operator->product_id == 24228 ||
																$operator->product_id == 41577 || $operator->product_id == 41581 || $operator->product_id == 41584 || $operator->product_id == 41582 || $operator->product_id == 41580 || $operator->product_id == 41585)
																echo "-";
															else
																echo number_format($operator->Provision,2,".","."); $sumProv += $operator->Provision; 
														} 
														else echo "-"; ?></td>
											<td><?php 
												if($operator->Vermittler != ""){
													if($operator->product_id == 595 || $operator->product_id == 3080 || $operator->product_id == 3081 || $operator->product_id == 3082 || $operator->product_id == 24224 || $operator->product_id == 24228 ||
														$operator->product_id == 41577 || $operator->product_id == 41581 || $operator->product_id == 41584 || $operator->product_id == 41582 || $operator->product_id == 41580 || $operator->product_id == 41585)
														{echo number_format($operator->Netto_b + $operator->Netto_k,2,".",".");}
													else
														echo number_format(($operator->Netto_b + $operator->Netto_k) - $operator->Provision,2,".",".");
												}
												else 
													echo number_format($operator->Netto_b + $operator->Netto_k,2,".","."); ?></td>
										</tr>
									<?php else: ?>
										<tr>
											<td><?php echo $wochentage[date("w", strtotime($dateW1))] . " " . date('d.m.Y', strtotime($dateW1)) ?></td>
											<!--<td><?php echo $operator->Betreiber != "" ? "Betreiber: " . $operator->Betreiber : "Vermittler: " . $operator->Vermittler ?></td>-->
											<td><?php echo $lot->parklot ?></td>									
											<td><?php echo 0;?></td>
											<td><?php echo number_format(0.00,2,".","."); ?></td>
											<td><?php echo number_format(0.00,2,".","."); ?></td>
											<td><?php echo number_format(0.00,2,".","."); ?></td>
											<td><?php echo number_format(0.00,2,".","."); ?></td>
											<td><?php echo number_format(0.00,2,".","."); ?></td>
											<td><?php echo number_format(0.00,2,".","."); ?></td>
											<td><?php echo number_format(0.00,2,".","."); ?></td>
											<td><?php if($operator->Vermittler != ""){ 
													if($operator->product_id == 595 || $operator->product_id == 3080 || $operator->product_id == 3081 || $operator->product_id == 3082 || $operator->product_id == 24224 || $operator->product_id == 24228 ||
														$operator->product_id == 41577 || $operator->product_id == 41581 || $operator->product_id == 41584 || $operator->product_id == 41582 || $operator->product_id == 41580 || $operator->product_id == 41585)
														echo "-";
													else
														echo number_format(0.00,2,".","."); 
												} 
												else echo "-"; ?></td>
											<td><?php echo number_format(0.00,2,".","."); ?></td>
										</tr>		
									<?php endif; ?>
								<?php $dateW1 = date('Y-m-d', strtotime($dateW1 . '+1 day')); endwhile; ?>
									<tr>
										<td><strong><?php echo "Summe" ?></strong></td>
										<!--<td><strong><?php echo "-" ?></strong></td>-->
										<td><strong><?php echo $lot->parklot ?></strong></td>									
										<td><strong><?php echo $sumBuchungen;?></strong></td>
										<td><strong><?php echo number_format($sumBrutto_b,2,".",".") ?></strong></td>
										<td><strong><?php echo number_format($sumNetto_b,2,".",".") ?></strong></td>
										<td><strong><?php echo number_format($sumBrutto_k,2,".",".") ?></strong></td>
										<td><strong><?php echo number_format($sumNetto_k,2,".",".") ?></strong></td>
										<td><strong><?php echo number_format($sumBrutto,2,".",".") ?></strong></td>
										<td><strong><?php echo number_format($sumNetto,2,".",".") ?></strong></td>
										<td><strong><?php echo ($sumNetto_b + $sumNetto_k) != 0 ? number_format(round((floatval($sumNetto_b) + floatval($sumNetto_k)) / floatval($sumBuchungen),2),2,".",".") : '0.00' ?></strong></td>
										<td><strong><?php if($operator->Vermittler != ""){
													if($operator->product_id == 595 || $operator->product_id == 3080 || $operator->product_id == 3081 || $operator->product_id == 3082 || $operator->product_id == 24224 || $operator->product_id == 24228 ||
														$operator->product_id == 41577 || $operator->product_id == 41581 || $operator->product_id == 41584 || $operator->product_id == 41582 || $operator->product_id == 41580 || $operator->product_id == 41585)
														echo "-";
													else
														echo number_format($sumProv,2,".",".");}
											else echo "-"; ?></strong></td>
										<td><strong><?php if($operator->Vermittler != ""){
													if($operator->product_id == 595 || $operator->product_id == 3080 || $operator->product_id == 3081 || $operator->product_id == 3082 || $operator->product_id == 24224 || $operator->product_id == 24228 ||
														$operator->product_id == 41577 || $operator->product_id == 41581 || $operator->product_id == 41584 || $operator->product_id == 41582 || $operator->product_id == 41580 || $operator->product_id == 41585)
														echo number_format($sumNetto,2,".",".");
													else
														echo number_format($sumNetto - $sumProv,2,".",".");}
												else echo number_format($sumNetto,2,".","."); ?></strong></td>
									</tr>
							</tbody>
						</table>
						<?php $z++; ?>
										
						<?php 
						$sumBuchungen = $sumBrutto_b = $sumBrutto_k = $sumNetto_b = $sumNetto_k = $sumBrutto = $sumNetto = $sumProv = 0;					
					}; ?>
				
				<?php
				$content = ob_get_clean();
				// instantiate and use the dompdf class
				$options = new Options();
				$options->set('isRemoteEnabled', true);
				$dompdf = new Dompdf($options);
				$dompdf->loadHtml($content);

				// (Optional) Setup the paper size and orientation
				$dompdf->setPaper('A4', 'landscape');

				// Render the HTML as PDF
				$dompdf->render();
				
				$file = $dompdf->output();
					$fileName = ist_umsätze;
				if(!file_exists(ABSPATH . 'wp-content/uploads')){
					mkdir(ABSPATH . 'wp-content/uploads');
				}
				$filePath = ABSPATH . 'wp-content/uploads/' . $fileName . '.pdf';
				$pdf = fopen($filePath, 'w');
				fwrite($pdf, $file);
				fclose($pdf);
				
				$pdf_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
				echo "<script>location.href = '".$pdf_url."/wp-content/uploads/ist_ums%C3%A4tze.pdf';</script>";
				//unlink($filePath);
				?>
			<?php endif; ?>
		</form>
	</div>
</div>