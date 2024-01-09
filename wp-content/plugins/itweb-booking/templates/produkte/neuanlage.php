<?php
$additionalServices = Database::getInstance()->getAdditionalServices();
$product_groups = Database::getInstance()->getProductGroups();
$clients = Database::getInstance()->getAllClients();
$brokers = Database::getInstance()->getBrokers();
$transfers = Database::getInstance()->getAllTransfers();
?>

<script>
function change_for(e){
	var betreiber = document.getElementById("betreiber");
	var vermittler = document.getElementById("vermittler");
	var hotel = document.getElementById("hotel");
		
	if(e.value == 'betreiber'){
		betreiber.style.display = "block";
		vermittler.style.display = "none";
		hotel.style.display = "none";
	}
	else if(e.value == 'vermittler'){
		betreiber.style.display = "none";
		vermittler.style.display = "block";
		hotel.style.display = "none";
	}
	else if(e.value == 'hotel'){
		betreiber.style.display = "none";
		vermittler.style.display = "none";
		hotel.style.display = "block";
	}
}
</script>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page <?php echo $_GET['page'] ?>">
        <div class="page-title itweb_adminpage_head">
            <h3>Neuenlage</h3>
        </div>
        <div class="page-body">
            <form method="POST" action="/wp-content/plugins/itweb-booking/classes/Helper.php"
                  enctype="multipart/form-data">
                <input type="hidden" name="task" value="save_product">
                <div class="row">
					<div class="col-sm-12 col-md-2">
                        <label for="">Produkt ist für den</label>
                        <select name="product_isfor" class="form-control" onchange="change_for(this)">
                            <?php if(count($clients) > 0): ?>
								<option value="betreiber">Betreiber</option>                            
                            <?php endif; ?>
							<?php if(count($brokers) > 0): ?>
								<option value="vermittler">Vermittler</option>
							<?php endif; ?>
							<?php if(count($transfers) > 0): ?>
								<option value="hotel">Transfer</option>
							<?php endif; ?>
                        </select>
                    </div>
					<div class="col-sm-12 col-md-3" id="betreiber">
                        <label for="">An Betreiber zuweisen</label>
                        <select name="betreiber" class="form-control">
                            <?php foreach($clients as $client): ?>
								<option value="<?php echo $client->id ?>"><?php echo $client->client ?></option>
							<?php endforeach; ?>
                        </select>
                    </div>
					<div class="col-sm-12 col-md-3" id="vermittler" style="display: none;">
                        <label for="">An Vermittler zuweisen</label>
                        <select name="vermittler" class="form-control">
                            <?php foreach($brokers as $broker): ?>
								<option value="<?php echo $broker->id ?>"><?php echo $broker->company ?></option>
							<?php endforeach; ?>
                        </select>
                    </div>
					<div class="col-sm-12 col-md-3" id="hotel" style="display: none;">
                        <label for="">An Transfer zuweisen</label>
                        <select name="hotel" class="form-control">
                            <?php foreach($transfers as $transfer): ?>
								<option value="<?php echo $transfer->id ?>"><?php echo $transfer->transfer ?></option>
							<?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="tabs">
                    <div class="row my-4 ui-product-tabs">
                        <div class="col-2 ui-product-sep">
                            <a href="javascript:void(0)" class="tab-open ui-product-tabs-text" data-target="#tab1">Immobilie</a>
                        </div>
						<div class="col-2 ui-product-sep">
							<a href="javascript:void(0)" class="tab-open ui-product-tabs-text" data-target="#tab2">Beschreibung</a>
						</div>
                        <div class="col-2 ui-product-sep">
                            <a href="javascript:void(0)" class="tab-open ui-product-tabs-text" data-target="#tab3">Buchungsbestätigung</a>
                        </div>
                        <div class="col-2 ui-product-sep">
                            <a href="javascript:void(0)" class="tab-open ui-product-tabs-text" data-target="#tab4">Zusatzleistung</a>
                        </div>
                        <div class="col-2 ui-product-sep">
                            <a href="javascript:void(0)" class="tab-open ui-product-tabs-text" data-target="#tab5">Kalender</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 tab-content d-none" id="tab1">
                            <div class="row ui-lotdata-block">
                                <h5 class="ui-lotdata-title">Stellplatz</h5>
								<div class="col-sm-12 col-md-12 ui-lotdata">
                                    <div class="row m50">
                                        <div class="col-sm-12 col-md-3">
											<label for="">Immobilien Name</label><br>
											<input type="text" name="parklot" size="35" placeholder="">
										</div>
										<div class="col-sm-12 col-md-2">
											<label for="">Produktgruppe</label><br>
											<select name="group_id" class="form-control">
												<?php foreach ($product_groups as $group) : ?>
													<?php $child_product_groups = Database::getInstance()->getChildProductGroupsByPerentId($group->id); ?>
													<?php if(count($child_product_groups) > 0): ?>
														<?php foreach ($child_product_groups as $child_group) : ?>
														<option value="<?php echo $child_group->id ?>"><?php echo $child_group->name ?></option>
														<?php endforeach; ?>
													<?php else: ?>
														<option value="<?php echo $group->id ?>"><?php echo $group->name ?></option>
													<?php endif; ?>
												<?php endforeach; ?>
											</select>
										</div>
										<div class="col-sm-12 col-md-3">
											<label for="">Adresse</label><br>
											<input type="text" name="parklot_adress" size="45" placeholder="">
										</div>
										<div class="col-sm-12 col-md-3">
											<label for="">Telefon-Nr.</label><br>
											<input type="text" name="parklot_phone" placeholder="">
										</div>
									</div><br>
									<div class="row m50">
										<div class="col-sm-12 col-md-2">
											<label for="">Immobilien Typ</label>
											<select name="parkhaus">
												<option value="Parkhaus überdacht">Parkhaus überdacht</option>
												<option value="Parkhaus nicht überdacht">Parkhaus nicht überdacht</option>
												<option value="Parkplatz überdacht">Parkplatz überdacht</option>
												<option value="Parkplatz nicht überdacht">Parkplatz nicht überdacht</option>
											</select>
										</div>
										<div class="col-sm-12 col-md-1">
											<label for="" class="d-block">Produkt Typ</label>
											<select name="type">
												<option value="shuttle">shuttle</option>
												<option value="valet">valet</option>										
											</select>
										</div>
                                        <div class="col-sm-12 col-md-1 ui-lotdata-date">
											<label for="">Aktiv von</label><br>
											<input type="text" id="proActivdateFrom" name="date_from" size="7" placeholder="Von" class="air-datepicker"
                                                   data-language="de">
										</div>
										<div class="col-sm-12 col-md-1 ui-lotdata-date">
											<label for="">Aktiv bis</label><br>
											<input type="text" id="proActivdateTo" name="date_to" value="2023-12-30" size="7" placeholder="Bis" class="air-datepicker"
                                                   data-language="de">
										</div>
										<div class="col-sm-12 col-md-1">
											<label for="">Vorlaufzeit</label>
											<input type="number" name="booking_lead_time" data-language="de" autocomplete="off">
										</div>
                                    </div><br>
									<div class="row m50">
										<div class="col-sm-12 col-md-1">
											<label for="">Kontigent</label>
											<input type="number" name="contigent">
										</div>
										<div class="col-sm-12 col-md-1">
											<label for="">Prefix</label>
											<input type="text" name="parklot_prefix" placeholder="">
										</div>
										<div class="col-sm-12 col-md-1">
											<label for="">Farbcode</label><br>
											<input type="color" name="parklot_color">
										</div>
										<div class="col-sm-12 col-md-1">
											<label for="">Kurzname</label><br>
											<input type="text" name="parklot_short">
										</div>
										<div class="col-sm-12 col-md-1">
											<label for="">Entfernung</label><br>
											<input type="text" name="parklot_distance">
										</div>
										<div class="col-sm-12 col-md-1">
											<label for="">Preis/Extratag</label><br>
											<input type="number" name="parklot_extraPrice_perDay">
										</div>
										<div class="col-sm-12 col-md-1">
											<label for="">Provision</label><br>
											<input type="number" name="commision">
										</div>
										<div class="col-sm-12 col-md-2">
											<label for="">Provision WS</label><br>
											<input type="number" name="commision_ws">
										</div>
								   </div>
								</div>
							</div>							
                            <div class="row ui-lotdata-block ui-lotdata-block-next restrictions-wrapper">
                                <h5 class="ui-lotdata-title">Beschränkungen / keine Fahrten</h5>
								<div class="col-sm-12 col-md-12 ui-lotdata">
									<div class="row m50">
										<div class="col-12 row-item restriction-item">
											<div class="row">
												<div class="col-sm-12 col-md-2">
													<label for="">Grund</label>
													<input type="text" name="restriction_darum[]" placeholder="">
												</div>
												<div class="col-sm-12 col-md-1 ui-lotdata-date">
													<label for="">Datum</label>
													<input type="text" class="air-datepicker" name="restriction_date[]"
														   data-language="de" placeholder="">
												</div>
												<div class="col-sm-12 col-md-1">
													<label for="">Uhrzeit</label>
													<input type="text" class="timepicker" name="restriction_time[]"
														   placeholder="">
												</div>
												<div class="col-2 add_del_buttons">
													<span class="btn btn-danger del-table-row"
														  data-table="restrictions">x</span>
													<span class="btn btn-secondary plus-icon add-restriction-template">+</span>
												</div>
											</div>
										</div>
									</div>
								</div>
                            </div>                           
							<div class="row ui-lotdata-block ui-lotdata-block-next order_cancellations-wrapper">
								<h5 class="ui-lotdata-title">Stornogebühr</h5>
								<div class="col-sm-12 col-md-12 ui-lotdata">
									<div class="row m50">	
										<div class="col-12 row-item cancellation-item">
											<div class="row">	
												<div class="col-sm-12 col-md-2 ">
													<label for="">Frei Stunden vor Anreise</label>
													<input type="number" name="cancellation_hours[]" class="w100"
														   placeholder="">                                    
												</div>
												<div class="col-sm-12 col-md-2">
													<label for="">Gebühr Fix / Protzent</label>
													<select name="cancellation_type[]" class="w100">
														<option value="fix">Fix</option>
														<option value="percent" selected>%</option>
													</select> 
												</div>	
												<div class="col-sm-12 col-md-1">
													<label for="">Wert</label>
													<input type="number" name="cancellation_value[]" class="w100"
														   placeholder="">
												</div>
												<div class="col-2 add_del_buttons">
													<span class="btn btn-danger del-table-row"
														  data-table="order_cancellations">x</span>
													<span class="btn btn-secondary plus-icon add-cancellation-template">+</span>
												</div>
											</div>
										</div>
									</div>
								</div>
                            </div>
                            <div class="row ui-lotdata-block ui-lotdata-block-next">  
                                <h5 class="ui-lotdata-title">Produktbild</h5>
								<div class="col-sm-12 col-md-12 ui-lotdata">
									<div class="row">
										<div class="col-6">
											<label for="">Produktbilder</label>
											<input type="file" name="images[]" accept="image/x-png,image/gif,image/jpeg"
												   multiple>
										</div>
									</div>
								</div>
                            </div>
                        </div>
						
						<div class="col-12 tab-content d-none" id="tab2">
							<div class="row ui-lotdata-block">
								<h5 class="ui-lotdata-title">Produktbeschreibung</h5>
								<div class="col-sm-12 col-md-12 ui-lotdata">
									<div class="row">
										<div class="col-sm-12 col-md-12">
											<?php wp_editor( "" , "dsc_new", $settings = array("textarea_name"=>"dsc", 'textarea_rows' => get_option('default_post_edit_rows', 20),) ); ?>
										</div>
									</div>
								</div>
							</div>
						</div>
						
                        <div class="col-12 tab-content d-none" id="tab3">
							<div class="row ui-lotdata-block">
								<h5 class="ui-lotdata-title">Buchungsbestätigung Texte</h5>
								<div class="col-sm-12 col-md-12 ui-lotdata">
									<div class="row">
										<div class="col-sm-12 col-md-4">
											<label for="confirmation_byArrival">Bei der Anreise</label>
											<textarea class="" rows="10" cols="45" name="confirmation_byArrival" ></textarea>
										</div>
										<div class="col-sm-12 col-md-4">
											<label for="confirmation_byDeparture">Bei der Abreise</label>
											<textarea class="" rows="10" cols="45" name="confirmation_byDeparture" ></textarea>
										</div>
										<div class="col-sm-12 col-md-4">
											<label for="confirmation_note">Hinweis-Text</label>
											<textarea class="" rows="10" cols="45" name="confirmation_note" ></textarea>
										</div>
									</div>
								</div>
							</div>
                        </div>
                        <div class="col-12 tab-content d-none" id="tab4">
							<div class="row ui-lotdata-block">
								<h5 class="ui-lotdata-title">Zusatzleistungen</h5>
								<div class="col-sm-12 col-md-12 ui-lotdata">
									<div class="row">
										<table class="table table-sm add-ser-check">
											<tbody>
											<?php foreach (Database::getInstance()->getAdditionalServices() as $service) : ?>
												<tr class="check-row" data-id="<?php echo $service->id ?>">
													<td>
														<input type="hidden" name="add_ser_id[]">
														<?php echo $service->name ?>
													</td>
													<td class="text-right"><?php echo number_format($service->price,2) . "€" ?></td>
												</tr>
											<?php endforeach; ?>
											<?php if (count(Database::getInstance()->getAdditionalServices()) <= 0) : ?>
												<td>
													No results found!
												</td>
												<td></td>
												<td></td>
											<?php endif; ?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
                        </div>
						
                        <div class="col-12 tab-content d-none" id="tab5">
                            <div class="row ui-lotdata-block">
								<?php require_once(plugin_dir_path(__FILE__) . '../calendar/index.php') ?>
							</div>
						</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="mt-5 btn btn-primary">Speichern</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
