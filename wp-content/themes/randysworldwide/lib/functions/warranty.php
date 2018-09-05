<?php
/**
 *
 * Displays a warranty section
 *
 * @param string type          displays a different theme for different warranties
 * @param string title         title of section
 * @param string description   short description in section
 * @param string link          URL to more info
 *
 */

function get_warranty($type, $title, $description, $link) {
  $logo_url = '';
  if ( $type === 'yukon' ) {
    $logo_url = '/wp-content/themes/randysworldwide/dist/images/yukon_logo_white.svg';
  } elseif ( $type === 'usa_standard' ) {
    $logo_url = '/wp-content/themes/randysworldwide/dist/images/usa_standard_logo_white.svg';
  }

  $bg_color = '';
  if ( $type === 'yukon' ) {
    $bg_color = 'section--purple';
  } elseif ( $type === 'usa_standard' ) {
    $bg_color = 'section--rust';
  }

  $return = '<div class="section ' . $bg_color . ' section--texture-bg">
    <div class="container">
      <div class="row">
        <div class="col-md-3">
          <div class="center-align">
            <img src="' . $logo_url . '" class="warranty-item__image" alt="' . $type . '" />
          </div>
        </div>
        <div class="col-md-9">
          <div class="warranty-item">
            <h3 class="warranty-item__label m-t-1">Warranties</h3>
            <h2 class="warranty-item__title m-b-2">' . $title . '</h2>
            <p class="warranty-item__description">' . $description . '</p>
            <div class="m-t-2">
              <a href="' . $link . '" class="warranty-item__button button">Learn More</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>';

  return $return;
}
