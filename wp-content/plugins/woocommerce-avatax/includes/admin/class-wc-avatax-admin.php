<?php
/**
 * Define the WC_AvaTax_Admin class
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce AvaTax to newer
 * versions in the future. If you wish to customize WooCommerce AvaTax for your
 * needs please refer to http://docs.woocommerce.com/document/woocommerce-avatax/
 *
 * @package   AvaTax\Admin
 * @author    SkyVerge
 * @copyright Copyright (c) 2016-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Set up the AvaTax admin.
 *
 * @since 1.0.0
 */
class WC_AvaTax_Admin {


	/** @var WC_AvaTax_Settings $settings The settings class */
	public $settings;


	/**
	 * Construct the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->includes();

		// Load the admin scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );

		// Display and save the product tax code field
		add_action( 'woocommerce_product_options_tax', array( $this, 'display_product_tax_code_field' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_tax_code' ), 10, 2 );

		// Display the product tax code quick edit field
		add_action( 'manage_product_posts_custom_column', array( $this, 'add_product_quick_edit_inline_values' ), 10 );
		add_action( 'woocommerce_product_quick_edit_end',  array( $this, 'display_product_tax_code_quick_edit' ) );

		// Display and save the product tax code bulk edit field
		add_action( 'woocommerce_product_bulk_edit_end', array( $this, 'display_product_tax_code_bulk_edit' ) );
		add_action( 'woocommerce_product_bulk_edit_save', array( $this, 'save_product_tax_code_bulk_edit' ) );

		// Add the product category tax code field
		add_action( 'product_cat_add_form_fields', array( $this, 'add_category_tax_code_field' ) );
		add_action( 'product_cat_edit_form_fields', array( $this, 'edit_category_tax_code_field' ) );

		// Save the product category tax code field
		// The same is done when creating a new category from WC_AvaTax_AJAX::save_category_tax_code_field
		add_action( 'edit_product_cat', array( $this, 'save_category_tax_code_field' ) );

		// Add product category tax code column
		add_filter( 'manage_edit-product_cat_columns', array( $this, 'add_category_tax_code_column' ) );
		add_filter( 'manage_product_cat_custom_column', array( $this, 'display_category_tax_code_column' ), 10, 3 );

		// Add the VAT ID information to the order billing information
		add_action( 'woocommerce_admin_billing_fields', array( $this, 'add_admin_order_vat_id' ) );

		// Hide our custom line item meta from the order admin
		add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'hide_order_item_meta' ) );

		// Add the item tax rate input to the order admin
		add_action( 'woocommerce_admin_order_item_values', array( $this, 'add_order_item_tax_rate' ), 10, 3 );

		// Add a "Send to Avalara" action to the order action options if calculation is enabled
		if ( wc_avatax()->calculate_taxes() ) {
			add_action( 'woocommerce_order_actions', array( $this, 'add_order_action' ) );
		}

		// Add and save the customer tax settings fields
		add_action( 'show_user_profile',        array( $this, 'add_tax_meta_fields' ), 15, 1 );
		add_action( 'edit_user_profile',        array( $this, 'add_tax_meta_fields' ), 15, 1 );
		add_action( 'personal_options_update',  array( $this, 'save_tax_meta_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_tax_meta_fields' ) );
	}


	/**
	 * Include the admin files.
	 *
	 * @since 1.0.0
	 */
	public function includes() {

		// The settings class
		require_once( wc_avatax()->get_plugin_path() . '/includes/admin/class-wc-avatax-settings.php' );
		$this->settings = new WC_AvaTax_Settings;
	}


	/**
	 * Load the admin scripts and styles.
	 *
	 * @since 1.0.0
	 * @param string $hook_suffix The current screen suffix
	 */
	public function enqueue_scripts_styles( $hook_suffix ) {

		// Only enqueue the scripts and styles on the settings screen or edit/new order screens
		if ( wc_avatax()->is_plugin_settings() || ( 'product' === get_post_type() && 'edit.php' === $hook_suffix ) || ( 'shop_order' === get_post_type() && ( 'post.php' === $hook_suffix || 'post-new.php' === $hook_suffix ) ) ) {

			wp_enqueue_script( 'wc-avatax-admin', wc_avatax()->get_plugin_url() . '/assets/js/admin/wc-avatax-admin.min.js', array( 'jquery' ), WC_AvaTax::VERSION, true );

			wp_localize_script( 'wc-avatax-admin', 'wc_avatax_admin', array(
				'address_nonce' => wp_create_nonce( 'wc_avatax_validate_origin_address' ),
				'assets_url'    => esc_url( wc_avatax()->get_framework_assets_url() ),
				'ajax_url'      => admin_url( 'admin-ajax.php' ),
			) );

			wp_enqueue_style( 'wc-avatax-admin', wc_avatax()->get_plugin_url() . '/assets/css/admin/wc-avatax-admin.min.css', WC_AvaTax::VERSION );
		}
	}


