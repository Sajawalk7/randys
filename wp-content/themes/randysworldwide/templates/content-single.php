<?php
$image = get_the_post_thumbnail_url('', 'large');
?>

<?php if ( $image ) { ?>
  <div class="hero" style="background-image: url('<?= $image ?>');">
    <h1><?php the_title(); ?></h1>
  </div>
<?php } ?>
<div class="container section">
  <?php while (have_posts()) : the_post(); ?>
    <article <?php post_class(); ?>>
      <header>
        <?php if ( !$image ) { ?>
          <h1 class="entry-title"><?php the_title(); ?></h1>
        <?php } ?>
        <?php get_template_part('templates/entry-meta'); ?>
      </header>
      <div class="entry-content">
        <?php the_content(); ?>
      </div>
      <footer>
        <?php wp_link_pages(['before' => '<nav class="page-nav"><p>' . __('Pages:', 'sage'), 'after' => '</p></nav>']); ?>
      </footer>
      <?php // comments_template('/templates/comments.php'); ?>
    </article>
  <?php endwhile; ?>
</div>
