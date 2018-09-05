<?php
/**
 * Repoprts
 *
 * This template will display various reports
 * based on user access levels.
 */

use Roots\Sage\RANDYS;

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}
?>
  <div class="col-sm-4">
    <header class="title">
      <h3>Reports</h3>
    </header>
    <ul>
      <?php if ( 1 === Customer_Access::current_class() && 1 === Customer_Access::current_relationship() ) { ?>
        <li><a href="/my-account/order-history/?level=1">National Account Order History &amp; Shipment Tracking</a></li>
        <li><a href="/my-account/order-history/?level=2">Subsidiary Order History &amp; Shipment Tracking</a></li>
      <?php } else { ?>
        <li><a href="/my-account/order-history/">Order History &amp; Shipment Tracking</a></li>
      <?php }
      if ( 1 === Customer_Access::current_class() && RANDYS\returned_orders() ) { ?>
        <li><a href="/my-account/credit-history">Credit History</a></li>
    <?php } ?>
    </ul>
  </div>

<?php
