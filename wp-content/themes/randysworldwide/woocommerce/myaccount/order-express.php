<div class="row">
    <div class="col-xs-12">
        <h2>Order Express</h2>
        <div class="back"><a href="/my-account"><i class="fa fa-arrow-left" aria-hidden="true"></i> My Account</a></div>
        <p>When you know what you need, get it quick with Order Express! Simply populate your part number and quantity and instantly your part is added to your cart. You can view your cart at anytime to check your order. Shop between Diff Wizard and the RANDYS site to make sure the right parts are chosen every time.</p>

        <p>In the case that a product is unavailable, Order Express will show that once the product number is entered.</p>
    </div>

    <div class="col-md-12">
      <div id="order-express-holder">
        <div id="order-express-header">
          <div class="row">
            <div class="col-xs-1">In Cart</div>
            <div class="col-xs-2">Part #</div>
            <div class="col-xs-1">QTY</div>
            <div class="col-xs-1">Price</div>
            <div class="col-xs-4">Description</div>
            <div class="col-xs-2">Availability</div>
            <div class="col-xs-1">Wishlist</div>
          </div>
        </div>

        <!-- Product that already added to cart -->
        <div id="order-express-added-to-cart" class="col-md-12 row">
        </div>

        <!-- Adding product -->
        <div id="order-express-adding-to-cart" class="col-md-12 row">
            <div class="col-md-12 row  no-margin no-padding">
                <div class="col-xs-1 text-center">
                    <a class="button button--short-blue button--slim" id="button-add-product">ADD</a>
                </div>
                <div class="col-xs-2" style="position: relative">
                    <input aria-label="Product Number" type="text" id="adding-product-number" data-provide="typeahead">
                    <i class="fa fa-spin fa-spinner" aria-hidden="true" id="lookup-spinner"></i>
                </div>
                <div class="col-xs-1">
                    <input aria-label="Product Quantity" type="number" id="adding-product-quantity" class="text-right" step="1" min="1" readonly>
                </div>
                <div class="col-xs-1 text-right"><span id="adding-product-price"></span></div>
                <div class="col-xs-4"><span id="adding-product-description"></span></div>
                <div class="col-xs-2 text-center"><span id="adding-product-availability"></span></div>
                <div class="col-xs-1 text-center">
                    <img alt="" id="adding-product-added-to-whishlist" src='/wp-content/themes/randysworldwide/dist/images/icon_green_checkmark.png'>
                    <a id="adding-product-adding-to-whishlist" style="cursor: pointer">
                        <img alt="Add to Wishlist" src='/wp-content/themes/randysworldwide/dist/images/icon_add_to_whishlist.png'>
                    </a>
                </div>
            </div>
        </div>
      </div>
    </div>

    <div class="col-xs-12">
        <br/><br/>
    </div>

    <div class="col-xs-12">
        <a class="button button--short-blue button--slim" href="/cart">VIEW CART</a>
        <a class="button button--short-blue button--slim" href="/checkout">QUICK SHOP</a>
    </div>

    <div class="col-xs-12">
        <br/><br/>
    </div>
</div>
<input type="hidden" id="product_lookup_nonce" value="<?php echo wp_create_nonce("product_lookup_nonce"); ?>">
<input type="hidden" id="add_product_to_cart_nonce" value="<?php echo wp_create_nonce("add_product_to_cart_nonce"); ?>">
<input type="hidden" id="add_product_to_whishlist_nonce" value="<?php echo wp_create_nonce("add_product_to_whishlist_nonce"); ?>">

<div class="modal fade" id="loading-dialog">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body" style="padding: 20px; text-align: center">
                <i class="fa fa-spin fa-spinner" aria-hidden="true" style="font-size: medium"></i> Processing... Please wait
            </div>
        </div>
    </div>
</div>
