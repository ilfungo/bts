<?php

/**
 * Plugin Name: WooCommerce Membership
 * Plugin URI: http://www.rightpress.net/woocommerce-membership
 * Description: Sell online memberships with WooCommerce and create members-only areas on your website
 * Version: 1.0.1
 * Author: RightPress
 * Author URI: http://www.rightpress.net
 * Requires at least: 3.6
 * Tested up to: 3.9
 * 
 * Text Domain: woocommerce-membership
 * Domain Path: /languages
 * 
 * @package WooCommerce_Membership
 * @category Core
 * @author RightPress
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define Constants
define('RPWCM_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('RPWCM_PLUGIN_URL', plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__)));
define('RPWCM_VERSION', '1.0.1');
define('RPWCM_OPTIONS_VERSION', '1');

if (!class_exists('WooCommerce_Membership')) {

/**
 * Main plugin class
 * 
 * @package WooCommerce_Membership
 * @author RightPress
 */
class WooCommerce_Membership
{
    // WARNING: ONLY CHANGE THIS IF YOU KNOW WHAT WILL HAPPEN (AND IF YOU DON'T, THERE'S NO NEED TO CHANGE THIS)
    public static $debug = false;
    // WARNING: ONLY CHANGE THIS IF YOU KNOW WHAT WILL HAPPEN (AND IF YOU DON'T, THERE'S NO NEED TO CHANGE THIS)

    // Singleton instance
    private static $instance = false;

    /**
     * Singleton control
     */
    public static function get_instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Class constructor
     * 
     * @access public
     * @return void
     */
    public function __construct()
    {
        // Load translation
        load_plugin_textdomain('woocommerce-membership', false, dirname(plugin_basename(__FILE__)) . '/languages/');

        // Load includes
        foreach (glob(RPWCM_PLUGIN_PATH . '/includes/*.inc.php') as $filename)
        {
            include $filename;
        }

        // Load classes
        foreach (glob(RPWCM_PLUGIN_PATH . '/includes/classes/*.class.php') as $filename)
        {
            include $filename;
        }

        // Initialize plugin configuration
        $this->settings = rpwcm_plugin_settings();

        // Load/parse plugin settings
        $this->opt = $this->get_options();

        // Hook to WordPress 'init' action
        add_action('init', array($this, 'on_init'), 99);

        // Admin-only hooks
        if (is_admin() && !defined('DOING_AJAX')) {

            // Additional Plugins page links
            add_filter('plugin_action_links_'.plugin_basename(__FILE__), array($this, 'plugins_page_links'));

            // Add settings page menu link
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_init', array($this, 'plugin_options_setup'));

            // Load backend assets conditionally
            if (preg_match('/post_type=(membership)/i', $_SERVER['QUERY_STRING'])) {
                add_action('admin_enqueue_scripts', array($this, 'enqueue_backend_assets'));
            }

            // ... and load some assets on all pages
            add_action('admin_enqueue_scripts', array($this, 'enqueue_backend_assets_all'), 99);
        }

        // Other hooks
        add_action('add_meta_boxes', array($this, 'remove_meta_boxes'), 99, 2);
    }

    /**
     * Add settings link on plugins page
     * 
     * @access public
     * @param array $links
     * @return void
     */
    public function plugins_page_links($links)
    {
        $settings_link = '<a href="http://support.rightpress.net/" target="_blank">'.__('Support', 'woocommerce-membership').'</a>';
        array_unshift($links, $settings_link);
        // No settings for now
        // $settings_link = '<a href="edit.php?post_type=membership&page=settings">'.__('Settings', 'woocommerce-membership').'</a>';
        // array_unshift($links, $settings_link);
        return $links; 
    }

    /**
     * WordPress 'init'
     * 
     * @access public
     * @return void
     */
    public function on_init()
    {
        // Display granted memberships on frontend single order view page (not implemented yet)
        /*add_action(
            apply_filters('woocommerce_membership_order_view_hook', 'woocommerce_order_details_after_order_table'),
            array($this, 'display_frontend_order_granted_memberships'),
            apply_filters('woocommerce_membership_order_view_position', 9)
        );*/
    }

