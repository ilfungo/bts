<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to WooCommerce Orders
 * 
 * @class WooCommerce_Membership_Order
 * @package WooCommerce_Membership
 * @author RightPress
 */
if (!class_exists('WooCommerce_Membership_Order')) {

class WooCommerce_Membership_Order
{

    /**
     * Constructor class
     * 
     * @access public
     * @param mixed $id
     * @return void
     */
    public function __construct($id = null)
    {
        // Save plan configuration on the checkout
        add_action('woocommerce_add_order_item_meta', array($this, 'save_order_item_plans'), 10, 3);
        add_filter('woocommerce_hidden_order_itemmeta', array($this, 'hide_membership_plans'));

        // Grant membership on these WooCommerce actions
        add_action('woocommerce_payment_complete', array($this, 'order_paid'), 9);
        add_action('woocommerce_order_status_processing', array($this, 'order_paid'), 9);
        add_action('woocommerce_order_status_completed', array($this, 'order_paid'), 9);

        // Remove membership on these WooCommerce actions
        add_action('woocommerce_order_status_cancelled', array($this, 'order_cancelled'));
        add_action('woocommerce_order_status_refunded', array($this, 'order_cancelled'));
        add_action('woocommerce_order_status_failed', array($this, 'order_cancelled'));

        // Trashed, untrashed and deleted
        add_action('before_delete_post', array($this, 'post_deleted'));
        add_action('trashed_post', array($this, 'post_trashed'));
        add_action('untrashed_post', array($this, 'post_untrashed'));
    }

    /**
     * Save membership plan IDs to order item meta
     * 
     * @access public
     * @param int $item_id
     * @param array $cart_item
     * @param string $cart_item_key
     * @return void
     */
    public function save_order_item_plans($item_id, $cart_item, $cart_item_key)
    {
        $id = !empty($cart_item['variation_id']) ? $cart_item['variation_id'] : $cart_item['product_id'];

        // Check if it's a membership product
        if (WooCommerce_Membership_Product::is_membership($id)) {
            foreach (WooCommerce_Membership_Product::get_membership_plans($id, 'enabled') as $plan_id => $plan) {
                wc_add_order_item_meta($item_id, '_rpwcm_plans', $plan_id);
            }
        }
    }

    /**
     * Hide membership keys on order items list
     * 
     * @access public
     * @param array $keys
     * @return array
     */
    public function hide_membership_plans($keys)
    {
        $keys[] = '_rpwcm_plans';
        return $keys;
    }

    /**
     * Order paid - grant membership
     * 
     * @access public
     * @param int $order_id
     * @return void
     */
    public function order_paid($order_id)
    {
        $order = new WC_Order($order_id);

        foreach ($order->get_items() as $item_id => $item) {

            // Only proceed if we have any plan IDs set
            if (is_array($item['item_meta']['_rpwcm_plans'])) {

                // Get correct ID
                $id = isset($item['variation_id']) ? $item['variation_id'] : $item['product_id'];

                // Allow other plugins to cancel membership activation
                if (!apply_filters('woocommerce_membership_cancel_activation', false, $order_id, $item_id, $item, $id)) {

                    // Grant access now
                    foreach ($item['item_meta']['_rpwcm_plans'] as $plan_id) {
                        WooCommerce_Membership_Plan::add_member($plan_id, $order->user_id);
                    }
                }
            }
        }
    }

    /**
     * Order cancelled - remove membership
     * 
     * @access public
     * @param int $order_id
     * @return void
     */
    public function order_cancelled($order_id)
    {
        $order = new WC_Order($order_id);

        foreach ($order->get_items() as $item_id => $item) {

            // Only proceed if we have any plan IDs set
            if (is_array($item['item_meta']['_rpwcm_plans'])) {

                // Get correct ID
                $id = isset($item['variation_id']) ? $item['variation_id'] : $item['product_id'];

                // Allow other plugins to cancel membership deactivation
                if (!apply_filters('woocommerce_membership_cancel_deactivation', false, $order_id, $item_id, $item, $id)) {

                    // Remove access now
                    foreach ($item['item_meta']['_rpwcm_plans'] as $plan_id) {
                        WooCommerce_Membership_Plan::remove_member($plan_id, $order->user_id);
                    }
                }
            }
        }
    }

    /**
     * Get array of membership plan objects from WooCommerce Order ID
     * 
     * @access public
     * @param int $order_id
     * @return array
     */
    public static function get_membership_plans_from_order_id($order_id)
    {
        $memberships = array();

        if ($order = new WC_Order($order_id)) {
            foreach ($order->get_items() as $item) {

                // Get correct ID
                $product_id = !empty($item['variation_id']) ? $item['variation_id'] : $item['product_id'];

                if ($product_membership_ids = WooCommerce_Membership_Product::get_membership_plans($product_id)) {
                    foreach ($product_membership_ids as $product_membership_id) {
                        if (!isset($memberships[$product_membership_id])) {
                            $memberships[$product_membership_id] = WooCommerce_Membership_Plan::cache($product_membership_id);
                        }
                    }
                }
            }
        }

        return $memberships;
    }

    /**
     * Display granted membership plans on single order view page
     * Currently this function is not used, added for later versions
     * 
     * @access public
     * @param object $order
     * @return void
     */
    public function display_frontend_order_granted_plans($order)
    {
        $plans = WooCommerce_Membership_Order::get_membership_plans_from_order_id($order->id);

        if (!empty($plans) && apply_filters('woocommerce_membership_display_order_granted_plans', true)) {
            WooCommerce_Membership::include_template('myaccount/membership-list', array(
                'plans' => $plans,
                'title' => __('My Memberships', 'woocommerce-membership'),
            ));
        }
    }

    /**
     * Order deleted
     * 
     * @access public
     * @param int $post_id
     * @return void
     */
    public function post_deleted($post_id)
    {
        global $post_type;

        if ($post_type == 'shop_order') {
            $this->order_cancelled($post_id);
        }
    }

    /**
     * Order trashed
     * 
     * @access public
     * @param int $post_id
     * @return void
     */
    public function post_trashed($post_id)
    {
        global $post_type;

        if ($post_type == 'shop_order') {
            $this->order_cancelled($post_id);
        }
    }

    /**
     * Order untrashed
     * 
     * @access public
     * @param int $post_id
     * @return void
     */
    public function post_untrashed($post_id)
    {
        global $post_type;

        if ($post_type == 'shop_order') {

            $order = new WC_Order($post_id);

            if (in_array($order->status, array('processing', 'completed'))) {
                $this->order_paid($post_id);
            }
        }
    }

}

new WooCommerce_Membership_Order();

}