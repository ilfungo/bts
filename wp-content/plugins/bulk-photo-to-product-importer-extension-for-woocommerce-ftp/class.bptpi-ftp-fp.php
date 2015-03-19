<?php
//cerca le seguenti voci
//creazione foto
//

class bptpi_ftp_fp {

	var $version = '1.0';
	var $basename = '';
	var $folder = '';
	
	var $meets_guidelines = array(); // Internal use only.
	
	function __construct($plugin) {
		$this->basename = $plugin;
		$this->folder = dirname($plugin);
		//Register general hooks.
		add_action('init', array(&$this, 'load_translations')); // must run before admin_menu
		add_action('admin_init', array(&$this, 'admin_init'));
		add_action('admin_menu', array(&$this, 'admin_menu'));
	}
	
	function requires_32() {
		echo '<div class="error"><p>' . __('<strong>BPTPI FTP FP:</strong> Sorry, This plugin requires WordPress 3.2+. Please upgrade your WordPress installation or deactivate this plugin.', 'bptpi-ftp-fp') . '</p></div>';
	}
	
	function load_translations() {
		//Load any translation files needed:
		load_plugin_textdomain('bptpi-ftp-fp', '', $this->folder . '/langs/');
	}
	
	function admin_init() {

		//Register our JS & CSS
		wp_register_style ('bptpi-ftp-fp', plugins_url( '/bptpi-ftp-fp.css', __FILE__ ), array(), $this->version);

		if ( ! function_exists('submit_button') ) {
			add_action('admin_notices', array(&$this, 'requires_32') );
			return;
		}

		//Enqueue JS & CSS
		add_action('load-media_page_bptpi-ftp-fp', array(&$this, 'add_styles') );
		add_action('media_upload_server', array(&$this, 'add_styles') );

		add_filter('plugin_action_links_' . $this->basename, array(&$this, 'add_configure_link'));

		if ( $this->user_allowed() ) {
			//Add actions/filters
			add_filter('media_upload_tabs', array(&$this, 'tabs'));
			add_action('media_upload_server', array(&$this, 'tab_handler'));
		}
		
		//Register our settings:
		register_setting('bptpi_ftp_fp', 'frmsvr_root', array(&$this, 'sanitize_option_root') );
		//register_setting('bptpi-ftp-fp', 'frmsvr_last_folder');
		register_setting('bptpi_ftp_fp', 'frmsvr_uac');
		register_setting('bptpi_ftp_fp', 'frmsvr_uac_users');
		register_setting('bptpi_ftp_fp', 'frmsvr_uac_role');
		
	}
	
	function admin_menu() {
		if ( ! function_exists('submit_button') )
			return;
		if ( $this->user_allowed() )
			add_media_page( __('Add From Server', 'bptpi-ftp-fp'), __('Add From Server', 'bptpi-ftp-fp'), 'read', 'bptpi-ftp-fp', array(&$this, 'menu_page') );
		add_options_page( __('Add From Server Settings', 'bptpi-ftp-fp'), __('Add From Server', 'bptpi-ftp-fp'), 'manage_options', 'bptpi-ftp-fp-settings', array(&$this, 'options_page') );
	}

	function add_configure_link($_links) {
		$links = array();
		if ( $this->user_allowed() )
			$links[] = '<a href="' . admin_url('upload.php?page=bptpi-ftp-fp') . '">' . __('Import Files', 'bptpi-ftp-fp') . '</a>';
		if ( current_user_can('manage_options') )
			$links[] = '<a href="' . admin_url('options-general.php?page=bptpi-ftp-fp-settings') . '">' . __('Options', 'bptpi-ftp-fp') . '</a>';

		return array_merge($links, $_links);
	}

	//Add a tab to the media uploader:
	function tabs($tabs) {
		if ( $this->user_allowed() )
			$tabs['server'] = __('Add From Server', 'bptpi-ftp-fp');
		return $tabs;
	}
	
	function add_styles() {
		//Enqueue support files.
		if ( 'media_upload_server' == current_filter() )
			wp_enqueue_style('media');
		wp_enqueue_style('bptpi-ftp-fp');
	}

	//Handle the actual page:
	function tab_handler(){
		if ( ! $this->user_allowed() )
			return;

		//Set the body ID
		$GLOBALS['body_id'] = 'media-upload';

		//Do an IFrame header
		iframe_header( __('Add From Server', 'bptpi-ftp-fp') );

		//Add the Media buttons	
		media_upload_header();

		//Handle any imports:
		$this->handle_fp_imports();

		//Do the content
		$this->main_content();

		//Do a footer
		iframe_footer();
	}
	
	function menu_page() {
		if ( ! $this->user_allowed() )
			return;

		//Handle any imports:
		$this->handle_fp_imports();

		echo '<div class="wrap">';
		screen_icon('upload');
		echo '<h2>' . __('Add From Server', 'bptpi-ftp-fp') . '</h2>';

		//Do the content
		$this->main_content();
		
		echo '</div>';
	}

	function options_page() {
		if ( ! current_user_can('manage_options') )
			return;

		include 'class.bptpi-ftp-fp-settings.php';
		$this->settings = new bptpi_ftp_fp_settings($this);
		$this->settings->render();
	}

	function get_root( $context = 'use' ) {
		static $static_root = null;
		if ( $static_root )
			return $static_root;

		$root = get_option('frmsvr_root', false);
		if ( strpos($root, '%') !== false && 'raw' != $context ) {
			$user = wp_get_current_user();

			$root = str_replace('%username%', $user->user_login, $root);
			$root = str_replace('%role%', $user->roles[0], $root);
		}
		if ( false === $root ) {
			$file = __FILE__;
			if ( '/' == $file[0] )
				$root = '/';
			elseif ( preg_match('/(\w:)/i', __FILE__, $root_win_match) )
				$root = $root_win_match[1];
		}

		if ( strlen($root) > 1 )
			$root =  untrailingslashit($root);
		$static_root = $root = strtolower( $root );
		return $root;
	}

