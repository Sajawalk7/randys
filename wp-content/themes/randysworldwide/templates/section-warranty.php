<?php
  // ACF Fields
  $warranty_title = get_field('warranty_title');
  $warranty_description = get_field('warranty_description');
  $warranty_link = get_field('warranty_link');
  $warranty_product = get_field('warranty_type');

  echo get_warranty($warranty_product, $warranty_title, $warranty_description, $warranty_link);
