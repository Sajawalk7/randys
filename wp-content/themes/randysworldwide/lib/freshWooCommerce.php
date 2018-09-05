<?php
namespace Roots\Sage\RANDYS;

/**
 * Get the master list of warehouses
 */
function get_warehouse_ids() {
  return array('CA', 'KY', 'TN', 'WA', 'MN', 'OR', 'TX');
}

/**
 * check to see if user is wholesale or not, returns a boolean
 */
function is_wholesale() {
  // Get $wpdb to use in queries
  global $wpdb;

  $woo_cust_id = get_current_user_id();
  $customer_class = $wpdb->get_var( $wpdb->prepare( "SELECT CUSTCLAS FROM randys_customers WHERE WooCustID=%d", $woo_cust_id ) );

  $is_wholesale = 'wholesale' === strtolower($customer_class) ? true : false;
  return $is_wholesale;
}


function cache_randys_credit_values() {
    $woo_cust_id = get_current_user_id();
    if ( in_array( false, array( get_transient( '_randys_credit_limit_' . $woo_cust_id, '_randys_credit_balance_' . $woo_cust_id, '_randys_credit_available_' . $woo_cust_id ) ) ) ) {
        // query and cache values
        global $wpdb;
        $credit = $wpdb->get_row( $wpdb->prepare( "SELECT CreditCurrentAmount, CreditLimit FROM randys_customers WHERE WooCustID = %d", $woo_cust_id ) );
        if ( !empty($credit) ) {
          // customers with negative CreditCurrentAmount cannot have more credit than CreditLimit
          $available_credit = min( $credit->CreditLimit, $credit->CreditLimit - $credit->CreditCurrentAmount );
          set_transient( '_randys_credit_limit_' . $woo_cust_id, $credit->CreditLimit, 60 );
          set_transient( '_randys_credit_balance_' . $woo_cust_id, $credit->CreditCurrentAmount, 60 );
          set_transient( '_randys_credit_available_' . $woo_cust_id, $available_credit, 60 );
        }
    }
}

// maximum possible customer spend
function get_credit_limit() {
    cache_randys_credit_values();
    $woo_cust_id = get_current_user_id();
    return get_transient( '_randys_credit_limit_' . $woo_cust_id );
}

// amount already spent
function get_credit_balance() {
    cache_randys_credit_values();
    $woo_cust_id = get_current_user_id();
    return get_transient( '_randys_credit_balance_' . $woo_cust_id );
}

// amount available to spend
function get_credit_available() {
    cache_randys_credit_values();
    $woo_cust_id = get_current_user_id();
    return get_transient( '_randys_credit_available_' . $woo_cust_id );
}


/**
 * Get user's credit html for use on My Account dashboard
 */
function get_credit_html() {
  // Get $wpdb to use in queries
  global $wpdb;

  $credit_html = 'N/A';

  if (get_credit_limit()) {
    $credit_html = '<span class="float-left bold">Credit Limit:</span><span class="float-right"> $' . number_format_i18n( get_credit_limit(), 2 ) . '</span><span class="clearfix"></span>';
    $credit_html .= '<span class="float-left bold">Current Balance:</span><span class="float-right"> $' . number_format_i18n( get_credit_balance(), 2 ) . '</span><span class="clearfix"></span>';
    $credit_html .= '<span class="float-left bold">Remaining Balance:</span><span class="float-right"> $' . number_format_i18n( get_credit_available(), 2 ) . '</span><span class="clearfix"></span>';
  }

  return $credit_html;
}


/**
 * populate the wholesale access gravity form with user's ID
 **/
function woo_number_population_function( $value ) {
    return get_current_user_id();
}
add_filter( 'gform_field_value_woo_number', __NAMESPACE__ . '\\woo_number_population_function' );


/*
* Remove WooCommerce Default styles
**/
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );


function get_product_availability_by_warehouse($post_id) {
    // Import wpdb so we can run SQL commands in this function
  global $wpdb;

  // First let's get the SKU for the product so we can look up its availability
  $sku = get_post_meta($post_id, '_sku', true);

  // We will use this array as 'CA' => true if it's available and 'CA' => false if it's unavailable
  $availability_by_warehouse = array();

  // Pull in the list of warehouses
  $warehouse_ids = get_warehouse_ids();

  // Set all warehouse_ids as 0 before continuing
  foreach ($warehouse_ids as $warehouse_id) {
    $availability_by_warehouse[$warehouse_id] = 0;
  }

   // Make sure we have an actual sku here
  if (!empty($sku)) {


    // Query up the inventory
    $inventory_array = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT item, warehouse, qty
          FROM randys_InventoryByWarehouse
          WHERE item = %s",
        array($sku)
      )
    );

    // Loop through the inventories, marking them as available or not
    foreach ($inventory_array as $inventory) {
      if ($inventory->qty !== '0') {
        $availability_by_warehouse[$inventory->warehouse] = (int)$inventory->qty;
      }
    }
  }

  return $availability_by_warehouse;
}

