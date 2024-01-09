<?php
/**
* Template Name: User.Account
 */
ini_set("memory_limit", "1024M");
global $wpdb;

$current_user = wp_get_current_user();
$user_id = get_current_user_id();
$all_meta_for_user = get_user_meta( $user_id );

if(isset($_POST) && $_POST['update'] == 1){
	
	wp_update_user( array( 'ID' => $user_id,
		  'first_name' => $_POST['first_name'],
		  'last_name' => $_POST['last_name'],
		  'display_name' => $_POST['first_name'] . " " . $_POST['last_name']
	));
	
	if(isset($_POST['password']) || $_POST['password'] != ''){
		wp_update_user( array( 'ID' => $user_id,
			'user_pass' => $_POST['password']
		));
	}
	
	if($all_meta_for_user['gkunde'][0]){
		update_user_meta( $user_id, 'firmenname', $_POST['firmenname'] );
		update_user_meta( $user_id, 'billing_company', $_POST['firmenname'] );
		update_user_meta( $user_id, 'ust_id', $_POST['ust_id'] );
		$firma = "Firma: " . $_POST['firmenname'] . "<br>";
		$ust_id = "USt-IdNr.: " . $_POST['ust_id'] . "<br>";
	}
	update_user_meta( $user_id, 'gender', $_POST['billing_grander'] );
	update_user_meta( $user_id, 'billing_grander', $_POST['billing_grander'] );
	
	update_user_meta( $user_id, 'strasse', $_POST['billing_address_1'] );
	update_user_meta( $user_id, 'billing_address_1', $_POST['billing_address_1'] );
	
	update_user_meta( $user_id, 'plz_ort', $_POST['billing_postcode'] . " " . $_POST['billing_city']);
	update_user_meta( $user_id, 'billing_postcode', $_POST['billing_postcode'] );
	update_user_meta( $user_id, 'billing_city', $_POST['billing_city'] );
	
	update_user_meta( $user_id, 'billing_email', $_POST['email'] );
	update_user_meta( $user_id, 'billing_first_name', $_POST['first_name'] );
	update_user_meta( $user_id, 'billing_last_name', $_POST['last_name'] );
	
	if($_POST['billing_grander'] == 'male')
		$anrede = "<p>Sehr geehrter Herr " . $_POST['last_name'] . ",</p>";
	elseif($_POST['billing_grander'] == 'female')
		$anrede = "<p>Sehr geehrter Herr " . $_POST['last_name'] . ",</p>";
	else
		$anrede = "<p>Sehr geehrtere Damen und Herren,</p>";

	if($_POST['newsletter'] == 'newsletter' && $all_meta_for_user['newsletter'][0] == null)
		add_user_meta($user_id, 'newsletter', 'newsletter');
	elseif($_POST['newsletter'] == null && $all_meta_for_user['newsletter'][0] == 'newsletter')
		delete_user_meta($user_id, 'newsletter');

	$body = $anrede . 
			"<p>Sie haben Ihre Kundendaten geändert. Folgende Informationen sind jetzt bei uns hinterlegt:</p>" . 
			$firma .
			$ust_id .
			"Vorname: " . $_POST['first_name'] . "<br>" . 
			"Nachname: " . $_POST['last_name'] . "<br>" . 
			"Adresse: " . $_POST['billing_address_1'] . ", " . $_POST['billing_postcode'] . " " . $_POST['billing_city'] . "<br>" . 
			"<p>Sie haben noch Fragen? Schreiben Sie uns einfach eine E-Mail an
			<a href='mailto:info@airport-parking-stuttgart.de'>info@airport-parking-stuttgart.de</a> oder rufen Sie uns
			unter <a href='tel:+49 711 22 051 245'>+49 711 22 051 245</a> an.
			</p>
			<p>Montag bis Freitag von 07:00 bis 19:00 Uhr.
			   Aus dem dt. Festnetz zum Ortstarif. Mobilfunkkosten abweichend.</p>
			<p>Mit freundlichen Grüßen<br><br>
				APS-Airport-Parking-Stuttgart GmbH<br>
				Raiffeisenstrasse. 18<br>
				70794 Filderstadt<br><br>
				Tel. +49 711 22 051 245<br>
				E-Mail: <a href='mailto:info@airport-parking-stuttgart.de'>info@airport-parking-stuttgart.de</a><br><br>
				Inhaber: Erdem Aras<br>
				Sitz des Unternehmens: Filderstadt<br>
				Steuernummer: 97003/45708<br><br>
				Diese Email einschließlich ihrer Anhänge ist vertraulich. Sie beinhaltet
				u.U. streng vertrauliche Informationen.Unberechtigtes Lesen,
				Kopieren, Speichern und Weiterleiten ist untersagt.
				Wir bitten, eine fehlgeleitete Email unverzüglich vollständig zu löschen
				und uns hierüber zu informieren.<br><br>
				Vielen Dank.<br><br>
				This email and any attachments are confidential. They may contain
				legally privileged information. Unauthorized reading, copying,
				disclosure or use is strictly prohibited. If you are not the intended
				recipient of this email, please delete its contents immediately and
				notify us.<br><br>
				Thank you.</p>";
	$headers = array('Content-Type: text/html; charset=UTF-8');
	//wp_mail( $_POST['email'], '[APS] Ihre Kundendaten wurden aktualisiert', $body, $headers );
	
	header("Refresh:0; url=/kundenkonto");
}

