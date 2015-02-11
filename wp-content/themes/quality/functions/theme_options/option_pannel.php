<?php
add_action('admin_menu', 'webriti_admin_menu_pannel');  
function webriti_admin_menu_pannel()
 {	$page=add_theme_page( __('theme','quality'), __('Option Panel','quality'), 'edit_theme_options', 'webriti', 'webriti_option_panal_function' ); 
 	add_action('admin_print_styles-'.$page, 'webriti_admin_enqueue_script');
 }
function webriti_admin_enqueue_script()
{		
	wp_enqueue_script('quality_tab',get_template_directory_uri().'/functions/theme_options/js/option-panel-js.js',array('media-upload','jquery-ui-sortable'));	
	wp_enqueue_script('quality_tab1',get_template_directory_uri().'/functions/theme_options/js/bootstrap-modal.js');
	wp_enqueue_style('thickbox');	
	wp_enqueue_style('quality_option',get_template_directory_uri().'/functions/theme_options/css/style-option.css');
	wp_enqueue_style('quality_option-panel',get_template_directory_uri().'/functions/theme_options/css/qualityoption_pannel.css');
	wp_enqueue_style('quality_option-panel3',get_template_directory_uri().'/functions/theme_options/css/busiprof-bootstrap.css');
}
function webriti_option_panal_function()
{	require_once('webriti_option_pannel.php'); }
?>