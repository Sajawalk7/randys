<?php
  /*
  /* Template is used for search, products archive, Resource center archive
  /* Need to check which template we are looking at and display proper content
  **/
  $title = '';
  $count_title = '';
  $results_count = 0;
  $sort_select_selector = 'orderby';

  // If we have a query count update $results_count
  if( isset($results) ) {
    $results_count = $results[1];
  }

  if( is_search() ) {
    $title = 'Search Results';
    $count_title = 'Parts Found';
    $sort_select_selector = 'orderby-search';
    global $wp_query;
    $results_count = $wp_query->found_posts;
  } elseif( is_page('diff-wizard') ) {
    $title = 'Part Results';
    $count_title = 'Parts Found';
  } elseif( is_post_type_archive( 'resource-center' ) ) {
    $title = 'Resource Results';
    $count_title = 'Resources Found';
  } elseif( is_product_category() || is_page_template('template-product-browsing-pagebuilder.php') ) {
    $term_id = get_queried_object()->term_id;
    $title = get_cat_name($term_id);
    $count_title = 'Parts Found';
  }
?>

<div id="archive-header" class="archive-header row justify-content-between align-items-center m-b-2">
  <div class="col-xs-12 col-sm-12 col-md m-b-2">
    <div class="archive-header__section-title"><?php echo $title; ?></div>
    <div class="archive-header__count sm"><span><?php echo $results_count; ?></span> <?php echo $count_title; ?></div>
  </div>
  <div class="archive-header__sort col-xs-12 col-md-5 col-lg-4 m-b-2">
    <?php
      // if we are on Resource center use Facet sort
      if( is_post_type_archive('resource-center') ) {
        echo do_shortcode( '[facetwp sort="true"]' );
      } else { ?>
      <div class="row align-items-center">
        <div class="col-xs-2 col-md-3 sm">sort by</div>
        <div class="col-xs-10 col-md-9">
          <form>
            <span class="select">
              <select id="<?php echo $sort_select_selector; ?>" name="orderby" class="<?php echo $sort_select_selector; ?>">
                <option value="yukon" selected>Yukon Parts</option>
                <option value="price">Price</option>
                <option value="sku">Part Number</option>
              </select>
            </span>
            <input type="hidden" name="sort_nonce" id="sort_nonce" value="<?php echo wp_create_nonce( 'sort_nonce' ); ?>" />
          </form>
        </div>
      </div>
      <?php }
    ?>
  </div>
</div>