/**
* Check and output product Availability in Warehouse
*
* @param $post_id accepts an integer of the Post's ID to get the warehouse's availability from
**/
function warehouse_availability() {

  $availability_by_warehouse = get_product_availability_by_warehouse(get_the_ID());

  // Initialize the output with an opening up of html
  $data =
  '<div class="warehouse-availability m-b-1">
    <div class="warehouse-availability__title sm">
      Warehouse Availability
    </div>
    <ul class="warehouse-availability__list list-inline">';

  // Loop through the states and generate the HTML for each
  foreach ($availability_by_warehouse as $warehouse_id => $quantity) {

    // If it's not available, mark it as unavailable
    $unavailable_class = $quantity ? '' : 'warehouse-availability__list-item--not-active';
    $icon = $quantity ? 'fa fa-check' : 'fa fa-times';

    // Generate the code for this warehouse object
    $data .= '<li class="warehouse-availability__list-item ' . $unavailable_class . '">
                <span class="warehouse-availability__list-icon"><i class="' . $icon . '" aria-hidden="true"></i></span>
                <span class="warehouse-availability__list-name">' . $warehouse_id . '</span>
              </li>';
  }

  // Cap off the end of the html output
  $data .= '
    </ul>
  </div>';

  return $data;
}

/*
* Product Archive
* Determine what what CTA button to add depending on product availablity
*/
function archive_cart_button_availability(\WC_Product $product) {
  $availability_by_warehouse = get_product_availability_by_warehouse($product->get_id());
  if( '0.00' === $product->get_price() ) {
    return '<a href="/contact-us" rel="nofollow" class="button add_to_cart_button">Call for Price</a>';
  } elseif( !array_sum($availability_by_warehouse) ) {
    return '<a href="/contact-us" rel="nofollow" class="button add_to_cart_button">Call for Details</a>';
  } elseif( !is_product()) {
    return do_action( 'woocommerce_after_shop_loop_item' );
  } else {
    return '<a rel="nofollow" href="?add-to-cart=' . $product->id . '" data-quantity="1" data-product_id="' . $product->id . '" class="button button--block product_type_simple add_to_cart_button ajax_add_to_cart">Add to cart</a>';
  }
}

/*
* Product Single
* Determine what what CTA button to add depending on product availablity
*/
function single_cart_button_availability() {

  $availability_by_warehouse = get_product_availability_by_warehouse(get_the_ID());

  if( !array_sum($availability_by_warehouse) ) {
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
  }
}
add_action( 'woocommerce_before_single_product', __NAMESPACE__ . '\\single_cart_button_availability', 1 );

/*
 * Remove SKU from sinlge product summary
 */
remove_action( 'woocommerce_single_product_summary', __NAMESPACE__ . '\\woocommerce_template_single_meta', 40 );

/*
 * Remove Default Sorting
 */
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

/*
 * Remove single product page meta
 */
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

/*
 * Remove excerpt from single product summary
 * Add the_content() to product summary
 */
remove_action( 'woocommerce_single_product_summary', __NAMESPACE__ . '\\woocommerce_template_single_excerpt', 20 );
add_action( 'woocommerce_single_product_summary', 'the_content', 20 );

/**
* Add reviews to the bottom of the page
*/
add_action( 'woocommerce_after_single_product', 'comments_template', 20 );


/**
* Remove Sales Flash
*/
function remove_sales_flash() {
    return false;
}
add_filter('woocommerce_sale_flash', __NAMESPACE__ . '\\remove_sales_flash');

/**
* Remove default Pagination from Shop page
*/
remove_action( 'woocommerce_after_shop_loop', __NAMESPACE__ . '\\woocommerce_pagination', 10 );

/*
* Product Single
* Determine back button link and text
*/
function single_product_back_button() {
  $url_path = $_SERVER['REQUEST_URI'];

  if ( strpos($url_path, 'diff-wizard') !== false ) {
    if ( ! session_id() ) {
      session_start();
    }
    $_SESSION['product-back-button-text'] = 'Return to Diff Wizard Results';
    $_SESSION['product-back-button-url'] = $url_path;
  } elseif( strpos($url_path, 'product-category') !== false || is_search() ) {
    if ( ! session_id() ) {
      session_start();
    }
    $_SESSION['product-back-button-text'] = 'Back to Product List';
    $_SESSION['product-back-button-url'] = $url_path;
  } elseif( strpos($url_path, 'product') === false) {
     if ( ! session_id() ) {
      session_start();
    }
    unset(
      $_SESSION['product-back-button-text'],
      $_SESSION['product-back-button-url']
    );
  }
}
add_action('wp', __NAMESPACE__ . '\\single_product_back_button');

/**
* Product Single
* Update Product Single Page Title
**/
function product_single_title_update( $title = '', $args = array(), $escape = true ) {
  if( is_product() ) {
    $title = get_the_title();
    $sku = get_post_meta(get_the_ID(), '_sku', true);
    if( $sku === '' ) {
      return $title;
    } else {
      return '[' . $sku . '] ' . $title;
    }
  }
}
add_filter( 'the_seo_framework_pre_add_title', __NAMESPACE__ . '\\product_single_title_update', 10, 3 );

