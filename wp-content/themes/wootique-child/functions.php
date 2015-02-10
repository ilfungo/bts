<?php
add_action( 'wp_enqueue_scripts', 'enqueue_parent_theme_style' );
function enqueue_parent_theme_style() {
wp_enqueue_style( 'quality', get_template_directory_uri().'/style.css' );
}
 /*    action   simone    */

 // rimuovi numero risultati

 remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );

 // rimuovi ordinamento prodotti

    remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );


add_action( 'woocommerce_after_shop_loop_item', 'remove_add_to_cart_buttons', 1 );

function remove_add_to_cart_buttons() {
    remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart' );
}

//mostro 100 prodotti per pagina
add_filter( 'loop_shop_per_page', create_function( '$cols', 'return 100;' ), 20 );

// Simple products
add_filter( 'woocommerce_quantity_input_args', 'jk_woocommerce_quantity_input_args', 10, 2 );
function jk_woocommerce_quantity_input_args( $args, $product ) {
    $args['input_value'] 	= 1;	// Starting value
    $args['max_value'] 		= 80; 	// Maximum value
    $args['min_value'] 		= 1;   	// Minimum value
    $args['step'] 		= 1;    // Quantity steps
    return $args;
}