	function user_allowed() {
		if ( ! current_user_can('upload_files') )
			return false;

		switch ( get_option('frmsvr_uac', 'allusers') ) {
			case 'allusers':
				return true;
			case 'role':
				$user = wp_get_current_user();
				$roles = $user->roles;
				$allowed_roles = get_option('frmsvr_uac_role', array());
				foreach ( $roles as $r ) {
					if ( in_array($r, $allowed_roles) )
						return true;
				}
				return false;
			case 'listusers':
				$user = wp_get_current_user();
				$allowed_users = explode("\n", get_option('frmsvr_uac_users', ''));
				$allowed_users = array_map('trim', $allowed_users);
				$allowed_users = array_filter($allowed_users);
				return in_array($user->user_login, $allowed_users);
		}
		return false;
	}
	
	function sanitize_option_root($input) {
		$_input = $input;
		if ( 'specific' == $input )
			$input = stripslashes($_POST['frmsvr_root-specified']);
		if ( !$this->validate_option_root( $input ) )
			$input = get_option('frmsvr_root');
		
		$input = strtolower($input);
		$input = str_replace('\\', '/', $input);

		return $input;
	}

	function validate_option_root($o) {
		if ( strpos($o, '%') !== false ) {
			// Ensure only valid placeholders are used:
			if ( preg_match_all('!%(.*?)%!', $o, $placeholders) ) {
				$valid_ph = array('username', 'role');
				foreach ( $placeholders[1] as $ph ) {
					if ( !in_array($ph, $valid_ph) ) {
						add_settings_error('general', 'update_failed', sprintf(__('The placeholder %s is not valid in the root path.', 'bptpi-ftp-fp'),  '%' . $ph . '%'), 'error');
						return false;
					}
				}
				return true;
			}
		}
		if ( !is_dir($o) || !is_readable($o) ) {
			add_settings_error('general', 'update_failed', __('The root path specified could not be read.', 'bptpi-ftp-fp'), 'error');
			return false;
		}
		return true;
	}

    function admin_error($string) {
        ?>
        <div id="message" class="error below-h2">
            <p><?php _e( $string, 'my-text-domain' ); ?></p>
        </div>
    <?php
    }
    //add_action( 'admin_notices', 'my_admin_notice' );


    function check_ini_integrity($ini_array){
        $error = "";
        //print_r($ini_array);
        $term = get_term( $ini_array[scuolaID] , "product_cat" );
        $user = get_userdata( $ini_array[userID] );
        if(($term->slug != $ini_array[scuolaSlug]) || ($ini_array[scuolaSlug]=='')){
            $error = "Il file ini non è configurato correttamente: parametri della scuola";
            return $error;
        }
        if(($user->user_login != $ini_array[userName]) || ($ini_array[userID]=='')){
            $error = "Il file ini non è configurato correttamente: parametri dell'utente";
            return $error;
        }
        
        if($ini_array[prezzoAN]=='' || !is_numeric($ini_array[prezzoAN])){
            $error = "Il file ini non è configurato correttamente: prezzi delle foto, annuario";
            return $error;
        }
        if($ini_array[prezzoCL]=='' || !is_numeric($ini_array[prezzoCL])){
            $error = "Il file ini non è configurato correttamente: prezzi delle foto, classe";
            return $error;
        }
        if($ini_array[prezzoFF]=='' || !is_numeric($ini_array[prezzoFF])){
            $error = "Il file ini non è configurato correttamente: prezzi delle foto, foto focus";
            return $error;
        }
        //$ini_array[prezzoAN] $ini_array[prezzoCL] $ini_array[prezzoFF]
        return $error;
    }
    function check_file_integrity($file){
        //wp_check_filetype( $file, $mimes )
        //@todo migliorare l'acquisizione di $cwd
        $cwd = trailingslashit(stripslashes($_POST['cwd']));

        $filetype = wp_check_filetype( $file);
        if($filetype[type] != "image/jpeg"){
            $error = "Il file $file ha un estensione non conforme a quella richiesta";
            $path = $cwd.'bptpi_import_log.log';
            if(!attach_txt_file($error, $path)){
                echo '<div class="updated error"><p>Non posso scrivere il mio log</p></div>';
            }
            return $error;
        }
        $dati_foto=explode("-",$file);
        //print_r($dati_foto);
        $error = "";
        if(count($dati_foto)<3){
            $error = "Il file $file ha un nome non conforme ai parametri standard"."\r\n";;
            $path = $cwd.'bptpi_import_log.log';
            if(!attach_txt_file($error, $path)){
                echo '<div class="updated error"><p>Non posso scrivere il mio log</p></div>';
            }
            return $error;
        }
        $pt = array("FF","CL","AN");
        if(!in_array($dati_foto[2],$pt)){
            $error = "Il file $file ha un nome non conforme ai parametri standard"."\r\n";;
            $path = $cwd.'bptpi_import_log.log';
            if(!attach_txt_file($error, $path)){
                echo '<div class="updated error"><p>Non posso scrivere il mio log</p></div>';
            }
            return $error;
        }
        return $error;
    }

