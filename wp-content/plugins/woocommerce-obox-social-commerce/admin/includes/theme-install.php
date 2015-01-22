<?php function oboxfb_theme_upload(){
	/****************/
	/* File details */
	$input = $_FILES["new_theme"];
	$file = str_replace(".zip", "", $input["name"]);
	$file_type = $input["type"];
	if(!strpos($file_type, "zip")) :
		die("File type not valid");
	endif;
	
	//Upload
	$file_upload = new File_Upload_Upgrader('new_theme', 'package');

	//Get the new updload
	$package = $file_upload->package;
	/********************************/
	/* Use our Theme_Upgrader class */
	$plugin_upgrade = new OBOXFB_Theme_Upgrader( new Theme_Installer_Skin( compact('type', 'title', 'nonce', 'url') ) );
	
	$show_progress = $plugin_upgrade->install($package, $file);
	
	if ( is_wp_error($show_progress) ) :
		$error = $show_progress->get_error_message();
		die($error);
	endif;
	
	/******************************************/
	/* Fetch the theme list and the new theme */
	$themes = get_oboxfb_themes(OBOXFBDIR."themes");
	$new_theme = get_theme_data(OBOXFBDIR."themes/".$file."/style.css");
	$latest_theme = $new_theme['Title'];
	
	
	/*****************************************/
	/* Set the Template Details as Variables */
	$template = $themes[$latest_theme]['Template'];
	$stylesheet = $themes[$latest_theme]['Stylesheet'];
	$title = $themes[$latest_theme]['Title'];
	$version = $themes[$latest_theme]['Version'];
	$description = $themes[$latest_theme]['Description'];
	$author = $themes[$latest_theme]['Author'];
	$screenshot = $themes[$latest_theme]['Screenshot'];
	$theme_root = $themes[$latest_theme]['Theme Root'];
	$theme_root_uri = $themes[$latest_theme]['Theme Root URI'];
	
	
	/***************************************/
	/* Set the Preview Link for this Theme */
	$preview_link = esc_url(get_option('home') . '/');
	if ( is_ssl() ) : $preview_link = str_replace( 'http://', 'https://', $preview_link ); endif;
	$preview_link = htmlspecialchars( add_query_arg( array('site_switch' => 'mobile', 'preview' => 1, 'template' => $template, 'stylesheet' => urlencode($stylesheet), 'TB_iframe' => 'true'), $preview_link ) );
	$preview_text = esc_attr( sprintf( __('Preview of &#8220;%s&#8221;','ocmx'), $title ) );
	$thickbox_class = 'thickbox thickbox-preview';
	
	
	/****************************/
	/* Set the Activation Links */
	$activate_link = admin_url("admin.php?page=mobile-themes&action=activate&amp;template=".urlencode($template)."&amp;stylesheet=".urlencode($stylesheet));
	$activate_text = esc_attr( sprintf( __('Activate %s','ocmx'), $title ) );
	$actions = array();
	$actions[] = '<a href="' . $preview_link . '" class="thickbox thickbox-preview" title="' . esc_attr(sprintf(__('Preview %s','ocmx'), $theme_name)) . '">' . __('Preview','ocmx') . '</a>';
	$actions[] = '<a href="' . $activate_link .  '" class="activatelink" title="' . $activate_text . '">' . __('Activate','ocmx') . '</a>';
	
	
	/*********************/
	/* Compile the links */
	$actions = apply_filters('theme_action_links', $actions, $theme_data);
	$actions = implode ( ' ', $actions ); ?>
    <a href="<?php echo $preview_link; ?>" class="<?php echo $thickbox_class; ?> screenshot">
		<img src="<?php echo $theme_root_uri . '/' . $stylesheet . '/' . $screenshot; ?>" alt="" />
    </a>
    <div class="new-bubble"><span><?php _e("New","ocmx"); ?></span></div>
    <h4><?php printf(__('%1$s %2$s by %3$s', 'ocmx'), $title, $version, $author) ; ?></h4>
    <div class="theme-functions"><?php echo $actions ?></div>
    <div id="delete-theme-<?php echo $stylesheet; ?>" class="no_display">
        <a href="#" class="remove-link"><?php _e("Delete Theme","ocmx"); ?></a>
    </div>
<?php die("");
} ?>