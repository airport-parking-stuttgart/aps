<?php
require_once 'icalendar.php';
$db = Database::getInstance();

$current_user = wp_get_current_user();
$mitarbeiter = $db->getActivUser_einsatzplan();

$grund_array = array('U' => 'Urlaub', 'SU' => 'Sonderurlaub', 'F' => 'Freistellung', 'BF' => 'Bezahlte Freistellung', 'K' => 'Krank 1', 'KK' => 'Krank 2', 'UF' => 'Überstundenfrei',
				'BFS' => 'Berufsschule', 'FB' => 'Fortbildung', 'GR' => 'Geschäftsreise', 'X' => 'Sperren', 'DEL' => 'Löschen');

$wochentage = array("So.", "Mo.", "Di.", "Mi.", "Do.", "Fr.", "Sa.");
$months = array('1' => 'Januar', '2' => 'Fabruar', '3' => 'März', '4' => 'April', '5' => 'Mai', '6' => 'Juni',
				'7' => 'Juli', '8' => 'August', '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Dezember');
if (isset($_GET['month']) && $_GET['month'] < 10)
    $zero = '0';
else
    $zero = '';

if (isset($_GET["month"]))
    $c_month = $_GET["month"];
else
    $c_month = date('n');
if (isset($_GET["year"]))
    $c_year = $_GET["year"];
else
    $c_year = date('Y');
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $c_month, $c_year);

if (isset($_GET["filter"]))
    $filter = $_GET["filter"];
else
    $filter = '';

if(isset($_GET['filter'])){
	$set_filter = "&filter=" . $_GET['filter'];
}
else
	$set_filter = "";

if(isset($_GET['role'])){
	$role = "&role=" . $_GET['role'];
}
else
	$role = "";

if($c_month == 1){
	$prev_year = $c_year - 1;
	$prev_month = 12;	
	$next_year = $c_year;
	$next_month = $c_month + 1;
}
elseif($c_month == 12){
	$prev_year = $c_year;
	$prev_month = $c_month - 1;
	$next_year = $c_year + 1;
	$next_month = 1;
}
else{
	$prev_year = $c_year;
	$prev_month = $c_month - 1;
	$next_year = $c_year;
	$next_month = $c_month + 1;
}

if(isset($_POST)){
	if($_POST['btn'] == 1){
		unset($_POST['btn']);
				
		foreach($_POST as $key => $val){
			$db->delete_urlaubsstunden($key, $c_year);
			$db->addUrlaubsstunden($key, $c_year, $val);
		}
		
	}
}
$urlaubsstd = $db->getUrlaubsstunden($c_year);
$urlaubsstd_LJ = $db->getUrlaubsstunden($c_year-1);

