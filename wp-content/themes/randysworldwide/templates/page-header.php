<?php use Roots\Sage\Titles; ?>

<div class="page-header">
  <?php
    if ( is_cart() || is_checkout() ) {
      echo '<span class="float-right">Customer Service: 1-866-631-0196</span>';
    }
  ?>
  <h1><?= Titles\title(); ?></h1>
</div>
