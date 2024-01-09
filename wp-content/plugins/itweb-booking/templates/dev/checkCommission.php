<?php 
$products = Database::getInstance()->getBrokerLotsById(5);
$disabled = "disabled";

if(isset($_POST["date"])){
	$date = (explode(" - ",$_POST["date"]));
	$date[0] = date('Y-m-d', strtotime($date[0]));
	$date[1] = date('Y-m-d', strtotime($date[1]));
}
else{
	$date1 = date("Y-m-d");
	$date2 = date("Y-m-d", strtotime($date1 . ' +30 day'));
}

if($_POST['show'] == true || $_POST['update'] == true){
	if($_POST['product'] == '5')
		$product = "AND (o.product_id = 621 || o.product_id = 624 || o.product_id = 683 || o.product_id = 901 || o.product_id = 24261 || o.product_id = 24263 || o.product_id = 24609 || o.product_id = 28878)";
	if($_POST['product'] == '6')
		$product = "AND (o.product_id = 45856 || o.product_id = 41402 || o.product_id = 41403)";
	else
		$product = "AND o.product_id = ".$_POST['product'];

	

	global $wpdb;
	$provisions = $wpdb->get_results("
	SELECT o.order_id, o.code, ROUND(om.meta_value, 2) AS com_db, ROUND(o.b_total + o.m_v_total, 2) AS betrag, pl.commision, pl.commision_ws FROM {$wpdb->prefix}itweb_orders o 
	LEFT JOIN {$wpdb->prefix}itweb_orders_meta om ON om.order_id = o.order_id AND om.meta_key = 'provision'
	LEFT JOIN {$wpdb->prefix}posts p ON p.ID = o.order_id
	LEFT JOIN {$wpdb->prefix}itweb_parklots pl ON pl.product_id = o.product_id
	WHERE p.post_status = 'wc-processing' ".$product." AND DATE(o.date_from) BETWEEN '". $date[0]."' AND '". $date[1]."'"
	);
	
	foreach($provisions as $p){

		if($p->code == 'STR2' || $p->code == 'STR0' || $p->code == 'STRW' || $p->code == 'ST12' || $p->code == 'ST15' || $p->code == 'STRI')
			$prozent = $_POST['prozent_ws'];
		else
			$prozent = $_POST['prozent'];
		
		$berechnet = number_format((($p->betrag / 119 * 100) / 100 * $prozent), 2, ".", ".");
		if($p->com_db != $berechnet){
			$disabled = "";
			echo $p->order_id . " Betrag: ". $p->betrag . " DB: " . $p->com_db . " Berechnung: " .$berechnet . " %: " . $prozent . "<br>";
			if($_POST['update'] == true){
				Database::getInstance()->updateBookingMeta($p->order_id, 'provision', $berechnet);
			}
		}
	}
	
	/*
	foreach($provisions as $k){
		if($k->cp != $k->p){
			$disabled = "";
			echo $k->order_id . " " . get_post_meta($k->order_id, 'token')[0] . ", cp: " . $k->cp . ", p: ". $k->p . "<br>";
			
			if($_POST['update'] == true)
				if($k->p == null)
					Database::getInstance()->saveBookingMeta($k->order_id, 'provision', $k->cp);
				else
					Database::getInstance()->updateBookingMeta($k->order_id, 'provision', $k->cp);
			
			//if($k->p == '0.00' && $k->cp != '0.00'){
				//Database::getInstance()->updateBookingMeta($k->order_id, 'provision', $k->cp);
			//}
			//if($k->cp == '0.00' && $k->p != '0.00'){
				
				$netto = number_format((float)$k->p / 20 *100,4, '.', '');
				
				$woo_order = wc_get_order($k->order_id);
				$order_items = $woo_order->get_items();
				foreach ( $order_items as $key => $value ) {
					$order_item_id = $key;
					   $product_value = $value->get_data();
					   $product_id    = $product_value['product_id']; 
				}
				$product = wc_get_product( $product_id );
				
				$order_items[ $order_item_id ]->set_total( $netto );
				$woo_order->calculate_taxes();
				$woo_order->calculate_totals();
				$woo_order->save();
				
			//}
		}
	}
	*/
}

//echo "<pre>"; print_r($_POST); echo "</pre>";
?>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-body">
		<form class="update-price" action="<?php echo basename($_SERVER['REQUEST_URI']); ?>" method="POST">
			<div class="row m60">
				<div class="col-sm-12 col-md-3 col-lg-2">
					<select name="product" class="form-item form-control">
						<option value="5">HEX</option>
						<option value="6">Parkos</option>
						<?php foreach($products as $product) : ?>
						<?php if($product->product_id == '537' || $product->product_id == '592' || $product->product_id == '619' || 
								$product->product_id == '873' || $product->product_id == '3851' || $product->product_id == '8782' || 
								$product->product_id == '6762' || $product->product_id == '6772')
									continue;
						?>
							<option value="<?php echo $product->product_id ?>"
								<?php echo (isset($_POST['product']) && $_POST['product'] == $product->product_id) ? ' selected' : '' ?>>
								<?php echo $product->parklot ?>
							</option>
						<?php endforeach; ?>
					</select>					
				</div>
				<div class="col-sm-12 col-md-2">
					<input type="text" class="datepicker-range form-item form-control" name="date" data-multiple-dates-separator=" - " placeholder="" value="<?php echo $_POST["date"] ? $_POST["date"] : date('d.m.Y', strtotime($date1)) . " - " . date('d.m.Y', strtotime($date2)) ?>">
				</div>
				<div class="col-sm-12 col-md-2">
					<input type="text" class="form-item form-control" name="prozent" placeholder="Regulät" value="<?php echo $_POST["prozent"] ? $_POST["prozent"] : "" ?>">
					<input type="text" class="form-item form-control" name="prozent_ws" placeholder="WS" value="<?php echo $_POST["prozent_ws"] ? $_POST["prozent_ws"] : "" ?>">
				</div>
				<div class="col-sm-12 col-md-2 ">										
					<button class="btn btn-primary edit-order-btn" type="submit" name="show" value="1">Anzeigen</button>
					<button class="btn btn-primary edit-order-btn" type="submit" name="update" value="1" <?php echo $disabled ?>>Update</button>
				</div>
			</div>
		</form>
	</div>
</div>