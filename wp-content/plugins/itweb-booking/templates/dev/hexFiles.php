<?php

if($_POST){
	$importDIR = ABSPATH . 'wp-content/uploads/aps_bookings_report_backup/'.$_POST['year'].'/'.$_POST['month'].'/';	
	if($_POST['day'] == null)
		$dirs = scandir($importDIR);
	else
		$dirs = array(0 => $_POST['day']);
	foreach ($dirs as $dir) {
		if($dir == "." || $dir == "..") continue;
		echo "<br>";
		echo $_POST['year'] . " - " . $_POST['month'] . " - " . $dir . "<br>";
		echo "<br>";
		$importDIR = ABSPATH . 'wp-content/uploads/aps_bookings_report_backup/'.$_POST['year'].'/'.$_POST['month'].'/'.$dir;	
		$files = scandir($importDIR);
		foreach ($files as $key => $file) {
			$data = array();
			if (!empty($file)) {
				$lines = @file($importDIR . '/' . $file);
					for ($i = 0; $i < sizeof($lines); $i++) {
						if ((strpos($file, 'STR') !== false) || (strpos($file, 'STB') !== false) ) {
							// HX Files
							$data[] = explode("\t", mb_convert_encoding($lines[$i], "UTF-8", "iso-8859-1"));
							//$data[] = explode("\t", htmlspecialchars($lines[$i]));
						} else {
							// Default files.
							$data[] = explode("\t", htmlspecialchars($lines[$i]));	
						}
						
					}
			}
			foreach ($data as $key => $value) {
				$arrivalDateArray = str_split(str_replace('"', "",$value[6]), 2);
				$arrivalDate = date('Y-m-d', strtotime($arrivalDateArray[2] .'-' . $arrivalDateArray[1] . '-' . $arrivalDateArray[0]));
				$departureDateArray = str_split(str_replace('"', "",$value[8]), 2);
				$departureDate = date('Y-m-d', strtotime($departureDateArray[2] .'-' . $departureDateArray[1] . '-' . $departureDateArray[0]));
				$bbokingCode = $value[2];
				
				global $wpdb;
				$order_id = $wpdb->get_row("
				SELECT pm.post_id FROM 59hkh_postmeta pm 
				WHERE pm.meta_key = 'token' and pm.meta_value = '" . $bbokingCode . "'
				");
				$and = get_post_meta($order_id->post_id, 'first_anreisedatum');
				$and = date('Y-m-d', strtotime($and[0]));
				$abd = get_post_meta($order_id->post_id, 'first_abreisedatum');
				$abd = date('Y-m-d', strtotime($abd[0]));
				
				/*
				if($and != $arrivalDate)
					echo $order_id->post_id . " - " . $bbokingCode . " AN " . $arrivalDate . " - " . $and . "<br>";
				if($abd != $departureDate)
					echo $order_id->post_id . " - " . $bbokingCode . " AB " . $departureDate . " - " . $abd . "<br>";
				*/
				foreach($value as $v){
					echo $v . "\t";
				}
				echo "<br>";			
			}		
		}
	}
}
	//echo "<pre>";
	//print_r($_POST);
	//echo "</pre>";
?>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-body">
		<form class="update-price" action="<?php echo basename($_SERVER['REQUEST_URI']); ?>" method="POST">
			<div class="row m60">
				<div class="col-sm-12 col-md-1 col-lg-1">
					<select name="year" class="form-item form-control">
						<option value="">Jahr</option>
						<option value="2021">2021</option>
						<option value="2022">2022</option>
						<option value="2023">2023</option>
						<option value="2024">2024</option>
					</select>
				</div>
				<div class="col-sm-12 col-md-2 col-lg-2">
					<select name="month" class="form-item form-control">
						<option value="">Monat</option>
						<option value="December">Dezember</option>
						<option value="November">November</option>
						<option value="October">Oktober</option>
						<option value="September">September</option>
						<option value="August">August</option>
						<option value="July">Juli</option>
						<option value="June">Juni</option>
						<option value="May">Mai</option>
						<option value="April">April</option>
						<option value="March">März</option>
						<option value="February">Februar</option>
						<option value="January">Januar</option>
					</select>
				</div>
				<div class="col-sm-12 col-md-1 col-lg-1">
					<input type="text" name="day" class="form-control">
				</div>
				<div class="col-sm-12 col-md-2 ">										
					<button class="btn btn-primary edit-order-btn" type="submit" name="show" value="1">Anzeigen</button>
				</div>
			</div>
		</form>
	</div>
</div>

