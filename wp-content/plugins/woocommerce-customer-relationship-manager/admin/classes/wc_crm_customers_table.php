<?php
/**
 * Table with list of customers.
 *
 * @author   Actuality Extensions
 * @package  WooCommerce_Customer_Relationship_Manager
 * @since    1.0
 */

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
require_once( 'wc_crm_customer_details.php' );

require_once( plugin_dir_path( __FILE__ ) . '../../functions.php' );

class WC_Crm_Customers_Table extends WP_List_Table {

	var $data = array();
	public $pending_count = array();

	function __construct() {
		parent::__construct( array(
			'singular' => __( 'customer', 'wc_customer_relationship_manager' ), //singular name of the listed records
			'plural' => __( 'customers', 'wc_customer_relationship_manager' ), //plural name of the listed records
			'ajax' => false //does this table support ajax?
			//'screen'   => isset( $args['screen'] ) ? $args['screen'] : null,
		) );
		add_action( 'admin_head', array(&$this, 'admin_header') );
	}

	function admin_header() {
		global $wc_customer_relationship_manager;
		$page = ( isset( $_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
		if ( $wc_customer_relationship_manager->id != $page )
			return;
		echo '<style type="text/css">';
		if ( woocommerce_crm_mailchimp_enabled() ) {
			echo '.wp-list-table .column-id {}';
			echo '.wp-list-table .column-customer_status { width: 45px;}';
			echo '.wp-list-table .column-customer_name { width: 15%;}';
			echo '.wp-list-table .column-email { width: 15%;}';
			echo '.wp-list-table .column-phone { width: 85px;}';
			echo '.wp-list-table .column-user { width: 10%;}';
			echo '.wp-list-table .column-last_purchase { width: 110px;}';
			echo '.wp-list-table .column-num_orders { width: 48px;}';
			echo '.wp-list-table .column-order_value { width: 10%;}';
			echo '.wp-list-table .column-enrolled { width: 47px;}';
			echo '.wp-list-table .column-customer_notes { width: 48px;}';
			echo '.wp-list-table .column-crm_actions { width: 120px;}';
		} else {
			echo '.wp-list-table .column-id {}';
			echo '.wp-list-table .column-customer_status { width: 45px;}';
			echo '.wp-list-table .column-customer_name { width: 15%;}';
			echo '.wp-list-table .column-email { width: 15%;}';
			echo '.wp-list-table .column-phone { width: 85px;}';
			echo '.wp-list-table .column-user { width: 10%;}';
			echo '.wp-list-table .column-last_purchase { width: 110px;}';
			echo '.wp-list-table .column-num_orders { width: 48px;}';
			echo '.wp-list-table .column-order_value { width: 10%;}';
			echo '.wp-list-table .column-customer_notes { width: 48px;}';
			echo '.wp-list-table .column-crm_actions { width: 120px;}';
		}
		echo '</style>';
	}

	function no_items() {
		_e( 'No customers data found. Try to adjust the filter.', 'wc_customer_relationship_manager' );
	}

	function pagination( $which ) {
		if ( empty( $this->_pagination_args ) ) {
			return;
		}

		$total_items = $this->_pagination_args['total_items'];
		$total_pages = $this->_pagination_args['total_pages'];
		$infinite_scroll = false;
		if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
			$infinite_scroll = $this->_pagination_args['infinite_scroll'];
		}

		$output = '<span class="displaying-num">' . sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

		$current = $this->get_pagenum();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

		$current_url = remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first' ), $current_url );

		$page_links = array();

		$disable_first = $disable_last = '';
		if ( $current == 1 ) {
			$disable_first = ' disabled';
		}
		if ( $current == $total_pages ) {
			$disable_last = ' disabled';
		}
		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'first-page' . $disable_first,
			esc_attr__( 'Go to the first page' ),
			esc_url( remove_query_arg( 'paged', $current_url ) ),
			'&laquo;'
		);

		if(isset($_POST) && !empty($_POST)){
			foreach ($_POST as $key => $value) {
				if(empty($value)) continue;
				switch ($key) {
					case '_user_type':
					case '_customer_date_from':
					case '_customer_state':
					case '_customer_city':
					case '_customer_country':
					case '_customer_user':
					case '_customer_product':
					case '_products_variations':
					case '_order_status':
					case '_products_categories':
					case '_products_brands':
					case '_customer_status':
						$current_url = add_query_arg( $key, $value, $current_url );
						break;
				}
			}
		}

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'prev-page' . $disable_first,
			esc_attr__( 'Go to the previous page' ),
			esc_url( add_query_arg( 'paged', max( 1, $current-1 ), $current_url ) ),
			'&lsaquo;'
		);

