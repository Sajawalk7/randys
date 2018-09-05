<?php
/**
 * Edit address form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-edit-address.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$page_title = ( $load_address === 'billing' ) ? __( 'Billing Address', 'woocommerce' ) : __( 'Shipping Address', 'woocommerce' );

do_action( 'woocommerce_before_edit_account_address_form' ); ?>

<?php if ( ! $load_address ) : ?>
	<?php wc_get_template( 'myaccount/my-address.php' ); ?>
<?php else : ?>

	<form method="post" class="clearfix">

		<h3><?php echo apply_filters( 'woocommerce_my_account_edit_address_title', $page_title ); ?></h3>

		<?php do_action( "woocommerce_before_edit_address_form_{$load_address}" ); ?>

		<?php if( ! empty( $_POST ) ) { ?>

			<?php if(wp_verify_nonce( $_POST['form_edit_nonce'], 'form_edit_nonce_action' ) ) { ?>

				<?php foreach ( $address as $key => $field ) : ?>

					<?php woocommerce_form_field( $key, $field, wc_clean( $_POST[ $key ] ) ); ?>

				<?php endforeach; ?>

			<?php } ?>

		<?php } else { ?>

			<?php foreach ( $address as $key => $field ) : ?>

				<?php woocommerce_form_field( $key, $field, $field['value'] ); ?>

			<?php endforeach; ?>

		<?php } ?>

		<?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>
		<div class="clearfix"></div>
		<p class="center-align">
			<input type="submit" class="button m-t-2" name="save_address" value="<?php esc_attr_e( 'Save Address', 'woocommerce' ); ?>" />
			<?php wp_nonce_field( 'woocommerce-edit_address' ); ?>
			<input type="hidden" name="action" value="edit_address" />
		</p>
    <?php wp_nonce_field( 'form_edit_nonce_action', 'form_edit_nonce' ); ?>
	</form>

<?php endif; ?>

<?php do_action( 'woocommerce_after_edit_account_address_form' ); ?>
