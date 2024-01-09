<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once(__DIR__ . '/../../../../../../wp-config.php');
global $wpdb;

require_once (__DIR__ . '/../../../classes/Database.php');

// today
$filter_today['buchung_von'] = date('Y-m-d');
$filter_today['buchung_bis'] = date('Y-m-d', strtotime(date('Y-m-d') . '+1 day'));
$filter_today['orderBy'] = "Buchungsdatum";
$today = Database::getInstance()->get_bookinglist($filter_today);

ob_start();
?>

<table class="table table-sm">
	<thead>
		<tr>
			<th>Nr.</th>
			<th>Produkt</th>
			<th>Buchungsnummer</th>
			<th>Name</th>
			<th>Anreisedatum</th>
			<th>Uhrzeit</th>
			<th>Abreisedatum</th>
			<th>Uhrzeit</th>
			<th>Parkdauer</th>
			<th>Personen</th>
			<th>E-Mail</th>
			<th>Zahlungsart</th>
			<th>Gebühren</th>
			<th>Netto</th>
			<th>Service</th>
		</tr>
	</thead>
	<tbody>
		<?php $k = 1; foreach ($today as $booking) : ?>
		<?php
			
			$additionalPrice = "0.00";
			$services = Database::getInstance()->getBookingMetaAsResults($booking->order_id, 'additional_services');
			if(count($services) > 0){
				foreach($services as $v){
					$s = Database::getInstance()->getAdditionalService($v->meta_value);
					$additionalPrice += $s->price;
				}
			}
		?>
		<tr>
			<td><?php echo $k ?></td>
			<td><?php echo $booking->Code ?></td>
			<td><?php echo get_post_meta($booking->order_id, 'token', true) ?></td>
			<td><?php echo get_post_meta($booking->order_id, '_billing_first_name', true) . ' ' . get_post_meta($booking->order_id, '_billing_last_name', true) ?></td>                   
			<td><?php echo get_post_meta($booking->order_id, 'Anreisedatum', true) ? date('d.m.Y', strtotime(get_post_meta($booking->order_id, 'Anreisedatum', true))) : "-" ?></td>										
			<td><?php echo get_post_meta($booking->order_id, 'Uhrzeit von', true) ? date('H:i', strtotime(get_post_meta($booking->order_id, 'Uhrzeit von', true))) : "-" ?></td>                  
			<td><?php echo get_post_meta($booking->order_id, 'Abreisedatum', true) ? date('d.m.Y', strtotime(get_post_meta($booking->order_id, 'Abreisedatum', true))) : "-" ?></td>									
			<td><?php echo get_post_meta($booking->order_id, 'Uhrzeit bis', true) ? date('H:i', strtotime(get_post_meta($booking->order_id, 'Uhrzeit bis', true))) : "-" ?></td>					
			<td><?php echo get_post_meta($booking->order_id, 'Anreisedatum', true) != null && get_post_meta($booking->order_id, 'Abreisedatum', true) != null && $booking->is_for != 'hotel' ? getDaysBetween2Dates(new DateTime(get_post_meta($booking->order_id, 'Anreisedatum', true)), new DateTime(get_post_meta($booking->order_id, 'Abreisedatum', true))) : "-" ?></td>										
			<td><?php echo get_post_meta($booking->order_id, 'Personenanzahl', true) ? get_post_meta($booking->order_id, 'Personenanzahl', true) : "-" ?></td>							
			<td><?php echo get_post_meta($booking->order_id, '_billing_email', true) ? get_post_meta($booking->order_id, '_billing_email', true) : "-" ?></td>
			<td><?php echo get_post_meta($booking->order_id, '_payment_method_title', true) ?></td>
			<td><?php echo get_post_meta($booking->order_id, '_order_total', true) ?></td>
			<td><?php echo number_format(get_post_meta($booking->order_id, '_order_total', true) / 119 * 100, 2, ".", ".") ?></td>
			<td><?php echo $additionalPrice != '0.00' ? number_format($additionalPrice, 2, '.', '') : '-' ?></td>
		</tr>
	<?php $k++; endforeach; ?>
	</tbody>
</table>

<?php $content = ob_get_clean(); ?>
<?php echo $content; ?>