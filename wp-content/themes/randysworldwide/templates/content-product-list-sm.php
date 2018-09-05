<?php
  use Roots\Sage\RANDYS;

  // Get the alternate product and the alt_banner
  $product_sm = wc_get_product( $post_id );
  $product_sm_sku = get_post_meta($post_id, '_sku', true);
  $product_sm_banner = get_post_meta($post_id, '_brandbannerid', true);
  $product_sm_proxynum = get_post_meta($post_id, '_proxynumber', true);

  // Check if we have a proxy number
  $product_sm_proxynum_output = $product_sm_proxynum ? '[' . $product_sm_proxynum . ']' : '';


  // Setup alternate products banner flags
  $product_sm_banner_class = null;
  if( $product_sm_banner === '1' ) {
    $product_sm_banner_class = ' archive-product__image-link--best-seller';
  } elseif( $product_sm_banner === '2' ) {
    $product_sm_banner_class = ' archive-product__image-link--best-value';
  } elseif( $product_sm_banner === '3' ) {
    $product_sm_banner_class = ' archive-product__image-link--best-warranty';
  }

  // Get Alternate Item Featured Image src
  $src = RANDYS\get_product_image_by_post_id($post_id, true);
?>
<article <?php post_class('archive-product archive-product--alternate'); ?>>
  <div class="row">
    <div class="col-xs-4">
      <a href="<?php echo get_permalink($post_id); ?>" class="archive-product__image-link<?php echo $product_sm_banner_class; ?>">
        <img src="<?php echo $src; ?>" alt="Product Image" width="300" class="woocommerce-placeholder wp-post-image" height="300">
      </a>
    </div>
    <div class="archive-product__body-wrapper col-xs-8">
      <header>
        <h2 class="archive-product__number entry-title"><a href="<?php echo get_permalink($post_id); ?>" class="archive-product__permalink"><?php echo $product_sm_sku; ?></a><span class="archive-product__sku--light"><?php echo $product_sm_proxynum_output; ?></span></h2>
        <p class="archive-product__title"><?php echo get_the_title($post_id); ?>
      </header>
      <?php if( null !== $product_sm->get_price() ): ?>
        <p class="price price--xs m-b-0">
          <span class="amount">$<?php echo $product_sm->get_price(); ?></span>
        </p>
      <?php endif; ?>
    </div>
    <div class="col-sm-12 m-t-1">
      <?php echo RANDYS\archive_cart_button_availability($product_sm); ?>
    </div>
  </div>
</article>
