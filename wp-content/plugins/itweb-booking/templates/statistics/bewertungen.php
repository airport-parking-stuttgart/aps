<?php
$products = Database::getInstance()->getAllLots();

$ges5 = $ges4 = $ges3 = $ges2 = $ges1 = $sentMails = $sum = $pos = $neg = 0;
$sum_pho = $kat_pho['Kat1'] = $kat_pho['Kat2'] = $kat_pho['Kat3'] = 0;
$sum_sie = $kat_sie['Kat1'] = $kat_sie['Kat2'] = $kat_sie['Kat3'] = 0;
$sum_ost = $kat_ost['Kat1'] = $kat_ost['Kat2'] = $kat_ost['Kat3'] = 0;

foreach($products as $product){
	if($product->order_lot == 1 || $product->order_lot == 10 ){
			$sentMailsSQL = Database::getInstance()->getRatingsCountMails($product->product_id);
			$sentMails += $sentMailsSQL->Anzahl;
			$ratings[$product->parklot] = Database::getInstance()->getRatings($product->product_id);
			$ratings[$product->parklot]['5st'] = 0;
			$ratings[$product->parklot]['4st'] = 0;
			$ratings[$product->parklot]['3st'] = 0;
			$ratings[$product->parklot]['2st'] = 0;
			$ratings[$product->parklot]['1st'] = 0;
			$ratings[$product->parklot]['order_lot'] = $product->order_lot;
			$ratings[$product->parklot]['product_id'] = $product->product_id;
			$ratings[$product->parklot]['product'] = $product->parklot;
		if(count($ratings[$product->parklot]) > 0){			
			foreach($ratings[$product->parklot] as $rating){
				$data = unserialize($rating->Kat);
				$kat_pho['Kat1'] += $data['ctr_h8S7'];
				$kat_pho['Kat2'] += $data['ctr_h8S8'];
				$kat_pho['Kat3'] += $data['ctr_h8S9'];
				
				$kat_aps['Kat1'] += $data['ctr_h8S7'];
				$kat_aps['Kat2'] += $data['ctr_h8S8'];
				$kat_aps['Kat3'] += $data['ctr_h8S9'];

				if($rating->Rating == 5){
					$ratings[$product->parklot]['5st'] += 1;
					$ges5 += 1;
					$sum_pho += 1;
					$sum += 1;
					$pos += 1;
				}
				if($rating->Rating < 5 && $rating->Rating >= 4){
					$ratings[$product->parklot]['4st'] += 1;
					$ges4 += 1;
					$sum_pho += 1;
					$sum += 1;
					$pos += 1;
				}
				if($rating->Rating < 4 && $rating->Rating >= 3){
					$ratings[$product->parklot]['3st'] += 1;
					$ges3 += 1;
					$sum_pho += 1;
					$sum += 1;
				}
				if($rating->Rating < 3 && $rating->Rating >= 2){
					$ratings[$product->parklot]['2st'] += 1;
					$ges2 += 1;
					$sum_pho += 1;
					$sum += 1;
					$neg += 1;
				}
				if($rating->Rating < 2 && $rating->Rating >= 1){
					$ratings[$product->parklot]['1st'] += 1;
					$ges1 += 1;
					$sum_pho += 1;
					$sum += 1;
					$neg += 1;
				}
			}
		}
	}
	if($product->order_lot == 30){
			$sentMailsSQL = Database::getInstance()->getRatingsCountMails($product->product_id);
			$sentMails += $sentMailsSQL->Anzahl;
			$ratings[$product->parklot] = Database::getInstance()->getRatings($product->product_id);
			$ratings[$product->parklot]['5st'] = 0;
			$ratings[$product->parklot]['4st'] = 0;
			$ratings[$product->parklot]['3st'] = 0;
			$ratings[$product->parklot]['2st'] = 0;
			$ratings[$product->parklot]['1st'] = 0;
			$ratings[$product->parklot]['order_lot'] = $product->order_lot;
			$ratings[$product->parklot]['product_id'] = $product->product_id;
			$ratings[$product->parklot]['product'] = $product->parklot;
		if(count($ratings[$product->parklot]) > 0){			
			foreach($ratings[$product->parklot] as $rating){
				$data = unserialize($rating->Kat);
				$kat_sie['Kat1'] += $data['ctr_h8S7'];
				$kat_sie['Kat2'] += $data['ctr_h8S8'];
				$kat_sie['Kat3'] += $data['ctr_h8S9'];
				
				$kat_aps['Kat1'] += $data['ctr_h8S7'];
				$kat_aps['Kat2'] += $data['ctr_h8S8'];
				$kat_aps['Kat3'] += $data['ctr_h8S9'];

				if($rating->Rating == 5){
					$ratings[$product->parklot]['5st'] += 1;
					$ges5 += 1;
					$sum_sie += 1;
					$sum += 1;
					$pos += 1;
				}
				if($rating->Rating < 5 && $rating->Rating >= 4){
					$ratings[$product->parklot]['4st'] += 1;
					$ges4 += 1;
					$sum_sie += 1;
					$sum += 1;
					$pos += 1;
				}
				if($rating->Rating < 4 && $rating->Rating >= 3){
					$ratings[$product->parklot]['3st'] += 1;
					$ges3 += 1;
					$sum_sie += 1;
					$sum += 1;
				}
				if($rating->Rating < 3 && $rating->Rating >= 2){
					$ratings[$product->parklot]['2st'] += 1;
					$ges2 += 1;
					$sum_sie += 1;
					$sum += 1;
					$neg += 1;
				}
				if($rating->Rating < 2 && $rating->Rating >= 1){
					$ratings[$product->parklot]['1st'] += 1;
					$ges1 += 1;
					$sum_sie += 1;
					$sum += 1;
					$neg += 1;
				}
			}
		}
	}
	if($product->order_lot == 40 || $product->order_lot == 50){
			$sentMailsSQL = Database::getInstance()->getRatingsCountMails($product->product_id);
			$sentMails += $sentMailsSQL->Anzahl;
			$ratings[$product->parklot] = Database::getInstance()->getRatings($product->product_id);
			$ratings[$product->parklot]['5st'] = 0;
			$ratings[$product->parklot]['4st'] = 0;
			$ratings[$product->parklot]['3st'] = 0;
			$ratings[$product->parklot]['2st'] = 0;
			$ratings[$product->parklot]['1st'] = 0;
			$ratings[$product->parklot]['order_lot'] = $product->order_lot;
			$ratings[$product->parklot]['product_id'] = $product->product_id;
			$ratings[$product->parklot]['product'] = $product->parklot;
		if(count($ratings[$product->parklot]) > 0){			
			foreach($ratings[$product->parklot] as $rating){
				$data = unserialize($rating->Kat);
				$kat_ost['Kat1'] += $data['ctr_h8S7'];
				$kat_ost['Kat2'] += $data['ctr_h8S8'];
				$kat_ost['Kat3'] += $data['ctr_h8S9'];
				
				$kat_aps['Kat1'] += $data['ctr_h8S7'];
				$kat_aps['Kat2'] += $data['ctr_h8S8'];
				$kat_aps['Kat3'] += $data['ctr_h8S9'];

				if($rating->Rating == 5){
					$ratings[$product->parklot]['5st'] += 1;
					$ges5 += 1;
					$sum_ost += 1;
					$sum += 1;
					$pos += 1;
				}
				if($rating->Rating < 5 && $rating->Rating >= 4){
					$ratings[$product->parklot]['4st'] += 1;
					$ges4 += 1;
					$sum_ost += 1;
					$sum += 1;
					$pos += 1;
				}
				if($rating->Rating < 4 && $rating->Rating >= 3){
					$ratings[$product->parklot]['3st'] += 1;
					$ges3 += 1;
					$sum_ost += 1;
					$sum += 1;
				}
				if($rating->Rating < 3 && $rating->Rating >= 2){
					$ratings[$product->parklot]['2st'] += 1;
					$ges2 += 1;
					$sum_ost += 1;
					$sum += 1;
					$neg += 1;
				}
				if($rating->Rating < 2 && $rating->Rating >= 1){
					$ratings[$product->parklot]['1st'] += 1;
					$ges1 += 1;
					$sum_ost += 1;
					$sum += 1;
					$neg += 1;
				}
			}
		}
	}
}
$kat_pho['Kat1'] = $sum_pho != 0 ? round($kat_pho['Kat1'] / $sum_pho, 2) : 0;
$kat_pho['Kat2'] = $sum_pho != 0 ? round($kat_pho['Kat2'] / $sum_pho, 2) : 0;
$kat_pho['Kat3'] = $sum_pho != 0 ? round($kat_pho['Kat3'] / $sum_pho, 2) : 0;

