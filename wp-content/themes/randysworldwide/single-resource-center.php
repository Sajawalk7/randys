<?php while (have_posts()) : the_post(); ?>
<?php

  // Deterimine what type of resource
  // update link and link text based on results
  $resource_type = get_field('resource_type');
  $cta_text = 'View Details';
  $link_url = get_field('resource_link');
  if( $resource_type === 'video' ) {
    $cta_text = 'Watch';
    $link_url = get_field('resource_youtube_video_url');
  } elseif( $resource_type === 'pdf' ) {
    $cta_text = 'View PDF &nbsp;<i class="fa fa-file-text" aria-hidden="true"></i>';
    $link_url = get_field('pdf_document')['url'];
  }

?>
<div class="section section--tan">
  <div class="container">
    <div class="row">
      <div class="col-md-10 offset-md-1">
      <h1 class="section-title m-b-1"><?php the_title(); ?></h1>
      <div class="wysiwyg-content">
        <p><?php the_content(); ?></p>
      </div>
      <?php if( $link_url ): ?>
      <div class="center-align m-t-3">
        <a href="<?php echo $link_url; ?>" target="_blank" class="button"><?php echo $cta_text; ?></a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endwhile; ?>
