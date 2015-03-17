<?php
/**
 * Empty cart page
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

wc_print_notices();

?>

<p class="cart-empty"><?php _e( 'Your cart is currently empty.', 'woocommerce' ) ?></p>

<?php do_action( 'woocommerce_cart_is_empty' );
global $current_user;
//echo "user".$current_user->ID;
$billing_scuola_taxonomy_slug = get_user_meta($current_user->ID, "billing_scuola_taxonomy_slug",true);
if($billing_scuola_taxonomy_slug!="")//customer & vendor?? no il vendor no
    $redirect = "/?product_cat=".$billing_scuola_taxonomy_slug;

if(isset($_SESSION['class_url']))
    $link = $_SESSION['class_url'];
elseif($current_user->roles[0]=="customer" && $billing_scuola_taxonomy_slug!="")
    $link = $billing_scuola_taxonomy_slug;
else
    $link = apply_filters( 'woocommerce_return_to_shop_redirect', get_permalink( wc_get_page_id( 'shop' ) ) );
?>

<p class="return-to-shop"><a class="button wc-backward" href="<?php echo $link; ?>">
        <?php _e( 'Return To Shop', 'woocommerce' ) ?>
</a></p>