<?php
$title = get_sub_field('title');
$backgound = get_sub_field('background')['sizes']['large'];
$deep_link = get_sub_field('deep_link');
$anchor = $deep_link ? '<a id="'.$deep_link.'" class="anchor"></a>' : '';
?>

<?= $anchor ?>
<div class="section section--tan slider" style="background-image: url(<?= $backgound ?>);">
  <div class="slider__overlap">
    <?php if ( $title ) { echo '<h2 class="slider__title center-align m-b-2">' . $title . '</h2>'; } ?>
    <div class="container">
      <?php if ( have_rows('slides') ) { ?>
        <div class="pagebuilder-slider">
          <?php while ( have_rows('slides') ) { the_row();
            $content = get_sub_field('content');
            $text_size = true === get_sub_field('large_text') ? 'large-text' : '';
            ?>
            <div class="pagebuilder-slider__slide <?= $text_size ?>">
              <?= $content ?>
            </div>
          <?php } ?>
        </div>
      <?php } ?>
    </div>
  </div>
</div>
