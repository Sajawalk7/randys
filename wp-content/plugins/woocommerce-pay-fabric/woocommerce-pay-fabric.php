<?php
namespace WooCommercePayFabric;

require_once(WP_PLUGIN_DIR . '/woocommerce/includes/abstracts/abstract-wc-settings-api.php');
require_once(WP_PLUGIN_DIR . '/woocommerce/includes/abstracts/abstract-wc-payment-gateway.php');
require_once(WP_PLUGIN_DIR . '/woocommerce/includes/gateways/class-wc-payment-gateway-cc.php');
require_once(WP_PLUGIN_DIR . '/woocommerce/includes/wc-notice-functions.php');

if ( !class_exists( 'WP_Http' ) ) {
    include_once( ABSPATH . WPINC. '/class-http.php' );
}
use WC_Payment_Gateway_CC;
use WP_Http;

/**
 * Plugin Name: WooCommerce PayFabric
 * Plugin URI: https://www.freshconsutling.com/
 * Description: WooCommerce plugin for PayFabric, which is a cloud-based payment processing solution for merchants and developers that makes it easy to accept and manage online payments in your application, website or ecommerce storefront.
 * Author: Fresh Consulting
 * Author URI:
 * Version: 0.0.1
 * Text Domain: wc-fabric-gateway
 * Domain Path:
 *
 * Copyright:
 *
 * @package
 * @author Fresh Consulting
 * @category Admin
 * @copyright Copyright (c) Fresh Consulting.
 *
 */

defined( 'ABSPATH' ) || exit;

// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    return;
}

class WC_Fabric_Gateway extends WC_Payment_Gateway_CC {
    /**
     * Constructor
     */
    public function __construct() {

        $this->id = 'fc_fabric_gateway';
        $this->icon = '';
        $this->has_fields = true;
        $this->method_title = 'PayFabric';
        $this->method_description = '';

        // Supports the default credit card form
        // Render stand credit card form
        $this->supports = array( 'default_credit_card_form' );

        // Initial variable
        $this->sandbox_uri = "https://sandbox.payfabric.com/V2/Rest/api/";
        $this->production_uri = "https://www.payfabric.com/V2/Rest/api/";

        // Define user set variables
        $this->title = $this->get_option( 'title' );
        $this->description = $this->get_option( 'description' );
        $this->instructions = $this->get_option( 'instructions', $this->description );
        $this->gatewayid = $this->get_option( 'gatewayid' );
        $this->deviceId = $this->get_option( 'deviceId' );
        $this->password = $this->get_option( 'password' );
        $this->is_production = $this->get_option( 'production' );
        $this->is_book = $this->get_option( 'book' );

        $this->timeout = ('yes' === $this->is_production) ? 5 : 20;
        $this->process_uri = ('yes' !== $this->is_production) ? $this->sandbox_uri : $this->production_uri;
        // Load the settings
        $this->init_form_fields();
        $this->init_settings();
        // Actions
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
        add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
        // Customer Emails
        // To do
        //add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
    }

    // We can render custom fields on Credit card
    public function payment_fields() {
        echo '<p><strong>Pay securely using your credit card</strong> <img src="' . plugins_url() . '/woocommerce-pay-fabric/images/visa.svg" alt="Visa" class="card-img" width="32"> <img src="' . plugins_url() . '/woocommerce-pay-fabric/images/mastercard.svg" alt="Mastercard" class="card-img" width="32"> <img src="' . plugins_url() . '/woocommerce-pay-fabric/images/discover.svg" alt="Discover" class="card-img" width="32"> <img src="' . plugins_url() . '/woocommerce-pay-fabric/images/amex.svg" alt="Amex" class="card-img" width="32"></p>';
        // Trying render payment form
        // Call orginal payment the Woocommerce
        $this->form();
    }

