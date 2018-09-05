<?php
namespace Roots\Sage\RANDYS;

/*******************
 * Checkout Fields *
 *******************/

function vehicle_checkout_fields( $checkout ) {
  echo '</div></div></div></div></div><div class="section section--tan"><div class="container"><div class="row"><div class="col-sm-12"><div id="vehicle-information"><h3>' . __('Vehicle Information') . '</h3><div class="card card--padded"><p><strong>Would you like to have one of our parts experts review your vehicle information to ensure that your parts are compatible?</strong></p>';

  woocommerce_form_field_radio( 'review', array(
    'type'          => 'select',
    'class'         => array('radio-group', 'form-row-wide'),
    'label'         => __(''),
    'placeholder'   => __(''),
    'required'      => false,
    'options'       => array(
        // Link is tied to Page-Builders FAQ. If moved or deleted this link will need to be updated also
      'No Thanks'     => 'No Thanks. Skip the Review. <a href="/customer-service/policies/#collapse-3">(Returns & Cancellations)</a>',
      'Yes'           => 'Yes, I’d like to enter my vehicle information for review.',
    ),
  ), $checkout->get_value( 'review' ) );

  echo '<div class="vehicle-info active">';

  woocommerce_form_field( 'make_model', array(
    'type'          => 'text',
    'class'         => array('form-row-third', 'form-row-first'),
    'label'         => __('Make / Model'),
    'placeholder'   => __(''),
    ), $checkout->get_value( 'make_model' ));

    woocommerce_form_field( 'year', array(
    'type'          => 'text',
    'class'         => array('form-row-third', 'form-row-middle'),
    'label'         => __('Year'),
    'placeholder'   => __(''),
    ), $checkout->get_value( 'year' ));

    woocommerce_form_field( 'axle', array(
    'type'          => 'text',
    'class'         => array('form-row-third', 'form-row-last'),
    'label'         => __('Axle'),
    'placeholder'   => __(''),
    ), $checkout->get_value( 'axle' ));

  woocommerce_form_field( 'questions_comments', array(
    'type'          => 'textarea',
    'class'         => array('form-row-wide'),
    'label'         => __('Questions / Comments (500 characters max)'),
    'placeholder'   => __(''),
    'maxlength'     => 500,
  ), $checkout->get_value( 'questions_comments' ));

  wp_nonce_field( 'vehicle_nonce_action', 'vehicle_nonce_field' );

  echo '</div></div>'; // .card

}
add_action( 'woocommerce_after_order_notes', __NAMESPACE__ . '\\vehicle_checkout_fields' );

/**
 * Update the order meta with Vehicle Information field values
 */
function my_custom_checkout_field_update_order_meta( $order_id ) {
  $terms_nonce = wp_verify_nonce( $_POST['vehicle_nonce_field'], 'vehicle_nonce_action' );
  if ( $terms_nonce ) {
    if ( ! empty( $_POST['make_model'] ) ) {
      add_post_meta( $order_id, '_make_model', sanitize_text_field( $_POST['make_model'] ) );
    }
    if ( ! empty( $_POST['year'] ) ) {
      add_post_meta( $order_id, '_year', sanitize_text_field( $_POST['year'] ) );
    }
    if ( ! empty( $_POST['axle'] ) ) {
      add_post_meta( $order_id, '_axle', sanitize_text_field( $_POST['axle'] ) );
    }
    if ( ! empty( $_POST['questions_comments'] ) ) {
      add_post_meta( $order_id, '_questions_comments', sanitize_text_field( $_POST['questions_comments'] ) );
    }
    // process PO Number if available
    if ( ! empty( $_POST['po_number'] ) ) {
      add_post_meta( $order_id, '_po_number', sanitize_text_field( $_POST['po_number'] ) );
    }
    // process Shipper ID number if available
    if ( ! empty( $_POST['shipper_id_number'] ) ) {
      add_post_meta( $order_id, '_shipper_id_number', sanitize_text_field( $_POST['shipper_id_number'] ) );
    }
  }
}
add_action( 'woocommerce_checkout_update_order_meta', __NAMESPACE__ . '\\my_custom_checkout_field_update_order_meta' );


/**
 * Add dropship order checkbox to shipping address fields
 */
function dropship_checkbox( $checkout ) {

  woocommerce_form_field( 'dropship_order', array(
      'type' => 'checkbox',
      'class' => array('form-row-wide'),
      'label' => __('Dropship Order', 'woocommerce'),
      'required' => false,
  ), WC()->session->get( 'use_dropship', false ));

}
add_action( 'woocommerce_after_checkout_shipping_form', __NAMESPACE__ . '\\dropship_checkbox' );