	//Handle the imports
    //questa è la funzione principale controlla tutto l'import
    function handle_fp_imports() {
        set_time_limit(600);//due ore prima di andare in timeout
        //global $ptp_importer;
        $photos_obj = PTPImporter_Product::getInstance();

        //se esistono dei file
        if ( !empty($_POST['files']) && !empty($_POST['cwd']) ) {

            $files = array_map('stripslashes', $_POST['files']);
            $cwd = trailingslashit(stripslashes($_POST['cwd']));

            //se non mi da errore rinomino e sposto il mio file nella cartella dei file importati
            $done_dir = $cwd."file_importati";
            if ( !file_exists( $done_dir ) ) {
                chmod($cwd, 777);
                if (!mkdir( $done_dir, 0755, true )) {
                    $risultato = "Non sono in grado di creare la directory!";
                    $this->admin_error($risultato);
                    return;
                }
            }

            //leggo e verifico il file di ini
            $ini_files="";
            foreach (glob($cwd."*.ini") as $file) {
                $ini_file = $file;
            }
            if($ini_file==""){
                $err="Attenzione file conf.ini mancante o non ha nome conf.ini";
                $this->admin_error($err);
                return;
            }
            $ini_array = parse_ini_file($ini_file, true);
            //cambio la virgola in punto
            $ini_array[prezzoAN] = str_replace(",",".",$ini_array[prezzoAN]);
            $ini_array[prezzoCL] = str_replace(",",".",$ini_array[prezzoCL]);
            $ini_array[prezzoFF] = str_replace(",",".",$ini_array[prezzoFF]);
            //controllo l'integrità dei dati inseriti nel file ini
            $risultato = $this->check_ini_integrity($ini_array);
            if($risultato!=""){
                $this->admin_error($risultato);
                return;
            }
            //se il file di ini non è buono a questo punto sono giù fuori
            //parametri di configurazione del plugi originale
            $post_id = isset($_REQUEST['post_id']) ? intval($_REQUEST['post_id']) : 0;
            $import_date = isset($_REQUEST['import-date']) ? $_REQUEST['import-date'] : 'file';

            $import_to_gallery = isset($_POST['gallery']) && 'on' == $_POST['gallery'];
            if ( ! $import_to_gallery && !isset($_REQUEST['cwd']) )
                $import_to_gallery = true; // cwd should always be set, if it's not, and neither is gallery, this must be the first page load.

            if ( ! $import_to_gallery )
                $post_id = 0;

            flush();
            wp_ob_end_flush_all();

            //ciclo sui file ed eseguo tutte le operazioni del caso
            foreach ( (array)$files as $file ) {
                sleep(0.1);//ad ogni ciclo do un decimo di secondo di pace al server
                //effettuo un check della correttezza del nome del file se il file non va bene per essere caricato allora passo al file successivo
                $filename = $cwd . $file;
                $dati_foto = explode("-",$file);
                $risultato = $this->check_file_integrity($file);
                if($risultato!=""){
                    $this->admin_error($risultato);
                    return;
                }
                //se è l'annuario...
                //per ora faccio tutto mano!
                //if($dati_foto[2]=='AN'){}

                //effettua l'import del file senza creare il watermark e usa delle dimensioni che non so...
                //effettua upload...
                //chiama handle_fp_import_file che si occupa di sanitize il file, crea le thumb utili (credo) e sposta il file nella cartella di upload
                //crea il post, genera i meta
                $response = $this->upload_file($cwd,$file);

                //prendo la scuola come da configurazione ini e creo una classe se non esiste, se esiste prendo solo l'id
                $scuola = get_term_by('id', $ini_array[scuolaID], 'product_cat');
                $slug_classe = strtolower($dati_foto[1]."-".$scuola->slug);
                $classe_scuola = get_term_by('slug', $slug_classe, 'product_cat');
                if(!$classe_scuola){
                    $args = array(
                            'slug' => $slug_classe,
                            'parent'=> $ini_array[scuolaID]
                    );
                    $inserted_term = wp_insert_term( $dati_foto[1], "product_cat", $args );
                    $classe_scuola = get_term_by('id', $inserted_term[term_id], 'product_cat');
                }
                //creo variation group (che determina il prezzo)
                switch($dati_foto[2]){
                    case "FF":
                        $variation_group = "8";//foto focus
                        $variation_group_price = $ini_array[prezzoFF];
                        break;
                    case "CL":
                        $variation_group = "34";//foto di classe
                        $variation_group_price = $ini_array[prezzoCL];
                        break;
                    case "AN":
                        $variation_group = "35";//annuario
                        $variation_group_price = $ini_array[prezzoAN];
                        break;
                }

                //qui devo aggiungere il codice per fare l'upload secondo le modalità di btpti
                $photos_obj->get_file( $response['file_id'] );

                $nonce  = wp_create_nonce( 'ptp_nonce' );
                $val_for_creation = array (
                    'ptp_nonce' => $nonce,
                    '_wp_http_referer' => '/wp-admin/admin.php?page=ptp_bulk_import',
                    'term_id' => $classe_scuola->term_id,
                    'variation_group' => $variation_group,
                    'variation_group_price' => $variation_group_price,
                    'password_protect' => 'No',
                    'action' => 'ptp_product_import',
                    'attachments' => array('0' => $response[file_id]),
                    'titles' => array($response[file_id] => ''),
                    'userID' => $ini_array[userID]
                );

                $output = $this->product_import($val_for_creation);

                // Rename original file
                rename( $filename , "{$done_dir}/{$file}" );


                if ( is_wp_error($id) ) {
                    echo '<div class="updated error"><p>' . sprintf(__('<em>%s</em> was <strong>not</strong> imported due to an error: %s', 'bptpi-ftp-fp'), esc_html($file), $id->get_error_message() ) . '</p></div>';
                } else {
                    //increment the gallery count
                    if ( $import_to_gallery ){
                        echo "<script type='text/javascript'>jQuery('#attachments-count').text(1 * jQuery('#attachments-count').text() + 1);</script>";
                        echo '<div class="updated"><p>' . sprintf(__('<em>%s</em> has been added to Media library', 'bptpi-ftp-fp'), esc_html($file)) . '</p></div>';
                    }
                    //function write_txt_file($content, $path, $has_sections=FALSE)
                    $content = esc_html($file). " è stato aggiunto ai tuoi prodotti!"."\r\n";
                    $path = $cwd.'bptpi_import_log.log';
                    if(!attach_txt_file($content, $path)){
                        echo '<div class="updated error"><p>Non posso scrivere il mio log</p></div>';
                    }
                    }
                flush();
                wp_ob_end_flush_all();
            }
        }
    }

