<?php
	// Get Make By Year 
	add_action( 'wp_ajax_get_year_by_category', 'get_year_by_category' );
	add_action( 'wp_ajax_nopriv_get_year_by_category', 'get_year_by_category' );
	function get_year_by_category(){
		global $wpdb;

		//$table_name = $wpdb->prefix . "liveshoutbox";
		//$table_name = "firstscribe_cars_car";
		$cat_id=$_REQUEST['cat_id'];
		$result= array();
		//$result['year']=$year;

		$sql="SELECT fcc.year FROM `category_product_tbl` cpt
		INNER JOIN firstscribe_cars_car_product2 fccp on fccp.product_id=cpt.product_id
		INNER JOIN firstscribe_cars_car fcc on fcc.entity_id=fccp.car_id
		WHERE 1 AND category_id='$cat_id' AND fcc.status='1' GROUP BY fcc.year ORDER BY fcc.year DESC";
		$results = $wpdb->get_results($sql);
		$rowcount = count($results);
		//print_r($results);
		$year_array=array();
		if($rowcount>0){
			$result['result']='1';
			foreach ($results as $row) {
				$year_array[]=$row->year;
			}
		}else{
			$result['result']='0';
		}
		
		
		$result['year']=$year_array;
		echo json_encode($result);
		wp_die();

	} 
	// Get Make By Year 
	add_action( 'wp_ajax_get_make_by_year', 'get_make_by_year' );
	add_action( 'wp_ajax_nopriv_get_make_by_year', 'get_make_by_year' );
	function get_make_by_year(){
		global $wpdb;

		//$table_name = $wpdb->prefix . "liveshoutbox";
		$table_name = "firstscribe_cars_car";
		$year=$_REQUEST['zyear'];
		$cat_id=$_REQUEST['cat_id'];
		$result= array();
		//$result['year']=$year;

		$sql="SELECT fcc.make FROM `category_product_tbl` cpt INNER JOIN firstscribe_cars_car_product2 fccp on fccp.product_id=cpt.product_id INNER JOIN firstscribe_cars_car fcc on fcc.entity_id=fccp.car_id WHERE 1 AND cpt.category_id='$cat_id' AND fcc.year='$year' AND fcc.status='1' GROUP BY fcc.make";
		$results = $wpdb->get_results($sql);
		$rowcount = count($results);
		//print_r($results);
		$make_array=array();
		if($rowcount>0){
			$result['result']='1';
			foreach ($results as $row) {
				$make_array[]=$row->make;
			}
		}else{
			$result['result']='0';
		}
		
		
		$result['make']=$make_array;
		echo json_encode($result);
		wp_die();

	} 

	// Get Model By Make
	add_action( 'wp_ajax_get_model_by_make', 'get_model_by_make' );
	add_action( 'wp_ajax_nopriv_get_model_by_make', 'get_model_by_make' );
	function get_model_by_make(){
		global $wpdb;

		//$table_name = $wpdb->prefix . "liveshoutbox";
		$table_name = "firstscribe_cars_car";
		$year=$_REQUEST['zyear'];
		$make=$_REQUEST['zmake'];
		$cat_id=$_REQUEST['cat_id'];
		$result= array();
		//$result['year']=$year;
		//$result['make']=$make;

		$sql="SELECT fcc.model FROM `category_product_tbl` cpt
		INNER JOIN firstscribe_cars_car_product2 fccp on fccp.product_id=cpt.product_id
		INNER JOIN firstscribe_cars_car fcc on fcc.entity_id=fccp.car_id
		WHERE 1 AND cpt.category_id='$cat_id' AND fcc.year='$year' AND fcc.make='$make' AND fcc.status='1' GROUP BY fcc.model";
		$results = $wpdb->get_results($sql);
		$rowcount = count($results);
		//print_r($results);
		$model_array=array();
		if($rowcount>0){
			$result['result']='1';
			foreach ($results as $row) {
				$model_array[]=$row->model;
			}
		}else{
			$result['result']='0';
		}
		
		
		$result['model']=$model_array;
		echo json_encode($result);
		wp_die();

	}

	// Get Model By Make
	add_action( 'wp_ajax_get_unit_model_by_model', 'get_unit_model_by_model' );
	add_action( 'wp_ajax_nopriv_get_unit_model_by_model', 'get_unit_model_by_model' );
	function get_unit_model_by_model(){
		global $wpdb;

		//$table_name = $wpdb->prefix . "liveshoutbox";
		$table_name = "firstscribe_cars_car";
		$year=$_REQUEST['zyear'];
		$make=$_REQUEST['zmake'];
		$model=$_REQUEST['zmodel'];
		$cat_id=$_REQUEST['cat_id'];
		$result= array();
		// $result['year']=$year;
		// $result['make']=$make;
		// $result['model']=$model;

		$sql="SELECT fcc.unit_model_name FROM `category_product_tbl` cpt
		INNER JOIN firstscribe_cars_car_product2 fccp on fccp.product_id=cpt.product_id
		INNER JOIN firstscribe_cars_car fcc on fcc.entity_id=fccp.car_id
		WHERE 1 AND cpt.category_id='$cat_id' AND fcc.year='$year' AND fcc.make='$make' AND fcc.model='$model'  AND fcc.status='1' GROUP BY fcc.unit_model_name";
		$results = $wpdb->get_results($sql);
		$rowcount = count($results);
		//print_r($results);
		$unit_model_array=array();
		if($rowcount>0){
			$result['result']='1';
			foreach ($results as $row) {
				$unit_model_array[]=$row->unit_model_name;
			}
		}else{
			$result['result']='0';
		}
		
		
		$result['unit_model']=$unit_model_array;
		echo json_encode($result);
		wp_die();

	}


	



