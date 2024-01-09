<?php
$id = $_GET['edit'];
$products = Database::getInstance()->getProducts();
$hotelTransfer = HotelTransfers::getHotelTransfer($id);

$hotelProduct = wc_get_product($hotelTransfer->product_id);
$variations = $hotelProduct->get_children();
$order = wc_get_order($hotelTransfer->order_id);
$timefrom = $hotelTransfer->transfer_vom_hotel ? date('H:i', strtotime($hotelTransfer->transfer_vom_hotel)) : '';
$timeto = $hotelTransfer->ankunftszeit_ruckflug ? date('H:i', strtotime($hotelTransfer->ankunftszeit_ruckflug)) : '';


?>
<div class="container my-5">
    <form action="/wp-content/plugins/itweb-booking/classes/Helper.php" method="POST">
        <input type="hidden" name="task" value="update_hotel_transfer">
        <input type="hidden" name="id" value="<?php echo $hotelTransfer->id ?>">

        <input type="text" class="form-control mb-2 single-datepicker" name="datefrom" placeholder="Transfer zum Flughafen"
               data-value="<?php echo $hotelTransfer->datefrom ?>">

        <input type="text" class="form-control mb-2 single-datepicker" name="dateto" placeholder="Rücktransfer zum Hotel"
               data-value="<?php echo $hotelTransfer->dateto ?>">

        <input type="text" class="form-control mb-2" name="vorname" placeholder="Vorname"
               value="<?php echo $order->get_billing_first_name() ?>">

        <input type="text" class="form-control mb-2" name="nachname" placeholder="Nachname"
               value="<?php echo $order->get_billing_last_name() ?>">

        <input type="tel" class="form-control mb-2" name="phone" placeholder="Mobil-/Telefonnummer"
               value="<?php echo $order->get_billing_phone() ?>">
		<?php 
			if($hotelTransfer->datefrom != null && $hotelTransfer->dateto != null)
				$m = 2;
			else
				$m = 1;
		?>
        <select name="product" class="form-control mb-2">
            <option value="">Anzahl Personen</option>
            <?php foreach ($variations as $variation) : ?>
                <?php
                $product_variation = new WC_Product_Variation($variation);
                $name = explode(' - ', $product_variation->get_name())[1];
                ?>
                <option value="<?php echo $product_variation->get_id() ?>" <?php echo $product_variation->get_id() == $hotelTransfer->variation_id ? 'selected' : '' ?>>
                    <?php echo $name ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="text" class="form-control mb-2 timepicker" name="transfer_vom_hotel"
               value="<?php echo $timefrom ?>"
               placeholder="Geplanter Transfer vom Hotel">

        <input type="text" class="form-control mb-2 timepicker" name="ankunftszeit_ruckflug"
               value="<?php echo $timeto ?>"
               placeholder="Ankunftszeit Rückflug">

        <input type="text" class="form-control mb-2" name="hinflugnummer" placeholder="Flugnummer Hinflug"
               value="<?php echo $hotelTransfer->hinflug_nummer ?>">

        <input type="text" class="form-control mb-2" name="ruckflugnummer" placeholder="Flugnummer Rückflug"
               value="<?php echo $hotelTransfer->ruckflug_nummer ?>">
		
		<input type="text" class="form-control mb-2" name="sonstiges" placeholder="Sonstiges: z. B. Kindersitz"
		value="<?php echo get_post_meta($hotelTransfer->order_id, 'Sonstige 1')[0]; ?>">

        <a type="button" href="/transferliste" class="btn btn-secondary mt-4">Zurück</a>
        <button type="submit" class="btn btn-primary mt-4">Aktualisieren</button>
    </form>
</div>