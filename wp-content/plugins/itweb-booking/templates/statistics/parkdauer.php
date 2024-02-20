<?php 

if(empty($_GET['site']) || $_GET['site'] == 1){
	$c_month = date('n');
	$c_year = date('Y');
	$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $c_month, $c_year);
	
	$dateto = date('Y-m-d', strtotime(date($c_year."-".$c_month."-".$daysInMonth)));
	$datefrom = date('Y-m-d', strtotime(date($c_year."-".$c_month."-01")));
	if($_GET['date_from'])
		$datefrom = date('Y-m-d', strtotime($_GET['date_from']));
	if($_GET['date_to'])
		$dateto = date('Y-m-d', strtotime($_GET['date_to']));
	$token = $anreise = $abreise = "";
	unset($_GET['anreiseVon']);
	unset($_GET['anreiseBis']);
	unset($_GET['token']);
	$filter['datum_von'] = $filter['datum_bis'] = $filter['token'] = "";
	$filter['buchung_von'] = $datefrom;
	$filter['buchung_bis'] = date('Y-m-d', strtotime($dateto . '+1 day'));
	$filter['orderBy'] = "Buchungsdatum";
	$filter['betreiber'] = $_GET['betreiber'] != null ? $_GET['betreiber'] : "";
	$allorders = Database::getInstance()->get_bookinglistV2("wc-processing", $filter, "");

	foreach($allorders as $booking){
		$duration = $booking->Anreisedatum != null && $booking->Abreisedatum != null && $booking->is_for != 'hotel' ? getDaysBetween2Dates(new DateTime($booking->Anreisedatum), new DateTime($booking->Abreisedatum)) : 0;
		$parkdauer[$duration] += 1;
	}

	for($i = 1; $i <= 30; $i++){
		if($parkdauer[$i] == null)
			$parkdauer[$i] = 0;
	}
	ksort($parkdauer);
	
	$titel_duration = date('d.m.Y', strtotime($datefrom));
	$titel_duration .= $datefrom != $dateto ? " - " . date('d.m.Y', strtotime($dateto)) : "";
	$titel_duration .= isset($_GET['betreiber']) ? " | " . $_GET['betreiber'] : " | Alle Betreiber";
}

