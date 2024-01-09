<?php
$db = Database::getInstance();
$users = $db->getUser_einsatzplan();
if(isset($_GET['date']))
	$get_date = "&date=" . $_GET['date'];
else
	$get_date = "";

if(isset($_POST['btn_del'])){
	$id = $_POST['btn_del'];
	$db->deleteStempel($id);
}

if(isset($_POST['btn_edit'])){
	$id = $_POST['btn_edit'];
	$data['id'] = $id;
	if($_POST['date_'.$id]){	
		$data['date'] = date('Y-m-d', strtotime($_POST['date_'.$id]));
		$data['year'] = date('Y', strtotime($data['date']));
		$data['month'] =  date('m', strtotime($data['date']));
		$data['day'] =  date('d', strtotime($data['date']));
		$data['kw'] =  date('W', strtotime($data['date']));
		$wochentage = array("so", "mo", "di", "mi", "do", "fr", "sa");
		$tag = date('w', strtotime($data['date']));
		$data['weekday'] = $wochentage[$tag];
		$data['time_in'] = $_POST['time_in_'.$id];
		$data['time_out'] = $_POST['time_out_'.$id];
		$db->updateStempel($data);
	}
	
	if($_POST['time_in_'.$id] != null && $_POST['time_out_'.$id] != null){
		
		$check_in = $_POST['time_in_'.$id];
		$check_out = $_POST['time_out_'.$id];
		
		if(strtotime($check_in) > strtotime($check_out))
			$nex_day = 24;
		else
			$nex_day = 0;
		
		$diff_times = number_format(abs((strtotime($check_in) - strtotime($check_out)) / 3600 - $nex_day), 2, ".", ".");
		
		if($diff_times > 4.5 && $diff_times <= 7.5)
			$diff_times -= 0.5;
		elseif($diff_times > 7.5)
			$diff_times -= 1;
		
		$user_id = $_POST['user_id_'.$id];
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
		$db->updateStempelStd($data);
	}
	else{
		$data['std'] = 0;
		$data['nts'] = 0;		
		$db->updateStempelStd($data);
	}
}

$date = isok($_GET, 'date') ? dateFormat($_GET['date']) : date('Y-m-d');
$stempels = $db->getAllStempelsDate($date);

foreach($stempels as $stempel){
	$data_sg[$stempel->id]['id'] = $stempel->id;
	$data_sg[$stempel->id]['user_id'] = $stempel->user_id;
	$data_sg[$stempel->id]['rf_id'] = $stempel->rf_id;
	
	$data_sg[$stempel->id]['id'] = $stempel->id;
	$data_sg[$stempel->id]['date'] = $stempel->date;
	$data_sg[$stempel->id]['year'] = $stempel->year;
	$data_sg[$stempel->id]['month'] = $stempel->month;
	$data_sg[$stempel->id]['day'] = $stempel->day;
	$data_sg[$stempel->id]['kw'] = $stempel->kw;
	$data_sg[$stempel->id]['weekday'] = $stempel->weekday;
	$data_sg[$stempel->id]['time_in'] = $stempel->time_in;
	$data_sg[$stempel->id]['time_out'] = $stempel->time_out;
	
	$data_sg[$stempel->id]['std'] = $stempel->std;
	$data_sg[$stempel->id]['nts'] = $stempel->nts;
	
}

//echo "<pre>"; print_r($_POST); echo "</pre>";
?>
<style>
table{
	border-collapse: separate !important;
}

