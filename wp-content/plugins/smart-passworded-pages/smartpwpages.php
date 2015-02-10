<?php
/*
Plugin Name: Smart Passworded Pages
Plugin URI: http://thecodecave.com/plugins/smart-passworded-pages-plugin/
Description: Allows a central login page for password protected child pages. Enter a password and you are taken to the newest child page with a matching password.
Version: 1.1.5
Author: Brian Layman
Author URI: http://eHermitsInc.com/
License: GPL2
Requires: 2.5

Copyright 2014  Brian Layman  (email : plugins@thecodecave.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
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
 class smartPWPages {
	/**
	 * Smart Passworded Pages
	 * Embeds a form for password submission into a post via a shortcode.
	 */
     function smartpwpages_shortcode( $atts ) {
		global $post;
		extract( shortcode_atts( array(
			'label' => __( 'Enter', 'smartpwpages' ),
			'ID' => 'smartPWLogin',
		), $atts ) );
		$result =  '<form ID="' . $ID . '" method="post" action="' . get_permalink() . '" >' . PHP_EOL;
		if ( isset( $_GET['wrongpw'] ) ) $result .= '<p id="smartPWError">' . __( 'You\'ve entered an invalid password.</p>', 'smartpwpages' ) . PHP_EOL;
		$result .= '	<input class="requiredField" type="password" name="smartPassword" id="smartPassword" value=""/>' . PHP_EOL;
		$result .= '	<input type="hidden" name="smartParent" value="' .  $post->ID . '" />' . PHP_EOL;
		$result .= '	<input type="hidden" name="smartPWPage_nonce" value="' . wp_create_nonce( smartPWPage ).'" />' . PHP_EOL;
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
		global $wp_hasher;
		if ( isset( $_POST[ 'smartPassword' ] ) && isset( $_POST[ 'smartParent' ] ) && wp_verify_nonce( $_POST[ 'smartPWPage_nonce' ], smartPWPage ) ) {
			$parentForm  = (int) $_POST[ 'smartParent' ] ;
			$password = $_POST[ 'smartPassword' ];

			if ( function_exists( 'wp_unslash' ) ) {
				$postPassword = wp_unslash( $password );
			} else {
				$postPassword = stripslashes( $password );
			}

			$args = array(		
				'sort_order' => 'DESC',
				'sort_column' => 'post_date',
				'hierarchical' => 1,
				'child_of' => $parentForm,
				'parent' => $parentForm,
				'post_type' => 'page',
				'post_status' => 'publish'
			);
			
			if ( function_exists( 'pause_exclude_pages' ) ) pause_exclude_pages();

			$myPages = get_pages( $args );

			if ( function_exists( 'resume_exclude_pages' ) ) resume_exclude_pages();

			// Version 3.4 and higher has better security on the pw pages
			if ( version_compare( $wp_version, '3.4', '>=' ) ) {
				if ( empty( $wp_hasher ) ) {
					// By default, use the portable hash from phpass
					require_once( ABSPATH . 'wp-includes/class-phpass.php' );
					$wp_hasher = new PasswordHash( 8, true );
				}
			}

			foreach( $myPages as $page ) {
				if ( ( $page->post_password == $postPassword ) || ( !empty( $wp_hasher ) && $wp_hasher->CheckPassword( $post->post_password, $postPassword ) ) ) {
					$permalink = get_permalink( $page->ID );
					$this->pw_redirect( $permalink, $postPassword );
				}
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
$smartPWPages = new smartPWPages();
add_action( 'init', array( $smartPWPages, 'process_form' ) );
add_shortcode( 'smartpwpages', array( $smartPWPages, 'smartpwpages_shortcode' ) );