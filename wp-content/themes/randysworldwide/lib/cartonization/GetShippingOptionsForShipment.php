<?php

namespace Shipping;

// CONSTANTS
 define("SHIP_FACTOR", 1.10); // shipping factor used to calculate shipping
 define("YCW", "YCW"); // productNumbers that start with YCW
 define("CUSTOM", "CUSTOM"); // customer custom shipmode

class ShippingOption
{
    public $code = '';
    public $desc = '';
    public $shipping = 0.0;
    public $handling = 0.0;
    public $greatPlainsId = '';
}


class ShippingEngine
{

    // static use only
    private function __construct() {}


/**
 * @return array Array of ShippingOption objects
 */
public static function getShippingOptionsForShipment(\Cartonization\ShipmentInfo $shipment, array $shipments, /* int */ $num_shipments, $is_wholesale, $customer_number)
{
  if ( (is_checkout() || (defined( 'IS_PLACE_ORDER_REQUEST' ) && IS_PLACE_ORDER_REQUEST)) && is_object($shipment) ) {
    $shippingOptions = array();

    if($shipment->warehouse != 'OOS')
    {

        $free_shipping_eligible = self::cartQualifiesForFreeShipping($shipments, $is_wholesale);

        // woocommerce customer shipping info
        $customer = array(
            'is_wholesale' => $is_wholesale,
            'customer_number' => $customer_number,
            'shipping_address' => WC()->customer->get_shipping_address(),
            'shipping_city'      => WC()->customer->get_shipping_city(),
            'shipping_state'     => WC()->customer->get_shipping_state(),
            'shipping_country'   => WC()->customer->get_shipping_country(),
            'shipping_postalcode'  => WC()->customer->get_shipping_postcode(),
        );

        // Get UPS Rates
        $shipmentUPSRates = self::getUPSRates($shipment->warehouse, $shipment->get_products_in_shipment(), $shipment->boxes, $customer, $free_shipping_eligible, $num_shipments);


        // If OnTrac shipping is available for this shipments warehouse then
        // we get OnTrac shipping options
        if (static::onTracShippingIsAvailable($shipment->warehouse, $customer['shipping_state']))
        {
            $shipmentOnTracRates = static::getOnTracRates($shipment->warehouse, $shipment->get_products_in_shipment(), $shipment->boxes, $customer);
        }

        // add shipping rates to results
        // adding both UPS rates and OnTrack rates
        if(isset($shipmentUPSRates) && !empty($shipmentUPSRates))
        {
            foreach($shipmentUPSRates as $upsRate)
            {
                array_push($shippingOptions, $upsRate);
            }
        }

        if(isset($shipmentOnTracRates) && !empty($shipmentOnTracRates))
        {
            foreach($shipmentOnTracRates as $onTracRate)
            {
                array_push($shippingOptions, $onTracRate);
            }
        }
    }

    return $shippingOptions;
  }
}


// Helper functions
/**
 * @return array Array of ShippingOption objects
 */
private static function getUPSRates(/* string */ $warehouse, array $items, array $boxes, array $customer, /* bool */ $free_shipping_eligible, /* int */ $num_shipments)
{
    $use_shipper_id = WC()->session->get( 'use_shipper_id', false );

    $payapl_session = WC()->session->get( 'paypal_express_checkout' );

    // shipping options depend on these values
    // constants IS_PRODUCTION and UPS_NEGOTIATE_RATES are not checked
    $cache_key = 'ups_api_' . md5(implode('-', array($use_shipper_id, $warehouse, json_encode($items), json_encode($boxes), json_encode($customer), $free_shipping_eligible, isset($payapl_session))));
    $cached_data = get_transient($cache_key);
    if( $cached_data ) {
        return $cached_data;
    }

    /*
      ----PROCESS SHIPPING OPTIONS----
    */
    $shippingRateOptions = array();

    // calculate handling
    $handling_cost = static::calculateHandling(count($boxes), $items, $customer);

    /* ----UPS rate options---- */

    // UPS REST API
    $url = "";
    if (defined('IS_PRODUCTION') && IS_PRODUCTION)
    {
        $url = "https://onlinetools.ups.com/rest/Rate"; // Production
    }
    else
    {
        $url = "https://wwwcie.ups.com/rest/Rate"; // Test
    }

    // UPS auth
    $username = "9X9728";
    $password = "RRPUPS$01";
    $accessLicense = "0D2817192C3B40AE";


    // warehouse shipping locations
    $shipperLocations = array(
      "WA" => array(
        "City" => "Everett",
        "Zip" => "98204",
      ),
      "TN" => array(
        "City" => "Franklin",
        "Zip" => "37067",
      ),
      "CA" => array(
        "City" => "Fresno",
        "Zip" => "93725",
      ),
      "KY" => array(
        "City" => "Florence",
        "Zip" => "41042",
      ),
      "MN" => array(
        "City" => "Zumbrota",
        "Zip" => "55992",
      ),
	  "OR" => array(
        "City" => "Portland",
        "Zip" => "97220",
      ),
	  "TX" => array(
        "City" => "Arlington",
        "Zip" => "76011",
      ),
    );

    // greatPlainsId (Shipping code label)
    $greatPlainsIds = array(
      "03" => array(
        "id" => "UPS/GROUND01",
        "desc" => "UPS - Ground",
      ), // Domestic/Ground
      "07" => array(
        "id" => "UPS/INT2DAY01",
        "desc" => "UPS - WorldWide Express",
      ), // WorldWide Express
      "08" => array(
        "id" => "UPS/INT4DAY02",
        "desc" => "UPS - WorldWide Expedited",
      ), // WorldWide Expedited
      "11" => array(
        "id" => "UPS/GROUND02",
        "desc" => "",
      ), // Ground to Canada
      "12" => array(
        "id" => "UPS/3DAY01",
        "desc" => "UPS - ThreeDaySelect",
      ), // Domestic/1or3day?
      "13" => array(
        "id" => "UPS/1DAY03",
        "desc" => "UPS - NextDayAirSaver",
      ), // Domestic/1or3day?
      "65" => array(
        "id" => "UPS/INT3DAY01",
        "desc" => "UPS - WorldWide Saver",
      ), // WorldWide Saver
    );

    /*
      By Excluding our UPS account number we ensure that we get back PUBLISHED rates
      If we ever decide to charge NEGOTIATED rates we'll switch on the UPS_NEGOTIATE_RATES constant
    */
    $shipperNumber = "";
    $negotiatedRatesIndicator = "";
    if(defined('UPS_NEGOTIATE_RATES') && UPS_NEGOTIATE_RATES)
    {
        $shipperNumber = defined('IS_PRODUCTION') && IS_PRODUCTION ? "9X9728" : "4W8V79";
        $negotiatedRatesIndicator = "yes";
    }
    // UPS Shipper
    $upsShipper = array(
      'ShipperNumber' => $shipperNumber,
      'Address' => array(
        'AddressLine' => array(),
        'City' => $shipperLocations[$warehouse]["City"],
        'StateProvinceCode' => $warehouse,
        'PostalCode' => $shipperLocations[$warehouse]["Zip"],
        'CountryCode' => 'US',
      )
    );

    // UPS ShipTo
    $upsShipTo = array(
      'Address' => array(
        'AddressLine' => array( $customer['shipping_address'] ),
        'City' => $customer['shipping_city'],
        'StateProvinceCode' => $customer['shipping_state'],
        'PostalCode' => $customer['shipping_postalcode'],
        'CountryCode' => $customer['shipping_country'],
      )
    );

    // UPS ShipFrom
    $upsShipFrom = array(
      'Address' => array(
        'AddressLine' => array(),
        'City' => $shipperLocations[$warehouse]["City"],
        'StateProvinceCode' => $warehouse,
        'PostalCode' => $shipperLocations[$warehouse]["Zip"],
        'CountryCode' => 'US',
      )
    );

    if ('US' === $customer['shipping_country']) {
        $supportedServices = array( '03', '12', '13' );
    } else {
        $supportedServices = array( '08', '65', '07' );
    }

    $api_failure = false;

    // loop through supported services
    foreach($supportedServices as $key => $service)
    {

        // Initialize varaibles for this shipping option
        $shippingOptionCode = '';
        $shippingOptionDesc = '';
        $shippingTotalCharges = 0;
        $shippingOptionFailure = false;

        // We need to call the API once per box
        foreach ($boxes as $box)
        {
            // The shipment weight is the weight of the box and all of the items in the box.
            $shipmentWeight = $box->weight;

            // loop through items to calculate box used capacity
            foreach($box->items as $key => $item)
            {
                $shipmentWeight += $item->weight * $item->quantity;
            }

            // If a single box is greater than 150 lbs, UPS will reject it.
            // That means that this shipment will have to be WILL CALL only
            if ($shipmentWeight >= 150) {

                // Return the single shipping option
                return $shippingRateOptions;
            }

            // UPS Service
            $upsService = array(
              'Code' => $service,
              'Description' => 'Service Code Description'
            );

            // UPS Package
            $upsPackage = array(
              'PackagingType' => array(
                'Code' => '02',
                'Description' => 'Rate'
              ),
              'PackageWeight' => array(
                'UnitOfMeasurement' => array(
                  'Code' => 'Lbs',
                  'Description' => 'pounds'
                ),
                'Weight' => strval($shipmentWeight)
              )
            );

            // UPS Shipment
            $upsShipment = array(
              'Shipper' => $upsShipper,
              'ShipTo' => $upsShipTo,
              'ShipFrom' => $upsShipFrom,
              'Service' => $upsService,
              'Package' => $upsPackage,
              'ShipmentRatingOptions' => array(
                'NegotiatedRatesIndicator' => $negotiatedRatesIndicator
              )
            );

            // build post data
            $postData = array(
              'UPSSecurity' => array(
                'UsernameToken' => array(
                  'Username' => $username,
                  'Password' => $password
                ),
                'ServiceAccessToken' => array(
                  'AccessLicenseNumber' => $accessLicense
                )
              ),
              'RateRequest' => array(
                'Request' => array(
                  'RequestOption' => 'Rate',
                  'TransactionReference' => array(
                    'CustomerContext' => 'Customer Context'
                  )
                ),
                'Shipment' => $upsShipment
              )
            );

            // setting up request
            $args = array(
              'method' => 'POST',
              'timeout' => 45,
              'redirection' => 5,
              'httpversion' => '1.0',
              'blocking' => true,
              'headers' => array(),
              'body' => json_encode($postData),
              'cookies' => array()
            );

            // request
            $response = wp_remote_post($url, $args);

            if( is_wp_error( $response ) )
            {
                $api_failure = true;
                $shippingOptionFailure = true;
                $error_message = $response->get_error_message();
                error_log('Error:');
                error_log($error_message);
                echo "Something went wrong in UPS Request. Error message: \"$error_message\"";
                break;
            }
            else
            {
                // get info from response
                $shippingRateResponse = json_decode($response['body']);

                if(array_key_exists("RateResponse", $shippingRateResponse))
                {
                    $shippingRates = $shippingRateResponse->RateResponse->RatedShipment;

                    if($shippingRates != null)
                    {
                        $shippingOptionDesc = $shippingRates->Service->Description;
                        $shippingOptionCode = $shippingRates->Service->Code;
                        // check to see if customer has shipper account
                        if ( ! $use_shipper_id )
                        {
                            $shippingTotalCharges += round(($shippingRates->TotalCharges->MonetaryValue * SHIP_FACTOR), 2);
                        }
                    }
                }
                elseif(array_key_exists("Fault", $shippingRateResponse))
                {
                    $api_failure = true;
                    $shippingOptionFailure = true;
                    error_log("Response Fault:");
                    error_log(var_export($shippingRateResponse, true));
                }
            }
        }


        if (!$shippingOptionFailure)
        {

            // free shipping option
            if ( $free_shipping_eligible && '03' === $shippingOptionCode )
            {
                $shippingTotalCharges = 0;
                $shippingOptionDesc = 'Free Shipping*';
            }

            // Shipping option from UPS response
            $shippingOption = new ShippingOption();
            $shippingOption->code = $shippingOptionCode;
            $shippingOption->desc = $shippingOptionDesc == '' ? $greatPlainsIds[$shippingOptionCode]["desc"] : $shippingOptionDesc;
            $shippingOption->shipping = $shippingTotalCharges;
            $shippingOption->handling = $handling_cost;
            $shippingOption->greatPlainsId = $greatPlainsIds[$shippingOptionCode]["id"];

            array_push($shippingRateOptions, $shippingOption);
        }
    }

    // will call option
    if ( 1 === $num_shipments && false === isset($payapl_session) ) {
      $shippingOption = new ShippingOption();
      $shippingOption->code = "WILL CALL";
      $shippingOption->desc = "WILL CALL";
      $shippingOption->greatPlainsId = "WILL CALL";

      array_push($shippingRateOptions, $shippingOption);
    }

    if (!$api_failure) {
        // cache results
        set_transient($cache_key, $shippingRateOptions);
    }

    return $shippingRateOptions;
}

/**
 * @return array Array of ShippingOption objects
 */
private static function getOnTracRates(/* string */ $warehouse, array $items, array $boxes, array $customer)
{
    /*
      ----PROCESS ONTRAC SHIPPING OPTIONS----
    */
    $shippingRateOptions = array();

    // calculate handling
    $boxCount = count($boxes);
    $handling_cost = static::calculateHandling($boxCount, $items, $customer);

    // calculate total weight of shipment
    $shipmentWeight = static::calculateShipmentWeight($boxes);

    // customer delivery zip code
    $deliveryZipCode = $customer['shipping_postalcode'];

    $use_shipper_id = WC()->session->get( 'use_shipper_id', false );


    // shipping options depend on these values
    // constants IS_PRODUCTION and UPS_NEGOTIATE_RATES are not checked
    $cache_key = 'ontrac_api_' . md5(implode('-', array($warehouse, $deliveryZipCode, $handling_cost, $shipmentWeight, $use_shipper_id, $deliveryZipCode)));
    $cached_data = get_transient($cache_key);
    if( $cached_data ) {
        return $cached_data;
    }


    /* ----OnTrac rate options---- */

    // OnTrac API
    $url = "";
    if(defined('IS_PRODUCTION') && IS_PRODUCTION)
    {
        $url = "https://www.shipontrac.net/OnTracWebServices/OnTracServices.svc/V2/"; // Production
    }
    else
    {
        $url = "https://www.shipontrac.net/OnTracTestWebServices/OnTracServices.svc/V2/"; // Test
    }

    // set up request
    $apiAccounts = array(
      "WA" => array(
        "accountNumber" => defined('IS_PRODUCTION') && IS_PRODUCTION ? "126068" : "37",
        "apiPassword" => defined('IS_PRODUCTION') && IS_PRODUCTION ? "RRPEverett" : "testpass",
        "pickUpZipCode" => "98204"
      ),
      "CA" => array(
        "accountNumber" => defined('IS_PRODUCTION') && IS_PRODUCTION ? "126067" : "37",
        "apiPassword" => defined('IS_PRODUCTION') && IS_PRODUCTION ? "RRPFresno" : "testpass",
        "pickUpZipCode" => "93725"
      )
    );

    $accountNumber = $apiAccounts[$warehouse]["accountNumber"];
    $apiPassword = $apiAccounts[$warehouse]["apiPassword"];
    $pickUpZipCode = $apiAccounts[$warehouse]["pickUpZipCode"];

    // build url with parameters
    $url .= $accountNumber;
    $url .= "/rates?pw=" . $apiPassword;
    $url .= "&packages=RWA1;";
    $url .= $pickUpZipCode . ";";
    $url .= $deliveryZipCode . ";";
    $url .= "false;"; // Residential address
    $url .= "0.00;"; // COD $ value
    $url .= "false;"; // Saturday delivery
    $url .= "0;"; // Declared $ value
    $url .= $shipmentWeight . ";"; // Weight
    $url .= "0;"; // Package = 0,  Letter = 1

    // initiate request
    $ci = curl_init();
    curl_setopt($ci, CURLOPT_URL, $url);
    curl_setopt($ci, CURLOPT_FAILONERROR, 1);
    curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ci, CURLOPT_TIMEOUT, 15);
    $retValue = curl_exec($ci);

