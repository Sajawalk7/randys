<?php
use Roots\Sage\Titles;

$image = get_sub_field('background_image')['sizes']['hero'];
$icon = get_sub_field('icon');
$title = get_sub_field('title');
$page_title = $title ? $title : Titles\title();
$icon_image = $icon.'_circle';
$texture = '';
$color = '';
if ( 'yukon' === $icon || 'usa_standard' === $icon ) {
  $icon_image = $icon.'_logo_white';
  $texture = 'hero--texture-bg';
}
if ( 'yukon' === $icon ) {
  $color = 'hero--purple';
} elseif ( 'usa_standard' === $icon ) {
  $color = 'hero--rust';
}
?>
<div class="hero <?= $texture ?> <?= $color ?>" style="background-image: url('<?= $image ?>');">
  <?php if ( 'none' != $icon ) { ?>
    <img src="<?= get_template_directory_uri(); ?>/dist/images/<?= $icon_image ?>.svg" class="hero__icon" alt="<?= $page_title ?>">
  <?php } ?>
  <h1> <?= $page_title ?></h1>
</div>
