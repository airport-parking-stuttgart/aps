<?php
	
$db = Database::getInstance();

$current_user = wp_get_current_user();

$users_fahrer = $db->getActivUser_einsatzplan();
//echo "<pre>"; print_r($users_fahrerAc); echo "</pre>";

$year1 = date('Y');
$year2 = date('Y');

$year3 = date('Y');
$year4 = date('Y');
$year5 = date('Y');
$year6 = date('Y');

if(isset($_GET['role']))
	$query = "&role=" . $_GET['role'];
else
	$query = "";
//if(isset($_GET['cw']))
//	$query = "&cw=" . $_GET['cw'];
//else
//	$query = "";

if(isset($_GET['cw'])){
	$kw1 = $_GET['cw'];
	$kw2 = $_GET['cw'] + 1;
	
	$kw3 = $_GET['cw'] - 1;
	$kw4 = $_GET['cw'] - 2;
	$kw5 = $_GET['cw'] - 3;
	$kw6 = $_GET['cw'] - 4;
}
else{
	$kw1 = date('W');
	$kw2 = date('W') + 1;
	
	$kw3 = date('W') - 1;
	$kw4 = date('W') - 2;
	$kw5 = date('W') - 3;
	$kw6 = date('W') - 4;
}

$k = $kw1;
$timestamp_montag = strtotime("{$year1}-W{$k}");
$firstDay = date("d.m.Y", $timestamp_montag);
$firstDay_kw1 = date("d.m.Y", $timestamp_montag);
$lastDay = date('d.m.Y', strtotime("+6 day", strtotime($firstDay)));
$week1 = $firstDay . " - " . $lastDay;

$date = new DateTime;
$date->setISODate(date('Y'), 53);
$weeks = ($date->format("W") === "53" ? 53 : 52);

if($kw1 >= $weeks){
	$year2++;
	$kw2 = 1;
}

if($kw1 == 1){
	$year3 --;
	$year4 --;
	$year5 --;
	$year6 --;
	$kw3 = $weeks - 1;
	$kw4 = $weeks - 2;
	$kw5 = $weeks - 3;
	$kw6 = $weeks - 4;
}

$k = $kw2 < 10 ? "0" . $kw2 : $kw2;
$timestamp_montag = strtotime("{$year1}-W{$k}");
$firstDay = date("d.m.Y", $timestamp_montag);
$firstDay_kw2 = date("d.m.Y", $timestamp_montag);
$lastDay = date('d.m.Y', strtotime("+6 day", strtotime($firstDay)));
$week2 = $firstDay . " - " . $lastDay;

$k = $kw3 < 10 ? "0" . $kw3 : $kw3;
$timestamp_montag = strtotime("{$year1}-W{$k}");
$firstDay = date("d.m.Y", $timestamp_montag);
$firstDay_kw3 = date("d.m.Y", $timestamp_montag);
$lastDay = date('d.m.Y', strtotime("+6 day", strtotime($firstDay)));
$week3 = $firstDay . " - " . $lastDay;

$k = $kw4 < 10 ? "0" . $kw4 : $kw4;
$timestamp_montag = strtotime("{$year1}-W{$k}");
$firstDay = date("d.m.Y", $timestamp_montag);
$firstDay_kw4 = date("d.m.Y", $timestamp_montag);
$lastDay = date('d.m.Y', strtotime("+6 day", strtotime($firstDay)));
$week4 = $firstDay . " - " . $lastDay;

$k = $kw5 < 10 ? "0" . $kw5 : $kw5;
$timestamp_montag = strtotime("{$year1}-W{$k}");
$firstDay = date("d.m.Y", $timestamp_montag);
$firstDay_kw5 = date("d.m.Y", $timestamp_montag);
$lastDay = date('d.m.Y', strtotime("+6 day", strtotime($firstDay)));
$week5 = $firstDay . " - " . $lastDay;

$k = $kw6 < 10 ? "0" . $kw6 : $kw6;
$timestamp_montag = strtotime("{$year1}-W{$k}");
$firstDay = date("d.m.Y", $timestamp_montag);
$firstDay_kw6 = date("d.m.Y", $timestamp_montag);
$lastDay = date('d.m.Y', strtotime("+6 day", strtotime($firstDay)));
$week6 = $firstDay . " - " . $lastDay;


