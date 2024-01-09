<?php
$products = Database::getInstance()->getAllLots();
$date = date('Y-m-d', strtotime(date('Y-m-d')));
if((isok($_GET, 'date')))
	$date = date('Y-m-d', strtotime($_GET['date']));
$year = date('Y');
$lastyear = date('Y', strtotime('-1 year'));
$kurzfristig = Database::getInstance()->statistic_getTime_BookingsV2($year, 'kf');
$mittelfristig = Database::getInstance()->statistic_getTime_BookingsV2($year, 'mf');
$langfristig = Database::getInstance()->statistic_getTime_BookingsV2($year, 'lf');
$kurzfristig_LY = Database::getInstance()->statistic_getTime_BookingsV2($lastyear, 'kf');
$mittelfristig_LY = Database::getInstance()->statistic_getTime_BookingsV2($lastyear, 'mf');
$langfristig_LY = Database::getInstance()->statistic_getTime_BookingsV2($lastyear, 'lf');

if(count($kurzfristig) == 0){
	for($i = 0; $i <= 11; $i++){
		$kurzfristig[$i]->Anzahl = 0;
		$kurzfristig[$i]->Jahr = $year;
		$kurzfristig[$i]->Monat = $i + 1;
	}
}
if(count($mittelfristig) == 0){
	for($i = 0; $i <= 11; $i++){
		$mittelfristig[$i]->Anzahl = 0;
		$mittelfristig[$i]->Jahr = $year;
		$mittelfristig[$i]->Monat = $i + 1;
	}
}
if(count($langfristig) == 0){
	for($i = 0; $i <= 11; $i++){
		$langfristig[$i]->Anzahl = 0;
		$langfristig[$i]->Jahr = $year;
		$langfristig[$i]->Monat = $i + 1;
	}
}

if(count($kurzfristig_LY) == 0){
	for($i = 0; $i <= 11; $i++){
		$kurzfristig_LY[$i]->Anzahl = 0;
		$kurzfristig_LY[$i]->Jahr = $lastyear;
		$kurzfristig_LY[$i]->Monat = $i + 1;
	}
}
if(count($mittelfristig_LY) == 0){
	for($i = 0; $i <= 11; $i++){
		$mittelfristig_LY[$i]->Anzahl = 0;
		$mittelfristig_LY[$i]->Jahr = $lastyear;
		$mittelfristig_LY[$i]->Monat = $i + 1;
	}
}
if(count($langfristig_LY) == 0){
	for($i = 0; $i <= 11; $i++){
		$langfristig_LY[$i]->Anzahl = 0;
		$langfristig_LY[$i]->Jahr = $lastyear;
		$langfristig_LY[$i]->Monat = $i + 1;
	}
}

for($i = 1; $i <= 12; $i++){
	foreach($kurzfristig as $kf){
		if($kf->Monat == $i){
			$allData_kf[$i][$year]['Year'] = $year;
			$allData_kf[$i][$year]['Month'] = $i;
			$allData_kf[$i][$year]['Anzahl'] = $kf->Anzahl;
			break;
		}
		else{
			$allData_kf[$i][$year]['Year'] = $year;
			$allData_kf[$i][$year]['Month'] = $i;
			$allData_kf[$i][$year]['Anzahl'] = 0;
		}			
	}
	foreach($kurzfristig_LY as $kf){
		if($kf->Monat == $i){
			$allData_kf[$i][$lastyear]['Year'] = $lastyear;
			$allData_kf[$i][$lastyear]['Month'] = $i;
			$allData_kf[$i][$lastyear]['Anzahl'] = $kf->Anzahl;
			break;
		}
		else{
			$allData_kf[$i][$lastyear]['Year'] = $lastyear;
			$allData_kf[$i][$lastyear]['Month'] = $i;
			$allData_kf[$i][$lastyear]['Anzahl'] = 0;
		}			
	}
}

for($i = 1; $i <= 12; $i++){
	foreach($mittelfristig as $mf){
		if($mf->Monat == $i){
			$allData_mf[$i][$year]['Year'] = $year;
			$allData_mf[$i][$year]['Month'] = $i;
			$allData_mf[$i][$year]['Anzahl'] = $mf->Anzahl;
			break;
		}
		else{
			$allData_mf[$i][$year]['Year'] = $year;
			$allData_mf[$i][$year]['Month'] = $i;
			$allData_mf[$i][$year]['Anzahl'] = 0;
		}			
	}
	foreach($mittelfristig_LY as $mf){
		if($mf->Monat == $i){
			$allData_mf[$i][$lastyear]['Year'] = $lastyear;
			$allData_mf[$i][$lastyear]['Month'] = $i;
			$allData_mf[$i][$lastyear]['Anzahl'] = $mf->Anzahl;
			break;
		}
		else{
			$allData_mf[$i][$lastyear]['Year'] = $lastyear;
			$allData_mf[$i][$lastyear]['Month'] = $i;
			$allData_mf[$i][$lastyear]['Anzahl'] = 0;
		}			
	}
}

