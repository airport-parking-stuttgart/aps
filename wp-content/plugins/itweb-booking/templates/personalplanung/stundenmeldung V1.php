<?php
$db = Database::getInstance();

$current_user = wp_get_current_user();
$mitarbeiter = $db->getActivUser_einsatzplan();

$wochentage = array("So.", "Mo.", "Di.", "Mi.", "Do.", "Fr.", "Sa.");
$months = array('1' => 'Januar', '2' => 'Fabruar', '3' => 'März', '4' => 'April', '5' => 'Mai', '6' => 'Juni',
				'7' => 'Juli', '8' => 'August', '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Dezember');
if (isset($_GET['month']) && $_GET['month'] < 10)
    $zero = '0';
else
    $zero = '';

if (isset($_GET["month"])){
	$c_month = $_GET["month"];
	$month = $_GET["month"];
}
else{
	$c_month = date('n');
	$month = date('m');
}
    

if (isset($_GET["year"]))
    $c_year = $_GET["year"];
else
    $c_year = date('Y');
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $c_month, $c_year);

if($c_month == 1){
	$prev_year = $c_year - 1;
	$prev_month = 12;	
	$next_year = $c_year;
	$next_month = $c_month + 1;
}
elseif($c_month == 12){
	$prev_year = $c_year;
	$prev_month = $c_month + 1;
	$next_year = $c_year + 1;
	$next_month = 1;
}
else{
	$prev_year = $c_year;
	$prev_month = $c_month - 1;
	$next_year = $c_year;
	$next_month = $c_month + 1;
}


$firstDayOfMonth = date($c_year."-".$month."-01");
$lastDayOfMonth = date($c_year."-".$month."-t");
$firstWeek = date('W', strtotime($firstDayOfMonth));
$lastWeek = date('W', strtotime($lastDayOfMonth));

if ($c_month == 1 && $firstWeek > $lastWeek) {
	$firstWeek = 1;
}

function getKWs($c_year, $firstDayOfMonth, $lastDayOfMonth){
	//$startDate = new DateTime("$year-01-01");
	//$endDate = new DateTime("$year-12-31");
	$year = $c_year;
	$startDate = new DateTime($firstDayOfMonth);
	$endDate = new DateTime($lastDayOfMonth);

	$currentDate = clone $startDate;

	$weeks = array();

	while ($currentDate <= $endDate) {
		$week = $currentDate->format("W");
		$month = $currentDate->format("n");

		if ($currentDate->format("Y") != $year) {
			break;
		}

		if (!isset($weeks[$month])) {
			$weeks[$month] = array();
		}

		if (!isset($weeks[$month][$week])) {
			$weeks[$month][$week] = 0;
		}

		$weeks[$month][$week] += 1;

		$currentDate->modify("+1 day");
	}

	foreach ($weeks as $month => $weekData) {
		//echo $month . ":<br>";
		foreach ($weekData as $week => $dayCount) {
			$wochen[$week][$month]['KW'] = $week;
			$wochen[$week][$month]['Tage'] = $dayCount;
			//echo "KW " . $week . ": " . $dayCount . " Tage<br>";
		}
	}
	return $wochen;
}

$weeks = getKWs($c_year, $firstDayOfMonth, $lastDayOfMonth);

