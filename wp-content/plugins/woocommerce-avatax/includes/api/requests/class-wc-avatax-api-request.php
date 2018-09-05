<?php
/**
 * Define the WC_AvaTax_API_Request class
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
 * The AvaTax API request class.
 *
 * @since 1.0.0
 */
class WC_AvaTax_API_Request extends SV_WC_API_JSON_Request {


	/**
	 * Construct the AvaTax request object.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->method = 'GET';
	}


	/**
	 * Void a document in Avalara based on a WooCommerce order.
	 *
	 * @since 1.0.0
	 * @param int $order_id The associated order ID.
	 */
	public function void_order( $order_id ) {

		$doc_code = get_post_meta( $order_id, '_order_key', true );

		// If the order has no key, bail
		if ( ! $doc_code ) {
			throw new SV_WC_API_Exception( __( 'Invalid order key.', 'woocommerce-avatax' ) );
		}

		$this->void_document( $doc_code );
	}


	/**
	 * Void a document in Avalara based on a WooCommerce refund.
	 *
	 * @since 1.0.0
	 * @param \WC_Order_Refund $refund order refund object
	 */
	public function void_refund( WC_Order_Refund $refund ) {

		$order_key = get_post_meta( SV_WC_Order_Compatibility::get_prop( $refund, 'parent_id' ), '_order_key', true );

		// If the order has no key, bail
		if ( ! $order_key ) {
			throw new SV_WC_API_Exception( __( 'Invalid order key.', 'woocommerce-avatax' ) );
		}

		$doc_code = $order_key . '-' . SV_WC_Order_Compatibility::get_prop( $refund, 'id' );

		$this->void_document( $doc_code, 'refund' );
	}


	/**
	 * Void a document in Avalara.
	 *
	 * @since 1.0.0
	 * @param int $doc_code The document code. Usually a WooCommerce order_key
	 * @param string $origin Optional. Whether the document came from an order or refund
	 */
	public function void_document( $doc_code, $origin = 'order' ) {

		$this->method = 'POST';
		$this->path   = 'tax/cancel';
		$this->params = array(
			'CancelCode'  => 'DocVoided',
			'CompanyCode' => SV_WC_Helper::str_truncate( get_option( 'wc_avatax_company_code' ), 25, '' ),
			'DocCode'     => SV_WC_Helper::str_truncate( $doc_code, 50, '' ),
			'DocType'     => 'SalesInvoice',
		);

		if ( 'refund' === $origin ) {
			$this->params['DocType'] = 'ReturnInvoice';
		} else {
			$this->params['DocType'] = 'SalesInvoice';
		}
	}


	/**
	 * Test the API credentials.
	 *
	 * @since 1.0.0
	 */
	public function test() {

		$path = 'tax/';

		// Add some coordinates to complete the request.
		$path .= '35.0820877,-106.9566669/get';

		$this->path = add_query_arg( 'saleamount', 0, $path );
	}


	/**
	 * Prepare an address for the AvaTax API.
	 *
	 * Instead of keeping the input array keys 1-to-1 with the AvaTax API param keys, we map them to
	 * WooCommerce's standard address keys to make things easier on the WooCommerce side and avoid
	 * extra changes if the AvaTax API changes.
	 *
	 * @since 1.0.0
	 * @param array $address The address details. @see `WC_AvaTax_API::validate_address()` for formatting.
	 * @param string $id Optional. The unique address ID.
	 * @return array The formatted address.
	 */
	protected function prepare_address( $address, $id = '' ) {

		$defaults = array(
			'address_1' => '',
			'address_2' => '',
			'city'      => '',
			'state'     => '',
			'country'   => '',
			'postcode'  => '',
		);

		$address = wp_parse_args( (array) $address, $defaults );

		$address = array(
			'Line1'       => SV_WC_Helper::str_truncate( $address['address_1'], 50 ),
			'Line2'       => SV_WC_Helper::str_truncate( $address['address_2'], 50 ),
			'City'        => SV_WC_Helper::str_truncate( $address['city'], 50 ),
			'Region'      => SV_WC_Helper::str_truncate( $address['state'], 3, '' ),
			'Country'     => SV_WC_Helper::str_truncate( $address['country'], 2, '' ),
			'PostalCode'  => SV_WC_Helper::str_truncate( $address['postcode'], 11, '' ),
		);

		// Add the unique ID if set
		if ( $id ) {
			$address['AddressCode'] = $id;
		}

		return $address;
	}


}
