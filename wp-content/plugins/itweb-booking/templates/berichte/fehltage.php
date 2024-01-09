<?php
$db = Database::getInstance();
$mitarbeiter = $db->getActivUser_einsatzplan();
$grund_array = array('U' => 'Urlaub', 'SU' => 'Sonderurlaub', 'F' => 'Freistellung', 'BF' => 'Bezahlte Freistellung', 'K' => 'Krank 1', 'KK' => 'Krank 2', 'UF' => 'Überstundenfrei',
				'BFS' => 'Berufsschule', 'FB' => 'Fortbildung', 'GR' => 'Geschäftsreise', 'X' => 'Sperren', 'DEL' => 'Löschen');
$months = array('1' => 'Januar', '2' => 'Fabruar', '3' => 'März', '4' => 'April', '5' => 'Mai', '6' => 'Juni',
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

foreach($mitarbeiter as $ma){
	$wp_user = get_user_by('id', $ma->user_id);
	for($i = 1; $i <= $daysInMonth; $i++){
		$day = $i < 10 ? "0" . $i : $i;
		$date = $c_year . "-" . $c_month . "-" . $day;
		$wochentag = str_replace(".", "", strtolower($wochentage[date("w", strtotime($date))]));
		$kw = date("W", strtotime($date));
		$einsatzplan = $db->getEinsatzplanByUserIDandDay($kw, $c_year, $wochentag, $ma->user_id);
		
		foreach($einsatzplan as $key => $val){
			if($val != null && !str_contains($val, '-')){
				$fehltage[$wp_user->display_name][$val] += 1;
				$sum_fehltage[$val] += 1;
			}
		}
	}
}

for($i = 1; $i <= $daysInMonth; $i++){
	$day = $i < 10 ? "0" . $i : $i;
	$date = $c_year . "-" . $c_month . "-" . $day;
	$wochentag = str_replace(".", "", strtolower($wochentage[date("w", strtotime($date))]));
	$kw = date("W", strtotime($date));
	
	foreach($mitarbeiter as $ma){
		$wp_user = get_user_by('id', $ma->user_id);
		$einsatzplan = $db->getEinsatzplanByUserIDandDay($kw, $c_year, $wochentag, $ma->user_id);
		foreach($einsatzplan as $key => $val){
			if($val != null && !str_contains($val, '-')){
				$fehltage_date[$date][$val] += 1;
				$sum_fehltage_date[$val] += 1;
			}
		}
	}
}

for($m = 1; $m <= 12; $m++){
	$y_month = $m;
	$y_daysInMonth = cal_days_in_month(CAL_GREGORIAN, $y_month, $c_year);
	
	foreach($mitarbeiter as $ma){
		$wp_user = get_user_by('id', $ma->user_id);
		for($i = 1; $i <= $daysInMonth; $i++){
			$day = $i < 10 ? "0" . $i : $i;
			$date = $c_year . "-" . $y_month . "-" . $day;
			$wochentag = str_replace(".", "", strtolower($wochentage[date("w", strtotime($date))]));
			$kw = date("W", strtotime($date));
			$einsatzplan = $db->getEinsatzplanByUserIDandDay($kw, $c_year, $wochentag, $ma->user_id);
			
			foreach($einsatzplan as $key => $val){
				if($val != null && !str_contains($val, '-')){
					$fehltage_year[$wp_user->display_name][$val] += 1;
					$sum_fehltage_year[$val] += 1;
				}
			}
		}
	}
}
//echo "<pre>"; print_r($fehltage_date); echo "</pre>";

?>
<style>
td, th {
	border-left: 1px solid black;
}
thead th {
  position: -webkit-sticky; /* for Safari */
  position: sticky;
  top: 0;
}

.U{
	background: #ccffcc;
}
.SU{
	background: #c3fc6a;
}
.BFS{
	background: #ccffff;
}
.F{
	background: #ccccff;
}
.BF{
	background: #8c8cff;
}
.K, .KK{
	background: #ffcccc;
}
.UF{
	background: #ffa1dc;
}
.FB{
	background: #a1fff9;
}
.GR{
	background: #f6fcbb;
}
.table_div {
  width: 100%;
  max-height: 800px;
  overflow: scroll;
  position: relative;
}
.table_div_filter{
	width: 33%;
}
</style>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-title itweb_adminpage_head">
		<h3>Fehl- und Krankheitstage</h3>
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
							<select name="grund" class="form-item form-control">
								<option value="">Kürzel</option>
								<?php foreach($grund_array as $key => $val): ?>						
									<?php if($key == 'X' || $key == 'DEL') continue; ?>
									<option value="<?php echo $key ?>" <?php echo $_GET['grund'] == $key ? "selected" : "" ?>><?php echo $val ?></option>						
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-sm-12 col-md-1">
							<button class="btn btn-primary d-block w-100" type="submit">Filter</button>
						</div>
						<div class="col-sm-12 col-md-2">                    
							<a href="<?php echo '/wp-admin/admin.php?page=fehltage' ?>" class="btn btn-secondary d-block w-100" >Zurücksetzen</a>
						</div>
					</div>
				</div>
			</div>
		</form>
		<div class="row">
			<div class="col-sm-12 col-md-12">
				<details open>
					<summary class="itweb_add_head-summary">Nach Mitarbeiter</summary>
					<br><br>
					<div class="table_div">											
						<table class="table table-sm <?php echo isset($_GET['grund']) ? "table_div_filter" : "" ?>">
							<thead>
								<tr>
									<th style="background: #ffffff">Mitarbeiter</th>
									<?php foreach($grund_array as $key => $val): ?>						
										<?php if($key == 'X' || $key == 'DEL') continue; ?>
										<?php if(isset($_GET['grund']) && $_GET['grund'] != $key) continue; ?>
										<th class="<?php echo $key ?>"><?php echo $val; ?></th>				
									<?php endforeach; ?>							
							</thead>
							<tbody>
								<?php foreach($mitarbeiter as $ma): ?>
									<?php $wp_user = get_user_by('id', $ma->user_id); ?>
									<tr>
										<td><?php echo $wp_user->display_name; ?></td>
										<?php foreach($grund_array as $key => $val): ?>						
											<?php if($key == 'X' || $key == 'DEL') continue; ?>
											<?php if(isset($_GET['grund']) && $_GET['grund'] != $key) continue; ?>
											<td class="<?php echo $fehltage[$wp_user->display_name][$key] != null ? $key : "" ?>"><center><?php echo $fehltage[$wp_user->display_name][$key] != null ? $fehltage[$wp_user->display_name][$key] : "0"; ?><center></td>				
										<?php endforeach; ?>	
									</tr>
								<?php endforeach; ?>
								<tr>
									<td><strong>Summe</strong></td>
									<?php foreach($grund_array as $key => $val): ?>						
										<?php if($key == 'X' || $key == 'DEL') continue; ?>
										<?php if(isset($_GET['grund']) && $_GET['grund'] != $key) continue; ?>
										<td class="<?php echo $key ?>"><center><strong><?php echo $sum_fehltage[$key] != null ? $sum_fehltage[$key] : "0"; ?></strong></center></td>				
									<?php endforeach; ?>	
								</tr>
							</tbody>
						</table>				
					</div>
				</details>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12 col-md-12">
				<details>
					<summary class="itweb_add_head-summary">Nach Datum</summary>
					<br><br>
					<div class="table_div">											
						<table class="table table-sm <?php echo isset($_GET['grund']) ? "table_div_filter" : "" ?>">
							<thead>
								<tr>
									<th style="background: #ffffff">Datum</th>
									<?php foreach($grund_array as $key => $val): ?>						
										<?php if($key == 'X' || $key == 'DEL') continue; ?>
										<?php if(isset($_GET['grund']) && $_GET['grund'] != $key) continue; ?>
										<th class="<?php echo $key ?>"><?php echo $val; ?></th>				
									<?php endforeach; ?>				
							</thead>
							<tbody>
								<?php for($i = 1; $i <= $daysInMonth; $i++): ?>
								<?php $day = $i < 10 ? "0" . $i : $i; ?>
								<?php $date = $c_year . "-" . $c_month . "-" . $day; ?>
									<tr>										
										<td><?php echo $day . "." . $c_month . "." . $c_year; ?></th>
										<?php foreach($grund_array as $key => $val): ?>						
											<?php if($key == 'X' || $key == 'DEL') continue; ?>
											<?php if(isset($_GET['grund']) && $_GET['grund'] != $key) continue; ?>
											<td class="<?php echo $fehltage_date[$date][$key] != null ? $key : "" ?>"><center><?php echo $fehltage_date[$date][$key] != null ? $fehltage_date[$date][$key] : "0"; ?></center></td>				
										<?php endforeach; ?>	
									</tr>
								<?php endfor; ?>	
								<tr>
									<td><strong>Summe</strong></td>
									<?php foreach($grund_array as $key => $val): ?>						
										<?php if($key == 'X' || $key == 'DEL') continue; ?>
										<?php if(isset($_GET['grund']) && $_GET['grund'] != $key) continue; ?>
										<td class="<?php echo $key ?>"><center><strong><?php echo $sum_fehltage[$key] != null ? $sum_fehltage[$key] : "0"; ?></strong></center></td>				
									<?php endforeach; ?>										
								</tr>
							</tbody>
						</table>				
					</div>
				</details>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12 col-md-12">
				<details>
					<summary class="itweb_add_head-summary">Im Jahr <?php echo $c_year ?></summary>
					<br><br>
					<div class="table_div">											
						<table class="table table-sm <?php echo isset($_GET['grund']) ? "table_div_filter" : "" ?>">
							<thead>
								<tr>
									<th style="background: #ffffff">Mitarbeiter</th>
									<?php foreach($grund_array as $key => $val): ?>						
										<?php if($key == 'X' || $key == 'DEL') continue; ?>
										<?php if(isset($_GET['grund']) && $_GET['grund'] != $key) continue; ?>
										<th class="<?php echo $key ?>"><?php echo $val; ?></th>				
									<?php endforeach; ?>							
							</thead>
							<tbody>
								<?php foreach($mitarbeiter as $ma): ?>
									<?php $wp_user = get_user_by('id', $ma->user_id); ?>
									<tr>
										<td><?php echo $wp_user->display_name; ?></td>
										<?php foreach($grund_array as $key => $val): ?>						
											<?php if($key == 'X' || $key == 'DEL') continue; ?>
											<?php if(isset($_GET['grund']) && $_GET['grund'] != $key) continue; ?>
											<td class="<?php echo $fehltage_year[$wp_user->display_name][$key] != null ? $key : "" ?>"><center><?php echo $fehltage_year[$wp_user->display_name][$key] != null ? $fehltage_year[$wp_user->display_name][$key] : "0"; ?><center></td>				
										<?php endforeach; ?>	
									</tr>
								<?php endforeach; ?>
								<tr>
									<td><strong>Summe</strong></td>
									<?php foreach($grund_array as $key => $val): ?>						
										<?php if($key == 'X' || $key == 'DEL') continue; ?>
										<?php if(isset($_GET['grund']) && $_GET['grund'] != $key) continue; ?>
										<td class="<?php echo $key ?>"><center><strong><?php echo $sum_fehltage_year[$key] != null ? $sum_fehltage_year[$key] : "0"; ?></strong></center></td>				
									<?php endforeach; ?>	
								</tr>
							</tbody>
						</table>				
					</div>
				</details>
			</div>
		</div>
	</div>
</div>