/**
 * Get the master list of warehouses
 */
function get_warehouse_ids_forZum() {
  return array('CA', 'KY', 'TN', 'WA');
}
	/*
* Product Archive
* Determine what what CTA button to add depending on product availablity
*/
function archive_cart_button_availability_forZum(\WC_Product $product) {
  //$availability_by_warehouse = get_product_availability_by_warehouse_forZum($product->get_id());
  if( '0.00' === $product->get_price() ) {
    return '<a href="/contact-us" rel="nofollow" class="button add_to_cart_button">Call for Price</a>';
  } elseif( !array_sum($availability_by_warehouse) ) {
    return '<a href="/contact-us" rel="nofollow" class="button add_to_cart_button">Call for Details</a>';
  } elseif( !is_product()) {
    return do_action( 'woocommerce_after_shop_loop_item' );
  } else {
    return '<a rel="nofollow" href="?add-to-cart=' . $product->id . '" data-quantity="1" data-product_id="' . $product->id . '" class="button button--block product_type_simple add_to_cart_button ajax_add_to_cart">Add to cart</a>';
  }
}


function get_product_availability_by_warehouse_forZum($post_id) {
    // Import wpdb so we can run SQL commands in this function
  global $wpdb;

  // First let's get the SKU for the product so we can look up its availability
  $sku = get_post_meta($post_id, '_sku', true);

  // We will use this array as 'CA' => true if it's available and 'CA' => false if it's unavailable
  $availability_by_warehouse = array();

  // Pull in the list of warehouses
  $warehouse_ids = get_warehouse_ids_forZum();

  // Set all warehouse_ids as 0 before continuing
  foreach ($warehouse_ids as $warehouse_id) {
    $availability_by_warehouse[$warehouse_id] = 0;
  }

   // Make sure we have an actual sku here
  if (!empty($sku)) {


    // Query up the inventory
    $inventory_array = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT item, warehouse, qty
          FROM randys_InventoryByWarehouse
          WHERE item = %s",
        array($sku)
      )
    );

    // Loop through the inventories, marking them as available or not
    foreach ($inventory_array as $inventory) {
      if ($inventory->qty !== '0') {
        $availability_by_warehouse[$inventory->warehouse] = (int)$inventory->qty;
      }
    }
  }

  return $availability_by_warehouse;
}

/**
* Check and output product Availability in Warehouse
*
* @param $post_id accepts an integer of the Post's ID to get the warehouse's availability from
**/
function warehouse_availability_forZum($post_id) {

  $availability_by_warehouse = get_product_availability_by_warehouse_forZum($post_id);

  // Initialize the output with an opening up of html
  $data =
  '<div class="warehouse-availability m-b-1">
    <div class="warehouse-availability__title sm">
      Warehouse Availability
    </div>
    <ul class="warehouse-availability__list list-inline">';

  // Loop through the states and generate the HTML for each
  foreach ($availability_by_warehouse as $warehouse_id => $quantity) {

    // If it's not available, mark it as unavailable
    $unavailable_class = $quantity ? '' : 'warehouse-availability__list-item--not-active';
    $icon = $quantity ? 'fa fa-check' : 'fa fa-times';

    // Generate the code for this warehouse object
    $data .= '<li class="warehouse-availability__list-item ' . $unavailable_class . '">
                <span class="warehouse-availability__list-icon"><i class="' . $icon . '" aria-hidden="true"></i></span>
                <span class="warehouse-availability__list-name">' . $warehouse_id . '</span>
              </li>';
  }

  // Cap off the end of the html output
  $data .= '
    </ul>
  </div>';

  return $data;
}

function return_file_name($path){
	$urls=explode("/", $path);
	return end($urls);
}

function file_exists_ci($file) {
  if (file_exists($file)){
  	return return_file_name($file);
  }
    
  $lowerfile = strtolower($file);
  foreach (glob(dirname($file) . '/*')  as $file){
  	if (strtolower($file) == $lowerfile){
		return return_file_name($file);
  	}
  }
    
  return FALSE;
}
?>