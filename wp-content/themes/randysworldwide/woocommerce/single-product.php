<?php
/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */
  global $wpdb;

  $productID = get_the_ID();
  $sku = get_post_meta($productID, '_sku', true);

  // Setup back button URL and text
  if( isset($_SESSION['product-back-button-url']) ) {
    $back_button_url = $_SESSION['product-back-button-url'];
    $back_button_text = $_SESSION['product-back-button-text'];
  } else {
    $back_button_url = '/#diff-wizard-form';
    $back_button_text = 'Search Products';
  }

  // get UPC based off the current sku number
  $proxy_results = $wpdb->get_row(
    $wpdb->prepare(
      "SELECT DISTINCT proxy FROM randys_productupc WHERE ProductNumber = %s",
      $sku
    )
  );

  // if we have a proxy number setup the output
  $proxy_output = '';
  if( !empty($proxy_results) ) {
    // we should only have one proxy
    $proxy_number = $proxy_results->proxy;
    $proxy_output = '<span class="product-header__sku--light">[' . $proxy_number . ']</span>';
  }

?>
<div class="section">
  <div class="container">
    <div class="product-header">
      <a href="<?php echo $back_button_url; ?>" class="button button--ghost button--sm-height button--slim m-r-2"><?php echo $back_button_text; ?></a>
      <div class="product-header__sku"><?php echo $sku; ?> <?php echo $proxy_output; ?> </div>
    </div>
    <hr />
    <?php
      // Fix duplicate of get_header, get_sidebar, and get_footer
      // https://roots.io/using-woocommerce-with-sage/
      woocommerce_content();
    ?>
  </div>
</div>
