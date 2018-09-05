<?php
  use Roots\Sage\Extras;
  
  global $wp_query;
  $search_count = $wp_query->found_posts;
?>
<?php get_template_part('templates/search-hero'); ?>
<div class="section">
  <div class="container">

  <?php get_template_part('templates/archive-header'); ?>

  <?php if (!have_posts()) : ?>
    <div class="alert alert-warning">
      <?php _e('Sorry, no results were found. Please refine your search', 'sage'); ?>
    </div>
  <?php endif; ?>

  <?php while (have_posts()) : the_post(); ?>
    <?php get_template_part('templates/content', 'search'); ?>
  <?php endwhile; ?>

  <div class="center-align m-t-2">
    <?php echo Extras\base_pagination(); ?>
  </div>

  </div>
</div>
