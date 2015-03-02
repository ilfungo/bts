<?php
/**
 * Grouped product add to cart - Override
 *
 * @author 		simone
 * @package 	WooCommerce/Templates
 * @version     2.1.7
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product, $post;

$parent_product_post = $post;

do_action( 'woocommerce_before_add_to_cart_form' ); ?>
<?php if (is_product()){ ?>

<form class="cart" method="post" enctype='multipart/form-data'>

	<table cellspacing="0" class="group_table">
		<tbody>
			<?php
				foreach ( $grouped_products as $product_id ) :
					$product = wc_get_product( $product_id );
					$post    = $product->post;
					setup_postdata( $post );
					?>
					<tr>
						<td>
							<?php if ( $product->is_sold_individually() || ! $product->is_purchasable() ) : ?>
								<?php woocommerce_template_loop_add_to_cart(); ?>
							<?php else : ?>
								<?php
									$quantites_required = true;
									woocommerce_quantity_input( array( 'input_name' => 'quantity[' . $product_id . ']', 'input_value' => '0' ) );
								?>
							<?php endif; ?>
						</td>

						<td class="labeld">
							<label for="product-r<?php echo $product_id; ?>">
								<?php echo $product->is_visible() ? '<a href="' . get_permalink() . '">' . get_the_title() . '</a>' : get_the_title(); ?>
							</label>
						</td>

						<?php  do_action ( 'woocommerce_grouped_product_list_before_price', $product ); ?>


					</tr>
					<?php
				endforeach;

				// Reset to parent grouped product
				$post    = $parent_product_post;
				$product = wc_get_product( $parent_product_post->ID );
				setup_postdata( $parent_product_post );
			?>
		</tbody>
	</table>
<?php } ?>

    <!-- disabilito input type="hidden" per l'acquisto   -->
<?php if (is_product()){ ?>

	<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->id ); ?>" />

	<?php if ( $quantites_required ) : ?>


		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

		<button type="submit" class="single_add_to_cart_button button alt"><?php echo $product->single_add_to_cart_text(); ?></button>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

	<?php endif; ?>
 <?php } ?>
</form>

<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>