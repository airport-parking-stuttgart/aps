<?php

/*

	$apiKey = 'fxIbhYpr2HVO38QHyjLuhx5F66TgC94fB1A23qS2PdLlLgyr8xWDJFTLGavBe0J6';
	$clientId = '2QprL13rmoTfaaQx9EN2oVxgL49'; 





    $url = 'https://api.staging.roosh.online/provider/v1/';

    $ch = curl_init($url);      

    $data = ['apiKey'=>$apiKey, 'clientId'=>$clientId];

    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);    

    $headers = [
        'Content-Type:application/json',
        'apiKey: ' . $apiKey,
        'clientId: ' . $clientId];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);

    curl_close($ch);
	
	echo "<pre>"; print_r($result); echo "</pre>";
	
*/




$products = Database::getInstance()->getAllLotsNoTransfer();
$product_groups = Database::getInstance()->getProductGroups();
$date = date('Y-m-d', strtotime(date('Y-m-d')));
$date_now = date("Y-m-d H:i");
$filter = array("datum" => '', "token" => '', "kennzeichen" => '', "lotnr" => '', "orderBy" => 'Anreisedatum');
$filter['filter_product'] = $_GET['product'];
if (isok($_GET, 'token')) {
	$date = $plate = $lot_nr = "";
	$token = $_GET['token'];
	$filter['datum'] = $filter['kennzeichen'] = $filter['lotnr'] = "";
	$filter['token'] = $token;
	unset($_GET['date']);
	unset($_GET['kennzeichen']);
	unset($_GET['lot_nr']);
	$allorders = Database::getInstance()->get_abgestellte_pkw($filter);
}

if (isok($_GET, 'kennzeichen')) {
	$date = $token = $lot_nr = "";
	$filter['datum'] = $filter['token'] = $filter['lotnr'] = "";
	$plate = $_GET['kennzeichen'];
	$filter['kennzeichen'] = $plate;
	unset($_GET['date']);
	unset($_GET['token']);
	unset($_GET['lot_nr']);
	$allorders = Database::getInstance()->get_abgestellte_pkw($filter);
}

if (isok($_GET, 'lot_nr')) {
	$date = $token = $plate = "";
	$lot_nr = $_GET['lot_nr'];
	$filter['lotnr'] = $lot_nr;
	$filter['datum'] = $filter['token'] = $filter['kennzeichen'] = "";
	unset($_GET['date']);
	unset($_GET['token']);
	unset($_GET['kennzeichen']);
    $allorders = Database::getInstance()->get_abgestellte_pkw($filter);
}

if (isok($_GET, 'date') || $date != "") {
    if($_GET['date'])
		$date = date('Y-m-d', strtotime($_GET['date']));
	$token = $plate = $lot_nr = "";
	$filter['datum'] = $date;
	$filter['token'] = $filter['kennzeichen'] = $filter['lotnr'] = null;
	$allorders = Database::getInstance()->get_abgestellte_pkw($filter);
}

