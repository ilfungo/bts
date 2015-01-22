<?php
function oboxfb_general_options (){
	$ocmx_tabs = array(
					array(
						  "option_header" => "Home Page",
						  "use_function" => "fetch_oboxfb_options",
						  "function_args" => "general_site_options",
						  "ul_class" => "admin-block-list clearfix"
					),
					array(
						  "option_header" => "Customization",
						  "use_function" => "fetch_oboxfb_options",
						  "function_args" => "customization_options",
						  "ul_class" => "admin-block-list clearfix"
					),
					array(
						  "option_header" => "Social &amp; Sharing",
						  "use_function" => "fetch_oboxfb_options",
						  "function_args" => "sharing_options",
						  "ul_class" => "admin-block-list clearfix"
					  ),
					array(
						"option_header" => "Setup",
						"use_function" => "fetch_oboxfb_options",
						"function_args" => "setup_options",
						  "ul_class" => "admin-block-list clearfix"
					)
				);
	$ocmx_container = new OCMX_oboxfb_Container();
	$ocmx_container->load_container("General Options", $ocmx_tabs, "Save Changes");
};

function oboxfb_image_options (){
	$ocmx_tabs = array(
					array(
						"option_header" => "Logo &amp; Images",
						"use_function" => "fetch_oboxfb_options",
						"function_args" => "image_options",
						  "ul_class" => "admin-block-list clearfix"
					  )
				);
	$ocmx_container = new OCMX_oboxfb_Container();
	$ocmx_container->load_container("Logo &amp; Images", $ocmx_tabs, "Save Changes", $note);
};
function oboxfb_theme_options (){
	$ocmx_tabs = array(
					array(
						"option_header" => "Themes",
						"use_function" => "oboxfb_theme_list",
						"function_args" => "",
						"ul_class" => "clearfix",
					  	"base_button" => array("id" => "theme-list-edit-1", "rel" => "", "href" => "#", "html" => "Edit List"),
					  	"top_button" => array("id" => "theme-list-edit", "rel" => "", "href" => "#", "html" => "Edit List")
					  )
				);
	$note = "We recommend that you use a WebKit browser, such as Google Chrome or Safari to preview themes.";
	$ocmx_container = new OCMX_oboxfb_Container();
	$ocmx_container->load_container("Themes", $ocmx_tabs, "", $note);
};
function oboxfb_advert_options(){
	global $advert_areas;
	$ocmx_tabs = array();
	foreach($advert_areas as $ad_area => $option) :
		array_unshift($ocmx_tabs,
					array(
						  "option_header" => $ad_area,
						  "use_function" => "fetch_oboxfb_options",
						  "function_args" => $option."_adverts",
						  "ul_class" => "admin-block-list advert clearfix"
					  )
				);
	endforeach;

	$ocmx_container = new OCMX_oboxfb_Container();
	$ocmx_container->load_container("Adverts", $ocmx_tabs);
};
function oboxfb_plugin_options (){
	$ocmx_tabs = array(
					array(
						"option_header" => "Plugin Compatibility",
						"use_function" => "fetch_oboxfb_options",
						"function_args" => "plugins",
						"ul_class" => "admin-block-list clearfix"
					  )
				);
	$ocmx_container = new OCMX_oboxfb_Container();
	$ocmx_container->load_container("Plugin Compatibility", $ocmx_tabs, "Save Changes");
};

function oboxfb_upgrade_options (){
	$ocmx_tabs = array(
					array(
						 "option_header" => "Update Plugin",
						  "use_function" => "oboxfb_upgrade_license_options",
						  "function_args" => "",
						  "ul_class" => "admin-block-list clearfix"
					  )
				);
	$ocmx_container = new OCMX_oboxfb_Container();
	$ocmx_container->load_container("Update Plugin", $ocmx_tabs, "");
};

?>