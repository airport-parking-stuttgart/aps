<?php
$product_groups = Database::getInstance()->getProductGroups();
$child_product_groups = Database::getInstance()->getProductGroups();

if (isok($_POST, 'group_name')) {
	if (isset($_POST['group_name']) && $_POST['group_name'] != "") {
		for ($i = 0; $i < count($_POST['group_name']); $i++) {
			if (empty($_POST['group_name'][$i])) {
				continue;
			}
			if (isset($_POST['group_id'][$i]) && !empty($_POST['group_id'][$i])) {
				Database::getInstance()->updateProductGroups($_POST['group_id'][$i], $_POST['group_name'][$i]);
			} else {				
				Database::getInstance()->saveProductGroups($_POST['group_name'][$i]);
			}
		}		
	}	
}

if (isok($_POST, 'child_group_name')) {
	if (isset($_POST['child_group_name']) && $_POST['child_group_name'] != "") {
		for ($i = 0; $i < count($_POST['child_group_name']); $i++) {
			if (empty($_POST['child_group_name'][$i])) {
				continue;
			}
			if (isset($_POST['child_group_id'][$i]) && !empty($_POST['child_group_id'][$i])) {
				echo $_POST['child_group_name'][$i] . "br>";
				Database::getInstance()->updateChildProductGroups($_POST['child_group_id'][$i], $_POST['child_group_perent_id'][$i], $_POST['child_group_name'][$i]);
			} else {				
				Database::getInstance()->saveChildProductGroups($_POST['child_group_perent_id'][$i], $_POST['child_group_name'][$i]);
			}
		}
	}
}
if(isset($_POST['save']))
	header('Location: ' . $_SERVER['HTTP_REFERER']);

//echo "<pre>"; print_r($_POST); echo "</pre>";
?>
<div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3>Produktgruppen</h3>
    </div>
    <div class="page-body">
		<form action="#" method="POST">
			<div class="row ui-lotdata-block ui-lotdata-block-next product_groups-wrapper">
				<h5 class="ui-lotdata-title">Produktgruppen bearbeiten</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
				<?php foreach ($product_groups as $group) : ?>
					<div class="col-12 row-item group-item">
						<div class="row">
							<div class="col-sm-12 col-md-3">
								<input type="hidden" name="group_id[]"
									   value="<?php echo $group->id ?>">
								<label for="">Bezeichnung Hauptgruppe</label>
								<input type="text" name="group_name[]" placeholder="" class="w100"								   
									   value="<?php echo $group->name ?>">
							</div>						
							<div class="col-2 add_del_buttons">
								<span class="btn btn-danger del-table-row"
									  data-table="product_groups" data-id="<?php echo $group->id ?>">x</span>
								<span class="btn btn-secondary plus-icon add-group-template">+</span>
							</div>
						</div>
					</div>						
				
				<?php endforeach; ?>
					<div class="col-12 row-item group-item">
						<div class="row">
							<div class="col-sm-12 col-md-3">
								<input type="hidden" name="group_id[]">
								<label for="">Bezeichnung Hauptgruppe</label>
								<input type="text" name="group_name[]" value="" placeholder="" class="w100">
							</div>
							<div class="col-2 add_del_buttons">
								<span class="btn btn-danger del-table-row"
									  data-table="product_groups">x</span>
								<span class="btn btn-secondary plus-icon add-group-template">+</span>
							</div>
						</div>
					</div>
				</div>
				<?php if(count($product_groups) > 0): ?>
				<div class="col-12 product_child_groups-wrapper">
					<?php $child_product_groups = Database::getInstance()->getChildProductGroups(); ?>							
					<?php foreach ($child_product_groups as $child_group) : ?>								
						<div class="col-12 row-item child-group-item">
							<div class="row">
								<div class="col-12 col-sm-1">
									<label for="">Hauptgruppe</label>
									<select name="child_group_perent_id[]" class="form-control">
									<?php foreach ($product_groups as $group) : ?>
									<option value="<?php echo $group->id ?>" <?php echo $group->id == $child_group->perent_id ? "selected" : "" ?>><?php echo $group->name ?></option>
									<?php endforeach; ?>
									</select>
								</div>
								<div class="col-sm-12 col-md-3">
									<input type="hidden" name="child_group_id[]"
										   value="<?php echo $child_group->id ?>">											
									<label for="">Bezeichnung Untergruppe</label>
									<input type="text" name="child_group_name[]" placeholder="" class="w100"								   
										   value="<?php echo $child_group->name ?>">
								</div>						
								<div class="col-2 add_del_buttons">
									<span class="btn btn-danger del-table-row"
										  data-table="product_groups" data-id="<?php echo $child_group->id ?>">x</span>
									<span class="btn btn-secondary plus-icon add-child-group-template">+</span>
								</div>
							</div>
						</div>
					<?php endforeach; ?>					
					<div class="col-12 row-item child-group-item">
						<div class="row">
							<div class="col-12 col-sm-1">
								<label for="">Hauptgruppe</label>
								<select name="child_group_perent_id[]" class="form-control">
									<?php foreach ($product_groups as $group) : ?>
									<option value="<?php echo $group->id ?>"><?php echo $group->name ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-sm-12 col-md-3">
								<input type="hidden" name="child_group_id[]">
								<label for="">Bezeichnung Untergruppe</label>
								<input type="text" name="child_group_name[]" placeholder="" class="w100">
							</div>
							<div class="col-2 add_del_buttons">
								<span class="btn btn-danger del-table-row"
									  data-table="product_groups">x</span>
								<span class="btn btn-secondary plus-icon add-child-group-template">+</span>
							</div>
						</div>
					</div>
					<?php endif; ?>
				</div>
			</div>
			<div class="row m10">
				<div class="col-12">
					<button class="btn btn-primary" name="save">
						Speichern
					</button>
				</div>
			</div>
			</div>	
		</form>
    </div>
</div>