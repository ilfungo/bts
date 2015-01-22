<?php
/**
 * Template Function Overrides
 *
 */

add_filter( 'woocommerce_product_tabs', 'fp_woocommerce_remove_reviews_tab', 98);
function fp_woocommerce_remove_reviews_tab($tabs) {

    unset($tabs['reviews']);

    return $tabs;
}




// Add term page
// == Aggiungo un campo password in chiaro alla pagina (HALF) categoria
// cioè http://localhost/bts/wp-admin/edit-tags.php?taxonomy=product_cat&post_type=product
function tutorialshares_taxonomy_add_new_meta_field() {
	// this will add the custom meta field to the add new term page
	?>
	<div class="form-field">
		<label for="term_meta[custom_term_meta]"><?php _e( 'Password in chiaro', 'tutorialshares' ); ?></label>
		<input type="text" name="term_meta[custom_term_meta]" id="term_meta[custom_term_meta]" value="">
		<p class="description"><?php _e( 'Attribuisci una password alla categoria per renderla accessibile solo attraverso una password specifica, non inserire spazi o caratteri speciali, solo numeri e lettere','tutorialshares' ); ?></p>
	</div>
<?php
}
add_action( 'product_cat_add_form_fields', 'tutorialshares_taxonomy_add_new_meta_field', 10, 2 );
// == Aggiungo un campo password in chiaro alla pagina (HALF) categoria

// == Aggiungo un campo password in chiaro alla pagina (FULL) categoria
// esempio http://localhost/bts/wp-admin/edit-tags.php?action=edit&taxonomy=product_cat&tag_ID=14&post_type=product
// Edit term page
function tutorialshares_taxonomy_edit_meta_field($term) {
 
	// put the term ID into a variable
	$t_id = $term->term_id;
 
	// retrieve the existing value(s) for this meta field. This returns an array
	$term_meta = get_option( "taxonomy_$t_id" ); ?>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[custom_term_meta]"><?php _e( 'Password in chiaro', 'tutorialshares' ); ?></label></th>
		<td>
			<input type="text" name="term_meta[custom_term_meta]" id="term_meta[custom_term_meta]" value="<?php echo esc_attr( $term_meta['custom_term_meta'] ) ? esc_attr( $term_meta['custom_term_meta'] ) : ''; ?>">
			<p class="description"><?php _e( 'Attribuisci una password alla categoria per renderla accessibile solo attraverso una password specifica, non inserire spazi o caratteri speciali, solo numeri e lettere','tutorialshares' ); ?></p>
		</td>
	</tr>
<?php
}
add_action( 'product_cat_edit_form_fields', 'tutorialshares_taxonomy_edit_meta_field', 10, 2 );
// == Aggiungo un campo password in chiaro alla pagina (FULL) categoria

// Save extra taxonomy fields callback function.
function save_taxonomy_custom_meta( $term_id ) {
	if ( isset( $_POST['term_meta'] ) ) {
		$t_id = $term_id;
		$term_meta = get_option( "taxonomy_$t_id" );
		$cat_keys = array_keys( $_POST['term_meta'] );
		foreach ( $cat_keys as $key ) {
			if ( isset ( $_POST['term_meta'][$key] ) ) {
				$term_meta[$key] = $_POST['term_meta'][$key];
			}
		}
		// Save the option array.
		update_option( "taxonomy_$t_id", $term_meta );
	}
}  
add_action( 'edited_product_cat', 'save_taxonomy_custom_meta', 10, 2 );  
add_action( 'create_product_cat', 'save_taxonomy_custom_meta', 10, 2 );

/*====to display data====*/

//$metafieldArray = get_option('taxonomy_'. $term->term_id);
//$metafieldoutput = $metafieldArray['custom_term_meta'];

//echo $metafieldoutput;

/*====to display data====*/


