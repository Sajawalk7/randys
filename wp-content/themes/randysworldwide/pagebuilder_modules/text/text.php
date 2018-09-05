<?php
$title = get_sub_field('title');
$text = get_sub_field('text_content');
$image = get_sub_field('image')['sizes']['large'];
$size = $image ? 'col-md-6' : 'col-sm-10 offset-sm-1';
$center_align = true === get_sub_field('center_align_text') ? 'center-align' : '';
$color = 'tan' === get_sub_field('background_color') ? 'section--tan' : '';
$deep_link = get_sub_field('deep_link');
$anchor = $deep_link ? '<a id="'.$deep_link.'" class="anchor"></a>' : '';
?>

<?= $anchor ?>
<div class="section <?= $color ?> <?= $center_align ?> text-section">
  <div class="container">
    <?php if ( $title && 'col-md-6' !== $size ) { ?>
      <div class="row">
        <div class="col-sm-12">
          <h2><?= $title ?></h2>
        </div>
      </div>
    <?php } ?>
    <div class="row">
      <?php if ( 'col-md-6' === $size ) { ?>
        <div class="<?= $size ?> align-self-center">
          <img src="<?= $image ?>" class="img-fluid m-b-1" alt="image">
        </div>
      <?php } ?>
      <div class="<?= $size ?> <?= $center_align ?> align-self-center">
        <?php if ( $title && 'col-md-6' === $size ) { ?>
          <h2><?= $title ?></h2>
        <?php } ?>
        <?= $text ?>
      </div>
    </div>
  </div>
</div>
