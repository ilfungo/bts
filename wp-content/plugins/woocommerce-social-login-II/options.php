<?php
if (!defined('ABSPATH')) exit('No direct script access allowed');

 /**
 * CodeNegar WooCommerce Social Login Options
 *
 * Generates Options page
 *
 * @package    	WooCommerce Social Login
 * @author      Farhad Ahmadi <ahm.farhad@gmail.com>
 * @license     http://codecanyon.net/licenses
 * @link		http://codenegar.com/woocommerce-social-login/
 * @version    	1.0
 */
 
global $woocommerce, $codenegar_wcsl;
$current_section = isset($_GET['section'])? $_GET['section']: '';

$current = $current_section ? '' : 'class="current"';

$links = array( '<a href="' . admin_url('admin.php?page=woocommerce_settings&tab=social_login') . '" ' . $current . '>' . __( 'Social Networks', $codenegar_wcsl->text_domain ) . '</a>' );

$socials = $codenegar_wcsl->helper->get_social_list();
$socials['users_list'] = 'Users List'; // add a new item to array
$payment_gateways = $woocommerce->payment_gateways->payment_gateways();

foreach ( $socials as $id=>$title ) {
	$current = ( $id == $current_section ) ? 'class="current"' : '';
	$links[] = '<a href="' . add_query_arg( 'section', $id, admin_url('admin.php?page=woocommerce_settings&tab=social_login') ) . '"' . $current . '>' . esc_html( $title ) . '</a>';
}

echo '<ul class="subsubsub"><li>' . implode( ' | </li><li>', $links ) . '</li></ul><br class="clear" />';

// Get specific social network options
if ( $current_section ) {
	global $codenegar_wcsl;
	$codenegar_wcsl->html->get_social_login_html($current_section);
} else { // Get main section options
	$codenegar_wcsl->html->main_section();
	$codenegar_wcsl->html->main_dragable();
}