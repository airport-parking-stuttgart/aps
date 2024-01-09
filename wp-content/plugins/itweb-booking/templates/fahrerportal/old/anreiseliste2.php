<?php
//$products = Database::getInstance()->getProducts();
// Get All Orders
ini_set("memory_limit", "1024M");
$filter['list'] = 1;

$dateFrom = isok($_GET, 'dateFrom') ? dateFormat($_GET['dateFrom']) : date('Y-m-d');
$dateTo = isok($_GET, 'dateTo') ? dateFormat($_GET['dateTo']) : date('Y-m-d');
$filter['datum_von'] = $dateFrom;
$filter['datum_bis'] = $dateTo;
$filter['type'] = "shuttle";
$filter['filter_product'] = $_GET['product'];
$filter['searchFrom'] = $_GET['searchFrom'];

$_SESSION['dateFrom'] = $dateFrom;
$_SESSION['dateTo'] = $dateTo;

$allorders = Database::getInstance()->getBookings2($filter);

$data = [];
foreach ($allorders as $booking) {

	// filter by product id
	// if (isset($_GET['product']) && !empty($_GET['product'])) {
	// 	if ($_GET['product'] == phg && ($booking->Produkt != 537 && $booking->Produkt != 595 && $booking->Produkt != 621 && $booking->Produkt != 683 && $booking->Produkt != 592 && $booking->Produkt != 3080 && $booking->Produkt != 624 &&
	// 		$booking->Produkt != 41453 && $booking->Produkt != 41577 && $booking->Produkt != 41466 && $booking->Produkt != 41581)) {
	// 		continue;
	// 	} elseif ($_GET['product'] == ph && ($booking->Produkt != 537 && $booking->Produkt != 595 && $booking->Produkt != 621 && $booking->Produkt != 683 && $booking->Produkt != 41453 && $booking->Produkt != 41577)) {
	// 		continue;
	// 	} elseif ($_GET['product'] == pho && ($booking->Produkt != 592 && $booking->Produkt != 3080 && $booking->Produkt != 624 && $booking->Produkt != 41466 && $booking->Produkt != 41581)) {
	// 		continue;
	// 	} elseif ($_GET['product'] == ppb && ($booking->Produkt != 619 && $booking->Produkt != 3081 && $booking->Produkt != 24609 && $booking->Produkt != 41468 && $booking->Produkt != 41584)) {
	// 		continue;
	// 	} elseif ($_GET['product'] == pps && ($booking->Produkt != 873 && $booking->Produkt != 3082 && $booking->Produkt != 901 && $booking->Produkt != 41470 && $booking->Produkt != 41582 && $booking->Produkt != 45856)) {
	// 		continue;
	// 	} elseif ($_GET['product'] == ost && ($booking->Produkt != 24222 && $booking->Produkt != 24224 && $booking->Produkt != 24261 && $booking->Produkt != 24226 && $booking->Produkt != 24228 && $booking->Produkt != 24263 &&
	// 		$booking->Produkt != 41472 && $booking->Produkt != 41580 && $booking->Produkt != 41474 && $booking->Produkt != 41585 && $booking->Produkt != 41402 && $booking->Produkt != 41403)) {
	// 		continue;
	// 	} elseif ($_GET['product'] == ostph && ($booking->Produkt != 24222 && $booking->Produkt != 24224 && $booking->Produkt != 24261 && $booking->Produkt != 41472 && $booking->Produkt != 41580 && $booking->Produkt != 41402)) {
	// 		continue;
	// 	} elseif ($_GET['product'] == ostnu && ($booking->Produkt != 24226 && $booking->Produkt != 24228 && $booking->Produkt != 24263 && $booking->Produkt != 41474 && $booking->Produkt != 41585 && $booking->Produkt != 41403)) {
	// 		continue;
	// 	}
	// }

	if ($booking->Status == "wc-cancelled") {
		$anreisezeit = date('H:i', strtotime("23:59"));
		$color = "#ff0000";
	} else {
		$anreisezeit = date('H:i', strtotime($booking->Uhrzeit_von));
		$color = $booking->Color;
	}

	if ($booking->Vorname == null)
		$customor = $booking->Nachname;
	elseif ($booking->Nachname == null)
		$customor = $booking->Vorname;
	elseif (strlen($booking->Nachname) < 2)
		$customor = $booking->Vorname;
	elseif (strlen($booking->Nachname) > 2)
		$customor = $booking->Nachname;
	else
		$customor = $booking->Nachname;


	$additionalPrice = "0.00";
	$services = Database::getInstance()->getBookingMetaAsResults($booking->order_id, 'additional_services');
	if (count($services) > 0) {
		foreach ($services as $v) {
			$s = Database::getInstance()->getAdditionalService($v->meta_value);
			$additionalPrice += $s->price;
		}
	}

	$_SESSION['additionalPrice'] = $additionalPrice;

	$data[] = [
		'Nr.' => $booking->order_id,
		'PCode.' => $booking->Code, //get_field('prefix', array_values($order->get_items())[0]->get_product_id()),
		'Buchung' => $booking->Token,
		'Kunde' => $customor,
		'Anreisedatum' => dateFormat($booking->Anreisedatum, 'de'),
		'Anreisezeit' => $anreisezeit,
		'Personen' => $booking->Personenanzahl,
		'Parkplatz' => $booking->Parkplatz,
		'Abreisedatum' => $booking->AbreisedatumEdit,
		'AbreisedatumPH' => $booking->Abreisedatum,
		'Rückflugnummer' => $booking->RückflugnummerEdit,
		'Landung' => $booking->Uhrzeit_bis_Edit,
		'Betrag' => $booking->Betrag, // . ' ' . $order->get_currency(),
		'Fahrer' => $booking->FahrerAn,
		'Sonstiges' => $booking->Sonstige_1,
		'Service' => $additionalPrice,
		'Status' => $booking->Status,
		'Bearbeitet' => $booking->editByArr,
		'Aktion' => '',
		'Color' => $color,
		'is_for' => $booking->is_for
	];
}

