<?php
/**
 * Define the WC_AvaTax_API_Response class
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
class WC_AvaTax_API_Response extends SV_WC_API_JSON_Response {


	/**
	 * Get the details of the voided transaction.
	 *
	 * @since 1.0.0
	 * @return array The voided transaction data
	 */
	public function get_void_data() {

		$data = $this->CancelTaxResult;

		$data = array(
			'transaction_id' => $data->TransactionId,
			'document_id'    => $data->DocId,
		);

		return $data;
	}
}
