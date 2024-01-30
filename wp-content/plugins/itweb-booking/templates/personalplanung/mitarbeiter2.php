<?php
$db = Database::getInstance();
$current_user = wp_get_current_user();
if(isset($_POST)){
	if($_POST['btn'] == 1){
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
		  $user = get_user_by('id', $result);
		  add_user_meta( $result, 'type', $_POST['type']);
		  add_user_meta( $result, 'std_mon', $_POST['std_mon']);
		  $db->addUser_einsatzplan($result, $_POST);
		}
	}
	elseif(isset($_POST['btn_del_d'])){
		if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner'){
			$db->disableUser_einsatzplan($_POST[btn_del_d]);
		}
	}
	elseif(isset($_POST['btn_del_a'])){
		if($current_user->user_login == 'sergej' || $current_user->user_login == 'aras' || $current_user->user_login == 'cakir' || $current_user->user_login == 'soner'){
			$db->activateUser_einsatzplan($_POST[btn_del_a]);
		}
	}
	elseif(isset($_POST['btn_edit'])){
		$user_id = $_POST['btn_edit'];
		wp_update_user( array( 'ID' => $user_id,
		  'user_login' => $_POST['login_'.$user_id],
		  //'user_pass' => $_POST['user_pw'],
		  'first_name' => $_POST['vorname_'.$user_id],
		  'last_name' => $_POST['nachname_'.$user_id],
		  'display_name' => $_POST['vorname_'.$user_id],
		  'role' => $_POST['role_'.$user_id]
		));
		if(isset($_POST['login_'.$user_id])){
			global $wpdb;
			$wpdb->update(
				$wpdb->users, 
				['user_login' => $_POST['login_'.$user_id]], 
				['ID' => $user_id]
			);
		}
		if(isset($_POST['pw_'.$user_id])){
			wp_update_user( array( 'ID' => $user_id,
				'user_pass' => $_POST['pw_'.$user_id]
			));
		}
		update_user_meta( $user_id, 'urlaub', $_POST['urlaub_'.$user_id] );
		if($_POST['type_'.$user_id] != null)
			update_user_meta( $user_id, 'type', $_POST['type_'.$user_id] );
		else
			delete_user_meta($user_id, 'type');
		
		update_user_meta( $user_id, 'std_mon', $_POST['std_mon_'.$user_id] );
	}
}


$users = $db->getUser_einsatzplan();

//echo "<pre>"; print_r($users); echo "</pre>";
?>

