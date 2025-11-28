<?php

use MilliCache\Engine\Request\Hasher;
use MilliCache\Engine\Request\Parser;
use MilliCache\Engine\Cache\Config;

describe('Hasher', function () {
	beforeEach(function () {
		// Save original server state.
		$this->original_server = $_SERVER;
		$this->original_cookie = $_COOKIE;

		// Set up test environment.
		$_SERVER['REQUEST_URI'] = '/test/page?id=123';
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['HTTPS'] = 'on';
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_COOKIE = array('session' => 'abc123');

		$this->config = new Config(
			3600,
			600,
			true,
			false,
			array(),
			array(),
			array(),
			array(),
			array()
		);

		$this->parser = new Parser($this->config);
		$this->hasher = new Hasher($this->config, $this->parser);
	});

	afterEach(function () {
		// Restore original state.
		$_SERVER = $this->original_server;
		$_COOKIE = $this->original_cookie;
	});

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

	describe('get_debug_data', function () {
		it('returns null when debug is disabled', function () {
			$this->hasher->generate();
			expect($this->hasher->get_debug_data())->toBeNull();
		});

		it('returns debug data when debug is enabled', function () {
			$config = new Config(
				3600, 600, true, true,
				array(), array(), array(), array(), array()
			);
			$hasher = new Hasher($config, $this->parser);
			$hasher->generate();

			$debug = $hasher->get_debug_data();
			expect($debug)->not->toBeNull();
			expect($debug)->toHaveKey('request_hash');
		});

		it('includes all hash components in debug data', function () {
			$config = new Config(
				3600, 600, true, true,
				array(), array(), array(), array(), array()
			);
			$hasher = new Hasher($config, $this->parser);
			$hasher->generate();

			$debug = $hasher->get_debug_data();
			$hash_data = $debug['request_hash'];

			expect($hash_data)->toHaveKey('request');
			expect($hash_data)->toHaveKey('host');
			expect($hash_data)->toHaveKey('https');
			expect($hash_data)->toHaveKey('method');
			expect($hash_data)->toHaveKey('unique');
			expect($hash_data)->toHaveKey('cookies');
		});
	});
});
