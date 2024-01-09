<?php
require_once 'icalendar.php';
global $wpdb;

if (isset($_GET['month']) && $_GET['month'] < 10)
    $zero = '0';
else
    $zero = '';

if (isset($_GET["month"])){
	$c_month = $_GET["month"];
	$con_month = $zero.$_GET["month"];
}
    
else{
	 $c_month = date('n');
	 $con_month = date('m');
}
   
if (isset($_GET["year"]))
    $c_year = $_GET["year"];
else
    $c_year = date('Y');

$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $c_month, $c_year);

if(isset($_GET['ps'])){
	$product = $_GET['product'];
	$price = $_GET['price'];
	$date = $_GET['date'];
	if(isset($date)){
		$date = (explode(" - ",$date));
		$date[0] = date('Y-m-d', strtotime($date[0]));
		$date[1] = date('Y-m-d', strtotime($date[1]));
		
		$name = $wpdb->get_row("SELECT name FROM " . $wpdb->prefix . "itweb_prices WHERE id = " . $price);
		$name->name = str_replace(" ", "%20", $name->name);
		
		while($date[0] <= $date[1]){
			Database::getInstance()->addEventsFast($date[0], $product, $price);				 
			
			$url = "https://airport-parking-germany.de/curl/?request=apm_save_cal&pw=apmcal_req57159428&p_name=".$name->name."&product_id=".$product."&datefrom=".$date[0]."&dateto=".$date[0];
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, 0);
				// Receive server response ...
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
				$server_output = curl_exec($ch);
				curl_close($ch);
			
			$date[0] = date('Y-m-d', strtotime($date[0]. ' + 1 days'));
		}			
	}
	header('Location: /wp-admin/admin.php?page=preisschienen&year='.$c_year);
}
if(isset($_GET['con'])){
	$sql_p_id = $_GET['product'];
	$val = $_GET['ammount'];
	$date = date('Y-m-d', strtotime($_GET['date']));
	Database::getInstance()->saveContingent($date, $sql_p_id, $val);
	//Database::getInstance()->setLotsContingent($lot_id, $ammount);
	header('Location: /wp-admin/admin.php?page=preisschienen&year='.$c_year);
}


$clients = Database::getInstance()->getAllClients();
$prices = Database::getInstance()->getPricesByYear($c_year);

$months = array('1' => 'Januar', '2' => 'Fabruar', '3' => 'März', '4' => 'April', '5' => 'Mai', '6' => 'Juni',
    '7' => 'Juli', '8' => 'August', '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Dezember');
$years = array('2021' => '2021', '2022' => '2022', '2023' => '2023', '2024' => '2024', '2025' => '2025');
$wochentage = array("So.", "Mo.", "Di.", "Mi.", "Do.", "Fr.", "Sa.");


$currentMonth = (int)date('n');

$date_con[0] = $c_year.'-'.$con_month.'-01';
$date_con[1] = $c_year.'-'.$con_month.'-'.cal_days_in_month(CAL_GREGORIAN,$c_month,$c_year);
$allContingent = Database::getInstance()->getAllContingent($date_con);

foreach($allContingent as $ac){
	$set_con[$ac->date."_".$ac->product_id] = $ac->contingent;
}

$feiertage = array();
$ical = new iCalendar();
$filename = ABSPATH . 'wp-content/uploads/feiertage/ferien_baden-wuerttemberg_'.$c_year.'.ics';
$ical->parse("$filename");
$ical_data = $ical->get_all_data();

foreach ($ical_data['VEVENT'] as $key => $data) {
	//get StartDate And StartTime
	$start_dttimearr = explode('T', $data['DTSTART']);
	$StartDate = $start_dttimearr[0];
	$startTime = $start_dttimearr[1];
	//get EndDate And EndTime
	$end_dttimearr = explode('T', $data['DTEND']);
	$EndDate = $end_dttimearr[0];
	$EndTime = $end_dttimearr[1];
	$titel = $data['SUMMARY'];

	$output[0] = substr( $StartDate, 0, 4);
	$output[1] = substr( $StartDate, 4, 2);
	$output[2] = substr( $StartDate, 6, 2);
	$StartDate = date('Y-m-d', strtotime($output[0] . '-' . $output[1] . '-' . $output[2]));

	$output[0] = substr( $EndDate, 0, 4);
	$output[1] = substr( $EndDate, 4, 2);
	$output[2] = substr( $EndDate, 6, 2);
	$EndDate = date('Y-m-d', strtotime($output[0] . '-' . $output[1] . '-' . $output[2]));
	
	//echo $StartDate . " " . $EndDate . "<br>";
	while($StartDate != date('Y-m-d', strtotime($EndDate . '+0 day'))){
		$feiertage[date('d', strtotime($StartDate))."-".date('n', strtotime($StartDate))."-".date('Y', strtotime($StartDate))] = $titel;
		
		$StartDate = date('Y-m-d', strtotime($StartDate . '+1 day'));
	}
}