foreach($urlaubsstd as $urlstd){
	$urlaubsstunden[$urlstd->user_id] = $urlstd->stunden;
}
foreach($urlaubsstd_LJ as $urlstd){
	$urlaubsstunden_LJ[$urlstd->user_id] = $urlstd->stunden;
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

if(isset($_GET['date_from']) && isset($_GET['date_to'])){
	$date_from = date('Y-m-d', strtotime($_GET['date_from']));
    $date_to = date('Y-m-d', strtotime($_GET['date_to']));
    $period = new DatePeriod(
        new DateTime($date_from),
        new DateInterval('P1D'),
        new DateTime($date_to . '+1 day')
    );
	foreach ($period as $key => $value){
		$date = $value->format('Y-m-d');
		$wochentag = str_replace(".", "", strtolower($wochentage[date("w", strtotime($date))]));
		$kw = date("W", strtotime($date));
		$data['kw'] = $kw;
		$data['year'] = $value->format('Y');
		$data['wochentag'] = $wochentag;
		$data['eintrag'] = "X";
		//foreach($mitarbeiter as $ma){
		//	$data['user_id'] = $ma->user_id;
		//	$db->add_urlaubsplanung($data);			
		//}
		if($_GET['sperre'] == 1)
			$db->add_urlaubsplanung_sperre($data);
		elseif($_GET['sperre'] == 2)
			$db->del_urlaubsplanung_sperre($data);
		
		//header("Refresh:0; url=admin.php?page=urlaubsplan");
	}
}


//echo "<pre>"; print_r($c_year); echo "</pre>";

?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>  
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>  
<style>

.head{
	background:aquamarine;
}

.color_line {
  background: #ffcc99;
}
.color_ad{
	background: #00ccff !important;
}
.color_office{
	background: #00ff99 !important;
}
.color_kordi{
	background: #ffff99 !important;
}
.border_left{
	border-left: 1px solid #606060;
}

.U{
	background: #ccffcc;
}
.SU{
	background: #c3fc6a;
}
.BFS{
	background: #ccffff;
}
.F{
	background: #ccccff;
}
.BF{
	background: #8c8cff;
}
.K, .KK{
	background: #ffcccc;
}
.UF{
	background: #ffa1dc;
}
.FB{
	background: #a1fff9;
}
.GR{
	background: #f6fcbb;
}
.X{
	background: #ff4d4d;
}

.wochenende{
	background: #e6e6e6;
}

.feiertag{
	background: #99ccff;
}

.filter_table{
	background: none
}
.text_table{
	visibility: hidden;
}

.btn-submit{
	font-size: 23px;
}


.table_div {
  width: 100%;
  max-height: 800px;
  overflow: scroll;
  position: relative;
}

table {
  position: relative;
  border-collapse: collapse;
}

td, th {
  padding: 0.25em;
  border: 2px solid #c1c1c1; cursor: pointer; white-space: nowrap;
}

thead th {
  position: -webkit-sticky; /* for Safari */
  position: sticky;
  top: 0;
  background: aquamarine;
  color: #000;
}

thead th:first-child {
  left: 0;
  z-index: 1;
}

tbody th {
  position: -webkit-sticky; /* for Safari */
  position: sticky;
  left: 0;
  background: aquamarine;
}




#context-menu {
        position: fixed;
        z-index: 10000;
        width: 150px;
        background: #fff;
        border-radius: 5px;
		border: 2px solid #1e73be;
        transform: scale(0);
        transform-origin: top left;
      }

      #context-menu.visible {
        transform: scale(1);
        transition: transform 200ms ease-in-out;
      }

      #context-menu .item {
        padding: 8px 10px;
        font-size: 15px;
        color: #000;
        cursor: pointer;
        border-radius: inherit;
      }

      #context-menu .item:hover {
        background: #1e73be;
		color: #fff;
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


<div id="context-menu">
	<?php foreach($grund_array as $key => $val): ?>	
	<?php if($key == 'X') continue; ?>
  <div class="item" data-grund="<?php echo $key ?>" ><?php echo $val ?></div>
  <?php endforeach; ?>
</div>


