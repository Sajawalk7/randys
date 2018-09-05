<?php
/**
 * WooCommerce AvaTax
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
 * @package   AvaTax\Includes
 * @author    SkyVerge
 * @copyright Copyright (c) 2016-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * AvaTax Import/Export Handler
 *
 * Adds support for:
 *
 * + Customer / Order CSV Export
 * + Customer / Order XML Export
 *
 * @since 1.0
 */
class WC_AvaTax_Import_Export_Handler {

	/**
	 * Setup class
	 *
	 * @since 1.3.0
	 */
	public function __construct() {

		// Customer / Order CSV Export column headers/data + line item data
		add_filter( 'wc_customer_order_csv_export_order_headers', array( $this, 'add_vat_id_to_csv_export_column_headers' ), 15 );
		add_filter( 'wc_customer_order_csv_export_order_row',     array( $this, 'add_vat_id_to_csv_export_column_data' ), 10, 3 );

		// Customer / Order CSV Export customer column headers / data
		add_filter( 'wc_customer_order_csv_export_customer_headers', array( $this, 'add_customer_tax_info_csv_export_column_headers' ), 15 );
		add_filter( 'wc_customer_order_csv_export_customer_row',     array( $this, 'add_customer_tax_info_to_csv_export_data' ), 10, 3 );

		// Customer / Order XML Export
		if ( function_exists( 'wc_customer_order_xml_export_suite' ) && version_compare( wc_customer_order_xml_export_suite()->get_version(), '2.0.0', '<' ) ) {
			add_filter( 'wc_customer_order_xml_export_suite_order_export_order_list_format', array( $this, 'add_xml_export_order_vat_id' ), 10, 2 );
		} else {
			add_filter( 'wc_customer_order_xml_export_suite_order_data', array( $this, 'add_xml_export_order_vat_id' ), 10, 2 );
		}

		// Customer / Order XML Export customer data
		add_filter( 'wc_customer_order_xml_export_suite_customer_export_data', array( $this, 'add_xml_export_customer_tax_info' ), 10, 3 );
	}


	/** Customer/Order CSV Export compat **************************************/


	/**
	 * Adds support for Customer/Order CSV Export by adding a
	 * `vat_id` column header.
	 *
	 * @since 1.3.0
	 * @param array $headers existing array of header key/names for the CSV export
	 * @return array
	 */
	public function add_vat_id_to_csv_export_column_headers( $headers ) {

		$new_headers = array( 'vat_id' => 'vat_id' );

		if ( isset( $headers['billing_company'] ) ) {
			$headers = SV_WC_Helper::array_insert_after( $headers, 'billing_company', $new_headers );
		} else {
			$headers = array_merge( $headers, $new_headers );
		}

		return $headers;
	}


	/**
	 * Adds support for Customer/Order CSV Export by adding data for the
	 * `vat_id` column header.
	 *
	 * @since 1.3.0
	 * @param array $order_data generated order data matching the column keys in the header
	 * @param WC_Order $order order being exported
	 * @param \WC_Customer_Order_CSV_Export_Generator $csv_generator instance
	 * @return array
	 */
	public function add_vat_id_to_csv_export_column_data( $order_data, $order, $csv_generator ) {

		$vat_id = array( 'vat_id' => SV_WC_Order_Compatibility::get_meta( $order, '_billing_wc_avatax_vat_id' ) );

		$new_order_data = array();

		if ( $this->is_one_row_per_item( $csv_generator ) ) {

			foreach ( $order_data as $data ) {
				$new_order_data[] = array_merge( (array) $data, $vat_id );
			}

		} else {

			$new_order_data = array_merge( $order_data, $vat_id );
		}

		return $new_order_data;
	}


	/**
	 * Determine if the CSV Export format/format definition are set to export
	 * one row per item.
	 *
	 * @since 1.3.0
	 * @param \WC_Customer_Order_CSV_Export_Generator $csv_generator instance
	 * @return bool
	 */
	private function is_one_row_per_item( $csv_generator ) {

		// sanity check - bail if CSV Export is not active, or if the provided parameter is not as expected
		if ( ! function_exists( 'wc_customer_order_csv_export' ) || ! $csv_generator instanceof WC_Customer_Order_CSV_Export_Generator ) {
			return false;
		}

		$one_row_per_item = false;

		// determine if the selected format is "one row per item"
		if ( version_compare( wc_customer_order_csv_export()->get_version(), '4.0.0', '<' ) ) {

			$one_row_per_item = ( 'default_one_row_per_item' === $csv_generator->order_format || 'legacy_one_row_per_item' === $csv_generator->order_format );

		// v4.0.0 - 4.0.2
		} elseif ( ! isset( $csv_generator->format_definition ) ) {

			// get the CSV Export format definition
			$format_definition = wc_customer_order_csv_export()->get_formats_instance()->get_format( $csv_generator->export_type, $csv_generator->export_format );

			$one_row_per_item = isset( $format_definition['row_type'] ) && 'item' === $format_definition['row_type'];

		// v4.0.3+
		} else {

			$one_row_per_item = 'item' === $csv_generator->format_definition['row_type'];
		}

		return $one_row_per_item;
	}


