jQuery(function ($) {

    var URL = wpAjax.ajax_url;

    function download(key, type) {
        $('#product_data_download_key').val(key);
        $('#product_data_download_ext').val(type);
        $('#product_data_download_form').submit();
    }

    function onError(jqXHR, textStatus, error) {
        $('#div-loading-product-data').css('display', 'none');
        alert("Something went wrong, please try again.");
    }

    function checkResponse(response, type) {
        $('#div-loading-product-data').css('display', 'none');
        var data = JSON.parse(response);
        if ('key' in data) {
            download(data.key, type);
        } else if ('error' in data) {
            alert(data.error);
        }
    }

    $(document).ready(function() {
        $('#download-product-xml').on('click', function (e) {
            e.preventDefault();
            $('#div-loading-product-data').css('display', 'block');
            $.ajax({
                method: 'post',
                url : URL,
                data : {
                    action: 'download_product_data_xml'
                },
                success: function(response) {
                    checkResponse(response, "xml");
                },
                error: onError
            });
        });

        $('#download-product-csv').on('click', function (e) {
            e.preventDefault();
            $('#div-loading-product-data').css('display', 'block');
            $.ajax({
                method: 'post',
                url : URL,
                data : {
                    action: 'download_product_data_csv'
                },
                success: function(response) {
                    checkResponse(response, "csv");
                },
                error: onError
            });
        });

        $('#download-product-application-xml').on('click', function (e) {
            e.preventDefault();
            $('#div-loading-product-data').css('display', 'block');
            $.ajax({
                method: 'post',
                url : URL,
                data : {
                    action: 'download_product_application_xml'
                },
                success: function(response) {
                    checkResponse(response, "xml");
                },
                error: onError
            });

        });

        $('#download-product-application-excel').on('click', function (e) {
            e.preventDefault();
            $('#div-loading-product-data').css('display', 'block');
            $.ajax({
                method: 'post',
                url : URL,
                data : {
                    action: 'download_product_application_excel'
                },
                success: function(response) {
                    checkResponse(response, "xlsx");
                },
                error: onError
            });
        });
    });
});
