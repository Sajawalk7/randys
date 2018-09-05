<?php

  // We need a new cron schedule for every 15 minutes
  function fresh_fifteen_minute_cron_schedule( $schedule_array ) {
      $schedule_array[ 'every-15-minutes' ] = array( 'interval' => 15 * 60, 'display' => 'Every 15 minutes' );
      return $schedule_array;
  }
  add_filter( 'cron_schedules', __NAMESPACE__ . '\\fresh_fifteen_minute_cron_schedule' );


  // If we're not already scheduled, let's go ahead and schedule one.
  if ( ! wp_next_scheduled( 'fresh_import_products_hook' ) ) {
    wp_schedule_event( time(), 'every-15-minutes', 'fresh_import_products_hook' );
  }

  // Function that imports products from Randys
  add_action( 'fresh_import_products_hook', __NAMESPACE__ . '\\fresh_import_products' );
  function fresh_import_products() {

    // This may take longer than 30 seconds.
    // To avoid the fatal timeout error, set the time limit for this cron to 15 minutes.
    set_time_limit(60 * 15);

    // Make sure that we get the categories inserted properly
    importProductCategories();

    // Import wpdb so we can use it
    global $wpdb;

    // Let's get all the required data from the randys_product table.
    $results = $wpdb->get_results(
      "SELECT randys_product.productid         AS productid,
                  randys_product.productnumber     AS productnumber,
                  randys_product.title             AS title,
                  -- randys_product.description       AS description, -- Replaced by 'randys_webdescription.type = Feature' and 'randys_webdescription.type = Bullet'
                  -- randys_product.keywords          AS keywords,
                  -- randys_product.thumbimage        AS thumbimage,
                  randys_product.fullimage         AS fullimage,
                  randys_product.proxynumber       AS proxynumber,
                  randys_product.price             AS price,
                  randys_product.qty               AS qty,
                  randys_product.weight            AS weight,
                  randys_product.p4                AS p4,
                  randys_product.p5                AS p5,
                  randys_product.p6                AS p6,
                  randys_product.list              AS list,
                  randys_product.map               AS map,
                  randys_product.warrantyid        AS warrantyid,
                  -- randys_product.locations         AS locations,
                  randys_product.cost              AS cost,
                  randys_product.height            AS height,
                  randys_product.width             AS width,
                  randys_product.length            AS length,
                  -- randys_product.exception         AS exception,
                  -- randys_product.volumecoefficient AS volumecoefficient,
                  -- randys_product.splinecount       AS splinecount,
                  -- randys_product.ratio             AS ratio,
                  -- randys_product.endratio          AS endratio,
                  -- randys_product.axlelugs          AS axlelugs,
                  -- randys_product.abs               AS abs,
                  -- randys_product.braketype         AS braketype,
                  -- randys_product.floattype         AS floattype,
                  -- randys_product.drivetype         AS drivetype,
                  -- randys_product.tractioncontrol   AS tractioncontrol,
                  -- randys_product.clutchinfo        AS clutchinfo,
                  -- randys_product.axlelength        AS axlelength,
                  randys_product.lugdiameter       AS lugdiameter,
                  randys_product.hubdiameter       AS hubdiameter,
                  -- randys_product.bearingdiameter   AS bearingdiameter,
                  -- randys_product.ujointsize        AS ujointsize,
                  -- randys_product.productline       AS productline,
                  randys_product.technotes         AS technotes,

                  -- It's safe to use a separator here since the data we are selecting are only numbers
                  GROUP_CONCAT(DISTINCT wp_termmeta.term_id separator ',') AS category_ids,

                  -- It's safe to use a separator here since the data we are selecting are only numbers
                  GROUP_CONCAT(DISTINCT wp_termmeta_parent.term_id separator ',') AS category_parent_ids,

                  -- Instruction information
                  randys_instruction.instructionname AS instruction_instructionname,
                  randys_instruction.filename        AS instruction_filename,

                  -- Warranty -- There should only be 1 here, but it won't be found because of 'GROUP BY productid'
                  GROUP_CONCAT(DISTINCT warranty_webdescription.Note separator ' ')       AS warranty,

                  -- Feature -- There should only be 1 here, but it won't be found because of 'GROUP BY productid'
                  GROUP_CONCAT(DISTINCT feature_webdescription.Note separator ' ')        AS feature,

                  -- Bullet Points
                  IFNULL(GROUP_CONCAT(DISTINCT bullet_webdescription.Note separator '</li><li>'), randys_product.description) AS bullets,

                  -- Manufacturer (from `brands`)
                  randys_brands.BrandName            AS brandname,
                  randys_brands.BannerID             AS brandbannerid

      FROM randys_product

      -- Join in the categories to tag onto the product
      LEFT JOIN randys_categoryproduct ON randys_categoryproduct.productid = randys_product.productid
      LEFT JOIN randys_category ON randys_category.categoryid = randys_categoryproduct.categoryid
      LEFT JOIN wp_termmeta ON wp_termmeta.meta_key = '_randys_category_id'
                              AND wp_termmeta.meta_value = randys_categoryproduct.categoryid
      -- Join in the parent category if possible
      LEFT JOIN randys_category r_category_parent ON r_category_parent.categoryid = randys_category.parentid
      LEFT JOIN wp_termmeta wp_termmeta_parent ON wp_termmeta_parent.meta_key = '_randys_category_id'
                              AND wp_termmeta_parent.meta_value = r_category_parent.categoryid

      -- Join in the instruction manuals onto the product
      LEFT JOIN randys_instructionproduct ON randys_instructionproduct.productid = randys_product.productid
      LEFT JOIN randys_instruction ON randys_instruction.instructionid = randys_instructionproduct.instructionid

      -- Join in to get the item descriptions for the warranties, features, and bullets
      LEFT JOIN randys_itemdesc ON randys_itemdesc.ProductNumber = randys_product.ProductNumber
      -- Get the warranties from the itemdesc
      LEFT JOIN randys_webdescription warranty_webdescription ON warranty_webdescription.ID = randys_itemdesc.DescID
                          AND warranty_webdescription.Type = 'Warranty'
      LEFT JOIN randys_webdescription feature_webdescription  ON  feature_webdescription.ID = randys_itemdesc.DescID
                          AND feature_webdescription.Type  = 'Feature'
      LEFT JOIN randys_webdescription bullet_webdescription   ON   bullet_webdescription.ID = randys_itemdesc.DescID
                          AND bullet_webdescription.Type   = 'Bullet'

      -- Join in the brands to find in Manufacturer
      LEFT JOIN randys_brands ON FIND_IN_SET(randys_product.productline, randys_brands.pls) > 0

      -- Since we are using GROUP_CONCAT earlier, we need to group by productid to avoid grouping everything into one
      GROUP BY productid"
    );

    // Go through each of the products
    foreach ($results as $product) {


      $existing_products = $wpdb->get_results(
        $wpdb->prepare(
          "SELECT ID,post_name
          FROM wp_posts
            WHERE ID=(
                SELECT post_id
                  FROM wp_postmeta
                    WHERE meta_key= %s
                      AND meta_value= %d
                    LIMIT 1
            )",
            array('_randy_productid', $product->productid)
        )
      );

      $attributes = array(
        "_visibility"       => 'visible',
        "_sku"              => $product->productnumber,
        "_randy_productid"  => $product->productid,
        "_fullimage"        => $product->fullimage,
        "_proxynumber"      => $product->proxynumber,
        "_price"            => $product->price,
        "_stock"            => $product->qty,
        "_weight"           => $product->weight,
        "_warrantyid"       => $product->warrantyid,
        "_cost"             => $product->cost,
        "_height"           => $product->height,
        "_width"            => $product->width,
        "_length"           => $product->length,
        "_lugdiameter"      => $product->lugdiameter,
        "_hubdiameter"      => $product->hubdiameter,
        "_instruction_name" => $product->instruction_instructionname,
        "_instruction_file" => $product->instruction_filename,
        "_brandname"        => $product->brandname,
        "_brandbannerid"    => $product->brandbannerid,
        "_is_yukon"         => (strpos($product->brandname, 'Yukon') === 0) ? "yes" : "no",
        "_warranty_note"    => $product->warranty,
        "_tech_notes"       => $product->technotes,
        "_price_1"          => $product->list,
        "_price_2"          => $product->price,
        "_price_3"          => $product->map,
        "_price_4"          => $product->p4,
        "_price_5"          => $product->p5,
        "_price_6"          => $product->p6,
        "_manage_stock"     => 'yes',
        "_backorders"       => 'no',
      );

      // Customize the description to be the feature information
      $custom_description = $product->feature;

      // If there are bullet points, add all of the bullet points
      if ($product->bullets) {
        $custom_description .= "<br/><ul><li>" . $product->bullets . "</li></ul>";
      }

      //Insert and Update
      if (count($existing_products) > 0) {

        //Update product at target database.
        $update_values = array($product->title, $custom_description, sanitize_title($product->productnumber));
        $sql_raw = "UPDATE wp_posts
             SET post_title=%s,
               post_content=%s,
               post_name=%s
             WHERE ID=" . $existing_products[0]->ID;

        // Prepare & run the sql
        $wpdb->query($wpdb->prepare($sql_raw, $update_values)); // WPCS: unprepared SQL OK

        // Get the ID from the existing product
        $post_id = $existing_products[0]->ID;

      } else {


        // Run the insert query
        $wpdb->query(
          $wpdb->prepare(
            "INSERT INTO wp_posts
              SET post_name=%s,
                  post_title=%s,
                  post_content=%s,
                  post_type='product',
                  post_excerpt='',
                  to_ping='',
                  pinged='',
                  post_content_filtered=''",
            array(
              sanitize_title($product->productnumber),
              $product->title,
              $custom_description,
            )
          )
        );

        // Get the post ID, this will be used later
        $post_id = $wpdb->insert_id;
      }

      // Now we insert the attributes
      foreach ($attributes as $k => $v) {

        // Check to see if we have the attribute that we're about to insert/update
        $post_meta_results = $wpdb->get_results(
          $wpdb->prepare(
            "SELECT meta_id FROM wp_postmeta WHERE post_id= %d AND meta_key= %s LIMIT 1",
            array($post_id, $k)
          )
        );

        // If we have the attribute, UPDATE it, if not, then INSERT it
        if (count($post_meta_results) > 0) {
          $post_meta_id = $post_meta_results[0]->meta_id;
          $sql = $wpdb->prepare("UPDATE wp_postmeta
              SET meta_value='%s'
              WHERE meta_id= %d", array($v, $post_meta_id));
        }else{
          $sql = $wpdb->prepare("INSERT INTO wp_postmeta
              SET post_id= %d,
                meta_key='%s',
                meta_value='%s'", array($post_id, $k, $v));
        }

        // Finally, run the Insert/Update
        $wpdb->query($sql); // WPCS: unprepared SQL OK
      }

      // Explode out the categories into a list
      $category_id_array = explode(',', $product->category_ids);

      // Explode out the parent categories too
      $category_parent_id_array = explode(',', $product->category_parent_ids);

      // Combine the arrays so we can loop through both and add them
      $all_category_array = array_merge($category_id_array, $category_parent_id_array);

      // Loop through the categories to apply each of the categories to the current product
      foreach ($all_category_array as $category_id) {

        // Run the query to find out if we already have this category
        $existing_relationship = $wpdb->get_row(
          $wpdb->prepare(
            "SELECT * FROM `wp_term_relationships`
                WHERE object_id = %s
                  AND term_taxonomy_id = %s",
            array($post_id, $category_id)
          )
        );

        // If none were found, then let's insert it
        if ( $existing_relationship === null) {

          // Insert the default term meta for a product
          $wpdb->insert(
            'wp_term_relationships',
            array(
              "object_id" => $post_id,
              "term_taxonomy_id" => $category_id,
              "term_order" => 0,
            )
          );
        }
      }
    }

    // Now, let's delete the products that are not in the Randys DB
    $wpdb->query(
      $wpdb->prepare(
        "DELETE wp_posts, wp_postmeta FROM wp_posts
          LEFT JOIN wp_postmeta ON wp_postmeta.post_id = wp_posts.ID

          -- Above deletes everything we need to delete, now we just need to filter properly.
          -- Let's do a subquery to find the right IDs
            WHERE wp_posts.ID IN (
              -- I added `SELECT * FROM (...) AS t` to namespace out the tables
              SELECT * FROM (

                -- Get all of the post_id that have a meta that doesn't match the existing tables.
                SELECT post_id FROM wp_postmeta
                  WHERE meta_key = %s
                    AND meta_value NOT IN (
                      SELECT ProductID FROM randys_product
                    )
              ) AS t
            )
        ",
        array('_randy_productid')
      )
    );
  }

  // This is run at the beginning of fresh_import_products.
  // This function makes sure that all the product categories in the database exist.
  function importProductCategories() {

    // Get wpdb so we can use it here
    global $wpdb;

    // Let's get all the data from the category table.
    $category_results = $wpdb->get_results(
      "SELECT categoryname,
            parentid,
            type,
            categoryid
      FROM randys_category"
    );

    // Go through the categories that we got from the table
    foreach ($category_results as $category) {
      // Insert the category
      insert_category_if_missing($category);
    }

    // Now, let's delete the categories that are not in the Randys DB
    $wpdb->query(
      $wpdb->prepare(
        "DELETE wp_terms, wp_termmeta, wp_term_taxonomy, wp_term_relationships FROM wp_terms
          LEFT JOIN wp_termmeta ON wp_termmeta.term_id = wp_terms.term_id
          LEFT JOIN wp_term_taxonomy ON wp_term_taxonomy.term_id = wp_terms.term_id
          LEFT JOIN wp_term_relationships ON wp_term_relationships.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id

          -- Above deletes everything we need to delete, now we just need to filter properly.
          -- Let's do a subquery to find the right IDs (I added 'SELECT * FROM' to namespace out the tables)
            WHERE wp_terms.term_id IN (SELECT * FROM (

                -- Here we get all the term_ids of all the products
                SELECT term_id FROM wp_term_taxonomy
                  WHERE taxonomy = %s
                    -- Here we filter out the ID list by categories we want to keep
                    AND term_id NOT IN (
                        SELECT term_id FROM wp_termmeta
                          WHERE meta_key = %s
                            AND CAST(meta_value AS UNSIGNED) IN (SELECT categoryid FROM randys_category
                                                                    WHERE parentid = %d) -- Added so that children are also deleted
                    )
            ) AS t)",
        array('product_cat', '_randys_category_id', 0)
      )
    );
  }

  // Inserts a category. The $category needs to be a row from the randys table 'categories'
  function insert_category_if_missing($category) {

    // Ignore all categories that are children of other categories!
    if ($category->parentid !== '0') {
      return 0;
    }

    // Get wpdb so we can use it
    global $wpdb;

    // Run the query to find the woo category
    $existing_category = $wpdb->get_row(
      $wpdb->prepare(
        "SELECT term_id,
                   name,
                   slug,
                   term_group
        FROM wp_terms
        WHERE term_id = (SELECT term_id FROM wp_termmeta
                              WHERE meta_key = '_randys_category_id'
                                AND meta_value = %s LIMIT 1)",
        array($category->categoryid)
      )
    );

    // Does this category not exist?
    if ( $existing_category === null ) {
      // The category does not yet exist, so me must insert it

      // Before we go ahead and insert this category, let's check if it has a parent..
      if ($category->parentid !== '0') {

        // Get the r_parent_id, this will be used later on
        $r_parent_id = $category->parentid;

        // Now, let's get the data for the parent
        $parent_category = $wpdb->get_row(
          $wpdb->prepare(
            "SELECT categoryname,
                    parentid,
                    type,
                    categoryid
            FROM randys_category
              WHERE categoryid = %s",
            array($r_parent_id)
          )
        );

        // Now, let's make sure the parent has been put into the system before setting this one as the parent
        $parent_id = insert_category_if_missing($parent_category);

      } else {
        // Set the parent_id to 0 to signify there is no parent
        $parent_id = 0;
      }

      // Get a slug, and make sure it isn't already taken
      $slug = find_unique_term_slug(sanitize_title($category->categoryname));

      // Insert the category
      $wpdb->insert(
        'wp_terms',
        array(
          "name" => $category->categoryname,
          "slug" => $slug,
          "term_group" => 0,
        )
      );

      // Get the ID of the just inserted category
      $term_id = $wpdb->insert_id;

      // Insert the default term meta for a product
      $wpdb->insert(
        'wp_termmeta',
        array(
          "term_id" => $term_id,
          "meta_key" => "order",
          "meta_value" => 0,
        )
      );

      // Add an extra term meta for the randys category ID
      $wpdb->insert(
        'wp_termmeta',
        array(
          "term_id" => $term_id,
          "meta_key" => "_randys_category_id",
          "meta_value" => $category->categoryid,
        )
      );

      // Insert the taxonomy for the term, here we point at the parent.
      $wpdb->insert(
        'wp_term_taxonomy',
        array(
          "term_id" => $term_id,
          "taxonomy" => "product_cat",
          "description" => "",
          "parent" => $parent_id,
          "count" => 0,
        )
      );
    } else {
      // Category exists, so let's update it

      // We need to check the slug before we update it, as it could be a duplicate
      $slug = find_unique_term_slug(sanitize_title($category->categoryname), $existing_category->slug);

      // Update the name and the slug
      $wpdb->update(
        'wp_terms',
        array(
          'name' => $category->categoryname,
          'slug' => $slug,
        ),
        array(
          'term_id' => $existing_category->term_id,
        )
      );

      // Save the term id to be sent back
      $term_id = $existing_category->term_id;
    }

    // Return the term, this helps with missing parents
    return $term_id;
  }

  // Finds a unique term slug
  function find_unique_term_slug($sanitized_slug, $acceptable_slug = null) {
    global $wpdb;

    // Now we know that the requirements have been met, check to see if our slug is unique
    // Set the slug
    $slug = null;
    $slug_to_test = $sanitized_slug;
    $index = 1;

    // Loop through the slugs till we get one that isn't taken
    do {

      // Run the query to find the woo category
      $duplicate_slug = $wpdb->get_row(
        $wpdb->prepare(
          "SELECT * FROM wp_terms WHERE slug = %s",
          array($slug_to_test)
        )
      );

      // The slug is unique, save it and let's move on!
      if ($duplicate_slug === null) {
        $slug = $slug_to_test;
      } elseif ($slug_to_test === $acceptable_slug) {
        // This matches the acceptable slug, use this anyways
        $slug = $acceptable_slug;
      } else {
        // The slug isn't unique, let's try adding a '1' to the end of it and test that
        $slug_to_test = $sanitized_slug . '-' . $index;
        $index += 1;
      }
    } while ($slug === null);

    // Return our found slug
    return $slug;
  }