/**
* Get the product image from the post_id
*/
function get_product_image_by_post_id( $post_id, $small = false ) {

  // Get the image source
  $src = get_post_meta($post_id, '_fullimage', true);

  // Get the upload directory
  $upload_dir = untrailingslashit( wp_upload_dir()['baseurl'] );

  // If we have no source, use the default instead
  if ($src) {

    // Check if file exists in our uploads folder
    $file = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/uploads/product-images/'. $src;
    if(file_exists($file)){
      $output = $upload_dir . '/product-images/' . $src;
    } else {
      $output = '/wp-content/themes/randysworldwide/dist/images/randys_product_default_sm.png';
    }
    // Generate the full image source
    return $output;
  }

  // If we got to this part, an image has not been found.
  if ($small) {
    return '/wp-content/themes/randysworldwide/dist/images/randys_product_default_sm.png';
  } else {
    return '/wp-content/themes/randysworldwide/dist/images/randys_product_default.png';
  }
}

/**
* Get mapped product images and fallback
*/
function custom_woocommerce_placeholder_img_src( $src ) {
  if( ! is_admin() || defined( 'DOING_AJAX' ) && DOING_AJAX ) {
    global $post;
    $src = get_product_image_by_post_id($post->ID, false);
  } else {
    $src = '/wp-content/themes/randysworldwide/dist/images/randys_product_default.png';
  }
  return $src;
}
add_filter('woocommerce_placeholder_img_src', __NAMESPACE__ . '\\custom_woocommerce_placeholder_img_src');

/**
 * Add Vehicle Information fields to the checkout
 * function woocommerce_form_field_radio() is to add a radio button group
 */
function woocommerce_form_field_radio( $key, $args, $value = '' ) {
  global $woocommerce;
  $defaults = array(
    'type' => 'radio',
    'label' => '',
    'placeholder' => '',
    'required' => false,
    'class' => array(),
    'label_class' => array(),
    'return' => false,
    'options' => array(),
  );
  $args = wp_parse_args( $args, $defaults );

  $required = ( $args[ 'required' ] ) ? ' <abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>' : '';
  switch ( $args[ 'type' ] ) {
    case "select":
      $options = '';
      if ( !empty( $args[ 'options' ] ) ) {
        $count = 0;
        foreach ( $args[ 'options' ] as $option_key => $option_text ) {
          $checked = $count === 1 ? 'checked="checked"' : '';
          $options .= '<li><input type="radio" name="'.$key .'" id="'.$key.'" value="'.$option_key.'" '.selected( $value, $option_key, false ).' '.$checked.' > <span>'.$option_text.'</span></li>';
          $field = '<ul class="form-row '.implode( ' ', $args[ 'class' ] ).'" id="'.$key.'_field">'.$options.'</ul>';
          $count++;
        }
      }
      break;
  }
  if ( $args[ 'return' ] ) {
    return $field;
  } else {
    echo $field;
  }
}




 // Ajax cart contents icon
function woocommerce_header_add_to_cart_fragment( $fragments ) {
	global $woocommerce;

	ob_start();

  $contents = false === strpos($fragments['div.widget_shopping_cart_content'], 'No products in the cart.') ? 'items' : 'empty';

	?>
	<a href="<?php echo WC()->cart->get_cart_url(); ?>" class="header-cart-button"><span class="hidden-md-down">View Cart </span><i class="fa fa-shopping-cart" aria-hidden="true"></i><div class="cart-count <?= $contents ?>"><?php echo WC()->cart->get_cart_contents_count(); ?></div></a>
	<?php

	$fragments['a.header-cart-button'] = ob_get_clean();

	return $fragments;
}
add_filter('add_to_cart_fragments', __NAMESPACE__ . '\\woocommerce_header_add_to_cart_fragment');

function price_lock_level_query($product_sku = null) {
  global $wpdb;

  $results = $wpdb->get_results(
    $wpdb->prepare(
      "SELECT c.custnmbr,
              c.prclevel,
              pl.price
          FROM randys_customers c
            LEFT JOIN randys_pricelock pl ON pl.custnmbr = c.custnmbr AND pl.itemnmbr=%s
          WHERE c.woocustid=%s",
      array($product_sku, get_current_user_id())
    )
  );

  return $results;
}

/**
 * Check users price level and map to post meta value
 */
function check_user_price_level() {
  if ( is_user_logged_in() && is_wholesale() ) {
    $price_level = price_lock_level_query();
  }

  if( isset($price_level) ) {
    $price_level = $price_level[0]->prclevel;
  } else {
    $price_level = null;
  }

  switch( $price_level ) {
    case 'P2':
      return '_price_2';
    case 'P4':
      return '_price_4';
    case 'P5':
      return '_price_5';
    case 'P6':
      return '_price_6';
    default:
      return '_price_3';
  }

}

/**
 * Custom price overriding
 */
