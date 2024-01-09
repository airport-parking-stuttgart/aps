<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-logo">
		<img class="adm-logo" src="<?php echo home_url(); ?>/wp-content/uploads/2021/05/logo-e1596314559277.png" alt="" width="300" height="200">
	</div>
	
<?php
if (!isset($_GET['edit'])) :
    $brokers = Database::getInstance()->getBrokers();
    ?>

        <div class="page-title itweb_adminpage_head">
            <h3>Vermittler Bearbeiten</h3>
        </div>
        <div class="page-body">
            <table class="table table-sm">
                <thead>
                <tr>
                    <th>Tittel</th>
                    <th>Name</th>
                    <th>Firma</th>
                    <th>Strasse / Nr</th>
                    <th>PLZ</th>
                    <th>Ort</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($brokers as $broker): ?>
                    <tr class="row-item">
                        <td><?php echo $broker->title ?></td>
                        <td><?php echo $broker->firstname ." "?><?php echo $broker->lastname ?></td>
                        <td><?php echo $broker->company ?></td>
                        <td><?php echo $broker->street ?></td>
                        <td><?php echo $broker->zip ?></td>
                        <td><?php echo $broker->location ?></td>
                        <!--<td style="width: 130px;text-align: right;">
                            <a href="/wp-admin/admin.php?page=vermittler-bearbeiten&edit=<?php echo $broker->id ?>"
                               class="btn btn-secondary btn-sm">
                                Edit
                            </a>
                            <a href="#" class="btn btn-danger btn-sm del-table-row" data-id="<?php echo $broker->id ?>"
                               data-table="brokers">
                                Delete
                            </a>
                        </td>-->
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (count($brokers) <= 0) : ?>
                <p>No results found!</p>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <?php
    require_once plugin_dir_path(__FILE__) . "broker-edit-template.php";
    ?>
<?php endif; ?>