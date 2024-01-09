<?php
/**
 *
 * validate dates
 *
 */
if(strtotime($_GET['datefrom']) < strtotime(date('Y-m-d'))
    || strtotime($_GET['dateto']) < strtotime(date('Y-m-d'))
    || strtotime($_GET['datefrom']) > strtotime($_GET['dateto'])){
    echo '<script>window.location.href = "shop";</script>';
}
global $product;
$disabledDate = date('Y-m-d');
$services = Database::getInstance()->getProductAdditionalServices($product->get_id());
?>

<div class="custom-fields">
    <input type="hidden" class="pro-id" value="<?php echo $product->get_id() ?>">
    <div class="custom-field-row">
        <label style="display: block">
            <strong>Time from*</strong>
        </label>
        <input type="text" class="product-page-timefrom" name="timefrom">
    </div>
    <div class="custom-field-row">
        <label style="display: block">
            <strong>Time to*</strong>
        </label>
        <input type="text" class="product-page-timeto" name="timeto">
    </div>
    <?php if (count($services) > 0) : ?>
        <br>
        <hr>
        <br>
        <div class="custom-field-row">
            <p>
                <b>Additional Services</b>
            </p>
            <?php foreach ($services as $service) : ?>
                <label for="addSer-<?php echo $service->id ?>" style="display: block">
                    <input type="checkbox" data-id="<?php echo $service->id ?>"
                           data-price="<?php echo $service->price ?>" class="front-additional-service"
                           id="addSer-<?php echo $service->id ?>">
                    <?php echo $service->name ?>
                </label>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="custom-field-row">
        <div class="alert alert-danger product-page-errors" role="alert" style="display: none;"></div>
    </div>
</div>
<div class="itweb-loader"
     style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;z-index:999999;background:rgba(0,0,0,0.8)">
    <span class="fas fa-spinner fa-spin"
    style="position:absolute;color:white;font-size:40px;left:50%;top:50%;transform:translate(-50%,-50%)"></span>
</div>