	/**
	 * Display tax code field.
	 *
	 * @since 1.0
	 */
	public function display_product_tax_code_field() {

		woocommerce_wp_text_input(
			array(
				'id'          => '_wc_avatax_code',
				'class'       => 'hide_if_external',
				'label'       => __( 'Tax Code', 'woocommerce-avatax' ),
				'placeholder' => get_option( 'wc_avatax_default_product_code' ),
			)
		);
	}


	/**
	 * Save the tax code field
	 *
	 * @param int $post_id The product ID
	 * @since 1.0.0
	 */
	public function save_product_tax_code( $post_id ) {

		update_post_meta( $post_id, '_wc_avatax_code', sanitize_text_field( $_POST['_wc_avatax_code'] ) );
	}


	/**
	 * Add markup for the custom product meta values so Quick Edit can fill the inputs.
	 *
	 * @since 1.1.0
	 * @param string $column the current column slug
	 */
	public function add_product_quick_edit_inline_values( $column ) {

		global $the_product;

		if ( 'name' === $column ) {

			echo '<div id="wc_avatax_inline_' . (int) $the_product->get_id() . '" class="hidden">';
				echo '<div class="tax_code">' . esc_html( SV_WC_Product_Compatibility::get_meta( $the_product, '_wc_avatax_code' ) ) . '</div>';
			echo '</div>';
		}
	}


	/**
	 * Display the product tax code quick edit field.
	 *
	 * @since 1.0.0
	 */
	public function display_product_tax_code_quick_edit() {

		include( wc_avatax()->get_plugin_path() . '/includes/admin/views/html-field-product-tax-code-quick-edit.php' );
	}


	/**
	 * Display the product tax code bulk edit field.
	 *
	 * @since 1.0.0
	 */
	public function display_product_tax_code_bulk_edit() {

		include( wc_avatax()->get_plugin_path() . '/includes/admin/views/html-field-product-tax-code-bulk-edit.php' );
	}


	/**
	 * Save the product tax code bulk edit field.
	 *
	 * @since 1.0.0
	 */
	public function save_product_tax_code_bulk_edit( $product ) {

		if ( ! empty( $_REQUEST['change_wc_avatax_code'] ) ) {

			$new_code     = sanitize_text_field( $_REQUEST['_wc_avatax_code'] );
			$current_code = SV_WC_Product_Compatibility::get_meta( $product, '_wc_avatax_code' );

			// Update to new tax code if different than current tax code
			if ( isset( $new_code ) && $new_code !== $current_code ) {
				update_post_meta( $product->get_id(), '_wc_avatax_code', $new_code );
			}
		}
	}


	/**
	 * Display the tax code field on the add product category screen.
	 *
	 * @since 1.0.0
	 */
	public function add_category_tax_code_field() {

		include( wc_avatax()->get_plugin_path() . '/includes/admin/views/html-field-add-category-tax-code.php' );
	}


	/**
	 * Display the tax code field on the edit product category screen.
	 *
	 * @since 1.0.0
	 * @param object $term The term
	 */
	public function edit_category_tax_code_field( $term ) {

		$tax_code = get_woocommerce_term_meta( $term->term_id, 'wc_avatax_tax_code', true );

		include( wc_avatax()->get_plugin_path() . '/includes/admin/views/html-field-edit-category-tax-code.php' );
	}


	/**
	 * Save the category tax code field.
	 *
	 * @since 1.0.0
	 * @param int $term_id The term ID.
	 */
	public function save_category_tax_code_field( $term_id ) {

		$tax_code = sanitize_text_field( SV_WC_Helper::get_post( 'wc_avatax_category_tax_code' ) );

		update_woocommerce_term_meta( $term_id, 'wc_avatax_tax_code', $tax_code );
	}


	/**
	 * Add the tax code column to category admin.
	 *
	 * @since 1.0.0
	 * @param array $columns The existing category columns.
	 * @return array $columns
	 */
	public function add_category_tax_code_column( $columns ) {

		$columns['tax_code'] = __( 'Tax Code', 'woocommerce-avatax' );

		return $columns;
	}


	/**
	 * Display the tax code in its column.
	 *
	 * @since 1.0.0
	 * @param string $columns The column content.
	 * @param string $column The current column slug.
	 * @param int $id The category ID.
	 * @return string $columns The amended column content.
	 */
	public function display_category_tax_code_column( $columns, $column, $id ) {

		if ( 'tax_code' == $column ) {
			$columns .= get_woocommerce_term_meta( $id, 'wc_avatax_tax_code', true );
		}

		return $columns;
	}


