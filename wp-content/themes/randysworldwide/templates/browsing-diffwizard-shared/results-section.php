<?php
  use Roots\Sage\Extras;
  use Roots\Sage\RANDYS;


  // Make sure the diff id is set
  if( isset( $_GET['diffid']) ) {

    if( is_page('diff-wizard') && isset($_GET['diffyear']) ) {
      // Store results in sessions
      $_SESSION['diffID'] = $_GET['diffid'];
      $_SESSION['diffyear'] = $_GET['diffyear'];
      $_SESSION['make'] = $_GET['make'];
      $_SESSION['model'] = $_GET['model'];
      $_SESSION['drivetype'] = $_GET['drivetype'];
    }

    // Setup query for products related to diff id
    $diffID = $_GET['diffid'];

    $selectedCat = null;
    if( isset($_GET['cat-category']) ) {
      $selectedCat = $_GET['cat-category'];
    }

    $parentID = null;
    if( isset($_GET['parent-id']) ) {
      $parentID = $_GET['parent-id'];
    }

    // Function will return array of ids
    $product_results = RANDYS\get_product_id_query($diffID, $parentID, $selectedCat);
    // Setup string of ids returned by $product_results
    $productIDs = [];
    foreach( $product_results as $id) {
      $productIDs[] .= $id->post_id;
    }

    // Setup loop so we can get total pages and query count
    $args = RANDYS\product_archive_query_args($productIDs, 'price');
    $results = RANDYS\product_archive_query($args, null, 'price');
    $total_pages = $results[2];

    // We have the diffid. Use it to get Name, Description, and image
    $diffname_results = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT DISTINCT a.DiffName, a.DiffDescription, d.FullImage FROM randys_advancedsearch a JOIN randys_differential d ON d.DifferentialID = a.DiffID WHERE DiffID = %d",
        $_GET['diffid']
      )
    );

    $diffname = $diffname_results[0]->DiffName;
    $diffdescription = $diffname_results[0]->DiffDescription;
    $diffimagefile = $diffimage = $diffname_results[0]->FullImage;
    $diffbackbutton = '/#diff-wizard-form';

    if( is_product_category() ) {
      $product = get_queried_object();
      $product_slug = $product->slug;
      $diffbackbutton = '/product-category/' . $product_slug;
    }

    // Path defined in /lib/extras.php
    global $diff_image_path;

    // build out image path and filename
    $diffimage = $diff_image_path . $diffimagefile;

    // Check if file exits in directory
    if(file_exists($_SERVER['DOCUMENT_ROOT'] . $diffimage)) {
      $diffimage_src = $diffimage;
    } else {
      $diffimage_src = '/wp-content/themes/randysworldwide/dist/images/randys_product_default.png';
    }

  } elseif( isset( $_GET['cat-category']) ) {

    $selectedCat = $_GET['cat-category'];
    $parentID = $_GET['parent-id'];

    // Function will return array of ids
    $product_results = RANDYS\get_product_id_query(null, $parentID, $selectedCat);

    // Setup string of ids returned by $product_results
    $productIDs = [];
    foreach( $product_results as $id) {
      $productIDs[] .= $id->post_id;
    }

    if( $productIDs ) {
      // Setup loop so we can get total pages and query count
      $args = RANDYS\product_archive_query_args($productIDs, 'price');
      $results = RANDYS\product_archive_query($args, null, 'price');
      $total_pages = $results[2];
    }
  } else {

    $parentID = $wpdb->get_row(
      $wpdb->prepare(
        "SELECT DISTINCT CategoryID FROM randys_category WHERE CategoryName = %s AND ParentID = %s",
        array(html_entity_decode(get_queried_object()->name), 0)
      )
    );

    // Add a hidden field so we can grab the category id in the ajax request for filtering
    echo '<input type="hidden" class="queried-category" value="'.$parentID->CategoryID.'">';

    $product_results = RANDYS\get_product_id_from_cat_query($parentID->CategoryID);

    // Setup string of ids returned by $product_results
    $productIDs = [];
    foreach( $product_results as $id) {
      $productIDs[] .= $id->post_id;
    }

    if( $productIDs ) {
      // Setup loop so we can get total pages and query count
      $args = RANDYS\product_archive_query_args($productIDs, 'price');
      $results = RANDYS\product_archive_query($args, null, 'price');
      $total_pages = $results[2];
    }
  }

