"use strict";jQuery(function(o){o(document).ready(function(){function t(t){o("#dowloadable_action").val("download_updated_custom_export"),o("#downloadable_key").val(t),o("#download_form").submit()}o("#button-custom-export-generate").on("click",function(t){o("#button-custom-export-generate").val("GENERATING..."),o("#button-custom-export-generate").prop("disabled",!0),o("#generate-result").css("display","none"),t.preventDefault(),o("#form-custom-export").submit()}),o("#form-custom-export").ajaxForm({url:"/wp-admin/admin-ajax.php",type:"post",success:function(e,r,u,a){o("#button-custom-export-generate").val("GENERATE"),o("#button-custom-export-generate").prop("disabled",!1);var s=JSON.parse(e);"key"in s?(o("#generate-result").css("display","block"),o("#generate-result").html(s.message),t(s.key)):"error"in s&&(o("#generate-result").css("display","block"),o("#generate-result").html(s.error))},error:function(t,e,r){o("#button-custom-export-generate").val("GENERATE"),o("#button-custom-export-generate").prop("disabled",!1),console.log("error")}}),o("#button-upload-custom-export").on("click",function(t){o("#button-upload-custom-export").val("UPLOADING..."),o("#button-upload-custom-export").prop("disabled",!0),o("#upload-result").css("display","none"),t.preventDefault(),o("#form-upload-custom-export").submit()}),o("#form-upload-custom-export").ajaxForm({url:"/wp-admin/admin-ajax.php",type:"post",success:function(e,r,u,a){o("#button-upload-custom-export").val("UPLOAD"),o("#button-upload-custom-export").prop("disabled",!1);var s=JSON.parse(e);"key"in s?(o("#upload-result").css("display","block"),o("#upload-result").html(s.message),t(s.key)):"error"in s&&(o("#upload-result").css("display","error"),o("#upload-result").html(s.message))},error:function(t,e,r){o("#button-upload-custom-export").val("UPLOAD"),o("#button-upload-custom-export").prop("disabled",!1),console.log("error")}})})});