if($_GET['site'] == 2){
	$months = array('1' => 'Januar', '2' => 'Februar', '3' => 'März', '4' => 'April', '5' => 'Mai', '6' => 'Juni',
				'7' => 'Juli', '8' => 'August', '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Dezember');
	if (isset($_GET["month"]))
		$c_month = $_GET["month"];
	else
		$c_month = date('n');
	if (isset($_GET["year"]))
		$c_year = $_GET["year"];
	else
		$c_year = date('Y');
	$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $c_month, $c_year);
	
	if (isset($_GET['month']) && $_GET['month'] < 10)
		$zero = '0';
	else
		$zero = '';
	$m = isset($_GET['month']) ? $zero . $_GET['month'] : date('m');
	$datefrom = date('Y-m-d', strtotime(date($c_year . "-" . $m . "-01")));
	$w_datefrom = date('Y-m-d', strtotime(date($c_year . "-" . $m . "-01")));
	$dateto = date('Y-m-d', strtotime(date($c_year . "-" . $m . "-" . $daysInMonth)));
	$wochentage = array("So.", "Mo.", "Di.", "Mi.", "Do.", "Fr.", "Sa.");

	$token = $anreise = $abreise = "";
	unset($_GET['anreiseVon']);
	unset($_GET['anreiseBis']);
	unset($_GET['token']);
	$filter['datum_von'] = $filter['datum_bis'] = $filter['token'] = "";
	$filter['buchung_von'] = $datefrom;
	$filter['buchung_bis'] = date('Y-m-d', strtotime($dateto . '+1 day'));
	$filter['orderBy'] = "Buchungsdatum";
	$filter['betreiber'] = $_GET['betreiber'] != null ? $_GET['betreiber'] : "";
	$allorders = Database::getInstance()->get_bookinglistV2("wc-processing", $filter, "");

	foreach($allorders as $booking){
		$wt = $wochentage[date("w", strtotime($booking->Buchungsdatum))];
		$wochentag[$wt] += 1;
		$calendar[date('Y-m-d', strtotime($booking->Buchungsdatum))][$wochentage[date("w", strtotime($booking->Buchungsdatum))]] += 1;
		$sum_cal[$wochentage[date("w", strtotime($booking->Buchungsdatum))]] += 1;
		$all_sum_cal += 1;
	}
	foreach($wochentage as $key => $val){
		if($wochentag[$val] == null)
		$wochentag[$val] = 0;
		
		if($sum_cal[$val] == null)
			$sum_cal[$val] = 0;
	}
	
	if($all_sum_cal == null)
		$all_sum_cal = 0;
	
	foreach($wochentag as $key => $val){
		if($key == "Mo."){
			$sort_wochentag[0]['day'] = $key;
			$sort_wochentag[0]['val'] = $val;
		}
		elseif($key == "Di."){
			$sort_wochentag[1]['day'] = $key;
			$sort_wochentag[1]['val'] = $val;
		}
		elseif($key == "Mi."){
			$sort_wochentag[2]['day'] = $key;
			$sort_wochentag[2]['val'] = $val;
		}
		elseif($key == "Do."){
			$sort_wochentag[3]['day'] = $key;
			$sort_wochentag[3]['val'] = $val;
		}
		elseif($key == "Fr."){
			$sort_wochentag[4]['day'] = $key;
			$sort_wochentag[4]['val'] = $val;
		}
		elseif($key == "Sa."){
			$sort_wochentag[5]['day'] = $key;
			$sort_wochentag[5]['val'] = $val;
		}
		elseif($key == "So."){
			$sort_wochentag[6]['day'] = $key;
			$sort_wochentag[6]['val'] = $val;
		}
	}
	ksort($sort_wochentag);
	
	while($w_datefrom <= $dateto){
		if($calendar[$w_datefrom][$wochentage[date("w", strtotime($w_datefrom))]] == null)
			$calendar[$w_datefrom][$wochentage[date("w", strtotime($w_datefrom))]] = 0;
		$w_datefrom = date('Y-m-d', strtotime($w_datefrom . '+1 day'));
	}
	
	$titel_weekday = $months[$c_month] . " " . $c_year;
	$titel_weekday .= isset($_GET['betreiber']) ? " | " . $_GET['betreiber'] : " | Alle Betreiber";
}

