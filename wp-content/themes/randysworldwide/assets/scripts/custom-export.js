jQuery(function ($) {

    $(document).ready(function() {

        function download(key) {
            $('#dowloadable_action').val("download_updated_custom_export");
            $('#downloadable_key').val(key);
            $('#download_form').submit();
        }

        // Create Big commerce file
        $('#button-custom-export-generate').on('click', function(e) {
            $('#button-custom-export-generate').val("GENERATING...");
            $('#button-custom-export-generate').prop('disabled', true);
            $('#generate-result').css('display', 'none');
            e.preventDefault();
            $('#form-custom-export').submit();
        });

        $('#form-custom-export').ajaxForm({
            url: "/wp-admin/admin-ajax.php",
            type: "post",
            success: function(responseText, statusText, xhr, $form) {
                $('#button-custom-export-generate').val("GENERATE");
                $('#button-custom-export-generate').prop('disabled', false);

                var data = JSON.parse(responseText);
                if ('key' in data) {
                    $('#generate-result').css('display', 'block');
                    $('#generate-result').html(data.message);
                    download(data.key);
                } else if ('error' in data) {
                    $('#generate-result').css('display', 'block');
                    $('#generate-result').html(data.error);
                }
            },
            error: function(jqXHR, textStatus, error) {
                $('#button-custom-export-generate').val("GENERATE");
                $('#button-custom-export-generate').prop('disabled', false);
                console.log('error');
            }
        });


        // Upload custom export

        $('#button-upload-custom-export').on('click', function(e) {
            $('#button-upload-custom-export').val("UPLOADING...");
            $('#button-upload-custom-export').prop('disabled', true);
            $('#upload-result').css('display', 'none');
            e.preventDefault();
            $('#form-upload-custom-export').submit();
        });

        $('#form-upload-custom-export').ajaxForm({
            url: "/wp-admin/admin-ajax.php",
            type: "post",
            success: function(responseText, statusText, xhr, $form) {
                $('#button-upload-custom-export').val("UPLOAD");
                $('#button-upload-custom-export').prop('disabled', false);
                var data = JSON.parse(responseText);
                if ('key' in data) {
                    $('#upload-result').css('display', 'block');
                    $('#upload-result').html(data.message);
                    download(data.key);
                } else if ('error' in data) {
                    $('#upload-result').css('display', 'error');
                    $('#upload-result').html(data.message);
                }
            },
            error: function(jqXHR, textStatus, error) {
                $('#button-upload-custom-export').val("UPLOAD");
                $('#button-upload-custom-export').prop('disabled', false);
                console.log('error');
            }
       });

    });

});
