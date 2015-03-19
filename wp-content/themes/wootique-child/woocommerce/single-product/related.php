<?php
/**
 * Related Products
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product, $woocommerce_loop;

if ( empty( $product ) || ! $product->exists() ) {
	return;
}
$posts_per_page = 1000;
$related = $product->get_related($posts_per_page);

if ( sizeof( $related ) == 0 ) return;

$args = apply_filters( 'woocommerce_related_products_args', array(
	'post_type'            => 'product',
	'ignore_sticky_posts'  => 1,
	'no_found_rows'        => 1,
	'posts_per_page'       => $posts_per_page,
	'orderby'              => $orderby,
	'post__in'             => $related,
	'post__not_in'         => array( $product->id )
) );

$products = new WP_Query( $args );

$woocommerce_loop['columns'] = 4;

if($GLOBALS['main_foto_type']!="annuario"){

if ( $products->have_posts() ) : ?>
    <div style="clear:both"></div>
	<div class="products">

		<h2><?php //_e( 'Related Products', 'woocommerce' );
            $main_foto_type = $GLOBALS['main_foto_type'];
            if($main_foto_type=="foto focus")
                $main_foto_type = "foto ritratto";
            echo "Altre ". $main_foto_type ;?></h2>

		<?php woocommerce_product_loop_start();?>

			<?php while ( $products->have_posts() ) : $products->the_post(); ?>

				<?php //wc_get_template_part( 'content', 'product' ); ?>
                <?php wc_get_template_part( 'content', 'relatedproduct' ); ?>

			<?php endwhile; // end of the loop. ?>

		<?php woocommerce_product_loop_end();
        unset($GLOBALS['main_foto_type']); ?>

	</div>

<?php endif;

}

wp_reset_postdata();
