<div class="section section--tan">
  <div class="container">

    <?php get_template_part('templates/page', 'header'); ?>

    <div class="alert alert-warning">
      <?php _e('Sorry, but the page you are trying to view can not be found. Please return to our homepage, use the Diff Wizard parts finder above, or try using one of the below resources.', 'sage'); ?>
    </div>
	<div class="custom-not-found">
		<?php dynamic_sidebar( '404' ); ?>
	</div>

    <?php get_search_form(); ?>

  </div>
</div>
