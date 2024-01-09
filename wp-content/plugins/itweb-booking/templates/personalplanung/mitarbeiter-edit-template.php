<?php

$user_id = $_GET["edit"];
$user = get_user_by('id', $user_id);
$user_data = get_userdata( $user_id );
$db = Database::getInstance();
$current_user = wp_get_current_user();
if(isset($_POST)){
	if(isset($_POST['btn_edit'])){
		
		if($user_id == 3)
			$u_role = "administrator";
		else
			$u_role = $_POST['role'];
		
		$db->updateEinsatzplanRole($user_id, $u_role);
		
		wp_update_user( array( 'ID' => $user_id,
		  //'user_login' => $_POST['login_'.$user_id],
		  //'user_pass' => $_POST['user_pw'],
		  'first_name' => $_POST['first_name'],
		  'last_name' => $_POST['last_name'],
		  'display_name' => $_POST['first_name'] . " " . $_POST['last_name'],
		  'role' => $u_role
		));
		/*
		if(isset($_POST['user_name'])){
			global $wpdb;
			$wpdb->update(
				$wpdb->users, 
				['user_login' => $_POST['user_name']], 
				['ID' => $user_id]
			);
		}
		*/
		if(isset($_POST['user_pw'])){
			wp_update_user( array( 'ID' => $user_id,
				'user_pass' => $_POST['user_pw']
			));
		}
		
		update_user_meta( $user_id, 'short_name', $_POST['short_name']);
		update_user_meta( $user_id, 'birthdate', $_POST['birthdate']);
		update_user_meta( $user_id, 'birthlocation', $_POST['birthlocation']);
		update_user_meta( $user_id, 'street', $_POST['street']);
		update_user_meta( $user_id, 'street-nr', $_POST['street-nr']);
		update_user_meta( $user_id, 'zip-code', $_POST['zip-code']);
		update_user_meta( $user_id, 'city', $_POST['city']);
		update_user_meta( $user_id, 'firstday', $_POST['firstday']);
		update_user_meta( $user_id, 'lastday', $_POST['lastday']);
		update_user_meta( $user_id, 'type', $_POST['type']);
		update_user_meta( $user_id, 'ma_nr', $_POST['ma_nr']);
		update_user_meta( $user_id, 'stempel_nr', $_POST['stempel_nr']);
		update_user_meta( $user_id, 'job', $_POST['job']);
		update_user_meta( $user_id, 'fromounty', $_POST['fromounty']);
		update_user_meta( $user_id, 'steuerklasse', $_POST['steuerklasse']);
		update_user_meta( $user_id, 'steuernr', $_POST['steuernr']);
		update_user_meta( $user_id, 'sozi', $_POST['sozi']);
		update_user_meta( $user_id, 'versicherung', $_POST['versicherung']);
		update_user_meta( $user_id, 'kontoinhaber', $_POST['kontoinhaber']);
		update_user_meta( $user_id, 'bankname', $_POST['bankname']);
		update_user_meta( $user_id, 'bic', $_POST['bic']);
		update_user_meta( $user_id, 'iban', $_POST['iban']);
		update_user_meta( $user_id, 'std_tag', $_POST['std_tag']);
		update_user_meta( $user_id, 'std_w', $_POST['std_w']);
		update_user_meta( $user_id, 'std_mon', $_POST['std_mon']);
		update_user_meta( $user_id, 'std_lohn', $_POST['std_lohn']);
		update_user_meta( $user_id, 'std_lohn_fest', $_POST['std_lohn_fest']);
		update_user_meta( $user_id, 'bonus', $_POST['bonus']);
		update_user_meta( $user_id, 'bonusab', $_POST['bonusab']);
		update_user_meta( $user_id, 'bonusbis', $_POST['bonusbis']);
		if(isset($_POST['pause']))
			update_user_meta( $user_id, 'pause', $_POST['pause']);
		else
			delete_user_meta($user_id, 'pause');
		update_user_meta( $user_id, 'urlaub', $_POST['urlaub']);
		
		header("Location: admin.php?page=mitarbeiter");
	}
}

$v = get_user_meta( $user->ID, 'first_name', true );
$n = get_user_meta( $user->ID, 'last_name', true );
if(get_user_meta( $user->ID, 'short_name', true ) == null){
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
}
else{
	$short = mb_strtolower(get_user_meta( $user->ID, 'short_name', true ));
}

//	echo "<pre>"; print_r($user_data->roles); echo "</pre>";
?>

