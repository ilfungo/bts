<?php
/*
	Plugin Name: Obox Social Commerce
	Plugin URI: http://obox-design.com
	Description: A framework which formats your commerce site for facebook
	Version: 1.2.7
	Author: Obox Design
	Author URI: http://www.obox-design.com
*/

/***************************/
/* Set Directories and Files*/
$presstrendsid = "z13czhdttqjag5rmkud3rmjorj3gyp7oj";
$wp_plugin_dir = ABSPATH."wp-content/plugins/";
$plugin_dir = ABSPATH."wp-content/plugins/obox-social-commerce/";
$plugin_url = get_bloginfo('wpurl')."/wp-content/plugins/obox-social-commerce/";

define('OBOXFBID', '1540');
define('OBOXFB_VER', '1.2.6' );
define('OBOXFBDIR', $plugin_dir);
define('OBOXFBURL', $plugin_url);

/***********************************/
/* Run when we activate the plugin */
function oboxfb_setup(){
	include(OBOXFBDIR."admin/setup/theme-options.php");
	include(OBOXFBDIR."admin/includes/functions.php");

	global $oboxfb_theme_options;

	foreach($oboxfb_theme_options as $theme_option => $value)
		{
			if(function_exists("oboxfb_reset_option")):
				oboxfb_reset_option($theme_option);
			endif;
		}
}

register_activation_hook( __FILE__, 'oboxfb_setup' );

/***********************/
/* Include admin files */
function oboxfb_includes(){
	$include_folders = array("functions/", "admin/interface/", "admin/includes/", "admin/setup/");
	include_once ("admin/folder-class.php");
	include_once ("admin/load-includes.php");
}
add_action("plugins_loaded", "oboxfb_includes");

/****************************************/
/* Begin OCMX Mobile Checks & Implement */
function begin_oboxfb(){
	global $ocmx_oboxfb_class;
	$ocmx_oboxfb_class = new OBOXFB();
	$ocmx_oboxfb_class->initiate();
}
add_action( 'plugins_loaded', 'begin_oboxfb' );

/***********************/
/* Add OCMX Menu Items */
function oboxfb_add_admin() {

 	global $add_general_page, $add_update_page ;
 	add_object_page("Social Commerce", "Social Commerce", 'manage_options', basename(__FILE__), '', '//obox-design.com/images/ocmx-favicon.png');

	$add_general_page = add_submenu_page(basename(__FILE__), "General Options", "Settings", "manage_options",  basename(__FILE__), 'oboxfb_general_options');
	$add_update_page = add_submenu_page(basename(__FILE__), "Update", "Update", "manage_options",  "obox-fb-upgrade", 'oboxfb_upgrade_options');
}
add_action('admin_menu', 'oboxfb_add_admin');

/****************************/
/* Add Localization Support */
load_plugin_textdomain( 'ocmx', false, dirname( plugin_basename( __FILE__ ) ) . '/admin/lang/' );