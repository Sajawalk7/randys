<?php
/**
 * Define the WC_AvaTax_Frontend class
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
 * @package   AvaTax\Frontend
 * @author    SkyVerge
 * @copyright Copyright (c) 2016-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Set up the AvaTax front-end.
 *
 * @since 1.0.0
 */
class WC_AvaTax_Frontend {


	/**
	 * Construct the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		if ( $this->address_validation_enabled() ) {

			// Load the JS
			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );

			// Add an address validation button below each address form at checkout.
			add_action( 'woocommerce_after_checkout_billing_form', array( $this, 'add_validate_address_button' ) );
			add_action( 'woocommerce_after_checkout_shipping_form', array( $this, 'add_shipping_validate_address_button' ) );

			// Validate the customer address at checkout when JavaScript is disabled.
			add_action( 'woocommerce_checkout_process', array( $this, 'validate_address' ) );
		}

		if ( wc_avatax()->calculate_taxes() ) {

			// Display a "pending calculation" message on the cart page
			if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
				add_action( 'woocommerce_cart_totals_before_order_total', array( $this, 'display_cart_calculation_message' ) );
			} else {
				add_filter( 'woocommerce_cart_totals_taxes_total_html', array( $this, 'adjust_single_tax_total_html' ) );
			}

			// Add the VAT field if enabled
			if ( apply_filters( 'wc_avatax_enable_vat', ( 'yes' === get_option( 'wc_avatax_enable_vat' ) ) ) ) {
				add_filter( 'woocommerce_billing_fields', array( $this, 'add_checkout_vat_field' ) );
			}

			// Add the AvaTax calculation to the review order product subtotal
			add_filter( 'woocommerce_get_price_including_tax', array( $this, 'adjust_product_price_including_tax' ), 10, 3 );
		}
	}


	/**
	 * Load the front-end JS.
	 *
	 * @since 1.0.0
	 */
	public function load_scripts() {

		if ( ! is_checkout() ) {
			return;
		}

		wp_enqueue_script( 'wc-avatax-frontend', wc_avatax()->get_plugin_url() . '/assets/js/frontend/wc-avatax-frontend.min.js', array( 'jquery' ), WC_AvaTax::VERSION, true );

		wp_localize_script( 'wc-avatax-frontend', 'wc_avatax_frontend', array(
			'address_validation_nonce'     => wp_create_nonce( 'wc_avatax_validate_customer_address' ),
			'address_validation_countries' => $this->get_address_validation_countries(),
			'i18n'                         => array(
				'address_validated' => __( 'Address validated.', 'woocommerce-avatax' ),
			),
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		) );
	}


	/**
	 * Add an address validation button at checkout.
	 *
	 * @since 1.0.0
	 */
	public function add_validate_address_button() {

		echo $this->get_validate_address_button();
	}


	/**
	 * Add an address validation button at checkout.
	 *
	 * @since 1.1.1
	 */
	public function add_shipping_validate_address_button() {

		echo $this->get_validate_address_button( 'shipping' );
	}


	/**
	 * Get the address validation button markup.
	 *
	 * @since 1.1.1
	 */
	protected function get_validate_address_button( $type = 'billing' ) {

		/**
		 * Filter the address validation button label.
		 *
		 * @since 1.0.0
		 * @param string $label The address validation button label.
		 */
		$label = apply_filters( 'wc_avatax_validate_address_button_label', __( 'Validate Address', 'woocommerce-avatax' ) );

		echo '<a class="wc_avatax_validate_address button" data-address-type="' . esc_attr( $type ) . '">' . esc_html( $label ) . '</a>';
	}


	/**
	 * Validate the customer address at checkout when JavaScript is disabled.
	 *
	 * @since 1.0.0
	 */
	public function validate_address() {

		// If the address validation button was not pressed, bail
		if ( ! SV_WC_Helper::get_post( 'woocommerce_checkout_update_totals' ) ) {
			return;
		}

		// Skip shipping if not needed
		if ( SV_WC_Helper::get_post( 'ship_to_different_address' ) ) {
			$type = 'shipping';
		} else {
			$type = 'billing';
		}

		$response = wc_avatax()->get_api()->validate_address( array(
			'address_1' => SV_WC_Helper::get_post( $type . '_address_1' ),
			'address_2' => SV_WC_Helper::get_post( $type . '_address_2' ),
			'city'      => SV_WC_Helper::get_post( $type . '_city' ),
			'state'     => SV_WC_Helper::get_post( $type . '_state' ),
			'country'   => SV_WC_Helper::get_post( $type . '_country' ),
			'postcode'  => SV_WC_Helper::get_post( $type . '_postcode' ),
		) );

		$address = $response->get_normalized_address();

		// Set the shipping address values to the normalized address
		$_POST[ $type . '_address_1' ] = $address['address_1'];
		$_POST[ $type . '_address_2' ] = $address['address_2'];
		$_POST[ $type . '_city' ]      = $address['city'];
		$_POST[ $type . '_state' ]     = $address['state'];
		$_POST[ $type . '_country' ]   = $address['country'];
		$_POST[ $type . '_postcode' ]  = $address['postcode'];

		wc_add_notice( __( 'Address validated.', 'woocommerce-avatax' ), 'success' );
	}


