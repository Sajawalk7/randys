<?php
use Roots\Sage\RANDYS;

/**
* Get Product Filters Ajax request
**/
add_action("wp_ajax_get_product_filter", "get_product_filter");
add_action("wp_ajax_nopriv_get_product_filter", "get_product_filter");

function get_product_filter() {

  if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

    // Nonce Verification
    if (check_ajax_referer( 'product_filter_nonce', 'nonce', false ) ) {

      // Get the filter that was changed
      $changed_dropdown = null;
      if( isset($_GET['changedDropdown']) ) {
        $changed_dropdown = $_GET['changedDropdown'];
      }

      $parent_value = null;
      if( isset($_GET['parentValue']) ) {
        $parent_value = $_GET['parentValue'];
      }

      $category_value = null;
      if( isset($_GET['categoryValue']) ) {
        $category_value = $_GET['categoryValue'];
      }

      $u_joint_type_value = null;
      if( isset($_GET['uJointTypeValue']) ) {
        $u_joint_type_value = $_GET['uJointTypeValue'];
      }

      if( isset($_GET['source']) ) {
        $source = $_GET['source'];
      }

      if( isset($_GET['selectedSort']) ) {
        $selectedSort = $_GET['selectedSort'];
      }

      // we should always have a diffid
      if( isset($_GET['diffIDSelected']) ) {
        $diffIDSelected = $_GET['diffIDSelected'];
      } else {
        // 400 Bad Request
        http_response_code(400);
        echo "Error 400: Bad Request. Reason: Invalid/Missing Information.";
        exit;
      }
    }

    $input = $_REQUEST;

    // Setup the values we need to pass through to get Alternate items
    $yearOption = $input['year'];
    $makeOption = $input['make'];
    $modelOption = $input['model'];
    $diffIDOption = $diffIDSelected;

    if( $input['drivetype'] !== 'null' ) {
      $driveTypeRaw = $input['drivetype'];

      $driveType_pieces = explode(" Diff - ", $driveTypeRaw);

      $sideOption = $driveType_pieces[0];
      $driveTypeOption = $driveType_pieces[1];
    }

    // Runs the query to get a list of all Alternate Items
    $alternate_items = null;
    if( $input['drivetype'] !== 'null' ) {
      $alternate_items = RANDYS\get_alternate_items_query(
        $yearOption,
        $driveTypeOption,
        $sideOption,
        $modelOption,
        $diffIDOption
      );
    }

    // Set filters
    class filter {
      const FILTER_ARRAY = [
        'Brand' => 'Brand',
        'Brake Type' => 'BrakeType',
        'Bearing Diameter' => 'BearingDiameter',
        'ABS' => 'ABS',
        'Lug Diameter' => 'LugDiameter',
        'Axle Length' => 'AxleLength',
        'Clutch' => 'ClutchInfo',
        'Float Type' => 'FloatType',
        'Spline Count' => 'SplineCount',
        'Gear Ratio' => 'GearRatio',
        'Sub Category' => 'Category',
        'Parent' => 'Parent',
      ];
    }

    class secondary_filter {
      const FILTER_ARRAY = [
        'Yoke Cap Diameter' => 'YokeCapDiameterA',
        'Yoke Span ' => 'YokeSpanA',
        'Yoke Clip Type' => 'YokeClipTypeA',
        'U-Joint Cap Diameter A' => 'UJointCapDiameterA',
        'U-Joint Span A' => 'UJointSpanA',
        'U-Joint Clip Type A' => 'UJointClipTypeA',
        'U-Joint Type' => 'UJointType',
      ];
    }

    class tertiary_filter {
      const FILTER_ARRAY = [
        'U-Joint Cap Diameter B' => 'UJointCapDiameterB',
        'U-Joint Span B' => 'UJointSpanB',
        'U-Joint Clip Type B' => 'UJointClipTypeB',
      ];
    }

    // Select values that should show secondary filters
    class filter_activations {
      const VALUES_ARRAY = [
        'Yokes',
        'Universal Joints',
        'U-joints - 1310',
        'U-joints - 1330',
        'U-joints - 1350',
        'U-joints - Adapter',
        'U-joints - Misc.',
        'U-joints - Off Road Only',
      ];
    }

    $full_filter_list = array_merge_recursive(
      filter::FILTER_ARRAY,
      secondary_filter::FILTER_ARRAY,
      tertiary_filter::FILTER_ARRAY
    );
    

    $active_filters = [];
    if( $yearOption !== '' && $yearOption !== 'null' ) {
      array_push($active_filters, 'year');
    }

    if( $makeOption !== '' && $makeOption !== 'null' ) {
      array_push($active_filters, 'make');
    }
    if( $modelOption !== '' && $modelOption !== 'null' ) {
      array_push($active_filters, 'model');
    }

    foreach ( $full_filter_list as $filter ) {
      if( isset($input[$filter]) ) {
        $active_filters[] = $filter;
      }
    }

    // Stores the general information for the json to send back to the front end
    $output = array(
      'success' => false,
    );

    $output['diffdata'] = get_prod_diffs(
      $diffIDSelected,
      $active_filters,
      $parent_value,
      $category_value,
      $u_joint_type_value
    );

    // It shouldn't be possible to get 0 entries, unless by front-end glitch or tampering
    if ( $diffIDSelected === '' ) {
      // 400 Bad Request
      http_response_code(400);
      echo "Error 400: Bad Request. Reason: Invalid Information.";
      exit;

    } else {
      // We have more than 2 differentials, try to find the difference between these differentials
      $output['filters'] = get_diff_prod_filters(
        $output['diffdata'],
        $active_filters,
        $diffIDSelected,
        $parent_value,
        $category_value,
        $u_joint_type_value
      );
    }

    $product_results = get_prod_list(
      $diffIDSelected,
      $active_filters,
      $alternate_items,
      $source,
      $selectedSort
    );

    // Output Product Content, Count, and available product IDs for sort
    $output['productList'] = $product_results[0];
    $output['productCount'] = $product_results[1];
    $output['productIDS'] = $product_results[2];
    $output['totalPages'] = $product_results[3];

     // Get the changed dropdown send it as part of the request
    if( isset($changed_dropdown) ) {
      $output['changedDropdown'] = $changed_dropdown;
    }

    $output['success'] = true;

    // Send the payload
    echo json_encode($output);
  }
  die();
}

