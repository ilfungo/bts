<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('SO_Offers')) {

    Class SO_Offers {

        function __construct() {
            
        }

        /**
	 * return Order details 
	 */
        function get_current_order_details($where) {

            $wc_compat = SA_Smart_Offers::wc_compat();

            $order_containing_ids = $current_order_details = array();
            $found_categories_ids = $found_categories_ids_total = $order = array();
            $order_id = $wc_compat::get_checkout_pay_page_order_id();

            if ($order_id && $order_id > 0) {

                $order = $wc_compat::get_order($order_id);

                if ($order->status == "completed" || $order->status == "processing") {

                    $order_items = $order->get_items();
                    $order_total = 0;

                    if (!empty($order_items)) {

                        foreach ($order_items as $item_details) {

                            $order_containing_ids[] = $wc_compat::is_wc_gte_20() ? $item_details['product_id'] : $item_details['id'];

                            if ($item_details['variation_id'] != '') {
                                $order_containing_ids[] = $item_details['variation_id'];
                            }

                            $order_total += $item_details ['line_subtotal'];

                            $get_prod_category_ids = wp_get_post_terms($item_details ['product_id'], 'product_cat', array("fields" => "ids"));

                            if (count($get_prod_category_ids) > 0) {
                                $get_prod_category_ids = array_fill_keys($get_prod_category_ids, $item_details ['line_subtotal']);
                                $found_categories_ids[] = $get_prod_category_ids;
                            }
                        }
                    }

                    foreach ($found_categories_ids as $found_categories_id) {
                        foreach ($found_categories_id as $cat_id => $cat_price) {
                            if (isset($found_categories_ids_total[$cat_id])) {
                                $found_categories_ids_total[$cat_id] += $cat_price;
                            }
                        }
                    }

                    $order_containing_ids = array_unique($order_containing_ids);
                    $order_grand_total = $order->order_total;

                    if (count($order_containing_ids)) {
                        $current_order_contains_products = implode(',', $order_containing_ids);
                    }

                    $current_order_details = array('offer_rule_cart_contains' => $current_order_contains_products,
                        'offer_rule_total' => $order_total,
                        'offer_rule_grand_total' => $order_grand_total,
                        'offer_rule_cart_category_details' => $found_categories_ids_total);
                }
            }

            return array($order_containing_ids, $current_order_details, $order);
        }

        /**
	 * return pgae details
	 */
        function get_page_details() {
            global $woocommerce;

            $wc_compat = SA_Smart_Offers::wc_compat();

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

            if ($where == "thankyou" || $where == "any") {

                $where_url = (isset($_SERVER ["HTTPS"]) && $_SERVER ["HTTPS"] == "on") ? "https://" : "http://";
                if ($_SERVER ["SERVER_PORT"] != "80") {
                    $where_url .= $_SERVER ["SERVER_NAME"] . ":" . $_SERVER ["SERVER_PORT"] . $_SERVER ["REQUEST_URI"];
                } else {
                    $where_url .= $_SERVER ["SERVER_NAME"] . $_SERVER ["REQUEST_URI"];
                }
            }

            return array($where, $where_url);
        }

        /**
	 * return accepted offer ids in the session
	 */
        function get_accepted_offer_ids_from_session() {

            $accepted_ids_in_session = array();

            // Check whether 'sa_smart_offers_accepted_offer_ids' session variable is set or not.
            $accepted_session_variable = SO_Session_Handler::check_session_set_or_not('sa_smart_offers_accepted_offer_ids');
            if ($accepted_session_variable) {
                $accepted_ids_in_session = SO_Session_Handler::so_get_session_value('sa_smart_offers_accepted_offer_ids');
            }

            return $accepted_ids_in_session;
        }

        /**
	 * Return skippedoffer in the session 
	 */
        function get_skipped_offer_ids_from_session() {
            $skipped_ids_in_session = array();
            $skipped_session_variable = SO_Session_Handler::check_session_set_or_not('sa_smart_offers_skipped_offer_ids');
            if ($skipped_session_variable) {
                $skipped_ids_in_session = SO_Session_Handler::so_get_session_value('sa_smart_offers_skipped_offer_ids');
            }

            return $skipped_ids_in_session;
        }

        /**
	 * return offer id value set after offer skipped
	 */
        function get_offer_id_on_skipping($skip_offer_id_variable) {


            $skipped_offer_id_variable = SO_Session_Handler::check_session_set_or_not($skip_offer_id_variable);
            $offer_id_on_skipping = '';
            if ($skipped_offer_id_variable) {
                $offer_id_on_skipping = SO_Session_Handler::so_get_session_value($skip_offer_id_variable);
            }

            return array($offer_id_on_skipping, $skipped_offer_id_variable);
        }

        /**
	 * return valid offers in a page
	 */
        function get_valid_offer_ids($data) {

            extract($data);

            $valid_offer_ids = array();

            if (!empty($offer_id_on_skipping)) {

                $unset_id = false;
                $offer_id = $offer_id_on_skipping;

                if (!empty($skipped_ids_in_session) || !empty($accepted_ids_in_session)) {
                    if (in_array($offer_id_on_skipping, $skipped_ids_in_session) || in_array($offer_id_on_skipping, $accepted_ids_in_session)) {
                        $unset_id = true;
                    }
                }

                if ($unset_id == true) {
                    SO_Session_Handler::so_delete_session($skip_offer_id_variable);
                } else {
                    $so_offer = new SO_Offer();
                    //$offer_price = get_post_meta( $offer_id, 'offer_price', true ); // Need to fetch price based on variation id, variation data, prod_id
                    $offer_price = $so_offer->get_offer_price(array('offer_id' => $offer_id));
                    $valid_offer_ids [$offer_id] = $offer_price;
                }
            } else {

                $parent_offer_id_variable = ( $where == "any" ) ? str_replace(array('/', '-', '&', '=', ':'), '', $where_url) . '_parent_offer_id' : $where . '_parent_offer_id';
                $check_parent_offer_id = SO_Session_Handler::check_session_set_or_not($parent_offer_id_variable);
                if ($check_parent_offer_id) {
                    SO_Session_Handler::so_delete_session($parent_offer_id_variable);
                }

                if (!empty($offer_ids)) {
                    
                    // get offers based on ids for future
                    $offer_ids = explode(',', $offer_ids);
                    $offer_ids = $this->get_page_offers($page, $offer_ids);
                } else {
                    $offer_ids = $this->get_page_offers($page);
                }

                if (!empty($offer_ids)) {
                    //Get user's details
                    $user_details = ( $where == "thankyou" ) ? $this->get_user_details($page, $order) : $this->get_user_details($page, '');
                    // Get Cart/Order details
                    $cart_order_details = ( $where == "thankyou" ) ? $current_order_details : $this->get_cart_contents();
                    $details = array_merge($user_details, $cart_order_details);
                    $valid_offer_ids = $this->validate_offers($page, $offer_ids, $details);
                } else {
                    return;
                }
            }

            if (empty($valid_offer_ids)) {
                return;
            }

            return $valid_offer_ids;
        }

        /**
	 * Return valid offer after validating offer aganist SO settings 
	 */
        function get_offers($offer_ids = null) {

            list($where, $where_url) = $this->get_page_details();
            $page = $where . '_page';

            $order_containing_ids = $current_order_details = array();
            $order = null;
            if ($where == "thankyou") {
                list($order_containing_ids, $current_order_details, $order) = $this->get_current_order_details($where);
            }

            $accepted_ids_in_session = $this->get_accepted_offer_ids_from_session();
            $skipped_ids_in_session = $this->get_skipped_offer_ids_from_session();
            $skip_offer_id_variable = ( $where == "any" ) ? str_replace(array('/', '-', '&', '=', ':'), '', $where_url) . '_skip_offer_id' : $where . '_skip_offer_id';
            list($offer_id_on_skipping, $skipped_offer_id_variable) = $this->get_offer_id_on_skipping($skip_offer_id_variable);

            $data = array(
                'page' => $page,
                'where' => $where,
                'where_url' => $where_url,
                'accepted_ids_in_session' => $accepted_ids_in_session,
                'skipped_ids_in_session' => $skipped_ids_in_session,
                'skip_offer_id_variable' => $skip_offer_id_variable,
                'offer_id_on_skipping' => $offer_id_on_skipping,
                'current_order_details' => $current_order_details,
                'offer_ids' => $offer_ids,
                'skipped_offer_id_variable' => $skipped_offer_id_variable,
                'order' => $order,
            );

            $valid_offer_ids = $this->get_valid_offer_ids($data);

            // TODO: Define settings class and fetch value from it.
            $get_option_for_hidden = get_option('woo_sm_offer_show_hidden_items');
            $get_option_for_price = get_option('woo_sm_offers_if_multiple');
            // Pick a single offer from available offers
            
            if(empty($valid_offer_ids)) {
                return;
            } 
            
            $offer_data = $this->process_offers($get_option_for_hidden, $get_option_for_price, $valid_offer_ids, $where, $order_containing_ids);
            $data['offer_data'] = $offer_data;
            return $data;
        }

        /**
	 * return offers details after processing the settings saved and checking offered prod is_in stock or not.
	 */
        function process_offers($get_option_for_hidden, $get_option_for_price, $offer, $where, $order_containing_ids = array()) {

            $wc_compat = SA_Smart_Offers::wc_compat();

            if ($get_option_for_price == 'high_price') {
                arsort($offer);
            } elseif ($get_option_for_price == 'low_price') {
                asort($offer);
            } elseif ($get_option_for_price == 'random') {
                arsort($offer);
            }

            $max_inline_offer = get_option('so_max_inline_offer') ? get_option('so_max_inline_offer') : 1;
            $offer_details = array();
            $i = 0;

            foreach ($offer as $post_id => $sale_price) {

                if( $where == "home" && $i == 1 ){
                    continue;
                }
                
                $offered_prod_id = get_post_meta($post_id, 'target_product_ids', true);

                if (empty($offered_prod_id)) {
                    continue;
                }

                if ($where == "thankyou") {
                    foreach ($order_containing_ids as $id) {
                        if ($id == $offered_prod_id)
                            continue 2;
                    }
                }

                $offered_product_instance = SO_Product_Details::get_product_instance($offered_prod_id);

                // To not to show offer on cart page if it has parent variabale prod for WC < 2.0
                if ($where == "cart" && !($wc_compat::is_wc_gte_20())) {
                    if ($offered_product_instance->product_type == 'variable' && !isset($offered_product_instance->variation_id))
                        continue;
                }

                $stock = ($offered_product_instance->is_in_stock() == true) ? 1 : 0;

                if ($stock == 1) {
                    if ($get_option_for_hidden == "no" && $offered_product_instance->visibility == "hidden") {
                        $show_offer = 0;
                    } else {
                        $show_offer = 1;
                    }
                } else {
                    $show_offer = 0;
                }

                if ($show_offer == 1) {

                    if ($i < $max_inline_offer) {
                        $offer_details [$i]['post_id'] = $post_id;
                        $offer_details [$i]['id'] = $offered_prod_id;
                        $offer_details [$i]['offer_price'] = $sale_price;
                    }
                }
                $i++;
            }

            return $offer_details;
        }

        /**
	 * return postmeta values of all offers of a particular page.
	 */
        function get_page_offers($page, $offer_ids = array()) {
            global $wpdb, $current_user;

//                              ===== Calculating ids to skipped =======================
            $offers_to_skip = array();

            //Checking whehter session is set or not.
            $skipped_session_variable = SO_Session_Handler::check_session_set_or_not('sa_smart_offers_skipped_offer_ids');
            $accepted_session_variable = SO_Session_Handler::check_session_set_or_not('sa_smart_offers_accepted_offer_ids');

            // Getting skipped/accepted ids of session.
            $skipped_ids_in_session = ( $skipped_session_variable ) ? SO_Session_Handler::so_get_session_value('sa_smart_offers_skipped_offer_ids') : array();
            $accepted_ids_in_session = ( $accepted_session_variable ) ? SO_Session_Handler::so_get_session_value('sa_smart_offers_accepted_offer_ids') : array();
            
            
            if (!empty($skipped_ids_in_session)) {
                $offers_to_skip = array_merge($offers_to_skip, $skipped_ids_in_session);
            }

            if (!empty($accepted_session_variable)) {
                $offers_to_skip = array_merge($offers_to_skip, $accepted_ids_in_session);
            }

            if ($current_user->ID != 0) {
                $offers_skipped_by_user = get_user_meta($current_user->ID, 'customer_skipped_offers', true);
                if (!empty($offers_skipped_by_user)) {
                    $offers_to_skip = array_merge($offers_to_skip, $offers_skipped_by_user);
                }
            }

            $offers_to_skip = array_unique($offers_to_skip);

//                              =======================================================

            $results_for_fetching_offers = array();

            $wpdb->query("SET SESSION group_concat_max_len=999999");
            
            if (!empty($offer_ids)) {

                // Query to fetch valid offer ids
                $query_to_fetch_all_offers = "SELECT post.ID from {$wpdb->prefix}posts as post JOIN {$wpdb->prefix}postmeta as postmeta
                                              ON (post.ID = postmeta.post_id)
                                              WHERE post.post_type = 'smart_offers' AND post.post_status = 'publish'
                                              AND post.ID IN (" . implode(',', $offer_ids) . ")";
            } else {

                $query_to_fetch_all_offers = "SELECT post.ID from {$wpdb->prefix}posts as post JOIN {$wpdb->prefix}postmeta as postmeta
                                              ON (post.ID = postmeta.post_id)
                                              WHERE post.post_type = 'smart_offers' AND post.post_status = 'publish'
                                              AND postmeta.meta_key = 'offer_rule_page_options' AND postmeta.meta_value LIKE '%$page%' ";
            }

            if (count($offers_to_skip) > 0) {

                $offers_to_skip_list = implode(', ', $offers_to_skip);

                if (!empty($offers_to_skip_list)) {
                    $query_to_fetch_all_offers .= ' AND post.id NOT IN (' . $offers_to_skip_list . ')';
                }
            }

            $results_for_fetching_offers = $wpdb->get_col($query_to_fetch_all_offers);

            if (count($results_for_fetching_offers) > 0) {
                $offers = $this->get_all_offer_rules_meta($results_for_fetching_offers);
            } else {
                $offers = array();
            }

            return $offers;
        }

        /**
	 * return offer rules of all offer ids
	 */
        function get_all_offer_rules_meta($offer_ids) {
            global $wpdb, $current_user;

            $offers = array();

            if (!empty($offer_ids) && is_array($offer_ids)) {

                foreach ($offer_ids as $offer_id) {
                    $offers [$offer_id] ['default_rule_show_offer'] = true;
                }

                $offer_ids = implode(',', $offer_ids);

                $query_to_get_all_meta = "SELECT pm.post_id,pm.meta_key,pm.meta_value from {$wpdb->prefix}postmeta as pm
                                      WHERE pm.meta_key LIKE 'offer_rule_%'
                                      AND pm.meta_key NOT LIKE '%page%'
                                      AND pm.post_id IN($offer_ids) ";

                $results_for_fetching_offers = $wpdb->get_results($query_to_get_all_meta);

                if (count($results_for_fetching_offers) > 0) {
                    foreach ($results_for_fetching_offers as $key => $value) {
                        $offers [$value->post_id] [$value->meta_key] = is_serialized($value->meta_value) ? maybe_unserialize($value->meta_value) : $value->meta_value;
                    }
                }
            }

            return $offers;
        }

        /**
	 * return valid offers after validating aganist rules based on cart/order details and user details
	 */
        function validate_offers($page, $page_offers_id, $details) {
            $user_cart_contains = ( isset($details ['offer_rule_cart_contains']) ) ? explode(",", $details ['offer_rule_cart_contains']) : array();
            $user_has_bought = ( isset($details ['offer_rule_has_bought']) ) ? explode(",", $details ['offer_rule_has_bought']) : array();
            $cart_category_details = ( isset($details['offer_rule_cart_category_details']) ) ? $details['offer_rule_cart_category_details'] : array();
            
            
            

            $valid_offers_id = array();

            foreach ($page_offers_id as $offer_id => $value) {

                $valid_offers_id [] = $offer_id;

                foreach ($value as $rule_key => $rule_value) {

                    if ($rule_key == "offer_rule_category_amount" || $rule_key == "offer_rule_category_total")
                        continue;

                    $bool = false;
                    switch ($rule_key) {

                        case "default_rule_show_offer" :
                            $bool = true;
                            break;

                        case "offer_rule_total_ordered_less" :

                            if (isset($details ['offer_rule_order_total']) && $details ['offer_rule_order_total'] <= $rule_value) {
                                $bool = true;
                            }

                            break;

                        case "offer_rule_total_ordered_more" :

                            if (isset($details ['offer_rule_order_total']) && $details ['offer_rule_order_total'] >= $rule_value) {
                                $bool = true;
                            }

                            break;

                        case "offer_rule_cart_total_less" :

                            if (isset($details ['offer_rule_total']) && $details ['offer_rule_total'] <= $rule_value) {
                                $bool = true;
                            }

                            break;

                        case "offer_rule_cart_total_more" :

                            if (isset($details ['offer_rule_total']) && $details ['offer_rule_total'] >= $rule_value) {
                                $bool = true;
                            }

                            break;

                        case "offer_rule_cart_grand_total_less" :

                            if (isset($details ['offer_rule_grand_total']) && $details ['offer_rule_grand_total'] <= $rule_value) {
                                $bool = true;
                            }

                            break;

                        case "offer_rule_cart_grand_total_more" :

                            if (isset($details ['offer_rule_grand_total']) && $details ['offer_rule_grand_total'] >= $rule_value) {
                                $bool = true;
                            }

                            break;

                        case "offer_rule_registered_period" :

                            switch ($rule_value) {
                                case "one_month" :

                                    if (isset($details ['offer_rule_registered_period']) && $details ['offer_rule_registered_period'] < 1) {
                                        $bool = true;
                                    }
                                    break;
                                case "three_month" :

                                    if (isset($details ['offer_rule_registered_period']) && $details ['offer_rule_registered_period'] < 3) {
                                        $bool = true;
                                    }
                                    break;
                                case "six_month" :

                                    if (isset($details ['offer_rule_registered_period']) && $details ['offer_rule_registered_period'] < 6) {
                                        $bool = true;
                                    }
                                    break;
                                case "less_than_1_year" :

                                    if (isset($details ['offer_rule_registered_period']) && $details ['offer_rule_registered_period'] < 12) {
                                        $bool = true;
                                    }
                                    break;
                                case "more_than_1_year" :

                                    if (isset($details ['offer_rule_registered_period']) && $details ['offer_rule_registered_period'] > 12) {
                                        $bool = true;
                                    }
                                    break;
                            }

                            break;

                        case "offer_rule_registered_user" :

                            if (isset($details ['offer_rule_registered_user']) && $details ['offer_rule_registered_user'] == $rule_value) {
                                $bool = true;
                            }

                            break;

                        case "offer_rule_user_role" :

                            if (isset($details ['offer_rule_user_role']) && $details ['offer_rule_user_role'] == $rule_value) {
                                $bool = true;
                            }

                            break;

                        case "offer_rule_user_role_not" :

                            if (isset($details ['offer_rule_user_role']) && $details ['offer_rule_user_role'] != $rule_value) {
                                $bool = true;
                            }

                            break;

                        case "offer_rule_cart_contains" :

                            $rule_cart_contains = explode(",", $rule_value);
                            $cart_contain_val = (count(array_intersect($rule_cart_contains, $user_cart_contains)) == count($rule_cart_contains)) ? 1 : 0;
                            
                            if ($cart_contain_val == 1) {
                                $bool = true;
                            }

                            break;

                        case "offer_rule_cart_doesnot_contains" :

                            $rule_cart_doesnot_contains = explode(",", $rule_value);

                            $cart_doesnot_contain_val = (count(array_intersect($rule_cart_doesnot_contains, $user_cart_contains)) == 0 ) ? 1 : 0;
                            if ($cart_doesnot_contain_val == 1) {
                                $bool = true;
                            }
                            break;

                        case "offer_rule_has_bought" :

                            $rule_has_bought = explode(",", $rule_value);
                            $user_bought_val = (count(array_intersect($rule_has_bought, $user_has_bought)) == count($rule_has_bought)) ? 1 : 0;
                            if ($user_bought_val == 1) {
                                $bool = true;
                            }

                            break;

                        case "offer_rule_not_bought" :

                            $rule_not_bought = explode(",", $rule_value);
                            $user_not_bought_val = (count(array_intersect($rule_not_bought, $user_has_bought)) == 0) ? 1 : 0;
                            if ($user_not_bought_val == 1) {
                                $bool = true;
                            }

                            break;

                        case "offer_rule_offer_valid_between" :

                            if (isset($rule_value['offer_valid_from']) && !empty($rule_value['offer_valid_from'])) {

                                if (current_time('timestamp') >= $rule_value['offer_valid_from']) {

                                    $bool = true;

                                    if (isset($rule_value['offer_valid_till']) && !empty($rule_value['offer_valid_till'])) {

                                        if (current_time('timestamp') <= $rule_value['offer_valid_till']) {
                                            $bool = true;
                                        } else {
                                            $bool = false;
                                        }
                                    }
                                }
                            }

                            break;

                        case "offer_rule_cart_prod_categories_is" :

                            $rule_contains_categories = $rule_value;

                            if (array_key_exists($rule_contains_categories, $cart_category_details)) {

                                if (!empty($value['offer_rule_category_amount'])) {

                                    $cart_amount = $cart_category_details[$rule_contains_categories];

                                    if ($value['offer_rule_category_total'] == "category_total_less") {
                                        if ($cart_amount <= $value['offer_rule_category_amount']) {
                                            $bool = true;
                                        }
                                    } elseif ($value['offer_rule_category_total'] == "category_total_more") {
                                        if ($cart_amount >= $value['offer_rule_category_amount']) {
                                            $bool = true;
                                        }
                                    }
                                } else {
                                    $bool = true;
                                }
                            } else {
                                $bool = false;
                            }

                            break;

                        case "offer_rule_cart_prod_categories_not_is" :

                            $rule_not_contains_categories = $rule_value;

                            if (!empty($cart_category_details) && !array_key_exists($rule_not_contains_categories, $cart_category_details)) {
                                $bool = true;
                            } else {
                                $bool = false;
                            }

                            break;
                    }
                    if ($bool === false) {
                        $key = array_search($offer_id, $valid_offers_id);
                        unset($valid_offers_id [$key]);
                        break 1;
                    }
                }
            }
            $validated_offers_id = array();

            foreach ($valid_offers_id as $id) {
                if ($id != '') {
                    $so_offer = new SO_Offer();
                    $offer_price = $so_offer->get_offer_price(array('offer_id' => $id));
                    $validated_offers_id [$id] = $offer_price;
                }
            }
            
            return $validated_offers_id;
        }

        /**
	 * return details of the current user.
	 */
        function get_user_details($page, $order) {
            global $current_user, $wpdb;

            $wc_compat = SA_Smart_Offers::wc_compat();

            $registered_user = ($current_user->ID != 0) ? 'yes' : 'no';
            $user_role = ( $current_user->ID != 0 ) ? $current_user->roles[0] : '';
            $current_order_id = ( $page == 'thankyou_page') ? $order->id : '';

            $post_status_query_part = "AND post.post_status = 'publish'";

            if ( $wc_compat::is_wc_gte_22() ) {
                $post_status_query_part = "AND post.post_status IN ( '" . implode( "','", array_keys( wc_get_order_statuses() ) ) . "' )";
            }

            if ($current_user->ID != 0) {

                $today = date("Y-m-d");
                $registered_date = $current_user->data->user_registered;
                $registered_date = date("Y-m-d", strtotime($registered_date));

                $start_date = strtotime($registered_date);
                $end_date = strtotime($today);

                $year_1 = date('Y', $start_date);
                $year_2 = date('Y', $end_date);

                $month_1 = date('m', $start_date);
                $month_2 = date('m', $end_date);

                $date_1 = date('d', $start_date);
                $date_2 = date('d', $end_date);

                if ($date_2 < $date_1) {
                    $registered_period = (($year_2 - $year_1) * 12) + ($month_2 - $month_1) - 1;
                } else {
                    $registered_period = (($year_2 - $year_1) * 12) + ($month_2 - $month_1);
                }

                $user_email = $current_user->data->user_email;

                $query_to_get_all_orders_id_of_customers = "SELECT post.ID from {$wpdb->prefix}posts as post JOIN {$wpdb->prefix}postmeta as postmeta
                                                                       ON (post.ID = postmeta.post_id)
                                                                       WHERE post.post_type = 'shop_order' {$post_status_query_part}
                                                                       AND postmeta.meta_key = '_customer_user' AND postmeta.meta_value = '$current_user->ID' ";
            } else {

                $registered_period = '';

                if ($page == "thankyou_page") {

                    $user_email = $order->billing_email;

                    $query_to_get_all_orders_id_of_customers = "SELECT post.ID from {$wpdb->prefix}posts as post JOIN {$wpdb->prefix}postmeta as postmeta
                                                                       ON (post.ID = postmeta.post_id)
                                                                       WHERE post.post_type = 'shop_order' AND {$post_status_query_part}
                                                                       AND postmeta.meta_key = '_billing_email' AND postmeta.meta_value = '$user_email' ";
                }
            }

            if (!empty($current_order_id) && $current_order_id != '') {
                $query_to_get_all_orders_id_of_customers .= " AND post.ID NOT IN ( $current_order_id ) ";
            }

            // CODE CHANGES IN QUERY TO MAKE IT COMPATIBLE WITH WC 2.0

            if (($current_user->ID != 0) || ($current_user->ID == 0 && $page == 'thankyou_page')) {

                $get_all_orders_id_of_customers = $wpdb->get_col($query_to_get_all_orders_id_of_customers);

                if (count($get_all_orders_id_of_customers) > 0) {

                    if ( $wc_compat::is_wc_gte_22() ) {
                        $query_to_get_valid_orders = $wpdb->get_col("SELECT ID FROM {$wpdb->posts}
                                                                        WHERE post_type = 'shop_order'
                                                                        AND post_status IN ( 'wc-completed', 'wc-processing', 'wc-on-hold' )
                                                                        AND ID IN (" . implode(',', $get_all_orders_id_of_customers) . ")");
                    } else {
                        $query_to_get_valid_orders = $wpdb->get_col("SELECT tr.object_id from {$wpdb->prefix}term_relationships as tr JOIN {$wpdb->prefix}term_taxonomy as tt
                                                                      ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
                                                                      WHERE tt.taxonomy = 'shop_order_status'
                                                                      AND tt.term_id IN ( SELECT t.term_id from {$wpdb->prefix}terms as t
                                                                                           WHERE t.slug = 'completed' OR t.slug = 'processing' OR t.slug = 'on-hold')
                                                                      AND tr.object_id IN (" . implode(',', $get_all_orders_id_of_customers) . ");
                                                                    ");
                    }
                    
                    if (count($query_to_get_valid_orders) > 0) {

                        $products_id = $orders_total = array();

                        if ($wc_compat::is_wc_gte_20()) {

                            $query_to_all_order_items = "SELECT pm.meta_value as order_total,
                                                                GROUP_CONCAT(order_item_meta.meta_value ORDER BY order_item_meta.meta_key SEPARATOR '###') AS order_items_meta_value
                                                                FROM {$wpdb->prefix}postmeta as pm
                                                                JOIN {$wpdb->prefix}woocommerce_order_items as order_items ON (pm.post_id = order_items.order_id)
                                                                JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON (order_items.order_item_id = order_item_meta.order_item_id ) 
                                                                WHERE pm.meta_key IN ('_order_total')
                                                                AND order_item_meta.meta_key IN ('_product_id','_variation_id')
                                                                AND order_items.order_item_type LIKE 'line_item'
                                                                AND pm.post_id IN ( " . implode(',', $query_to_get_valid_orders) . " )
                                                                GROUP BY pm.post_id

                                                                                                        ";

                            $results = $wpdb->get_results($query_to_all_order_items, ARRAY_A);


                            foreach ($results as $result) {

                                $ids = explode('###', $result ['order_items_meta_value']);

                                foreach ($ids as $id) {
                                    if ($id == '')
                                        continue;
                                    $products_id[] = $id;
                                }

                                $orders_total [] = $result ['order_total'];
                            }
                        } else {

                            $query_to_all_order_items = " SELECT GROUP_CONCAT(pm.meta_value ORDER BY pm.meta_key SEPARATOR '###') FROM {$wpdb->prefix}postmeta as pm
                                                                                                            WHERE pm.meta_key IN ('_order_items','_order_total')
                                                                                                            AND pm.post_id IN ( " . implode(',', $query_to_get_valid_orders) . " )
                                                                                                            GROUP BY pm.post_id";

                            $results = $wpdb->get_col($query_to_all_order_items);

                            foreach ($results as $result) {

                                $data = explode('###', $result);

                                $unserialized_data = maybe_unserialize($data [0]);

                                if (!empty($unserialized_data)) {
                                    foreach ($unserialized_data as $unserialize_data) {
                                        if ($unserialize_data ['variation_id'] != "") {

                                            $products_id [] = $unserialize_data ['id'];
                                            $products_id [] = $unserialize_data ['variation_id'];
                                        } else {
                                            $products_id [] = $unserialize_data ['id'];
                                        }
                                    }
                                }

                                $orders_total [] = $data [1];
                            }
                        }



                        $products_id = array_unique($products_id);
                        sort($products_id);
                        $products_id = implode(',', $products_id);

                        $total_ordered = array_sum($orders_total);
                    } else {
                        $products_id = '';
                        $total_ordered = '';
                    }
                } else {
                    $products_id = '';
                    $total_ordered = '';
                }
            } else {
                $products_id = '';
                $total_ordered = '';
            }

            $user_details = array('offer_rule_registered_user' => $registered_user, 'offer_rule_registered_period' => $registered_period, 'offer_rule_has_bought' => $products_id, 'offer_rule_order_total' => $total_ordered, 'offer_rule_user_role' => $user_role);
            
            return $user_details;
        }

        /**
	 * return cart details
	 */
        function get_cart_contents() {
            global $woocommerce;
            $cart_contains_products = $found_categories_ids = $found_categories_ids_total = array();

            foreach ($woocommerce->cart->cart_contents as $cart_item) {
                
                if ($cart_item ['variation_id'] != '') {
                    $cart_contains_products [] = $cart_item ['variation_id'];
                    $cart_contains_products [] = $cart_item ['product_id'];
                } else {
                    $cart_contains_products [] = $cart_item ['product_id'];
                }

                $get_prod_category_ids = wp_get_post_terms($cart_item ['product_id'], 'product_cat', array("fields" => "ids"));

                if (count($get_prod_category_ids) > 0) {
                    $line_subtotal = (isset($cart_item ['line_subtotal'])) ? $cart_item ['line_subtotal'] : 0;
                    $get_prod_category_ids = array_fill_keys($get_prod_category_ids, $line_subtotal);
                    $found_categories_ids[] = $get_prod_category_ids;
                }
            }

            foreach ($found_categories_ids as $found_categories_id) {
                foreach ($found_categories_id as $cat_id => $cat_price) {
                    if (isset($found_categories_ids_total[$cat_id])) {
                        $found_categories_ids_total[$cat_id] += $cat_price;
                    } else {
                        $found_categories_ids_total[$cat_id] = $cat_price;
                    }
                }
            }

            $cart_contains_products = array_unique($cart_contains_products);
            asort($cart_contains_products);
            $cart_contains_products = implode(',', $cart_contains_products);

            $cart_total = $woocommerce->cart->cart_contents_total;

            $cart_grand_total = $woocommerce->cart->total;

            $cart_details = array('offer_rule_cart_contains' => $cart_contains_products, 'offer_rule_total' => $cart_total, 'offer_rule_grand_total' => $cart_grand_total, 'offer_rule_cart_category_details' => $found_categories_ids_total);

            return $cart_details;
        }

    }
}
