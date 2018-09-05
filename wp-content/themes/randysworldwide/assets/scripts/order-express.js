jQuery(function ($) {
    var URL = wpAjax.ajax_url;
    var modal = null;
    var selected_product = null;
    var added_products = [];
    var check_mark = "<img src='/wp-content/themes/randysworldwide/dist/images/icon_green_checkmark.png'>";

    function add_to_whishlist(product_id) {
        modal.modal('show');
        $.ajax({
            method: 'post',
            url : URL,
            data : {
                action: 'add_product_to_whishlist',
                product_id: product_id,
                nonce: $('#add_product_to_whishlist_nonce').val()
            },
            success: function(response) {
                modal.modal('hide');
                if (selected_product !== null && product_id === selected_product.post_id) {
                    $('#adding-product-added-to-whishlist').css('display', 'inline');
                    $('#adding-product-adding-to-whishlist').css('display', 'none');
                    selected_product.favorited = "1";
                } else {
                    $("a[name='added-product-adding-to-whishlist']").each(function (index) {
                        if ($(this).attr('data-id') === product_id) {
                            $(this).replaceWith(check_mark);
                        }
                    });
                }
            },
            error: function(jqXHR, textStatus, error) {
                modal.modal('hide');
                alert("Something went wrong, please try again.");
            }
        });
    }

    function add_added_product(product, quantity) {
        var whishlist = "";
        if (parseInt(product.favorited) === 1) {
            whishlist = check_mark;
        } else {
            whishlist = "<a name='added-product-adding-to-whishlist' data-id='" + product.post_id + "' style='cursor: pointer'>" +
                        "<img src='/wp-content/themes/randysworldwide/dist/images/icon_add_to_whishlist.png'></a>";
        }

        var html = "<div class='col-md-12 row no-margin no-padding'>" +
                    "<div class='col-xs-1 text-center'><img src='/wp-content/themes/randysworldwide/dist/images/icon_green_checkmark.png'></div>" +
                    "<div class='col-xs-2'><input type='text' readonly value='" + product.ProductNumber + "'></div>" +
                    "<div class='col-xs-1'><input type='text' readonly value='" + quantity + "' class='text-right'></div>" +
                    "<div class='col-xs-1 text-right'>$" + product.Price + "</div>" +
                    "<div class='col-xs-4'>" + product.Title + "</div>" +
                    "<div class='col-xs-2 text-center text-success'>Available</div>" +
                    "<div class='col-xs-1 text-center'>" + whishlist + "</div>" +
                    "</div>";
        $('#order-express-added-to-cart').append(html);
        added_products.push(product);

        $("a[name='added-product-adding-to-whishlist']").on('click', function () {
            add_to_whishlist($(this).attr('data-id'));
        });
    }

    function loading(finish) {
        if (finish) {
            $('#lookup-spinner').css('display', 'none');
        } else {
            $('#lookup-spinner').css('display', 'inline');
        }
    }

    function reset_adding_product(with_product_number) {
        selected_product = null;

        if (with_product_number) {
            $('#adding-product-number').val("");
        }

        var quantity_text = $('#adding-product-quantity');
        quantity_text.attr('readonly', true);
        quantity_text.val("");

        $('#adding-product-price').text('');
        $('#adding-product-description').text('');
        $('#adding-product-availability').text('');
        $('#adding-product-added-to-whishlist').css('display', 'none');
        $('#adding-product-adding-to-whishlist').css('display', 'none');
        $('#button-add-product').css('display', 'none');
    }

    function product_lookup(query, callback) {
        loading(false);
        reset_adding_product(false);
        $.ajax({
            method: 'post',
            url : URL,
            data : {
                action: 'product_lookup',
                query: query,
                page_size: 8,
                nonce: $('#product_lookup_nonce').val()
            },
            success: function(response) {
                loading(true);
                var data = JSON.parse(response);
                if ('error' in data) {
                    alert(data.error);
                } else {
                    callback(data);
                }
            },
            error: function(jqXHR, textStatus, error) {
                loading(true);
                $('#adding-product-number').val('');
                alert("Something went wrong, please try again.");
            }
        });
    }

    function display_text(product) {
        return product.ProductNumber;
    }

    function after_select(product) {
        var not_available_text = "Not Available. Call 1-866-631-0196 for Back-Order.";
        if (product.Qty <= 0) {
            alert(not_available_text);
            return;
        }

        var found = false;
        added_products.forEach(function (item) {
            if (product.ProductNumber === item.ProductNumber) {
                found = true;
            }
        });

        if (found) {
            alert("You already added Part Number: " + product.ProductNumber);
            $('#adding-product-number').val("");
            return;
        }

        selected_product = product;

        $('#adding-product-price').text('$' + product.Price);
        $('#adding-product-description').text(product.Title);

        if (parseInt(product.favorited) === 1) {
            $('#adding-product-added-to-whishlist').css('display', 'inline');
            $('#adding-product-adding-to-whishlist').css('display', 'none');
        } else {
            $('#adding-product-added-to-whishlist').css('display', 'none');
            $('#adding-product-adding-to-whishlist').css('display', 'inline');
        }

        $('#button-add-product').css('display', 'inline');

        var availability = $('#adding-product-availability');
        availability.removeClass("text-danger");
        availability.removeClass("text-success");
        availability.addClass("text-success");
        availability.text("Available");

        var quantity = $('#adding-product-quantity');
        quantity.attr('readonly', false);
        quantity.val("1");
        quantity.focus();
    }

    function add_to_cart(url, product_id, quantity) {
        $.ajax({
            method: 'post',
            url : url,
            data : {
                'add-to-cart': product_id,
                'quantity': quantity
            },
            success: function(response) {
                modal.modal('hide');
                add_added_product(selected_product, quantity);
                reset_adding_product(true);
            },
            error: function(jqXHR, textStatus, error) {
                modal.modal('hide');
                alert("Something went wrong, please try again.");
            }
        });
    }

    function get_product_url() {
        if (selected_product === null) {
            return;
        }

        var quantity = parseInt($('#adding-product-quantity').val());
        if (quantity <= 0) {
            $('#adding-product-quantity').val("1");
            return;
        }

        modal.modal('show');
        $.ajax({
            method: 'post',
            url : URL,
            data : {
                action: 'add_product_to_cart',
                product_sku: selected_product.ProductNumber,
                quantity: quantity,
                nonce: $('#add_product_to_cart_nonce').val()
            },
            success: function(response) {
                var data = JSON.parse(response);
                if ('url' in data && 'id' in data) {
                    add_to_cart(data.url, data.id, quantity);
                } else {
                    modal.modal('hide');
                    if ('error' in data) {
                        alert(data.error);
                    } else {
                        alert("Something went wrong, please try again.");
                    }
                }
            },
            error: function(jqXHR, textStatus, error) {
                modal.modal('hide');
                alert("Something went wrong, please try again.");
            }
        });
    }

    $(document).ready(function() {
        $('#adding-product-number').typeahead({
            minLength: 2,
            source: product_lookup,
            displayText: display_text,
            afterSelect: after_select
        });

        modal = $('#loading-dialog').modal({
            show: false
        });

        $('#adding-product-quantity').keypress(function(e) {
            if(e.which === 13) {
                get_product_url();
            }
        });

        $('#adding-product-adding-to-whishlist').on('click', function () {
            if (selected_product === null) {
                return;
            }
            add_to_whishlist(selected_product.post_id);
        });

        $('#button-add-product').on('click', function () {
            if (selected_product !== null || parseInt($('#adding-product-quantity').val()) > 0) {
                get_product_url();
            }
        });
    });
});
