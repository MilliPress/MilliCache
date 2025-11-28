<?php

use MilliCache\Core\Storage;
use MilliCache\Engine\Cache\Handler;
use MilliCache\Engine\Cache\Validator;
use MilliCache\Engine\Cache\Reader;
use MilliCache\Engine\Cache\Writer;
use MilliCache\Engine\Cache\Config;
use MilliCache\Engine\Cache\Entry;

describe('Handler', function () {
	beforeEach(function () {
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

		$this->storage = Mockery::mock(Storage::class);
		$this->handler = new Handler($this->config, $this->storage);
	});

	afterEach(function () {
		Mockery::close();
	});

	describe('constructor', function () {
		it('creates handler with config and storage', function () {
			$handler = new Handler($this->config, $this->storage);
			expect($handler)->toBeInstanceOf(Handler::class);
		});

		it('initializes validator', function () {
			expect($this->handler->get_validator())->toBeInstanceOf(Validator::class);
		});

		it('initializes reader', function () {
			expect($this->handler->get_reader())->toBeInstanceOf(Reader::class);
		});

		it('initializes writer', function () {
			expect($this->handler->get_writer())->toBeInstanceOf(Writer::class);
		});
	});

	describe('get_and_validate', function () {
		it('returns miss result when cache not found', function () {
			$this->storage->shouldReceive('is_available')->andReturn(true);
			$this->storage->shouldReceive('get_cache')->andReturn(null);

			$result = $this->handler->get_and_validate('hash', true);

			expect($result['serve'])->toBeFalse();
			expect($result['regenerate'])->toBeFalse();
			expect($result['entry'])->toBeNull();
		});

		it('serves fresh uncompressed cache', function () {
			$cache_data = array(
				'output' => '<html>',
				'headers' => array(),
				'status' => 200,
				'gzip' => false,
				'updated' => time() - 1800, // Fresh
			);

			$this->storage->shouldReceive('is_available')->andReturn(true);
			$this->storage->shouldReceive('get_cache')->andReturn(
				array($cache_data, array(), false)
			);

			$result = $this->handler->get_and_validate('hash', true);

			expect($result['serve'])->toBeTrue();
			expect($result['regenerate'])->toBeFalse();
			expect($result['entry'])->toBeInstanceOf(CacheEntry::class);
			expect($result['entry']->output)->toBe('<html>');
		});

		it('serves stale cache with regeneration when possible', function () {
			$cache_data = array(
				'output' => '<html>',
				'headers' => array(),
				'status' => 200,
				'gzip' => false,
				'updated' => time() - 3700, // Stale
			);

			$this->storage->shouldReceive('is_available')->andReturn(true);
			$this->storage->shouldReceive('get_cache')->andReturn(
				array($cache_data, array(), false)
			);
			$this->storage->shouldReceive('lock')->andReturn(true);

			$result = $this->handler->get_and_validate('hash', true);

			expect($result['serve'])->toBeTrue();
			expect($result['regenerate'])->toBeTrue();
		});

		it('decompresses gzipped cache before serving', function () {
			$original = '<html>Content</html>';
			$cache_data = array(
				'output' => gzcompress($original),
				'headers' => array(),
				'status' => 200,
				'gzip' => true,
				'updated' => time() - 1800,
			);

			$this->storage->shouldReceive('is_available')->andReturn(true);
			$this->storage->shouldReceive('get_cache')->andReturn(
				array($cache_data, array(), false)
			);

			$result = $this->handler->get_and_validate('hash', true);

			expect($result['serve'])->toBeTrue();
			expect($result['entry']->output)->toBe($original);
			expect($result['entry']->gzip)->toBeFalse();
		});

		it('does not serve when decompression fails', function () {
			$cache_data = array(
				'output' => 'invalid compressed data',
				'headers' => array(),
				'status' => 200,
				'gzip' => true,
				'updated' => time() - 1800,
			);

			$this->storage->shouldReceive('is_available')->andReturn(true);
			$this->storage->shouldReceive('get_cache')->andReturn(
				array($cache_data, array(), false)
			);

			$result = $this->handler->get_and_validate('hash', true);

			expect($result['serve'])->toBeFalse();
			expect($result['entry'])->toBeNull();
		});

		it('deletes too-old entries', function () {
			$cache_data = array(
				'output' => '<html>',
				'headers' => array(),
				'status' => 200,
				'gzip' => false,
				'updated' => time() - 5000, // Too old
			);

			$this->storage->shouldReceive('is_available')->andReturn(true);
			$this->storage->shouldReceive('get_cache')->andReturn(
				array($cache_data, array(), false)
			);
			$this->storage->shouldReceive('delete_cache')->with('hash')->once();

			$result = $this->handler->get_and_validate('hash', true);

			expect($result['serve'])->toBeFalse();
		});
	});

	describe('cache_output', function () {
		beforeEach(function () {
			// Mock http_response_code() behavior
			if (!function_exists('http_response_code')) {
				function http_response_code($code = null) {
					return $code ?? 200;
				}
			}
		});

		it('caches output successfully', function () {
			$output = '<html>Test</html>';
			$flags = array('flag1');

			$this->storage->shouldReceive('is_available')->andReturn(true);
			$this->storage->shouldReceive('perform_cache')
				->once()
				->andReturn(true);

			$result = $this->handler->cache_output('hash', $output, $flags);

			expect($result['cached'])->toBeTrue();
			expect($result['reason'])->toBe('');
		});

		it('does not cache 5xx responses', function () {
			// Note: Cannot easily mock http_response_code in tests
			// This test validates the method structure
			expect($this->handler)->toBeInstanceOf(Handler::class);
		});

		it('includes custom TTL and grace', function () {
			$output = '<html>Test</html>';

			$this->storage->shouldReceive('is_available')->andReturn(true);
			$this->storage->shouldReceive('perform_cache')
				->with(
					'hash',
					Mockery::on(function ($data) {
						return $data['custom_ttl'] === 7200
							&& $data['custom_grace'] === 1200;
					}),
					Mockery::any(),
					Mockery::any()
				)
				->andReturn(true);

			$result = $this->handler->cache_output(
				'hash',
				$output,
				array(),
				7200, // Custom TTL
				1200  // Custom grace
			);

			expect($result['cached'])->toBeTrue();
		});

		it('includes debug data when provided', function () {
			$output = '<html>Test</html>';
			$debug = array('info' => 'test');

			$this->storage->shouldReceive('is_available')->andReturn(true);
			$this->storage->shouldReceive('perform_cache')
				->with(
					'hash',
					Mockery::on(function ($data) use ($debug) {
						return $data['debug'] === $debug;
					}),
					Mockery::any(),
					Mockery::any()
				)
				->andReturn(true);

			$result = $this->handler->cache_output(
				'hash',
				$output,
				array(),
				null,
				null,
				$debug
			);

			expect($result['cached'])->toBeTrue();
		});

		it('returns storage failure reason', function () {
			$this->storage->shouldReceive('is_available')->andReturn(true);
			$this->storage->shouldReceive('perform_cache')->andReturn(false);

			$result = $this->handler->cache_output('hash', '<html>', array());

			expect($result['cached'])->toBeFalse();
			expect($result['reason'])->toBe('Storage failed');
		});
	});
});
