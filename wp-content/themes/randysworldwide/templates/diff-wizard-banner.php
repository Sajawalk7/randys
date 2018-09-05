<div class="diffwizard diffwizard--controls diffwizard--banner">
  <div class="container diffwizard__flex-mobile">
    <img src="<?= get_template_directory_uri(); ?>/dist/images/diff_wizard_logo.svg" alt="Diff Wizard" class="diffwizard__logo">
    <?php require_once('diff-wizard-form.php'); ?>
  </div>
  <?php if( is_page('diff-wizard') ): ?>
    <div class="diffwizard__m-toggle">
      <i class="diffwizard__m-expand fa fa-plus hidden-md-up"></i>
      <div class="diffwizard__m-collapse">
        Close <i class="fa fa-times hidden-md-up"></i>
      </div>
    </div>
  <?php endif; ?>
</div>
