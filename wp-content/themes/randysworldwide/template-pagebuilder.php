<?php
/**
 * Template Name: Pagebuilder Template
 */

if ( class_exists('acf') ) {
  // Pagebuilder
  if ( have_rows('modules') ) {
    while ( have_rows('modules') ) { the_row();
      get_template_part('pagebuilder_modules/'.get_row_layout().'/'.get_row_layout());
    }
  } else {
    // no layouts found
    echo 'No Modules Selected.';
  }
}
