<div class="section section--tan tiles-section">
  <div class="container">
    <?php
    if ( have_rows('tiles') ) { ?>
      <div class="row">
        <?php while ( have_rows('tiles') ) { the_row();
          $image = get_sub_field('image')['sizes']['tile'];
          $title = get_sub_field('title');
          $content = get_sub_field('content');
          $cta_text = '' !== get_sub_field('cta_text') ? get_sub_field('cta_text') : null;
          $cta_link_type = get_sub_field('cta_link');
          $cta_link = null;
          if ( 'internal_link' === $cta_link_type[0]['acf_fc_layout'] ) {
            $cta_link = $cta_link_type[0]['page'];
          } elseif ( 'external_link' === $cta_link_type[0]['acf_fc_layout'] ) {
            $cta_link = $cta_link_type[0]['url'];
          }
          if ( true === get_sub_field('full_width') ) { ?>
            <div class="col-sm-12">
          <?php } else { ?>
            <div class="col-lg-6">
          <?php }
            echo get_tile('full', 'dark', 'left', $image, $title, $content, null, null, $cta_link, $cta_text); ?>
          </div>
        <?php } ?>
      </div>
    <?php } ?>
  </div>
</div>