<style>
table{
	border-collapse: separate !important;
}
</style>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Mitarbeiter bearbeiten</h3>
    </div>
	<br>
	<div class="row my-2 ui-lotdata-block ui-lotdata-block-next">
		<h5 class="ui-lotdata-title">Mitarbeiter bearbeiten</h5>
		<div class="col-sm-12 col-md-12 ui-lotdata">
			<div class="row">
				<div class="col-sm-12 col-md-12">
					<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . "?page=mitarbeiter&edit=".$user_id; ?>">
						<div class="row">
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-1">
								<label for="first_name">Vorname</label><br>
								<input type="text" name="first_name" placeholder="" class="" value="<?php echo get_user_meta( $user->ID, 'first_name', true ); ?>" required>
							</div>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-1">
								<label for="last_name">Nachname</label><br>
								<input type="text" name="last_name" placeholder="" class="" value="<?php echo get_user_meta( $user->ID, 'last_name', true ); ?>" required>
							</div>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-1">
								<label for="short_name">Kürzel</label><br>
								<input type="text" name="short_name" placeholder="" class="" value="<?php echo $short; ?>" required>
							</div>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-1">
								<label for="birthdate">Geburtsdatum</label><br>
								<input type="text" placeholder="" name="birthdate" class="single-datepicker" value="<?php echo get_user_meta( $user->ID, 'birthdate', true ); ?>">
							</div>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="birthlocation">Geburtsort</label><br>
								<input type="text" placeholder="" name="birthlocation" class="" value="<?php echo get_user_meta( $user->ID, 'birthlocation', true ); ?>">
							</div>				
						</div>
						<br>
						<div class="row">
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="street">Straße</label><br>
								<input type="text" placeholder="" name="street" class="" value="<?php echo get_user_meta( $user->ID, 'street', true ); ?>">
							</div>
							<div class="col-sm-12 col-xs-12 col-md-2 col-lg-1">
								<label for="street-nr">Nr.</label><br>
								<input type="text" placeholder="" name="street-nr" class="" value="<?php echo get_user_meta( $user->ID, 'street-nr', true ); ?>">
							</div>
							<div class="col-sm-12 col-xs-12 col-md-2 col-lg-1">
								<label for="zip-code">PLZ</label><br>
								<input type="text" placeholder="" name="zip-code" class="" value="<?php echo get_user_meta( $user->ID, 'zip-code', true ); ?>">
							</div>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="city">Ort</label><br>
								<input type="text" placeholder="" name="city" class="" value="<?php echo get_user_meta( $user->ID, 'city', true ); ?>">
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="firstday">Erster Arbeitstag</label><br>
								<input type="text" placeholder="" size="10" name="firstday" class="single-datepicker" value="<?php echo get_user_meta( $user->ID, 'firstday', true ); ?>">
							</div>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="lastday">Letzter Arbeitstag</label><br>
								<input type="text" placeholder="" size="10" name="lastday" class="single-datepicker" value="<?php echo get_user_meta( $user->ID, 'lastday', true ); ?>">
							</div>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="type">Gruppe</label><br>
								<select name="type" id="type" onchange="changeType()">
									<option value="VZ" <?php echo get_user_meta( $user->ID, 'type', true ) == 'VZ' ? 'selected' : '' ?>>Vollzeit (VZ)</option>
									<option value="TZ" <?php echo get_user_meta( $user->ID, 'type', true ) == 'TZ' ? 'selected' : '' ?>>Teilzeit (TZ)</option>
									<option value="GfB" <?php echo get_user_meta( $user->ID, 'type', true ) == 'GfB' ? 'selected' : '' ?>>GfB</option>
								</select>
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-1">
								<label for="ma_nr">MA Nr.</label><br>
								<input type="text" placeholder="" name="ma_nr" class="" value="<?php echo get_user_meta( $user->ID, 'ma_nr', true ); ?>">
							</div>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="stempel_nr">Stempel Nr.</label><br>
								<input type="text" placeholder="" name="stempel_nr" class="" value="<?php echo get_user_meta( $user->ID, 'stempel_nr', true ); ?>">
							</div>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-1">
								<label for="job">Tätigkeit</label><br>
								<input type="text" placeholder="" name="job" class="" value="<?php echo get_user_meta( $user->ID, 'job', true ); ?>">
							</div>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-1">
								<label for="fromounty">Staatsangehörigkeit</label><br>
								<input type="text" placeholder="" name="fromounty" class="" value="<?php echo get_user_meta( $user->ID, 'fromounty', true ); ?>">
							</div>
							<div class="col-sm-12 col-xs-12 col-md-2 col-lg-1">
								<label for="steuerklasse">Steuerklasse</label><br>
								<input type="text" placeholder="" name="steuerklasse" class="" value="<?php echo get_user_meta( $user->ID, 'steuerklasse', true ); ?>">
							</div>
							<div class="col-sm-12 col-xs-12 col-md-2 col-lg-1">
								<label for="steuernr">Steuernummer</label><br>
								<input type="text" placeholder="" name="steuernr" class="" value="<?php echo get_user_meta( $user->ID, 'steuernr', true ); ?>">
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="sozi">Sozialversicherungsnummer</label><br>
								<input type="text" placeholder="" name="sozi" class="" value="<?php echo get_user_meta( $user->ID, 'sozi', true ); ?>">
							</div>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-1">
								<label for="versicherung">Krankenversicherung</label><br>
								<input type="text" placeholder="" name="versicherung" class="" value="<?php echo get_user_meta( $user->ID, 'versicherung', true ); ?>">
							</div>							
						</div>
						<br>
						<div class="row">
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="kontoinhaber">Bankempfänger</label><br>
								<input type="text" placeholder="" name="kontoinhaber" class="" value="<?php echo get_user_meta( $user->ID, 'kontoinhaber', true ); ?>">
							</div>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="bankname">Bankname</label><br>
								<input type="text" placeholder="" name="bankname" class="" value="<?php echo get_user_meta( $user->ID, 'bankname', true ); ?>">
							</div>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-1">
								<label for="bic">BIC</label><br>
								<input type="text" placeholder="" name="bic" class="" value="<?php echo get_user_meta( $user->ID, 'bic', true ); ?>">
							</div>
							<div class="col-sm-12 col-xs-12 col-md-5 col-lg-4">
								<label for="iban">IBAN</label><br>
								<input type="text" placeholder="" name="iban" class="" value="<?php echo get_user_meta( $user->ID, 'iban', true ); ?>">
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-sm-12 col-xs-12 col-md-2 col-lg-1">
								<label for="std_tag">Tagesstd.</label><br>
								<input type="number" placeholder="" size="5" name="std_tag" class="" value="<?php echo get_user_meta( $user->ID, 'std_tag', true ); ?>">
							</div>
							<div class="col-sm-12 col-xs-12 col-md-2 col-lg-1">
								<label for="std_w">Wochenstd.</label><br>
								<input type="number" placeholder="" size="5" name="std_w" class="" value="<?php echo get_user_meta( $user->ID, 'std_w', true ); ?>">
							</div>
							<div class="col-sm-12 col-xs-12 col-md-2 col-lg-1">
								<label for="std_mon">Monatsstd.</label><br>
								<input type="number" placeholder="" size="5" name="std_mon" class="" value="<?php echo get_user_meta( $user->ID, 'std_mon', true ); ?>">
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-1">
								<label for="std_lohn">Std. Lohn (€)</label><br>
								<input type="text" placeholder="" name="std_lohn" class="" value="<?php echo get_user_meta( $user->ID, 'std_lohn', true ); ?>">
							</div>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-1" id="lohnFest">
								<label for="std_lohn_fest">Lohn fest (€)</label><br>
								<input type="text" placeholder="" name="std_lohn_fest" class="" value="<?php echo get_user_meta( $user->ID, 'std_lohn_fest', true ); ?>">
							</div>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-1" id="bonus">
								<label for="bonus">Schicht-Bonus</label><br>
								<input type="text" placeholder="" name="bonus" class="" value="<?php echo get_user_meta( $user->ID, 'bonus', true ); ?>">
							</div>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2" id="bonusab">
								<label for="bonusab">Schicht-Bonus Ab</label><br>
								<input type="time" placeholder="" name="bonusab" class="" value="<?php echo get_user_meta( $user->ID, 'bonusab', true ); ?>">
							</div>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2" id="bonusbis">
								<label for="bonusbis">Schicht-Bonus Bis</label><br>
								<input type="time" placeholder="" name="bonusbis" class="" value="<?php echo get_user_meta( $user->ID, 'bonusbis', true ); ?>">
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-1">
								<label for="pause">Pausenregulierung</label><br>
								<input type="checkbox" name="pause" value="1" <?php echo get_user_meta( $user->ID, 'pause', true ) == '1' ? 'checked' : '' ?>>
							</div>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-1">
								<label for="urlaub">Urlaubsanspruch</label><br>
								<input type="number" name="urlaub" size="5" placeholder="" class="" value="<?php echo get_user_meta( $user->ID, 'urlaub', true ); ?>">
							</div>
						</div>
						<br>
						<div class="row">							
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-1">
								<label for="role">Rolle</label><br>
								<select name="role" required>
									<option value ="fahrer" <?php echo in_array('fahrer', $user_data->roles) ? "selected" : "" ?>>Fahrer</option>
									<option value ="koordinator" <?php echo in_array('koordinator', $user_data->roles) ? "selected" : "" ?>>Koordinator</option>
									<option value ="admin2" <?php echo in_array('admin2', $user_data->roles) ? "selected" : "" ?>>Verwaltung</option>
								</select>
							</div>							
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="user_name">Benutzername</label><br>
								<input type="text" name="user_name" placeholder="" class="" value="<?php echo $user->user_login ?>" required>
							</div>
							<!--<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="user_mail">E-Mail</label>
								<input type="text" name="user_mail" placeholder="" class="">
							</div>-->
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="user_pw">Neues Password</label><br>
								<input type="password" name="user_pw" minlength="6" placeholder="" class="" autocomplete="off">
							</div>
						</div><br><br>
						<div class="row">
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<button class="btn btn-primary" type="submit" name="btn_edit" value="1">Speichern</button>
							</div>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<a href="<?php echo '/wp-admin/admin.php?page=mitarbeiter' ?>" class="btn btn-secondary" >Schließen</a>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<br><br>
</div>

<script>
bonus = document.getElementById("bonus");
bonusab = document.getElementById("bonusab");
bonusbis = document.getElementById("bonusbis");
lohnFest = document.getElementById("lohnFest");
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
</script>