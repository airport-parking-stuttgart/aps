<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once(__DIR__ . '/../../../../../../wp-config.php');
global $wpdb;

require_once (__DIR__ . '/../../../classes/Database.php');

$months = array('1' => 'Januar', '2' => 'Februar', '3' => 'März', '4' => 'April', '5' => 'Mai', '6' => 'Juni',
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
<h4>Buchungen mit Anzahl Personen Anreise Monat <?php echo $months[$c_month] ?></h4>
<table class="table table-sm">
	<thead>
		<tr>
			<th>Vermittler</th>
			<th>1 Pers.</th>
			<th>2 Pers.</th>
			<th>3 Pers.</th>
			<th>4 Pers.</th>
			<th>Mehr als 4 Pers.</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($clients as $client): ?>
			<?php
			$pers = 0;
			unset($filter['buchung_von']);
			unset($filter['buchung_bis']);
			$filter['datum_von'] = $datefrom;
			$filter['datum_bis'] = $dateto;
			$filter['orderBy'] = "Anreisedatum";
			$filter['betreiber'] = strtolower($client->short);
			$allorders = Database::getInstance()->get_bookinglistV2("wc-processing", $filter, "");
			$sum_orders_im += count($allorders);						
			foreach($allorders as $order){							
				$pers += $order->Personenanzahl;
				$sum_pers_im += $order->Personenanzahl;
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
				<td><?php echo $pers_anz[$client->short]['1 Person'] ?></td>
				<td><?php echo $pers_anz[$client->short]['2 Personen'] ?></td>
				<td><?php echo $pers_anz[$client->short]['3 Personen'] ?></td>
				<td><?php echo $pers_anz[$client->short]['4 Personen'] ?></td>
				<td><?php echo $pers_anz[$client->short]['Mehr als 4 Personen'] ?></td>
			</tr>
		<?php endforeach; ?>
		<?php foreach($brokers as $broker): ?>
			<?php			
			$days = $pers = 0;
			unset($filter['buchung_von']);
			unset($filter['buchung_bis']);
			$filter['datum_von'] = $datefrom;
			$filter['datum_bis'] = $dateto;
			$filter['orderBy'] = "Anreisedatum";
			$filter['betreiber'] = strtolower($broker->short);
			$allorders = Database::getInstance()->get_bookinglistV2("wc-processing", $filter, "");
			$sum_orders_im += count($allorders);
			foreach($allorders as $order){				
				$pers += $order->Personenanzahl;
				$sum_pers_im += $order->Personenanzahl;
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
				<td><?php echo $pers_anz[$broker->short]['1 Person'] ?></td>
				<td><?php echo $pers_anz[$broker->short]['2 Personen'] ?></td>
				<td><?php echo $pers_anz[$broker->short]['3 Personen'] ?></td>
				<td><?php echo $pers_anz[$broker->short]['4 Personen'] ?></td>
				<td><?php echo $pers_anz[$broker->short]['Mehr als 4 Personen'] ?></td>
			</tr>
		<?php endforeach; ?>
		<tr>
			<td><strong>Summe</strong></td>
			<td><strong><?php echo $d_pers_anz['1 Person'] ?></strong></td>
			<td><strong><?php echo $d_pers_anz['2 Personen'] ?></strong></td>
			<td><strong><?php echo $d_pers_anz['3 Personen'] ?></strong></td>
			<td><strong><?php echo $d_pers_anz['4 Personen'] ?></strong></td>
			<td><strong><?php echo $d_pers_anz['Mehr als 4 Personen'] ?></strong></td>
		</tr>
	</tbody>
</table>

<?php
$data_diagramm = array(
	array('1 Person', intval($d_pers_anz['1 Person']), (string)$d_pers_anz['1 Person']),
	array('2 Personen', intval($d_pers_anz['2 Personen']), (string)$d_pers_anz['2 Personen']),
	array('3 Personen', intval($d_pers_anz['3 Personen']), (string)$d_pers_anz['3 Personen']),
	array('4 Personen', intval($d_pers_anz['4 Personen']), (string)$d_pers_anz['4 Personen']),
	array('Mehr als 4 Personen', intval($d_pers_anz['Mehr als 4 Personen']), (string)$d_pers_anz['Mehr als 4 Personen'])
);
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