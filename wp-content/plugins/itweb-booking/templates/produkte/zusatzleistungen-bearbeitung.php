<?php
$additional_services = Database::getInstance()->getAdditionalServices();

if (isok($_POST, 'service_name')) {
	
	// update order cancellation
	if (isset($_POST['service_name']) && $_POST['service_name'] != "") {
		for ($i = 0; $i < count($_POST['service_name']); $i++) {
			if (empty($_POST['service_name'][$i])) {
				continue;
			}
			if (isset($_POST['service_id'][$i]) && !empty($_POST['service_id'][$i])) {
				//echo "<pre>"; print_r($_POST); echo "</pre>";
				Database::getInstance()->updateAdditionalService($_POST['service_id'][$i], $_POST['service_name'][$i], $_POST['description'][$i], $_POST['service_price'][$i]);
			} else {				
				Database::getInstance()->saveAdditionalService($_POST['service_name'][$i], $_POST['description'][$i], $_POST['service_price'][$i]);
			}
		}
		header('Location: ' . $_SERVER['HTTP_REFERER']);
	}
	
	//echo "<pre>"; print_r($_POST); echo "</pre>";
}

?>
<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Zusatzleistungen</h3>
    </div>
    <div class="page-body">
		<form action="#" method="POST">
			<div class="row ui-lotdata-block ui-lotdata-block-next additional_services-wrapper">
				<h5 class="ui-lotdata-title">Zusatzleistungen bearbeiten</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<?php foreach ($additional_services as $service) : ?>
						<div class="col-12 row-item service-item">
							<div class="row">
								<div class="col-sm-12 col-md-3">
									<input type="hidden" name="service_id[]"
										   value="<?php echo $service->id ?>">
									<label for="">Bezeichnung</label>
									<input type="text" name="service_name[]" placeholder="" class="w100"								   
										   value="<?php echo $service->name ?>">
								</div>
								<div class="col-sm-12 col-md-2">
									<label for="">Beschreibug</label>
									<textarea type="text" name="description[]" placeholder="" class="w100"
										   value="<?php echo $service->description ?>"><?php echo $service->description ?></textarea>
								</div>
								<div class="col-sm-12 col-md-1">
									<label for="">Preis</label>
									<input type="text" name="service_price[]" placeholder="" class="w100"
										   value="<?php echo $service->price ?>">
								</div>							
								<div class="col-2 add_del_buttons">
									<span class="btn btn-danger del-table-row"
										  data-table="additional_services" data-id="<?php echo $service->id ?>">x</span>
									<span class="btn btn-secondary plus-icon add-services-template">+</span>
								</div>
							</div>
						</div>
					 <?php endforeach; ?>
					<div class="col-12 row-item service-item">
						<div class="row">
							<div class="col-sm-12 col-md-3">
								<input type="hidden" name="service_id[]">
								<label for="">Bezeichnung</label>
								<input type="text" name="service_name[]" placeholder="" class="w100">
							</div>
							<div class="col-sm-12 col-md-2">
								<label for="">Beschreibung</label>
								<textarea type="text" name="description[]" class="w100"></textarea>
							</div>
							<div class="col-sm-12 col-md-1">
								<label for="">Preis</label>
								<input type="text" name="service_price[]" class="w100">
							</div>
							<div class="col-2 add_del_buttons">
								<span class="btn btn-danger del-table-row"
									  data-table="additional_services">x</span>
								<span class="btn btn-secondary plus-icon add-services-template">+</span>
							</div>
						</div>
					</div>
				</div>				
			</div>
			<div class="row m10">
				<div class="col-12">
					<button class="btn btn-primary">
						Speichern
					</button>
				</div>
			</div>
		</form>
    </div>
</div>