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
 * The request object contains the parameters for a data query.
 * Server responses are cached in this object.
 * 
 * @phpstan-type JSONType array{
 *     space: array{
 *         s: list<int>
 *     },
 *     columns: list<string>,
 *     period: array{
 *         p1: period\Period,
 *         p2?: period\Period
 *     },
 *     max-results: int,
 *     page-num: int,
 *     options: array{
 *         ignore_null_properties: bool
 *     },
 *     filter?: FormattedFiltersType,
 *     evo?: Evolution,
 *     sort?: list<string>
 * }
 * @phpstan-type JSONTotalsType array{
 *     space: array{
 *         s: list<int>
 *     },
 *     columns: list<string>,
 *     period: array{
 *         p1: period\Period,
 *         p2?: period\Period
 *     },
 *     options: array{
 *         ignore_null_properties: bool
 *     },
 *     filter?: FormattedFiltersType,
 *     evo?: Evolution
 * }
 * @phpstan-type FormattedFiltersType array{
 *     metric?: filter\Filter,
 *     property?: filter\Filter
 * }
 * @phpstan-import-type APIResponseType from Client
 */
class Request implements \JsonSerializable {
    
    /**
     * Maximum number of results in one page.
     */
    const MAX_PAGE_RESULTS = 10000;
    /**
     * Maximum number of pages in a request.
     */
    const MAX_PAGES = 20;
    
    private Client $client;
    /** @var list<int> */
    private array $sites;
    /** @var list<string> */
    private array $columns;
    private period\Period $period;
    private ?period\Period $cmp_period;
    private ?filter\Filter $metric_filter;
    private ?filter\Filter $property_filter;
    private ?Evolution $evolution;
    /** @var list<string> */
    private array $sort;
    private int $max_results;
    private int $page_num;
    private bool $ignore_null_properties;
    private ResultRowList $result_rows;
    private object $rowcount_raw;
    private object $total_raw;
    
    /**
     * Constructs a new request.
     * @param Client $client API-connection.
     * @param array{
     *     sites: list<int>,
     *     columns: list<string>,
     *     period: period\Period,
     *     cmp_period?: ?period\Period,
     *     metric_filter?: ?filter\Filter,
     *     property_filter?: ?filter\Filter,
     *     evolution?: ?Evolution,
     *     sort?: ?list<string>,
     *     max_results?: ?int,
     *     ignore_null_properties?: ?bool
     * } $params Parameters:
     * sites: List of site ID's.
     * columns: List of metrics and properties.
     * period: Analysis period.
     * cmp_period: Comparison period (optional)
     * metric_filter: Filters on metrics (optional)
     * property_filter: Filters on properties (optional)
     * evolution: Not implemented yet (optional)
     * sort: List of properties/metrics according to which the results will be
     * sorted (optional).
     * max_results: Maximum number of results (default and maximum: 200000
     * (200k))
     * ignore_null_properties: When set to true, null values will not be
     * included in the results (default false)
     */
    public function __construct( Client $client, $params ) {
        $this->client = $client;
        $this->page_num = 1;
        
        $this->sites = $params['sites'];
        $this->columns = $params['columns'];
        $this->period = $params['period'];
        if ( array_key_exists('cmp_period', $params) ) {
            $this->cmp_period = $params['cmp_period'];
        }
        if ( array_key_exists('metric_filter', $params) ) {
            $this->metric_filter = $params['metric_filter'];
        }
        if ( array_key_exists('property_filter', $params) ) {
            $this->property_filter = $params['property_filter'];
        }
        if ( array_key_exists('evolution', $params) ) {
            $this->evolution = $params['evolution'];
        }
        if ( array_key_exists('sort', $params) ) {
            $this->sort = $params['sort'];
        } else {
            $this->sort = [];
        }
        if ( array_key_exists('max_results', $params) ) {
            $this->max_results = $params['max_results'];
        } else {
            $this->max_results = self::MAX_PAGES * self::MAX_PAGE_RESULTS;
        }
        if ( array_key_exists('ignore_null_properties', $params) ) {
            $this->ignore_null_properties = $params['ignore_null_properties'];
        } else {
            $this->ignore_null_properties = false;
        }
    }
    
    /**
     * @return JSONType
     */
    public function jsonSerialize(): array {
        $response = [
            'space' => [
                's' => $this->sites
            ],
            'columns' => $this->columns,
            'period' => [
                'p1' => $this->period
            ],
            'max-results' => $this->get_max_page_results(),
            'page-num' => $this->page_num,
            'options' => [
                'ignore_null_properties' => $this->ignore_null_properties
            ]
        ];
        if ( isset($this->cmp_period) ) {
            $response['period']['p2'] = $this->cmp_period;
        }
        $formatted_filters = $this->format_filters();
        if ( count($formatted_filters) > 0 ) {
            $response['filter'] = $formatted_filters;
        }
        if ( isset($this->evolution) ) {
            $response['evo'] = $this->evolution;
        }
        if ( count($this->sort) > 0 ) {
            $response['sort'] = $this->sort;
        }
        return $response;
    }
    
