<?php
/**
 * Plugin Name: ShortStack for WP
 * Plugin URI: http://www.shortstack.com
 * Description: Provides a shortcode for embedding published ShortStack Campaigns into WordPress
 * Version: 1.0.3
 * Author: ShortStack.com
 * Author Email: theteam@shortstacklab.com
 * Author URI: http://www.shortstack.com/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// Inserts iframe into content via the [shortstack] shortcode.
// Options:
//  smart_url: The smart url for the campaign
//  width: Width of the embedded iframe
//  height: Height of the embedded iframe
//
if ( !function_exists( 'shortstack_embed_campaign' ) ) {
  function shortstack_embed_campaign($args){
    // Parse shortcode attributes
    $settings = shortcode_atts(array(
      'smart_url' => '',
      'width' => '100%',
      'height' => '800',
      'responsive' => 'false',
      'v_offset' => 0,
      'autoscroll_p' => 'true'
    ), $args);
    return $settings['responsive'] === 'true' ? shortstack_build_responsive_embed($settings) : shortstack_build_fixed_embed($settings);
  }
}

//Load responsive JS by default because it's easiest to unload it in the fixed function.
if ( !function_exists( 'shortstack_load_responsive_js' ) ) {
  function shortstack_load_responsive_js() {
    if (is_ssl()) {
      wp_enqueue_script(
          'iframeResizer',
          'https://d2xcq4qphg1ge9.cloudfront.net/javascript/responsive_embed/20150624/iframeResizer.min.js',
          '',
          '',
          true
      );
    } else {
      wp_enqueue_script(
          'iframeResizer',
          'http://d2xcq4qphg1ge9.cloudfront.net/javascript/responsive_embed/20150624/iframeResizer.min.js',
          '',
          '',
          true
      );
    }
    wp_enqueue_script(
        'shortstack-for-wp',
        plugins_url('/public/js/shortstack-for-wp.js', __FILE__),
        array('iframeResizer'),
        '',
        true
    );
  }
}

// Add md5 verification to iframeResizer
if ( !function_exists( 'shortstack_add_script_attributes' ) ) {
  function shortstack_add_script_attributes($tag, $handle) {
      if ('shortstack-for-wp' === $handle) {
         return str_replace(' src', '\
          integrity="sha512-Kus20MO89rJhySoo97ZltzVqMStNd/YvZ9MEWLwVst86YX8CZiaM+aP9ictDF7xosHJKfUWt2uaZgji7fzoJBw==" \
          crossorigin="anonymous" \
          src', $tag);
      }
      if ('iframeResizer' === $handle) {
         return str_replace(' src', '\
          integrity="sha512-xXLUe88ZHgBh2yfp+BZqGZPRx/phHJ6ur0KG2GB7iLxcsfygzbErBslNarACjsM5Cuyg11twTgIgf6b1cBxEFw==" \
          crossorigin="anonymous" \
          src', $tag);
      }
      return $tag;
  }
}


if ( !function_exists( 'shortstack_unload_responsive_js' ) ) {
  function shortstack_unload_responsive_js() {
    wp_dequeue_script( 'iframeResizer' );
    wp_dequeue_script( 'shortstack-for-wp' );
  }
}

if ( !function_exists( 'shortstack_build_fixed_embed' ) ) {
  function shortstack_build_fixed_embed($settings){
    shortstack_unload_responsive_js();

    $iframe = '<iframe src="'.$settings['smart_url'].'?embed=1" id="'.
      shortstack_get_id($settings['smart_url']).'"'.
      ' width="'.$settings['width'].'" height="'.$settings['height'].'"'.
      ' scrolling="auto" seamless="seamless" frameborder="0">'.
      '</iframe>';
    return $iframe;
  }
}

if ( !function_exists( 'shortstack_build_responsive_embed' ) ) {
  function shortstack_build_responsive_embed($settings){
    $iframe_src = preg_replace('/http(s)?:/i', '', $settings['smart_url']) . '?embed=1';
    $iframe_src .= '&v_offset=' . intval($settings['v_offset']);
    $iframe_src .= '&autoscroll_p=' . ($settings['autoscroll_p'] === 'true' ? 1 : 0);
    $iframe = "<iframe class='campaign_embed campaign_embed_responsive' src='". $iframe_src ."'
      seamless='seamless' frameborder='0' width='100%' scrolling='no'></iframe>";
     return $iframe;
  }
}

if ( !function_exists( 'shortstack_get_id' ) ) {
  function shortstack_get_id($smart_url){
    $i = strrpos($smart_url, '/');
    $shortcode = substr($smart_url, $i);
    return 'sscampaign_'.$shortcode;
  }
}

add_action( 'wp_enqueue_scripts',  'shortstack_load_responsive_js');
add_shortcode("shortstack", 'shortstack_embed_campaign');

add_filter('iframeResizer', 'shortstack_add_script_attributes', 10, 2);
add_filter('shortstack-for-wp', 'shortstack_add_script_attributes', 10, 2);

?>
