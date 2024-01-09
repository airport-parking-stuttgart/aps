<?php
$id = $_GET['edit'];

if (isok($_POST, 'api_code')) {
	
	// update order cancellation
	if (isset($_POST['api_code']) && $_POST['api_code'] != "") {
		for ($i = 0; $i < count($_POST['api_code']); $i++) {
			if (empty($_POST['api_code'][$i])) {
				continue;
			}
			
			if($_POST['broker_id'][$i] == null)
				$_POST['broker_id'][$i] = $id;
			
			if (isset($_POST['api_id'][$i]) && !empty($_POST['api_id'][$i])) {
				Database::getInstance()->updateAPICodes($_POST['api_id'][$i], $_POST['api_code'][$i], $_POST['product_id'][$i], $_POST['api_ws'][$i], $_POST['broker_id'][$i], $_POST['api_type'][$i], $_POST['api_service'][$i]);
			} else {				
				Database::getInstance()->saveAPICodes($_POST['api_code'][$i], $_POST['product_id'][$i], $_POST['api_ws'][$i], $_POST['broker_id'][$i], $_POST['api_type'][$i], $_POST['api_service'][$i]);
			}
		}
		header('Location: ' . $_SERVER['HTTP_REFERER']);
	}
	
	//echo "<pre>"; print_r($_POST); echo "</pre>";
}

$products = Database::getInstance()->getBrokerLots();
$broker = Database::getInstance()->getBroker($id);

$brokerProducts = Database::getInstance()->getBrokerProducts($id);
$brokerProducts = wp_list_pluck($brokerProducts, 'product_id');

$api_codes = Database::getInstance()->getAPICodesById($id);

?>
<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3><?php echo $broker->company ?></h3>
    </div>
	<div class="page-body">
		<form action="#" method="POST">
			<div class="row ui-lotdata-block ui-lotdata-block-next api_code-wrapper">
				<h5 class="ui-lotdata-title">Bearbeiten</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					
					<?php foreach ($api_codes as $api_code) : ?>
						<div class="col-12 row-item api-item">
							<div class="row">
								<div class="col-sm-12 col-md-2">
									<input type="hidden" name="broker_id[]" value="<?php echo $id ?>">
									<input type="hidden" name="api_id[]" value="<?php echo $api_code->id ?>">
									<label for="">Externer Code/ID</label>
									<input type="text" name="api_code[]" placeholder="" class="w100" value="<?php echo $api_code->code ?>">
								</div>
								<div class="col-sm-12 col-md-2">
									<label for="">Produkt</label>
									<select name="product_id[]" class="form-item form-control">
										<?php foreach($brokerProducts as $product_id): ?>
											<?php $product =  Database::getInstance()->getParklotByProductId($product_id); ?>
											<option value="<?php echo $product->product_id ?>" <?php echo $product->product_id == $api_code->product_id ? "selected" : "" ?>>
												<?php echo $product->parklot_short ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="col-sm-12 col-md-2">
									<label for="">Winter Spezial</label>
									<select name="api_ws[]" class="form-item form-control">
										<option value="0"<?php echo $api_code->ws == "0" ? "selected" : "" ?>>Nein</option>
										<option value="1"<?php echo $api_code->ws == "1" ? "selected" : "" ?>>Ja</option>
									</select>
								</div>
								<div class="col-sm-12 col-md-2">
									<label for="">Typ</label>
									<select name="api_type[]" class="form-item form-control">
										<option value="indoor"<?php echo $api_code->type == "indoor" ? "selected" : "" ?>>überdacht</option>
										<option value="outdoor"<?php echo $api_code->type == "outdoor" ? "selected" : "" ?>>außen</option>
									</select>
								</div>
								<div class="col-sm-12 col-md-2">
									<label for="">Service</label>
									<select name="api_service[]" class="form-item form-control">
										<option value="shuttle"<?php echo $api_code->service == "shuttle" ? "selected" : "" ?>>Shuttle</option>
										<option value="valet"<?php echo $api_code->service == "valet" ? "selected" : "" ?>>Valet</option>
									</select>
								</div>	
								<div class="col-2 add_del_buttons">
									<span class="btn btn-danger del-table-row"
										  data-table="extern_api_codes" data-id="<?php echo $api_code->id ?>">x</span>
									<span class="btn btn-secondary plus-icon add-api_code-template">+</span>
								</div>
							</div>
						</div>
					 <?php endforeach; ?>
					
					<div class="col-12 row-item api-item">
						<div class="row">
							<div class="col-sm-12 col-md-2">
								<input type="hidden" name="broker_id[]" value="<?php echo $id ?>">
								<input type="hidden" name="api_id[]">
								<label for="">Externer Code/ID</label>
								<input type="text" name="api_code[]" placeholder="" class="w100">
							</div>
							<div class="col-sm-12 col-md-2">
								<label for="">Produkt</label>
								<select name="product_id[]" class="form-item form-control">
									<?php foreach($brokerProducts as $product_id): ?>
										<?php $product =  Database::getInstance()->getParklotByProductId($product_id); ?>
										<option value="<?php echo $product->product_id ?>">
											<?php echo $product->parklot_short ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-sm-12 col-md-2">
								<label for="">Winter Spezial</label>
								<select name="api_ws[]" class="form-item form-control">
									<option value="0">Nein</option>
									<option value="1">Ja</option>
								</select>
							</div>
							<div class="col-sm-12 col-md-2">
								<label for="">Typ</label>
								<select name="api_type[]" class="form-item form-control">
									<option value="indoor">überdacht</option>
									<option value="outdoor">außen</option>
								</select>
							</div>
							<div class="col-sm-12 col-md-2">
								<label for="">Service</label>
								<select name="api_service[]" class="form-item form-control">
									<option value="shuttle">Shuttle</option>
									<option value="valet">Valet</option>
								</select>
							</div>
							<div class="col-2 add_del_buttons">
								<span class="btn btn-danger del-table-row"
									  data-table="extern_api_codes">x</span>
								<span class="btn btn-secondary plus-icon add-api_code-template">+</span>
							</div>
						</div>
					</div>
				</div>				
			</div>
			<div class="row m10">
				<div class="col-12">
					<button class="btn btn-primary">Speichern</button>
				</div>
			</div>
		</form>
    </div>
</div>