<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('SO_Admin_Dashboard_Widget')) {

    Class SO_Admin_Dashboard_Widget {

        function __construct() {
            add_action('wp_dashboard_setup', array($this, 'init_dashboard'), 10);
        }

        /**
	 * Init dashboard widgets
	 */
        function init_dashboard() {
            wp_add_dashboard_widget('smart_offers_dashboard_widget', __('Smart Offers', ''), array($this, 'smart_offers_stats'));
        }

        /**
	 * Show SO statistics
	 */
        function smart_offers_stats() {
            global $woocommerce, $wpdb;

            $wc_compat = SA_Smart_Offers::wc_compat();
            if ($wc_compat::is_wc_gte_21()) {
                wp_enqueue_style('woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css');
            }

            $wpdb->query("SET SESSION group_concat_max_len=999999");
            $query_to_fetch_offers_count = "SELECT pm.meta_value from 
													{$wpdb->prefix}postmeta as pm, {$wpdb->prefix}posts as p
												WHERE pm.meta_key  = 'so_accept_skip_counter'
												AND pm.post_id = p.ID
												AND p.post_type = 'smart_offers' AND p.post_status NOT IN ('trash') ";

            $results_for_offers_count = $wpdb->get_col($query_to_fetch_offers_count);

            $accept_count = 0;
            $skip_count = 0;
            $total_count = 0;

            foreach ($results_for_offers_count as $result) {
                $result = maybe_unserialize($result);
                foreach ($result as $key => $value) {
                    if ($key == "accepted") {
                        $accept_count += $value;
                    }
                    if ($key == "skipped") {
                        $skip_count += $value;
                    }
                    if ($key == "offer_shown") {
                        $total_count += $value;
                    }
                }
            }

            if ( $wc_compat::is_wc_gte_22() ) {
                $query_to_fetch_offers_sale = "SELECT pm.meta_value from {$wpdb->prefix}postmeta as pm
                                                        JOIN {$wpdb->posts} as posts
                                                            ON ( posts.ID = pm.post_id )
                                                      WHERE pm.meta_key IN ('smart_offers_meta_data')
                                                      AND posts.post_type = 'shop_order'
                                                      AND posts.post_status IN ('wc-completed','wc-processing','wc-on-hold')
                                                          ";
            } else {
                $query_to_fetch_offers_sale = "SELECT pm.meta_value from {$wpdb->prefix}postmeta as pm
                                                      WHERE pm.meta_key IN ('smart_offers_meta_data')
                                                      AND pm.post_id IN (  SELECT id FROM {$wpdb->prefix}posts AS posts
                                                                            JOIN {$wpdb->prefix}term_relationships AS term_relationships
                                                                                                        ON term_relationships.object_id = posts.ID
                                                                                        JOIN {$wpdb->prefix}term_taxonomy AS term_taxonomy
                                                                                                        ON term_taxonomy.term_taxonomy_id = term_relationships.term_taxonomy_id
                                                                                        JOIN {$wpdb->prefix}terms AS terms
                                                                                                        ON term_taxonomy.term_id = terms.term_id
                                                                        WHERE terms.name IN ('completed','processing','on-hold')
                                                                            AND posts.post_status = 'publish' )
                                                          ";
            }

            $results_for_offers_sale = $wpdb->get_col($query_to_fetch_offers_sale);

            $offers_paid_through = 0;
            $total_sale = 0;
            foreach ($results_for_offers_sale as $result) {
                $result = maybe_unserialize($result);
                $offers_paid_through = $offers_paid_through + count($result);
                foreach ($result as $key => $value) {

                    $total_sale += $value ['offered_price'];
                }
            }

            $conversion_rate = ($total_count != 0) ? ($offers_paid_through / $total_count) * 100 : 0;

            $stats = '<ul class="woocommerce_stats">';
            $stats .= '<li style="width: 59%; overflow: hidden"><strong>' . $wc_compat::wc_price($total_sale) . '</strong><center> ' . __('Revenue from Offers', 'smart_offers') . '</center></li>';
            $stats .= '<li style="width: 31%; overflow: hidden"><strong>' . $wc_compat::wc_format_decimal($conversion_rate) . '%' . '</strong> ' . __('Conversion Rate', 'smart_offers') . '</li>';
            $stats .= '</ul>';
            $stats .= '<ul class="woocommerce_stats">';
            $stats .= '<li style="width: 21%"><strong>' . $total_count . '</strong> ' . __('Offers Seen', 'smart_offers') . '</li>';
            $stats .= '<li style="width: 21%"><strong>' . $skip_count . '</strong> ' . __('Skipped', 'smart_offers') . '</li>';
            $stats .= '<li style="width: 21%"><strong>' . $accept_count . '</strong> ' . __('Accepted', 'smart_offers') . '</li>';
            $stats .= '<li style="width: 21%"><strong>' . $offers_paid_through . '</strong> ' . __('Paid Through', 'smart_offers') . '</li>';
            $stats .= '</ul>';

            echo $stats;
        }

    }
    return new SO_Admin_Dashboard_Widget();
}


