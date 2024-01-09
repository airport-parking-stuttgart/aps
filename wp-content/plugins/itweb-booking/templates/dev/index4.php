<?php


global $wpdb;
$importDIR = ABSPATH . 'wp-content/plugins/itweb-booking/templates/dev/hex';
$files = scandir($importDIR);


$handle = fopen ($importDIR . '/' . $files[2],'r');
?>
<style>
th{
	width: 100px;
}
</style>
<table>
	<thead>
		<tr>
			<th>Code</th>
			<th>B-Nr.</th>
			<th>ID</th>
			<th>HEX Betrag</th>
			<th>APS Betrag</th>
			<th>HEX Prov</th>
			<th>APS Prov</th>
		</tr>
	</thead>
	<tbody>
<?php

while (($csv_array = fgetcsv ($handle, 1000, ',', '"')) !== FALSE ) {

  
	$code = explode(" ", ($csv_array[3]));
	
	$order_id = $wpdb->get_row("
				SELECT pm.post_id
				FROM {$wpdb->prefix}postmeta pm 
				WHERE pm.meta_key = 'token' and pm.meta_value = '" . trim($code[1]) . "'
				");
	if($order_id->post_id == null){
		echo "Buchung nicht vorhanden." . trim($code[1]) . "<br>";
		continue;
	}
  
	if($code[0] == 'STR4' || $code[0] == 'STR8' || $code[0] == 'STB2' || $code[0] == 'STB1' || $code[0] == 'STRH'){
		$hex_betrag = number_format($csv_array[8] / 70 * 100, 2, ".", ".");
		$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 30, 2, ".", ".");
	}
	if($code[0] == 'STR2'){
		$hex_betrag = number_format($csv_array[8] / 75 * 100, 2, ".", ".");
		$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 25, 2, ".", ".");
	}

	if($code[0] == 'STR6' || $code[0] == 'STR7' || $code[0] == 'STRD'){
		$hex_betrag = number_format($csv_array[8] / 70 * 100, 2, ".", ".");
		$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 30, 2, ".", ".");
	}
	if($code[0] == 'STR0'){
		$hex_betrag = number_format($csv_array[8] / 75 * 100, 2, ".", ".");
		$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 25, 2, ".", ".");
	}
  
	if($code[0] == 'STR1' ||$code[0] == 'STR9'){
		$hex_betrag = number_format($csv_array[8] / 70 * 100, 2, ".", ".");
		$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 30, 2, ".", ".");
	}
	if($code[0] == 'STRW'){
		$hex_betrag = number_format($csv_array[8] / 75 * 100, 2, ".", ".");
		$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 25, 2, ".", ".");
	}
	
	if($code[0] == 'ST10' || $code[0] == 'ST11'){
		$hex_betrag = number_format($csv_array[8] / 70 * 100, 2, ".", ".");
		$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 30, 2, ".", ".");
	}
	if($code[0] == 'ST12'){
		$hex_betrag = number_format($csv_array[8] / 75 * 100, 2, ".", ".");
		$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 25, 2, ".", ".");
	}
	
	if($code[0] == 'ST13' || $code[0] == 'ST14'){
		$hex_betrag = number_format($csv_array[8] / 70 * 100, 2, ".", ".");
		$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 30, 2, ".", ".");
	}
	if($code[0] == 'ST15'){
		$hex_betrag = number_format($csv_array[8] / 75 * 100, 2, ".", ".");
		$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 25, 2, ".", ".");
	}
	
	if($code[0] == 'ST16'){
		$hex_betrag = number_format($csv_array[8] / 70 * 100, 2, ".", ".");
		$hex_provision = number_format(($hex_betrag / 119 * 100) / 100 * 30, 2, ".", ".");
	}

	$sys_betrag = get_post_meta($order_id->post_id, '_order_total', true);
	$sys_provisionSQL = $wpdb->get_row("
				SELECT pm.meta_value
				FROM {$wpdb->prefix}itweb_orders_meta pm 
				WHERE pm.meta_key = 'provision' and pm.order_id = " . $order_id->post_id . "
				");
	$sys_provision = number_format($sys_provisionSQL->meta_value, 2, ".", ".");
	
	if($hex_betrag == $sys_betrag)
		$style_betrag = "color: green;";
	else
		$style_betrag = "color: red;";
	if($hex_provision == $sys_provision)
		$style_prov = "color: green;";
	else
		$style_prov = "color: red;";
	?>
	<tr>
		<td><?php echo $code[0] ?></td>
		<td><?php echo $code[1] ?></td>
		<td><?php echo $order_id->post_id ?></td>
		<td><?php echo $hex_betrag ?></td>
		<td style="<?php echo $style_betrag ?>"><?php echo $sys_betrag ?></td>
		<td><?php echo $hex_provision ?></td>
		<td style="<?php echo $style_prov ?>"><?php echo $sys_provision ?></td>
	</tr>
  <?php
  //echo "<pre>"; print_r($csv_array); echo "</pre>";
}

fclose($handle);

?>
	</tbody>
</table>