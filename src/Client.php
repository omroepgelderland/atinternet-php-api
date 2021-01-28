<?php
/**
 * Copyright 2022 Omroep Gelderland
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * 
 * @author Remy Glaser <rglaser@gld.nl>
 * @package atinternet_php_api
 */

namespace atinternet_php_api;

/**
 * Main API class.
 */
class Client {
	
	private string $access_key;
	private string $secret_key;
	
	/**
	 * Construct a new API connection.
	 * @param string $access_key Access key provided by AT Internet.
	 * @param string $secret_key Secret key.
	 */
	public function __construct( string $access_key, string $secret_key ) {
		$this->access_key = $access_key;
		$this->secret_key = $secret_key;
	}
	
	/**
	 * Execute an API request.
	 * @param string $method API method.
	 * @param Request|array $request JSON-serializable request object.
	 * @return \stdClass API response.
	 * @throws ATInternetError
	 */
	public function request( string $method, $request ): \stdClass {
		$ch = curl_init();
		if ( $ch === false ) {
			throw new ATInternetError('curl error');
		}
		try {
			$res = curl_setopt_array($ch, [
				CURLOPT_URL => sprintf(
					'https://api.atinternet.io/v3/data/%s',
					$method
				),
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => json_encode($request),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTPHEADER => $this->get_headers()
			]);
			if ( $res === false ) {
				throw new ATInternetError('curl error');
			}
			$curl_data = curl_exec($ch);
			$response_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
			if ( $response_code >= 400 ) {
				throw new ATInternetError(sprintf('HTTP error %d', $response_code));
			}
			if ( $curl_data === false ) {
				throw new ATInternetError(curl_error($ch), curl_errno($ch));
			}
			$response = json_decode($curl_data);
			if ( $response === null ) {
				throw new ATInternetError($curl_data);
			}
			if ( isset($response->ErrorCode) ) {
				$error_code = $response->ErrorCode;
				$error_message = $response->ErrorMessage;
				$error_name = $response->ErrorName;
				switch ( $error_name ) {
					case 'InvalidSort':
						throw new InvalidSort($error_message, $error_code);
					case 'InvalidMaxResults':
						throw new InvalidMaxResults($error_message, $error_code);
					case 'DataNotReady':
						throw new DataNotReady($error_message, $error_code);
					case 'InvalidPeriod':
						throw new InvalidPeriod($error_message, $error_code);
					case 'InvalidSpace':
						throw new InvalidSpace($error_message, $error_code);
					default:
						throw new ATInternetError($error_message, $error_code);
				}
			}
		} finally {
			curl_close($ch);
		}
		return $response;
	}
	
	/**
	 * Returns the headers for API requests.
	 * @return array
	 */
	private function get_headers(): array {
		return [
			sprintf(
				'x-api-key: %s_%s',
				$this->access_key,
				$this->secret_key
			),
			'Content-type: application/json'
		];
	}
		
}
