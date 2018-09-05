<?php
/**
 * Checkout login form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-login.php.
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
 * @version     2.0.0
 */

use Roots\Sage\RANDYS;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( is_user_logged_in() || 'no' === get_option( 'woocommerce_enable_checkout_login_reminder' ) ) {
	return;
}

$info_message = apply_filters( 'woocommerce_checkout_login_message', __( '<a href="#" class="show-login-signup"><div class="card card--padded">Click here to login, Create a new account or skip to checkout as a guest', 'woocommerce' )  . '</div></a>');
wc_print_notice( $info_message, 'notice' );
?>

<div class="row login-signup m-t-2">
	<div class="col-sm-12">
		<div class="woocommerce-message m-b-2"><p><i class="fa fa-exclamation-triangle woocommerce-message__alert-icon" aria-hidden="true"></i> <?php echo RANDYS\reset_password_message(); ?></p></div>
	</div>
	<div class="col-sm-6">
		<h3>Login</h3>
		<div class="card card--padded">
			<?php
				woocommerce_login_form(
					array(
						'redirect' => wc_get_page_permalink( 'checkout' ),
						'hidden'   => false,
					)
				);
			?>
		</div>
	</div>
	<div class="col-sm-6">
		<h3>Sign Up</h3>
		<div class="card card--padded">
			<form method="post" class="signup">

				<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
					<label for="reg_email"><span class="required">*</span><?php _e( 'Email Address', 'woocommerce' ); ?></label>
					<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" value="<?php if ( check_ajax_referer( 'singup_nonce', 'nonce', false ) && ! empty( $_POST['email'] ) ) echo esc_attr( $_POST['email'] ); ?>" />
				</p>

				<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
					<label for="reg_password"><span class="required">*</span><?php _e( 'Password', 'woocommerce' ); ?></label>
					<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" />
				</p>

				<!-- Spam Trap -->
				<div style="<?php echo ( ( is_rtl() ) ? 'right' : 'left' ); ?>: -999em; position: absolute;"><label for="trap"><?php _e( 'Anti-spam', 'woocommerce' ); ?></label><input type="text" name="email_2" id="trap" tabindex="-1" autocomplete="off" /></div>

				<p class="woocomerce-FormRow form-row form-row-wide">
					<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
					<input type="submit" class="woocommerce-Button button button--full-width m-t-1" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>" />
				</p>
				<input type="hidden" name="login_nonce" id="login_nonce" value="<?php echo wp_create_nonce( 'login_nonce' ); ?>" />
			</form>
		</div>
	</div>
</div>
