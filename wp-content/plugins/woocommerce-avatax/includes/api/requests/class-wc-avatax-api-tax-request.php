<?php
/**
 * Define the WC_AvaTax_API_Tax_Request class
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
 * @package   AvaTax\API
 * @author    SkyVerge
 * @copyright Copyright (c) 2016-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * The AvaTax API address request class.
 *
 * @since 1.0.0
 */
class WC_AvaTax_API_Tax_Request extends WC_AvaTax_API_Request {

	/** @var string origin address ID */
	protected $origin_id = 'origin';

	/** @var string shipping address ID */
	protected $destination_id = 'destination';


	/**
	 * Construct the tax request object.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->method = 'POST';
	}


	/**
	 * Get the calculated tax for the current cart at checkout.
	 *
	 * @since 1.0.0
	 * @param \WC_Cart $cart cart object
	 */
	public function process_checkout( WC_Cart $cart ) {

		parse_str( SV_WC_Helper::get_post( 'post_data' ), $post_data );

		if ( empty( $post_data ) ) {
			$post_data = $_POST;
		}

		/**
		 * Filter the origin address at checkout.
		 *
		 * @since 1.1.0
		 * @param array $address {
		 *     The address values.
		 *
		 *     @type string $address_1 Address line 1
		 *     @type string $address_2 Address line 2
		 *     @type string $city      The city name
		 *     @type string $state     The state/region
		 *     @type string $country   The country code
		 *     @type string $postcode  The postal code
		 * }
		 * @param \WC_Cart $cart The cart instance
		 */
		$origin_address = apply_filters( 'wc_avatax_checkout_origin_address', get_option( 'wc_avatax_origin_address' ), $cart );

		/**
		 * Filter the destination address at checkout.
		 *
		 * @since 1.1.0
		 * @param array $address {
		 *     The address values.
		 *
		 *     @type string $address_1 Address line 1
		 *     @type string $address_2 Address line 2
		 *     @type string $city      The city name
		 *     @type string $state     The state/region
		 *     @type string $country   The country code
		 *     @type string $postcode  The postal code
		 * }
		 * @param \WC_Cart $cart The cart instance
		 */
		$destination_address = apply_filters( 'wc_avatax_checkout_destination_address', array(
			'address_1' => WC()->customer->get_shipping_address(),
			'address_2' => WC()->customer->get_shipping_address_2(),
			'city'      => WC()->customer->get_shipping_city(),
			'state'     => WC()->customer->get_shipping_state(),
			'country'   => WC()->customer->get_shipping_country(),
			'postcode'  => WC()->customer->get_shipping_postcode(),
		), $cart );

		// temporary fix for local pickup support until upgrading the REST API / supporting multiple shipping addresses {BR 2017-02-25}
		$chosen_shipping_method = WC()->session->get( 'chosen_shipping_methods' );
		$chosen_shipping_method = explode( ':', $chosen_shipping_method[0] );

		if ( 'local_pickup' === $chosen_shipping_method[0] || 'local_pickup_plus' === $chosen_shipping_method[0] ) {
			$destination_address = $origin_address;
			$destination_address['address_2'] = '';
		}

		// Set the addresses
		$addresses = array(
			$this->origin_id      => $origin_address,
			$this->destination_id => $destination_address,
		);

		$lines = array();

		// Check for a pre-tax discount
		$discount = $cart->get_total_discount();

		// Set the cart contents
		foreach ( $cart->cart_contents as $cart_item_key => $values ) {

			$_product = $values['data'];

			if ( ! $_product->is_taxable() ) {
				continue;
			}

			// Set the sku if it exists. Otherwise, use the variation or product ID
			if ( $_product->get_sku() ) {
				$sku = $_product->get_sku();
			} else {
				$sku = $_product->get_id();
			}

			$lines[] = $this->prepare_line( array(
				'key'         => $cart_item_key,
				'sku'         => $sku,
				'tax_code'    => $this->prepare_product_tax_code( $_product ),
				'description' => $_product->get_title(),
				'quantity'    => $values['quantity'],
				'total'       => $values['line_total'],
				'discounted'  => ( ( $discount && $discount > 0 ) || $_product->is_on_sale() ),
			) );
		}

		// Set the fees
		foreach ( $cart->get_fees() as $fee ) {

			if ( ! $fee->taxable ) {
				continue;
			}

			$lines[] = $this->prepare_line( array(
				'key'         => $fee->id,
				'sku'         => $fee->id,
				'tax_code'    => get_option( 'wc_avatax_default_product_code', 'P0000000' ),
				'description' => $fee->name,
				'quantity'    => 1,
				'total'       => $fee->amount,
				'discounted'  => ( $discount && $discount > 0 ),
			) );
		}

		// Set the shipping
		if ( 0 < $cart->shipping_total ) {

			$lines[] = $this->prepare_line( array(
				'key'         => 'shipping',
				'sku'         => 'shipping',
				'description' => 'Shipping',
				'total'       => $cart->shipping_total,
				'discounted'  => ( $discount && $discount > 0 ),
			), 'shipping' );
		}

		// Almost ready to send lovingly off to AvaTax!
		$args = array(
			'customer'  => ( ! empty( $post_data['billing_email'] ) ) ? $post_data['billing_email'] : 'Guest',
			'addresses' => $addresses,
			'lines'     => $lines,
		);

		// Set the VAT if it exists
		if ( $vat = WC()->customer->vat_id ) {
			$args['vat'] = $vat;
		}

		// Set the exemption if it exists
		if ( $exemption = get_user_meta( get_current_user_id(), 'wc_avatax_tax_exemption', true ) ) {
			$args['exemption'] = $exemption;
		}

		$this->set_params( $args );
	}