if(isset($_POST)){
	if($_POST['btn'] == $kw1){
		$kw = $_POST['kw_1'];
		unset($_POST['btn']);
		unset($_POST['kw_1']);
		//$data = $_POST;
		
		$db->delete_einsatzplan($kw, $year1);
		//$db->add_einsatzplan($table, $kw, $data);
		
		foreach($users_fahrer as $fahrer){
			
			if(isset($_GET['role'])){
				if($_GET['role'] == "buro" && $fahrer->role == "fahrer")
					continue;
				elseif($_GET['role'] == "fahrer" && $fahrer->role != "fahrer")
					continue;
			}
								
			$mo_pause = $_POST['mo_'.$fahrer->user_id];
			if(($mo_pause != null && $mo_pause != "") && str_contains($mo_pause, '-')) {
				$times = explode("-", $mo_pause);
				if(strtotime($times[0]) > strtotime($times[1]))
					$nex_day = 24;
				else
					$nex_day = 0;
				
				$diff_times = number_format(abs((strtotime($times[0]) - strtotime($times[1])) / 3600 - $nex_day), 1, ".", ".");
				if($diff_times > 4.5 && $diff_times <= 7.5)
					$mo_pause_val[$fahrer->user_id] = 0.5;
				elseif($diff_times > 7.5)
					$mo_pause_val[$fahrer->user_id] = 1;
				else
					$mo_pause_val[$fahrer->user_id] = 0;
			}
			else
				$mo_pause_val[$fahrer->user_id] = '';
			
			$di_pause = $_POST['di_'.$fahrer->user_id];
			if(($di_pause != null && $di_pause != "") && str_contains($di_pause, '-')) {
				$times = explode("-", $di_pause);
				if(strtotime($times[0]) > strtotime($times[1]))
					$nex_day = 24;
				else
					$nex_day = 0;
				
				$diff_times = number_format(abs((strtotime($times[0]) - strtotime($times[1])) / 3600 - $nex_day), 1, ".", ".");
				if($diff_times > 4.5 && $diff_times <= 7.5)
					$di_pause_val[$fahrer->user_id] = 0.5;
				elseif($diff_times > 7.5)
					$di_pause_val[$fahrer->user_id] = 1;
				else
					$di_pause_val[$fahrer->user_id] = 0;
			}
			else
				$di_pause_val[$fahrer->user_id] = '';
			
			$mi_pause = $_POST['mi_'.$fahrer->user_id];
			if(($mi_pause != null && $mi_pause != "") && str_contains($mi_pause, '-')) {
				$times = explode("-", $mi_pause);
				if(strtotime($times[0]) > strtotime($times[1]))
					$nex_day = 24;
				else
					$nex_day = 0;
				
				$diff_times = number_format(abs((strtotime($times[0]) - strtotime($times[1])) / 3600 - $nex_day), 1, ".", ".");
				if($diff_times > 4.5 && $diff_times <= 7.5)
					$mi_pause_val[$fahrer->user_id] = 0.5;
				elseif($diff_times > 7.5)
					$mi_pause_val[$fahrer->user_id] = 1;
				else
					$mi_pause_val[$fahrer->user_id] = 0;
			}
			else
				$mi_pause_val[$fahrer->user_id] = '';
			
			$do_pause = $_POST['do_'.$fahrer->user_id];
			if(($do_pause != null && $do_pause != "") && str_contains($do_pause, '-')) {
				$times = explode("-", $do_pause);
				if(strtotime($times[0]) > strtotime($times[1]))
					$nex_day = 24;
				else
					$nex_day = 0;
				
				$diff_times = number_format(abs((strtotime($times[0]) - strtotime($times[1])) / 3600 - $nex_day), 1, ".", ".");
				if($diff_times > 4.5 && $diff_times <= 7.5)
					$do_pause_val[$fahrer->user_id] = 0.5;
				elseif($diff_times > 7.5)
					$do_pause_val[$fahrer->user_id] = 1;
				else
					$do_pause_val[$fahrer->user_id] = 0;
			}
			else
				$do_pause_val[$fahrer->user_id] = '';
			
			$fr_pause = $_POST['fr_'.$fahrer->user_id];
			if(($fr_pause != null && $fr_pause != "") && str_contains($fr_pause, '-')) {
				$times = explode("-", $fr_pause);
				if(strtotime($times[0]) > strtotime($times[1]))
					$nex_day = 24;
				else
					$nex_day = 0;
				
				$diff_times = number_format(abs((strtotime($times[0]) - strtotime($times[1])) / 3600 - $nex_day), 1, ".", ".");
				if($diff_times > 4.5 && $diff_times <= 7.5)
					$fr_pause_val[$fahrer->user_id] = 0.5;
				elseif($diff_times > 7.5)
					$fr_pause_val[$fahrer->user_id] = 1;
				else
					$fr_pause_val[$fahrer->user_id] = 0;
			}
			else
				$fr_pause_val[$fahrer->user_id] = '';
			
			$sa_pause = $_POST['sa_'.$fahrer->user_id];
			if(($sa_pause != null && $sa_pause != "") && str_contains($sa_pause, '-')) {
				$times = explode("-", $sa_pause);
				if(strtotime($times[0]) > strtotime($times[1]))
					$nex_day = 24;
				else
					$nex_day = 0;
				
				$diff_times = number_format(abs((strtotime($times[0]) - strtotime($times[1])) / 3600 - $nex_day), 1, ".", ".");
				if($diff_times > 4.5 && $diff_times <= 7.5)
					$sa_pause_val[$fahrer->user_id] = 0.5;
				elseif($diff_times > 7.5)
					$sa_pause_val[$fahrer->user_id] = 1;
				else
					$sa_pause_val[$fahrer->user_id] = 0;
			}
			else
				$sa_pause_val[$fahrer->user_id] = '';
			
			$so_pause = $_POST['so_'.$fahrer->user_id];
			if(($so_pause != null && $so_pause != "") && str_contains($so_pause, '-')) {
				$times = explode("-", $so_pause);
				if(strtotime($times[0]) > strtotime($times[1]))
					$nex_day = 24;
				else
					$nex_day = 0;
				
				$diff_times = number_format(abs((strtotime($times[0]) - strtotime($times[1])) / 3600 - $nex_day), 1, ".", ".");
				if($diff_times > 4.5 && $diff_times <= 7.5)
					$so_pause_val[$fahrer->user_id] = 0.5;
				elseif($diff_times > 7.5)
					$so_pause_val[$fahrer->user_id] = 1;
				else
					$so_pause_val[$fahrer->user_id] = 0;
			}
			else
				$so_pause_val[$fahrer->user_id] = '';
			
			$data[$fahrer->user_id]->fahrer_id = $_POST['fahrer_id_'.$fahrer->user_id];
			$data[$fahrer->user_id]->bus_mo = $_POST['bus_mo_'.$fahrer->user_id];
			$data[$fahrer->user_id]->mo = $_POST['mo_'.$fahrer->user_id];
			$data[$fahrer->user_id]->mo_pause = $mo_pause_val[$fahrer->user_id];
			$data[$fahrer->user_id]->bus_di = $_POST['bus_di_'.$fahrer->user_id];
			$data[$fahrer->user_id]->di = $_POST['di_'.$fahrer->user_id];
			$data[$fahrer->user_id]->di_pause = $di_pause_val[$fahrer->user_id];
			$data[$fahrer->user_id]->bus_mi = $_POST['bus_mi_'.$fahrer->user_id];
			$data[$fahrer->user_id]->mi = $_POST['mi_'.$fahrer->user_id];
			$data[$fahrer->user_id]->mi_pause = $mi_pause_val[$fahrer->user_id];
			$data[$fahrer->user_id]->bus_do = $_POST['bus_do_'.$fahrer->user_id];
			$data[$fahrer->user_id]->do = $_POST['do_'.$fahrer->user_id];
			$data[$fahrer->user_id]->do_pause = $do_pause_val[$fahrer->user_id];
			$data[$fahrer->user_id]->bus_fr = $_POST['bus_fr_'.$fahrer->user_id];
			$data[$fahrer->user_id]->fr = $_POST['fr_'.$fahrer->user_id];
			$data[$fahrer->user_id]->fr_pause = $fr_pause_val[$fahrer->user_id];
			$data[$fahrer->user_id]->bus_sa = $_POST['bus_sa_'.$fahrer->user_id];
			$data[$fahrer->user_id]->sa = $_POST['sa_'.$fahrer->user_id];
			$data[$fahrer->user_id]->sa_pause = $sa_pause_val[$fahrer->user_id];
			$data[$fahrer->user_id]->bus_so = $_POST['bus_so_'.$fahrer->user_id];
			$data[$fahrer->user_id]->so = $_POST['so_'.$fahrer->user_id];
			$data[$fahrer->user_id]->so_pause = $so_pause_val[$fahrer->user_id];
			$db->add_einsatzplan($kw, $year1, $data[$fahrer->user_id]);
		}
		//echo "<script>location.href = '/wp-admin/admin.php?page=einsatzplan".$query."';</script>";
	}
	elseif($_POST['btn'] == $kw2){
		$kw = $_POST['kw_2'];
		unset($_POST['btn']);
		unset($_POST['kw_2']);
		//$data = $_POST;
		
		$db->delete_einsatzplan($kw, $year2);
		//$db->add_einsatzplan($table, $kw, $data);
		
		foreach($users_fahrer as $fahrer){
			
			if(isset($_GET['role'])){
				if($_GET['role'] == "buro" && $fahrer->role == "fahrer")
					continue;
				elseif($_GET['role'] == "fahrer" && $fahrer->role != "fahrer")
					continue;
			}
			
			$mo_pause = $_POST['mo_'.$fahrer->user_id];
			if(($mo_pause != null && $mo_pause != "") && str_contains($mo_pause, '-')) {
				$times = explode("-", $mo_pause);
				if(strtotime($times[0]) > strtotime($times[1]))
					$nex_day = 24;
				else
					$nex_day = 0;
				
				$diff_times = number_format(abs((strtotime($times[0]) - strtotime($times[1])) / 3600 - $nex_day), 1, ".", ".");
				if($diff_times > 4.5 && $diff_times <= 7.5)
					$mo_pause_val[$fahrer->user_id] = 0.5;
				elseif($diff_times > 7.5)
					$mo_pause_val[$fahrer->user_id] = 1;
				else
					$mo_pause_val[$fahrer->user_id] = 0;
			}
			else
				$mo_pause_val[$fahrer->user_id] = '';
			
			$di_pause = $_POST['di_'.$fahrer->user_id];
			if(($di_pause != null && $di_pause != "") && str_contains($di_pause, '-')) {
				$times = explode("-", $di_pause);
				if(strtotime($times[0]) > strtotime($times[1]))
					$nex_day = 24;
				else
					$nex_day = 0;
				
				$diff_times = number_format(abs((strtotime($times[0]) - strtotime($times[1])) / 3600 - $nex_day), 1, ".", ".");
				if($diff_times > 4.5 && $diff_times <= 7.5)
					$di_pause_val[$fahrer->user_id] = 0.5;
				elseif($diff_times > 7.5)
					$di_pause_val[$fahrer->user_id] = 1;
				else
					$di_pause_val[$fahrer->user_id] = 0;
			}
			else
				$di_pause_val[$fahrer->user_id] = '';
			
			$mi_pause = $_POST['mi_'.$fahrer->user_id];
			if(($mi_pause != null && $mi_pause != "") && str_contains($mi_pause, '-')) {
				$times = explode("-", $mi_pause);
				if(strtotime($times[0]) > strtotime($times[1]))
					$nex_day = 24;
				else
					$nex_day = 0;
				
				$diff_times = number_format(abs((strtotime($times[0]) - strtotime($times[1])) / 3600 - $nex_day), 1, ".", ".");
				if($diff_times > 4.5 && $diff_times <= 7.5)
					$mi_pause_val[$fahrer->user_id] = 0.5;
				elseif($diff_times > 7.5)
					$mi_pause_val[$fahrer->user_id] = 1;
				else
					$mi_pause_val[$fahrer->user_id] = 0;
			}
			else
				$mi_pause_val[$fahrer->user_id] = '';
			
			$do_pause = $_POST['do_'.$fahrer->user_id];
			if(($do_pause != null && $do_pause != "") && str_contains($do_pause, '-')) {
				$times = explode("-", $do_pause);
				if(strtotime($times[0]) > strtotime($times[1]))
					$nex_day = 24;
				else
					$nex_day = 0;
				
				$diff_times = number_format(abs((strtotime($times[0]) - strtotime($times[1])) / 3600 - $nex_day), 1, ".", ".");
				if($diff_times > 4.5 && $diff_times <= 7.5)
					$do_pause_val[$fahrer->user_id] = 0.5;
				elseif($diff_times > 7.5)
					$do_pause_val[$fahrer->user_id] = 1;
				else
					$do_pause_val[$fahrer->user_id] = 0;
			}
			else
				$do_pause_val[$fahrer->user_id] = '';
			
			$fr_pause = $_POST['fr_'.$fahrer->user_id];
			if(($fr_pause != null && $fr_pause != "") && str_contains($fr_pause, '-')) {
				$times = explode("-", $fr_pause);
				if(strtotime($times[0]) > strtotime($times[1]))
					$nex_day = 24;
				else
					$nex_day = 0;
				
				$diff_times = number_format(abs((strtotime($times[0]) - strtotime($times[1])) / 3600 - $nex_day), 1, ".", ".");
				if($diff_times > 4.5 && $diff_times <= 7.5)
					$fr_pause_val[$fahrer->user_id] = 0.5;
				elseif($diff_times > 7.5)
					$fr_pause_val[$fahrer->user_id] = 1;
				else
					$fr_pause_val[$fahrer->user_id] = 0;
			}
			else
				$fr_pause_val[$fahrer->user_id] = '';
			
			$sa_pause = $_POST['sa_'.$fahrer->user_id];
			if(($sa_pause != null && $sa_pause != "") && str_contains($sa_pause, '-')) {
				$times = explode("-", $sa_pause);
				if(strtotime($times[0]) > strtotime($times[1]))
					$nex_day = 24;
				else
					$nex_day = 0;
				
				$diff_times = number_format(abs((strtotime($times[0]) - strtotime($times[1])) / 3600 - $nex_day), 1, ".", ".");
				if($diff_times > 4.5 && $diff_times <= 7.5)
					$sa_pause_val[$fahrer->user_id] = 0.5;
				elseif($diff_times > 7.5)
					$sa_pause_val[$fahrer->user_id] = 1;
				else
					$sa_pause_val[$fahrer->user_id] = 0;
			}
			else
				$sa_pause_val[$fahrer->user_id] = '';
			
			$so_pause = $_POST['so_'.$fahrer->user_id];
			if(($so_pause != null && $so_pause != "") && str_contains($so_pause, '-')) {
				$times = explode("-", $so_pause);
				if(strtotime($times[0]) > strtotime($times[1]))
					$nex_day = 24;
				else
					$nex_day = 0;
				
				$diff_times = number_format(abs((strtotime($times[0]) - strtotime($times[1])) / 3600 - $nex_day), 1, ".", ".");
				if($diff_times > 4.5 && $diff_times <= 7.5)
					$so_pause_val[$fahrer->user_id] = 0.5;
				elseif($diff_times > 7.5)
					$so_pause_val[$fahrer->user_id] = 1;
				else
					$so_pause_val[$fahrer->user_id] = 0;
			}
			else
				$so_pause_val[$fahrer->user_id] = '';
			
			$data[$fahrer->user_id]->fahrer_id = $_POST['fahrer_id_'.$fahrer->user_id];
			$data[$fahrer->user_id]->bus_mo = $_POST['bus_mo_'.$fahrer->user_id];
			$data[$fahrer->user_id]->mo = $_POST['mo_'.$fahrer->user_id];
			$data[$fahrer->user_id]->mo_pause = $mo_pause_val[$fahrer->user_id];
			$data[$fahrer->user_id]->bus_di = $_POST['bus_di_'.$fahrer->user_id];
			$data[$fahrer->user_id]->di = $_POST['di_'.$fahrer->user_id];
			$data[$fahrer->user_id]->di_pause = $di_pause_val[$fahrer->user_id];
			$data[$fahrer->user_id]->bus_mi = $_POST['bus_mi_'.$fahrer->user_id];
			$data[$fahrer->user_id]->mi = $_POST['mi_'.$fahrer->user_id];
			$data[$fahrer->user_id]->mi_pause = $mi_pause_val[$fahrer->user_id];
			$data[$fahrer->user_id]->bus_do = $_POST['bus_do_'.$fahrer->user_id];
			$data[$fahrer->user_id]->do = $_POST['do_'.$fahrer->user_id];
			$data[$fahrer->user_id]->do_pause = $do_pause_val[$fahrer->user_id];
			$data[$fahrer->user_id]->bus_fr = $_POST['bus_fr_'.$fahrer->user_id];
			$data[$fahrer->user_id]->fr = $_POST['fr_'.$fahrer->user_id];
			$data[$fahrer->user_id]->fr_pause = $fr_pause_val[$fahrer->user_id];
			$data[$fahrer->user_id]->bus_sa = $_POST['bus_sa_'.$fahrer->user_id];
			$data[$fahrer->user_id]->sa = $_POST['sa_'.$fahrer->user_id];
			$data[$fahrer->user_id]->sa_pause = $sa_pause_val[$fahrer->user_id];
			$data[$fahrer->user_id]->bus_so = $_POST['bus_so_'.$fahrer->user_id];
			$data[$fahrer->user_id]->so = $_POST['so_'.$fahrer->user_id];
			$data[$fahrer->user_id]->so_pause = $so_pause_val[$fahrer->user_id];
			$db->add_einsatzplan($kw, $year2, $data[$fahrer->user_id]);
		}
		//echo("<script>location.href = '/wp-admin/admin.php?page=einsatzplan".$query."';</script>");
	}

	elseif($_POST['btn_del'] == $kw1){
		$db->delete_einsatzplan($kw1, $year1);
		//echo("<script>location.href = '/wp-admin/admin.php?page=einsatzplan".$query."';</script>");
	}	
	elseif($_POST['btn_del'] == $kw2){
		$db->delete_einsatzplan($kw2, $year2);
		//echo("<script>location.href = '/wp-admin/admin.php?page=einsatzplan".$query."';</script>");
	}	
}


//echo "<pre>"; print_r($users_fahrer); echo "</pre>";


