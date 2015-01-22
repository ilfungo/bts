<?php
if (!defined('ABSPATH')) exit('No direct script access allowed');

 /**
 * CodeNegar WooCommerce Social Login HTML class
 *
 * Generates HTML codes
 *
 * @package    	WooCommerce Social Login
 * @author      Farhad Ahmadi <ahm.farhad@gmail.com>
 * @license     http://codecanyon.net/licenses
 * @link		http://codenegar.com/woocommerce-social-login/
 * @version    	1.0
 */
 
class CodeNegar_wcsl_html{
	
	function __construct(){
		
	}
	
	function get_social_login_html($social){
		switch ($social){
			case 'google':
				$this->google();
			break;
			case 'facebook':
				$this->facebook();
			break;
			case 'twitter':
				$this->twitter();
			break;
			case 'yahoo':
				$this->yahoo();
			break;
			case 'live':
				$this->live();
			break;
			case 'linkedin':
				$this->linkedin();
			break;
			case 'foursquare':
				$this->foursquare();
			break;
			case 'aol':
				$this->aol();
			break;
			case 'users_list':
				$this->users_list();
			break;
		}
	}
	
	/**
	* HTML code needed for wordpress native uploader
	*/
	
	public function image_upload_field($value='', $name='') {
		global $codenegar_wcpf;
	?>
		
		<input id="<?php echo $name; ?>" type="text" size="90" name="<?php echo $name; ?>" value="<?php echo $value; ?>" />
		<input id="<?php echo $name; ?>_button" type="button" value="<?php _e('Upload Image', $codenegar_wcpf->text_domain); ?>" />
		
		<script type='text/javascript' >
		jQuery(document).ready(function() {

			jQuery('#<?php echo $name; ?>_button').click(function() {
				formfield = jQuery('#<?php echo $name; ?>').attr('name');
				tb_show('', 'media-upload.php?post_id=1&flash=0&simple_slideshow=true&TB_iframe=true');
				return false;
			});

			window.send_to_editor = function(html) {
				imgurl = jQuery('img',html).attr('src');
				jQuery('#<?php echo $name; ?>').val(imgurl);
				tb_remove();
			}
		});
		</script>
		
	<?php
	}
	
