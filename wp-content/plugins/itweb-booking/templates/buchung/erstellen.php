<?php
session_start();
unset($_SESSION['errors']['all']);
global $wpdb;
$db = Database::getInstance();


if($_GET['step'] == 2){
	$startDate = isset($_GET['date_from']) ? date_format(date_create($_GET['date_from']), 'Y-m-d') : date('Y-m-d');
	$endDate = isset($_GET['date_to']) ? date_format(date_create($_GET['date_to']), 'Y-m-d') : date('Y-m-d');
	$dayDiff = getDaysBetween2Dates(new DateTime($startDate), new DateTime($endDate));
	$startDateEn = date_format(date_create($startDate), 'Y-m-d H:i');
	$endDateEn = date_format(date_create($endDate), 'Y-m-d H:i');
	$startDateOnly = date_format(date_create($startDate), 'Y-m-d');
	$endDateOnly = date_format(date_create($endDate), 'Y-m-d');
	
	$conDate[0] = $startDateOnly;
	$conDate[1] = $endDateOnly;
	$allContingent = Database::getInstance()->getAllContingent($conDate);
	foreach($allContingent as $ac){
		$set_con[$ac->date."_".$ac->product_id] = $ac->contingent;
	}
		
	$period = new DatePeriod(
		new DateTime($startDateOnly),
		new DateInterval('P1D'),
		new DateTime($endDateOnly . '+1 day')
	);
	$periodCount = iterator_count($period);

	$sql = "select parklots.*";

	foreach ($period as $key => $value) {
		   $date = $value->format('Y-m-d');

	$sql .= ", COUNT(DISTINCT CASE WHEN '{$date}' BETWEEN DATE(orders.date_from) AND DATE(orders.date_to) THEN orders.id END) AS used_{$value->format('Y_m_d')} ";
	}
	$sql .= "FROM {$wpdb->prefix}itweb_parklots parklots 
			LEFT JOIN {$wpdb->prefix}itweb_orders orders ON parklots.product_id = orders.product_id 
			LEFT JOIN {$wpdb->prefix}posts po ON po.ID = orders.order_id 
			WHERE ('$startDateOnly' BETWEEN parklots.datefrom AND parklots.dateto) AND ('$endDateOnly' BETWEEN parklots.datefrom AND parklots.dateto) 
			AND parklots.is_for = 'betreiber' AND parklots.type = 'shuttle' AND parklots.deleted = 0 and po.post_status = 'wc-processing'
			GROUP BY parklots.id ORDER BY parklots.order_lot";

	$results = $wpdb->get_results($sql);
	
	$sql = "select parklots.*";

	foreach ($period as $key => $value) {
		$date = $value->format('Y-m-d');

		$sql .= ", COUNT(DISTINCT CASE WHEN '{$date}' BETWEEN DATE(orders.date_from) AND DATE(orders.date_to) THEN orders.id END) AS used_{$value->format('Y_m_d')} ";
	}
	
	$sql .= "FROM {$wpdb->prefix}itweb_parklots parklots 
			LEFT JOIN {$wpdb->prefix}itweb_orders orders ON parklots.product_id = orders.product_id 
			LEFT JOIN {$wpdb->prefix}posts po ON po.ID = orders.order_id 
			WHERE ('$startDateOnly' BETWEEN parklots.datefrom AND parklots.dateto) AND ('$endDateOnly' BETWEEN parklots.datefrom AND parklots.dateto) 
			AND parklots.is_for = 'vermittler' AND parklots.type = 'shuttle' AND parklots.deleted = 0 and po.post_status = 'wc-processing'
			GROUP BY parklots.id ORDER BY parklots.order_lot";

	$results_apg = $wpdb->get_results($sql);
}

if($_GET['step'] == 2 && $_GET['for'] == 'tf'){
	$products = $db->getTransferLots();
}

if($_GET['step'] == 3){
	$startDate = isset($_GET['date_from']) ? date_format(date_create($_GET['date_from']), 'Y-m-d') : date('Y-m-d');
	$endDate = isset($_GET['date_to']) ? date_format(date_create($_GET['date_to']), 'Y-m-d') : date('Y-m-d');
	$parklot = $db->getParklotByProductId($_GET['pid']);
	$restrictions = $db->getRestrictionsByProductId($_GET['pid']);
	$productAdditionalServices = $db->getProductAdditionalServices($_GET['pid']);
	
	if (Discounts::getAvailableDiscounts($_GET['pid'], $startDate, $endDate) && isset($_GET['ws'])){
		$priceList = number_format(Pricelist::calculateAndDiscount($_GET['pid'], $startDate, $endDate));
	}
	else	
		$priceList =  number_format(Pricelist::calculate($_GET['pid'], $startDate, $endDate), 2, '.', '');
}

