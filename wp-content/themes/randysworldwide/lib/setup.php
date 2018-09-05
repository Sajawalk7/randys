<?php

namespace Roots\Sage\Setup;

use Roots\Sage\Assets;

/**
 * Theme setup
 */
function setup() {
  // Enable features from Soil when plugin is activated
  // https://roots.io/plugins/soil/
  add_theme_support('soil-clean-up');
  add_theme_support('soil-nav-walker');
  add_theme_support('soil-nice-search');
  add_theme_support('soil-jquery-cdn');
  add_theme_support('soil-relative-urls');

  // Make theme available for translation
  // Community translations can be found at https://github.com/roots/sage-translations
  load_theme_textdomain('sage', get_template_directory() . '/lang');

  // Enable plugins to manage the document title
  // http://codex.wordpress.org/Function_Reference/add_theme_support#Title_Tag
  add_theme_support('title-tag');

  // Register wp_nav_menu() menus
  // http://codex.wordpress.org/Function_Reference/register_nav_menus
  register_nav_menus([
    'primary_navigation' => __('Primary Navigation', 'sage'),
  ]);

  // Enable post thumbnails
  // http://codex.wordpress.org/Post_Thumbnails
  // http://codex.wordpress.org/Function_Reference/set_post_thumbnail_size
  // http://codex.wordpress.org/Function_Reference/add_image_size
  add_theme_support('post-thumbnails');

  // Enable post formats
  // http://codex.wordpress.org/Post_Formats
  add_theme_support('post-formats', ['aside', 'gallery', 'link', 'image', 'quote', 'video', 'audio']);

  // Enable HTML5 markup support
  // http://codex.wordpress.org/Function_Reference/add_theme_support#HTML5
  add_theme_support('html5', ['caption', 'comment-form', 'comment-list', 'gallery', 'search-form']);

  // Use main stylesheet for visual editor
  // To add custom styles edit /assets/styles/layouts/_tinymce.scss
  add_editor_style(Assets\asset_path('styles/main.css'));
}
add_action('after_setup_theme', __NAMESPACE__ . '\\setup');

/**
 * Register sidebars
 */
function widgets_init() {
  register_sidebar([
    'name'          => __('Primary', 'sage'),
    'id'            => 'sidebar-primary',
    'before_widget' => '<section class="widget %1$s %2$s">',
    'after_widget'  => '</section>',
    'before_title'  => '<h3>',
    'after_title'   => '</h3>',
  ]);

  register_sidebar([
    'name'          => __('Footer', 'sage'),
    'id'            => 'sidebar-footer',
    'before_widget' => '<section class="widget %1$s %2$s">',
    'after_widget'  => '</section>',
    'before_title'  => '<h3>',
    'after_title'   => '</h3>',
  ]);
}
add_action('widgets_init', __NAMESPACE__ . '\\widgets_init');

/**
 * Register ACF Options Page
 */
if( function_exists('acf_add_options_page') ) {
  acf_add_options_page(array(
    'page_title' 	=> 'Pagebuilder Shared Assets',
    'menu_title'	=> 'Pagebuilder Assets',
    'menu_slug' 	=> 'pagebuilder-shared-assets',
    'capability'	=> 'edit_posts',
    'redirect'		=> false,
  ));

  acf_add_options_page(array(
    'page_title' 	=> 'Global Assets',
    'menu_title'	=> 'Global Assets',
    'menu_slug' 	=> 'global-assets',
    'capability'	=> 'edit_posts',
    'redirect'		=> false,
  ));
}

/**
 * Determine which pages should NOT display the sidebar
 */
function display_sidebar() {
  static $display;

  isset($display) || $display = !in_array(true, [
    // The sidebar will NOT be displayed if ANY of the following return true.
    // @link https://codex.wordpress.org/Conditional_Tags
    is_404(),
    is_front_page(),
    is_page_template('template-custom.php'),
    is_archive(),
    is_single(),
    is_woocommerce(),
    is_cart(),
    is_checkout(),
    is_search(),
    'is_account_page',
  ]);

  return apply_filters('sage/display_sidebar', $display);
}

