<div class="block ui-tabs-panel deactive" id="option-ui-id-4" >	
	<?php $current_options = get_option('quality_options');
	if(isset($_POST['webriti_settings_save_4']))
	{	
		if($_POST['webriti_settings_save_4'] == 1) 
		{
			if ( empty($_POST) || !wp_verify_nonce($_POST['webriti_gernalsetting_nonce_customization'],'webriti_customization_nonce_gernalsetting') )
			{  print 'Sorry, your nonce did not verify.';	exit; }
			else  
			{		
				$current_options['project_heading_one']=sanitize_text_field($_POST['project_heading_one']);
				$current_options['project_tagline']=sanitize_text_field($_POST['project_tagline']);
				
				$current_options['project_one_thumb']=sanitize_text_field($_POST['project_one_thumb']);
				$current_options['project_two_thumb']=sanitize_text_field($_POST['project_two_thumb']);
				$current_options['project_three_thumb']=sanitize_text_field($_POST['project_three_thumb']);
				$current_options['project_four_thumb']=sanitize_text_field($_POST['project_four_thumb']);
				
				$current_options['project_one_title']=sanitize_text_field($_POST['project_one_title']);
				$current_options['project_two_title']=sanitize_text_field($_POST['project_two_title']);
				$current_options['project_three_title']=sanitize_text_field($_POST['project_three_title']);
				$current_options['project_four_title']=sanitize_text_field($_POST['project_four_title']);
			    
				//project enabled setting	
                if(isset($_POST['home_projects_enabled']))
				{ echo $current_options['home_projects_enabled']="on"; }
				else 				
				{ echo $current_options['home_projects_enabled']="off"; }
				
				update_option('quality_options',$current_options);
			}
		}	
		if($_POST['webriti_settings_save_4'] == 2) 
		{	
			$project_img = QUALITY_TEMPLATE_DIR_URI .'/images/projects_thumb.png';
			$current_options['home_projects_enabled']="on";			
			
			$current_options['project_heading_one']="Featured Portfolio Projects";
			$current_options['project_tagline']="Maecenas sit amet tincidunt elit. Pellentesque habitant morbi tristique senectus et netus et Nulla facilisi.";
			
			$current_options['project_one_thumb']=$project_img;			
			$current_options['project_one_title']="Lorem Ipsum";
			
			
			$current_options['project_two_thumb']=$project_img;			
			$current_options['project_two_title']="Postao je popularan";
			
			
			$current_options['project_three_thumb']=$project_img;			
			$current_options['project_three_title']="kojekakve promjene s";
			
			
			$current_options['project_four_thumb']=$project_img;			
			$current_options['project_four_title']="kojekakve promjene s";
			
			
				
			update_option('quality_options',$current_options);
		}
	}  ?>
	<form method="post" id="webriti_theme_options_4">
		<div id="heading">
			<table style="width:100%;"><tr>
				<td><h2><?php _e('Projects Section Settings','quality');?></h2></td>
				<td style="width:30%;">
					<div class="webriti_settings_loding" id="webriti_loding_4_image"></div>
					<div class="webriti_settings_massage" id="webriti_settings_save_4_success" ><?php _e('Options data successfully Saved','quality');?></div>
					<div class="webriti_settings_massage" id="webriti_settings_save_4_reset" ><?php _e('Options data successfully reset','quality');?></div>
				</td>
				<td style="text-align:right;">
					<input class="reset-button btn" type="button" name="reset" value="Restore Defaults" onclick="webriti_option_data_reset('4');">
					<input class="btn btn-primary" type="button" value="Save Options" onclick="webriti_option_data_save('4')" >
				</td>
				</tr>
			</table>	
		</div>	
		
		<?php wp_nonce_field('webriti_customization_nonce_gernalsetting','webriti_gernalsetting_nonce_customization'); ?>
		<div class="section">
			<h3><?php _e('Enable Home Project Section','quality'); ?></h3>
			<input type="checkbox" <?php if($current_options['home_projects_enabled']=='on') echo "checked='checked'"; ?> id="home_projects_enabled" name="home_projects_enabled" > <span class="explain"><?php _e('Enable Projects section in fornt page.','quality'); ?></span>
		</div>
		<div class="section">
			<h3><?php _e('Home Project  Heading','quality'); ?></h3>
			<hr>
			<h3><?php _e('Project Section Heading','quality'); ?></h3>
			<input class="webriti_inpute" type="text" value="<?php if(isset($current_options['project_heading_one'])) { echo $current_options['project_heading_one']; } ?>" id="project_heading_one" name="project_heading_one" size="36" />
			<span class="icons help"><span class="tooltip"><?php  _e('Enter Project Section Heading','quality');?></span></span>
		
			<h3><?php _e('Project Section Tagline','quality'); ?><span class="icons help"><span class="tooltip"><?php  _e('Enter Project Thumbnail','quality');?></span></span></h3>
			<input class="webriti_inpute"  type="text" name="project_tagline" id="project_tagline" value="<?php if( isset($current_options['project_tagline'])) echo $current_options['project_tagline']; ?>" >
			<span class="icons help"><span class="tooltip"><?php  _e('Enter Project Section Tagline','quality');?></span></span>	
		</div>		
		<div class="section">
			<h3><?php _e('Home Project One','quality'); ?></h3>
			<hr>
			<h3><?php _e('Project One Title','quality'); ?></h3>
			<input class="webriti_inpute" type="text" value="<?php if(isset($current_options['project_one_title'])) { echo $current_options['project_one_title']; } ?>" id="project_one_title" name="project_one_title" size="36" />
			<span class="icons help"><span class="tooltip"><?php  _e('Enter Project Title','quality');?></span></span>
		
			<h3><?php _e('Project One Thumbnail','quality'); ?><span class="icons help"><span class="tooltip"><?php  _e('Enter Project Thumbnail','quality');?></span></span></h3>
			<input class="webriti_inpute"  type="text" name="project_one_thumb" id="project_one_thumb" value="<?php if( isset($current_options['project_one_thumb'])) echo $current_options['project_one_thumb']; ?>" >
			<input type="button" id="upload_button" value="Add Thumb One" class="upload_image_button" />			
			<?php if(isset($current_options['project_one_thumb'])) { ?>
			<p><img class="webriti_home_slide" src="<?php echo $current_options['project_one_thumb'];  ?>" /></p>
			<?php } ?>
			
		</div>	
		<div class="section">
			<h3><?php _e('Home Project Two','quality'); ?></h3>
			<hr>
			<h3><?php _e('Project Two Title','quality'); ?></h3>
			<input class="webriti_inpute" type="text" value="<?php if(isset($current_options['project_two_title'])) { echo $current_options['project_two_title']; } ?>" id="project_two_title" name="project_two_title" size="36" />
			<span class="icons help"><span class="tooltip"><?php  _e('Enter Project Title','quality');?></span></span>
		
			<h3><?php _e('Project Two Thumbnail','quality'); ?><span class="icons help"><span class="tooltip"><?php  _e('Enter Project Thumbnail','quality');?></span></span></h3>
			<input class="webriti_inpute"  type="text" name="project_two_thumb" id="project_two_thumb" value="<?php if( isset($current_options['project_two_thumb'])) echo $current_options['project_two_thumb']; ?>" >
			<input type="button" id="upload_button" value="Add Thumb Two" class="upload_image_button" />			
			<?php if(isset($current_options['project_two_thumb'])) { ?>
			<p><img class="webriti_home_slide" src="<?php echo $current_options['project_two_thumb'];  ?>" /></p>
			<?php } ?>
			
		</div>
		<div class="section">
			<h3><?php _e('Home Project Three','quality'); ?></h3>
			<hr>
			<h3><?php _e('Project Three Title','quality'); ?></h3>
			<input class="webriti_inpute" type="text" value="<?php if(isset($current_options['project_three_title'])) { echo $current_options['project_three_title']; } ?>" id="project_three_title" name="project_three_title" size="36" />
			<span class="icons help"><span class="tooltip"><?php  _e('Enter Project Title','quality');?></span></span>
		
			<h3><?php _e('Project Three Thumbnail','quality'); ?><span class="icons help"><span class="tooltip"><?php  _e('Enter Project Thumbnail','quality');?></span></span></h3>
			<input class="webriti_inpute"  type="text" name="project_three_thumb" id="project_three_thumb" value="<?php if( isset($current_options['project_three_thumb'])) echo $current_options['project_three_thumb']; ?>" >
			<input type="button" id="upload_button" value="Add Thumb Three" class="upload_image_button" />			
			<?php if(isset($current_options['project_three_thumb'])) { ?>
			<p><img class="webriti_home_slide" src="<?php echo $current_options['project_three_thumb'];  ?>" /></p>
			<?php } ?>
			
		</div>
		<div class="section">
			<h3><?php _e('Home Project Four','quality'); ?></h3>
			<hr>
			<h3><?php _e('Project Four Title','quality'); ?></h3>
			<input class="webriti_inpute" type="text" value="<?php if(isset($current_options['project_four_title'])) { echo $current_options['project_four_title']; } ?>" id="project_four_title" name="project_four_title" size="36" />
			<span class="icons help"><span class="tooltip"><?php  _e('Enter Project Title','quality');?></span></span>
		
			<h3><?php _e('Project Four Thumbnail','quality'); ?><span class="icons help"><span class="tooltip"><?php  _e('Enter Project Thumbnail','quality');?></span></span></h3>
			<input class="webriti_inpute"  type="text" name="project_four_thumb" id="project_four_thumb" value="<?php if( isset($current_options['project_four_thumb'])) echo $current_options['project_four_thumb']; ?>" >
			<input type="button" id="upload_button" value="Add Thumb Four" class="upload_image_button" />			
			<?php if(isset($current_options['project_four_thumb'])) { ?>
			<p><img class="webriti_home_slide" src="<?php echo $current_options['project_four_thumb'];  ?>" /></p>
			<?php } ?>
			
		</div>				
		<div id="button_section">
			<input type="hidden" value="1" id="webriti_settings_save_4" name="webriti_settings_save_4" />
			<input class="reset-button btn" type="button" name="reset" value="Restore Defaults" onclick="webriti_option_data_reset('4');">
			<input class="btn btn-primary" type="button" value="Save Options" onclick="webriti_option_data_save('4')" >
		</div>
	</form>
</div>