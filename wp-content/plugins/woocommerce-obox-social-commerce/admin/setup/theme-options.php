<?php
global $oboxfb_theme_options, $advert_areas;

$oboxfb_theme_options = array();

$oboxfb_theme_options["general_site_options"] =
	array(
		array(
			"main_section" => "Featured Slider",
			"main_description" => "These settings control the content that will be displayed in the feature slider. You can upload slider images and content.",
			"sub_elements" =>
				array(
					array("label" => "Enable", "description" => "", "name" => "oboxfb_slider", "default" => "no", "id" => "oboxfb_slider", "input_type" => "select", "options" => array("Yes" => "yes", "No" => "no")),
					array("label" => "Post Count", "description" => "", "name" => "oboxfb_slider_count", "default" => "5", "id" => "", "input_type" => "select", "options" => array("3" => "3", "6" => "6", "9" => "9")),
					array("label" => "Slider Duration", "description" => "", "name" => "oboxfb_slider_duration", "default" => "5", "id" => "oboxfb_slider_duration", "input_type" => "input"),
					array("label" => "Image Dimensions", "description" => "", "name" => "oboxfb_slider_dimensions", "default" => "620x300", "id" => "oboxfb_slider_dimensions", "input_type" => "select", "options" => array( "620 x 300 pixels" => "620x300" , "620 x 465 pixels" => "620x465" ) )
				)
			),
		array("label" => "Breadcrumbs", "description" => "Select whether or not you would like to display breadcrumbs on shop and list pages", "name" => "oboxfb_breadcrumbs", "default" => "no", "id" => "oboxfb_breadcrumbs", "input_type" => "select", "options" => array("Yes" => "yes", "No" => "no")),
		array(
			"main_section" => "Shop Introduction",
			"main_description" => "These settings control the introduction text on the home page of your store. You can add a title and some text.",
			"sub_elements" =>
				array(
					array("label" => "Enable", "description" => "", "name" => "oboxfb_intro", "default" => "no", "id" => "oboxfb_intro", "input_type" => "select", "options" => array("Yes" => "yes", "No" => "no")),
					array("label" => "Title", "description" => "Leave blank to have no title", "name" => "oboxfb_intro_title", "default" => "", "id" => "oboxfb_intro_title", "input_type" => "input"),
					array("label" => "Intro Text", "description" => "", "name" => "oboxfb_intro_text", "default" => "", "id" => "oboxfb_intro_text", "input_type" => "memo")

			)
		),
		array(
			"main_section" => "Product Display",
			"main_description" => "These settings control what type of product list you want on the home page. You can choose between listing featured products or specific product categories.",
			"sub_elements" =>
				array(
					array("label" => "Enable", "description" => "", "name" => "oboxfb_products", "default" => "yes", "id" => "oboxfb_products", "input_type" => "select", "options" => array("Yes" => "yes", "No" => "no")),
					array("label" => "Title", "description" => "Leave blank to have no title", "name" => "oboxfb_products_title", "default" => "Products", "id" => "oboxfb_products_title", "input_type" => "input"),
					array("label" => "Display", "description" => "", "name" => "oboxfb_products_category", "default" => "recent_products", "id" => "oboxfb_products_category", "input_type" => "select", "options" => array("Most Recent Products" => "recent_products", "Featured Products" => "feature_products", "Product Categories" => "product_categories")),
					array("label" => "Post Count", "description" => "Only if Posts selected above", "name" => "oboxfb_products_count", "default" => "4", "id" => "", "input_type" => "select", "options" => array("4" => "4", "8" => "8", "12" => "12"))
			)
		)
	);

$oboxfb_theme_options["customization_options"] = array(
			array("label" => "Upload Your Logo", "description" => "<strong>Recommended size 162px x 50px</strong><br />Upload your company logo here. You can either upload it from your PC or you can enter in a URL.", "name" => "oboxfb_custom_logo", "default" => "", "id" => "upload_button_logo", "input_type" => "file", "sub_title" => "mobile-logo"),
			array("label" => "Custom Sidebar Text", "description" => "Use this text area to enter in any copyrights or links to your main site. You can use HTML in this area.", "name" => "oboxfb_custom_footer", "default" => "<a href=\"http://www.obox-design.com/\">Obox Social Commerce</a> created by Obox Design", "id" => "oboxfb_custom_footer", "input_type" => "memo"),
			array("label" => "Custom CSS", "description" => "Enter changed classes from the theme stylesheet, or custom CSS here.", "name" => "oboxfb_custom_css", "default" => "", "id" => "ocmx_custom_css", "input_type" => "memo")

	);

