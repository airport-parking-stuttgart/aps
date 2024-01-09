<?php
global $wpdb;
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
		<div class="TabelleV5-1">
			<div class="TabelleV5-2">
				<div class="parklots-data-table" id="top">
					<div class="table-col-wrapper">
						<div class="table-col" style="position: absolute; left: -13em;">						
							<p class="th th-space">&nbsp;</p>
							<p class="th-datum">Datum</p>
						</div>
						<?php $products = Database::getInstance()->getAllLots(); ?>
						<?php foreach ($products as $dateParklot) : ?>
							<?php if($dateParklot->order_lot == 1): ?>
								<div style="width: 310px;" class="table-col">
									<p class="th ausen-border-left ausen-border-top"><?php echo $dateParklot->parklot_short?></p>
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
									<p class="th ausen-border-top produkt-trenner"><?php echo $dateParklot->parklot_short . " + STRH"?></p>
									<div class="th-lots grid-three">
										<strong><p class="produkt-trenner">Gebucht</p></strong>
										<p>Frei</p>
										<p>Gesamt</p>
									</div>
								</div>
								<div class="table-col">
									<p class="th ausen-border-top ausen-border-right produkt-trenner">Gesamt PH</p>
									<div class="th-total-ph grid-three">
										<strong><p class="produkt-trenner">Gebucht</p></strong>
										<p>Frei</p>
										<p class="ausen-border-right">Gesamt</p>
									</div>
								</div>
							<?php endif; ?>
							<?php if($dateParklot->order_lot == 10): ?>
								<div style="width: 300px;" class="table-col">																			
									<p class="th ausen-border-left ausen-border-top"><?php echo $dateParklot->parklot_short?></p>
									<div class="th-lots grid-five">
										<p class="ausen-border-left">O APS</p>
										<p>O APG</p>
										<strong><p>Gebucht</p></strong>
										<p>Frei</p>
										<p>Gesamt</p>
									</div>
								</div>	
							<?php endif; ?>
							<?php if($dateParklot->order_lot == 14): ?>								
								<div class="table-col">																				
									<p class="th ausen-border-top produkt-trenner"><?php echo $dateParklot->parklot_short?></p>
									<div class="th-lots grid-three">
										<strong><p class="produkt-trenner">Gebucht</p></strong>
										<p>Frei</p>
										<p>Gesamt</p>
									</div>	
								</div>
								<div class="table-col">
									<p class="th ausen-border-top ausen-border-right produkt-trenner">Gesamt OD</p>
									<div class="th-total-od grid-three">
										<strong><p class="produkt-trenner">Gebucht</p></strong>
										<p>Frei</p>
										<p class="ausen-border-right">Gesamt</p>
									</div>
								</div>
							<?php endif; ?>
							<?php if($dateParklot->order_lot == 30): ?>
								<div style="width: 300px;" class="table-col">																				
									<p class="th ausen-border-left ausen-border-top"><?php echo $dateParklot->parklot_short?></p>
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
									<p class="th ausen-border-top produkt-trenner"><?php echo $dateParklot->parklot_short?></p>
									<div class="th-lots grid-three">
										<strong><p class="produkt-trenner">Gebucht</p></strong>
										<p>Frei</p>
										<p>Gesamt</p>
									</div>
								</div>	
							<?php endif; ?>
							<?php if($dateParklot->order_lot == 35): ?>
								<div class="table-col">																			
									<p class="th ausen-border-top produkt-trenner"><?php echo $dateParklot->parklot_short?></p>
									<div class="th-lots grid-three">
										<strong><p class="produkt-trenner">Gebucht</p></strong>
										<p>Frei</p>
										<p>Gesamt</p>
									</div>	
								</div>
								<div class="table-col">
									<p class="th ausen-border-top ausen-border-right">Gesamt SIE</p>
									<div class="th-total-si grid-three">
										<strong><p class="produkt-trenner">Gebucht</p></strong>
										<p>Frei</p>
										<p class="ausen-border-right">Gesamt</p>
									</div>
								</div>
							<?php endif; ?>
							<?php if($dateParklot->order_lot == 40): ?>
								<div style="width: 300px;" class="table-col">																				
									<p class="th ausen-border-left ausen-border-top"><?php echo $dateParklot->parklot_short?></p>
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
									<p class="th ausen-border-top produkt-trenner"><?php echo $dateParklot->parklot_short?></p>
									<div class="th-lots grid-three">
										<strong><p class="produkt-trenner">Gebucht</p></strong>
										<p>Frei</p>
										<p>Gesamt</p>
									</div>	
								</div>
							<?php endif; ?>
							<?php if($dateParklot->order_lot == 47): ?>
								<div class="table-col">																				
									<p class="th ausen-border-top produkt-trenner"><?php echo $dateParklot->parklot_short?></p>
									<div class="th-lots grid-three">
										<strong><p class="produkt-trenner">Gebucht</p></strong>
										<p>Frei</p>
										<p>Gesamt</p>
									</div>
								</div>
								<div class="table-col">
									<p class="th ausen-border-top ausen-border-right produkt-trenner">Gesamt OSTPH</p>
									<div class="th-total-ostph grid-three">
										<strong><p class="produkt-trenner">Gebucht</p></strong>
										<p>Frei</p>
										<p class="ausen-border-right">Gesamt</p>
									</div>
								</div>
							<?php endif; ?>
							<?php if($dateParklot->order_lot == 50): ?>
								<div style="width: 300px;" class="table-col">																			
									<p class="th ausen-border-left ausen-border-top"><?php echo $dateParklot->parklot_short?></p>
									<div class="th-lots grid-five">
										<p class="ausen-border-left">APS P</p>
										<p>APG P</p>
										<strong><p>Gebucht</p></strong>
										<p>Frei</p>
										<p>Gesamt</p>
									</div>
								</div>	
							<?php endif; ?>
							<?php if($dateParklot->order_lot == 54): ?>
								<div class="table-col">																				
									<p class="th ausen-border-top produkt-trenner"><?php echo $dateParklot->parklot_short?></p>
									<div class="th-lots grid-three">
										<strong><p class="produkt-trenner">Gebucht</p></strong>
										<p>Frei</p>
										<p>Gesamt</p>
									</div>	
								</div>	
							<?php endif; ?>
							<?php if($dateParklot->order_lot == 55): ?>
								<div class="table-col">																				
									<p class="th ausen-border-top produkt-trenner"><?php echo $dateParklot->parklot_short?></p>
									<div class="th-lots grid-three">
										<strong><p class="produkt-trenner">Gebucht</p></strong>
										<p>Frei</p>
										<p>Gesamt</p>
									</div>	
								</div>	
								<div class="table-col">
									<p class="th ausen-border-top ausen-border-right produkt-trenner">Gesamt OSTP</p>
									<div class="th-total-osta grid-three">
										<strong><p class="produkt-trenner">Gebucht</p></strong>
										<p>Frei</p>
										<p class="ausen-border-right">Gesamt</p>
									</div>
								</div>
							<?php endif; ?>							
						<?php endforeach; ?>
						<div class="table-col">
							<p class="th ausen-border-left ausen-border-top ausen-border-right">Summe</p>
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
						//$totalUsedBERN = 0;
						//$totalFreeBERN = 0;
						//$totalContigentBERN = 0;
						$totalUsedSIE = 0;
						$totalFreeSIE = 0;
						$totalContigentSIE = 0;
						$totalUsedOSTH = 0;
						$totalFreeOSTH = 0;
						$totalContigentOSTH = 0;
						//$totalUsedOSTHV = 0;
						//$totalFreeOSTHV = 0;
						//$totalContigentOSTHV = 0;
						$totalUsedOSTP = 0;
						$totalFreeOSTP = 0;
						$totalContigentOSTP = 0;
						$av += 1;
						?>
						<div class="table-col-wrapper">
							<?php
							$dateParklots = Database::getInstance()->getParkotsWithOrdersData($value->format('Y-m-d'));
							$lastItem = !next($period);
							?>
							<div class="table-col" style="position: absolute; left: -13em;">
								<p class="th-datum-r tr-datum-<?php echo $i % 2; ?>"><?php echo "<br>" . $wochentage[date("w", strtotime($value->format('d.m.Y')))] . " " . $value->format('d.m.Y') ?></p>
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
								<?php //if($dateParklot->order_lot == 2 || $dateParklot->order_lot == 11 || $dateParklot->order_lot == 21 || $dateParklot->order_lot >= 31 || $dateParklot->order_lot >= 41 || $dateParklot->order_lot >= 51) continue; ?>
								<?php
								if (!isset($total[$dateParklot->parklot_short])) {
									$total[$dateParklot->parklot_short] = [
										'used' => 0,
										'free' => 0,
										'total' => 0
									];
								}								

								$free = ((int)$dateParklot->contigent - (int)$dateParklot->used) > 0 ?
									(int)$dateParklot->contigent - (int)$dateParklot->used : 0;
								$totalUsed += (int)$dateParklot->used;
								$totalFree += $free;
								$totalContigent += (int)$dateParklot->contigent;								
								
								/// Parkhaus
								if($dateParklot->order_lot == 5){
									(int)$dateParklot->used += $totalUsedSTRH[$i];
									(int)$dateParklot->contigent += $totalContigentSTRH[$i];
									$free -= $totalUsedSTRH[$i];
									$totalUsed += $totalUsedSTRH[$i];
									$totalFree -= $totalUsedSTRH[$i];
								}
								
								if($dateParklot->order_lot == 1 || $dateParklot->order_lot == 3 || $dateParklot->order_lot == 5){
									$freePH = ((int)$dateParklot->contigent - (int)$dateParklot->used) > 0 ?
									(int)$dateParklot->contigent - (int)$dateParklot->used : 0;
									$totalUsedPH += (int)$dateParklot->used;
									$totalFreePH += $freePH;
									$totalContigentPH += (int)$dateParklot->contigent;							
									
									$totalTotalUsedPH += (int)$dateParklot->used;
									$totalTotalFreePH += $freePH;
									$totalTotalContigentPH += $totalContigentPH;							
								}
								if($dateParklot->order_lot == 1){
									$aps_ph_used = (int)$dateParklot->used;
									$aps_ph_free = ((int)$dateParklot->contigent - (int)$dateParklot->used) > 0 ? (int)$dateParklot->contigent - (int)$dateParklot->used : 0;
									$aps_ph_contingent = (int)$dateParklot->contigent;	
								}
								if($dateParklot->order_lot == 3){
									$apg_ph_used = (int)$dateParklot->used;
									$apg_ph_free = 0;
									$totalTotalUsedPH -= $apg_ph_used;
									$totalTotalFreePH -= $aps_ph_free;
									$totalFree -= $apg_ph_used;
								}								
								///
								
								/// Oberdeck
								if($dateParklot->order_lot == 10 || $dateParklot->order_lot == 12 || $dateParklot->order_lot == 14){
									$freeOD = ((int)$dateParklot->contigent - (int)$dateParklot->used) > 0 ?
									(int)$dateParklot->contigent - (int)$dateParklot->used : 0;
									$totalUsedOD += (int)$dateParklot->used;
									$totalFreeOD += $freeOD;
									$totalContigentOD += (int)$dateParklot->contigent;
									
									$totalTotalUsedOD += (int)$dateParklot->used;
									$totalTotalFreeOD += $freeOD;
									$totalTotalContigentOD += (int)$dateParklot->contigent;
								}
								
								if($dateParklot->order_lot == 10){
									$aps_od_used = (int)$dateParklot->used;
									$aps_od_free = ((int)$dateParklot->contigent - (int)$dateParklot->used) > 0 ? (int)$dateParklot->contigent - (int)$dateParklot->used : 0;
									$aps_od_contingent = (int)$dateParklot->contigent;	
								}
								
								if($dateParklot->order_lot == 12){
									$apg_od_used = (int)$dateParklot->used;
									$apg_od_free = 0;
									$totalTotalUsedOD -= $apg_od_used;
									$totalTotalFreeOD -= $aps_od_free;
									$totalFree -= $apg_od_used;
								}
								///
								
								
								/*
								/// Bernhausen
								if($dateParklot->order_lot == 20 || $dateParklot->order_lot == 21 || $dateParklot->order_lot == 22 || $dateParklot->order_lot == 23 || $dateParklot->order_lot == 24){
									$freeBERN = ((int)$dateParklot->contigent - (int)$dateParklot->used) > 0 ?
									(int)$dateParklot->contigent - (int)$dateParklot->used : 0;
									$totalUsedBERN += (int)$dateParklot->used;
									$totalFreeBERN += $freeBERN;
									$totalContigentBERN += (int)$dateParklot->contigent;
									
									$totalTotalUsedBERN += (int)$dateParklot->used;
									$totalTotalFreeBERN += $freeBERN;
									$totalTotalContigentBERN += (int)$dateParklot->contigent;
								}
								
								if($dateParklot->order_lot == 20){
									$aps_bern_used = (int)$dateParklot->used;
									$aps_bern_free = ((int)$dateParklot->contigent - (int)$dateParklot->used) > 0 ? (int)$dateParklot->contigent - (int)$dateParklot->used : 0;
									$aps_bern_contingent = (int)$dateParklot->contigent;	
								}
								
								if($dateParklot->order_lot == 21){
									$apg_bern_used = (int)$dateParklot->used;
									$apg_bern_free = 0;
									$totalTotalUsedBERN -= $apg_bern_used;
									$totalTotalFreeBERN -= $aps_bern_free;
									$totalFree -= $apg_bern_used;
								}
								///
								*/
								
								/// Sielmingen
								if($dateParklot->order_lot == 30 || $dateParklot->order_lot == 32 || $dateParklot->order_lot == 34 || $dateParklot->order_lot == 35){
									$freeSIE = ((int)$dateParklot->contigent - (int)$dateParklot->used) > 0 ?
									(int)$dateParklot->contigent - (int)$dateParklot->used : 0;
									$totalUsedSIE += (int)$dateParklot->used;
									$totalFreeSIE += $freeSIE;
									$totalContigentSIE += (int)$dateParklot->contigent;
									
									$totalTotalUsedSIE += (int)$dateParklot->used;
									$totalTotalFreeSIE += $freeSIE;
									$totalTotalContigentSIE += (int)$dateParklot->contigent;
								}
								
								if($dateParklot->order_lot == 30){
									$aps_sie_used = (int)$dateParklot->used;
									$aps_sie_free = ((int)$dateParklot->contigent - (int)$dateParklot->used) > 0 ? (int)$dateParklot->contigent - (int)$dateParklot->used : 0;
									$aps_sie_contingent = (int)$dateParklot->contigent;	
								}
								
								if($dateParklot->order_lot == 32){
									$apg_sie_used = (int)$dateParklot->used;
									$apg_sie_free = 0;
									$totalTotalUsedSIE -= $apg_sie_used;
									$totalTotalFreeSIE -= $aps_sie_free;
									$totalFree -= $apg_sie_used;
								}
								///
								
								/// Parkhalle
								if($dateParklot->order_lot == 40 || $dateParklot->order_lot == 42 || $dateParklot->order_lot == 44 || $dateParklot->order_lot == 47){
									$freeOSTH = ((int)$dateParklot->contigent - (int)$dateParklot->used) > 0 ?
									(int)$dateParklot->contigent - (int)$dateParklot->used : 0;
									$totalUsedOSTH += (int)$dateParklot->used;
									$totalFreeOSTH += $freeOSTH;
									$totalContigentOSTH += (int)$dateParklot->contigent;
									
									$totalTotalUsedOSTH += (int)$dateParklot->used;
									$totalTotalFreeOSTH += $freeOSTH;
									$totalTotalContigentOSTH += (int)$dateParklot->contigent;
								}
								if($dateParklot->order_lot == 40){
									$aps_osth_used = (int)$dateParklot->used;
									$aps_osth_free = ((int)$dateParklot->contigent - (int)$dateParklot->used) > 0 ? (int)$dateParklot->contigent - (int)$dateParklot->used : 0;
									$aps_osth_contingent = (int)$dateParklot->contigent;	
								}
								if($dateParklot->order_lot == 42){
									$apg_osth_used = (int)$dateParklot->used;
									$apg_osth_free = 0;
									$totalTotalUsedOSTH -= $apg_osth_used;
									$totalTotalFreeOSTH -= $aps_osth_free;
									$totalFree -= $apg_osth_used;
								}
								///
								
								/// Parkplatz
								if($dateParklot->order_lot == 50 || $dateParklot->order_lot == 52 || $dateParklot->order_lot == 54 || $dateParklot->order_lot == 55){
									$freeOSTP = ((int)$dateParklot->contigent - (int)$dateParklot->used) > 0 ?
									(int)$dateParklot->contigent - (int)$dateParklot->used : 0;
									$totalUsedOSTP += (int)$dateParklot->used;
									$totalFreeOSTP += $freeOSTP;
									$totalContigentOSTP += (int)$dateParklot->contigent;
									
									$totalTotalUsedOSTP += (int)$dateParklot->used;
									$totalTotalFreeOSTP += $freeOSTP;
									$totalTotalContigentOSTP += (int)$dateParklot->contigent;
								}
								if($dateParklot->order_lot == 50){
									$aps_ostp_used = (int)$dateParklot->used;
									$aps_ostp_free = ((int)$dateParklot->contigent - (int)$dateParklot->used) > 0 ? (int)$dateParklot->contigent - (int)$dateParklot->used : 0;
									$aps_ostp_contingent = (int)$dateParklot->contigent;	
								}
								if($dateParklot->order_lot == 52){
									$apg_ostp_used = (int)$dateParklot->used;
									$apg_ostp_free = 0;
									$totalTotalUsedOSTP -= $apg_ostp_used;
									$totalTotalFreeOSTP -= $aps_ostp_free;
									$totalFree -= $apg_ostp_used;
								}
								///
								

								// calculate parklot used, free, contigent in all dates
								$total[$dateParklot->parklot_short]['used'] += (int)$dateParklot->used;
								$total[$dateParklot->parklot_short]['free'] += $free;
								$total[$dateParklot->parklot_short]['total'] += (int)$dateParklot->contigent;
								
								
								?>
								<?php /// Parkhaus ?>
								<?php if($dateParklot->order_lot == 1): ?>
								<div style="width: 310px;" class="table-col">
									
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
										<strong><p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?> <?php echo $background ?>"><?php echo $apg_ph_used_r[$i] + $dateParklot->used . "<br>"; echo $aps_ph_contingent != 0 ? number_format(($apg_ph_used_r[$i] + $dateParklot->used) / $aps_ph_contingent * 100, 2,".",".") : "0.00"; echo "%";?></p></strong>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free - $apg_ph_used_r[$i]  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format(($free - $apg_ph_used_r[$i]) / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> total-val tr-lots-<?php echo $i % 2; ?>"><?php echo $dateParklot->contigent ?></p>
									</div>									
								</div>
								<?php endif; ?>
								<?php if($dateParklot->order_lot == 3): ?>								
								<!--<div class="table-col">
									<?php if ($key == 0) : ?>																				
										<p class="th"><?php echo "APS PH + " . $dateParklot->parklot_short?></p>
										<div class="th-lots grid-three">
											<p>Gebucht</p>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>																			
									<?php endif; ?>
									<div class="grid-three">
										<p class="used-val tr-lots-<?php echo $i % 2; ?>">
											<?php echo ($aps_ph_used + $apg_ph_used) . "<br>"; echo $aps_ph_contingent != 0 ? number_format(($aps_ph_used + $apg_ph_used) / $aps_ph_contingent * 100, 2,".",".") : "0.00"; echo "%"; ?>
										</p>
										<p class="free-val tr-lots-<?php echo $i % 2; ?>"><?php echo ($aps_ph_free - $apg_ph_used) . "<br>"; echo $aps_ph_contingent != 0 ?  number_format(($aps_ph_free - $apg_ph_used) / $aps_ph_contingent * 100, 2,".",".") : "0.00"; echo "%";?></p>
										<p class="total-val tr-lots-<?php echo $i % 2; ?>"><?php echo $aps_ph_contingent ?></p>
									</div>									
								</div>-->
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
										<strong><p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><?php echo $dateParklot->used . "<br>"; echo $dateParklot->contigent != 0 ? number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%";?></p></strong>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format($free / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> total-val tr-lots-<?php echo $i % 2; ?>"><?php echo $dateParklot->contigent ?></p>
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
										<strong><p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner tr-total-ph-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><?php echo $totalUsedPH . "<br>"; echo $totalContigentPH != 0 ? number_format($totalUsedPH / $totalContigentPH * 100, 2,".",".") : "0.00"; echo "%"; ?></p></strong>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> tr-total-ph-<?php echo $i % 2; ?>"><?php echo ($totalFreePH - $apg_ph_used) . "<br>"; echo $totalContigentPH != 0 ? number_format(($totalFreePH - $apg_ph_used) / $totalContigentPH * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> ausen-border-right tr-total-ph-<?php echo $i % 2; ?>"><?php echo $totalContigentPH ?></p>
									</div>
								</div>
									<?php 
									$total['Summe PH']['used'] = $totalTotalUsedPH;
									$total['Summe PH']['free'] = $totalTotalFreePH;
									$total['Summe PH']['total'] = $totalTotalContigentPH;		
									?>
								<?php endif; ?>
								<?php /// ?>
								
								<?php /// Oberdeck ?>
								<?php if($dateParklot->order_lot == 10): ?>
								<div style="width: 300px;" class="table-col">
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
										<strong><p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><?php echo $apg_od_used_r[$i] + $dateParklot->used . "<br>"; echo $aps_od_contingent != 0 ? number_format(($apg_od_used_r[$i] + $dateParklot->used) / $aps_od_contingent * 100, 2,".",".") : "0.00"; echo "%";?></p></strong>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format($free / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> total-val tr-lots-<?php echo $i % 2; ?>"><?php echo $dateParklot->contigent ?></p>
									</div>									
								</div>
								<?php endif; ?>
								<?php if($dateParklot->order_lot == 12): ?>				
								<!--<div class="table-col">
									<?php if ($key == 0) : ?>																				
										<p class="th"><?php echo "OAPS + " . $dateParklot->parklot_short?></p>
										<div class="th-lots grid-three">
											<p>Gebucht</p>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>																			
									<?php endif; ?>
									<div class="grid-three">
										<p class="used-val tr-lots-<?php echo $i % 2; ?>">
											<?php echo ($aps_od_used + $apg_od_used) . "<br>"; echo $aps_od_contingent != 0 ? number_format(($aps_od_used + $apg_od_used) / $aps_od_contingent * 100, 2,".",".") : "0.00"; echo "%"; ?>
										</p>
										<p class="free-val tr-lots-<?php echo $i % 2; ?>"><?php echo ($aps_od_free - $apg_od_used) . "<br>"; echo $aps_od_contingent != 0 ?  number_format(($aps_od_free - $apg_od_used) / $aps_od_contingent * 100, 2,".",".") : "0.00"; echo "%";?></p>
										<p class="total-val tr-lots-<?php echo $i % 2; ?>"><?php echo $aps_od_contingent ?></p>
									</div>									
								</div>-->
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
										<strong><p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><?php echo $dateParklot->used . "<br>"; echo $dateParklot->contigent != 0 ? number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%";?></p></strong>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format($free / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> total-val tr-lots-<?php echo $i % 2; ?>"><?php echo $dateParklot->contigent ?></p>
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
										<strong><p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner tr-total-od-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><?php echo $totalUsedOD . "<br>"; echo $totalContigentOD != 0 ? number_format($totalUsedOD / $totalContigentOD * 100, 2,".",".") : "0.00"; echo "%"; ?></p></strong>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> tr-total-od-<?php echo $i % 2; ?>"><?php echo ($totalFreeOD - $apg_od_used) . "<br>"; echo $totalContigentOD != 0 ? number_format(($totalFreeOD - $apg_od_used) / $totalContigentOD * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> ausen-border-right tr-total-od-<?php echo $i % 2; ?>"><?php echo $totalContigentOD ?></p>
									</div>
								</div>
									<?php 
									$total['Summe OD']['used'] = $totalTotalUsedOD;
									$total['Summe OD']['free'] = $totalTotalFreeOD;
									$total['Summe OD']['total'] = $totalTotalContigentOD;		
									?>
								<?php endif; ?>
								<?php /// ?>
								
								<?php /// Sielmingen ?>
								<?php if($dateParklot->order_lot == 30): ?>
								<div style="width: 300px;" class="table-col">
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
										<strong><p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><?php echo $apg_sie_used_r[$i] + $dateParklot->used . "<br>"; echo $aps_sie_contingent != 0 ? number_format(($apg_sie_used_r[$i] + $dateParklot->used) / $aps_sie_contingent * 100, 2,".",".") : "0.00"; echo "%";?></p></strong>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format($free / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> total-val tr-lots-<?php echo $i % 2; ?>"><?php echo $dateParklot->contigent ?></p>
									</div>									
								</div>
								<?php endif; ?>
								<?php if($dateParklot->order_lot == 32): ?>
								<!--<div class="table-col">
									<?php if ($key == 0) : ?>																				
										<p class="th"><?php echo "APS S + " . $dateParklot->parklot_short?></p>
										<div class="th-lots grid-three">
											<p>Gebucht</p>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>																			
									<?php endif; ?>
									<div class="grid-three">
										<p class="used-val tr-lots-<?php echo $i % 2; ?>">
											<?php echo ($aps_sie_used + $apg_sie_used) . "<br>"; echo $aps_sie_contingent != 0 ? number_format(($aps_sie_used + $apg_sie_used) / $aps_sie_contingent * 100, 2,".",".") : "0.00"; echo "%"; ?>
										</p>
										<p class="free-val tr-lots-<?php echo $i % 2; ?>"><?php echo ($aps_sie_free - $apg_sie_used) . "<br>"; echo $aps_sie_contingent != 0 ?  number_format(($aps_sie_free - $apg_sie_used) / $aps_sie_contingent * 100, 2,".",".") : "0.00"; echo "%";?></p>
										<p class="total-val tr-lots-<?php echo $i % 2; ?>"><?php echo $aps_sie_contingent ?></p>
									</div>									
								</div>-->
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
										<strong><p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><?php echo $dateParklot->used . "<br>"; echo $dateParklot->contigent != 0 ? number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%";?></p></strong>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format($free / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> total-val tr-lots-<?php echo $i % 2; ?>"><?php echo $dateParklot->contigent ?></p>
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
										<strong><p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><?php echo $dateParklot->used . "<br>"; echo $dateParklot->contigent != 0 ? number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%";?></p></strong>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format($free / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> total-val tr-lots-<?php echo $i % 2; ?>"><?php echo $dateParklot->contigent ?></p>
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
											<strong><p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner tr-total-si-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><?php echo $totalUsedSIE . "<br>"; echo $totalContigentSIE != 0 ? number_format($totalUsedSIE / $totalContigentSIE * 100, 2,".",".") : "0.00"; echo "%"; ?></p></strong>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> tr-total-si-<?php echo $i % 2; ?>"><?php echo ($totalFreeSIE - $apg_sie_used) . "<br>"; echo $totalContigentSIE != 0 ? number_format(($totalFreeSIE - $apg_sie_used) / $totalContigentSIE * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
											<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> ausen-border-right tr-total-si-<?php echo $i % 2; ?>"><?php echo $totalContigentSIE ?></p>
										</div>
								</div>
									<?php 
									$total['Summe SIE']['used'] = $totalTotalUsedSIE;
									$total['Summe SIE']['free'] = $totalTotalFreeSIE;
									$total['Summe SIE']['total'] = $totalTotalContigentSIE;		
									?>
								<?php endif; ?>
								<?php /// ?>
								
								<?php /// Parkhalle ?>
								<?php if($dateParklot->order_lot == 40): ?>
								<div style="width: 300px;" class="table-col">
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
										<strong><p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><?php echo $apg_osth_used_r[$i] + $dateParklot->used . "<br>"; echo $aps_osth_contingent != 0 ? number_format(($apg_osth_used_r[$i] + $dateParklot->used) / $aps_osth_contingent * 100, 2,".",".") : "0.00"; echo "%";?></p></strong>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format($free / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> total-val tr-lots-<?php echo $i % 2; ?>"><?php echo $dateParklot->contigent ?></p>
									</div>									
								</div>
								<?php endif; ?>
								<?php if($dateParklot->order_lot == 42): ?>						
								<!--<div class="table-col">
									<?php if ($key == 0) : ?>																				
										<p class="th"><?php echo "APS OSTPH + " . $dateParklot->parklot_short?></p>
										<div class="th-lots grid-three">
											<p>Gebucht</p>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>																			
									<?php endif; ?>
									<div class="grid-three">
										<p class="used-val tr-lots-<?php echo $i % 2; ?>">
											<?php echo ($aps_osth_used + $apg_osth_used) . "<br>"; echo $aps_osth_contingent != 0 ? number_format(($aps_osth_used + $apg_osth_used) / $aps_osth_contingent * 100, 2,".",".") : "0.00"; echo "%"; ?>
										</p>
										<p class="free-val tr-lots-<?php echo $i % 2; ?>"><?php echo ($aps_osth_free - $apg_osth_used) . "<br>"; echo $aps_osth_contingent != 0 ?  number_format(($aps_osth_free - $apg_osth_used) / $aps_osth_contingent * 100, 2,".",".") : "0.00"; echo "%";?></p>
										<p class="total-val tr-lots-<?php echo $i % 2; ?>"><?php echo $aps_osth_contingent ?></p>
									</div>									
								</div>-->
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
										<strong><p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><?php echo $dateParklot->used . "<br>"; echo $dateParklot->contigent != 0 ? number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%";?></p></strong>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format($free / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> total-val tr-lots-<?php echo $i % 2; ?>"><?php echo $dateParklot->contigent ?></p>
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
										<strong><p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><?php echo $dateParklot->used . "<br>"; echo $dateParklot->contigent != 0 ? number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%";?></p></strong>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format($free / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> total-val tr-lots-<?php echo $i % 2; ?>"><?php echo $dateParklot->contigent ?></p>
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
										<strong><p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner tr-total-ostph-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><?php echo $totalUsedOSTH . "<br>"; echo $totalContigentOSTH != 0 ? number_format($totalUsedOSTH / $totalContigentOSTH * 100, 2,".",".") : "0.00"; echo "%"; ?></p></strong>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> tr-total-ostph-<?php echo $i % 2; ?>"><?php echo ($totalFreeOSTH - $apg_osth_used) . "<br>"; echo $totalContigentOSTH != 0 ? number_format(($totalFreeOSTH - $apg_osth_used) / $totalContigentOSTH * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> ausen-border-right tr-total-ostph-<?php echo $i % 2; ?>"><?php echo $totalContigentOSTH ?></p>
									</div>
								</div>
									<?php 
									$total['Summe OSTH']['used'] = $totalTotalUsedOSTH;
									$total['Summe OSTH']['free'] = $totalTotalFreeOSTH;
									$total['Summe OSTH']['total'] = $totalTotalContigentOSTH;		
									?>
								<?php endif; ?>
								<?php /// ?>
								
								<?php /// Parkplatz ?>
								<?php if($dateParklot->order_lot == 50): ?>
								<div style="width: 300px;" class="table-col">
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
										<strong><p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><?php echo $apg_ostp_used_r[$i] + $dateParklot->used . "<br>"; echo $aps_ostp_contingent != 0 ? number_format(($apg_ostp_used_r[$i] + $dateParklot->used) / $aps_ostp_contingent * 100, 2,".",".") : "0.00"; echo "%";?></p></strong>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format($free / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> total-val tr-lots-<?php echo $i % 2; ?>"><?php echo $dateParklot->contigent ?></p>
									</div>									
								</div>
								<?php endif; ?>
								<?php if($dateParklot->order_lot == 52): ?>
								<!--<div class="table-col">
									<?php if ($key == 0) : ?>																				
										<p class="th"><?php echo $dateParklot->parklot_short?></p>
										<div class="th-lots grid-three">
											<p>Gebucht</p>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>																				
									<?php endif; ?>
									<div class="grid-three">
										<p class="used-val tr-lots-<?php echo $i % 2; ?>"><?php echo $dateParklot->used . "<br>"; echo $aps_osth_contingent != 0 ? number_format($dateParklot->used / $aps_osth_contingent * 100, 2,".",".") : "0.00"; echo "%";?></p>
										<p class="free-val tr-lots-<?php echo $i % 2; ?>"><?php echo "-" ?></p>
										<p class="total-val tr-lots-<?php echo $i % 2; ?>"><?php echo "-" ?></p>
									</div>									
								</div>
								<div class="table-col">
									<?php if ($key == 0) : ?>																				
										<p class="th"><?php echo "APS OSTNÜ + " . $dateParklot->parklot_short?></p>
										<div class="th-lots grid-three">
											<p>Gebucht</p>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>																			
									<?php endif; ?>
									<div class="grid-three">
										<p class="used-val tr-lots-<?php echo $i % 2; ?>">
											<?php echo ($aps_ostp_used + $apg_ostp_used) . "<br>"; echo $aps_ostp_contingent != 0 ? number_format(($aps_ostp_used + $apg_ostp_used) / $aps_ostp_contingent * 100, 2,".",".") : "0.00"; echo "%"; ?>
										</p>
										<p class="free-val tr-lots-<?php echo $i % 2; ?>"><?php echo ($aps_ostp_free - $apg_ostp_used) . "<br>"; echo $aps_ostp_contingent != 0 ?  number_format(($aps_ostp_free - $apg_ostp_used) / $aps_ostp_contingent * 100, 2,".",".") : "0.00"; echo "%";?></p>
										<p class="total-val tr-lots-<?php echo $i % 2; ?>"><?php echo $aps_ostp_contingent ?></p>
									</div>									
								</div>-->
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
										<strong><p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><?php echo $dateParklot->used . "<br>"; echo $dateParklot->contigent != 0 ? number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%";?></p></strong>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format($free / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> total-val tr-lots-<?php echo $i % 2; ?>"><?php echo $dateParklot->contigent ?></p>
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
										<strong><p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner used-val tr-lots-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><?php echo $dateParklot->used . "<br>"; echo $dateParklot->contigent != 0 ? number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%";?></p></strong>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free  . "<br>"; echo $dateParklot->contigent != 0 ?  number_format($free / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> total-val tr-lots-<?php echo $i % 2; ?>"><?php echo $dateParklot->contigent ?></p>
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
										<strong><p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> produkt-trenner tr-total-osta-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><?php echo $totalUsedOSTP . " / "; echo $totalContigentOSTP != 0 ? number_format($totalUsedOSTP / $totalContigentOSTP * 100, 2,".",".") : "0.00"; echo "%"; ?></p></strong>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> tr-total-osta-<?php echo $i % 2; ?>"><?php echo ($totalFreeOSTP - $apg_ostp_used) . " / "; echo $totalContigentOSTP != 0 ? number_format(($totalFreeOSTP - $apg_ostp_used) / $totalContigentOSTP * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
										<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> ausen-border-right tr-total-osta-<?php echo $i % 2; ?>"><?php echo $totalContigentOSTP ?></p>
									</div>
								</div>
									<?php 
									$total['Summe OSTP']['used'] = $totalTotalUsedOSTP;
									$total['Summe OSTP']['free'] = $totalTotalFreeOSTP;
									$total['Summe OSTP']['total'] = $totalTotalContigentOSTP;		
									?>
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
									<strong><p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> ausen-border-left tr-total-<?php echo $i % 2; ?> <?php echo $text_color ?>  <?php echo $background ?>"><?php echo $totalUsed . "<br>"; echo $totalContigent != 0 ? number_format($totalUsed / $totalContigent * 100, 2,".",".") : "0.00"; echo "%"; ?></p></strong>
									<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> tr-total-<?php echo $i % 2; ?>"><?php echo $totalFree . "<br>"; echo $totalContigent != 0 ? number_format($totalFree / $totalContigent * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
									<p class="<?php echo $i == $dates-1 ? "ausen-border-bottom" : "" ?> ausen-border-right tr-total-<?php echo $i % 2; ?>"><?php echo $totalContigent ?></p>
								</div>
							</div>
							<div class="clearfix"></div>
						</div>
					<?php $i++; endforeach; 
										
					?>
					<!--<div class="table-col-wrapper">
						<div class="table-col" style="position: absolute; left: -13em;">
							<p class="tr-total th-total">Durchschnitt</p>
						</div>
						<?php foreach ($total as $item) : ?>
							<div class="table-col">
								<div class="grid-three">
									<p class="tr-total"><?php echo number_format($item['used'] / $av,2,".",".") ?></p>
									<p class="tr-total"><?php echo number_format($item['free']  / $av,2,".",".") ?></p>
									<p class="tr-total"><?php echo number_format($item['total'] / $av,0,".",".") ?></p>
								</div>
							</div>
						<?php endforeach; ?>
						<div class="table-col">
							<div class="grid-three">
								<p class="tr-total"><?php echo number_format($totalTotal['used'] / $av,2,".",".") ?></p>
								<p class="tr-total"><?php echo number_format($totalTotal['free'] / $av,2,".",".") ?></p>
								<p class="tr-total"><?php echo number_format($totalTotal['total'] / $av,0,".",".") ?></p>
							</div>
						</div>
						<div class="clearfix"></div>
					</div>-->
				</div>
			</div>
		</div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
$('#bottom').on('scroll', function () {
    $('#top').scrollLeft($(this).scrollLeft());
});
</script>