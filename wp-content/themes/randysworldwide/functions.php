<?php
/**
 * Sage includes
 *
 * The $sage_includes array determines the code library included in your theme.
 * Add or remove files to the array as needed. Supports child theme overrides.
 *
 * Please note that missing files will produce a fatal error.
 *
 * @link https://github.com/roots/sage/pull/1042
 */
remove_action( 'template_redirect', 'redirect_canonical' );
$sage_includes = [
  'lib/avatax-modification/modify-tax-actions.php', // Modifies the avatax plugin to use shipments
  'lib/assets.php',                 // Scripts and stylesheets
  'lib/extras.php',                 // Custom functions
  'lib/setup.php',                  // Theme setup
  'lib/titles.php',                 // Page titles
  'lib/wrapper.php',                // Theme wrapper class
  'lib/customizer.php',             // Theme customizer
  'lib/bs4Navwalker.php',           // Adding back in bootstrap navwalker - v4
  'lib/functions/tiles.php',        // Adding tile layout function
  'lib/functions/warranty.php',     // Adding warranty layout function
  'lib/functions/get_glossary.php', // Function for getting glossary letter from ajax request
  'lib/functions/get_product_sort.php', // Function for getting product sort ajax request
  'lib/functions/get_product_filter.php', // Function for getting product filter ajax request
  'lib/functions/get_another_diff.php', // Function for getting another diff from ajax request
  'lib/functions/get_pagination.php', // Function for getting another diff from ajax request
  'lib/functions/check_diff_image.php', // Function for checking diff image from ajax request
  'lib/freshDBIntegration.php',     // Custom Database Integration functions
  'lib/freshProductImportCron.php', // Custom product importer script
  'lib/freshDifferentialsImportCron.php', // Custom differential importer script
  'lib/freshWooCommerce.php',       // Woocommerce hooks
  'lib/woocommerce_checkout_overrides.php',  // WooCommerce Cart/Checkout hooks
  'lib/filtering-apis/diff-wizard.php',
  'lib/filtering-apis/product-browsing.php',
  'lib/downloadable-images.php',
  'lib/downloadable-products.php',
  'lib/customer-access.php',
  'lib/custom-export.php',
  'lib/order-express.php',
  'lib/redirector.php',
  'lib/cartonization/GetBoxesForOrder.php',
  'lib/cartonization/GetShippingOptionsForShipment.php',

];

foreach ($sage_includes as $file) {
  if (!$filepath = locate_template($file)) {
    trigger_error(sprintf(__('Error locating %s for inclusion', 'sage'), $file), E_USER_ERROR);
  }

  require_once $filepath;
}
unset($file, $filepath);



// Tj testing
function sh_coupons_add_meta_boxes( $post ){
  add_meta_box( 'prefix_meta_box', "Developer Secret", 'sh_coupons_meta_box_fun', null, 'side','low');
}

add_action( 'add_meta_boxes_shop_coupon', 'sh_coupons_add_meta_boxes' );

function sh_coupons_meta_box_fun(){
  global $post;
  echo "Making Something Funny.";
  if ($post->filter=="edit"){
      ?>
    <script type="text/javascript">
      var old_title=jQuery("#title").val();
      var title=old_title.split("-");
      if(title[0]=="discount"){
        title.shift();
        var new_title=title.join("-");
        jQuery("#title").val(new_title);
      }
      jQuery("#title").attr("placeholder","");
    </script>
    <?php } else{ ?>
    <script type="text/javascript">
    jQuery("#title").attr("placeholder","");

    </script>

      <?php }
  ?>
  <style type="text/css">
    #title-prompt-text{
      display: none;
    }
    #titlediv #title{
      padding-left: 101px;
    }
    div#titlewrap{
      position: relative;
    }
    div#titlewrap::after {
        content: "discount-";
        position: absolute;
        top: 0px;
        left: 1px;
        font-size: 1.7em;
        color: grey;
        padding: 10px 8px;
    }
    #prefix_meta_box{
      display: none;
    }
  </style>
  <?php
}



add_filter( 'wp_insert_post_data' , 'modify_post_title' , '99', 1 ); // Grabs the inserted post data so you can modify it.

function modify_post_title( $data )
{
  if($data['post_type'] == 'shop_coupon' && isset($_POST['post_title'])) { // If the actual field name of the rating date is different, you'll have to update this.
    $title="discount-".$_POST['post_title'];
    $data['post_title'] =  $title ; //Updates the post title to your new title.
  }
  return $data; // Returns the modified data.
}

