<?php function oboxfb_video_info($url) {
	
	// Handle Youtube
	if (strpos($url, "youtube.com")) :
	    $url = parse_url($url);
	    $vid = parse_str($url['query'], $output);
	    $video_id = $output['v'];
	    $data['video_type'] = 'youtube';
	    $data['video_id'] = $video_id;
	    $xml = simplexml_load_file("http://gdata.youtube.com/feeds/api/videos?q=$video_id");
	
	    foreach ($xml->entry as $entry) :
	        // get nodes in media: namespace
	        $media = $entry->children('http://search.yahoo.com/mrss/');
	        
	        // get video player URL
	        $attrs = $media->group->player->attributes();
	        $watch = $attrs['url']; 
	        
	        // get video thumbnail
	        $data['thumb_1'] = $media->group->thumbnail[0]->attributes(); // Thumbnail 1
	        $data['thumb_2'] = $media->group->thumbnail[1]->attributes(); // Thumbnail 2
	        $data['thumb_3'] = $media->group->thumbnail[2]->attributes(); // Thumbnail 3
	        $data['thumb_large'] = $media->group->thumbnail[0]->attributes(); // Large thumbnail
	        $data['tags'] = $media->group->keywords; // Video Tags
	        $data['cat'] = $media->group->category; // Video category
	        $attrs = $media->group->thumbnail[0]->attributes();
	        $thumbnail = $attrs['url']; 
	        
	        // get <yt:duration> node for video length
	        $yt = $media->children('http://gdata.youtube.com/schemas/2007');
	        $attrs = $yt->duration->attributes();
	        $data['duration'] = $attrs['seconds'];
	        
	        // get <yt:stats> node for viewer statistics
	        $yt = $entry->children('http://gdata.youtube.com/schemas/2007');
	        $attrs = $yt->statistics->attributes();
	        $data['views'] = $viewCount = $attrs['viewCount']; 
	        $data['title']=$entry->title;
	        $data['info']=$entry->content;
	        
	        // get <gd:rating> node for video ratings
	        $gd = $entry->children('http://schemas.google.com/g/2005'); 
	        if ($gd->rating) {
	            $attrs = $gd->rating->attributes();
	            $data['rating'] = $attrs['average']; 
	        } else { $data['rating'] = 0;}
	    endforeach;
	// Handle Vimeo
	elseif (strpos($url, "vimeo.com")) :
	    $video_id=explode('vimeo.com/', $url);
	    $video_id=$video_id[1];
	    $data['video_type'] = 'vimeo';
	    $data['video_id'] = $video_id;
	    $xml = simplexml_load_file("http://vimeo.com/api/v2/video/$video_id.xml");
	        
	    foreach ($xml->video as $video) :
	        $data['id']=$video->id;
	        $data['title']=$video->title;
	        $data['info']=$video->description;
	        $data['url']=$video->url;
	        $data['upload_date']=$video->upload_date;
	        $data['mobile_url']=$video->mobile_url;
	        $data['thumb_small']=$video->thumbnail_small;
	        $data['thumb_medium']=$video->thumbnail_medium;
	        $data['thumb_large']=$video->thumbnail_large;
	        $data['user_name']=$video->user_name;
	        $data['urer_url']=$video->urer_url;
	        $data['user_thumb_small']=$video->user_portrait_small;
	        $data['user_thumb_medium']=$video->user_portrait_medium;
	        $data['user_thumb_large']=$video->user_portrait_large;
	        $data['user_thumb_huge']=$video->user_portrait_huge;
	        $data['likes']=$video->stats_number_of_likes;
	        $data['views']=$video->stats_number_of_plays;
	        $data['comments']=$video->stats_number_of_comments;
	        $data['duration']=$video->duration;
	        $data['width']=$video->width;
	        $data['height']=$video->height;
	        $data['tags']=$video->tags;
	    endforeach;
	// Set false if invalid URL
	else :
		$data = false;
	endif;
	return $data;

}