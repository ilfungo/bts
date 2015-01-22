<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

global $post;
$wc_compat = SA_Smart_Offers::wc_compat();
$param_name = ( $wc_compat::is_wc_gte_21() ) ? 'woocommerce_admin_meta_boxes' : 'woocommerce_writepanel_params';
?>

<style type="text/css">
    div.woo_offer_rule {
        overflow: visible;
        opacity: 1
    }

    div.woo_offer_rule p.type select.action {
        margin-right: 7px;
        width: 165px
    }

    div.woo_offer_rule p.type button.remove_rule_option {
        float: right;
    }

    div.options_group input.skip_options_radio {
        margin-left: 7px
    }

    div.woo_offer_rule p.type select.role {
        margin-right: 7px
    }
</style>

<script type="text/javascript">

    jQuery(function() {

        jQuery("select.ajax_chosen_select_products_and_only_variations").ajaxChosen({
            method: 'GET',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            dataType: 'json',
            afterTypeDelay: 100,
            data: {
                action: 'woocommerce_json_search_products_and_only_variations',
                security: '<?php echo wp_create_nonce("search-products-and-only-variations"); ?>'
            }
        }, function(data) {

            var terms = {};

            jQuery.each(data, function(i, val) {
                terms[i] = val;
            });

            return terms;
        });

        jQuery("select.ajax_chosen_select_a_category").ajaxChosen({
            method: 'GET',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            dataType: 'json',
            afterTypeDelay: 100,
            data: {
                action: 'woocommerce_json_search_prod_category',
                security: '<?php echo wp_create_nonce("so-search-product-category"); ?>'
            }
        }, function(data) {

            var terms = {};

            jQuery.each(data, function(i, val) {
                terms[i] = val;
            });

            return terms;
        });

        var loop;
        var last_index = jQuery('.woo_offer_rules .woo_offer_rule').last().index();

        jQuery('#offer_rules').on('click', 'a.add_new_rule', function() {

            if (loop == undefined) {

                var size_of_rules = jQuery('.woo_offer_rules .woo_offer_rule').length;

                if (size_of_rules == 0) {
                    loop = 0;
                } else {
                    loop = last_index + 1;
                }

            } else {
                loop = loop + 1;
            }

            jQuery('.woo_offer_rules').append('<div class="woo_offer_rule" >\
                                                        <p class="type">\
                                                                <label class="hidden"><?php
_e('Type:', 'smart_offers');
?></label>\
                                                                <select class="role" id="role" name="offer_type[' + loop + ']">\
                                                                        <option value="cartorder"><?php
_e('Cart/Order', 'smart_offers');
?></option>\
                                                                        <option value="user"><?php
_e('User', 'smart_offers');
?></option>\<option value="offer_valid_between"><?php
_e('Offer Valid Between', 'smart_offers');
?></option>\
                                                                </select>\
                                                                <label class="hidden"><?php
_e('Action:', 'smart_offers');
?></label>\
                                                                <select class="action" id="action" name="offer_action[' + loop + ']">\
                                                                        <option value="cart_contains" name="cartorder"><?php
_e('Contains Product', 'smart_offers');
?></option>\
                                                                        <option value="cart_doesnot_contains" name="cartorder"><?php
_e('Does not contains Product', 'smart_offers');
?></option>\
                                                                        <option value="cart_total_less" name="cartorder"><?php
_e('Total is less than or equal to', 'smart_offers');
?></option>\
                                                                        <option value="cart_total_more" name="cartorder"><?php
_e('Total is more than or equal to', 'smart_offers');
?></option>\
                                                                        <option value="cart_grand_total_less" name="cartorder"><?php
_e('Grand Total is less than or equal to', 'smart_offers');
?></option>\
                                                                        <option value="cart_grand_total_more" name="cartorder"><?php
_e('Grand Total is more than or equal to', 'smart_offers');
?></option>\
                                                                        <option value="cart_prod_categories_is" name="cartorder"><?php
_e('Contains Products from Category', 'smart_offers');
?></option>\
                                                                        <option value="cart_prod_categories_not_is" name="cartorder"><?php
_e('Does not contains Product from Category', 'smart_offers');
?></option>\
                                                                        <option value="has_bought" name="user"><?php
_e('Has Purchased', 'smart_offers');
?></option>\
                                                                        <option value="not_bought" name="user"><?php
_e('Has not Purchased', 'smart_offers');
?></option>\
                                                                        <option value="registered_user" name="user"><?php
_e('Is', 'smart_offers');
?></option>\
                                <option value="user_role" name="user"><?php _e('Is a', 'smart_offers'); ?></option>\
                                <option value="user_role_not" name="user"><?php _e('Is not a', 'smart_offers'); ?></option>\
                                                                        <option value="registered_period" name="user"><?php
_e('Is Registered for', 'smart_offers');
?></option>\
                                                                        <option value="total_ordered_less" name="user"><?php
_e('Has previously Purchased less than or equal to', 'smart_offers');
?></option>\
                                                                        <option value="total_ordered_more" name="user"><?php
_e('Has previously Purchased more than or equal to', 'smart_offers');
?></option>\
                                                                </select>\
                                                                <input class="price" type="number" step="any" size="5" name="price[' + loop + ']" data-placeholder="Enter price" />\
                                                                <span id="search_product_ids_' + loop + '"><select id="search_product_ids_' + loop + '" name="search_product_ids_' + loop + '[]" class="ajax_chosen_select_products_and_variations" multiple="multiple" data-placeholder="Search for a product"></select></span>\
                                                                <span id="search_category_ids_' + loop + '"><select id="search_category_ids_' + loop + '" name="search_category_ids_' + loop + '[]" class="ajax_chosen_select_a_category" multiple="multiple" data-placeholder="Search for a category"></select></span>\
                                                                <label class="hidden"><?php
_e('registered user action:', 'smart_offers');
?></label>\
                                                                <select class="registered_user_action_' + loop + '" id="registered_user_action_' + loop + '" name="registered_user_action_' + loop + '">\
                                                                        <option value="yes"><?php
_e('Registered', 'smart_offers');
?></option>\
                                                                        <option value="no"><?php
_e('A Visitor', 'smart_offers');
?></option>\
                                                                </select>\
                                                                <label class="hidden"><?php
_e('registered period action:', 'smart_offers');
?></label>\
                                                                <select class="registered_period_action_' + loop + '" id="registered_period_action_' + loop + '" name="registered_period_action_' + loop + '">\
                                                                        <option value="one_month" name="registered_period_one_month" ><?php
_e('Less than 1 Month', 'smart_offers');
?></option>\
                                                                        <option value="three_month" name="registered_period_three_month"><?php
_e('Less than 3 Months', 'smart_offers');
?></option>\
                                                                        <option value="six_month" name="registered_period_six_month"><?php
_e('Less than 6 Months', 'smart_offers');
?></option>\
                                                                        <option value="less_than_1_year" name="registered_period_less_than_1_yr"><?php
_e('Less than 1 Year', 'smart_offers');
?></option>\
                                                                        <option value="more_than_1_year" name="registered_period_more_than_1_yr"><?php
_e('More than 1 Year', 'smart_offers');
?></option>\
                                                                </select>\
                                                                    <select class="user_role_' + loop + '" id="user_role_' + loop + '" name="user_role_' + loop + '">\
<?php
if (!isset($wp_roles)) {
    $wp_roles = new WP_Roles();
}
$all_roles = $wp_roles->roles;

foreach ($all_roles as $role_id => $role) {
    echo '<option value="' . $role_id . '" name="' . $role_id . '" >' . $role['name'] . '</option>';
}
?>\
                                                                    </select>\
                                <span class="offer_dates_fields" name="offer_valid_between_' + loop + '" id="offer_valid_between_' + loop + '" ><label class="hidden"><?php
_e('offer_valid_between:', 'smart_offers');
?></label>\
                                <input type="text" class="short date-picker" name="_offer_valid_from_' + loop + '" id="_offer_valid_from_' + loop + '" placeholder="<?php _e('From&hellip; YYYY-MM-DD', 'placeholder', 'smart_offers'); ?>" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"  />\
                                <input type="text" class="short date-picker" name="_offer_valid_till_' + loop + '" id="_offer_valid_till_' + loop + '" value="" placeholder="<?php _e('To&hellip; YYYY-MM-DD', 'placeholder', 'smart_offers'); ?>" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"  />\
                                </span>\
                                                                <button type="button" class="remove_rule_option button" id="' + loop + '" >x</button></p>\
                                <p class="category_total_' + loop + '">\
                                <select id="category_total_' + loop + '" name="category_total_' + loop + '" style="margin-left: 147px;width: 165px;margin-right: 7px;">\
                                    <option value="category_total_more"><?php _e('Subtotal of products in that category is more than or equal to', 'smart_offers'); ?></option>\
                                    <option value="category_total_less"><?php _e('Subtotal of products in that category is less than or equal to', 'smart_offers'); ?></option>\
                                </select>\
                                <input type="number" class="category_amount" id="category_amount_' + loop + '" step="any" size="5" name="category_amount_' + loop + '" placeholder="Enter price" style="width: 15%;">\
                                </p>\
                                                </div>');



            jQuery("select.ajax_chosen_select_products_and_variations").ajaxChosen({
                method: 'GET',
                url: <?php echo $param_name . ".ajax_url"; ?>,
                dataType: 'json',
                afterTypeDelay: 100,
                data: {
                    action: 'woocommerce_json_search_products_and_variations',
                    security: <?php echo $param_name . ".search_products_nonce"; ?>
                }
            }, function(data) {

                var terms = {};

                jQuery.each(data, function(i, val) {
                    terms[i] = val;
                });

                return terms;
            });

            jQuery("select.ajax_chosen_select_products_and_only_variations").ajaxChosen({
                method: 'GET',
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                dataType: 'json',
                afterTypeDelay: 100,
                data: {
                    action: 'woocommerce_json_search_products_and_only_variations',
                    security: '<?php echo wp_create_nonce("search-products-and-only-variations"); ?>'
                }
            }, function(data) {

                var terms = {};

                jQuery.each(data, function(i, val) {
                    terms[i] = val;
                });

                return terms;
            });

            jQuery("select.ajax_chosen_select_a_category").ajaxChosen({
                method: 'GET',
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                dataType: 'json',
                afterTypeDelay: 100,
                data: {
                    action: 'woocommerce_json_search_prod_category',
                    security: '<?php echo wp_create_nonce("so-search-product-category"); ?>'
                }
            }, function(data) {

                var terms = {};

                jQuery.each(data, function(i, val) {
                    terms[i] = val;
                });

                return terms;
            });

            jQuery(".date-picker").datepicker({
                dateFormat: "yy-mm-dd",
                numberOfMonths: 1,
                showButtonPanel: true,
                showOn: "button",
                buttonImage: <?php echo $param_name . ".calendar_image"; ?>,
                buttonImageOnly: true
            });

            jQuery('select.role[name="offer_type[' + loop + ']"]').trigger('change');

            return false; // to stay on that area of page
        });


        jQuery('#offer_rules').on('change', 'select.role', function() {

            // Hiding all element at first
            jQuery(this).closest('.woo_offer_rule').find('select[name*="offer_action"]').css('display', 'none');
            jQuery(this).closest('.woo_offer_rule').find('input[name*="price"]').css('display', 'none');
            jQuery(this).closest('.woo_offer_rule').find('select[name*="search_product_ids_"]').css('display', 'none');
            jQuery(this).closest('.woo_offer_rule').find('span[id*="search_product_ids_"]').css('display', 'none');
//                                                                                jQuery(this).closest('.woo_offer_rule').find('span[id*="search_product_ids_to_remove_"]').css( 'display' , 'none' );
            jQuery(this).closest('.woo_offer_rule').find('span[id*="search_category_ids_"]').css('display', 'none');

            jQuery(this).closest('.woo_offer_rule').find('select[name*="registered_period_action_"]').css('display', 'none');
            jQuery(this).closest('.woo_offer_rule').find('select[name*="registered_user_action_"]').css('display', 'none');
            jQuery(this).closest('.woo_offer_rule').find('select[name*="user_role_"]').css('display', 'none');
            jQuery(this).closest('.woo_offer_rule').find('span[name*="offer_valid_between_"]').css('display', 'none');

            jQuery(this).closest('.woo_offer_rule').find('p[class*="category_total_"]').css('display', 'none');

            var id = jQuery(this).val();
            var name = jQuery(this).attr('name');
            var loop = name.split("[")[1].split("]")[0];

            if (id == "offer_valid_between") {

                jQuery(this).closest('.woo_offer_rule').find('span[name="offer_valid_between_' + loop + '"]').css('display', 'inline');
                jQuery(this).closest('.woo_offer_rule').find('input[name="_offer_valid_from_' + loop + '"]').css('display', 'inline');
                jQuery(this).closest('.woo_offer_rule').find('input[name="_offer_valid_till_' + loop + '"]').css('display', 'inline');

            } else {

                if (jQuery(this).data('options') == undefined) {
                    /*Taking an array of all options-2 and kind of embedding it on the select1*/
                    jQuery(this).data('options', jQuery('#action[name="offer_action[' + loop + ']"] option').clone());

                }

                jQuery(this).closest('.woo_offer_rule').find('select[name="offer_action[' + loop + ']"]').css('display', 'inline');
                var options = jQuery(this).data('options').filter('[name=' + id + ']');
                jQuery('select[name="offer_action[' + loop + ']"]').html(options);

                jQuery('select.action[name="offer_action[' + loop + ']"]').trigger('change');

            }

        });

        jQuery('#offer_rules').on('change', 'select.action', function() {

            var name = jQuery(this).attr('name');
            var loop = name.split("[")[1].split("]")[0];
            var id = jQuery('select[name="offer_action[' + loop + ']"] option:selected').text();

            // Return if select action is hidden
            if (jQuery(this).closest('.woo_offer_rule select[name="offer_action[' + loop + ']"]').is(":visible") == false) {
                return false;
            }
            
            if (id == 'Contains Products from Category') {
                jQuery('p.category_total_' + loop + ' ').css('display', 'block');
            } else {
                jQuery('p.category_total_' + loop + '').css('display', 'none');
            }

            if (id == 'Total is less than or equal to' || id == 'Total is more than or equal to' || id == 'Grand Total is less than or equal to' || id == 'Grand Total is more than or equal to' || id == 'Has previously Purchased less than or equal to' || id == 'Has previously Purchased more than or equal to') {

                jQuery(this).closest('.woo_offer_rule').find('input.price').css('display', 'inline');
                jQuery(this).closest('.woo_offer_rule').find('span#search_product_ids_' + loop + '').css('display', 'none');
                jQuery(this).closest('.woo_offer_rule').find('select[name="registered_user_action_' + loop + '"]').css('display', 'none');
                jQuery(this).closest('.woo_offer_rule').find('select[name="registered_period_action_' + loop + '"]').css('display', 'none');
                jQuery(this).closest('.woo_offer_rule').find('select[name="user_role_' + loop + '"]').css('display', 'none');
//                                                                                    jQuery(this).closest('.woo_offer_rule').find('span#search_product_ids_to_remove_'+loop+'').css( 'display' , 'none' );
                jQuery(this).closest('.woo_offer_rule').find('span#search_category_ids_' + loop + '').css('display', 'none');

            } else if (id == 'Is') {
                jQuery(this).closest('.woo_offer_rule').find('input.price').css('display', 'none');
                jQuery(this).closest('.woo_offer_rule').find('span#search_product_ids_' + loop + '').css('display', 'none');
                jQuery(this).closest('.woo_offer_rule').find('select[name="registered_user_action_' + loop + '"]').css('display', 'inline');
                jQuery(this).closest('.woo_offer_rule').find('select[name="registered_period_action_' + loop + '"]').css('display', 'none');
                jQuery(this).closest('.woo_offer_rule').find('select[name="user_role_' + loop + '"]').css('display', 'none');
//                                                                                    jQuery(this).closest('.woo_offer_rule').find('span#search_product_ids_to_remove_'+loop+'').css( 'display' , 'none' );
                jQuery(this).closest('.woo_offer_rule').find('span#search_category_ids_' + loop + '').css('display', 'none');

            } else if (id == 'Is Registered for') {
                jQuery(this).closest('.woo_offer_rule').find('input.price').css('display', 'none');
                jQuery(this).closest('.woo_offer_rule').find('span#search_product_ids_' + loop + '').css('display', 'none');
                jQuery(this).closest('.woo_offer_rule').find('select[name="registered_user_action_' + loop + '"]').css('display', 'none');
                jQuery(this).closest('.woo_offer_rule').find('select[name="registered_period_action_' + loop + '"]').css('display', 'inline');
                jQuery(this).closest('.woo_offer_rule').find('select[name="user_role_' + loop + '"]').css('display', 'none');
//                                                                                    jQuery(this).closest('.woo_offer_rule').find('span#search_product_ids_to_remove_'+loop+'').css( 'display' , 'none' );
                jQuery(this).closest('.woo_offer_rule').find('span#search_category_ids_' + loop + '').css('display', 'none');

            } else if (id == 'Is a' || id == 'Is not a') {
                jQuery(this).closest('.woo_offer_rule').find('input.price').css('display', 'none');
                jQuery(this).closest('.woo_offer_rule').find('span#search_product_ids_' + loop + '').css('display', 'none');
                jQuery(this).closest('.woo_offer_rule').find('select[name="registered_user_action_' + loop + '"]').css('display', 'none');
                jQuery(this).closest('.woo_offer_rule').find('select[name="registered_period_action_' + loop + '"]').css('display', 'none');
                jQuery(this).closest('.woo_offer_rule').find('select[name="user_role_' + loop + '"]').css('display', 'inline');
//                                                                                    jQuery(this).closest('.woo_offer_rule').find('span#search_product_ids_to_remove_'+loop+'').css( 'display' , 'none' );
                jQuery(this).closest('.woo_offer_rule').find('span#search_category_ids_' + loop + '').css('display', 'none');

            } else if (id == 'Contains Products from Category' || id == 'Does not contains Product from Category') {
                jQuery(this).closest('.woo_offer_rule').find('input.price').css('display', 'none');
                jQuery(this).closest('.woo_offer_rule').find('span#search_product_ids_' + loop + '').css('display', 'none');
                jQuery(this).closest('.woo_offer_rule').find('select[name="registered_user_action_' + loop + '"]').css('display', 'none');
                jQuery(this).closest('.woo_offer_rule').find('select[name="registered_period_action_' + loop + '"]').css('display', 'none');
                jQuery(this).closest('.woo_offer_rule').find('select[name="user_role_' + loop + '"]').css('display', 'none');
//                                                                                    jQuery(this).closest('.woo_offer_rule').find('span#search_product_ids_to_remove_'+loop+'').css( 'display' , 'none' );
                jQuery(this).closest('.woo_offer_rule').find('span#search_category_ids_' + loop + '').css('display', 'inline');

                limit_category(loop);
            } else if (id == 'Contains Product' || id == 'Does not contains Product' || id == 'Has Purchased' || id == 'Has not Purchased') {
                jQuery(this).closest('.woo_offer_rule').find('input.price').css('display', 'none');
                jQuery(this).closest('.woo_offer_rule').find('span#search_product_ids_' + loop + '').css('display', 'inline');
                jQuery(this).closest('.woo_offer_rule').find('select[name="registered_user_action_' + loop + '"]').css('display', 'none');
                jQuery(this).closest('.woo_offer_rule').find('select[name="registered_period_action_' + loop + '"]').css('display', 'none');
                jQuery(this).closest('.woo_offer_rule').find('select[name="user_role_' + loop + '"]').css('display', 'none');
//                                                                                    jQuery(this).closest('.woo_offer_rule').find('span#search_product_ids_to_remove_'+loop+'').css( 'display' , 'none' );
                jQuery(this).closest('.woo_offer_rule').find('span#search_category_ids_' + loop + '').css('display', 'none');

            }

            return false;

        });

        function limit_category(loop) {
            var id = jQuery('select[name="offer_action[' + loop + ']"] option:selected').text();

            setTimeout(function() {
<?php if ($wc_compat::is_wc_gte_21()) { ?>
                    if (jQuery('div#search_category_ids_' + loop + '_chosen ul.chosen-choices li').length >= 2 && ( id == 'Contains Products from Category' || id == 'Does not contains Product from Category' ) ) {

                        jQuery('div#search_category_ids_' + loop + '_chosen ul.chosen-choices li.search-field').css('visibility', 'hidden');
                        jQuery('div#search_category_ids_' + loop + '_chosen div.chosen-drop').css('display', 'none');
                        jQuery('p.category_total_' + loop + ' ').css('display', 'block');

                    } else {

                        jQuery('div#search_category_ids_' + loop + '_chosen ul.chosen-choices li.search-field').css('visibility', 'visible');
                        jQuery('div#search_category_ids_' + loop + '_chosen div.chosen-drop').css('display', 'block');
                        jQuery('p.category_total_' + loop + '').css('display', 'none');

                    }
<?php } else { ?>
                    if (jQuery('div#search_category_ids_' + loop + '_chzn ul.chzn-choices li').length >= 2 && ( id == 'Contains Products from Category' || id == 'Does not contains Product from Category' ) ) {

                        jQuery('div#search_category_ids_' + loop + '_chzn ul.chzn-choices li.search-field').css('visibility', 'hidden');
                        jQuery('div#search_category_ids_' + loop + '_chzn div.chzn-drop').css('display', 'none');
                        jQuery('p.category_total_' + loop + ' ').css('display', 'block');

                    } else {

                        jQuery('div#search_category_ids_' + loop + '_chzn ul.chzn-choices li.search-field').css('visibility', 'visible');
                        jQuery('div#search_category_ids_' + loop + '_chzn div.chzn-drop').css('display', 'block');
                        jQuery('p.category_total_' + loop + '').css('display', 'none');

                    }

<?php } ?>
            }, 1);

        }

        jQuery('#offer_rules').on('change', 'select[id^="search_category_ids_"]', function() {

            var id = jQuery(this).attr('id');
            var loop = id.split("search_category_ids_")[1];
            limit_category(loop);
        });


        // to remove rule
        jQuery('button.remove_rule_option').live('click', function() {

            var rule_id = jQuery(this).attr('id');

            if (jQuery("input[name='price[" + rule_id + "]']").val().trim().length == 0 && jQuery('div#search_product_ids_' + rule_id + '_chosen ul.chosen-choices li.search-choice').length == 0 && !(jQuery("select[name='offer_type[" + rule_id + "]']").val() == "user" && (jQuery("select[name='offer_action[" + rule_id + "]']").val() == "registered_user" || jQuery("select[name='offer_action[" + rule_id + "]']").val() == "registered_period"))) {

                jQuery(this).closest('div').remove();
                return false;

            } else {
                answer = confirm('<?php
_e('Are you sure you want delete this rule?', 'smart_offers');
?>');
            }


            if (answer) {
                jQuery(this).closest('div').remove();
            }

            return false;

        });


    });


</script>


<?php
wp_nonce_field('woocommerce_save_data', 'woocommerce_meta_nonce');
?>

<div id="offers_options" class="panel woocommerce_options_panel">


    <div id="offer_rules" class="panel">


        <div class="woo_offer_rules">
            <?php
            $offer_rules = get_post_meta($post->ID, '_offer_rules', true);

            $loop = 0;

            if (is_array($offer_rules) && sizeof($offer_rules) > 0) {

                foreach ($offer_rules as $key => $value) {
                    ?>
                    <div class="woo_offer_rule">
                        <p class="type"><label class="hidden"><?php
            _e('Type:', 'smart_offers');
                    ?></label>
                            <select class="role" id="role" name="offer_type[<?php
                        echo $loop;
                    ?>]">

                                <option <?php
                    selected('cartorder', $value ['offer_type']);
                    ?>
                                    value="cartorder"><?php
                        _e('Cart/Order', 'smart_offers');
                    ?></option>
                                <option <?php
                                selected('user', $value ['offer_type']);
                    ?> value="user"><?php
                                    _e('User', 'smart_offers');
                                    ?></option>
                                <option <?php
                                selected('offer_valid_between', $value ['offer_type']);
                                    ?> value="offer_valid_between"><?php
                                    _e('Offer Valid Between
                                                    ', 'smart_offers');
                                    ?></option>
                            </select> <label class="hidden"><?php
                                _e('Action:', 'smart_offers');
                                    ?></label>
                            <select class="action" id="action"
                                    name="offer_action[<?php
                        echo $loop;
                                    ?>]">

                                <option <?php
                            selected('cart_contains', $value ['offer_action']);
                                    ?>
                                    value="cart_contains" name="cartorder"><?php
                        _e('Contains Product', 'smart_offers');
                                    ?></option>
                                <option <?php
                                selected('cart_doesnot_contains', $value ['offer_action']);
                                    ?>
                                    value="cart_doesnot_contains" name="cartorder"><?php
                        _e('Does not contains Product', 'smart_offers');
                                    ?></option>
                                <option <?php
                                selected('cart_total_less', $value ['offer_action']);
                                    ?>
                                    value="cart_total_less" name="cartorder"><?php
                        _e('Total is less than or equal to', 'smart_offers');
                                    ?></option>
                                <option <?php
                                selected('cart_total_more', $value ['offer_action']);
                                    ?>
                                    value="cart_total_more" name="cartorder"><?php
                        _e('Total is more than or equal to', 'smart_offers');
                                    ?></option>
                                <option
                                <?php
                                selected('cart_grand_total_less', $value ['offer_action']);
                                ?>
                                    value="cart_grand_total_less" name="cartorder"><?php
                        _e('Grand Total is less than or equal to', 'smart_offers');
                                ?></option>
                                <option
                                <?php
                                selected('cart_grand_total_more', $value ['offer_action']);
                                ?>
                                    value="cart_grand_total_more" name="cartorder"><?php
                        _e('Grand Total is more than or equal to', 'smart_offers');
                                ?></option>
                                <option <?php
                                selected('cart_prod_categories_is', $value ['offer_action']);
                                ?>
                                    value="cart_prod_categories_is" name="cartorder"><?php
                        _e('Contains Products from Category', 'smart_offers');
                                ?></option>
                                <option <?php
                                selected('cart_prod_categories_not_is', $value ['offer_action']);
                                ?>
                                    value="cart_prod_categories_not_is" name="cartorder"><?php
                        _e('Does not contains Product from Category', 'smart_offers');
                                ?></option>
                                <option <?php
                                selected('has_bought', $value ['offer_action']);
                                ?>
                                    value="has_bought" name="user"><?php
                        _e('Has Purchased', 'smart_offers');
                                ?></option>
                                <option <?php
                                selected('not_bought', $value ['offer_action']);
                                ?>
                                    value="not_bought" name="user"><?php
                        _e('Has not Purchased', 'smart_offers');
                                ?></option>
                                <option <?php
                                selected('registered_user', $value ['offer_action']);
                                ?>
                                    value="registered_user" name="user"><?php
                        _e('Is', 'smart_offers');
                                ?></option>
                                <option <?php selected('user_role', $value ['offer_action']); ?> value="user_role" name="user"><?php _e('Is a', 'smart_offers'); ?></option>
                                <option <?php selected('user_role_not', $value ['offer_action']); ?> value="user_role_not" name="user"><?php _e('Is not a', 'smart_offers'); ?></option>
                                <option <?php
                            selected('registered_period', $value ['offer_action']);
                                ?>
                                    value="registered_period" name="user"><?php
                        _e('Is Registered for', 'smart_offers');
                                ?></option>
                                <option
                                <?php
                                selected('total_ordered_less', $value ['offer_action']);
                                ?>
                                    value="total_ordered_less" name="user"><?php
                        _e('Has previously Purchased less than or equal to', 'smart_offers');
                                ?></option>
                                <option
                                <?php
                                selected('total_ordered_more', $value ['offer_action']);
                                ?>
                                    value="total_ordered_more" name="user"><?php
                        _e('Has previously Purchased more than or equal to', 'smart_offers');
                                ?></option>
                            </select> <input
                                value="<?php
                                if ($value ['offer_action'] == 'cart_total_less' || $value ['offer_action'] == 'cart_total_more' || $value ['offer_action'] == 'cart_grand_total_less' || $value ['offer_action'] == 'cart_grand_total_more' || $value ['offer_action'] == 'total_ordered_less' || $value ['offer_action'] == 'total_ordered_more') {
                                    echo $value ['offer_rule_value'];
                                } else {
                                    echo "";
                                }
                                ?>"
                                class="price" type="number" step="any" size="5" name="price[<?php
                        echo $loop;
                                ?>]"
                                data-placeholder="Enter price" /> <span
                                id="<?php
                        echo 'search_product_ids_' . $loop;
                                ?>"><select
                                    id="<?php
                        echo 'search_product_ids_' . $loop;
                                ?>"
                                    name="<?php
                            echo 'search_product_ids_' . $loop . '[]';
                                ?>"
                                    class="ajax_chosen_select_products_and_variations" multiple="multiple"
                                    data-placeholder="Search for a product">
                                        <?php
                                        if ($value ['offer_action'] == 'cart_contains' || $value ['offer_action'] == 'has_bought' || $value ['offer_action'] == 'not_bought' || $value ['offer_action'] == 'cart_doesnot_contains') {
                                            $offer_rule_product_ids = explode(',', $value ['offer_rule_value']);

                                            foreach ($offer_rule_product_ids as $offer_rule_product_id) {

                                                if ($offer_rule_product_id) {

                                                    $title = SO_Product_Details::get_product_title($offer_rule_product_id);
                                                    $sku = get_post_meta($offer_rule_product_id, '_sku', true);

                                                    if (!$title)
                                                        continue;

                                                    if (isset($sku) && $sku)
                                                        $sku = ' (SKU: ' . $sku . ')';

                                                    echo '<option value="' . $offer_rule_product_id . '" selected="selected">' . $title . $sku . '</option>';
                                                }
                                            }
                                        } else {
                                            echo '<option value="" ></option>';
                                        }
                                        ?>
                                </select></span> 
                            <span id="<?php echo 'search_category_ids_' . $loop; ?>">
                                <select id="<?php echo 'search_category_ids_' . $loop; ?>" name="<?php echo 'search_category_ids_' . $loop . '[]'; ?>"
                                        class="ajax_chosen_select_a_category" multiple="multiple" data-placeholder="Search for a category" >
        <?php
        if ($value ['offer_action'] == 'cart_prod_categories_is' || $value ['offer_action'] == 'cart_prod_categories_not_is') {
            $offer_rule_product_category_ids = explode(',', $value ['offer_rule_value']);

            foreach ($offer_rule_product_category_ids as $offer_rule_product_category_id) {

                if ($offer_rule_product_category_id) {

                    $category = get_term($offer_rule_product_category_id, 'product_cat');

                    if (!$category)
                        continue;

                    echo '<option value="' . $offer_rule_product_category_id . '" selected="selected">' . $category->name . '</option>';
                }
            }
        } else {
            echo '<option value="" ></option>';
        }
        ?>
                                </select>

                            </span>
                            <label
                                class="hidden"><?php
                                    _e('registered user action:', 'smart_offers');
                                    ?></label>
                            <select class="<?php
                                    echo 'registered_user_action_' . $loop;
                                    ?>"
                                    id="<?php
                                echo 'registered_user_action_' . $loop;
                                ?>"
                                    name="<?php
                            echo 'registered_user_action_' . $loop;
                            ?>">
                                <option value="yes"
                                    <?php
                                    selected('yes', $value ['offer_rule_value']);
                                    ?>><?php
                                    _e('Registered', 'smart_offers');
                                    ?></option>
                                <option value="no" <?php
                                selected('no', $value ['offer_rule_value']);
                                ?>><?php
                                            _e('A Visitor', 'smart_offers');
                                            ?></option>
                            </select> <label class="hidden"><?php
                                _e('registered period action:', 'smart_offers');
                                ?></label>
                            <select class="<?php
                                    echo 'registered_period_action_' . $loop;
                                    ?>"
                                    id="<?php
                                echo 'registered_period_action_' . $loop;
                                ?>"
                                    name="<?php
                            echo 'registered_period_action_' . $loop;
                            ?>">
                                <option
                                    <?php
                                    if ($value ['offer_rule_value'] == 'one_month')
                                        echo 'selected="selected"';
                                    ?>
                                    value="one_month" name="registered_period_one_month"><?php
                                _e('Less than 1 Month', 'smart_offers');
                                ?></option>
                                <option
                                <?php
                                if ($value ['offer_rule_value'] == 'three_month')
                                    echo 'selected="selected"';
                                ?>
                                    value="three_month" name="registered_period_three_month"><?php
                                _e('Less than 3 Months', 'smart_offers');
                                ?></option>
                                <option
                                <?php
                                if ($value ['offer_rule_value'] == 'six_month')
                                    echo 'selected="selected"';
                                ?>
                                    value="six_month" name="registered_period_six_month"><?php
                                _e('Less than 6 Months', 'smart_offers');
                                ?></option>
                                <option
                                <?php
                                if ($value ['offer_rule_value'] == 'less_than_1_year')
                                    echo 'selected="selected"';
                                ?>
                                    value="less_than_1_year" name="registered_period_less_than_1_yr"><?php
                                _e('Less than 1 Year', 'smart_offers');
                                ?></option>
                                <option
                                <?php
                                if ($value ['offer_rule_value'] == 'more_than_1_year')
                                    echo 'selected="selected"';
                                ?>
                                    value="more_than_1_year" name="registered_period_more_than_1_yr"><?php
                                _e('More than 1 year', 'smart_offers');
                                ?></option>
                            </select>
                            <select class="<?php echo 'user_role_' . $loop; ?>" id="<?php echo 'user_role_' . $loop; ?>" name="<?php echo 'user_role_' . $loop; ?>">
                                        <?php
                                        if (!isset($wp_roles)) {
                                            $wp_roles = new WP_Roles();
                                        }
                                        $all_roles = $wp_roles->roles;

                                        foreach ($all_roles as $role_id => $role) {
                                            echo '<option value="' . $role_id . '" name="' . $role_id . '" ' . selected(esc_attr($value ['offer_rule_value']), esc_attr($role_id), false) . '>' . $role['name'] . '</option>';
                                        }
                                        ?>

                            </select>
                            <span class="offer_dates_fields" name="<?php echo 'offer_valid_between_' . $loop; ?>" id="<?php echo 'offer_valid_between_' . $loop; ?>" ><label class="hidden"><?php
                                _e('offer_valid_between:', 'smart_offers');
                                ?></label>
                                <input type="text" class="short date-picker" name="<?php echo '_offer_valid_from_' . $loop; ?>" id="<?php echo '_offer_valid_from_' . $loop; ?>" 
                                       value="<?php
                        if (is_array($value ['offer_rule_value']) && isset($value ['offer_rule_value']['offer_valid_from'])) {
                            echo!empty($value ['offer_rule_value']['offer_valid_from']) ? date_i18n('Y-m-d', $value ['offer_rule_value']['offer_valid_from']) : '';
                        }
                        ?>"
                                       placeholder="<?php _e('From&hellip; YYYY-MM-DD', 'placeholder', 'smart_offers'); ?>" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"  />
                                <input type="text" class="short date-picker" name="<?php echo '_offer_valid_till_' . $loop; ?>" id="<?php echo '_offer_valid_till_' . $loop; ?>" 
                                       value="<?php
                                       if (is_array($value ['offer_rule_value']) && isset($value ['offer_rule_value']['offer_valid_till'])) {
                                           echo (!empty($value ['offer_rule_value']['offer_valid_till']) && $value ['offer_rule_value']['offer_valid_till'] != '') ? date_i18n('Y-m-d', $value ['offer_rule_value']['offer_valid_till']) : '';
                                       }
                                       ?>" 
                                       placeholder="<?php _e('To&hellip; YYYY-MM-DD', 'placeholder', 'smart_offers'); ?>" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"  />
                            </span>
                            <button type="button" class="remove_rule_option button"
                                    id="<?php
                               echo $loop;
                               ?>">x</button>
                        </p>
                                    <?php if ($value ['offer_action'] == 'cart_prod_categories_is') { ?>
                            <p class="<?php echo 'category_total_' . $loop; ?>">
                                <select id="<?php echo 'category_total_' . $loop; ?>" name="<?php echo 'category_total_' . $loop; ?>" style="margin-left: 147px;width: 165px;margin-right: 7px;">
                                    <option value="category_total_more" <?php selected('category_total_more', $value ['category_total']); ?>><?php _e('Subtotal of products in that category is more than or equal to', 'smart_offers'); ?></option>
                                    <option value="category_total_less" <?php selected('category_total_less', $value ['category_total']); ?>><?php _e('Subtotal of products in that category is less than or equal to', 'smart_offers'); ?></option>
                                </select>
                                <input type="number" class="<?php echo 'category_amount'; ?>" id="<?php echo 'category_amount_' . $loop; ?>" value="<?php echo $value ['category_amount']; ?>" step="any" size="5" name="<?php echo 'category_amount_' . $loop; ?>" data-placeholder="Enter price(Optional)" style="width: 15%;">
                            </p>
        <?php } ?>    
                    </div>

                        <?php
                        $loop ++;
                    }
                }
                ?>
            <script type="text/javascript">

                jQuery(function() {
                    jQuery('select.role').trigger('change');
                });
            </script></div>

        <p>
            <a href="#" class="add_new_rule button"><?php
            _e('+ Add New Rule', 'smart_offers');
                ?></a>
        </p>

    </div>

</div>
<?php ?>
