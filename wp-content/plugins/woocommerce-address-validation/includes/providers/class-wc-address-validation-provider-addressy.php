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
 * Addressy Provider Class
 *
 * Extends abstract provider class to provide address verification via Addressy API
 *
 * @link https://www.addressy.com/
 * @since 2.0.0
 */
class WC_Address_Validation_Provider_Addressy extends WC_Address_Validation_Provider {


	/** @var string service key for API */
	public $service_key;

	/** @var string service key for API */
	public $validate_international_addresses;


	/**
	 * Setup id/title/description and declare country / feature support
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->id = 'addressy';

		$this->title = __( 'Addressy', 'woocommerce-address-validation' );

		/* translators: Placeholders: %1$s - <a> tag, %2$s - </a> tag */
		$this->description = sprintf( __( 'Addressy offers 100 free US address verification per month for both residential and commercial US addresses, and paid accounts can perform lookup for addresses in any country. %1$sSign up for a free account%2$s now to get started.', 'woocommerce-address-validation'), '<a href="https://www.addressy.com/partner/ADRSY11126" target="_blank">', '</a>' );

		$this->supports = array(
			'address_validation',
			'address_classification',
		);

		// setup form fields
		$this->init_form_fields();

		// load settings
		$this->init_settings();

		$this->service_key                       = $this->settings['service_key'];
		$this->validate_international_addresses  = isset( $this->settings['validate_international_addresses'] ) ? $this->settings['validate_international_addresses'] : 'no';

		// Save settings
		add_action( 'wc_address_validation_update_provider_options_' . $this->id, array( $this, 'process_admin_options' ) );
	}


	/**
	 * Check if provider is configured correctly
	 *
	 * @since 2.0.0
	 * @return bool true if configured, false otherwise
	 */
	public function is_configured() {

		return $this->service_key ? true : false;
	}


	/**
	 * Init settings
	 *
	 * @since 2.0.0
	 */
	public function init_form_fields() {

		$this->form_fields = array(

			'service_key'  => array(
				'title'    => __( 'Service Key', 'woocommerce-address-validation' ),
				'type'     => 'text',
				/* translators: Placeholders: %1$s - <a> tag, %2$s - </a> tag */
				'description' => sprintf( __( 'Enter your service key, which can be obtained by following the %1$sAddressy Setup Guide%2$s.', 'woocommerce-address-validation' ), '<a href="' . esc_url( wc_address_validation()->get_documentation_url() ) . '#addressy">', '</a>' ),
				'default'  => '',
			),

			'validate_international_addresses'  => array(
				'title'    => __( 'Validate international addresses', 'woocommerce-address-validation' ),
				'type'     => 'checkbox',
				'label'    => __( 'Enable lookup for customers outside the US (Requires a paid Addressy account)', 'woocommerce-address-validation' ),
				'default'  => 'no',
			),
		);
	}


}