    private function get_gatewayids() {
        $gatewayids = array(
            'no' => 'Select Gateway'
        );
        if ($this->deviceId !== '' && $this->password !== '') {
            if ($this->get_token()) {
                $request = new WP_Http;
                $headers = array('authorization' => $this->token );
                $result = $request->request($this->process_uri . "setupid",
                    array(
                        'headers' => $headers,
                        'timeout' => $this->timeout,
                    ));
                $response = $result['body'];
                if (!empty($response)) {
                    // Convert the JSON into a multi-dimensional array
                    $responseArray = json_decode($response, true);
                    foreach ($responseArray as $gateway) {
                        $gatewayids[$gateway['Name']] = $gateway['Name'] . ' (' . $gateway['Processor'] . ')';
                    }
                }
            }
        }
        return $gatewayids;
    }

    /**
     * Initialize Gateway Settings Form Fields
     */
    public function init_form_fields() {

        $gatewayids = $this->get_gatewayids();
        $this->form_fields = apply_filters( 'wc_fabric_form_fields', array(
            'enabled' => array(
                'title' => 'Enable/Disable',
                'type' => 'checkbox',
                'label' => 'Enable PayFabric',
                'default' => 'yes',
            ),
            'title' => array(
                'title' => 'Title',
                'type' => 'text',
                'description' => '',
                'default' => 'PayFabric',
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => 'Description',
                'type' => 'textarea',
                'description' => '',
                'default' => 'Description here',
                'desc_tip' => true,
            ),
            'instructions' => array(
                'title' => 'Instructions',
                'type' => 'textarea',
                'description' => '',
                'default' => 'Instructions here',
                'desc_tip' => true,
            ),
            'deviceId' => array(
                'title' => 'Device ID',
                'type' => 'text',
                'description' => '',
                'default' => '',
                'desc_tip' => true,
            ),
            'password' => array(
                'title' => 'Device Password',
                'type' => 'password',
                'description' => '',
                'default' => '',
                'desc_tip' => true,
            ),
            'gatewayid' => array(
                'title' => 'Gateway Account Name',
                'type' => 'select',
                'description' => 'Required set Device ID and Password',
                'default' => '',
                'desc_tip' => true,
                'options' => $gatewayids,
            ),
            'book' => array(
                'title' => 'Book',
                'type' => 'checkbox',
                'default' => 'no',
                'description' => 'Enable Pre-Authorization Only',
            ),
            'production' => array(
                'title' => 'Production',
                'type' => 'checkbox',
                'default' => 'no',
                'description' => 'Enable Production',
            ),
        ));
    }

    /**
     * Output for the order received page
     */
    public function thankyou_page() {
        if ( $this->instructions ) {
            echo wpautop( wptexturize( $this->instructions ) );
        }
    }

    public function admin_options() {
        echo '<h3>PayFabric</h3>';
        echo '<p>Powered by Fresh Consulting</p>';
        echo '<table class="form-table">';
        $this->generate_settings_html();
        echo '</table>';
    }

