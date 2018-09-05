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
 * @package     WC-Address-Validation/Provider/Postcode-Software-Dot-Net
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * PostcodeSoftware.net Provider Class
 *
 * Extends abstract provider class to provide postcode lookup via PostcodeSoftware.net API
 *
 * @link
 * @since 1.0
 */
class WC_Address_Validation_Provider_Postcode_Software_Dot_Net extends WC_Address_Validation_Provider {


	/* API Endpoint */
	const API_ENDPOINT = 'http://ws1.postcodesoftware.co.uk/lookup.asmx/getAddress?';


	/**
	 * Setup id/title/description and declare country / feature support
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->id = 'postcodesoftware_dot_net';

		$this->title = __( 'PostcodeSoftware.net', 'woocommerce-address-validation' );

		$this->countries = array( 'GB' );

		$this->supports = array(
			'postcode_lookup'
		);

		// setup form fields
		$this->init_form_fields();

		// load settings
		$this->init_settings();

		$this->account_number = $this->settings[ 'account_number' ];
		$this->password       = $this->settings[ 'password' ];

		if ( $this->account_number && $this->password ) {
			$this->configured = true;
		}

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

		// set account/password/postcode GET args
		$args = array(
			'account'  => $this->account_number,
			'password' => $this->password,
			'postcode' => urlencode( $postcode )
		);

		// send GET request
		$response = wp_safe_remote_get( add_query_arg( $args, self::API_ENDPOINT ) );

		// check for network timeout, etc
		if ( is_wp_error( $response ) || ( ! isset( $response['body'] ) ) ) {
			$locations = array( 'value' => 'none', 'name' => __( 'No addresses found, please check your postcode and try again.', 'woocommerce-address-validation' ) );
		} else {

			// decode response body
			$response = simplexml_load_string( $response['body'] );

			// setup locations if more than 1 exists and there's no error
			if ( isset( $response->ErrorNumber ) && '0' == (string) $response->ErrorNumber ) {

				$address_1 = (string) $response->Address1; // cast as string otherwise simpleXML elements are used
				$address_2 = (string) $response->Address2;
				$city      = (string) $response->Town;
				$postcode  = (string) $response->Postcode;
				$state     = (string) $response->County;

				if ( isset( $response->PremiseData ) ) {

					// premise data is stored in ';' separated format
					foreach ( explode( ';', $response->PremiseData ) as $location_num => $location ) {

						if ( ! $location ) {
							continue;
						}

						// individual company/name is stored in '|' separated format
						$location = explode( '|', $location );

						$premise = str_replace( '/', ' ', "{$location[1]} {$location[2]} {$address_1}");
						$company = ( isset( $location[0] ) ) ? $location[0] : '';

						$locations[ $location_num ] = array(
							'value'     => "location-{$location_num}",
							'company'   => $company,
							'address_1' => $premise,
							'address_2' => $address_2,
							'city'      => $city,
							'postcode'  => $postcode,
							'state'     => $state,
							'name'      => "{$company} {$premise} {$address_2} {$city}",
						);
					}
				} else {

					$locations[] = array(
						'value'     => "location",
						'address_1' => $address_1,
						'address_2' => $address_2,
						'city'      => $city,
						'postcode'  => $postcode,
						'state'     => $state,
						'name'      => "{$address_1} {$address_2} {$city}",
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
			wc_address_validation()->log( $response->asXML() );
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

		return $this->account_number && $this->password ? true : false;
	}


	/**
	 * Init settings
	 *
	 * @since 1.0
	 */
	public function init_form_fields() {

		$this->form_fields = array(

			'account_number' => array(
				'title'    => __( 'Account Number', 'woocommerce-address-validation' ),
				'type'     => 'text',
				'desc_tip' => __( 'Log into your account or look at your sign-up email to find your account number.', 'woocommerce-address-validation' ),
				'default'  => '',
			),

			'password' => array(
				'title'    => __( 'Password', 'woocommerce-address-validation' ),
				'type'     => 'text',
				'desc_tip' => __( 'Log into your account or look at your sign-up email to find your password.', 'woocommerce-address-validation' ),
				'default'  => '',
			),
		);
	}


} // end \WC_Address_Validation_PostcodeSoftware_Dot_Net class
