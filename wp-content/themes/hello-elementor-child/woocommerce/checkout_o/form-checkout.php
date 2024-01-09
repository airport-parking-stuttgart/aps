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


do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}


?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<style>
.summary {
	border: 1px solid #d6e9c6;
	border-radius: 4px;
	box-shadow: 0 1px 1px rgba(0,0,0,.05);
	height: 500px;
	font-family: 'Kanit', sans-serif, Arial, sans-serif;
	padding: 0px;
	color: #666666;
}
.summary p{
	font-size: 11px;
}
.summary-h h3{
	background-color: #3b8ae3;
	color: white;
	padding: 10px 0;
	margin-top: 0px;
	border-radius: 4px;
}
.summary-p-name, .summary-adress, .summary-attr, .summary-sum{
	padding: 0px 30px;
}
.summary-adress{
	border-bottom: 1px solid grey;
}
.summary-attr:last-child{
	border-bottom: 1px solid grey;
}
.summary-sum{
	border-top: 1px solid grey;
}
</style>
<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

	<?php if ( $checkout->get_checkout_fields() ) : ?>

		<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>


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
