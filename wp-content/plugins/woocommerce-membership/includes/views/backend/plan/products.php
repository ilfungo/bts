<?php

/**
 * View for Membership Plan Edit page Related Products block
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<?php if (!empty($products)): ?>

    <table class="rpwcm_membership_plan_item_list">
        <thead>
            <tr>
                <th class="rpwcm_third_width rpwcm_membership_plan_item_list_product"><?php _e('Product Name', 'woocommerce-membership'); ?></th>
                <th class="rpwcm_third_width rpwcm_membership_plan_item_list_type"><?php _e('Type', 'woocommerce-membership'); ?></th>
                <th class="rpwcm_third_width rpwcm_membership_plan_item_list_since"><?php _e('Current Price', 'woocommerce-membership'); ?></th>
            </tr>
        </thead>

        <tbody>
            <?php foreach($products as $product_id => $product): ?>
                <tr>
                    <td class="rpwcm_third_width rpwcm_membership_plan_item_list_product">
                        <?php WooCommerce_Membership::print_link_to_post($product['main_id'], $product['title']); ?>
                    </td>
                    <td class="rpwcm_third_width rpwcm_membership_plan_item_list_type">
                        <?php echo $product['type']; ?>
                    </td>
                    <td class="rpwcm_third_width rpwcm_membership_plan_item_list_since">
                        <?php $product = new WC_Product($product_id); ?>
                        <?php if ($product): ?>
                            <?php echo wc_price($product->get_price()); ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php else: ?>

    <p>
        <?php _e('No related products found.', 'woocommerce-membership'); ?>
    </p>

<?php endif; ?>