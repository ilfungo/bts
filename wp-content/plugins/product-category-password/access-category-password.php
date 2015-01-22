<?php
/*
Plugin Name: Product Category Password
Text Domain: pcpwp
Plugin URI: http://www.federicoporta.com
Description: Protects products categories by setting a Password for the wanted restricted category.
Author: Federico Porta
Version: 1.0
Author URI: http://www.federicoporta.com
*/

/**
 * Language init
 */
function pcpwp_lang_init() {
 load_plugin_textdomain( 'pcpwp', false, basename(dirname(__FILE__)) );
}
add_action('plugins_loaded', 'pcpwp_lang_init');


/** The info field  - questa funzione mostra */
if( !function_exists('pcpwp_func_info_message'))  {
	function pcpwp_func_info_message(){
	/* Get the option value from the database. */
		$options = get_option( 'pcpwp_settings_options' );
		$info_option = ($options['info_message'] != '') ? $options['info_message'] : __('This content has restricted access, please type the password below and get access.', 'pcpwp');
		/* Echo the field. */ ?>
		<textarea style="width: 50%; height: 100px;" id="info_message" name="pcpwp_settings_options[info_message]"><?php echo esc_attr($info_option); ?></textarea>
		<p class="description">
		    <?php _e( 'The message displayed before the password form replacing the content (and the excerpt) of the article (HTML formating with allowed tags).', 'pcpwp' ); ?><br>
		    <?php echo '<strong>'.__('Allowed tags:', 'pcpwp').'</strong> '. allowed_tags() ?>
        </p>
	<?php }
}

/** The error mesage **/
if( !function_exists('pcpwp_func_error_message'))  {
	function pcpwp_func_error_message(){
	/* Get the option value from the database. */
		$options = get_option( 'pcpwp_settings_options' );
		$error_message = ($options['error_message'] != '') ? $options['error_message'] : __('Sorry, but this is the wrong password.', 'pcpwp');	
		/* Echo the field. */ ?>
		<input style="width: 95%;" type="text" id="message_error_option" name="pcpwp_settings_options[error_message]" value="<?php echo esc_attr($error_message); ?>" />
		<p class="description">
		    <?php _e( 'The message that will display if the user typed the wrong password (HTML formating with allowed tags).', 'pcpwp' ) ?><br>
		    <?php echo '<strong>'.__('Allowed tags:', 'pcpwp').'</strong> '. allowed_tags() ?>
        </p>
    <?php }
}

/** The feed item description text **/
if( !function_exists('pcpwp_func_feed_desc_text'))  {
	function pcpwp_func_feed_desc_text(){
	/* Get the option value from the database. */
		$options = get_option( 'pcpwp_settings_options' );
		$feed_desc_text_option = ($options['feed_desc_text'] != '') ? $options['feed_desc_text'] : __('Access to this post restricted, please go to the website to read it.', 'pcpwp');	
		/* Echo the field. */ ?>
		<input type="text" style="width: 95%;" id="feed_desc_text" name="pcpwp_settings_options[feed_desc_text]" value="<?php echo stripslashes($feed_desc_text_option); ?>" />
		<p class="description">
		    <?php _e( 'The feed item descriptions that belong to access restricted posts will be replaced by this sentence (HTML formating not allowed).', 'pcpwp' ) ?>
        </p>
    <?php }
}

/**
 * Sanitize and validate input. Accepts an array, return a sanitized array.
 */
if( !function_exists('pcpwp_options_validate'))  {
	function pcpwp_options_validate( $input ) {
	$options = get_option( 'pcpwp_settings_options' );
	
    /** Password crypting */
    if ($input['password'] != '')
	    $options['password'] = crypt($input['password'], $input['password']);
	
	/** Impacted Categories	validation **/
	$options['impacted_categories'] = $input['impacted_categories'];
		
	/** clean info field, HTML allowed for the format */
	$options['info_message'] = wp_filter_kses( $input['info_message'] );
	
	/** clean error message field, HTML allowed for the format */
	$options['error_message'] = wp_filter_kses( $input['error_message'] );
	
	/** clean feed desc text HTML not allowed */
	$options['feed_desc_text'] = wp_filter_nohtml_kses( $input['feed_desc_text'] );

	return $options;	
	}
}

/* ******************************* */
/* Frontend of the plugin          */
/* ******************************* */

