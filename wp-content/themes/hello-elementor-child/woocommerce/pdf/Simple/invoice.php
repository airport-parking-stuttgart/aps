<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php do_action( 'wpo_wcpdf_before_document', $this->get_type(), $this->order ); ?>

<table class="head container">
	<tr>
		<td class="header">
		<?php
		if ( $this->has_header_logo() ) {
			do_action( 'wpo_wcpdf_before_shop_logo', $this->get_type(), $this->order );
			$this->header_logo();
			do_action( 'wpo_wcpdf_after_shop_logo', $this->get_type(), $this->order );
		} else {
			$this->title();
		}
		?>
		</td>
		<td class="shop-info">
			<?php $web_company = Database::getInstance()->getSiteCompany(); ?>
			<?php do_action( 'wpo_wcpdf_before_shop_name', $this->get_type(), $this->order ); ?>
			<?php if($web_company->name):?>
				<div class="shop-name"><h3><?php echo $web_company->name; ?></h3></div>
			<?php else: ?>
				<div class="shop-name"><h3><?php $this->shop_name(); ?></h3></div>
			<?php endif; ?>
			<?php do_action( 'wpo_wcpdf_after_shop_name', $this->get_type(), $this->order ); ?>
			<?php do_action( 'wpo_wcpdf_before_shop_address', $this->get_type(), $this->order ); ?>
			<?php if($web_company->street && $web_company->zip && $web_company->location):?>
				<div class="shop-address"><?php echo $web_company->street . "<br>" . $web_company->zip . " " . $web_company->location; ?></div>
			<?php else: ?>
				<div class="shop-address"><?php $this->shop_address(); ?></div>
			<?php endif; ?>
			<?php do_action( 'wpo_wcpdf_after_shop_address', $this->get_type(), $this->order ); ?>
		</td>
	</tr>
</table>


<?php do_action( 'wpo_wcpdf_before_document_label', $this->get_type(), $this->order ); ?>

<h1 class="document-type-label">
	<?php if ( $this->has_header_logo() ) $this->title(); ?>
</h1>

<?php do_action( 'wpo_wcpdf_after_document_label', $this->get_type(), $this->order ); ?>