	/**
	 * Get the calculated tax for a specific order.
	 *
	 * @since 1.0.0
	 * @param \WC_Order $order order object
	 * @param bool $commit Whether to commit the transaction to Avalara
	 */
	public function process_order( WC_Order $order, $commit ) {

		// Get the origin address
		// If tax has already been calculated for the order and we're sending the result to Avalara,
		// then continue with the origin address that was used at last calculation.
		if ( SV_WC_Order_Compatibility::get_meta( $order, '_wc_avatax_origin_address' ) && $commit ) {
			$origin_address = SV_WC_Order_Compatibility::get_meta( $order, '_wc_avatax_origin_address' );
		} else {
			$origin_address = get_option( 'wc_avatax_origin_address' );
		}

		/**
		 * Filter the origin address when processing an order.
		 *
		 * @since 1.1.0
		 * @param array $address {
		 *     The address values.
		 *
		 *     @type string $address_1 Address line 1
		 *     @type string $address_2 Address line 2
		 *     @type string $city      The city name
		 *     @type string $state     The state/region
		 *     @type string $country   The country code
		 *     @type string $postcode  The postal code
		 * }
		 * @param \WC_Order $order The order instance
		 */
		$origin_address = apply_filters( 'wc_avatax_order_origin_address', $origin_address, $order );

		$destination_address = $order->get_address( 'shipping' );

		// if no shipping address was set, use the billing address
		if ( empty( $destination_address['country'] ) ) {
			$destination_address = $order->get_address( 'billing' );
		}

		/**
		 * Filter the destination address when processing an order.
		 *
		 * @since 1.1.0
		 * @param array $address {
		 *     The address values.
		 *
		 *     @type string $address_1 Address line 1
		 *     @type string $address_2 Address line 2
		 *     @type string $city      The city name
		 *     @type string $state     The state/region
		 *     @type string $country   The country code
		 *     @type string $postcode  The postal code
		 * }
		 * @param \WC_Order $order The order instance
		 */
		$destination_address = apply_filters( 'wc_avatax_order_destination_address', $destination_address, $order );

		// temporary fix for local pickup support until upgrading the REST API / supporting multiple shipping addresses {BR 2017-02-25}
		foreach ( $order->get_shipping_methods() as $method ) {

			$shipping = explode( ':', $method['method_id'] );

			if ( 'local_pickup' === $shipping[0] || 'local_pickup_plus' === $shipping[0] ) {
				$destination_address = $origin_address;
				break;
			}
		}

		// Set the addresses
		$addresses = array(
			$this->origin_id      => $origin_address,
			$this->destination_id => $destination_address,
		);

		$lines = array();

		// Check for a pre-tax discount
		$discount = $order->get_total_discount( true );

		// Add the order items as lines
		foreach ( $order->get_items() as $item_id => $item ) {

			$_product = $order->get_product_from_item( $item );

			if ( ! $_product->is_taxable() ) {
				continue;
			}

			// Set the sku if it exists. Otherwise, use the variation or product ID
			if ( $_product->get_sku() ) {
				$sku = $_product->get_sku();
			} else {
				$sku = $_product->get_id();
			}

			$lines[] = $this->prepare_line( array(
				'key'         => $item_id,
				'sku'         => $sku,
				'tax_code'    => $this->prepare_product_tax_code( $_product ),
				'description' => $_product->get_title(),
				'quantity'    => $item['qty'],
				'total'       => $item['line_total'],
				'discounted'  => ( ( $discount && $discount > 0 ) || $_product->is_on_sale() ),
			) );
		}

		// Add any fees
		foreach ( $order->get_fees() as $fee_id => $fee ) {

			$lines[] = $this->prepare_line( array(
				'key'         => $fee_id,
				'sku'         => strtolower( $fee['name'] ),
				'tax_code'    => get_option( 'wc_avatax_default_product_code', 'P0000000' ),
				'description' => $fee['name'],
				'quantity'    => 1,
				'total'       => $fee['line_total'],
				'discounted'  => ( $discount && $discount > 0 ),
			) );
		}

		// Add any shipping costs
		foreach ( $order->get_shipping_methods() as $item_id => $item ) {

			$lines[] = $this->prepare_line( array(
				'key'         => 'shipping_' . $item_id,
				'sku'         => $item['method_id'],
				'description' => $item['name'],
				'total'       => $item['cost'],
				'discounted'  => ( $discount && $discount > 0 ),
			), 'shipping' );
		}

		// Almost ready to send lovingly off to AvaTax!
		$args = array(
			'id'           => SV_WC_Order_Compatibility::get_prop( $order, 'order_key' ),
			'order_number' => $order->get_order_number(),
			'customer'     => SV_WC_Order_Compatibility::get_prop( $order, 'billing_email' ),
			'addresses'    => $addresses,
			'lines'        => $lines,
			'date'         => ( $date_created = SV_WC_Order_Compatibility::get_date_created( $order ) ) ? $date_created->date( 'Y-m-d' ) : '',
			'type'         => ( $commit ) ? 'payment' : 'estimate',
			'currency'     => SV_WC_Order_Compatibility::get_prop( $order, 'currency', 'view' ),
			'commit'       => $this->commit_calculations(),
		);

		// Set the VAT if it exists
		if ( $vat = SV_WC_Order_Compatibility::get_meta( $order, '_billing_wc_avatax_vat_id' ) ) {
			$args['vat'] = $vat;
		}

		// Set the exemption if it exists
		if ( $exemption = SV_WC_Order_Compatibility::get_meta( $order, '_wc_avatax_exemption' ) ) {
			$args['exemption'] = $exemption;
		} else {
			$args['exemption'] = get_user_meta( $order->get_user_id(), 'wc_avatax_tax_exemption', true );
		}

		$this->set_params( $args );
	}