	/** Customer/Order CSV Export - customers ***********************************/


	/**
	 * Adds headers for VAT ID and tax exemption status to customer exports.
	 *
	 * @since 1.3.0
	 * @param array $headers column headers for the CSV file
	 * @return array updated headers
	 */
	public function add_customer_tax_info_csv_export_column_headers( $headers ) {

		$new_headers = array(
			'vat_id'        => 'vat_id',
			'tax_exemption' => 'tax_exemption',
		);

		if ( isset( $headers['billing_company'] ) ) {
			$headers = SV_WC_Helper::array_insert_after( $headers, 'billing_company', $new_headers );
		} else {
			$headers = array_merge( $headers, $new_headers );
		}

		return $headers;
	}


	/**
	 * Adds VAT ID and tax exemption status to customer exports.
	 *
	 * @since 1.3.0
	 * @param array $customer_data the customer data for the CSV file
	 * @param \WP_User $user the user object for the export
	 * @param int $order_id order ID for the customer, if available
	 * @return array updated customer data
	 */
	public function add_customer_tax_info_to_csv_export_data( $customer_data, $user, $order_id ) {

		// get VAT ID for guest users
		if ( is_numeric( $order_id ) ) {
			$order  = wc_get_order( $order_id );
			$vat_id = SV_WC_Order_Compatibility::get_meta( $order, '_billing_wc_avatax_vat_id' );
		}

		// get VAT ID for registered users
		else {
			$vat_id = isset( $user->billing_wc_avatax_vat_id ) ? $user->billing_wc_avatax_vat_id : '';
		}

		$new_data = array(
			'vat_id'        => $vat_id,
			'tax_exemption' => isset( $user->wc_avatax_tax_exemption ) ? $user->wc_avatax_tax_exemption : '',
		);

		return array_merge( $customer_data, $new_data );
	}


	/** Customer/Order XML Export compat **************************************/


	/**
	 * Add a VATId element to the order XML export file.
	 *
	 * @since 1.3.0
	 * @param array $data order data
	 * @param \WC_Order $order
	 * @return array
	 */
	public function add_xml_export_order_vat_id( $data, $order ) {

		// sanity check
		if ( ! is_array( $data ) || ! $order instanceof WC_Order ) {
			return $data;
		}

		$new_data = array(
			'VATId' => SV_WC_Order_Compatibility::get_meta( $order, '_billing_wc_avatax_vat_id' ),
		);

		if ( isset( $data['BillingPhone'] ) ) {
			$data = SV_WC_Helper::array_insert_after( $data, 'BillingPhone', $new_data );
		} else {
			$data = array_merge( $data, $new_data );
		}

		return $data;
	}


	/** Customer/Order XML Export - customers **************************************/


	/**
	 * Adds VATId and TaxExemption information to customer XML export file.
	 *
	 * @since 1.3.0
	 * @param array $customer_data customer data in the format for array_to_xml()
	 * @param \WP_User $user user object
	 * @param int|null $order_id order ID for the customer, if available
	 * @return array updated customer data
	 */
	public function add_xml_export_customer_tax_info( $customer_data, $user, $order_id ) {

		// get VAT ID for guest users
		if ( is_numeric( $order_id ) ) {
			$order  = wc_get_order( $order_id );
			$vat_id = SV_WC_Order_Compatibility::get_meta( $order, '_billing_wc_avatax_vat_id' );
		}

		// get VAT ID for registered users
		else {
			$vat_id = isset( $user->billing_wc_avatax_vat_id ) ? $user->billing_wc_avatax_vat_id : '';
		}

		$new_data = array(
			'VATId'        => $vat_id,
			'TaxExemption' => isset( $user->wc_avatax_tax_exemption ) ? $user->wc_avatax_tax_exemption : '',
		);

		if ( isset( $customer_data['BillingCompany'] ) ) {
			$customer_data = SV_WC_Helper::array_insert_after( $customer_data, 'BillingCompany', $new_data );
		} else {
			$customer_data = array_merge( $customer_data, $new_data );
		}

		return $customer_data;
	}


}
