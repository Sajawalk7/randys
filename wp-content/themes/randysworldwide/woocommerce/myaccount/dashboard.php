<?php
/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     2.6.0
 */

use Roots\Sage\RANDYS;

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}
?>

<?php
  if ( false === RANDYS\is_wholesale() ) {
?>
<button class="button button--short-blue float-right m-l-2 m-b-2" data-toggle="modal" data-target="#wholesale-modal">Request Wholesale Access</button>
<?php } ?>

<h1>My Account</h1>

<p>
  <?php
    echo sprintf( esc_attr__( 'Hello %s%s%s (not %2$s? %sSign out%s)', 'woocommerce' ), '<strong>', esc_html( $current_user->display_name ), '</strong>', '<a href="' . esc_url( wc_logout_url( wc_get_page_permalink( 'myaccount' ) ) ) . '">', '</a>' );
  ?>
</p>

<p>
  <?php
    echo sprintf( esc_attr__( 'From your account dashboard you can view your recent orders, manage your shipping and billing addresses and %4$sedit your password and account details%2$s.', 'woocommerce' ), '<a href="' . esc_url( wc_get_endpoint_url( 'orders' ) ) . '">', '</a>', '<a href="' . esc_url( wc_get_endpoint_url( 'edit-address' ) ) . '">', '<a href="' . esc_url( wc_get_endpoint_url( 'edit-account' ) ) . '">' );
  ?>
</p>

<?php
  /**
  * My Account dashboard.
  *
  * @since 2.6.0
  */
  do_action( 'woocommerce_account_dashboard' );

  /**
  * Deprecated woocommerce_before_my_account action.
  *
  * @deprecated 2.6.0
  */
  do_action( 'woocommerce_before_my_account' );

  wc_get_template( 'myaccount/my-address.php' );
  ?>

  <div class="row m-t-3">

  <?php
    wc_get_template( 'myaccount/reports.php' );
    wc_get_template( 'myaccount/wholesale.php' );
  ?>

  </div>

  <?php

if ( false === RANDYS\is_wholesale() ) {
?>
<div class="modal fade" id="wholesale-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></div>
    <div class="modal-content">
      <div class="modal-body">
        <div class="wholesale-form">
          <p>Fill out the form below to request a Wholesale account.</p>
          <?php echo do_shortcode('[gravityform id="4" title="false" description="false" ajax="true" tabindex="100"]'); ?>
          </div>
      </div>
    </div>
  </div>
</div>
<?php
}

  /**
  * Deprecated woocommerce_after_my_account action.
  *
  * @deprecated 2.6.0
  */
  do_action( 'woocommerce_after_my_account' );

