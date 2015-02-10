<?php
if (!defined('ABSPATH')) exit('No direct script access allowed');

 /**
 * CodeNegar WooCommerce Social Login helper class
 *
 * Contains common plugin methods
 *
 * @package    	WooCommerce Social Login
 * @author      Farhad Ahmadi <ahm.farhad@gmail.com>
 * @license     http://codecanyon.net/licenses
 * @link		http://codenegar.com/woocommerce-social-login/
 * @version    	1.0
 */
 
class CodeNegar_wcsl_helper{
	
	function __construct(){
		
	}
	
	/**
	* Converts string to int and makes sure string parameters are safe
	* @param string/int/array $input, user input value
	* @param boolean $is_int, force convert to int 
	* @return string/int safe parameter
	*/
	
	public function prepare_parameter($input, $is_int=false){
	
		if(is_array($input)){
			foreach($input as $key=>$value){
				if($is_int){
					$input[$key] = intval($value);
				}else{
					$input[$key] = trim(stripslashes(strip_tags($value)));
				}
			}
		}else{
			if($is_int){
				$input = intval($input);
			}else{
				$input = trim(stripslashes(strip_tags($input)));
			}
		}
		return $input;
	}
	
	/**
	* Return limit length of a Wordpress post
	* @param int $limit, number of maximum characters to return
	* @return string limited character of input
	*/
	
	public function limit_str($str, $limit=100) {
        $str = trim(strip_tags($str));
		$str = strip_shortcodes($str);
		$excerpt = mb_substr($str,0,$limit);
		if (strlen($excerpt)<strlen($str)) {
			$excerpt .= '...';
		}
		return $excerpt;
	}
	
	/**
	* Converts array to stdClass
	* @return stdClass of input
	*/
	
	public function array_to_object($input){
		if (is_array($input)) {
			return (object) array_map(array(&$this, 'array_to_object'), $input);
		}
		else {
			return $input;
		}
	}
	
	public function get_social_list(){
		global $codenegar_wcsl;
		$socials = array(
			'google' => __('Google', $codenegar_wcsl->text_domain),
			'facebook' => __('Facebook', $codenegar_wcsl->text_domain),
			'twitter' => __('Twitter', $codenegar_wcsl->text_domain),
			'yahoo' => __('Yahoo', $codenegar_wcsl->text_domain),
			'live' => __('Windows Live', $codenegar_wcsl->text_domain),
			'linkedin' => __('LinkedIn', $codenegar_wcsl->text_domain),
			'foursquare' => __('Foursquare', $codenegar_wcsl->text_domain),
			'aol' => __('AOL', $codenegar_wcsl->text_domain)
		);
		
		$sorted_socials = get_option('codenegar_wcsl_main');
		
		if(!$sorted_socials || !isset($sorted_socials['social_order'])  || count($sorted_socials['social_order'])!=8){
			return $socials;
		}
		$sorted_socials = $sorted_socials['social_order'];
		$return = array();
		for($i=0; $i<8; $i++){
			$return[$sorted_socials[$i]] = $socials[$sorted_socials[$i]];
		}
		return $return;
	}
	
