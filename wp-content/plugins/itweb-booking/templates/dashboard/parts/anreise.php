<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once(__DIR__ . '/../../../../../../wp-config.php');
global $wpdb;

require_once (__DIR__ . '/../../../classes/Database.php');

$months = array('1' => 'Januar', '2' => 'Fabruar', '3' => 'März', '4' => 'April', '5' => 'Mai', '6' => 'Juni',
				'7' => 'Juli', '8' => 'August', '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Dezember');
$clients = Database::getInstance()->getAllClients();
$brokers = Database::getInstance()->getBrokers();

$c_month = date('n');
$c_year = date('Y');
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $c_month, $c_year);

$dateto = date('Y-m-d', strtotime(date($c_year."-".$c_month."-".$daysInMonth)));
$datefrom = date('Y-m-d', strtotime(date($c_year."-".$c_month."-01")));
ob_start();
?>
<h4>Anreise Monat <?php echo $months[$c_month] ?></h4>
<table class="table table-sm">
	<thead>
		<tr>
			<th>Vermittler</th>
			<th>Buchungen</th>
			<th>Ist-Umsatz</th>
			<th>d.B.U</th>
			<th>d.B.T</th>
			<th>d.B.Pers</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($clients as $client): ?>
		<?php
			$umsatz = $days = $pers = 0;
			unset($filter['buchung_von']);
			unset($filter['buchung_bis']);
			$filter['datum_von'] = $datefrom;
			$filter['datum_bis'] = $dateto;
			$filter['orderBy'] = "Anreisedatum";
			$filter['betreiber'] = strtolower($client->short);
			$allorders = Database::getInstance()->get_bookinglistV2("wc-processing", $filter, "");
			$sum_orders_im += count($allorders);						
			foreach($allorders as $order){
				$umsatz += $order->Preis;								
				$days += getDaysBetween2Dates(new DateTime($order->Anreisedatum), new DateTime($order->Abreisedatum));
				$pers += $order->Personenanzahl;
				$sum_umsatz_im += $order->Preis;
				$sum_days_im += getDaysBetween2Dates(new DateTime($order->Anreisedatum), new DateTime($order->Abreisedatum));
				$sum_pers_im += get_post_meta($order->order_id, 'Personenanzahl', true);
				$d_ist_umsatz[$client->short] +=  number_format($order->Preis, 0, ",", ".");
				$d_pers[$client->short] += $order->Personenanzahl;
				if($order->Personenanzahl == 1){
					$pers_anz[$client->short]['1 Person']++;
					$d_pers_anz['1 Person']++;
				}									
				if($order->Personenanzahl == 2){
					$pers_anz[$client->short]['2 Personen']++;
					$d_pers_anz['2 Personen']++;
				}									
				if($order->Personenanzahl == 3){
					$pers_anz[$client->short]['3 Personen']++;
					$d_pers_anz['3 Personen']++;
				}									
				if($order->Personenanzahl == 4){
					$pers_anz[$client->short]['4 Personen']++;
					$d_pers_anz['4 Personen']++;
				}									
				if($order->Personenanzahl > 4){
					$pers_anz[$client->short]['Mehr als 4 Personen']++;
					$d_pers_anz['Mehr als 4 Personen']++;
				}
			}
			if($d_ist_umsatz[$client->short] == null)
				$d_ist_umsatz[$client->short] = 0;
			if($d_pers[$client->short] == null)
				$d_pers[$client->short] = 0;
			if($d_pers_anz['1 Person'] == null)
				$d_pers_anz['1 Person'] = 0;
			if($d_pers_anz['2 Personen'] == null)
				$d_pers_anz['2 Personen'] = 0;
			if($d_pers_anz['3 Personen'] == null)
				$d_pers_anz['3 Personen'] = 0;
			if($d_pers_anz['4 Personen'] == null)
				$d_pers_anz['4 Personen'] = 0;
			if($d_pers_anz['Mehr als 4 Personen'] == null)
				$d_pers_anz['Mehr als 4 Personen'] = 0;
			if($pers_anz[$client->short]['1 Person'] == null)
				$pers_anz[$client->short]['1 Person'] = 0;
			if($pers_anz[$client->short]['2 Personen'] == null)
				$pers_anz[$client->short]['2 Personen'] = 0;
			if($pers_anz[$client->short]['3 Personen'] == null)
				$pers_anz[$client->short]['3 Personen'] = 0;
			if($pers_anz[$client->short]['4 Personen'] == null)
				$pers_anz[$client->short]['4 Personen'] = 0;
			if($pers_anz[$client->short]['Mehr als 4 Personen'] == null)
				$pers_anz[$client->short]['Mehr als 4 Personen'] = 0;
		?>
			<tr>
				<td><?php echo $client->short ?></td>
				<td><?php echo count($allorders) ?></td>
				<td><?php echo number_format($umsatz, 2, ",", ".") ?></td>
				<td><?php echo count($allorders) != 0 ? number_format($umsatz / count($allorders), 2, ",", ".") : "0" ?></td>
				<td><?php echo count($allorders) != 0 ? number_format($days / count($allorders), 1, ",", ".") : "0" ?></td>
				<td><?php echo count($allorders) != 0 ? number_format($pers / count($allorders), 2, ",", ".") : "0" ?></td>
			</tr>
		<?php endforeach; ?>
		<?php foreach($brokers as $broker): ?>
			<?php
			$umsatz = $days = $pers = 0;
			unset($filter['buchung_von']);
			unset($filter['buchung_bis']);
			$filter['datum_von'] = $datefrom;
			$filter['datum_bis'] = $dateto;
			$filter['orderBy'] = "Anreisedatum";
			$filter['betreiber'] = strtolower($broker->short);
			$allorders = Database::getInstance()->get_bookinglistV2("wc-processing", $filter, "");
			$sum_orders_im += count($allorders);
			foreach($allorders as $order){
				$umsatz += $order->Preis;								
				$days += getDaysBetween2Dates(new DateTime($order->Anreisedatum), new DateTime($order->Abreisedatum));
				$pers += $order->Personenanzahl;
				$sum_umsatz_im += $order->Preis;
				$sum_days_im += getDaysBetween2Dates(new DateTime($order->Anreisedatum), new DateTime($order->Abreisedatum));
				$sum_pers_im += $order->Personenanzahl;
				$d_ist_umsatz[$broker->short] +=  number_format($order->Preis, 0, ",", ".");
				$d_pers[$broker->short] += $order->Personenanzahl;
				if($order->Personenanzahl == 1){
					$pers_anz[$broker->short]['1 Person']++;
					$d_pers_anz['1 Person']++;
				}									
				if($order->Personenanzahl == 2){
					$pers_anz[$broker->short]['2 Personen']++;
					$d_pers_anz['2 Personen']++;
				}									
				if($order->Personenanzahl == 3){
					$pers_anz[$broker->short]['3 Personen']++;
					$d_pers_anz['3 Personen']++;
				}									
				if($order->Personenanzahl == 4){
					$pers_anz[$broker->short]['4 Personen']++;
					$d_pers_anz['4 Personen']++;
				}									
				if($order->Personenanzahl > 4){
					$pers_anz[$broker->short]['Mehr als 4 Personen']++;
					$d_pers_anz['Mehr als 4 Personen']++;
				}
					
			}
			if($d_ist_umsatz[$broker->short] == null)
				$d_ist_umsatz[$broker->short] = 0;
			if($d_pers[$broker->short] == null)
				$d_pers[$broker->short] = 0;
			if($d_pers_anz['1 Person'] == null)
				$d_pers_anz['1 Person'] = 0;
			if($d_pers_anz['2 Personen'] == null)
				$d_pers_anz['2 Personen'] = 0;
			if($d_pers_anz['3 Personen'] == null)
				$d_pers_anz['3 Personen'] = 0;
			if($d_pers_anz['4 Personen'] == null)
				$d_pers_anz['4 Personen'] = 0;
			if($d_pers_anz['Mehr als 4 Personen'] == null)
				$d_pers_anz['Mehr als 4 Personen'] = 0;
			if($pers_anz[$broker->short]['1 Person'] == null)
				$pers_anz[$broker->short]['1 Person'] = 0;
			if($pers_anz[$broker->short]['2 Personen'] == null)
				$pers_anz[$broker->short]['2 Personen'] = 0;
			if($pers_anz[$broker->short]['3 Personen'] == null)
				$pers_anz[$broker->short]['3 Personen'] = 0;
			if($pers_anz[$broker->short]['4 Personen'] == null)
				$pers_anz[$broker->short]['4 Personen'] = 0;
			if($pers_anz[$broker->short]['Mehr als 4 Personen'] == null)
				$pers_anz[$broker->short]['Mehr als 4 Personen'] = 0;
			?>						
			<tr>
				<td><?php echo $broker->short ?></td>
				<td><?php echo count($allorders) ?></td>
				<td><?php echo number_format($umsatz, 2, ",", ".") ?></td>
				<td><?php echo count($allorders) != 0 ? number_format($umsatz / count($allorders), 2, ",", ".") : "0" ?></td>
				<td><?php echo count($allorders) != 0 ? number_format($days / count($allorders), 1, ",", ".") : "0" ?></td>
				<td><?php echo count($allorders) != 0 ? number_format($pers / count($allorders), 2, ",", ".") : "0" ?></td>
			</tr>
		<?php endforeach; ?>
		<tr>
			<td><strong>Summe</strong></td>
			<td><strong><?php echo $sum_orders_im ?></strong></td>
			<td><strong><?php echo number_format($sum_umsatz_im, 2, ",", ".") ?></strong></td>
			<td><strong><?php echo $sum_orders_im != 0 ? number_format($sum_umsatz_im / $sum_orders_im, 2, ",", ".") : "0" ?></strong></td>
			<td><strong><?php echo $sum_orders_im != 0 ? number_format($sum_days_im / $sum_orders_im, 1, ",", ".") : "0" ?></strong></td>
			<td><strong><?php echo $sum_orders_im != 0 ? number_format($sum_pers_im / $sum_orders_im, 2, ",", ".") : "0" ?></strong></td>
		</tr>
	</tbody>
</table>
<?php

$data_diagramm = array();
foreach ($d_ist_umsatz as $key => $val) {
	$value = floatval($val);
	$anno = number_format($val, 0, ".", ".");
    $data_diagramm[] = array((string)$key, $value, (string)$anno);
}
?>

<?php $content = ob_get_clean(); ?>
<?php
$data = array(
    "content" => $content,
    "diagramm" => $data_diagramm,
    // Weitere Variablen hier
);

echo json_encode($data);
?>