$kat_sie['Kat1'] = $sum_sie != 0 ? round($kat_sie['Kat1'] / $sum_sie, 2) : 0;
$kat_sie['Kat2'] = $sum_sie != 0 ? round($kat_sie['Kat2'] / $sum_sie, 2) : 0;
$kat_sie['Kat3'] = $sum_sie != 0 ? round($kat_sie['Kat3'] / $sum_sie, 2) : 0;

$kat_ost['Kat1'] = $sum_ost != 0 ? round($kat_ost['Kat1'] / $sum_ost, 2) : 0;
$kat_ost['Kat2'] = $sum_ost != 0 ? round($kat_ost['Kat2'] / $sum_ost, 2) : 0;
$kat_ost['Kat3'] = $sum_ost != 0 ? round($kat_ost['Kat3'] / $sum_ost, 2) : 0;

$kat_aps['Kat1'] = $sum != 0 ? round($kat_aps['Kat1'] / $sum, 2) : 0;
$kat_aps['Kat2'] = $sum != 0 ? round($kat_aps['Kat2'] / $sum, 2) : 0;
$kat_aps['Kat3'] = $sum != 0 ? round($kat_aps['Kat3'] / $sum, 2) : 0;


//echo "<pre>"; print_r($products); echo "</pre>";

?>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    google.charts.load('current', {'packages': ['corechart']});
	
	<?php foreach($ratings as $product): ?>
		google.charts.setOnLoadCallback(drawVisualization_<?php echo $product['product_id'] ?>);
		function drawVisualization_<?php echo $product['product_id'] ?>() {
			// Some raw data (not necessarily accurate)
			var data = google.visualization.arrayToDataTable([
				['Bewertung', 'Anzahl', { role: 'annotation'}],
				
				<?php					
					echo "['5', {$product['5st']}, '{$product['5st']}'],";
					echo "['Zwischen 4 und 5', {$product['4st']}, '{$product['4st']}'],";
					echo "['Zwischen 3 und 4', {$product['3st']}, '{$product['3st']}'],";
					echo "['Zwischen 2 und 3', {$product['2st']}, '{$product['2st']}'],";
					echo "['Zwischen 1 und 2', {$product['1st']}, '{$product['1st']}'],";
				?>
			]);

			var options = {
				title: 'Bewertungen von <?php echo $product['product'] ?>',
				legend: {position: 'none'},
				vAxis: {title: 'Anzahl', viewWindowMode: "explicit", viewWindow:{ min: 0 }},
				hAxis: {title: 'Sterne'},
				//hAxis: {textPosition: 'none' },
				seriesType: 'bars',
				series: {5: {type: 'line'}}
			};

			var chart = new google.visualization.ComboChart(document.getElementById('<?php echo $product['product_id'] ?>'));
			chart.draw(data, options);
		}
	<?php endforeach; ?>
	
	google.charts.setOnLoadCallback(drawVisualization_kat_ph);	
	function drawVisualization_kat_ph() {
		// Some raw data (not necessarily accurate)
		var data = google.visualization.arrayToDataTable([
			['Bewertung', 'Kategorie', { role: 'annotation'}],
			
			<?php					
				echo "['Wartezeit bei Anreise', {$kat_pho['Kat1']}, '{$kat_pho['Kat1']}'],";
				echo "['Wartezeit bei Abreise', {$kat_pho['Kat2']}, '{$kat_pho['Kat2']}'],";
				echo "['Freundlichkeit der Mitarbeiter', {$kat_pho['Kat3']}, '{$kat_pho['Kat3']}'],";
			?>
		]);

		var options = {
			title: 'Bewertungen der Kategorien im Durchschnitt von PH/O',
			legend: {position: 'none'},
			vAxis: {title: 'Sterne', viewWindowMode: "explicit", viewWindow:{ min: 0, mex: 5 }},
			hAxis: {title: 'Kategorie'},
			//hAxis: {textPosition: 'none' },
			seriesType: 'bars',
			series: {5: {type: 'line'}}
		};

		var chart = new google.visualization.ComboChart(document.getElementById('kat_ph'));
		chart.draw(data, options);
	}
	
	google.charts.setOnLoadCallback(drawVisualization_kat_sie);	
	function drawVisualization_kat_sie() {
		// Some raw data (not necessarily accurate)
		var data = google.visualization.arrayToDataTable([
			['Bewertung', 'Kategorie', { role: 'annotation'}],
			
			<?php					
				echo "['Wartezeit bei Anreise', {$kat_sie['Kat1']}, '{$kat_sie['Kat1']}'],";
				echo "['Wartezeit bei Abreise', {$kat_sie['Kat2']}, '{$kat_sie['Kat2']}'],";
				echo "['Freundlichkeit der Mitarbeiter', {$kat_sie['Kat3']}, '{$kat_sie['Kat3']}'],";
			?>
		]);

		var options = {
			title: 'Bewertungen der Kategorien im Durchschnitt von SIE',
			legend: {position: 'none'},
			vAxis: {title: 'Sterne', viewWindowMode: "explicit", viewWindow:{ min: 0, mex: 5 }},
			hAxis: {title: 'Kategorie'},
			//hAxis: {textPosition: 'none' },
			seriesType: 'bars',
			series: {5: {type: 'line'}}
		};

		var chart = new google.visualization.ComboChart(document.getElementById('kat_sie'));
		chart.draw(data, options);
	}
	google.charts.setOnLoadCallback(drawVisualization_kat_ost);	
	function drawVisualization_kat_ost() {
		// Some raw data (not necessarily accurate)
		var data = google.visualization.arrayToDataTable([
			['Bewertung', 'Kategorie', { role: 'annotation'}],
			
			<?php					
				echo "['Wartezeit bei Anreise', {$kat_ost['Kat1']}, '{$kat_ost['Kat1']}'],";
				echo "['Wartezeit bei Abreise', {$kat_ost['Kat2']}, '{$kat_ost['Kat2']}'],";
				echo "['Freundlichkeit der Mitarbeiter', {$kat_ost['Kat3']}, '{$kat_ost['Kat3']}'],";
			?>
		]);

		var options = {
			title: 'Bewertungen der Kategorien im Durchschnitt von OST PH/P',
			legend: {position: 'none'},
			vAxis: {title: 'Sterne', viewWindowMode: "explicit", viewWindow:{ min: 0, mex: 5 }},
			hAxis: {title: 'Kategorie'},
			//hAxis: {textPosition: 'none' },
			seriesType: 'bars',
			series: {5: {type: 'line'}}
		};

		var chart = new google.visualization.ComboChart(document.getElementById('kat_ost'));
		chart.draw(data, options);
	}
	
	
	google.charts.setOnLoadCallback(drawVisualization_ges);
	function drawVisualization_ges() {
		// Some raw data (not necessarily accurate)
		var data = google.visualization.arrayToDataTable([
			['Bewertung', 'Anzahl', { role: 'annotation'}],
			
			<?php					
				echo "['5', {$ges5}, '{$ges5}'],";
				echo "['Zwischen 4 und 5', {$ges4}, '{$ges4}'],";
				echo "['Zwischen 3 und 4', {$ges3}, '{$ges3}'],";
				echo "['Zwischen 2 und 3', {$ges2}, '{$ges2}'],";
				echo "['Zwischen 1 und 2', {$ges1}, '{$ges1}'],";
			?>
		]);

		var options = {
			title: 'Bewertungen gesamt von APS',
			legend: {position: 'none'},
			vAxis: {title: 'Anzahl', viewWindowMode: "explicit", viewWindow:{ min: 0 }},
			hAxis: {title: 'Sterne'},
			//hAxis: {textPosition: 'none' },
			seriesType: 'bars',
			series: {5: {type: 'line'}}
		};

		var chart = new google.visualization.ComboChart(document.getElementById('ges'));
		chart.draw(data, options);
	}
	
	google.charts.setOnLoadCallback(drawVisualization_kat_aps);	
	function drawVisualization_kat_aps() {
		// Some raw data (not necessarily accurate)
		var data = google.visualization.arrayToDataTable([
			['Bewertung', 'Kategorie', { role: 'annotation'}],
			
			<?php					
				echo "['Wartezeit bei Anreise', {$kat_aps['Kat1']}, '{$kat_aps['Kat1']}'],";
				echo "['Wartezeit bei Abreise', {$kat_aps['Kat2']}, '{$kat_aps['Kat2']}'],";
				echo "['Freundlichkeit der Mitarbeiter', {$kat_aps['Kat3']}, '{$kat_aps['Kat3']}'],";
			?>
		]);

		var options = {
			title: 'Bewertungen der Kategorien im Durchschnitt von APS',
			legend: {position: 'none'},
			vAxis: {title: 'Sterne', viewWindowMode: "explicit", viewWindow:{ min: 0, mex: 5 }},
			hAxis: {title: 'Kategorie'},
			//hAxis: {textPosition: 'none' },
			seriesType: 'bars',
			series: {5: {type: 'line'}}
		};

		var chart = new google.visualization.ComboChart(document.getElementById('kat_aps'));
		chart.draw(data, options);
	}
	
