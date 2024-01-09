<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once(__DIR__ . '/../../../../../../wp-config.php');
global $wpdb;

require_once (__DIR__ . '/../../../classes/Database.php');

// Abreisen
$filter_de['list'] = 1;
$filter_de['datum_von_Ad'] = date('Y-m-d');
$filter_de['datum_bis_Ad'] = date('Y-m-d');
$filter['type'] = "shuttle";
$departure = Database::getInstance()->get_abreiseliste($filter_de);

ob_start();
?>
<table class="table table-sm">
	<thead>
		<tr>
			<th>Nr.</th>
			<th>Buchungs-Nr.</th> 		
			<th>Kunde</th> 
			<th>Abreisezeit</th> 
			<th>Personen</th> 
		</tr>
	</thead>
	<tbody>
	<?php $k = 1; foreach ($departure as $order) : ?>					
	<?php if ($order->Status == "wc-cancelled") continue; ?>
		<tr>
			<td><?php echo $k ?></td>
			<td><?php echo get_post_meta($order->order_id, 'token', true)  ?></td> 		
			<td><?php echo get_post_meta($order->order_id, '_billing_first_name', true) . " " . get_post_meta($order->order_id, '_billing_last_name', true) ?></td>
			<td><?php echo get_post_meta($order->order_id, 'Uhrzeit bis', true) ?></td> 
			<td><?php echo get_post_meta($order->order_id, 'Personenanzahl', true) ?></td> 				
		</tr>
	<?php $k++; endforeach; ?>
	</tbody>
</table>

<?php $content = ob_get_clean(); ?>
<?php echo $content; ?>