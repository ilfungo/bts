<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to WordPress User
 * 
 * @class WooCommerce_Membership_User
 * @package WooCommerce_Membership
 * @author RightPress
 */
if (!class_exists('WooCommerce_Membership_User')) {

class WooCommerce_Membership_User
{
    private $memberships_keys_to_recount = array();

    /**
     * Constructor class
     * 
     * @access public
     * @param mixed $id
     * @return void
     */
    public function __construct($id = null)
    {
        // Enforce user registration
        add_action('woocommerce_before_checkout_form', array($this, 'enforce_user_registration'), 99);
        add_action('woocommerce_before_checkout_process', array($this, 'enforce_user_registration'), 99);
        add_filter('wc_checkout_params', array($this, 'enforce_user_registration_js'), 99);

        // WordPress hooks
        add_action('delete_user', array($this, 'delete_user'));
        add_action('deleted_user', array($this, 'deleted_user'));
    }

    /**
     * Allow no guest checkout when membership product is in cart
     * 
     * @access public
     * @param object $checkout
     * @return void
     */
    public function enforce_user_registration($checkout)
    {
        // User already registered?
        if (is_user_logged_in()) {
            return;
        }

        if (!$checkout) {
            global $woocommerce;
            $checkout = &$woocommerce->checkout;
        }

        // Only proceed if cart contains membership
        if (WooCommerce_Membership_Checkout::cart_contains_membership()) {

            // Enable registration
            $checkout->enable_signup = true;

            // Enforce registration
            $checkout->enable_guest_checkout = false;

            // Must create account
            $checkout->must_create_account = true;
        }
    }

    /**
     * Allow no guest checkout (Javascript part)
     * 
     * @access public
     * @param array $properties
     * @return array
     */
    public function enforce_user_registration_js($properties)
    {
        // User already registered?
        if (is_user_logged_in()) {
            return $properties;
        }

        // No membership in cart?
        if (!WooCommerce_Membership_Checkout::cart_contains_membership()) {
            return $properties;
        }

        $properties['option_guest_checkout'] = 'no';

        return $properties;
    }

    /**
     * Get user full name from database with link to user profile
     * 
     * @access public
     * @param int $user_id
     * @param string $name
     * @return string
     */
    public static function get_user_full_name_link($user_id, $name = '')
    {
        if (empty($name)) {
            if ($user = get_userdata($user_id)) {
                $first_name = get_the_author_meta('first_name', $user_id);
                $last_name = get_the_author_meta('last_name', $user_id);

                if ($first_name || $last_name) {
                    $name = join(' ', array($first_name, $last_name));
                }
                else {
                    $name = $user->display_name;
                }
            }
        }

        return '<a href="user-edit.php?user_id=' . $user_id . '">' . $name . '</a>';
    }

    /**
     * Check if user has at least one of the provided roles
     * 
     * @access public
     * @param array $roles
     * @param int|object|null $user
     * @return bool
     */
    public static function user_has_role($roles, $user = null)
    {
        // Get user
        if (!is_object($user)) {
            $user = empty($user) ? wp_get_current_user() : get_userdata($user);
        }

        // No user?
        if (empty($user)) {
            return false;
        }

        return array_intersect($roles, (array) $user->roles) ? true : false;
    }

    /**
     * Get all user capabilities
     * 
     * @access public
     * @param int|object|null $user
     * @return array
     */
    public static function get_user_capabilities($user = null)
    {
        $capabilities = array();

        // Get user
        if (!is_object($user)) {
            $user = empty($user) ? wp_get_current_user() : get_userdata($user);
        }

        // No user?
        if (empty($user)) {
            return array();
        }

        // Extract capabilities
        foreach ($user->allcaps as $cap_key => $cap) {
            if ($cap) {
                $capabilities[] = $cap_key;
            }
        }

        return (array) apply_filters('woocommerce_membership_user_capabilities', $capabilities, $user);
    }

    /**
     * User is being deleted
     * 
     * @access public
     * @param int $user_id
     * @return void
     */
    public function delete_user($user_id)
    {
        foreach (self::get_user_capabilities($user_id) as $capability) {
            $this->memberships_keys_to_recount[] = $capability;
        }
    }

    /**
     * User has been deleted
     * 
     * @access public
     * @param int $user_id
     * @return void
     */
    public function deleted_user($user_id)
    {
        WooCommerce_Membership_Plan::update_member_count(array_unique($this->memberships_keys_to_recount));
    }

}

new WooCommerce_Membership_User();

}