foreach($mitarbeiter as $ma){
	foreach ($weeks as $week => $val){
		$data =  $db->getEinsatzplanByUserID($week, $c_year, $ma->user_id);
		$times = explode("-", $data->mo);
		if($times[1] != null && $times[0] != null){
			$diff_times[$week][$ma->user_id] += number_format((int)$times[1] - (int)$times[0], 1, ".", ".");
			$diff_times[$week][$ma->user_id] += number_format($data->mo_pause, 1, ".", ".");
			
			if((get_user_meta( $ma->user_id, 'bonusab', true ) != 0 && get_user_meta( $ma->user_id, 'bonusbis', true ) != 0) && get_user_meta( $ma->user_id, 'type', true ) != 'GfB'){
				if((get_user_meta( $ma->user_id, 'bonusbis', true )*1) >= $times[0])
					$bonus[$week][$ma->user_id] += get_user_meta( $ma->user_id, 'bonusbis', true ) - $times[0];
				if(($times[1] >= get_user_meta( $ma->user_id, 'bonusab', true )*1))	
					$bonus[$week][$ma->user_id] += $times[1] - get_user_meta( $ma->user_id, 'bonusab', true );
			}	
		}
		$times = explode("-", $data->di);
		if($times[1] != null && $times[0] != null){
			$diff_times[$week][$ma->user_id] += number_format((int)$times[1] - (int)$times[0], 1, ".", ".");
			$diff_times[$week][$ma->user_id] += number_format($data->di_pause, 1, ".", ".");
			
			if((get_user_meta( $ma->user_id, 'bonusab', true ) != 0 && get_user_meta( $ma->user_id, 'bonusbis', true ) != 0) && get_user_meta( $ma->user_id, 'type', true ) != 'GfB'){
				if((get_user_meta( $ma->user_id, 'bonusbis', true )*1) >= $times[0])
					$bonus[$week][$ma->user_id] += get_user_meta( $ma->user_id, 'bonusbis', true ) - $times[0];
				if(($times[1] >= get_user_meta( $ma->user_id, 'bonusab', true )*1))	
					$bonus[$week][$ma->user_id] += $times[1] - get_user_meta( $ma->user_id, 'bonusab', true );
			}	
		}
		$times = explode("-", $data->mi);
		if($times[1] != null && $times[0] != null){
			$diff_times[$week][$ma->user_id] += number_format((int)$times[1] - (int)$times[0], 1, ".", ".");
			$diff_times[$week][$ma->user_id] += number_format($data->mi_pause, 1, ".", ".");
			
			if((get_user_meta( $ma->user_id, 'bonusab', true ) != 0 && get_user_meta( $ma->user_id, 'bonusbis', true ) != 0) && get_user_meta( $ma->user_id, 'type', true ) != 'GfB'){
				if((get_user_meta( $ma->user_id, 'bonusbis', true )*1) >= $times[0])
					$bonus[$week][$ma->user_id] += get_user_meta( $ma->user_id, 'bonusbis', true ) - $times[0];
				if(($times[1] >= get_user_meta( $ma->user_id, 'bonusab', true )*1))	
					$bonus[$week][$ma->user_id] += $times[1] - get_user_meta( $ma->user_id, 'bonusab', true );
			}	
		}
		$times = explode("-", $data->do);
		if($times[1] != null && $times[0] != null){
			$diff_times[$week][$ma->user_id] += number_format((int)$times[1] - (int)$times[0], 1, ".", ".");
			$diff_times[$week][$ma->user_id] += number_format($data->do_pause, 1, ".", ".");
			
			if((get_user_meta( $ma->user_id, 'bonusab', true ) != 0 && get_user_meta( $ma->user_id, 'bonusbis', true ) != 0) && get_user_meta( $ma->user_id, 'type', true ) != 'GfB'){
				if((get_user_meta( $ma->user_id, 'bonusbis', true )*1) >= $times[0])
					$bonus[$week][$ma->user_id] += get_user_meta( $ma->user_id, 'bonusbis', true ) - $times[0];
				if(($times[1] >= get_user_meta( $ma->user_id, 'bonusab', true )*1))	
					$bonus[$week][$ma->user_id] += $times[1] - get_user_meta( $ma->user_id, 'bonusab', true );
			}	
		}
		$times = explode("-", $data->fr);
		if($times[1] != null && $times[0] != null){
			$diff_times[$week][$ma->user_id] += number_format((int)$times[1] - (int)$times[0], 1, ".", ".");
			$diff_times[$week][$ma->user_id] += number_format($data->fr_pause, 1, ".", ".");
			
			if((get_user_meta( $ma->user_id, 'bonusab', true ) != 0 && get_user_meta( $ma->user_id, 'bonusbis', true ) != 0) && get_user_meta( $ma->user_id, 'type', true ) != 'GfB'){
				if((get_user_meta( $ma->user_id, 'bonusbis', true )*1) >= $times[0])
					$bonus[$week][$ma->user_id] += get_user_meta( $ma->user_id, 'bonusbis', true ) - $times[0];
				if(($times[1] >= get_user_meta( $ma->user_id, 'bonusab', true )*1))	
					$bonus[$week][$ma->user_id] += $times[1] - get_user_meta( $ma->user_id, 'bonusab', true );
			}	
		}
		$times = explode("-", $data->sa);
		if($times[1] != null && $times[0] != null){
			$diff_times[$week][$ma->user_id] += number_format((int)$times[1] - (int)$times[0], 1, ".", ".");
			$diff_times[$week][$ma->user_id] += number_format($data->sa_pause, 1, ".", ".");
			
			if((get_user_meta( $ma->user_id, 'bonusab', true ) != 0 && get_user_meta( $ma->user_id, 'bonusbis', true ) != 0) && get_user_meta( $ma->user_id, 'type', true ) != 'GfB'){
				if((get_user_meta( $ma->user_id, 'bonusbis', true )*1) >= $times[0])
					$bonus[$week][$ma->user_id] += get_user_meta( $ma->user_id, 'bonusbis', true ) - $times[0];
				if(($times[1] >= get_user_meta( $ma->user_id, 'bonusab', true )*1))	
					$bonus[$week][$ma->user_id] += $times[1] - get_user_meta( $ma->user_id, 'bonusab', true ); 
			}	
		}
		$times = explode("-", $data->so);
		if($times[1] != null && $times[0] != null){
			$diff_times[$week][$ma->user_id] += number_format((int)$times[1] - (int)$times[0], 1, ".", ".");
			$diff_times[$week][$ma->user_id] += number_format($data->so_pause, 1, ".", ".");
			
			if((get_user_meta( $ma->user_id, 'bonusab', true ) != 0 && get_user_meta( $ma->user_id, 'bonusbis', true ) != 0) && get_user_meta( $ma->user_id, 'type', true ) != 'GfB'){
				if((get_user_meta( $ma->user_id, 'bonusbis', true )*1) >= $times[0])
					$bonus[$week][$ma->user_id] += get_user_meta( $ma->user_id, 'bonusbis', true ) - $times[0];
				if(($times[1] >= get_user_meta( $ma->user_id, 'bonusab', true )*1))	
					$bonus[$week][$ma->user_id] += $times[1] - get_user_meta( $ma->user_id, 'bonusab', true );
			}	
		}
		
		$diff_times[$week][$ma->user_id] -= get_user_meta( $ma->user_id, 'std_w', true);
		$diff_times[$week][$ma->user_id] = number_format($diff_times[$week][$ma->user_id] / 7 * $val[$c_month]['Tage'], 1, ".", ".");
	}
}


