<?php
$clients = Database::getInstance()->getAllClients();
$brokers = Database::getInstance()->getBrokers();
$transfer = Database::getInstance()->getTransferLots();
//echo "<pre>"; print_r($brokers); echo "</pre>";
	
?>
<style>
.parklot{
	min-width: 315px;
}
</style>
<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
		<h3>Produkte</h3>
	</div>
	<?php foreach($clients as $client): ?>
		<?php $client_products = Database::getInstance()->getClientProducts($client->id); ?>
		<h4><?php echo $client->client ?></h4>
		<table class="table table-sm">
			<thead>
				<tr>
					<th>Title</th>
					<th>Abkürzung</th>
					<th>Prefix</th>
					<th>Erstellt</th>
					<th>Aktiv</th>
					<th>Typ</th>
					<th>Kontingent</th>
					<th>Vorlaufszeit</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($client_products as $product): ?>
					<?php $parklot = Database::getInstance()->getParklotByProductId($product->product_id); ?>			
					<?php if($parklot->deleted != 0) continue; ?>
					<?php $product = wc_get_product($parklot->product_id); ?>
					<tr>
						<td class="parklot"><?php echo $parklot->parklot ?></td>
						<td style="background-color: <?php echo $parklot->color ?>"><?php echo $parklot->parklot_short  ?></td>
						<td><?php echo $parklot->prefix  ?></td>
						<td>
							<?php echo date('d.m.Y H:i', strtotime($product->get_date_created())) ?>
						</td>
						<td><?php echo dateFormat($parklot->datefrom, 'de') . ' - ' . dateFormat($parklot->dateto, 'de') ?></td>
						<td><?php echo $parklot->type ?></td>
						<td><?php echo $parklot->contigent ?></td>
						<td><?php echo $parklot->booking_lead_time ?>
						</td>
					</tr>			
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endforeach; ?>
	
	<?php foreach($brokers as $broker): ?>
		<?php $broker_products = Database::getInstance()->getBrokerLotsById($broker->id); ?>		
		<h4><?php echo $broker->company ?></h4>
		<table class="table table-sm">
			<thead>
				<tr>
					<th>Title</th>
					<th>Abkürzung</th>
					<th>Prefix</th>
					<th>Erstellt</th>
					<th>Aktiv</th>
					<th>Typ</th>
					<th>Kontingent</th>
					<th>Vorlaufszeit</th>
					<th></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($broker_products as $product): ?>
					<?php $parklot = Database::getInstance()->getParklotByProductId($product->product_id); ?>			
					<?php if($parklot->deleted != 0) continue; ?>
					<?php $product = wc_get_product($parklot->product_id); ?>
					<tr>
						<td class="parklot"><?php echo $parklot->parklot ?></td>
						<td style="background-color: <?php echo $parklot->color ?>"><?php echo $parklot->parklot_short  ?></td>
						<td><?php echo $parklot->prefix  ?></td>
						<td>
							<?php echo date('d.m.Y H:i', strtotime($product->get_date_created())) ?>
						</td>
						<td><?php echo dateFormat($parklot->datefrom, 'de') . ' - ' . dateFormat($parklot->dateto, 'de') ?></td>
						<td><?php echo $parklot->type ?></td>
						<td><?php echo $parklot->contigent ?></td>
						<td><?php echo $parklot->booking_lead_time ?>
						</td>
					</tr>			
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endforeach; ?>
	
	<h4><?php echo "Transfer" ?></h4>
	<table class="table table-sm">
		<thead>
			<tr>
				<th>Title</th>
				<th>Abkürzung</th>
				<th>Prefix</th>
				<th>Erstellt</th>
				<th>Aktiv</th>
				<th>Typ</th>
				<th>Kontingent</th>
				<th>Vorlaufszeit</th>
				<th></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($transfer as $product): ?>
				<?php $parklot = Database::getInstance()->getParklotByProductId($product->product_id); ?>			
				<?php if($parklot->deleted != 0) continue; ?>
				<?php $product = wc_get_product($parklot->product_id); ?>
				<tr>
					<td class="parklot"><?php echo $parklot->parklot ?></td>
					<td style="background-color: <?php echo $parklot->color ?>"><?php echo $parklot->parklot_short  ?></td>
					<td><?php echo $parklot->prefix  ?></td>
					<td>
						<?php echo date('d.m.Y H:i', strtotime($product->get_date_created())) ?>
					</td>
					<td><?php echo dateFormat($parklot->datefrom, 'de') . ' - ' . dateFormat($parklot->dateto, 'de') ?></td>
					<td><?php echo $parklot->type ?></td>
					<td><?php echo $parklot->contigent ?></td>
					<td><?php echo $parklot->booking_lead_time ?></td>
				</tr>			
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
