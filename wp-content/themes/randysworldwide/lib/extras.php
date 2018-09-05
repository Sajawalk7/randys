<?php

namespace Roots\Sage\Extras;

use Roots\Sage\Setup;
use Roots\Sage\RANDYS;

/*
 * Get Diff image directory
 */
 global $diff_image_path;
$diff_image_path = '/wp-content/uploads/differential-images/';

/**
 * Add <body> classes
 */
function body_class($classes) {
  // Add page slug if it doesn't exist
  if (is_single() || is_page() && !is_front_page()) {
    if (!in_array(basename(get_permalink()), $classes)) {
      $classes[] = basename(get_permalink());
    }
  }

  // Add class if sidebar is active
  if (Setup\display_sidebar()) {
    $classes[] = 'sidebar-primary';
  }

  return $classes;
}
add_filter('body_class', __NAMESPACE__ . '\\body_class');

/**
 * Clean up the_excerpt()
 */
function excerpt_more() {
  if( is_search() ||
  is_shop() ||
  is_post_type_archive( 'resource-center' ) ||
  is_page( 'diff-wizard' ) ||
  is_product_category() ) {
    return '&hellip;';
  } else {
    return '&hellip; <a href="' . get_permalink() . '">' . __('Continued', 'sage') . '</a>';
  }
}
add_filter('excerpt_more', __NAMESPACE__ . '\\excerpt_more');

/**
 * Custom Image Sizes
*/
add_image_size( 'hero', 1600, 574 );
add_image_size( 'tile-lg', 1140, 556 );
add_image_size( 'tile', 500, 500 );

/**
 * update the_excerpt() length
**/
function custom_excerpt_lengths( $length ) {
  if( is_post_type_archive( 'resource-center' ) ) {
    return 20;
  } elseif( is_search() ) {
    return 15;
  }
}
add_filter( 'excerpt_length', __NAMESPACE__ . '\\custom_excerpt_lengths', 999 );

/*
* Custom Pagination output
**/
function base_pagination($the_query = NULL, $page = 'paged') {
    if ( NULL == $the_query ) {
      global $wp_query;
    } else {
      $wp_query = $the_query;
    }
    $big = 999999999; // This needs to be an unlikely integer

    // For more options and info view the docs for paginate_links()
    // http://codex.wordpress.org/Function_Reference/paginate_links
    $paginate_links = paginate_links( array(
        'base' => str_replace( $big, '%#%', get_pagenum_link($big) ),
        'current' => max( 1, get_query_var($page) ),
        'format' => '?paged=%#%',
        'prev_text'    => __('«'),
        'next_text'    => __('»'),
        'end_size'     => 1,
        'mid_size'     => 3,
        'total' => $wp_query->max_num_pages,
    ) );

    // Display the pagination if more than one page is found
    $pagination = '';
    if ( $paginate_links ) {
        $pagination .= '<div class="pagination">';
        $pagination .= $paginate_links;
        $pagination .= '</div><!--// end .pagination -->';
        return $pagination;
    }
}

/**
 * Init Custom Post types
 */
function register_custom_posts_init() {

  // Differntials
  register_post_type( 'differentials',
    array(
      'labels' => array(
        'name'            => __( 'Differentials' ),
        'singular_name'   => __( 'Differential' ),
        'menu_name'       => __( 'Differentials' ),
      ),
      'public'              => true,
      'menu_icon'           => 'dashicons-admin-page',
      'exclude_from_search' => true,
      'has_archive'         => false,
      'rewrite'             => array('slug' => 'differential-identification'),
      'show_in_admin_bar'   => true,
      'show_ui'             => true,
      'capability_type'     => 'post',
      'hierarchical'        => true,
      'supports'            => array(
        'title', 'revisions', 'editor',
      ),
    )
  );

  // Resource Center
  register_post_type( 'resource-center',
    array(
      'labels' => array(
        'name'            => __( 'Resource Center' ),
        'singular_name'   => __( 'Resource Center' ),
        'menu_name'       => __( 'Resource Center' ),
      ),
      'public'              => true,
      'menu_icon'           => 'dashicons-format-aside',
      'exclude_from_search' => false,
      'has_archive'         => true,
      'rewrite'             => array('slug' => 'resource-center'),
      'show_in_admin_bar'   => true,
      'show_ui'             => true,
      'capability_type'     => 'post',
      'hierarchical'        => true,
      'supports'            => array(
        'title', 'revisions', 'thumbnail', 'editor',
      ),
    )
  );

  // Related Videos
  register_post_type( 'related-videos',
    array(
      'labels' => array(
        'name'            => __( 'Related Videos' ),
        'singular_name'   => __( 'Related Video' ),
        'menu_name'       => __( 'Related Videos' ),
      ),
      'public'              => false,
      'menu_icon'           => 'dashicons-format-video',
      'exclude_from_search' => true,
      'publicly_queriable' => true,
      'has_archive'         => false,
      'rewrite'             => array('slug' => 'related-videos'),
      'show_in_admin_bar'   => true,
      'show_ui'             => true,
      'capability_type'     => 'post',
      'hierarchical'        => true,
      'supports'            => array(
        'title', 'revisions', 'editor', 'excerpt',
      ),
    )
  );

  // Custom Taxonomy: Resource Cateogory
  $labels = array(
    'name'               => 'Resource Categories',
    'singular_name'      => 'Resource Category',
    'menu_name'          => 'Categories',
  );

  $args = array(
    'labels'             => $labels,
    'rewrite'            => array( 'slug' => 'resource-category' ),
    'hierarchical'       => true,
  );

  register_taxonomy( 'resource_categories', 'resource-center', $args );
}
add_action('init', __NAMESPACE__ . '\\register_custom_posts_init');


