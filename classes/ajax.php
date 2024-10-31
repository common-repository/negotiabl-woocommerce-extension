<?php 

class NegotiablAjax{
	
	function __construct( ) { 
		add_action("wp_ajax_getOfferCart", array($this, "getOfferCart"));
		add_action("wp_ajax_nopriv_getOfferCart", array($this, "getOfferCart"));
	}

	function getOfferCart( ) { 
	
		// disable by shabin on 13-02-17
		// reason, api inconsistently failing with "200, Invalid request"
		/* if ( ! wp_verify_nonce( $_POST[ 'nonce' ], 'negotiablNonceCommon' ) ) { 
			die( json_encode( array( 'status' => 0, 'msg' => __( 'Invalid Request.', NEGOTIABL_TEXTDOMAIN ) ) ) );
		} */
		
		$cartData = Negotiabl_WooCommerceCart::getCartData( true );
		die( json_encode( array( 'status' => 1, 'data' => $cartData ) ) );
		
	}
}

new NegotiablAjax();