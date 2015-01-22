<?php

/**
 * View for any post with meta box Restrict Access
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="rpwcm_post_membership">
    <?php _e('Restrict access to members only.', 'woocommerce-membership'); ?>
    <div class="rpwcm_post_membership_field">
        <?php WooCommerce_Membership::render_field_multiselect(array('name' => '_rpwcm_only_caps', 'class' => 'rpwcm_only_plans', 'values' => $plans, 'selected' => $selected)); ?>
    </div>
</div>