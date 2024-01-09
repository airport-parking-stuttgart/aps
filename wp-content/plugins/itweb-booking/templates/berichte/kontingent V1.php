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
        new DateTime($today . '+30 day')
    );

    $date = [
        $period->start->format('Y-m-d'),
        $period->end->format('Y-m-d')
    ];
}
$wochentage = array("So.", "Mo.", "Di.", "Mi.", "Do.", "Fr.", "Sa.");

?>

<style>
div.TabelleV5-1 {padding-left:13em;}
div.TabelleV5-2 {position:relative;width:100%; padding:0px;}
.th-space{border-top: 1px solid #909090; border-left: 1px solid #909090;}
.th-total, .th-datum-r, .th-datum{border-left: 1px solid #909090;}
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
				<div class="parklots-data-table">
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
						$k = 0; $l = 0;
						foreach ($period as $key => $value){
							$dateParklots = Database::getInstance()->getParkotsWithOrdersData($value->format('Y-m-d'));
							foreach ($dateParklots as $dateParklot){
								if($dateParklot->order_lot == 6){
									$freeSTRH[$k] = ((int)$dateParklot->contigent - (int)$dateParklot->used) > 0 ?
									(int)$dateParklot->contigent - (int)$dateParklot->used : 0;
									$totalUsedSTRH[$k] += (int)$dateParklot->used;
									$totalFreeSTRH[$k] += $freeSTRH[$k];
									$totalContigentSTRH[$k] += (int)$dateParklot->contigent;
									$k++;
								}
								
							}
						}
					?>
					
					
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
								<?php if ($key == 0) : ?>
									<p class="th th-space">&nbsp;</p>
									<p class="th-datum">Datum</p>
								<?php endif; ?>
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
								if($dateParklot->order_lot == 5){
									(int)$dateParklot->used += $totalUsedSTRH[$l];
									(int)$dateParklot->contigent += $totalContigentSTRH[$l];
									$l++;
								}

								$free = ((int)$dateParklot->contigent - (int)$dateParklot->used) > 0 ?
									(int)$dateParklot->contigent - (int)$dateParklot->used : 0;
								$totalUsed += (int)$dateParklot->used;
								$totalFree += $free;
								$totalContigent += (int)$dateParklot->contigent;
								
								if($dateParklot->order_lot == 1 /*|| $dateParklot->order_lot == 2*/ || $dateParklot->order_lot == 3 /*|| $dateParklot->order_lot == 4*/ || $dateParklot->order_lot == 5){
									$freePH = ((int)$dateParklot->contigent - (int)$dateParklot->used) > 0 ?
									(int)$dateParklot->contigent - (int)$dateParklot->used : 0;
									$totalUsedPH += (int)$dateParklot->used;
									$totalFreePH += $freePH;
									$totalContigentPH += (int)$dateParklot->contigent;							
									
									$totalTotalUsedPH += (int)$dateParklot->used;
									$totalTotalFreePH += $freePH;
									$totalTotalContigentPH += $totalContigentPH;							
								}
								if($dateParklot->order_lot == 10 /*|| $dateParklot->order_lot == 11*/ || $dateParklot->order_lot == 12 /*|| $dateParklot->order_lot == 13*/ || $dateParklot->order_lot == 14){
									$freeOD = ((int)$dateParklot->contigent - (int)$dateParklot->used) > 0 ?
									(int)$dateParklot->contigent - (int)$dateParklot->used : 0;
									$totalUsedOD += (int)$dateParklot->used;
									$totalFreeOD += $freeOD;
									$totalContigentOD += (int)$dateParklot->contigent;
									
									$totalTotalUsedOD += (int)$dateParklot->used;
									$totalTotalFreeOD += $freeOD;
									$totalTotalContigentOD += (int)$dateParklot->contigent;
								}
								/*
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
								*/
								if($dateParklot->order_lot == 30 /*|| $dateParklot->order_lot == 31*/ || $dateParklot->order_lot == 32 /*|| $dateParklot->order_lot == 33*/ || $dateParklot->order_lot == 34 || $dateParklot->order_lot == 35){
									$freeSIE = ((int)$dateParklot->contigent - (int)$dateParklot->used) > 0 ?
									(int)$dateParklot->contigent - (int)$dateParklot->used : 0;
									$totalUsedSIE += (int)$dateParklot->used;
									$totalFreeSIE += $freeSIE;
									$totalContigentSIE += (int)$dateParklot->contigent;
									
									$totalTotalUsedSIE += (int)$dateParklot->used;
									$totalTotalFreeSIE += $freeSIE;
									$totalTotalContigentSIE += (int)$dateParklot->contigent;
								}
								if($dateParklot->order_lot == 40 /*|| $dateParklot->order_lot == 41*/ || $dateParklot->order_lot == 42 /*|| $dateParklot->order_lot == 43*/ || $dateParklot->order_lot == 44 || $dateParklot->order_lot == 47){
									$freeOSTH = ((int)$dateParklot->contigent - (int)$dateParklot->used) > 0 ?
									(int)$dateParklot->contigent - (int)$dateParklot->used : 0;
									$totalUsedOSTH += (int)$dateParklot->used;
									$totalFreeOSTH += $freeOSTH;
									$totalContigentOSTH += (int)$dateParklot->contigent;
									
									$totalTotalUsedOSTH += (int)$dateParklot->used;
									$totalTotalFreeOSTH += $freeOSTH;
									$totalTotalContigentOSTH += (int)$dateParklot->contigent;
								}
								/*if($dateParklot->order_lot == 44 || $dateParklot->order_lot == 45){
									$freeOSTHV = ((int)$dateParklot->contigent - (int)$dateParklot->used) > 0 ?
									(int)$dateParklot->contigent - (int)$dateParklot->used : 0;
									$totalUsedOSTHV += (int)$dateParklot->used;
									$totalFreeOSTHV += $freeOSTHV;
									$totalContigentOSTHV += (int)$dateParklot->contigent;
									
									$totalTotalUsedOSTHV += (int)$dateParklot->used;
									$totalTotalFreeOSTHV += $freeOSTHV;
									$totalTotalContigentOSTHV += (int)$dateParklot->contigent;
								}*/
								if($dateParklot->order_lot == 50 /*|| $dateParklot->order_lot == 51*/ || $dateParklot->order_lot == 52 /*|| $dateParklot->order_lot == 53*/ || $dateParklot->order_lot == 54 || $dateParklot->order_lot == 55){
									$freeOSTP = ((int)$dateParklot->contigent - (int)$dateParklot->used) > 0 ?
									(int)$dateParklot->contigent - (int)$dateParklot->used : 0;
									$totalUsedOSTP += (int)$dateParklot->used;
									$totalFreeOSTP += $freeOSTP;
									$totalContigentOSTP += (int)$dateParklot->contigent;
									
									$totalTotalUsedOSTP += (int)$dateParklot->used;
									$totalTotalFreeOSTP += $freeOSTP;
									$totalTotalContigentOSTP += (int)$dateParklot->contigent;
								}
								

								// calculate parklot used, free, contigent in all dates
								$total[$dateParklot->parklot_short]['used'] += (int)$dateParklot->used;
								$total[$dateParklot->parklot_short]['free'] += $free;
								$total[$dateParklot->parklot_short]['total'] += (int)$dateParklot->contigent;
								?>
								<?php //if($k != 21 && $k != 22): ?>
								<div class="table-col">
									<?php if ($key == 0) : ?>
										<p class="th"><?php echo $dateParklot->parklot_short; echo $dateParklot->order_lot == 5 ? " + STRH" : "" ?></p>
										<div class="th-lots grid-three">
											<p>Gebucht</p>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>
									<?php endif; ?>
									<div class="grid-three">
										<p class="used-val tr-lots-<?php echo $i % 2; ?>"><?php echo $dateParklot->used . " / "; echo $dateParklot->contigent != 0 ? number_format($dateParklot->used / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%";?></p>
										<p class="free-val tr-lots-<?php echo $i % 2; ?>"><?php echo $free  . " / "; echo $dateParklot->contigent != 0 ?  number_format($free / $dateParklot->contigent * 100, 2,".",".") : "0.00"; echo "%"?></p>
										<p class="total-val tr-lots-<?php echo $i % 2; ?>"><?php echo $dateParklot->contigent ?></p>
									</div>
								</div>
								<?php //endif; ?>
								<?php if($k == 3): ?>
									<div class="table-col">
									<?php if ($key == 0) : ?>
										<p class="th">Gesamt PH</p>
										<div class="th-total-ph grid-three">
											<p>Gebucht</p>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>
									<?php endif; ?>
										<div class="grid-three">
											<p class="tr-total-ph-<?php echo $i % 2; ?>"><?php echo $totalUsedPH . " / "; echo $totalContigentPH != 0 ? number_format($totalUsedPH / $totalContigentPH * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
											<p class="tr-total-ph-<?php echo $i % 2; ?>"><?php echo $totalFreePH . " / "; echo $totalContigentPH != 0 ? number_format($totalFreePH / $totalContigentPH * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
											<p class="tr-total-ph-<?php echo $i % 2; ?>"><?php echo $totalContigentPH ?></p>
										</div>
									</div>
									<?php 
									$total['Summe PH']['used'] = $totalTotalUsedPH;
									$total['Summe PH']['free'] = $totalTotalFreePH;
									$total['Summe PH']['total'] = $totalTotalContigentPH;		
								endif; ?>
								<?php if($k == 6): ?>
									<div class="table-col">
									<?php if ($key == 0) : ?>
										<p class="th">Gesamt OD</p>
										<div class="th-total-od grid-three">
											<p>Gebucht</p>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>
									<?php endif; ?>
										<div class="grid-three">
											<p class="tr-total-od-<?php echo $i % 2; ?>"><?php echo $totalUsedOD . " / "; echo $totalContigentOD != 0 ? number_format($totalUsedOD / $totalContigentOD * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
											<p class="tr-total-od-<?php echo $i % 2; ?>"><?php echo $totalFreeOD . " / "; echo $totalContigentOD != 0 ? number_format($totalFreeOD / $totalContigentOD * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
											<p class="tr-total-od-<?php echo $i % 2; ?>"><?php echo $totalContigentOD ?></p>
										</div>
									</div>
									<?php 
									$total['Summe OD']['used'] = $totalTotalUsedOD;
									$total['Summe OD']['free'] = $totalTotalFreeOD;
									$total['Summe OD']['total'] = $totalTotalContigentOD;		
								endif; ?>
								<!--
								<?php if($k == 9): ?>
									<div class="table-col">
									<?php if ($key == 0) : ?>
										<p class="th">Gesamt BERN</p>
										<div class="th-total-br grid-three">
											<p>Gebucht</p>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>
									<?php endif; ?>
										<div class="grid-three">
											<p class="tr-total-br-<?php echo $i % 2; ?>"><?php echo $totalUsedBERN . " / "; echo $totalContigentBERN != 0 ? number_format($totalUsedBERN / $totalContigentBERN * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
											<p class="tr-total-br-<?php echo $i % 2; ?>"><?php echo $totalFreeBERN . " / "; echo $totalContigentBERN != 0 ? number_format($totalFreeBERN / $totalContigentBERN * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
											<p class="tr-total-br-<?php echo $i % 2; ?>"><?php echo $totalContigentBERN ?></p>
										</div>
									</div>
									<?php 
									$total['Summe BERN']['used'] = $totalTotalUsedBERN;
									$total['Summe BERN']['free'] = $totalTotalFreeBERN;
									$total['Summe BERN']['total'] = $totalTotalContigentBERN;		
								endif; ?>
								-->
								<?php if($k == 10): ?>
									<div class="table-col">
									<?php if ($key == 0) : ?>
										<p class="th">Gesamt SIE</p>
										<div class="th-total-si grid-three">
											<p>Gebucht</p>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>
									<?php endif; ?>
										<div class="grid-three">
											<p class="tr-total-si-<?php echo $i % 2; ?>"><?php echo $totalUsedSIE . " / "; echo $totalContigentSIE != 0 ? number_format($totalUsedSIE / $totalContigentSIE * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
											<p class="tr-total-si-<?php echo $i % 2; ?>"><?php echo $totalFreeSIE . " / "; echo $totalContigentSIE != 0 ? number_format($totalFreeSIE / $totalContigentSIE * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
											<p class="tr-total-si-<?php echo $i % 2; ?>"><?php echo $totalContigentSIE ?></p>
										</div>
									</div>
									<?php 
									$total['Summe SIE']['used'] = $totalTotalUsedSIE;
									$total['Summe SIE']['free'] = $totalTotalFreeSIE;
									$total['Summe SIE']['total'] = $totalTotalContigentSIE;		
								endif; ?>
								<?php if($k == 14): ?>
									<div class="table-col">
									<?php if ($key == 0) : ?>
										<p class="th">Gesamt OSTPH</p>
										<div class="th-total-ostph grid-three">
											<p>Gebucht</p>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>
									<?php endif; ?>
										<div class="grid-three">
											<p class="tr-total-ostph-<?php echo $i % 2; ?>"><?php echo $totalUsedOSTH . " / "; echo $totalContigentOSTH != 0 ? number_format($totalUsedOSTH / $totalContigentOSTH * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
											<p class="tr-total-ostph-<?php echo $i % 2; ?>"><?php echo $totalFreeOSTH . " / "; echo $totalContigentOSTH != 0 ? number_format($totalFreeOSTH / $totalContigentOSTH * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
											<p class="tr-total-ostph-<?php echo $i % 2; ?>"><?php echo $totalContigentOSTH ?></p>
										</div>
									</div>
									<?php 
									$total['Summe OSTH']['used'] = $totalTotalUsedOSTH;
									$total['Summe OSTH']['free'] = $totalTotalFreeOSTH;
									$total['Summe OSTH']['total'] = $totalTotalContigentOSTH;		
								endif; ?>
								<!--<?php if($k == 24): ?>
									<div class="table-col">
									<?php if ($key == 0) : ?>
										<p class="th">Gesamt OSTPHV</p>
										<div class="th-total-ostph grid-three">
											<p>Gebucht</p>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>
									<?php endif; ?>
										<div class="grid-three">
											<p class="tr-total-ostph-<?php echo $i % 2; ?>"><?php echo $totalUsedOSTHV . " / "; echo $totalContigentOSTHV != 0 ? number_format($totalUsedOSTHV / $totalContigentOSTHV * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
											<p class="tr-total-ostph-<?php echo $i % 2; ?>"><?php echo $totalFreeOSTHV . " / "; echo $totalContigentOSTHV != 0 ? number_format($totalFreeOSTHV / $totalContigentOSTHV * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
											<p class="tr-total-ostph-<?php echo $i % 2; ?>"><?php echo $totalContigentOSTHV ?></p>
										</div>
									</div>
									<?php 
									//$total['Summe OSTHV']['used'] = $totalTotalUsedOSTHV;
									//$total['Summe OSTHV']['free'] = $totalTotalFreeOSTHV;
									//$total['Summe OSTHV']['total'] = $totalTotalContigentOSTHV;		
								endif; ?>-->
								<?php if($k == 18): ?>
									<div class="table-col">
									<?php if ($key == 0) : ?>
										<p class="th">Gesamt OSTP</p>
										<div class="th-total-osta grid-three">
											<p>Gebucht</p>
											<p>Frei</p>
											<p>Gesamt</p>
										</div>
									<?php endif; ?>
										<div class="grid-three">
											<p class="tr-total-osta-<?php echo $i % 2; ?>"><?php echo $totalUsedOSTP . " / "; echo $totalContigentOSTP != 0 ? number_format($totalUsedOSTP / $totalContigentOSTP * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
											<p class="tr-total-osta-<?php echo $i % 2; ?>"><?php echo $totalFreeOSTP . " / "; echo $totalContigentOSTP != 0 ? number_format($totalFreeOSTP / $totalContigentOSTP * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
											<p class="tr-total-osta-<?php echo $i % 2; ?>"><?php echo $totalContigentOSTP ?></p>
										</div>
									</div>
									<?php 
									$total['Summe OSTP']['used'] = $totalTotalUsedOSTP;
									$total['Summe OSTP']['free'] = $totalTotalFreeOSTP;
									$total['Summe OSTP']['total'] = $totalTotalContigentOSTP;		
								endif; ?>
								
							<?php $k++; endforeach; ?>
							<?php
							// calculate row total from column total
							$totalTotal['used'] += $totalUsed;
							$totalTotal['free'] += $totalFree;
							$totalTotal['total'] += $totalContigent;										
							?>
							<div class="table-col">
								<?php if ($key == 0) : ?>
									<p class="th">Summe</p>
									<div class="th-total grid-three">
										<p>Gebucht</p>
										<p>Frei</p>
										<p>Gesamt</p>
									</div>
								<?php endif; ?>
								<div class="grid-three">
									<p class="tr-total-<?php echo $i % 2; ?>"><?php echo $totalUsed . " / "; echo $totalContigent != 0 ? number_format($totalUsed / $totalContigent * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
									<p class="tr-total-<?php echo $i % 2; ?>"><?php echo $totalFree . " / "; echo $totalContigent != 0 ? number_format($totalFree / $totalContigent * 100, 2,".",".") : "0.00"; echo "%"; ?></p>
									<p class="tr-total-<?php echo $i % 2; ?>"><?php echo $totalContigent ?></p>
								</div>
							</div>
							<div class="clearfix"></div>
						</div>
					<?php $i++; endforeach; 
										
					?>
					<div class="table-col-wrapper">
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
					</div>
				</div>
			</div>
		</div>
    </div>
</div>