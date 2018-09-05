<?php

namespace Cartonization;

class Box
{
    public $name = '';
    public $length = 0;
    public $width = 0;
    public $height = 0;
    public $weight = 0.0;
    public $capacity = 0.0;
    public $items = array();  // array of ProductInfo objects
}

class ProductInfo
{
    public $id = 0;
    public $cartItemKey = "";
    public $cartItem = null;
    public $productNumber = '';
    public $title = '';
    public $length = 0.0;
    public $width = 0.0;
    public $height = 0.0;
    public $weight = 0.0;
    public $quantity = 0;
    public $warehouse = '';
}

class ShipmentInfo
{
    // Warehouse code (WA, CA, KY, TN, TX) will be used.
    public $warehouse = '';
    // Fill with array of shipment boxes
    public $boxes = array();  // array of Box objects

    /**
     * @return array Array of ProductInfo objects
     */
    public function get_products_in_shipment()
    {
        $products = array();

        foreach($this->boxes as $box)
        {
            foreach($box->items as $item)
            {
                if(array_key_exists($item->id, $products))
                {
                    $products[$item->id]->quantity += $item->quantity;
                }
                else
                {
                    $products[$item->id] = clone $item;
                }
            }
        }

        return $products;
    }
}

class FillBox
{
    public $removedItems = array();
    public $shipmentInfo = null;
}



class CartonizationEngine
{