//echo "<pre>"; print_R($allorders); echo "</pre>";
?>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Abgestellte PKW</h3>
    </div>
	<br>
    <div class="page-body">
        <form class="form-filter">
			<div class="row my-2 ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Filtern</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row m10">
						<div class="col-sm-12 col-md-1 ui-lotdata-date">
							<input type="text" id="dateFrom" name="date" placeholder="Datum"
								   class="form-item form-control single-datepicker"
								   value="<?php echo $date != "" ? date('d.m.Y', strtotime($date)) : ''; ?>">
						</div>
						<!--<div class="col-sm-12 col-md-3 col-lg-2">
							<select name="lot" class="form-item form-control">
								<option value="">Produkt</option>
								<?php foreach($products as $product) : ?>
									<option value="<?php echo $product->product_id ?>"
										<?php echo (isset($_GET['lot']) && $_GET['lot'] == $product->product_id) ? ' selected' : '' ?>>
										<?php echo $product->parklot ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>-->
						<div class="col-sm-12 col-md-2">
							<select name="product" class="form-item form-control">
								<option value="">Standort</option>
								<?php foreach ($product_groups as $group) : ?>						
									<option value="<?php echo $group->id ?>" <?php echo $group->id == $_GET['product'] ? "selected" : "" ?>><?php echo $group->name ?></option>						
									<?php $child_product_groups = Database::getInstance()->getChildProductGroupsByPerentId($group->id); ?>
									<?php if(count($child_product_groups) > 0): ?>
										<?php foreach ($child_product_groups as $child_group) : ?>
										<option value="<?php echo $child_group->id ?>" <?php echo $child_group->id == $_GET['product'] ? "selected" : "" ?>><?php echo " - " . $child_group->name ?></option>
										<?php endforeach; ?>
									<?php endif; ?>
								<?php endforeach; ?>
							</select>
						</div>	
						<div class="col-sm-12 col-md-1">
							<input type="text" name="token" placeholder="B-Nr." class="form-item form-control"
								   value="<?php if($token != "") echo $token; else echo ''; ?>">
						</div>
						<div class="col-sm-12 col-md-2">
							<input type="text" name="kennzeichen" placeholder="Kennzeichen" class="form-item form-control"
								   value="<?php if($plate != "") echo $plate; else echo '';  ?>">
						</div>
						<div class="col-sm-12 col-md-1">
							<input type="text" name="lot_nr" placeholder="P-Nr." class="form-item form-control"
								   value="<?php echo isset($_GET['lot_nr']) ? $_GET['lot_nr'] : '' ?>">
						</div>
						<div class="col-sm-12 col-md-1">
							<button type="submit" class="btn btn-primary d-block w-100">Filter</button>
						</div>
						<div class="col-sm-12 col-md-2">                    
							<a href="<?php echo '/wp-admin/admin.php?page=buchung-abgestellte-pkw' ?>" class="btn btn-secondary d-block w-100" >Zurücksetzen</a>
						</div>
					</div>
				</div>
			</div>
        </form>
		<br><br>
        <table class="datatablePKWs table-responsive ">
            <thead>
            <tr>
				<th>Nr.</th>
                <th>Produkt</th>
                <th>Buchungsnummer</th>
                <th>Name</th>
                <th>Anreisedatum</th>
                <th>Uhrzeit</th>
                <th>Abreisedatum</th>
                <th>Uhrzeit</th>
                <th>E-Mail</th>
                <th>Kennzeichen</th>
                <th>Parkplatznummer</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php $nr = 1; foreach ($allorders as $booking) : ?>
				<?php 
					if(isset($_GET['lot']) && !empty($_GET['lot'])){
                        if($booking->Produkt != $_GET['lot']){
                            continue;
                        }
                    }
				?>
                <?php                  
					if ($date_now > get_post_meta($booking->order_id, 'Abreisedatum', true) . " " . get_post_meta($booking->order_id, 'Uhrzeit_bis', true)) {
						$status = "abgereist";
						$background = "background-color: #ffe6e6";
					}
					elseif ($date_now < get_post_meta($booking->order_id, 'Anreisedatum', true) . " " . get_post_meta($booking->order_id, 'Uhrzeit_von', true)) {
						$status = "erwartet";
						$background = "background-color: #ffffe6";
					}else{
						$status = "parkt";
						$background = "background-color: #e6ffe6";
					}

                ?>
                <tr class="<?php /*echo $hasGeneratedInvoice ? 'mark_done' : ''; */ ?>" style="<?php echo $background; ?>">
                    <td>
                        <?php echo $nr ?>
                    </td>
					<td>
                        <?php echo $booking->Code ?>
                    </td>
                    <td>
                        <?php echo get_post_meta($booking->order_id, 'token', true) ?>
                    </td>
                    <td>
                        <?php echo get_post_meta($booking->order_id, '_billing_first_name', true) . ' ' . get_post_meta($booking->order_id, '_billing_last_name', true) ?>
                    </td>
                    <td>
                        <?php echo date('Y-m-d', strtotime(get_post_meta($booking->order_id, 'Anreisedatum', true))) ?>
                    </td>
                    <td>
                        <?php echo date('H:i', strtotime(get_post_meta($booking->order_id, 'Uhrzeit von', true))) ?>
                    </td>
                    <td>
                        <?php echo date('Y-m-d', strtotime(get_post_meta($booking->order_id, 'Abreisedatum', true))) ?>
                    </td>
                    <td>
                        <?php echo date('H:i', strtotime(get_post_meta($booking->order_id, 'Uhrzeit bis', true))) ?>
                    </td>
                    <td>
                        <?php echo get_post_meta($booking->order_id, '_billing_email', true) ?>
                    </td>
					<td>
                        <?php echo get_post_meta($booking->order_id, 'Kennzeichen', true) ?>
                    </td>
                    <td><?php echo get_post_meta($booking->order_id, 'Parkplatz', true) != 0 && get_post_meta($booking->order_id, 'Parkplatz', true) != null ? get_post_meta($booking->order_id, 'Parkplatz', true) : "" ?></td>
                    <td><?php echo $status; ?>
					</td>
                </tr>
            <?php $nr++; endforeach; ?>
            </tbody>
        </table>
    </div>
</div>