    /**
     * Exctract some options from plugin settings array
     * 
     * @access public
     * @param string $name
     * @param bool $split_by_page
     * @return array
     */
    public function options($name, $split_by_page = false)
    {
        $results = array();

        // Iterate over settings array and extract values
        foreach ($this->settings as $page => $page_value) {
            $page_options = array();

            foreach ($page_value['children'] as $section => $section_value) {
                foreach ($section_value['children'] as $field => $field_value) {
                    if (isset($field_value[$name])) {
                        $page_options['rpwcm_' . $field] = $field_value[$name];
                    }
                }
            }

            $results[preg_replace('/_/', '-', $page)] = $page_options;
        }

        $final_results = array();

        if (!$split_by_page) {
            foreach ($results as $value) {
                $final_results = array_merge($final_results, $value);
            }
        }
        else {
            $final_results = $results;
        }

        return $final_results;
    }

    /**
     * Get options saved to database or default options if no options saved
     * 
     * @access public
     * @return array
     */
    public function get_options()
    {
        // Get options from database
        $saved_options = get_option('rpwcm_options', array());

        // Get current version (for major updates in future)
        if (!empty($saved_options)) {
            if (isset($saved_options[RPWCM_OPTIONS_VERSION])) {
                $saved_options = $saved_options[RPWCM_OPTIONS_VERSION];
            }
            else {
                // Migrate options here if needed...
            }
        }

        if (is_array($saved_options)) {
            return array_merge($this->options('default'), $saved_options);
        }
        else {
            return $this->options('default');
        }
    }

    /**
     * Return option
     * Warning: do not use in WooCommerce_Membership class constructor!
     * 
     * @access public
     * @param string $key
     * @return string|bool
     */
    public static function option($key)
    {
        $woocommerce_membership = WooCommerce_Membership::get_instance();
        return isset($woocommerce_membership->opt['rpwcm_' . $key]) ? $woocommerce_membership->opt['rpwcm_' . $key] : false;
    }

    /*
     * Update single option
     * 
     * @access public
     * @return bool
     */
    public function update_option($key, $value)
    {
        $this->opt[$key] = $value;
        return update_option('rpwcm_options', $this->opt);
    }

    /**
     * Add admin menu items
     * 
     * @access public
     * @return void
     */
    public function add_admin_menu()
    {
        // Add submenu links
        // No settings for initial version
        /*add_submenu_page(
            'edit.php?post_type=membership_plan',
            __('Settings', 'woocommerce-membership'),
            __('Settings', 'woocommerce-membership'),
            apply_filters('woocommerce_membership_capability', 'manage_options', 'settings'),
            'rpwcm_settings',
            array($this, 'set_up_settings_pages')
        );*/
    }

    /**
     * Register our settings fields with WordPress
     * 
     * @access public
     * @return void
     */
    public function plugin_options_setup()
    {
        // Check if current user can manage plugin options
        if (current_user_can(apply_filters('woocommerce_membership_capability', 'manage_options', 'settings'))) {

            // Iterate over tabs
            foreach ($this->settings as $tab_key => $tab) {

                register_setting(
                    'rpwcm_opt_group_' . $tab_key,
                    'rpwcm_options',
                    array($this, 'options_validate')
                );

                // Iterate over sections
                foreach ($tab['children'] as $section_key => $section) {

                    add_settings_section(
                        $section_key,
                        $section['title'],
                        '__return_false',
                        'rpwcm-admin-' . str_replace('_', '-', $tab_key)
                    );

                    // Iterate over fields
                    foreach ($section['children'] as $field_key => $field) {
                        add_settings_field(
                            'rpwcm_' . $field_key,
                            $field['title'],
                            array('WooCommerce_Membership', 'render_field_' . $field['type']),
                            'rpwcm-admin-' . str_replace('_', '-', $tab_key),
                            $section_key,
                            array(
                                'name'      => 'rpwcm_' . $field_key,
                                'options'   => $this->opt,
                                'after'     => isset($field['after']) ? $field['after'] : '',
                            )
                        );
                    }
                }
            }
        }
    }

