<?php 

$clients = Database::getInstance()->getAllClients();
$brokers = Database::getInstance()->getBrokers();

if (isset($_GET["selected"])) {
    if (empty($_GET["selected"]) || $_GET["selected"] == "all") {
        $salesMonth = Database::getInstance()->getAllSalesV2($_GET["month"], $_GET["year"], "all", "month", 'processing');
        $salesMonthCancelled = Database::getInstance()->getAllSalesV2($_GET["month"], $_GET["year"], "all", "month", 'cancelled');
		//$salesYear = Database::getInstance()->getAllSalesV2($_GET["month"], $_GET["year"], "all", "year");
        $salesFor = "Alle Umsätze";
    } elseif ($_GET["selected"] == "allC") {
        $salesMonth = Database::getInstance()->getAllSalesV2($_GET["month"], $_GET["year"], "betreiber", "month", 'processing');
		$salesMonthCancelled = Database::getInstance()->getAllSalesV2($_GET["month"], $_GET["year"], "betreiber", "month", 'cancelled');
        //$salesYear = Database::getInstance()->getAllSalesV2($_GET["month"], $_GET["year"], "betreiber", "year");
        $salesFor = "Umsätze aller Betreiber";
    } elseif ($_GET["selected"] == "allB") {
        $salesMonth = Database::getInstance()->getAllSalesV2($_GET["month"], $_GET["year"], "vermittler", "month", 'processing');
		$salesMonthCancelled = Database::getInstance()->getAllSalesV2($_GET["month"], $_GET["year"], "vermittler", "month", 'cancelled');
        //$salesYear = Database::getInstance()->getAllSalesV2($_GET["month"], $_GET["year"], "vermittler", "year");
        $salesFor = "Umsätze aller Vermittler";
    } elseif (strpos($_GET["selected"], "c-") !== false) {
        $c_id = str_replace("c-", "", $_GET["selected"]);
        $salesMonth = Database::getInstance()->getSalesProductsV2($_GET["month"], $_GET["year"], $c_id, "betreiber", "month", 'processing');
		$salesMonthCancelled = Database::getInstance()->getSalesProductsV2($_GET["month"], $_GET["year"], $c_id, "betreiber", "month", 'cancelled');
        //$salesYear = Database::getInstance()->getSalesProductsV2($_GET["month"], $_GET["year"], $c_id, "betreiber", "year");
        $salesFor = "Umsätze " . $salesMonth[0]->parklot;
    } elseif (strpos($_GET["selected"], "b-") !== false) {
        $b_id = str_replace("b-", "", $_GET["selected"]);
        $salesMonth = Database::getInstance()->getSalesProductsV2($_GET["month"], $_GET["year"], $b_id, "vermittler", "month", 'processing');
		$salesMonthCancelled = Database::getInstance()->getSalesProductsV2($_GET["month"], $_GET["year"], $b_id, "vermittler", "month", 'cancelled');
        //$salesYear = Database::getInstance()->getSalesProductsV2($_GET["month"], $_GET["year"], $b_id, "vermittler", "year");
        $salesFor = "Umsätze " . $salesMonth[0]->parklot;
    }
} else {
    $salesMonth = Database::getInstance()->getAllSalesV2(date('n'), date('Y'), "all", "month", 'processing');
	$salesMonthCancelled = Database::getInstance()->getAllSalesV2(date('n'), date('Y'), "all", "month", 'cancelled');
    //$salesYear = Database::getInstance()->getAllSalesV2(date('n'), date('Y'), "all", "year");
    $salesFor = "Alle Umsätze";
}

$months = array('1' => 'Januar', '2' => 'Februar', '3' => 'März', '4' => 'April', '5' => 'Mai', '6' => 'Juni',
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
$c = 1;
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $c_month, $c_year);

while ($daysInMonth >= $d){
	foreach ($salesMonth as $operator){
		if ($d == $operator->Tag){
			$bookingAmount[$d]['Tag'] = $operator->Tag;
			$bookingAmount[$d]['Buchungen'] = $operator->Buchungen;
			$bookingAmount[$d]['brutto_b'] = $operator->Brutto_b;
			$bookingAmount[$d]['brutto_k'] = $operator->Brutto_k;
			$bookingAmount[$d]['provision'] = $operator->Provision;
			$inday = 1;
            break;
		}
		else 
			$inday = 0;
	}
	if ($inday == 0){
		$bookingAmount[$d]['Tag'] = $d;
		$bookingAmount[$d]['Buchungen'] = 0;
		$bookingAmount[$d]['brutto_b'] = 0;
		$bookingAmount[$d]['brutto_k'] = 0;
		$bookingAmount[$d]['provision'] = 0;
	}
	$d++;
}

