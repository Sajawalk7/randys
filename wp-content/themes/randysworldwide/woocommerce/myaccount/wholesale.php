<?php
/**
 * Wholesale dashboard
 *
 * This template will display wholesale dashboard items
 * based on user access levels.
 */

use Roots\Sage\RANDYS;

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

if (1 == Customer_Access::current_class() ) {
  ?>
    <div class="col-sm-4">
      <header class="title">
        <h3>Credit</h3>
      </header>
      <?php
      echo RANDYS\get_credit_html(); ?>
      <?php if (Customer_Access::current_access_level() >= Customer_Access::LEVEL_DATA_ACCESS) { ?>
        <header class="title m-t-3">
          <h3>Shopping Options</h3>
        </header>
        <ul>
          <li><a href="/my-account/order-express">Order Express</a></li>
        </ul>
      <?php } ?>
    </div>
    <div class="col-sm-4">
      <header class="title">
        <h3>Tools and Downloads</h3>
      </header>
      <ul>
        <li><a href="/customer-service/#request-form">Part Returns Form</a></li>
        <li><a href="/my-account/digital-banners">Digital Banners</a></li>
        <li><a href="/my-account/download-images">Downloadable Images</a></li>
        <?php if (Customer_Access::current_access_level() >= Customer_Access::LEVEL_DATA_ACCESS) { ?>
            <li><a id="download-product-xml" href="#">Product Data - XML</a></i></li>
            <li><a id="download-product-csv" href="#">Product Data - CSV</a></li>
            <li><a id="download-product-application-xml" href="#">Product Application - XML</a></li>
            <li><a id="download-product-application-excel" href="#">Product Application - Excel</a></li>
            <?php if (Customer_Access::current_access_level() >= Customer_Access::LEVEL_BIG_COMMERCE_ACCESS) { ?>
                <li><a href="/my-account/custom-export/">Custom Export</a></li>
            <?php } ?>
        <?php } ?>
      </ul>
      <div id="div-loading-product-data">
        <img alt="Loading..." src="../wp-content/themes/randysworldwide/dist/images/ajax-loader.gif">&nbsp;&nbsp;&nbsp;&nbsp;PREPARING DOWNLOAD... Please Wait
      </div>
      <form id="product_data_download_form" class="hidden" action="/wp-admin/admin-ajax.php" method="post">
          <input name="action" type="hidden" value="download_product_file" />
          <input id="product_data_download_key" name="key" type="hidden" value="" />
          <input id="product_data_download_ext" name="ext" type="hidden" value="" />
          <input type="hidden" id="downloadable_products_nonce" name="nonce" value="<?php echo wp_create_nonce("downloadable_products_nonce"); ?>">
      </form>
      <?php
      $distributor_doc = get_field('document_upload', 'options');
      if ($distributor_doc) {
      ?>
      <header class="title m-t-2">
        <h3>Distributor Documents</h3>
      </header>
      <ul>
        <li><a href="<?= $distributor_doc ?>" target="_blank">RANDYS Distributor Packet</a></li>
      </ul>
      <?php } ?>
    </div>
  
  <?php
}
