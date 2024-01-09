<?php
$id = $_GET['edit'];
if (isok($_POST, 'broker_company')) {
    Database::getInstance()->updateBroker($_POST['broker_company'], $_POST['broker_title'], $_POST['broker_firstname'], $_POST['broker_lastname'], $_POST['broker_street'], $_POST['broker_zip'], $_POST['broker_location'], $_POST['broker_short'], ['id' => $id]);
    Database::getInstance()->updateBrokerProducts($id, $_POST['broker_products_id']);
	
	// update commissions
	if (isset($_POST['commission_date_from']) && $_POST['commission_date_from'] != "") {
		for ($i = 0; $i < count($_POST['commission_date_from']); $i++) {
			if (empty($_POST['commission_date_from'][$i])) {
				continue;
			}
			$datefrom = date('Y-m-d', strtotime($_POST['commission_date_from'][$i]));
			$dateto = date('Y-m-d', strtotime($_POST['commission_date_to'][$i]));
			if (isset($_POST['commision_id'][$i]) && !empty($_POST['commision_id'][$i])) {
				Database::getInstance()->updateBrokerCommission($id, $datefrom, $dateto, $_POST['commission_type'][$i], $_POST['commission_value'][$i], ['id' => $_POST['commision_id'][$i]]);
			} else {				
				Database::getInstance()->saveBrokerCommission($id, $datefrom, $dateto, $_POST['commission_type'][$i], $_POST['commission_value'][$i]);
			}
		}
	}
}
$products = Database::getInstance()->getBrokerLots();
$broker = Database::getInstance()->getBroker($id);

$brokerProducts = Database::getInstance()->getBrokerProducts($id);
$brokerProducts = wp_list_pluck($brokerProducts, 'product_id');
$brokerCommisions = Database::getInstance()->getBrokerCommissions($id);

