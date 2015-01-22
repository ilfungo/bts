<?php

/**********************/
/* Include OCMX files */
include_once ("load-scripts.php");

/**************************/
/* WP 3.4 Support         */
global $wp_version;
if ( version_compare( $wp_version, '3.4', '>=' ) )
	add_theme_support( 'custom-background' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );

if ( ! isset( $content_width ) ) $content_width = 800;
add_image_size( 'slider-main', 599, 300, true);

/*********************/
/* Load Localization */
load_theme_textdomain('ocmx', get_template_directory() . '/lang');

/*****************/
/* Add Nav Menus */
if (function_exists('register_nav_menus')) :
	register_nav_menus( array(
		'primary' => __('Primary Navigation', 'Default')
	) );
endif;

/************************************************/
/* Fallback Function for WordPress Custom Menus */

function ocmx_fallback() {
	echo '<ul id="nav" class="clearfix">';
		wp_list_pages('title_li=&');
	echo '</ul>';
}


/******************************************************************************/
/* Each theme has their own "No Posts" styling, so it's kept in functions.php */

function ocmx_no_posts(){
	_e("The page you are looking for does not exist","ocmx");
};
// disable the admin bar
show_admin_bar(false);

// Disable WooCommerce stylesheet for all themes
if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, "2.1" ) >= 0 ) {
	add_filter( 'woocommerce_enqueue_styles', '__return_false' );
} else {
	define( 'WOOCOMMERCE_USE_CSS', false );
}

function oboxfb_add_query_vars($query_vars) {
	$query_vars[] = 'stylesheet';
	return $query_vars;
}
add_filter( 'query_vars', 'oboxfb_add_query_vars' );
function oboxfb_takeover_css() {
	    $style = get_query_var('stylesheet');

	    if($style == "custom") {
		    include_once(get_template_directory(). '/style.php');
	        exit;
	    }
	}
add_action( 'template_redirect', 'oboxfb_takeover_css');

/**************************/
/* Facebook Support      */
function get_fbimage() {
	global $post;
	if ( !is_single() ){
		return '';
	}
	$src = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), '', '' );
	$fbimage = null;
	if ( has_post_thumbnail($post->ID) ) {
		$fbimage = $src[0];
	} else {
		global $post, $posts;
		$fbimage = '';
		$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i',
		$post->post_content, $matches);
		if(!empty($matches[1]))
			$fbimage = $matches [1] [0];
	}
	if(empty($fbimage)) {
		$fbimage = get_the_post_thumbnail($post->ID);
	}
	return $fbimage;
}
/**************************************************/
/* Redefine woocommerce_output_related_products() */

function woocommerce_output_related_products() {
woocommerce_related_products(4,1); // Display 3 products in rows of 3
}
