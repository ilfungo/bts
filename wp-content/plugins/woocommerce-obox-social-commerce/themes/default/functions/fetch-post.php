<?php $link = get_permalink($post->ID);
$image = get_oboxfb_image(620, '', '', 'div', 'post-image');
?>
<div class="post-content clearfix">

	<?php if( !is_home() ) { oboxfb_breadcrumbs( 'crumbs' ); } ?>

	<h2 class="post-title"><?php the_title(); ?></h2>
	<div class="embed-container">
		<?php echo $image ?>
	</div>


	<div class="copy clearfix">
		 <?php the_content(""); ?>
	</div>

	<?php if(!is_page()): ?>
		<ul class="next-prev-post-nav">
			<li>
				<?php if (get_adjacent_post(false, '', true)): // if there are older posts ?>
					&larr;  <span><?php previous_post_link("%link", "%title"); ?></span>
				<?php else : ?>
					&nbsp;
				<?php endif; ?>
			</li>
			<li>
				<?php if (get_adjacent_post(false, '', false)): // if there are newer posts ?>
					<span><?php next_post_link("%link", "%title"); ?></span> &rarr;
				<?php else : ?>
					&nbsp;
				<?php endif; ?>
			</li>
		</ul>
	<?php endif; ?>
</div>
