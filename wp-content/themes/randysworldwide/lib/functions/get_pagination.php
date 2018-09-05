<?php
use Roots\Sage\RANDYS;

function more_post_ajax(){
  if( check_ajax_referer( 'main_ajax_nonce', 'nonce', false ) ) {
    $offset = $_GET['offset'];
    $selectedSort = $_GET['selectedSort'];
    $products = $_GET['productIDs'];
    $productIDs = explode(',', $products);

    $yearOption = $_GET['year'];
    $modelOption = $_GET['model'];

    if( isset($_GET['diffID'])) {
      $diffIDOption = $_GET['diffID'];
    }

    if( isset($_GET['drivetype']) && $_GET['drivetype'] !== '' ) {
      $driveTypeRaw = $_GET['drivetype'];

      $driveType_pieces = explode(" Diff - ", $driveTypeRaw);

      $sideOption = $driveType_pieces[0];
      $driveTypeOption = $driveType_pieces[1];
    }


    // Runs the query to get a list of all Alternate Items
    $alternate_items = null;
    if( isset($_GET['drivetype']) && $_GET['drivetype'] !== '' ) {
      $alternate_items = RANDYS\get_alternate_items_query(
        $yearOption,
        $driveTypeOption,
        $sideOption,
        $modelOption,
        $diffIDOption
      );
    }

    $args = RANDYS\product_archive_query_args($productIDs, $selectedSort, $offset);
    $product_list = RANDYS\product_archive_query($args, $alternate_items, $selectedSort);

    echo $product_list[0];
  }
  exit;
}

add_action('wp_ajax_nopriv_more_post_ajax', 'more_post_ajax');
add_action('wp_ajax_more_post_ajax', 'more_post_ajax');
