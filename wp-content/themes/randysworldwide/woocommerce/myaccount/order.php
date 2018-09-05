<?php use Roots\Sage\RANDYS; ?>

<div class="back"><a href="/my-account"><i class="fa fa-arrow-left" aria-hidden="true"></i> My Account</a></div>
<?php
$order_id = explode('/', $_SERVER['REQUEST_URI'])[3];

global $wpdb;

$order = $wpdb->get_row(
  $wpdb->prepare("SELECT * FROM randys_orders WHERE randys_order_id = %s", $order_id)
);
?>

<div class="row">
  <div class="invoice-logo col-xs-3 col-sm-2">
    <img src="<?php echo get_template_directory_uri(); ?>/dist/images/RANDYS_logo_color.svg" alt="RANDYS Worldwide" class="img-fluid">
  </div>
  <div class="col-md-2 hidden-sm-down"></div>
  <div class="invoice-contacts col-xs-9 col-sm-5 col-md-4">
    <?php the_field('contact_info', 'options'); ?>
  </div>
  <div class="invoice-data col-xs-9 offset-xs-3 offset-sm-0 col-sm-5 col-md-4">
    Invoice: <?php echo $order->randys_order_id; ?><br>
    Date: <?php echo date_i18n( get_option( 'date_format' ), strtotime( $order->order_date ) ); ?><br>
    <input class="button button--short button--slim m-t-2" type="button" onClick="window.print()" value="Print"/>
  </div>
</div>

<div class="row addresses m-t-3 m-b-3">
  <div class="col-md-4">
    <header class="title">
      <h3><?php _e( 'Billing Address:', 'woocommerce' ); ?></h3>
    </header>
    <address>
      <?php
      if ( isset($order->billing_first_name) && '' !== $order->billing_first_name ) {
        echo esc_html( $order->billing_first_name ).' ';
      }
      if ( isset($order->billing_last_name) && '' !== $order->billing_last_name ) {
        echo esc_html( $order->billing_last_name );
      }
      if ( isset($order->billing_first_name) || isset($order->billing_last_name) ) {
        echo '<br>';
      }
      if ( isset($order->billing_company) && '' !== $order->billing_company ) {
        echo esc_html( $order->billing_company ).'<br>';
      }
      if ( isset($order->billing_address_1) && '' !== $order->billing_address_1 ) {
        echo esc_html( $order->billing_address_1 ).'<br>';
      }
      if ( isset($order->billing_address_2) && '' !== $order->billing_address_2 ) {
        echo esc_html( $order->billing_address_2 ).'<br>';
      }
      if ( isset($order->billing_city) && '' !== $order->billing_city ) {
        echo esc_html( $order->billing_city ).', ';
      }
      if ( isset($order->billing_state) && '' !== $order->billing_state ) {
        echo esc_html( $order->billing_state ).' ';
      }
      if ( isset($order->billing_postcode) && '' !== $order->billing_postcode ) {
        echo esc_html( $order->billing_postcode ).'<br>';
      }
      if ( isset($order->billing_country) && '' !== $order->billing_country ) {
        echo esc_html( $order->billing_country ).'<br>';
      }
      if ( isset($order->billing_phone) && '' !== $order->billing_phone ) {
        echo esc_html( $order->billing_phone ).'<br>';
      }
      if ( isset($order->billing_email) && '' !== $order->billing_email ) {
        echo esc_html( $order->billing_email );
      }
      ?>
    </address>
  </div>
  <div class="col-md-4">
    <header class="title">
      <h3><?php _e( 'Shipping Address:', 'woocommerce' ); ?></h3>
    </header>
    <address>
      <?php
      if ( isset($order->shipping_first_name) && '' !== $order->shipping_first_name ) {
        echo esc_html( $order->shipping_first_name ).' ';
      }
      if ( isset($order->shipping_last_name) && '' !== $order->shipping_last_name ) {
        echo esc_html( $order->shipping_last_name );
      }
      if ( isset($order->shipping_first_name) || isset($order->shipping_last_name) ) {
        echo '<br>';
      }
      if ( isset($order->shipping_company) && '' !== $order->shipping_company ) {
        echo esc_html( $order->shipping_company ).'<br>';
      }
      if ( isset($order->shipping_address_1) && '' !== $order->shipping_address_1 ) {
        echo esc_html( $order->shipping_address_1 ).'<br>';
      }
      if ( isset($order->shipping_address_2) && '' !== $order->shipping_address_2 ) {
        echo esc_html( $order->shipping_address_2 ).'<br>';
      }
      if ( isset($order->shipping_city) && '' !== $order->shipping_city ) {
        echo esc_html( $order->shipping_city ).', ';
      }
      if ( isset($order->shipping_state) && '' !== $order->shipping_state ) {
        echo esc_html( $order->shipping_state ).' ';
      }
      if ( isset($order->shipping_postcode) && '' !== $order->shipping_postcode ) {
        echo esc_html( $order->shipping_postcode ).'<br>';
      }
      if ( isset($order->shipping_country) && '' !== $order->shipping_country ) {
        echo esc_html( $order->shipping_country ).'<br>';
      }
      if ( isset($order->shipping_phone) && '' !== $order->shipping_phone) {
        echo esc_html( $order->shipping_phone ).'<br>';
      }
      if ( isset($order->shipping_email) && '' !== $order->shipping_email ) {
        echo esc_html( $order->shipping_email );
      }
      ?>
    </address>
  </div>
  <div class="col-md-4">
    <header class="title">
      <h3><?php _e( 'Contact:', 'woocommerce' ); ?></h3>
    </header>
    <table class="shop_table customer_details">
      <p>
      <?php if ( $order->billing_email ) :
        echo esc_html( $order->billing_email ).'<br>';
      endif; ?>

      <?php if ( $order->billing_phone ) :
          echo esc_html( $order->billing_phone );
      endif; ?>
      </p>
      <?php do_action( 'woocommerce_order_details_after_customer_details', $order ); ?>
    </table>
  </div>
