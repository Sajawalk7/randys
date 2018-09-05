<?php
$title = get_sub_field('title');
$intro = get_sub_field('intro');
$deep_link = get_sub_field('deep_link');
$anchor = $deep_link ? '<a id="'.$deep_link.'" class="anchor"></a>' : '';
?>

<?= $anchor ?>
<div class="section timeline">
  <div class="timeline__overlap">
    <?php if ( $title || $intro ) { ?>
      <div class="container">
        <div class="row">
          <div class="col-sm-10 center-align m-b-3">
            <?php if ( $title ) { echo '<h2>' . $title . '</h2>'; } ?>
            <?= $intro ?>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if ( have_rows('dates') ) { ?>
      <div class="timeline-slider">
        <?php while ( have_rows('dates') ) { the_row();
          $image = get_sub_field('image')['sizes']['large'];
          $year = get_sub_field('year');
          $type = get_sub_field('type');
          $content = get_sub_field('content');
          ?>
          <div class="timeline-slider__slide">
            <div style="background-image: url(<?= $image ?>)" class="slide__background m-b-2"></div>
            <div class="p-l-2 p-r-2">
              <div class="date"><?= $year ?></div>
              <?php if( $type ): ?>
                <h4 class="type"><?= $type ?></h4>
              <?php endif; ?>
              <?= $content ?>
            </div>
          </div>
        <?php } ?>
      </div>
    <?php } ?>
  </div>
</div>
