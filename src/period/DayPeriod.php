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

namespace atinternet_php_api\period;

/**
 * https://developers.atinternet-solutions.com/api-documentation/v3/#absolute-periods
 */
class DayPeriod implements AbsolutePeriod {
	
	private \DateTime $start;
	private \DateTime $end;
	private bool $include_time;
	
	/**
	 * 
	 * @param \DateTime $start
	 * @param \DateTime $end
	 * @param bool $include_time Whether to include the time values of the start
	 * and end times (default false).
	 */
	public function __construct( \DateTime $start, \DateTime $end, bool $include_time = false ) {
		$this->start = $start;
		$this->end = $end;
		$this->include_time = $include_time;
	}
	
	public function jsonSerialize(): array {
		$format = $this->include_time ? 'Y-m-d\TH:i:s' : 'Y-m-d';
		return [
			[
				'type' => 'D',
				'start' => $this->start->format($format),
				'end' => $this->end->format($format)
			]
		];
	}

	/**
	 * Creates a period for only the current day.
	 */
	public static function today(): DayPeriod {
		return new self(
			(new \DateTime())->setTime(0, 0, 0, 0),
			new \DateTime(),
			false
		);
	}
	
}
