<?php
global $wpdb;
$base_url = $_SERVER['HTTP_HOST'];

if (isset($_GET["year"]))
    $year = $_GET["year"];
else
    $year = date('Y');

?>

<?php if (isset($_GET['edit_price'])) {
    require_once 'edit-price.php';
} else { ?>
<?php
if(isset($_POST['name'])){
	unset($_POST['autofill']);
    $wpdb->insert($wpdb->prefix . 'itweb_prices', $_POST);
	
	if($base_url == "airport-parking-stuttgart.de"){
		$data1 = array(
			'request' => 'apm_price',
			 'pw' => 'apmpr_req57159428',
			 'prices' => $_POST
		);
		
		$query1 = http_build_query($data1);
		$query2 = http_build_query($data1);
		
		$ch1 = curl_init();
		$ch2 = curl_init();
		
		curl_setopt($ch1, CURLOPT_URL, 'https://airport-parking-germany.de/search-result/');
		curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch1, CURLOPT_POST, true);
		curl_setopt($ch1, CURLOPT_POSTFIELDS, $query1);

		curl_setopt($ch2, CURLOPT_URL, 'https://parken-zum-fliegen.de/curl/');
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch2, CURLOPT_POST, true);
		curl_setopt($ch2, CURLOPT_POSTFIELDS, $query2);
		
		$mh = curl_multi_init();
		
		curl_multi_add_handle($mh, $ch1);
		curl_multi_add_handle($mh, $ch2);
		
		do {
			curl_multi_exec($mh, $running);
		} while ($running > 0);
		
		$response1 = curl_multi_getcontent($ch1);
		$response2 = curl_multi_getcontent($ch2);
		
		curl_multi_remove_handle($mh, $ch1);
		curl_multi_remove_handle($mh, $ch2);
		curl_multi_close($mh);
		
		curl_close($ch1);
		curl_close($ch2);
	}
}

$sql = "select * from " . $wpdb->prefix . "itweb_prices where year = " . $year;
$prices = $wpdb->get_results($sql);
?>
<style>

.tableFixHead          { overflow: auto; height: 600px; }
.tableFixHead thead th { position: sticky; top: 0; z-index: 1; }

