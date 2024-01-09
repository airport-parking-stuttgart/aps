<?php 
require_once get_template_directory() . '/lib/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;
$db = Database::getInstance();

$order_id = $_GET["edit"];
$order = wc_get_order($order_id);

$car_images = $db->getValetCarImage($order_id);
$car_videos = $db->getValetCarVideos($order_id);

$parklot = Database::getInstance()->getParklotByProductId(count($order->get_items()) > 0 ? array_values($order->get_items())[0]->get_product_id() : '');

$booking['token'] = $order->get_meta('token');
$booking['dateTo'] = dateFormat($order->get_meta('Abreisedatum'), 'de');
$booking['flightFrom'] = $order->get_meta('Rückflugnummer');
$booking['timeTo'] = date('H:i', strtotime($order->get_meta('Uhrzeit bis')));
$booking['kilometerstand'] = $order->get_meta('Kilometerstand');
$booking['tankstand'] = $order->get_meta('Tankstand');
$booking['model'] = $order->get_meta('Fahrzeughersteller');
$booking['type'] = $order->get_meta('Fahrzeugmodell');
$booking['color'] = $order->get_meta('Fahrzeugfarbe');
$booking['merkmale'] = $order->get_meta('Merkmale');
$booking['a-m-date'] = $order->get_meta('Annahmedatum MA');
$booking['a-m-time'] = $order->get_meta('Annahmezeit MA');
$booking['a-m-user'] = $order->get_meta('Annahme MA');
$booking['u-unterschrift-k'] = $order->get_meta('Unterschrift K1');

if(file_exists(ABSPATH . 'wp-content/uploads/valet-protokolle/u_k1-' . get_post_meta($order_id, 'token', true) . ".png") || $_POST["ck1_hidden"] != null){
	$unterschrift_k = 1;
}
else
	$unterschrift_k = 0;


