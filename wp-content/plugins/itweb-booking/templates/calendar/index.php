<?php
    Database::getInstance()->deleteUnsavedEvents();
	$prices = Database::getInstance()->getPrices();
?>
<div class="row">
    <div class="col-2">
        <div id='external-events'>
            <h4>Preisschienen</h4>

            <div id='external-events-list'>
                <?php foreach ($prices as $price) : ?>
                    <div class='fc-event' data-id="<?php echo $price->id; ?>"><?php echo $price->name; ?></div>
                <?php endforeach; ?>
            </div>

            <!--<p>
                <input type='checkbox' id='drop-remove'/>
                <label for='drop-remove'>remove after drop</label>
            </p> -->
        </div>
    </div>
    <div class="col-10">
		<?php if(isset($_GET['edit'])): ?>
			<div class="row">
				<input type="hidden" name="product" class="product" value="<?php echo $_GET['edit'] ?>">
				<div class="col-2">
					<input type="text" class="datepicker-range form-item form-control date" name="date" data-multiple-dates-separator=" - " placeholder="Datum von - bis">
				</div>
				<div class="col-2">
					<select name="price" class="form-item form-control price">
						<option value="">Preisschiene</option>
						<?php foreach($prices as $price) : ?>
							<option value="<?php echo $price->id ?>">
								<?php echo $price->name ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="col-2">
					<a class="btn btn-primary d-block w-100 addPrices_calendarFast" type="submit">Zuweisen</a>
				</div>
				<div class="col-2">
					<!--<a class="btn btn-danger d-block w-100 delPrices_calendarFast" type="submit">Löschen von/bis</a>-->
				</div>
				<div class="col-2">
					<a href="#" class="price-delete-btn btn btn-danger" data-lotid="<?php echo $_GET['edit'] ?>">Alle Einträge Löschen</a>
				</div>
			</div><br><br>
		<?php endif; ?>
        <div id='calendar'></div>
    </div>
</div>