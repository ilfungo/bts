<div class="block ui-tabs-panel deactive" id="option-ui-id-2" >
<?php $current_options = get_option('quality_options');
	if(isset($_POST['webriti_settings_save_2']))
	{	
		if($_POST['webriti_settings_save_2'] == 1) 
		{
			if ( empty($_POST) || !wp_verify_nonce($_POST['webriti_gernalsetting_nonce_customization'],'webriti_customization_nonce_gernalsetting') )
			{  print 'Sorry, your nonce did not verify.';	exit; }
			else  
			{	
				$current_options['home_feature']=sanitize_text_field($_POST['home_feature']);				
				$current_options['home_image_title']=sanitize_text_field($_POST['home_image_title']);
				$current_options['home_image_sub_title']=sanitize_text_field($_POST['home_image_sub_title']);
				$current_options['home_image_description']=sanitize_text_field($_POST['home_image_description']);
				
				update_option('quality_options',$current_options);
			}
		}	
		if($_POST['webriti_settings_save_2'] == 2) 
		{
			
			$current_options['home_feature']= QUALITY_TEMPLATE_DIR_URI .'/images/slider/slide.jpg';				
			$current_options['home_image_title']="Theme Feature Goes Here!";
			$current_options['home_image_sub_title']="Wordpress Premium Theme";
			$current_options['home_image_description']='Fully Responsive Theme Amazing Design.';
			
			update_option('quality_options',$current_options);
		}
	}  ?>
	<form method="post" id="webriti_theme_options_2">
		<div id="heading">
			<table style="width:100%;"><tr>
				<td><h2><?php _e('Quick Start Settings','quality');?></h2></td>
				<td style="width:30%;">
					<div class="webriti_settings_loding" id="webriti_loding_2_image"></div>
					<div class="webriti_settings_massage" id="webriti_settings_save_2_success" ><?php _e('Options data successfully Saved','quality');?></div>
					<div class="webriti_settings_massage" id="webriti_settings_save_2_reset" ><?php _e('Options data successfully reset','quality');?></div>
				</td>
				<td style="text-align:right;">					
					<input class="reset-button btn" type="button" name="reset" value="Restore Defaults" onclick="webriti_option_data_reset('2');">
					<input class="btn btn-primary" type="button" value="Save Options" onclick="webriti_option_data_save('2')" >
				</td>
				</tr>
			</table>			
		</div>	
		<?php wp_nonce_field('webriti_customization_nonce_gernalsetting','webriti_gernalsetting_nonce_customization'); ?>
		
		<div class="section">
			<h3><?php _e('Home Feature Image','quality'); ?>
				<span class="icons help"><span class="tooltip"><?php  _e('Add your Home Feaure Image here','quality');?></span></span>
			</h3>
			<input class="webriti_inpute" type="text" value="<?php if($current_options['home_feature']!='') { echo esc_attr($current_options['home_feature']); } ?>" id="home_feature" name="home_feature" size="36" class="upload has-file"/>
			<input type="button" id="upload_button" value="Custom Image" class="upload_image_button" />
			
			<?php if($current_options['home_feature']!='') { ?>
			<p><img style="height:250px;width:1100px;" src="<?php if($current_options['home_feature']!='') { echo esc_attr($current_options['home_feature']); } ?>" /></p>
			<?php } ?>
		</div>
			<div class="section">
				<h3><?php _e('Banner Title','quality'); ?></h3>
				<input class="webriti_inpute"  type="text" name="home_image_title" id="home_image_title" value="<?php if( isset($current_options['home_image_title'])) echo $current_options['home_image_title']; ?>" >
				<span class="icons help"><span class="tooltip"><?php  _e('Enter Banner title','quality');?></span></span>
			</div>
			<div class="section">
				<h3><?php _e('Banner Sub Title','quality'); ?></h3>
				<input class="webriti_inpute"  type="text" name="home_image_sub_title" id="home_image_sub_title" value="<?php if( isset($current_options['home_image_sub_title'])) echo $current_options['home_image_sub_title']; ?>" >
				<span class="icons help"><span class="tooltip"><?php  _e('Enter Banner Sub Title','quality');?></span></span>
			</div>
			<div class="section">
				<h3><?php _e('Banner Description','quality'); ?></h3>
				<textarea rows="5" cols="8" id="home_image_description" name="home_image_description"  class="textbox1" ><?php if(isset($current_options['home_image_description'])) { echo esc_attr($current_options['home_image_description']); } ?></textarea>
				<div class=""><?php _e('Enter Banner description text less then 150 character .','quality'); ?><br></div>
			</div>		
		<div id="button_section">
			<input type="hidden" value="1" id="webriti_settings_save_2" name="webriti_settings_save_2" />
			<input class="reset-button btn" type="button" name="reset" value="Restore Defaults" onclick="webriti_option_data_reset('2');">
			<input class="btn btn-primary" type="button" value="Save Options" onclick="webriti_option_data_save('2')" >
			<!--  alert massage when data saved and reset -->
		</div>
	</form>	
</div>