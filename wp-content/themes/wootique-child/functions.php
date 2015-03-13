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


//mostro 100 prodotti per pagina
add_filter( 'loop_shop_per_page', create_function( '$cols', 'return 100;' ), 20 );

// Simple products
add_filter( 'woocommerce_quantity_input_args', 'jk_woocommerce_quantity_input_args', 10, 2 );
function jk_woocommerce_quantity_input_args( $args, $product ) {
    $args['max_value'] 		= 80; 	// Maximum value
    $args['min_value'] 		= 1;   	// Minimum value
    return $args;
}
/*  ordinamento prodotti custom  */
add_filter( 'woocommerce_get_catalog_ordering_args', 'custom_woocommerce_get_catalog_ordering_args' );

function custom_woocommerce_get_catalog_ordering_args( $args ) {
    $orderby_value = isset( $_GET['orderby'] ) ? woocommerce_clean( $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );

    if ( 'random_list' == $orderby_value ) {
        $args['orderby'] = 'rand';
        $args['order'] = '';
        $args['meta_key'] = ' ';
    }

    return $args;
}

add_filter( 'woocommerce_default_catalog_orderby_options', 'custom_woocommerce_catalog_orderby' );
add_filter( 'woocommerce_catalog_orderby', 'custom_woocommerce_catalog_orderby' );


function custom_woocommerce_catalog_orderby( $sortby ) {
    $sortby['random_list'] = 'Random';
    return $sortby;
}


function wooc_extra_register_fields() {
    ?>

    <div class="clear"></div>

    <p class="form-row form-row-first validate-required" id="billing_first_name_field">
        <label for="billing_first_name" class="">Nome <abbr class="required" title="obbligatorio">*</abbr>
        </label>
        <input type="text" class="input-text " name="billing_first_name" id="billing_first_name" placeholder="" value="">
    </p>
    <p class="form-row form-row-last validate-required" id="billing_last_name_field">
        <label for="billing_last_name" class="">Cognome <abbr class="required" title="obbligatorio">*</abbr>
        </label>
        <input type="text" class="input-text " name="billing_last_name" id="billing_last_name" placeholder="" value="">
    </p>
    <p class="form-row form-row-first validate-required validate-phone" id="billing_phone_field">
        <label for="billing_phone" class="">Telefono <abbr class="required" title="obbligatorio">*</abbr>
        </label>
        <input type="text" class="input-text " name="billing_phone" id="billing_phone" placeholder="" value="">
    </p>
    <div class="clear"></div>
    <p class="form-row form-row-wide validate-required" id="billing_scuola_field">
        <label for="billing_scuola" class="">Scuola <abbr class="required" title="obbligatorio">*</abbr>
        </label>
        <input type="text" class="input-text " name="billing_scuola" id="billing_scuola" value="<?php echo $_SESSION["scuola_name"];?>" readonly>
    </p>
    <p class="form-row form-row-wide validate-required" id="billing_classe_field">
        <label for="billing_scuola" class="">Classe <abbr class="required" title="obbligatorio">*</abbr>
        </label>
        <span class="warning"><strong>Attenzione</strong> la classe inserita è vincolante per l'ordine! Scegliendo la classe sbagliata non potrai acquistare e vedere le tue foto!</span>
        <br><br>
        <?php
        function woocommerce_subcats_from_parentcat_by_ID($parent_cat_ID,$isArray=true) {
            $args = array(
                'hierarchical' => 1,
                'show_option_none' => '',
                'hide_empty' => 0,
                'parent' => $parent_cat_ID,
                'taxonomy' => 'product_cat'
            );
            if($isArray){
                $subcats = get_categories($args);
                $output = $subcats;
            }else{
                $subcats = get_categories($args);

                foreach ($subcats as $sc) {
                    //$link = get_term_link( $sc->slug, $sc->taxonomy );
                    $output .= '<option value="'.$sc->term_id.'">'.$sc->name.'</option>';
                }
            }
            return $output;
        }
        if($_SESSION["scuola_id"]!="" && ! empty($_SESSION["scuola_id"])){
            $output = '<select name="billing_classe_id" id="billing_classe_id">';
            $output .= '<option selected value="">Scegli la tua classe</option>';
            $output .= woocommerce_subcats_from_parentcat_by_ID($_SESSION["scuola_id"],false);
            $output .= '</select>';
            echo $output;

        }
        //var_dump($output);
        ?>
    </p>
    <input type="hidden" class="input-text " name="billing_scuola_taxonomy_id" id="billing_scuola_taxonomy_id" value="<?php echo  $_SESSION["scuola_id"]?>">
    <input type="hidden" class="input-text " name="billing_scuola_taxonomy_slug" id="billing_scuola_taxonomy_slug" value="<?php echo  $_SESSION["scuola_slug"]?>">
    <div class="clear"></div>

<?php
}
add_action( 'woocommerce_register_form_start', 'wooc_extra_register_fields' );
/**
 * Validate the extra register fields.
 *
 * @param  string $username          Current username.
 * @param  string $email             Current email.
 * @param  object $validation_errors WP_Error object.
 *
 * @return void
 */


