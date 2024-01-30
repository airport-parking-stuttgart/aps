<?php

if (!isset($_GET['edit'])) :
    $brokers = Database::getInstance()->getBrokers();
?>
    <div class="page container-fluid <?php echo $_GET['page'] ?>">
        <div class="page-title itweb_adminpage_head">
            <h3>Vermittler API Konfiguration</h3>
        </div>
		<div class="page-body">
			<table class="table table-sm">
				<thead>
				<tr>
					<th>Firma</th>
					<th></th>
				</tr>
				</thead>
				<tbody>
					<tr class="row-item">
						<td>APG-Airport-Parking-Germany</td>						
						<td style="width: 130px;text-align: right;">
							<a href="/wp-admin/admin.php?page=api&edit=apg" class="btn btn-secondary btn-sm">Einstellen</a>
						</td>
					</tr>
					<tr class="row-item">
						<td>Holiday Extras GmbH</td>
						<td style="width: 130px;text-align: right;">
							<a href="/wp-admin/admin.php?page=api&edit=hex" class="btn btn-secondary btn-sm">Einstellen</a>
						</td>
					</tr>
					<tr class="row-item">	
						<td>Parkos</td>
						<td style="width: 130px;text-align: right;">
							<a href="/wp-admin/admin.php?page=api&edit=parkos" class="btn btn-secondary btn-sm">Einstellen</a>
						</td>
					</tr>
					<tr class="row-item">	
						<td>FluParks</td>
						<td style="width: 130px;text-align: right;">
							<a href="/wp-admin/admin.php?page=api&edit=fluparks" class="btn btn-secondary btn-sm">Einstellen</a>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
    </div>
<?php else: ?>
    <?php
    require_once plugin_dir_path(__FILE__) . "api-edit-template.php";
    ?>
<?php endif; ?>