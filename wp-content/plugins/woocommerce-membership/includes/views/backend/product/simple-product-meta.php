<?php

/**
 * View for WooCommerce Product Edit page Membership Plan selection field
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="options_group show_if_rpwcm_simple">
    <p class="form-field _rpwcm_plans_field">
        <label for="_rpwcm_plans"><?php _e('Membership Plans', 'woocommerce-membership'); ?></label>
        <?php WooCommerce_Membership::render_field_multiselect(array('name' => '_rpwcm_plans', 'class' => 'rpwcm_field_plans', 'values' => $values, 'selected' => $selected)); ?>
    </p>
</div>