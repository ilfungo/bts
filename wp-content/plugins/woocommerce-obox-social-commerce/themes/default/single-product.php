<?php get_header();
$link = get_permalink($post->ID);
?>

	<div id="col2">

		<?php if ( have_posts() ) while ( have_posts() ) : the_post(); global $post, $product; ?>

			<div class="post-content clearfix">

				<?php if( !is_home() ) { oboxfb_breadcrumbs( 'crumbs' ); } ?>

				<?php do_action('woocommerce_before_single_product', $post, $product); ?>
				<h2 class="post-title"><?php the_title(); ?></h2>
				<div class="product-left">
					<?php do_action( 'woocommerce_before_single_product_summary', $post, $product ); ?>
				</div>

				<div class="product-content">
					<div class="product-price">
						<?php do_action( 'woocommerce_single_product_summary', $post, $product ); ?>
					</div>
				<?php if(get_option('oboxfb_social_tag') !="") : echo get_option('oboxfb_social_tag');
				elseif( get_option('oboxfb_social_meta') !="false" ) : ?>
					<ul class="social-links">
						<li class="addthis">
							<!-- AddThis Button BEGIN -->
							<div class="addthis_toolbox addthis_default_style ">
								<a class="addthis_button_facebook_like"></a>
								<a class="addthis_button_tweet"></a>
								<a class="addthis_button_google_plusone" g:plusone:size="medium"></a>
								<a class="addthis_counter addthis_pill_style"></a>
							</div>
							<script type="text/javascript" src="http://s7.addthis.com/js/300/addthis_widget.js#pubid=xa-507462e4620a0fff"></script>
							<!-- AddThis Button END -->
						</li>
					</ul>
				<?php endif; ?>
				</div>

				<div class="clearfix"></div>

				<div itemscope itemtype="http://schema.org/Product" id="product-<?php the_ID(); ?>" <?php post_class(); ?>>
					<div class="woocommerce_tabs clearfix">
						<?php do_action( 'woocommerce_after_single_product_summary', $post, isset($product) ); ?>
					</div>
				</div>

			</div>

		<?php endwhile; ?>

	</div>

<?php get_footer(); ?>