<?php
defined('ABSPATH') || exit;
require_once get_template_directory() . '/lib/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

if(isset($_GET['transfere'])){
	$user_id = $_GET['transfere'];
}
else 
	$user_id = "";

if(isset($_GET['transfere'])){
	if(isset($_GET['y']))
		$year = $_GET['y'];
	else
		$year = date('Y');

	$monate = array(1=>"Januar",
					2=>"Februar",
					3=>"M&auml;rz",
					4=>"April",
					5=>"Mai",
					6=>"Juni",
					7=>"Juli",
					8=>"August",
					9=>"September",
					10=>"Oktober",
					11=>"November",
					12=>"Dezember");
					
					
	if($_GET['pdf']){
		//include get_template_directory() . '/invoices/transfere_invoice.php';
		ob_start();
		if (get_template_directory() . '/invoices/transfere_invoice.php') {
			include get_template_directory() . '/invoices/transfere_invoice.php';
		}
		$content = ob_get_clean();
				// instantiate and use the dompdf class
				$options = new Options();
				$options->set('isRemoteEnabled', true);
				$dompdf = new Dompdf($options);
				$dompdf->loadHtml($content);

				// (Optional) Setup the paper size and orientation
				$dompdf->setPaper('A4', 'portrait');

				// Render the HTML as PDF
				$dompdf->render();
				
				$file = $dompdf->output();
					$fileName = $product->parklot_short."-".$_GET['month']."-".$_GET['inv-year'];
				if(!file_exists(ABSPATH . 'wp-content/uploads/transfer_rechnungen')){
					mkdir(ABSPATH . 'wp-content/uploads/transfer_rechnungen');
				}
				$filePath = ABSPATH . 'wp-content/uploads/transfer_rechnungen/' . $fileName . '.pdf';
				$pdf = fopen($filePath, 'w');
				fwrite($pdf, $file);
				fclose($pdf);
				
				$pdf_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
				echo "<script>location.href = '".$pdf_url."/wp-content/uploads/transfer_rechnungen/".$product->parklot_short."-".$_GET['month']."-".$_GET['inv-year'].".pdf';</script>";
	}
}
?>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-title itweb_adminpage_head">
		<h3>Rechnungen</h3>
	</div>
	<div class="page-body">
		<form class="form-filter">
			<div class="row ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Transfer wählen</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-md-2">
							<select class="form-item form-control" name="transfere">
								<option value="" <?php echo $user_id == "" ? "selected" : "" ?>>Auswählen</option>
								<option value="24" <?php echo $user_id == "24" ? "selected" : "" ?>>AMH</option>
								<option value="25" <?php echo $user_id == "25" ? "selected" : "" ?>>HMA</option>
								<!--<option value="26" <?php echo $user_id == "26" ? "selected" : "" ?>>IAPS</option>-->
							</select>
						</div>
						<div class="col-sm-12 col-md-2">
							<button class="btn btn-primary d-block w-100" type="submit">Auswählen</button>
						</div>
					</div>
					<br><br>
					<?php if(isset($_GET['transfere'])): ?>
					<div class="row">
						<div class="col-sm-12 col-md-2">
							<select class="form-item form-control" name="y">
								<?php for($i = 2021; $i <= date('Y'); $i++): ?>
								<option value="<?php echo $i ?>" <?php echo $year == $i ? "selected" : "" ?>><?php echo $i ?></option>
								<?php endfor; ?>
							</select>
						</div>
						<div class="col-sm-12 col-md-2">
							<button class="btn btn-primary d-block w-100" type="submit">Filter</button>
						</div>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</form><br>
		<?php if(isset($_GET['transfere'])): ?>
			<?php if($year <= date('Y')): ?>
			<div class="row">
				<div class="col-12">
					<div class="table-responsive">
						<table class="table table-sm">
							<thead>
							<tr>
								<th>Monat</th>
								<th>Personen</th>
								<th>Zum Flughafen</th>
								<th>Rücktransfer</th>
								<th>Hin- und Rücktransfer</th>
								<th>Rechnungsbetrag</th>
								<th>Ansehen</th>
							</tr>
							</thead>
							<tbody>
							<?php for($i = 1; $i <=12; $i++): ?>
								<?php
									if($i >= date('m') && $year == date('Y'))
										break;
									//$dateObj   = DateTime::createFromFormat('!m', $i);
									//$monthName = $dateObj->format('F');

									$monat = date($i);
									
									$hotelTransfers_hib = HotelTransfers::getHotelTransfersForInvioce_Hin($i, $year, $user_id);
									$hotelTransfers_zurück = HotelTransfers::getHotelTransfersForInvioce_Zurück($i, $year, $user_id);
									$hotelTransfers_beide = HotelTransfers::getHotelTransfersForInvioce_Beide($i, $year, $user_id);
									
									$sumPersonen_hin = 0;
									$preis_hin = 0;
									$sumPersonen_zurück = 0;
									$preis_zurück = 0;
									$sumPersonen_beide = 0;
									$preis_beide = 0;
									if(count($hotelTransfers_hib) > 0){
										foreach($hotelTransfers_hib as $b){		
											$order = wc_get_order($b->Buchung);
											$variation = new WC_Product_Variation($b->variation_id);
											$name = $variation->get_name();
											$personen = explode(' - ', $name)[1];
											$personen = filter_var($personen, FILTER_SANITIZE_NUMBER_INT);
											$sumPersonen_hin += $personen;
											$preis_hin += $b->Betrag;
											
										}
									}
									if(count($hotelTransfers_zurück) > 0){
										foreach($hotelTransfers_zurück as $b){		
											$order = wc_get_order($b->Buchung);
											$variation = new WC_Product_Variation($b->variation_id);
											$name = $variation->get_name();
											$personen = explode(' - ', $name)[1];
											$personen = filter_var($personen, FILTER_SANITIZE_NUMBER_INT);
											$sumPersonen_zurück += $personen;
											$preis_zurück += $b->Betrag;
											
										}
									}
									if(count($hotelTransfers_beide) > 0){
										foreach($hotelTransfers_beide as $b){		
											$order = wc_get_order($b->Buchung);
											$variation = new WC_Product_Variation($b->variation_id);
											$name = $variation->get_name();
											$personen = explode(' - ', $name)[1];
											$personen = filter_var($personen, FILTER_SANITIZE_NUMBER_INT);
											$sumPersonen_beide += $personen;
											$preis_beide += $b->Betrag;
											
										}
									}
									
									$gesamtbetrag = $preis_hin + $preis_zurück + $preis_beide;
								?>
								<tr>
									<td><?php echo $monate[$monat] . " " . $year; ?></td>
									<td><?php echo $sumPersonen_hin + $sumPersonen_zurück + ($sumPersonen_beide * 2) ?></td>
									<td><?php echo $preis_hin ?></td>
									<td><?php echo $preis_zurück ?></td>
									<td><?php echo $preis_beide ?></td>
									<td><?php echo $gesamtbetrag > 0 ? $gesamtbetrag : "0" ?></td>
									<td><a class="" href="<?php echo basename($_SERVER['REQUEST_URI'])?>&pdf=1&tr=<?php echo $user_id ?>&month=<?php echo $i . '&inv-year=' . $year ?>" target="_blank">Anzeigen</a></td>
								</tr>
							<?php endfor; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>	
			<?php else: ?>
			<div class="row">
				<div class="col-12">
					<p>Keine Rechnungen vorhanden.</p>
				</div>
			</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>