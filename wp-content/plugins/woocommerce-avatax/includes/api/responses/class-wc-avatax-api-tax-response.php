<?php
/**
 * Define the WC_AvaTax_API_Tax_Response class
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
 * The AvaTax API tax response class.
 *
 * @since 1.0.0
 */
class WC_AvaTax_API_Tax_Response extends WC_AvaTax_API_Response {


	/**
	 * Get the calculated line items.
	 *
	 * @since 1.0.0
	 * @return array The calculated line items
	 */
	public function get_lines() {

		$lines = array();

		foreach ( $this->TaxLines as $line ) {

			$lines[] = array(
				'id'    => $line->LineNo,
				'total' => $line->Tax,
				'code'  => $line->TaxCode,
				'rate'  => ( 0 === $line->Taxable ) ? 0 : $line->Rate,
			);
		}

		return $lines;
	}


	/**
	 * Get the origin address.
	 *
	 * @since 1.0.0
	 * @return array The origin address
	 */
	public function get_origin_address() {

		// Get the origin address
		foreach ( $this->TaxAddresses as $address ) {

			if ( 'origin' === $address->AddressCode ) {

				// Map the API response to their proper keys
				$address = array(
					'address_1' => $address->Address,
					'city'      => $address->City,
					'state'     => $address->Region,
					'country'   => $address->Country,
					'postcode'  => $address->PostalCode,
				);

				break;
			}
		}

		return $address;
	}


	/**
	 * Get the destination address.
	 *
	 * @since 1.1.0
	 * @return array The destination address
	 */
	public function get_destination_address() {

		// Get the destination address
		foreach ( $this->TaxAddresses as $address ) {

			if ( 'destination' === $address->AddressCode ) {

				// Map the API response to their proper keys
				$address = array(
					'address_1' => $address->Address,
					'city'      => $address->City,
					'state'     => $address->Region,
					'country'   => $address->Country,
					'postcode'  => $address->PostalCode,
				);

				break;
			}
		}

		return $address;
	}


	/**
	 * Get the total tax amount.
	 *
	 * @since 1.0.0
	 * @return float The total tax calculated.
	 */
	public function get_total_tax() {

		return $this->TotalTax;
	}


	/**
	 * Get the effective tax date.
	 *
	 * @since 1.0.0
	 * @return string The effective tax date in YYYY-MM-DD format.
	 */
	public function get_tax_date() {

		return $this->TaxDate;
	}
}
