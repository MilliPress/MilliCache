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

		it('exposes auth header as an "auth" bucket when present', function () {
			$_COOKIE = array();
			$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer token123';
			$this->hasher->generate();

			$variant = $this->hasher->get_variant();
			expect($variant)->not->toBeNull();
			expect($variant)->toHaveKey('buckets');
			expect($variant['buckets'])->toHaveKey('auth');
			expect($variant['buckets']['auth'])->toBe(md5('Bearer token123'));
		});
	});

	describe('Accept negotiation bucket', function () {
		beforeEach(function () {
			$this->bucket_config   = new Config(
				3600, 600, true, false,
				array(), array(), array(), array(), array(),
				array( 'accept' => array( 'text/markdown' => 'md' ) )
			);
			$this->bucket_parser   = new Parser($this->bucket_config);
			$this->bucket_resolver = new \MilliCache\Engine\Request\Bucket\Resolver($this->bucket_config);
			$this->bucket_hasher   = new Hasher($this->bucket_config, $this->bucket_parser, $this->bucket_resolver);
		});

		it('returns null bucket when no Accept header is sent', function () {
			unset($_SERVER['HTTP_ACCEPT']);
			$this->bucket_hasher->generate();

			expect($this->bucket_resolver->get('accept'))->toBeNull();
		});

		it('returns null bucket for wildcard Accept', function () {
			$_SERVER['HTTP_ACCEPT'] = '*/*';
			$this->bucket_hasher->generate();

			expect($this->bucket_resolver->get('accept'))->toBeNull();
		});

		it('returns md bucket when text/markdown is the top preference', function () {
			$_SERVER['HTTP_ACCEPT'] = 'text/markdown';
			$this->bucket_hasher->generate();

			expect($this->bucket_resolver->get('accept'))->toBe('md');
		});

		it('routes a typical browser Accept to the default bucket', function () {
			$_SERVER['HTTP_ACCEPT'] = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8';
			$this->bucket_hasher->generate();

			expect($this->bucket_resolver->get('accept'))->toBeNull();
		});

		it('honors q-value preference (md preferred wins)', function () {
			$_SERVER['HTTP_ACCEPT'] = 'text/html;q=0.5, text/markdown;q=1.0';
			$this->bucket_hasher->generate();

			expect($this->bucket_resolver->get('accept'))->toBe('md');
		});

		it('produces different hashes for default and md buckets', function () {
			$_SERVER['HTTP_ACCEPT'] = 'text/html';
			$this->bucket_hasher->generate();
			$default_hash = $this->bucket_hasher->get_hash();

			$_SERVER['HTTP_ACCEPT'] = 'text/markdown';
			$md_resolver = new \MilliCache\Engine\Request\Bucket\Resolver($this->bucket_config);
			$md_hasher   = new Hasher($this->bucket_config, $this->bucket_parser, $md_resolver);
			$md_hasher->generate();

			expect($md_hasher->get_hash())->not->toBe($default_hash);
		});

		it('surfaces the bucket in the variant for debug headers', function () {
			$_COOKIE = array();
			$_SERVER['HTTP_ACCEPT'] = 'text/markdown';
			$this->bucket_hasher->generate();

			$variant = $this->bucket_hasher->get_variant();

			expect($variant)->not->toBeNull();
			expect($variant['buckets']['accept'])->toBe('md');
		});

		it('does not bucket when no accept map is configured', function () {
			$bare_config   = create_test_config();
			$bare_resolver = new \MilliCache\Engine\Request\Bucket\Resolver($bare_config);
			$bare_hasher   = new Hasher($bare_config, new Parser($bare_config), $bare_resolver);
			$_SERVER['HTTP_ACCEPT'] = 'text/markdown';
			$bare_hasher->generate();

			expect($bare_resolver->get('accept'))->toBeNull();
		});
	});

	describe('Resolver::add programmatic extension', function () {
		it('merges programmatically added buckets into the resolved set', function () {
			$resolver = new \MilliCache\Engine\Request\Bucket\Resolver($this->config);
			$resolver->add('language', 'en');
			$resolver->add('tenant', 'acme');

			$hasher = new Hasher($this->config, $this->parser, $resolver);
			$hasher->generate();

			expect($resolver->get('language'))->toBe('en');
			expect($resolver->get('tenant'))->toBe('acme');
		});

		it('silently drops empty names and tokens', function () {
			$resolver = new \MilliCache\Engine\Request\Bucket\Resolver($this->config);
			$resolver->add('good', 'ok');
			$resolver->add('', 'no-name');
			$resolver->add('no-token', '');

			$hasher = new Hasher($this->config, $this->parser, $resolver);
			$hasher->generate();

			expect($resolver->all())->toBe(array( 'good' => 'ok' ));
		});

		it('a programmatically added bucket changes the hash', function () {
			$base_hasher = new Hasher($this->config, $this->parser);
			$base_hasher->generate();
			$base_hash = $base_hasher->get_hash();

			$resolver = new \MilliCache\Engine\Request\Bucket\Resolver($this->config);
			$resolver->add('tenant', 'acme');

			$tenant_hasher = new Hasher($this->config, $this->parser, $resolver);
			$tenant_hasher->generate();

			expect($tenant_hasher->get_hash())->not->toBe($base_hash);
		});

		it('overrides built-in resolver output when names collide', function () {
			$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer real-token';

			$resolver = new \MilliCache\Engine\Request\Bucket\Resolver($this->config);
			$resolver->add('auth', 'forced');

			$hasher = new Hasher($this->config, $this->parser, $resolver);
			$hasher->generate();

			expect($resolver->get('auth'))->toBe('forced');
		});
	});
});
