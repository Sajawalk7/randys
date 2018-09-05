<?php
  /*
  ** Diff Wizard Results page
  */
  use Roots\Sage\Extras;

  // If we don't have a diff id selected yet, output diffs and filters
  if( !isset($_GET['diffid'])  ) {

  get_template_part('templates/browsing-diffwizard-shared/filtering-section');

  // We have the diff id, lets output the results
  } else {

  get_template_part('templates/browsing-diffwizard-shared/results-section');

 }
