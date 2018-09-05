<?php
  use Roots\Sage\RANDYS;

  $product = wc_get_product( get_the_ID() );
  $product_link = get_permalink();
  $randys_sku = get_post_meta(get_the_ID(), '_sku', true);
  $randy_productid = get_post_meta(get_the_ID(), '_randy_productid', true);
  $randy_proxynum = get_post_meta(get_the_ID(), '_proxynumber', true);

  // Check if we have a proxy number
  $proxynum_output = $randy_proxynum ? '[' . $randy_proxynum . ']' : '';

  // Setup products banner flags
  if( isset($source) && $source === 'diff-wizard' ) {
    $banner_class = null;
    $banner = get_post_meta(get_the_ID(), '_brandbannerid', true);
    if( $banner === '1' ) {
      $banner_class = ' archive-product__image-link--best-seller';
    } elseif( $banner === '2' ) {
      $banner_class = ' archive-product__image-link--best-value';
    } elseif( $banner === '3' ) {
      $banner_class = ' archive-product__image-link--best-warranty';
    }
  }

?>

<div class="container">
  <article <?php post_class('archive-product'); ?>>
    <div class="row">
      <div class="col-md-2">
        <a href="<?php echo $product_link; ?>" class="archive-product__image-link<?php if(isset($banner_class)) { echo $banner_class; } ?>">
          <?php echo wc_placeholder_img('large'); ?>
        </a>
      </div>
      <div class="col-md-10">
        <div class="row">
          <div class="archive-product__body-wrapper col-md-12 col-lg-7">
            <header>
              <h2 class="archive-product__number entry-title"><a href="<?php echo $product_link; ?>" class="archive-product__permalink"><?php echo $randys_sku; ?></a><span class="archive-product__sku--light"><?php echo $proxynum_output; ?></span></h2>
              <p class="archive-product__title"><?php the_title(); ?>
              <?php if (get_post_type() === 'post') { get_template_part('templates/entry-meta'); } ?>
            </header>
          </div>
          <div class="archive-product__body-wrapper col-md-5 col-lg-6 col-xl-7">
            <?php $price = $product->get_price(); ?>
            <?php if( null !== $price && '0.00' !== $price ): ?>
              <p class="price price--lg"><span class="amount">$<?php echo $price ?></span>
                <?php 
                if ( is_user_logged_in() && RANDYS\is_wholesale() ) { ?>
                <span class="retailPrice">[<?php echo $map_price = wc_price( get_post_meta(get_the_ID(), '_price_3')[0] );?>]</span>
                <style type="text/css">
                  .retailPrice,.retailPrice span{
                    font-size: 17px !important;
                    color: red;
                  }
                </style>
                <?php } ?>
              </p>
            <?php endif; ?>
            <div class="entry-summary m-b-1">
              <?php echo RANDYS\archive_cart_button_availability($product); ?>
            </div>
          </div>
          <div class="archive-product__warehouse col-md-7 col-lg-6 col-xl-5 align-self-end">
            <?php
              echo RANDYS\warehouse_availability();
            ?>
          </div>
        </div>
      </div>
    </div>
  </article>
</div>
<?php
if( isset($alternate_items) ) {

  global $wpdb;
  $i = 0;

  foreach($alternate_items as $product_sm) {
    if( $product_sm->PrimaryID === $randy_productid ) {

      // get the products post id
      $product_post_ID = $wpdb->get_results(
        $wpdb->prepare(
          "SELECT post_id FROM wp_postmeta WHERE meta_key = %s AND meta_value = %d",
          array('_randy_productid', $product_sm->ProductID)
        )
      );

      // Set the Post ID for this Alternate Item
      $post_id = $product_post_ID[0]->post_id;

      // Place in section and title on first item
      if( $i === 0 ):
    ?>
      <div class="section section--tan section--sm section--triangle-top">
        <div class="container">
          <h3 class="archive-product__alt-parts-title">Alternative Parts</h3>
          <div class="row products-slider">
      <?php endif; //  if( $i === 0 ) ?>
      <?php include(locate_template('templates/content-product-list-sm.php', false, false)); ?>
    <?php $i++; }
  }
  echo '</div></div></div>';
}
