<div class="section">
  <div class="container">
    <div class="row">
      <div class="col-sm-12 center-align">
        <h2><?= get_sub_field('headline'); ?></h2>
        <p><?= get_sub_field('body'); ?></p>
        <?php
        if ( have_rows('type') ) {
          while ( have_rows('type') ) { the_row();
            get_template_part('pagebuilder_modules/calculator/'.get_row_layout());
          }
        } else {
          // no layouts found
          echo 'No Modules Selected.';
        }
        ?>
        <small>RANDYS Worldwide makes no guarantee to the accuracy of calculations. Please email <a href="mailto:CustomerService@randysworldwide.com">CustomerService@randysworldwide.com</a> with any calculation errors.</small>
      </div>
    </div>
  </div>
</div>
