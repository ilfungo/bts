<?php function oboxfb_ajax_upload(){
	$input_name = $_POST["input_name"];
	while (list($key,$value) = each($_FILES)){
		$key = str_replace("_file", "", $input_name);
		//Upload Image
		$upload = wp_upload_bits($_FILES[$input_name]['name'], null, file_get_contents($_FILES[$input_name]['tmp_name']));
		
		//Add Image to our Image Library
		$meta_key = $_POST["meta_key"];
		
		oboxfb_add_attachment($upload, $meta_key);
		
		//Update Option
		update_option($key, $upload["url"]);
		die($upload["url"]);
	}
}

function oboxfb_add_attachment($upload, $meta_key)
	{	
		//Using method explained in http://codex.wordpress.org/Function_Reference/wp_insert_attachment
		global $pID;
		
		$filename = $upload["file"];
		
		$wp_filetype = wp_check_filetype(basename($filename), null );
		
		$attachment = array('post_mime_type' => $wp_filetype['type'],'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),'post_content' => '','post_status' => 'inherit');
		
		$latest_post_id = get_posts("sort_order=DESC&sort_column=ID&number=1&type=any");
		if($is_logo == 0) :
			$new_id = ($latest_post_id[0]->ID+1);
		else :
			$new_id = 0;
		endif;
		
		$attach_id = wp_insert_attachment( $attachment, $filename, $new_id);
		
		if($is_logo !== 0) :
			$newmeta = array("obox-".$meta_key => 1);
			$update_logo = add_post_meta($attach_id, "obox-".$meta_key, 1);
		endif;
	}