/**
 * Theme assets
 */
function assets() {
  wp_enqueue_style('sage/css', Assets\asset_path('styles/main.css'), false, null);
  wp_enqueue_style('open_sans', 'https://fonts.googleapis.com/css?family=Open+Sans:300i,600,600i,700,800', false, null);

  if (is_single() && comments_open() && get_option('thread_comments')) {
    wp_enqueue_script('comment-reply');
  }

  wp_register_script('sage/js', Assets\asset_path('scripts/main.js'), ['jquery'], null, true);
  $params = array(
    'ajax_url' => admin_url('admin-ajax.php', 'https'),
    'ajax_nonce' => wp_create_nonce('main_ajax_nonce'),
  );
  wp_localize_script('sage/js', 'wpAjax', $params);
  wp_enqueue_script('sage/js');
  wp_enqueue_script('fontAwesome/js', 'https://use.fontawesome.com/0052a2afb6.js', null, null, true);

  if( is_page('diff-wizard') ) {
    wp_register_script('products-filter/js', Assets\asset_path('scripts/products-filter.js'), ['jquery'], null, true);
    wp_enqueue_script('products-filter/js');
  }


  if( is_product_category() || is_page('product-category') || is_page_template('template-product-browsing-pagebuilder.php') ) {
    wp_register_script('browsing/js', Assets\asset_path('scripts/product-browsing.js'), ['jquery'], null, true);
    $params = array(
      'ajax_url' => admin_url('admin-ajax.php', 'https'),
      'ajax_nonce' => wp_create_nonce('browsing_ajax_nonce'),
    );
    wp_localize_script('browsing/js', 'browsing', $params);
    wp_enqueue_script('browsing/js');
  } elseif ( is_account_page() ) {
    wp_register_script('downloadable-product/js', Assets\asset_path('scripts/downloadable-product.js'), ['jquery'], null, true);
    wp_enqueue_script('downloadable-product/js');
    wp_register_script('downloadable-image/js', Assets\asset_path('scripts/downloadable-image.js'), ['jquery'], null, true);
    wp_enqueue_script('downloadable-image/js');
    wp_register_script('custom-export/js', Assets\asset_path('scripts/custom-export.js'), ['jquery', 'jquery-form'], null, true);
    wp_enqueue_script('custom-export/js');
    wp_register_script('order-express/js', Assets\asset_path('scripts/order-express.js'), ['jquery'], null, true);
    wp_enqueue_script('order-express/js');
    wp_register_script('bootstrap3-typeahead/js', Assets\asset_path('scripts/bootstrap3-typeahead.js'), ['jquery'], null, true);
    wp_enqueue_script('bootstrap3-typeahead/js');
  }

  // <!-- BEGIN GCR Badge Code -->
  wp_register_script('google-customer-review-badge/js', 'https://apis.google.com/js/platform.js?onload=renderBadge', null, null, true);
  wp_enqueue_script('google-customer-review-badge/js');
  // <!-- END GCR Badge Code -->
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\assets', 100);

/**
 * Load custom admin styles
 */
function load_custom_wp_admin_style() {
        wp_register_style( 'custom_wp_admin_css', Assets\asset_path('/styles/admin.css'), false, '1.0.0' );
        wp_enqueue_style( 'custom_wp_admin_css' );
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\load_custom_wp_admin_style' );

/**
 * Add async and defer attributes to enqueued scripts
 * http://scottnelle.com/756/async-defer-enqueued-wordpress-scripts/
 */
function add_async_defer_attributes($tag, $handle) {
  // the handles of the enqueued sscripts we want to add async and defer (add to this list if more are needed)
  $async_scripts = array('google-customer-review-badge/js');

  if (in_array($handle, $async_scripts))
  {
    return str_replace(' src', ' async="async" defer="defer" src', $tag);
  }

  return $tag;
}
add_filter('script_loader_tag', __NAMESPACE__ . '\\add_async_defer_attributes', 10, 2);
