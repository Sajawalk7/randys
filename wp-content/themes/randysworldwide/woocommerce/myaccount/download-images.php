<?php
$select_type = 0;
if (isset($_GET['select_type'])) {
    $select_type = $_GET['select_type'];
}

// Product image
$base_image_path = "./wp-content/uploads/product-images/";
$base_url_path = "/wp-content/uploads/product-images/";

$order_array = [
    "name_asc"      => ["title" => "Product Name (ascending)",          "code" => "p.ProductNumber ASC"],
    "name_desc"     => ["title" => "Product Name (descending)",         "code" => "p.ProductNumber DESC"],
    "date_asc"      => ["title" => "Date Created (oldest to newest)",   "code" => "p.ProductID ASC"],
    "date_desc"     => ["title" => "Date Created (newest to oldest)",   "code" => "p.ProductID DESC"],
    "filename_asc"  => ["title" => "File Name (ascending)",             "code" => "p.FullImage ASC"],
    "filename_desc" => ["title" => "File Name (descending)",            "code" => "p.FullImage DESC"]
];

global $wpdb;

$categories = $wpdb->get_results("SELECT c.CategoryID, c.CategoryName FROM randys_category as c WHERE c.CategoryID in (SELECT DISTINCT cp.CategoryID FROM randys_categoryproduct as cp) ORDER BY c.CategoryName;");

$current_category_id = 0;
if (isset($_GET['category_id'])) {
    $current_category_id = $_GET['category_id'];
} elseif (count($categories) > 0) {
    $current_category_id = $categories[0]->CategoryID;
}

$order_by = 'p.ProductNumber ASC';
if (isset($_GET['order_by'])) {
    $order_by = $order_array[$_GET['order_by']]["code"];
}

$products = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT p.ProductID, p.ProductNumber, p.Title, p.Description, p.ThumbImage, p.FullImage FROM randys_product as p INNER JOIN randys_categoryproduct as cp on p.ProductID = cp.ProductID WHERE p.ThumbImage <> '' AND p.FullImage <> '' AND cp.CategoryID = %d ORDER BY %s",
        array($current_category_id, $order_by)
    )
);

$app_order_array = [
    "date_asc"      => ["title" => "Date Created (oldest to newest)"],
    "date_desc"     => ["title" => "Date Created (newest to oldest)"],
    "filename_asc"  => ["title" => "File Name (ascending)"],
    "filename_desc" => ["title" => "File Name (descending)"]
];

$app_images = get_app_images();

$app_order_by = 'date_asc';
if (isset($_GET['app_order_by']) && isset($app_order_array[$_GET['app_order_by']])) {
    $app_order_by = $_GET['app_order_by'];
}

usort($app_images, function ($a, $b) use ($app_order_by) {
    if ($app_order_by == 'date_asc') {
        if ($a["time"] == $b["time"]) {
            return 0;
        } elseif ($a["time"] < $b["time"]) {
            return -1;
        } else {
            return 1;
        }
    } elseif ($app_order_by == 'date_desc') {
        if ($a["time"] == $b["time"]) {
            return 0;
        } elseif ($a["time"] > $b["time"]) {
            return -1;
        } else {
            return 1;
        }
    } elseif ($app_order_by == 'filename_asc') {
        if ($a["name"] == $b["name"]) {
            return 0;
        } elseif ($a["name"] < $b["name"]) {
            return -1;
        } else {
            return 1;
        }
    } elseif ($app_order_by == 'filename_desc') {
        if ($a["name"] == $b["name"]) {
            return 0;
        } elseif ($a["name"] > $b["name"]) {
            return -1;
        } else {
            return 1;
        }
    }
});

