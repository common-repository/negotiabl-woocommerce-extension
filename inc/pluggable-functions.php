<?php 
if( !function_exists( 'cPrint' ) ) { 
	function cPrint( $array, $exit = false, $text = '' ) { 
		echo '<pre>' . print_r( $array, true ) . '</pre>';
		if( $exit ) die( $text );
	}
}
function negotiabl_checkIsCurlEnabled( ) {
	return function_exists('curl_version');
}

function negotiabl_getKey( ) { 
	return get_option( 'woo_negotiabl_merchant_id' );
}

function negotiabl_getPassword( ) { 
	return get_option( 'woo_negotiabl_api_token' );
}

/* function negotiablGetPopupTime( ) { 
	return get_option( 'woo_negotiabl_popup_time' );
} */

function negotiabl_isAuthDone( ) { 
	return get_option( 'woo_negotiabl_auth' );
}

function negotiabl_isSSLEnabled( ) { 
	if( isset( $_REQUEST[ 'page' ] ) && isset( $_REQUEST[ 'tab' ] ) && $_REQUEST[ 'page' ] == 'wc-settings' && $_REQUEST[ 'tab' ] == 'woo_negotiabl_settings' ) { 
		if( isset( $_REQUEST[ 'woo_negotiabl_use_ssl' ] ) && $_REQUEST[ 'woo_negotiabl_use_ssl' ] == 1 ) 
			return true;
		else return false;
	} elseif( get_option( 'woo_negotiabl_use_ssl' ) == 1 ) 
		return true;
		
	return false;
}