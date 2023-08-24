<?php
/**
 * Copyright 2023 Omroep Gelderland
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

namespace atinternet_php_api\filter;

/**
 * Represents a combination of filters.
 * https://developers.atinternet-solutions.com/api-documentation/v3/#filter
 */
abstract class FilterList implements Filter {

    /**
     * @abstract
     * @var string
     */
    protected const OPERATOR = null;
    
    /** @var Filter[] */
    private array $filters;
    
    /**
     * @param Filter $filters,...
     */
    public function __construct( Filter ...$filters ) {
        $this->filters = $filters;
    }
    
    public function jsonSerialize(): mixed {
        return $this->get_formatted_filters();
    }
    
    private function get_formatted_filters(): array {
        return [
            static::OPERATOR => $this->filters
        ];
    }
    
}