    if(curl_errno($ci))
    {
        error_log('Something went wrong in OnTrac Request:' . curl_error($ci));
    }
    else
    {
        $oXML = new \SimpleXMLElement($retValue);

        if (isset($oXML->Shipments) && isset($oXML->Shipments->Error) && !empty($oXML->Shipments->Error)) {
            error_log('OnTracRateResponse Error: ' . $oXML->Shipments->Error);
        }

        $shipmentResponse = $oXML->Shipments->Shipment;
        if (isset($shipmentResponse) && isset($shipmentResponse->Rates) && isset($shipmentResponse->Rates->Rate))
        {
            $shipmentRates = $shipmentResponse->Rates->Rate;
            if (count($shipmentRates) > 0)
            {
                foreach($shipmentRates as $rate)
                {
                    $serviceType = strval($rate->Service);
                    $serviceCharge = floatval($rate->ServiceCharge);
                    $codCharge = floatval($rate->ServiceChargeDetails->CODCharge);
                    $declaredCharge = floatval($rate->ServiceChargeDetails->DeclaredCharge);
                    $fuelCharge = floatval($rate->FuelCharge);
                    $totalCharge = floatval($rate->TotalCharge);
                    $transitDays = intval($rate->TransitDays);

                    $shipping_cost = 0;
                    if ( ! $use_shipper_id )
                    {
                        $shipping_cost = round(($totalCharge * SHIP_FACTOR), 2);
                    }

                    // Now let's check the type of service it is. We want to restrict the shipping options based on origin and destination...
                    // C—OnTrac Ground ; like UPS Ground, guaranteed by the end of the delivery day
                    // S—Sunrise ; earlier delivery times (times vary by zip code and can be viewed online) this service is more expensive than OnTrac Ground.
                    // G—Gold; earliest delivery times (times vary by zip code and can be viewed online) this service is more expensive than Sunrise/
                    // H—Palletized Freight; LTL service, charged by pallet by weight, delivery time is by end of day.

                    // right now we are ONLY going to give the user the option for Sunrise shipping.
                    // greatPlainsId (Shipping code label)
                    $greatPlainsIds = array(
                        "C" => array(
                            "id" => "ONTRAC/GROUND",
                            "desc" => "OnTrac - Ground"
                        ), // OnTrac Ground
                        "S" => array(
                            "id" => "ONTRAC/SUNRISE",
                            "desc" => "OnTrac - Sunrise"
                        ), // OnTrac Sunrise
                    );

                    if(array_key_exists($serviceType, $greatPlainsIds))
                    {
                        // Shipping option from OnTrac response
                        $shippingOption = new ShippingOption();
                        $shippingOption->code = $serviceType;
                        $shippingOption->desc = $greatPlainsIds[$serviceType]["desc"];
                        $shippingOption->shipping = $shipping_cost;
                        $shippingOption->handling = $handling_cost;
                        $shippingOption->greatPlainsId = $greatPlainsIds[$serviceType]["id"];

                        array_push($shippingRateOptions, $shippingOption);
                    }
                }
                // cache results
                set_transient($cache_key, $shippingRateOptions);
            }
        }
    }