if(isset($_POST["update_val"])){
	//echo "<pre>"; print_r($_POST); echo "</pre>";
	
	/// Step One
	if(!file_exists(ABSPATH . 'wp-content/uploads/valet-car-images')){
		mkdir(ABSPATH . 'wp-content/uploads/valet-car-images');
	}
	$images = $_FILES['car_images'];
	if(count($images['name']) > 0)
		Database::getInstance()->saveValetCarImage($order_id, $images['name']);
	for ($i = 0; $i < count($images['name']); $i++) {
		$target_file = ABSPATH . 'wp-content/uploads/valet-car-images/' . basename($images['name'][$i]);
		move_uploaded_file($images["tmp_name"][$i], $target_file);
	}
	
	if(!file_exists(ABSPATH . 'wp-content/uploads/valet-car-videos')){
		mkdir(ABSPATH . 'wp-content/uploads/valet-car-videos');
	}
	
	$videos = $_FILES['car_videos'];
	if(count($videos['name']) > 0)
		Database::getInstance()->saveValetCarVideo($order_id, $videos['name']);
	
	for ($k = 0; $k < count($videos['name']); $k++) {
		$target_file = ABSPATH . 'wp-content/uploads/valet-car-videos/' . basename($videos['name'][$k]);
		move_uploaded_file($videos["tmp_name"][$k], $target_file);
	}
	///
	
	/// Step Two
	if($_POST["kilometerstand"])
		update_post_meta($order_id, 'Kilometerstand', $_POST["kilometerstand"]);
	if($_POST["tankstand"])
		update_post_meta($order_id, 'Tankstand', $_POST["tankstand"]);
	///
	
	/// Step Three
	if($_POST["dateTo"])
		update_post_meta($order_id, 'Abreisedatum', dateFormat($_POST["dateTo"]));
	
	if($_POST["flightFrom"])
		update_post_meta($order_id, 'Rückflugnummer', $_POST["flightFrom"]);
	
	if($_POST["timeTo"])
			update_post_meta($order_id, 'Uhrzeit bis', $_POST["timeTo"]);
	///
	
	/// Step Four
	if($_POST["mail_adress"])
		update_post_meta($order_id, '_billing_email', $_POST["mail_adress"]);
	///
	
	/// Step Five
	if($_POST["model"])
		update_post_meta($order_id, 'Fahrzeughersteller', $_POST["model"]);
	if($_POST["type"])
		update_post_meta($order_id, 'Fahrzeugmodell', $_POST["type"]);
	if($_POST["color"])
		update_post_meta($order_id, 'Fahrzeugfarbe', $_POST["color"]);
	if($_POST["merkmale"])
		update_post_meta($order_id, 'Merkmale', $_POST["merkmale"]);
	
	if(isset($_POST["ck1_hidden"]) && $_POST["ck1_hidden"] != null){
		if(strlen($_POST["ck1_hidden"]) > 1300){
			$img = $_POST["ck1_hidden"];
			$img = str_replace('data:image/png;base64,', '', $img);
			$img = str_replace(' ', '+', $img);
			$data = base64_decode($img);
			$fileName = "u_k1-" . get_post_meta($order_id, 'token', true) . ".png";
			if(!file_exists(ABSPATH . 'wp-content/uploads/valet-protokolle')){
				mkdir(ABSPATH . 'wp-content/uploads/valet-protokolle');
			}
			$upload_dir = ABSPATH . 'wp-content/uploads/valet-protokolle/';
			$file = $upload_dir . $fileName;
			file_put_contents($file, $data);
		}
	}
	if($data["u-unterschrift-k"])
			update_post_meta($order_id, 'Unterschrift K', $data["u-unterschrift-k"]);
	///
	
	/// Step Six
	if($_POST["a-m-date"])
		update_post_meta($order_id, 'Annahmedatum MA', $_POST["a-m-date"]);
	if($_POST["a-m-time"])
		update_post_meta($order_id, 'Annahmezeit MA', $_POST["a-m-time"]);
	if($_POST["a-m-user"])
		update_post_meta($order_id, 'Annahme MA', $_POST["a-m-user"]);
	///
	
	/// Step Seven
	
	if(isset($_POST["send_protocol_mail"])){
		ob_start();
	?>
		<style>
		*{
			 font-size: 11px;
		}
		
		table{
			border-collapse: collapse
		}
		td, th{
			border:1px solid black;
		}
		.valet-car-image{
			float: left; margin-right: 5px;
		}
		.clear {
			clear: left;
		}

		.u-k-table{
			float: left; margin-right: 10px;
		}

		.page_break { page-break-before: always; }

		</style>
		<table>
			<tr>
				<td style="width: 700px; border: none;">
					<img style="max-height: 150px; width: auto" src="<?php echo get_home_url() . '/wp-content/uploads/2021/12/APS-Logo-klein.png' ?>" alt="">
				</td>
				<td style="width: 100px; border: none; text-align: right;">
					<p style="">APS Airport-Parking-Stuttgart GmbH<br>
					Raiffeisenstraße 18 – 70794 Filderstadt</p>
					<p>Buchung: <?php echo get_post_meta($order_id, 'token', true) ?><br>
					Seite 1 - 2</p>
				</td>
			</tr>
		</table>
		<br>
		<div class="col-12 m60">
			<h3>Valet-Service Annahme Protokoll zur Buchung <?php echo get_post_meta($order_id, 'token', true) ?></h3>
		</div>
		<div class="col-12 m60">
			<h5>Kunden Details</h5>
			<table>
				<tr>
					<?php //if(get_post_meta($order_id, '_billing_company', true)): ?>
					<!--<th style="width: 250px">Firma</th>-->
					<?php //endif; ?>
					<th style="width: 250px">Name, Nachname</th>
					<th style="width: 125px">Telefon</th>
					<th style="width: 250px">E-Mail</th>
				</tr>
				<tr>
					<?php //if(get_post_meta($order_id, '_billing_company', true)): ?>
					<!--<td><?php echo get_post_meta($order_id, '_billing_company', true) ?></td>-->
					<?php //endif; ?>
					<td><?php echo get_post_meta($order_id, '_billing_first_name', true) . " " . get_post_meta($order_id, '_billing_last_name', true) ?></td>
					<td><?php echo get_post_meta($order_id, '_billing_phone', true) ?></td>
					<td><?php echo $_POST["mail_adress"] ?></td>
				</tr>
			</table>

			<h5>Buchungsinformationen</h5>
			<table>
				<tr>
					<th style="width: 125px">Anreisedatum</th>
					<th style="width: 125px">Anreisezeit</th>
					<th style="width: 125px">Abreisedatum</th>
					<th style="width: 125px">Abreisezeit</th>
					<th style="width: 125px">Hinflug-Nr.</th>
					<th style="width: 125px">Rückflug-Nr.</th>
				</tr>
				<tr>
					<td><?php echo dateFormat(get_post_meta($order_id, 'Anreisedatum', true), 'de') ?></td>
					<td><?php echo get_post_meta($order_id, 'Uhrzeit von', true) ?></td>
					<td><?php echo dateFormat($_POST["dateTo"], 'de') ?></td>
					<td><?php echo get_post_meta($order_id, 'Uhrzeit bis', true) ?></td>
					<td><?php echo $_POST["flightFrom"] ?></td>
					<td><?php echo $_POST["timeTo"] ?></td>
				</tr>
				<tr>
					<td colspan="6">Adresse: <?php echo $parklot->adress ?></td>
				</tr>
			</table>
			<h5>Fahrzeuginformationen</h5>
			<table>
				<tr>
					<th style="width: 250px">Hersteller</th>
					<th style="width: 125px">Typ</th>
					<th style="width: 125px">Farbe</th>
					<th style="width: 125px">Kennzeichen</th>
					
					<th style="width: 125px">Kilometerstand</th>
					<th style="width: 125px">Tankfüllung</th>
				</tr>
				<tr>
					<td><?php echo $_POST["model"] ?></td>
					<td><?php echo $_POST["type"] ?></td>
					<td><?php echo $_POST["color"] ?></td>
					<td><?php echo get_post_meta($order_id, 'Kennzeichen', true) ?></td>
					
					<td><?php echo $_POST["kilometerstand"] ?></td>
					<td><?php echo 'Ca. ' . $_POST["tankstand"] . '%' ?></td>
				</tr>
				<tr height="200px">
					<td colspan="5">Sonstige Merkmale: <?php echo $_POST["merkmale"] ?></td>
					<?php if($unterschrift_k): ?>
					<td><img src="<?php echo get_home_url() . '/wp-content/uploads/valet-protokolle/u_k1-' . get_post_meta($order_id, 'token', true) . ".png" ?>"></td>
					<?php else: ?>
					<td><?php echo "&nbsp;<br>&nbsp;<br>&nbsp;" ?></td>
					<?php endif;?>
				</tr>
			</table>
			<br>
			<div class="u-k-table">
				<table>
					<tr>
						<th style="border:1px solid black;" colspan="3">Annahme vom Mitarbeiter</th>
					</tr>
					<tr>				
						<td style="width: 100px; text-align: center;"><strong>Datum</strong></td>
						<td style="width: 100px; text-align: center;"><strong>Uhrzeit</strong></td>
						<td style="width: 200px; text-align: center;"><strong>Mitarbeiter</strong></td>
					</tr>
					<tr>
						<td><?php echo get_post_meta($order_id, 'Annahmedatum MA', true) ? dateFormat(get_post_meta($order_id, 'Annahmedatum MA', true), 'de') : "&nbsp;" ?></td>
						<td><?php echo get_post_meta($order_id, 'Annahmezeit MA', true) ?></td>
						<td><?php echo get_post_meta($order_id, 'Annahme MA', true) ?></td>
					</tr>
				</table>
			</div>
		</div>
		<div class="clear"></div>
		<table class="page_break">
			<tr>
				<td style="width: 700px; border: none;">
					<img style="max-height: 150px; width: auto" src="<?php echo get_home_url() . '/wp-content/uploads/2021/12/APS-Logo-klein.png' ?>" alt="">
				</td>
				<td style="width: 100px; border: none; text-align: right;">
					<p style="">APS Airport-Parking-Stuttgart GmbH<br>
					Raiffeisenstraße 18 – 70794 Filderstadt</p>
					<p>Buchung: <?php echo get_post_meta($order_id, 'token', true) ?><br>
					Seite 2 - 2</p>
				</td>
			</tr>
		</table>
		<br><br>
		<div class="col-12 m60">
			<h5>Vor Ort aufgenommenen Bilder</h5>
			<?php $i = $g = 1; ?>
			<?php foreach ($car_images as $image): ?>
				<?php if($image->image_file == null) continue; ?>
				<div class="valet-car-image">
					<img style="max-height: 175px; width: auto" src="<?php echo get_home_url() . '/wp-content/uploads/valet-car-images/' . basename($image->image_file) ?>" alt="">
				</div>
				<?php if($i == 3){
						echo "<br><br><br><br><br><br><br><br><br><br><br>";
					} ?>
				<?php $i++; $g++; ?>
				<?php if($i > 3) $i = 1; ?>
				<?php if($g == 6) break; ?>
			<?php endforeach; ?>
		</div>
		<div class="clear"></div>
			
			<?php
		$content = ob_get_clean();
			// instantiate and use the dompdf class
			$options = new Options();
			$options->set('isRemoteEnabled', true);
			$dompdf = new Dompdf($options);
			$dompdf->loadHtml($content);

			// (Optional) Setup the paper size and orientation
			$dompdf->setPaper('A4', 'landscape');

			// Render the HTML as PDF
			$dompdf->render();

			$file = $dompdf->output();
				$fileName = get_post_meta($order_id, 'token', true);
			if(!file_exists(ABSPATH . 'wp-content/uploads/valet-protokolle')){
				mkdir(ABSPATH . 'wp-content/uploads/valet-protokolle');
			}
			$filePath = ABSPATH . 'wp-content/uploads/valet-protokolle/' . $fileName . '.pdf';
			$pdf = fopen($filePath, 'w');
			fwrite($pdf, $file);
			fclose($pdf);
		$headers = array('Content-Type: text/html; charset=UTF-8');
		$body = "<strong>Hallo " . get_post_meta($order_id, '_billing_first_name', true) . " " . get_post_meta($order_id, '_billing_last_name', true) . "</strong><br><br>
				Im Anhang erhalten Sie das Annahmeprotokoll zu Ihrer Parkplatzbuchung.<br><br>
				Wir wünschen Ihnen eine gute Reise.<br><br>
				Viele Grüßen und bis bald.<br>
				Ihr <a href='www.airport-parking-stuttgart.de'>airport-parking-stuttgart.de</a><br><br>
				Tel: +49 711 22 051 245<br>Web: <a href='www.airport-parking-stuttgart.de'>www.airport-parking-stuttgart.de</a><br><br>
				Geschäftsanschrift:<br>
				APS Airport-Parking-Stuttgart GmbH<br>Raiffeisenstrasse 18<br>70794 Filderstadt<br>
				Inhaber: Erdem Aras<br>Steuernummer: 99008/07242<br>";
		wp_mail($_POST["mail_adress"], '[APS] Annahmeprotokoll - ' . $booking['token'], $body, $headers, $filePath);
	}	
	
	if(isset($_POST["send_mail"])){
		$mailer = WC()->mailer();
		$mails = $mailer->get_emails();
		wp_mail('it@airport-parking-stuttgart.de', 'Mail', print_r($mails, true), $headers);
		if ( ! empty( $mails ) ) {
			foreach ( $mails as $mail ) {
				if ( $mail->id == 'customer_processing_order' /*|| $mail->id == 'customer_on_hold_order' */ ){
					$mail->trigger( $order_id ); 
					break;
				}
			}            
		}
	}
	///
}
?>



