<div class="block ui-tabs-panel deactive" id="option-ui-id-5" >	
	<?php $current_options = get_option('quality_options');
	if(isset($_POST['webriti_settings_save_5']))
	{	
		if($_POST['webriti_settings_save_5'] == 1) 
		{
			if ( empty($_POST) || !wp_verify_nonce($_POST['webriti_gernalsetting_nonce_customization'],'webriti_customization_nonce_gernalsetting') )
			{  print 'Sorry, your nonce did not verify.';	exit; }
			else  
			{		
				
				$current_options['blog_heading']=sanitize_text_field($_POST['blog_heading']);
				//blog enabled setting	
                if(isset($_POST['home_blog_enabled']))
				{ echo $current_options['home_blog_enabled']="on"; 
				} 
                 else 
                { echo $current_options['home_blog_enabled']="off";
                }				 
				
				update_option('quality_options',$current_options);
			}
		}	
		if($_POST['webriti_settings_save_5'] == 2) 
		{	
			
			$current_options['home_blog_enabled']="on";			
			
			$current_options['blog_heading']="Latest <span>From</span> Blog";
			
			update_option('quality_options',$current_options);
		}
	}  ?>
	<form method="post" id="webriti_theme_options_5">
		<div id="heading">
			<table style="width:100%;"><tr>
				<td><h2><?php _e('Blog  Settings','quality');?></h2></td>
				<td style="width:30%;">
					<div class="webriti_settings_loding" id="webriti_loding_5_image"></div>
					<div class="webriti_settings_massage" id="webriti_settings_save_5_success" ><?php _e('Options data successfully Saved','quality');?></div>
					<div class="webriti_settings_massage" id="webriti_settings_save_5_reset" ><?php _e('Options data successfully reset','quality');?></div>
				</td>
				<td style="text-align:right;">
					<input class="reset-button btn" type="button" name="reset" value="Restore Defaults" onclick="webriti_option_data_reset('5');">
					<input class="btn btn-primary" type="button" value="Save Options" onclick="webriti_option_data_save('5')" >
				</td>
				</tr>
			</table>	
		</div>	
		
		<?php wp_nonce_field('webriti_customization_nonce_gernalsetting','webriti_gernalsetting_nonce_customization'); ?>
		<div class="section">
			<h3><?php _e('Enable Blog on HOME Section','quality'); ?></h3>
			<input type="checkbox" <?php if($current_options['home_blog_enabled']=='on') echo "checked='checked'"; ?> id="home_blog_enabled" name="home_blog_enabled" > <span class="explain"><?php _e('Enable Blog section in front page.','quality'); ?></span>
			<h3><?php _e('Blog Heading','quality'); ?></h3>
			<input class="webriti_inpute" type="text" value="<?php if(isset($current_options['blog_heading'])) { echo $current_options['blog_heading']; } ?>" id="blog_heading" name="blog_heading" size="36" />
			<span class="icons help"><span class="tooltip"><?php  _e('Blog Section Heading','quality');?></span></span>
		
		</div>						
		<div id="button_section">
			<input type="hidden" value="1" id="webriti_settings_save_5" name="webriti_settings_save_5" />
			<input class="reset-button btn" type="button" name="reset" value="Restore Defaults" onclick="webriti_option_data_reset('5');">
			<input class="btn btn-primary" type="button" value="Save Options" onclick="webriti_option_data_save('5')" >
		</div>
	</form>
</div>