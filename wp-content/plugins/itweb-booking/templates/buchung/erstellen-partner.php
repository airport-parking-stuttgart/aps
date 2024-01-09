<?php


if($_GET['step2'] == 1){
	echo "<pre>"; print_r($_POST); echo "</pre>";
}

?>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-title itweb_adminpage_head">
		<h3>Partner Buchung Erstellen</h3>
	</div>
	 <div class="page-body">
		<?php if(empty($_GET['step1'])): ?>
		<form action="#" method="GET">
			<input type="hidden" name="page" value="partner-buchung-erstellen">
			<div class="row ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Buchung erstellen</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row m10">
						<div class="col-sm-12 col-md-1 ui-lotdata-date">
							<label for="">Partner</label>
							<select class="form-control" name="pID">
								<option value="24">AMH</option>
								<option value="25">HMA</option>
								<option value="26">IAPS</option>
							</select>
						</div>
						<div class="col-sm-12 col-md-1 ui-lotdata-date">
							<label for="">Hin-Transfer</label>
							<input type="text" name="date_from" class="air-datepicker form-control"							
								   data-language="de" autocomplete="off" value=""
								   readonly>
						</div>
						<div class="col-sm-12 col-md-1 ui-lotdata-date">
							<label for="">Rück-Transfer</label>
							<input type="text" name="date_to" class="air-datepicker form-control"							
								   data-language="de" autocomplete="off" value=""
								   readonly>
						</div>						
					</div>
					<div class="row m10">
						<div class="col-sm-12 col-md-2 col-lg-1">
							<button type="submit" name="step1" value="1" class="btn btn-primary">Weiter</button>
						</div>
					</div>
				</div>
			</div>
		</form>
		<?php endif; ?>
		<?php if($_GET['step1'] == 1): ?>
		<?php
		
		if($_GET['date_from'] == null && $_GET['date_to'] == null)
			header('Location: /wp-admin/admin.php?page=partner-buchung-erstellen');
		
		$product_id = HotelTransfers::getHotelProdukt($_GET['pID']);
		if($product_id){
			$hotelProduct = wc_get_product($product_id->product_id);
			$variations = $hotelProduct->get_children();
		}
		if($_GET['pID'] == 24)
			$partner = 'AMH';
		elseif($_GET['pID'] == 25)
			$partner = 'HMA';
		else
			$partner = 'IAPS';
		?>
		<div class="row ui-lotdata-block ui-lotdata-block-next">
			<h5 class="ui-lotdata-title">Buchung erstellen</h5>
			<div class="col-sm-12 col-md-12 ui-lotdata">
				<div class="row m10">
					<div class="col-sm-12 col-md-1 ui-lotdata-date">
						<label for="">Partner</label>
						<input class="form-control" name="pID" value="<?php echo $partner ?>" readonly>
					</div>
					<?php if($_GET['date_from'] != null): ?>
					<div class="col-sm-12 col-md-1 ui-lotdata-date">
						<label for="">Hin-Transfer</label>
						<input type="text" name="date_from" class="form-control"							
							   data-language="de" autocomplete="off" value="<?php echo $_GET['date_from'] ?>"
							   readonly>
					</div>
					<?php endif; ?>
					<?php if($_GET['date_to'] != null): ?>
					<div class="col-sm-12 col-md-1 ui-lotdata-date">
						<label for="">Rück-Transfer</label>
						<input type="text" name="date_to" class="form-control"							
							   data-language="de" autocomplete="off" value="<?php echo $_GET['date_to'] ?>"
							   readonly>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<form  action="/wp-content/plugins/itweb-booking/classes/Helper.php" method="POST">
			<input type="hidden" name="task" value="new_hotel_transfer_backend">
			<?php if($_GET['date_from'] != null && $_GET['date_to'] != null): ?>
				<input type="hidden" name="type" value="all">
			<?php elseif($_GET['date_from'] != null && $_GET['date_to'] == null): ?>
				<input type="hidden" name="type" value="hintransfer">
			<?php elseif($_GET['date_from'] == null && $_GET['date_to'] != null): ?>
				<input type="hidden" name="type" value="rucktransfer">
			<?php endif; ?>
			<input type="hidden" name="datefrom" value="<?php echo $_GET['date_from'] ?>">
			<input type="hidden" name="dateto" value="<?php echo $_GET['date_to'] ?>">
			<input type="hidden" name="pID" value="<?php echo isset($_GET['pID']) ? $_GET['pID'] : get_current_user_id() ?>">
			
			<div class="row ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Buchungsdetails</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row m10">
						<div class="col-sm-12 col-md-2">
							<label for="">Vorname</label>
							<input type="text" class="form-control mb-2" name="vorname" placeholder="" required>
						</div>
						<div class="col-sm-12 col-md-2">
							<label for="">Nachname</label>
							<input type="text" class="form-control mb-2" name="nachname" placeholder="" required>
						</div>
						<div class="col-sm-12 col-md-2">
							<label for="">Telefon</label>
							<input type="tel" class="form-control mb-2" name="phone" placeholder="">
						</div>
					</div>
					<div class="row m10">
						<div class="col-sm-12 col-md-2">
							<label for="">Anzahl Personen</label>
							<select name="product" class="form-control mb-2" required>
								<?php foreach ($variations as $variation) : ?>
									<?php
									$product_variation = new WC_Product_Variation($variation);
									$name = explode(' - ', $product_variation->get_name())[1];
									?>
									<option value="<?php echo $product_variation->get_id() ?>">
										<?php echo $name ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						<?php if($_GET['date_from'] != null): ?>
						<div class="col-sm-12 col-md-2">
							<label for="">Transfer vom Hotel</label>
							<input type="text" class="form-control mb-2 timepicker" name="transfer_vom_hotel" placeholder="">
						</div>
						<?php endif; ?>
						<?php if($_GET['date_to'] != null): ?>
						<div class="col-sm-12 col-md-2">
							<label for="">Ankunftszeit Rückflug</label>
							<input type="text" class="form-control mb-2 timepicker" name="ankunftszeit_ruckflug" placeholder="">
						</div>
						<?php endif; ?>
						
						<?php if($_GET['date_from'] != null): ?>
						<div class="col-sm-12 col-md-2">
							<label for="">Flugnummer Hinflug</label>
							<input type="text" class="form-control mb-2" name="hinflugnummer" placeholder="">
						</div>
						<?php endif; ?>
						<?php if($_GET['date_to'] != null): ?>
						<div class="col-sm-12 col-md-2">
							<label for="">Flugnummer Rückflug</label>
							<input type="text" class="form-control mb-2" name="ruckflugnummer" placeholder="">
						</div>
						<?php endif; ?>
						<div class="col-sm-12 col-md-4">
							<label for="">Sonstiges: z. B. Kindersitz</label>
							<input type="text" class="form-control mb-2" name="sonstiges" placeholder="">
						</div>
					</div>
					<div class="row m10">
						<div class="col-sm-12 col-md-2">
							<button type="submit" name="step2" value="1" class="btn btn-primary">Transfer buchen</button>
						</div>
						<div class="col-sm-12 col-md-2">
							<a href="<?php echo '/wp-admin/admin.php?page=partner-buchung-erstellen' ?>" class="btn btn-secondary d-block w-100" >Zurücksetzen</a>
						</div>
					</div>
				</div>
			</div>
		</form>
		<?php endif; ?>
	</div>
</div>
		