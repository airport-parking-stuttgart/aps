<?php
session_start();
$db = Database::getInstance();
if (isok($_POST, 'date_from')) {
    $db->saveOrder($_POST);
}
$products = $db->getClientLots();
if (isset($_GET['pid'])) {
    $id = $_GET['pid'];
    $productAdditionalServices = $db->getProductAdditionalServices($id);
    $priceList = number_format(Pricelist::calculateAndDiscount($id, dateFormat($_GET['from']), dateFormat($_GET['to'])), 2, '.', '');
    $parklot = $db->getParklotByProductId($id);
    $restrictions = $db->getRestrictionsByProductId($id);
    $dateRestriction = $db->getDateRestriction($id, date('Y-m-d', strtotime($_GET['from'])));
}
?>
    <div class="page container-fluid <?php echo $_GET['page'] ?>">
        <div class="page-title itweb_adminpage_head">
            <h3>Buchung Erstellen</h3>
        </div>
        <div class="page-body">
            <form action="#" method="POST">
                <?php if (isset($_SESSION['errors']['all'])): ?>
                    <?php foreach ($_SESSION['errors']['all'] as $errors): ?>
                        <?php foreach ($errors as $error): ?>
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <?php echo $error ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Schließen">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div class="row ui-lotdata-block ui-lotdata-block-next">
					<h5 class="ui-lotdata-title">Buchung erstellen</h5>
					<div class="col-sm-12 col-md-12 ui-lotdata">
						<div class="row m10">
							<div class="col-sm-12 col-md-3">
								<label for="">Product</label>
								<select name="product" id="newOrderProduct" class="form-control">
									<option value=""></option>
									<?php foreach ($products as $product) : ?>
										<option value="<?php echo $product->product_id ?>" <?php echo $product->product_id == $_GET['pid'] ? 'selected' : '' ?>>
											<?php echo $product->parklot ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-sm-12 col-md-1 ui-lotdata-date">
								<?php
								$dateFrom = isok($_GET, 'from') ? date('Y-m-d', strtotime($_GET['from'])) : ''
								?>
								<label for="">Datum von</label>
								<input type="text" name="date_from" class="air-datepicker form-control date-from"
									   data-restrictions="<?php echo $restrictions ? Restrictions::stringifyDates($restrictions) : '' ?>"
									   data-date-min="<?php echo $parklot ? date('Y-m-d', strtotime($parklot->datefrom)) : '' ?>"
									   data-date-max="<?php echo $parklot ? date('Y-m-d', strtotime($parklot->dateto)) : '' ?>"
									   data-language="de" autocomplete="off" value="<?php echo $dateFrom ?>"
									   data-onselect="newOrderDateFromOnSelect" readonly>
							</div>					
							<div class="col-sm-12 col-md-1 ui-lotdata-date">
								<?php
								$dateTo = isok($_GET, 'to') ? date('Y-m-d', strtotime($_GET['to'])) : ''
								?>
								<label for="">Datum bis</label>
								<input type="text" name="date_to" class="disabled form-control date-to" autocomplete="off"
									   data-restrictions="<?php echo $restrictions ? Restrictions::stringifyDates($restrictions) : '' ?>"
									   data-date-min="<?php echo $parklot ? date('Y-m-d', strtotime($parklot->datefrom)) : '' ?>"
									   data-date-max="<?php echo $parklot ? date('Y-m-d', strtotime($parklot->dateto)) : '' ?>"
									   data-language="de" value="<?php echo $dateTo ?>" data-onselect="newOrderDateToOnSelect"
									   disabled readonly>
							</div>
							<div class="col-sm-12 col-md-1">                    
								 <label for="">&nbsp;</label>
								<a href="<?php echo '/wp-admin/admin.php?page=buchung-bearbeiten' ?>" class="btn btn-secondary d-block w-100" >Schließen</a>
							</div>
						</div>
				<?php if (isset($_GET['pid'])): ?>
						<div class="row m10">
							<div class="col-sm-12 col-md-1">
								<label for="">Uhrzeit</label>
								<input type="text" name="time_from" class="form-control time-from" autocomplete="off" readonly>
							</div>
							<div class="col-sm-12 col-md-1">
								<label for="">Hinflugnummer</label>
								<input type="text" name="hinflugnummer" class="form-control">
							</div>
							<div class="col-sm-12 col-md-1">
								<label for="">Uhrzeit</label>
								<input type="text" name="time_to" class="form-control time-to" autocomplete="off" readonly>
							</div>
							<div class="col-sm-12 col-md-2">
								<label for="">Rückflugnummer</label>
								<input type="text" name="ruckflugnummer" class="form-control">
							</div>
							<?php if($parklot->type == 'shuttle'): ?>
							<div class="col-sm-12 col-md-1">
								<label for="">Personenanzahl</label>
								<input type="number" name="personenanzahl" class="form-control">
							</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<div class="row ui-lotdata-block ui-lotdata-block-next">
					<h5 class="ui-lotdata-title">Persönliche Daten</h5>
					<div class="col-sm-12 col-md-12 ui-lotdata">
						<div class="row m10">				
							<div class="col-sm-12 col-md-1">
								<label for="">Anrede</label>
								<select name="anrede" class="form-control">
									<option value="Herr">Herr</option>
									<option value="Frau">Frau</option>
								</select>
							</div>
							<div class="col-sm-12 col-md-2">
								<label for="">Firmenname</label>
								<input type="text" name="firmenname" class="form-control">
							</div>
							<div class="col-sm-12 col-md-2">
								<label for="">Nachname</label>
								<input type="text" name="nachname" class="form-control">
							</div>
							<div class="col-sm-12 col-md-2">
								<label for="">Vorname</label>
								<input type="text" name="vorname" class="form-control">
							</div>
							<div class="col-sm-12 col-md-2">
								<label for="">Telefonnummer</label>
								<input type="text" name="telefonnummer" class="form-control">
							</div>
							<div class="col-sm-12 col-md-2">
								<label for="">E-Mail</label>
								<input type="text" name="email" class="form-control">
							</div>
						</div>
						<div class="row m10">
							<div class="col-sm-12 col-md-3">
								<label for="">Anschrift</label>
								<input type="text" name="anschrift" class="form-control">
							</div>
							<div class="col-sm-12 col-md-1">
								<label for="">Postleitzahl</label>
								<input type="number" name="postleitzahl" class="form-control">
							</div>
							<div class="col-sm-12 col-md-2">
								<label for="">Ort</label>
								<input type="text" name="ort" class="form-control">
							</div>
							<div class="col-sm-12 col-md-2">
								<label for="">Kennzeichen</label>
								<input type="text" name="kennzeichen" class="form-control">
							</div>
						</div>
					</div>
				</div>
				<?php if($parklot->type == 'valet'): ?>
				<div class="row ui-lotdata-block ui-lotdata-block-next">
					<h5 class="ui-lotdata-title">Fahrzeugangaben</h5>
					<div class="col-sm-12 col-md-12 ui-lotdata">
						<div class="row m10">
							<div class="col-sm-12 col-md-12">
								<h3 class="itweb_add_head">Fahrzeugdaten</h3>
							</div>
						</div>
						<div class="row m10">
							<div class="col-sm-12 col-md-2">
								<label for="">Hersteller</label>
								<input type="text" name="model" class="form-control">
							</div>
							<div class="col-sm-12 col-md-2">
								<label for="">Typ</label>
								<input type="text" name="type" class="form-control">
							</div>
							<div class="col-sm-12 col-md-2">
								<label for="">Farbe</label>
								<input type="text" name="color" class="form-control">
							</div>						
						</div>
					</div>
				</div>
				<?php endif; ?>
				<?php if(count($productAdditionalServices) > 0): ?>
				<div class="row ui-lotdata-block ui-lotdata-block-next">
					<h5 class="ui-lotdata-title">Zusatzleistungen</h5>
					<div class="col-sm-12 col-md-12 ui-lotdata">						
						<div class="row m10">
							<div class="col-12">
								<table class="table table-sm add-ser-check">
									<tbody>
									<?php foreach ($productAdditionalServices as $service) : ?>
										<tr class="check-row" data-id="<?php echo $service->id ?>">
											<td>
												<input type="hidden" name="add_ser_id[]">
												<?php echo $service->name ?>
											</td>
											<td class="text-right"><?php echo number_format($service->price,2,".",".") ?></td>
										</tr>
									<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				<?php endif; ?>
				<div class="row ui-lotdata-block ui-lotdata-block-next">
					<h5 class="ui-lotdata-title">Buchungskosten</h5>
					<div class="col-sm-12 col-md-12 ui-lotdata">
						<div class="row m10">
							<?php if ($_GET['pid']): ?>
								<div class="col-12 text-left">
									<div class="total-order-price">
									Gesamtbetrag:
									<span class="current-price" data-price="<?php echo $priceList ?>">
										<?php echo to_float($priceList) ?>
									</span>
										<?php echo get_woocommerce_currency() ?>
									</div>
								</div>
							<?php endif; ?>
							<div class="col-sm-12 col-md-12 col-lg-12">
								<input type="radio" id="send_mail" name="send_mail" value="mail" <?php echo $disabledBtn; ?>>
								<label for="send_mail">Buchungsbestätigung senden</label><br>
							</div>
							<div class="col-6">
								<br><button class="btn btn-primary">
									Speichern
								</button>
							</div>
						</div>
					</div>
				</div>
				<?php endif; ?>
            </form>
        </div>
    </div>
<?php session_unset(); ?>