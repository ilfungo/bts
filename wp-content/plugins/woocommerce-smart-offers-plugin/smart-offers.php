<?php
/*
  Plugin Name: Smart Offers
  Plugin URI: http://www.storeapps.org/product/smart-offers/
  Description: <strong>Smart Offers</strong> lets you earn more by creating a powerful sales funnel of upsells, downsells and backend offers. Show special offers during checkout or on my account page.
  Version: 2.3
  Author: Store Apps
  Author URI: http://www.storeapps.org/
  Copyright (c) 2013, 2014 Store Apps
 */
if (!defined('ABSPATH')) {
	exit;
}

$active_plugins = (array) get_option('active_plugins', array());

if (is_multisite()) {
	$active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
}

if (!( in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins) )) {
	return;
} else {

	if (!class_exists('SA_Smart_Offers')) {

		class SA_Smart_Offers {

			function __construct() {
				global $wpdb;

				$this->define_constants();
				$this->includes();

				add_action('init', array($this, 'init'));

				if (is_admin()) {
					$this->initialize_so_upgrade();
				}
			}

			/*
			 * Include class files
			 */

			function includes() {

				if (is_admin()) {
					include_once 'includes/admin/class-so-admin-install.php';
					include_once 'includes/admin/class-so-admin-settings.php';

					// Post type
					include_once( 'includes/admin/class-so-admin-post-type.php' );
					include_once( 'includes/admin/class-so-admin-offer.php' );
					include_once( 'includes/admin/class-so-admin-offers.php' );
					include_once( 'includes/admin/class-so-admin-dashboard-widget.php' );
					include_once( 'includes/admin/class-so-admin-footer.php' );

					 if ( ! class_exists( 'Store_Apps_Upgrade' ) ) {
						require_once 'sa-includes/class-storeapps-upgrade.php';
					}
				}

				include_once ( 'includes/class-so-product-details.php' );
				include_once ( 'classes/class-wc-compatibility.php' );
				include_once ( 'classes/class-wc-compatibility-2-2.php' );

				if (!is_admin() || defined('DOING_AJAX')) {
					include_once( 'includes/frontend/class-so-init.php' );
					include_once( 'includes/frontend/class-so-shortcodes.php' );
					include_once( 'includes/frontend/class-so-offer.php' );
					include_once( 'includes/frontend/class-so-offers.php' );
					include_once( 'includes/frontend/class-so-session-handler.php' );
				}
			}

			/**
			 * Function for getting suitable compatibility class
			 */
			public static function wc_compat() {
				return 'SA_WC_Compatibility_2_2';
			}

			/*
			 * Defining SO Constants
			 */

			private function define_constants() {

				define('SO_PLUGIN_FILE', __FILE__);
				define('SMART_OFFERS', substr( plugin_basename( __FILE__ ), 0, strpos( plugin_basename( __FILE__ ), '/' ) ) );
				define('SO_TEXT_DOMAIN', 'smart_offers');
			}

			function init() {

				/**
				 * Localisation
				 * */
				load_plugin_textdomain(SO_TEXT_DOMAIN, false, dirname(plugin_basename(__FILE__)) . '/languages');
			}

			/*
			 * Initializing So Upgrade class
			 */
			function initialize_so_upgrade() {
				$sku = 'so';
				$prefix = 'smart_offers';
				$plugin_name = 'Smart Offers';
				$documentation_link = 'http://www.storeapps.org/support/documentation/smart-offers/';
				new Store_Apps_Upgrade(__FILE__, $sku, $prefix, $plugin_name, SO_TEXT_DOMAIN, $documentation_link);
			}
			
		}// End of class SA_Smart_Offers
		
		
		/*
		 * Initializing SO class
		 */
		function initialize_so() {
			$GLOBALS['sa_smart_offers'] = new SA_Smart_Offers();
		}

		add_action('woocommerce_loaded', 'initialize_so');
	} // End class exists check
}
