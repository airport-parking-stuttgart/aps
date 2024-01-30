<?php
$db = Database::getInstance();

$current_user = wp_get_current_user();

$users_fahrer = $db->getActivUser_einsatzplan();
//echo "<pre>"; print_r($users_fahrerAc); echo "</pre>";

$year1 = date('Y');
$year2 = date('Y');
$year3 = date('Y');
$kw1 = date('W');
$kw2 = date('W') + 1;
$kw3 = date('W') + 2;

$k = $kw1;
$timestamp_montag = strtotime("{$year1}-W{$k}");
$firstDay = date("d.m.Y", $timestamp_montag);
$lastDay = date('d.m.Y', strtotime("+6 day", strtotime($firstDay)));
$week1 = $firstDay . " - " . $lastDay;

$k = $kw2 < 10 ? "0" . $kw2 : $kw2;
$timestamp_montag = strtotime("{$year1}-W{$k}");
$firstDay = date("d.m.Y", $timestamp_montag);
$lastDay = date('d.m.Y', strtotime("+6 day", strtotime($firstDay)));
$week2 = $firstDay . " - " . $lastDay;

$k = $kw3 < 10 ? "0" . $kw3 : $kw3;
$timestamp_montag = strtotime("{$year1}-W{$k}");
$firstDay = date("d.m.Y", $timestamp_montag);
$lastDay = date('d.m.Y', strtotime("+6 day", strtotime($firstDay)));
$week3 = $firstDay . " - " . $lastDay;

$date = new DateTime;
$date->setISODate(date('Y'), 53);
$weeks = ($date->format("W") === "53" ? 53 : 52);

if($kw1 >= $weeks){
	$year2++;
	$year3++;
	$kw2 = 1;
	$kw3 = 2;
}

if($kw2 >= $weeks){
	$year3++;
	$kw3 = 1;
}