.dataTables_filter{
	float: left !important;
}
</style>
<div class="page container-fluid anreise-template <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Stempelsystem</h3>
    </div>
    <div class="page-body">
        <form class="form-filter m10">
			<div class="row ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Filtern</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row">
						<div class="col-12 col-md-1 ui-lotdata-date">
							<input type="text" placeholder="Datum" name="date"
								   value="<?php echo date('d.m.Y', strtotime($date)) ?>" class="single-datepicker form-control form-item">
						</div>
						<div class="col-12 col-md-1">
							<button class="btn btn-primary d-block w-100" type="submit">Filter</button>
						</div>
						<div class="col-sm-12 col-md-2">                    
							<a href="<?php echo '/wp-admin/admin.php?page=stempelsystem' ?>" class="btn btn-secondary d-block w-100" >Zurücksetzen</a>
						</div>
					</div>
				</div>
			</div>
        </form>
		<br><br>
		<div class="row">
			<div class="col-12 col-md-12">
				<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . "?page=stempelsystem".$get_date; ?>">	
					<table class="mitarbeiter_datatable table-responsive">
						<thead>
							<tr>
								<th>Name</th>
								<th>Vorname</th>
								<th>Kürzel</th>
								<th>MA Nr.</th>
								<th>Stempel Nr.</th>
								<th>Gruppe</th>
								<th>Datum</th>											
								<th>Uhrzeit In</th>										
								<th>Uhrzeit Out</th>									
								<th>Stunden</th>
								<th>Nacht-Bonus</th>
								<th>Aktion</th>
								<th>Löschen</th>
						</thead>
						<tbody>
							<?php foreach($data_sg as $stempel): ?>
								<?php $wp_user = get_user_by('id', $stempel['user_id']); ?>
								<?php if($wp_user != null): ?>								
								<tr>									
									<td><?php echo get_user_meta( $wp_user->ID, 'last_name', true ); ?></td>
									<td><?php echo get_user_meta( $wp_user->ID, 'first_name', true ); ?></td>
									<td><?php echo get_user_meta( $wp_user->ID, 'short_name', true ); ?></td>
									<td><?php echo get_user_meta( $wp_user->ID, 'ma_nr', true ); ?></td>
									<td><?php echo $stempel['rf_id']; ?></td>
									<td><?php echo get_user_meta( $wp_user->ID, 'type', true ) ?></td>
									<td style="display: none"><input type="hidden" name="user_id_<?php echo $stempel['id'] ?>" value="<?php echo $stempel['user_id'] ?>"></td>
									<td style="background: #ccffcc"><input type="text" style="width:115px;" class="single-datepicker" name="date_<?php echo $stempel['id'] ?>" value="<?php echo dateFormat($stempel['date'], 'de') ?>" readonly></td>
										
									<?php if($stempel['time_in']): ?>
										<td style="background: #ccffcc"><input type="time" style="width:100px;" name="time_in_<?php echo $stempel['id'] ?>" value="<?php echo date('H:i', strtotime($stempel['time_in'])) ?>"></td>
									<?php else: ?>
										<td style="background: #ccffcc"><input type="time" style="width:100px;" name="time_in_<?php echo $stempel['id'] ?>" value=""></td>
									<?php endif; ?>
									<?php if($stempel['time_out']): ?>
										<td style="background: #ffcccc"><input type="time" style="width:100px;" name="time_out_<?php echo $stempel['id'] ?>" value="<?php echo date('H:i', strtotime($stempel['time_out'])) ?>"></td>
									<?php else: ?>
										<td style="background: #ffcccc"><input type="time" style="width:100px;" name="time_out_<?php echo $stempel['id'] ?>" value=""></td>
									<?php endif; ?>
									<?php if($stempel['time_in'] && $stempel['time_out']): ?>
										<td><?php echo $stempel['std']; ?></td>
										<td><?php echo $stempel['nts']; ?></td>
									<?php else: ?>
										<td>-</td>
										<td>-</td>
									<?php endif; ?>
									<td><button class="btn btn-primary" type="submit" name="btn_edit" value="<?php echo $stempel['id'] ?>">Speichern</button></td>
									<td><button class="btn btn-danger" type="submit" name="btn_del" value="<?php echo $stempel['id'] ?>">Löschen</button></td>
								</tr>
								<?php endif; ?>
							<?php endforeach;?>
						</tbody>
					</table>
				</form>
			</div>
		</div>
	</div>
</div>