// Get a list of differentials based on search parameters
function get_prod_diffs($diffID, $active_filters, $parent_value, $category_value, $u_joint_type_value) {
  global $wpdb;

  $diff_where_value = '';
  $diff_where_data = [$diffID];
  if( count($active_filters) !== 0 ) {
    foreach($active_filters as $filter_value) {
      // First, make sure the filter has a value then add to where statement
      if ($filter_value === 'year') {
        $diff_where_value .= ' AND startyear <= %d AND endyear >= %d';
        $diff_where_data[] = $_GET['year'];
        $diff_where_data[] = $_GET['year'];
      } elseif ($filter_value == 'GearRatio') {
        $diff_where_value .= " AND find_in_set(%s, replace(GearRatio, ', ', ',')) <> 0";
        $diff_where_data[] = $_GET[$filter_value];
      } else {
        $diff_where_value .= ' AND ' . $filter_value . ' = %s';
        $diff_where_data[] = $_GET[$filter_value];
      }
    }
  }

  // If U-joint Type value is Conversion enable tertiary_filters
  if( $u_joint_type_value === 'Conversion') {

    $select = "diffid, diffname, diffdescription, diffimage, "
              . implode(', ', tertiary_filter::FILTER_ARRAY). ', '
              . implode(', ', secondary_filter::FILTER_ARRAY). ', '
              . implode(', ', filter::FILTER_ARRAY);

  // If Parent or Category value matches one of our filter_activation items enable secondary_filters
  } elseif( in_array($parent_value, filter_activations::VALUES_ARRAY) ||
      in_array($category_value, filter_activations::VALUES_ARRAY)) {

    $select = "diffid, diffname, diffdescription, diffimage, "
              . implode(', ', secondary_filter::FILTER_ARRAY). ', '
              . implode(', ', filter::FILTER_ARRAY);

  } else {

    $select = "diffid, diffname, diffdescription, diffimage, "
              . implode(', ', filter::FILTER_ARRAY);

  }

  // Query up the rows
  $query = $wpdb->prepare("SELECT DISTINCT " . $select . " FROM randys_advancedsearch WHERE DiffID = %d " . $diff_where_value, $diff_where_data); // WPCS: unprepared SQL OK
  $results = $wpdb->get_results($query); // WPCS: unprepared SQL OK

  // Return the differential that matches the queried information
  return $results;
}


function get_prod_list($diffID, $active_filters, $alternate_items, $source, $selectedSort) {
  global $wpdb;

  $where_value = '';
  $where_data = ['_randy_productid', $diffID];
  if( count($active_filters) !== 0 ) {
    foreach($active_filters as $filter_value) {
      if( $filter_value == 'year' ) {
        $where_value .= ' AND startyear <= %d AND endyear >= %d';
        $where_data[] = $_GET['year'];
        $where_data[] = $_GET['year'];
      } elseif( $filter_value == 'SplineCount' ) {
        preg_match_all('!\d+!', $_GET[$filter_value], $matches);
        foreach( $matches[0] as $key => $spline_value ) {
          if( $key === 0 ) {
            $where_value .= ' AND (' . $filter_value . ' = %s';
          } else {
            $where_value .= ' OR ' . $filter_value . ' = %s';
          }

          $where_data[] .= $spline_value;
        }

        // Make sure we close out the parentheses
        $where_value .= ')';

      } elseif ($filter_value == 'GearRatio') {
        $where_value .= " AND find_in_set(%s, replace(GearRatio, ', ', ',')) <> 0";
        $where_data[] = $_GET[$filter_value];
      } else {
        $where_value .= ' AND ' . $filter_value . ' = %s';
        $where_data[] = $_GET[$filter_value];
      }
    }
  }
  $query = $wpdb->prepare("SELECT DISTINCT post_id FROM wp_postmeta WHERE meta_key = %s AND meta_value IN(SELECT ProductID FROM randys_advancedsearch WHERE DiffID = %d" . $where_value . ")", $where_data); // WPCS: unprepared SQL OK
  $product_results = $wpdb->get_results($query); // WPCS: unprepared SQL OK

  $productIDs = [];
  foreach( $product_results as $id) {
    $productIDs[] = $id->post_id;
  }

  $args = RANDYS\product_archive_query_args($productIDs, $selectedSort);
  $product_list = RANDYS\product_archive_query($args, $alternate_items, $selectedSort);

  return array($product_list[0], $product_list[1], $productIDs, $product_list[2]);
}

