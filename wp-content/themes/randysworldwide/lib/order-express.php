<?php

function product_lookup() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        if (check_ajax_referer('product_lookup_nonce', 'nonce', false) === false || !isset($_POST['query']) || $_POST['query'] == "") {
            http_response_code(400);
            echo "Error 400: Bad Request. Reason: Invalid/Missing Information.";
            exit;
        }

        global $wpdb;
        $current_user = wp_get_current_user();

        $result = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT p2.ProductID, p2.Title, p2.ProductNumber, p2.Qty, p2.Price,
                    po.ID as post_id, (CASE WHEN wl.prod_id IS NOT NULL THEN 1 ELSE 0 END) as favorited
                    FROM randys_productsearch as p1
                    RIGHT JOIN randys_product as p2 ON p1.ProductID = p2.ProductID
                    RIGHT JOIN wp_postmeta as pm ON pm.meta_key = '_sku' AND CONVERT(pm.meta_value USING utf8mb4) = CONVERT(p1.ProductNumber USING utf8mb4)
                    RIGHT JOIN wp_posts as po ON po.ID = pm.post_id
                    LEFT JOIN wp_yith_wcwl as wl ON wl.prod_id = po.ID AND wl.user_id = %d
                    WHERE p1.Brand in(
                        SELECT b.BrandName FROM randys_brands as b
                        INNER JOIN randys_customerbrands as cb ON b.BrandID = cb.BrandID
                        INNER JOIN randys_customers as c ON cb.CustomerID = c.CUSTNMBR
                        WHERE c.WOOCustID = %d
                    )
                    AND p2.ProductNumber LIKE %s
                    ORDER BY p2.ProductNumber
                    LIMIT %d;",
                $current_user->ID, $current_user->ID, $_POST['query'] . "%", max($_POST['page_size'], 20)
            )
        );
        if (count($result) > 0) {
            echo json_encode($result);
        } else {
            echo json_encode(["error" => "The Part Number you entered is not valid. Please try again."]);
        }
    }
    die();
}

add_action("wp_ajax_product_lookup", "product_lookup");
add_action("wp_ajax_nopriv_product_lookup", "product_lookup");

function add_product_to_cart() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        if (check_ajax_referer('add_product_to_cart_nonce', 'nonce', false) === false ||
            !isset($_POST['product_sku']) || $_POST['product_sku'] == "" ||
            !isset($_POST['quantity']) || $_POST['quantity'] == "") {
            http_response_code(400);
            echo "Error 400: Bad Request. Reason: Invalid/Missing Information.";
            exit;
        }

        $product_sku = $_POST['product_sku'];
        $quantity = $_POST['quantity'];

        $product_id = wc_get_product_id_by_sku($product_sku);
        if ($product_id === 0) {
            echo json_encode(["error" => "Product not found"]);
            die();
        }

        $product = new WC_Product( $product_id );

        $stock = $product->get_stock_quantity();
        $cart_quantity = check_cart_quantity($product_id);

        if( ($quantity + $cart_quantity ) > $stock ) {
            echo json_encode(["error" => "You cannot add that amount to the cart - we have ".$stock." in stock and you already have ".$cart_quantity." in your cart. Adjust your quantity or call 1-866-631-0196 for back order."]);
            die();
        }

        echo json_encode(["url" => $product->add_to_cart_url(), "id" => $product->id]);
    }
    die();
}

add_action("wp_ajax_add_product_to_cart", "add_product_to_cart");
add_action("wp_ajax_nopriv_add_product_to_cart", "add_product_to_cart");

function check_cart_quantity($product_id) {
    global $woocommerce;
    foreach($woocommerce->cart->get_cart() as $key => $val ) {
        $_product = $val['data'];

        if($product_id == $_product->id ) {
            return $val['quantity'];
        }
    }

    return 0;
}

function add_product_to_whishlist() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        if (check_ajax_referer('add_product_to_whishlist_nonce', 'nonce', false) === false ||
            !isset($_POST['product_id']) || $_POST['product_id'] == "") {
            http_response_code(400);
            echo "Error 400: Bad Request. Reason: Invalid/Missing Information.";
            exit;
        }

        $current_user = wp_get_current_user();
        if ($current_user === null) {
            echo json_encode(["error" => "Need to logged in"]);
            die();
        }

        $whishlists = YITH_WCWL::get_instance()->get_wishlists();
        if (count($whishlists) == 0) {
            $whishlist_id = YITH_WCWL::get_instance()->generate_default_wishlist($current_user->ID);
        } else {
            $whishlist_id = $whishlists[0]['ID'];
        }

        YITH_WCWL::get_instance()->details = ["add_to_wishlist" => $_POST['product_id'], "wishlist_id" => $whishlist_id];
        YITH_WCWL::get_instance()->add();

        echo json_encode([]);
    }
    die();
}

add_action("wp_ajax_add_product_to_whishlist", "add_product_to_whishlist");
add_action("wp_ajax_nopriv_add_product_to_whishlist", "add_product_to_whishlist");