function process_dropship_checkbox( $post_data ) {

	if ( ! empty( $post_data ) ) {

		// parse the post_data string
		$post_data = explode( '&', $post_data );
		foreach ( $post_data as $pair ) {
			$pair = explode( '=', $pair );
			$post_data[ $pair[0] ] = urldecode( $pair[1] );
		}

        if ( array_key_exists( 'dropship_order', $post_data ) && $post_data['dropship_order'] && array_key_exists( 'ship_to_different_address', $post_data ) && $post_data['ship_to_different_address'] ) {
            WC()->session->set( 'use_dropship', true );
        } else {
            WC()->session->set( 'use_dropship', false );
        }
	}

}
add_action('woocommerce_checkout_update_order_review', __NAMESPACE__ . '\\process_dropship_checkbox');


/**
 * Add shipper id checkbox to checkout page
 */
function shipperid_checkbox( $checkout ) {
  $shipping_id = get_shipping_id();

  if ( is_user_logged_in() && is_wholesale() && $shipping_id ) {
    woocommerce_form_field( 'shipper_id', array(
        'type' => 'checkbox',
        'class' => array('form-row-wide'),
        'label' => __('Use Shipping Account Number', 'woocommerce'),
        'required' => false,
    ), WC()->session->get( 'use_shipper_id', false ));

    echo '<input type="hidden" class="input-hidden" name="shipper_id_number" id="shipper_id_number" value="' . $shipping_id . '">';
  }

}
add_action( 'woocommerce_checkout_order_review', __NAMESPACE__ . '\\shipperid_checkbox', 5 );

function process_shipperid_checkbox( $post_data ) {

	if ( ! empty( $post_data ) ) {

		// parse the post_data string
		$post_data = explode( '&', $post_data );
		foreach ( $post_data as $pair ) {
			$pair = explode( '=', $pair );
			$post_data[ $pair[0] ] = urldecode( $pair[1] );
		}

        if ( array_key_exists( 'shipper_id', $post_data ) && $post_data['shipper_id'] ) {
            WC()->session->set( 'use_shipper_id', true );
        } else {
            WC()->session->set( 'use_shipper_id', false );
        }
	}

}
add_action('woocommerce_checkout_update_order_review', __NAMESPACE__ . '\\process_shipperid_checkbox');


/**
 * Add PO Number field to checkout page
 */
function po_number( $checkout ) {
  if ( is_user_logged_in() && is_wholesale() ) {
    echo '<div class="container terms">';
    woocommerce_form_field( 'po_number', array(
        'type' => 'text',
        'class' => array('form-row-first'),
        'label' => __('PO Number (if applicable)', 'woocommerce'),
        'placeholder' => 'PO Number',
        'required' => false,
    ), $checkout->get_value( 'po_number' ));
    echo '</div>';
  }

}
add_action( 'woocommerce_review_order_after_payment', __NAMESPACE__ . '\\po_number', 5 );

function process_po_number( $post_data ) {

	if ( ! empty( $post_data ) ) {

		// parse the post_data string
		$post_data = explode( '&', $post_data );
		foreach ( $post_data as $pair ) {
			$pair = explode( '=', $pair );
			$post_data[ $pair[0] ] = urldecode( $pair[1] );
		}

    if ( array_key_exists( 'po_number', $post_data ) && $post_data['po_number'] ) {
        WC()->session->set( 'po_number', $post_data['po_number'] );
    } else {
        WC()->session->set( 'po_number', null );
    }
	}

}
add_action('woocommerce_checkout_update_order_review', __NAMESPACE__ . '\\process_po_number');


/**
 * Add terms checkbox field to the checkout
 */
function terms_field($checkout) {
  echo '<div class="container terms">';
  woocommerce_form_field( 'terms', array(
    'type'          => 'checkbox',
    'class'         => array('form-row-wide'),
    'label'         => __('I have read and understand this site\'s <a href="/customer-service/policies" target="_blank">Terms of Agreement</a> and <a href="/customer-service/policies" target="_blank">Restocking Policy</a>'),
    'placeholder'   => __(''),
    'required'      => true,
  ), $checkout->get_value( 'terms' ));

  wp_nonce_field( 'terms_nonce_action', 'terms_nonce_field' );

  echo '</div>';
}
add_action( 'woocommerce_review_order_after_payment', __NAMESPACE__ . '\\terms_field', 10, 1 );

/**
 * Process the checkout to make sure the terms checkbox is checked
 */
function checkbox_field_process() {
  $terms_nonce = wp_verify_nonce( $_POST['terms_nonce_field'], 'terms_nonce_action' );
  // Check if set, if its not set add an error.
  if ( $terms_nonce && empty($_POST['terms']) ) {
    wc_add_notice( __( '<strong>Please agree to our terms and conditions.</strong>' ), 'error' );
  }
}
add_action('woocommerce_checkout_process', __NAMESPACE__ . '\\checkbox_field_process');

// some things should only happen when the customer is actually trying to place the order
function check_for_place_order_request() {
    if ( ! defined( 'IS_PLACE_ORDER_REQUEST' ) ) {
        define( 'IS_PLACE_ORDER_REQUEST', true );
    }
}
add_action('woocommerce_before_checkout_process', __NAMESPACE__ . '\\check_for_place_order_request');
add_filter( 'woocommerce_is_checkout', __NAMESPACE__ . '\\check_for_place_order_request', 10, 1 );

