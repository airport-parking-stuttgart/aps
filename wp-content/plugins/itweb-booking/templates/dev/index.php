<?php
	
$db = Database::getInstance();
$current_user = wp_get_current_user();
$users_fahrer = $db->getActivUser_einsatzplan();
if(isset($_GET['cw']))
	$get_cw = "&cw=" . $_GET['cw'];
else
	$get_cw = "";

if (isset($_GET["year"]))
    $year1 = $_GET["year"];
else
    $year1 = date('Y');

if(isset($_GET['cw'])){
	$kw1 = $_GET['cw'];
}
else{
	$kw1 = date('W');
}
$query = "";
if(isset($_GET['year']))
	$query .= "&year=" . $_GET['year'];
else
	$query .= "";

if(isset($_GET['role']))
	$query .= "&role=" . $_GET['role'];
else
	$query .= "";

$k = $kw1;
$timestamp_montag = strtotime("{$year1}-W{$k}");
$firstDay = date("d.m.Y", $timestamp_montag);
$firstDay_kw1 = date("d.m.Y", $timestamp_montag);
$lastDay = date('d.m.Y', strtotime("+6 day", strtotime($firstDay)));
$week1 = $firstDay . " - " . $lastDay;

$date = new DateTime;
$date->setISODate($year1, 53);
$weeks = ($date->format("W") === "53" ? 53 : 52);

if($year1 >= date('Y'))
	$kws = date('W', strtotime(date('Y-m-d')));
else
	$kws = $weeks;