/*
// Obtain a list of columns
foreach ($data as $key => $row) {
	$bDate[$key] = $row['Anreisedatum'];
	$arr[$key]  = $row['Anreisezeit'];
	$token[$key] = $row['Buchung'];
}

// Sort the data with volume descending, edition ascending
array_multisort($bDate, SORT_ASC, $arr, SORT_ASC, $token, SORT_DESC, $data);
*/
$user = wp_get_current_user();

if ($user->user_login == 'aras' || $user->user_login == 'cakir' || $user->user_login == 'sergej')
	$editOK = "";
else
	$editOK = "readonly";

?>

<style>
	.dt-buttons,
	.dataTables_paginate,
	.dataTables_info,
	#arrivalBooking_filter {
		display: none !important;
	}

	#customPagination {
		margin-top: 30px;
		float: right;
	}

	.search {
		position: absolute;
		right: 20px;
	}
</style>


<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-title itweb_adminpage_head">
		<h3>Anreiseliste Shuttle </h3>
	</div>

	<div class="page-body">
		<form class="form-filter" id="myForm">
			<input type="hidden" name="page" value="anreiseliste">
			<div class="row ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Nach Datum filtern</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-md-1 ui-lotdata-date">
							<input type="text" id="dateFrom" name="dateFrom" placeholder="Datum von" class="form-item form-control single-datepicker" value="<?php echo dateFormat($dateFrom, 'de') ?>">
						</div>
						<div class="col-sm-12 col-md-1 ui-lotdata-date">
							<input type="text" id="dateTo" name="dateTo" placeholder="Datum bis" class="form-item form-control single-datepicker" value="<?php echo dateFormat($dateTo, 'de') ?>">
						</div>
						<div class="col-sm-12 col-md-2">
							<select name="product" class="form-item form-control">
								<option value="">Standort</option>
								<option value="phg" <?php echo (isset($_GET['product']) && $_GET['product'] == 'phg') ? ' selected' : '' ?>>
									<?php echo 'PH/O' ?>
								</option>
								<option value="ph" <?php echo (isset($_GET['product']) && $_GET['product'] == 'ph') ? ' selected' : '' ?>>
									<?php echo ' - PH' ?>
								</option>
								<option value="pho" <?php echo (isset($_GET['product']) && $_GET['product'] == 'pho') ? ' selected' : '' ?>>
									<?php echo ' - PHO' ?>
								</option>
								<option value="ppb" <?php echo (isset($_GET['product']) && $_GET['product'] == 'ppb') ? ' selected' : '' ?>>
									<?php echo 'PPB' ?>
								</option>
								<option value="pps" <?php echo (isset($_GET['product']) && $_GET['product'] == 'pps') ? ' selected' : '' ?>>
									<?php echo 'PPS' ?>
								</option>
								<option value="ost" <?php echo (isset($_GET['product']) && $_GET['product'] == 'ost') ? ' selected' : '' ?>>
									<?php echo 'OST' ?>
								</option>
								<option value="ostph" <?php echo (isset($_GET['product']) && $_GET['product'] == 'ostph') ? ' selected' : '' ?>>
									<?php echo ' - OSTPH' ?>
								</option>
								<option value="ostnu" <?php echo (isset($_GET['product']) && $_GET['product'] == 'ostnu') ? ' selected' : '' ?>>
									<?php echo ' - OSTNÜ' ?>
								</option>
							</select>
						</div>

						<div class="col-sm-12 col-md-1">
							<button class="btn btn-primary d-block w-100" type="submit">Filter</button>
						</div>
						<div class="col-sm-12 col-md-2">
							<a href="<?php echo '/wp-admin/admin.php?page=anreiseliste' ?>" class="btn btn-secondary d-block w-100">Zurücksetzen</a>
						</div>
					</div><br>
					<div class="row">
						<div class="col-sm-12 col-md-3 col-lg-2">
							<a href="<?php echo '/wp-admin/admin.php?page=abreiseliste' ?>" class="btn btn-primary d-block w-100">Abreiseliste Shuttle</a>
						</div>
						<!--
						<div class="col-sm-12 col-md-3 col-lg-2">                    
							<a href="<?php echo '/wp-admin/admin.php?page=anreiseliste-valet' ?>" class="btn btn-primary d-block w-100" >Anreiseliste Valet</a>
						</div>
						<div class="col-sm-12 col-md-3 col-lg-2">                    
							<a href="<?php echo '/wp-admin/admin.php?page=abreiseliste-valet' ?>" class="btn btn-primary d-block w-100" >Abreiseliste Valet</a>
						</div>
						-->
					</div>
				</div>
			</div>

			<div class="search">
				<label>Suchen: </label>
				<input type="text" id="search" name="searchFrom" value="<?= $_GET['searchFrom']; ?>" class="">
			</div>


		</form>

		<div class="btn">
			<form action="<?= get_site_url() . '/wp-content/plugins/itweb-booking/templates/fahrerportal/anreiseliste-excel.php'; ?>" method="post">
				<button type="submit" id="btnExport" value="Export to Excel" class="btn btn-success">Excel</button>
			</form>
		</div>

		<div class="btn">
			<form action="<?= get_site_url() . '/wp-content/plugins/itweb-booking/templates/fahrerportal/anreiseliste-pdf.php'; ?>" method="post">
				<button type="submit" id="btnExport" value="Export to PDF" class="btn btn-success">Anreiseliste exportieren</button>
			</form>
		</div>

		<table class="table-responsive" id="arrivalBooking">
			<thead>
				<tr>
					<?php if (count($data) > 0) : ?>
						<?php foreach ($data[0] as $key => $value) : ?>
							<?php if ($key == "Color" || $key == "AbreisedatumPH" || $key == "is_for") continue; ?>
							<?php //if(($user->user_login != 'aras' || $user->user_login != 'cakir' || $user->user_login != 'sergej') && $key == "Bearbeitet") continue; 
							?>
							<th><?php echo $key ?></th>
						<?php endforeach; ?>
					<?php endif; ?>
				</tr>
			</thead>
			<tbody>
				<?php $i = 1;
				foreach ($data as $order) : ?>

					<tr style="background-color: <?php echo $order['Color'] ?> !important" export-color="<?php echo $order['Color'] ?>" class="row<?php echo $i % 2; ?>">
						<input type="hidden" class="order-nr" value="<?php echo $order['Nr.'] ?>">
						<td class="nr" export-color="<?php echo $order['Color'] ?>">
							<?php echo $i ?>
						</td>
						<td class="order-pcode">
							<?php echo $order['PCode.']; ?>
						</td>
						<td class="order-token">
							<?php echo $order['Buchung'] ?>
						</td>
						<td class="order-kunde">
							<input type="text" style="width:150px;" value="<?php echo strip_tags($order['Kunde']) ?>" class="transparent-input"><span style="display: none;"><?php echo strip_tags($order['Kunde']) ?></span>
						</td>
						<td class="order-datefrom">
							<input type="text" style="width:115px;" class="anListeDateFrom transparent-input single-datepicker" value="<?php echo $order['Anreisedatum'] ?>" readonly><span style="display: none;"><?php echo $order['Anreisedatum'] ?></span>
						</td>
						<td class="order-timefrom">
							<input type="time" style="width:100px;" value="<?php echo $order['Anreisezeit'] ?>" class="transparent-input" placeholder="00:00"><span style="display: none;"><?php echo $order['Anreisezeit'] ?></span>
						</td>
						<td class="order-persons">
							<input type="text" style="width:70px;" value="<?php echo $order['Personen'] ?>" class="transparent-input"><span style="display: none;"><?php echo $order['Personen'] ?></span>
						</td>
						<td class="order-parkplatz">
							<input type="text" style="width:90px;" value="<?php echo $order['Parkplatz'] ?>" class="transparent-input"><span style="display: none;"><?php echo $order['Parkplatz'] ?></span>
						</td>
						<td class="order-dateto" style="">
							<input type="text" style="width:115px; <?php if ((int)$order['Anreisezeit'] >= 0 && (int)$order['Anreisezeit'] <= 2) echo 'background-color: rgba(255, 119, 51, 0.5) !important;'; ?>" value="<?php if ($order['Abreisedatum'] != "") echo date('d.m.Y', strtotime($order['Abreisedatum']));
																																																							else echo ""; ?>" data-date="<?php echo $order['AbreisedatumPH']; ?>" placeholder="<?php if ($order['AbreisedatumPH'] != null) echo date('d.m.Y', strtotime($order['AbreisedatumPH']));
																																																																												else echo ""; ?>" class="anListeDateTo transparent-input single-datepicker" readonly><span style="display: none;">
								<?php //if($order['Abreisedatum'] != "") echo date('d.m.Y', strtotime($order['Abreisedatum'])); else echo ""; 
								?></span>
						</td>
						<td class="order-ruckflug">
							<input type="text" style="width:100px;" value="<?php if ($order['Rückflugnummer'] != "") echo $order['Rückflugnummer'];
																			else echo ""; ?>" class="transparent-input"><span style="display: none;"><?php //if($order['Rückflugnummer'] != "") echo $order['Rückflugnummer']; else echo ""; 
																																						?></span>
						</td>
						<td class="order-landung">
							<input type="time" style="width:100px;" value="<?php if ($order['Landung'] != "") echo $order['Landung'];
																			else echo ""; ?>" class="transparent-input" placeholder="00:00"><span style="display: none;"><?php //if($order['Landung'] != "") echo $order['Landung']; else echo ""; 
																																											?></span>
						</td>
						<td class="order-betrag" style="position: relative;">
							<?php
							//$betrag = explode(' ', $order['Betrag']);
							//$price = $betrag[0];
							//$currency = $betrag[1];
							?>
							<?php if (get_post_meta($order['Nr.'], '_transaction_id')[0] == "barzahlung") : ?>
								<input type="text" <?php echo $editOK; ?> style="width:100px; background: rgba(255, 255, 255, 0.5) !important" value="<?php echo $order['Betrag'] ?>" class="form-control transparent-input"><span style="display: none;"><?php echo $order['Betrag'];
																																																															if ($order['Service'] != '0.00') echo " " . number_format($order['Service'], 2, '.', ''); ?></span>
							<?php else : ?>
								<input type="text" <?php echo $editOK; ?> style="width:100px; background: rgba(255, 255, 255, 0.5) !important" value="-" class="form-control transparent-input"><span style="display: none;"><?php if ($order['Service'] != '0.00') echo number_format($order['Service'], 2, '.', '');
																																																								else echo '-'; ?></span>
							<?php endif; ?>
						</td>
						<td class="order-fahrer">
							<input type="text" style="width:150px;" value="<?php echo $order['Fahrer'] ?>" class="transparent-input"><span style="display: none;"><?php echo $order['Fahrer'] ?></span>
						</td>
						<td class="order-sonstiges">
							<input type="text" style="width:150px;" value="<?php echo $order['Sonstiges'] ?>" class="transparent-input"><span style="display: none;"><?php echo $order['Sonstiges'] ?></span>
						</td>
						<td class="order-service">
							<input type="text" style="width:100px;" value="<?php if ($order['Service'] != '0.00') echo number_format($order['Service'], 2, '.', '');
																			else echo '-'; ?>" class="transparent-input" placeholder="0.00" readonly>
						</td>
						<td class="order-status">
							<select class="transparent-input">

								<?php foreach (wc_get_order_statuses() as $key => $value) : ?>
									<?php
									if (
										$key == 'wc-processing' ||
										$key == 'wc-cancelled' ||
										$key == 'wc-refunded' ||
										$key == 'wc-pending'
									) :
									?>
										<option value="<?php echo $key ?>" <?php echo $key == $order['Status'] ? 'selected' : '' ?>>
											<?php
											if ($key == 'wc-processing') echo "abgeschlossen";
											elseif ($key == 'wc-cancelled') echo "storniert";
											elseif ($key == 'wc-refunded') echo "erstattet";
											elseif ($key == 'wc-pending') echo "nicht bezahlt";
											?>
										</option>
									<?php else : continue;
									endif; ?>
								<?php endforeach; ?>
							</select>
							<!--						--><?php //if($order['Status'] == 'processing' && get_post_meta($order['Nr.'], '_transaction_id', true) != "")
															//								echo "bezahlt";
															//							  elseif($order['Status'] == 'processing' && get_post_meta($order['Nr.'], '_transaction_id', true) == "")
															//								echo "Barzahlung";
															//							  elseif($order['Status'] == 'cancelled')
															//								echo "storniert";
															//					
															?>
						</td>
						<?php if ($user->user_login == 'aras' || $user->user_login == 'cakir' || $user->user_login == 'sergej') : ?>
							<td class="order-edit">
								<input type="text" readonly style="width:150px;" value="<?php echo $order['Bearbeitet'] ?>" class="transparent-input">
							</td>
						<?php else : ?>
							<td class="order-edit">
								<input type="text" readonly style="width:150px;" value="-" class="transparent-input">
							</td>
						<?php endif; ?>
						<?php if ($order['is_for'] != 'hotel') : ?>
							<td>
								<!--                        <a href="#" class="anreiseliste-modal" data-order="--><?php //echo $order['Nr.'] 
																													?><!--"-->
								<!--                           data-target="#editListModal">-->
								<!--                            Edit-->
								<!--                        </a>-->
								<a href="#" class="save-anreiseliste-row">Speichern</a>
							</td>
						<?php else :  ?>
							<td>
								<p class="">-</p>
							</td>
						<?php endif; ?>
					</tr>
				<?php $i++;
				endforeach; ?>
			</tbody>
		</table>
		<?php if (count($allorders) <= 0) : ?>
			<p>Keine Ergebnisse gefunden!</p>
		<?php endif; ?>


		<!-- ######### -->
		<?php
		$total_rows = $_SESSION['total_rows'];
		$total_pages = ceil($total_rows / 25);
		if (isset($_GET['step']) && !empty($_GET['step'])) {
			$activePage = $_GET['step'];
		} else {
			$activePage = 1;
		}
		$page = $_GET['step'];


		$query = $_GET;
		$query['step'] = $page + 1;
		$query_result = http_build_query($query);
		$href_next = $_SERVER['PHP_SELF'] . "?" . $query_result;

		$query['step'] = $page - 1;
		$query_result = http_build_query($query);
		$href_prev = $_SERVER['PHP_SELF'] . "?" . $query_result;
		?>

		<nav id="customPagination" aria-label="Page navigation example">
			<ul class="pagination">
				<li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
					<a class="page-link text-dark" href="<?= $href_prev; ?>">Zurück</a>
				</li>
				<?php
				for ($i = 1; $i <= $total_pages; $i++) {
					if (
						$i == 1 ||
						$i == 2 ||
						$i == $total_pages - 1 ||
						$i == $total_pages ||
						$i == $activePage + 2 ||
						$i == $activePage + 1 ||
						$i == $activePage ||
						$i == $activePage - 1 ||
						$i == $activePage - 2 ||

						$i == ($total_pages / 2)  + 2 ||
						$i == ($total_pages / 2)  + 1 ||
						$i == ($total_pages / 2)  ||
						$i == ($total_pages / 2)  - 1 ||
						$i == ($total_pages / 2)  - 2
					) {
						$query = $_GET;
						// replace parameter(s)
						$query['step'] = $i;
						// rebuild url
						$query_result = http_build_query($query);
						$href = $_SERVER['PHP_SELF'] . "?" . $query_result;

						if ($i == $activePage) {
							echo "<li class='page-item'>";
							echo "<a class='page-link bg bg-dark text-white' href='" . $href . "'>" . $i . "</a> ";
							echo "</li>";
						} else {
							if ($i == ($activePage - 2) && $activePage > 6) {
								echo "<li class='page-item'>";
								echo "<a class='page-link text-dark'>...</a> ";
								echo "</li>";
								echo "<li class='page-item'>";
								echo "<a class='page-link text-dark' href='" . $href . "'>" . $i . "</a> ";
								echo "</li>";
							} else if ($i == ($activePage + 2) && $activePage < $total_pages - 2) {
								echo "<li class='page-item'>";
								echo "<a class='page-link text-dark' href='" . $href . "'>" . $i . "</a> ";
								echo "</li>";
								echo "<li class='page-item'>";
								echo "<a class='page-link text-dark'>...</a> ";
								echo "</li>";
							} else {
								echo "<li class='page-item'>";
								echo "<a class='page-link text-dark' href='" . $href . "'>" . $i . "</a> ";
								echo "</li>";
							}
						}
					} else {
					}
				}
				?>
				<li class="page-item <?php if ($page >= $total_pages || $page <= 1) echo 'disabled'; ?>">
					<a class="page-link text-dark" href="<?= $href_next; ?>">Vor</a>
				</li>
			</ul>
		</nav>
		<!-- ######### -->
	</div>
</div>

<script>
	// get the input and form elements
	const myInput = document.getElementById('search');
	const myForm = document.getElementById('myForm');

	// create a variable to store the timeout ID
	let timeoutId;

	// add an event listener for input changes
	myInput.addEventListener('input', () => {
		// clear the timeout if it exists
		clearTimeout(timeoutId);

		// set a new timeout to execute after 3 seconds
		timeoutId = setTimeout(() => {
			// submit the form
			myForm.submit();
		}, 2000);
	});
</script>