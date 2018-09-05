<?php
  $page_id = 10; // ID of /resource-center/
?>

<?php get_template_part('templates/search-hero'); ?>

<div class="section section--tan">
  <div class="container">
    <h2 class="section-title center-align"><span class="text-circle">OR</span> Select a Category</h2>
    <div class="resource-center-category">
      <?php
        // Setup args
        $args = array(
          'taxonomy' => 'resource_categories',
          'orderby' => 'menu_order',
        );
        $resource_categories = get_categories( $args );
        echo '<div class="row justify-content-center">';
        echo '<div class="col-lg-3 hidden-lg-up"><div class="card card__mobile-active-item"><div class="card-block center-align"><span class="card-text">Select Category</span></span><i class="fa fa-chevron-down" aria-hidden="true"></i></div></div></div>';
        foreach( $resource_categories as $category ) {
          $cat_image = get_field('category_image', $category)['sizes']['medium'];
          echo '<div class="col-lg-3 card-column"><div class="card card-category-item" data-category-slug="' . $category->slug . '">';
          echo '<div class="card-block card-category-item__block">';
          echo '<div class="card-category-item__image-wrapper hidden-md-down">';
          echo '<img src="' . $cat_image . '" class="card-category-item__image img-fluid" />';
          echo '</div>';
          echo $category->name;
          echo '</div></div></div>';
        }
        echo '</div>';
      ?>
      <div class="center-align">
        <button class="resource-center-category__reset m-b-2 button button--slim button--sm-height">Reset</button>
      </div>
    </div>
  </div>
</div>

<div class="section">
  <div class="container">
    <div id="resource-center" class="resource-center">
      <?php get_template_part('templates/archive-header'); ?>
      <div class="resource-center__container m-t-3">
        <?php echo do_shortcode('[facetwp template="resource-center"]'); ?>
      </div>
      <div class="resource-center__loading-state"></div>
      <div class="resource-center__no-results center-align m-t-3">
        <h3>Sorry, but nothing matched your search terms. Please refine your search.</h3>
      </div>
    </div>
  </div>
</div>

<?php require_once('templates/sections/section-cta.php'); ?>


<div class="hide">
  <?php
    // Display default facets hidden on page
    echo facetwp_display( 'facet', 'resource_center_search');
    echo facetwp_display( 'facet', 'resource_center_categories' );
  ?>
</div>