if(isset($_POST)){
	if($_POST['btn'] == $kw1){
		$kw = $_POST['kw_1'];
		unset($_POST['btn']);
		unset($_POST['kw_1']);
		//$data = $_POST;
		
		$db->delete_einsatzplan($kw, $year1);
		//$db->add_einsatzplan($table, $kw, $data);
		
		foreach($users_fahrer as $fahrer){
			
			$mo_pause = $_POST['mo_'.$fahrer->user_id];
			if(($mo_pause != null && $mo_pause != "") && str_contains($mo_pause, '-')) {
				$times = explode("-", $mo_pause);
				$diff_times = number_format($times[1] - $times[0], 1, ".", ".");
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
				$diff_times = number_format($times[1] - $times[0], 1, ".", ".");
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
				$diff_times = number_format($times[1] - $times[0], 1, ".", ".");
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
				$diff_times = number_format($times[1] - $times[0], 1, ".", ".");
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
				$diff_times = number_format($times[1] - $times[0], 1, ".", ".");
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
				$times = explode("-", $fr_pause);
				$diff_times = number_format($times[1] - $times[0], 1, ".", ".");
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
				$times = explode("-", $fr_pause);
				$diff_times = number_format($times[1] - $times[0], 1, ".", ".");
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
	}
	elseif($_POST['btn'] == $kw2){
		$kw = $_POST['kw_2'];
		unset($_POST['btn']);
		unset($_POST['kw_2']);
		//$data = $_POST;
		
		$db->delete_einsatzplan($kw, $year2);
		//$db->add_einsatzplan($table, $kw, $data);
		
		foreach($users_fahrer as $fahrer){
			
			$mo_pause = $_POST['mo_'.$fahrer->user_id];
			if(($mo_pause != null && $mo_pause != "") && str_contains($mo_pause, '-')) {
				$times = explode("-", $mo_pause);
				$diff_times = number_format($times[1] - $times[0], 1, ".", ".");
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
				$diff_times = number_format($times[1] - $times[0], 1, ".", ".");
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
				$diff_times = number_format($times[1] - $times[0], 1, ".", ".");
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
				$diff_times = number_format($times[1] - $times[0], 1, ".", ".");
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
				$diff_times = number_format($times[1] - $times[0], 1, ".", ".");
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
				$times = explode("-", $fr_pause);
				$diff_times = number_format($times[1] - $times[0], 1, ".", ".");
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
				$times = explode("-", $fr_pause);
				$diff_times = number_format($times[1] - $times[0], 1, ".", ".");
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
	}
	elseif($_POST['btn'] == $kw3){
		$kw = $_POST['kw_3'];
		unset($_POST['btn']);
		unset($_POST['kw_3']);
		
		$db->delete_einsatzplan($kw, $year3);
		//$db->add_einsatzplan($table, $kw, $data);
		
		foreach($users_fahrer as $fahrer){
			
			$mo_pause = $_POST['mo_'.$fahrer->user_id];
			if(($mo_pause != null && $mo_pause != "") && str_contains($mo_pause, '-')) {
				$times = explode("-", $mo_pause);
				$diff_times = number_format($times[1] - $times[0], 1, ".", ".");
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
				$diff_times = number_format($times[1] - $times[0], 1, ".", ".");
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
				$diff_times = number_format($times[1] - $times[0], 1, ".", ".");
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
				$diff_times = number_format($times[1] - $times[0], 1, ".", ".");
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
				$diff_times = number_format($times[1] - $times[0], 1, ".", ".");
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
				$times = explode("-", $fr_pause);
				$diff_times = number_format($times[1] - $times[0], 1, ".", ".");
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
				$times = explode("-", $fr_pause);
				$diff_times = number_format($times[1] - $times[0], 1, ".", ".");
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
			$db->add_einsatzplan($kw, $year3, $data[$fahrer->user_id]);
		}
	}
	elseif($_POST['btn_del'] == $kw1){
		$db->delete_einsatzplan($kw1, $year1);
	}	
	elseif($_POST['btn_del'] == $kw2){
		$db->delete_einsatzplan($kw2, $year2);
	}
		
	elseif($_POST['btn_del'] == $kw3){
		$db->delete_einsatzplan($kw3, $year3);	
	}
		
	
}


//echo "<pre>"; print_r($t); echo "</pre>";


?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>  
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>  
<style>
.color_line {
  background: #ffcc99;
}
.color_ad{
	background: #00ccff !important;
}
.color_office{
	background: #00ff99 !important;
}
.color_kordi{
	background: #ffff99 !important;
}
.border_left{
	border-left: 1px solid;
}

th, td {white-space: nowrap;}

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

</style>
<div class="page container-fluid <?php echo $_GET['page'] ?>">

    <div class="page-title itweb_adminpage_head">
		<h3>Einsatzplan</h3>
	</div>
	<div class="page-body">
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . "?page=einsatzplan"; ?>">
			<div class="table-wrapper">	
				<table class="table table-sm" id="table1">
					<thead>
						<tr>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<th>KW <input type="text" name="kw_1" size="3" min="1" max="52" value="<?php echo $kw1; ?>" readonly><br><span><?php echo $week1 ?></span></th>
							<?php else:?>
							<th>KW <?php echo $kw1; ?><br><span><?php echo $week1 ?></span></th>
							<?php endif; ?>
							<th class="border_left"></th>
							<th>MO</th>
							<th>Pause</th>
							<th class="border_left"></th>
							<th>DI</th>
							<th>Pause</th>
							<th class="border_left"></th>
							<th>MI</th>
							<th>Pause</th>
							<th class="border_left"></th>
							<th>DO</th>
							<th>Pause</th>
							<th class="border_left"></th>
							<th>FR</th>
							<th>Pause</th>
							<th class="border_left"></th>
							<th>SA</th>
							<th>Pause</th>
							<th class="border_left"></th>
							<th>SO</th>
							<th>Pause</th>
							<th>Soll</th>
							<th>Geplant</th>
						</tr>
					</thead>
					<tbody class="row_position_table1">
					<?php $i = 1; foreach($users_fahrer as $fahrer): ?>
						<?php $wp_user = get_user_by('id', $fahrer->user_id); ?>
						<?php $data =  $db->getEinsatzplanByUserID($kw1, $year1, $fahrer->user_id) ?>
						<?php if($fahrer->user_id == 7 || $fahrer->user_id == 8)
								$linaColor = 'color_ad';
							elseif($fahrer->user_id == 10 || $fahrer->user_id == 68 || $fahrer->user_id == 287)
								$linaColor = 'color_office';
							elseif($fahrer->role == 'koordinator')
								$linaColor = 'color_kordi';
							else
								$linaColor = '';
							if($current_user->user_login != 'sergej' && $current_user->user_login != 'aras' && $current_user->user_login != 'cakir' && $current_user->user_login != 'soner' && $current_user->user_login != 'cam' && $current_user->user_login != 'choudhary'){
								if($fahrer->user_id != get_current_user_id())
									continue;
							}
							$diff_times[$fahrer->user_id] = 0;
							
						?>
						<input type="hidden" name="fahrer_id_<?php echo $fahrer->user_id ?>" value="<?php echo $fahrer->user_id ?>">
						<tr class="<?php echo ($i % 2) != 1 ? "color_line" : ""?> <?php echo $linaColor ?>" id="<?php echo $fahrer->user_id ?>">
							<td><?php echo $wp_user->display_name; echo get_user_meta( $fahrer->user_id, 'type', true ) != null && get_user_meta( $fahrer->user_id, 'type', true ) != '-' ? ", " . get_user_meta( $fahrer->user_id, 'type', true ) : ""; ?></td>
							
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td class="border_left"><input type="number" name="bus_mo_<?php echo $fahrer->user_id ?>" size="3" min="1" max="20" value="<?php echo $data->bus_mo != null && $data->bus_mo != 0 ? $data->bus_mo : "" ?>"></td>
							<?php else:?>
							<td class="border_left"><?php echo $data->bus_mo != null && $data->bus_mo != 0 ? $data->bus_mo : "" ?></td>
							<?php endif; ?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="mo_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->mo != null ? $data->mo : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->mo != null ? $data->mo : "" ?></td>
							<?php endif; ?>
							<?php
								$times = explode("-", $data->mo);
								$diff_times[$fahrer->user_id] += number_format($times[1] - $times[0], 1, ".", ".");
							?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="mo_pause_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->mo_pause != null ? $data->mo_pause : "" ?>" readonly></td>
							<?php else:?>
							<td><?php echo $data->mo_pause != null ? $data->mo_pause : "" ?></td>
							<?php endif; ?>
							
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td class="border_left"><input type="number" name="bus_di_<?php echo $fahrer->user_id ?>" size="3" min="1" max="20" value="<?php echo $data->bus_di != null && $data->bus_di != 0 ? $data->bus_di : "" ?>"></td>
							<?php else:?>
							<td class="border_left"><?php echo $data->bus_di != null && $data->bus_di != 0 ? $data->bus_di : "" ?></td>
							<?php endif; ?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="di_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->di != null ? $data->di : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->di != null ? $data->di : "" ?></td>
							<?php endif; ?>
							<?php
								$times = explode("-", $data->di);
								$diff_times[$fahrer->user_id] += number_format($times[1] - $times[0], 1, ".", ".");
							?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="di_pause_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->di_pause != null ? $data->di_pause : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->di_pause != null ? $data->di_pause : "" ?></td>
							<?php endif; ?>
							
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td class="border_left"><input type="number" name="bus_mi_<?php echo $fahrer->user_id ?>" size="3" min="1" max="20" value="<?php echo $data->bus_mi != null && $data->bus_mi != 0 ? $data->bus_mi : "" ?>"></td>
							<?php else:?>
							<td class="border_left"><?php echo $data->bus_mi != null && $data->bus_mi != 0 ? $data->bus_mi : "" ?></td>
							<?php endif; ?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="mi_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->mi != null ? $data->mi : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->mi != null ? $data->mi : "" ?></td>
							<?php endif; ?>
							<?php
								$times = explode("-", $data->mi);
								$diff_times[$fahrer->user_id] += number_format($times[1] - $times[0], 1, ".", ".");
							?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="mi_pause_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->mi_pause != null ? $data->mi_pause : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->mi_pause != null ? $data->mi_pause : "" ?></td>
							<?php endif; ?>
							
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td class="border_left"><input type="number" name="bus_do_<?php echo $fahrer->user_id ?>" size="3" min="1" max="20" value="<?php echo $data->bus_do != null && $data->bus_do != 0 ? $data->bus_do : "" ?>"></td>
							<?php else:?>
							<td class="border_left"><?php echo $data->bus_do != null && $data->bus_do != 0 ? $data->bus_do : "" ?></td>
							<?php endif; ?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="do_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->do != null ? $data->do : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->do != null ? $data->do : "" ?></td>
							<?php endif; ?>
							<?php
								$times = explode("-", $data->do);
								$diff_times[$fahrer->user_id] += number_format($times[1] - $times[0], 1, ".", ".");
							?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="do_pause_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->do_pause != null ? $data->do_pause : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->do_pause != null ? $data->do_pause : "" ?></td>
							<?php endif; ?>
							
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td class="border_left"><input type="number" name="bus_fr_<?php echo $fahrer->user_id ?>" size="3" min="1" max="20" value="<?php echo $data->bus_fr != null && $data->bus_fr != 0 ? $data->bus_fr : "" ?>"></td>
							<?php else:?>
							<td class="border_left"><?php echo $data->bus_fr != null && $data->bus_fr != 0 ? $data->bus_fr : "" ?></td>
							<?php endif; ?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="fr_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->fr != null ? $data->fr : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->fr != null ? $data->fr : "" ?></td>
							<?php endif; ?>
							<?php
								$times = explode("-", $data->fr);
								$diff_times[$fahrer->user_id] += number_format($times[1] - $times[0], 1, ".", ".");
							?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="fr_pause_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->fr_pause != null ? $data->fr_pause : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->fr_pause != null ? $data->fr_pause : "" ?></td>
							<?php endif; ?>
							
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td class="border_left"><input type="number" name="bus_sa_<?php echo $fahrer->user_id ?>" size="3" min="1" max="20" value="<?php echo $data->bus_sa != null && $data->bus_sa != 0 ? $data->bus_sa : "" ?>"></td>
							<?php else:?>
							<td class="border_left"><?php echo $data->bus_sa != null && $data->bus_sa != 0 ? $data->bus_sa : "" ?></td>
							<?php endif; ?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="sa_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->sa != null ? $data->sa : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->sa != null ? $data->sa : "" ?></td>
							<?php endif; ?>
							<?php
								$times = explode("-", $data->sa);
								$diff_times[$fahrer->user_id] += number_format($times[1] - $times[0], 1, ".", ".");
							?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="sa_pause_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->sa_pause != null ? $data->sa_pause : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->sa_pause != null ? $data->sa_pause : "" ?></td>
							<?php endif; ?>
							
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td class="border_left"><input type="number" name="bus_so_<?php echo $fahrer->user_id ?>" size="3" min="1" max="20" value="<?php echo $data->bus_so != null && $data->bus_so != 0 ? $data->bus_so : "" ?>"></td>
							<?php else:?>
							<td class="border_left"><?php echo $data->bus_so != null && $data->bus_so != 0 ? $data->bus_so : "" ?></td>
							<?php endif; ?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="so_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->so != null ? $data->so : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->so != null ? $data->so : "" ?></td>
							<?php endif; ?>
							<?php
								$times = explode("-", $data->so);
								$diff_times[$fahrer->user_id] += number_format($times[1] - $times[0], 1, ".", ".");
							?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="so_pause_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->so_pause != null ? $data->so_pause : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->so_pause != null ? $data->so_pause : "" ?></td>
							<?php endif; ?>
							<td><?php echo get_user_meta( $fahrer->user_id, 'std_mon', true ) ?></td>
							<td><?php echo $diff_times[$fahrer->user_id] ?></td>
						</tr>
						<?php $i++; endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
			<button class="btn btn-primary" type="submit" name="btn" value="<?php echo $kw1; ?>">Speichern</button>
			<button class="btn btn-primary" type="submit" name="btn_del" value="<?php echo $kw1; ?>">Daten löschen</button>
			<?php endif; ?>
		</form>
		<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
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
		<br><br>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . "?page=einsatzplan"; ?>">
			<div class="table-wrapper">	
				<table class="table table-sm">
					<thead>
						<tr>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<th>KW <input type="text" name="kw_2" size="3" min="1" max="52" value="<?php echo $kw2; ?>" readonly><br><span><?php echo $week2 ?></span></th>
							<?php else:?>
							<th>KW <?php echo $kw2; ?><br><span><?php echo $week2 ?></span></th>
							<?php endif; ?>
							<th class="border_left"></th>
							<th>MO</th>
							<th>Pause</th>
							<th class="border_left"></th>
							<th>DI</th>
							<th>Pause</th>
							<th class="border_left"></th>
							<th>MI</th>
							<th>Pause</th>
							<th class="border_left"></th>
							<th>DO</th>
							<th>Pause</th>
							<th class="border_left"></th>
							<th>FR</th>
							<th>Pause</th>
							<th class="border_left"></th>
							<th>SA</th>
							<th>Pause</th>
							<th class="border_left"></th>
							<th>SO</th>
							<th>Pause</th>
							<th>Soll</th>
							<th>Geplant</th>
						</tr>
					</thead>
					<tbody>
					<?php $i = 1; foreach($users_fahrer as $fahrer): ?>
						<?php $wp_user = get_user_by('id', $fahrer->user_id); ?>
						<?php $data =  $db->getEinsatzplanByUserID($kw2, $year2, $fahrer->user_id) ?>
						<?php if($fahrer->user_id == 7 || $fahrer->user_id == 8)
								$linaColor = 'color_ad';
							elseif($fahrer->user_id == 10 || $fahrer->user_id == 68 || $fahrer->user_id == 287)
								$linaColor = 'color_office';
							elseif($fahrer->role == 'koordinator')
								$linaColor = 'color_kordi';
							else
								$linaColor = '';
							if($current_user->user_login != 'sergej' && $current_user->user_login != 'aras' && $current_user->user_login != 'cakir' && $current_user->user_login != 'soner' && $current_user->user_login != 'cam' && $current_user->user_login != 'choudhary'){
								if($fahrer->user_id != get_current_user_id())
									continue;
							}
							$diff_times[$fahrer->user_id] = 0;
						?>
						<input type="hidden" name="fahrer_id_<?php echo $fahrer->user_id ?>" value="<?php echo $fahrer->user_id ?>">
						<tr class="<?php echo ($i % 2) != 1 ? "color_line" : ""?> <?php echo $linaColor ?>">
							<td><?php echo $wp_user->display_name; echo get_user_meta( $fahrer->user_id, 'type', true ) != null && get_user_meta( $fahrer->user_id, 'type', true ) != '-' ? ", " . get_user_meta( $fahrer->user_id, 'type', true ) : ""; ?></td>
							
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td class="border_left"><input type="number" name="bus_mo_<?php echo $fahrer->user_id ?>" size="3" min="1" max="20" value="<?php echo $data->bus_mo != null && $data->bus_mo != 0 ? $data->bus_mo : "" ?>"></td>
							<?php else:?>						
							<td class="border_left"><?php echo $data->bus_mo != null && $data->bus_mo != 0 ? $data->bus_mo : "" ?></td>
							<?php endif; ?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="mo_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->mo != null ? $data->mo : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->mo != null ? $data->mo : "" ?></td>
							<?php endif; ?>
							<?php
								$times = explode("-", $data->mo);
								$diff_times[$fahrer->user_id] += number_format($times[1] - $times[0], 1, ".", ".");
							?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="mo_pause_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->mo_pause != null ? $data->mo_pause : "" ?>" readonly></td>
							<?php else:?>
							<td><?php echo $data->mo_pause != null ? $data->mo_pause : "" ?></td>
							<?php endif; ?>
							
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td class="border_left"><input type="number" name="bus_di_<?php echo $fahrer->user_id ?>" size="3" min="1" max="20" value="<?php echo $data->bus_di != null && $data->bus_di != 0 ? $data->bus_di : "" ?>"></td>
							<?php else:?>
							<td class="border_left"><?php echo $data->bus_di != null && $data->bus_di != 0 ? $data->bus_di : "" ?></td>
							<?php endif; ?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="di_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->di != null ? $data->di : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->di != null ? $data->di : "" ?></td>
							<?php endif; ?>
							<?php
								$times = explode("-", $data->di);
								$diff_times[$fahrer->user_id] += number_format($times[1] - $times[0], 1, ".", ".");
							?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="di_pause_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->di_pause != null ? $data->di_pause : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->di_pause != null ? $data->di_pause : "" ?></td>
							<?php endif; ?>
							
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td class="border_left"><input type="number" name="bus_mi_<?php echo $fahrer->user_id ?>" size="3" min="1" max="20" value="<?php echo $data->bus_mi != null && $data->bus_mi != 0 ? $data->bus_mi : "" ?>"></td>
							<?php else:?>
							<td class="border_left"><?php echo $data->bus_mi != null && $data->bus_mi != 0 ? $data->bus_mi : "" ?></td>
							<?php endif; ?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="mi_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->mi != null ? $data->mi : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->mi != null ? $data->mi : "" ?></td>
							<?php endif; ?>
							<?php
								$times = explode("-", $data->mi);
								$diff_times[$fahrer->user_id] += number_format($times[1] - $times[0], 1, ".", ".");
							?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="mi_pause_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->mi_pause != null ? $data->mi_pause : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->mi_pause != null ? $data->mi_pause : "" ?></td>
							<?php endif; ?>
							
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td class="border_left"><input type="number" name="bus_do_<?php echo $fahrer->user_id ?>" size="3" min="1" max="20" value="<?php echo $data->bus_do != null && $data->bus_do != 0 ? $data->bus_do : "" ?>"></td>
							<?php else:?>
							<td class="border_left"><?php echo $data->bus_do != null && $data->bus_do != 0 ? $data->bus_do : "" ?></td>
							<?php endif; ?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="do_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->do != null ? $data->do : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->do != null ? $data->do : "" ?></td>
							<?php endif; ?>
							<?php
								$times = explode("-", $data->do);
								$diff_times[$fahrer->user_id] += number_format($times[1] - $times[0], 1, ".", ".");
							?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="do_pause_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->do_pause != null ? $data->do_pause : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->do_pause != null ? $data->do_pause : "" ?></td>
							<?php endif; ?>
							
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td class="border_left"><input type="number" name="bus_fr_<?php echo $fahrer->user_id ?>" size="3" min="1" max="20" value="<?php echo $data->bus_fr != null && $data->bus_fr != 0 ? $data->bus_fr : "" ?>"></td>
							<?php else:?>
							<td class="border_left"><?php echo $data->bus_fr != null && $data->bus_fr != 0 ? $data->bus_fr : "" ?></td>
							<?php endif; ?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="fr_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->fr != null ? $data->fr : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->fr != null ? $data->fr : "" ?></td>
							<?php endif; ?>
							<?php
								$times = explode("-", $data->fr);
								$diff_times[$fahrer->user_id] += number_format($times[1] - $times[0], 1, ".", ".");
							?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="fr_pause_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->fr_pause != null ? $data->fr_pause : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->fr_pause != null ? $data->fr_pause : "" ?></td>
							<?php endif; ?>
							
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td class="border_left"><input type="number" name="bus_sa_<?php echo $fahrer->user_id ?>" size="3" min="1" max="20" value="<?php echo $data->bus_sa != null && $data->bus_sa != 0 ? $data->bus_sa : "" ?>"></td>
							<?php else:?>
							<td class="border_left"><?php echo $data->bus_sa != null && $data->bus_sa != 0 ? $data->bus_sa : "" ?></td>
							<?php endif; ?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="sa_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->sa != null ? $data->sa : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->sa != null ? $data->sa : "" ?></td>
							<?php endif; ?>
							<?php
								$times = explode("-", $data->sa);
								$diff_times[$fahrer->user_id] += number_format($times[1] - $times[0], 1, ".", ".");
							?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="sa_pause_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->sa_pause != null ? $data->sa_pause : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->sa_pause != null ? $data->sa_pause : "" ?></td>
							<?php endif; ?>
							
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td class="border_left"><input type="number" name="bus_so_<?php echo $fahrer->user_id ?>" size="3" min="1" max="20" value="<?php echo $data->bus_so != null && $data->bus_so != 0 ? $data->bus_so : "" ?>"></td>
							<?php else:?>
							<td class="border_left"><?php echo $data->bus_so != null && $data->bus_so != 0 ? $data->bus_so : "" ?></td>
							<?php endif; ?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="so_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->so != null ? $data->so : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->so != null ? $data->so : "" ?></td>
							<?php endif; ?>
							<?php
								$times = explode("-", $data->so);
								$diff_times[$fahrer->user_id] += number_format($times[1] - $times[0], 1, ".", ".");
							?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="so_pause_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->so_pause != null ? $data->so_pause : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->so_pause != null ? $data->so_pause : "" ?></td>
							<?php endif; ?>
							<td><?php echo get_user_meta( $fahrer->user_id, 'std_mon', true ) ?></td>
							<td><?php echo $diff_times[$fahrer->user_id] ?></td>
						</tr>
						<?php $i++; endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
			<button class="btn btn-primary" type="submit" name="btn" value="<?php echo $kw2; ?>">Speichern</button>
			<button class="btn btn-primary" type="submit" name="btn_del" value="<?php echo $kw2; ?>">Daten löschen</button>
			<?php endif; ?>
		</form>
		<br><br>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . "?page=einsatzplan"; ?>">
			<div class="table-wrapper">
				<table class="table table-sm">
					<thead>
						<tr>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<th>KW <input type="text" name="kw_3" size="3" min="1" max="52" value="<?php echo $kw3; ?>" readonly><br><span><?php echo $week3 ?></span></th>
							<?php else:?>
							<th>KW <?php echo $kw3; ?><br><span><?php echo $week3 ?></span></th>
							<?php endif; ?>
							<th class="border_left"></th>
							<th>MO</th>
							<th>Pause</th>
							<th class="border_left"></th>
							<th>DI</th>
							<th>Pause</th>
							<th class="border_left"></th>
							<th>MI</th>
							<th>Pause</th>
							<th class="border_left"></th>
							<th>DO</th>
							<th>Pause</th>
							<th class="border_left"></th>
							<th>FR</th>
							<th>Pause</th>
							<th class="border_left"></th>
							<th>SA</th>
							<th>Pause</th>
							<th class="border_left"></th>
							<th>SO</th>
							<th>Pause</th>
							<th>Soll</th>
							<th>Geplant</th>
						</tr>
					</thead>
					<tbody>
					<?php $i = 1; foreach($users_fahrer as $fahrer): ?>
						<?php $wp_user = get_user_by('id', $fahrer->user_id); ?>
						<?php $data =  $db->getEinsatzplanByUserID($kw3, $year3, $fahrer->user_id) ?>
						<?php if($fahrer->user_id == 7 || $fahrer->user_id == 8)
								$linaColor = 'color_ad';
							elseif($fahrer->user_id == 10 || $fahrer->user_id == 68 || $fahrer->user_id == 287)
								$linaColor = 'color_office';
							elseif($fahrer->role == 'koordinator')
								$linaColor = 'color_kordi';
							else
								$linaColor = '';
							if($current_user->user_login != 'sergej' && $current_user->user_login != 'aras' && $current_user->user_login != 'cakir' && $current_user->user_login != 'soner' && $current_user->user_login != 'cam' && $current_user->user_login != 'choudhary'){
								if($fahrer->user_id != get_current_user_id())
									continue;
							}
							$diff_times[$fahrer->user_id] = 0;
						?>
						<input type="hidden" name="fahrer_id_<?php echo $fahrer->user_id ?>" value="<?php echo $fahrer->user_id ?>">
						<tr class="<?php echo ($i % 2) != 1 ? "color_line" : ""?> <?php echo $linaColor ?>">
							<td><?php echo $wp_user->display_name; echo get_user_meta( $fahrer->user_id, 'type', true ) != null && get_user_meta( $fahrer->user_id, 'type', true ) != '-' ? ", " . get_user_meta( $fahrer->user_id, 'type', true ) : ""; ?></td>
							
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td class="border_left"><input type="number" name="bus_mo_<?php echo $fahrer->user_id ?>" size="3" min="1" max="20" value="<?php echo $data->bus_mo != null && $data->bus_mo != 0 ? $data->bus_mo : "" ?>"></td>
							<?php else:?>
							<td class="border_left"><?php echo $data->bus_mo != null && $data->bus_mo != 0 ? $data->bus_mo : "" ?></td>
							<?php endif; ?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="mo_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->mo != null ? $data->mo : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->mo != null ? $data->mo : "" ?></td>
							<?php endif; ?>
							<?php
								$times = explode("-", $data->mo);
								$diff_times[$fahrer->user_id] += number_format($times[1] - $times[0], 1, ".", ".");
							?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="mo_pause_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->mo_pause != null ? $data->mo_pause : "" ?>" readonly></td>
							<?php else:?>
							<td><?php echo $data->mo_pause != null ? $data->mo_pause : "" ?></td>
							<?php endif; ?>
							
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td class="border_left"><input type="number" name="bus_di_<?php echo $fahrer->user_id ?>" size="3" min="1" max="20" value="<?php echo $data->bus_di != null && $data->bus_di != 0 ? $data->bus_di : "" ?>"></td>
							<?php else:?>
							<td class="border_left"><?php echo $data->bus_di != null && $data->bus_di != 0 ? $data->bus_di : "" ?></td>
							<?php endif; ?>						
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="di_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->di != null ? $data->di : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->di != null ? $data->di : "" ?></td>
							<?php endif; ?>
							<?php
								$times = explode("-", $data->di);
								$diff_times[$fahrer->user_id] += number_format($times[1] - $times[0], 1, ".", ".");
							?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="di_pause_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->di_pause != null ? $data->di_pause : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->di_pause != null ? $data->di_pause : "" ?></td>
							<?php endif; ?>
							
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td class="border_left"><input type="number" name="bus_mi_<?php echo $fahrer->user_id ?>" size="3" min="1" max="20" value="<?php echo $data->bus_mi != null && $data->bus_mi != 0 ? $data->bus_mi : "" ?>"></td>
							<?php else:?>
							<td class="border_left"><?php echo $data->bus_mi != null && $data->bus_mi != 0 ? $data->bus_mi : "" ?></td>
							<?php endif; ?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="mi_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->mi != null ? $data->mi : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->mi != null ? $data->mi : "" ?></td>
							<?php endif; ?>
							<?php
								$times = explode("-", $data->mi);
								$diff_times[$fahrer->user_id] += number_format($times[1] - $times[0], 1, ".", ".");
							?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="mi_pause_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->mi_pause != null ? $data->mi_pause : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->mi_pause != null ? $data->mi_pause : "" ?></td>
							<?php endif; ?>
							
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td class="border_left"><input type="number" name="bus_do_<?php echo $fahrer->user_id ?>" size="3" min="1" max="20" value="<?php echo $data->bus_do != null && $data->bus_do != 0 ? $data->bus_do : "" ?>"></td>
							<?php else:?>
							<td class="border_left"><?php echo $data->bus_do != null && $data->bus_do != 0 ? $data->bus_do : "" ?></td>
							<?php endif; ?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="do_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->do != null ? $data->do : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->do != null ? $data->do : "" ?></td>
							<?php endif; ?>
							<?php
								$times = explode("-", $data->do);
								$diff_times[$fahrer->user_id] += number_format($times[1] - $times[0], 1, ".", ".");
							?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="do_pause_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->do_pause != null ? $data->do_pause : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->do_pause != null ? $data->do_pause : "" ?></td>
							<?php endif; ?>
							
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td class="border_left"><input type="number" name="bus_fr_<?php echo $fahrer->user_id ?>" size="3" min="1" max="20" value="<?php echo $data->bus_fr != null && $data->bus_fr != 0 ? $data->bus_fr : "" ?>"></td>
							<?php else:?>
							<td class="border_left"><?php echo $data->bus_fr != null && $data->bus_fr != 0 ? $data->bus_fr : "" ?></td>
							<?php endif; ?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="fr_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->fr != null ? $data->fr : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->fr != null ? $data->fr : "" ?></td>
							<?php endif; ?>
							<?php
								$times = explode("-", $data->fr);
								$diff_times[$fahrer->user_id] += number_format($times[1] - $times[0], 1, ".", ".");
							?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="fr_pause_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->fr_pause != null ? $data->fr_pause : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->fr_pause != null ? $data->fr_pause : "" ?></td>
							<?php endif; ?>
							
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td class="border_left"><input type="number" name="bus_sa_<?php echo $fahrer->user_id ?>" size="3" min="1" max="20" value="<?php echo $data->bus_sa != null && $data->bus_sa != 0 ? $data->bus_sa : "" ?>"></td>
							<?php else:?>
							<td class="border_left"><?php echo $data->bus_sa != null && $data->bus_sa != 0 ? $data->bus_sa : "" ?></td>
							<?php endif; ?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="sa_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->sa != null ? $data->sa : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->sa != null ? $data->sa : "" ?></td>
							<?php endif; ?>
							<?php
								$times = explode("-", $data->sa);
								$diff_times[$fahrer->user_id] += number_format($times[1] - $times[0], 1, ".", ".");
							?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="sa_pause_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->sa_pause != null ? $data->sa_pause : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->sa_pause != null ? $data->sa_pause : "" ?></td>
							<?php endif; ?>
							
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td class="border_left"><input type="number" name="bus_so_<?php echo $fahrer->user_id ?>" size="3" min="1" max="20" value="<?php echo $data->bus_so != null && $data->bus_so != 0 ? $data->bus_so : "" ?>"></td>
							<?php else:?>
							<td class="border_left"><?php echo $data->bus_so != null && $data->bus_so != 0 ? $data->bus_so : "" ?></td>
							<?php endif; ?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="so_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->so != null ? $data->so : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->so != null ? $data->so : "" ?></td>
							<?php endif; ?>
							<?php
								$times = explode("-", $data->so);
								$diff_times[$fahrer->user_id] += number_format($times[1] - $times[0], 1, ".", ".");
							?>
							<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
							<td><input type="text" name="so_pause_<?php echo $fahrer->user_id ?>" size="7" value="<?php echo $data->so_pause != null ? $data->so_pause : "" ?>"></td>
							<?php else:?>
							<td><?php echo $data->so_pause != null ? $data->so_pause : "" ?></td>
							<?php endif; ?>
							<td><?php echo get_user_meta( $fahrer->user_id, 'std_mon', true ) ?></td>
							<td><?php echo $diff_times[$fahrer->user_id] ?></td>
						</tr>
						<?php $i++; endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
			<button class="btn btn-primary" type="submit" name="btn" value="<?php echo $kw3; ?>">Speichern</button>
			<button class="btn btn-primary" type="submit" name="btn_del" value=" <?php echo $kw3; ?>">Daten löschen</button>
			<?php endif; ?>
		</form>
	</div>
</div>

<script>

</script>