	/**
	 * Get the calculated tax for a refunded order.
	 *
	 * @since 1.0.0
	 * @param \WC_Order_Refund $refund order refund object
	 */
	public function process_refund( WC_Order_Refund $refund ) {

		// Get the original order
		$order = wc_get_order( SV_WC_Order_Compatibility::get_prop( $refund, 'parent_id' ) );

		if ( ! $order ) {
			return false;
		}

		if ( SV_WC_Order_Compatibility::get_meta( $order, '_wc_avatax_destination_address' ) ) {
			$destination_address = SV_WC_Order_Compatibility::get_meta( $order, '_wc_avatax_destination_address' );
		} else {
			$destination_address = $order->get_address( 'shipping' );
		}

		// if no shipping address was set, use the billing address
		if ( empty( $destination_address['country'] ) ) {
			$destination_address = $order->get_address( 'billing' );
		}

		// Set the origin and destination addresses
		$addresses = array(
			$this->origin_id      => SV_WC_Order_Compatibility::get_meta( $order, '_wc_avatax_origin_address' ),
			$this->destination_id => $destination_address,
		);

		$lines = array();

		// Add the order items and fees as lines
		foreach ( $refund->get_items( array( 'line_item', 'fee' ) ) as $item_id => $item ) {

			// If this item has no refund amount, bail
			if ( 0 == $item['line_total'] ) {
				continue;
			}

			$quantity = ( 0 == $item['qty'] ) ? 1 : $item['qty'];

			$refunded_item_id = ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) ? $item->get_meta( '_refunded_item_id' ) : $item['refunded_item_id'];

			$lines[] = $this->prepare_line( array(
				'key'         => $item_id,
				'sku'         => sanitize_title( $item['name'] ),
				'tax_code'    => wc_get_order_item_meta( $refunded_item_id, '_wc_avatax_code', true ),
				'description' => $item['name'],
				'quantity'    => abs( $quantity ),                // Refund item quantities should always be positive
				'total'       => abs( $item['line_total'] ) * -1, // Refund item totals should always be negative
			) );
		}

