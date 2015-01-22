<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('SO_Admin_Settings')) {

    class SO_Admin_Settings {

        function __construct() {
            add_action('woocommerce_settings_tabs_smart_offers', array(&$this, 'sa_smart_offers_settings_tab_content'), 1);
            add_action('woocommerce_update_options_smart_offers', array(&$this, 'update_smart_offers_options'));
            add_action('init', array(&$this, 'so_settings'));

            add_filter('woocommerce_settings_tabs_array', array(&$this, 'sa_smart_offers_settings_tab'), 25);
        }

        /**
	 * Show SO settings
	 */
        function sa_smart_offers_settings_tab_content() {
            global $woocommerce_settings;

            woocommerce_admin_fields($woocommerce_settings ['smart_offers']);
            $hidden_option = get_option('woo_sm_offer_show_hidden_items');
            $max_offer_to_show = get_option('so_max_inline_offer');
            ?>

            <table class='form-table'>
                <tbody>
                    <tr valign="top">
                        <th class="titledesc" scope="row"><?php _e('Show Offers for hidden product', 'smart_offers'); ?></th>
                        <td class="forminp"><select id="woo_sm_offer_show_hidden_items" name="woo_sm_offer_show_hidden_items">
                                <option value="yes" <?php selected('yes', $hidden_option); ?> ><?php _e('Yes', 'smart_offers'); ?></option>
                                <option value="no" <?php selected('no', $hidden_option); ?> ><?php _e('No', 'smart_offers');
                        ?></option>
                            </select></td>
                    </tr>
                    <tr valign="top">
                        <th class="titledesc" scope="row"><?php _e('Multiple Offers? Select one with...', 'smart_offers'); ?></th>
                        <td class="forminp">
                            <fieldset><input type="radio" name="woo_sm_offers_if_multiple"
                                             value="high_price"
                                             <?php
                                             if (get_option('woo_sm_offers_if_multiple') == "high_price")
                                                 echo 'checked="checked"';
                                             ?> />
                                Higher Price</fieldset>
                            <fieldset><input type="radio" name="woo_sm_offers_if_multiple"
                                             value="low_price"
                                             <?php
                                             if (get_option('woo_sm_offers_if_multiple') == "low_price")
                                                 echo 'checked="checked"';
                                             ?> />
                                Lower Price</fieldset>
                            <fieldset><input type="radio" name="woo_sm_offers_if_multiple"
                                             value="random"
                                             <?php
                                             if (get_option('woo_sm_offers_if_multiple') == "random")
                                                 echo 'checked="checked"';
                                             ?> />
                                Pick one randomly</fieldset>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th class="titledesc" scope="row"><?php _e('Maximum inline offers on a page', 'smart_offers'); ?></th>
                        <td class="forminp">
                            <input type="number" step="any" min="1" class="short" name="so_max_inline_offer" id="so_max_inline_offer" value="<?php echo $max_offer_to_show; ?>"> 
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php
            $style_for_accept = get_option('so_css_for_accept');
            $style_for_skip = get_option('so_css_for_skip');

            $woocommerce_settings ['smart_offers'] = apply_filters('sa_smart_offers_settings', array(array('name' => __('Styles', 'smart_offers'), 'type' => 'title', '', 'id' => 'smart_offers_style_settings'), array('name' => __('For Accept Link', 'smart_offers'), 'id' => 'so_css_for_accept', 'css' => 'width:100%; height: 150px;', 'std' => $style_for_accept, 'type' => 'textarea'), array('name' => __('For Skip Link', 'smart_offers'), 'id' => 'so_css_for_skip', 'css' => 'width:100%; height: 150px;', 'std' => $style_for_skip, 'type' => 'textarea'), array('type' => 'sectionend', 'id' => 'smart_offers_style_settings')));

            woocommerce_admin_fields($woocommerce_settings ['smart_offers']);
        }

        /**
	 * Add preference setting in SO setting
	 */
        function so_settings() {
            global $woocommerce_settings;

            $woocommerce_settings ['smart_offers'] = apply_filters('sa_smart_offers_settings', array(array('name' => __('Preferences', SO_TEXT_DOMAIN), 'type' => 'title', '', 'id' => 'smart_offers_settings'), array('type' => 'sectionend', 'id' => 'smart_offers_settings')));
        }

        /**
	 * Save SO setting options
	 */
        function update_smart_offers_options() {

            if (isset($_POST ['woo_sm_offer_show_hidden_items']) && $_POST ['woo_sm_offer_show_hidden_items'] == 'yes') {
                update_option('woo_sm_offer_show_hidden_items', 'yes');
            } else {
                update_option('woo_sm_offer_show_hidden_items', 'no');
            }

            if (isset($_POST ['woo_sm_offers_if_multiple'])) {
                update_option('woo_sm_offers_if_multiple', $_POST ['woo_sm_offers_if_multiple']);
            }

            if (isset($_POST ['so_max_inline_offer'])) {
                update_option('so_max_inline_offer', $_POST ['so_max_inline_offer']);
            }

            if (isset($_POST ['so_css_for_accept'])) {
                update_option('so_css_for_accept', $_POST ['so_css_for_accept']);
            }

            if (isset($_POST ['so_css_for_skip'])) {
                update_option('so_css_for_skip', $_POST ['so_css_for_skip']);
            }
        }

        /**
	 * Add Smart Offers tab in WC seetings
	 */
        function sa_smart_offers_settings_tab($tabs) {
            $tabs ['smart_offers'] = __('Smart Offers', SO_TEXT_DOMAIN);
            return $tabs;
        }
        
        function can_show_hidden_items() {
            return get_option('woo_sm_offer_show_hidden_items');
        }
        
        function get_price_settings() {
            return get_option('woo_sm_offers_if_multiple');
        }
        

    }

    new SO_Admin_Settings();
}