    public function product_import($val_for_creation) {
        //check_ajax_referer( 'ptp_product_import', 'ptp_nonce' );//torna meno uno poverino...

        $photos_obj = PTPImporter_Product::getInstance();

        //sostituisco $_POST con $val_to_post
        $post_ids = $this->create($val_for_creation);//riscrivo la funzione perchè così posso mettere lo user che voglio io (da ini)!!!
        //$post_ids = $photos_obj->create($val_for_creation);//devo solo scoprire quali sono i valori da passare

        if ( !$post_ids ) {
            /*si ma questo non è più un file ajax
            echo json_encode( array(
                'success' => false,
                'error' => $post_ids
            ) );*/

            exit;
        }

        global $ptp_importer;
        $groups = (array) ptp_get_term_meta( $val_to_post['term_id'], $ptp_importer->term_variation_groups_meta_key, true );
        if( !in_array( $val_to_post['variation_group'], $groups ) ) {
            $groups[] = $val_to_post['variation_group'];
        }
        ptp_update_term_meta( $val_to_post['term_id'], $ptp_importer->term_variation_groups_meta_key, $groups );

        // Associate variation group with term
        ptp_update_term_meta( $val_to_post['term_id'], $ptp_importer->term_variation_group_meta_key, $val_to_post['variation_group'] );

        do_action( 'ptp_product_import_complete', $val_to_post );

        /*si ma questo non è più un file ajax
         * echo json_encode( array(
            'success' => true
        ) );*/

        //da dove chiamo dovrei ritornare qualcosa...
        //exit;
    }

    /**
     * Upload a file and insert as attachment
     *
     * @param int $post_id
     * @return int|bool
     */
    //invento alcuni parametri da rivedere con calma inserendo i parametri reali!!!
    public function upload_file($cwd, $file) {

        $uploaded_file = $this->handle_fp_import_file( $cwd.$file, array('test_form' => false) );

            $file_loc = $uploaded_file[file];
            $file_name = basename($uploaded_file[file]);
            $file_type = wp_check_filetype( $uploaded_file[file] );

            $attachment = array(
                'post_mime_type' => $file_type['type'],
                'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $uploaded_file[file] ) ),
                'post_content' => '',
                'post_status' => 'inherit'
            );

            global $ptp_importer;


//wp_insert_attachment( $attachment, $filename, $parent_post_id );
//Parameters
//$attachment
//(array) (required) Array post_title, post_content (empty string), post_status and post_mime_type
//wp_generate_attachment_metadata qui abbiamo un'add_action che mi fagenerare il file con il watermark!

            $attach_id = wp_insert_attachment( $attachment, $uploaded_file[file] );
            update_post_meta( $attach_id, $ptp_importer->attachment_meta_key, 'yes' );
            $attach_data = wp_generate_attachment_metadata( $attach_id, $file_loc );
            //provo a sostituire deirettamente con il nome del mio add action
            //$attach_data = ptp_generate_watermaked_images( $attach_id, $file_loc );
            wp_update_attachment_metadata( $attach_id, $attach_data );
            return array( 'success' => true, 'variations' => $variations, 'file_id' => $attach_id );
        //}

        //return array( 'success' => false, 'error' => $_FILES['ptp_attachment']['name'] );
    }

    /**
     * Insert a new post
     *
     * @param array $posted
     * @return array $post_ids
     */
    //creazione foto
    public function create( $posted ) {
        global $ptp_importer;
        $photos_obj = PTPImporter_Product::getInstance();

        $settings_obj = PTPImporter_Settings::getInstance();
        $settings = $settings_obj->get();
        $post_ids = array();

        foreach ( $posted['attachments'] as $file_id ) {
            $file_data = $photos_obj->get_file( $file_id );

            $post = array(
                'post_title' => $posted['titles'][$file_id] ? $posted['titles'][$file_id] : $file_data['name'],
                'post_content' => '',
                'post_type' => 'product',
                'post_status' => 'publish',
                'post_author' => $posted['userID'],
                'tax_input' => array(
                    'product_type' => array( ptp_product_type_term_id() ),
                    'product_cat' => array( $posted['term_id'] )
                )
            );

            // Create product
            $post_id = wp_insert_post( $post );
            $post_ids[] = $post_id;

            // Form product metadata
            $metadata = ptp_product_metadata_defaults();

            // Attach event date to this product
            $metadata[ $ptp_importer->event_date_meta_key ] = date( 'Y-m-d H:i:s', strtotime( $posted['date'] ) );

            // Add meta that determines if this product is imported by this plugin
            $metadata['_ptp_product'] = 'yes';
            // Record attachment id for later use
            $metadata['_ptp_attchement_id'] = $file_id;
            // Record the variation group id
            $metadata['_ptp_variation_group_id'] = $posted['variation_group'];

            //var_dump($metadata);
            // Update metadata
            foreach ( $metadata as $key => $value ) {
                update_post_meta( $post_id, $key, $value );
            }

            // Set file as the post thumbnail for the product
            set_post_thumbnail( $post_id, $file_id );

            // Create variations(child products) for this grouped product
            $this->create_variations( $posted['variation_group'], $post_id, $posted['userID'], $file_data, $posted['variation_group_price'] );

            if(isset($posted['assoc'][$file_id]))
            {
                unset($posted['users']);
                $posted['users'] = $posted['assoc'][$file_id];
                unset($posted['assoc']);
            }

            do_action( 'ptp_create_products_complete', $post_id, $posted['term_id'], $posted['users']);

            sleep(intval($settings['interval']));
        }

        return $post_ids;
    }



