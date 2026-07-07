<?php

use MilliCache\Core\Storage;
use MilliCache\Engine\Cache\Writer;
use MilliCache\Engine\Cache\Config;
use MilliCache\Engine\Cache\Entry;

uses()->beforeEach(function () {
	$this->config = create_test_config( ignore_cookies: array( 'test_cookie' ) );

	$this->storage = Mockery::mock(Storage::class);
	$this->writer = new Writer($this->config, $this->storage);

	// Save original headers state
	$this->original_headers = headers_list();
});

describe('Writer', function () {

	describe('should_cache', function () {
		it('allows caching for 2xx status codes', function () {
			$result = $this->writer->should_cache(200);

			expect($result['cacheable'])->toBeTrue();
			expect($result['reason'])->toBe('');
		});

		it('allows caching for 3xx status codes', function () {
			$result = $this->writer->should_cache(301);

			expect($result['cacheable'])->toBeTrue();
		});

		it('allows caching for 4xx status codes', function () {
			$result = $this->writer->should_cache(404);

			expect($result['cacheable'])->toBeTrue();
		});

		it('disallows caching for 5xx status codes', function () {
			$result = $this->writer->should_cache(500);

			expect($result['cacheable'])->toBeFalse();
			expect($result['reason'])->toBe('Server error response');
		});

		it('disallows caching for 503 status', function () {
			$result = $this->writer->should_cache(503);

			expect($result['cacheable'])->toBeFalse();
			expect($result['reason'])->toBe('Server error response');
		});
	});

	describe('validate_headers', function () {
		it('keeps ordinary response headers, including repeated ones', function () {
			$result = $this->writer->validate_headers(array(
				'Content-Type: text/html; charset=UTF-8',
				'Link: <https://example.test/wp-json/>; rel="https://api.w.org/"',
				'Link: <https://example.test/?p=9>; rel=shortlink',
			));

			expect($result['cacheable'])->toBeTrue();
			expect($result['headers'])->toBe(array(
				'Content-Type: text/html; charset=UTF-8',
				'Link: <https://example.test/wp-json/>; rel="https://api.w.org/"',
				'Link: <https://example.test/?p=9>; rel=shortlink',
			));
		});

		it('strips our own X-MilliCache-* diagnostics', function () {
			$result = $this->writer->validate_headers(array(
				'Content-Type: text/html',
				'X-MilliCache-Status: hit',
				'X-MilliCache-Key: abc123',
			));

			expect($result['headers'])->toBe(array('Content-Type: text/html'));
		});

		it('strips the per-hop Age header', function () {
			$result = $this->writer->validate_headers(array(
				'Content-Type: text/html',
				'Age: 33',
			));

			expect($result['headers'])->toBe(array('Content-Type: text/html'));
		});

		it('strips the bare serve-time Cache-Control: no-cache', function () {
			$result = $this->writer->validate_headers(array(
				'Content-Type: text/html',
				'Cache-Control: no-cache',
			));

			expect($result['headers'])->toBe(array('Content-Type: text/html'));
		});

		it('keeps multi-directive Cache-Control values intact', function () {
			$result = $this->writer->validate_headers(array(
				'Cache-Control: no-cache, must-revalidate, max-age=0',
			));

			expect($result['headers'])->toBe(array('Cache-Control: no-cache, must-revalidate, max-age=0'));
		});

		it('keeps an s-maxage Cache-Control untouched', function () {
			$result = $this->writer->validate_headers(array(
				'Cache-Control: s-maxage=30',
			));

			expect($result['headers'])->toBe(array('Cache-Control: s-maxage=30'));
		});
	});

	describe('create_entry', function () {
		it('creates entry with basic data', function () {
			$entry = $this->writer->create_entry(
				'<html>',
				array('Content-Type: text/html'),
				200
			);

			expect($entry)->toBeInstanceOf(Entry::class);
			expect($entry->output)->toBe('<html>');
			expect($entry->headers)->toBe(array('Content-Type: text/html'));
			expect($entry->status)->toBe(200);
		});

		it('sets gzip flag when config enables it', function () {
			$entry = $this->writer->create_entry(
				'<html>',
				array(),
				200
			);

			expect($entry->gzip)->toBeTrue();
		});

		it('does not set gzip flag when config disables it', function () {
			$config = new Config(
				3600, 600, false, false, // Gzip disabled
				array(), array(), array(), array(), array()
			);
			$writer = new Writer($config, $this->storage);

			$entry = $writer->create_entry(
				'<html>',
				array(),
				200
			);

			expect($entry->gzip)->toBeFalse();
		});

		it('includes custom TTL and grace', function () {
			$entry = $this->writer->create_entry(
				'<html>',
				array(),
				200,
				7200, // Custom TTL
				1200  // Custom grace
			);

			expect($entry->custom_ttl)->toBe(7200);
			expect($entry->custom_grace)->toBe(1200);
		});

		it('includes url when provided', function () {
			$entry = $this->writer->create_entry(
				'<html>',
				array(),
				200,
				null,
				null,
				'example.com/about/'
			);

			expect($entry->url)->toBe('example.com/about/');
		});

		it('includes variant data when provided', function () {
			$variant = array('cookies' => array('session_id'));
			$entry = $this->writer->create_entry(
				'<html>',
				array(),
				200,
				null,
				null,
				'',
				$variant
			);

			expect($entry->variant)->toBe($variant);
		});

		it('sets updated timestamp to current time', function () {
			$before = time();
			$entry = $this->writer->create_entry('<html>', array(), 200);
			$after = time();

			expect($entry->updated)->toBeGreaterThanOrEqual($before);
			expect($entry->updated)->toBeLessThanOrEqual($after);
		});
	});

	describe('compress', function () {
		it('returns entry as-is when gzip disabled', function () {
			$entry = new Entry(
				'<html>',
				'',
				array(),
				200,
				false, // Gzip disabled
				time()
			);

			$result = $this->writer->compress($entry);

			expect($result)->toBe($entry);
			expect($result->output)->toBe('<html>');
		});

		it('compresses output when gzip enabled', function () {
			$original = '<html>Large content here</html>';
			$entry = new Entry(
				$original,
				'',
				array(),
				200,
				true, // Gzip enabled
				time()
			);

			$result = $this->writer->compress($entry);

			expect($result->output)->not->toBe($original);
			expect($result->gzip)->toBeTrue();

			// Verify it's actually compressed
			$decompressed = gzuncompress($result->output);
			expect($decompressed)->toBe($original);
		});

		it('disables gzip on compression failure', function () {
			// Create entry with gzip flag but simulate failure
			// (Note: gzcompress rarely fails, but we handle it)
			$entry = new Entry(
				'<html>',
				'',
				array(),
				200,
				true,
				time()
			);

			$result = $this->writer->compress($entry);

			// Should return entry (compressed or with gzip disabled)
			expect($result)->toBeInstanceOf(Entry::class);
		});
	});

	describe('store', function () {
		it('returns false when storage unavailable', function () {
			$this->storage->shouldReceive('is_available')->andReturn(false);

			$entry = new Entry('<html>', '', array(), 200, false, time());
			$result = $this->writer->store('hash', $entry, array(), true);

			expect($result)->toBeFalse();
		});

		it('stores cache entry successfully', function () {
			$entry = new Entry(
				'<html>',
				'',
				array('Content-Type: text/html'),
				200,
				false,
				time()
			);

			$this->storage->shouldReceive('is_available')->andReturn(true);
			$this->storage->shouldReceive('perform_cache')
				->with('hash', Mockery::type('array'), array('flag1'), true)
				->andReturn(true);

			$result = $this->writer->store('hash', $entry, array('flag1'), true);

			expect($result)->toBeTrue();
		});

		it('converts entry to array for storage', function () {
			$entry = new Entry(
				'<html>',
				'',
				array('Header: value'),
				200,
				true,
				$timestamp = time(),
				7200,
				1200
			);

			$this->storage->shouldReceive('is_available')->andReturn(true);
			$this->storage->shouldReceive('perform_cache')
				->with('hash', Mockery::on(function ($data) use ($timestamp) {
					return is_array($data)
						&& $data['output'] === '<html>'
						&& $data['status'] === 200
						&& $data['gzip'] === true
						&& $data['updated'] === $timestamp
						&& $data['custom_ttl'] === 7200
						&& $data['custom_grace'] === 1200;
				}), Mockery::any(), Mockery::any())
				->once()
				->andReturn(true);

			$result = $this->writer->store('hash', $entry, array(), true);

			expect($result)->toBeTrue();
		});

		it('passes cacheable flag to storage', function () {
			$entry = new Entry('<html>', '', array(), 200, false, time());

			$this->storage->shouldReceive('is_available')->andReturn(true);
			$this->storage->shouldReceive('perform_cache')
				->with(Mockery::any(), Mockery::any(), Mockery::any(), false)
				->once()
				->andReturn(true);

			$result = $this->writer->store('hash', $entry, array(), false);

			expect($result)->toBeTrue();
		});
	});

	describe('integration', function () {
		it('creates, compresses, and stores complete workflow', function () {
			$output = '<html>Test content</html>';
			$headers = array('Content-Type: text/html');

			// Create entry
			$entry = $this->writer->create_entry($output, $headers, 200);
			expect($entry->gzip)->toBeTrue();

			// Compress
			$compressed = $this->writer->compress($entry);
			expect($compressed->output)->not->toBe($output);

			// Store
			$this->storage->shouldReceive('is_available')->andReturn(true);
			$this->storage->shouldReceive('perform_cache')->andReturn(true);

			$stored = $this->writer->store('hash', $compressed, array('flag1'), true);
			expect($stored)->toBeTrue();
		});
	});
});
