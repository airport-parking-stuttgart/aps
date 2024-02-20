<?php
$products = Database::getInstance()->getAllLots();
$date = date('Y-m-d', strtotime(date('Y-m-d')));
if((isok($_GET, 'date')))
	$date = date('Y-m-d', strtotime($_GET['date']));

// filter by product id
if((isok($_GET, 'product'))){
	$filter['product'] = $_GET['product'];
}

$kurzfristig = Database::getInstance()->getTime_BookingsV2($date, 'kf', $filter);
$mittelfristig = Database::getInstance()->getTime_BookingsV2($date, 'mf', $filter);
$langfristig = Database::getInstance()->getTime_BookingsV2($date, 'lf', $filter);

?>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-title itweb_adminpage_head">
        <h3>Vorlaufzeit</h3>
    </div>
	<br>
    <div class="page-body">
        <form class="form-filter">
            <div class="row my-2 ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Nach Datum filtern</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row my-2">
						<div class="col-sm-12 col-md-1 col-lg-1 ui-lotdata-date">
							<input type="text" name="date" placeholder="Buchung von" class="form-item form-control single-datepicker" value="<?php if($date != "") echo date('d.m.Y', strtotime($date)); else echo ''; ?>">
						</div>	
						<div class="col-sm-12 col-md-3 col-lg-2">
							<select name="product" class="form-item form-control">
								<option value="">Produkt</option>
								<?php foreach($products as $product) : ?>
									<option value="<?php echo $product->product_id ?>"
										<?php echo (isset($_GET['product']) && $_GET['product'] == $product->product_id) ? ' selected' : '' ?>>
										<?php echo $product->parklot ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-sm-12 col-md-3 col-lg-2">
							<button class="btn btn-primary d-block w-100" type="submit">Filter</button>
						</div>
						<div class="col-sm-12 col-md-2">                    
							<a href="<?php echo '/wp-admin/admin.php?page=time-booking' ?>" class="btn btn-secondary d-block w-100" >Zurücksetzen</a>
						</div>
					</div>
				</div>
            </div>
        </form>
		<br><br>
		<div class="row">
			<div class="col-sm-12 col-md-12">
				<details>
					<summary class="itweb_add_head-summary">Kurzfristige Buchungen <?php echo isset($_GET['date']) ? $_GET['date'] : date('d') . "." . date('m') . "." . date('Y'); ?> mit Anreise innerhalb 14 Tagen</summary>
					<br><br>
					<table class="bookings_datatable table-responsive">
						<thead>
						<tr>
							<th>Nr.</th>
							<th>Datum</th>
							<th>Produkt</th>
							<th>Buchungsnummer</th>
							<th>Name</th>
							<th>Anreisedatum</th>
							<th>Uhrzeit</th>
							<th>Abreisedatum</th>
							<th>Uhrzeit</th>
							<th>Parkdauer</th>
							<th>Personen</th>
							<th>E-Mail</th>
							<th>Zahlungsart</th>
							<th>Gebühren</th>
							<th>Service</th>
							<th>Voraus</th>
						</tr>
						</thead>
						<tbody>
						<?php $n = 1; foreach ($kurzfristig as $booking) : ?>
							<tr class="" style="<?php if(get_post_meta($booking->order_id, '_payment_method_title')[0] == 'MasterCard' || get_post_meta($booking->order_id, '_payment_method_title')[0] == 'Visa') echo 'background-color: #ffffcc;'; ?>">
								<td>
									<?php echo $n; ?>
								</td>
								<td>
									<?php echo date('d.m.Y', strtotime($booking->Buchungsdatum)); ?>
								</td>
								<td>
									<?php echo $booking->Code ?>
								</td>
								<td>
									<?php echo $booking->Token ?>
								</td>
								<td>
									<?php echo $booking->Vorname . ' ' . $booking->Nachname ?>
								</td>
								<td>
									<?php if($booking->Anreisedatum): ?>
										<?php echo date('d.m.Y', strtotime($booking->Anreisedatum)) ?>
									<?php else: echo "-" ?>
									<?php endif; ?>
								</td>
								<td>
									<?php if($booking->Uhrzeit_von): ?>
										<?php echo date('H:i', strtotime($booking->Uhrzeit_von)) ?>
									<?php else: echo "-" ?>
									<?php endif; ?>
								</td>
								<td>
									<?php if($booking->Abreisedatum): ?>
										<?php echo date('d.m.Y', strtotime($booking->Abreisedatum)) ?>
									<?php else: echo "-" ?>
									<?php endif; ?>
								</td>
								<td>
									<?php if($booking->Uhrzeit_bis): ?>
										<?php echo date('H:i', strtotime($booking->Uhrzeit_bis)) ?>
									<?php else: echo "-" ?>
									<?php endif; ?>
								</td>
								<td>
									<?php if($booking->Anreisedatum != null && $booking->Abreisedatum != null && $booking->is_for != 'hotel'): ?>
										<?php echo getDaysBetween2Dates(new DateTime($booking->Anreisedatum), new DateTime($booking->Abreisedatum)) ?>
									<?php else: echo "-" ?>
									<?php endif; ?>
								</td>
								<td>
									<?php if($booking->Personenanzahl): ?>
										<?php echo $booking->Personenanzahl ?>
									<?php else: echo "-" ?>
									<?php endif; ?>	
								</td>
								<td>
									<?php if($booking->Email): ?>
										<?php echo $booking->Email ?>
									<?php else: echo "-" ?>
									<?php endif; ?>	
								</td>
								<!--<td>
								<?php 
									if($booking->Status == 'wc-processing') 
										echo "abgeschlossen";
									elseif($booking->Status == 'wc-cancelled') 
										echo "storniert";
								?>
								</td>-->
								<td>
									<?php echo $booking->Bezahlmethode ?>
								</td>
								<td>
									<?php echo number_format($booking->Betrag, 2, '.', '.') ?>
								</td>
								<td>
									<?php echo $booking->Service != 0 ? number_format($booking->Service, 2, '.', '.') : '-' ?>
								</td>
								<td>
									<?php echo $booking->Tage; ?>
								</td>
							</tr>
						<?php $n++; endforeach; ?>
						</tbody>
					</table>
				</details>
				<br><br><br>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12 col-md-12">
				<details>
					<summary class="itweb_add_head-summary">Mittelfristige Buchungen <?php echo isset($_GET['date']) ? $_GET['date'] : date('d') . "." . date('m') . "." . date('Y'); ?> mit Anreise innerhalb 90 Tagen</summary>
					<br><br>
					<table class="bookings_datatable table-responsive">
						<thead>
						<tr>
							<th>Nr.</th>
							<th>Datum</th>
							<th>Produkt</th>
							<th>Buchungsnummer</th>
							<th>Name</th>
							<th>Anreisedatum</th>
							<th>Uhrzeit</th>
							<th>Abreisedatum</th>
							<th>Uhrzeit</th>
							<th>Parkdauer</th>
							<th>Personen</th>
							<th>E-Mail</th>
							<th>Zahlungsart</th>
							<th>Gebühren</th>
							<th>Service</th>
							<th>Voraus</th>
						</tr>
						</thead>
						<tbody>
						<?php $n = 1; foreach ($mittelfristig as $booking) : ?>
							<tr class="" style="<?php if(get_post_meta($booking->order_id, '_payment_method_title')[0] == 'MasterCard' || get_post_meta($booking->order_id, '_payment_method_title')[0] == 'Visa') echo 'background-color: #ffffcc;'; ?>">
								<td>
									<?php echo $n; ?>
								</td>
								<td>
									<?php echo date('d.m.Y', strtotime($booking->Buchungsdatum)); ?>
								</td>
								<td>
									<?php echo $booking->Code ?>
								</td>
								<td>
									<?php echo $booking->Token ?>
								</td>
								<td>
									<?php echo $booking->Vorname . ' ' . $booking->Nachname ?>
								</td>
								<td>
									<?php if($booking->Anreisedatum): ?>
										<?php echo date('d.m.Y', strtotime($booking->Anreisedatum)) ?>
									<?php else: echo "-" ?>
									<?php endif; ?>
								</td>
								<td>
									<?php if($booking->Uhrzeit_von): ?>
										<?php echo date('H:i', strtotime($booking->Uhrzeit_von)) ?>
									<?php else: echo "-" ?>
									<?php endif; ?>
								</td>
								<td>
									<?php if($booking->Abreisedatum): ?>
										<?php echo date('d.m.Y', strtotime($booking->Abreisedatum)) ?>
									<?php else: echo "-" ?>
									<?php endif; ?>
								</td>
								<td>
									<?php if($booking->Uhrzeit_bis): ?>
										<?php echo date('H:i', strtotime($booking->Uhrzeit_bis)) ?>
									<?php else: echo "-" ?>
									<?php endif; ?>
								</td>
								<td>
									<?php if($booking->Anreisedatum != null && $booking->Abreisedatum != null && $booking->is_for != 'hotel'): ?>
										<?php echo getDaysBetween2Dates(new DateTime($booking->Anreisedatum), new DateTime($booking->Abreisedatum)) ?>
									<?php else: echo "-" ?>
									<?php endif; ?>
								</td>
								<td>
									<?php if($booking->Personenanzahl): ?>
										<?php echo $booking->Personenanzahl ?>
									<?php else: echo "-" ?>
									<?php endif; ?>	
								</td>
								<td>
									<?php if($booking->Email): ?>
										<?php echo $booking->Email ?>
									<?php else: echo "-" ?>
									<?php endif; ?>	
								</td>
								<!--<td>
								<?php 
									if($booking->Status == 'wc-processing') 
										echo "abgeschlossen";
									elseif($booking->Status == 'wc-cancelled') 
										echo "storniert";
								?>
								</td>-->
								<td>
									<?php echo $booking->Bezahlmethode ?>
								</td>
								<td>
									<?php echo number_format($booking->Betrag, 2, '.', '.') ?>
								</td>
								<td>
									<?php echo $booking->Service != 0 ? number_format($booking->Service, 2, '.', '.') : '-' ?>
								</td>
								<td>
									<?php echo $booking->Tage; ?>
								</td>
							</tr>
						<?php $n++; endforeach; ?>
						</tbody>
					</table>
				</details>
				<br><br><br>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12 col-md-12">
				<details>
					<summary class="itweb_add_head-summary">Langfristige Buchungen <?php echo isset($_GET['date']) ? $_GET['date'] : date('d') . "." . date('m') . "." . date('Y'); ?> mit Anreise ab 90 Tagen</summary>
					<br><br>
					<table class="bookings_datatable table-responsive">
						<thead>
						<tr>
							<th>Nr.</th>
							<th>Datum</th>
							<th>Produkt</th>
							<th>Buchungsnummer</th>
							<th>Name</th>
							<th>Anreisedatum</th>
							<th>Uhrzeit</th>
							<th>Abreisedatum</th>
							<th>Uhrzeit</th>
							<th>Parkdauer</th>
							<th>Personen</th>
							<th>E-Mail</th>
							<th>Zahlungsart</th>
							<th>Gebühren</th>
							<th>Service</th>
							<th>Voraus</th>
						</tr>
						</thead>
						<tbody>
						<?php $n = 1; foreach ($langfristig as $booking) : ?>
							<tr class="" style="<?php if(get_post_meta($booking->order_id, '_payment_method_title')[0] == 'MasterCard' || get_post_meta($booking->order_id, '_payment_method_title')[0] == 'Visa') echo 'background-color: #ffffcc;'; ?>">
								<td>
									<?php echo $n; ?>
								</td>
								<td>
									<?php echo date('d.m.Y', strtotime($booking->Buchungsdatum)); ?>
								</td>
								<td>
									<?php echo $booking->Code ?>
								</td>
								<td>
									<?php echo $booking->Token ?>
								</td>
								<td>
									<?php echo $booking->Vorname . ' ' . $booking->Nachname ?>
								</td>
								<td>
									<?php if($booking->Anreisedatum): ?>
										<?php echo date('d.m.Y', strtotime($booking->Anreisedatum)) ?>
									<?php else: echo "-" ?>
									<?php endif; ?>
								</td>
								<td>
									<?php if($booking->Uhrzeit_von): ?>
										<?php echo date('H:i', strtotime($booking->Uhrzeit_von)) ?>
									<?php else: echo "-" ?>
									<?php endif; ?>
								</td>
								<td>
									<?php if($booking->Abreisedatum): ?>
										<?php echo date('d.m.Y', strtotime($booking->Abreisedatum)) ?>
									<?php else: echo "-" ?>
									<?php endif; ?>
								</td>
								<td>
									<?php if($booking->Uhrzeit_bis): ?>
										<?php echo date('H:i', strtotime($booking->Uhrzeit_bis)) ?>
									<?php else: echo "-" ?>
									<?php endif; ?>
								</td>
								<td>
									<?php if($booking->Anreisedatum != null && $booking->Abreisedatum != null && $booking->is_for != 'hotel'): ?>
										<?php echo getDaysBetween2Dates(new DateTime($booking->Anreisedatum), new DateTime($booking->Abreisedatum)) ?>
									<?php else: echo "-" ?>
									<?php endif; ?>
								</td>
								<td>
									<?php if($booking->Personenanzahl): ?>
										<?php echo $booking->Personenanzahl ?>
									<?php else: echo "-" ?>
									<?php endif; ?>	
								</td>
								<td>
									<?php if($booking->Email): ?>
										<?php echo $booking->Email ?>
									<?php else: echo "-" ?>
									<?php endif; ?>	
								</td>
								<!--<td>
								<?php 
									if($booking->Status == 'wc-processing') 
										echo "abgeschlossen";
									elseif($booking->Status == 'wc-cancelled') 
										echo "storniert";
								?>
								</td>-->
								<td>
									<?php echo $booking->Bezahlmethode ?>
								</td>
								<td>
									<?php echo number_format($booking->Betrag, 2, '.', '.') ?>
								</td>
								<td>
									<?php echo $booking->Service != 0 ? number_format($booking->Service, 2, '.', '.') : '-' ?>
								</td>
								<td>
									<?php echo $booking->Tage; ?>
								</td>
							</tr>
						<?php $n++; endforeach; ?>
						</tbody>
					</table>
				</details>
				<br><br><br>
			</div>
		</div>
    </div>
</div>