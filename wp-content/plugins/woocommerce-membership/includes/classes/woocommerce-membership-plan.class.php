<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Membership Plan object clas
 * 
 * @class WooCommerce_Membership_Plan
 * @package WooCommerce_Membership
 * @author RightPress
 */
if (!class_exists('WooCommerce_Membership_Plan')) {

class WooCommerce_Membership_Plan
{
    private static $post_type = 'membership_plan';
    private static $cache = array();
    public static $all_plans;
    public static $all_plan_keys;

    /**
     * Constructor class
     * 
     * @access public
     * @param mixed $id
     * @return void
     */
    public function __construct($id = null)
    {
        if ($id === null) {

            // Hook some actions on init
            add_action('init', array($this, 'on_init'), 99);

            // Actions related to this post type
            add_action('restrict_manage_posts', array($this, 'add_list_filters'));
            add_filter('parse_query', array($this, 'handle_list_filter_queries'));
            add_action('manage_membership_plan_posts_columns', array($this, 'manage_list_columns'));
            add_action('manage_membership_plan_posts_custom_column', array($this, 'manage_list_column_values'), 10, 2);
            add_filter('views_edit-membership_plan', array($this, 'manage_list_views'));
            add_filter('bulk_actions-edit-membership_plan', array($this, 'manage_list_bulk_actions'));
            add_filter('posts_join', array($this, 'expand_list_search_context_join'));
            add_filter('posts_where', array($this, 'expand_list_search_context_where'));
            add_filter('posts_groupby', array($this, 'expand_list_search_context_group_by'));
            add_action('save_post', array($this, 'save_meta_box'), 99, 2);
            add_action('before_delete_post', array($this, 'post_deleted'));
            add_action('trashed_post', array($this, 'post_trashed'));

            // Ajax handlers
            add_action('wp_ajax_get_membership_plan_key', array($this, 'ajax_get_membership_plan_key'));
            add_action('wp_ajax_get_membership_plan_key', array($this, 'ajax_get_membership_plan_key'));
        }
        else {
            $this->id = $id;
            $this->populate();
        }
    }

    /**
     * Run on WP init
     * 
     * @access public
     * @return void
     */
    public function on_init()
    {
        $this->add_post_type();
    }

    /**
     * Add membership_plan post type
     * 
     * @access public
     * @return void
     */
    public function add_post_type()
    {
        // Define labels
        $labels = array(
            'name'               => __('Membership Plans', 'woocommerce-membership'),
            'singular_name'      => __('Membership Plan', 'woocommerce-membership'),
            'add_new'            => __('Add Plan', 'woocommerce-membership'),
            'add_new_item'       => __('Add Membership Plan', 'woocommerce-membership'),
            'edit_item'          => __('Edit Plan', 'woocommerce-membership'),
            'new_item'           => __('New Membership Plan', 'woocommerce-membership'),
            'all_items'          => __(' Plans', 'woocommerce-membership'),
            'view_item'          => __('View Membership Plan', 'woocommerce-membership'),
            'search_items'       => __('Search Plans', 'woocommerce-membership'),
            'not_found'          => __('No Plans Found', 'woocommerce-membership'),
            'not_found_in_trash' => __('No Plans Found In Trash', 'woocommerce-membership'),
            'parent_item_colon'  => '',
            'menu_name'          => __('Membership', 'woocommerce-membership'),
        );

        // Define settings
        $args = array(
            'labels'               => $labels,
            'description'          => __('WooCommerce Membership Plans', 'woocommerce-membership'),
            'public'               => false,
            'show_ui'              => true,
            'menu_position'        => 56,
            'capability_type'      => 'post',
            'capabilities'         => array(
                'create_posts'     => true,
            ),
            'map_meta_cap'         => true,
            'supports'             => array('title'),
            'register_meta_box_cb' => array($this, 'add_meta_boxes'),
        );

        // Register new post type
        register_post_type(self::$post_type, $args);

        // Register custom taxonomy (membership status)
        register_taxonomy('plan_status', self::$post_type, array(
            'label'             => __('Status', 'woocommerce-membership'),
            'labels'            => array(
                'name'          => __('Status', 'woocommerce-membership'),
                'singular_name' => __('Status', 'woocommerce-membership'),
            ),
            'public'            => false,
            'show_admin_column' => true,
            'query_var'         => true,
        ));

        // Register custom terms - membership plan status
        foreach (WooCommerce_Membership_Plan::get_statuses() as $status_key => $status) {
            if (!term_exists($status_key, 'plan_status')) {
                wp_insert_term($status['title'], 'plan_status', array(
                    'slug' => $status_key,
                ));
            }
        }

        // Change some default behavior, values etc.
        add_filter('enter_title_here', array($this, 'enter_title_here'));
    }

    /**
     * Change "Enter title here" text
     * 
     * @access public
     * @param string $title
     * @return string
     */
    public function enter_title_here($title)
    {
        global $typenow;

        if ($typenow == self::$post_type) {
            $title = __('Enter plan name here', 'woocommerce-membership');
        }

        return $title;
    }

    /**
     * Return membership plan key from title
     * 
     * @access public
     * @return string
     */
    public function ajax_get_membership_plan_key()
    {
        if (isset($_POST['data']) && $title = self::create_key_from_title($_POST['data'])) {
            echo json_encode(array(
                'error' => 0,
                'title' => $title,
            ));
            exit;
        }

        echo json_encode(array(
            'error' => 1
        ));
        exit;
    }

    /**
     * Add meta boxes
     * 
     * @access public
     * @param mixed $post
     * @return void
     */
    public function add_meta_boxes($post)
    {
        // General membership plan details block
        add_meta_box(
            'rpwcm_membership_plan_details',
            __('Membership Plan Details', 'woocommerce-membership'),
            array($this, 'render_meta_box_details'),
            'membership_plan',
            'normal',
            'high'
        );

        // Related products
        add_meta_box(
            'rpwcm_membership_plan_products',
            __('Related Products', 'woocommerce-membership'),
            array($this, 'render_meta_box_products'),
            'membership_plan',
            'normal',
            'high'
        );

        // Members
        add_meta_box(
            'rpwcm_membership_plan_members',
            __('Members', 'woocommerce-membership'),
            array($this, 'render_meta_box_members'),
            'membership_plan',
            'normal',
            'high'
        );

        // Membership_plan actions
        add_meta_box(
            'rpwcm_membership_plan_actions',
            __('Plan Actions', 'woocommerce-membership'),
            array($this, 'render_meta_box_actions'),
            'membership_plan',
            'side',
            'default'
        );
    }

    /**
     * Render membership_plan edit page meta box Membership Details content
     * 
     * @access public
     * @param mixed $post
     * @return void
     */
    public function render_meta_box_details($post)
    {
        $plan = self::cache($post->ID);

        if (!$plan) {
            return;
        }

        // Get membership plan statuses
        $plan_statuses = WooCommerce_Membership_Plan::get_statuses();

        // Load view
        include RPWCM_PLUGIN_PATH . '/includes/views/backend/plan/details.php';
    }

    /**
     * Render membership plan edit page meta box Membership Products content
     * 
     * @access public
     * @param mixed $post
     * @return void
     */
    public function render_meta_box_products($post)
    {
        $plan = self::cache($post->ID);

        if (!$plan) {
            return;
        }

        // Get membership plan products
        $products = $plan->get_products(true);

        // Load view
        include RPWCM_PLUGIN_PATH . '/includes/views/backend/plan/products.php';
    }

    /**
     * Render membership plan edit page meta box Membership Members content
     * 
     * @access public
     * @param mixed $post
     * @return void
     */
    public function render_meta_box_members($post)
    {
        $plan = self::cache($post->ID);

        if (!$plan) {
            return;
        }

        // Get membership plan members
        $members = $plan->get_members_list();

        // Load view
        include RPWCM_PLUGIN_PATH . '/includes/views/backend/plan/members.php';
    }

    /**
     * Render membership plan edit page meta box Membership Actions content
     * 
     * @access public
     * @param mixed $post
     * @return void
     */
    public function render_meta_box_actions($post)
    {
        $plan = self::cache($post->ID);

        if (!$plan) {
            return;
        }

        // Get membership plan actions
        $actions = $plan->get_actions();

        // Load view
        include RPWCM_PLUGIN_PATH . '/includes/views/backend/plan/actions.php';
    }

    /**
     * Save custom fields from edit page
     * 
     * @access public
     * @param int $post_id
     * @param object $post
     * @return void
     */
    public function save_meta_box($post_id, $post)
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

        // Proceed only if post type is membership plan
        if ($post->post_type != 'membership_plan') {
            return;
        }

        $plan = self::cache($post_id);

        if (!$plan) {
            return;
        }

        // Actions
        if (!empty($_POST['rpwcm_plan_button']) && $_POST['rpwcm_plan_button'] == 'actions' && !empty($_POST['rpwcm_plan_actions'])) {
            switch ($_POST['rpwcm_plan_actions']) {

                // Save
                case 'save':

                    if (empty($_POST['post_title'])) {
                        return;
                    }

                    // Prevent infinite loop
                    remove_action('save_post', array($this, 'save_meta_box'), 99, 2);

                    // New post?
                    if ($post->post_status == 'draft') {
                        wp_publish_post($post_id);
                        $plan->update_field('name', $_POST['post_title']);
                        $plan->update_field('status', 'enabled');
                        $plan->update_field('key', self::create_key_from_title($_POST['post_title']));
                    }

                    // Existing post - update title only and only if it does not match current title
                    else if ($_POST['title'] != $plan->name) {
                        $plan->update_field('name', $_POST['post_title']);
                    }

                    add_action('save_post', array($this, 'save_meta_box'), 99, 2);

                    break;

                // Disable
                case 'disable':
                    $plan->update_field('status', 'disabled');
                    break;

                // Enable
                case 'enable':
                    $plan->update_field('status', 'enabled');
                    break;

                default:
                    break;
            }
        }
    }

    /**
     * Add filtering capabilities
     * 
     * @access public
     * @return void
     */
    public function add_list_filters()
    {
        global $typenow;
        global $wp_query;

        if ($typenow != self::$post_type) {
            return;
        }

        // Extract selected filter options
        $selected = array();

        foreach (array('plan_status') as $taxonomy) {
            if (!empty($wp_query->query[$taxonomy]) && is_numeric($wp_query->query[$taxonomy])) {
                $selected[$taxonomy] = $wp_query->query[$taxonomy];
            }
            else if (!empty($wp_query->query[$taxonomy])) {
                $term = get_term_by('slug', $wp_query->query[$taxonomy], $taxonomy);
                $selected[$taxonomy] = $term ? $term->term_id : 0;
            }
            else {
                $selected[$taxonomy] = 0;
            }
        }

        // Add statuses
        wp_dropdown_categories(array(
            'show_option_all'   =>  __('All statuses', 'woocommerce-membership'),
            'taxonomy'          =>  'plan_status',
            'name'              =>  'plan_status',
            'selected'          =>  $selected['plan_status'],
            'show_count'        =>  true,
            'hide_empty'        =>  false,
        ));
    }

    /**
     * Handle list filter queries
     * 
     * @access public
     * @param object $query
     * @return void
     */
    public function handle_list_filter_queries($query)
    {
        global $pagenow;
        global $typenow;

        if ($pagenow != 'edit.php' || $typenow != self::$post_type) {
            return;
        }

        $qv = &$query->query_vars;

        foreach (array('plan_status') as $taxonomy) {
            if (isset($qv[$taxonomy]) && is_numeric($qv[$taxonomy]) && $qv[$taxonomy] != 0) {
                $term = get_term_by('id', $qv[$taxonomy], $taxonomy);
                $qv[$taxonomy] = $term->slug;
            }
        }
    }

    /**
     * Manage list columns
     * 
     * @access public
     * @param array $columns
     * @return array
     */
    public function manage_list_columns($columns)
    {
        $new_columns = array();

        foreach ($columns as $column_key => $column) {
            $allowed_columns = array(
                'cb',
            );

            if (in_array($column_key, $allowed_columns)) {
                $new_columns[$column_key] = $column;
            }
        }

        $new_columns['name']        = __('Name', 'woocommerce-membership');
        $new_columns['key']         = __('Key', 'woocommerce-membership');
        $new_columns['status']      = __('Status', 'woocommerce-membership');
        $new_columns['products']    = __('Products', 'woocommerce-membership');
        $new_columns['members']       = __('Members', 'woocommerce-membership');

        return $new_columns;
    }

    /**
     * Manage list column values
     * 
     * @access public
     * @param array $column
     * @param int $post_id
     * @return void
     */
    public function manage_list_column_values($column, $post_id)
    {
        $plan = self::cache($post_id);

        switch ($column) {

            case 'name':
                WooCommerce_Membership::print_link_to_post($plan->id, $plan->name);
                break;

            case 'key':
                echo '<code>' . $plan->key . '</code>';
                break;

            case 'status':
                echo '<a class="membership_plan_status_' . $plan->status . '" href="edit.php?post_type=membership_plan&amp;plan_status=' . $plan->status . '">' . $plan->status_title . '</a>';
                break;

            case 'products':
                if ($plan->product_count == 0) {
                    echo 0;
                }
                else {
                    echo '<a href="edit.php?post_type=product&amp;membership_plan=' . $post_id . '">' . $plan->product_count . '</a>';
                }
                break;
 
           case 'members':
                echo '<a href="users.php?role=' . $plan->key . '">' . $plan->member_count . '</a>';
                break;

            default:
                break;
        }
    }

    /**
     * Manage list bulk actions
     * 
     * @access public
     * @param array $actions
     * @return array
     */
    public function manage_list_bulk_actions($actions)
    {
        $new_actions = array();

        foreach ($actions as $action_key => $action) {
            if (in_array($action_key, array('trash', 'untrash', 'delete'))) {
                $new_actions[$action_key] = $action;
            }
        }

        return $new_actions;
    }

    /**
     * Manage list views
     * 
     * @access public
     * @param array $views
     * @return array
     */
    public function manage_list_views($views)
    {
        $new_views = array();

        foreach ($views as $view_key => $view) {
            if (in_array($view_key, array('all', 'trash'))) {
                $new_views[$view_key] = $view;
            }
        }

        return $new_views;
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

        if ($pagenow == 'edit.php' && $typenow == 'membership_plan' && isset($_GET['s']) && $_GET['s'] != '') {
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

        // Define post types with search contexts, meta field whitelist (searchable meta fields) etc
        $post_types = array(
            'membership_plan' => array(
                'contexts' => array(
                ),
                'meta_whitelist' => array(
                ),
            ),
        );

        // Search
        if ($pagenow == 'edit.php' && isset($_GET['post_type']) && isset($post_types[$_GET['post_type']]) && !empty($_GET['s'])) {

            $search_phrase = trim($_GET['s']);
            $exact_match = false;
            $context = null;

            // Exact match?
            if (preg_match('/^\".+\"$/', $search_phrase) || preg_match('/^\'.+\'$/', $search_phrase)) {
                $exact_match = true;
                $search_phrase = substr($search_phrase, 1, -1);
            }
            else if (preg_match('/^\\\\\".+\\\\\"$/', $search_phrase) || preg_match('/^\\\\\'.+\\\\\'$/', $search_phrase)) {
                $exact_match = true;
                $search_phrase = substr($search_phrase, 2, -2);
            }
            // Or search with context?
            else {
                foreach ($post_types[$_GET['post_type']]['contexts'] as $context_key => $context_value) {
                    if (preg_match('/^' . $context_key . '\:/i', $search_phrase)) {
                        $context = $context_value;
                        $search_phrase = trim(preg_replace('/^' . $context_key . '\:/i', '', $search_phrase));
                        break;
                    }
                }
            }

            // Search by ID?
            if ($context == 'ID') {
                $replacement = $wpdb->prepare(
                    '(' . $wpdb->posts . '.ID LIKE %s)',
                    $search_phrase
                );
            }

            // Search within other context
            else if ($context) {
                $replacement = $wpdb->prepare(
                    '(' . $wpdb->postmeta . '.meta_key LIKE %s) AND (' . $wpdb->postmeta . '.meta_value LIKE %s)',
                    $context,
                    $search_phrase
                );
            }

            // Regular search
            else {
                $whitelist = $wpdb->postmeta . '.meta_key IN (\'' . join('\', \'', $post_types[$_GET['post_type']]['meta_whitelist']) . '\')';

                // Exact match?
                if ($exact_match) {
                    $replacement = $wpdb->prepare(
                        '(' . $wpdb->posts . '.ID LIKE %s) OR (' . $wpdb->postmeta . '.meta_value LIKE %s)',
                        $search_phrase,
                        $search_phrase
                    );
                    $replacement = '(' . $whitelist . ' AND ' . $replacement . ')';

                }

                // Regular match
                else {
                    $replacement = '(' . $whitelist . ' AND ((' . $wpdb->posts . '.ID LIKE $1) OR (' . $wpdb->postmeta . '.meta_value LIKE $1)))';
                }
            }

            $where = preg_replace('/\(\s*' . $wpdb->posts . '.post_title\s+LIKE\s*(\'[^\']+\')\s*\)/', $replacement, $where);
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

        if ($pagenow == 'edit.php' && $typenow == 'membership_plan' && isset($_GET['s']) && $_GET['s'] != '') {
            $groupby = $wpdb->posts . '.ID';
        }

        return $groupby;
    }

    /**
     * Load object from cache
     * 
     * @access public
     * @param string $type
     * @param int $id
     * @return object
     */
    public static function cache($id)
    {
        if (!isset(self::$cache[$id])) {

            $object = new self($id);

            if (!$object) {
                return false;
            }

            self::$cache[$id] = $object;
        }

        return self::$cache[$id];
    }

    /**
     * Popuplate existing plan object with properties
     * 
     * @access public
     * @return void
     */
    public function populate()
    {
        if (!$this->id) {
            return false;
        }

        // Get post
        $post = get_post($this->id);

        if (!in_array($post->post_status, array('publish', 'trash'))) {
            return;
        }

        // Get status
        $statuses = self::get_statuses();
        $post_terms = wp_get_post_terms($this->id, 'plan_status');
        $this->status = $post_terms[0]->slug;
        $this->status_title = $statuses[$this->status]['title'];

        // Get other fields
        $post_meta = WooCommerce_Membership::unwrap_post_meta(get_post_meta($this->id));

        // Name
        $this->name = get_the_title($this->id);

        // Key
        $this->key = isset($post_meta['key']) ? $post_meta['key'] : '';

        // Product count
        $this->product_count = isset($post_meta['product_count']) ? $post_meta['product_count'] : 0;

        // Member count
        $this->member_count = isset($post_meta['member_count']) ? $post_meta['member_count'] : 0;
    }

    /**
     * Update single Membership Plan field
     * 
     * @access public
     * @return void
     */
    public function update_field($field, $value)
    {
        $this->$field = $value;

        switch ($field) {

            case 'status':

                $statuses = self::get_statuses();

                if (isset($statuses[$value])) {
                    $this->status_title = $statuses[$value]['title'];
                }

                wp_set_object_terms($this->id, $value, 'plan_status');

                break;

            case 'name':
                wp_update_post(array(
                    'ID'    => $this->id,
                    'title' => $value,
                ));
                break;

            default:
                update_post_meta($this->id, $field, $value);
                break;
        }
    }

    /**
     * Define and return all membership statuses
     * 
     * @access public
     * @return array
     */
    public static function get_statuses()
    {
        return array(
            'enabled'   => array(
                'title' => __('enabled', 'woocommerce-membership'),
            ),
            'disabled'    => array(
                'title' => __('disabled', 'woocommerce-membership'),
            ),
        );
    }

    /**
     * Get array of actions available
     * 
     * @access public
     * @return array
     */
    public function get_actions()
    {
        $actions = array();

        // Save plan details
        $actions['save'] = __('Save Plan', 'woocommerce-membership');

        // New plan?
        if (!isset($this->status)) {
            return $actions;
        }

        // Enable
        if ($this->status == 'disabled') {
            $actions['enable'] = __('Enable Plan', 'woocommerce-membership');
        }

        // Disable
        if ($this->status == 'enabled') {
            $actions['disable'] = __('Disable Plan', 'woocommerce-membership');
        }

        return $actions;
    }

    /**
     * Get array of IDs and names of WooCommerce Products that grant access to this membership plan
     * 
     * @access public
     * @param bool $names
     * @param bool $include_trashed
     * @return array
     */
    public function get_products($names = true, $include_trashed = true)
    {
        $products = array();

        $statuses = array('publish', 'pending', 'draft', 'future', 'private');

        if ($include_trashed) {
            $statuses[] = 'trashed';
        }

        // Simple product IDs
        $query = new WP_Query(array(
            'post_type'         => 'product',
            'post_status'       => $statuses,
            'posts_per_page'    => -1,
            'fields'            => 'ids',
            'meta_query'        => array(
                array(
                    'key'       => '_rpwcm_plans',
                    'value'     => $this->id,
                    'compare'   => '=',
                ),
            ),
        ));

        // Iterate over simple product IDs and get their names
        foreach ($query->posts as $product_id) {
            $products[$product_id] = array(
                'main_id'   => $product_id,
                'title'     => $names ? self::get_formatted_product_name($product_id) : '',
                'type'      => __('Simple Product', 'woocommerce-membership'),
            );
        }

        // Product variation IDs
        $query = new WP_Query(array(
            'post_type'         => 'product_variation',
            'post_status'       => $statuses,
            'posts_per_page'    => -1,
            'fields'            => 'ids',
            'meta_query'        => array(
                array(
                    'key'       => '_rpwcm_plans',
                    'value'     => $this->id,
                    'compare'   => '=',
                ),
            ),
        ));

        // Iterate over variation IDs and get their names and parent IDs
        foreach ($query->posts as $variation_id) {
            $parent = get_post_ancestors($variation_id);

            if (!empty($parent[0])) {
                if ($include_trashed || !in_array(get_post_status($parent[0]), array('auto-draft', 'inherit', 'trash'))) {
                    $products[$variation_id] = array(
                        'main_id'   => (string) $parent[0],
                        'title'     => $names ? self::get_formatted_product_name($variation_id) : '',
                        'type'      => __('Product Variation', 'woocommerce-membership'),
                    );
                }
            }
        }

        return $products;
    }

    /**
     * Get formatted product name
     * 
     * @access public
     * @param int $product_id
     * @return string
     */
    public function get_formatted_product_name($product_id)
    {
        $product = new WC_Product($product_id);
        return $product ? $product->get_formatted_name() : '';
    }

    /**
     * Get array of WordPress User IDs that are members of this membership plan
     * 
     * @access public
     * @return array
     */
    public function get_members_list()
    {
        if (empty($this->key)) {
            return array();
        }

        global $wpdb;

        // Fetch users
        $query = new WP_User_Query(array(
            'fields'        => array('ID', 'user_email'),
            'meta_key'      => $wpdb->prefix . 'capabilities',
            'meta_value'    => serialize(strval($this->key)),
            'meta_compare'  => 'LIKE',
        ));

        return $query->results;
    }

    /**
     * Create key (used as a WordPress capability) from Membership Plan title
     * 
     * @access public
     * @param string $title
     * @return string|bool
     */
    public static function create_key_from_title($title)
    {
        if (empty($title)) {
            return false;
        }

        $title = sanitize_title($title);
        $title = str_replace('-', '_', $title);

        $original_title = $title;
        $i = 1;

        while (self::capability_exists($title) || self::key_exists($title)) {
            $i++;
            $title = $original_title . '_' . $i;
        }

        return $title;
    }

    /**
     * Check if capability exists
     * 
     * @access public
     * @param string $capability
     * @return bool
     */
    public static function capability_exists($capability)
    {
        global $wp_roles;

        foreach ($wp_roles->roles as $role) {
            if (isset($role['capabilities'][$capability])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if key exists in other membership plans
     * 
     * @access public
     * @param string $key
     * @return bool
     */
    public static function key_exists($key)
    {
        $query = new WP_Query(array(
            'post_type'     => self::$post_type,
            'fields'        => 'ids',
            'meta_query'    => array(
                array(
                    'key'       => 'key',
                    'value'     => $key,
                    'compare'   => '=',
                ),
            ),
        ));

        return empty($query->posts) ? false : true;
    }

    /**
     * Get list of all plan keys for admin display
     * 
     * @access public
     * @return array
     */
    public static function get_list_of_all_plan_keys()
    {
        if (!self::$all_plan_keys) {
            self::$all_plan_keys = array();

            foreach (self::get_list_of_all_plans() as $plan_id => $plan_title) {
                if ($key = get_post_meta($plan_id, 'key', true)) {
                    self::$all_plan_keys[$key] = $plan_title;
                }
            }
        }

        return self::$all_plan_keys;
    }

    /**
     * Get list of all plans for admin display
     * 
     * @access public
     * @return array
     */
    public static function get_list_of_all_plans()
    {
        if (!self::$all_plans) {
            self::$all_plans = array();

            $query = new WP_Query(array(
                'post_type'         => self::$post_type,
                'post_status'       => array('publish', 'pending', 'draft', 'future', 'private', 'trash'),
                'posts_per_page'    => -1,
                'fields'            => 'ids',
            ));

            foreach ($query->posts as $id) {
                self::$all_plans[$id] = get_the_title($id);
            }
        }

        return self::$all_plans;
    }

    /**
     * Update product count
     * 
     * @access public
     * @param int $plan_id
     * @return void
     */
    public static function update_product_count($plan_id)
    {
        if ($plan = self::cache($plan_id)) {
            $plan->update_field('product_count', count($plan->get_products(false, false)));
        }
    }

    /**
     * Update member count
     * 
     * @access public
     * @param array $keys
     * @return void
     */
    public static function update_member_count($keys)
    {
        global $wpdb;

        foreach ($keys as $key) {
            if ($plan = self::get_by_key($key)) {

                // Fetch user count
                $query = new WP_User_Query(array(
                    'number'        => 1,
                    'offset'        => 0,
                    'count_total'   => true,
                    'meta_key'       => $wpdb->prefix . 'capabilities',
                    'meta_value'     => serialize(strval($key)),
                    'meta_compare'   => 'LIKE',
                ));

                $plan->update_field('member_count', $query->get_total());
            }
        }
    }

    /**
     * Grant access
     * 
     * @access public
     * @param int $plan_id
     * @param int $user_id
     * @return void
     */
    public static function add_member($plan_id, $user_id)
    {
        if ($plan = self::cache($plan_id)) {
            if (!empty($user_id) && !empty($plan->key) && $user = get_user_by('id', $user_id)) {
                $user->add_cap($plan->key);
                update_user_meta($user_id, '_rpwcm_' . $plan->key . '_since', time());
                self::update_member_count(array($plan->key));
            }
        }
    }

    /**
     * Remove access
     * 
     * @access public
     * @param int $plan_id
     * @param int $user_id
     * @return void
     */
    public static function remove_member($plan_id, $user_id)
    {
        if ($plan = self::cache($plan_id)) {
            if (!empty($user_id) && !empty($plan->key) && $user = get_user_by('id', $user_id)) {
                $user->remove_cap($plan->key);
                delete_user_meta($user_id, '_rpwcm_' . $plan->key . '_since');
                self::update_member_count(array($plan->key));
            }
        }
    }

    /**
     * Remove plan from products
     * 
     * @access public
     * @param int $post_id
     * @return void
     */
    public static function remove_plan_from_products($post_id)
    {
        $plan = self::cache($post_id);

        if (isset($plan->key)) {

            // Get related products
            $products = $plan->get_products(false);

            foreach ($products as $product_id => $product) {

                // Remove plan from product
                WooCommerce_Membership_Product::remove_plan($product_id, $post_id);

                // Recheck if simple product is still a membership
                WooCommerce_Membership_Product::recheck_membership_status($product_id);

                // Recheck if variable product is still a membership
                if ($product_id != $product['main_id']) {
                    WooCommerce_Membership_Product::recheck_membership_status($product['main_id']);
                }
            }
        }
    }

    /**
     * Remove plan from members
     * 
     * @access public
     * @param int $post_id
     * @return void
     */
    public static function remove_plan_from_members($post_id)
    {
        global $wpdb;

        $plan = self::cache($post_id);

        if (isset($plan->key)) {

            // Fetch users
            $query = new WP_User_Query(array(
                'meta_key'      => $wpdb->prefix . 'capabilities',
                'meta_value'    => serialize(strval($plan->key)),
                'meta_compare'  => 'LIKE',
            ));

            foreach ($query->results as $user) {
                $user->remove_cap($plan->key);
                delete_user_meta($user->ID, '_rpwcm_' . $plan->key . '_since');
            }
        }
    }

    /**
     * Remove plan from posts (remove access restriction)
     * This is invoked when membership plan is deleted permanently
     * 
     * @access public
     * @param int $post_id
     * @return void
     */
    public static function remove_plan_from_posts($post_id)
    {
        if ($key = get_post_meta($post_id, 'key', true)) {

            // Get all (and any) posts that have restriction by this key
            $query = new WP_Query(array(
                'post_status'       => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
                'posts_per_page'    => -1,
                'fields'            => 'ids',
                'meta_query'        => array(
                    array(
                        'key'       => '_rpwcm_only_caps',
                        'value'     => $key,
                        'compare'   => '=',
                    ),
                ),
            ));

            // Iterate over all found post IDs and delete restriction
            foreach ($query->posts as $post_id) {
                delete_post_meta($post_id, '_rpwcm_only_caps', $key);
            }
        }
    }

    /**
     * Get by key
     * 
     * @access public
     * @param string $key
     * @return object|bool
     */
    public static function get_by_key($key)
    {
        $query = new WP_Query(array(
            'post_type'         => 'membership_plan',
            'posts_per_page'    => -1,
            'fields'            => 'ids',
            'meta_query'        => array(
                array(
                    'key'       => 'key',
                    'value'     => $key,
                    'compare'   => '=',
                ),
            ),
        ));

        if (!empty($query->posts)) {
            return self::cache(array_shift($query->posts));
        }

        return false;
    }

    /**
     * Plan deleted
     * 
     * @access public
     * @param int $post_id
     * @return void
     */
    public function post_deleted($post_id)
    {
        global $post_type;

        if ($post_type == 'membership_plan') {
            self::remove_plan_from_products($post_id);
            self::remove_plan_from_members($post_id);
            self::remove_plan_from_posts($post_id);
        }
    }

    /**
     * Plan trashed
     * 
     * @access public
     * @param int $post_id
     * @return void
     */
    public function post_trashed($post_id)
    {
        global $post_type;

        if ($post_type == 'membership_plan') {
            $plan = self::cache($post_id);
            $plan->update_field('status', 'disabled');
        }
    }

    /**
     * Leave only keys of enabled membership plans
     * 
     * @access public
     * @param array $keys
     * @return array
     */
    public static function enabled_keys_only($keys)
    {
        $enabled_keys = array();

        $GLOBALS['rpwcm_getting_enabled_keys'] = true;

        // Simple product IDs
        $query = new WP_Query(array(
            'post_type'         => 'membership_plan',
            'post_status'       => 'publish',
            'posts_per_page'    => -1,
            'fields'            => 'ids',
            'meta_query'        => array(
                array(
                    'key'       => 'key',
                    'value'     => $keys,
                    'compare'   => 'IN',
                ),
            ),
            'tax_query' => array(
                array(
                    'taxonomy'  => 'plan_status',
                    'field'     => 'slug',
                    'terms'     => 'enabled',
                ),
            ),
        ));

        $GLOBALS['rpwcm_getting_enabled_keys'] = false;

        foreach ($query->posts as $id) {
            $enabled_keys[] = get_post_meta($id, 'key', true);
        }

        return $enabled_keys;
    }

}

new WooCommerce_Membership_Plan();

}