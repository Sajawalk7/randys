<?php
/**
* Diffwizard API Request
**/
function diffwizard_ajax_request() {
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
    $input = $_REQUEST;
    require_once('filtering-api-functions.php');

    // If the year isn't set, then we are missing the necessary information
    if (!isset($input['year'])) {
      // 400 Bad Request
      http_response_code(400);
      echo "Error 400: Bad Request. Reason: Invalid/Missing Information.";
      exit;
    }

    // Set filters
    class filter {
      const FILTER_ARRAY = [
        'Ring Gear Diameter' => 'RingGearDiameter',
        'Cover Bolts' => 'CoverBolts',
        'Ring Gear Bolts' => 'RingGearBolts',
        'Spline Count' => 'SplineCount',
        'Dropout' => 'Dropout',
        'Pinion Support' => 'PinionSupport',
        'Carrier Breaks' => 'CarrierBreaks',
        'Rear Suspension' => 'RearSuspension',
        'Pinion Nut Size' => 'PinionNutSize',
      ];
    }

    // Stores the general information for the json to send back to the front end
    $output = array(
      'success' => false,
    );

    // If we're missing one of the 4 main fields, query those up.
    if (!isset($input['make']) ||
        !isset($input['model']) ||
        !isset($input['drivetype'])) {
      // Get the dropdown information
      $dropdown_data = get_dropdown($input);
      if( is_string($dropdown_data) ) {
        // 400 Bad Request
        http_response_code(400);
        echo "Error 400: Bad Request. Reason: " . $dropdown_data;
        exit;
      }

      // Reset the request, as it would have changed if only 1 dropdown was found for make, model, or drivetype.
      $input = $dropdown_data['request'];
      $output['dropdowndata'] = $dropdown_data['data'];
      $output['dropdown'] = $dropdown_data['dropdown'];
    }


    // If we have the 4 required fields, query up the differentials
    if (isset($input['year']) &&
        isset($input['make']) &&
        isset($input['model']) &&
        isset($input['drivetype'])) {
      $output['diffdata'] = get_diffs($input);

      // It shouldn't be possible to get 0 entries, unless by front-end glitch or tampering
      if (count($output['diffdata']) === 0) {
        // 400 Bad Request
        http_response_code(400);
        echo "Error 400: Bad Request. Reason: Invalid Information.";
        exit;

      } elseif (count($output['diffdata']) >= 2) {
        // We have more than 2 differentials, try to find the difference between these differentials
        $output['filters'] = get_diff_filters($output['diffdata']);
      }
    }

    // Pack up the request data into the output to send back to the frontend
    $output['input'] = array(
      'year'           => (isset($input['year']) ? $input['year'] : ''),
      'make'           => (isset($input['make']) ? $input['make'] : ''),
      'model'          => (isset($input['model']) ? $input['model'] : ''),
      'drivetype'      => (isset($input['drivetype']) ? $input['drivetype'] : ''),
    );

    foreach (filter::FILTER_ARRAY as $filter) {
      $output['input'][$filter] = isset($input[$filter]) ? $input[$filter] : '';
    }

    $output['success'] = true;

    // Send the payload
    echo json_encode($output);
  }
  die();
}
add_action( 'wp_ajax_nopriv_diffwizard_ajax_request', 'diffwizard_ajax_request' );
add_action( 'wp_ajax_diffwizard_ajax_request', 'diffwizard_ajax_request' );
