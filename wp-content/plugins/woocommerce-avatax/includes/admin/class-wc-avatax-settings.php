<?php
/**
 * Define the WC_AvaTax_Settings class
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
 * @package   AvaTax\Admin
 * @author    SkyVerge
 * @copyright Copyright (c) 2016-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Set up the admin settings.
 *
 * @since 1.0.0
 */
class WC_AvaTax_Settings {


	/** @var string $id The settings page ID */
	protected $id = 'avatax';


	/**
	 * Construct the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Add the settings section to the WooCommerce Tax tab
		add_filter( 'woocommerce_get_sections_tax', array( $this, 'add_settings_section' ) );

		// Output the settings
		add_action( 'woocommerce_get_settings_tax', array( $this, 'add_settings' ) );

		// Display a custom address settings field
		add_action( 'woocommerce_admin_field_wc_avatax_address', array( $this, 'display_address_fields' ) );

		// Save the settings
		add_action( 'woocommerce_settings_save_tax', array( $this, 'save_settings' ) );
	}


	/**
	 * Add the AvaTax section to the Tax tab.
	 *
	 * @since 1.0.0
	 * @param array $sections The existing Tax sections.
	 * @return array $sections The new Tax sections.
	 */
	public function add_settings_section( $sections ) {

		$sections[ $this->id ] = __( 'AvaTax', 'woocommerce-avatax' );

		return $sections;
	}


	/**
	 * Get the API settings.
	 *
	 * @since 1.0.0
	 * @return array $settings The API settings.
	 */
	public function get_api_settings() {

		$connection_status = get_transient( 'wc_avatax_connection_status' );

		$settings = array(

			array(
				'name' => __( 'Connection Settings', 'woocommerce-avatax' ),
				'type' => 'title',
				'desc' => sprintf(
					/* translators: Placeholders: %1$s - <a> tag, %2$s - </a> tag */
					__( 'Log in to your AvaTax %1$sAdmin Console%2$s to find your connection information.', 'woocommerce-avatax' ),
					'<a href="https://admin-avatax.avalara.net" target="_blank">',
					'</a>'
				),
			),

			array(
				'id'                => 'wc_avatax_api_account_number',
				'name'              => __( 'Account Number', 'woocommerce-avatax' ),
				'type'              => 'text',
				'class'             => 'wc-avatax-connection-field',
				'css'               => 'min-width:300px;',
				'custom_attributes' => array(
					'data-wc-avatax-connection-status' => $connection_status,
				),
			),

			array(
				'id'                => 'wc_avatax_api_license_key',
				'name'              => __( 'License Key', 'woocommerce-avatax' ),
				'type'              => 'text',
				'class'             => 'wc-avatax-connection-field',
				'css'               => 'min-width:300px;',
				'custom_attributes' => array(
					'data-wc-avatax-connection-status' => $connection_status,
				),
			),

			array(
				'id'      => 'wc_avatax_api_environment',
				'name'    => __( 'Environment', 'woocommerce-avatax' ),
				'options' => array(
					'production'  => __( 'Production', 'woocommerce-avatax' ),
					'development' => __( 'Development', 'woocommerce-avatax' ),
				),
				'default' => 'production',
				'type'    => 'select',
			),

			array(
				'type' => 'sectionend',
			),
		);

		/**
		 * Filter the API settings.
		 *
		 * @since 1.0.0
		 * @param array $settings The API settings.
		 */
		return (array) apply_filters( 'woocommerce_get_settings_' . $this->id . '_api', $settings );
	}


