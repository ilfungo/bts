<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('SO_Admin_Offer')) {

    Class SO_Admin_Offer {

        function __construct() {
            add_action('add_meta_boxes', array(&$this, 'add_smart_offers_custom_box'));
            add_action('save_post', array(&$this, 'on_process_offers_meta'), 10, 2);

            add_action('wp_ajax_woocommerce_json_search_offers', array(&$this, 'woocommerce_json_search_offers'), 1, 2);
            add_action('wp_ajax_woocommerce_json_search_prod_category', array(&$this, 'woocommerce_json_search_prod_category'), 1, 2);
            add_action('wp_ajax_woocommerce_json_search_coupons', array(&$this, 'woocommerce_json_search_coupons'), 1, 2);
            add_action('wp_ajax_woocommerce_json_search_products_and_only_variations', array(&$this, 'woocommerce_json_search_products_and_only_variations'), 1, 2);
            add_action('admin_enqueue_scripts', array(&$this, 'so_admin_style'));


            add_filter('enter_title_here', array(&$this, 'woo_smart_offers_enter_title_here'), 1, 2);
            add_filter('default_content', array(&$this, 'so_add_default_content'));
            add_filter('post_updated_messages', array(&$this, 'so_add_custom_messages'));
            // To add product variation shortcode on save post
            add_filter('wp_insert_post_data', array(&$this, 'add_shortcode_in_post_content'));
        }

        /**
	 * Save meta data for Smart Offers
	 */
        function on_process_offers_meta($post_id, $post) {
            global $wpdb;

            if (empty($post_id) || empty($post) || empty($_POST))
                return;
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
                return;
            if (is_int(wp_is_post_revision($post)))
                return;
            if (is_int(wp_is_post_autosave($post)))
                return;
            if (empty($_POST ['woocommerce_meta_nonce']) || !wp_verify_nonce($_POST ['woocommerce_meta_nonce'], 'woocommerce_save_data'))
                return;
            if (!current_user_can('edit_post', $post_id))
                return;
            if ($post->post_type != 'smart_offers')
                return;

            $wc_compat = SA_Smart_Offers::wc_compat();

            $offer_rules = array(); // array to store data in serialized format
            // Delete product rules, but not the pages they need to be shown on...
            $delete_query = "DELETE FROM {$wpdb->prefix}postmeta where meta_key like 'offer_rule_%' and meta_key not like 'offer_rule_%_page' and meta_key != 'offer_rule_page_options' and post_id = $post_id ";
            $wpdb->query($delete_query);

            clean_post_cache( $post_id );
            
            if (isset($_POST ['offer_type'])) {

                $offer_type = $_POST ['offer_type'];
                $offer_action = $_POST ['offer_action'];
                $price = $_POST ['price'];

                $i = 0;
                foreach ($offer_type as $offer_key => $value) {

                    $offer_rules [$i] ['offer_type'] = $offer_type [$offer_key];

                    if ($offer_rules [$i] ['offer_type'] == "offer_valid_between") {

                        $offer_rules [$i] ['offer_action'] = $offer_rules [$i] ['offer_type'];

                        $offer_valid_from = $_POST["_offer_valid_from_" . $offer_key];
                        $offer_valid_till = $_POST["_offer_valid_till_" . $offer_key];

                        // Dates
                        if ($offer_valid_from) {
                            $date_from = strtotime($offer_valid_from);
                        } else {
                            $date_from = strtotime(date('Y-m-d'));
                        }

                        if ($offer_valid_till) {
                            $date_to = strtotime($offer_valid_till);
                        } else {
                            $date_to = '';
                        }

                        if ($offer_valid_till && !$offer_valid_from) {
                            $date_from = strtotime('NOW', current_time('timestamp'));
                        }

                        if ($offer_valid_till && strtotime($offer_valid_till) < strtotime('NOW', current_time('timestamp'))) {

                            $date_from = '';
                            $date_to = '';
                        }

                        $offer_valid_between = array();
                        $offer_valid_between['offer_valid_from'] = $date_from;
                        $offer_valid_between['offer_valid_till'] = $date_to;

                        $offer_rules [$i] ['offer_rule_value'] = $offer_valid_between;
                    } else {
                        $offer_rules [$i] ['offer_action'] = $offer_action [$offer_key];

                        if ($offer_action [$offer_key] == 'cart_total_less' || $offer_action [$offer_key] == 'cart_total_more' || $offer_action [$offer_key] == 'cart_grand_total_less' || $offer_action [$offer_key] == 'cart_grand_total_more' || $offer_action [$offer_key] == 'total_ordered_less' || $offer_action [$offer_key] == 'total_ordered_more') {

                            $offer_rules [$i] ['offer_rule_value'] = $price [$offer_key];
                        } elseif ($offer_action [$offer_key] == 'has_bought' || $offer_action [$offer_key] == 'not_bought' || $offer_action [$offer_key] == 'cart_contains' || $offer_action [$offer_key] == 'cart_doesnot_contains') {

                            $key = "search_product_ids_" . $offer_key;
                            $offer_rules [$i] ['offer_rule_value'] = implode(',', $_POST [$key]);
                        } elseif ($offer_action [$offer_key] == 'registered_user') {

                            $key = "registered_user_action_" . $offer_key;
                            $offer_rules [$i] ['offer_rule_value'] = $_POST [$key];
                        } elseif ($offer_action [$offer_key] == 'registered_period') {

                            $key = "registered_period_action_" . $offer_key;
                            $offer_rules [$i] ['offer_rule_value'] = $_POST [$key];
                        } elseif ($offer_action [$offer_key] == 'user_role') {

                            $key = "user_role_" . $offer_key;
                            $offer_rules [$i] ['offer_rule_value'] = $_POST [$key];
                        } elseif ($offer_action [$offer_key] == 'user_role_not') {

                            $key = "user_role_" . $offer_key;
                            $offer_rules [$i] ['offer_rule_value'] = $_POST [$key];
                        } elseif ($offer_action[$offer_key] == 'cart_prod_categories_is') {

                            $key = "search_category_ids_" . $offer_key;
                            $offer_rules [$i] ['offer_rule_value'] = implode(',', $_POST [$key]);
                            $offer_rules [$i] ['category_total'] = $_POST ['category_total_' . $i];
                            $offer_rules [$i] ['category_amount'] = $_POST ['category_amount_' . $i];
                        } elseif ($offer_action[$offer_key] == 'cart_prod_categories_not_is') {

                            $key = "search_category_ids_" . $offer_key;
                            $offer_rules [$i] ['offer_rule_value'] = implode(',', $_POST [$key]);
                            
                        }
                    }

                    $i++;
                }
            }

            foreach ($offer_rules as $j) {
                if (array_key_exists('offer_action', $j) && array_key_exists('offer_rule_value', $j)) {
                    $meta_key = 'offer_rule_' . $j ['offer_action'];
                    update_post_meta($post_id, $meta_key, $j ['offer_rule_value']);
                    if ($meta_key == "offer_rule_cart_prod_categories_is") {
                        update_post_meta($post_id, "offer_rule_category_total", $j ['category_total']);
                        update_post_meta($post_id, "offer_rule_category_amount", $j ['category_amount']);
                    }
                }
            }

            update_post_meta($post_id, '_offer_rules', $offer_rules);

            if (isset($_POST ['post_title'])) {
                update_post_meta($post_id, 'offer_title', $_POST ['post_title']);
            } else {
                delete_post_meta($post_id, 'offer_title', array());
            }

            if (isset($_POST ['target_product_ids'])) :
                $target_products = array();
                $ids = $_POST ['target_product_ids'];
                foreach ($ids as $id) :
                    if ($id && $id > 0)
                        $target_products [] = $id;
                endforeach
                ;
                update_post_meta($post_id, 'target_product_ids', implode(',', $target_products));
            else :
                update_post_meta($post_id, 'target_product_ids', '');
            endif;

            if (isset($_POST ['offer_price'])) :
                update_post_meta($post_id, 'offer_price', $_POST ['offer_price']);
            else :
                delete_post_meta($post_id, 'offer_price');
            endif;

            if (isset($_POST ['discount_type'])) :
                update_post_meta($post_id, 'discount_type', $_POST ['discount_type']);
            else :
                delete_post_meta($post_id, 'discount_type');
            endif;

            $offer_rule_page_options = array();

            if (isset($_POST ['offer_rule_home_page'])) :
                update_post_meta($post_id, 'offer_rule_home_page', $_POST ['offer_rule_home_page']);
                $offer_rule_page_options [] = "home_page";
            else :
                delete_post_meta($post_id, 'offer_rule_home_page');
            endif;

            if (isset($_POST ['offer_rule_cart_page'])) :
                $offer_rule_page_options [] = "cart_page";
                update_post_meta($post_id, 'offer_rule_cart_page', $_POST ['offer_rule_cart_page']);


            else :
                delete_post_meta($post_id, 'offer_rule_cart_page');
            endif;

            if (isset($_POST ['offer_rule_checkout_page'])) :
                $offer_rule_page_options [] = "checkout_page";
                update_post_meta($post_id, 'offer_rule_checkout_page', $_POST ['offer_rule_checkout_page']);


            else :
                delete_post_meta($post_id, 'offer_rule_checkout_page');
            endif;

            if (isset($_POST ['offer_rule_thankyou_page'])) :
                update_post_meta($post_id, 'offer_rule_thankyou_page', $_POST ['offer_rule_thankyou_page']);
                $offer_rule_page_options [] = "thankyou_page";
            else :
                delete_post_meta($post_id, 'offer_rule_thankyou_page');
            endif;

            if (isset($_POST ['offer_rule_myaccount_page'])) :
                update_post_meta($post_id, 'offer_rule_myaccount_page', $_POST ['offer_rule_myaccount_page']);
                $offer_rule_page_options [] = "myaccount_page";
            else :
                delete_post_meta($post_id, 'offer_rule_myaccount_page');
            endif;

            if (isset($_POST ['offer_rule_any_page'])) :
                update_post_meta($post_id, 'offer_rule_any_page', $_POST ['offer_rule_any_page']);
                $offer_rule_page_options [] = "any_page";
            else :
                delete_post_meta($post_id, 'offer_rule_any_page');
            endif;

            if ($offer_rule_page_options) {
                $page_options_value = implode(',', $offer_rule_page_options);
                update_post_meta($post_id, 'offer_rule_page_options', $page_options_value);
            } else {
                delete_post_meta($post_id, 'offer_rule_page_options');
            }

            if (isset($_POST ['so_show_offer_as'])) :
                update_post_meta($post_id, 'so_show_offer_as', $_POST ['so_show_offer_as']);
            else :
                delete_post_meta($post_id, 'so_show_offer_as');
            endif;

            $actions_on_accept = array();

            $actions_on_accept['add_to_cart'] = true;

            if (isset($_POST ['sa_remove_prods_from_cart'])) {
                $prods_ids_to_remove = array();
                if (isset($_POST ['remove_prods_from_cart']) && count($_POST ['remove_prods_from_cart']) > 0) {
                    $prods_ids_to_remove = $_POST ['remove_prods_from_cart'];
                }

                if (count($prods_ids_to_remove) > 0) {
                    $prod_ids = implode(',', $prods_ids_to_remove);
                    $actions_on_accept[$_POST ['sa_remove_prods_from_cart']] = $prod_ids;
                }
            }

            if (isset($_POST ['sa_apply_coupon'])) {
                $apply_coupons = array();
                if (isset($_POST ['sa_coupon_title']) && count($_POST ['sa_coupon_title']) > 0) {
                    $apply_coupons = $_POST ['sa_coupon_title'];
                }

                if (count($apply_coupons) > 0) {
                    $coupons = implode(',', $apply_coupons);
                    $actions_on_accept[$_POST ['sa_apply_coupon']] = $coupons;
                }
            }

            if (isset($_POST ['sa_redirect_to_url'])) {
                if (isset($_POST ['accept_redirect_url']) && !empty($_POST ['accept_redirect_url'])) {
                    $actions_on_accept[$_POST ['sa_redirect_to_url']] = $_POST ['accept_redirect_url'];
                }
            }

            if (isset($_POST ['sa_buy_now'])) {
                $actions_on_accept['buy_now'] = true;
            }

            if ($actions_on_accept) {
                update_post_meta($post_id, 'so_actions_on_accept', $actions_on_accept);
            } else {
                delete_post_meta($post_id, 'so_actions_on_accept');
            }

            if (isset($_POST ['sa_smart_offer_if_denied'])) {
                update_post_meta($post_id, 'sa_smart_offer_if_denied', $_POST ['sa_smart_offer_if_denied']);
                if ($_POST ['sa_smart_offer_if_denied'] == "url") {
                    $text_option = "text_" . $_POST ['sa_smart_offer_if_denied'];
                    update_post_meta($post_id, 'url', $_POST [$text_option]);
                } elseif ($_POST ['sa_smart_offer_if_denied'] == "offer_page") {
                    if (isset($_POST ['offer_ids'])) {
                        $offers = array();
                        $ids = $_POST ['offer_ids'];
                        foreach ($ids as $id) :
                            if ($id && $id > 0)
                                $offers [] = $id;
                        endforeach
                        ;
                        update_post_meta($post_id, 'url', implode(',', $offers));
                    }
                } elseif ($_POST ['sa_smart_offer_if_denied'] == "particular_page") {
                    update_post_meta($post_id, 'url', $_POST ['page_id']);
                } else {
                    delete_post_meta($post_id, 'url');
                }
            } else {
                update_post_meta($post_id, 'sa_smart_offer_if_denied', "order_page");
                // if its "order_page", then do not save url
                delete_post_meta($post_id, 'url');
            }


            // NEWLY ADDED CODE TO REMOVE SkIPPED IDS FROM CUSTOMERS RECORD IF IT IS UNCHECKED. 
            $skip_permanently = get_post_meta($post_id, 'sa_smart_offer_if_denied_skip_permanently', true);

            if ($skip_permanently && !isset($_POST['sa_smart_offer_if_denied_skip_permanently'])) {

                $query_to_fetch_users_skipped_ids = " SELECT um.user_id,um.meta_value from {$wpdb->prefix}usermeta as um
                                                                            WHERE um.meta_key = 'customer_skipped_offers'
                                    
                                                                        ";
                $results_for_customers_skipped_ids = $wpdb->get_results($query_to_fetch_users_skipped_ids, ARRAY_A);

                $new_skipped_ids = array();

                foreach ($results_for_customers_skipped_ids as $result) {

                    $skipped_ids = maybe_unserialize($result['meta_value']);

                    if (in_array($post_id, $skipped_ids)) {

                        $key = array_search($post_id, $skipped_ids);
                        unset($skipped_ids [$key]);

                        $new_skipped_ids[$result['user_id']] = $skipped_ids;
                    }
                }

                $query_case = array();
                $user_ids = array();

                if (count($new_skipped_ids > 0)) {

                    $wpdb->query("SET SESSION group_concat_max_len=999999");
                    foreach ($new_skipped_ids as $id => $meta_value) {

                        $user_ids[] = $id;
                        $query_case[] = "WHEN " . $id . " THEN '" . $wpdb->_real_escape(maybe_serialize($meta_value)) . "'";
                    }
                    $update_query_for_customer_skipped_ids = " UPDATE {$wpdb->prefix}usermeta  
                                                                                    SET meta_value = CASE user_id " . implode("\n", $query_case) . " 
                                                                                    END 
                                                                                    WHERE user_id IN (" . implode(",", $user_ids) . ")
                                                                                    AND meta_key IN ('customer_skipped_offers')
                                                                                    ";
                }

                $wpdb->query($update_query_for_customer_skipped_ids);
            }

            if (isset($_POST ['sa_smart_offer_if_denied_skip_permanently'])) {
                update_post_meta($post_id, 'sa_smart_offer_if_denied_skip_permanently', $_POST ['sa_smart_offer_if_denied_skip_permanently']);
            } else {
                delete_post_meta($post_id, 'sa_smart_offer_if_denied_skip_permanently');
            }

            $position_accept = strpos($_POST ['content'], '[so_acceptlink]');
            $position_skip = strpos($_POST ['content'], '[so_skiplink]');
            $sc_position = strpos($_POST ['content'], '[so_product_variants]');
            
            if (!$position_accept || !$position_skip) {
                $offered_prod_instance = SO_Product_Details::get_product_instance(implode(',', $target_products));
                $url = admin_url('post.php?action=edit&message=2&post=' . $post_id);
                if ($sc_position === false && ( ($wc_compat::is_wc_gte_20() && $offered_prod_instance->product_type == 'variable') || ((!$wc_compat::is_wc_gte_20() && $offered_prod_instance->product_type == 'variable' && !isset($offered_prod_instance->variation_id)) ) )) {
                    $url = add_query_arg('show_sc_msg', true, $url);
                }
                wp_safe_redirect($url);
                exit();
            }
        }

        /**
	 * Show metaboxes in SO
	 */
        function add_smart_offers_custom_box() {
            //
            add_meta_box('so-whats-the-offer', __("What's the offer? ", 'smart_offers'), array(&$this, 'so_whats_the_offer_meta_box'), 'smart_offers', 'normal', 'high');
            add_meta_box('smart-offers-desc', __('Offer Description', 'smart_offers'), array(&$this, 'so_add_editor'), 'smart_offers', 'normal', 'high');
            add_meta_box('so-where-to-show-offer', __('Which page/s to show this offer on? ', 'smart_offers'), array(&$this, 'so_where_to_show_offer'), 'smart_offers', 'normal', 'high');
            add_meta_box('so-when-to-show-offer', __('When to show this offer? ', 'smart_offers'), array(&$this, 'so_when_to_show_offer'), 'smart_offers', 'normal', 'high');
            add_meta_box('so-action-when-offer-skipped', __('What to do when this offer is accepted/skipped?  ', 'smart_offers'), array(&$this, 'so_when_offer_is_skipped'), 'smart_offers', 'normal', 'high');

            remove_meta_box('woothemes-settings', 'smart_offers', 'normal');
            remove_meta_box('commentstatusdiv', 'smart_offers', 'normal');
            remove_meta_box('slugdiv', 'smart_offers', 'normal');
        }

        /**
	 * Change the post title
	 */
        function woo_smart_offers_enter_title_here($text, $post) {
            if ($post->post_type == 'smart_offers')
                return __('Offer Title', SO_TEXT_DOMAIN);
            return $text;
        }

        /**
	 * Show What's the Offer meta box
	 */
        function so_whats_the_offer_meta_box() {
            global $woocommerce, $post;

            $wc_compat = SA_Smart_Offers::wc_compat();

            $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
            $assets_path = str_replace(array('http:', 'https:'), '', $woocommerce->plugin_url()) . '/assets/';

            if ($wc_compat::is_wc_gte_21()) {

                // Register scripts
                wp_register_script('woocommerce_admin', $woocommerce->plugin_url() . '/assets/js/admin/woocommerce_admin' . $suffix . '.js', array('jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip'), $woocommerce->version);
                wp_register_script('woocommerce_admin_meta_boxes', $woocommerce->plugin_url() . '/assets/js/admin/meta-boxes' . $suffix . '.js', array('jquery', 'jquery-ui-datepicker', 'jquery-ui-sortable', 'accounting', 'round'), $woocommerce->version);
                wp_register_script('woocommerce_admin_meta_boxes_variations', $woocommerce->plugin_url() . '/assets/js/admin/meta-boxes-variations' . $suffix . '.js', array('jquery', 'jquery-ui-sortable'), $woocommerce->version);
                wp_register_script('ajax-chosen', $woocommerce->plugin_url() . '/assets/js/chosen/ajax-chosen.jquery' . $suffix . '.js', array('jquery', 'chosen'), $woocommerce->version);
                wp_register_script('chosen', $woocommerce->plugin_url() . '/assets/js/chosen/chosen.jquery' . $suffix . '.js', array('jquery'), $woocommerce->version);

                wp_enqueue_script('woocommerce_admin');
                wp_enqueue_script('woocommerce_admin_meta_boxes');
                wp_enqueue_script('woocommerce_admin_meta_boxes_variations');
                wp_enqueue_script('ajax-chosen');
                wp_enqueue_script('chosen');

                $woocommerce_witepanel_params = array('ajax_url' => admin_url('admin-ajax.php'), 'search_products_nonce' => wp_create_nonce("search-products"), 'calendar_image' => $woocommerce->plugin_url() . '/assets/images/calendar.png');

                wp_localize_script('woocommerce_admin_meta_boxes', 'woocommerce_admin_meta_boxes', $woocommerce_witepanel_params);
//                                    wp_localize_script( 'woocommerce_admin_meta_boxes_variations', 'woocommerce_admin_meta_boxes_variations', $woocommerce_witepanel_params );
//                                    wp_enqueue_script( 'chosen-rtl', $woocommerce->plugin_url() . '/assets/js/chosen/chosen-rtl' . $suffix . '.js', array( 'jquery' ), $woocommerce->version, true );
                wp_enqueue_style('woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css');
                wp_enqueue_style('woocommerce_chosen_styles', $assets_path . 'css/chosen.css');
            } else {
                // Register scripts
                wp_register_script('woocommerce_admin', $woocommerce->plugin_url() . '/assets/js/admin/woocommerce_admin' . $suffix . '.js', array('jquery', 'jquery-ui-widget', 'jquery-ui-core'), '1.0');
                wp_register_script('woocommerce_writepanel', $woocommerce->plugin_url() . '/assets/js/admin/write-panels' . $suffix . '.js', array('jquery'));
                wp_register_script('ajax-chosen', $woocommerce->plugin_url() . '/assets/js/chosen/ajax-chosen.jquery' . $suffix . '.js', array('jquery'), '1.0');

                wp_enqueue_script('woocommerce_admin');
                wp_enqueue_script('woocommerce_writepanel');
                wp_enqueue_script('ajax-chosen');

                $woocommerce_witepanel_params = array('ajax_url' => admin_url('admin-ajax.php'), 'search_products_nonce' => wp_create_nonce("search-products"), 'calendar_image' => $woocommerce->plugin_url() . '/assets/images/calendar.png');

                wp_localize_script('woocommerce_writepanel', 'woocommerce_writepanel_params', $woocommerce_witepanel_params);

                wp_enqueue_style('woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css');
                wp_enqueue_style('jquery-ui-style', (is_ssl()) ? 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' : 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
                wp_enqueue_style('woocommerce_chosen_styles', $woocommerce->plugin_url() . '/assets/css/chosen.css');
            }
            ?>
            

            <script type="text/javascript">


                jQuery(document).ready(function() {
                    set_unique_offer_product();

                    jQuery('select#target_product_ids').change(function() {
                        set_unique_offer_product();
                    });

                    function set_unique_offer_product() {

                        setTimeout(function() {

                    <?php if ($wc_compat::is_wc_gte_21()) { ?>
                                if (jQuery('div#target_product_ids_chosen ul.chosen-choices li').length >= 2) {

                                    jQuery('div#target_product_ids_chosen ul.chosen-choices li.search-field').css('visibility', 'hidden');
                                    jQuery('div#target_product_ids_chosen div.chosen-drop').css('display', 'none');

                                } else {

                                    jQuery('div#target_product_ids_chosen ul.chosen-choices li.search-field').css('visibility', 'visible');
                                    jQuery('div#target_product_ids_chosen div.chosen-drop').css('display', 'block');

                                }
            <?php } else { ?>

                                if (jQuery('div#target_product_ids_chzn ul.chzn-choices li').length >= 2) {

                                    jQuery('div#target_product_ids_chzn ul.chzn-choices li.search-field').css('visibility', 'hidden');
                                    jQuery('div#target_product_ids_chzn div.chzn-drop').css('display', 'none');

                                } else {

                                    jQuery('div#target_product_ids_chzn ul.chzn-choices li.search-field').css('visibility', 'visible');
                                    jQuery('div#target_product_ids_chzn div.chzn-drop').css('display', 'block');

                                }


            <?php } ?>
                        }, 300);
                    }
                });


            </script>

            <div id="so_whats_offer_panel" class="panel woocommerce_options_panel">
                <p class="form-field"><label for="target_product_ids"><?php
            _e('Offered Product', 'smart_offers');
            ?></label>
                    <select id="target_product_ids" name="target_product_ids[]"
                            class="ajax_chosen_select_products_and_variations" multiple="multiple"
                            data-placeholder="<?php
            _e('Search for a product...', 'smart_offers');
            ?>">
            <?php
            $product_ids = get_post_meta($post->ID, 'target_product_ids', true);
            if ($product_ids) {
                $product_ids = explode(',', $product_ids);
                foreach ($product_ids as $product_id) {
                    $title = SO_Product_Details::get_product_title($product_id);
                    $sku = get_post_meta($product_id, '_sku', true);

                    if (!$title)
                        continue;

                    if (isset($sku) && $sku)
                        $sku = ' (SKU: ' . $sku . ')';

                    echo '<option value="' . $product_id . '" selected="selected">' . $title . $sku . '</option>';
                }
            }
            ?>
                    </select> <img class="help_tip"
                                   data-tip='<?php
                                _e('This product would be shown as an offer and on accepting this offer, this product would be added to cart ', 'smart_offers');
                                ?>'
                                   src="<?php
                                echo $woocommerce->plugin_url();
                                ?>/assets/images/help.png" /></p>

                                <?php
                                $discount_types = array('fixed_price' => __(get_woocommerce_currency_symbol() . ' - Fixed Price', 'smart_offers'),
                                    'price_discount' => __(get_woocommerce_currency_symbol() . ' - Discount', 'smart_offers'),
                                    'percent_discount' => '% - Discount'
                                );
                                ?>

                <p class="form-field offer_price_field ">
                    <label for="offer_price"><?php _e('Offer At', 'smart_offers'); ?></label>
                    <input type="number" step="any" class="short" name="offer_price" id="offer_price" value="<?php echo get_post_meta($post->ID, 'offer_price', true) ?>"> 

                    <select id="discount_type" name="discount_type" class="select short">
                                   <?php
                                   foreach ($discount_types as $key => $value) {

                                       echo "<option value='$key' " . selected($key, get_post_meta($post->ID, 'discount_type', true)) . "> $value </option>";
                                   }
                                   ?>
                    </select>
                    <span class="description"><?php _e('Enter an amount/discount as a promotional price for above offered product e.g. 2.99', 'smart_offers'); ?></span>

                </p>

            </div>
                <?php
            }

            //
            function so_add_editor() {
                global $post;
                ?>
            <script type="text/javascript">
                jQuery(function() {

                    jQuery('a#missing_shortcode').click(function() {
                        if ((jQuery('textarea#content').css('display') == 'none')) {
                            jQuery('textarea#content').css('display', "");
                        }

                        var postContent = jQuery('textarea#content').val();

                        var position = postContent.indexOf('<div class="so_accept">');
                        if (position == -1) {
                            position = postContent.indexOf('<div class="so_skip">');
                        }

                        var trimmedContent = '';
                        if (position > 0) {
                            trimmedContent = postContent.substr(0, position);
                            trimmedContent += '<div class="so_accept"><a href="[so_acceptlink]">Yes, Add to Cart</a></div>';
                            trimmedContent += '<div class="so_skip"><a href="[so_skiplink]">No, Skip this offer</a></div>';

                        } else {

                            trimmedContent = postContent + '<div class="so_accept"><a href="[so_acceptlink]">Yes, Add to Cart</a></div>';
                            trimmedContent += '<div class="so_skip"><a href="[so_skiplink]">No, Skip this offer</a></div>';
                        }

                        jQuery('textarea#content').val(trimmedContent);
                        jQuery('input#publish').trigger('click');
                        return false;
                    });
                });
            </script>

            <?php
            $settings = array('quicktags' => array('buttons' => 'em,strong,link'), 'textarea_name' => 'content', 'quicktags' => true, 'tinymce' => true);

            wp_editor(htmlspecialchars_decode($post->post_content), 'content', $settings);
        }

        /**
	 * Show Where to show Offer meta box
	 */
        function so_where_to_show_offer() {
            global $post;

            $show_offer_as = get_post_meta($post->ID, 'so_show_offer_as', true);
            if (empty($show_offer_as)) {
                $show_offer_as = "offer_as_inline";
            }
            ?>
            <div id="so_where_to_offer" class="panel woocommerce_options_panel">

                <p class="form-field page_option_for_offer_field">
                <fieldset name="page_options">
                    <label id="page_option_for_offer" for="page_option_for_offer">Show this Offer on:</label>
                    <fieldset>
                        <fieldset>
                            <input type="checkbox" id="page_options_1" name="offer_rule_home_page" class="checkbox" value="yes" 
            <?php if (get_post_meta($post->ID, 'offer_rule_home_page', true) == "yes") echo 'checked="checked"'; ?> />
                            <span class="description"><?php _e('Home page as a popup', 'smart_offers'); ?></span>
                        </fieldset>
                        <fieldset>
                            <input type="checkbox" id="page_options_1" name="offer_rule_cart_page" class="checkbox" value="yes" 
            <?php if (get_post_meta($post->ID, 'offer_rule_cart_page', true) == "yes") echo 'checked="checked"'; ?> />
                            <span class="description"><?php _e('Cart page', 'smart_offers'); ?></span>
                        </fieldset>
                        <fieldset>
                            <input type="checkbox" id="page_options_2" name="offer_rule_checkout_page" class="checkbox" value="yes"
            <?php if (get_post_meta($post->ID, 'offer_rule_checkout_page', true) == "yes") echo 'checked="checked"'; ?> />
                            <span class="description"><?php _e('Checkout page', 'smart_offers'); ?></span>
                        </fieldset>
                        <fieldset>
                            <input type="checkbox" id="page_options_3" name="offer_rule_thankyou_page" class="checkbox" value="yes"
            <?php if (get_post_meta($post->ID, 'offer_rule_thankyou_page', true) == "yes") echo 'checked="checked"'; ?> />
                            <span class="description"><?php _e('Order Complete page', 'smart_offers'); ?></span>
                        </fieldset>
                        <fieldset><input type="checkbox" id="page_options_4" name="offer_rule_myaccount_page" class="checkbox" value="yes"
            <?php if (get_post_meta($post->ID, 'offer_rule_myaccount_page', true) == "yes") echo 'checked="checked"'; ?> />
                            <span class="description"><?php _e('My Account page', 'smart_offers'); ?></span>
                        </fieldset>
                        <fieldset><input type="checkbox" id="page_options_5" name="offer_rule_any_page" class="checkbox" value="yes"
            <?php if (get_post_meta($post->ID, 'offer_rule_any_page', true) == "yes") echo 'checked="checked"'; ?> />
                            <span class="description"><?php _e('Any other page where shortcode is added', 'smart_offers'); ?></span>
                        </fieldset>    
                    </fieldset>
                </fieldset>
                <!--</p>-->
            </div>

            <div id="so_offer_as" class="panel woocommerce_options_panel">
                <table class="form-table">
                    <tbody>
                        <tr valign="top" class="">
                            <th scope="row" class="titledesc"><?php _e('Show this Offer as:', 'smart_offers'); ?></th>
                            <td class="forminp forminp-checkbox" id="show-offer-images" >
                                <fieldset class="">
                                    <legend class="screen-reader-text"><span></span></legend>
                                    <label for="img_offer_as_inline">
                                        <div class='sprite show-offer-inline'></div>
                                    </label> 										
                                </fieldset>
                            </td>
                            <td class="forminp forminp-checkbox">
                                <fieldset class="">
                                    <legend class="screen-reader-text"><span></span></legend>
                                    <label for="img_offer_as_popup">
                                        <div class='sprite show-offer-as-lightbox'></div>
                                    </label> 
                                </fieldset>
                            </td>
                        </tr>
                        <tr valign="top" class="">
                            <th scope="row" class="titledesc" id="show-offer-images-labels" ></th>
                            <td >
                                <fieldset class="">
                                    <legend class="screen-reader-text"><span></span></legend>
                                    <label for="offer_as_inline">
                                        <input type="radio" id="offer_as_inline" name="so_show_offer_as" class="checkbox" value="offer_as_inline" 
            <?php if ($show_offer_as == "offer_as_inline") echo 'checked="checked"'; ?> />
                                        <span class="description"><?php _e('Inline with page content', 'smart_offers'); ?></span>
                                </fieldset>
                            </td>
                            <td >
                                <fieldset class="">
                                    <legend class="screen-reader-text"><span></span></legend>
                                    <label for="offer_as_popup">
                                        <input type="radio" id="offer_as_popup" name="so_show_offer_as" class="checkbox" value="offer_as_popup"
            <?php if ($show_offer_as == "offer_as_popup") echo 'checked="checked"'; ?> />
                                        <span class="description"><?php _e('Lightbox / Popup / Modal dialog', 'smart_offers'); ?></span>
                                </fieldset>
                            </td>
                        </tr>


                    </tbody>

                </table>

            </div>                                  
            <?php
        }

        /**
	 * Show Offer Rules meta box
	 */
        function so_when_to_show_offer() {

            include_once("class-so-admin-offer-rule.php");
        }

        /**
	 * Show Action to be taken when offer is accep/skip meta box
	 */
        function so_when_offer_is_skipped() {
            global $woocommerce, $post;

            $wc_compat = SA_Smart_Offers::wc_compat();

            $action_on_accept = get_post_meta($post->ID, 'so_actions_on_accept', true);
            $prod_ids_to_remove =  $apply_coupon = null;
            if (empty($action_on_accept)) {
                $add_to_cart = true;
            } else {

                $add_to_cart = ( isset($action_on_accept['add_to_cart']) && $action_on_accept['add_to_cart'] == true ) ? true : false;

                $buy_now = ( isset($action_on_accept['buy_now']) && $action_on_accept['buy_now'] == true ) ? true : false;

                
                if (isset($action_on_accept['remove_prods_from_cart'])) {
                    $remove_prods_from_cart = true;
                    $prod_ids_to_remove = $action_on_accept['remove_prods_from_cart'];
                }

                
                if (isset($action_on_accept['sa_apply_coupon'])) {
                    $sa_apply_coupon = true;
                    $apply_coupon = $action_on_accept['sa_apply_coupon'];
                }

                if (isset($action_on_accept['sa_redirect_to_url'])) {
                    $sa_redirect_to_url = true;
                }
            }
            ?>
            <script type="text/javascript">

                jQuery(document).ready(function() {

                    jQuery("select.ajax_chosen_select_offers").ajaxChosen({
                        method: 'GET',
                        url: '<?php
            echo admin_url('admin-ajax.php');
            ?>',
                        dataType: 'json',
                        afterTypeDelay: 100,
                        data: {
                            action: 'woocommerce_json_search_offers',
                            security: '<?php
            echo wp_create_nonce("search-offers");
            ?>'
                        }
                    }, function(data) {

                        var terms = {};

                        jQuery.each(data, function(i, val) {
                            terms[i] = val;
                        });

                        return terms;
                    });

                    // Ajax Chosen Coupon Selectors
                    jQuery("select.ajax_chosen_select_coupons").ajaxChosen({
                        method: 'GET',
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        dataType: 'json',
                        afterTypeDelay: 100,
                        data: {
                            action: 'woocommerce_json_search_coupons',
                            security: '<?php echo wp_create_nonce("search-coupons"); ?>'
                        }
                    }, function(data) {

                        var terms = {};

                        jQuery.each(data, function(i, val) {
                            terms[i] = val;
                        });

                        return terms;
                    });

                    jQuery(".accept_input_checkboxes").change(function() {
                        var id = jQuery(this).attr('id');

                        if (id == "sa_redirect_to_url") {
                            if (jQuery('input#buy_now').is(':checked')) {
                                jQuery('input#buy_now').attr('checked', false);
                            }
                        }

                        if (id == "buy_now") {
                            if (jQuery('input#sa_redirect_to_url').is(':checked')) {
                                jQuery('input#sa_redirect_to_url').attr('checked', false);
                            }
                        }

                    });


                });
            </script>
            <div id="so_when_offer_accepted" class="panel woocommerce_options_panel">
                <h3> <?php _e('Actions to take when offer is accepted :', 'smart_offers'); ?></h3></br>
                <fieldset>
                    <input type="checkbox" class="accept_input_checkboxes" name="sa_add_to_cart" id="add_to_cart" <?php if ($add_to_cart == true) echo 'checked="checked"'; ?> value="add_to_cart" >
                    <label class="accept_input_checkboxes" id="add_to_cart" for="add_to_cart">
            <?php _e('Add the offered product to cart', 'smart_offers'); ?></label>
                </fieldset></br>
                <fieldset>
                    <input type="checkbox" class="accept_input_checkboxes" name="sa_remove_prods_from_cart" id="remove_prods_from_cart" <?php if (isset($remove_prods_from_cart) && $remove_prods_from_cart == true) echo 'checked="checked"'; ?> value="remove_prods_from_cart">
                    <label class="accept_input_checkboxes" id="remove_prods_from_cart" for="remove_prods_from_cart">
            <?php _e('Remove following products from the cart', 'smart_offers'); ?></label>
                    <select id="remove_prods_from_cart" name="remove_prods_from_cart[]" class="ajax_chosen_select_products_and_only_variations" multiple="multiple"
                            data-placeholder="<?php _e('Search for a product...', 'smart_offers'); ?>" >
            <?php
            if ($prod_ids_to_remove) {
                $prod_ids_to_remove = explode(',', $prod_ids_to_remove);
                foreach ($prod_ids_to_remove as $product_id) {
                    $title = SO_Product_Details::get_product_title($product_id);
                    $sku = get_post_meta($product_id, '_sku', true);

                    if (!$title)
                        continue;

                    if (isset($sku) && $sku)
                        $sku = ' (SKU: ' . $sku . ')';

                    echo '<option value="' . $product_id . '" selected="selected">' . $title . $sku . '</option>';
                }
            }
            ?>
                    </select>
                </fieldset></br>
                <fieldset>
                    <input type="checkbox" class="accept_input_checkboxes" name="sa_apply_coupon" id="sa_apply_coupon" <?php if (isset($sa_apply_coupon) && $sa_apply_coupon == true) echo 'checked="checked"'; ?> value="sa_apply_coupon">
                    <label class="accept_input_checkboxes" id="sa_redirect_to_url" for="sa_apply_coupon">
                        <?php _e('Apply Coupons', 'smart_offers'); ?></label>
                    <select id="sa_coupon_title" name="sa_coupon_title[]" class="ajax_chosen_select_coupons" multiple="multiple" data-placeholder="<?php _e('Search for a coupon...', 'smart_offers'); ?>">

                                <?php
                                if (!class_exists('WC_Coupon')) {
                                    require_once( WP_PLUGIN_DIR . '/woocommerce/classes/class-wc-coupon.php' );
                                }

                                $all_discount_types = $wc_compat::wc_get_coupon_types();

                                if ($apply_coupon) {
                                    $coupon_titles = explode(',', $apply_coupon);
                                    foreach ($coupon_titles as $coupon_title) {

                                        $coupon = new WC_Coupon($coupon_title);

                                        $discount_type = $coupon->discount_type;

                                        if (isset($discount_type) && $discount_type)
                                            $discount_type = ' ( Type: ' . $all_discount_types[$discount_type] . ' )';

                                        echo '<option value="' . $coupon_title . '" selected="selected">' . $coupon_title . $discount_type . '</option>';
                                    }
                                }
                                ?>
                    </select>
                </fieldset></br>
                <fieldset>
                    <input type="checkbox" class="accept_input_checkboxes" name="sa_redirect_to_url" id="sa_redirect_to_url" <?php if (isset($sa_redirect_to_url) && $sa_redirect_to_url == true) echo 'checked="checked"'; ?> value="sa_redirect_to_url">
                    <label class="accept_input_checkboxes" id="sa_redirect_to_url" for="sa_redirect_to_url">
                        <?php _e('Redirect to a URL', 'smart_offers'); ?></label>
                    <input type='text' placeholder="<?php _e("https://www.google.co.in", "smart_offers"); ?>" name='accept_redirect_url' id='accept_redirect_url' value='<?php if (isset($action_on_accept['sa_redirect_to_url'])) {
                echo $action_on_accept['sa_redirect_to_url'];
            } ?>' />
                </fieldset></br>
                <fieldset>
                    <input type="checkbox" class="accept_input_checkboxes" name="sa_buy_now" id="buy_now" <?php if (isset($buy_now) && $buy_now == true) echo 'checked="checked"'; ?> value="buy_now">
                    <label class="accept_input_checkboxes" id="buy_now" for="buy_now">
                        <?php _e('Instantly Checkout with "Buy Now" plugin', 'smart_offers'); ?></label>
                </fieldset></br>

            </div>
            <div id="so_when_offer_skipped" class="panel woocommerce_options_panel">

                        <?php
                        $offer_denied_option = get_post_meta($post->ID, 'sa_smart_offer_if_denied', true);
                        if (empty($offer_denied_option))
                            $offer_denied_option = 'order_page';
                        $url = get_post_meta($post->ID, 'url', true);
                        ?>
                <h3> Actions to take when offer is skipped :</h3></br>

                <fieldset><input type="radio"
                                 class='skip_options_radio' name="sa_smart_offer_if_denied"
                                 id="order_page" value="order_page"
                        <?php
                        if ($offer_denied_option == "order_page")
                            echo 'checked="checked"';
                        ?>
                                  /><label class="skip_options_radio"
                                 id="order_page" for="order_page"><?php
                        _e('Skip only - Hide this offer', 'smart_offers');
                        ?></label>
                </fieldset>
                <br />

                <fieldset><input type="radio" class='skip_options_radio'
                                 name="sa_smart_offer_if_denied" id="offer_page" value="offer_page"
                <?php
                if ($offer_denied_option == "offer_page")
                    echo 'checked="checked"';
                ?>
                                  /><label class="skip_options_radio"
                                 for="offer_page"><?php
            _e('Skip & Show Another Offer', 'smart_offers');
            ?></label>
                    <select id="offer_ids" name="offer_ids[]"
                            class="ajax_chosen_select_offers" multiple="multiple"
                            data-placeholder="<?php
                                 _e('Search for an offer...', 'smart_offers');
                                 ?>">
                                 <?php
                                 if ($offer_denied_option == "offer_page") {
                                     $offer_id = get_post_meta($post->ID, 'url', true);
                                     if ($offer_id) {
                                         $offer_id = explode(',', $offer_id);

                                         foreach ($offer_id as $id) {
                                             $title = get_the_title($id);
                                             if (!$title) {
                                                 echo '<option value="" ></option>';
                                             } else {
                                                 echo '<option value="' . $id . '" selected="selected">' . $title . '</option>';
                                             }
                                         }
                                     }
                                 }
                                 ?>
                    </select><img class="help_tip"
                                  data-tip='<?php
                         _e('Offer to be shown if this offer is skipped. If multiple offers are chosen, one will be shown based on your settings.', 'smart_offers');
                         ?>'
                                  src="<?php
                            echo $woocommerce->plugin_url();
                            ?>/assets/images/help.png" /></fieldset>
                <br />

                <fieldset><input type="radio" class='skip_options_radio'
                                 name="sa_smart_offer_if_denied" id="particular_page"
                                 value="particular_page"
                                <?php
                                if ($offer_denied_option == "particular_page")
                                    echo 'checked="checked"';
                                ?>
                                  /><label class="skip_options_radio"
                                 for="particular_page"><?php
                                _e('Skip & Redirect to', 'smart_offers');
                                ?></label>
                                <?php
                                $args = array('selected' => $url);

                                wp_dropdown_pages($args);
                                ?>
                </fieldset>
                <br />

                <fieldset><input type="radio" class="skip_options_radio"
                                 name="sa_smart_offer_if_denied" id="url" value="url"
                                  <?php
                                  if ($offer_denied_option == "url")
                                      echo 'checked="checked"';
                                  ?>
                                  /><label class="skip_options_radio" for="url"><?php
                                  _e('Skip & Redirect to URL', 'smart_offers');
                                  ?></label>
                                 <?php
                                 $value = ($offer_denied_option == "url") ? $url : '';
                                 ?>
                    <input type='text' name='text_url' id='text_url'
                           value='<?php
                                     echo $value;
                                     ?>' /></fieldset>


                <p class="form-field">
                <fieldset>
                    <input type="checkbox" class="checkbox"
                           id="sa_smart_offer_if_denied_skip_permanently"
                           name="sa_smart_offer_if_denied_skip_permanently" class="checkbox"
                           value="yes"
            <?php
            if (get_post_meta($post->ID, 'sa_smart_offer_if_denied_skip_permanently', true) == "yes")
                echo 'checked="checked"';
            ?>><?php
                                 _e('<strong>Hide From This User</strong> - Never show this offer to this customer again if skipped once', 'smart_offers');
                                 ?>
                    <fieldset>
                        </p>
                        </div>
                    <?php
                }

                /**
                * Search for offers and return json
                *
                * @access public
                * @return void
                * @see WC_AJAX::woocommerce_json_search_offers()
                */
                function woocommerce_json_search_offers($x = '', $post_types = array('smart_offers')) {

                    check_ajax_referer('search-offers', 'security');

                    $term = (string) urldecode(stripslashes(strip_tags($_GET ['term'])));

                    if (empty($term))
                        die();

                    $args = array('post_type' => $post_types, 'post_status' => 'publish', 'posts_per_page' => - 1, 'meta_query' => array(array('key' => 'offer_title', 'value' => $term, 'compare' => 'LIKE')), 'fields' => 'ids');

                    $posts = get_posts($args);

                    $found_offers = array();

                    if ($posts)
                        foreach ($posts as $post) {
                            $found_offers [$post] = get_the_title($post);
                        }

                    echo json_encode($found_offers);

                    die();
                }

                /**
                * Search for coupons and return json
                *
                * @access public
                * @return void
                * @see WC_AJAX::woocommerce_json_search_coupons()
                */
                function woocommerce_json_search_coupons($x = '', $post_types = array('shop_coupon')) {
                    global $wpdb;

                    check_ajax_referer('search-coupons', 'security');

                    $wc_compat = SA_Smart_Offers::wc_compat();

                    $term = (string) urldecode(stripslashes(strip_tags($_GET['term'])));

                    if (empty($term))
                        die();

                    $args = array(
                        'post_type' => $post_types,
                        'post_status' => 'publish',
                        'posts_per_page' => -1,
                        's' => $term,
                        'fields' => 'all'
                    );

                    $posts = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE post_type LIKE 'shop_coupon' AND post_title LIKE '$term%' AND post_status = 'publish'");

                    $found_products = array();

                    $all_discount_types = $wc_compat::wc_get_coupon_types();

                    if ($posts)
                        foreach ($posts as $post) {

                            $discount_type = get_post_meta($post->ID, 'discount_type', true);

                            if (!empty($all_discount_types[$discount_type])) {
                                $discount_type = ' (Type: ' . $all_discount_types[$discount_type] . ')';
                                $found_products[get_the_title($post->ID)] = get_the_title($post->ID) . $discount_type;
                            }
                        }

                    echo json_encode($found_products);

                    die();
                }

                /**
                * Search for categories and return json
                *
                * @access public
                * @return void
                * @see WC_AJAX::woocommerce_json_search_prod_category()
                */
                function woocommerce_json_search_prod_category($x = '', $category = array('product_cat')) {

                    check_ajax_referer('so-search-product-category', 'security');

                    $term = (string) urldecode(stripslashes(strip_tags($_GET ['term'])));

                    if (empty($term))
                        die();

                    $args = array(
                        'search' => $term,
                        'hide_empty' => 0
                    );

                    $get_category_by_name = get_terms('product_cat', $args);

                    $found_category = array();

                    if ($get_category_by_name) {
                        foreach ($get_category_by_name as $term) {
                            $found_category[$term->term_id] = $term->name;
                        }
                    }

                    echo json_encode($found_category);

                    die();
                }

                /**
                * Search for simple products, variations and return json
                *
                * @access public
                * @return void
                * @see WC_AJAX::woocommerce_json_search_prod_category()
                */
                function woocommerce_json_search_products_and_only_variations($x = '', $post_types = array('product', 'product_variation')) {

                    check_ajax_referer('search-products-and-only-variations', 'security');

                    $wc_compat = SA_Smart_Offers::wc_compat();

                    $term = (string) urldecode(stripslashes(strip_tags($_GET['term'])));

                    if (empty($term))
                        die();

                    if (is_numeric($term)) {

                        $args = array(
                            'post_type' => $post_types,
                            'post_status' => array("publish", "private"),
                            'posts_per_page' => -1,
                            'post__in' => array(0, $term),
                            'fields' => 'ids'
                        );

                        $args2 = array(
                            'post_type' => $post_types,
                            'post_status' => array("publish", "private"),
                            'posts_per_page' => -1,
                            'post_parent' => $term,
                            'fields' => 'ids'
                        );

                        $args3 = array(
                            'post_type' => $post_types,
                            'post_status' => array("publish", "private"),
                            'posts_per_page' => -1,
                            'meta_query' => array(
                                array(
                                    'key' => '_sku',
                                    'value' => $term,
                                    'compare' => 'LIKE'
                                )
                            ),
                            'fields' => 'ids'
                        );

                        $posts = array_unique(array_merge(get_posts($args), get_posts($args2), get_posts($args3)));
                    } else {

                        $args = array(
                            'post_type' => $post_types,
                            'post_status' => array("publish", "private"),
                            'posts_per_page' => -1,
                            's' => $term,
                            'fields' => 'ids'
                        );

                        $args2 = array(
                            'post_type' => $post_types,
                            'post_status' => array("publish", "private"),
                            'posts_per_page' => -1,
                            'meta_query' => array(
                                array(
                                    'key' => '_sku',
                                    'value' => $term,
                                    'compare' => 'LIKE'
                                )
                            ),
                            'fields' => 'ids'
                        );

                        $posts = array_unique(array_merge(get_posts($args, ARRAY_A), get_posts($args2, ARRAY_A)));
                    }

                    $found_products = array();

                    if ($posts) {

                        foreach ($posts as $post) {

                            $post_type = get_post_type($post);
                            $product_type = wp_get_object_terms($post, 'product_type', array('fields' => 'slugs'));

                            if ($post_type == "product" && $product_type[0] == "variable") {
                                continue;
                            } else {

                                // To show the name of the products according to WC version
                                if ($wc_compat::is_wc_gte_20()) {
                                    $product = $wc_compat::get_product($post);

                                    $found_products[$post] = $wc_compat::get_formatted_product_name( $product );
                                } else {

                                    $SKU = get_post_meta($post, '_sku', true);

                                    if (isset($SKU) && $SKU)
                                        $SKU = ' (SKU: ' . $SKU . ')';

                                    $found_products[$post] = get_the_title($post) . ' &ndash; #' . $post . $SKU;
                                }
                            }
                        }
                    }

                    echo json_encode($found_products);

                    die();
                }

                /**
                * 
                * Add default content in offer description
                */
                function so_add_default_content($content) {
                    global $post_type;

                    if (isset($post_type)) {
                        if ($post_type == "smart_offers") {
                            $content = '
<h1>Offer Heading</h1>

<p>Offer Description</p>

<div class="so_accept"><a href=[so_acceptlink]>Yes, Add to Cart</a></div>
<div class="so_skip"><a href=[so_skiplink]>No, Skip this offer</a></div>
';

                            return $content;
                        }
                    }
                }

                /**
                * Add custom message for SO
                */
                public function so_add_custom_messages($messages) {
                    $post_ID = isset($post_ID) ? (int) $post_ID : 0;
                    $messages ['smart_offers'] [1] = sprintf(__('Offer updated successfully.'));
                    $messages ['smart_offers'] [2] = sprintf(__('<strong>Warning:</strong> Offer description does not include accept / skip links. <a id="missing_shortcode" href="">Click here to fix it automatically.</a>'));
                    return $messages;
                }

                /**
                * Add [so_product_variant] shortcode in offer description if not present
                */
                function add_shortcode_in_post_content($data) {

                    //                              To execute this only if post type is smart_offers and also if POST contains target_product_ids
                    if ($data['post_type'] != "smart_offers")
                        return $data;

                    if (isset($_POST ['target_product_ids']) && isset($_POST ['content'])) {

                        $wc_compat = SA_Smart_Offers::wc_compat();

                        $offered_product_id = implode(',', $_POST ['target_product_ids']);
                        $offered_prod_instance = SO_Product_Details::get_product_instance($offered_product_id);
                        $sc_position = strpos($_POST ['content'], '[so_product_variants]');
                        $add_sc = false;

                        if ($wc_compat::is_wc_gte_20()) {

                            if ($offered_prod_instance->product_type == 'variable' && ( $sc_position === false )) {
                                $add_sc = true;
                            }
                        } else {

                            if ($offered_prod_instance->product_type == 'variable' && !isset($offered_prod_instance->variation_id) && ( $sc_position === false )) {
                                $add_sc = true;
                            }
                        }

                        if ($add_sc == true) {
                            $data['post_content'] = "[so_product_variants]" . $_POST ['content'];
                            add_filter('redirect_post_location', array(&$this, 'my_redirect_post_location_filter'));
                        }
                    }

                    return $data;
                }

                /**
                * Add redirect parameter after adding shortcode
                */
                function my_redirect_post_location_filter($location) {
                    remove_filter('redirect_post_location', __FUNCTION__);
                    $location = add_query_arg('show_sc_msg', true, $location);
                    return $location;
                }

                /**
                * Add additional CSS
                */
                function so_admin_style() {
                    global $woocommerce, $typenow, $sa_smart_offers,$post_type;
                    if ($typenow == 'smart_offers') {
                        wp_enqueue_style('woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css');
                        wp_enqueue_style('so_admin_styles', plugins_url(SMART_OFFERS) . '/assets/css/admin.css');
                        wp_enqueue_style('jquery-ui-style', (is_ssl()) ? 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' : 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
                        
                        $wc_compat = SA_Smart_Offers::wc_compat();
                        if ($wc_compat::is_wc_gte_20()) {
                            
                            $css = "img.help_tip { 
                                        width:16px;height=16px; 
                                    }
                                    
                                    input.skip_options_radio, input.accept_input_checkboxes { 
                                        float: left; 
                                    }
                                
                                    div#so_offer_as .form-table th { 
                                        width: 135px;
                                    }
                                    
                                    div#so_where_to_offer .description, div#so_offer_as .description {
                                        display: inline;
                                        margin-left: 5px;
                                        
                                    }
                                    
                                    div#so_offer_as label{
                                        width: 175px;
                                    }
                                    
                                    div#so_offer_as input[type=radio]{
                                       margin-top: 0px; 
                                       margin-right: 0px;
                                    }
                                    
                                    div.woo_offer_rule select.role {
                                        width: 18%;

                                    }

                            
                            ";
                            
                            wp_add_inline_style( 'so_admin_styles', $css );
                        } 
                     
                        $style_to_hide_view_btn = "#post-preview, #view-post-btn{display: none;}";
                        wp_add_inline_style( 'so_admin_styles', $style_to_hide_view_btn );
                    }
                    
                }

            }

            return new SO_Admin_Offer();
        }


