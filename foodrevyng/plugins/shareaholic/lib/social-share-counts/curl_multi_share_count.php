<?php
/**
 * Shareaholic Multi Share Count
 *
 * @package shareaholic
 * @version 2.0.0.0
 */

require_once 'share_count.php';

/**
 * A class that implements ShareaholicShareCounts
 * This class will get the share counts by calling
 * the social services via curl_multi
 *
 * @package shareaholic
 */
class ShareaholicCurlMultiShareCount extends ShareaholicShareCount {
	/**
	 * This function should get all the counts for the
	 * supported services
	 *
	 * It should return an associative array with the services as
	 * the keys and the counts as the value.
	 *
	 * Example:
	 * array('facebook' => 12, 'pinterest' => 0, 'twitter' => 14, ...);
	 *
	 * @return Array an associative array of service => counts
	 */
	public function get_counts() {
		$services_length    = count( $this->services );
		$config             = self::get_services_config();
		$response           = array();
		$meta               = array();
		$response['status'] = 200;

		// Input Params.
		$show_raw             = isset( $this->options['show_raw'] ) ? $this->options['show_raw'] : '1';
		$show_response_header = isset( $this->options['show_response_header'] ) ? $this->options['show_response_header'] : '0';

		// array of curl handles.
		$curl_handles = array();

		// multi handle.
		$multi_handle = curl_multi_init();

		for ( $i = 0; $i < $services_length; $i++ ) {
			$service = $this->services[ $i ];

			if ( ! isset( $config[ $service ] ) ) {
				continue;
			}

			if ( isset( $config[ $service ]['prepare'] ) ) {
				$this->{$config[ $service ]['prepare']}( $this->url, $config );
			}

			// Create the curl handle.
			$curl_handles[ $service ] = curl_init();

			// set the curl options to make the request.
			$this->curl_setopts( $curl_handles[ $service ], $config, $service );

			// add the handle to curl_multi_handle.
			curl_multi_add_handle( $multi_handle, $curl_handles[ $service ] );
		}

		// Run curl_multi only if there are some actual curl handles.
		if ( count( $curl_handles ) > 0 ) {
			// While we're still active, execute curl.
			$running = null;
			do {
				$mrc = curl_multi_exec( $multi_handle, $running );
			} while ( $mrc == CURLM_CALL_MULTI_PERFORM );

			while ( $running && $mrc == CURLM_OK ) {
				// Wait for activity on any curl-connection
				if ( curl_multi_select( $multi_handle ) == -1 ) {
					usleep( 1 );
				}

				// Continue to exec until curl is ready to give us more data.
				do {
					$mrc = curl_multi_exec( $multi_handle, $running );
				} while ( $mrc == CURLM_CALL_MULTI_PERFORM );
			}

			// handle the responses.
			foreach ( $curl_handles as $service => $handle ) {
				if ( curl_errno( $handle ) ) {
					$response['status'] = 500;
				}

				$headers     = array();
				$body        = array();
				$headers_arr = array();
				$aHeaders    = array();

				// Parse header and body from response.
				$header_size = curl_getinfo( $handle, CURLINFO_HEADER_SIZE );
				$headers     = substr( curl_multi_getcontent( $handle ), 0, $header_size );
				$body        = substr( curl_multi_getcontent( $handle ), $header_size );

				// Explode header values.
				$headers_arr = explode( "\r\n", $headers );
				// Remove empty values.
				$headers_arr = array_filter( $headers_arr );
				// Convert to key=>value.
				foreach ( $headers_arr as $i => $line ) {
					if ( 0 === $i ) {
						$aHeaders['http_code'] = trim( $line );
					} else {
						list($key, $val)                = explode( ': ', $line, 2 );
						$aHeaders[ strtolower( $key ) ] = trim( $val );
					}
				}

				$result = array(
					'body'     => $body,
					'response' => array(
						'code' => curl_getinfo( $handle, CURLINFO_HTTP_CODE ),
					),
				);

				$callback = $config[ $service ]['callback'];

				// Facebook auth?
				if ( $service == 'facebook' && isset( $this->options['facebook_access_token'] ) ) {
					$counts = $this->$callback( $result, isset( $this->options['facebook_access_token'] ) );
				} else {
					$counts = $this->$callback( $result );
				}

				if ( is_numeric( $counts ) ) {
					$response['data'][ $service ] = $counts;
				}

				// Include Response Headers?
				if ( '1' === $show_response_header ) {
					$whitelisted_response_header_keys = array(
						'http_code',
						'x-app-usage',
						'expires',
						'cache-control',
					);
					$result_response_headers          = array(
						'response' => array(
							'header' => array_intersect_key( $aHeaders, array_flip( $whitelisted_response_header_keys ) ),
							'code'   => curl_getinfo( $handle, CURLINFO_HTTP_CODE ),
						),
					);
					$meta                             = array_merge( $meta, $result_response_headers );
				}

				// Include Raw Body & Headers?
				if ( '1' === $show_raw ) {
					$result_raw = array(
						'raw' => array(
							'body'    => $body,
							'headers' => $headers,
						),
					);
					$meta       = array_merge( $meta, $result_raw );
				}

				$this->raw_response[ $service ] = $meta;

				curl_multi_remove_handle( $multi_handle, $handle );
				curl_close( $handle );
			}
			curl_multi_close( $multi_handle );
		}
		return $response;
	}

