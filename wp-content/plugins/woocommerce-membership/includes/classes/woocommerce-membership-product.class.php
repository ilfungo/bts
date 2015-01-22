<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to WooCommerce Products
 * 
 * @class WooCommerce_Membership_Product
 * @package WooCommerce_Membership
 * @author RightPress
 */
if (!class_exists('WooCommerce_Membership_Product')) {

class WooCommerce_Membership_Product
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
        // WooCommerce hooks
        add_filter('product_type_options', array($this, 'add_simple_product_option'));
        add_action('woocommerce_product_options_general_product_data', array($this, 'display_simple_product_selection'));
        add_action('woocommerce_variation_options', array($this, 'add_variation_option'), 10, 3);
        add_action('woocommerce_product_after_variable_attributes', array($this, 'display_variation_selection'), 10, 3);
        add_action('woocommerce_process_product_meta_simple', array($this, 'process_simple_product_meta'));
        add_action('woocommerce_process_product_meta_variable', array($this, 'process_variable_product_meta'));

        // WordPress hooks
        add_filter('manage_edit-product_columns', array($this, 'product_list_custom_column'), 99);
        add_action('manage_product_posts_custom_column', array($this, 'product_list_custom_column_value'), 99);
        add_filter('posts_join', array($this, 'expand_list_search_context_join'));
        add_filter('posts_where', array($this, 'expand_list_search_context_where'));
        add_filter('posts_groupby', array($this, 'expand_list_search_context_group_by'));
        add_action('before_delete_post', array($this, 'post_deleted'));
        add_action('trashed_post', array($this, 'post_trashed'));
        add_action('untrashed_post', array($this, 'post_untrashed'));
    }

    /**
     * Get array of membership plans
     * 
     * @access public
     * @param int $product_id
     * @param string $status
     * @return array
     */
    public static function get_membership_plans($product_id, $status = '')
    {
        $plans = array();

        foreach (self::get_membership_plan_ids($product_id) as $plan_id) {
            $plan = WooCommerce_Membership_Plan::cache($plan_id);

            if (empty($status) || $plan->status == $status) {
                $plans[$plan_id] = $plan;
            }
        }

        return $plans;
    }

    /**
     * Get array of membership plan ids from WooCommerce Product
     * 
     * @access public
     * @param int $product_id
     * @return array
     */
    public static function get_membership_plan_ids($product_id)
    {
        $ids = array();

        foreach (get_post_meta($product_id, '_rpwcm_plans') as $plan_id) {
            if (WooCommerce_Membership_Post::post_is_active($plan_id)) {
                $ids[] = $plan_id;
            }
        }

        return $ids;
    }

    /**
     * Check if product is membership product (i.e. if it grants access to at least one membership)
     * 
     * @access public
     * @param int $product_id
     * @return bool
     */
    public static function is_membership($product_id)
    {
        if (get_post_meta($product_id, '_rpwcm')) {
            return true;
        }

        // No? Check children...
        $children = get_children(array(
            'post_parent' => $product_id,
            'post_type' => 'product'
        ));

        foreach ($children as $child) {
            if (get_post_meta($child->ID, '_rpwcm')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove plan from product
     * 
     * @access public
     * @param int $product_id
     * @param int $plan_id
     * @return void
     */
    public static function remove_plan($product_id, $plan_id)
    {
        delete_post_meta($product_id, '_rpwcm_plans', $plan_id);
    }

    /**
     * Recheck if product is still a membership product
     * 
     * @access public
     * @param int $product_id
     * @return void
     */
    public static function recheck_membership_status($product_id)
    {
        $post_type = get_post_type($product_id);

        if ($post_type == 'product_variation') {
            if (get_post_meta($product_id, '_rpwcm_plans')) {
                delete_post_meta($product_id, '_rpwcm');

                foreach (get_post_ancestors($product_id) as $parent_id) {
                    if (get_post_type($parent_id) == 'product') {

                        $has_other_membership_variations = false;

                        $children = get_children(array(
                            'post_parent' => $parent_id,
                            'post_type' => 'product_variation',
                            'posts_per_page' => -1,
                            'fields' => 'ids',
                        ));

                        foreach ($children as $children_id) {
                            if (get_post_meta($children_id, '_rpwcm')) {
                                $has_other_membership_variations = true;
                                break;
                            }
                        }

                        if (!$has_other_membership_variations) {
                            delete_post_meta($parent_id, '_rpwcm');
                            delete_post_meta($parent_id, '_rpwcm_plans');
                            delete_post_meta($parent_id, '_rpwcm_child_plans');
                        }
                        break;
                    }
                }
            }
        }
        else if ($post_type == 'product') {
            if (!get_post_meta($product_id, '_rpwcm_plans')) {
                delete_post_meta($product_id, '_rpwcm');
            }
        }
    }

    /**
     * Add simple product property checkbox (checkbox that converts simple product to membership product)
     * 
     * @access public
     * @param array $checkboxes
     * @return array
     */
    public function add_simple_product_option($checkboxes)
    {
        $checkboxes['rpwcm'] = array(
            'id'            => '_rpwcm',
            'wrapper_class' => 'show_if_simple',
            'label'         => __('Membership', 'woocommerce-membership'),
            'description'   => __('This product grants access to one or more membership plans.', 'woocommerce-membership'),
            'default'       => 'no'
        );

        return $checkboxes;
    }

    /**
     * Display membership selection field on product page (simple product)
     * 
     * @access public
     * @return void
     */
    public function display_simple_product_selection()
    {
        // Get post
        global $post;
        $post_id = $post->ID;

        // Retrieve currently selected plans
        $selected = get_post_meta($post_id, '_rpwcm_plans');

        // Retrieve all possible plans
        $values = WooCommerce_Membership_Plan::get_list_of_all_plans();

        require RPWCM_PLUGIN_PATH . 'includes/views/backend/product/simple-product-meta.php';
    }

    /**
     * Add variable product property checkbox (checkbox that converts variation to membership variation)
     * 
     * @access public
     * @param int $loop
     * @param array $variation_data
     * @param object $variation
     * @return void
     */
    public function add_variation_option($loop, $variation_data, $variation)
    {
        echo '<label><input type="checkbox" class="checkbox _rpwcm_variable" name="_rpwcm[' . $loop . ']" ' . checked(self::is_membership($variation->ID), true, false) . ' /> ' . __('Membership', 'woocommerce-membership') . ' <a class="tips" data-tip="' . __('This variation grants access to one or more membership plans.', 'woocommerce-membership') . '" href="#">[?]</a></label>';
    }

    /**
     * Display membership field on variation
     * 
     * @access public
     * @param int $loop
     * @param array $variation_data
     * @param object $variation
     * @return void
     */
    public function display_variation_selection($loop, $variation_data, $variation)
    {
        // Get post id
        $post_id = $variation->ID;

        // Retrieve currently selected plans
        $selected = get_post_meta($post_id, '_rpwcm_plans');

        // Retrieve all possible plans
        $values = WooCommerce_Membership_Plan::get_list_of_all_plans();

        require RPWCM_PLUGIN_PATH . 'includes/views/backend/product/variation-meta.php';
    }

    /**
     * Save simple product membership-related meta data
     * 
     * @access public
     * @param int $post_id
     * @param bool $variable
     * @return void|array
     */
    public function process_simple_product_meta($post_id, $variable = false, $loop = null)
    {
        // Get correct membership checkbox value
        $membership = $variable ? $_POST['_rpwcm'][$loop] : $_POST['_rpwcm'];

        if (!empty($_POST['_rpwcm_plans'])) {
            $plans = $variable ? (array) $_POST['_rpwcm_plans'][$loop] : (array) $_POST['_rpwcm_plans'];
        }
        else {
            $plans = array();
        }

        // Get previously set plans
        $old_plans = get_post_meta($post_id, '_rpwcm_plans');

        // Delete previously set plans
        delete_post_meta($post_id, '_rpwcm_plans');

        // Is membership?
        if (isset($membership) && $membership == 'on') {
            update_post_meta($post_id, '_rpwcm', 'yes');

            // Save new plans
            foreach ($plans as $plan_id) {
                add_post_meta($post_id, '_rpwcm_plans', $plan_id);
            }

            $result = !$variable ? null : array('result' => true, 'plans' => $plans);
        }
        else {
            delete_post_meta($post_id, '_rpwcm');
            $result = !$variable ? null : array('result' => false);
        }

        self::update_count($post_id, $old_plans);

        return $result;
    }

    /**
     * Save variable product membership-related meta data
     * 
     * @access public
     * @param int $post_id
     * @return void
     */
    public function process_variable_product_meta($post_id)
    {
        // Find max post id
        $all_ids = $_POST['variable_post_id'];
        $max_id = max(array_keys($all_ids));

        $variable_product_has_memberships = false;
        $plans = array();

        // Iterate over all variations and save them
        for ($i = 0; $i <= $max_id; $i++) {

            // Skip non-existing keys
            if (!isset($all_ids[$i])) {
                continue;
            }

            // Get post ID for current variable product
            $variable_post_id = (int) $all_ids[$i];

            // Handle as simple product
            $result = $this->process_simple_product_meta($variable_post_id, true, $i);

            if ($result['result']) {
                $variable_product_has_memberships = true;

                if (is_array($result['plans'])) {
                    $plans = array_merge($plans, $result['plans']);
                }
            }
        }

        if ($variable_product_has_memberships) {
            update_post_meta($post_id, '_rpwcm', 'yes');

            // Store plan IDs on parent as well for it to be easily searchable
            delete_post_meta($post_id, '_rpwcm_child_plans');

            foreach (array_unique($plans) as $plan_id) {
                add_post_meta($post_id, '_rpwcm_child_plans', $plan_id);
            }
        }
        else {
            delete_post_meta($post_id, '_rpwcm');
        }
    }

    /**
     * Insert custom column into product list view header
     * 
     * @access public
     * @param array $columns
     * @return array
     */
    public function product_list_custom_column($columns)
    {
        // Check if array format is as expected
        if (!is_array($columns) || !isset($columns['product_type'])) {
            return $columns;
        }

        // Insert new column after column product_type
        $offset = array_search('product_type', array_keys($columns)) + 1;

        return array_merge (
                array_slice($columns, 0, $offset),
                array('rpwcm' => '<span class="rpwcm_product_list_header_icon tips" data-tip="' . __('Membership Product', 'woocommerce-membership') . '">' . __('Membership', 'woocommerce-membership') . '</span>'),
                array_slice($columns, $offset, null)
            );
    }

    /**
     * Display custom column value
     * 
     * @access public
     * @param array $columns
     * @return array
     */
    public function product_list_custom_column_value($column)
    {
        global $post;
        global $woocommerce;
        global $the_product;

        if (empty($the_product) || $the_product->id != $post->ID) {
            $the_product = get_product($post);
        }

        if ($column == 'rpwcm') {
            if (self::is_membership($the_product->id)) {
                $tip = $the_product->product_type == 'simple' ? __('This product is a membership', 'woocommerce-membership') : __('Contains at least one membership', 'woocommerce-membership');
                echo '<i class="fa fa-group rpwcm_product_list_icon tips" data-tip="' . $tip . '"></i>';
            }
        }
    }

    /**
     * Expand list search context
     * 
     * @access public
     * @param string $join
     * @return string
     */
    public function expand_list_search_context_join($join)
    {
        global $typenow;
        global $pagenow;
        global $wpdb;

        if ($pagenow == 'edit.php' && $typenow == 'product' && !empty($_GET['membership_plan']) && is_numeric($_GET['membership_plan'])) {
            $join .= 'LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
        }

        return $join;
    }

    /**
     * Expand list search context with more fields
     * 
     * @access public
     * @param string $where
     * @return string
     */
    public function expand_list_search_context_where($where)
    {
        global $typenow;
        global $pagenow;
        global $wpdb;

        // Filter by Membership Product
        if ($pagenow == 'edit.php' && $typenow == 'product' && isset($_GET['membership_plan']) && is_numeric($_GET['membership_plan']) && class_exists('WC_Product')) {
            $where = sprintf(' AND ' . $wpdb->posts . '.post_type = \'product\' AND (' . $wpdb->postmeta . '.meta_key IN (\'_rpwcm_plans\', \'_rpwcm_child_plans\')) AND (' . $wpdb->postmeta . '.meta_value LIKE %s)', intval($_GET['membership_plan']));
        }

        return $where;
    }

    /**
     * Expand list search context with more fields - group results by id
     * 
     * @access public
     * @param string $groupby
     * @return string
     */
    public function expand_list_search_context_group_by($groupby)
    {
        global $typenow;
        global $pagenow;
        global $wpdb;

        if ($pagenow == 'edit.php' && $typenow == 'product' && !empty($_GET['membership_plan']) && is_numeric($_GET['membership_plan'])) {
            $groupby = $wpdb->posts . '.ID';
        }

        return $groupby;
    }

    /**
     * Update membership product count
     * 
     * @access public
     * @param array $plans
     * @return void
     */
    public static function update_count($product_id, $plans = array())
    {
        // Get plans by product id
        $plans = !empty($plans) ? $plans : get_post_meta($product_id, '_rpwcm_plans');
        $plans = !empty($plans)? $plans : get_post_meta($product_id, '_rpwcm_child_plans');

        foreach ($plans as $plan_id) {
            WooCommerce_Membership_Plan::update_product_count($plan_id);
        }
    }

}

new WooCommerce_Membership_Product();

}