<?php function oboxfb_advert($advert){
	if(get_option($advert."_ad_non_users") == "true" && is_user_logged_in())
		return false;
		
	if(get_option($advert."_ad_postst_only") == "true" && !is_page() && !is_single())
		return false;
		
	if(get_option($advert."_ad_buysell_id") != "") : ?>
		<div class="post-advert"><?php echo stripslashes(get_option($advert."_ad_buysell_id")); ?></div>
	<?php elseif(get_option($advert."_ad_image") != "") : ?>
		<div class="post-advert">
			<a href="<?php echo get_option($advert."_ad_href"); ?>" target="_blank" rel="nofollow" class="post-advert">
				<img src="<?php echo get_option($advert."_ad_image"); ?>" alt="<?php echo get_option($advert."_ad_title"); ?>" />
			</a>
		</div>
	<?php endif;
} ?>