<?php get_header(); ?>

<div id="col2">
	<ul class="posts">
		<?php if (have_posts()) :
	        global $post;
	        while (have_posts()) : the_post(); setup_postdata($post);
	            get_template_part("/functions/fetch-list");
	        endwhile;
	    else :
	        ocmx_no_posts();
	    endif; ?> 
    </ul>
</div>

<?php get_footer(); ?>