    /**
     * Serialization without some properties for getRowCount and getTotal
     * queries.
     * @return JSONTotalsType
     */
    private function jsonSerialize_totals(): array {
        $response = $this->jsonSerialize();
        unset($response['sort']);
        unset($response['max-results']);
        unset($response['page-num']);
        return $response;
    }
    
    /**
     * Format the filters for serialization.
     * @return FormattedFiltersType
     */
    private function format_filters(): array {
        $response = [];
        if ( isset($this->metric_filter) ) {
            $response['metric'] = $this->metric_filter;
        }
        if ( isset($this->property_filter) ) {
            $response['property'] = $this->property_filter;
        }
        return $response;
    }
    
    /**
     * Execute a query and return a result object with multiple pages of
     * responses from the API.
     * Server responses are cached in this object. Call Request::clear() to
     * clear the cache.
     * Use ATInternet::get_result_rows() to get results without having to deal
     * with paging.
     * https://developers.atinternet-solutions.com/piano-analytics/data-api/technical-information/methods#getdata
     * @return ResultPageList
     */
    public function get_result_pages(): ResultPageList {
        return new ResultPageList($this);
    }
    
    /**
     * Execute a data query. Only one page of results is returned. This page may
     * not include all data.
     * Use ATInternet::get_result_pages() to get a more complete result.
     * Use ATInternet::get_result_rows() to get results without having to deal
     * with paging.
     * https://developers.atinternet-solutions.com/piano-analytics/data-api/technical-information/methods#getdata
     * @return APIResponseType
     * @throws APIError
     */
    public function get_result_page( int $page_num ): object {
        // In a previous version pages where cached in the object. This causes
        // memory errors.
        $this->page_num = $page_num;
        return $this->client->request('getData', $this);
    }
    
    /**
     * Execute the query and return a result object with all rows from the API.
     * Server responses are cached in this object. Call Request::clear() to
     * clear the cache.
     * https://developers.atinternet-solutions.com/piano-analytics/data-api/technical-information/methods#getdata
     * @return ResultRowList
     */
    public function get_result_rows(): ResultRowList {
        $this->result_rows ??= new ResultRowList($this);
        return $this->result_rows;
    }
    
    /**
     * Returns the number of results for a query.
     * Returns the entire response object from the API.
     * Server responses are cached in this object. Call Request::clear() to
     * clear the cache.
     * https://developers.atinternet-solutions.com/piano-analytics/data-api/technical-information/methods#getrowcount
     * @return APIResponseType
     * @throws APIError
     */
    public function get_rowcount_raw(): object {
        $this->rowcount_raw ??= $this->client->request('getRowCount', $this->jsonSerialize_totals());
        return $this->rowcount_raw;
    }
    
    /**
     * Returns the number of results for a query. max_results is ignored.
     * Server responses are cached in this object. Call Request::clear() to
     * clear the cache.
     * https://developers.atinternet-solutions.com/piano-analytics/data-api/technical-information/methods#getrowcount
     * @throws APIError
     */
    public function get_rowcount(): int {
        $rowcount_raw = $this->get_rowcount_raw();
        if ( !isset($rowcount_raw->RowCounts) ) {
            throw new ATInternetError('Key RowCounts missing in response');
        }
        return $rowcount_raw->RowCounts[0]->RowCount;
    }
    
    /**
     * Get the totals for each metric in a request. max_results is ignored.
     * Returns the entire response object from the API.
     * Server responses are cached in this object. Call Request::clear() to
     * clear the cache.
     * https://developers.atinternet-solutions.com/piano-analytics/data-api/technical-information/methods#gettotal
     * @return APIResponseType
     * @throws APIError
     */
    public function get_total_raw(): object {
        $this->total_raw ??= $this->client->request('getTotal', $this->jsonSerialize_totals());
        return $this->total_raw;
    }
    
    /**
     * Get the totals for each metric in a request.
     * Server responses are cached in this object. Call Request::clear() to
     * clear the cache.
     * https://developers.atinternet-solutions.com/piano-analytics/data-api/technical-information/methods#gettotal
     * @return object
     * @throws APIError
     */
    public function get_total(): object {
        $total_raw = $this->get_total_raw();
        if ( !isset($total_raw->DataFeed) ) {
            throw new ATInternetError('Key DataFeed missing in response');
        }
        $data = $total_raw->DataFeed->Rows[0];
        foreach ( $data as $key => $value ) {
            if ( $value === '-' ) {
                unset($data->$key);
            }
        }
        return $data;
    }
    
    /**
     * Get the maximum number of results for the current page.
     */
    private function get_max_page_results(): int {
        return max(0,min(self::MAX_PAGE_RESULTS, $this->max_results-self::MAX_PAGE_RESULTS*($this->page_num-1)));
    }
    
    /**
     * Returns true if the current page is the page after the last page that
     * contains results.
     * @param int $page_num The current page.
     */
    public function is_after_last_page( int $page_num ): bool {
        $this->page_num = $page_num;
        return $this->get_max_page_results() === 0;
    }
    
}
