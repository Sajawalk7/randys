<?php

function create_temp_file($prefix) {
    return tempnam(trailingslashit(get_template_directory()), $prefix);
}

function get_temp_file($filename) {
    // Workaround:
    // Could not create a file in system temp dir in Dave's machine
    // Could not create a file in template dir in Tom's machine

    $file_path = trailingslashit(get_template_directory()) . $filename;
    if (file_exists($file_path)) {
        return $file_path;
    } else {
        $file_path = trailingslashit(sys_get_temp_dir()) . $filename;
        if (file_exists($file_path)) {
            return $file_path;
        }
    }
    return null;
}

function ends_with($haystack, $needle) {
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }
    return (substr($haystack, -$length) === $needle);
}

function get_app_images($path = "./wp-content/uploads") {
    static $image_extensions = ["jpg", "jpeg", "png"];
    static $base_directory = "/wp-content/uploads";
    static $whitelist = [
        "/differential-images"
    ];

    $result = [];
    if ($handle = opendir($path)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry == "." || $entry == "..") {
                continue;
            }

            $filePath = $path."/".$entry;

            $is_whitelist = true;
            foreach ($whitelist as $item) {
                if (strpos($filePath, $base_directory . $item) === false) {
                    $is_whitelist = false;
                    break;
                }
            }

            if ($is_whitelist == false) {
                continue;
            }

            if (is_dir($filePath)) {
                $result = array_merge($result, get_app_images($filePath));

            } else {
                $is_image = false;
                foreach ($image_extensions as $image_extension) {
                    if (ends_with($entry, "." . $image_extension)) {
                        $is_image = true;
                        break;
                    }
                }
                if ($is_image) {
                    $hash = md5(explode($base_directory, $filePath)[1]);
                    $result[] = ["hash" => $hash, "path" => ltrim($filePath, '.'), "name" => basename($filePath), "time" => filemtime($filePath)];
                }
            }
        }
        closedir($handle);
    }
    return $result;
}

function prepare_downloadable_image() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
      if ( check_ajax_referer( 'main_ajax_nonce', 'nonce', false ) ) {
        if (!isset($_POST['ids'])) {
            http_response_code(400);
            echo "Error 400: Bad Request. Reason: Invalid/Missing Information.";
            exit;
        }

        global $wpdb;
        $tmp = explode(",", $_POST['ids']);
        $ids = [];
        foreach ($tmp as $item) {
            if (is_numeric($item)) {
                array_push($ids, intval($item));
            }
        }

        // Query for the product images
        $products = $wpdb->get_results($wpdb->prepare("SELECT * FROM randys_product WHERE ProductID IN (" . implode(",", array_fill(0, count($ids), '%d')) . ");", $ids));

        $readme = "This ZIP file from RANDYS Worldwide was created at " . date("Y-m-d H:i:s") .
            " and contains the following files:\n\n";

        // create temp file
        // Could not create a file in system temp dir in Dave's machine
        // Could not create a file in template dir in Tom's machine
        $temp_file = create_temp_file('DIM');

        // Create zip file
        $zip = new ZipArchive;
        $zip->open($temp_file, ZIPARCHIVE::CREATE);

        $base_image_path = wp_upload_dir()['basedir']."/product-images/";

        foreach ($products as $product) {
            $filename = $base_image_path . $product->FullImage;
            if (file_exists($filename)) {
                $baseFilename = basename($filename);
                $zip->addFile($filename, $baseFilename);
                $readme .= "\t* " . $baseFilename . " - " . date("Y-m-d-H_i_s", filemtime($filename)) . "\n";
            }
        }

        $readme_file = create_temp_file('DRM');
        file_put_contents($readme_file, $readme);
        $zip->addFile($readme_file, "README.txt");

        $zip->close();
        echo json_encode(["key" => basename($temp_file)]);
        unlink($readme_file);
      }
    }
    die();
}

function prepare_downloadable_app_image() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
      if ( check_ajax_referer( 'main_ajax_nonce', 'nonce', false ) ) {
        if (!isset($_POST['hashes'])) {
            http_response_code(400);
            echo "Error 400: Bad Request. Reason: Invalid/Missing Information.";
            exit;
        }

        $readme = "This ZIP file from RANDYS Worldwide was created at " . date("Y-m-d H:i:s") .
            " and contains the following files:\n\n";

        // create temp file
        $temp_file = create_temp_file('DIM');

        // Create zip file
        $zip = new ZipArchive;
        $zip->open($temp_file, ZIPARCHIVE::CREATE);

        $hashes = explode(",", $_POST['hashes']);

        $files = get_app_images(wp_upload_dir()['basedir']);

        foreach ($hashes as $hash) {
            if (($key = array_search($hash, array_column($files, "hash"))) === false) {
                continue;
            }

            $filename = $files[$key]["path"];

            if (file_exists($filename)) {
                $baseFilename = basename($filename);
                $zip->addFile($filename, $baseFilename);
                $readme .= "\t* " . $baseFilename . " - " . date("Y-m-d-H_i_s", filemtime($filename)) . "\n";
            }
        }

        $readme_file = create_temp_file('DRM');
        file_put_contents($readme_file, $readme);
        $zip->addFile($readme_file, "README.txt");

        $zip->close();
        echo json_encode(["key" => basename($temp_file)]);
        unlink($readme_file);
      }
    }
    die();
}

function GUID() {
    if (function_exists('com_create_guid') === true) {
        return trim(com_create_guid(), '{}');
    }
    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

function download_downloadable_image() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
      if ( check_ajax_referer( 'downloadable_images_nonce', 'nonce', false ) ) {
        $file_path = get_temp_file($_POST['key']);

        if ($file_path == null) {
            http_response_code(404);
            echo "Error 404: File Not Found.";
            exit;
        }

        $filename = GUID() . "-" . date("Y-m-d-H_i_s") . ".zip";

        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header("Cache-control: private");
        header("Pragma: no-cache");
        header('Content-Length: ' . filesize($file_path));
        echo file_get_contents($file_path);

        // Delete temp file after downloaded
        @unlink($file_path);
      }
    }
    die();
}

add_action("wp_ajax_prepare_downloadable_image", "prepare_downloadable_image");
add_action("wp_ajax_nopriv_prepare_downloadable_image", "prepare_downloadable_image");

add_action("wp_ajax_prepare_downloadable_app_image", "prepare_downloadable_app_image");
add_action("wp_ajax_nopriv_prepare_downloadable_app_image", "prepare_downloadable_app_image");

add_action("wp_ajax_download_downloadable_image", "download_downloadable_image");
add_action("wp_ajax_nopriv_download_downloadable_image", "download_downloadable_image");
