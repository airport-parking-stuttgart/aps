<?php

$clients = Database::getInstance()->getAllClients();
$brokers = Database::getInstance()->getBrokers();
$product_groups = Database::getInstance()->getProductGroups();
$companies = Database::getInstance()->getAllCompanies();
$wochentage = array("So.", "Mo.", "Di.", "Mi.", "Do.", "Fr.", "Sa.");

if (isset($_GET["selected"])) {
    if ((empty($_GET["selected"]) || $_GET["selected"] == "all") && $_GET["product"] == "") {
        $salesMonth = Database::getInstance()->getAllSalesV2($_GET["month"], $_GET["year"], "all", "month", 'processing');
        //$salesYear = Database::getInstance()->getAllSales($_GET["month"], $_GET["year"], "all", "year");
        $salesFor = "Alle Umsätze";
    } elseif ($_GET["selected"] == "allC" && $_GET["product"] == "") {
        $salesMonth = Database::getInstance()->getAllSalesV2($_GET["month"], $_GET["year"], "betreiber", "month", 'processing');
        //$salesYear = Database::getInstance()->getAllSales($_GET["month"], $_GET["year"], "betreiber", "year");
        $salesFor = "Umsätze aller Betreiber";
    } elseif ($_GET["selected"] == "allB" && $_GET["product"] == "") {
        $salesMonth = Database::getInstance()->getAllSalesV2($_GET["month"], $_GET["year"], "vermittler", "month", 'processing');
        //$salesYear = Database::getInstance()->getAllSales($_GET["month"], $_GET["year"], "vermittler", "year");
        $salesFor = "Umsätze aller Vermittler";
    } 
	foreach ($clients as $client){
		if($_GET["selected"] == "c-".$client->id && $_GET["product"] == "") {
			$c_id = str_replace("c-", "", $_GET["selected"]);
			$salesMonth = Database::getInstance()->getSalesProductsV2($_GET["month"], $_GET["year"], $client->id, "betreiber", "month", 'processing');
			//$salesYear = Database::getInstance()->getSalesProducts($_GET["month"], $_GET["year"], $c_id, "betreiber", "year");
			$salesFor = "Umsätze " . $salesMonth[0]->parklot;
		}
	}
	
	foreach ($brokers as $broker){
		if ($_GET["selected"] == "b-".$broker->id && $_GET["product"] == "") {
			$b_id = str_replace("b-", "", $_GET["selected"]);
			$salesMonth = Database::getInstance()->getSalesProductsV2($_GET["month"], $_GET["year"], $broker->id, "vermittler", "month", 'processing');
			//$salesYear = Database::getInstance()->getSalesProducts($_GET["month"], $_GET["year"], $b_id, "vermittler", "year");
			$salesFor = "Umsätze " . $salesMonth[0]->parklot;
		}
	}
	if ((empty($_GET["selected"]) || $_GET["selected"] == "all") && isset($_GET["product"])) {
		$salesMonth = Database::getInstance()->getAllSalesV2($_GET["month"], $_GET["year"], $_GET["product"], "month", 'processing');
		$salesFor = "Umsätze Parkhaus gesamt";
	} 
	
} else {
    $salesMonth = Database::getInstance()->getAllSalesV2(date('n'), date('Y'), "all", "month", 'processing');
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

$d = 1;
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $c_month, $c_year);


// Monat
foreach ($salesMonth as $operator) {
    $sumOrders += $operator->Buchungen;
    $sumGross_b += $operator->Brutto_b;
    $sumNet_b += $operator->Netto_b;
	$sumGross_k += $operator->Brutto_k;
    $sumNet_k += $operator->Netto_k;
	$sumGross += ($operator->Brutto_b + $operator->Brutto_k);
	$sumNet += ($operator->Netto_b + $operator->Netto_k);
    $sumProv += $operator->Provision;
}

