<?php get_header(); ?>

		<div id="col2">

			<?php if(get_option("oboxfb_slider") == 'yes' ) :
				$post_count = get_option("oboxfb_slider_count");
				$ocmx_posts = new WP_Query("posts_per_page=" . $post_count . "&post_type=social-slider&orderby=menu_order&order=ASC");
				$count = 1; ?>
				<div id="slider-container">
					<div class="oboxfb-slider clearfix <?php if( '620x465' == get_option( 'oboxfb_slider_dimensions' ) ) { echo 'tall'; } ?>">
						<ul class="gallery-container gallery-image">
							<?php while ($ocmx_posts->have_posts()) : $ocmx_posts->the_post();
								$link = get_post_meta($ocmx_posts->post->ID, 'obox_slider', TRUE);
								if( '' != $link) {
									$slider_link = $link['obox_imageurl'];
								}  else {
									$slider_link = '#';
								}

								if( get_option( 'oboxfb_slider_dimensions' ) != '' ) {
									$slider_dimensions = get_option( 'oboxfb_slider_dimensions' );
								} else {
									$slider_dimensions = '620x300';
								}
								$image = get_the_post_thumbnail( $post->ID , $slider_dimensions ); ?>
								<li>
									<a href="<?php if($slider_link !="") : echo $slider_link; else : the_permalink($post->ID); endif; ?>">
										<?php echo $image; ?>
									</a>
								</li>
							<?php
								$count++;
							endwhile; ?>
						</ul>
						<a href="#" class="next"><?php _e("Next", "ocmx"); ?></a>
						<a href="#" class="previous"><?php _e("Previous", "ocmx"); ?></a>
						<div class="slider-dots">
							<?php for($i=1; $i < $count; $i++) : ?>
								<a href="#" rel="<?php echo ($i-1); ?>" class="dot <?php if($i == 1) : ?>dot-selected<?php endif; ?>"><?php echo $i; ?></a>
							<?php endfor; ?>
						</div>
						<div id="slider-number" class="no_display"><?php echo get_option("oboxfb_slider_count"); ?></div>
						<div id="slider-auto" class="no_display"><?php echo get_option("oboxfb_slider_duration"); ?></div>
					</div>
				</div>
			<?php endif;
				// Refresh post data
				wp_reset_postdata();
			?>

			 <?php if(get_option("oboxfb_intro") == 'yes' ) : ?>
				<div class="intro-text">
					<?php if(get_option("oboxfb_intro_title")) : ?>
						<h3 class="widgettitle"><?php echo get_option("oboxfb_intro_title"); ?></h3>
					<?php endif;
					echo get_option("oboxfb_intro_text"); ?>
				</div>
			 <?php endif; ?>

			<div class="post-list clearfix">
			<?php
				if(get_option("oboxfb_products_title")) :
					echo '<h3 class="widgettitle">' . get_option("oboxfb_products_title") . '</h3>';
				endif;

				if(get_option("oboxfb_products_category") == "feature_products") :
					echo do_shortcode('[featured_products per_page="'.get_option("oboxfb_products_count").'" columns="4"]');

				elseif(get_option("oboxfb_products_category") == "recent_products") :
					echo do_shortcode('[recent_products per_page="'.get_option("oboxfb_products_count").'" columns="4"]');

				elseif(get_option("oboxfb_products_category") == "product_categories") :
					$cats = get_terms( 'product_cat'); ?>
					<ul class="products">
						<?php foreach ($cats as $cat) :
							$link = get_site_url().'/product-category/'.$cat->slug;
							$thumbnail_id 	= get_woocommerce_term_meta( $cat->term_id , 'thumbnail_id', true );
							if ($thumbnail_id) :
								$image = wp_get_attachment_image($thumbnail_id, '130x130');
							else :
								$image = '<img src="'.$woocommerce->plugin_url().'/assets/images/placeholder.png'.'"alt="" />';
							endif; ?>
							<li class="product">
								<a href="<?php echo $link; ?>"><?php echo $image; ?></a>
								<a href="<?php echo $link; ?>"><?php echo $cat->name ?></a>
								<p><?php if( $cat->description != '' ) echo $cat->description; ?></p>
							</li>
						<?php endforeach; ?>
					</ul>

				<?php endif; ?>
			</div>

		</div>

<?php get_footer(); ?>