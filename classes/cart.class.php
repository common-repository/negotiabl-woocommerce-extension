<?php 
class Negotiabl_WooCommerceCart { 
	
	public static function createEventCartByAjax( $proId ){ 
		$isAuthDone = negotiabl_isAuthDone();
		if( $isAuthDone ){
			$cartData = SELF::getCartData( );
			
			if( $cartData ) { 
				$reqObj = new Negotiabl_WooCommerceRequest( NEGOTIABL_CREATE_URL, $cartData );
				/* $reqObj->setBasicAuth( );
				$reqObj->setHeader( );
				$reqObj->setHttpHeader( ); */
				$status = $reqObj->send( );
			}
		}
	}
	
	public static function createEventCart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) { 
		$isAuthDone = negotiabl_isAuthDone();
		if( $isAuthDone ) { 
			$cartData = SELF::getCartData( );
			
			if( $cartData ) { 
				$reqObj = new Negotiabl_WooCommerceRequest( NEGOTIABL_CREATE_URL, $cartData );
				/* $reqObj->setBasicAuth( );
				$reqObj->setHeader( );
				$reqObj->setHttpHeader( ); */
				$status = $reqObj->send( );
			}
		}
	}
	
	public static function updateEventCart( $updated ) { 
		if( $updated ) { 
			$isAuthDone = negotiabl_isAuthDone();
			if( $isAuthDone ){
				$cartData = SELF::getCartData( );
				
				if( $cartData ) { 
					$reqObj = new Negotiabl_WooCommerceRequest( NEGOTIABL_UPDATE_URL, $cartData );
					/* $reqObj->setBasicAuth( );
					$reqObj->setHeader( );
					$reqObj->setHttpHeader( ); */
					$status = $reqObj->send( );
				}
			}
		}
	}
	
	public static function getCartData( $checkCartEmpty = false ) { 
		
		global $woocommerce;
		
		if( !$woocommerce ) return false;
		$cartObj = $woocommerce->cart;
		
		if( !( is_object( $cartObj ) && get_class( $cartObj ) == 'WC_Cart' ) )return false;
		
		$cartData = $cartObj->get_cart( );
		if( $checkCartEmpty && !$cartData ) { 
			return array( 'id' => 0, 'line_items' => array( ), 'customer' => SELF::getCartCustomer( ) );
		}
		
		$lineItems = array( );
		if( $cartData ) { 
			foreach( $cartData as $key => $data ) { 
				$product = $data[ 'data' ];
				
				$wcDateTimeObj = $product->get_date_created( );
				$createdTimeStamp = ( $wcDateTimeObj && get_class( $wcDateTimeObj ) == 'WC_DateTime' )?$wcDateTimeObj->getTimestamp( ):0;
				
				$wcDateTimeObj = $product->get_date_modified( );
				$modifiedTimeStamp = ( $wcDateTimeObj && get_class( $wcDateTimeObj ) == 'WC_DateTime' )?$wcDateTimeObj->getTimestamp( ):0;
				
				$lineItems[] = array( 
									"key"				=> $data[ 'key' ],
									"product_id"		=> $data[ 'product_id' ],
									"variation_id"		=> $data[ 'variation_id' ],
									"variation"			=> $data[ 'variation' ],
									"quantity"			=> $data[ 'quantity' ],
									"line_tax_data"		=> $data[ 'line_tax_data' ],
									"line_subtotal"		=> $data[ 'line_subtotal' ],
									"line_subtotal_tax"	=> $data[ 'line_subtotal_tax' ],
									"line_total"		=> $data[ 'line_total' ],
									"line_tax"			=> $data[ 'line_tax' ],
									"data"				=> array( 
																"id"				=> $product->get_id( ),
																"name"				=> $product->get_name( ),
																"slug"				=> $product->get_slug( ),
																"date_created"		=> $createdTimeStamp,
																"date_modified"		=> $modifiedTimeStamp,
																"status"			=> $product->get_status( ),
																"featured"			=> $product->get_featured( ),
																"catalog_visibility"=> $product->get_catalog_visibility( ),
																"description"		=> $product->get_description( ),
																"short_description"	=> $product->get_short_description( ),
																"sku"				=> $product->get_sku( ),
																"price"				=> $product->get_price( ),
																"regular_price"		=> $product->get_regular_price( ),
																"sale_price"		=> $product->get_sale_price( )
															)
								);
			}
		}
		
		$data = array( 'line_items' => $lineItems, 'customer' => SELF::getCartCustomer( ) );
		$cartId = SELF::addCart( $data );
		if( !$cartId ) return false;
		
		$data[ 'id' ] = $cartId;
		return $data;
	}
	
	private static function addCart( $cartData = array( ) ) { 
		
		global $woocommerce;
		$cartSession = $woocommerce->session;
		if( !( is_object( $cartSession ) && get_class( $cartSession ) == 'WC_Session_Handler' ) )return false;
		session_start( );
		$cartId = $_SESSION[ 'negotiabl_cart_id' ];
		
		$data			= array( );
		$data[ 'key' ]	= $cartSession->get_customer_id( );
		if( $cartId > 0 ) $data[ 'id' ] = $cartId;
		$data[ 'cart' ] = json_encode( $cartData );
		
		$obj = new Negotiabl_Cart( );
		$cartId = $obj->addUpdate( $data );
		
		if( is_wp_error( $cartId ) ) { 
			WC_Admin_Settings::add_error( __( $cartId->get_error_message(), NEGOTIABL_TEXTDOMAIN ) );
			$woocommerce->cart->empty_cart( true );
			return false;
		}
		
		$_SESSION[ 'negotiabl_cart_id' ] = $cartId;

		setcookie( 'negotiabl_cart_id', $cartId, time( ) + ( 86400 * 30 ), "/" );

		return $cartId;
		
	}
	
	private static function getCartCustomer( ) { 
		
		global $woocommerce;
		if( !$woocommerce ) return false;
		$customer = $woocommerce->customer;
		if( !( is_object( $customer ) && get_class( $customer ) == 'WC_Customer' ) )return false;
		
		$wcDateTimeObj = $customer->get_date_created( );
		$createdTimeStamp = ( $wcDateTimeObj && get_class( $wcDateTimeObj ) == 'WC_DateTime' )?$wcDateTimeObj->getTimestamp( ):0;
		
		$wcDateTimeObj = $customer->get_date_modified( );
		$modifiedTimeStamp = ( $wcDateTimeObj && get_class( $wcDateTimeObj ) == 'WC_DateTime' )?$wcDateTimeObj->getTimestamp( ):0;
		
		$customerData = array( 
							"id"			=> $customer->get_id( ),
							"date_created"	=> $createdTimeStamp,
							"date_modified"	=> $modifiedTimeStamp,
							"email"			=> $customer->get_email( ),
							"first_name"	=> $customer->get_first_name( ),
							"last_name"		=> $customer->get_last_name( ),
							"display_name"	=> $customer->get_display_name( ),
							"customer" => array( 
											"first_name"	=> $customer->get_billing_first_name( ),
											"last_name"		=> $customer->get_billing_last_name( ),
											"company"		=> $customer->get_billing_company( ),
											"address_1"		=> $customer->get_billing_address_1( ),
											"address_2"		=> $customer->get_billing_address_2( ),
											"city"			=> $customer->get_billing_city( ),
											"state"			=> $customer->get_billing_state( ),
											"postcode"		=> $customer->get_billing_postcode( ),
											"country"		=> $customer->get_billing_country( ),
											"email"			=> $customer->get_billing_email( ),
											"phone"			=> $customer->get_billing_phone( )
										),
							"shipping" => array( 
											"first_name"	=> $customer->get_shipping_first_name( ),
											"last_name"		=> $customer->get_shipping_last_name( ),
											"company"		=> $customer->get_shipping_company( ),
											"address_1"		=> $customer->get_shipping_address_1( ),
											"address_2"		=> $customer->get_shipping_address_2( ),
											"city"			=> $customer->get_shipping_city( ),
											"state"			=> $customer->get_shipping_state( ),
											"postcode"		=> $customer->get_shipping_postcode( ),
											"country"		=> $customer->get_shipping_country( )
										),
							"is_paying_customer" => $customer->get_is_paying_customer( )
						);
		
		return $customerData;
		
	}
	
	public static function emptyCart( ) { 
		SELF::removeCartFromApi( );
	}
	
	public static function removeCartItem( $cart_item_key, $cartObj ) { 
		SELF::removeCartFromApi( );
	}
	
	private static function removeCartFromApi( ) { 
		session_start( );
		if( isset( $_SESSION[ 'negotiabl_cart_id' ] ) ) { 
			$isAuthDone = negotiabl_isAuthDone();
			if( $isAuthDone ) { 
				$cartData = SELF::getCartData( );
				
				if( $cartData ) { 
					$reqObj = new Negotiabl_WooCommerceRequest( NEGOTIABL_UPDATE_URL, $cartData );
					/* $reqObj->setBasicAuth( );
					$reqObj->setHeader( );
					$reqObj->setHttpHeader( ); */
					$status = $reqObj->send( );
				}
				
				SELF::removeCartSessionKey( );
				
			}
		}
	}
	
	private static function removeCartSessionKey( ) { 
		session_start( );
		if( isset( $_SESSION[ 'negotiabl_cart_id' ] ) ) { 
			$isAuthDone = negotiabl_isAuthDone();
			if( $isAuthDone ) { 
				global $woocommerce;
				if( !$woocommerce ) return false;
				$cartObj = $woocommerce->cart;
				if( !( is_object( $cartObj ) && get_class( $cartObj ) == 'WC_Cart' ) )return false;
				$cartData = $cartObj->get_cart( );
				if( !$cartData ) { 
					unset( $_SESSION[ 'negotiabl_cart_id' ] );
					setcookie( 'negotiabl_cart_id', null, -1, '/' );
					return true;
				}
				return false;
			}
		}
	}
	
	public static function updateEventCartIdIntoOrder( $orderId ) { 
		session_start( );
		if( $orderId > 0 && isset( $_SESSION[ 'negotiabl_cart_id' ] ) && $_SESSION[ 'negotiabl_cart_id' ] > 0 ) { 
			update_post_meta( $orderId, '_negotiabl_cart_id', $_SESSION[ 'negotiabl_cart_id' ] );
			
			/*$order = wc_get_order( $orderId );
			$orderItems = ( is_object( $order ) && get_class( $order ) == 'WC_Order' )?$order->get_items():array( );
			if( $orderItems ) { 
				foreach ( $orderItems as $itemId => $itemData ) { 
					wc_add_order_item_meta( $itemId,  '_negotiabl_cart_id',  $_SESSION[ 'negotiabl_cart_id' ] ); 
				}
			}*/
		}
	}
	
}