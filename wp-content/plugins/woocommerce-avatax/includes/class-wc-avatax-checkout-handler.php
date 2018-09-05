<?php
/**
 * Define the WC_AvaTax_Checkout_Handler class
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
 * Handle the checkout-specific functionality.
 *
 * @since 1.0.0
 */
class WC_AvaTax_Checkout_Handler {


	/**
	 * Construct the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		if ( wc_avatax()->calculate_taxes() ) {

			// Calculate the tax based on the cart at checkout
			add_action( 'woocommerce_after_calculate_totals', array( $this, 'calculate_taxes' ) );

			// Set the customer VAT ID at checkout
			add_action( 'woocommerce_checkout_update_order_review', array( $this, 'set_customer_vat' ) );
		}
	}


	/**
	 * Set the checkout totals to reflect the calculated tax from AvaTax.
	 *
	 * These tax totals are preserved in the session and saved to the order when it is placed via
	 * gateways that don't trigger `woocommerce_payment_complete`. However, the data isn't saved as
	 * a transaction in Avalara at this point and needs to be manually sent (committed) by the order
	 * admin "Send to Avalara" action.
	 *
	 * TODO: When the AvaTax REST API allows modifying existing documents, it would be better to send
	 *       this data as an "Uncommitted" document, then switch it to "Committed" once the order has
	 *       been paid, both at `woocommerce_payment_complete` or via the admin action.
	 *
	 * WooCommerce already handles the various tax display scenarios (included tax, etc...)
	 * pretty well, so instead of filter every total/subtotal output, we force-add the AvaTax taxes
	 * to the cart's tax totals. The rest of the calculations and display are handled automatically.
	 *
	 * @since 1.0.0
	 * @param WC_Cart $cart The cart instance.
	 */
	public function calculate_taxes( $cart ) {

		try {

			/**
			 * Fire before calculating the cart tax at checkout.
			 *
			 * @since 1.0.0
			 */
			do_action( 'wc_avatax_before_checkout_tax_calculated' );

			// If at checkout and updating the order review (address changes, etc...) then ping the API
			if ( $this->ready_for_calculation() && $this->needs_calculation() ) {

				// Ping the API
				$response = wc_avatax()->get_api()->calculate_checkout_tax( $cart );

				$result = array(
					'lines' => $response->get_lines(),
					'total' => $response->get_total_tax(),
				);

				$this->store_tax_result( $result, $cart );

			// Or if not at checkout at all (add to cart, ect...), clear any calculations
			} else if ( ! $this->ready_for_calculation() ) {

				// Clear the calculated taxes from the session
				$this->clear_stored_tax_results();

				return;

			// Otherwise, the order is being processed/paid, so grab the session
			} else {

				$result = $this->get_stored_tax_result( $cart );
			}

			if ( ! $result ) {
				return;
			}

			$total_tax    = $result['total'];
			$shipping_tax = 0;

			foreach ( $result['lines'] as $line ) {

				$line_id  = $line['id'];
				$line_tax = $line['total'];

				// If this is the shipping line, add to the shipping tax total
				if ( 'shipping' == $line_id ) {

					$shipping_tax += $line_tax;

				} else if ( isset( $cart->cart_contents[ $line_id ] ) ) {

					// Add the AvaTax line taxes
					$cart->cart_contents[ $line_id ]['line_tax'] += $line_tax;
					$cart->cart_contents[ $line_id ]['line_subtotal_tax'] += $line_tax;

					$cart->cart_contents[ $line_id ]['line_tax_data']['total']['avatax']    = $line_tax;
					$cart->cart_contents[ $line_id ]['line_tax_data']['subtotal']['avatax'] = $line_tax;
				}
			}

			$subtotal_tax = $total_tax - $shipping_tax;

			$cart->taxes['avatax']          = $subtotal_tax;
			$cart->shipping_taxes['avatax'] = $shipping_tax;

			$cart->tax_total += $subtotal_tax;
			$cart->shipping_tax_total += $shipping_tax;

			$cart->total += $total_tax;
			$cart->subtotal += $subtotal_tax;

			/**
			 * Fire after calculating the cart tax at checkout.
			 *
			 * @since 1.0.0
			 */
			do_action( 'wc_avatax_after_checkout_tax_calculated' );

		} catch ( SV_WC_API_Exception $e ) {

			$error = sprintf( __( 'Checkout Error: %s', 'woocommerce-avatax' ), $e->getMessage() );

			if ( wc_avatax()->logging_enabled() ) {
				wc_avatax()->log( $error );
			}
		}
	}


	/**
	 * Store a set of calculated tax results for a cart instance
	 *
	 * @since 1.1.2
	 * @param array $result {
	 *     The tax results
	 *
	 *     @type array $lines The line items
	 *     @type int   $total The total tax
	 * }
	 * @param \WC_Cart $cart the cart instance for which taxes were calculated
	 */
	protected function store_tax_result( $result, WC_Cart $cart ) {

		// Ensure stored results are always in the same format
		$result = wp_parse_args( (array) $result, array(
			'lines' => array(),
			'total' => 0,
		) );

		$cart_id = $this->generate_cart_id( $cart );
		$totals  = $this->get_stored_tax_results();

		$totals[ $cart_id ] = $result;

		WC()->session->set( 'avatax_totals', $totals );
	}


