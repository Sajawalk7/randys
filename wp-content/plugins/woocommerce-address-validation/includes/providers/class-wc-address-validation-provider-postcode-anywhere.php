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
 * @package     WC-Address-Validation/Provider/Postcode-Anywhere
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * PostcodeAnywhere.co.uk Provider Class
 *
 * Extends abstract provider class to provide postcode lookup via PostCodeAnywhere.co.uk API
 *
 * @link http://www.postcodeanywhere.co.uk/support/webservices/PostcodeAnywhere/Interactive/FindByPostcode/v1/default.aspx
 * @since 1.0
 */
class WC_Address_Validation_Provider_Postcode_Anywhere extends WC_Address_Validation_Provider {


	/* API Endpoint */
	const API_ENDPOINT = 'http://services.postcodeanywhere.co.uk/PostcodeAnywhere/Interactive/RetrieveByParts/v1.00/json3.ws?';


	/**
	 * Setup id/title/description and declare country / feature support
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->id = 'postcode_anywhere';

		$this->title = __( 'PCA Predict (formerly Postcode Anywhere)', 'woocommerce-address-validation' );

		$this->countries = array( 'GB', 'GG', 'JE', 'IM' );

		$this->supports = array(
			'postcode_lookup',
		);

		// setup form fields
		$this->init_form_fields();

		// load settings
		$this->init_settings();

		$this->api_key      = $this->settings['api_key'];
		$this->api_username = $this->settings['api_username'];

		// Save settings
		add_action( 'wc_address_validation_update_provider_options_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Lookup postcode using API
	 *
	 * @since 1.0
	 * @param string $postcode
	 * @param string $house_number Optional. Used by Postcode.nl.
	 * @return array locations found
	 */
	public function lookup_postcode( $postcode, $house_number = '' ) {

		$locations = array();

		// set key and postcode GET args
		$args = array(
			'Key'      => $this->api_key,
			'Postcode' => urlencode( $postcode )
		);

		// add username if available
		if ( $this->api_username ) {
			$args[ 'UserName'] = $this->api_username;
		}

		// send GET request
		$response = wp_safe_remote_get( add_query_arg( $args, self::API_ENDPOINT ) );

		// check for network timeout, etc
		if ( is_wp_error( $response ) || ( ! isset( $response['body'] ) ) ) {
			$locations = array( 'value' => 'none', 'name' => __( 'No addresses found, please check your postcode and try again.', 'woocommerce-address-validation' ) );
		} else {

			// decode response body
			$response = json_decode( $response['body'] );

			// setup locations if an error was not returned
			if ( isset( $response->Items ) && is_array( $response->Items ) && ! isset( $response->Items[0]->Error ) ) {

				foreach( $response->Items as $item_num => $item ) {

					$locations[ $item_num ] = array(
						'value'     => "location-{$item_num}",
						'company'   => $item->Company,
						'address_1' => $item->Line1,
						'address_2' => $item->Line2,
						'address_3' => $item->Line3,
						'city'      => $item->PostTown,
						'postcode'  => $item->Postcode,
						'state'     => $item->County,
						'name'      => "{$item->Company} {$item->Line1} {$item->Line2} {$item->Line3} {$item->PostTown}",
					);
				}

			} else {

				/**
				 * Change the message displayed when a postcode lookup returns no addresses
				 *
				 * @since 1.0.4
				 * @param string $message the message to display
				 * @param string $postcode the postcode the user entered
				 */
				$locations = array( 'value' => 'none', 'name' => apply_filters( 'wc_address_validation_postcode_lookup_no_address_found_message', __( 'No addresses found, please check your postcode and try again.', 'woocommerce-address-validation' ), $postcode ) );
			}
		}

		if ( 'yes' == get_option( 'wc_address_validation_debug_mode' ) ) {
			wc_address_validation()->log( print_r( $response, true ) );
		}

		return $locations;
	}


	/**
	 * Check if provider is configured correctly
	 *
	 * @since 1.0
	 * @return bool true if configured, false otherwise
	 */
	public function is_configured() {

		return $this->api_key ? true : false;
	}


	/**
	 * Init settings
	 *
	 * @since 1.0
	 */
	public function init_form_fields() {

		$this->form_fields = array(

			'api_key'  => array(
				'title'    => __( 'API Key', 'woocommerce-address-validation' ),
				'type'     => 'text',
				'description' => __( 'Enter your API Key from the Postcode Anywhere website.', 'woocommerce-address-validation' ),
				'default'  => '',
			),

			'api_username' => array(
				'title'    => __( 'API Username', 'woocommerce' ),
				'type'     => 'text',
				'description' => __( 'Enter your username associated with your Royal Mail account. This is not required, so leave blank if you do not have one.', 'woocommerce-address-validation' ),
				'default'  => ''
			),
		);
	}


} // end \WC_Address_Validation_Postcode_Anywhere class
