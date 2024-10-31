<?php
/**
 * QBO API Client Class
*/
	class CP_Client {
	
		/**
		 * API base endpoint
		 */
		const API_ENDPOINT = 'wc-api/v2/';
	
		/**
		 * The HASH alorithm to use for oAuth signature, SHA256 or SHA1
		 */
		const HASH_ALGORITHM = 'SHA256';
	
		/**
		 * The API URL
		 * @var string
		 */
		private $_api_url;
	
		/**
		 * The WooCommerce Consumer Key
		 * @var string
		 */
		private $_consumer_key;
	
		/**
		 * The WooCommerce Consumer Secret
		 * @var string
		 */
		private $_consumer_secret;
	
		/**
		 * If the URL is secure, used to decide if oAuth or Basic Auth must be used
		 * @var boolean
		 */
		private $_is_ssl;
	
		/**
		 * Return the API data as an Object, set to false to keep it in JSON string format
		 * @var boolean
		 */
		private $_return_as_object = true;
	
		/**
		 * Default contructor
		 * @param string  $consumer_key    The consumer key
		 * @param string  $consumer_secret The consumer secret
		 * @param string  $store_url       The URL to the WooCommerce store
		 * @param boolean $is_ssl          If the URL is secure or not, optional
		 */
		public function __construct( $consumer_key, $consumer_secret, $store_url, $is_ssl = false ) {
			
			if ( ! empty( $consumer_key ) && ! empty( $consumer_secret ) && ! empty( $store_url ) ) {
				$this->_api_url = (  rtrim($store_url,'/' ) . '/' ) . self::API_ENDPOINT;
				$this->set_consumer_key( $consumer_key );
				$this->set_consumer_secret( $consumer_secret );
				$this->set_is_ssl( $is_ssl );
			} else if ( ! isset( $consumer_key ) && ! isset( $consumer_secret ) ) {
				//throw new Exception( 'Error: __construct() - Consumer Key / Consumer Secret missing.' );
			} else {
				//throw new Exception( 'Error: __construct() - Store URL missing.' );
			}
		}
		
		public function qbo_add_customer( $order_id, $data = array(), $license  ) {
			return $this->_make_api_call( 'qbo/' . $order_id.'/customer/'.$license, $data, 'POST' );
		}
		public function qbo_add_order( $order_id, $data = array(), $license  ) {
			return $this->_make_api_call( 'qbo/' . $order_id.'/order/'.$license, $data, 'POST' );
		}
		public function qbo_update_order( $order_id, $data = array(), $license  ) {
			return $this->_make_api_call( 'qbo/' . $order_id.'/order/update/'.$license, $data, 'POST' );
		}
		public function qb_add_items( $order_id, $product = array() , $license ) {
			return $this->_make_api_call( 'qb/items/add/'.$license, $product, 'POST' );
		}
		public function qbo_get_items( $prods = array(), $license ) {
			return $this->_make_api_call( 'qbo/items/get/'.$license, $prods , 'POST' );
		}
		public function qbo_sync_item( $prods = array(), $license  ){
			return $this->_make_api_call( 'qbo/item/sync/'.$license, $prods , 'POST' );
		}
		public function qbo_update_item( $data, $license  ){
			return $this->_make_api_call( 'qbo/item/update/'.$license, $data , 'POST' );
		}
		public function qbo_get_report( $license  ) {
			return $this->_make_api_call( 'qbo/report/get/'.$license);
		}
		public function qbo_get_sales_tax_info( $license  ) {
			return $this->_make_api_call( 'qbo/salestax/get/'.$license);
		}
		public function qbo_get_accounts( $license ){
			return $this->_make_api_call( 'qbo/accounts/get/'.$license);
		}
		public function qbo_get_deposit_accounts( $license ){
			return $this->_make_api_call( 'qbo/accounts/get/deposit'.$license);
		}
		public function qbo_get_sales_tax_codes( $license ) {
			return $this->_make_api_call( 'qbo/salestaxcodes/get/'.$license);
		}
		public function qbo_get_payment_methods( $license ) {
			return $this->_make_api_call( 'qbo/paymentmethods/get/'.$license);
		}
		public function get_should_show_usage( $license ){
			return $this->_make_api_call( 'qbo/usage/show/'.$license);
		}
		public function get_cp_usage( $license ){
			return $this->_make_api_call( 'qbo/usage/get/'.$license);
		}
		public function get_cp_messages( $license ){
			return $this->_make_api_call( 'qbo/messages/get/'.$license);
		}
		public function get_cp_account( $license ){
			return $this->_make_api_call('qbo/accountinfo/get/'.$license);
		}
		public function cp_activate_license( $license, $data = array() ){
			return $this->_make_api_call('cp/edd/license/'.$license.'/activate', $data, 'POST');
		}
		public function cp_deactivate_license( $license, $data = array() ){
			return $this->_make_api_call('cp/edd/license/'.$license.'/deactivate', $data, 'POST');
		}
		public function check_service( $license, $data = array() ){
			return $this->_make_api_call('cp/edd/license/'.$license.'/check', $data, 'POST');
		}
		public function get_license( $data = array() ){
			return $this->_make_api_call('cp/license/get', $data, 'POST');
		}
		public function get_notices( $license, $data = array() ){
			return $this->_make_api_call('cp/license/'.$license.'/notices/get', $data, 'POST');
		}
		/**
	 * Set the consumer key
	 * @param string $consumer_key
	 */
	public function set_consumer_key( $consumer_key ) {
		$this->_consumer_key = $consumer_key;
	}

	/**
	 * Set the consumer secret
	 * @param string $consumer_secret
	 */
	public function set_consumer_secret( $consumer_secret ) {
		$this->_consumer_secret = $consumer_secret;
	}

	/**
	 * Set SSL variable
	 * @param boolean $is_ssl
	 */
	public function set_is_ssl( $is_ssl ) {
		if ( $is_ssl == '' ) {
			if ( strtolower( substr( $this->_api_url, 0, 5 ) ) == 'https' ) {
				$this->_is_ssl = true;
			} else $this->_is_ssl = false;
		} else $this->_is_ssl = $is_ssl;
	}

	/**
	 * Set the return data as object
	 * @param boolean $is_object
	 */
	public function set_return_as_object( $is_object = true ) {
		$this->_return_as_object = $is_object;
	}

	/**
	 * Make the call to the API
	 * @param  string $endpoint
	 * @param  array  $params
	 * @param  string $method
	 * @return mixed|json string
	 */
	private function _make_api_call( $endpoint, $params = array(), $method = 'GET' ) {
		$ch = curl_init();
		$post = false;
		if( 'POST' == $method){
			$post 	= $params;
			$params = array();
		}
		// Check if we must use Basic Auth or 1 legged oAuth, if SSL we use basic, if not we use OAuth 1.0a one-legged
		if ( $this->_is_ssl ) {
			curl_setopt( $ch, CURLOPT_USERPWD, $this->_consumer_key . ":" . $this->_consumer_secret );
		} else {
			$params['oauth_consumer_key'] = $this->_consumer_key;
			$params['oauth_timestamp'] = time();
			$params['oauth_nonce'] = sha1( microtime() );
			$params['oauth_signature_method'] = 'HMAC-' . self::HASH_ALGORITHM;
			$params['oauth_signature'] = $this->generate_oauth_signature( $params, $method, $endpoint );
		}

		if ( isset( $params ) && is_array( $params ) ) {
			$paramString = '?' . http_build_query( $params );
		} else {
			$paramString = null;
		}

		// Set up the enpoint URL
		
		
		curl_setopt( $ch, CURLOPT_URL, $this->_api_url . $endpoint . $paramString );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 30 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_REFERER, get_home_url() );
		

        if ( 'POST' === $method && $post ) {
			curl_setopt( $ch, CURLOPT_POST, true );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $post ) );
    	} else if ( 'DELETE' === $method ) {
			curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'DELETE' );
    	}

		$return = curl_exec( $ch );
		
		$code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		
		if ( $this->_return_as_object ) {
			$return = json_decode( $return );
		}

		if ( empty( $return ) ) {
			$return = '{"errors":[{"code":"' . $code . '","message":"cURL HTTP error ' . $code . '"}]}';
			$return = json_decode( $return );
		}
		curl_close($ch); 
		return $return;
	}

	/**
	 * Generate oAuth signature
	 * @param  array  $params
	 * @param  string $http_method
	 * @param  string $endpoint
	 * @return string
	 */
	public function generate_oauth_signature( $params, $http_method, $endpoint ) {
		$base_request_uri = rawurlencode( $this->_api_url . $endpoint );

		// normalize parameter key/values and sort them
		$params = $this->normalize_parameters( $params );
		uksort( $params, 'strcmp' );

		// form query string
		$query_params = array();
		foreach ( $params as $param_key => $param_value ) {
			$query_params[] = $param_key . '%3D' . $param_value; // join with equals sign
		}

		$query_string = implode( '%26', $query_params ); // join with ampersand

		// form string to sign (first key)
		$string_to_sign = $http_method . '&' . $base_request_uri . '&' . $query_string;
		return base64_encode( hash_hmac( self::HASH_ALGORITHM, $string_to_sign, $this->_consumer_secret, true ) );
	}

	/**
	 * Normalize each parameter by assuming each parameter may have already been
	 * encoded, so attempt to decode, and then re-encode according to RFC 3986
	 *
	 * Note both the key and value is normalized so a filter param like:
	 *
	 * 'filter[period]' => 'week'
	 *
	 * is encoded to:
	 *
	 * 'filter%5Bperiod%5D' => 'week'
	 *
	 * This conforms to the OAuth 1.0a spec which indicates the entire query string
	 * should be URL encoded
	 *
	 * @since 0.3.1
	 * @see rawurlencode()
	 * @param array $parameters un-normalized pararmeters
	 * @return array normalized parameters
	 */
	private function normalize_parameters( $parameters ) {

		$normalized_parameters = array();

		foreach ( $parameters as $key => $value ) {

			// percent symbols (%) must be double-encoded
			$key   = str_replace( '%', '%25', rawurlencode( rawurldecode( $key ) ) );
			$value = str_replace( '%', '%25', rawurlencode( rawurldecode( $value ) ) );

			$normalized_parameters[ $key ] = $value;
		}

		return $normalized_parameters;
	}


}