<div class="page container-fluid <?php echo $_GET['page'] ?>">

    <div class="page-title itweb_adminpage_head">
		<h3>Urlaubsplan</h3>
	</div>
	<div class="page-body">
		<div class="row ui-lotdata-block ui-lotdata-block-next">
			<h5 class="ui-lotdata-title">Monat anzeigen</h5>
			<div class="col-sm-12 col-md-12 ui-lotdata">						
				<details id='1'>
					<summary>Datum | <a href="<?php echo '/wp-admin/admin.php?page=urlaubsplan&month='.$prev_month.'&year='.$prev_year . $set_filter . $role ?>" class="btn btn-primary"><</a> 
					<span class="btn btn-primary"><?php echo $months[$c_month] . " " . $c_year ?> </span>
					<a href="<?php echo '/wp-admin/admin.php?page=urlaubsplan&month='.$next_month.'&year='.$next_year . $set_filter . $role ?>" class="btn btn-primary">></a>
					| MA Gruppe
					<select name="role" id="year" onchange="change_role(this)">						
						<option value="all" <?php echo $_GET['role'] == "all" ? ' selected' : '' ?>>Alle</option>
						<option value="buro" <?php echo $_GET['role'] == "buro" ? ' selected' : '' ?>>Büro</option>
						<option value="fahrer" <?php echo $_GET['role'] == "fahrer" ? ' selected' : '' ?>>Fahrer</option>
					</select>
					</summary>
					<br>
					<div class="row">
						<div class="col-sm-12 col-md-12">
							<select name="year" id="year" onchange="change_year(this)">
								<?php for ($i = 2021; $i <= date('Y') + 1; $i++) : ?>
									<option value="<?php echo $i ?>" <?php echo $i == $c_year ? ' selected' : '' ?>>
										<?php echo $i ?>
									</option>
								<?php endfor; ?>
							</select>
							<a href="<?php echo '/wp-admin/admin.php?page=urlaubsplan&month=1&year='.$c_year . $set_filter . $role ?>" class="btn <?php echo $c_month == 1 ? 'btn-primary' : 'btn-secondary' ?>" >Januar</a>
							<a href="<?php echo '/wp-admin/admin.php?page=urlaubsplan&month=2&year='.$c_year . $set_filter . $role ?>" class="btn <?php echo $c_month == 2 ? 'btn-primary' : 'btn-secondary' ?>" >Februar</a>
							<a href="<?php echo '/wp-admin/admin.php?page=urlaubsplan&month=3&year='.$c_year . $set_filter . $role ?>" class="btn <?php echo $c_month == 3 ? 'btn-primary' : 'btn-secondary' ?>" >März</a>
							<a href="<?php echo '/wp-admin/admin.php?page=urlaubsplan&month=4&year='.$c_year . $set_filter . $role ?>" class="btn <?php echo $c_month == 4 ? 'btn-primary' : 'btn-secondary' ?>" >April</a>
							<a href="<?php echo '/wp-admin/admin.php?page=urlaubsplan&month=5&year='.$c_year . $set_filter . $role ?>" class="btn <?php echo $c_month == 5 ? 'btn-primary' : 'btn-secondary' ?>" >Mai</a>
							<a href="<?php echo '/wp-admin/admin.php?page=urlaubsplan&month=6&year='.$c_year . $set_filter . $role ?>" class="btn <?php echo $c_month == 6 ? 'btn-primary' : 'btn-secondary' ?>" >Juni</a>
							<a href="<?php echo '/wp-admin/admin.php?page=urlaubsplan&month=7&year='.$c_year . $set_filter . $role ?>" class="btn <?php echo $c_month == 7 ? 'btn-primary' : 'btn-secondary' ?>" >Juli</a>
							<a href="<?php echo '/wp-admin/admin.php?page=urlaubsplan&month=8&year='.$c_year . $set_filter . $role ?>" class="btn <?php echo $c_month == 8 ? 'btn-primary' : 'btn-secondary' ?>" >August</a>
							<a href="<?php echo '/wp-admin/admin.php?page=urlaubsplan&month=9&year='.$c_year . $set_filter . $role ?>" class="btn <?php echo $c_month == 9 ? 'btn-primary' : 'btn-secondary' ?>" >Sepember</a>
							<a href="<?php echo '/wp-admin/admin.php?page=urlaubsplan&month=10&year='.$c_year . $set_filter . $role ?>" class="btn <?php echo $c_month == 10 ? 'btn-primary' : 'btn-secondary' ?>" >Oktober</a>
							<a href="<?php echo '/wp-admin/admin.php?page=urlaubsplan&month=11&year='.$c_year . $set_filter . $role ?>" class="btn <?php echo $c_month == 11 ? 'btn-primary' : 'btn-secondary' ?>" >November</a>
							<a href="<?php echo '/wp-admin/admin.php?page=urlaubsplan&month=12&year='.$c_year . $set_filter . $role ?>" class="btn <?php echo $c_month == 12 ? 'btn-primary' : 'btn-secondary' ?>" >Dezember</a>
						</div>
					</div>
				</details>
				<br>
				<details id='2'>
					<summary>Anzeigen | <?php echo isset($_GET['filter']) ? $grund_array[$_GET['filter']] : "Alles" ?></summary>
						<div class="row">
							<div class="col-sm-12 col-md-12">
								<span class="btn <?php echo $filter == "" ? 'btn-primary' : 'btn-secondary' ?>" onclick="filter('')">Alles</span>								
								<?php foreach($grund_array as $key => $val): ?>						
									<?php if($current_user->user_login != 'aras' && $key == 'X') continue; ?>
									<?php if($key == 'DEL' || $key == 'X') continue; ?>								
									<span href="" class="btn <?php echo $filter == $key ? 'btn-primary' : 'btn-secondary' ?>" onclick="filter('<?php echo $key ?>')"><?php echo $val ?></span>								
								<?php endforeach; ?>
							</div>
						</div>
				</details>
			</div>
		</div>
		<form class="form-filter">
			<div class="row">
				<div class="col-sm-12 col-md-3">
					Eintragen: 
					<select name="grund" id="grund">
						<?php foreach($grund_array as $key => $val): ?>						
							<?php if($key == 'X') continue; ?>
							<option value="<?php echo $key ?>"><?php echo $val ?></option>						
						<?php endforeach; ?>
					</select>
				</div>
				<?php if($current_user->user_login == 'aras'): ?>
				<div class="col-sm-12 col-md-3 col-lg-1 ui-lotdata-date">
					<input type="text" id="dateFrom" name="date_from" placeholder="Datum von" class="form-item form-control single-datepicker" value="<?php echo $datefrom != "" ? date('d.m.Y', strtotime($datefrom)) : ''; ?>">
				</div>
				<div class="col-sm-12 col-md-3 col-lg-1 ui-lotdata-date">
					<input type="text" id="dateTo" name="date_to" placeholder="Datum bis" class="form-item form-control single-datepicker" value="<?php echo $dateto != "" ? date('d.m.Y', strtotime($dateto)) : ''; ?>">
				</div>
				<div class="col-sm-12 col-md-1 col-lg-1">
					<select name="sperre"  class="form-item form-control">
						<option value="1">sperren</option>
						<option value="2">entsperren</option>
					</select>
				</div>
				<div class="col-sm-12 col-md-1 col-lg-1">
					<button class="btn btn-primary d-block w-100" type="submit">Eintragen</button>
				</div>
				<?php endif; ?>
			</div>
		</form>
		<br>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?page=urlaubsplan&month='.$c_month.'&year='.$c_year . $set_filter . $role; ?>">
			<div class="row">
				<div class="col-sm-12 col-md-12">
					<button class="btn btn-primary" type="submit" name="btn" value="1">Soll-Tage Speichern</button><br><br>
					<div class="table_div">						
						<table class="table">
							<thead>
								<tr>
									<th>Mitarbeiter</th>
									<th>ÜVJ</th>
									<th>Soll</th>
									<th>Gpl.</th>
									<th>Rest</th>
									<?php for($i = 1; $i <= $daysInMonth; $i++): ?>
										<?php $day = $i < 10 ? "0" . $i : $i; ?>
										<?php $date = $c_year . "-" . $c_month . "-" . $day ?>
										<?php 											
											if($wochentage[date("w", strtotime($date))] == 'So.' || $wochentage[date("w", strtotime($date))] == 'Sa.')
												$th_css = 'wochenende';
											elseif($feiertage[$day."-".$c_month."-".$c_year] != null)
												$th_css = 'feiertag';
											else
												$th_css = 'head';

											$wochentag = str_replace(".", "", strtolower($wochentage[date("w", strtotime($date))]));
											$kw = date("W", strtotime($date));
											$sperre = $db->get_urlaubsplanung_sperre($kw, $c_year, $wochentag);
											$sperre = $sperre->$wochentag;
										?>
										<?php if($th_css == "feiertag"): ?>
											<th data-title="<?php echo $feiertage[$day."-".$c_month."-".$c_year]?>" class="<?php echo $sperre == null ? $th_css : "X" ?>"><?php echo $wochentage[date("w", strtotime($date))] . " " . $day ?></th>
										<?php else: ?>
											<th class="<?php echo $sperre == null ? $th_css : "X" ?>"><?php echo $wochentage[date("w", strtotime($date))] . " " . $day ?></th>
										<?php endif; ?>
									<?php endfor; ?>
								</tr>
							</thead>
							<tbody class="row_position_table1">							
								<?php $n = 1; foreach($mitarbeiter as $ma): ?>
									<?php
										if(isset($_GET['role'])){
											if($_GET['role'] == "buro" && $ma->role == "fahrer")
												continue;
											elseif($_GET['role'] == "fahrer" && $ma->role != "fahrer")
												continue;
										}
									?>
									
									<?php $wp_user = get_user_by('id', $ma->user_id); ?>
									<?php
										if($urlaubsstunden[$ma->user_id])
											$soll = $urlaubsstunden[$ma->user_id]; 
										else
											$soll = get_user_meta( $ma->user_id, 'urlaub', true ) != null ? get_user_meta( $ma->user_id, 'urlaub', true ) :  0;
										if($urlaubsstunden_LJ[$ma->user_id])
											$soll_LJ = $urlaubsstunden_LJ[$ma->user_id]; 
										else
											$soll_LJ = 0; 
									?>
									<?php $gaplant = $db->getUrlaubstage($ma->user_id, $c_year); ?>
									<?php $gaplant_LJ = $db->getUrlaubstage($ma->user_id, $c_year-1); ?>
									<?php $rest = $soll - $gaplant; ?>
									<?php
										if($c_year-1 < 2023)
											$rest_LJ = 0;
										else
											$rest_LJ = $soll_LJ - $gaplant_LJ; 
									?>
									<tr>
										<th><?php echo $n . ". " . $wp_user->display_name; ?></th>
										<td><?php echo $rest_LJ ?></td>
										<td><input type="text" name="<?php echo $ma->user_id ?>" size="2" value="<?php echo $soll; ?>"></td>
										<td id="<?php echo $ma->user_id ?>-geplant"><?php echo $gaplant ?></td>
										<td id="<?php echo $ma->user_id ?>-rest" style="<?php echo $gaplant > ($soll + $rest_LJ) && $rest <= 0 ? 'color: red;' : '' ?>"><?php echo $rest + $rest_LJ ?></td>
										<?php for($i = 1; $i <= $daysInMonth; $i++): ?>
										<?php $day = $i < 10 ? "0" . $i : $i; ?>
										<?php $date = $c_year . "-" . $c_month . "-" . $day ?>
										<?php $wochentag = str_replace(".", "", strtolower($wochentage[date("w", strtotime($date))])) ?>
										<?php $kw = date("W", strtotime($date)) ?>
										<?php $einsatzplan = $db->getEinsatzplanByUserIDandDay($kw, $c_year, $wochentag, $ma->user_id); ?>
										<?php $sperre = $db->get_urlaubsplanung_sperre($kw, $c_year, $wochentag); ?>
										<?php $sperre = $sperre->$wochentag ?>
										<?php if($filter != "" && $filter != $einsatzplan->$wochentag){
													if(($wochentag == 'so' || $wochentag == 'sa') && $sperre == null)
														$td_css = 'wochenende';
													elseif($sperre != null)
														$td_css = 'X';
													else
														$td_css = 'filter_table';
												}
												else{
													if(($wochentag == 'so' || $wochentag == 'sa') && $einsatzplan->$wochentag == null && $sperre == null){
														$td_css = 'wochenende';
													}
													elseif($sperre != null && $einsatzplan->$wochentag == null)
														$td_css = 'X';
													else
														$td_css = $einsatzplan->$wochentag;
												}
												if($wochentag == 'so'){
														$style = "border-right: 2.5px solid black !important";
													}
													else
														$style = "";
										?>
										<?php $td_css_test = $filter != "" && $filter != $einsatzplan->$wochentag ? 'text_table' : '';?>
										<?php $td_css_we = $wochentag == 'Sa' || $wochentag == 'So' ? 'wochenende' : ''; ?>									
											<td class="<?php echo $td_css; ?>" id="<?php echo $ma->user_id ?>-<?php echo $i ?>-<?php echo $c_month ?>-<?php echo $c_year ?>" style="text-align: center; <?php echo $style ?>"
											data-user_id="<?php echo $ma->user_id ?>"
											data-day="<?php echo $i ?>" 
											data-month="<?php echo $c_month ?>" 
											data-year="<?php echo $c_year ?>" 
											data-kw="<?php echo $kw ?>"										
											data-wochentag="<?php echo $wochentag ?>" 
											data-user_name="<?php echo $current_user->user_login ?>"
											data-closed_date="<?php echo $sperre ?>"
											onclick="set_left(this)" oncontextmenu="set_right(this)">
											<span class="<?php echo $td_css_test ?>"><?php echo $einsatzplan->$wochentag ?></span>
											</td>
										<?php endfor; ?>
									</tr>
								<?php $n++; endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<script>
