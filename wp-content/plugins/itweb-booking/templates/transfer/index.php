
<?php
$products = Database::getInstance()->getTransferLots();
$transfers = Database::getInstance()->getAllTransfers();
$locations = Database::getInstance()->getLocations();
?>
<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page <?php echo $_GET['page'] ?>">
		<div class="page-logo">
			<img class="adm-logo" src="<?php echo home_url(); ?>/wp-content/uploads/2021/08/AP-Management-System-klein.png" alt="" width="300" height="200">
		</div>
		<div class="page-title itweb_adminpage_head">
			<h3>Transfer</h3>
		</div>
		<br>
		<div class="page-body">
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
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>