	/**
	 * Display a "pending calculation" message on the cart page when displaying a single tax total.
	 *
	 * @since 1.2.1
	 * @param string $html the tax total HTML
	 * @return string
	 */
	public function adjust_single_tax_total_html( $html ) {

		if ( is_cart() && wc_avatax()->override_wc_rates() ) {
			$html = esc_html( $this->get_cart_calculation_message() );
		}

		return $html;
	}


	/**
	 * Display a "pending calculation" message on the cart page when taxes are itemized.
	 *
	 * @since 1.2.1
	 */
	public function display_cart_calculation_message() {

		/** This filter is documented in woocommerce-avatax/woocommerce-avatax.php */
		$title = apply_filters( 'wc_avatax_tax_label', WC()->countries->tax_or_vat() );

		echo '<tr class="tax-total">';
			echo '<th>' . esc_html( $title ) . '</th>';
			echo '<td data-title="' . esc_attr( $title ) . '">' . esc_html( $this->get_cart_calculation_message() ) . '</td>';
		echo '</tr>';
	}


	/**
	 * Get the "pending calculation" message for the cart page.
	 *
	 * @since 1.2.1
	 * @return string
	 */
	protected function get_cart_calculation_message() {

		/**
		 * Filter the cart pending tax calculation message.
		 *
		 * @since 1.2.1
		 * @param string $message
		 */
		return apply_filters( 'wc_avatax_cart_message', __( 'Taxes will be calculated at checkout', 'woocommerce-avatax' ) );
	}


	/**
	 * Add the VAT field to the checkout billing fields.
	 *
	 * @since 1.0.0
	 * @param array $fields The existing checkout fields.
	 * @return array $fields The checkout fields.
	 */
	public function add_checkout_vat_field( $fields ) {

		$origin_address = get_option( 'wc_avatax_origin_address', array() );

		// Only output the VAT if applicable to the shop's origin address
		if ( ! in_array( $origin_address['country'], WC()->countries->get_european_union_countries( 'eu_vat' ) ) ) {
			return $fields;
		}

		/**
		 * Filter the VAT ID checkout field label.
		 *
		 * @since 1.0.0
		 * @param string $label The VAT ID checkout field label.
		 */
		$label = apply_filters( 'wc_avatax_vat_id_field_label', __( 'VAT ID', 'woocommerce-avatax' ) );

		$fields['billing_wc_avatax_vat_id'] = array(
			'label' => $label,
			'class' => array( 'form-row-wide' ),
		);

		return $fields;
	}


	/**
	 * Add the AvaTax calculation to the review order product subtotal.
	 *
	 * @since 1.0.0
	 * @return string $price The product subtotal.
	 */
	public function adjust_product_price_including_tax( $price, $qty, $product ) {

		if ( is_checkout() ) {

			$product_id = $product->get_id();

			foreach ( WC()->cart->cart_contents as $item_key => $item ) {

				if ( $product_id == $item['product_id'] || $product_id == $item['variation_id'] ) {
					$avatax = ( isset( $item['line_tax_data']['subtotal']['avatax'] ) ) ? $item['line_tax_data']['subtotal']['avatax'] : 0;
					$price += $avatax;
				}
			}
		}

		return $price;
	}


	/**
	 * Determine if address validation is available at checkout.
	 *
	 * @since 1.0.0
	 * @return bool $enabled Whether address validation is available at checkout.
	 */
	public function address_validation_available() {

		$countries = $this->get_address_validation_countries();

		return (bool) apply_filters( 'wc_avatax_address_validation_available', in_array( WC()->customer->get_shipping_country(), $countries ) );
	}


	/**
	 * Determine if address validation is enabled.
	 *
	 * @since 1.0.0
	 * @return bool $enabled Whether address validation is enabled.
	 */
	public function address_validation_enabled() {

		/**
		 * Filter whether address validation is enabled.
		 *
		 * @since 1.0.0
		 * @param bool $enabled Whether address validation is enabled.
		 */
		return (bool) apply_filters( 'wc_avatax_enable_address_validation', ( 'yes' === get_option( 'wc_avatax_enable_address_validation' ) ) );
	}


	/**
	 * Determine if address validation is available at checkout.
	 *
	 * @since 1.0.0
	 * @return bool $enabled Whether address validation is available at checkout.
	 */
	public function get_address_validation_countries() {

		$countries = get_option( 'wc_avatax_address_validation_countries' );

		/**
		 * Filter the countries that support address validation.
		 *
		 * @since 1.0.0
		 * @param array $countries The countries that support address validation.
		 */
		return (array) apply_filters( 'wc_avatax_address_validation_countries', $countries );
	}
}
