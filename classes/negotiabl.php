<?php

class Negotiabl { 
	
	public static function plugin_activation( ) { 
		
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS " . NEGOTIABL_CART_TABLE . " (
									  `negoc_id` bigint(20) NOT NULL AUTO_INCREMENT,
									  `negoc_cart_data` text NOT NULL,
									  `negoc_order_id` bigint(20) NOT NULL,
									  `negoc_session_key` varchar(255) NOT NULL,
									  `negoc_created` datetime NOT NULL,
									  `negoc_updated` datetime NOT NULL,
									  PRIMARY KEY (`negoc_id`),
									  KEY `negoc_order_id` (`negoc_order_id`)
									) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

	}
	
	public static function plugin_deactivation( ) { 
		
	}
	
	public static function init( ) { 
		session_start( );
		if( !( isset( $_SESSION[ 'negotiabl_cart_id' ] ) && $_SESSION[ 'negotiabl_cart_id' ] <= 0 ) ) { 
			global $woocommerce;
			$cartSession = $woocommerce->session;
			$cartId = ( isset( $_COOKIE[ 'negotiabl_cart_id' ] ) && $_COOKIE[ 'negotiabl_cart_id' ] > 0 )?$_COOKIE[ 'negotiabl_cart_id' ]:0;
			if( is_object( $cartSession ) && get_class( $cartSession ) == 'WC_Session_Handler' && $cartId > 0 ) { 
				$ncObj = new Negotiabl_Cart( );
				$existCartKey = $ncObj->getCartKey( $cartId );
				if( $existCartKey == $cartSession->get_customer_id( ) ) { 
					$_SESSION[ 'negotiabl_cart_id' ] = $cartId;
				} else { 
					setcookie( 'negotiabl_cart_id', null, -1, '/' );
				}
			}
		}
	}

	public static function plugins_loaded( ) { 
		load_plugin_textdomain( NEGOTIABL_TEXTDOMAIN, false, NEGOTIABL__PLUGIN_DIR . '/languages' ); 
	}
	
	public static function addScriptsAndStyles() {

		/*wp_enqueue_style( 'style-name', get_stylesheet_uri() );*/
		wp_enqueue_script( 'ff-custom', plugins_url( '../assets/js/custom.js', __FILE__ ), array( 'jquery' ), time(), true );
		
		$args = array ( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'negotiablNonceCommon' ) );
		wp_localize_script( 'ff-custom', 'negotiabl', $args );
		
		$isAuthDone = negotiabl_isAuthDone( );
		if( $isAuthDone ) { 
			$key = negotiabl_getKey( );
			//$popupTime = negotiablGetPopupTime( );

			//local
			/* wp_enqueue_script( 'woo-mmo-external', 'http://localhost:3000/js/woo-commerce/plugin/v1/sb.js?merchant_id=' . $key, array( ), '', true ); */

			//dev
			/* wp_enqueue_script( 'woo-mmo-external', 'https://ecomnegoalerts-dev.herokuapp.com/js/woo-commerce/plugin/v1/sb.js?merchant_id=' . $key, array( ), '', true ); */

			//prod
			wp_enqueue_script( 'woo-mmo-external', 'https://app.negotiabl.com/js/woo-commerce/plugin/v1/sb.js?merchant_id=' . $key, array( ), '', true );
			
			
			$cartData = Negotiabl_WooCommerceCart::getCartData( true );
			wp_localize_script( 'woo-mmo-external', 'negotiabl_woo_comm', array( 
				'cart_data' => $cartData,
				//'popup_time' => ( absint( $popupTime ) > 0?absint( $popupTime ):5 ),
				'merchant_id' => $key
			) );
		}
		
	}
	
	public static function curlErrorNotice( ) {
 		?>
		<br/>	
		<div class="error notice">
			<p><?php _e( 'Curl should be enable for Woo Negotiabl Plugin.', 'woo_negotiabl' ); ?></p>
		</div>
		<?php 
	}
	
}
