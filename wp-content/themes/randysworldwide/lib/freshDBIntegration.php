<?php

/*
 * Function that turns raw metadata into a useful array.
 * Here it is used by:
 *    randys_integration_database_export_users()
 *    randys_integration_database_export_orders()
 */
function meta_to_array($meta_data) {
  // This is the array we will be sending back
  $output_data = array();
  // Go through each item in the meta_data and put it nicely into the array
  foreach ($meta_data as $data_item) {
    $output_data[$data_item->meta_key] = $data_item->meta_value;
  }
  return $output_data;
}

/**
 * Export users to the integration database as they're created
 */
function randys_integration_database_export_users($user_id, $is_inside_a_transaction = false) {

  // Get $wpdb to use in queries against the custom web_users table
  global $wpdb;

  // Start the transaction, and wrap everything in a try/catch so that if it bugs out, we can rollback the changes
  if (! $is_inside_a_transaction) {
    $wpdb->query("START TRANSACTION");
  }

  try {

    // Get the user object
    $user = get_user_by('ID', $user_id);

    // Create the query to check if this user already exists in the table
    $exists_results = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT * FROM web_users WHERE wp_customer_id = %d LIMIT 1",
        array($user_id)
      )
    );

    // Get the meta data for this user
    $user_meta_data_raw = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM wp_usermeta WHERE user_id=%d",
            array($user_id)
        )
    );
    $user_meta_data = meta_to_array($user_meta_data_raw);

    // User info we want to copy over from the ['wp' => 'randys']
    $user_info = array(
      'ID'              => 'wp_customer_id',
      'user_login'      => 'user_login',
      'user_email'      => 'user_email',
      'user_registered' => 'user_registered',
      'user_nicename'   => 'display_name',
    );
    $meta_info = array(
      'randys_customer_id',
      'shipping_postcode',
      'shipping_state',
      'shipping_city',
      'shipping_address_1',
      'shipping_address_2',
      'shipping_country',
      'shipping_last_name',
      'shipping_first_name',
      'shipping_company',
      'billing_postcode',
      'billing_state',
      'billing_city',
      'billing_address_1',
      'billing_address_2',
      'billing_country',
      'billing_phone',
      'billing_email',
      'billing_last_name',
      'billing_first_name',
      'billing_company',
    );

    // If we have no entries, then we insert one for this user into the database.
    if (count($exists_results) === 0) {

      $query_columns = array();       // Column Name
      $query_values = array();        // Column Value
      // For query_value_formats, See: https://developer.wordpress.org/reference/classes/wpdb/prepare/

      // First, let's add the main variables
      foreach ($user_info as $source_key => $target_key) {
        // Add the column
        $query_columns[] = $target_key;

        // Add the value to the values array
        $query_values[] = $user->data->$source_key;
      }

      // Second, let's add the meta data
      foreach ($meta_info as $meta_key ) {
        // Add the column
        $query_columns[] = $meta_key;

        // Add the value to the values array
        $query_values[] = isset($user_meta_data[$meta_key]) ? $user_meta_data[$meta_key] : NULL;
      }

      // Here we create all the query value formats.
      $query_value_formats = array_fill(0, count($query_columns), '%s');

      // Get the SQL query ready for the User Insert
      $insert_query_raw = "INSERT INTO web_users (" .
                           implode(', ', $query_columns) .
                           ") VALUES (" .
                           implode(", ", $query_value_formats) .
                           ")";
      $insert_query = $wpdb->prepare($insert_query_raw, $query_values); // WPCS: unprepared SQL OK

      // Run the query to insert the user into the DB
      $insert_results = $wpdb->get_results($insert_query); // WPCS: unprepared SQL OK

    } else {

      // Query sets are what goes after SET in the query. "key = %s"
      $query_sets = array();
      // Sore the values for each SET
      $query_values = array();

      // First, let's add the main variables
      foreach ($user_info as $source_key => $target_key) {
        // Add the column
        $query_sets[] = $target_key . " = %s";

        // Add the value to the values array
        $query_values[] = $user->data->$source_key;
      }

      // Second, let's add the meta data
      foreach ($meta_info as $meta_key ) {
        // Add the column
        $query_sets[] = $meta_key . " = %s";

        // Add the value to the values array
        $query_values[] = isset($user_meta_data[$meta_key]) ? $user_meta_data[$meta_key] : NULL;
      }

      // Get the SQL query ready for the User Update
      $update_query_raw = "UPDATE web_users SET " .
                           implode(', ', $query_sets) .
                           " WHERE wp_customer_id = " . $user_id;

      // Format the query
      $update_query = $wpdb->prepare($update_query_raw, $query_values); // WPCS: unprepared SQL OK

      // Run the query to update the user's information
      $update_results = $wpdb->get_results($update_query); // WPCS: unprepared SQL OK
    }

    // All went well, commit this query.
    if (!$is_inside_a_transaction) {
      $wpdb->query("COMMIT");
    }

  } catch (Exception $e) {
    // Something went wrong, log this error and rollback
    if ($is_inside_a_transaction) {
      throw $e;
    } else {
      $wpdb->query("ROLLBACK");
    }
    error_log("Error occurred in the integration database user export.\nRolling back transaction for User: " . $user_id .
              "\nError: " . $e->getMessage()
    );
  }
}
add_action( 'user_register', __NAMESPACE__ . '\\randys_integration_database_export_users', 10, 2 );
add_action( 'profile_update', __NAMESPACE__ . '\\randys_integration_database_export_users', 10, 2 );

