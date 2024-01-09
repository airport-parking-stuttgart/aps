<?php

if (!isset($_GET['edit'])) :

$db = Database::getInstance();
$current_user = wp_get_current_user();
if(isset($_POST)){
	if(isset($_POST['btn_del_d'])){
		//if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner'){
			$db->disableUser_einsatzplan($_POST[btn_del_d]);
		//}
	}
	elseif(isset($_POST['btn_del_a'])){
		//if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner'){
			$db->activateUser_einsatzplan($_POST[btn_del_a]);
		//}
	}
	elseif($_POST['btn'] == 1){
		$result = wp_insert_user( array(
		  'user_login' => $_POST['user_name'],
		  'user_pass' => $_POST['user_pw'],
		  //'user_email' => $_POST['user_mail'],
		  'first_name' => $_POST['first_name'],
		  'last_name' => $_POST['last_name'],
		  'display_name' => $_POST['first_name'],
		  'role' => $_POST['role']
		));
		if(is_wp_error($result)){
		  $error = $result->get_error_message();
		  echo $error;
		}else{
			$v = $_POST['first_name'];
			$n = $_POST['last_name'];
			
			$first_short = mb_substr($v, 0, 1);
			$seccond_short = mb_substr($n, 0, 1);
			$short = mb_strtolower($first_short . $seccond_short);
			$exist = $db->getUser_shortname($short);
			
			if($exist != null){
				$first_short = mb_substr($v, 0, 1);
				$seccond_short = mb_substr($n, 0, 2);
				$short = mb_strtolower($first_short . $seccond_short);
				$exist = $db->getUser_shortname($short);
				if($exist != null){
					$first_short = mb_substr($v, 0, 2);
					$seccond_short = mb_substr($n, 0, 2);
					$short = mb_strtolower($first_short . $seccond_short);
				}
			}
			
		  $user = get_user_by('id', $result);
		  add_user_meta( $result, 'short_name', $short);
		  add_user_meta( $result, 'birthdate', $_POST['birthdate']);
		  add_user_meta( $result, 'birthlocation', $_POST['birthlocation']);
		  add_user_meta( $result, 'street', $_POST['street']);
		  add_user_meta( $result, 'street-nr', $_POST['street-nr']);
		  add_user_meta( $result, 'zip-code', $_POST['zip-code']);
		  add_user_meta( $result, 'city', $_POST['city']);
		  add_user_meta( $result, 'firstday', $_POST['firstday']);
		  add_user_meta( $result, 'lastday', $_POST['lastday']);
		  add_user_meta( $result, 'type', $_POST['type']);
		  add_user_meta( $result, 'ma_nr', $_POST['ma_nr']);
		  add_user_meta( $result, 'stempel_nr', $_POST['stempel_nr']);
		  add_user_meta( $result, 'job', $_POST['job']);
		  add_user_meta( $result, 'fromounty', $_POST['fromounty']);
		  add_user_meta( $result, 'steuerklasse', $_POST['steuerklasse']);
		  add_user_meta( $result, 'steuernr', $_POST['steuernr']);
		  add_user_meta( $result, 'sozi', $_POST['sozi']);
		  add_user_meta( $result, 'versicherung', $_POST['versicherung']);
		  add_user_meta( $result, 'kontoinhaber', $_POST['kontoinhaber']);
		  add_user_meta( $result, 'bankname', $_POST['bankname']);
		  add_user_meta( $result, 'bic', $_POST['bic']);
		  add_user_meta( $result, 'iban', $_POST['iban']);
		  add_user_meta( $result, 'std_tag', $_POST['std_tag']);
		  add_user_meta( $result, 'std_w', $_POST['std_w']);
		  add_user_meta( $result, 'std_mon', $_POST['std_mon']);
		  add_user_meta( $result, 'std_lohn', $_POST['std_lohn']);
		  add_user_meta( $result, 'std_lohn_fest', $_POST['std_lohn_fest']);
		  add_user_meta( $result, 'bonus', $_POST['bonus']);
		  add_user_meta( $result, 'bonusab', $_POST['bonusab']);
		  add_user_meta( $result, 'bonusbis', $_POST['bonusbis']);
		  add_user_meta( $result, 'pause', $_POST['pause']);
		  add_user_meta( $result, 'urlaub', $_POST['pause']);
		  $db->addUser_einsatzplan($result, $_POST);
		}
	}
}


