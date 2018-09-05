<?php
/**
 * Plugin Name: WooCommerce AvaTax
 * Plugin URI: http://www.woocommerce.com/products/woocommerce-avatax/
 * Description: Seamless integration with Avalara's tax calculation and management services.
 * Author: SkyVerge
 * Author URI: http://www.woocommerce.com/
 * Version: 1.4.0
 * Text Domain: woocommerce-avatax
 * Domain Path: /i18n/languages/
 *
 * Copyright: (c) 2016-2017, SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   AvaTax
 * @author    SkyVerge
 * @copyright Copyright (c) 2016-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

// Required functions
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'woo-includes/woo-functions.php' );
}

// Plugin updates
woothemes_queue_update( plugin_basename( __FILE__ ), '57077a4b28ba71cacf692bcf4a1a7f60', '1389326' );

// WC active check
if ( ! is_woocommerce_active() ) {
	return;
}

// If the legacy plugin is active, hold off and deactivate it first
if ( class_exists( 'WC_AvaTax' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'includes/class-wc-avatax-legacy-handler.php' );
	WC_AvaTax_Legacy_Handler::instance();
	return;
}

// Required library class
if ( ! class_exists( 'SV_WC_Framework_Bootstrap' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'lib/skyverge/woocommerce/class-sv-wc-framework-bootstrap.php' );
}

SV_WC_Framework_Bootstrap::instance()->register_plugin( '4.6.0', __( 'WooCommerce AvaTax', 'woocommerce-avatax' ), __FILE__, 'init_woocommerce_avatax', array(
	'minimum_wc_version'   => '2.5.5',
	'minimum_wp_version'   => '4.1',
	'backwards_compatible' => '4.4',
) );

function init_woocommerce_avatax() {

/**
 * WooCommerce AvaTax main plugin class.
 *
 * @since 1.0.0
 */
class WC_AvaTax extends SV_WC_Plugin {


	/** plugin version number */
	const VERSION = '1.4.0';

	/** plugin id */
	const PLUGIN_ID = 'avatax';

	/** @var WC_AvaTax single instance of this plugin */
	protected static $instance;

	/** @var WC_AvaTax_API the api class */
	protected $api;

	/** @var \WC_AvaTax_Order_Handler instance */
	protected $order_handler;

	/** @var \WC_AvaTax_Checkout_Handler instance */
	protected $checkout_handler;

	/** @var \WC_AvaTax_Admin instance */
	protected $admin;

	/** @var \WC_AvaTax_Frontend instance */
	protected $frontend;

	/** @var \WC_AvaTax_AJAX instance */
	protected $ajax;

	/** @var \WC_AvaTax_Import_Export_Handler instance, adds support for import/export functionality */
	protected $import_export_handler;

	/** @var bool $calculate_taxes Whether tax calculation is enabled */
	private $calculate_taxes;

	/** @var boo,l $logging_enabled Whether debug logging is enabled */
	private $logging_enabled;


