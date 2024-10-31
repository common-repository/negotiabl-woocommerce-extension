(function( $ ) { 
	runAjax = function ( $args, $fileForm ) { 
		
		$obj = $args.obj;
		if( typeof $fileForm == typeof undefined ) $fileForm = false;
	
		$href = ( typeof $args.ajax_url == typeof undefined )?negotiabl.ajax_url:$args.ajax_url;
		$data = $args.data;
		$ajaxType = $args.ajaxType;
		$dataType = ( typeof $args.data_type == typeof undefined )?'json':$args.data_type;
		
		$funcSuccess = $args.success;
		$funcBeforeSend = $args.beforeSend;
		$funcComplete = $args.complete;
		$funcError = $args.error;
		
		if( typeof $ajaxType == typeof undefined ) $ajaxType = 'POST';
		$currentObject = $( $obj );
		if( $currentObject.hasClass( 'running' ) ) return false;
		
		$ajaxArgs = {
					url: $href,
					data: $data,
					type: $ajaxType,
					dataType: $dataType,
					beforeSend: function( ){
						$currentObject.addClass( 'running' );
						if( typeof $funcBeforeSend != typeof undefined ) 
							$funcBeforeSend( );
					},
					complete: function( ){
						$currentObject.removeClass( 'running' );
						if( typeof $funcComplete != typeof undefined ) 
							$funcComplete( );
					},
					error: function( ) {
						$currentObject.removeClass( 'running' );
						if( typeof $funcError != typeof undefined ) 
							$funcError( );
					},
					success: function( data ) {
						$currentObject.removeClass( 'running' );
						if( typeof $funcSuccess != typeof undefined ) 
						   $funcSuccess( data );
					},
				};
		if( $fileForm === true ) { 
			$ajaxArgs.contentType = false;
			$ajaxArgs.cache = false;
			$ajaxArgs.processData = false;
		}
		
		$.ajax( $ajaxArgs );
			
		return true;
	};
	
	$( document ).ready(function( ) { 

		$( document.body ).on( 'added_to_cart', function( fragment, hash ) { 
			runAjax({
				obj: $( document.body ),
				data: { action: "getOfferCart", nonce: negotiabl.nonce },
				success: function( response ) { 
					if( response.status == 1 ) negotiabl_woo_comm.cart_data = response.data;
				}
			});
		});
		$( document.body ).on( 'updated_cart_totals', function( e ) { 
			runAjax({
				obj: $( document.body ),
				data: { action: "getOfferCart", nonce: negotiabl.nonce },
				success: function( response ) { 
					if( response.status == 1 ) negotiabl_woo_comm.cart_data = response.data;
				}
			});
		});
		$( document.body ).on( 'removed_from_cart', function( fragment, hash ) { 
			runAjax({
				obj: $( document.body ),
				data: { action: "getOfferCart", nonce: negotiabl.nonce },
				success: function( response ) { 
					if( response.status == 1 ) negotiabl_woo_comm.cart_data = response.data;
				}
			});
		});
	});
})( jQuery );