    // Override method
    /**
     * Process the payment and return the result
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment( $order_id ) {
        $order = wc_get_order( $order_id );

        $payment_error = null;

        $process = $this->process_order_payment($order);

        if ( is_array($process) ) {
            $httpResponseCode = $process['response']['code'];

            if ($httpResponseCode >= 300) {
                // Handle status errors
                // Refer https://github.com/PayFabric/PayFabric-APIs/blob/v2/Sections/Errors.md
                $error_response = "";
                switch ($httpResponseCode) {
                case 400:
                    $error_response = "(400) The request is missing required information.";
                    break;
                case 401:
                    $error_response = "(401) The authorization is invalid or revoked.";
                    break;
                case 404:
                    $error_response = "(404) Endpoint not found.";
                    break;
                case 412:
                    $error_response = "(412) Missing fields or mandatory parameters.";
                    break;
                case 500:
                    $error_response = "(500) PayFabric server has encountered an error.";
                    break;
                default:
                    $error_response = "(" . $httpResponseCode . ") Unexpected error.";
                    break;
                }

                $payment_error = $error_response;
            }

            $response = $process['body'];
            // Convert the JSON into a multi-dimensional array
            $responseArray = json_decode($response, true);

            // Check error on alive
            if (!is_null($responseArray['PayFabricErrorCode'])) {
                // Handle errors
                $payment_error = $responseArray['Message'];
            }

            if ($responseArray['Status'] != 'Approved') {
                $payment_error = $responseArray['Status'] . ':' . $responseArray['Message'];
            }
        } else {
            // if $process isn't an array then it is an error
            $payment_error = $process;
        }

        // check the $payment_error array for entries
        if ( !empty($payment_error) ) {
            // add to error message, log, and return to checkout to display error message
            $this->log($payment_error);
            wc_add_notice( __('Payment error: ', 'woothemes') . $payment_error, 'error' );
            return;
        } else {
            // continue processing the order if there have been no errors

            // Is the customer a guest or logged in?
            $customer = $this->get_customer($order->id);
            $card_id = '';

            if (0 == $customer) { // Then they are a guest

              $customer_id = 'guest-'.$order->id;
              $new_card = $this->create_card($order, $customer_id);
              $card_id = json_decode($new_card['body'])->Result;

            } else {

              $customer_id = 'wp-user-'.$customer;
              // Set the Card
              $new_card = $this->create_card($order, $customer_id);
              $card_data = json_decode($new_card['body']);

              if (null !== $card_data) {
                $card_id = $card_data->Result;

              } else {
                // If there is an error
                // set variables for comparisons
                $order_info = array(
                  'card_number' => substr(str_replace( array( '/', ' '), '', $_POST['fc_fabric_gateway-card-number'] ), -4),
                  'card_expire' => str_replace( array( '/', ' '), '', $_POST['fc_fabric_gateway-card-expiry'] ),
                  'customer_id' => $customer_id
                );

                // loop through all cards
                $customer_cards = json_decode($this->get_cards($customer_id)['body']);
                foreach ($customer_cards as $card) {
                  // find the match and update, then return id

                  $card_info = array(
                    'card_number' => substr($card->Account, -4),
                    'card_expire' => $card->ExpDate,
                    'customer_id' => $card->Customer
                  );

                  if ( $order_info === $card_info ) {
                    // update
                    $card_id = $card->ID;
                    $this->update_card($order, $customer_id, $card_id);
                  }
                }
              }
            }
            // Add the card and order info to the order meta
            update_post_meta($order->post->ID, '_transaction_id', $responseArray['TrxKey']);
            update_post_meta($order->post->ID, '_wallet_id', $card_id);

            $card_number = str_replace( array( '/', ' '), '', $_POST['fc_fabric_gateway-card-number'] );
            $card_identifier = $this->identify_card($card_number);
            update_post_meta($order->post->ID, '_card_identifier', $card_identifier);

            // Void the auth
            $void = $this->void_order_auth($responseArray['TrxKey']);

            // Reduce stock levels
            $order->reduce_order_stock();
            // Remove cart
            WC()->cart->empty_cart();

            $order->update_status( 'processing', 'PayFabric Approved' );

            do_action('woocommerce_payment_complete', $order->id);

            // Return thank you redirect
            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url( $order ),
            );
        }

    }

    private function process_order_payment( $order ) {

        try {
            if (!$this->get_token()) {
                $this->log($responseArray['Message']);
                wc_add_notice( __('Payment error: ', 'woothemes') . 'Failed to get token.', 'error' );
                return;
            }

            $card_number = str_replace( array( '/', ' '), '', $_POST['fc_fabric_gateway-card-number'] );
            $card_expire = str_replace( array( '/', ' '), '', $_POST['fc_fabric_gateway-card-expiry'] );
            $card_identifier = $this->identify_card($card_number);
            $currency = $order->get_order_currency();
            $post_field = ('yes' === $this->is_book) ? 'Book' : 'Sale';
            $post_field = '{
            "Amount": "1",
            "BatchNumber": "",
            "Card": {
                "Account": "' . $card_number . '",
                "Billto": {
                "City": "' . $order->billing_city . '",
                "Country": "' . $order->billing_country . '",
                "Email": "' . $order->billing_email . '",
                "Line1": "' . $order->billing_address_1 . '",
                "Line2": "' . $order->billing_address_2 . '",
                "Line3": "",
                "Phone": "' . $order->billing_phone . '",
                "State": "' . $order->billing_state . '",
                "Zip": "' . $order->billing_postcode . '"
                },
                 "CardHolder": {
                    "DriverLicense": "",
                    "FirstName": "' . $order->billing_first_name . '",
                    "LastName": "' . $order->billing_last_name . '",
                    "MiddleName": "",
                    "SSN": ""
                },
                "Customer": "' . $order->billing_first_name . ' ' . $order->billing_last_name . '",
                "ExpDate": "' . $card_expire . '",
                "GPAddressCode": "",
                "GatewayToken": "",
                "Identifier": "' . $card_identifier . '",
                "IsDefaultCard": false,
                "IssueNumber": "",
                "UserDefine1": "",
                "UserDefine2": "",
                "UserDefine3": "",
                "UserDefine4": ""
            },
            "Currency": "' . $currency . '",
            "Customer": "' . $order->billing_first_name . ' ' . $order->billing_last_name . '",
            "Document": {
                "Head": [
                    {
                    "Name":"ORDER NUMBER",
                    "Value": "' . $order->get_order_number() . '",
                    }
                ],
                "Lines": [],
                "UserDefined": []
            },
            "PayDate": "",
            "ReferenceKey": null,
            "ReferenceTrxs": [],
            "ReqAuthCode": "",
            "SetupId": "' . $this->gatewayid . '",
            "Shipto": {
                "City": "' . $order->shipping_city . '",
                "Country": "' . $order->shipping_country . '",
                "Customer": "' . $order->shipping_first_name . ' ' . $order->shipping_last_name . '",
                "Email": "",
                "Line1": "' . $order->shipping_address_1 . '",
                "Line2": "' . $order->shipping_address_2 . '",
                "Line3": "",
                "Phone": "",
                "State": "' . $order->state . '",
                "Zip": "' . $order->shipping_postcode . '"
            },
            "TrxUserDefine1": "",
            "TrxUserDefine2": "",
            "TrxUserDefine3": "",
            "TrxUserDefine4": "",
            "Type": "' . $post_field . '"
            }';

            $request = new WP_Http;
            $headers = array('authorization' => $this->token, 'content-type' => 'application/json');
            $result = $request->request($this->process_uri . "transaction/process?cvc=" . $_POST['fc_fabric_gateway-card-cvc'],
                array(
                    'method' => 'POST',
                    'body'    => $post_field,
                    'headers' => $headers,
                    'timeout' => $this->timeout,
                ));
            return $result;

        } catch ( Exception $e ) {
            $this->log($e->getMessage());
            return $e->getMessage();
        }
    }

    private function get_customer($order_id) {
      global $wpdb;
      $customer_id = $wpdb->get_var( $wpdb->prepare( 
        "SELECT meta_value
          FROM wp_postmeta
          WHERE meta_key = '_customer_user' AND post_id = %d",
        $order_id
      ));

      return $customer_id;
    }

    private function create_card( $order, $customer_id ) {
        try {
            if (!$this->get_token()) {
                $this->log($responseArray['Message']);
                wc_add_notice( __('Payment error: ', 'woothemes') . 'Failed to get token.', 'error' );
                return;
            }

            $card_number = str_replace( array( '/', ' '), '', $_POST['fc_fabric_gateway-card-number'] );
            $card_expire = str_replace( array( '/', ' '), '', $_POST['fc_fabric_gateway-card-expiry'] );
            $post_field = '{
                "Account": "' . $card_number . '",
                "Billto": {
                  "City": "' . $order->billing_city . '",
                  "Country": "' . $order->billing_country . '",
                  "Email": "' . $order->billing_email . '",
                  "Line1": "' . $order->billing_address_1 . '",
                  "Line2": "' . $order->billing_address_2 . '",
                  "Line3": "",
                  "Phone": "' . $order->billing_phone . '",
                  "State": "' . $order->billing_state . '",
                  "Zip": "' . $order->billing_postcode . '"
                },
                "CardHolder": {
                    "FirstName": "' . $order->billing_first_name . '",
                    "LastName": "' . $order->billing_last_name . '"
                },
                "Customer": "' . $customer_id . '",
                "ExpDate": "' . $card_expire . '",
                "Tender": "CreditCard",
            }';

            $request = new WP_Http;
            $headers = array('authorization' => $this->token, 'content-type' => 'application/json');
            $result = $request->request($this->process_uri . "wallet/create",
                array(
                    'method' => 'POST',
                    'body'    => $post_field,
                    'headers' => $headers,
                    'timeout' => $this->timeout,
                ));
            return $result;

        } catch ( Exception $e ) {
              $this->log($e->getMessage());
              return $e->getMessage();
        }
    }

    private function update_card( $order, $customer_id, $card_id ) {
        try {
            if (!$this->get_token()) {
                $this->log($responseArray['Message']);
                wc_add_notice( __('Payment error: ', 'woothemes') . 'Failed to get token.', 'error' );
                return;
            }
            
            $card_expire = str_replace( array( '/', ' '), '', $_POST['fc_fabric_gateway-card-expiry'] );
            $post_field = '{
                "ID": "' . $card_id . '",
                "Billto": {
                  "City": "' . $order->billing_city . '",
                  "Country": "' . $order->billing_country . '",
                  "Email": "' . $order->billing_email . '",
                  "Line1": "' . $order->billing_address_1 . '",
                  "Line2": "' . $order->billing_address_2 . '",
                  "Line3": "",
                  "Phone": "' . $order->billing_phone . '",
                  "State": "' . $order->billing_state . '",
                  "Zip": "' . $order->billing_postcode . '"
                },
                "CardHolder": {
                    "FirstName": "' . $order->billing_first_name . '",
                    "LastName": "' . $order->billing_last_name . '"
                },
                "Customer": "' . $customer_id . '",
                "ExpDate": "' . $card_expire . '",
                "Tender": "CreditCard",
            }';

            $request = new WP_Http;
            $headers = array('authorization' => $this->token, 'content-type' => 'application/json');
            $result = $request->request($this->process_uri . "wallet/update",
                array(
                    'method' => 'POST',
                    'body'    => $post_field,
                    'headers' => $headers,
                    'timeout' => $this->timeout,
                ));
            return $result;

        } catch ( Exception $e ) {
              $this->log($e->getMessage());
              return $e->getMessage();
        }
    }

    private function get_cards( $customer_id ) {
      try {
          if (!$this->get_token()) {
              $this->log($responseArray['Message']);
              wc_add_notice( __('Payment error: ', 'woothemes') . 'Failed to get token.', 'error' );
              return;
          }

          $request = new WP_Http;
          $headers = array('authorization' => $this->token, 'content-type' => 'application/json');
          $result = $request->request($this->process_uri . "wallet/get/" . $customer_id . "?tender=CreditCard",
              array(
                  'method' => 'GET',
                  'headers' => $headers,
                  'timeout' => $this->timeout,
              ));
          return $result;

      } catch ( Exception $e ) {
            $this->log($e->getMessage());
            return $e->getMessage();
      }
    }

    private function void_order_auth( $transaction_id ) {

        try {
            if (!$this->get_token()) {
                $this->log($responseArray['Message']);
                wc_add_notice( __('Payment error: ', 'woothemes') . 'Failed to get token.', 'error' );
                return;
            }
            $request = new WP_Http;
            $headers = array('authorization' => $this->token, 'content-type' => 'application/json');
            $result = $request->request($this->process_uri . "reference/" . $transaction_id . "?trxtype=VOID",
                array(
                    'method' => 'GET',
                    'headers' => $headers,
                    'timeout' => $this->timeout,
                ));
            return $result;

        } catch ( Exception $e ) {
          $this->log($e->getMessage());
          return $e->getMessage();
      }
    }

    private function get_token() {
        $request = new WP_Http;
        $headers = array('authorization' => $this->deviceId . "|" . $this->password );
        $result = $request->request($this->process_uri . "token/create",
            array(
                'headers' => $headers,
                'timeout' => $this->timeout,
            ));

        if (isset($result->errors)) {
            if (is_admin()) {
                add_action( 'admin_notices', __NAMESPACE__ . '\\token_admin_notice__error' );

            } else {
                if ( is_array($result->errors) ) {
                    foreach ($result->errors as $error) {
                        wc_add_notice(__('Payment error: ', 'woothemes') . $error[0], 'error');
                    }
                } else {
                    wc_add_notice(__('Payment error: ', 'woothemes') . $result->errors, 'error');
                }
            }
            return false;
        }

        $httpResponseCode = $result['response']['code'];

        if ($httpResponseCode >= 300) {
            // Handle errors
            $error_message = 'Token error. response ' . $httpResponseCode;

            if (is_admin()) {
                add_action( 'admin_notices', __NAMESPACE__ . '\\token_admin_notice__error' );
            } else {
                wc_add_notice(__('Payment error: ', 'woothemes') . $error_message, 'error');
            }

            $this->log($error_message);
            return false;
        }

        $response = $result['body'];
        // Convert the JSON into a multi-dimensional array
        $responseArray = json_decode($response, true);
        // Output the results of the request
        $this->token = $responseArray["Token"];
        return true;
    }

    private function identify_card($card_number) {
      $identifier = null;
      $cards = array(array('/^4[0-9]{12}(?:[0-9]{3})?$/', 'VISA'),
        array('/^(?:5[1-5][0-9]{2}|222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720)[0-9]{12}$/', 'MC'),
        array('/^3[47][0-9]{13}$/', 'AMEX'),
        array('/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/', 'Diners Club'),
        array('/^6(?:011|5[0-9]{2})[0-9]{12}$/', 'DISCVR'),
        array('/^(?:2131|1800|35\d{3})\d{11}$/', 'JCB')
      );

      foreach ($cards as $card) {
        preg_match($card[0], $card_number, $matches);
        if (0 < count($matches)) {
          $identifier = $card[1];
        }
      }

      return $identifier;
    }

    private function log( $log_message ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
            error_log( print_r( compact( 'log_message' ), true ) );
        }
    }

} // end class


/**
 * Add the gateway to WC Available Gateways
 *
 * @since 0.0.1
 * @param
 * @return
 */
function wc_fabric_add_to_gateways( $methods ) {
    // Add
    $methods [] = __NAMESPACE__ . '\\WC_Fabric_Gateway';
    return $methods ;
}
// Add payment method to the WooCommerce
add_filter( 'woocommerce_payment_gateways', __NAMESPACE__ .'\\wc_fabric_add_to_gateways' );


function wc_fabric_gateway_plugin_links( $links ) {

    $plugin_links = array(
        '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=fc_fabric_gateway' ) . '"> Settings </a>'
    );

    return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), __NAMESPACE__ . '\\wc_fabric_gateway_plugin_links' );


function wc_fabric_gateway_init() {
    if (class_exists('WC_Fabric_Gateway')) {
        new WC_Fabric_Gateway();
    }
}
/**
 * PayFabric Payment Gateway
 *
 *
 * @class WC_Fabric_Gateway
 * @extends WC_Payment_Gateway
 * @version 0.0.1
 * @package WooCommerce/Classes/Payment
 * @author Fresh Consulting
 */
// Call back after actived we load our setting into Woo here
// This gets function to listener queue (It run alway)
add_action( 'plugins_loaded', __NAMESPACE__ . '\\wc_fabric_gateway_init' );


function token_admin_notice__error() {
    $class = 'notice notice-error';
    $message = 'Error: Invalid token, please verify your Device ID and Device Password again?';

    printf( '<div class="%1$s"><h2>PayFabric plugin</h2><p>%2$s</p></div>', $class, $message );
}
