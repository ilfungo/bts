<?php
/**
 * Plugin Name: WooCommerce Customer Relationship Manager
 * Plugin URI: http://actualityextensions.com/
 * Description: Allows for better overview of WooCommerce customers, communication with customers, listing amount spent by customers for certain period and more!
 * Version: 2.4.2
 * Author: Actuality Extensions
 * Author URI: http://actualityextensions.com/
 * Tested up to: 3.7.1
 *
 * Copyright: (c) 2012-2013 Actuality Extensions
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package     WC-Customer-Relationship-Manager
 * @author      Actuality Extensions
 * @category    Plugin
 * @copyright   Copyright (c) 2012-2013, Actuality Extensions
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return; // Check if WooCommerce is active

require_once( plugin_dir_path( __FILE__ ) . 'functions.php' );

if ( !class_exists( 'MCAPI_Wc_Crm' ) ) {
	require_once( 'admin/classes/api/MCAPI.class.php' );
}
if ( !class_exists( 'WooCommerce_Customer_Relationship_Manager' ) ) {

	class WooCommerce_Customer_Relationship_Manager {


		public function __construct() {
      global $wc_crm_db_version, $statuses;
      $wc_crm_db_version = "3.2";
      

      
      define('WC_CRM_FILE', __FILE__);
      // installation after woocommerce is available and initialized
        if (is_admin() && !defined('DOING_AJAX'))
            add_action('woocommerce_init', array($this, 'wc_crm_install'));


			$this->current_tab = ( isset( $_GET['tab'] ) ) ? $_GET['tab'] : 'general';

			// settings tab
			$this->settings_tabs = array();
      $statuses = $this->statuses = array(
                        'Customer' => 'Customer',
                        'Lead' => 'Lead',
                        'Follow-Up' => 'Follow-Up',
                        'Prospect' => 'Prospect',
                        'Favourite' => 'Favourite',
                        'Blocked' => 'Blocked',
                        'Flagged' => 'Flagged'
                        );

      register_deactivation_hook( __FILE__, array($this, 'deactivate') );

      add_action( 'admin_enqueue_scripts', array($this, 'enqueue_dependencies_admin') );

      add_action( 'admin_head', array($this, 'view_customer_button') );
			add_action( 'admin_head', array($this, 'view_customer_link') );


			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array($this, 'action_links') );

			// Add the settings fields to each tab.
			add_action( 'woocommerce_customer_relationship_settings', array($this, 'add_settings_fields'), 10 );

			add_action( 'woocommerce_init', array($this, 'includes') );
      add_action( 'wc_crm_restrict_list_customers', array($this, 'woocommerce_crm_restrict_list_customers') );
			add_action( 'wc_crm_restrict_list_logs', array($this, 'woocommerce_crm_restrict_list_logs') );

			add_filter( 'woocommerce_shop_order_search_fields', array($this, 'woocommerce_crm_search_by_email') );
			add_filter( 'views_edit-shop_order', array($this, 'views_shop_order') );
      add_filter( 'woocommerce_checkout_customer_userdata', array($this, 'wc_crm_add_customer_status'));
			add_action( 'admin_post_export_csv', array($this, 'export_csv') );

			/*AJAX EVENTS*/
			

      add_filter('user_contactmethods', array( $this, 'modify_contact_methods') );
      add_action( 'show_user_profile', array( $this, 'add_user_field_status') );
      add_action( 'edit_user_profile', array( $this, 'add_user_field_status') );

      add_action( 'personal_options_update', array( $this, 'save_user_field_status')  );
      add_action( 'edit_user_profile_update', array( $this, 'save_user_field_status')  );

      add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'select_customer_id' ) );
	  
      add_action( 'save_post_shop_order', array( $this, 'update_customer_table') );

      add_action( 'profile_update', array( $this, 'update_customer_table_user'), 10, 2 );
      add_action( 'user_register', array( $this, 'update_customer_table_user'), 10, 2 );
      add_action( 'delete_user', array( $this, 'delete_customer'), 10, 2 );

		}
    function delete_customer($user_id){
      $user_obj = get_userdata( $user_id );
      $email = $user_obj->user_email;
      if(!$email) return;
      global $wpdb;
      $sql = "DELETE FROM {$wpdb->prefix}wc_crm_customers
              WHERE email = '$email'
      ";
      $wpdb->query($sql);

      $sql2 = "UPDATE {$wpdb->postmeta} postmeta
                SET postmeta.meta_value = 0
                WHERE postmeta.meta_value = $user_id AND meta_key = '_customer_user'
      ";
      $wpdb->query($sql2);

      $sql = $this->get_guest_sql("AND usermeta_email.meta_value = '$email'");
      $result = $wpdb->query($sql);
      $this->delete_empty_guests();
    }
    function view_customer_button(){
      $screen = get_current_screen();
      if($screen->id != 'shop_order' || !isset($_GET['post']) || empty($_GET['post']) ) return;
      $crm_customer_link = get_option( 'woocommerce_crm_customer_link', 'customer' );

      $url = '';
      if($crm_customer_link == 'customer')
        $url = 'admin.php?page=wc_new_customer&order_id='.$_GET['post'];
        

      $user_id = get_post_meta( $_GET['post'], '_customer_user', true ); 
      if($user_id){
        if($crm_customer_link == 'customer')
          $url = get_admin_url().'admin.php?page=wc_new_customer&user_id='.$user_id;
        else
          $url = get_admin_url().'user-edit.php?user_id='.$user_id;
      }

      if(empty($url)) return false;
      ?>
      <script>
      jQuery(document).ready(function($){
        $('h2 .add-new-h2').after('<a class="add-new-h2 add-new-view-customer" href="<?php echo $url; ?>"><?php _e("View Customer", "wc_customer_relationship_manager"); ?></a>');
      });
      </script>
      <style>
        .wrap .add-new-h2.add-new-view-customer, .wrap .add-new-h2.add-new-view-customer:active{
          background: #2ea2cc;
          color:#fff
        }
        .wrap .add-new-h2.add-new-view-customer:hover{
          background: #1e8cbe;
          border-color: #0074a2;
        }
      </style>
      <?php
    }
    function view_customer_link(){
      $screen = get_current_screen();
      
      if($screen->id != 'edit-shop_order' && ( !isset($_GET['page']) || $_GET['page'] != 'wc_new_customer') ) return;
      $crm_customer_link = get_option( 'woocommerce_crm_customer_link', 'customer' );

      $url = '';
      if($crm_customer_link == 'customer'){
        $url = get_admin_url().'admin.php?page=wc_new_customer&user_id=';
        ?>
        <script>
        jQuery(document).ready(function($){

          $('td.column-order_title').each(function(){
            var $a = $(this).find('a').first().nextAll('a');
            if($a.length > 0 ){
              var user_id = $a.attr('href').replace('user-edit.php?user_id=', '');
              if(user_id){
                $a.attr('href', '<?php echo $url; ?>'+user_id);
              }
            }
          });
        });
        </script>
        <?php
      }       
    }

    function delete_empty_guests() {
      global $wpdb;
        $sql = "DELETE FROM {$wpdb->prefix}wc_crm_customers
                WHERE email NOT IN(
                  SELECT posts_email.meta_value
                  FROM {$wpdb->posts}

                  inner JOIN {$wpdb->postmeta} posts_email
                    ON ( posts_email.post_id = {$wpdb->posts}.ID AND posts_email.meta_key = '_billing_email' AND posts_email.meta_value != '' )

                  inner JOIN {$wpdb->postmeta} posts_users
                    ON ( posts_users.post_id = posts_email.post_id AND posts_users.meta_key = '_customer_user')

                  WHERE {$wpdb->posts}.post_type = 'shop_order'
                  AND posts_users.meta_value = 0
                  )
                AND user_id IS NULL
        ";
        #echo $sql; die;
      $wpdb->query($sql);
    }
    function delete_empty_customers() {
      global $wpdb;
        $sql = "DELETE FROM {$wpdb->prefix}wc_crm_customers
                WHERE (
                  (user_id NOT IN (
                    SELECT posts_user.meta_value 
                    FROM {$wpdb->posts}

                    right JOIN {$wpdb->postmeta} posts_user
                      ON ( posts_user.post_id={$wpdb->posts}.ID  AND posts_user.meta_key = '_customer_user' AND posts_user.meta_value > 0)
                    
                    WHERE {$wpdb->posts}.post_type = 'shop_order'
                    )
                  AND user_id IS NOT NULL ) 
                  OR email = '')
                AND capabilities NOT LIKE '%customer%'
        ";
        #echo $sql; die;
      $wpdb->query($sql);
    }
    function update_customer_table_user($user_id){
        $user_info = get_userdata($user_id);
        $email   = $user_info->user_email;
        if(!$email) return;
        global $wpdb;

        $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wc_crm_customers WHERE email = '$email'"); 
        
        if(!empty($results)){          
          $sql = $this->get_user_sql("AND {$wpdb->users}.user_email = '$email'");
          $sql2 = $this->get_cust_sql("AND {$wpdb->users}.user_email = '$email'");
        }else{
          $sql  = $this->get_user_sql("AND {$wpdb->users}.user_email != ''");
          $sql2 = $this->get_cust_sql("AND {$wpdb->users}.user_email != ''");   
        }
        #echo '<textarea style="width: 100%; height: 100%; ">'.$sql2.'</textarea>'; die;
        $wpdb->query($sql); 
        $wpdb->query($sql2); 
        $this->delete_empty_customers();     
    }
  	function update_customer_table($post_id){	
      $user_id = get_post_meta( $post_id, '_customer_user', true );
      if(!$user_id){
  		  $email   = get_post_meta( $post_id, '_billing_email', true );
        if(empty($email)) return false;
        global $wpdb;
        $sql = $this->get_guest_sql("AND usermeta_email.meta_value = '$email'");
        $result = $wpdb->query($sql);
        $status = get_post_status( $post_id );
        
      }
      else{
        $this->update_customer_table_user($user_id);
      }   
      $this->delete_empty_guests();
      $this->delete_empty_customers();
  	}

    

		public function select_customer_id(){
      if( isset($_GET['post_type']) && $_GET['post_type'] == 'shop_order' && isset($_GET['user_id']) && !empty($_GET['user_id']) ){
        $user_id = $_GET['user_id'];

        ob_start(); ?>
          jQuery('#customer_user').append('<option selected="selected" value="<?php echo $user_id; ?>"><?php echo get_the_author_meta( 'user_firstname', $user_id ) ?> <?php echo  get_the_author_meta( 'user_lastname', $user_id )?> (#<?php echo $user_id?> – <?php echo get_the_author_meta( 'user_email', $user_id )?>)</option>');

          <?php
          $customer_details = new WC_Crm_Customer_Details($user_id, 0);
          $customer_details->init_address_fields();
          $__b_address = $customer_details->billing_fields;
          $__s_address = $customer_details->shipping_fields;
          $formatted_shipping_address = wp_kses( $customer_details->get_formatted_shipping_address(), array( "br" => array() ) );
          $formatted_shipping_address = str_replace('<br />', '<br />\\', $formatted_shipping_address);

          $formatted_billing_address = wp_kses( $customer_details->get_formatted_billing_address(), array( "br" => array() ) );
          $formatted_billing_address = str_replace('<br />', '<br />\\', $formatted_billing_address);
            foreach ($__b_address as $key => $field ) { ?>
             jQuery('#_billing_<?php echo $key; ?>').val( "<?php echo get_the_author_meta( 'billing_'.$key, $user_id );?>" );
          <?php
            }
            foreach ($__s_address as $key => $field ) { ?>
             jQuery('#_shipping_<?php echo $key; ?>').val( "<?php echo get_the_author_meta( 'shipping_'.$key, $user_id );?>" );
          <?php
            }
          ?>
            jQuery('.order_data_column_container .order_data_column').last().find('.address')
            .html('<?php echo "<p><strong>" . __( "Address", "woocommerce" ) . ":</strong>" . $formatted_shipping_address . "</p>"; ?>');

            jQuery('.order_data_column_container .order_data_column').first().next().find('.address')
            .html('<?php echo "<p><strong>" . __( "Address", "woocommerce" ) . ":</strong>" . $formatted_billing_address . "</p>"; ?>');
        <?php

        $js_string = ob_get_contents();

        ob_end_clean();
        wc_enqueue_js( $js_string );
      }else if( isset($_GET['post_type']) && $_GET['post_type'] == 'shop_order' && isset($_GET['last_order_id']) && !empty($_GET['last_order_id']) ){
        $last_order_id = $_GET['last_order_id'];

        ob_start();
          $customer_details = new WC_Crm_Customer_Details(0, $last_order_id);
          $customer_details->init_address_fields();
          $__b_address = $customer_details->billing_fields;
          $__s_address = $customer_details->shipping_fields;
          $formatted_shipping_address = wp_kses( $customer_details->get_formatted_shipping_address(), array( "br" => array() ) );
          $formatted_shipping_address = str_replace('<br />', '<br />\\', $formatted_shipping_address);

          $formatted_billing_address = wp_kses( $customer_details->get_formatted_billing_address(), array( "br" => array() ) );
          $formatted_billing_address = str_replace('<br />', '<br />\\', $formatted_billing_address);
            foreach ($__b_address as $key => $field ) {
              $name_var = 'billing_'.$key;
              $field_val = $customer_details->order->$name_var;
              ?>
             jQuery('#_billing_<?php echo $key; ?>').val( "<?php echo $field_val;?>" );
          <?php
            }
            foreach ($__s_address as $key => $field ) {
            $name_var = 'shipping_'.$key;
            $field_val = $customer_details->order->$name_var;
            ?>
            jQuery('#_shipping_<?php echo $key; ?>').val( "<?php echo $field_val;?>" );
          <?php
            }
          ?>
            jQuery('.order_data_column_container .order_data_column').last().find('.address')
            .html('<?php echo "<p><strong>" . __( "Address", "woocommerce" ) . ":</strong>" . $formatted_shipping_address . "</p>"; ?>');

            jQuery('.order_data_column_container .order_data_column').first().next().find('.address')
            .html('<?php echo "<p><strong>" . __( "Address", "woocommerce" ) . ":</strong>" . $formatted_billing_address . "</p>"; ?>');
        <?php

        $js_string = ob_get_contents();

        ob_end_clean();
        wc_enqueue_js( $js_string );
      }
    }


    public function deactivate(){      
      global $wpdb;
      $table = $wpdb->prefix."wc_crm_customers";
      delete_option('wc_crm_db_updated');
      delete_option('wc_crm_db_version');

      $wpdb->query("DROP TABLE IF EXISTS $table");
    }


		public function wc_crm_install() {
			global $wpdb, $wc_crm_db_version;
      $wpdb->hide_errors();
      $installed_ver = get_option( "wc_crm_db_version" );
      $up = false;
      if( $installed_ver != $wc_crm_db_version){
      #if( $_GET['page'] == 'wc-customer-relationship-manager' ){

        $collate = '';
                if ($wpdb->has_cap('collation')) {
                    if (!empty($wpdb->charset))
                        $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
                    if (!empty($wpdb->collate))
                        $collate .= " COLLATE $wpdb->collate";
                }

        // initial install
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        $table_name = $wpdb->prefix . "wc_crm_log";
  			$sql = "CREATE TABLE $table_name (
  							ID bigint(20) NOT NULL AUTO_INCREMENT,
  							subject text NOT NULL,
  							activity_type VARCHAR(50) DEFAULT '' NOT NULL,
  							user_id bigint(20) NOT NULL,
                created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  							created_gmt datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                message text NOT NULL,
                user_email text NOT NULL,
                phone text NOT NULL,
                call_type text NOT NULL,
                call_purpose text NOT NULL,
                related_to text NOT NULL,
                number_order_product text NOT NULL,
                call_duration text NOT NULL,
                log_status text NOT NULL,
  							PRIMARY KEY  (ID)
  			)" . $collate;
  			dbDelta( $sql );

        $table_name = $wpdb->prefix . "wc_crm_groups";
        $sql = "CREATE TABLE $table_name (
                ID bigint(20) NOT NULL AUTO_INCREMENT,
                group_name VARCHAR(200) NOT NULL,
                group_slug text,
                group_type VARCHAR(200) NOT NULL,
                group_total_spent_mark VARCHAR(200) NOT NULL,
                group_total_spent VARCHAR(200) NOT NULL,
                group_user_role VARCHAR(200) NOT NULL,
                group_customer_status VARCHAR(200) NOT NULL,
                group_last_order VARCHAR(200) NOT NULL,
                group_last_order_from VARCHAR(200) NOT NULL,
                group_last_order_to VARCHAR(200) NOT NULL,
                PRIMARY KEY  (ID)
        )" . $collate;
        dbDelta( $sql );
		
  		 $table_name = $wpdb->prefix . "wc_crm_customers";
          $sql = "CREATE TABLE $table_name (
                  email VARCHAR(200) NOT NULL,
                  user_id bigint(20),
                  capabilities VARCHAR(200),
                  first_name VARCHAR(200),
                  last_name VARCHAR(200),
                  nicename VARCHAR(200),
                  status VARCHAR(200),
                  phone VARCHAR(200),
                  city VARCHAR(200),
                  state VARCHAR(200),
                  country VARCHAR(200),
  				        order_id bigint(20),
                  last_order_date DATETIME,
                  num_orders INT( 20 ) DEFAULT 0,
                  total_spent FLOAT(20) DEFAULT 0,
                  brands VARCHAR(200),
                  categories VARCHAR(200),
                  find_orders INT( 20 ) DEFAULT 0,
                  PRIMARY KEY  (email)
          )" . $collate;
          dbDelta( $sql );


        if(get_option( "wc_crm_db_version" )) {
          update_option( "wc_crm_db_version", $wc_crm_db_version );
        }else{
          add_option( "wc_crm_db_version", $wc_crm_db_version );
        }
        $up = true;
      }
      #if(isset($_GET['page']) && $_GET['page'] == 'wc-customer-relationship-manager') $up = true;
      $updated = get_option( "wc_crm_db_updated" );
      if(!$updated || $updated == 'no' || $up){
        #$wpdb->show_errors(); 
        $sql  = $this->get_user_sql("AND {$wpdb->users}.user_email != ''");
        $sql2 = $this->get_cust_sql("AND {$wpdb->users}.user_email != ''");   
        $sql3 = $this->get_guest_sql("AND usermeta_email.meta_value != ''");   

        #echo '<textarea style="width: 100%; height: 100%; ">'.$sql.'</textarea>'; die;
        #echo '<textarea style="width: 100%; height: 100%; ">'.$sql2.'</textarea>'; die;
        #echo '<textarea style="width: 100%; height: 100%; ">'.$sql3.'</textarea>';

        $wpdb->query($sql);
        $wpdb->query($sql2);
        $wpdb->query($sql3);

        $this->delete_empty_guests();
        $this->delete_empty_customers();

        if(get_option( "wc_crm_db_updated" )) {
          update_option( "wc_crm_db_updated", 'yes' );
        }else{
          add_option( "wc_crm_db_updated", 'yes' );
        }
      }
		}


		/**
		 * The plugin's id
		 * @var string
		 */
		var $id = 'wc-customer-relationship-manager';

		/**
		 * Enqueue admin CSS and JS dependencies
		 */
		public function enqueue_dependencies_admin() {
			wp_enqueue_script( array('jquery', 'editor', 'thickbox', 'media-upload') );
			wp_enqueue_style( 'thickbox' );
			wp_register_script( 'textbox_js', plugins_url( 'assets/js/TextboxList.js', __FILE__ ), array('jquery') );
			wp_enqueue_script( 'textbox_js' );
			wp_register_script( 'timer', plugins_url( 'assets/js/jquery.timer.js', __FILE__ ), array('jquery') );
			wp_enqueue_script( 'timer' );
			wp_register_script( 'jquery-ui', plugins_url( 'assets/js/jquery-ui.js', __FILE__ ), array('jquery') );
			wp_enqueue_script( 'jquery-ui' );
			wp_register_script( 'growing_input', plugins_url( 'assets/js/GrowingInput.js', __FILE__ ), array('jquery') );
			wp_enqueue_script( 'growing_input' );
			wp_register_style( 'textbox_css', plugins_url( 'assets/css/TextboxList.css', __FILE__ ) );
			wp_enqueue_style( 'textbox_css' );
			wp_register_style( 'jquery-ui-css', plugins_url( 'assets/css/jquery-ui.css', __FILE__ ) );
			wp_enqueue_style( 'jquery-ui-css' );

      wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css' );

			wp_register_style( 'woocommerce-customer-relationship-style-admin', plugins_url( 'assets/css/admin.css', __FILE__ ), array('textbox_css', 'woocommerce_admin_styles') );
			wp_enqueue_style( 'woocommerce-customer-relationship-style-admin' );
			wp_register_script( 'woocommerce-customer-relationship-script-admin', plugins_url( 'assets/js/admin.js', __FILE__ ), array('jquery', 'textbox_js', 'growing_input') );
			wp_enqueue_script( 'woocommerce-customer-relationship-script-admin' );

			wp_register_script( 'woocommerce_admin_crm', plugins_url() . '/woocommerce/assets/js/admin/woocommerce_admin.min.js', array('jquery', 'jquery-blockui', 'jquery-placeholder', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip') );
			wp_enqueue_script( 'woocommerce_admin_crm' );
			wp_register_script( 'woocommerce_tiptip_js', plugins_url() . '/woocommerce/assets/js/jquery-tiptip/jquery.tipTip.min.js' );
			wp_enqueue_script( 'woocommerce_tiptip_js' );

      wp_register_script( 'ajax-chosen', WC()->plugin_url() . '/assets/js/chosen/ajax-chosen.jquery.js', array('jquery', 'chosen'), WC_VERSION );
      wp_register_script( 'chosen', WC()->plugin_url() . '/assets/js/chosen/chosen.jquery.js', array('jquery'), WC_VERSION );
      wp_enqueue_script( 'ajax-chosen' );
      wp_enqueue_script( 'chosen' );

				wp_register_script( 'mousewheel', plugins_url( 'assets/js/jquery.mousewheel.js', __FILE__ ), array('jquery') );
				wp_enqueue_script( 'mousewheel' );
				wp_register_script( 'fancybox', plugins_url( 'assets/js/jquery.fancybox.pack.js', __FILE__ ), array('jquery', 'mousewheel') );
				wp_enqueue_script( 'fancybox' );

				wp_register_style( 'fancybox_styles', plugins_url('/assets/css/fancybox/jquery_fancybox.css', __FILE__ ) );
				wp_enqueue_style( 'fancybox_styles' );
				wp_register_style( 'fancybox-buttons', plugins_url('/assets/css/fancybox/jquery.fancybox-buttons.css', __FILE__ ) );
				wp_enqueue_style( 'fancybox-buttons' );



        if( isset($_GET['page']) && $_GET['page'] == 'wc_new_customer' ){
            wp_register_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI.min.js', array( 'jquery' ), '2.66', true );
            wp_enqueue_script( 'jquery-blockui' );
            if( (isset($_GET['user_id']) && !empty($_GET['user_id']) )
            || ( isset($_GET['order_id']) && !empty($_GET['order_id']) ) ){
              wp_register_script( 'google_map', 'http://maps.google.com/maps/api/js?sensor=true', array( 'jquery' ), '3.0', true );
              wp_register_script( 'jquery_ui_map', plugins_url( 'assets/js/jquery.ui.map.full.min.js', __FILE__ ), array( 'google_map' ), '3.0', true );
              wp_enqueue_script( 'google_map' );
              wp_enqueue_script( 'jquery_ui_map' );
            }
            $params = array(
              'ajax_loader_url'     => apply_filters( 'woocommerce_ajax_loader_url', WC()->plugin_url() . '/assets/images/ajax-loader@2x.gif' ),              
              'ajax_url'            => admin_url('admin-ajax.php'),
              'wc_crm_loading_states'    => wp_create_nonce("wc_crm_loading_states"),
              'copy_billing'          => __( 'Copy billing information to shipping information? This will remove any currently entered shipping information.', 'woocommerce' ),
            );

            wp_localize_script( 'woocommerce-customer-relationship-script-admin', 'wc_crm_customer_params', $params );

      }
       if( isset($_GET['page']) && $_GET['page'] == 'wc-customer-relationship-manager' ){
         $params_crm = array(
              'curent_time'    => current_time('Y-m-d'),
              'curent_time_h'  => current_time('g'),
              'curent_time_m'  => current_time('i'),
              'curent_time_s'  => current_time('s'),
            );
          wp_localize_script( 'woocommerce-customer-relationship-script-admin', 'wc_crm_params', $params_crm );
      }

			add_thickbox();
		}

		/**
		 * Add action links under WordPress > Plugins
		 *
		 * @param $links
		 * @return array
		 */
		public function action_links( $links ) {

			$plugin_links = array(
				'<a href="' . admin_url( 'admin.php?page=wc_crm_settings' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>',
			);

			return array_merge( $plugin_links, $links );
		}



		/**
		 * Include required files
		 */
		public function includes() {
			if ( is_admin() ) {
				require_once( 'admin/admin-init.php' ); // Admin section
         if (defined('DOING_AJAX')) {
              require_once( 'admin/classes/wc_crm_ajax.php' );
          }
			}
		}

		/**
		 * Handle CSV file download
		 */
		function export_csv() {
      if ( !current_user_can( 'manage_options' ) )
				return;

			header( 'Content-Type: application/csv' );
			header( 'Content-Disposition: attachment; filename=customers_' . date( 'Y-m-d' ) . '.csv' );
			header( 'Pragma: no-cache' );

      $__wc_crm_customer_details = new WC_Crm_Customer_Details(0, 0);
      $__wc_crm_customer_details->init_address_fields('', '', false);
      $__b_address = $__wc_crm_customer_details->billing_fields;
      $__s_address = $__wc_crm_customer_details->shipping_fields;

      $wc_crm_customers_table = new WC_Crm_Customers_Table();
      $wc_crm_customers_table->prepare_items();
      $data = $wc_crm_customers_table->items;

      echo '"Customer name",';
      foreach ($__b_address as $key => $label) {
            if($key=='first_name' || $key=='last_name') continue;
            echo '"Billing ' . $label['label'] . '",';
      }
      foreach ($__s_address as $key => $label) {
        if($key=='first_name' || $key=='last_name') continue;
        echo '"Shipping ' . $label['label'] . '",';
      }
      echo '"Username",';
      echo '"Last purchase date",';
      echo '"Number of orders",';
      echo '"Total value",';
      echo "\"Subscribed\"\n";

      if ( woocommerce_crm_mailchimp_enabled() ) {
        $members = woocommerce_crm_get_members();
      }
      foreach ( $data as $customer ) {
      $item = get_object_vars ( $customer );

      echo '"' . $item['first_name'] . ' '.$item['last_name'].'",';

        if($item['user_id'] ){
          $user_id = $item['user_id'];
          $wc_crm_customer_details = new WC_Crm_Customer_Details($user_id, 0);
          $wc_crm_customer_details->init_address_fields('', '', false);
          $b_address = $wc_crm_customer_details->billing_fields;

          $s_address = $wc_crm_customer_details->shipping_fields;
          foreach ($b_address as $key => $value) {
            if($key=='first_name' || $key=='last_name') continue;
            if($key=='country') {
              echo '"' . $item['country'] . '",';
              continue;
            }
            if($key=='email') {
              echo '"' . $item['email'] . '",';
              continue;
            }
            if($key=='phone') {
              echo '"' . $item['phone'] . '",';
              continue;
            }
            $field_name = 'billing_' . $key;
            $field_value = get_the_author_meta( $field_name , $user_id );
            echo '"' . $field_value . '",';
          }
          foreach ($s_address as $key => $value) {
            if($key=='first_name' || $key=='last_name') continue;
            $field_name = 'shipping_' . $key;
            $field_value = get_the_author_meta( $field_name , $user_id );
            echo '"' . $field_value . '",';
          }
          $user = @get_userdata( $user_id );
          echo '"' . ( isset( $user->user_login ) ? $user->user_login : __( 'Guest', 'wc_customer_relationship_manager' ) )  . '",';

        }else{
          $order_id = $item['order_id'];
          $user_id = 0;
          $order = new WC_Order( $order_id );
          $wc_crm_customer_details_g = new WC_Crm_Customer_Details(0, $order_id);
          $wc_crm_customer_details_g->init_address_fields('', '', false);
          $b_address = $wc_crm_customer_details_g->billing_fields;
          $s_address = $wc_crm_customer_details_g->shipping_fields;
          foreach ($b_address as $key => $value) {
            if($key=='first_name' || $key=='last_name') continue;
            if($key=='country') {
              echo '"' . $item['country'] . '",';
              continue;
            }
            if($key=='email') {
              echo '"' . $item['email'] . '",';
              continue;
            }
            if($key=='phone') {
              echo '"' . $item['phone'] . '",';
              continue;
            }
            $name_var = 'billing_'.$key;
            $field_value = $wc_crm_customer_details_g->order->$name_var;
            echo '"' . $field_value . '",';
          }
          foreach ($s_address as $key => $value) {
            if($key=='first_name' || $key=='last_name') continue;
            $var_name = 'shipping_'.$key;
            $field_value = $wc_crm_customer_details_g->order->$name_var;
            echo '"' . $field_value . '",';
          }
          echo '"' .  __( 'Guest', 'wc_customer_relationship_manager' )  . '",';
        }


        echo '"' . woocommerce_crm_get_pretty_time( $item['last_order_date'], true ) . '",';
        echo '"' . $item['num_orders'] . '",';        
        if ( woocommerce_crm_mailchimp_enabled() ) {
          $enrolled_plain = in_array( $item['email'], $members ) ? 'yes' : 'no';
          echo '"' . $item['total_spent'] . '",';
          echo '"' . $enrolled_plain . "\"\n";
        }else{
          echo '"' . $item['total_spent'] . "\"\n";
        }
			}

		}

		public function woocommerce_crm_customer_name_filter() {
      global $wpdb;
			?>
			<select id="dropdown_customers" name="_customer_user">
          <option value=""><?php _e( 'Show all customers', 'wc_customer_relationship_manager' ) ?></option>
          <?php
          if ( !empty( $_REQUEST['_customer_user'] ) ) {
            $user = $_REQUEST['_customer_user'];
            $sql = "SELECT * FROM {$wpdb->prefix}wc_crm_customers WHERE email='$user'";
            $user_results = $wpdb->get_results($sql);
            foreach ($user_results as $user) {
              
              echo '<option value="' . $user->email . '" ';
              selected( 1, 1 );
              echo '>' . $user->first_name . ' ' . $user->last_name . ' (' . ( !empty( $user->user_id ) ? '#' . $user->user_id : __( "Guest", 'wc_customer_relationship_manager' ) ) . ' &ndash; ' . sanitize_email( $user->email ) . ')' . '</option>';

            }
            
          }
          ?>
        </select>
			<?php
		}
    public function woocommerce_crm_customer_status_filter() {
		global $wpdb;
		$sql = $this->get_customer_sql('status');
		$customer_status = $wpdb->get_results($sql);
      ?>
      <select id="dropdown_customer_status" name="_customer_status">
          <option value=""><?php _e( 'Show all customer statuses', 'wc_customer_relationship_manager' ) ?></option>
          <?php
        foreach ($customer_status as $status) {
			   if(!$status->status || $status->status == NULL) continue;
            if ( !empty( $_REQUEST['_customer_status'] ) && $_REQUEST['_customer_status'] == $status->status ) {
              echo '<option value="' . $status->status . '" ' . selected( 1, 1 ) . '>' . $status->status . ' (' . $status->count . ')</option>';
            }else{
              echo '<option value="' . $status->status . '" >' . $status->status . ' (' . $status->count . ')</option>';
            }
          }
          ?>
        </select>
      <?php
    }
		public function woocommerce_crm_products_filter() {
      global $wpdb;
			?>
			<select name='_customer_product' id='dropdown_product'>
          <option value=""><?php _e( 'Show all products', 'wc_customer_relationship_manager' ); ?></option>
          <?php
            $product_id = $_REQUEST['_customer_product'];
            if ( $product_id ) {
                $product = get_product( $product_id );
                echo '<option value="' . esc_attr( $product_id ) . '" selected="selected">' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
            }
          ?>
        </select>
			<?php
		}
		public function woocommerce_crm_country_filter() {
			global $wpdb, $woocommerce;
      $sql = $this->get_customer_sql('country');
			$order_countries = $wpdb->get_results($sql);
			?>
			<select name='_customer_country' id='dropdown_country'>
          <option value=""><?php _e( 'Show all countries', 'wc_customer_relationship_manager' ); ?></option>
          <?php

          foreach ( $order_countries as $country ) {
			if(!$country->country || $country->country == NULL) continue;
            echo '<option value="' . $country->country . '" ';
            if ( !empty( $_REQUEST['_customer_country'] ) && $_REQUEST['_customer_country'] == $country->country ) {
              echo 'selected';
            }
            echo '>' . esc_html__( $country->country ) . ' - ' . $woocommerce->countries->countries[$country->country] . ' (' . absint( $country->count ) . ')</option>';
          }
          ?>
        </select>
			<?php
		}
		public function woocommerce_crm_state_filter() {
			global $wpdb;
      $sql = $this->get_customer_sql('state');
			$order_states = $wpdb->get_results($sql);
			?>
			<select name='_customer_state' id='dropdown_state'>
          <option value=""><?php _e( 'Show all states', 'wc_customer_relationship_manager' ); ?></option>
          <?php

          foreach ( $order_states as $state) {
			if(!$state->state || $state->state == NULL) continue;
            echo '<option value="' . $state->state . '" ';
            if ( !empty( $_REQUEST['_customer_state'] ) && $_REQUEST['_customer_state'] == $state->state ) {
              echo 'selected';
            }
            echo '>' . esc_html__( $state->state ) . ' (' . absint( $state->count ) . ')</option>';
          }
          ?>
        </select>
			<?php
		}
		public function woocommerce_crm_city_filter() {
			global $wpdb;
      $sql = $this->get_customer_sql('city');
			$order_city = $wpdb->get_results($sql);
			?>
			<select name='_customer_city' id='dropdown_city'>
          <option value=""><?php _e( 'Show all cities', 'wc_customer_relationship_manager' ); ?></option>
          <?php

          foreach ( $order_city as $city ) {
			if(!$city->city || $city->city == NULL) continue;
            echo '<option value="' . $city->city . '" ';
            if ( !empty( $_REQUEST['_customer_city'] ) && $_REQUEST['_customer_city'] == $city->city ) {
              echo 'selected';
            }
            echo '>' . esc_html__( $city->city ) . ' (' . absint( $city->count ) . ')</option>';
          }
          ?>
        </select>
			<?php
		}
		public function woocommerce_crm_last_order_filter() {
			?>
			<select name='_customer_date_from' id='dropdown_date_from'>
          <option value=""><?php _e( 'All time results', 'wc_customer_relationship_manager' ); ?></option>

          <option
            value="<?php echo date( 'Y-m-d H:00:00', strtotime( '-24 hours' ) ); ?>" <?php if ( !empty( $_REQUEST['_customer_date_from'] ) && date( 'Y-m-d H:00:00', strtotime( '-24 hours' ) ) == $_REQUEST['_customer_date_from'] ) {
            echo "selected";
          } ?> ><?php _e( 'Last 24 hours', 'wc_customer_relationship_manager' ); ?></option>

          <option
            value="<?php echo date( 'Y-m-01 00:00:00', strtotime( 'this month' ) ); ?>" <?php if ( !empty( $_REQUEST['_customer_date_from'] ) && date( 'Y-m-01 00:00:00', strtotime( 'this month' ) ) == $_REQUEST['_customer_date_from'] ) {
            echo "selected";
          } ?> ><?php _e( 'This month', 'wc_customer_relationship_manager' ); ?></option>
          <option
            value="<?php echo date( 'Y-m-d 00:00:00', strtotime( '-30 days' ) ); ?>" <?php if ( !empty( $_REQUEST['_customer_date_from'] ) && date( 'Y-m-d 00:00:00', strtotime( '-30 days' ) ) == $_REQUEST['_customer_date_from'] ) {
            echo "selected";
          } ?> ><?php _e( 'Last 30 days', 'wc_customer_relationship_manager' ); ?></option>
          <option
            value="<?php echo date( 'Y-m-d 00:00:00', strtotime( '-6 months' ) ); ?>" <?php if ( !empty( $_REQUEST['_customer_date_from'] ) && date( 'Y-m-d 00:00:00', strtotime( '-6 months' ) ) == $_REQUEST['_customer_date_from'] ) {
            echo "selected";
          } ?> ><?php _e( 'Last 6 months', 'wc_customer_relationship_manager' ); ?></option>
          <option
            value="<?php echo date( 'Y-m-d 00:00:00', strtotime( '-12 months' ) ); ?>" <?php if ( !empty( $_REQUEST['_customer_date_from'] ) && date( 'Y-m-d 00:00:00', strtotime( '-12 months' ) ) == $_REQUEST['_customer_date_from'] ) {
            echo "selected";
          } ?>><?php _e( 'Last 12 months', 'wc_customer_relationship_manager' ); ?></option>
        </select>
			<?php
		}
		public function woocommerce_crm_user_roles_filter() {
			global $wp_roles;
			?>
			<select name='_user_type' id='dropdown_user_type'>
				<option value=""><?php _e( 'Show all user roles', 'wc_customer_relationship_manager' ); ?></option>
				  <?php
				  foreach ( $wp_roles->role_names as $role => $name ) : ?>
					<option value="<?php echo strtolower($name); ?>" <?php if ( !empty( $_REQUEST['_user_type'] ) && strtolower($name) == $_REQUEST['_user_type'] ) {
					  echo "selected";
					} ?>><?php _e( $name, 'wc_customer_relationship_manager' ); ?>
				</option>

          <?php
          endforeach;
          ?>

            <option value="guest_user" <?php if ( !empty( $_REQUEST['_user_type'] ) && 'guest_user' == $_REQUEST['_user_type'] ) {
            echo "selected";
          } ?>><?php _e( 'Guest', 'wc_customer_relationship_manager' ); ?></option>
        </select>
			<?php
		}
		public function woocommerce_crm_products_variations_filter() {
			?>
			<select name="_products_variations[]" id="dropdown_products_and_variations" multiple="multiple" data-placeholder="<?php _e( 'Search for a product&hellip;', 'woocommerce' ); ?>" style="width: 400px">
				<?php
						$product_ids = $_REQUEST['_products_variations'];
						if ( $product_ids ) {
							foreach ( $product_ids as $product_id ) {
								$product = get_product( $product_id );
								echo '<option value="' . esc_attr( $product_id ) . '" selected="selected">' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
							}
						}
					?>
			</select>

			<?php
		}
		public function woocommerce_crm_order_status_filter() {
      global $wpdb;
      $sql            = $this->get_customer_sql('order_status');
      $order_status   = $wpdb->get_results($sql);      
      $wc_statuses    = wc_get_order_statuses();
      ?>
      <select name='_order_status' id='dropdown_order_status'>
        <option value=""><?php _e( 'Show all statuses', 'woocommerce' ); ?></option>
        <?php
          foreach ( $order_status as $status ) {
            
            if(!isset($wc_statuses[$status->post_status])) continue;
            echo '<option value="' . esc_attr( $status->post_status ) . '"';

            if ( isset( $_REQUEST['_order_status'] ) ) {
              selected( $status->post_status, $_REQUEST['_order_status'] );
            }

            echo '>' . esc_html__( $wc_statuses[$status->post_status], 'woocommerce' )  . ' ('.$status->count.')' . '</option>';
          }
        ?>
      </select>

      <?php
    }
    /****************/
    public function woocommerce_crm_products_categories_filter() {
      ?>
      <select name='_products_categories[]' id='dropdown_products_categories' multiple="multiple" data-placeholder="<?php _e( 'Search for a category&hellip;', 'woocommerce' ); ?>" >
        <?php
          $cat = array();
          if ( isset( $_REQUEST['_products_categories'] ) ) {
            $cat = $_REQUEST['_products_categories'];
          }
          $all_cat = get_terms( array('product_cat'),  array( 'orderby' => 'name', 'order' => 'ASC')  );
          if(!empty($all_cat)){
            foreach ($all_cat as $key => $value) {
              echo '<option value="'.$value->term_id.'" '.( in_array($value->term_id, $cat) ? 'selected="selected"' : '' ).'>'.$value->name.'</option>';
            }
          }
        ?>
      </select>

      <?php
    }
    public function woocommerce_crm_products_brands_filter() {
      if( class_exists( 'WC_Brands_Admin' ) ) {
  			?>
        <select name='_products_brands[]' id='dropdown_products_brands' multiple="multiple" data-placeholder="<?php _e( 'Search for a brand&hellip;', 'woocommerce' ); ?>" >
          <?php $brand = array();
                if ( isset( $_REQUEST['_products_brands'] ) ) {
                  $brand = $_REQUEST['_products_brands'];
                }
                $all_brands = get_terms( array('product_brand'),  array( 'orderby' => 'name', 'order' => 'ASC')  );
                if(!empty($all_brands)){
                  foreach ($all_brands as $key => $value) {
                    echo '<option value="'.$value->term_id.'" '.( in_array($value->term_id, $brand) ? 'selected="selected"' : '' ).'>'.$value->name.'</option>';
                  }
                }
          ?>
        </select>

  			<?php
      }
		}
    /****************/

    public function woocommerce_crm_types_of_activity_filter() {
      global $activity_types;
      ?>
      <select name='activity_types' id='dropdown_activity_types'>
        <option value=""><?php _e( 'Show all types', 'woocommerce' ); ?></option>
        <?php
          foreach ( $activity_types as $type=>$count ) {
            echo '<option value="' . esc_attr( $type ) . '"';

            if ( isset( $_REQUEST['activity_types'] ) ) {
              selected( $type, $_REQUEST['activity_types'] );
            }
            echo '>' . esc_html__( $type, 'woocommerce' ) . ' (' . absint( $count ) . ')</option>';
          }
        ?>
      </select>
      <?php
    }
    public function woocommerce_crm_created_date_filter() {
      global $months, $wp_locale;
      $month_count = count( $months );

      if ( !$month_count || ( 1 == $month_count && 0 == $months[0]->month ) )
        return;

      $m = isset( $_GET['created_date'] ) ? (int) $_GET['created_date'] : 0;
      $m = isset( $_POST['created_date'] ) ? (int) $_POST['created_date'] : $m;
        ?>
            <select name='created_date' id="created_date">
              <option<?php selected( $m, 0 ); ?> value='0'><?php _e( 'Show all dates' ); ?></option>
        <?php
            foreach ( $months as $arc_row ) {
              if ( 0 == $arc_row->year )
                continue;

              $month = zeroise( $arc_row->month, 2 );
              $year = $arc_row->year;

              printf( "<option %s value='%s'>%s</option>\n",
                selected( $m, $year . $month, false ),
                esc_attr( $arc_row->year . $month ),
                /* translators: 1: month name, 2: 4-digit year */
                sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year )
              );
            }
        ?>
      </select>
        <?php
    }
    public function woocommerce_crm_log_username_filter() {
      global $log_users;
      ?>
      <select name='log_users' id='dropdown_log_users'>
        <option value=""><?php _e( 'Show all authors', 'woocommerce' ); ?></option>
        <?php
          foreach ( $log_users as $userid=>$count ) {
            $userdata = get_userdata( $userid );
            echo '<option value="' . absint( $userid ) . '"';

            if ( isset( $_REQUEST['log_users'] ) ) {
              selected( $userid, $_REQUEST['log_users'] );
            }
            echo '>' . $userdata->first_name.' '.$userdata->last_name  . ' (' . absint( $count ) . ')</option>';
          }
        ?>
      </select>
      <?php
    }

    /**
     * Filter for Logs page
     *
     */
    public function woocommerce_crm_restrict_list_logs() {
        $woocommerce_crm_filters_log = array(
            'types_of_activity',
            'created_date',
            'log_username'
          );
          ?>
          <div class="alignleft actions">
          <?php
            foreach ($woocommerce_crm_filters_log as $key => $value) {
                add_action( 'woocommerce_crm_add_filters_log', array($this, 'woocommerce_crm_'.$value.'_filter') );
            }
            do_action( 'woocommerce_crm_add_filters_log');
          ?>
          <input type="submit" id="post-query-submit" class="button action" value="Filter"/>
        </div>
          <?php
          $js = "
                jQuery('select#dropdown_activity_types').css('width', '150px').chosen();

                jQuery('select#dropdown_log_users').css('width', '150px').chosen();

                jQuery('select#created_date').css('width', '150px').chosen();
            ";

      if ( class_exists( 'WC_Inline_Javascript_Helper' ) ) {
        $woocommerce->get_helper( 'inline-javascript' )->add_inline_js( $js );
      } elseif( function_exists('wc_enqueue_js') ){
        wc_enqueue_js($js);
      }  else {
        $woocommerce->add_inline_js( $js );
      }
    }
		/**
		 * Provides the select boxes to filter Customers, Country and Time Period.
		 *
		 */
		public function woocommerce_crm_restrict_list_customers() {
			global $woocommerce;
			$woocommerce_crm_filters = get_option( 'woocommerce_crm_filters' );
      $_customer_date_from = '';
      $_user_type = '';
      $_customer_state = '';
      $_customer_city = '';
      $_customer_user = '';
      $_customer_country = '';
      $_customer_product = '';
      $_order_status = '';
			if( !empty($woocommerce_crm_filters) ) :
			?>
			<div class="alignleft actions">
				<?php
					foreach ($woocommerce_crm_filters as $key => $value) {
							add_action( 'woocommerce_crm_add_filters', array($this, 'woocommerce_crm_'.$value.'_filter') );
					}
					do_action( 'woocommerce_crm_add_filters');
				?>
			<input type="submit" id="post-query-submit" class="button action" value="Filter"/>

				<?php
        $_customer_date_from = isset( $_REQUEST['_customer_date_from'] ) ? $_REQUEST['_customer_date_from'] : '';
        $_user_type = isset( $_REQUEST['_user_type'] ) ? $_REQUEST['_user_type'] : '';
        $_customer_state = isset( $_REQUEST['_customer_state'] ) ? $_REQUEST['_customer_state'] : '';
        $_customer_city = isset( $_REQUEST['_customer_city'] ) ? $_REQUEST['_customer_city'] : '';
				$_customer_user = isset( $_REQUEST['_customer_user'] ) ? $_REQUEST['_customer_user'] : '';
        $_customer_country = isset( $_REQUEST['_customer_country'] ) ? $_REQUEST['_customer_country'] : '';
        $_customer_product = isset( $_REQUEST['_customer_product'] ) ? $_REQUEST['_customer_product'] : '';
				$_order_status = isset( $_REQUEST['_order_status'] ) ? $_REQUEST['_order_status'] : '';

				?>

			</div>

			<?php
endif;

			$js = "
              jQuery('#doaction').click(function(){
                var val = $('select[name=\"action\"]').val();
                if( val == 'export_csv'){
                 location.href='admin-post.php?action=export_csv&_customer_user=$_customer_user&_customer_country=$_customer_country&_customer_date_from=$_customer_date_from&_user_type=$_user_type&_customer_state=$_customer_state&_customer_city=$_customer_city&_customer_product=$_customer_product';
                return false;

               }
              });

                jQuery('select#dropdown_customer_status').css('width', '150px').chosen();

                jQuery('select#dropdown_country').css('width', '150px').chosen();

                jQuery('select#dropdown_state').css('width', '150px').chosen();

                jQuery('select#dropdown_city').css('width', '150px').chosen();

                jQuery('select#dropdown_date_from').css('width', '150px').chosen();

                jQuery('select#dropdown_user_type').css('width', '150px').chosen();

                jQuery('select#dropdown_order_status').css('width', '150px').chosen();

                jQuery('select#dropdown_products_categories').css('width', '400px').chosen();

                jQuery('select#dropdown_products_brands').css('width', '400px').chosen();

                jQuery('select#dropdown_product').css('width', '150px').ajaxChosen({
                    method:     'GET',
                    url:      '" . admin_url( 'admin-ajax.php' ) . "',
                    dataType:     'json',
                    afterTypeDelay: 100,
                    minTermLength:  3,
                    data:   {
                        action:   'woocommerce_crm_json_search_products',
                        security:   '" . wp_create_nonce( "search-products" ) . "',
                        default:  '" . __( 'Show all products', 'wc_customer_relationship_manager' ) . "',
                    }
                }, function (data) {

                    var terms = {};

                    $.each(data, function (i, val) {
                        terms[i] = val;
                    });

                    return terms;
                });

                jQuery('select#dropdown_customers').css('width', '200px').ajaxChosen({
                    method: 		'GET',
                    url: 			'" . admin_url( 'admin-ajax.php' ) . "',
                    dataType: 		'json',
                    afterTypeDelay: 100,
                    minTermLength: 	3,
                    data:		{
                        action: 	'woocommerce_crm_json_search_customers',
                        security: 	'" . wp_create_nonce( "search-customers" ) . "',
                        default:	'" . __( 'Show all customers', 'wc_customer_relationship_manager' ) . "',
                    }
                }, function (data) {

                    var terms = {};

                    $.each(data, function (i, val) {
                        terms[i] = val;
                    });

                    return terms;
                });



								jQuery('select#dropdown_products_and_variations').css('width', '400px').ajaxChosen({
                    method: 		'GET',
                    url: 			'" . admin_url( 'admin-ajax.php' ) . "',
                    dataType: 		'json',
                    afterTypeDelay: 100,
                    minTermLength: 	3,
                    data:		{
                        action: 	'woocommerce_crm_json_search_variations',
                        security: 	'" . wp_create_nonce( "search-products" ) . "',
                    }
                }, function (data) {

                    var terms = {};

                    $.each(data, function (i, val) {
                        terms[i] = val;
                    });

                    return terms;
                });
            ";

			if ( class_exists( 'WC_Inline_Javascript_Helper' ) ) {
				$woocommerce->get_helper( 'inline-javascript' )->add_inline_js( $js );
			} elseif( function_exists('wc_enqueue_js') ){
				wc_enqueue_js($js);
			}  else {
				$woocommerce->add_inline_js( $js );
			}

		}

		

    public function modify_contact_methods($profile_fields) {

        // Add new fields
        $profile_fields['twitter'] = 'Twitter Username';
        $profile_fields['skype'] = 'Skype';

        return $profile_fields;
      }

    public function save_user_field_status($user_id ) {
        if ( !current_user_can( 'edit_user', $user_id ) )
            return false;
        update_user_meta( $user_id, 'customer_status', $_POST['customer_status'] );
      }

    public function add_user_field_status($user ) {
      ?>
      <table class="form-table">
          <tr>
              <th><label for="dropdown"><?php _e( 'Customer status', 'wc_customer_relationship_manager' ) ?></label></th>
              <td>
                  <select id="customer_status" name="customer_status" class="chosen_select">
                      <?php
                      $selected = get_the_author_meta( 'customer_status', $user->ID );
                      if ( empty($selected) ) $selected ='Lead';
                      $statuses = $this->statuses;
                      foreach ( $statuses as $key => $status ) {
                        echo '<option value="' . esc_attr( $key ) . '" ' . selected( $key, $selected, false ) . '>' . esc_html__( $status, 'wc_customer_relationship_manager' ) . '</option>';
                      }
                  ?>
                  </select>
                  <span class="description"></span>
              </td>
          </tr>
      </table>
      <?php
      }

		

		/**
		 * Overrides the WooCommerce search in orders capability if we search by customer.
		 *
		 * @param $fields
		 * @return array
		 */
		public function woocommerce_crm_search_by_email( $fields ) {
			if ( isset( $_GET["search_by_email_only"] ) ) {
				return array('_billing_email');
			}
			return $fields;
		}

		/**
		 * @param $views
		 * @return array
		 */
		public function views_shop_order( $views ) {
			if ( isset( $_GET["search_by_email_only"] ) ) {
				return array();
			}
			return $views;
		}

		/**
		 * get_tab_in_view()
		 *
		 * Get the tab current in view/processing.
		 */
		function get_tab_in_view( $current_filter, $filter_base ) {
			return str_replace( $filter_base, '', $current_filter );
		}


		/**
		 * add_settings_fields()
		 *
		 * Add settings fields for each tab.
		 */
		function add_settings_fields() {
			global $woocommerce_settings;

			// Load the prepared form fields.
			$this->init_form_fields();

			if ( is_array( $this->fields ) )
				foreach ( $this->fields as $k => $v )
					$woocommerce_settings[$k] = $v;
		}

   

		/**
		 * init_form_fields()
		 *
		 * Prepare form fields to be used in the various tabs.
		 */
		function init_form_fields() {


		}

    public function wc_crm_add_customer_status($userdata){
      update_user_meta( $userdata['ID'], 'customer_status', 'Customer' );
      return $userdata;
    }

    public function customer_filter()
    {
      $filter = '';
      if(isset($_REQUEST['_user_type']) && !empty($_REQUEST['_user_type'])){
      
        if($_REQUEST['_user_type'] == 'guest_user')
            $filter .= "AND (capabilities IS NULL OR capabilities = '' )
            ";
        else
            $filter .= "AND capabilities LIKE '%".$_REQUEST['_user_type']."%'
            ";
      }
      if( isset($_REQUEST['_customer_date_from']) && !empty( $_REQUEST['_customer_date_from'] ) ){
        $filter .= "AND  DATE(last_order_date) >= '".date( 'Y-m-d', strtotime( $_REQUEST['_customer_date_from'] ) ) . "'
              ";
      }
     if( isset($_REQUEST['_customer_state']) && !empty( $_REQUEST['_customer_state'] ) ){
          $filter .= "AND  state  = '". $_REQUEST['_customer_state'] . "'
          ";
      }
      if( isset($_REQUEST['_customer_city']) && !empty( $_REQUEST['_customer_city'] ) ){
          $filter .= "AND  city = '". $_REQUEST['_customer_city'] . "'
          ";
      }
      if( isset($_REQUEST['_customer_country']) && !empty( $_REQUEST['_customer_country'] ) ){
          $filter .= "AND  country = '". $_REQUEST['_customer_country'] . "'
          ";
      }
      if( isset($_REQUEST['_customer_user']) && !empty( $_REQUEST['_customer_user'] ) ){
        $term = $_REQUEST['_customer_user'];
        $filter .= "AND email = '$term'
          ";
      }
      if( isset($_REQUEST['s']) && !empty( $_REQUEST['s'] ) ){
        $term = $_REQUEST['s'];
        $filter .= "AND (LOWER(first_name) LIKE LOWER('%$term%') OR LOWER(last_name) LIKE LOWER('%$term%') OR LOWER(email) LIKE LOWER('%$term%') OR user_id LIKE '%$term%' OR concat_ws(' ',first_name,last_name) LIKE '%$term%' )
          ";
      }       

      if( isset($_REQUEST['_customer_status']) && !empty( $_REQUEST['_customer_status'] ) ){
          $filter .= "AND  status = '". $_REQUEST['_customer_status'] . "'
          ";
      }
      
      return $filter;


    }

    function get_customer_sql($value='')
    {
      global $wpdb;
      if($value == '') return '';
      $filter = $this->customer_filter();

      if($value == 'order_status'){        
        $sql = "SELECT post_status, count(post_status) as count
        FROM (
          SELECT * FROM {$wpdb->prefix}wc_crm_customers
          inner join {$wpdb->postmeta} 
                ON ({$wpdb->postmeta}.meta_value = {$wpdb->prefix}wc_crm_customers.email AND {$wpdb->postmeta}.meta_key = '_billing_email' AND {$wpdb->prefix}wc_crm_customers.email != '')
          inner JOIN {$wpdb->posts} posts
                ON ({$wpdb->postmeta}.post_id= posts.ID AND posts.post_status != 'trash' AND posts.post_status != 'auto-draft'  AND posts.post_type =  'shop_order' )

          group by {$wpdb->prefix}wc_crm_customers.email
          ) as crm_table
          group by post_status ";
          
          #echo '<textarea style="width: 100%; height: 100%; ">'.$sql.'</textarea>'; die;
      }else{
        $sql = "SELECT $value, count($value) as count
        FROM (
          SELECT * FROM {$wpdb->prefix}wc_crm_customers
          WHERE 1=1
          $filter
          ) as crm_table
          group by $value
      " ;
      }
      
      
      return $sql;
    }

    function get_user_sql($f = ''){
      $filter = str_replace('WHERE', 'AND', $f);

      $num_orders_status = get_option('woocommerce_crm_number_of_orders');
      if(!$num_orders_status || empty($num_orders_status)){
        $num_orders_status[] = 'wc-completed';
      }
      $num_orders_statuses = "'" . implode("','", $num_orders_status) . "'";

      $total_value_status = get_option('woocommerce_crm_total_value');
      if(!$total_value_status || empty($total_value_status)){
        $total_value_status[] = 'wc-completed';
      }
      $total_value_statuses = "'" . implode("','", $total_value_status) . "'";

      global $wpdb;
      $wpdb->query('SET OPTION SQL_BIG_SELECTS = 1');
      $sql = "INSERT INTO {$wpdb->prefix}wc_crm_customers (email, user_id, capabilities, first_name, last_name, nicename, status, phone, city, state, country, categories, brands, order_id, last_order_date, num_orders, total_spent, find_orders)
        SELECT * FROM (          
              SELECT 
              {$wpdb->users}.user_email as user_email,
              {$wpdb->users}.ID as user_id,
              usermeta_role.meta_value as crm_capabilities,
              usermeta_fn.meta_value as crm_first_name,
              usermeta_ln.meta_value as crm_last_name,
              {$wpdb->users}.user_login as crm_nicename,
              IF(usermeta_status.meta_value IS NULL, 'Customer', usermeta_status.meta_value) as crm_status,
              usermeta_phone.meta_value as crm_phone,
              usermeta_city.meta_value as crm_city,
              usermeta_state.meta_value as crm_state,
              usermeta_country.meta_value as crm_country,
              usermeta_categories.meta_value as crm_categories,
              usermeta_brands.meta_value as crm_brands,
              max({$wpdb->posts}.ID) as crm_order_id,
              max({$wpdb->posts}.post_date) as crm_last_order_date,
              SUM( if({$wpdb->posts}.post_status IN({$num_orders_statuses}), 1, 0)) as crm_num_orders,
              SUM(meta_amount.meta_value) as crm_total_spent,
              COUNT({$wpdb->posts}.ID) as crm_find_orders
             
          FROM {$wpdb->posts}

          inner JOIN {$wpdb->postmeta} posts_meta
                ON (posts_meta.post_id={$wpdb->posts}.ID AND posts_meta.meta_key = '_customer_user' AND posts_meta.meta_value > 0 AND {$wpdb->posts}.post_type =  'shop_order' )
                
          LEFT JOIN {$wpdb->users}  ON ({$wpdb->users}.ID = posts_meta.meta_value)

          LEFT JOIN {$wpdb->usermeta} usermeta_role
                ON (usermeta_role.user_id={$wpdb->users}.ID AND usermeta_role.meta_key = '{$wpdb->prefix}capabilities')
                
          LEFT JOIN {$wpdb->usermeta} usermeta_fn
                ON (usermeta_fn.user_id={$wpdb->users}.ID AND usermeta_fn.meta_key = 'first_name')
          
          LEFT JOIN {$wpdb->usermeta} usermeta_ln
                ON (usermeta_ln.user_id={$wpdb->users}.ID AND usermeta_ln.meta_key = 'last_name')
                
          LEFT JOIN {$wpdb->usermeta} usermeta_status
                ON (usermeta_status.user_id={$wpdb->users}.ID AND usermeta_status.meta_key = 'customer_status')
                
          LEFT JOIN {$wpdb->usermeta} usermeta_phone
                ON (usermeta_phone.user_id={$wpdb->users}.ID AND usermeta_phone.meta_key = 'billing_phone')
                
          LEFT JOIN {$wpdb->usermeta} usermeta_city
                ON (usermeta_city.user_id={$wpdb->users}.ID AND usermeta_city.meta_key = 'billing_city')
          
          LEFT JOIN {$wpdb->usermeta} usermeta_country
                ON (usermeta_country.user_id={$wpdb->users}.ID AND usermeta_country.meta_key='billing_country')
                
          LEFT JOIN {$wpdb->usermeta} usermeta_state
                ON (usermeta_state.user_id={$wpdb->users}.ID AND usermeta_state.meta_key = 'billing_state')

          LEFT JOIN {$wpdb->usermeta} usermeta_categories
                ON (usermeta_categories.user_id={$wpdb->users}.ID AND usermeta_categories.meta_key = 'customer_categories')

          LEFT JOIN {$wpdb->usermeta} usermeta_brands
                ON (usermeta_brands.user_id={$wpdb->users}.ID AND usermeta_brands.meta_key = 'customer_brands')

          LEFT JOIN {$wpdb->postmeta} meta_amount
                      ON({$wpdb->posts}.post_status IN({$total_value_statuses}) AND meta_amount.post_id = {$wpdb->posts}.ID AND meta_amount.meta_key='_order_total' )
            
          WHERE 1=1
          $filter
            
          group by user_email 
        ) as crm_table
        
        ON DUPLICATE KEY UPDATE capabilities = crm_table.crm_capabilities,
               first_name = crm_table.crm_first_name,
               last_name = crm_table.crm_last_name,
               nicename = crm_table.crm_nicename,
               status = IF(crm_table.crm_status IS NULL, 'Customer', crm_table.crm_status),
               phone = crm_table.crm_phone,
               city = crm_table.crm_city,
               state = crm_table.crm_state,
               country = crm_table.crm_country,
               categories = crm_table.crm_categories,
               brands = crm_table.crm_brands,
               order_id = crm_table.crm_order_id,
               last_order_date = crm_table.crm_last_order_date,
               num_orders = crm_table.crm_num_orders,
               total_spent = crm_table.crm_total_spent";
      return $sql;
    }

    function get_cust_sql($f = ''){
      $filter = str_replace('WHERE', 'AND', $f);

      global $wpdb;
      $wpdb->query('SET OPTION SQL_BIG_SELECTS = 1');
      $sql = "INSERT INTO {$wpdb->prefix}wc_crm_customers (email, user_id, capabilities, first_name, last_name, nicename, status, phone, city, state, country, categories, brands, order_id, last_order_date, num_orders, total_spent, find_orders)
        SELECT * FROM (          
              SELECT 
              {$wpdb->users}.user_email as user_email,
              {$wpdb->users}.ID as user_id,
              usermeta_role.meta_value as crm_capabilities,
              usermeta_fn.meta_value as crm_first_name,
              usermeta_ln.meta_value as crm_last_name,
              {$wpdb->users}.user_login as crm_nicename,
              IF(usermeta_status.meta_value IS NULL, 'Customer', usermeta_status.meta_value) as crm_status,
              usermeta_phone.meta_value as crm_phone,
              usermeta_city.meta_value as crm_city,
              usermeta_state.meta_value as crm_state,
              usermeta_country.meta_value as crm_country,
              usermeta_categories.meta_value as crm_categories,
              usermeta_brands.meta_value as crm_brands,
              NULL as crm_order_id,
              NULL as crm_last_order_date,
              0 as crm_num_orders,
              0 as crm_total_spent,
              0 as crm_find_orders
             
          FROM {$wpdb->users}

          LEFT JOIN {$wpdb->postmeta} posts_meta
                ON ( posts_meta.meta_key = '_customer_user' AND posts_meta.meta_value = {$wpdb->users}.ID )

          LEFT JOIN {$wpdb->usermeta} usermeta_role
                ON (usermeta_role.user_id={$wpdb->users}.ID AND usermeta_role.meta_key = '{$wpdb->prefix}capabilities')
                
          LEFT JOIN {$wpdb->usermeta} usermeta_fn
                ON (usermeta_fn.user_id={$wpdb->users}.ID AND usermeta_fn.meta_key = 'first_name')
          
          LEFT JOIN {$wpdb->usermeta} usermeta_ln
                ON (usermeta_ln.user_id={$wpdb->users}.ID AND usermeta_ln.meta_key = 'last_name')
                
          LEFT JOIN {$wpdb->usermeta} usermeta_status
                ON (usermeta_status.user_id={$wpdb->users}.ID AND usermeta_status.meta_key = 'customer_status')
                
          LEFT JOIN {$wpdb->usermeta} usermeta_phone
                ON (usermeta_phone.user_id={$wpdb->users}.ID AND usermeta_phone.meta_key = 'billing_phone')
                
          LEFT JOIN {$wpdb->usermeta} usermeta_city
                ON (usermeta_city.user_id={$wpdb->users}.ID AND usermeta_city.meta_key = 'billing_city')
          
          LEFT JOIN {$wpdb->usermeta} usermeta_country
                ON (usermeta_country.user_id={$wpdb->users}.ID AND usermeta_country.meta_key='billing_country')
                
          LEFT JOIN {$wpdb->usermeta} usermeta_state
                ON (usermeta_state.user_id={$wpdb->users}.ID AND usermeta_state.meta_key = 'billing_state')

          LEFT JOIN {$wpdb->usermeta} usermeta_categories
                ON (usermeta_categories.user_id={$wpdb->users}.ID AND usermeta_categories.meta_key = 'customer_categories')

          LEFT JOIN {$wpdb->usermeta} usermeta_brands
                ON (usermeta_brands.user_id={$wpdb->users}.ID AND usermeta_brands.meta_key = 'customer_brands')
            
          WHERE 1=1
          AND usermeta_role.meta_value LIKE '%customer%'
          AND posts_meta.meta_value IS NULL
          $filter
            
          group by user_email 
        ) as crm_table
        
        ON DUPLICATE KEY UPDATE capabilities = crm_table.crm_capabilities,
               first_name = crm_table.crm_first_name,
               last_name = crm_table.crm_last_name,
               nicename = crm_table.crm_nicename,
               status = IF(crm_table.crm_status IS NULL, 'Customer', crm_table.crm_status),
               phone = crm_table.crm_phone,
               city = crm_table.crm_city,
               state = crm_table.crm_state,
               country = crm_table.crm_country,
               categories = crm_table.crm_categories,
               brands = crm_table.crm_brands,
               order_id = NULL,
               last_order_date = NULL,
               num_orders = 0,
               total_spent = 0";
      return $sql;
    }
    function get_guest_sql($f = ''){
      $filter = str_replace('WHERE', 'AND', $f);

      $num_orders_status = get_option('woocommerce_crm_number_of_orders');
      if(!$num_orders_status || empty($num_orders_status)){
        $num_orders_status[] = 'wc-completed';
      }
      $num_orders_statuses = "'" . implode("','", $num_orders_status) . "'";

      $total_value_status = get_option('woocommerce_crm_total_value');
      if(!$total_value_status || empty($total_value_status)){
        $total_value_status[] = 'wc-completed';
      }
      $total_value_statuses = "'" . implode("','", $total_value_status) . "'";

      global $wpdb;
      $wpdb->query('SET OPTION SQL_BIG_SELECTS = 1');
      $sql = "INSERT INTO {$wpdb->prefix}wc_crm_customers (email, user_id, capabilities, first_name, last_name, nicename, status, phone, city, state, country, categories, brands, order_id, last_order_date, num_orders, total_spent, find_orders)
        SELECT * FROM (          
               SELECT  
              usermeta_email.meta_value as user_email,
              NULL as user_id,
              NULL as crm_capabilities,
              usermeta_fn.meta_value as crm_first_name,
              usermeta_ln.meta_value as crm_last_name,
              NULL as crm_nicename,
              NULL as crm_status,             
              usermeta_phone.meta_value as crm_phone,
              usermeta_city.meta_value as crm_city,
              usermeta_state.meta_value as crm_state,
              usermeta_country.meta_value as crm_country,
              NULL as crm_categories,
              NULL as crm_brands,
              max({$wpdb->posts}.ID) as crm_order_id,
              max({$wpdb->posts}.post_date) as crm_last_order_date,
              SUM( if({$wpdb->posts}.post_status IN({$num_orders_statuses}), 1, 0)) as crm_num_orders,
              SUM(meta_amount.meta_value) as crm_total_spent,
              COUNT({$wpdb->posts}.ID) as crm_find_orders
          FROM {$wpdb->posts}

          inner JOIN {$wpdb->postmeta} guests
                ON (guests.post_id={$wpdb->posts}.ID AND guests.meta_key = '_customer_user' AND guests.meta_value = 0 AND {$wpdb->posts}.post_type =  'shop_order' )
                
          LEFT JOIN {$wpdb->postmeta} usermeta_fn
                ON (usermeta_fn.post_id={$wpdb->posts}.ID AND usermeta_fn.meta_key = '_billing_first_name')
          
          LEFT JOIN {$wpdb->postmeta} usermeta_ln
                ON (usermeta_ln.post_id={$wpdb->posts}.ID AND usermeta_ln.meta_key = '_billing_last_name')
                
          LEFT JOIN {$wpdb->postmeta} usermeta_phone
                ON (usermeta_phone.post_id={$wpdb->posts}.ID AND usermeta_phone.meta_key = '_billing_phone')
                
          LEFT JOIN {$wpdb->postmeta} usermeta_city
                ON (usermeta_city.post_id={$wpdb->posts}.ID AND usermeta_city.meta_key = '_billing_city')
          
          LEFT JOIN {$wpdb->postmeta} usermeta_country
                ON (usermeta_country.post_id={$wpdb->posts}.ID AND usermeta_country.meta_key='_billing_country')
                
          LEFT JOIN {$wpdb->postmeta} usermeta_state
                ON (usermeta_state.post_id={$wpdb->posts}.ID AND usermeta_state.meta_key = '_billing_state')
          
          LEFT JOIN {$wpdb->postmeta} usermeta_email
                ON (usermeta_email.post_id={$wpdb->posts}.ID AND usermeta_email.meta_key = '_billing_email')

          LEFT JOIN {$wpdb->postmeta} meta_amount
                      ON({$wpdb->posts}.post_status IN({$total_value_statuses}) AND meta_amount.post_id = {$wpdb->posts}.ID AND meta_amount.meta_key='_order_total' )

          WHERE 1=1
          $filter
            
          group by user_email 
        ) as crm_table
        
        ON DUPLICATE KEY UPDATE capabilities = crm_table.crm_capabilities,
               first_name = crm_table.crm_first_name,
               last_name = crm_table.crm_last_name,
               nicename = crm_table.crm_nicename,
               status = crm_table.crm_status,
               phone = crm_table.crm_phone,
               city = crm_table.crm_city,
               state = crm_table.crm_state,
               country = crm_table.crm_country,
               categories = crm_table.crm_categories,
               brands = crm_table.crm_brands,
               order_id = crm_table.crm_order_id,
               last_order_date = crm_table.crm_last_order_date,
               num_orders = crm_table.crm_num_orders,
               total_spent = crm_table.crm_total_spent";
      return $sql;
    }

	}

	$wc_customer_relationship_manager = new WooCommerce_Customer_Relationship_Manager();

}