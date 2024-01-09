<?php
get_header();
$userName = wp_get_current_user()->display_name;
?>
<?php if (isset($_GET['edit'])) : ?>
    <?php require_once get_stylesheet_directory() . '/partner-dashboard-templates/edit-transfer.php'; ?>
<?php else: ?>
    <?php
    
	if (isset($_GET["date"])) {
		$date = (explode(" - ", $_GET["date"]));
		$date[0] = date('Y-m-d', strtotime($date[0]));
		$date[1] = date('Y-m-d', strtotime($date[1]));		
	}
	else{
		$date[0] = date('Y') . '-' . date('m') . '-' . "01";
		$date[1] = date('Y') . '-' . date('m') . '-' . date('t');
	}
	$hotelTransfers = HotelTransfers::getHotelTransfers($date[0], $date[1]);
	
	//echo "<pre>";
	//print_r($hotelTransfers);
	//echo "</pre>";
    ?>
    <style>
        .text-center {
            text-align: center;
            margin: 0;
        }

        .dataTables_filter input {
            margin: 0 !important;
        }
    </style>
    <div class="container">
        <div class="btn-s mb-5 mt-5">
            <a href="/partner-dashboard" type="button" class="btn btn-primary">Neue Buchung</a>
            <a href="/transferliste" type="button" class="btn btn-primary">Transferliste</a>
			<a href="/transfer-rechnungen" type="button" class="btn btn-primary">Rechnungen</a>
			<a href="/wp-login.php?action=logout" type="button" class="btn btn-primary">Abmelden</a>
        </div>

        <h2>Transferliste</h2>
		<form class="form-filter">
			<div class="row">
                <div class="col-sm-12 col-md-3">
                    <input type="text" class="datepicker-range form-item form-control" name="date"
                           data-multiple-dates-separator=" - " placeholder="" autocomplete="off"
                           data-from="<?php echo $date[0] ?>" data-to="<?php echo $date[1] ?>">
                </div>
                <div class="col-sm-12 col-md-2">
                    <button class="btn btn-primary d-block w-100" type="submit">Filter</button>
                </div>
            </div>
		</form><br>
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="datatable">
                        <thead>
                        <tr>
                            <th>B.Nr.</th>
							<th>Buchungsdatum</th>
                            <th>Kunde</th>
                            <th>Transfer FH <i class="fas fa-long-arrow-alt-right"></i></th>
                            <th><i class="fa fa-clock"></i></th>
                            <th>
                                <i class="fas fa-long-arrow-alt-left"></i> Rücktransfer
                            </th>
                            <th><i class="fa fa-clock"></i></th>
                            <th>Personen</th>
                            <th>Status</th>
                            <th>Aktion</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($hotelTransfers as $hotelTransfer) : ?>
                            <?php
                            $order = wc_get_order($hotelTransfer->order_id);
                            $variation = new WC_Product_Variation($hotelTransfer->variation_id);
                            $name = $variation->get_name();
							
							//if ($order->get_status() != 'completed' || $order->get_status() != 'processing')
							//	continue;
							
                            $allowEdit = false;
                            if (!empty($hotelTransfer->datefrom) && dateDifference($hotelTransfer->datefrom, date('Y-m-d')) > 0) {
                                $allowEdit = true;
                            } else if (empty($hotelTransfer->datefrom) && !empty($hotelTransfer->dateto) && dateDifference($hotelTransfer->dateto, date('Y-m-d')) > 0) {
                                $allowEdit = true;
                            }
                            ?>
							<?php if ($order != null) :?>
								<?php if ($order->get_status() == 'completed' || $order->get_status() == 'processing') : ?>
								<tr>
									<td><?php echo $hotelTransfer->token ?></td>
									<td><?php echo date('d.m.Y', strtotime($order->order_date)); ?></td>
									<td><?php echo $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ?></td>
									<td><?php echo $hotelTransfer->datefrom ? date('d.m.Y', strtotime($hotelTransfer->datefrom)) : '<p class="text-center">-</p>' ?></td>
									<td><?php echo $hotelTransfer->transfer_vom_hotel ? date('H:i', strtotime($hotelTransfer->transfer_vom_hotel)) : '<p class="text-center">-</p>' ?></td>
									<td><?php echo /*$userName . ' - ' . */($hotelTransfer->dateto ? date('d.m.Y', strtotime($hotelTransfer->dateto)) : '<p class="text-center">-</p>') ?></td>
									<td><?php echo $hotelTransfer->ankunftszeit_ruckflug ? date('H:i', strtotime($hotelTransfer->ankunftszeit_ruckflug)) : '<p class="text-center">-</p>' ?></td>
									<td>
										<?php echo explode(' - ', $name)[1]; ?>
									</td>
									<td>
										<p class="text-center">
											<?php if ($order->get_status() == 'completed' || $order->get_status() == 'processing') : ?>
												<i class="fas fa-check"></i>
											<?php else : ?>
												<i class="fas fa-minus"></i>
											<?php endif; ?>
										</p>
									</td>
									<td>
										<?php if ($allowEdit) : ?>
											<a href="?edit=<?php echo $hotelTransfer->id ?>"
											   style="color:black;margin-right: 10px;">
												<i class="fas fa-edit"></i>
											</a>
											<?php if ($order->get_status() == 'completed' || $order->get_status() == 'processing') : ?>
												<a href="" class="del-ht" data-id="<?php echo $hotelTransfer->order_id ?>" style="color: red">
													<i class="fas fa-times-circle"></i>
												</a>
											<?php endif; ?>
										<?php endif; ?>
									</td>
								</tr>
								<?php endif; ?>
							<?php endif; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php get_footer() ?>