		if ( 'bottom' == $which ) {
			$html_current_page = $current;
		} else {
			$html_current_page = sprintf( "%s<input class='current-page' id='current-page-selector' title='%s' type='text' name='paged' value='%s' size='%d' />",
				'<label for="current-page-selector" class="screen-reader-text">' . __( 'Select Page' ) . '</label>',
				esc_attr__( 'Current page' ),
				$current,
				strlen( $total_pages )
			);
		}
		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		$page_links[] = '<span class="paging-input">' . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . '</span>';

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'next-page' . $disable_last,
			esc_attr__( 'Go to the next page' ),
			esc_url( add_query_arg( 'paged', min( $total_pages, $current+1 ), $current_url ) ),
			'&rsaquo;'
		);

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'last-page' . $disable_last,
			esc_attr__( 'Go to the last page' ),
			esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
			'&raquo;'
		);

		$pagination_links_class = 'pagination-links';
		if ( ! empty( $infinite_scroll ) ) {
			$pagination_links_class = ' hide-if-js';
		}
		$output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

		if ( $total_pages ) {
			$page_class = $total_pages < 2 ? ' one-page' : '';
		} else {
			$page_class = ' no-pages';
		}
		$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

		echo $this->_pagination;
	}

	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'customer_status':
			case 'customer_name':
			case 'email':
			case 'phone':
			case 'user':
			case 'last_purchase':
			case 'num_orders':
			case 'order_value':
			case 'customer_notes':
			case 'enrolled':
			case 'crm_actions':
				return $item[$column_name];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'last_purchase' => array('last_purchase', true),
			'num_orders' => array('num_orders', true),
			'order_value' => array('order_value', true),
		);
		if ( woocommerce_crm_mailchimp_enabled() ) {
			$sortable_columns['enrolled'] = array('enrolled', true);
		};
		return $sortable_columns;
	}

	function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'customer_status' => '<span class="status_head tips" data-tip="' . esc_attr__( 'Customer Status', 'wc_customer_relationship_manager' ) . '">' . esc_attr__( 'Customer Status', 'wc_customer_relationship_manager' ) . '</span>',
			'customer_name' => __( 'Customer Name', 'wc_customer_relationship_manager' ),
			'email' => __( 'Email', 'wc_customer_relationship_manager' ),
			'phone' => __( 'Phone', 'wc_customer_relationship_manager' ),
			'user' => __( 'Username', 'wc_customer_relationship_manager' ),
			'last_purchase' => __( 'Last Order', 'wc_customer_relationship_manager' ),
			'num_orders' => '<span class="ico_orders tips" data-tip="' . esc_attr__( 'Number of Orders', 'wc_customer_relationship_manager' ) . '">' . esc_attr__( 'Number of Orders', 'wc_customer_relationship_manager' ) . '</span>',
			'customer_notes' => '<span class="ico_notes tips" data-tip="' . esc_attr__( 'Customer Notes', 'wc_customer_relationship_manager' ) . '">' . esc_attr__( 'Customer Notes', 'wc_customer_relationship_manager' ) . '</span>',
			'order_value' => '<span class="ico_value tips" data-tip="' . esc_attr__( 'Total Value', 'wc_customer_relationship_manager' ) . '">' . esc_attr__( 'Total Value', 'wc_customer_relationship_manager' ) . '</span>',
		);
		if ( woocommerce_crm_mailchimp_enabled() ) {
			$columns['enrolled'] = '<span class="ico_news tips" data-tip="' . esc_attr__( 'Newsletter Subscription', 'wc_customer_relationship_manager' ) . '">'.esc_attr__( 'Newsletter Subscription', 'wc_customer_relationship_manager' ).'</span>';
		};
		$columns['crm_actions'] = __( 'Actions', 'wc_customer_relationship_manager' );
		return $columns;
	}

	function usort_reorder( $a, $b ) {
		// If no sort, default to last purchase
		$orderby = ( !empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'last_purchase';
		// If no order, default to desc
		$order = ( !empty( $_GET['order'] ) ) ? $_GET['order'] : 'desc';
		// Determine sort order
		if ( $orderby == 'order_value' ) {
			$result = $a[$orderby] - $b[$orderby];
		} else {
			$result = strcmp( $a[$orderby], $b[$orderby] );
		}
		// Send final sort direction to usort
		return ( $order === 'asc' ) ? $result : -$result;
	}


	function get_bulk_actions() {
		global $wc_customer_relationship_manager;
		$actions = array(
			'email' => __( 'Send Email', 'wc_customer_relationship_manager' ),
			'export_csv' => __( 'Export Contacts', 'wc_customer_relationship_manager' ),
		);
		$statuses = $wc_customer_relationship_manager->statuses;
		foreach ($statuses as $key => $value) {
			$actions[$key] = sprintf( __( 'Mark as %s', 'wc_customer_relationship_manager' ), $value );
		}
		return $actions;
	}

	  function prepare_items() {
		global $role, $usersearch, $wpdb, $wc_customer_relationship_manager;

		$inner          = '';
		$per_page       = 'customers_per_page';
		$users_per_page = $this->get_items_per_page( $per_page );
		$limit          = $users_per_page;
		$filter         = $wc_customer_relationship_manager->customer_filter();
		$paged          = $this->get_pagenum();
		$offset         = ($paged-1) * $users_per_page;
		$orderby        = 'last_order_date';
    $order          = 'DESC';

		/*****/
		#add_filter( 'woocommerce_shop_order_search_fields', array($this, 'woocommerce_crm_search_by_email') );
		if( (isset($_REQUEST['_customer_product']) && !empty( $_REQUEST['_customer_product'] ) ) 
			|| (isset($_REQUEST['_products_variations']) && !empty( $_REQUEST['_products_variations'] ))
			|| (isset($_REQUEST['_order_status']) && !empty( $_REQUEST['_order_status'] ))
			|| (isset($_REQUEST['_products_categories']) && !empty( $_REQUEST['_products_categories'] ))
			|| (isset($_REQUEST['_products_brands']) && !empty( $_REQUEST['_products_brands'] ))
			){
			$inner .= "
			inner join {$wpdb->postmeta} on ({$wpdb->postmeta}.meta_value = {$wpdb->prefix}wc_crm_customers.email AND {$wpdb->postmeta}.meta_key = '_billing_email' AND {$wpdb->prefix}wc_crm_customers.email != '' )
			";
		}
		if( (isset($_REQUEST['_customer_product']) && !empty( $_REQUEST['_customer_product'] )) 
			|| (isset($_REQUEST['_products_variations']) && !empty( $_REQUEST['_products_variations'] ))
			|| (isset($_REQUEST['_products_categories']) && !empty( $_REQUEST['_products_categories'] ))
			|| (isset($_REQUEST['_products_brands']) && !empty( $_REQUEST['_products_brands'] ))
			){
			$inner .= "
			inner join {$wpdb->prefix}woocommerce_order_items on {$wpdb->prefix}woocommerce_order_items.order_id = {$wpdb->postmeta}.post_id
			";
		}
		if( (isset($_REQUEST['_customer_product']) && !empty( $_REQUEST['_customer_product'] )) 
			|| (isset($_REQUEST['_products_categories']) && !empty( $_REQUEST['_products_categories'] ))
			|| (isset($_REQUEST['_products_brands']) && !empty( $_REQUEST['_products_brands'] ))
			){
			$inner .= "			
			inner join 	{$wpdb->prefix}woocommerce_order_itemmeta as product on ( product.order_item_id = {$wpdb->prefix}woocommerce_order_items.order_item_id and product.meta_key = '_product_id' ) ";
		}

		if((isset($_REQUEST['_products_categories']) && !empty( $_REQUEST['_products_categories'] ))
			|| (isset($_REQUEST['_products_brands']) && !empty( $_REQUEST['_products_brands'] ))
			){
			$tax = '';
			if(isset($_REQUEST['_products_categories'])) $tax .= "taxonomy.taxonomy = 'product_cat'";
			if(isset($_REQUEST['_products_brands'])){
				if(!empty($tax))
					$tax .= ' OR ';
			 	$tax .= "taxonomy.taxonomy = 'product_brand'";
			}
			$inner .= "
					inner join 	{$wpdb->prefix}term_relationships as relationships on (relationships.object_id = 	product.meta_value ) 
					inner join 	{$wpdb->prefix}term_taxonomy as taxonomy on (relationships.term_taxonomy_id = taxonomy.term_taxonomy_id AND ($tax) ) 
					";            
		}

		if( isset($_REQUEST['_customer_product']) && !empty( $_REQUEST['_customer_product'] ) ){
			$filter.= " AND product.meta_value = " . $_REQUEST['_customer_product'];
    }

    if( isset($_REQUEST['_products_variations']) && !empty( $_REQUEST['_products_variations'] ) ){
			$y = '';
			foreach ($_REQUEST['_products_variations'] as $v){
				if ($y){
					$ff .= ' OR ';
				}
				else{
					$y = 'OR';
					$ff = 'AND (';
				}
				$ff .= 'variation.meta_value = ' . $v;
			}
			$filter .= $ff . ')';
			$inner .= "
				inner join 	{$wpdb->prefix}woocommerce_order_itemmeta as variation on (variation.order_item_id = 	{$wpdb->prefix}woocommerce_order_items.order_item_id and variation.meta_key = '_variation_id' ) 
				";
  	}
      

    /**************/
    if( isset($_REQUEST['_order_status']) && !empty( $_REQUEST['_order_status'] ) ){
    	$request = $_REQUEST['_order_status'];
      $inner .= "
              inner JOIN {$wpdb->posts} posts
              ON ({$wpdb->postmeta}.post_id= posts.ID AND posts.post_status = '{$request}'  AND posts.post_type =  'shop_order' )
      ";    
    }
    if( isset($_REQUEST['_products_categories']) && !empty( $_REQUEST['_products_categories'] ) ){
        $y = '';
				foreach ($_REQUEST['_products_categories'] as $v){
					if ($y){
						$ff .= ' OR ';
					}
					else{
						$y = 'OR';
						$ff = '
						AND (';
					}
					$ff .= " (taxonomy.term_id = " . $v . " AND taxonomy.taxonomy = 'product_cat' )";
				}
				$filter .= $ff . ')';
				
    }
    if( isset($_REQUEST['_products_brands']) && !empty( $_REQUEST['_products_brands'] ) ){
    	$y = '';
				foreach ($_REQUEST['_products_brands'] as $v){
					if ($y){
						$ff .= ' OR ';
					}
					else{
						$y = 'OR';
						$ff = '
						AND (';
					}
					$ff .= " (taxonomy.term_id = " . $v . " AND taxonomy.taxonomy = 'product_brand' )";
				}
				$filter .= $ff . ')';
    }
    /**************/
      
      if(isset($_GET['orderby']) && !empty($_GET['orderby'])){
      	if ($_GET['orderby'] == 'last_purchase') $orderby = 'last_order_date';
      	elseif($_GET['orderby'] == 'order_value') $orderby = 'total_spent';
      	else $orderby = $_GET['orderby'];
      }
      if(isset($_GET['order']) && !empty($_GET['order']))
      	$order = $_GET['order'];

			$sql = "SELECT * 
	      FROM {$wpdb->prefix}wc_crm_customers
				$inner
				WHERE 1=1
				$filter
				group by email
	      ORDER BY $orderby $order	        
				" ;	

				#echo '<textarea style="width: 100%; height: 100%; ">'.$sql.'</textarea>'; die;

	 	$ss     = $wpdb->get_results( $sql);
	 	$count_ = $wpdb->num_rows;
	 	$this->set_pagination_args( array(
			'total_items' => $count_,
			'per_page' => $users_per_page,
		) );

 		$results = $wpdb->get_results( $sql . "LIMIT $limit OFFSET  $offset");	
		$this->items = $results;
	}

	/**
	 * Generate the list table rows.
	 *
	 * @since 3.1.0
	 * @access public
	 */
	function display_rows() {
		$style = '';
		foreach ( $this->items as $user_object ) {

			$style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
			$members = array();
			if ( woocommerce_crm_mailchimp_enabled() ) {
				$members = woocommerce_crm_get_members();
			}
			echo "\n\t" . $this->single_row( $user_object, $style, $members );
		}
	}

	/**
	 * Generate HTML for a single row on the users.php admin panel.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @param object $user_object The current user object.
	 * @param string $style       Optional. Style attributes added to the <tr> element.
	 *                            Must be sanitized. Default empty.
	 * @param string $role        Optional. Key for the $wp_roles array. Default empty.
	 * @param int    $numposts    Optional. Post count to display for this user. Defaults
	 *                            to zero, as in, a new user has made zero posts.
	 * @return string Output for a single row.
	 */
	function single_row( $user_object, $style = '', $members) {

		$checkbox = '';
			$actions = array();

			$email = $user_object->email;

			if ( woocommerce_crm_mailchimp_enabled() ) {
				$enrolled = in_array( $email, $members ) ? "<span class='enrolled-yes'></span>" : "<span class='enrolled-no'></span>";
			}
			if($user_object->user_id){
				$checkbox = '<label class="screen-reader-text" for="cb-select-' . $user_object->user_id . '">' . sprintf( __( 'Select %s' ), $user_object->nicename ) . '</label>'
						. "<input type='checkbox' name='user_id[]' id='user_{$user_object->user_id}' value='{$user_object->user_id}' />";

				$_status = $user_object->status;
				if($_status){
					$customer_status = '<div style="position: relative;"><span class="'.$_status.' tips" data-tip="' . esc_attr( $_status ) . '"></span></div>';
				}
				else{
					$customer_status = '<div style="position: relative;"><span class="Customer tips" data-tip="Customer"></span></div>';
				}

				$customer_name = "<strong><a href='admin.php?page=wc_new_customer&user_id=" . $user_object->user_id . "'>" . $user_object->first_name . ' ' . $user_object->last_name . "<a></strong>";

				$billing_phone = $user_object->phone;

				$login = '<a href="admin.php?page=wc_new_customer&user_id=' . $user_object->user_id . '">' . $user_object->nicename . '</a>';


				if($user_object->order_id){
					$last_purchase = woocommerce_crm_get_pretty_time( $user_object->order_id );
				}else{
					$last_purchase = '';
				}		

				$num_orders = $user_object->num_orders;
				$order_value = wc_price( $user_object->total_spent );

				$wc_crm_customer_details = new WC_Crm_Customer_Details($user_object->user_id);
				$notes = $wc_crm_customer_details->get_last_customer_note();
				if($notes == 'No Customer Notes')
					$customer_notes = '<span class="note-off">-</span>';
				else
				  $customer_notes = '<a href="admin.php?page=wc_new_customer&screen=customer_notes&user_id='.$user_object->user_id.'" class="fancybox note-on tips" data-tip="'.$notes.'"></a>';

				if ($last_purchase != '' ){
					$actions['orders'] = array(
						'classes' => 'view',
						'url' => sprintf( 'edit.php?s=%s&post_status=%s&post_type=%s&shop_order_status&_customer_user&paged=1&mode=list&search_by_email_only', urlencode( $email ), 'all', 'shop_order' ),
						'action' => 'view',
						'name' => __( 'View Orders', 'wc_customer_relationship_manager' ),
						'target' => ''
					);					
				}
				$actions['email'] = array(
					'classes' => 'email',
					'url' => sprintf( '?page=%s&action=%s&user_id=%s', $_REQUEST['page'], 'email', $user_object->user_id ),
					'name' => __( 'Send Email', 'wc_customer_relationship_manager' ),
					'target' => ''
				);
				if ($billing_phone){
					$actions['phone'] = array(
						'classes' => 'phone',
						'url' => sprintf( '?page=%s&action=%s&user_id=%s', $_REQUEST['page'], 'phone_call', $user_object->user_id ),
						'name' => __( 'Call Customer', 'wc_customer_relationship_manager' ),
						'target' => ''
					);
				}
				$actions['activity'] = array(
					'classes' => 'activity',
					'url' => sprintf( '?page=%s&user_id=%s', 'wc_crm_logs', $user_object->user_id  ),
					'name' => __( 'Contact Activity', 'wc_customer_relationship_manager' ),
					'target' => ''
				);

			}elseif ($user_object->order_id) {
				$checkbox = "<input type='checkbox' name='order_id[]' id='user_order_id{$user_object->order_id}' value='{$user_object->order_id}' />";
				$customer_status = '';

				$customer_name = "<strong><a href='admin.php?page=wc_new_customer&order_id=" . $user_object->order_id . "'>" . $user_object->first_name . ' ' . $user_object->last_name . "<a></strong>";

				$billing_phone = $user_object->phone;

				$login = __( 'Guest', 'wc_customer_relationship_manager' );

				$last_purchase = woocommerce_crm_get_pretty_time( $user_object->order_id );

				$customer_notes = '<span class="note-off">-</span>';

				$num_orders = $user_object->num_orders;
				$order_value = wc_price( $user_object->total_spent );
				

				$actions['orders'] = array(
					'classes' => 'view',
					'url' => sprintf( 'edit.php?s=%s&post_status=%s&post_type=%s&shop_order_status&_customer_user&paged=1&mode=list&search_by_email_only', urlencode( $email ), 'all', 'shop_order' ),
					'action' => 'view',
					'name' => __( 'View Orders', 'wc_customer_relationship_manager' ),
					'target' => ''
				);
				$actions['email'] = array(
					'classes' => 'email',
					'url' => sprintf( '?page=%s&action=%s&order_id=%s', $_REQUEST['page'], 'email', $user_object->order_id ),
					'name' => __( 'Send Email', 'wc_customer_relationship_manager' ),
					'target' => ''
				);
				if ($billing_phone){
					$actions['phone'] = array(
						'classes' => 'phone',
						'url' => sprintf( '?page=%s&action=%s&order_id=%s', $_REQUEST['page'], 'phone_call', $user_object->order_id ),
						'name' => __( 'Call Customer', 'wc_customer_relationship_manager' ),
						'target' => ''
					);
				}
				$actions['activity'] = array(
					'classes' => 'activity',
					'url' => sprintf( '?page=%s&order_id=%s', 'wc_crm_logs', $user_object->order_id  ),
					'name' => __( 'Contact Activity', 'wc_customer_relationship_manager' ),
					'target' => ''
				);
			}

			$crm_actions =  '';
			if(!empty($actions)){
				foreach ( $actions as $action ) {
					$crm_actions .= '<a class="button tips '.esc_attr($action['classes']).'" href="'.esc_url( $action['url'] ).'" data-tip="'.esc_attr( $action['name'] ).'" '.esc_attr( $action['target'] ).' >'.esc_attr( $action['name'] ).'</a>';
				}
			}

		
		

		$r = "<tr id='user-$user_object->user_id'$style>";

		list( $columns, $hidden ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$class = "class=\"$column_name column-$column_name\"";

			$style = '';
			if ( in_array( $column_name, $hidden ) )
				$style = ' style="display:none;"';

			$attributes = "$class$style";

			switch ( $column_name ) {
				case 'cb':
					$r .= "<th scope='row' class='check-column'>$checkbox</th>";
					break;
				case 'customer_status':
					$r .= "<td $attributes>$customer_status</td>";
					break;
				case 'customer_name':
					$r .= "<td $attributes>$customer_name</td>";
					break;
				case 'email':
					$r .= "<td $attributes><a href='mailto:$email' title='" . esc_attr( sprintf( __( 'E-mail: %s' ), $email ) ) . "'>$email</a></td>";
					break;
				case 'phone':
					$r .= "<td $attributes>".$billing_phone."</td>";
					break;
				case 'user':
					$r .= "<td $attributes>$login</td>";
					break;
				case 'last_purchase':
					$r .= "<td $attributes>$last_purchase</td>";
					break;
				case 'num_orders':
					$r .= "<td $attributes>$num_orders</td>";
					break;
				case 'customer_notes':
					$r .= "<td $attributes>$customer_notes</td>";
					break;
				case 'order_value':
					$r .= "<td $attributes>$order_value</td>";
					break;
				case 'crm_actions':
					$r .= "<td $attributes> <p>$crm_actions</p> </td>";
					break;
				case 'enrolled':
						$r .= "<td $attributes> <p>$enrolled</p> </td>";
					break;
				default:
					$r .= "<td $attributes></td>";

					/**
					 * Filter the display output of custom columns in the Users list table.
					 *
					 * @since 2.8.0
					 *
					 * @param string $output      Custom column output. Default empty.
					 * @param string $column_name Column name.
					 * @param int    $user_id     ID of the currently-listed user.
					 */
					$r .= apply_filters( 'wc_pos_customer_custom_column', '', $column_name, $user_object->user_id );
					$r .= "</td>";
			}
		}
		$r .= '</tr>';

		return $r;
	}


	function extra_tablenav( $which ) {
		if ( $which == 'top' ) {
			do_action( 'wc_crm_restrict_list_customers' );
		}
	}
}