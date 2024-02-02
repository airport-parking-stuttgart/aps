<?php if(isset($_GET['edit'])): ?>
    <?php
    require_once plugin_dir_path(__FILE__) . "betreiber-edit.php";
    ?>
<?php else: ?>
<?php
if (isset($_POST['save'])) {
    $insertId = Database::getInstance()->saveClient($_POST['client'], $_POST['tax_number'], $_POST['contact'],
        $_POST['tel'], $_POST['email'], $_POST['address'], $_POST['inv_date'], $_POST['short']);
	Database::getInstance()->saveClientProducts($insertId, $_POST['client_products_id']);
}

// delete client
if(isset($_GET['d'])){
    Database::getInstance()->deleteClient($_GET['d']);
	Database::getInstance()->deleteClientPrudictLink($_GET['d']);   
	header('Location: ' . $_SERVER['HTTP_REFERER']);
}
$products = Database::getInstance()->getClientLots();
$clients = Database::getInstance()->getAllClients();
?>
<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page <?php echo $_GET['page'] ?>">
		<div class="page-title itweb_adminpage_head">
			<h3>Betreiber</h3>
		</div>
		<div class="page-body">
			<form action="#" method="POST">
				<div class="row ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Angaben zum Betreiber</h5>
					<div class="col-sm-12 col-md-12 ui-lotdata">
						<div class="row">
							<div>
								<input type="hidden" name="save" value="save">
							</div>
							<div class="col-sm-12 col-md-3">
								<label for="">Betreiber</label>
								<input type="text" name="client" class="form-control" required>
								<input type="hidden" name="save" value="save">
							</div>
							<div class="col-sm-12 col-md-3">
								<label for="">Rechnugsanschrift</label>
								<input type="text" name="address" class="form-control">
							</div>	
							<div class="col-sm-12 col-md-3">
								<label for="">&nbsp;</label>
								<button class="btn btn-primary d-block w-100" type="submit">Betreiber Neuanlage</button>
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
								<input type="text" name="short" class="form-control" required>
							</div>
						</div>
					</div>
				</div>
				
				<div class="row ui-lotdata-block ui-lotdata-block-next">
					<h5 class="ui-lotdata-title">Produkte zuweisen</h5>
					<div class="col-sm-12 col-md-12 ui-lotdata">
						<div class="row">
							<div class="col-sm-12 col-md-4">
								<select name="client_products_id[]" class="form-control" multiple>
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
				<h5 class="ui-lotdata-title">Angelegte Betreiber</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<table class="table table-responsive clients-table">
						<thead>
						<th>Betreiber</th>
						<th>Standort</th>
						<th>Rechnugsanschrift</th>
						<th>Abrechnung</th>
						<th>Steuernummer</th>
						<th>Ansprechpartner</th>
						<th>Tel</th>
						<th>E-Mail</th>
						<th></th>
						</thead>
						<tbody>
						<?php foreach($clients as $client) : ?>
							<tr>
								<td><?php echo $client->client ?></td>
								<td><?php echo $client->location ?></td>
								<td><?php echo $client->address ?></td>
								<td><?php echo $client->inv_date ?></td>
								<td><?php echo $client->tax_number ?></td>
								<td><?php echo $client->contact ?></td>
								<td><?php echo $client->email ?></td>
								<td><?php echo $client->tel ?></td>
								<td>
									<a href="/wp-admin/admin.php?page=betreiber&edit=<?php echo $client->id ?>" class="rm-add-ser">
										Edit
									</a>
									|
									<a href="/wp-admin/admin.php?page=betreiber&d=<?php echo $client->id ?>" class="rm-add-ser">
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