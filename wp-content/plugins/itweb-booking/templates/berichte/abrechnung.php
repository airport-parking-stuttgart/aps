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


$allorders = Database::getInstance()->get_abrechnungV2($filter);

$datum = strtotime($dateFrom);
$kw = date("W", $datum);
$wochentage = array("so", "mo", "di", "mi", "do", "fr", "sa");
$w = $wochentage[date("w", strtotime($dateFrom))];
$fahrer = $db->getEinsatzplanOfDay($kw, $year, $w);

//echo "<pre>"; print_r($allorders); echo "</pre>";
?>

<style>
.dataTables_length{
	display: none !important;
}
</style>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-title itweb_adminpage_head">
		<h3>Abrechnung</h3>
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
							<a href="<?php echo '/wp-admin/admin.php?page=abrechnung' ?>" class="btn btn-secondary d-block w-100">Zurücksetzen</a>
						</div>
					</div><br>
				</div>
			</div>
		</form>

		<table class="table-responsive" id="returnBooking">
			<thead>
				<tr>
					<th>Nr.</th>
					<th>PCode</th>
					<th>Buchung</th>
					<th>Kunde</th>
					<th>Abreisedatum</th>
					<th>Abreisezeit</th>
					<th>Verlängerung</th>
					<th>Personen</th>
					<th>Fahrer</th>
					<th>Sonstiges 1</th>
					<th>Sonstiges 2</th>
					<th>Betrag</th>
					<th>Service</th>
					<th>Sperrgepäck</th>
					<th>Gesamt</th>
				</tr>
			</thead>
			<tbody>
				<?php  $i = 1; $ges_barzahlung = 0;
				foreach ($allorders as $order) : ?>
				<?php
					$abreisezeit = date('H:i', strtotime($order->Uhrzeit_bis));
					$color = $order->Color;
					

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

					if($order->Sonstige_2 != null){
						if (str_contains($order->Sonstige_2, 'TAG')) {
							if(date("Y", strtotime($order->Anreisedatum)) == "2023")
								$verlangerung = (int)$order->Sonstige_2 * 10;
							else
								$verlangerung = (int)$order->Sonstige_2 * 15;
						}
						else
							$verlangerung = 0;
					}
					else
						$verlangerung = 0;

					if($order->Personenanzahl > 4){
						$mehr_perst = $order->Personenanzahl - 4;
						if(date("Y", strtotime($order->Anreisedatum)) == "2023")
							$mehr_pers = $mehr_perst * 10;
						else
							$mehr_pers = $mehr_perst * 20;
						
					}
					else
						$mehr_pers = 0;
					
					if($order->Sperrgepack == "1"){
						if(date("Y", strtotime($order->Anreisedatum)) == "2023")
							$spgp = 10;
						else
							$spgp = 20;
					}
					else
						$spgp = 0;
					
					if($order->Bezahlmethode == "Barzahlung")
						$betrag = $order->Preis;
					else
						$betrag = 0;
					
					$gesamt = $betrag + $additionalPrice + $spgp + $mehr_pers + $verlangerung;
					
					if(($additionalPrice + $spgp + $mehr_pers + $verlangerung) == 0 && $order->Bezahlmethode != "Barzahlung")
						continue;
					
					$ges_barzahlung += $gesamt;
										
					if($order->Sonstige_1 != null && strlen($order->Sonstige_1) <= 8){
						if(str_contains(strtolower($order->Sonstige_1), strtolower('BEZ'))){
							$teile = explode(" ", strtolower($order->Sonstige_1));
							//foreach($fahrer as $ma){
								if($teile[1] != null){
									//if(strtolower($teile[1]) == strtolower(get_user_meta( $ma->user_id, 'short_name', true ))){
										$ma_user[strtolower($teile[1])]['Betrag'] += $gesamt;
										$ma_user[strtolower($teile[1])]['Nummer'] .= $i . ", ";
									//}
								}
								elseif($teile[2] != null){
									//if(strtolower($teile[2]) == strtolower(get_user_meta( $ma->user_id, 'short_name', true ))){
										$ma_user[strtolower($teile[2])]['Betrag'] += $gesamt;
										$ma_user[strtolower($teile[2])]['Nummer'] .= $i . ", ";
									//}
								}
							//}
						}
					}
					elseif($order->FahrerAn){
						$ma_user[strtolower($order->FahrerAn)]['Betrag'] += $gesamt;
						$ma_user[strtolower($order->FahrerAn)]['Nummer'] .= $i . ", ";
					}
					else{
						$ma_user['unbekannt']['Betrag'] += $gesamt;
						$ma_user['unbekannt']['Nummer'] .= $i . ", ";
					}
					
					
					$_SESSION['additionalPrice'] = $additionalPrice;
					?>
					<tr style="background-color: <?php echo $color ?> !important" export-color="<?php echo $color ?>" class="row<?php echo $i % 2; ?>">
						<input type="hidden" class="order-nr" value="<?php echo $order->order_id ?>">
						<td class="order-nr" export-color="<?php echo $order->Color ?>"><?php echo $i ?></td>
						<td class="order-pcode"><?php echo $order->Code ?></td>
						<td class="order-token"><?php echo $order->Token ?></td>
						<td class="order-kunde"><?php echo trim(strip_tags($customor)) ?></td>
						<td class="order-dateto"><?php echo dateFormat($order->Abreisedatum, 'de') ?></td>
						<td class="order-timeto"><?php echo $abreisezeit ?></td>
						<td class="order-timeto"><?php echo $verlangerung != 0 ? number_format($verlangerung, 2, '.', '') : "-" ?></td>
						<td class="order-persons"><?php echo $order->Personenanzahl ?> <?php echo $mehr_pers != 0 ? "(" . number_format($mehr_pers, 2, '.', ''). "€)" : "" ?></td>
						<td class="order-fahrer"><?php echo $order->FahrerAn ?></td>
						<td class="order-sonstige1"><?php echo $order->Sonstige_1 ?></td>
						<td class="order-sonstige2"><?php echo $order->Sonstige_2 ?></td>
						<?php if($order->Bezahlmethode == "Barzahlung"): ?>
						<td class="order-betrag"><?php echo $order->Preis ?></td>
						<?php else: ?>
						<td class="order-betrag">-</td>
						<?php endif; ?>
						<td class="order-service"><?php echo $order->Service != 0 ? number_format($order->Service, 2, '.', '') : '-'; ?></td>
						<td class="order-spgp"><?php echo $spgp != 0 ? number_format($spgp, 2, '.', '') : '-'; ?></td>
						<td class="order-summe"><?php echo number_format($gesamt, 2, '.', ''); ?></td>						
					</tr>
				<?php $i++;
				endforeach; ?>
			</tbody>
		</table>
		<?php if (count($allorders) <= 0) : ?>
			<p>Keine Ergebnisse gefunden!</p>
		<?php endif; ?>
		<div class="row">
			<div class="col-sm-12 col-md-12">
				<h5>Gesamte Barzahlung <?php ?> <?php echo number_format($ges_barzahlung, 2, ',', '.') ?>€</h5>
			</div>
		</div>
		<br><br>
		<?php 
		foreach ($ma_user as $eintrag) {
			// Überprüfe, ob der Schlüssel 'Nummer' im aktuellen Eintrag existiert
			if (isset($eintrag['Nummer'])) {
				// Verwende rtrim(), um das letzte Komma zu entfernen
				$eintrag['Nummer'] = rtrim(trim($eintrag['Nummer']), ',');
			}
		}
		?>
		<?php //echo "<pre>"; print_r($ma_user); echo "</pre>"; ?>
		<div class="row">
			<div class="col-sm-12 col-md-12">
				<h5>Abrechnung</h5>
				<table class="table table-sm" style="width: 25%">
					<thead>
						<tr>
							<th>Name</th>
							<th>Nr.</th>
							<th>Umsatz</th>
						</tr>
					</thead>
					<tbody>						
						<?php foreach($ma_user as $key => $val): ?>
						<?php if($key == "unbekannt") continue; ?>
						<tr>
							<td><?php echo $key ?></td>
							<td><?php echo $val['Nummer'] ?></td>
							<td><?php echo number_format($val['Betrag'], 2, ',', '.') ?></td>
						</tr>
						<?php endforeach; ?>
						<?php if($ma_user['unbekannt']['Betrag']): ?>
						<tr>
							<td>unbekannt</td>
							<td><?php echo $ma_user['unbekannt']['Nummer'] ?></td>
							<td><?php echo number_format($ma_user['unbekannt']['Betrag'], 2, ',', '.') ?></td>
						</tr>
						<?php endif; ?>
						<tr>
							<td><strong>Summe</strong></td>
							<td><strong></strong></td>
							<td><strong><?php echo number_format($ges_barzahlung, 2, ',', '.') ?></strong></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>