	public function create_table(){
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$wcsl_users = $wpdb->prefix . "wcsl_users";
		
		$sql = "CREATE TABLE IF NOT EXISTS ".$wcsl_users."(
		  `ID` int(11) NOT NULL AUTO_INCREMENT,
		  `wp_user_id` int(11) NOT NULL,
		  `provider` varchar(15) NOT NULL,
		  `identifier` varchar(100) NOT NULL,
		  `profileURL` varchar(255) NOT NULL,
		  `photoURL` varchar(255) NOT NULL,
		  `displayName` varchar(50) NOT NULL,
		  `webSiteURL` varchar(255) NOT NULL,
		  `description` varchar(50) NOT NULL,
		  `firstName` varchar(50) NOT NULL,
		  `lastName` varchar(50) NOT NULL,
		  `gender` varchar(10) NOT NULL,
		  `language` varchar(20) NOT NULL,
		  `age` int(11) NOT NULL,
		  `birthDay` int(11) NOT NULL,
		  `birthMonth` int(11) NOT NULL,
		  `birthYear` int(11) NOT NULL,
		  `email` varchar(50) NOT NULL,
		  `phone` varchar(30) NOT NULL,
		  `address` varchar(255) NOT NULL,
		  `country` varchar(20) NOT NULL,
		  `region` varchar(20) NOT NULL,
		  `city` varchar(25) NOT NULL,
		  `zip` varchar(20) NOT NULL,
		  `ip` varchar(100) NOT NULL,
		  `agent` varchar(255) NOT NULL,
		  PRIMARY KEY (`ID`)
		)ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1"; 
		dbDelta($sql); 
	}
	
	public function set_defaults(){
		$options = array(
			'codenegar_wcsl_google',
			'codenegar_wcsl_facebook',
			'codenegar_wcsl_twitter',
			'codenegar_wcsl_yahoo',
			'codenegar_wcsl_live',
			'codenegar_wcsl_linkedin',
			'codenegar_wcsl_foursquare',
			'codenegar_wcsl_login_aol',
		);
		$defaults = array(
			'codenegar_wcsl_enabled' => 'no',
			'codenegar_wcsl_appid' => '',
			'codenegar_wcsl_appsecret' => '',
		);
		
		foreach($options as $option){
			$args = get_option($option);
			$merged = codenegar_parse_args($args, $defaults);
			update_option($option, $merged);
		}
		
		$args = get_option('codenegar_wcsl_main');
		$defaults = array(
			'social_login_text' => __('<p>Click on any social network to connect.</p>', 'woocommerce-social-login'), // codenegar_wcsl may not be available yet so we use hardcoded text domain
			'social_login_element_type'=> 'span',
			'social_login_redirect_to'=> 'checkout',
			'codenegar_wcsl_custom_url'=> '',
			'social_login_icon_size'=> '24px',
			'social_login_icons_display'=> 'after_login',
			'display_profile_page'=> 'yes',
			'display_my_account_page_after_from'=> 'yes',
			'display_my_account_page'=> 'yes',
			'social_login_error_text'=> ''
		);
		$merged = codenegar_parse_args($args, $defaults);
		update_option('codenegar_wcsl_main', $merged);
	}
	
	public function code_to_error($error_code, $network=''){
		global $codenegar_wcsl;
		switch($error_code){
			case 0 : 
				$this->display_error('unspecified_error', __('<strong>Error</strong>: Unspecified error. Transaction my not be completed.', $codenegar_wcsl->text_domain));
				break;
			case 1 :
				$this->display_error('configuration_error', __('<strong>Error</strong>: WooCommerce Social Login configuration error. Please check again.', $codenegar_wcsl->text_domain));
				break;
			case 2 :
				$this->display_error('provider_error', __('<strong>Error</strong>: Provider not properly configured.', $codenegar_wcsl->text_domain));
				break;
			case 3 :
				$this->display_error('unknown_provider', __('<strong>Error</strong>: Unknown or disabled provider.', $codenegar_wcsl->text_domain));
				break;
			case 4 :
				$this->display_error('missing_credentials', __('<strong>Error</strong>: Missing provider application credentials.', $codenegar_wcsl->text_domain));
				break;
			case 5 :
				$this->display_error('authentication_failed', __('<strong>Error</strong>: Authentication failed. The user has canceled the authentication or the provider refused the connection.', $codenegar_wcsl->text_domain));
				break;  
			case 6 :
				$this->display_error('profile_request', __('<strong>Error</strong>: User profile request failed. Most likely the user is not connected to the provider and he should to authenticate again.', $codenegar_wcsl->text_domain));
				break;
			case 7 :
				$this->display_error('not _connected', __('<strong>Error</strong>: User not connected to the provider.', $codenegar_wcsl->text_domain));
				break;
		}
	}
	
	public function start_login($network){
		global $wp, $wpdb, $codenegar_wcsl;
		require_once($codenegar_wcsl->path . 'library/Hybrid/Auth.php');
		$options = get_option("codenegar_wcsl_$network");
		if(!is_array($options) || count($options) == 0){
			return;
		}
		$hybridauth = new stdClass();
		try{
		// create an instance for Hybridauth with the configs
		$config = $this->get_network_config($network);
		if($config===false) return; // no config has been set
		$hybridauth = new Hybrid_Auth($config);
		
		// try to authenticate the user with the provider, 
		// user will be redirected to provider website for authentication, 
		// if he already did, then Hybridauth will ignore this step and return an instance of the adapter
		$adapter = $hybridauth->authenticate($network);
		$next = '';
		if(isset($_GET['next']) && !empty($_GET['next'])){
			$next = $_GET['next'];
		}
		$hybridauth->redirect($this->callback_url($network, $next));		
		}
		catch(Exception $e){
			$hybridauth = new Hybrid_Auth($config);
			// remove any previous sessions
			$hybridauth->logoutAllProviders();
			$this->code_to_error($e->getCode(), $network);
		}
	}
	
	public function save_login($network){
		global $wp, $wpdb, $codenegar_wcsl;//, $new_twitter_settings;
		
		if(!in_array($network, array_keys($this->get_social_list()))){
			$this->display_error('bad_provider', __('<strong>Error</strong>: Specified provider is not available currently or misspelled.', $codenegar_wcsl->text_domain));
		}
		
		require_once($codenegar_wcsl->path . 'library/Hybrid/Auth.php');
		$config = $this->get_network_config($network);
		if($config===false) return; // no config has been set
		$hybridauth = new Hybrid_Auth($config);
		
		if(!$hybridauth->isConnectedWith($network)){
			// remove any previous sessions
			$hybridauth->logoutAllProviders();
			$this->display_error('expired_session', __('<strong>Error</strong>: Your are not connected to the specified provider or your session has expired.', $codenegar_wcsl->text_domain));
		}
		
		// call back the requested provider adapter instance
		// new we know users is connected to selected network
		$adapter = $hybridauth->getAdapter($network);
		
		// grab the user profile
		$user_data = $adapter->getUserProfile();
		
		// no email but user is logged in
		if((!isset($user_data->email) || empty($user_data->email) || !is_email($user_data->email)) && is_user_logged_in()){
			// if no email provided and user is logged in get his email
			global $current_user;
			get_currentuserinfo();
			$user_data->email = $current_user->user_email;
		}
		if(!isset($user_data->email)) $user_data->email = '';
		$user_data->wcsl_email = $user_data->email;
		
		
		if(is_user_logged_in()){ // they are linking their account to a social nework
			global $current_user;
			get_currentuserinfo();
			$user_ID = $current_user->ID; // logged in wp user id
			if(intval($user_ID)>0){
				// save user social data
				$this->save_user($user_data, $user_ID, $network);
				$this->redirect(); // user is already logged just link his account to selected network
			}
		}
		
		// check if there is a previous login
		$wcsl_users = $wpdb->prefix . "wcsl_users";
		$query = $wpdb->prepare('SELECT * FROM ' . $wcsl_users . ' WHERE provider = "%s" AND identifier = "%s"', $network, $user_data->identifier);
		
		$row = $wpdb->get_results($query, OBJECT);
		
		if(($wpdb->num_rows)>0){ // user has been registered before
			$row=$row[0];
			$user_id = intval($row->wp_user_id);
			if($user_id>0){
				$this->login_user($user_id); // just login them in wordpress
			}
			$this->redirect();
		}
		
		if($wpdb->num_rows==0){ // new social user
		
			if( // we need email to create an account
			(!isset($user_data->email) || empty($user_data->email) || !is_email($user_data->email)) &&
			(!isset($_POST['wcsl_email']) || empty($_POST['wcsl_email']) || !is_email($_POST['wcsl_email']))
			){
				// some services such as twitter don't provide email address; So get it manually
				$this->get_email($network);
			}
			
			$user_data->wcsl_email = (isset($user_data->email) && is_email($user_data->email))? $user_data->email : $_POST['wcsl_email'];
			
			// user has an account but has signed out
			if(!is_user_logged_in() && email_exists($user_data->wcsl_email)){
				// tell user to login first
				// remove any previous sessions
				$hybridauth->logoutAllProviders();
				$this->display_error('email_exists', __('<strong>Error</strong>: Email already exists, If you have an account login first.', $codenegar_wcsl->text_domain));
			}
			
			// if email doesn't exists and user is not logged in
			if(!is_user_logged_in() && !email_exists($user_data->wcsl_email)){
				$wp_user_id = $this->add_user($network, $user_data);
				if($wp_user_id>0){
					$this->login_user($wp_user_id);
				}
				$this->redirect();
			}
		}
	}
	
	public function display_error($name, $text){
		global $codenegar_wcsl;
		$main_options = get_option('codenegar_wcsl_main');
		$text_msg = trim($main_options['social_login_error_text']);
		if(!empty($text_msg)){
			$text_msg = '<p class="message register wcsl_message">'.do_shortcode($text_msg).'</p>';
		}
		$errors = new WP_Error();
		$errors->add($name, $text);
		login_header(__('WooCommerce Social Login', $codenegar_wcsl->text_domain) , $text_msg, $errors);
		login_footer('user_login');
		exit;
	}
	
	public function get_email($network){
		global $codenegar_wcsl;
		$wcsl_email = '';
		$errors = new WP_Error();
		if(isset($_POST['wcsl_email'])){
		  $wcsl_email = $_POST['wcsl_email'];
		  if ($wcsl_email == '') {
			$errors->add('empty_email', __('<strong>Error</strong>: Enter your email address.', $codenegar_wcsl->text_domain));
		  } elseif (!is_email($wcsl_email)) {
			$errors->add('invalid_email', __('<strong>Error</strong>: The email address did not validate.', $codenegar_wcsl->text_domain));
			$wcsl_email = '';
		  } elseif (email_exists($wcsl_email)) {
			$errors->add('email_exists', __('<strong>Error</strong>: Email already exists, If you have an account login first.', $codenegar_wcsl->text_domain));
		  }
		  if (isset($_POST['wcsl_email']) && $errors->get_error_code() == '') {
			return $wcsl_email;
		  }
		}

		login_header(__('Registration Form', $codenegar_wcsl->text_domain) , '<p class="message register wcsl_message">' . __('Please enter your email address to complete registration.', $codenegar_wcsl->text_domain) . '</p>', $errors);
		?>
		<form name="registerform" id="registerform" action="<?php echo $this->callback_url($network); ?>" method="post">
			  <p>
					  <label for="wcsl_email"><?php _e('E-mail', $codenegar_wcsl->text_domain) ?><br />
					  <input type="email" name="wcsl_email" id="wcsl_email" class="input" value="<?php echo esc_attr(stripslashes($wcsl_email)); ?>" size="25" tabindex="20" /></label>
			  </p>
			  <p id="reg_passmail"><?php _e('Username and Password will be sent to your email.', $codenegar_wcsl->text_domain) ?></p>
			  <br class="clear" />
			  <p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button-primary" value="<?php esc_attr_e('Register', $codenegar_wcsl->text_domain); ?>" tabindex="100" /></p>
		</form>
		<?php
		login_footer('user_login');
		exit;
	}
	
	public function login_url($network, $next=''){
		$login_url = site_url('wp-login.php');
		$login_url = add_query_arg('social_login', 1, $login_url);
		$login_url = add_query_arg('social_network', $network, $login_url);
		if(!empty($next)>0){
			$login_url = add_query_arg('next', $next, $login_url);
		}
		return $login_url;
	}
	
	public function callback_url($network, $next=''){
		$callback_url = site_url('wp-login.php');
		$callback_url = add_query_arg('social_login_done', 1, $callback_url);
		$callback_url = add_query_arg('social_network', $network, $callback_url);
		if(!empty($next)>0){
			$callback_url = add_query_arg('next', $next, $callback_url);
		}
		return $callback_url;
	}
	
	public function error_url($network){
		$login_url = site_url('wp-login.php');
		$login_url = add_query_arg('social_login_error', 1, $login_url);
		$login_url = add_query_arg('social_network', $network, $login_url);
		return $login_url;
	}
	
	public function get_network_config($network){
		global $codenegar_wcsl;
		$yahoo_o = get_option("codenegar_wcsl_yahoo");
		$aol_o = get_option("codenegar_wcsl_aol");
		$google_o = get_option("codenegar_wcsl_google");
		$facebook_o = get_option("codenegar_wcsl_facebook");
		$twitter_o = get_option("codenegar_wcsl_twitter");
		$live_o = get_option("codenegar_wcsl_live");
		$linkedin_o = get_option("codenegar_wcsl_linkedin");
		$foursquare_o = get_option("codenegar_wcsl_foursquare");
		 
		$config = array(
			"base_url" => $codenegar_wcsl->url . "library/", 

			"providers" => array ( 
				// openid providers are disabled at this version
				"Yahoo" => array ( 
					"enabled" => ((isset($yahoo_o['codenegar_wcsl_enabled']) && (intval($yahoo_o['codenegar_wcsl_enabled'])==1 || $yahoo_o['codenegar_wcsl_enabled'] == 'yes'))? true: false),
					"keys"    => array ( "key"=>$yahoo_o['codenegar_wcsl_appid'], "secret" => $yahoo_o['codenegar_wcsl_appsecret'] ),
				),

				"AOL"  => array (
					"enabled" => ((isset($aol_o['codenegar_wcsl_enabled']) && (intval($aol_o['codenegar_wcsl_enabled'])==1 || $aol_o['codenegar_wcsl_enabled'] == 'yes'))? true: false),
				),

				"Google" => array ( 
					"enabled" => ((isset($google_o['codenegar_wcsl_enabled']) && (intval($google_o['codenegar_wcsl_enabled'])==1 || $google_o['codenegar_wcsl_enabled'] == 'yes'))? true: false),
					"keys"    => array ( "key" => $google_o['codenegar_wcsl_appid'], "id" => $google_o['codenegar_wcsl_appid'], "secret" => $google_o['codenegar_wcsl_appsecret'] ), 
				),

				"Facebook" => array ( 
					"enabled" => ((isset($facebook_o['codenegar_wcsl_enabled']) && (intval($facebook_o['codenegar_wcsl_enabled'])==1 || $facebook_o['codenegar_wcsl_enabled'] == 'yes'))? true: false),
					"keys"    => array ( "key" => $facebook_o['codenegar_wcsl_appid'], "id" => $facebook_o['codenegar_wcsl_appid'], "secret" => $facebook_o['codenegar_wcsl_appsecret'] ), 
				),

				"Twitter" => array ( 
					"enabled" => ((isset($twitter_o['codenegar_wcsl_enabled']) && (intval($twitter_o['codenegar_wcsl_enabled'])==1 || $twitter_o['codenegar_wcsl_enabled'] == 'yes'))? true: false),
					"keys"    => array ( "key" => $twitter_o['codenegar_wcsl_appid'], "secret" => $twitter_o['codenegar_wcsl_appsecret'] ) 
				),

				// windows live
				"Live" => array ( 
					"enabled" => ((isset($live_o['codenegar_wcsl_enabled']) && (intval($live_o['codenegar_wcsl_enabled'])==1 || $live_o['codenegar_wcsl_enabled'] == 'yes'))? true: false),
					"keys"    => array ( "id" => $live_o['codenegar_wcsl_appid'], "secret" => $live_o['codenegar_wcsl_appsecret'] ) 
				),

				"LinkedIn" => array ( 
					"enabled" => ((isset($linkedin_o['codenegar_wcsl_enabled']) && (intval($linkedin_o['codenegar_wcsl_enabled'])==1 || $linkedin_o['codenegar_wcsl_enabled'] == 'yes'))? true: false),
					"keys"    => array ( "key" => $linkedin_o['codenegar_wcsl_appid'], "secret" => $linkedin_o['codenegar_wcsl_appsecret'] ) 
				),

				"Foursquare" => array (
					"enabled" => ((isset($foursquare_o['codenegar_wcsl_enabled']) && (intval($foursquare_o['codenegar_wcsl_enabled'])==1 || $foursquare_o['codenegar_wcsl_enabled'] == 'yes'))? true: false),
					"keys"    => array ( "key" => $foursquare_o['codenegar_wcsl_appid'], "id" => $foursquare_o['codenegar_wcsl_appid'], "secret" => $foursquare_o['codenegar_wcsl_appsecret'] ) 
				)
			),

			// if you want to enable logging, set 'debug_mode' to true  then provide a writable file by the web server on "debug_file"
			"debug_mode" => false,
			"debug_file" => "",
		);
		return $config;
		
	}
	
	public function login_user($user_id){
		// set the WP login cookie
		$secure_cookie = is_ssl() ? true : false;
		wp_set_auth_cookie( $user_id, true, $secure_cookie );
		
		// Set the global user object
		$current_user = get_user_by ('id', $user_id);
	}
	
	public function add_user($network, $user_data){
		global $wpdb;
		$prefix = 'user_';
		$rand_username = $prefix . wp_rand(100, 9999999);
		while (username_exists($rand_username)){ // avoid duplicate user name
			$rand_username = $prefix . wp_rand(100, 9999999);
		}
		$rand_password = wp_generate_password(12, false);
		$email = $user_data->wcsl_email; // added manually
		$wp_user_id = wp_create_user($rand_username, $rand_password, $email);
		$user_info = array(
			'ID' => $wp_user_id,
			'role' => 'customer'
		);
		
		if(isset($user_data->webSiteURL) && !empty($user_data->webSiteURL)){ $user_info['user_url'] = $user_data->webSiteURL; }
		if(isset($user_data->displayName) && !empty($user_data->displayName)){ $user_info['display_name'] = $user_data->displayName; }
		if(isset($user_data->firstName) && !empty($user_data->firstName)){ $user_info['first_name'] = $user_data->firstName; }
		if(isset($user_data->lastName) && !empty($user_data->lastName)){ $user_info['last_name'] = $user_data->lastName; }
		if(isset($user_data->description) && !empty($user_data->description)){ $user_info['description'] = $user_data->description; }
		wp_update_user( $user_info); // add user information to newley created account
		
		$this->save_user($user_data, $wp_user_id, $network);
		
		// save woocommerce checkout form information
		if(isset($user_data->firstName)) add_user_meta($wp_user_id, 'billing_first_name', $user_data->firstName, true);
		if(isset($user_data->firstName)) add_user_meta($wp_user_id, 'shipping_first_name', $user_data->firstName, true);
		if(isset($user_data->lastName)) add_user_meta($wp_user_id, 'billing_last_name', $user_data->lastName, true);
		if(isset($user_data->lastName)) add_user_meta($wp_user_id, 'shipping_last_name', $user_data->lastName, true);
		if(isset($user_data->country)) add_user_meta($wp_user_id, 'billing_country', $user_data->country, true);
		if(isset($user_data->country)) add_user_meta($wp_user_id, 'shipping_country', $user_data->country, true);
		//if(isset($user_data->company)) add_user_meta($wp_user_id, 'billing_company', $user_info['first_name'], true);
		//if(isset($user_data->company)) add_user_meta($wp_user_id, 'shipping_company', $user_info['first_name'], true);
		if(isset($user_data->address)) add_user_meta($wp_user_id, 'billing_address_1', $user_data->address, true);
		if(isset($user_data->address)) add_user_meta($wp_user_id, 'shipping_address_1', $user_data->address, true);
		if(isset($user_data->city)) add_user_meta($wp_user_id, 'billing_city', $user_data->city, true);
		if(isset($user_data->city)) add_user_meta($wp_user_id, 'shipping_city', $user_data->city, true);
		if(isset($user_data->zip)) add_user_meta($wp_user_id, 'billing_postcode', $user_data->zip, true);
		if(isset($user_data->zip)) add_user_meta($wp_user_id, 'shipping_postcode', $user_data->zip, true);
		if(isset($user_data->phone)) add_user_meta($wp_user_id, 'billing_phone', $user_info['first_name'], true);
		add_user_meta($wp_user_id, 'billing_email', $email, true);
		
		// send the user a confirmation and their login details; woocommerce custom mail
		//$mailer = $woocommerce->mailer();
		//$mailer->customer_new_account( $this->customer_id, $user_pass );
		
		// send password by email via wordpress
		wp_new_user_notification($wp_user_id, $rand_password);
		return $wp_user_id;
	}
	
	public function save_user($user_data, $wp_user_id, $network){
		global $wpdb;
		// save wp_user_id to database
		$wcsl_users = $wpdb->prefix . "wcsl_users";
		$new_record = array(
			'wp_user_id' => $wp_user_id,
			'provider' => strtolower($network),
			'identifier' => $user_data->identifier,
			'ip' => preg_replace('/[^0-9a-fA-F:., ]/', '',$_SERVER['REMOTE_ADDR']),
			'agent' => substr($_SERVER['HTTP_USER_AGENT'], 0, 254)
		);
		if(isset($user_data->profileURL)) $new_record['profileURL'] = $user_data->profileURL;
		if(isset($user_data->photoURL)) $new_record['photoURL'] = $user_data->photoURL;
		if(isset($user_data->displayName)) $new_record['displayName'] = $user_data->displayName;
		if(isset($user_data->webSiteURL)) $new_record['webSiteURL'] = $user_data->webSiteURL;
		if(isset($user_data->description)) $new_record['description'] = $user_data->description;
		if(isset($user_data->firstName)) $new_record['firstName'] = $user_data->firstName;
		if(isset($user_data->lastName)) $new_record['lastName'] = $user_data->lastName;
		if(isset($user_data->gender)) $new_record['gender'] = $user_data->gender;
		if(isset($user_data->language)) $new_record['language'] = $user_data->language;
		if(isset($user_data->age)) $new_record['age'] = $user_data->age;
		if(isset($user_data->birthDay)) $new_record['birthDay'] = $user_data->birthDay;
		if(isset($user_data->birthMonth)) $new_record['birthMonth'] = $user_data->birthMonth;
		if(isset($user_data->birthYear)) $new_record['birthYear'] = $user_data->birthYear;
		if(isset($user_data->wcsl_email)) $new_record['email'] = $user_data->wcsl_email;
		if(isset($user_data->phone)) $new_record['phone'] = $user_data->phone;
		if(isset($user_data->address)) $new_record['address'] = $user_data->address;
		if(isset($user_data->country)) $new_record['country'] = $user_data->country;
		if(isset($user_data->region)) $new_record['region'] = $user_data->region;
		if(isset($user_data->city)) $new_record['city'] = $user_data->city;
		if(isset($user_data->zip)) $new_record['zip'] = $user_data->zip;
		$query = $wpdb->insert($wcsl_users,$new_record);
		
		// save wcsl data as meta data
		add_user_meta($wp_user_id, '_wcsl_user_id', $wpdb->insert_id, true);
		add_user_meta($wp_user_id, '_wcsl_provider', $network, true);
	}
	
	public function redirect(){
		if(isset($_GET['next']) && !empty($_GET['next'])){
			wp_redirect(urldecode($_GET['next']));
			exit;
		}
		$main_options = get_option('codenegar_wcsl_main');
		$to = $main_options['social_login_redirect_to'];
		if(strlen($to)>0 && $to !="custom"){
			$page_id = woocommerce_get_page_id($to);
			if(intval($page_id)>0){
				$url = get_permalink($page_id);
			}else{
				$url = home_url('/');
			}
		}elseif($to =="custom"){
			$url = $main_options['codenegar_wcsl_custom_url'];
		}
		
		wp_redirect(get_permalink($page_id));
		exit;
	}
	
	public function count_logged($network){
		global $wpdb;
		$wcsl_users = $wpdb->prefix . "wcsl_users";
		$user_count = $wpdb->get_var("SELECT COUNT(*) FROM $wcsl_users where provider='$network'");
		return intval($user_count);
	}
	
	/**
	* Registers a shortcode for using widget anywhere
	*/
	
	public function shortcode(){
		global $codenegar_wcsl;
		ob_start();
		?>
			<form method="post" class="login codenegar_social_login" style="display: block;">
			<?php
			$codenegar_wcsl->html->icons();
			?>
			<div class="clear"></div>
			</form>
		<?php
		return ob_get_clean();
	}
	
	/**
	* Registers a shortcode for using widget anywhere
	*/
	
	public function register_shortcode(){
		add_shortcode("woocommerce_social_login", array(&$this, 'shortcode'));
	}
	
	public function added_socials_list(){
		global $wpdb;
		$return = array();
		if(!is_user_logged_in()){
			return $return; // empty array
		}
		
		global $current_user;
		get_currentuserinfo();
		$user_ID = $current_user->ID; // logged in wp user id
		if(isset($user_ID) && intval($user_ID)>0){
			// check if there is a previous login
			$wcsl_users = $wpdb->prefix . "wcsl_users";
			$query = 'SELECT provider FROM ' . $wcsl_users . ' WHERE wp_user_id = '.$user_ID;
			$rows = $wpdb->get_results($query, OBJECT);
			
			if(($wpdb->num_rows)>0){ // user has been registered before
				foreach($rows as $row){
					$return[] = $row->provider;
				}
			}
			return $return;
		}
		
	}
}
?>