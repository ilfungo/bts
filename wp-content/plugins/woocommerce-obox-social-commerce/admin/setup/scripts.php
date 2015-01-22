<?php
function oboxfb_add_scripts()
	{
		global $themeid, $ocmx_oboxfb_class;

		//Add support for 2.9 and 3.0 functions and setup jQuery for theme
		if(is_admin()) :
			wp_enqueue_script("jquery");
			wp_enqueue_script( 'jquery-ui-draggable' );
			wp_enqueue_script( 'jquery-ui-droppable' );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'jquery-ui-tabs' );

			wp_enqueue_script( "ajaxupload", OBOXFBURL."admin/scripts/ajaxupload.js", array( "jquery" ) );

			if(strpos( $_SERVER['REQUEST_URI'], 'obox-fb' ) !== false) :
				wp_enqueue_script( "oboxfb-jquery", OBOXFBURL."admin/scripts/ocmx_jquery.js", array( "jquery" ) );
				wp_localize_script( "oboxfb-jquery", "oboxfb", array( "ajaxurl" => admin_url( "admin-ajax.php" ), "appid" => get_option("fb_appid"), "url" => get_site_url() ) );
				wp_enqueue_script( "mobile-upgrade", OBOXFBURL."admin/scripts/upgrade.js", array( "jquery" ) );
			endif;
		else :
			if($ocmx_oboxfb_class->allow_oboxfb() === true) :
				wp_enqueue_script( "oboxfb-jquery", OBOXFBURL."themes/default.js", array( "jquery" ) );
				wp_enqueue_script( "jquery.resize.end", OBOXFBURL."themes/".$ocmx_oboxfb_class->oboxfb_stylesheet()."/scripts/jquery.resize.end.js", array( "jquery" ) );
				wp_enqueue_script( "theme-jquery", OBOXFBURL."themes/".$ocmx_oboxfb_class->oboxfb_stylesheet()."/scripts/theme-jquery.js", array( "jquery" ) );
			endif;

			wp_localize_script( "oboxfb-jquery", "oboxfb", array( "ajaxurl" => admin_url( "admin-ajax.php" ), "path" => get_bloginfo("url") ) );
			wp_enqueue_script( "uniform", OBOXFBURL."admin/scripts/jquery.uniform.js", array( "jquery" ) );
			wp_enqueue_script( "validate", OBOXFBURL."admin/scripts/jquery.validate.min.js", array( "jquery" ) );
		endif;

		add_action( 'wp_ajax_oboxfb-cancel-session', 'oboxfb_cancel_session' );
		add_action( 'wp_ajax_nopriv_oboxfb-cancel-session', 'oboxfb_cancel_session' );
		add_action("wp_ajax_validate_key", "oboxfb_validate_key");
		add_action("wp_ajax_do_oboxfb_upgrade", "do_oboxfb_upgrade");
		add_action( 'wp_ajax_oboxfb_save-options', 'oboxfb_update_options');
		add_action( 'wp_ajax_oboxfb_reset-options', 'reset_oboxfb_options');
		add_action( 'wp_ajax_nopriv_oboxfb_ads-refresh', 'oboxfb_ads_refresh' );
		add_action( 'wp_ajax_oboxfb_ads-refresh', 'oboxfb_ads_refresh' );
		add_action( 'wp_ajax_oboxfb_ads-remove', 'oboxfb_ads_remove' );
		add_action( 'wp_ajax_oboxfb_ajax-upload', 'oboxfb_ajax_upload' );
		add_action( 'wp_ajax_oboxfb_theme-upload', 'oboxfb_theme_upload' );
		add_action( 'wp_ajax_oboxfb_theme-remove', 'oboxfb_theme_remove' );
		add_action( 'wp_ajax_oboxfb_remove-image', 'oboxfb_ajax_remove_image' );
	}
add_action("init", "oboxfb_add_scripts");