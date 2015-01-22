<?php
/**
 * WooCommerce group Functions
 *
 * @author 		WooThemes
 * @category 	Core
 * @package 	WooCommerce/Functions
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Get group taxonomies.
 *
 * @return object
 */
function wc_get_groups() {

	$transient_name = 'wc_crm_group_taxonomies';

	if ( false === ( $group_taxonomies = get_transient( $transient_name ) ) ) {

		global $wpdb;

		$group_taxonomies = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "wc_crm_groups" );

		set_transient( $transient_name, $group_taxonomies );
	}

	return apply_filters( 'wc_crm_groups', $group_taxonomies );
}



function wc_crm_group_exists( $group_slug ) {
	global $wpdb;

	return $label = $wpdb->get_var( $wpdb->prepare( "SELECT group_slug FROM {$wpdb->prefix}wc_crm_groups WHERE group_slug = %s;", $group_slug ) );
}