?>
<h2>Downloadable Images</h2>
<div class="back"><a href="/my-account"><i class="fa fa-arrow-left" aria-hidden="true"></i> My Account</a></div>
<form action='/my-account/download-images/' class="row">
    <div class="col-xs-12">
        <div class="col-md-4 col-xs-12 inline-block">
            <label for="select_type">Gallery Type</label>
            <span class="select diffwizard__select">
                <select id="select_type" name="select_type">
                    <option value="0" <?php echo $select_type == 0 ? "selected" : ""  ?>>Product Images</option>
                    <option value="1" <?php echo $select_type == 1 ? "selected" : ""  ?>>App Images</option>
                </select>
            </span>
        </div><div class="col-md-8 col-xs-12 inline-block">
            &nbsp;
        </div>
    </div>

    <div class="col-xs-12">
        <br/>
    </div>

    <div class="col-xs-12" style="margin-bottom: 15px">
        <div class="col-md-6 col-xs-12 inline-block">
            <input type="button" id="btn-download" value="DOWNLOAD SELECTED IMAGES"
                   class="button button--short-blue button--slim" disabled>
        </div><div class="col-md-6 col-xs-12 inline-block" style="text-align: right">
            <input type="button" id="btn-select-all" value="SELECT ALL" class="button button--short-blue button--slim">
            <input type="button" id="btn-clear-select" value="CLEAR SELECTION" class="button button--short-blue button--slim" disabled>
        </div>
    </div>

    <div id="downloadable-image" class="col-xs-12" style="display: none">
        <div class="col-md-6 col-xs-12 inline-block ">
            <label for="category_id">Product Categories</label>
            <span class="select diffwizard__select">
                <select id="category_id" name="category_id" onchange="this.form.submit()">
                    <?php
                    foreach ($categories as $category) {
                        if ($category->CategoryID == $current_category_id) {
                            echo "<option value='" . $category->CategoryID . "' selected>" . $category->CategoryName . "</option>\n";
                        } else {
                            echo "<option value='" . $category->CategoryID . "'>" . $category->CategoryName . "</option>\n";
                        }
                    }
                    ?>
                </select>
            </span>
        </div><div class="col-md-2 hidden-xs inline-block">&nbsp;</div><div class="col-md-4 col-xs-12 inline-block">
            <label for="order_by">Sort Order</label>
            <span class="select diffwizard__select">
                <select id="order_by" name="order_by" onchange="this.form.submit()">
                    <?php
                    foreach ($order_array as $key => $value) {
                        if ($value["code"] == $order_by) {
                            echo "<option value='" . $key . "' selected>" . $value["title"] . "</option>\n";
                        } else {
                            echo "<option value='" . $key . "'>" . $value["title"] . "</option>\n";
                        }
                    }
                    ?>
                </select>
            </span>
        </div>

        <div class="col-xs-12"><br/></div>

        <div class="col-xs-12"><div class="row">
            <?php
            if (count($products) == 0) {
                ?>
                <div class="col-xs-12 text-danger" style="text-align: center">No Image Available</div>
                <?php
            } else {
                foreach ($products as $product) {
                    if (file_exists($base_image_path.$product->FullImage) == false) {
                        continue;
                    }

                    $title = htmlentities($product->Title);
                    if ($product->Title != $product->Description) {
                        $title .= "\n".htmlentities($product->Description);
                    }

                    ?>
                    <div class="col-xl-1 col-lg-2 col-md-2 col-sm-3 col-xs-4">
                        <div class="downloadable-image-tile" data-toggle="tooltip" title="<?php echo $title ?>">
                            <div class="downloadable-image-holder">
                                <div class="checkbox-panel">
                                    <input type="checkbox" name="chk_product" id="chk_product<?php echo $product->ProductID ?>"
                                           value="<?php echo $product->ProductID ?>" class="downloadable-image-checkbox"/>
                                    <label for="chk_product<?php echo $product->ProductID ?>" class="downloadable-image-label"></label>
                                </div>
                                <div class="image-panel">
                                    <img src="<?php echo $base_url_path . $product->FullImage ?>">
                                </div>
                            </div>
                            <div class="downloadable-tile-title">
                                <?php echo $product->ProductNumber ?>
                            </div>
                        </div>
                    </div>
                <?php
                }
            }
            ?>
        </div></div>
    </div>

    <div id="downloadable-app-image" class="col-xs-12" style="display: none">
        <div class="col-md-12 col-xs-12">
            <div class="col-md-6 col-xs-12 inline-block">
                &nbsp;
            </span>
            </div><div class="col-md-2 hidden-xs inline-block">&nbsp;</div><div class="col-md-4 col-xs-12 inline-block">
                <label for="app_order_by">Sort Order</label>
            <span class="select diffwizard__select">
                <select id="app_order_by" name="app_order_by" onchange="this.form.submit()">
                    <?php
                    foreach ($app_order_array as $key => $value) {
                        if ($key == $app_order_by) {
                            echo "<option value='" . $key . "' selected>" . $value["title"] . "</option>\n";
                        } else {
                            echo "<option value='" . $key . "'>" . $value["title"] . "</option>\n";
                        }
                    }
                    ?>
                </select>
            </span>
            </div>
        </div>

        <div class="col-xs-12"><br/></div>

        <div class="col-xs-12"><div class="row">
            <?php
            if (count($app_images) == 0) {
                ?>
                <div class="col-xs-12 text-danger" style="text-align: center">No Image Available</div>
            <?php
            } else {
                // each object contain [hash_key, file_url, file_time]
                foreach ($app_images as $key => $app_image) {
                    ?>
                    <div class="col-xl-1 col-lg-2 col-md-2 col-sm-3 col-xs-4">
                        <div class="downloadable-image-tile" data-toggle="tooltip" title="<?php echo htmlentities($app_image["name"]) ?>">
                            <div class="downloadable-image-holder">
                                <div class="checkbox-panel">
                                    <input type="checkbox" name="chk_app_image" id="chk_app_image<?php echo $key ?>"
                                           value="<?php echo $app_image["hash"] ?>" class="downloadable-app-image-checkbox"/>
                                    <label for="chk_app_image<?php echo $key ?>" class="downloadable-image-label"></label>
                                </div>
                                <div class="image-panel">
                                    <img src="<?php echo $app_image["path"] ?>">
                                </div>
                            </div>
                            <div class="downloadable-tile-title">
                                <?php echo $app_image["name"] ?>
                            </div>
                        </div>
                    </div>
                <?php
                }
            }
            ?>
        </div></div>

    </div>

    <div class="col-xs-12">
        <br/><br/><br/>
    </div>
</form>

<form id="download_form" method="post" action="/wp-admin/admin-ajax.php" class="hidden">
    <input type="hidden" name="action" value="download_downloadable_image"/>
    <input type="hidden" name="key" value="" id="downloadableKey"/>
    <input type="hidden" id="downloadable_images_nonce" name="nonce" value="<?php echo wp_create_nonce("downloadable_images_nonce"); ?>">
</form>

<script language="javascript">
    var current_type = <?php echo $select_type?>;
</script>
