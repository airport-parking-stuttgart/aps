<?php
global $wpdb;

if(!empty($_POST)){
	foreach($_POST as $key => $val){
		if($val == null)
			continue;
		if (str_contains($key, 'basic')){
			$basic_str = explode("_", $key);
			$basic_p = $basic_str[1];
			$basic_val = $val;
			
			Database::getInstance()->updateBasicContingent($basic_p, $basic_val);
		}
		else{
			$post_key = explode("_", $key);
			$sql_date = $post_key[0];
			$sql_p_id = $post_key[1];
		
			Database::getInstance()->saveContingent($sql_date, $sql_p_id, $val);
		}

	}
	header('Location: /wp-admin/admin.php?page=kontingent');
}


if (isset($_GET["date"])) {
    $date = (explode(" - ", $_GET["date"]));
    $date[0] = date('Y-m-d', strtotime($date[0]));
    $date[1] = date('Y-m-d', strtotime($date[1]));
    $period = new DatePeriod(
        new DateTime($date[0]),
        new DateInterval('P1D'),
        new DateTime($date[1] . '+1 day')
    );

} else {
    $today = date('Y-m-d');
    $period = new DatePeriod(
        new DateTime($today),
        new DateInterval('P1D'),
        new DateTime($today . '+31 day')
    );

    $date = [
        date('Y-m-d'),
        date('Y-m-d', strtotime(date('Y-m-d') . "+30 day"))
    ];
}
$wochentage = array("So.", "Mo.", "Di.", "Mi.", "Do.", "Fr.", "Sa.");
$allContingent = Database::getInstance()->getAllContingent($date);

foreach($allContingent as $ac){
	$set_con[$ac->date."_".$ac->product_id] = $ac->contingent;
}


//echo "<pre>"; print_r($_POST); echo "</pre>";
?>

<style>
.table-full{
	height: 950px;
	overflow: scroll;
}

