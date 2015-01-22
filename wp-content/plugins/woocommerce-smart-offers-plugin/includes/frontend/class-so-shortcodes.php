<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('SO_Shortcodes')) {

    Class SO_Shortcodes {

        function __construct() {
            // Add shortcodes on different wordpress & Woocommerce hooks
            add_action('wp_head', array(&$this, 'show_offer_on_home_page'));

            $wc_compat = SA_Smart_Offers::wc_compat();
            
            if ($wc_compat::is_wc_gte_20()) {
                add_action('woocommerce_before_cart', array(&$this, 'to_show_offer_on_cart'));
            } else {
                add_action('woocommerce_before_cart_table', array(&$this, 'to_show_offer_on_cart'));
            }


            add_action('woocommerce_cart_is_empty', array(&$this, 'so_cart_empty'));
            add_action('woocommerce_before_checkout_form', array(&$this, 'to_show_offer_on_checkout'));
            add_action('woocommerce_before_my_account', array(&$this, 'to_show_offer_on_account'));
            add_action('woocommerce_thankyou', array(&$this, 'to_show_offer_on_thankyou'), 9);


            add_shortcode('so_show_offers', array(&$this, 'shortcode_for_showing_offers'));
            add_shortcode('so_acceptlink', array(&$this, 'shortcode_for_accept_link'));
            add_shortcode('so_skiplink', array(&$this, 'shortcode_for_skip_link'));
            add_shortcode('so_product_variants', array(&$this, 'shortcode_for_showing_product_variants'));
            add_shortcode('so_quantity', array(&$this, 'shortcode_for_showing_quantity'));
        }

        /**
	 * Process and show offer on Home page as popup
	 */
        function show_offer_on_home_page() {
            if (is_home() || is_front_page()) {
                do_shortcode("[so_show_offers display_as='popup']");
            }
        }

        /**
	 * Process and show offer on cart page
	 */
        function to_show_offer_on_cart() {
            do_shortcode("[so_show_offers]");
        }

        /**
	 * Process and show offer on Cart empty template
	 */
        function so_cart_empty() {
            $this->to_show_offer_on_cart();
        }

        /**
	 * Process and show offer on Checkout page as popup
	 */
        function to_show_offer_on_checkout() {
            do_shortcode("[so_show_offers]");
        }

        /**
	 * Process and show offer on account page as popup
	 */
        function to_show_offer_on_account() {
            do_shortcode("[so_show_offers]");
        }

        /**
	 * Process and show offer on order received page as popup
	 */
        function to_show_offer_on_thankyou($order_id) {
            do_shortcode("[so_show_offers]");
        }

        /**
	 * Shortcode function for accept button.
	 */
        function shortcode_for_accept_link($atts) {
            return $this->get_link($atts, 'accept');
        }

        /**
	 * Shortcode function for skip button.
	 */
        function shortcode_for_skip_link($atts) {
            return $this->get_link($atts, 'skip');
        }

        /**
	 * return accept/skip link
	 */
        function get_link($atts, $action) {

            if (isset($_GET ['preview']) && $_GET ['preview'] == 'true') {
                return home_url();
            }

            if (empty($atts)) {
                return;
            }

            extract(shortcode_atts(array(
                'offer_id' => '',
                'page_url' => ''
                            ), $atts));

            $page_url = urldecode($page_url);

            $skip_url = add_query_arg('so_offer_id', $offer_id, add_query_arg("so_action", $action, $page_url));

            return wp_nonce_url($skip_url, 'so_' . $action);
        }

        /**
	 * Shortcode to show product variants in Offer description
	 */
        function shortcode_for_showing_product_variants($atts) {

            if (empty($atts)) {
                return;
            }

            $wc_compat = SA_Smart_Offers::wc_compat();

            extract(shortcode_atts(array(
                'prod_id' => '',
                'offer_id' => '',
                'page' => '',
                'where_url' => ''
                            ), $atts));

            if ($page == "cart_page" && !($wc_compat::is_wc_gte_20())) {
                return;
            }

            wp_enqueue_script('wc-add-to-cart-variation');

            $product = SO_Product_Details::get_product_instance($prod_id);
            $available_variations = $product->get_available_variations();

            foreach ($available_variations as $key => $value) {

                $variation_id = $value['variation_id'];
                $prod_instance = SO_Product_Details::get_product_instance($variation_id);
                $price = $prod_instance->get_price();
                $so_offer = new SO_Offer();
                $offer_price = $so_offer->get_offer_price(array('offer_id' => $offer_id, 'prod_id' => $variation_id));
                $available_variations[$key]['price_html'] = '<span class="price"> Offer Price: ' . $wc_compat::wc_price($offer_price) . '</span>';
            }

            $attributes = $product->get_variation_attributes();
            $selected_attributes = $product->get_variation_default_attributes();

            if ($wc_compat::is_wc_gte_20()) {

                $return_string = '<form action="' . do_shortcode("[so_acceptlink offer_id=" . $offer_id . " page_url=" . urlencode($where_url . "/") . "]") . '" class="variations_form cart" method="POST" id="so_addtocart_' . $offer_id . '" enctype="multipart/form-data" data-product_id="' . $prod_id . '" data-product_variations="' . esc_attr(json_encode($available_variations)) . '">';
                $return_string .= '<table class="variations" cellspacing="0"><tbody>';
                $loop = 1;
                foreach ($attributes as $name => $options) {

                    $return_string .= '<tr><td class="label"><label for="' . sanitize_title($name) . '">' . $wc_compat::wc_attribute_label($name) . '</label></td>';
                    $return_string .= '<td class="value"><select class="attribute_' . $loop . '" id="' . esc_attr(sanitize_title($name)) . '" name="attribute_' . sanitize_title($name) . '">';
                    $return_string .= '<option value="">' . __('Choose an option', 'smart_offers') . '</option>';

                    if (is_array($options)) {

                        $selected_value = ( isset($selected_attributes[sanitize_title($name)]) ) ? $selected_attributes[sanitize_title($name)] : '';

                        if (taxonomy_exists($name)) {

                            $orderby = $wc_compat::wc_attribute_orderby($name);

                            $args = array();
                            switch ($orderby) {
                                case 'name' :
                                    $args = array('orderby' => 'name', 'hide_empty' => false, 'menu_order' => false);
                                    break;
                                case 'id' :
                                    $args = array('orderby' => 'id', 'order' => 'ASC', 'menu_order' => false);
                                    break;
                                case 'menu_order' :
                                    $args = array('menu_order' => 'ASC');
                                    break;
                            }

                            $terms = get_terms($name, $args);

                            foreach ($terms as $term) {
                                if (!in_array($term->slug, $options))
                                    continue;

                                $return_string .= '<option value="' . esc_attr($term->slug) . '" ' . selected($selected_value, $term->slug, false) . '>' . apply_filters('woocommerce_variation_option_name', $term->name) . '</option>';
                            }
                        } else {

                            foreach ($options as $option) {
                                $return_string .= '<option value="' . esc_attr(sanitize_title($option)) . '" ' . selected(sanitize_title($selected_value), sanitize_title($option), false) . '>' . esc_html(apply_filters('woocommerce_variation_option_name', $option)) . '</option>';
                            }
                        }
                    }

                    $return_string .= '</select></td></tr>';
                    $loop++;
                }
            } else {

                $return_string = '<script type="text/javascript">';
                $return_string .= 'var product_variations_' . $prod_id . '=' . json_encode($available_variations) . '</script>';
                $return_string .= '<form action="' . do_shortcode("[so_acceptlink offer_id=" . $offer_id . " page_url=" . urlencode($where_url . "/") . "]") . '" class="variations_form cart" method="POST" id="so_addtocart_' . $offer_id . '" enctype="multipart/form-data" data-product_id="' . $prod_id . '">';
                $return_string .= '<table class="variations" cellspacing="0"><tbody>';
                $loop = 1;
                foreach ($attributes as $name => $options) {

                    $return_string .= '<tr><td class="label"><label for="' . sanitize_title($name) . '">' . $wc_compat::wc_attribute_label($name) . '</label></td>';
                    $return_string .= '<td class="value"><select class="attribute_' . $loop . '" id="' . esc_attr(sanitize_title($name)) . '" name="attribute_' . sanitize_title($name) . '">';
                    $return_string .= '<option value="">' . __('Choose an option', 'smart_offers') . '</option>';

                    if (is_array($options)) {

                        $selected_value = ( isset($selected_attributes[sanitize_title($name)]) ) ? $selected_attributes[sanitize_title($name)] : '';

                        if (taxonomy_exists(sanitize_title($name))) {

                            $terms = get_terms(sanitize_title($name), array('menu_order' => 'ASC'));

                            foreach ($terms as $term) {

                                if (!in_array($term->slug, $options))
                                    continue;
                                $return_string .= '<option value="' . $term->slug . '" ' . selected($selected_value, $term->slug, false) . '>' . apply_filters('woocommerce_variation_option_name', $term->name) . '</option>';
                            }
                        } else {

                            foreach ($options as $option) {
                                $return_string .= '<option value="' . esc_attr(sanitize_title($option)) . '" ' . selected(sanitize_title($selected_value), sanitize_title($option), false) . '>' . esc_html(apply_filters('woocommerce_variation_option_name', $option)) . '</option>';
                            }
                        }
                    }

                    $return_string .= '</select></td></tr>';
                    $loop++;
                }
            }

            $return_string .= '</tbody></table>';
            $return_string .= '<input type="hidden" id="parent_prod_id" name="parent_prod_id" value="' . $prod_id . '">';
            $return_string .= '<input type="hidden" name="variation_id" value="" />';
            $return_string .= '<div class="single_variation_wrap" style="display:none;"><div class="single_variation"></div></div></form>';

            return $return_string;
        }

        //
        function shortcode_for_showing_quantity($atts) {

            extract(shortcode_atts(array(
                'value' => 1,
                'allow_change' => 'false',
                'min' => 1,
                'max' => '',
                'prod_id' => '',
                'offer_id' => '',
                'page' => '',
                'where_url' => ''
                            ), $atts));

            $wc_compat = SA_Smart_Offers::wc_compat();

            if ($page == "cart_page" && !($wc_compat::is_wc_gte_20()))
                return;

            if ($allow_change == 'false') {
                $style = "display: none";
            }

            $html = '<form action="' . do_shortcode("[so_acceptlink offer_id=" . $offer_id . " page_url=" . urlencode($where_url . "/") . "]") . '" method="POST" id="so_qty_' . $offer_id . '"';
            if (!empty($style)) {
                $html .= 'style="' . $style . '"';
            }
            $html .= '>';

            $qty_params = array('input_value' => $value,
                'max_value' => $max,
                'min_value' => $min);

            $html .= woocommerce_quantity_input($qty_params, null, false);
            $html .= '</form>';

            return $html;
        }

        
        /**
	 * Shortcode to show offer
	 */
        function shortcode_for_showing_offers($atts) {
            extract(shortcode_atts(array(
                'display_as' => '',
                'offer_ids' => ''
                            ), $atts));

            $so_offers = new SO_Offers();
            $offers_data = $so_offers->get_offers($offer_ids);

            if (empty($offers_data)) {
                return;
            }

            $so_offer = new SO_Offer();
            $so_offer->prepare_offer($display_as, $offers_data);
        }

    }

    return new SO_Shortcodes();
}