// Get the secondary dropdowns for different filters
function get_diff_prod_filters($differentials, $active_filters, $diffIDSelected, $parent_value, $category_value, $u_joint_type_value) {
  global $wpdb;

  // Stores all the found filters as 'filter' => array('value1', 'value2', 'value3')
  $found_filters = array();

  // If Conversion is selected U-joint type enable tertiary filters
  if( $u_joint_type_value === 'Conversion') {

    $filter_list = array_merge_recursive(
      tertiary_filter::FILTER_ARRAY,
      secondary_filter::FILTER_ARRAY,
      filter::FILTER_ARRAY
    );

  } elseif( in_array($parent_value, filter_activations::VALUES_ARRAY) ||
      in_array($category_value, filter_activations::VALUES_ARRAY)) {

    $filter_list = array_merge(secondary_filter::FILTER_ARRAY, filter::FILTER_ARRAY);

  } else {
    $filter_list = filter::FILTER_ARRAY;
  }

  // Loop through each of the possible filters
  foreach ($filter_list as $key => $filter) {
    // We will keep an array of all found values
    $found_values = array();
    // Go through each of the differentials to add up the values
    foreach ($differentials as $diff) {
      // If the value isn't present
      if (!in_array($diff->$filter, $found_values) && ($diff->$filter !== '') && ($diff->$filter !== null)) {
        $found_values[] = $diff->$filter;
      }
    }
    rsort($found_values);


    if ( $key === 'Sub Category' || count($found_values) >= 2 || in_array($filter, $active_filters) ) {
      $found_filters[] = array(
        'label' => $key,
        'value' => $filter,
        'data'  => $found_values,
      );
    }
  }


  // Get the 'Spline Count' of the current Diff
  $diff_spline = $wpdb->get_row(
    $wpdb->prepare(
      "SELECT SplineCount From randys_differential WHERE DifferentialID = %d",
      $diffIDSelected
    )
  );

  foreach( $found_filters as $key => $filters ) {

    // Many Gear Ratio items return multiple values in a string.
    // Break them into own value, and update $found_filters array
    if( $filters['label'] === 'Gear Ratio') {
      $gear_ratio_arr = '';
      $ratios_arr = array();
      // Once Gear Ratio is found loop through each item
      foreach( $filters['data'] as $gear_item ) {

        if( strpos($gear_item, ',') ) {
          // If a string has multiple values, break into own value
          $items = explode(', ', $gear_item);
          $ratios_arr = array_merge($items, $ratios_arr);
        } else {
          // Otherwise just add it to the array
          $ratios_arr[] = $gear_item;
        }

        // Sort, remove dupliates, and remove empty values
        rsort($ratios_arr);
        $unique_arr = array_unique($ratios_arr);
        $gear_ratio_arr = array_diff( $unique_arr, array( '' ) );
        $gear_ratio_arr = array_values($gear_ratio_arr);
      }

      // Send updated information back to $found_filters array
      $found_filters[$key]['data'] = $gear_ratio_arr;

    }


    // Handle format of how spline count is being outputted
    if( $filters['label'] === 'Spline Count' && $diff_spline ) {
      // Once Spline Count is found loop through each item
      foreach( $filters['data'] as $spline_item ) {
        if( $spline_item === $diff_spline->SplineCount ) {
          // If spline count is stock
          $stock_spline = 'Stock Spline (' . $spline_item . ' Splines)';
        } else {
          // Otherwise format non-stock output
          $spline_numbers_arr[] = $spline_item;
          sort($spline_numbers_arr);
          $last_spline  = array_slice($spline_numbers_arr, -1);
          $first_spline = join(', ', array_slice($spline_numbers_arr, 0, -1));
          $all_splines  = array_filter(array_merge(array($first_spline), $last_spline), 'strlen');
          $non_stock_spline = 'NON-Stock Spline (' . join(', & ', $all_splines) . ' Splines)';
        }

      }

      $spline_arr = [];
      if( isset($stock_spline ) ) {
        $spline_arr[] = $stock_spline;
      }
      if( isset( $non_stock_spline) ) {
        $spline_arr[] = $non_stock_spline;
      }

      // Send updated information back to $found_filters array
      $found_filters[$key]['data'] = $spline_arr;
    }

  }

  return $found_filters;
}