?>
<?php if( isset( $_GET['diffid']) ) { ?>
<div class="section section--tan section--lg-top">
  <div class="container">
    <div class="row">
      <div id="differential-item" class="col-lg-7 col-xs-12 m-b-2">
        <div class="differential-item__differential-data row">
          <div class="col-sm-6">
            <img src="<?php echo $diffimage_src; ?>" class="img-fluid mx-auto">
          </div>
          <div class="col-sm-6">
              <p class="differential-item__selectedname">Your selected differential</p>
              <h3 class="differential-item_diffname"><?php echo $diffname; ?></h3>
              <p class="differential-item__diffdescription"><?php echo $diffdescription; ?></p>
              <a href="<?php echo $diffbackbutton; ?>" class="button button--slim button--ghost back-button"><i class="fa fa-chevron-left hidden-md-down" aria-hidden="true"></i> &nbsp;This is not my Differential</a>
          </div>
        </div>
      </div>
      <div class="col-lg-5">
        <?php include( locate_template('templates/select-diff-form.php') ); ?>
      </div>
    </div>

    <?php if( is_page('diff-wizard') ): ?>

    <div class="products-filter products-filter--diffwizard">
      <div class="row justify-content-between">
        <div class="col-xs">
          <h3 class="products-filter__title">Filter Your Parts by Category</h3>
        </div>
        <div class="col-xs">
          <div class="products-filter__reset text-right">Reset <i class="products-filter__reset-icon fa fa-refresh" aria-hidden="true"></i></div>
        </div>
      </div>
      <div class="row">
        <div class="center-align"><div class="products-filter__spinner spinner" style="display: none;"></div></div>
      </div>
      <div class="products-filter__list">
        <?php
          // Get the parent categories related to this diff id
          if( isset($_GET["another-make"]) ) {
            $part_category_results = $wpdb->get_results(
              $wpdb->prepare(
                "SELECT DISTINCT Parent, ParentID
                FROM randys_advancedsearch
                WHERE DiffID = %d",
                array( $diffID )
              )
            );
          } else {
            $item = '';
            $value = array($diffID);

            if( isset($_GET['diffyear']) ) {
              $item .= ' AND startyear <= %d AND endyear >= %d';
              array_push($value, $_GET["diffyear"], $_GET["diffyear"]);
            }

            if( isset($_GET['make']) ) {
              $item .= ' AND make = %s';
              array_push($value, $_GET['make']);
            }

            if( isset($_GET['model']) ) {
              $item .= ' AND model = %s';
              array_push($value, $_GET['model']);
            }

            if( isset($_GET['drivetype']) ) {
              $split_drivetype = explode(" Diff - ", $_GET['drivetype']);

              $item .= 'AND drivetype = %s  AND side = %s';
              array_push($value, $split_drivetype[1], $split_drivetype[0]);
            }

            $part_category_results = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT Parent, ParentID FROM randys_advancedsearch WHERE DiffID = %d " . $item . " ORDER BY Parent", $value ) ); // WPCS: unprepared SQL OK
          }
        ?>
        <div class="products-filter__form-item Parent unchanged">
          <label class="products-filter__label">Part Category</label>
          <div class="products-filter__select-wrap select">
            <select aria-label="Select Category" class="products-filter__select static" data-label="Parent" id="Parent" name="Parent">
              <option value="" selected="">ALL</option>
              <?php
                foreach ( $part_category_results as $category ) {
                  echo "<option value='" . $category->Parent . "'>" . $category->Parent . "</option>\n";
                }
              ?>
            </select>
          </div>
        </div>
        </div>
        <input type="hidden" name="product_filter_nonce" id="product_filter_nonce" value="<?php echo wp_create_nonce( 'product_filter_nonce' ); ?>" />
        <div id="current-filters" class="hide"></div>
    </div>
    <?php endif; // is_page('diff-wizard') ?>
  </div>
</div>

<?php } // isset( $_GET['diffid'])  ?>

<?php if( $productIDs ) { ?>
<div class="section <?php if(is_page('diff-wizard')) { echo 'm-t-3 section--lg-top'; } else { echo 'section--sm-top'; } ?>">
  <div class="container">
    <?php
      // Use locate_template so we can pass in results count from loop
      include( locate_template('templates/archive-header.php') );
    ?>
  </div>
  <div class="product-results"></div>
  <div class="text-center m-t-3">
    <a class="button button--ghost load-more-products" href="#">Load More</a>
    <div class="load-more-products__spinner spinner"></div>
  </div>
  <div class="total-pages hide"><?php echo $total_pages ?></div>
  <div id="current-ids" class="hide" data-query-ids=""></div>
</div>
<?php } // $productIDs ?>

<?php if( !$productIDs && is_page('diff-wizard') ) { ?>
  <div class="section <?php if(is_page('diff-wizard')) { echo 'm-t-3 section--lg-top'; } else { echo 'section--sm-top'; } ?>">
    <div class="container">
      <h3>No Results Found</h3>
    </div>
  </div>
<?php } // !$productIDs && is_page('diff-wizard')  ?>
