<?php
/**
 * Copyright 2023 Omroep Gelderland
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * 
 * @author Remy Glaser <rglaser@gld.nl>
 */

namespace atinternet_php_api;

/**
 * Main API class.
 * 
 * @phpstan-type APIResponseType object{
 *     DataFeed?: object{
 *         Columns: list<object{
 *             Category: string,
 *             Name: string,
 *             Type: string,
 *             CustomerType: string,
 *             Label: string,
 *             Description: string,
 *             Filterable: bool
 *         }>,
 *         Rows: list<object>,
 *         Context: object
 *     },
 *     RowCounts?: list<object{
 *         RowCount: int
 *     }>
 * }
 * @phpstan-type APIErrorResponseType object{
 *     ErrorMessage?: string,
 *     ErrorType?: string
 * }
 */
class Client {
    
    private string $access_key;
    private string $secret_key;
    
    /**
     * Construct a new API connection.
     * @param string $access_key Access key provided by Piano analytics.
     * @param string $secret_key Secret key.
     */
    public function __construct( string $access_key, string $secret_key ) {
        $this->access_key = $access_key;
        $this->secret_key = $secret_key;
    }
    
    /**
     * Execute an API request.
     * @param string $method API method.
     * @param Request|array<mixed> $request JSON-serializable request object.
     * @return APIResponseType API response.
     * @throws APIError
     */
    public function request( string $method, Request|array $request ): object {
        $ch = curl_init();
        if ( $ch === false ) {
            throw new APIError('curl error');
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
                throw new APIError('curl error');
            }
            $response_raw = \curl_exec($ch);
            if ( $response_raw === false ) {
                throw new APIError(curl_error($ch), curl_errno($ch));
            }
            /**
             * @var APIResponseType|APIErrorResponseType|null
             */
            $response = json_decode($response_raw);
            $http_status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

            $error_message = curl_error($ch);
            $error_type = null;

            if ( isset($response->ErrorMessage) ) {
                $error_message = $response->ErrorMessage;
            }
            if ( isset($response->ErrorType) ) {
                $error_type = $response->ErrorType;
            }

            if ( $http_status >= 400 || isset($response->ErrorMessage) || isset($response->ErrorType) ) {
                $error_message = isset($error_type) ? "{$error_type}: {$error_message}" : $error_message;
                $e = new APIError($error_message, $http_status);
                $e->type = $error_type;
                throw $e;
            }
            if ( $response === null ) {
                throw new APIError($response_raw, $http_status);
            }
        } finally {
            curl_close($ch);
        }
        /** @var APIResponseType */
        return $response;
    }
    
    /**
     * Returns the headers for API requests.
     * @return list<string>
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