while ($daysInMonth >= $c){
	foreach ($salesMonthCancelled as $operator){
		if ($c == $operator->Tag){
			$bookingAmount[$c]['Stornos'] = $operator->Buchungen;

			$inday = 1;
            break;
		}
		else 
			$inday = 0;
	}
	if ($inday == 0){
		$bookingAmount[$c]['Stornos'] = 0;
	}
	$c++;
}


//echo "<pre>";
//print_r($bookingAmount);
//echo "</pre>";

?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    google.charts.load('current', {'packages': ['corechart']});
    google.charts.setOnLoadCallback(drawVisualization_amount);
	google.charts.setOnLoadCallback(drawVisualization_payed);
	google.charts.setOnLoadCallback(drawVisualization_provision);
	
    function drawVisualization_amount() {
        // Some raw data (not necessarily accurate)
        var data = google.visualization.arrayToDataTable([
            ['Tag', 'Buchungen', { role: 'annotation'}, 'Stornos', { role: 'annotation'}],
			
			<?php
            foreach ($bookingAmount as $a) {
                
                echo "['{$a['Tag']}', {$a['Buchungen']}, '{$a['Buchungen']}', {$a['Stornos']}, '{$a['Stornos']}'],";
            }
            ?>
        ]);

        var options = {
            title: 'Buchungen und Stornos ' + <?php echo $c_month . "." . $c_year ?>,
            vAxis: {title: 'Menge'},
            hAxis: {title: 'Tag'},
            seriesType: 'bars',			
            series: {5: {type: 'line'}},
			annotations: {
				textStyle: {
				  fontSize: 11,
				  auraColor: 'none',
				}
			}
        };

        var chart = new google.visualization.ComboChart(document.getElementById('booking_amount'));
        chart.draw(data, options);
    }
	
    function drawVisualization_payed() {
        // Some raw data (not necessarily accurate)
        var data = google.visualization.arrayToDataTable([
            ['Tag', 'Barzahlung', { role: 'annotation'}, { role: 'style' }, 'Kreditkarte', { role: 'annotation'}, { role: 'style' }],
			
			<?php
            foreach ($bookingAmount as $p) {
                
                echo "['{$p['Tag']}', {$p['brutto_b']}, '{$p['brutto_b']}', 'color: #1a73e8', {$p['brutto_k']}, '{$p['brutto_k']}', 'color: #08961f'],";
            }
            ?>
        ]);

        var options = {
            title: 'Zahlungen ' + <?php echo $c_month . "." . $c_year ?>,
            vAxis: {title: 'Summe'},
            hAxis: {title: 'Tag'},
            seriesType: 'bars',
			colors: ['#1a73e8', '#08961f'],
			
            series: {5: {type: 'line'}},
			annotations: {
				textStyle: {
				  fontSize: 11,
				  auraColor: 'none',
				}
			}
        };

        var chart = new google.visualization.ComboChart(document.getElementById('booking_payed'));
        chart.draw(data, options);
    }
	
	function drawVisualization_provision() {
        // Some raw data (not necessarily accurate)
        var data = google.visualization.arrayToDataTable([
            ['Tag', 'Provision', { role: 'annotation'}],
			
			<?php
            foreach ($bookingAmount as $a) {
                
                echo "['{$a['Tag']}', {$a['provision']}, '{$a['provision']}'],";
            }
            ?>
        ]);

        var options = {
            title: 'Provisionen ' + <?php echo $c_month . "." . $c_year ?>,
            vAxis: {title: 'Summe'},
            hAxis: {title: 'Tag'},
			legend: 'none',
            seriesType: 'bars',			
            series: {5: {type: 'line'}},
			annotations: {
				textStyle: {
				  fontSize: 11,
				  auraColor: 'none',
				}
			}
        };

        var chart = new google.visualization.ComboChart(document.getElementById('booking_provision'));
        chart.draw(data, options);
    }
	
</script>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Tägliche Zahlen</h3>
    </div>
    <div class="page-body">
        <form class="form-filter">
            <div class="row ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Umsätze filtern</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-md-1">
							<input type="hidden" value="<?php echo $salesFor ?>" class="salesFor">
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
								<?php for ($i = 2021; $i <= 2022; $i++) : ?>
									<option value="<?php echo $i ?>" <?php echo $i == $c_year ? ' selected' : '' ?>>
										<?php echo $i ?>
									</option>
								<?php endfor; ?>
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
					</div>
				</div>
            </div>
			<div class="row">
				<div class="col-sm-12 col-md-12">
					<div class="chart" id="booking_amount"></div>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12 col-md-12">
					<div class="chart" id="booking_payed"></div>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12 col-md-12">
					<div class="chart" id="booking_provision"></div>
				</div>
			</div>
		</form>
	</div>
</div>



