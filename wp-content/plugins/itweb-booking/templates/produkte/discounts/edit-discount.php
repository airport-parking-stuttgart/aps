
<?php

$discount = Database::getInstance()->getDiscountsById($_GET['edit-discount']);
$clients = Database::getInstance()->getAllClients();
$base_url = $_SERVER['HTTP_HOST'];

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
	if(isset($_POST['discount_note'])){
		$message .= "<p>".$_POST['discount_note']."</p>";
	}
	
	Database::getInstance()->updateDiscount(
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
	, ['id' => $_POST['discount_id']]);
	
	if($base_url == "airport-parking-stuttgart.de"){
		$url = "https://airport-parking-germany.de/curl/?request=apm_update_discount&pw=apmprd_req57159428";
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
			 'discount_id' => $_POST['discount_id']
		)));
		// Receive server response ...
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
		$server_output = curl_exec($ch);
		curl_close($ch);
	}

	header('Location: /wp-admin/admin.php?page=rabatte');
}


//echo "<pre>"; print_r($discount); echo "</pre>";
?>
<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Rabatt bearbeiten</h3>
    </div>
    <div class="page-body">
		<form action="<?php echo basename($_SERVER['REQUEST_URI']); ?>" method="POST">
			 <div class="row ui-lotdata-block ui-lotdata-block-next discounts-wrapper">
				<div class="row m50">
					<div class="col-12 row-item discount-item">
						<div class="row">
							<input type="hidden" name="discount_id" value="<?php echo $_GET['edit-discount'] ?>">
							<div class="col-sm-12 col-md-2">
								<label for="">Name</label>
								<input type="text" name="discount_name" placeholder=""
									   value="<?php echo $discount->name ?>" readonly>
							</div>
							<div class="col-sm-12 col-md-1 ui-lotdata-date">
								<label for="">Von</label>
								<input type="text" class="single-datepicker" name="von"											   
									   autocomplete="off"
									   placeholder=""
									   value="<?php echo date('d.m.Y', strtotime($discount->interval_from)) ?>">
							</div>
							<div class="col-sm-12 col-md-1 ui-lotdata-date">
								<label for="">Bis</label>
								<input type="text" class="single-datepicker" name="bis"
									   autocomplete="off"
									   placeholder=""
									   value="<?php echo date('d.m.Y', strtotime($discount->interval_to)) ?>">
							</div>
							<div class="col-sm-12 col-md-1">
								<label for="">Art</label><br>
								<select name="discount_type">
									<option value="fix" <?php echo $discount->type == 'fix' ? 'selected' : '' ?>>Fix</option>
									<option value="percent" <?php echo $discount->type == 'percent' ? 'selected' : '' ?>>%</option>
								</select>
							</div>
							<div class="col-sm-12 col-md-1">
								<label for="">ÜD</label>
								<input type="text" name="value_ud" placeholder=""
									   value="<?php echo $discount->value_ud ?>">
							</div>
							<div class="col-sm-12 col-md-1">
								<label for="">PP</label>
								<input type="text" name="value_pp" placeholder=""
									   value="<?php echo $discount->value_pp ?>">
							</div>
							<div class="col-sm-12 col-md-1">
								<label for="">Voraustage</label>
								<input type="number" name="discount_days_before" placeholder=""
									   value="<?php echo $discount->days_before ?>">
							</div>																	
							<div class="col-sm-12 col-md-1">
								<label for="">Min Tage</label>
								<input type="number" name="discount_min_days" class="w100"
									   placeholder=""
									   value="<?php echo $discount->min_days ?>">
							</div>
							<div class="col-sm-12 col-md-1">
								<label for="">Max Tage</label>
								<input type="number" name="discount_max_days" class="w100"
									   placeholder=""
									   value="<?php echo $discount->max_days ?>">
							</div>						
							<div class="col-sm-12 col-md-1">
								<label for="">stornierbar</label>
								<select name="discount_cancel">
									<option value="on" <?php echo $discount->cancel == 'on' ? 'selected' : "" ?>>Ja</option>
									<option value="" <?php echo $discount->cancel == '' ? 'selected' : "" ?>>Nein</option>
								</select>
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-sm-12 col-md-1 ui-lotdata-date">
								<label for="">Kontingent bis</label>
								<input type="number" name="discount_contigent_limit" class="w100"
									   placeholder=""
									   value="<?php echo $discount->discount_contigent ?>">
							</div>
							<div class="col-sm-12 col-md-2">
								<label for="">Bezahlmethode</label>										
								<select  name="methode">
									<option value="on_bar" <?php echo $discount->methode == 'on_bar' ? 'selected' : '' ?>>online / bar</option>
									<option value="on" <?php echo $discount->methode == 'on' ? 'selected' : '' ?>>online</option>
								</select>
							</div>
							<!--<div class="col-sm-12 col-md-7">
								<label for="">Hinweis</label><br>
								<input type="text" name="discount_note" size="50" value="<?php echo strip_tags($discount->message) ?>">
							</div>-->
							<!--<div class="col-12 order-md-2">
								<label for="">Nachricht</label>
								<?php wp_editor($discount->message, 'editor-'. generateToken(64), $settings = array('textarea_name' => 'discount_message', 'wpautop' => false)); ?>
							</div>-->							
						</div>						
						<br>
						<div class="row">
							<div class="col-sm-12 col-md-4">
								<h4>Produkte zuweisen</h4>
								<?php foreach($clients as $client): ?>
								<?php $client_products = Database::getInstance()->getClientProducts($client->id); ?>
									<?php foreach($client_products as $product): ?>												
										<?php $parklot = Database::getInstance()->getParklotByProductId($product->product_id); ?>
										<?php if($parklot->deleted != 0) continue; ?>
										<label><input type="checkbox" name="product_ids[]" value="<?php echo $parklot->product_id ?>" <?php echo str_contains($discount->product_id, $parklot->product_id) ? 'checked' : '' ?>><?php echo $parklot->parklot ?> (<?php echo $parklot->parklot_short ?>)</label><br>
									<?php endforeach; ?>
								<?php endforeach; ?>
							</div>									
						</div>
						<hr>
					</div>
				</div>					
			</div>
			<div class="row m10">
				<div class="col-sm-12 col-md-1">
					<button class="btn btn-primary" name="btn" value="1">Speichern</button>
				</div>
				<div class="col-sm-12 col-md-1">                    
					<a href="<?php echo '/wp-admin/admin.php?page=rabatte' ?>" class="btn btn-secondary d-block w-100" >Schließen</a><br>
				</div>
			</div>
		</form>
    </div>
</div>
