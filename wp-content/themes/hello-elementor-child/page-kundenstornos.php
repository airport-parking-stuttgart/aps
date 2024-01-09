<?php
/**
* Template Name: User.Stornos
*/
ini_set("memory_limit", "1024M");
global $wpdb;


$user_id = get_current_user_id();

$args = array(
    'customer_id' => $user_id,
	'status'      => 'cancelled',
    'limit' => -1, // to retrieve _all_ orders by this user
);
$orders = wc_get_orders($args);

if(isset($_GET["token"])){
	$sql_token = " HAVING
			MAX(CASE WHEN pm.meta_key = 'token' THEN pm.meta_value END) = '".$_GET["token"]."' ";
	if(isset($_GET["booking_date"])) unset($_GET["booking_date"]);
	if(isset($_GET["arrival_date"])) unset($_GET["arrival_date"]);
}
else{
	$sql_token = " ";
}

if(isset($_GET["arrival_date"])){
	$b_date = explode(" - ", $_GET["arrival_date"]);
	$b_date[0] = date_format(date_create($b_date[0]), 'Y-m-d');
	$b_date[1] = date_format(date_create($b_date[1]), 'Y-m-d');	
	$sql_date = " AND DATE_FORMAT(o.date_from,'%Y-%m-%d') BETWEEN '".$b_date[0]."' AND '".$b_date[1]."' ";
	if(isset($_GET["booking_date"])) unset($_GET["booking_date"]);
}
else{
	$sql_date = " ";
}

if(isset($_GET["booking_date"])){
	$b_date = explode(" - ", $_GET["booking_date"]);
	$b_date[0] = date_format(date_create($b_date[0]), 'Y-m-d');
	$b_date[1] = date_format(date_create($b_date[1]), 'Y-m-d');	
	$sql_date = " AND DATE_FORMAT(p.post_date,'%Y-%m-%d') BETWEEN '".$b_date[0]."' AND '".$b_date[1]."' ";
	if(isset($_GET["arrival_date"])) unset($_GET["arrival_date"]);
}
else{
	$sql_date = " ";
}

$i = 0;
foreach ($orders as $order){
	$sql = "SELECT @num := @num + 1 AS position,
		p.ID AS order_id,
		DATE(p.post_date) AS date_created,
		p.post_status AS Status,
		o.date_from AS datefrom, o.date_to AS dateto,
		pl.parklot AS parklotname,
		pl.product_id AS lot_id,
		pl.parkhaus AS type,
		MAX(CASE WHEN pm.meta_key = 'token' THEN pm.meta_value END) AS Token,
		MAX(CASE WHEN pm.meta_key = '_billing_first_name' THEN pm.meta_value END) AS customer_firstname,
		MAX(CASE WHEN pm.meta_key = '_billing_last_name' THEN pm.meta_value END) AS customer_lastname,
		MAX(CASE WHEN pm.meta_key = '_billing_email' THEN pm.meta_value END) AS Email,
		MAX(CASE WHEN pm.meta_key = '_order_total' THEN pm.meta_value END) AS Betrag,
		MAX(CASE WHEN pm.meta_key = '_payment_method_title' THEN pm.meta_value END) AS Bezahlmethode
					
		FROM {$wpdb->prefix}posts p
		JOIN (SELECT @num := 0 FROM DUAL) AS n ON 1=1
		LEFT JOIN {$wpdb->prefix}postmeta pm ON pm.post_id = p.ID
		INNER JOIN {$wpdb->prefix}itweb_orders o ON o.order_id = p.ID
		INNER JOIN {$wpdb->prefix}itweb_parklots pl ON pl.product_id = o.product_id
		WHERE
		p.post_type = 'shop_order' AND o.order_id = ".$order->get_id()." 
		".$sql_date."
		GROUP BY p.ID ". $sql_token ."
		ORDER BY position DESC, date_created DESC
		";
	$bookings[$i] = $wpdb->get_row($sql);
	$i++;
}

$startDate = new DateTime(date('Y-m-d'));
$startDate->modify('+1 day');

$endDate = new DateTime(date('Y-m-d'));
$endDate->modify('+2 day');

//echo "<pre>"; print_r($bookings); echo "</pre>";

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

.booling_tbl{
	overflow:scroll; 
	white-space: nowrap;
}

th{
	background-color: #3b8ae3;
	color: white;
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
                            <span itemprop="name"> Stornos </span>
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
						<div class="panel-body ad-airport m_active">
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
				<div class="col-sm-12 col-md-12 col-lg-12 booling_tbl">
					<?php if(count($bookings) > 0): ?>
					<table class="table">
						<thead>
						<tr>
							<th>Nr.</th>
							<th>Buchung</th>
							<th>Datum</th>
							<th>Kunde</th>
							<th>Parkplatzname</th>
							<th>Typ</th>
							<th>Anreisedatum</th>
							<th>Anreisezeit</th>
							<th>Abreisedatum</th>
							<th>Abreisezeit</th>
							<th>Parkdauer</th>
							<th>Personen</th>           
							<th>Preis</th>
							<th>Zahlungsmethode</th>
							<th>Status</th>
						</tr>
						</thead>
						<tbody>
							<?php $p = 1; foreach ($bookings as $booking) : ?>
							<?php
								if($booking == null || $booking == "")
									continue;
								if($booking->Status == 'wc-processing')
									$status = "bearbeitet";
								elseif($booking->Status == 'wc-cancelled') 
									$status = "storniert";
								elseif($booking->Status == 'wc-refunded')
									$status = "erstattet";
								elseif($booking->Status == 'wc-on-hold')
									$status = "bearbeitet";
								else
									$status = "abgebrochen";									
							?>
								<tr>
									<td><?php echo $p ?></td>
									<td><?php echo $booking->Token ?></td>
									<td><?php echo date_format(date_create($booking->date_created), 'd.m.Y') ?></td>
									<td><?php echo $booking->customer_lastname ?></td>
									<td><?php echo $booking->parklotname ?></td>
									<td><?php echo $booking->type ?></td>
									<td><?php echo date_format(date_create($booking->datefrom), 'd.m.Y') ?></td>
									<td><?php echo date_format(date_create($booking->datefrom), 'H:i') ?></td>
									<td><?php echo date_format(date_create($booking->dateto), 'd.m.Y') ?></td>
									<td><?php echo date_format(date_create($booking->dateto), 'H:i') ?></td>
									<td><?php echo getDaysBetween2Dates(
											new DateTime(date_format(date_create($booking->datefrom), 'd.m.Y')),
											new DateTime(date_format(date_create($booking->dateto), 'd.m.Y'))); ?>
									</td>
									<td><?php echo get_post_meta($booking->order_id, 'Personenanzahl', true) ?></td>               
									<td><?php echo $booking->Betrag ?> €</td>
									<td><?php echo $booking->Bezahlmethode ?></td>
									<td><?php echo $status ?></td>
								</tr>
							<?php $p++; endforeach; ?>	
						</tbody>
					</table>
					<?php else: ?>
					<p>Keine Stornos vorhanden.</p>
					<?php endif; ?>
				</div>
				<?php else: ?>
				   <p>Sie sind nicht eingeloggt. <a href="/login">Hier Einloggen</a></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<?php get_footer() ?>