<?php
if (!defined('ABSPATH')) exit('No direct script access allowed');

 /**
 * CodeNegar WooCommerce Social Login User Details
 *
 * Generates HTML codes of User Details
 *
 * @package    	WooCommerce Social Login
 * @author      Farhad Ahmadi <ahm.farhad@gmail.com>
 * @license     http://codecanyon.net/licenses
 * @link		http://codenegar.com/woocommerce-social-login/
 * @version    	1.0
 */
 
global $codenegar_wcsl, $wpdb;
$wcsl_users = $wpdb->prefix . "wcsl_users";
$id = intval($_GET['user_id']);
$query = "SELECT * FROM $wcsl_users where ID=$id limit 1";
$data = $wpdb->get_row($query, ARRAY_A);
if(count($data)==0) return;
$columns = array(
	'ID' => __('Record ID', $codenegar_wcsl->text_domain),
	'identifier' => __('identifier', $codenegar_wcsl->text_domain),
	'wp_user_id' => __('WordPress User ID', $codenegar_wcsl->text_domain),
	'provider'=>__('Provider', $codenegar_wcsl->text_domain),
	'profileURL'=>__('Profile URL', $codenegar_wcsl->text_domain),
	'photoURL'=>__('Photo URL', $codenegar_wcsl->text_domain),
	'displayName'=>__('Name', $codenegar_wcsl->text_domain),
	'webSiteURL'=>__('website', $codenegar_wcsl->text_domain),
	'description'=>__('Description', $codenegar_wcsl->text_domain),
	'firstName'=>__('First Name', $codenegar_wcsl->text_domain),
	'lastName'=>__('Last Name', $codenegar_wcsl->text_domain),
	'gender'=>__('Gender', $codenegar_wcsl->text_domain),
	'language'=>__('Language', $codenegar_wcsl->text_domain),
	'age'=>__('Age', $codenegar_wcsl->text_domain),
	'email'=>__('Email', $codenegar_wcsl->text_domain),
	'phone'=>__('Phone', $codenegar_wcsl->text_domain),
	'address'=>__('Address', $codenegar_wcsl->text_domain),
	'country'=>__('Country', $codenegar_wcsl->text_domain),
	'region'=>__('Region', $codenegar_wcsl->text_domain),
	'city'=>__('City', $codenegar_wcsl->text_domain),
	'zip'=>__('Zip', $codenegar_wcsl->text_domain),
	'ip'=>__('IP Address', $codenegar_wcsl->text_domain),
	'agent'=>__('User Agent', $codenegar_wcsl->text_domain),
	'birthDay'=>__('birthDay', $codenegar_wcsl->text_domain),
	'birthMonth'=>__('birthMonth', $codenegar_wcsl->text_domain),
	'birthYear'=>__('birthYear', $codenegar_wcsl->text_domain),
);
$i = 0;
?>

<table class="wp-list-table widefat plugins" cellspacing="0">
	<thead>
	<tr>
		<th scope="col" id="name" class="manage-column column-name" style="">
		<?php _e('Key', $codenegar_wcsl->text_domain); ?>
		</th>
		<th scope="col" id="description" class="manage-column column-description" style="">
		<?php _e('Value', $codenegar_wcsl->text_domain); ?>
		</th>
	</tr>
	</thead>

	<tfoot>
	<tr>
		<th scope="col" class="manage-column column-name" style="">
		<?php _e('Key', $codenegar_wcsl->text_domain); ?>
		</th>
		<th scope="col" class="manage-column column-description" style="">
		<?php _e('Value', $codenegar_wcsl->text_domain); ?>
		</th>
		</tr>
	</tfoot>

	<tbody id="the-list">
	<?php foreach ($data as $key=>$val):
		  if(empty($val)) continue; // skip empty values
	?>
		<tr id="buggy" class="<?php if(($i % 2)==0) { echo  'active'; }else{ echo 'inactive'; } $i++; ?>">
		<td class="plugin-title">
		<strong><?php echo $columns[$key]; ?></strong>
		</td>
		<td class="column-description desc">
		<div class="plugin-description">
		<p><?php echo $val; ?></p>
		</div>
		</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>