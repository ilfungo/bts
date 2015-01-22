<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('SO_Init')) {

    Class SO_Init {

        function __construct() {
            ob_start();
            // Action to process accept and skip functionality
            add_action('wp_head', array(&$this, 'so_process_offer_action'));
            add_action('wp_head', array(&$this, 'so_wp_head'));

            add_action('woocommerce_cart_updated', array(&$this, 'remove_offered_product_having_parent'), 12);
            add_action('woocommerce_before_cart_item_quantity_zero', array(&$this, 'remove_offered_product'), 10, 2);
            add_action('woocommerce_after_cart_item_quantity_update', array(&$this, 'remove_offered_product'), 10, 2);
            add_action('woocommerce_before_calculate_totals', array(&$this, 'add_offered_price'));
            add_action('woocommerce_checkout_process', array(&$this, 'add_offered_price_during_checkout'));


            // Add additional meta data
            add_action('woocommerce_checkout_update_order_meta', array(&$this, 'so_update_order_meta'), 10, 2);
            add_action('woocommerce_order_status_changed', array($this, 'change_paid_through_count'), 10, 3);
            add_action('wp_enqueue_scripts', array($this, 'apply_css_on_accept_skip_class'));
            add_action('wp_logout', array(&$this, 'so_clear_session'));
            add_filter('woocommerce_get_cart_item_from_session', array(&$this, 'get_offered_cart_item_from_session'), 10, 2);
            add_filter('woocommerce_cart_item_quantity', array(&$this, 'offered_prod_cart_item_quantity'), 10, 2);
        }

        /**
	 * Enqueqe Accept/Skip CSS on preview offer
	 */
        function so_wp_head() {
            if (isset($_GET ['preview']) && $_GET ['preview'] == 'true') {
                wp_enqueue_style('so_frontend_css');
            }
        }

        /**
	 * Add Accept/Skip CSS
	 */
        function apply_css_on_accept_skip_class() {
            wp_register_style('so_frontend_css', plugins_url(SMART_OFFERS) . '/assets/css/frontend.css', 'so_frontend_css');
            $accept = get_option('so_css_for_accept');
            $skip = get_option('so_css_for_skip');
            $style_for_accept = "div.so_accept { $accept }";
            $style_for_skip = "div.so_skip { $skip }";
            wp_add_inline_style('so_frontend_css', $style_for_accept);
            wp_add_inline_style('so_frontend_css', $style_for_skip);
        }
        
        /**
	 * Remove upsell product from cart if cart contains rule does not satisfy
	 */
        function remove_offered_product($cart_item_key, $quantity = 0) {
            global $woocommerce;

            // To execute the function only if some product is being removed or quantity set to 0
            if ($quantity == 0) {

                $cart = $woocommerce->cart->cart_contents;
                unset($cart[$cart_item_key]);

                $count_of_offered_prod_in_cart = 0;
                $count_of_non_offered_prod_in_cart = 0;
                $count_offered_product_having_parent_id = 0;
                $key_of_offered_prod_having_parent_id = array();

                foreach ($cart as $key => $values) {

                    if (isset($values['smart_offers']['cart_contains_keys'])) {
                        $count_of_offered_prod_in_cart++;
                    } else {
                        $count_of_non_offered_prod_in_cart++;
                    }
                }

                $offer_ids_to_unset = array();

                // To perform further execution only of there are offered prod in cart
                if ($count_of_offered_prod_in_cart > 0) {

                    foreach ($cart as $key => $values) {

                        if (isset($values['smart_offers']) && isset($values['smart_offers']['cart_contains_keys'])) {
                            $cart_contains_keys = $values['smart_offers']['cart_contains_keys'];

                            foreach ($cart_contains_keys as $k => $cart_key) {

                                if ($cart_item_key == $cart_key) {

                                    if (isset($values['smart_offers']['parent_offer_id'])) {
                                        unset($woocommerce->cart->cart_contents[$key]['smart_offers']['cart_contains_keys'][$k]);
                                    }

                                    unset($cart[$key]['smart_offers']['cart_contains_keys'][$k]);
                                }
                            }
                        }
                    }

                    $cart_items_keys_to_be_removed = array();

                    foreach ($cart as $k => $v) {

                        if (isset($v['smart_offers']) && isset($v['smart_offers']['cart_contains_keys'])) {

                            $cart_contains_keys = $v['smart_offers']['cart_contains_keys'];
                            $cart_contains_ids = $v['smart_offers']['cart_contains_ids'];
                            $ids = array();

                            if (!empty($cart_contains_keys)) {

                                foreach ($cart_contains_keys as $cart_contains_key) {

                                    if ($cart[$cart_contains_key]['variation_id'] != '') {
                                        $ids[] = $cart[$cart_contains_key]['variation_id'];
                                        $ids[] = $cart[$cart_contains_key]['product_id'];
                                    } else {
                                        $ids[] = $cart[$cart_contains_key]['product_id'];
                                    }
                                }
                            } else {

                                foreach ($cart as $item_key => $item_val) {

                                    if ($k != $item_key) {
                                        if ($cart[$item_key]['variation_id'] != '') {
                                            $ids[] = $cart[$item_key]['variation_id'];
                                            $ids[] = $cart[$item_key]['product_id'];
                                        } else {
                                            $ids[] = $cart[$item_key]['product_id'];
                                        }
                                    }
                                }
                            }

                            $cart_contains_value = ( count(array_intersect($cart_contains_ids, $ids)) == count($cart_contains_ids) ) ? 1 : 0;

                            if ($cart_contains_value == 0) {

                                if (isset($v['smart_offers']) && isset($v['smart_offers']['parent_offer_id'])) {

                                    continue;
                                } else {
                                    unset($cart[$k]);
                                    $cart_items_keys_to_be_removed[] = $k;
                                }
                            } else {
                                continue;
                            }
                        } else {
                            continue;
                        }
                    }
                    if (!empty($cart_items_keys_to_be_removed)) {
                        foreach ($cart_items_keys_to_be_removed as $item_key) {

                            $offer_id = $woocommerce->cart->cart_contents[$item_key]['smart_offers']['offer_id'];
                            $offer_ids_to_unset[] = $offer_id;
                            if (isset($woocommerce->cart->cart_contents[$key]['smart_offers']['parent_offer_id'])) {
                                $offer_ids_to_unset[] = $woocommerce->cart->cart_contents[$key]['smart_offers']['parent_offer_id'];
                            }
                            unset($woocommerce->cart->cart_contents[$item_key]);
                        }
                    } else {
                        return;
                    }

                    if (count($offer_ids_to_unset) > 0) {
                        SO_Session_Handler::unset_offer_ids_from_session($offer_ids_to_unset);
                    }
                } else {
                    return;
                }
            } else {
                return;
            }
        }

        /**
	 * Remove upsell product from cart if rules of upsell offer or it's parent offer don't satisfy
	 */
        function remove_offered_product_having_parent() {
            global $woocommerce;
            
            $so_offers = new SO_Offers();

            if (( isset($_GET['remove_item']) && $_GET['remove_item'] ) || (!empty($_POST['update_cart']) )) {

                $wc_compat = SA_Smart_Offers::wc_compat();

                $offer_ids_to_unset = array();

                foreach ($woocommerce->cart->cart_contents as $key => $values) {
                    $offer_ids = array();
                    if ( isset($values['smart_offers']) ) {

                        // To validate the offers on anu updation of cart
                        if (( $values['smart_offers']['accepted_from'] == "cart_page" || $values['smart_offers']['accepted_from'] == "checkout_page" || $values['smart_offers']['accepted_from'] == "myaccount_page" || $values['smart_offers']['accepted_from'] == "home_page" || $values['smart_offers']['accepted_from'] == "any_page")) {

                            $offer_ids[] = $values['smart_offers']['offer_id'];

                            if (( isset($values['smart_offers']['cart_contains_keys']) && empty($values['smart_offers']['cart_contains_keys']))) {
                                if (is_array($values['smart_offers']['parent_offer_id'])) {
                                    $offer_ids = array_unique(array_merge($offer_ids, $values['smart_offers']['parent_offer_id']));
                                } else {
                                    $offer_ids[] = $values['smart_offers']['parent_offer_id'];
                                }
                            }
                        }

                        if (!empty($offer_ids)) {
                            $user_details = $so_offers->get_user_details("cart", ''); //get all user details
                            // Get Cart/Order details
                            $cart_order_details = $so_offers->get_cart_contents();
                            $dp = (int) get_option('woocommerce_price_num_decimals');
                            $woocommerce->cart->calculate_shipping();

                            if ($wc_compat::is_wc_gte_20()) {
                                $cart_total = apply_filters('woocommerce_calculated_total', number_format($woocommerce->cart->cart_contents_total + $woocommerce->cart->tax_total + $woocommerce->cart->shipping_tax_total + $woocommerce->cart->shipping_total - $woocommerce->cart->discount_total + $woocommerce->cart->fee_total, $dp, '.', ''));
                            } else {
                                $cart_total = apply_filters('woocommerce_calculated_total', number_format($woocommerce->cart->cart_contents_total + $woocommerce->cart->tax_total + $woocommerce->cart->shipping_tax_total + $woocommerce->cart->shipping_total - $woocommerce->cart->discount_total, $dp, '.', ''));
                            }

                            $cart_order_details['offer_rule_grand_total'] = $cart_total;

                            $details = array_merge($user_details, $cart_order_details);
                            $offer_rules = $so_offers->get_all_offer_rules_meta($offer_ids);
                            $valid_offers = $so_offers->validate_offers("cart_page", $offer_rules, $details);

                            if (empty($valid_offers)) {
                                $offer_id = $woocommerce->cart->cart_contents[$key]['smart_offers']['offer_id'];
                                $offer_ids_to_unset[] = $offer_id;
                                if (isset($woocommerce->cart->cart_contents[$key]['smart_offers']['parent_offer_id'])) {
                                    $offer_ids_to_unset[] = $woocommerce->cart->cart_contents[$key]['smart_offers']['parent_offer_id'];
                                }
                                $woocommerce->cart->set_quantity($key, 0);
                            } else {
                                continue;
                            }
                        }
                    } else {
                        continue;
                    }
                }

                if (count($offer_ids_to_unset) > 0) {
                    SO_Session_Handler::unset_offer_ids_from_session($offer_ids_to_unset);
                }
            } else {
                return;
            }
        }

        /**
	 * Add meta information in the order and increase the count of offer
	 */
        function so_update_order_meta($order_id, $posted) {
            global $woocommerce;

            $so_order_meta = array();

            foreach ($woocommerce->cart->get_cart() as $cart_key => $values) {

                if (isset($values ['smart_offers'])) {

                    $offer_id = $values ['smart_offers'] ['offer_id'];
                    $so_order_count = get_post_meta($offer_id, 'so_order_count', true);
                    $count = (empty($so_order_count) || !array_key_exists('order_count', $so_order_count)) ? 1 : ++$so_order_count ['order_count'];
                    $so_order_count ['order_count'] = $count;
                    update_post_meta($offer_id, 'so_order_count', $so_order_count);
                    $product_id = (isset($values ['variation_id']) && $values ['variation_id'] != '') ? $values ['variation_id'] : $values ['product_id'];
                    $so_order_meta [$offer_id] ['product_id'] = $product_id;
                    $so_order_meta [$offer_id] ['offered_price'] = $values ['data']->price;
                }
            }

            if (!empty($so_order_meta)) {
                update_post_meta($order_id, 'smart_offers_meta_data', $so_order_meta);
            }
        }

        /**
	 * Fetch all skipped offers of cart and account page by user
	 */
        function get_skipped_offers($current_offer_id) {
            global $current_user;

            $user_skipped_offers = get_user_meta($current_user->ID, 'customer_skipped_offers', true);
            
            if (!empty($user_skipped_offers)) {
                $customer_skipped_offers = maybe_unserialize($user_skipped_offers);
            }
            $customer_skipped_offers [] = $current_offer_id;
            $customer_skipped_offers = array_unique($customer_skipped_offers); 

            return $customer_skipped_offers;
        }

        /**
	 * Add offered price in cart.
	 */
        function add_offered_price($cart_object) {
            global $woocommerce;

            $so_offer = new SO_Offer();
            if (sizeof($cart_object->cart_contents) > 0) {
                foreach ($cart_object->cart_contents as $key => $value) {
                    if (isset($value ['smart_offers'] ['accept_offer'])) {

//                                                      Calculate price on basis of product_id
                        $product_id = ( isset($value['variation_id']) && $value['variation_id'] != '' ) ? $value['variation_id'] : $value['product_id'];
//                					$price = get_post_meta( $values ['smart_offers'] ['offer_id'], 'offer_price', true );
                        $offer_id = $value ['smart_offers'] ['offer_id'];
                        $price = $so_offer->get_offer_price(array('offer_id' => $offer_id, 'prod_id' => $product_id));
                        $value ['data']->price = $price;
                        $value ['data']->sale_price = $price;
                        $value ['data']->regular_price = $price;
                    } else {
                        return;
                    }
                }
            }
        }

        /**
	 * Add offered price in checkout.
	 */
        function add_offered_price_during_checkout() {
            global $woocommerce;

            $cart = $woocommerce->cart->cart_contents;

            if (sizeof($cart) > 0) {
                foreach ($cart as $key => $value) {

                    if (isset($value ['smart_offers']['accept_offer'])) {

                        //                                          Calculate price on basis of product_id
                        $product_id = ( isset($value['variation_id']) && $value['variation_id'] != '' ) ? $value['variation_id'] : $value['product_id'];
                        //                                          $price = get_post_meta( $values ['smart_offers'] ['offer_id'], 'offer_price', true );
                        $offer_id = $value ['smart_offers'] ['offer_id'];
                        $so_offer = new SO_Offer();
                        $price = $so_offer->get_offer_price(array('offer_id' => $offer_id, 'prod_id' => $product_id));
                        $value ['data']->price = $price;
                        $value ['data']->sale_price = $price;
                        $value ['data']->regular_price = $price;
                    }
                }
            }
        }

        /**
	 * Set quantity for the offered product in cart.
	 */
        function offered_prod_cart_item_quantity($quantity, $cart_item_key) {
            global $woocommerce;

            if (isset($woocommerce->cart->cart_contents [$cart_item_key] ['smart_offers']))
                return $woocommerce->cart->cart_contents [$cart_item_key] ['quantity'];
            return $quantity;
        }

        /**
	 * Add offered price in cart.
	 */
        function get_offered_cart_item_from_session($cart_item, $values) {
            global $woocommerce;

            if (isset($values ['smart_offers'])) {
                $so_offer = new SO_Offer();
                $cart_item ['smart_offers'] = $values ['smart_offers'];

//                                      Calculate price on basis of product_id
                $product_id = ( isset($cart_item['variation_id']) && $cart_item['variation_id'] != '' ) ? $cart_item['variation_id'] : $cart_item['product_id'];
//					$price = get_post_meta( $values ['smart_offers'] ['offer_id'], 'offer_price', true );
                $offer_id = $values ['smart_offers'] ['offer_id'];
                $price = $so_offer->get_offer_price(array('offer_id' => $offer_id, 'prod_id' => $product_id));

                $cart_item ['data']->price = $price;
                $cart_item ['data']->sale_price = $price;
                $cart_item ['data']->regular_price = $price;
            }

            return $cart_item;
        }
        
        /**
	 * Action to perform on accept/skip offer
	 */
        function so_process_offer_action() {
            global $current_user, $woocommerce;
            
            $so_offer = new SO_Offer();
            $so_offers = new SO_Offers();

            if (isset($_GET['so_action']) && ( $_GET['so_action'] == "accept" || $_GET ['so_action'] == "skip" )) {

                $wc_compat = SA_Smart_Offers::wc_compat();

                // Nonce security check
                $nonce = $_GET['_wpnonce'];
                if (!wp_verify_nonce($nonce, 'so_' . $_GET ['so_action']) || !isset($_GET['so_offer_id'])) {
                    return;
                }    
                
                $current_offer_id = $_GET['so_offer_id'];
                
                if (is_home() || is_front_page()) {
                    $where = "home";
                    $where_url = home_url();
                } elseif (is_cart()) {
                    $where = "cart";
                    $where_url = $woocommerce->cart->get_cart_url();
                } elseif (is_account_page()) {
                    $where = "myaccount";
                    $where_url = get_permalink(woocommerce_get_page_id('myaccount'));
                } else {
                    $where = "any";
                }

                if ($wc_compat::is_wc_gte_21()) {
                    global $wp;
                    if (is_checkout()) {

                        if (isset($wp->query_vars['order-received'])) {
                            $where = "thankyou";
                        } else {
                            $where = "checkout";
                            $where_url = $woocommerce->cart->get_checkout_url();
                        }
                    }
                } else {

                    if (is_checkout()) {
                        $where = "checkout";
                        $where_url = $woocommerce->cart->get_checkout_url();
                    } elseif ((function_exists('is_order_received_page') && is_order_received_page()) || is_page(woocommerce_get_page_id('thanks'))) {
                        $where = "thankyou";
                    }
                }
                $page = $where . '_page';
                
                if (in_array($where, array('thankyou', 'any'))) {
                    $where_url = ( wp_get_referer() ) ? wp_get_referer() : home_url();
                }

//                              ===============================================================
                // Array to store accepted/skipped offer ids
                $accepted_ids_in_session = $skipped_ids_in_session = $offer_id_on_skipping = array();

                // Check whether 'sa_smart_offers_accepted_offer_ids' session variable is set or not.
                $accepted_session_variable = SO_Session_Handler::check_session_set_or_not('sa_smart_offers_accepted_offer_ids');
                if ($accepted_session_variable) {
                    $accepted_ids_in_session = SO_Session_Handler::so_get_session_value('sa_smart_offers_accepted_offer_ids');
                }

                // Check whether 'sa_smart_offers_skipped_offer_ids' session variable is set or not.
                $skipped_session_variable = SO_Session_Handler::check_session_set_or_not('sa_smart_offers_skipped_offer_ids');
                if ($skipped_session_variable) {
                    $skipped_ids_in_session = SO_Session_Handler::so_get_session_value('sa_smart_offers_skipped_offer_ids');
                }

                // Need to work on this again. Create array and store id as page or where_url
                // Determine offer id on skipping
                //                          ========================================================================================================

                $skip_offer_id_variable = ( $where == "any" ) ? str_replace(array('/', '-', '&', '=', ':'), '', $where_url) . '_skip_offer_id' : $where . '_skip_offer_id';
                $skipped_offer_id_variable = SO_Session_Handler::check_session_set_or_not($skip_offer_id_variable);

                $offer_id_on_skipping = '';
                if ($skipped_offer_id_variable) {
                    $offer_id_on_skipping = SO_Session_Handler::so_get_session_value($skip_offer_id_variable);
                }

                $parent_offer_id_variable = ( $where == "any" ) ? str_replace(array('/', '-', '&', '=', ':'), '', $where_url) . '_parent_offer_id' : $where . '_parent_offer_id';
                //                          ======================================================================================================== 

                if ($_GET['so_action'] == "accept") {

                    $variation_data = ( isset($_POST['variation_id']) || isset($_POST['quantity']) ) ? $_POST : array();
                    $parent_offer_id = '';

                    if ($offer_id_on_skipping != '') {
                        $check_parent_offer_id = SO_Session_Handler::check_session_set_or_not($parent_offer_id_variable);
                        $parent_offer_id = ( $check_parent_offer_id ) ? SO_Session_Handler::so_get_session_value($parent_offer_id_variable) : '';
                    }

                    SO_Session_Handler::so_delete_session($parent_offer_id_variable);
                    SO_Session_Handler::so_delete_session($skip_offer_id_variable);

                    SO_Session_Handler::so_set_session_variables('sa_smart_offers_accepted_offer_ids', $current_offer_id);
                    
                    // Update stats
                    $so_offer->update_accept_skip_count($current_offer_id, 'accepted');
                    // Adds to cart
                    $so_offer->action_on_accept_offer($current_offer_id, $page, $parent_offer_id, $variation_data);
                } elseif ($_GET['so_action'] == "skip") {
                    
                    $so_offer->update_accept_skip_count($current_offer_id, 'skipped');

                    // Update if this offer needs to be skipped permanently for this user
                    $skip_permanently = get_post_meta($current_offer_id, 'sa_smart_offer_if_denied_skip_permanently', true);

                    if (!empty($skip_permanently) && $skip_permanently == true && $current_user->ID != 0) {
                        $customer_skipped_offers = $this->get_skipped_offers($current_offer_id);
                        $customer_skipped_offers = array_unique($customer_skipped_offers);
                        update_user_meta($current_user->ID, 'customer_skipped_offers', $customer_skipped_offers );
                    }

                    // To store skipped offers in session even if they are updated in DB
                    SO_Session_Handler::so_set_session_variables('sa_smart_offers_skipped_offer_ids', $current_offer_id);
                    SO_Session_Handler::so_delete_session($skip_offer_id_variable);
                    

                    $redirecting_option = get_post_meta($current_offer_id, 'sa_smart_offer_if_denied', true);
                    $redirect_to = get_post_meta($current_offer_id, 'url', true);

                    
                    if (empty($redirecting_option)) {
                        ob_clean();
                        wp_safe_redirect($where_url);
                    } else {
                        if ($redirecting_option == 'order_page') {
                            ob_clean();
                            wp_safe_redirect($where_url);
                        } elseif ($redirect_to != "") {

                            if ($redirecting_option == "offer_page") {

                                $offers_skipped_by_user = array();
                                if( $current_user->ID != 0 ) {
                                    get_user_meta($current_user->ID, 'customer_skipped_offers', true) ;
                                }
                                
                                $redirect_to = explode(',', $redirect_to);
                                $valid_another_offer_ids = array();

                                // Check whether 'sa_smart_offers_skipped_offer_ids' session variable is set or not.
                                $skipped_session_variable = SO_Session_Handler::check_session_set_or_not('sa_smart_offers_skipped_offer_ids');
                                // Check whether 'sa_smart_offers_accepted_offer_ids' session variable is set or not.
                                $accepted_session_variable = SO_Session_Handler::check_session_set_or_not('sa_smart_offers_accepted_offer_ids');

                                // Getting skipped/accepted ids of session.
                                $skipped_ids_in_session = ( $skipped_session_variable ) ? SO_Session_Handler::so_get_session_value('sa_smart_offers_skipped_offer_ids') : array();
                                $accepted_ids_in_session = ( $accepted_session_variable ) ? SO_Session_Handler::so_get_session_value('sa_smart_offers_accepted_offer_ids') : array();

                                $skipped_offer_id_variable = SO_Session_Handler::check_session_set_or_not($skip_offer_id_variable);
                                $offer_id_on_skipping = ( $skipped_offer_id_variable ) ? SO_Session_Handler::so_get_session_value($skip_offer_id_variable) : '';

                                $check_parent_offer_id_set_or_not = SO_Session_Handler::check_session_set_or_not($parent_offer_id_variable);

                                if (!$check_parent_offer_id_set_or_not) {
                                    SO_Session_Handler::so_set_session_variables($parent_offer_id_variable, $current_offer_id);
                                }

                                
                                foreach ($redirect_to as $value) {

                                    $unset_offer_id = false;

                                    if (in_array($value, $offers_skipped_by_user)) {
                                        $unset_offer_id = true;
                                    }

                                    if ($skipped_session_variable) {
                                        if (in_array($value, $skipped_ids_in_session)) {
                                            $unset_offer_id = true;
                                        }
                                    }

                                    if ($accepted_session_variable) {

                                        if (in_array($value, $accepted_ids_in_session)) {
                                            $unset_offer_id = true;
                                        }
                                    }

                                    if ($unset_offer_id && $unset_offer_id == true) {
                                        $key = array_search($value, $redirect_to);
                                        unset($redirect_to [$key]);
                                    } else {
                                        $get_offer_price = $so_offer->get_offer_price(array('offer_id' => $value));
//                                                          $valid_another_offer_ids[$value] = get_post_meta( $value, 'offer_price', true ); // Need to fetch price based on variation id, variation data, prod_id
                                        $valid_another_offer_ids[$value] = $get_offer_price;
                                    }
                                }

                                // TODO: Define settings class and fetch value from it.
                                $get_option_for_hidden = get_option('woo_sm_offer_show_hidden_items');
                                $get_option_for_price = get_option('woo_sm_offers_if_multiple');
                                $show_another_offer_data = $so_offers->process_offers($get_option_for_hidden, $get_option_for_price, $valid_another_offer_ids, $where, $order_containing_ids);
                                
                                if (!empty($show_another_offer_data)) {
                                    $valid_offer_id = $show_another_offer_data[0]['post_id'];
                                    SO_Session_Handler::so_set_session_variables($skip_offer_id_variable, $valid_offer_id);
                                }
//                                ob_clean();
                                wp_safe_redirect($where_url);
                            } elseif ($redirecting_option == "url") {

//                                ob_clean();
                                if (!preg_match("~^(?:ht)tps?://~i", $redirect_to)) {
                                    $return_url = (@$_SERVER ["HTTPS"] == "on") ? "https://" : "http://";
                                    $return_url = "http://" . $redirect_to;
                                } else {
                                    $return_url = $redirect_to;
                                }

                                wp_redirect($return_url);
                            } elseif ($redirecting_option == "particular_page") {

//                                ob_clean();
                                wp_safe_redirect(get_permalink($redirect_to));
                            }
                        }
                    }
                }
            }
        }

        /**
	 * Empty SO related session data on logout
	 */
        function so_clear_session() {
            global $woocommerce;

            $wc_compat = SA_Smart_Offers::wc_compat();

            SO_Session_Handler::so_delete_session('sa_smart_offers_skipped_offer_ids');
            SO_Session_Handler::so_delete_session('sa_smart_offers_accepted_offer_ids');

            $pages = array('cart', 'checkout', 'thankyou', 'myaccount', 'home', 'any');
            
            foreach ($pages as $page) {
                SO_Session_Handler::so_delete_session($page . '_skip_offer_id');
                SO_Session_Handler::so_delete_session($page . '_parent_offer_id');
            }

            if ($wc_compat::is_wc_gte_21()) {
                $data = get_option('_wc_session_' . $woocommerce->session->get_customer_id(), array());
            } else {
                $data = $_SESSION;
            }
            
            if (!empty($data)) {
                foreach ($data as $key_name => $value) {
                    if (strpos($key_name, '_skip_offer_id') !== false || strpos($key_name, '_parent_offer_id') !== false) {
                        SO_Session_Handler::so_delete_session($key_name);
                    }
                }
            }
        }

        /**
	 * Change the order count in case of order status change
	 */
        function change_paid_through_count($order_id, $old_status, $new_status) {

            $wc_compat = SA_Smart_Offers::wc_compat();

            $is_change_paid_through_count = false;

            if ( ( $wc_compat::is_wc_gte_22() && ( $new_status == 'wc-cancelled' || $new_status == 'wc-refunded' || $new_status == 'wc-failed' ) )
                    || ( $new_status == 'cancelled' || $new_status == 'refunded' || $new_status == 'failed' ) ) {
                $is_change_paid_through_count = true;
            }

            if ( $is_change_paid_through_count ) {
                $so_order_meta = get_post_meta($order_id, 'smart_offers_meta_data', true);

                foreach ($so_order_meta as $offer_id => $offer_data) {
                    $order_count = get_post_meta($offer_id, 'so_order_count', true);

                    if ($order_count) {
                        $count = --$order_count['order_count'];
                        $order_count['order_count'] = $count;
                        update_post_meta($offer_id, 'so_order_count', $order_count);
                    }
                }
            }
        }

    }

    return new SO_Init();
}    