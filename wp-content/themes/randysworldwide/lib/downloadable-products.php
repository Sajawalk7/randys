<?php
if (function_exists("generateRandomString") == false) {
    function generate_random_string($length = 8) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}

function my_price_level() {
    global $wpdb;
    $current_user = wp_get_current_user();

    $user = $wpdb->get_row($wpdb->prepare("SELECT PRCLEVEL FROM randys_customers WHERE WOOCustID = %d", array($current_user->ID)));

    if ($user === null) {
        return null;
    }
    return $user->PRCLEVEL;
}

function jobber_price($product, $price_level = null) {
    if ($price_level === null) {
        $price_level = my_price_level();
    }
    switch ($price_level) {
        case "P1": return $product->List;
        case "P2": return $product->Price;
        case "P3": return $product->MAP;
        case "P4": return $product->P4;
        case "P5": return $product->P5;
        case "P6": return $product->P6;
        default: return 0;  // FailSafe
    }
}

function customer_available_products() {
    global $wpdb;
    $current_user = wp_get_current_user();

    return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT p1.*, c1.CategoryName as CategoryName, c2.CategoryName as ParentCategoryName, p2.FullImage, p2.Height, p2.Width, p2.Length, p2.List, pupc.UPC
                FROM randys_productsearch as p1
                RIGHT JOIN randys_product as p2 ON p1.ProductID = p2.ProductID
                RIGHT JOIN randys_category as c1 ON p1.CategoryID = c1.CategoryID
                RIGHT JOIN randys_category as c2 ON p1.ParentCategoryID = c2.CategoryID
                RIGHT JOIN randys_productupc as pupc ON p1.ProxyNumber = pupc.Proxy
                WHERE p1.Brand in(SELECT b.BrandName FROM randys_brands as b
                  INNER JOIN randys_customerbrands as cb ON b.BrandID = cb.BrandID
                  INNER JOIN randys_customers as c ON cb.CustomerID = c.CUSTNMBR
                  WHERE c.WOOCustID = %d)",
                array($current_user->ID)
            )
    );
}

function customer_available_product_applications() {
    global $wpdb;
    $current_user = wp_get_current_user();

    return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ProductNumber, DiffName, Make, Model, StartYear, EndYear, Side, DriveType, Brand, Category, Parent
                FROM randys_advancedsearch
                WHERE Brand in(SELECT b.BrandName FROM randys_brands as b
                  INNER JOIN randys_customerbrands as cb ON b.BrandID = cb.BrandID
                  INNER JOIN randys_customers as c ON cb.CustomerID = c.CUSTNMBR
                  WHERE c.WOOCustID = %d)",
                $current_user->ID
            )
    );
}

function escape_string_for_csv($string) {
    return "\"" . str_replace("\"", "\"\"", $string) . "\"";
}

function image_url($string) {
    return home_url() . "/wp-content/uploads/product-images/" . $string;
}


function download_product_data_xml() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        $products = customer_available_products();

        if (count($products) == 0) {
            echo json_encode(["error" => "You do not have access to any Brand or Product"]);
        } else {
            $xml = new SimpleXMLElement('<DocumentElement/>');
            foreach ($products as $item) {
                $product = $xml->addChild('Product');
                $product->addChild('ParentCategoryName', htmlspecialchars($item->ParentCategoryName));
                $product->addChild('ChildCategoryName', htmlspecialchars($item->CategoryName));
                $product->addChild('BrandName', htmlspecialchars($item->Brand));
                $product->addChild('ProductNumber', htmlspecialchars($item->ProductNumber));
                $product->addChild('SKU2', htmlspecialchars($item->ProductNumber));
                $product->addChild('Title', htmlspecialchars($item->Title));
                $product->addChild('DESCRIPTION', htmlspecialchars($item->Description));
                $product->addChild('Map_Price', htmlspecialchars($item->MAP));
                $product->addChild('Jobber', htmlspecialchars(jobber_price($item)));
                $product->addChild('Qty', htmlspecialchars($item->Qty));
                $product->addChild('Category', htmlspecialchars($item->CategoryName));
                $product->addChild('Brand', htmlspecialchars($item->Brand));
                $product->addChild('IMAGE', htmlspecialchars($item->FullImage != "" ? image_url($item->FullImage) : ""));
                $product->addChild('Thumbnail', htmlspecialchars($item->ThumbImage != "" ? image_url($item->ThumbImage) : ""));
                $product->addChild('WEIGHT', htmlspecialchars($item->Weight));
                $product->addChild('Height', htmlspecialchars($item->Height));
                $product->addChild('Width', htmlspecialchars($item->Width));
                $product->addChild('Length', htmlspecialchars($item->Length));
                $product->addChild('UPC', htmlspecialchars($item->UPC));
            }
            $result = $xml->asXML();
            $temp_file = create_temp_file('PDX');
            file_put_contents($temp_file, $result);
            echo json_encode(["key" => basename($temp_file)]);
        }
    }
    die();
}

