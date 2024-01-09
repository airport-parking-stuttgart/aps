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
<h4>Buchungen Monat <?php echo $months[$c_month] ?></h4>
<table class="table table-sm">
	<thead>
		<tr>
			<th>Vermittler</th>
			<th>Anzahl</th>
			<th>Umsatz</th>
			<th>d.B.U</th>
			<th>d.B.T</th>
			<th>d.B.Pers</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($clients as $client): ?>
		<?php
			$umsatz = $days = $pers = 0;
			$filter['buchung_von'] = $datefrom;
			$filter['buchung_bis'] = date('Y-m-d', strtotime($dateto));
			$filter['orderBy'] = "Buchungsdatum";
			$filter['betreiber'] = strtolower($client->short);
			$allorders = Database::getInstance()->get_bookinglistV2("wc-processing", $filter, '');
			$sum_orders_m += count($allorders);						
			foreach($allorders as $order){
				$umsatz += $order->Preis;								
				$days += getDaysBetween2Dates(new DateTime($order->Anreisedatum), new DateTime($order->Abreisedatum));
				$pers += $order->Personenanzahl;
				$sum_umsatz_m += $order->Preis;
				$sum_days_m += getDaysBetween2Dates(new DateTime($order->Anreisedatum), new DateTime($order->Abreisedatum));
				$sum_pers_m += $order->Personenanzahl;
				$d_umsatz[$client->short] +=  number_format($order->Preis, 0, ",", ".");
			}
			if($d_umsatz[$client->short] == null)
				$d_umsatz[$client->short] = 0;
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
			$filter['buchung_von'] = $datefrom;
			$filter['buchung_bis'] = date('Y-m-d', strtotime($dateto));
			$filter['orderBy'] = "Buchungsdatum";
			$filter['betreiber'] = strtolower($broker->short);
			$allorders = Database::getInstance()->get_bookinglistV2("wc-processing", $filter, '');
			$sum_orders_m += count($allorders);
			foreach($allorders as $order){
				$umsatz += $order->Preis;								
				$days += getDaysBetween2Dates(new DateTime($order->Anreisedatum), new DateTime($order->Abreisedatum));
				$pers += $order->Personenanzahl;
				$sum_umsatz_m += $order->Preis;
				$sum_days_m += getDaysBetween2Dates(new DateTime($order->Anreisedatum), new DateTime($order->Abreisedatum));
				$sum_pers_m += $order->Personenanzahl;
				$d_umsatz[$broker->short] +=  number_format($order->Preis, 0, ",", ".");
			}
			if($d_umsatz[$broker->short] == null)
				$d_umsatz[$broker->short] = 0;
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
			<td><strong><?php echo $sum_orders_m ?></strong></td>
			<td><strong><?php echo number_format($sum_umsatz_m, 2, ",", ".") ?></strong></td>
			<td><strong><?php echo $sum_orders_m != 0 ? number_format($sum_umsatz_m / $sum_orders_m, 2, ",", ".") : "0" ?></strong></td>
			<td><strong><?php echo $sum_orders_m != 0 ? number_format($sum_days_m / $sum_orders_m, 1, ",", ".") : "0" ?></strong></td>
			<td><strong><?php echo $sum_orders_m != 0 ? number_format($sum_pers_m / $sum_orders_m, 2, ",", ".") : "0" ?></strong></td>
		</tr>
	</tbody>
</table>
<?php

$data_diagramm = array();
foreach ($d_umsatz as $key => $val) {
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
