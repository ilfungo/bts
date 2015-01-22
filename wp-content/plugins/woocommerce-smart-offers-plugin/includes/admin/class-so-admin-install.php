<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Installation related functions and actions.
 *
 * @author 		WooThemes
 * @category 	Admin
 * @package 	WooCommerce/Classes
 * @version     2.1.0
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('SO_Admin_Install')) {

    /**
     * WC_Install Class
     */
    class SO_Admin_Install {

        /**
         * Hook in tabs.
         */
        public function __construct() {
            register_activation_hook(SO_PLUGIN_FILE, array($this, 'install'));
        }

        /**
         * install SO
         */
        public function install() {
            $this->create_options();
            
            SO_Admin_Post_Type::register_post_type();
            
            // Flush rules after install
            flush_rewrite_rules();
        }

        /**
         * Default options
         *
         * Sets up the default options used on the settings page
         *
         * @access public
         */
        function create_options() {

            add_option('woo_sm_offer_show_hidden_items', 'yes');
            add_option('woo_sm_offers_if_multiple', 'high_price');
            add_option('so_max_inline_offer', 1);

            $so_css_for_accept = "display: block;
                                border-style: groove;
                                border-color: #ffab23;
                                border-width: 3px 4px 4px 3px;
                                height: 50px;
                                width: 320px;
                                background: #ffec64;
                                color: #333;
                                line-height: 2;
                                text-align: center;
                                font-size: 25px;
                                margin: auto;
                                text-decoration: none;
                                font-family: Myriad Pro, Impact, Helvetica, sans-serif;
                                font-weight: 800;
                                text-shadow: 1px 1px 0px #ffee66;
                                border-radius: 9px;";

            $so_css_for_skip =  "text-align: center; margin: auto;";
            
            add_option('so_css_for_accept', $so_css_for_accept);
            add_option('so_css_for_skip', $so_css_for_skip);
        }

    }

}

return new SO_Admin_Install();
