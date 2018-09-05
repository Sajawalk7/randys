<?php
/**
 * WooCommerce Address Validation
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Address Validation to newer
 * versions in the future. If you wish to customize WooCommerce Address Validation for your
 * needs please refer to http://docs.woocommerce.com/document/address-validation/ for more information.
 *
 * @package     WC-Address-Validation/Provider
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Address Validation Validator class
 *
 * Extended by address providers to handle address/postcode validation and lookup
 *
 * @since 1.0
 */
abstract class WC_Address_Validation_Provider extends WC_Settings_API {

	/** @var string unique prefix for saving settings */
	public $plugin_id = 'wc_address_validation_';

	/** @var string unique ID for the validator, required */
	public $id;

	/** @var string title used on settings page */
	protected $title;

	/** @var string description used on settings page */
	protected $description;

	/** @var array array of countries this validator is valid for */
	protected $countries = array();

	/** @var array features this validator supports (e.g. post code lookup) */
	protected $supports = array();


	/**
	 * Return the provider's title
	 *
	 * @since 1.0
	 * @return string the provider title
	 */
	public function get_title() {

		$title = empty( $this->title ) ? ucwords( str_replace( array( '_', '-' ), '', $this->id ) ) : $this->title;

		/**
		 * Filter the address provider title
		 *
		 * @since 2.0.0
		 * @param string $title
		 * @param \WC_Address_Validation_Provider $this
		 */
		return apply_filters( 'wc_address_validation_provider_title', $title, $this );
	}


	/**
	 * Return the description for admin screens
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		/**
		 * Filter the address provider description
		 *
		 * @since 2.0.0
		 * @param string $description
		 * @param \WC_Address_Validation_Provider $this
		 */
		return apply_filters( 'wc_address_validation_provider_description', $this->description, $this );
	}


	/**
	 * Return an array of supported features
	 *
	 * Since 2.0.0 returns an array of non-formatted
	 * features. To get formatted feature labels, use
	 * get_supported_features_formatted() instead.
	 *
	 * @since 1.0
	 * @return array supported features
	 */
	public function get_supported_features() {
		return $this->supports;
	}


	/**
	 * Return an array of formatted supported features
	 *
	 * @since 2.0.0
	 * @return array supported features, formatted
	 */
	public function get_supported_features_formatted() {
		return array_map( array( $this, 'get_feature_label' ), $this->supports );
	}


	/**
	 * Get feature label
	 *
	 * @since 2.0.0
	 * @param string $feature
	 * @return string localized feature label
	 */
	public function get_feature_label( $feature ) {

		$labels = array(
			'address_validation'     => __( 'Address Validation', 'woocommerce-address-validation' ),
			'address_classification' => __( 'Address Classification', 'woocommerce-address-validation' ),
			'geocoding'              => __( 'Geocoding', 'woocommerce-address-validation' ),
			'postcode_lookup'        => __( 'Postcode Lookup', 'woocommerce-address-validation' ),
		);

		$label = ! empty( $labels[ $feature ] )	? $labels[ $feature ] : ucwords( str_replace( '_', ' ', $feature ) );

		/**
		 * Filter the feature label
		 *
		 * Allows third parties to supply their own labels to any custom features
		 *
		 * @since 2.0.0
		 * @param string $label
		 * @param string $feature
		 */
		return apply_filters( 'wc_address_validation_feature_label', $label, $feature );
	}


	/**
	 * Return an array of supported countries
	 *
	 * @since 1.2.0
	 * @return array supported countries
	 */
	public function get_supported_countries() {

		return $this->countries;
	}


	/**
	 * Check if provider is configured correctly, overridden by child providers
	 *
	 * @since 1.0
	 * @return bool true if configured, false otherwise
	 */
	public function is_configured() {

		return true;
	}


	/**
	 * Postcode lookup function stub to be overridden by child providers
	 *
	 * This method only needs to be overridden for providers that support postcode lookup
	 *
	 * @since 1.0
	 * @param string $postcode
	 * @param string $house_number Optional. Used by Postcode.nl.
	 */
	public function lookup_postcode( $postcode, $house_number = '' ) { }


	/**
	 * Check if a provider supports a given feature
	 *
	 * Options = 'postcode_lookup', 'address_validation', 'geocoding', 'address_classification'
	 *
	 * @since 1.0
	 * @param string $feature the name of a feature to test support for
	 * @return bool true if the provider supports the feature, false otherwise
	 */
	public function supports( $feature ) {

		return apply_filters( 'wc_address_validation_provider_supports', in_array( $feature, $this->supports ) ? true : false, $feature, $this );
	}


	/**
	 * Show the title / description and settings for provider
	 *
	 * @since 1.0
	 */
	public function admin_options() {

		echo '<h2>' . esc_html( $this->get_title() ) . '</h2>';

		if ( $this->id !== wc_address_validation()->get_handler_instance()->get_active_provider()->id ) {

			/* translators: %1$s - provider name, %2$s - opening <a> tag, %3$s - closing </a> tag */
			$message = __( '%1$s is not selected as your active provider. Please change your active provider under the %2$sGeneral Options%3$s and save your settings to enable %1$s.', 'woocommerce-address-validation' );
			$message = sprintf( $message, $this->get_title(), '<a href="' . wc_address_validation()->get_settings_url() . '">', '</a>' );

			printf( '<div class="notice notice-warning below-h2"><p>%s</p></div>', $message );
		}

		echo wp_kses_post( wpautop( $this->get_description() ) );

		parent::admin_options();

		// Display supported features
		?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $this->id . '_supported_features' ); ?>">
						<?php _e( 'Supported Features', 'woocommerce-address-validation' ); ?>
					</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text">
							<span><?php _e( 'Supported Features', 'woocommerce-address-validation' ); ?></span>
						</legend>
						<p><?php echo esc_html( implode( ', ', $this->get_supported_features_formatted() ) ); ?></p>
					</fieldset>
				</td>
			</tr>
		</table>
		<?php
	}


}
