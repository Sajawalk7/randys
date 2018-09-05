<?php
  $title = get_the_title();

  $diff_id = get_post_meta(get_the_ID(), '_randy_diffid', true);
  $diff_image_file = get_post_meta(get_the_ID(), '_diffimage', true);

  // Path defined in /lib/extras.php
  global $diff_image_path;

  // build out image path and filename
  $diff_image = $diff_image_path . $diff_image_file;


    // Check if file exits in directory
  if(file_exists($_SERVER['DOCUMENT_ROOT'] . $diff_image)) {
    $diff_image_src = $diff_image;
  } else {
    $diff_image_src = '/wp-content/themes/randysworldwide/dist/images/randys_product_default.png';
  }

?>
<div class="container">
  <section class="diff section section--sm section--divider">
    <div class="row m-t-3 m-b-3 align-items-center">
      <div class="diff__image m-b-2">
        <img src="<?php echo $diff_image_src; ?>" alt="<?php echo $title; ?>">
      </div>
      <div class="diff__summary">
        <h1 itemprop="name" class="diff__title"><?php echo $title; ?></h1>
        <div class="diff__description"><?php echo $post->post_content; ?></div>
        <a href="/diff-wizard/?diffid=<?php echo $diff_id; ?>" class="button m-t-2">View Part List</a>
      </div><!-- .summary -->
    </div>
  </section>

  <?php
    // Specs
    $ring_gear_diameter = get_post_meta(get_the_ID(), '_ringgeardiameter', true);
    $cover_bolts = get_post_meta(get_the_ID(), '_coverbolts', true);
    $ring_gear_bolts = get_post_meta(get_the_ID(), '_ringgearbolts', true);
    $spline_count = get_post_meta(get_the_ID(), '_splinecount', true);
    $dropout = get_post_meta(get_the_ID(), '_dropout', true);
    $pinion_support = get_post_meta(get_the_ID(), '_pinionsupport', true);
    $carrier_breaks = get_post_meta(get_the_ID(), '_carrierbreaks', true);
    $rear_suspension = get_post_meta(get_the_ID(), '_rearsuspension', true);
    $pinion_nut_size = get_post_meta(get_the_ID(), '_pinionnutsize', true);

    // update dropout boolean to output text
    if( $dropout === '0' ) {
      $dropout = 'False';
    } elseif( $dropout === '1' ) {
      $dropout = 'True';
    }

    // update dropout boolean to output text
    if( $pinion_support === '0' ) {
      $pinion_support = 'False';
    } elseif( $pinion_support === '1') {
      $pinion_support = 'True';
    }

    // Place key and value in array for later use
    $specs_array = [
      'Ring Gear Diameter' => $ring_gear_diameter,
      'Cover Bolts' => $cover_bolts,
      'Ring Gear Bolts' => $ring_gear_bolts,
      'Spline Count' => $spline_count,
      'Dropout' => $dropout,
      'Pinion Support' => $pinion_support,
      'Carrier Breaks' => $carrier_breaks,
      'Rear Suspension' => $rear_suspension,
      'Pinion Nut Size' => $pinion_nut_size,
    ];
  ?>
  <section class="section section--sm section--divider">
    <h2 class="diff-section-title m-b-3">Specs</h2>
    <ul class="list-two-col list-unstyled">
      <?php
        // Loop through each spec item and output in list
        foreach( $specs_array as $key => $spec ) {
          if( isset($spec) ) {
            echo '<li>' . $key . ': <span class="bold dark-gray">' . $spec . '</span></li>';
          }
        }
      ?>
    </ul>
  </section>

  <?php
    $compatible_model = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT MakeID, ModelID, CONCAT(Model, ' (', StartYear, ' - ', EndYear, ')') AS Name
          FROM randys_advancedsearch
            WHERE DiffID = %d
          GROUP BY ModelID, Model
          ORDER BY Name",
        array( $diff_id )
      )
    );
    if ($compatible_model) {
      ?>
      <section class="section section--sm">
        <h2 class="diff-section-title m-b-3">Compatible with Models</h2>
        <ul class="list-two-col list-unstyled">
          <?php foreach ($compatible_model as $model) { ?>
          <li><?php echo $model->Name; ?></li>
          <?php } ?>
        </ul>
      </section>
      <?php
    }
  ?>
</div>
