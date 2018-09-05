<?php
  $image_url = get_field('location_contact_image', 'options')['sizes']['large'];
  $column1 = get_field('column_1', 'options');
  $column2 = get_field('column_2', 'options');
  $column3 = get_field('column_3', 'options');
  $deep_link = get_sub_field('deep_link');
  $anchor = $deep_link ? '<a id="'.$deep_link.'" class="anchor"></a>' : '';
?>

<?= $anchor ?>
<div class="section section--tan locations-contacts">
  <div class="container">
    <?php if( $image_url ): ?>
      <img src="<?php echo $image_url; ?>" alt="Locations Map" class="center-align img-fluid m-b-3">
    <?php endif; // $image_url ?>
    <div class="row">
      <div class="col-md-4">
        <?= $column1 ?>
      </div>
      <div class="col-md-4">
        <?= $column2 ?>
      </div>
      <div class="col-md-4">
        <?= $column3 ?>
      </div>
    </div>
    <h3 class="m-t-3">Have a question?</h3>
    <?php
      // Add the 'Contact'' form Gravity Form to the page (ID: 2)
      echo do_shortcode('[gravityform id="2" title="false" description="false" ajax="true"]');
    ?>
  </div>
</div>
