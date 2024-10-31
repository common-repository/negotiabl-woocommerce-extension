<?php 
class Negotiabl_Cart { 

	private $table, $wpdb;
	function __construct( ) { 
		global $wpdb;
		$this->table = NEGOTIABL_CART_TABLE;
		$this->wpdb = $wpdb;
	}
	
	function getCartId( $key = '' ) { 
		return $this->wpdb->get_var( $this->wpdb->prepare( "SELECT negoc_id FROM `{$this->table}` WHERE `negoc_session_key` = '%s'", $key ) );
	}
	
	function getCartKey( $id = '' ) { 
		return $this->wpdb->get_var( $this->wpdb->prepare( "SELECT negoc_session_key FROM `{$this->table}` WHERE `negoc_id` = %d", $id ) );
	}
	
	function addUpdate( $data = array( ) ) { 
		$cartId = absint( $data[ 'id' ] );
		$addUpdateData = array( 
							'negoc_cart_data' => $data[ 'cart' ],
							'negoc_session_key' => $data[ 'key' ],
							'negoc_updated' => date( 'Y-m-d H:i:s' )
						);
		$add = true;
		if( $cartId > 0 ) { 
			$cartId = $this->wpdb->get_var( $this->wpdb->prepare( "SELECT negoc_id FROM `{$this->table}` WHERE `negoc_id` = %d", $cartId ) );
		}
		
		if( $cartId <= 0 ) { 
			$addUpdateData[ 'negoc_created' ] = date( 'Y-m-d H:i:s' );
			$this->wpdb->insert( 
				$this->table,
				$addUpdateData,
				array( '%s', '%s', '%s', '%s' )
			);
		} else {
			$addUpdateData[ 'negoc_order_id' ] = ( isset( $data[ 'order_id' ] ) && $data[ 'order_id' ] )?intval( $data[ 'order_id' ] ):0;
			$this->wpdb->update( 
				$this->table,
				$addUpdateData,
				array( 'negoc_id' => $cartId ),
				array( '%s', '%s', '%s', '%d' ),
				array( '%d' )
			);
		}
		
		if( $this->wpdb->last_error !== '' ) return new WP_Error( 'broke', __( "There is something went wrong, please try again after some time.", NEGOTIABL_TEXTDOMAIN ) );
		
		return ( $cartId <= 0 )?$this->wpdb->insert_id:$cartId;
		
	}
	
}
