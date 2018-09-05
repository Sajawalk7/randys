<?php
$catalog = get_field('pdf_upload', 'options');
?>
<div class="back"><a href="/my-account"><i class="fa fa-arrow-left" aria-hidden="true"></i> My Account</a></div>
<h3>Banners are customized to your needs. Fill out the form below to order.</h3>
<?php if ($catalog) {
  echo '<a href="'.$catalog.'" target="_blank"><img src="'.get_site_url().'/wp-includes/images/media/document.png" alt="Download pdf"> View Banners (PDF)</a>';
} else {
  echo '<p>No PDF uploaded yet to review.</p>';
} ?>
<hr class="m-t-3 m-b-3">
<?php
gravity_form(5, false, false);
