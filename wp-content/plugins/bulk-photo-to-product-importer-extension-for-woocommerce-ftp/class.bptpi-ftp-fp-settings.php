<?php
class bptpi_ftp_fp_settings {
	var $main; // main Add From Server instance.
	function __construct( &$afs ) {
		$this->main = $afs;
	}

	function render() {//questo è il codice della pagina delle opzioni!
		echo '<div class="wrap">';
		screen_icon('options-general');
		echo '<h2>' . __('Add From Server Settings', 'bptpi_ftp_fp') . '</h2>';
		echo '<form method="post" action="options.php">';
		settings_fields( 'add_from_server' );
		$uac = get_option('frmsvr_uac', 'allusers');
		$root = $this->main->get_root( 'raw' );
		?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('User Access Control', 'bptpi_ftp_fp'); ?></th>
				
				<td><fieldset>
				<legend class="screen-reader-text"><span><?php _e('User Access Control', 'bptpi_ftp_fp'); ?></span></legend>
				<label for="frmsvr_uac-allusers">
				<input name="frmsvr_uac" type="radio" id="frmsvr_uac-allusers" value="allusers" <?php checked($uac, 'allusers'); ?> />
				<?php _e('All users with the ability to upload files', 'bptpi_ftp_fp'); ?>
				</label>
				<br />
				<label for="frmsvr_uac-role">
				<input name="frmsvr_uac" type="radio" id="frmsvr_uac-role" value="role" <?php checked($uac, 'role'); ?> />
				<?php _e('Any user with the ability to upload files in the following roles', 'bptpi_ftp_fp'); ?>
				</label>
				<?php 
					$current_roles = (array)get_option('frmsvr_uac_role', array());
					foreach ( get_editable_roles() as $role => $details ) {
						if ( !isset($details['capabilities']['upload_files']) || !$details['capabilities']['upload_files'] )
							continue;
						?>
						<label for="frmsvr_uac-role-<?php echo esc_attr($role); ?>">
						<input type="checkbox" name="frmsvr_uac_role[]" id="frmsvr_uac-role-<?php echo esc_attr($role); ?>" value="<?php echo esc_attr($role); ?>" <?php checked(in_array($role, $current_roles)); ?> />
						<?php echo translate_user_role($details['name'] ); ?>
						</label>
						<?php
					}
				?>
				<br />
				<label for="frmsvr_uac-listusers">
				<input name="frmsvr_uac" type="radio" id="frmsvr_uac-listusers" value="listusers" <?php checked($uac, 'listusers'); ?> />
				<?php _e('Any users with the ability to upload files listed below', 'bptpi_ftp_fp'); ?>
				</label>
				<br />
				<textarea rows="5" cols="20" name="frmsvr_uac_users" class="large-text code"><?php echo esc_textarea(get_option('frmsvr_uac_users', 'admin')); ?></textarea>
				<br />
				<small><em><?php _e("List the user login's one per line", 'bptpi_ftp_fp'); ?></em></small>
				</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Root Directory', 'bptpi_ftp_fp'); ?></th>
				
				<td><fieldset>
				<legend class="screen-reader-text"><span><?php _e('Root Directory', 'bptpi_ftp_fp'); ?></span></legend>
				<label for="frmsvr_root-default">
				<?php
				$default_root = '/';
				if ( preg_match('!(\w:)!', __FILE__, $matches) )
					$default_root = strtolower($matches[1]);
				?>
				<input name="frmsvr_root" type="radio" id="frmsvr_root-default" value="<?php echo esc_attr($default_root); ?>" <?php checked($root, $default_root); ?> />
				<?php _e('Do not lock browsing to a specific directory', 'bptpi_ftp_fp'); ?>
				</label>
				<br />
				<label for="frmsvr_root-specify">
				<input name="frmsvr_root" type="radio" id="frmsvr_root-specify" value="specific" <?php checked($root != $default_root); ?> />
				<?php _e('Lock browsing to the directory specified below', 'bptpi_ftp_fp'); ?>
				</label>
				<br />
				<input type="text" name="frmsvr_root-specified" id="frmsvr_root-specify-specified" class="large-text code" value="<?php echo esc_attr( str_replace('/', DIRECTORY_SEPARATOR, $root) . (strlen($root) > 1 ? DIRECTORY_SEPARATOR : '')); ?>" />
				<br />
				<small><em><?php
					printf( __('You may use placeholders such as %s and %s in the path.', 'bptpi_ftp_fp'), '%username%', '%role%'); 
					echo '&nbsp;&nbsp;';
					printf( __('For reference, Your WordPress Root path is: <code>%s</code>', 'bptpi_ftp_fp'), ABSPATH);
					?>
				</em></small>
				</fieldset>
				</td>
			</tr>
		</table>
		<script type="text/javascript">
			jQuery('#frmsvr_root-specify-specified').change( function() { jQuery('#frmsvr_root-specify').attr('checked', 'checked'); });
		</script>
		<?php
		submit_button( __('Save Changes', 'bptpi_ftp_fp'), 'primary', 'submit');
		echo '</form>';
		echo '</div>';
	}
}