    /**
     * Render checkbox field
     * 
     * @access public
     * @return void
     */
    public static function render_field_checkbox($args = array())
    {
        printf(
            '<input type="checkbox" id="%s" name="rpwcm_options[%s]" value="1" %s />%s',
            $args['name'],
            $args['name'],
            checked($args['options'][$args['name']], true, false),
            !empty($args['after']) ? '&nbsp;&nbsp;' . $args['after'] : ''
        );
    }

    /**
     * Render checkbox field
     * 
     * @access public
     * @return void
     */
    public static function render_field_text($args = array())
    {
        printf(
            '<input type="text" id="%s" name="rpwcm_options[%s]" value="%s" class="rpwcm_field_width" />%s',
            $args['name'],
            $args['name'],
            $args['options'][$args['name']],
            !empty($args['after']) ? '&nbsp;&nbsp;' . $args['after'] : ''
        );
    }

    /**
     * Render multiselect field
     * 
     * @access public
     * @return void
     */
    public static function render_field_multiselect($args = array())
    {
        printf('<select multiple name="%s[]" class="rpwcm_field_multiselect %s">', $args['name'], $args['class']);

        foreach ($args['values'] as $value_key => $value) {
            printf('<option value="%s" %s>%s</option>', $value_key, (in_array($value_key, $args['selected']) ? 'selected="selected"' : ''), $value);
        }

        echo '</select>';
    }

    /**
     * Validate saved options
     * 
     * @access public
     * @param array $input
     * @return void
     */
    public function options_validate($input)
    {
        $output = $this->opt;

        if (empty($_POST['current_tab']) || !isset($this->settings[$_POST['current_tab']])) {
            return $output;
        }

        $errors = array();

        // Iterate over fields and validate new values
        foreach ($this->settings[$_POST['current_tab']]['children'] as $section_key => $section) {
            foreach ($section['children'] as $field_key => $field) {

                $current_field_key = 'rpwcm_' . $field_key;

                switch($field['validation']['rule']) {

                    // Checkbox
                    case 'bool':
                        $input[$current_field_key] = (!isset($input[$current_field_key]) || $input[$current_field_key] == '') ? '0' : $input[$current_field_key];
                        if (in_array($input[$current_field_key], array('0', '1')) || ($input[$current_field_key] == '' && $field['validation']['empty'] == true)) {
                            $output[$current_field_key] = $input[$current_field_key];
                        }
                        else {
                            array_push($errors, array('setting' => $current_field_key, 'code' => 'bool', 'title' => $field['title']));
                        }
                        break;

                    // Number
                    case 'number':
                        if (is_numeric($input[$current_field_key]) || ($input[$current_field_key] == '' && $field['validation']['empty'] == true)) {
                            $output[$current_field_key] = $input[$current_field_key];
                        }
                        else {
                            array_push($errors, array('setting' => $current_field_key, 'code' => 'number', 'title' => $field['title']));
                        }
                        break;

                    // Option
                    case 'option':
                        if (isset($input[$current_field_key]) && (isset($field['values'][$input[$current_field_key]]) || ($input[$current_field_key] == '' && $field['validation']['empty'] == true))) {
                            $output[$current_field_key] = $input[$current_field_key];
                        }
                        else if (!isset($input[$current_field_key])) {
                            $output[$current_field_key] = '';
                        }
                        else {
                            array_push($errors, array('setting' => $current_field_key, 'code' => 'option', 'title' => $field['title']));
                        }
                        break;

                    // Text input
                    default:
                        break;
                }
            }
        }

        // Display settings updated message
        add_settings_error(
            'rpwcm',
            'rpwcm_' . 'settings_updated',
            __('Your settings have been saved.', 'woocommerce-membership'),
            'updated'
        );

        // Display errors
        foreach ($errors as $error) {
            $reverted = __('Reverted to a previous value.', 'woocommerce-membership');

            $messages = array(
                'number' => __('must be numeric', 'woocommerce-membership') . '. ' . $reverted,
                'bool' => __('must be either 0 or 1', 'woocommerce-membership') . '. ' . $reverted,
                'option' => __('is not allowed', 'woocommerce-membership') . '. ' . $reverted,
                'email' => __('is not a valid email address', 'woocommerce-membership') . '. ' . $reverted,
                'url' => __('is not a valid URL', 'woocommerce-membership') . '. ' . $reverted,
                'string' => __('is not a valid text string', 'woocommerce-membership') . '. ' . $reverted,
            );

            add_settings_error(
                'rpwcm',
                $error['code'],
                __('Value of', 'woocommerce-membership') . ' "' . $error['title'] . '" ' . $messages[$error['code']]
            );
        }

        return $output;
    }

