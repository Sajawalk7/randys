<?php

class Big_Commerce_Data {

    const ITEM_TYPE = "Item Type";
    const PRODUCT_ID = "Product ID";
    const PRODUCT_NAME = "Product Name";
    const PRODUCT_TYPE = "Product Type";
    const PRODUCT_CODE_SKU = "Product Code/SKU";
    const BIN_PICKING_NUMBER = "Bin Picking Number";
    const BRAND_NAME = "Brand Name";
    const OPTION_SET = "Option Set";
    const OPTION_SET_ALIGN = "Option Set Align";
    const PRODUCT_DESCRIPTION = "Product Description";
    const PRICE = "Price";
    const COST_PRICE = "Cost Price";
    const RETAIL_PRICE = "Retail Price";
    const SALE_PRICE = "Sale Price";
    const FIXED_SHIPPING_COST = "Fixed Shipping Cost";
    const FREE_SHIPPING = "Free Shipping";
    const PRODUCT_WARRANTY = "Product Warranty";
    const PRODUCT_WEIGHT = "Product Weight";
    const PRODUCT_WIDTH = "Product Width";
    const PRODUCT_HEIGHT = "Product Height";
    const PRODUCT_DEPTH = "Product Depth";
    const ALLOW_PURCHASE = "Allow Purchases?";
    const PRODUCT_VISIBLE = "Product Visible?";
    const PRODUCT_AVAILABILITY = "Product Availability";
    const TRACK_INVENTORY = "Track Inventory";
    const CURRENT_STOCK_LEVEL = "Current Stock Level";
    const LOW_STOCK_LEVEL = "Low Stock Level";
    const CATEGORY = "Category";
    const PRODUCT_IMAGE_ID_1 = "Product Image ID - 1";
    const PRODUCT_IMAGE_FILE_1 = "Product Image File - 1";
    const PRODUCT_IMAGE_DESCRIPTION_1 = "Product Image Description - 1";
    const PRODUCT_IMAGE_IS_THUMBNAIL_1 = "Product Image Is Thumbnail - 1";
    const PRODUCT_IMAGE_SORT_1 = "Product Image Sort - 1";
    const PRODUCT_IMAGE_ID_2 = "Product Image ID - 2";
    const PRODUCT_IMAGE_FILE_2 = "Product Image File - 2";
    const PRODUCT_IMAGE_DESCRIPTION_2 = "Product Image Description - 2";
    const PRODUCT_IMAGE_IS_THUMBNAIL_2 = "Product Image Is Thumbnail - 2";
    const PRODUCT_IMAGE_SORT_2 = "Product Image Sort - 2";
    const PRODUCT_IMAGE_ID_3 = "Product Image ID - 3";
    const PRODUCT_IMAGE_FILE_3 = "Product Image File - 3";
    const PRODUCT_IMAGE_DESCRIPTION_3 = "Product Image Description - 3";
    const PRODUCT_IMAGE_IS_THUMBNAIL_3 = "Product Image Is Thumbnail - 3";
    const PRODUCT_IMAGE_SORT_3 = "Product Image Sort - 3";
    const PRODUCT_IMAGE_ID_4 = "Product Image ID - 4";
    const PRODUCT_IMAGE_FILE_4 = "Product Image File - 4";
    const PRODUCT_IMAGE_DESCRIPTION_4 = "Product Image Description - 4";
    const PRODUCT_IMAGE_IS_THUMBNAIL_4 = "Product Image Is Thumbnail - 4";
    const PRODUCT_IMAGE_SORT_4 = "Product Image Sort - 4";
    const SEARCH_KEYWORDS = "Search Keywords";
    const PAGE_TITLE = "Page Title";
    const META_KEYWORDS = "Meta Keywords";
    const META_DESCRIPTION = "Meta Description";
    const MYOB_ASSET_ACCT = "MYOB Asset Acct";
    const MYOB_INCOME_ACCT = "MYOB Income Acct";
    const MYOB_EXPENSE_ACCT = "MYOB Expense Acct";
    const PRODUCT_CONDITION = "Product Condition";
    const SHOW_PRODUCT_CONDITION = "Show Product Condition?";
    const EVENT_DATE_REQUIRED = "Event Date Required?";
    const EVENT_DATE_NAME = "Event Date Name";
    const EVENT_DATE_IS_LIMITED = "Event Date Is Limited?";
    const EVENT_DATE_START_DATE = "Event Date Start Date";
    const EVENT_DATE_END_DATE = "Event Date End Date";
    const SORT_ORDER = "Sort Order";
    const PRODUCT_TAX_CLASS = "Product Tax Class";
    const PRODUCT_UPC_EAN = "Product UPC/EAN";
    const STOP_PROCESSING_RULES = "Stop Processing Rules";
    const PRODUCT_URL = "Product URL";
    const REDIRECT_OLD_URL = "Redirect Old URL?";
    const GPS_GLOBAL_TRADE_ITEM_NUMBER = "GPS Global Trade Item Number";
    const GPS_MANUFACTURER_PART_NUMBER = "GPS Manufacturer Part Number";
    const GPS_GENDER = "GPS Gender";
    const GPS_AGE_GROUP = "GPS Age Group";
    const GPS_COLOR = "GPS Color";
    const GPS_SIZE = "GPS Size";
    const GPS_MATERIAL = "GPS Material";
    const GPS_PATTERN = "GPS Pattern";
    const GPS_ITEM_GROUP_ID = "GPS Item Group ID";
    const GPS_CATEGORY = "GPS Category";
    const GPS_ENABLED = "GPS Enabled";
    const AVALARA_PRODUCT_TAX_CODE = "Avalara Product Tax Code";
    const PRODUCT_CUSTOMER_FILEDS = "Product Custom Fields";

