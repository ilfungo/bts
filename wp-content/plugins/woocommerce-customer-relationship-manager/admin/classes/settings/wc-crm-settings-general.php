<?php
/**
 * WooCommerce General Settings
 *
 * @author 		WooThemes
 * @category 	Admin
 * @package 	WooCommerce/Admin
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Crm_Settings_General' ) ) :

/**
 * WC_Crm_Settings_General
 */
class WC_Crm_Settings_General extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'general_crm';
		$this->label = __( 'General', 'woocommerce' );

		add_filter( 'wc_crm_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'wc_crm_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'wc_crm_settings_save_' . $this->id, array( $this, 'save' ) );

	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {
		global $woocommerce;
		$filters = array(
					'name' => __( 'Filters', 'wc_customer_relationship_manager' ),
					'desc' => 'Choose which filters you would like to display on the Customers page.',
					'id' => 'woocommerce_crm_filters',
					'css' => '',
					'std' => '',
					'type' => 'multiselect',
					'options' => array(
							'user_roles' => __( 'User Roles', 'wc_customer_relationship_manager' ),
							'last_order' => __( 'Last Order', 'wc_customer_relationship_manager' ),
							'state' => __( 'State', 'wc_customer_relationship_manager' ),
							'city' => __( 'City', 'wc_customer_relationship_manager' ),
							'country' => __( 'Country', 'wc_customer_relationship_manager' ),
							'customer_name' => __( 'Customer Name', 'wc_customer_relationship_manager' ),
							'products' => __( 'Products', 'wc_customer_relationship_manager' ),
							'products_variations' => __( 'Products Variations', 'wc_customer_relationship_manager' ),
              'order_status' => __( 'Order Status', 'wc_customer_relationship_manager' ),
							'customer_status' => __( 'Customer Status', 'wc_customer_relationship_manager' ),
							'products_categories' => __( 'Product Categories', 'wc_customer_relationship_manager' ),
						),
					'defa'
				);
		if( class_exists( 'WC_Brands_Admin' ) ) {
			$filters['options']['products_brands'] = __( 'Product Brands', 'wc_customer_relationship_manager' );
		}
		return apply_filters( 'woocommerce_customer_relationship_general_settings_fields', array(

			array( 'title' => __( 'General Options', 'woocommerce' ), 'type' => 'title', 'desc' => '', 'id' => 'general_crm_options' ),

			$filters,

			array(
					'name'    => __( 'Number of Orders', 'wc_customer_relationship_manager' ),
					'desc'    => 'Choose which statuses the customer orders must be before appearing in the Number of Orders column.',
					'id'      => 'woocommerce_crm_number_of_orders',
					'css'     => '',
					'std'     => '',
					'type'    => 'multiselect',
					'options' => wc_get_order_statuses(),
				),

			array(
					'name'    => __( 'Total Value', 'wc_customer_relationship_manager' ),
					'desc'    => 'Choose which statuses the customer orders must be before appearing in the Total Value column.',
					'id'      => 'woocommerce_crm_total_value',
					'css'     => '',
					'std'     => '',
					'type'    => 'multiselect',
					'options' => wc_get_order_statuses(),
				),

			array( 'type' => 'sectionend', 'id' => 'general_crm_options'),

		) ); // End general settings

	}

	/**
	 * Save settings
	 */
	public function save() {
		$settings = $this->get_settings();

		update_option( "wc_crm_db_updated", 'no' );

		WC_Crm_Settings::save_fields( $settings );
	}

}

endif;

return new WC_Crm_Settings_General();
