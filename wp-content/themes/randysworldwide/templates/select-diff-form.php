<form class="select-another-diff">
  <div class="select-another-diff__title">or choose a differential</div>
  <?php
    $get_all_makes = $wpdb->get_results(
      "SELECT DISTINCT make FROM randys_advancedsearch WHERE make IS NOT NULL AND make != 'NULL' ORDER BY make"
    );
  ?>
  <span class="select select-another-diff__select m-b-2">
    <select aria-label="Select Make" id="another-make" class="select-another-diff-dropdown select-another-diff-dropdown--make" name="another-make">
      <option value="" disabled selected>Make</option>
      <?php
        if( isset( $get_all_makes ) ) {
          foreach ( $get_all_makes as $make ) {
            echo "<option value='" . $make->make . "'>" . $make->make . "</option>\n";
          }
        }
      ?>
    </select>
  </span>
  <span class="select select--disabled select-another-diff__select m-b-2">
    <div class="spinner spinner--input"></div>
    <select aria-label="Select Differential" id="differential" class="select-another-diff-dropdown select-another-diff-dropdown--differential" name="differential" disabled="">
      <option value="" disabled="" selected="">Differential</option>
    </select>
  </span>
  <input type="hidden" name="another_diff_nonce" id="another_diff_nonce" value="<?php echo wp_create_nonce( 'another_diff_nonce' ); ?>" />
  <input type="submit" value="GO" class="select-another-diff__submit button button--sm-height button--slim" disabled="">
</form>
