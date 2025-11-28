<?php

use MilliCache\Core\Storage;
use MilliCache\Engine\Cache\Reader;
use MilliCache\Engine\Cache\Validator;
use MilliCache\Engine\Cache\Config;
use MilliCache\Engine\Cache\Entry;
use MilliCache\Engine\Cache\Result;

describe('Reader', function () {
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
		$this->validator = new Validator($this->config);
		$this->reader = new Reader($this->config, $this->storage, $this->validator);
	});

	afterEach(function () {
		Mockery::close();
	});

	describe('get', function () {
		it('returns miss when storage is unavailable', function () {
			$this->storage->shouldReceive('is_available')->andReturn(false);

			$result = $this->reader->get('test_hash');

			expect($result->is_miss())->toBeTrue();
		});

		it('returns miss when cache not found', function () {
			$this->storage->shouldReceive('is_available')->andReturn(true);
			$this->storage->shouldReceive('get_cache')->with('test_hash')->andReturn(null);

			$result = $this->reader->get('test_hash');

			expect($result->is_miss())->toBeTrue();
		});

		it('returns miss when cache data is empty', function () {
			$this->storage->shouldReceive('is_available')->andReturn(true);
			$this->storage->shouldReceive('get_cache')->with('test_hash')->andReturn(array(array(), array(), false));

			$result = $this->reader->get('test_hash');

			expect($result->is_miss())->toBeTrue();
		});

		it('returns hit with valid cache data', function () {
			$cache_data = array(
				'output' => '<html>',
				'headers' => array('Content-Type: text/html'),
				'status' => 200,
				'gzip' => false,
				'updated' => time(),
			);

			$this->storage->shouldReceive('is_available')->andReturn(true);
			$this->storage->shouldReceive('get_cache')->with('test_hash')->andReturn(
				array($cache_data, array('flag1'), false)
			);

			$result = $this->reader->get('test_hash');

			expect($result->is_hit())->toBeTrue();
			expect($result->entry)->toBeInstanceOf(CacheEntry::class);
			expect($result->entry->output)->toBe('<html>');
		});

		it('includes flags and lock status in result', function () {
			$cache_data = array(
				'output' => '<html>',
				'headers' => array(),
				'status' => 200,
				'gzip' => false,
				'updated' => time(),
			);

			$this->storage->shouldReceive('is_available')->andReturn(true);
			$this->storage->shouldReceive('get_cache')->with('test_hash')->andReturn(
				array($cache_data, array('flag1', 'flag2'), true)
			);

			$result = $this->reader->get('test_hash');

			expect($result->flags)->toBe(array('flag1', 'flag2'));
			expect($result->locked)->toBeTrue();
		});
	});

	describe('should_serve', function () {
		it('returns false for cache miss', function () {
			$result = CacheResult::miss();
			$decision = $this->reader->should_serve($result, 'hash', true);

			expect($decision['serve'])->toBeFalse();
			expect($decision['regenerate'])->toBeFalse();
		});

		it('deletes too-old entries and returns false', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false,
				time() - 5000 // Very old
			);
			$result = CacheResult::hit($entry, array(), false);

			$this->storage->shouldReceive('delete_cache')->with('hash')->once();

			$decision = $this->reader->should_serve($result, 'hash', true);

			expect($decision['serve'])->toBeFalse();
		});

		it('serves fresh cache immediately', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false,
				time() - 1800 // 30 minutes ago, fresh
			);
			$result = CacheResult::hit($entry, array(), false);

			$decision = $this->reader->should_serve($result, 'hash', true);

			expect($decision['serve'])->toBeTrue();
			expect($decision['regenerate'])->toBeFalse();
		});

		it('does not serve stale locked cache', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false,
				time() - 3700 // Stale
			);
			$result = CacheResult::hit($entry, array(), true); // Locked

			$decision = $this->reader->should_serve($result, 'hash', true);

			expect($decision['serve'])->toBeFalse();
			expect($decision['regenerate'])->toBeFalse();
		});

		it('locks and serves stale cache with regeneration', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false,
				time() - 3700 // Stale
			);
			$result = CacheResult::hit($entry, array(), false); // Not locked

			$this->storage->shouldReceive('lock')->with('hash')->andReturn(true);

			$decision = $this->reader->should_serve($result, 'hash', true); // Can regenerate

			expect($decision['serve'])->toBeTrue();
			expect($decision['regenerate'])->toBeTrue();
		});

		it('does not serve stale cache when cannot regenerate', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false,
				time() - 3700 // Stale
			);
			$result = CacheResult::hit($entry, array(), false);

			$this->storage->shouldReceive('lock')->with('hash')->andReturn(true);

			$decision = $this->reader->should_serve($result, 'hash', false); // Cannot regenerate

			expect($decision['serve'])->toBeFalse();
		});

		it('does not serve when lock fails', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false,
				time() - 3700 // Stale
			);
			$result = CacheResult::hit($entry, array(), false);

			$this->storage->shouldReceive('lock')->with('hash')->andReturn(false);

			$decision = $this->reader->should_serve($result, 'hash', true);

			expect($decision['serve'])->toBeFalse();
		});
	});

	describe('decompress', function () {
		it('returns entry as-is when not compressed', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false, // Not gzipped
				time()
			);

			$result = $this->reader->decompress($entry);

			expect($result)->toBe($entry);
		});

		it('returns null when config does not support gzip', function () {
			$config = new Config(
				3600, 600, false, false, // Gzip disabled
				array(), array(), array(), array(), array()
			);
			$reader = new Reader($config, $this->storage, $this->validator);

			$entry = new Entry(
				gzcompress('<html>'),
				array(),
				200,
				true, // Gzipped
				time()
			);

			$result = $reader->decompress($entry);

			expect($result)->toBeNull();
		});

		it('decompresses gzipped entry successfully', function () {
			$original = '<html>Content</html>';
			$compressed = gzcompress($original);

			$entry = new Entry(
				$compressed,
				array('Header: value'),
				200,
				true,
				time()
			);

			$result = $this->reader->decompress($entry);

			expect($result)->not->toBeNull();
			expect($result->output)->toBe($original);
			expect($result->gzip)->toBeFalse();
			expect($result->headers)->toBe(array('Header: value'));
		});

		it('returns null on decompression failure', function () {
			$entry = new Entry(
				'invalid compressed data',
				array(),
				200,
				true,
				time()
			);

			$result = $this->reader->decompress($entry);

			expect($result)->toBeNull();
		});
	});

	describe('output', function () {
		it('outputs cache content and exits', function () {
			$entry = new Entry(
				'<html>Test</html>',
				array('Content-Type: text/html', 'X-Custom: value'),
				200,
				false,
				time()
			);

			// Note: Cannot fully test exit behavior in unit tests
			// This test validates the method signature and basic behavior
			expect(function () use ($entry) {
				ob_start();
				try {
					$this->reader->output($entry, false);
				} catch (\Throwable $e) {
					// Catch exit call
				}
				$output = ob_get_clean();
				expect($output)->toBe('<html>Test</html>');
			})->not->toThrow();
		});
	});
});
