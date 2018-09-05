<?php
/**
 * Define the WC_AvaTax_Order_Handler class
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
 * Handle the order-specific functionality.
 *
 * @since 1.0.0
 */
class WC_AvaTax_Order_Handler {

	/** @var string The prefix for order note error messages **/
	protected $error_prefix;


	/**
	 * Construct the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->error_prefix = '<strong>' . __( 'AvaTax Error', 'woocommerce-avatax' ) . '</strong> -';

		if ( wc_avatax()->calculate_taxes() ) {

			// Set the effective tax date when a new order is placed
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'set_checkout_order_meta' ) );

			// Calculate order taxes and send to Avalara tax when payment is complete
			add_action( 'woocommerce_payment_complete', array( $this, 'process_paid_order' ) );

			// Also calculate and send on order status change for gateways that don't call WC_Order::payment_complete
			add_action( 'woocommerce_order_status_on-hold_to_processing', array( $this, 'process_paid_order' ) );
			add_action( 'woocommerce_order_status_on-hold_to_completed',  array( $this, 'process_paid_order' ) );
			add_action( 'woocommerce_order_status_failed_to_processing',  array( $this, 'process_paid_order' ) );
			add_action( 'woocommerce_order_status_failed_to_completed',   array( $this, 'process_paid_order' ) );

			// Calculate order taxes and send to Avalara manually through the admin action
			add_action( 'woocommerce_order_action_wc_avatax_send', array( $this, 'process_order' ) );

			// Void an order's Avalara document when cancelled
			add_action( 'woocommerce_order_status_cancelled', array( $this, 'void_order' ) );

			// Void an order's refund documents in Avalara.
			add_action( 'wc_avatax_after_order_voided', array( $this, 'void_order_refunds' ) );
		}
	}


	/**
	 * Set the effective tax date based on the order date.
	 *
	 * @since 1.0.0
	 * @param int $order_id The order ID
	 */
	public function set_checkout_order_meta( $order_id ) {

		$order = wc_get_order( $order_id );

		if ( $order && isset( WC()->cart->taxes['avatax'] ) ) {

			update_post_meta( $order_id, '_wc_avatax_tax_calculated', 'yes' );

			if ( $date_created = SV_WC_Order_Compatibility::get_date_created( $order ) ) {
				update_post_meta( $order_id, '_wc_avatax_tax_date', $date_created->date( 'Y-m-d' ) );
			}
		}
	}


	/**
	 * Calculate order taxes and send to Avalara tax when payment is complete.
	 *
	 * @since 1.0.0
	 * @param WC_Order $order The order object.
	 */
	public function process_paid_order( $order_id ) {

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		// Clear any calculated taxes from the session
		unset( WC()->session->avatax_result );

		// If tax was never calculated for the order (manually or at checkout), bail
		if ( ! SV_WC_Order_Compatibility::get_meta( $order, '_wc_avatax_tax_calculated' ) ) {
			return;
		}

		// Calculate the order taxes and send a document to Avalara
		$this->process_order( $order );
	}


