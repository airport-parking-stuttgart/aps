<?php
ini_set("memory_limit", "1024M");
$user = wp_get_current_user();

// Anreisen
$filter_arr['list'] = 1;
$filter_arr['datum_von'] = date('Y-m-d');
$filter_arr['datum_bis'] = date('Y-m-d');
$arrival_shuttle = Database::getInstance()->get_fahrerliste("Anreise", "shuttle", $filter_arr);
$arrival_valet = Database::getInstance()->get_fahrerliste("Anreise", "valet", $filter_arr);
$arrival = array_merge($arrival_shuttle, $arrival_valet);

// Abreisen
$filter_de['list'] = 1;
$filter_de['datum_von'] = date('Y-m-d');
$filter_de['datum_bis'] = date('Y-m-d');
$filter['type'] = "shuttle";
$departure_shuttle = Database::getInstance()->get_fahrerliste("Abreise", "shuttle", $filter_de);
$departure_valet = Database::getInstance()->get_fahrerliste("Abreise", "valet", $filter_de);
$departure = array_merge($departure_shuttle, $departure_valet);

// today
$filter_today['buchung_von'] = date('Y-m-d');
$filter_today['buchung_bis'] = date('Y-m-d', strtotime(date('Y-m-d') . '+1 day'));
$filter_today['orderBy'] = "Buchungsdatum";
$today = Database::getInstance()->get_bookinglistV2("wc-processing", $filter_today, "");

// Stornos
$filte['token'] == null;
$rerund = Database::getInstance()->get_bookinglistV2("wc-cancelled", $filte, " AND o.Bezahlmethode = 'PayPal / Kreditkarte' ");

//echo "<pre>"; print_r($departure_shuttle); echo "</pre>";
?>


