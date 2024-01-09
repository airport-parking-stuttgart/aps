<?php

/**
 * Template Name: Curl-Page
 */


if($_GET["request"] == 'apm' && $_GET['pw'] == 'pzf_req54894135'){
	$db = Database::getInstance();
	$db->saveOrderFomPZF($_POST);
}

if(isset($_GET['request']) && $_GET['request'] == 'pzf_add_booking' && $_GET['pw'] == 'pzf_req54894135'){
	$db = Database::getInstance();
	$db->getOrderFomPZF($_POST);
}

if($_GET["request"] == 'apm' && $_GET['pw'] == 'apg_req54894136' && $_GET['add'] == 1){
	$db = Database::getInstance();
	$db->saveOrderFomAPG($_POST, $_GET['lot']);
}

if($_GET["request"] == 'apm' && $_GET['pw'] == 'apg_req54894136' && $_GET['c'] == 1){
	$order_id = $wpdb->get_row("SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'token' and meta_value = '" . $_POST['token'] . "'");
$order = new WC_Order($order_id->post_id);
        if (!empty($order)) {
            $order->update_status( 'cancelled' );
        }
}

if($_GET["request"] == 'apm' && $_GET['pw'] == 'apg_req54894136' && $_GET['update'] == 1){
        $db = Database::getInstance();
	$db->updateOrderFomAPG($_POST);
}

if($_GET["request"] == 'apm' && $_GET['pw'] == 'apg_req54894136' && $_GET['p'] == 1){
        $db = Database::getInstance();
	$db->cancelToProcessingFomAPG($_POST);
}


if($_GET["request"] == 'apm' && $_GET['pw'] == 'apg_req54894136' && $_GET['get_cont'] == 1){
	$date[0] = $_GET['datefrom'];
	$date[1] = $_GET['dateto'];
	$db = Database::getInstance();
	$data_con = $db->getAllContingent($date);
	$dataLot = $db->sendContingentToAPG($_GET, true);
	
	$data = array_merge( $data_con, $dataLot );
	
	$data = json_encode($data, true);
	print_r($data);
}

if($_GET["request"] == 'apm' && $_GET['pw'] == 'pzf_req54894135' && $_GET['get_cont'] == 1){
	$date[0] = $_GET['datefrom'];
	$date[1] = $_GET['dateto'];
	$db = Database::getInstance();
	$data_con = $db->getAllContingentPZF($date);
	$dataLot = $db->sendContingentToPZF($_GET, true);
	
	$data = array_merge( $data_con, $dataLot );
	
	$data = json_encode($data, true);
	print_r($data);
}


if($_GET["request"] == 'frid' && $_GET['pw'] == 'apg_req54894135'){
	
	if($_POST['data']['cmd'] == null){
		$db = Database::getInstance();
		if($_POST['data']['UID']){
			$user_id = $db->getUserByFRID($_POST['data']['UID']);
			$name = get_user_meta( $user_id->user_id, 'first_name', true ) . " " . get_user_meta( $user_id->user_id, 'last_name', true );
		}
		
		$rf_id = $_POST['data']['UID'];	
		$last_stempel = $db->getLastStempel($user_id->user_id, $rf_id);
		if($user_id->user_id){	
			if($last_stempel->state == 'in' || ($last_stempel->state == 'out' && $last_stempel->date != date('Y-m-d')) || $last_stempel == null){
				if($last_stempel->time_in != "" && $last_stempel->time_out == null){
					
					$check_in = $last_stempel->time_in;
					$check_out = $_POST['time'];
					
					if(strtotime($check_in) > strtotime($check_out))
						$nex_day = 24;
					else
						$nex_day = 0;
					
					$diff_times = number_format(abs((strtotime($check_in) - strtotime($check_out)) / 3600 - $nex_day), 2, ".", ".");
					
					if($diff_times > 4.5 && $diff_times <= 7.5)
						$diff_times -= 0.5;
					elseif($diff_times > 7.5)
						$diff_times -= 1;
								
					$bonusFrom = get_user_meta($user_id->user_id, 'bonusab', true ).":00";
					$bonusTo = get_user_meta($user_id->user_id, 'bonusbis', true ).":00";
					
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
								
					$bonus = number_format($overlap_hours, 2, ".", ".");
								
					addStempal($user_id->user_id, $rf_id, $_POST, 'out', $diff_times, $bonus);
					echo $_POST['devID'].',ACK;'.$name.';Check-Out!; ;'. $_POST['date'] . " " . $_POST['time']. ';8f0801';
				}		
				else{
					addStempal($user_id->user_id, $rf_id, $_POST, 'in', 0, 0);
					echo $_POST['devID'].',ACK;'.$name.';Check-In!; ;'. $_POST['date'] . " " . $_POST['time']. ';339104';
				}
			}
			else{
				echo $_POST['devID'].',NAK;'.$name.';Check_In nicht möglich!; ;Bereits ausgecheckt;8f0801';
			}
		}
		else{
			echo $_POST['devID'].',NAK; ;Stempel keinem ;Mitarbeiter zugeordnet! ;'.$rf_id.';8f0801';
		}
	}
}

function addStempal($user_id, $rf_id, $data, $state, $diff_times, $bonus){
	$db = Database::getInstance();
	$db->addStempel($user_id, $rf_id, $data, $state, $diff_times, $bonus);
}


?>