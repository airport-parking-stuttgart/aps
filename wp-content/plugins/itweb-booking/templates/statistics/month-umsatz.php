<?php 


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


$yearBookings = Database::getInstance()->statistic_getBookingsV2($c_year, 'processing');
$yearBookingsCancelled = Database::getInstance()->statistic_getBookingsV2($c_year, 'cancelled');

if(count($yearBookings) == 0){
	for($i = 0; $i <= 11; $i++){
		$yearBookings[$i]->Anzahl = 0;
		$yearBookings[$i]->Barzahlung = 0;
		$yearBookings[$i]->Kreditkarte = 0;
		$yearBookings[$i]->Monat = $i + 1;
	}
}
if(count($yearBookingsCancelled) == 0){
	for($i = 0; $i <= 11; $i++){
		$yearBookingsCancelled[$i]->Stornos = 0;
	}
}


for($i = 1; $i <= 12; $i++){
	foreach($yearBookings as $kf){
		if($kf->Monat == $i){
			$BookingsMonth[$i]['Month'] = $i;
			$BookingsMonth[$i]['Anzahl'] = $kf->Anzahl;
			$BookingsMonth[$i]['Barzahlung'] = round($kf->Barzahlung,2);
			$BookingsMonth[$i]['Kreditkarte'] = round($kf->Kreditkarte,2);
			break;
		}
		else{
			$BookingsMonth[$i]['Month'] = $i;
			$BookingsMonth[$i]['Anzahl'] = 0;
			$BookingsMonth[$i]['Barzahlung'] = 0;
			$BookingsMonth[$i]['Kreditkarte'] = 0;
		}			
	}
}
for($i = 1; $i <= 12; $i++){
	foreach($yearBookingsCancelled as $kf){
		if($kf->Monat == $i){
			$BookingsMonth[$i]['Stornos'] = $kf->Anzahl;
			break;
		}
		else{
			$BookingsMonth[$i]['Stornos'] = 0;
		}			
	}
}


//echo "<pre>"; print_r($yearBookingsCancelled); echo "</pre>";
?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    google.charts.load('current', {'packages': ['corechart']});
	google.charts.setOnLoadCallback(drawVisualization_year);
	google.charts.setOnLoadCallback(drawVisualization_payed);
	
    function drawVisualization_year() {
        // Some raw data (not necessarily accurate)
        var data = google.visualization.arrayToDataTable([
            ['Monate', 'Buchungen', { role: 'annotation'}, 'Stornos', { role: 'annotation'}],
			
			<?php
            foreach ($BookingsMonth as $y) {
                
                echo "['{$y['Month']}', {$y['Anzahl']}, '{$y['Anzahl']}', {$y['Stornos']}, '{$y['Stornos']}'],";
            }
            ?>
        ]);

        var options = {
            title: 'Buchungen und Stornos ' + <?php echo $c_year ?>,
            vAxis: {title: 'Menge'},
            hAxis: {title: 'Monat'},
            seriesType: 'bars',			
            series: {5: {type: 'line'}},
			annotations: {
				textStyle: {
				  fontSize: 11,
				  auraColor: 'none',
				  color: '#871b47',
				}
			}
        };

        var chart = new google.visualization.ComboChart(document.getElementById('booking_year'));
        chart.draw(data, options);
    }
	
	function drawVisualization_payed() {
        // Some raw data (not necessarily accurate)
        var data = google.visualization.arrayToDataTable([
            ['Monat', 'Barzahlung', { role: 'annotation'}, { role: 'style' }, 'Kreditkarte', { role: 'annotation'}, { role: 'style' }],
			
			<?php
            foreach ($BookingsMonth as $p) {
                
                echo "['{$p['Month']}', {$p['Barzahlung']}, '{$p['Barzahlung']}', 'color: #1a73e8', {$p['Kreditkarte']}, '{$p['Kreditkarte']}', 'color: #08961f'],";
            }
            ?>
        ]);

        var options = {
            title: 'Zahlungen ' + <?php echo $c_year ?>,
            vAxis: {title: 'Summe'},
            hAxis: {title: 'Monat'},
            seriesType: 'bars',
			colors: ['#1a73e8', '#08961f'],
			
            series: {5: {type: 'line'}},
			annotations: {
				textStyle: {
				  fontSize: 11,
				  auraColor: 'none',
				  color: '#871b47',
				}
			}
        };

        var chart = new google.visualization.ComboChart(document.getElementById('booking_payed'));
        chart.draw(data, options);
    }
</script>

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
							<select name="year" class="form-item form-control">
								<?php for ($i = 2021; $i <= date('Y'); $i++) : ?>
									<option value="<?php echo $i ?>" <?php echo $i == $c_year ? ' selected' : '' ?>>
										<?php echo $i ?>
									</option>
								<?php endfor; ?>
							</select>
						</div>
						<div class="col-sm-12 col-md-1">
							<button class="btn btn-primary d-block w-100" type="submit">Filter</button>
						</div>
					</div>
				</div>
            </div>
			<div class="row">
				<div class="col-sm-12 col-md-6">
					<div class="chart" id="booking_year"></div>
				</div>
				<div class="col-sm-12 col-md-6">
					<div class="chart" id="booking_payed"></div>
				</div>
			</div>
		</form>
	</div>
</div>



