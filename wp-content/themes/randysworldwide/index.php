<?php
$image = wp_get_attachment_url( get_post_thumbnail_id(get_option( 'page_for_posts' )), 'large' );
?>

<div class="hero" style="background-image: url('<?= $image ?>');">
  <img src="<?= get_template_directory_uri(); ?>/dist/images/glossary_circle.svg" class="hero__icon" alt="Blog">
  <h1>Blog</h1>
</div>
<div class="container section blog-section">
  <?php if (!have_posts()) : ?>
    <div class="alert alert-warning">
      <?php _e('Sorry, no results were found.', 'sage'); ?>
    </div>
    <?php get_search_form(); ?>
  <?php endif; ?>

  <?php while (have_posts()) : the_post(); ?>
    <?php get_template_part('templates/content', get_post_type() != 'post' ? get_post_type() : get_post_format()); ?>
  <?php endwhile; ?>

  <?php the_posts_navigation(); ?>
</div>