function download_product_data_csv() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        $products = customer_available_products();

        if (count($products) == 0) {
            echo json_encode(["error" => "You do not have access to any Brand or Product"]);
        } else {
            $result = "ParentCategoryName,ChildCategoryName,BrandName,ProductNumber,SKU2,Title,DESCRIPTION,Map_Price,Jobber,Qty,Category,Brand,IMAGE,Thumbnail,WEIGHT,Height,Width,Length,UPC\n";

            foreach ($products as $item) {
                $result .= escape_string_for_csv($item->ParentCategoryName) . ",";
                $result .= escape_string_for_csv($item->CategoryName) . ",";
                $result .= escape_string_for_csv($item->Brand) . ",";
                $result .= escape_string_for_csv($item->ProductNumber) . ",";
                $result .= escape_string_for_csv($item->ProductNumber) . ",";
                $result .= escape_string_for_csv($item->Title) . ",";
                $result .= escape_string_for_csv($item->Description) . ",";
                $result .= escape_string_for_csv($item->MAP) . ",";
                $result .= escape_string_for_csv(jobber_price($item)) . ",";
                $result .= escape_string_for_csv($item->Qty) . ",";
                $result .= escape_string_for_csv($item->CategoryName) . ",";
                $result .= escape_string_for_csv($item->Brand) . ",";
                $result .= escape_string_for_csv($item->FullImage != "" ? image_url($item->FullImage) : "") . ",";
                $result .= escape_string_for_csv($item->ThumbImage != "" ? image_url($item->ThumbImage) : "") . ",";
                $result .= escape_string_for_csv($item->Weight) . ",";
                $result .= escape_string_for_csv($item->Height) . ",";
                $result .= escape_string_for_csv($item->Width) . ",";
                $result .= escape_string_for_csv($item->Length) . ",";
                $result .= escape_string_for_csv($item->UPC) . "\n";
            }

            $temp_file = create_temp_file('PDC');
            file_put_contents($temp_file, $result);
            echo json_encode(array("key" => basename($temp_file)));
        }
    }
    die();
}

function download_product_application_xml() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        $products = customer_available_product_applications();

        if (count($products) == 0) {
            echo json_encode(["error" => "You do not have access to any Brand or Product"]);
        } else {
            $xml = new SimpleXMLElement('<DocumentElement/>');
            foreach ($products as $item) {
                $application = $xml->addChild('Application');
                $application->addChild('ProductNumber', htmlspecialchars($item->ProductNumber));
                $application->addChild('SKU2', htmlspecialchars($item->ProductNumber));
                $application->addChild('DIFFERENTIAL', htmlspecialchars($item->DiffName));
                $application->addChild('Make', htmlspecialchars($item->Make));
                $application->addChild('Model', htmlspecialchars($item->Model));
                $application->addChild('StartYear', htmlspecialchars($item->StartYear));
                $application->addChild('EndYear', htmlspecialchars($item->EndYear));
                $application->addChild('Axle', htmlspecialchars($item->Side));
                $application->addChild('DriveType', htmlspecialchars($item->DriveType));
                $application->addChild('BrandName', htmlspecialchars($item->Brand));
                $application->addChild('ChildCategoryName', htmlspecialchars($item->Category));
                $application->addChild('ParentCategoryName', htmlspecialchars($item->Parent));
            }
            $result = $xml->asXML();
            $temp_file = create_temp_file('PAX');
            file_put_contents($temp_file, $result);
            echo json_encode(["key" => basename($temp_file)]);
        }
    }
    die();
}

function download_product_application_excel() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        require_once(get_template_directory() . '/lib/PHP_XLSXWriter/xlsxwriter.class.php');
        $products = customer_available_product_applications();

        if (count($products) == 0) {
            echo json_encode(["error" => "You do not have access to any Brand or Product"]);
        } else {

            // Headers
            $headers = ["ProductNumber", "SKU2", "DIFFERENTIAL", "Make", "Model", "StartYear", "EndYear", "Axle", "DriveType", "BrandName", "ChildCategoryName", "ParentCategoryName"];

            $data = array($headers);

            // Content
            foreach ($products as $index => $item) {
                $data[] = array(
                    $item->ProductNumber,
                    $item->ProductNumber,
                    $item->DiffName,
                    $item->Make,
                    $item->Model,
                    $item->StartYear,
                    $item->EndYear,
                    $item->Side,
                    $item->DriveType,
                    $item->Brand,
                    $item->Category,
                    $item->Parent,
                );
            }

            $temp_file = create_temp_file('PAE');
            $writer = new \XLSXWriter();
            $writer->writeSheet($data);
            $writer->writeToFile($temp_file);
            echo json_encode(["key" => basename($temp_file)]);
        }
    }
    die();
}

function download_product_file() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
      if ( check_ajax_referer( 'downloadable_products_nonce', 'nonce', false ) ) {
        $file_path = get_temp_file($_POST['key']);
        if ($file_path == null) {
            http_response_code(404);
            echo "Error 404: File Not Found.";
            exit;
        }

        $ext = $_POST['ext'];

        header('Content-Description: File Transfer');

        if ($ext == "csv") {
            header('Content-Type: text/csv');
        } elseif ($ext == "xml") {
            header('Content-Type: text/xml');
        } elseif ($ext == "xlsx") {
            Header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        }

        header('Content-Disposition: attachment; filename=' . generate_random_string() . "." . $ext);
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

add_action("wp_ajax_download_product_data_xml", "download_product_data_xml");
add_action("wp_ajax_nopriv_download_product_data_xml", "download_product_data_xml");

add_action("wp_ajax_download_product_data_csv", "download_product_data_csv");
add_action("wp_ajax_nopriv_download_product_data_csv", "download_product_data_csv");

add_action("wp_ajax_download_product_application_xml", "download_product_application_xml");
add_action("wp_ajax_nopriv_download_product_application_xml", "download_product_application_xml");

add_action("wp_ajax_download_product_application_excel", "download_product_application_excel");
add_action("wp_ajax_nopriv_download_product_application_excel", "download_product_application_excel");

add_action("wp_ajax_download_product_file", "download_product_file");
add_action("wp_ajax_nopriv_download_product_file", "download_product_file");
