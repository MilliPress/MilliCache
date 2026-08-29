<?php

use MilliCache\Engine\Request\Processor;
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

		$this->config = create_test_config( ignore_request_keys: array( 'utm_source' ) );

		$this->handler = new Processor($this->config);
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
			$handler = new Processor($this->config);
			expect($handler)->toBeInstanceOf(Processor::class);
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
		it('generates a hash without touching the request superglobals', function () {
			$_SERVER['HTTP_IF_NONE_MATCH'] = 'etag';

			$hash = $this->handler->process();

			// Hash should be MD5 format.
			expect($hash)->toMatch('/^[a-f0-9]{32}$/');

			// Conditional headers go, the request itself stays as sent.
			expect($_SERVER)->not->toHaveKey('HTTP_IF_NONE_MATCH');
			expect($_GET)->toHaveKey('utm_source');
			expect($_SERVER['REQUEST_URI'])->toBe('/test/page?id=123&utm_source=google');
			expect($_SERVER['QUERY_STRING'])->toBe('id=123&utm_source=google');
		});

		it('hashes the ignored keys away regardless of superglobal normalization', function () {
			$hash_raw = $this->handler->process();

			$this->handler->normalize();
			$hash_normalized = ( new Processor($this->config) )->process();

			$_SERVER['REQUEST_URI'] = '/test/page?id=123';
			$_SERVER['QUERY_STRING'] = 'id=123';
			$_GET = array('id' => '123');
			$hash_clean = ( new Processor($this->config) )->process();

			expect($hash_raw)->toBe($hash_normalized);
			expect($hash_raw)->toBe($hash_clean);
		});

		it('returns consistent hash for same request', function () {
			$hash1 = $this->handler->process();

			// Create new handler with same setup.
			$_SERVER['REQUEST_URI'] = '/test/page?id=123&utm_source=google';
			$_SERVER['QUERY_STRING'] = 'id=123&utm_source=google';
			$_GET = array('id' => '123', 'utm_source' => 'google');

			$handler2 = new Processor($this->config);
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

		it('matches the request hash for URLs with a non-default port', function () {
			$_SERVER['HTTP_HOST'] = 'localhost:8888';
			$_SERVER['HTTPS'] = '';
			$_SERVER['REQUEST_URI'] = '/hello-world/';

			$request_hash = $this->handler->get_url_hash();
			$url_hash = $this->handler->get_url_hash('http://localhost:8888/hello-world/');

			expect($url_hash)->toBe($request_hash);
		});

		it('ignores default ports like the Host header does', function () {
			$hash1 = $this->handler->get_url_hash('https://example.com:443/page');
			$hash2 = $this->handler->get_url_hash('https://example.com/page');
			$hash3 = $this->handler->get_url_hash('http://example.com:80/page');
			$hash4 = $this->handler->get_url_hash('http://example.com/page');

			expect($hash1)->toBe($hash2);
			expect($hash3)->toBe($hash4);
		});
	});

	describe('get_url', function () {
		it('returns null before processing', function () {
			expect($this->handler->get_url())->toBeNull();
		});

		it('returns full URL with scheme after processing', function () {
			$this->handler->process();
			$url = $this->handler->get_url();

			// utm_source is ignored, so the normalized URL must not include it.
			expect($url)->toBe('https://example.com/test/page?id=123');
		});
	});

	describe('get_variant', function () {
		it('returns null for vanilla anonymous request', function () {
			$_COOKIE = array();
			$this->handler->process();
			expect($this->handler->get_variant())->toBeNull();
		});

		it('returns variant data when unique variables are configured', function () {
			$_COOKIE = array();
			$config = new Config(
				3600, 600, true, false,
				array(), array(), array(), array('utm_source'), array( 'device', 'mobile' )
			);
			$handler = new Processor($config);
			$handler->process();

			$variant = $handler->get_variant();
			expect($variant)->not->toBeNull();
			expect($variant)->toHaveKey('unique');
		});
	});

	describe('integration', function () {
		it('properly hashes a complex request and normalizes it on demand', function () {
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
			$handler = new Processor($config);

			$hash = $handler->process();

			// Hashing leaves the request intact.
			expect($_GET)->toHaveKey('utm_source');
			expect($_GET)->toHaveKey('fbclid');

			// The deferred normalization removes exactly the ignored keys.
			$handler->normalize();
			expect($_GET)->toHaveKey('id');
			expect($_GET)->toHaveKey('color');
			expect($_GET)->not->toHaveKey('utm_source');
			expect($_GET)->not->toHaveKey('fbclid');
			expect($_SERVER['REQUEST_URI'])->toBe('/Products/Item?id=5&color=blue');

			// Verify hash is generated.
			expect($hash)->toMatch('/^[a-f0-9]{32}$/');
		});
	});
});
