<?php function create_oboxfb_form($input, $counter, $label_class = "") { ?>
	<?php if($label_class != "") : ?>
    	<div class="<?php echo $label_class; ?>">
	<?php endif; ?>
		<?php // Set the input value to default or get_option()
		if(!get_option($input["name"])) :
			$input_value = $input["default"];
		else :
			$input_value = get_option($input["name"]);
		endif;	
		// This denotes that we're using the wp-categories instead of set options
		if(isset($input["options"]) === "loop_categories" || isset($input["options"]) === "multi_categories") :
			$category_args = array('hide_empty' => false);
			$option_loop = get_categories($category_args);
		elseif(isset($input["options"]) === "loop_pages") :
			$option_loop = get_pages();
		elseif(isset($input["options"]) === "loop_galleries") :
			$option_loop = list_ocmx_galleries();
		elseif(isset($input["options"])) :
			//$values =  array_values($input["options"]);	
			$option_loop = $input['options'];
		else :
			//$values =  array_values($input["options"]);	
			$option_loop = array();
		endif;
		
		//Switch through the input_type
		switch($input["input_type"]) :
			case 'select';
				if(isset($input['linked'])) : ?>
					<select size="1" name="<?php echo $input["name"]; ?>" id="<?php echo $input["id"]; ?>" onchange="javacript: check_linked('<?php echo $input['id'];?>', '<?php echo $input['linked'];?>')">
				<?php else : ?>
					<select size="1" name="<?php echo $input["name"]; ?>" id="<?php echo $input["id"]; ?>" rel="<?php echo $input["prefix"]; ?>">
				<?php endif;
					// Tiny little hack.. if we've set the options to loop through the categories, we must have an "All" option
					if ($input["options"] == "loop_galleries") : ?>
						<option <?php if($input_value == 0){echo "selected=\"selected\"";} ?> value="0"><?php if($input["zero_wording"]) : echo $input["zero_wording"]; else : _e("All","ocmx"); endif; ?></option>
				<?php elseif ($input["options"] == "loop_categories") : ?>
						<option <?php if($input_value == 0){echo "selected=\"selected\"";} ?> value="0"><?php if($input["zero_wording"]) : echo $input["zero_wording"]; else : _e("All","ocmx"); endif; ?></option>
				<?php elseif($input["options"] == "loop_pages" && ($input["linked"])) : ?>
						<option <?php if($input_value == 0){echo "selected=\"selected\"";} ?> value="0"><?php if($input["zero_wording"]) : echo $input["zero_wording"]; else : _e("Use a Custom Description","ocmx"); endif; ?></option>
				<?php elseif($input["options"] == "loop_pages") : ?>
						<option <?php if($input_value == 0){echo "selected=\"selected\"";} ?> value="0"><?php if($input["zero_wording"]) : echo $input["zero_wording"]; else : _e("None","ocmx"); endif; ?></option>
				<?php endif;
					foreach($option_loop as $option_label => $value) :
						// Set the $value and $label for the options
						if($input["options"] == "loop_categories") :
							$use_value =  $value->slug;
							$label =  $value->cat_name;
						elseif($input["options"] == "loop_pages") : 
							$use_value =  $value->ID;
							$label =  $value->post_title;
						elseif ($input["options"] == "loop_galleries") :
							$use_value =  $value->menuId;
							$label =  $value->GalleryTitle;
						else :		
							$use_value  =  $value;
							$label =  $option_label;
						endif;
						
						//If this option == the value we set above, select it
						if($use_value == $input_value) :
							$selected = " selected='selected' ";
						else :
							$selected = " ";
						endif; ?>
						<option value="<?php echo $use_value; ?>" <?php echo $selected; ?>><?php echo $label; ?></option>
					<?php endforeach;  ?>
				</select>
			<?php  break; case 'checkbox' : ?>
				<?php if(isset($option_loop) && is_array($option_loop)): ?>
                    <ul class="form-options contained-forms">`
                        <?php foreach($option_loop as $option_label => $value) :
                            // Set the $value and $label for the options
                            if($input["options"] == "loop_categories" || $input["options"] == "multi_categories") : 
                                $use_value =  $value->slug;
                                $label =  $value->cat_name;
                            elseif($input["options"] == "loop_pages") : 
                                $use_value =  $value->ID;
                                $label =  $value->post_title;
                            else :	
                                $use_value  =  $value;
                                $label =  $option_label;
                            endif;
                            if($use_value == $input_value):
                                $selected = " checked='checked' ";
                            else :
                                $selected = " ";
                            endif;
                            ?>
                            <li><input type="checkbox" name="<?php echo $input["name"]."_".$counter; ?>" value="<?php echo $use_value; ?>" /> <?php echo $label; ?></li>
                        <?php endforeach; ?>
                    </ul>
				<?php else : ?>             
                    <input type="checkbox" name="<?php echo $input["name"]; ?>" <?php if($input_value == "true") : ?>checked="checked"<?php endif; ?>  /> <?php if(isset($label)) : echo $label; endif; ?>
                <?php endif;?>
			<?php  break; case 'radio' :?>
				<ul class="form-options contained-forms">
					<?php foreach($option_loop as $option_label => $value) :
                        // Check whether we must loop through the categories for the options
                        if($input["options"] == "loop_categories") : 
                            $use_value =  $value->slug;
                            $label =  $value->cat_name;
                        else :		
                            $use_value  =  $value;
                            $label =  $option_label;
                        endif;
                        if($use_value == $input_value) :
                            $selected = " selected='selected' ";
                        else :
                            $selected = " ";
                        endif; ?>
                        <li><input type="radio" name="<?php echo $input["name"]; ?>" value="<?php echo $use_value; ?>" />&nbsp;<?php echo $option_label; ?> </li>
                    <?php endforeach; ?>
                </ul>
			<?php break; case 'memo':
				if(isset($input['linked'])) :
					if((get_option($input['linked'])) && get_option($input['linked']) !== "0") :
						$disabled_element = "disabled=\"disabled\"";
					else :
						$disabled_element = "";
					endif;
				else :
					$disabled_element = "";
				endif; ?>
				<textarea name="<?php echo $input["name"]; ?>" id="<?php echo $input["id"]; ?>" <?php echo $disabled_element; ?> class="site-tracking"><?php echo stripslashes($input_value); ?></textarea>
			<?php	break; case 'file': ?>
				
				
                <div class="logo-display">
					<a href="<?php echo $input_value; ?>" class="std_link" rel="lightbox" target="_blank" id="<?php echo $input["id"]; ?>_href" style="background: url('<?php echo $input_value; ?>') no-repeat center;"></a>
				</div>
	                
                <div class="file">
                	<?php if( isset($meta_key) && $meta_key != '' ) : ?>
	                    <label><?php ucfirst($meta_key); ?></label>
                    <?php endif; ?>
                    <input type="text"  name="<?php echo $input["name"]; ?>" id="<?php echo $input["id"]; ?>_text" value="<?php echo $input_value; ?>" />            	                 
                    <input type="button"name="<?php echo $input["name"]; ?>_file" <?php if(isset($input["id"])) : ?>id="<?php echo $input["id"]; ?>"<?php endif; ?> value="<?php _e("Browse","ocmx"); ?>" class="button" />              
                    <input type="button" id="clear_<?php echo $input["id"]; ?>" value="<?php _e("Clear","ocmx"); ?>" class="button" />
					<span class="tooltip"><?php _e("Your image".(isset($meta_key))." will not be automatically resized.","ocmx"); ?></span>
                </div>
                
                
                <div  class="previous-logos clearfix">
					<?php $args = array("post_type" => "attachment", "meta_key" => "obox-".$input["sub_title"], "meta_value" => 1, "showposts" => -1);
                        $attachments = $attachments = get_posts($args);
                    /*if ($attachments) : ?><h4><?php _e("Previously uploaded ".$input["sub_title"]."'s","ocmx"); ?></h4><?php endif; */?>                
                    <ul>
                        <?php if ($attachments) :
                            foreach ($attachments as $post) : 
                                $attach_data = get_post_meta($post->ID, "obox-".$input["sub_title"]);
                                $attachment_src = wp_get_attachment_image_src($post->ID); ?>
                                <li>
                                    <a href="" class="image" id="<?php echo $post->ID; ?>">
                                        <img src="<?php echo $attachment_src[0]; ?>" />                            	
                                    </a>
                                    <a href="" class="remove">Delete</a>
                                </li>
                        <?php  endforeach; 
                        endif;?>
                        <li id="new-upload-<?php echo $input["sub_title"]; ?>" style="display: none;">
                            <a href="" class="image"></a>
                        </li>
                    </ul>
				</div>
			<?php break; case 'button': ?>
				<input type="button" name="<?php echo $input["name"]; ?>" id="<?php echo $input["id"]; ?>" value="<?php echo $input_value; ?>" class="button-primary" />
				
			<?php break; case 'html':
				if(isset($input["html"])) :
					echo $input["html"];
				endif;
			break; case 'hidden': ?>
				<input type="hidden" name="<?php echo $input["name"]; ?>" id="<?php echo $input["id"]; ?>" value="<?php echo $input_value; ?>" />
			<?php  break; default :
				if(isset($input['linked'])) :
					if((get_option($input['linked'])) && get_option($input['linked']) != "0") :
						$disabled_element = "disabled=\"disabled\"";
					else :
						$disabled_element = "";
					endif;
				else :
					$disabled_element = "";
				endif; ?>
				<input type="text" name="<?php echo $input["name"]; ?>" id="<?php echo $input["id"]; ?>" value="<?php echo $input_value; ?>" <?php echo $disabled_element; ?> />
			<?php  if(isset($input["html"])) :
					echo $input["html"];
				endif;
			break;
		endswitch; ?>
	<?php if($label_class != "") : ?>
		</div>
	<?php endif; ?>
<?php } ?>