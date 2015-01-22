<?php
/**
 * Logic related to displaying CRM page.
 *
 * @author   Actuality Extensions
 * @package  WooCommerce_Customer_Relationship_Manager
 * @since    1.0
 */

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once( 'classes/wc_crm_customers_table.php' );
require_once( 'classes/wc_crm_email_handling.php' );
require_once( 'classes/wc_crm_phone_call.php' );


add_action('init', 'wc_customer_relationship_manager_customers_actions');

function wc_customer_relationship_manager_customers_actions()
{
	global $wc_customer_relationship_manager;
	$action = '';
	$statuses = $wc_customer_relationship_manager->statuses;
	if(isset($_REQUEST['action']) && !empty($_REQUEST['action']) && $_REQUEST['action'] != '-1'){
		$action = $_REQUEST['action'];
	}elseif(isset($_REQUEST['action2']) && !empty($_REQUEST['action2']) && $_REQUEST['action2'] != '-1'){
		$action = $_REQUEST['action2'];
	}
	if(!empty($action) && array_key_exists($action, $statuses) ){
		wc_crm_change_customer_status($action);
	}

	if ( isset( $_POST['send'] ) && isset( $_POST['recipients'] ) && isset( $_POST['emaileditor'] ) && isset( $_POST['subject'] ) ) {
		WC_Crm_Email_Handling::process_form();
	}else	if ( isset( $_POST['save_phone_call'] ) ) {
		WC_Crm_Phone_Call::process_form();
	}
}

function wc_customer_relationship_manager_add_options() {
	global $wc_crm_customers_table;

	$option = 'per_page';
	$args = array(
		'label' => __( 'Customers', 'wc_customer_relationship_manager' ),
		'default' => 20,
		'option' => 'customers_per_page'
	);
	add_screen_option( $option, $args );
	$wc_crm_customers_table = new WC_Crm_Customers_Table();
}

add_filter('set-screen-option', 'wc_customer_relationship_manager_set_options', 10, 3);
function wc_customer_relationship_manager_set_options($status, $option, $value) {
    if ( 'customers_per_page' == $option ) return $value;
    return $status;
}

/**
 * Gets template for emailing customers.
 *
 * @param $template_name
 * @param array $args
 */
function wc_crm_custom_woocommerce_get_template( $template_name, $args = array() ) {

	if ( $args && is_array( $args ) )
		extract( $args );

	$located = dirname( __FILE__ ) . '/templates/' . $template_name;

	do_action( 'woocommerce_before_template_part', $template_name, '', $located, $args );

	include( $located );

	do_action( 'woocommerce_after_template_part', $template_name, '', $located, $args );

}

/**
 * Renders CRM page.
 */
function wc_customer_relationship_manager_render_list_page() {
	global $wc_crm_customers_table, $wc_customer_relationship_manager;
	echo '<div class="wrap" id="wc-crm-page"><div class="icon32"><img src="' . plugins_url( 'assets/img/customers-icons.png', dirname( __FILE__ ) ) . '" width="29" height="29" /></div>';
	wc_crm_page_title($_REQUEST);
	wc_crm_page_messages($_REQUEST);
	if(isset($_GET['message']) && $_GET['message'] == 1){
		echo '<div id="message" class="updated fade"><p>Customer added.</p></div>';
	}
	?>
	<form method="post" id="wc_crm_customers_form" action="admin.php?page=wc-customer-relationship-manager">
		<input type="hidden" name="page" value="wc-customer-relationship-manager">
		<?php	

		if ( isset( $_GET['order_list'] ) ) {
			require_once( 'classes/wc_crm_order_list.php');
			$wc_crm_order_list = new WC_Crm_Order_List();
			$wc_crm_order_list->prepare_items();
			$wc_crm_order_list->display();
		}elseif ( isset( $_GET['product_list'] ) ) {
			require_once( 'classes/wc_crm_product_list.php');
			$wc_crm_product_list = new WC_Crm_Product_List();
			$wc_crm_product_list->prepare_items();
			$wc_crm_product_list->display();
		}
		else if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'email' && !isset( $_REQUEST['send'] )) {
				WC_Crm_Email_Handling::display_form();
		}
		else if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'phone_call' && !isset( $_POST['save_phone_call'] ) ) {
			WC_Crm_Phone_Call::display_form();
		} else {
			?>
			<p class="search-box">
			<?php
				$ss ='';
				if ( !empty( $_POST['s'] ) ){
					echo '<a href="?page=wc-customer-relationship-manager" style="float: left; padding-right: 15px ; ">Reset</a>';
					$ss =$_POST['s'];
				}
			?>
				<label for="post-search-input" class="screen-reader-text">Search</label>
				<input type="search" value="<?php echo $ss; ?>" name="s" id="post-search-input">
				<input type="submit" value="Search Customers" class="button" id="search-submit" name="">
			</p>
			<?php
			$wc_crm_customers_table->prepare_items();
			$wc_crm_customers_table->display();
		}
		?>
	</form></div>
