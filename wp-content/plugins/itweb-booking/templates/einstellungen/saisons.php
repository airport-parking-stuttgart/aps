<?php
global $wpdb;
$clients = Database::getInstance()->getAllClients();
$sql = "select * from " . $wpdb->prefix . "itweb_prices where year = " . date('Y');
$prices_all = $wpdb->get_results($sql);

foreach($clients as $client){
	$client_products = Database::getInstance()->getClientProducts($client->id);
	foreach($client_products as $product){
		$parklot = Database::getInstance()->getParklotByProductId($product->product_id);
		if($parklot->deleted != 0) continue;
			$products[$parklot->product_id] = $parklot->product_id;
	}
}


if (isok($_POST, 'name')) {
	
	// update order cancellation
	if (isset($_POST['name']) && $_POST['name'] != "") {
		for ($i = 0; $i < count($_POST['name']); $i++) {
			if (empty($_POST['name'][$i])) {
				continue;
			}
			$date_from = date('Y-m-d', strtotime($_POST['date_from'][$i]));
			$date_to = date('Y-m-d', strtotime($_POST['date_to'][$i]));
			if (isset($_POST['saison_id'][$i]) && !empty($_POST['saison_id'][$i])) {
				Database::getInstance()->updateSaisons($_POST['saison_id'][$i], $_POST['name'][$i], $date_from, $date_to);
			} else {				
				Database::getInstance()->saveSaisons($_POST['name'][$i], $date_from, $date_to);
			}
		}
	}
	
	//foreach($_POST['saison_id'] as $saison){
		//foreach($products as $product){
			foreach($_POST as $key => $val){
				if(str_contains($key, ':staffel')){
					$pieces = explode(":", $key);
					$i = explode("_", $pieces[0]);
					$s = explode("_", $pieces[3]);
					$data['saison_id'] = $s[1];
					
					$pl = explode("_", $pieces[2]);
					$data['product_id'] = $pl[1];
					
					if($pieces[1] == 'per')
						$data['typ'] = 'prozent';
					else
						$data['typ'] = 'kontingent';
					
					$data['price_id'] = $_POST['ps_'.$s[1].'_pl_'.$pl[1].'_'.$pieces[5]][0];
					$data['wert'] = $val[0];
					
					if(in_array($pl[1], $_POST['product_'.$s[1]]))
						$data['aktiv'] = '1';
					else
						$data['aktiv'] = '0';
					
					if($i[1] == ""){
						Database::getInstance()->savePreisstaffel($data);						
					}
						
					else{
						$data['id'] = $i[1];
						Database::getInstance()->updatePreisstaffel($data);
					}
				}				
			}
		//}
	//}
	header('Location: ' . $_SERVER['HTTP_REFERER']);
}

$saisons = Database::getInstance()->getSaisons();
//echo "<pre>"; print_r($data); echo "</pre>";

