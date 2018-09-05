<?php

// Conditional logic to redirect users to the correct pages
function randys_redirector() {

  global $wpdb;

  // Get the current url
  $current_url = $_SERVER["REQUEST_URI"];

  // We only want to redirect people who are on the page /redirect
  if (strpos($current_url, '/redirect') === 0) {

    // The headers should not be cached for this URL as it will vary between 301 to 404 based on GET parameters
    nocache_headers();

    // Get the get parameters and lowercase them
    $get_parameters = array_change_key_case($_GET);

    // Now we want to redirect depending on the parameter

    // Makes
    if (isset($get_parameters['makeid']) && !empty($get_parameters['makeid'])) {

      // Makes are determined by this static list
      $makes = array(
        '35' => '/dodge-drivetrain-differential/',
        '12' => '/toyota-drivetrain-differential/',
        '7' => '/jeep-drivetrain-differential/',
        '3' => '/chevrolet-drivetrain-differential/',
        '2' => '/ford-drivetrain-differential/',
        '1' => '/dodge-drivetrain-differential/',
      );

      // If the make was found in our list, then redirect to it
      if (array_key_exists($get_parameters['makeid'], $makes)) {
        wp_redirect($makes[$get_parameters['makeid']], 301);
        exit;
        return;
      }
    }

    // Products
    if (isset($get_parameters['prodid']) && !empty($get_parameters['prodid'])) {

      // Try to find the product from the randys product id
      $post_id = $wpdb->get_var(
        $wpdb->prepare(
          "SELECT post_id FROM wp_postmeta WHERE meta_key = '_randy_productid' AND meta_value = %s",
          array($get_parameters['prodid'])
        )
      );

      // If the post (product) was found, then redirect to it
      if ($post_id) {
        wp_redirect(get_post_permalink($post_id), 301);
        exit;
        return;
      }
    }

    // Differentials
    if (isset($get_parameters['diffid']) && !empty($get_parameters['diffid'])) {

      // Try to find the differential from the randys diff id
      $post_id = $wpdb->get_var(
        $wpdb->prepare(
          "SELECT MAX(post_id) FROM wp_postmeta WHERE meta_key = '_randy_diffid' AND meta_value = %s",
          array($get_parameters['diffid'])
        )
      );

      // If the post (differential) was found, then redirect to it
      if ($post_id) {
        wp_redirect(get_permalink($post_id), 301);
        exit;
        return;
      }
    }

    // Categories (Parent and Children)
    if (isset($get_parameters['parentid']) && !empty($get_parameters['parentid']) ||
              isset($get_parameters['catid'])    && !empty($get_parameters['catid'])) {

      // Get the ID that was set (Parent has priority if both are set)
      $cat_id = (isset($get_parameters['parentid']) && !empty($get_parameters['parentid'])) ? $get_parameters['parentid'] : $get_parameters['catid'];

      // Get the parent ID of this category
      $parent_id = $wpdb->get_var(
        $wpdb->prepare(
          "SELECT ParentID FROM randys_category WHERE CategoryID = %d",
          array((int)$cat_id)
        )
      );

      // Set the category ID as the parent if it had a parent (0 is used for "No Parent")
      $cat_id = $parent_id && $parent_id > 0 ? $parent_id : $cat_id;

      // Try to find the category from the randys parent id
      $term_id = $wpdb->get_var(
        $wpdb->prepare(
          "SELECT term_id FROM wp_termmeta WHERE meta_key = '_randys_category_id' AND meta_value = %s",
          array($cat_id)
        )
      );

      // If the category was found, then redirect to it
      if ($term_id) {
        wp_redirect(get_term_link((int)$term_id), 301);
        exit;
        return;
      }
    }

    // If none were found (or failed to find their page) this should result as a 404
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
  }
}
add_action( 'template_redirect', 'randys_redirector' );