if (isset($_GET["selected"])){
	foreach($clients as $client){
		$buchungen_clients[$client->id] = Database::getInstance()->getSalesProductsV2($_GET["month"], $_GET["year"], $client->id, "betreiber", "month", 'processing');
	}
	foreach($brokers as $broker){
		$buchungen_brokers[$broker->id] = Database::getInstance()->getSalesProductsV2($_GET["month"], $_GET["year"], $broker->id, "vermittler", "month", 'processing');
	}
}
else{
	foreach($clients as $client){
		$buchungen_clients[$client->id] = Database::getInstance()->getSalesProductsV2(date('n'), date('Y'), $client->id, "betreiber", "month", 'processing');
	}
	foreach($brokers as $broker){
		$buchungen_brokers[$broker->id] = Database::getInstance()->getSalesProductsV2(date('n'), date('Y'), $broker->id, "vermittler", "month", 'processing');
	}
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



//echo "<pre>"; print_r($anteil); echo "</pre>";
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
							<a href="<?php echo '/wp-admin/admin.php?page=umsatz' ?>" class="btn btn-secondary d-block w-100" >Zurücksetzen</a>
						</div>
					</div>
				</div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-md-12">
                    <details open="open">
                        <summary class="itweb_add_head-summary">
                            Gesamtumsätze <?php echo isset($_GET['month']) ? $zero . $_GET['month'] : date('m');
                            echo ".";
                            echo isset($_GET['year']) ? $_GET['year'] : date('Y'); ?></summary>
                        <br><br>
                        <table class="table table-sm"> <!-- sales-table -->
                            <thead>
                            <tr>
                                <th>Datum <?php //echo $c_year?></th>
                                <th>Buchungen</th>
                                <th>Umsatz bar brutto</th>
                                <th>Umsatz bar netto</th>
								<th>Umsatz online brutto</th>
                                <th>Umsatz online netto</th>
								<th>Umsatz brutto</th>
								<th>Umsatz netto</th>
                                <th>Durchschnitt netto</th>
                                <th>Provision netto</th>
								<th>Umsatz NN</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td><strong><?php echo isset($_GET['month']) ? $zero . $_GET['month'] : date('m');
                                        echo "-01 bis ";
                                        echo isset($_GET['month']) ? $zero . $_GET['month'] : date('m');
                                        echo "-" . $daysInMonth ?></strong></td>
                                <td><strong><?php echo $sumOrders != null ? $sumOrders : "0" ?></strong></td>
                                <td>
                                    <strong><?php echo $sumGross_b != null ? number_format($sumGross_b, 2, ".", ".") : "0.00" ?></strong>
                                </td>
                                <td>
                                    <strong><?php echo $sumNet_b != null ? number_format($sumNet_b, 2, ".", ".") : "0.00" ?></strong>
                                </td>
                                <td>
                                    <strong><?php echo $sumGross_k != null ? number_format($sumGross_k, 2, ".", ".") : "0.00" ?></strong>
                                </td>
                                <td>
                                    <strong><?php echo $sumNet_k != null ? number_format($sumNet_k, 2, ".", ".") : "0.00" ?></strong>
                                </td>
								<td><strong><?php echo $sumGross != null ? number_format($sumGross,2,".",".") : "0.00" ?></strong></td>
								<td><strong><?php echo $sumNet != null ? number_format($sumNet,2,".",".") : "0.00" ?></strong></td>
                                <td>
                                    <strong><?php echo ($sumNet_b + $sumNet_k) != 0 ? number_format(round((floatval($sumNet_b) + floatval($sumNet_k)) / floatval($sumOrders), 2), 2, ".", ".") : '0.00' ?></strong>
                                </td>
                                <td>
                                    <strong><?php echo $sumProv != null ? number_format($sumProv, 2, ".", ".") : "0.00" ?></strong>
                                </td>
								<td>
                                    <strong><?php echo $sumProv != null && $sumNet != null ? number_format($sumNet - $sumProv, 2, ".", ".") : "0.00" ?></strong>
                                </td>
                            </tr>
                            </tbody>
                        </table>
					</details>
						<br>
					<details>
                        <summary class="itweb_add_head-summary">Anteile Buchungen gesamt <?php echo isset($_GET['month']) ? $zero . $_GET['month'] : date('m'); echo "."; echo isset($_GET['year']) ? $_GET['year'] : date('Y'); ?></summary>
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
                    <br><br><br>

                    <details>
                        <summary class="itweb_add_head-summary">Umsatz nach Tag</summary>
                        <table class="" id="daySales">
                            <thead>
                            <tr>
								<th>Nr.</th>
                                <th>Datum</th>
                                <th>Buchungen</th>
                                <th>Brutto bar</th>
                                <th>Netto bar</th>
								<th>Brutto online</th>
                                <th>Netto online</th>
								<th>Umsatz brutto</th>
								<th>Umsatz netto</th>
                                <th>&#216; netto</th>
                                <th>Provision netto</th>
								<th>Umsatz NN</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $nr = 1; while ($daysInMonth >= $d): ?>
                                <?php foreach ($salesMonth as $operator) : ?>
                                    <?php if ($d == $operator->Tag): ?>
									<?php $wochentag = date('Y-m-d', strtotime($operator->Datum)) ?>
                                        <tr>
                                            <td><?php echo $nr < 10 ? "0".$nr : $nr ?></td>
											<td><?php echo $wochentage[date("w", strtotime($wochentag))] . " " . date('d.m.', strtotime($operator->Datum)) ?></td>
                                            <td><?php echo $operator->Buchungen ?></td>
                                            <td><?php echo number_format($operator->Brutto_b, 2, ".", ".") ?></td>
                                            <td><?php echo number_format($operator->Netto_b, 2, ".", ".") ?></td>
											<td><?php echo number_format($operator->Brutto_k, 2, ".", ".") ?></td>
                                            <td><?php echo number_format($operator->Netto_k, 2, ".", ".") ?></td>
											<td><?php echo number_format($operator->Brutto_k + $operator->Brutto_b, 2, ".", ".") ?></td>
                                            <td><?php echo number_format($operator->Netto_k + $operator->Netto_b, 2, ".", ".") ?></td>
                                            <td><?php echo ($operator->Netto_b + $operator->Netto_k) != 0 ? number_format(round((floatval($operator->Netto_b) + floatval($operator->Netto_k)) / floatval($operator->Buchungen), 2), 2, ".", ".") : '0.00' ?></td>
                                            <td><?php echo number_format($operator->Provision, 2, ".", ".") ?></td>
											<td><?php echo number_format(($operator->Netto_k + $operator->Netto_b) - $operator->Provision, 2, ".", ".") ?></td>
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
											$t = $d < 10 ? '0' . $d : $d;
											$m = $c_month < 10 ? '0' . $c_month : $c_month;
											$y = $c_year;
											$datefull = $y . "-" . $m . "-" . $t;
											$wochentag = date('Y-m-d', strtotime($datefull));
											echo $wochentage[date("w", strtotime($wochentag))] . " " . $t . "." . $m;
                                                                                 
                                            ?></td>										
                                        <td><?php echo '0' ?></td>
                                        <td><?php echo '0.00' ?></td>
                                        <td><?php echo '0.00' ?></td>
                                        <td><?php echo '0.00' ?></td>
                                        <td><?php echo '0.00' ?></td>
										<td><?php echo '0.00' ?></td>
                                        <td><?php echo '0.00' ?></td>
										<td><?php echo '0.00' ?></td>
                                        <td><?php echo '0.00' ?></td>
										<td><?php echo '0.00' ?></td>
                                    </tr>
                                <?php endif;
                                $d++; ?>
                            <?php $nr++; endwhile; ?>
                            <tr><strong>
                                    <td><strong>Summe:</strong></td>
									<td><strong></strong></td>
                                    <td><strong><?php echo $sumOrders != null ? $sumOrders : "0" ?></strong></td>
                                    <td>
                                        <strong><?php echo $sumGross_b != null ? number_format($sumGross_b, 2, ".", ".") : "0.00" ?></strong>
                                    </td>
                                    <td>
                                        <strong><?php echo $sumNet_b != null ? number_format($sumNet_b, 2, ".", ".") : "0.00" ?></strong>
                                    </td>
									<td>
                                        <strong><?php echo $sumGross_k != null ? number_format($sumGross_k, 2, ".", ".") : "0.00" ?></strong>
                                    </td>
                                    <td>
                                        <strong><?php echo $sumNet_k != null ? number_format($sumNet_k, 2, ".", ".") : "0.00" ?></strong>
                                    </td>
									<td>
                                        <strong><?php echo ($sumGross_k + $sumGross_b) != null ? number_format($sumGross_k + $sumGross_b, 2, ".", ".") : "0.00" ?></strong>
                                    </td>
									<td>
                                        <strong><?php echo ($sumNet_k + $sumNet_b) != null ? number_format($sumNet_k + $sumNet_b, 2, ".", ".") : "0.00" ?></strong>
                                    </td>
                                    <td>
                                        <strong><?php echo($sumNet_b + $sumNet_k) != 0.00 ? number_format(round((floatval($sumNet_b) + floatval($sumNet_k)) / floatval($sumOrders), 2), 2, ".", ".") : '0.00' ?></strong>
                                    </td>
                                    <td>
                                        <strong><?php echo $sumProv != null ? number_format($sumProv, 2, ".", ".") : "0.00" ?></strong>
                                    </td>
									<td>
                                        <strong><?php echo $sumProv != null && ($sumNet_k + $sumNet_b) != null ? number_format(($sumNet_k + $sumNet_b) - $sumProv, 2, ".", ".") : "0.00" ?></strong>
                                    </td>
                            </tr>
                            </tbody>
                        </table>
                    </details>
                </div>
            </div>
        </form>
    </div>
</div>