/*
(function() {
$('details').on('toggle', function(event) {
var id = $(this).attr('id')
var isOpen = $(this).attr('open')
console.log(id, isOpen)
window.localStorage.setItem('details-'+id, isOpen)
  })

function setDetailOpenStatus(item) {
  if (item.includes('details-')) {
var id = item.split('details-')[1];
var status = window.localStorage.getItem(item)
if (status == 'open'){
  $("#"+id).attr('open',true)
 }
   }
}

$( document ).ready(function() {
  for (var i = 0; i < localStorage.length; i++) {
setDetailOpenStatus(localStorage.key(i));
  }
});
})();
*/

function change_year(e){
	var path = new URL(window.location.href);
	 path.searchParams.set('year', e.value);
	 location.href = path.href;
}

function change_role(e){
	var path = new URL(window.location.href);
	 path.searchParams.set('role', e.value);
	 location.href = path.href;
}

function filter(val){
	var path = new URL(window.location.href);
	if(val != ''){
		path.searchParams.set('filter', val);
		location.href = path.href;
	}
	else{
		path.searchParams.delete('filter');
		location.href = path.href;
	}
}

function set_left(e){
	click = 1;
	e.addEventListener('contextmenu', event => event.preventDefault());
	var helperUrl = '/wp-content/plugins/itweb-booking/classes/Helper.php';
	grund = document.getElementById("grund").value;
	user_id = e.dataset.user_id;
	day = e.dataset.day;
	month = e.dataset.month;
	year = e.dataset.year;
	t_day = day < 10 ? "0" + day : day;
	t_month = month < 10 ? "0" + month : month;
	date = year + "-" + t_month + "-" + t_day;
	kw = e.dataset.kw;
	wochentag = e.dataset.wochentag;
	closed_date = e.dataset.closed_date;
		
	if(click == 1){
		
		if(e.dataset.user_name != 'aras' && closed_date == 'X' && grund == "U")
			return false;
		
		if(grund == 'U' && grund != e.innerText && closed_date != 'X'){				
			field = e.innerText;
			field = field.replace(/\s+/g, '');
			e.className = '';								
			e.classList.add(grund);
			if(field != 'U' && (wochentag != 'sa' && wochentag != 'so')){
				geplant = document.getElementById(user_id + "-geplant").innerText;
				geplant++;
				document.getElementById(user_id + "-geplant").innerText = geplant;
				rest = document.getElementById(user_id + "-rest").innerText;
				rest--;
				document.getElementById(user_id + "-rest").innerText = rest;
			}
			task = 'urlaubsplan_eintrag';
			e.innerText = grund;
		}
					
		if(grund != e.innerText && grund != 'DEL'){
			if(grund == 'U' && closed_date == 'X' && e.dataset.user_name != 'aras'){
				return false;
			}
				
			else{
				e.className = '';
				e.classList.add(grund);
				field = e.innerText;
				field = field.replace(/\s+/g, '');
				if(field == 'U' && (wochentag != 'sa' && wochentag != 'so')){
					geplant = document.getElementById(user_id + "-geplant").innerText;
					geplant--;
					document.getElementById(user_id + "-geplant").innerText = geplant;
					rest = document.getElementById(user_id + "-rest").innerText;
					rest++;
					document.getElementById(user_id + "-rest").innerText = rest;
				}
				task = 'urlaubsplan_eintrag';
				e.innerText = grund;
			}
		}
			
		if(grund == 'DEL'){
			if((wochentag == 'sa' || wochentag == 'so') && closed_date != "X")
				e.classList.add('wochenende');
			else if(closed_date == "X")
				e.className = 'X';
			else
				e.className = '';
			field = e.innerText;
			field = field.replace(/\s+/g, '');
			if(field == 'U' && (wochentag != 'sa' && wochentag != 'so')){
				geplant = document.getElementById(user_id + "-geplant").innerText;
				geplant--;
				document.getElementById(user_id + "-geplant").innerText = geplant;
				rest = document.getElementById(user_id + "-rest").innerText;
				rest++;
				document.getElementById(user_id + "-rest").innerText = rest;
			}
			
			task = 'del_urlaubsplanung';
			e.innerText = '';				
		}

		$.ajax({  
			url: helperUrl,  
			type: 'POST',
			data: {
				task: task,
				user_id: user_id,
				day: day,
				month: month,
				year: year,
				date: date,
				kw: kw,
				wochentag: wochentag,
				grund: grund,
				eintrag: grund
			},  
			success:function(){  
			}  
		});
		click = 0;
	}
}