    /**
     * Create variations
     *
     * @param int $term_id
     * @param int $parent_id
     * @param array $file_data
     * @return array $post_ids
     */
    public function create_variations( $term_id, $parent_id, $current_user, $file_data, $variation_group_price ) {
        $post_ids = array();

        $variation_obj = PTPImporter_Variation_Group::getInstance();
        $group = $variation_obj->group( $term_id );

        foreach ( $group->variations as $variation ) {
            $post = array(
                'post_title' => $variation['name'],
                'post_content' => '',
                'post_type' => 'product',
                'post_status' => 'publish',
                'post_author' => $current_user,
                'post_parent' => $parent_id,
            );

            // Create product
            $post_id = wp_insert_post( $post );
            $post_ids[] = $post_id;

            // Form product metadata
            $metadata = ptp_product_metadata_defaults();
            //echo 'variation_group_price'.$variation_group_price;exit();
            // Set price
            $metadata['_price'] = $variation_group_price;//$variation['price'];
            // Set regular price
            $metadata['_regular_price'] = $variation_group_price;//$variation['price'];
            // Set visibility to blank so it won't be displayed
            $metadata['_visibility'] = '';
            // Add meta that determines if this product is used as variation for a grouped data
            $metadata['_ptp_as_variation'] = 'yes';
            // Add SKU
            $metadata['_sku'] = uniqid();

            if ( $variation['name'] == 'Downloadable' || $variation['name'] == 'downloadable' ) {
                $uploads_dir = wp_upload_dir();
                $file_path = $uploads_dir['baseurl'] . '/woocommerce_uploads'  . $uploads_dir['subdir'] . '/downloadable_' . basename( $file_data['url'] );

                // Set as downloadable
                $metadata['_downloadable'] = 'yes';
                // Set as virtual'
                $metadata['_virtual'] = 'yes';

                // Set download path
                $metadata['_downloadable_files'] = array( md5($file_path) => array( "name" => basename( $file_data['url'] ), "file" => $file_path ));
                // Set download limit
                $metadata['_download_limit'] = '';
                // Set download expiry
                $metadata['_download_expiry'] = '';
            }

            // Update metadata
            foreach ( $metadata as $key => $value ) {
                update_post_meta( $post_id, $key, $value );
            }
        }

        return $post_ids;
    }

    function write_ini_file($assoc_arr, $path, $has_sections=FALSE) {
        $content = "";
        if ($has_sections) {
            foreach ($assoc_arr as $key=>$elem) {
                $content .= "[".$key."]\n";
                foreach ($elem as $key2=>$elem2) {
                    if(is_array($elem2))
                    {
                        for($i=0;$i<count($elem2);$i++)
                        {
                            $content .= $key2."[] = \"".$elem2[$i]."\"\n";
                        }
                    }
                    else if($elem2=="") $content .= $key2." = \n";
                    else $content .= $key2." = \"".$elem2."\"\n";
                }
            }
        }
        else {
            foreach ($assoc_arr as $key=>$elem) {
                if(is_array($elem))
                {
                    for($i=0;$i<count($elem);$i++)
                    {
                        $content .= $key."[] = \"".$elem[$i]."\"\n";
                    }
                }
                else if($elem=="") $content .= $key." = \n";
                else $content .= $key." = \"".$elem."\"\n";
            }
        }

        if (!$handle = fopen($path, 'a+')) {
            return false;
        }

        $success = fwrite($handle, $content);
        fclose($handle);

        return $success;
    }