		// Add any shipping refunds
		foreach ( $refund->get_shipping_methods() as $item_id => $item ) {

			// If this shipping method has no refund amount, bail
			if ( 0 == $item['cost'] ) {
				continue;
			}

			$refunded_item_id = ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) ? $item->get_meta( '_refunded_item_id' ) : $item['refunded_item_id'];

			$lines[] = $this->prepare_line( array(
				'key'         => 'shipping_' . $item_id,
				'sku'         => $item['method_id'],
				'tax_code'    => wc_get_order_item_meta( $refunded_item_id, '_wc_avatax_code', true ),
				'description' => $item['name'],
				'total'       => abs( $item['cost'] ) * -1,
			), 'shipping' );
		}

		if ( empty( $lines ) ) {
			throw new SV_WC_API_Exception( 'Refund amounts must be set per line item. You need to add the refund manually from your Avalara Control Panel.' );
		}

		// Make the original order ID unique
		$id = SV_WC_Order_Compatibility::get_prop( $order, 'order_key' ) . '-' . SV_WC_Order_Compatibility::get_prop( $refund, 'id' );

		// Almost ready to send lovingly off to AvaTax!
		$args = array(
			'id'           => $id,
			'order_number' => $order->get_order_number(),
			'customer'     => SV_WC_Order_Compatibility::get_prop( $order, 'billing_email' ),
			'addresses'    => $addresses,
			'lines'        => $lines,
			'type'         => 'refund',
			'tax_date'     => SV_WC_Order_Compatibility::get_meta( $order, '_wc_avatax_tax_date' ),
			'reason'       => ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) ? $refund->get_reason() : $refund->get_refund_reason(),
			'commit'       => $this->commit_calculations(),
		);

		// Set the VAT if it exists
		if ( $vat = SV_WC_Order_Compatibility::get_meta( $order, '_billing_wc_avatax_vat_id' ) ) {
			$args['vat'] = $vat;
		}

		if ( $exemption = SV_WC_Order_Compatibility::get_meta( $order, '_wc_avatax_exemption' ) ) {
			$args['exemption'] = $exemption;
		}

		$this->set_params( $args );
	}


	/**
	 * Set the calculation request params.
	 *
	 * @since 1.0.0
	 * @param array $args {
	 *     The AvaTax API parameters.
	 *
	 *     @type int    $id           The unique transaction ID.
	 *     @type string $order_number The order number for reference
	 *     @type string $customer     The unique customer identifier.
	 *     @type array  $addresses    The origin and destination addresses. @see `WC_AvaTax_API::prepare_address()` for formatting.
	 *     @type array  $lines        The line items used for calculation. @see `WC_AvaTax_API::prepare_line()` for formatting.
	 *     @type string $date         The document creation date. Format: YYYY-MM-DD. Default: the current date.
	 *     @type string $tax_date     The effective tax date. Format: YYYY-MM-DD.
	 *     @type string $type         The type of transaction requested of AvaTax. Accepts `checkout`, `payment`, or `refund`. Default: `checkout`.
	 *     @type string $currency     The calculation currency code. Default: the shop currency code.
	 *     @type string $vat          The customer's VAT ID.
	 *     @type bool   $exemption    Whether the transaction has tax exemption.
	 *     @type bool   $commit       Whether to commit this calculation as a finalized transaction. Default: `false`.
	 * }
	 */
	public function set_params( $args ) {

		$current_date = date( 'Y-m-d', current_time( 'timestamp' ) );

		$defaults = array(
			'id'           => '',
			'order_number' => 0,
			'customer'     => '',
			'addresses'    => array(),
			'lines'        => array(),
			'date'         => date( 'Y-m-d', current_time( 'timestamp' ) ),
			'tax_date'     => '',
			'type'         => 'checkout',
			'currency'     => get_woocommerce_currency(),
			'vat'          => false,
			'exemption'    => false,
			'commit'       => false,
		);

		$args = wp_parse_args( $args, $defaults );

		// Set the base request params
		$params = array(
			'CustomerCode' => SV_WC_Helper::str_truncate( $args['customer'], 50, '' ),
			'CurrencyCode' => SV_WC_Helper::str_truncate( $args['currency'], 3, '' ),
			'DocDate'      => $args['date'],
			'Lines'        => $args['lines'],
		);

		// Format and set the addresses
		foreach ( $args['addresses'] as $address_id => $address ) {

			/**
			 * Filter the the each address before calling the API.
			 *
			 * @since 1.1.0
			 * @param $address array the address
			 */
			$address = apply_filters( 'wc_avatax_tax_' . $address_id . '_address', $address );

			$params['Addresses'][] = $this->prepare_address( $address, $address_id );
		}

		if ( $company_code = get_option( 'wc_avatax_company_code' ) ) {
			$params['CompanyCode'] = SV_WC_Helper::str_truncate( $company_code, 25 );
		}

		if ( $args['id'] ) {
			$params['DocCode'] = SV_WC_Helper::str_truncate( $args['id'], 50, '' );
		}

		if ( $args['order_number'] ) {
			$params['PurchaseOrderNo'] = SV_WC_Helper::str_truncate( $args['order_number'], 50, '' );
		}

		// Set a tax date override if required
		if ( $args['tax_date'] && $args['tax_date'] !== $args['date'] ) {

			$params['TaxOverride'] = array(
				'TaxOverrideType' => 'TaxDate',
				'TaxDate'         => $args['tax_date'],
				'Reason'          => isset( $args['reason'] ) ? $args['reason'] : '',
			);
		}

		// Handle the document type and overrides differently for checkout calculations, payments, and refunds
		switch ( $args['type'] ) {

			case 'payment':
				$params['DocType'] = 'SalesInvoice';
			break;

			case 'refund':

				$params['DocType'] = 'ReturnInvoice';

				if ( isset( $params['TaxOverride'] ) ) {
					$params['TaxOverride']['Reason'] = ! empty( $args['reason'] ) ? $args['reason'] : 'Refund';
				}

			break;

			default:
				$params['DocType'] = 'SalesOrder';
		}

		// Set the VAT if it exists
		if ( $vat = $args['vat'] ) {
			$params['BusinessIdentificationNo'] = SV_WC_Helper::str_truncate( $vat, 25, '' );
		}

		// Set the exemption if it exists
		if ( $args['exemption'] ) {
			$params['CustomerUsageType'] = $args['exemption'];
		}

		// Should this be committed?
		$params['Commit'] = $args['commit'];

		$this->path   = 'tax/get';
		$this->params = $params;
	}


	/**
	 * Prepare an order line item for the AvaTax API.
	 *
	 * @since 1.0.0
	 * @param array $item {
	 *     The line item details.
	 *
	 *     @type string $key         The unique line identifier.
	 *     @type string $sku         The unique item identifier like the product SKU or ID.
	 *     @type string $tax_code    The item tax code.
	 *     @type string $description The item description or product title.
	 *     @type int    $quantity    The item quantity.
	 *     @type float  $total       The line extended total price.
	 *     @type bool   $discounted  Whether the item has a pre-tax discount.
	 * }
	 * @param string $type Optional. The line item type. Default: 'product'. Accepts: 'product' & 'shipping'.
	 * @return array $line The formatted line.
	 */
	protected function prepare_line( $item, $type = 'product' ) {

		$defaults = array(
			'key'         => '',
			'sku'         => '',
			'tax_code'    => '',
			'description' => '',
			'quantity'    => 1,
			'total'       => 0,
			'discounted'  => false,
			'origin'      => $this->origin_id,
			'destination' => $this->destination_id,
		);

		$item = wp_parse_args( $item, $defaults );

		$line = array();

		// If dealing with a WooCommerce product (default)
		if ( 'product' === $type ) {
			$line = array(
				'LineNo'          => $item['key'],
				'ItemCode'        => SV_WC_Helper::str_truncate( $item['sku'], 50, '' ),
				'TaxCode'         => SV_WC_Helper::str_truncate( $item['tax_code'], 25, '' ),
				'Description'     => SV_WC_Helper::str_truncate( $item['description'], 255, '' ),
				'Qty'             => (int) $item['quantity'],
			);

		// Or dealing with a shipping total
		} else if ( 'shipping' === $type ) {
			$line = array(
				'LineNo'          => $item['key'],
				'ItemCode'        => SV_WC_Helper::str_truncate( $item['sku'], 50, '' ),
				'TaxCode'         => get_option( 'wc_avatax_shipping_code', 'FR' ),
				'Description'     => SV_WC_Helper::str_truncate( $item['description'], 255, '' ),
				'Qty'             => 1,
			);
		}

		// Set the extended price (quantity * base price)
		$line['Amount'] = (float) $item['total'];

		$line['Discounted'] = (bool) $item['discounted'];

		// Set the addresses for jurisdiction calculation
		$line['OriginCode']      = $item['origin'];
		$line['DestinationCode'] = $item['destination'];

		return $line;
	}


	/**
	 * Get a product's tax code with fallbacks.
	 *
	 * @since 1.0.0
	 * @param WC_Product $product The product object.
	 * @return string $tax_code The tax code.
	 */
	protected function prepare_product_tax_code( WC_Product $product ) {

		$tax_code = '';

		// Check for a product-specific tax code
		if ( SV_WC_Product_Compatibility::get_meta( $product, '_wc_avatax_code' ) ) {

			$tax_code = SV_WC_Product_Compatibility::get_meta( $product, '_wc_avatax_code' );

		// If a variation, check for the parent product's tax code
		} elseif ( $product->is_type( 'variation' ) ) {

			$product = wc_get_product( SV_WC_Product_Compatibility::get_prop( $product, 'parent_id' ) );

			if ( SV_WC_Product_Compatibility::get_meta( $product, '_wc_avatax_code' ) ) {
				$tax_code = SV_WC_Product_Compatibility::get_meta( $product, '_wc_avatax_code' );
			}

		}

		// If none was found yet, check the product's category
		if ( ! $tax_code ) {

			$categories = get_the_terms( $product->get_id(), 'product_cat' );

			if ( is_array( $categories ) ) {

				foreach ( $categories as $category ) {

					if ( $category_tax_code = get_woocommerce_term_meta( $category->term_id, 'wc_avatax_tax_code', true ) ) {
						$tax_code = $category_tax_code;
						break;
					}
				}
			}
		}

		// Use the default tax code as a fallback
		if ( ! $tax_code ) {
			$tax_code = get_option( 'wc_avatax_default_product_code', 'P0000000' );
		}

		/**
		 * Filter the product tax code.
		 *
		 * Uses the product category's tax code (if any) or the default setting as a fallback.
		 *
		 * @since 1.0.0
		 * @param string $tax_code The tax code.
		 * @param WC_Product $product The product object.
		 */
		return apply_filters( 'wc_avatax_get_product_tax_code', $tax_code, $product );
	}


	/**
	 * Determine if new tax documents should be committed on calculation.
	 *
	 * @since 1.0.0
	 * @return bool $commit Whether new tax documents should be committed on calculation.
	 */
	protected function commit_calculations() {

		/**
		 * Filter whether new tax documents should be committed on calculation.
		 *
		 * @since 1.0.0
		 * @param bool $commit Whether new tax documents should be committed on calculation.
		 */
		return (bool) apply_filters( 'wc_avatax_commit_calculations', ( 'yes' === get_option( 'wc_avatax_commit' ) ) );
	}
}