function list_price($price, \WC_Product $product) {
  $cache_key = 'product_' . $product->id . 'user_' . get_current_user_id();
  if( get_transient( $cache_key ) ) {
    $price = get_transient( $cache_key );
  } else {
    $price = NAN;
    // Get $wpdb to use in queries
    global $wpdb;

    $product_sku = get_post_meta(
      $product->get_id(), '_sku'
    );

    // Setup post meta to retrive price levels
    $product_meta = get_post_meta($product->get_id());
    if ( is_user_logged_in() && is_wholesale() ) {

      $results = price_lock_level_query($product_sku[0]);

      // Check for price lock first
      if ( null !== $results[0]->price ) {
        $price = $results[0]->price;
      } else {
        // If no price lock, look at the customer price level
        $price_level = $results[0]->prclevel;
        $custnmbr = $results[0]->custnmbr;
        // if $price_level is null or P2 use the Price price
        if ( 'P2' === $price_level || null === $price_level ) {
          $price = $product_meta['_price_2'][0];
        } elseif ( 'P4' === $price_level ) {
          $price = $product_meta['_price_4'][0];
        } elseif ( 'P5' === $price_level ) {
          $price = $product_meta['_price_5'][0];
        } elseif ( 'P6' === $price_level ) {
          $price = $product_meta['_price_6'][0];
        } else {
          // if $price_level isn't any of those, it is set to MAP price
          $price = $product_meta['_price_3'][0];
        }

      }
    } else {
      // If customer is not logged in use the MAP price
      $price = $product_meta['_price_3'][0];
    }
    // Store in database
    set_transient($cache_key, $price, 1 * HOUR_IN_SECONDS);
  }
  return $price;
}
add_filter( 'woocommerce_get_price', __NAMESPACE__ . '\\list_price', 10, 2 );

function new_price_html($price, \WC_Product $product) {
  $map_price = wc_price( get_post_meta($product->get_id(), '_price_3')[0] );
  // If price is different than MAP price, show map price crossed out
  if ( '<span class="amount">Free!</span>' !== $price ) {
    if ( $price !== $map_price ) {
      $price = sprintf( '<span class="old-price">[%s]</span>&nbsp;&nbsp; %s', $map_price, $price );
    }
  } else {
    $price = '';
  }
  return $price;
}

function single_price_html($price, \WC_Product $product) {
  $price = new_price_html($price, $product);
  return $price;
}
add_filter( 'woocommerce_get_price_html', __NAMESPACE__ . '\\single_price_html', 100, 2 );

function order_price_html($price, array $product) {
  if ( is_checkout() ) {
    // Show price reduction on checkout page, but not cart page
    $product_sku = get_post_meta(
      $product['product_id'], '_sku'
    );
    $price = $price;
  }
  return $price;
}
add_filter( 'woocommerce_cart_item_subtotal', __NAMESPACE__ . '\\order_price_html', 100, 2 );


/**
** Removes company from Addressy Validation within WooComerce Address Validation plugin
*/
function remove_company_validation($array) {
  foreach($array as $key => $form) {

    foreach($form as $index => $field) {
      if($field['field'] === 'Company') {
        unset($array[$key][$index]);
      }
    }

  }

  // Reset key numbers for both shipping and billing fields
  $shipping = array_values($array['shipping']);
  $billing = array_values($array['billing']);

  // Place reset array keys and values back into $array
  $array = ['billing' => $billing, 'shipping' => $shipping];
  return $array;
}
add_filter('wc_address_validation_addressy_addresses', __NAMESPACE__ . '\\remove_company_validation', 10, 1);


/**
 * Add new endpoints to my account
 */
function custom_account_endpoints() {
  add_rewrite_endpoint( 'digital-banners', EP_ROOT | EP_PAGES );
  add_rewrite_endpoint( 'download-images', EP_ROOT | EP_PAGES );
  add_rewrite_endpoint( 'custom-export', EP_ROOT | EP_PAGES );
  add_rewrite_endpoint( 'order-express', EP_ROOT | EP_PAGES );
  add_rewrite_endpoint( 'order-history', EP_ROOT | EP_PAGES );
  add_rewrite_endpoint( 'order', EP_ROOT | EP_PAGES );
  add_rewrite_endpoint( 'credit-history', EP_ROOT | EP_PAGES );
}
add_action( 'init', __NAMESPACE__ . '\\custom_account_endpoints' );

function custom_account_query_vars( $vars ) {
  $vars[] = 'digital-banners';
  $vars[] = 'download-images';
  $vars[] = 'custom-export';
  $vars[] = 'order-express';
  $vars[] = 'order-history';
  $vars[] = 'order';
  $vars[] = 'credit-history';

  return $vars;
}
add_filter( 'query_vars', __NAMESPACE__ . '\\custom_account_query_vars', 0 );

function digital_banners_account_endpoint_content() {
  wc_get_template('woocommerce/myaccount/banner-images.php');
}
add_action( 'woocommerce_account_digital-banners_endpoint', __NAMESPACE__ . '\\digital_banners_account_endpoint_content' );

function download_images_account_endpoint_content() {
  wc_get_template('woocommerce/myaccount/download-images.php');
}
add_action( 'woocommerce_account_download-images_endpoint', __NAMESPACE__ . '\\download_images_account_endpoint_content' );

function custom_export_account_endpoint_content() {
  wc_get_template('woocommerce/myaccount/custom-export.php');
}
add_action( 'woocommerce_account_custom-export_endpoint', __NAMESPACE__ . '\\custom_export_account_endpoint_content' );

function order_express_account_endpoint_content() {
  wc_get_template('woocommerce/myaccount/order-express.php');
}
add_action( 'woocommerce_account_order-express_endpoint', __NAMESPACE__ . '\\order_express_account_endpoint_content' );

