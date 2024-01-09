<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once(__DIR__ . '/../../../../../../wp-config.php');
global $wpdb;

require_once (__DIR__ . '/../../../classes/Database.php');


$clients = Database::getInstance()->getAllClients();
$brokers = Database::getInstance()->getBrokers();

$dateto = date('Y-m-d');
$datefrom = date('Y-m-d');

ob_start();
?>
<h4>Buchungen heute</h4>
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
			$allorders = Database::getInstance()->get_bookinglistV2("wc-processing", $filter, "");
			$sum_orders += count($allorders);						
			foreach($allorders as $order){
				$umsatz += $order->Preis;								
				$days += getDaysBetween2Dates(new DateTime($order->Anreisedatum), new DateTime($order->Abreisedatum));
				$pers += $order->Personenanzahl;
				$sum_umsatz += $order->Preis;
				$sum_days += getDaysBetween2Dates(new DateTime($order->Anreisedatum), new DateTime($order->Abreisedatum));
				$sum_pers += $order->Personenanzahl;
				$d_umsatz_heute[$client->short] +=  number_format($order->Preis, 0, ",", ".");
			}
			//echo "<pre>"; print_r($allorders); echo "</pre>";
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
			$allorders = Database::getInstance()->get_bookinglistV2("wc-processing", $filter, "");
			$sum_orders += count($allorders);
			foreach($allorders as $order){
				$umsatz += $order->Preis;								
				$days += getDaysBetween2Dates(new DateTime($order->Anreisedatum), new DateTime($order->Abreisedatum));
				$pers += $order->Personenanzahl;
				$sum_umsatz += $order->Preis;
				$sum_days += getDaysBetween2Dates(new DateTime($order->Anreisedatum), new DateTime($order->Abreisedatum));
				$sum_pers += $order->Personenanzahl;
				$d_umsatz_heute[$broker->short] +=  number_format($order->Preis, 0, ",", ".");
			}
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
			<td><strong><?php echo $sum_orders ?></strong></td>
			<td><strong><?php echo number_format($sum_umsatz, 2, ",", ".") ?></strong></td>
			<td><strong><?php echo $sum_orders != 0 ? number_format($sum_umsatz / $sum_orders, 2, ",", ".") : "0" ?></strong></td>
			<td><strong><?php echo $sum_orders != 0 ? number_format($sum_days / $sum_orders, 1, ",", ".") : "0" ?></strong></td>
			<td><strong><?php echo $sum_orders != 0 ? number_format($sum_pers / $sum_orders, 2, ",", ".") : "0" ?></strong></td>
		</tr>
	</tbody>
</table>

<?php
$data_diagramm = array();
foreach ($d_umsatz_heute as $key => $val) {
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
