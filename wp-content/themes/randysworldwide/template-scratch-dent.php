<?php
/**
 * Template Name: Scratch & Dent Template
 */

use Roots\Sage\Titles;
?>

<?php while (have_posts()) : the_post();
  $image = get_field('hero_background_image')['sizes']['hero'];
  $icon = get_field('hero_icon');
  $title = get_field('hero_title');
  $page_title = $title ? $title : Titles\title();
  $message = get_field('call_message');
  ?>
  <div class="hero" style="background-image: url('<?= $image ?>');">
    <?php if( $icon ): ?>
      <img src="<?= get_template_directory_uri(); ?>/dist/images/<?= $icon ?>_circle.svg" class="hero__icon" alt="<?= $page_title ?>">
    <?php endif; ?>
    <h1> <?= $page_title ?></h1>
  </div>

  <div class="section scratch-dent">
    <div class="container">
      <?php if( have_rows('scratch_&_dent_items') ) { ?>
        <?php while( have_rows('scratch_&_dent_items') ) { the_row();
          $name = get_sub_field('name');
          $description = get_sub_field('description');
          $price = get_sub_field('price');
          $image = get_sub_field('image')['sizes']['large'];
          ?>
          <div class="row scratch-dent__item m-b-3">
            <div class="col-sm-5 align-self-center">
              <img src="<?= $image ?>" alt="<?= $name ?>" class="img-fluid"><br>
              <span class="sm">Image may vary from actual product</span>
            </div>
            <div class="col-sm-7 align-self-center">
              <h3><?= $name ?></h3>
              <p class="price"><?= $price ?></p>
              <p><?= $description ?></p>
              <span class="orange"><?= $message ?></span>
            </div>
          </div>
        <?php } ?>
      <?php } ?>
    </div>
  </div>
<?php endwhile; ?>
