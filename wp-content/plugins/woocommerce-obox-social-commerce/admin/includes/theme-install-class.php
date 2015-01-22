<?php if(isset($_REQUEST["action"]) && $_REQUEST["action"] != "do-core-upgrade" ):
	include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	class OBOXFB_Theme_Upgrader extends WP_Upgrader {
	
		var $result;
	
		function upgrade_strings() {
			$this->strings['up_to_date'] = __('The theme is at the latest version.','ocmx');
			$this->strings['no_package'] = __('Upgrade package not available.','ocmx');
			$this->strings['downloading_package'] = __('Downloading update from <span class="code">%s</span>&#8230;','ocmx');
			$this->strings['unpack_package'] = __('Unpacking the update&#8230;','ocmx');
			$this->strings['remove_old'] = __('Removing the old version of the theme&#8230;','ocmx');
			$this->strings['remove_old_failed'] = __('Could not remove the old theme.','ocmx');
			$this->strings['process_failed'] = __('Theme upgrade failed.','ocmx');
			$this->strings['process_success'] = __('Theme upgraded successfully.','ocmx');
		}
	
		function install_strings() {
			$this->strings['no_package'] = __('Install package not available.','ocmx');
			$this->strings['downloading_package'] = __('Downloading install package from <span class="code">%s</span>&#8230;','ocmx');
			$this->strings['unpack_package'] = __('Unpacking the package&#8230;','ocmx');
			$this->strings['installing_package'] = __('Installing the theme&#8230;','ocmx');
			$this->strings['process_failed'] = __('Theme install failed.','ocmx');
			$this->strings['process_success'] = __('Theme installed successfully.','ocmx');
		}
	
		function install($package, $file) {
	
			$this->init();
			$this->install_strings();
			
			$options = array(
							'package' => $package,
							'destination' => OBOXFBDIR . 'themes/' . $file,
							'clear_destination' => true, //Do not overwrite files.
							'clear_working' => true
							);
			
			$this->run($options);
	
			if ( is_wp_error($this->result) )
				return $this->result;
	
			// Force refresh of theme update information
			delete_site_transient('update_themes');
	
			if ( empty($result['destination_name']) )
				return false;
			else
				return $result['destination_name'];
		}
	
		function upgrade($theme) {
	
			$this->init();
			$this->upgrade_strings();
	
			// Is an update available?
			$current = get_site_transient( 'update_themes' );
	
			$r = $current->response[ $theme ];
	
			add_filter('upgrader_pre_install', array(&$this, 'current_before'), 10, 2);
			add_filter('upgrader_post_install', array(&$this, 'current_after'), 10, 2);
			add_filter('upgrader_clear_destination', array(&$this, 'delete_old_theme'), 10, 4);
	
			$options = array(
							'package' => $r['package'],
							'destination' => OBOXFBDIR . 'themes',
							'clear_destination' => true,
							'clear_working' => true,
							'hook_extra' => array(
												'theme' => $theme
												)
							);
	
			$this->run($options);
	
			if ( is_wp_error($this->result) )
				return $this->result;
	
			// Force refresh of theme update information
			delete_site_transient('update_themes');
	
			return true;
		}
	
		function bulk_upgrade($themes) {
	
			$this->init();
			$this->bulk = true;
			$this->upgrade_strings();
	
			$current = get_site_transient( 'update_themes' );
	
			add_filter('upgrader_pre_install', array(&$this, 'current_before'), 10, 2);
			add_filter('upgrader_post_install', array(&$this, 'current_after'), 10, 2);
			add_filter('upgrader_clear_destination', array(&$this, 'delete_old_theme'), 10, 4);
	
			// Connect to the Filesystem first.
			$res = $this->fs_connect( array(WP_CONTENT_DIR) );
			if ( ! $res ) {
				return false;
			}
	
			$this->maintenance_mode(true);
	
			$results = array();
	
			$this->update_count = count($themes);
			$this->update_current = 0;
			foreach ( $themes as $theme ) {
				$this->update_current++;
				
	
				// Get the URL to the zip file
				$r = $current->response[ $theme ];
	
				$options = array(
								'package' => $r['package'],
								'destination' => OBOXFBDIR . 'themes',
								'clear_destination' => true,
								'clear_working' => true,
								'hook_extra' => array(
													'theme' => $theme
													)
								);
	
				$result = $this->run($options);
	
				$results[$theme] = $this->result;
	
				// Prevent credentials auth screen from displaying multiple times
				if ( false === $result )
					break;
			} //end foreach $plugins
	
			$this->maintenance_mode(false);
	
			// Cleanup our hooks, incase something else does a upgrade on this connection.
			remove_filter('upgrader_pre_install', array(&$this, 'current_before'), 10, 2);
			remove_filter('upgrader_post_install', array(&$this, 'current_after'), 10, 2);
			remove_filter('upgrader_clear_destination', array(&$this, 'delete_old_theme'), 10, 4);
	
			// Force refresh of theme update information
			delete_site_transient('update_themes');
	
			return $results;
		}
	
		function current_before($return, $theme) {
	
			if ( is_wp_error($return) )
				return $return;
	
			$theme = isset($theme['theme']) ? $theme['theme'] : '';
	
			if ( $theme != get_stylesheet() ) //If not current
				return $return;
			//Change to maintainence mode now.
			if ( ! $this->bulk )
				$this->maintenance_mode(true);
	
			return $return;
		}
		function current_after($return, $theme) {
			if ( is_wp_error($return) )
				return $return;
	
			$theme = isset($theme['theme']) ? $theme['theme'] : '';
	
			if ( $theme != get_stylesheet() ) //If not current
				return $return;
	
			//Ensure stylesheet name hasnt changed after the upgrade:
			if ( $theme == get_stylesheet() && $theme != $this->result['destination_name'] ) {
				$theme_info = $this->theme_info();
				$stylesheet = $this->result['destination_name'];
				$template = !empty($theme_info['Template']) ? $theme_info['Template'] : $stylesheet;
				switch_theme($template, $stylesheet, true);
			}
	
			//Time to remove maintainence mode
			if ( ! $this->bulk )
				$this->maintenance_mode(false);
			return $return;
		}
	
		function delete_old_theme($removed, $local_destination, $remote_destination, $theme) {
			global $wp_filesystem;
	
			$theme = isset($theme['theme']) ? $theme['theme'] : '';
	
			if ( is_wp_error($removed) || empty($theme) )
				return $removed; //Pass errors through.
	
			$themes_dir = $wp_filesystem->wp_themes_dir();
			if ( $wp_filesystem->exists( trailingslashit($themes_dir) . $theme ) )
				if ( ! $wp_filesystem->delete( trailingslashit($themes_dir) . $theme, true ) )
					return false;
			return true;
		}
	
		function theme_info($theme = null) {
	
			if ( empty($theme) ) {
				if ( !empty($this->result['destination_name']) )
					$theme = $this->result['destination_name'];
				else
					return false;
			}
			return get_theme_data(WP_CONTENT_DIR . '/themes/' . $theme . '/style.css');
		}
	
	} 
endif;?>