    curl_close($ci);

    return $shippingRateOptions;
}

/**
 * return @float
 */
private static function calculateHandling(/* int */ $bc, array $items, array $customer)
{
    $zeroHandlingFee = 0.0;
    $customHandlingFeeThree = 3.0;
    $customHandlingFeeSix = 6.0;
    $allYCWitemsincart = true; // assume all items in the cart are YCW.

    foreach($items as $item)
    {
        if(!(substr($item->productNumber, 0, 3) == YCW))
        {
            $allYCWitemsincart = false; // at least one item is not YCW.
            break; // no need to check the rest of the items in the cart.
        }
    }

    if($allYCWitemsincart)
    {
        return $zeroHandlingFee;
    }

    if($customer['customer_number'] == "132927")
    {
        return $customHandlingFeeThree;
    }

    if($customer['customer_number'] == "90418")
    {
        return $customHandlingFeeSix;
    }

    return static::handlingFeePerBox($customer['is_wholesale']) * $bc;
}

/**
 * @return float
 */
private static function handlingFeePerBox(/* bool */ $is_wholesale)
{
    $feePerBox = 8.50;

    if($is_wholesale)
    {
        $feePerBox = 6.50;
    }

    return $feePerBox;
}

/**
 * @return float
 */
private static function calculateShipmentWeight(array $boxes)
{
    $shipmentWeight = 0.0;

    // loop through boxes to calculate total weight
    foreach($boxes as $key => $box)
    {
        $boxUsedCapacity = 0.0;

        // loop through items to calculate box used capacity
        foreach($box->items as $key => $item)
        {
            $boxUsedCapacity += $item->weight * $item->quantity;
        }

        $shipmentWeight += $box->weight + $boxUsedCapacity;
    }

    return ceil($shipmentWeight);
}

