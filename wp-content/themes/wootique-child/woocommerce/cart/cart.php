<?php
/**
 * Cart Page
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

wc_print_notices();

do_action( 'woocommerce_before_cart' ); ?>

<form action="<?php echo esc_url( WC()->cart->get_cart_url() ); ?>" method="post">

<?php do_action( 'woocommerce_before_cart_table' ); ?>
<table class="shop_table cart" cellspacing="0">
	<thead>
		<tr>
			<th class="product-remove">&nbsp;</th> 
			<th class="product-thumbnail">&nbsp;</th>
			<th class="product-name"><?php _e( 'Product', 'woocommerce' ); ?></th>
			<th class="product-price"><?php _e( 'Price', 'woocommerce' ); ?></th>
			<th class="product-quantity"><?php _e( 'Quantity', 'woocommerce' ); ?></th>
			<th class="product-subtotal"><?php _e( 'Total', 'woocommerce' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php do_action( 'woocommerce_before_cart_contents' ); ?>

		<?php
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				?>
				<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

					<td class="product-remove">
						<?php
							echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf( '<a href="%s" class="remove" title="%s">&times;</a>', esc_url( WC()->cart->get_remove_url( $cart_item_key ) ), __( 'Remove this item', 'woocommerce' ) ), $cart_item_key );
						?>
					</td>

					<td class="product-remove">
						<?php
							$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
                            //@todo richiamare il parent... per mettere un link, questo è il single product non visibile

							/*if ( ! $_product->is_visible() )
								echo $thumbnail;
							else
								printf( '<a href="%s">%s</a>', $_product->get_permalink(), $thumbnail );*/
                            $filter1 = $cart_item["addons"][1]["value"];
                            $filter0 = "";
                            if($cart_item["addons"][0]["value"]=="Vignette"){
                                $filter0 = "Vignette";
                            }else{
                                $filter1 = $cart_item["addons"][0]["value"];
                            }



                            if(isset($_product->post->post_parent)){
                                echo '<a href="'. get_permalink($_product->post->post_parent).'&amp;filter0='.$filter0.'&amp;filter1='.$filter1.'">';
                                echo $thumbnail;
                                echo '</a>';
                            }else{
                                echo $thumbnail;
                            }
						?>
					</td>

					<td class="product-name">
						<?php
                        //print_r($_product->post->post_parent);
                        //print_r($cart_item["addons"]);
							//if ( ! $_product->is_visible() )
						//controllo se la foto ha un filtro applicato
						    //if($cart_item["addons"][0]["value"] != "Originale") {
                if(isset($_product->post->post_parent))
                    echo '<a href="'. get_permalink($_product->post->post_parent).'&amp;filter0='.$filter0.'&amp;filter1='.$filter1.'">';

				echo apply_filters('woocommerce_cart_item_name', $_product->get_title(), $cart_item, $cart_item_key);

                if(isset($_product->post->post_parent))
                    echo "</a>";

                                if(isset($cart_item["addons"][0]["value"]))
							        echo "</br> Filtro : " . $cart_item["addons"][0]["value"];
                                if(isset($cart_item["addons"][1]["value"]))
                                    echo " + ".$cart_item["addons"][1]["value"];
							//}else{
								//echo apply_filters('woocommerce_cart_item_name', $_product->get_title(), $cart_item, $cart_item_key);
							//s}
							//else
							//	echo apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', $_product->get_permalink(), $_product->get_title() ), $cart_item, $cart_item_key )
						?>
					</td>

					<td class="product-price">
						<?php
							echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
						?>
					</td>

					<td class="product-quantity">
						<?php
							if ( $_product->is_sold_individually() ) {
								$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
							} else {
								$product_quantity = woocommerce_quantity_input( array(
									'input_name'  => "cart[{$cart_item_key}][qty]",
									'input_value' => $cart_item['quantity'],
									'max_value'   => $_product->backorders_allowed() ? '' : $_product->get_stock_quantity(),
									'min_value'   => '0'
								), $_product, false );
							}

							echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key );
						?>
					</td>

					<td class="product-subtotal">
						<?php
							echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
						?>
					</td>
				</tr>
				<?php
			}
		}

		do_action( 'woocommerce_cart_contents' );
		?>
		<tr>
			<td colspan="6" class="actions">

				<?php if ( WC()->cart->coupons_enabled() ) { ?>
					<div class="coupon">

						<label for="coupon_code"><?php _e( 'Coupon', 'woocommerce' ); ?>:</label> <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php _e( 'Coupon code', 'woocommerce' ); ?>" /> <input type="submit" class="button" name="apply_coupon" value="<?php _e( 'Apply Coupon', 'woocommerce' ); ?>" />

						<?php do_action('woocommerce_cart_coupon'); ?>

					</div>
				<?php } ?>
				<?php if(isset($_SESSION['class_url'])){ ?>
				<a href="<?php print_r($_SESSION['class_url'])?>" class="button alt">Torna alla classe</a>
                <?php } ?>
				<input type="submit" class="button" name="update_cart" value="<?php _e( 'Update Cart', 'woocommerce' ); ?>" /> <input type="submit" class="checkout-button button alt wc-forward" name="proceed" value="<?php _e( 'Proceed to Checkout', 'woocommerce' ); ?>" />

				<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>

				<?php wp_nonce_field( 'woocommerce-cart' ); ?>
			</td>
		</tr>

		<?php do_action( 'woocommerce_after_cart_contents' ); ?>
	</tbody>
</table>

<?php do_action( 'woocommerce_after_cart_table' ); ?>

</form>

<div class="cart-collaterals">

	<?php do_action( 'woocommerce_cart_collaterals' ); ?>

	<?php woocommerce_cart_totals(); ?>

	<?php woocommerce_shipping_calculator(); ?>

</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
