# At Internet (piano analytics) PHP client

This library enables you to get queries from the AT Internet Data API v3.
This is a third-party library.
A subscription to AT Internet is required.

## Requirements ##
* [PHP 7.4 or higher](https://www.php.net/)

## Installation ##

You can use **Composer** or **Download the Release**

### Composer

The preferred method is via [composer](https://getcomposer.org/). Follow the
[installation instructions](https://getcomposer.org/doc/00-intro.md) if you do not already have
composer installed.

Once composer is installed, execute the following command in your project root to install this library:

```sh
composer require omroepgelderland/atinternet-php-api
```

Finally, be sure to include the autoloader:

```php
require_once '/path/to/your-project/vendor/autoload.php';
```

## Example

```php

require_once __DIR__.'/vendor/autoload.php';

use \atinternet_php_api\filter\FilterListAnd;
use \atinternet_php_api\filter\FilterEndpoint;

// Create API connection
$at = new \atinternet_php_api\Client($access_key, $secret_key);

// Create data request parameters.
$request = new \atinternet_php_api\Request($at, [
	'sites' => [$site_id],
	'columns' => [
		'date',
		'article_id',
		'site_id',
		'domain',
		'platform',
		'device_type',
		'os_group',
		'm_unique_visitors',
		'm_visits',
		'm_page_loads'
	],
	'period' => new \atinternet_php_api\period\DayPeriod(
		new \DateTime('2022-06-01'),
		new \DateTime('2022-06-01')
	),
	'sort' => [
		'-m_page_loads'
	],
	'property_filter' => new FilterListAnd(
		new FilterEndpoint(
			'article_id',
			FilterEndpoint::IS_EMPTY,
			false
		),
		new FilterEndpoint(
			'domain',
			FilterEndpoint::CONTAINS,
			[
				'example.nl',
				'www.example.nl'
			]
		)
	)
]);

// All results
foreach ( $request->get_result_rows() as $item ) {
	var_dump($item);
}

// Number of results
var_dump($request->get_rowcount());

// Cumulative metrics for all resulting rows
var_dump($request->get_total());

```