	/**
	 * Calculate order taxes and send to Avalara.
	 *
	 * @since 1.0.0
	 * @param WC_Order $order The order object.
	 * @return int|bool $order_id The processed order ID or false on failure.
	 */
	public function process_order( WC_Order $order ) {

		// If this order has already been sent to Avalara, bail
		if ( $this->is_order_posted( $order ) ) {
			return;
		}

		/**
		 * Fire before processing tax for an order.
		 *
		 * @since 1.0.0
		 * @param int $order_id The order ID.
		 */
		do_action( 'wc_avatax_before_order_processed', SV_WC_Order_Compatibility::get_prop( $order, 'id' ) );

		// Attempt the calculation
		$result = $this->calculate_order_tax( $order, true );

		// If failed, update the order accordingly
		if ( $result instanceof SV_WC_API_Exception ) {

			$this->add_status( $order, 'error' );

			$order->add_order_note(
				/* translators: Placeholders: %1$s - error indicator, %2$s - error message */
				sprintf( __( '%1$s Order could not be sent. %2$s', 'woocommerce-avatax' ),
					$this->error_prefix,
					$result->getMessage()
				)
			);

			/**
			 * Fire if an order failed to send to Avalara.
			 *
			 * @since 1.0.0
			 * @param int $order_id The order ID
			 */
			do_action( 'wc_avatax_order_failed', SV_WC_Order_Compatibility::get_prop( $order, 'id' ) );

		// Otherwise, continue processing
		} elseif ( $result instanceof WC_Order ) {

			// Remove any error status if it exists
			$this->remove_status( $order, 'error' );

			// Let the world know: this order has been posted to Avalara
			$this->add_status( $order, 'posted' );

			$order->add_order_note( __( 'Order sent to Avalara.', 'woocommerce-avatax' ) );

			/**
			 * Fire when an order is sent to Avalara.
			 *
			 * @since 1.0.0
			 * @param int $order_id The order ID
			 */
			do_action( 'wc_avatax_order_processed', SV_WC_Order_Compatibility::get_prop( $order, 'id' ) );
		}
	}


