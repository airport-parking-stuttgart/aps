<?php if(isset($_GET['edit'])): ?>
    <?php
    require_once plugin_dir_path(__FILE__) . "transfer-edit.php";
    ?>
<?php else: ?>
<?php
if (isset($_POST['save'])) {
    $insertId = Database::getInstance()->saveTransfer($_POST['transfer'], $_POST['location'], $_POST['tax_number'], $_POST['contact'],
        $_POST['tel'], $_POST['email'], $_POST['address'], $_POST['inv_date'], $_POST['short']);
	Database::getInstance()->saveTransferProducts($insertId, $_POST['transfer_products_id']);
}

// delete client
if(isset($_GET['d'])){
    Database::getInstance()->deleteTransfer($_GET['d']);
	Database::getInstance()->deleteTransferPrudictLink($_GET['d']);   
	header('Location: ' . $_SERVER['HTTP_REFERER']);
}
$products = Database::getInstance()->getTransferLots();
$transfers = Database::getInstance()->getAllTransfers();
$locations = Database::getInstance()->getLocations();
?>
<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page <?php echo $_GET['page'] ?>">
		<div class="page-title itweb_adminpage_head">
			<h3>Transfer</h3>
		</div>
		<br>
		<div class="page-body">
			<form action="#" method="POST">
				<div class="row ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Angaben zum Transfer</h5>
					<div class="col-sm-12 col-md-12 ui-lotdata">
						<div class="row">
							<div>
								<input type="hidden" name="save" value="save">
							</div>
							<div class="col-sm-12 col-md-3">
								<label for="">Firma</label>
								<input type="text" name="transfer" class="form-control">
								<input type="hidden" name="save" value="save">
							</div>
							<div class="col-sm-12 col-md-3">							
								<label for="">Standort</label>
								<select name="location" class="form-control">
									<?php foreach($locations as $location): ?>
										<option value="<?php echo $location->id ?>"><?php echo $location->location ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-sm-12 col-md-3">
								<label for="">Rechnugsanschrift</label>
								<input type="text" name="address" class="form-control">
							</div>	
							<div class="col-sm-12 col-md-3">
								<label for="">&nbsp;</label>
								<button class="btn btn-primary d-block w-100" type="submit">Transfer Neuanlage</button>
							</div>
						</div>

						<div class="row">
							<div class="col-sm-12 col-md-3">
								<label for="">Ansprechpartner</label>
								<input type="text" name="contact" class="form-control">
							</div>						
							<div class="col-sm-12 col-md-3">
								<label for="">Tel</label>
								<input type="tel" name="tel" class="form-control">
							</div>
							<div class="col-sm-12 col-md-3">
								<label for="">E-Mail</label>
								<input type="email" name="email" class="form-control">
							</div>
						</div>
						<div class="row">							
							<div class="col-sm-12 col-md-3">
								<label for="">Steuernummer</label>
								<input type="text" name="tax_number" class="form-control">
							</div>
							<div class="col-sm-12 col-md-3">
								<label for="">Abrechnung zum xx folgemonat</label>
								<input type="number" name="inv_date" class="form-control">
							</div>
							<div class="col-sm-12 col-md-1">
								<label for="">Kürzel</label>
								<input type="text" name="short" class="form-control">
							</div>
						</div>
					</div>
				</div>
				
				<div class="row ui-lotdata-block ui-lotdata-block-next">
					<h5 class="ui-lotdata-title">Produkte zuweisen</h5>
					<div class="col-sm-12 col-md-12 ui-lotdata">
						<div class="row">
							<div class="col-sm-12 col-md-4">
								<select name="transfer_products_id[]" class="form-control" multiple>
									<?php foreach ($products as $product) : ?>
										<option value="<?php echo $product->product_id ?>">
											<?php echo $product->parklot ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					</div>
				</div>
			</form>
			<div class="row ui-lotdata-block">
				<h5 class="ui-lotdata-title">Angelegte Transfer</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<table class="table table-responsive clients-table">
						<thead>
						<th>Firma</th>
						<th>Rechnugsanschrift</th>
						<th>Abrechnung</th>
						<th>Steuernummer</th>
						<th>Ansprechpartner</th>
						<th>Tel</th>
						<th>E-Mail</th>
						<th></th>
						</thead>
						<tbody>
						<?php foreach($transfers as $transfer) : ?>
							<tr>
								<td><?php echo $transfer->transfer ?></td>
								<td><?php echo $transfer->address ?></td>
								<td><?php echo $transfer->inv_date ?></td>
								<td><?php echo $transfer->tax_number ?></td>
								<td><?php echo $transfer->contact ?></td>
								<td><?php echo $transfer->email ?></td>
								<td><?php echo $transfer->tel ?></td>
								<td>
									<a href="/wp-admin/admin.php?page=transfer-neuanlage&edit=<?php echo $transfer->id ?>" class="rm-add-ser">
										Edit
									</a>
									|
									<a href="/wp-admin/admin.php?page=transfer-neuanlage&d=<?php echo $transfer->id ?>" class="rm-add-ser">
										löschen
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>