    // static use only
    private function __construct() {}

/**
* Meant to be called with:
*     WC()->cart->get_cart(), WC()->customer->get_shipping_state()
* Expected first cartItems input:
* values used:
*  - key
*  - product_id
*  - quantity
*  - line_total
*  - data->post->post_name
*  - data->post->post_title
*
* Array
* (
*     [8d1de7457fa769ece8d93a13a59c8552] => Array
*         (
*             [product_id] => 8205
*             [variation_id] => 0
*             [variation] => Array
*                 (
*                 )
*
*             [quantity] => 1
*             [line_total] => 56.95
*             [line_tax] => 0
*             [line_subtotal] => 56.95
*             [line_subtotal_tax] => 0
*             [line_tax_data] => Array
*                 (
*                     [total] => Array
*                         (
*                         )
*
*                     [subtotal] => Array
*                         (
*                         )
*
*                 )
*
*             [data] => WC_Product_Simple Object
*                 (
*                     [id] => 8205
*                     [post] => WP_Post Object
*                         (
*                             [ID] => 8205
*                             [post_author] => 0
*                             [post_date] => 0000-00-00 00:00:00
*                             [post_date_gmt] => 0000-00-00 00:00:00
*                             [post_content] => 7/8" bronze bearing race punch, 13" long. No return.
*                             [post_title] => 7/8" bronze bearing race punch
*                             [post_excerpt] =>
*                             [post_status] => publish
*                             [comment_status] => open
*                             [ping_status] => open
*                             [post_password] =>
*                             [post_name] => yt-p21
*                             [to_ping] =>
*                             [pinged] =>
*                             [post_modified] => 0000-00-00 00:00:00
*                             [post_modified_gmt] => 0000-00-00 00:00:00
*                             [post_content_filtered] =>
*                             [post_parent] => 0
*                             [guid] =>
*                             [menu_order] => 0
*                             [post_type] => product
*                             [post_mime_type] =>
*                             [comment_count] => 0
*                             [filter] => raw
*                         )
*
*                     [product_type] => simple
*                     [shipping_class:protected] =>
*                     [shipping_class_id:protected] => 0
*                     [total_stock] =>
*                     [supports:protected] => Array
*                         (
*                             [0] => ajax_add_to_cart
*                         )
*
*                     [price] => 59.98
*                     [tax_status] => taxable
*                     [tax_class] =>
*                 )
*
*         )
*
*     [2eacc82231f2e62f9acb38bece54635e] => Array
*         (
*             [product_id] => 6156
*             [variation_id] => 0
*             [variation] => Array
*                 (
*                 )
*
*             [quantity] => 1
*             [line_total] => 962.61
*             [line_tax] => 0
*             [line_subtotal] => 962.61
*             [line_subtotal_tax] => 0
*             [line_tax_data] => Array
*                 (
*                     [total] => Array
*                         (
*                         )
*
*                     [subtotal] => Array
*                         (
*                         )
*
*                 )
*
*             [data] => WC_Product_Simple Object
*                 (
*                     [id] => 6156
*                     [post] => WP_Post Object
*                         (
*                             [ID] => 6156
*                             [post_author] => 0
*                             [post_date] => 0000-00-00 00:00:00
*                             [post_date_gmt] => 0000-00-00 00:00:00
*                             [post_content] => Trac Loc positraction for 10.5" Ford Extra HD (full-floating only) with 3 pinion. The Trac Loc is a less aggressive, street-friendly limited slip suitable for daily driven vehicles and mild off-road use. This unit uses clutches and is Rebuildable. All units come standard with a one year warranty against manufacturing defects. This unit fits full floating applications only.
*                             [post_title] => Trac Loc positraction for 10.5" Ford Extra HD (full-floating only) with 3 pinion.
*                             [post_excerpt] =>
*                             [post_status] => publish
*                             [comment_status] => open
*                             [ping_status] => open
*                             [post_password] =>
*                             [post_name] => forf105506
*                             [to_ping] =>
*                             [pinged] =>
*                             [post_modified] => 0000-00-00 00:00:00
*                             [post_modified_gmt] => 0000-00-00 00:00:00
*                             [post_content_filtered] =>
*                             [post_parent] => 0
*                             [guid] =>
*                             [menu_order] => 0
*                             [post_type] => product
*                             [post_mime_type] =>
*                             [comment_count] => 0
*                             [filter] => raw
*                         )
*
*                     [product_type] => simple
*                     [shipping_class:protected] =>
*                     [shipping_class_id:protected] => 0
*                     [total_stock] =>
*                     [supports:protected] => Array
*                         (
*                             [0] => ajax_add_to_cart
*                         )
*
*                     [price] => 1061.18
*                     [tax_status] => taxable
*                     [tax_class] =>
*                 )
*
*         )
*
*
* )
*
*
* @return array Array of ShipmentInfo objects
*
*/
public static function getBoxesForOrder(array $cartItems, /* string */ $destinationStateCode)
{
    // Initialize collections for shipments.
    $results = array();

    if($destinationStateCode)
    {
        // Database connection values
        $dbserver = DB_HOST;
        $dbusername = DB_USER;
        $dbpassword = DB_PASSWORD;
        $dbname = DB_NAME;

        /*
        *   -----------PRODUCT DETAILS-----------
        */
        // Note: This method is using a generic PDO query against the database.
        // This can be updated to use whatever ORM desired.

        // Get details needed about products from database.
        $queryProducts = "SELECT ProductID, ProductNumber, Title, Length, Width, Height, Weight";
        $queryProducts .= " FROM randys_product";
        $queryProducts .= " WHERE ProductID IN (";

        $queryParameters = array();
        $parameterTypes = array();

        // Use list of product Ids for IN clause
        $firstItem = true;
        foreach($cartItems as $item)
        {
            // grab productId from cart item meta data using the cart item post id
            $tempProductId = get_post_meta($item['product_id'])['_randy_productid'][0];

            if($firstItem == false)
            {
                $queryProducts .= ", ?";
                array_push($queryParameters, $tempProductId);
                array_push($parameterTypes, "int");
            }
            else
            {
                $queryProducts .= "?";
                array_push($queryParameters, $tempProductId);
                array_push($parameterTypes, "int");
                $firstItem = false;
            }
        }

        $queryProducts .= ")";

        $resultProducts = array();

        try
        {
            $db = new \PDO('mysql:host=' . $dbserver . ';dbname=' . $dbname . ';charset=utf8', '' . $dbusername . '', '' . $dbpassword . '');
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $search = $db->prepare($queryProducts);

            // Iterate through query params and bind. Set type based on
            // parameter type in array.
            for($i = 0; $i < count($queryParameters); $i++)
            {
                // Switch used in event new types are needed later
                switch($parameterTypes[$i])
                {
                    case "string":
                        $search->bindValue($i + 1, $queryParameters[$i], \PDO::PARAM_STR);
                        break;

                    case "int":
                        $search->bindValue($i + 1, $queryParameters[$i], \PDO::PARAM_INT);
                        break;
                }
            }

            $search->execute();
            $resultProducts = $search->fetchAll(\PDO::FETCH_ASSOC);
        }
        catch (\PDOException $e)
        {
            die("Error executing database query: " . $e->getMessage());
        }

        $productsByWarehouse = array();

        // iterate through product details
        foreach($resultProducts as $detail)
        {
            $product = new ProductInfo;
            $product->id = intval($detail['ProductID']);
            $product->productNumber = $detail['ProductNumber'];
            $product->title = $detail['Title'];
            $product->length = floatval($detail['Length']);
            $product->width = floatval($detail['Width']);
            $product->height = floatval($detail['Height']);
            $product->weight = floatval($detail['Weight']);

            // Find quantity in initial list and assign to object.
            $isAssigned = false;
            while(!$isAssigned)
            {
                foreach($cartItems as $key => $item)
                {
                    // grab productId from cart item meta data using the cart item post id
                    $tempProductId = get_post_meta($item['product_id'])['_randy_productid'][0];

                    if ($tempProductId == $product->id)
                    {
                        // assign quantity to product
                        $product->quantity = $item["quantity"];

                        // also assign cart key
                        $product->cartItemKey = $key;

                        // save WooCommerce cart item for later use by WooCommerce
                        $product->cartItem = $item;
                    }
                    $isAssigned = true;
                }
            }

            // get warehouse that has enough inventory for product
            $queryInventoryByWarehouse = "SELECT Item, Warehouse, Qty FROM randys_InventoryByWarehouse WHERE Item = (?) AND Qty > 0";
            $locations = array();

            try
            {
                $db = new \PDO('mysql:host=' . $dbserver . ';dbname=' . $dbname . ';charset=utf8', '' . $dbusername . '', '' . $dbpassword . '');
                $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

                $searchInventoryByWarehouse = $db->prepare($queryInventoryByWarehouse);
                $searchInventoryByWarehouse->bindValue(1, $product->productNumber, \PDO::PARAM_STR);
                $searchInventoryByWarehouse->execute();
                $resultsInventoryByWarehouse = $searchInventoryByWarehouse->fetchAll(\PDO::FETCH_ASSOC);

                foreach($resultsInventoryByWarehouse as $inv)
                {
                    array_push($locations, $inv['Warehouse']);
                }
            }
            catch (\PDOException $e)
            {
                die("Error executing database query: " . $e->getMessage());
            }

            // if there are inventory in any of the warehouses
            if($resultsInventoryByWarehouse != null)
            {
                // Get prioritized warehouses to ship from based on destination
                // state code.
                $queryWarehouse = "SELECT warehouse FROM randys_StateShippingOrder WHERE state = ? ORDER BY priority ASC";
                $product->warehouse = 'OOS'; // If none are found, then the product is out of stock at all available warehouses

                try
                {
                    // orders shipping to US and CA ship from warehouses listed in randys_StateShippingOrder
                    $db = new \PDO('mysql:host=' . $dbserver . ';dbname=' . $dbname . ';charset=utf8', '' . $dbusername . '', '' . $dbpassword . '');
                    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

                    $warehouseSearch = $db->prepare($queryWarehouse);
                    $warehouseSearch->bindValue(1, $destinationStateCode, \PDO::PARAM_STR);
                    $warehouseSearch->execute();
                    $warehouseResults = $warehouseSearch->fetchAll(\PDO::FETCH_ASSOC);

                    // "Per our Distribution Director all Non-US and Non-Canada only ship out of WA due to international paperwork."
                    if (empty($warehouseResults)) {
                        $warehouseResults = array(array('warehouse' => 'WA'));
                    }

                    // Loop through each warehouse
                    foreach($warehouseResults as $priorityWarehouse)
                    {
                        if(in_array($priorityWarehouse['warehouse'], $locations))
                        {
                            // get available quantities at current warehouse
                            $key = array_search($priorityWarehouse['warehouse'], array_column($resultsInventoryByWarehouse, 'Warehouse'));
                            $availableQuantity = $resultsInventoryByWarehouse[$key]['Qty'];

                            if ( $availableQuantity >= $product->cartItem['quantity'] ) {
                              // assign all of requested quantity to this warehouse
                              $product->warehouse = $priorityWarehouse['warehouse'];
                              break;
                            } else {
                              // assign only some of requested quantity to this warehouse
                              $productClone = clone $product;
                              $product->cartItem['quantity'] -= $availableQuantity;
                              $product->quantity -= $availableQuantity;
                              $product->cartItem['line_subtotal'] = $product->quantity * $product->cartItem['data']->get_price();
                              $product->cartItem['line_total'] = $product->cartItem['line_subtotal'];

                              // assign clone all of the available inventory
                              $productClone->cartItem['quantity'] = $availableQuantity;
                              $productClone->quantity = $availableQuantity;
                              $productClone->cartItem['line_subtotal'] = $productClone->quantity * $productClone->cartItem['data']->get_price();
                              $productClone->cartItem['line_total'] = $productClone->cartItem['line_subtotal'];

                              // assign clone to warehouse
                              $productClone->warehouse = $priorityWarehouse['warehouse'];

                              // Add clone product into product details
                              if(!array_key_exists($productClone->warehouse, $productsByWarehouse))
                              {
                                  $productsByWarehouse[$productClone->warehouse] = array();
                              }

                              array_push($productsByWarehouse[$productClone->warehouse], $productClone);
                            }

                        }
                    }
                }
                catch (\PDOException $e)
                {
                    die("Error executing database query: " . $e->getMessage());
                }
            }
            else // else there is no inventory in any of the warehouses
            {
                // Out of stock
                $product->warehouse = 'OOS';
            }

            // Add product into product details
            $productArrKey = $product->warehouse;
            $productArrVal = clone $product;

            if(!array_key_exists($productArrKey, $productsByWarehouse))
            {
                $productsByWarehouse[$productArrKey] = array();
            }

            array_push($productsByWarehouse[$productArrKey], $productArrVal);
        }
        /*
        *   -----------------END-----------------
        */

        /*
        *   Move OOS to the end of array
        */
        if ( array_key_exists('OOS', $productsByWarehouse) ) {
          $OOS = $productsByWarehouse['OOS'];
          unset($productsByWarehouse['OOS']);
          $productsByWarehouse['OOS'] = $OOS;
        }

        /*
        *   ------WAREHOUSE BOXES STRUCTURE------
        */
        $warehouseBoxes = array();

        // iterate through shipping details
        foreach($productsByWarehouse as $warehouse => $products)
        {
            if ($warehouse != 'OOS')
            {
                // setup query for getting boxes based on warehouse
                $queryBoxes = "SELECT * FROM randys_warehouseboxes WHERE State = ?";
                $resultBoxes = array();

                try
                {
                    $db = new \PDO('mysql:host=' . $dbserver . ';dbname=' . $dbname . ';charset=utf8', '' . $dbusername . '', '' . $dbpassword . '');
                    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

                    $searchBoxes = $db->prepare($queryBoxes);
                    $searchBoxes->bindValue(1, $warehouse, \PDO::PARAM_STR);
                    $searchBoxes->execute();
                    $resultBoxes = $searchBoxes->fetchAll(\PDO::FETCH_ASSOC);
                    $resultBoxesPretty = array();

                    foreach($resultBoxes as $box)
                    {
                        $tempBox = new Box;
                        $tempBox->name = $box['Name'];
                        $tempBox->length = intval($box['Length']);
                        $tempBox->width = intval($box['Width']);
                        $tempBox->height = intval($box['Height']);
                        $tempBox->weight = floatval($box['Weight']);
                        $tempBox->capacity = floatval($box['Capacity']);

                        array_push($resultBoxesPretty, $tempBox);
                    }
                }
                catch (\PDOException $e)
                {
                    die("Error executing database query: " . $e->getMessage());
                }

                // add to warehouseBoxes array structure
                $arrKey = $warehouse;
                $arrVal = $resultBoxesPretty;

                if(!array_key_exists($arrKey, $warehouseBoxes))
                {
                    $warehouseBoxes[$arrKey] = $arrVal;
                }
            }
        }
        /*
        *   -----------------END-----------------
        */



        /*
        * --------------PROCESSING-------------
        */
        // process order by warehouse
        foreach($productsByWarehouse as $warehouse => $products)
        {
            if($warehouse != 'OOS')
            {
                // get products associated to the current warehouse
                $boxes = $warehouseBoxes[$warehouse];

                // sort order items
                usort($products, 'self::sortItems');

                while(!empty($products)) // while there are items to process
                {
                    // get the best/smallest box
                    $tempBestBox = self::bestBox($products, $boxes);

                    // if entire order fits in box then add it all to box
                    if(self::fitsBox($products, $tempBestBox))
                    {
                        // assume shipment does not exist
                        $shipmentExists = false;
                        $shipmentIndex = null;

                        foreach($results as $key => $shipment)
                        {
                            if($shipment->warehouse == $warehouse)
                            {
                                $shipmentExists = true;
                                $shipmentIndex = $key;
                            }
                        }

                        // if yes then add new box to shipment
                        if($shipmentExists)
                        {
                            // add products to box
                            foreach($products as $product)
                            {
                                array_push($tempBestBox->items, $product);
                            }

                            // add box to shipment details
                            array_push($results[$shipmentIndex]->boxes, $tempBestBox);
                        }
                        // if not then add the new shipment to results
                        else
                        {
                            // new shipment details
                            $shipmentDetail = new ShipmentInfo;
                            $shipmentDetail->warehouse = $warehouse;
                            $shipmentDetail->boxes = array();

                            // add products to box
                            foreach($products as $product)
                            {
                                array_push($tempBestBox->items, $product);
                            }

                            // add box to shipment detail
                            array_push($shipmentDetail->boxes, $tempBestBox);

                            // add shipment detail to results
                            array_push($results, $shipmentDetail);
                        }

                        // empty order
                        $products = array();
                    }
                    else
                    {
                        // not all parts fit in bestbox so we fill box with the parts we can then add
                        // this as another shipment, then continue with the rest of the items.
                        $fillBestBox = self::fillBox($boxes, $tempBestBox, $products);

                        // remove the items that were filled in above box
                        foreach($fillBestBox->removedItems as $ri)
                        {
                            // the check is done with two for loops because each element in array
                            // could be different ProductInfo objects so checking if it was
                            // just in the array was not enough to verify it was a match
                            foreach($products as $item)
                            {
                                if($ri->id == $item->id)
                                {
                                    $tempPos = array_search($item, $products);
                                    // remove item from order
                                    // if quantity matches then we remove all quantities otherwise we update quantity
                                    if($ri->quantity == $item->quantity)
                                    {
                                        array_splice($products, $tempPos, 1);
                                    }
                                    else
                                    {
                                        // update quantity
                                        $tempItem = clone $item;
                                        $tempItem->quantity = $ri->quantity;

                                        $products[$tempPos] = $tempItem;
                                    }
                                }
                            }
                        }

                        // assume shipment does not exist
                        $shipmentExists = false;
                        $shipmentIndex = null;

                        foreach($results as $key => $shipment)
                        {
                            if($shipment->warehouse == $warehouse)
                            {
                                $shipmentExists = true;
                                $shipmentIndex = $key;
                            }
                        }

                        // if yes then add new box to shipment
                        if($shipmentExists)
                        {
                            // there will not ever be more than one box that comes back from fillBox() function so we grab the first element in array
                            $boxToAdd = clone $fillBestBox->shipmentInfo->boxes[0];

                            // add box to shipment details
                            array_push($results[$shipmentIndex]->boxes, $boxToAdd);
                        }
                        // if not then add the new shipment to results
                        else
                        {
                            // new shipment details
                            $shipmentDetail = new ShipmentInfo;
                            $shipmentDetail->warehouse = $warehouse;
                            $shipmentDetail->boxes = $fillBestBox->shipmentInfo->boxes;

                            // add shipment detail to results
                            array_push($results, $shipmentDetail);
                        }
                    }
                }
            }
            else
            {
                // out of stock box
                $outOfStockBox = new Box;
                $outOfStockBox->name = "Out of Stock";
                $outOfStockBox->items = $products;

                // out of stock shipment details OR available but not at the priority warehouse
                $outOfStockShipmentDetail = new ShipmentInfo;
                $outOfStockShipmentDetail->warehouse = $warehouse;
                array_push($outOfStockShipmentDetail->boxes, $outOfStockBox);

                array_push($results, $outOfStockShipmentDetail);
            }
        }
        /*
        *   -----------------END-----------------
        */
    }
    else
    {
        // No destination selected shipment detail (to help show the cart contents even when destination is not available)
        // Products
        $noDestProducts = array();

        foreach($cartItems as $key => $item)
        {
            $product = new ProductInfo;
            $product->id = intval(get_post_meta($item['product_id'])['_randy_productid'][0]);
            $product->cartItemKey = $key;
            $product->productNumber = $item['data']->post->post_name;
            $product->title = $item['data']->post->post_title;
            $product->quantity = $item['quantity'];

            array_push($noDestProducts, $product);
        }

        // Box
        $noDestBox = new Box;
        $noDestBox->name = "No Destination";
        $noDestBox->items = $noDestProducts;

        // Shipment
        $noDestShipmentDetail = new ShipmentInfo;
        $noDestShipmentDetail->warehouse = 'NOD';
        array_push($noDestShipmentDetail->boxes, $noDestBox);

        array_push($results, $noDestShipmentDetail);
    }

    return $results;
}

// Helper functions (process boxing)
/**
 * @return Box
 */
private static function bestBox(array $wholeOrder, array $boxes)
{

    // sort boxes (smallest to largest)
    usort($boxes, 'self::sortBoxes');

    foreach($boxes as $box)
    {
        if(self::fitsBox($wholeOrder, $box))
        {
            return $box;
        }
    }

    // return largest box that fits order
    return self::largestPartBox($wholeOrder, $boxes);
}

/**
 * @return bool
 */
private static function fitsBox(array $order, Box $box)
{
    $retVal = false;

    if(($box->capacity >= self::orderWeight($order))
    && (self::boxVolume($box) >= self::orderVolume($order))
    && (self::boxLargestDimension($box) >= self::orderMaxDimension($order))
    && (self::boxMiddleDimension($box) >= self::orderMidDimension($order))
    && (self::boxSmallestDimension($box) >= self::orderMinDimension($order)))
    {
        $retVal = true;
    }

    return $retVal;
}

/*
 * @return Box
 */
private static function largestPartBox(array $order, array $boxes)
{
    usort($order, 'self::reverseSortItems'); // largest item first

    $tempItem = $order[0];
    $retVal = null;

    usort($boxes, 'self::reverseSortBoxes'); // largest box first

    $haveBox = false;

    foreach($boxes as $box)
    {
        if(self::fitsPart($box, $tempItem) && !$haveBox)
        {
            $retVal = $box;
            $haveBox = true;
        }
    }

    if (!$haveBox) {
        // item does not fit in any existing boxes; make a new box just for it
        $retVal = new Box;
        $retVal->name     = $tempItem->title;
        $retVal->length   = $tempItem->length;
        $retVal->width    = $tempItem->width;
        $retVal->height   = $tempItem->height;
        $retVal->weight   = 0;                 // The weight is the weight of the container, which there is none
        $retVal->capacity = $tempItem->weight;  // The capacity is the amount of weight it can carry, which will be this item
    }

    usort($boxes, 'self::sortBoxes'); // resort boxes to preserve logic for other functions

    return $retVal;
}


/**
 * @return FillBox
 */
private static function fillBox(array $boxes, Box $box, array $products)
{
    $fillThisBox = clone $box;

    // new shipment details
    $shipmentDetail = new ShipmentInfo;
    $shipmentDetail->boxes = array();

    // sort orders
    usort($products, 'self::reverseSortItems');

    $removedItems = array();

    $qty = 0;
    $i = 1;

    foreach($products as $item)
    {
        // can we fit all of this item into this box?
        if((self::lineItemWeight($item) <= self::boxRemainingCapacity($fillThisBox))
        && (self::lineItemVolume($item) <= self::boxRemainingVolume($fillThisBox))
        && (self::itemLargestDimension($item) <= self::boxLargestDimension($fillThisBox))
        && (self::itemMiddleDimension($item) <= self::boxMiddleDimension($fillThisBox))
        && (self::itemSmallestDimension($item) <= self::boxSmallestDimension($fillThisBox)))
        {
            $tempItem = clone $item;

            array_push($fillThisBox->items, $tempItem);
            array_push($removedItems, $tempItem);
        }
        // if not can we fit at least 1 of this item into this box?
        elseif ($item->quantity > 1)
        {
            $tempItem = clone $item;
            $qty = $tempItem->quantity;
            $i = 1;
            $whatWeFitInTheBox = null;

            while($i <= $qty)
            {
                if(($tempItem->weight <= self::boxRemainingCapacity($fillThisBox))
                && (self::itemVolume($tempItem) <= self::boxRemainingVolume($fillThisBox))
                && (self::itemLargestDimension($tempItem) <= self::boxLargestDimension($fillThisBox))
                && (self::itemMiddleDimension($tempItem) <= self::boxMiddleDimension($fillThisBox))
                && (self::itemSmallestDimension($tempItem) <= self::boxSmallestDimension($fillThisBox)))
                {
                    if ($i == 1)
                    {
                        $fillThisBox = self::boxUpdateQty($fillThisBox, $tempItem, 1);
                        $tempItem->quantity -= 1;
                    }
                    else
                    {
                        $fillThisBox = self::boxChangeQty($fillThisBox, $tempItem, 1);
                        $tempItem->quantity -= 1;
                    }

                    $whatWeFitInTheBox = $tempItem;
                }

                $i++;
            }

            if ($whatWeFitInTheBox) {
                array_push($removedItems, $whatWeFitInTheBox);
            }
        }
    }

    // add filled box to shipment
    // quick check to see if items can be in a smaller box since some items were not added
    // sort to get smallest box first
    usort($boxes, 'self::sortBoxes');
    $switchBox = false;

    if(count($fillThisBox->items) > 0)
    {
        foreach($boxes as $b)
        {
            if(($b->capacity >= self::boxUsedCapacity($fillThisBox))
            && (self::boxVolume($b) >= self::boxUsedVolume($fillThisBox))
            && (self::boxLargestDimension($b) >= self::boxItemsMaxDimension($fillThisBox))
            && (self::boxMiddleDimension($b) >= self::boxItemsMidDimension($fillThisBox))
            && (self::boxSmallestDimension($b) >= self::boxItemsMinDimension($fillThisBox))
            && (self::boxSortField($b) < self::boxSortField($fillThisBox)))
            {
                if(($b->name != $fillThisBox->name) && !$switchBox)
                {
                    $freshBox = new Box;
                    $freshBox->name = $b->name;
                    $freshBox->length = $b->length;
                    $freshBox->width = $b->width;
                    $freshBox->height = $b->height;
                    $freshBox->weight = $b->weight;
                    $freshBox->capacity = $b->capacity;
                    $freshBox->items = $fillThisBox->items;

                    // add box to shipment
                    array_push($shipmentDetail->boxes, $freshBox);
                    $switchBox = true;
                }
            }
        }

        if(!$switchBox)
        {
            // add box to shipment
            array_push($shipmentDetail->boxes, $fillThisBox);
        }
    }

    // prepare return values
    $retVal = new FillBox;
    $retVal->removedItems = $removedItems;
    $retVal->shipmentInfo = $shipmentDetail;

    return $retVal;
}



// Helper functions (order values)
/**
 * @return float
 */
private static function orderWeight(array $order)
{
    $retVal = 0.0;

    foreach($order as $item)
    {
        $retVal += $item->weight;
    }

    return $retVal;
}

/**
 * @return float
 */
private static function orderVolume(array $order)
{
    $retVal = 0.0;

    foreach($order as $item)
    {
        $itemVol = $item->width * $item->height * $item->length;
        $itemQty = $item->quantity;
        $lineItemVolume = $itemVol * $itemQty;

        $retVal += $lineItemVolume;
    }

    return $retVal;
}

/**
 * @return float
 */
private static function orderMaxDimension(array $order)
{
    $retVal = 0.0;

    foreach($order as $item)
    {
        $currentLargest = self::itemLargestDimension($item);

        if ($currentLargest > $retVal)
        {
            $retVal = $currentLargest;
        }
    }

    return $retVal;
}

/**
 * @return float
 */
private static function orderMidDimension(array $order)
{
    $retVal = 0.0;

    foreach($order as $item)
    {
        $currentMid = self::itemMiddleDimension($item);

        if($currentMid > $retVal)
        {
            $retVal = $currentMid;
        }
    }

    return $retVal;
}

/**
 * @return float
 */
private static function orderMinDimension(array $order)
{
    $retVal = 0.0;

    foreach($order as $item)
    {
        $currentMin = self::itemSmallestDimension($item);

        if($currentMin > $retVal)
        {
            $retVal = $currentMin;
        }
    }

    return $retVal;
}



// Helper functions (box values)
/**
 * @return float
 */
private static function boxVolume(Box $box)
{
    $retVal = $box->length * $box->width * $box->height;

    return $retVal;
}

/**
 * @return float
 */
private static function boxLargestDimension(Box $box)
{
    $tempArr = self::boxDimensionArray($box);

    return $tempArr[2];
}

/**
 * @return float
 */
private static function boxMiddleDimension(Box $box)
{
    $tempArr = self::boxDimensionArray($box);

    return $tempArr[1];
}

/**
 * @return float
 */
private static function boxSmallestDimension(Box $box)
{
    $tempArr = self::boxDimensionArray($box);

    return $tempArr[0];
}

/**
 * @return array
 */
private static function boxDimensionArray(Box $box)
{
    $tempArr = array();
    array_push($tempArr, $box->height);
    array_push($tempArr, $box->width);
    array_push($tempArr, $box->length);

    sort($tempArr);

    return $tempArr;
}

/**
 * @return bool
 */
private static function fitsPart(Box $box, ProductInfo $item)
{
    $retVal = false;

    if((self::boxLargestDimension($box) >= self::itemLargestDimension($item))
        && (self::boxMiddleDimension($box) >= self::itemMiddleDimension($item))
        && (self::boxSmallestDimension($box) >= self::itemSmallestDimension($item))
        && $box->capacity >= $item->weight
        && self::boxVolume($box) >= self::itemVolume($item))
    {
        $retVal = true;
    }

    return $retVal;
}

/**
 * @return float
 */
private static function boxRemainingCapacity(Box $box)
{
    $retVal = $box->capacity;

    foreach($box->items as $item)
    {
        $retVal = $retVal - self::lineItemWeight($item);
    }

    return $retVal;
}

/**
 * @return float
 */
private static function boxUsedCapacity(Box $box)
{
    $retVal = 0.0;

    foreach($box->items as $ib)
    {
        $retVal += self::lineItemWeight($ib);
    }

    return $retVal;
}

/**
 * @return float
 */
private static function boxRemainingVolume(Box $box)
{
    $retVal = self::boxVolume($box);

    foreach($box->items as $item)
    {
        $retVal = $retVal - self::lineItemVolume($item);
    }

    return $retVal;
}

/**
 * @return float
 */
private static function boxUsedVolume(Box $box)
{
    $retVal = 0;

    foreach($box->items as $ib)
    {
        $retVal += self::lineItemVolume($ib);
    }

    return $retVal;
}

/**
 * @return Box
 */
private static function boxUpdateQty(Box $box, ProductInfo $item, /* int */ $qty)
{
    $tempBox = clone $box;
    $tempItem = clone $item;

    $tempItem->quantity = $qty;
    array_push($tempBox->items, $tempItem);

    return $tempBox;
}

/**
 * @return Box
 */
private static function boxChangeQty(Box $box, ProductInfo $item, /* int */ $delta)
{
    $tempBox = $box;

    foreach($box->items as $bi)
    {
        if($bi->productNumber == $item->productNumber)
        {
            $bi->quantity += $delta;
        }
    }

    return $tempBox;
}

/**
 * @return float
 */
private static function boxItemsMaxDimension(Box $box)
{
    $retVal = 0;

    foreach($box->items as $bi)
    {
        if(self::itemLargestDimension($bi) > $retVal)
        {
            $retVal = self::itemLargestDimension($bi);
        }
    }

    return $retVal;
}

/**
 * @return float
 */
private static function boxItemsMidDimension(Box $box)
{
    $retVal = 0;

    foreach($box->items as $bi)
    {
        if(self::itemMiddleDimension($bi) > $retVal)
        {
            $retVal = self::itemMiddleDimension($bi);
        }
    }

    return $retVal;
}

/**
 * @return float
 */
private static function boxItemsMinDimension(Box $box)
{
    $retVal = 0;

    foreach($box->items as $bi)
    {
        if(self::itemSmallestDimension($bi) > $retVal)
        {
            $retVal = self::itemSmallestDimension($bi);
        }
    }

    return $retVal;
}

/**
 * @return float
 */
private static function boxSortField(Box $box)
{
    $retVal = $box->width * $box->height * $box->length * $box->capacity;

    return $retVal;
}



// Helper functions (item values)
/**
 * @return float
 */
private static function itemVolume(ProductInfo $item)
{
    $retVal = $item->length * $item->width * $item->height;

    return $retVal;
}

/**
 * @return float
 */
private static function itemLargestDimension(ProductInfo $item)
{
    $tempArr = self::itemDimensionArray($item);

    return $tempArr[2];
}

/**
 * @return float
 */
private static function itemMiddleDimension(ProductInfo $item)
{
    $tempArr = self::itemDimensionArray($item);

    return $tempArr[1];
}

/**
 * @return float
 */
private static function itemSmallestDimension(ProductInfo $item)
{
    $tempArr = self::itemDimensionArray($item);

    return $tempArr[0];
}

/**
 * @return array
 */
private static function itemDimensionArray(ProductInfo $item)
{
    $tempArr = array();
    array_push($tempArr, $item->height);
    array_push($tempArr, $item->width);
    array_push($tempArr, $item->length);

    sort($tempArr);

    return $tempArr;
}



// Helper functions (lineItem values)
/**
 * @return float
 */
private static function lineItemWeight(ProductInfo $item)
{
    return $item->weight * $item->quantity;
}

/**
 * @return float
 */
private static function lineItemVolume(ProductInfo $item)
{
    return $item->height * $item->length * $item->width * $item->quantity;
}



// Helper functions (sort)
/**
 * @return int
 */
private static function sortBoxes(Box $a, Box $b)
{
    $tempA = self::boxSortField($a);
    $tempB = self::boxSortField($b);

    if($tempA == $tempB)
    {
        return 0;
    }

    return ($tempA < $tempB) ? -1 : 1;
}

/**
 * @return int
 */
private static function reverseSortBoxes(Box $a, Box $b)
{
    return -1 * self::sortBoxes($a, $b);
}

/**
 * @return int
 */
private static function sortItems(ProductInfo $a, ProductInfo $b)
{
    $tempA = $a->weight;
    $tempB = $b->weight;

    if($tempA == $tempB)
    {
        return 0;
    }

    return ($tempA < $tempB) ? -1 : 1;
}

/**
 * @return int
 */
private static function reverseSortItems(ProductInfo $a, ProductInfo $b)
{
    return -1 * self::sortItems($a, $b);
}

}