</div>

<div class="woocommerce-thankyou-order-details order_details p-b-2 row">
  <div class="col-sm-3 m-b-3 customer">
    <h3 class="m-b-1"><?php _e( 'Customer', 'woocommerce' ); ?></h3>
    <?php echo $order->randys_customer_id; ?>
  </div>
  <div class="col-sm-3 m-b-3 shipping">
    <h3 class="m-b-1"><?php _e( 'Ship Via', 'woocommerce' ); ?></h3>
    <?php echo $order->shipping_method; ?>
  </div>
  <div class="col-sm-3 m-b-3 invoice">
    <h3 class="m-b-1"><?php _e( 'Invoice #', 'woocommerce' ); ?></h3>
    <?php echo $order->randys_order_id; ?>
  </div>
  <div class="col-sm-3 m-b-3 terms">
    <h3 class="m-b-1"><?php _e( 'Terms', 'woocommerce' ); ?></h3>
    <?php echo $order->payment_method; ?>
  </div>
  <hr>
  <div class="col-sm-3 date">
    <h3 class="m-b-1"><?php _e( 'Order Date', 'woocommerce' ); ?></h3>
    <?php echo date_i18n( get_option( 'date_format' ), strtotime( $order->order_date ) ); ?>
  </div>
  <div class="col-sm-3 purchase-order-number">
    <h3 class="m-b-1"><?php _e( 'Purchase Order #', 'woocommerce' ); ?></h3>
    <?php echo $order->po_num; ?>
  </div>
  <div class="col-sm-3 sales-person">
    <h3 class="m-b-1"><?php _e( 'Sales Person', 'woocommerce' ); ?></h3>
    <?php echo $order->sales_person; ?>
  </div>
  <div class="col-sm-3 original-order">
    <h3 class="m-b-1"><?php _e( 'Original Order', 'woocommerce' ); ?></h3>
    <?php echo $order->original_order; ?>
  </div>
