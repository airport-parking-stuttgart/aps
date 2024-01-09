<form action="/wp-content/plugins/itweb-booking/classes/Helper.php" method="POST">
    <input type="hidden" name="task" value="new_hotel_transfer">
    <input type="hidden" name="type" value="rucktransfer">
    <input type="hidden" name="dateto" value="<?php echo $_GET['rucktransfer'] ?>">
	<input type="hidden" name="pID" value="<?php echo isset($_GET['pID']) ? $_GET['pID'] : get_current_user_id() ?>">

    <input type="text" class="form-control mb-2" name="vorname" placeholder="Vorname">

    <input type="text" class="form-control mb-2" name="nachname" placeholder="Nachname">

    <input type="tel" class="form-control mb-2" name="phone" placeholder="Mobil-/Telefonnummer">

    <select name="product" class="form-control mb-2" required>
        <option value="">Anzahl Personen</option>
        <?php foreach ($variations as $variation) : ?>
            <?php
            $product_variation = new WC_Product_Variation($variation);
            $name = explode(' - ', $product_variation->get_name())[1];
            ?>
            <option value="<?php echo $product_variation->get_id() ?>">
                <?php echo $name ?>
            </option>
        <?php endforeach; ?>
    </select>

    <input type="text" class="form-control mb-2 timepicker" name="ankunftszeit_ruckflug" placeholder="Ankunftszeit Rückflug">

    <input type="text" class="form-control mb-2" name="ruckflugnummer" placeholder="Flugnummer Rückflug">

	<input type="text" class="form-control mb-2" name="sonstiges" placeholder="Sonstiges: z. B. Kindersitz">

    <a type="button" href="/partner-dashboard" class="btn btn-secondary mt-4">Zurück</a>
    <button type="submit" class="btn btn-primary mt-4">Transfer buchen</button>
</form>