    /**
     * Set up settings pages
     * 
     * @access public
     * @return void
     */
    public function set_up_settings_pages()
    {
        // Get current page & tab ids
        $current_tab = $this->get_current_settings_tab();

        // Print notices
        settings_errors('rpwcm');

        // Print header
        include RPWCM_PLUGIN_PATH . 'includes/views/backend/settings/header.php';

        // Print settings page content
        include RPWCM_PLUGIN_PATH . 'includes/views/backend/settings/fields.php';

        // Print footer
        include RPWCM_PLUGIN_PATH . 'includes/views/backend/settings/footer.php';
    }

    /**
     * Get current settings tab
     * 
     * @access public
     * @return string
     */
    public function get_current_settings_tab()
    {
        // Check if we know tab identifier
        if (isset($_GET['tab']) && isset($this->settings[$_GET['tab']])) {
            $tab = $_GET['tab'];
        }
        else {
            $keys = array_keys($this->settings);
            $tab = array_shift($keys);
        }

        return $tab;
    }

    /**
     * Load backend assets conditionally
     * 
     * @access public
     * @return bool
     */
    public function enqueue_backend_assets()
    {
        // Our own scripts and styles
        wp_register_script('rpwcm-backend-scripts', RPWCM_PLUGIN_URL . '/assets/js/backend.js', array('jquery'), RPWCM_VERSION);
        wp_register_style('rpwcm-backend-styles', RPWCM_PLUGIN_URL . '/assets/css/backend.css', array(), RPWCM_VERSION);

        // Scripts
        wp_enqueue_script('rpwcm-backend-scripts');

        // Styles
        wp_enqueue_style('rpwcm-backend-styles');
    }

