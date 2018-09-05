<?php
require_once(WP_PLUGIN_DIR . '/woocommerce/includes/abstracts/abstract-wc-settings-api.php');
require_once(WP_PLUGIN_DIR . '/woocommerce/includes/abstracts/abstract-wc-payment-gateway.php');
require_once(WP_PLUGIN_DIR . '/woocommerce/includes/wc-notice-functions.php');
if( !class_exists( 'WP_Http' ) ) {
  include_once( ABSPATH . WPINC. '/class-http.php' );
}
use Roots\Sage\RANDYS;
/**
 * Plugin Name: Fresh WooCommerce Cash
 * Plugin URI: https://www.freshconsutling.com/
 * Description: Let wholesale customers pay with cash on will call pickup
 * Author: Fresh Consulting
 * Plugin URI: https://www.freshconsutling.com/
 * Version: 1.0.0
 * Text Domain: wc-gateway-cash
 * Domain Path: /i18n/languages/
 *
 *
 * @package   WC-Gateway-Cash
 * @author    Fresh Consulting
 * @category  Admin
 * @copyright Copyright (c) Fresh consulting.
 *
 */
defined( 'ABSPATH' ) or exit;

// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

/**
 * Add the gateway to WC Available Gateways
 * 
 * @since 1.0.0
 * @param array $gateways all available WC gateways
 * @return array $gateways all WC gateways + cash gateway
 */
function wc_cash_add_to_gateways( $gateways ) {
	$gateways[] = 'WC_Gateway_Cash';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'wc_cash_add_to_gateways' );

/**
 * Adds plugin page links
 * 
 * @since 1.0.0
 * @param array $links all plugin links
 * @return array $links all plugin links + our custom links (i.e., "Settings")
 */
function wc_cash_gateway_plugin_links( $links ) {
	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=cash_gateway' ) . '">' . __( 'Configure', 'wc-gateway-cash' ) . '</a>'
	);
	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_cash_gateway_plugin_links' );

/**
 * Cash Payment Gateway
 *
 * Pay with cash on will call pickup
 *
 * @class       WC_Gateway_Cash
 * @extends     WC_Payment_Gateway
 * @version     1.0.0
 * @package     WooCommerce/Classes/Payment
 * @author      Fresh Consulting
 */
 add_action( 'plugins_loaded', 'wc_cash_gateway_init', 11 );

function wc_cash_gateway_init() {
  class WC_Gateway_Cash extends WC_Payment_Gateway {

    public function __construct() {

      $this->id                 = 'cash';
      $this->icon               = '';
      $this->has_fields         = true;
      $this->method_title       = __( 'Cash', 'wc-gateway-cash' );
      $this->method_description = __( 'Pay with cash on will call pickup.', 'wc-gateway-cash' );
      $this->count = 0;

      // Load the settings.
      $this->init_form_fields();
      $this->init_settings();

      // Define user set variables
      $this->title        = $this->get_option( 'title' );
      $this->description  = $this->get_option( 'description' );
      $this->instructions = $this->get_option( 'instructions', $this->description );
      
      // Actions
      add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
      
      // Customer Emails
      add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
    }

    /**
    * Payment form on checkout page.
    */
    public function payment_fields() {
      $description = $this->get_description();

      if ( $description ) {
        echo '<span>' . wpautop( wptexturize( trim( $description ) ) ) . '</span>';
      }
    }

    /**
    * Initialize Gateway Settings Form Fields
    */
    public function init_form_fields() {
      $this->form_fields = apply_filters( 'wc_cash_form_fields', array(
        'enabled' => array(
          'title'   => __( 'Enable/Disable', 'wc-gateway-cash' ),
          'type'    => 'checkbox',
          'label'   => __( 'Enable Cash Payment', 'wc-gateway-cash' ),
          'default' => 'yes'
        ),
        
        'title' => array(
          'title'       => __( 'Title', 'wc-gateway-cash' ),
          'type'        => 'text',
          'description' => __( 'This controls the title for the payment method the customer sees during checkout.', 'wc-gateway-cash' ),
          'default'     => __( 'Cash', 'wc-gateway-cash' ),
          'desc_tip'    => true,
        ),
        
        'description' => array(
          'title'       => __( 'Description', 'wc-gateway-cash' ),
          'type'        => 'textarea',
          'description' => __( 'Payment method description that the customer will see on your checkout.', 'wc-gateway-cash' ),
          'default'     => __( 'Use cash on will call pickup.', 'wc-gateway-cash' ),
          'desc_tip'    => true,
        ),
        
        'instructions' => array(
          'title'       => __( 'Instructions', 'wc-gateway-cash' ),
          'type'        => 'textarea',
          'description' => __( 'Instructions that will be added to the thank you page and emails.', 'wc-gateway-cash' ),
          'default'     => '',
          'desc_tip'    => true,
        ),
      ) );
    }

    /**
    * Add content to the WC emails.
    *
    * @access public
    * @param WC_Order $order
    * @param bool $sent_to_admin
    * @param bool $plain_text
    */
    public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
    
      if ( $this->instructions && ! $sent_to_admin && $this->id === $order->payment_method && $order->has_status( 'completed' ) ) {
        echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
      }
    }

    /**
    * Process the payment and return the result
    *
    * @param int $order_id
    * @return array
    */
    public function process_payment( $order_id ) {

      $order = wc_get_order( $order_id );

      // Reduce stock levels
      $order->reduce_order_stock();

      // Remove cart
      WC()->cart->empty_cart();

      // Mark as completed
      $order->update_status( 'completed', __( 'Cash used.', 'wc-gateway-cash' ) );

      do_action('woocommerce_payment_complete', $order_id);
      
      // Return thankyou redirect
      return array(
        'result' 	=> 'success',
        'redirect'	=> $this->get_return_url( $order )
      );
    }

  } // end \WC_Gateway_Cash class
}
