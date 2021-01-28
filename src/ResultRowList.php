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
 * Iterator for all result rows across multiple pages.
 */
class ResultRowList implements \Iterator {
	
	private ResultPageList $result_pages;
	private array $rows;
	private int $row_index;
	private int $total_index;
	
	/**
	 * 
	 * @param Request $request Data request.
	 */
	public function __construct( Request $request ) {
		$this->result_pages = new ResultPageList($request);
		$this->rewind();
	}
	
	public function current(): \stdClass {
		return $this->get_rows()[$this->row_index];
	}
	
	public function key(): int {
		return $this->total_index;
	}
	
	public function next(): void {
		$this->row_index++;
		$this->total_index++;
		if ( $this->row_index >= $this->get_row_count() ) {
			unset($this->rows);
			$this->row_index = 0;
			$this->result_pages->next();
		}
	}
	
	public function rewind(): void {
		$this->result_pages->rewind();
		unset($this->rows);
		$this->row_index = 0;
		$this->total_index = 0;
	}
	
	public function valid(): bool {
		return $this->result_pages->valid() && $this->row_index < $this->get_row_count();
	}
	
	private function get_rows(): array {
		$this->rows ??= $this->result_pages->current()->DataFeed->Rows;
		return $this->rows;
	}
	
	private function get_row_count(): int {
		return count($this->get_rows());
	}
	
}
