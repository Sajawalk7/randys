<?php
/**
 * Review order table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table class="shop_table woocommerce-checkout-review-order-table">
	<thead>
		<tr>
      <th>
        <div class="row">
          <div class="col-xs-5 col-sm-6 col-lg-7 product-name"><?php _e( 'Product', 'woocommerce' ); ?></div>
          <div class="col-xs-3 col-sm-2 product-name"><?php _e( 'Quantity', 'woocommerce' ); ?></div>
          <div class="col-xs-4 col-lg-3 product-total"><?php _e( 'Total', 'woocommerce' ); ?></div>
        </div>
      </th>
      <th class="product-shipping"><?php _e( 'Shipping (Select option for each warehouse)', 'woocommerce' ); ?></th>
		</tr>
	</thead>
	<tbody>
    <?php
    do_action( 'woocommerce_review_order_before_cart_contents' );

    $packages = WC()->shipping->get_packages();
    foreach ($packages as $package_index => $package) { ?>
      <tr class="cart_item">
        <td>
          <?php foreach($package['contents'] as $cart_item_key => $cart_item) { ?>
            <div class="row">
              <div class="col-xs-5 col-sm-6 col-lg-7 product-name">
                <?php $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                ?>
                <div class="item">
                    <?php echo get_post_meta($_product->get_id(), '_sku', true); ?><br>
                    <?php echo apply_filters( 'woocommerce_cart_item_name', $_product->get_title(), $cart_item, $cart_item_key ) . '&nbsp;'; ?>
                </div>
              </div>
              <div class="col-xs-3 col-sm-2 product-quantity">
                <div class="item"><?php echo apply_filters( 'woocommerce_checkout_cart_item_quantity', ' <span class="product-quantity total">' . sprintf( '&times; %s', $cart_item['quantity'] ) . '</span>', $cart_item, $cart_item_key ); ?></div>
              </div>
              <div class="col-xs-4 col-lg-3 product-total">
                <?php $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                ?>
                <div class="item"><?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?></div>
              </div>
            </div>
          <?php } // foreach item ?>
        </td>
        <td class="product-shipping">
          <?php if (is_array($package) && array_key_exists('shipment_origin_warehouse', $package) && $package['shipment_origin_warehouse'] == 'OOS') { // out of stock ?>
            <div><span>Out of Stock!</span></div>
            <div style="display:none;">
            <?php do_action( 'shipping_options_for_package', $checkout, $package_index, $package ); ?>
            </div>
          <?php } elseif (is_array($package) && array_key_exists('shipment_origin_warehouse', $package) && $package['shipment_origin_warehouse'] == 'NOD') { ?>
            <div><span>Please fill out billing/shipping info.</span></div>
          <?php } elseif (is_array($package) && array_key_exists('shipment_origin_warehouse', $package)) { ?>
            <div><span>Warehouse: <?= $package['shipment_origin_warehouse']; ?></span></div>
            <?php
            do_action( 'shipping_options_for_package', $checkout, $package_index, $package );
          } else { ?>
            <div><span>Please fill out billing/shipping info.</span></div>
          <?php } ?>
        </td>
      </tr>
    <?php
    } // foreach shipment

    do_action( 'woocommerce_review_order_after_cart_contents' );
    ?>
	</tbody>
	<tfoot>

		<tr class="cart-subtotal">
			<th><?php _e( 'Subtotal', 'woocommerce' ); ?></th>
			<td><?php wc_cart_totals_subtotal_html(); ?></td>
		</tr>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : 
    $user_code=explode("-", $code);
    if($user_code[0]=="discount"){
      array_shift($user_code);
      $ccode=implode("-", $user_code);
    }else{
      $ccode=$code;
    }
    ?>
			<tr class="cart-discount tax-rate coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<th><?php wc_cart_totals_coupon_label( $ccode ); ?></th>
				<td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<tr class="fee tax-rate">
				<th><?php echo esc_html( $fee->name ); ?></th>
				<td><?php wc_cart_totals_fee_html( $fee ); ?></td>
			</tr>
		<?php endforeach; ?>


		<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
			<tr class="cart-shiping">
				<th><?php _e( 'Shipping & Handling', 'woocommerce' ); ?></th>
				<td><?php echo wc_price(WC()->cart->shipping_total); ?></td>
			</tr>
		<?php endif; ?>

		<?php if ( wc_tax_enabled() && 'excl' === WC()->cart->tax_display_cart ) : ?>
			<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
				<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
					<tr class="tax-rate tax-rate-<?php echo sanitize_title( $code ); ?>">
						<th><?php echo esc_html( $tax->label ); ?></th>
						<td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr class="tax-total">
					<th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
					<td><?php wc_cart_totals_taxes_total_html(); ?></td>
				</tr>
			<?php endif; ?>
		<?php endif; ?>

		<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

		<tr class="order-total">
			<th><?php _e( 'Total', 'woocommerce' ); ?></th>
			<td><?php echo wc_cart_totals_order_total_html(); ?></td>
		</tr>

		<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>

	</tfoot>
</table>
    </div>
  </div>
</div>
