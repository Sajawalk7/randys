<?php
  namespace Roots\Sage\RANDYS;

  // Avoid this file if the avatax plugin is not installed or turned on
  if ( !function_exists( 'wc_avatax' ) ) {
    return;
  }

  // Pull in the GetBoxesForOrder, we will need this to get the correct tax per shipment
  require_once( __DIR__ . "/../cartonization/GetBoxesForOrder.php" );

  // Define the options key
  define("ADDRESS_OPTIONS_KEY", "options_warehouse_addresses");

  // Put together the warehouse address page for the admin panel
  acf_add_options_page(array(
    'page_title' => 'Warehouse Addresses',
    'menu_title' => 'Warehouse Addresses',
    'menu_slug' => 'warehouse-addresses',
    'capability' => 'edit_posts',
  ));

  // The subkeys are:
  //    'address_line'
  //    'state_code'
  //    'city'
  //    'zip'

  // Recreate the WC_Cart to add our own attribute to it
  class FC_Cart extends \WC_Cart {
    public $origin_state_code;
  }

  // Remove the cart page calculate tax action
  remove_action( 'woocommerce_after_calculate_totals', array( wc_avatax()->get_checkout_handler(),  'calculate_taxes' ) );
  // Cover it up with our own function to handle taxes via shipment
  add_action( 'woocommerce_after_calculate_totals', __NAMESPACE__ . '\\custom_calculate_taxes');

  function custom_calculate_taxes( $cart ) {

    global $wpdb;

    // Don't calculate the taxes if we're not ready to calculate the taxes
    if ( ! is_ready_for_calculation() ) {
      // Not yet ready to calculate (WooCommerce not ready, Missing important address items, ect.)
      return;
    }

    // Get the shipping methods selected for this user
    $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );

    /*
     * GET AND SORT THROUGH THE SHIPMENTS
     */

    // Get the separate shipments
    $packages = WC()->shipping->get_packages();

    // Make a variable to put the taxes into
    $taxes = array();
    $taxes_detail = array();
    $total_order_tax = 0;
    $total_order_shipping_tax = 0;
    $is_first_shipment = true;

    // Query up the user's tax information
    $customer_info = $wpdb->get_row($wpdb->prepare('SELECT * FROM randys_customers WHERE WOOCustID = %d', get_current_user_id()));

    // Loop through each of the shipments
    foreach ($packages as $package_index => $package) {

      // If a product is OUT OF STOCK (keyed as OOS)
      // we cannot calculate the tax for this order
      if ( $package['shipment_origin_warehouse'] == 'OOS' ) {
        continue;
      }

      // Get the origin warehouse id
      $origin_warehouse_id = $package['shipment_origin_warehouse'];

      // Check to see if this user is tax exempt
      $tax_key = $origin_warehouse_id . "TaxExempt";
      if ($customer_info && $customer_info->$tax_key == 1) {
        // This customer is tax exempt from this state and should not be charged tax

        // Save the totals for this shipment
        $taxes[$origin_warehouse_id] = array(
          'total_tax' => 0,
          'total_shipping_tax' => 0,
        );
        $taxes_detail[$origin_warehouse_id] = [];
        $is_first_shipment = false;

        // Go to the next shipment
        continue;
      }

      // Create a fake cart for this shipment
      $ship_cart = new FC_Cart();

      // Set the state code for the origin of the shipment
      $ship_cart->origin_state_code = $origin_warehouse_id;


      // add the items
      // NOTE: Don't use $cart->add_to_cart because that would eventually call the
      // action 'woocommerce_after_calculate_totals' resulting in an infinite loop
      $ship_cart->cart_contents = $package['contents'];


      // Dropship fee is only added to the first shipment
      // NOTE when processing payment and saving the order as shipments fees must be handled the same as they are here
      if ($is_first_shipment) {
          $ship_cart->fees = $cart->get_fees();
      }

      // Make sure we have shipping methods before trying to add them
      $chosen_method = false;
      if ( ! empty( $chosen_methods[ $package_index ] ) ) {
          $chosen_method = $chosen_methods[ $package_index ];
      }

      if ( $chosen_method ) {
          $rate = $package['rates'][ $chosen_method ];
          // Apply the correct shipping method to this shipment
          // Now let's tack on this cost as a shipping price for this cart
          $ship_cart->shipping_total = $rate->cost;
      }


      // Ping the API with the cart to find the current tax.
      $api_result = wc_avatax()->get_api()->calculate_checkout_tax( $ship_cart );

      // Get the data from the API result
      $total_shipment_tax = $api_result->get_total_tax();
      $api_result_lines = $api_result->get_lines();
      $total_shipment_shipping_tax = 0;

      // Go through each item and apply their tax.
      foreach ( $api_result_lines as $line ) {

        $line_id  = $line['id'];
        $line_tax = $line['total'];

        // If this is the shipping line, add to the shipping tax total
        if ( 'shipping' == $line_id ) {

          $total_shipment_shipping_tax += $line_tax;

        } elseif ( isset( $cart->cart_contents[ $line_id ] ) ) {

          // Increase the tax for this item
          $cart->cart_contents[ $line_id ]['line_tax']          += $line_tax;
          $cart->cart_contents[ $line_id ]['line_subtotal_tax'] += $line_tax;

          // Save this tax source
          $cart->cart_contents[ $line_id ]['line_tax_data']['total']['custom_avatax']    = $line_tax;
          $cart->cart_contents[ $line_id ]['line_tax_data']['subtotal']['custom_avatax'] = $line_tax;
        }
      }

      // Save the totals for this shipment
      $taxes[$origin_warehouse_id] = array(
        'total_tax' => $total_shipment_tax,
        'total_shipping_tax' => $total_shipment_shipping_tax,
      );
      $taxes_detail[$origin_warehouse_id] = $api_result_lines;

      // Update the totals for the whole master order
      $total_order_tax += $total_shipment_tax;
      $total_order_shipping_tax += $total_shipment_shipping_tax;
      $is_first_shipment = false;
    }

    // Now that we have the taxes, save them in the session
    WC()->session->set( 'new_taxes', $taxes );
    WC()->session->set( 'new_taxes_detail', $taxes_detail );

    // Get the subtotal (TAX - SHIPPING_TAX)
    $order_subtotal_tax = $total_order_tax - $total_order_shipping_tax;

    // Save to the cart this specific tax source
    $cart->taxes['custom_avatax']          = $order_subtotal_tax;
    $cart->shipping_taxes['custom_avatax'] = $total_order_shipping_tax;

    // Save to the cart the tax subtotal and the tax shipping
    $cart->tax_total += $order_subtotal_tax;
    $cart->shipping_tax_total += $total_order_shipping_tax;

    // Increase the subtotal and the total
    $cart->total += $total_order_tax;
    $cart->subtotal += $order_subtotal_tax;
  }


  // Remove all actions that do the final tax calculation
  remove_action( 'woocommerce_payment_complete', array( wc_avatax()->get_order_handler(), 'calculate_taxes' ) );
  remove_action( 'woocommerce_order_status_on-hold_to_processing', array( wc_avatax()->get_order_handler(), 'calculate_taxes' ) );
  remove_action( 'woocommerce_order_status_on-hold_to_completed', array( wc_avatax()->get_order_handler(), 'calculate_taxes' ) );
  remove_action( 'woocommerce_order_status_failed_to_processing', array( wc_avatax()->get_order_handler(), 'calculate_taxes' ) );
  remove_action( 'woocommerce_order_status_failed_to_completed', array( wc_avatax()->get_order_handler(), 'calculate_taxes' ) );

  // Overwrite the hooks with our own hooks
  add_action( 'woocommerce_payment_complete', __NAMESPACE__ . '\\calculate_taxes' );
  add_action( 'woocommerce_order_status_on-hold_to_processing', __NAMESPACE__ . '\\calculate_taxes' );
  add_action( 'woocommerce_order_status_on-hold_to_completed', __NAMESPACE__ . '\\calculate_taxes' );
  add_action( 'woocommerce_order_status_failed_to_processing', __NAMESPACE__ . '\\calculate_taxes' );
  add_action( 'woocommerce_order_status_failed_to_completed', __NAMESPACE__ . '\\calculate_taxes' );

  function calculate_taxes( $order_id ) {
    // Final calculations are skipped to avoid charging double the tax amount.
    // This is because taxes have been added to the cart as a fee, and will be charged to the user.
    // So we do not need to add on an order_tax when the tax is already being charged to the customer.

    // NOTE: Be aware of the $commit here and how it affects 'payment' VS 'estimate'
  }


  // Remove actions that process the order (the above 'calculate_taxes' normally calls 'process_order')
  remove_action( 'woocommerce_order_action_wc_avatax_send', array( wc_avatax()->get_order_handler(),  'process_order' ) );

  // Overwrite action with our own action
  add_action( 'woocommerce_order_action_wc_avatax_send', __NAMESPACE__ . '\\process_order' );

  // Custom function to process the order
  function process_order(WC_Order $order ) {
    // Orders should not be processed via plugin
  }


  // Remove actions for voids/returns
  add_action( 'woocommerce_order_status_cancelled', array( wc_avatax()->get_order_handler(), 'void_order' ) );
  add_action( 'wc_avatax_after_order_voided', array( wc_avatax()->get_order_handler(), 'void_order_refunds' ) );

  // Add action for the voids/returns
  add_action( 'woocommerce_order_status_cancelled', __NAMESPACE__ . '\\deny_refund' );
  add_action( 'wc_avatax_after_order_voided', __NAMESPACE__ . '\\deny_refund' );

  // If there is an attempt at a refund/void/return, deny it
  function deny_refund() {
    throw new Exception("Refunds are not allowed", 1);
  }


  /*
   * ADD FILTER FOR THE ADDRESS CORRECTION
   */

  // Hook into the filter so we can overwrite it
  add_filter('wc_avatax_checkout_origin_address', __NAMESPACE__ . '\\correct_avatax_address', 10, 2);

  // Here we get the correct address to show.
  function correct_avatax_address( $old_address, $cart ) {

    // Get the count of the warehouse_addresses
    $wh_address_count = (int)get_option(ADDRESS_OPTIONS_KEY);

    // Make an empty array for the addresses to be stored in
    $wh_address_list = array();

    // Loop through the indexes and add each warehouse address
    for ($a_index = 0; $a_index < $wh_address_count; $a_index++) {

      // Make the root option
      $option_root = ADDRESS_OPTIONS_KEY . '_' . $a_index . '_';

      // Create the address
      $address = array(
        'address_1' => get_option($option_root . 'address_line'),
        'city'      => get_option($option_root . 'city'),
        'state'     => get_option($option_root . 'state_code'),
        'postcode'  => get_option($option_root . 'zip'),
        'country'   => 'US',
      );

      // Put the address into the address list
      // NOTE: More than one address in a single state will break this code
      $wh_address_list[get_option($option_root . 'state_code')] = $address;
    }

    // If the state code is in the cart...
    if (property_exists($cart, 'origin_state_code')) {
      // Send our own address
      return $wh_address_list[$cart->origin_state_code];
    } else {
      throw new Exception('Incorrect cart type WC_Cart passed to avatax. Expected FC_Cart');
    }
  }


// Finds out if the cart is ready for calculation
function is_ready_for_calculation() {

  // We need to call a private function on the class `wc_avatax()->get_checkout_handler()`. Function found here:
  // wp-content/plugins/woocommerce-avatax/includes/class-wc-avatax-checkout-handler.php :: ready_for_calculation()

  // Getting a private function to run is a bit hacky, but seems to be the most
  // accurate way to get the answer without copying code or editing the plugin.

  // Get the checkout handler's reflection
  $reflection = new \ReflectionObject( wc_avatax()->get_checkout_handler() );

  // Get the method from the reflection
  $method = $reflection->getMethod( 'ready_for_calculation' );

  // Make that method public
  $method->setAccessible( true );

  // Send back the result from the invoked private function
  return $method->invoke( wc_avatax()->get_checkout_handler() );
}


// Order cannot be created unless a rate code is supplied
add_filter( 'woocommerce_rate_code', __NAMESPACE__ . '\\set_custom_tax_rate_code', 10, 2 );
function set_custom_tax_rate_code( $code_string, $key ) {
  // Only change the code string if we find our custom key
  return ( 'custom_avatax' === $key ? 'CUSTOM_AVATAX' : $code_string );
}
