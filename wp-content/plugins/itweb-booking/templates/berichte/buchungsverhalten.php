<?php

$clients = Database::getInstance()->getAllClients();
$brokers = Database::getInstance()->getBrokers();
$product_groups = Database::getInstance()->getProductGroups();
if (isset($_GET["selected"])) {
    if ((empty($_GET["selected"]) || $_GET["selected"] == "all") && $_GET["product"] == "") {
        $salesMonth = Database::getInstance()->getAllSales($_GET["month"], $_GET["year"], "all", "month", 'processing');
		$salesMonthArrival = Database::getInstance()->getAllSalesArrivalsV2($_GET["month"], $_GET["year"], "all", "month_arrival", 'processing');
        //$salesYear = Database::getInstance()->getAllSales($_GET["month"], $_GET["year"], "all", "year");
        $salesFor = "Alle Umsätze";
    } elseif ($_GET["selected"] == "allC" && $_GET["product"] == "") {
        $salesMonth = Database::getInstance()->getAllSales($_GET["month"], $_GET["year"], "betreiber", "month", 'processing');
		$salesMonthArrival = Database::getInstance()->getAllSalesArrivalsV2($_GET["month"], $_GET["year"], "betreiber", "month", 'processing');
        //$salesYear = Database::getInstance()->getAllSales($_GET["month"], $_GET["year"], "betreiber", "year");
        $salesFor = "Umsätze aller Betreiber";
    } elseif ($_GET["selected"] == "allB" && $_GET["product"] == "") {
        $salesMonth = Database::getInstance()->getAllSales($_GET["month"], $_GET["year"], "vermittler", "month", 'processing');
		$salesMonthArrival = Database::getInstance()->getAllSalesArrivalsV2($_GET["month"], $_GET["year"], "vermittler", "month", 'processing');
        //$salesYear = Database::getInstance()->getAllSales($_GET["month"], $_GET["year"], "vermittler", "year");
        $salesFor = "Umsätze aller Vermittler";
    } 
		
	foreach ($clients as $client){
		if($_GET["selected"] == "c-".$client->id && $_GET["product"] == "") {
			$c_id = str_replace("c-", "", $_GET["selected"]);
			$salesMonth = Database::getInstance()->getSalesProductsV2($_GET["month"], $_GET["year"], $client->id, "betreiber", "month", 'processing');
			$salesMonthArrival = Database::getInstance()->getSalesArrivalsProductsV2($_GET["month"], $_GET["year"], $client->id, "betreiber", "month", 'processing');
			$salesFor = "Umsätze " . $salesMonth[0]->parklot;
		}
	}
	
	foreach ($brokers as $broker){
		if ($_GET["selected"] == "b-".$broker->id && $_GET["product"] == "") {
			$b_id = str_replace("b-", "", $_GET["selected"]);
			$salesMonth = Database::getInstance()->getSalesProductsV2($_GET["month"], $_GET["year"], $broker->id, "vermittler", "month", 'processing');
			$salesMonthArrival = Database::getInstance()->getSalesArrivalsProductsV2($_GET["month"], $_GET["year"], $broker->id, "vermittler", "month", 'processing');
			$salesFor = "Umsätze " . $salesMonth[0]->parklot;
		}
	}
	
	if ((empty($_GET["selected"]) || $_GET["selected"] == "all") && isset($_GET["product"])) {
		$salesMonth = Database::getInstance()->getAllSales($_GET["month"], $_GET["year"], $_GET["product"], "month", 'processing');
		$salesMonthArrival = Database::getInstance()->getAllSalesArrivalsV2($_GET["month"], $_GET["year"], $_GET["product"], "month", 'processing');
		$salesFor = "Umsätze Parkhaus gesamt";
	} 
	
} else {
    $salesMonth = Database::getInstance()->getAllSales(date('n'), date('Y'), "all", "month", 'processing');
	$salesMonthArrival = Database::getInstance()->getAllSalesArrivalsV2(date('n'), date('Y'), "all", "month_arrival", 'processing');
    //$salesYear = Database::getInstance()->getAllSales(date('n'), date('Y'), "all", "year");
    $salesFor = "Alle Umsätze";
}

$months = array('1' => 'Januar', '2' => 'Fabruar', '3' => 'März', '4' => 'April', '5' => 'Mai', '6' => 'Juni',
    '7' => 'Juli', '8' => 'August', '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Dezember');
$years = array('2021' => '2021', '2022' => '2022', '2023' => '2023', '2024' => '2024', '2025' => '2025');

if (isset($_GET['month']) && $_GET['month'] < 10)
    $zero = '0';