<table class="order-data-addresses">
	<tr>
		<td class="address billing-address">
			<!-- <h3><?php _e( 'Billing Address:', 'woocommerce-pdf-invoices-packing-slips' ); ?></h3> -->
			<?php do_action( 'wpo_wcpdf_before_billing_address', $this->get_type(), $this->order ); ?>
			<?php 
				if(get_post_meta($_GET['order_ids'], '_billing_company', true) != null) 
					echo get_post_meta($_GET['order_ids'], '_billing_company', true);
				else{
					echo get_post_meta($_GET['order_ids'], '_billing_first_name', true) . " " . get_post_meta($_GET['order_ids'], '_billing_last_name', true);
				}
				echo "<br>";
				echo get_post_meta($_GET['order_ids'], '_billing_address_1', true);
				echo "<br>";
				echo get_post_meta($_GET['order_ids'], '_billing_postcode', true) . " " . get_post_meta($_GET['order_ids'], '_billing_city', true);
				echo "<br>";
			?>
			<?php //$this->billing_address(); ?>
			<?php do_action( 'wpo_wcpdf_after_billing_address', $this->get_type(), $this->order ); ?>
			<?php if ( isset( $this->settings['display_email'] ) ) : ?>
				<div class="billing-email"><?php $this->billing_email(); ?></div>
			<?php endif; ?>
			<?php if ( isset( $this->settings['display_phone'] ) ) : ?>
				<div class="billing-phone"><?php $this->billing_phone(); ?></div>
			<?php endif; ?>
		</td>
		<td class="address shipping-address">
			<?php if ( $this->show_shipping_address() ) : ?>
				<h3><?php _e( 'Ship To:', 'woocommerce-pdf-invoices-packing-slips' ); ?></h3>
				<?php do_action( 'wpo_wcpdf_before_shipping_address', $this->get_type(), $this->order ); ?>
				<?php $this->shipping_address(); ?>
				<?php do_action( 'wpo_wcpdf_after_shipping_address', $this->get_type(), $this->order ); ?>
				<?php if ( isset( $this->settings['display_phone'] ) ) : ?>
					<div class="shipping-phone"><?php $this->shipping_phone(); ?></div>
				<?php endif; ?>
			<?php endif; ?>
		</td>
		<td class="order-data">
			<table>
				<?php do_action( 'wpo_wcpdf_before_order_data', $this->get_type(), $this->order ); ?>
				<?php if ( isset( $this->settings['display_number'] ) ) : ?>
					<tr class="invoice-number">
						<th><?php echo $this->get_number_title(); ?></th>
						<td><?php $this->invoice_number(); ?></td>
					</tr>
				<?php endif; ?>
				<?php if ( isset( $this->settings['display_date'] ) ) : ?>
					<tr class="invoice-date">
						<th><?php echo $this->get_date_title(); ?></th>
						<td><?php $this->invoice_date(); ?></td>
					</tr>
				<?php endif; ?>
				<?php $this->order_number() ?>
				<tr class="order-number">
					<th><?php _e( 'Buchungs-Nr:', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
					<td><?php echo get_post_meta($_GET['order_ids'], 'token', true) /*$this->order_number()*/; ?></td>
				</tr>
				<tr class="order-date">
					<th><?php _e( 'Buchungsdatum:', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
					<td><?php $this->order_date(); ?></td>
				</tr>
				<?php if ( $payment_method = $this->get_payment_method() ) : ?>
				<tr class="payment-method">
					<th><?php _e( 'Payment Method:', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
					<td><?php echo $payment_method; ?></td>
				</tr>
				<?php endif; ?>
				<?php do_action( 'wpo_wcpdf_after_order_data', $this->get_type(), $this->order ); ?>
			</table>			
		</td>
	</tr>
</table>

<?php do_action( 'wpo_wcpdf_before_order_details', $this->get_type(), $this->order ); ?>

<table class="order-details">
	<thead>
		<tr>
			<th class="product"><?php _e( 'Product', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th class="quantity"><?php _e( 'Parkdauer', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th class="price"><?php _e( 'Price', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $this->get_order_items() as $item_id => $item ) : ?>
			<tr class="<?php echo apply_filters( 'wpo_wcpdf_item_row_class', 'item-'.$item_id, esc_attr( $this->get_type() ), $this->order, $item_id ); ?>">
				<td class="product">
					<?php $description_label = __( 'Description', 'woocommerce-pdf-invoices-packing-slips' ); // registering alternate label translation ?>
					<span class="item-name"><?php echo $item['name']; ?></span>
					<?php do_action( 'wpo_wcpdf_before_item_meta', $this->get_type(), $item, $this->order  ); ?>
					<span class="item-meta"><?php echo $item['meta']; ?></span>
					<dl class="meta">
						<?php $description_label = __( 'SKU', 'woocommerce-pdf-invoices-packing-slips' ); // registering alternate label translation ?>
						<?php if ( ! empty( $item['sku'] ) ) : ?><dt class="sku"><?php _e( 'SKU:', 'woocommerce-pdf-invoices-packing-slips' ); ?></dt><dd class="sku"><?php echo esc_attr( $item['sku'] ); ?></dd><?php endif; ?>
						<?php if ( ! empty( $item['weight'] ) ) : ?><dt class="weight"><?php _e( 'Weight:', 'woocommerce-pdf-invoices-packing-slips' ); ?></dt><dd class="weight"><?php echo esc_attr( $item['weight'] ); ?><?php echo esc_attr( get_option( 'woocommerce_weight_unit' ) ); ?></dd><?php endif; ?>
					</dl>
					<?php do_action( 'wpo_wcpdf_after_item_meta', $this->get_type(), $item, $this->order  ); ?>
				</td>
				<!--<td class="quantity"><?php echo $item['quantity']; ?></td>-->				
				<td class="quantity">
				<?php 
					if(get_post_meta($_GET['order_ids'], 'Anreisedatum', true) != null && get_post_meta($_GET['order_ids'], 'Abreisedatum', true) != null)
						echo getDaysBetween2Dates(new DateTime(get_post_meta($_GET['order_ids'], 'Anreisedatum', true)), new DateTime(get_post_meta($_GET['order_ids'], 'Abreisedatum', true))) . " Tag(e)";
					else
						echo '-';
				?>
				</td>
				<td class="price"><?php echo $item['order_price']; ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
	<tfoot>
		<tr class="no-borders">
			<td class="no-borders">
				<div class="document-notes">
					<?php do_action( 'wpo_wcpdf_before_document_notes', $this->get_type(), $this->order ); ?>
					<?php if ( $this->get_document_notes() ) : ?>
						<h3><?php _e( 'Notes', 'woocommerce-pdf-invoices-packing-slips' ); ?></h3>
						<?php $this->document_notes(); ?>
					<?php endif; ?>
					<?php do_action( 'wpo_wcpdf_after_document_notes', $this->get_type(), $this->order ); ?>
				</div>
				<div class="customer-notes">
					<?php do_action( 'wpo_wcpdf_before_customer_notes', $this->get_type(), $this->order ); ?>
					<?php if ( $this->get_shipping_notes() ) : ?>
						<h3><?php _e( 'Customer Notes', 'woocommerce-pdf-invoices-packing-slips' ); ?></h3>
						<?php $this->shipping_notes(); ?>
					<?php endif; ?>
					<?php do_action( 'wpo_wcpdf_after_customer_notes', $this->get_type(), $this->order ); ?>
				</div>				
			</td>
			<td class="no-borders" colspan="2">
				<table class="totals">
					<tfoot>
						<?php foreach ( $this->get_woocommerce_totals() as $key => $total ) : ?>
							<?php if($total['label'] == 'Zwischensumme'):?>
								<?php $netto = number_format(get_post_meta($_GET['order_ids'], '_order_total', true) / 119 * 100, 2, ",", ".") ?>
								<?php $mwst = number_format(get_post_meta($_GET['order_ids'], '_order_total', true) / 119 * 19, 2, ",", "."); ?>							
								<tr class="<?php echo esc_attr( $key ); ?>">
									<th class="description"><?php echo $total['label']; ?></th>
									<td class="price"><span class="totals-price"><?php echo $netto . " €"; ?></span></td>
								</tr>
								<tr class="<?php echo esc_attr( $key ); ?>">
									<th class="description"><?php echo "+ 19% Ust"; ?></th>
									<td class="price"><span class="totals-price"><?php echo $mwst . " €"; ?></span></td>
								</tr>
							<?php else: ?>
							<tr class="<?php echo esc_attr( $key ); ?>">
								<th class="description"><?php echo $total['label']; ?></th>
								<td class="price"><span class="totals-price"><?php echo get_post_meta($_GET['order_ids'], '_order_total', true) . " €"; ?></span></td>
							</tr>
							<?php endif; ?>
						<?php endforeach; ?>
					</tfoot>
				</table>
			</td>
		</tr>
	</tfoot>
</table>

<div class="bottom-spacer"></div>

<?php do_action( 'wpo_wcpdf_after_order_details', $this->get_type(), $this->order ); ?>
<?php if($web_company): ?>
<style>
.footer{
	position: absolute;
    bottom: 0;
    width: 100%;
    padding: 10px;
}
.row .col-item {
        width: 25%;
        padding: 0 10px 0 0;
        float: left;
        box-sizing: border-box;
        margin: 0px 0;
		font-size: 9px !important;
    }
</style>
<div class="footer">
    <div class="row">
        <div class="col-item">
			<?php if($web_company->name): ?>
            <?php echo $web_company->name . "<br>" ?>
			<?php endif; ?>
			<?php if($web_company->street): ?>
            <?php echo $web_company->street . "<br>" ?>
			<?php endif; ?>
			<?php if($web_company->zip != null && $web_company->location != null): ?>
            <?php echo $web_company->zip . " " . $web_company->location . "<br>" ?>
			<?php endif; ?>
			<?php if($web_company->phone): ?>
            <?php echo "Telefon: " . $web_company->phone . "<br>" ?>
			<?php endif; ?>
        </div>
        <div class="col-item">
			<?php if($web_company->email): ?>
            <?php echo $web_company->email . "<br>" ?>
			<?php endif; ?>
            <?php echo "www." . $_SERVER['HTTP_HOST'] . "<br>" ?>
        </div>
		<?php if($web_company->bank != null && $web_company->iban != null && $web_company->bic != null): ?>
        <div class="col-item">
            <?php echo $web_company->bank . "<br>" ?>
            <?php echo "IBAN: " . $web_company->iban . "<br>" ?>
            <?php echo "BIC/SWIFT Code: " . $web_company->bic . "<br>" ?>
        </div>
		<?php endif; ?>
        <?php if($web_company->name): ?>
		<div class="col-item">
            <?php echo "Mit " . $web_company->name . "<br>" ?>
            <?php echo "günstig und sicher am Flughafen <br>" ?>
        </div>
		<?php endif; ?>
		<div class="clear"></div>		
    </div>
</div>
<?php endif; ?>
<?php if ( $this->get_footer() ) : ?>
	<!--<div id="footer">-->
		<!-- hook available: wpo_wcpdf_before_footer -->
		<?php //$this->footer(); ?>
		<!-- hook available: wpo_wcpdf_after_footer -->
	<!--</div>--><!-- #letter-footer -->
<?php endif; ?>

<?php do_action( 'wpo_wcpdf_after_document', $this->get_type(), $this->order ); ?>
