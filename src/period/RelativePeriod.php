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

namespace atinternet_php_api\period;

/**
 * https://developers.atinternet-solutions.com/piano-analytics/data-api/parameters/period#relative-periods
 * 
 * @phpstan-type JSONType list<array{
 *     type: 'R',
 *     granularity: string,
 *     offset: int
 * }>
 */
class RelativePeriod implements Period {
    
    public const YEAR = 'Y';
    public const QUARTER = 'Q';
    public const MONTH = 'M';
    public const WEEK = 'W';
    public const DAY = 'D';
    
    private string $granularity;
    private int $offset;
    
    /**
     * 
     * @param string $granularity Time period
     * @param int $offset Offset relative to the current data. Can be negative.
     */
    public function __construct( string $granularity, int $offset ) {
        $this->granularity = $granularity;
        $this->offset = $offset;
    }
    
    /**
     * @return JSONType
     */
    public function jsonSerialize(): array {
        return [
            [
                'type' => 'R',
                'granularity' => $this->granularity,
                'offset' => $this->offset
            ]
        ];
    }
    
}