$filename = ABSPATH . 'wp-content/uploads/feiertage/gesetzliche_feiertage_baden-wuerttemberg_'.$c_year.'.ics';
$ical->parse("$filename");
$ical_data = $ical->get_all_data();

foreach ($ical_data['VEVENT'] as $key => $data) {
	//get StartDate And StartTime
	$start_dttimearr = explode('T', $data['DTSTART']);
	$StartDate = $start_dttimearr[0];
	$startTime = $start_dttimearr[1];
	//get EndDate And EndTime
	$end_dttimearr = explode('T', $data['DTEND']);
	$EndDate = $end_dttimearr[0];
	$EndTime = $end_dttimearr[1];
	$titel = $data['SUMMARY'];

	$output[0] = substr( $StartDate, 0, 4);
	$output[1] = substr( $StartDate, 4, 2);
	$output[2] = substr( $StartDate, 6, 2);
	$StartDate = date('Y-m-d', strtotime($output[0] . '-' . $output[1] . '-' . $output[2]));

	$output[0] = substr( $EndDate, 0, 4);
	$output[1] = substr( $EndDate, 4, 2);
	$output[2] = substr( $EndDate, 6, 2);
	$EndDate = date('Y-m-d', strtotime($output[0] . '-' . $output[1] . '-' . $output[2]));
	
	//echo $StartDate . " " . $EndDate . "<br>";
	while($StartDate != date('Y-m-d', strtotime($EndDate . '+0 day'))){
		$feiertage[date('d', strtotime($StartDate))."-".date('n', strtotime($StartDate))."-".date('Y', strtotime($StartDate))] = $titel;
		
		$StartDate = date('Y-m-d', strtotime($StartDate . '+1 day'));
	}
}

//echo "<pre>"; print_r($feiertage); echo "</pre>";
?>
<style>
th, td{
	white-space: nowrap;
	border-right: 1px solid black
}

.table_div {
  width: 100%;
  overflow: scroll;
  position: relative;
}

table{
	border-top: 3px solid black;
	border-bottom: 3px solid black
}

th:first-child, td:first-child {
  position: sticky;
  left: 0;
  z-index: 2;
  background-clip: padding-box;
}

.head{
	background: ffffff;
}

.feiertag{
	background: #99ccff;
}