<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3><?php echo get_post_meta($order_id, '_billing_first_name', true) . " " . get_post_meta($order_id, '_billing_last_name', true) ?> - <?php echo $booking['token'] ?></h3>
    </div>
	<div class="page-body">
		<form class="update-price" action="<?php echo basename($_SERVER['REQUEST_URI']); ?>" method="POST" enctype="multipart/form-data">
			<div class="row ui-lotdata-block ui-lotdata-block-next" id="step_one">
				<h5 class="ui-lotdata-title">Fahrzeug Bilder und Videos hochladen</h5>
				<div class="col-sm-12 col-md-12 col-lg-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-md-12 col-lg-6">
							<label for="">Fahrzeugbilder</label>
							<input type="file" name="car_images[]" accept="image/x-png,image/gif,image/jpeg" multiple>
						</div>
						<div class="col-sm-12 col-md-12 col-lg-12 gallery-images">
							<div class="row">
								<?php foreach ($car_images as $image): ?>
									<?php if($image->image_file == null) continue; ?>
									<div class="col-sm-6 col-md-6 col-lg-3 valet-car-image">
										<span class="del-valet-img"
											  data-id="<?php echo $image->id ?>" data-name="<?php echo $image->image_file ?>">X</span>
										<img style="max-height: 300px; width: auto" src="<?php echo get_home_url() . '/wp-content/uploads/valet-car-images/' . basename($image->image_file) ?>" alt="">
									</div>
								<?php endforeach; ?>
							</div>
							<br><br>
							<div class="row">
								<div class="col-sm-12 col-md-12 col-lg-6">
									<label for="">Videos</label>
									<input type="file" name="car_videos[]" accept="video/*"
										   multiple>
								</div>
								<div class="col-sm-12 col-md-12 col-lg-12 gallery-images">
									<div class="row">
										<?php foreach ($car_videos as $video): ?>
											<?php if($video->video_file == null) continue; ?>
											<div class="col-12 col-sm-6 col-md-6 col-lg-3 valet-car-video">
												<span class="del-valet-vid"
													  data-id="<?php echo $video->id ?>" data-name="<?php echo $video->video_file ?>">X</span>										
												<video class="video-scr" width="300" height="auto" controls>
													<source src="<?php echo get_home_url() . '/wp-content/uploads/valet-car-videos/' . basename($video->video_file) ?>">
												</video>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
							</div>
						</div>						
					</div>
					<div class="row">
						<div class="col-sm-12 col-md-6 col-lg-2">
							<br><a class="btn btn-primary" onclick="to_step_two()">Weiter</a>
						</div>
						<div class="col-sm-12 col-md-6 col-lg-4">
							<br><a href= "<?php echo '/wp-admin/admin.php?page=buchung-bearbeiten&edit=' . $order_id . '&anv=1'?>" class="btn btn-primary">Komplette Bearbeitung</a>
						</div>
						<div class="col-sm-12 col-md-3 col-lg-2">                    
							<br><a href="<?php echo '/wp-admin/admin.php?page=anreiseliste-valet' ?>" class="btn btn-secondary d-block w-100" >Schließen</a>
						</div>
					</div>
				</div>
			</div>
			
			<div class="row ui-lotdata-block ui-lotdata-block-next" id="step_two">
				<h5 class="ui-lotdata-title">Kilometerstand und Tankfüllung eintragen</h5>
				<div class="col-sm-12 col-md-12 col-lg-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-xs-12 col-md-4 col-lg-3">
							<label for="kilometerstand">Kilometerstand</label><br>
							<input type="number" name="kilometerstand" placeholder="" class="" value="<?php echo $booking['kilometerstand'] ?>" >
						</div>
						<div class="col-sm-12 col-xs-12 col-md-5 col-lg-4">
							<label for="tankstand">Tankfüllung</label><br>
							<input type="range" step="10" min="0" max="101" name="tankstand" id="tankstand" value="<?php echo $booking['tankstand'] ? $booking['tankstand'] : '50'?>"  
							oninput="set_tankVal(this.value)" onchange="set_tankVal(this.value)">
							<span id="tankstand_val"><?php echo "  "; echo $booking['tankstand'] ? $booking['tankstand'] : '50';?></span>%
						</div>		
					</div>
					<div class="row">
						<div class="col-sm-12 col-md-4 col-lg-2">
							<br><a class="btn btn-primary" onclick="to_step_one()">Zurück</a>
						</div>
						<div class="col-sm-12 col-md-4 col-lg-2">
							<br><a class="btn btn-primary" onclick="to_step_three()">Weiter</a>
						</div>
						<div class="col-sm-12 col-md-4 col-lg-4">
							<br><a href= "<?php echo '/wp-admin/admin.php?page=buchung-bearbeiten&edit=' . $order_id . '&anv=1'?>" class="btn btn-primary">Komplette Bearbeitung</a>
						</div>
						<div class="col-sm-12 col-md-3 col-lg-2">                    
							<br><a href="<?php echo '/wp-admin/admin.php?page=anreiseliste-valet' ?>" class="btn btn-secondary d-block w-100" >Schließen</a>
						</div>
					</div>
				</div>
			</div>
			<div class="row ui-lotdata-block ui-lotdata-block-next" id="step_three">
				<h5 class="ui-lotdata-title">Rückreiseinformationen eintragen</h5>
				<div class="col-sm-12 col-md-12 col-lg-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-xs-12 col-md-3 col-lg-3">
							<label for="dateTo">Abreisedatum</label><br>
							<input type="text" name="dateTo" placeholder="" class="--single-datepicker --editBookingDateTo" value="<?php echo $booking['dateTo'] ?>" required readonly>
						</div>
						<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
							<label for="flightFrom">Rückflug-Nr.</label>
							<input type="text" name="flightFrom" placeholder="" class="" value="<?php echo $booking['flightFrom'] ?>" required <?php echo $disabled; ?>>
						</div>
						<div class="col-sm-12 col-xs-12 col-md-2 col-lg-2">
							<label for="timeTo">Abreisezeit</label>
							<input type="text" name="timeTo" placeholder="" class="timepicker" value="<?php echo $booking['timeTo'] ?>" required readonly>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12 col-md-4 col-lg-2">
							<br><a class="btn btn-primary" onclick="to_step_two()">Zurück</a>
						</div>
						<div class="col-sm-12 col-md-4 col-lg-2">
							<br><a class="btn btn-primary" onclick="to_step_four()">Weiter</a>
						</div>
						<div class="col-sm-12 col-md-4 col-lg-4">
							<br><a href= "<?php echo '/wp-admin/admin.php?page=buchung-bearbeiten&edit=' . $order_id . '&anv=1'?>" class="btn btn-primary">Komplette Bearbeitung</a>
						</div>
						<div class="col-sm-12 col-md-3 col-lg-2">                    
							<br><a href="<?php echo '/wp-admin/admin.php?page=anreiseliste-valet' ?>" class="btn btn-secondary d-block w-100" >Schließen</a>
						</div>
					</div>
				</div>
			</div>
			<div class="row ui-lotdata-block ui-lotdata-block-next" id="step_four">
				<h5 class="ui-lotdata-title">E-Mail Adresse prüfen</h5>
				<div class="col-sm-12 col-md-12 col-lg-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-xs-12 col-md-6 col-lg-4">
							<label for="mail_adress">E-Mail</label>
							<input type="email" size="50" name="mail_adress" placeholder="" class="" value="<?php echo get_post_meta($order_id, '_billing_email', true) ?>">
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12 col-md-4 col-lg-2">
							<br><a class="btn btn-primary" onclick="to_step_three()">Zurück</a>
						</div>
						<div class="col-sm-12 col-md-4 col-lg-2">
							<br><a class="btn btn-primary" onclick="to_step_five()">Weiter</a>
						</div>
						<div class="col-sm-12 col-md-4 col-lg-4">
							<br><a href= "<?php echo '/wp-admin/admin.php?page=buchung-bearbeiten&edit=' . $order_id . '&anv=1'?>" class="btn btn-primary">Komplette Bearbeitung</a>
						</div>
						<div class="col-sm-12 col-md-3 col-lg-2">                    
							<br><a href="<?php echo '/wp-admin/admin.php?page=anreiseliste-valet' ?>" class="btn btn-secondary d-block w-100" >Schließen</a>
						</div>
					</div>
				</div>
			</div>
			<div class="row ui-lotdata-block ui-lotdata-block-next" id="step_five">
				<h5 class="ui-lotdata-title">Fahrzeuginformationen prüfen</h5>
				<div class="col-sm-12 col-md-12 col-lg-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-xs-12 col-md-4 col-lg-3">
							<label for="model">Model</label>
							<input type="text" name="model" placeholder="" class="" value="<?php echo $booking['model'] ?>" >
						</div>
						<div class="col-sm-12 col-xs-12 col-md-4 col-lg-3">
							<label for="type">Typ</label>
							<input type="text" name="type" placeholder="" class="" value="<?php echo $booking['type'] ?>" >
						</div>
						<div class="col-sm-12 col-xs-12 col-md-3 col-lg-2">
							<label for="color">Farbe</label>
							<input type="text" name="color" placeholder="" class="" value="<?php echo $booking['color'] ?>" >
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12 col-md-6">
							<label for="merkmale">Sonstige Merkmale</label><br>
							<textarea name="merkmale" rows="4" cols="30"><?php echo $booking['merkmale'] ?></textarea>
						</div>
						<div class="col-sm-12 col-md-6">
							<label for="">Unterschrift Fahrzeugangaben</label><br>
							<?php if($unterschrift_k): ?>
							<img src="<?php echo get_home_url() . '/wp-content/uploads/valet-protokolle/u_k1-' . get_post_meta($order_id, 'token', true) . ".png" ?>">
							<?php else: ?>
							<input type="hidden" name="ck1_hidden" id="ck1_hidden">
							<canvas style="border:1px solid black;" id="canvas-k1" width="300" height="100"></canvas><br>
							<a class="btn" id="ck1_clear" onclick="clear_ck1()">löschen</a>
							<?php endif; ?>
							<p>Unterschrift Kunde</p>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12 col-md-4 col-lg-2">
							<br><a class="btn btn-primary" onclick="to_step_four()">Zurück</a>
						</div>
						<div class="col-sm-12 col-md-4 col-lg-2">
							<br><a class="btn btn-primary" onclick="to_step_six()">Weiter</a>
						</div>
						<div class="col-sm-12 col-md-4 col-lg-4">
							<br><a href= "<?php echo '/wp-admin/admin.php?page=buchung-bearbeiten&edit=' . $order_id . '&anv=1'?>" class="btn btn-primary">Komplette Bearbeitung</a>
						</div>
						<div class="col-sm-12 col-md-3 col-lg-2">                    
							<br><a href="<?php echo '/wp-admin/admin.php?page=anreiseliste-valet' ?>" class="btn btn-secondary d-block w-100" >Schließen</a>
						</div>
					</div>
				</div>
			</div>
			<div class="row ui-lotdata-block ui-lotdata-block-next" id="step_six">
				<h5 class="ui-lotdata-title">Fahrzeugannahme</h5>
				<div class="col-sm-12 col-md-12 col-lg-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-md-3 col-lg-3">
							<label for="a-m-date">Datum</label><br>
							<input type="text" name="a-m-date" placeholder="" class="single-datepicker" value="<?php echo $booking['a-m-date'] ? $booking['a-m-date'] : ""; ?>">
						</div>						
						<div class="col-sm-12 col-md-3 col-lg-2">
							<label for="a-m-time">Uhrzeit</label><br>
							<input type="time" name="a-m-time" placeholder="" class="" value="<?php echo $booking['a-m-time'] ? $booking['a-m-time'] : ""; ?>" >
						</div>
						<div class="col-sm-12 col-md-4 col-lg-3">
							<label for="a-m-user">Mitarbeiter</label><br>
							<input type="text" name="a-m-user" placeholder="" class="" value="<?php echo $booking['a-m-user'] ? $booking['a-m-user'] : ""; ?>">
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12 col-md-4 col-lg-2">
							<br><a class="btn btn-primary" onclick="to_step_five()">Zurück</a>
						</div>
						<div class="col-sm-12 col-md-4 col-lg-2">
							<br><a class="btn btn-primary" onclick="to_step_seven()">Weiter</a>
						</div>
						<div class="col-sm-12 col-md-4 col-lg-4">
							<br><a href= "<?php echo '/wp-admin/admin.php?page=buchung-bearbeiten&edit=' . $order_id . '&anv=1'?>" class="btn btn-primary">Komplette Bearbeitung</a>
						</div>
						<div class="col-sm-12 col-md-3 col-lg-2">                    
							<br><a href="<?php echo '/wp-admin/admin.php?page=anreiseliste-valet' ?>" class="btn btn-secondary d-block w-100" >Schließen</a>
						</div>
					</div>
				</div>
			</div>
			<div class="row ui-lotdata-block ui-lotdata-block-next" id="step_seven">
				<h5 class="ui-lotdata-title">Buchungsbearbeitung abschließen</h5>
				<div class="col-sm-12 col-md-12 col-lg-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-md-12 col-lg-12">
							<input type="checkbox" id="send_mail" name="send_mail" value="mail" <?php echo $disabledBtn; ?>>
							<label for="send_mail">Buchungsbestätigung senden</label><br>
						</div>
						<div class="col-sm-12 col-md-12 col-lg-12">
							<input type="checkbox" id="send_protocol_mail" name="send_protocol_mail" value="mail_p" >
							<label for="send_protocol_mail">Annahmeprotokoll an Kunden senden</label><br>
						</div>
						<div class="col-sm-12 col-md-4 col-lg-2">
							<br><a class="btn btn-primary" onclick="to_step_six()">Zurück</a>
						</div>
						<div class="col-sm-12 col-md-4 col-lg-4">
							<input type="hidden" name="update_val" value="1">
							<br><input class="btn btn-primary" type="submit" value="Buchung aktualisieren">
						</div>
						<div class="col-sm-12 col-md-4 col-lg-4">
							<br><a href= "<?php echo '/wp-admin/admin.php?page=buchung-bearbeiten&edit=' . $order_id . '&anv=1'?>" class="btn btn-primary">Komplette Bearbeitung</a>
						</div>
						<div class="col-sm-12 col-md-3 col-lg-2">                    
							<br><a href="<?php echo '/wp-admin/admin.php?page=anreiseliste-valet' ?>" class="btn btn-secondary d-block w-100" >Schließen</a>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<script>
