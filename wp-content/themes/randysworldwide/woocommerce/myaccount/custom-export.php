<h2>Custom Export</h2>
<div class="back"><a href="/my-account"><i class="fa fa-arrow-left" aria-hidden="true"></i> My Account</a></div>
<div class="row">
    <div class="col-xs-12">
        <form id="form-custom-export" action="/wp-admin/admin-ajax.php" method="post">
          <h3><strong>Big Commerce File</strong></h3>
          <p>Admin Panel Big Commerce file with all active products
            Set customer number and price level override values.</p>
          <input name="action" type="hidden" value="create_custom_export" />
          <input type="hidden" id="create_custom_export_nonce" name="nonce" value="<?php echo wp_create_nonce("create_custom_export_nonce"); ?>">
          <label for="customer_number">Customer Number</label>
          <input id="customer_number" name="customer_number" type="text" />
          <label for="price_level">Price Level</label>
          <input id="price_level" name="price_level" type="text" />
          <input id="button-custom-export-generate" class="button button--short-blue button--slim m-t-1" type="button" value="GENERATE" />
          <div id="generate-result" class="m-t-1" style="background-color: #f1f1f1;border: solid 2px #636363; padding: 10px;margin:20px; display: none">
          </div>
        </form>
    </div>

    <div class="col-xs-12"><hr /></div>

    <div class="col-xs-12">
        <form id="form-upload-custom-export" action="/wp-admin/admin-ajax.php" method="post" enctype="multipart/form-data">
          <h3>Upload Big Commerce Product information</h3>
          <p>First provide an extract of your current Big Commerce products in "Bulk Edit" CSV format. Click <a href="https://support.bigcommerce.com/articles/Public/Exporting-Products/" target="_blank">here</a> for instructions on how to export from Big Commerce.</p>
          <p>A new file that contains only include new, updated, and obsolete products will be generated.</p>
          <input name="action" type="hidden" value="upload_custom_export" />
          <input type="hidden" id="upload_custom_export_nonce" name="nonce" value="<?php echo wp_create_nonce("upload_custom_export_nonce"); ?>">
          <input aria-label="Select File" accept=".csv" name="file" type="file" />
          <input id="button-upload-custom-export" class="button button--short-blue button--slim m-t-1" type="button" value="UPLOAD" />
          <div id="upload-result" class="m-t-1" style="background-color: #f1f1f1;border: solid 2px #636363; padding: 10px;margin:20px; display: none">
          </div>
        </form>
    </div>
</div>

<div>
    <form id="download_form" method="post" action="/wp-admin/admin-ajax.php" class="hidden">
        <input type="hidden" name="action" id="dowloadable_action" value="download_updated_custom_export"/>
        <input type="hidden" name="key" value="" id="downloadable_key"/>
        <input type="hidden" id="download_updated_custom_export_nonce" name="nonce" value="<?php echo wp_create_nonce("download_updated_custom_export_nonce"); ?>">
    </form>
</div>
