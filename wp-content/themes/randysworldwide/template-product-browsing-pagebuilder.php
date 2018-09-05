<?php
/**
 * Template Name: Product Browsing Pagebuilder
 */
use Roots\Sage\Titles;

$image = get_field('background_image')['sizes']['hero'];
$title = get_field('title');
$icon = get_field('icon');
$page_title = $title ? $title : Titles\title();
$icon_image = $icon.'_circle';
?>
<div class="hero" style="background-image: url('<?= $image ?>');">
  <?php if( $icon !== 'none' ): ?>
    <img src="<?= get_template_directory_uri(); ?>/dist/images/<?= $icon_image ?>.svg" class="hero__icon" alt="<?= $page_title ?>">
  <?php endif; ?>
  <h1> <?= $page_title ?></h1>
</div>
<section class="section section--tan product-browsing-form-section">
  <div class="container">
    <?php get_template_part('templates/product-browsing-form'); ?>
  </div>
</section>
<?php
/**
*
* Filter Result View
*
*/
if( isset($_GET['cat-make']) && $_GET['cat-make'] !== '' && !isset($_GET['diffid']) ) {

  get_template_part('templates/browsing-diffwizard-shared/filtering-section');

} elseif( isset( $_GET['cat-category']) || isset($_GET['diffid']) ) {
  /**
  *
  * Diff Results view
  *
  */
  get_template_part('templates/browsing-diffwizard-shared/results-section');

} elseif ( class_exists('acf') ) {
  echo '<div class="page-builder-warp page-builder-warp--has-filter">';
  // Pagebuilder
  if ( have_rows('modules') ) {
    while ( have_rows('modules') ) { the_row();
      get_template_part('pagebuilder_modules/'.get_row_layout().'/'.get_row_layout());
    }
  } else {
    // no layouts found
    echo '<div class="section section--tan"><div class="m-b-3 m-t-2"><div class="container">No Modules Selected.</div></div></div>';
  }
  echo '</div>';
}