/* Start and destroy sessions */
add_action('init', 'myStartSession', 1);
add_action('wp_logout', 'myEndSession');
add_action('wp_login', 'myEndSession');

function myStartSession() {
    if (!session_id()) {
        session_start();
    }
}

function myEndSession() {
    session_destroy ();
}

/* Validation of the password */
function pcpwp_session_check() {
    // The form has been submited
    if(isset($_POST['pass'])) {
	    // Checking password
	    $pcpwp_options = get_option('pcpwp_settings_options');
        if(crypt($_POST['pass'], $_POST['pass']) == $pcpwp_options['password']) {
            $_SESSION['pcpwp_session'] = 1;
        }
        elseif (crypt($_POST['pass'], $_POST['pass']) != $pcpwp_options['password']) {
        	$_POST['msg'] = ($pcpwp_options['error_message'] != '') ? '<p style="color: darkred;">'.$pcpwp_options['error_message'].'</p>' : '<p style="color: darkred;">'.__('Sorry, but this is the wrong password.', 'pcpwp').'</p>';
        	$_SESSION['pcpwp_session'] = 0;
        }
    }
}
add_action('init', 'pcpwp_session_check', 2);

/* Displaying the password form or the feed replacement sentence */
function pcpwp_frontend_changes($content) {
    $pcpwp_options = get_option('pcpwp_settings_options');
	if ( in_category($pcpwp_options['impacted_categories']) ) {
        if (isset($_SESSION['pcpwp_session']) && $_SESSION['pcpwp_session'] == 1) {
            $content = $content;
        } else { 
            if (is_feed()) {
                // Feed content replacement
                $content = stripslashes( $pcpwp_options['feed_desc_text'] );
            } else {
                // Post or excerpt content replacement
                $content = (isset($options['info_message']) && $options['info_message'] != '') ? '<p>'.$options['info_message'].'</p>' : '<p>'.__('This content has restricted access, please type the password below and get access.', 'pcpwp').'</p>';
                $content .= '
                <form name="login" action="'.$_SERVER['REQUEST_URI'].'" method="post"> 
                    <input type="password" name="pass"> <input type="submit" value="'.__('Get access', 'pcpwp').'">
                </form>'; 
                if (isset ($_POST['msg'])) $content .= $_POST['msg'];
            }
        }
    }
	return $content;
}
add_filter( 'the_content', 'pcpwp_frontend_changes' );
add_filter( 'get_the_excerpt', 'pcpwp_frontend_changes' );


/*=========================questa roba qui non interessa=================================*/


/* ******************************* */
/* Backend of the plugin (options) */
/* ******************************* */

//add_action( 'admin_menu', 'pcpwp_options_add_page' );
/**
 * Load up the options page
 */
if( !function_exists('pcpwp_options_add_page'))  {
	function pcpwp_options_add_page() {
		add_options_page( 
			__( 'Product Category Password', 'pcpwp' ), // Title for the page
			__( 'Product Category Password', 'pcpwp' ), //  Page name in admin menu
			'manage_options', //  Minimum role required to see the page
			'pcpwp_options_page', // unique identifier
			'pcpwp_options_do_page'  // name of function to display the page
		);
		add_action( 'admin_init', 'pcpwp_options_settings' );	
	}
}
/**
 * Create the options page
 */

if( !function_exists('pcpwp_options_do_page'))  {
	function pcpwp_options_do_page() { ?>

<div class="wrap">

        <h2><?php _e( 'Product Category Password Options', 'pcpwp' ) ?></h2>  
        
        <?php 
        /*** To debug, here we can print the plugin options **/
        /* 
        echo '<pre>';
        $options = get_option( 'pcpwp_settings_options' );
        print_r($options); 
        echo '</pre>';
        */
         ?>
        
        <form method="post" action="options.php">
        		<?php settings_fields( 'pcpwp_settings_options' ); ?>
		  	<?php do_settings_sections('pcpwp_setting_section'); ?>
		  	<p><input class="button-primary"  name="Submit" type="submit" value="<?php esc_attr_e(__('Save Changes','pcpwp')); ?>" /></p>		
        </form>
        
</div>

<?php
	} // end pcpwp_options_do_page
}

/**
 * Init plugin options to white list our options
 */
