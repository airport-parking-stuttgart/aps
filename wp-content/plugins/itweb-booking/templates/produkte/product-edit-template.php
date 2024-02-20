<?php
global $wpdb;
$id = $_GET['edit'];
$db = Database::getInstance();
$product = wc_get_product($id);
$orderCancellations = $db->getOrderCancellationByProductId($id);
$parklot = $db->getParklotByProductId($id);
$group_id = $parklot->group_id;
$additionalServices = $db->getAdditionalServices();
$productRestrictions = $db->getRestrictionsByProductId($id);
$productDiscounts = $db->getDiscountsByProductId($id);
$description = $product->get_description();
$attachment_ids = $product->get_gallery_image_ids();
$gallery = [];
$product_groups = Database::getInstance()->getProductGroups();
foreach ($attachment_ids as $attachment_id) {
    $gallery[] = [
        'id' => $attachment_id,
        'url' => wp_get_attachment_url($attachment_id)
    ];
}
$clean_permalink = sanitize_title( $parklot->parklot, $id );

if($parklot->is_for == "hotel"){	
	$variations = $product->get_children();
	foreach ($variations as $variation){
		$product_variation = new WC_Product_Variation($variation);
		$name = explode(' - ', $product_variation->get_name())[1];
		$price = $product_variation->regular_price;
	}
}

