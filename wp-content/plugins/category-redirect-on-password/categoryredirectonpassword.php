<?php
/*
Plugin Name: Category Redirect on Password
Plugin URI: http://www.federicoporta.com
Description: Allow to hide categories by menu and find them with a password
Version: 1.0
Author: Category Redirect on Password
Author URI: http://www.federicoporta.com
License: GPL2
Requires: 2.5


*/

/**
 * Define Constants
 */
define( 'SECONDS_TO_STORE_PW', 864000); // 864000 = 10 Days 

/**
 * Smart Passworded Pages Class
 * @copyright Copyright (c), Brian Layman
 * @author Brian Layman <plugins@TheCodeCave.com>
 */
 class catProtector {
	/**
	 * Smart Passworded Pages
	 * Embeds a form for password submission into a post via a shortcode.
	 */
     function catprotector_shortcode( $atts ) {
		global $post;
		extract( shortcode_atts( array(
			'label' => __( 'Vai alla tua scuola', 'catprotector' ),
			'ID' => 'smartPWLogin',
		), $atts ) );
		$result =  '<form ID="' . $ID . '" method="post" action="' . get_permalink() . '" >' . PHP_EOL;
		if ( isset( $_GET['wrongpw'] ) ) $result .= '<p id="smartPWError">' . __( 'Hai inserito una password non valida</p>.', 'catprotector' ) . PHP_EOL;
		$result .= '	<input class="requiredField" type="password" name="smartPassword" id="smartPassword" value=""/>' . PHP_EOL;
		$result .= '	<input type="hidden" name="smartParent" value="' .  $post->ID . '" />' . PHP_EOL;
		$result .= '	<input type="hidden" name="catProtector_nonce" value="' . wp_create_nonce( catProtector ).'" />' . PHP_EOL;
		$result .= '	<input type="submit" value="' . $label . '" />' . PHP_EOL;
		$result .= '</form>' . PHP_EOL;
		return $result;
	}

	/**
	 * Password Redirect
	 * Decodes the password, stores it in a cookie and redirects the visitor to that page.
	 */
	 function pw_redirect( $perma, $password ) {
		global $wp_version, $wp_hasher;

		// Version 3.6 introduces a new function
		if ( function_exists( 'wp_unslash' ) ) {
			$cookiePW = wp_unslash( $password );
		} else {
			$cookiePW = stripslashes( $password );
		}

		// Version 3.4 and higher has better security on the pw pages
		if ( version_compare( $wp_version, '3.4', '>=' ) ) {
			if ( empty( $wp_hasher ) ) {
				// By default, use the portable hash from phpass
				require_once( ABSPATH . 'wp-includes/class-phpass.php');
				$wp_hasher = new PasswordHash( 8, true );
			}

			// Potentially using a custom hasher, hash the pw
			$cookiePW = $wp_hasher->HashPassword( $cookiePW );
		}		
		
		// Store password for the length in the constant
		setcookie( 'wp-postpass_' . COOKIEHASH, $cookiePW, time() + SECONDS_TO_STORE_PW, COOKIEPATH );
		wp_safe_redirect( $perma );
		exit();
	}
	
	/**
	 * Process Form
	 * Decodes the password submitted on a form, find a page that uses it and redirects the visitor to that page.
	 */
	function process_form() {
		global $wp_hasher, $wpdb;;
		if ( isset( $_POST[ 'smartPassword' ] ) && isset( $_POST[ 'smartParent' ] ) && wp_verify_nonce( $_POST[ 'catProtector_nonce' ], catProtector ) ) {
			$parentForm  = (int) $_POST[ 'smartParent' ] ;
			$password = $_POST[ 'smartPassword' ];

			if ( function_exists( 'wp_unslash' ) ) {
				$postPassword = wp_unslash( $password );
			} else {
				$postPassword = stripslashes( $password );
			}


            //devo agire qui e usare probabilmente wp_query per andare a prendere quella categoria che
            //contiene quel campo specifico di password visible_password che sta in wp_options
            //
			$args = array(		
				'sort_order' => 'DESC',
				'sort_column' => 'post_date',
				'hierarchical' => 1,
				'child_of' => $parentForm,
				'parent' => $parentForm,
				'post_type' => 'page',
				'post_status' => 'publish'
			);


            $results = $wpdb->get_row($wpdb->prepare(
                "SELECT option_name FROM $wpdb->options WHERE option_value LIKE %s",
                '%:"'.$postPassword.'";%'
            ));


			if ( function_exists( 'pause_exclude_pages' ) ) pause_exclude_pages();

            //var_dump($results->option_name);
            $option_name=str_replace("taxonomy_","",$results->option_name);
            //var_dump($term_link);

            /*
			if ( function_exists( 'resume_exclude_pages' ) ) resume_exclude_pages();

			// Version 3.4 and higher has better security on the pw pages
			if ( version_compare( $wp_version, '3.4', '>=' ) ) {
				if ( empty( $wp_hasher ) ) {
					// By default, use the portable hash from phpass
					require_once( ABSPATH . 'wp-includes/class-phpass.php' );
					$wp_hasher = new PasswordHash( 8, true );
				}
			}
            */


            //var_dump($option_name);
            if ($option_name!="") {//todo migliorare il controllo qui!
                $term = get_term( $option_name, "product_cat" );
                $term_link = get_term_link( $term);
                $this->pw_redirect( $term_link, $postPassword );
            }


			// Nothing more to do here. If we reached here, we've submitted a pw but no match was found. 
			// Allow the page to continue loading, but hack $_GET to indicate the status
			$_GET[ 'wrongpw' ] = TRUE;
		}
	}
}

/**
 * Intialize Plugin
 */
$catProtector = new catProtector();
add_action( 'init', array( $catProtector, 'process_form' ) );
add_shortcode( 'catprotector', array( $catProtector, 'catprotector_shortcode' ) );