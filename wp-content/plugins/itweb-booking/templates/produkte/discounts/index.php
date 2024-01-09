<?php if (isset($_GET['edit-discount'])) {
    require_once 'edit-discount.php';
} else { ?>

<?php


$clients = Database::getInstance()->getAllClients();

if($_POST['btn'] == 1){
	
	$interval_from = date('Y-m-d', strtotime($_POST['von']));
	$interval_to = date('Y-m-d', strtotime($_POST['bis']));
	
	
		
	if (isset($_POST['product_ids']) && is_array($_POST['product_ids'])) {
		$product_ids_string = implode(', ', $_POST['product_ids']);
		$_POST['product_ids'] = $product_ids_string;
	}
	$message = "";
	if($_POST['discount_cancel'] != 'on'){
		$message .= "<p>Nicht stornierbar</p>";
	}
	if($_POST['methode'] == 'on'){
		$message .= "<p>Nur Onlinezahlung</p>";
	}
	
	Database::getInstance()->saveDiscount(
		$_POST['discount_name'], 
		$interval_from, 
		$interval_to, 
		$_POST['discount_type'], 
		$_POST['value_ud'],
		$_POST['value_pp'],		
		$_POST['discount_days_before'],
		$_POST['discount_min_days'],
		$_POST['discount_max_days'],
		$_POST['discount_contigent_limit'],
		$_POST['product_ids'],
		$_POST['methode'],
		$_POST['discount_cancel'], 
		$message
	);
	
	
	global $wpdb;					
	$discount_id = $wpdb->get_row("SELECT id FROM " . $wpdb->prefix . "itweb_discounts WHERE name = '" . $_POST['discount_name'] . "' AND interval_from = '" . $interval_from . "' AND interval_to = '" . $interval_to . "' 
	AND type = '" . $_POST['discount_type'] . "' AND value_ud = '" . $_POST['value_ud'] . "' AND value_pp = '" . $_POST['value_pp'] . "' 
	AND days_before = '" . $_POST['discount_days_before'] . "' AND discount_contigent = '" . $_POST['discount_contigent_limit'] . "' AND 
	product_id = '" . $_POST['product_ids'] . "' AND min_days = '" . $_POST['discount_min_days'] . "' AND max_days = '" . $_POST['discount_max_days']. "'");
	
	$url = "https://airport-parking-germany.de/curl/?request=apm_add_discount&pw=apmprd_req57159428";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,
	http_build_query(array(
		 'discount_name' => $_POST['discount_name'],
		 'interval_from' => $interval_from,
		 'interval_to' => $interval_to,
		 'discount_type' => $_POST['discount_type'],
		 'value_ud' => $_POST['value_ud'],
		 'value_pp' => $_POST['value_pp'],
		 'discount_days_before' => $_POST['discount_days_before'],
		 'discount_min_days' => $_POST['discount_min_days'],
		 'discount_max_days' => $_POST['discount_max_days'],
		 'discount_contigent_limit' => $_POST['discount_contigent_limit'],
		 'discount_cancel' => $_POST['discount_cancel'],
		 'discount_message' => $message,
		 'methode' => $_POST['methode'],
		 'product_id' => $_POST['product_ids'],
		 'discount_id' => $discount_id->id
	)));
	// Receive server response ...
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
	$server_output = curl_exec($ch);
	curl_close($ch);
	
	
}

if($_GET['del-discount']){
	Database::getInstance()->deleteDiscountsById($_GET['del-discount']);
	
	$url = "https://airport-parking-germany.de/curl/?request=apm_del_discount&pw=apmds_req57159428";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,
	http_build_query(array(
		 'discount_id' => $_GET['del-discount']
	)));
	// Receive server response ...
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
	$server_output = curl_exec($ch);
	curl_close($ch);
	
	header('Location: /wp-admin/admin.php?page=rabatte');
}

$discounts = Database::getInstance()->getDiscounts();

