<div class="back"><a href="/my-account"><i class="fa fa-arrow-left" aria-hidden="true"></i> My Account</a></div>
<h2>Order History</h2>
<?php
global $wpdb;

$woo_cust_id = get_current_user_id();

$customer_num = $wpdb->get_var(
  $wpdb->prepare("SELECT CUSTNMBR FROM randys_customers WHERE WOOCustID = %d", $woo_cust_id)
);

// Get report access level
$access = 2;
if (isset($_GET['level'])) {
  $access = intval($_GET['level']);
}

$orders = array();

if ( 1 === Customer_Access::current_class() && 1 === Customer_Access::current_relationship() && 2 === $access ) {
  // If user is a WHOLESALE parent account

  // Get all children accounts
  $children = $wpdb->get_results(
    $wpdb->prepare("SELECT CUSTNMBR FROM randys_customers WHERE ParentID = %d AND CUSTNMBR != %d", array($customer_num, $customer_num))
  );

  $where_data = array();
  $count = 0;
  $where_statement = "WHERE randys_customer_id = ";

  foreach ( $children as $child => $value ) {
    if ( 1 <= $count ) {
      $where_statement .= " OR randys_customer_id = %s";
    } else {
      $where_statement .= "%s";
    }
    array_push($where_data, $value->CUSTNMBR);
    $count++;
  }

  $orders = $wpdb->get_results($wpdb->prepare("SELECT * FROM randys_orders ".$where_statement, $where_data)); // WPCS: unprepared SQL OK

} else {
  // User is child or RETAIL account
  $orders = $wpdb->get_results(
    $wpdb->prepare("SELECT * FROM randys_orders WHERE randys_customer_id = %s", $customer_num)
  );
}

// Get an array of order ids that are in randys_orders
$randys_order_ids = array();
foreach ($orders as $order) {
  $randys_order_ids[] = $order->web_order_id;
}

// Get all the orders that the customer has placed (orders not in randys_orders table will show as processing)
$web_orders_query = "SELECT wp_order_id FROM web_orders WHERE wp_customer_id = %d";

if ($randys_order_ids) {
  $web_orders_query .= " AND wp_order_id NOT IN (" . implode(', ', array_fill(0, count($randys_order_ids), '%s')) . ")";
}
//echo $web_orders_query;

$web_orders = $wpdb->get_results($wpdb->prepare($web_orders_query, array_merge(array($woo_cust_id), $randys_order_ids))); // WPCS: unprepared SQL OK


$sql_order_hist="SELECT * FROM randys_OrderHist WHERE CUSTNMBR = '$customer_num'";
$old_orders = $wpdb->get_results($sql_order_hist);


// If no orders have been made, then state that to the user
if (count($web_orders) == 0 && count($orders) == 0) {
  //if (count($old_orders) == 0) {
    echo "No orders have been made yet";
 // }
} else {

  echo '<div class="row order order--header">';
    echo '<div class="col-xs-3 col-sm-2">INVOICE #</div>';
    echo '<div class="col-xs-3">DATE</div>';
    echo '<div class="col-xs-3">PO #</div>';
    echo '<div class="col-xs-3 col-sm-2">TOTAL</div>';
    echo '<div class="col-xs-2 hidden-xs-down"></div>';
  echo '</div>';

  foreach ( $web_orders as $web_order_result ) {
    // Get the order
    $wp_order_id = (int)(explode('-', $web_order_result->wp_order_id)[0]);
    $wc_order = new WC_Order($wp_order_id);
    $order_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $wc_order->order_date);

    echo '<div class="row order">';
    echo '<div class="col-xs-3 col-sm-2 order__id"><a href="'.$wc_order->get_view_order_url().'">Processing...</a></div>';
    echo '<div class="col-xs-3 order__date">'.$order_datetime->format('Y-m-d').'</div>';
    echo '<div class="col-xs-3 order__status">Processing...</div>';
    echo '<div class="col-xs-3 col-sm-2 order__total"><span class="total">'.$wc_order->get_formatted_order_total().'</span></div>';
    echo '<div class="col-xs-2 hidden-xs-down"><a href="'.$wc_order->get_view_order_url().'" class="button button--short">View</a></div>';
    echo '</div>';
  }

  foreach ( $orders as $order ) {
    echo '<div class="row order">';
    echo '<div class="col-xs-3 col-sm-2 order__id"><a href="/my-account/order/'.$order->randys_order_id.'">'.$order->randys_order_id.'</a></div>';
    echo '<div class="col-xs-3 order__date">'.explode(' ', $order->order_date)[0].'</div>';
    echo '<div class="col-xs-3 order__status">'.$order->po_num.'</div>';
    echo '<div class="col-xs-3 col-sm-2 order__total"><span class="total">$'.$order->order_total.'</span></div>';
    echo '<div class="col-xs-2 hidden-xs-down"><a href="/my-account/order/'.$order->randys_order_id.'" class="button button--short">View</a></div>';
    echo '</div>';
  }
}


// if (count($old_orders) >0) {
//   echo '<div class="row order order--header">';
//     echo '<div class="col-xs-2 col-sm-2">INVOICE #</div>';
//     echo '<div class="col-xs-2">Order #</div>';
//     echo '<div class="col-xs-1">Ordered</div>';
//     echo '<div class="col-xs-1 col-sm-1">Shipped</div>';
//     echo '<div class="col-xs-1 col-sm-1">Total</div>';
//     echo '<div class="col-xs-2 col-sm-2">Shipping</div>';
//     echo '<div class="col-xs-1 col-sm-1">PO #</div>';
//     echo '<div class="col-xs-2 col-sm-2">Tracking</div>';
//   echo '</div>';
//   foreach ( $old_orders as $old_orders_result ) {
//     // Get the order
//    $wp_order_id = (int)(explode('-', $old_orders_result->OrderNumber)[0]);
//     // $wc_order = new WC_Order($wp_order_id);
//     // print_r($wc_order);
//     $order_date = DateTime::createFromFormat('Y-m-d H:i:s', $old_orders_result->OrderDate);
//     $ship_date = DateTime::createFromFormat('Y-m-d H:i:s', $old_orders_result->ShipDate);
//     // exit;
//     echo '<div class="row order">';
//     echo '<div class="col-xs-2 col-sm-2 order__id"><a href="">'.$old_orders_result->InvoiceNumber.'</a></div>';
//     echo '<div class="col-xs-2 col-sm-2 order__id">'.$old_orders_result->OrderNumber.'</div>';
//     echo '<div class="col-xs-1 order__date">'.$order_date->format('Y-m-d').'</div>';
//     echo '<div class="col-xs-1 ship__date">'.$ship_date->format('Y-m-d').'</div>';
//     echo '<div class="col-xs-1 order__status">'.$old_orders_result->Total.'</div>';
//     echo '<div class="col-xs-2 order__status">'.$old_orders_result->ShipMethod.'</div>';
//     echo '<div class="col-xs-1 order__status">'.$old_orders_result->PONumber.'</div>';
//     echo '<div class="col-xs-1 order__status">'.$old_orders_result->TrackingNumbers.'</div>';

//     echo '</div>';
//   }
// }