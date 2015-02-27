<?php
/*
function fp_debug_mode() {
    if ( FP_DEBUG ) {
        error_reporting( E_ALL );

        if ( FP_DEBUG_DISPLAY ){
            ini_set( 'display_errors', 1 );
            ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT);
        }elseif ( null !== FP_DEBUG_DISPLAY ){
            ini_set( 'display_errors', 0 );
        }

        if ( FP_DEBUG_LOG ) {
            ini_set( 'log_errors', 1 );
            ini_set( 'error_log', FP_CONTENT_DIR . '/debug.log' );
        }
    } else {
        error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR  | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );
    }
    if ( defined( 'XMLRPC_REQUEST' ) )
        ini_set( 'display_errors', 0 );
}
fp_debug_mode();
*/
//define('WP_DEBUG', true);
ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT);
