<?php
$post_id = get_the_id();
$image = get_the_post_thumbnail_url($post_id, 'large');
$image = $image ? '<img src="'.$image.'" alt="image" class="img-fluid">' : null;
?>

<article <?php post_class(); ?>>
  <div class="row m-t-3 m-b-3">
    <?php if ( $image ) { ?>
      <div class="col-sm-4 align-self-center">
        <a href="<?php the_permalink(); ?>">
          <?= $image ?>
        </a>
      </div>
      <div class="col-sm-8 align-self-center">
    <?php } else { ?>
      <div class="col-sm-12">
    <?php } ?>
    <header>
      <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
      <?php get_template_part('templates/entry-meta'); ?>
    </header>
    <div class="entry-summary">
      <?php the_excerpt(); ?>
    </div>
  </div>
</article>