one = document.getElementById('step_one');
two = document.getElementById('step_two');
three = document.getElementById('step_three');
four = document.getElementById('step_four');
five = document.getElementById('step_five');
six = document.getElementById('step_six');
seven = document.getElementById('step_seven');

one.style.display = "block";
two.style.display = "none";
three.style.display = "none";
four.style.display = "none";
five.style.display = "none";
six.style.display = "none";
seven.style.display = "none";

function to_step_one(){
	one.style.display = "block";
	two.style.display = "none";
	three.style.display = "none";
	four.style.display = "none";
	five.style.display = "none";
	six.style.display = "none";
	seven.style.display = "none";
}

function to_step_two(){
	one.style.display = "none";
	two.style.display = "block";
	three.style.display = "none";
	four.style.display = "none";
	five.style.display = "none";
	six.style.display = "none";
	seven.style.display = "none";
}

function set_tankVal(e){
	document.getElementById("tankstand_val").innerHTML = e
}

function to_step_three(){
	one.style.display = "none";
	two.style.display = "none";
	three.style.display = "block";
	four.style.display = "none";
	five.style.display = "none";
	six.style.display = "none";
	seven.style.display = "none";
}

function to_step_four(){
	one.style.display = "none";
	two.style.display = "none";
	three.style.display = "none";
	four.style.display = "block";
	five.style.display = "none";
	six.style.display = "none";
	seven.style.display = "none";
}

