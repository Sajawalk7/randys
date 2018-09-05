<?php
  $link = get_permalink();
  $resource_image = get_the_post_thumbnail_url($post->ID, 'large');
  if( !$resource_image ) {
    $resource_image = '/wp-content/themes/randysworldwide/dist/images/RANDYS_logo_grey.svg';
  }

  // Get resource type and set $cta_text depending on result
  $resource_type = get_field('resource_type');
  $cta_text = 'View Details';
  $resource_type_class = '';
  if( $resource_type === 'video' ) {
    $cta_text = 'Watch';
    $resource_type_class = 'card-resource-item--video';
  } elseif( $resource_type === 'pdf' ) {
    $cta_text = 'View PDF &nbsp;<i class="fa fa-file-text" aria-hidden="true"></i>';
    $resource_type_class = 'card-resource-item--pdf';
  }


  // Get youtube input
  // use regex to get id of video
  $youtube_url = get_field('resource_youtube_video_url');
  $regex_pattern = "/(youtube.com|youtu.be)\/(watch)?(\?v=)?(\S+)?/";
  $match = '';
  if (preg_match($regex_pattern, $youtube_url, $match)) {
    $video_id = $match[4];
  } else {
    $video_id = '';
  }

  // If video url present input modal data attr
  $video_modal_data = '';
  if( $youtube_url ) {
    $video_modal_data = 'data-toggle="modal" data-target="#video-modal" data-youtube-id="' . $video_id.'"';
  }
?>
<div class="col-md-6 col-lg-3">
  <div class="card card-resource-item <?php echo $resource_type_class; ?>">
    <?php if( $resource_image ): ?>
      <a href="<?php echo $link; ?>" class="card-resource-item__image-container video-modal-trigger" <?php echo $video_modal_data; ?>>
        <img class="card-img-top img-fluid" src="<?php echo $resource_image; ?>" alt="Card image cap" />
        <?php if( $youtube_url ): ?>
        <i class="card-resource-item__video-icon" aria-hidden="true" style="background-image:url(/wp-content/themes/randysworldwide/assets/images/Play-CTA.svg)"></i>
        <?php endif; ?>
      </a>
    <?php endif; ?>
    <div class="card-block center-align">
      <h4 class="card-title card-resource-item__title"><?php the_title(); ?></h4>
      <div class="card-text card-resource-item__text"><?php the_excerpt(); ?></div>
      <a href="<?php echo $link; ?>" class="video-modal-trigger" <?php echo $video_modal_data; ?>><?php echo $cta_text; ?></a>
    </div>
  </div>
  <?php
    // if resource type is equal to video add video modal
    if( $resource_type === 'video' ) {
      require_once('video-modal.php');
    }
  ?>
</div>
