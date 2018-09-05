<?php
/**
 * Thankyou page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
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
 * @version     3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $order ) : ?>

	<?php if ( $order->has_status( 'failed' ) ) : ?>
		<div class="container">
			<p class="woocommerce-thankyou-order-failed"><?php _e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' ); ?></p>

			<p class="woocommerce-thankyou-order-failed-actions">
				<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php _e( 'Pay', 'woocommerce' ) ?></a>
				<?php if ( is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay"><?php _e( 'My Account', 'woocommerce' ); ?></a>
				<?php endif; ?>
			</p>
		</div>

	<?php else : ?>
		<div class="container">

      <?php wc_get_template( 'order/order-details-customer.php', array( 'order' => $order ) ); ?>

			</div>
			<div class="clear"></div>
		</div>

	<?php endif; ?>

	<div class="section order-review-section">
		<div class="container">
			<?php do_action( 'woocommerce_thankyou_' . $order->payment_method, $order->id ); ?>
			<?php do_action( 'woocommerce_thankyou', $order->id ); ?>
		</div>
	</div>

<?php else : ?>
	<div class="container">
		<p class="woocommerce-thankyou-order-received"><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', __( 'Thank you. Your order has been received.', 'woocommerce' ), null ); ?></p>
	</div>

<?php endif; ?>

<?php
	$estimated_ship_date = new DateTime("now", new DateTimeZone("America/Los_Angeles"));

	if($estimated_ship_date->format("D") == "Fri") {
		$estimated_ship_date->modify("+3 day");
	}
	elseif($estimated_ship_date->format("D") == "Sat") {
		$estimated_ship_date->modify("+2 day");
	}
	else {
		$estimated_ship_date->modify("+1 day");
	}

	$estimated_delivery_date = $estimated_ship_date->modify("+5 day");
	if($estimated_delivery_date->format("D") == "Sat") {
		$estimated_delivery_date->modify("+2 day");
	}
	elseif($estimated_delivery_date->format("D") == "Sun") {
		$estimated_delivery_date->modify("+1 day");
	}
?>

<!-- BEGIN GCR Opt-in Module Code -->
<script src="https://apis.google.com/js/platform.js?onload=renderOptIn"
  async defer>
</script>

<script>
  window.renderOptIn = function() {
    window.gapi.load('surveyoptin', function() {
      window.gapi.surveyoptin.render(
        {
          "merchant_id": 8883434,
          "order_id": "<?= $order->id ?>",
          "email": "<?= $order->billing_email ?>",
          "delivery_country": "<?= $order->billing_country ?>",
          "estimated_delivery_date": "<?= $estimated_delivery_date->format('Y-m-d') ?>",
          "opt_in_style": "CENTER_DIALOG"
        });
     });
  }
</script>
<!-- END GCR Opt-in Module Code -->

<!-- BEGIN GCR Language Code -->
<script>
  window.___gcfg = {
    lang: 'en_US'
  };
</script>
<!-- END GCR Language Code -->