function to_step_five(){
	one.style.display = "none";
	two.style.display = "none";
	three.style.display = "none";
	four.style.display = "none";
	five.style.display = "block";
	six.style.display = "none";
	seven.style.display = "none";
}

function to_step_six(){
	one.style.display = "none";
	two.style.display = "none";
	three.style.display = "none";
	four.style.display = "none";
	five.style.display = "none";
	six.style.display = "block";
	seven.style.display = "none";
}

function to_step_seven(){
	one.style.display = "none";
	two.style.display = "none";
	three.style.display = "none";
	four.style.display = "none";
	five.style.display = "none";
	six.style.display = "none";
	seven.style.display = "block";
}

const ck = document.getElementById("canvas-k1");

if(ck){    
	ck.addEventListener("mousedown", setLastCoords_k); // fires before mouse left btn is released
	ck.addEventListener("mousemove", freeForm_k);
	ck.addEventListener("mouseleave", setData_k);
	
	const ctx_k = ck.getContext("2d");
 
	function setLastCoords_k(e) {
		const {x, y} = ck.getBoundingClientRect();
		lastX_k = e.clientX - x;
		lastY_k = e.clientY - y;
	}
  
	function freeForm_k(e) {
		if (e.buttons !== 1) return; // left button is not pushed yet
		penTool_k(e);
	}
	
	function penTool_k(e) {
		const {x, y} = ck.getBoundingClientRect();
		const newX = e.clientX - x;
		const newY = e.clientY - y;

		ctx_k.beginPath();
		ctx_k.lineWidth = 2;
		ctx_k.moveTo(lastX_k, lastY_k);
		ctx_k.lineTo(newX, newY);
		ctx_k.strokeStyle = 'black';
		ctx_k.stroke();
		ctx_k.closePath();

		lastX_k = newX;
		lastY_k = newY;
	}
	
	function setData_k(e) {
		document.getElementById('ck1_hidden').value = ck.toDataURL('image/png');
	}
}

