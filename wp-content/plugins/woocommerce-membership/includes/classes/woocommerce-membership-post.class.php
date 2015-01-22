<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to WordPress posts (including pages and custom post types)
 * 
 * @class WooCommerce_Membership_Post
 * @package WooCommerce_Membership
 * @author RightPress
 */
if (!class_exists('WooCommerce_Membership_Post')) {

class WooCommerce_Membership_Post
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
        // Backend
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'), 1, 2);
        add_action('save_post', array($this, 'save_post'), 99, 2);
        add_filter('manage_posts_columns' , array($this, 'add_membership_column'));
        add_filter('manage_pages_columns', array($this, 'add_membership_column'));
        add_action('manage_posts_custom_column', array($this, 'add_membership_column_value'), 10, 2);
        add_action('manage_pages_custom_column', array($this, 'add_membership_column_value'), 10, 2);

        // Frontend
        add_filter('the_posts', array($this, 'filter_posts'));
        add_filter('get_pages', array($this, 'filter_posts'));
        add_filter('the_content', array($this, 'filter_content'));
        add_filter('get_the_excerpt', array($this, 'filter_content'));
        add_filter('wp_get_nav_menu_items', array($this, 'filter_menu'), 10, 3);
        add_filter('posts_where', array($this, 'expand_posts_where'));

        // Shortcodes
        add_shortcode('woocommerce_members_only', array($this, 'shortcode_members_only'));
        add_shortcode('woocommerce_non_members_only', array($this, 'shortcode_non_members_only'));
    }

    /**
     * Add meta boxes
     * 
     * @access public
     * @param string $post_type
     * @param object $post
     * @return void
     */
    public function add_meta_boxes($post_type, $post)
    {
        // Add content access metabox to all post types
        if (WooCommerce_Membership_Plan::get_list_of_all_plans()) {
            if (!in_array($post_type, array('membership_plan', 'shop_order', 'shop_coupon', 'product', 'product_variation'))) {
                if (!apply_filters('woocommerce_membership_skip_post_type', false, $post_type)) {
                    add_meta_box(
                        'rpwcm_post_membership',
                        __('Restrict Access', 'woocommerce-membership'),
                        array($this, 'render_meta_box'),
                        $post_type,
                        'side',
                        'high'
                    );
                }
            }
        }
    }

    /**
     * Render content access restriction meta box
     * 
     * @access public
     * @param mixed $post
     * @return void
     */
    public function render_meta_box($post)
    {
        global $type_now;

        // Get membership plan list
        $plans = WooCommerce_Membership_Plan::get_list_of_all_plan_keys();

        // Get preselected options
        $selected = get_post_meta($post->ID, '_rpwcm_only_caps');

        // Load view
        include RPWCM_PLUGIN_PATH . '/includes/views/backend/post/restrict-access.php';
    }

    /**
     * Save post meta box
     * 
     * @access public
     * @param int $post_id
     * @param object $post
     * @return void
     */
    public function save_post($post_id, $post)
    {
        // Check if required properties were passed in
        if (empty($post_id) || empty($post)) {
            return;
        }

        // Make sure user has permissions to edit this post
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Make sure the correct post ID was passed from form
        if (empty($_POST['post_ID']) || $_POST['post_ID'] != $post_id) {
            return;
        }

        // Make sure it is not a draft save action
        if (defined('DOING_AUTOSAVE') || is_int(wp_is_post_autosave($post)) || is_int(wp_is_post_revision($post))) {
            return;
        }

        delete_post_meta($post_id, '_rpwcm_only_caps');

        if (!empty($_POST['_rpwcm_only_caps'])) {
            if (!apply_filters('woocommerce_membership_skip_post_type', false, $post_type)) {
                $plans = WooCommerce_Membership_Plan::get_list_of_all_plan_keys();

                foreach ($_POST['_rpwcm_only_caps'] as $cap) {
                    if (isset($plans[$cap])) {
                        add_post_meta($post_id, '_rpwcm_only_caps', $cap);
                    }
                }
            }
        }
    }

    /**
     * Check if user has access to post
     * 
     * @access public
     * @param int $post_id
     * @param int $user_id
     * @return bool
     */
    public static function user_has_access_to_post($post_id, $user_id = null)
    {
        // Get keys (capabilities) of plans that grant access to this post
        $plan_keys = get_post_meta($post_id, '_rpwcm_only_caps');

        // Empty means that we don't restrict access to this post
        if (empty($plan_keys)) {
            return true;
        }

        // Not logged in? We can't check their membership then
        if (!is_user_logged_in()) {
            return false;
        }

        // Get user ID if one has not been passed
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        // Get user
        $user = get_user_by('id', $user_id);

        // Can't get user? 
        if (!$user) {
            return true;
        }

        // Get user capabilities
        $user_capabilities = WooCommerce_Membership_User::get_user_capabilities($user);
        $user_capabilities = WooCommerce_Membership_Plan::enabled_keys_only($user_capabilities);

        // Check each plan
        foreach ($plan_keys as $plan_key) {
            if (in_array($plan_key, $user_capabilities)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if we need to grant this user access because of specific area of site or specific role
     * 
     * @access public
     * @return bool
     */
    public static function skip_admin()
    {
        if (is_admin() || WooCommerce_Membership_User::user_has_role(apply_filters('woocommerce_membership_grant_access_roles', array('administrator')))) {
            return WooCommerce_Membership::$debug ? false : true;
        }

        return false;
    }

    /**
     * Filter out posts that user does not have access to
     * 
     * @access public
     * @param array $pages
     * @return void
     */
    public function filter_posts($posts)
    {
        // Skip admin
        if (self::skip_admin()) {
            return $posts;
        }

        // Check each post
        foreach ($posts as $post_key => $post) {
            if (!self::user_has_access_to_post($post->ID)) {
                unset($posts[$post_key]);
            }
        }

        // Return filtered posts
        return $posts;
    }

    /**
     * Filter content (post content and except)
     * 
     * @access public
     * @param string $content
     * @return string
     */
    public function filter_content($content)
    {
        global $post;

        // Current user has access to content?
        if (self::skip_admin() || !isset($post->ID) || self::user_has_access_to_post($post->ID)) {
            return $content;
        }

        return '';
    }

    /**
     * Filter menu items that user has not access to
     * 
     * @access public
     * @param array $items
     * @param object $menu
     * @param array $args
     * @return void
     */
    public function filter_menu($items, $menu, $args)
    {
        // Skip admin
        if (self::skip_admin()) {
            return $items;
        }

        // Check each menu item
        foreach ($items as $item_key => $item) {
            if (!self::user_has_access_to_post($item->object_id)) {
                unset($items[$item_key]);
            }
        }

        // Return filtered items
        return $items;
    }

    /**
     * Fetch only those posts that user has access to
     * 
     * @access public
     * @param string $where
     * @return void
     */
    public function expand_posts_where($where)
    {
        if (isset($GLOBALS['rpwcm_getting_enabled_keys']) && $GLOBALS['rpwcm_getting_enabled_keys']) {
            return $where;
        }

        global $wpdb;

        if (self::skip_admin()) {
            return $where;
        }

        // Get all user capabilities
        $capabilities = WooCommerce_Membership_User::get_user_capabilities();
        $capabilities = WooCommerce_Membership_Plan::enabled_keys_only($capabilities);

        // Only allow access to posts that are not restricted and to posts that user has access to
        $where .= sprintf(' AND ' . $wpdb->posts . '.ID IN (SELECT ID FROM ' . $wpdb->posts . ' WHERE ID NOT IN (SELECT post_id FROM ' . $wpdb->postmeta . ' WHERE ' . $wpdb->postmeta . '.meta_key = \'_rpwcm_only_caps\') UNION ALL SELECT post_id AS ID FROM ' . $wpdb->postmeta . ' WHERE ' . $wpdb->postmeta . '.meta_key = \'_rpwcm_only_caps\' AND ' . $wpdb->postmeta . '.meta_value IN (\'%s\'))', implode('\',\'', $capabilities));

        return $where;
    }

    /**
     * Shortcode to display content to members only
     * 
     * @access public
     * @param array $atts
     * @param string $content
     * @param bool $non_members
     * @return string
     */
    public function shortcode_members_only($atts, $content = '')
    {
        return self::shortcode_members($atts, $content, true);
    }

    /**
     * Shortcode to display content to non-members only
     * 
     * @access public
     * @param array $atts
     * @param string $content
     * @return string
     */
    public function shortcode_non_members_only($atts, $content = '')
    {
        return self::shortcode_members($atts, $content, false);
    }

    /**
     * Shortcode logic for both member and non-member shortcodes
     * This function is also used by woocommerce_members_only() and woocommerce_non_members_only() functions
     * 
     * @access public
     * @param array $atts
     * @param string $content
     * @param bool $members
     * @param bool $is_function
     * @return string
     */
    public static function shortcode_members($atts, $content, $members, $is_function = false)
    {
        // Get plans from attributes
        if ($is_function) {
            $keys = $atts;
        }
        else {
            $atts = shortcode_atts(array('key' => '', 'keys' => ''), $atts);
            $keys = !empty($atts['keys']) ? array_map('trim', explode(',', $atts['keys'])) : array(trim($atts['key']));
            $keys = array_filter($keys, 'strlen');
        }

        $shortcode = $members ? 'woocommerce_non_members_only' : 'woocommerce_members_only';

        // Shortcode placed but no plan keys set? Get all plan keys (i.e. accept any member or non-member)
        if (empty($keys)) {
            $keys = array_keys(WooCommerce_Membership_Plan::get_list_of_all_plan_keys());
        }

        // Get enabled plan keys
        $capabilities = WooCommerce_Membership_User::get_user_capabilities();
        $capabilities = WooCommerce_Membership_Plan::enabled_keys_only($capabilities);

        // Check if user has any of the defined membership plan keys (capabilities) set
        $display = array_intersect($keys, $capabilities) ? true : false;

        // Inverse for non-members
        $display = $members ? $display : !$display;

        // Allow developers to override
        $display = apply_filters('woocommerce_membership_display_shortcode_content', $display, $shortcode, $keys, $content);

        return $display ? $content : '';
    }

    /**
     * Check if post is existant and not in trash
     * 
     * @access public
     * @param string $post_id
     * @return bool
     */
    public static function post_is_active($post_id)
    {
        $post_status = get_post_status($post_id);

        if ($post_status && $post_status != 'trash') {
            return true;
        }

        return false;
    }

    /**
     * Add membership column to post lists
     * 
     * @access public
     * @param array $columns
     * @return array
     */
    public function add_membership_column($columns)
    {
        global $post_type;

        if (!in_array($post_type, array('membership_plan', 'shop_order', 'shop_coupon', 'product', 'product_variation'))) {
            if (!apply_filters('woocommerce_membership_skip_post_type', false, $post_type)) {
                $columns = array_merge($columns, array(
                    'rpwcm_membership' => '<span class="rpwcm_post_list_header_icon tips" title="' . __('Members-only', 'woocommerce-membership') . '">' . __('Members-Only Content', 'woocommerce-membership') . '</span>',
                ));
            }
        }

        return $columns;
    }

    /**
     * Manage list column values
     * 
     * @access public
     * @param array $column
     * @param int $post_id
     * @return void
     */
    public function add_membership_column_value($column, $post_id)
    {
        if ($column == 'rpwcm_membership') {
            if (get_post_meta($post_id, '_rpwcm_only_caps')) {
                echo '<i class="fa fa-group rpwcm_post_list_icon tips" title="' . __('Access is restricted to members only', 'woocommerce-membership') . '"></i>';
            }
        }
    }

}

new WooCommerce_Membership_Post();

}