$users = $db->getUser_einsatzplan();

//echo "<pre>"; print_r($users); echo "</pre>";
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>  
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>  
<style>
table{
	border-collapse: separate !important;
}
.odd{background-color: #eee !important;}

.dataTables_filter{
	float: left !important;
}
</style>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
		<h3>Mitarbeiter</h3>
    </div>
	<br>
	<div class="row my-2 ui-lotdata-block ui-lotdata-block-next">
		<h5 class="ui-lotdata-title">Mitarbeiter Übersicht</h5>
		<div class="col-sm-12 col-md-12 ui-lotdata">
			<div class="row">
				<div class="col-sm-12 col-md-12">
					<details>
						<summary class="itweb_add_head-summary">Mitarbeiter Neuanlage</summary>
						<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . "?page=mitarbeiter"; ?>">
							<div class="row">
								<div class="col-sm-12 col-xs-12 col-md-3 col-lg-1">
									<label for="first_name">Vorname</label><br>
									<input type="text" name="first_name" placeholder="" class="" required>
								</div>
								<div class="col-sm-12 col-xs-12 col-md-3 col-lg-1">
									<label for="last_name">Nachname</label><br>
									<input type="text" name="last_name" placeholder="" class="" required>
								</div>
								<!--<div class="col-sm-12 col-xs-12 col-md-3 col-lg-1">
									<label for="short_name">Kürzel</label><br>
									<input type="text" name="short" placeholder="" class="" required>
								</div>-->
								<div class="col-sm-12 col-xs-12 col-md-3 col-lg-1">
									<label for="birthdate">Geburtsdatum</label><br>
									<input type="text" placeholder="" name="birthdate" class="single-datepicker">
								</div>
								<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
									<label for="birthlocation">Geburtsort</label><br>
									<input type="text" placeholder="" name="birthlocation" class="">
								</div>				
							</div>
							<br>
							<div class="row">
								<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
									<label for="street">Straße</label><br>
									<input type="text" placeholder="" name="street" class="">
								</div>
								<div class="col-sm-12 col-xs-12 col-md-2 col-lg-1">
									<label for="street-nr">Nr.</label><br>
									<input type="text" placeholder="" name="street-nr" class="">
								</div>
								<div class="col-sm-12 col-xs-12 col-md-2 col-lg-1">
									<label for="zip-code">PLZ</label><br>
									<input type="text" placeholder="" name="zip-code" class="">
								</div>
								<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
									<label for="city">Ort</label><br>
									<input type="text" placeholder="" name="city" class="">
								</div>
							</div>
							<br>
							<div class="row">
								<div class="col-12 col-xs-12 col-md-3 col-lg-2">
									<label for="firstday">Erster Arbeitstag</label><br>
									<input type="text" placeholder="" size="10" name="firstday" class="single-datepicker">
								</div>
								<div class="col-12 col-xs-12 col-md-3 col-lg-2">
									<label for="lastday">Letzter Arbeitstag</label><br>
									<input type="text" placeholder="" size="10" name="lastday" class="single-datepicker">
								</div>
								<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
									<label for="type">Gruppe</label><br>
									<select name="type" id="type" onchange="changeType()">
										<option value="VZ">Vollzeit (VZ)</option>
										<option value="TZ">Teilzeit (TZ)</option>
										<option value="GfB">GfB</option>
									</select>
								</div>
							</div>
							<br>
							<div class="row">
								<div class="col-sm-12 col-xs-12 col-md-3 col-lg-1">
									<label for="ma_nr">MA Nr.</label><br>
									<input type="text" placeholder="" name="ma_nr" class="">
								</div>
								<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
									<label for="stempel_nr">Stempel Nr.</label><br>
									<input type="text" placeholder="" name="stempel_nr" class="">
								</div>
								<div class="col-sm-12 col-xs-12 col-md-3 col-lg-1">
									<label for="job">Tätigkeit</label><br>
									<input type="text" placeholder="" name="job" class="">
								</div>
								<div class="col-sm-12 col-xs-12 col-md-3 col-lg-1">
									<label for="fromounty">Staatsangehörigkeit</label><br>
									<input type="text" placeholder="" name="fromounty" class="">
								</div>
								<div class="col-sm-12 col-xs-12 col-md-2 col-lg-1">
									<label for="steuerklasse">Steuerklasse</label><br>
									<input type="text" placeholder="" name="steuerklasse" class="">
								</div>
								<div class="col-sm-12 col-xs-12 col-md-2 col-lg-1">
									<label for="steuernr">Steuernummer</label><br>
									<input type="text" placeholder="" name="steuernr" class="">
								</div>
							</div>
							<br>
							<div class="row">
								<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
									<label for="sozi">Sozialversicherungsnummer</label><br>
									<input type="text" placeholder="" name="sozi" class="">
								</div>
								<div class="col-sm-12 col-xs-12 col-md-3 col-lg-1">
									<label for="versicherung">Krankenversicherung</label><br>
									<input type="text" placeholder="" name="versicherung" class="">
								</div>							
							</div>
							<br>
							<div class="row">
								<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
									<label for="kontoinhaber">Bankempfänger</label><br>
									<input type="text" placeholder="" name="kontoinhaber" class="">
								</div>
								<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
									<label for="bankname">Bankname</label><br>
									<input type="text" placeholder="" name="bankname" class="">
								</div>
								<div class="col-sm-12 col-xs-12 col-md-3 col-lg-1">
									<label for="bic">BIC</label><br>
									<input type="text" placeholder="" name="bic" class="">
								</div>
								<div class="col-sm-12 col-xs-12 col-md-5 col-lg-4">
									<label for="iban">IBAN</label><br>
									<input type="text" placeholder="" name="iban" class="">
								</div>
							</div>
							<br>
							<div class="row">
								<div class="col-sm-12 col-xs-12 col-md-2 col-lg-1">
									<label for="std_tag">Tagesstd.</label><br>
									<input type="number" placeholder="" size="5" name="std_tag" class="">
								</div>
								<div class="col-sm-12 col-xs-12 col-md-2 col-lg-1">
									<label for="std_w">Wochenstd.</label><br>
									<input type="number" placeholder="" size="5" name="std_w" class="">
								</div>
								<div class="col-sm-12 col-xs-12 col-md-2 col-lg-1">
									<label for="std_mon">Monatsstd.</label><br>
									<input type="number" placeholder="" size="5" name="std_mon" class="">
								</div>
							</div>
							<br>
							<div class="row">
								<div class="col-sm-12 col-xs-12 col-md-2 col-lg-1">
									<label for="std_lohn">Std. Lohn (€)</label><br>
									<input type="text" placeholder="" name="std_lohn" class="">
								</div>
								<div class="col-sm-12 col-xs-12 col-md-2 col-lg-1" id="lohnFest">
									<label for="std_lohn_fest">Lohn fest (€)</label><br>
									<input type="text" placeholder="" name="std_lohn_fest" class="">
								</div>
								<div class="col-sm-12 col-xs-12 col-md-2 col-lg-1" id="bonus">
									<label for="bonus">Schicht-Bonus</label><br>
									<input type="text" placeholder="" name="bonus" class="">
								</div>
								<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2" id="bonusab">
									<label for="bonusab">Schicht-Bonus Ab</label><br>
									<input type="time" placeholder="" name="bonusab" class="">
								</div>
								<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2" id="bonusbis">
									<label for="bonusbis">Schicht-Bonus Bis</label><br>
									<input type="time" placeholder="" name="bonusbis" class="">
								</div>
							</div>
							<br>
							<div class="row">
								<div class="col-sm-12 col-xs-12 col-md-2 col-lg-1">
									<label for="pause">Pausenregulierung</label><br>
									<input type="checkbox" name="pause" value="1">
								</div>
								<div class="col-sm-12 col-xs-12 col-md-3 col-lg-1">
									<label for="urlaub">Urlaubsanspruch</label><br>
									<input type="number" name="urlaub" size="5" placeholder="" class="">
								</div>
							</div>
							<br>
							<div class="row">							
								<div class="col-sm-12 col-xs-12 col-md-2 col-lg-1">
									<label for="role">Rolle</label><br>
									<select name="role" required>
										<option value ="fahrer">Fahrer</option>
										<option value ="koordinator">Koordinator</option>
										<option value ="admin2">Verwaltung</option>
									</select>
								</div>							
								<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
									<label for="user_name">Benutzername</label><br>
									<input type="text" name="user_name" placeholder="" class="" required>
								</div>
								<!--<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
									<label for="user_mail">E-Mail</label>
									<input type="text" name="user_mail" placeholder="" class="">
								</div>-->
								<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
									<label for="user_pw">Password</label><br>
									<input type="password" name="user_pw" minlength="6" placeholder="" class="" required>
								</div>
							</div><br><br>
							<div class="row">
								<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
									<button class="btn btn-primary" type="submit" name="btn" value="1">Mitarbeiter anlegen</button>
								</div>
							</div>
						</form>
					</details>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-sm-12 col-md-12">
					<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . "?page=mitarbeiter"; ?>">
						<div class="row">
							<div class="col-sm-12 col-xs-12 col-md-12 col-lg-12">
								<table class="mitarbeiter_datatable table-responsive">
									<thead>
										<tr>
											<th>Nr.</th>
											<th>Name</th>
											<th>Vorname</th>
											<th>Kürzel</th>
											<th>MA Nr.</th>
											<th>Stempel Nr.</th>
											<th>Gruppe</th>
											<th>Urlaubsanspruch</th>											
											<th>Benutzername</th>
											<!--<th>Rolle</th>-->										
											<th>Bearbeiten</th>
											<th>Aktion</th>
										</tr>
									<thead>
									<tbody class="row_position_table1">
										<?php $i = 1; foreach($users as $user): ?>
											<?php $wp_user = get_user_by('id', $user->user_id); ?>
											<?php if($wp_user != null): ?>
											<tr id="<?php echo $user->user_id ?>">
												<td><?php echo $i; ?></td>
												<td><?php echo get_user_meta( $wp_user->ID, 'last_name', true ); ?></td>
												<td><?php echo get_user_meta( $wp_user->ID, 'first_name', true ); ?></td>
												<td><?php echo get_user_meta( $wp_user->ID, 'short_name', true ); ?></td>
												<td><?php echo get_user_meta( $wp_user->ID, 'ma_nr', true ); ?></td>
												<td><?php echo get_user_meta( $wp_user->ID, 'stempel_nr', true ); ?></td>
												<td><?php echo get_user_meta( $wp_user->ID, 'type', true ) ?></td>
												<td><?php echo get_user_meta( $wp_user->ID, 'urlaub', true ); ?></td>
												<td><?php echo $wp_user->user_login ?></td>
												<!--<td><?php echo $user->role ?></td>-->
												<td><a href="/wp-admin/admin.php?page=mitarbeiter&edit=<?php echo $wp_user->ID ?>" class="btn btn-sm btn-secondary">Bearbeiten</a></td>
												
												<?php if($user->status == 0): ?>
													<td><button class="btn btn-danger" type="submit" name="btn_del_d" value="<?php echo $user->user_id ?>">Deaktivieren</button></td>
												<?php else: ?>
													<td><button class="btn btn-secondary" type="submit" name="btn_del_a" value="<?php echo $user->user_id ?>">Aktivieren</button></td>
												<?php endif; ?>
											</tr>
											<?php endif; ?>
										<?php $i++; endforeach;?>
									</tbody>
								</table>
							</div><br><br>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
bonus = document.getElementById("bonus");
bonusab = document.getElementById("bonusab");
bonusbis = document.getElementById("bonusbis");
lohnFest = document.getElementById("lohnFest");
function changeType(){
	type = document.getElementById("type");
	if(type.value == "GfB"){
		bonus.style.display = "none";
		bonusab.style.display = "none";
		bonusbis.style.display = "none";
		lohnFest.style.display = "none";
	}
	else{
		bonus.style.display = "block";
		bonusab.style.display = "block";
		bonusbis.style.display = "block";
		lohnFest.style.display = "block";
	}
}

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
</script>

<?php else: ?>
<?php require_once plugin_dir_path(__FILE__) . "mitarbeiter-edit-template.php"; ?>
<?php endif; ?>