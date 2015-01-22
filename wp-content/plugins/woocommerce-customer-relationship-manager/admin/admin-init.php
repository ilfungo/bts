<?php
/**
 * Admin init logic
 *
 * @author   Actuality Extensions
 * @package  WooCommerce_Customer_Relationship_Manager
 * @since    1.0
 */


if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once( 'customers.php' );
require_once( 'groups.php' );
require_once( 'logs.php' );
require_once( 'customer_details.php' );
require_once( 'settings.php' );

add_action( 'admin_menu', 'wc_customer_relationship_manager_add_menu' );
add_action( 'admin_print_footer_scripts', 'wc_customer_relationship_manager_highlight_menu_item');

/**
 * Add the menu item
 */
function wc_customer_relationship_manager_add_menu() {
	global $wc_customer_relationship_manager;

	$hook = add_menu_page(
		__( 'Customers', 'wc_customer_relationship_manager' ), // page title
		__( 'Customers', 'wc_customer_relationship_manager' ), // menu title
		'manage_woocommerce', // capability
		$wc_customer_relationship_manager->id, // unique menu slug
		'wc_customer_relationship_manager_render_list_page',
		null,
		56
	);

	$new_customer_hook = add_submenu_page( $wc_customer_relationship_manager->id, ( (isset($_GET['user_id']) && !empty($_GET['user_id'])) ? __( "Customer Profile", 'wc_customer_relationship_manager' ) : __( "Add New Customer", 'wc_customer_relationship_manager' ) ) , '<span id="wc_crm_add_new_customer">'.__( "Add New", 'wc_customer_relationship_manager').'</span>', 'manage_woocommerce', 'wc_new_customer', 'wc_customer_relationship_manager_render_new_customer_page' );


	$logs_hook = add_submenu_page($wc_customer_relationship_manager->id, __( "Activity", 'wc_customer_relationship_manager' ), __( "Activity", 'wc_customer_relationship_manager'), 'manage_woocommerce', 'wc_crm_logs', 'wc_customer_relationship_manager_render_logs_page' );

 	$groups_hook = add_submenu_page( $wc_customer_relationship_manager->id, __( "Groups", 'wc_customer_relationship_manager' ), __( "Groups", 'wc_customer_relationship_manager'), 'manage_woocommerce', 'wc_user_grps', 'wc_customer_relationship_manager_render_groups_list_page' );

	add_submenu_page( $wc_customer_relationship_manager->id, __( "Settings", 'wc_customer_relationship_manager' ), __( "Settings", 'wc_customer_relationship_manager'), 'manage_woocommerce', 'wc_crm_settings', 'wc_customer_relationship_manager_render_settings_page' );

	add_action( "load-$hook", 'wc_customer_relationship_manager_add_options' );
	add_action( "load-$logs_hook", 'wc_customer_relationship_manager_logs_add_options' );
	add_action( "load-$new_customer_hook", 'wc_customer_relationship_manager_new_customer_add_options' );
	add_action( "load-$groups_hook", 'wc_customer_relationship_manager_groups_add_options' );
}

function wc_customer_relationship_manager_highlight_menu_item(){
	global $wc_point_of_sale;
   if( isset($_GET['page']) && $_GET['page'] == 'wc_new_customer' && ( isset($_GET['user_id']) || isset($_GET['order_id']) ) ){

    ?>
        <script type="text/javascript">
            jQuery(document).ready( function($) {
                jQuery('#wc_crm_add_new_customer').parent().removeClass('current').parent().removeClass('current').prev().addClass('current').children().addClass('current');
            });
        </script>
    <?php
    }
}