</script>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-title itweb_adminpage_head">
        <h3>Bewertungen</h3>
    </div>
    <div class="page-body">
		<div class="row">
			<div class="col-sm-12 col-md-4">
				<a href="/wp-admin/admin.php?page=reviewx-all" class="btn btn-sm btn-secondary">Zu den Bewertungen</a><br>
				<!--<h4>Gesendete Anfragen: <?php echo $sentMails - 500 ?></h4>-->
				<h4>Erhaltene Bewertungen: <?php echo $sum ?>. Davon <?php echo $pos ?> positive und <?php echo $neg ?> negative.</h4>
			</div>
		</div><br>
		<div class="row">
			<?php foreach($ratings as $product): ?>
				<?php if($product['order_lot'] == 1 || $product['order_lot'] == 10 ): ?>
					<div class="col-sm-12 col-md-4">
						<div class="chart" id="<?php echo $product['product_id'] ?>"></div>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
			<div class="col-sm-12 col-md-4">
				<div class="chart" id="kat_ph"></div>
			</div>
		</div>
		<div class="row">
			<?php foreach($ratings as $product): ?>
				<?php if($product['order_lot'] == 30 ): ?>
					<div class="col-sm-12 col-md-4">
						<div class="chart" id="<?php echo $product['product_id'] ?>"></div>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
			<div class="col-sm-12 col-md-4">
				<div class="chart" id="kat_sie"></div>
			</div>
		</div>
		<div class="row">
			<?php foreach($ratings as $product): ?>
				<?php if($product['order_lot'] == 40 || $product['order_lot'] == 50): ?>
					<div class="col-sm-12 col-md-4">
						<div class="chart" id="<?php echo $product['product_id'] ?>"></div>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
			<div class="col-sm-12 col-md-4">
				<div class="chart" id="kat_ost"></div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12 col-md-4">
				<div class="chart" id="ges"></div>
			</div>
			<div class="col-sm-12 col-md-4">
				<div class="chart" id="kat_aps"></div>
			</div>
		</div>
    </div>
</div>