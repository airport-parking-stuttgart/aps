<?php

global $wpdb;

$months = array('1' => 'Januar', '2' => 'Fabruar', '3' => 'März', '4' => 'April', '5' => 'Mai', '6' => 'Juni',
				'7' => 'Juli', '8' => 'August', '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Dezember');
$c_month = date('n');
$c_year = date('Y');

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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

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
			<div class="col-sm-12 col-md-4" id="show_td_btn">
				<h4>Buchungen heute</h4>
				<button onclick="load_td(this)" class="btn btn-primary">Anzeigen</button>
				<hr>
			</div>
			<div class="col-sm-12 col-md-4 block" id="table_td" style="display: none"></div>
			<script>
			function load_td(e) {
				var helperUrl = '/wp-content/plugins/itweb-booking/templates/dashboard/parts/today.php';
				const td = document.getElementById("table_td");
				const show_td_btn = document.getElementById("show_td_btn");
				const show_td_diag = document.getElementById("diagramm_td");
				e.innerHTML = "Lade <i class='fas fa-spinner fa-pulse'></i>";
				$ = jQuery;
				$.ajax({
					type:"POST",
					url:helperUrl,
					traditional:true,
					dataType: "json",
					success:function(data){
						var content = data.content;
						var diagramm = data.diagramm;
						td.innerHTML = content;
						set_td_diagram(diagramm);
						show_td_btn.style = "display: none";
						td.style = "display: block";
						show_td_diag.style = "display: block";
					},
					error:function(data){
					}
				});
			}
			function set_td_diagram(diagramm) {
				google.charts.setOnLoadCallback(drawVisualization_buchungen_heute);
				function drawVisualization_buchungen_heute() {
					// Some raw data (not necessarily accurate)
					var data = google.visualization.arrayToDataTable([
						['Betreiber', 'Umsatz', {role: 'annotation'}],
						['', 0, ''],
					]);
					for (var i = 0; i < diagramm.length; i++) {
							data.addRow(diagramm[i]);
						}
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
			}
			</script>
			<div class="col-sm-12 col-md-5 block" id="diagramm_td" style="display: none">
				<div class="chart" id="buchungen_heute"></div>
			</div>
		</div>
		<br>
		<?php ////////////////////// ?>
		<div class="row">
			<div class="col-sm-12 col-md-4" id="show_con_btn">
				<h4>Kontingent Stand heute</h4>
				<button onclick="load_con(this)" class="btn btn-primary">Anzeigen</button>
				<hr>
			</div>
			<div class="col-sm-12 col-md-4 block" id="table_con" style="display: none"></div>	
			
			<script>
			function load_con(e) {
				var helperUrl = '/wp-content/plugins/itweb-booking/templates/dashboard/parts/contingent.php';
				const con = document.getElementById("table_con");
				const show_con_btn = document.getElementById("show_con_btn");
				const show_con_diag = document.getElementById("diagramm_con");
				e.innerHTML = "Lade <i class='fas fa-spinner fa-pulse'></i>";
				$ = jQuery;
				$.ajax({
					type:"POST",
					url:helperUrl,
					traditional:true,
					dataType: "json",
					success:function(data){
						var content = data.content;
						var diagramm = data.diagramm;
						con.innerHTML = content;
						set_con_diagram(diagramm);
						show_con_btn.style = "display: none";
						con.style = "display: block";
						show_con_diag.style = "display: block";
					},
					error:function(data){
					}
				});
			}
			function set_con_diagram(diagramm) {
				google.charts.setOnLoadCallback(drawVisualization_kontingent);
				function drawVisualization_kontingent() {
					// Some raw data (not necessarily accurate)
					var data = google.visualization.arrayToDataTable([
						['Betreiber', 'Gebucht', 'Frei'],
						[diagramm[0][0], parseInt(diagramm[0][1]), parseInt(diagramm[0][2])],
						[diagramm[1][0], parseInt(diagramm[1][1]), parseInt(diagramm[1][2])],
						[diagramm[2][0], parseInt(diagramm[2][1]), parseInt(diagramm[2][2])],
						[diagramm[3][0], parseInt(diagramm[3][1]), parseInt(diagramm[3][2])],
						[diagramm[4][0], parseInt(diagramm[4][1]), parseInt(diagramm[4][2])],
						[diagramm[5][0], parseInt(diagramm[5][1]), parseInt(diagramm[5][2])],
						[diagramm[6][0], parseInt(diagramm[6][1]), parseInt(diagramm[6][2])]
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
			}
			</script>
			<div class="col-sm-12 col-md-6 block" id="diagramm_con" style="display: none">				
				<div class="chart" id="kontingent"></div>
			</div>
		</div>
		<br>
		<?php ////////////////////// ?>
		<?php
		$c_month = date('n');
		$c_year = date('Y');
		$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $c_month, $c_year);

		$dateto = date('Y-m-d', strtotime(date($c_year."-".$c_month."-".$daysInMonth)));
		$datefrom = date('Y-m-d', strtotime(date($c_year."-".$c_month."-01")));
		?>
		<div class="row">					
			<div class="col-sm-12 col-md-3" id="show_bok_btn">
				<h4>Buchungen Monat <?php echo $months[$c_month] ?></h4>
				<button onclick="load_bok(this)" class="btn btn-primary">Anzeigen</button>
				<hr>
			</div>
			<div class="col-sm-12 col-md-4 block" id="table_bok" style="display: none"></div>	
			<script>
			function load_bok(e) {
				var helperUrl = '/wp-content/plugins/itweb-booking/templates/dashboard/parts/buchungen.php';
				const bok = document.getElementById("table_bok");
				const show_bok_btn = document.getElementById("show_bok_btn");
				const show_bok_diag = document.getElementById("diagramm_bok");
				e.innerHTML = "Lade <i class='fas fa-spinner fa-pulse'></i>";
				$ = jQuery;
				$.ajax({
					type:"POST",
					url:helperUrl,
					traditional:true,
					dataType: "json",
					success:function(data){
						var content = data.content;
						var diagramm = data.diagramm;
						bok.innerHTML = content;
						set_bok_diagram(diagramm);
						show_bok_btn.style = "display: none";
						bok.style = "display: block";
						show_bok_diag.style = "display: block";
					},
					error:function(data){						
					}
				});
			}
			function set_bok_diagram(diagramm) {
				google.charts.setOnLoadCallback(drawVisualization_umsatz_monat);
				function drawVisualization_umsatz_monat() {
					// Some raw data (not necessarily accurate)
					var data = google.visualization.arrayToDataTable([
						['Betreiber', 'Umsatz', { role: 'annotation'}],
						['', 0, ''],
					]);
					for (var i = 0; i < diagramm.length; i++) {
						data.addRow(diagramm[i]);
					}
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
			}
			</script>
			<div class="col-sm-12 col-md-5 block" id="diagramm_bok" style="display: none">
				<div class="chart" id="umsatz_monat"></div>
			</div>
		</div>
		<br>
		<?php ////////////////////// ?>
		<div class="row">					
			<div class="col-sm-12 col-md-3" id="show_anr_btn">
				<h4>Anreise Monat <?php echo $months[$c_month] ?></h4>
				<button onclick="load_anr(this)" class="btn btn-primary">Anzeigen</i></button>
				<hr>
			</div>
			<div class="col-sm-12 col-md-4 block" id="table_anr" style="display: none"></div>
			<script>
			function load_anr(e) {
				var helperUrl = '/wp-content/plugins/itweb-booking/templates/dashboard/parts/anreise.php';
				const anr = document.getElementById("table_anr");
				const show_anr_btn = document.getElementById("show_anr_btn");
				const show_anr_diag = document.getElementById("diagramm_anr");
				e.innerHTML = "Lade <i class='fas fa-spinner fa-pulse'></i>";
				$ = jQuery;
				$.ajax({
					type:"POST",
					url:helperUrl,
					traditional:true,
					dataType: "json",
					success:function(data){
						var content = data.content;
						var diagramm = data.diagramm;
						anr.innerHTML = content;
						set_anr_diagram(diagramm);
						show_anr_btn.style = "display: none";
						anr.style = "display: block";
						show_anr_diag.style = "display: block";
					},
					error:function(data){						
					}
				});
			}
			function set_anr_diagram(diagramm) {
				google.charts.setOnLoadCallback(drawVisualization_ist_umsatz_monat);
				function drawVisualization_ist_umsatz_monat() {
					// Some raw data (not necessarily accurate)
					var data = google.visualization.arrayToDataTable([
						['Betreiber', 'Umsatz', { role: 'annotation'}],
						['', 0, ''],						
					]);
					for (var i = 0; i < diagramm.length; i++) {
							data.addRow(diagramm[i]);
						}
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
			}
			</script>
			<div class="col-sm-12 col-md-5 block" id="diagramm_anr" style="display: none">
				<div class="chart" id="ist_umsatz_monat"></div>
			</div>
		</div>
		<br>
		<?php ////////////////////// ?>
		<div class="row">
			<div class="col-sm-12 col-md-4" id="show_per_btn">
				<h4>Buchungen mit Anzahl Personen Anreise Monat <?php echo $months[$c_month] ?></h4>
				<button onclick="load_per(this)" class="btn btn-primary">Anzeigen</i></button>
				<hr>
			</div>
			<div class="col-sm-12 col-md-4 block" id="table_per" style="display: none"></div>
			
			<script>
			function load_per(e) {
				var helperUrl = '/wp-content/plugins/itweb-booking/templates/dashboard/parts/person.php';
				const per = document.getElementById("table_per");
				const show_per_btn = document.getElementById("show_per_btn");
				const show_per_diag = document.getElementById("diagramm_per");
				e.innerHTML = "Lade <i class='fas fa-spinner fa-pulse'></i>";
				$ = jQuery;
				$.ajax({
					type:"POST",
					url:helperUrl,
					traditional:true,
					dataType: "json",
					success:function(data){
						var content = data.content;
						var diagramm = data.diagramm;
						per.innerHTML = content;
						set_per_diagram(diagramm);
						show_per_btn.style = "display: none";
						per.style = "display: block";
						show_per_diag.style = "display: block";
					},
					error:function(data){						
					}
				});
			}
			function set_per_diagram(diagramm) {
				google.charts.setOnLoadCallback(drawVisualization_personen);
				function drawVisualization_personen() {
					// Some raw data (not necessarily accurate)
					var data = google.visualization.arrayToDataTable([
						['Personen', 'Anzahl', { role: 'annotation'}],												
						[diagramm[0][0], parseInt(diagramm[0][1]), diagramm[0][2]],
						[diagramm[1][0], parseInt(diagramm[1][1]), diagramm[1][2]],
						[diagramm[2][0], parseInt(diagramm[2][1]), diagramm[2][2]],
						[diagramm[3][0], parseInt(diagramm[3][1]), diagramm[3][2]],
						[diagramm[4][0], parseInt(diagramm[4][1]), diagramm[4][2]],
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
			}
			</script>
		
			<div class="col-sm-12 col-md-5 block" id="diagramm_per" style="display: none">
				<div class="chart" id="personen"></div>
			</div>
		</div>
		<br>
		<?php ////////////////////// ?>
		<div class="row" style="display: none">
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
