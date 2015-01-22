<?php $link = get_permalink($post->ID);
$image = get_oboxfb_image(620, '380', '', 'div', 'post-image'); ?>
<li class="post">		
    <h2 class="post-title"><a href="<?php echo $link; ?>"><?php the_title(); ?></a></h2>
	<div class="post-image">
    	<?php echo $image ?>
    </div>
	<div class="post-meta clearfix">
		<p><?php echo date_i18n('d F Y', strtotime($post->post_date)); ?>, <?php _e("written by","ocmx"); ?> <?php the_author_posts_link(); ?></p>
	</div>
    <div class="copy clearfix">
        <?php if($post->post_excerpt !== "") :
            the_excerpt();
        else :
            the_content("");
        endif; ?>
        <p><a href="<?php echo $link; ?>" class="action-link"><?php _e("Continue Reading &rarr;", "ocmx"); ?></a></p>
    </div>    
</li>                        
