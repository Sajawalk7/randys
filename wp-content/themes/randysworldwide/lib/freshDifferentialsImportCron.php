<?php
// If we're not already scheduled, let's go ahead and schedule one.
if ( ! wp_next_scheduled( 'fresh_import_differentials_hook' ) ) {
  wp_schedule_event( time(), 'every-15-minutes', 'fresh_import_differentials_hook' );
}

// Function that imports differentials from Randys
add_action( 'fresh_import_differentials_hook', __NAMESPACE__ . '\\fresh_import_differentials' );
function fresh_import_differentials() {

  // This may take longer than 30 seconds.
  // To avoid the fatal timeout error, set the time limit for this cron to 15 minutes.
  set_time_limit(60 * 15);

  // Import wpdb so we can use it
  global $wpdb;

  $wpdb->query('START TRANSACTION');

  $wpdb->query(
    "DELETE wp_posts, wp_postmeta FROM wp_posts
    LEFT JOIN wp_postmeta ON wp_postmeta.post_id = wp_posts.ID
    WHERE post_type = 'differentials'"
  );

  // Let's get all the required data from the randys_differential table.
  $results = $wpdb->get_results(
    "SELECT randys_differential.DifferentialID   AS diffid,
                 randys_differential.Name             AS name,
                 randys_differential.Description      AS description,
                 randys_differential.FullImage        AS image,
                 randys_differential.RingGearDiameter AS ringgeardiameter,
                 randys_differential.CoverBolts       AS coverbolts,
                 randys_differential.RingGearBolts    AS ringgearbolts,
                 randys_differential.SplineCount      AS splinecount,
                 randys_differential.Dropout          AS dropout,
                 randys_differential.PinionSupport    AS pinionsupport,
                 randys_differential.CarrierBreaks    AS carrierbreaks,
                 randys_differential.RearSuspension   AS rearsuspension,
                 randys_differential.PinionNutSize    AS pinionnutsize

    FROM randys_differential

    GROUP BY diffid"
  );

  foreach( $results as $diff ) {

    //Check if diff exists
    $existing_diff = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT ID,post_name
          FROM wp_posts
            WHERE ID=(
                SELECT post_id
                  FROM wp_postmeta
                    WHERE meta_key='_randy_diffid'
                      AND meta_value= %d
                    LIMIT 1
            )",
        $diff->diffid
      )
    );

    $attributes = array(
      "_randy_diffid"     => $diff->diffid,
      "_diffimage"        => $diff->image,
      "_ringgeardiameter" => $diff->ringgeardiameter,
      "_coverbolts"       => $diff->coverbolts,
      "_ringgearbolts"    => $diff->ringgearbolts,
      "_splinecount"      => $diff->splinecount,
      "_dropout"          => $diff->dropout,
      "_pinionsupport"    => $diff->pinionsupport,
      "_carrierbreaks"    => $diff->carrierbreaks,
      "_rearsuspension"   => $diff->rearsuspension,
      "_pinionnutsize"    => $diff->pinionnutsize,
    );

    if (count($existing_diff) > 0) {

       // Update differential at target database.
        $update_values = array($diff->name, $diff->description, sanitize_title($diff->name));
        $sql_raw = "UPDATE wp_posts
             SET post_title=%s,
               post_content=%s,
               post_name=%s
             WHERE ID=" . $existing_diff[0]->ID;

        // Prepare & run the sql
        $wpdb->query($wpdb->prepare($sql_raw, $update_values)); // WPCS: unprepared SQL OK

        // Get the ID from the existing differential
        $post_id = $existing_diff[0]->ID;


    } else {

        // Run the insert query
        $wpdb->query(
          $wpdb->prepare(
            "INSERT INTO wp_posts
              SET post_name=%s,
                  post_title=%s,
                  post_content=%s,
                  post_type=%s,
                  post_excerpt='',
                  to_ping='',
                  pinged='',
                  post_content_filtered=''",
            array(
              sanitize_title($diff->name),
              $diff->name,
              $diff->description,
              'differentials'
            )
          )
        );

        // Get the post ID, this will be used later
        $post_id = $wpdb->insert_id;
    }

    // Now we insert the attributes
    foreach ($attributes as $k => $v) {

      // Check to see if we have the attribute that we're about to insert/update
      $post_meta_results = $wpdb->get_results($wpdb->prepare(
        "SELECT meta_id FROM wp_postmeta
        WHERE post_id= %d AND meta_key=%s LIMIT 1",
        array($post_id, $k)
      ));

      // If we have the attribute, UPDATE it, if not, then INSERT it
      if (count($post_meta_results) > 0) {
        $post_meta_id = $post_meta_results[0]->meta_id;
        $wpdb->query(
          $wpdb->prepare("UPDATE wp_postmeta
            SET meta_value='%s'
            WHERE meta_id= %d", array($v, $post_meta_id)
          )
        );
      }else{
        $wpdb->query(
          $wpdb->prepare(
            "INSERT INTO wp_postmeta
            SET post_id = %d,
              meta_key='%s',
              meta_value='%s'", array($post_id, $k, $v)
          )
        );
      }

    }

    if($results) {
      $wpdb->query('COMMIT');
    }
    else {
      $wpdb->query('ROLLBACK');
    }
  }
}