if($_POST['step'] == 4){
	$db->saveOrder($_POST);
}


//echo "<pre>"; print_r($results_apg); echo "</pre>";
?>

<style>
.head_title{
	text-align: center;
	background-color: #cceeff;
	padding: 10 0;
}
.product-cart, .product-cart-valet{
	width: 30%;
	background-color: white;
	margin-top: 10px;
	margin-right: 10px;
	float: left;
	border: solid 1px #2196f3;
}

.airport-div-card-head {
  position: relative;
}
.airport-div-card-head-note{
	position: absolute;
	background-color: red;
	color: white;
	padding: 0 10px;
	border-radius: 5px;
}
.airport-div-card-head .airport-img{
	max-height: 125px;
	width: 100%;
	min-height: 125px;
}

.airport-div_content-1{
	width: auto;
	background-color: #2196f3;
	margin-top: -5px;
	min-height: 65px;
}
ul.airport-div_content-card-1{
	list-style: none;
	margin: 0;
	padding: 4px;
	border-radius: 7px;
}
ul.airport-div_content-card-1 li{
	display: table-cell;
}
.airport-div_content-card-1 p{
	margin: 0;
	font-weight: 600;
	margin-left: 5px;
	color: white;
}

.card-body_attr{
	min-height: 110px;
}

a.btn.disabled{
	pointer-events: none;
}
.btn.disabled{
	opacity: .65;
}

.content_produkte{
	width: 100%;
	clear: left;
}

.red-text{
	color: red !important;
}

.center {
  text-align: center;
}

.booking_image{
	width: 100%;
	height: auto;
}

.btn-order-parklot{
	padding: 4px !important;
	margin-top: 10px;
}

.card-body_attr > ul > li{
	margin-top: 10px;
}

.flex-container {
  display: flex;
  width: 100%;
  flex-flow: row wrap;
  --justify-content: center;
}
.flex-container > div {
  margin: 10px; 
  font-size: 30px;
  width: 250px;
}

.airport-div-card-head-note-green{
	position: absolute;
	background-color: #076320;
	color: white;
	padding: 0 10px;
	border-radius: 5px;
	font-size: 16px;
}
.airport-div-card-head-note-red{
	position: absolute;
	background-color: #d70000;;
	color: white;
	padding: 0 10px;
	border-radius: 5px;
	font-size: 16px;
}

.card-body{
	margin-left: 10px;
	font-size: 14px;
}

.left_produkte, .right_produkte{
	width: 20%;
	float: left;
}

.shuttle, .valet{
	float: left;
	width: 100%;
	text-align: center;
	background-color: #cceeff;
	padding: 5 0;
	font-size: 16px;
}

.footer{
	position: relative;
	min-height: 75px;
}

.footer .book-btn-dsc{
	position: absolute;
	bottom: 60px;
}
.footer .book-btn{
	position: absolute;
	bottom: 15px;
}
.card-body_title{
	min-height: 40px;
}


.tooltip {
  position: relative;
  display: inline-block;
  --border-bottom: 1px dotted black;
}

.tooltip .tooltiptext {
  visibility: hidden;
  width: 120px;
  background-color: white;
  color: red;
  padding: 10px;
  text-align: center;
  border-radius: 2px;
  border: 2px dotted #2196f3;
  
  /* Position the tooltip */
  position: absolute;
  z-index: 1;
  bottom: 100%;
  left: 50%;
  margin-left: -60px;
}

.tooltip:hover .tooltiptext {
  visibility: visible;
}

