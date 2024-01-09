<?php
$id = $_GET['edit'];

if(isset($_POST['update'])){
	Database::getInstance()->updateClient($_POST['client'], $_POST['location'], $_POST['tax_number'], $_POST['contact'],
	$_POST['tel'], $_POST['email'], $_POST['address'], $_POST['inv_date'], $_POST['short'], ['id' => $id]);
	Database::getInstance()->updateClientProducts($id, $_POST['client_products_id']);
}
$products = Database::getInstance()->getClientLots();
$client = Database::getInstance()->getClient($id);
$clientProducts = Database::getInstance()->getClientProducts($id);
$clientProducts = wp_list_pluck($clientProducts, 'product_id');
$locations = Database::getInstance()->getLocations();
?>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3><?php echo $client->client ?></h3>
    </div>
    <div class="page-body">
        <form action="#" method="POST">
            <div class="row ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Betreiber Angaben</h5>
                <div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-md-3">
							<label for="">Betreiber</label>
							<input type="text" name="client" class="form-control" value="<?php echo $client->client ?>">
							<input type="hidden" name="update" value="update">
						</div>
						<div class="col-sm-12 col-md-3">
							<label for="">Standort</label>
							<select name="location" class="form-control">
								<?php foreach($locations as $location): ?>
									<option value="<?php echo $location->id ?>" <?php if($location->id == $client->location_id) echo "selected" ?>><?php echo $location->location ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-sm-12 col-md-3">
							<label for="">Rechnugsanschrift</label>
							<input type="text" name="address" class="form-control" value="<?php echo $client->address ?>">
						</div>				
						<div class="col-sm-12 col-md-2">
							<label for="">&nbsp;</label>
							<button class="btn btn-primary d-block w-100" type="submit">Betreiber Aktualisieren</button>
						</div>
						<div class="col-sm-12 col-md-1">                    
							 <label for="">&nbsp;</label>
							<a href="<?php echo '/wp-admin/admin.php?page=betreiber' ?>" class="btn btn-secondary d-block w-100" >Schließen</a>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12 col-md-3">
							<label for="">Ansprechpartner</label>
							<input type="text" name="contact" class="form-control" value="<?php echo $client->contact ?>">
						</div>
						<div class="col-sm-12 col-md-3">
							<label for="">Tel</label>
							<input type="tel" name="tel" class="form-control" value="<?php echo $client->tel ?>">
						</div>
						<div class="col-sm-12 col-md-3">
							<label for="">E-Mail</label>
							<input type="email" name="email" class="form-control" value="<?php echo $client->email ?>">
						</div>
					</div>
                    <div class="row">
						<div class="col-sm-12 col-md-3">
							<label for="">Steuernummer</label>
							<input type="text" name="tax_number" class="form-control" value="<?php echo $client->tax_number ?>">
						</div>
						<div class="col-sm-12 col-md-3">
                            <label for="">Abrechnung zum xx folgemonat</label>
                            <input type="number" name="inv_date" class="form-control" value="<?php echo $client->inv_date ?>">
                        </div>
						<div class="col-sm-12 col-md-3">
                            <label for="">Kürzel</label>
                            <input type="text" name="short" class="form-control" value="<?php echo $client->short ?>">
                        </div>
					</div>
				</div>
			</div>

			<div class="row ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Zugewiesene Produkte</h5>            
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-md-4">
							<select name="client_products_id[]" class="form-control" multiple>
								<?php foreach ($products as $product) : ?>
									<option value="<?php echo $product->product_id ?>" <?php echo in_array($product->product_id, $clientProducts) ? 'selected' : '' ?>>
										<?php echo $product->parklot ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				</div>
            </div>
        </form>
    </div>
</div>