    private $has_header = false;
    private $is_default_header = false;
    private $headers = [];
    private $rows = [];

    private $columnCode = 0;
    private $columnName = 0;
    private $columnBrandName = 0;
    private $columnProductDescription = 0;
    private $columnPrice = 0;
    private $columnCostPrice = 0;
    private $columnProductWeight = 0;
    private $columnProductWidth = 0;
    private $columnProductHeight = 0;
    private $columnProductDepth = 0;
    private $columnCurrentStockLevel = 0;
    private $columnSearchKeywords = 0;
    private $columnMetaDescription = 0;
    private $columnProductImageDescription1 = 0;
    private $columnVisible = 0;

    public function has_require_fields($columns) {
        $require_fields = [
            self::ITEM_TYPE,
            self::PRODUCT_CODE_SKU,
            self::BRAND_NAME,
            self::PRODUCT_DESCRIPTION,
            self::PRICE,
            self::COST_PRICE,
            self::PRODUCT_HEIGHT,
            self::PRODUCT_WEIGHT,
            self::PRODUCT_WIDTH,
            self::PRODUCT_DEPTH,
            self::CURRENT_STOCK_LEVEL,
            self::SEARCH_KEYWORDS,
            self::PRODUCT_VISIBLE,
            self::PRODUCT_NAME,
            self::META_DESCRIPTION,
            self::PRODUCT_IMAGE_DESCRIPTION_1
        ];
        $diff = array_diff($require_fields, $columns);
        return count($diff) == 0;
    }

    public function has_header() {
        return $this->has_header;
    }

    public function get_row_count() {
        return count($this->rows);
    }

    /**
     *
     * @param $new_headers - array
     */
    public function set_header($new_headers) {
        $this->headers = $new_headers;
        $this->has_header = true;
        $this->get_header_columns();
        $this->is_default_header = false;
    }

