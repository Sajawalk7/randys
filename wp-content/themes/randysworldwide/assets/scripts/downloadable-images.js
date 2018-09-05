jQuery(function ($) {

    var URL = wpAjax.ajax_url;

    function updateButtons() {
        var all = 0;
        var checked = 0;
        if (parseInt(current_type) === 0) {
            all = $( "input.downloadable-image-checkbox" ).length;
            checked = $( "input.downloadable-image-checkbox:checked" ).length;
        } else {
            all = $( "input.downloadable-app-image-checkbox" ).length;
            checked = $( "input.downloadable-app-image-checkbox:checked" ).length;
        }

        $('#btn-download').prop('disabled', checked === 0);
        $('#btn-select-all').prop('disabled', checked === all);
        $('#btn-clear-select').prop('disabled', checked === 0);
    }

    function changeTab() {
        current_type = $('#select_type').val();

        $('#downloadable-image').css('display', 'none');
        $('#downloadable-app-image').css('display', 'none');

        if (parseInt(current_type) === 0) {
            $('#downloadable-image').css('display', 'inline');
        } else {
            $('#downloadable-app-image').css('display', 'inline');
        }

        updateButtons();
    }

    function getFileWithKey(key) {
        $('#downloadableKey').val(key);
        $('#download_form').submit();
    }

    function download() {
        $('#btn-download').val("GETTING FILES FOR DOWNLOAD...");
        $('#btn-download').prop('disabled', true);

        if (parseInt(current_type) === 0) {
            var checked = $( "input.downloadable-image-checkbox:checked" );
            var ids = [];
            checked.each(function (index) {
                ids.push($(this).val());
            });

            ids = ids.join();

            $.ajax({
                method: 'post',
                url : URL,
                data : {
                    action: 'prepare_downloadable_image',
                    ids: ids,
                    is_product: parseInt(current_type) === 0 ? 1 : 0,
                    nonce: wpAjax.ajax_nonce
                },
                success: function(response) {
                    $('#btn-download').val("DOWNLOAD SELECTED IMAGES");
                    $('#btn-download').prop('disabled', false);
                    var data = JSON.parse(response);
                    getFileWithKey(data.key);
                },
                error: function(jqXHR, textStatus, error) {
                    $('#btn-download').val("DOWNLOAD SELECTED IMAGES");
                    $('#btn-download').prop('disabled', false);
                }
            });
        } else {
            var app_checked = $( "input.downloadable-app-image-checkbox:checked" );
            var hashes = [];
            app_checked.each(function (index) {
                hashes.push($(this).val());
            });

            hashes = hashes.join();

            $.ajax({
                method: 'post',
                url : URL,
                data : {
                    action: 'prepare_downloadable_app_image',
                    hashes: hashes,
                    is_product: parseInt(current_type) === 0 ? 1 : 0,
                    nonce: wpAjax.ajax_nonce
                },
                success: function(response) {
                    $('#btn-download').val("DOWNLOAD SELECTED IMAGES");
                    $('#btn-download').prop('disabled', false);
                    var data = JSON.parse(response);
                    getFileWithKey(data.key);
                },
                error: function(jqXHR, textStatus, error) {
                    $('#btn-download').val("DOWNLOAD SELECTED IMAGES");
                    $('#btn-download').prop('disabled', false);
                }
            });
        }
    }

    function selectAll() {
        if (parseInt(current_type) === 0) {
            $('.downloadable-image-checkbox').prop("checked", true);
        } else {
            $('.downloadable-app-image-checkbox').prop("checked", true);
        }
        updateButtons();
    }

    function clearAll() {
        if (parseInt(current_type) === 0) {
            $('.downloadable-image-checkbox').prop("checked", false);
        } else {
            $('.downloadable-app-image-checkbox').prop("checked", false);
        }
        updateButtons();
    }

    $(document).ready(function() {
      if ( 0 < $('#select_type').length ) {
        $('#select_type').on('change', changeTab);
        $('#btn-download').on('click', download);
        $('#btn-select-all').on('click', selectAll);
        $('#btn-clear-select').on('click', clearAll);
        $('input.downloadable-image-checkbox').on('change', updateButtons);
        $('input.downloadable-app-image-checkbox').on('change', updateButtons);
        updateButtons();
      }
    });

    if ( 0 < $('#select_type').length ) {
      changeTab();
    }
});
