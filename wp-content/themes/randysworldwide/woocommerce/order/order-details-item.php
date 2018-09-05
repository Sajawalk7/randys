<?php
/**
 * Order Item Details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details-item.php.
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
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Roots\Sage\RANDYS;

if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
	return;
}
?>
<tr class="<?php echo $border_top; ?> <?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'order_item', $item, $order ) ); ?>">
	<td class="product-name">
		<?php
			if ( $border_top ) {
				echo '<span class="warehouse">Origin Warehouse: '.$item['item_meta']['origin_warehouse_id'][0].'</span>';
			}

			$is_visible        = $product && $product->is_visible();
			$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );

			echo get_post_meta($product->get_id(), '_sku', true).'<br>';

			echo apply_filters( 'woocommerce_order_item_name', $product_permalink ? sprintf( '<a href="%s">%s</a>', $product_permalink, $item['name'] ) : $item['name'], $item, $is_visible );

			do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order );

			// This data is currently not to be shown on the invoice, but we'll just
			// comment it out so we could quickly turn it on if we need to later.
			// $order->display_item_meta( $item );
			// $order->display_item_downloads( $item );

			do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order );
		?>
	</td>
	<td class="product-quantity">
		<?php
			echo apply_filters( 'woocommerce_order_item_quantity_html', ' <span class="product-quantity total">' . sprintf( '&times; %s', $item['qty'] ) . '</span>', $item );
		?>
  </td>
  <?php if ( RANDYS\is_wholesale() ) { ?>
    <td class="product-total list-price">
      <?php echo wc_price(get_post_meta($item['product_id'], '_price_2')[0]); ?>
    </td>
  <?php } else { ?>
    <td></td>
  <?php } ?>
	<td class="product-total your-price">
		<?php echo wc_price($product->get_price()); ?>
	</td>
  <td class="product-total extended-price">
		<?php echo wc_price($product->get_price() * $item['qty']);  ?>
	</td>
</tr>
<?php if ( $show_purchase_note && $purchase_note ) : ?>
<tr class="product-purchase-note">
	<td colspan="3"><?php echo wpautop( do_shortcode( wp_kses_post( $purchase_note ) ) ); ?></td>
</tr>
<?php endif;