	/**
	 * Get the stored tax results for a cart instance
	 *
	 * @since 1.1.2
	 * @param \WC_Cart $cart the cart instance
	 * @return array
	 */
	protected function get_stored_tax_result( WC_Cart $cart ) {

		$totals = $this->get_stored_tax_results();

		$cart_id = $this->generate_cart_id( $cart );

		return isset( $totals[ $cart_id ] ) ? $totals[ $cart_id ] : null;
	}


	/**
	 * Get all of the stored tax results for the current session
	 *
	 * @since 1.1.2
	 * @return array
	 */
	protected function get_stored_tax_results() {

		$totals = WC()->session->get( 'avatax_totals' );

		if ( ! $totals ) {
			$totals = array();
		}

		return $totals;
	}


	/**
	 * Clear all of the stored tax results for the current session
	 *
	 * @since 1.1.2
	 */
	protected function clear_stored_tax_results() {

		unset( WC()->session->avatax_totals );
	}


	/**
	 * Generate a unique ID for the given cart instance.
	 *
	 * @since 1.1.2
	 * @param \WC_Cart $cart the cart instance
	 * @return string
	 */
	protected function generate_cart_id( WC_Cart $cart ) {

		$cart = clone $cart;

		$cart_contents = array();

		foreach ( $cart->cart_contents as $key => $item ) {

			// Remove the actual product objects, just in case
			unset( $item['data'] );

			$cart_contents[ $key ] = $item;
		}

		// Build the hash data from the cart contents that affect tax calculation
		$hash_data = array(
			$cart_contents,
			$cart->get_fees(),
			$cart->shipping_total,
		);

		$cart_id = md5( json_encode( $hash_data ) );

		return $cart_id;
	}


	/**
	 * Determine if the front-end is ready for tax calculation.
	 *
	 * The main factors here are whether we're on the checkout page and if the customer
	 * has supplied enough address information.
	 *
	 * @since 1.0.0
	 * @return bool $ready_for_calculation Whether the front-end is ready for tax calculation.
	 */
	private function ready_for_calculation() {

        if ( ! WC()->customer->get_shipping_country() || ! WC()->customer->get_shipping_state() || ! WC()->customer->get_shipping_postcode() ) {
            return false;
        }

		// first check that we're on the checkout page
		$ready_for_calculation = ( defined( 'WOOCOMMERCE_CHECKOUT' ) && WOOCOMMERCE_CHECKOUT ) || isset( $_POST['woocommerce_checkout_update_totals'] );

		// next check that the basic minimum address info is available
		$ready_for_calculation = ( $ready_for_calculation && WC()->customer->get_shipping_country() && $this->is_taxable() );

		// check the locale for required region & postcode fields
		$locale_fields = WC()->countries->get_address_fields( WC()->customer->get_shipping_country(), 'shipping_' );

		if ( $locale_fields['shipping_state']['required'] && ! WC()->customer->get_shipping_state() ) {
			$ready_for_calculation = false;
		}

		if ( $locale_fields['shipping_postcode']['required'] && ! WC()->customer->get_shipping_postcode() ) {
			$ready_for_calculation = false;
		}

		/**
		 * Filter whether the front-end is ready for tax calculation.
		 *
		 * @since 1.0.0
		 * @param $ready_for_calculation Whether the front-end is ready for tax calculation.
		 */
		return (bool) apply_filters( 'wc_avatax_checkout_ready_for_calculation', $ready_for_calculation );
	}


	/**
	 * Determine if the cart needs new taxes calculated.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	private function needs_calculation() {

		$needs_calculation = (
			doing_action( 'wc_ajax_update_order_review' ) ||
			doing_action( 'wp_ajax_woocommerce_update_order_review' ) ||
			doing_action( 'wp_ajax_nopriv_woocommerce_update_order_review' ) ||
			WC()->session->get( 'reload_checkout' ) ||
			isset( $_POST['woocommerce_checkout_update_totals'] )
		);

		/**
		 * Filter whether the cart needs new taxes calculated.
		 *
		 * @since 1.0.0
		 * @param $needs_calculation Whether the cart needs new taxes calculated.
		 */
		return (bool) apply_filters( 'wc_avatax_cart_needs_calculation', $needs_calculation );
	}


	/**
	 * Determine if tax calculation is supported by the customer's taxable address.
	 *
	 * @since 1.1.0
	 * @return bool
	 */
	private function is_taxable() {

		$taxable_address = WC()->customer->get_taxable_address();

		$country_code = $taxable_address[0];
		$state        = $taxable_address[1];

		/**
		 * Filter whether the destination location is taxable by AvaTax.
		 *
		 * @since 1.1.0
		 * @param bool $is_taxable
		 */
		return apply_filters( 'wc_avatax_checkout_is_taxable', wc_avatax()->is_location_taxable( $country_code, $state ) );
	}


	/**
	 * Set the customer's VAT ID at checkout.
	 *
	 * @since 1.0.0
	 * @param string $post_data The posted data.
	 */
	public function set_customer_vat( $post_data ) {

		if ( ! empty( $post_data ) ) {

			$post_data = explode( '&', $post_data );

			foreach ( $post_data as $pair ) {
				$pair                  = explode( '=', $pair );
				$post_data[ $pair[0] ] = urldecode( $pair[1] );
			}
		}

		if ( isset( $post_data['billing_wc_avatax_vat_id'] ) ) {
			WC()->customer->vat_id = $post_data['billing_wc_avatax_vat_id'];
		}
	}


}