let lastX_k = 0;
let lastY_k = 0;

function clear_ck1(){
	const ck = document.getElementById("canvas-k1");
	ctx = ck.getContext("2d");
	ctx.clearRect(0, 0, ck.width, ck.height);
}

</script>

<script>
(function() {
	
	// Get a regular interval for drawing to the screen
	window.requestAnimFrame = (function (callback) {
		return window.requestAnimationFrame || 
					window.webkitRequestAnimationFrame ||
					window.mozRequestAnimationFrame ||
					window.oRequestAnimationFrame ||
					window.msRequestAnimaitonFrame ||
					function (callback) {
						window.setTimeout(callback, 1000/60);
					};
	})();


const ckt = document.getElementById("canvas-k1");
const ck_clear = document.getElementById("ck1_clear");


if(ckt){
	// Set up the canvas
	var ctx_kt = ckt.getContext("2d");
	ctx_kt.strokeStyle = "#222222";
	ctx_kt.lineWith = 2;

	// Set up mouse events for drawing
	var drawing_kt = false;
	var mousePos_kt = { x:0, y:0 };
	var lastPos_kt = mousePos_kt;
	ckt.addEventListener("mousedown", function (e) {
		drawing_kt = true;
		lastPos_kt = getMousePos_kt(ckt, e);
	}, false);
	ckt.addEventListener("mouseup", function (e) {
		drawing_kt = false;
		
	}, false);
	ckt.addEventListener("mousemove", function (e) {
		mousePos_kt = getMousePos_kt(ckt, e);
	}, false);

	// Set up touch events for mobile, etc
	ckt.addEventListener("touchstart", function (e) {
		mousePos_kt = getTouchPos_kt(ckt, e);
		var touch_kt = e.touches[0];
		var mouseEvent_kt = new MouseEvent("mousedown", {
			clientX: touch_kt.clientX,
			clientY: touch_kt.clientY
		});
		ckt.dispatchEvent(mouseEvent_kt);
		disableScroll();
	}, false);
	ckt.addEventListener("touchend", function (e) {
		var mouseEvent_kt = new MouseEvent("mouseup", {});
		document.getElementById('ck1_hidden').value = ckt.toDataURL('image/png');
		ckt.dispatchEvent(mouseEvent_kt);
		enableScroll();
	}, false);
	ckt.addEventListener("touchmove", function (e) {
		var touch_kt = e.touches[0];
		var mouseEvent_kt = new MouseEvent("mousemove", {
			clientX: touch_kt.clientX,
			clientY: touch_kt.clientY
		});
		//disableScroll();
		ckt.dispatchEvent(mouseEvent_kt);
	}, false);
	
	ck_clear.addEventListener("click", function (e) {
		ckt.width = ckt.width;
		ctx_kt.clearRect(0, 0, ckt.width, ckt.height);
	}, false);
	
	// Prevent scrolling when touching the canvas
		document.body.addEventListener("touchstart", function (e) {
			if (e.target == ckt) {
				e.preventDefault();
			}
		}, false);
		document.body.addEventListener("touchend", function (e) {
			if (e.target == ckt) {
				e.preventDefault();
			}
		}, false);
		document.body.addEventListener("touchmove", function (e) {
			if (e.target == ckt) {
				e.preventDefault();
			}
	}, false);
}	



	// Get the position of the mouse relative to the canvas
	function getMousePos_kt(canvasDom, mouseEvent) {
		var rect_kt = canvasDom.getBoundingClientRect();
		return {
			x: mouseEvent.clientX - rect_kt.left,
			y: mouseEvent.clientY - rect_kt.top
		};
	}

	// Get the position of a touch relative to the canvas
	function getTouchPos_kt(canvasDom, touchEvent_kt) {
		var rect_kt = canvasDom.getBoundingClientRect();
		return {
			x: touchEvent_kt.touches[0].clientX - rect_kt.left,
			y: touchEvent_kt.touches[0].clientY - rect_kt.top
		};
	}

	// Draw to the canvas
	function renderCanvas_kt() {
		if (drawing_kt) {
			ctx_kt.moveTo(lastPos_kt.x, lastPos_kt.y);
			ctx_kt.lineTo(mousePos_kt.x, mousePos_kt.y);
			ctx_kt.stroke();
			lastPos_kt = mousePos_kt;
		}
	}
	
	function preventDefault(e) {
	  e.preventDefault();
	}

	// modern Chrome requires { passive: false } when adding event
	var supportsPassive = false;
	try {
	  window.addEventListener("test", null, Object.defineProperty({}, 'passive', {
		get: function () { supportsPassive = true; } 
	  }));
	} catch(e) {}

	var wheelOpt = supportsPassive ? { passive: false } : false;
	var wheelEvent = 'onwheel' in document.createElement('div') ? 'wheel' : 'mousewheel';


	function disableScroll() {
	  window.addEventListener('DOMMouseScroll', preventDefault, false); // older FF
	  window.addEventListener(wheelEvent, preventDefault, wheelOpt); // modern desktop
	  window.addEventListener('touchmove', preventDefault, wheelOpt); // mobile
	  window.addEventListener('keydown', preventDefaultForScrollKeys, false);
	}

	function enableScroll() {
	  window.removeEventListener('DOMMouseScroll', preventDefault, false);
	  window.removeEventListener(wheelEvent, preventDefault, wheelOpt); 
	  window.removeEventListener('touchmove', preventDefault, wheelOpt);
	  window.removeEventListener('keydown', preventDefaultForScrollKeys, false);
	}

	// Allow for animation
	(function drawLoop () {
		requestAnimFrame(drawLoop);
		renderCanvas_kt();
	})();

})();
</script>