?>
<style>
.preis_table td{border: 1px solid black; padding:10px;}
.border_right {border-right: 4px solid black !important}
hr {border: 2px solid #1e73be;}
.card{
	background: linear-gradient(170deg, rgba(255,255,255,1) 0%, rgba(240,246,251,1) 100%);
	border: 1px solid #98c6ee;
	padding: 10px;
	box-shadow: 3px 3px 5px #98c6ee;
}
</style>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Saisons Einstellungen</h3>
    </div>
	<div class="page-body">
		<form action="#" method="POST">
			<div class="row ui-lotdata-block ui-lotdata-block-next saison-wrapper">
				<h5 class="ui-lotdata-title">Saisons</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">					
					<?php foreach ($saisons as $saison) : ?>
						<div class="col-12 card row-item saison-item">
							<div class="row">
								<div class="col-sm-12 col-md-2">
									<input type="hidden" name="saison_id[]" value="<?php echo $saison->id ?>">
									<label for="">Name</label>
									<input type="text" name="name[]" placeholder="" class="w100" value="<?php echo $saison->name ?>">
								</div>
								<div class="col-sm-12 col-md-2 ui-lotdata-date">
									<label for="">Von</label><br>
									<input type="text" name="date_from[]" size="8" class="air-datepicker" autocomplete="off" data-language="de" value="<?php echo $saison->date_from ?>">
								</div>
								<div class="col-sm-12 col-md-2 ui-lotdata-date">
									<label for="">Bis</label><br>
									<input type="text" name="date_to[]" size="8" class="air-datepicker" autocomplete="off" data-language="de" value="<?php echo $saison->date_to ?>">
								</div>
								<div class="col-2 add_del_buttons">
									<span class="btn btn-danger del-table-row"
										  data-table="saisons" data-id="<?php echo $saison->id ?>">x</span>
									<span class="btn btn-secondary plus-icon add-saison-template">+</span>
								</div>
							</div>
							<style>
							.preis_table td{border: 1px solid black; padding:10px;}
							.border_right {border-right: 2px solid black !important}
							</style>
							<div class="row">
								<div class="col-sm-12 col-md-12">
								<details>
								<summary class="">Produkte</summary>
								<br>
								<div class="row">
									<div class="col-sm-12 col-md-12">
										<h5>Produkte zuweisen</h5>
										<?php foreach($clients as $client): ?>
											<?php $client_products = Database::getInstance()->getClientProducts($client->id); ?>
											<?php foreach($client_products as $product): ?>												
												<?php $parklot = Database::getInstance()->getParklotByProductId($product->product_id); ?>
												<?php if($parklot->deleted != 0) continue; ?>
													<?php $ps_product = Database::getInstance()->getPreisstaffelProducts($saison->id, $product->product_id); ?>
													<?php $ps_active_product[$saison->id] = Database::getInstance()->getPreisstaffelActiveProducts($saison->id, $product->product_id); ?>
													<?php $productIds = array_column($ps_active_product[$saison->id], 'product_id'); ?>
													<hr>
													<input type="checkbox" name="product_<?php echo $saison->id ?>[]" value="<?php echo $parklot->product_id ?>"
													<?php echo in_array($product->product_id, $productIds) ? "checked" : "" ?>><?php echo $parklot->parklot ?> (<?php echo $parklot->parklot_short ?>)<br>
													<?php 
													$group_id = $parklot->group_id; 
													$sql = "select SUM(contigent) AS contingent from " . $wpdb->prefix . "itweb_parklots where group_id = " . $group_id . " AND is_for != 'hotel' GROUP BY group_id";
													$contingent = $wpdb->get_row($sql);
													$data = Database::getInstance()->getPreisstaffelProducts($saison->id, $product->product_id);
													
													/// Abfrage PS Start
													$sql = "select * from " . $wpdb->prefix . "itweb_events e JOIN " . $wpdb->prefix . "itweb_prices p ON p.id = e.price_id
													WHERE DATE(e.datefrom) BETWEEN '".$saison->date_from."' AND '".$saison->date_to."' AND e.product_id = ".$parklot->product_id." ORDER BY p.id ASC LIMIT 1;";
													$first_prices = $wpdb->get_results($sql);													
													
													
													
													/// WENN PS Start nicht gefunden, PS A
													if($first_prices[0]->id == null){
														$sql = "select * from " . $wpdb->prefix . "itweb_prices WHERE year >= '2024' ORDER BY id ASC LIMIT 1";
														$first_prices = $wpdb->get_results($sql);														
													}
													
													if($first_prices[0]->id != null){
														$sql = "select * from " . $wpdb->prefix . "itweb_prices WHERE id > " . $first_prices[0]->id . " ORDER BY id ASC LIMIT 1";
														$next_prices1 = $wpdb->get_results($sql);
														
														if($next_prices1[0]->id == null){
															$sql = "select * from " . $wpdb->prefix . "itweb_prices WHERE year >= '2024' ORDER BY id ASC LIMIT 1";
															$next_prices1 = $wpdb->get_results($sql);
														}
													}
													
													if($next_prices1[0]->id != null){
														$sql = "select * from " . $wpdb->prefix . "itweb_prices WHERE id > " . $next_prices1[0]->id . " ORDER BY id ASC LIMIT 1";
														$next_prices2 = $wpdb->get_results($sql);
													
														if($next_prices2[0]->id == null){
															$sql = "select * from " . $wpdb->prefix . "itweb_prices WHERE year >= '2024' ORDER BY id ASC LIMIT 1";
															$next_prices2 = $wpdb->get_results($sql);
														}
													}
													
													if($next_prices2[0]->id != null){
														$sql = "select * from " . $wpdb->prefix . "itweb_prices WHERE id > " . $next_prices2[0]->id . " ORDER BY id ASC LIMIT 1";
														$next_prices3 = $wpdb->get_results($sql);
														
														if($next_prices3[0]->id == null){
															$sql = "select * from " . $wpdb->prefix . "itweb_prices WHERE year >= '2024' ORDER BY id ASC LIMIT 1";
															$next_prices3 = $wpdb->get_results($sql);
														}
													}
													
													if($next_prices3[0]->id != null){
														$sql = "select * from " . $wpdb->prefix . "itweb_prices WHERE id > " . $next_prices3[0]->id . " ORDER BY id ASC LIMIT 1";
														$next_prices4 = $wpdb->get_results($sql);
														
														if($next_prices4[0]->id == null){
															$sql = "select * from " . $wpdb->prefix . "itweb_prices WHERE year >= '2024' ORDER BY id ASC LIMIT 1";
															$next_prices4 = $wpdb->get_results($sql);
														}
													}
													
													if($next_prices4[0]->id != null){
														$sql = "select * from " . $wpdb->prefix . "itweb_prices WHERE id > " . $next_prices4[0]->id . " ORDER BY id ASC LIMIT 1";
														$next_prices5 = $wpdb->get_results($sql);
														
														if($next_prices5[0]->id == null){
															$sql = "select * from " . $wpdb->prefix . "itweb_prices WHERE year >= '2024' ORDER BY id ASC LIMIT 1";
															$next_prices5 = $wpdb->get_results($sql);
														}
													}

													?>
													
													<table class="preis_table" style="white-space: nowrap;">
														<tbody>
															<tr>
																<td class="border_right"><?php echo $parklot->parklot_short ?></td>
																<td><select name="ps_<?php echo $saison->id ?>_pl_<?php echo $product->product_id ?>_1[]">
																	<?php foreach($prices_all as $price): ?>																	
																	<?php //if($price->id < $prices_added[0]->price_id) continue; ?>	
																		<?php if($data[0]->price_id != null && $data[0]->price_id != "" && $data[0]->price_id != 0): ?>	
																			<option value="<?php echo $price->id ?>" <?php echo $data[0]->price_id == $price->id ? "selected" : "" ?>><?php echo $price->name ?></option>
																		<?php else: ?>
																			<option value="<?php echo $price->id ?>" <?php echo $first_prices[0]->price_id == $price->id ? "selected" : "" ?>><?php echo $price->name ?></option>
																		<?php endif; ?>
																	<?php endforeach; ?>
																</select></td>																
																<td class="border_right"></td>
																<td><select name="ps_<?php echo $saison->id ?>_pl_<?php echo $product->product_id ?>_2[]">																	
																	<?php foreach($prices_all as $price): ?>																	
																		<?php if($data[2]->price_id != null && $data[2]->price_id != "" && $data[2]->price_id != 0): ?>	
																			<option value="<?php echo $price->id ?>" <?php echo $data[2]->price_id == $price->id ? "selected" : "" ?>><?php echo $price->name ?></option>
																		<?php elseif($next_prices1[0]->id != null): ?>
																			<option value="<?php echo $price->id ?>" <?php echo $next_prices1[0]->id == $price->id ? "selected" : "" ?>><?php echo $price->name ?></option>
																		<?php endif; ?>
																	<?php endforeach; ?>
																</select></td>																
																<td class="border_right"></td>
																<td><select name="ps_<?php echo $saison->id ?>_pl_<?php echo $product->product_id ?>_3[]">
																	<?php foreach($prices_all as $price): ?>																	
																		<?php if($data[4]->price_id != null && $data[4]->price_id != "" && $data[4]->price_id != 0): ?>
																			<option value="<?php echo $price->id ?>" <?php echo $data[4]->price_id == $price->id ? "selected" : "" ?>><?php echo $price->name ?></option>
																		<?php elseif($next_prices2[0]->id != null): ?>
																			<option value="<?php echo $price->id ?>" <?php echo $next_prices2[0]->id == $price->id ? "selected" : "" ?>><?php echo $price->name ?></option>
																		<?php endif; ?>
																	<?php endforeach; ?>
																</select></td>																
																<td class="border_right"></td>
																<td><select name="ps_<?php echo $saison->id ?>_pl_<?php echo $product->product_id ?>_4[]">
																	<?php foreach($prices_all as $price): ?>																	
																		<?php if($data[6]->price_id != null && $data[6]->price_id != "" && $data[6]->price_id != 0): ?>
																			<option value="<?php echo $price->id ?>" <?php echo $data[6]->price_id == $price->id ? "selected" : "" ?>><?php echo $price->name ?></option>
																		<?php elseif($next_prices3[0]->id != null): ?>
																			<option value="<?php echo $price->id ?>" <?php echo $next_prices3[0]->id == $price->id ? "selected" : "" ?>><?php echo $price->name ?></option>
																		<?php endif; ?>
																	<?php endforeach; ?>
																</select></td>																
																<td class="border_right"></td>
																<td><select name="ps_<?php echo $saison->id ?>_pl_<?php echo $product->product_id ?>_5[]">
																	<?php foreach($prices_all as $price): ?>																	
																		<?php if($data[8]->price_id != null && $data[8]->price_id != "" && $data[8]->price_id != 0): ?>
																			<option value="<?php echo $price->id ?>" <?php echo $data[8]->price_id == $price->id ? "selected" : "" ?>><?php echo $price->name ?></option>
																		<?php elseif($next_prices4[0]->id != null): ?>
																			<option value="<?php echo $price->id ?>" <?php echo $next_prices4[0]->id == $price->id ? "selected" : "" ?>><?php echo $price->name ?></option>
																		<?php endif; ?>
																	<?php endforeach; ?>
																</select></td>																
																<td class="border_right"></td>
																<td><select name="ps_<?php echo $saison->id ?>_pl_<?php echo $product->product_id ?>_6[]">
																	<?php foreach($prices_all as $price): ?>																	
																		<?php if($data[10]->price_id != null && $data[10]->price_id != "" && $data[10]->price_id != 0): ?>
																			<option value="<?php echo $price->id ?>" <?php echo $data[10]->price_id == $price->id ? "selected" : "" ?>><?php echo $price->name ?></option>
																		<?php elseif($next_prices5[0]->id != null): ?>
																			<option value="<?php echo $price->id ?>" <?php echo $next_prices5[0]->id == $price->id ? "selected" : "" ?>><?php echo $price->name ?></option>
																		<?php endif; ?>
																	<?php endforeach; ?>
																</select></td>																
																<td class="border_right"></td>
															</tr>
															<tr>
																<td class="border_right"><?php echo $contingent->contingent ?></td>
																
																<td><input type="number" name="id_<?php echo $data[0]->id ?>:con:lotid_<?php echo $parklot->product_id ?>:saisonid_<?php echo $saison->id ?>:staffel:1[]" size="6" min="0" max="<?php echo $contingent->contingent ?>" value="<?php echo $data[0]->typ == 'kontingent' && $data[0]->wert != 0 ? $data[0]->wert : ""?>"></td>
																<td class="border_right"><input type="number" name="id_<?php echo $data[1]->id ?>:per:lotid_<?php echo $parklot->product_id ?>:saisonid_<?php echo $saison->id ?>:staffel:1[]" size="5" min="0" max="100" value="<?php echo $data[1]->typ == 'prozent' && $data[1]->wert != 0 ? $data[1]->wert : ""?>">%</td>
																
																<td><input type="number" name="id_<?php echo $data[2]->id ?>:con:lotid_<?php echo $parklot->product_id ?>:saisonid_<?php echo $saison->id ?>:staffel:2[]" size="6" min="0" max="<?php echo $contingent->contingent ?>" value="<?php echo $data[2]->typ == 'kontingent' && $data[2]->wert != 0 ? $data[2]->wert : ""?>"></td>
																<td class="border_right"><input type="number" name="id_<?php echo $data[3]->id ?>:per:lotid_<?php echo $parklot->product_id ?>:saisonid_<?php echo $saison->id ?>:staffel:2[]" size="5" min="0" max="100" value="<?php echo $data[3]->typ == 'prozent' && $data[3]->wert != 0 ? $data[3]->wert : ""?>">%</td>
															
																<td><input type="number" name="id_<?php echo $data[4]->id ?>:con:lotid_<?php echo $parklot->product_id ?>:saisonid_<?php echo $saison->id ?>:staffel:3[]" size="6" min="0" max="<?php echo $contingent->contingent ?>" value="<?php echo $data[4]->typ == 'kontingent' && $data[4]->wert != 0 ? $data[4]->wert : ""?>"></td>
																<td class="border_right"><input type="number" name="id_<?php echo $data[5]->id ?>:per:lotid_<?php echo $parklot->product_id ?>:saisonid_<?php echo $saison->id ?>:staffel:3[]" size="5" min="0" max="100" value="<?php echo $data[5]->typ == 'prozent' && $data[5]->wert != 0 ? $data[5]->wert : ""?>">%</td>
																
																<td><input type="number" name="id_<?php echo $data[6]->id ?>:con:lotid_<?php echo $parklot->product_id ?>:saisonid_<?php echo $saison->id ?>:staffel:4[]" size="6" min="0" max="<?php echo $contingent->contingent ?>" value="<?php echo $data[6]->typ == 'kontingent' && $data[6]->wert != 0 ? $data[6]->wert : ""?>"></td>
																<td class="border_right"><input type="number" name="id_<?php echo $data[7]->id ?>:per:lotid_<?php echo $parklot->product_id ?>:saisonid_<?php echo $saison->id ?>:staffel:4[]" size="5" min="0" max="100" value="<?php echo $data[7]->typ == 'prozent' && $data[7]->wert != 0 ? $data[7]->wert : ""?>">%</td>
																
																<td><input type="number" name="id_<?php echo $data[8]->id ?>:con:lotid_<?php echo $parklot->product_id ?>:saisonid_<?php echo $saison->id ?>:staffel:5[]" size="6" min="0" max="<?php echo $contingent->contingent ?>" value="<?php echo $data[8]->typ == 'kontingent' && $data[8]->wert != 0 ? $data[8]->wert : ""?>"></td>
																<td class="border_right"><input type="number" name="id_<?php echo $data[9]->id ?>:per:lotid_<?php echo $parklot->product_id ?>:saisonid_<?php echo $saison->id ?>:staffel:5[]" size="5" min="0" max="100" value="<?php echo $data[9]->typ == 'prozent' && $data[9]->wert != 0 ? $data[9]->wert : ""?>">%</td>
															
																<td><input type="number" name="id_<?php echo $data[10]->id ?>:con:lotid_<?php echo $parklot->product_id ?>:saisonid_<?php echo $saison->id ?>:staffel:6[]" size="6" min="0" max="<?php echo $contingent->contingent ?>" value="<?php echo $data[10]->typ == 'kontingent' && $data[10]->wert != 0 ? $data[10]->wert : ""?>"></td>
																<td class="border_right"><input type="number" name="id_<?php echo $data[11]->id ?>:per:lotid_<?php echo $parklot->product_id ?>:saisonid_<?php echo $saison->id ?>:staffel:6[]" size="5" min="0" max="100" value="<?php echo $data[11]->typ == 'prozent' && $data[11]->wert != 0 ? $data[11]->wert : ""?>">%</td>
															
															</tr>
															<tr>
																<td class="border_right"></td>
																<?php $lastVal = 0; ?>											
																<?php 
																	if($data[0]->wert != null && $data[0]->wert != 0){
																		$wert = $data[0]->wert;																		
																	}								
																	elseif($data[1]->wert != null && $data[1]->wert != 0){
																		$wert = $contingent->contingent / 100 * $data[1]->wert;
																	}
																	else
																		$wert = "";
																?>
																<td style="color: red;"><?php echo $wert != 0 ? number_format($wert - $lastVal, 0, ".", ".") : "" ?></td>										
																<td class="border_right"><?php echo $wert != 0 ? number_format($wert, 0, ".", ".") : "" ?></td>
																
																<?php $lastVal = $wert; ?>
																<?php 
																	if($data[2]->wert != null && $data[2]->wert != 0){
																		$wert = $data[2]->wert;																		
																	}								
																	elseif($data[3]->wert != null && $data[3]->wert != 0){
																		$wert = $contingent->contingent / 100 * $data[3]->wert;
																	}
																	else
																		$wert = "";
																?>
																<td style="color: red;"><?php echo $wert != 0 ? number_format($wert - $lastVal, 0, ".", ".") : "" ?></td>	
																<td class="border_right"><?php echo $wert != 0 ? number_format($wert, 0, ".", ".") : "" ?></td>
																<?php $lastVal = $wert; ?>
																
																<?php $lastVal = $wert; ?>
																<?php 
																	if($data[4]->wert != null && $data[4]->wert != 0){
																		$wert = $data[4]->wert;																		
																	}								
																	elseif($data[5]->wert != null && $data[5]->wert != 0){
																		$wert = $contingent->contingent / 100 * $data[5]->wert;
																	}
																	else
																		$wert = "";
																?>
																<td style="color: red;"><?php echo $wert != 0 ? number_format($wert - $lastVal, 0, ".", ".") : "" ?></td>	
																<td class="border_right"><?php echo $wert != 0 ? number_format($wert, 0, ".", ".") : "" ?></td>
																<?php $lastVal = $wert; ?>
																
																<?php $lastVal = $wert; ?>
																<?php 
																	if($data[6]->wert != null && $data[6]->wert != 0){
																		$wert = $data[6]->wert;																		
																	}								
																	elseif($data[7]->wert != null && $data[7]->wert != 0){
																		$wert = $contingent->contingent / 100 * $data[7]->wert;
																	}
																	else
																		$wert = "";
																?>
																<td style="color: red;"><?php echo $wert != 0 ? number_format($wert - $lastVal, 0, ".", ".") : "" ?></td>	
																<td class="border_right"><?php echo $wert != 0 ? number_format($wert, 0, ".", ".") : "" ?></td>
																<?php $lastVal = $wert; ?>
																
																<?php $lastVal = $wert; ?>
																<?php 
																	if($data[8]->wert != null && $data[8]->wert != 0){
																		$wert = $data[8]->wert;																		
																	}								
																	elseif($data[9]->wert != null && $data[9]->wert != 0){
																		$wert = $contingent->contingent / 100 * $data[9]->wert;
																	}
																	else
																		$wert = "";
																?>
																<td style="color: red;"><?php echo $wert != 0 ? number_format($wert - $lastVal, 0, ".", ".") : "" ?></td>	
																<td class="border_right"><?php echo $wert != 0 ? number_format($wert, 0, ".", ".") : "" ?></td>
																<?php $lastVal = $wert; ?>
																
																<?php $lastVal = $wert; ?>
																<?php 
																	if($data[10]->wert != null && $data[10]->wert != 0){
																		$wert = $data[10]->wert;																		
																	}								
																	elseif($data[11]->wert != null && $data[11]->wert != 0){
																		$wert = $contingent->contingent / 100 * $data[11]->wert;
																	}
																	else
																		$wert = "";
																?>
																<td style="color: red;"><?php echo $wert != 0 ? number_format($wert - $lastVal, 0, ".", ".") : "" ?></td>	
																<td class="border_right"><?php echo $wert != 0 ? number_format($wert, 0, ".", ".") : "" ?></td>
																<?php $lastVal = $wert; ?>
															</tr>
														</tbody>
													</table>													
											<?php endforeach; ?>											
										<?php endforeach; ?>
									</div>
								</div>									
								</details>
								</div>
							</div>
						</div>
					 <?php endforeach; ?>
					
					<div class="col-12 row-item saison-item">
						<div class="row">
							<div class="col-sm-12 col-md-2">
								<input type="hidden" name="saison_id[]">
								<label for="">Name</label>
								<input type="text" name="name[]" placeholder="" class="w100">
							</div>
							<div class="col-sm-12 col-md-2 ui-lotdata-date">
									<label for="">Von</label><br>
									<input type="text" name="date_from[]" size="8" class="air-datepicker" autocomplete="off" data-language="de" value="">
								</div>
								<div class="col-sm-12 co2-md-1 ui-lotdata-date">
									<label for="">Bis</label><br>
									<input type="text" name="date_to[]" size="8" class="air-datepicker" autocomplete="off" data-language="de" value="">
								</div>
							<div class="col-2 add_del_buttons">
								<span class="btn btn-danger del-table-row"
									  data-table="saisons">x</span>
								<span class="btn btn-secondary plus-icon add-saison-template">+</span>
							</div>
						</div>
					</div>
				</div>				
			</div>
			<div class="row m10">
				<div class="col-1">
					<button class="btn btn-primary">Speichern</button>
				</div>
				<div class="col-1">
					<a href="<?php echo '/wp-admin/admin.php?saisons' ?>" class="btn btn-secondary" >Zurück</a>
				</div>
			</div>
		</form>
    </div>
</div>