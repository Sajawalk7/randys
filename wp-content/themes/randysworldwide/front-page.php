<?php
  // Hero ACF Fields
  $hero_image_url = get_field('hero_background_image')['sizes']['hero'];
  $hero_image_url_mobile = get_field('hero_background_image_mobile')['sizes']['large'];
?>
<div class="hero hero--home hidden-sm-down" style="background-image: url('<?php echo $hero_image_url; ?>');">
</div>
<div class="hero hero--home hidden-md-up" style="background-image: url('<?php echo $hero_image_url_mobile; ?>');">
</div>
<div class="container">
  <div id="diff-wizard-form" class="diffwizard diffwizard--controls-home">
    <a href="#" class="button--clear white float-right m-t-1 hidden-sm-down">Clear</a>
    <h3><img src="<?= get_template_directory_uri(); ?>/dist/images/diff_wizard_logo.svg" alt="Diff Wizard" class="diffwizard__logo"> <span>FIND IT.</span> TRUST IT. BUY IT.</h3>
    <?php require('templates/diff-wizard-form.php'); ?>
  </div>
</div>
<?php
  $slides = get_field('homepage_slides');
  if( $slides ):
?>
<div class="section">
  <div class="container">
    <h2 class="center-align"><?= the_field('title') ?></h2>
    <div class="row">
      <div class="col-sm-10 offset-sm-1">
        <div class="product-slider">
          <?php foreach( $slides as $slide ): ?>
            <?php
              $link = isset($slide["link"][0]["page"]) ? $slide["link"][0]["page"] : $slide["link"][0]["url"];
              $img_url = $slide["image"]["sizes"]["medium"];
              if( $img_url ) {
                $image_html = '<img src="' . $img_url . '" class="img-fluid" alt="' . get_the_title() . '">';
              } else {
                $image_html = wc_placeholder_img( 'medium' );
              }
            ?>

          <div class="slide">
            <a href="<?= $link ?>" class="slide__image-wrapper"><?php echo $image_html; ?></a>
            <a href="<?= $link ?>"><?= $slide["title"] ?></a>
          </div>
          <?php endforeach; wp_reset_postdata(); ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
<div class="section section--tan">
  <div class="container">
    <h2 class="section-title center-align"><?= the_field('resources_title') ?></h2>
    <div class="tile-container">
      <div class="grid-sizer"></div>
      <div class="gutter-sizer"></div>
        <?php
          // Module one ACF Fields
          $m_one_primary_image_url = get_field('module_one_image')['sizes']['large'];
          $m_one_type = get_field('module_one_type');
          if( 'link' === $m_one_type ) {
            $m_one_video_url = null;
            $m_one_url = get_field('module_one_button_link');
            $m_two_button_label = get_field('module_one_button_label');
          } elseif( 'video' === $m_one_type ) {
            $m_one_video_url = get_field('module_one_video_url');
            $m_one_url = null;
            $m_two_button_label = null;
          }
          $m_one_title = get_field('module_one_title');
          $m_one_description = get_field('module_one_description');
          $m_one_product_url = get_field('module_one_product_icon');
          if( $m_one_product_url === 'none' || $m_one_product_url === null ) {
            $m_one_product_url = null;
          } elseif( $m_one_product_url === 'yukon' ) {
            $m_one_product_url = '/wp-content/themes/randysworldwide/assets/images/yukon_logo_color.svg';
          } elseif( $m_one_product_url === 'standard_gear' ) {
            $m_one_product_url = '/wp-content/themes/randysworldwide/assets/images/usa_standard_logo_color.svg';
          }

          echo get_tile('half', 'dark', 'top', $m_one_primary_image_url, $m_one_title, $m_one_description, $m_one_product_url, $m_one_video_url, $m_one_url, $m_two_button_label, null, true);
        ?>
        <?php
          // Module two ACF Fields
          $m_two_primary_image_url = get_field('module_two_image')['sizes']['large'];
          $m_two_title = get_field('module_two_title');
          $m_two_description = get_field('module_two_description');
          $m_two_product_url = get_field('module_two_product_icon');
          if( $m_two_product_url === 'none' || $m_two_product_url === null ) {
            $m_two_product_url = null;
          } elseif( $m_two_product_url === 'yukon' ) {
            $m_two_product_url = '/wp-content/themes/randysworldwide/assets/images/yukon_logo_white.svg';
          } elseif( $m_two_product_url === 'standard_gear' ) {
            $m_two_product_url = '/wp-content/themes/randysworldwide/assets/images/usa_standard_logo_color.svg';
          }
          $m_two_button_link = get_field('module_two_button_link');
          $m_two_button_label = get_field('module_two_button_label');

          echo get_tile('half', 'light', 'left', $m_two_primary_image_url, $m_two_title, $m_two_description, $m_two_product_url, null, $m_two_button_link, $m_two_button_label, true);
        ?>
        <?php
          // Module three ACF Fields
          $m_three_primary_image_url = get_field('module_three_image')['sizes']['large'];
          $m_three_title = get_field('module_three_title');
          $m_three_description = get_field('module_three_description');
          $m_three_product_url = get_field('module_three_product_icon');
          if( $m_three_product_url === 'none' || $m_three_product_url === null ) {
            $m_three_product_url = null;
          } elseif( $m_three_product_url === 'yukon' ) {
            $m_three_product_url = '/wp-content/themes/randysworldwide/assets/images/yukon_logo_color.svg';
          } elseif( $m_three_product_url === 'standard_gear' ) {
            $m_three_product_url = '/wp-content/themes/randysworldwide/assets/images/usa_standard_logo_color.svg';
          }
          $m_three_button_link = get_field('module_three_button_link');
          $m_three_button_label = get_field('module_three_button_label');

          echo get_tile('half', 'light', 'left', $m_three_primary_image_url, $m_three_title, $m_three_description, $m_three_product_url, null, $m_three_button_link, $m_three_button_label, true);
        ?>
    </div>
  </div>
</div>
<div class="section">
  <div class="container">
    <h2 class="center-align">Calculators</h2>
    <?php get_template_part('/pagebuilder_modules/calculator/landing'); ?>
  </div>
</div>
<?php require_once('templates/sections/section-cta.php'); ?>
<?php require_once('templates/section-warranty.php'); ?>