	public function main_section(){
		global $codenegar_wcsl;
		$main_options = get_option('codenegar_wcsl_main');
		$section = 'main';
		$codenegar_social_login_settings =  apply_filters('woocommerce_social_login_settings', array(
			array(
				'title' => __('Main Options', $codenegar_wcsl->text_domain),
				'desc' => '',
				'type' => 'title', 
				'id' => 'social_login_options'
			),
			array(
				'title' => __('Description about social login', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> 'social_login_text',
				'css' 		=> 'width:100%; height: 75px; margin-top: 5px;',
				'type' 		=> 'textarea',
				'default'	=> $main_options['social_login_text']
			),
			array(
				'title' => __('HTML Element Type', $codenegar_wcsl->text_domain),
				'id' 		=> 'social_login_element_type',
				'default'	=> $main_options['social_login_element_type'],
				'type' 		=> 'radio',
				'desc_tip'	=> '',
				'options'	=> array(
					'span' => __('SPAN', $codenegar_wcsl->text_domain),
					'div' => __('DIV', $codenegar_wcsl->text_domain),
					'li' => __('List', $codenegar_wcsl->text_domain),
					'label' => __('Label', $codenegar_wcsl->text_domain),
				),
			),
			array(
				'title' => __('After Login Redirect User to', $codenegar_wcsl->text_domain),
				'id' 		=> 'social_login_redirect_to',
				'default'	=> $main_options['social_login_redirect_to'],
				'type' 		=> 'radio',
				'desc_tip'	=> '',
				'options'	=> array(
					'shop' => __('Shop', $codenegar_wcsl->text_domain),
					'cart' => __('Cart Page', $codenegar_wcsl->text_domain),
					'checkout' => __('Checkout Page', $codenegar_wcsl->text_domain),
					'myaccount' => __('My Account Page', $codenegar_wcsl->text_domain),
					'edit_address' => __('Edit Address Page', $codenegar_wcsl->text_domain),
					'custom' => __('Custom URL(Enter Below)', $codenegar_wcsl->text_domain),
				),
			),
			array(
				'title' => __('Custom URL Redirect', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> "codenegar_wcsl_custom_url",
				'type' 		=> 'text',
				'css' 		=> 'min-width:360px;',
				'default'	=> $main_options['codenegar_wcsl_custom_url']
			),
			array(
				'title' => __( 'Display Social Login', $codenegar_wcsl->text_domain),
				'desc' 		=> __( 'At WordPress Profile Page', $codenegar_wcsl->text_domain),
				'id' 		=> 'display_profile_page',
				'default'	=> (isset($main_options['display_profile_page']) && (intval($main_options['display_profile_page'])==1 || $main_options['display_profile_page'] == 'yes'))? 'yes': 'no',
				'type' 		=> 'checkbox',
				'checkboxgroup'		=> 'start'
			),
			array(
				'title' => __( 'Display Social Login', $codenegar_wcsl->text_domain),
				'desc' 		=> __( 'At WooCommerce My Account Page After Login Form', $codenegar_wcsl->text_domain),
				'id' 		=> 'display_my_account_page_after_from',
				'default'	=> (isset($main_options['display_my_account_page_after_from']) && (intval($main_options['display_my_account_page_after_from'])==1 || $main_options['display_my_account_page_after_from'] == 'yes'))? 'yes': 'no',
				'type' 		=> 'checkbox',
				'checkboxgroup'		=> ''
			),
			array(
				'title' => __( 'Display Social Login', $codenegar_wcsl->text_domain),
				'desc' 		=> __( 'At WooCommerce My Account Page', $codenegar_wcsl->text_domain),
				'id' 		=> 'display_my_account_page',
				'default'	=> (isset($main_options['display_my_account_page']) && (intval($main_options['display_my_account_page'])==1 || $main_options['display_my_account_page'] == 'yes'))? 'yes': 'no',
				'type' 		=> 'checkbox',
				'checkboxgroup'		=> 'end'
			),
			array(
				'title' => __("", $codenegar_wcsl->text_domain),
				'id' 		=> 'social_login_icons_display',
				'default'	=> $main_options['social_login_icons_display'],
				'type' 		=> 'radio',
				'desc_tip'	=> '',
				'options'	=> array(
					'after_login' => __('At CheckOut After Login', $codenegar_wcsl->text_domain),
					'before_login' => __('At CheckOut Before Login', $codenegar_wcsl->text_domain),
					'after_coupon' => __('At CheckOut After Coupon', $codenegar_wcsl->text_domain),
					'none' => __('None', $codenegar_wcsl->text_domain),
				),
			),
			array(
				'title' => __('Icon Size', $codenegar_wcsl->text_domain),
				'id' 		=> 'social_login_icon_size',
				'default'	=> $main_options['social_login_icon_size'],
				'type' 		=> 'radio',
				'desc_tip'	=> '',
				'options'	=> array(
					'16px' => __('Small', $codenegar_wcsl->text_domain),
					'24px' => __('Medium', $codenegar_wcsl->text_domain),
					'32px' => __('Large', $codenegar_wcsl->text_domain),
					'custom' => __('Custom Icons', $codenegar_wcsl->text_domain)
				),
			),
			array(
				'desc' 		=> '',
				'id' 		=> 'social_login_section_name',
				'default'	=> $section,
				'type' 		=> 'text',
				'checkboxgroup'	=> '',
				'css'		=> 'display: none;'
			),
			array(
				'title' => __('Message on Error Page', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> 'social_login_error_text',
				'css' 		=> 'width:100%; height: 75px; margin-top: 5px;',
				'type' 		=> 'textarea',
				'default'	=> $main_options['social_login_error_text']
			),
			array( 'type' => 'sectionend', 'id' => 'social_login_options' ),
		));
		return woocommerce_admin_fields($codenegar_social_login_settings);
	}
	
	function main_dragable() {
		global $woocommerce, $codenegar_wcsl;
		$socials = $codenegar_wcsl->helper->get_social_list();
		?>
		<h3><?php _e('Drag to Change Order', $codenegar_wcsl->text_domain); ?></h3>
		<tr valign="top">
			<td class="forminp" colspan="2">
				<table class="wc_gateways widefat" cellspacing="0">
					<thead>
						<tr>
							<th width="1%" >&nbsp;</th>
							<th width="1%" style="display: none;"><?php _e('Change Order', $codenegar_wcsl->text_domain); ?></th>
							<th><?php _e('Network', $codenegar_wcsl->text_domain); ?></th>
							<th><?php _e('Register Count', $codenegar_wcsl->text_domain); ?></th>
							<th><?php _e('Status', $codenegar_wcsl->text_domain); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$logged_count = array();
						foreach ( $socials as $id=>$title ) :
							$logged_count[$id] = $codenegar_wcsl->helper->count_logged($id);
							echo '<tr>
								<td>
									<img src="'.$codenegar_wcsl->url.'images/16px/'.$id.'.png" width="16" height="16" />
								</td>
								<td width="1%" style="display: none;" class="radio">
									<input type="radio" name="social_network" value="' . esc_attr( $id ) . '" />
									<input type="hidden" name="social_order[]" value="' . esc_attr( $id ) . '" />
								</td>
								<td>
									<p><strong>' . $title . '</strong><br/></p>
								</td>
								<td>
									'.$logged_count[$id].'
								</td>
								<td>';
							$data = get_option("codenegar_wcsl_{$id}");
							
							if ( isset($data['codenegar_wcsl_enabled']) && $data['codenegar_wcsl_enabled'] == 'yes' )
								echo '<img src="' . $woocommerce->plugin_url() . '/assets/images/success@2x.png" width="16" height="14" alt="yes" />';
							else
								echo '<img src="' . $woocommerce->plugin_url() . '/assets/images/success-off@2x.png" width="16" height="14" alt="no" />';

							echo '</td>
							</tr>';

						endforeach;
						?>
					</tbody>
				</table>
			</td>
		</tr>
		<?php
		$colors = $logged_count; // for setting order
		$colors['google'] = '4E6CF7';
		$colors['facebook'] = '4863AE';
		$colors['twitter'] = '46c0FB';
		$colors['yahoo'] = 'A200C2';
		$colors['live'] = '0052A4';
		$colors['linkedin'] = '0083A8';
		$colors['foursquare'] = '44A8E0';
		$colors['aol'] = 'F00000';
		foreach($logged_count as $key=>$val){
			if($val==0){
				unset($logged_count[$key]);
				unset($colors[$key]);
			}
		}
		?>
    <script type="text/javascript">

      // Load the Visualization API and the piechart package.
      google.load('visualization', '1.0', {'packages':['corechart']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.setOnLoadCallback(drawChart);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawChart() {

        // Create the data table.
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Topping');
        data.addColumn('number', 'Slices');
        data.addRows([<?php
		foreach($logged_count as $key=>$val){
			echo "['".ucfirst($key)."', $val], ";
		}
        ?>]);

        // Set chart options
        var options = {'title':'<?php _e('Social Networks Register Percentage ', $codenegar_wcsl->text_domain); ?>',
                       'width':650,
                       'height':450};

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
    </script>
	<div id="chart_div" class="wcsl_chart" style="text-align: center; margin-top: 30px; width:650px; margin:auto;"></div>
		<?php
	}
	
	public function google(){
		$section = "google";
		$data = get_option("codenegar_wcsl_{$section}");
		global $codenegar_wcsl;
		
		$form_fields = array(
			array( 
				'title' => __('Google', $codenegar_wcsl->text_domain),
				'type' => 'title', 'id' => 'social_login'
			),

			array(
				'desc' 		=> '',
				'id' 		=> 'social_login_section_name',
				'default'	=> $section,
				'type' 		=> 'text',
				'checkboxgroup'	=> '',
				'css'		=> 'display: none;'
			),

			array(
				'title' 	=> __('Enabled', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> "codenegar_wcsl_enabled",
				'css' 		=> 'min-width:100px;',
				'default'	=> $data["codenegar_wcsl_enabled"],
				'type' 		=> 'select',
				'options' => array(
					'yes'  			=> __('Yes', $codenegar_wcsl->text_domain),
					'no'		=> __('No', $codenegar_wcsl->text_domain),
				),
				'desc_tip'	=>  true,
			),

			array(
				'title' => __('Client ID', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> "codenegar_wcsl_appid",
				'type' 		=> 'text',
				'css' 		=> 'min-width:360px;',
				'default'	=> $data["codenegar_wcsl_appid"]
			),
			
			array(
				'title' => __('Client secret', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> "codenegar_wcsl_appsecret",
				'type' 		=> 'text',
				'css' 		=> 'min-width:360px;',
				'default'	=> $data["codenegar_wcsl_appsecret"]
			),
			
			array(
				'title' => __('Callback URL', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> "codenegar_wcsl_callback_url",
				'type' 		=> 'text',
				'css' 		=> 'min-width:360px;',
				'custom_attributes' => array('disabled'=>'disabled'),
				'default'	=> add_query_arg("hauth.done", "Google", $codenegar_wcsl->url . 'library/')
			),
			array( 'type' => 'sectionend', 'id' => "social_login_{$section}" ),

		);
	
		woocommerce_admin_fields($form_fields);
	}
	
	public function facebook(){
		$section = "facebook";
		$data = get_option("codenegar_wcsl_{$section}");
		global $codenegar_wcsl;
		
		$form_fields = array(
			array( 
				'title' => __('Facebook', $codenegar_wcsl->text_domain),
				'type' => 'title', 'id' => 'social_login'
			),

			array(
				'desc' 		=> '',
				'id' 		=> 'social_login_section_name',
				'default'	=> $section,
				'type' 		=> 'text',
				'checkboxgroup'	=> '',
				'css'		=> 'display: none;'
			),

			array(
				'title' 	=> __('Enabled', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> "codenegar_wcsl_enabled",
				'css' 		=> 'min-width:100px;',
				'default'	=> $data["codenegar_wcsl_enabled"],
				'type' 		=> 'select',
				'options' => array(
					'yes'  			=> __('Yes', $codenegar_wcsl->text_domain),
					'no'		=> __('No', $codenegar_wcsl->text_domain),
				),
				'desc_tip'	=>  true,
			),

			array(
				'title' => __('App ID', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> "codenegar_wcsl_appid",
				'type' 		=> 'text',
				'css' 		=> 'min-width:360px;',
				'default'	=> $data["codenegar_wcsl_appid"]
			),
			
			array(
				'title' => __('App Secret', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> "codenegar_wcsl_appsecret",
				'type' 		=> 'text',
				'css' 		=> 'min-width:360px;',
				'default'	=> $data["codenegar_wcsl_appsecret"]
			),

			array( 'type' => 'sectionend', 'id' => "social_login_{$section}" ),

		);
	
		woocommerce_admin_fields($form_fields);
	}
	
	public function twitter(){
		$section = "twitter";
		$data = get_option("codenegar_wcsl_{$section}");
		global $codenegar_wcsl;
		
		$form_fields = array(
			array( 
				'title' => __('Twitter', $codenegar_wcsl->text_domain),
				'type' => 'title', 'id' => 'social_login'
			),

			array(
				'desc' 		=> '',
				'id' 		=> 'social_login_section_name',
				'default'	=> $section,
				'type' 		=> 'text',
				'checkboxgroup'	=> '',
				'css'		=> 'display: none;'
			),

			array(
				'title' 	=> __('Enabled', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> "codenegar_wcsl_enabled",
				'css' 		=> 'min-width:100px;',
				'default'	=> $data["codenegar_wcsl_enabled"],
				'type' 		=> 'select',
				'options' => array(
					'yes'  			=> __('Yes', $codenegar_wcsl->text_domain),
					'no'		=> __('No', $codenegar_wcsl->text_domain),
				),
				'desc_tip'	=>  true,
			),

			array(
				'title' => __('Consumer key', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> "codenegar_wcsl_appid",
				'type' 		=> 'text',
				'css' 		=> 'min-width:360px;',
				'default'	=> $data["codenegar_wcsl_appid"]
			),
			
			array(
				'title' => __('Consumer Secret', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> "codenegar_wcsl_appsecret",
				'type' 		=> 'text',
				'css' 		=> 'min-width:360px;',
				'default'	=> $data["codenegar_wcsl_appsecret"]
			),

			array( 'type' => 'sectionend', 'id' => "social_login_{$section}" ),

		);
	
		woocommerce_admin_fields($form_fields);
	}
	
	public function yahoo(){
			$section = "yahoo";
		$data = get_option("codenegar_wcsl_{$section}");
		global $codenegar_wcsl;
		
		$form_fields = array(
			array( 
				'title' => __('Yahoo', $codenegar_wcsl->text_domain),
				'type' => 'title', 'id' => 'social_login'
			),

			array(
				'desc' 		=> '',
				'id' 		=> 'social_login_section_name',
				'default'	=> $section,
				'type' 		=> 'text',
				'checkboxgroup'	=> '',
				'css'		=> 'display: none;'
			),

			array(
				'title' 	=> __('Enabled', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> "codenegar_wcsl_enabled",
				'css' 		=> 'min-width:100px;',
				'default'	=> $data["codenegar_wcsl_enabled"],
				'type' 		=> 'select',
				'options' => array(
					'yes'  			=> __('Yes', $codenegar_wcsl->text_domain),
					'no'		=> __('No', $codenegar_wcsl->text_domain),
				),
				'desc_tip'	=>  true,
			),

			array(
				'title' => __('Consumer Key', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> "codenegar_wcsl_appid",
				'type' 		=> 'text',
				'css' 		=> 'min-width:360px;',
				'default'	=> $data["codenegar_wcsl_appid"]
			),
			
			array(
				'title' => __('Consumer Secret', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> "codenegar_wcsl_appsecret",
				'type' 		=> 'text',
				'css' 		=> 'min-width:360px;',
				'default'	=> $data["codenegar_wcsl_appsecret"]
			),

			array( 'type' => 'sectionend', 'id' => "social_login_{$section}" ),

		);
	
		woocommerce_admin_fields($form_fields);
	}
	
	public function live(){
		$section = "live";
		$data = get_option("codenegar_wcsl_{$section}");
		global $codenegar_wcsl;
		
		$form_fields = array(
			array( 
				'title' => __('Microsoft Live', $codenegar_wcsl->text_domain),
				'type' => 'title', 'id' => 'social_login'
			),

			array(
				'desc' 		=> '',
				'id' 		=> 'social_login_section_name',
				'default'	=> $section,
				'type' 		=> 'text',
				'checkboxgroup'	=> '',
				'css'		=> 'display: none;'
			),

			array(
				'title' 	=> __('Enabled', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> "codenegar_wcsl_enabled",
				'css' 		=> 'min-width:100px;',
				'default'	=> $data["codenegar_wcsl_enabled"],
				'type' 		=> 'select',
				'options' => array(
					'yes'  			=> __('Yes', $codenegar_wcsl->text_domain),
					'no'		=> __('No', $codenegar_wcsl->text_domain),
				),
				'desc_tip'	=>  true,
			),

			array(
				'title' => __('Client ID', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> "codenegar_wcsl_appid",
				'type' 		=> 'text',
				'css' 		=> 'min-width:360px;',
				'default'	=> $data["codenegar_wcsl_appid"]
			),
			
			array(
				'title' => __('Client Secret', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> "codenegar_wcsl_appsecret",
				'type' 		=> 'text',
				'css' 		=> 'min-width:360px;',
				'default'	=> $data["codenegar_wcsl_appsecret"]
			),

			array( 'type' => 'sectionend', 'id' => "social_login_{$section}" ),

		);
	
		woocommerce_admin_fields($form_fields);
	}
	
	public function linkedin(){
		$section = "linkedin";
		$data = get_option("codenegar_wcsl_{$section}");
		global $codenegar_wcsl;
		
		$form_fields = array(
			array( 
				'title' => __('LinkedIn', $codenegar_wcsl->text_domain),
				'type' => 'title', 'id' => 'social_login'
			),

			array(
				'desc' 		=> '',
				'id' 		=> 'social_login_section_name',
				'default'	=> $section,
				'type' 		=> 'text',
				'checkboxgroup'	=> '',
				'css'		=> 'display: none;'
			),

			array(
				'title' 	=> __('Enabled', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> "codenegar_wcsl_enabled",
				'css' 		=> 'min-width:100px;',
				'default'	=> $data["codenegar_wcsl_enabled"],
				'type' 		=> 'select',
				'options' => array(
					'yes'  			=> __('Yes', $codenegar_wcsl->text_domain),
					'no'		=> __('No', $codenegar_wcsl->text_domain),
				),
				'desc_tip'	=>  true,
			),

			array(
				'title' => __('API Key', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> "codenegar_wcsl_appid",
				'type' 		=> 'text',
				'css' 		=> 'min-width:360px;',
				'default'	=> $data["codenegar_wcsl_appid"]
			),
			
			array(
				'title' => __('Secret Key', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> "codenegar_wcsl_appsecret",
				'type' 		=> 'text',
				'css' 		=> 'min-width:360px;',
				'default'	=> $data["codenegar_wcsl_appsecret"]
			),

			array( 'type' => 'sectionend', 'id' => "social_login_{$section}" ),

		);
	
		woocommerce_admin_fields($form_fields);
	}
	
	public function foursquare(){
		$section = "foursquare";
		$data = get_option("codenegar_wcsl_{$section}");
		global $codenegar_wcsl;
		
		$form_fields = array(
			array( 
				'title' => __('Foursquare', $codenegar_wcsl->text_domain),
				'type' => 'title', 'id' => 'social_login'
			),

			array(
				'desc' 		=> '',
				'id' 		=> 'social_login_section_name',
				'default'	=> $section,
				'type' 		=> 'text',
				'checkboxgroup'	=> '',
				'css'		=> 'display: none;'
			),

			array(
				'title' 	=> __('Enabled', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> "codenegar_wcsl_enabled",
				'css' 		=> 'min-width:100px;',
				'default'	=> $data["codenegar_wcsl_enabled"],
				'type' 		=> 'select',
				'options' => array(
					'yes'  			=> __('Yes', $codenegar_wcsl->text_domain),
					'no'		=> __('No', $codenegar_wcsl->text_domain),
				),
				'desc_tip'	=>  true,
			),

			array(
				'title' => __('Client ID', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> "codenegar_wcsl_appid",
				'type' 		=> 'text',
				'css' 		=> 'min-width:360px;',
				'default'	=> $data["codenegar_wcsl_appid"]
			),
			
			array(
				'title' => __('Client Secret', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> "codenegar_wcsl_appsecret",
				'type' 		=> 'text',
				'css' 		=> 'min-width:360px;',
				'default'	=> $data["codenegar_wcsl_appsecret"]
			),
			
			array(
				'title' => __('Callback URL', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> "codenegar_wcsl_callback_url",
				'type' 		=> 'text',
				'css' 		=> 'min-width:360px;',
				'custom_attributes' => array('disabled'=>'disabled'),
				'default'	=> add_query_arg("hauth.done", "Foursquare", $codenegar_wcsl->url . 'library/')
			),
			array( 'type' => 'sectionend', 'id' => "social_login_{$section}" ),

		);
	
		woocommerce_admin_fields($form_fields);
	}
	
	public function aol(){
		$section = "aol";
		$data = get_option("codenegar_wcsl_{$section}");
		global $codenegar_wcsl;
		
		$form_fields = array(
			array( 
				'title' => __('AOL', $codenegar_wcsl->text_domain),
				'type' => 'title', 'id' => 'social_login'
			),

			array(
				'desc' 		=> '',
				'id' 		=> 'social_login_section_name',
				'default'	=> $section,
				'type' 		=> 'text',
				'checkboxgroup'	=> '',
				'css'		=> 'display: none;'
			),

			array(
				'title' 	=> __('Enabled', $codenegar_wcsl->text_domain),
				'desc' 		=> '',
				'id' 		=> "codenegar_wcsl_enabled",
				'css' 		=> 'min-width:100px;',
				'default'	=> $data["codenegar_wcsl_enabled"],
				'type' 		=> 'select',
				'options' => array(
					'yes'  			=> __('Yes', $codenegar_wcsl->text_domain),
					'no'		=> __('No', $codenegar_wcsl->text_domain),
				),
				'desc_tip'	=>  true,
			),
			array( 'type' => 'sectionend', 'id' => "social_login_{$section}" ),

		);
	
		woocommerce_admin_fields($form_fields);
	}
	
	public function users_list(){
		global $codenegar_wcsl;
		if(isset($_GET['details_view']) && isset($_GET['user_id']) && intval($_GET['user_id'])>0){
			require_once($codenegar_wcsl->path . 'details.php');
			wp_nonce_field('woocommerce-settings', '_wpnonce');
			return;
		}
		require_once($codenegar_wcsl->path . 'table.php');
		$wcsl_users = new Wcsl_users();
		$wcsl_users->prepare_items();
		$wcsl_users->display();
		wp_nonce_field('woocommerce-settings', '_wpnonce');
	}
	
	public function icons($next=''){
		global $codenegar_wcsl;
		$social_list = $codenegar_wcsl->helper->get_social_list();
		$added_socials_list = $codenegar_wcsl->helper->added_socials_list();
		$main_section_options = get_option("codenegar_wcsl_main");
		echo '<div class="codenegar_social_login_text">' . $main_section_options['social_login_text'] . '</div>';
		$element = $main_section_options['social_login_element_type'];
		$icon_size = $main_section_options['social_login_icon_size'];
		if($element=="li") echo '<ul clas="codenegar_wcsl_icons_list">';
		foreach($social_list as $name=>$title){
			$options = get_option("codenegar_wcsl_$name");
			$is_added = in_array($name, $added_socials_list);
			if($is_added){
				$name .= '_added';
			}
			if($options['codenegar_wcsl_enabled'] != 'yes'){
				continue; // skip disabled services
			}
			if($is_added){
				$url = "#!";
			}else{
				$url = $codenegar_wcsl->helper->login_url($name, $next);
			}
			echo "<{$element} class=\"codenegar_wcsl_icon wcsl_{$name}_icon\">" . '<a rel="nofollow" href="'.$url.'" title="'.$title.'">
			<img alt="'.$title.'" src="'.$codenegar_wcsl->url . 'images/'.$icon_size.'/' . $name .'.png"/>
			</a>' ."</{$element}>";
		}
		if($element=="li") echo "</ul>";
	}
	
	public function frontend(){
		global $codenegar_wcsl;
	?>
		<p class="woocommerce-info"><?php _e('Prefer Social Login', $codenegar_wcsl->text_domain); ?> <a href="#" class="codenegar_show_social_login"><?php _e( 'Click here to login', $codenegar_wcsl->text_domain ); ?></a></p>
		<form method="post" class="login codenegar_social_login" style="display: none;">
		<?php
		$this->icons();
		?>
		<div class="clear"></div>
		</form>
	<?php
	}
	
	public function profile_page(){
		global $codenegar_wcsl;
	?>
		<h3><?php _e('Link Your account', $codenegar_wcsl->text_domain); ?></h3>
		  <table class="form-table">
			<tbody>
				<tr>	
				<th></th>	
				<td>
				<?php
					$next = admin_url('profile.php');
					/* add a parameter that indicates successfull add message or logged icons be different */
					$this->icons($next);
				?>
				</td>
				</tr>
			</tbody>
		</table>
	<?php
	}
	
	public function my_account_page(){
		global $codenegar_wcsl;
	?>
	<p class="wcsl_blank_seperator">&nbsp;</p>
	<h2><?php _e('Link Your account', $codenegar_wcsl->text_domain); ?></h2>
	<p class="myaccount_address">
		<?php
			$next = get_permalink(intval(get_option('woocommerce_myaccount_page_id')));
			$this->icons($next);
		?>
	</p>
	<?php
	}
}
?>