    public function set_default_header() {
        $headers = [
            self::ITEM_TYPE,
            self::PRODUCT_ID,
            self::PRODUCT_NAME,
            self::PRODUCT_TYPE,
            self::PRODUCT_CODE_SKU,
            self::BIN_PICKING_NUMBER,
            self::BRAND_NAME,
            self::OPTION_SET,
            self::OPTION_SET_ALIGN,
            self::PRODUCT_DESCRIPTION,
            self::PRICE,
            self::COST_PRICE,
            self::RETAIL_PRICE,
            self::SALE_PRICE,
            self::FIXED_SHIPPING_COST,
            self::FREE_SHIPPING,
            self::PRODUCT_WARRANTY,
            self::PRODUCT_WEIGHT,
            self::PRODUCT_WIDTH,
            self::PRODUCT_HEIGHT,
            self::PRODUCT_DEPTH,
            self::ALLOW_PURCHASE,
            self::PRODUCT_VISIBLE,
            self::PRODUCT_AVAILABILITY,
            self::TRACK_INVENTORY,
            self::CURRENT_STOCK_LEVEL,
            self::LOW_STOCK_LEVEL,
            self::CATEGORY,
            self::PRODUCT_IMAGE_ID_1,
            self::PRODUCT_IMAGE_FILE_1,
            self::PRODUCT_IMAGE_DESCRIPTION_1,
            self::PRODUCT_IMAGE_IS_THUMBNAIL_1,
            self::PRODUCT_IMAGE_SORT_1,
            self::PRODUCT_IMAGE_ID_2,
            self::PRODUCT_IMAGE_FILE_2,
            self::PRODUCT_IMAGE_DESCRIPTION_2,
            self::PRODUCT_IMAGE_IS_THUMBNAIL_2,
            self::PRODUCT_IMAGE_SORT_2,
            self::PRODUCT_IMAGE_ID_3,
            self::PRODUCT_IMAGE_FILE_3,
            self::PRODUCT_IMAGE_DESCRIPTION_3,
            self::PRODUCT_IMAGE_IS_THUMBNAIL_3,
            self::PRODUCT_IMAGE_SORT_3,
            self::PRODUCT_IMAGE_ID_4,
            self::PRODUCT_IMAGE_FILE_4,
            self::PRODUCT_IMAGE_DESCRIPTION_4,
            self::PRODUCT_IMAGE_IS_THUMBNAIL_4,
            self::PRODUCT_IMAGE_SORT_4,
            self::SEARCH_KEYWORDS,
            self::PAGE_TITLE,
            self::META_KEYWORDS,
            self::META_DESCRIPTION,
            self::MYOB_ASSET_ACCT,
            self::MYOB_INCOME_ACCT,
            self::MYOB_EXPENSE_ACCT,
            self::PRODUCT_CONDITION,
            self::SHOW_PRODUCT_CONDITION,
            self::EVENT_DATE_REQUIRED,
            self::EVENT_DATE_NAME,
            self::EVENT_DATE_IS_LIMITED,
            self::EVENT_DATE_START_DATE,
            self::EVENT_DATE_END_DATE,
            self::SORT_ORDER,
            self::PRODUCT_TAX_CLASS,
            self::PRODUCT_UPC_EAN,
            self::STOP_PROCESSING_RULES,
            self::PRODUCT_URL,
            self::REDIRECT_OLD_URL,
            self::GPS_GLOBAL_TRADE_ITEM_NUMBER,
            self::GPS_MANUFACTURER_PART_NUMBER,
            self::GPS_GENDER,
            self::GPS_AGE_GROUP,
            self::GPS_COLOR,
            self::GPS_SIZE,
            self::GPS_MATERIAL,
            self::GPS_PATTERN,
            self::GPS_ITEM_GROUP_ID,
            self::GPS_CATEGORY,
            self::GPS_ENABLED,
            self::AVALARA_PRODUCT_TAX_CODE,
            self::PRODUCT_CUSTOMER_FILEDS
        ];
        $this->set_header($headers);
        $this->is_default_header = true;
    }

    /**
     * Get the required column's indexes
     */
    private function get_header_columns() {
        $this->columnCode = array_search(self::PRODUCT_CODE_SKU, $this->headers);
        $this->columnName = array_search(self::PRODUCT_NAME, $this->headers);
        $this->columnBrandName = array_search(self::BRAND_NAME, $this->headers);
        $this->columnProductDescription = array_search(self::PRODUCT_DESCRIPTION, $this->headers);
        $this->columnPrice = array_search(self::PRICE, $this->headers);
        $this->columnCostPrice = array_search(self::COST_PRICE, $this->headers);
        $this->columnProductWeight = array_search(self::PRODUCT_WEIGHT, $this->headers);
        $this->columnProductWidth = array_search(self::PRODUCT_WIDTH, $this->headers);
        $this->columnProductHeight = array_search(self::PRODUCT_HEIGHT, $this->headers);
        $this->columnProductDepth = array_search(self::PRODUCT_DEPTH, $this->headers);
        $this->columnCurrentStockLevel = array_search(self::CURRENT_STOCK_LEVEL, $this->headers);
        $this->columnSearchKeywords = array_search(self::SEARCH_KEYWORDS, $this->headers);
        $this->columnMetaDescription = array_search(self::META_DESCRIPTION, $this->headers);
        $this->columnProductImageDescription1 = array_search(self::PRODUCT_IMAGE_DESCRIPTION_1, $this->headers);
        $this->columnVisible = array_search(self::PRODUCT_VISIBLE, $this->headers);
    }

