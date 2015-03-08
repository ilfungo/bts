<?php
/*
Plugin Name: BPTPI FTP FP
Version: 1.0
Description: Plugin per consentire l'importazione delle foto/prodotto per BPTPI direttamente da FTP. <strong>Nota:</strong> Tutti i file saranno copiati dalla directory di upload.
Author: Federico Porta
Author URI: http://www.federicoporta.com/
*/

add_action('plugins_loaded', 'bptpi_ftp_fp_load');
function bptpi_ftp_fp_load() {
	if ( ! is_admin() )
		return;
	include 'class.bptpi-ftp-fp.php';
	$GLOBALS['bptpi_ftp_fp'] = new bptpi_ftp_fp( plugin_basename(__FILE__) );
    //$GLOBALS['add-from-server'] = new add_from_server( plugin_basename(__FILE__) );
}
//per il debug usa
//FirePHP($message, $label = null, $type = 'LOG')