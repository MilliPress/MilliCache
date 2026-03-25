<?php

use MilliCache\Engine\Request\Hasher;
use MilliCache\Engine\Request\Parser;
use MilliCache\Engine\Cache\Config;

uses()
	->beforeEach(function () {
		// Save original server state.
		$this->original_server = $_SERVER;
		$this->original_cookie = $_COOKIE;

		// Set up test environment.
		$_SERVER['REQUEST_URI'] = '/test/page?id=123';
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['HTTPS'] = 'on';
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_COOKIE = array('session' => 'abc123');

		$this->config = create_test_config();

		$this->parser = new Parser($this->config);
		$this->hasher = new Hasher($this->config, $this->parser);
	})
	->afterEach(function () {
		// Restore original state.
		$_SERVER = $this->original_server;
		$_COOKIE = $this->original_cookie;
	});

describe('Hasher', function () {

	describe('generate', function () {
		it('generates an MD5 hash', function () {
			$hash = $this->hasher->generate();
			expect($hash)->toMatch('/^[a-f0-9]{32}$/');
		});

		it('generates consistent hash for same request', function () {
			$hash1 = $this->hasher->generate();

			// Create new hasher with same config.
			$hasher2 = new Hasher($this->config, $this->parser);
			$hash2 = $hasher2->generate();

			expect($hash1)->toBe($hash2);
		});

		it('generates different hash when URI changes', function () {
			$hash1 = $this->hasher->generate();

			$_SERVER['REQUEST_URI'] = '/different/page';
			$hasher2 = new Hasher($this->config, $this->parser);
			$hash2 = $hasher2->generate();

			expect($hash1)->not->toBe($hash2);
		});

		it('generates different hash when host changes', function () {
			$hash1 = $this->hasher->generate();

			$_SERVER['HTTP_HOST'] = 'different.com';
			$hasher2 = new Hasher($this->config, $this->parser);
			$hash2 = $hasher2->generate();

			expect($hash1)->not->toBe($hash2);
		});

		it('generates different hash when method changes', function () {
			$hash1 = $this->hasher->generate();

			$_SERVER['REQUEST_METHOD'] = 'POST';
			$hasher2 = new Hasher($this->config, $this->parser);
			$hash2 = $hasher2->generate();

			expect($hash1)->not->toBe($hash2);
		});

		it('generates different hash when cookies change', function () {
			$hash1 = $this->hasher->generate();

			$_COOKIE['new_cookie'] = 'value';
			$hasher2 = new Hasher($this->config, $this->parser);
			$hash2 = $hasher2->generate();

			expect($hash1)->not->toBe($hash2);
		});

		it('includes authorization header in hash', function () {
			$hash1 = $this->hasher->generate();

			$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer token123';
			$hasher2 = new Hasher($this->config, $this->parser);
			$hash2 = $hasher2->generate();

			expect($hash1)->not->toBe($hash2);
		});

		it('includes unique variables in hash', function () {
			$config1 = new Config(
				3600, 600, true, false,
				array(), array(), array(), array(),
				array('user_id' => '123')
			);
			$hasher1 = new Hasher($config1, new Parser($config1));
			$hash1 = $hasher1->generate();

			$config2 = new Config(
				3600, 600, true, false,
				array(), array(), array(), array(),
				array('user_id' => '456')
			);
			$hasher2 = new Hasher($config2, new Parser($config2));
			$hash2 = $hasher2->generate();

			expect($hash1)->not->toBe($hash2);
		});

		it('stores hash for later retrieval', function () {
			$hash = $this->hasher->generate();
			expect($this->hasher->get_hash())->toBe($hash);
		});
	});

	describe('get_hash', function () {
		it('returns null before generation', function () {
			expect($this->hasher->get_hash())->toBeNull();
		});

		it('returns hash after generation', function () {
			$hash = $this->hasher->generate();
			expect($this->hasher->get_hash())->toBe($hash);
		});
	});

	describe('get_url', function () {
		it('returns null before generation', function () {
			expect($this->hasher->get_url())->toBeNull();
		});

		it('returns full URL with scheme after generation', function () {
			$this->hasher->generate();
			$url = $this->hasher->get_url();

			expect($url)->toBe('https://example.com/test/page?id=123');
		});

		it('reflects different host', function () {
			$_SERVER['HTTP_HOST'] = 'shop.example.com';
			$_SERVER['REQUEST_URI'] = '/products';
			$hasher = new Hasher($this->config, $this->parser);
			$hasher->generate();

			expect($hasher->get_url())->toBe('https://shop.example.com/products');
		});

		it('uses http scheme when HTTPS is off', function () {
			$_SERVER['HTTPS'] = '';
			$hasher = new Hasher($this->config, $this->parser);
			$hasher->generate();

			expect($hasher->get_url())->toBe('http://example.com/test/page?id=123');
		});
	});

	describe('get_variant', function () {
		it('returns null for vanilla anonymous request', function () {
			$_COOKIE = array();
			$this->hasher->generate();
			expect($this->hasher->get_variant())->toBeNull();
		});

		it('includes https false when not HTTPS', function () {
			$_COOKIE = array();
			$_SERVER['HTTPS'] = '';
			$this->hasher->generate();

			$variant = $this->hasher->get_variant();
			expect($variant)->not->toBeNull();
			expect($variant['https'])->toBeFalse();
		});

		it('does not include https when HTTPS is on', function () {
			$_COOKIE = array();
			$_SERVER['HTTPS'] = 'on';
			$this->hasher->generate();

			expect($this->hasher->get_variant())->toBeNull();
		});

		it('includes method when not GET', function () {
			$_COOKIE = array();
			$_SERVER['REQUEST_METHOD'] = 'POST';
			$this->hasher->generate();

			$variant = $this->hasher->get_variant();
			expect($variant)->not->toBeNull();
			expect($variant['method'])->toBe('POST');
		});

		it('does not include method for GET requests', function () {
			$_COOKIE = array();
			$_SERVER['REQUEST_METHOD'] = 'GET';
			$this->hasher->generate();

			expect($this->hasher->get_variant())->toBeNull();
		});

		it('returns cookie names when cookies are present', function () {
			$_COOKIE = array( 'session_id' => 'abc', 'pref' => 'dark' );
			$this->hasher->generate();

			$variant = $this->hasher->get_variant();
			expect($variant)->not->toBeNull();
			expect($variant)->toHaveKey('cookies');
			expect($variant['cookies'])->toBe(array( 'session_id', 'pref' ));
		});

		it('returns unique variables when configured', function () {
			$_COOKIE = array();
			$config = new Config(
				3600, 600, true, false,
				array(), array(), array(), array(), array( 'device', 'mobile' )
			);
			$hasher = new Hasher($config, $this->parser);
			$hasher->generate();

			$variant = $hasher->get_variant();
			expect($variant)->not->toBeNull();
			expect($variant)->toHaveKey('unique');
			expect($variant['unique'])->toBe(array( 'device', 'mobile' ));
		});

		it('includes auth header in unique when present', function () {
			$_COOKIE = array();
			$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer token123';
			$this->hasher->generate();

			$variant = $this->hasher->get_variant();
			expect($variant)->not->toBeNull();
			expect($variant['unique'])->toHaveKey('mc-auth-header');
		});
	});
});
