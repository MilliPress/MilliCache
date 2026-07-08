<?php

use MilliCache\Core\Storage;
use MilliCache\Engine\Cache\Manager;
use MilliCache\Engine\Cache\Validator;
use MilliCache\Engine\Cache\Reader;
use MilliCache\Engine\Cache\Writer;
use MilliCache\Engine\Cache\Config;
use MilliCache\Engine\Cache\Entry;

uses()->beforeEach(function () {
	$this->config = create_test_config();

	$this->storage = Mockery::mock(Storage::class);
	$this->handler = new Manager($this->config, $this->storage);
});

describe('Handler', function () {

	describe('constructor', function () {
		it('creates handler with config and storage', function () {
			$handler = new Manager($this->config, $this->storage);
			expect($handler)->toBeInstanceOf(Manager::class);
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

			$result = $this->handler->get_and_validate('hash');

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

			$result = $this->handler->get_and_validate('hash');

			expect($result['serve'])->toBeTrue();
			expect($result['regenerate'])->toBeFalse();
			expect($result['entry'])->toBeInstanceOf(Entry::class);
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

			$result = $this->handler->get_and_validate('hash');

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

			$result = $this->handler->get_and_validate('hash');

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

			$result = suppressing_errors(fn() => $this->handler->get_and_validate('hash'));

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

			$result = $this->handler->get_and_validate('hash');

			expect($result['serve'])->toBeFalse();
		});
	});

	describe('cache_output', function () {
		it('caches output successfully', function () {
			$output = '<html>Test</html>';
			$flags = array('flag1');

			$this->storage->shouldReceive('is_available')->andReturn(true);
			$this->storage->shouldReceive('perform_cache')
				->once()
				->andReturn(true);

			$result = $this->handler->cache_output('hash', $output, $flags, 200, array());

			expect($result['cached'])->toBeTrue();
			expect($result['reason'])->toBe('');
		});

		it('does not cache 5xx responses', function () {
			$result = $this->handler->cache_output('hash', '<html>', array(), 500, array());

			expect($result['cached'])->toBeFalse();
			expect($result['reason'])->toBe('Server error response');
		});

		it('does not cache responses above the size limit', function () {
			$output = str_repeat('a', Writer::MAX_ENTRY_SIZE + 1);

			$result = $this->handler->cache_output('hash', $output, array(), 200, array());

			expect($result['cached'])->toBeFalse();
			expect($result['reason'])->toContain('exceeds');
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
				200,
				array(),
				7200, // Custom TTL
				1200  // Custom grace
			);

			expect($result['cached'])->toBeTrue();
		});

		it('includes url in stored entry', function () {
			$output = '<html>Test</html>';

			$this->storage->shouldReceive('is_available')->andReturn(true);
			$this->storage->shouldReceive('perform_cache')
				->with(
					'hash',
					Mockery::on(function ($data) {
						return $data['url'] === 'example.com/about/';
					}),
					Mockery::any(),
					Mockery::any()
				)
				->andReturn(true);

			$result = $this->handler->cache_output(
				'hash',
				$output,
				array(),
				200,
				array(),
				null,
				null,
				'example.com/about/'
			);

			expect($result['cached'])->toBeTrue();
		});

		it('includes variant data when provided', function () {
			$output = '<html>Test</html>';
			$variant = array('cookies' => array('session_id'));

			$this->storage->shouldReceive('is_available')->andReturn(true);
			$this->storage->shouldReceive('perform_cache')
				->with(
					'hash',
					Mockery::on(function ($data) use ($variant) {
						return $data['variant'] === $variant;
					}),
					Mockery::any(),
					Mockery::any()
				)
				->andReturn(true);

			$result = $this->handler->cache_output(
				'hash',
				$output,
				array(),
				200,
				array(),
				null,
				null,
				'',
				$variant
			);

			expect($result['cached'])->toBeTrue();
		});

		it('returns storage failure reason', function () {
			$this->storage->shouldReceive('is_available')->andReturn(true);
			$this->storage->shouldReceive('perform_cache')->andReturn(false);

			$result = $this->handler->cache_output('hash', '<html>', array(), 200, array());

			expect($result['cached'])->toBeFalse();
			expect($result['reason'])->toBe('Storage failed');
		});

		it('stores the headers returned by the millicache_entry_headers filter', function () {
			global $test_filters;
			$test_filters['millicache_entry_headers'] = array(
				'Content-Type: text/html',
				'Cache-Tag: 2:home,2:post:9',
				'Cache-Control: s-maxage=30',
			);

			$this->storage->shouldReceive('is_available')->andReturn(true);
			$this->storage->shouldReceive('perform_cache')
				->with(
					'hash',
					Mockery::on(function ($data) {
						return $data['headers'] === array(
							'Content-Type: text/html',
							'Cache-Tag: 2:home,2:post:9',
							'Cache-Control: s-maxage=30',
						);
					}),
					// The flags handed to the filter are the ones being stored.
					array('2:home', '2:post:9'),
					Mockery::any()
				)
				->andReturn(true);

			$result = $this->handler->cache_output(
				'hash',
				'<html>Test</html>',
				array('2:home', '2:post:9'),
				200,
				array('Content-Type: text/html')
			);

			unset($test_filters['millicache_entry_headers']);

			expect($result['cached'])->toBeTrue();
		});
	});
});
