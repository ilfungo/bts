<?php function oboxfb_add_theme_scripts()
	{
		global $woocommerce;

		if(!is_admin()) :
			wp_enqueue_script("jquery");
			wp_enqueue_script( "resize-end", get_bloginfo("template_directory")."/scripts/jquery.resize.end.js", array( "jquery" ) );
			wp_enqueue_script( "superfish", get_bloginfo("template_directory")."/scripts/superfish.js", array( "jquery" ) );
			wp_enqueue_script( "theme-jquery", get_bloginfo("template_directory")."/scripts/theme-jquery.js", array( "jquery" ) );
			if(isset($woocommerce))
				wc_enqueue_js('jQuery(document).ajaxSuccess(function(event,request,settings){
							var legalJSON = request.responseText;
							// Get the valid JSON only from the returned string
							if ( legalJSON.indexOf("<!--WC_START-->") >= 0 )
								legalJSON = legalJSON.split("<!--WC_START-->")[1]; // Strip off before after WC_START

							if ( legalJSON.indexOf("<!--WC_END-->") >= 0 )
								legalJSON = legalJSON.split("<!--WC_END-->")[0]; // Strip off anything after WC_END

							data = jQuery.parseJSON(legalJSON);
							if(data.result == "success") {

								setTimeout(function(){window.location = "#";}, 999999);
								if(jQuery.browser.msie || jQuery.browser.mozilla)
									{Screen = jQuery("html");}
								else
									{Screen = jQuery("body");}
								Screen.animate({scrollTop: 0});

								jQuery.blockUI({
									message: "<img src=\"' . esc_url( apply_filters( 'woocommerce_ajax_loader_url', $woocommerce->plugin_url() . '/assets/images/ajax-loader@2x.gif' ) ) . '\" alt=\"Redirecting&hellip;\" style=\"float:left; margin-right: 10px;\" />'.__('Please complete your order in the new window which will pop-up shortly. If a pop-up does not appear, click <a href=\""+data.redirect+"\" target=\"_blank\">here</a> to continue.', 'woocommerce').'",
									overlayCSS:
									{
										background: "#fff",
										opacity: 0.6
									},
									css: {
										top:            "40%",
								        padding:        20,
								        textAlign:      "center",
								        color:          "#555",
								        border:         "3px solid #aaa",
								        backgroundColor:"#fff",
								        cursor:         "wait",
								        lineHeight:		"32px"
								    }
								});
								window.open(data.redirect, "width=800,height=640");
								jQuery("#blockUI").scrollIntoView();
							}

						});');

			//Localization
			wp_localize_script( "theme-jquery", "ThemeAjax", array( "ajaxurl" => admin_url( "admin-ajax.php" ) ) );
		endif;
	};
add_action('init', 'oboxfb_add_theme_scripts');

function oboxfb_deregister_scripts(){
	global $ocmx_oboxfb_class;
	if($ocmx_oboxfb_class->site_style() == 'oboxfb') :
		// Remove Woo Lightbox scripts
		wp_dequeue_script('fancybox');
		wp_dequeue_style('woocommerce_fancybox_styles');
	endif;
};
add_action('wp_enqueue_scripts', 'oboxfb_deregister_scripts', 25);
