<?php
  //FIXME: ACF doesn't handle Archive pages the same as
  // regular pages. Need to see how we can call in ACF to
  // the resource center archive

  // Column One ACF fields
  $cta_m_one_primary_image_url = get_field('cta_module_one_image', $page_id)['sizes']['large'];
  $cta_m_one_primary_image_position = get_field('cta_module_one_image_position', $page_id);
  $cta_m_one_title = get_field('cta_module_one_title', $page_id);
  $cta_m_one_description = get_field('cta_module_one_description', $page_id);
  $cta_m_one_button_link = get_field('cta_module_one_button_link', $page_id);
  $cta_m_one_button_label = get_field('cta_module_one_button_label', $page_id);

  // Column Two ACF Fields
  $cta_m_two_primary_image_url = get_field('cta_module_two_image', $page_id)['sizes']['large'];
  $cta_m_two_primary_image_position = get_field('cta_module_two_image_position', $page_id);
  $cta_m_two_title = get_field('cta_module_two_title', $page_id);
  $cta_m_two_description = get_field('cta_module_two_description', $page_id);
  $cta_m_two_button_link = get_field('cta_module_two_button_link', $page_id);
  $cta_m_two_button_label = get_field('cta_module_two_button_label', $page_id);
?>
<div class="section section--tan">
  <div class="container">
    <div class="row">
      <div class="col-xs-12 col-lg">
        <?php echo get_tile('full', 'dark', $cta_m_one_primary_image_position, $cta_m_one_primary_image_url, $cta_m_one_title, $cta_m_one_description, null, null, $cta_m_one_button_link, $cta_m_one_button_label); ?>
      </div>
      <div class="col-xs-12 col-lg">
        <?php echo get_tile('full', 'dark', $cta_m_two_primary_image_position, $cta_m_two_primary_image_url, $cta_m_two_title, $cta_m_two_description, null, null, $cta_m_two_button_link, $cta_m_two_button_label); ?>
      </div>
    </div>
  </div>
</div>