$sql_data = $db->getZeitkonto($c_year, $c_month);
foreach($sql_data as $val){
	$uvm[$val->user_id] = $val->uvm;
}

foreach($diff_times as $w_ma){
	foreach($mitarbeiter as $ma){
		$ist_ma[$ma->user_id] += $w_ma[$ma->user_id];
	}
}
foreach($bonus as $b_ma){
	foreach($mitarbeiter as $ma){
		$bonus_ma[$ma->user_id] += $b_ma[$ma->user_id];
	}
}

if($_POST['btn'] == 1){
	$db->deleteStundenmeldung($c_year, $c_month);
	foreach($_POST as $key => $val){
		if($key == 'btn' || $key == 'year' || $key == 'month')
			continue;
		$kd = explode("_", $key);
		$col = $kd[0];
		$user_id = $kd[1];
		$data[$user_id][$col] = $val;
	}
	foreach($data as $key => $val){
		$db->addStundenmeldung($key, $val['auszahlung'], $val['sonstiges'], $c_year, $c_month);
	}
	
}

$sql_data = $db->getStundenmeldung($c_year, $c_month);

foreach($sql_data as $val){
	$data_sm[$val->user_id]['auszahlung'] = $val->auszahlung;
	$data_sm[$val->user_id]['sonstiges'] = $val->sonstiges;
}

