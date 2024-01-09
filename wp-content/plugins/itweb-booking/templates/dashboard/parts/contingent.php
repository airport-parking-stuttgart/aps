<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once(__DIR__ . '/../../../../../../wp-config.php');
global $wpdb;

require_once (__DIR__ . '/../../../classes/Database.php');

$dateto = date('Y-m-d');
$datefrom = date('Y-m-d');

$dateParklots = Database::getInstance()->getParkotsWithOrdersData($datefrom);

$date[0] = $dateto;
$date[1] = $dateto;
$allContingent = Database::getInstance()->getAllContingent($date);

foreach($allContingent as $ac){
	$set_con[$ac->date."_".$ac->product_id] = $ac->contingent;
}
ob_start();
?>

	<h4>Kontingent Stand heute</h4>
	<table class="table table-sm">
		<thead>
			<tr>
				<th>Standort</th>
				<th>Soll</th>
				<th>Ist</th>
				<th>Prozent belegt</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Parkhaus PH</td>
				<?php
					$parklot = Database::getInstance()->getParklotByProductId(537);
					if($set_con[date('Y-m-d')."_537"] != null)
						$con_ph += $set_con[date('Y-m-d')."_537"];
					else
						$con_ph += $parklot->contigent;
					$parklot = Database::getInstance()->getParklotByProductId(621);
					if($set_con[date('Y-m-d')."_621"] != null)
						$con_ph += $set_con[date('Y-m-d')."_621"];
					else
						$con_ph += $parklot->contigent;
				?>
				<td><?php echo $con_ph ?></td>
				<?php
				foreach ($dateParklots as $dateParklot){
					if($dateParklot->order_lot == 1 || $dateParklot->order_lot == 3 || $dateParklot->order_lot == 5 || $dateParklot->order_lot == 6)
						$used_ph += $dateParklot->used;
				}
				
				if($con_ph != 0){
					if(number_format($used_ph / $con_ph * 100, 2,".",".") >= 70 && number_format($used_ph / $con_ph * 100, 2,".",".") < 85)
						$text_color = "purple";
					elseif(number_format($used_ph / $con_ph * 100, 2,".",".") >= 85)
						$text_color = "red";										
					else
						$text_color = "";
				}
				else
					$text_color = "";
				if(number_format($used_ph / $con_ph * 100, 2,".",".") == 100)
					$background = "green";
				elseif($used_ph > $con_ph)
					$background = "yellow";
				else
					$background = "";
				
				?>
				<td><?php echo $used_ph ?></td>
				<td class="<?php echo $text_color ?> <?php echo $background ?>"><?php echo $con_ph != 0 ? number_format($used_ph / $con_ph * 100, 2, ",", ".") : "0"?></td>
			</tr>
			<tr>
				<td>Parkhaus OD</td>
				<?php
					$parklot = Database::getInstance()->getParklotByProductId(592);
					if($set_con[date('Y-m-d')."_592"] != null)
						$con_od += $set_con[date('Y-m-d')."_592"];
					else
						$con_od += $parklot->contigent;
					
					$parklot = Database::getInstance()->getParklotByProductId(624);
					if($set_con[date('Y-m-d')."_624"] != null)
						$con_od += $set_con[date('Y-m-d')."_624"];
					else
						$con_od += $parklot->contigent;
				?>
				<td><?php echo $con_od ?></td>
				<?php
				foreach ($dateParklots as $dateParklot){
					if($dateParklot->order_lot == 10 || $dateParklot->order_lot == 12 || $dateParklot->order_lot == 14)
						$used_od += $dateParklot->used;
				}
				
				if($con_od != 0){
					if(number_format($used_od / $con_od * 100, 2,".",".") >= 70 && number_format($used_od / $con_od * 100, 2,".",".") < 85)
						$text_color = "purple";
					elseif(number_format($used_od / $con_od * 100, 2,".",".") >= 85)
						$text_color = "red";										
					else
						$text_color = "";
				}
				else
					$text_color = "";
				if(number_format($used_od / $con_od * 100, 2,".",".") == 100)
					$background = "green";
				elseif($used_od > $con_od)
					$background = "yellow";
				else
					$background = "";
				?>
				<td><?php echo $used_od ?></td>
				<td class="<?php echo $text_color ?> <?php echo $background ?>"><?php echo $con_od != 0 ? number_format($used_od / $con_od * 100, 2, ",", ".") : "0"?></td>
			</tr>
			<tr>
				<td>Sielmingen</td>
				<?php
					$parklot = Database::getInstance()->getParklotByProductId(873);
					if($set_con[date('Y-m-d')."_873"] != null)
						$con_sie += $set_con[date('Y-m-d')."_873"];
					else
						$con_sie += $parklot->contigent;
					$parklot = Database::getInstance()->getParklotByProductId(901);
					if($set_con[date('Y-m-d')."_901"] != null)
						$con_sie += $set_con[date('Y-m-d')."_901"];
					else
						$con_sie += $parklot->contigent;
					$parklot = Database::getInstance()->getParklotByProductId(45856);
					if($set_con[date('Y-m-d')."_45856"] != null)
						$con_sie += $set_con[date('Y-m-d')."_45856"];
					else
						$con_sie += $parklot->contigent;
				?>
				<td><?php echo $con_sie ?></td>
				<?php
				foreach ($dateParklots as $dateParklot){
					if($dateParklot->order_lot == 30 || $dateParklot->order_lot == 32 || $dateParklot->order_lot == 34 || $dateParklot->order_lot == 35)
						$used_sie += $dateParklot->used;
				}
				
				if($con_sie != 0){
					if(number_format($used_sie / $con_sie * 100, 2,".",".") >= 70 && number_format($used_sie / $con_sie * 100, 2,".",".") < 85)
						$text_color = "purple";
					elseif(number_format($used_sie / $con_sie * 100, 2,".",".") >= 85)
						$text_color = "red";										
					else
						$text_color = "";
				}
				else
					$text_color = "";
				if(number_format($used_sie / $con_sie * 100, 2,".",".") == 100)
					$background = "green";
				elseif($used_sie > $con_sie)
					$background = "yellow";
				else
					$background = "";
				?>
				<td><?php echo $used_sie ?></td>
				<td class="<?php echo $text_color ?> <?php echo $background ?>"><?php echo $con_sie != 0 ? number_format($used_sie / $con_sie * 100, 2, ",", ".") : "0"?></td>
			</tr>
			<tr>
				<td>Ostfildern PH</td>
				<?php
					$parklot = Database::getInstance()->getParklotByProductId(24222);
					if($set_con[date('Y-m-d')."_24222"] != null)
						$con_ost += $set_con[date('Y-m-d')."_24222"];
					else
						$con_ost += $parklot->contigent;
					$parklot = Database::getInstance()->getParklotByProductId(24261);
					if($set_con[date('Y-m-d')."_24261"] != null)
						$con_ost += $set_con[date('Y-m-d')."_24261"];
					else
						$con_ost += $parklot->contigent;
					$parklot = Database::getInstance()->getParklotByProductId(41402);
					if($set_con[date('Y-m-d')."_41402"] != null)
						$con_ost += $set_con[date('Y-m-d')."_41402"];
					else
						$con_ost += $parklot->contigent;
				?>
				<td><?php echo $con_ost ?></td>
				<?php
				foreach ($dateParklots as $dateParklot){
					if($dateParklot->order_lot == 40 || $dateParklot->order_lot == 42 || $dateParklot->order_lot == 44 || $dateParklot->order_lot == 47)
						$used_ost += $dateParklot->used;
				}
				
				if($con_ost != 0){
					if(number_format($used_ost / $con_ost * 100, 2,".",".") >= 70 && number_format($used_ost / $con_ost * 100, 2,".",".") < 85)
						$text_color = "purple";
					elseif(number_format($used_ost / $con_ost * 100, 2,".",".") >= 85)
						$text_color = "red";										
					else
						$text_color = "";
				}
				else
					$text_color = "";
				if(number_format($used_ost / $con_ost * 100, 2,".",".") == 100)
					$background = "green";
				elseif($used_ost > $con_ost)
					$background = "yellow";
				else
					$background = "";
				?>
				<td><?php echo $used_ost ?></td>
				<td class="<?php echo $text_color ?> <?php echo $background ?>"><?php echo $con_ost != 0 ? number_format($used_ost / $con_ost * 100, 2, ",", ".") : "0"?></td>
			</tr>
			<tr>
				<td>Ostfildern PP</td>
				<?php
					$parklot = Database::getInstance()->getParklotByProductId(24226);
					if($set_con[date('Y-m-d')."_24226"] != null)
						$con_nu += $set_con[date('Y-m-d')."_24226"];
					else
						$con_nu += $parklot->contigent;
					$parklot = Database::getInstance()->getParklotByProductId(24263);
					if($set_con[date('Y-m-d')."_24263"] != null)
					$con_nu += $set_con[date('Y-m-d')."_24263"];
					else
						$con_nu += $parklot->contigent;
					$parklot = Database::getInstance()->getParklotByProductId(41403);
					if($set_con[date('Y-m-d')."_41403"] != null)
						$con_nu += $set_con[date('Y-m-d')."_41403"];
					else
						$con_nu += $parklot->contigent;
				?>
				<td><?php echo $con_nu ?></td>
				<?php
				foreach ($dateParklots as $dateParklot){
					if($dateParklot->order_lot == 50 || $dateParklot->order_lot == 52 || $dateParklot->order_lot == 54 || $dateParklot->order_lot == 55)
						$used_nu += $dateParklot->used;
				}
				
				if($con_nu != 0){
					if(number_format($used_nu / $con_nu * 100, 2,".",".") >= 70 && number_format($used_nu / $con_nu * 100, 2,".",".") < 85)
						$text_color = "purple";
					elseif(number_format($used_nu / $con_nu * 100, 2,".",".") >= 85)
						$text_color = "red";										
					else
						$text_color = "";
				}
				else
					$text_color = "";
				if(number_format($used_nu / $con_nu * 100, 2,".",".") == 100)
					$background = "green";
				elseif($used_nu > $con_nu)
					$background = "yellow";
				else
					$background = "";
				?>
				<td><?php echo $used_nu ?></td>
				<td class="<?php echo $text_color ?> <?php echo $background ?>"><?php echo $con_nu != 0 ? number_format($used_nu / $con_nu * 100, 2, ",", ".") : "0"?></td>
			</tr>
			<tr>
				<td>Bernhausen PG</td>
				<?php
					$parklot = Database::getInstance()->getParklotByProductId(80566);
					if($set_con[date('Y-m-d')."_80566"] != null)
						$con_pg += $set_con[date('Y-m-d')."_80566"];
					else
						$con_pg += $parklot->contigent;
				?>
				<td><?php echo $con_pg ?></td>
				<?php
				foreach ($dateParklots as $dateParklot){
					if($dateParklot->order_lot == 60 || $dateParklot->order_lot == 61)
						$used_pg += $dateParklot->used;
				}
				
				if($con_pg != 0){
					if(number_format($used_pg / $con_pg * 100, 2,".",".") >= 70 && number_format($used_pg / $con_pg * 100, 2,".",".") < 85)
						$text_color = "purple";
					elseif(number_format($used_pg / $con_pg * 100, 2,".",".") >= 85)
						$text_color = "red";										
					else
						$text_color = "";
				}
				else
					$text_color = "";
				if(number_format($used_pg / $con_pg * 100, 2,".",".") == 100)
					$background = "green";
				elseif($used_pg > $con_pg)
					$background = "yellow";
				else
					$background = "";
				?>
				<td><?php echo $used_pg ?></td>
				<td class="<?php echo $text_color ?> <?php echo $background ?>"><?php echo $con_pg != 0 ? number_format($used_pg / $con_pg * 100, 2, ",", ".") : "0"?></td>
			</tr>
			<tr>
				<td>Plieningen PLI</td>
				<?php
					$parklot = Database::getInstance()->getParklotByProductId(80567);
					if($set_con[date('Y-m-d')."_80567"] != null)
						$con_pli += $set_con[date('Y-m-d')."_80567"];
					else
						$con_pli += $parklot->contigent;
					$parklot = Database::getInstance()->getParklotByProductId(82130);
					if($set_con[date('Y-m-d')."_82130"] != null)
						$con_pli += $set_con[date('Y-m-d')."_82130"];
					else
						$con_pli += $parklot->contigent;
				?>
				<td><?php echo $con_pli ?></td>
				<?php
				foreach ($dateParklots as $dateParklot){
					if($dateParklot->order_lot == 70 || $dateParklot->order_lot == 71 || $dateParklot->order_lot == 72)
						$used_pli += $dateParklot->used;
				}
				
				if($con_pli != 0){
					if(number_format($used_pli / $con_pli * 100, 2,".",".") >= 70 && number_format($used_pli / $con_pli * 100, 2,".",".") < 85)
						$text_color = "purple";
					elseif(number_format($used_pli / $con_pli * 100, 2,".",".") >= 85)
						$text_color = "red";										
					else
						$text_color = "";
				}
				else
					$text_color = "";
				if(number_format($used_pli / $con_pli * 100, 2,".",".") == 100)
					$background = "green";
				elseif($used_pli > $con_pli)
					$background = "yellow";
				else
					$background = "";
				?>
				<td><?php echo $used_pli ?></td>
				<td class="<?php echo $text_color ?> <?php echo $background ?>"><?php echo $con_pli != 0 ? number_format($used_pli / $con_pli * 100, 2, ",", ".") : "0"?></td>
			</tr>
			<tr>
				<?php $sum_con = $con_ph + $con_od + $con_sie + $con_ost + $con_nu + $con_pg + $con_pli; ?>
				<?php $sum_used = $used_ph + $used_od + $used_sie + $used_ost + $used_nu + $used_pg + $used_pli; ?>
				<?php
				if($sum_con != 0){
					if(number_format($sum_used / $sum_con * 100, 2,".",".") >= 70 && number_format($sum_used / $sum_con * 100, 2,".",".") < 85)
						$text_color = "purple";
					elseif(number_format($sum_used / $sum_con * 100, 2,".",".") >= 85)
						$text_color = "red";										
					else
						$text_color = "";
				}
				else
					$text_color = "";
				if(number_format($sum_used / $sum_con * 100, 2,".",".") == 100)
					$background = "green";
				elseif($sum_used > $sum_con)
					$background = "yellow";
				else
					$background = "";
				?>
				<td><strong>Summe</strong></td>
				<td><strong><?php echo $sum_con ?></strong></td>
				<td><strong><?php echo $sum_used ?></strong></td>
				<td class="<?php echo $text_color ?> <?php echo $background ?>"><strong><?php echo $sum_con != 0 ? number_format($sum_used / $sum_con * 100, 2, ",", ".") : "0"?></strong></td>
			</tr>
		</tbody>
	</table>


<?php
$free_ph = $con_ph - $used_ph;
if($free_ph <= 0)
	$free_ph = 0;
$free_od = $con_od - $used_od;
if($free_od <= 0)
	$free_od = 0;
$free_sie = $con_sie - $used_sie;
if($free_sie <= 0)
	$free_sie = 0;
$free_ost = $con_ost - $used_ost;
if($free_ost <= 0)
	$free_ost = 0;
$free_nu = $con_nu - $used_nu;
if($free_nu <= 0)
	$free_nu = 0;
$free_pg = $con_pg - $used_pg;
if($free_pg <= 0)
	$free_pg = 0;
$free_pli = $con_pli - $used_pli;
if($free_pli <= 0)
	$free_pli = 0;

$data_diagramm = array(
	array('PH', $used_ph, $free_ph),
	array('OD', $used_od, $free_od),
	array('SIE', $used_sie, $free_sie),
	array('OST', $used_ost, $free_ost),
	array('NÜ', $used_nu, $free_nu),
	array('PG', $used_pg, $free_pg),
	array('PLI', $used_pg, $free_pli)
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
