<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Post types
 *
 * Registers post types and taxonomies
 *
 * @class 		WC_Post_types
 * @version		2.1.0
 * @package		WooCommerce/Classes/Products
 * @category	Class
 * @author 		WooThemes
 */

if (!class_exists('SO_Admin_Post_Type')) {
class SO_Admin_Post_Type {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array(__CLASS__, 'register_post_type'), 20);
    }

    /**
     * Register core post types
     */
    public static function register_post_type() {
        if (post_type_exists('smart_offers'))
            return;

        $labels = array('name' => __('Smart Offers', SO_TEXT_DOMAIN),
            'singular_name' => __('Smart Offer', SO_TEXT_DOMAIN),
            'menu_name' => __('Smart Offers', SO_TEXT_DOMAIN),
            'add_new' => __('Add New Offer', 'post', SO_TEXT_DOMAIN),
            'add_new_item' => __('Add New Offer', SO_TEXT_DOMAIN),
            'edit' => __('Edit', SO_TEXT_DOMAIN),
            'edit_item' => __('Edit Offer', SO_TEXT_DOMAIN),
            'new_item' => __('New Offer', SO_TEXT_DOMAIN),
            'search_items' => __('Search Offers', SO_TEXT_DOMAIN),
            'not_found' => __('No offers found', SO_TEXT_DOMAIN),
            'not_found_in_trash' => __('No offers found in Trash', SO_TEXT_DOMAIN),
            'parent' => __('Parent offer', SO_TEXT_DOMAIN)
        );

        $args = array('labels' => $labels,
            'description' => '',
            'publicly_queryable' => true,
            'exclude_from_search' => true,
            'capability_type' => 'post',
            'public' => true,
            'hierarchical' => false,
            'rewrite' => array('slug' => 'smart_offer', 'with_front' => true, 'pages' => true, 'feeds' => true),
            'has_archive' => true,
            'query_var' => 'smart_offer',
            'supports' => array('title'),
            'show_ui' => true,
            'menu_position' => 30,
            'show_in_menu' => 'woocommerce', // Use menu slug to put this as submenu
            'show_in_nav_menus' => true);

        register_post_type('smart_offers', $args);
    }

}

    return new SO_Admin_Post_Type();
}


