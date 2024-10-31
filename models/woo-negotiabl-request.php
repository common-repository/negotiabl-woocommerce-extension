<?php 
class Negotiabl_WooCommerceRequest { 
	private $url		= null;
	private $data		= null;
	private $headers	= array();
	private $response	= null;
	
	public function __construct( $url, $data ) { 
		$this->data		= $data;
	 	$this->url		= $url;
	}
	
	function send( ) {
		if( $this->url ) {
			
			$key = negotiabl_getKey();
			$password = negotiabl_getPassword();
			
			$this->headers['authorization'] = 'Basic '.base64_encode( $key . ':' . $password );
			$this->headers['Content-Type'] = "application/json";
			$this->headers['x-wc-webhook-source']  = get_site_url( '/' );

			$args = array(
				'headers' => $this->headers,
				'body' => json_encode($this->data)
			);
			$this->response = wp_remote_post($this->url, $args);
			return $this->response;
		}
		return false;
	}
	
	function getResponse( ) { 
		return $this->response;
	}

	function getResponseCode (){
		if(! empty($this->response) ){
			return wp_remote_retrieve_response_code($this->response);
		}
		return false;
	}
}