<?php
/**
* Template Name: User.Dashboard
*/
if(isset($_SERVER['HTTPS'])){
	$protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
}
else{
	$protocol = 'http';
}
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'];
global $wpdb;
$current_user = wp_get_current_user();
$user_id = get_current_user_id();
$args = array(
    'customer_id' => $user_id,
	'status'      => array('processing', 'on-hold'),
    'limit' => -1, // to retrieve _all_ orders by this user
);
$orders = wc_get_orders($args);


$monate = array("01"=>"Januar",
                "02"=>"Februar",
                "03"=>"M&auml;rz",
                "04"=>"April",
                "05"=>"Mai",
                "06"=>"Juni",
                "07"=>"Juli",
                "08"=>"August",
                "09"=>"September",
                "10"=>"Oktober",
                "11"=>"November",
                "12"=>"Dezember");
$i = 0;
foreach ($orders as $order){
	$sql = "SELECT 
		p.ID AS order_id,
		DATE(p.post_date) AS date_created,
		p.post_status AS Status,
		o.date_from AS datefrom, o.date_to AS dateto,
		pl.parklot AS parklotname,
		pl.adress,
		pl.product_id,
		pl.confirmation_byArrival,
		pl.confirmation_note,
		MAX(CASE WHEN pm.meta_key = '_order_total' THEN pm.meta_value END) AS Betrag,
		MAX(CASE WHEN pm.meta_key = '_payment_method_title' THEN pm.meta_value END) AS Bezahlmethode
					
		FROM {$wpdb->prefix}posts p
		LEFT JOIN {$wpdb->prefix}postmeta pm ON pm.post_id = p.ID
		INNER JOIN {$wpdb->prefix}itweb_orders o ON o.order_id = p.ID
		INNER JOIN {$wpdb->prefix}itweb_parklots pl ON pl.product_id = o.product_id
		WHERE
		p.post_type = 'shop_order' AND DATE(o.date_from) >= DATE(NOW()) AND o.order_id = ".$order->get_id()." 
		";
	$result = $wpdb->get_row($sql);
	if($result->datefrom != null){
		$bookings[$i] = $wpdb->get_row($sql);
		$i++;
	}
	
}

$key_values = array_column($bookings, 'datefrom');
array_multisort($key_values, SORT_ASC, $bookings);

$nextArrival = $bookings[0];

foreach ($orders as $order){
	$sql = "SELECT 
		p.ID AS order_id,
		DATE(p.post_date) AS date_created,
		p.post_status AS Status,
		o.date_from AS datefrom, o.date_to AS dateto,
		pl.parklot AS parklotname,
		pl.adress,
		pl.product_id,
		pl.confirmation_byDeparture,
		pl.confirmation_note,
		MAX(CASE WHEN pm.meta_key = '_order_total' THEN pm.meta_value END) AS Betrag,
		MAX(CASE WHEN pm.meta_key = '_payment_method_title' THEN pm.meta_value END) AS Bezahlmethode
					
		FROM {$wpdb->prefix}posts p
		LEFT JOIN {$wpdb->prefix}postmeta pm ON pm.post_id = p.ID
		INNER JOIN {$wpdb->prefix}itweb_orders o ON o.order_id = p.ID
		INNER JOIN {$wpdb->prefix}itweb_parklots pl ON pl.product_id = o.product_id
		WHERE
		p.post_type = 'shop_order' AND DATE(o.dateto) >= DATE(NOW()) AND o.order_id = ".$order->get_id()." 
		";
	$result = $wpdb->get_row($sql);
	if($result->dateto != null){
		$bookings[$k] = $wpdb->get_row($sql);
		$k++;
	}
}
$key_values = array_column($bookings, 'dateto');
array_multisort($key_values, SORT_ASC, $bookings);

$nextDeparture = $bookings[0];

if(date($nextDeparture->dateto) > date($nextArrival->datefrom) && date($nextArrival->datefrom) != null){
	$arrival = 1;
	$departure = 0;
	if(get_post_meta($nextArrival->order_id, 'Hinflugnummer', true) == ""){
		$hinflug = "";
		$toAdd = 1;
	}
	else
		$hinflug = get_post_meta($nextArrival->order_id, 'Hinflugnummer', true);
	
	if(get_post_meta($nextArrival->order_id, 'Rückflugnummer', true) == ""){
		$rueckglug = "";
		$toAdd = 1;
	}
	else
		$rueckglug = get_post_meta($nextArrival->order_id, 'Rückflugnummer', true);
		
	if(get_post_meta($nextArrival->order_id, 'Kennzeichen', true) == ""){
		$kfz_nr = "";
		$toAdd = 1;
	}
	else
		$kfz_nr = get_post_meta($order_id, 'Kennzeichen', true);
	
}
elseif(date($nextDeparture->dateto) != null){
	$arrival = 0;
	$departure = 1;
}
else{
	$arrival = 0;
	$departure = 0;
}