    /**
     * Append a whole new row
     * @param $row - array
     */
    public function add_row($row) {
        array_push($this->rows, $row);
    }

    /**
     * Get the required row by using column's index and value
     * @param $column - column index
     * @param $value - value of column
     * @return array|null
     */
    public function get_row_where_column($column, $value) {
        foreach ($this->rows as $row) {
            if ($row[$column] == $value) {
                return $row;
            }
        }
        return null;
    }

    /**
     * Get the required row by the given product code
     * @param $code - product code
     * @return array|null
     */
    public function get_product_from_code($code) {
        return $this->get_row_where_column($this->columnCode, $code);
    }

    /**
     * Fetch product number from every rows
     * @return array|null
     */
    public function get_all_product_number() {
        $ret = [];
        foreach ($this->rows as $row) {
            array_push($ret, $row[$this->columnCode]);
        }
        return array_unique($ret);
    }

    /**
     * Return the array of visible product's code (Product Visible = "Y")
     * @return array
     */
    public function get_visible_product() {
        $ret = [];
        foreach ($this->rows as $row) {
            if (strtoupper($row[$this->columnVisible]) == "Y") {
                array_push($ret, $row[$this->columnCode]);
            }
        }
        return array_unique($ret);
    }

    /**
     * Update the obsoleted product's visibility to "N"
     * @param array $obsoleted_product_codes
     * @return int
     */
    public function update_obsoleted_product(array $obsoleted_product_codes) {
        $count = 0;
        foreach ($this->rows as $row) {
            if (array_search($row[$this->columnCode], $obsoleted_product_codes) !== false) {
                $row[$this->columnVisible] = "N";
                $count++;
            }
        }
        return $count;
    }

    /**
     * Check if product from csv need to update with product from database
     * @param $active_product
     * @param $this_product
     * @param $product_number
     * @param $meta_description
     * @param $product_description
     * @param $cost
     * @param $search_keyword
     * @return bool
     */
    private function is_product_changed($active_product, $this_product, $product_number, $meta_description, $product_description, $cost, $search_keyword) {
        if ($this_product[$this->columnName] != $product_number ||
            $this_product[$this->columnBrandName] != $active_product->Brand ||
            $this_product[$this->columnProductDescription] != $product_description ||
            $this_product[$this->columnPrice] != $active_product->MAP ||
            $this_product[$this->columnPrice] != $cost ||
            $this_product[$this->columnProductWeight] != $active_product->Weight ||
            $this_product[$this->columnProductWidth] != $active_product->Width ||
            $this_product[$this->columnProductHeight] != $active_product->Height ||
            $this_product[$this->columnProductDepth] != $active_product->Length ||
            $this_product[$this->columnCurrentStockLevel] != $active_product->Qty ||
            $this_product[$this->columnSearchKeywords] != $search_keyword ||
            $this_product[$this->columnMetaDescription] != $meta_description ||
            $this_product[$this->columnProductImageDescription1] != $active_product->Title) {
            return true;
        }
        return false;
    }

