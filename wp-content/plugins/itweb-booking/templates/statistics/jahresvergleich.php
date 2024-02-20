<?php 

$clients = Database::getInstance()->getAllClients();
$brokers = Database::getInstance()->getBrokers();


if (isset($_GET["year"]))
    $current_year = $_GET["year"];
else
    $current_year = date('Y');

$last_year = $current_year - 1;

$months = array('1' => 'Januar', '2' => 'Februar', '3' => 'März', '4' => 'April', '5' => 'Mai', '6' => 'Juni',
				'7' => 'Juli', '8' => 'August', '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Dezember');

if($_GET['type'] == null || $_GET['type'] == 'b'){
	for($i = 1; $i <= 12; $i++){
		foreach ($clients as $client){
			$salesMonth_c_ly[$client->short] = Database::getInstance()->getSalesProductsV2($i, $last_year, $client->id, "betreiber", "month", 'processing');
		}
			
		foreach ($salesMonth_c_ly as $short => $operators) {
			foreach($operators as $operator){
				$m = $operator->Monat < 10 ? "0".$operator->Monat : $operator->Monat;
				$d = $operator->Tag < 10 ? "0".$operator->Tag : $operator->Tag;
				if(date($m."-".$d) <= date("m-d")){	
					$data_ly[$i][$short]['sumOrders'] += $operator->Buchungen;
					$data_ly[$i][$short]['sumGross'] += ($operator->Brutto_b + $operator->Brutto_k);
				}
			}
			
		}
	}

	for($i = 1; $i <= 12; $i++){
		foreach ($brokers as $broker){
			$salesMonth_b_ly[$broker->short] = Database::getInstance()->getSalesProductsV2($i, $last_year, $broker->id, "vermittler", "month", 'processing');	
		}
			
		foreach ($salesMonth_b_ly as $short => $operators) {
			foreach($operators as $operator){
				$m = $operator->Monat < 10 ? "0".$operator->Monat : $operator->Monat;
				$d = $operator->Tag < 10 ? "0".$operator->Tag : $operator->Tag;
				if(date($m."-".$d) <= date("m-d")){	
					$data_ly[$i][$short]['sumOrders'] += $operator->Buchungen;
					$data_ly[$i][$short]['sumGross'] += ($operator->Brutto_b + $operator->Brutto_k);
				}
			}
			
		}
	}

	for($i = 1; $i <= 12; $i++){
		foreach ($clients as $client){
			$salesMonth_c_cy[$client->short] = Database::getInstance()->getSalesProductsV2($i, $current_year, $client->id, "betreiber", "month", 'processing');	
		}
			
		foreach ($salesMonth_c_cy as $short => $operators) {
			foreach($operators as $operator){	
				$data[$i][$short]['sumOrders'] += $operator->Buchungen;
				$data[$i][$short]['sumGross'] += ($operator->Brutto_b + $operator->Brutto_k);
				
			}			
		}
	}

	for($i = 1; $i <= 12; $i++){
		foreach ($brokers as $broker){
			$salesMonth_b_cy[$broker->short] = Database::getInstance()->getSalesProductsV2($i, $current_year, $broker->id, "vermittler", "month", 'processing');	
		}
			
		foreach ($salesMonth_b_cy as $short => $operators) {
			foreach($operators as $operator){	
				$data[$i][$short]['sumOrders'] += $operator->Buchungen;
				$data[$i][$short]['sumGross'] += ($operator->Brutto_b + $operator->Brutto_k);
			}			
		}
	}

	for($i = 0; $i <= count($clients)-1; $i++){
		$names[$i] = $clients[$i]->short;
	}
	for($i = 0; $i <= count($brokers)-1; $i++){
		$names[$i+count($clients)] = $brokers[$i]->short;
	}

	for($i = 1; $i <= 12; $i++){
		foreach($names as $name){
			$wert_ly = $data_ly[$i][$name]['sumGross'] != null ? number_format($data_ly[$i][$name]['sumGross'], 0, "", "") : 0;
			$data_ly[$i][$name]['sumGross'] = $wert_ly;
			$summe_umsatz_ly[$i] += $wert_ly;
			
			$buchungen_ly = $data_ly[$i][$name]['sumOrders'] != null ? number_format($data_ly[$i][$name]['sumOrders'], 0, "", "") : 0;
			$data_ly[$i][$name]['sumOrders'] = $buchungen_ly;
			$summe_buchungen_ly[$i] += $buchungen_ly;
			
			$wert = $data[$i][$name]['sumGross'] != null ? number_format($data[$i][$name]['sumGross'], 0, "", "") : 0;
			$data[$i][$name]['sumGross'] = $wert;
			$summe_umsatz[$i] += $wert;
			
			$buchungen = $data[$i][$name]['sumOrders'] != null ? number_format($data[$i][$name]['sumOrders'], 0, "", "") : 0;
			$data[$i][$name]['sumOrders'] = $buchungen;
			$summe_buchungen[$i] += $buchungen;
		}
	}

	for($i = 1; $i <= 12; $i++){
		$dsu_ly[$i] = $summe_buchungen_ly[$i] != 0 && $summe_umsatz_ly[$i] != 0 ? number_format($summe_umsatz_ly[$i] / $summe_buchungen_ly[$i], 0, "", "") : 0;
		$dsu[$i] = $summe_buchungen[$i] != 0 && $summe_umsatz[$i] != 0 ? number_format($summe_umsatz[$i] / $summe_buchungen[$i], 0, "", "") : 0;
	}
}
else{
	for($i = 1; $i <= 12; $i++){
		foreach ($clients as $client){
			$salesMonth_c_ly[$client->short] = Database::getInstance()->statistic_getSalesArrivalsProducts(date($last_year.'-m-d'), $i, $last_year, $client->id, "betreiber", "month", 'processing');
		}
			
		foreach ($salesMonth_c_ly as $short => $operators) {
			foreach($operators as $operator){
				$data_ly[$i][$short]['sumOrders'] += $operator->Buchungen;
				$data_ly[$i][$short]['sumGross'] += ($operator->Brutto_b + $operator->Brutto_k);
				
			}
			
		}
	}
	
	for($i = 1; $i <= 12; $i++){
		foreach ($brokers as $broker){
			$salesMonth_b_ly[$broker->short] = Database::getInstance()->statistic_getSalesArrivalsProducts(date($last_year.'-m-d'), $i, $last_year, $broker->id, "vermittler", "month", 'processing');	
		}
			
		foreach ($salesMonth_b_ly as $short => $operators) {
			foreach($operators as $operator){	
				$data_ly[$i][$short]['sumOrders'] += $operator->Buchungen;
				$data_ly[$i][$short]['sumGross'] += ($operator->Brutto_b + $operator->Brutto_k);
			}
			
		}
	}

	for($i = 1; $i <= 12; $i++){
		foreach ($clients as $client){
			$salesMonth_c_cy[$client->short] = Database::getInstance()->statistic_getSalesArrivalsProducts(date($current_year.'-m-d'), $i, $current_year, $client->id, "betreiber", "month", 'processing');	
		}
			
		foreach ($salesMonth_c_cy as $short => $operators) {
			foreach($operators as $operator){
				$data[$i][$short]['sumOrders'] += $operator->Buchungen;
				$data[$i][$short]['sumGross'] += ($operator->Brutto_b + $operator->Brutto_k);
			}			
		}
	}

	for($i = 1; $i <= 12; $i++){
		foreach ($brokers as $broker){
			$salesMonth_b_cy[$broker->short] = Database::getInstance()->statistic_getSalesArrivalsProducts(date($current_year.'-m-d'), $i, $current_year, $broker->id, "vermittler", "month", 'processing');	
		}
			
		foreach ($salesMonth_b_cy as $short => $operators) {
			foreach($operators as $operator){
				$data[$i][$short]['sumOrders'] += $operator->Buchungen;
				$data[$i][$short]['sumGross'] += ($operator->Brutto_b + $operator->Brutto_k);
			}			
		}
	}

	for($i = 0; $i <= count($clients)-1; $i++){
		$names[$i] = $clients[$i]->short;
	}
	for($i = 0; $i <= count($brokers)-1; $i++){
		$names[$i+count($clients)] = $brokers[$i]->short;
	}

	for($i = 1; $i <= 12; $i++){
		foreach($names as $name){
			$wert_ly = $data_ly[$i][$name]['sumGross'] != null ? number_format($data_ly[$i][$name]['sumGross'], 0, "", "") : 0;
			$data_ly[$i][$name]['sumGross'] = $wert_ly;
			$summe_umsatz_ly[$i] += $wert_ly;
			
			$buchungen_ly = $data_ly[$i][$name]['sumOrders'] != null ? number_format($data_ly[$i][$name]['sumOrders'], 0, "", "") : 0;
			$data_ly[$i][$name]['sumOrders'] = $buchungen_ly;
			$summe_buchungen_ly[$i] += $buchungen_ly;
			
			$wert = $data[$i][$name]['sumGross'] != null ? number_format($data[$i][$name]['sumGross'], 0, "", "") : 0;
			$data[$i][$name]['sumGross'] = $wert;
			$summe_umsatz[$i] += $wert;
			
			$buchungen = $data[$i][$name]['sumOrders'] != null ? number_format($data[$i][$name]['sumOrders'], 0, "", "") : 0;
			$data[$i][$name]['sumOrders'] = $buchungen;
			$summe_buchungen[$i] += $buchungen;
		}
	}

	for($i = 1; $i <= 12; $i++){
		$dsu_ly[$i] = $summe_buchungen_ly[$i] != 0 && $summe_umsatz_ly[$i] != 0 ? number_format($summe_umsatz_ly[$i] / $summe_buchungen_ly[$i], 0, "", "") : 0;
		$dsu[$i] = $summe_buchungen[$i] != 0 && $summe_umsatz[$i] != 0 ? number_format($summe_umsatz[$i] / $summe_buchungen[$i], 0, "", "") : 0;
	}
}
//echo "<pre>"; print_r($salesMonth_c_cy); echo "</pre>";
?>


