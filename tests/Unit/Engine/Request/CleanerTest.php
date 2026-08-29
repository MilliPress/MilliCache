<?php

use MilliCache\Engine\Request\Cleaner;
use MilliCache\Engine\Cache\Config;

uses()
	->beforeEach(function () {
		// Save original server state.
		$this->original_server = $_SERVER;
		$this->original_get = $_GET;
		$this->original_request = $_REQUEST;

		$this->config = new Config(
			3600,
			600,
			true,
			false,
			array(),
			array(),
			array(),
			array('utm_source', 'fbclid'),
			array()
		);

		$this->cleaner = new Cleaner($this->config);
	})
	->afterEach(function () {
		// Restore original state.
		$_SERVER = $this->original_server;
		$_GET = $this->original_get;
		$_REQUEST = $this->original_request;
	});

describe('Cleaner', function () {

	describe('clean_conditional_headers', function () {
		it('removes ETag and Last-Modified validators', function () {
			$_SERVER['HTTP_IF_NONE_MATCH'] = 'etag123';
			$_SERVER['HTTP_IF_MODIFIED_SINCE'] = 'date';

			$this->cleaner->clean_conditional_headers();

			expect($_SERVER)->not->toHaveKey('HTTP_IF_NONE_MATCH');
			expect($_SERVER)->not->toHaveKey('HTTP_IF_MODIFIED_SINCE');
		});

		it('leaves the request URI and query untouched', function () {
			$_SERVER['REQUEST_URI'] = '/page?id=123&utm_source=google';
			$_SERVER['QUERY_STRING'] = 'id=123&utm_source=google';
			$_GET = array('id' => '123', 'utm_source' => 'google');
			$_REQUEST = $_GET;

			$this->cleaner->clean_conditional_headers();

			expect($_SERVER['REQUEST_URI'])->toBe('/page?id=123&utm_source=google');
			expect($_SERVER['QUERY_STRING'])->toBe('id=123&utm_source=google');
			expect($_GET)->toHaveKey('utm_source');
			expect($_REQUEST)->toHaveKey('utm_source');
		});
	});

	describe('normalize_superglobals', function () {
		it('cleans QUERY_STRING', function () {
			$_SERVER['QUERY_STRING'] = 'id=123&utm_source=google&name=test';

			$this->cleaner->normalize_superglobals();

			expect($_SERVER['QUERY_STRING'])->toBe('id=123&name=test');
		});

		it('cleans REQUEST_URI with query string', function () {
			$_SERVER['REQUEST_URI'] = '/page?id=123&utm_source=google&name=test';

			$this->cleaner->normalize_superglobals();

			expect($_SERVER['REQUEST_URI'])->toBe('/page?id=123&name=test');
		});

		it('removes query string from REQUEST_URI if all params ignored', function () {
			$_SERVER['REQUEST_URI'] = '/page?utm_source=google&fbclid=123';

			$this->cleaner->normalize_superglobals();

			expect($_SERVER['REQUEST_URI'])->toBe('/page');
		});

		it('handles REQUEST_URI without query string', function () {
			$_SERVER['REQUEST_URI'] = '/simple/page';

			$this->cleaner->normalize_superglobals();

			expect($_SERVER['REQUEST_URI'])->toBe('/simple/page');
		});

		it('preserves the order of the surviving parameters', function () {
			$_SERVER['REQUEST_URI'] = '/shop/?paged=2&utm_source=x&orderby=price';
			$_SERVER['QUERY_STRING'] = 'paged=2&utm_source=x&orderby=price';

			$this->cleaner->normalize_superglobals();

			expect($_SERVER['REQUEST_URI'])->toBe('/shop/?paged=2&orderby=price');
			expect($_SERVER['QUERY_STRING'])->toBe('paged=2&orderby=price');
		});

		it('writes the path and values back without entity encoding', function () {
			$_SERVER['REQUEST_URI'] = "/it's/?q=a+b%20c&fbclid=1&x=<y>";
			$_SERVER['QUERY_STRING'] = 'q=a+b%20c&fbclid=1&x=<y>';

			$this->cleaner->normalize_superglobals();

			expect($_SERVER['REQUEST_URI'])->toBe("/it's/?q=a+b%20c&x=<y>");
			expect($_SERVER['QUERY_STRING'])->toBe('q=a+b%20c&x=<y>');
		});

		it('keeps a valueless parameter and a repeated key', function () {
			$_SERVER['REQUEST_URI'] = '/page?flag&utm_source=a&id=1&id=2';

			$this->cleaner->normalize_superglobals();

			expect($_SERVER['REQUEST_URI'])->toBe('/page?flag&id=1&id=2');
		});

		it('removes ignored parameters from $_GET', function () {
			$_GET = array(
				'id' => '123',
				'utm_source' => 'google',
				'name' => 'test',
			);
			$_REQUEST = $_GET;

			$this->cleaner->normalize_superglobals();

			expect($_GET)->toHaveKey('id');
			expect($_GET)->toHaveKey('name');
			expect($_GET)->not->toHaveKey('utm_source');
		});

		it('removes ignored parameters from $_REQUEST', function () {
			$_GET = array(
				'id' => '123',
				'utm_source' => 'google',
			);
			$_REQUEST = $_GET;

			$this->cleaner->normalize_superglobals();

			expect($_REQUEST)->toHaveKey('id');
			expect($_REQUEST)->not->toHaveKey('utm_source');
		});

		it('handles empty QUERY_STRING', function () {
			$_SERVER['QUERY_STRING'] = '';

			$this->cleaner->normalize_superglobals();

			expect($_SERVER['QUERY_STRING'])->toBe('');
		});

		it('handles missing QUERY_STRING', function () {
			unset($_SERVER['QUERY_STRING']);

			$this->cleaner->normalize_superglobals();

			expect($_SERVER)->not->toHaveKey('QUERY_STRING');
		});

		it('handles empty $_GET', function () {
			$_GET = array();
			$_REQUEST = array();

			$this->cleaner->normalize_superglobals();

			expect($_GET)->toBe(array());
			expect($_REQUEST)->toBe(array());
		});

		it('does not touch the conditional headers', function () {
			$_SERVER['HTTP_IF_NONE_MATCH'] = 'etag';

			$this->cleaner->normalize_superglobals();

			expect($_SERVER['HTTP_IF_NONE_MATCH'])->toBe('etag');
		});

		it('normalizes every superglobal in one call', function () {
			$_SERVER['QUERY_STRING'] = 'id=1&utm_source=test';
			$_SERVER['REQUEST_URI'] = '/page?id=1&utm_source=test';
			$_GET = array('id' => '1', 'utm_source' => 'test');
			$_REQUEST = $_GET;

			$this->cleaner->normalize_superglobals();

			expect($_SERVER['QUERY_STRING'])->toBe('id=1');
			expect($_SERVER['REQUEST_URI'])->toBe('/page?id=1');
			expect($_GET)->not->toHaveKey('utm_source');
			expect($_REQUEST)->not->toHaveKey('utm_source');
		});
	});
});