if ( !function_exists('fp_category_unlock_form') ) :
    /**
     * Passoword template
     *
     * @param array
     * @return string
     */
    function fp_category_unlock_form() {
        global $ptp_importer, $bptpi_premium, $post, $cat;


        //per ora lo commento comunque voglio fare il debug anche se ho fatto il login!!!
        //if ( current_user_can( 'manage_woocommerce' ) )
        //    return;

        $term_id = '';

        //devo mettere un flag scuola nella categoria, controllare se è scuola, allora acchiappo la password
        //nella pagina che ne so io devo dargli il form della password, dalla password devo poi risalire alla scuola abbinata



        if ( $_GET['term_id'] ) {
            $term_id = $_GET['term_id'];
        } else if ( wp_get_post_terms( $post->ID, $ptp_importer->woocommerce_cat_tax ) && is_single() ) { // If in single product view
            foreach ( wp_get_post_terms( $post->ID, $ptp_importer->woocommerce_cat_tax ) as $term ) {
                if ( ptp_term_has_password( $term->term_id ) ) {
                    $term_id = $term->term_id;
                    break;
                }
            }
        } else {
            $term_id = get_queried_object_id();
        }

        print_r($term_id );
        /*
        print_r($ptp_importer);
        print_r($bptpi_premium);
        print_r($post);
        print_r($cat);
        */

        // qui gli devo dire che if(is_tax()){ allora deve prendere la
        //perchè ritora un hash??? com'è salvata la password??

        if(is_tax("product_cat")){echo " è una taxonomy product_cat";}

        $password_hash = ptp_get_term_meta( $term_id, $bptpi_premium->term_password_meta_key, true );
        echo $password_hash ;

        if ( !$password_hash )
            return;

        if ( $_COOKIE['ptp_loggged_in'] ) {
            if ( ptp_get_term_meta( $term_id, $_SERVER['REMOTE_ADDR'], true ) == $_COOKIE['ptp_loggged_in'] )
                return;
        }

        $connector = get_option( 'permalink_structure' ) != '' ? '?' : '&';

        if ( isset( $_POST['ptp_term_password'] ) && wp_check_password( $_POST['ptp_term_password'], $password_hash ) ) {
            $hash = wp_hash_password( $_SERVER['REMOTE_ADDR'] );
            ptp_update_term_meta( $term_id, $_SERVER['REMOTE_ADDR'], $_SERVER['REMOTE_ADDR'] . $hash );

            ob_start();

            // Set our cookie
            setcookie( 'ptp_loggged_in', $_SERVER['REMOTE_ADDR'] . $hash, 0, '/' );

            if ( !isset($_GET['wrong-password']) )
                wp_redirect( $_SERVER['REQUEST_URI'], 302 ); // Redirect to avoid reposting post data
            else
                wp_redirect( substr( $_SERVER['REQUEST_URI'], 0, strpos( $_SERVER['REQUEST_URI'], $connector ) ), 302 ); // Redirect to avoid reposting post data
        } else if ( isset( $_POST['ptp_term_password'] ) ) {
            ob_start();

            if ( !isset($_GET['wrong-password']) ){
                if(isset($_REQUEST['post_type'])	&&	($_REQUEST['post_type']	!=	'') )
                    $connector	=	rtrim($connector, "?");
                wp_redirect( $_SERVER['REQUEST_URI'] . $connector . '&wrong-password=1', 302 ); // Redirect to avoid reposting post data
            } else
                wp_redirect( $_SERVER['REQUEST_URI'], 302 ); // Redirect to avoid reposting post data
        }

        $templates = ptp_templates();

        if ( locate_template( $templates['password'], false ) ) {
            $template = locate_template( $templates['password'], true );
        } else {
            $template = $bptpi_premium->plugin_path . '/front-end/templates/' . $templates['password'];
        }

        include_once $template;
        exit;
    }
    add_action( 'template_redirect', 'fp_category_unlock_form' );
endif;

/*redirect
add_filter ('add_to_cart_redirect', 'redirect_to_checkout');

function redirect_to_checkout() {
    global $woocommerce;
    $checkout_url = $woocommerce->cart->get_checkout_url();
    return $checkout_url;
}
*/
/* role and capabilities

// get the the role object
$role_object = get_role( 'shop_manager' );
// add $cap capability to this role object
$role_object->add_cap( 'edit_theme_options' );
*/

//remove_action( 'woocommerce_after_shop_loop_item', array('WCV_Vendor_Shop', 'template_loop_sold_by'), 9, 2);