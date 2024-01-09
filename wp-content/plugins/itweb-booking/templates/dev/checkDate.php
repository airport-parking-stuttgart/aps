<?php
global $wpdb;

$dates = $wpdb->get_results("
SELECT o.order_id as o_id, DATE(o.date_from) order_from, DATE(o.date_to) order_to, TIME(o.date_from) order_time_from, TIME(o.date_to) order_time_to,
	(SELECT DATE(pm.meta_value)
		FROM 59hkh_postmeta pm
		WHERE pm.meta_key = 'Anreisedatum' and pm.post_id = o_id) meta_from,
     (SELECT DATE(pm.meta_value) 
		FROM 59hkh_postmeta pm
		WHERE pm.meta_key = 'Abreisedatum' and pm.post_id = o_id) meta_to,
      (SELECT pm.meta_value
		FROM 59hkh_postmeta pm
		WHERE pm.meta_key = 'Uhrzeit von' and pm.post_id = o_id) meta_time_from,
     (SELECT pm.meta_value
		FROM 59hkh_postmeta pm
		WHERE pm.meta_key = 'Uhrzeit bis' and pm.post_id = o_id) meta_time_to
FROM 59hkh_itweb_orders o
LEFT JOIN 59hkh_wc_order_stats s on s.order_id = o.order_id
WHERE s.status = 'wc-processing'
");

foreach($dates as $k){
	if($k->order_from != $k->meta_from){
		if($k->meta_from != null){
			if($_POST['update']){
				$wpdb->update('59hkh_itweb_orders', [
				'date_from' => date('Y-m-d H:i', strtotime($k->meta_from . ' ' . $k->meta_time_from))            
				], ['order_id' => $k->o_id]);
			}
		}
		echo "Datum von: " . $k->o_id . ", of: " . $k->order_from . ", mf: " . $k->meta_from . "<br>";
	}
	if($k->order_to != $k->meta_to){
		if($k->meta_to != null){
			if($_POST['update']){
				$wpdb->update('59hkh_itweb_orders', [
					'date_to' => date('Y-m-d H:i', strtotime($k->meta_to . ' ' . $k->meta_time_to))            
					], ['order_id' => $k->o_id]);
			}
		}
		echo "Datum bis: " .  $k->o_id . ", ot: " . $k->order_to . ", mt: " . $k->meta_to . "<br>";
	}
	if($k->order_from == $k->order_to){
		echo "Datum von == bis: " .  $k->o_id . ", of: " . $k->order_from . ", ot: " . $k->order_to . "<br>";
	}
	
	if($k->meta_to < $k->meta_from){
		echo "Abreise < Anreise: " .  $k->o_id . ", mf: " . $k->order_from . ", mt: " . $k->meta_to . "<br>";
	}
	
	/*if($k['order_time_from'] != $k['meta_time_from']){
		if($k['meta_time_from'] != null)
			echo $k['order_id'] . ", otf: " . $k['order_time_from'] . ", mtf: " . $k['meta_time_from'] . "<br>";
	}
	if($k['order_time_to'] != $k['meta_time_to.".00"']){
		if($k['meta_time_to'] != null)
			echo $k['order_id'] . ", ott: " . $k['order_time_to'] . ", mtt: " . $k['meta_time_to'] . "<br>";
	}*/
}
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