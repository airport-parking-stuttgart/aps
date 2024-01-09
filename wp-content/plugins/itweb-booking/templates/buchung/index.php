<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-logo">
		<img class="adm-logo" src="<?php echo home_url(); ?>/wp-content/uploads/2021/12/APS-Logo-klein.png" alt="" width="300" height="200">
	</div>

	<?php


	$products = Database::getInstance()->getAllLots();
	$payment_methods = get_enabled_payment_methods();
	$companies = Database::getInstance()->getAllCompanies();

	$dateto = date('Y-m-d', strtotime(date('Y-m-d')));
	$datefrom = date('Y-m-d', strtotime($dateto . '-2 day'));
	if($_GET['anreiseVon'])
		$anreiseVon = date('Y-m-d', strtotime($_GET['anreiseVon']));
	if($_GET['anreiseBis'])
		$anreiseBis = date('Y-m-d', strtotime($_GET['anreiseBis']));

	if(isok($_GET, 'token')){
		$datefrom = $dateto = $anreise = $abreise = "";
		$token = $_GET['token'];
		unset($_GET['date_from']);
		unset($_GET['date_to']);
		unset($_GET['anreiseVon']);
		unset($_GET['anreiseBis']);
		unset($_GET['payment_method']);
		unset($_GET['betreiber']);
		$filter['datum_von'] = $filter['datum_bis'] = $filter['buchung_von'] = $filter['buchung_bis'] = "";
		$filter['token'] = $token;
		$allorders = Database::getInstance()->get_bookinglistV2("wc-processing", $filter, "");
	}

	if((isok($_GET, 'anreiseVon') && isok($_GET, 'anreiseBis')) || ($anreiseVon != ""&& $anreiseBis)){
		$token = $datefrom = $dateto = "";
		unset($_GET['date_from']);
		unset($_GET['date_to']);
		unset($_GET['token']);
		$filter['token'] = $filter['buchung_von'] = $filter['buchung_bis'] = "";
		$filter['datum_von'] = $anreiseVon;
		$filter['datum_bis'] = $anreiseBis;	
		if((isok($_GET, 'product'))){
			unset($_GET['betreiber']);
			$filter['product'] = $_GET['product'];
		}
		elseif((isok($_GET, 'betreiber'))){
			$filter['betreiber'] = $_GET['betreiber'];
		}
		if((isok($_GET, 'payment_method'))){
			$filter['payment_method'] = $_GET['payment_method'];
		}
		$filter['orderBy'] = "Anreisedatum";
		$allorders = Database::getInstance()->get_bookinglistV2("wc-processing", $filter, "");
	}

	if((isok($_GET, 'date_from') && isok($_GET, 'date_to')) || ($datefrom != "" && $dateto != "")){
		if($_GET['date_from'])
			$datefrom = date('Y-m-d', strtotime($_GET['date_from']));
		if($_GET['date_to'])
			$dateto = date('Y-m-d', strtotime($_GET['date_to']));
		$token = $anreise = $abreise = "";
		unset($_GET['anreiseVon']);
		unset($_GET['anreiseBis']);
		unset($_GET['token']);
		$filter['datum_von'] = $filter['datum_bis'] = $filter['token'] = "";
		$filter['buchung_von'] = $datefrom;
		$filter['buchung_bis'] = date('Y-m-d', strtotime($dateto));
		if((isok($_GET, 'product'))){
			unset($_GET['betreiber']);
			$filter['product'] = $_GET['product'];
		}
		elseif((isok($_GET, 'betreiber'))){
			$filter['betreiber'] = $_GET['betreiber'];
		}
		if((isok($_GET, 'payment_method'))){
			$filter['payment_method'] = $_GET['payment_method'];
		}
		$filter['orderBy'] = "Buchungsdatum";
		$allorders = Database::getInstance()->get_bookinglistV2("wc-processing", $filter, "");
	}