if(isset($_POST)){
	if($_POST['btn'] == $kw1){
		$kw = $_POST['kw_1'];
		unset($_POST['btn']);
		unset($_POST['kw_1']);

		foreach($users_fahrer as $fahrer){			
			if(isset($_GET['role'])){
				if($_GET['role'] == "buro" && $fahrer->role == "fahrer")
					continue;
				elseif($_GET['role'] == "fahrer" && $fahrer->role != "fahrer")
					continue;
			}
								
		}
		
		foreach($_POST as $key => $val){
			$key_parts = explode("_", $key);
			$data['state'] = $key_parts[1];
			$data['user_id'] = $key_parts[3];
			$data['id'] = $key_parts[9];
			if($data['state'] == 'in' && $data['id'] != null){
				$data['time_in'] = $val != null ? $val : null;		
				$db->updateStempelIn($data);
			}
			if($data['state'] == 'out' && $data['id'] != null){
				$data['time_out'] = $val != null ? $val : null;		
				$db->updateStempelOut($data);
			}
			if($data['id'] != null){
				$stempel = $db->getStempelById($data['id']);				
				
				if($stempel->time_in != null && $stempel->time_out != null){
					$check_in = $stempel->time_in;
					$check_out = $stempel->time_out;
					
					if(strtotime($check_in) > strtotime($check_out))
						$nex_day = 24;
					else
						$nex_day = 0;
					
					$diff_times = number_format(abs((strtotime($check_in) - strtotime($check_out)) / 3600 - $nex_day), 2, ".", ".");
					
					if($diff_times > 4.5 && $diff_times <= 7.5)
						$diff_times -= 0.5;
					elseif($diff_times > 7.5)
						$diff_times -= 1;					
					
					$user_id = $data['user_id'];
					$bonusFrom = get_user_meta($user_id, 'bonusab', true ).":00";
					$bonusTo = get_user_meta($user_id, 'bonusbis', true ).":00";
					
					// Convert to timestamps
					$check_in_time = strtotime($check_in);
					$check_out_time = strtotime($check_out);
					$bonusFrom_time = strtotime($bonusFrom);
					$bonusTo_time = strtotime($bonusTo);
					
					// If the shift crosses midnight, adjust the check_out time
					if ($check_out_time < $check_in_time) {
						$check_out_time += 86400;
					}
					
					// If the bonus period crosses midnight, adjust the bonusTo time
					if ($bonusTo_time < $bonusFrom_time) {
						$bonusTo_time += 86400;
					}
					
					$overlap = 0;
					
					// Calculate overlap with the current day's bonus period
					if ($check_in_time < $bonusTo_time) {
						$overlap += max(0, min($check_out_time, $bonusTo_time) - max($check_in_time, $bonusFrom_time));
					}
					
					// If the bonus period crosses midnight, calculate overlap with the previous day's bonus period
					if ($bonusFrom_time < $bonusTo_time) {
						$overlap += max(0, min($check_out_time, $bonusTo_time - 86400) - max($check_in_time, $bonusFrom_time - 86400));
					}
					
					$overlap_hours = $overlap / 3600;
								
					$bonus = $overlap_hours;
					
					$data['std'] = $diff_times;
					$data['nts'] = $bonus;
					$data['state'] = 'out';
					$db->updateStempelStd($data);
					$data = null;
				}
				else{
					$data['std'] = 0;
					$data['nts'] = 0;
					$data['state'] = 'in';
					$db->updateStempelStd($data);
					$data = null;
				}
				
				if($stempel->time_in == null && $stempel->time_out == null){
					$db->deleteStempel($stempel->id);
				}
			}
			
			if($data['id'] == null && $data['state'] == 'in' && $val != null){
				$data['rf_id'] = $key_parts[5];
				$data['date'] = date('Y-m-d', strtotime($key_parts[7]));
				$data['c_year'] = date('Y', strtotime($data['date']));
				$data['month'] =  date('m', strtotime($data['date']));
				$data['c_day'] =  date('d', strtotime($data['date']));
				$data['kw'] =  date('W', strtotime($data['date']));
				$data['weekday'] = $key_parts[12];
				$data['time'] = $val;
				addStempal($data['user_id'], $data['rf_id'], $data, 'in', 0, 0);
				//echo "<pre>"; print_r($data); echo "</pre>";
				$data = null;
			}
			if($data['id'] == null && $data['state'] == 'out' && $val != null){
				$data['date'] = date('Y-m-d', strtotime($key_parts[7]));
				$data['c_year'] = date('Y', strtotime($data['date']));
				$data['kw'] =  date('W', strtotime($data['date']));
				$data['weekday'] = $key_parts[12];
				$data['time_out'] = $val;
				$stempel = $db->getStempelKWWeekday($data['user_id'], $data['c_year'], $data['kw'], $data['weekday']);
				$data['id'] = $stempel->id;
				if($stempel->time_in){
					$db->updateStempelOut($data);
					$stempel = $db->getStempelKWWeekday($data['user_id'], $data['c_year'], $data['kw'], $data['weekday']);
					if($stempel->time_in != null && $stempel->time_out != null){
						$check_in = $stempel->time_in;
						$check_out = $stempel->time_out;
						
						if(strtotime($check_in) > strtotime($check_out))
							$nex_day = 24;
						else
							$nex_day = 0;
						
						$diff_times = number_format(abs((strtotime($check_in) - strtotime($check_out)) / 3600 - $nex_day), 2, ".", ".");
						
						if($diff_times > 4.5 && $diff_times <= 7.5)
							$diff_times -= 0.5;
						elseif($diff_times > 7.5)
							$diff_times -= 1;					
						
						$user_id = $data['user_id'];
						$bonusFrom = get_user_meta($user_id, 'bonusab', true ).":00";
						$bonusTo = get_user_meta($user_id, 'bonusbis', true ).":00";
						
						// Convert to timestamps
						$check_in_time = strtotime($check_in);
						$check_out_time = strtotime($check_out);
						$bonusFrom_time = strtotime($bonusFrom);
						$bonusTo_time = strtotime($bonusTo);
						
						// If the shift crosses midnight, adjust the check_out time
						if ($check_out_time < $check_in_time) {
							$check_out_time += 86400;
						}
						
						// If the bonus period crosses midnight, adjust the bonusTo time
						if ($bonusTo_time < $bonusFrom_time) {
							$bonusTo_time += 86400;
						}
						
						$overlap = 0;
						
						// Calculate overlap with the current day's bonus period
						if ($check_in_time < $bonusTo_time) {
							$overlap += max(0, min($check_out_time, $bonusTo_time) - max($check_in_time, $bonusFrom_time));
						}
						
						// If the bonus period crosses midnight, calculate overlap with the previous day's bonus period
						if ($bonusFrom_time < $bonusTo_time) {
							$overlap += max(0, min($check_out_time, $bonusTo_time - 86400) - max($check_in_time, $bonusFrom_time - 86400));
						}
						
						$overlap_hours = $overlap / 3600;
									
						$bonus = $overlap_hours;
						
						$data['std'] = $diff_times;
						$data['nts'] = $bonus;
						$data['state'] = 'out';
						$db->updateStempelStd($data);
						$data = null;
					}
					else{
						$data['std'] = 0;
						$data['nts'] = 0;
						$data['state'] = 'in';
						$db->updateStempelStd($data);
						$data = null;
					}
				}
			}
		}
		
		//echo("<script>location.href = '/wp-admin/admin.php?page=einsatzplan';</script>");
	}
}

function addStempal($user_id, $rf_id, $data, $state, $diff_times, $bonus){
	$db = Database::getInstance();
	$db->addStempel($user_id, $rf_id, $data, $state, $diff_times, $bonus);
}

