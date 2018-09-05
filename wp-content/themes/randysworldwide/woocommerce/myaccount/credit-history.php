<div class="back"><a href="/my-account"><i class="fa fa-arrow-left" aria-hidden="true"></i> My Account</a></div>
<?php
use Roots\Sage\RANDYS;

$orders = RANDYS\returned_orders();
?>
<h2>Credit History</h2>
<div class="row order order--header">
  <div class="col-xs-3">INVOICE #</div>
  <div class="col-xs-3">INVOICE DATE</div>
  <div class="col-xs-3">TOTAL</div>
  <div class="col-xs-3">PO #</div>
</div>
<?php foreach ( $orders as $order ) { ?>
  <div class="row order">
    <div class="col-xs-3"><?php echo $order->randys_order_id; ?></div>
    <div class="col-xs-3"><?php echo explode(' ', $order->order_date)[0]; ?></div>
    <div class="col-xs-3"><?php echo '$'.$order->refunded_total; ?></div>
    <div class="col-xs-3"><?php echo $order->po_num; ?></div>
  </div>
<?php }