    /**
     * Load backend assets on all pages
     * 
     * @access public
     * @return bool
     */
    public function enqueue_backend_assets_all()
    {
        // Our own scripts and styles
        wp_register_style('rpwcm-backend-styles-all', RPWCM_PLUGIN_URL . '/assets/css/backend-all.css', array(), RPWCM_VERSION);
        wp_register_script('rpwcm-backend-scripts-all', RPWCM_PLUGIN_URL . '/assets/js/backend-all.js', array('jquery'), RPWCM_VERSION);

        // Font awesome (icons)
        wp_register_style('rpwcm-font-awesome', RPWCM_PLUGIN_URL . '/assets/font-awesome/css/font-awesome.min.css', array(), '4.1');

        // Pass variables to Javascript
        $localize = array(
            'empty_plan_title'          => __('name not set', 'woocommerce-membership'),
            'empty_plan_key'            => __('key not set', 'woocommerce-membership'),
            'title_membership_product'  => __('Membership product', 'woocommerce-membership'),
            'title_no_plans'            => __('No plans match', 'woocommerce-membership'),
            'title_plans_placeholder'   => __('Select Some Plans', 'woocommerce-membership'),
        );

        global $typenow;
        global $post;

        if ($typenow == 'membership_plan' && $post && isset($post->ID)) {
            $plan = WooCommerce_Membership_Plan::cache($post->ID);

            if ($plan) {
                $localize['membership_plan_exists'] = !empty($plan->key) ? 1 : 0;
            }
        }

        wp_localize_script('rpwcm-backend-scripts-all', 'rpwcm_vars', $localize);

        // Scripts
        wp_enqueue_script('rpwcm-backend-scripts-all');

        // Styles
        wp_enqueue_style('rpwcm-backend-styles-all');
        wp_enqueue_style('rpwcm-font-awesome');

        // Disable auto-save
        global $typenow;
        if ($typenow == 'membership_plan') {
            wp_dequeue_script('autosave');
        }

        // Chosen
        if (!wp_script_is('chosen', 'enqueued')) {
            wp_register_script('rpwcm-chosen', RPWCM_PLUGIN_URL . '/assets/js/chosen.jquery.js', array('jquery'), '1.0.0');
            wp_enqueue_script('rpwcm-chosen');
            wp_register_style('rpwcm-chosen', RPWCM_PLUGIN_URL . '/assets/css/chosen.min.css', array(), RPWCM_VERSION);
            wp_enqueue_style('rpwcm-chosen');
        }
    }

    /**
     * Unwrap array elements from get_post_meta moves all [0] elements one level higher
     * 
     * @access public
     * @param array $input
     * @return array
     */
    public static function unwrap_post_meta($input)
    {
        $output = array();

        foreach ($input as $key => $value) {
            if (count($value) == 1) {
                if (is_array($value)) {
                    $output[$key] = $value[0];
                }
                else {
                    $output[$key] = $value;
                }
            }
            else if (count($value) > 1) {
                $output[$key] = $value;
            }
        }

        return $output;
    }

    /**
     * Print link to post edit page
     * 
     * @access public
     * @param int $id
     * @param string $title
     * @param string $pre
     * @param string $post
     * @return void
     */
    public static function print_link_to_post($id, $title = '', $pre = '', $post = '')
    {
        echo self::get_link_to_post_html($id, $title, $pre, $post);
    }

    /**
     * Format link to post edit page
     * 
     * @access public
     * @param int $id
     * @param string $title
     * @param string $pre
     * @param string $post
     * @return void
     */
    public static function get_link_to_post_html($id, $title = '', $pre = '', $post = '')
    {
        $title_to_display = !empty($title) ? $title : '#' . $id;
        $html = $pre . ' <a href="post.php?post=' . $id . '&action=edit">' . $title_to_display . '</a> ' . $post;
        return $html;
    }

    /**
     * Print frontend link to post
     * 
     * @access public
     * @param int $id
     * @param string $title
     * @param string $pre
     * @param string $post
     * @return void
     */
    public static function print_frontend_link_to_post($id, $title = '', $pre = '', $post = '')
    {
        echo self::get_frontend_link_to_post_html($id, $title, $pre, $post);
    }

    /**
     * Format frontend link to post
     * 
     * @access public
     * @param int $id
     * @param string $title
     * @param string $pre
     * @param string $post
     * @return void
     */
    public static function get_frontend_link_to_post_html($id, $title = '', $pre = '', $post = '')
    {
        $title_to_display = !empty($title) ? $title : '#' . $id;
        $html = $pre . ' <a href="' . get_permalink($id) . '">' . $title_to_display . '</a> ' . $post;
        return $html;
    }

