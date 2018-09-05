<?php
/**
 * Template Name: Zumbrota Template
 */
?>
<?php 
global $wpdb;
$searchType=(isset($_GET['stype'])==true)?$_GET['stype']:"";
$category_id=(isset($_GET['zumbrota_category'])==true)?$_GET['zumbrota_category']:"";
$zumbrota_year=(isset($_GET['zumbrota_year'])==true)?$_GET['zumbrota_year']:"";
$zumbrota_make=(isset($_GET['zumbrota_make'])==true)?$_GET['zumbrota_make']:"";
$zumbrota_model=(isset($_GET['zumbrota_model'])==true)?$_GET['zumbrota_model']:"";
$zumbrota_unit_model_name=(isset($_GET['zumbrota_unit_model_name'])==true)?$_GET['zumbrota_unit_model_name']:"";
$search_number=(isset($_GET['search_by_number'])==true)?$_GET['search_by_number']:"";
$order_by=(isset($_GET['order_by'])==true)?$_GET['order_by']:"name";
$dir=(isset($_GET['dir'])==true)?$_GET['dir']:"ASC";

if($searchType!==""){
	$url="?stype=".$searchType;
	$str="";
	if($category_id!==""){
		$str.= " AND cpt.category_id='$category_id'";
		$url.="&zumbrota_category=".$category_id;
	}
	if($zumbrota_year!==""){
		$str.= " AND fcc.year='$zumbrota_year'";
		$url.="&zumbrota_year=".$zumbrota_year;
	}
	if($zumbrota_make!==""){
		$str.= " AND fcc.make='$zumbrota_make'";
		$url.="&zumbrota_make=".$zumbrota_make;
	}
	if($zumbrota_model!==""){
		$str.= " AND fcc.model='$zumbrota_model'";
		$url.="&zumbrota_model=".$zumbrota_model;
	}
	if($zumbrota_unit_model_name!==""){
		$str.= " AND fcc.unit_model_name='$zumbrota_unit_model_name'";
		$url.="&zumbrota_unit_model_name=".$zumbrota_unit_model_name;
	}
	if($search_number!==""){
		$url.="&search_by_number=".$search_number;
	}
	$str.= " AND fcc.status='1'";
	if($order_by=="name"){
		$str.=" ORDER BY pnm.ProductNumber ".$dir;
	}else if($order_by=="price"){
		$str.=" ORDER BY rp.Price ".$dir;
	}else{
		$str.=" ORDER BY cpt.product_id ".$dir;
	}
	if($searchType=='cat_module'){
		$total_sql="SELECT cpt.product_id FROM `category_product_tbl` cpt
			INNER JOIN firstscribe_cars_car_product2 fccp on fccp.product_id=cpt.product_id
			INNER JOIN firstscribe_cars_car fcc on fcc.entity_id=fccp.car_id
			INNER JOIN productid_and_number pnm on pnm.product_id=fccp.product_id
			INNER JOIN randys_product rp on rp.ProductNumber=pnm.ProductNumber
			WHERE 1".$str;
	}else{
		$total_sql="SELECT ProductID FROM randys_product WHERE ProductNumber LIKE '%$search_number%'";
	}
	$total_results = $wpdb->get_results($total_sql);
	$total_rows = count($total_results);
	$page_no=(isset($_GET['pn'])==true)?$_GET['pn']:1;
	$per_page=(isset($_GET['per_page'])==true)?$_GET['per_page']:36;
	$both_side=5;
	$total_page = ceil($total_rows/$per_page);
	$limit_start=($page_no-1)*$per_page;
	$pagination='<ul class="pagination">';
	$start=$page_no-$both_side;if($start<1){$start=1;}
	$end=$page_no+$both_side;if($end>$total_page){$end=$total_page;}

	if($page_no>1){
		$pagination.='<li><a href="'.$url.'&per_page='.$per_page."&order_by=".$order_by."&dir=".$dir.'&pn='.($page_no-1).'" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
	}else{
		$pagination.='<li class="disabled"><a href="javascript:void(0);" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
	}
	for($i=$start;$i<=$end;$i++){
		if($i==$page_no){
			$pagination.='<li class="active"><a href="javascript:void(0);">'.$i.'</a></li>';
		}else{
			$pagination.='<li><a href="'.$url.'&per_page='.$per_page."&order_by=".$order_by."&dir=".$dir.'&pn='.$i.'">'.$i.'</a></li>';
		}
		
	}
	if($page_no<$total_page){
		$pagination.='<li><a href="'.$url.'&per_page='.$per_page."&order_by=".$order_by."&dir=".$dir.'&pn='.($page_no+1).'" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
	}else{
		$pagination.='<li class="disabled"><a href="javascript:void(0)" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
	}
	$pagination.='</ul>';
	if($total_page<=1){
		$pagination="";
	}

	if($searchType=='cat_module'){
	
		$sql="SELECT cpt.product_id,fccp.car_id,fcc.year,fcc.make,fcc.model,fcc.unit_model_name,pnm.ProductNumber,rp.title,rp.ProductID,rp.MAP,rp.Description,rp.Title,rp.FullImage,rp.Price FROM `category_product_tbl` cpt
			INNER JOIN firstscribe_cars_car_product2 fccp on fccp.product_id=cpt.product_id
			INNER JOIN firstscribe_cars_car fcc on fcc.entity_id=fccp.car_id
			INNER JOIN productid_and_number pnm on pnm.product_id=fccp.product_id
			INNER JOIN randys_product rp on rp.ProductNumber=pnm.ProductNumber
			WHERE 1".$str." LIMIT ".$limit_start.",".$per_page;
	}else{
		$order_str="";
		if($order_by=="name"){
			$order_str.=" ORDER BY ProductNumber ".$dir;
		}else if($order_by=="price"){
			$order_str.=" ORDER BY Price ".$dir;
		}else{
			$order_str.=" ORDER BY ProductID ".$dir;
		}
		$sql = "SELECT * FROM randys_product WHERE ProductNumber LIKE '%$search_number%'".$order_str." LIMIT ".$limit_start.",".$per_page;
	}
	$results = $wpdb->get_results($sql);
	$rowcount = count($results);
	$max_res=$limit_start+$per_page;
	if($max_res>$rowcount){
		$max_res=$limit_start+$rowcount;
	}
	$totalResultHtml=($limit_start+1).'-'.($max_res).' of '.$total_rows;
}
//echo do_shortcode('[zumbrota_parts_finder]');
?>
<div class="zumb-wrap">
	<?php if($searchType!==""){ ?>
	<div class="zumbrota-search-result">
		<div class="header-row-result row-result-show">
			<div class="container">
				<div class="toolbar">
					<div class="sorter">
						<div class="sort-by">
							<label>Sort By</label>
							<select title="Sort By" class="order-by-change">
								<option <?php if($order_by=='position'){ ?> selected <?php } ?> value="<?php echo $url.'&per_page='.$per_page."&order_by=position&dir=".$dir;?>">Relevance</option>
								<option <?php if($order_by=='name'){ ?> selected <?php } ?> value="<?php echo $url.'&per_page='.$per_page."&order_by=name&dir=".$dir;?>">Name</option>
								<option <?php if($order_by=='price'){ ?> selected <?php } ?> value="<?php echo $url.'&per_page='.$per_page."&order_by=price&dir=".$dir;?>">Price</option>
							</select>
							<?php if($dir=="ASC"){ ?>
							<a href="<?php echo $url.'&per_page='.$per_page."&order_by=".$order_by."&dir=DESC"?>" class="sort-by-asc" title="Set Ascending Direction">Set Descending Direction</a>
							<?php } else { ?>
							<a href="<?php echo $url.'&per_page='.$per_page."&order_by=".$order_by."&dir=ASC"?>" class="sort-by-desc" title="Set Ascending Direction">Set Ascending Direction</a>
							<?php } ?>
							
							
						</div>
					</div>
					<div class="pager">
						<div class="count-container">
							<p class="total-result"><strong><?php echo $totalResultHtml;?></strong></p>
							<div class="total-result-limit">
								<label>Show</label>
									<select class="per-page-limit">
										<option <?php if($per_page==12){?> selected <?php } ?> value="<?php echo $url."&per_page=12"."&order_by=".$order_by."&dir=".$dir;?>">12</option>
										<option <?php if($per_page==24){?> selected <?php } ?> value="<?php echo $url."&per_page=24"."&order_by=".$order_by."&dir=".$dir;?>">24</option>
										<option <?php if($per_page==36){?> selected <?php } ?> value="<?php echo $url."&per_page=36"."&order_by=".$order_by."&dir=".$dir;?>">36</option>
										<option <?php if($per_page==48){?> selected <?php } ?> value="<?php echo $url."&per_page=48"."&order_by=".$order_by."&dir=".$dir;?>">48</option>
										<option <?php if($per_page==60){?> selected <?php } ?> value="<?php echo $url."&per_page=60"."&order_by=".$order_by."&dir=".$dir;?>">60</option>
									</select>
							</div>
						</div>
						<div class="pagination-show"><?php echo $pagination;?></div>
					</div>
				</div>
				<div class="clearfix"></div>
			</div>
			
		</div>
		<div class="container" id="show_search_result">
			<?php if($rowcount>0){
				foreach ($results as $row) {
					if($row->ProductID):
					$product_id=$row->ProductID;
					$ProductNumber=$row->ProductNumber;
					$sql2="SELECT post_id FROM `".$wpdb->prefix ."postmeta` WHERE `meta_key` = '_sku' AND meta_value='$ProductNumber'";
					$post_info = $wpdb->get_row($sql2);
					$post_id=$post_info->post_id;
					$purl = get_permalink($post_id);
					$product = wc_get_product($post_id);
					$randys_sku = get_post_meta($post_id, '_sku', true);
					$randy_productid = get_post_meta($post_id, '_randy_productid', true);
					$randy_proxynum = get_post_meta($post_id, '_proxynumber', true);
					$proxynum_output = $randy_proxynum ? '[' . $randy_proxynum . ']' : '';
					$price = $row->MAP;
					$Image = $row->FullImage;
					$upload_dir = wp_upload_dir();
					$full_image_dir = $upload_dir['basedir'] . '/product-images/' . $Image;
					$full_image = $upload_dir['baseurl'] . '/product-images/' ;//. $Image;
					
					$image_name=file_exists_ci($full_image_dir);
					//echo file_exists($full_image_dir);
					
			?>
			<div class="container">
				<article class="archive-product">
					<div class="row">
						<div class="col-md-2">
							<a href="<?php echo $purl;?>" class="archive-product__image-link zumb">
							<?php if($image_name){?>
								<img src="<?php echo $full_image.$image_name; ?>" alt="<?php echo $Image; ?>"/>
							<?php }else{ echo wc_placeholder_img('large'); }?>
							</a>
						</div>
						<div class="col-md-10">
							<div class="row">
								<div class="col-md-12 col-lg-7">
									<header>
										<h2 class="archive-product__number entry-title">
											<a href="<?php echo $purl;?>" class="archive-product__permalink"><?php echo $randys_sku;?></a>
											<span class="archive-product__sku--light"><?php echo $proxynum_output;?></span>
										</h2>
										<p class="archive-product__title"><?php echo $row->Title;?></p>
									</header>
								</div>
								<div class="col-md-5 col-lg-6 col-xl-7">
									<?php if( null !== $price && '0.00' !== $price ):
										echo '<p class="price price--lg">';
										echo '<span class="amount">$'.$price.'</span>';
										echo '</p>';
									endif;
									?>
									<div class="entry-summary m-b-1"><?php echo archive_cart_button_availability_forZum($product);?>
									</div>
								</div>
								<div class="col-md-7 col-lg-6 col-xl-5 align-self-end">
									<?php echo warehouse_availability_forZum($post_id);?>
								</div>
							</div>
						</div>
					</div>
				</article>
			</div>
			<?php endif;
				}
			}else{ ?>
				<div class="container">
					<article class="archive-product text-center">
						<h1>No Data Found!</h1>
					</article>
				</div>


			<?php 
			}
			?>
		</div>
		<div class="header-row-result row-result-show">
			<div class="container">
				<div class="toolbar">
					<div class="sorter">
						<div class="sort-by">
							<label>Sort By</label>
							<select title="Sort By" class="order-by-change">
								<option <?php if($order_by=='position'){ ?> selected <?php } ?> value="<?php echo $url."&order_by=position&dir=".$dir;?>">Relevance</option>
								<option <?php if($order_by=='name'){ ?> selected <?php } ?> value="<?php echo $url."&order_by=name&dir=".$dir;?>">Name</option>
								<option <?php if($order_by=='price'){ ?> selected <?php } ?> value="<?php echo $url."&order_by=price&dir=".$dir;?>">Price</option>
							</select>
							<?php if($dir=="ASC"){ ?>
							<a href="<?php echo $url."&order_by=".$order_by."&dir=DESC"?>" class="sort-by-asc" title="Set Ascending Direction">Set Descending Direction</a>
							<?php } else { ?>
							<a href="<?php echo $url."&order_by=".$order_by."&dir=ASC"?>" class="sort-by-desc" title="Set Ascending Direction">Set Ascending Direction</a>
							<?php } ?>
							
							
						</div>
					</div>
					<div class="pager">
						<div class="count-container">
							<p class="total-result"><strong><?php echo $totalResultHtml;?></strong></p>
							<div class="total-result-limit">
								<label>Show</label>
									<select class="per-page-limit">
										<option <?php if($per_page==12){?> selected <?php } ?> value="<?php echo $url."&per_page=12";?>">12</option>
										<option <?php if($per_page==24){?> selected <?php } ?> value="<?php echo $url."&per_page=24";?>">24</option>
										<option <?php if($per_page==36){?> selected <?php } ?> value="<?php echo $url."&per_page=36";?>">36</option>
										<option <?php if($per_page==48){?> selected <?php } ?> value="<?php echo $url."&per_page=48";?>">48</option>
										<option <?php if($per_page==60){?> selected <?php } ?> value="<?php echo $url."&per_page=60";?>">60</option>
									</select>
							</div>
						</div>
						<div class="pagination-show"><?php echo $pagination;?></div>
					</div>
				</div>
				<div class="clearfix"></div>
			</div>
			
		</div>
	</div>
	<?php } ?>
	<div class="zumb-sidebar">
		<div class="form">
			<?php do_shortcode('[zumbrota_parts_finder]'); ?>
		</div>
	</div>
</div>
