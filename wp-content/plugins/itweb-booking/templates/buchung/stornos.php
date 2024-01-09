<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-logo">
		<img class="adm-logo" src="https://airport-parking-stuttgart.de/wp-content/uploads/2021/12/APS-Logo-klein.png" alt="" width="300" height="200">
	</div>

<?php
$user = wp_get_current_user();
$datefrom = date('Y-m-d', strtotime(date('Y-m-d')));
$dateto = date('Y-m-d', strtotime($datefrom . '+5 day'));

if($_GET['date_from'])
	$datefrom = date('Y-m-d', strtotime($_GET['date_from']));
if($_GET['date_to'])
	$dateto = date('Y-m-d', strtotime($_GET['date_to']));

// Get All Orders

if(isok($_GET, 'token')){
	$datefrom = $dateto = "";
	$token = $_GET['token'];
	unset($_GET['date_from']);
	unset($_GET['date_to']);
	unset($_GET['payment_method']);
	$filter['datum_von'] = $filter['datum_bis'] = $filter['payment_method'] = "";
	$filter['token'] = $token;
	$allorders = Database::getInstance()->get_bookinglistV2("wc-cancelled", $filter, "");
}

if((isok($_GET, 'date_from') && isok($_GET, 'date_to')) || ($datefrom != "" && $dateto != "")){
	$token = "";
	unset($_GET['token']);
	$filter['token'] = "";
	$filter['datum_von'] = $datefrom;
	$filter['datum_bis'] = $dateto;	

	if((isok($_GET, 'payment_method'))){
		$filter['payment_method'] = $_GET['payment_method'];
	}
	$filter['orderBy'] = "Anreisedatum";
	$allorders = Database::getInstance()->get_bookinglistV2("wc-cancelled", $filter, "");
}


