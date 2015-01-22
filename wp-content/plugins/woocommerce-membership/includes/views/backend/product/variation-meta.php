<?php

/**
 * View for WooCommerce Variation Edit page Membership Plan selection field
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<tr class="show_if_rpwcm_variable" style="display: table-row;">
    <td>
        <label for="_rpwcm_plans"><?php _e('Membership Plans:', 'woocommerce-membership'); ?></label>
        <?php WooCommerce_Membership::render_field_multiselect(array('name' => '_rpwcm_plans[' . $loop . ']', 'class' => 'rpwcm_field_plans', 'values' => $values, 'selected' => $selected)); ?>
    </td>
    <td>
    </td>
</tr>