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

namespace atinternet_php_api;

/**
 * Iterable set of pages with data results.
 */
class ResultPageList implements \Iterator {
    
    private Request $request;
    private int $page_num;
    
    /**
     * 
     * @param Request $request Data request.
     */
    public function __construct( Request $request ) {
        $this->request = $request;
        $this->page_num = 1;
    }

    public function current(): \stdClass {
        $data = $this->request->get_result_page($this->page_num);
        return $data;
    }
    
    public function key(): \scalar {
        return $this->page_num;
    }
    
    public function next(): void {
        $this->page_num++;
    }
    
    public function rewind(): void {
        $this->page_num = 1;
    }
    
    public function valid(): bool {
        return !$this->request->is_after_last_page($this->page_num);
    }
}
