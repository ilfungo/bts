<?php function oboxfb_social_links(){
	global $post;
	$link = get_permalink($post->ID);
	if(get_option("oboxfb_social_link_usage") != "off") : ?>
	<ul class="post-meta clearfix">
        <li class="social-links">
            <?php if(get_option("oboxfb_facebook") != "false") : ?>
                <div class="social-facebook">
                    <iframe src="//www.facebook.com/plugins/like.php?href=<?php echo $link; ?>&amp;layout=button_count&amp;show_faces=true&amp;width=50&amp;action=like&amp;colorscheme=light&amp;height=21" style="border: medium none; overflow: hidden; width: 50px; height: 21px;" allowtransparency="true" frameborder="0" scrolling="no"></iframe>
                </div>
            <?php endif; ?>
            <?php if(get_option("oboxfb_twitter") != "false") : ?>
                <div class="social-twitter">
                    <a href="http://twitter.com/share" class="twitter-share-button" data-count="none" data-url="<?php the_permalink(); ?>" data-text="<?php the_title()?>">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
                </div>
            <?php endif; ?>
            <?php if(get_option("oboxfb_googleplus") != "false") : ?>
                <div class="social-google">
                    <!-- Place this tag where you want the +1 button to render -->
                    <g:plusone size="medium" count="false" href="<?php echo $link; ?>"></g:plusone>
                    
                    <!-- Place this tag after the last plusone tag -->
                    <script type="text/javascript">
                      (function() { 
                        var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
                        po.src = 'https://apis.google.com/js/plusone.js';
                        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
                      })();
                    </script>
                </div>
            <?php endif; ?>
        </li>
	</ul>
<?php endif;
};

add_action("oboxfb_social_links", "oboxfb_social_links"); 

function oboxfb_post_meta($wrap = "h5"){ 
	global $post;
	if(get_option("oboxfb_post_meta") == "off") :
		return false;
	elseif((is_single() || is_archive()) && get_option("oboxfb_post_meta") == "pages") :
		return false;
	elseif(is_page() && get_option("oboxfb_post_meta") == "posts") :
		return false;
	endif;?>
	<<?php echo $wrap; ?> class="date">
		<?php if(get_option("oboxfb_post_date") != "false") :  
			echo date("F j, Y", strtotime($post->post_date));
			$hasdate = 1; 
		endif;?>
        <?php if(get_option("oboxfb_post_author") != "false") : ?>  
			<?php if(isset($hasdate)) :
				_e("by", "ocmx");
			else :
				_e("By", "ocmx");
			endif; ?> <a href="<?php the_author_meta('url'); ?>"><?php the_author(); ?></a> 
        <?php endif;?>
		<?php if(!is_page()) : 
			if(get_option("oboxfb_post_tags") != "false") :?>
				<?php the_tags(_("Tagged: ", "ocmx"),', ');
			endif;
			if(get_option("oboxfb_post_categories") != "false") :
            	_e("Posted In", "ocmx"); ?> <?php the_category(', ');
			endif;
		endif; ?>
	</<?php echo $wrap; ?>>
    
<?php 
	}
add_action("oboxfb_post_meta", "oboxfb_post_meta");

function oboxfb_author_bio(){ 
	if(get_option("oboxfb_author_display") == "off") :
		return false;
	elseif((is_single() || is_archive()) && get_option("oboxfb_author_display") == "pages") :
		return false;
	elseif(is_page() && get_option("oboxfb_author_display") == "posts") :
		return false;
	endif;?>
	<ul class="comment-container author">
		<li class="comment clearfix">
            <div class="comment-post no-margin">
                <a href="#" class="comment-avatar"><?php echo get_avatar( get_the_author_meta('email'), "45" ); ?></a>
                <h4 class="comment-name"><?php the_author_meta('nickname'); ?></h4>
                <p><?php the_author_meta('description'); ?></p>
			</div>
	    </li>
    </ul>        
<?php 
}
add_action("oboxfb_author_bio", "oboxfb_author_bio"); ?>