?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>  
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>  
<style>
.table tbody tr:nth-child(even) {background: #dae5f0;}

.border_left{
	border-left: 3px solid !important;
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
	width: 100px;
	height: 25px;
	min-height: 20px !important;
}
tr{
	font-size: 0.8rem; !important;
}
</style>
<div class="page container-fluid <?php echo $_GET['page'] ?>">

    <div class="page-title itweb_adminpage_head">
		<h3>Einsatzplan</h3>
	</div>
	<div class="page-body">		
		<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'birten'): ?>
		<div class="row">
			<div class="col-sm-12 col-md-3" >
				<?php if($kw1 != 1): ?>
					<a href="<?php echo '/wp-admin/admin.php?page=einsatzplan&cw='.($kw1 - 1).$query ?>" class="btn btn-primary"><</a>
				<?php endif; ?>
				<select name="cw" id="cw" onchange="change_cw(this)">						
					<?php for($cw = 1; $cw <= 52; $cw++): ?>
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
				<?php if($kw1 != $weeks): ?>
					<a href="<?php echo '/wp-admin/admin.php?page=einsatzplan&cw='.($kw1 + 1).$query ?>" class="btn btn-primary">></a>
				<?php endif; ?>
			</div>
			<div class="col-sm-12 col-md-1" >
				<select name="role" id="year" onchange="change_role(this)">						
					<option value="all" <?php echo $_GET['role'] == "all" ? ' selected' : '' ?>>Alle</option>
					<option value="buro" <?php echo $_GET['role'] == "buro" ? ' selected' : '' ?>>Büro</option>
					<option value="fahrer" <?php echo $_GET['role'] == "fahrer" ? ' selected' : '' ?>>Fahrer</option>
				</select>
			</div>
		</div>
		<br>
		<?php endif; ?>
		<hr>
		<div class="row">			
			<div class="col-sm-12 col-md-2">
				<h3>Aktuelle Woche</h3>
			</div>
			<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'birten'): ?>
			<div class="col-sm-12 col-md-3" >				
				<form action="<?= get_site_url() . '/wp-content/plugins/itweb-booking/templates/personalplanung/einsatzplan-pdf.php'; ?>" method="post">
					<input type="hidden" name="table" value="1">
					<button type="submit" id="btnExport" value="Export to PDF" class="btn btn-success">Einsatzplan exportieren</button>
				</form>
			</div>
			<?php endif; ?>
		</div>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . "?page=einsatzplan".$query; ?>">				
			<div class="row">
				<div class="col-sm-12 col-md-12" >
					<div class="table-wrapper" style="width: 100%; overflow: scroll;" >												
						<table class="table table-sm" id="table1">
							<thead>
								<tr>
									<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'birten'): ?>
									<th style="position: sticky;left:0;">KW <input type="text" name="kw_1" size="3" min="1" max="52" value="<?php echo $kw1; ?>" readonly></th>
									<?php else:?>
									<th style="position: sticky;left:0;">KW <?php echo $kw1; ?></th>
									<?php endif; ?>
									<th class="border_left">Bus</th>
									<th>MO<br><?php echo date('d.m.', strtotime("+0 day", strtotime($firstDay_kw1))) ?></th>
									<th>Pause</th>
									<th class="border_left">Bus</th>
									<th>DI<br><?php echo date('d.m.', strtotime("+1 day", strtotime($firstDay_kw1))) ?></th>
									<th>Pause</th>
									<th class="border_left">Bus</th>
									<th>MI<br><?php echo date('d.m.', strtotime("+2 day", strtotime($firstDay_kw1))) ?></th>
									<th>Pause</th>
									<th class="border_left">Bus</th>
									<th>DO<br><?php echo date('d.m.', strtotime("+3 day", strtotime($firstDay_kw1))) ?></th>
									<th>Pause</th>
									<th class="border_left">Bus</th>
									<th>FR<br><?php echo date('d.m.', strtotime("+4 day", strtotime($firstDay_kw1))) ?></th>
									<th>Pause</th>
									<th class="border_left">Bus</th>
									<th>SA<br><?php echo date('d.m.', strtotime("+5 day", strtotime($firstDay_kw1))) ?></th>
									<th>Pause</th>
									<th class="border_left">Bus</th>
									<th>SO<br><?php echo date('d.m.', strtotime("+6 day", strtotime($firstDay_kw1))) ?></th>
									<th>Pause</th>
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
								?>
								<?php $wp_user = get_user_by('id', $fahrer->user_id); ?>
								<?php $data =  $db->getEinsatzplanByUserID($kw1, $year1, $fahrer->user_id) ?>						
								<?php 
																			
									if($current_user->user_login != 'sergej' && $current_user->user_login != 'aras' && $current_user->user_login != 'cakir' && $current_user->user_login != 'soner' && $current_user->user_login != 'birten'){
										if($fahrer->user_id != get_current_user_id())
											continue;
									}									
																											
									if($data->mo != null && !str_contains($data->mo, '-') && !str_contains($data->mo, 'K') && !str_contains($data->mo, 'KK') && !str_contains($data->mo, 'F') && !str_contains($data->mo, '?'))
										$read_mo = "readonly";
									else
										$read_mo = "";
									
									if($data->di != null && !str_contains($data->di, '-') && !str_contains($data->di, 'K') && !str_contains($data->di, 'KK') && !str_contains($data->di, 'F') && !str_contains($data->di, '?'))
										$read_di = "readonly";
									else
										$read_di = "";
									
									if($data->mi != null && !str_contains($data->mi, '-') && !str_contains($data->mi, 'K') && !str_contains($data->mi, 'KK') && !str_contains($data->mi, 'F') && !str_contains($data->mi, '?'))
										$read_mi = "readonly";
									else
										$read_mi = "";
									
									if($data->do != null && !str_contains($data->do, '-') && !str_contains($data->do, 'K') && !str_contains($data->do, 'KK') && !str_contains($data->do, 'F') && !str_contains($data->do, '?'))
										$read_do = "readonly";
									else
										$read_do = "";
									
									if($data->fr != null && !str_contains($data->fr, '-') && !str_contains($data->fr, 'K') && !str_contains($data->fr, 'KK') && !str_contains($data->fr, 'F') && !str_contains($data->fr, '?'))
										$read_fr = "readonly";
									else
										$read_fr = "";
									
									if($data->sa != null && !str_contains($data->sa, '-') && !str_contains($data->sa, 'K') && !str_contains($data->sa, 'KK') && !str_contains($data->sa, 'F') && !str_contains($data->sa, '?'))
										$read_sa = "readonly";
									else
										$read_sa = "";
									
									if($data->so != null && !str_contains($data->so, '-') && !str_contains($data->so, 'K') && !str_contains($data->so, 'KK') && !str_contains($data->so, 'F') && !str_contains($data->so, '?'))
										$read_so = "readonly";
									else
										$read_so = "";
								?>
								<input type="hidden" name="fahrer_id_<?php echo $fahrer->user_id ?>" value="<?php echo $fahrer->user_id ?>">
								<tr style="background: <?php echo ($i % 2) != 1 ? '#dae5f0' : '#ffffff'?>" id="<?php echo $fahrer->user_id ?>">
									<td style="position: sticky;left:0;background: <?php echo ($i % 2) != 1 ? '#dae5f0' : '#ffffff'?>"><?php echo $wp_user->display_name; echo get_user_meta( $fahrer->user_id, 'type', true ) != null && get_user_meta( $fahrer->user_id, 'type', true ) != '-' ? ", " . get_user_meta( $fahrer->user_id, 'type', true ) : ""; ?></td>							
									<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'birten'): ?>
									<td class="border_left"><input class="bus_mr" type="text" name="bus_mo_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_mo != null && $data->bus_mo != 0 ? $data->bus_mo : "" ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '');"></td>
									<td><input class="day_val" type="text" name="mo_<?php echo $fahrer->user_id ?>" size="11" value="<?php echo $data->mo != null ? $data->mo : "" ?>" <?php echo $read_mo ?> oninput="validateInput(this)"></td>
									<td><input class="pause" type="text" name="mo_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->mo_pause != null ? $data->mo_pause : "" ?>" readonly></td>
									
									<td class="border_left"><input class="bus_mr" type="text" name="bus_di_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_di != null && $data->bus_di != 0 ? $data->bus_di : "" ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '');"></td>
									<td><input class="day_val" type="text" name="di_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->di != null ? $data->di : "" ?>" <?php echo $read_di ?> oninput="validateInput(this)"></td>
									<td><input class="pause" type="text" name="di_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->di_pause != null ? $data->di_pause : "" ?>" readonly></td>
									
									<td class="border_left"><input class="bus_mr" type="text" name="bus_mi_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_mi != null && $data->bus_mi != 0 ? $data->bus_mi : "" ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '');"></td>
									<td><input class="day_val" type="text" name="mi_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->mi != null ? $data->mi : "" ?>" <?php echo $read_mi ?> oninput="validateInput(this)"></td>
									<td><input class="pause" type="text" name="mi_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->mi_pause != null ? $data->mi_pause : "" ?>" readonly></td>
									
									<td class="border_left"><input class="bus_mr" type="text" name="bus_do_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_do != null && $data->bus_do != 0 ? $data->bus_do : "" ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '');"></td>
									<td><input class="day_val" type="text" name="do_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->do != null ? $data->do : "" ?>" <?php echo $read_do ?> oninput="validateInput(this)"></td>
									<td><input class="pause" type="text" name="do_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->do_pause != null ? $data->do_pause : "" ?>" readonly></td>
									
									<td class="border_left"><input class="bus_mr" type="text" name="bus_fr_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_fr != null && $data->bus_fr != 0 ? $data->bus_fr : "" ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '');"></td>
									<td><input class="day_val" type="text" name="fr_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->fr != null ? $data->fr : "" ?>" <?php echo $read_fr ?> oninput="validateInput(this)"></td>
									<td><input class="pause" type="text" name="fr_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->fr_pause != null ? $data->fr_pause : "" ?>" readonly></td>
									
									<td class="border_left"><input class="bus_mr" type="text" name="bus_sa_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_sa != null && $data->bus_sa != 0 ? $data->bus_sa : "" ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '');"></td>
									<td><input class="day_val" type="text" name="sa_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->sa != null ? $data->sa : "" ?>" <?php echo $read_sa ?> oninput="validateInput(this)"></td>
									<td><input class="pause" type="text" name="sa_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->sa_pause != null ? $data->sa_pause : "" ?>" readonly></td>
									
									<td class="border_left"><input class="bus_mr" type="text" name="bus_so_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_so != null && $data->bus_so != 0 ? $data->bus_so : "" ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '');"></td>
									<td><input class="day_val" type="text" name="so_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->so != null ? $data->so : "" ?>" <?php echo $read_so ?> oninput="validateInput(this)"></td>
									<td><input class="pause" type="text" name="so_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->so_pause != null ? $data->so_pause : "" ?>" readonly></td>
									
									<?php else:?>
									<td class="border_left"><?php echo $data->bus_mo != null && $data->bus_mo != 0 ? $data->bus_mo : "" ?></td>
									<td><?php echo $data->mo != null ? $data->mo : "" ?></td>
									<td><?php echo $data->mo_pause != null ? $data->mo_pause : "" ?></td>
									
									<td class="border_left"><?php echo $data->bus_di != null && $data->bus_di != 0 ? $data->bus_di : "" ?></td>
									<td><?php echo $data->di != null ? $data->di : "" ?></td>
									<td><?php echo $data->di_pause != null ? $data->di_pause : "" ?></td>
									
									<td class="border_left"><?php echo $data->bus_mi != null && $data->bus_mi != 0 ? $data->bus_mi : "" ?></td>
									<td><?php echo $data->mi != null ? $data->mi : "" ?></td>
									<td><?php echo $data->mi_pause != null ? $data->mi_pause : "" ?></td>
									
									<td class="border_left"><?php echo $data->bus_do != null && $data->bus_do != 0 ? $data->bus_do : "" ?></td>
									<td><?php echo $data->do != null ? $data->do : "" ?></td>
									<td><?php echo $data->do_pause != null ? $data->do_pause : "" ?></td>
									
									<td class="border_left"><?php echo $data->bus_fr != null && $data->bus_fr != 0 ? $data->bus_fr : "" ?></td>
									<td><?php echo $data->fr != null ? $data->fr : "" ?></td>
									<td><?php echo $data->fr_pause != null ? $data->fr_pause : "" ?></td>
									
									<td class="border_left"><?php echo $data->bus_sa != null && $data->bus_sa != 0 ? $data->bus_sa : "" ?></td>
									<td><?php echo $data->sa != null ? $data->sa : "" ?></td>
									<td><?php echo $data->sa_pause != null ? $data->sa_pause : "" ?></td>
									
									<td class="border_left"><?php echo $data->bus_so != null && $data->bus_so != 0 ? $data->bus_so : "" ?></td>
									<td><?php echo $data->so != null ? $data->so : "" ?></td>
									<td><?php echo $data->so_pause != null ? $data->so_pause : "" ?></td>									
									<?php endif; ?>								
								</tr>
								<?php $i ++; endforeach; ?>
							</tbody>
						</table>						
					</div>
					<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'birten'): ?>
					<button class="btn btn-primary" type="submit" name="btn" value="<?php echo $kw1; ?>">Speichern</button>
					<button class="btn btn-primary" type="submit" name="btn_del" value="<?php echo $kw1; ?>">Daten löschen</button>
					<?php endif; ?>						
				</div>
			</div>
		</form>
		<?php ob_start(); ?>
		<style>
		th{
			vertical-align:top; font-size: 11px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; white-space: nowrap;
		}
		td{
			font-size: 11px; border: 1px solid black; padding:3px; white-space: nowrap;
		}
		</style>
		<div class="row">
			<div class="col-sm-12 col-md-12" >
				<h3 style='text-align:center'>Einsatzplan - KW <?php echo $kw1 . ", " . $week1 ?></h3>
				<table class="table" style=' font-size: 12px; border-collapse:collapse; width: 100%'>
					<thead>
						<tr>							
							<th>KW <?php echo $kw1; ?></th>
							<th class="border_left">Bus</th>
							<th>MO<br><?php echo date('d.m.', strtotime("+0 day", strtotime($firstDay_kw1))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>DI<br><?php echo date('d.m.', strtotime("+1 day", strtotime($firstDay_kw1))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>MI<br><?php echo date('d.m.', strtotime("+2 day", strtotime($firstDay_kw1))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>DO<br><?php echo date('d.m.', strtotime("+3 day", strtotime($firstDay_kw1))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>FR<br><?php echo date('d.m.', strtotime("+4 day", strtotime($firstDay_kw1))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>SA<br><?php echo date('d.m.', strtotime("+5 day", strtotime($firstDay_kw1))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>SO<br><?php echo date('d.m.', strtotime("+6 day", strtotime($firstDay_kw1))) ?></th>
							<th>Pause</th>
							<th>Einsatzplan Bestätigung</th>
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
						?>
						<?php $wp_user = get_user_by('id', $fahrer->user_id); ?>
						<?php $data =  $db->getEinsatzplanByUserID($kw1, $year1, $fahrer->user_id) ?>
						<tr>
							<td><?php echo substr(get_user_meta($fahrer->user_id, 'first_name', true), 0, 1) . ". " . get_user_meta($fahrer->user_id, 'last_name', true); $wp_user->display_name; echo get_user_meta( $fahrer->user_id, 'type', true ) != null && get_user_meta( $fahrer->user_id, 'type', true ) != '-' ? ", " . get_user_meta( $fahrer->user_id, 'type', true ) : ""; ?></td>															
							<td class="border_left"><?php echo $data->bus_mo != null && $data->bus_mo != 0 ? $data->bus_mo : "" ?></td>	
							<td><?php echo $data->mo != null ? $data->mo : "" ?></td>
							<td><?php echo $data->mo_pause != null ? $data->mo_pause : "" ?></td>
							
							<td class="border_left"><?php echo $data->bus_di != null && $data->bus_di != 0 ? $data->bus_di : "" ?></td>
							<td><?php echo $data->di != null ? $data->di : "" ?></td>
							<td><?php echo $data->di_pause != null ? $data->di_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_mi != null && $data->bus_mi != 0 ? $data->bus_mi : "" ?></td>
							<td><?php echo $data->mi != null ? $data->mi : "" ?></td>
							<td><?php echo $data->mi_pause != null ? $data->mi_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_do != null && $data->bus_do != 0 ? $data->bus_do : "" ?></td>
							<td><?php echo $data->do != null ? $data->do : "" ?></td>
							<td><?php echo $data->do_pause != null ? $data->do_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_fr != null && $data->bus_fr != 0 ? $data->bus_fr : "" ?></td>
							<td><?php echo $data->fr != null ? $data->fr : "" ?></td>
							<td><?php echo $data->fr_pause != null ? $data->fr_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_sa != null && $data->bus_sa != 0 ? $data->bus_sa : "" ?></td>
							<td><?php echo $data->sa != null ? $data->sa : "" ?></td>
							<td><?php echo $data->sa_pause != null ? $data->sa_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_so != null && $data->bus_so != 0 ? $data->bus_so : "" ?></td>
							<td><?php echo $data->so != null ? $data->so : "" ?></td>
							<td><?php echo $data->so_pause != null ? $data->so_pause : "" ?></td>
							<td></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php $content = ob_get_clean(); ?>
		<?php $_SESSION['einsatzplan_1'] = $content; ?>
		
		<?php if(empty($_GET['role']) || $_GET['role'] == "all"): ?>
			<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'birten'): ?>
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
			<?php endif; ?>
		<?php endif; ?>
		<br><br>
		<hr>
		<div class="row">			
			<div class="col-sm-12 col-md-2">
				<h3>Nächste Woche</h3>
			</div>
			<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'birten'): ?>
			<div class="col-sm-12 col-md-2">
				<form action="<?= get_site_url() . '/wp-content/plugins/itweb-booking/templates/personalplanung/einsatzplan-pdf.php'; ?>" method="post">
					<input type="hidden" name="table" value="2">
					<button type="submit" id="btnExport" value="Export to PDF" class="btn btn-success">Einsatzplan exportieren</button>
				</form>
			</div>
			<?php endif; ?>
		</div>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . "?page=einsatzplan".$query; ?>">	
			<div class="row">			
				<div class="col-sm-12 col-md-12">
					<div class="table-wrapper" style="width: 100%; overflow: scroll;">																		
						<table class="table table-sm">
							<thead>
								<tr>
									<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'birten'): ?>
									<th style="position: sticky;left:0;">KW <input type="text" name="kw_2" size="3" min="1" max="52" value="<?php echo $kw2; ?>" readonly></th>
									<?php else:?>
									<th style="position: sticky;left:0;">KW <?php echo $kw2; ?><</th>
									<?php endif; ?>
									<th class="border_left">Bus</th>
									<th>MO<br><?php echo date('d.m.', strtotime("+0 day", strtotime($firstDay_kw2))) ?></th>
									<th>Pause</th>
									<th class="border_left">Bus</th>
									<th>DI<br><?php echo date('d.m.', strtotime("+1 day", strtotime($firstDay_kw2))) ?></th>
									<th>Pause</th>
									<th class="border_left">Bus</th>
									<th>MI<br><?php echo date('d.m.', strtotime("+2 day", strtotime($firstDay_kw2))) ?></th>
									<th>Pause</th>
									<th class="border_left">Bus</th>
									<th>DO<br><?php echo date('d.m.', strtotime("+3 day", strtotime($firstDay_kw2))) ?></th>
									<th>Pause</th>
									<th class="border_left">Bus</th>
									<th>FR<br><?php echo date('d.m.', strtotime("+4 day", strtotime($firstDay_kw2))) ?></th>
									<th>Pause</th>
									<th class="border_left">Bus</th>
									<th>SA<br><?php echo date('d.m.', strtotime("+5 day", strtotime($firstDay_kw2))) ?></th>
									<th>Pause</th>
									<th class="border_left">Bus</th>
									<th>SO<br><?php echo date('d.m.', strtotime("+6 day", strtotime($firstDay_kw2))) ?></th>
									<th>Pause</th>
								</tr>
							</thead>
							<tbody>
							<?php $i = 1; foreach($users_fahrer as $fahrer): ?>
								<?php
									if(isset($_GET['role'])){
										if($_GET['role'] == "buro" && $fahrer->role == "fahrer")
											continue;
										elseif($_GET['role'] == "fahrer" && $fahrer->role != "fahrer")
											continue;
									}
								?>
								<?php $wp_user = get_user_by('id', $fahrer->user_id); ?>
								<?php $data =  $db->getEinsatzplanByUserID($kw2, $year2, $fahrer->user_id) ?>
								<?php 
																		
									if($current_user->user_login != 'sergej' && $current_user->user_login != 'aras' && $current_user->user_login != 'cakir' && $current_user->user_login != 'soner' && $current_user->user_login != 'birten'){
										if($fahrer->user_id != get_current_user_id())
											continue;
									}									
									
									if($data->mo != null && !str_contains($data->mo, '-') && !str_contains($data->mo, 'K') && !str_contains($data->mo, 'KK') && !str_contains($data->mo, 'F') && !str_contains($data->mo, '?'))
										$read_mo = "readonly";
									else
										$read_mo = "";
									
									if($data->di != null && !str_contains($data->di, '-') && !str_contains($data->di, 'K') && !str_contains($data->di, 'KK') && !str_contains($data->di, 'F') && !str_contains($data->di, '?'))
										$read_di = "readonly";
									else
										$read_di = "";
									
									if($data->mi != null && !str_contains($data->mi, '-') && !str_contains($data->mi, 'K') && !str_contains($data->mi, 'KK') && !str_contains($data->mi, 'F') && !str_contains($data->mi, '?'))
										$read_mi = "readonly";
									else
										$read_mi = "";
									
									if($data->do != null && !str_contains($data->do, '-') && !str_contains($data->do, 'K') && !str_contains($data->do, 'KK') && !str_contains($data->do, 'F') && !str_contains($data->do, '?'))
										$read_do = "readonly";
									else
										$read_do = "";
									
									if($data->fr != null && !str_contains($data->fr, '-') && !str_contains($data->fr, 'K') && !str_contains($data->fr, 'KK') && !str_contains($data->fr, 'F') && !str_contains($data->fr, '?'))
										$read_fr = "readonly";
									else
										$read_fr = "";
									
									if($data->sa != null && !str_contains($data->sa, '-') && !str_contains($data->sa, 'K') && !str_contains($data->sa, 'KK') && !str_contains($data->sa, 'F') && !str_contains($data->sa, '?'))
										$read_sa = "readonly";
									else
										$read_sa = "";
									
									if($data->so != null && !str_contains($data->so, '-') && !str_contains($data->so, 'K') && !str_contains($data->so, 'KK') && !str_contains($data->so, 'F') && !str_contains($data->so, '?'))
										$read_so = "readonly";
									else
										$read_so = "";
								?>
								<input type="hidden" name="fahrer_id_<?php echo $fahrer->user_id ?>" value="<?php echo $fahrer->user_id ?>">
								<tr style="background: <?php echo ($i % 2) != 1 ? '#dae5f0' : '#ffffff'?>" id="<?php echo $fahrer->user_id ?>">
									<td style="position: sticky;left:0;background: <?php echo ($i % 2) != 1 ? '#dae5f0' : '#ffffff'?>"><?php echo $wp_user->display_name; echo get_user_meta( $fahrer->user_id, 'type', true ) != null && get_user_meta( $fahrer->user_id, 'type', true ) != '-' ? ", " . get_user_meta( $fahrer->user_id, 'type', true ) : ""; ?></td>									
									<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'birten'): ?>
									<td class="border_left"><input class="bus_mr" type="text" name="bus_mo_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_mo != null && $data->bus_mo != 0 ? $data->bus_mo : "" ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '');"></td>
									<td><input class="day_val" type="text" name="mo_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->mo != null ? $data->mo : "" ?>" <?php echo $read_mo ?> oninput="validateInput(this)"></td>
									<td><input class="pause" type="text" name="mo_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->mo_pause != null ? $data->mo_pause : "" ?>" readonly></td>
									
									<td class="border_left"><input class="bus_mr" type="text" name="bus_di_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_di != null && $data->bus_di != 0 ? $data->bus_di : "" ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '');"></td>
									<td><input class="day_val" type="text" name="di_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->di != null ? $data->di : "" ?>" <?php echo $read_di ?> oninput="validateInput(this)"></td>
									<td><input class="pause" type="text" name="di_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->di_pause != null ? $data->di_pause : "" ?>" readonly></td>
									
									<td class="border_left"><input class="bus_mr" type="text" name="bus_mi_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_mi != null && $data->bus_mi != 0 ? $data->bus_mi : "" ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '');"></td>
									<td><input class="day_val" type="text" name="mi_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->mi != null ? $data->mi : "" ?>" <?php echo $read_mi ?> oninput="validateInput(this)"></td>
									<td><input class="pause" type="text" name="mi_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->mi_pause != null ? $data->mi_pause : "" ?>" readonly></td>
									
									<td class="border_left"><input class="bus_mr" type="text" name="bus_do_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_do != null && $data->bus_do != 0 ? $data->bus_do : "" ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '');"></td>
									<td><input class="day_val" type="text" name="do_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->do != null ? $data->do : "" ?>" <?php echo $read_do ?> oninput="validateInput(this)"></td>
									<td><input class="pause" type="text" name="do_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->do_pause != null ? $data->do_pause : "" ?>" readonly></td>
									
									<td class="border_left"><input class="bus_mr" type="text" name="bus_fr_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_fr != null && $data->bus_fr != 0 ? $data->bus_fr : "" ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '');"></td>
									<td><input class="day_val" type="text" name="fr_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->fr != null ? $data->fr : "" ?>" <?php echo $read_fr ?> oninput="validateInput(this)"></td>
									<td><input class="pause" type="text" name="fr_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->fr_pause != null ? $data->fr_pause : "" ?>" readonly></td>
									
									<td class="border_left"><input class="bus_mr" type="text" name="bus_sa_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_sa != null && $data->bus_sa != 0 ? $data->bus_sa : "" ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '');"></td>
									<td><input class="day_val" type="text" name="sa_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->sa != null ? $data->sa : "" ?>" <?php echo $read_sa ?> oninput="validateInput(this)"></td>
									<td><input class="pause" type="text" name="sa_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->sa_pause != null ? $data->sa_pause : "" ?>" readonly></td>
									
									<td class="border_left"><input class="bus_mr" type="text" name="bus_so_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_so != null && $data->bus_so != 0 ? $data->bus_so : "" ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '');"></td>
									<td><input class="day_val" type="text" name="so_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->so != null ? $data->so : "" ?>" <?php echo $read_so ?> oninput="validateInput(this)"></td>
									<td><input class="pause" type="text" name="so_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->so_pause != null ? $data->so_pause : "" ?>" readonly></td>									
									<?php else:?>						
									<td class="border_left"><?php echo $data->bus_mo != null && $data->bus_mo != 0 ? $data->bus_mo : "" ?></td>
									<td><?php echo $data->mo != null ? $data->mo : "" ?></td>
									<td><?php echo $data->mo_pause != null ? $data->mo_pause : "" ?></td>
									
									<td class="border_left"><?php echo $data->bus_di != null && $data->bus_di != 0 ? $data->bus_di : "" ?></td>
									<td><?php echo $data->di != null ? $data->di : "" ?></td>
									<td><?php echo $data->di_pause != null ? $data->di_pause : "" ?></td>
									
									<td class="border_left"><?php echo $data->bus_mi != null && $data->bus_mi != 0 ? $data->bus_mi : "" ?></td>
									<td><?php echo $data->mi != null ? $data->mi : "" ?></td>
									<td><?php echo $data->mi_pause != null ? $data->mi_pause : "" ?></td>
									
									<td class="border_left"><?php echo $data->bus_do != null && $data->bus_do != 0 ? $data->bus_do : "" ?></td>
									<td><?php echo $data->do != null ? $data->do : "" ?></td>
									<td><?php echo $data->do_pause != null ? $data->do_pause : "" ?></td>
									
									<td class="border_left"><?php echo $data->bus_fr != null && $data->bus_fr != 0 ? $data->bus_fr : "" ?></td>
									<td><?php echo $data->fr != null ? $data->fr : "" ?></td>
									<td><?php echo $data->fr_pause != null ? $data->fr_pause : "" ?></td>
									
									<td class="border_left"><?php echo $data->bus_sa != null && $data->bus_sa != 0 ? $data->bus_sa : "" ?></td>
									<td><?php echo $data->sa != null ? $data->sa : "" ?></td>
									<td><?php echo $data->sa_pause != null ? $data->sa_pause : "" ?></td>
									
									<td class="border_left"><?php echo $data->bus_so != null && $data->bus_so != 0 ? $data->bus_so : "" ?></td>
									<td><?php echo $data->so != null ? $data->so : "" ?></td>
									<td><?php echo $data->so_pause != null ? $data->so_pause : "" ?></td>
									<?php endif; ?>									
								</tr>
								<?php $i++; endforeach; ?>
							</tbody>
						</table>					
					</div>
					<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'birten'): ?>
					<button class="btn btn-primary" type="submit" name="btn" value="<?php echo $kw2; ?>">Speichern</button>
					<button class="btn btn-primary" type="submit" name="btn_del" value="<?php echo $kw2; ?>">Daten löschen</button>
					<?php endif; ?>				
				</div>
			</div>
		</form>
		<?php ob_start(); ?>
		<style>
		th{
			vertical-align:top; font-size: 11px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; white-space: nowrap;
		}
		td{
			font-size: 11px; border: 1px solid black; padding:3px; white-space: nowrap;
		}
		</style>
		<div class="row">
			<div class="col-sm-12 col-md-12" >
				<h3 style='text-align:center'>Einsatzplan - KW <?php echo $kw2 . ", " . $week2 ?></h3>
				<table class="table" style=' font-size: 12px; border-collapse:collapse; width: 100%'>
					<thead>
						<tr>							
							<th>KW <?php echo $kw2; ?></th>
							<th class="border_left">Bus</th>
							<th>MO<br><?php echo date('d.m.', strtotime("+0 day", strtotime($firstDay_kw2))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>DI<br><?php echo date('d.m.', strtotime("+1 day", strtotime($firstDay_kw2))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>MI<br><?php echo date('d.m.', strtotime("+2 day", strtotime($firstDay_kw2))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>DO<br><?php echo date('d.m.', strtotime("+3 day", strtotime($firstDay_kw2))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>FR<br><?php echo date('d.m.', strtotime("+4 day", strtotime($firstDay_kw2))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>SA<br><?php echo date('d.m.', strtotime("+5 day", strtotime($firstDay_kw2))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>SO<br><?php echo date('d.m.', strtotime("+6 day", strtotime($firstDay_kw2))) ?></th>
							<th>Pause</th>
							<th>Einsatzplan Bestätigung</th>
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
						?>
						<?php $wp_user = get_user_by('id', $fahrer->user_id); ?>
						<?php $data =  $db->getEinsatzplanByUserID($kw2, $year2, $fahrer->user_id) ?>
						<tr>
							<td><?php echo substr(get_user_meta($fahrer->user_id, 'first_name', true), 0, 1) . ". " . get_user_meta($fahrer->user_id, 'last_name', true); $wp_user->display_name; echo get_user_meta( $fahrer->user_id, 'type', true ) != null && get_user_meta( $fahrer->user_id, 'type', true ) != '-' ? ", " . get_user_meta( $fahrer->user_id, 'type', true ) : ""; ?></td>															
							<td class="border_left"><?php echo $data->bus_mo != null && $data->bus_mo != 0 ? $data->bus_mo : "" ?></td>	
							<td><?php echo $data->mo != null ? $data->mo : "" ?></td>
							<td><?php echo $data->mo_pause != null ? $data->mo_pause : "" ?></td>
							
							<td class="border_left"><?php echo $data->bus_di != null && $data->bus_di != 0 ? $data->bus_di : "" ?></td>
							<td><?php echo $data->di != null ? $data->di : "" ?></td>
							<td><?php echo $data->di_pause != null ? $data->di_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_mi != null && $data->bus_mi != 0 ? $data->bus_mi : "" ?></td>
							<td><?php echo $data->mi != null ? $data->mi : "" ?></td>
							<td><?php echo $data->mi_pause != null ? $data->mi_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_do != null && $data->bus_do != 0 ? $data->bus_do : "" ?></td>
							<td><?php echo $data->do != null ? $data->do : "" ?></td>
							<td><?php echo $data->do_pause != null ? $data->do_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_fr != null && $data->bus_fr != 0 ? $data->bus_fr : "" ?></td>
							<td><?php echo $data->fr != null ? $data->fr : "" ?></td>
							<td><?php echo $data->fr_pause != null ? $data->fr_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_sa != null && $data->bus_sa != 0 ? $data->bus_sa : "" ?></td>
							<td><?php echo $data->sa != null ? $data->sa : "" ?></td>
							<td><?php echo $data->sa_pause != null ? $data->sa_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_so != null && $data->bus_so != 0 ? $data->bus_so : "" ?></td>
							<td><?php echo $data->so != null ? $data->so : "" ?></td>
							<td><?php echo $data->so_pause != null ? $data->so_pause : "" ?></td>
							<td></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php $content = ob_get_clean(); ?>
		<?php $_SESSION['einsatzplan_2'] = $content; ?>
		<br>
		<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'birten'): ?>
		<hr>
		<div class="row">			
			<div class="col-sm-12 col-md-12">
				<div class="table-wrapper" style="width: 100%; overflow: scroll;">						
					<h3>Vergangene 4 Wochen</h3>
					<form action="<?= get_site_url() . '/wp-content/plugins/itweb-booking/templates/personalplanung/einsatzplan-pdf.php'; ?>" method="post">
						<input type="hidden" name="table" value="3">
						<button type="submit" id="btnExport" value="Export to PDF" class="btn btn-success">Einsatzplan exportieren</button>
					</form><br>
					<table class="table table-sm">
						<thead>
							<tr>
								<th style="position: sticky;left:0;">KW <input type="text" name="kw_2" size="3" min="1" max="52" value="<?php echo $kw3; ?>" readonly></th>
								<th class="border_left">Bus</th>
								<th>MO<br><?php echo date('d.m.', strtotime("+0 day", strtotime($firstDay_kw3))) ?></th>
								<th>Pause</th>
								<th class="border_left">Bus</th>
								<th>DI<br><?php echo date('d.m.', strtotime("+1 day", strtotime($firstDay_kw3))) ?></th>
								<th>Pause</th>
								<th class="border_left">Bus</th>
								<th>MI<br><?php echo date('d.m.', strtotime("+2 day", strtotime($firstDay_kw3))) ?></th>
								<th>Pause</th>
								<th class="border_left">Bus</th>
								<th>DO<br><?php echo date('d.m.', strtotime("+3 day", strtotime($firstDay_kw3))) ?></th>
								<th>Pause</th>
								<th class="border_left">Bus</th>
								<th>FR<br><?php echo date('d.m.', strtotime("+4 day", strtotime($firstDay_kw3))) ?></th>
								<th>Pause</th>
								<th class="border_left">Bus</th>
								<th>SA<br><?php echo date('d.m.', strtotime("+5 day", strtotime($firstDay_kw3))) ?></th>
								<th>Pause</th>
								<th class="border_left">Bus</th>
								<th>SO<br><?php echo date('d.m.', strtotime("+6 day", strtotime($firstDay_kw3))) ?></th>
								<th>Pause</th>
							</tr>
						</thead>
						<tbody>
						<?php $i = 1; foreach($users_fahrer as $fahrer): ?>
							<?php
								if(isset($_GET['role'])){
									if($_GET['role'] == "buro" && $fahrer->role == "fahrer")
										continue;
									elseif($_GET['role'] == "fahrer" && $fahrer->role != "fahrer")
										continue;
								}
							?>
							<?php $wp_user = get_user_by('id', $fahrer->user_id); ?>
							<?php $data =  $db->getEinsatzplanByUserID($kw3, $year3, $fahrer->user_id) ?>						
							<tr style="background: <?php echo ($i % 2) != 1 ? '#dae5f0' : '#ffffff'?>">
								<td style="position: sticky;left:0;background: <?php echo ($i % 2) != 1 ? '#dae5f0' : '#ffffff'?>"><?php echo $wp_user->display_name; echo get_user_meta( $fahrer->user_id, 'type', true ) != null && get_user_meta( $fahrer->user_id, 'type', true ) != '-' ? ", " . get_user_meta( $fahrer->user_id, 'type', true ) : ""; ?></td>																							
								
								<td class="border_left"><?php echo $data->bus_mo != null && $data->bus_mo != 0 ? $data->bus_mo : "" ?></td>								
								<td><input class="day_val" type="text" name="mo_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->mo != null ? $data->mo : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="mo_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->mo_pause != null ? $data->mo_pause : "" ?>" readonly></td>								
								
								<td class="border_left"><input class="bus_mr" type="text" name="bus_di_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_di != null && $data->bus_di != 0 ? $data->bus_di : "" ?>" readonly></td>
								<td><input class="day_val" type="text" name="di_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->di != null ? $data->di : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="di_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->di_pause != null ? $data->di_pause : "" ?>" readonly></td>								
								
								<td class="border_left"><input class="bus_mr" type="text" name="bus_mi_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_mi != null && $data->bus_mi != 0 ? $data->bus_mi : "" ?>" readonly></td>
								<td><input class="day_val" type="text" name="mi_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->mi != null ? $data->mi : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="mi_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->mi_pause != null ? $data->mi_pause : "" ?>" readonly></td>
								
								
								<td class="border_left"><input class="bus_mr" type="text" name="bus_do_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_do != null && $data->bus_do != 0 ? $data->bus_do : "" ?>" readonly></td>
								<td><input class="day_val" type="text" name="do_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->do != null ? $data->do : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="do_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->do_pause != null ? $data->do_pause : "" ?>" readonly></td>
								
								<td class="border_left"><input class="bus_mr" type="text" name="bus_fr_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_fr != null && $data->bus_fr != 0 ? $data->bus_fr : "" ?>" readonly></td>
								<td><input class="day_val" type="text" name="fr_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->fr != null ? $data->fr : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="fr_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->fr_pause != null ? $data->fr_pause : "" ?>" readonly></td>
								
								<td class="border_left"><input class="bus_mr" type="text" name="bus_sa_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_sa != null && $data->bus_sa != 0 ? $data->bus_sa : "" ?>" readonly></td>
								<td><input class="day_val" type="text" name="sa_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->sa != null ? $data->sa : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="sa_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->sa_pause != null ? $data->sa_pause : "" ?>" readonly></td>
								
								<td class="border_left"><input class="bus_mr" type="text" name="bus_so_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_so != null && $data->bus_so != 0 ? $data->bus_so : "" ?>" readonly></td>
								<td><input class="day_val" type="text" name="so_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->so != null ? $data->so : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="so_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->so_pause != null ? $data->so_pause : "" ?>" readonly></td>								
							</tr>
							<?php $i++; endforeach; ?>
						</tbody>
					</table>					
				</div>			
			</div>
		</div>
		<?php ob_start(); ?>
		<style>
		th{
			vertical-align:top; font-size: 11px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; white-space: nowrap;
		}
		td{
			font-size: 11px; border: 1px solid black; padding:3px; white-space: nowrap;
		}
		</style>
		<div class="row">
			<div class="col-sm-12 col-md-12" >
				<h3 style='text-align:center'>Einsatzplan - KW <?php echo $kw3 . ", " . $week3 ?></h3>
				<table class="table" style=' font-size: 12px; border-collapse:collapse; width: 100%'>
					<thead>
						<tr>							
							<th>KW <?php echo $kw3; ?></th>
							<th class="border_left">Bus</th>
							<th>MO<br><?php echo date('d.m.', strtotime("+0 day", strtotime($firstDay_kw3))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>DI<br><?php echo date('d.m.', strtotime("+1 day", strtotime($firstDay_kw3))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>MI<br><?php echo date('d.m.', strtotime("+2 day", strtotime($firstDay_kw3))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>DO<br><?php echo date('d.m.', strtotime("+3 day", strtotime($firstDay_kw3))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>FR<br><?php echo date('d.m.', strtotime("+4 day", strtotime($firstDay_kw3))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>SA<br><?php echo date('d.m.', strtotime("+5 day", strtotime($firstDay_kw3))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>SO<br><?php echo date('d.m.', strtotime("+6 day", strtotime($firstDay_kw3))) ?></th>
							<th>Pause</th>
							<th>Einsatzplan Bestätigung</th>
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
						?>
						<?php $wp_user = get_user_by('id', $fahrer->user_id); ?>
						<?php $data =  $db->getEinsatzplanByUserID($kw3, $year3, $fahrer->user_id) ?>
						<tr>
							<td><?php echo substr(get_user_meta($fahrer->user_id, 'first_name', true), 0, 1) . ". " . get_user_meta($fahrer->user_id, 'last_name', true); $wp_user->display_name; echo get_user_meta( $fahrer->user_id, 'type', true ) != null && get_user_meta( $fahrer->user_id, 'type', true ) != '-' ? ", " . get_user_meta( $fahrer->user_id, 'type', true ) : ""; ?></td>															
							<td class="border_left"><?php echo $data->bus_mo != null && $data->bus_mo != 0 ? $data->bus_mo : "" ?></td>	
							<td><?php echo $data->mo != null ? $data->mo : "" ?></td>
							<td><?php echo $data->mo_pause != null ? $data->mo_pause : "" ?></td>
							
							<td class="border_left"><?php echo $data->bus_di != null && $data->bus_di != 0 ? $data->bus_di : "" ?></td>
							<td><?php echo $data->di != null ? $data->di : "" ?></td>
							<td><?php echo $data->di_pause != null ? $data->di_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_mi != null && $data->bus_mi != 0 ? $data->bus_mi : "" ?></td>
							<td><?php echo $data->mi != null ? $data->mi : "" ?></td>
							<td><?php echo $data->mi_pause != null ? $data->mi_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_do != null && $data->bus_do != 0 ? $data->bus_do : "" ?></td>
							<td><?php echo $data->do != null ? $data->do : "" ?></td>
							<td><?php echo $data->do_pause != null ? $data->do_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_fr != null && $data->bus_fr != 0 ? $data->bus_fr : "" ?></td>
							<td><?php echo $data->fr != null ? $data->fr : "" ?></td>
							<td><?php echo $data->fr_pause != null ? $data->fr_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_sa != null && $data->bus_sa != 0 ? $data->bus_sa : "" ?></td>
							<td><?php echo $data->sa != null ? $data->sa : "" ?></td>
							<td><?php echo $data->sa_pause != null ? $data->sa_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_so != null && $data->bus_so != 0 ? $data->bus_so : "" ?></td>
							<td><?php echo $data->so != null ? $data->so : "" ?></td>
							<td><?php echo $data->so_pause != null ? $data->so_pause : "" ?></td>
							<td></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php $content = ob_get_clean(); ?>
		<?php $_SESSION['einsatzplan_3'] = $content; ?>
		<br>
		<hr>
		<div class="row">			
			<div class="col-sm-12 col-md-12">
				<div class="table-wrapper" style="width: 100%; overflow: scroll;">						
					<form action="<?= get_site_url() . '/wp-content/plugins/itweb-booking/templates/personalplanung/einsatzplan-pdf.php'; ?>" method="post">
						<input type="hidden" name="table" value="4">
						<button type="submit" id="btnExport" value="Export to PDF" class="btn btn-success">Einsatzplan exportieren</button>
					</form><br>
					<table class="table table-sm">
						<thead>
							<tr>
								<th style="position: sticky;left:0;">KW <input type="text" name="kw_2" size="3" min="1" max="52" value="<?php echo $kw4; ?>" readonly></th>
								<th class="border_left">Bus</th>
								<th>MO<br><?php echo date('d.m.', strtotime("+0 day", strtotime($firstDay_kw4))) ?></th>
								<th>Pause</th>
								<th class="border_left">Bus</th>
								<th>DI<br><?php echo date('d.m.', strtotime("+1 day", strtotime($firstDay_kw4))) ?></th>
								<th>Pause</th>
								<th class="border_left">Bus</th>
								<th>MI<br><?php echo date('d.m.', strtotime("+2 day", strtotime($firstDay_kw4))) ?></th>
								<th>Pause</th>
								<th class="border_left">Bus</th>
								<th>DO<br><?php echo date('d.m.', strtotime("+3 day", strtotime($firstDay_kw4))) ?></th>
								<th>Pause</th>
								<th class="border_left">Bus</th>
								<th>FR<br><?php echo date('d.m.', strtotime("+4 day", strtotime($firstDay_kw4))) ?></th>
								<th>Pause</th>
								<th class="border_left">Bus</th>
								<th>SA<br><?php echo date('d.m.', strtotime("+5 day", strtotime($firstDay_kw4))) ?></th>
								<th>Pause</th>
								<th class="border_left">Bus</th>
								<th>SO<br><?php echo date('d.m.', strtotime("+6 day", strtotime($firstDay_kw4))) ?></th>
								<th>Pause</th>
							</tr>
						</thead>
						<tbody>
						<?php $i = 1; foreach($users_fahrer as $fahrer): ?>
							<?php
								if(isset($_GET['role'])){
									if($_GET['role'] == "buro" && $fahrer->role == "fahrer")
										continue;
									elseif($_GET['role'] == "fahrer" && $fahrer->role != "fahrer")
										continue;
								}
							?>
							<?php $wp_user = get_user_by('id', $fahrer->user_id); ?>
							<?php $data =  $db->getEinsatzplanByUserID($kw4, $year4, $fahrer->user_id) ?>						
							<tr style="background: <?php echo ($i % 2) != 1 ? '#dae5f0' : '#ffffff'?>">
								<td style="position: sticky;left:0;background: <?php echo ($i % 2) != 1 ? '#dae5f0' : '#ffffff'?>"><?php echo $wp_user->display_name; echo get_user_meta( $fahrer->user_id, 'type', true ) != null && get_user_meta( $fahrer->user_id, 'type', true ) != '-' ? ", " . get_user_meta( $fahrer->user_id, 'type', true ) : ""; ?></td>																							
								
								<td class="border_left"><?php echo $data->bus_mo != null && $data->bus_mo != 0 ? $data->bus_mo : "" ?></td>								
								<td><input class="day_val" type="text" name="mo_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->mo != null ? $data->mo : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="mo_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->mo_pause != null ? $data->mo_pause : "" ?>" readonly></td>								
								
								<td class="border_left"><input class="bus_mr" type="text" name="bus_di_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_di != null && $data->bus_di != 0 ? $data->bus_di : "" ?>" readonly></td>
								<td><input class="day_val" type="text" name="di_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->di != null ? $data->di : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="di_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->di_pause != null ? $data->di_pause : "" ?>" readonly></td>								
								
								<td class="border_left"><input class="bus_mr" type="text" name="bus_mi_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_mi != null && $data->bus_mi != 0 ? $data->bus_mi : "" ?>" readonly></td>
								<td><input class="day_val" type="text" name="mi_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->mi != null ? $data->mi : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="mi_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->mi_pause != null ? $data->mi_pause : "" ?>" readonly></td>
								
								
								<td class="border_left"><input class="bus_mr" type="text" name="bus_do_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_do != null && $data->bus_do != 0 ? $data->bus_do : "" ?>" readonly></td>
								<td><input class="day_val" type="text" name="do_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->do != null ? $data->do : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="do_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->do_pause != null ? $data->do_pause : "" ?>" readonly></td>
								
								<td class="border_left"><input class="bus_mr" type="text" name="bus_fr_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_fr != null && $data->bus_fr != 0 ? $data->bus_fr : "" ?>" readonly></td>
								<td><input class="day_val" type="text" name="fr_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->fr != null ? $data->fr : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="fr_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->fr_pause != null ? $data->fr_pause : "" ?>" readonly></td>
								
								<td class="border_left"><input class="bus_mr" type="text" name="bus_sa_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_sa != null && $data->bus_sa != 0 ? $data->bus_sa : "" ?>" readonly></td>
								<td><input class="day_val" type="text" name="sa_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->sa != null ? $data->sa : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="sa_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->sa_pause != null ? $data->sa_pause : "" ?>" readonly></td>
								
								<td class="border_left"><input class="bus_mr" type="text" name="bus_so_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_so != null && $data->bus_so != 0 ? $data->bus_so : "" ?>" readonly></td>
								<td><input class="day_val" type="text" name="so_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->so != null ? $data->so : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="so_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->so_pause != null ? $data->so_pause : "" ?>" readonly></td>								
							</tr>
							<?php $i++; endforeach; ?>
						</tbody>
					</table>					
				</div>			
			</div>
		</div>
		<?php ob_start(); ?>
		<style>
		th{
			vertical-align:top; font-size: 11px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; white-space: nowrap;
		}
		td{
			font-size: 11px; border: 1px solid black; padding:3px; white-space: nowrap;
		}
		</style>
		<div class="row">
			<div class="col-sm-12 col-md-12" >
				<h3 style='text-align:center'>Einsatzplan - KW <?php echo $kw4 . ", " . $week4 ?></h3>
				<table class="table" style=' font-size: 12px; border-collapse:collapse; width: 100%'>
					<thead>
						<tr>							
							<th>KW <?php echo $kw4; ?></th>
							<th class="border_left">Bus</th>
							<th>MO<br><?php echo date('d.m.', strtotime("+0 day", strtotime($firstDay_kw4))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>DI<br><?php echo date('d.m.', strtotime("+1 day", strtotime($firstDay_kw4))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>MI<br><?php echo date('d.m.', strtotime("+2 day", strtotime($firstDay_kw4))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>DO<br><?php echo date('d.m.', strtotime("+3 day", strtotime($firstDay_kw4))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>FR<br><?php echo date('d.m.', strtotime("+4 day", strtotime($firstDay_kw4))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>SA<br><?php echo date('d.m.', strtotime("+5 day", strtotime($firstDay_kw4))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>SO<br><?php echo date('d.m.', strtotime("+6 day", strtotime($firstDay_kw4))) ?></th>
							<th>Pause</th>
							<th>Einsatzplan Bestätigung</th>
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
						?>
						<?php $wp_user = get_user_by('id', $fahrer->user_id); ?>
						<?php $data =  $db->getEinsatzplanByUserID($kw4, $year4, $fahrer->user_id) ?>
						<tr>
							<td><?php echo substr(get_user_meta($fahrer->user_id, 'first_name', true), 0, 1) . ". " . get_user_meta($fahrer->user_id, 'last_name', true); $wp_user->display_name; echo get_user_meta( $fahrer->user_id, 'type', true ) != null && get_user_meta( $fahrer->user_id, 'type', true ) != '-' ? ", " . get_user_meta( $fahrer->user_id, 'type', true ) : ""; ?></td>															
							<td class="border_left"><?php echo $data->bus_mo != null && $data->bus_mo != 0 ? $data->bus_mo : "" ?></td>	
							<td><?php echo $data->mo != null ? $data->mo : "" ?></td>
							<td><?php echo $data->mo_pause != null ? $data->mo_pause : "" ?></td>
							
							<td class="border_left"><?php echo $data->bus_di != null && $data->bus_di != 0 ? $data->bus_di : "" ?></td>
							<td><?php echo $data->di != null ? $data->di : "" ?></td>
							<td><?php echo $data->di_pause != null ? $data->di_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_mi != null && $data->bus_mi != 0 ? $data->bus_mi : "" ?></td>
							<td><?php echo $data->mi != null ? $data->mi : "" ?></td>
							<td><?php echo $data->mi_pause != null ? $data->mi_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_do != null && $data->bus_do != 0 ? $data->bus_do : "" ?></td>
							<td><?php echo $data->do != null ? $data->do : "" ?></td>
							<td><?php echo $data->do_pause != null ? $data->do_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_fr != null && $data->bus_fr != 0 ? $data->bus_fr : "" ?></td>
							<td><?php echo $data->fr != null ? $data->fr : "" ?></td>
							<td><?php echo $data->fr_pause != null ? $data->fr_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_sa != null && $data->bus_sa != 0 ? $data->bus_sa : "" ?></td>
							<td><?php echo $data->sa != null ? $data->sa : "" ?></td>
							<td><?php echo $data->sa_pause != null ? $data->sa_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_so != null && $data->bus_so != 0 ? $data->bus_so : "" ?></td>
							<td><?php echo $data->so != null ? $data->so : "" ?></td>
							<td><?php echo $data->so_pause != null ? $data->so_pause : "" ?></td>
							<td></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php $content = ob_get_clean(); ?>
		<?php $_SESSION['einsatzplan_4'] = $content; ?>
		<br>
		<hr>
		<div class="row">			
			<div class="col-sm-12 col-md-12">
				<div class="table-wrapper" style="width: 100%; overflow: scroll;">						
					<form action="<?= get_site_url() . '/wp-content/plugins/itweb-booking/templates/personalplanung/einsatzplan-pdf.php'; ?>" method="post">
						<input type="hidden" name="table" value="5">
						<button type="submit" id="btnExport" value="Export to PDF" class="btn btn-success">Einsatzplan exportieren</button>
					</form><br>
					<table class="table table-sm">
						<thead>
							<tr>
								<th style="position: sticky;left:0;">KW <input type="text" name="kw_2" size="3" min="1" max="52" value="<?php echo $kw5; ?>" readonly></th>
								<th class="border_left">Bus</th>
								<th>MO<br><?php echo date('d.m.', strtotime("+0 day", strtotime($firstDay_kw5))) ?></th>
								<th>Pause</th>
								<th class="border_left">Bus</th>
								<th>DI<br><?php echo date('d.m.', strtotime("+1 day", strtotime($firstDay_kw5))) ?></th>
								<th>Pause</th>
								<th class="border_left">Bus</th>
								<th>MI<br><?php echo date('d.m.', strtotime("+2 day", strtotime($firstDay_kw5))) ?></th>
								<th>Pause</th>
								<th class="border_left">Bus</th>
								<th>DO<br><?php echo date('d.m.', strtotime("+3 day", strtotime($firstDay_kw5))) ?></th>
								<th>Pause</th>
								<th class="border_left">Bus</th>
								<th>FR<br><?php echo date('d.m.', strtotime("+4 day", strtotime($firstDay_kw5))) ?></th>
								<th>Pause</th>
								<th class="border_left">Bus</th>
								<th>SA<br><?php echo date('d.m.', strtotime("+5 day", strtotime($firstDay_kw5))) ?></th>
								<th>Pause</th>
								<th class="border_left">Bus</th>
								<th>SO<br><?php echo date('d.m.', strtotime("+6 day", strtotime($firstDay_kw5))) ?></th>
								<th>Pause</th>
							</tr>
						</thead>
						<tbody>
						<?php $i = 1; foreach($users_fahrer as $fahrer): ?>
							<?php
								if(isset($_GET['role'])){
									if($_GET['role'] == "buro" && $fahrer->role == "fahrer")
										continue;
									elseif($_GET['role'] == "fahrer" && $fahrer->role != "fahrer")
										continue;
								}
							?>
							<?php $wp_user = get_user_by('id', $fahrer->user_id); ?>
							<?php $data =  $db->getEinsatzplanByUserID($kw5, $year5, $fahrer->user_id) ?>						
							<tr style="background: <?php echo ($i % 2) != 1 ? '#dae5f0' : '#ffffff'?>">
								<td style="position: sticky;left:0;background: <?php echo ($i % 2) != 1 ? '#dae5f0' : '#ffffff'?>"><?php echo $wp_user->display_name; echo get_user_meta( $fahrer->user_id, 'type', true ) != null && get_user_meta( $fahrer->user_id, 'type', true ) != '-' ? ", " . get_user_meta( $fahrer->user_id, 'type', true ) : ""; ?></td>																							
								
								<td class="border_left"><?php echo $data->bus_mo != null && $data->bus_mo != 0 ? $data->bus_mo : "" ?></td>								
								<td><input class="day_val" type="text" name="mo_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->mo != null ? $data->mo : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="mo_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->mo_pause != null ? $data->mo_pause : "" ?>" readonly></td>								
								
								<td class="border_left"><input class="bus_mr" type="text" name="bus_di_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_di != null && $data->bus_di != 0 ? $data->bus_di : "" ?>" readonly></td>
								<td><input class="day_val" type="text" name="di_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->di != null ? $data->di : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="di_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->di_pause != null ? $data->di_pause : "" ?>" readonly></td>								
								
								<td class="border_left"><input class="bus_mr" type="text" name="bus_mi_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_mi != null && $data->bus_mi != 0 ? $data->bus_mi : "" ?>" readonly></td>
								<td><input class="day_val" type="text" name="mi_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->mi != null ? $data->mi : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="mi_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->mi_pause != null ? $data->mi_pause : "" ?>" readonly></td>
								
								
								<td class="border_left"><input class="bus_mr" type="text" name="bus_do_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_do != null && $data->bus_do != 0 ? $data->bus_do : "" ?>" readonly></td>
								<td><input class="day_val" type="text" name="do_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->do != null ? $data->do : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="do_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->do_pause != null ? $data->do_pause : "" ?>" readonly></td>
								
								<td class="border_left"><input class="bus_mr" type="text" name="bus_fr_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_fr != null && $data->bus_fr != 0 ? $data->bus_fr : "" ?>" readonly></td>
								<td><input class="day_val" type="text" name="fr_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->fr != null ? $data->fr : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="fr_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->fr_pause != null ? $data->fr_pause : "" ?>" readonly></td>
								
								<td class="border_left"><input class="bus_mr" type="text" name="bus_sa_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_sa != null && $data->bus_sa != 0 ? $data->bus_sa : "" ?>" readonly></td>
								<td><input class="day_val" type="text" name="sa_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->sa != null ? $data->sa : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="sa_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->sa_pause != null ? $data->sa_pause : "" ?>" readonly></td>
								
								<td class="border_left"><input class="bus_mr" type="text" name="bus_so_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_so != null && $data->bus_so != 0 ? $data->bus_so : "" ?>" readonly></td>
								<td><input class="day_val" type="text" name="so_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->so != null ? $data->so : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="so_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->so_pause != null ? $data->so_pause : "" ?>" readonly></td>								
							</tr>
							<?php $i++; endforeach; ?>
						</tbody>
					</table>					
				</div>			
			</div>
		</div>
		<?php ob_start(); ?>
		<style>
		th{
			vertical-align:top; font-size: 11px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; white-space: nowrap;
		}
		td{
			font-size: 11px; border: 1px solid black; padding:3px; white-space: nowrap;
		}
		</style>
		<div class="row">
			<div class="col-sm-12 col-md-12" >
				<h3 style='text-align:center'>Einsatzplan - KW <?php echo $kw5 . ", " . $week5 ?></h3>
				<table class="table" style=' font-size: 12px; border-collapse:collapse; width: 100%'>
					<thead>
						<tr>							
							<th>KW <?php echo $kw5; ?></th>
							<th class="border_left">Bus</th>
							<th>MO<br><?php echo date('d.m.', strtotime("+0 day", strtotime($firstDay_kw5))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>DI<br><?php echo date('d.m.', strtotime("+1 day", strtotime($firstDay_kw5))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>MI<br><?php echo date('d.m.', strtotime("+2 day", strtotime($firstDay_kw5))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>DO<br><?php echo date('d.m.', strtotime("+3 day", strtotime($firstDay_kw5))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>FR<br><?php echo date('d.m.', strtotime("+4 day", strtotime($firstDay_kw5))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>SA<br><?php echo date('d.m.', strtotime("+5 day", strtotime($firstDay_kw5))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>SO<br><?php echo date('d.m.', strtotime("+6 day", strtotime($firstDay_kw5))) ?></th>
							<th>Pause</th>
							<th>Einsatzplan Bestätigung</th>
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
						?>
						<?php $wp_user = get_user_by('id', $fahrer->user_id); ?>
						<?php $data =  $db->getEinsatzplanByUserID($kw5, $year5, $fahrer->user_id) ?>
						<tr>
							<td><?php echo substr(get_user_meta($fahrer->user_id, 'first_name', true), 0, 1) . ". " . get_user_meta($fahrer->user_id, 'last_name', true); $wp_user->display_name; echo get_user_meta( $fahrer->user_id, 'type', true ) != null && get_user_meta( $fahrer->user_id, 'type', true ) != '-' ? ", " . get_user_meta( $fahrer->user_id, 'type', true ) : ""; ?></td>															
							<td class="border_left"><?php echo $data->bus_mo != null && $data->bus_mo != 0 ? $data->bus_mo : "" ?></td>	
							<td><?php echo $data->mo != null ? $data->mo : "" ?></td>
							<td><?php echo $data->mo_pause != null ? $data->mo_pause : "" ?></td>
							
							<td class="border_left"><?php echo $data->bus_di != null && $data->bus_di != 0 ? $data->bus_di : "" ?></td>
							<td><?php echo $data->di != null ? $data->di : "" ?></td>
							<td><?php echo $data->di_pause != null ? $data->di_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_mi != null && $data->bus_mi != 0 ? $data->bus_mi : "" ?></td>
							<td><?php echo $data->mi != null ? $data->mi : "" ?></td>
							<td><?php echo $data->mi_pause != null ? $data->mi_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_do != null && $data->bus_do != 0 ? $data->bus_do : "" ?></td>
							<td><?php echo $data->do != null ? $data->do : "" ?></td>
							<td><?php echo $data->do_pause != null ? $data->do_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_fr != null && $data->bus_fr != 0 ? $data->bus_fr : "" ?></td>
							<td><?php echo $data->fr != null ? $data->fr : "" ?></td>
							<td><?php echo $data->fr_pause != null ? $data->fr_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_sa != null && $data->bus_sa != 0 ? $data->bus_sa : "" ?></td>
							<td><?php echo $data->sa != null ? $data->sa : "" ?></td>
							<td><?php echo $data->sa_pause != null ? $data->sa_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_so != null && $data->bus_so != 0 ? $data->bus_so : "" ?></td>
							<td><?php echo $data->so != null ? $data->so : "" ?></td>
							<td><?php echo $data->so_pause != null ? $data->so_pause : "" ?></td>
							<td></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php $content = ob_get_clean(); ?>
		<?php $_SESSION['einsatzplan_5'] = $content; ?>
		<br>
		<hr>
		<div class="row">			
			<div class="col-sm-12 col-md-12">
				<div class="table-wrapper" style="width: 100%; overflow: scroll;">						
					<form action="<?= get_site_url() . '/wp-content/plugins/itweb-booking/templates/personalplanung/einsatzplan-pdf.php'; ?>" method="post">
						<input type="hidden" name="table" value="6">
						<button type="submit" id="btnExport" value="Export to PDF" class="btn btn-success">Einsatzplan exportieren</button>
					</form><br>
					<table class="table table-sm">
						<thead>
							<tr>
								<th style="position: sticky;left:0;">KW <input type="text" name="kw_2" size="3" min="1" max="52" value="<?php echo $kw6; ?>" readonly></th>
								<th class="border_left">Bus</th>
								<th>MO<br><?php echo date('d.m.', strtotime("+0 day", strtotime($firstDay_kw6))) ?></th>
								<th>Pause</th>
								<th class="border_left">Bus</th>
								<th>DI<br><?php echo date('d.m.', strtotime("+1 day", strtotime($firstDay_kw6))) ?></th>
								<th>Pause</th>
								<th class="border_left">Bus</th>
								<th>MI<br><?php echo date('d.m.', strtotime("+2 day", strtotime($firstDay_kw6))) ?></th>
								<th>Pause</th>
								<th class="border_left">Bus</th>
								<th>DO<br><?php echo date('d.m.', strtotime("+3 day", strtotime($firstDay_kw6))) ?></th>
								<th>Pause</th>
								<th class="border_left">Bus</th>
								<th>FR<br><?php echo date('d.m.', strtotime("+4 day", strtotime($firstDay_kw6))) ?></th>
								<th>Pause</th>
								<th class="border_left">Bus</th>
								<th>SA<br><?php echo date('d.m.', strtotime("+5 day", strtotime($firstDay_kw6))) ?></th>
								<th>Pause</th>
								<th class="border_left">Bus</th>
								<th>SO<br><?php echo date('d.m.', strtotime("+6 day", strtotime($firstDay_kw6))) ?></th>
								<th>Pause</th>
							</tr>
						</thead>
						<tbody>
						<?php $i = 1; foreach($users_fahrer as $fahrer): ?>
							<?php
								if(isset($_GET['role'])){
									if($_GET['role'] == "buro" && $fahrer->role == "fahrer")
										continue;
									elseif($_GET['role'] == "fahrer" && $fahrer->role != "fahrer")
										continue;
								}
							?>
							<?php $wp_user = get_user_by('id', $fahrer->user_id); ?>
							<?php $data =  $db->getEinsatzplanByUserID($kw6, $year6, $fahrer->user_id) ?>						
							<tr style="background: <?php echo ($i % 2) != 1 ? '#dae5f0' : '#ffffff'?>">
								<td style="position: sticky;left:0;background: <?php echo ($i % 2) != 1 ? '#dae5f0' : '#ffffff'?>"><?php echo $wp_user->display_name; echo get_user_meta( $fahrer->user_id, 'type', true ) != null && get_user_meta( $fahrer->user_id, 'type', true ) != '-' ? ", " . get_user_meta( $fahrer->user_id, 'type', true ) : ""; ?></td>																							
								
								<td class="border_left"><?php echo $data->bus_mo != null && $data->bus_mo != 0 ? $data->bus_mo : "" ?></td>								
								<td><input class="day_val" type="text" name="mo_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->mo != null ? $data->mo : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="mo_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->mo_pause != null ? $data->mo_pause : "" ?>" readonly></td>								
								
								<td class="border_left"><input class="bus_mr" type="text" name="bus_di_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_di != null && $data->bus_di != 0 ? $data->bus_di : "" ?>" readonly></td>
								<td><input class="day_val" type="text" name="di_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->di != null ? $data->di : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="di_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->di_pause != null ? $data->di_pause : "" ?>" readonly></td>								
								
								<td class="border_left"><input class="bus_mr" type="text" name="bus_mi_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_mi != null && $data->bus_mi != 0 ? $data->bus_mi : "" ?>" readonly></td>
								<td><input class="day_val" type="text" name="mi_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->mi != null ? $data->mi : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="mi_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->mi_pause != null ? $data->mi_pause : "" ?>" readonly></td>
								
								
								<td class="border_left"><input class="bus_mr" type="text" name="bus_do_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_do != null && $data->bus_do != 0 ? $data->bus_do : "" ?>" readonly></td>
								<td><input class="day_val" type="text" name="do_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->do != null ? $data->do : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="do_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->do_pause != null ? $data->do_pause : "" ?>" readonly></td>
								
								<td class="border_left"><input class="bus_mr" type="text" name="bus_fr_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_fr != null && $data->bus_fr != 0 ? $data->bus_fr : "" ?>" readonly></td>
								<td><input class="day_val" type="text" name="fr_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->fr != null ? $data->fr : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="fr_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->fr_pause != null ? $data->fr_pause : "" ?>" readonly></td>
								
								<td class="border_left"><input class="bus_mr" type="text" name="bus_sa_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_sa != null && $data->bus_sa != 0 ? $data->bus_sa : "" ?>" readonly></td>
								<td><input class="day_val" type="text" name="sa_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->sa != null ? $data->sa : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="sa_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->sa_pause != null ? $data->sa_pause : "" ?>" readonly></td>
								
								<td class="border_left"><input class="bus_mr" type="text" name="bus_so_<?php echo $fahrer->user_id ?>" size="1" min="1" max="20" value="<?php echo $data->bus_so != null && $data->bus_so != 0 ? $data->bus_so : "" ?>" readonly></td>
								<td><input class="day_val" type="text" name="so_<?php echo $fahrer->user_id ?>" size="3" value="<?php echo $data->so != null ? $data->so : "" ?>" readonly></td>
								<td><input class="pause" type="text" name="so_pause_<?php echo $fahrer->user_id ?>" size="1" value="<?php echo $data->so_pause != null ? $data->so_pause : "" ?>" readonly></td>								
							</tr>
							<?php $i++; endforeach; ?>
						</tbody>
					</table>					
				</div>			
			</div>
		</div>
		<?php ob_start(); ?>
		<style>
		th{
			vertical-align:top; font-size: 11px; border: 1px solid black; padding:3px; font-weight: bold; background-color: #2d4154; color: #fff; white-space: nowrap;
		}
		td{
			font-size: 11px; border: 1px solid black; padding:3px; white-space: nowrap;
		}
		</style>
		<div class="row">
			<div class="col-sm-12 col-md-12" >
				<h3 style='text-align:center'>Einsatzplan - KW <?php echo $kw6 . ", " . $week6 ?></h3>
				<table class="table" style=' font-size: 12px; border-collapse:collapse; width: 100%'>
					<thead>
						<tr>							
							<th>KW <?php echo $kw6; ?></th>
							<th class="border_left">Bus</th>
							<th>MO<br><?php echo date('d.m.', strtotime("+0 day", strtotime($firstDay_kw6))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>DI<br><?php echo date('d.m.', strtotime("+1 day", strtotime($firstDay_kw6))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>MI<br><?php echo date('d.m.', strtotime("+2 day", strtotime($firstDay_kw6))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>DO<br><?php echo date('d.m.', strtotime("+3 day", strtotime($firstDay_kw6))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>FR<br><?php echo date('d.m.', strtotime("+4 day", strtotime($firstDay_kw6))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>SA<br><?php echo date('d.m.', strtotime("+5 day", strtotime($firstDay_kw6))) ?></th>
							<th>Pause</th>
							<th class="border_left">Bus</th>
							<th>SO<br><?php echo date('d.m.', strtotime("+6 day", strtotime($firstDay_kw6))) ?></th>
							<th>Pause</th>
							<th>Einsatzplan Bestätigung</th>
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
						?>
						<?php $wp_user = get_user_by('id', $fahrer->user_id); ?>
						<?php $data =  $db->getEinsatzplanByUserID($kw6, $year6, $fahrer->user_id) ?>
						<tr>
							<td><?php echo substr(get_user_meta($fahrer->user_id, 'first_name', true), 0, 1) . ". " . get_user_meta($fahrer->user_id, 'last_name', true); $wp_user->display_name; echo get_user_meta( $fahrer->user_id, 'type', true ) != null && get_user_meta( $fahrer->user_id, 'type', true ) != '-' ? ", " . get_user_meta( $fahrer->user_id, 'type', true ) : ""; ?></td>															
							<td class="border_left"><?php echo $data->bus_mo != null && $data->bus_mo != 0 ? $data->bus_mo : "" ?></td>	
							<td><?php echo $data->mo != null ? $data->mo : "" ?></td>
							<td><?php echo $data->mo_pause != null ? $data->mo_pause : "" ?></td>
							
							<td class="border_left"><?php echo $data->bus_di != null && $data->bus_di != 0 ? $data->bus_di : "" ?></td>
							<td><?php echo $data->di != null ? $data->di : "" ?></td>
							<td><?php echo $data->di_pause != null ? $data->di_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_mi != null && $data->bus_mi != 0 ? $data->bus_mi : "" ?></td>
							<td><?php echo $data->mi != null ? $data->mi : "" ?></td>
							<td><?php echo $data->mi_pause != null ? $data->mi_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_do != null && $data->bus_do != 0 ? $data->bus_do : "" ?></td>
							<td><?php echo $data->do != null ? $data->do : "" ?></td>
							<td><?php echo $data->do_pause != null ? $data->do_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_fr != null && $data->bus_fr != 0 ? $data->bus_fr : "" ?></td>
							<td><?php echo $data->fr != null ? $data->fr : "" ?></td>
							<td><?php echo $data->fr_pause != null ? $data->fr_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_sa != null && $data->bus_sa != 0 ? $data->bus_sa : "" ?></td>
							<td><?php echo $data->sa != null ? $data->sa : "" ?></td>
							<td><?php echo $data->sa_pause != null ? $data->sa_pause : "" ?></td>								
							
							<td class="border_left"><?php echo $data->bus_so != null && $data->bus_so != 0 ? $data->bus_so : "" ?></td>
							<td><?php echo $data->so != null ? $data->so : "" ?></td>
							<td><?php echo $data->so_pause != null ? $data->so_pause : "" ?></td>
							<td></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php $content = ob_get_clean(); ?>
		<?php $_SESSION['einsatzplan_6'] = $content; ?>
		<br>
		<?php endif; ?>
	</div>
</div>

<script>
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

				function validateInput(input) {
				  const inputValue = input.value;
				  
				  if (inputValue === "") {
					input.setCustomValidity("");
					return;
				  }
				  
				  const regex = /^(\d{2}:\d{2}-\d{2}:\d{2}|[FK])$/;

				  if (!regex.test(inputValue)) {
					input.setCustomValidity("Ungültiges Format. Bitte verwenden Sie STD:MIN-STD:MIN oder F/K.");
				  } else {
					input.setCustomValidity("");
				  }
				}
</script>