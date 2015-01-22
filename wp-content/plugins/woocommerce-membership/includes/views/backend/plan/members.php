<?php

/**
 * View for Membership Plan Edit page Members block
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<?php if (!empty($members)): ?>

    <table class="rpwcm_membership_plan_item_list">
        <thead>
            <tr>
                <th class="rpwcm_third_width rpwcm_membership_plan_item_list_name"><?php _e('Member Name', 'woocommerce-membership'); ?></th>
                <th class="rpwcm_third_width rpwcm_membership_plan_item_list_email"><?php _e('Email Address', 'woocommerce-membership'); ?></th>
                <th class="rpwcm_third_width rpwcm_membership_plan_item_list_since"><?php _e('Member Since', 'woocommerce-membership'); ?></th>
            </tr>
        </thead>

        <tbody>
            <?php foreach($members as $member): ?>
                <tr>
                    <td class="rpwcm_third_width rpwcm_membership_plan_item_list_name">
                        <?php echo WooCommerce_Membership_User::get_user_full_name_link($member->ID); ?>
                    </td>
                    <td class="rpwcm_third_width rpwcm_membership_plan_item_list_email">
                        <a href="mailto:<?php echo $member->user_email; ?>"><?php echo $member->user_email; ?></a>
                    </td>
                    <td class="rpwcm_third_width rpwcm_membership_plan_item_list_since">
                        <?php echo WooCommerce_Membership::get_adjusted_datetime(get_user_meta($member->ID, '_rpwcm_' . $plan->key . '_since', true)); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php else: ?>

    <p>
        <?php _e('No members found.', 'woocommerce-membership'); ?>
    </p>

<?php endif; ?>