<?php
  // Setup query for diff-wizard
  global $wpdb;

  $parent_query_where = '';
  $parent_query_data = '';
  $parent_input_field = '';
  $filter_title = 'Filter to View Product Parts';

  /**
   ** Product Category Page specific
   */
  if( is_product_category() ) {
    $cat_name = html_entity_decode(single_cat_title('', false));
    $filter_title = 'Find ' . $cat_name . ' for Your Vehicle & Fitment';
    // If we are on a product category page lets get the id to query
    $get_parent_id = $wpdb->get_row(
      $wpdb->prepare(
        "SELECT DISTINCT CategoryID FROM randys_category WHERE CategoryName = %s",
        $cat_name
      )
    );

    $parent_input_field = '<input value="' . $get_parent_id->CategoryID . '" name="parent-id" id="parent-id" type="hidden" />';

    // Appened Parent ID Where data
    if( isset($_GET['cat-year']) ) {
      $parent_query_where = ' AND ParentID = %d';
      $parent_query_data = $get_parent_id->CategoryID;
    } else {
      $parent_query_where = ' WHERE ParentID = %d';
      $parent_query_data = $get_parent_id->CategoryID;
    }

    // Certain categories such as "Team Gear don't have Year, Make, Model, and Drivetype values
    // For these we want to skip straight to category and populate it
    if( !isset($_GET['cat-year']) || $_GET['cat-year'] === '' ) {
      $get_all_cats = $wpdb->get_results(
        $wpdb->prepare(
          "SELECT DISTINCT CategoryName FROM randys_category WHERE ParentID = %d",
          $get_parent_id->CategoryID
        )
      );
    }
  }

  /**
   ** Pre-populate Make
   */
  if( is_page_template('template-product-browsing-pagebuilder.php')) {
    // Get the make selected
    $post_id = get_the_ID();
    $make_selected = get_post_meta($post_id, 'meta-box-dropdown', true);

  }

  if( isset($make_selected) && $make_selected !== '' ) {
    $year_results = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT DISTINCT StartYear, EndYear FROM randys_advancedsearch WHERE Make = %s",
        $make_selected
      )
    );
    foreach( $year_results as $years ) {
      for ($i = $years->StartYear; $i <= $years->EndYear; $i++) {
        $actual_years[$i] = 1;
      }
    }
    $year_results = array_keys($actual_years);
    rsort($year_results);
  } elseif( $parent_query_data === '' ) {
    $year_results = $wpdb->get_row(
      "SELECT MIN(startyear), MAX(endyear)  FROM randys_advancedsearch"
    );
  } else {
    $year_results = $wpdb->get_row(
      $wpdb->prepare(
        "SELECT MIN(startyear), MAX(endyear)  FROM randys_advancedsearch WHERE ParentID = %d",
        $parent_query_data
      )
    );
  }

  // If cat year is set, run a query to get available makes
  if( isset($_GET['cat-year']) ) {
    $query = $wpdb->prepare("SELECT DISTINCT Make FROM randys_advancedsearch WHERE startyear <= %d AND endyear >= %d" . $parent_query_where, array($_GET['cat-year'], $_GET['cat-year'], $parent_query_data)); // WPCS: unprepared SQL OK
    $make_results = $wpdb->get_results($query); // WPCS: unprepared SQL OK
  }

  // If cat year + make is set, run a query to get available model
  if( isset($_GET['cat-year']) && isset($_GET['cat-make']) ) {
    $query = $wpdb->prepare("SELECT DISTINCT Model FROM randys_advancedsearch WHERE startyear <= %d AND endyear >= %d AND Make = %s" . $parent_query_where, array($_GET['cat-year'], $_GET['cat-year'], $_GET['cat-make'], $parent_query_data)); // WPCS: unprepared SQL OK
    $model_results = $wpdb->get_results($query); // WPCS: unprepared SQL OK
  }

  // If we have all inputs, run a query to get available drive types
  if( isset($_GET['cat-year']) && isset($_GET['cat-make']) && isset($_GET['cat-model']) ) {
    $query = $wpdb->prepare("SELECT DISTINCT CONCAT(side, ' Diff - ', drivetype) FROM randys_advancedsearch WHERE startyear <= %d AND endyear >= %d AND Make = %s AND Model = %s" . $parent_query_where, array($_GET['cat-year'], $_GET['cat-year'], $_GET['cat-make'], $_GET['cat-model'], $parent_query_data)); // WPCS: unprepared SQL OK
    $drivetype_results = $wpdb->get_results($query); // WPCS: unprepared SQL OK
  }

  // Get a list of all products
  if( isset($_GET['cat-year']) && $_GET['cat-year'] !== '' && isset($_GET['cat-make']) && isset($_GET['cat-model']) && isset($_GET['cat-drivetype']) ) {
    if( $parent_query_where ) {
      $parent_query_where = 'AND ParentID = %d';
    }
    $query = $wpdb->prepare("SELECT DISTINCT category AS 'CategoryName' FROM randys_advancedsearch WHERE startyear <= %d AND endyear >= %d AND make = %s AND model = %s AND CONCAT(side, ' Diff - ', drivetype) = %s " . $parent_query_where . " ORDER BY category ASC", array($_GET['cat-year'], $_GET['cat-year'], $_GET['cat-make'], $_GET['cat-model'], $_GET['cat-drivetype'], $parent_query_data)); // WPCS: unprepared SQL OK
    $get_all_cats = $wpdb->get_results($query); // WPCS: unprepared SQL OK
  }
