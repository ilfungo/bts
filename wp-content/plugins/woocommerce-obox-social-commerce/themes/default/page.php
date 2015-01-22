<?php get_header(); ?>

<div id="col2">

	<?php if (have_posts()) :
		global $post;
		while (have_posts()) : the_post(); setup_postdata($post);
			get_template_part("/functions/fetch-post");
		endwhile;
	else :
		ocmx_no_posts();
	endif; ?>

</div>

<?php get_footer(); ?>