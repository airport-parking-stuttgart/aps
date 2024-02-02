
<?php


if($_POST){
	
	if($_POST['product_id'] == 812 || $_POST['product_id'] == 1054 || $_POST['product_id'] == 1645 || $_POST['product_id'] == 1740 || $_POST['product_id'] == 2199 
	|| $_POST['product_id'] == 4655 || $_POST['product_id'] == 5949 || $_POST['product_id'] == 5951
	|| $_POST['product_id'] == 5953 || $_POST['product_id'] == 5955 || $_POST['product_id'] == 5957 || $_POST['product_id'] == 5958 
	|| $_POST['product_id'] == 7050 || $_POST['product_id'] == 7053 || $_POST['product_id'] == 9463 || $_POST['product_id'] == 9465){
		Database::getInstance()->saveOrderFomAPG($_POST, 'apg');
		
	}
	else{
		$data['product'] = $_POST['product_id'];
		$data['arrivalDate'] = $_POST['startDateOnly'];
		$data['departureDate'] = $_POST['endDateOnly'];
		$data['arrivalTime'] = $_POST['order_time_from'];
		$data['departureTime'] = $_POST['order_time_to'];
		$data['outboundFlightNumber'] = $_POST['hinflug'];
		$data['returnFlightNumber'] = $_POST['ruckflug'];
		$data['countTravellers'] = $_POST['persons_nr'];
		$data['lastName'] = $_POST['billing_last_name'];
		$data['firstName'] = $_POST['billing_first_name'];
		$data['mobileNumber'] = $_POST['billing_phone'];
		$data['eMail'] = $_POST['billing_email'];
		$data['license_plate'] = $_POST['kfz_kennzeichen'];
		$data['bookingCode'] = $_POST['bookingCode'];
		$data['paymentOptions'] = $_POST['payment_method_title'];
		$data['totalParkingCosts'] = $_POST['price'];
		$data['bookingState'] = 'N';
		$data['creationDate'] = $_POST['bookingDate'];
		echo "<pre>"; print_r($data);echo "</pre>";
		Database::getInstance()->devAddBooking($data);
		echo "<br>OK";
	}
		

}

?>
 <form action="#" method="POST">
	<div class="row m10">
		<div class="col-sm-12 col-md-1">
			<label for="">Product_ID (Bei APG die APG-ID)</label>
			<input type="text" name="product_id" class="form-control">
		</div>
		<div class="col-sm-12 col-md-1">
			<label for="">Buchungsdatum</label>
			<input type="text" name="bookingDate" class="form-control">
		</div>
		<div class="col-sm-12 col-md-1">
			<label for="">Datum von</label>
			<input type="text" name="startDateOnly" class="form-control">
		</div>
		<div class="col-sm-12 col-md-1">
			<label for="">Datum bis</label>
			<input type="text" name="endDateOnly" class="form-control">
		</div>	
	</div>
	<div class="row m10">
		<div class="col-sm-12 col-md-1">
			<label for="">Uhrzeit hin</label>
			<input type="text" name="order_time_from" class="form-control">
		</div>
		<div class="col-sm-12 col-md-1">
			<label for="">Hinflugnummer</label>
			<input type="text" name="hinflug" class="form-control">
		</div>
		<div class="col-sm-12 col-md-1">
			<label for="">Uhrzeit zurück</label>
			<input type="text" name="order_time_to" class="form-control">
		</div>
		<div class="col-sm-12 col-md-1">
			<label for="">Rückflugnummer</label>
			<input type="text" name="ruckflug" class="form-control">
		</div>
		<div class="col-sm-12 col-md-1">
			<label for="">Personenanzahl</label>
			<input type="number" name="persons_nr" class="form-control">
		</div>
	</div>
	<div class="row m10">				
		<div class="col-sm-12 col-md-2">
			<label for="">Firmenname</label>
			<input type="text" name="billing_company" class="form-control">
		</div>
		<div class="col-sm-12 col-md-2">
			<label for="">Nachname</label>
			<input type="text" name="billing_last_name" class="form-control">
		</div>
		<div class="col-sm-12 col-md-2">
			<label for="">Vorname</label>
			<input type="text" name="billing_first_name" class="form-control">
		</div>
		<div class="col-sm-12 col-md-2">
			<label for="">Telefonnummer</label>
			<input type="text" name="billing_phone" class="form-control">
		</div>
		<div class="col-sm-12 col-md-2">
			<label for="">E-Mail</label>
			<input type="text" name="billing_email" class="form-control">
		</div>
	</div>
	<div class="row m10">
		<div class="col-sm-12 col-md-3">
			<label for="">Anschrift</label>
			<input type="text" name="billing_address_1" class="form-control">
		</div>
		<div class="col-sm-12 col-md-1">
			<label for="">Postleitzahl</label>
			<input type="number" name="billing_postcode" class="form-control">
		</div>
		<div class="col-sm-12 col-md-2">
			<label for="">Ort</label>
			<input type="text" name="billing_city" class="form-control">
		</div>
		<div class="col-sm-12 col-md-2">
			<label for="">Kennzeichen</label>
			<input type="text" name="kfz_kennzeichen" class="form-control">
		</div>
	</div>

	<div class="row m10">
		<div class="col-sm-12 col-md-2">
			<label for="">Buchungs-Nr.</label>
			<input type="text" name="bookingCode" class="form-control">
		</div>
		<div class="col-sm-12 col-md-2">
			<label for="">Zahlungsmethode</label>
			<input type="text" name="payment_method_title" class="form-control">
		</div>
		<div class="col-sm-12 col-md-2">
			<label for="">Preis</label>
			<input type="text" name="price" class="form-control">
		</div>
		<div class="col-6">
			<br><button class="btn btn-primary">
				Speichern
			</button>
		</div>
	</div>
</form>