$oboxfb_theme_options["sharing_options"] = array(
		array(
				"main_section" => "Facebook Sharing Options",
				"main_description" => "Set a default image URL to appear on Facebook shares if no featured image is found. Recommended size 200x200.",
				"sub_elements" =>
					array(
						array("label" => "Disable OpenGraph?", "description" => "Select No if you want to disable the plugin's OpenGraph support(do this only if not using our sharing buttons)", "name" => "ocmx_open_graph", "default" => "no", "id" => "ocmx_open_graph", "input_type" => 'select', 'options' => array('Yes' => 'yes', 'No' => 'no')
						),

						array("label" => "Image URL", "description" => "", "name" => "ocmx_site_thumbnail", "sub_title" => "Open Graph image", "default" => "", "id" => "upload_button_ocmx_site_thumbnail", "input_type" => "file", "args" => array("width" => 80, "height" => 80)
						)
					)
			),
		array("label" => "Social Sharing", "description" => "Display Social Sharing on products?", "name" => "oboxfb_social_meta", "default" => "yes", "id" => "oboxfb_social_meta", "input_type" => 'select', 'options' => array('Yes' => 'true', 'No' => 'false')),
		array("label" => "Social Widget Code", "description" => "Paste the template tag or code for your social sharing plugin here.", "name" => "oboxfb_social_tag", "default" => "", "id" => "", "input_type" => "memo"),
		array(
				"main_section" => "Press Trends Analytics",
				"main_description" => "Select Yes Opt out. No personal data is collected.",
				"sub_elements" =>
				array(
					array("label" => "Disable Press Trends?", "description" => "PressTrends helps Obox build better themes and provide awesome support by retrieving aggregated stats. PressTrends also provides a <a href='http://wordpress.org/extend/plugins/presstrends/' title='PressTrends Plugin for WordPress' target='_blank'>plugin for you</a> that delivers stats on how your site is performing against similar sites like yours. <a href='http://www.presstrends.me' title='PressTrends' target='_blank'>Learn more…</a>","name" => "ocmx_disable_press_trends", "default" => "no", "id" => "ocmx_disable_press_trends", "input_type" => 'select', 'options' => array('Yes' => 'yes', 'No' => 'no'))
				)
			)
	);

$appid = get_option("fb_appid");
$url = get_site_url();
$oboxfb_theme_options["setup_options"] =
	array(
		array("label" => "Step by Step Setup", "description" => "View our setup process in order to install the Facebook Commerce plugin onto your Facebook page, or view the <a href='https://kb.oboxsites.com/themedoc-category/social-commerce/' target='_blank'>Setup Documentation.</a>", "name" => "setup_video", "default" => "", "id" => "setup_video", "input_type" => "html", "html" => ""),


		array("label" => "Facebook App Setup", "description" => "First, create your Facebook AppID in order to setup the page on your Fanpage. <a target='_blank' class='admin-button' href='https://developers.facebook.com/'>Open Facebook Developers</a>", "name" => "setup_video", "default" => "https://developers.facebook.com/apps", "id" => "setup_video", "input_type" => "html", "html" => ""),
		array("label" => "Facebook AppID", "description" => "Now that you've created your AppID, paste it into the form here. <a target='_blank' id=\"fb_appid_link\" class='admin-button' href='https://www.facebook.com/dialog/pagetab?app_id=" . $appid . "&next=" . $url."/'>Add the Tab to Facebook</a>", "name" => "fb_appid", "default" => "", "id" => "fb_appid", "input_type" => "input"),
		array(
			"main_section" => "Force Social Site",
			"main_description" => "(Recommended for testing)",
			"sub_elements" => array(
				array("label" => "", "description" => "Set this option to 'yes' when you want to test your Social Commerce site outside of Facebook. Remember to turn it back to no when you're done otherwise your main site will look like Social Commerce when users visit it outside of Facebook!", "name" => "oboxfb_force", "default" => "no", "id" => "oboxfb_force", "input_type" => "select", "options" => array("Yes" => "yes", "No" => "no"))
			)
		)
);

?>