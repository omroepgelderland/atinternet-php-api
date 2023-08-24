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
 * Represents a filter statement.
 * https://developers.atinternet-solutions.com/api-documentation/v3/#filter
 */
class FilterEndpoint implements Filter {
    
    /**
     * Filter for integers, strings, dates and booleans
     */
    public const EQUALS = '$eq';
    
    /**
     * Filter for integers, string and booleans
     */
    public const NOT_EQUALS = '$neq';
    
    /**
     * Filter for integers and strings
     */
    public const IN_ARRAY = '$in';
    
    /**
     * Filter for integers and dates
     */
    public const GREATER = '$gt';
    
    /**
     * Filter for integers and dates
     */
    public const GREATER_OR_EQUAL = '$gte';
    
    /**
     * Filter for integers and dates
     */
    public const LOWER = '$lt';
    
    /**
     * Filter for integers and dates
     */
    public const LOWER_OR_EQUAL = '$lte';
    
    /**
     * Filter for integers, strings and booleans
     */
    public const IS_NULL = '$na';
    
    /**
     * Filter for integers, strings and booleans
     */
    public const IS_UNDEFINED = '$undefined';
    
    /**
     * combination of IS_NULL and IS_UNDEFINED
     * Filter for integers, strings and booleans
     */
    public const IS_EMPTY = '$empty';
    
    /**
     * Filter for strings
     */
    public const CONTAINS = '$lk';
    
    /**
     * Filter for strings
     */
    public const NOT_CONTAINS = '$nlk';
    
    /**
     * Filter for strings
     */
    public const STARTS_WITH = '$start';
    
    /**
     * Filter for strings
     */
    public const NOT_STARTS_WITH = '$nstart';
    
    /**
     * Filter for strings
     */
    public const ENDS_WITH = '$end';
    
    /**
     * Filter for strings
     */
    public const NOT_ENDS_WITH = '$nend';

    /**
     * Compare a datetime field to the period of the analysis.
     * Possible expressions:
     * start: Is equal to the start of the time period
     * end: Is equal to the end of the period.
     * all: Is equal to the time period.
     */
    public const PERIOD = '$period';
    
    private string $field;
    private string $operator;
    private $expression;
    
    /**
     * 
     * @param string $field Property or metric to compare.
     * @param string $operator Comparison operator.
     * @param mixed $expression Comparison expression (integer, string, date or array)
     */
    public function __construct( string $field, string $operator, $expression ) {
        $this->field = $field;
        $this->operator = $operator;
        $this->expression = $expression;
    }
    
    public function jsonSerialize(): mixed {
        return [
            $this->field => [
                $this->operator => $this->expression
            ]
        ];
    }
    
}
