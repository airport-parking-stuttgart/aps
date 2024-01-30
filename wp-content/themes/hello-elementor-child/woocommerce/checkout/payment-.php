<?php
/**
 * Checkout Payment Section
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/payment.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.5.3
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_ajax() ) {
	do_action( 'woocommerce_review_order_before_payment' );
}
?>

<div id="payment" class="woocommerce-checkout-payment">
	<?php if ( WC()->cart->needs_payment() ) : ?>
		<h3>Wie möchten Sie bezahlen?</h3>
		<ul class="wc_payment_methods payment_methods methods">
			<select class="payment form-control" style="width: 250px;" onchange="toggle(this.value)">			
				<option value="payment">Zahlungsmethode wählen</option>
				<?php
				foreach ($available_gateways as $gateway) {?>
				<option name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" data-order_button_text="<?php echo esc_attr( $gateway->order_button_text ); ?>"><?php echo $gateway->get_title(); ?></option>			
			<?php } ?>
			</select><br>
			<?php
			if ( ! empty( $available_gateways ) ) {
				foreach ( $available_gateways as $gateway ) {
					wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
				}
			} else {
				echo '<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . apply_filters( 'woocommerce_no_available_payment_methods_message', WC()->customer->get_billing_country() ? esc_html__( 'Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) : esc_html__( 'Please fill in your details above to see available payment methods.', 'woocommerce' ) ) . '</li>'; // @codingStandardsIgnoreLine
			}
			?>
		</ul>	
	
<script>
    function toggle(value) {
		//var bs_payone_prepayment = document.getElementById("bs_payone_prepayment");
		//var bs_payone_paypal = document.getElementById("bs_payone_paypal");
		//var bs_payone_creditcard = document.getElementById("bs_payone_creditcard");
		//var bs_payone_sofort = document.getElementById("bs_payone_sofort");
		var place_order = document.getElementById("place_order");
		//var stripe = document.getElementById("stripe");
		var paypal = document.getElementById("paypal");
		var paypal_plus = document.getElementById("paypal_plus");
		//var bacs = document.getElementById("bacs");
		
		if (document.getElementById(value).style.display == 'none') {
            //document.getElementById(value).style.display = 'block';      
		}
		/*if(value == "bs_payone_prepayment"){
			bs_payone_prepayment.style.display = "block";
			//bs_payone_paypal.style.display = "none";
			bs_payone_creditcard.style.display = "none";
			bs_payone_sofort.style.display = "none";
			stripe.style.display = "none";
			paypal.style.display = "none";
			paypal_plus.style.display = "none";
			bacs.style.display = "none";
			document.getElementById("bs_payone_prepayment").click();
			document.getElementById("bs_payone_prepayment").checked = true;
			place_order.disabled = false;
		}*/
		/*else if(value == "bs_payone_paypal"){
			bs_payone_paypal.style.display = "block";
			bs_payone_prepayment.style.display = "none";				
			bs_payone_creditcard.style.display = "none";
			bs_payone_sofort.style.display = "none";
			stripe.style.display = "none";
			paypal.style.display = "none";
			paypal_plus.style.display = "none";
			bacs.style.display = "none";
			document.getElementById("bs_payone_paypal").click();
			document.getElementById("bs_payone_paypal").checked = true;
			place_order.disabled = false;
		}*/
		/*else if(value == "bs_payone_creditcard"){				
			bs_payone_creditcard.style.display = "block";
			//bs_payone_prepayment.style.display = "none";				
			//bs_payone_paypal.style.display = "none";
			bs_payone_sofort.style.display = "none";
			stripe.style.display = "none";
			paypal.style.display = "none";
			paypal_plus.style.display = "none";
			bacs.style.display = "none";
			document.getElementById("bs_payone_creditcard").click();
			document.getElementById("bs_payone_creditcard").checked = true;
			place_order.disabled = false;
		}*/
		/*else if(value == "bs_payone_sofort"){				
			//bs_payone_creditcard.style.display = "none";
			//bs_payone_prepayment.style.display = "none";				
			//bs_payone_paypal.style.display = "none";
			bs_payone_sofort.style.display = "block";
			stripe.style.display = "none";
			paypal.style.display = "none";
			paypal_plus.style.display = "none";
			bacs.style.display = "none";
			document.getElementById("bs_payone_sofort").click();
			document.getElementById("bs_payone_sofort").checked = true;
			place_order.disabled = false;
		}*/
		/*if(value == "stripe"){				
			//bs_payone_creditcard.style.display = "none";
			//bs_payone_prepayment.style.display = "none";				
			//bs_payone_paypal.style.display = "none";
			//bs_payone_sofort.style.display = "none";
			stripe.style.display = "block";
			paypal.style.display = "none";
			paypal_plus.style.display = "none";
			bacs.style.display = "none";
			document.getElementById("payment_method_stripe").click();
			document.getElementById("payment_method_stripe").checked = true;
			place_order.disabled = false;
		}*/
		if(value == "paypal"){				
			//bs_payone_creditcard.style.display = "none";
			//bs_payone_prepayment.style.display = "none";				
			//bs_payone_paypal.style.display = "none";
			//bs_payone_sofort.style.display = "none";
			//stripe.style.display = "none";
			paypal.style.display = "block";
			paypal_plus.style.display = "none";
			//bacs.style.display = "none";
			document.getElementById("payment_method_paypal").click();
			document.getElementById("payment_method_paypal").checked = true;
			place_order.disabled = false;
		}
		else if(value == "paypal_plus"){				
			//bs_payone_creditcard.style.display = "none";
			//bs_payone_prepayment.style.display = "none";				
			//bs_payone_paypal.style.display = "none";
			//bs_payone_sofort.style.display = "none";
			//stripe.style.display = "none";
			paypal_plus.style.display = "block";
			paypal.style.display = "none";
			//bacs.style.display = "none";
			document.getElementById("payment_method_paypal_plus").click();
			document.getElementById("payment_method_paypal_plus").checked = true;
			place_order.disabled = false;
		}
		/*else if(value == "bacs"){
			//bs_payone_creditcard.style.display = "none";
			//bs_payone_prepayment.style.display = "none";				
			//bs_payone_paypal.style.display = "none";
			//bs_payone_sofort.style.display = "none";
			stripe.style.display = "none";
			paypal.style.display = "none";
			paypal_plus.style.display = "none";
			bacs.style.display = "block";
			document.getElementById("payment_method_bacs").click();
			document.getElementById("payment_method_bacs").checked = true;
			place_order.disabled = false;
		}*/
		else if(value == "payment"){
			//bs_payone_prepayment.style.display = "none";
			//bs_payone_paypal.style.display = "none";
			//bs_payone_creditcard.style.display = "none";
			//bs_payone_sofort.style.display = "none";
			//stripe.style.display = "none";
			paypal.style.display = "none";
			paypal_plus.style.display = "none";
			//bacs.style.display = "none";
			place_order.disabled = false; 
		}
    }
</script>	

	
	
	<?php endif; ?>
	<div class="form-row place-order">
		<noscript>
			<?php
			/* translators: $1 and $2 opening and closing emphasis tags respectively */
			printf( esc_html__( 'Since your browser does not support JavaScript, or it is disabled, please ensure you click the %1$sUpdate Totals%2$s button before placing your order. You may be charged more than the amount stated above if you fail to do so.', 'woocommerce' ), '<em>', '</em>' );
			?>
			<br/><button type="submit" class="button alt" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'Update totals', 'woocommerce' ); ?>"><?php esc_html_e( 'Update totals', 'woocommerce' ); ?></button>
		</noscript>

		<?php wc_get_template( 'checkout/terms.php' ); ?>

		<?php do_action( 'woocommerce_review_order_before_submit' ); ?>

		<?php echo apply_filters( 'woocommerce_order_button_html', '<button disabled type="submit" class="button alt" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>' ); // @codingStandardsIgnoreLine ?>

		<?php do_action( 'woocommerce_review_order_after_submit' ); ?>

		<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
	</div>
</div>
<?php
if ( ! is_ajax() ) {
	do_action( 'woocommerce_review_order_after_payment' );
}