/*
* Add custom tables into search
**/

function products_search_join( $join ) {
  global $wpdb;

  if( is_search() ) {
    $join .= " LEFT JOIN " . $wpdb->postmeta . " r_pm ON r_pm.post_id = wp_posts.ID AND r_pm.meta_key = '_randy_productid' LEFT JOIN randys_product ON r_pm.meta_value = randys_product.productid LEFT JOIN randys_oemparts ON r_pm.meta_value = randys_oemparts.productid";
  }

  return $join;
}
add_filter('posts_join', __NAMESPACE__ . '\\products_search_join' );

function products_search_where( $where ) {

    if( is_search() ) {
	if ( true === RANDYS\is_wholesale() )
    {
	//echo '<h3 class="container" style="padding: 20px;text-align: center;color: #ffffff;background: #0b3960;text-transform: uppercase;letter-spacing: 1px;">This is wholesale search</h3>';
	// Wholesale Search - Logged-in Wholesale
    //$where=preg_replace("/\{(.*?)\}/"," {$1} ",$where);
    $where = preg_replace(
        "/\(\s*wp_posts\.post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
        "(randys_product.keywords LIKE $1)", $where );
	$where = preg_replace(
        "/\(\s*wp_posts\.post_excerpt\s+LIKE\s*(\'[^\']+\')\s*\)/",
        "(randys_product.productnumber LIKE $1)", $where );
	$where = preg_replace(
        "/\(\s*wp_posts\.post_content\s+LIKE\s*(\'[^\']+\')\s*\)/",
        "(randys_product.proxynumber LIKE $1)", $where );
	}else{
		//echo '<h3 class="container" style="padding: 20px;text-align: center;color: #ffffff;background: #0b3960;text-transform: uppercase;letter-spacing: 1px;">This is simple search</h3>';
		//Simple Search - Not Logged-in AND Logged-in retail
		/* chagne search operator */
		$where = preg_replace(
       "/\(\s*wp_posts\.post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
       "(wp_posts.post_title LIKE $1) OR (randys_product.productnumber LIKE $1) OR (randys_product.proxynumber LIKE $1) OR (randys_product.description LIKE $1) OR (randys_product.keywords LIKE $1)", $where);
	   // echo "<hr>";
		//echo $where;
	}
   }
  //echo "<hr>";
// echo $where;
  //exit;

  return $where;
}
add_filter('posts_where', __NAMESPACE__ . '\\products_search_where', 10 );

function product_search_groupby( $groupby ) {
  global $wpdb;

  if( !is_search() ) {
    return $groupby;
  }

  // we need to group on post ID

  $mygroupby = "{$wpdb->posts}.ID";

  if( preg_match( "/$mygroupby/", $groupby )) {
    // grouping we need is already there
    return $groupby;
  }

  if( !strlen(trim($groupby))) {
    // groupby was empty, use ours
    return $mygroupby;
  }

  // wasn't empty, append ours
  return $groupby . ", " . $mygroupby;
}
add_filter('posts_groupby', __NAMESPACE__ . '\\product_search_groupby' );

/*
* Only include products into search results
**/
function SearchFilter($query) {

  if ( $query->is_search && is_search() ) {
    $query->set('post_type', 'product');
  }

  return $query;

}
add_filter('pre_get_posts', __NAMESPACE__ . '\\SearchFilter');

// Admin menu for import related videos csv form
function video_setup_menu() {
    add_menu_page( 'Related Videos Import', 'Related Videos Import', 'manage_options', 'videos-import', __NAMESPACE__ . '\\videos_import_init' );
}
add_action('admin_menu', __NAMESPACE__ . '\\video_setup_menu');

