<?php
global $wpdb;
$bookings = $wpdb->get_results("
SELECT o.order_id, FORMAT(o.b_total,2) AS b_total, FORMAT(o.m_v_total,2) AS k_total,
(select FORMAT(pm.meta_value,2) from 59hkh_postmeta pm where pm.meta_key = '_order_total' and pm.post_id = o.order_id) AS pm_total,
(select pm.meta_value from 59hkh_postmeta pm where pm.meta_key = '_payment_method_title' and pm.post_id = o.order_id) AS pm_payment
FROM 59hkh_itweb_orders o 
inner join 59hkh_wc_order_stats ps on ps.order_id = o.order_id
WHERE ps.status = 'wc-processing' and o.product_id = 3082
");
$disabled = "disabled";
if(count($bookings) > 0){
	foreach($bookings as $k){
		if($k->pm_payment == 'Barzahlung'){
			if($k->b_total != $k->pm_total){
				$disabled = "";
				echo $k->order_id . " " . get_post_meta($k->order_id, 'token')[0] . ", b_total: " . $k->b_total . ", pm_total: ". $k->pm_total . "<br>";
				if($_POST['update']){
					$wpdb->update('59hkh_itweb_orders', [
						'b_total' => $k->pm_total,
						'm_v_total' => 0
					], ['order_id' => $k->order_id]);	
				}
			}
		}
		else{
			if($k->k_total != $k->pm_total){
				$disabled = "";
				echo $k->order_id . " " . get_post_meta($k->order_id, 'token')[0] . ", k_total: " . $k->k_total . ", pm_total: ". $k->pm_total . "<br>";
				if($_POST['update']){
					$wpdb->update('59hkh_itweb_orders', [
						'b_total' => 0,
						'm_v_total' => $k->pm_total
					], ['order_id' => $k->order_id]);	
				}
			}
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