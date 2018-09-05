<?php use Roots\Sage\Extras;
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
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
 * @version     3.3.0
 */
?>
<div class="section">
  <div class="container">
    <?php get_template_part('templates/archive-header'); ?>
    <?php
      // Fix duplicate of get_header, get_sidebar, and get_footer
      // https://roots.io/using-woocommerce-with-sage/
      woocommerce_content();
    ?>
    <div class="center-align m-t-2">
      <?php echo Extras\base_pagination(); ?>
    </div>
  </div>
</div>