function custom_order_history_endpoint_content() {
  wc_get_template('woocommerce/myaccount/order-history.php');
}
add_action( 'woocommerce_account_order-history_endpoint', __NAMESPACE__ . '\\custom_order_history_endpoint_content' );

function custom_order_endpoint_content() {
  wc_get_template('woocommerce/myaccount/order.php');
}
add_action( 'woocommerce_account_order_endpoint', __NAMESPACE__ . '\\custom_order_endpoint_content' );

function custom_credit_history_endpoint_content() {
  wc_get_template('woocommerce/myaccount/credit-history.php');
}
add_action( 'woocommerce_account_credit-history_endpoint', __NAMESPACE__ . '\\custom_credit_history_endpoint_content' );

// Check if customer has orders with status of 'returned' and get orders
function returned_orders() {
  global $wpdb;
  $woo_cust_id = get_current_user_id();
  $returned_orders = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM randys_orders o LEFT JOIN randys_customers c ON c.custnmbr = o.randys_customer_id WHERE c.woocustid=%s AND status='returned'", array($woo_cust_id) ) );

  return $returned_orders;
}

// define the yith_wcwl_add_to_wishlisth_button_html callback
function filter_yith_wcwl_add_to_wishlisth_button_html( $template, $wishlist_url, $product_type, $exists ) {
  if ( 1 != \Customer_Access::current_class() ) {
    $template = '';
  }
  return $template;
};

// add the filter
add_filter( 'yith_wcwl_add_to_wishlisth_button_html', __NAMESPACE__ . '\\filter_yith_wcwl_add_to_wishlisth_button_html', 10, 4 );


// Get Shipping ID
function get_shipping_id() {
  global $wpdb;
  $woo_cust_id = get_current_user_id();
  $shipping_id = $wpdb->get_var( $wpdb->prepare( "SELECT ShipperID FROM randys_customers WHERE WOOCustID=%d", $woo_cust_id ) );

  if ( $shipping_id ) {
    return $shipping_id;
  } else {
    return false;
  }
}

/**
 * Show shipping ID on customer shipping address page
 */
function add_shipping_id( $available_gateways ) {
  $shipping_id = get_shipping_id();

  echo '<div class="card--inline m-b-3 position-right position-right--50">';
  if ( isset($shipping_id) && $shipping_id ) {
    echo '<p>Shipping ID: ' . $shipping_id . '</p>';
  } else {
    echo '<p>Please contact RANDYS to add a Shipper Account ID to your account.</p>';
  }
  echo '</div>';
}
add_filter('woocommerce_before_edit_account_address_form', __NAMESPACE__ . '\\add_shipping_id' );


function check_current_cart($product_id) {
  global $woocommerce;
  foreach($woocommerce->cart->get_cart() as $key => $val ) {
    $_product = $val['data'];

    if($product_id == $_product->id ) {
      return true;
    }
  }

  return false;
}

/*
** Get Alternate Items
*/
function get_alternate_items_query($yearOption, $driveTypeOption, $sideOption, $modelOption, $diffIDOption) {
  global $wpdb;

  $alternate_items = $wpdb->get_results(
    $wpdb->prepare(
    "SELECT DISTINCT
      p.ProductNumber AS PrimaryProductNumber,
      p.ProductId AS PrimaryID,
      aapl2.Priority,
      alternates.ProductNumber,
      alternates.ProductID,
      alternates.Title,
      p2.ProxyNumber,
      p2.MAP,
      p2.Qty
    FROM randys_advancedsearch a /* Starting diff information */


    JOIN randys_product p /* Starting products */
      ON p.ProductID = a.ProductID

    JOIN randys_aa_alternateproductlines aapl /* Based upon starting product line / category */
      ON aapl.ProductLine = p.ProductLine
      AND aapl.SubCategoryId = a.CategoryID

    JOIN randys_aa_scenariocategories asc1 /* Match up to grouping number */
      ON asc1.CategoryID = aapl.CategoryId

    JOIN randys_aa_alternateproductlines aapl2 /* Alternate product lines for grouping number */
      ON asc1.CategoryID = aapl2.CategoryId

    JOIN randys_product p2 /* Alternate products */
      ON p2.ProductLine = aapl2.ProductLine
      AND (p.Ratio IS NULL OR p2.Ratio >= p.Ratio)
      AND (p.EndRatio IS NULL OR p2.EndRatio <= p.EndRatio)
      AND p2.Qty > 0

    JOIN randys_advancedsearch alternates /* Alternate products advanced */
      ON alternates.ProductID = p2.ProductID
      AND alternates.CategoryID = aapl2.SubCategoryId
      AND alternates.DiffID = a.DiffID
      AND alternates.ModelID = a.ModelID
      AND alternates.MakeID = a.MakeID
      AND alternates.DriveType = a.DriveType
      AND alternates.Side = a.Side
      AND alternates.ProductID != a.ProductID
      AND alternates.StartYear <= %d
      AND alternates.EndYear >= %d

    JOIN randys_brands b
        /* contains BannerId for 'Best Seller', 'Best Value' banners (see ProductBanners table) */
      ON b.BrandID = alternates.BrandID

    WHERE  a.DriveType = %s
      AND a.Side = %s
      AND a.Model = %s
      AND a.StartYear <= %d
      AND a.EndYear >= %d
      AND a.DiffID = %d
    ORDER BY
      PrimaryProductNumber,
      Priority
    ",
    array($yearOption, $yearOption, $driveTypeOption, $sideOption, $modelOption, $yearOption, $yearOption, $diffIDOption)
    )
  );

  return $alternate_items;
}

