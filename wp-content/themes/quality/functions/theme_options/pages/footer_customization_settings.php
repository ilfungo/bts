<div class="block ui-tabs-panel deactive" id="option-ui-id-11" >	
	<?php $current_options = get_option('quality_options');
	//print_r($current_options);
	if(isset($_POST['webriti_settings_save_11']))
	{	
		if($_POST['webriti_settings_save_11'] == 1) 
		{
			if ( empty($_POST) || !wp_verify_nonce($_POST['webriti_gernalsetting_nonce_customization'],'webriti_customization_nonce_gernalsetting') )
			{  print 'Sorry, your nonce did not verify.';	exit; }
			else  
			{		
				$current_options['footer_customizations']=sanitize_text_field($_POST['footer_customizations']);
				$current_options['created_by_webriti_text']=sanitize_text_field($_POST['created_by_webriti_text']);
				$current_options['created_by_link']=esc_url_raw($_POST['created_by_link']);
				update_option('quality_options',$current_options);
			}
		}	
		if($_POST['webriti_settings_save_11'] == 2) 
		{
			$current_options['footer_customizations']="&copy; 2014 quality Center. All Rights Reserved. Powered by";
			$current_options['created_by_webriti_text']="Webriti";
			$current_options['created_by_link']="http://webriti.com/";		
			update_option('quality_options',$current_options);
		}
	}  ?>
	<form method="post" id="webriti_theme_options_11">
		<div id="heading">
			<table style="width:100%;"><tr>
				<td><h2><?php _e('Footer Customizations','quality');?></h2></td>
				<td><div class="webriti_settings_loding" id="webriti_loding_11_image"></div>
					<div class="webriti_settings_massage" id="webriti_settings_save_11_success" ><?php _e('Options data successfully Saved','quality');?></div>
					<div class="webriti_settings_massage" id="webriti_settings_save_11_reset" ><?php _e('Options data successfully reset','quality');?></div>
				</td>
				<td style="text-align:right;">
					<input class="reset-button btn" type="button" name="reset" value="Restore Defaults" onclick="webriti_option_data_reset('11');">
					<input class="btn btn-primary" type="button" value="Save Options" onclick="webriti_option_data_save('11')" >
				</td>
				</tr>
			</table>	
		</div>		
		<?php wp_nonce_field('webriti_customization_nonce_gernalsetting','webriti_gernalsetting_nonce_customization'); ?>
		<div class="section">		
			<h3><?php _e('footer customizations text','quality'); ?></h3>
			<input class="webriti_inpute"  type="text" name="footer_customizations" id="footer_customizations" value="<?php if(isset($current_options['footer_customizations'])) { echo $current_options['footer_customizations']; } ?>" >
			<span class="explain"><?php  _e('Enter the footer customizations text','quality');?></span>
		</div>		
		<div class="section">	
		<h3><?php _e('Created By Webriti text','quality'); ?></h3>			
			<input class="webriti_inpute"  type="text" name="created_by_webriti_text" id="created_by_webriti_text" value="<?php if(isset($current_options['created_by_webriti_text'])) { echo $current_options['created_by_webriti_text']; } ?>" >
			<span class="explain"><?php  _e('Enter the created by webriti text','quality');?></span>
		</div>
		
		<div class="section">	
		<h3><?php _e('Created By Link','quality'); ?></h3>			
			<input class="webriti_inpute" placeholder="Enter http://example.com"  type="text" name="created_by_link" id="created_by_link" value="<?php if(isset($current_options['created_by_link'])) { echo $current_options['created_by_link']; } ?>" >
			<span class="explain"><?php  _e('Enter the Call Out Button Link','quality');?></span>
		</div>
		
		<div id="button_section">
			<input type="hidden" value="1" id="webriti_settings_save_11" name="webriti_settings_save_11" />
			<input class="reset-button btn" type="button" name="reset" value="Restore Defaults" onclick="webriti_option_data_reset('11');">
			<input class="btn btn-primary" type="button" value="Save Options" onclick="webriti_option_data_save('11')" >
		</div>
	</form>
</div>