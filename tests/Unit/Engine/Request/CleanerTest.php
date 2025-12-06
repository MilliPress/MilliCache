<?php

use MilliCache\Engine\Request\Cleaner;
use MilliCache\Engine\Request\Parser;
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

		$this->parser = new Parser($this->config);
		$this->cleaner = new Cleaner($this->config, $this->parser);
	})
	->afterEach(function () {
		// Restore original state.
		$_SERVER = $this->original_server;
		$_GET = $this->original_get;
		$_REQUEST = $this->original_request;
	});

describe('Cleaner', function () {

	describe('clean_request', function () {
		it('removes ETag headers', function () {
			$_SERVER['HTTP_IF_NONE_MATCH'] = 'etag123';
			$_SERVER['HTTP_IF_MODIFIED_SINCE'] = 'date';

			$this->cleaner->clean_request();

			expect($_SERVER)->not->toHaveKey('HTTP_IF_NONE_MATCH');
			expect($_SERVER)->not->toHaveKey('HTTP_IF_MODIFIED_SINCE');
		});

		it('cleans QUERY_STRING', function () {
			$_SERVER['QUERY_STRING'] = 'id=123&utm_source=google&name=test';

			$this->cleaner->clean_request();

			expect($_SERVER['QUERY_STRING'])->toBe('id=123&name=test');
		});

		it('cleans REQUEST_URI with query string', function () {
			$_SERVER['REQUEST_URI'] = '/page?id=123&utm_source=google&name=test';

			$this->cleaner->clean_request();

			expect($_SERVER['REQUEST_URI'])->toBe('/page?id=123&name=test');
		});

		it('removes query string from REQUEST_URI if all params ignored', function () {
			$_SERVER['REQUEST_URI'] = '/page?utm_source=google&fbclid=123';

			$this->cleaner->clean_request();

			expect($_SERVER['REQUEST_URI'])->toBe('/page');
		});

		it('handles REQUEST_URI without query string', function () {
			$_SERVER['REQUEST_URI'] = '/simple/page';

			$this->cleaner->clean_request();

			expect($_SERVER['REQUEST_URI'])->toBe('/simple/page');
		});

		it('removes ignored parameters from $_GET', function () {
			$_GET = array(
				'id' => '123',
				'utm_source' => 'google',
				'name' => 'test',
			);
			$_REQUEST = $_GET;

			$this->cleaner->clean_request();

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

			$this->cleaner->clean_request();

			expect($_REQUEST)->toHaveKey('id');
			expect($_REQUEST)->not->toHaveKey('utm_source');
		});

		it('handles empty QUERY_STRING', function () {
			$_SERVER['QUERY_STRING'] = '';

			$this->cleaner->clean_request();

			expect($_SERVER['QUERY_STRING'])->toBe('');
		});

		it('handles missing QUERY_STRING', function () {
			unset($_SERVER['QUERY_STRING']);

			$this->cleaner->clean_request();

			expect($_SERVER)->not->toHaveKey('QUERY_STRING');
		});

		it('handles empty $_GET', function () {
			$_GET = array();
			$_REQUEST = array();

			$this->cleaner->clean_request();

			expect($_GET)->toBe(array());
			expect($_REQUEST)->toBe(array());
		});

		it('performs complete cleanup in one call', function () {
			$_SERVER['HTTP_IF_NONE_MATCH'] = 'etag';
			$_SERVER['QUERY_STRING'] = 'id=1&utm_source=test';
			$_SERVER['REQUEST_URI'] = '/page?id=1&utm_source=test';
			$_GET = array('id' => '1', 'utm_source' => 'test');
			$_REQUEST = $_GET;

			$this->cleaner->clean_request();

			expect($_SERVER)->not->toHaveKey('HTTP_IF_NONE_MATCH');
			expect($_SERVER['QUERY_STRING'])->toBe('id=1');
			expect($_SERVER['REQUEST_URI'])->toBe('/page?id=1');
			expect($_GET)->not->toHaveKey('utm_source');
			expect($_REQUEST)->not->toHaveKey('utm_source');
		});
	});
});
