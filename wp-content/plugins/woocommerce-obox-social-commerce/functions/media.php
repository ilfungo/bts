<?php function get_oboxfb_image($width = 460, $height = '', $href_class = 'thumbnail', $wrap = '', $wrap_class = '', $hide_href = false){
	global $post;
	//Set iamge HTML to nothing
	$img_html = "";

	//Check whether we're actually going to display the thumbnail or not
	if(get_option("oboxfb_image_usage") == "off") :
		return false;
	elseif(!is_archive() && is_single() && get_option("oboxfb_image_usage") == "lists") :
		return false;
	elseif(!is_page() && !is_single() && get_option("oboxfb_image_usage") == "posts") :
		return false;
	endif;

	//Set up which meta value we're using for the post
	$meta = "other_media";
	// Thumbnail quality
	if(get_option("oboxfb_thumbnail_quality")) :
		$q = get_option("oboxfb_thumbnail_quality");
	else :
		$q = 70;
	endif;
	$get_thumbnail = get_post_meta($post->ID, $meta, true);

	//Custom post meta for video
	if(get_option("oboxfb_video_usage")) :
		$video = get_option("oboxfb_video_usage");
	else :
		$video = "main_video";
	endif;
	$get_post_video = get_post_meta($post->ID, $video, true);

	if ($get_post_video !== "") :
		$post_image = preg_replace("/(width\s*=\s*[\"\'])[0-9]+([\"\'])/i", "$1 100% \" wmode=\"transparent\"", $get_post_video);
		$post_image = preg_replace("/(height\s*=\s*[\"\'])[0-9]+([\"\'])/i", "$1 250$2", $post_image);
	//Begin the thumbnail check
	elseif ( function_exists("has_post_thumbnail") && has_post_thumbnail()) :
		if(has_post_thumbnail($post->ID)) :
			// Set the height to a huge number so that WP only sizes to the width
			if($height == "") : $height = 2000; endif;
			//Set the post Image Path
			$post_image = get_the_post_thumbnail($post->ID, array($width, $height));
		endif;
	elseif (get_option("oboxfb_use_timthumb") != "false" && $get_thumbnail != "") :
		$post_image = "<img src=\"".get_bloginfo('template_directory')."/functions/timthumb.php?q=$q&amp;src=$get_thumbnail&amp;w=$width&amp;h=$height&amp;zc=1&amp;a=".get_option("oboxfb_thumbnail_alignment")."\" alt=\"$post->post_title\" />";
	elseif ($get_thumbnail != "") :
		$post_image = "<img src=\"$get_thumbnail\" alt=\"$post->post_title\" />";
	else :
		//There is no image, lets quit
		return false;
	endif;

	//Create the image HTML with the link around it
	$link = get_permalink($post->ID);
	if($hide_href == false) :
		$img_html = "<a href=\"$link\" class=\"$href_class\">$post_image</a>";
	else :
		$img_html = $post_image;
	endif;

	//Class for the surrounding divs
	if($wrap_class != "") :
    	$class = " class=\"$wrap_class\"";
    endif;

	if($wrap != "") :
    	$img_html = "<$wrap".$class.">".$img_html."</$wrap>";
	else :
		$img_html;
	endif;
	return $img_html;
}
function oboxfb_setup_image_sizes() {
  add_image_size('620x300', 620, 300, true);
  add_image_size('620x465', 620, 465, true);
  add_image_size('130x130', 130, 130, true);
}
add_action( 'init', 'oboxfb_setup_image_sizes', 1 );