//echo "<pre>"; print_r($prices); echo "</pre>";
?>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>
            <?php echo $product->get_title() ?>
        </h3>
    </div>
    <div class="page-body">
        <form method="POST" action="/wp-content/plugins/itweb-booking/classes/Helper.php" enctype="multipart/form-data">
            <input type="hidden" name="task" value="update_product">
            <div class="row">
				<input type="hidden" name="product_id" value="<?php echo $product->get_id() ?>">
				<?php if($parklot->is_for == "hotel"): ?>
					<input type="hidden" name="for_hotel" value="1">
				<?php endif; ?>
				<div class="col-sm-12 col-md-2">
					<label for="">Produkt ist für den</label>
					<select name="product_isfor" class="form-control">
						<option value="betreiber" <?php if($parklot->is_for == "betreiber") echo "selected" ?>>Betreiber</option>                            
						<option value="vermittler" <?php if($parklot->is_for == "vermittler") echo "selected" ?>>Vermittler</option>
						<option value="hotel" <?php if($parklot->is_for == "hotel") echo "selected" ?>>Transfer</option>
					</select>
				</div>
            </div>
            <div class="tabs">
                <div class="row my-4 ui-product-tabs">
                    <div class="col-1 ui-product-sep">
                        <a href="javascript:void(0)" class="tab-open ui-product-tabs-text" data-target="#tab1">Immobilie</a>
                    </div>
					<?php if($parklot->is_for != "hotel"): ?>
						<div class="col-1 ui-product-sep">
							<a href="javascript:void(0)" class="tab-open ui-product-tabs-text" data-target="#tab2">Beschreibung</a>
						</div>
						<div class="col-2 ui-product-sep">
							<a href="javascript:void(0)" class="tab-open ui-product-tabs-text" data-target="#tab3">Buchungsbestätigung</a>
						</div>
						<div class="col-1 ui-product-sep">
							<a href="javascript:void(0)" class="tab-open ui-product-tabs-text" data-target="#tab4">Zusatzleistung</a>
						</div>
					<?php endif; ?>
					<?php if($parklot->is_for != "hotel"): ?>
                    <div class="col-1 ui-product-sep">
                        <a href="javascript:void(0)" class="tab-open ui-product-tabs-text" data-target="#tab5">Kalender</a>
                    </div>
					<?php else: ?>
					<div class="col-1 ui-product-sep">
                        <a href="javascript:void(0)" class="tab-open ui-product-tabs-text" data-target="#tab5">Preisliste</a>
                    </div>
					<?php endif; ?>
                </div>
                <div class="row">
                    <div class="col-12 tab-content" id="tab1">
                        <div class="row ui-lotdata-block">
							<h5 class="ui-lotdata-title">Stellplatz</h5>
							<div class="col-sm-12 col-md-12 ui-lotdata">
								<div class="row m50">
									<div class="col-sm-12 col-md-3">
										<label for="">Immobilien Name</label><br>
										<input type="text" name="parklot" size="35" placeholder="Parkplatz"
													   value="<?php echo $parklot->parklot ?>" required>
									</div>
									<div class="col-sm-12 col-md-2">
										<label for="">Produktgruppe</label><br>
										<select name="group_id" class="form-control">
											<?php foreach ($product_groups as $group) : ?>
												<?php $child_product_groups = Database::getInstance()->getChildProductGroupsByPerentId($group->id); ?>
												<?php if(count($child_product_groups) > 0): ?>
													<?php foreach ($child_product_groups as $child_group) : ?>
													<option value="<?php echo $child_group->id ?>" <?php echo $child_group->id == $parklot->group_id ? "selected" : "" ?>><?php echo $child_group->name ?></option>
													<?php endforeach; ?>
												<?php else: ?>
													<option value="<?php echo $group->id ?>" <?php echo $group->id == $parklot->group_id ? "selected" : "" ?>><?php echo $group->name ?></option>
												<?php endif; ?>
											<?php endforeach; ?>
										</select>
									</div>
									<div class="col-sm-12 col-md-3">
										<label for="">Adresse</label><br>
										<input type="text" name="parklot_adress" size="45" value="<?php echo $parklot->adress ?>" required>
									</div>
									<div class="col-sm-12 col-md-3">
										<label for="">Telefon-Nr.</label><br>
										<input type="text" name="parklot_phone" value="<?php echo $parklot->phone ?>" required>
									</div>
								</div><br>
								<div class="row m50">
									<div class="col-sm-12 col-md-2">
										<label for="">Immobilien Typ</label>
										<select name="parkhaus">
											<option value="Parkhaus überdacht" <?php if($parklot->parkhaus == "Parkhaus überdacht") echo "selected" ?>>Parkhaus überdacht</option>
											<option value="Parkhaus nicht überdacht" <?php if($parklot->parkhaus == "Parkhaus nicht überdacht") echo "selected" ?>>Parkhaus nicht überdacht</option>
											<option value="Parkplatz überdacht" <?php if($parklot->parkhaus == "Parkplatz überdacht") echo "selected" ?>>Parkplatz überdacht</option>
											<option value="Parkplatz nicht überdacht" <?php if($parklot->parkhaus == "Parkplatz nicht überdacht") echo "selected" ?>>Parkplatz nicht überdacht</option>
										</select>
									</div>
									<div class="col-sm-12 col-md-1">
										<label for="" class="d-block">Produkt Typ</label>
										<select name="type">
											<option value="shuttle" <?php echo $parklot->type == 'shuttle' ? 'selected' : '' ?>>shuttle</option>
											<option value="valet" <?php echo $parklot->type == 'valet' ? 'selected' : '' ?>>valet</option>						
										</select>
									</div>
									<div class="col-sm-12 col-md-2 ui-lotdata-date">
										<label for="">Aktiv von</label><br>
										<input type="text" name="date_from" size="7" placeholder="Von" class=""
													   autocomplete="off"
													   data-language="de" value="<?php echo $parklot->datefrom ?>" readonly required>
									</div>
									<div class="col-sm-12 col-md-2 ui-lotdata-date">
										<label for="">Aktiv bis</label><br>
										<input type="text" name="date_to" size="7" placeholder="Bis" class=""
													   autocomplete="off"
													   data-language="de" value="<?php echo $parklot->dateto ?>" readonly required>
									</div>
									<div class="col-sm-12 col-md-1">
										<label for="">Vorlaufzeit</label>
										<input type="number" name="booking_lead_time" autocomplete="off" data-language="de"
											   value="<?php echo $parklot->booking_lead_time != null ? $parklot->booking_lead_time : "0" ?>">
									</div>
								</div><br>
								<div class="row m50">
									<div class="col-sm-12 col-md-1">
										<label for="">Kontigent</label>
										<input type="number" name="contigent" value="<?php echo $parklot->contigent != null ? $parklot->contigent : "0" ?>" required>
									</div>
									<div class="col-sm-12 col-md-1">
										<label for="">Prefix</label>
										<input type="text" name="parklot_prefix" value="<?php echo $parklot->prefix ?>" required>
									</div>
									<div class="col-sm-12 col-md-1">
										<label for="">Farbcode</label><br>
										<input type="color" name="parklot_color" value="<?php echo $parklot->color ?>" required>
									</div>
									<div class="col-sm-12 col-md-1">
										<label for="">Kurzname</label><br>
										<input type="text" name="parklot_short" value="<?php echo $parklot->parklot_short ?>" required>
									</div>
									<div class="col-sm-12 col-md-1">
										<label for="">Entfernung</label><br>
										<input type="text" name="parklot_distance" value="<?php echo $parklot->distance ?>">
									</div>
									<div class="col-sm-12 col-md-1">
										<label for="">Preis/Extratag</label><br>
										<input type="number" name="parklot_extraPrice_perDay" value="<?php echo $parklot->extraPrice_perDay ?>">
									</div>
									<div class="col-sm-12 col-md-1">
										<label for="">Provision</label><br>
										<input type="number" name="commision" value="<?php echo $parklot->commision ?>">
									</div>
									<div class="col-sm-12 col-md-2">
										<label for="">Provision WS</label><br>
										<input type="number" name="commision_ws" value="<?php echo $parklot->commision_ws ?>">
									</div>
								</div>
							</div>
						</div>
                            					
                        <div class="row ui-lotdata-block ui-lotdata-block-next restrictions-wrapper">
                            <h5 class="ui-lotdata-title">Beschränkungen / keine Fahrten</h5>
							<div class="col-sm-12 col-md-12 ui-lotdata">
								<?php foreach ($productRestrictions as $restriction) : ?>
									<div class="row m50">
										<div class="col-12 row-item restriction-item">
											<div class="row">
												<div class="col-sm-12 col-md-2">
													<input type="hidden" name="restriction_id[]"
														   value="<?php echo $restriction->id ?>">
													<label for="">Grund</label>
													<input type="text" name="restriction_darum[]" placeholder=""
														   value="<?php echo $restriction->darum ?>">
												</div>
												<div class="col-sm-12 col-md-1 ui-lotdata-date">
													<label for="">Datum</label>
													<input type="text" class="air-datepicker" name="restriction_date[]"
														   data-language="de" placeholder="" autocomplete="off"
														   value="<?php echo $restriction->date ?>">
												</div>
												<div class="col-sm-12 col-md-1">
													<label for="">Uhrzeit</label>
													<input type="text" class="timepicker" name="restriction_time[]"
														   autocomplete="off"
														   placeholder="" value="<?php echo $restriction->time ?>">
												</div>
												<div class="col-2 add_del_buttons">
													<span class="btn btn-danger del-table-row"
														  data-id="<?php echo $restriction->id ?>"
														  data-table="restrictions">x</span>
													<span class="btn btn-secondary plus-icon add-restriction-template">+</span>
												</div>
											</div>
										</div>
									</div>
								<?php endforeach; ?>
								<div class="row m50">
									<div class="col-12 row-item restriction-item">
										<div class="row">
											<div class="col-sm-12 col-md-2">
												<input type="hidden" name="restriction_id[]">
												<label for="">Grund</label>
												<input type="text" name="restriction_darum[]" placeholder="">
											</div>
											<div class="col-sm-12 col-md-1 ui-lotdata-date">
												<label for="">Datum</label>
												<input type="text" class="air-datepicker" name="restriction_date[]"
													   autocomplete="off"
													   data-language="de" placeholder="">
											</div>
											<div class="col-sm-12 col-md-1">
												<label for="">Uhrzeit</label>
												<input type="text" class="timepicker" name="restriction_time[]"
													   autocomplete="off"
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
						<?php if($parklot->is_for != "hotel"): ?>
                        <!--<div class="row ui-lotdata-block ui-lotdata-block-next discounts-wrapper">
                            <h5 class="ui-lotdata-title">Rabattierung</h5>
							<div class="col-sm-12 col-md-12 ui-lotdata">
								<?php foreach ($productDiscounts as $discount) : ?>
									<div class="row m50">
										<div class="col-12 row-item discount-item">
											<div class="row">
												<div class="col-sm-12 col-md-2">
													<input type="hidden" name="discount_id[]"
														   value="<?php echo $discount->id ?>">
													<label for="">Rabattbezeichnung</label>
													<input type="text" name="discount_name[]" placeholder=""
														   value="<?php echo $discount->name ?>">
												</div>
												<div class="col-sm-12 col-md-2">
													<label for="">Saison Rabat von - bis</label>
													<input type="text" class="datepicker-range" name="discount_interval[]"
														   data-multiple-dates-separator=" - "
														   autocomplete="off"
														   placeholder=""
														   data-from="<?php echo $discount->interval_from ?>"
														   data-to="<?php echo $discount->interval_to ?>">
												</div>
												<div class="col-sm-12 col-md-1">
													<label for="">Art</label><br>
													<select name="discount_type[]">
														<option value="fix" <?php echo $discount->type == 'fix' ? 'selected' : '' ?>>
															Fix
														</option>
														<option value="percent" <?php echo $discount->type == 'percent' ? 'selected' : '' ?>>
															%
														</option>
													</select>
												</div>
												<div class="col-sm-12 col-md-1">
													<label for="">Wert</label>
													<input type="text" name="discount_value[]" placeholder=""
														   value="<?php echo $discount->value ?>">
												</div>
												<div class="col-sm-12 col-md-1">
													<label for="">Voraustage</label>
													<input type="number" name="discount_days_before[]" placeholder=""
														   value="<?php echo $discount->days_before ?>">
												</div>
												<div class="col-sm-12 col-md-1">
													<label for="">Rabattkontingent</label>
													<input type="number" name="discount_contigent_limit[]" class="w100"
														   placeholder=""
														   value="<?php echo $discount->discount_contigent ?>">
												</div>
												<div class="col-sm-12 col-md-1">
													<label for="">Min Tage</label>
													<input type="number" name="discount_min_days[]" class="w100"
														   placeholder=""
														   value="<?php echo $discount->min_days ?>">
												</div>
												<div class="col-sm-12 col-md-1">
													<label for="">stornierbar</label>
													<input type="checkbox" name="discount_cancel[]" class="w100" <?php echo $discount->cancel == 'on' ? 'checked' : "" ?>>
												</div>
                                                <div class="col-12 order-md-2">
                                                    <label for="">Nachricht</label>
                                                    <?php wp_editor($discount->message, 'editor-'. generateToken(64), $settings = array('textarea_name' => 'discount_message[]', 'wpautop' => false)); ?>
                                                </div>
												<div class="col-2 add_del_buttons">
													<span class="btn btn-danger del-table-row"
														  data-table="discounts" data-id="<?php echo $discount->id ?>">x</span>
													<span class="btn btn-secondary plus-icon add-discount-template">+</span>
												</div>
											</div>
                                            <hr>
										</div>
									</div>
								<?php endforeach; ?>
								<div class="row m50">
									<div class="col-12 row-item discount-item">
										<div class="row">
											<div class="col-sm-12 col-md-2">
												<input type="hidden" name="discount_id[]">
												<label for="">Rabattbezeichnung</label>
												<input type="text" name="discount_name[]" placeholder="">
											</div>
											<div class="col-sm-12 col-md-2">
												<label for="">Saison Rabat von - bis</label>
												<input type="text" class="datepicker-range" name="discount_interval[]"
													   data-multiple-dates-separator=" - "
													   autocomplete="off"
													   placeholder="">
											</div>
											<div class="col-sm-12 col-md-1">
												<label for="">Art</label><br>
												<select name="discount_type[]">
													<option value="fix">Fix</option>
													<option value="percent">%</option>
												</select>
											</div>
											<div class="col-sm-12 col-md-1">
												<label for="">Wert</label>
												<input type="text" name="discount_value[]" placeholder="">
											</div>
											<div class="col-sm-12 col-md-1">
												<label for="">Voraustage</label>
												<input type="number" name="discount_days_before[]" placeholder="">
											</div>
											<div class="col-sm-12 col-md-1">
												<label for="">Rabattkontingent</label>
												<input type="number" name="discount_contigent_limit[]" class="w100"
													   placeholder="">
											</div>
											<div class="col-sm-12 col-md-1">
												<label for="">Min Tage</label>
												<input type="number" name="discount_min_days[]" class="w100"
													   placeholder="">
											</div>
											<div class="col-sm-12 col-md-1">
												<label for="">stornierbar</label>
												<input type="checkbox" name="discount_cancel[]" class="w100">
											</div>
                                            <div class="col-12 order-md-2">
                                                <label for="">Nachricht</label>
                                                <?php wp_editor('', 'editor-'. generateToken(64), $settings = array('textarea_name' => 'discount_message[]', 'wpautop' => false)); ?>
                                            </div>
											<div class="col-2 add_del_buttons">
													<span class="btn btn-danger del-table-row"
														  data-table="discounts">x</span>
												<span class="btn btn-secondary plus-icon add-discount-template">+</span>
											</div>
										</div>
                                        <hr>
									</div>
								</div>
							</div>
						</div>-->
						<?php endif; ?>
                        <div class="row ui-lotdata-block ui-lotdata-block-next order_cancellations-wrapper">
                            <h5 class="ui-lotdata-title">Stornogebühr</h5>
							<div class="col-sm-12 col-md-12 ui-lotdata">
								<?php foreach ($orderCancellations as $cancellation) : ?>
									<div class="row m50">
										<div class="col-12 row-item cancellation-item">
											<div class="row">
												<div class="col-sm-12 col-md-2">
													<input type="hidden" name="order_cancellation_id[]"
														   value="<?php echo $cancellation->id ?>">
													<label for="">Frei Stunden vor Anreise</label>
													<input type="number" name="cancellation_hours[]" class="w100"
														   placeholder=""
														   value="<?php echo $cancellation->hours_before ?>">
												</div>
												<div class="col-sm-12 col-md-2">
													<label for="">Gebühr Fix / Protzent</label>
													<select name="cancellation_type[]" class="w100">
														<option value="fix" <?php echo $cancellation->type == 'fix' ? 'selected' : '' ?>>Fix</option>
														<option value="percent" <?php echo $cancellation->type == 'percent' ? 'selected' : '' ?>>%</option>
													</select>
												</div>
												<div class="col-sm-12 col-md-1">
													<label for="">Wert</label>
													<input type="number" name="cancellation_value[]" class="w100"
														   placeholder="Wert" value="<?php echo $cancellation->value ?>">
												</div>
												<div class="col-2 add_del_buttons">
													<span class="btn btn-danger del-table-row"
														  data-table="order_cancellations" data-id="<?php echo $cancellation->id ?>">x</span>
													<span class="btn btn-secondary plus-icon add-cancellation-template">+</span>
												</div>
											</div>
										</div>
									</div>
								 <?php endforeach; ?>
								<div class="row m50">
									<div class="col-12 row-item cancellation-item">
										<div class="row">
											<div class="col-sm-12 col-md-2">
												<input type="hidden" name="order_cancellation_id[]">
												<label for="">Frei Stunden vor Anreise</label>
												<input type="number" name="cancellation_hours[]" class="w100" placeholder="">
											</div>
											<div class="col-sm-12 col-md-2">
												<label for="">Gebühr Fix / Protzent</label>
												<select name="cancellation_type[]" class="w100">
													<option value="fix">Fix</option>
													<option value="percent">%</option>
												</select>
											</div>
											<div class="col-sm-12 col-md-1">
												<label for="">Wert</label>
												<input type="number" name="cancellation_value[]" class="w100" placeholder="Wert">
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
						<?php if($parklot->is_for != "hotel"): ?>
                        <div class="row ui-lotdata-block ui-lotdata-block-next">                       
                            <h5 class="ui-lotdata-title">Produktbild</h5>
							<div class="col-sm-12 col-md-12 ui-lotdata">
								<div class="row">
									<div class="col-6">
										<label for="">Produktbilder</label>
										<input type="file" name="images[]" accept="image/x-png,image/gif,image/jpeg"
											   multiple>
									</div>
									<div class="col-12 m60 gallery-images">
										<div class="row">
											<?php foreach ($gallery as $galleryImage): ?>
												<?php if ($galleryImage['url']) : ?>
													<div class="col-12 col-sm-6 col-md-3 gallery-image">
														<span class="del-img"
															  data-id="<?php echo $galleryImage['id'] ?>">X</span>
														<img src="<?php echo $galleryImage['url'] ?>" alt="">
													</div>
												<?php endif; ?>
											<?php endforeach; ?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<?php endif; ?>
                    </div>
					<div class="col-12 tab-content d-none" id="tab2">
						<div class="row ui-lotdata-block">
							<h5 class="ui-lotdata-title">Produktbeschreibung</h5>
							<div class="col-sm-12 col-md-12 ui-lotdata">
								<div class="row">
									<div class="col-sm-12 col-md-12">
										<?php wp_editor( "$description" , "dsc_".$id, $settings = array("textarea_name"=>"dsc", 'textarea_rows' => get_option('default_post_edit_rows', 20),) ); ?>
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
										<textarea class="" rows="10" cols="45" name="confirmation_byArrival" value="<?php echo $parklot->confirmation_byArrival ?>"><?php echo $parklot->confirmation_byArrival ?></textarea>
									</div>
									<div class="col-sm-12 col-md-4">
										<label for="confirmation_byDeparture">Bei der Abreise</label>
										<textarea class="" rows="10" cols="45" name="confirmation_byDeparture" value="<?php echo $parklot->confirmation_byDeparture ?>"><?php echo $parklot->confirmation_byDeparture ?></textarea>
									</div>
									<div class="col-sm-12 col-md-4">
										<label for="confirmation_note">Hinweis-Text</label>
										<textarea class="" rows="10" cols="45" name="confirmation_note" value="<?php echo $parklot->confirmation_note ?>"><?php echo $parklot->confirmation_note ?></textarea>
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
										<?php
										$productAdditionalServices = $db->getProductAdditionalServicesByProductId($product->get_id());
										?>
										<?php foreach (Database::getInstance()->getAdditionalServices() as $service) : ?>
											<?php
											$exists = false;
											foreach ($productAdditionalServices as $pas) {
												if ($service->id == $pas->add_ser_id) {
													$exists = true;
													break;
												}
											}
											?>
											<tr class="check-row <?php echo $exists ? 'mark_done' : '' ?>"
												data-id="<?php echo $service->id ?>">
												<td>
													<input type="hidden" name="add_ser_id[]" value="<?php echo $service->id ?>">
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
					<?php if($parklot->is_for != "hotel"): ?>
                    <div class="col-12 tab-content d-none" id="tab5">
                        <div class="row ui-lotdata-block">
							<?php require_once(plugin_dir_path(__FILE__) . '../calendar/index.php') ?>
						</div>
					</div>
					<?php else: ?>
					<div class="col-12 tab-content d-none" id="tab5">
						<div class="row ui-lotdata-block">
							<h5 class="ui-lotdata-title">Preisliste</h5>
							<div class="col-sm-12 col-md-12 ui-lotdata">
								<div class="row">
									<?php foreach ($variations as $variation): ?>
										<?php $product_variation = new WC_Product_Variation($variation); ?>
										<?php $name = explode(' - ', $product_variation->get_name()); ?>
										<div class="col-sm-12 col-md-4">
											<br><label for=""><?php echo end($name) ?></label><br>
											<input type="text" class="" name="<?php echo $product_variation->get_id() ?>" value="<?php echo $product_variation->regular_price ?>">
										</div>
									<?php endforeach; ?>								
								</div>
							</div>
						</div>
					</div>
					<?php endif; ?>
                </div>
            </div><br>
            <div class="row">
                <div class="col-1">
                    <button type="submit" class="btn btn-primary">Speichern</button>
                </div>
				 <div class="col-1">
                    <a href="<?php echo '/wp-admin/admin.php?page=produkte-bearbeiten' ?>" class="btn btn-secondary" >Schließen</a>
                </div>
            </div>
        </form>
    </div>
</div>