<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<?php if($_GET['type'] == null || $_GET['type'] == 'b'): ?>
<script type="text/javascript">
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(umsatz);
	google.charts.setOnLoadCallback(buchungen);
	google.charts.setOnLoadCallback(sum_umsatz);
	google.charts.setOnLoadCallback(sum_buchungen);

    // Funktion zum Zeichnen des Diagramms
    function umsatz() {
    // Daten für das Diagramm
    var data = google.visualization.arrayToDataTable([
        ['Monat'
            <?php
            foreach ($names as $name){
                echo ", '{$name} {$last_year}', { role: 'annotation'}, '{$name} {$current_year}', { role: 'annotation'}";                    
            }
            ?>
        ],

        <?php
            for($i = 1; $i <= 12; $i++) {
                echo "['{$months[$i]}'";
                foreach ($names as $name){                    
                    $betrag_ly = $data_ly[$i][$name]['sumGross'];
                    $betrag = $data[$i][$name]['sumGross'];
                    
					$ds_ly = $data_ly[$i][$name]['sumOrders'] != 0 ? number_format($data_ly[$i][$name]['sumGross'] / $data_ly[$i][$name]['sumOrders'], 0, "", "") : 0;
					$ds = $data[$i][$name]['sumOrders'] != 0 ? number_format($data[$i][$name]['sumGross'] / $data[$i][$name]['sumOrders'], 0, "", "") : 0;
					
					echo ", {$betrag_ly}, '{$betrag_ly}€ (⌀{$ds_ly}€)', {$betrag}, '{$betrag}€ (⌀{$ds}€)'";
                }
                echo "],";
            }
        ?>
    ]);

    // Optionen für das Diagramm
    var options = {
        title: 'Umsatz pro Monat im Vergleich zu ' + <?php echo $last_year ?> + ' und ' + <?php echo $current_year ?>,
        legend: { position: 'top', maxLines: 3, textStyle: { fontSize: 12 } },
        bars: 'vertical', // Vertikale Balken
        isStacked: false, // Balken nicht stapeln (Clustered)
        bar: { groupWidth: '100%', width: '100%' }, // Anpassung der Balkenbreite
        height: 2000,
        colors: ['#1e73be', '#1e73be', '#00163d', '#00163d', '#58007a', '#58007a', '#c6c900', '#c6c900', '#16c902', '#16c902'],
        vAxis: {
            title: 'Monat' // Titel für die Y-Achse
        },
        hAxis: {
            title: 'Betrag in €' // Titel für die X-Achse
        },
        annotations: {
            textStyle: {
                fontSize: 12,
                color: 'black'
            }
        },
        chartArea: {
            top: 200, // Reduzieren Sie den oberen Rand nach Bedarf
            left: 200, // Reduzieren Sie den linken Rand nach Bedarf
            right: 100, // Reduzieren Sie den rechten Rand nach Bedarf
            bottom: 150 // Reduzieren Sie den unteren Rand nach Bedarf
        }
    };

    // Diagramm zeichnen
    var chart = new google.visualization.BarChart(document.getElementById('umsatz'));

    chart.draw(data, options);
}
	
	// Funktion zum Zeichnen des Diagramms
    function buchungen() {
      // Daten für das Diagramm
      var data = google.visualization.arrayToDataTable([
        ['Monat'
			<?php
			foreach ($names as $name){
				echo ", '{$name} {$last_year}', { role: 'annotation'}, '{$name} {$current_year}', { role: 'annotation'}";					
			}
			?>
		],
        
			<?php
				for($i = 1; $i <= 12; $i++) {
					echo "['{$months[$i]}'";
					foreach ($names as $name){					
						$betrag_ly = $data_ly[$i][$name]['sumOrders'];
						$betrag = $data[$i][$name]['sumOrders'];
						
						$ds_ly = $data_ly[$i][$name]['sumOrders'] != 0 ? number_format($data_ly[$i][$name]['sumGross'] / $data_ly[$i][$name]['sumOrders'], 0, "", "") : 0;
						$ds = $data[$i][$name]['sumOrders'] != 0 ? number_format($data[$i][$name]['sumGross'] / $data[$i][$name]['sumOrders'], 0, "", "") : 0;
						
						echo ", {$betrag_ly}, '{$betrag_ly} (⌀{$ds_ly}€)', {$betrag}, '{$betrag} (⌀{$ds}€)'";
					}
					echo "],";
				}
            ?>
      ]);

      // Optionen für das Diagramm
      var options = {
        title: 'Buchungen pro Monat im Vergleich zu ' + <?php echo $last_year ?> + ' und ' + <?php echo $current_year ?>,
        legend: { position: 'top', maxLines: 3, textStyle: { fontSize: 12 } },
        bars: 'vertical', // Vertikale Balken
        isStacked: false, // Balken nicht stapeln (Clustered)
        bar: { groupWidth: '100%', width: '100%' }, // Anpassung der Balkenbreite
        height: 2000,
		colors: ['#1e73be', '#1e73be', '#00163d', '#00163d', '#58007a', '#58007a', '#c6c900', '#c6c900', '#16c902', '#16c902'],
		vAxis: {
          title: 'Monat' // Titel für die Y-Achse
        },
		hAxis: {
          title: 'Anzahl' // Titel für die X-Achse
        },
		annotations: {
          textStyle: {
            fontSize: 12,
            color: 'black'
          }
		},
		chartArea: {
          top: 200, // Reduzieren Sie den oberen Rand nach Bedarf
          left: 200, // Reduzieren Sie den linken Rand nach Bedarf
          right: 100, // Reduzieren Sie den rechten Rand nach Bedarf
          bottom: 150 // Reduzieren Sie den unteren Rand nach Bedarf
        }
      };

      // Diagramm zeichnen
      var chart = new google.visualization.BarChart(document.getElementById('buchungen'));
	  
	  chart.draw(data, options);
    }
	
	function sum_umsatz() {
        // Some raw data (not necessarily accurate)
        var data = google.visualization.arrayToDataTable([
            ['Monate', 'Umsatz ' + <?php echo $last_year ?>, { role: 'annotation'}, 'Umsatz ' + <?php echo $current_year ?>, { role: 'annotation'}],
			
			<?php
            for($i = 1; $i <= 12; $i++) {
                $betrag_ly = $summe_umsatz_ly[$i];
				$betrag = $summe_umsatz[$i];
				$ds_ly = $dsu_ly[$i];
				$ds = $dsu[$i];
                echo "['{$months[$i]}', {$betrag_ly}, '{$betrag_ly}€ (⌀{$ds_ly}€)', {$betrag}, '{$betrag}€ (⌀{$ds}€)'],";
            }
            ?>
        ]);

        var options = {
            title: 'Summe Umsatz ' + <?php echo $last_year ?> + ' und ' + <?php echo $current_year ?>,
            vAxis: {title: 'Betrag in €'},
            hAxis: {title: 'Monat'},
            seriesType: 'bars',			
            series: {5: {type: 'line'}},
			annotations: {
				textStyle: {
				  fontSize: 11,
				  auraColor: 'none',
				  color: '#871b47',
				}
			}
        };

        var chart = new google.visualization.ComboChart(document.getElementById('sum_umsatz'));
        chart.draw(data, options);
    }
	
	function sum_buchungen() {
        // Some raw data (not necessarily accurate)
        var data = google.visualization.arrayToDataTable([
            ['Monate', 'Buchungen ' + <?php echo $last_year ?>, { role: 'annotation'}, 'Buchungen ' + <?php echo $current_year ?>, { role: 'annotation'}],
			
			<?php
            for($i = 1; $i <= 12; $i++) {
                $betrag_ly = $summe_buchungen_ly[$i];
				$betrag = $summe_buchungen[$i];
                echo "['{$months[$i]}', {$betrag_ly}, '{$betrag_ly}', {$betrag}, '{$betrag}'],";
            }
            ?>
        ]);

        var options = {
            title: 'Summe Buchungen ' + <?php echo $last_year ?> + ' und ' + <?php echo $current_year ?>,
            vAxis: {title: 'Anzahl'},
            hAxis: {title: 'Monat'},
            seriesType: 'bars',			
            series: {5: {type: 'line'}},
			annotations: {
				textStyle: {
				  fontSize: 11,
				  auraColor: 'none',
				  color: '#871b47',
				}
			}
        };

        var chart = new google.visualization.ComboChart(document.getElementById('sum_buchungen'));
        chart.draw(data, options);
    }
	
