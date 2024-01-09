<?php
global $wpdb;
if (isset($_GET["date"])) {
    $date = (explode(" - ", $_GET["date"]));
    $date[0] = date('Y-m-d', strtotime($date[0]));
    $date[1] = date('Y-m-d', strtotime($date[1]));
    $period = new DatePeriod(
        new DateTime($date[0]),
        new DateInterval('P1D'),
        new DateTime($date[1] . '+1 day')
    );

} else {
    $today = date('Y-m-d');
    $period = new DatePeriod(
        new DateTime($today),
        new DateInterval('P1D'),
        new DateTime($today . '+7 day')
    );

    $date = [
        $period->start->format('Y-m-d'),
        $period->end->format('Y-m-d')
    ];
}

?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Kontingentübersicht</h3>
    </div>
    <div class="page-body">
        <form class="form-filter">
            <div class="row ui-lotdata-block ui-lotdata-block-next">
                <h5 class="ui-lotdata-title">Nach Datum filtern</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-md-2">
							<input type="text" class="datepicker-range form-item form-control" name="date"
								   data-multiple-dates-separator=" - " placeholder="" autocomplete="off"
								   data-from="<?php echo $date[0] ? $date[0] : '' ?>" data-to="<?php echo $date[1] ? $date[1] : '' ?>">
						</div>
						<div class="col-sm-12 col-md-1">
							<button class="btn btn-primary d-block w-100" type="submit">Filter</button>
						</div>
					</div>
				</div>
            </div>
        </form>
		
        <div class="">
            <?php
			
			foreach ($period as $key => $value) {
               
				$output[$key] = Database::getInstance()->getParkotsWithOrdersData($value->format('Y-m-d'));
				for($i = 0; $i < count($output[$key]); $i++){
					$output[$key][$i]->Datum = $value->format('Y-m-d');
				}				
			}

			$z = 0;
			$c =  count($output[0]);
			for($i = 0; $i <= ($c - 1); $i++){							
				foreach ($period as $key => $value) {					
					$data[$i][$key] = $output[$key][$z];					
				}
				$z++;
			}
			//echo "<pre>";
			//print_r($data);
			//echo "</pre>";
								
			?>
        </div>
		<?php for($i = 0; $i <= ($c - 1); $i++) : ?>				
		<script type="text/javascript">
			google.charts.load('current', {'packages': ['corechart']});
			google.charts.setOnLoadCallback(drawVisualization_<?php echo $i ?>);

			function drawVisualization_<?php echo $i ?>() {
				// Some raw data (not necessarily accurate)
				var data = google.visualization.arrayToDataTable([
					['Datum', 'Gebucht', { role: 'annotation'}, 'Frei', { role: 'annotation'}],
					<?php
					foreach ($period as $key => $value) {
						$free = $data[$i][$key]->contigent - $data[$i][$key]->used;
						echo "['{$data[$i][$key]->Datum}', {$data[$i][$key]->used}, '{$data[$i][$key]->used}', {$free}, '{$free}'],";						
					}
					?>
					
				]);

				var options = {
					isStacked: 'percent',
					title: 'Kontingent <?php echo $data[$i][$key]->parklot ?>',
					vAxis: {title: 'Kontingent'},
					hAxis: {title: 'Tag'},
					seriesType: 'bars',			
					series: {5: {type: 'line'}},
					annotations: {
						textStyle: {
						  fontSize: 11,
						  auraColor: 'none',
						}
					}
				};

				var chart = new google.visualization.ComboChart(document.getElementById('kontingent_<?php echo $i ?>'));
				chart.draw(data, options);
			}					
		</script>
		<div class="row">
			<div class="col-sm-12 col-md-12">
				<div class="chart" id="<?php echo "kontingent_".$i ?>"></div>
			</div>
		</div>
		<?php endfor; ?>				
    </div>
</div>