<?php
/**
 * The Template for displaying products in a product category. Simply includes the archive template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/taxonomy-product_cat.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see       https://docs.woocommerce.com/document/template-structure/
 * @package   WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

global $wpdb;

// Get the Category ID
$term_id = get_queried_object()->term_id;
$post_id = 'product_cat_'.$term_id;

// Setup hero vars
$image = get_field('hero_background_image', $post_id)['sizes']['hero'];
$icon = get_field('hero_icon', $post_id);
$title = get_field('hero_title', $post_id);
$fallback_title = get_cat_name($term_id);
$page_title = $title ? $title : $fallback_title;

// Get background-color for browsing form
if( isset( $_GET['cat-category']) &&
    $_GET['cat-make'] === '' &&
    !isset($_GET['diffid']) ) {
  $bg_color = 'section--transparent';
} else {
  $bg_color = 'section--tan';
}

// Get Parent ID from randys_category table
$parentID = $wpdb->get_row(
  $wpdb->prepare(
    "SELECT DISTINCT CategoryID FROM randys_category WHERE CategoryName = %s AND ParentID = %s",
    array(get_queried_object()->name, 0)
  )
);
if( $parentID !== null ) {
  echo '<input id="parent-name" type="hidden" value=" ' . $parentID->CategoryID . '" name="parent-id" class="parent-id" />';
}
?>
<div class="hero" style="background-image: url('<?= $image ?>');">
  <?php if ( 'none' != $icon ): ?>
    <img src="<?= get_template_directory_uri(); ?>/dist/images/<?= $icon ?>_circle.svg" class="hero__icon" alt="<?= $page_title ?>">
  <?php endif; ?>
  <h1> <?= $page_title ?></h1>
</div>
<?php if(get_field('disable_product_filter', $post_id) === false ): ?>
<div class="section product-browsing-form-section <?php echo $bg_color; ?>">
  <div class="container">
    <?php get_template_part('templates/product-browsing-form'); ?>
  </div>
</div>
<?php endif; ?>

<?php
  // If we don't have a diff id selected yet, output diffs and filters
if( isset($_GET['cat-make']) && $_GET['cat-make'] !== '' && !isset($_GET['diffid']) ) {

    get_template_part('templates/browsing-diffwizard-shared/filtering-section');

  // We either have the diffid or the single cat id lets add the results
} elseif( isset( $_GET['cat-category']) || isset($_GET['diffid']) ) {

  get_template_part('templates/browsing-diffwizard-shared/results-section');


/*
** Product category Landing Page
** If product browsing form has not been filled out
** display the category infomation
*/
} else {
  $queried_object = get_queried_object();
  $taxonomy = $queried_object->taxonomy;
  $term_id = $queried_object->term_id;
  $has_filter = get_field('disable_product_filter', $post_id) ? '' : ' page-builder-warp--has-filter';
  echo '<div class="page-builder-warp' . $has_filter . '">';
  if ( class_exists('acf') ) {
    // Pagebuilder
    if ( have_rows('modules', $taxonomy . '_' . $term_id) ) {
      while ( have_rows('modules', $taxonomy . '_' . $term_id) ) { the_row();
        get_template_part('pagebuilder_modules/'.get_row_layout().'/'.get_row_layout());
      }
    } else {
      // no layouts found
      echo '<div class="section section--tan"><div class="m-b-3 m-t-2"><div class="container">No Modules Selected.</div></div></div>';
    }
  }
  echo '</div>'; // <div class="page-builder-warp">
}

// Show results if we don't a filter bar
if( get_field('disable_product_filter', $post_id) === true ) {
  get_template_part('templates/browsing-diffwizard-shared/results-section');
}

