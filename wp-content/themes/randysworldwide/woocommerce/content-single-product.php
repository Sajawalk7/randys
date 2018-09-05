<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     3.0.0
 */

use Roots\Sage\RANDYS;

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

  global $wpdb;
  global $product;

  /**
   * woocommerce_before_single_product hook.
   *
   * @hooked wc_print_notices - 10
   */
  do_action( 'woocommerce_before_single_product' );

  if ( post_password_required() ) {
    echo get_the_password_form();
    return;
  }

  $productID = get_the_ID();
  $sku = get_post_meta($productID, '_sku', true);
  $randy_productid = get_post_meta($productID, '_randy_productid', true);
?>

<?php
  if( RANDYS\check_current_cart($productID) && isset($_SESSION['diffID']) ) {
    RANDYS\get_complementary_items(
      $_SESSION['diffID'],
      $_SESSION['diffyear'],
      $_SESSION['make'],
      $_SESSION['model'],
      $_SESSION['drivetype'],
      RANDYS\getCustomerNumber(),
      get_post_meta($productID, '_sku', true)
    );
  }
?>

<div itemscope itemtype="<?php echo woocommerce_get_product_schema(); ?>" id="product-<?= $sku ?>" <?php post_class('product-overview'); ?>>
  <div class="row m-b-3">
    <?php
      /**
       * woocommerce_before_single_product_summary hook.
       *
       * @hooked woocommerce_show_product_images - 20
       */
      do_action( 'woocommerce_before_single_product_summary' );
    ?>

    <div class="summary entry-summary">
      <?php

        // Hook into the woocommerce_single_product_summary hook to apply our own technical notes
        add_action( 'woocommerce_single_product_summary', 'randys_woocommerce_template_single_technical_notes', 25 );
        function randys_woocommerce_template_single_technical_notes() {

          // Get the technical notes from the meta, these need to be placed below the "woocommerce_template_single_excerpt"
          $technical_notes = get_post_meta(get_the_ID(), '_tech_notes', true);

          // If technical notes were found, add them to the product description
          if ($technical_notes) {
            ?>
              <p><span class="bold dark-gray">Technical Notes: </span><?php echo $technical_notes; ?></p>
            <?php
          }
        }
		
        /**
         * woocommerce_single_product_summary hook.
         *
         * @hooked woocommerce_template_single_title - 5
         * @hooked woocommerce_template_single_price - 10
         * @hooked woocommerce_template_single_excerpt - 20
         * @hooked randys_woocommerce_template_single_technical_notes - 25
         * @hooked woocommerce_template_single_add_to_cart - 30
         */
        do_action( 'woocommerce_single_product_summary' );
        $availability_by_warehouse = RANDYS\get_product_availability_by_warehouse(get_the_ID());
        if( '0.00' === $product->get_price() ) {
          echo '<a href="/contact-us"  rel="nofollow" class="button">Call for Price</a>';
        } elseif( !array_sum($availability_by_warehouse) ) {
          echo '<a href="/contact-us" rel="nofollow" class="button">Call for Details</a>';
        }
      ?>

      <?php
        // Warehouse Availability
        echo RANDYS\warehouse_availability();

        // Installation guide
        $i_name = get_post_meta($productID, '_instruction_name', true);
        $i_file = get_post_meta($productID, '_instruction_file', true);

        // Make sure we have both file uri and the file's name
        if ($i_file && $i_name) {

          // Get the URL for the file, as well as the Directory so we can make sure the file exists.
          $i_file_url = untrailingslashit( wp_upload_dir()['baseurl'] ) . "/installation_instructions/" . $i_file;
          $i_file_dir = untrailingslashit( wp_upload_dir()['basedir'] ) . "/installation_instructions/" . $i_file;

          // Now make sure the file exists on this server before showing link
          if (file_exists($i_file_dir)) {

            ?>
            <div class="m-t-3">
              <a href="<?php echo $i_file_url; ?>" target="_blank" class="button button--ghost button--slim button--sm-height"><?php echo $i_name; ?></a>
            </div>
            <?php
          }
        }
      ?>
    </div><!-- .summary -->
  </div>

  <?php
    $pro_sku = $product->get_sku();
    // Related Videos
    $videos = get_posts(array(
      'post_type' => 'related-videos',
      'posts_per_page' => 3,
      'meta_query' => array(
        array(
          'key' => 'product_id', // name of custom field
          'value' => $pro_sku,
          'compare' => 'LIKE',
        ),
      ),
    ));
	// echo '<pre>';
	// print_r($videos);
    if ($videos) {
  ?>
  <section class="section section--sm">
    <div class="related-video">
      <div class="row">
        <?php
        foreach( $videos as $video ) {
          $youtube_id = explode('watch?v=', $video->post_content)[1];
          ?>
          <div class="related-video__item col-lg-4">
            <div class="related-video__image-wrapper video-modal-trigger" style="background-image:url('https://img.youtube.com/vi/<?= $youtube_id ?>/0.jpg');" data-toggle="modal" data-target="#video-modal" data-youtube-id="<?= $youtube_id ?>">
              <i class="related-video__video-icon" aria-hidden="true" style="background-image:url(/wp-content/themes/randysworldwide/assets/images/Play-CTA.svg)"></i>
            </div>
            <div class="related-video__body-wrapper">
              <a href="#" data-toggle="modal" data-target="#video-modal" data-youtube-id="<?= $youtube_id ?>" class="related-video__title video-modal-trigger"><?= $video->post_title ?></a>
              <p class="m-b-0"><?= $video->post_excerpt ?></p>
            </div>
          </div>
        <?php } ?>
      </div>
    </div>
    <?php require_once(get_theme_root() .'/randysworldwide/templates/video-modal.php'); ?>
  </section>
  <?php } ?>

  <?php
    // Quick Specs

    $manufacturer = get_post_meta($productID, '_brandname', true);

    $warranty = get_post_meta($productID, '_warranty_note', true);
    $warranty_link = null;


    // Check if any available content
    if( $product->has_weight() || $product->get_length() || $product->get_width() || $product->get_height() || $manufacturer || $warranty ):
  ?>
  <section class="section section--sm section--divider">
    <h2 class="product-section-title m-b-3">Quick Specs</h2>
    <div class="row">
      <div class="col-md-12 col-lg">

        <?php if($manufacturer) { ?>
          <div class="row m-b-2">
            <div class="col-xs-12 col-sm-3 bold dark-gray">Manufacturer:</div>
            <div class="col-xs"><?php echo $manufacturer; ?></div>
          </div>
        <?php } ?>

        <?php if ($warranty) { ?>
          <?php
            if (strpos($manufacturer, 'Yukon') !== false) {
              $warranty_link = '/customer-service/yukon-warranty/';
            } elseif(strpos($manufacturer, 'USA Standard') !== false) {
              $warranty_link = '/customer-service/usa-standard-warranty/';
            }
          ?>
          <div class="row m-b-2">
            <div class="col-xs-12 col-sm-3 bold dark-gray">Warranty:</div>
            <div class="col-xs">
              <?php echo $warranty; ?>
              <?php if( $warranty !== 'No Warranty' && $warranty_link) : ?>
              <div>
                <a href="<?php echo $warranty_link; ?>">More Info</a>
              </div>
              <?php endif; ?>
            </div>
          </div>
        <?php } ?>

        <?php if ( $product->has_weight() ) { ?>
        <div class="row m-b-2">
          <div class="col-xs-3 bold dark-gray">Weight:</div>
          <div class="col-xs"><?php echo $product->get_weight(); ?> lbs</div>
        </div>
      </div>
      <?php } ?>
      <?php if( $product->get_length() || $product->get_width() || $product->get_height() ): ?>
      <div class="col-md-12 col-lg">
        <?php if( $product->get_length() ) { ?>
        <div class="row m-b-2">
          <div class="col-xs-3 bold dark-gray">Shipping Length:</div>
          <div class="col-xs"><?php echo $product->get_length(); ?> inches</div>
        </div>
        <?php } ?>
        <?php if( $product->get_width() ) { ?>
        <div class="row m-b-2">
          <div class="col-xs-3 bold dark-gray">Shipping Width:</div>
          <div class="col-xs"><?php echo $product->get_width(); ?> inches</div>
        </div>
        <?php } ?>
        <?php if( $product->get_height() ) { ?>
        <div class="row m-b-2">
          <div class="col-xs-3 bold dark-gray">Shipping Height:</div>
          <div class="col-xs"><?php echo $product->get_height(); ?> inches</div>
        </div>
        <?php } ?>
      </div>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php
    // Compatible Differentials
    $compatible_differentials = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT Name
          FROM randys_differential
          WHERE DifferentialID IN (SELECT DifferentialID FROM `randys_differentialproduct` WHERE ProductID = %d)",
        array( $randy_productid )
      )
    );

    if ($compatible_differentials) {
      ?>
      <section class="section section--sm section--divider">
        <h2 class="product-section-title m-b-3">Compatible Differentials</h2>
        <ul class="list-two-col list-unstyled">
          <?php foreach ($compatible_differentials as $differential) { ?>
          <li><?php echo $differential->Name; ?></li>
          <?php } ?>
        </ul>
      </section>

      <?php
    }
    // Compatible Make / Model
    $compatible_make_model = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT MakeID, ModelID, CONCAT(Make, ' ', Model, ' (', StartYear, ' - ', EndYear, ')') AS Name
          FROM randys_advancedsearch
            WHERE ProductID = %d
          GROUP BY MakeID, ModelID, Make, Model
          ORDER BY Name",
        array( $randy_productid )
      )
    );

    if ($compatible_make_model) {
      ?>
      <section class="section section--sm">
        <h2 class="product-section-title m-b-3">Compatible Make / Model</h2>
          <ul class="list-two-col list-unstyled">
            <?php foreach ($compatible_make_model as $make_model) { ?>
            <li><?php echo $make_model->Name; ?></li>
            <?php } ?>
          </ul>
      </section>
      <?php
    }
  ?>
  <meta itemprop="itemCondition" content="newCondition" />
  <?php
  $image = get_the_post_thumbnail_url();
  if ( $image )  { ?>
    <meta itemprop="image" content="<?php echo get_the_post_thumbnail_url(); ?>" />
  <?php }
  ?>
  <meta itemprop="url" content="<?php the_permalink(); ?>" />
  <meta itemprop="description" content="<?php echo htmlentities($product->post->post_content); ?>" />

</div><!-- #product-<?= $sku ?> -->

<?php do_action( 'woocommerce_after_single_product' ); ?>