function wooc_validate_extra_register_fields( $username, $email, $validation_errors ) {
    if ( isset( $_POST['billing_first_name'] ) && empty( $_POST['billing_first_name'] ) ) {
        $validation_errors->add( 'billing_first_name_error', __( '<strong>Errore</strong>: Devi inserire il nome!', 'woocommerce' ) );
    }

    if ( isset( $_POST['billing_last_name'] ) && empty( $_POST['billing_last_name'] ) ) {
        $validation_errors->add( 'billing_last_name_error', __( '<strong>Errore</strong>: Devi inserire il cognome!.', 'woocommerce' ) );
    }


    if ( isset( $_POST['billing_phone'] ) && empty( $_POST['billing_phone'] ) ) {
        $validation_errors->add( 'billing_phone_error', __( '<strong>Errore</strong>: Devi insierire il tuo numero di telefono!.', 'woocommerce' ) );
    }

    if ( isset( $_POST['billing_classe_id'] ) && empty( $_POST['billing_classe_id'] ) ) {
        $validation_errors->add( 'billing_classe_id_error', __( '<strong>Errore</strong>: Devi inserire la tua classe!', 'woocommerce' ) );
    }

    if ( isset( $_POST['billing_scuola_taxonomy_id'] ) && empty( $_POST['billing_scuola_taxonomy_id'] ) ) {
        $validation_errors->add( 'billing_scuola_taxonomy_id_error', __( '<strong>Errore</strong>: Contatta l\'amministratore del sito per completare l\'iscrizione riferendo questo errore: billing_scuola_taxonomy_id!.', 'woocommerce' ) );
    }

    if ( isset( $_POST['billing_scuola_taxonomy_slug'] ) && empty( $_POST['billing_scuola_taxonomy_slug'] ) ) {
        $validation_errors->add( 'billing_scuola_taxonomy_slug_error', __( '<strong>Errore</strong>: Contatta l\'amministratore del sito per completare l\'iscrizione riferendo questo errore: billing_scuola_taxonomy_slug!.', 'woocommerce' ) );
    }


}

add_action( 'woocommerce_register_post', 'wooc_validate_extra_register_fields', 10, 3 );

/**
 * Save the extra register fields.
 *
 * @param  int  $customer_id Current customer ID.
 *
 * @return void
 */
