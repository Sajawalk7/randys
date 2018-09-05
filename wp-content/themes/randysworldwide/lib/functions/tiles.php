<?php
/**
 *
 * Displays a tile layout
 *
 * @param string $grid_size         size of column (optional)
 * @param string $color             color of tile
 * @param sting $image_position     display position of image
 * @param string $primary_image     URL of primary image
 * @param string $title             title of tile
 * @param string $description       description of tile
 * @param string $product_image     URL for product image within content (optional)
 * @param string $video             URL for video (optional)
 * @param string $button_link       URL for CTA button (optional)
 * @param string $button_label      text for CTA button (optional)
 * @param boolean $background_size  add class to specify background-size (optional)
 *
 */

function get_tile($grid_size, $color, $image_position, $primary_image, $title, $description, $product_image = null, $video = null, $button_link = null, $button_label = null, $background_size = null, $fixed_height = null) {


  // Deterime what $product_image was selected
  if( $product_image === '/wp-content/themes/randysworldwide/assets/images/yukon_logo_white.svg') {
    $product_color = 'purple';
  } elseif( $product_image === '/wp-content/themes/randysworldwide/assets/images/usa_standard_logo_color.svg' ) {
    $product_color = 'tan';
  } else {
    $product_color = '';
  }

  // Declare column width classes
  if( $grid_size === null ) {
    $col_size = 'default';
  } elseif( $grid_size === 'full' ) {
    $col_size = 'full';
  } else {
    $col_size = 'half';
  }

  // Declare what color tile is
  if( $color === 'dark' ) {
    $color_class = 'dark';
  } else {
    $color_class = 'light';
  }

  // Declare what position image is in
  if( $image_position === 'top') {
    $image_position_class = 'top';
  } else {
    $image_position_class = 'left';
  }

  // Declare body column size dependent on if we have a button
  if( $button_link != null && $image_position === 'top' ) {
    $body_column_size = 'col-xs-9';
  } else {
    $body_column_size = 'col-xs-12';
  }

  // Declare class that specifies what background-size property to use
  $bg_size = true === $background_size ? ' tile__image-container--contain' : '';

  // Declare class that specifies a fixed height tile
  $fixed_height_class = true === $fixed_height ? ' tile--fixed-height' : '';

  $regex_pattern = "/(youtube.com|youtu.be)\/(watch)?(\?v=)?(\S+)?/";
  $match = '';

  if (preg_match($regex_pattern, $video, $match)) {
    $video_id = $match[4];
  } else {
    $video_id = '';
  }

  // if video, add modal attributes
  if( $video != null ) {
    $modal_attr = 'data-toggle="modal" data-target="#video-modal" data-youtube-id="' . $video_id . '"';
  } else {
    $modal_attr = '';
  }

  $tile =
  '<div class="tile tile--image-' . $image_position_class . ' tile--' . $color_class . ' tile--' . $col_size . $fixed_height_class . '">
    <a href="' . $button_link . '" ' . $modal_attr . ' class="tile__image-container video-modal-trigger' . $bg_size . '" style="background-image: url(' . $primary_image . ');">';
  if( $video ) {
    $tile .= '<i class="tile__video-icon" aria-hidden="true" style="background-image:url(/wp-content/themes/randysworldwide/assets/images/Play-CTA.svg)"></i>';
  }
  $tile .=
    '</a>
    <div class="tile__content-container row flex-items-sm-middle">';
  if ( $product_image != null ) {
    $tile .=
      '<div class="tile__product-image-wrapper tile__product-image-wrapper--' . $product_color . '">
        <img src="' . $product_image . '" alt="" width="100" class="tile__product-image img-fluid"/>
      </div>';
  }
  $tile .=
      '<div class="' . $body_column_size . '">
        <h3 class="tile__title">' . $title . '</h3>
        <div class="tile__description ellipsis">' . $description . '</div>';
  if( $button_link != null && $image_position === 'left' ) {
    $tile .=
        '<a href="' . $button_link . '" class="button button--sm button--slim button--sm-height button--ghost tile__button">' . $button_label . '</a>';
  } elseif( $button_link != null && $image_position === 'top' ) {
    $tile .=
        '<a href="' . $button_link . '" class="button button--sm button--slim button--sm-height button--ghost tile__button hidden-md-up">' . $button_label . '</a>';
  }
  $tile .=
      '</div>';
  if( $button_link != null && $image_position === 'top' ) {
    $tile .=
      '<div class="col-xs-3 text-sm-center hidden-sm-down">
        <a href="' . $button_link . '" class="button button--sm button--sm-height button--slim button--ghost">' . $button_label . '</a>
      </div>';
  }
  $tile .=
    '</div>
  </div>
  ';

  // If video, add bootstrap modal
  if( $video != null ) {
    $tile .=
    '<div class="modal fade" id="video-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></div>
        <div class="modal-content">
          <div class="modal-body">
            <div class="embed-container">
              <div id="player"></div>
            </div>
          </div>
        </div>
      </div>
    </div>';
  }

  return $tile;
}
