<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('SO_Offer')) {

    Class SO_Offer {

        function __construct() {
            
        }

        /**
	 * Preparing the valid offer to show
	 */
        function prepare_offer($display_as, $offers_data) {
            global $woocommerce;

            extract( $offers_data );

            $wc_compat = SA_Smart_Offers::wc_compat();

            // Show offer
            if (!empty($offer_data)) {
                $show_offer_as_popup = (!empty($display_as) ) ? $display_as : '';
                $js = $this->get_offer_js();

                $wc_compat::enqueue_js($js);
                
                if (count($offer_data) > 1) {
                    $show_offer_as_popup = "inline";
                }

                foreach ($offer_data as $data) {
                    $this->show_offer($data, $page, $where_url, $show_offer_as_popup);
                }
            } else {
                if ($skipped_offer_id_variable && !empty($where_url)) {
                    ob_clean();

                    if (SO_Session_Handler::check_session_set_or_not($skip_offer_id_variable)) {
                        SO_Session_Handler::so_delete_session($skip_offer_id_variable);
                    }
                    wp_safe_redirect($where_url);
                    exit();
                } else {
                    return;
                }
            }
        }

        /**
	 * Show Offer
	 */
        function show_offer($offer_data, $page, $where_url, $display_as) {
            global $woocommerce;

            $wc_compat = SA_Smart_Offers::wc_compat();

            $offer_id = $offer_data ['post_id'];

            $this->update_accept_skip_count($offer_id, 'offer_shown');

            if (!empty($offer_data)) {
                wp_enqueue_style('so_frontend_css');

                $post_content = $this->return_post_content($offer_id, $page, $where_url);

                if (!empty($display_as)) {
                    if ($display_as == "popup") {
                        $show_offer_as = "offer_as_popup";
                    }
                    if ($display_as == "inline") {
                        $show_offer_as = "offer_as_inline";
                    }
                } else {
                    $show_offer_as = get_post_meta($offer_id, 'so_show_offer_as', true);
                }

                echo apply_filters('the_content', $post_content);

                if ($show_offer_as == "offer_as_inline") {
                    $js = ' jQuery( function() {
                                                    jQuery("#so-offer-content-' . $offer_id . '").css( "display" , "inline" );
                                                }); ';
                } elseif ($show_offer_as == "offer_as_popup") {

                    if (!wp_script_is('jquery')) {
                        wp_enqueue_script('jquery');
                        wp_enqueue_style('jquery');
                    }

                    if (!wp_script_is('thickbox')) {
                        wp_enqueue_script('thickbox');
                    }

                    if (!wp_style_is('thickbox')) {
                        wp_enqueue_style('thickbox');
                    }

                    $js = 'jQuery( function() {
                                                      jQuery(document).ready(function() {
                                                            tb_show( jQuery("#so-offer-content-' . $offer_id . '").attr("data-title"),"#TB_inline?modal=true&amp;inlineId=so-offer-content-' . $offer_id . '",false);
                                                            
                                                            setTimeout(function() {
                                                                jQuery("#TB_ajaxContent").css( "height" , "auto" );
                                                                jQuery("#TB_ajaxContent").css( "width" , "auto" );
                                                                jQuery("#TB_window").css( "top" , "350px" );
                                                                jQuery("#TB_window").css( "position" , "absolute" );

                                                            }, 300 );
                                                        });
                                                });';
                }
                $wc_compat::enqueue_js($js);
            } else {
                return;
            }
        }
        /**
	 * Return necessary js when showing offer
	 */
        function get_offer_js() {
            $js = ' jQuery(document).ready(function(){
                                            
                                            function hide_add_to_cart( form_id ){
                                            
                                                if( form_id != "" ){
                                                    var str_length = ("so_addtocart_").length;
                                                    var offer_id = form_id.substr(str_length);
                                                    setTimeout(function() {
                                                
                                                        var product_id = jQuery("form#"+form_id).attr("data-product_id");
                                                        var variation_id = jQuery("form#"+form_id + " input[name=variation_id]").val();
                                                        var all_variations = jQuery("form#"+form_id).data( "product_variations" );
                                                        var form_qty_id = "so_qty_"+ offer_id;
                                                        
                                                        if( variation_id ){

                                                                // Fallback to window property if not set - backwards compat
                                                                if ( ! all_variations )
                                                                        all_variations = window[ "product_variations_" + product_id ];

                                                                jQuery.each(all_variations, function(i, obj) {
                                                                
                                                                    jQuery("div#so-entry-content-"+ offer_id + " div.so_skip").show();
                                                                    jQuery("div#so-entry-content-"+ offer_id + " div.so_skip a[href*=\'so_action=skip\']").show();
                                                                    if( obj.variation_id == variation_id ){

                                                                        if ( ! obj.is_in_stock && ! obj.backorders_allowed ) {
                                                                                jQuery("div#so-entry-content-"+ offer_id + " div.so_accept").hide();
                                                                                jQuery("div#so-entry-content-"+ offer_id + " div.so_accept a[href*=\'so_action=accept\']").hide();
                                                                        } else {
                                                                            jQuery("div#so-entry-content-"+ offer_id + " div.so_accept").show();
                                                                            jQuery("div#so-entry-content-"+ offer_id + " div.so_accept a[href*=\'so_action=accept\']").show();
                                                                        }

                                                                    }

                                                                });
                                                                
                                                                if( jQuery("form#"+ form_qty_id).length > 0 && jQuery("form#"+ form_qty_id).is(".allow_change") ) {
                                                                
                                                                    jQuery("form#"+ form_qty_id).show();

                                                                }
                                                                
                                                            } else {
                                                            
                                                                jQuery("div#so-entry-content-"+ offer_id + " div.so_accept").hide();
                                                                jQuery("div#so-entry-content-"+ offer_id + " div.so_accept a[href*=\'so_action=accept\']").hide();
                                                                jQuery("div#so-entry-content-"+ offer_id + " div.so_skip").hide();
                                                                jQuery("div#so-entry-content-"+ offer_id + " div.so_skip a[href*=\'so_action=skip\']").hide();
                                                                
                                                                if( jQuery("form#"+ form_qty_id).length > 0 && jQuery("form#"+ form_qty_id).is(":visible")){
                                                                    jQuery("form#"+ form_qty_id).hide();
                                                                    jQuery("form#"+ form_qty_id).addClass("allow_change");
                                                                } 
                                                            }   


                                                    }, 100 );
                                                }
                                            }
                                            
                                            var variation_form = jQuery("form").closest("div[id^=\'so-entry-content-\'] .variations_form");
                                            
                                            if( variation_form.length > 0 ) {
                                                jQuery.each( variation_form, function( key, value ) {
                                                    var form_id = jQuery(value).attr("id");
                                                    hide_add_to_cart( form_id );
                                                    
                                                });
                                                

                                                jQuery(".variations select").change(function(){
                                                        var selected_form_id = jQuery(this).closest(".variations_form").attr("id");
                                                        hide_add_to_cart( selected_form_id );
                                                });
                                            }
                                            
                                            jQuery("a[href*=\'so_action=accept\']").click(function(e){

                                                    var selected_offer_id = jQuery(this).parent().closest("div.entry-content").find("input#so-offer-id").val();
                                                    var current_form_id = jQuery(this).parent().closest("div.entry-content").find("form#so_addtocart_"+selected_offer_id).length;
                                                    var current_qty_form_id = jQuery(this).parent().closest("div.entry-content").find("form#so_qty_"+selected_offer_id).length;

                                                    if( current_form_id > 0 ){
                                                        e.preventDefault();
                                                        jQuery("#so_addtocart_"+selected_offer_id).submit();
                                                    }
                                                    
                                                    if( current_qty_form_id > 0 ){
                                                    
                                                        e.preventDefault();
                                                        
                                                        if( current_form_id > 0 ){
                                                            var form_id = "so_hidden_form_" + selected_offer_id;
                                                            jQuery("<div id="+form_id+"></div>"  ).appendTo("#so_qty_"+selected_offer_id).hide();
                                                            jQuery("#so_addtocart_"+selected_offer_id + " input" ).appendTo("#so_hidden_form_"+selected_offer_id);
                                                            jQuery("#so_addtocart_"+selected_offer_id + " select" ).appendTo("#so_hidden_form_"+selected_offer_id);
                                                        }
                                                        jQuery("#so_qty_"+selected_offer_id).submit();
                                                    }
                                            });
                                            
                                        }); ';

            return $js;
        }

        /**
	 * Calculate Offer price
	 */
        function get_offer_price($offer_data) {

            $wc_compat = SA_Smart_Offers::wc_compat();

            if (isset($offer_data['offer_id'])) {
                $offer_id = $offer_data['offer_id'];
            }

            if (isset($offer_data['prod_id']) && $offer_data['prod_id'] != '') {
                $offered_prod_id = $offer_data['prod_id'];
            } else {
                $offered_prod_id = get_post_meta($offer_id, 'target_product_ids', true);
            }

            $offered_prod_instance = SO_Product_Details::get_product_instance($offered_prod_id);

            $priority = get_option('woo_sm_offers_if_multiple');

            if ($wc_compat::is_wc_gte_20()) {

                if ($offered_prod_instance->product_type == 'simple' || $offered_prod_instance->product_type == 'variable') {

                    // fetch price of the product
                    $price = $offered_prod_instance->get_price();
                } elseif ($offered_prod_instance->product_type == 'variable') {

                    if ($priority == "high_price" || $priority == "random") {

                        $price = get_post_meta($offered_prod_id, '_max_variation_price', true);
                    } elseif ($priority == "low_price") {

                        $price = get_post_meta($offered_prod_id, '_min_variation_price', true);
                    }
                }
            } else {

                if ($offered_prod_instance->product_type == 'simple' || ( $offered_prod_instance->product_type == 'variable' && isset($offered_prod_instance->variation_id) )) {
                    $price = $offered_prod_instance->get_price();
                } elseif ($offered_prod_instance->product_type == 'variable' && !isset($offered_prod_instance->variation_id)) {

                    if ($priority == "high_price" || $priority == "random") {

                        $price = get_post_meta($offered_prod_id, '_max_variation_price', true);
                    } elseif ($priority == "low_price") {

                        $price = get_post_meta($offered_prod_id, '_min_variation_price', true);
                    }
                }
            }

            $discount_type = get_post_meta($offer_id, 'discount_type', true);
            $discount_price = get_post_meta($offer_id, 'offer_price', true);


            // Calculating discount price
            switch ($discount_type) {

                case "fixed_price" :
                    $offer_price = $discount_price;
                    break;
                case "price_discount" :
                    $offer_price = $price - $discount_price;
                    break;
                case "percent_discount" :
                    $percent_discount = ( $price != 0 ) ? ( $discount_price / 100 ) * $price : 0;
                    $offer_price = $price - $percent_discount;
                    break;
            }

            $offer_price = ( $offer_price < 0 ) ? 0 : $offer_price;
            return $offer_price;
        }

        /**
	 * modify shortcode params and return Offer description/content
	 */
        function return_post_content($offer_id, $page, $where_url) {

            $post = get_post($offer_id);
            $post_content = $post->post_content;
            $post_content .= '<input type="hidden" id="so-offer-id" value="' . $offer_id . '">';

//                          ============ Modifying shortcode [so_accept_link] / Modifying shortcode [so_skip_link] =====

            $shortcode_accept_start = strpos($post_content, '[so_acceptlink');

            if ($shortcode_accept_start !== false) {
                $shortcode_accept_end = strpos($post_content, "]", $shortcode_accept_start);
                if ($shortcode_accept_end !== false) {
                    $shortcode_accept_length = $shortcode_accept_end - $shortcode_accept_start + 1;
                    $shortcode_accept_string = substr($post_content, $shortcode_accept_start, $shortcode_accept_length);
                    $new_accept_shortcode = "[so_acceptlink offer_id=" . $offer_id . " page_url=" . urlencode($where_url) . " ]";
                    $post_content = str_replace($shortcode_accept_string, $new_accept_shortcode, $post_content);
                }
            }

            $shortcode_skip_start = strpos($post_content, '[so_skiplink');
            if ($shortcode_skip_start !== false) {
                $shortcode_skip_end = strpos($post_content, "]", $shortcode_skip_start);
                if ($shortcode_skip_end !== false) {
                    $shortcode_skip_length = $shortcode_skip_end - $shortcode_skip_start + 1;
                    $shortcode_skip_string = substr($post_content, $shortcode_skip_start, $shortcode_skip_length);
                    $new_skip_shortcode = "[so_skiplink offer_id=" . $offer_id . " page_url=" . urlencode($where_url) . " ]";
                    $post_content = str_replace($shortcode_skip_string, $new_skip_shortcode, $post_content);
                }
            }

//                          ============================================================================================
//                          ============ Modifying shortcode [so_product_variants] =====================================

            $offered_product = get_post_meta($post->ID, 'target_product_ids', true);
            $offered_prod_instance = SO_Product_Details::get_product_instance($offered_product);
            if ($offered_prod_instance->product_type == "variable") {

                $shortcode_start = strpos($post_content, '[so_product_variants');
                $shortcode_end = strpos($post_content, "]", $shortcode_start);

                if ($shortcode_start !== false && $shortcode_end !== false) {
                    $shortcode_length = $shortcode_end - $shortcode_start + 1;
                    $shortcode_string = substr($post_content, $shortcode_start, $shortcode_length);
                    $new_shortcode = "[so_product_variants prod_id=" . $offered_product . " offer_id=" . $offer_id . " page=" . $page . " where_url=" . $where_url . "]";
                    $post_content = str_replace($shortcode_string, $new_shortcode, $post_content);
                }
            }
//                          ============================================================================================
//                          ============ Modifying shortcode [so_quantity] =====================================

            $shortcode_quantity_start = strpos($post_content, '[so_quantity');
            if ($shortcode_quantity_start !== false) {
                $shortcode_quantity_end = strpos($post_content, "]", $shortcode_quantity_start);
                if ($shortcode_quantity_end !== false) {

                    $shortcode_quantity_length = $shortcode_quantity_end - $shortcode_quantity_start + 1;
                    $shortcode_quantity_string = substr($post_content, $shortcode_quantity_start, $shortcode_quantity_length);
                    $shortcode_qty_substr_length = ( $shortcode_quantity_end - 1 ) - $shortcode_quantity_start + 1;
                    $new_qty_shortcode = substr($post_content, $shortcode_quantity_start, $shortcode_qty_substr_length);
                    $new_qty_shortcode .= " prod_id=" . $offered_product . " offer_id=" . $offer_id . " page=" . $page . " where_url=" . $where_url . "]";
                    $post_content = str_replace($shortcode_quantity_string, $new_qty_shortcode, $post_content);
                }
            }

            return '<div class="so-offer-content" id="so-offer-content-' . $offer_id . '" style="display:none;"><div id="so-entry-content-' . $offer_id . '" class="entry-content woocommerce" >' . $post_content . '</div></div>';
        }

        /**
	 * Change accept/skip count of an offer
	 */
        function update_accept_skip_count($current_offer_id, $meta_key) {
            $accept_skip_counter = get_post_meta($current_offer_id, 'so_accept_skip_counter', true);

            $count = (empty($accept_skip_counter) || !array_key_exists($meta_key, $accept_skip_counter)) ? 1 : ++$accept_skip_counter [$meta_key];

            $accept_skip_counter [$meta_key] = $count;

            update_post_meta($current_offer_id, 'so_accept_skip_counter', $accept_skip_counter);
        }
        
        /**
	 * Action to perform when offer is accepted
	 */
        function action_on_accept_offer($post_id, $page, $parent_offer_id, $variation_data) {
            global $woocommerce;

            if (isset($variation_data['variation_id']) && $variation_data['variation_id'] != '') {
                $target_product_id = $variation_data['variation_id'];
            } else {
                $target_product_id = get_post_meta($post_id, 'target_product_ids', true);
            }

            $quantity = ( isset($variation_data['quantity']) && !empty($variation_data['quantity']) ) ? $variation_data['quantity'] : 1;

            if (!empty($target_product_id)) {

                if (isset($variation_data['variation_id']) && $variation_data['variation_id'] != '') {

                    $all_variations_set = true;
                    $parent_id = $variation_data['parent_prod_id'];
                    $adding_to_cart = SO_Product_Details::get_product_instance($parent_id); //get_product( $parent_id );
                    $attributes = $adding_to_cart->get_attributes();
                    $variation_id = $variation_data['variation_id'];
                    $variation_instance = SO_Product_Details::get_product_instance($variation_id); //get_product( $variation_id );

                    foreach ($attributes as $attribute) {

                        if (!$attribute['is_variation'])
                            continue;

                        $taxonomy = 'attribute_' . sanitize_title($attribute['name']);

                        if (!empty($_POST[$taxonomy])) {

                            // Get value from post data
                            // Don't use woocommerce_clean as it destroys sanitized characters
                            $value = sanitize_title(trim(stripslashes($_POST[$taxonomy])));

                            // Get valid value from variation
                            $valid_value = $variation_instance->variation_data[$taxonomy];

                            // Allow if valid
                            if ($valid_value == '' || $valid_value == $value) {
                                if ($attribute['is_taxonomy'])
                                    $variation[esc_html($attribute['name'])] = $value;
                                else {
                                    // For custom attributes, get the name from the slug
                                    $options = array_map('trim', explode('|', $attribute['value']));
                                    foreach ($options as $option) {
                                        if (sanitize_title($option) == $value) {
                                            $value = $option;
                                            break;
                                        }
                                    }
                                    $variation[esc_html($attribute['name'])] = $value;
                                }
                                continue;
                            }
                        }


                        $all_variations_set = false;
                    }
                } else {

                    $target_product_instance = SO_Product_Details::get_product_instance($target_product_id);

                    if (isset($target_product_instance->variation_id)) {
                        $parent_id = $target_product_instance->id;
                        $variation_id = $target_product_instance->variation_id;
                        $variation = $target_product_instance->variation_data;
                    } else {
                        $parent_id = $target_product_instance->id;
                        $variation_id = '';
                        $variation = '';
                    }
                }

                // Storing offer rules of parent in case of skipped offers
                if ((!empty($parent_offer_id) ) || ( $page == "cart_page" || $page == "checkout_page" || $page == "myaccount_page" || $page == "home_page" || $page == "any_page")) {
//                                            $offer_rules = get_post_meta( $parent_offer_id, '_offer_rules', true ) ;
                    $offer_rules = get_post_meta($post_id, '_offer_rules', true);
                }

                $action_on_accept = get_post_meta($post_id, 'so_actions_on_accept', true);

                if (!empty($action_on_accept)) {
                    if (isset($action_on_accept['remove_prods_from_cart'])) {
                        $products_to_be_removed = $action_on_accept['remove_prods_from_cart'];
                    }
                }

                if (!empty($offer_rules)) {

                    foreach ($offer_rules as $key => $val) {
                        if ($val ["offer_action"] == "cart_contains") {
                            $cart_contains = $val ['offer_rule_value'];
                        }
                    }

                    $cart_contains = ( isset($cart_contains) ) ? explode(",", $cart_contains) : array();
                    $products_to_be_removed = ( isset($products_to_be_removed) && !empty($products_to_be_removed) ) ? explode(",", $products_to_be_removed) : array();

                    foreach ($products_to_be_removed as $prod_ids) {
                        $prod_parent_id = wp_get_post_parent_id($prod_ids);
                        if (!empty($prod_parent_id)) {
                            $products_to_be_removed[] = $prod_parent_id;
                        }
                    }

                    $cart_contains_item_key = array();
                    $keys_of_products_removed = array();

                    if (!empty($cart_contains)) {

                        if (count($products_to_be_removed) > 0) {
                            $cart_contains = array_diff($cart_contains, $products_to_be_removed);
                        }

                        foreach ($cart_contains as $id) {
                            foreach ($woocommerce->cart->cart_contents as $key => $values) {
                                if ($id == $values['product_id'] || $id == $values['variation_id']) {
                                    $cart_contains_item_key[] = $key;
                                }
                            }
                        }
                    }


                    if (count($products_to_be_removed) > 0) {
                        foreach ($products_to_be_removed as $p_id) {
                            foreach ($woocommerce->cart->cart_contents as $key => $values) {
                                if ($p_id == $values['product_id'] || $p_id == $values['variation_id']) {
                                    $keys_of_products_removed[] = $key;
                                }
                            }
                        }
                    }

                    if (!empty($keys_of_products_removed)) {

                        if (count($cart_contains_item_key) > 0) {
                            $cart_contains_item_key = array_diff($cart_contains_item_key, $keys_of_products_removed);
                        }
                        if (count($cart_contains) < 0) {
                            $cart_contains = array();
                        }
                        if (count($cart_contains_item_key) < 0) {
                            $cart_contains_item_key = array();
                        }


                        if (isset($parent_offer_id) && !empty($parent_offer_id)) {
                            $parent_offer_ids = array();
                            array_push($parent_offer_ids, $parent_offer_id);
                        } else {
                            $parent_offer_ids = array();
                        }

                        $cart = $woocommerce->cart->cart_contents;
                        foreach ($keys_of_products_removed as $cart_key) {

                            if (isset($cart[$cart_key]['smart_offers'])) {
                                if (is_array($cart[$cart_key]['smart_offers']['cart_contains_keys']) && count($cart[$cart_key]['smart_offers']['cart_contains_keys']) > 0) {
                                    $cart_contains_item_key = array_unique(array_merge($cart_contains_item_key, $cart[$cart_key]['smart_offers']['cart_contains_keys']));
                                }

                                if (is_array($cart[$cart_key]['smart_offers']['cart_contains_ids']) && count($cart[$cart_key]['smart_offers']['cart_contains_ids']) > 0) {
                                    $cart_contains = array_unique(array_merge($cart_contains, $cart[$cart_key]['smart_offers']['cart_contains_ids']));
                                }

                                if (isset($cart[$cart_key]['smart_offers']['parent_offer_id']) && !empty($cart[$cart_key]['smart_offers']['parent_offer_id'])) {
                                    if (is_array($cart[$cart_key]['smart_offers']['parent_offer_id'])) {
                                        $parent_offer_id = array_unique(array_merge($parent_offer_ids, $cart[$cart_key]['smart_offers']['parent_offer_id']));
                                    } else {
                                        array_push($parent_offer_ids, $cart[$cart_key]['smart_offers']['parent_offer_id']);
                                    }
                                }
                            }
                            unset($woocommerce->cart->cart_contents[$cart_key]);
                        }

                        if (is_array($parent_offer_ids) && count($parent_offer_ids) > 0) {
                            $parent_offer_id = $parent_offer_ids;
                        }
                    }

                    if (!empty($cart_contains) && is_array($cart_contains) && !empty($cart_contains_item_key)) {
                        $args ['smart_offers'] = array('accept_offer' => true, 'offer_id' => $post_id, 'accepted_from' => $page, 'cart_contains_keys' => $cart_contains_item_key, 'cart_contains_ids' => $cart_contains);
                    } else {
                        $args ['smart_offers'] = array('accept_offer' => true, 'offer_id' => $post_id, 'accepted_from' => $page);
                    }
                } else {
                    $args ['smart_offers'] = array('accept_offer' => true, 'offer_id' => $post_id, 'accepted_from' => $page);
                }

                if (is_array($parent_offer_id) && count($parent_offer_id) > 0) {
                    $args ['smart_offers']['parent_offer_id'] = $parent_offer_id;
                } elseif (!is_array($parent_offer_id) && $parent_offer_id != '') {
                    $args ['smart_offers']['parent_offer_id'] = $parent_offer_id;
                }

                $add_to_cart = $woocommerce->cart->add_to_cart($parent_id, $quantity, $variation_id, $variation, $args);


                if ($add_to_cart == true) {

                    $action_on_accept = get_post_meta($post_id, 'so_actions_on_accept', true);

                    if (isset($action_on_accept['sa_apply_coupon']) && !empty($action_on_accept['sa_apply_coupon'])) {
                        $coupons = explode(",", $action_on_accept['sa_apply_coupon']);
                        if (is_array($coupons) && count($coupons) > 0) {
                            foreach ($coupons as $coupon_title) {
                                $woocommerce->cart->add_discount($coupon_title);
                            }
                        }
                    }

                    if (isset($action_on_accept['sa_redirect_to_url']) && !empty($action_on_accept['sa_redirect_to_url'])) {
//                                                
                        $url = $action_on_accept['sa_redirect_to_url'];
                    } elseif (isset($action_on_accept['buy_now']) && $action_on_accept['buy_now'] == true && class_exists('WC_Buy_Now')) {

                        $buy_now = new WC_Buy_Now();
                        $buy_now->checkout_redirect();
                    } else {
                        if ($page == "cart_page") {
                            $url = $woocommerce->cart->get_cart_url();
                        } else {
                            $url = $woocommerce->cart->get_checkout_url();
                        }
                    }
                } else {
                    $url = ( wp_get_referer() ) ? wp_get_referer() : $woocommerce->cart->get_cart_url();
                }

                if ($url) {
                    
                        ob_clean();
                        wp_redirect($url);
                    
                }
            }
        }
        
        function action_on_skip_offer() {
            
        }

    }
}
