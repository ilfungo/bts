<?php function oboxfb_theme_remove(){
	global $wp_filesystem;
	$template  = $_GET["template"];
	
	if ( empty($template) )
		return false;

	ob_start();
	if ( false === ($credentials = request_filesystem_credentials($url)) ) {
		$data = ob_get_contents();
		ob_end_clean();
		if ( ! empty($data) ){
			die($data);
		}
		return;
	}

	if ( ! WP_Filesystem($credentials) ) {
		request_filesystem_credentials($url, '', true); // Failed to connect, Error and request again
		$data = ob_get_contents();
		ob_end_clean();
		if ( ! empty($data) ) {
			die($data);
		}
		return;
	}


	if ( ! is_object($wp_filesystem) )
		return new WP_Error('fs_unavailable', __('Could not access filesystem.','ocmx'));

	if ( is_wp_error($wp_filesystem->errors) && $wp_filesystem->errors->get_error_code() )
		return new WP_Error('fs_error', __('Filesystem error.','ocmx'), $wp_filesystem->errors);

	//Get the base plugin folder
	$themes_dir = OBOXFBDIR."themes";
	if ( empty($themes_dir) )
		return new WP_Error('fs_no_themes_dir', __('Unable to locate WordPress theme directory.','ocmx'));

	$themes_dir = trailingslashit( $themes_dir );
	$theme_dir = trailingslashit($themes_dir . $template);
	
	$deleted = $wp_filesystem->delete($theme_dir, true);

	if ( ! $deleted )
		return new WP_Error('could_not_remove_theme', sprintf(__('Could not fully remove the theme %s.','ocmx'), $template) );

	// Force refresh of theme update information
	delete_site_transient('update_themes');
	die("Success");
	return true;
}; ?>