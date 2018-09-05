<?php
/**
 * Define the WC_AvaTax_AJAX class
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce AvaTax to newer
 * versions in the future. If you wish to customize WooCommerce AvaTax for your
 * needs please refer to http://docs.woocommerce.com/document/woocommerce-avatax/
 *
 * @package   AvaTax\AJAX
 * @author    SkyVerge
 * @copyright Copyright (c) 2016-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Handle the AJAX-specific functionality.
 *
 * @since 1.0.0
 */
class WC_AvaTax_AJAX {


	/**
	 * Construct the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Validate the Origin Address settings fields
		add_action( 'wp_ajax_wc_avatax_validate_origin_address', array( $this, 'validate_origin_address' ) );

		// Validate the customer address at checkout
		add_action( 'wp_ajax_wc_avatax_validate_customer_address', array( $this, 'validate_customer_address' ) );
		add_action( 'wp_ajax_nopriv_wc_avatax_validate_customer_address', array( $this, 'validate_customer_address' ) );

		// Display and save the product variation tax code field
		add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'display_product_variation_tax_code_field' ), 15, 3 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'save_product_variation_tax_code' ) );

		// Save the product tax code quick edit field
		add_action( 'woocommerce_product_quick_edit_save', array( $this, 'save_product_tax_code_quick_edit' ) );

		// Save the tax code field when a new product category is created
		add_action( 'created_product_cat', array( $this, 'save_category_tax_code_field' ), 10, 2 );

		// Add estimated AvaTax calculations to orders when "Calculate Taxes" is run from the admin
		add_action( 'woocommerce_saved_order_items', array( $this, 'estimate_order_tax' ) );

		// Process order refunds
		add_action( 'woocommerce_order_refunded', array( $this, 'process_refund' ), 10, 2 );
	}


	/**
	 * Validate the Origin Address settings fields.
	 *
	 * @since 1.0.0
	 */
	public function validate_origin_address() {

		// No nonce? No go
		check_ajax_referer( 'wc_avatax_validate_origin_address', 'nonce' );

		try {

			/**
			 * Fire before validating the origin address.
			 *
			 * @since 1.0.0
			 */
			do_action( 'wc_avatax_before_origin_address_validated' );

			$response = wc_avatax()->get_api()->validate_address( array(
				'address_1' => SV_WC_Helper::get_request( 'line1' ),
				'city'      => SV_WC_Helper::get_request( 'city' ),
				'state'     => SV_WC_Helper::get_request( 'region' ),
				'country'   => SV_WC_Helper::get_request( 'country' ),
				'postcode'  => SV_WC_Helper::get_request( 'postcode' ),
			) );

			// Documented in `WC_AvaTax_Settings::save_address_field`
			$address = (array) apply_filters( 'wc_avatax_save_address_field', $response->get_normalized_address() );

			// Save the validated address
			update_option( 'wc_avatax_origin_address', $address );

			/**
			 * Fire after validating the origin address.
			 *
			 * @since 1.0.0
			 * @param array $address The validated and normalized address.
			 */
			do_action( 'wc_avatax_after_origin_address_validated', $address );

			wp_send_json( array(
				'code'    => 200,
				'address' => $address,
			) );

		} catch ( SV_WC_API_Exception $e ) {

			if ( wc_avatax()->logging_enabled() ) {
				wc_avatax()->log( $e->getMessage() );
			}

			wp_send_json( array(
				'code'  => (int) $e->getCode(),
				'error' => esc_html( $e->getMessage() ),
			) );
		}
	}


	/**
	 * Validate the customer address at checkout.
	 *
	 * @since 1.0.0
	 */
	public function validate_customer_address() {

		// No nonce? No go
		if ( ! wp_verify_nonce( SV_WC_Helper::get_request( 'nonce' ), 'wc_avatax_validate_customer_address' ) ) {
			wp_die();
		}

		try {

			/**
			 * Fire before validating a customer address.
			 *
			 * @since 1.0.0
			 * @param array $address The validated and normalized address.
			 */
			do_action( 'wc_avatax_before_customer_address_validated' );

			$response = wc_avatax()->get_api()->validate_address( array(
				'address_1' => SV_WC_Helper::get_post( 'address_1' ),
				'address_2' => SV_WC_Helper::get_post( 'address_2' ),
				'city'      => SV_WC_Helper::get_post( 'city' ),
				'state'     => SV_WC_Helper::get_post( 'state' ),
				'country'   => SV_WC_Helper::get_post( 'country' ),
				'postcode'  => SV_WC_Helper::get_post( 'postcode' ),
			) );

			$address = $response->get_normalized_address();

			// Set the shipping address values to the normalized address
			WC()->customer->set_shipping_address( $address['address_1'] );
			WC()->customer->set_shipping_address_2( $address['address_2'] );
			WC()->customer->set_shipping_city( $address['city'] );
			WC()->customer->set_shipping_state( $address['state'] );
			WC()->customer->set_shipping_country( $address['country'] );
			WC()->customer->set_shipping_postcode( $address['postcode'] );

			$type = SV_WC_Helper::get_post( 'type' );

			// If validating a billing address, set those values too
			if ( 'billing' === $type ) {
				WC()->customer->set_address( $address['address_1'] );
				WC()->customer->set_address_2( $address['address_2'] );
				WC()->customer->set_city( $address['city'] );
				WC()->customer->set_state( $address['state'] );
				WC()->customer->set_country( $address['country'] );
				WC()->customer->set_postcode( $address['postcode'] );
			}

			// Prepend the address type (billing or shipping) to the keys
			foreach ( $address as $key => $value ) {
				$address[ $type . '_' . $key ] = $value;
				unset( $address[ $key ] );
			}

			/**
			 * Fire after validating a customer address.
			 *
			 * @since 1.0.0
			 * @param array $address The validated and normalized address.
			 */
			do_action( 'wc_avatax_after_customer_address_validated', $address );

			// Off you go
			wp_send_json( array(
				'code'    => 200,
				'address' => $address,
			) );

		} catch ( SV_WC_API_Exception $e ) {

			if ( wc_avatax()->logging_enabled() ) {
				wc_avatax()->log( $e->getMessage() );
			}

			wp_send_json( array(
				'code'  => (int) $e->getCode(),
				'error' => esc_html( $e->getMessage() ),
			) );
		}
	}