[data-title]:hover:after {
    opacity: 1;
    transition: all 0.1s ease 0.5s;
    visibility: visible;
}
[data-title]:after {
    content: attr(data-title);
    background-color: #99ccff;
    color: #111;
    font-size: 100%;
    position: absolute;
    padding: 1px 5px 2px 5px;
    bottom: -2em;
    left: 85%;
    white-space: nowrap;
    box-shadow: 1px 1px 3px #222222;
    opacity: 0;
    border: 1px solid #111111;
    z-index: 99999;
    visibility: hidden;
}
[data-title] {
    position: -webkit-sticky; /* for Safari */
    position: sticky;
}
</style>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Preisschienen</h3>
    </div>
    <div class="page-body">      
		<div class="row">
			<div class="col-sm-12 col-md-4">
				<div class="row ui-lotdata-block ui-lotdata-block-next">
					<h5 class="ui-lotdata-title">Monat filtern</h5>
					<div class="col-sm-12 col-md-12 ui-lotdata">
						<form class="form-filter">
							<div class="row">
								<div class="col-sm-12 col-md-4">
									<input type="hidden" value="<?php echo $salesFor ?>" class="salesFor">
									<input type="hidden" value="<?php echo isset($_GET['month']) ? $zero . $_GET['month'] : date('m'); echo "."; echo isset($_GET['year']) ? $_GET['year'] : date('Y'); ?>" class="salesDate">
									<select name="month" class="form-item form-control">
										<?php foreach ($months as $key => $value) : ?>
											<option value="<?php echo $key ?>" <?php echo $key == $c_month ? ' selected' : '' ?>>
												<?php echo $value ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="col-sm-12 col-md-4">
									<select name="year" class="form-item form-control">
										<?php for ($i = 2023; $i <= date('Y')+1; $i++) : ?>
											<option value="<?php echo $i ?>" <?php echo $i == $c_year ? ' selected' : '' ?>>
												<?php echo $i ?>
											</option>
										<?php endfor; ?>
									</select>
								</div>
								<div class="col-sm-12 col-md-3">
									<button class="btn btn-primary d-block w-100" type="submit">Filter</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>		
		<div class="row">
			<div class="col-sm-12 col-md-6">
				<div class="row ui-lotdata-block ui-lotdata-block-next">
					<h5 class="ui-lotdata-title">Preisschiene zuweisen</h5>
					<div class="col-sm-12 col-md-12 ui-lotdata">
						<form class="form-filter">
							<div class="row">
								<div class="col-sm-12 col-md-4">
									<input type="text" class="datepicker-range form-item form-control date" name="date" data-multiple-dates-separator=" - " placeholder="Datum von - bis">
								</div>
								<div class="col-sm-12 col-md-3">
									<select name="product" class="form-item form-control price">
										<option value="">Produkt</option>
										<?php foreach($clients as $client): ?>
										<?php $client_products = Database::getInstance()->getClientProducts($client->id); ?>
											<?php foreach($client_products as $p): ?>
											<?php $product = Database::getInstance()->getParklotByProductId($p->product_id); ?>
											<option value="<?php echo $product->product_id ?>">
												<?php echo $product->parklot_short ?>
											</option>
											<?php endforeach; ?>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="col-sm-12 col-md-3">
									<select name="price" class="form-item form-control price">
										<option value="">Preisschiene</option>
										<?php foreach($prices as $price) : ?>
											<option value="<?php echo $price->id ?>">
												<?php echo $price->name ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="col-sm-12 col-md-2">
									<button class="btn btn-primary d-block w-100 form-item form-control" type="submit" name="ps" value="1" >Eintrag</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
			<div class="col-sm-12 col-md-6">
				<div class="row ui-lotdata-block ui-lotdata-block-next">
					<h5 class="ui-lotdata-title">Kontingent zuweisen</h5>
					<div class="col-sm-12 col-md-12 ui-lotdata">
						<form class="form-filter">
							<div class="row">
								<div class="col-sm-12 col-md-3 col-lg-3">
									<input type="text" name="date" placeholder="Datum" class="form-item form-control single-datepicker">
								</div>
								<div class="col-sm-12 col-md-3">
									<select name="product" class="form-item form-control price">
										<option value="">Produkt</option>
										<?php foreach($clients as $client): ?>
										<?php $client_products = Database::getInstance()->getClientProducts($client->id); ?>
											<?php foreach($client_products as $p): ?>
											<?php $product = Database::getInstance()->getParklotByProductId($p->product_id); ?>
											<option value="<?php echo $product->product_id ?>">
												<?php echo $product->parklot_short ?>
											</option>
											<?php endforeach; ?>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="col-sm-12 col-md-3">
									<input type="number" class="form-item form-control" name="ammount">
								</div>
								<div class="col-sm-12 col-md-2">
									<button class="btn btn-primary d-block w-100 form-item form-control" type="submit" name="con" value="1">Eintrag</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12 col-md-12">
				<div class="table_div">	
					<table class="table table-sm" id="">
						<thead>
							<tr>
								<th style="background-color: #ffffff">Produkt</th>
								<?php for($hm = 1; $hm <= cal_days_in_month(CAL_GREGORIAN,$c_month,$c_year); $hm++): ?>
								<?php 
								$day = $hm < 10 ? "0". $hm : $hm;
								$month = $c_month < 10 ? "0". $c_month : $c_month;
								$wt = $wochentage[date("w", strtotime($day.".".$month.".".$c_year))];
								
								if($feiertage[$day."-".$c_month."-".$c_year] != null)
									$th_css = 'feiertag';
								else
									$th_css = 'head';
								
								?>
								<?php if($th_css == "feiertag"): ?>
								<th data-title="<?php echo $feiertage[$day."-".$c_month."-".$c_year]?>" class="<?php echo $th_css ?>" style="<?php echo $wt == "Mo." ? "border-left: 3px solid black" : "" ?>"><?php echo $wt . " " . $hm . "." ?></th>
								<?php else: ?>
								<th style="background-color: #ffffff; <?php echo $wt == "Mo." ? "border-left: 3px solid black" : "" ?>"><?php echo $wt . " " . $hm . "." ?></th>
								<?php endif; ?>
								<?php endfor; ?>
							</tr>
						</thead>
						<tbody>
							<?php foreach($clients as $client): ?>
							<?php $client_products = Database::getInstance()->getClientProducts($client->id); ?>
								<?php foreach($client_products as $p): ?>
								<?php 
								$product = Database::getInstance()->getParklotByProductId($p->product_id);
								$apg_shortName = str_replace("APS", "APG", $product->parklot_short);
								$apg_product = Database::getInstance()->getParklotByShortName($apg_shortName);
								?>
								<tr style="background-color: <?php echo $product->color ?>">
									<td style="background-color: <?php echo $product->color ?>"><?php echo $product->parklot_short ?></td>
									<?php for($hm = 1; $hm <= cal_days_in_month(CAL_GREGORIAN,$c_month,$c_year); $hm++): 
										$day = $hm < 10 ? "0". $hm : $hm;
										$month = $c_month < 10 ? "0". $c_month : $c_month;
										$wt = $wochentage[date("w", strtotime($day.".".$month.".".$c_year))];
										
										$cont = Database::getInstance()->getParkotsWithOrdersDataAndProductID($c_year.'-'.$c_month.'-'.$day, $product->product_id);
										$apg_cont = Database::getInstance()->getParkotsWithOrdersDataAndProductID($c_year.'-'.$c_month.'-'.$day, $apg_product->product_id);
										$cont->used += $apg_cont->used;
										$usedGesDay[$hm] += $cont->used;
										$contingent = $set_con[$c_year."-".$month."-".$day."_".$product->product_id] != null ? $set_con[$c_year."-".$month."-".$day."_".$product->product_id] : $product->contigent;
										$contGes[$hm] += $contingent;
									?>					
									<td style="background-color: <?php echo $product->color ?>; color: <?php echo $contingent <=  $cont->used ? 'red' : '' ?>; <?php echo $wt == "Mo." ? "border-left: 3px solid black" : "" ?>"><?php echo $contingent . " / " . $cont->used; ?></td>
									<?php endfor; ?>
								</tr>
								<tr style="background-color: <?php echo $product->color ?>">
									<td style="background-color: <?php echo $product->color ?>">PS</td>
									<?php for($hm = 1; $hm <= cal_days_in_month(CAL_GREGORIAN,$c_month,$c_year); $hm++): 
										$day = $hm < 10 ? "0". $hm : $hm;
										$month = $c_month < 10 ? "0". $c_month : $c_month;
										$wt = $wochentage[date("w", strtotime($day.".".$month.".".$c_year))];
										$ps= Database::getInstance()->getPriceByDateAndProductID($c_year.'-'.$c_month.'-'.$day, $product->product_id);
									?>					
									<td style="<?php echo $wt == "Mo." ? "border-left: 3px solid black" : "" ?>"><?php echo$ps->name; ?></td>
									<?php endfor; ?>
								</tr>
								<tr><td style="border: none"></td></tr>
								<?php endforeach; ?>
							<?php endforeach; ?>
							<tr>
								<td style="background-color: #ffffff"></td>
								<?php for($hm = 1; $hm <= cal_days_in_month(CAL_GREGORIAN,$c_month,$c_year); $hm++): ?>
								<?php
										$day = $hm < 10 ? "0". $hm : $hm;
										$month = $c_month < 10 ? "0". $c_month : $c_month;
										$wt = $wochentage[date("w", strtotime($day.".".$month.".".$c_year))];
								?>
								<td style="background-color: #ffffff; <?php echo $wt == "Mo." ? "border-left: 3px solid black" : "" ?>"><strong><?php echo $contGes[$hm] . " / " . $usedGesDay[$hm] ?></strong></td>
								<?php endfor; ?>
							</tr>
							<tr>
								<td style="background-color: #ffffff">Prozent</td>
								<?php for($hm = 1; $hm <= cal_days_in_month(CAL_GREGORIAN,$c_month,$c_year); $hm++): ?>
								<?php
										$day = $hm < 10 ? "0". $hm : $hm;
										$month = $c_month < 10 ? "0". $c_month : $c_month;
										$wt = $wochentage[date("w", strtotime($day.".".$month.".".$c_year))];
								?>
								<td style="background-color: #ffffff; <?php echo $wt == "Mo." ? "border-left: 3px solid black" : "" ?>"><?php echo $contGes[$hm] != 0 ? number_format($usedGesDay[$hm] * 100 / $contGes[$hm], 2, ",", ".") : "0"; echo "%" ?></td>
								<?php endfor; ?>
							</tr>
								<td style="background-color: #ffffff; border-bottom: 3px solid black; "><strong>Tag</strong></td>
								<?php for($hm = 1; $hm <= cal_days_in_month(CAL_GREGORIAN,$c_month,$c_year); $hm++): ?>
								<?php
										$day = $hm < 10 ? "0". $hm : $hm;
										$month = $c_month < 10 ? "0". $c_month : $c_month;
										$wt = $wochentage[date("w", strtotime($day.".".$month.".".$c_year))];
									
										if($feiertage[$day."-".$c_month."-".$c_year] != null)
											$th_css = 'feiertag';
										else
											$th_css = 'head';
								?>
								<?php if($th_css == "feiertag"): ?>
								<th data-title="<?php echo $feiertage[$day."-".$c_month."-".$c_year]?>" class="<?php echo $th_css ?>" style="<?php echo $wt == "Mo." ? "border-left: 3px solid black" : "" ?>"><?php echo $wt . " " . $hm . "." ?></th>
								<?php else: ?>
								<th style="background-color: #ffffff; <?php echo $wt == "Mo." ? "border-left: 3px solid black" : "" ?>"><?php echo $wt . " " . $hm . "." ?></th>
								<?php endif; ?>
								<?php endfor; ?>
							<tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>