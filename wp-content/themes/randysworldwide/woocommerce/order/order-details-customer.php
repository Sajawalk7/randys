<?php
/**
 * Order Customer Details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details-customer.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

		<div class="row addresses m-t-3 m-b-3">
			<div class="col-md-4">
				<header class="title">
					<h3><?php _e( 'Billing Address:', 'woocommerce' ); ?></h3>
				</header>
				<address>
					<?php echo ( $address = $order->get_formatted_billing_address() ) ? $address : __( 'N/A', 'woocommerce' ); ?>
				</address>
			</div>
			<div class="col-md-4">
				<header class="title">
					<h3><?php _e( 'Shipping Address:', 'woocommerce' ); ?></h3>
				</header>
				<address>
					<?php echo ( $address = $order->get_formatted_shipping_address() ) ? $address : __( 'N/A', 'woocommerce' ); ?>
				</address>
			</div>
			<div class="col-md-4">
				<header class="title">
					<h3><?php _e( 'Contact:', 'woocommerce' ); ?></h3>
				</header>
				<table class="shop_table customer_details">
					<p>
					<?php if ( $order->billing_email ) :
						echo esc_html( $order->billing_email ).'<br>';
				  endif; ?>

					<?php if ( $order->billing_phone ) :
							echo esc_html( $order->billing_phone );
					endif; ?>
					</p>
					<?php do_action( 'woocommerce_order_details_after_customer_details', $order ); ?>
				</table>
			</div>
		</div>
