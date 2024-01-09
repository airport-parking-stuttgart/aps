<?php
global $wpdb;
if(isset($_POST['name'])){
    $id = (int)$_POST['id'];
    unset($_POST['id']);
    $wpdb->update($wpdb->prefix . 'itweb_prices', $_POST, ['id' => $id]);
	
	$data1 = array(
		'request' => 'apm_price_update',
		 'pw' => 'apmpru_req57159428',
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
$price = $wpdb->get_row("select * from " . $wpdb->prefix . "itweb_prices where id = " . $_GET['edit_price']);

$price = json_decode(json_encode($price), true);

?>
<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-title itweb_adminpage_head">
		<h3>Preisschienen bearbeiten - <?php echo $price['name']; ?></h3>
	</div>
	<form class="update-price" action="#" method="POST">
		<div class="row">
			<div class="col-1 col-sm-1 col-md-1">
				<div class="row">
				<div class="col-sm-12 col-md-12">                    
					<a href="<?php echo '/wp-admin/admin.php?page=prices' ?>" class="btn btn-secondary d-block w-100" >Schließen</a><br>
				</div>
					<?php foreach ($price as $key => $value) : ?>
						<?php if ($key == 'id') : ?>
							<input type="hidden" name="id" value="<?php echo $value; ?>">
						<?php else : ?>
							<div class="col-12 col-sm-12 col-md-12">
								<?php if ($key !== 'user_id') : ?>									
									<?php
										if($key == "year"){
											$lable = "Jahr";
										}
										else
											$lable = str_replace('Day_', 'Tag ', ucfirst($key)); 
									?>
									<label for=""><?php echo $lable; ?></label>
								<?php endif; ?>
								<?php if ($key == 'name') : ?>
									<input class="form-control" type="text" name="name" value="<?php echo $value; ?>" readonly>
								<?php elseif ($key !== 'user_id'): ?>
									<input class="form-control" type="number" name="<?php echo $key; ?>" value="<?php echo $value; ?>">
								<?php endif; ?>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-12">
				<button type="submit" class="btn btn-primary m10">Speichern</button>
			</div>
		</div>
	</form>
</div>