    //il mio import (non genera nessun post!)
    function handle_fp_import_file($file, $post_id = 0, $import_date = 'file') {
        set_time_limit(120);

        // Initially, Base it on the -current- time.
        $time = current_time('mysql', 1);
        // Next, If it's post to base the upload off:
        if ( 'post' == $import_date && $post_id > 0 ) {
            $post = get_post($post_id);
            if ( $post && substr( $post->post_date_gmt, 0, 4 ) > 0 )
                $time = $post->post_date_gmt;
        } elseif ( 'file' == $import_date ) {
            $time = gmdate( 'Y-m-d H:i:s', @filemtime($file) );
        }

        // A writable uploads dir will pass this test. Again, there's no point overriding this one.
        if ( ! ( ( $uploads = wp_upload_dir($time) ) && false === $uploads['error'] ) )
            return new WP_Error( 'upload_error', $uploads['error']);

        $wp_filetype = wp_check_filetype( $file, null );

        extract( $wp_filetype );

        if ( ( !$type || !$ext ) && !current_user_can( 'unfiltered_upload' ) )
            return new WP_Error('wrong_file_type', __( 'Sorry, this file type is not permitted for security reasons.' ) ); //A WP-core string..

        //Is the file already in the uploads folder?
        if ( preg_match('|^' . preg_quote(str_replace('\\', '/', $uploads['basedir'])) . '(.*)$|i', $file, $mat) ) {

            $filename = basename($file);
            $new_file = $file;

            $url = $uploads['baseurl'] . $mat[1];

            $attachment = get_posts(array( 'post_type' => 'attachment', 'meta_key' => '_wp_attached_file', 'meta_value' => ltrim($mat[1], '/') ));
            if ( !empty($attachment) )
                return new WP_Error('file_exists', __( 'Sorry, That file already exists in the WordPress media library.', 'bptpi-ftp-fp' ) );

            //Ok, Its in the uploads folder, But NOT in WordPress's media library.
            if ( 'file' == $import_date ) {
                $time = @filemtime($file);
                if ( preg_match("|(\d+)/(\d+)|", $mat[1], $datemat) ) { //So lets set the date of the import to the date folder its in, IF its in a date folder.
                    $hour = $min = $sec = 0;
                    $day = 1;
                    $year = $datemat[1];
                    $month = $datemat[2];

                    // If the files datetime is set, and it's in the same region of upload directory, set the minute details to that too, else, override it.
                    if ( $time && date('Y-m', $time) == "$year-$month" )
                        list($hour, $min, $sec, $day) = explode(';', date('H;i;s;j', $time) );

                    $time = mktime($hour, $min, $sec, $month, $day, $year);
                }
                $time = gmdate( 'Y-m-d H:i:s', $time);

                // A new time has been found! Get the new uploads folder:
                // A writable uploads dir will pass this test. Again, there's no point overriding this one.
                if ( ! ( ( $uploads = wp_upload_dir($time) ) && false === $uploads['error'] ) )
                    return new WP_Error( 'upload_error', $uploads['error']);
                $url = $uploads['baseurl'] . $mat[1];
            }
        } else {
            //se il file non è nell'upload dir (questo il mio caso attuale!!!)
            $filename = wp_unique_filename( $uploads['path'], basename($file));

            // copy the file to the uploads dir - ECCO QUI CAMBIA dovrei fare l'upload nella directory NON SCARICABILE
            $new_file = $uploads['path'] . '/' . $filename;
            if ( false === @copy( $file, $new_file ) )
                return new WP_Error('upload_error', sprintf( __('The selected file could not be copied to %s.', 'bptpi-ftp-fp'), $uploads['path']) );

            // Set correct file permissions
            $stat = stat( dirname( $new_file ));
            $perms = $stat['mode'] & 0000666;
            @ chmod( $new_file, $perms );
            // Compute the URL
            $url = $uploads['url'] . '/' . $filename;

            if ( 'file' == $import_date )
                $time = gmdate( 'Y-m-d H:i:s', @filemtime($file));
        }

        //Apply upload filters
        $return = apply_filters( 'handle_import_file', array( 'file' => $new_file, 'url' => $url, 'type' => $type ) );
        $new_file = $return['file'];
        $url = $return['url'];
        $type = $return['type'];

        $title = preg_replace('!\.[^.]+$!', '', basename($file));
        $content = '';

        // use image exif/iptc data for title and caption defaults if possible
        if ( $image_meta = @wp_read_image_metadata($new_file) ) {
            if ( '' != trim($image_meta['title']) )
                $title = trim($image_meta['title']);
            if ( '' != trim($image_meta['caption']) )
                $content = trim($image_meta['caption']);
        }

        if ( $time ) {
            $post_date_gmt = $time;
            $post_date = $time;
        } else {
            $post_date = current_time('mysql');
            $post_date_gmt = current_time('mysql', 1);
        }

        // Construct the attachment array

        $uploaded_file = array(
            'file' => $new_file,
            'url' => $url,
            'type' => $type,
            'post_mime_type' => $type,
            'guid' => $url,
            'post_parent' => $post_id,
            'post_title' => $title,
            'post_name' => $title,
            'post_content' => $content,
            'post_date' => $post_date,
            'post_date_gmt' => $post_date_gmt
        );
        //[file] => /home/federico/public_html/btsb.bnj.xyz/wp-content/uploads/2015/03/stixxxx1.jpg
        //[url] => http://btsb.bnj.xyz/wp-content/uploads/2015/03/stixxxx1.jpg
        //[type] => image/jpeg
        return $uploaded_file;
    }

