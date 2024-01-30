<?php
$db = Database::getInstance();

$current_user = wp_get_current_user();

$mitarbeiter = $db->getActivUser_einsatzplan();

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
$currentMonth = (int)date('n');
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $c_month, $c_year);

foreach($mitarbeiter as $ma){
	//$user[$ma->user_id]['Urlaubstage']
}

//echo "<pre>"; print_r($users_fahrer); echo "</pre>";


?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>  
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>  
<style>
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
	border-left: 1px solid;
}

.Urlaub1, .Urlaub05{
	background-color: #ccffcc;
}
.Berufsschule{
	background-color: #ccffff;
}
.Frei{
	background-color: #ccccff;
}
.Krank{
	background-color: #ffcccc
}

.btn-submit{
	font-size: 23px;
}
</style>
<div class="page container-fluid <?php echo $_GET['page'] ?>">

    <div class="page-title itweb_adminpage_head">
		<h3>Urlaubsplan</h3>
	</div>
	<div class="page-body">
		<div class="row ui-lotdata-block ui-lotdata-block-next">
			<h5 class="ui-lotdata-title">Monat anzeigen</h5>
			<div class="col-sm-12 col-md-12 ui-lotdata">
				<div class="row">
					<div class="col-sm-12 col-md-2">
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
					<div class="col-sm-12 col-md-1">
						<select name="year" class="form-item form-control">
							<?php for ($i = 2021; $i <= 2023; $i++) : ?>
								<option value="<?php echo $i ?>" <?php echo $i == $c_year ? ' selected' : '' ?>>
									<?php echo $i ?>
								</option>
							<?php endfor; ?>
						</select>
					</div>

					<div class="col-sm-12 col-md-1">
						<button class="btn btn-primary d-block w-100" type="submit">Filter</button>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12 col-md-12">
				<div style="width: 100%; overflow: scroll;">
					<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . "?page=einsatzplan"; ?>">
						<table class="table table-sm" id="table1">
							<thead>
								<tr>
									<th style="position: sticky;left:0;background-color:aquamarine;">Mitarbeiter</th>
									<th>Soll</th>
									<th>Geplant</th>
									<th>Rest</th>
									<?php for($i = 1; $i <= $daysInMonth; $i++): ?>
										<?php $day = $i < 10 ? "0" . $i : $i; ?>
										<?php $date = $c_year . "-" . $c_month . "-" . $day ?>
										<th><?php echo $wochentage[date("w", strtotime($date))] . ", " . $day ?></th>
									<?php endfor; ?>
								</tr>
							</thead>
							<tbody class="row_position_table1">
								<?php foreach($mitarbeiter as $ma): ?>
									<?php $wp_user = get_user_by('id', $ma->user_id); ?>
									<?php $soll = get_user_meta( $ma->user_id, 'urlaub', true ) != null ? get_user_meta( $ma->user_id, 'urlaub', true ) :  0; ?>
									<?php $gaplant = $db->getUrlaubstage($ma->user_id, $c_year); ?>
									<?php $rest = get_user_meta( $ma->user_id, 'urlaub', true ) - $gaplant; ?>
									<tr>
										<td style="position: sticky;left:0;background-color:aquamarine;"><?php echo $wp_user->display_name; ?></td>
										<td><?php echo $soll; ?></td>
										<td><?php echo $gaplant ?></td>
										<td><?php echo $rest ?></td>
										<?php for($i = 1; $i <= $daysInMonth; $i++): ?>
										<?php $day = $i < 10 ? "0" . $i : $i; ?>
										<?php $date = $c_year . "-" . $c_month . "-" . $day ?>
										<?php $wochentag = str_replace(".", "", strtolower($wochentage[date("w", strtotime($date))])) ?>
										<?php $kw = date("W", strtotime($date)) ?>
										<?php $einsatzplan = $db->getEinsatzplanByUserIDandDay($kw, $c_year, $wochentag, $ma->user_id); ?>
										<?php if(str_contains($einsatzplan->$wochentag, "-"))
												$td_css = $selected = "Arbeit";
											  elseif($einsatzplan->$wochentag == 'U' || $einsatzplan->$wochentag == 'U1')
												$td_css = $selected = "Urlaub1";
											elseif($einsatzplan->$wochentag == 'U05')
												$td_css = $selected = "Urlaub05";
											  elseif($einsatzplan->$wochentag == 'BFS')
												$td_css = $selected = "Berufsschule";
											  elseif($einsatzplan->$wochentag == 'F')
												$td_css = $selected = "Frei";
											  elseif($einsatzplan->$wochentag == 'K')
												$td_css = $selected = "Krank";
											  else
												$td_css = $selected = "";
										?>
										<?php //echo "<pre>"; print_r($einsatzplan); echo "</pre>"; ?>									
											<td class="<?php echo $td_css ?>"> <?php //echo $wochentag . " " . $date . " " . $kw ?>
												<select name="grund-<?php echo $ma->user_id . "-" . $i ?>" id="" 
												data-user_id="<?php echo $ma->user_id ?>" 
												data-day="<?php echo $i ?>" 
												data-month="<?php echo $c_month ?>" 
												data-year="<?php echo $c_year ?>" 
												data-kw="<?php echo $kw ?>"
												data-wochentag="<?php echo $wochentag ?>"
												onchange="setField(this)">
													<option value="">Grund</option>
														<option value="A" <?php echo $selected == "Arbeit" ? "selected" : "" ?>>Arbeit</option>
														<option value="U1" <?php echo $selected == "Urlaub1" ? "selected" : "" ?>>Urlaub 1T</option>
														<option value="U05" <?php echo $selected == "Urlaub05" ? "selected" : "" ?>>Urlaub 0,5T</option>
														<option value="F" <?php echo $selected == "Frei" ? "selected" : "" ?>>Frei</option>
														<option value="BF">Bezahlte Freistellung</option>
														<option value="K" <?php echo $selected == "Krank" ? "selected" : "" ?>>Krank</option>
														<option value="KA">Kurzarbeit</option>
														<option value="BFS" <?php echo $selected == "Berufsschule" ? "selected" : "" ?>>Berufsschule</option>
														<option value="FB">Fortbildung</option>
														<option value="GR">Geschäftsreise</option>
												</select>
												<input type="text" name="time-<?php echo $ma->user_id . "-" . $i ?>"" size="7" value="<?php echo $einsatzplan->$wochentag ?>">
												<a class="btn-submit" style="float: right" 
													data-user_id="<?php echo $ma->user_id ?>" 
													data-day="<?php echo $i ?>" 
													data-month="<?php echo $c_month ?>" 
													data-year="<?php echo $c_year ?>" 
													data-kw="<?php echo $kw ?>"
													data-wochentag="<?php echo $wochentag ?>" 
													onclick="addData(this)">&#10145;</a>
											</td>
										<?php endfor; ?>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</form>
					<?php if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner' || $current_user->user_login == 'cam' || $current_user->user_login == 'choudhary'): ?>
					<!--<script>
					var helperUrl = '/wp-content/plugins/itweb-booking/classes/Helper.php';
					 $( ".row_position_table1" ).sortable({  
							delay: 150,  
							stop: function() {  
								var selectedData = new Array();  
								$('.row_position_table1>tr').each(function() {  
									selectedData.push($(this).attr("id"));
									//alert(selectedData[0]);
								});  
								updateOrder(selectedData);  
							}  
						});
						 function updateOrder(data) {  
							$.ajax({  
								url: helperUrl,  
								type: 'POST',
								data: {
									task: 'einsatzplan_ordnen',
									position: data
								},  
								success:function(){  
								}  
							})  
						} 
					</script>-->
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
<script>

