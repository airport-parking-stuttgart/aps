<?php

if(isset($_POST) && $_POST != null){
	Database::getInstance()->setSettings($_POST);
	echo '<script type="text/javascript">location.reload();</script>';
	//echo "<pre>"; print_r($_POST); echo "</pre>";
}
$settings = Database::getInstance()->getSettings();
//echo "<pre>"; print_r($settings); echo "</pre>";
?>
<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-title itweb_adminpage_head">
		<h3>Allgemeine Einstellungen</h3>
	</div>
	<div class="page-body">
		<form action="#" method="POST">
			<div class="row ui-lotdata-block ui-lotdata-block-next">
				<h5 class="ui-lotdata-title">Allgemeine Einstellungen</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-md-2">
							<label for="">Angebotener Service</label>
							<select name="offered_service" class="form-item form-control">
								<option value="shuttle_valet" <?php echo $settings->offered_service == "shuttle_valet" ? "selected" : "" ?>>Shuttle/Valet</option>
								<option value="shuttle" <?php echo $settings->offered_service == "shuttle" ? "selected" : "" ?>>Shuttle</option>
								<option value="valet" <?php echo $settings->offered_service == "valet" ? "selected" : "" ?>>Valet</option>
							</select>
						</div>
						<div class="col-sm-12 col-md-1">
							<label for="">Menüfarbe</label><br>
							<input type="color" name="menu_color" value="<?php echo $settings->menu_color != null ? $settings->menu_color : "#0080c0" ?>">
						</div>
						<div class="col-sm-12 col-md-1">
							<label for="">Untermenüfarbe</label><br>
							<input type="color" name="submenu_color" value="<?php echo $settings->submenu_color != null ? $settings->submenu_color : "#0d3960" ?>">
						</div>
					</div>
				</div>
			</div>
			<div class="row m10">
				<div class="col-1">
					<button class="btn btn-primary">Speichern</button>
				</div>
			</div>
		</form>
	</div>
</div>