if($_GET['site'] == 3){
	$c_month = date('n');
	$c_year = date('Y');
	$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $c_month, $c_year);
	
	$dateto = date('Y-m-d', strtotime(date($c_year."-".$c_month."-".$daysInMonth)));
	$datefrom = date('Y-m-d', strtotime(date($c_year."-".$c_month."-01")));
	if($_GET['date_from'])
		$datefrom = date('Y-m-d', strtotime($_GET['date_from']));
	if($_GET['date_to'])
		$dateto = date('Y-m-d', strtotime($_GET['date_to']));
	$token = $anreise = $abreise = "";
	unset($_GET['anreiseVon']);
	unset($_GET['anreiseBis']);
	unset($_GET['token']);
	$filter['datum_von'] = $filter['datum_bis'] = $filter['token'] = "";
	$filter['buchung_von'] = $datefrom;
	$filter['buchung_bis'] = date('Y-m-d', strtotime($dateto . '+1 day'));
	$filter['orderBy'] = "Buchungsdatum";
	$filter['betreiber'] = $_GET['betreiber'] != null ? $_GET['betreiber'] : "";
	$allorders = Database::getInstance()->get_bookinglistV2("wc-processing", $filter, "");

	foreach($allorders as $booking){
		$zeit =  date('H', strtotime($booking->Uhrzeit_von));
		if($zeit >= 3 && $zeit < 6)
			$anreisezeit['03-06'] += 1;
		elseif($zeit >= 6 && $zeit < 12)
			$anreisezeit['06-12'] += 1;
		elseif($zeit >= 12)
			$anreisezeit['nach 12'] += 1;
		else
			$anreisezeit['sonstige Zeit'] += 1;
	}
	
	if($anreisezeit['03-06'] == null)
		$anreisezeit['03-06'] = 0;
	if($anreisezeit['06-12'] == null)
		$anreisezeit['06-12'] = 0;
	if($anreisezeit['nach 12'] == null)
		$anreisezeit['nach 12'] = 0;
	
	ksort($anreisezeit);
	
	$titel_times = date('d.m.Y', strtotime($datefrom));
	$titel_times .= $datefrom != $dateto ? " - " . date('d.m.Y', strtotime($dateto)) : "";
	$titel_times .= isset($_GET['betreiber']) ? " | " . $_GET['betreiber'] : " | Alle Betreiber";
	
	
	unset($_GET['anreiseVon']);
	unset($_GET['anreiseBis']);
	unset($_GET['token']);
	$filter['datum_von'] = $filter['datum_bis'] = $filter['token'] = "";
	$filter['buchung_von'] = $datefrom;
	$filter['buchung_bis'] = date('Y-m-d', strtotime($dateto . '+1 day'));
	$filter['orderBy'] = "Buchungsdatum";
	$allBookings = Database::getInstance()->get_bookinglistV2("wc-processing", $filter, "");
	
	foreach($allBookings as $booking){
		$zeit =  date('H', strtotime($booking->Buchungsdatum));
		if($zeit >= 3 && $zeit < 6)
			$anreisezeit_b['03-06'] += 1;
		elseif($zeit >= 6 && $zeit < 9)
			$anreisezeit_b['06-09'] += 1;
		elseif($zeit >= 9 && $zeit < 12)
			$anreisezeit_b['09-12'] += 1;
		elseif($zeit >= 12 && $zeit < 15)
			$anreisezeit_b['12-15'] += 1;
		elseif($zeit >= 15 && $zeit < 18)
			$anreisezeit_b['15-18'] += 1;
		elseif($zeit >= 18 && $zeit < 21)
			$anreisezeit_b['18-21'] += 1;
		elseif($zeit >= 21 && $zeit < 23)
			$anreisezeit_b['21-23'] += 1;
		else
			$anreisezeit_b['sonstige Zeit'] += 1;
	}
	
	if($anreisezeit_b['03-06'] == null)
		$anreisezeit_b['03-06'] = 0;
	if($anreisezeit_b['06-09'] == null)
		$anreisezeit_b['06-09'] = 0;
	if($anreisezeit_b['09-12'] == null)
		$anreisezeit_b['09-12'] = 0;
	if($anreisezeit_b['12-15'] == null)
		$anreisezeit_b['12-15'] = 0;
	if($anreisezeit_b['15-18'] == null)
		$anreisezeit_b['15-18'] = 0;
	if($anreisezeit_b['18-21'] == null)
		$anreisezeit_b['18-21'] = 0;
	if($anreisezeit_b['21-23'] == null)
		$anreisezeit_b['21-23'] = 0;
	
	ksort($anreisezeit_b);
	
	$titel_times_b = date('d.m.Y', strtotime($datefrom));
	$titel_times_b .= $datefrom != $dateto ? " - " . date('d.m.Y', strtotime($dateto)) : "";
	$titel_times_b .= isset($_GET['betreiber']) ? " | " . $_GET['betreiber'] : " | Alle Betreiber";
}

//echo "<pre>"; print_r($allBookings); echo "</pre>";


?>


<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<?php if(empty($_GET['site']) || $_GET['site'] == 1): ?>
	<script type="text/javascript">
	google.charts.load('current', {'packages': ['corechart']});
	google.charts.setOnLoadCallback(drawVisualization_day_duration);
	function drawVisualization_day_duration() {
		// Some raw data (not necessarily accurate)
		var data = google.visualization.arrayToDataTable([
			['Parkdauer', 'Buchungen', { role: 'annotation'}],
			
			<?php
			foreach ($parkdauer as $key => $val) {
				
				echo "['{$key}', {$val}, '{$val}'],";
			}
			?>
		]);

		var options = {
			title: 'Parkdauer | Buchungsdatum <?php echo $titel_duration ?>',
			vAxis: {title: 'Buchungen'},
			hAxis: {title: 'Parkdauer in Tagen'},
			seriesType: 'bars',
			legend: 'none',		
			series: {5: {type: 'line'}},
			annotations: {
				textStyle: {
				  fontSize: 11,
				  auraColor: 'none',
				}
			}
		};

		var chart = new google.visualization.ComboChart(document.getElementById('day_duration'));
		chart.draw(data, options);
	}
	</script>