else
    $zero = '';

if (isset($_GET["month"]))
    $c_month = $_GET["month"];
else
    $c_month = date('n');
if (isset($_GET["year"]))
    $c_year = $_GET["year"];
else
    $c_year = date('Y');
$currentMonth = (int)date('n');

$d = $t = 1;
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $c_month, $c_year);


//echo "<pre>"; print_r($salesMonthArrival); echo "</pre>";

?>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Tägliche Buchungen</h3>
    </div>
    <div class="page-body">
        <form class="form-filter">
            <div class="row ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Umsätze filtern</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-md-1">
							<input type="hidden" value="<?php echo $salesFor ?>" class="salesFor">
							<input type="hidden" value="<?php echo isset($_GET['month']) ? $zero . $_GET['month'] : date('m'); echo "."; echo isset($_GET['year']) ? $_GET['year'] : date('Y'); ?>" class="salesDate">
							<select name="month" class="form-item form-control">
								<?php foreach ($months as $key => $value) : ?>
									<?php
									//if ($c_year == (int)date('Y') && $currentMonth < $key) {
									//	break;
									//}
									?>
									<option value="<?php echo $key ?>" <?php echo $key == $c_month ? ' selected' : '' ?>>
										<?php echo $value ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-sm-12 col-md-1">
							<select name="year" class="form-item form-control">
								<?php for ($i = 2021; $i <= date('Y'); $i++) : ?>
									<option value="<?php echo $i ?>" <?php echo $i == $c_year ? ' selected' : '' ?>>
										<?php echo $i ?>
									</option>
								<?php endfor; ?>
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

						<div class="col-sm-12 col-md-3">
							<select name="selected" class="form-item form-control">
								<optgroup label="Alle Umsätze">
									<option value="all" <?php if ($_GET["selected"] == "all") echo "selected" ?>>Alle Umsätze
									</option>
									<option value="allC" <?php if ($_GET["selected"] == "allC") echo "selected" ?>>Alle
										Betreiber
									</option>
									<option value="allB" <?php if ($_GET["selected"] == "allB") echo "selected" ?>>Alle
										Vermittler
									</option>
								</optgroup>
								<optgroup label="Betreiber">
									<?php foreach ($clients as $client): ?>
										<option value="c-<?php echo $client->id ?>" <?php if ($_GET["selected"] == "c-" . $client->id) echo "selected"; ?>><?php echo $client->client ?></option>
									<?php endforeach; ?>
								</optgroup>
								<optgroup label="Vermittler">
									<?php foreach ($brokers as $broker): ?>
										<option value="b-<?php echo $broker->id ?>" <?php if ($_GET["selected"] == "b-" . $broker->id) echo "selected"; ?>><?php echo $broker->company ?></option>
									<?php endforeach; ?>
								</optgroup>
							</select>
						</div>

						<div class="col-sm-12 col-md-1">
							<button class="btn btn-primary d-block w-100" type="submit">Filter</button>
						</div>
						<div class="col-sm-12 col-md-2">                    
							<a href="<?php echo '/wp-admin/admin.php?page=buchungsverhalten' ?>" class="btn btn-secondary d-block w-100" >Zurücksetzen</a>
						</div>
					</div>
				</div>
            </div>
		</form>
		<div class="row">
			<div class="col-sm-12 col-md-6">
				<h4>Getätigte Buchungen</h4>
				<table class="table table-sm" id="">
					<thead>
					<tr>
						<th>Nr.</th>
						<th>Datum</th>
						<th>Buchungen</th>
						<th>&#216; brutto</th>
						<th>&#216; netto</th>
						<th>&#216; Tage</th>

					</tr>
					</thead>
					<tbody>
					<?php $nr = 1; while ($daysInMonth >= $d): ?>
						<?php foreach ($salesMonth as $operator) : ?>
							<?php if ($d == $operator->Tag): ?>
							<?php 
							$sum_buchungen += $operator->Buchungen; 
							$sum_brutto += ($operator->Brutto_k + $operator->Brutto_b);
							$sum_netto += ($operator->Netto_k + $operator->Netto_b);
							$sum_tage += $operator->Tage;
							?>
								<tr>
									<td><?php echo $nr < 10 ? "0".$nr : $nr ?></td>
									<td><?php echo date('d.m.', strtotime($operator->Datum)) ?></td>
									<td><?php echo $operator->Buchungen ?></td>
									<td><?php echo number_format(($operator->Brutto_k + $operator->Brutto_b) / $operator->Buchungen, 2, ",", ".") ?></td>
									<td><?php echo number_format(($operator->Netto_k + $operator->Netto_b) / $operator->Buchungen, 2, ",", ".") ?></td>
									<td><?php echo number_format($operator->Tage / $operator->Buchungen, 2, ",", ".") ?></td>										
								</tr>
								<?php $inday = 1;
								break;
							else: $inday = 0; ?>
							<?php endif; ?>
						<?php endforeach; ?>
						<?php if ($inday == 0): ?>
							<tr>
								<td><?php echo $nr < 10 ? "0".$nr : $nr ?></td>
								<td><?php 
									echo $d < 10 ? '0' . $d : $d;
									echo ".";
									echo $c_month < 10 ? '0' . $c_month : $c_month;
									//echo ".";
									//echo $c_year;                                                                                  
									?></td>										
								<td><?php echo '0' ?></td>
								<td><?php echo '0,00' ?></td>
								<td><?php echo '0,00' ?></td>
								<td><?php echo '0' ?></td>
							</tr>
						<?php endif;
						$d++; ?>
					<?php $nr++; endwhile; ?>
						<tr>
							<td><strong>Summe</strong></td>
							<td>&nbsp;</td>
							<td><strong><?php echo $sum_buchungen ?></strong></td>
							<td><strong><?php echo number_format($sum_brutto / $sum_buchungen, 2, ",", ".") ?></strong></td>
							<td><strong><?php echo number_format($sum_netto / $sum_buchungen, 2, ",", ".") ?></strong></td>
							<td><strong><?php echo number_format($sum_tage / $sum_buchungen, 2, ",", ".") ?></strong></td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php
			$sum_buchungen = 0; 
			$sum_brutto = 0;
			$sum_netto = 0;
			$sum_tage = 0;
			?>
			<div class="col-sm-12 col-md-6">
				<h4>Im selben Monat angereist</h4>
				<table class="table table-sm" id="">
					<thead>
					<tr>
						<th>Nr.</th>
						<th>Datum</th>
						<th>Anreisen</th>
						<th>&#216; brutto</th>
						<th>&#216; netto</th>
						<th>&#216; Tage</th>

					</tr>
					</thead>
					<tbody>
					<?php $nr = 1; while ($daysInMonth >= $t): ?>
						<?php foreach ($salesMonthArrival as $operator) : ?>
							<?php if ($t == $operator->Tag): ?>
							<?php 
							$sum_buchungen += $operator->Buchungen; 
							$sum_brutto += ($operator->Brutto_k + $operator->Brutto_b);
							$sum_netto += ($operator->Netto_k + $operator->Netto_b);
							$sum_tage += $operator->Tage;
							?>
								<tr>
									<td><?php echo $nr < 10 ? "0".$nr : $nr ?></td>
									<td><?php echo date('d.m.', strtotime($operator->Datum)) ?></td>
									<td><?php echo $operator->Buchungen ?></td>
									<td><?php echo number_format(($operator->Brutto_k + $operator->Brutto_b) / $operator->Buchungen, 2, ",", ".") ?></td>
									<td><?php echo number_format(($operator->Netto_k + $operator->Netto_b) / $operator->Buchungen, 2, ",", ".") ?></td>
									<td><?php echo number_format($operator->Tage / $operator->Buchungen, 2, ",", ".") ?></td>										
								</tr>
								<?php $inday = 1;
								break;
							else: $inday = 0; ?>
							<?php endif; ?>
						<?php endforeach; ?>
						<?php if ($inday == 0): ?>
							<tr>
								<td><?php echo $nr < 10 ? "0".$nr : $nr ?></td>
								<td><?php 
									echo $t < 10 ? '0' . $t : $t;
									echo ".";
									echo $c_month < 10 ? '0' . $c_month : $c_month;
									//echo ".";
									//echo $c_year;                                                                                  
									?></td>										
								<td><?php echo '0' ?></td>
								<td><?php echo '0,00' ?></td>
								<td><?php echo '0,00' ?></td>
								<td><?php echo '0' ?></td>
							</tr>
						<?php endif;
						$t++; ?>
					<?php $nr++; endwhile; ?>
						<tr>
							<td><strong>Summe</strong></td>
							<td>&nbsp;</td>
							<td><strong><?php echo $sum_buchungen ?></strong></td>
							<td><strong><?php echo number_format($sum_brutto / $sum_buchungen, 2, ",", ".") ?></strong></td>
							<td><strong><?php echo number_format($sum_netto / $sum_buchungen, 2, ",", ".") ?></strong></td>
							<td><strong><?php echo number_format($sum_tage / $sum_buchungen, 2, ",", ".") ?></strong></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>      
    </div>
</div>