/**
 * @return bool
 */
private static function onTracShippingIsAvailable(/* string */ $warehouse, /* string */ $destination)
{
    // Not available for TN or KY.
    $rtnVal = false;

    switch($warehouse)
    {
      case "WA":
        if($destination == "WA" || $destination == "OR")
        {
          $rtnVal = true;
        }
        break;
      case "CA":
        if($destination == "CA" || $destination == "NV" || $destination == "OR" || $destination == "WA" || $destination == "UT" || $destination == "CO" || $destination == "AZ")
        {
          $rtnVal = true;
        }
        break;
      default:
        break;
    }

    return $rtnVal;
}

/**
 * @return bool
 */
private static function cartQualifiesForFreeShipping(array $shipments, /* bool */ $is_wholesale)
{
    global $woocommerce;
    $customer = $woocommerce->customer;

    if($is_wholesale)
    {
        return static::cartShipmentsQualifyForFreeShipping($shipments, $is_wholesale);
    }

    if($customer->shipping_state != "HI" && $customer->shipping_state != "AK" && $customer->shipping_country == "US")
    {
        return static::cartShipmentsQualifyForFreeShipping($shipments, $is_wholesale);
    }

    return false;
}

/**
 * @return bool
 */
private static function cartShipmentsQualifyForFreeShipping(array $shipments, /* bool */ $is_wholesale)
{
    $freeShipQualify = 0.0;
    $allYCWitemsincart = true; // assume all YCW items in the cart.

    foreach ($shipments as $shipment) {
        if($shipment->warehouse != 'OOS')
        {
            $items = $shipment->get_products_in_shipment();

            foreach ($items as $item)
            {
                // WHOLESALE only gets free shipping if they buy enough of SPECIFIC products
                if($is_wholesale)
                {
                    if(static::itemGetsFreeShipping($item))
                    {
                        $freeShipQualify += static::itemTotal($item);
                    }
                }
                // RETAIL only gets free shipping if they buy enough (i.e $999) of ANY products
                else
                {
                    $freeShipQualify += static::itemTotal($item);
                }

                if(!(substr($item->productNumber, 0, 3) == YCW))
                {
                    $allYCWitemsincart = false; // at least one item is not YCW.
                }
            }
        }
    }

    return ($freeShipQualify >= floatVal(static::getFreeShippingThreshold())) || $allYCWitemsincart;
}

