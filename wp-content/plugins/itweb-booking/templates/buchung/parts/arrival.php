<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once(__DIR__ . '/../../../../../../wp-config.php');
global $wpdb;

require_once (__DIR__ . '/../../../classes/Database.php');

// Anreisen
$filter_arr['list'] = 1;
$filter_arr['datum_von'] = date('Y-m-d');
$filter_arr['datum_bis'] = date('Y-m-d');
$arrival = Database::getInstance()->get_anreiseliste($filter_arr);

ob_start();
?>
<table class="table table-sm">
	<thead>
		<tr>
			<th>Nr.</th>
			<th>Buchungs-Nr.</th> 		
			<th>Kunde</th>
			<th>Anreisezeit</th>
			<th>Personen</th>
		</tr>
	</thead>
	<tbody>
	<?php $k = 1; foreach ($arrival as $order) : ?>					
	<?php if ($order->Status == "wc-cancelled") continue; ?>	
		<tr>
			<td><?php echo $k ?></td> 	
			<td><?php echo get_post_meta($order->order_id, 'token', true) ?></td> 		
			<td><?php echo get_post_meta($order->order_id, '_billing_first_name', true) . " " . get_post_meta($order->order_id, '_billing_last_name', true) ?></td>
			<td><?php echo get_post_meta($order->order_id, 'Uhrzeit von', true) ?></td> 
			<td><?php echo get_post_meta($order->order_id, 'Personenanzahl', true) ?></td> 				
		</tr>
	<?php $k++; endforeach; ?>
	</tbody>
</table>
<?php $content = ob_get_clean(); ?>
<?php echo $content; ?>