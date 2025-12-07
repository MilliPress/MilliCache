<?php

use MilliCache\Engine\Request\Parser;
use MilliCache\Engine\Cache\Config;

uses()->beforeEach(function () {
	$this->config = new Config(
		3600,
		600,
		true,
		false,
		array(),
		array(),
		array('ga_*', 'utm_*', '_ga'),
		array('utm_source', 'utm_campaign', 'fbclid'),
		array()
	);

	$this->parser = new Parser($this->config);
});

describe('Parser', function () {

	describe('parse_request_uri', function () {
		it('normalizes path to lowercase', function () {
			$result = $this->parser->parse_request_uri('/About/Contact');
			expect($result)->toBe('/about/contact');
		});

		it('removes ignored query parameters', function () {
			$result = $this->parser->parse_request_uri('/page?id=123&utm_source=google&name=test');
			expect($result)->toBe('/page?id=123&name=test');
		});

		it('sorts query parameters alphabetically', function () {
			$result = $this->parser->parse_request_uri('/page?z=1&a=2&m=3');
			expect($result)->toBe('/page?a=2&m=3&z=1');
		});

		it('removes all query params if all are ignored', function () {
			$result = $this->parser->parse_request_uri('/page?utm_source=google&fbclid=123');
			expect($result)->toBe('/page');
		});

		it('handles paths without query strings', function () {
			$result = $this->parser->parse_request_uri('/simple/path');
			expect($result)->toBe('/simple/path');
		});

		it('handles empty path', function () {
			$result = $this->parser->parse_request_uri('');
			expect($result)->toBe('');
		});

		it('handles root path', function () {
			$result = $this->parser->parse_request_uri('/');
			expect($result)->toBe('/');
		});

		it('decodes HTML entities in query string', function () {
			$result = $this->parser->parse_request_uri('/page?a=1&amp;b=2');
			expect($result)->toBe('/page?a=1&b=2');
		});
	});

	describe('remove_query_args', function () {
		it('removes matching query parameters', function () {
			$result = $this->parser->remove_query_args('foo=bar&utm_source=test&baz=qux', array('utm_source'));
			expect($result)->toBe('baz=qux&foo=bar');
		});

		it('supports wildcard patterns', function () {
			$result = $this->parser->remove_query_args('ga_id=123&ga_session=456&other=789', array('ga_*'));
			expect($result)->toBe('other=789');
		});

		it('handles parameters without values', function () {
			$result = $this->parser->remove_query_args('foo&bar=123&baz', array('foo'));
			expect($result)->toBe('bar=123&baz');
		});

		it('returns empty string for empty query', function () {
			$result = $this->parser->remove_query_args('', array('test'));
			expect($result)->toBe('');
		});

		it('sorts remaining parameters', function () {
			$result = $this->parser->remove_query_args('z=1&a=2&m=3', array());
			expect($result)->toBe('a=2&m=3&z=1');
		});

		it('removes all parameters if all match patterns', function () {
			$result = $this->parser->remove_query_args('utm_source=google&utm_campaign=test', array('utm_*'));
			expect($result)->toBe('');
		});

		it('decodes HTML entities before processing', function () {
			$result = $this->parser->remove_query_args('a=1&amp;b=2', array('b'));
			expect($result)->toBe('a=1');
		});
	});

	describe('parse_cookies', function () {
		it('removes cookies matching ignore patterns', function () {
			$cookies = array(
				'session_id' => '123',
				'ga_tracking' => '456',
				'_ga' => '789',
				'user_pref' => 'abc',
			);

			$result = $this->parser->parse_cookies($cookies);

			expect($result)->toHaveKey('session_id');
			expect($result)->toHaveKey('user_pref');
			expect($result)->not->toHaveKey('ga_tracking');
			expect($result)->not->toHaveKey('_ga');
		});

		it('is case insensitive for cookie names', function () {
			$cookies = array(
				'GA_TRACKING' => '123',
				'regular' => '456',
			);

			$result = $this->parser->parse_cookies($cookies);

			expect($result)->not->toHaveKey('GA_TRACKING');
			expect($result)->toHaveKey('regular');
		});

		it('returns all cookies if none match ignore list', function () {
			$cookies = array(
				'session' => '123',
				'user' => '456',
			);

			$result = $this->parser->parse_cookies($cookies);

			expect($result)->toHaveCount(2);
			expect($result)->toBe($cookies);
		});

		it('returns empty array for empty input', function () {
			$result = $this->parser->parse_cookies(array());
			expect($result)->toBe(array());
		});

		it('supports wildcard patterns in cookie names', function () {
			$cookies = array(
				'utm_source' => '1',
				'utm_campaign' => '2',
				'other' => '3',
			);

			$result = $this->parser->parse_cookies($cookies);

			expect($result)->toHaveCount(1);
			expect($result)->toHaveKey('other');
		});
	});

	describe('get_url_hash', function () {
		it('generates consistent hash for same URL', function () {
			$hash1 = $this->parser->get_url_hash('example.com', '/page');
			$hash2 = $this->parser->get_url_hash('example.com', '/page');

			expect($hash1)->toBe($hash2);
		});

		it('generates different hashes for different URLs', function () {
			$hash1 = $this->parser->get_url_hash('example.com', '/page1');
			$hash2 = $this->parser->get_url_hash('example.com', '/page2');

			expect($hash1)->not->toBe($hash2);
		});

		it('normalizes host to lowercase', function () {
			$hash1 = $this->parser->get_url_hash('Example.COM', '/page');
			$hash2 = $this->parser->get_url_hash('example.com', '/page');

			expect($hash1)->toBe($hash2);
		});

		it('normalizes path through parser', function () {
			$hash1 = $this->parser->get_url_hash('example.com', '/Page?utm_source=test&id=123');
			$hash2 = $this->parser->get_url_hash('example.com', '/page?id=123');

			expect($hash1)->toBe($hash2);
		});

		it('returns MD5 hash format', function () {
			$hash = $this->parser->get_url_hash('example.com', '/page');
			expect($hash)->toMatch('/^[a-f0-9]{32}$/');
		});
	});
});