    /**
     * Check and update the products from csv with products from database
     * @param array $active_products - array of product objects from database
     * @return int
     */
    public function update_changed_product(array $active_products) {
        $count = 0;
        foreach ($active_products as $active_product) {
            $this_product = $this->get_product_from_code($active_product->ProductNumber);

            // Generate the required column
            $product_number = $active_product->ProductNumber . " - " . $active_product->Title;
            $meta_description = str_replace("\r\n\r\n", "\r\n\r\n\r\n\r\n", $active_product->Description);
            $product_description = "<h2>" . $active_product->Title . "</h2><p>" . $meta_description . "</p>";
            $cost = jobber_price($active_product);
            $search_keyword = $active_product->Title . "," . $active_product->Title;

            if ($this_product != null && $this->is_product_changed($active_product, $this_product, $product_number, $meta_description, $product_description, $cost, $search_keyword)) {
                $this_product[$this->columnName] = $product_number;
                $this_product[$this->columnBrandName] = $active_product->Brand;
                $this_product[$this->columnProductDescription] = $product_description;
                $this_product[$this->columnPrice] = $active_product->MAP;
                $this_product[$this->columnPrice] = $cost;
                $this_product[$this->columnProductWeight] = $active_product->Weight;
                $this_product[$this->columnProductWidth] = $active_product->Width;
                $this_product[$this->columnProductHeight] = $active_product->Height;
                $this_product[$this->columnProductDepth] = $active_product->Length;
                $this_product[$this->columnCurrentStockLevel] = $active_product->Qty;
                $this_product[$this->columnSearchKeywords] = $search_keyword;
                $this_product[$this->columnMetaDescription] = $meta_description;
                $this_product[$this->columnProductImageDescription1] = $active_product->Title;

                $cost++;
            }
        }
        return $count;
    }

    /**
     * Add product from database to CSV
     * @param array $new_products
     * @param $price_level
     */
    public function add_new_product(array $new_products, $price_level = null) {
        global $wpdb;

        foreach ($new_products as $new_product) {
            $meta_description = str_replace("\r\n\r\n", "\r\n\r\n\r\n\r\n", $new_product->Description);
            $product_description = "<h2>" . $new_product->Title . "</h2><p>" . $meta_description . "</p>";
            $cost = jobber_price($new_product, $price_level);
            $search_keyword = $new_product->Title . "," . $new_product->Title;

            $free_ships = $wpdb->get_results($wpdb->prepare("SELECT  * FROM randys_freeship WHERE ProductID = %d", $new_product->ProductID));

            $record = [
                "Product",                                                      // Item Type
                "",                                                             // Product ID
                $new_product->ProductNumber . " - " . $new_product->Title,      // Product Number
                "P",                                                            // Product Type
                $new_product->ProductNumber,                                    // Product Code
                "",                                                             // Bin Picking Number
                $new_product->Brand,                                            // Brand
                "",                                                             // Option Set
                "Right",                                                        // Option Set Align
                $product_description,                                           // Product Description
                $new_product->MAP,                                              // Price
                $cost,                                                          // Cost Price
                "0",                                                            // Retail Price
                "0",                                                            // Sale Price
                "0",                                                            // Fixed Shipping
                count($free_ships) > 0 ? "Y" : "N",                             // Free shipping
                "",                                                             // Product Warranty
                $new_product->Weight,                                           // Product Weight
                $new_product->Width,                                            // Product Width
                $new_product->Height,                                           // Product Height
                $new_product->Length,                                           // Product Depth
                "Y",                                                            // Allow Purchases
                "Y",                                                            // Product Visible
                "",                                                             // Product Availability
                "by product",                                                   // Track Inventory
                $new_product->Qty,                                              // Current Stock Level
                "0",                                                            // Low Stock Level
                $new_product->Category,                                         // Category
                "",                                                             // Product Image ID - 1
                image_url($new_product->FullImage),                             // Product Image File - 1
                $new_product->Title,                                            // Product Image Description - 1
                "Y",                                                            // Product Image Is Thumbnail - 1
                "0",                                                            // Product Image Sort - 1
                "",                                                             // Product Image ID - 2
                "",                                                             // Product Image File - 2
                "",                                                             // Product Image Description - 2
                "",                                                             // Product Image Is Thumbnail - 2
                "",                                                             // Product Image Sort - 2
                "",                                                             // Product Image ID - 3
                "",                                                             // Product Image File - 3
                "",                                                             // Product Image Description - 3
                "",                                                             // Product Image Is Thumbnail - 3
                "",                                                             // Product Image Sort - 3
                "",                                                             // Product Image ID - 4
                "",                                                             // Product Image File - 4
                "",                                                             // Product Image Description - 4
                "",                                                             // Product Image Is Thumbnail - 4
                "",                                                             // Product Image Sort - 4
                $search_keyword,                                                // Search Keywords
                "",                                                             // Page Title
                "",                                                             // Meta Keyword
                $meta_description,                                              // Meta Description
                "",                                                             // MYOB Asset
                "",                                                             // MYOB Income Acct
                "",                                                             // MYOB Expense Acct
                "New",                                                          // Product Condition
                "N",                                                            // Show Product Condition?
                "N",                                                            // Event Date Required?
                "Delivery Date",                                                // Event Date Name
                "N",                                                            // Event Date Is Limited?
                "0",                                                            // Event Date Start Date
                "0",                                                            // Event Date End Date
                "0",                                                            // Sort Order
                "Default Tax Class",                                            // Product Tax Class
                "",                                                             // Product UPC/EAN
                "N",                                                            // Stop Processing Rules
                "",                                                             // Product URL
                "",                                                             // Redirect Old URL?
                "",                                                             // GPS Global Trade Item Number
                "",                                                             // GPS Manufacturer Part Number
                "",                                                             // GPS Gender
                "",                                                             // GPS Age Group
                "",                                                             // GPS Color
                "",                                                             // GPS Size
                "",                                                             // GPS Material
                "",                                                             // GPS Pattern
                "",                                                             // GPS Item Group ID
                "",                                                             // GPS Category
                "N",                                                            // GPS Enabled

                // these 2 columns will not store in CSV
                "",                                                             // Avalara Product Tax Code
                ""                                                              // Product Custom Fields
            ];

            array_push($this->rows, $record);
        }
    }

