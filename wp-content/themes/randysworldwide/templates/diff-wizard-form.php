<?php
  // Setup query for diff-wizard
  global $wpdb;

  $year_results = $wpdb->get_results(
    "SELECT MIN(startyear), MAX(endyear) FROM randys_advancedsearch"
  );

  // If diff year is set, run a query to get available makes
  if( isset($_GET['diffyear']) ) {
    $make_results = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT DISTINCT Make FROM randys_advancedsearch WHERE startyear <= %d AND endyear >= %d ORDER BY Make",
        array($_GET['diffyear'], $_GET['diffyear'])
      )
    );
  }

  // If diff year + make is set, run a query to get available model
  if( isset($_GET['diffyear']) && isset($_GET['make']) ) {
    $model_results = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT DISTINCT Model FROM randys_advancedsearch WHERE startyear <= %d AND endyear >= %d AND Make = %s ORDER BY Model",
        array($_GET['diffyear'], $_GET['diffyear'], $_GET['make'])
      )
    );
  }
  // If we have all inputs, run a query to get available drive types
  if( isset($_GET['diffyear']) && isset($_GET['make']) && isset($_GET['model']) ) {
    $drivetype_results = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT DISTINCT CONCAT(side, ' Diff - ', drivetype) FROM randys_advancedsearch WHERE startyear <= %d AND endyear >= %d AND Make = %s AND Model = %s ORDER BY CONCAT(side, ' Diff - ', drivetype)",
        array($_GET['diffyear'], $_GET['diffyear'], $_GET['make'], $_GET['model'])
      )
    );
  }

?>
<form action='/diff-wizard/' class="diffwizard__form">
  <span class="select diffwizard__select">
    <select aria-label="Select Year" id="year" class="diffwizard-dropdown" name="diffyear">
      <option value="" disabled selected>Year</option>
      <?php
        $min_query = "MIN(startyear)";
        $max_query = "MAX(endyear)";
        if( $year_results[0]->$max_query && $year_results[0]->$min_query ) {
          for ($i = $year_results[0]->$max_query; $i >= $year_results[0]->$min_query; $i--) {
            if( isset($_GET['diffyear']) && $_GET['diffyear'] === (string)$i ) {
              echo "<option value='" . $i . "' selected>" . $i . "</option>\n";
            } else {
              echo "<option value='" . $i . "'>" . $i . "</option>\n";
            }
          }
        }
      ?>
    </select>
  </span>
  <span class="select select--disabled diffwizard__select diffwizard__select--m-collapse">
    <div class="spinner spinner--input"></div>
    <select aria-label="Select Make" id="make" class="diffwizard-dropdown" name="make">
      <option value="" disabled selected>Make</option>
      <?php
        if( isset( $make_results ) ) {
          foreach ( $make_results as $make ) {
            if( isset($_GET['make']) && $_GET['make'] === (string)$make->Make ) {
              echo "<option value='" . $make->Make . "' selected>" . $make->Make . "</option>\n";
            } else {
              echo "<option value='" . $make->Make . "'>" . $make->Make . "</option>\n";
            }
          }
        }
      ?>
    </select>
  </span>
  <span class="select select--disabled diffwizard__select diffwizard__select--m-collapse">
    <div class="spinner spinner--input"></div>
    <select aria-label="Select Model" id="model" class="diffwizard-dropdown" name="model">
      <option value="" disabled selected>Model</option>
      <?php
        if( isset( $model_results ) ) {
          foreach ( $model_results as $model ) {
            if( isset($_GET['model']) && $_GET['model'] === (string)$model->Model ) {
              echo "<option value='" . $model->Model . "' selected>" . $model->Model . "</option>\n";
            } else {
              echo "<option value='" . $model->Model . "'>" . $model->Model . "</option>\n";
            }
          }
        }
      ?>
    </select>
  </span>
  <span class="select select--disabled diffwizard__select diffwizard__select--m-collapse">
    <div class="spinner spinner--input"></div>
    <select aria-label="Select Drive Type" id="drivetype" class="diffwizard-dropdown" name="drivetype" disabled>
      <option value="" disabled selected>Drive Type</option>
      <?php
        if( isset( $drivetype_results ) ) {
          foreach ( $drivetype_results as $drivetype ) {
            $drivetype_key = "CONCAT(side, ' Diff - ', drivetype)";
            if( isset($_GET['drivetype']) && $_GET['drivetype'] === (string)$drivetype->$drivetype_key ) {
              echo "<option value='" . $drivetype->$drivetype_key  . "' selected>" . $drivetype->$drivetype_key  . "</option>\n";
            } else {
              echo "<option value='" . $drivetype->$drivetype_key  . "'>" . $drivetype->$drivetype_key  . "</option>\n";
            }
          }
        }
      ?>
    </select>
  </span>
  <input type="submit" value="GO" class="diffwizard__submit button button--short-blue button--slim diffwizard__select--m-collapse" disabled>
</form>
