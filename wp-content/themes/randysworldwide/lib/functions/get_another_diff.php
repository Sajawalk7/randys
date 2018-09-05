<?php
add_action("wp_ajax_get_another_diff", "get_another_diff");
add_action("wp_ajax_nopriv_get_another_diff", "get_another_diff");

function get_another_diff() {
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
    global $wpdb;

    // Nonce Verification
    if (check_ajax_referer( 'another_diff_nonce', 'nonce', false ) ) {
      $selectedMake = $_GET['selectedMake'];
    } else {
      // 400 Bad Request
      http_response_code(400);
      echo "Error 400: Bad Request. Reason: Invalid/Missing Information.";
      exit;
    }

    // If the another-make isn't set, then we are missing the necessary information
    if (!isset($_GET['selectedMake'])) {
      // 400 Bad Request
      http_response_code(400);
      echo "Error 400: Bad Request. Reason: Invalid/Missing Information.";
      exit;
    }

    $output = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT DISTINCT DiffID, DiffName FROM randys_advancedsearch WHERE Make = %s ORDER BY DiffName desc",
        $selectedMake )
    );

    echo json_encode($output);
  }
  die();

}