	/**
	 * Add the VAT ID information to the order billing information.
	 *
	 * @since 1.0.0
	 * @param array $fields The existing billing fields
	 * @return array
	 */
	public function add_admin_order_vat_id( $fields ) {

		$fields['wc_avatax_vat_id'] = array(
			'label' => __( 'VAT ID', 'woocommerce-avatax' ),
		);

		return $fields;
	}


	/**
	 * Hide our custom line item meta from the order admin.
	 *
	 * @since 1.0.0
	 * @param array $hidden_meta The hidden line item keys.
	 * @return array $hidden_meta
	 */
	public function hide_order_item_meta( $hidden_meta ) {

		$hidden_meta[] = '_wc_avatax_code';
		$hidden_meta[] = '_wc_avatax_rate';

		return $hidden_meta;
	}


	/**
	 * Add the item tax rate input to the order admin.
	 *
	 * @since 1.0.0
	 * @param WC_Product $product The product object.
	 * @param array $item The item meta.
	 * @param int $item_id The item ID.
	 */
	public function add_order_item_tax_rate( $product, $item, $item_id ) {

		// Only add this value if a tax rate was set for the item
		if ( ( ! is_array( $item ) && ! $item instanceof WC_Order_Item_Tax ) || empty( $item['wc_avatax_rate'] ) ) {
			return;
		}

		echo '<input
				class="wc_avatax_refund_line_rate"
				name="wc_avatax_refund_line_rate[' . absint( $item_id ) . ']"
				value="' . (float) $item['wc_avatax_rate'] . '"
				type="hidden"
			/>';
	}


	/**
	 * Add a "Send to Avalara" action to the order action options.
	 *
	 * @since 1.0.0
	 * @global WC_Order $theorder The current order object.
	 * @param array $actions The available order actions.
	 * @return array $actions
	 */
	public function add_order_action( $actions ) {
		global $theorder;

		// Only add the action if the order is ready for sending
		if ( wc_avatax()->get_order_handler()->is_order_ready( $theorder ) ) {
			$actions['wc_avatax_send'] = __( 'Send to Avalara', 'woocommerce-avatax' );
		}

		return $actions;
	}


	/**
	 * Add the customer tax settings fields.
	 *
	 * @since 1.0.0
	 * @param WP_User $user The user object.
	 */
	public function add_tax_meta_fields( $user ) {

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		// Usage codes and their descriptions
		// Note: `M` and `O` are intentionally absent
		$usage_types = array(
			'A' => __( 'Federal government', 'woocommerce-avatax' ),
			'B' => __( 'State government', 'woocommerce-avatax' ),
			'C' => __( 'Tribe / Status Indian / Indian Band', 'woocommerce-avatax' ),
			'D' => __( 'Foreign diplomat', 'woocommerce-avatax' ),
			'E' => __( 'Charitable or benevolent organization', 'woocommerce-avatax' ),
			'F' => __( 'Religious or educational organization', 'woocommerce-avatax' ),
			'G' => __( 'Resale', 'woocommerce-avatax' ),
			'H' => __( 'Commercial agricultural production', 'woocommerce-avatax' ),
			'I' => __( 'Industrial production / manufacturer', 'woocommerce-avatax' ),
			'J' => __( 'Direct pay permit', 'woocommerce-avatax' ),
			'K' => __( 'Direct mail', 'woocommerce-avatax' ),
			'L' => __( 'Other', 'woocommerce-avatax' ),
			'N' => __( 'Local government', 'woocommerce-avatax' ),
			'P' => __( 'Commercial aquaculture', 'woocommerce-avatax' ),
			'Q' => __( 'Commercial Fishery', 'woocommerce-avatax' ),
			'R' => __( 'Non-resident', 'woocommerce-avatax' ),
			'MED1' => __( 'US MDET with exempt sales tax', 'woocommerce-avatax' ),
			'MED2' => __( 'US MDET with taxable sales tax', 'woocommerce-avatax' ),
		);

		/**
		 * Filter the customer usage types.
		 *
		 * @since 1.0.0
		 * @param array $usage_types The usage types formatted as $code => $description
		 */
		$usage_types = apply_filters( 'wc_avatax_customer_usage_types', $usage_types );

		$selected_type = get_user_meta( $user->ID, 'wc_avatax_tax_exemption', true );

		include( wc_avatax()->get_plugin_path() . '/includes/admin/views/html-edit-user-tax-fields.php' );
	}


	/**
	 * Save the customer tax settings.
	 *
	 * @since 1.0.0
	 * @param int $user_id The user ID.
	 */
	public function save_tax_meta_fields( $user_id ) {

		// Save the tax exemption code
		update_user_meta( $user_id, 'wc_avatax_tax_exemption', wc_clean( $_POST['wc_avatax_user_exemption'] ) );
	}
}