/*
** Get Complementary Items
*/
function get_complementary_items($diffid, $diffyear, $make, $model, $drivetype, $customer_number, $product_number) {

  global $wpdb;
  // Get Complementary product items
  $complementary_items = $wpdb->get_results(
    $wpdb->prepare(
      "SELECT DISTINCT this_addOns.productnumber AS ProductNumber, P.ProductID as ProductID
      FROM (
        SELECT DISTINCT
        tmp_addons.priority,
        C.categoryname,
        C.categoryID,
        P.productline,
        P.ProductID,
        P.productnumber,
        %s AS 'VehicleDiffId',
        %s AS 'VehicleModel',
        %s AS 'VehicleYear',
        %s AS 'VehicleMake',
        %s AS 'VehicleDriveType'

      FROM randys_product P

      INNER JOIN randys_categoryproduct CP
        ON CP.productID = P.ProductID

      INNER JOIN randys_category C
        ON C.categoryID = CP.categoryID

      INNER JOIN (-- @bb
        -- GetAlternatesAndAddons
        -- add alternates that are an equal or higher priority than the selected product line.

       SELECT
         priority,
         productline,
         'AlternateProductLines' AS type,
         SubCategoryId

         FROM
           randys_aa_alternateproductlines APP

         WHERE
           categoryid IN
             (SELECT
               category_1.categoryID

               FROM
               -- get the grouping number (called Category) that the selected product/subcategory is in.
                 (SELECT
                   categoryID

                 FROM
                   randys_aa_scenariocategories
                 WHERE CategoryID in (
                   SELECT DISTINCT CategoryID FROM randys_aa_alternateproductlines

                   WHERE ProductLine = (
                    SELECT ProductLine FROM randys_product WHERE ProductNumber = %s
                   )

                  AND SubCategoryId = (
                    SELECT C.CategoryID from randys_category C
                    INNER JOIN randys_categoryproduct CP ON CP.CategoryID = C.CategoryID
                    INNER JOIN randys_product P ON P.ProductID = CP.ProductID
                    WHERE ProductNumber = %s LIMIT 1
                  )
                )
              ) category_1
            )

            UNION
            SELECT
              priority,
              productline,
              'AddOnProductLines' AS type,
              SubCategoryId

            FROM randys_aa_addonproductlines

            WHERE
              categoryID IN (SELECT
                category_2.categoryID

              FROM
                (SELECT DISTINCT
                  categoryID
                FROM
                  randys_aa_alternateproductlines
                WHERE  CategoryID in (
                  select DISTINCT CategoryID from randys_aa_alternateproductlines

                  where ProductLine = (
                    select ProductLine from randys_product where ProductNumber = %s
                  )
                  AND SubCategoryId = (
                    select C.CategoryID from randys_category C
                    inner join randys_categoryproduct CP ON CP.CategoryID = C.CategoryID
                    inner Join randys_product P on P.ProductID = CP.ProductID
                    where ProductNumber = %s limit 1
                  )
                )
              ) category_2
            )
            UNION SELECT
              priority,
              productline,
              'CheckoutProductLines' AS type,
              SubCategoryId
            FROM
              randys_aa_checkoutproductlines
            ORDER BY priority
            -- End GetAlternatesAndAddons
        ) tmp_addons -- [tmp_addons] add all Checkout product lines.

        ON tmp_addons.productline = P.productline
        AND tmp_addons.SubCategoryId = C.categoryID
        WHERE  P.productnumber <> %s
        AND tmp_addons.type = 'AddOnProductLines'

      ) this_addOns -- addons end

      INNER JOIN randys_advancedsearch A
        ON this_addOns.productnumber = A.productnumber
        AND ( ( A.diffid = this_addOns.VehicleDiffId
        AND A.model = this_addOns.VehicleModel )
        OR A.cattype = 'S' )
        AND this_addOns.categoryID = A.categoryID

      INNER JOIN randys_product P
        ON A.productID = P.ProductID

      INNER JOIN randys_brands B
        ON B.brandID = A.brandID

      LEFT OUTER JOIN randys_brands PB
      ON PB.BannerId = B.BannerID
        WHERE (%d = '0' or A.BrandID IN (select BrandID from randys_customerbrands where customerID = %d))

      UNION
        SELECT DISTINCT this_chkOut_1.productnumber AS ProductNumber, P.ProductID as ProductID
        FROM (SELECT DISTINCT
          C.categoryname,
          C.categoryID,
          P.productline,
          P.ProductID,
          P.productnumber,
          %s AS 'VehicleDiffId',
          %s AS 'VehicleModel',
          %s AS 'VehicleYear',
          %s AS 'VehicleMake',
          %s AS 'VehicleDriveType'

          FROM randys_product P

          INNER JOIN randys_categoryproduct CP
            ON CP.productID = P.ProductID

          INNER JOIN randys_category C
            ON C.categoryID = CP.categoryID

          INNER JOIN (
            SELECT
              productline,
              priority,
              SubCategoryId

            FROM randys_aa_checkoutproductlines

            ORDER  BY priority
          ) a -- tmp_checkouts Get Checkouts
            WHERE  P.productnumber <> %s
        ) this_chkOut_1  -- checkOut

        INNER JOIN randys_advancedsearch A
          ON this_chkOut_1.productnumber = A.productnumber
          AND ( ( A.diffid = this_chkOut_1.VehicleDiffId
          AND A.model = this_chkOut_1.VehicleModel )
          OR A.cattype = 'S' )
          AND this_chkOut_1.categoryID = A.categoryID

        INNER JOIN randys_product P
          ON A.ProductID = P.ProductID

        INNER JOIN randys_brands B
          ON B.brandID = A.brandID

        WHERE (%d = '0' or A.BrandID IN (select BrandID from randys_customerbrands where customerID = %d))

      -- This gets the RPS Book.
      UNION
      SELECT this_chkOut_2.productnumber AS ProductNumber, P.ProductID as ProductID
      FROM (SELECT DISTINCT
        C.categoryname,
        C.categoryID,
        P.productline,
        P.ProductID,
        P.productnumber,
        %s AS 'VehicleDiffId',
        %s AS 'VehicleModel',
        %s AS 'VehicleYear',
        %s AS 'VehicleMake',
        %s AS 'VehicleDriveType'
      FROM   randys_product P

      INNER JOIN randys_categoryproduct CP
        ON CP.productID = P.ProductID

      INNER JOIN randys_category C
        ON C.categoryID = CP.categoryID

      INNER JOIN (
        SELECT
          productline,
          priority,
          SubCategoryId
        FROM   randys_aa_checkoutproductlines
        ORDER  BY priority
        ) a -- tmp_checkouts Get Checkouts
        WHERE  P.productnumber <> %s
      ) this_chkOut_2

      INNER JOIN randys_advancedsearch A
        ON this_chkOut_2.productnumber = A.productnumber

      INNER JOIN randys_product P
        ON A.productID = P.ProductID

      INNER JOIN randys_brands B
        ON B.brandID = A.brandID

      WHERE  P.productnumber = %s
      ORDER BY productnumber",
      array(
        $diffid,
        $model,
        $diffyear,
        $make,
        $drivetype,
        $product_number,
        $product_number,
        $product_number,
        $product_number,
        $product_number,
        $customer_number,
        $customer_number,
        $diffid,
        $model,
        $diffyear,
        $make,
        $drivetype,
        $product_number,
        $customer_number,
        $customer_number,
        $diffid,
        $model,
        $diffyear,
        $make,
        $drivetype,
        $product_number,
        'RPSBOOK-01'
      )
    )
  );

  if( $complementary_items ) {
    $comp_output = '<div class="products-slider m-t-3">';

    foreach($complementary_items as $key => $complementary) {

      $product_post_ID = $wpdb->get_results(
        $wpdb->prepare(
          "SELECT post_id FROM wp_postmeta WHERE meta_key = %s AND meta_value = %d",
          array('_randy_productid', $complementary->ProductID)
        )
      );
      $post_id = $product_post_ID[0]->post_id;

      ob_start();
      include(locate_template('templates/content-product-list-sm.php', false, false));
      $comp_output .= ob_get_clean();
    }
    $comp_output .= '</div>';
    echo $comp_output;
  }

}

