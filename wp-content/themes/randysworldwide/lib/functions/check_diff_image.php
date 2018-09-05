<?php
add_action("wp_ajax_check_diff_image", "check_diff_image");
add_action("wp_ajax_nopriv_check_diff_image", "check_diff_image");

function check_diff_image() {

  // get the Image URL and selector passed in
  if( check_ajax_referer( 'main_ajax_nonce', 'nonce', false ) ) {
    $diffURL = $_GET['diffURL'];
    $diffSelector = $_GET['selector'];



    // Check if this file exists in directory
    if(file_exists($_SERVER['DOCUMENT_ROOT'] . $diffURL)) {
      $output['diffURL'] = $diffURL;
    } else {
      $output['diffURL'] = '/wp-content/themes/randysworldwide/dist/images/randys_product_default.png';
    }

    $output['diffSelector'] = $diffSelector;


    // Output image URL
    echo json_encode($output);
  }

  die();

}
