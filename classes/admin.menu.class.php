<?php  
class NegotiablAdminMenu { 

	public $menuTab = 'woo_negotiabl_settings';
	public function __construct( ) { 
		add_action( 'woocommerce_settings_save_' . $this->menuTab, array( $this, 'beforeSaveSettings' ) );
		add_action( 'woocommerce_settings_start', array( $this, 'beforeSettingOutput' ) );
	}
	
	public function beforeSaveSettings( ) { 
		
		$negotiablMerchantId = sanitize_text_field(trim( $_POST['woo_negotiabl_merchant_id'] ));
		$negotiablApiToken = sanitize_text_field(trim( $_POST['woo_negotiabl_api_token'] ));
		$store_currency = get_woocommerce_currency();
		$store_currency_symbol = get_woocommerce_currency_symbol();
		$url = admin_url( 'admin.php?page=wc-settings&connect=400&tab=' . $this->menuTab );
		
		if( $negotiablMerchantId == '' || $negotiablApiToken == '' ) { 
			wp_safe_redirect( $url );
			exit;
		}
		
		$authenticate = $this->authMerchant( $negotiablMerchantId, $negotiablApiToken, $store_currency, $store_currency_symbol );
		if( !$authenticate ) { 
			delete_option( 'woo_negotiabl_auth' );
			delete_option( 'woo_negotiabl_merchant_id' );
			delete_option( 'woo_negotiabl_api_token' );
			/* delete_option( 'woo_negotiabl_popup_time' ); */
			wp_safe_redirect( $url );
			exit;
		} else { 
			add_action( 'woocommerce_settings_saved', array( $this, 'afterSettingSaved' ) );
		}
		
	}
	
	public function afterSettingSaved( ) { 
		/* if( !get_option( 'woo_negotiabl_auth' ) ) {  */
		update_option( 'woo_negotiabl_auth', true );
		update_option( 'woocommerce_api_enabled', 'yes' );
		$keys = SELF::addApiKey( );
		if( $keys ) { 
			$reqObj = new Negotiabl_WooCommerceRequest( NEGOTIABL_ADD_API_URL, $keys );
			$reqObj->send( );
		}
		/* } */

		$url = admin_url( 'admin.php?page=wc-settings&connect=200&tab=' . $this->menuTab );
		wp_safe_redirect( $url );
		exit;
	}
	
	public static function addApiKey( ) { 
		
		if( !is_user_logged_in( ) ) return;
		
		global $wpdb;
		
		$description = 'Negotiabl-Api-Key';
		$permissions = 'read_write';
		$user_id     = get_current_user_id();

		$keys = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}woocommerce_api_keys WHERE user_id = $user_id AND description LIKE \"$description\"", ARRAY_A );
		if( $keys ) { 
			$wpdb->delete(
					$wpdb->prefix . 'woocommerce_api_keys',
					array( 'key_id' => $keys[ 'key_id' ] ),
					array( '%d' )
				);
		}
		
		$consumer_key    = 'ck_' . wc_rand_hash();
		$consumer_secret = 'cs_' . wc_rand_hash();

		$data = array(
			'user_id'         => $user_id,
			'description'     => $description,
			'permissions'     => $permissions,
			'consumer_key'    => wc_api_hash( $consumer_key ),
			'consumer_secret' => $consumer_secret,
			'truncated_key'   => substr( $consumer_key, -7 )
		);

		$wpdb->insert(
			$wpdb->prefix . 'woocommerce_api_keys',
			$data,
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);
		
		if( $wpdb->last_error !== '' ) return false;
		
		return array( 'consumer_key' => $consumer_key, 'consumer_secret' => $consumer_secret );
		
	}
	
	function authMerchant( $negotiablMerchantId, $negotiablApiToken, $store_currency, $store_currency_symbol ) { 
		$reqObj = new Negotiabl_WooCommerceRequest( NEGOTIABL_AUTH_URL, array( 
			'key' => $negotiablMerchantId,
			'password' => $negotiablApiToken,
			'currency' => $store_currency,
			'currency_symbol' => $store_currency_symbol
		) );
		$response = $reqObj->send( );
		if ( is_wp_error ( $response ) ) {
			return false;
		} elseif ( $reqObj->getResponseCode() != 200 ) {
			return false;
		}
		return true;
	}
	
	public function beforeSettingOutput( ) { 
		if( isset( $_REQUEST['connect'] ) && $_REQUEST['connect'] != 200 ) { 
			WC_Admin_Settings::add_error( __( 'Entered credentials are invalid.', NEGOTIABL_TEXTDOMAIN ) );
		} elseif( isset( $_REQUEST['connect'] ) && $_REQUEST['connect'] == 200 ) { 
			WC_Admin_Settings::add_message( __( 'Your settings have been saved.', NEGOTIABL_TEXTDOMAIN ) );
		}
		
	}
	
	public function wooNegotiablSettingTab( $settings_tabs ) {
        $settings_tabs[ $this->menuTab ] = __( 'Negotiabl', NEGOTIABL_TEXTDOMAIN );
        return $settings_tabs;
    }
	
	public function wooNegotiablSettingTabFields() { 
	    woocommerce_admin_fields( $this->getWooNegotiablsSettingsFields( ) );
	}

	public function wooNegotiablUpdateSettingTabFields() { 
	    woocommerce_update_options( $this->getWooNegotiablsSettingsFields( ) );
	}

	private function getWooNegotiablsSettingsFields() { 
		$settings = array( 
			'section_title' => array(
                'name'     => __( 'Negotiabl Settings', NEGOTIABL_TEXTDOMAIN ),
                'type'     => 'title',
                'desc'     => '',
                'id'       => 'wc_settings_tab_section_title'
            ),
			'woo_negotiabl_merchant_id' => array(
				'name'     => __( 'App Id', NEGOTIABL_TEXTDOMAIN ),
				'type'     => 'text',
				'id'       => 'woo_negotiabl_merchant_id',
				'custom_attributes' => array( 'autocomplete' => 'false' )
			),
			'woo_negotiabl_api_token' => array(
				'name' => __( 'Password', NEGOTIABL_TEXTDOMAIN ),
				'type' => 'password',
				'id'   => 'woo_negotiabl_api_token',
				'custom_attributes' => array( 'autocomplete' => 'false' )
			),
			/* 'woo_negotiabl_popup_time' => array(
				'name' => __( 'Show Pop up After Following Minutes', NEGOTIABL_TEXTDOMAIN ),
				'type' => 'number',
				'desc' => __( 'Please enter in minutes, By default will be 5 minutes.', NEGOTIABL_TEXTDOMAIN ),
				'id'   => 'woo_negotiabl_popup_time',
				'custom_attributes' => array( 'max' => 30, 'min' => 0 )
			), */
			'woo_negotiabl_use_ssl' => array(
				'name' => __( 'Check if Store supports https', NEGOTIABL_TEXTDOMAIN ),
				'type' => 'checkbox',
				'default' => '',
				/* 'desc' => __( 'Please check if store supports https.', NEGOTIABL_TEXTDOMAIN ), */
				'id'   => 'woo_negotiabl_use_ssl'
			),
			'section_end' => array(
				 'type' => 'sectionend',
				 'id' => 'wc_settings_tab_section_end'
			)
		);
		
		return apply_filters( 'wc_settings_tab_negotiabl_settings', $settings );
	}
}
