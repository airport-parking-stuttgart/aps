<?php
if($_POST){
	$company = Database::getInstance()->getSiteCompany();
	if($company->id == null)
		Database::getInstance()->saveSiteCompany($_POST);
	else
		Database::getInstance()->updateSiteCompany($_POST);
}
$company = Database::getInstance()->getSiteCompany();
?>
<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-title itweb_adminpage_head">
		<h3>Informationen zum Webseitenbetreiber</h3>
	</div>
	<div class="page-body">
		<form action="#" method="POST">
			<div class="row ui-lotdata-block ui-lotdata-block-next api_code-wrapper">
				<h5 class="ui-lotdata-title">Informationen bearbeiten</h5>
				<div class="col-sm-12 col-md-12 ui-lotdata">
					<div class="row">
						<div class="col-sm-12 col-md-3">
							<input type="hidden" name="id" value="<?php echo $company->id ?>">
							<label for="">Firmenname</label>
							<input type="text" name="name" placeholder="" class="w100" value="<?php echo $company->name ?>">
						</div>
						<div class="col-sm-12 col-md-3">
							<label for="">Straße</label>
							<input type="text" name="street" placeholder="" class="w100" value="<?php echo $company->street ?>">
						</div>
						<div class="col-sm-12 col-md-1">
							<label for="">PLZ</label>
							<input type="text" name="zip" placeholder="" class="w100" value="<?php echo $company->zip ?>">
						</div>
						<div class="col-sm-12 col-md-3">
							<label for="">Ort</label>
							<input type="text" name="location" placeholder="" class="w100" value="<?php echo $company->location ?>">
						</div>
					</div>
					<br>
					<div class="row">
						<div class="col-sm-12 col-md-3">
							<label for="">Inhaber</label>
							<input type="text" name="owner" placeholder="" class="w100" value="<?php echo $company->owner ?>">
						</div>
						<div class="col-sm-12 col-md-3">
							<label for="">USt-IdNr.</label>
							<input type="text" name="ust_id" placeholder="" class="w100" value="<?php echo $company->ust_id ?>">
						</div>
						<div class="col-sm-12 col-md-3">
							<label for="">Steuer-Nr.</label>
							<input type="text" name="st_nr" placeholder="" class="w100" value="<?php echo $company->st_nr ?>">
						</div>
					</div>
					<br>
					<div class="row">
						<div class="col-sm-12 col-md-3">
							<label for="">Webseite</label>
							<input type="text" name="web" placeholder="" class="w100" value="<?php echo $company->web ?>">
						</div>
						<div class="col-sm-12 col-md-3">
							<label for="">E-Mail</label>
							<input type="text" name="email" placeholder="" class="w100" value="<?php echo $company->email ?>">
						</div>
						<div class="col-sm-12 col-md-2">
							<label for="">Telefon</label>
							<input type="text" name="phone" placeholder="" class="w100" value="<?php echo $company->phone ?>">
						</div>
					</div>
					<br>
					<div class="row">
						<div class="col-sm-12 col-md-2">
							<label for="">Bankname</label>
							<input type="text" name="bank" placeholder="" class="w100" value="<?php echo $company->bank ?>">
						</div>
						<div class="col-sm-12 col-md-3">
							<label for="">IBAN</label>
							<input type="text" name="iban" placeholder="" class="w100" value="<?php echo $company->iban ?>">
						</div>
						<div class="col-sm-12 col-md-2">
							<label for="">BIC/SWIFT</label>
							<input type="text" name="bic" placeholder="" class="w100" value="<?php echo $company->bic ?>">
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