$customer_roles = array('customer');
$admin_roles = array('administrator', 'admin2');
$fahrer_roles = array('koordinator', 'fahrer');
$hotel_roles = array('hotel_role');

get_header(); 
?>
<style>
.update{
	background-color: #00ffcc;
	padding: 3px;
}
td{
	padding-right: 50px;
	padding-top: 30px;
	width: 33%;
}
.star{
	color: red;
}
.inactive{
	color: #007bff;
}
.m_inactive:hover{
	color: #003773;
}
.m_active{
	background-color: #3b8ae324 !important;
	color: #003773;
}
</style>

<script src="/wp-content/plugins/itweb-parking-booking/bootstrap-4.5.3-dist/js/popper.min.js"></script>
<script src="/wp-content/plugins/itweb-parking-booking/bootstrap-4.5.3-dist/js/bootstrap.min.js"></script>

<div class="body-cover">
    <div class="container">
		<div class="row">
            <div class="col-md-5">
                <div aria-label="Breadcrumbs DE" role="navigation">
                    <ul itemscope="" itemtype="https://schema.org/BreadcrumbList" class="breadcrumb">
                        <li class="active"><span class="divider icon-location"></span></li>
                        <li itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem"><a
                                    itemprop="item" href="../" class="pathway"><span itemprop="name">Startseite</span></a>
                            <span class="divider">
                <img src="<?php echo get_template_directory_uri(); ?>/inc/assets/images/arrow-parking.png" alt="">
              </span>
                            <meta itemprop="position" content="1">
                        </li>
                        <li itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem"
                            class="active">
                            <span itemprop="name"> Mein Konto </span>
                            <meta itemprop="position" content="2">
                        </li>
                    </ul>
                </div>
            </div>
        </div>
		<div class="row">
			<div class="col-sm-12 col-md-12 col-lg-3">
				<div class="left-sidebar">
					<div class="panel panel-primary">
						<?php if (array_intersect($customer_roles, $current_user->roles)): ?>
							<div class="panel-body ad-airport m_inactive">
								<a class="wp-menu-image dashicons-before dashicons-admin-site" aria-hidden="true" href="/kundendashboard/">Aktuelles</a>
							</div>
							<div class="panel-body ad-airport m_inactive">
								<details>
									<summary class="wp-menu-image dashicons-before dashicons-edit" aria-hidden="true">Neue Buchung</summary>								
									<div class="panel-body ad-airport">
										<form action="/results/" method="GET">
											<div class="form-group formgroup-rez">
												<img src="<?php echo get_template_directory_uri(); ?>/inc/assets/images/calendar-small.png" id="input_img-form" alt="">

												<label for="" class="icon-text text-bold">Anreisedatum</label>
												<input id="" name="datefrom" type="text" class="form-control typeahead single-datepicker" autocomplete="off" required>
											</div>
											<div class="form-group formgroup-rez">
												<img src="<?php echo get_template_directory_uri(); ?>/inc/assets/images/calendar-small.png" id="input_img-form" alt="" required>

												<label for="" class="icon-text text-bold">Abreisedatum</label>
												<input id="selectedAirport" name="dateto" type="text" class="form-control typeahead single-datepicker" autocomplete="off" required>
											</div>
											<button class="btn btn-primary btn-md pl-full-width btn-suchen pl-margin-top-10" type="submit">Suchen</button>
										</form>
									</div>
								</details>
							</div>
							<div class="panel-body ad-airport m_inactive">
								<a class="wp-menu-image dashicons-before dashicons-book" aria-hidden="true" href="/kundenbuchungen/">Buchungen</a>
							</div>
							<div class="panel-body ad-airport m_inactive">
								<a class="wp-menu-image dashicons-before dashicons-no" aria-hidden="true" href="/kundenstornos/">Stornos</a>
							</div>						
							<div class="panel-body ad-airport m_active">
								<a class="wp-menu-image dashicons-before dashicons-admin-users" aria-hidden="true" href="/kundenkonto/">Mein Konto</a>
							</div>
						<?php endif; ?>
					</div>					
				</div>
			</div>
			<div class="col-sm-12 col-md-12 col-lg-9">
				<?php if (!array_intersect($customer_roles, $current_user->roles)): ?>
					<?php if (array_intersect($admin_roles, $current_user->roles)): ?>
						<a href="<?php echo '/wp-admin/admin.php?page=dashboard' ?>" class="btn btn-info d-block w-100" >Weiter zur Verwaltung</a>
					<?php endif; ?>
					<?php if (array_intersect($hotel_roles, $current_user->roles)): ?>
						<a href="<?php echo '/partner-dashboard/' ?>" class="btn btn-info d-block w-100" >Weiter zur Verwaltung</a>
					<?php endif; ?>
					<?php if (array_intersect($fahrer_roles, $current_user->roles)): ?>						
						<a href="<?php echo '/wp-admin/admin.php?page=fahrerlisten' ?>" class="btn btn-info d-block w-100" >Weiter zu den Listen</a>
					<?php endif; ?>
				<?php endif; ?>
				
				<?php if ( is_user_logged_in() ): ?>
					<?php if (array_intersect($customer_roles, $current_user->roles)): ?>
						<h3>Kundendetails</h3>
						<?php if(isset($_POST['update'])): ?>
							<p class="update">Ihre Daten wurden aktualisiert.</p>
						<?php endif; ?>
						<form action="/kundenkonto" method="POST">
							<input type="hidden" name="email" placeholder="" class="" value="<?php echo esc_html( $current_user->user_email ) ?>">
							<table>
								<tr>
									<td>
										<label for="email">E-Mail</label><br>
										<input type="email" name="email" placeholder="" class="form-control" value="<?php echo esc_html( $current_user->user_email ) ?>" disabled>
									</td>
									<td>
										<label for="user_name">Benutzername</label><br>
										<input type="text" name="user_name" placeholder="" size="50" class="form-control" value="<?php echo esc_html( $current_user->user_login ) ?>" disabled>
									</td>								
									<td>
										<label for="password">Passwort ändern</label><br>
										<input type="password" name="password" placeholder="" class="form-control" value="">
									</td>
								</tr>
								<?php if($all_meta_for_user['gkunde'][0]): ?>
								<tr>								
									<td>
										<label for="firmenname">Firma</label><br>
										<input type="text" name="firmenname" placeholder="" class="form-control" value="<?php echo esc_html( $all_meta_for_user['billing_company'][0] ) ?>" >
									</td>
									<td>
										<label for="ust_id">USt-IdNr.</label><br>
										<input type="text" name="ust_id" placeholder="" class="form-control" value="<?php echo esc_html( $all_meta_for_user['ust_id'][0] ) ?>" >
									</td>
								</tr>
								<?php endif; ?>
								<tr>
									<td>
										<label for="gender">Anrede<span class="star">*</span></label><br>
										<input type="radio" name="billing_grander" value="male" placeholder="" class="" <?php echo $all_meta_for_user['billing_grander'][0] == 'male' ? 'checked' : ''?> required> Herr
										<input type="radio" name="billing_grander" value="female" placeholder="" class="" <?php echo $all_meta_for_user['billing_grander'][0] == 'female' ? 'checked' : ''?> required> Frau
									</td>
									<td>
										<label for="first_name">Vorname<span class="star">*</span></label><br>
										<input type="text" name="first_name" placeholder="" class="form-control" value="<?php echo esc_html( $current_user->user_firstname ) ?>" required>
									</td>
									<td>
										<label for="last_name">Nachname<span class="star">*</span></label><br>
										<input type="text" name="last_name" placeholder="" class="form-control" value="<?php echo esc_html( $current_user->user_lastname ) ?>" required>
									</td>
								</tr>
								<tr>
									<td>
										<label for="billing_address_1">Straße<span class="star">*</span></label><br>
										<input type="text" name="billing_address_1" placeholder="" class="form-control" value="<?php echo esc_html( $all_meta_for_user['billing_address_1'][0] ) ?>" required>
									</td>
									<td>
										<label for="billing_postcode">PLZ<span class="star">*</span></label><br>
										<input type="text" name="billing_postcode" placeholder="" class="form-control" value="<?php echo esc_html( $all_meta_for_user['billing_postcode'][0] ) ?>" required>
									</td>
									<td>
										<label for="billing_city">Ort<span class="star">*</span></label><br>
										<input type="text" name="billing_city" placeholder="" class="form-control" value="<?php echo esc_html( $all_meta_for_user['billing_city'][0] ) ?>" required>
									</td>
								</tr>
							</table>
							<input type="checkbox" name="agb" class="" value="agb" required> Ich habe die <a href="/agb" target="_blank">Allgemeine Geschäftsbedingungen</a> gelesen und stimme diese zu!<span class="star">*</span><br>
							<input type="checkbox" name="ds" class="" value="ds" required> Ich habe die <a href="/datenschutzrichtlinien" target="_blank">Ich habe die <a href="/agb" target="_blank">Datenschutzerklärung</a> zur Kenntnis genommen. Ich stimme zu, dass meine Angaben und Daten zur Bearbeitung meiner Buchung elektronisch erhoben und gespeichert werden.<span class="star">*</span><br>
							<input type="checkbox" name="newsletter" class="" value="newsletter" <?php echo $all_meta_for_user['newsletter'][0] != null ? "checked" : "" ?>> News-Letter (für Rabattaktionen, Gutscheine etc)<br>
							<br>
							<button class="btn btn-primary pl-margin-top-10" name="update" value="1">Aktualisieren</button>
						</form>
					<?php endif; ?>
				<?php else: ?>
				   <p>Sie sind nicht eingeloggt. <a href="/login">Hier Einloggen</a></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<?php get_footer() ?>