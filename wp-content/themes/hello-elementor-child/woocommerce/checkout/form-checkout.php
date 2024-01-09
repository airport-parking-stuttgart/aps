<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check booking
do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
    echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
    return;
}


if(array_values($_SESSION['parklots'])[0]['discount'] == "true"){
	$productSession = array_values($_SESSION['parklots'])[0];
}
else{
	$productSession = array_values($_SESSION['parklots'])[1];
}
	

$parklot = Database::getInstance()->getParklotByProductId($productSession['product_id']);
$dayDiff = getDaysBetween2Dates(new DateTime($productSession["datefrom"]), new DateTime($productSession["dateto"]));
$additionalServiceses = Database::getInstance()->getProductAdditionalServicesByProductId($productSession['product_id']);
foreach($additionalServiceses as $val){
	$services[$val->add_ser_id] = Database::getInstance()->getAdditionalService($val->add_ser_id);
}

//echo "<pre>"; print_r($_SESSION); echo "</pre>";	
?>


<!-- The Modal -->

<div id="modalServiceInfo" class="modalServiceInfo">
  <div class="modal-content">
	<div class="summary-h h3">
		<span class="close">&times;</span>
		<h2><?php echo $parklot->parklot;?></h2></div>
		<div class="modal-inner-content">
			<span>
			<?php 
			if(count($services) > 0){
				foreach($services as $s){
					echo "<h3>" . $s->name . " für " . number_format($s->price, 2, '.', '') . "€</h3>";
					$desc = nl2br($s->description);
					echo $desc;					
				}
			}
			else
				echo "Keine Serviceleistungen vorhanden.";
			
			?>
			</span>
		</div>
	</div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<script>
var modal = document.getElementById("modalServiceInfo");
var span = document.getElementsByClassName("close")[0];
function getServiceInfo(){
	modal.style.display = "block";
}
// When the user clicks on <span> (x), close the modal
span.onclick = function() {
	modal.style.display = "none";
}
// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
</script>
<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

    <?php if ( $checkout->get_checkout_fields() ) : ?>

        <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

        <div class="row" id="customer_details">
            <div class="col-md-8">
                <?php do_action( 'woocommerce_checkout_billing' ); ?>
                <?php do_action( 'woocommerce_checkout_shipping' ); ?>
            </div>
            <div class="col-md-4 summary">
                <div class="summary-h" >
                    <center><h3>Ihre Parkplatzbuchung</h3></center>
                </div>
                <div class="summary-p-name">
                    <h4><?php
                        echo $parklot->parklot;
                        ?>
                    </h4>
                </div>
                <div class="summary-adress">
                    <p>
                        <?php
                        echo $parklot->adress;
                        ?>
                    </p>
                </div><br>
                <div class="summary-attr">
                    <?php if($parklot->type): ?>
                        <p><i class="fa fa-product-hunt fa-lg"><?php echo " ". $parklot->type;?></i></p>
                    <?php endif; ?>
                </div>
                <div class="summary-attr">
                    <p><i class="fa fa-calendar-check-o fa-lg"><?php echo " Anreise: ". dateFormat($productSession["datefrom"], 'de');?></i></p>
                </div>
                <div class="summary-attr">
                    <p><i class="fa fa-calendar-check-o fa-lg"><?php echo " Abreise: ". dateFormat($productSession["dateto"], 'de');?></i></p>
                </div>
                <div class="summary-sum">
                    <h4><i class="fa fa-arrow-right fa-lg"><?php echo " Parkgebühren: "; wc_cart_totals_subtotal_html(); ?></i></h4>
                    <span><?php if($dayDiff >= 2) echo "(für " . $dayDiff . " Tage)"; else echo "(für einen Tag)"; ?></span><br>
                    <?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
                        <span><?php wc_cart_totals_coupon_label( $coupon ); ?></span>
                        <span><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
                    <?php endforeach; ?>
                    <?php if(WC()->cart->get_coupons()):?>
                        <script>
                            document.getElementsByClassName("woocommerce-remove-coupon")[0].onclick = function() {setTimeout(function () { location.reload(true); }, 1000);};
                        </script>
                    <?php endif; ?>
                </div>
				<?php if(count($services) > 0): ?>
					<div class="summary-attr"><h5>Weitere Informationen zu unseren Serivceleistungen finden Sie <span class="service_info" onclick="getServiceInfo()">hier</span>.</h5></div><br>
				<?php endif; ?>
            </div>
            <div class="col-md-8">

            </div>
        </div>
        <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

    <?php endif; ?>

    <?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

    <!--<h3 id="order_review_heading"><?php esc_html_e( 'Your order', 'woocommerce' ); ?></h3> -->

    <?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

    <!--<div id="order_review" class="woocommerce-checkout-review-order"> -->
    <?php do_action( 'woocommerce_checkout_order_review' ); ?>
    <!--</div>-->

    <?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