// ensure no out of stock items in cart
function check_for_out_of_stock_items($packages) {
  //echo "<pre>";print_r($packages);
    if ( defined( 'IS_PLACE_ORDER_REQUEST' ) && IS_PLACE_ORDER_REQUEST ) {
        $cart = WC()->cart->get_cart();
        foreach ($packages as $package_index => $package) {
            if ( is_array($package) && array_key_exists('shipment_origin_warehouse', $package) && $package['shipment_origin_warehouse'] == 'OOS' ) {
                $message = 'Stock availability of the following items is currently limited. We have adjusted your order as follows. Please try again.';
                $message .= '<ul class="woocommerce-oos">';
                foreach ($package['contents'] as $package_key => $package_item) {
                    $message .= '<li>';
                    $message .= 'Removed <strong>' . $package_item['quantity'] . '</strong> unit(s) of Part Number <strong class="text-uppercase">' . $package_item['data']->post->post_name . '</strong>';
                    $message .= '</li>';
                    $cart_item_key = WC()->cart->generate_cart_id($package_item['product_id']);
                    // new quantity = current quantity - OOS quantity
                    $new_quantity = $cart[$cart_item_key]['quantity'] - $package_item['quantity'];
                    // OOS items are already excluded from totals
                    WC()->cart->set_quantity( $cart_item_key, $new_quantity, false );
                }
                $message .= '</ul>';

                // remove the OOS package
                unset($packages[$package_index]);

                // set refresh_totals to hide the now-removed OOS products on the checkout page
                WC()->session->set( 'refresh_totals', true );

                wc_add_notice( $message, 'error' );
            }
        }
    }
    return $packages;
}
// run after packages are created
add_filter('woocommerce_cart_shipping_packages', __NAMESPACE__ . '\\check_for_out_of_stock_items', 20);


/****************
 * Admin Fields *
 ****************/

/**
 * Display Vehicle Information field values on the order edit page
 */
function my_custom_checkout_field_display_admin_order_meta($order){
  echo '<div class="clear"></div><h3>Vehicle Information</h3>';
  echo '<p class="form-field form-field-wide wc-customer-user"><strong>'.__('Review').':</strong> ' . get_post_meta( $order->id, 'Review', true ) . '<br>';
  echo '<strong>'.__('Make / Model').':</strong> ' . get_post_meta( $order->id, 'Make / Model', true ) . '<br>';
  echo '<strong>'.__('Year').':</strong> ' . get_post_meta( $order->id, 'Year', true ) . '<br>';
  echo '<strong>'.__('Axle').':</strong> ' . get_post_meta( $order->id, 'Axle', true ) . '<br>';
  echo '<strong>'.__('Questions / Comments').':</strong> ' . get_post_meta( $order->id, 'Questions / Comments', true ) . '<br><br><br></p>';
}

add_action( 'woocommerce_admin_order_data_after_order_details', __NAMESPACE__ . '\\my_custom_checkout_field_display_admin_order_meta', 10, 1 );



/********************
 * Manage Addresses *
 ********************/

/**
 * Update order with shipping address
 */
function shipping_address_update_order_meta( $order_id ) {
  $shipping_fields = array(
    'shipping_first_name',
    'shipping_last_name',
    'shipping_company',
    'shipping_country',
    'shipping_address_1',
    'shipping_address_2',
    'shipping_city',
    'shipping_state',
    'shipping_postcode'
  );

  if ( check_ajax_referer( '_wpnonce', 'nonce', false ) && !empty($_POST['ship_to_different_address']) ) {
    foreach ($shipping_fields as $field) {
      if (! empty( $_POST[$field] ) ) {
        update_post_meta( $order_id, '_'.$field, sanitize_text_field( $_POST[$field] ) );
      }
    }

  }
}
add_action( 'woocommerce_checkout_update_order_meta', __NAMESPACE__ . '\\shipping_address_update_order_meta' );

/**
 * Update Customer Meta with shipping address if dropship order is not checked
 */
function update_customer_data( $true, $instance ) {
  $user_id = get_current_user_id();
  $shipping_fields = array(
    'shipping_first_name',
    'shipping_last_name',
    'shipping_company',
    'shipping_country',
    'shipping_address_1',
    'shipping_address_2',
    'shipping_city',
    'shipping_state',
    'shipping_postcode'
  );

  $use_dropship = WC()->session->get('use_dropship', false);
  if ( check_ajax_referer( '_wpnonce', 'nonce', false ) && ! empty( $_POST['ship_to_different_address'] ) && ! $use_dropship ) {
    foreach ($shipping_fields as $field) {
      update_user_meta( $user_id, $field, sanitize_text_field( $_POST[$field] ) );
    }
  }
  return $true;
};

// add the filter
add_filter( 'woocommerce_checkout_update_customer_data', __NAMESPACE__ . '\\update_customer_data', 10, 2 );



/***********
 * Coupons *
 ***********/

/**
 * Move coupon form on checkout page
 */
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
add_action( 'woocommerce_after_checkout_form', 'woocommerce_checkout_coupon_form' );