function randys_integration_database_update_user_meta( $mid, $object_id, $meta_key, $_meta_value ) {
    // must match $meta_info in randys_integration_database_export_users
    $meta_info = array(
      'randys_customer_id',
      'shipping_postcode',
      'shipping_state',
      'shipping_city',
      'shipping_address_1',
      'shipping_address_2',
      'shipping_country',
      'shipping_last_name',
      'shipping_first_name',
      'shipping_company',
      'billing_postcode',
      'billing_state',
      'billing_city',
      'billing_address_1',
      'billing_address_2',
      'billing_country',
      'billing_phone',
      'billing_email',
      'billing_last_name',
      'billing_first_name',
      'billing_company',
    );
    if (in_array($meta_key, $meta_info)) {
        // this is a meta value we care about; update the integration database
        randys_integration_database_export_users($object_id);
    }
}
add_action( 'added_user_meta', __NAMESPACE__ . '\\randys_integration_database_update_user_meta', 10, 4 );


/**
 * Export orders to the integration database
 */
function randys_integration_database_export_orders( $order_id ) {

  // Pull in the wordpress database, we're going to be using direct queries
  global $wpdb;

  // Start the transaction and wrap the function in a try/catch just to make sure all goes well
  $wpdb->query("START TRANSACTION");

  try {

    // Get the order
    $order_data_raw = $wpdb->get_results($wpdb->prepare("SELECT * FROM wp_posts WHERE ID = %d", array($order_id)));
    $order_data = $order_data_raw[0];

    // Get the meta data for this order
    $order_meta_data_raw = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM wp_postmeta WHERE post_id=%d",
            array($order_id)
        )
    );
    $order_meta_data = meta_to_array($order_meta_data_raw);

    // Now that we have the customer, make sure the customer data is up to date
    randys_integration_database_export_users($order_meta_data['_customer_user'], true);

    $origin_list = get_post_meta($order_id, 'shipment_origins', true);

    $first_shipment = true; // Signifies if we are on the first order, certain things like fees depend upon this

    // Go through each origin, we will need to insert these orders in separately
    foreach ($origin_list as $origin_warehouse_id) {

      // Concatenate the shipment ID from the order_id and origin_warehouse_id
      $shipment_id = $order_id . "-" . $origin_warehouse_id;

      // Post info we want to copy over from the ['wp' => 'randys']
      $order_info = array(
        // 'ID'          => 'wp_order_id',     <== This will be handled differently
        'post_status' => 'status',
        'post_date' => 'order_date',
      );

      // Array lining up the meta keys to the target keys in the database.
      $meta_info = array(
        '_transaction_id'               => 'transaction_id',
        //'_cart_discount'                => 'cart_discount', // Discount needs to be calculated
        // '_customer_ip_address'          => '',
        '_customer_user'                => 'wp_customer_id',
        // '_customer_user_agent'          => '',
        // '_download_permissions_granted' => '',
        // '_edit_last'                    => '',
        // '_edit_lock'                    => '',
        '_order_currency'               => 'order_currency',
        // '_order_key'                    => '',

        // Shipping, Shipping Tax, and Tax will all be handled by the line_items, no need to handle them here
        // '_order_shipping'               => 'shipping_total',
        // '_order_shipping_tax'           => 'shipping_tax_total',
        // '_order_tax'                    => 'tax_total',

        // Order Total will be be calculated by the line_items, no need to handle it here
        //'_order_total'                  => 'order_total',

        // '_order_version'                => '',
        '_payment_method'               => 'payment_method',
        // '_payment_method_title'         => '',
        // '_prices_include_tax'           => '',
        // '_recorded_sales'               => '',
        '_shipping_address_1'           => 'shipping_address_1',
        '_shipping_address_2'           => 'shipping_address_2',
        '_shipping_city'                => 'shipping_city',
        '_shipping_company'             => 'shipping_company',
        '_shipping_country'             => 'shipping_country',
        '_shipping_first_name'          => 'shipping_first_name',
        '_shipping_last_name'           => 'shipping_last_name',
        '_shipping_postcode'            => 'shipping_postcode',
        '_shipping_state'               => 'shipping_state',
        '_billing_address_1'            => 'billing_address_1',
        '_billing_address_2'            => 'billing_address_2',
        '_billing_city'                 => 'billing_city',
        '_billing_company'              => 'billing_company',
        '_billing_country'              => 'billing_country',
        '_billing_email'                => 'billing_email',
        '_billing_first_name'           => 'billing_first_name',
        '_billing_last_name'            => 'billing_last_name',
        // '_billing_phone'                => 'billing_phone', // PHONE NEEDS TO BE SANITIZED
        '_billing_postcode'             => 'billing_postcode',
        '_billing_state'                => 'billing_state',
        '_make_model'                   => 'vehicle_make_model',
        '_year'                         => 'vehicle_year',
        '_axle'                         => 'vehicle_axle',
        '_questions_comments'           => 'vehicle_questions',
        '_po_number'                    => 'po_number',
        '_transaction_id'               => 'transaction_number',
        '_shipper_id_number'            => 'shipper_id',
        '_wallet_id'                    => 'wallet_id',
        '_card_identifier'              => 'card_identifier'

        // SHIPPING METHOD IS UNIQUE AND WILL BE RECORDED DIFFERENTLY
        // 'NULL' => 'shipping_method'
        // SAME WITH REFUND
        // 'NULL' => 'refunded_total'
        // SAME WITH WAREHOUSE
        // 'NULL' => 'warehouse'
      );

      // SITE REFUNDS NOT ALLOWED. REFUND TOTAL ALWAYS 0
      $refunded_total = '0';

      /*
       * Determine if we need to create a new order or update an existing one
       */
      // Create the query to check if this order already exists in the table
      $exists_results = $wpdb->get_results(
        $wpdb->prepare(
          "SELECT * FROM web_orders WHERE wp_order_id = %s LIMIT 1",
          array($shipment_id)
        )
      );

      // If we have no entries, then we insert one for this order into the database.
      if (count($exists_results) === 0) {

        // Columns that we will be populating, along with a value array and their formats.
        $query_columns = array();
        $query_values = array();
        $query_value_formats = array();

        // First, let's add the main variables
        foreach ($order_info as $source_key => $target_key) {
          // Add the column
          $query_columns[] = $target_key;

          // Add the value to the values array
          $query_values[] = $order_data->$source_key;
        }

        // Second, let's add the meta data
        foreach ($meta_info as $meta_source => $meta_target) {
          // Add the column
          $query_columns[] = $meta_target;

          // Add the value to the values array (If it exists in the array)
          $query_values[] = isset($order_meta_data[$meta_source]) ? $order_meta_data[$meta_source] : NULL;
        }


        /* HERE WE ADD THE EXTRA INFORMATION NOT IN WP_POST OR WP_POSTMETA */

        // NEW ID
        $query_columns[] = "wp_order_id";
        $query_values[] = $shipment_id;

        // WAREHOUSE
        $query_columns[] = "warehouse";
        $query_values[] = $origin_warehouse_id;

        // PHONE
        $query_columns[] = "billing_phone";
        $query_values[] = isset($order_meta_data['_billing_phone']) ? preg_replace("/[^0-9]/", "", $order_meta_data['_billing_phone']) : NULL;

        // NOTES
        $query_columns[] = "notes";
        $query_values[] = $order_data->post_excerpt;

        // Here we create all the query value formats.
        $query_value_formats = array_fill(0, count($query_columns), '%s');

        // Get the SQL query ready for the User Insert
        $insert_query_raw = "INSERT INTO web_orders (" .
                             implode(",\n  ", $query_columns) .
                             "\n) VALUES (\n" .
                             implode(",\n  ", $query_value_formats) .
                             ")";
        $insert_query = $wpdb->prepare($insert_query_raw, $query_values); // WPCS: unprepared SQL OK

        // Run the query to insert the order into the DB
        $insert_results = $wpdb->get_results($insert_query); // WPCS: unprepared SQL OK

      } else {

        // Query sets are what goes after SET in the query. "key = %s"
        $query_sets = array();
        // Sore the values for each SET
        $query_values = array();

        // First, let's add the main variables
        foreach ($order_info as $source_key => $target_key) {
          // Add the column
          $query_sets[] = $target_key . " = %s";

          // Add the value to the values array
          $query_values[] = $order_data->$source_key;
        }

        // Second, let's add the meta data
        foreach ($meta_info as $meta_source => $meta_target ) {
          // Add the column
          $query_sets[] = $meta_target . " = %s";

          // Add the value to the values array
          $query_values[] = isset($order_meta_data[$meta_source]) ? $order_meta_data[$meta_source] : NULL;
        }


        /* HERE WE ADD THE EXTRA INFORMATION NOT IN WP_POST OR WP_POSTMETA */

        // WAREHOUSE
        $query_sets[] = "warehouse = %s";
        $query_values[] = $origin_warehouse_id;


        // Get the SQL query ready for the User Update
        $update_query_raw = "UPDATE web_orders SET\n  " .
                             implode(",\n  ", $query_sets) .
                             " WHERE wp_order_id = %s";
        // Add in the ID for the WHERE clause
        $query_values[] = $shipment_id;

        // Format the query
        $update_query = $wpdb->prepare($update_query_raw, $query_values); // WPCS: unprepared SQL OK

        // Run the query to update the order's information
        $update_results = $wpdb->get_results($update_query); // WPCS: unprepared SQL OK
      }

      // Now that the order is for sure in the database, let's add/update the line items
      randys_integration_add_or_update_order_items($order_id, $origin_warehouse_id, $first_shipment, $shipment_id);

      // Finally, the first shipment is over, from now on we will no longer be on the first one
      $first_shipment = false;
    }

    // All went well, commit this query.
    $wpdb->query("COMMIT");

  } catch (Exception $e) {
    // Something went wrong, log this error and rollback
    $wpdb->query("ROLLBACK");
    error_log("Error occurred in the integration database order export.\nRolling back transaction for Order: " . $order_id .
              "\nError: " . $e->getMessage()
    );
  }
}
// add the action
add_action('woocommerce_payment_complete', __NAMESPACE__ . '\\randys_integration_database_export_orders', 10, 1);


