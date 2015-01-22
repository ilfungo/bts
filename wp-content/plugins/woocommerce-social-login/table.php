<?php
if (!defined('ABSPATH')) exit('No direct script access allowed');

 /**
 * CodeNegar WooCommerce Social Login User Table
 *
 * Generates List of Users
 *
 * @package    	WooCommerce Social Login
 * @author      Farhad Ahmadi <ahm.farhad@gmail.com>
 * @license     http://codecanyon.net/licenses
 * @link		http://codenegar.com/woocommerce-social-login/
 * @version    	1.0
 */
 
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
 
class Wcsl_users extends WP_List_Table{

	function __construct() {
		parent::__construct( array(
		'singular'=> 'wp_list_wcsl_user', //Singular label
		'plural' => 'wp_list_wcsl_users', //plural label, also this well be one of the table css class
		'ajax'  => false //We don't support Ajax for this table
		) );
	}

	/**
	 * Define the columns that are going to be used in the table
	 * @return array $columns, the array of columns to use with the table
	 */
	function get_columns() {
		global $codenegar_wcsl;
		return $columns= array(
			'col_provider'=>__('Provider', $codenegar_wcsl->text_domain),
			'col_displayName'=>__('Name', $codenegar_wcsl->text_domain),
			'col_profileURL'=>__('Profile URL', $codenegar_wcsl->text_domain),
			'col_wp_user_id'=>__('WP User', $codenegar_wcsl->text_domain),
			'col_ip'=>__('IP', $codenegar_wcsl->text_domain),
			'col_details'=>__('Full Details', $codenegar_wcsl->text_domain),
		);
	}

	/**
	 * Prepare the table with different parameters, pagination, columns and table elements
	*/
	
	function prepare_items() {
		global $wpdb, $_wp_column_headers;
		$screen = get_current_screen();

		$wcsl_users = $wpdb->prefix . "wcsl_users";
		$query = "SELECT * FROM $wcsl_users";

		/* -- Ordering parameters -- */
		//Parameters that are going to be used to order the result
		$orderby = !empty($_GET["orderby"]) ? mysql_real_escape_string($_GET["orderby"]) : 'ASC';
		$order = !empty($_GET["order"]) ? mysql_real_escape_string($_GET["order"]) : '';
		if(!empty($orderby) & !empty($order)){
		$query.=' ORDER BY '.$orderby.' '.$order;
		}else{
			$query.=' ORDER BY ID DESC'; // show newest first
		}

		/* -- Pagination parameters -- */
		//Number of elements in your table?
		$totalitems = $wpdb->query($query); //return the total number of affected rows
		
		$perpage = 10; //How many to display per page?
		
		$paged = !empty($_GET["paged"]) ? mysql_real_escape_string($_GET["paged"]) : ''; //Which page is this?
		
		if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; } //Page Number
		
		$totalpages = ceil($totalitems/$perpage); //How many pages do we have in total?
		
		if(!empty($paged) && !empty($perpage)){ //adjust the query to take pagination into account
				$offset=($paged-1)*$perpage;
		$query.=' LIMIT '.(int)$offset.','.(int)$perpage;
		}

		/* -- Register the pagination -- */
		$this->set_pagination_args( array(
				"total_items" => $totalitems,
				"total_pages" => $totalpages,
				"per_page" => $perpage,
		) );
		//The pagination links are automatically built according to those parameters
		
		/* — Register the Columns — */
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		/* -- Fetch the items -- */
		$this->items = $wpdb->get_results($query);
	}

	/**
	 * Display the rows of records in the table
	 * @return string, echo the markup of the rows
	 */
	function display_rows() {
		global $codenegar_wcsl;
		//Get the records registered in the prepare_items method
		$records = $this->items;

		//Get the columns registered in the get_columns and get_sortable_columns methods
		list( $columns, $hidden ) = $this->get_column_info();

		//Loop for each record
		if(!empty($records)){foreach($records as $rec){

		echo '<tr id="record_'.$rec->ID.'">';
			foreach ( $columns as $column_name => $column_display_name ) {
				//Style attributes for each col
				$class = "class='$column_name column-$column_name'";
				$style = "";
				if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
				$attributes = $class . $style;

				if(empty($rec->profileURL)) $rec->profileURL = "#";
				
				switch ( $column_name ) {
					case "col_provider":   echo '<td '.$attributes.'>'.ucfirst(stripslashes($rec->provider)).'</td>'; break;
					case "col_displayName":   echo '<td '.$attributes.'>'.stripslashes($rec->displayName).'</td>'; break;
					case "col_profileURL": echo '<td '.$attributes.'><a href="'. $rec->profileURL .'" >#</a></td>'; break;
					case "col_wp_user_id": echo '<td '.$attributes.'><a href=user-edit.php?user_id='. $rec->wp_user_id .' >'. $rec->wp_user_id .'</a></td>'; break;
					case "col_ip":   echo '<td '.$attributes.'>'.stripslashes($rec->ip).'</td>'; break;
					case "col_details":   echo '<td '.$attributes.'><a href="admin.php?page=woocommerce_settings&tab=social_login&section=users_list&details_view=1&user_id='.stripslashes($rec->ID).'">'.__('Click to View', $codenegar_wcsl->text_domain).'</a></td>'; break;
				}
			}
			echo'</tr>';
		}}
	}
}
?>