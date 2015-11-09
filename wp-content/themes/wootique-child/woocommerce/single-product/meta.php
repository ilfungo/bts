<?php
/**
 * Single Product Meta
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $product;
$cat_count = sizeof( get_the_terms( $post->ID, 'product_cat' ) );
$tag_count = sizeof( get_the_terms( $post->ID, 'product_tag' ) );
?>
<div class="product_meta">
    <?php //do_action( 'woocommerce_product_meta_start' ); ?>
    <?php //template_loop_sold_by_cat()?>

	<?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>

		<span class="sku_wrapper"><?php _e( 'SKU:', 'woocommerce' ); ?> <span class="sku" itemprop="sku"><?php echo ( $sku = $product->get_sku() ) ? $sku : __( 'N/A', 'woocommerce' ); ?></span>.</span>

	<?php endif; ?>

	<?php
    if($GLOBALS['main_foto_type']!="annuario")
        echo $product->get_categories( ', ', '<span class="posted_in">' . _n( 'Classe : ', 'Classi : ', $cat_count, 'woocommerce' ) . ' ', '</span>' );
    else{
        if(isset($_SESSION['class_slug']))echo 'Classe: <a href="/?product_cat='.$_SESSION['class_slug'].'">'.$_SESSION['class_name']."</a>";
    }
    //perchè unset? perchè se non arrivo da una classe devo evitare che tenga una classe "vecchia"
    unset($_SESSION['class_slug']);
    //unset($_SESSION['class_name']);
    ?>

	<?php echo $product->get_tags( ', ', '<span class="tagged_as">' . _n( 'Tag:', 'Tags:', $tag_count, 'woocommerce' ) . ' ', '.</span>' ); ?>

	<?php do_action( 'woocommerce_product_meta_end' ); ?>

</div>