/**
 * @return bool
 */
private static function itemGetsFreeShipping(\Cartonization\ProductInfo $item)
{
    $itemIsFreeShipping = false;

    // Database connection values
    $dbserver = DB_HOST;
    $dbusername = DB_USER;
    $dbpassword = DB_PASSWORD;
    $dbname = DB_NAME;

    // see if item is free shipping
    $queryItemFreeShipping = "SELECT ProductID FROM randys_freeship WHERE ProductID = (?)";

    try
    {
        $db = new \PDO('mysql:host=' . $dbserver . ';dbname=' . $dbname . ';charset=utf8', '' . $dbusername . '', '' . $dbpassword . '');
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $searchItemFreeShipping = $db->prepare($queryItemFreeShipping);
        $searchItemFreeShipping->bindValue(1, $item->id, \PDO::PARAM_INT);
        $searchItemFreeShipping->execute();
        $resultsItemFreeShipping = $searchItemFreeShipping->fetch(\PDO::FETCH_ASSOC);

        if ($resultsItemFreeShipping && $resultsItemFreeShipping["ProductID"] != null)
        {
            $itemIsFreeShipping = true;
        }
    }
    catch (\PDOException $e)
    {
        die("Error executing database query: " . $e->getMessage());
    }

    return $itemIsFreeShipping;
}

