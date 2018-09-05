<?php
add_action("wp_ajax_get_glossary", "get_glossary");
add_action("wp_ajax_nopriv_get_glossary", "get_glossary");

function get_glossary() {

  // Nonce Verification
  if (check_ajax_referer( 'glossary_nonce', 'nonce', false ) ) {
    $row = $_POST['row'];
    $page_id = $_POST['page_id'];
  }

  $rows = get_field('letter', $page_id);
  $glossary_row = $rows[$row]; // get the first row
  $glossary_row_terms = $glossary_row['terms'];

  echo $glossary_row_terms;

  die();

}