<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Tagesüberblick</h3>
    </div>
    <div class="page-body">		
		<div class="row">
			<div class="col-sm-12 col-md-6">	
				<details>
					<summary class="itweb_add_head-summary">Heutige Anreisen</summary>
					<br><br>			
					<table class="table table-sm">
						<thead>
							<tr>
								<th>Nr.</th>
								<th>Buchungs-Nr.</th> 		
								<th>Kunde</th>
								<th>Anreisezeit</th>
								<th>Personen</th>
							</tr>
						</thead>
						<tbody>
						<?php $k = 1; foreach ($arrival as $order) : ?>					
						<?php if ($order->Status == "wc-cancelled") continue; ?>	
							<tr>
								<td><?php echo $k ?></td> 	
								<td><?php echo $order->Token ?></td> 		
								<td><?php echo $order->Vorname . " " . $order->Nachname ?></td>
								<td><?php echo $order->Uhrzeit_von ?></td> 
								<td><?php echo $order->Personenanzahl ?></td> 				
							</tr>
						<?php $k++; endforeach; ?>
						</tbody>
					</table>
				</details>
			</div>
			<div class="col-sm-12 col-md-6">
				<details>
					<summary class="itweb_add_head-summary">Heutige Abreisen</summary>
					<br><br>
					<table class="table table-sm">
						<thead>
							<tr>
								<th>Nr.</th>
								<th>Buchungs-Nr.</th> 		
								<th>Kunde</th> 
								<th>Abreisezeit</th> 
								<th>Personen</th> 
							</tr>
						</thead>
						<tbody>
						<?php $k = 1; foreach ($departure as $order) : ?>					
						<?php if ($order->Status == "wc-cancelled") continue; ?>
							<tr>
								<td><?php echo $k ?></td>
								<td><?php echo $order->Token  ?></td> 		
								<td><?php echo $order->Vorname . " " . $order->Nachname ?></td>
								<td><?php echo $order->Uhrzeit_bis ?></td> 
								<td><?php echo $order->Personenanzahl ?></td> 				
							</tr>
						<?php $k++; endforeach; ?>
						</tbody>
					</table>
				</details>
			</div>			
		</div>
		<div class="row">
			<div class="col-sm-12 col-md-12">
				<details>
					<summary class="itweb_add_head-summary">Heutige Buchungen</summary>
					<br><br>
					<table class="table table-sm">
						<thead>
							<tr>
								<th>Nr.</th>
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
								<th>Netto</th>
								<th>Service</th>
							</tr>
						</thead>
						<tbody>
							<?php $k = 1; foreach ($today as $booking) : ?>						
							<tr>
								<td><?php echo $k ?></td>
								<td><?php echo $booking->Code ?></td>
								<td><?php echo $booking->Token ?></td>
								<td><?php echo $booking->Vorname . ' ' . $booking->Nachname ?></td>                   
								<td><?php echo $booking->Anreisedatum ? date('d.m.Y', strtotime($booking->Anreisedatum)) : "-" ?></td>										
								<td><?php echo $booking->Uhrzeit_von ? date('H:i', strtotime($booking->Uhrzeit_von)) : "-" ?></td>                  
								<td><?php echo $booking->Abreisedatum ? date('d.m.Y', strtotime($booking->Abreisedatum)) : "-" ?></td>									
								<td><?php echo $booking->Uhrzeit_bis ? date('H:i', strtotime($booking->Uhrzeit_bis)) : "-" ?></td>					
								<td><?php echo $booking->Anreisedatum != null && $booking->Abreisedatum != null && $booking->is_for != 'hotel' ? getDaysBetween2Dates(new DateTime($booking->Anreisedatum), new DateTime($booking->Abreisedatum)) : "-" ?></td>										
								<td><?php echo $booking->Personenanzahl ? $booking->Personenanzahl : "-" ?></td>							
								<td><?php echo $booking->Email ? $booking->Email : "-" ?></td>
								<td><?php echo $booking->Bezahlmethode ?></td>
								<td><?php echo number_format($booking->Preis, 2, ".", ".") ?></td>
								<td><?php echo number_format($booking->Preis / 119 * 100, 2, ".", ".") ?></td>
								<td><?php echo $booking->Service != 0 ? number_format($booking->Service, 2, '.', '.') : '-' ?></td>
							</tr>
						<?php $k++; endforeach; ?>
						</tbody>
					</table>
				</details>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12 col-md-12">
				<details>
					<summary class="itweb_add_head-summary">Nicht erstattete stornierte Buchungen</summary>
					<br><br>
					<table class="table table-sm">
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
							<th>E-Mail</th>
							<th>Zahlungsart</th>
							<?php if($user->user_login == 'aras' || $user->user_login == 'cakir' || $user->user_login == 'sergej'): ?>
							<th>Link</th>
							<th>Erstattet</th>
							<?php endif; ?>
						</tr>
						</thead>
						<tbody>
						<?php $k = 1; foreach ($rerund as $booking) : ?>
							<?php $product = Database::getInstance()->getParklotByProductId($booking->Produkt); ?>
							<?php if($booking->Status == 'wc-cancelled' && 
									($booking->Bezahlmethode != 'Barzahlung' && $booking->Bezahlmethode != 'MasterCard' && $booking->Bezahlmethode != 'Visa' && 
									get_post_meta($booking->order_id, '_transaction_id', true) != null && 
									get_post_meta($booking->order_id, 'editBooking_refund', true) == null && $product->deleted == 0) && $product->is_for == 'betreiber' && 
									get_post_meta($booking->order_id, 'paypal_rerunded', true) == 0
								):
							?>
							<tr>
								<td><?php echo $k ?></td>
								<td><?php echo date('Y-m-d', strtotime($booking->Buchungsdatum)); ?></td>
								<td><?php echo $product->parklot_short ?></td>
								<td><?php echo $booking->Token ?></td>
								<td><?php echo $booking->Vorname . ' ' . $booking->Nachname ?></td>
								<td><?php echo date('Y-m-d', strtotime($booking->Anreisedatum)) ?></td>
								<td><?php echo date('H:i', strtotime($booking->Uhrzeit_von)) ?></td>
								<td><?php echo date('Y-m-d', strtotime($booking->Abreisedatum)) ?></td>
								<td><?php echo date('H:i', strtotime($booking->Uhrzeit_bis)) ?></td>
								<td><?php echo $booking->Email ?></td>
								<td><?php echo $booking->Bezahlmethode ?></td>
								<?php if($user->user_login == 'aras' || $user->user_login == 'cakir' || $user->user_login == 'sergej'): ?>
									<?php if($booking->Bezahlmethode == 'PayPal / Kreditkarte' && get_post_meta($booking->order_id, '_paypal_status', true) == 'completed' && ($product->is_for == 'betreiber')): ?>
										<td>
										<a href="https://www.paypal.com/activity/payment/<?php echo get_post_meta($booking->order_id, '_transaction_id', true) ?>"
											class="btn btn-sm btn-secondary" target="_blank">Erstatten</a>							
										</td>
										<td>
											<input type="checkbox" value="<?php echo $booking->order_id ?>" <?php echo get_post_meta($booking->order_id, 'paypal_rerunded', true) == 1 ? "checked" : "" ?> onclick="refund(this)">
										</td>
									<?php else: ?>
										<td>-</td>
										<td>-</td>
									<?php endif; ?>
								<?php endif; ?>
							</tr>
							<?php endif; ?>
						<?php $k++; endforeach; ?>
						</tbody>
					</table>
				</details>
			</div>
		</div>
	</div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
function refund(e) {
	var helperUrl = '/wp-content/plugins/itweb-booking/classes/Helper.php';
	var booking = e.value;
	if (e.checked == true){	
		var status = 1;
	} else {
		var status = 0;
	}
	
	$ = jQuery;
  	$.ajax({
		type:"POST",
		url:helperUrl,
		data:{
			"task": 'set_refund',
			"order_id": booking,
			"status": status
		},
		traditional:true,
		success:function(msg){

		},
		error:function(msg){

		}
	});
} 
</script>