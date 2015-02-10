<div class="block ui-tabs-panel deactive" id="option-ui-id-3" >	
	<?php $current_options = get_option('quality_options');
	if(isset($_POST['webriti_settings_save_3']))
	{	
		if($_POST['webriti_settings_save_3'] == 1) 
		{
			if ( empty($_POST) || !wp_verify_nonce($_POST['webriti_gernalsetting_nonce_customization'],'webriti_customization_nonce_gernalsetting') )
			{  print 'Sorry, your nonce did not verify.';	exit; }
			else  
			{	
			if(isset($_POST['service_enable']))
				{ echo $current_options['service_enable']="on"; } 
                else 
                { echo $current_options['service_enable']="off"; }				
				
				$current_options['service_title'] = sanitize_text_field($_POST['service_title']);
				$current_options['service_description'] = sanitize_text_field($_POST['service_description']);
				$current_options['service_one_title'] = sanitize_text_field($_POST['service_one_title']);
				$current_options['service_two_title'] = sanitize_text_field($_POST['service_two_title']);
				$current_options['service_three_title'] = sanitize_text_field($_POST['service_three_title']);
				$current_options['service_four_title'] = sanitize_text_field($_POST['service_four_title']);
				
				$current_options['service_one_icon'] = sanitize_text_field($_POST['service_one_icon']);
				$current_options['service_two_icon'] = sanitize_text_field($_POST['service_two_icon']);
				$current_options['service_three_icon'] = sanitize_text_field($_POST['service_three_icon']);
				$current_options['service_four_icon'] = sanitize_text_field($_POST['service_four_icon']);
				
				$current_options['service_one_text'] = sanitize_text_field($_POST['service_one_text']);
				$current_options['service_two_text'] = sanitize_text_field($_POST['service_two_text']);
				$current_options['service_three_text'] = sanitize_text_field($_POST['service_three_text']);
				$current_options['service_four_text'] = sanitize_text_field($_POST['service_four_text']);
				
				update_option('quality_options',$current_options);
			}
		}	
		if($_POST['webriti_settings_save_3'] == 2) 
		{
			
			$current_options['service_enable'] = "on";
			$current_options['service_title']='What We Do';
			$current_options['service_description'] ='We provide best WordPress solutions for your business. Thanks to our framework you will get more happy customers.';
			$current_options['service_one_title'] = 'Fully Responsive';
			$current_options['service_two_title'] = 'SEO Friendly';
			$current_options['service_three_title'] = 'Easy Customization';
			$current_options['service_four_title'] = 'Well Documentation';
			
			$current_options['service_one_icon'] = 'fa fa-shield';
			$current_options['service_two_icon'] = 'fa fa-tablet';
			$current_options['service_three_icon'] = 'fa fa-edit';
			$current_options['service_four_icon'] = 'fa fa-star-half-o';
			
			$current_options['service_one_text'] = 'Lorem Ipsum which looks reason able. The generated Lorem Ipsum is ';
			$current_options['service_two_text'] = 'Lorem Ipsum sd kl;kop iweopi opwipoei paiop oppsum is ';
			$current_options['service_three_text'] = 'Lorem Ip sdl; k;lke poiowie iopwi oped Lorem Ipsum is ';
			$current_options['service_four_text'] = 'Lorem Ipsum which looks reason able. The generated Lorem Ipsum is ';
			update_option('quality_options',$current_options);
		}
	}  ?>
	<form method="post" id="webriti_theme_options_3">
		<div id="heading">
			<table style="width:100%;"><tr>
				<td><h2><?php _e('Service Settings','quality');?></h2></td>
				<td><div class="webriti_settings_loding" id="webriti_loding_3_image"></div>
					<div class="webriti_settings_massage" id="webriti_settings_save_3_success" ><?php _e('Options data successfully Saved','quality');?></div>
					<div class="webriti_settings_massage" id="webriti_settings_save_3_reset" ><?php _e('Options data successfully reset','quality');?></div>
				</td>
				<td style="text-align:right;">
					<input class="reset-button btn" type="button" name="reset" value="Restore Defaults" onclick="webriti_option_data_reset('3');">
					<input class="btn btn-primary" type="button" value="Save Options" onclick="webriti_option_data_save('3')" >
				</td>
				</tr>
			</table>	
		</div>		
		<?php wp_nonce_field('webriti_customization_nonce_gernalsetting','webriti_gernalsetting_nonce_customization'); ?>
		<div class="section">
			<h3><?php _e('Enable Service Section On HOME Page Template','quality'); ?>  </h3>
			<input type="checkbox" <?php if($current_options['service_enable']=='on') echo "checked='checked'"; ?> id="service_enable" name="service_enable" > <span class="explain"><?php _e('Enable Service section on Home TEMPLATE .','quality'); ?></span>
		</div>
		<div class="section">
			<h3><?php _e('Service Section','quality'); ?></h3>
			<hr>
			<h3><?php _e('Service Title','quality'); ?></h3>
			<input class="webriti_inpute"  type="text" name="service_title" id="service_title" value="<?php echo $current_options['service_title']; ?>" >
			<span class="explain"><?php _e('Enter the service title.','quality'); ?></span>
		</div>
		<div class="section">	
		<h3><?php _e('Service Description','quality'); ?></h3>
			<textarea rows="3" cols="8" id="service_description" name="service_description"><?php if($current_options['service_description']!='') { echo esc_attr($current_options['service_description']); } ?></textarea>
			<span class="explain"><?php _e('Enter the service description.','quality'); ?></span>
		</div>
		<div class="section">	
		<h3><?php _e('Service Title One ','quality'); ?></h3>
			<input class="webriti_inpute"  type="text" name="service_one_title" id="service_one_title" value="<?php echo $current_options['service_one_title']; ?>" >
			<span class="explain"><?php _e('Enter the service title.','quality'); ?></span>
		</div>
		<div class="section">	
		<h3><?php _e('Service Icon One ','quality'); ?></h3>
			<input class="webriti_inpute"  type="text" name="service_one_icon" id="service_one_icon" value="<?php echo $current_options['service_one_icon']; ?>" >
			<span class="explain"><?php _e('Enter the service icon.','quality'); ?></span>
		</div>
		<div class="section">	
		<h3><?php _e('Service Text One ','quality'); ?></h3>
			<textarea rows="3" cols="8" id="service_one_text" name="service_one_text"><?php if($current_options['service_one_text']!='') { echo esc_attr($current_options['service_one_text']); } ?></textarea>
			<span class="explain"><?php _e('Enter the service text.','quality'); ?></span>
		</div>
		<div class="section">	
		<h3><?php _e('Service Title Two ','quality'); ?></h3>
			<input class="webriti_inpute"  type="text" name="service_two_title" id="service_two_title" value="<?php echo $current_options['service_two_title']; ?>" >
			<span class="explain"><?php _e('Enter the service title.','quality'); ?></span>
		</div>
		<div class="section">	
		<h3><?php _e('Service Icon Two ','quality'); ?></h3>
			<input class="webriti_inpute"  type="text" name="service_two_icon" id="service_two_icon" value="<?php echo $current_options['service_two_icon']; ?>" >
			<span class="explain"><?php _e('Enter the service icon.','quality'); ?></span>
		</div>
		<div class="section">	
		<h3><?php _e('Service Text Two ','quality'); ?></h3>
			<textarea rows="3" cols="8" id="service_two_text" name="service_two_text"><?php if($current_options['service_two_text']!='') { echo esc_attr($current_options['service_two_text']); } ?></textarea>
			<span class="explain"><?php _e('Enter the service text.','quality'); ?></span>
		</div>
		<div class="section">	
		<h3><?php _e('Service Title Three ','quality'); ?></h3>
			<input class="webriti_inpute"  type="text" name="service_three_title" id="service_three_title" value="<?php echo $current_options['service_three_title']; ?>" >
			<span class="explain"><?php _e('Enter the service title.','quality'); ?></span>
		</div>
		<div class="section">	
		<h3><?php _e('Service Icon Three ','quality'); ?></h3>
			<input class="webriti_inpute"  type="text" name="service_three_icon" id="service_three_icon" value="<?php echo $current_options['service_three_icon']; ?>" >
			<span class="explain"><?php _e('Enter the service icon.','quality'); ?></span>
		</div>
		<div class="section">	
		<h3><?php _e('Service Text Three ','quality'); ?></h3>
			<textarea rows="3" cols="8" id="service_three_text" name="service_three_text"><?php if($current_options['service_three_text']!='') { echo esc_attr($current_options['service_three_text']); } ?></textarea>
			<span class="explain"><?php _e('Enter the service text.','quality'); ?></span>
		</div>
		<div class="section">	
		<h3><?php _e('Service Title Four ','quality'); ?></h3>
			<input class="webriti_inpute"  type="text" name="service_four_title" id="service_four_title" value="<?php echo $current_options['service_four_title']; ?>" >
			<span class="explain"><?php _e('Enter the service title.','quality'); ?></span>
		</div>
		<div class="section">	
		<h3><?php _e('Service Icon Four ','quality'); ?></h3>
			<input class="webriti_inpute"  type="text" name="service_four_icon" id="service_four_icon" value="<?php echo $current_options['service_four_icon']; ?>" >
			<span class="explain"><?php _e('Enter the service icon.','quality'); ?></span>
		</div>
		<div class="section">	
		<h3><?php _e('Service Text Four ','quality'); ?></h3>
			<textarea rows="3" cols="8" id="service_four_text" name="service_four_text"><?php if($current_options['service_four_text']!='') { echo esc_attr($current_options['service_four_text']); } ?></textarea>
			<span class="explain"><?php _e('Enter the service text.','quality'); ?></span>
		</div>
		<div id="button_section">
			<input type="hidden" value="1" id="webriti_settings_save_3" name="webriti_settings_save_3" />
			<input class="reset-button btn" type="button" name="reset" value="Restore Defaults" onclick="webriti_option_data_reset('3');">
			<input class="btn btn-primary" type="button" value="Save Options" onclick="webriti_option_data_save('3')" >
		</div>
	</form>
</div>