//echo "<pre>"; print_r($_POST); echo "</pre>";
?>
<style>
.modal-content{
	padding-left: 20px;
}
.modal-dialog{
	max-width: 1500px;
}
</style>
<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Rabatte</h3>
    </div>
    <div class="page-body">
		<div class="m10">
			<button class="btn btn-primary" data-toggle="modal" data-target="#newPriceModal">Rabatt hinzufügen</button>
		</div>
		<br><br>
		<table class="table table-sm">
			<thead>
				<tr>
					<th>Name</th>
					<th>Von</th>
					<th>Bis</th>
					<th>Art</th>
					<th>ÜD</th>
					<th>PP</th>
					<th>Voraustage</th>
					<th>Min Tage</th>
					<th>Max Tage</th>
					<th>Storno</th>
					<th>Kontingent bis</th>
					<th>Bezahlmethode</th>
					<th></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($discounts as $discount): ?>
					<tr>
						<td><?php echo $discount->name ?></td>
						<td><?php echo date('d.m.Y', strtotime($discount->interval_from)) ?></td>
						<td><?php echo date('d.m.Y', strtotime($discount->interval_to))  ?></td>
						<td><?php echo $discount->type ?></td>
						<td><?php echo $discount->value_ud ?></td>
						<td><?php echo $discount->value_pp ?></td>
						<td><?php echo $discount->days_before ?></td>
						<td><?php echo $discount->min_days ?></td>
						<td><?php echo $discount->max_days ?></td>
						<td><?php echo $discount->cancel == 'on' ? 'Ja' : 'Nein' ?></td>
						<td><?php echo $discount->discount_contigent ?></td>
						<td><?php echo $discount->methode == 'on' ? 'Online' : 'Bar/Online' ?></td>
						<td style="width: 100px;text-align: right;">
							<a href="/wp-admin/admin.php?page=rabatte&edit-discount=<?php echo $discount->id ?>"
							   class="btn btn-sm btn-secondary">Bearbeiten</a>
						</td>
						<td style="width: 100px;text-align: right;">
							<a href="/wp-admin/admin.php?page=rabatte&del-discount=<?php echo $discount->id ?>"
							   class="btn btn-sm btn-danger">Löschen</a>
						</td>
					</tr>			
				<?php endforeach; ?>
			</tbody>
		</table>
		<form action="<?php echo basename($_SERVER['REQUEST_URI']); ?>" method="POST">
			<div class="modal" id="newPriceModal" tabindex="-1" role="dialog">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">Rabatt hinzufügen</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<div class="row m50">
								<div class="col-12 row-item discount-item">
									<div class="row">
										<div class="col-sm-12 col-md-2">
											<label for="">Name</label>
											<input type="text" name="discount_name" placeholder="">
										</div>
										<div class="col-sm-12 col-md-1 ui-lotdata-date">
											<label for="">Von</label>
											<input type="text" class="single-datepicker" name="von"
												   autocomplete="off"
												   placeholder="">
										</div>
										<div class="col-sm-12 col-md-1 ui-lotdata-date">
											<label for="">Bis</label>
											<input type="text" class="single-datepicker" name="bis"
												   autocomplete="off"
												   placeholder="">
										</div>
										<div class="col-sm-12 col-md-1">
											<label for="">Art</label><br>
											<select name="discount_type">
												<option value="fix">Fix</option>
												<option value="percent">%</option>
											</select>
										</div>
										<div class="col-sm-12 col-md-1">
											<label for="">ÜD</label>
											<input type="text" name="value_ud" placeholder="">
										</div>
										<div class="col-sm-12 col-md-1">
											<label for="">PP</label>
											<input type="text" name="value_pp" placeholder="">
										</div>
										<div class="col-sm-12 col-md-1">
											<label for="">Voraustage</label>
											<input type="number" name="discount_days_before" placeholder="">
										</div>
										<div class="col-sm-12 col-md-1">
											<label for="">Min Tage</label>
											<input type="number" name="discount_min_days" class="w100"
												   placeholder="">
										</div>
										<div class="col-sm-12 col-md-1">
											<label for="">Max Tage</label>
											<input type="number" name="discount_max_days" class="w100"
												   placeholder="">
										</div>
										<div class="col-sm-12 col-md-1">
											<label for="">stornierbar</label>
											<select name="discount_cancel">
												<option value="on" >Ja</option>
												<option value="">Nein</option>
											</select>
										</div>
									</div>
									<br>
									<div class="row">
										<div class="col-sm-12 col-md-1 ui-lotdata-date">
											<label for="">Kontingent bis</label>
											<input type="number" name="discount_contigent_limit" class="w100"
												   placeholder="">
										</div>
										<!--<div class="col-sm-12 col-md-3">
											<label for="">Produkt</label>
											<select name="product_ids[]" multiple>
												<?php foreach($clients as $client): ?>
													<?php $client_products = Database::getInstance()->getClientProducts($client->id); ?>
													<?php foreach($client_products as $product): ?>												
														<?php $parklot = Database::getInstance()->getParklotByProductId($product->product_id); ?>
														<?php if($parklot->deleted != 0) continue; ?>
														<option value="<?php echo $parklot->product_id ?>"><?php echo $parklot->parklot ?></option>
													<?php endforeach; ?>
												<?php endforeach; ?>
											</select>
										</div>-->
										
										<div class="col-sm-12 col-md-2">
											<label for="">Bezahlmethode</label>										
											<select name="methode">
												<option value="on_bar">online / bar</option>
												<option value="on">online</option>
											</select>
										</div>
										<!--<div class="col-sm-12 col-md-7">
											<label for="">Hinweis</label><br>
											<input type="text" name="discount_note" size="50" placeholder="">
										</div>-->
										<!--<div class="col-12 col-md-7 order-md-2">
											<label for="">Nachricht</label>
											<?php wp_editor('', 'editor-'. generateToken(64), $settings = array('textarea_name' => 'discount_message', 'wpautop' => false)); ?>
										</div>-->										
									</div>
									<hr>
								</div>
								<div class="row">
									<div class="col-sm-12 col-md-12">
										<h4>Produkte zuweisen</h4>
										<?php foreach($clients as $client): ?>
											<?php $client_products = Database::getInstance()->getClientProducts($client->id); ?>
											<?php foreach($client_products as $product): ?>												
												<?php $parklot = Database::getInstance()->getParklotByProductId($product->product_id); ?>
												<?php if($parklot->deleted != 0) continue; ?>
													<label><input type="checkbox" name="product_ids[]" value="<?php echo $parklot->product_id ?>"><?php echo $parklot->parklot ?> (<?php echo $parklot->parklot_short ?>)</label><br>
											<?php endforeach; ?>
										<?php endforeach; ?>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button class="btn btn-primary" name="btn" value="1">Speichern</button>
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Schließen</button>
						</div>
					</div>
				</div>
			</div>
		</form>
    </div>
</div>
<?php } ?>