	/**
	 * Get the tax calculation settings.
	 *
	 * @since 1.0.0
	 * @return array $settings The tax calculation settings.
	 */
	public function get_tax_settings() {

		$settings = array(

			array(
				'name' => __( 'Tax Calculation', 'woocommerce-avatax' ),
				'type' => 'title',
			),

			array(
				'id'       => 'wc_avatax_enable_tax_calculation',
				'name'     => __( 'Enable/Disable', 'woocommerce-avatax' ),
				'desc'     => __( 'Enable AvaTax tax calculation', 'woocommerce-avatax' ),
				'desc_tip' => __( 'This will override all configured WooCommerce tax rates and replace them with the rates fetched from AvaTax.', 'woocommerce-avatax' ),
				'default'  => 'no',
				'type'     => 'checkbox',
			),

			array(
				'id'       => 'wc_avatax_tax_locations',
				'name'     => __( 'Supported Locations', 'woocommerce' ),
				'type'     => 'select',
				'desc_tip' => __( 'Limit tax calculation and only send documents to Avalara for specific countries and jurisdictions.', 'woocommerce-avatax' ),
				'default'  => 'all',
				'class'    => 'wc-enhanced-select',
				'css'      => 'min-width: 350px;',
				'options'  => array(
					'all'      => __( 'All locations', 'woocommerce' ),
					'specific' => __( 'Specific locations only', 'woocommerce' )
				)
			),

			array(
				'id'      => 'wc_avatax_specific_tax_locations',
				'name'    => __( 'Specific Locations', 'woocommerce-avatax' ),
				'type'    => 'multiselect',
				'class'   => 'wc-enhanced-select',
				'options' => wc_avatax()->get_available_tax_locations(),
			),

			array(
				'id'       => 'wc_avatax_company_code',
				'name'     => __( 'Company Code', 'woocommerce-avatax' ),
				'desc_tip' => __( 'The company code in your AvaTax account in which documents should be posted.', 'woocommerce-avatax' ),
				'type'     => 'text',
			),

			array(
				'id'       => 'wc_avatax_origin_address',
				'name'     => __( 'Origin Address', 'woocommerce-avatax' ),
				'desc_tip' => __( 'Your warehouse or base of operations. This address will be used for tax calculation.', 'woocommerce-avatax' ),
				'type'     => 'wc_avatax_address',
				'default'  => array(
					'country' => WC()->countries->get_base_country(),
				),
			),

			array(
				'id'       => 'wc_avatax_default_product_code',
				'name'     => __( 'Default Product Tax Code', 'woocommerce-avatax' ),
				'desc_tip' => __( 'You can set a specific tax code for each product or product category.', 'woocommerce-avatax' ),

				/* translators: Placeholders: %1$s - <a> tag, %2$s - </a> tag */
				'desc'     => sprintf( __( '%1$sLearn more%2$s', 'woocommerce-avatax' ),
					'<a href="https://help.avalara.com/000_AvaTax_Calc/000AvaTaxCalc_User_Guide/051_Select_AvaTax_System_Tax_Codes/Tax_Codes_-_Frequently_Asked_Questions" target="_blank">',
					'</a>'
				),
				'type'     => 'text',
				'default'  => 'P0000000',
			),

			array(
				'id'      => 'wc_avatax_shipping_code',
				'name'    => __( 'Default Shipping Tax Code', 'woocommerce-avatax' ),
				'type'    => 'text',
				'default' => 'FR',
			),

			array(
				'id'      => 'wc_avatax_enable_vat',
				'title'   => __( 'Enable VAT', 'woocommerce-avatax' ),
				'desc'    => __( 'Allow customers to input their VAT ID during checkout', 'woocommerce-avatax' ),
				'type'    => 'checkbox',
				'default' => 'no',
			),

			array(
				'id'      => 'wc_avatax_commit',
				'name'    => __( 'Commit Transactions', 'woocommerce-avatax' ),
				'desc'    => __( 'Set transactions as "committed" when sending to Avalara', 'woocommerce-avatax' ),
				'default' => 'yes',
				'type'    => 'checkbox',
			),

			array(
				'type' => 'sectionend',
			),
		);

		/**
		 * Filter the tax calculation settings.
		 *
		 * @since 1.0.0
		 * @param array $settings The tax calculation settings.
		 */
		return (array) apply_filters( 'woocommerce_get_settings_' . $this->id . '_tax_calculation', $settings );
	}


	/**
	 * Get the address validation settings.
	 *
	 * @since 1.0.0
	 * @return array $settings The address validation settings.
	 */
	public function get_address_settings() {

		$settings = array(

			array(
				'type' => 'title',
				'name' => __( 'Address Validation', 'woocommerce-avatax' ),
				'desc' => __( 'Validate shipping addresses at checkout before calculating tax.', 'woocommerce-avatax' ),
			),

			array(
				'id'      => 'wc_avatax_enable_address_validation',
				'name'    => __( 'Enable/Disable', 'woocommerce-avatax' ),
				'desc'    => __( 'Enable AvaTax address validation', 'woocommerce-avatax' ),
				'default' => 'no',
				'type'    => 'checkbox',
			),

			array(
				'title'   => __( 'Supported Countries', 'woocommerce-avatax' ),
				'id'      => 'wc_avatax_address_validation_countries',
				'class'   => 'wc-enhanced-select',
				'options' => array(
					'US' => __( 'United States (US)', 'woocommerce-avatax' ),
					'CA' => __( 'Canada', 'woocommerce-avatax' ),
				),
				'default' => array(
					'US',
					'CA',
				),
				'type'    => 'multi_select_countries'
			),

			array(
				'type' => 'sectionend',
			),
		);

		/**
		 * Filter the address validation settings.
		 *
		 * @since 1.0.0
		 * @param array $settings The address validation settings.
		 */
		return (array) apply_filters( 'woocommerce_get_settings_' . $this->id . '_address_validation', $settings );
	}


	/**
	 * Get the address verification settings.
	 *
	 * @since 1.0.0
	 * @return array $settings The address verification settings.
	 */
	public function get_misc_settings() {

		$settings = array(

			array(
				'type' => 'title',
			),

			array(
				'id'      => 'wc_avatax_debug',
				'name'    => __( 'Debug Mode', 'woocommerce-avatax' ),
				'desc'    => __( 'Log API requests, responses, and errors for debugging', 'woocommerce-avatax' ),
				'default' => 'no',
				'type'    => 'checkbox',
			),

			array(
				'type' => 'sectionend',
			),
		);

		/**
		 * Filter the misc settings.
		 *
		 * @since 1.0.0
		 * @param array $settings The misc settings.
		 */
		return apply_filters( 'woocommerce_get_settings_' . $this->id . '_misc', $settings );
	}