/*404 widget*/
register_sidebar( array( 
	'name' => '404', 
	'id' => '404', 
	'before_widget' => '<div id="%1$s" class="%2$s widget not-found">', 
	'after_widget' => '</div>', 
	'before_title' => '<h3 class="widget-title not-found-title">', 
	'after_title' => '</h3>'
) );

/* zumbrota parts finder shortcode */
add_shortcode( 'zumbrota_parts_finder', 'add_zumbrota_parts_finder' ); 
function add_zumbrota_parts_finder() {
	
?>
	<!-- Zumbrota search form -->
	<div class="zumb-wrapper">
		<div class="zumbrota-search">
			<h2 class="form-title">FIND THE PART YOU NEED</h2>
			<form action="/zumbrota-search-results" class="search-by-parts-form" id="form-parts">
				<div class="input-box">
					<input type="search" name="search_by_number" id="search_by_number" value="" class="input-text required-entry" maxlength="128" placeholder="Search By Part Number">
					<button type="submit" class="button search-button" name="stype" value="part_module">Search Parts</button>
				</div>
			</form>
			<div class="or-wrapper clearfix">
				<div class="left two-column line">
					<div class="inner"></div>
				</div>
				<div class="or absolute">OR</div>
				<div class="left two-column line">
					<div class="inner"></div>
				</div>
			</div>

			<form action="/zumbrota-search-results" class="advanced-part-search" method="get" id="form-attr">
				<div class="clearfix fields">
					<div class="left one-column xsm-tablet-one-column field">
						<select name="zumbrota_category" id="zumbrota_category" data-label="CATEGORY" required>
							<option value="">Category</option>
							<option value="23">Transfer Cases</option>
							<option value="24" class="subcat">--Transfer Cases</option>
							<option value="25" class="subcat">--Transfer Case Parts</option>
							<option value="4">Differentials</option>
							<option value="10" class="subcat">--Front Differentials</option>
							<option value="11" class="subcat">--Rear Differentials</option>
							<option value="12" class="subcat">--Differential Parts</option>
							<option value="5">Transmissions</option>
							<option value="13" class="subcat">--Manual Transmissions</option>
							<option value="14" class="subcat">--Automatic Transmissions</option>
							<option value="6">Engines</option>
							<option value="7">Parts</option>
							<option value="16" class="subcat">--Axles</option>
							<option value="17" class="subcat">--Bearing / Seal Gaskets Kits</option>
							<option value="26" class="subcat">----Differential Kits</option>
							<option value="27" class="subcat">----Manual Transmission Kits</option>
							<option value="28" class="subcat">----Transfer Case Kits</option>
							<option value="18" class="subcat">--Differential Parts</option>
							<option value="19" class="subcat">--Manual Transmission Parts</option>
							<option value="20" class="subcat">--Transfer Case Parts</option>
							<option value="21" class="subcat">--Bearings</option>
							<option value="22" class="subcat">--Yokes &amp; U Joints</option>
						</select>
					</div>
					<div class="left two-column xsm-tablet-one-column field">
						<select name="zumbrota_year" id="zumbrota_year" data-label="Year" disabled>
							
							<option value="">Year</option>
						</select>
					</div>
					<div class="left two-column xsm-tablet-one-column field">
						<select name="zumbrota_make" id="zumbrota_make" data-label="Make" disabled>
							<option value="">Make</option>
						</select>
					</div>
					<div class="left two-column xsm-tablet-one-column field">
						<select name="zumbrota_model" id="zumbrota_model" data-label="Model" disabled>
							<option value="">Model</option>
						</select>
					</div>
					<div class="left two-column xsm-tablet-one-column field">
						<select name="zumbrota_unit_model_name" id="zumbrota_unit_model_name" data-label="Unit Model Name" disabled>
							<option value="">Unit Model Name</option>
						</select>
					</div>
				</div>

				<div class="field-submit text-center">
					<button type="submit" name="stype" value="cat_module">Search Now</button>
				</div>
			</form>
			<div class="ajax-loader"></div>
		</div>	
	</div>	
<?php	

}
include 'zumbrota-search.php';
/* JS code for zumbrota category filter */

wp_register_script('zumbrotascript', get_template_directory_uri() . '/dist/scripts/zumbrota.js', array('jquery'), '1.0.0'); // Custom scripts
wp_enqueue_script('zumbrotascript'); // Enqueue it!