function setField(e){
	user_id = e.dataset.user_id;
	day = e.dataset.day;
	month = e.dataset.month;
	year = e.dataset.year;
	t_day = day < 10 ? "0" + day : day;
	date = year + "-" + month + "-" + t_day;
	kw = e.dataset.kw;
	wochentag = e.dataset.wochentag;
	if(e.value == "A" || e.value == "")
		document.getElementsByName("time-"+user_id+"-"+day)[0].value = "";
	else
		document.getElementsByName("time-"+user_id+"-"+day)[0].value = e.value;
}

function addData(e){
	var helperUrl = '/wp-content/plugins/itweb-booking/classes/Helper.php';
	user_id = e.dataset.user_id;
	day = e.dataset.day;
	month = e.dataset.month;
	year = e.dataset.year;
	t_day = day < 10 ? "0" + day : day;
	date = year + "-" + month + "-" + t_day;
	kw = e.dataset.kw;
	wochentag = e.dataset.wochentag;
	grund = document.getElementsByName("grund-"+user_id+"-"+day)[0].value;
	eintrag = document.getElementsByName("time-"+user_id+"-"+day)[0].value;
	
	$.ajax({  
		url: helperUrl,  
		type: 'POST',
		data: {
			task: 'urlaubsplan_eintrag',
			user_id: user_id,
			day: day,
			month: month,
			year: year,
			date: date,
			kw: kw,
			wochentag: wochentag,
			grund: grund,
			eintrag: eintrag
		},  
		success:function(){  
		}  
	});
}

</script>