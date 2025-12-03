<?php

use MilliCache\Engine\Request\Manager;
use MilliCache\Engine\Request\Parser;
use MilliCache\Engine\Request\Cleaner;
use MilliCache\Engine\Request\Hasher;
use MilliCache\Engine\Cache\Config;

uses()
	->beforeEach(function () {
		// Save original server state.
		$this->original_server = $_SERVER;
		$this->original_cookie = $_COOKIE;
		$this->original_get = $_GET;
		$this->original_request = $_REQUEST;

		// Set up test environment.
		$_SERVER['REQUEST_URI'] = '/test/page?id=123&utm_source=google';
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['HTTPS'] = 'on';
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['QUERY_STRING'] = 'id=123&utm_source=google';
		$_COOKIE = array('session' => 'abc123');
		$_GET = array('id' => '123', 'utm_source' => 'google');
		$_REQUEST = $_GET;

		$this->config = new Config(
			3600,
			600,
			true,
			false,
			array(),
			array(),
			array(),
			array('utm_source'),
			array()
		);

		$this->handler = new Manager($this->config);
	})
	->afterEach(function () {
		// Restore original state.
		$_SERVER = $this->original_server;
		$_COOKIE = $this->original_cookie;
		$_GET = $this->original_get;
		$_REQUEST = $this->original_request;
	});

describe('Handler', function () {

	describe('constructor', function () {
		it('creates handler with config', function () {
			$handler = new Manager($this->config);
			expect($handler)->toBeInstanceOf(Manager::class);
		});

		it('initializes parser', function () {
			expect($this->handler->get_parser())->toBeInstanceOf(Parser::class);
		});

		it('initializes cleaner', function () {
			expect($this->handler->get_cleaner())->toBeInstanceOf(Cleaner::class);
		});

		it('initializes hasher', function () {
			expect($this->handler->get_hasher())->toBeInstanceOf(Hasher::class);
		});
	});

	describe('process', function () {
		it('cleans request and generates hash', function () {
			$hash = $this->handler->process();

			// Hash should be MD5 format.
			expect($hash)->toMatch('/^[a-f0-9]{32}$/');

			// Request should be cleaned.
			expect($_GET)->not->toHaveKey('utm_source');
			expect($_SERVER['REQUEST_URI'])->toBe('/test/page?id=123');
		});

		it('returns consistent hash for same request', function () {
			$hash1 = $this->handler->process();

			// Create new handler with same setup.
			$_SERVER['REQUEST_URI'] = '/test/page?id=123&utm_source=google';
			$_SERVER['QUERY_STRING'] = 'id=123&utm_source=google';
			$_GET = array('id' => '123', 'utm_source' => 'google');

			$handler2 = new Manager($this->config);
			$hash2 = $handler2->process();

			expect($hash1)->toBe($hash2);
		});
	});

	describe('get_url_hash', function () {
		it('generates hash for current request', function () {
			$hash = $this->handler->get_url_hash();
			expect($hash)->toMatch('/^[a-f0-9]{32}$/');
		});

		it('generates hash for provided URL', function () {
			$hash = $this->handler->get_url_hash('https://example.com/page?id=123');
			expect($hash)->toMatch('/^[a-f0-9]{32}$/');
		});

		it('generates same hash for equivalent URLs', function () {
			$hash1 = $this->handler->get_url_hash('https://example.com/page?id=123&utm_source=test');
			$hash2 = $this->handler->get_url_hash('https://example.com/page?id=123');

			// Should be same because utm_source is ignored.
			expect($hash1)->toBe($hash2);
		});

		it('normalizes host case', function () {
			$hash1 = $this->handler->get_url_hash('https://Example.COM/page');
			$hash2 = $this->handler->get_url_hash('https://example.com/page');

			expect($hash1)->toBe($hash2);
		});

		it('uses current request when URL is null', function () {
			$hash1 = $this->handler->get_url_hash(null);
			$hash2 = $this->handler->get_url_hash();

			expect($hash1)->toBe($hash2);
		});
	});

	describe('get_debug_data', function () {
		it('returns null when debug is disabled', function () {
			$this->handler->process();
			expect($this->handler->get_debug_data())->toBeNull();
		});

		it('returns debug data when debug is enabled', function () {
			$config = new Config(
				3600, 600, true, true,
				array(), array(), array(), array('utm_source'), array()
			);
			$handler = new Manager($config);
			$handler->process();

			$debug = $handler->get_debug_data();
			expect($debug)->not->toBeNull();
			expect($debug)->toHaveKey('request_hash');
		});
	});

	describe('integration', function () {
		it('properly cleans and hashes complex request', function () {
			$_SERVER['REQUEST_URI'] = '/Products/Item?id=5&utm_source=email&color=blue&fbclid=abc';
			$_SERVER['HTTP_HOST'] = 'Shop.Example.COM';
			$_SERVER['QUERY_STRING'] = 'id=5&utm_source=email&color=blue&fbclid=abc';
			$_GET = array(
				'id' => '5',
				'utm_source' => 'email',
				'color' => 'blue',
				'fbclid' => 'abc',
			);

			$config = new Config(
				3600, 600, true, false,
				array(), array(), array(), array('utm_*', 'fbclid'), array()
			);
			$handler = new Manager($config);

			$hash = $handler->process();

			// Verify cleaning.
			expect($_GET)->toHaveKey('id');
			expect($_GET)->toHaveKey('color');
			expect($_GET)->not->toHaveKey('utm_source');
			expect($_GET)->not->toHaveKey('fbclid');

			// Verify hash is generated.
			expect($hash)->toMatch('/^[a-f0-9]{32}$/');
		});
	});
});
