<div class="section video-section">
  <div class="container">
    <?php
    // Inline video vars
    $video_image = get_sub_field('background_image')['sizes']['large'];
    $video_url = get_sub_field('video_url');
    $regex_pattern = "/(youtube.com|youtu.be)\/(watch)?(\?v=)?(\S+)?/";
    $match = '';

    if (preg_match($regex_pattern, $video_url, $match)) {
      $video_id = $match[4];
    } else {
      $video_id = '';
    }
    ?>
    <div class="inline-video" style="background-image:url('<?php echo $video_image; ?>');" data-youtube-id="<?php echo $video_id; ?>">
      <div id="player-inline"></div>
      <i class="inline-video__icon" aria-hidden="true" style="background-image:url(/wp-content/themes/randysworldwide/assets/images/Play-CTA.svg)"></i>
    </div>
  </div>
</div>