</style>
<script src="/wp-content/plugins/itweb-booking/assets/bootstrap-4.5.3-dist/js/popper.min.js"></script>
<script src="/wp-content/plugins/itweb-booking/assets/bootstrap-4.5.3-dist/js/bootstrap.min.js"></script>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-title itweb_adminpage_head">
		<h3>Buchung Erstellen</h3>
	</div>
	<div class="page-body">
		<?php if(empty($_GET['step'])): ?>
			<form class="form-filter">
				<div class="row ui-lotdata-block ui-lotdata-block-next">
					<h5 class="ui-lotdata-title">Produkt wählen</h5>
					<div class="col-sm-12 col-md-12 ui-lotdata">
						<div class="row m10">							
							<div class="col-sm-12 col-md-1 ui-lotdata-date">								
								<label for="">Anreisedatum</label><br>
								<input type="text" name="date_from" class="air-datepicker form-item form-control date-from"									   
									   data-language="de" autocomplete="off"
									   readonly required>
							</div>					
							<div class="col-sm-12 col-md-1 ui-lotdata-date">								
								<label for="">Abreisedatum</label><br>
								<input type="text" name="date_to" class="air-datepicker form-item form-control date-to" 									   
									   data-language="de" autocomplete="off"
									   readonly required>
							</div>
							<div class="col-sm-12 col-md-1 ui-lotdata-date">								
								<label for="">Buchung für</label><br>
								<select name="for" class="form-item form-control">
									<option value="sv">Shuttle/Valet</option>
									<option value="tf">Transfer</option>
								</select>
							</div>
						</div>						
						<br>
						<div class="row m10">
							<div class="col-sm-12 col-md-1">
								<button type="submit" class="btn btn-primary" class="form-item form-control">Weiter</button>
							</div>
						</div>
					</div>
				</div>
				<input type="hidden" name="step" value="2" class="form-item form-control">
			</form>
		<?php endif; ?>
		<?php if($_GET['step'] == 2 && $_GET['for'] == 'sv'): ?>			
			<?php
				if($_GET['date_from'] == null || $_GET['date_to'] == null)
					header('Location: /wp-admin/admin.php?page=buchung-erstellen');
			?>
			<form class="form-filter">
				<div class="row ui-lotdata-block ui-lotdata-block-next">
					<h5 class="ui-lotdata-title">Datum wählen</h5>
					<div class="col-sm-12 col-md-12 ui-lotdata">
						<div class="row m10">
							<div class="col-sm-12 col-md-2">
								<input type="hidden" name="date_from" value="<?php echo $_GET['date_from'] ?>" class="form-control">
								<label for="">Anreisedatum</label><br>
								<input type="text" value="<?php echo $_GET['date_from'] ?>" size="10" readonly>
							</div>
							<div class="col-sm-12 col-md-2">
								<input type="hidden" name="date_to" value="<?php echo $_GET['date_to'] ?>" class="form-control">
								<label for="">Abreisedatum</label><br>
								<input type="text" value="<?php echo $_GET['date_to'] ?>" size="10" readonly>
							</div>
							<div class="col-sm-12 col-md-1">                    
								<br><a href="<?php echo '/wp-admin/admin.php?page=buchung-erstellen' ?>" class="btn btn-secondary" >Zurück</a>
							</div>
						</div>
							<!--<div class="col-sm-12 col-md-3">
								<label for="">Product</label><br>
								<select name="pid" class="form-item form-control">
									<?php foreach ($results as $parklot) : ?>
										<option value="<?php echo $parklot->product_id ?>" <?php echo $parklot->product_id == $_GET['pid'] ? 'selected' : '' ?>>
											<?php echo $parklot->parklot ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>-->
							
							
						<div class="flex-container">
						<?php $showLot = 0; $x = 0;?>
                            <?php foreach ($results as $parklot) : ?>
                                <?php
                                $product = wc_get_product($parklot->product_id);
                                $product_url = get_permalink($product->get_id());
                                $rating = $product->get_average_rating();
                                $count = $product->get_rating_count();
                                $image_id = $product->get_image_id();
                                $ratingIng = (int)$rating;
                                $image_url = wp_get_attachment_image_url($image_id, 'full');
                                $image_url = $image_url ? $image_url : '/wp-content/uploads/woocommerce-placeholder-600x600.png';
								$canBook = "";
								$rabatt = Discounts::getDiscounts($parklot->product_id, 100, dateFormat($startDate), dateFormat($endDate));

                                if ($ratingGet && !in_array($ratingIng, explode(',', $ratingGet))
                                    || $product->get_id() == __HOTEL_PRODUCT_ID
//                                    || !$parklotObj->canOrderLeadTime($startDate, date('H:i'))
                                ) {
                                    echo '<script>decreaseFreeParklots();</script>';
                                    continue;
                                }

								if((int)Pricelist::calculate($product->get_id(), $startDate, $endDate) <= 0){
									echo '<script>decreaseFreeParklots();</script>';
									continue;
								}

                                foreach($period as $key=>$value){
                                    $property = 'used_' . $value->format('Y_m_d');
                                    if($set_con[$value->format('Y-m-d')."_".$parklot->product_id] != null)
										$con = $set_con[$value->format('Y-m-d')."_".$parklot->product_id];
									else
										$con = $parklot->contigent;
									
									// get APG used
									if($parklot->product_id == 537){
										foreach($results_apg as $apg){
											if($apg->product_id == 595){
												$parklot->$property += $apg->$property;
											}
										}
									}
									if($parklot->product_id == 592){
										foreach($results_apg as $apg){
											if($apg->product_id == 3080){
												$parklot->$property += $apg->$property;
											}
										}
									}
									if($parklot->product_id == 619){
										foreach($results_apg as $apg){
											if($apg->product_id == 3081){
												$parklot->$property += $apg->$property;
											}
										}
									}
									if($parklot->product_id == 873){
										foreach($results_apg as $apg){
											if($apg->product_id == 3082){
												$parklot->$property += $apg->$property;
											}
										}
									}
									if($parklot->product_id == 24222){
										foreach($results_apg as $apg){
											if($apg->product_id == 24224){
												$parklot->$property += $apg->$property;
											}
										}
									}
									if($parklot->product_id == 24226){
										foreach($results_apg as $apg){
											if($apg->product_id == 24228){
												$parklot->$property += $apg->$property;
											}
										}
									}
									
									if($parklot->$property >= $con){
                                        $canBook = "AUSGEBUCHT";
                                        echo '<script>decreaseFreeParklots();</script>';
										$showLot--;
                                    }
                                }

								$restrictions = Database::getInstance()->getRestrictionsByProductId($parklot->product_id);
								foreach($restrictions as $key=>$value){
									if($value->date == $startDate || $value->date == $endDate){
										$canBook = "GESCHLOSSEN";
										//echo '<script>decreaseFreeParklots();</script>';
										$showLot--;
									}
								}
								//echo "<pre>";print_r($restrictions);echo "</pre>";