</div>
</div>
</div>
</div>
<div class="section section--tan order-review-section">
  <div class="container">
    <?php if ( $notes = $order->notes ) : ?>
      <?php echo $order->notes; ?>
    <?php endif; ?>

    <h3><?php _e( 'Your Order', 'woocommerce' ); ?></h3>
    <div class="table-container">
      <table class="shop_table order_details">
        <thead>
          <tr>
            <th class="product-name"><?php _e( 'Item Description', 'woocommerce' ); ?></th>
            <th class="product-quantity"><?php _e( 'Qty', 'woocommerce' ); ?></th>
            <th class="product-total list-price"><?php _e( 'Item Weight', 'woocommerce' ); ?></th>
            <th class="product-total list-price"><?php _e( 'Total Weight', 'woocommerce' ); ?></th>
            <?php if ( RANDYS\is_wholesale() ) { ?>
              <th class="product-total list-price"><?php _e( 'List Price', 'woocommerce' ); ?></th>
              <th class="product-total your-price"><?php _e( 'Your Price', 'woocommerce' ); ?></th>
            <?php } else { ?>
              <th></th>
              <th class="product-total your-price"><?php _e( 'Price', 'woocommerce' ); ?></th>
            <?php } ?>
            <th class="product-total extended-price"><?php _e( 'Extended Price', 'woocommerce' ); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php
            // Get order items
            $order_items = $wpdb->get_results(
              $wpdb->prepare("SELECT * FROM randys_order_items WHERE randys_order_id = %s", $order_id)
            );

            $order_weight = '';

            foreach( $order_items as $order_item => $value ) {
              $order_weight = $order_weight + ($value->weight * $value->qty);

              ?>
              <tr class="order_item">
                <td><?php echo $value->item_description; ?></td>
                <td><?php echo $value->qty; ?></td>
                <td><?php echo $value->weight; ?></td>
                <td><?php echo $value->weight * $value->qty; ?></td>
                <td><span class="woocommerce-Price-amount amount"><?php echo wc_price($value->list_price); ?></span></td>
                <td><span class="woocommerce-Price-amount amount"><?php echo wc_price($value->paid_price); ?></span></td>
                <td><span class="woocommerce-Price-amount amount"><?php echo wc_price($value->subtotal); ?></span></td>
              </tr>
            <?php }
            if ( '0.00' !== $order->dropship && null !== $order->dropship ) { ?>
              <tr class="order_item">
                <td>Drop Ship Charge</td>
                <td></td>
                <td></td>
                <td></td>
                <td><?php echo wc_price(0.00); ?></td>
                <td><?php echo wc_price($order->dropship); ?></td>
                <td><?php echo wc_price($order->dropship); ?></td>
              </tr>
            <?php }
          ?>
        </tbody>
        <tfoot>
          <tr>
            <th scope="row">Total Shipping Weight (lbs):</th>
            <td></td>
            <td></td>
            <td><?php echo $order_weight; ?></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <th scope="row">Subtotal:</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><?php echo wc_price($order->order_total - $order->shipping_total - $order->tax_total); ?></td>
          </tr>
          <tr>
            <th scope="row">Shipping &amp; Handling:</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><?php echo wc_price($order->shipping_total); ?></td>
          </tr>
          <tr>
            <th scope="row">Tax:</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><?php echo wc_price($order->tax_total); ?></td>
          </tr>
          <tr>
            <th scope="row">Total:</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><?php echo wc_price($order->order_total); ?></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <h4 class="m-t-3">Verify all parts &amp; ratios are correct prior to installation.</h4>
    <p><span class="underline">Call for a Return Authorization Number.</span> Only Authorized Returns Will Be Accepted!<p>
    <p>All returns must be sent to:</p>
    <p>RANDYS Worldwide<br> 10411 Airport Road<br> Everett, WA 98204</p>

  </div>
</div>
<div class="section">
  <div class="container">
    <h4>ALL ITEMS ARE WARRANTIED BY THEIR RESPECTIVE MANUFACTURERS, NOT BY RANDYS Worldwide!</h4>
    <p>Rebuilt items are warranted for 3 months against defects in workmanship; not against abuse, loading, or improper lubrication. All parts must be shipped prepaid freight to our shop for our inspection and determination. We do not authorize, nor will we pay for, outside repairs. ANY AUTHORIZED OUTSIDE REPAIR VOIDS THIS WARRANTY. We will not pay for labor, loss of revenue or perishable goods, commercial losses, costs of telephone calls or general inconvenience. This is our only warranty expressed or implied. All goods must be returned within 30 days, and must be accompanied by this bill. A restock fee of 15% or more will be charged on all authorized returns. Unauthorized returns may be refused, or accepted subject to a 25% or larger restock fee.</p>
  </div>
</div>
