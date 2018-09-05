<?php

// Get the dropdown
function get_dropdown($input) {
  global $wpdb;

  $nonce_verified = false;
  if( check_ajax_referer( 'browsing_ajax_nonce', 'nonce', false ) ) {
    $nonce_verified = true;
  }

  // Setup variables depending on filter source
  if( $nonce_verified &&
      isset($_GET['source']) &&
      $_GET['source'] === 'product-browsing') {
    $make = 'cat-make';
    $model = 'cat-model';
    $drivetype = 'cat-drivetype';
  } else {
    $make = 'make';
    $model = 'model';
    $drivetype = 'drivetype';
  }

  // Stores the rows from the database
  $output_data = array();
  // Used to determine if the loop should exit. Is marked as 'true' when the queries are done.
  $done = false;
  // Repeat these steps if only 1 thing was found
  do {

    // Set up the variables with the 'year'
    $select = 'make'; // The next one that should be found. Used in the query.

    // Append to the variables if 'make' is found
    if (isset($input[$make])) {
      $select = 'model';
    }
    // Append if 'model' is found
    if (isset($input[$model])) {
      $select = "CONCAT(side, ' Diff - ', drivetype)";
    }

    // Append if 'drivetype' is found
    if(isset($input[$drivetype])) {
      $select = 'category';
    }

    $query_where = get_query_where($input);

    // Query up the rows
    $query = "SELECT DISTINCT " . $select . " FROM randys_advancedsearch WHERE " . $query_where . " ORDER BY " . $select . " desc";
    $results = $wpdb->get_results($query); // WPCS: unprepared SQL OK

    // It shouldn't be possible to get 0 entries, unless by front-end glitch or tampering
    if (count($results) === 0) {
      // 400 Bad Request
      return "Invalid Information";

    // One result is found, add it to the query and go again
    } elseif (count($results) === 1) {

      // We shouldn't repeat if we have a single drivetype, just add this to the data and call it good
      if ($select === "CONCAT(side, ' Diff - ', drivetype)") {
        $input[$drivetype] = $results[0]->$select;
        if( !isset($_GET['source']) ) {
          $output_data[] = $results[0]->$select;
          $done = true;
        }

      } elseif ($select === "category") {
        $input['cat-' . $select] = $results[0]->$select;
        $output_data[] = $results[0]->$select;
        $done = true;

      // Append to the request, don't mark as done and repeat the query process.
      } else {
        if( !isset($_GET['source']) ) {
          $input[$select] = $results[0]->$select;
        } else {
          $input['cat-' . $select] = $results[0]->$select;
        }
      }

    // More than 1 row was found, pack up the info and send to the front end for population
    } else {
      foreach ($results as $result) {
        $output_data[] = $result->$select;
      }
      $done = true;
    }
  } while ($done === false);

  // If we are dealing with the 'drivetype' set the 'select' to drivetype
  if ($select === "CONCAT(side, ' Diff - ', drivetype)") {
    $select = 'drivetype';
  }

  // Return the data
  return array('data' => $output_data, 'request' => $input, 'dropdown' => $select);
}

// Get a list of differentials based on search parameters
function get_diffs($input) {
  global $wpdb;

  // Put together the fields we want to get from the differentials to compare them later on
  $query_select = "a.diffid, a.diffname, a.diffdescription, a.diffimage, d.FullImage, a." . implode(', a.', filter::FILTER_ARRAY);

  // Base query for pulling up drivetypes
  $query = "SELECT " . $query_select . " FROM randys_advancedsearch a JOIN randys_differential d ON d.DifferentialID = a.DiffID WHERE ";

  // Set the 'WHERE' portion of the query
  $query .= get_query_where($input);

  // We want only the differential types
  $query .= " GROUP BY diffid";

  // Query up the rows
  $results = $wpdb->get_results($query); // WPCS: unprepared SQL OK

  // Return the differential that matches the queried information
  return $results;
}

// Get the secondary dropdowns for different filters
function get_diff_filters($differentials) {

  // Stores all the found filters as 'filter' => array('value1', 'value2', 'value3')
  $found_filters = array();

  // Loop through each of the possible filters
  foreach (filter::FILTER_ARRAY as $key => $filter) {

    // We will keep an array of all found values
    $found_values = array();

    // Go through each of the differentials to add up the values
    foreach ($differentials as $diff) {

      // If the value isn't present
      if (!in_array($diff->$filter, $found_values) && ($diff->$filter !== '') && ($diff->$filter !== null)) {
        $found_values[] = $diff->$filter;
      }
    }

    // If we found more than one value, add this to the filters
    if (count($found_values) >= 2) {
      if( $key === 'Pinion Support') {
        $found_values = array(0 => 'No', 1 => 'Yes');
      }

      $found_filters[] = array(
        'label' => $key,
        'value' => $filter,
        'data'  => $found_values,
      );
    }

  }
  return $found_filters;
}

// Get the query from the input
function get_query_where($input) {

  $nonce_verified = false;
  if( check_ajax_referer( 'browsing_ajax_nonce', 'nonce', false ) ) {
    $nonce_verified = true;
  }

  global $wpdb;

  if( $nonce_verified &&
      isset($_GET['source']) &&
      $_GET['source'] === 'product-browsing') {
    $year = 'cat-year';
    $make = 'cat-make';
    $model = 'cat-model';
    $drivetype = 'cat-drivetype';
  } else {
    $year = 'year';
    $make = 'make';
    $model = 'model';
    $drivetype = 'drivetype';
  }

  // Set up the variables with the 'year'
  $query_data = array($input[$year], $input[$year]); // Data to be formatted into the query
  $query_where = "startyear <= %d AND endyear >= %d"; // The 'WHERE ...' part of the query

  if(isset($input['parent-id']) && $input['parent-id'] !== 'undefined') {
    $query_data[] = $input['parent-id'];
    $query_where .= " AND ParentID = %d";
  }

  // Append to the variables if 'make' is found
  if (isset($input[$make])) {
    $query_data[] = $input[$make];
    $query_where .= " AND make = %s";
  }

  // Append to the variables if 'model' is found
  if (isset($input[$model])) {
    $query_data[] = $input[$model];
    $query_where .= " AND model = %s";
  }

  // Append to the variables if 'drivetype' is found
  if (isset($input[$drivetype]) && $input[$drivetype] !== 'null') {

    // drivetype is shown on the form as 'Front Diff - 4WD'.
    // The data is stored as
    //    drivetype => '4WD'
    //    side      => 'Front'
    // Let's break out drivetype into two separate inputs

    // Split current drivetype input
    $split_drivetype = explode(" Diff - ", $input[$drivetype]);

    $query_data[] = $split_drivetype[1];
    $query_where .= " AND drivetype = %s";

    $query_data[] = $split_drivetype[0];
    $query_where .= " AND side = %s";
  }

  if (isset($input['cat-category'])) {
    $query_data[] = $input['cat-category'];
    $query_where .= " And category = %s";
  }

  // Apply all filters if they are found
  foreach (filter::FILTER_ARRAY as $filter) {
    // Append to the variables if '$filter' is found
    if (isset($input[$filter])) {
      $query_data[] = $input[$filter];
      $query_where .= " AND a." . $filter . " = %s";
    }
  }

  // Return the 'WHERE' portion of the query
  return $wpdb->prepare($query_where, $query_data); // WPCS: unprepared SQL OK
}