	/**
	 * Calculate and update taxes for an order.
	 *
	 * By default, this calculation is invisible to Avatax. If you want to record this transaction
	 * as an Avalara document you can set the `$commit` param to `true`.
	 *
	 * @since 1.0.0
	 * @param WC_Order $order The order object.
	 * @param bool $commit Whether to commit the transaction to Avalara
	 * @return int|bool $order_id The processed order ID or false on failure.
	 */
	public function calculate_order_tax( WC_Order $order, $commit = false ) {

		try {

			/**
			 * Fire before calculating tax for an order.
			 *
			 * @since 1.0.0
			 * @param int $order_id The order ID.
			 */
			do_action( 'wc_avatax_before_order_tax_calculated', SV_WC_Order_Compatibility::get_prop( $order, 'id' ) );

			// Reset the order values
			// This removes any AvaTax calculations that stuck around from checkout or manual creation
			$order->calculate_totals();

			// Call the API
			$response = wc_avatax()->get_api()->calculate_order_tax( $order, $commit );

			foreach ( $response->get_lines() as $line ) {

				$line_id  = $line['id'];
				$line_tax = $line['total'];

				// If this is the shipping line, add to the shipping tax total
				if ( SV_WC_Helper::str_starts_with( $line_id, 'shipping_' ) ) {

					$item_id = str_replace( 'shipping_', '', $line_id );

					// Get the shipping method's existing tax data
					$taxes = maybe_unserialize( wc_get_order_item_meta( $item_id, 'taxes', true ) );

					// Add the AvaTax data and update
					if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) {
						$taxes['total']['avatax'] = $line_tax;
					} else {
						$taxes['avatax'] = $line_tax;
					}

					wc_update_order_item_meta( $item_id, 'taxes', $taxes );

				} else {

					$item_id = $line_id;

					// Get the item's existing line taxes
					$total_tax    = wc_get_order_item_meta( $line_id, '_line_tax', true );
					$subtotal_tax = wc_get_order_item_meta( $line_id, '_line_subtotal_tax', true );

					// Add the AvaTax line taxes
					wc_update_order_item_meta( $line_id, '_line_tax', $total_tax + $line_tax );
					wc_update_order_item_meta( $line_id, '_line_subtotal_tax', $subtotal_tax + $line_tax );

					// Get the item's existing tax data
					$tax_data = maybe_unserialize( wc_get_order_item_meta( $line_id, '_line_tax_data', true ) );

					// Add the AvaTax data and update
					$tax_data['total']['avatax']    = $line_tax;
					$tax_data['subtotal']['avatax'] = $line_tax;
					wc_update_order_item_meta( $line_id, '_line_tax_data', $tax_data );

				}

				// Save the item tax code
				wc_update_order_item_meta( $item_id, '_wc_avatax_code', wc_clean( $line['code'] ) );

				// Save the calculated tax rate
				wc_update_order_item_meta( $item_id, '_wc_avatax_rate', (float) $line['rate'] );
			}

			// Save the effective tax date
			update_post_meta( SV_WC_Order_Compatibility::get_prop( $order, 'id' ), '_wc_avatax_tax_date', $response->get_tax_date() );

			// Save the calculated addresses as order meta in case refund calculation is needed
			update_post_meta( SV_WC_Order_Compatibility::get_prop( $order, 'id' ), '_wc_avatax_origin_address', $response->get_origin_address() );
			update_post_meta( SV_WC_Order_Compatibility::get_prop( $order, 'id' ), '_wc_avatax_destination_address', $response->get_destination_address() );

			// save the customer use code, if any
			update_post_meta( SV_WC_Order_Compatibility::get_prop( $order, 'id' ), '_wc_avatax_exemption', get_user_meta( $order->get_user_id(), 'wc_avatax_tax_exemption', true ) );

			update_post_meta( SV_WC_Order_Compatibility::get_prop( $order, 'id' ), '_wc_avatax_tax_calculated', 'yes' );

			// Get a fresh order object with all of the meta loaded that was set above.
			$order = wc_get_order( $order );

			// Update the total tax values
			$order->update_taxes();

			// Update the order total values
			// Don't recalculate taxes however, as this would clear the line item taxes
			$order->calculate_totals( false );

			/**
			 * Fire after calculating tax for an order.
			 *
			 * @since 1.0.0
			 * @param int $order_id The order ID.
			 */
			do_action( 'wc_avatax_after_order_tax_calculated', SV_WC_Order_Compatibility::get_prop( $order, 'id' ) );

			return $order;

		} catch ( SV_WC_API_Exception $e ) {

			if ( wc_avatax()->logging_enabled() ) {
				wc_avatax()->log( $e->getMessage() );
			}

			return $e;
		}
	}


	/**
	 * Calculate refund taxes and send to Avalara.
	 *
	 * Totals passed around this method are mostly negative floats that will _subtract_ from an order's total.
	 *
	 * @since 1.0.0
	 * @param WC_Order_Refund $refund The order refund object.
	 */
	public function process_refund( WC_Order_Refund $refund ) {

		$order = wc_get_order( SV_WC_Order_Compatibility::get_prop( $refund, 'parent_id' ) );

		if ( ! $order ) {
			return;
		}

		try {

			/**
			 * Fire before processing tax for a refund.
			 *
			 * @since 1.0.0
			 * @param int $refund_id The refund ID.
			 */
			do_action( 'wc_avatax_before_refund_processed', SV_WC_Order_Compatibility::get_prop( $refund, 'id' ) );

			// Make the call
			$response = wc_avatax()->get_api()->calculate_refund_tax( $refund );

			// Store the refund amount estimated by the standard WooCommerce methods
			$estimated_total = $refund->get_total();

			foreach ( $response->get_lines() as $line ) {

				$line_id  = $line['id'];
				$line_tax = $line['total'];

				// Handle the shipping line items
				if ( SV_WC_Helper::str_starts_with( $line_id, 'shipping_' ) ) {

					$item_id = str_replace( 'shipping_', '', $line_id );

					$taxes = maybe_unserialize( wc_get_order_item_meta( $item_id, 'taxes', true ) );

					// Remove this item's estimated tax amount from the total
					if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) {
						$estimated_total -= $taxes['total']['avatax'];
					} else {
						$estimated_total += abs( $taxes['avatax'] );
					}

					// Set the newly calculated amount
					if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) {
						$taxes['total']['avatax'] = $line_tax;
					} else {
						$taxes['avatax'] = $line_tax;
					}

					wc_update_order_item_meta( $item_id, 'taxes', $taxes );

				// Handle all other line items
				} else {

					$estimated_line_tax = wc_get_order_item_meta( $line_id, '_line_tax', true );

					// Remove this item's estimated tax amount from the total
					if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) {
						$estimated_total -= $estimated_line_tax;
					} else {
						$estimated_total += abs( $estimated_line_tax );
					}

					// Set the newly calculated amounts
					wc_update_order_item_meta( $line_id, '_line_tax', $line_tax );
					wc_update_order_item_meta( $line_id, '_line_subtotal_tax', $line_tax );

					$tax_data = maybe_unserialize( wc_get_order_item_meta( $line_id, '_line_tax_data', true ) );

					$tax_data['total']['avatax']    = $line_tax;
					$tax_data['subtotal']['avatax'] = $line_tax;

					wc_update_order_item_meta( $line_id, '_line_tax_data', $tax_data );
				}
			}

			// Set the newly calculated amount
			if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) {
				$total = $estimated_total + $response->get_total_tax();
			} else {
				$total = $estimated_total - $response->get_total_tax();
			}

			// Phew! Finally, set the total refund amount (positive)
			update_post_meta( SV_WC_Order_Compatibility::get_prop( $refund, 'id' ), '_refund_amount', abs( $total ) );

			if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) {

				// Get a fresh refund object with the meta loaded that we set above.
				$refund = wc_get_order( $refund );
			}

			// Update the total tax values for the refund
			$refund->update_taxes();

			// Set the grand total
			$refund->set_total( $total );

			// Add the refunded status to the original order
			$this->add_status( $order, 'refunded' );

			$order->add_order_note( sprintf( __( 'Refund #%s sent to Avalara.', 'woocommerce-avatax' ), SV_WC_Order_Compatibility::get_prop( $refund, 'id' ) ) );

			/**
			 * Fire after processing tax for a refund.
			 *
			 * @since 1.0.0
			 * @param int $refund_id The refund ID.
			 */
			do_action( 'wc_avatax_after_refund_processed', SV_WC_Order_Compatibility::get_prop( $refund, 'id' ) );

		} catch ( SV_WC_API_Exception $e ) {

			if ( wc_avatax()->logging_enabled() ) {
				wc_avatax()->log( $e->getMessage() );
			}

			$this->add_status( $order, 'error' );

			$order->add_order_note(
				/* translators: Placeholders: %1$s - error indicator, %2$s - error message */
				sprintf( __( '%1$s Refund could not be sent. %2$s Please add the refund manually from your Avalara Control Panel.', 'woocommerce-avatax' ),
					$this->error_prefix,
					$e->getMessage()
				)
			);
		}
	}


	/**
	 * Void an order's Avalara document.
	 *
	 * @since 1.0.0
	 * @param int $order_id The order ID.
	 */
	public function void_order( $order_id ) {

		// If the order has already been voided, bail
		if ( $this->is_order_voided( $order_id ) || ! $this->is_order_posted( $order_id ) ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		try {

			/**
			 * Fire before voiding tax for an order.
			 *
			 * @since 1.0.0
			 * @param int $order_id The order ID.
			 */
			do_action( 'wc_avatax_before_order_voided', $order_id );

			$response = wc_avatax()->get_api()->void_order( $order_id );

			$this->add_status( $order_id, 'voided' );

			$void_data = $response->get_void_data();

			// Set a couple of reference meta values in case we need them in the future
			update_post_meta( $order_id, '_wc_avatax_transaction_id', $void_data['transaction_id'] );
			update_post_meta( $order_id, '_wc_avatax_doc_id', $void_data['document_id'] );

			$order->add_order_note( __( 'Order voided in Avalara.', 'woocommerce-avatax' ) );

			/**
			 * Fire after voiding tax for an order.
			 *
			 * @since 1.0.0
			 * @param int $order_id The order ID.
			 * @param array $void_data The raw void data from Avalara.
			 */
			do_action( 'wc_avatax_after_order_voided', $order_id, $void_data );

		} catch ( SV_WC_API_Exception $e ) {

			if ( wc_avatax()->logging_enabled() ) {
				wc_avatax()->log( $e->getMessage() );
			}

			$this->add_status( $order_id, 'error' );

			$order->add_order_note(
				/* translators: Placeholders: %1$s - error indicator, %2$s - error message */
				sprintf( __( '%1$s Order could not be voided. %2$s Please void manually from your Avalara Control Panel.', 'woocommerce-avatax' ),
					$this->error_prefix,
					$e->getMessage()
				)
			);
		}
	}


	/**
	 * Void an order's refund documents in Avalara.
	 *
	 * @since 1.0.0
	 * @param int $order_id The order ID
	 */
	public function void_order_refunds( $order_id ) {

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		/**
		 * Filter whether refunds should be voided along with their parent order.
		 *
		 * @since 1.0.0
		 * @param bool $void_refunds
		 */
		if ( ! apply_filters( 'wc_avatax_void_order_refunds', true ) ) {
			return;
		}

		/**
		 * Fire before voiding order refunds.
		 *
		 * @since 1.0.0
		 * @param int $order_id The order ID
		 */
		do_action( 'wc_avatax_before_order_refunds_voided', $order_id );

		$refunds        = $order->get_refunds();
		$failed_refunds = array();

		foreach ( $refunds as $refund ) {

			try {

				$response = wc_avatax()->get_api()->void_refund( $refund );

				$this->add_status( $refund, 'voided' );

				$void_data = $response->get_void_data();

				// Set a couple of reference meta values in case we need them in the future
				update_post_meta( SV_WC_Order_Compatibility::get_prop( $refund, 'id' ), '_wc_avatax_transaction_id', $void_data['transaction_id'] );
				update_post_meta( SV_WC_Order_Compatibility::get_prop( $refund, 'id' ), '_wc_avatax_doc_id', $void_data['document_id'] );

				$order->add_order_note( sprintf( __( 'Refund #%s voided in Avalara', 'woocommerce-avatax' ), SV_WC_Order_Compatibility::get_prop( $refund, 'id' ) ) );

			} catch ( SV_WC_API_Exception $e ) {

				if ( wc_avatax()->logging_enabled() ) {
					wc_avatax()->log( $e->getMessage() );
				}

				$this->add_status( $refund, 'error' );

				$failed_refunds[] = SV_WC_Order_Compatibility::get_prop( $refund, 'id' );
			}
		}

		// If something went wrong, leave an order note
		if ( ! empty( $failed_refunds ) ) {

			// Generalize the note if all refunds failed
			if ( count( $refunds ) === count( $failed_refunds ) ) {

				$error = __( 'Refunds could not be voided. Please void manually from your Avalara Control Panel.', 'woocommerce-avatax' );

			// Otherwise, list the refund IDs
			} else {

				$refund_ids = implode( ', #', $failed_refunds );

				$error = sprintf( __( 'Some refunds could not be voided. Please void refund %s manually from your Avalara Control Panel.', 'woocommerce-avatax' ),
					'#' . $refund_ids
				);
			}

			$order->add_order_note( $this->error_prefix . ' ' . $error );
		}

		/**
		 * Fire after voiding order refunds.
		 *
		 * @since 1.0.0
		 * @param int $order_id The order ID
		 */
		do_action( 'wc_avatax_after_order_refunds_voided', $order_id );
	}


	/**
	 * Add an AvaTax status to an order.
	 *
	 * @since 1.0.0
	 * @param \WC_Order|int $order The order object or ID.
	 * @param string $status The AvaTax status to add.
	 * @return int|false The resulting meta ID on success, false on failure.
	 */
	public function add_status( $order, $status ) {

		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		// Add the status if it doesn't already exist
		if ( ! $this->order_has_status( $order, $status ) ) {
			return add_post_meta( SV_WC_Order_Compatibility::get_prop( $order, 'id' ), '_wc_avatax_status', $status );
		} else {
			return false;
		}
	}


	/**
	 * Remove an AvaTax status from an order.
	 *
	 * @since 1.0.0
	 * @param \WC_Order|int $order The order object or ID.
	 * @param string $status The AvaTax status to remove.
	 * @return bool
	 */
	public function remove_status( $order, $status ) {

		return delete_post_meta( SV_WC_Order_Compatibility::get_prop( $order, 'id' ), '_wc_avatax_status', $status );
	}


	/**
	 * Determine if an order has already been posted to AvaTax.
	 *
	 * @since 1.0.0
	 * @param \WC_Order|int $order The order object or ID.
	 * @return bool Whether the order has already been posted to AvaTax.
	 */
	public function is_order_posted( $order ) {

		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		return ( $this->order_has_status( $order, 'posted' ) );
	}


	/**
	 * Determine if an order's refund has been posted to AvaTax.
	 *
	 * @since 1.0.0
	 * @param \WC_Order|int $order The order object or ID.
	 * @return bool Whether the order's refund has been posted to AvaTax.
	 */
	public function is_order_refunded( $order ) {

		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		return ( $this->order_has_status( $order, 'refunded' ) );
	}


	/**
	 * Determine if an order has been voided in AvaTax.
	 *
	 * @since 1.0.0
	 * @param \WC_Order|int $order The order object or ID.
	 * @return bool Whether the order has been voided in AvaTax.
	 */
	public function is_order_voided( $order ) {

		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		return ( $this->order_has_status( $order, 'voided' ) );
	}


	/**
	 * Determine if an order has a specific AvaTax status.
	 *
	 * @since 1.0.0
	 * @param \WC_Order|int $order The order object or ID.
	 * @param string $status Optional. The AvaTax status to check. If none set, it checks if any
	 *                       status is set.
	 * @return bool Whether the order has the specific status.
	 */
	public function order_has_status( $order, $status = '' ) {

		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		$statuses = $this->get_order_statuses( $order );

		// Check for any status if no specific status is passed
		if ( ! $status ) {
			return ! empty( $statuses );
		}

		return in_array( $status, $statuses );
	}


	/**
	 * Get the statuses of an order when last posted to AvaTax.
	 *
	 * Orders can have multiple statuses, like `posted` and 'refunded'.
	 *
	 * @since 1.0.0
	 * @param \WC_Order|int $order The order object or ID.
	 * @return array The order's AvaTax statuses.
	 */
	public function get_order_statuses( $order ) {

		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		$statuses = get_post_meta( SV_WC_Order_Compatibility::get_prop( $order, 'id' ), '_wc_avatax_status' );

		if ( ! $statuses ) {
			$statuses = array();
		}

		return $statuses;
	}


	/**
	 * Determine if an order is ready to be sent to AvaTax.
	 *
	 * The primary factor is if the order has a status that identifies it as "paid".
	 *
	 * @since 1.0.0
	 * @param WC_Order $order The order object
	 * @return bool Whether the order is ready to be sent to AvaTax.
	 */
	public function is_order_ready( WC_Order $order ) {

		// Assume it's not ready
		$is_ready = false;

		// Only continue checking if the order hasn't already been sent to AvaTax
		if ( ! $this->is_order_posted( $order ) ) {

			$status = $order->get_status();

			/**
			 * Filter the order statuses that allow manual order sending.
			 *
			 * @since 1.0.0
			 * @param array $ready_statuses The valid statuses.
			 */
			$ready_statuses = apply_filters( 'wc_avatax_order_ready_statuses', array(
				'processing',
				'completed',
			) );

			// See if the order has one of the ready statuses
			$is_ready = in_array( $status, $ready_statuses );

			// If not, and Order Status Manager is active, then check the status' paid property
			if ( class_exists( 'WC_Order_Status_Manager_Order_Status' ) && ! $is_ready ) {

				$status = new WC_Order_Status_Manager_Order_Status( $status );

				$is_ready = ( $status->get_id() > 0 && ! $status->is_core_status() && $status->is_paid() );
			}
		}

		/**
		 * Filter whether an order is ready to be sent to AvaTax.
		 *
		 * @since 1.0.0
		 * @param bool $is_ready
		 * @param int $order_id The order ID
		 */
		return apply_filters( 'wc_avatax_order_is_ready', $is_ready, SV_WC_Order_Compatibility::get_prop( $order, 'id' ) );
	}


}