/*
 * Breaking out the order_item logic from the order logic
 */
function randys_integration_add_or_update_order_items( $order_id, $origin_warehouse_id, $first_shipment, $shipment_id ) {

  global $wpdb;

  // Get the order items associated with this order
  $order_items = $wpdb->get_results(
    $wpdb->prepare(
      "SELECT * FROM wp_woocommerce_order_items WHERE order_id= %d",
      array($order_id)
    )
  );

  // Set up the order variables to start adding to
  $order_subtotal = 0;
  $order_total = 0;

  // GO through all the order items
  foreach ($order_items as $item) {

    // Get the data for this one item
    $item_meta_raw = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT * FROM wp_woocommerce_order_itemmeta WHERE order_item_id= %d",
        $item->order_item_id
      )
    );
    $item_meta = meta_to_array($item_meta_raw);

    if ( array_key_exists('quantity_from_warehouse_'.$origin_warehouse_id, $item_meta) || (array_key_exists('origin_warehouse_id', $item_meta) && $item_meta['origin_warehouse_id'] === $origin_warehouse_id) ) {

      // Change what we do depending on the item that we pulled up
      switch ($item->order_item_type) {
        // Custom line items for SHIPMENTS
        case 'shipment_tax':
          set_integration_tax($item_meta, $shipment_id);
          $order_total = $order_total + $item_meta['tax_amount'];
          break;

        case 'shipment_discount':
          set_integration_discount($item_meta, $shipment_id);
          $order_total = $order_total - $item_meta['discount_amount'];
          break;

        case 'shipment_coupon':
          $item_meta['_qty'] = 1;
          $item_meta['_line_subtotal'] = $item_meta['discount_amount'] * -1;
          $item_meta['_line_total'] = $item_meta['discount_amount'] * -1;
          $item_meta['_sku'] = $item->order_item_name;
          set_integration_item($item, $item_meta, $shipment_id);
          break;
        
        case 'shipment_shipping':
          set_integration_shipping($item_meta, $shipment_id);
          $order_total = $order_total + $item_meta['cost'];
          break;

        case 'line_item':
           $total_quantity = $item_meta['_qty'];
          $entire_subtotal = $item_meta['_line_subtotal'];
          $item_meta['origin_warehouse_id'] = $origin_warehouse_id;
          //$item_meta['_qty'] = $item_meta['quantity_from_warehouse_'.$origin_warehouse_id];
          $item_meta['_qty'] = $item_meta['_qty'];
          $item_meta['_line_subtotal'] = $entire_subtotal / $total_quantity;
          $item_meta['after_discount_total'] = $item_meta['_line_total']; //tj 
          $item_meta['after_discount_subtotal'] = $item_meta['_line_total']/ $total_quantity; //tj 
          $item_meta['_line_total'] = $item_meta['_line_subtotal'] * $item_meta['_qty'];

          set_integration_item($item, $item_meta, $shipment_id);
          $order_subtotal = $order_subtotal + $item_meta['_line_subtotal'] * $item_meta['_qty'];
          $order_total = $order_total + $item_meta['_line_total'];
          break;

        case 'fee':
          set_integration_item($item, $item_meta, $shipment_id);
          $order_subtotal = $order_subtotal + $item_meta['_line_subtotal'];
          $order_total = $order_total + $item_meta['_line_total'];
          break;

        // We are explicitly ignoring normal tax and shipping
        case 'shipping':
        case 'tax':
          break;

        // Anything that wasn't caught above is not expected and should be logged
        default:
          error_log("Unexpected Line Item Type: " . $item->order_item_type);
          break;
      }
    }
  }

  // Put the order_total into the order
  $wpdb->query(
    $wpdb->prepare(
      "UPDATE web_orders SET subtotal = %f, order_total = %f WHERE wp_order_id = %s",
      array(
        $order_subtotal,
        $order_total,
        $shipment_id,
      )
    )
  );
}

