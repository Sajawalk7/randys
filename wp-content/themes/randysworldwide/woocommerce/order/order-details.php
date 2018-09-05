<?php
/**
 * Order details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details.php.
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

use Roots\Sage\RANDYS;

$order = wc_get_order( $order_id );

// if COD, call woocommerce_payment_complete here to send to web_orders
$payment_gateway = get_post_meta( $order_id, '_payment_method', true );
if ( 'cod' === $payment_gateway ) {
  do_action('woocommerce_payment_complete', $order_id);
}

$show_purchase_note    = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
$show_customer_details = is_user_logged_in() && $order->get_user_id() === get_current_user_id();
?>
<h3><?php _e( 'Your Order', 'woocommerce' ); ?></h3>
<table class="shop_table order_details">
	<thead>
		<tr>
			<th class="product-name"><?php _e( 'Product', 'woocommerce' ); ?></th>
      <th class="product-quantity"><?php _e( 'Quantity', 'woocommerce' ); ?></th>
      <?php if ( RANDYS\is_wholesale() ) { ?>
        <th class="product-total list-price"><?php _e( 'List Price', 'woocommerce' ); ?></th>
        <th class="product-total your-price"><?php _e( 'Your Price', 'woocommerce' ); ?></th>
      <?php } else { ?>
        <th></th>
        <th class="product-total your-price"><?php _e( 'Price', 'woocommerce' ); ?></th>
      <?php } ?>
			<th class="product-total extended-price"><?php _e( 'Extended Price', 'woocommerce' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
			$order_items = $order->get_items();

			// Loop through items and see if an item comes from multiple warehouses, if so, separate out
			foreach ($order_items as $index => $item) {
				if ( 1 < count($item['item_meta']['origin_warehouse_id']) ) {
					// if there are more than 1 warehouse, unset the current item
					unset($order_items[$index]);
					// loop through warehouses
					foreach ($item['item_meta']['origin_warehouse_id'] as $warehouse) {
						$new_item = $item;
						$new_item['item_meta']['origin_warehouse_id'] = array($warehouse);
						$new_item['qty'] = $new_item['quantity_from_warehouse_'.$warehouse];
						array_push($order_items, $new_item);
					}
				}
			}

			function sort_by_warehouse($a, $b) {
				if ( isset($a['item_meta']['origin_warehouse_id'][0]) ) {
					return strcasecmp($b['item_meta']['origin_warehouse_id'][0], $a['item_meta']['origin_warehouse_id'][0]);
				} else {
					return;
				}
			}
			uasort($order_items, 'sort_by_warehouse');

			// set state so we can see if it changes
			$state = '';

			foreach( $order_items as $item_id => $item ) {
				$product = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );

				// reset this var every time the loop starts so we can set it if the below conditional is true
				$line = '';
				if ( isset($item['item_meta']['origin_warehouse_id'][0]) && $state !== $item['item_meta']['origin_warehouse_id'][0] ) {
					$state = $item['item_meta']['origin_warehouse_id'][0];
					$line = 'border-top';
				}

				wc_get_template( 'order/order-details-item.php', array(
					'order'			     => $order,
					'item_id'		     => $item_id,
					'item'			     => $item,
					'show_purchase_note' => $show_purchase_note,
					'purchase_note'	     => $product ? get_post_meta( $product->id, '_purchase_note', true ) : '',
					'product'	         => $product,
					'border_top'      => $line,
				) );
			}
		?>
		<?php do_action( 'woocommerce_order_items_table', $order ); ?>
	</tbody>
	<tfoot>
		<?php
			foreach ( $order->get_order_item_totals() as $key => $total ) {
				?>
				<tr>
					<th scope="row"><?php echo $total['label']; ?></th>
					<td></td>
					<td></td>
					<td></td>
					<td><?php echo $total['value']; ?></td>
				</tr>
				<?php
			}
		?>
	</tfoot>
</table>

<h4 class="m-t-3">Verify all parts &amp; ratios are correct prior to installation.</h4>
<p><span class="underline">Call for a Return Authorization Number.</span> Only Authorized Returns Will Be Accepted!<p>
<p>All returns must be sent to:</p>
<p>RANDYS Worldwide<br> 10411 Airport Road<br> Everett, WA 98204</p>

<?php do_action( 'woocommerce_order_details_after_order_table', $order );