?>
<div class="products-filter products-filter--browsing<?php if( isset($make_selected) && $make_selected !== '' ) { ?> products-filter--browsing-four<?php } ?>">
  <div class="row">
    <h3 class="products-filter__title"><?php echo $filter_title; ?></h3>
  </div>
  <form action='' class="products-filter__form row justify-content-between">
  <?php
    $min_query = "MIN(startyear)";
    $max_query = "MAX(endyear)";
    // If result doesn't have a min and max year we need to jump straight to category selection
    if( is_product_category() &&
        $year_results->$min_query === null &&
        $year_results->$max_query === null ) {
  ?>
      <div class="products-filter__form-item">
        <span class="select products-filter__select">
          <select aria-label="Filter by Year" id="cat-year" class="products-filter-dropdown" name="cat-year">
            <option value="" disabled>Year</option>
            <option value="" selected>All Years</option>
          </select>
        </span>
      </div>
      <div class="products-filter__form-item">
        <span class="select products-filter__select">
          <select aria-label="Filter by Make" id="cat-make" class="products-filter-dropdown" name="cat-make">
            <option value="" disabled>Make</option>
            <option value="" selected>All Makes</option>
          </select>
        </span>
      </div>
      <div class="products-filter__form-item">
        <span class="select products-filter__select">
          <select aria-label="Filter by Model" id="cat-model" class="products-filter-dropdown" name="cat-model">
            <option value="" disabled>Model</option>
            <option value="" selected>All Model</option>
          </select>
        </span>
      </div>
      <div class="products-filter__form-item">
        <span class="select products-filter__select">
          <select aria-label="Filter by Drivetype" id="cat-drivetype" class="products-filter-dropdown" name="cat-drivetype">
            <option value="" disabled>Drivetype</option>
            <option value="" selected>All Drivetypes</option>
          </select>
        </span>
      </div>
      <div class="products-filter__form-item">
        <span class="select products-filter__select">
          <select aria-label="Filter by Category" id="cat-category" name="cat-category" class="active">
            <option value="" disabled selected>Category</option>
            <?php
              if( isset($get_all_cats) ) {
                // Loop through all product categories and place in selected value
                foreach( $get_all_cats as $cat ):
                  if ( ( isset($_GET['cat-category']) && $_GET['cat-category'] === $cat->CategoryName ) || count($get_all_cats) === 1 ) {
                    echo '<option value="' . $cat->CategoryName . '" selected>' . $cat->CategoryName . '</option>';
                  } else {
                    echo '<option value="' . $cat->CategoryName . '">' . $cat->CategoryName . '</option>';
                  }
                endforeach;
              }
              ?>
          </select>
        </span>
      </div>
      <input type="submit" value="GO" class="products-filter__submit button button--sm-height button--slim">
    <?php } else { ?>
    <div class="products-filter__form-item">
       <span class="select products-filter__select">
        <select aria-label="Filter by Year" id="cat-year" class="products-filter-dropdown" name="cat-year">
          <option value="" disabled selected>Year</option>
          <?php
            if(isset($make_selected) && $make_selected !== '') {
              foreach($year_results as $year) {
                if( isset($_GET['cat-year']) && $_GET['cat-year'] === (string)$year ) {
                  echo "<option value='" . $year . "' selected>" . $year . "</option>\n";
                } else {
                  echo "<option value='" . $year . "'>" . $year . "</option>\n";
                }
              }
            } else {
              for ($i = $year_results->$max_query; $i >= $year_results->$min_query ; $i--) {
                if( isset($_GET['cat-year']) && $_GET['cat-year'] === (string)$i ) {
                  echo "<option value='" . $i . "' selected>" . $i . "</option>\n";
                } else {
                  echo "<option value='" . $i . "'>" . $i . "</option>\n";
                }
              }
            }
          ?>
        </select>
      </span>
    </div>
    <div class="products-filter__form-item <?php if( isset($make_selected) && $make_selected !== '' ) { echo ' products-filter__pre-populated hide'; } ?>">
      <span class="select select--disabled products-filter__select">
        <div class="spinner spinner--input"></div>
        <select aria-label="Filter by Make" id="cat-make" class="products-filter-dropdown" name="cat-make" disabled>
          <option value="" disabled selected>Make</option>
          <?php
            if( isset($make_selected) && $make_selected !== '' ) {
              echo "<option value='" . $make_selected . "' selected>" . $make_selected . "</option>\n";
            } elseif( isset( $make_results ) ) {
              foreach ( $make_results as $make ) {
                if( isset($_GET['cat-make']) && $_GET['cat-make'] === (string)$make->Make ) {
                  echo "<option value='" . $make->Make . "' selected>" . $make->Make . "</option>\n";
                } else {
                  echo "<option value='" . $make->Make . "'>" . $make->Make . "</option>\n";
                }
              }
            }
          ?>
        </select>
      </span>
    </div>
    <div class="products-filter__form-item">
      <span class="select select--disabled products-filter__select">
        <div class="spinner spinner--input"></div>
        <select aria-label="Filter by Model" id="cat-model" class="products-filter-dropdown" name="cat-model" disabled>
          <option value="" disabled selected>Model</option>
          <?php
            if( isset( $model_results ) ) {
              foreach ( $model_results as $model ) {
                if( isset($_GET['cat-model']) && $_GET['cat-model'] === (string)$model->Model ) {
                  echo "<option value='" . $model->Model . "' selected>" . $model->Model . "</option>\n";
                } else {
                  echo "<option value='" . $model->Model . "'>" . $model->Model . "</option>\n";
                }
              }
            }
          ?>
        </select>
      </span>
    </div>
    <div class="products-filter__form-item">
      <span class="select select--disabled products-filter__select">
        <div class="spinner spinner--input"></div>
        <select aria-label="Filter by Drivetype" id="cat-drivetype" class="products-filter-dropdown" name="cat-drivetype" disabled>
          <option value="" disabled selected>Drive Type</option>
          <?php
            if( isset( $drivetype_results ) ) {
              foreach ( $drivetype_results as $drivetype ) {
                $drivetype_key = "CONCAT(side, ' Diff - ', drivetype)";
                if( isset($_GET['cat-drivetype']) && $_GET['cat-drivetype'] === (string)$drivetype->$drivetype_key ) {
                  echo "<option value='" . $drivetype->$drivetype_key  . "' selected>" . $drivetype->$drivetype_key  . "</option>\n";
                } else {
                  echo "<option value='" . $drivetype->$drivetype_key  . "'>" . $drivetype->$drivetype_key  . "</option>\n";
                }
              }
            }
          ?>
        </select>
      </span>
    </div>
    <div class="products-filter__form-item">
      <span class="select select--disabled products-filter__select">
        <div class="spinner spinner--input"></div>
        <select aria-label="Filter by Category" id="cat-category" name="cat-category" class="products-filter-dropdown" disabled>
          <option value="" disabled selected>Category</option>
          <?php
            if( isset($get_all_cats) ) {
              // Loop through all product categories and place in selected value
              foreach( $get_all_cats as $cat ):
                if( isset($_GET['cat-category']) &&  $_GET['cat-category'] === $cat->CategoryName) {
                  echo '<option value="' . $cat->CategoryName . '" selected>' . $cat->CategoryName . '</option>';
                } else {
                  echo '<option value="' . $cat->CategoryName . '">' . $cat->CategoryName . '</option>';
                }
              endforeach;
            }
            ?>
        </select>
      </span>
    </div>
    <input type="submit" value="GO" class="products-filter__submit button button--sm-height button--slim" disabled>
    <?php } ?>
    <?php echo $parent_input_field; ?>
  </form>
</div>