function set_right(er){
	
	click = 1;
	var helperUrl = '/wp-content/plugins/itweb-booking/classes/Helper.php';
	user_id = er.dataset.user_id;
	day = er.dataset.day;
	month = er.dataset.month;
	year = er.dataset.year;
	t_day = day < 10 ? "0" + day : day;
	t_month = month < 10 ? "0" + month : month;
	date = year + "-" + t_month + "-" + t_day;
	kw = er.dataset.kw;
	wochentag = er.dataset.wochentag;
	closed_date = er.dataset.closed_date;
	const contextMenu = document.getElementById("context-menu");
	const scope = document.querySelector("body");

  const normalizePozition = (mouseX, mouseY) => {
	// ? compute what is the mouse position relative to the container element (scope)
	let {
	  left: scopeOffsetX,
	  top: scopeOffsetY,
	} = scope.getBoundingClientRect();
	
	scopeOffsetX = scopeOffsetX < 0 ? 0 : scopeOffsetX;
	scopeOffsetY = scopeOffsetY < 0 ? 0 : scopeOffsetY;
   
	const scopeX = mouseX - scopeOffsetX;
	const scopeY = mouseY - scopeOffsetY;

	// ? check if the element will go out of bounds
	const outOfBoundsOnX =
	  scopeX + contextMenu.clientWidth > scope.clientWidth;

	const outOfBoundsOnY =
	  scopeY + contextMenu.clientHeight > scope.clientHeight;

	let normalizedX = mouseX;
	let normalizedY = mouseY;

	// ? normalize on X
	if (outOfBoundsOnX) {
	  normalizedX =
		scopeOffsetX + scope.clientWidth - contextMenu.clientWidth;
	}

	// ? normalize on Y
	if (outOfBoundsOnY) {
	  normalizedY =
		scopeOffsetY + scope.clientHeight - contextMenu.clientHeight;
	}

	return { normalizedX, normalizedY };
  };
	if(er.id != null){
		scope.addEventListener("contextmenu", (event) => {
		event.preventDefault();

		const { clientX: mouseX, clientY: mouseY } = event;

		const { normalizedX, normalizedY } = normalizePozition(mouseX, mouseY);

		contextMenu.classList.remove("visible");
		contextMenu.style.top = `${normalizedY}px`;
		contextMenu.style.left = `${normalizedX}px`;

		setTimeout(() => {
			if(er.id != null)
				contextMenu.classList.add("visible");
		});
	  });
	

    scope.addEventListener("click", (ev) => {
	// ? close the menu if the user clicks outside of it
	if (ev.target.offsetParent != contextMenu) {
	  contextMenu.classList.remove("visible");
	  er = null;
	}
	else{
		field_set = document.getElementById(user_id + "-" + day + "-" + month + "-" + year);
		grund = ev.target.dataset.grund;	
		if(click == 1 && er.dataset.user_name != 'aras' && closed_date == 'X' && grund == "U"){
			contextMenu.classList.remove("visible");
			click = 0;
			er = null;
		}
		
		else if(click == 1 && field_set.id != null){			
			if(grund == 'U' && grund != field_set.innerText){				
				field = field_set.innerText;
				field = field.replace(/\s+/g, '');
				if(field != 'U' && (wochentag != 'sa' && wochentag != 'so')){
					geplant = document.getElementById(user_id + "-geplant").innerText;
					geplant++;
					document.getElementById(user_id + "-geplant").innerText = geplant;
					rest = document.getElementById(user_id + "-rest").innerText;
					rest--;
					document.getElementById(user_id + "-rest").innerText = rest;
				}
				task = 'urlaubsplan_eintrag';
			}
					
			if(grund != field_set.innerText && grund != 'DEL'){
				if(grund == 'U' && closed_date == 'X' && er.dataset.user_name != 'aras'){
					contextMenu.classList.remove("visible");
					click = 0;
					er = null;
					return false;
					
				}
				else{
					field = field_set.innerText;
					field = field.replace(/\s+/g, '');
					if(field == 'U' && (wochentag != 'sa' && wochentag != 'so')){
						geplant = document.getElementById(user_id + "-geplant").innerText;
						geplant--;
						document.getElementById(user_id + "-geplant").innerText = geplant;
						rest = document.getElementById(user_id + "-rest").innerText;
						rest++;
						document.getElementById(user_id + "-rest").innerText = rest;
					}
					task = 'urlaubsplan_eintrag';
				}
			}
				
			if(grund == 'DEL'){
				field = field_set.innerText;
				field = field.replace(/\s+/g, '');
				if(field == 'U' && (wochentag != 'sa' && wochentag != 'so')){
					geplant = document.getElementById(user_id + "-geplant").innerText;
					geplant--;
					document.getElementById(user_id + "-geplant").innerText = geplant;
					rest = document.getElementById(user_id + "-rest").innerText;
					rest++;
					document.getElementById(user_id + "-rest").innerText = rest;
				}
				task = 'del_urlaubsplanung';					
			}
			
			field_set = document.getElementById(user_id + "-" + day + "-" + month + "-" + year);
			field_set.className = '';								
			field_set.classList.add(grund);
			field_set.innerText = grund;
			
			if(grund == 'DEL'){
				if((wochentag == 'sa' || wochentag == 'so') && closed_date != "X")
					field_set.classList.add('wochenende');
				else if(closed_date == "X")
					field_set.classList.add('X');
				else
					field_set.className = '';
				field_set.innerText = '';
			}
			
			$.ajax({  
				url: helperUrl,  
				type: 'POST',
				data: {
					task: task,
					user_id: user_id,
					day: day,
					month: month,
					year: year,
					date: date,
					kw: kw,
					wochentag: wochentag,
					grund: grund,
					eintrag: grund
				},  
				success:function(){  
				}  
			});
			
			contextMenu.classList.remove("visible");
			click = 0;
			er = null;
		}
		else{
			contextMenu.classList.remove("visible");
			click = 0;
			er = null;
		}
	}
  });
  }
}

</script>
