<?php

if($_POST){
	foreach($_POST as $key => $val){
		$broker_id = $val;
		$value = $key;
		Database::getInstance()->updateBrokerAPI($broker_id, $value);
	}
}

if (!isset($_GET['edit'])) :
    $brokers = Database::getInstance()->getBrokers();
    

//echo "<pre>"; print_r($_POST); echo "</pre>";
?>
    <div class="page container-fluid <?php echo $_GET['page'] ?>">
        <div class="page-title itweb_adminpage_head">
            <h3>Vermittler Konfiguration</h3>
        </div>
		<form action="#" method="POST">
			<div class="page-body">
				<table class="table table-sm">
					<thead>
					<tr>
						<th>Firma</th>
						<th>Vermittler zuweisen</th>
						<th></th>
					</tr>
					</thead>
					<tbody>
						 <tr class="row-item">
							<td>APG-Airport-Parking-Germany</td>
							<td>
								<select name="apg" class="form-item form-control">
									<option value="">Produkt</option>
									<?php foreach ($brokers as $broker): ?>
										<option value="<?php echo $broker->id ?>" <?php echo $broker->broker_for == "apg" ? "selected": "" ?>>
											<?php echo $broker->company ?>
										</option>
									<?php endforeach; ?>
								</select>
							</td>
							<td style="width: 130px;text-align: right;">
								<a href="/wp-admin/admin.php?page=api&edit=apg" class="btn btn-secondary btn-sm">Einstellen</a>
							</td>
						</tr>
						<tr class="row-item">
							<td>Holiday Extras GmbH</td>
							<td>
								<select name="hex" class="form-item form-control">
									<option value="">Produkt</option>
									<?php foreach ($brokers as $broker): ?>
										<option value="<?php echo $broker->id ?>" <?php echo $broker->broker_for == "hex" ? "selected": "" ?>>
											<?php echo $broker->company ?>
										</option>
									<?php endforeach; ?>
								</select>
							</td>
							<td style="width: 130px;text-align: right;">
								<a href="/wp-admin/admin.php?page=api&edit=hex" class="btn btn-secondary btn-sm">Einstellen</a>
							</td>
						</tr>
						<tr class="row-item">	
							<td>Parkos</td>
							<td>
								<select name="parkos" class="form-item form-control">
									<option value="">Produkt</option>
									<?php foreach ($brokers as $broker): ?>
										<option value="<?php echo $broker->id ?>" <?php echo $broker->broker_for == "parkos" ? "selected": "" ?>>
											<?php echo $broker->company ?>
										</option>
									<?php endforeach; ?>
								</select>
							</td>
							<td style="width: 130px;text-align: right;">
								<a href="/wp-admin/admin.php?page=api&edit=parkos" class="btn btn-secondary btn-sm">Einstellen</a>
							</td>
						</tr>
						 </tr>
					</tbody>
				</table>
			</div>
			<div class="row m10">
				<div class="col-12">
					<button class="btn btn-primary">Speichern</button>
				</div>
			</div>
		</form>
    </div>
<?php else: ?>
    <?php
    require_once plugin_dir_path(__FILE__) . "api-edit-template.php";
    ?>
<?php endif; ?>