/**
 * @return float
 */
private static function itemTotal(\Cartonization\ProductInfo $item)
{
    $unit_price = $item->cartItem['data']->get_price();

    return $unit_price * $item->quantity;
}

/**
 * @return float
 */
private static function getFreeShippingThreshold()
{
    $freeShipLimit = 0.0;
    $woo_cust_id = get_current_user_id(); // get customer id

    // Database connection values
    $dbserver = DB_HOST;
    $dbusername = DB_USER;
    $dbpassword = DB_PASSWORD;
    $dbname = DB_NAME;

    if ( 0 != $woo_cust_id ) {
        // get the threshold for customer class
        $queryShippingThreshold = "SELECT threshold";
        $queryShippingThreshold .= " FROM randys_FreeShippingThresholds";
        $queryShippingThreshold .= " JOIN randys_customers";
        $queryShippingThreshold .= " ON LOWER(randys_customers.CUSTCLAS) = LOWER(randys_FreeShippingThresholds.CustomerClass)";
        $queryShippingThreshold .= " WHERE WOOCustID = (?)";

        try
        {
            $db = new \PDO('mysql:host=' . $dbserver . ';dbname=' . $dbname . ';charset=utf8', '' . $dbusername . '', '' . $dbpassword . '');
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $searchShippingThreshold = $db->prepare($queryShippingThreshold);
            $searchShippingThreshold->bindValue(1, $woo_cust_id, \PDO::PARAM_INT);
            $searchShippingThreshold->execute();
            $resultsShippingThreshold = $searchShippingThreshold->fetchAll(\PDO::FETCH_ASSOC);


            if(!empty($resultsShippingThreshold))
            {
                if($resultsShippingThreshold[0]["threshold"] != null)
                {
                    $freeShipLimit = $resultsShippingThreshold[0]["threshold"];
                }
            } else {
                $freeShipLimit = static::returnRetailThreshold();
            }
        }
        catch (\PDOException $e)
        {
            die("Error executing database query: " . $e->getMessage());
        }
    } else {
        $freeShipLimit = static::returnRetailThreshold();
    }

    return $freeShipLimit;
}

/**
 * @return float
 */
private static function returnRetailThreshold() {
    $freeShipLimit = 0.0;

    // Database connection values
    $dbserver = DB_HOST;
    $dbusername = DB_USER;
    $dbpassword = DB_PASSWORD;
    $dbname = DB_NAME;


    // get the threshold for RETAIL
    $queryShippingThreshold = "SELECT threshold FROM randys_FreeShippingThresholds WHERE CustomerClass = 'retail'";

    try
    {
        $db = new \PDO('mysql:host=' . $dbserver . ';dbname=' . $dbname . ';charset=utf8', '' . $dbusername . '', '' . $dbpassword . '');
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $searchShippingThreshold = $db->prepare($queryShippingThreshold);
        $searchShippingThreshold->execute();
        $resultsShippingThreshold = $searchShippingThreshold->fetchAll(\PDO::FETCH_ASSOC);

        if($resultsShippingThreshold != null)
        {
            if($resultsShippingThreshold[0]["threshold"] != null)
            {
                $freeShipLimit = $resultsShippingThreshold[0]["threshold"];
            }
        }
    }
    catch (\PDOException $e)
    {
        die("Error executing database query: " . $e->getMessage());
    }

    return $freeShipLimit;
}

}
