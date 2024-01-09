<?php
//$products = Database::getInstance()->getProducts();
// Get All Orders
$filter['list'] = 1;

if(isok($_GET, 'filter')){
	unset($_GET['step']);
	unset($_GET['filter']);
}
$db = Database::getInstance();
$product_groups = Database::getInstance()->getProductGroups();
$dateFrom = isok($_GET, 'dateFrom') ? dateFormat($_GET['dateFrom']) : date('Y-m-d');
$dateTo = isok($_GET, 'dateTo') ? dateFormat($_GET['dateTo']) : date('Y-m-d');
$year = isok($_GET, 'dateFrom') ? date("Y", strtotime($_GET['dateFrom'])) : date('Y');
$filter['datum_von'] = $dateFrom;
$filter['datum_bis'] = $dateTo;
$filter['filter_product'] = $_GET['product'];

$_SESSION['dateFrom'] = $dateFrom;
$_SESSION['dateTo'] = $dateTo;


$allorders = Database::getInstance()->get_fahrerliste("Abreise", $filter);

foreach($allorders as $order){
	$grput_sql = Database::getInstance()->getProductGroupsById($order->group_id);
	if($grput_sql->perent_id != null)
		$order->perent_id = $grput_sql->perent_id;
	else
		$order->perent_id = $grput_sql->id;
}

$user = wp_get_current_user();
if ($user->user_login == 'aras' || $user->user_login == 'cakir' || $user->user_login == 'sergej')
	$editOK = "";
else
	$editOK = "readonly";

$datum = strtotime($dateFrom);
$kw = date("W", $datum);
$wochentage = array("so", "mo", "di", "mi", "do", "fr", "sa");
$w = $wochentage[date("w", strtotime($dateFrom))];
$fahrer = $db->getEinsatzplanOfDay($kw, $year, $w);
?>

