<?php
if (isset($_POST['edit-name'])) {
    Database::getInstance()->updateAdditionalService($_GET['edit'], $_POST['edit-name'], $_POST['edit-price']);
}
$service = Database::getInstance()->getAdditionalService($_GET['edit']);
?>
    <div class="page container-fluid <?php echo $_GET['page'] ?>">
    <div class="page-title itweb_adminpage_head">
        <h3><?php echo $service->name ?></h3>
    </div>
    <div class="page-body">
        <form action="#" method="POST">
            <div class="row">
                <div class="col-12 col-sm-6 col-md-6">
                    <label for="">Name</label>
                    <input type="text" class="form-control" name="edit-name" value="<?php echo $service->name ?>" required>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <label for="">Price</label>
                    <input type="number" min="1" class="form-control" name="edit-price" value="<?php echo $service->price ?>" required>
                </div>
                <div class="col-sm-12 col-md-3">
                    <label for="" class="d-block">&nbsp;</label>
                    <button class="btn btn-primary" type="SUBMIT">UPDATE</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php
