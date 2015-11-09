<?php
/**
 * Template Function Overrides
 *
 */



//funzione aggiunta per il debug!!
function FirePHP($message, $label = null, $type = 'LOG')
{
    static $i = 0;

    if (headers_sent() === false)
    {
        $type = (in_array($type, array('LOG', 'INFO', 'WARN', 'ERROR')) === false) ? 'LOG' : $type;

        if (($_SERVER['HTTP_HOST'] == 'localhost') && (strpos($_SERVER['HTTP_USER_AGENT'], 'FirePHP') !== false))
        {
            $message = json_encode(array(array('Type' => $type, 'Label' => $label), $message));

            if ($i == 0)
            {
                header('X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2');
                header('X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3');
                header('X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1');
            }

            header('X-Wf-1-1-1-' . ++$i . ': ' . strlen($message) . '|' . $message . '|');
        }
    }
}


add_filter( 'woocommerce_product_tabs', 'fp_woocommerce_remove_reviews_tab', 98);
function fp_woocommerce_remove_reviews_tab($tabs) {

    unset($tabs['reviews']);

    return $tabs;
}


function product_cat_add_id($term) {

    // put the term ID into a variable
    $t_id = $term->term_id;
?>
    <tr class="form-field term-id">
        <th scope="row"><label for="term-id">ID categoria</label></th>
        <td><input type="text" size="40" value="<?=$t_id ?>" id="term-id" name="term-id" readonly>
        </td>
    </tr>
<?php
}
add_action( 'product_cat_edit_form_fields','product_cat_add_id' , 0, 2 );
//add_action( 'product_cat_add_form_fields', 'vpwd_taxonomy_add_new_meta_field', 10, 2 );

// Add term page
// == Aggiungo un campo password in chiaro alla pagina (HALF) categoria
// cioè http://localhost/bts/wp-admin/edit-tags.php?taxonomy=product_cat&post_type=product
function vpwd_taxonomy_add_new_meta_field() {
	// this will add the custom meta field to the add new term page
	?>
	<div class="form-field">
		<label for="term_meta[visible_password]"><?php _e( 'Password in chiaro', 'tutorialshares' ); ?></label>
		<input type="text" name="term_meta[visible_password]" id="term_meta[visible_password]" value="">
		<p class="description"><?php _e( 'Attribuisci una password alla categoria per renderla accessibile solo attraverso una password specifica, non inserire spazi o caratteri speciali, solo numeri e lettere','tutorialshares' ); ?></p>
	</div>
<?php
}
add_action( 'product_cat_add_form_fields', 'vpwd_taxonomy_add_new_meta_field', 2, 2 );
//remove_action( 'product_cat_edit_form_fields', 'product_cat_description' );
remove_action( 'product_cat_edit_form_fields', array( $this, 'product_cat_description' ) );


// == Aggiungo un campo password in chiaro alla pagina (HALF) categoria
// == Aggiungo un campo password in chiaro alla pagina (FULL) categoria
// esempio http://localhost/bts/wp-admin/edit-tags.php?action=edit&taxonomy=product_cat&tag_ID=14&post_type=product
// Edit term page
function vpwd_taxonomy_edit_meta_field($term) {
 
	// put the term ID into a variable
	$t_id = $term->term_id;
 
	// retrieve the existing value(s) for this meta field. This returns an array
	$term_meta = get_option( "taxonomy_$t_id" ); ?>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[visible_password]"><?php _e( 'Password in chiaro', 'tutorialshares' ); ?></label></th>
		<td>
			<input type="text" name="term_meta[visible_password]" id="term_meta[visible_password]" value="<?php echo esc_attr( $term_meta['visible_password'] ) ? esc_attr( $term_meta['visible_password'] ) : ''; ?>">
			<p class="description"><?php _e( 'Attribuisci una password alla categoria per renderla accessibile solo attraverso una password specifica, non inserire spazi o caratteri speciali, solo numeri e lettere','tutorialshares' ); ?></p>
		</td>
	</tr>
<?php
}
add_action( 'product_cat_edit_form_fields', 'vpwd_taxonomy_edit_meta_field', 4, 2 );
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
//$metafieldoutput = $metafieldArray['visible_password'];

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

        //print_r($term_id );
        /*
        print_r($ptp_importer);
        print_r($bptpi_premium);
        print_r($post);
        print_r($cat);
        */

        // qui gli devo dire che if(is_tax()){ allora deve prendere la
        //perchè ritora un hash??? com'è salvata la password??

        //if(is_tax("product_cat")){echo " è una taxonomy product_cat";}

        //ciao

        $password_hash = ptp_get_term_meta( $term_id, $bptpi_premium->term_password_meta_key, true );
        //echo $password_hash ;

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