<?php endif; ?>
<?php if($_GET['site'] == 2): ?>
	<script type="text/javascript">
	google.charts.load('current', {'packages': ['corechart']});
	google.charts.setOnLoadCallback(drawVisualization_weekdays);
	function drawVisualization_weekdays() {
		// Some raw data (not necessarily accurate)
		var data = google.visualization.arrayToDataTable([
			['Parkdauer', 'Buchungen', { role: 'annotation'}],
			
			<?php
			foreach ($sort_wochentag as $key => $val) {	
				echo "['{$sort_wochentag[$key][day]}', {$sort_wochentag[$key][val]}, '{$sort_wochentag[$key][val]}'],";
			}
			?>
		]);

		var options = {
			title: 'Wochentage | Buchungsmonat <?php echo $titel_weekday ?>',
			vAxis: {title: 'Buchungen'},
			hAxis: {title: 'Wochentag'},
			seriesType: 'bars',
			legend: 'none',		
			series: {5: {type: 'line'}},
			annotations: {
				textStyle: {
				  fontSize: 11,
				  auraColor: 'none',
				}
			}
		};

		var chart = new google.visualization.ComboChart(document.getElementById('weekdays'));
		chart.draw(data, options);
	}
	</script>
<?php endif; ?>
<?php if($_GET['site'] == 3): ?>
	<script type="text/javascript">
	google.charts.load('current', {'packages': ['corechart']});
	google.charts.setOnLoadCallback(drawVisualization_times);
	function drawVisualization_times() {
		// Some raw data (not necessarily accurate)
		var data = google.visualization.arrayToDataTable([
			['Parkdauer', 'Buchungen', { role: 'annotation'}],
			
			<?php
			foreach ($anreisezeit as $key => $val) {
				
				echo "['{$key}', {$val}, '{$val}'],";
			}
			?>
		]);

		var options = {
			title: 'Anreisezeit | Buchungsdatum <?php echo $titel_times ?>',
			vAxis: {title: 'Buchungen'},
			hAxis: {title: 'Anreisezeit'},
			seriesType: 'bars',
			legend: 'none',		
			series: {5: {type: 'line'}},
			annotations: {
				textStyle: {
				  fontSize: 11,
				  auraColor: 'none',
				}
			}
		};

		var chart = new google.visualization.ComboChart(document.getElementById('times'));
		chart.draw(data, options);
	}
	
	google.charts.setOnLoadCallback(drawVisualization_times_b);
	function drawVisualization_times_b() {
		// Some raw data (not necessarily accurate)
		var data = google.visualization.arrayToDataTable([
			['Parkdauer', 'Buchungen', { role: 'annotation'}],
			
			<?php
			foreach ($anreisezeit_b as $key => $val) {
				
				echo "['{$key}', {$val}, '{$val}'],";
			}
			?>
		]);

		var options = {
			title: 'Buchungszeit | Buchungsdatum <?php echo $titel_times_b ?>',
			vAxis: {title: 'Buchungen'},
			hAxis: {title: 'Anreisezeit'},
			seriesType: 'bars',
			legend: 'none',		
			series: {5: {type: 'line'}},
			annotations: {
				textStyle: {
				  fontSize: 11,
				  auraColor: 'none',
				}
			}
		};

		var chart = new google.visualization.ComboChart(document.getElementById('times_b'));
		chart.draw(data, options);
	}
	</script>