// Sets the integration's tax from the given line_item
function set_integration_tax( $item_meta, $shipment_id ) {
  global $wpdb;

  // Put the tax into the order
  $wpdb->query(
    $wpdb->prepare(
      "UPDATE web_orders SET tax_total = %f WHERE wp_order_id = %s",
      array(
        $item_meta['tax_amount'],
        $shipment_id
      )
    )
  );
}

// Sets the discount from the given line_item
function set_integration_discount( $item_meta, $shipment_id ) {
  global $wpdb;
  
  // Put the discount into the order
  $wpdb->query(
    $wpdb->prepare(
      "UPDATE web_orders SET cart_discount = %f WHERE wp_order_id = %s",
      array(
        $item_meta['discount_amount'],
        $shipment_id
      )
    )
  );
}

// Sets the shipping costs from the given line_item
function set_integration_shipping( $item_meta, $shipment_id ) {
  global $wpdb;

  // Put the shipping into the order
  $wpdb->query(
    $wpdb->prepare(
      "UPDATE web_orders SET shipping_total = %f, shipping_method = %s WHERE wp_order_id = %s",
      array(
        $item_meta['cost'],
        $item_meta['method_id'],
        $shipment_id
      )
    )
  );
}

// Sets the line_item (Can also bee a fee)
function set_integration_item( $item, $item_meta, $shipment_id ) {
  global $wpdb;

  // Order info we want to copy over ['wp' => 'randys']
  $item_info = array(
    'order_item_id'   => 'wp_order_item_id',
    // 'order_id'        => 'wp_order_id',  <== Added Separately
    'order_item_type' => 'order_item_type',
    // randys_product_number will be added separately
  );
  $item_meta_info = array(
    'wp_product_id'           => '_product_id',
    'qty'                     => '_qty',
    'subtotal'                => '_line_subtotal',
    'total'                   => '_line_total',
    'randys_product_number'   => '_sku',
  );

  // Create the query to check if this item already exists in the table
  $exists_results = $wpdb->get_results(
    $wpdb->prepare(
      "SELECT * FROM web_order_items WHERE wp_order_item_id = %d AND wp_order_id = %s LIMIT 1",
      array($item->order_item_id, $shipment_id)
    )
  );

  // If we have no entries, then we insert one for this item into the database.
  if (count($exists_results) === 0) {

    $query_columns = array();
    $query_values = array();
    $query_value_formats = array();

    // First, let's add the main variables
    foreach ($item_info as $source_key => $target_key) {
      // Add the column
      $query_columns[] = $target_key;

      // Add the value to the values array
      $query_values[] = $item->$source_key;
    }

    
    if(isset($item_meta['after_discount_total'])){
      $after_discount_total=$item_meta['after_discount_total'];
      $after_discount_subtotal=$item_meta['after_discount_subtotal'];
      $query_columns[]= 'discount_applied';
      $query_values[] = $item_meta['_line_total']-$after_discount_total;
      $query_columns[]= 'original_total';
      $query_values[] = $item_meta['_line_total'];
      $item_meta_info["total"]="after_discount_total";
      $item_meta_info["subtotal"]="after_discount_subtotal";
      
    }

    // Second, let's add the meta data
    foreach ($item_meta_info as $meta_source => $meta_target ) {
      // Add the column
      $query_columns[] = $meta_source;

      // Add the value to the values array
      $query_values[] = isset($item_meta[$meta_target]) ? $item_meta[$meta_target] : NULL;
    }

    // Save the randys product_id if this line item has one
    if( isset($item_meta['_randy_productid']) && !empty($item_meta['_randy_productid']) ) {
      $query_columns[] = "randys_product_number";
      $query_values[] = get_product_number_for_item($meta_data['_randy_productid']);
    }

    // Insert the shipment ID
    $query_columns[] = "wp_order_id";
    $query_values[] = $shipment_id;

    // Here we create all the query value formats.
    $query_value_formats = array_fill(0, count($query_columns), '%s');

    // Get the SQL query ready for the Item Insert
    $insert_query_raw = "INSERT INTO web_order_items\n(\n  " .
                         implode(",\n  ", $query_columns) .
                         "\n) VALUES (\n  " .
                         implode(",\n  ", $query_value_formats) .
                         "\n)";
    $insert_query = $wpdb->prepare($insert_query_raw, $query_values); // WPCS: unprepared SQL OK

    // Run the query to insert the item into the DB
    $insert_results = $wpdb->get_results($insert_query); // WPCS: unprepared SQL OK

  } else {

    // Query sets are what goes after SET in the query. "key = %s"
    $query_sets = array();
    // Sore the values for each SET
    $query_values = array();

    // First, let's add the main variables
    foreach ($item_info as $source_key => $target_key) {
      // Add the column
      $query_sets[] = $target_key . " = %s";

      // Add the value to the values array
      $query_values[] = $item->$source_key;
    }

    // Second, let's add the meta data
    foreach ($item_meta_info as $meta_target => $meta_source ) {
      // Add the column
      $query_sets[] = $meta_target . " = %s";

      // Add the value to the values array
      $query_values[] = isset($item_meta[$meta_source]) ? $item_meta[$meta_source] : NULL;
    }

    // Save the randys product_id if this line item has one
    if( isset($item_meta['_randy_productid']) && !empty($item_meta['_randy_productid']) ) {
      $query_sets[] = "randys_product_number = %s";
      $query_values[] = get_product_number_for_item($meta_data['_randy_productid']);
    }

    // Insert the shipment ID
    $query_sets[] = "wp_order_id = %s";
    $query_values[] = $shipment_id;

    // Get the SQL query ready for the Item Update
    $update_query_raw = "UPDATE web_order_items SET\n  " .
                         implode(",\n  ", $query_sets) .
                         " \nWHERE wp_order_item_id = " . $item->order_item_id;

    // Format the query
    $update_query = $wpdb->prepare($update_query_raw, $query_values); // WPCS: unprepared SQL OK

    // Run the query to update the item's information
    $update_results = $wpdb->get_results($update_query); // WPCS: unprepared SQL OK
  }
}

// Get the product sku (same as product number)
function get_product_number_for_item($product_id) {
  global $wpdb;

  return $wpdb->get_var(
    $wpdb->prepare(
      "SELECT ProductNumber FROM randys_product
         WHERE ProductID = %d",
      array($product_id)
    )
  );
}