// Admin menu page for  import related videos csv form
function videos_import_init() {
  global $wpdb;
  $okay_to_continue = false;

  // Check if there is an uploaded file
  if ( array_key_exists('csv', $_FILES) ) {
    if ( $_FILES['csv']['size'] > 0 ) {
      //get the csv file
      $file = $_FILES['csv']['tmp_name'];
      $handle = fopen($file, "r");

      // Read in first line of data
      $data = fgetcsv($handle);

      // Make sure the header row is correct, we should be expecting this
      if (strtolower($data[0]) == strtolower("URL")) {
        // We matched the header! We are good to continue
        $okay_to_continue = true;
      } else {
        error_log('Import file not formatted correctly.');
      }

      // loop through the csv file and insert into database
      while ( ($data = fgetcsv($handle)) && ($okay_to_continue) ){
        // make array of product skus and get post ids from each
        $skus = explode(', ', $data[3]);
        $product_ids = array();
        foreach ( $skus as $value ) {
          $args = array(
            'post_type'   => 'product',
            'meta_query'  => array(
              array(
                'value' => $value,
              ),
            ),
          );
          $query = new \WP_Query( $args );
          if( $query->have_posts() ) {
            while( $query->have_posts() ) {
              $query->the_post();
              array_push($product_ids, strval($query->post->ID));
            }
          }
          wp_reset_postdata();
        }

        // make post, get the id
        $new_post = (array(
          'post_content' => $data[0],
          'post_title' => $data[1],
          'post_excerpt' => $data[2],
          'post_type' => 'related-videos',
          'post_status' => 'publish',
        ));
        $new_post_id = wp_insert_post($new_post);

        // update the post terms
        update_field('field_58c1ad1dd6027', $product_ids, $new_post_id);
      }

      echo $_FILES['csv']['name'] . ' imported.';
    }
  }
  ?>
  <form action="" method="post" enctype="multipart/form-data" id="form1">
      <h1>Import CSV</h1>
      <input aria-label="Select file" type="file" enctype="multipart/form-data" id="csv" name="csv">
      <input aria-label="Upload" type="submit" name="Submit" value="Submit" class="upload_button"/>
    </form>
  <?php
}

// Register Pre-populate Make Meta Box
function pre_populate_make_meta_box() {
  global $post;
  if( 'template-product-browsing-pagebuilder.php' == get_post_meta( $post->ID, '_wp_page_template', true ) ) {
    add_meta_box(
      'rm-meta-box-id',
      esc_html__( 'Pre-populate make',
      'text-domain' ),
      'Roots\Sage\Extras\pre_populate_make_meta_box_callback',
      'page',
      'normal', 'high'
    );
  }
}
add_action( 'add_meta_boxes', __NAMESPACE__ . '\\pre_populate_make_meta_box');

// Add fields to Pre-populate Make Meta Box
function pre_populate_make_meta_box_callback( $object ) {
  global $wpdb;

  wp_nonce_field(basename(__FILE__), "meta-box-nonce");

  $make_results = $wpdb->get_results(
    "SELECT DISTINCT Make FROM randys_advancedsearch ORDER BY Make"
  );

  ?>
    <label for="selectamake">Select a Make (optional)</label>
    <select id="selectamake" name="meta-box-dropdown">
      <option value="" selected>None</option>
        <?php
          foreach ( $make_results as $make ) {
            if($make->Make == get_post_meta($object->ID, "meta-box-dropdown", true) && $make->Make !== null) {
              echo '<option selected>' . $make->Make . '</option>';
            } elseif( $make->Make !== null) {
              echo "<option value='" . $make->Make . "'>" . $make->Make . "</option>\n";
            }
          }
        ?>
    </select>
  <?php
}

// Save Pre-populate Make Meta Box values
function save_custom_meta_box($post_id, $post) {

  if (!isset($_POST["meta-box-nonce"]) ||
      !wp_verify_nonce($_POST["meta-box-nonce"], basename(__FILE__))) {
    return $post_id;
  }

  if(!current_user_can("edit_post", $post_id)) {
    return $post_id;
  }

  if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
    return $post_id;
  }

  $slug = "page";
  if($slug != $post->post_type) {
    return $post_id;
  }

  $meta_box_dropdown_value = "";

  if(isset($_POST["meta-box-dropdown"])) {
    $meta_box_dropdown_value = $_POST["meta-box-dropdown"];
  }
  update_post_meta($post_id, "meta-box-dropdown", $meta_box_dropdown_value);

}

add_action("save_post", __NAMESPACE__ . "\\save_custom_meta_box", 10, 3);

// Set up URL params for search sort
function setup_search_parameter() {
global $wp;
  $wp->add_query_var( 'sortby' );
}
add_action( 'init', __NAMESPACE__ . '\\setup_search_parameter' );

