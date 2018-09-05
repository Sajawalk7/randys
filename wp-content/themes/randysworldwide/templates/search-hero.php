<?php
  $search_count_output = '';
  $form_action = '';

  if( is_search() ) {
    global $wp_query;
    $search_count = $wp_query->found_posts;
    $background_image = get_field('search_page_hero', 'option')['sizes']['hero'];
    $title = 'Search Results';
    $search_count_output = '(' . $search_count . ')';
  } elseif( is_post_type_archive( 'resource-center' ) ) {
    $background_image = get_field('background_image', 10)['sizes']['hero'];
    $title = get_field('title', 10);
    $form_action = 'action="#resource-center"';
  }
?>
<div class="hero hero--search" style="background-image: url('<?php echo $background_image; ?>');">
    <div class="container">
    <h1 class="section-title"><?php echo $title; ?> <?php echo $search_count_output; ?></h1>
    <div class="resource-category-search__reset">Reset</div>
    <form <?php echo $form_action; ?> role="search" method="get" class="resource-category-search" action="<?php echo home_url( '/' ); ?>">
      <div class="input-group">
        <input type="search" class="form-control"
        placeholder="<?php echo esc_attr_x( 'Search', 'placeholder' ) ?>"
        value="<?php echo get_search_query() ?>" name="s"
        title="<?php echo esc_attr_x( 'Search for:', 'label' ) ?>" />
        <input type="hidden" name="sortby" value="yukon" />
        <span class="input-group-btn">
          <button aria-label="Submit" class="button button--slim button--icon m-l-1" type="submit"><i class="fa fa-search" aria-hidden="true"></i></button>
        </span>
      </div>
    </form>
  </div>
</div>