table  { width: 100%; font-size: 12px;}
th, td { padding: 8px 16px; }
th     { background:#eee; }

</style>

<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-title itweb_adminpage_head">
		<h3>Preisschienen</h3>
	</div>
	<div class="row">
		<div class="col-sm-12 col-md-1">
			<select name="ps_year" class="form-item form-control" onchange="change_year(this)">
				<?php for ($i = 2023; $i <= date('Y')+1; $i++) : ?>
					<option value="<?php echo $i ?>" <?php echo $i == $year ? ' selected' : '' ?>>
						<?php echo $i ?>
					</option>
				<?php endfor; ?>
			</select>
		</div>
	</div>
	<br>
	<?php if(count($prices) > 0): ?>
		<div class="tableFixHead">		
			<table border="1" class="prices-table" >
				<thead class="th-prices" >
				<tr>
					<th>Name</th>
					<th>Tag 1</th>
					<th>Tag 2</th>
					<th>Tag 3</th>
					<th>Tag 4</th>
					<th>Tag 5</th>
					<th>Tag 6</th>
					<th>Tag 7</th>
					<th>Tag 8</th>
					<th>Tag 9</th>
					<th>Tag 10</th>
					<th>Tag 11</th>
					<th>Tag 12</th>
					<th>Tag 13</th>
					<th>Tag 14</th>
					<th>Tag 15</th>
					<th>Tag 16</th>
					<th>Tag 17</th>
					<th>Tag 18</th>
					<th>Tag 19</th>
					<th>Tag 20</th>
					<th>Tag 21</th>
					<th>Tag 22</th>
					<th>Tag 23</th>
					<th>Tag 24</th>
					<th>Tag 25</th>
					<th>Tag 26</th>
					<th>Tag 27</th>
					<th>Tag 28</th>
					<th>Tag 29</th>
					<th>Tag 30</th>
					<th></th>
				</tr>
				</thead>
				<tbody class="tb-prices">
				<?php foreach ($prices as $price) : ?>
					<tr>
						<td><?php echo $price->name; ?></td>
						<td><?php echo $price->day_1; ?></td>
						<td><?php echo $price->day_2; ?></td>
						<td><?php echo $price->day_3; ?></td>
						<td><?php echo $price->day_4; ?></td>
						<td><?php echo $price->day_5; ?></td>
						<td><?php echo $price->day_6; ?></td>
						<td><?php echo $price->day_7; ?></td>
						<td><?php echo $price->day_8; ?></td>
						<td><?php echo $price->day_9; ?></td>
						<td><?php echo $price->day_10; ?></td>
						<td><?php echo $price->day_11; ?></td>
						<td><?php echo $price->day_12; ?></td>
						<td><?php echo $price->day_13; ?></td>
						<td><?php echo $price->day_14; ?></td>
						<td><?php echo $price->day_15; ?></td>
						<td><?php echo $price->day_16; ?></td>
						<td><?php echo $price->day_17; ?></td>
						<td><?php echo $price->day_18; ?></td>
						<td><?php echo $price->day_19; ?></td>
						<td><?php echo $price->day_20; ?></td>
						<td><?php echo $price->day_21; ?></td>
						<td><?php echo $price->day_22; ?></td>
						<td><?php echo $price->day_23; ?></td>
						<td><?php echo $price->day_24; ?></td>
						<td><?php echo $price->day_25; ?></td>
						<td><?php echo $price->day_26; ?></td>
						<td><?php echo $price->day_27; ?></td>
						<td><?php echo $price->day_28; ?></td>
						<td><?php echo $price->day_29; ?></td>
						<td><?php echo $price->day_30; ?></td>
						<td>
							<div>
								<a href="javascript:void(0);" class="del-price"
								   data-id="<?php echo $price->id; ?>" data-name="<?php echo $price->name; ?>">Löschen</a>
							</div>
							<div>
								<a href="<?php echo $_SERVER['REQUEST_URI'] ?>&edit_price=<?php echo $price->id; ?>">Bearbeiten</a>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>		
		</div>
	<?php else: ?>
		<p>Keine Preise vorhanden.</p>
	<?php endif; ?>
    <div class="m10">
        <a href="/wp-admin/admin.php?page=prices" class="btn">Zurück</a>
        <button class="btn btn-primary" data-toggle="modal" data-target="#newPriceModal">Preisschiene hinzufügen</button>
    </div>

    <form action="#" method="POST">
        <div class="modal" id="newPriceModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Preisschiene hinzufügen</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12 col-md-4">
								<label for="">Jahr</label>
								<select name="year" class="form-control">
									<?php for ($i = 2023; $i <= date('Y')+1; $i++) : ?>
										<option value="<?php echo $i ?>" <?php echo $i == $year ? ' selected' : '' ?>>
											<?php echo $i ?>
										</option>
									<?php endfor; ?>
								</select>
							</div>
							<div class="col-12 col-sm-5 col-md-4">
                                <label for="">Name</label>
                                <input class="form-control" type="text" name="name">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 1</label>
                                <input class="form-control" type="number" name="day_1" id="1">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 2</label>
                                <input class="form-control" type="number" name="day_2" id="2">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 3</label>
                                <input class="form-control" type="number" name="day_3" id="3">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 4</label>
                                <input class="form-control" type="number" name="day_4" id="4">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 5</label>
                                <input class="form-control" type="number" name="day_5" id="5">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 6</label>
                                <input class="form-control" type="number" name="day_6" id="6">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 7</label>
                                <input class="form-control" type="number" name="day_7" id="7">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 8</label>
                                <input class="form-control" type="number" name="day_8" id="8">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 9</label>
                                <input class="form-control" type="number" name="day_9" id="9">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 10</label>
                                <input class="form-control" type="number" name="day_10" id="10">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 11</label>
                                <input class="form-control" type="number" name="day_11" id="11">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 12</label>
                                <input class="form-control" type="number" name="day_12" id="12">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 13</label>
                                <input class="form-control" type="number" name="day_13" id="13">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 14</label>
                                <input class="form-control" type="number" name="day_14" id="14">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 15</label>
                                <input class="form-control" type="number" name="day_15" id="15">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 16</label>
                                <input class="form-control" type="number" name="day_16" id="16">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 17</label>
                                <input class="form-control" type="number" name="day_17" id="17">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 18</label>
                                <input class="form-control" type="number" name="day_18" id="18">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 19</label>
                                <input class="form-control" type="number" name="day_19" id="19">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 20</label>
                                <input class="form-control" type="number" name="day_20" id="20">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 21</label>
                                <input class="form-control" type="number" name="day_21" id="21">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 22</label>
                                <input class="form-control" type="number" name="day_22" id="22">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 23</label>
                                <input class="form-control" type="number" name="day_23" id="23">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 24</label>
                                <input class="form-control" type="number" name="day_24" id="24">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 25</label>
                                <input class="form-control" type="number" name="day_25" id="25">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 26</label>
                                <input class="form-control" type="number" name="day_26" id="26">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 27</label>
                                <input class="form-control" type="number" name="day_27" id="27">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 28</label>
                                <input class="form-control" type="number" name="day_28" id="28">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 29</label>
                                <input class="form-control" type="number" name="day_29" id="29">
                            </div>
                            <div class="col-12 col-sm-5 col-md-4">
                                <label for="">Tag 30</label>
                                <input class="form-control" type="number" name="day_30" id="30">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="user_id" value="<?php echo get_current_user_id(); ?>">
                        <button type="submit" class="btn btn-primary">Speichern</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Schließen</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<script>
document.getElementById("autofill_btn").addEventListener("click", autofill);
function autofill(e){
	if(document.getElementById("autofill").value == '')
		alert("Feld ist leer.");
	else if (confirm('Einträge einfügen bzw. überschreiben?')){
		var price_day = document.getElementById("autofill").value;
		for (let i = 1; i <= 30; i++) {
			document.getElementById(i).value = price_day * i;
		}
	}
}

function change_year(e){
	var path = new URL(window.location.href);
	 path.searchParams.set('year', e.value);
	 location.href = path.href;
}
</script>
<?php } ?>