if( !function_exists('pcpwp_options_settings'))  {
	function pcpwp_options_settings(){
		/* Register pcpwp settings. */
		register_setting( 
			'pcpwp_settings_options',  //$option_group , A settings group name. Must exist prior to the register_setting call. This must match what's called in settings_fields()
			'pcpwp_settings_options', // $option_name The name of an option to sanitize and save.
			'pcpwp_options_validate' // $sanitize_callback  A callback function that sanitizes the option's value.
        );

		/** Add a section **/
		add_settings_section(
			'pcpwp_option_main', //  section name unique ID
			'&nbsp;', // Title or name of the section (to be output on the page), you can leave nbsp here if not wished to display
			'pcpwp_option_section_text',  // callback to display the content of the section itself
			'pcpwp_setting_section' // The page name. This needs to match the text we gave to the do_settings_sections function call 
        );

		/** Register each option **/
		add_settings_field(
			'password',  //$id a unique id for the field 
			__( 'The password', 'pcpwp' ), // the title for the field 
			'pcpwp_func_password',  // the function callback, to display the input box 
			'pcpwp_setting_section',  // the page name that this is attached to (same as the do_settings_sections function call).  
			'pcpwp_option_main' // the id of the settings section that this goes into (same as the first argument to add_settings_section). 
        ); 
		
		add_settings_field(
			'impacted_categories', 
			__( 'Impacted categories', 'pcpwp' ), 
			'pcpwp_func_impacted_categories', 
			'pcpwp_setting_section',  
			'pcpwp_option_main' 
        ); 
        
             
        add_settings_field(
			'info_message',
			__( 'Info message', 'pcpwp' ),
			'pcpwp_func_info_message',
			'pcpwp_setting_section',
			'pcpwp_option_main'
        );
        
        add_settings_field(
			'error_message',
			__( 'Error message', 'pcpwp' ),
			'pcpwp_func_error_message',
			'pcpwp_setting_section',
			'pcpwp_option_main'
        );
        
        add_settings_field(
			'feed_desc_text',
			__( 'The feed item description text', 'pcpwp' ),
			'pcpwp_func_feed_desc_text',
			'pcpwp_setting_section',
			'pcpwp_option_main'
        );
    }
}

/** the theme section output**/
if( !function_exists('pcpwp_option_section_text'))  {
	function pcpwp_option_section_text(){
	echo '<p>'.__( 'Here you can set the options of Product Category Password plugin. Set a password, check the categories with restricted access (the posts in these categories will require a password authentication) and define the info and error messages displayed on frontend page. Finally, give a name (slug) to your new feed and the default item description for restricted posts.', 'pcpwp' ).'</p>';
	}
}

/** The password field **/
if( !function_exists('pcpwp_func_password'))  {
	function pcpwp_func_password() {
		 /* Get the option value from the database. */
		$options = get_option( 'pcpwp_settings_options' );
		$password = ($options['password'] != '') ? '' : 'pcpwppass' ;
		/* Echo the field. */ ?>
		<label for="paswword" > <?php _e( 'Password', 'pcpwp' ); ?></label>
		<input type="password" id="limit_true" name="pcpwp_settings_options[password]" value="<?php echo $password ?>" />
		<p class="description">
		    <?php _e( 'You can type a word or un sentence, no restriction. If not set, the default password is <strong>pcpwppass</strong>.', 'pcpwp' ); ?>
        </p>
    <?php }
}

/** The Impacted categories Checkboxes **/
if( !function_exists('pcpwp_func_impacted_categories'))  {
	function pcpwp_func_impacted_categories(){
	/* Get the option value from the database. */
		$options = get_option( 'pcpwp_settings_options' );
		$impacted_categories =  (is_array($options['impacted_categories'])) ? $options['impacted_categories'] : array();
		/* Echo the field. */ ?>
		<div id="impacted_categories">
		<?php
		$cats = get_categories(array('hide_empty' => 0));
        foreach( $cats as $cat ) { ?>
            <input type="checkbox" name="pcpwp_settings_options[impacted_categories][]" value="<?php echo $cat->cat_ID ?>"<?php if (in_array($cat->cat_ID, $impacted_categories)) echo ' checked'; ?> /> <?php echo $cat->cat_name ?><br>
        <?php } ?>
		<p class="description">
		    <?php _e( 'Check the categories that you want to have password restricted post access.', 'pcpwp' ); ?>
        </p>
        </div>
	<?php }
}


?>