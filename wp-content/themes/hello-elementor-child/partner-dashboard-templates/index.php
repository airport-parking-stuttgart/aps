<div class="btn-s mb-5 mt-5">
    <a href="/partner-dashboard" type="button" class="btn btn-primary">Neue Buchung</a>
	<?php if(empty($_GET['pID'])): ?>	
		<a href="/transferliste" type="button" class="btn btn-primary">Transferliste</a>
		<a href="/transfer-rechnungen" type="button" class="btn btn-primary">Rechnungen</a>
	<?php endif; ?>
	<a href="<?php echo wp_logout_url(get_permalink()) ?>" type="button" class="btn btn-primary">Abmelden</a>
<!--    <a type="button" class="btn btn-primary">Benutzerkonto ändern</a>-->
</div>
<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active" id="home-tab" data-toggle="tab" href="#Hin-und-Ruecktransfer" role="tab"
           aria-controls="home" aria-selected="true">Hin- und Rücktransfer</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="profile-tab" data-toggle="tab" href="#Nur-Hintransfer" role="tab"
           aria-controls="profile" aria-selected="false">Nur Hintransfer</a>

    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="contact-tab" data-toggle="tab" href="#Nur-Ruecktransfer" role="tab"
           aria-controls="contact" aria-selected="false">Nur Rücktransfer</a>
    </li>
</ul>
<div class="tab-content" id="myTabContent">



    <div class="tab-pane fade show active mt-3" id="Hin-und-Ruecktransfer" role="tabpanel"
         aria-labelledby="home-tab">
        <form method="GET">
			<?php if($roles[0] == 'administrator' && isset($_GET['pID'])): ?>
				<input type="hidden" name="pID" value="<?php echo $_GET['pID'] ?>">
			<?php endif; ?>
            <label for="exampleFormControlInput1" class="form-label">Transfer zum Flughafen</label>
            <input type="text" class="form-control mb-2 single-datepicker" id="exampleFormControlInput1"
                   placeholder="Datum auswählen" name="hintransfer" required>
            <label for="exampleFormControlInput1" class="form-label">Rücktransfer zum Hotel</label>
            <input type="text" class="form-control single-datepicker" id="exampleFormControlInput1"
                   placeholder="Datum auswählen" name="rucktransfer" required>

            <button type="submit" class="btn btn-primary mt-4">Weiter zur Buchung</button>
        </form>
    </div>


    <div class="tab-pane fade mt-3" id="Nur-Hintransfer" role="tabpanel" aria-labelledby="profile-tab">
        <form method="GET">
			<?php if($roles[0] == 'administrator' && isset($_GET['pID'])): ?>
				<input type="hidden" name="pID" value="<?php echo $_GET['pID'] ?>">
			<?php endif; ?>
            <label for="exampleFormControlInput1" class="form-label">Hintransfer</label>
            <input type="text" class="form-control mb-2 single-datepicker" id="exampleFormControlInput1"
                   placeholder="Datum auswählen" name="hintransfer" required>

            <button type="submit" class="btn btn-primary mt-4">Weiter zur Buchung</button>
        </form>
    </div>


    <div class="tab-pane fade mt-3" id="Nur-Ruecktransfer" role="tabpanel" aria-labelledby="contact-tab">
        <form method="GET">
			<?php if($roles[0] == 'administrator' && isset($_GET['pID'])): ?>
				<input type="hidden" name="pID" value="<?php echo $_GET['pID'] ?>">
			<?php endif; ?>
            <label for="exampleFormControlInput1" class="form-label">Rücktransfer</label>
            <input type="text" class="form-control mb-2 single-datepicker" id="exampleFormControlInput1"
                   placeholder="Datum auswählen" name="rucktransfer" required>
            <button type="submit" class="btn btn-primary mt-4">Weiter zur Buchung</button>
        </form>
    </div>

</div>