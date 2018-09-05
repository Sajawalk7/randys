<?php

class Customer_Access {
    const LEVEL_UNKNOWN = 0;
    const LEVEL_NOT_LOGGED_IN = 0;
    const LEVEL_BASIC_ACCESS = 1;       // listed in randys_customers, can download image
    const LEVEL_DATA_ACCESS = 2;        // listed in randys_xmlcustomers, can download product data
    const LEVEL_BIG_COMMERCE_ACCESS = 3;// listed in randys_xmlcustomers and BigCommerce == 1, can import / export

    const CLASS_UNKNOWN = 0;
    const CLASS_WHOLESALE = 1;          // has CUSTCLAS = 'WHOLESALE' in randys_customers
    const CLASS_RETAIL = 2;             // has CUSTCLAS = 'RETAIL' in randys_customers
    // TODO: other customer classes

    const RELATION_UNKNOWN = 0;
    const RELATION_PARENT = 1;          // has ParentID == CUSTNMBR in randys_customers
    const RELATION_CHILD = 2;           // has a ParentID != CUSTNMBR in randys_customers

    private static $access_level = Customer_Access::LEVEL_UNKNOWN;
    private static $customer_class = Customer_Access::CLASS_UNKNOWN;
    private static $customer_relation = Customer_Access::RELATION_UNKNOWN;

    /**
     * Get the current customer's access level
     * @return int (AccessLevel)
     */
    public static function current_access_level() {
        if (self::$access_level == Customer_Access::LEVEL_UNKNOWN) {
            self::load_current_customer();
        }
        return self::$access_level;
    }

    public static function current_class() {
        if (self::$customer_class == Customer_Access::CLASS_UNKNOWN) {
            self::load_current_customer();
        }
        return self::$customer_class;
    }

    public static function current_relationship() {
        if (self::$customer_relation == Customer_Access::RELATION_UNKNOWN) {
            self::load_current_customer();
        }
        return self::$customer_relation;
    }

    private static function load_current_customer() {
        self::$access_level = Customer_Access::LEVEL_NOT_LOGGED_IN;
        self::$customer_class = Customer_Access::CLASS_UNKNOWN;
        self::$customer_relation = Customer_Access::RELATION_UNKNOWN;

        $user = wp_get_current_user();

        if ($user->exists() == false) {
            return;
        }

        global $wpdb;

        $customer = $wpdb->get_row(
            $wpdb->prepare("SELECT CUSTNMBR, CUSTCLAS, ParentID FROM randys_customers WHERE WOOCustID = %d", $user->ID)
        );

        if ($customer === null) {
            return;
        }

        switch ($customer->CUSTCLAS) {
            case "WHOLESALE":
              self::$customer_class = Customer_Access::CLASS_WHOLESALE;
              self::$access_level = Customer_Access::LEVEL_BASIC_ACCESS;
              break;
            case "RETAIL":
              self::$customer_class = Customer_Access::CLASS_RETAIL;
              break;
        }

        self::$customer_relation = $customer->CUSTNMBR === $customer->ParentID ? Customer_Access::RELATION_PARENT : Customer_Access::RELATION_CHILD;

        $brands_customer = $wpdb->get_row(
            $wpdb->prepare("SELECT BrandID FROM randys_customerbrands WHERE CustomerID = %s", $customer->CUSTNMBR)
        );

        $xml_customer = $wpdb->get_row(
            $wpdb->prepare("SELECT BigCommerce FROM randys_xmlcustomers WHERE CustomerID = %s", $customer->CUSTNMBR)
        );

        if ($brands_customer) {
          self::$access_level = Customer_Access::LEVEL_DATA_ACCESS;
        }

        if ($xml_customer && $xml_customer->BigCommerce) {
          self::$access_level = Customer_Access::LEVEL_BIG_COMMERCE_ACCESS;
        }
    }

}