//								if ($parklot->used >= $parklot->contigent){
//									$canBook = false;
//									echo '<script>
//                                        decreaseFreeParklots();
//                                    </script>';
//								}

								if(date_format(date_create($_GET['datefrom']), 'm') == '12' && date_format(date_create($_GET['datefrom']), 'd') == '31')
								{
									$canBook = "GESCHLOSSEN";
									$showLot--;
								}
								if(date_format(date_create($_GET['datefrom']), 'm') == '01' && date_format(date_create($_GET['datefrom']), 'd') == '01')
								{
									$canBook = "GESCHLOSSEN";
									$showLot--;
								}

								if(date_format(date_create($_GET['dateto']), 'm') == '12' && date_format(date_create($_GET['dateto']), 'd') == '31')
								{
									$canBook = "GESCHLOSSEN";
									$showLot--;
								}
								if(date_format(date_create($_GET['dateto']), 'm') == '01' && date_format(date_create($_GET['dateto']), 'd') == '01')
								{
									$canBook = "GESCHLOSSEN";
									$showLot--;
								}
								$showLot++;

								if($parklot->product_id == 41453 || $parklot->product_id == 41466 || $parklot->product_id == 41468 || $parklot->product_id == 41470 || $parklot->product_id == 41472 || $parklot->product_id == 41474){
									$class1 = "shift-left";
									$class4 = "book-btn";
								}
								else{
									$class1 = "";
									$class4 = "";
								}

								if($x == 0){
									$x++;
									//continue;
								}
								
                                ?>
                                
								<div class="product-cart">
									<div class="airport-div-card-head">
										<!-- <div class="airport-div_img"> -->
										<?php if($rabatt[0] != ""): ?>
											<div class="airport-div-card-head-note-green">
												<?php echo $rabatt[1] . " (-" . $rabatt[0] . ")" ?>
											</div>
										<?php elseif($canBook != ""): ?>
											<div class="airport-div-card-head-note-red">
												<?php echo $canBook ?>
											</div>
										<?php endif; ?>
										<img class="airport-img" src="<?php echo $image_url ?>" alt="">
										<?php if(!empty($parklot->adress)) : ?>
										<div class="airport-div_content-1">
											<ul class="airport-div_content-card-1">
												<li>													
													<img src="/wp-content/themes/hello-elementor-child/inc/assets/images/point-img.png" alt="">													
												</li>
												<li>
													<p class="color-white"><?php echo $parklot->adress ?></p>
												</li>
											</ul>
										</div> <!-- end of airport-div_content-1 -->
										<?php endif; ?>
									</div>
									<div class="card-body">
										<div class="card-body_title">
											<h5 class="card-body-title"><?php echo $parklot->parklot ?></h5>
										</div>
										<div class="card-body_attr">
											<ul>
												<li>
													<img src="/wp-content/themes/hello-elementor-child/inc/assets/images/car-icon.png" alt="">
													<span class="card-body__starspan"><?php echo $parklot->type ?></span>
												</li>
												<li>
													<img src="/wp-content/themes/hello-elementor-child/inc/assets/images/parking-icon.png" alt="">
													<?php $typ = str_replace("Parkhaus ", "", $parklot->parkhaus); ?>
													<?php $typ = str_replace("Parkplatz ", "", $typ); ?>
													<span class="card-body__starspan"><?php echo $typ ?></span>
												</li>
												<li>
													<img src="/wp-content/themes/hello-elementor-child/inc/assets/images/distance-icon.png" alt="">
													<span class="card-body__starspan"><?php echo $parklot->distance . " Min."?></span>
												</li>
												<?php if($parklot->type == "valet"): ?>
												<li>
													<span class="card-body__starspan"><?php echo "Bei Anreise 00 - 06 Uhr <br>+ 30€ Nachtzuschlag"?></span>
												</li>
												<?php endif; ?>
											</ul>
										</div>
										<div class="footer">
											<?php if($canBook == ""): ?>
												<a href="<?php echo '/wp-admin/admin.php?page=buchung-erstellen&date_from='.$_GET['date_from'].'&date_to='.$_GET['date_to'].'&step=3&pid='.$parklot->product_id ?>"
														style="<?php
															echo Discounts::getAvailableDiscounts($product->get_id(), $startDate, $endDate) ?
																'color: #3a8ae2; background: none !important; border: 1px solid #3a8ae2' : ''
														?>"
														class="btn btn-primary btn-md pl-full-width btn-suchen-card pl-margin-top-10 btn-order-parklot form-item form-control <?php echo $class4 ?>">
													<?php echo number_format(Pricelist::calculate($product->get_id(), $startDate, $endDate), 2, '.', '') ?>
													€ - Weiter
												</a>
												<?php if (Discounts::getAvailableDiscounts($product->get_id(), $startDate, $endDate)) : ?>
													<a href="<?php echo '/wp-admin/admin.php?page=buchung-erstellen&date_from='.$_GET['date_from'].'&date_to='.$_GET['date_to'].'&step=3&pid='.$parklot->product_id.'&ws=1' ?>" 
															class="btn btn-primary btn-md pl-full-width btn-suchen-card pl-margin-top-10 btn-order-parklot  <?php echo $class4 ?>">
														<i class="fas fa-info"></i>&nbsp;
														<?php echo number_format(Pricelist::calculateAndDiscount($product->get_id(), $startDate, $endDate), 2, '.', '') ?>
														€ - <?php echo $rabatt[1] ?>
													</a>
												<?php endif; ?>
											<?php else: ?>
												<button style="<?php
															echo Discounts::getAvailableDiscounts($product->get_id(), $startDate, $endDate) ?
																'color: #3a8ae2; background: none !important; border: 1px solid #3a8ae2' : ''
														?>"
														class="btn btn-primary btn-md pl-full-width btn-suchen-card pl-margin-top-10 btn-order-parklot form-item form-control <?php echo $class4 ?>" disabled>
													<?php echo $canBook; ?>
												</button>
												<?php if (Discounts::getAvailableDiscounts($product->get_id(), $startDate, $endDate)) : ?>
													<button style="margin-top: 10px;" data-toggle="tooltip" data-placement="top" title="<?php echo $rabatt[1] . " (-" . $rabatt[0] . ")<br>" . $rabatt[2] ?>" data-html="true"
															class="btn btn-primary btn-md pl-full-width btn-suchen-card pl-margin-top-10 <?php echo $class4 ?>" disabled>
														<i class="fas fa-info"></i>&nbsp;
														<?php echo $canBook ?>
													</button>
												<?php endif; ?>
											<?php endif; ?>
										</div>
									</div>
								</div>
                            <?php endforeach; ?>
                        </div>	
					</div>
				</div>
				<input type="hidden" name="for" value="<?php echo $_GET['for'] ?>" class="form-item form-control">
				<input type="hidden" name="step" value="3" class="form-item form-control">
			</form>
		<?php endif; ?>
		<?php if($_GET['step'] == 3 && $parklot->is_for == "betreiber"): ?>
			<form method="POST" action="<?php echo basename($_SERVER['REQUEST_URI']) . "?page=buchung-erstellen"; ?>">
				
				<?php 
							
				$dateTimeFrom = date('Y-m-d', strtotime($_GET['date_from']));
				$dateTimeTo = date('Y-m-d', strtotime($_GET['date_to']));
				$orders = Orders::getOrdersByProductId($_GET['pid'], $dateTimeFrom, $dateTimeTo);
				if (count($orders) >= $parklot->contigent) {
					echo 'Parkplatz nicht verfügbar.';
				}
				
				?>
				
				<div class="row ui-lotdata-block ui-lotdata-block-next">
					<h5 class="ui-lotdata-title">Buchungsdetails eingeben</h5>
					<div class="col-sm-12 col-md-12 ui-lotdata">
						<div class="row m10">							
							<div class="col-sm-12 col-md-2">
								<input type="hidden" name="date_from" value="<?php echo $_GET['date_from'] ?>" class="form-control">
								<label for="">Anreisedatum</label><br>
								<input type="text" value="<?php echo $_GET['date_from'] ?>" size="10" readonly>
							</div>
							<div class="col-sm-12 col-md-2">
								<input type="hidden" name="date_to" value="<?php echo $_GET['date_to'] ?>" class="form-control">
								<label for="">Abreisedatum</label><br>
								<input type="text" value="<?php echo $_GET['date_to'] ?>" size="10" readonly>
							</div>
							<div class="col-sm-12 col-md-4">
								<input type="hidden" name="product" value="<?php echo $parklot->product_id ?>" class="form-control">
								<label for="">Product</label><br>
								<input type="text" value="<?php echo $parklot->parklot ?>" size="30" readonly>
							</div>
						</div>
						<br>
						<div class="row m10">
							<div class="col-sm-12 col-md-1">
								<label for="">Uhrzeit hin</label><br>
								<input type="text" name="time_from" class="form-control timepicker" autocomplete="off" required>
							</div>
							<div class="col-sm-12 col-md-1">
								<label for="">Hinflugnummer</label><br>
								<input type="text" name="hinflugnummer" class="form-control">
							</div>
							<div class="col-sm-12 col-md-1">
								<label for="">Uhrzeit zurück</label><br>
								<input type="text" name="time_to" class="form-control timepicker" autocomplete="off" required>
							</div>
							<div class="col-sm-12 col-md-1">
								<label for="">Rückflugnummer</label><br>
								<input type="text" name="ruckflugnummer" class="form-control">
							</div>
							<?php if($parklot->type == 'shuttle'): ?>
							<div class="col-sm-12 col-md-1">
								<label for="">Personenanzahl</label><br>
								<input type="number" name="personenanzahl" min="1" max="8" value="1" class="form-control" required>
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
								<label for="">Anrede</label><br>
								<select name="anrede" class="form-control">
									<option value="Herr">Herr</option>
									<option value="Frau">Frau</option>
								</select>
							</div>
							<div class="col-sm-12 col-md-2">
								<label for="">Firmenname</label><br>
								<input type="text" name="firmenname" class="form-control">
							</div>
							<div class="col-sm-12 col-md-2">
								<label for="">Vorname</label><br>
								<input type="text" name="vorname" class="form-control" required>
							</div>
							<div class="col-sm-12 col-md-2">
								<label for="">Nachname</label><br>
								<input type="text" name="nachname" class="form-control" required>
							</div>							
							<div class="col-sm-12 col-md-2">
								<label for="">Telefonnummer</label><br>
								<input type="text" name="telefonnummer" class="form-control">
							</div>
							<div class="col-sm-12 col-md-2">
								<label for="">E-Mail</label><br>
								<input type="text" name="email" class="form-control" required>
							</div>
						</div>
						<div class="row m10">
							<div class="col-sm-12 col-md-3">
								<label for="">Anschrift</label><br>
								<input type="text" name="anschrift" class="form-control">
							</div>
							<div class="col-sm-12 col-md-1">
								<label for="">Postleitzahl</label><br>
								<input type="text" name="postleitzahl" class="form-control">
							</div>
							<div class="col-sm-12 col-md-2">
								<label for="">Ort</label><br>
								<input type="text" name="ort" class="form-control">
							</div>
							<div class="col-sm-12 col-md-2">
								<label for="">Kennzeichen</label><br>
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
								<label for="">Hersteller</label><br>
								<input type="text" name="model" class="form-control" required>
							</div>
							<div class="col-sm-12 col-md-2">
								<label for="">Typ</label><br>
								<input type="text" name="type" class="form-control">
							</div>
							<div class="col-sm-12 col-md-2">
								<label for="">Farbe</label><br>
								<input type="text" name="color" class="form-control" required>
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
							<div class="col-12 text-left">
								<div class="total-order-price">Gesamtbetrag:
								<span class="current-price">
									<?php echo to_float($priceList) ?>
								</span>
									<?php echo get_woocommerce_currency() ?>
								</div>
							</div>
							<br>
						</div>
						<?php if($parklot->is_for == "betreiber"): ?>
						<div class="row m10">
							<div class="col-sm-12 col-md-12 col-lg-12">
								<input type="radio" id="send_mail" name="send_mail" value="mail">
								<label for="send_mail">Buchungsbestätigung senden</label><br>
							</div>
						</div>
						<?php endif; ?>
						<br>
						<div class="row m10">
							<div class="col-sm-12 col-md-1">
								<button class="btn btn-primary">Speichern</button>
							</div>
							<div class="col-sm-12 col-md-1">                    
								<a href="<?php echo '/wp-admin/admin.php?page=buchung-erstellen&date_from='.$_GET['date_from'].'&date_to='.$_GET['date_to'].'&step=2&for=sv' ?>" class="btn btn-secondary" >Zurück</a>
							</div>
						</div>
					</div>
				</div>
				<input type="hidden" name="price" value="<?php echo to_float($priceList) ?>" class="form-control">
				<input type="hidden" name="step" value="4" class="form-control">
			</form>				
		<?php endif; ?>
		<?php if($_GET['step'] == 2 && $_GET['for'] == 'tf'): ?>			
			<form class="form-filter">
				<div class="row ui-lotdata-block ui-lotdata-block-next">
					<h5 class="ui-lotdata-title">Datum wählen</h5>
					<div class="col-sm-12 col-md-12 ui-lotdata">
						<div class="row m10">							
							<?php if($_GET['date_from'] != null): ?>
							<div class="col-sm-12 col-md-1 ui-lotdata-date">
								<label for="">Hin-Transfer</label><br>
								<input type="text" name="date_from" class="form-control"							
									   data-language="de" autocomplete="off" value="<?php echo $_GET['date_from'] ?>"
									   readonly>
							</div>
							<?php endif; ?>
							<?php if($_GET['date_to'] != null): ?>
							<div class="col-sm-12 col-md-1 ui-lotdata-date">
								<label for="">Rück-Transfer</label><br>
								<input type="text" name="date_to" class="form-control"							
									   data-language="de" autocomplete="off" value="<?php echo $_GET['date_to'] ?>"
									   readonly>
							</div>
							<?php endif; ?>
							<div class="col-sm-12 col-md-3">
								<label for="">Product</label><br>
								<select name="pid" class="form-item form-control">
									<?php foreach ($products as $product) : ?>
										<option value="<?php echo $product->product_id ?>" <?php echo $product->product_id == $_GET['pid'] ? 'selected' : '' ?>>
											<?php echo $product->parklot ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<br>
						<div class="row m10">
							<div class="col-sm-12 col-md-1">
								<button type="submit" class="btn btn-primary" class="form-item form-control">Weiter</button>
							</div>
							<div class="col-sm-12 col-md-1">                    
								<a href="<?php echo '/wp-admin/admin.php?page=buchung-erstellen' ?>" class="btn btn-secondary" >Zurück</a>
							</div>
						</div>
					</div>
				</div>
				<input type="hidden" name="step" value="3" class="form-item form-control">
			</form>
		<?php endif; ?>
		<?php if($_GET['step'] == 3 && $parklot->is_for == "hotel"): ?>	
		<?php
		
		if($_GET['date_from'] == null && $_GET['date_to'] == null)
			header('Location: /wp-admin/admin.php?page=buchung-erstellen&pid='.$_GET['pid'].'&step=2');
		$p_id = $db->getParklotByProductId($_GET['pid']);
		$product_id = HotelTransfers::getHotelProdukt($p_id->user_id);
		
		if($product_id){
			$hotelProduct = wc_get_product($product_id->product_id);
			$variations = $hotelProduct->get_children();
		}
		?>
		<div class="row ui-lotdata-block ui-lotdata-block-next">
			<h5 class="ui-lotdata-title">Buchung erstellen</h5>
			<div class="col-sm-12 col-md-12 ui-lotdata">
				<div class="row m10">					
					<?php if($_GET['date_from'] != null): ?>
					<div class="col-sm-12 col-md-1 ui-lotdata-date">
						<label for="">Hin-Transfer</label><br>
						<input type="text" name="date_from" class="form-control"							
							   data-language="de" autocomplete="off" value="<?php echo $_GET['date_from'] ?>"
							   readonly>
					</div>
					<?php endif; ?>
					<?php if($_GET['date_to'] != null): ?>
					<div class="col-sm-12 col-md-1 ui-lotdata-date">
						<label for="">Rück-Transfer</label><br>
						<input type="text" name="date_to" class="form-control"							
							   data-language="de" autocomplete="off" value="<?php echo $_GET['date_to'] ?>"
							   readonly>
					</div>
					<?php endif; ?>
					<div class="col-sm-12 col-md-4">
						<label for="">Product</label><br>
						<input type="text" value="<?php echo $parklot->parklot ?>" size="30" readonly>
					</div>
				</div>
			</div>
		</div>
		<form  action="/wp-content/plugins/itweb-booking/classes/Helper.php" method="POST">
			<input type="hidden" name="task" value="new_hotel_transfer_backend">
			<?php if($_GET['date_from'] != null && $_GET['date_to'] != null): ?>
				<input type="hidden" name="type" value="all">
			<?php elseif($_GET['date_from'] != null && $_GET['date_to'] == null): ?>
				<input type="hidden" name="type" value="hintransfer">
			<?php elseif($_GET['date_from'] == null && $_GET['date_to'] != null): ?>
				<input type="hidden" name="type" value="rucktransfer">
			<?php endif; ?>
			<input type="hidden" name="datefrom" value="<?php echo $_GET['date_from'] ?>">
			<input type="hidden" name="dateto" value="<?php echo $_GET['date_to'] ?>">
			<input type="hidden" name="pID" value="<?php echo $p_id->user_id ?>">
			
			<div class="row ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Buchungsdetails</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row m10">
						<div class="col-sm-12 col-md-2">
							<label for="">Vorname</label><br>
							<input type="text" class="form-control mb-2" name="vorname" placeholder="" required>
						</div>
						<div class="col-sm-12 col-md-2">
							<label for="">Nachname</label><br>
							<input type="text" class="form-control mb-2" name="nachname" placeholder="" required>
						</div>
						<div class="col-sm-12 col-md-2">
							<label for="">Telefon</label><br>
							<input type="tel" class="form-control mb-2" name="phone" placeholder="">
						</div>
						<div class="col-sm-12 col-md-3">
							<label for="">E-Mail</label><br>
							<input type="email" class="form-control mb-2" name="email" placeholder="">
						</div>
					</div>
					<div class="row m10">
						<div class="col-sm-12 col-md-2">
							<label for="">Anzahl Personen</label><br>
							<select name="product" class="form-control mb-2" required>
								<?php foreach ($variations as $variation) : ?>
									<?php
									$product_variation = new WC_Product_Variation($variation);
									$name = explode(' - ', $product_variation->get_name());
									?>
									<option value="<?php echo $product_variation->get_id() ?>">
										<?php echo end($name) ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						<?php if($_GET['date_from'] != null): ?>
						<div class="col-sm-12 col-md-2">
							<label for="">Transfer vom Hotel/Parkplatz</label><br>
							<input type="text" class="form-control mb-2 timepicker" name="transfer_vom_hotel" placeholder="" required>
						</div>
						<?php endif; ?>
						<?php if($_GET['date_to'] != null): ?>
						<div class="col-sm-12 col-md-2">
							<label for="">Ankunftszeit Rückflug</label><br>
							<input type="text" class="form-control mb-2 timepicker" name="ankunftszeit_ruckflug" placeholder="" required>
						</div>
						<?php endif; ?>
						
						<?php if($_GET['date_from'] != null): ?>
						<div class="col-sm-12 col-md-2">
							<label for="">Flugnummer Hinflug</label><br>
							<input type="text" class="form-control mb-2" name="hinflugnummer" placeholder="">
						</div>
						<?php endif; ?>
						<?php if($_GET['date_to'] != null): ?>
						<div class="col-sm-12 col-md-2">
							<label for="">Flugnummer Rückflug</label><br>
							<input type="text" class="form-control mb-2" name="ruckflugnummer" placeholder="">
						</div>
						<?php endif; ?>
					</div>
					<div class="row m10">
						<div class="col-sm-12 col-md-4">
							<label for="">Sonstiges: z. B. Kindersitz</label><br>
							<input type="text" class="form-control mb-2" name="sonstiges" placeholder="">
						</div>
					</div>
					<br>
					<div class="row m10">
						<div class="col-sm-12 col-md-2">
							<button type="submit" name="step4" value="1" class="btn btn-primary">Transfer buchen</button>
						</div>
						<div class="col-sm-12 col-md-1">                    
							<a href="<?php echo '/wp-admin/admin.php?page=buchung-erstellen&date_from='.$_GET['date_from'].'&date_to='.$_GET['date_to'].'&step=2&for=tf' ?>" class="btn btn-secondary" >Zurück</a>
						</div>
					</div>
				</div>
			</div>
		</form>
		<?php endif; ?>
	</div>
</div>
<?php session_unset(); ?>