?>
<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Vermittler Bearbeiten</h3>
    </div>
    <div class="page-body">
        <form action="#" method="POST">
			<div class="row m60">	
				<div class="col-4">
					<div class="row ui-lotdata-block ui-lotdata-block-next">
						<h5 class="ui-lotdata-title">Vermittler bearbeiten</h5>
						<div class="col-sm-12 col-md-12 ui-lotdata">
							<div class="row">
								<div class="col-12 broker-item">
									<label for="">Firma</label>
									<input type="text" name="broker_company" class="form-control"
										   value="<?php echo $broker->company ?>">
								</div>
								<div class="col-12 col-sm-4 broker-item">
									<label for="">Kürzel</label>
									<input type="text" name="broker_short" class="form-control"
										   value="<?php echo $broker->short ?>">
								</div>
								<div class="col-12 broker-item">
									<label for="">Tittel</label>
									<select name="broker_title" class="form-control">
										<option value=""></option>
										<option value="Herr" <?php echo $broker->title === 'Herr' ? 'selected' : '' ?>>Herr</option>
										<option value="Frau" <?php echo $broker->title === 'Frau' ? 'selected' : '' ?>>Frau</option>
									</select>
								</div>
								<div class="col-12 col-sm-6 broker-item">
									<label for="">Vorname</label>
									<input type="text" name="broker_firstname" class="form-control"
										   value="<?php echo $broker->firstname ?>">
								</div>
								<div class="col-12 col-sm-6 broker-item">
									<label for="">Nachname</label>
									<input type="text" name="broker_lastname" class="form-control"
										   value="<?php echo $broker->lastname ?>">
								</div>
								<div class="col-12 broker-item">
									<label for="">Strasse / Nr</label>
									<input type="text" name="broker_street" class="form-control" value="<?php echo $broker->street ?>">
								</div>
								<div class="col-12 col-sm-6 broker-item">
									<label for="">Plz</label>
									<input type="text" name="broker_zip" class="form-control" value="<?php echo $broker->zip ?>">
								</div>
								<div class="col-12 col-sm-6 broker-item">
									<label for="">Ort</label>
									<input type="text" name="broker_location" class="form-control" value="<?php echo $broker->location_id ?>">
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-8">
					<div class="row ui-lotdata-block ui-lotdata-block-next commissions-wrapper">
						<h5 class="ui-lotdata-title">Provision</h5>
						<div class="col-sm-12 col-md-12 ui-lotdata">
							<div class="row">
								<?php foreach ($brokerCommisions as $commission) : ?>
									<div class="col-12 row-item commission-item">
										<div class="row">
											<div class="col-sm-12 col-md-2">
												<input type="hidden" name="commision_id[]"
													   value="<?php echo $commission->id ?>">
												<label for="">Datum von</label>
												<input type="text" name="commission_date_from[]" placeholder="" class="air-datepicker"
													   data-language="de"
													   value="<?php echo $commission->commission_date_from ?>">
											</div>
											<div class="col-sm-12 col-md-2">
												<label for="">Datum bis</label>
												<input type="text" name="commission_date_to[]" placeholder="" class="air-datepicker"
													   data-language="de"
													   value="<?php echo $commission->commission_date_to ?>">
											</div>
											<div class="col-sm-12 col-md-2">
												<label for="">Vom Umsatz</label>
												<select name="commission_type[]" class="w100">
													<option value="brutto" <?php echo $commission->commission_type == 'brutto' ? 'selected' : '' ?>>
														brutto
													</option>
													<option value="netto" <?php echo $commission->commission_type == 'netto' ? 'selected' : '' ?>>
														netto
													</option>
												</select>
											</div>
											<div class="col-sm-12 col-md-2">
												<label for="">Wert</label>
												<input type="number" name="commission_value[]" class="w100"
													   placeholder="Wert" value="<?php echo $commission->commission_value ?>">
											</div>
											<div class="col-2 add_del_buttons">
												<span class="btn btn-danger del-table-row"
													  data-table="commissions" data-id="<?php echo $commission->id ?>">x</span>
												<span class="btn btn-secondary plus-icon add-commission-template">+</span>
											</div>
										</div>
									</div>
								 <?php endforeach; ?>
								<div class="col-12 row-item commission-item">
									<div class="row">
										<div class="col-sm-12 col-md-2">
											<input type="hidden" name="commision_id[]">
											<label for="">Datum von</label>
											<input type="text" name="commission_date_from[]" placeholder="" class="air-datepicker"
												   data-language="de">
										</div>
										<div class="col-sm-12 col-md-2">
											<label for="">Datum bis</label>
											<input type="text" name="commission_date_to[]" placeholder="" class="air-datepicker"
												   data-language="de">
										</div>
										<div class="col-sm-12 col-md-2">
											<label for="">Vom Umsatz</label>
											<select name="commission_type[]" class="w100">
												<option value="brutto">brutto</option>
												<option value="netto">netto</option>
											</select> 
										</div>
										<div class="col-sm-12 col-md-2">
											<label for="">Wert</label>
											<input type="number" name="commission_value[]" class="w100"
												   placeholder="">
										</div>
										<div class="col-2 add_del_buttons">
											<span class="btn btn-danger del-table-row"
												  data-table="commissions">x</span>
											<span class="btn btn-secondary plus-icon add-commission-template">+</span>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
            <div class="row ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Provision</h5>
                <div class="col-sm-12 col-md-12 ui-lotdata">
                    <div class="row">
						<div class="col-sm-12 col-md-4">
							<select name="broker_products_id[]" class="form-control" multiple>
								<?php foreach ($products as $product) : ?>
									<option value="<?php echo $product->product_id ?>" <?php echo in_array($product->product_id, $brokerProducts) ? 'selected' : '' ?>>
										<?php echo $product->parklot ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="row m10">
						<div class="col-1">
							<br><button class="btn btn-primary">
								Speichern
							</button>
						</div>
						<div class="col-1">                    
							<br><a href="<?php echo '/wp-admin/admin.php?page=vermittler-bearbeiten' ?>" class="btn btn-secondary d-block w-100" >Schließen</a>
						</div>
					</div>
				</div>
			</div>
        </form>
    </div>
</div>