	/**
	 * Initializes the plugin
	 *
	 * @since 1.0.0
	 * @return \WC_AvaTax
	 */
	public function __construct() {

		parent::__construct( self::PLUGIN_ID, self::VERSION, array(
			'text_domain' => 'woocommerce-avatax',
		) );

		// Include required files
		add_action( 'sv_wc_framework_plugins_loaded', array( $this, 'includes' ) );

		// Add the AvaTax rate code
		add_filter( 'woocommerce_rate_code', array( $this, 'set_tax_rate_code' ), 10, 2 );

		// Set the AvaTax rate code label
		add_filter( 'woocommerce_cart_tax_totals', array( $this, 'set_tax_rate_labels' ), 10, 2 );

		// Set the order item tax rate ID
		add_filter( 'woocommerce_order_item_get_rate_id', array( $this, 'set_order_item_tax_rate_id' ), 10, 2 );

		if ( $this->override_wc_rates() ) {
			add_filter( 'woocommerce_find_rates', '__return_empty_array' );
		}

		// Lifecycle
		add_action( 'admin_init', array ( $this, 'maybe_activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// Turn off API request logging unless specified in the settings
		if ( ! $this->logging_enabled() ) {
			remove_action( 'wc_' . $this->get_id() . '_api_request_performed', array( $this, 'log_api_request' ) );
		}
	}


	/**
	 * Include required files
	 *
	 * @since 1.0.0
	 */
	public function includes() {

		// Set up the order handler
		$this->order_handler = $this->load_class( '/includes/class-wc-avatax-order-handler.php', 'WC_AvaTax_Order_Handler' );

		// Set up the checkout handler
		$this->checkout_handler = $this->load_class( '/includes/class-wc-avatax-checkout-handler.php', 'WC_AvaTax_Checkout_Handler' );

		// Frontend includes
		if ( ! is_admin() ) {
			$this->frontend = $this->load_class( '/includes/frontend/class-wc-avatax-frontend.php', 'WC_AvaTax_Frontend' );
		}

		// Admin includes
		if ( is_admin() && ! is_ajax() ) {
			$this->admin = $this->load_class( '/includes/admin/class-wc-avatax-admin.php', 'WC_AvaTax_Admin' );
		}

		// Import / Export handler needs to be available in admin over ajax
		if ( is_admin() ) {
			$this->import_export_handler = $this->load_class( '/includes/class-wc-avatax-import-export-handler.php', 'WC_AvaTax_Import_Export_Handler' );
		}

		// AJAX includes
		if ( is_ajax() ) {
			$this->ajax = $this->load_class( '/includes/class-wc-avatax-ajax.php', 'WC_AvaTax_AJAX' );
		}
	}


	/**
	 * Get the admin class instance.
	 *
	 * @since 1.2.0
	 * @return \WC_AvaTax_Admin
	 */
	public function get_admin_instance() {
		return $this->admin;
	}


	/**
	 * Get the frontend class instance.
	 *
	 * @since 1.2.0
	 * @return \WC_AvaTax_Frontend
	 */
	public function get_frontend_instance() {
		return $this->frontend;
	}


	/**
	 * Get the ajax handler.
	 *
	 * @since 1.2.0
	 * @return \WC_AvaTax_AJAX
	 */
	public function get_ajax_handler() {
		return $this->ajax;
	}


	/**
	 * Return the import/export handler class instance
	 *
	 * @since 1.3.0
	 * @return \WC_AvaTax_Import_Export_Handler
	 */
	public function get_import_export_handler_instance() {
		return $this->import_export_handler;
	}


	/**
	 * Get the order handler.
	 *
	 * @since 1.2.0
	 * @return WC_AvaTax_Order_Handler The order handler object
	 */
	public function get_order_handler() {
		return $this->order_handler;
	}


	/**
	 * Get the checkout handler.
	 *
	 * @since 1.2.0
	 * @return WC_AvaTax_Checkout_Handler The checkout handler object
	 */
	public function get_checkout_handler() {
		return $this->checkout_handler;
	}


	/**
	 * Backwards compat for changing the visibility of some class instances.
	 *
	 * @TODO Remove this as part of WC 3.1 compat {CW 2016-05-19}
	 *
	 * @since 1.2.0
	 */
	public function __get( $name ) {

		switch ( $name ) {

			case 'admin':
				_deprecated_function( 'wc_avatax()->admin', '1.2.0', 'wc_avatax()->get_admin_instance()' );
				return $this->get_admin_instance();

			case 'frontend':
				_deprecated_function( 'wc_avatax()->frontend', '1.2.0', 'wc_avatax()->get_frontend_instance()' );
				return $this->get_frontend_instance();

			case 'ajax':
				_deprecated_function( 'wc_avatax()->ajax', '1.2.0', 'wc_avatax()->get_ajax_handler()' );
				return $this->get_ajax_handler();
		}

		// you're probably doing it wrong
		trigger_error( 'Call to undefined property ' . __CLASS__ . '::' . $name, E_USER_ERROR );

		return null;
	}


	/**
	 * Backwards compat for changing the naming of some methods.
	 *
	 * @TODO Remove this as part of WC 3.1 compat {CW 2016-05-19}
	 *
	 * @since 1.2.0
	 */
	public function __call( $name, $arguments ) {

		switch ( $name ) {

			case 'order_handler':
				_deprecated_function( 'wc_avatax()->order_handler()', '1.2.0', 'wc_avatax()->get_order_handler()' );
				return $this->get_order_handler( $arguments );

			case 'checkout_handler':
				_deprecated_function( 'wc_avatax()->checkout_handler()', '1.2.0', 'wc_avatax()->get_checkout_handler()' );
				return $this->get_checkout_handler( $arguments );
		}

		// you're probably doing it wrong
		trigger_error( 'Call to undefined method ' . __CLASS__ . '::' . $name . '()', E_USER_ERROR );

		return null;
	}


	/**
	 * Set the custom AvaTax tax rate code.
	 *
	 * @since 1.0.0
	 * @param string $code_string The tax rate code.
	 * @param int|string $key The requested tax rate code ID.
	 * @return string $code_string The tax rate code.
	 */
	public function set_tax_rate_code( $code_string, $key ) {

		if ( 'avatax' === $key ) {
			$code_string = 'AVATAX';
		}

		return $code_string;
	}


	/**
	 * Set the AvaTax tax rate label and amount label.
	 *
	 * @since 1.0.0
	 * @param array $tax_totals The existing tax rate totals.
	 * @param WC_Cart $cart The cart object.
	 * @return array
	 */
	public function set_tax_rate_labels( $tax_totals, $cart ) {

		$code = 'AVATAX';

		if ( isset( $tax_totals[ $code ] ) ) {

			$tax_totals[ $code ]->tax_rate_id = 'avatax';
			$tax_totals[ $code ]->is_compound = false;

			/**
			 * Filter the itemized tax label.
			 *
			 * @since 1.0.0
			 * @param string $label the tax label. Defaults to either "Tax" or "VAT".
			 */
			$tax_totals[ $code ]->label = apply_filters( 'wc_avatax_tax_label', WC()->countries->tax_or_vat() );
		}

		return $tax_totals;
	}


	/**
	 * Sets the order item tax rate ID.
	 *
	 * This is primarily used so that taxes display properly in the admin.
	 *
	 * @since 1.4.0
	 * @param int $rate_id The tax rate ID
	 * @param \WC_Order_Item_Tax The order item tax object
	 * @return string
	 */
	public function set_order_item_tax_rate_id( $rate_id, $item ) {

		if ( 'AVATAX' === $item->get_name() ) {
			$rate_id = 'avatax';
		}

		return $rate_id;
	}


	/** Admin methods ******************************************************/


	/**
	 * Render a notice for the user to read the docs before adding add-ons
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::add_admin_notices()
	 */
	public function add_admin_notices() {

		// show any dependency notices
		parent::add_admin_notices();

		$screen = get_current_screen();

		if ( ( isset( $_GET['page'] ) && 'wc-settings' === $_GET['page'] ) || 'plugins' === $screen->id ) {

			$notice      = '';
			$dismissible = true;

			// If the API is not connected, display a persistent notice throughout WC settings screens
			if ( ! $this->check_api() ) {

				$notice = sprintf(
					/* translators: Placeholders: %1$s - <strong> tag, %2$s - </strong> tag, %3$s - <a> tag, %4$s - </a> tag */
					__( '%1$sWooCommerce AvaTax is almost ready!%2$s To get started, please ​%3$sconnect to AvaTax%4$s.', 'woocommerce-avatax' ),
					'<strong>',
					'</strong>',
					'<a href="' . esc_url( $this->get_settings_url() ) . '">',
					'</a>'
				);

				$dismissible = false;

			// Otherwise, just a prompt to read the docs will do on our settings/plugins screen
			} elseif ( $this->is_plugin_settings() || 'plugins' === $screen->id ) {

				$notice = sprintf(
					/* translators: Placeholders: %1$s - <strong> tag, %2$s - </strong> tag, %3$s - <a> tag, %4$s - </a> tag */
					__( '%1$sThanks for installing WooCommerce AvaTax!%2$s Need help? %3$sRead the documentation%4$s.', 'woocommerce-avatax' ),
					'<strong>',
					'</strong>',
					'<a href="' . esc_url( $this->get_documentation_url() ) . '" target="_blank">',
					'</a>'
				);
			}

			if ( $notice ) {
				$this->get_admin_notice_handler()->add_admin_notice( $notice, 'wc-avatax-welcome', array(
					'always_show_on_settings' => false,
					'dismissible'             => $dismissible,
					'notice_class'            => 'updated'
				) );
			}

			// Display a notice AvaTax calculation is enabled by global WC taxes are disabled
			if ( 'yes' === get_option( 'wc_avatax_enable_tax_calculation' ) && ! wc_tax_enabled() ) {

				$tax_setting_url = SV_WC_Plugin_Compatibility::is_wc_version_gte_2_6() ? admin_url( 'admin.php?page=wc-settings' ) : admin_url( 'admin.php?page=wc-settings&tab=tax' );

				$this->get_admin_notice_handler()->add_admin_notice( sprintf(
					/* translators: Placeholders: %1$s - <strong> tag, %2$s - </strong> tag, , %3$s - <a> tag, %4$s - </a> tag */
					__( '%1$sWooCommerce taxes are disabled.%2$s To see tax rates from AvaTax at checkout, please %3$senable taxes%4$s for your store.', 'woocommerce-avatax' ),
					'<strong>', '</strong>',
					'<a href="' . esc_url( $tax_setting_url ) . '">', '</a>'
				), 'wc-taxes-deactivated-notice', array( 'notice_class' => 'error' ) );
			}
		}

		// Display a notice when the legacy extension is deactivated
		if ( 'plugins' === $screen->id && 'yes' === get_option( 'wc_avatax_legacy_deactivated' ) ) {

			$this->get_admin_notice_handler()->add_admin_notice( __( 'The legacy version of the WooCommerce AvaTax exension was deactivated.', 'woocommerce-avatax' ), 'legacy-deactivated-notice', array(
				'always_show_on_settings' => false,
				'notice_class'            => 'updated'
			) );

			delete_option( 'wc_avatax_legacy_deactivated' );
		}
	}


	/** Helper methods ******************************************************/


	/**
	 * Main WC_AvaTax Instance, ensures only one instance is/can be loaded.
	 *
	 * @since 1.0.0
	 * @see wc_avatax()
	 * @return WC_AvaTax
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Gets the plugin documentation URL
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::get_documentation_url()
	 * @return string
	 */
	public function get_documentation_url() {
		return 'http://docs.woocommerce.com/document/woocommerce-avatax/';
	}


	/**
	 * Gets the plugin support URL
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::get_support_url()
	 * @return string
	 */
	public function get_support_url() {

		return 'https://woocommerce.com/my-account/tickets/';
	}


	/**
	 * Returns the plugin name, localized
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::get_plugin_name()
	 * @return string the plugin name
	 */
	public function get_plugin_name() {
		return __( 'WooCommerce AvaTax', 'woocommerce-avatax' );
	}


	/**
	 * Returns __FILE__
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::get_file()
	 * @return string the full path and filename of the plugin file
	 */
	protected function get_file() {
		return __FILE__;
	}


	/**
	 * Returns true if on the plugin's settings page
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::is_plugin_settings()
	 * @return boolean true if on the settings page
	 */
	public function is_plugin_settings() {
		return isset( $_GET['page'] ) &&
			'wc-settings' == $_GET['page'] &&
			isset( $_GET['tab'] ) &&
			'tax' == $_GET['tab'] &&
			isset( $_GET['section'] ) &&
			'avatax' == $_GET['section'];
	}


	/**
	 * Gets the plugin configuration URL
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::get_settings_link()
	 * @param string $plugin_id optional plugin identifier.  Note that this can be a
	 *        sub-identifier for plugins with multiple parallel settings pages
	 *        (ie a gateway that supports both credit cards and echecks)
	 * @return string plugin settings URL
	 */
	public function get_settings_url( $plugin_id = null ) {
		return admin_url( 'admin.php?page=wc-settings&tab=tax&section=avatax' );
	}


	/**
	 * Determine if AvaTax calculation is enabled.
	 *
	 * @since 1.0.0
	 * @return bool $calculate_taxes Whether AvaTax calculation is enabled.
	 */
	public function calculate_taxes() {

		$this->calculate_taxes = ( wc_tax_enabled() && 'yes' === get_option( 'wc_avatax_enable_tax_calculation' ) );

		/**
		 * Filter whether AvaTax calculation is enabled.
		 *
		 * @since 1.0.0
		 * @param bool $calculate_taxes Whether AvaTax calculation is enabled.
		 */
		return (bool) apply_filters( 'wc_avatax_calculate_taxes', $this->calculate_taxes );
	}


	/**
	 * Determine if WooCommerce tax rates should be overridden.
	 *
	 * @since 1.0.0
	 * @return bool Whether WooCommerce tax rates should be overridden.
	 */
	public function override_wc_rates() {

		/**
		 * Filter whether WooCommerce tax rates should be overridden.
		 *
		 * @since 1.0.0
		 * @param bool $override Whether WooCommerce tax rates should be overridden.
		 */
		$override = (bool) apply_filters( 'wc_avatax_override_woocommerce_rates', $this->calculate_taxes() );

		return $override;
	}


	/**
	 * Determine if tax calculation is supported by the customer's entered location.
	 *
	 * Currently this only checks the plugin's availability settings and not any
	 * actual nexus settings in the merchant's Avalara account, as that information
	 * is not yet available via their REST API.
	 *
	 * @since 1.1.0
	 * @param string $country_code the country code to check
	 * @param string $state Optional. The state to check. Omit to only check the country
	 * @return bool
	 */
	public function is_location_taxable( $country_code, $state = '' ) {

		$taxable = false;

		$locations = $this->get_enabled_tax_locations();

		// if any state is valid (wildcard), no need to check further
		if ( in_array( $country_code . ':*', $locations ) ) {
			$taxable = true;
		} elseif ( $state ) {
			$taxable = in_array( $country_code . ':' . $state, $locations );
		}

		/**
		 * Filters whether a provided country/state combo is taxable by AvaTax.
		 *
		 * @since 1.2.3
		 * @param bool $taxable
		 * @param string $country_code the country code to check
		 * @param string $state the state to check
		 */
		return (bool) apply_filters( 'wc_avatax_is_location_taxable', $taxable, $country_code, $state );
	}


	/**
	 * Get the locations where tax calculation is enabled in the settings.
	 *
	 * @since 1.1.0
	 * @return array
	 */
	public function get_enabled_tax_locations() {

		if ( 'specific' === get_option( 'wc_avatax_tax_locations', 'all' ) ) {
			$locations = get_option( 'wc_avatax_specific_tax_locations', array() );
		} else {
			$locations = array_keys( $this->get_available_tax_locations() );
		}

		/**
		 * Filter the locations where tax calculation is enabled in the settings.
		 *
		 * @since 1.1.0
		 * @param array $locations the locations in the format $country_code:$state_code => $country_name
		 */
		return apply_filters( 'wc_avatax_enabled_tax_locations', $locations );
	}


	/**
	 * Get the locations where tax calculation is available from Avalara.
	 *
	 * @since 1.1.0
	 * @return array
	 */
	public function get_available_tax_locations() {

		$countries = ( WC()->countries->countries ) ? WC()->countries->countries : array();

		// These countries can be supported at the state level
		$countries_with_jurisdictions = array(
			'BR',
			'CA',
			'IN',
			'US',
		);

		$locations = array();

		foreach ( $countries as $country_code => $country_name ) {

			$locations[ $country_code . ':*' ] = $country_name;

			if ( in_array( $country_code, $countries_with_jurisdictions ) && $states = WC()->countries->get_states( $country_code ) ) {

				foreach ( $states as $state_code => $state_name ) {
					$locations[ $country_code . ':' . $state_code ] = '&nbsp;&nbsp;&nbsp;&nbsp;' . $state_name;
				}
			}
		}

		/**
		 * Filter the locations where tax calculation is available from Avalara.
		 *
		 * @since 1.1.0
		 * @param array $locations the locations in the format $country_code:$state_code => $country_name
		 */
		return apply_filters( 'wc_avatax_available_tax_locations', $locations );
	}


	/**
	 * Determine if debug logging is enabled.
	 *
	 * @since 1.0.0
	 * @return bool $logging_enabled Whether debug logging is enabled.
	 */
	public function logging_enabled() {

		$this->logging_enabled = ( 'yes' === get_option( 'wc_avatax_debug' ) );

		/**
		 * Filter whether debug logging is enabled.
		 *
		 * @since 1.0.0
		 * @param bool $logging_enabled Whether debug logging is enabled.
		 */
		return apply_filters( 'wc_avatax_logging_enabled', $this->logging_enabled );
	}


	/**
	 * Get the API class instance.
	 *
	 * @since 1.0.0
	 * @return WC_AvaTax_API
	 */
	public function get_api() {

		// Return the API object if already instantiated
		if ( is_object( $this->api ) ) {
			return $this->api;
		}

		// Load the API classes
		require_once( $this->get_plugin_path() . '/includes/api/class-wc-avatax-api.php' );
		require_once( $this->get_plugin_path() . '/includes/api/requests/class-wc-avatax-api-request.php' );
		require_once( $this->get_plugin_path() . '/includes/api/requests/class-wc-avatax-api-tax-request.php' );
		require_once( $this->get_plugin_path() . '/includes/api/requests/class-wc-avatax-api-address-request.php' );
		require_once( $this->get_plugin_path() . '/includes/api/responses/class-wc-avatax-api-response.php' );
		require_once( $this->get_plugin_path() . '/includes/api/responses/class-wc-avatax-api-tax-response.php' );
		require_once( $this->get_plugin_path() . '/includes/api/responses/class-wc-avatax-api-address-response.php' );

		// Get the API token & secret
		$account_number = get_option( 'wc_avatax_api_account_number' );
		$license_key    = get_option( 'wc_avatax_api_license_key' );
		$environment    = get_option( 'wc_avatax_api_environment' );

		// Instantiate the API
		return $this->api = new WC_AvaTax_API( $account_number, $license_key, $environment );
	}

	/**
	 * Determine if API credentials exist and are valid.
	 *
	 * @since 1.0.0
	 * @param bool $check_cache Whether to check the cached result first.
	 * @return bool Whether the API credentials exist and are valid.
	 */
	public function check_api( $check_cache = true ) {

		// Check for the cached result first
		if ( $check_cache && ( $cache = get_transient( 'wc_avatax_connection_status' ) ) ) {

			if ( 'connected' == $cache ) {
				return true;
			} else if ( 'not-connected' == $cache ) {
				return false;
			}
		}

		/**
		 * Filter the amount of time to keep the connection status cache.
		 *
		 * @since 1.0.0
		 * @param int $expiration The cache expiration, in seconds.
		 */
		$cache_expiration = apply_filters( 'wc_avatax_connection_status_cache_expiration', MINUTE_IN_SECONDS * 5 );

		// No cache exists, so test the API
		try {

			$this->get_api()->test();

			set_transient( 'wc_avatax_connection_status', 'connected', $cache_expiration );

			return true;

		} catch ( SV_WC_API_Exception $e ) {

			if ( $this->logging_enabled() ) {
				$this->log( $e->getCode() . ' - ' . $e->getMessage() );
			}

			set_transient( 'wc_avatax_connection_status', 'not-connected', $cache_expiration );

			return false;
		}
	}


	/** Lifecycle methods ******************************************************/


	/**
	 * Install default settings & pages
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::install()
	 */
	protected function install() {

		// include settings so we can install defaults
		$settings = $this->load_class( '/includes/admin/class-wc-avatax-settings.php', 'WC_AvaTax_Settings' );

		// install default settings for each section
		foreach ( $settings->get_settings() as $setting ) {

			if ( isset( $setting['default'] ) ) {

				update_option( $setting['id'], $setting['default'] );
			}
		}

		$this->maybe_migrate();
	}


	/**
	 * Handles upgrade routines.
	 *
	 * @since 1.3.0
	 * @see SV_WC_Plugin::upgrade()
	 * @param string $installed_version currently installed version
	 */
	public function upgrade( $installed_version ) {

		// upgrade to version 1.3.0
		if ( version_compare( $installed_version, '1.3.0', '<' ) && 'yes' === get_option( 'wc_avatax_migrated' ) ) {
			// for users we've previously migrated, delete the old settings
			delete_option( 'woocommerce_avatax_settings' );
		}
	}


	/**
	 * Determine if the legacy AvaTax plugin's settings exist and migrate them if so.
	 *
	 * @since 1.0.0
	 */
	protected function maybe_migrate() {

		if ( 'yes' === get_option( 'wc_avatax_migrated' ) ) {
			return;
		}

		$this->log( 'Starting migration from legacy extension' );

		/**
		 * Process settings
		 */

		$legacy_settings = get_option( 'woocommerce_avatax_settings', array() );

		if ( ! empty( $legacy_settings ) ) {

			$settings = array(
				'wc_avatax_origin_address' => array(),
			);

			// These options can be copied to ours directly
			$direct_options = array(
				'account'              => 'wc_avatax_api_account_number',
				'license'              => 'wc_avatax_api_license_key',
				'company_code'         => 'wc_avatax_company_code',
				'default_tax_code'     => 'wc_avatax_default_product_code',
				'default_freight_code' => 'wc_avatax_shipping_code',
				'addr_filter_list'     => 'wc_avatax_address_validation_countries',
			);

			foreach ( $legacy_settings as $name => $value ) {

				switch ( $name ) {

					case 'avalara_url':
						$settings['wc_avatax_api_environment'] = ( SV_WC_Helper::str_starts_with( $value, 'https://development' ) ) ? 'development' : 'production';
						break;

					case 'disable_tax_calc':

						if ( 'yes' !== $value ) {
							$settings['wc_avatax_enable_tax_calculation'] = 'yes';

							// Enable WC taxes as the legacy plugin required them to be disabled
							update_option( 'woocommerce_calc_taxes', 'yes' );
						}

						break;

					case 'disable_addr_validation':
						$settings['wc_avatax_enable_address_validation'] = ( 'yes' !== $value ) ? 'yes' : 'no';
						break;

					case 'commit_action':
						$settings['wc_avatax_commit'] = ( 'c' === $value ) ? 'yes' : 'no';
						break;

					case 'enable_exempt_id':
						$settings['wc_avatax_enable_vat'] = ( 'b' === $value ) ? 'yes' : 'no';
						break;

					// Rebuild the origin address
					case 'origin_street':
						$settings['wc_avatax_origin_address']['address_1'] = $value;
						break;
					case 'origin_city':
						$settings['wc_avatax_origin_address']['city'] = $value;
						break;
					case 'origin_state':
						$settings['wc_avatax_origin_address']['state'] = $value;
						break;
					case 'origin_zip':
						$settings['wc_avatax_origin_address']['postcode'] = $value;
						break;
					case 'origin_country':
						$settings['wc_avatax_origin_address']['country'] = $value;
						break;

					default:
						if ( isset( $direct_options[ $name ] ) ) {
							$settings[ $direct_options[ $name ] ] = $value;
						}
				}
			}

			// Update the settings with the migrated values
			foreach ( $settings as $name => $value ) {

				if ( '' !== $value ) {
					update_option( $name, $value );
				}
			}

			// Remove the legacy settings
			delete_option( 'woocommerce_avatax_settings' );
		}

		/**
		 * Process orders
		 */

		// Get order that have been processed by AvaTax but haven't been migrated yet
		$legacy_orders = get_posts( array(
			'post_type'   => 'shop_order',
			'post_status' => 'any',
			'meta_query'  => array(
				'relation' => 'AND',
				array(
					'key'     => '_taxnow_avalaracommit',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => '_wc_avatax_status',
					'value'   => 'The tops of UPS trucks are not brown :( (bug #23268)',
					'compare' => 'NOT EXISTS',
				),
			),
		) );

		// Convert to our custom order statuses
		foreach ( $legacy_orders as $order ) {

			$order = wc_get_order( $order->ID );

			$order_id = SV_WC_Order_Compatibility::get_prop( $order, 'id' );

			add_post_meta( $order_id, '_wc_avatax_status', 'posted' );

			if ( 'return' === get_post_meta( $order_id, '_taxnow_avalaracommit', true ) ) {
				add_post_meta( $order_id, '_wc_avatax_status', 'refunded' );
			}

			if ( $order->has_status( 'cancelled' ) ) {
				add_post_meta( $order_id, '_wc_avatax_status', 'voided' );
			}

			// Don't process this one again
			add_post_meta( $order_id, '_wc_avatax_status', 'migrated' );
		}

		global $wpdb;

		// Migrate the product tax codes
		// legacy key: _taxnow_taxcode
		// new key: _wc_avatax_code
		$wpdb->update( $wpdb->postmeta, array(
			'meta_key' => '_wc_avatax_code',
		), array(
			'meta_key' => '_taxnow_taxcode',
		) );

		// Migration complete
		update_option( 'wc_avatax_migrated', 'yes' );

		$this->log( 'Migration complete' );
	}


	/**
	 * Handle plugin activation
	 *
	 * @since 1.0.0
	 */
	public function maybe_activate() {

		$is_active = get_option( 'wc_avatax_is_active', false );

		if ( ! $is_active ) {

			update_option( 'wc_avatax_is_active', true );

			/**
			 * Run when AvaTax is activated.
			 *
			 * @since 1.0.0
			 */
			do_action( 'wc_avatax_activated' );
		}

	}


	/**
	 * Handle plugin deactivation
	 *
	 * @since 1.0.0
	 */
	public function deactivate() {

		delete_option( 'wc_avatax_is_active' );

		/**
		 * Run when AvaTax is deactivated
		 *
		 * @since 1.0.0
		 */
		do_action( 'wc_avatax_deactivated' );
	}


} // end WC_AvaTax class


/**
 * Returns the One True Instance of WC_AvaTax
 *
 * @since 1.0.0
 * @return WC_AvaTax
 */
function wc_avatax() {
	return WC_AvaTax::instance();
}

// fire it up!
wc_avatax();

} // init_woocommerce_avatax()
