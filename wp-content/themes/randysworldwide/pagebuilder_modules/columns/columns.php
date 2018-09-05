<?php
$column_width = 1 === count(get_sub_field('columns')) ? 'col-sm-7 column--single' : 'col-md-4';
?>

<div class="section columns">
  <div class="container">
    <?php if ( have_rows('columns') ) { ?>
      <div class="row">
        <?php while ( have_rows('columns') ) { the_row();
          $image = get_sub_field('image')['sizes']['large'];
          $content = get_sub_field('content');
          $link = '';
          $link_target = '';
          if ( have_rows('link') ) {
            while ( have_rows('link') ) { the_row();
              $layout = get_row_layout();
              $link .= 'internal_link' === $layout ? get_sub_field('page') : get_sub_field('url');
              $link_target = 'external_link' === $layout ? '_blank' : '';
            }
          }
          ?>
          <div class="<?= $column_width ?> center-align column m-b-2">
            <?php if ( $link ) { ?>
              <a href="<?= $link ?>" target="<?= $link_target ?>">
            <?php }
            if ( $image ) { ?>
              <div style="background-image: url(<?= $image ?>);" class="column__image m-b-2"></div>
            <?php }
            if ( $link ) { ?>
              </a>
            <?php } ?>
            <?= $content ?>
          </div>
        <?php } ?>
      </div>
    <?php } ?>
  </div>
</div>