<style>
.dataTables_length{
	display: none !important;
}
</style>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-title itweb_adminpage_head">
		<h3>Abreiseliste Shuttle</h3>
	</div>
	<div class="page-body">
		<form class="form-filter" id="myForm">
			<input type="hidden" name="page" value="abreiseliste">
			<div class="row ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Nach Datum filtern</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-md-1 ui-lotdata-date">
							<input type="text" id="dateFrom" name="dateFrom" placeholder="Datum von" class="form-item form-control single-datepicker" value="<?php echo dateFormat($dateFrom, 'de') ?>">
						</div>
						<div class="col-sm-12 col-md-1 ui-lotdata-date">
							<input type="text" id="dateTo" name="dateTo" placeholder="Datum bis" class="form-item form-control single-datepicker" value="<?php echo dateFormat($dateTo, 'de') ?>">
						</div>
						<div class="col-sm-12 col-md-2">
							<select name="product" class="form-item form-control">
								<option value="">Standort</option>
								<?php foreach ($product_groups as $group) : ?>						
									<option value="<?php echo $group->id ?>" <?php echo $group->id == $_GET['product'] ? "selected" : "" ?>><?php echo $group->name ?></option>						
									<?php $child_product_groups = Database::getInstance()->getChildProductGroupsByPerentId($group->id); ?>
									<?php if(count($child_product_groups) > 0): ?>
										<?php foreach ($child_product_groups as $child_group) : ?>
										<option value="<?php echo $child_group->id ?>" <?php echo $child_group->id == $_GET['product'] ? "selected" : "" ?>><?php echo " - " . $child_group->name ?></option>
										<?php endforeach; ?>
									<?php endif; ?>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-sm-12 col-md-1">
							<button class="btn btn-primary d-block w-100 form-item form-control" name="filter" value="1" type="submit">Filter</button>
						</div>
						<div class="col-sm-12 col-md-2">
							<a href="<?php echo '/wp-admin/admin.php?page=abreiseliste' ?>" class="btn btn-secondary d-block w-100">Zurücksetzen</a>
						</div>
					</div><br>
					<div class="row">
						<div class="col-sm-12 col-md-3 col-lg-2">
							<a href="<?php echo '/wp-admin/admin.php?page=anreiseliste' ?>" class="btn btn-primary d-block w-100">Anreiseliste Shuttle</a>
						</div>
						<!--
						<div class="col-sm-12 col-md-3 col-lg-2">                    
							<a href="<?php echo '/wp-admin/admin.php?page=anreiseliste-valet' ?>" class="btn btn-primary d-block w-100" >Anreiseliste Valet</a>
						</div>
						<div class="col-sm-12 col-md-3 col-lg-2">                    
							<a href="<?php echo '/wp-admin/admin.php?page=abreiseliste-valet' ?>" class="btn btn-primary d-block w-100" >Abreiseliste Valet</a>
						</div>
						-->
					</div>
				</div>
			</div>
		</form>

		<div class="btn">
			<form action="<?= get_site_url() . '/wp-content/plugins/itweb-booking/templates/fahrerportal/abreiseliste-excel.php'; ?>" method="post">
				<button type="submit" id="btnExport" value="Export to Excel" class="btn btn-success">Excel</button>
			</form>
		</div>

		<div class="btn">
			<form action="<?= get_site_url() . '/wp-content/plugins/itweb-booking/templates/fahrerportal/abreiseliste-pdf.php'; ?>" method="post">
				<button type="submit" id="btnExport" value="Export to PDF" class="btn btn-success">Abreiseliste exportieren</button>
			</form>
		</div>
		<br><br>
		<table class="table-responsive" id="returnBooking">
			<thead>
				<tr>
					<th>Nr.</th>
					<th>PCode</th>
					<th>Buchung</th>
					<th>Kunde</th>
					<th>Abreisedatum</th>
					<th>Abreisezeit</th>
					<th>Personen</th>
					<th>Rückflug</th>
					<th>Parkplatz</th>
					<th>Fahrer</th>
					<th>Sonstiges 1</th>
					<th>Sonstiges 2</th>
					<th>Betrag</th>
					<th>Service</th>
					<th>Status</th>
					<th>Bearbeitet</th>
					<th>Aktion</th>
				</tr>
			</thead>
			<tbody>
				<?php  $i = 1;
				foreach ($allorders as $order) : ?>
				<?php
					if ($order->Status == "wc-cancelled") {
						$abreisezeit = date('H:i', strtotime("23:59"));
						$color = "#ff0000";
					} else {
						$abreisezeit = date('H:i', strtotime($order->Uhrzeit_bis));
						$color = $order->Color;
					}

					if ($order->Vorname == null)
						$customor = $order->Nachname;
					elseif ($order->Nachname == null)
						$customor = $order->Vorname;
					elseif (strlen($order->Nachname) < 2)
						$customor = $order->Vorname;
					elseif (strlen($order->Nachname) > 2)
						$customor = $order->Nachname;
					else
						$customor = $order->Nachname;


					$additionalPrice = $order->Service;

					?>
					<tr style="background-color: <?php echo $color ?> !important" export-color="<?php echo $color ?>" class="row<?php echo $i % 2; ?>">
						<input type="hidden" class="order-nr" value="<?php echo $order->order_id ?>">
						<td class="order-nr" export-color="<?php echo $order->Color ?>">
							<?php echo $i ?>
						</td>
						<td class="order-pcode">
							<?php echo $order->Code ?>
						</td>
						<td class="order-token">
							<?php echo $order->Token ?>
						</td>
						<td class="order-kunde">
							<input type="text" style="width:150px;" value="<?php echo trim(strip_tags($customor)) ?>" class="transparent-input"><span style="display: none;"><?php echo strip_tags($customor) ?></span>
						</td>
						<td class="order-dateto">
							<input type="text" style="width:115px;" value="<?php echo dateFormat($order->Abreisedatum, 'de') ?>" class="transparent-input single-datepicker" readonly>
						</td>
						<td class="order-timeto">
							<input type="time" style="width:100px;" value="<?php echo $abreisezeit ?>" class="transparent-input" placeholder="00:00">
						</td>
						<td class="order-persons">
							<input type="text" style="width:70px;" value="<?php echo $order->Personenanzahl ?>" class="transparent-input">
						</td>
						<td class="order-ruckflug">
							<input type="text" style="width:100px;" value="<?php echo $order->Ruckflugnummer ?>" class="transparent-input"><span style="display: none;"><?php echo $order->Ruckflugnummer ?></span>
						</td>
						<td class="order-parkplatz">
							<input type="text" style="width:90px;" value="<?php echo $order->Parkplatz ?>" class="transparent-input"><span style="display: none;"><?php echo $order->Parkplatz ?></span>
						</td>
						<td class="order-fahrer">
							<?php							
								foreach($fahrer as $ma){
									if (str_contains($ma->$w, '-')){										
										$found = 1;
										break;
									}
									else
										$found = 0;
								}								
							?>
							<?php if(count($fahrer) > 0 && $found == 1): ?>							
							<select style="width:70px;" class="transparent-input">
								<option value=""></option>
								<?php foreach($fahrer as $ma): ?>
									<?php if (str_contains($ma->$w, '-')): ?>
											<option value="<?php echo get_user_meta( $ma->user_id, 'short_name', true ) ?>" <?php echo $order->FahrerAb == get_user_meta( $ma->user_id, 'short_name', true ) ? "selected" : "" ?>><?php echo get_user_meta( $ma->user_id, 'short_name', true ) ?></option>	
									<?php endif; ?>									
								<?php endforeach; ?>
							</select>
							<?php else: ?>
							<input type="text" style="width:70px;" value="<?php echo $order->FahrerAb ?>" class="transparent-input">
							<?php endif; ?>
						</td>
						<td class="order-sonstige1">
							<input type="text" style="width:150px;" value="<?php echo $order->Sonstige_1 ?>" class="transparent-input">
						</td>
						<td class="order-sonstige2">
							<input type="text" style="width:150px;" value="<?php echo $order->Sonstige_2 ?>" class="transparent-input">
						</td>
						<td class="order-betrag" style="position:relative;">
							<?php if (($order->Bezahlmethode == "Barzahlung" || $order->Produkt == 6772) && $order->Preis != '0.00') : ?>
								<input type="text" <?php echo $editOK; ?> style="width:100px; background: rgba(255, 255, 255, 0.5) !important" value="<?php echo $order->Preis ?>" class="form-control transparent-input">
							<?php else : ?>
								<input type="text" <?php echo $editOK; ?> style="width:100px; background: rgba(255, 255, 255, 0.5) !important" value="-" class="form-control transparent-input">
							<?php endif; ?>
						</td>
						<td class="order-service">
							<input type="text" style="width:100px;" value="<?php echo $order->Service != 0 ? number_format($order->Service, 2, '.', '') : '-'; ?>" class="transparent-input" placeholder="0.00" readonly>
						</td>
						<td class="order-status">
							<select class="transparent-input">
								<?php foreach (wc_get_order_statuses() as $key => $value) : ?>
									<?php
									if (
										$key == 'wc-processing' ||
										$key == 'wc-cancelled' ||
										$key == 'wc-refunded' ||
										$key == 'wc-pending'
									) :
									?>
										<option value="<?php echo $key ?>" <?php echo $key == $order->Status ? 'selected' : '' ?>>
											<?php
											if ($key == 'wc-processing') echo "abgeschlossen";
											elseif ($key == 'wc-cancelled') echo "storniert";
											elseif ($key == 'wc-refunded') echo "erstattet";
											elseif ($key == 'wc-pending') echo "nicht bezahlt";
											?>
										</option>
									<?php else : continue;
									endif; ?>
								<?php endforeach; ?>
							</select>
						</td>
						<?php if ($user->user_login == 'aras' || $user->user_login == 'cakir' || $user->user_login == 'sergej') : ?>
							<td class="order-edit">
								<input type="text" readonly style="width:150px;" value="<?php echo $order->editByRet ?>" class="transparent-input">
							</td>
						<?php else : ?>
							<td class="order-edit">
								<input type="text" readonly style="width:150px;" value="-" class="transparent-input">
							</td>
						<?php endif; ?>
						<?php if ($order->is_for != 'hotel') : ?>
							<td>
								<a href="#" class="save-abreiseliste-row">
									Speichern
								</a>
							</td>
						<?php else :  ?>
							<td>
								<p class="">-</p>
							</td>
						<?php endif; ?>
					</tr>
				<?php $i++;
				endforeach; ?>
			</tbody>
		</table>
		<?php if (count($allorders) <= 0) : ?>
			<p>Keine Ergebnisse gefunden!</p>
		<?php endif; ?>
	</div>
</div>