/*
** Tells us when we need to clear session data
*/
function complentary_session_reset_data() {
  // Diff Wizard session data
  $url_path = $_SERVER['REQUEST_URI'];
  if (strpos($url_path, 'diff-wizard') !== false || strpos($url_path, 'product') !== false ) {
    if ( ! session_id() ) {
      session_start();
    }
  } else {
    if ( ! session_id() ) {
      session_start();
    }
    unset(
      $_SESSION['diffID'],
      $_SESSION['diffyear'],
      $_SESSION['make'],
      $_SESSION['model'],
      $_SESSION['drivetype']
    );
  }
}
add_action('wp', __NAMESPACE__ . '\\complentary_session_reset_data');

function reset_password_message() {
  return 'If you haven\'t logged in yet on our new site you\'ll need to <a href="/my-account/lost-password/">reset your password</a> first!';
}

/**
* Set the Args for product archives wp_query()
**/
function product_archive_query_args($productIDs, $selectedSort, $offset = 0) {
  $meta_key = '';
  $orderby = '';

  if( isset( $selectedSort )) {
    if( $selectedSort === 'sku' ) {
      $meta_key = '_sku';
      $orderby = 'meta_value';
      $order = 'ASC';
    } elseif ( $selectedSort === 'yukon' ) {
      $meta_key = '_is_yukon';
      $orderby = 'meta_value';
      $order = 'DESC';
    } elseif ( $selectedSort === 'price' ) {
      $meta_key = check_user_price_level();
      $orderby = 'meta_value_num';
      $order = 'ASC';
    }
  }

  $args = array(
    'post_type' => 'product',
    'post__in' => $productIDs,
    'posts_per_page' => 25,
    'offset' => $offset,
    'meta_key' => $meta_key,
    'orderby' => $orderby,
    'order' => $order,
  );

  return $args;
}

