<?php
$db = Database::getInstance();

$current_user = wp_get_current_user();
$mitarbeiter = $db->getActivUser_einsatzplan();

$wochentage = array("So.", "Mo.", "Di.", "Mi.", "Do.", "Fr.", "Sa.");
$months = array('1' => 'Januar', '2' => 'Fabruar', '3' => 'März', '4' => 'April', '5' => 'Mai', '6' => 'Juni',
				'7' => 'Juli', '8' => 'August', '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Dezember');

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
$lastDayOfMonth = date($c_year."-".$month."-".$daysInMonth);
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
	$db->deleteZeitkonto($_POST['year'], $_POST['month']);
	foreach($_POST as $key => $val){
		if($key == 'btn' || $key == 'year' || $key == 'month')
			continue;
		$kd = explode("_", $key);
		$user_id = $kd[1];
		$data_uvm[$user_id] = $val;
	}
	foreach($data_uvm as $key => $val){
		$db->addZeitkonto($key, $val, $_POST['year'], $_POST['month']);
	}
}

$check = $db->checkZeitkonto($next_year, $next_month);

if($c_month == date('n') && $c_year == date('Y')){
	$css_close = "blue";
}
elseif($check != null){
	$css_close = "green";
}
else{
	$css_close = "red";
}



//echo "<pre>"; print_r($weeks); echo "</pre>";
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>  
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>  
<style>
.green{
	background: #248f24;
}
.red{
	background: #cc0000
}
.blue{
	background: #007bff
}
tr:nth-child(even) {background-color: #dae5f0;}

th, td{
	border: 1px solid black !important;
}

th{
	background: #c4dbff;
}
</style>


<div class="page container-fluid <?php echo $_GET['page'] ?>">

    <div class="page-title itweb_adminpage_head">
		<h3>Zeitkonto</h3>
	</div>
	<div class="page-body">
		<div class="row ui-lotdata-block ui-lotdata-block-next">
			<h5 class="ui-lotdata-title">Monat anzeigen</h5>
			<div class="col-sm-12 col-md-12 ui-lotdata">						
				<details id='1'>
					<summary>Datum | <a href="<?php echo '/wp-admin/admin.php?page=zeitkonto&month='.$prev_month.'&year='.$prev_year ?>" class="btn btn-primary"><</a> 
					<span class="btn btn-primary <?php echo $css_close ?>"><?php echo $months[$c_month] . " " . $c_year ?> </span>
					<a href="<?php echo '/wp-admin/admin.php?page=zeitkonto&month='.$next_month.'&year='.$next_year ?>" class="btn btn-primary">></a>					
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
							<?php 
							$check_month = $db->checkZeitkonto($c_year, 2); 
							if(1 == date('n') && $c_year == date('Y')){
								$css = "blue";
							}
							elseif($check_month != null){
								$css = "green";
							}
							else{
								$css = "red";
							}
							?>
							<a href="<?php echo '/wp-admin/admin.php?page=zeitkonto&month=1&year='.$c_year ?>" class="btn <?php echo $c_month == 1 ? 'btn-primary ' : 'btn-secondary '; echo $css ?>" >Januar</a>
							<?php 
							$check_month = $db->checkZeitkonto($c_year, 3); 
							if(2 == date('n') && $c_year == date('Y')){
								$css = "blue";
							}
							elseif($check_month != null){
								$css = "green";
							}
							else{
								$css = "red";
							}
							?>
							<a href="<?php echo '/wp-admin/admin.php?page=zeitkonto&month=2&year='.$c_year ?>" class="btn <?php echo $c_month == 2 ? 'btn-primary ' : 'btn-secondary '; echo $css ?>" >Februar</a>
							<?php 
							$check_month = $db->checkZeitkonto($c_year, 4); 
							if(3 == date('n') && $c_year == date('Y')){
								$css = "blue";
							}
							elseif($check_month != null){
								$css = "green";
							}
							else{
								$css = "red";
							}
							?>
							<a href="<?php echo '/wp-admin/admin.php?page=zeitkonto&month=3&year='.$c_year ?>" class="btn <?php echo $c_month == 3 ? 'btn-primary ' : 'btn-secondary '; echo $css ?>" >März</a>
							<?php 
							$check_month = $db->checkZeitkonto($c_year, 5); 
							if(4 == date('n') && $c_year == date('Y')){
								$css = "blue";
							}
							elseif($check_month != null){
								$css = "green";
							}
							else{
								$css = "red";
							}
							?>
							<a href="<?php echo '/wp-admin/admin.php?page=zeitkonto&month=4&year='.$c_year ?>" class="btn <?php echo $c_month == 4 ? 'btn-primary ' : 'btn-secondary '; echo $css ?>" >April</a>
							<?php 
							$check_month = $db->checkZeitkonto($c_year, 6); 
							if(5 == date('n') && $c_year == date('Y')){
								$css = "blue";
							}
							elseif($check_month != null){
								$css = "green";
							}
							else{
								$css = "red";
							}
							?>
							<a href="<?php echo '/wp-admin/admin.php?page=zeitkonto&month=5&year='.$c_year ?>" class="btn <?php echo $c_month == 5 ? 'btn-primary ' : 'btn-secondary '; echo $css ?>" >Mai</a>
							<?php 
							$check_month = $db->checkZeitkonto($c_year, 7); 
							if(6 == date('n') && $c_year == date('Y')){
								$css = "blue";
							}
							elseif($check_month != null){
								$css = "green";
							}
							else{
								$css = "red";
							}
							?>
							<a href="<?php echo '/wp-admin/admin.php?page=zeitkonto&month=6&year='.$c_year ?>" class="btn <?php echo $c_month == 6 ? 'btn-primary ' : 'btn-secondary '; echo $css ?>" >Juni</a>
							<?php 
							$check_month = $db->checkZeitkonto($c_year, 8); 
							if(7 == date('n') && $c_year == date('Y')){
								$css = "blue";
							}
							elseif($check_month != null){
								$css = "green";
							}
							else{
								$css = "red";
							}
							?>
							<a href="<?php echo '/wp-admin/admin.php?page=zeitkonto&month=7&year='.$c_year ?>" class="btn <?php echo $c_month == 7 ? 'btn-primary ' : 'btn-secondary '; echo $css ?>" >Juli</a>
							<?php 
							$check_month = $db->checkZeitkonto($c_year, 9); 
							if(8 == date('n') && $c_year == date('Y')){
								$css = "blue";
							}
							elseif($check_month != null){
								$css = "green";
							}
							else{
								$css = "red";
							}
							?>
							<a href="<?php echo '/wp-admin/admin.php?page=zeitkonto&month=8&year='.$c_year ?>" class="btn <?php echo $c_month == 8 ? 'btn-primary ' : 'btn-secondary '; echo $css ?>" >August</a>
							<?php 
							$check_month = $db->checkZeitkonto($c_year, 10); 
							if(9 == date('n') && $c_year == date('Y')){
								$css = "blue";
							}
							elseif($check_month != null){
								$css = "green";
							}
							else{
								$css = "red";
							}
							?>
							<a href="<?php echo '/wp-admin/admin.php?page=zeitkonto&month=9&year='.$c_year ?>" class="btn <?php echo $c_month == 9 ? 'btn-primary ' : 'btn-secondary '; echo $css ?>" >Sepember</a>
							<?php 
							$check_month = $db->checkZeitkonto($c_year, 11); 
							if(10 == date('n') && $c_year == date('Y')){
								$css = "blue";
							}
							elseif($check_month != null){
								$css = "green";
							}
							else{
								$css = "red";
							}
							?>
							<a href="<?php echo '/wp-admin/admin.php?page=zeitkonto&month=10&year='.$c_year ?>" class="btn <?php echo $c_month == 10 ? 'btn-primary ' : 'btn-secondary '; echo $css ?>" >Oktober</a>
							<?php 
							$check_month = $db->checkZeitkonto($c_year, 12); 
							if(11 == date('n') && $c_year == date('Y')){
								$css = "blue";
							}
							elseif($check_month != null){
								$css = "green";
							}
							else{
								$css = "red";
							}
							?>
							<a href="<?php echo '/wp-admin/admin.php?page=zeitkonto&month=11&year='.$c_year ?>" class="btn <?php echo $c_month == 11 ? 'btn-primary ' : 'btn-secondary '; echo $css ?>" >November</a>
							<?php 
							$check_month = $db->checkZeitkonto($next_year, 1); 
							if(12 == date('n') && $c_year == date('Y')){
								$css = "blue";
							}
							elseif($check_month != null){
								$css = "green";
							}
							else{
								$css = "red";
							}
							?>
							<a href="<?php echo '/wp-admin/admin.php?page=zeitkonto&month=12&year='.$c_year ?>" class="btn <?php echo $c_month == 12 ? 'btn-primary ' : 'btn-secondary '; echo $css ?>" >Dezember</a>
						</div>
					</div>
				</details>
				<br>
				<form action="<?= get_site_url() . '/wp-content/plugins/itweb-booking/templates/personalplanung/zeitkonto-pdf.php'; ?>" method="post">
					<button type="submit" id="btnExport" value="Export to PDF" class="btn btn-success">Zeitkonto exportieren</button>
				</form>
			</div>
		</div>
		<div class="row">
			<?php 
			$query = "";
			if(isset($_GET['month']))
				$query .= "&month=".$_GET['month']."&year=".$_GET['year'];
			?>
			<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . "?page=zeitkonto".$query; ?>">
				<input type="hidden" name="year" value="<?php echo $next_year ?>">
				<input type="hidden" name="month" value="<?php echo $next_month ?>">
				<div class="col-sm-12 col-md-12">
					<button class="btn btn-primary <?php echo $check != null ? "green" : "" ?>" type="submit" name="btn" value="1"><?php echo $check == null ? "Abschließen" : "Erneut abschließen" ?></button><br><br>
					<table class="table">
						<thead>
							<tr>
								<th></th>
								<th>ÜVM</th>
								<?php foreach ($weeks as $week => $val) : ?>
									<th><?php echo "KW: " . $week; ?></th>
								<?php endforeach; ?>
								<th>NTS-Stunden</th>
								<th>Ist</th>
								<th>Soll</th>
								<th>ÜNM</th>
							<tr>
						</thead>
						<tbody class="row_position_table1">
							<?php $n = 1; foreach($mitarbeiter as $ma): ?>
							<?php $wp_user = get_user_by('id', $ma->user_id); ?>							
							<tr id="<?php echo $ma->user_id ?>">
								<td><?php echo $wp_user->display_name; ?></td>
								<td><?php echo $uvm[$ma->user_id] != null ? $uvm[$ma->user_id] : "0" ?></td>
								<?php foreach ($weeks as $week => $val) : ?>
								<td><?php echo $diff_times[$week][$ma->user_id] != null ? $diff_times[$week][$ma->user_id] : "0" ?></td>
								<?php endforeach; ?>
								<td><?php echo $bonus_ma[$ma->user_id] != null ? $bonus_ma[$ma->user_id] : "0" ?></td>
								<td><?php echo $ist_ma[$ma->user_id] != null ? $ist_ma[$ma->user_id] : "0" ?></td>
								<td><?php echo get_user_meta( $ma->user_id, 'std_mon', true ) != null ? get_user_meta( $ma->user_id, 'std_mon', true ) : "-" ?></td>								
								<?php $unm = ($ist_ma[$ma->user_id] + $uvm[$ma->user_id]) - get_user_meta( $ma->user_id, 'std_mon', true ); ?>
								<td><input type="text" name="uvm_<?php echo $ma->user_id ?>" value="<?php echo $unm != null ? $unm : "0" ?>"></td>
							</tr>
							<?php $n++; endforeach; ?>
						</tbody>					
					</table>					
				</div>
			</form>
		</div>
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
		<div class="row">
			<div class="col-sm-12 col-md-12">
				<h3 style='text-align:center'>Zeitkonto - <?php echo $months[$c_month] . " " . $c_year ?></h3>
				<table  style=' font-size: 12px; border-collapse:collapse; width: 100%'>
					<thead>
						<tr>
							<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Mitarbeiter</th>
							<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>ÜVM</th>
							<?php foreach ($weeks as $week => $val) : ?>
								<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'><?php echo "KW: " . $week; ?></th>
							<?php endforeach; ?>
							<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>NTS-Stunden</th>
							<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Ist</th>
							<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>Soll</th>
							<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff;'>ÜNM</th>
							<th style=' font-size: 12px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; width: 200px;'>Vermerke</th>
						<tr>
					</thead>
					<tbody>
						<?php $n = 1; foreach($mitarbeiter as $ma): ?>
						<?php $wp_user = get_user_by('id', $ma->user_id); ?>							
						<tr>
							<th style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap; text-align:left'><?php echo $n . ". " . $wp_user->display_name; ?></th>
							<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'><?php echo $uvm[$ma->user_id] != null ? $uvm[$ma->user_id] : "0" ?></td>
							<?php foreach ($weeks as $week => $val) : ?>
							<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'><?php echo $diff_times[$week][$ma->user_id] != null ? $diff_times[$week][$ma->user_id] : "0" ?></td>
							<?php endforeach; ?>
							<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'><?php echo $bonus_ma[$ma->user_id] != null ? $bonus_ma[$ma->user_id] : "0" ?></td>
							<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'><?php echo $ist_ma[$ma->user_id] != null ? $ist_ma[$ma->user_id] : "0" ?></td>
							<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'><?php echo get_user_meta( $ma->user_id, 'std_mon', true ) != null ? get_user_meta( $ma->user_id, 'std_mon', true ) : "-" ?></td>								
							<?php $unm = ($ist_ma[$ma->user_id] + $uvm[$ma->user_id]) - get_user_meta( $ma->user_id, 'std_mon', true ); ?>
							<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap;'><?php echo $unm != null ? $unm : "0" ?></td>
							<td style=' font-size: 12px; border: 1px solid black; padding:3px; white-space: nowrap; width: 200px;'></td>
						</tr>
						<?php $n++; endforeach; ?>
					</tbody>					
				</table>
			</div>
		</div>
		<?php $content = ob_get_clean(); ?>
		<?php $_SESSION['zeitkonto'] = $content; ?>		
	</div>
</div>
<script>
function change_year(e){
	var path = new URL(window.location.href);
	 path.searchParams.set('year', e.value);
	 location.href = path.href;
}
</script>