function map_search_parameter( $wp_query ) {
  if ( $meta_value = $wp_query->get( 'sortby' ) ) {
    if($meta_value === 'price') {
      $wp_query->set( 'meta_key', RANDYS\check_user_price_level() );
      $wp_query->set( 'orderby', 'meta_value_num' );
      $wp_query->set( 'order', 'ASC' );
    } elseif($meta_value === 'yukon') {
      $wp_query->set( 'meta_key', '_is_yukon' );
      $wp_query->set( 'orderby', 'meta_value' );
      $wp_query->set( 'order', 'DESC' );
    } elseif($meta_value === 'sku') {
      $wp_query->set( 'meta_key', '_sku' );
      $wp_query->set( 'orderby', 'meta_value' );
      $wp_query->set( 'order', 'ASC' );
    }
  }
}
add_action( 'parse_query', __NAMESPACE__ . '\\map_search_parameter' );


/**
 * =================================================
 * Disable Emoji
 * http://wordpress.stackexchange.com/questions/185577/disable-emojicons-introduced-with-wp-4-2
 * https://wordpress.org/plugins/disable-emojis/
 * =================================================
 */

/**
 * Filter function used to remove the tinymce emoji plugin.
 *
 * @param    array  $plugins
 * @return   array             Difference betwen the two arrays
 */
function disable_emojis_tinymce( $plugins ) {
    if ( is_array( $plugins ) ) {
        return array_diff( $plugins, array( 'wpemoji' ) );
    } else {
        return array();
    }
}

/**
 * Remove emoji CDN hostname from DNS prefetching hints.
 *
 * @param  array  $urls          URLs to print for resource hints.
 * @param  string $relation_type The relation type the URLs are printed for.
 * @return array                 Difference betwen the two arrays.
 */
function disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
    if ( 'dns-prefetch' == $relation_type ) {
        /** This filter is documented in wp-includes/formatting.php */
        $emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2.2.1/svg/' );

        $urls = array_diff( $urls, array( $emoji_svg_url ) );
    }

    return $urls;
}

function disable_wp_emojicons() {

  // all actions related to emojis
  remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
  remove_action( 'admin_print_styles', 'print_emoji_styles' );
  remove_action( 'wp_print_styles', 'print_emoji_styles' );
  remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
  remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
  remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
  remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

  // filter to remove TinyMCE emojis
  add_filter( 'tiny_mce_plugins', __NAMESPACE__ . '\\disable_emojis_tinymce' );

  // remove DNS prefetch
  add_filter( 'wp_resource_hints', __NAMESPACE__ . '\\disable_emojis_remove_dns_prefetch', 10, 2 );
}

add_action( 'init', __NAMESPACE__ . '\\disable_wp_emojicons' );

// Gravity forms 
function email_handler( $email ) {
  if ( 'New submission from Newsletter' === $email['subject'] ) {
    $from = explode('"', $email['headers']['From'], 3)[1];
    
    // Get $wpdb to use in queries
    global $wpdb;

    $existing_customer = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM wp_users WHERE user_email=%s", $from ) );

    if( NULL !== $existing_customer ) {
      $email['abort_email'] = true;
      update_user_meta($existing_customer, 'NewsletterOptIn', true);
    }
  }

  return $email;
}
add_action( 'gform_pre_send_email', __NAMESPACE__ . '\\email_handler' );

/**
 * Alters the title on resource page.
 *
 * @param string $title The current title.
 * @param array $args The title generation arguments.
 * @param bool $escape Whether the title is being escaped.
 * @return string Title. Does not need to be escaped.
 */
function resource_page_title( $title = '', $args = array(), $escape = true ) {

    /**
     * @link https://developer.wordpress.org/reference/functions/is_post_type_archive/
     */
    if ( is_post_type_archive( 'resource-center' ) ) {
      $title = 'Drivetrain & Differential Tech Resources | ' . get_bloginfo();
    }

    return $title;
}
add_filter( 'the_seo_framework_pro_add_title', __NAMESPACE__ . '\\resource_page_title', 10, 3 );


/**
 * Alters the description on resource page.
 *
 * @param string $description The current description.
 * @param int $id The Post, Page or Term ID.
 * @return string Description. Does not need to be escaped.
 */
function resource_description( $description = '', $id = 0 ) {

    /**
     * @link https://developer.wordpress.org/reference/functions/is_post_type_archive/
     */
    if ( is_post_type_archive( 'resource-center' ) ) {
        $description = 'RANDYS is your source for differential and drivetrain expertise. Our Resource Center is packed with install guides, tech tips, video walkthroughs and more!';
    }

    return $description;
}
add_filter( 'the_seo_framework_description_output', __NAMESPACE__ . '\\resource_description', 10, 2 );
add_filter( 'the_seo_framework_ogdescription_output', __NAMESPACE__ . '\\resource_description', 10, 2 );
add_filter( 'the_seo_framework_twitterdescription_output', __NAMESPACE__ . '\\resource_description', 10, 2 );