    public function write_to_csv($file_path) {
        $write_headers = $this->headers;
        $remove_last_2_columns = false;

        if ($write_headers[count($write_headers) - 2] == self::AVALARA_PRODUCT_TAX_CODE && $write_headers[count($write_headers) - 1] == self::PRODUCT_CUSTOMER_FILEDS) {
            // Remove the last 2 columns
            $remove_last_2_columns = true;
            array_pop($write_headers);
            array_pop($write_headers);
        }

        $result = implode(",", $write_headers) . "\n";

        foreach ($this->rows as $row) {
            $write_columns = $row;
            if ($remove_last_2_columns) {
                // Remove the last 2 columns
                array_pop($write_columns);
                array_pop($write_columns);
            }

            $column_count = count($write_columns);
            foreach ($write_columns as $key => $column) {
                if ($key < $column_count - 1) {
                    $result .= escape_string_for_csv($column) . ",";
                } else {
                    $result .= escape_string_for_csv($column) . "\n";
                }
            }
        }
        file_put_contents($file_path, $result);
    }
}


class Big_Commerce_Updater {

    public static $last_error = "";
    public static $message = "";

    /**
     * Get all the products that customer can access
     * @param null $customer_number - optional, get the current logged in customer if $customer_number = null
     * @return array|null|object
     */
    private static function customer_available_products($customer_number = null) {
        global $wpdb;

        if ($customer_number == null) {
            $current_user = wp_get_current_user();

            return $wpdb->get_results(
                     $wpdb->prepare(
                        "SELECT p1.ProductID, p1.ProductNumber, p1.Title, p1.Description, p1.Brand, p2.List, p2.Price, p2.MAP,
                        p2.P4, p2.P5, p2.P6, p2.Weight, p2.Width, p2.Height, p2.Length, p2.Qty, c1.CategoryName as Category, p2.FullImage
                        FROM randys_productsearch as p1
                        RIGHT JOIN randys_product as p2 ON p1.ProductID = p2.ProductID
                        RIGHT JOIN randys_category as c1 ON p1.CategoryID = c1.CategoryID
                        WHERE p1.Brand in(SELECT b.BrandName FROM randys_brands as b
                        INNER JOIN randys_customerbrands as cb ON b.BrandID = cb.BrandID
                        INNER JOIN randys_customers as c ON cb.CustomerID = c.CUSTNMBR
                        WHERE c.WOOCustID = %d)",
                        $current_user->ID
                     )
            );
        } else {

            return $wpdb->get_results(
                     $wpdb->prepare(
                        "SELECT p1.ProductID, p1.ProductNumber, p1.Title, p1.Description, p1.Brand, p2.List, p2.Price, p2.MAP,
                        p2.P4, p2.P5, p2.P6, p2.Weight, p2.Width, p2.Height, p2.Length, p2.Qty, c1.CategoryName as Category, p2.FullImage
                        FROM randys_productsearch as p1
                        RIGHT JOIN randys_product as p2 ON p1.ProductID = p2.ProductID
                        RIGHT JOIN randys_category as c1 ON p1.CategoryID = c1.CategoryID
                        WHERE p1.Brand in(SELECT b.BrandName FROM randys_brands as b
                        INNER JOIN randys_customerbrands as cb ON b.BrandID = cb.BrandID
                        INNER JOIN randys_customers as c ON cb.CustomerID = c.CUSTNMBR
                        WHERE c.CUSTNMBR = %s)",
                        $customer_number
                     )
            );
        }
    }

