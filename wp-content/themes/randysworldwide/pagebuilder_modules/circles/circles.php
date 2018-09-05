<?php
$title = get_sub_field('title');
$intro = get_sub_field('intro');
$deep_link = get_sub_field('deep_link');
$anchor = $deep_link ? '<a id="'.$deep_link.'" class="anchor"></a>' : '';
?>

<?= $anchor ?>
<div class="section section--tan circles">
  <div class="container">
    <div class="row">
      <div class="col-sm-10 center-align m-b-3">
        <h2><?= $title ?></h2>
        <?= $intro ?>
      </div>
    </div>
    <div class="row">
      <?php
      $count = count(get_sub_field('circles'));
      $remainder = $count % 3;
      $i = 1;
      if ( have_rows('circles') ) {
        while ( have_rows('circles') ) { the_row();
          $circle_title = get_sub_field('title');
          $circle_content = get_sub_field('content');
          $circle_image = get_sub_field('image')['sizes']['medium'];
          $circle_link = '';
          $circle_link_target = '';
          if ( have_rows('link') ) {
            while ( have_rows('link') ) { the_row();
              $layout = get_row_layout();
              $circle_link .= 'internal_link' === $layout ? get_sub_field('page') : get_sub_field('url');
              $circle_link_target = 'external_link' === $layout ? '_blank' : '';
            }
          }
          $circle_margin = '';
          if ( 2 === $remainder ) {
            // Get current position
            $position = $count - $i;
            if ( 1 === $position ) { // if second to last
              $circle_margin = 'mr-md-0';
            } elseif ( 0 === $position ) { // if last
              $circle_margin = 'ml-md-0';
            }
          }
          ?>
          <div class="col-sm-6 col-md-4 center-align circle <?= $circle_margin ?>">
            <?php
            if ( $circle_link ) { ?>
              <a href="<?= $circle_link ?>" target="<?= $circle_link_target ?>">
            <?php } ?>
            <div class="circle__background" style="background-image: url(<?= $circle_image ?>);">
              <h3 class="vertical-align-center center-align"><?= $circle_title ?></h3>
            </div>
            <?php if ( $circle_link ) { ?>
              </a>
            <?php } ?>
            <div class="center-align m-t-2 m-b-3">
              <?= $circle_content ?>
            </div>
          </div>
          <?php
          $i++;
        }
      } ?>
    </div>
  </div>
</div>