if($_POST['update'] == 1){
	if(isset($_POST['flight_departure']))
		update_post_meta($nextArrival->order_id, 'Hinflugnummer', $_POST['flight_departure']);
	if(isset($_POST['flight_outbound']))
		update_post_meta($nextArrival->order_id, 'Rückflugnummer', $_POST['flight_outbound']);
	if(isset($_POST['license_plate']))
		update_post_meta($nextArrival->order_id, 'Kennzeichen', $_POST['license_plate']);
	echo("<script>location.href = '/dashboard/';</script>");
}
//echo "<pre>"; print_r($_POST); echo "</pre>";

get_header(); 
?>

<style>

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

.grid-container {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  column-gap: 500px;
}
.grid-item{
	margin-top: 50px;
}

.nextline{
	margin-top: 150px;
}

.bookings{
	text-align: center;
	background-color: #6a6a6a;
	font-size: 20px;
	color: white;
	padding: 15px 0;
}

.flex-container {
  display: flex;
  width: 100%;
  flex-flow: row wrap;
  --justify-content: center;
}
.flex-container > div {
  margin: 10px; 
  width: 350px;
}
.flex-container > .left_part {
  text-align: center;
}
.title{
	font-size: 20px;
	font-weight: bold;
}
.arrival{
	color: green;
	font-size: 20px;
	font-weight: bold;
}
.airport-img{
	max-height: 180px;
	width: auto;
	min-height: 180px;
}



 /* The Modal (background) */
                .modal {
                    display: none;
                    /* Hidden by default */
                    position: fixed;
                    /* Stay in place */
                    z-index: 1;
                    /* Sit on top */
                    padding-top: 100px;
                    /* Location of the box */
                    left: 0;
                    top: 0;
                    width: 100%;
                    /* Full width */
                    height: 100%;
                    /* Full height */
                    overflow: auto;
                    /* Enable scroll if needed */
                    background-color: rgb(0, 0, 0);
                    /* Fallback color */
                    background-color: rgba(0, 0, 0, 0.4);
                    /* Black w/ opacity */
                }

                /* Modal Content */
                .modal-content {
                    position: relative;
                    background-color: #fefefe;
                    margin: auto;
                    padding: 0;
                    border: 1px solid #888;
                    width: 70%;
                    box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
                    -webkit-animation-name: animatetop;
                    -webkit-animation-duration: 0.4s;
                    animation-name: animatetop;
                    animation-duration: 0.4s;

                }

                /* Add Animation */
                @-webkit-keyframes animatetop {
                    from {
                        top: -300px;
                        opacity: 0
                    }

                    to {
                        top: 0;
                        opacity: 1
                    }
                }

                @keyframes animatetop {
                    from {
                        top: -300px;
                        opacity: 0
                    }

                    to {
                        top: 0;
                        opacity: 1
                    }
                }

                /* The Close Button */
                .close {
                    color: white;
                    float: right;
                    font-size: 28px;
                    font-weight: bold;

                }

                .close:hover,
                .close:focus {
                    color: #000;
                    text-decoration: none;
                    cursor: pointer;
                }

                .modal-header {
                    padding: 2px 16px;
                    /* background-color: #5cb85c; */
                    color: white;
                }

                .modal-header .close {
                    margin-top: 8px !important;
                    color: #3b8ae3;
                }

                .btn-modal {
                    background-color: #3b8ae3 !important;
                    color: #fff;
                    border: none;
                }

                .modal-body {
                    padding: 30px 16px 16px 16px;
                }

                .modal-body input {
                    border: none;
                    border-bottom: 1px solid #B5B5B5;
                    padding: 10px;
                    width: 100%;
                }

                .modal-body input:focus {
                    outline: none !important;
                }

                .modal-footer {
                    padding: 2px 16px;
                    /* background-color: #5cb85c; */
                    color: white;
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
                            <span itemprop="name"> Aktuelles </span>
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
						<div class="panel-body ad-airport m_active">
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
						<div class="panel-body ad-airport m_inactive">
							<a class="wp-menu-image dashicons-before dashicons-admin-users" aria-hidden="true" href="/kundenkonto/">Mein Konto</a>
						</div>
					</div>					
				</div>
			</div>
			<div class="col-sm-12 col-md-12 col-lg-9">
				<?php if ( is_user_logged_in() ): ?>
					<?php if($arrival == 1 && $departure == 0): ?>
						<div class="flex-container">
							<div class="left_part">
								<span class="title">Ihre nächste Anreise ist</span><br>
								<span>am <span class="arrival"><?php echo date('d.m.Y', strtotime($nextArrival->datefrom)) ?></span></span> um
								<span><span class="arrival"><?php echo date('H:i', strtotime($nextArrival->datefrom)); ?></span></span><br><br>
								<span class="title">Ihr Parkplatz</span><br>
								<span><?php echo $nextArrival->parklotname; ?></span><br>
								<span><?php echo $nextArrival->adress; ?></span><br>
								<a href="#"><span class="card-body__starspan" id="myBtn">Karte anzeigen</span></a>
							</div>
							<div class="right_part">
								<?php 
								$product = wc_get_product($nextArrival->product_id);
								$image_id = $product->get_image_id();
								$image_url = wp_get_attachment_image_url($image_id, 'full');
								$image_url = $image_url ? $image_url : '/wp-content/uploads/woocommerce-placeholder-600x600.png';
								?>
								<img class="airport-img" src="<?php echo $image_url ?>" alt="">
							</div>
						</div>
						<?php if($toAdd == 1): ?>
							<hr>
							<div class="row">
								<div class="col-sm-12 col-md-12 col-lg-12">
									<span class="title">Bitte ergänzen Sie die fehlenden Angaben</span><br><br>
								</div>
							</div>
							<form action="/dashboard" method="POST">
								<div class="row">
									<div class="col-sm-12 col-md-12 col-lg-12">
										<div class="row">
											<?php if($hinflug == ""): ?>
												<div class="col-sm-12 col-md-12 col-lg-3">
													<label for="flight_departure">Flugnummer Hinflug</label><br>
													<input type="text" class="form-control" name="flight_departure">
												</div>
											<?php endif; ?>
											<?php if($rueckglug == ""): ?>
												<div class="col-sm-12 col-md-12 col-lg-3">
													<label for="flight_outbound">Flugnummer Rückflug</label><br>
													<input type="text" class="form-control" name="flight_outbound">
												</div>
											<?php endif; ?>
											<?php if($kfz_nr == ""): ?>
												<div class="col-sm-12 col-md-12 col-lg-3">
													<label for="license_plate">KFZ-Kennzeichen</label><br>
													<input type="text" class="form-control" name="license_plate">
												</div>
											<?php endif; ?>										
										</div>
									</div>
								</div>
								<br>
								<div class="row">
									<div class="col-sm-12 col-md-12 col-lg-2">
										<input type="hidden" name="update" value="1">
										<input class="btn btn-primary edit-order-btn" type="submit" value="Eintragen">
									</div>
								</div>
							</form>
						<?php endif; ?>
						<hr>
						<?php
						if(date_format(date_create($nextArrival->datefrom), 'Y-m-d') < '2023-12-31')
							$hinweis = "Ab der 5. Person wird ein Aufschlag in Höhe von 5,00 Euro pro Fahrt und Person erhoben.
										Bei einer verspäteten Abreise werden 10,00 EUR für jeden zusätzlichen Tag berechnet.
										Bei Sperrgepäck entsteht ein Aufpreis von 5,00 Euro pro Fahrt.";
						elseif(date_format(date_create($nextArrival->datefrom), 'Y-m-d') > '2024-01-01')
							$hinweis = "Ab der 5. Person wird ein Aufschlag in Höhe von 10,00 Euro pro Fahrt und Person erhoben.
										Bei einer verspäteten Abreise werden 15,00 EUR für jeden zusätzlichen Tag berechnet.
										Bei Sperrgepäck entsteht ein Aufpreis von 10,00 Euro pro Fahrt.";
						else
							$hinweis = "";
						?>
										<div class="row">
							<div class="col-sm-12 col-md-12 col-lg-12">
								<span class="title">Hinweis</span><br>
								<span><?php echo $hinweis . " " .  $nextArrival->confirmation_note; ?></span><br>
							</div>
						</div>
						<br><br>
						<div class="row">
							<div class="col-sm-12 col-md-12 col-lg-12">
								<span class="title">Bei Anreise</span><br>
								<span><?php echo $nextArrival->confirmation_byArrival; ?></span><br>
							</div>
						</div>
					<?php elseif($arrival == 0 && $departure == 1): ?>
						<div class="flex-container">
							<div class="left_part">
								<span class="title">Ihre Abreise ist</span><br>
								<span>am <span class="arrival"><?php echo date('d.m.Y', strtotime($nextDeparture->dateto)) ?></span></span> um
								<span><span class="arrival"><?php echo date('H:i', strtotime($nextDeparture->dateto)); ?></span></span><br><br>
								<span class="title">Ihr Parkplatz</span><br>
								<span><?php echo $nextDeparture->parklotname; ?></span><br>
								<span><?php echo $nextDeparture->adress; ?></span><br>
								<a href="#"><span class="card-body__starspan" id="myBtn">Karte anzeigen</span></a>
							</div>
							<div class="right_part">
								<?php 
								$product = wc_get_product($nextDeparture->product_id);
								$image_id = $product->get_image_id();
								$image_url = wp_get_attachment_image_url($image_id, 'full');
								$image_url = $image_url ? $image_url : '/wp-content/uploads/woocommerce-placeholder-600x600.png';
								?>
								<img class="airport-img" src="<?php echo $image_url ?>" alt="">
							</div>
						</div>
						<hr>
						<div class="row">
							<div class="col-sm-12 col-md-12 col-lg-12">
								<span class="title">Hinweis</span><br>
								<span><?php echo $nextDeparture->confirmation_note; ?></span><br>
							</div>
						</div>
						<br><br>
						<div class="row">
							<div class="col-sm-12 col-md-12 col-lg-12">
								<span class="title">Bei Abreise</span><br>
								<span><?php echo $nextDeparture->confirmation_byDeparture; ?></span><br>
							</div>
						</div>
					<?php else: ?>
						<p>Es sind keine Informationen vorhanden.</p>
					<?php endif; ?>
				<?php else: ?>
				   <p>Sie sind nicht eingeloggt. <a href="/login">Hier Einloggen</a></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<div id="myModal" class="modal">
                <!-- Modal content -->
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="row width-100">
                            <div class="col-11">
                                <h2>Routenplaner – Sicher am Ziel ankommen</h2>
                            </div>
                            <div class="col-1">
                                <span class="close" id="btn_close">&times;</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-4">
                                <input type="text" placeholder="Enter your start address">
                            </div>
                            <div class="col-4">
                                <input type="text" value="<?php echo $nextArrival->adress ?>" placeholder="Startadresse eingeben" readonly>
                            </div>
                            <div class="col-3">
                                <button class="btn btn-primary btn-md pl-full-width btn-modal pl-margin-top-10-1">
                                    Calculate route
                                </button>
                            </div>
                        </div>
                        <div class="row mt-5">
                            <div class="col-12">
                                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d84132.34001823139!2d9.107175739267596!3d48.779300952403624!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4799db34c1ad8fd3%3A0x79d5c11c7791cfe4!2sStuttgart%2C%20Germany!5e0!3m2!1sen!2s!4v1593513117349!5m2!1sen!2s"
                                        width="100%" height="450" frameborder="0" style="border:0;" allowfullscreen=""
                                        aria-hidden="false" tabindex="0"></iframe>
                            </div>
                        </div>

                    </div>
                    <!-- <div class="modal-footer">
                        <h3>Modal Footer</h3>
                    </div> -->
                </div>
            </div>

<?php get_footer() ?>
 
<script>
                // Get the modal
                var modal = document.getElementById("myModal");

                // Get the button that opens the modal
                var btn = document.getElementById("myBtn");

                // Get the <span> element that closes the modal
                var span = document.getElementById("btn_close");

                // When the user clicks the button, open the modal
                btn.onclick = function () {
                    modal.style.display = "block";
                }

                // When the user clicks on <span> (x), close the modal
                span.onclick = function () {
                    modal.style.display = "none";
                }

                // When the user clicks anywhere outside of the modal, close it
                window.onclick = function (event) {
                    if (event.target == modal) {
                        modal.style.display = "none";
                    }
                }
            </script>