<?php

/*
 * Returns settings for this plugin
 * 
 * @return array
 */
if (!function_exists('rpwcm_plugin_settings')) {
function rpwcm_plugin_settings()
{
    return array(
        'general' => array(
            'title' => __('General', 'woocommerce-membership'),
            'icon' => '<i class="fa fa-cogs" style="font-size: 0.8em;"></i>',
            'children' => array(
            ),
        ),
    );
}
}