?>

	<div class="page-title itweb_adminpage_head">
		<h3>Buchungen</h3>
	</div>
	<br>
	<div class="page-body">
		<form class="form-filter" id="myForm">
			<input type="hidden" name="page" value="buchungen">
			<div class=" row my-2 ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Buchungen filtern</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-md-3 col-lg-2">
							<input type="text" name="token" placeholder="Buchung" class="form-item form-control" value="<?php echo $token != "" ? $token : ''; ?>">
						</div>
						<div class="col-sm-12 col-md-1 col-lg-1 ui-lotdata-date">
							<input type="text" id="dateFrom" name="date_from" placeholder="Buchung von" class="form-item form-control single-datepicker" value="<?php echo $datefrom != "" ? date('d.m.Y', strtotime($datefrom)) : ''; ?>">
						</div>
						<div class="col-sm-12 col-md-1 col-lg-1 ui-lotdata-date">
							<input type="text" id="dateTo" name="date_to" placeholder="Buchung bis" class="form-item form-control single-datepicker" value="<?php echo $dateto != "" ? date('d.m.Y', strtotime($dateto)) : ''; ?>">
						</div>
						<div class="col-sm-12 col-md-1 col-lg-1 ui-lotdata-date">
							<input type="text" id="arrivaldateFrom" name="anreiseVon" placeholder="Anreise von" class="form-item form-control single-datepicker" value="<?php echo $anreiseVon != "" ? date('d.m.Y', strtotime($anreiseVon)) : ''; ?>">
						</div>
						<div class="col-sm-12 col-md-1 col-lg-1 ui-lotdata-date">
							<input type="text" id="arrivaldateTo" name="anreiseBis" placeholder="Anreise bis" class="form-item form-control single-datepicker" value="<?php echo $anreiseBis != "" ? date('d.m.Y', strtotime($anreiseBis)) : ''; ?>">
						</div>
					</div>

					<div class="row my-2">
						<div class="col-sm-12 col-md-3 col-lg-2">
							<select name="product" class="form-item form-control">
								<option value="">Produkt</option>
								<?php foreach ($products as $product) : ?>
									<option value="<?php echo $product->product_id ?>" <?php echo (isset($_GET['product']) && $_GET['product'] == $product->product_id) ? ' selected' : '' ?>>
										<?php echo $product->parklot ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="col-sm-12 col-md-3 col-lg-2">
							<select name="betreiber" class="form-item form-control">
								<option value="">Betreiber</option>
								<?php foreach($companies as $company) : ?>
								<option value="<?php echo strtolower($company->short) ?>" <?php echo (isset($_GET['betreiber']) && $_GET['betreiber'] == strtolower($company->short)) ? ' selected' : '' ?>>
									<?php echo $company->short ?>
								</option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="col-sm-12 col-md-3 col-lg-2">
							<select name="payment_method" class="form-item form-control">
								<option value="">Zahlungsart</option>
								<option value="Barzahlung" <?php echo (isset($_GET['payment_method']) && $_GET['payment_method'] == "Barzahlung") ? 'selected' : '' ?>>
									Barzahlung
								</option>
								<option value="MasterCard" <?php echo (isset($_GET['payment_method']) && $_GET['payment_method'] == "MasterCard") ? 'selected' : '' ?>>
									MasterCard
								</option>
								<option value="Visa" <?php echo (isset($_GET['payment_method']) && $_GET['payment_method'] == "Visa") ? 'selected' : '' ?>>
									Visa
								</option>
								<option value="PayPal" <?php echo (isset($_GET['payment_method']) && $_GET['payment_method'] == "PayPal") ? 'selected' : '' ?>>
									PayPal
								</option>
							</select>
						</div>
						<div class="col-sm-12 col-md-3 col-lg-2">
							<button class="btn btn-primary d-block w-100" type="submit">Filter</button>
						</div>
						<div class="col-sm-12 col-md-2">
							<a href="<?php echo '/wp-admin/admin.php?page=buchungen' ?>" class="btn btn-secondary d-block w-100">Zurücksetzen</a>
						</div>
					</div>
				</div>
			</div>
		</form>
		<br><br>
		<?php if ($datefrom != "" && $dateto != "") : ?>
		<table class="bookings_bd_datatable table-responsive">
		<? else : ?>
		<table class="bookings_datatable table-responsive">
		<?php endif; ?>
			<thead>
				<tr>
					<th>Nr.</th>
					<th>Datum</th>
					<th>Produkt</th>
					<th>Buchungsnummer</th>
					<th>Name</th>
					<th>Anreisedatum</th>
					<th>Uhrzeit</th>
					<th>Abreisedatum</th>
					<th>Uhrzeit</th>
					<th>Parkdauer</th>
					<th>Personen</th>
					<th>E-Mail</th>
					<th>Zahlungsart</th>
					<th>Gebühren</th>
					<th>Service</th>
					<th>Rechnung</th>
				</tr>
			</thead>
			<tbody>
				<?php $nr = 1; foreach ($allorders as $booking) : ?>
					<?php $hasGeneratedInvoice = count(get_post_meta($booking->order_id, 'generated_invoice')) > 0; ?>
					<tr class="<?php echo $hasGeneratedInvoice ? 'mark_done' : '' ?>">
						<td>
							<?php echo $nr ?>
						</td>
						<td>
							<?php echo date('d.m.Y', strtotime($booking->Buchungsdatum)); ?>
						</td>
						<td style="background-color: <?php echo $booking->Color ?> !important">
							<?php echo $booking->Code ?>
						</td>
						<td>
							<?php echo $booking->Token ?>
						</td>
						<td>
							<?php echo $booking->Vorname . ' ' . $booking->Nachname ?>
						</td>                   
						<td>
							<?php echo $booking->Anreisedatum ? date('d.m.Y', strtotime($booking->Anreisedatum)) : "-" ?>
						</td>										
						<td>
							<?php echo $booking->Uhrzeit_von ? date('H:i', strtotime($booking->Uhrzeit_von)) : "-" ?>
						</td>                  
						<td>
							<?php echo $booking->Abreisedatum ? date('d.m.Y', strtotime($booking->Abreisedatum)) : "-" ?>
						</td>									
						<td>
							<?php echo $booking->Uhrzeit_bis ? date('H:i', strtotime($booking->Uhrzeit_bis)) : "-" ?>
						</td>					
						<td>
							<?php echo $booking->Anreisedatum != null && $booking->Abreisedatum != null && $booking->is_for != 'hotel' ? getDaysBetween2Dates(new DateTime($booking->Anreisedatum), new DateTime($booking->Abreisedatum)) : "-" ?>
						</td>										
						<td>
							<?php echo $booking->Personenanzahl ? $booking->Personenanzahl : "-" ?>
						</td>					
						<td>
							<?php echo $booking->Email ? $booking->Email : "-" ?>
						</td>
						<td>
							<?php echo $booking->Bezahlmethode ? $booking->Bezahlmethode : "-" ?>
						</td>
						<td>
							<?php echo $booking->Bezahlmethode == "Barzahlung" ? number_format($booking->Preis, 2, '.', '') : "bezahlt"; ?>
						</td>
						<td>
							<?php echo $booking->Service ? number_format($booking->Service, 2, '.', '') : "-" ?>
						</td>
						<td>
							<a target="_blank" href="/wp-admin/admin-ajax.php?action=generate_wpo_wcpdf&document_type=invoice&order_ids=<?php echo $booking->order_id ?>&_wpnonce=<?php echo wp_create_nonce('generate_wpo_wcpdf') ?>">PDF</a>
						</td>
					</tr>
				<?php $nr++; endforeach; ?>
			</tbody>
		</table>
	</div>
</div>