	private function curl_setopts( $curl_handle, $config, $service ) {
		$facebook_access_token = isset( $this->options['facebook_access_token'] ) ? $this->options['facebook_access_token'] : false;
		$http2                 = isset( $this->options['http2'] ) ? $this->options['http2'] : '0';

		$timeout = isset( $config[ $service ]['timeout'] ) ? $config[ $service ]['timeout'] : 1000;
		$timeout = isset( $this->options['timeout'] ) ? $this->options['timeout'] : $timeout;

		// set the url to make the curl request to
		if ( $service == 'facebook' && $facebook_access_token ) {
			$url = $config[ $service ]['url_auth'];
			$url = str_replace( '%s', $this->url, $url );
			$url = str_replace( '%auth%', $facebook_access_token, $url );
			curl_setopt( $curl_handle, CURLOPT_URL, $url );
		} else {
			curl_setopt( $curl_handle, CURLOPT_URL, str_replace( '%s', $this->url, $config[ $service ]['url'] ) );
		}

		// other necessary settings:
		// CURLOPT_RETURNTRANSER means return output as string or not.
		curl_setopt_array(
			$curl_handle,
			array(
				CURLOPT_HEADER         => 1,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_TIMEOUT_MS     => $timeout,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
			)
		);

		// HTTP/2 support?
		if ( $http2 == '1' ) {
			curl_setopt( $curl_handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0 );
		}

		// set the http method? default: GET
		if ( $config[ $service ]['method'] === 'POST' ) {
			curl_setopt( $curl_handle, CURLOPT_POST, 1 );
		}

		// set the body and headers?
		$headers = isset( $config[ $service ]['headers'] ) ? $config[ $service ]['headers'] : array();
		$body    = isset( $config[ $service ]['body'] ) ? $config[ $service ]['body'] : null;

		if ( isset( $body ) ) {
			if ( isset( $headers['Content-Type'] ) && $headers['Content-Type'] === 'application/json' ) {
				$data_string = json_encode( $body );

				curl_setopt(
					$curl_handle,
					CURLOPT_HTTPHEADER,
					array(
						'Content-Type: application/json',
						'Content-Length: ' . strlen( $data_string ),
					)
				);

				curl_setopt( $curl_handle, CURLOPT_POSTFIELDS, $data_string );
			}
		}

		// set the useragent?
		$useragent = isset( $config[ $service ]['User-Agent'] ) ? $config[ $service ]['User-Agent'] : 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:24.0) Gecko/20100101 Firefox/24.0';
		curl_setopt( $curl_handle, CURLOPT_USERAGENT, $useragent );
	}


}