	/**
	 * Get all of the combined settings.
	 *
	 * @since 1.0.0
	 * @return array $settings The combined settings.
	 */
	public function get_settings() {

		$settings = array_merge(
			$this->get_api_settings(),
			$this->get_tax_settings(),
			$this->get_address_settings(),
			$this->get_misc_settings()
		);

		/**
		 * Filter the combined settings.
		 *
		 * @since 1.0.0
		 * @param array $settings The combined settings.
		 */
		return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings );
	}


	/**
	 * Replace core Tax settings with our own when the AvaTax section is being viewed.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function add_settings( $settings ) {

		global $current_section;

		// Output the general settings
		if ( $this->id == $current_section ) {

			// Always display the API settings
			$settings = array_merge(
				$this->get_api_settings(),
				$this->get_misc_settings()
			);

			// If the API credentials are good, display the other settings
			if ( wc_avatax()->check_api() ) {

				$settings = array_merge(
					$this->get_tax_settings(),
					$this->get_address_settings(),
					$settings
				);
			}

		}

		return $settings;
	}


	/**
	 * Display a custom address settings field.
	 *
	 * @since 1.0.0
	 * @param array $options The field options.
	 */
	public function display_address_fields( $options ) {

		$description      = WC_Admin_Settings::get_field_description( $options );
		$tooltip_html     = $description['tooltip_html'];
		$description      = $description['description'];
		$id               = $options['id'];
		$label            = $options['title'];
		$type             = $options['type'];
		$values           = WC_Admin_Settings::get_option( $id );
		$countries        = WC()->countries->countries;
		$selected_country = ( isset( $values['country'] ) ) ? $values['country'] : WC()->countries->get_base_country();

		include( wc_avatax()->get_plugin_path() . '/includes/admin/views/setting-address.php' );
	}


	/**
	 * Save the settings.
	 *
	 * @since 1.0.0
	 * @global string $current_section The current settings section.
	 */
	public function save_settings() {

		global $current_section;

		// Output the general settings
		if ( $this->id == $current_section ) {

			// If the API credentials were good at last check, save the settings
			if ( 'connected' == get_transient( 'wc_avatax_connection_status' ) ) {
				$this->save_fields( $this->get_tax_settings() );
				$this->save_fields( $this->get_address_settings() );
				$this->save_fields( $this->get_misc_settings() );
			}

			// Always save the API & misc. settings
			$this->save_fields( $this->get_api_settings() );
			$this->save_fields( $this->get_misc_settings() );

			// Reset the API status transient
			delete_transient( 'wc_avatax_connection_status' );

			// Check the API again and display an error for bad credentials
			if ( ! wc_avatax()->check_api() ) {
				WC_Admin_Settings::add_error( __( 'Your Avalara connection settings are not valid.', 'woocommerce-avatax' ) );
			}
		}
	}


	/**
	 * Save the settings fields.
	 *
	 * This is a simple wrapper for `WC_Admin_Settings::save_fields` to intercept our custom "address"
	 * field type for special handling. All other fields are saved as usual. This is being improved in WC 2.4+
	 * but for now this is easiest for older versions.
	 *
	 * @since 1.0.0
	 * @param array $fields The settings fields to save.
	 */
	private function save_fields( $fields ) {

		// Loop through each setting and look for an address field
		foreach ( $fields as $key => $field ) {

			// If found, save it our way and remove it from the settings to save the WooCommerce way
			if ( isset( $field['id'] ) && isset( $field['type'] ) && 'wc_avatax_address' == $field['type'] ) {
				$this->save_address_field( $field );
				unset( $fields[ $key ] );
			}
		}

		WC_Admin_Settings::save_fields( $fields );
	}


	/**
	 * Save the custom address field.
	 *
	 * @since 1.0.0
	 * @param array $field The field definition.
	 */
	private function save_address_field( $field ) {

		$address = isset( $_POST[ $field['id'] ] ) ? wp_unslash( $_POST[ $field['id'] ] ) : array();

		/**
		 * Filter the address values before the final save.
		 *
		 * @since 1.0.0
		 * @param array $address {
		 *     The address values.
		 *
		 * @type string @address_1 The street address.
		 * @type string @city      The city name.
		 * @type string @state     The state.
		 * @type string @country   The country code.
		 * @type string @postcode  The postal code.
		 * }
		 */
		$address = (array) apply_filters( 'wc_avatax_save_address_field', $address );

		$address = array_map( 'wc_clean', $address );

		if ( ! empty( $address ) ) {
			update_option( $field['id'], $address );
		}
	}


}
