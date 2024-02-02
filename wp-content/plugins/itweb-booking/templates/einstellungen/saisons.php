<?php

if (isok($_POST, 'name')) {
	
	// update order cancellation
	if (isset($_POST['name']) && $_POST['name'] != "") {
		for ($i = 0; $i < count($_POST['name']); $i++) {
			if (empty($_POST['name'][$i])) {
				continue;
			}
			$date_from = date('Y-m-d', strtotime($_POST['date_from'][$i]));
			$date_to = date('Y-m-d', strtotime($_POST['date_to'][$i]));
			if (isset($_POST['saison_id'][$i]) && !empty($_POST['saison_id'][$i])) {
				Database::getInstance()->updateSaisons($_POST['saison_id'][$i], $_POST['name'][$i], $date_from, $date_to);
			} else {				
				Database::getInstance()->saveSaisons($_POST['name'][$i], $date_from, $date_to);
			}
		}
		header('Location: ' . $_SERVER['HTTP_REFERER']);
	}
	
	//echo "<pre>"; print_r($_POST); echo "</pre>";
}

$saisons = Database::getInstance()->getSaisons();

?>
<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Saisons Einstellungen</h3>
    </div>
	<div class="page-body">
		<form action="#" method="POST">
			<div class="row ui-lotdata-block ui-lotdata-block-next saison-wrapper">
				<h5 class="ui-lotdata-title">Saisons</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					
					<?php foreach ($saisons as $saison) : ?>
						<div class="col-12 row-item saison-item">
							<div class="row">
								<div class="col-sm-12 col-md-2">
									<input type="hidden" name="saison_id[]" value="<?php echo $saison->id ?>">
									<label for="">Name</label>
									<input type="text" name="name[]" placeholder="" class="w100" value="<?php echo $saison->name ?>">
								</div>
								<div class="col-sm-12 col-md-1 ui-lotdata-date">
									<label for="">Von</label><br>
									<input type="text" name="date_from[]" size="8" class="air-datepicker" autocomplete="off" data-language="de" value="<?php echo $saison->date_from ?>">
								</div>
								<div class="col-sm-12 col-md-1 ui-lotdata-date">
									<label for="">Bis</label><br>
									<input type="text" name="date_to[]" size="8" class="air-datepicker" autocomplete="off" data-language="de" value="<?php echo $saison->date_to ?>">
								</div>
								<div class="col-2 add_del_buttons">
									<span class="btn btn-danger del-table-row"
										  data-table="saisons" data-id="<?php echo $saison->id ?>">x</span>
									<span class="btn btn-secondary plus-icon add-saison-template">+</span>
								</div>
							</div>
						</div>
					 <?php endforeach; ?>
					
					<div class="col-12 row-item saison-item">
						<div class="row">
							<div class="col-sm-12 col-md-2">
								<input type="hidden" name="saison_id[]">
								<label for="">Name</label>
								<input type="text" name="name[]" placeholder="" class="w100">
							</div>
							<div class="col-sm-12 col-md-1 ui-lotdata-date">
									<label for="">Von</label><br>
									<input type="text" name="date_from[]" size="8" class="air-datepicker" autocomplete="off" data-language="de" value="">
								</div>
								<div class="col-sm-12 col-md-1 ui-lotdata-date">
									<label for="">Bis</label><br>
									<input type="text" name="date_to[]" size="8" class="air-datepicker" autocomplete="off" data-language="de" value="">
								</div>
							<div class="col-2 add_del_buttons">
								<span class="btn btn-danger del-table-row"
									  data-table="saisons">x</span>
								<span class="btn btn-secondary plus-icon add-saison-template">+</span>
							</div>
						</div>
					</div>
				</div>				
			</div>
			<div class="row m10">
				<div class="col-1">
					<button class="btn btn-primary">Speichern</button>
				</div>
				<div class="col-1">
					<a href="<?php echo '/wp-admin/admin.php?saisons' ?>" class="btn btn-secondary" >Zurück</a>
				</div>
			</div>
		</form>
    </div>
</div>