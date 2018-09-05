<?php
/**
 * Cart totals
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-totals.php.
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
 * @version     2.3.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="cart_totals <?php if ( WC()->customer->has_calculated_shipping() ) echo 'calculated_shipping'; ?>">

	<?php do_action( 'woocommerce_before_cart_totals' ); ?>

  <table cellspacing="0" class="shop_table shop_table_responsive m-b-1">
    <tr class="cart-subtotal">
			<th><?php _e( 'Items', 'woocommerce' ); ?></th>
			<th width="120px"><span class="float-right"><?php echo WC()->cart->get_cart_contents_count(); ?></span></th>
		</tr>
  </table>

  <?php if ( wc_coupons_enabled() ) { ?>
    <form action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
      <div class="cart-coupons row">
        <div class="col-xs-12">
          <label for="coupon_code"><?php _e( 'Discount Code', 'woocommerce' ); ?></label>
        </div>
        <div class="col-xs-8">
          <p class="custom-coupon">
          	<!-- <span>DISCOUNT-</span> -->
          	<input type="text" name="view_coupon_code" class="input-text" id="view_coupon_code" value="" placeholder="<?php esc_attr_e( 'Promo Code Here', 'woocommerce' ); ?>">
          	<input type="hidden" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Promo Code Here', 'woocommerce' ); ?>">
        </div>
        <div class="col-xs-4">
          <input type="submit" class="button button--slim button--short float-right" name="apply_coupon" value="<?php esc_attr_e( 'Apply', 'woocommerce' ); ?>">
          <?php do_action( 'woocommerce_cart_coupon' ); ?>
        </div>
      </div>
    </form>
    <script type="text/javascript">
    	jQuery(function($){
    	  // Added For Discount Prefix Cupon
		  $("#view_coupon_code").on("keyup",function(){
		    var cuponCode=$(this).val();
		    $("#coupon_code").val("discount-"+cuponCode);
		  });
    	});
    </script>
  <?php } ?>

	<table cellspacing="0" class="shop_table shop_table_responsive border-top m-t-2">

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : 
		$user_code=explode("-", $code);
		if($user_code[0]=="discount"){
			array_shift($user_code);
			$ccode=implode("-", $user_code);
		}else{
			$ccode=$code;
		}
		?>
			<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<th><?php wc_cart_totals_coupon_label( $ccode ); ?></th>
				<td data-title="<?php echo esc_attr( wc_cart_totals_coupon_label( $coupon, false ) ); ?>"><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
			</tr>
		<?php endforeach; ?>

		<tr class="cart-subtotal">
			<th><?php _e( '*Subtotal', 'woocommerce' ); ?></th>
			<th data-title="<?php esc_attr_e( 'Subtotal', 'woocommerce' ); ?>">
				<?php echo wc_price(WC()->cart->cart_contents_total); ?>
			</th>
		</tr>

	</table>
	<p class="sm">*Shipping costs and taxes will be calculated in the checkout process.</p>
	<a href="https://sealinfo.thawte.com/thawtesplash?form_file=fdf/thawtesplash.fdf&dn=WWW.RANDYSWORLDWIDE.COM&lang=en" target="_blank" class="d-block m-b-2">
		<img src="https://seal.thawte.com/getthawteseal?at=0&sealid=0&dn=WWW.RANDYSWORLDWIDE.COM&lang=en&gmtoff=420" alt="Thawte Security Badge"/>
	</a>

	<div class="wc-proceed-to-checkout">
		<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
	</div>

	<?php do_action( 'woocommerce_after_cart_totals' ); ?>
</div>
