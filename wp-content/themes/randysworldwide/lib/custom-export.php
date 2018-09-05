<?php

require_once get_template_directory() . "/lib/big-commerce.php";


function create_custom_export() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        if (check_ajax_referer('create_custom_export_nonce', 'nonce', false) === false) {
            http_response_code(400);
            echo "Error 400: Bad Request. Reason: Invalid/Missing Information.";
            exit;
        }

        $customer_number = isset($_POST['customer_number']) && $_POST['customer_number'] !== "" ? $_POST['customer_number'] : null;
        $price_level = isset($_POST['price_level']) && $_POST['price_level'] !== "" ? $_POST['price_level'] : null;

        $ret = Big_Commerce_Updater::generate_big_commerce_csv($customer_number, $price_level);
        if ($ret == null) {
            echo json_encode(["error" => Big_Commerce_Updater::$last_error]);
        } else {
            echo json_encode(["key" => basename($ret), "message" => Big_Commerce_Updater::$message]);
        }
    }
    die();
}

add_action("wp_ajax_create_custom_export", "create_custom_export");


function upload_custom_export() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        if (check_ajax_referer('upload_custom_export_nonce', 'nonce', false) === false) {
            http_response_code(400);
            echo "Error 400: Bad Request. Reason: Invalid/Missing Information.";
            exit;
        }

        $file = array_shift($_FILES);

        if ($file === null) {
            http_response_code(400);
            echo "Error 400: Bad Request. Reason: File is Missing.";
            exit;
        }

        $ret = Big_Commerce_Updater::update_big_commerce_csv($file);
        if ($ret == null) {
            echo json_encode(["error" => Big_Commerce_Updater::$last_error]);
        } else {
            echo json_encode(["key" => basename($ret), "message" => Big_Commerce_Updater::$message]);
        }
    }
    die();
}

function download_updated_custom_export() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        if (check_ajax_referer('download_updated_custom_export_nonce', 'nonce', false) === false) {
            http_response_code(400);
            echo "Error 400: Bad Request. Reason: Invalid/Missing Information.";
            exit;
        }

        $file_path = get_temp_file($_POST['key']);
        if ($file_path == null) {
            http_response_code(404);
            echo "Error 404: File Not Found.";
            exit;
        }

        header('Content-Description: File Transfer');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=' . generate_random_string() . ".csv");
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header("Cache-control: private");
        header("Pragma: no-cache");
        header('Content-Length: ' . filesize($file_path));
        echo file_get_contents($file_path);

        // Delete temp file after downloaded
        @unlink($file_path);
    }
    die();
}

add_action("wp_ajax_upload_custom_export", "upload_custom_export");
add_action("wp_ajax_download_updated_custom_export", "download_updated_custom_export");