	/**
	 * Display the product varation tax code field.
	 *
	 * @since 1.0.0
	 * @param int $loop The varation loop key.
	 * @param array $variation_data The variation data.
	 * @param \WC_Product_Variation $variation The variation object.
	 */
	public function display_product_variation_tax_code_field( $loop, $variation_data, $variation ) {

		$default  = get_post_meta( $variation->post_parent, '_wc_avatax_code', true );
		$tax_code = get_post_meta( $variation->ID, '_wc_avatax_code', true );

		include( wc_avatax()->get_plugin_path() . '/includes/admin/views/html-field-product-variation-tax-code.php' );
	}


	/**
	 * Save a product varation tax code.
	 *
	 * @since 1.0.0
	 * @param int $varation_id The varation ID.
	 */
	public function save_product_variation_tax_code( $variation_id ) {

		$tax_code = '';

		if ( isset( $_POST['variable_wc_avatax_code'] ) && isset( $_POST['variable_post_id'] ) && ( false !== ( $i = array_search( $variation_id, $_POST['variable_post_id'] ) ) ) ) {
			$tax_code = $_POST['variable_wc_avatax_code'][ $i ];
		}

		if ( '' !== $tax_code ) {
			update_post_meta( $variation_id, '_wc_avatax_code', wc_clean( $tax_code ) );
		} else {
			delete_post_meta( $variation_id, '_wc_avatax_code' );
		}
	}


	/**
	 * Save the product tax code quick edit field.
	 *
	 * @since 1.0.0
	 * @param \WC_Product $product The product object.
	 */
	public function save_product_tax_code_quick_edit( $product ) {

		if ( isset( $_REQUEST['_wc_avatax_code'] ) ) {
			update_post_meta( $product->get_id(), '_wc_avatax_code', sanitize_text_field( $_REQUEST['_wc_avatax_code'] ) );
		}
	}


	/**
	 * Save the tax code field when a new product category is created.
	 *
	 * @since 1.0.0
	 * @param int $term_id The term ID.
	 * @param int $tt_id The term taxonomy ID.
	 */
	public function save_category_tax_code_field( $term_id, $tt_id ) {

		$tax_code = sanitize_text_field( SV_WC_Helper::get_post( 'wc_avatax_category_tax_code' ) );

		update_woocommerce_term_meta( $term_id, 'wc_avatax_tax_code', $tax_code );
	}


	/**
	 * Add estimated AvaTax calculations to orders when "Calculate Taxes" is run from the admin.
	 *
	 * @since 1.0.0
	 * @param int $order_id The order ID.
	 */
	public function estimate_order_tax( $order_id ) {

		// If not otherwise calculating taxes, bail
		if ( ! doing_action( 'wp_ajax_woocommerce_calc_line_taxes' ) ) {
			return;
		}

		// If tax calculation is turned off, bail
		if ( ! wc_avatax()->calculate_taxes() ) {
			return;
		}

		$order = wc_get_order( $order_id );

		// If this order has already been sent to Avalara, bail
		if ( ! $order || wc_avatax()->get_order_handler()->is_order_posted( $order ) ) {
			return;
		}

		wc_avatax()->get_order_handler()->calculate_order_tax( $order );
	}


	/**
	 * Process order refunds and get accurate tax refund rates from the AvaTax API.
	 *
	 * Totals passed around this method are mostly negative floats that will _subtract_ from an order's total.
	 *
	 * @since 1.0.0
	 * @param int $order_id The order ID.
	 * @param int $refund_id The refund ID.
	 */
	public function process_refund( $order_id, $refund_id ) {

		// If tax calculation is turned off, bail
		if ( ! wc_avatax()->calculate_taxes() ) {
			return;
		}

		/**
		 * Filter whether refunds should be calculated as negative tax liability with Avalara.
		 *
		 * @since 1.0.0
		 * @param bool $calculate_refund_taxes
		 */
		if ( ! apply_filters( 'wc_avatax_calculate_refund_taxes', true ) ) {
			return;
		}

		$refund = wc_get_order( $refund_id );

		if ( ! $refund ) {
			return;
		}

		// If the refund's original order is already voided or was never posted to Avalara, bail
		if ( wc_avatax()->get_order_handler()->is_order_voided( $order_id ) || ! wc_avatax()->get_order_handler()->is_order_posted( $order_id ) ) {
			return;
		}

		wc_avatax()->get_order_handler()->process_refund( $refund );
	}


}