<style>
table{
	border-collapse: separate !important;
}
</style>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Mitarbeiter</h3>
    </div>
	<div class="row my-2 ui-lotdata-block ui-lotdata-block-next">
		<h5 class="ui-lotdata-title">Mitarbeiter anlegen</h5>
		<div class="col-sm-12 col-md-12 ui-lotdata">
			<div class="row">
				<div class="col-sm-12 col-md-12">
					<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . "?page=mitarbeiter"; ?>">
						<div class="row">
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="first_name">Vorname</label>
								<input type="text" name="first_name" placeholder="" class="" required>
							</div>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="last_name">Nachname</label>
								<input type="text" name="last_name" placeholder="" class="" required>
							</div>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="type">Als</label><br>
								<select name="type">
									<option value="VZ">Vollzeit</option>
									<option value="TZ">Teilzeit</option>
									<option value="Aushilfe">Aushilfe</option>
								</select>
							</div>
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="role">Rolle</label><br>
								<select name="role" required>
									<option value ="fahrer">Fahrer</option>
									<option value ="koordinator">Koordinator</option>
								</select>
							</div>
							<div class="col-sm-12 col-xs-12 col-md-2 col-lg-1">
								<label for="urlaub">Urlaubstage</label>
								<input type="number" name="urlaub" step="0.5" placeholder="" class="">
							</div>
							<div class="col-sm-12 col-xs-12 col-md-2 col-lg-1">
								<label for="std_mon">Std./Mon.</label>
								<input type="number" name="std_mon" step="0.5" placeholder="" class="">
							</div>
						</div><br>
						<div class="row">
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="user_name">Benutzername</label>
								<input type="text" name="user_name" placeholder="" class="" required>
							</div>
							<!--<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="user_mail">E-Mail</label>
								<input type="text" name="user_mail" placeholder="" class="">
							</div>-->
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<label for="user_pw">Password</label>
								<input type="password" name="user_pw" minlength="6" placeholder="" class="" required>
							</div>
						</div><br><br>
						<div class="row">
							<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
								<button class="btn btn-primary" type="submit" name="btn" value="1">Benutzer anlegen</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<br><br>
	<div class="row my-2 ui-lotdata-block ui-lotdata-block-next">
		<h5 class="ui-lotdata-title">Mitarbeiter bearbeiten</h5>
		<div class="col-sm-12 col-md-12 ui-lotdata">
			<div class="row">
				<div class="col-sm-12 col-md-12">
					<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . "?page=mitarbeiter"; ?>">
						<div class="row">
							<div class="col-sm-12 col-xs-12 col-md-12 col-lg-12">
								<table class="table"> <!-- sales-table -->
									<thead>
										<tr>
											<th>Vorname</th>
											<th>Nachname</th>
											<th>Als</th>
											<th>Benutzername</th>
											<th>Rolle</th>
											<th>Urlaubstage</th>
											<th>Std./Mon.</th>
											<th>Neues Passwort</th>
											<th>Bearbeiten</th>
											<th>Aktion</th>
										</tr>
									<thead>
									<tbody>
										<?php foreach($users as $user): ?>
											<?php $wp_user = get_user_by('id', $user->user_id); ?>
											<?php if($wp_user != null): ?>
											<tr>
												<td><input type="text" size="10" name="vorname_<?php echo $wp_user->ID ?>" value="<?php echo get_user_meta( $wp_user->ID, 'first_name', true ); ?>"></td>
												<td><input type="text" size="10" name="nachname_<?php echo $wp_user->ID ?>" value="<?php echo get_user_meta( $wp_user->ID, 'last_name', true ); ?>"></td>
												<td>
													<select name="type_<?php echo $wp_user->ID ?>">
														<option>-</option>
														<option value="VZ" <?php echo get_user_meta( $wp_user->ID, 'type', true ) == 'VZ' ? 'selected' : ""; ?> >Vollzeit</option>
														<option value="TZ" <?php echo get_user_meta( $wp_user->ID, 'type', true ) == 'TZ' ? 'selected' : ""; ?> >Teilzeit</option>
														<option value="Aushilfe" <?php echo get_user_meta( $wp_user->ID, 'type', true ) == 'Aushilfe' ? 'selected' : ""; ?> >Aushilfe</option>
													</select>
												</td>
												<td><input type="text" size="10" name="login_<?php echo $wp_user->ID ?>" value="<?php echo $wp_user->user_login ?>"></td>
												<td>
												<?php if($user->role != 'admin2'): ?>
													<select name="role_<?php echo $wp_user->ID ?>">
														<option value="fahrer" <?php echo $user->role == 'fahrer' ? "selected" : "" ?>>Fahrer</option>
														<option value="koordinator" <?php echo $user->role == 'koordinator' ? "selected" : "" ?>>Koordinator</option>
													</select>												
												<? else: ?>
													<?php echo $user->role ?>
												</td>												
												<?php endif; ?>
												<td><input type="number" size="5" step="0.5" name="urlaub_<?php echo $wp_user->ID ?>" value="<?php echo get_user_meta( $wp_user->ID, 'urlaub', true ); ?>"></td>
												<td><input type="number" size="5" step="0.5" name="std_mon_<?php echo $wp_user->ID ?>" value="<?php echo get_user_meta( $wp_user->ID, 'std_mon', true ); ?>"></td>
												<td><input type="password" name="pw_<?php echo $wp_user->ID ?>"></input></td>
												<td><button class="btn btn" type="submit" name="btn_edit" value="<?php echo $user->user_id ?>">Speichern</button></td>
												
												<?php if($user->status == 0): ?>
													<td><button class="btn btn-danger" type="submit" name="btn_del_d" value="<?php echo $user->user_id ?>">Deaktivieren</button></td>
												<?php else: ?>
													<td><button class="btn btn-secondary" type="submit" name="btn_del_a" value="<?php echo $user->user_id ?>">Aktivieren</button></td>
												<?php endif; ?>
											</tr>
											<?php endif; ?>
										<?php endforeach;?>
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