</script>
<?php else: ?>
<script type="text/javascript">
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(umsatz);
	google.charts.setOnLoadCallback(buchungen);
	google.charts.setOnLoadCallback(sum_umsatz);
	google.charts.setOnLoadCallback(sum_buchungen);

    // Funktion zum Zeichnen des Diagramms
    function umsatz() {
    // Daten für das Diagramm
    var data = google.visualization.arrayToDataTable([
        ['Monat'
            <?php
            foreach ($names as $name){
                echo ", '{$name} {$last_year}', { role: 'annotation'}, '{$name} {$current_year}', { role: 'annotation'}";                    
            }
            ?>
        ],

        <?php
            for($i = 1; $i <= 12; $i++) {
                echo "['{$months[$i]}'";
                foreach ($names as $name){                    
                    $betrag_ly = $data_ly[$i][$name]['sumGross'];
                    $betrag = $data[$i][$name]['sumGross'];
                    
					$ds_ly = $data_ly[$i][$name]['sumOrders'] != 0 ? number_format($data_ly[$i][$name]['sumGross'] / $data_ly[$i][$name]['sumOrders'], 0, "", "") : 0;
					$ds = $data[$i][$name]['sumOrders'] != 0 ? number_format($data[$i][$name]['sumGross'] / $data[$i][$name]['sumOrders'], 0, "", "") : 0;
					
					echo ", {$betrag_ly}, '{$betrag_ly}€ (⌀{$ds_ly}€)', {$betrag}, '{$betrag}€ (⌀{$ds}€)'";
                }
                echo "],";
            }
        ?>
    ]);

    // Optionen für das Diagramm
    var options = {
        title: 'Ist-Umsatz pro Monat im Vergleich zu ' + <?php echo $last_year ?> + ' und ' + <?php echo $current_year ?>,
        legend: { position: 'top', maxLines: 3, textStyle: { fontSize: 12 } },
        bars: 'vertical', // Vertikale Balken
        isStacked: false, // Balken nicht stapeln (Clustered)
        bar: { groupWidth: '100%', width: '100%' }, // Anpassung der Balkenbreite
        height: 2000,
        colors: ['#1e73be', '#1e73be', '#00163d', '#00163d', '#58007a', '#58007a', '#c6c900', '#c6c900', '#16c902', '#16c902'],
        vAxis: {
            title: 'Monat' // Titel für die Y-Achse
        },
        hAxis: {
            title: 'Betrag in €' // Titel für die X-Achse
        },
        annotations: {
            textStyle: {
                fontSize: 12,
                color: 'black'
            }
        },
        chartArea: {
            top: 200, // Reduzieren Sie den oberen Rand nach Bedarf
            left: 200, // Reduzieren Sie den linken Rand nach Bedarf
            right: 100, // Reduzieren Sie den rechten Rand nach Bedarf
            bottom: 150 // Reduzieren Sie den unteren Rand nach Bedarf
        }
    };

    // Diagramm zeichnen
    var chart = new google.visualization.BarChart(document.getElementById('umsatz'));

    chart.draw(data, options);
}
	
	// Funktion zum Zeichnen des Diagramms
    function buchungen() {
      // Daten für das Diagramm
      var data = google.visualization.arrayToDataTable([
        ['Monat'
			<?php
			foreach ($names as $name){
				echo ", '{$name} {$last_year}', { role: 'annotation'}, '{$name} {$current_year}', { role: 'annotation'}";					
			}
			?>
		],
        
			<?php
				for($i = 1; $i <= 12; $i++) {
					echo "['{$months[$i]}'";
					foreach ($names as $name){					
						$betrag_ly = $data_ly[$i][$name]['sumOrders'];
						$betrag = $data[$i][$name]['sumOrders'];
						
						$ds_ly = $data_ly[$i][$name]['sumOrders'] != 0 ? number_format($data_ly[$i][$name]['sumGross'] / $data_ly[$i][$name]['sumOrders'], 0, "", "") : 0;
						$ds = $data[$i][$name]['sumOrders'] != 0 ? number_format($data[$i][$name]['sumGross'] / $data[$i][$name]['sumOrders'], 0, "", "") : 0;
						
						echo ", {$betrag_ly}, '{$betrag_ly} (⌀{$ds_ly}€)', {$betrag}, '{$betrag} (⌀{$ds}€)'";
					}
					echo "],";
				}
            ?>
      ]);

      // Optionen für das Diagramm
      var options = {
        title: 'Anreisen pro Monat im Vergleich zu ' + <?php echo $last_year ?> + ' und ' + <?php echo $current_year ?>,
        legend: { position: 'top', maxLines: 3, textStyle: { fontSize: 12 } },
        bars: 'vertical', // Vertikale Balken
        isStacked: false, // Balken nicht stapeln (Clustered)
        bar: { groupWidth: '100%', width: '100%' }, // Anpassung der Balkenbreite
        height: 2000,
		colors: ['#1e73be', '#1e73be', '#00163d', '#00163d', '#58007a', '#58007a', '#c6c900', '#c6c900', '#16c902', '#16c902'],
		vAxis: {
          title: 'Monat' // Titel für die Y-Achse
        },
		hAxis: {
          title: 'Anzahl' // Titel für die X-Achse
        },
		annotations: {
          textStyle: {
            fontSize: 12,
            color: 'black'
          }
		},
		chartArea: {
          top: 200, // Reduzieren Sie den oberen Rand nach Bedarf
          left: 200, // Reduzieren Sie den linken Rand nach Bedarf
          right: 100, // Reduzieren Sie den rechten Rand nach Bedarf
          bottom: 150 // Reduzieren Sie den unteren Rand nach Bedarf
        }
      };

      // Diagramm zeichnen
      var chart = new google.visualization.BarChart(document.getElementById('buchungen'));
	  
	  chart.draw(data, options);
    }
	
	function sum_umsatz() {
        // Some raw data (not necessarily accurate)
        var data = google.visualization.arrayToDataTable([
            ['Monate', 'Ist-Umsatz ' + <?php echo $last_year ?>, { role: 'annotation'}, 'Ist-Umsatz ' + <?php echo $current_year ?>, { role: 'annotation'}],
			
			<?php
            for($i = 1; $i <= 12; $i++) {
                $betrag_ly = $summe_umsatz_ly[$i];
				$betrag = $summe_umsatz[$i];
				$ds_ly = $dsu_ly[$i];
				$ds = $dsu[$i];
                echo "['{$months[$i]}', {$betrag_ly}, '{$betrag_ly}€ (⌀{$ds_ly})', {$betrag}, '{$betrag}€ (⌀{$ds})'],";
            }
            ?>
        ]);

        var options = {
            title: 'Summe Ist-Umsatz ' + <?php echo $last_year ?> + ' und ' + <?php echo $current_year ?>,
            vAxis: {title: 'Betrag in €'},
            hAxis: {title: 'Monat'},
            seriesType: 'bars',			
            series: {5: {type: 'line'}},
			annotations: {
				textStyle: {
				  fontSize: 11,
				  auraColor: 'none',
				  color: '#871b47',
				}
			}
        };

        var chart = new google.visualization.ComboChart(document.getElementById('sum_umsatz'));
        chart.draw(data, options);
    }
	
	function sum_buchungen() {
        // Some raw data (not necessarily accurate)
        var data = google.visualization.arrayToDataTable([
            ['Monate', 'Anreisen ' + <?php echo $last_year ?>, { role: 'annotation'}, 'Anreisen ' + <?php echo $current_year ?>, { role: 'annotation'}],
			
			<?php
            for($i = 1; $i <= 12; $i++) {
                $betrag_ly = $summe_buchungen_ly[$i];
				$betrag = $summe_buchungen[$i];
                echo "['{$months[$i]}', {$betrag_ly}, '{$betrag_ly}', {$betrag}, '{$betrag}'],";
            }
            ?>
        ]);

        var options = {
            title: 'Summe Anreisen ' + <?php echo $last_year ?> + ' und ' + <?php echo $current_year ?>,
            vAxis: {title: 'Anzahl'},
            hAxis: {title: 'Monat'},
            seriesType: 'bars',			
            series: {5: {type: 'line'}},
			annotations: {
				textStyle: {
				  fontSize: 11,
				  auraColor: 'none',
				  color: '#871b47',
				}
			}
        };

        var chart = new google.visualization.ComboChart(document.getElementById('sum_buchungen'));
        chart.draw(data, options);
    }
	