<?php
}
function wc_crm_page_title($request){
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'wc_user_grps' ) {
		$group_name = '';
		if ( isset( $_GET['id'] ) && !empty($_GET['id']) ) {
			global $wpdb;
			$table_name = $wpdb->prefix . "wc_crm_groups";
			$id = $_GET['id'];
			$db_data = $wpdb->get_results("SELECT * FROM $table_name WHERE ID = $id");
			$group_name = ' <i>"'.$db_data[0]->group_name.'"</i>';
		}
		echo '<h2>' . __( 'Group', 'wc_customer_relationship_manager' ) . $group_name .'</h2>';
	}elseif ( isset( $request['order_list'] ) ) {
		echo '<h2>' . __( 'Orders', 'wc_customer_relationship_manager' ) . '</h2>';
	}elseif ( isset( $request['product_list'] ) ) {
		echo '<h2>' . __( 'Products ', 'wc_customer_relationship_manager' ) . '</h2>';
	}
	else if ( isset( $request['action'] ) && $request['action'] == 'email'  && !isset( $request['send'] ) ) {}
	else if ( isset( $request['action'] ) && $request['action'] == 'phone_call' && !isset( $_POST['save_phone_call'] )) {}
	else {
			echo '<h2>' . __( 'Customers', 'wc_customer_relationship_manager' ) . ' <a href="'.admin_url().'admin.php?page=wc_new_customer" class="add-new-h2">Add Customer</a></h2>';
	}
}
function wc_crm_page_messages($request){
	$messages = array();
	if ( isset($request['update']) ) {

			switch ( $request['update'] ) {
				case "sent_email":
					$messages[] =  __( 'Your email has been successfully sent.', 'wc_customer_relationship_manager' );
					break;
				case "save_phone_call":
					$messages[] =  __( 'Phone Call has been saved.', 'wc_customer_relationship_manager' );
					break;
			}
	}
	if ( isset($request['action']) ) {
			switch ( $request['action'] ) {
				case "Favourite":
				case "Lead":
				case "Follow-Up":
				case "Prospect":
				case "Favourite":
				case "Blocked":
				case "Blocked":
				case "Flagged":
					$messages[] =  __( 'Customer status updated.', 'wc_customer_relationship_manager' );
					break;
			}
	}
	if ( ! empty( $messages ) ) {
		foreach ( $messages as $msg )
			echo '<div id="message" class="updated fade"><p>' . $msg . '</p></div>';
	}
}

function wc_crm_change_customer_status($action = '')
	{
		
		global $wc_customer_relationship_manager;
		if (empty($action)) return;
		if (!isset($_POST['user_id']) && empty($_POST['user_id'])) return;

			foreach ($_POST['user_id'] as $value) {
				$user_id = $value;				
				$status = '';
				
				$statuses = $wc_customer_relationship_manager->statuses;
				if(array_key_exists($action, $statuses) ){
					update_user_meta( $user_id, 'customer_status', $statuses[$action] );
	        $status = $action;
				}
				global $wpdb;
				 $sql = "UPDATE {$wpdb->prefix}wc_crm_customers
				 					SET status = '$status'
              		WHERE user_id = $user_id
              
      	";
      	$wpdb->query($sql);
			}

	}