?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>  
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>  
<style>
.table tbody tr:nth-child(even) {background: #dae5f0;}

.border_left{
	border-left: 1px solid;
}

th, td {white-space: nowrap;}

th, td{
	border: 1px solid black !important;
}

.table thead th{
	vertical-align:top; !important;
	background: #2d4154; 
	color: #fff;
}

.table-wrapper
{
    width: 100%;
    overflow: auto;
}

.headcol {
  position: absolute;
  width: 5em;
  left: 0;
  top: auto;
  border-top-width: 1px;
  /*only relevant for first row*/
  margin-top: -1px;
  /*compensate for top border*/
}

.headcol:before {
  content: 'Row ';
}

.bus_mr{
	width: 35px;
	height: 25px;
	min-height: 20px !important;
}
.pause{
	width: 40px;
	height: 25px;
	min-height: 20px !important;
}
.day_val{
	width: 60px;
	height: 25px;
	min-height: 20px !important;
}
tr{
	font-size: 0.8rem; !important;
}
</style>
<div class="page container-fluid <?php echo $_GET['page'] ?>">

    <div class="page-title itweb_adminpage_head">
		<h3>Stempelübersicht</h3>
	</div>
	<div class="page-body">		
		<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'birten'  || $current_user->user_login == 'hakan'): ?>
		<div class="row">
			<div class="col-sm-12 col-md-1">
				<select name="year" id="year" onchange="change_year(this)">
					<?php for ($i = 2021; $i <= date('Y'); $i++) : ?>
						<option value="<?php echo $i ?>" <?php echo $i == $year1 ? ' selected' : '' ?>>
							<?php echo $i ?>
						</option>
					<?php endfor; ?>
				</select>
			</div>
			<div class="col-sm-12 col-md-3" >
				<?php if($kw1 != 1): ?>
					<a href="<?php echo '/wp-admin/admin.php?page=stempelansicht&cw='.($kw1 - 1).$query ?>" class="btn btn-primary"><</a>
				<?php endif; ?>
				<select name="cw" id="cw" onchange="change_cw(this)">						
					<?php for($cw = ($kws*1); $cw >= 1 ; $cw--): ?>
					<?php
					$cw = $cw >= 10 ? $cw : "0".$cw;
					if(isset($_GET['cw']))
						$c_aw = $_GET['cw'];
					else
						$c_aw = $kw1;
					$c_k = $cw;
					$c_timestamp_montag = strtotime("{$year1}-W{$c_k}");
					$c_firstDay = date("d.m.Y", $c_timestamp_montag);
					$c_firstDay_kw1 = date("d.m.Y", $c_timestamp_montag);
					$c_lastDay = date('d.m.Y', strtotime("+6 day", strtotime($c_firstDay)));
					$c_week = $c_firstDay . " - " . $c_lastDay;
					?>
					<option value="<?php echo $cw ?>" <?php echo $cw == $c_aw ? ' selected' : '' ?>><?php echo "KW " . $cw . ": " . $c_week ?></option>
					<?php endfor; ?>
				</select>
				<?php if($kw1 < ($kws*1)): ?>
					<a href="<?php echo '/wp-admin/admin.php?page=stempelansicht&cw='.($kw1 + 1).$query ?>" class="btn btn-primary">></a>
				<?php endif; ?>
			</div>
			<div class="col-sm-12 col-md-1" >
				<select name="role" id="role" onchange="change_role(this)">						
					<option value="all" <?php echo $_GET['role'] == "all" ? ' selected' : '' ?>>Alle</option>
					<option value="buro" <?php echo $_GET['role'] == "buro" ? ' selected' : '' ?>>Büro</option>
					<option value="fahrer" <?php echo $_GET['role'] == "fahrer" ? ' selected' : '' ?>>Fahrer</option>
				</select>
			</div>
			<div class="col-sm-12 col-md-3" >				
				<form action="<?= get_site_url() . '/wp-content/plugins/itweb-booking/templates/personalplanung/stempelansicht-pdf.php'; ?>" method="post">
					<button type="submit" id="btnExport" value="Export to PDF" class="btn btn-success">Einsatzplan exportieren</button>
				</form>
			</div>
		</div>
		<br>
		<?php endif; ?>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . "?page=stempelansicht".$get_cw.$query; ?>">				
			<div class="row">
				<div class="col-sm-12 col-md-12" >
					<div class="table-wrapper" style="width: 100%; overflow: scroll;" >						
						<table class="table table-sm" id="table1">
							<thead>
								<tr>
									<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'birten' || $current_user->user_login == 'hakan'): ?>
									<th style="position: sticky;left:0;">KW <input type="text" name="kw_1" size="3" min="1" max="52" value="<?php echo $kw1; ?>" readonly></th>
									<?php else:?>
									<th style="position: sticky;left:0;">KW <?php echo $kw1; ?></th>
									<?php endif; ?>
									<?php if(date('Y-m-d', strtotime("+0 day", strtotime($firstDay_kw1))) <= date('Y-m-d', strtotime(date('Y-m-d')))): ?>
									<th style="border-left: 3px solid !important;">MO<br><?php echo date('d.m.', strtotime("+0 day", strtotime($firstDay_kw1))) ?></th>
									<th>Check-In</th>
									<th>Check-Out</th>
									<?php endif; ?>
									<?php if(date('Y-m-d', strtotime("+1 day", strtotime($firstDay_kw1))) <= date('Y-m-d', strtotime(date('Y-m-d')))): ?>
									<th style="border-left: 3px solid !important;">DI<br><?php echo date('d.m.', strtotime("+1 day", strtotime($firstDay_kw1))) ?></th>
									<th>Check-In</th>
									<th>Check-Out</th>
									<?php endif; ?>
									<?php if(date('Y-m-d', strtotime("+2 day", strtotime($firstDay_kw1))) <= date('Y-m-d', strtotime(date('Y-m-d')))): ?>
									<th style="border-left: 3px solid !important;">MI<br><?php echo date('d.m.', strtotime("+2 day", strtotime($firstDay_kw1))) ?></th>
									<th>Check-In</th>
									<th>Check-Out</th>
									<?php endif; ?>
									<?php if(date('Y-m-d', strtotime("+3 day", strtotime($firstDay_kw1))) <= date('Y-m-d', strtotime(date('Y-m-d')))): ?>
									<th style="border-left: 3px solid !important;">DO<br><?php echo date('d.m.', strtotime("+3 day", strtotime($firstDay_kw1))) ?></th>
									<th>Check-In</th>
									<th>Check-Out</th>
									<?php endif; ?>
									<?php if(date('Y-m-d', strtotime("+4 day", strtotime($firstDay_kw1))) <= date('Y-m-d', strtotime(date('Y-m-d')))): ?>
									<th style="border-left: 3px solid !important;">FR<br><?php echo date('d.m.', strtotime("+4 day", strtotime($firstDay_kw1))) ?></th>
									<th>Check-In</th>
									<th>Check-Out</th>
									<?php endif; ?>
									<?php if(date('Y-m-d', strtotime("+5 day", strtotime($firstDay_kw1))) <= date('Y-m-d', strtotime(date('Y-m-d')))): ?>
									<th style="border-left: 3px solid !important;">SA<br><?php echo date('d.m.', strtotime("+5 day", strtotime($firstDay_kw1))) ?></th>
									<th>Check-In</th>
									<th>Check-Out</th>
									<?php endif; ?>
									<?php if(date('Y-m-d', strtotime("+6 day", strtotime($firstDay_kw1))) <= date('Y-m-d', strtotime(date('Y-m-d')))): ?>
									<th style="border-left: 3px solid !important;">SO<br><?php echo date('d.m.', strtotime("+6 day", strtotime($firstDay_kw1))) ?></th>
									<th>Check-In</th>
									<th>Check-Out</th>
									<?php endif; ?>
								</tr>
							</thead>
							<tbody class="row_position_table1">
							<?php $i = 1; foreach($users_fahrer as $fahrer): ?>
								<?php
									if(isset($_GET['role'])){
										if($_GET['role'] == "buro" && $fahrer->role == "fahrer")
											continue;
										elseif($_GET['role'] == "fahrer" && $fahrer->role != "fahrer")
											continue;
									}
									
									if(get_user_meta($fahrer->user_id, 'stempel_nr', true ) == null)
										continue;
								?>
								<?php $wp_user = get_user_by('id', $fahrer->user_id); ?>
								<?php $data =  $db->getEinsatzplanByUserID($kw1, $year1, $fahrer->user_id) ?>						
								<tr style="background: <?php echo ($i % 2) != 1 ? '#dae5f0' : '#ffffff'?>" id="<?php echo $fahrer->user_id ?>">									
									<td style="position: sticky;left:0;background-color:<?php echo $left_color ?>;"><?php echo $wp_user->display_name; echo get_user_meta( $fahrer->user_id, 'type', true ) != null && get_user_meta( $fahrer->user_id, 'type', true ) != '-' ? ", " . get_user_meta( $fahrer->user_id, 'type', true ) : ""; ?></td>							
									<?php if(date('Y-m-d', strtotime("+0 day", strtotime($firstDay_kw1))) <= date('Y-m-d', strtotime(date('Y-m-d')))): ?>
										<td style="border-left: 3px solid !important;"><?php echo $data->mo != null ? $data->mo : "" ?></td>
										<?php $stempel =  $db->getStempelKWWeekday($fahrer->user_id, $year1, $kw1, 'mo') ?>
										<?php if($stempel->time_in): ?>										
											<td style="background: #ccffcc"><input type="time" style="width:75px;" name="time_in_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+0 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_mo" value="<?php echo date('H:i', strtotime($stempel->time_in)) ?>"></td>			
										<?php else: ?>
											<td style="background: #ccffcc"><input type="time" style="width:75px;" name="time_in_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+0 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_mo" value=""></td>
										<?php endif; ?>
										<?php if($stempel->time_out): ?>
											<td style="background: #ffcccc"><input type="time" style="width:75px;" name="time_out_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+0 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_mo" value="<?php echo date('H:i', strtotime($stempel->time_out)) ?>"></td>			
										<?php else: ?>
											<td style="background: #ffcccc"><input type="time" style="width:75px;" name="time_out_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+0 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_mo" value=""></td>
										<?php endif; ?>
									<?php endif; ?>
									
									<?php if(date('Y-m-d', strtotime("+1 day", strtotime($firstDay_kw1))) <= date('Y-m-d', strtotime(date('Y-m-d')))): ?>
										<td style="border-left: 3px solid !important;"><?php echo $data->di != null ? $data->di : "" ?></td>
										<?php $stempel =  $db->getStempelKWWeekday($fahrer->user_id, $year1, $kw1, 'di') ?>
										<?php if($stempel->time_in): ?>
											<td style="background: #ccffcc"><input type="time" style="width:75px;" name="time_in_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+1 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_di" value="<?php echo date('H:i', strtotime($stempel->time_in)) ?>"></td>			
										<?php else: ?>
											<td style="background: #ccffcc"><input type="time" style="width:75px;" name="time_in_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+1 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_di" value=""></td>
										<?php endif; ?>
										<?php if($stempel->time_out): ?>
											<td style="background: #ffcccc"><input type="time" style="width:75px;" name="time_out_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+1 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_di" value="<?php echo date('H:i', strtotime($stempel->time_out)) ?>"></td>			
										<?php else: ?>
											<td style="background: #ffcccc"><input type="time" style="width:75px;" name="time_out_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+1 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_di" value=""></td>
										<?php endif; ?>
									<?php endif; ?>
									
									<?php if(date('Y-m-d', strtotime("+2 day", strtotime($firstDay_kw1))) <= date('Y-m-d', strtotime(date('Y-m-d')))): ?>
										<td style="border-left: 3px solid !important;"><?php echo $data->mi != null ? $data->mi : "" ?></td>
										<?php $stempel =  $db->getStempelKWWeekday($fahrer->user_id, $year1, $kw1, 'mi') ?>
										<?php if($stempel->time_in): ?>
											<td style="background: #ccffcc"><input type="time" style="width:75px;" name="time_in_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+2 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_mi" value="<?php echo date('H:i', strtotime($stempel->time_in)) ?>"></td>			
										<?php else: ?>
											<td style="background: #ccffcc"><input type="time" style="width:75px;" name="time_in_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+2 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_mi" value=""></td>
										<?php endif; ?>
										<?php if($stempel->time_out): ?>
											<td style="background: #ffcccc"><input type="time" style="width:75px;" name="time_out_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+2 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_mi" value="<?php echo date('H:i', strtotime($stempel->time_out)) ?>"></td>			
										<?php else: ?>
											<td style="background: #ffcccc"><input type="time" style="width:75px;" name="time_out_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+2 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_mi" value=""></td>
										<?php endif; ?>
									<?php endif; ?>
									
									<?php if(date('Y-m-d', strtotime("+3 day", strtotime($firstDay_kw1))) <= date('Y-m-d', strtotime(date('Y-m-d')))): ?>
										<td style="border-left: 3px solid !important;"><?php echo $data->do != null ? $data->do : "" ?></td>
										<?php $stempel =  $db->getStempelKWWeekday($fahrer->user_id, $year1, $kw1, 'do') ?>
										<?php if($stempel->time_in): ?>
											<td style="background: #ccffcc"><input type="time" style="width:75px;" name="time_in_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+3 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_do" value="<?php echo date('H:i', strtotime($stempel->time_in)) ?>"></td>			
										<?php else: ?>
											<td style="background: #ccffcc"><input type="time" style="width:75px;" name="time_in_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+3 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_do" value=""></td>
										<?php endif; ?>
										<?php if($stempel->time_out): ?>
											<td style="background: #ffcccc"><input type="time" style="width:75px;" name="time_out_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+3 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_do" value="<?php echo date('H:i', strtotime($stempel->time_out)) ?>"></td>			
										<?php else: ?>
											<td style="background: #ffcccc"><input type="time" style="width:75px;" name="time_out_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+3 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_do" value=""></td>
										<?php endif; ?>
									<?php endif; ?>
									
									<?php if(date('Y-m-d', strtotime("+4 day", strtotime($firstDay_kw1))) <= date('Y-m-d', strtotime(date('Y-m-d')))): ?>
										<td style="border-left: 3px solid !important;"><?php echo $data->fr != null ? $data->fr : "" ?></td>
										<?php $stempel =  $db->getStempelKWWeekday($fahrer->user_id, $year1, $kw1, 'fr') ?>
										<?php if($stempel->time_in): ?>
											<td style="background: #ccffcc"><input type="time" style="width:75px;" name="time_in_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+4 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_fr" value="<?php echo date('H:i', strtotime($stempel->time_in)) ?>"></td>			
										<?php else: ?>
											<td style="background: #ccffcc"><input type="time" style="width:75px;" name="time_in_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+4 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_fr" value=""></td>
										<?php endif; ?>
										<?php if($stempel->time_out): ?>
											<td style="background: #ffcccc"><input type="time" style="width:75px;" name="time_out_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+4 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_fr" value="<?php echo date('H:i', strtotime($stempel->time_out)) ?>"></td>			
										<?php else: ?>
											<td style="background: #ffcccc"><input type="time" style="width:75px;" name="time_out_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+4 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_fr" value=""></td>
										<?php endif; ?>
									<?php endif; ?>
									
									<?php if(date('Y-m-d', strtotime("+5 day", strtotime($firstDay_kw1))) <= date('Y-m-d', strtotime(date('Y-m-d')))): ?>
										<td style="border-left: 3px solid !important;"><?php echo $data->sa != null ? $data->sa : "" ?></td>
										<?php $stempel =  $db->getStempelKWWeekday($fahrer->user_id, $year1, $kw1, 'sa') ?>
										<?php if($stempel->time_in): ?>
											<td style="background: #ccffcc"><input type="time" style="width:75px;" name="time_in_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+5 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_sa" value="<?php echo date('H:i', strtotime($stempel->time_in)) ?>"></td>			
										<?php else: ?>
											<td style="background: #ccffcc"><input type="time" style="width:75px;" name="time_in_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+5 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_sa" value=""></td>
										<?php endif; ?>
										<?php if($stempel->time_out): ?>
											<td style="background: #ffcccc"><input type="time" style="width:75px;" name="time_out_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+5 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_sa" value="<?php echo date('H:i', strtotime($stempel->time_out)) ?>"></td>			
										<?php else: ?>
											<td style="background: #ffcccc"><input type="time" style="width:75px;" name="time_out_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+5 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_sa" value=""></td>
										<?php endif; ?>
									<?php endif; ?>
									
									<?php if(date('Y-m-d', strtotime("+6 day", strtotime($firstDay_kw1))) <= date('Y-m-d', strtotime(date('Y-m-d')))): ?>
										<td style="border-left: 3px solid !important;"><?php echo $data->so != null ? $data->so : "" ?></td>
										<?php $stempel =  $db->getStempelKWWeekday($fahrer->user_id, $year1, $kw1, 'so') ?>
										<?php if($stempel->time_in): ?>
											<td style="background: #ccffcc"><input type="time" style="width:75px;" name="time_in_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+6 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_so" value="<?php echo date('H:i', strtotime($stempel->time_in)) ?>"></td>			
										<?php else: ?>
											<td style="background: #ccffcc"><input type="time" style="width:75px;" name="time_in_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+6 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_so" value=""></td>
										<?php endif; ?>
										<?php if($stempel->time_out): ?>
											<td style="background: #ffcccc"><input type="time" style="width:75px;" name="time_out_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+6 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_so" value="<?php echo date('H:i', strtotime($stempel->time_out)) ?>"></td>			
										<?php else: ?>
											<td style="background: #ffcccc"><input type="time" style="width:75px;" name="time_out_user_<?php echo $fahrer->user_id ?>_rfid_<?php echo get_user_meta($fahrer->user_id, 'stempel_nr', true ) ?>_date_<?php echo date('Y-m-d', strtotime("+6 day", strtotime($firstDay_kw1))) ?>_group_<?php echo $stempel->id ?>_kw_<?php echo $kw1 ?>_so" value=""></td>
										<?php endif; ?>
									<?php endif; ?>
								</tr>
								<?php $i ++; endforeach; ?>
							</tbody>
						</table>						
					</div>
					<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'birten'  || $current_user->user_login == 'hakan'): ?>
					<button class="btn btn-primary" type="submit" name="btn" value="<?php echo $kw1; ?>">Speichern</button>
					<?php endif; ?>						
				</div>
			</div>
		</form>
		<?php ob_start(); ?>
		<style>
		th{
			vertical-align:top; font-size: 11px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; white-space: nowrap; width: auto;
		}
		td{
			font-size: 11px; border: 1px solid black; padding:3px; white-space: nowrap; width: auto;
		}
		</style>
		<div class="row">
			<div class="col-sm-12 col-md-12" >
				<h3 style='text-align:center'>Stempelübersicht - KW <?php echo $kw1 . ", " . $week1 ?></h3>
				<table class="table" style=' font-size: 12px; border-collapse:collapse; width: 100%'>
					<thead>
						<tr>							
							<th>KW <?php echo $kw1; ?></th>
							<th>MO<br><?php echo date('d.m.', strtotime("+0 day", strtotime($firstDay_kw1))) ?></th>
							<th>In</th>
							<th>Out</th>
							<th>DI<br><?php echo date('d.m.', strtotime("+1 day", strtotime($firstDay_kw1))) ?></th>
							<th>In</th>
							<th>Out</th>
							<th>MI<br><?php echo date('d.m.', strtotime("+2 day", strtotime($firstDay_kw1))) ?></th>
							<th>In</th>
							<th>Out</th>
							<th>DO<br><?php echo date('d.m.', strtotime("+3 day", strtotime($firstDay_kw1))) ?></th>
							<th>In</th>
							<th>Out</th>
							<th>FR<br><?php echo date('d.m.', strtotime("+4 day", strtotime($firstDay_kw1))) ?></th>
							<th>In</th>
							<th>Out</th>
							<th>SA<br><?php echo date('d.m.', strtotime("+5 day", strtotime($firstDay_kw1))) ?></th>
							<th>In</th>
							<th>Out</th>
							<th>SO<br><?php echo date('d.m.', strtotime("+6 day", strtotime($firstDay_kw1))) ?></th>
							<th>In</th>
							<th>Out</th>
						</tr>
					</thead>
					<tbody class="row_position_table1">
						<?php foreach($users_fahrer as $fahrer): ?>
						<?php
							if(isset($_GET['role'])){
								if($_GET['role'] == "buro" && $fahrer->role == "fahrer")
									continue;
								elseif($_GET['role'] == "fahrer" && $fahrer->role != "fahrer")
									continue;
							}
							if(get_user_meta($fahrer->user_id, 'stempel_nr', true ) == null)
								continue;
						?>
						<?php $wp_user = get_user_by('id', $fahrer->user_id); ?>
						<?php $data =  $db->getEinsatzplanByUserID($kw1, $year1, $fahrer->user_id) ?>
						<tr>
							<td><?php echo $wp_user->display_name; echo get_user_meta( $fahrer->user_id, 'type', true ) != null && get_user_meta( $fahrer->user_id, 'type', true ) != '-' ? ", " . get_user_meta( $fahrer->user_id, 'type', true ) : ""; ?></td>																						
							<td><?php echo $data->mo != null ? $data->mo : "" ?></td>
							<?php $stempel =  $db->getStempelKWWeekday($fahrer->user_id, $year1, $kw1, 'mo') ?>
							<?php if($stempel->time_in): ?>										
								<td style="background: #ccffcc"><?php echo date('H:i', strtotime($stempel->time_in)) ?></td>			
							<?php else: ?>
								<td style="background: #ccffcc">-</td>
							<?php endif; ?>
							<?php if($stempel->time_out): ?>
								<td style="background: #ffcccc"><?php echo date('H:i', strtotime($stempel->time_out)) ?></td>			
							<?php else: ?>
								<td style="background: #ffcccc">-</td>
							<?php endif; ?>
							<td><?php echo $data->di != null ? $data->di : "" ?></td>
							<?php $stempel =  $db->getStempelKWWeekday($fahrer->user_id, $year1, $kw1, 'di') ?>
							<?php if($stempel->time_in): ?>										
								<td style="background: #ccffcc"><?php echo date('H:i', strtotime($stempel->time_in)) ?></td>			
							<?php else: ?>
								<td style="background: #ccffcc">-</td>
							<?php endif; ?>
							<?php if($stempel->time_out): ?>
								<td style="background: #ffcccc"><?php echo date('H:i', strtotime($stempel->time_out)) ?></td>			
							<?php else: ?>
								<td style="background: #ffcccc">-</td>
							<?php endif; ?>
							<td><?php echo $data->mi != null ? $data->mi : "" ?></td>
							<?php $stempel =  $db->getStempelKWWeekday($fahrer->user_id, $year1, $kw1, 'mi') ?>
							<?php if($stempel->time_in): ?>										
								<td style="background: #ccffcc"><?php echo date('H:i', strtotime($stempel->time_in)) ?></td>			
							<?php else: ?>
								<td style="background: #ccffcc">-</td>
							<?php endif; ?>
							<?php if($stempel->time_out): ?>
								<td style="background: #ffcccc"><?php echo date('H:i', strtotime($stempel->time_out)) ?></td>			
							<?php else: ?>
								<td style="background: #ffcccc">-</td>
							<?php endif; ?>
							<td><?php echo $data->do != null ? $data->do : "" ?></td>
							<?php $stempel =  $db->getStempelKWWeekday($fahrer->user_id, $year1, $kw1, 'do') ?>
							<?php if($stempel->time_in): ?>										
								<td style="background: #ccffcc"><?php echo date('H:i', strtotime($stempel->time_in)) ?></td>			
							<?php else: ?>
								<td style="background: #ccffcc">-</td>
							<?php endif; ?>
							<?php if($stempel->time_out): ?>
								<td style="background: #ffcccc"><?php echo date('H:i', strtotime($stempel->time_out)) ?></td>			
							<?php else: ?>
								<td style="background: #ffcccc">-</td>
							<?php endif; ?>
							<td><?php echo $data->fr != null ? $data->fr : "" ?></td>
							<?php $stempel =  $db->getStempelKWWeekday($fahrer->user_id, $year1, $kw1, 'fr') ?>
							<?php if($stempel->time_in): ?>										
								<td style="background: #ccffcc"><?php echo date('H:i', strtotime($stempel->time_in)) ?></td>			
							<?php else: ?>
								<td style="background: #ccffcc">-</td>
							<?php endif; ?>
							<?php if($stempel->time_out): ?>
								<td style="background: #ffcccc"><?php echo date('H:i', strtotime($stempel->time_out)) ?></td>			
							<?php else: ?>
								<td style="background: #ffcccc">-</td>
							<?php endif; ?>
							<td><?php echo $data->sa != null ? $data->sa : "" ?></td>
							<?php $stempel =  $db->getStempelKWWeekday($fahrer->user_id, $year1, $kw1, 'sa') ?>
							<?php if($stempel->time_in): ?>										
								<td style="background: #ccffcc"><?php echo date('H:i', strtotime($stempel->time_in)) ?></td>			
							<?php else: ?>
								<td style="background: #ccffcc">-</td>
							<?php endif; ?>
							<?php if($stempel->time_out): ?>
								<td style="background: #ffcccc"><?php echo date('H:i', strtotime($stempel->time_out)) ?></td>			
							<?php else: ?>
								<td style="background: #ffcccc">-</td>
							<?php endif; ?>
							<td><?php echo $data->so != null ? $data->so : "" ?></td>
							<?php $stempel =  $db->getStempelKWWeekday($fahrer->user_id, $year1, $kw1, 'so') ?>
							<?php if($stempel->time_in): ?>										
								<td style="background: #ccffcc"><?php echo date('H:i', strtotime($stempel->time_in)) ?></td>			
							<?php else: ?>
								<td style="background: #ccffcc">-</td>
							<?php endif; ?>
							<?php if($stempel->time_out): ?>
								<td style="background: #ffcccc"><?php echo date('H:i', strtotime($stempel->time_out)) ?></td>			
							<?php else: ?>
								<td style="background: #ffcccc">-</td>
							<?php endif; ?>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php $content = ob_get_clean(); ?>
		<?php $_SESSION['stempelansicht'] = $content; ?>
	</div>
</div>

<script>
function change_year(e){
	var path = new URL(window.location.href);
	 path.searchParams.set('year', e.value);
	 path.searchParams.delete('cw');
	 location.href = path.href;
}
function change_role(e){
	var path = new URL(window.location.href);
	 path.searchParams.set('role', e.value);
	 location.href = path.href;
}
function change_cw(e){
	var path = new URL(window.location.href);
	 path.searchParams.set('cw', e.value);
	 location.href = path.href;
}
</script>