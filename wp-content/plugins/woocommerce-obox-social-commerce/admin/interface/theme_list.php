<?php
function oboxfb_theme_list(){
	global $ocmx_oboxfb_class;
	
	if ( current_user_can('switch_themes') && isset($_GET['action']) ) {
		if ( 'activate' == $_GET['action'] ) {
			update_option("ocmx_oboxfb_theme", $_GET['template']);
			update_option("ocmx_oboxfb_stylesheet", $_GET['stylesheet']);
		}
		else if ( 'delete' == $_GET['action'] ) {
			check_admin_referer('delete-theme_' . $_GET['template']);
			if ( !current_user_can('delete_themes') )
				wp_die( __( 'Cheatin&#8217; uh?','ocmx' ) );
			delete_theme($_GET['template']);
		}
	}
	/*************************/
    /* Current Theme Details */
	$current_theme = $ocmx_oboxfb_class->oboxfb_stylesheet();
	
	$theme_dir = OBOXFBDIR."themes";

	$themes = get_oboxfb_themes($theme_dir);
	
	uksort( $themes, "strnatcasecmp" );
	
	$theme_total = count( $themes );
		
	if ( $theme_total ) :
		$style = '';
		$theme_names = array_keys($themes);
		natcasesort($theme_names);
		$table = array();
	
		for ( $col = 1; $col <= $theme_total; $col++ ) :
			$table[$col] = array_shift($theme_names);
		endfor;
		$count = 1;
		foreach ( $table as $col => $theme_name ) : 
				if ( !empty($theme_name) ) :
				
				/*****************************************/
				/* Set the Template Details as Variables */
				$template = $themes[$theme_name]['Template'];
				$stylesheet = $themes[$theme_name]['Stylesheet'];
				$title = $themes[$theme_name]['Title'];
				$version = $themes[$theme_name]['Version'];
				$description = $themes[$theme_name]['Description'];
				$author = $themes[$theme_name]['Author'];
				$screenshot = $themes[$theme_name]['Screenshot'];
				$theme_root = $themes[$theme_name]['Theme Root'];
				$theme_root_uri = $themes[$theme_name]['Theme Root URI'];
				
                if($stylesheet == $current_theme) : $class = "theme-block-item active"; else : $class = "theme-block-item"; endif;  ?>
                
			   <li class="<?php echo $class; ?>">
					<?php 
					/***************************************/
					/* Set the Preview Link for this Theme */
					$preview_link = esc_url(get_option('home') . '/');
					if ( is_ssl() ) : $preview_link = str_replace( 'http://', 'https://', $preview_link ); endif;
					$preview_link = htmlspecialchars( add_query_arg( array('site_switch' => 'facebook', 'preview' => 1, 'template' => $template, 'stylesheet' => urlencode($stylesheet), 'TB_iframe' => 'true'), $preview_link ) );
					$preview_text = esc_attr( sprintf( __('Preview of &#8220;%s&#8221;','ocmx'), $title ) );
					$thickbox_class = 'thickbox thickbox-preview';
					
					/****************************/
					/* Set the Activation Links */
					$activate_link = admin_url("admin.php?page=".$_REQUEST["page"]."&action=activate&amp;template=".urlencode($template)."&amp;stylesheet=".urlencode($stylesheet));
					$activate_text = esc_attr( sprintf( __('Activate &#8220;%s&#8221;','ocmx'), $title ) );
					$actions = array();
					$actions[] = '<a href="' . $preview_link . '" class="thickbox thickbox-preview" title="' . esc_attr(sprintf(__('Preview &#8220;%s&#8221;','ocmx'), $theme_name)) . '">' . __('Preview','ocmx') . '</a>';
					$actions[] = '<a href="' . $activate_link .  '" class="activatelink" title="' . $activate_text . '">' . __('Activate','ocmx') . '</a>';
					
					/*********************/
					/* Compile the links */
					$actions = apply_filters('theme_action_links', $actions, $themes[$theme_name]);
					$actions = implode ( ' ', $actions ); ?>
					
					<a href="<?php echo $activate_link; ?>" class="screenshot">
						<?php if ( $screenshot ) : ?>
							<img src="<?php echo $theme_root_uri . '/' . $stylesheet . '/' . $screenshot; ?>" alt="" />
						<?php endif; ?>
					</a>
					<h4><?php printf(__('%1$s %2$s by %3$s','ocmx'), $title, $version, $author) ; ?></h4>
					<div class="theme-functions"><?php echo $actions ?></div>
					<div id="delete-theme-<?php echo $stylesheet; ?>" class="no_display">
	            		<a href="#" class="remove-link"><?php _e("Delete Theme","ocmx"); ?></a>
                	</div>
				</li>
			<?php endif;
			$count++;
		endforeach; ?>
        <li class="theme-block-item empty clearfix">
        	<a href="#" id="add-theme">
            	<span><?php _e("Add a New Theme","ocmx"); ?></span>
            </a>
		</li>
	 <?php endif;
}
add_action("oboxfb_theme_list", "oboxfb_theme_list");

/*********************************/
/* Use Thickbox for the previews 
if(is_admin()) :
	wp_enqueue_script( 'theme-preview' );
	wp_enqueue_style( 'thickbox-css' );
	add_thickbox(); 
endif;?>
*/
