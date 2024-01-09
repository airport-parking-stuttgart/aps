<?php
global $wpdb;
$prices = $wpdb->get_results("
SELECT pl.order_id, pl.product_gross_revenue, pm.meta_value FROM 59hkh_wc_order_product_lookup pl 
inner join 59hkh_postmeta pm on pm.post_id = pl.order_id
WHERE pm.meta_key = '_order_total' and pl.product_gross_revenue != pm.meta_value
");
$disabled = "disabled";
if(count($prices) > 0){
	foreach($prices as $k){
		$disabled = "";
		echo $k->order_id . " " . get_post_meta($k->order_id, 'token')[0] . " " . $k->product_gross_revenue . " " . $k->meta_value . "<br>";
		if($_POST['update']){
			$wpdb->update('59hkh_wc_order_product_lookup', [
					'product_net_revenue' => $k->meta_value / 119 * 100,
					'product_gross_revenue' => $k->meta_value,
					'tax_amount' => $k->meta_value / 119 * 19
				], ['order_id' => $k->order_id]);
			update_post_meta($k->order_id, '_order_tax', ($k->meta_value / 119 * 19));
		}
	}
}
if($_POST['update']){
	echo "<br>aktualisiert<br>";
}

echo "<pre>";
//print_r($_POST);
echo "</pre>";
?>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-body">
		<form class="update-price" action="<?php echo basename($_SERVER['REQUEST_URI']); ?>" method="POST">
			<div class="row m60">
				<div class="col-sm-12 col-md-2 ">					
					<input type="hidden" name="update" value="1">
					<input class="btn btn-primary edit-order-btn" type="submit" value="Update" <?php echo $disabled ?>>
				</div>
			</div>
		</form>
	</div>
</div>