    /**
     * Get timezone-adjusted formatted date/time string
     * 
     * @access public
     * @param int $timestamp
     * @param string $format
     * @param string $context
     * @return string
     */
    public static function get_adjusted_datetime($timestamp = '', $format = null, $context = null)
    {
        if (empty($timestamp)) {
            return '';
        }

        // Create time zone object and set time zone
        $date_time = new DateTime('@' . $timestamp);
        $time_zone = new DateTimeZone(self::get_time_zone());
        $date_time->setTimezone($time_zone);

        // No format passed? Get it from WordPress settings and allow developers to override it
        if ($format === null) {
            $date_format = apply_filters('woocommerce_membership_date_format', get_option('date_format'), $context);
            $time_format = apply_filters('woocommerce_membership_time_format', get_option('time_format'), $context);
            $format = $date_format . (apply_filters('woocommerce_membership_display_event_time', true) ? ' ' . $time_format : '');
        }

        // Format and return
        return $date_time->format($format);
    }

    /**
     * Get timezone string
     * 
     * @access public
     * @return string
     */
    public static function get_time_zone()
    {
        if ($time_zone = get_option('timezone_tsring')) {
            return $time_zone;
        }

        if ($utc_offset = get_option('gmt_offset')) {

            $utc_offset = $utc_offset * 3600;
            $dst = date('I');

            // Try to get timezone name from offset
            if ($time_zone = timezone_name_from_abbr('', $utc_offset)) {
                return $time_zone;
            }

            // Try to guess timezone by looking at a list of all timezones
            foreach (timezone_abbreviations_list() as $abbreviation) {
                foreach ($abbreviation as $city) {
                    if ($city['dst'] == $dst && $city['offset'] == $utc_offset) {
                        return $city['timezone_id'];
                    }
                }
            }
        }

        return 'UTC';
    }

    /**
     * Include template
     * 
     * @access public
     * @param string $template
     * @param array $args
     * @return string
     */
    public static function include_template($template, $args = array())
    {
        if ($args && is_array($args)) {
            extract($args);
        }

        include self::get_template_path($template);
    }

    /**
     * Select correct template (allow overrides in theme folder)
     * 
     * @access public
     * @param string $template
     * @return string
     */
    public static function get_template_path($template)
    {
        $template = rtrim($template, '.php') . '.php';

        // Check if this template exists in current theme
        if (!($template_path = locate_template(array('woocommerce-membership/' . $template)))) {
            $template_path = RPWCM_PLUGIN_PATH . 'templates/' . $template;
        }

        return $template_path;
    }

    /**
     * Check WooCommerce version
     * 
     * @access public
     * @param string $version
     * @return bool
     */
    public static function wc_version_gte($version)
    {
        if (defined('WC_VERSION') && WC_VERSION) {
            return version_compare(WC_VERSION, $version, '>=');
        }
        else if (defined('WOOCOMMERCE_VERSION') && WOOCOMMERCE_VERSION) {
            return version_compare(WOOCOMMERCE_VERSION, $version, '>=');
        }
        else {
            return false;
        }
    }

    /**
     * Remove meta boxes from own pages
     * 
     * @access public
     * @param string $post_type
     * @param object $post
     * @return void
     */
    public function remove_meta_boxes($post_type, $post)
    {
        // Remove third party metaboxes from own pages
        if ($post_type == 'membership_plan') {
            $meta_boxes_to_leave = apply_filters('woocommerce_membership_third_party_meta_boxes_to_leave', array());

            foreach (self::get_meta_boxes() as $context => $meta_boxes_by_context) {
                foreach ($meta_boxes_by_context as $subcontext => $meta_boxes_by_subcontext) {
                    foreach ($meta_boxes_by_subcontext as $meta_box_id => $meta_box) {
                        if (!in_array($meta_box_id, $meta_boxes_to_leave)) {
                            remove_meta_box($meta_box_id, $post_type, $context);
                        }
                    }
                }
            }
        }
    }

    /**
     * Get list of meta boxes for current screent
     *  
     * @access public
     * @return array
     */
    public static function get_meta_boxes()
    {
        global $wp_meta_boxes;

        $screen = get_current_screen();
        $page = $screen->id;

        return $wp_meta_boxes[$page];
    }

}

WooCommerce_Membership::get_instance();

}