	//Create the content for the page
	function main_content() {
		global $pagenow;
		$post_id = isset($_REQUEST['post_id']) ? intval($_REQUEST['post_id']) : 0;
		$import_to_gallery = isset($_POST['gallery']) && 'on' == $_POST['gallery'];
		if ( ! $import_to_gallery && !isset($_REQUEST['cwd']) )
			$import_to_gallery = true; // cwd should always be set, if it's not, and neither is gallery, this must be the first page load.
		$import_date = isset($_REQUEST['import-date']) ? $_REQUEST['import-date'] : 'file';

		if ( 'upload.php' == $pagenow )
			$url = admin_url('upload.php?page=bptpi-ftp-fp');
		else
			$url = admin_url('media-upload.php?tab=server');

		if ( $post_id )
			$url = add_query_arg('post_id', $post_id, $url);

		$cwd = trailingslashit(get_option('frmsvr_last_folder', WP_CONTENT_DIR));

		if ( isset($_REQUEST['directory']) ) 
			$cwd .= stripslashes(urldecode($_REQUEST['directory']));

		if ( isset($_REQUEST['adirectory']) && empty($_REQUEST['adirectory']) )
			$_REQUEST['adirectory'] = '/'; //For good measure.

		if ( isset($_REQUEST['adirectory']) )
			$cwd = stripslashes(urldecode($_REQUEST['adirectory']));

		$cwd = preg_replace('![^/]*/\.\./!', '', $cwd);
		$cwd = preg_replace('!//!', '/', $cwd);

		if ( ! is_readable($cwd) && is_readable( $this->get_root() . '/' . ltrim($cwd, '/') ) )
			$cwd = $this->get_root() . '/' . ltrim($cwd, '/');

		if ( ! is_readable($cwd) && get_option('frmsvr_last_folder') )
			$cwd = get_option('frmsvr_last_folder');

		if ( ! is_readable($cwd) )
			$cwd = WP_CONTENT_DIR;

		if ( strpos($cwd, $this->get_root()) === false )
			$cwd = $this->get_root();

		$cwd = str_replace('\\', '/', $cwd);

		if ( strlen($cwd) > 1 )
			$cwd = untrailingslashit($cwd);

		if ( ! is_readable($cwd) ) {
			echo '<div class="error"><p>';
			_e('<strong>Error:</strong> This users root directory is not readable. Please have your site administrator correct the <em>Add From Server</em> root directory settings.', 'bptpi-ftp-fp');
			echo '</p></div>';
			return;
		}

		update_option('frmsvr_last_folder', $cwd);

		$files = $this->find_files($cwd, array('levels' => 1));

		$parts = explode('/', ltrim(str_replace($this->get_root(), '/', $cwd), '/'));
		if ( $parts[0] != '' )
			$parts = array_merge(array(''), $parts);
		$dir = $cwd;
		$dirparts = '';
		for ( $i = count($parts)-1; $i >= 0; $i-- ) {
			$piece = $parts[$i];
			$adir = implode('/', array_slice($parts, 0, $i+1));
			if ( strlen($adir) > 1 )
				$adir = ltrim($adir, '/');
			$durl = esc_url(add_query_arg(array('adirectory' => $adir ), $url));
			$dirparts = '<a href="' . $durl . '">' . $piece . DIRECTORY_SEPARATOR . '</a>' . $dirparts; 
			$dir = dirname($dir);
		}
		unset($dir, $piece, $adir, $durl);

		?>
		<div class="frmsvr_wrap">
		<p><?php printf(__('<strong>Current Directory:</strong> <span id="cwd">%s</span>', 'bptpi-ftp-fp'), $dirparts) ?></p>
		<?php 
			$quickjumps = array();
			$quickjumps[] = array( __('WordPress Root', 'bptpi-ftp-fp'), ABSPATH );
			if ( ( $uploads = wp_upload_dir() ) && false === $uploads['error'] )
				$quickjumps[] = array( __('Uploads Folder', 'bptpi-ftp-fp'), $uploads['path']);
			$quickjumps[] = array( __('Content Folder', 'bptpi-ftp-fp'), WP_CONTENT_DIR );

			$quickjumps = apply_filters('frmsvr_quickjumps', $quickjumps);

			if ( ! empty($quickjumps) ) {
				$pieces = array();
				foreach( $quickjumps as $jump ) {
					list( $text, $adir ) = $jump;
					$adir = str_replace('\\', '/', strtolower($adir));
					if ( strpos($adir, $this->get_root()) === false )
						continue;
					$adir = preg_replace('!^' . preg_quote($this->get_root(), '!') . '!i', '', $adir);
					if ( strlen($adir) > 1 )
						$adir = ltrim($adir, '/');
					$durl = add_query_arg(array('adirectory' => rawurlencode($adir)), $url);
					$pieces[] = "<a href='$durl'>$text</a>";
				}
				if ( ! empty($pieces) ) {
					echo '<p>';
					printf( __('<strong>Quick Jump:</strong> %s', 'bptpi-ftp-fp'), implode(' | ', $pieces) );
					echo '</p>';
				}
			}
		 ?>
         <?php
         $file = $cwd.'/bptpi_import_log.log';

         // the following line prevents the browser from parsing this as HTML.
         header('Content-Type: text/plain');

         // get the file contents, assuming the file to be readable (and exist)
         $contents = file_get_contents($file);

         ?>
            <div class="wrapper-log">
                <div class="log">
                    <pre><?php echo $contents;?></pre>
                </div>
            </div>

        <style type="text/css">
            .widefat td, .widefat th {
                padding: 0;
            }
            .widefat td, .widefat td ol, .widefat td p, .widefat td ul {
                font-size: 10px;
                line-height: 1em;
            }
            th.check-column input, th.check-column, .check-column{
                margin:1px 1px 1px 1px ;
                padding:0;
            }
            .widefat td a{
                font-size: 13px;
                line-height: 1.5em;
            }
            .widefat tbody th.check-column, .widefat tfoot th.check-column, .widefat thead th.check-column {
                padding: 0px 0px 0px 15px ;
            }
        </style>
		<form method="post" action="<?php echo $url ?>">
         <?php if ( 'media-upload.php' == $GLOBALS['pagenow'] && $post_id > 0 ) : ?>
		<p><?php printf(__('Once you have selected files to be imported, Head over to the <a href="%s">Media Library tab</a> to add them to your post.', 'bptpi-ftp-fp'), esc_url(admin_url('media-upload.php?type=image&tab=library&post_id=' . $post_id)) ); ?></p>
        <?php endif; ?>
		<table class="widefat">
		<thead>
			<tr>
				<th class="check-column"><input type='checkbox' /></th>
				<th><?php _e('File', 'bptpi-ftp-fp'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		$parent = dirname($cwd);
		if ( (strpos($parent, $this->get_root()) === 0) && is_readable($parent) ) :
			$parent = preg_replace('!^' . preg_quote($this->get_root(), '!') . '!i', '', $parent);
		?>
			<tr>
				<td>&nbsp;</td>
				<?php /*  <td class='check-column'><input type='checkbox' id='file-<?php echo $sanname; ?>' name='files[]' value='<?php echo esc_attr($file) ?>' /></td> */ ?>
				<td><a href="<?php echo add_query_arg(array('adirectory' => rawurlencode($parent)), $url) ?>" title="<?php echo esc_attr(dirname($cwd)) ?>"><?php _e('Parent Folder', 'bptpi-ftp-fp') ?></a></td>
			</tr>
		<?php endif; ?>
		<?php
			$directories = array();
			foreach( (array)$files as $key => $file ) {
				if ( '/' == substr($file, -1) ) {
					$directories[] = $file;
					unset($files[$key]);
				}
			}

			sort($directories);
			sort($files);
			
			foreach( (array)$directories as $file  ) :
				$filename = preg_replace('!^' . preg_quote($cwd) . '!i', '', $file);
				$filename = ltrim($filename, '/');
				$folder_url = add_query_arg(array('directory' => rawurlencode($filename), 'import-date' => $import_date, 'gallery' => $import_to_gallery ), $url);
		?>
			<tr>
				<td>&nbsp;</td>
				<?php /* <td class='check-column'><input type='checkbox' id='file-<?php echo $sanname; ?>' name='files[]' value='<?php echo esc_attr($file) ?>' /></td> */ ?>
				<td><a href="<?php echo $folder_url ?>"><?php echo esc_html( rtrim($filename, '/') . DIRECTORY_SEPARATOR ); ?></a></td>
			</tr>
		<?php
			endforeach;
			$names = $rejected_files = $unreadable_files = array();
			$unfiltered_upload = current_user_can( 'unfiltered_upload' );
			foreach ( (array)$files as $key => $file ) {
				if ( ! $unfiltered_upload ) {
					$wp_filetype = wp_check_filetype( $file );
					if ( false === $wp_filetype['type'] ) {
						$rejected_files[] = $file;
						unset($files[$key]);
						continue;
					}
				}
				if ( ! is_readable($file) ) {
					$unreadable_files[] = $file;
					unset($files[$key]);
					continue;
				}
			}
			
			foreach ( array( 'meets_guidelines' => $files, 'unreadable' => $unreadable_files, 'doesnt_meets_guidelines' => $rejected_files) as $key => $_files ) :
			$file_meets_guidelines = $unfiltered_upload || ('meets_guidelines' == $key);
			$unreadable = 'unreadable' == $key;
			foreach ( $_files as $file  ) :
				$classes = array();

				if ( ! $file_meets_guidelines )
					$classes[] = 'doesnt-meet-guidelines';
				if ( $unreadable )
					$classes[] = 'unreadable';

				if ( preg_match('/\.(.+)$/i', $file, $ext_match) ) 
					$classes[] = 'filetype-' . $ext_match[1];

				$filename = preg_replace('!^' . preg_quote($cwd) . '!', '', $file);
				$filename = ltrim($filename, '/');
				$sanname = preg_replace('![^a-zA-Z0-9]!', '', $filename);

				$i = 0;
				while ( in_array($sanname, $names) )
					$sanname = preg_replace('![^a-zA-Z0-9]!', '', $filename) . '-' . ++$i;
				$names[] = $sanname;
		?>
			<tr class="<?php echo esc_attr(implode(' ', $classes)); ?>" title="<?php if ( ! $file_meets_guidelines ) { _e('Sorry, this file type is not permitted for security reasons. Please see the FAQ.', 'bptpi-ftp-fp'); } elseif ($unreadable) { _e('Sorry, but this file is unreadable by your Webserver. Perhaps check your File Permissions?', 'bptpi-ftp-fp'); } ?>">
				<th class='check-column'><input type='checkbox' id='file-<?php echo $sanname; ?>' name='files[]' value='<?php echo esc_attr($filename) ?>' <?php disabled(!$file_meets_guidelines || $unreadable); //@todo commento brutalmente perchè non trovo le guidelines ?> /></th>
				<td><label for='file-<?php echo $sanname; ?>'><?php echo esc_html($filename) ?></label></td>
			</tr>
			<?php endforeach; endforeach;?>
		</tbody>
		<tfoot>
			<tr>
				<th class="check-column"><input type='checkbox' /></th>
				<th><?php _e('File', 'bptpi-ftp-fp'); ?></th>
			</tr>
		</tfoot>
		</table>

		<fieldset>
			<legend><?php _e('Import Options', 'bptpi-ftp-fp'); ?></legend>
	
		<?php if ( $post_id != 0 ) : ?>
			<input type="checkbox" name="gallery" id="gallery-import" <?php checked( $import_to_gallery ); ?> /> <label for="gallery-import"><?php _e('Attach imported files to this post', 'bptpi-ftp-fp')?></label>
			<br class="clear" />
		<?php endif; ?>
			<?php _e('Set the imported date to the', 'bptpi-ftp-fp'); ?>
			<input type="radio" name="import-date" id="import-time-currenttime" value="current" <?php checked('current', $import_date); ?> /> <label for="import-time-currenttime"><?php _e('Current Time', 'bptpi-ftp-fp'); ?></label>
			<input type="radio" name="import-date" id="import-time-filetime" value="file" <?php checked('file', $import_date); ?> /> <label for="import-time-filetime"><?php _e('File Time', 'bptpi-ftp-fp'); ?></label>
			<?php if ( $post_id != 0 ) : ?>
			<input type="radio" name="import-date" id="import-time-posttime" value="post" <?php checked('post', $import_date); ?> /> <label for="import-time-posttime"><?php _e('Post Time', 'bptpi-ftp-fp'); ?></label>
			<?php endif; ?>
		</fieldset>
		<br class="clear" />
		<input type="hidden" name="cwd" value="<?php echo esc_attr( $cwd ); ?>" />
		<?php submit_button( __('Import', 'bptpi-ftp-fp'), 'primary', 'import', false); ?>
		</form>
		</div>
	<?php
	}

	//HELPERS	
	function find_files( $folder, $args = array() ) {

		if ( strlen($folder) > 1 )
			$folder = untrailingslashit($folder);
	
		$defaults = array( 'pattern' => '', 'levels' => 100, 'relative' => '' );
		$r = wp_parse_args($args, $defaults);
	
		extract($r, EXTR_SKIP);
		
		//Now for recursive calls, clear relative, we'll handle it, and decrease the levels.
		unset($r['relative']);
		--$r['levels'];
	
		if ( ! $levels )
			return array();
		
		if ( ! is_readable($folder) )
			return array();
	
		$files = array();
		if ( $dir = @opendir( $folder ) ) {
			while ( ( $file = readdir($dir) ) !== false ) {
				if ( in_array($file, array('.', '..') ) )
					continue;
				if ( is_dir( $folder . '/' . $file ) ) {
					$files2 = $this->find_files( $folder . '/' . $file, $r );
					if( $files2 )
						$files = array_merge($files, $files2 );
					else if ( empty($pattern) || preg_match('|^' . str_replace('\*', '\w+', preg_quote($pattern)) . '$|i', $file) )
						$files[] = $folder . '/' . $file . '/';
				} else {
					if ( empty($pattern) || preg_match('|^' . str_replace('\*', '\w+', preg_quote($pattern)) . '$|i', $file) )
						$files[] = $folder . '/' . $file;
				}
			}
		}
		@closedir( $dir );
	
		if ( ! empty($relative) ) {
			$relative = trailingslashit($relative);
			foreach ( $files as $key => $file )
				$files[$key] = preg_replace('!^' . preg_quote($relative) . '!', '', $file);
		}
	
		return $files;
	}

}//end class

?>