//echo "<pre>"; print_r($mitarbeiter); echo "</pre>";
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>  
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>  
<style>
.VZ{background: #00ace6}
.TZ{background: #cccccc}
.GfB{background: #ffff99}
th{white-space: nowrap;}
table {
  display: block;
  overflow-x: auto;
  white-space: nowrap;
}
.table-container {
	overflow-x: auto;
}

table {
	width: 100%;
}

th, td{
	border: 1px solid black !important;
}

th{
	background: #c4dbff;
}
</style>

<div class="page container-fluid <?php echo $_GET['page'] ?>">

    <div class="page-title itweb_adminpage_head">
		<h3>Stundenmeldung</h3>
	</div>
	<div class="page-body">
		<div class="row ui-lotdata-block ui-lotdata-block-next">
			<h5 class="ui-lotdata-title">Monat anzeigen</h5>
			<div class="col-sm-12 col-md-12 ui-lotdata">						
				<details id='1'>
					<summary>Datum | <a href="<?php echo '/wp-admin/admin.php?page=stundenmeldung&month='.$prev_month.'&year='.$prev_year ?>" class="btn btn-primary"><</a> 
					<span class="btn btn-primary"><?php echo $months[$c_month] . " " . $c_year ?> </span>
					<a href="<?php echo '/wp-admin/admin.php?page=stundenmeldung&month='.$next_month.'&year='.$next_year ?>" class="btn btn-primary">></a>
					</summary>
					<br>
					<div class="row">
						<div class="col-sm-12 col-md-12">
							<select name="year" id="year" onchange="change_year(this)">
								<?php for ($i = 2021; $i <= date('Y') + 1; $i++) : ?>
									<option value="<?php echo $i ?>" <?php echo $i == $c_year ? ' selected' : '' ?>>
										<?php echo $i ?>
									</option>
								<?php endfor; ?>
							</select>
							<a href="<?php echo '/wp-admin/admin.php?page=stundenmeldung&month=1&year='.$c_year ?>" class="btn <?php echo $c_month == 1 ? 'btn-primary' : 'btn-secondary' ?>" >Januar</a>
							<a href="<?php echo '/wp-admin/admin.php?page=stundenmeldung&month=2&year='.$c_year ?>" class="btn <?php echo $c_month == 2 ? 'btn-primary' : 'btn-secondary' ?>" >Februar</a>
							<a href="<?php echo '/wp-admin/admin.php?page=stundenmeldung&month=3&year='.$c_year ?>" class="btn <?php echo $c_month == 3 ? 'btn-primary' : 'btn-secondary' ?>" >März</a>
							<a href="<?php echo '/wp-admin/admin.php?page=stundenmeldung&month=4&year='.$c_year ?>" class="btn <?php echo $c_month == 4 ? 'btn-primary' : 'btn-secondary' ?>" >April</a>
							<a href="<?php echo '/wp-admin/admin.php?page=stundenmeldung&month=5&year='.$c_year ?>" class="btn <?php echo $c_month == 5 ? 'btn-primary' : 'btn-secondary' ?>" >Mai</a>
							<a href="<?php echo '/wp-admin/admin.php?page=stundenmeldung&month=6&year='.$c_year ?>" class="btn <?php echo $c_month == 6 ? 'btn-primary' : 'btn-secondary' ?>" >Juni</a>
							<a href="<?php echo '/wp-admin/admin.php?page=stundenmeldung&month=7&year='.$c_year ?>" class="btn <?php echo $c_month == 7 ? 'btn-primary' : 'btn-secondary' ?>" >Juli</a>
							<a href="<?php echo '/wp-admin/admin.php?page=stundenmeldung&month=8&year='.$c_year ?>" class="btn <?php echo $c_month == 8 ? 'btn-primary' : 'btn-secondary' ?>" >August</a>
							<a href="<?php echo '/wp-admin/admin.php?page=stundenmeldung&month=9&year='.$c_year ?>" class="btn <?php echo $c_month == 9 ? 'btn-primary' : 'btn-secondary' ?>" >Sepember</a>
							<a href="<?php echo '/wp-admin/admin.php?page=stundenmeldung&month=10&year='.$c_year ?>" class="btn <?php echo $c_month == 10 ? 'btn-primary' : 'btn-secondary' ?>" >Oktober</a>
							<a href="<?php echo '/wp-admin/admin.php?page=stundenmeldung&month=11&year='.$c_year ?>" class="btn <?php echo $c_month == 11 ? 'btn-primary' : 'btn-secondary' ?>" >November</a>
							<a href="<?php echo '/wp-admin/admin.php?page=stundenmeldung&month=12&year='.$c_year ?>" class="btn <?php echo $c_month == 12 ? 'btn-primary' : 'btn-secondary' ?>" >Dezember</a>
						</div>
					</div>
				</details>
				<br>				
				<div class="row">						
					<div class="col-sm-12 col-md-3">						
						<form action="<?= get_site_url() . '/wp-content/plugins/itweb-booking/templates/personalplanung/stundenmeldung-pdf.php'; ?>" method="post">
							<button type="submit" id="btnExport" value="Export to PDF" class="btn btn-success">Stundenmeldung exportieren</button>
						</form>
					</div>
					<div class="col-sm-12 col-md-2">						
						<form action="<?= get_site_url() . '/wp-content/plugins/itweb-booking/templates/personalplanung/stundenmeldung-pdf.php'; ?>" method="post">	
							<input type="hidden" name="mail" value="1">
							<input type="hidden" name="date" value="<?php echo $months[$c_month] . " " . $c_year ?>">
							<button type="submit" id="btnExport" value="Export to PDF" class="btn btn-success">Per Mail versenden</button>
						</form>
					</div>						
				</div>				
			</div>
		</div>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . "?page=stundenmeldung".$query; ?>">
			<div class="row">
			<?php 
			$query = "";
			if(isset($_GET['month']))
				$query .= "&month=".$_GET['month']."&year=".$_GET['year'];
			?>
			
				<div class="col-sm-12 col-md-12">
					<button class="btn btn-primary" type="submit" name="btn" value="1">Speichern</button><br><br>
					<input type="hidden" name="year" value="<?php echo $c_year ?>">
					<input type="hidden" name="month" value="<?php echo $c_month ?>">					
					<div class="table-container">
					<table class="table">
						<thead>
							<tr>
								<th>BG Verkehr</th>
								<th>Name</th>
								<th>Status</th>
								<th>Std. Lohn</th>
								<th>Festgehalt</th>
								<th style='background-color: #ff9933; width: 20px;'></th>
								<th>Übertrag VM</th>
								<th>Ist Monat</th>
								<th>Gesamt Std.</th>
								<th style='background-color: #ff9933; width: 20px;'></th>
								<th>Soll Ausz.</th>
								<th>Rest</th>
								<th>Überstd. Ausz.</th>
								<th>Gesamt Aus. MA</th>
								<!--<th>Nachtschicht</th>-->
								<th>Übertrag NM</th>
								<th>Kommentar</th>
							<tr>
						</thead>
						<tbody class="row_position_table1">
							<?php foreach($mitarbeiter as $ma): ?>
							<?php $wp_user = get_user_by('id', $ma->user_id); ?>
							<?php $user_data = get_userdata( $ma->user_id ); ?>
							<?php 							
								if(in_array('fahrer', $user_data->roles))
									$rolle = "Fahrer";
								elseif(in_array('koordinator', $user_data->roles))
									$rolle = "Fahrer";
								elseif(in_array('admin2', $user_data->roles))
									$rolle = "Büro";
								if($ma->user_id == 7)
									$rolle = "Geschäftsführer";
							?>
							<tr id="<?php echo $ma->user_id ?>">
								<td><?php echo $rolle ?></td>
								<td><?php echo $wp_user->display_name; ?></td>
								<td class="<?php echo get_user_meta( $ma->user_id, 'type', true ); ?>"><?php echo get_user_meta( $ma->user_id, 'type', true ); ?></td>
								<?php 
									if(get_user_meta( $ma->user_id, 'std_lohn', true ) != null) 
										$stunden_lohn = get_user_meta( $ma->user_id, 'std_lohn', true ) . " €";
									else
										$stunden_lohn =  "-";
								?>
								<td><?php echo $stunden_lohn ?></td>
								<?php 
									if(get_user_meta( $ma->user_id, 'std_lohn_fest', true ) != null) 
										$fest_lohn = get_user_meta( $ma->user_id, 'std_lohn_fest', true ) . " €";
									else
										$fest_lohn = "-";
								?>
								<td><?php echo $fest_lohn ?></td>
								<td style='background: #ff9933'></td>
								<td><?php echo $uvm[$ma->user_id] != null ? $uvm[$ma->user_id] : "0" ?></td>
								<td><?php echo $ist_ma[$ma->user_id] != null ? $ist_ma[$ma->user_id] : "0" ?></td>
								<?php
									$ges_std = $ist_ma[$ma->user_id] + $uvm[$ma->user_id] != null ? $ist_ma[$ma->user_id] + $uvm[$ma->user_id] : "0";
								?>
								<td><?php echo $ges_std ?></td>								
								<td style='background: #ff9933'></td>
								<td><?php echo get_user_meta( $ma->user_id, 'std_mon', true ) != null ? get_user_meta( $ma->user_id, 'std_mon', true ) : "-" ?></td>								
								<?php
									$rest = $ist_ma[$ma->user_id] + $uvm[$ma->user_id] - get_user_meta( $ma->user_id, 'std_mon', true ) != null ? $ist_ma[$ma->user_id] + $uvm[$ma->user_id] - get_user_meta( $ma->user_id, 'std_mon', true ) : "0";
								?>
								<td><?php echo $rest ?></td>
								<?php
									if($data_sm[$ma->user_id]['auszahlung'] != null)
										$auszahlung = $data_sm[$ma->user_id]['auszahlung'];
									//elseif(get_user_meta( $ma->user_id, 'std_mon', true ) != null)
									//	$auszahlung = get_user_meta( $ma->user_id, 'std_mon', true );
									else
										$auszahlung = "0";
								?>								
								<td><input type="text" name="auszahlung_<?php echo $ma->user_id ?>" placeholder="" class="" value="<?php echo $auszahlung ?>"></td>
								<?php
									$ges_aus = get_user_meta( $ma->user_id, 'std_mon', true ) + $auszahlung != null ? get_user_meta( $ma->user_id, 'std_mon', true ) + $auszahlung : "0";
								?>
								<td><?php echo $ges_aus ?></td>
								<?php
								/*
								if(get_user_meta( $ma->user_id, 'type', true ) == "GfB")
									$bonus = "";
								else{
									if($bonus_ma[$ma->user_id] != null)
										$bonus = $bonus_ma[$ma->user_id];
									else
										$bonus = 0;
								}
								*/
								?>
								<!--<td style="<?php echo get_user_meta( $ma->user_id, 'type', true ) == "GfB" ? "background: #595959" : "" ?>"><?php echo $bonus ?></td>-->						
								<?php
									if($ges_std < 0)
										$unm = $ges_std + $ges_aus != null ? $ges_std + $ges_aus : "0";
									else
										$unm = $ges_std - $ges_aus != null ? $ges_std - $ges_aus : "0";
								?>
								<td><?php echo $unm ?></td>
								<td><input type="text" name="sonstiges_<?php echo $ma->user_id ?>" placeholder="" class="" value="<?php echo $data_sm[$ma->user_id]['sonstiges'] ?>"></td>
							</tr>
							<?php endforeach; ?>
						</tbody>					
					</table>
					</div>
				</div>			
			</div>
		</form>
		<script>
			var helperUrl = '/wp-content/plugins/itweb-booking/classes/Helper.php';
			 $( ".row_position_table1" ).sortable({  
					delay: 150,  
					stop: function() {  
						var selectedData = new Array();  
						$('.row_position_table1>tr').each(function() {  
							selectedData.push($(this).attr("id"));
							//alert(selectedData[0]);
						});  
						updateOrder(selectedData);  
					}  
				});
				 function updateOrder(data) {  
					$.ajax({  
						url: helperUrl,  
						type: 'POST',
						data: {
							task: 'einsatzplan_ordnen',
							position: data
						},  
						success:function(){  
						}  
					}) 
				} 
			</script>
		<?php ob_start(); ?>
		<style>
		.VZ{background: #00ace6}
		.TZ{background: #cccccc}
		.GfB{background: #ffff99}
		</style>
		<div class="row">
			<div class="col-sm-12 col-md-12">
				<h3 style='text-align:center'>Stundenmeldung - <?php echo $months[$c_month] . " " . $c_year ?></h3>
				<table class="table" style=' font-size: 12px; border-collapse:collapse; width: 100%'>
					<thead>
						<tr>
							<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; white-space: nowrap;'>BG Verkehr</th>
							<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; white-space: nowrap;'>Name</th>
							<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; white-space: nowrap;'>Status</th>
							<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; white-space: nowrap;'>Std. Lohn</th>
							<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; white-space: nowrap;'>Festgehalt</th>
							<!--<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; white-space: nowrap; width: 20px;'></th>
							<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; white-space: nowrap;'>Übertrag VM</th>
							<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; white-space: nowrap;'>Ist Monat</th>
							<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; white-space: nowrap;'>Gesamt Std.</th>
							<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; white-space: nowrap; width: 20px;'></th>-->
							<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; white-space: nowrap;'>Soll Ausz.</th>
							<!--<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; white-space: nowrap;'>Rest</th>
							<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; white-space: nowrap;'>Überstd. Ausz.</th>-->
							<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; white-space: nowrap;'>Gesamt Aus. MA</th>
							<!--<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; white-space: nowrap;'>Nachtschicht</th>
							<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; white-space: nowrap;'>Übertrag NM</th>-->
							<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; white-space: nowrap;'>Kommentar</th>
						<tr>
					</thead>
					<tbody>
						<?php foreach($mitarbeiter as $ma): ?>
						<?php $wp_user = get_user_by('id', $ma->user_id); ?>
						<?php $user_data = get_userdata( $ma->user_id ); ?>
						<?php 							
							if(in_array('fahrer', $user_data->roles))
								$rolle = "Fahrer";
							elseif(in_array('koordinator', $user_data->roles))
								$rolle = "Fahrer";
							elseif(in_array('admin2', $user_data->roles))
								$rolle = "Büro";
							if($ma->user_id == 7)
									$rolle = "Geschäftsführer";
						?>
						<tr>
							<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'><?php echo $rolle ?></td>
							<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'><?php echo $wp_user->display_name; ?></td>
							<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;' class="<?php echo get_user_meta( $ma->user_id, 'type', true ); ?>"><?php echo get_user_meta( $ma->user_id, 'type', true ); ?></td>
							<?php 
								if(get_user_meta( $ma->user_id, 'std_lohn', true ) != null) 
									$stunden_lohn = get_user_meta( $ma->user_id, 'std_lohn', true );
								else
									$stunden_lohn = "-";
							?>
							<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'><?php echo $stunden_lohn ?></td>
							<?php 
								if(get_user_meta( $ma->user_id, 'std_lohn_fest', true ) != null) 
									$fest_lohn = get_user_meta( $ma->user_id, 'std_lohn_fest', true );
								else
									$fest_lohn = "-";
							?>
							<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'><?php echo $fest_lohn ?></td>
							<!--<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap; background: #ff9933'></td>
							<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'><?php echo $uvm[$ma->user_id] != null ? $uvm[$ma->user_id] : "0" ?></td>
							<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'><?php echo $ist_ma[$ma->user_id] != null ? $ist_ma[$ma->user_id] : "0" ?></td>
							<?php
								$ges_std = $ist_ma[$ma->user_id] + $uvm[$ma->user_id] != null ? $ist_ma[$ma->user_id] + $uvm[$ma->user_id] : "0";
							?>
							<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'><?php echo $ges_std ?></td>
							<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap; background: #ff9933'></td>-->
							<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'><?php echo get_user_meta( $ma->user_id, 'std_mon', true ) != null ? get_user_meta( $ma->user_id, 'std_mon', true ) : "-" ?></td>								
							<?php
								$rest = $ist_ma[$ma->user_id] + $uvm[$ma->user_id] - get_user_meta( $ma->user_id, 'std_mon', true ) != null ? $ist_ma[$ma->user_id] + $uvm[$ma->user_id] - get_user_meta( $ma->user_id, 'std_mon', true ) : "0";
							?>
							<!--<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'><?php echo $rest ?></td>
							<?php
								if($data_sm[$ma->user_id]['auszahlung'] != null)
									$auszahlung = $data_sm[$ma->user_id]['auszahlung'];
								//elseif(get_user_meta( $ma->user_id, 'std_mon', true ) != null)
								//	$auszahlung = get_user_meta( $ma->user_id, 'std_mon', true );
								else
									$auszahlung = "0";
							?>								
							<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'><?php echo $auszahlung ?></td>-->
							<?php
								$ges_aus = get_user_meta( $ma->user_id, 'std_mon', true ) + $auszahlung != null ? get_user_meta( $ma->user_id, 'std_mon', true ) + $auszahlung : "0";
							?>
							<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'><?php echo $ges_aus ?></td>
							<?php
							/*
							if(get_user_meta( $ma->user_id, 'type', true ) == "GfB")
								$bonus = "";
							else{
								if($bonus_ma[$ma->user_id] != null)
									$bonus = $bonus_ma[$ma->user_id];
								else
									$bonus = 0;
							}
							*/
							?>
							<!--<td style="font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap; <?php echo get_user_meta( $ma->user_id, 'type', true ) == "GfB" ? "background: #595959" : "" ?>"><?php echo $bonus ?></td>						
							<?php
								if($ges_std < 0)
									$unm = $ges_std + $ges_aus != null ? $ges_std + $ges_aus : "0";
								else
									$unm = $ges_std - $ges_aus != null ? $ges_std - $ges_aus : "0";
							?>
							<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'><?php echo $unm ?></td>-->	
							<td style=' font-size: 12px; border: 1px solid black; padding:3px;'><?php echo $data_sm[$ma->user_id]['sonstiges'] ?></td>
						</tr>
						<?php endforeach; ?> 
					</tbody>					
				</table>
			</div>
		</div>
		<?php $content = ob_get_clean(); ?>
		<?php $_SESSION['stundenmeldung'] = $content; ?>	
	</div>
</div>
<script>
function change_year(e){
	var path = new URL(window.location.href);
	 path.searchParams.set('year', e.value);
	 location.href = path.href;
}
</script>