remove_action( 'woocommerce_after_shop_loop_item', array('WCV_Vendor_Shop', 'template_loop_sold_by'), 9, 2);
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

// Hook in
add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );

// Our hooked in function - $fields is passed via the filter!
//documentaion page: http://docs.woothemes.com/document/tutorial-customising-checkout-fields-using-actions-and-filters/
function custom_override_checkout_fields( $fields ) {
    unset($fields['order']['order_comments']);
    unset($fields['billing']['billing_address_1']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_city']);
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_state']);
    //unset($fields['billing']['billing_country_field']);
    unset($fields['billing']['billing_country']);
    //unset($fields['billing']['billing_address_1']);
    unset($fields['shipping']['woocommerce-shipping-fields']);

    //print_r($fields);

    return $fields;
}

/**

 * Redirect non-logged-in users to the registration page

 */


//se non ho fatto login non posso vedere nè i prodotti nè le categorie prodotto!!
add_action( 'template_redirect', function(){
    global $current_user;
    $billing_scuola_taxonomy_slug = get_user_meta($current_user->ID, "billing_scuola_taxonomy_slug",true);

    //se vado alla pagina del login ma non ho un valore che soddisfi l'id taxonomy faccio redirect alla pag smistamento!!
    //cambio logica, faccio vedere solo il login e non la registrazione
    /*if( ! is_user_logged_in() && is_page( array(9)) && $_SESSION["scuola_id"] == 0  && (empty( $_POST['billing_first_name'] ) || ! empty( $_POST['billing_first_name'] ) && trim( $_POST['billing_first_name'] ) == '')){
        wp_redirect( '/?page_id=2' );
        exit;
    }*/
    //session_unset();
    //session_destroy();

    global $wp;
    $current_url = $wp->query_string;
    if(is_user_logged_in() && $_SESSION["scuola_redirect"]=="redirect" &&  is_page( array(9))){
        unset($_SESSION["scuola_redirect"]);
        wp_redirect( '/?product_cat='.$billing_scuola_taxonomy_slug."&caso=caso7" );
        exit;
    }elseif(!is_user_logged_in() && $_SESSION["scuola_slug"] &&  is_page( array(2))){
        wp_redirect( '/?page_id=9&caso=caso5' );
        exit;
    }elseif($current_url=="post_type=product" && get_user_role()!="administrator"){
        wp_redirect( '/?caso=caso6' );
        exit;
    }elseif( ! is_user_logged_in() && (is_product_category() || is_product())){
        wp_redirect( '/?page_id=9&caso=caso3' );
        exit;
    }elseif( is_page( array(2)) && get_user_role()=="customer"){
    //se sono nella pagina 2 (vai alla scuola) e sono logato allora effettuo redirect esattamente alla mia scuola
        if($billing_scuola_taxonomy_slug!=""){
            wp_redirect( '/?product_cat='.$billing_scuola_taxonomy_slug."&caso=caso2" );
            exit;
        }
    }elseif( get_user_role()=="customer" && (is_tax("product_cat") || is_product())){
        //se sono logato (implicito) e il mio ruolo è di customer e sono in una product category o in un prodotto
        //devo verificare che io sia nella mia specifica procut category!!!
        //echo wc_origin_trail_ancestor();
        if($billing_scuola_taxonomy_slug!=wc_origin_trail_ancestor()){
            wp_redirect( '/?product_cat='.$billing_scuola_taxonomy_slug."&caso=caso3" );
            exit;
        }
    }
    //echo $current_user->user_login;
});

    function get_user_role(){
        global $current_user;
        $roles = $current_user->roles;
        $role = array_shift($roles);
        return $role;
    }


function wc_origin_trail_ancestor( $link = false, $trail = false ) {
//http://wordpress.stackexchange.com/questions/56784/get-main-parent-categories-for-a-product

    if (is_tax("product_cat")) {
        global $wp_query;
        $q_obj = $wp_query->get_queried_object();
        $cat_id = $q_obj->term_id;//12

        $descendant = get_term_by("id", $cat_id, "product_cat");
        $descendant_id = $descendant->term_id;//12

        $ancestors = get_ancestors($cat_id, 'product_cat');
        $ancestors = array_reverse($ancestors);

        $origin_ancestor = get_term_by("id", $ancestors[0], "product_cat");
        $origin_ancestor_id = $origin_ancestor->term_id;

        $ac = count($ancestors);
        if($ac==0){$ancestors[0]=$descendant_id;}

    } else if ( is_product() ) {

        $descendant = get_the_terms( $post->ID, 'product_cat' );
        $descendant = array_reverse($descendant);
        $descendant = $descendant[0];
        $descendant_id = $descendant->term_id;

        $ancestors = array_reverse(get_ancestors($descendant_id, 'product_cat'));
        $ac = count($ancestors);

    }else{
        return "no_product-no_cat";
    }
    //echo "si lo sono!!!123";
    //exit();
    //$c = 1;
    //exit();


        $origin_ancestor_term = get_term_by("id", $ancestors[0], "product_cat");
        return $origin_ancestor_term->slug;


}

function template_loop_sold_by_cat($product_id) {
    //$author     = WCV_Vendors::get_vendor_from_product( $product_id );
    //$sold_by = WCV_Vendors::is_vendor( $author )
    //  ? sprintf( '<a href="%s">%s</a>', WCV_Vendors::get_vendor_shop_page( $author), WCV_Vendors::get_vendor_shop_name( $author ) )
    //    : get_bloginfo( 'name' );
    $scuola = wc_origin_trail_ancestor();
    $term = get_term_by('slug', $scuola, 'product_cat');
    $scuola_name = $term->name;
    $link_scuola = "/?product_cat=".$scuola;
    echo 'Scuola: <a href="'.$link_scuola.'">'.$scuola_name.'</a> <br />';
}

//add_action( 'woocommerce_product_meta_start', array( 'WCV_Vendor_Cart', 'sold_by_meta' ), 10, 2 );

add_filter( 'manage_edit-product_columns', 'show_product_order' );
function show_product_order($columns){

    unset( $columns['tags'] );
    //unset( $columns['price'] );
    unset( $columns['sku'] );
    unset( $columns['product_tag'] );

    return $columns;
}



//add_action( 'show_user_profile', 'add_scuola_fields', 5, 1 );

//aggiungo campi scuola e classe all'utente
add_action( 'show_user_profile', 'add_scuola_fields', 2, 1 );
add_action( 'edit_user_profile', 'add_scuola_fields', 2, 1 );

function add_scuola_fields( $user ){
    ?>
    <h3>Campi della scuola</h3>

    <table class="form-table">
        <tr>
            <th><label for="user_id">ID utente</label></th>
            <td><input type="text" name="user_id" value="<?php echo $user->ID; ?>" class="regular-text" readonly /></td>
        </tr>
        <tr>
            <th><label for="scuola">Scuola</label></th>
            <td><input type="text" name="billing_scuola_taxonomy_id" value="<?php echo esc_attr(get_the_author_meta( 'billing_scuola_taxonomy_id', $user->ID )); ?>" class="regular-text" /> (<?php echo esc_attr(get_the_author_meta( 'billing_scuola_taxonomy_slug', $user->ID )); ?>)</td>
        </tr>

        <tr>
            <th><label for="classe">Classe</label></th>
            <td><input type="text" name="billing_classe_id" value="<?php echo esc_attr(get_the_author_meta( 'billing_classe_id', $user->ID )); ?>" class="regular-text" /></td>
        </tr>

    </table>
<?php
}

add_action( 'personal_options_update', 'save_scuola_fields' );
add_action( 'edit_user_profile_update', 'save_scuola_fields' );

function save_scuola_fields( $user_id )
{
    update_user_meta( $user_id,'billing_scuola_taxonomy_id', sanitize_text_field( $_POST['billing_scuola_taxonomy_id'] ) );
    $scuola=get_term( sanitize_text_field( $_POST['billing_scuola_taxonomy_id'] ), "product_cat");
    //print_r($scuola->slug);exit;
    update_user_meta( $user_id,'billing_scuola_taxonomy_slug', $scuola->slug );
    update_user_meta( $user_id,'billing_classe_id', sanitize_text_field( $_POST['billing_classe_id'] ) );
}


// non qui non così!
add_action( 'woocommerce_checkout_update_order_meta', 'classe_add_order_meta', 10, 2 );

function classe_add_order_meta( $order_id, $posted ) {
    if(isset($_SESSION['class_name'])) {
        $class_name = $_SESSION['class_name'];
    }else{
        $class_name = "sessione_scaduta";
    }
    update_post_meta( $order_id, '_order_classe', $class_name );
}


/* non va :(
add_action( 'woocommerce_add_order_item_meta', 'item_classe_order_itemmeta', 10, 2 );
function item_classe_order_itemmeta( $item_id, $values, $cart_item_key ) {
    if(isset($_SESSION['class_name'])) {
        $class_name = $_SESSION['class_name'];
    }else{
        $class_name = "sessione_scaduta";
    }
    wc_add_order_item_meta( $item_id, '_product_classe', $class_name );
}*/

/*
add_action( 'woocommerce_add_order_item_meta', '25979024_add_order_item_meta', 10, 3 );
function 25979024_add_order_item_meta( $order_item_id, $cart_item, $cart_item_key ) {
    wc_add_order_item_meta( $order_item_id, '_pdf_something', 'hide this stuff' );
}
*/