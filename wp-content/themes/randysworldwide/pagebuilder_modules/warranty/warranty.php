<?php
$warranty_title = get_sub_field('title');
$warranty_description = get_sub_field('warranty_description');
$warranty_link = get_sub_field('warranty_link');
$warranty_type = get_sub_field('warranty_type');

echo get_warranty($warranty_type, $warranty_title, $warranty_description, $warranty_link);
