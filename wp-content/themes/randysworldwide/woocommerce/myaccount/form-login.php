<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
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

use Roots\Sage\RANDYS;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<div class="row m-t-3">

	<?php wc_print_notices(); ?>

	<?php do_action( 'woocommerce_before_customer_login_form' ); ?>

	<div class="col-sm-12">
		<div class="woocommerce-message m-b-2"><p><i class="fa fa-exclamation-triangle woocommerce-message__alert-icon" aria-hidden="true"></i> <?php echo RANDYS\reset_password_message(); ?></p></div>
	</div>

	<div class="u-columns col2-set col-md-6" id="customer_login">

		<div class="u-column1 col-1">

			<h3><?php _e( 'Login', 'woocommerce' ); ?></h3>

			<div class="card card--padded">
				<form method="post" class="login">

					<?php do_action( 'woocommerce_login_form_start' ); ?>

					<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
						<label for="username"><?php _e( 'Username or email address', 'woocommerce' ); ?> <span class="required">*</span></label>
						<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" value="<?php if ( check_ajax_referer( 'login_nonce', 'nonce', false ) && ! empty( $_POST['username'] ) ) echo esc_attr( $_POST['username'] ); ?>" />
					</p>
					<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
						<label for="password"><?php _e( 'Password', 'woocommerce' ); ?> <span class="required">*</span></label>
						<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" />
					</p>

					<?php do_action( 'woocommerce_login_form' ); ?>

					<p class="form-row form-row-first">
						<input class="woocommerce-Input woocommerce-Input--checkbox input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" />
						<label for="rememberme" class="input-checkbox-label"></label> <?php _e( 'Remember me', 'woocommerce' ); ?>
					</p>
					<p class="form-row form-row-last text-right woocommerce-LostPassword lost_password">
						<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php _e( 'Lost your password?', 'woocommerce' ); ?></a>
					</p>
					<p class="form-row form-row-wide">
						<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
						<input type="submit" class="woocommerce-Button button button--full-width m-t-1" name="login" value="<?php esc_attr_e( 'Login', 'woocommerce' ); ?>" />
					</p>
					<input type="hidden" name="login_nonce" id="login_nonce" value="<?php echo wp_create_nonce( 'login_nonce' ); ?>" />
					<?php do_action( 'woocommerce_login_form_end' ); ?>
				</form>
			</div>
		</div>
	</div>
	<div class="col-md-6">

		<div class="u-column2 col-2">

			<h3><?php _e( 'Signup', 'woocommerce' ); ?></h3>
			<div class="card card--padded">
				<form method="post" class="signup">

					<?php do_action( 'woocommerce_register_form_start' ); ?>

					<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>

						<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
							<label for="reg_username"><?php _e( 'Username', 'woocommerce' ); ?> <span class="required">*</span></label>
							<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" value="<?php if ( check_ajax_referer( 'signup_nonce', 'nonce', false ) && ! empty( $_POST['username'] ) ) echo esc_attr( $_POST['username'] ); ?>" />
						</p>

					<?php endif; ?>

					<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
						<label for="reg_email"><?php _e( 'Email address', 'woocommerce' ); ?> <span class="required">*</span></label>
						<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" value="<?php if ( check_ajax_referer( 'singup_nonce', 'nonce', false ) && ! empty( $_POST['email'] ) ) echo esc_attr( $_POST['email'] ); ?>" />
					</p>

					<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>

						<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
							<label for="reg_password"><?php _e( 'Password', 'woocommerce' ); ?> <span class="required">*</span></label>
							<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" />
						</p>

					<?php endif; ?>

					<!-- Spam Trap -->
					<div style="<?php echo ( ( is_rtl() ) ? 'right' : 'left' ); ?>: -999em; position: absolute;"><label for="trap"><?php _e( 'Anti-spam', 'woocommerce' ); ?></label><input type="text" name="email_2" id="trap" tabindex="-1" autocomplete="off" /></div>

					<?php do_action( 'woocommerce_register_form' ); ?>
					<?php do_action( 'register_form' ); ?>

					<p class="woocomerce-FormRow form-row form-row-wide">
						<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
						<input type="submit" class="woocommerce-Button button button--full-width m-t-1" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>" />
					</p>
					<input type="hidden" name="signup_nonce" id="signup_nonce" value="<?php echo wp_create_nonce( 'signup_nonce' ); ?>" />
					<?php do_action( 'woocommerce_register_form_end' ); ?>

				</form>
			</div>
		</div>

	</div>

</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