</script>
<?php endif; ?>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-title itweb_adminpage_head">
        <?php if($_GET['type'] == null || $_GET['type'] == 'b'): ?>
			<h3>Jahresvergleich getätigte Buchungen</h3>
		<?php else : ?>
			<h3>Jahresvergleich Ist-Umsatz und Anreisen</h3>
		<?php endif; ?>
    </div>
    <div class="page-body">
        <form class="form-filter">
            <div class="row ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Umsätze filtern</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-md-1">
							<select name="year" class="form-item form-control">
								<?php for ($i = 2021; $i <= date('Y'); $i++) : ?>
									<option value="<?php echo $i ?>" <?php echo $i == $current_year ? ' selected' : '' ?>>
										<?php echo $i ?>
									</option>
								<?php endfor; ?>
							</select>
						</div>
						<div class="col-sm-12 col-md-2">
							<select name="type" class="form-item form-control">
									<option value="b" <?php echo $_GET['type'] == "b" ? ' selected' : '' ?>>Getätigte Buchungen</option>
									<option value="i" <?php echo $_GET['type'] == "i" ? ' selected' : '' ?>>Ist-Umsatz</option>
							</select>
						</div>
						<div class="col-sm-12 col-md-1">
							<button class="btn btn-primary d-block w-100" type="submit">Filter</button>
						</div>
					</div>
				</div>
            </div>
		</form>
		<div class="row">
			<div class="col-sm-12 col-md-6" style="height: 2000px;">
				<div class="chart" id="umsatz"></div>
			</div>
			<div class="col-sm-12 col-md-6">
				<div class="chart" id="buchungen"></div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12 col-md-12">
				<div class="chart" id="sum_umsatz"></div>
			</div>
			<div class="col-sm-12 col-md-12">
				<div class="chart" id="sum_buchungen"></div>
			</div>
		</div>
	</div>
</div>	