    /**
     * Create a Big Commerce CSV file based on the give customer number
     * @param $customer_number
     * @param $price_level
     * @return string - a path to created Big Commerce file
     */
    public static function generate_big_commerce_csv($customer_number, $price_level) {
        $sent = new Big_Commerce_Data();
        $sent->set_default_header();

        $products = self::customer_available_products($customer_number);

        if (count($products) == 0) {
            self::$last_error = "You do not have access to any Brand or Product";
            return null;
        }

        $sent->add_new_product($products, $price_level);

        // Write to file
        $temp_file = create_temp_file('BCU');
        $sent->write_to_csv($temp_file);

        self::$message = "<b>File Contents</b><br />New products: " . count($products);
        return $temp_file;
    }

    /**
     *
     * @param $file - a file object of uploaded file
     * @return string|null - a path to updated Big Commerce file
     */
    public static function update_big_commerce_csv($file) {
        self::$last_error = "";

        $file_path = $file['tmp_name'];
        if (!file_exists($file_path)) {
            self::$last_error = "File not exists";
            return null;
        }

        $file = fopen($file_path, "r");
        if ($file === false) {
            self::$last_error = "Could not open file";
            return null;
        }

        $sent = new Big_Commerce_Data();

        // Load file specified
        while (($columns = fgetcsv($file, 8056)) !== false) {
            if ($sent->has_header() == false) { // hasn't been set yet, this is the header row
                if ($sent->has_require_fields($columns)) {
                    $sent->set_header($columns);
                } else {
                    self::$last_error = "Malformat CSV";
                    return null;
                }
            } else {
                if (strtoupper($columns[0]) == "PRODUCT") {
                    $sent->add_row($columns);
                }
            }
        }
        fclose($file);

        // Trim obsolete and non-RRP products from items sent
        $sentRrp = $sent->get_visible_product();

        // Get active RRP products for this customer
        $active_products = self::customer_available_products();
        $active_product_numbers = [];
        foreach ($active_products as $product) {
            array_push($active_product_numbers, $product->ProductNumber);
        }

        // array of active product's numbers
        $active_product_numbers = array_unique($active_product_numbers);

        // array of obsoleted product's number
        $obsolete = array_diff($sentRrp, $active_product_numbers);

        // array of new products => active products not in file
        $newItems = array_diff($active_product_numbers, $sentRrp);

        // Mark obsoleted products as "N"
        $obsoleted_count = $sent->update_obsoleted_product($obsolete);

        // Changed products
        $changed_count = $sent->update_changed_product($active_product_numbers);

        // Add new products
        $new_products = array_filter($active_products, function ($product) use ($newItems) {
            return array_search($product->ProductNumber, $newItems) !== false;
        });
        $sent->add_new_product($new_products);

        // Write to file
        $temp_file = create_temp_file('BCU');
        $sent->write_to_csv($temp_file);

        self::$message = "<b>File Contents</b><br />New products: " . count($newItems) . "<br />Updated products: " . $changed_count . "<br />Obsolete products: " . count($obsolete);
        return $temp_file;
    }

}
