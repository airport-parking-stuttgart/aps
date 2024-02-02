<?php
$products = Database::getInstance()->getBrokerLots();
if (isok($_POST, 'broker_company')) {
    $insertId = Database::getInstance()->saveBroker($_POST['broker_company'], $_POST['broker_title'], $_POST['broker_firstname'], $_POST['broker_lastname'], $_POST['broker_street'], $_POST['broker_zip'], 
													$_POST['broker_location'], $_POST['broker_short'], $_POST['broker_api']);
    Database::getInstance()->saveBrokerProducts($insertId, $_POST['broker_products_id']);
	
	if (isset($_POST['commission_date_from']) && !empty($_POST['commission_date_from'])) {
		for ($i = 0; $i < count($_POST['commission_date_from']); $i++) {
			if (empty($_POST['commission_date_from'][$i])) {
				continue;
			}
			$datefrom = date('Y-m-d', strtotime($_POST['commission_date_from'][$i]));
			$dateto = date('Y-m-d', strtotime($_POST['commission_date_to'][$i]));
			Database::getInstance()->saveBrokerCommission($insertId, $datefrom, $dateto, $_POST['commission_type'][$i], $_POST['commission_value'][$i]);
		}
	}
}
?>
<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Vermittler Neuanlage</h3>
    </div>
    <div class="page-body">
        <form action="#" method="POST">
            <div class="row ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Angaben zum Vermittler</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">			
					<div class="row m10">
						<div class="col-2 col-sm-3">
							<label for="">Firma</label>
							<input type="text" name="broker_company" class="form-control" required>
						</div>
						<div class="col-2 col-sm-1">
							<label for="">Kürzel</label>
							<input type="text" name="broker_short" class="form-control" required>
						</div>
						<div class="col-12 col-sm-1">
							<label for="">API für</label>
							<select name="broker_api" class="form-control">
								<option value=""></option>
								<option value="apg">APG</option>
								<option value="hex">HEX</option>
								<option value="parkos">Parkos</option>
								<option value="fluparks">FluParks</option>
							</select>
						</div>
					</div>
					<div class="row m10">
						<div class="col-12 col-sm-1">
							<label for="">Tittel</label>
							<select name="broker_title" class="form-control">
								<option value=""></option>
								<option value="Herr">Herr</option>
								<option value="Frau">Frau</option>
							</select>
						</div>
						<div class="col-12 col-sm-2">
							<label for="">Vorname</label>
							<input type="text" name="broker_firstname" class="form-control">
						</div>
						<div class="col-12 col-sm-2">
							<label for="">Nachname</label>
							<input type="text" name="broker_lastname" class="form-control">
						</div>
					</div>
					<div class="row m10">
						<div class="col-12 col-sm-3">
							<label for="">Strasse / Nr</label>
							<input type="text" name="broker_street" class="form-control">
						</div>
						<div class="col-12 col-sm-1">
							<label for="">Plz</label>
							<input type="text" name="broker_zip" class="form-control">
						</div>
						<div class="col-12 col-sm-2">
							<label for="">Ort</label>
							<input type="text" name="broker_location" class="form-control">
						</div>
					</div>
				</div>
			</div>			
			<div class="row ui-lotdata-block ui-lotdata-block-next commissions-wrapper">
				<h5 class="ui-lotdata-title">Provision</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row m50">
						<div class="col-12 row-item commission-item">
							<div class="row">	
								<div class="col-sm-12 col-md-1 ui-lotdata-date">
									<label for="">Datum von</label>
									<input type="text" name="commission_date_from[]" placeholder="" class="air-datepicker"
										   data-language="de">
								</div>
								<div class="col-sm-12 col-md-1 ui-lotdata-date">
									<label for="">Datum bis</label>
									<input type="text" name="commission_date_to[]" placeholder="" class="air-datepicker"
										   data-language="de"> 
								</div>
								<div class="col-sm-12 col-md-2">
									<label for="">Von brutto / netto</label>
									<select name="commission_type[]" class="w100">
										<option value="brutto">brutto</option>
										<option value="netto">netto</option>
									</select> 
								</div>	
								<div class="col-sm-12 col-md-1">
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

			<div class="row ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Vermittelnde Produkte</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row m10">
						<div class="col-sm-12 col-md-3">
							<select name="broker_products_id[]" class="form-control" multiple>
								<?php foreach ($products as $product) : ?>
									<option value="<?php echo $product->product_id ?>">
										<?php echo $product->parklot ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div><br>
					<div class="row m10">
						<div class="col-12">
							<button class="btn btn-primary">Speichern</button>
						</div>
					</div>
				</div>
			</div>
        </form>
    </div>
</div>