<?php endif; ?>
<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <?php if(empty($_GET['site']) || $_GET['site'] == 1): ?>
		<div class="page-title itweb_adminpage_head">
			<h3>Buchungen nach Parkdauer</h3>
		</div>
		<div class="page-body">
			<form class="form-filter">
				<div class="row ui-lotdata-block ui-lotdata-block-next">
					<h5 class="ui-lotdata-title">Filter</h5>
					<div class="col-sm-12 col-md-12 ui-lotdata">
						<div class="row">
							<div class="col-sm-12 col-md-1 col-lg-1 ui-lotdata-date">
								<input type="text" id="dateFrom" name="date_from" placeholder="Buchung von" class="form-item form-control single-datepicker" value="<?php echo $datefrom != "" ? date('d.m.Y', strtotime($datefrom)) : ''; ?>">
							</div>
							<div class="col-sm-12 col-md-1 col-lg-1 ui-lotdata-date">
								<input type="text" id="dateTo" name="date_to" placeholder="Buchung bis" class="form-item form-control single-datepicker" value="<?php echo $dateto != "" ? date('d.m.Y', strtotime($dateto)) : ''; ?>">
							</div>
							<div class="col-sm-12 col-md-2">
								<select name="betreiber" class="form-item form-control">
									<option value="">Betreiber</option>
									<option value="aps" <?php echo (isset($_GET['betreiber']) && $_GET['betreiber'] == 'aps') ? ' selected' : '' ?>>
										<?php echo 'APS' ?>
									</option>
									<option value="apg" <?php echo (isset($_GET['betreiber']) && $_GET['betreiber'] == 'apg') ? ' selected' : '' ?>>
										<?php echo 'APG' ?>
									</option>
									<option value="hex" <?php echo (isset($_GET['betreiber']) && $_GET['betreiber'] == 'hex') ? ' selected' : '' ?>>
										<?php echo 'HEX' ?>
									</option>
									<option value="parkos" <?php echo (isset($_GET['betreiber']) && $_GET['betreiber'] == 'parkos') ? ' selected' : '' ?>>
										<?php echo 'Parkos' ?>
									</option>
								</select>
							</div>
							<div class="col-sm-12 col-md-1">
								<button class="btn btn-primary d-block w-100" type="submit">Filter</button>
							</div>
							<div class="col-sm-12 col-md-2">                    
								<a href="<?php echo '/wp-admin/admin.php?page=bookings' ?>" class="btn btn-secondary d-block w-100" >Zurücksetzen</a>
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-sm-12 col-md-3">
								<?php $paras = $_GET['betreiber'] != null ? "&betreiber=".$_GET['betreiber'] : ""; ?>								
								<a href="<?php echo "admin.php?page=bookings&site=2".$paras ?>" class="btn btn-primary">Buchungen nach Wochentagen</a>
							</div>
							<div class="col-sm-12 col-md-3">
								<?php $paras = $_GET['betreiber'] != null ? "&betreiber=".$_GET['betreiber'] : ""; ?>
								<?php $paras .= $_GET['date_from'] != null ? "&date_from=".$_GET['date_from'] . "&date_to=".$_GET['date_to'] : ""; ?>								
								<a href="<?php echo "admin.php?page=bookings&site=3".$paras ?>" class="btn btn-primary">Buchungen nach Uhrzeit</a>
							</div>
						</div>
					</div>
				</div>			
			</form>
			<div class="row">
				<div class="col-sm-12 col-md-12">
					<div class="chart" id="day_duration"></div>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php if($_GET['site'] == 2): ?>
		<div class="page-title itweb_adminpage_head">
			<h3>Buchungen nach Wochentagen</h3>
		</div>
		<div class="page-body">
			<form class="form-filter">
				<div class="row ui-lotdata-block ui-lotdata-block-next">
					<h5 class="ui-lotdata-title">Filter</h5>
					<div class="col-sm-12 col-md-12 ui-lotdata">
						<div class="row">
							<div class="col-sm-12 col-md-1 col-lg-1 ui-lotdata-date">
								<select name="month" class="form-item form-control">
								<?php foreach ($months as $key => $value) : ?>
									<option value="<?php echo $key ?>" <?php echo $key == $c_month ? ' selected' : '' ?>>
										<?php echo $value ?>
									</option>
								<?php endforeach; ?>
								</select>
							</div>							
							<div class="col-sm-12 col-md-1 col-lg-1 ui-lotdata-date">
								<select name="year" class="form-item form-control">
								<?php for ($i = 2021; $i <= date('Y'); $i++) : ?>
									<option value="<?php echo $i ?>" <?php echo $i == $c_year ? ' selected' : '' ?>>
										<?php echo $i ?>
									</option>
								<?php endfor; ?>
								</select>
							</div>
							<div class="col-sm-12 col-md-1 col-lg-1 ui-lotdata-date">
								<select name="weekday" class="form-item form-control">
									<option value="">Wochentag</option>
									<option value="1" <?php echo isset($_GET['weekday']) && $_GET['weekday'] == 1 ? ' selected' : '' ?>>Montag</option>
									<option value="2" <?php echo isset($_GET['weekday']) && $_GET['weekday'] == 2 ? ' selected' : '' ?>>Dienstag</option>
									<option value="3" <?php echo isset($_GET['weekday']) && $_GET['weekday'] == 3 ? ' selected' : '' ?>>Mittwoch</option>
									<option value="4" <?php echo isset($_GET['weekday']) && $_GET['weekday'] == 4 ? ' selected' : '' ?>>Donnerstag</option>
									<option value="5" <?php echo isset($_GET['weekday']) && $_GET['weekday'] == 5 ? ' selected' : '' ?>>Freitag</option>
									<option value="6" <?php echo isset($_GET['weekday']) && $_GET['weekday'] == 6 ? ' selected' : '' ?>>Samstag</option>
									<option value="0" <?php echo isset($_GET['weekday']) && $_GET['weekday'] == 0 ? ' selected' : '' ?>>Sonntag</option>
								</select>
							</div>
							<div class="col-sm-12 col-md-2">
								<select name="betreiber" class="form-item form-control">
									<option value="">Betreiber</option>
									<option value="aps" <?php echo (isset($_GET['betreiber']) && $_GET['betreiber'] == 'aps') ? ' selected' : '' ?>>
										<?php echo 'APS' ?>
									</option>
									<option value="apg" <?php echo (isset($_GET['betreiber']) && $_GET['betreiber'] == 'apg') ? ' selected' : '' ?>>
										<?php echo 'APG' ?>
									</option>
									<option value="hex" <?php echo (isset($_GET['betreiber']) && $_GET['betreiber'] == 'hex') ? ' selected' : '' ?>>
										<?php echo 'HEX' ?>
									</option>
									<option value="parkos" <?php echo (isset($_GET['betreiber']) && $_GET['betreiber'] == 'parkos') ? ' selected' : '' ?>>
										<?php echo 'Parkos' ?>
									</option>
								</select>
							</div>
							<div class="col-sm-12 col-md-1">
								<button class="btn btn-primary d-block w-100" type="submit">Filter</button>
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-sm-12 col-md-3">
								<?php $paras = $_GET['betreiber'] != null ? "&betreiber=".$_GET['betreiber'] : ""; ?>
								<a href="<?php echo "admin.php?page=bookings&site=1".$paras ?>" class="btn btn-primary">Buchungen nach Parkdauer</a>
							</div>
							<div class="col-sm-12 col-md-3">
								<?php $paras = $_GET['betreiber'] != null ? "&betreiber=".$_GET['betreiber'] : ""; ?>															
								<a href="<?php echo "admin.php?page=bookings&site=3".$paras ?>" class="btn btn-primary">Buchungen nach Uhrzeit</a>
							</div>
						</div>
					</div>
				</div>			
			</form>
			<div class="row">
				<div class="col-sm-12 col-md-6">
					<div class="chart" id="weekdays"></div>
				</div>
				<div class="col-sm-12 col-md-4">
					<h4>Buchungen <?php echo isset($_GET['weekday']) ? "| Buchungstag " . $wochentage[$_GET['weekday']] : "| Buchungsmonat " . $months[$c_month]; echo isset($_GET['betreiber']) ? " | " . $_GET['betreiber'] : " | Alle Betreiber" ?></h4>
					<table class="table table-sm">
						<thead>
							<tr>
								<th>Datum</th>
								<th>Buchungen</th>
						</thead>
						<tbody>
							<?php foreach($calendar as $date => $wd): ?>
								<?php if(isset($_GET['weekday']) && $_GET['weekday'] != date("w", strtotime($date))) continue; ?>
								<tr>
									<td><?php echo $wochentage[date("w", strtotime($date))] . " " . date('d.m.Y', strtotime($date)); ?></td>
									<td><?php echo $calendar[$date][$wochentage[date("w", strtotime($date))]] ?></td>
								</tr>
							<?php endforeach; ?>
							<tr>
								<td><strong>Summe</strong></td>
								<td><strong><?php echo isset($_GET['weekday']) ? $sum_cal[$wochentage[$_GET['weekday']]] : $all_sum_cal ?></strong></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php if($_GET['site'] == 3): ?>
		<div class="page-title itweb_adminpage_head">
			<h3>Buchungen nach Anreise- und Buchungsuhrzeit</h3>
		</div>
		<div class="page-body">
			<form class="form-filter">
				<div class="row ui-lotdata-block ui-lotdata-block-next">
					<h5 class="ui-lotdata-title">Filter</h5>
					<div class="col-sm-12 col-md-12 ui-lotdata">
						<div class="row">
							<div class="col-sm-12 col-md-1 col-lg-1 ui-lotdata-date">
								<input type="text" id="dateFrom" name="date_from" placeholder="Buchung von" class="form-item form-control single-datepicker" value="<?php echo $datefrom != "" ? date('d.m.Y', strtotime($datefrom)) : ''; ?>">
							</div>
							<div class="col-sm-12 col-md-1 col-lg-1 ui-lotdata-date">
								<input type="text" id="dateTo" name="date_to" placeholder="Buchung bis" class="form-item form-control single-datepicker" value="<?php echo $dateto != "" ? date('d.m.Y', strtotime($dateto)) : ''; ?>">
							</div>
							<div class="col-sm-12 col-md-2">
								<select name="betreiber" class="form-item form-control">
									<option value="">Betreiber</option>
									<option value="aps" <?php echo (isset($_GET['betreiber']) && $_GET['betreiber'] == 'aps') ? ' selected' : '' ?>>
										<?php echo 'APS' ?>
									</option>
									<option value="apg" <?php echo (isset($_GET['betreiber']) && $_GET['betreiber'] == 'apg') ? ' selected' : '' ?>>
										<?php echo 'APG' ?>
									</option>
									<option value="hex" <?php echo (isset($_GET['betreiber']) && $_GET['betreiber'] == 'hex') ? ' selected' : '' ?>>
										<?php echo 'HEX' ?>
									</option>
									<option value="parkos" <?php echo (isset($_GET['betreiber']) && $_GET['betreiber'] == 'parkos') ? ' selected' : '' ?>>
										<?php echo 'Parkos' ?>
									</option>
								</select>
							</div>
							<div class="col-sm-12 col-md-1">
								<button class="btn btn-primary d-block w-100" type="submit">Filter</button>
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-sm-12 col-md-3">
								<?php $paras = $_GET['betreiber'] != null ? "&betreiber=".$_GET['betreiber'] : ""; ?>								
								<?php $paras .= $_GET['date_from'] != null ? "&date_from=".$_GET['date_from'] . "&date_to=".$_GET['date_to'] : ""; ?>
								<a href="<?php echo "admin.php?page=bookings&site=1".$paras ?>" class="btn btn-primary">Buchungen nach Parkdauer</a>
							</div>
							<div class="col-sm-12 col-md-3">
								<?php $paras = $_GET['betreiber'] != null ? "&betreiber=".$_GET['betreiber'] : ""; ?>																
								<a href="<?php echo "admin.php?page=bookings&site=2".$paras ?>" class="btn btn-primary">Buchungen nach Wochentagen</a>
							</div>							
						</div>
					</div>
				</div>			
			</form>
			<div class="row">
				<div class="col-sm-12 col-md-6">
					<div class="chart" id="times"></div>
				</div>
				<div class="col-sm-12 col-md-6">
					<div class="chart" id="times_b"></div>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>

