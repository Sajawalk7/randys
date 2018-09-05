<?php
use Roots\Sage\RANDYS;

add_action("wp_ajax_get_product_sort", "get_product_sort");
add_action("wp_ajax_nopriv_get_product_sort", "get_product_sort");

function get_product_sort() {
  global $wpdb;

  if (check_ajax_referer( 'sort_nonce', 'nonce', false ) ) {
    // Setup query for products related to diff id
    $diffID = null;
    if( isset($_GET['diffID']) ) {
      $diffID = (int)$_GET['diffID'];
    }

    $selectSort = null;
    if( isset($_GET['selectedval']) ) {
      $selectedSort = $_GET['selectedval'];
    }

    $selectedCat = null;
    if( isset($_GET['cat']) && $_GET['cat'] !== '' ) {
      $selectedCat = $_GET['cat'];
    }

    $parentID = null;
    if( isset($_GET['parentID']) ) {
      $parentID = $_GET['parentID'];
    }

    $selectedIDs = null;
    if( isset($_GET['productIDs']) &&  $_GET['productIDs'] !== '' ) {
      $selectedIDs = $_GET['productIDs'];
    }

    // Setup the values we need to pass through to get Alternate items
    if( isset($_GET['diffYear']) ) {
      $yearOption = (int)$_GET['diffYear'];
    }
    if( isset($_GET['diffModel']) ) {
      $modelOptionRaw = $_GET['diffModel'];
      $modelOption = str_replace('+', ' ', $modelOptionRaw);
    }
    if( isset($_GET['diffDriveType']) && $_GET['diffDriveType'] !== '' ) {
      $driveTypeRaw = $_GET['diffDriveType'];
      $driveType_pieces = explode("+Diff+-+", $driveTypeRaw);
      $sideOption = $driveType_pieces[0];
      $driveTypeOption = $driveType_pieces[1];
    }
    if( isset($diffID) ) {
      $diffIDOption = $diffID;
    }

    if( isset($_GET['source']) ) {
      $source = $_GET['source'];
    }

  } else {
    // 400 Bad Request
    http_response_code(400);
    echo "Error 400: Bad Request. Reason: Invalid/Missing Information.";
    exit;
  }

  // If alternate items available run query
  $alternate_items = null;
  if( isset($yearOption) &&
      isset($modelOption) &&
      isset($sideOption) &&
      isset($driveTypeOption) &&
      isset($diffIDOption) ) {
    // Runs the query to get a list of all Alternate Items
    $alternate_items = RANDYS\get_alternate_items_query($yearOption, $driveTypeOption, $sideOption, $modelOption, $diffIDOption);
  }

  if( isset($selectedIDs) ) {
    $input = explode(',', $selectedIDs);
    foreach($input as $key => $id) {
      $product_results[$key]['post_id'] = $id;
    }
  } elseif ( isset($diffID) ) {
    // Function will return array of ids
    $input = RANDYS\get_product_id_query($diffID, $parentID, $selectedCat);
    $product_results = json_decode( json_encode($input), true);
  } else {
    // Function will return array of ids
    $input = RANDYS\get_product_id_from_cat_query($selectedCat);
    $product_results = json_decode( json_encode($input), true);
  }

  // Setup string of ids returned by $product_results
  $productIDs = [];
  foreach( $product_results as $id) {
    $productIDs[] = $id['post_id'];
  }

  $args = RANDYS\product_archive_query_args($productIDs, $selectedSort);
  $product_list = RANDYS\product_archive_query($args, $alternate_items, $selectedSort);

  $output['product'] = $product_list[0];
  $output['productIDs'] = $productIDs;
  echo json_encode($output);;
  die();

}