function product_archive_query($args, $alternate_items, $selectedSort) {
   $loop = new \WP_Query( $args );
   $results_count = $loop->found_posts;
   $total_pages = $loop->max_num_pages;

  $price_sort_array = array();
  $product_list = '';
  while ( $loop->have_posts() ) : $loop->the_post(); global $product;
    ob_start();
    include(locate_template('templates/content-product-list.php', false, false));
    if( $selectedSort === 'price' ) {
      $price_sort_array[$product->get_price()] = [$product->get_price(), ob_get_clean()];
    } else {
      $product_list .= ob_get_clean();
    }

  endwhile;

  // If we are sorting by price we need to sort after we have get_price() values
  if( $selectedSort === 'price' ) {
    sort($price_sort_array);
    foreach( $price_sort_array as $product_output ) {
      $product_list .= $product_output[1];
    }
  }

  return array($product_list, $results_count, $total_pages);
}

// Get a list of ids based on filter results
function get_product_id_query($diffID = null, $parentID = null, $category = null) {
  global $wpdb;

  if( isset($_GET["another-make"]) ) {
    $product_results = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT DISTINCT post_id
        FROM wp_postmeta
        WHERE meta_key = %s
        AND meta_value
        IN (SELECT ProductID FROM randys_advancedsearch WHERE DiffID = %s)",
        array('_randy_productid', $diffID)
      )
    );
  } elseif (isset($diffID) && isset($parentID) && isset($category) ) {
    $product_results = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT post_id
        FROM wp_postmeta
        WHERE meta_key = %s
        AND meta_value
        IN (SELECT ProductID FROM randys_advancedsearch WHERE DiffID = %s AND ParentID = %d AND Category = %s )",
        array('_randy_productid', $diffID, $parentID, $category)
      )
    );
  } elseif(isset($diffID) && !isset($parentID) && !isset($category) ) {
    $item = '';
    $value = array('_randy_productid', $diffID);

    if( isset($_GET['diffyear']) ) {
      $item .= 'AND startyear <= %d AND endyear >= %d ';
      array_push($value, $_GET["diffyear"], $_GET["diffyear"]);
    }

    if( isset($_GET['make']) ) {
      $item .= 'AND make = %s ';
      array_push($value, $_GET['make']);
    }

    if( isset($_GET['model']) ) {
      $item .= 'AND model = %s ';
      array_push($value, $_GET['model']);
    }

    if( isset($_GET['drivetype']) ) {
      $split_drivetype = explode(" Diff - ", $_GET['drivetype']);

      $item .= 'AND drivetype = %s  AND side = %s';
      array_push($value, $split_drivetype[1], $split_drivetype[0]);
    }

    $product_results = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT post_id FROM wp_postmeta WHERE meta_key = %s AND meta_value IN (SELECT ProductID FROM randys_advancedsearch WHERE DiffID = %s " . $item . ")", $value)); // WPCS: unprepared SQL OK

  } else {
    $category_where = '';
    $category_data = '';
    if( isset($category) ) {
      $category_where = 'AND Category = %s';
      $category_data = $category;
    }
    $query = $wpdb->prepare("SELECT post_id FROM wp_postmeta WHERE meta_key = %s AND meta_value IN (SELECT ProductID FROM randys_advancedsearch WHERE DiffID = %s " . $category_where . " )", array('_randy_productid', $diffID, $category_data)); // WPCS: unprepared SQL OK
    $product_results = $wpdb->get_results($query); // WPCS: unprepared SQL OK
  }
  return $product_results;
}

// Get a list of ids based on category
function get_product_id_from_cat_query($category) {
  global $wpdb;

  $product_results = $wpdb->get_results(
    $wpdb->prepare(
      "SELECT DISTINCT post_id
      FROM wp_postmeta
      JOIN randys_categoryproduct cp ON cp.ProductID = wp_postmeta.meta_value
      JOIN randys_category c ON c.CategoryID = cp.CategoryID
      WHERE wp_postmeta.meta_key = %s
      AND (c.CategoryID = %d OR c.ParentID = %d)",
      array('_randy_productid', $category, $category)
    )
  );

  return $product_results;
}

// define the angelleye_woocommerce_express_checkout_set_express_checkout_request_args callback 
function filter_checkout_set_express_checkout_request_args( $this_paypal_request ) {
  $items_array = array();
  foreach($this_paypal_request['Payments'][0]['order_items'] as $item) {
    $item['name'] = str_replace("&", "and", $item['name']);
    array_push($items_array, $item);
  }

  $this_paypal_request['Payments'][0]['order_items'] = $items_array;
  return $this_paypal_request;
}
// add the filter
add_filter( 'angelleye_woocommerce_express_checkout_set_express_checkout_request_args', __NAMESPACE__ . '\\filter_checkout_set_express_checkout_request_args', 10, 1 );

function check_warehouse_availability($stock, $product) {
  return array_sum(get_product_availability_by_warehouse($product->get_id()));
}
add_filter( 'woocommerce_get_stock_quantity', __NAMESPACE__ . '\\check_warehouse_availability', 10, 2 );