?>


    <div class="page-title itweb_adminpage_head">
        <h3>Stornierte Buchungen</h3>
    </div>
	<br>
    <div class="page-body">
        <form class="form-filter">
			<div class="row my-2 ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Storno filtern</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-md-2">
							<input type="text" name="token" placeholder="Buchung" class="form-item form-control" value="<?php if($token != "") echo $token; else echo ''; ?>">
						</div>
						<div class="col-sm-12 col-md-1 ui-lotdata-date">
							<input type="text" id="dateFrom" name="date_from" placeholder="Datum von" class="form-item form-control single-datepicker" value="<?php if($datefrom != "") echo date('d.m.Y', strtotime($datefrom)); else echo ''; ?>">
						</div>
						<div class="col-sm-12 col-md-1 ui-lotdata-date">
							<input type="text" id="dateTo" name="date_to" placeholder="Datum bis" class="form-item form-control single-datepicker" value="<?php if($dateto != "") echo date('d.m.Y', strtotime($dateto)); else echo ''; ?>">
						</div>
						<div class="col-sm-12 col-md-3 col-lg-2">
							<select name="payment_method" class="form-item form-control">
								<option value="">Zahlungsart</option>
									<option value="Barzahlung"
										<?php echo (isset($_GET['payment_method']) && $_GET['payment_method'] == "Barzahlung") ? 'selected' : '' ?>>
										Barzahlung
									</option>
									<option value="MasterCard"
										<?php echo (isset($_GET['payment_method']) && $_GET['payment_method'] == "MasterCard") ? 'selected' : '' ?>>
										MasterCard
									</option>
									<option value="Visa"
										<?php echo (isset($_GET['payment_method']) && $_GET['payment_method'] == "Visa") ? 'selected' : '' ?>>
										Visa
									</option>
									<option value="PayPal / Kreditkarte"
										<?php echo (isset($_GET['payment_method']) && $_GET['payment_method'] == "PayPal / Kreditkarte") ? 'selected' : '' ?>>
										PayPal / Kreditkarte
									</option>
							</select>
						</div>
						<div class="col-sm-12 col-md-1">
							<button class="btn btn-primary d-block w-100" type="submit">Filter</button>
						</div>
						<div class="col-sm-12 col-md-2">                    
							<a href="<?php echo '/wp-admin/admin.php?page=stornos' ?>" class="btn btn-secondary d-block w-100" >Zurücksetzen</a>
						</div>
					</div>
				</div>
			</div>
        </form>
		<br><br>
        <table class="datatable">
            <thead>
            <tr>
				<th>Nr.</th>
				<th>Datum</th>
                <th>Produkt</th>
                <th>Buchungsnummer</th>
                <th>Name</th>
				<th>Stornierungsdatum</th>
                <th>Anreisedatum</th>
                <th>Uhrzeit</th>
                <th>Abreisedatum</th>
                <th>Uhrzeit</th>
                <th>E-Mail</th>
				<th>Zahlungsart</th>
                <!--<th>Gebühren</th>-->
				<?php if($user->user_login == 'aras' || $user->user_login == 'cakir' || $user->user_login == 'sergej'): ?>
				<th>Link</th>
				<th>Erstattet</th>
				<th>Storno</th>
				<?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php $nr = 1; foreach ($allorders as $booking) : ?>
				<tr>
					<td>
						<?php echo $nr ?>
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
						<?php
						global $wpdb;
						$sql = "SELECT p.post_modified AS modified FROM {$wpdb->prefix}posts p WHERE p.ID = " . $booking->order_id;
						$modified_date = $wpdb->get_row($sql);
						?>
                        <?php echo date('d.m.Y', strtotime($modified_date->modified)) ?>
                    </td>
                    <td>
                        <?php echo date('d.m.Y', strtotime($booking->Anreisedatum)) ?>
                    </td>
                    <td>
                        <?php echo date('H:i', strtotime($booking->Uhrzeit_von)) ?>
                    </td>
                    <td>
                        <?php echo date('d.m.Y', strtotime($booking->Abreisedatum)) ?>
                    </td>
                    <td>
                        <?php echo date('H:i', strtotime($booking->Uhrzeit_bis)) ?>
                    </td>
                    <td>
                        <?php echo $booking->Email ? $booking->Email : "-" ?>
                    </td>
					<td>
                        <?php echo $booking->Bezahlmethode ?>
                    </td>
                    <!--<td>
                        <?php echo $booking->Preis ? number_format($booking->Preis, 2, '.', '') : "0.00" ?>
                    </td>-->
					<?php if($user->user_login == 'aras' || $user->user_login == 'cakir' || $user->user_login == 'sergej'): ?>
						<?php if($booking->Bezahlmethode != 'Barzahlung' && $booking->Bezahlmethode != 'MasterCard' && $booking->Bezahlmethode != 'Visa' && get_post_meta($booking->order_id, '_paypal_status', true) == 'completed' &&
								($booking->Produkt == 537 || $booking->Produkt == 592 || $booking->Produkt == 619 || $booking->Produkt == 873 || $booking->Produkt == 24222 || $booking->Produkt == 24226
								|| $booking->Produkt == 80566 || $booking->Produkt == 80567)): ?>
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
						<?php if(($booking->Produkt == 537 || 
								$booking->Produkt == 592 || 
								$booking->Produkt == 619 || 
								$booking->Produkt == 873 ||
								$booking->Produkt == 24222 ||
								$booking->Produkt == 24226 ||
								$booking->Produkt == 595 ||
								$booking->Produkt == 3080 ||
								$booking->Produkt == 3081 ||
								$booking->Produkt == 3082 ||
								$booking->Produkt == 24224 ||
								$booking->Produkt == 24228
								)): ?>
							<td><button class="btn btn-sm btn-secondary" value="<?php echo $booking->order_id ?>" onclick="toProcessing(this)">Zurücksetzen</button></td>
						<?php else: ?>
							<td>-</td>
						<?php endif; ?>
					<?php endif; ?>
				</tr>
            <?php $nr++; endforeach; ?>
            </tbody>
        </table>
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

function toProcessing(e) {
	var helperUrl = '/wp-content/plugins/itweb-booking/classes/Helper.php';
	var booking = e.value;
	
	$ = jQuery;
  	$.ajax({
		type:"POST",
		url:helperUrl,
		data:{
			"task": 'set_processing',
			"order_id": booking
		},
		traditional:true,
		success:function(msg){
			location.reload(); 
		},
		error:function(msg){

		}
	});
} 
</script>