// hide coupon form
function hide_coupon() {
	wc_enqueue_js( '
		$( "a.showcoupon" ).parent().hide();
	');
}
add_action( 'woocommerce_before_checkout_form', __NAMESPACE__ . '\\hide_coupon' );

// display fake coupon form
function fake_coupon() {
	 echo '<p class="text-right"> Have a coupon? <a href="#" id="show-coupon-form">Click here to enter your code</a>.</p><div class="coupon-form"><p class="form-row form-row-wide"><input aria-label="Coupon code" type="text" name="coupon_code_view" class="input-text" placeholder="Coupon code" id="coupon_code_copy_view" value="" /><input aria-label="Coupon code" type="hidden" name="coupon_code" class="input-text" placeholder="Coupon code" id="coupon_code_copy" value="" /></p><p class="form-row form-row-wide"><a href="#" class="button button--full-width" name="apply_coupon">Apply Coupon</a></p></div>';
    ?>
    <script type="text/javascript">
        jQuery(function($){
          // Added For Discount Prefix Cupon
        $("#coupon_code_copy_view").on("keyup",function(){
          var cuponCode=$(this).val();
          $("#coupon_code_copy").val("discount-"+cuponCode);
        });
        });
    </script>
    
    <?php
}
add_action( 'woocommerce_checkout_before_order_review', __NAMESPACE__ . '\\fake_coupon' );



/*******************
 * Payment Methods *
 *******************/

/**
 * manage when different payment gateways are available
 */
function manage_gateways( $available_gateways ) {
  global $wpdb;
  $customer_id = get_current_user_id();

  $available_payment = $wpdb->get_var(
    $wpdb->prepare(
      "SELECT Payment
          FROM randys_customer_payment
          WHERE WooCustID = %d LIMIT 1",
      array($customer_id)
    )
  );

  switch ($available_payment) {
    case 'A/R':
      $cart_total = WC()->cart->total;
      $credit_available = get_credit_available();
      if ( $credit_available < $cart_total ) {
        unset( $available_gateways['ar'] );
      }
      break;
    case 'CC':
    case 'COD':
      unset( $available_gateways['ar'] );
      break;
    default:
      unset( $available_gateways['ar'] );
      unset( $available_gateways['cod'] );
  }

  if ( in_array( 'WILL CALL', WC()->session->get( 'chosen_shipping_methods', array() ) ) ) {
    unset( $available_gateways['cod'] );
    unset( $available_gateways['paypal_express'] );
	unset( $available_gateways['fc_fabric_gateway'] );
  } else {
    unset( $available_gateways['cash'] );
  }

  return $available_gateways;
}
add_filter( 'woocommerce_available_payment_gateways', __NAMESPACE__ . '\\manage_gateways' );



/********************
 * Shipping Methods *
 ********************/

/**
* Add dropship fee
*/
function add_fees() {

  // add dropship fee
  // "Dropship fees are one per Master Order." https://app.asana.com/0/201338786956630/325388457233835/f
  $use_dropship = WC()->session->get('use_dropship', false);

  if ( $use_dropship ) {
    $subtotal = WC()->session->get('subtotal_ex_tax');
    if ( 25 > $subtotal ) {
      $dropship_fee = 0;
    } elseif ( 100 > $subtotal ) {
      $dropship_fee = 4.5;
    } else {
      $dropship_fee = 9;
    }
    // grepable: fee added here : DROPSHIP
    WC()->cart->add_fee( __('Dropship Fee', 'woocommerce'), $dropship_fee, true );
  }

}
add_action( 'woocommerce_cart_calculate_fees', __NAMESPACE__ . '\\add_fees' );

/**
 * Determine shipping packages
 * Requires a shipping destination state (WooCommerce -> Settings -> Shipping -> "Hide shipping costs until an address is entered")
 * Called from get_shipping_packages() in wp-content/plugins/woocommerce/includes/class-wc-cart.php
 */
function split_into_shipments($packages) {

    // we can only split into shipments if we have a shipping address
    if ( WC()->customer->get_shipping_country() && WC()->customer->get_shipping_state() && WC()->customer->get_shipping_postcode() ) {

        // discard WooCommerce-generated packages; we'll make our own
        $packages = array();

        $cart = WC()->cart->get_cart();
        $destination_state = WC()->customer->get_shipping_state();

        // array of ShipmentInfo objects
        //echo $destination_state;exit;
        $shipments = \Cartonization\CartonizationEngine::getBoxesForOrder($cart, $destination_state);
        //echo"<pre>";print_r($shipments);
        // set destination array for reuse
        $destination = array();
        $destination['country'] = WC()->customer->get_shipping_country();
        $destination['state'] = WC()->customer->get_shipping_state();
        $destination['postcode'] = WC()->customer->get_shipping_postcode();
        $destination['city'] = WC()->customer->get_shipping_city();
        $destination['address'] = WC()->customer->get_shipping_address();
        $destination['address_2'] = WC()->customer->get_shipping_address_2();

        // get number of shipments for use in getShippingOptionsForShipment()
        $num_shipments = count($shipments);

        // split shipments into packages
        foreach ($shipments as $shipment) {
            $cart_items = array();
            foreach ($shipment->boxes as $box) {
                foreach ($box->items as $item) {
                    $cart_items[$item->cartItemKey] = $item->cartItem;
                }
            }

            $package = array();
            $package['contents'] = $cart_items; // Items in the package
            $package['contents_cost'] = 0; // Cost of items in the package, set below
            $package['discount_amount'] = 0;
            $package['applied_coupons'] = array();
            $package['user']['ID'] = get_current_user_id();
            $package['destination'] = $destination;

            foreach ( $package['contents'] as $item ) {
                if ( $item['data']->needs_shipping() ) {
                    // $item['line_total'] is with coupon discounts applied and may contain fractional cents
                    // $item['line_subtotal'] is without any coupon discounts applied
                    if ( isset( $item['line_subtotal'] ) && isset( $item['line_total'] ) ) {
                        $package['contents_cost'] += $item['line_subtotal'];
                        $package['discount_amount'] += $item['line_subtotal'] - $item['line_total'];
                    }
                }
            }

            $coupons = WC()->cart->get_coupons();
            foreach ($coupons as $coupon) {
                if ($coupon->is_type('percent')) {
                    array_push($package['applied_coupons'], $coupon->code);
                } else {
                    error_log('Unsupported coupon type: ' . $coupon->__get('type'));
                }
            }

            // custom package fields
            $package['shipment_box_count'] = count($shipment->boxes);
            $package['shipment_origin_warehouse'] = $shipment->warehouse;

            // get shipping methods available for this shipment
            if ($shipment->warehouse != 'NOD') {
                $shipment_options = \Shipping\ShippingEngine::getShippingOptionsForShipment($shipment, $shipments, $num_shipments, is_wholesale(), getCustomerNumber());
                $package['shipment_shipping_options'] = $shipment_options;
            }

            // We must store whether or not the order is a COD order in the package
            // because calculate_shipping_for_package() in WC_Shipping caches the shipping
            // rates based on the package. If the package doesn't change we can't change
            // the shipping and handling fees.
            $chosen_gateway = WC()->session->chosen_payment_method;
            $package['is_cod_order'] = ($chosen_gateway === 'cod');

            $packages[] = $package;
        }
    }
    //echo "<pre>";print_r($packages);
    return $packages;
}
add_filter('woocommerce_cart_shipping_packages', __NAMESPACE__ . '\\split_into_shipments');


// called from woocommerce/includes/class-wc-shipping.php with:
// $package['rates'] = apply_filters( 'woocommerce_package_rates', $package['rates'], $package );
function add_package_rates($package_rates, $package) {

    if ( is_array($package) && array_key_exists('shipment_origin_warehouse', $package) && $package['shipment_origin_warehouse'] == 'OOS' ) {
        // create fake rate for the out of stock shipment
        $package_rates['DNS'] = new \WC_Shipping_Rate('DNS', 'Do Not Ship', 0);

    } elseif ( is_array($package) && array_key_exists('shipment_shipping_options', $package) && is_array($package['shipment_shipping_options']) ) {
        // we can only create rates if shipping options exist to create rates from

        // discard WooCommerce-generated package ratess; we'll make our own
        $package_rates = array();

        foreach ($package['shipment_shipping_options'] as $shipping_option) {
            // grepable: calculate shipping+handling
            $shipping_and_handling_cost = get_shipping_and_handling_for_shipment($shipping_option, $package);

            // public function __construct( $id = '', $label = '', $cost = 0, $taxes = array(), $method_id = '' ) {
            $package_rate = new \WC_Shipping_Rate($shipping_option->greatPlainsId, $shipping_option->desc, $shipping_and_handling_cost);
            $package_rates[$shipping_option->greatPlainsId] = $package_rate;
        }
    }

    return $package_rates;
}
add_filter('woocommerce_package_rates', __NAMESPACE__ . '\\add_package_rates', 10, 2);


function get_shipping_and_handling_for_shipment($shipping_option, $package) {

    // grepable: calculate COD fee
    $cod_fee = 0;

    // will call shipping code must match what is set in GetShippingOptionsForShipment.php
    if ( $package['is_cod_order'] && 'WILL CALL' !== $shipping_option->code ) {

        // COD fee is the same for both UPS and OnTrac
        $cod_fee = $package['shipment_box_count'] * 14.5;

    }

    // grepable: fee added here : FREIGHT and HANDLING and COD
    return $shipping_option->shipping + $shipping_option->handling + $cod_fee;
}


// Add custom shipping dropdown options to woocommerce checkout
function add_shipping_options_for_package( $checkout, $package_index, $package ) {

    // Get all of the chosen shipping methods
    $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
    $chosen_method = null;
    if ( ! empty( $chosen_methods[ $package_index ] ) ) {
        $chosen_method = $chosen_methods[ $package_index ];
    }

    $field_id = 'shippingoption_' . $package_index;
	
    if (is_array($package)
        && array_key_exists('shipment_origin_warehouse', $package)
        && array_key_exists('shipment_shipping_options', $package)
        && $package['shipment_origin_warehouse'] != 'OOS'
        && $package['shipment_origin_warehouse'] != 'NOD'
        && count($package['shipment_shipping_options']) > 0)
    {
        // if there are shipping options available
        // create custom WooCommerce 'fields' for these options
        $options = array();
        foreach ($package['shipment_shipping_options'] as $key => $shipping_option) {
            // grepable: calculate shipping+handling (for display only)
            $shipping_and_handling_cost = get_shipping_and_handling_for_shipment($shipping_option, $package);
            $options[$shipping_option->greatPlainsId] = __($shipping_option->desc . ' - $' . number_format($shipping_and_handling_cost, 2), 'woocommerce');
        }

        $label = null;
        foreach ( $options as $option ) {
          if ( 'Free Shipping' === substr($option, 0, 13) ) {
            $label = '* handling fees still apply to free shipping';
          }
        }
        woocommerce_form_field( $field_id, array(
            'type' => 'select',
            'class' => array('form-row-wide select'),
            'required' => false,
            'input_class' => array('shipping_methods'),
            'options' => $options,
            'description' => $label,
        ), $chosen_method);
    }


    if (is_array($package)
        && array_key_exists('shipment_origin_warehouse', $package)
        && $package['shipment_origin_warehouse'] == 'OOS')
    {
        // wp-content/plugins/woocommerce/includes/class-wc-checkout.php will fail the checkout
        // with an 'No shipping method has been selected' error if out of stock shipments do not
        // have a shipping method selected.
        // ensure that out of stock shipments have a 'Do Not Ship' shipping method set.

        $options = array('DNS' => 'Do Not Ship');
        woocommerce_form_field( $field_id, array(
            'type' => 'select',
            'class' => array('form-row-wide select'),
            'required' => false,
            'input_class' => array('shipping_methods'),
            'options' => $options,
        ), $chosen_method);
    }

}
add_action( 'shipping_options_for_package', __NAMESPACE__ . '\\add_shipping_options_for_package', 10, 3 );


function process_shipping_method_selection( $post_data ) {

	if ( ! empty( $post_data ) ) {

        // TODO: use parse_str
		// parse the post_data string
		$post_data = explode( '&', $post_data );
		foreach ( $post_data as $pair ) {
			$pair = explode( '=', $pair );
			$post_data[ $pair[0] ] = urldecode( $pair[1] );
		}

        $chosen_methods = array();

        foreach ($post_data as $post_key => $post_value) {
            if (0 === strpos($post_key, 'shippingoption_')) {
                // this is a shipping option!
                list($prefix, $package_index) = explode('_', $post_key);
                $chosen_methods[$package_index] = $post_value;
            }
        }

        WC()->session->set( 'chosen_shipping_methods', $chosen_methods );
	}

}
add_action('woocommerce_checkout_update_order_review', __NAMESPACE__ . '\\process_shipping_method_selection');



/****
 ****/



// modify totals
function action_woocommerce_calculate_totals( \WC_Cart $instance ) {
    // we can only sort out OOS shipments if we have a shipping address
    if ( is_checkout() && WC()->customer->get_shipping_country() && WC()->customer->get_shipping_state() && WC()->customer->get_shipping_postcode() ) {
        $packages = WC()->shipping->get_packages();
        $cart_subtotal = 0;
        $OOS_total = 0;
        foreach ($packages as $package_index => $package) {
            if ( 'OOS' != $package['shipment_origin_warehouse'] ) {
                $cart_subtotal += $package['contents_cost'];
            } else {
                $OOS_total += $package['contents_cost'];
            }
        }
        // reset totals to not include OOS items
        $instance->subtotal = $cart_subtotal;
        $instance->subtotal_ex_tax = $cart_subtotal;
        $instance->cart_contents_total = $cart_subtotal;
        $instance->total = $instance->total - $OOS_total;
    }
};
add_action( 'woocommerce_after_calculate_totals', __NAMESPACE__ . '\\action_woocommerce_calculate_totals', 10, 1 );


// Update order meta and order line item metas to include 'origin_state's
function update_order_meta($order_id) {
  global $wpdb;

  // Get the separate shipments
  $packages = WC()->shipping->get_packages();

  // Get all the line items for this order.
  $order_line_items = $wpdb->get_results(
    $wpdb->prepare(
      "SELECT oi.order_item_id   AS item_id,
              oi.order_item_type AS order_item_type,
              oim.meta_value     AS product_id
        FROM wp_woocommerce_order_items oi
        LEFT JOIN wp_woocommerce_order_itemmeta oim
          ON oim.order_item_id = oi.order_item_id AND oim.meta_key = '_product_id'
        WHERE order_id = %d",
      array(
        $order_id
      )
    )
  );

  // Get all of the chosen shipping methods
  $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );

  // An array to store all of the package origins
  $package_origins = array();

  // Loop through each package and save data accordingly
  foreach ($packages as $package_index => $package) {

    // Get the package's origin warehouse ID
    $warehouse_id = $package['shipment_origin_warehouse'];
    // Add this to the list of package origins
    $package_origins[] = $warehouse_id;

    // If we have a chosen shipping method, save it as a new line with the stat intact
    if ( ! empty( $chosen_methods[ $package_index ] ) ) {

      // Get the chosen shipping rate from the chosen shipping method
      $chosen_method = $chosen_methods[ $package_index ];
      $rate = $package['rates'][ $chosen_method ];

      // Save the shipping cost as a order_item
      $data = array(
        'order_item_name' => 'Shipping for Warehouse: ' . $warehouse_id,
        'order_item_type' => 'shipment_shipping',
        'order_id' => $order_id
      );
      $wpdb->insert($wpdb->prefix . 'woocommerce_order_items', $data);

      // Get the item_id for the newly inserted shipping method
      $item_id = absint( $wpdb->insert_id );

      // Add the cost for this shipping method.
      $cost_meta_data = array(
        'order_item_id' => $item_id,
        'meta_key' => 'cost',
        'meta_value' => $rate->cost
      );
      $wpdb->insert($wpdb->prefix . 'woocommerce_order_itemmeta', $cost_meta_data);

      // Add the origin warehouse id for this shipping method
      $meta_data = array(
        'order_item_id' => $item_id,
        'meta_key' => 'origin_warehouse_id',
        'meta_value' => $warehouse_id
      );
      $wpdb->insert($wpdb->prefix . 'woocommerce_order_itemmeta', $meta_data);

      // Add the method ID for this shipping method
      $meta_data = array(
        'order_item_id' => $item_id,
        'meta_key' => 'method_id',
        'meta_value' => $rate->id
      );
      $wpdb->insert($wpdb->prefix . 'woocommerce_order_itemmeta', $meta_data);
    }

    // Find a matching line item and save its origin warehouse id
    foreach ($order_line_items as $line_item) {

      // Make sure the fees are applied to the first package
      if ($line_item->order_item_type == 'fee' && $package_index == 0) {
        $meta_data = array(
          'order_item_id' => $line_item->item_id,
          'meta_key' => 'origin_warehouse_id',
          'meta_value' => $warehouse_id
        );
        $wpdb->insert($wpdb->prefix . 'woocommerce_order_itemmeta', $meta_data);
      }

      // Set regular line items to the current shipment
      if ($line_item->order_item_type == 'line_item') {
        foreach ($package['contents'] as $product) {
          if ($line_item->product_id == $product['product_id']) {

            // If found, add the origin warehouse id to the line item
            $meta_data = array(
              'order_item_id' => $line_item->item_id,
              'meta_key' => 'origin_warehouse_id',
              'meta_value' => $warehouse_id
            );
            $wpdb->insert($wpdb->prefix . 'woocommerce_order_itemmeta', $meta_data);

            $meta_data = array(
              'order_item_id' => $line_item->item_id,
              'meta_key' => 'quantity_from_warehouse_' . $warehouse_id,
              'meta_value' => $product['quantity']
            );
            $wpdb->insert($wpdb->prefix . 'woocommerce_order_itemmeta', $meta_data);

            // Let's also get the randys_productnumber (_sku) into here
            $product_sku = $wpdb->get_var(
              $wpdb->prepare(
                "SELECT meta_value FROM wp_postmeta WHERE meta_key='_sku' AND post_id = %d",
                array($line_item->product_id)
              )
            );
            // Put the sku into the item meta
            $meta_data = array(
              'order_item_id' => $line_item->item_id,
              'meta_key' => '_sku',
              'meta_value' => $product_sku
            );
            $wpdb->insert($wpdb->prefix . 'woocommerce_order_itemmeta', $meta_data);
          }
        }
      }
    }

    if ($package['discount_amount'] > 0) {

      // Save the total shipment discount as a woocommerce_order_item
      $data = array(
        'order_item_name' => 'Discount for Warehouse: ' . $warehouse_id,
        'order_item_type' => 'shipment_discount',
        'order_id' => $order_id
      );
      $wpdb->insert($wpdb->prefix . 'woocommerce_order_items', $data);

      // get item id
      $item_id = absint( $wpdb->insert_id );

      // Add the warehouse id as a meta to this tax woocommerce_order_item
      $meta_data = array(
        'order_item_id' => $item_id,
        'meta_key' => 'origin_warehouse_id',
        'meta_value' => $warehouse_id
      );
      $wpdb->insert($wpdb->prefix . 'woocommerce_order_itemmeta', $meta_data);

      // Add the discount_amount as a meta to this discount woocommerce_order_item
      $meta_data = array(
        'order_item_id' => $item_id,
        'meta_key' => 'discount_amount',
        'meta_value' => $package['discount_amount'],
      );
      $wpdb->insert($wpdb->prefix . 'woocommerce_order_itemmeta', $meta_data);
    }


    $discount_amount_remaining = $package['discount_amount'];

    foreach($package['applied_coupons'] as $coupon_code) {
        $coupon = new \WC_Coupon($coupon_code);
        // assumes all coupons apply equally to all shipments
        $coupon_discount_amount = $coupon->get_discount_amount($package['contents_cost']);
        // adjust for rounding
        if (abs($discount_amount_remaining - $coupon_discount_amount) < 0.02) {
            $coupon_discount_amount = $discount_amount_remaining;
        }
        $discount_amount_remaining -= $coupon_discount_amount;

        // Save the discount from this coupon for this shipment as a woocommerce_order_item
        $data = array(
            'order_item_name' => $coupon_code . ' (' . $warehouse_id . ')',
            'order_item_type' => 'shipment_coupon',
            'order_id' => $order_id
        );
        $wpdb->insert($wpdb->prefix . 'woocommerce_order_items', $data);

        // get item id
        $item_id = absint( $wpdb->insert_id );

        // Add the warehouse id as a meta to this tax woocommerce_order_item
        $meta_data = array(
            'order_item_id' => $item_id,
            'meta_key' => 'origin_warehouse_id',
            'meta_value' => $warehouse_id
        );
        $wpdb->insert($wpdb->prefix . 'woocommerce_order_itemmeta', $meta_data);

        // Add the discount_amount as a meta to this discount woocommerce_order_item
        // this is the discount for this shipment from this coupon
        $meta_data = array(
            'order_item_id' => $item_id,
            'meta_key' => 'discount_amount',
            'meta_value' => $coupon_discount_amount,
        );
        $wpdb->insert($wpdb->prefix . 'woocommerce_order_itemmeta', $meta_data);
    }
  }

  // Add the list of origin states to this order
  add_post_meta( $order_id, 'shipment_origins', $package_origins );

  // Save the shipment-specific tax data
  $taxes = WC()->session->get( 'new_taxes' );
  $taxes_detail = WC()->session->get( 'new_taxes_detail' );
  foreach ($taxes as $warehouse_id => $tax_data) {
    $tax_detail = $taxes_detail[$warehouse_id];

    // Save the tax as a order_item
    $data = array(
      'order_item_name' => 'Tax for Warehouse: ' . $warehouse_id,
      'order_item_type' => 'shipment_tax',
      'order_id' => $order_id
    );
    $wpdb->insert($wpdb->prefix . 'woocommerce_order_items', $data);

    // get item id
    $item_id = absint( $wpdb->insert_id );

    // Add the warehouse id as a meta to this tax order_item
    $meta_data = array(
      'order_item_id' => $item_id,
      'meta_key' => 'origin_warehouse_id',
      'meta_value' => $warehouse_id
    );
    $wpdb->insert($wpdb->prefix . 'woocommerce_order_itemmeta', $meta_data);

    // Add the rate_id as a meta to this tax order_item
    $meta_data = array(
      'order_item_id' => $item_id,
      'meta_key' => 'rate_id',
      'meta_value' => 'custom_avatax'
    );
    $wpdb->insert($wpdb->prefix . 'woocommerce_order_itemmeta', $meta_data);

    // Add the label as a meta to this tax order_item
    $meta_data = array(
      'order_item_id' => $item_id,
      'meta_key' => 'label',
      'meta_value' => 'Tax'
    );
    $wpdb->insert($wpdb->prefix . 'woocommerce_order_itemmeta', $meta_data);

    // Add the compound as a meta to this tax order_item
    $meta_data = array(
      'order_item_id' => $item_id,
      'meta_key' => 'compound',
      'meta_value' => '0'
    );
    $wpdb->insert($wpdb->prefix . 'woocommerce_order_itemmeta', $meta_data);

    // Add the tax_amount as a meta to this tax order_item
    $meta_data = array(
      'order_item_id' => $item_id,
      'meta_key' => 'tax_amount',
      'meta_value' => $tax_data['total_tax']
    );
    $wpdb->insert($wpdb->prefix . 'woocommerce_order_itemmeta', $meta_data);

    // Add the shipping_tax_amount as a meta to this tax order_item
    $meta_data = array(
      'order_item_id' => $item_id,
      'meta_key' => 'shipping_tax_amount',
      'meta_value' => $tax_data['total_shipping_tax']
    );
    $wpdb->insert($wpdb->prefix . 'woocommerce_order_itemmeta', $meta_data);
  }
}
add_action( 'woocommerce_checkout_update_order_meta', __NAMESPACE__ . '\\update_order_meta' );


function getCustomerNumber()
{
  global $wpdb;
  $customerNumber = "";
  $woo_cust_id = get_current_user_id(); // get customer id

  $customer_number = $wpdb->get_row(
    $wpdb->prepare(
      "SELECT CUSTNMBR FROM randys_customers WHERE WOOCustID = %d",
      $woo_cust_id
    )
  );
  if ( $customer_number !== null ) {
    $customerNumber = $customer_number->CUSTNMBR;
  }

  return $customerNumber;
}
