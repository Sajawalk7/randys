<?php
/**
 * WooCommerce Address Validation
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Address Validation to newer
 * versions in the future. If you wish to customize WooCommerce Address Validation for your
 * needs please refer to http://docs.woocommerce.com/document/address-validation/ for more information.
 *
 * @package     WC-Address-Validation/Templates/Postcode-Lookup
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Postcode lookup form
 *
 * @version 2.2.0
 * @since 1.0
 */

$address_type_class = "wc-address-validation-{$address_type}-field";
?>

<p class="form-row form-row-first wc-address-validation-field <?php echo esc_attr( $address_type_class ); ?>">
	<input type="text" class="input-text" name="wc_address_validation_postcode_lookup_postcode" placeholder="<?php esc_attr_e( 'Enter Postcode', 'woocommerce-address-validation' ); ?>" value="" />
</p>

<?php if ( $requires_house_number ): ?>
	<p class="form-row form-row-last wc-address-validation-field <?php echo esc_attr( $address_type_class ); ?>">
		<input type="text" class="input-text" name="wc_address_validation_postcode_lookup_postcode_house_number" placeholder="<?php esc_attr_e( 'Enter House Number', 'woocommerce-address-validation' ); ?>" value="" />
	</p>
<?php endif; ?>

<p class="form-row <?php if ( $requires_house_number ): ?>form-row-wide<?php else: ?>form-row-last<?php endif;?> wc-address-validation-field <?php echo esc_attr( $address_type_class ); ?>">
    <input type="hidden" class="wc-address-validation-address-type" value="<?php echo esc_attr( $address_type ); ?>" />
	<a href="#" class="button"><?php _e( 'Find Address', 'woocommerce-address-validation' ); ?></a>
</p>

<div class="clear"></div>

<p class="form-row message notes wc-address-validation-results wc-address-validation-field <?php echo esc_attr( $address_type_class ); ?>">
	<select name="wc_address_validation_postcode_lookup_postcode_results" style="width: 100%" class="wc-address-validation-enhanced-select select <?php echo esc_attr( $address_type ); ?>"></select>
</p>

<hr/>
<?php
