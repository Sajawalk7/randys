<div class="section section--tan section--lg-top">
  <div class="container">
    <?php if(!isset($_GET['diffyear']) && is_page('diff-wizard')): ?>
      <div class="row justify-content-center">
        <div class="col-sm-6">
          <?php include( locate_template('templates/select-diff-form.php') ); ?>
        </div>
      </div>
    <?php else: ?>
      <div class="row diffwizard-results">
        <div class="extra-filters-results col-lg-6">
          <div id="extra-filters">
          </div>
          <div id="current-filters" class="m-b-3">
            <div class="center-align">
              <a class='diffwizard--current-filter--remove button button--ghost m-b-2'>Reset Filter</a>
            </div>
          </div>
        </div>
        <div id="differential-list">
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>