div.TabelleV5-1 {padding-left:13em;}
div.TabelleV5-2 {position:relative;width:100%; padding:0px;}
.th-space{border-top: 1px solid #909090; border-left: 1px solid #909090;}
.th-total, .th-datum-r, .th-datum{border-left: 1px solid #909090;}

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

.ausen-border-left{
	border-left: 2px solid black !important;
}
.ausen-border-top{
	border-top: 2px solid black !important;
}
.ausen-border-bottom{
	border-bottom: 2px solid black !important;
}
.ausen-border-right{
	border-right: 2px solid black !important;
}
.produkt-trenner{
	border-left: 1.5px solid black;
}

#top{
	overflow: hidden;
}
</style>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Kontingentübersicht</h3>
    </div>
    <div class="page-body">
        <form class="form-filter">
            <div class="row ui-lotdata-block ui-lotdata-block-next">
                <h5 class="ui-lotdata-title">Nach Datum filtern</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-md-2">
							<input type="text" class="datepicker-range form-item form-control" name="date"
								   data-multiple-dates-separator=" - " placeholder="" autocomplete="off"
								   data-from="<?php echo $date[0] ? $date[0] : '' ?>" data-to="<?php echo $date[1] ? $date[1] : '' ?>">
						</div>
						<div class="col-sm-12 col-md-1">
							<button class="btn btn-primary d-block w-100" type="submit">Filter</button>
						</div>
					</div>
				</div>
            </div>
        </form>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . "?page=kontingent"; ?>">

			<button class="btn btn-primary d-block" type="submit">Speichern</button>

			<div class="TabelleV5-1">
				<div class="TabelleV5-2">
					<div class="parklots-data-table" id="top">
						<div class="table-col-wrapper">
							<div class="table-col" style="position: absolute; left: -13em;">						
								<p class="th th-space">&nbsp;<span style="visibility: hidden;">Datum</span><input type="text" size="3" class="" style="visibility: hidden;"></p>
								<p class="th-datum">Datum</p>
							</div>
							<?php $products = Database::getInstance()->getAllLots(); ?>
							<?php foreach ($products as $dateParklot) : ?>
								<?php if($dateParklot->order_lot == 1): ?>
									<div style="width: 330px;" class="table-col">
										<div class="row th ausen-border-top ausen-border-left produkt-trenner" style="border-bottom: 1px solid #909090 !important; border-right: 1px solid #909090 !important;">	
											<div class="col-sm-12 col-md-6">
												<p style="border: none; margin-left: -15px; margin-top: 5px;"><?php echo $dateParklot->parklot_short?></p>								
											</div>
											<div class="col-sm-12 col-md-6">										
												<p style="border: none; margin-right: -15px; float: right;"><input type="text" size="3" name="basic_<?php echo $dateParklot->product_id ?>" style="width: 60px;" value="<?php echo $dateParklot->contigent ?>"></p>
											</div>
										</div>
										<div class="th-lots grid-five">
											<p class="ausen-border-left">APS PH</p>
											<p>APG PH</p>
											<strong><p>Gebucht</p></strong>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>
									</div>
								<?php endif; ?>
								<?php if($dateParklot->order_lot == 5): ?>																						
									<div class="table-col">																			
										<div class="row th ausen-border-top produkt-trenner" style="border-bottom: 1px solid #909090 !important; border-right: 1px solid #909090 !important;">	
											<div class="col-sm-12 col-md-6">
												<p style="border: none; margin-left: -15px; margin-top: 5px;"><?php echo $dateParklot->parklot_short?></p>								
											</div>
											<div class="col-sm-12 col-md-6">										
												<p style="border: none; margin-right: -15px; float: right;"><input type="text" size="3" name="basic_<?php echo $dateParklot->product_id ?>" style="width: 60px;" value="<?php echo $dateParklot->contigent ?>"></p>
											</div>
										</div>
										<div class="th-lots grid-three">
											<strong><p class="produkt-trenner">Gebucht</p></strong>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>
									</div>
									<div class="table-col">
										<p class="th ausen-border-top ausen-border-right produkt-trenner">Gesamt PH
										<input type="text" size="3" style="width: 60px; visibility: hidden;" value=""></p>
										<div class="th-total-ph grid-three">
											<strong><p class="produkt-trenner">Gebucht</p></strong>
											<p>Frei</p>
											<p class="ausen-border-right">Gesamt</p>
										</div>
									</div>
								<?php endif; ?>
								<?php if($dateParklot->order_lot == 10): ?>
									<div style="width: 320px;px;" class="table-col">																			
										<div class="row th ausen-border-top ausen-border-left produkt-trenner" style="border-bottom: 1px solid #909090 !important; border-right: 1px solid #909090 !important;">	
											<div class="col-sm-12 col-md-6">
												<p style="border: none; margin-left: -15px; margin-top: 5px;"><?php echo $dateParklot->parklot_short?></p>								
											</div>
											<div class="col-sm-12 col-md-6">										
												<p style="border: none; margin-right: -15px; float: right;"><input type="text" size="3" name="basic_<?php echo $dateParklot->product_id ?>" style="width: 60px;" value="<?php echo $dateParklot->contigent ?>"></p>
											</div>
										</div>
										<div class="th-lots grid-five">
											<p class="ausen-border-left">APS OD</p>
											<p>APG OD</p>
											<strong><p>Gebucht</p></strong>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>
									</div>	
								<?php endif; ?>
								<?php if($dateParklot->order_lot == 14): ?>								
									<div class="table-col">																				
										<div class="row th ausen-border-top produkt-trenner" style="border-bottom: 1px solid #909090 !important; border-right: 1px solid #909090 !important;">	
											<div class="col-sm-12 col-md-6">
												<p style="border: none; margin-left: -15px; margin-top: 5px;"><?php echo $dateParklot->parklot_short?></p>								
											</div>
											<div class="col-sm-12 col-md-6">										
												<p style="border: none; margin-right: -15px; float: right;"><input type="text" size="3" name="basic_<?php echo $dateParklot->product_id ?>" style="width: 60px;" value="<?php echo $dateParklot->contigent ?>"></p>
											</div>
										</div>
										<div class="th-lots grid-three">
											<strong><p class="produkt-trenner">Gebucht</p></strong>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>	
									</div>
									<div class="table-col">
										<p class="th ausen-border-top ausen-border-right produkt-trenner">Gesamt OD
										<input type="text" size="3" style="width: 60px; visibility: hidden;" value=""></p>
										<div class="th-total-od grid-three">
											<strong><p class="produkt-trenner">Gebucht</p></strong>
											<p>Frei</p>
											<p class="ausen-border-right">Gesamt</p>
										</div>
									</div>
								<?php endif; ?>
								<?php if($dateParklot->order_lot == 30): ?>
									<div style="width: 320px;px;" class="table-col">																				
										<div class="row th ausen-border-top ausen-border-left produkt-trenner" style="border-bottom: 1px solid #909090 !important; border-right: 1px solid #909090 !important;">	
											<div class="col-sm-12 col-md-6">
												<p style="border: none; margin-left: -15px; margin-top: 5px;"><?php echo $dateParklot->parklot_short?></p>								
											</div>
											<div class="col-sm-12 col-md-6">										
												<p style="border: none; margin-right: -15px; float: right;"><input type="text" size="3" name="basic_<?php echo $dateParklot->product_id ?>" style="width: 60px;" value="<?php echo $dateParklot->contigent ?>"></p>
											</div>
										</div>
										<div class="th-lots grid-five">
											<p class="ausen-border-left">APS S</p>
											<p>APG S</p>
											<strong><p>Gebucht</p></strong>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>
									</div>	
								<?php endif; ?>
								<?php if($dateParklot->order_lot == 34): ?>
									<div class="table-col">																				
										<div class="row th ausen-border-top produkt-trenner" style="border-bottom: 1px solid #909090 !important; border-right: 1px solid #909090 !important;">	
											<div class="col-sm-12 col-md-6">
												<p style="border: none; margin-left: -15px; margin-top: 5px;"><?php echo $dateParklot->parklot_short?></p>								
											</div>
											<div class="col-sm-12 col-md-6">										
												<p style="border: none; margin-right: -15px; float: right;"><input type="text" size="3" name="basic_<?php echo $dateParklot->product_id ?>" style="width: 60px;" value="<?php echo $dateParklot->contigent ?>"></p>
											</div>
										</div>
										<div class="th-lots grid-three">
											<strong><p class="produkt-trenner">Gebucht</p></strong>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>
									</div>	
								<?php endif; ?>
								<?php if($dateParklot->order_lot == 35): ?>
									<div class="table-col">																			
										<div class="row th ausen-border-top produkt-trenner" style="border-bottom: 1px solid #909090 !important; border-right: 1px solid #909090 !important;">	
											<div class="col-sm-12 col-md-6">
												<p style="border: none; margin-left: -15px; margin-top: 5px;"><?php echo $dateParklot->parklot_short?></p>								
											</div>
											<div class="col-sm-12 col-md-6">										
												<p style="border: none; margin-right: -15px; float: right;"><input type="text" size="3" name="basic_<?php echo $dateParklot->product_id ?>" style="width: 60px;" value="<?php echo $dateParklot->contigent ?>"></p>
											</div>
										</div>
										<div class="th-lots grid-three">
											<strong><p class="produkt-trenner">Gebucht</p></strong>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>	
									</div>
									<div class="table-col">
										<p class="th ausen-border-top ausen-border-right produkt-trenner">Gesamt S
										<input type="text" size="3" style="width: 60px; visibility: hidden;" value=""></p>
										<div class="th-total-si grid-three">
											<strong><p class="produkt-trenner">Gebucht</p></strong>
											<p>Frei</p>
											<p class="ausen-border-right">Gesamt</p>
										</div>
									</div>
								<?php endif; ?>
								<?php if($dateParklot->order_lot == 40): ?>
									<div style="width: 320px;px;" class="table-col">																				
										<div class="row th ausen-border-top ausen-border-left produkt-trenner" style="border-bottom: 1px solid #909090 !important; border-right: 1px solid #909090 !important;">	
											<div class="col-sm-12 col-md-6">
												<p style="border: none; margin-left: -15px; margin-top: 5px;"><?php echo $dateParklot->parklot_short?></p>								
											</div>
											<div class="col-sm-12 col-md-6">										
												<p style="border: none; margin-right: -15px; float: right;"><input type="text" size="3" name="basic_<?php echo $dateParklot->product_id ?>" style="width: 60px;" value="<?php echo $dateParklot->contigent ?>"></p>
											</div>
										</div>
										<div class="th-lots grid-five">
											<p class="ausen-border-left">APS H</p>
											<p>APG H</p>
											<strong><p>Gebucht</p></strong>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>
									</div>	
								<?php endif; ?>
								<?php if($dateParklot->order_lot == 44): ?>
									<div class="table-col">																			
										<div class="row th ausen-border-top produkt-trenner" style="border-bottom: 1px solid #909090 !important; border-right: 1px solid #909090 !important;">	
											<div class="col-sm-12 col-md-6">
												<p style="border: none; margin-left: -15px; margin-top: 5px;"><?php echo $dateParklot->parklot_short?></p>								
											</div>
											<div class="col-sm-12 col-md-6">										
												<p style="border: none; margin-right: -15px; float: right;"><input type="text" size="3" name="basic_<?php echo $dateParklot->product_id ?>" style="width: 60px;" value="<?php echo $dateParklot->contigent ?>"></p>
											</div>
										</div>
										<div class="th-lots grid-three">
											<strong><p class="produkt-trenner">Gebucht</p></strong>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>	
									</div>
								<?php endif; ?>
								<?php if($dateParklot->order_lot == 47): ?>
									<div class="table-col">																				
										<div class="row th ausen-border-top produkt-trenner" style="border-bottom: 1px solid #909090 !important; border-right: 1px solid #909090 !important;">	
											<div class="col-sm-12 col-md-6">
												<p style="border: none; margin-left: -15px; margin-top: 5px;"><?php echo $dateParklot->parklot_short?></p>								
											</div>
											<div class="col-sm-12 col-md-6">										
												<p style="border: none; margin-right: -15px; float: right;"><input type="text" size="3" name="basic_<?php echo $dateParklot->product_id ?>" style="width: 60px;" value="<?php echo $dateParklot->contigent ?>"></p>
											</div>
										</div>
										<div class="th-lots grid-three">
											<strong><p class="produkt-trenner">Gebucht</p></strong>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>
									</div>
									<div class="table-col">
										<p class="th ausen-border-top ausen-border-right produkt-trenner">Gesamt H
										<input type="text" size="3" style="width: 60px; visibility: hidden;" value=""></p>
										<div class="th-total-ostph grid-three">
											<strong><p class="produkt-trenner">Gebucht</p></strong>
											<p>Frei</p>
											<p class="ausen-border-right">Gesamt</p>
										</div>
									</div>
								<?php endif; ?>
								<?php if($dateParklot->order_lot == 50): ?>
									<div style="width: 320px;px;" class="table-col">																			
										<div class="row th ausen-border-top ausen-border-left produkt-trenner" style="border-bottom: 1px solid #909090 !important; border-right: 1px solid #909090 !important;">	
											<div class="col-sm-12 col-md-6">
												<p style="border: none; margin-left: -15px; margin-top: 5px;"><?php echo $dateParklot->parklot_short?></p>								
											</div>
											<div class="col-sm-12 col-md-6">										
												<p style="border: none; margin-right: -15px; float: right;"><input type="text" size="3" name="basic_<?php echo $dateParklot->product_id ?>" style="width: 60px;" value="<?php echo $dateParklot->contigent ?>"></p>
											</div>
										</div>
										<div class="th-lots grid-five">
											<p class="ausen-border-left">APS PP</p>
											<p>APG PP</p>
											<strong><p>Gebucht</p></strong>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>
									</div>	
								<?php endif; ?>
								<?php if($dateParklot->order_lot == 54): ?>
									<div class="table-col">																				
										<div class="row th ausen-border-top produkt-trenner" style="border-bottom: 1px solid #909090 !important; border-right: 1px solid #909090 !important;">	
											<div class="col-sm-12 col-md-6">
												<p style="border: none; margin-left: -15px; margin-top: 5px;"><?php echo $dateParklot->parklot_short?></p>								
											</div>
											<div class="col-sm-12 col-md-6">										
												<p style="border: none; margin-right: -15px; float: right;"><input type="text" size="3" name="basic_<?php echo $dateParklot->product_id ?>" style="width: 60px;" value="<?php echo $dateParklot->contigent ?>"></p>
											</div>
										</div>
										<div class="th-lots grid-three">
											<strong><p class="produkt-trenner">Gebucht</p></strong>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>	
									</div>	
								<?php endif; ?>
								<?php if($dateParklot->order_lot == 55): ?>
									<div class="table-col">																				
										<div class="row th ausen-border-top produkt-trenner" style="border-bottom: 1px solid #909090 !important; border-right: 1px solid #909090 !important;">	
											<div class="col-sm-12 col-md-6">
												<p style="border: none; margin-left: -15px; margin-top: 5px;"><?php echo $dateParklot->parklot_short?></p>								
											</div>
											<div class="col-sm-12 col-md-6">										
												<p style="border: none; margin-right: -15px; float: right;"><input type="text" size="3" name="basic_<?php echo $dateParklot->product_id ?>" style="width: 60px;" value="<?php echo $dateParklot->contigent ?>"></p>
											</div>
										</div>
										<div class="th-lots grid-three">
											<strong><p class="produkt-trenner">Gebucht</p></strong>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>	
									</div>	
									<div class="table-col">
										<p class="th ausen-border-top ausen-border-right produkt-trenner">Gesamt PP
										<input type="text" size="3" style="width: 60px; visibility: hidden;" value=""></p>
										<div class="th-total-osta grid-three">
											<strong><p class="produkt-trenner">Gebucht</p></strong>
											<p>Frei</p>
											<p class="ausen-border-right">Gesamt</p>
										</div>
									</div>
								<?php endif; ?>							
							<?php endforeach; ?>
							<div class="table-col">
								<p class="th ausen-border-top ausen-border-right">Summe APS
								<input type="text" size="3" style="width: 60px; visibility: hidden;" value=""></p>
								<div class="th-total grid-three">
									<strong><p class="">Gebucht</p></strong>
									<p>Frei</p>
									<p class="ausen-border-right">Gesamt</p>
								</div>
							</div>
							<div class="table-col">
								<p class="th ausen-border-top ausen-border-right">Summe HEX
								<input type="text" size="3" style="width: 60px; visibility: hidden;" value=""></p>
								<div class="th-total grid-three">
									<strong><p class="">Gebucht</p></strong>
									<p>Frei</p>
									<p class="ausen-border-right">Gesamt</p>
								</div>
							</div>
							<div class="table-col">
								<p class="th ausen-border-top ausen-border-right">Summe Parkos
								<input type="text" size="3" style="width: 60px; visibility: hidden;" value=""></p>
								<div class="th-total grid-three">
									<strong><p class="">Gebucht</p></strong>
									<p>Frei</p>
									<p class="ausen-border-right">Gesamt</p>
								</div>
							</div>
							<div class="table-col">
								<p class="th ausen-border-left ausen-border-top ausen-border-right">Summe
								<input type="text" size="3" style="width: 60px; visibility: hidden;" value=""></p>
								<div class="th-total grid-three">
									<strong><p class="ausen-border-left">Gebucht</p></strong>
									<p>Frei</p>
									<p class="ausen-border-right">Gesamt</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="TabelleV5-1 table-full">
				<div class="TabelleV5-2">
					<div class="parklots-data-table" id="bottom" style="margin-top: 0px;">
						<?php
						$total = [];
						$totalTotal = [
							'used' => 0,
							'free' => 0,
							'total' => 0
						];
						$i = 0;
						?>
						
						<?php
							// STR4 + STRH - get STRH
							$k = $l = $av = $apg_ph = $apg_od = $apg_sie = $apg_osth = $apg_ostp = 0;
							foreach ($period as $key => $value){
								$dateParklots = Database::getInstance()->getParkotsWithOrdersData($value->format('Y-m-d'));
								foreach ($dateParklots as $dateParklot){
									
									if($dateParklot->order_lot == 3){
										$apg_ph_used_r[$apg_ph] = (int)$dateParklot->used;
										$apg_ph++;
									}
									
									if($dateParklot->order_lot == 6){									
										$totalUsedSTRH[$k] += (int)$dateParklot->used;
										$k++;
									}
									
									if($dateParklot->order_lot == 12){
										$apg_od_used_r[$apg_od] = (int)$dateParklot->used;
										$apg_od++;									
									}
									if($dateParklot->order_lot == 32){
										$apg_sie_used_r[$apg_sie] = (int)$dateParklot->used;
										$apg_sie++;
									}
									if($dateParklot->order_lot == 42){
										$apg_osth_used_r[$apg_osth] = (int)$dateParklot->used;
										$apg_osth++;
									}
									if($dateParklot->order_lot == 52){
										$apg_ostp_used_r[$apg_ostp] = (int)$dateParklot->used;
										$apg_ostp++;
									}
								}
							}						
						?>
						
						<?php foreach ($period as $key => $value){$dates++;}?>
						
						<?php foreach ($period as $key => $value) : ?>
							<?php
							$totalUsed = 0;
							$totalFree = 0;
							$totalContigent = 0;
							$totalUsedPH = 0;
							$totalFreePH = 0;
							$totalContigentPH = 0;
							$totalUsedOD = 0;
							$totalFreeOD = 0;
							$totalContigentOD = 0;
							$totalUsedSIE = 0;
							$totalFreeSIE = 0;
							$totalContigentSIE = 0;
							$totalUsedOSTH = 0;
							$totalFreeOSTH = 0;
							$totalContigentOSTH = 0;
							$totalUsedOSTP = 0;
							$totalFreeOSTP = 0;
							$totalContigentOSTP = 0;							
							$totalUsedAPS = 0;
							$totalFreeAPS = 0;
							$totalContigentAPS = 0;
							$totalUsedHEX = 0;
							$totalFreeHEX = 0;
							$totalContigentHEX = 0;
							$totalUsedPA = 0;
							$totalFreePA = 0;
							$totalContigentPA = 0;
							
							$av += 1;
							?>
							<div class="table-col-wrapper">
								<?php
								$dateParklots = Database::getInstance()->getParkotsWithOrdersData($value->format('Y-m-d'));
								$lastItem = !next($period);
								?>
								<div class="table-col" style="position: absolute; left: -13em;">
									<p class="th-datum-r tr-datum-<?php echo $i % 2; ?>"><?php echo $wochentage[date("w", strtotime($value->format('d.m.Y')))] . " " . $value->format('d.m.Y') . "<br>" ?>
									<span style="visibility: hidden;">Datum</span></p>
								</div>
								<?php $k = 1; foreach ($dateParklots as $dateParklot) : ?>
									<?php if($dateParklot->order_lot == 6 || $dateParklot->order_lot == 45 || $dateParklot->order_lot == 46 || $dateParklot->order_lot >= 100 || 
											$dateParklot->order_lot == 2 || $dateParklot->order_lot == 4 || $dateParklot->order_lot == 11 || $dateParklot->order_lot == 13 || 
											$dateParklot->order_lot == 20 || $dateParklot->order_lot == 21 || $dateParklot->order_lot == 22 || $dateParklot->order_lot == 23 || $dateParklot->order_lot == 24 || 
											$dateParklot->order_lot == 31 || $dateParklot->order_lot == 33 || 
											$dateParklot->order_lot == 41 || $dateParklot->order_lot == 43 || $dateParklot->order_lot == 51 || $dateParklot->order_lot == 53
											) 
											
											continue; 
									?>
									<?php
									if (!isset($total[$dateParklot->parklot_short])) {
										$total[$dateParklot->parklot_short] = [
											'used' => 0,
											'free' => 0,
											'total' => 0
										];
									}
									$con = $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] != null ? $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] : $dateParklot->contigent;							
									$dateParklot->contigent = $con;
									$free = (int)$con - (int)$dateParklot->used;
									$totalUsed += (int)$dateParklot->used;
									$totalFree += $free;
									$totalContigent += (int)$con;								
									
									/// Parkhaus
									if($dateParklot->order_lot == 5){
										(int)$dateParklot->used += $totalUsedSTRH[$i];
										(int)$dateParklot->contigent += $totalContigentSTRH[$i];
										$free -= $totalUsedSTRH[$i];
										$totalUsed += $totalUsedSTRH[$i];
										$totalFree -= $totalUsedSTRH[$i];
									}
									
									if($dateParklot->order_lot == 1 || $dateParklot->order_lot == 3 || $dateParklot->order_lot == 5){
										$freePH = (int)$dateParklot->contigent - (int)$dateParklot->used;
										$totalUsedPH += (int)$dateParklot->used;
										$totalFreePH += $freePH;
										$totalContigentPH += (int)$dateParklot->contigent;																	
									}
									if($dateParklot->order_lot == 1){
										$freeAPS = (int)$dateParklot->contigent - (int)$dateParklot->used;
										$aps_ph_used = (int)$dateParklot->used;
										$aps_ph_free = (int)$dateParklot->contigent - (int)$dateParklot->used;
										$aps_ph_contingent = (int)$dateParklot->contigent;
										
										$totalUsedAPS += (int)$dateParklot->used;
										$totalFreeAPS += $freeAPS;
										$totalContigentAPS += (int)$dateParklot->contigent;	
									}
									if($dateParklot->order_lot == 3){
										$apg_ph_used = (int)$dateParklot->used;
										$apg_ph_free = 0;
										$totalTotalUsedPH -= $apg_ph_used;
										$totalTotalFreePH -= $aps_ph_free;
										$totalUsedAPS += $apg_ph_used;
									}
									if($dateParklot->order_lot == 5){
										$freeHEX = (int)$dateParklot->contigent - (int)$dateParklot->used;																		
										$totalUsedHEX += (int)$dateParklot->used;
										$totalFreeHEX += $freeHEX;
										$totalContigentHEX += (int)$dateParklot->contigent;	
									}
									///
									
									/// Oberdeck
									if($dateParklot->order_lot == 10 || $dateParklot->order_lot == 12 || $dateParklot->order_lot == 14){
										$freeOD = (int)$dateParklot->contigent - (int)$dateParklot->used;
										$totalUsedOD += (int)$dateParklot->used;
										$totalFreeOD += $freeOD;
										$totalContigentOD += (int)$dateParklot->contigent;
									}
									
									if($dateParklot->order_lot == 10){
										$freeAPS = (int)$dateParklot->contigent - (int)$dateParklot->used;
										$aps_od_used = (int)$dateParklot->used;
										$aps_od_free = (int)$dateParklot->contigent - (int)$dateParklot->used;
										$aps_od_contingent = (int)$dateParklot->contigent;
										
										$totalUsedAPS += (int)$dateParklot->used;
										$totalFreeAPS += $freeAPS;
										$totalContigentAPS += (int)$dateParklot->contigent;	
									}
									
									if($dateParklot->order_lot == 12){
										$apg_od_used = (int)$dateParklot->used;
										$apg_od_free = 0;
										$totalTotalUsedOD -= $apg_od_used;
										$totalTotalFreeOD -= $aps_od_free;
										$totalUsedAPS += $apg_od_used;
									}
									
									if($dateParklot->order_lot == 14){
										$freeHEX = (int)$dateParklot->contigent - (int)$dateParklot->used;																		
										$totalUsedHEX += (int)$dateParklot->used;
										$totalFreeHEX += $freeHEX;
										$totalContigentHEX += (int)$dateParklot->contigent;	
									}
									///
									
									/// Sielmingen
									if($dateParklot->order_lot == 30 || $dateParklot->order_lot == 32 || $dateParklot->order_lot == 34 || $dateParklot->order_lot == 35){
										$freeSIE = (int)$dateParklot->contigent - (int)$dateParklot->used;
										$totalUsedSIE += (int)$dateParklot->used;
										$totalFreeSIE += $freeSIE;
										$totalContigentSIE += (int)$dateParklot->contigent;
									}
									
									if($dateParklot->order_lot == 30){
										$freeAPS = (int)$dateParklot->contigent - (int)$dateParklot->used;
										$aps_sie_used = (int)$dateParklot->used;
										$aps_sie_free = (int)$dateParklot->contigent - (int)$dateParklot->used;
										$aps_sie_contingent = (int)$dateParklot->contigent;	
										
										$totalUsedAPS += (int)$dateParklot->used;
										$totalFreeAPS += $freeAPS;
										$totalContigentAPS += (int)$dateParklot->contigent;	
									}
									
									if($dateParklot->order_lot == 32){
										$apg_sie_used = (int)$dateParklot->used;
										$apg_sie_free = 0;
										$totalTotalUsedSIE -= $apg_sie_used;
										$totalTotalFreeSIE -= $aps_sie_free;
										$totalUsedAPS += $apg_sie_used;
									}
									
									if($dateParklot->order_lot == 34){
										$freeHEX = (int)$dateParklot->contigent - (int)$dateParklot->used;																		
										$totalUsedHEX += (int)$dateParklot->used;
										$totalFreeHEX += $freeHEX;
										$totalContigentHEX += (int)$dateParklot->contigent;	
									}
									
									if($dateParklot->order_lot == 35){
										$freePA = (int)$dateParklot->contigent - (int)$dateParklot->used;																		
										$totalUsedPA += (int)$dateParklot->used;
										$totalFreePA += $freePA;
										$totalContigentPA += (int)$dateParklot->contigent;	
									}
									///
									
									/// Parkhalle
									if($dateParklot->order_lot == 40 || $dateParklot->order_lot == 42 || $dateParklot->order_lot == 44 || $dateParklot->order_lot == 47){
										$freeOSTH = (int)$dateParklot->contigent - (int)$dateParklot->used;
										$totalUsedOSTH += (int)$dateParklot->used;
										$totalFreeOSTH += $freeOSTH;
										$totalContigentOSTH += (int)$dateParklot->contigent;
									}
									if($dateParklot->order_lot == 40){
										$freeAPS = (int)$dateParklot->contigent - (int)$dateParklot->used;
										$aps_osth_used = (int)$dateParklot->used;
										$aps_osth_free = (int)$dateParklot->contigent - (int)$dateParklot->used;
										$aps_osth_contingent = (int)$dateParklot->contigent;
										
										$totalUsedAPS += (int)$dateParklot->used;
										$totalFreeAPS += $freeAPS;
										$totalContigentAPS += (int)$dateParklot->contigent;	
									}
									if($dateParklot->order_lot == 42){
										$apg_osth_used = (int)$dateParklot->used;
										$apg_osth_free = 0;
										$totalTotalUsedOSTH -= $apg_osth_used;
										$totalTotalFreeOSTH -= $aps_osth_free;
										$totalUsedAPS += $apg_osth_used;
									}
									
									if($dateParklot->order_lot == 44){
										$freeHEX = (int)$dateParklot->contigent - (int)$dateParklot->used;																		
										$totalUsedHEX += (int)$dateParklot->used;
										$totalFreeHEX += $freeHEX;
										$totalContigentHEX += (int)$dateParklot->contigent;	
									}
									if($dateParklot->order_lot == 47){
										$freePA = (int)$dateParklot->contigent - (int)$dateParklot->used;																		
										$totalUsedPA += (int)$dateParklot->used;
										$totalFreePA += $freePA;
										$totalContigentPA += (int)$dateParklot->contigent;	
									}
									///
									
									/// Parkplatz
									if($dateParklot->order_lot == 50 || $dateParklot->order_lot == 52 || $dateParklot->order_lot == 54 || $dateParklot->order_lot == 55){
										$freeOSTP = (int)$dateParklot->contigent - (int)$dateParklot->used;
										$totalUsedOSTP += (int)$dateParklot->used;
										$totalFreeOSTP += $freeOSTP;
										$totalContigentOSTP += (int)$dateParklot->contigent;
									}
									if($dateParklot->order_lot == 50){
										$freeAPS = (int)$dateParklot->contigent - (int)$dateParklot->used;
										$aps_ostp_used = (int)$dateParklot->used;
										$aps_ostp_free = (int)$dateParklot->contigent - (int)$dateParklot->used;
										$aps_ostp_contingent = (int)$dateParklot->contigent;
										
										$totalUsedAPS += (int)$dateParklot->used;
										$totalFreeAPS += $freeAPS;
										$totalContigentAPS += (int)$dateParklot->contigent;	
									}
									if($dateParklot->order_lot == 52){
										$apg_ostp_used = (int)$dateParklot->used;
										$apg_ostp_free = 0;
										$totalTotalUsedOSTP -= $apg_ostp_used;
										$totalTotalFreeOSTP -= $aps_ostp_free;
										$totalUsedAPS += $apg_ostp_used;
									}
									
									if($dateParklot->order_lot == 54){
										$freeHEX = (int)$dateParklot->contigent - (int)$dateParklot->used;																		
										$totalUsedHEX += (int)$dateParklot->used;
										$totalFreeHEX += $freeHEX;
										$totalContigentHEX += (int)$dateParklot->contigent;	
									}
									
									if($dateParklot->order_lot == 55){
										$freePA = (int)$dateParklot->contigent - (int)$dateParklot->used;																		
										$totalUsedPA += (int)$dateParklot->used;
										$totalFreePA += $freePA;
										$totalContigentPA += (int)$dateParklot->contigent;	
									}
									///
									

									// calculate parklot used, free, contigent in all dates
									$total[$dateParklot->parklot_short]['used'] += (int)$dateParklot->used;
									$total[$dateParklot->parklot_short]['free'] += $free;
									$total[$dateParklot->parklot_short]['total'] += (int)$dateParklot->contigent;
									
									
									?>
									<?php /// Parkhaus ?>
									<?php if($dateParklot->order_lot == 1): ?>
									<div style="width: 330px;" class="table-col">
										
										<?php
										if($aps_ph_contingent != 0){
											if(number_format(($apg_ph_used_r[$i] + $dateParklot->used) / $aps_ph_contingent * 100, 2,".",".") >= 70 && number_format(($apg_ph_used_r[$i] + $dateParklot->used) / $aps_ph_contingent * 100, 2,".",".") < 85)
												$text_color = "purple";
											elseif(number_format(($apg_ph_used_r[$i] + $dateParklot->used) / $aps_ph_contingent * 100, 2,".",".") >= 85)
												$text_color = "red";										
											else
												$text_color = "";
										}
										else
											$text_color = "";
										if(number_format(($apg_ph_used_r[$i] + $dateParklot->used) / $aps_ph_contingent * 100, 2,".",".") == 100)
											$background = "green";
										elseif(($apg_ph_used_r[$i] + $dateParklot->used) > $aps_ph_contingent)
											$background = "yellow";
										else
											$background = "";
										?>
										<div class="grid-five">
											<p class="ausen-border-left <?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> used-val tr-lots-<?php echo $i % 2; ?>"><?php echo $dateParklot->used . "<br>"; echo $dateParklot->contigent != 0 ? number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%";?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> used-val tr-lots-<?php echo $i % 2; ?>"><?php echo $apg_ph_used_r[$i] . "<br>"; echo $aps_ph_contingent != 0 ? number_format($apg_ph_used_r[$i] / $aps_ph_contingent * 100, 2,".",".") : "0.00"; echo "%";?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?> <?php echo $background ?>"><strong><?php echo $apg_ph_used_r[$i] + $dateParklot->used . "<br>"; echo $aps_ph_contingent != 0 ? number_format(($apg_ph_used_r[$i] + $dateParklot->used) / $aps_ph_contingent * 100, 2,".",".") : "0.00"; echo "%";?></strong></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free - $apg_ph_used_r[$i]  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format(($free - $apg_ph_used_r[$i]) / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>">
											<input type="text" size="3" name="<?php echo $value->format('Y-m-d') . "_" . $dateParklot->product_id ?>" class="" value="<?php echo $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] != null ? $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] : "" ?>"></p>
										</div>									
									</div>
									<?php endif; ?>
									<?php if($dateParklot->order_lot == 5): ?>																						
									<div class="table-col">									
										<?php
										if($dateParklot->contigent != 0){
											if(number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") >= 70 && number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") < 85)
												$text_color = "purple";
											elseif(number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") >= 85)
												$text_color = "red";										
											else
												$text_color = "";
										}
										else
											$text_color = "";
										
										if(number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") == 100)
											$background = "green";
										elseif($dateParklot->used > $dateParklot->contigent)
											$background = "yellow";
										else
											$background = "";
										?>		
										<div class="grid-three">
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><strong><?php echo $dateParklot->used . "<br>"; echo $dateParklot->contigent != 0 ? number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%";?></strong></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format($free / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><input type="text" size="3" name="<?php echo $value->format('Y-m-d') . "_" . $dateParklot->product_id ?>" class="" value="<?php echo $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] != null ? $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] : "" ?>"></p>
										</div>									
									</div>
									<div class="table-col">								
										<?php
										if($totalContigentPH != 0){
											if(number_format($totalUsedPH / $totalContigentPH * 100, 2,".",".") >= 70 && number_format($totalUsedPH / $totalContigentPH * 100, 2,".",".") < 85)
												$text_color = "purple";
											elseif(number_format($totalUsedPH / $totalContigentPH * 100, 2,".",".") >= 85)
												$text_color = "red";										
											else
												$text_color = "";
										}
										else
											$text_color = "";
										
										if(number_format($totalUsedPH / $totalContigentPH * 100, 2,".",".") == 100)
											$background = "green";
										elseif($totalUsedPH > $totalContigentPH)
											$background = "yellow";
										else
											$background = "";
										?>	
										<div class="grid-three">
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner tr-total-ph-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><strong><?php echo $totalUsedPH . "<br>"; echo $totalContigentPH != 0 ? number_format($totalUsedPH / $totalContigentPH * 100, 2,".",".") : "0.00"; echo "%"; ?></strong></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> tr-total-ph-<?php echo $i % 2; ?>"><?php echo $totalFreePH . "<br>"; echo $totalContigentPH != 0 ? number_format($totalFreePH / $totalContigentPH * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> ausen-border-right tr-total-ph-<?php echo $i % 2; ?>"><input type="text" size="3" class="" style="visibility: hidden;"></p>
										</div>
									</div>
									<?php endif; ?>
									<?php /// ?>
									
									<?php /// Oberdeck ?>
									<?php if($dateParklot->order_lot == 10): ?>
									<div style="width: 320px;" class="table-col">
										<?php
										if($aps_od_contingent != 0){
											if(number_format(($apg_od_used_r[$i] + $dateParklot->used) / $aps_od_contingent * 100, 2,".",".") >= 70 && number_format(($apg_od_used_r[$i] + $dateParklot->used) / $aps_od_contingent * 100, 2,".",".") < 85)
												$text_color = "purple";
											elseif(number_format(($apg_od_used_r[$i] + $dateParklot->used) / $aps_od_contingent * 100, 2,".",".") >= 85)
												$text_color = "red";										
											else
												$text_color = "";
										}
										else
											$text_color = "";
										
										if(number_format(($apg_od_used_r[$i] + $dateParklot->used) / $aps_od_contingent * 100, 2,".",".") == 100)
											$background = "green";
										elseif(($apg_od_used_r[$i] + $dateParklot->used) > $aps_od_contingent)
											$background = "yellow";
										else
											$background = "";
										?>	
										<div class="grid-five">
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> ausen-border-left used-val tr-lots-<?php echo $i % 2; ?>"><?php echo $dateParklot->used . "<br>"; echo $dateParklot->contigent != 0 ? number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%";?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> used-val tr-lots-<?php echo $i % 2; ?>"><?php echo $apg_od_used_r[$i] . "<br>"; echo $aps_od_contingent != 0 ? number_format($apg_od_used_r[$i] / $aps_od_contingent * 100, 2,".",".") : "0.00"; echo "%";?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><strong><?php echo $apg_od_used_r[$i] + $dateParklot->used . "<br>"; echo $aps_od_contingent != 0 ? number_format(($apg_od_used_r[$i] + $dateParklot->used) / $aps_od_contingent * 100, 2,".",".") : "0.00"; echo "%";?></strong></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free - $apg_od_used_r[$i]  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format(($free - $apg_od_used_r[$i]) / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><input type="text" size="3" name="<?php echo $value->format('Y-m-d') . "_" . $dateParklot->product_id ?>" class="" value="<?php echo $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] != null ? $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] : "" ?>"></p>
										</div>									
									</div>
									<?php endif; ?>
									<?php if($dateParklot->order_lot == 14): ?>								
									<div class="table-col">
										<?php
										if($dateParklot->contigent != 0){
											if(number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") >= 70 && number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") < 85)
												$text_color = "purple";
											elseif(number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") >= 85)
												$text_color = "red";										
											else
												$text_color = "";
										}
										else
											$text_color = "";
										
										if(number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") == 100)
											$background = "green";
										elseif($dateParklot->used > $dateParklot->contigent)
											$background = "yellow";
										else
											$background = "";
										?>	
										<div class="grid-three">
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><strong><?php echo $dateParklot->used . "<br>"; echo $dateParklot->contigent != 0 ? number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%";?></strong></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format($free / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><input type="text" size="3" name="<?php echo $value->format('Y-m-d') . "_" . $dateParklot->product_id ?>" class="" value="<?php echo $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] != null ? $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] : "" ?>"></p>
										</div>									
									</div>
									<div class="table-col">
										<?php
										if($totalContigentOD != 0){
											if(number_format($totalUsedOD / $totalContigentOD * 100, 2,".",".") >= 70 && number_format($totalUsedOD / $totalContigentOD * 100, 2,".",".") < 85)
												$text_color = "purple";
											elseif(number_format($totalUsedOD / $totalContigentOD * 100, 2,".",".") >= 85)
												$text_color = "red";										
											else
												$text_color = "";
										}
										else
											$text_color = "";
										
										if(number_format($totalUsedOD / $totalContigentOD * 100, 2,".",".") == 100)
											$background = "green";
										elseif($totalUsedOD > $totalContigentOD)
											$background = "yellow";
										else
											$background = "";
										?>	
										<div class="grid-three">
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner tr-total-od-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><strong><?php echo $totalUsedOD . "<br>"; echo $totalContigentOD != 0 ? number_format($totalUsedOD / $totalContigentOD * 100, 2,".",".") : "0.00"; echo "%"; ?></strong></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> tr-total-od-<?php echo $i % 2; ?>"><?php echo $totalFreeOD . "<br>"; echo $totalContigentOD != 0 ? number_format($totalFreeOD / $totalContigentOD * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> ausen-border-right tr-total-od-<?php echo $i % 2; ?>"><input type="text" size="3" class="" style="visibility: hidden;"></p>
										</div>
									</div>
									<?php endif; ?>
									<?php /// ?>
									
									<?php /// Sielmingen ?>
									<?php if($dateParklot->order_lot == 30): ?>
									<div style="width: 320px;" class="table-col">
										<?php
										if($aps_sie_contingent != 0){
											if(number_format(($apg_sie_used_r[$i] + $dateParklot->used) / $aps_sie_contingent * 100, 2,".",".") >= 70 && number_format(($apg_sie_used_r[$i] + $dateParklot->used) / $aps_sie_contingent * 100, 2,".",".") < 85)
												$text_color = "purple";
											elseif(number_format(($apg_sie_used_r[$i] + $dateParklot->used) / $aps_sie_contingent * 100, 2,".",".") >= 85)
												$text_color = "red";										
											else
												$text_color = "";
										}
										else
											$text_color = "";
										
										if(number_format(($apg_sie_used_r[$i] + $dateParklot->used) / $aps_sie_contingent * 100, 2,".",".") == 100)
											$background = "green";
										elseif(($apg_sie_used_r[$i] + $dateParklot->used) > $aps_sie_contingent)
											$background = "yellow";
										else
											$background = "";
										?>	
										<div class="grid-five">
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> ausen-border-left used-val tr-lots-<?php echo $i % 2; ?>"><?php echo $dateParklot->used . "<br>"; echo $dateParklot->contigent != 0 ? number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%";?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> used-val tr-lots-<?php echo $i % 2; ?>"><?php echo $apg_sie_used_r[$i] . "<br>"; echo $aps_sie_contingent != 0 ? number_format($apg_sie_used_r[$i] / $aps_sie_contingent * 100, 2,".",".") : "0.00"; echo "%";?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><strong><?php echo $apg_sie_used_r[$i] + $dateParklot->used . "<br>"; echo $aps_sie_contingent != 0 ? number_format(($apg_sie_used_r[$i] + $dateParklot->used) / $aps_sie_contingent * 100, 2,".",".") : "0.00"; echo "%";?></strong></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free - $apg_sie_used_r[$i]  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format(($free - $apg_sie_used_r[$i]) / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><input type="text" size="3" name="<?php echo $value->format('Y-m-d') . "_" . $dateParklot->product_id ?>" class="" value="<?php echo $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] != null ? $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] : "" ?>"></p>
										</div>									
									</div>
									<?php endif; ?>
									<?php if($dateParklot->order_lot == 34): ?>
									<div class="table-col">
										<?php
										if($dateParklot->contigent != 0){
											if(number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") >= 70 && number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") < 85)
												$text_color = "purple";
											elseif(number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") >= 85)
												$text_color = "red";										
											else
												$text_color = "";
										}
										else
											$text_color = "";
										
										if(number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") == 100)
											$background = "green";
										elseif($dateParklot->used > $dateParklot->contigent)
											$background = "yellow";
										else
											$background = "";
										?>	
										<div class="grid-three">
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><strong><?php echo $dateParklot->used . "<br>"; echo $dateParklot->contigent != 0 ? number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%";?></strong></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format($free / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><input type="text" size="3" name="<?php echo $value->format('Y-m-d') . "_" . $dateParklot->product_id ?>" class="" value="<?php echo $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] != null ? $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] : "" ?>"></p>
										</div>									
									</div>
									<?php endif; ?>
									<?php if($dateParklot->order_lot == 35): ?>
									<div class="table-col">
										<?php
										if($dateParklot->contigent != 0){
											if(number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") >= 70 && number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") < 85)
												$text_color = "purple";
											elseif(number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") >= 85)
												$text_color = "red";										
											else
												$text_color = "";
										}
										else
											$text_color = "";
										
										if(number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") == 100)
											$background = "green";
										elseif($dateParklot->used > $dateParklot->contigent)
											$background = "yellow";
										else
											$background = "";
										?>	
										<div class="grid-three">
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><strong><?php echo $dateParklot->used . "<br>"; echo $dateParklot->contigent != 0 ? number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%";?></strong></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format($free / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><input type="text" size="3" name="<?php echo $value->format('Y-m-d') . "_" . $dateParklot->product_id ?>" class="" value="<?php echo $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] != null ? $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] : "" ?>"></p>
										</div>									
									</div>
									<div class="table-col">
										<?php
										if($totalContigentSIE != 0){
											if(number_format($totalUsedSIE / $totalContigentSIE * 100, 2,".",".") >= 70 && number_format($totalUsedSIE / $totalContigentSIE * 100, 2,".",".") < 85)
												$text_color = "purple";
											elseif(number_format($totalUsedSIE / $totalContigentSIE * 100, 2,".",".") >= 85)
												$text_color = "red";										
											else
												$text_color = "";
										}
										else
											$text_color = "";
										
										if(number_format($totalUsedSIE / $totalContigentSIE * 100, 2,".",".") == 100)
											$background = "green";
										elseif($totalUsedSIE > $totalContigentSIE)
											$background = "yellow";
										else
											$background = "";
										?>	
											<div class="grid-three">
												<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner tr-total-si-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><strong><?php echo $totalUsedSIE . "<br>"; echo $totalContigentSIE != 0 ? number_format($totalUsedSIE / $totalContigentSIE * 100, 2,".",".") : "0.00"; echo "%"; ?></strong></p>
												<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> tr-total-si-<?php echo $i % 2; ?>"><?php echo $totalFreeSIE . "<br>"; echo $totalContigentSIE != 0 ? number_format($totalFreeSIE / $totalContigentSIE * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
												<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> ausen-border-right tr-total-si-<?php echo $i % 2; ?>"><input type="text" size="3" class="" style="visibility: hidden;"></p>
											</div>
									</div>
									<?php endif; ?>
									<?php /// ?>
									
									<?php /// Parkhalle ?>
									<?php if($dateParklot->order_lot == 40): ?>
									<div style="width: 320px;" class="table-col">
										<?php
										if($aps_osth_contingent != 0){
											if(number_format(($apg_osth_used_r[$i] + $dateParklot->used) / $aps_osth_contingent * 100, 2,".",".") >= 70 && number_format(($apg_osth_used_r[$i] + $dateParklot->used) / $aps_osth_contingent * 100, 2,".",".") < 85)
												$text_color = "purple";
											elseif(number_format(($apg_osth_used_r[$i] + $dateParklot->used) / $aps_osth_contingent * 100, 2,".",".") >= 85)
												$text_color = "red";										
											else
												$text_color = "";
										}
										else
											$text_color = "";
										
										if(number_format(($apg_osth_used_r[$i] + $dateParklot->used) / $aps_osth_contingent * 100, 2,".",".") == 100)
											$background = "green";
										elseif(($apg_osth_used_r[$i] + $dateParklot->used) > $aps_osth_contingent)
											$background = "yellow";
										else
											$background = "";
										?>	
										<div class="grid-five">
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> ausen-border-left used-val tr-lots-<?php echo $i % 2; ?>"><?php echo $dateParklot->used . "<br>"; echo $dateParklot->contigent != 0 ? number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%";?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> used-val tr-lots-<?php echo $i % 2; ?>"><?php echo $apg_osth_used_r[$i] . "<br>"; echo $aps_osth_contingent != 0 ? number_format($apg_osth_used_r[$i] / $aps_osth_contingent * 100, 2,".",".") : "0.00"; echo "%";?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><strong><?php echo $apg_osth_used_r[$i] + $dateParklot->used . "<br>"; echo $aps_osth_contingent != 0 ? number_format(($apg_osth_used_r[$i] + $dateParklot->used) / $aps_osth_contingent * 100, 2,".",".") : "0.00"; echo "%";?></strong></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free - $apg_osth_used_r[$i]  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format(($free - $apg_osth_used_r[$i]) / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><input type="text" size="3" name="<?php echo $value->format('Y-m-d') . "_" . $dateParklot->product_id ?>" class="" value="<?php echo $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] != null ? $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] : "" ?>"></p>
										</div>									
									</div>
									<?php endif; ?>
									<?php if($dateParklot->order_lot == 44): ?>
									<div class="table-col">
										<?php
										if($dateParklot->contigent != 0){
											if(number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") >= 70 && number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") < 85)
												$text_color = "purple";
											elseif(number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") >= 85)
												$text_color = "red";										
											else
												$text_color = "";
										}
										else
											$text_color = "";
										
										if(number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") == 100)
											$background = "green";
										elseif($dateParklot->used > $dateParklot->contigent)
											$background = "yellow";
										else
											$background = "";
										?>	
										<div class="grid-three">
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><strong><?php echo $dateParklot->used . "<br>"; echo $dateParklot->contigent != 0 ? number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%";?></strong></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format($free / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><input type="text" size="3" name="<?php echo $value->format('Y-m-d') . "_" . $dateParklot->product_id ?>" class="" value="<?php echo $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] != null ? $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] : "" ?>"></p>
										</div>									
									</div>
									<?php endif; ?>
									<?php if($dateParklot->order_lot == 47): ?>
									<div class="table-col">
										<?php
										if($dateParklot->contigent != 0){
											if(number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") >= 70 && number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") < 85)
												$text_color = "purple";
											elseif(number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") >= 85)
												$text_color = "red";										
											else
												$text_color = "";
										}
										else
											$text_color = "";
										
										if(number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") == 100)
											$background = "green";
										elseif($dateParklot->used > $dateParklot->contigent)
											$background = "yellow";
										else
											$background = "";
										?>	
										<div class="grid-three">
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><strong><?php echo $dateParklot->used . "<br>"; echo $dateParklot->contigent != 0 ? number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%";?></strong></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format($free / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><input type="text" size="3" name="<?php echo $value->format('Y-m-d') . "_" . $dateParklot->product_id ?>" class="" value="<?php echo $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] != null ? $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] : "" ?>"></p>
										</div>									
									</div>
									<div class="table-col">
										<?php
										if($totalContigentOSTH != 0){
											if(number_format($totalUsedOSTH / $totalContigentOSTH * 100, 2,".",".") >= 70 && number_format($totalUsedOSTH / $totalContigentOSTH * 100, 2,".",".") < 85)
												$text_color = "purple";
											elseif(number_format($totalUsedOSTH / $totalContigentOSTH * 100, 2,".",".") >= 85)
												$text_color = "red";										
											else
												$text_color = "";
										}
										else
											$text_color = "";
										
										if(number_format($totalUsedOSTH / $totalContigentOSTH * 100, 2,".",".") == 100)
											$background = "green";
										elseif($totalUsedOSTH > $totalContigentOSTH)
											$background = "yellow";
										else
											$background = "";
										?>	
										<div class="grid-three">
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner tr-total-ostph-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><strong><?php echo $totalUsedOSTH . "<br>"; echo $totalContigentOSTH != 0 ? number_format($totalUsedOSTH / $totalContigentOSTH * 100, 2,".",".") : "0.00"; echo "%"; ?></strong></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> tr-total-ostph-<?php echo $i % 2; ?>"><?php echo $totalFreeOSTH . "<br>"; echo $totalContigentOSTH != 0 ? number_format($totalFreeOSTH / $totalContigentOSTH * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> ausen-border-right tr-total-ostph-<?php echo $i % 2; ?>"><input type="text" size="3" class="" style="visibility: hidden;"></p>
										</div>
									</div>
									<?php endif; ?>
									<?php /// ?>
									
									<?php /// Parkplatz ?>
									<?php if($dateParklot->order_lot == 50): ?>
									<div style="width: 320px;" class="table-col">
										<?php
										if($aps_ostp_contingent != 0){
											if(number_format(($apg_ostp_used_r[$i] + $dateParklot->used) / $aps_ostp_contingent * 100, 2,".",".") >= 70 && number_format(($apg_ostp_used_r[$i] + $dateParklot->used) / $aps_ostp_contingent * 100, 2,".",".") < 85)
												$text_color = "purple";
											elseif(number_format(($apg_ostp_used_r[$i] + $dateParklot->used) / $aps_ostp_contingent * 100, 2,".",".") >= 85)
												$text_color = "red";										
											else
												$text_color = "";
										}
										else
											$text_color = "";
										
										if(number_format(($apg_ostp_used_r[$i] + $dateParklot->used) / $aps_ostp_contingent * 100, 2,".",".") == 100)
											$background = "green";
										elseif(($apg_ostp_used_r[$i] + $dateParklot->used) > $aps_ostp_contingent)
											$background = "yellow";
										else
											$background = "";
										?>	
										<div class="grid-five">
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> ausen-border-left used-val tr-lots-<?php echo $i % 2; ?>"><?php echo $dateParklot->used . "<br>"; echo $dateParklot->contigent != 0 ? number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%";?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> used-val tr-lots-<?php echo $i % 2; ?>"><?php echo $apg_ostp_used_r[$i] . "<br>"; echo $aps_ostp_contingent != 0 ? number_format($apg_ostp_used_r[$i] / $aps_ostp_contingent * 100, 2,".",".") : "0.00"; echo "%";?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><strong><?php echo $apg_ostp_used_r[$i] + $dateParklot->used . "<br>"; echo $aps_ostp_contingent != 0 ? number_format(($apg_ostp_used_r[$i] + $dateParklot->used) / $aps_ostp_contingent * 100, 2,".",".") : "0.00"; echo "%";?></strong></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free - $apg_ostp_used_r[$i]  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format(($free - $apg_ostp_used_r[$i]) / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><input type="text" size="3" name="<?php echo $value->format('Y-m-d') . "_" . $dateParklot->product_id ?>" class="" value="<?php echo $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] != null ? $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] : "" ?>"></p>
										</div>									
									</div>
									<?php endif; ?>
									<?php if($dateParklot->order_lot == 54): ?>
									<div class="table-col">
										<?php
										if($dateParklot->contigent != 0){
											if(number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") >= 70 && number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") < 85)
												$text_color = "purple";
											elseif(number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") >= 85)
												$text_color = "red";										
											else
												$text_color = "";
										}
										else
											$text_color = "";
										
										if(number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") == 100)
											$background = "green";
										elseif($dateParklot->used > $dateParklot->contigent)
											$background = "yellow";
										else
											$background = "";
										?>	
										<div class="grid-three">
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><strong><?php echo $dateParklot->used . "<br>"; echo $dateParklot->contigent != 0 ? number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%";?></strong></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format($free / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><input type="text" size="3" name="<?php echo $value->format('Y-m-d') . "_" . $dateParklot->product_id ?>" class="" value="<?php echo $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] != null ? $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] : "" ?>"></p>
										</div>									
									</div>
									<?php endif; ?>
									<?php if($dateParklot->order_lot == 55): ?>
									<div class="table-col">
										<?php
										if($dateParklot->contigent != 0){
											if(number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") >= 70 && number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") < 85)
												$text_color = "purple";
											elseif(number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") >= 85)
												$text_color = "red";										
											else
												$text_color = "";
										}
										else
											$text_color = "";
										
										if(number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") == 100)
											$background = "green";
										elseif($dateParklot->used > $dateParklot->contigent)
											$background = "yellow";
										else
											$background = "";
										?>	
										<div class="grid-three">
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><strong><?php echo $dateParklot->used . "<br>"; echo $dateParklot->contigent != 0 ? number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%";?></strong></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format($free / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><input type="text" size="3" name="<?php echo $value->format('Y-m-d') . "_" . $dateParklot->product_id ?>" class="" value="<?php echo $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] != null ? $set_con[$value->format('Y-m-d')."_".$dateParklot->product_id] : "" ?>"></p>
										</div>									
									</div>
									<div class="table-col">
										<?php
										if($totalContigentOSTP != 0){
											if(number_format($totalUsedOSTP / $totalContigentOSTP * 100, 2,".",".") >= 70 && number_format($totalUsedOSTP / $totalContigentOSTP * 100, 2,".",".") < 85)
												$text_color = "purple";
											elseif(number_format($totalUsedOSTP / $totalContigentOSTP * 100, 2,".",".") >= 85)
												$text_color = "red";										
											else
												$text_color = "";
										}
										else
											$text_color = "";
										
										if(number_format($totalUsedOSTP / $totalContigentOSTP * 100, 2,".",".") == 100)
											$background = "green";
										elseif($totalUsedOSTP > $totalContigentOSTP)
											$background = "yellow";
										else
											$background = "";
										?>	
										<div class="grid-three">
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner tr-total-osta-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><strong><?php echo $totalUsedOSTP . " / "; echo $totalContigentOSTP != 0 ? number_format($totalUsedOSTP / $totalContigentOSTP * 100, 2,".",".") : "0.00"; echo "%"; ?></strong></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> tr-total-osta-<?php echo $i % 2; ?>"><?php echo $totalFreeOSTP . " / "; echo $totalContigentOSTP != 0 ? number_format($totalFreeOSTP / $totalContigentOSTP * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> ausen-border-right tr-total-osta-<?php echo $i % 2; ?>"><input type="text" size="3" class="" style="visibility: hidden;"></p>
										</div>
									</div>
									<?php endif; ?>
									<?php /// ?>
									
								<?php $k++; endforeach; ?>
								<?php
								// calculate row total from column total
								$totalTotal['used'] += $totalUsed;
								$totalTotal['free'] += $totalFree;
								$totalTotal['total'] += $totalContigent;										
								?>
								<div class="table-col">								
									<?php
									if($totalContigentAPS != 0){
										if(number_format($totalUsedAPS / $totalContigentAPS * 100, 2,".",".") >= 70 && number_format($totalUsedAPS / $totalContigentAPS * 100, 2,".",".") < 85)
											$text_color = "purple";
										elseif(number_format($totalUsedAPS / $totalContigentAPS * 100, 2,".",".") >= 85)
											$text_color = "red";										
										else
											$text_color = "";
									}
									else
										$text_color = "";
									
									if(number_format($totalUsedAPS / $totalContigentAPS * 100, 2,".",".") == 100)
										$background = "green";
									elseif($totalUsedAPS > $totalContigentAPS)
										$background = "yellow";
									else
										$background = "";
									?>	
									<div class="grid-three">
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner tr-total-ph-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><strong><?php echo $totalUsedAPS . "<br>"; echo $totalContigentAPS != 0 ? number_format($totalUsedAPS / $totalContigentAPS * 100, 2,".",".") : "0.00"; echo "%"; ?></strong></p>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> tr-total-ph-<?php echo $i % 2; ?>"><?php echo ($totalFreeAPS - $apg_ph_used - $apg_od_used - $apg_sie_used - $apg_osth_used - $apg_ostp_used) . "<br>"; echo $totalContigentAPS != 0 ? number_format(($totalFreeAPS - $apg_ph_used - $apg_od_used - $apg_sie_used - $apg_osth_used - $apg_ostp_used) / $totalContigentAPS * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> ausen-border-right tr-total-ph-<?php echo $i % 2; ?>"><input type="text" size="3" class="" style="visibility: hidden;"></p>
									</div>
								</div>
								<div class="table-col">								
									<?php
									if($totalContigentHEX != 0){
										if(number_format($totalUsedHEX / $totalContigentHEX * 100, 2,".",".") >= 70 && number_format($totalUsedHEX / $totalContigentHEX * 100, 2,".",".") < 85)
											$text_color = "purple";
										elseif(number_format($totalUsedHEX / $totalContigentHEX * 100, 2,".",".") >= 85)
											$text_color = "red";										
										else
											$text_color = "";
									}
									else
										$text_color = "";
									
									if(number_format($totalUsedHEX / $totalContigentHEX * 100, 2,".",".") == 100)
										$background = "green";
									elseif($totalUsedHEX > $totalContigentHEX)
										$background = "yellow";
									else
										$background = "";
									?>	
									<div class="grid-three">
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner tr-total-ph-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><strong><?php echo $totalUsedHEX . "<br>"; echo $totalContigentHEX != 0 ? number_format($totalUsedHEX / $totalContigentHEX * 100, 2,".",".") : "0.00"; echo "%"; ?></strong></p>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> tr-total-ph-<?php echo $i % 2; ?>"><?php echo $totalFreeHEX . "<br>"; echo $totalContigentHEX != 0 ? number_format($totalFreeHEX / $totalContigentHEX * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> ausen-border-right tr-total-ph-<?php echo $i % 2; ?>"><input type="text" size="3" class="" style="visibility: hidden;"></p>
									</div>
								</div>
								<div class="table-col">								
									<?php
									if($totalContigentPA != 0){
										if(number_format($totalUsedPA / $totalContigentPA * 100, 2,".",".") >= 70 && number_format($totalUsedPA / $totalContigentPA * 100, 2,".",".") < 85)
											$text_color = "purple";
										elseif(number_format($totalUsedPA / $totalContigentPA * 100, 2,".",".") >= 85)
											$text_color = "red";										
										else
											$text_color = "";
									}
									else
										$text_color = "";
									
									if(number_format($totalUsedPA / $totalContigentPA * 100, 2,".",".") == 100)
										$background = "green";
									elseif($totalUsedPA > $totalContigentPA)
										$background = "yellow";
									else
										$background = "";
									?>	
									<div class="grid-three">
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner tr-total-ph-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><strong><?php echo $totalUsedPA . "<br>"; echo $totalContigentPA != 0 ? number_format($totalUsedPA / $totalContigentPA * 100, 2,".",".") : "0.00"; echo "%"; ?></strong></p>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> tr-total-ph-<?php echo $i % 2; ?>"><?php echo $totalFreePA . "<br>"; echo $totalContigentPA != 0 ? number_format($totalFreePA / $totalContigentPA * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> ausen-border-right tr-total-ph-<?php echo $i % 2; ?>"><input type="text" size="3" class="" style="visibility: hidden;"></p>
									</div>
								</div>
								<div class="table-col">
									<?php
										if($totalContigent != 0){
											if(number_format($totalUsed / $totalContigent * 100, 2,".",".") >= 70 && number_format($totalUsed / $totalContigent * 100, 2,".",".") < 85)
												$text_color = "purple";
											elseif(number_format($totalUsed / $totalContigent * 100, 2,".",".") >= 85)
												$text_color = "red";										
											else
												$text_color = "";
										}
										else
											$text_color = "";
										
										if(number_format($totalUsed / $totalContigent * 100, 2,".",".") == 100)
											$background = "green";
										elseif($totalUsed > $totalContigent)
											$background = "yellow";
										else
											$background = "";
									?>	
									<div class="grid-three">
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> ausen-border-left tr-total-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><strong><?php echo $totalUsed . "<br>"; echo $totalContigent != 0 ? number_format($totalUsed / $totalContigent * 100, 2,".",".") : "0.00"; echo "%"; ?></strong></p>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> tr-total-<?php echo $i % 2; ?>"><?php echo $totalFree . "<br>"; echo $totalContigent != 0 ? number_format($totalFree / $totalContigent * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> ausen-border-right tr-total-<?php echo $i % 2; ?>"><input type="text" size="3" class="" style="visibility: hidden;"></p>
									</div>
								</div>							
								<div class="clearfix"></div>
							</div>
						<?php $i++; endforeach; ?>
					</div>
				</div>
			</div>
		</form>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
$('#bottom').on('scroll', function () {
    $('#top').scrollLeft($(this).scrollLeft());
});
</script>