for($i = 1; $i <= 12; $i++){
	foreach($langfristig as $lf){
		if($lf->Monat == $i){
			$allData_lf[$i][$year]['Year'] = $year;
			$allData_lf[$i][$year]['Month'] = $i;
			$allData_lf[$i][$year]['Anzahl'] = $lf->Anzahl;
			break;
		}
		else{
			$allData_lf[$i][$year]['Year'] = $year;
			$allData_lf[$i][$year]['Month'] = $i;
			$allData_lf[$i][$year]['Anzahl'] = 0;
		}			
	}
	foreach($langfristig_LY as $lf){
		if($lf->Monat == $i){
			$allData_lf[$i][$lastyear]['Year'] = $lastyear;
			$allData_lf[$i][$lastyear]['Month'] = $i;
			$allData_lf[$i][$lastyear]['Anzahl'] = $lf->Anzahl;
			break;
		}
		else{
			$allData_lf[$i][$lastyear]['Year'] = $lastyear;
			$allData_lf[$i][$lastyear]['Month'] = $i;
			$allData_lf[$i][$lastyear]['Anzahl'] = 0;
		}			
	}
}



//echo "<pre>";
//print_r($langfristig_LY);
//echo "</pre>";

?>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    google.charts.load('current', {'packages': ['corechart']});
    google.charts.setOnLoadCallback(drawVisualization_kf);
	google.charts.setOnLoadCallback(drawVisualization_mf);
	google.charts.setOnLoadCallback(drawVisualization_lf);

    function drawVisualization_kf() {
        // Some raw data (not necessarily accurate)
        var data = google.visualization.arrayToDataTable([
            ['Monate', <?php echo "'$lastyear'" ?>, <?php echo "'$year'" ?>],
			
			<?php
            foreach ($allData_kf as $kf) {
                
                echo "['{$kf[$year]['Month']}', {$kf[$lastyear]['Anzahl']}, {$kf[$year]['Anzahl']}],";
            }
            ?>
        ]);

        var options = {
            title: 'Kurzfristige Buchungen mit Anreise innerhalb 14 Tagen',
            vAxis: {title: 'Menge'},
            hAxis: {title: 'Monat'},
            seriesType: 'bars',
            series: {5: {type: 'line'}}
        };

        var chart = new google.visualization.ComboChart(document.getElementById('kf_chart'));
        chart.draw(data, options);
    }
	
    function drawVisualization_mf() {
        // Some raw data (not necessarily accurate)
        var data = google.visualization.arrayToDataTable([
            ['Monate',<?php echo "'$lastyear'" ?>, <?php echo "'$year'" ?>],
			
			<?php
            foreach ($allData_mf as $mf) {
                
                echo "['{$mf[$year]['Month']}', {$mf[$lastyear]['Anzahl']}, {$mf[$year]['Anzahl']}],";
            }
            ?>
        ]);

        var options = {
            title: 'Mittelfristige Buchungen mit Anreise innerhalb 90 Tagen',
            vAxis: {title: 'Menge'},
            hAxis: {title: 'Monat'},
            seriesType: 'bars',
            series: {5: {type: 'line'}}
        };

        var chart = new google.visualization.ComboChart(document.getElementById('mf_chart'));
        chart.draw(data, options);
    }
	
function drawVisualization_lf() {
        // Some raw data (not necessarily accurate)
        var data = google.visualization.arrayToDataTable([
            ['Monate',<?php echo "'$lastyear'" ?>, <?php echo "'$year'" ?>],
			
			<?php
            foreach ($allData_lf as $lf) {
                
                echo "['{$lf[$year]['Month']}', {$lf[$lastyear]['Anzahl']}, {$lf[$year]['Anzahl']}],";
            }
            ?>
        ]);

        var options = {
            title: 'Langfristige Buchungen mit Anreise ab 90 Tagen',
            vAxis: {title: 'Menge'},
            hAxis: {title: 'Monat'},
            seriesType: 'bars',
            series: {5: {type: 'line'}}
        };

        var chart = new google.visualization.ComboChart(document.getElementById('lf_chart'));
        chart.draw(data, options);
    }
</script>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-title itweb_adminpage_head">
        <h3>Vorlaufzeit</h3>
    </div>
    <div class="page-body">
        <form class="form-filter">
            <!--<div class="row my-2 ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Nach Datum filtern</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row my-2">
						<div class="col-sm-12 col-md-1 col-lg-1 ui-lotdata-date">
							<input type="text" name="date" placeholder="Buchung von" class="form-item form-control single-datepicker" value="<?php if($date != "") echo date('d.m.Y', strtotime($date)); else echo ''; ?>">
						</div>	
						<div class="col-sm-12 col-md-3 col-lg-2">
							<select name="product" class="form-item form-control">
								<option value="">Produkt</option>
								<?php foreach($products as $product) : ?>
									<option value="<?php echo $product->product_id ?>"
										<?php echo (isset($_GET['product']) && $_GET['product'] == $product->product_id) ? ' selected' : '' ?>>
										<?php echo $product->parklot ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-sm-12 col-md-3 col-lg-2">
							<button class="btn btn-primary d-block w-100" type="submit">Filter</button>
						</div>
						<div class="col-sm-12 col-md-2">                    
							<a href="<?php echo '/wp-admin/admin.php?page=time-booking' ?>" class="btn btn-secondary d-block w-100" >Zurücksetzen</a>
						</div>
					</div>
				</div>
            </div>-->
        </form>
		<br><br>
		<div class="row">
			<div class="col-sm-12 col-md-4">
				<div class="chart" id="kf_chart"></div>
			</div>
			<div class="col-sm-12 col-md-4">
				<div class="chart" id="mf_chart"></div>
			</div>
			<div class="col-sm-12 col-md-4">
				<div class="chart" id="lf_chart"></div>
			</div>
		</div>
    </div>
</div>