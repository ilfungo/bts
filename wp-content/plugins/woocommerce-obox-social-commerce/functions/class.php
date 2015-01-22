<?php class OBOXFB
{
	function oboxfb_template(){
		//Which theme are we hooking?
		if(isset($_GET["template"]) && $_GET["template"]) :
			$template = $_GET["template"];
		elseif(get_option("ocmx_oboxfb_theme")) :
			$template = get_option("ocmx_oboxfb_theme");
		else :
			$template = "default";
		endif;
		//If theme is invalid, change to default
		if(!file_exists($this->oboxfb_template_dir()."/".$template)) :
			$template = "default";
		endif;

		return $template;
	}
	function oboxfb_stylesheet(){
		//Which theme are we hooking?
		if(isset($_GET["stylesheet"]) && $_GET["stylesheet"]) :
			$template = $_GET["stylesheet"];
		elseif(get_option("ocmx_oboxfb_stylesheet")) :
			$template = get_option("ocmx_oboxfb_stylesheet");
		else :
			$template = "default";
		endif;
		//If theme is invalid, change to default
		if(!file_exists($this->oboxfb_template_dir()."/".$template)) :
			$template = "default";
		endif;
		return $template;
	}

	function oboxfb_template_dir(){
		$template_path = OBOXFBDIR."themes";
		return $template_path;
	}

	function oboxfb_template_uri(){
		$template_path = OBOXFBURL."themes";
		return $template_path;
	}

	function remove_plugins(){
		// Disable  common plugins which can cause unwanted behaviour with Obox Mobile, such as showing a cached version of  the wrong site! */

		// Facebook Like button
		remove_filter('the_content', 'Add_Like_Button','ocmx');

		//Sharebar Plugin
		remove_filter('the_content', 'sharebar_auto');
		remove_action('wp_head', 'sharebar_header');

		// Hyper Cache
		if ( function_exists( 'hyper_activate' ) ) {
			global $hyper_cache_stop;
			$hyper_cache_stop = true;
		}
	}

	function allow_oboxfb(){
		global $post;
		//Begin without allowing mobile
		$oboxfb = false;
		$browserAgent = "";
		// Current browser
		if(isset($_SERVER['HTTP_REFERER']))
			$browserAgent = $_SERVER['HTTP_REFERER'];

		// Check for the home page
		$currenturl = "http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

		// If we're in the checkout page and inside facebook, set a 5minute cookie so that the user gets the fb "order thank you page"
		if( strpos($currenturl, "checkout") || ( isset($_GET["page_id"]) && $_GET["page_id"] == get_option("woocommerce_checkout_page_id") ) ) :
			if (isset($_GET["obox-fb"])) :
				setcookie("obox-fb", true, time()+720);
			endif;
		endif;

		// If we land on the home page of the site outside of facebook, clear the cookie
		if(!headers_sent() && $currenturl == get_bloginfo("url")."/" && !isset($_GET["obox-fb"])) :
			setcookie("obox-fb", "", -720);
			$oboxfb = false;
			$cancel = 1;
		endif;

		// Check if we're inside facebook
		if(isset($_GET["obox-fb"]) || ( isset($_COOKIE["obox-fb"]) && !isset($cancel) ) ) :
			$oboxfb = true;
		endif;

		// Force FB Site
		if(get_option("oboxfb_force") == "yes") :
			$oboxfb = true;
		endif;

		return $oboxfb;
	}

	function allow_slider(){
		if(get_option("oboxfb_slider") == "true") :
			return true;
		else :
			return false;
		endif;
	}

	function site_style(){
		if($this->allow_oboxfb() === true) :
			if(isset($_GET["site_switch"]) && $_GET["site_switch"]) :
				$site_style = $_GET["site_switch"];
			else :
				$site_style = "oboxfb";
			endif;
		elseif(isset($_GET["preview"]) && $_GET["preview"] && $_GET["site_switch"]) :
			$site_style = $_GET["site_switch"];
		else :
			$site_style = "normal";
		endif;

		if(!headers_sent() && !isset($_GET["preview"])) :
			setcookie("OBOXFB", "", time() - 720, COOKIEPATH, COOKIE_DOMAIN);
			setcookie("OBOXFB", $site_style, 0, COOKIEPATH, COOKIE_DOMAIN);
		endif;

		return $site_style;
	}

	function obox_fb_add_redirect($location){

		if( strpos( $location, "?" ) ) :
			$seperator = "&";
		else :
			$seperator = "?";
		endif;

		if( strpos( $location, "#" ) ) :
			$hash = strpos( $location , "#");
			$start = substr( $location , 0 , $hash);
			$len = ( strlen( $location ) - $hash);
			$end = substr( $location , $hash, $len);
			$location = $start . $seperator . "obox-fb=1" . $end;
		elseif( !strpos( $location, "obox-fb") ) :
			$location .= $seperator . "obox-fb=1";
		endif;

		return $location;
	}

	function set_home_page(){
		//Make sure we're not using the WordPress static home page

		$root = $this->oboxfb_template_dir() . "/" . $this->oboxfb_template();
		if(get_option( 'show_on_front', false ) == 'page' && (is_home() || is_front_page())) :
			query_posts( 'post_type=post' );
		endif;
	}

	function initiate(){
		$site_style = $this->site_style();
		if ($site_style == "oboxfb" && strpos( $_SERVER['REQUEST_URI'] , '/wp-admin' ) === false) :
			add_filter( 'stylesheet', array( &$this, 'oboxfb_stylesheet') );
			add_filter( 'template', array( &$this, 'oboxfb_template') );
			add_filter( 'theme_root', array( &$this, 'oboxfb_template_dir') );
			add_filter( 'theme_root_uri', array( &$this, 'oboxfb_template_uri') );
			/*add_filter("woocommerce_get_cart_url", array( &$this, 'obox_fb_add_redirect') );
			add_filter("woocommerce_get_checkout_url", array( &$this, 'obox_fb_add_redirect') );*/
			add_filter("wp_redirect", array( &$this, 'obox_fb_add_redirect') );
			add_action( 'wp', array( &$this, 'set_home_page') );
		endif;
	}
}
?>