function wooc_save_extra_register_fields( $customer_id ) {
    if ( isset( $_POST['billing_first_name'] ) ) {
        // WordPress default first name field.
        update_user_meta( $customer_id, 'first_name', sanitize_text_field( $_POST['billing_first_name'] ) );

        // WooCommerce billing first name.
        update_user_meta( $customer_id, 'billing_first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
    }

    if ( isset( $_POST['billing_last_name'] ) ) {
        // WordPress default last name field.
        update_user_meta( $customer_id, 'last_name', sanitize_text_field( $_POST['billing_last_name'] ) );

        // WooCommerce billing last name.
        update_user_meta( $customer_id, 'billing_last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
    }

    if ( isset( $_POST['billing_phone'] ) ) {
        // WooCommerce billing phone
        update_user_meta( $customer_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
    }

    if ( isset( $_POST['billing_scuola_taxonomy_id'] ) ) {
        update_user_meta( $customer_id, 'billing_scuola_taxonomy_id', sanitize_text_field( $_POST['billing_scuola_taxonomy_id'] ) );
    }

    if ( isset( $_POST['billing_scuola_taxonomy_slug'] ) ) {
        update_user_meta( $customer_id, 'billing_scuola_taxonomy_slug', sanitize_text_field( $_POST['billing_scuola_taxonomy_slug'] ) );
    }

    if ( isset( $_POST['billing_classe_id'] ) ) {
        update_user_meta( $customer_id, 'billing_classe_id', sanitize_text_field( $_POST['billing_classe_id'] ) );
    }
}

add_action( 'woocommerce_created_customer', 'wooc_save_extra_register_fields' );


// Add sold by to product loop before add to cart
//remove_action( 'woocommerce_after_shop_loop_item', array('WCV_Vendor_Shop', 'template_loop_sold_by'), 9 );
//add_action( 'woocommerce_after_shop_loop_item', 'template_loop_sold_by_cat', 9 );
add_filter( 'woocommerce_after_shop_loop_item', 'template_loop_sold_by_cat',1);

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 80 );


function woocommerce_template_main_title(){ ?>
    <div class="intro-text">
    Acquista la tua foto, puoi acquistarla "liscia" o applicando filtri colore.<br>
    La procedura di acquisto è facile, leggi bene in ogni pagina le istruzioni, non è un gioco,
    quello che acquisti andrà pagato!
    </div>
<?php }

add_action( 'woocommerce_single_product_summary', 'woocommerce_template_main_title', 5 );

// Remove the default Thematic blogtitle function
function change_BTPI_product_categories() {
    global $woocommerce_loop;
    global $wpdb, $bptpi_premium;


    $current_user = wp_get_current_user();
    $thisUser     = $current_user->ID;

    $sql          = "SELECT meta_value,taxonomy_id";
    $sql         .= " FROM {$wpdb->taxonomymeta}";
    $sql         .= " WHERE {$wpdb->taxonomymeta}.meta_key = '%s'";

    $users = $wpdb->get_results( $wpdb->prepare( $sql, $bptpi_premium->users_meta_key ) );
    $ids = array();
    //add cat ids if the current user is in the meta_value from taxonomymeta
    if($users){
        foreach($users as $row){
            if($row->meta_value) {
                $tempIds = unserialize($row->meta_value);
                if(in_array($thisUser,$tempIds))
                    array_push($ids,trim($row->taxonomy_id));
            }
        }
    }

    if($ids){
        // get terms and workaround WP bug with parents/pad counts
        $args = array(
            'orderby'    => 'name',
            'order'      => 'ASC',
            'hide_empty' => 0,
            'include'    => $ids,
            'pad_counts' => true
        );

        $product_categories = get_terms( 'product_cat', $args );
        $product_categories = array_slice( $product_categories, 0, 4 );
    }
    $woocommerce_loop['columns'] = 4;

    ob_start();

    // Reset loop/columns globals when starting a new loop
    $woocommerce_loop['loop'] = $woocommerce_loop['column'] = '';

    if ( $product_categories ) {
        woocommerce_product_loop_start();
        foreach ( $product_categories as $category ) {
            woocommerce_get_template( 'content-product_cat.php', array(
                'category' => $category
            ) );
        }

        woocommerce_product_loop_end();
        woocommerce_reset_loop();
    }
    remove_action('woocommerce_after_my_account','BPTI_product_categories');
    remove_action('show_user_profile','BPTI_product_categories');
}
add_action( 'woocommerce_after_my_account','change_BTPI_product_categories',3);
add_action( 'show_user_profile','change_BTPI_product_categories',3 );


/**
 * Redirect users to custom URL based on their role after login
 *
 * @param string $redirect
 * @param object $user
 * @return string
 */
function wc_custom_user_redirect( $redirect, $user ) {
// Get the first of all the roles assigned to the user
    //print_r($user);
    $billing_scuola_taxonomy_slug = get_user_meta($user->ID, "billing_scuola_taxonomy_slug",true);
    if($billing_scuola_taxonomy_slug!=""){//customer & vendor?? no il vendor no
        $redirect = "/?product_cat=".$billing_scuola_taxonomy_slug;
    }else{
        $redirect = true;
    }
    /*
    $role = $user->roles[0];
    $dashboard = admin_url();
    $myaccount = get_permalink( wc_get_page_id( 'myaccount' ) );
    if( $role == 'administrator' ) {
//Redirect administrators to the dashboard
        $redirect = $dashboard;
    } elseif ( $role == 'shop-manager' ) {
//Redirect shop managers to the dashboard
        $redirect = $dashboard;
    } elseif ( $role == 'editor' ) {
//Redirect editors to the dashboard
        $redirect = $dashboard;
    } elseif ( $role == 'author' ) {
//Redirect authors to the dashboard
        $redirect = $dashboard;
    } elseif ( $role == 'customer' || $role == 'subscriber' ) {
//Redirect customers and subscribers to the "My Account" page
        $redirect = $myaccount;
    } else {
//Redirect any other role to the previous visited page or, if not available, to the home
        $redirect = wp_get_referer() ? wp_get_referer() : home_url();
    }
    */
    return $redirect;
}
add_filter( 'woocommerce_login_redirect', 'wc_custom_user_redirect', 10, 2 );


function write_txt_file($content, $path, $has_sections=FALSE) {

    if (!$handle = fopen($path, 'w+')) {
        return false;
    }

    $success = fwrite($handle, $content);
    fclose($handle);

    return $success;
}


function attach_txt_file($content, $path, $has_sections=FALSE) {

    if (!$handle = fopen($path, 'a+')) {
        return false;
    }

    $success = fwrite($handle, $content);
    fclose($handle);

    return $success;
} 