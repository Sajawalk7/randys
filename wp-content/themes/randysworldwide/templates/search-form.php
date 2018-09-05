<form role="search" method="get" class="searchform group" action="<?php echo home_url( '/' ); ?>">
  <label>
    <input type="search" class="search-field"
    placeholder="<?php echo esc_attr_x( 'Search Products', 'placeholder' ) ?>"
    value="<?php echo get_search_query() ?>" name="s"
    title="<?php echo esc_attr_x( 'Search for:', 'label' ) ?>" />
    <input type="hidden" name="sortby" value="yukon" />
    <button aria-label="Search" type="submit" class="search-link"></button>
  </label>
</form>
