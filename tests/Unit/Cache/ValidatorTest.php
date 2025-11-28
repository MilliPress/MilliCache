<?php

use MilliCache\Engine\Cache\Validator;
use MilliCache\Engine\Cache\Config;
use MilliCache\Engine\Cache\Entry;

describe('Validator', function () {
	beforeEach(function () {
		$this->config = new Config(
			3600, // TTL: 1 hour
			600,  // Grace: 10 minutes
			true,
			false,
			array(),
			array(),
			array(),
			array(),
			array()
		);

		$this->validator = new Validator($this->config);
	});

	describe('is_stale', function () {
		it('returns false for fresh entries', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false,
				time() - 1800 // Updated 30 minutes ago
			);

			expect($this->validator->is_stale($entry))->toBeFalse();
		});

		it('returns true for stale entries', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false,
				time() - 3700 // Updated 61 minutes ago (past TTL)
			);

			expect($this->validator->is_stale($entry))->toBeTrue();
		});

		it('uses custom TTL from entry', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false,
				time() - 1800, // Updated 30 minutes ago
				1200 // Custom TTL: 20 minutes
			);

			// Should be stale because custom TTL is 20 minutes
			expect($this->validator->is_stale($entry))->toBeTrue();
		});

		it('uses custom TTL parameter over entry TTL', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false,
				time() - 1800, // Updated 30 minutes ago
				1200 // Custom TTL in entry: 20 minutes
			);

			// Override with 2 hour TTL
			expect($this->validator->is_stale($entry, 7200))->toBeFalse();
		});
	});

	describe('is_too_old', function () {
		it('returns false for fresh entries', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false,
				time() - 1800 // Updated 30 minutes ago
			);

			expect($this->validator->is_too_old($entry))->toBeFalse();
		});

		it('returns false for stale but within grace', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false,
				time() - 3900 // Updated 65 minutes ago (past TTL but within grace)
			);

			expect($this->validator->is_too_old($entry))->toBeFalse();
		});

		it('returns true for entries past TTL + grace', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false,
				time() - 4300 // Updated 71 minutes ago (past TTL + grace)
			);

			expect($this->validator->is_too_old($entry))->toBeTrue();
		});

		it('uses custom grace from entry', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false,
				time() - 3700, // Updated 61 minutes ago
				null,
				300 // Custom grace: 5 minutes
			);

			// Should be too old (61 > 60 + 5)
			expect($this->validator->is_too_old($entry))->toBeTrue();
		});
	});

	describe('is_fresh', function () {
		it('returns true for fresh entries', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false,
				time() - 1800 // Updated 30 minutes ago
			);

			expect($this->validator->is_fresh($entry))->toBeTrue();
		});

		it('returns false for stale entries', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false,
				time() - 3700 // Updated 61 minutes ago
			);

			expect($this->validator->is_fresh($entry))->toBeFalse();
		});
	});

	describe('time_to_expiry', function () {
		it('returns positive seconds for fresh cache', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false,
				time() - 1800 // Updated 30 minutes ago
			);

			$remaining = $this->validator->time_to_expiry($entry);
			expect($remaining)->toBeGreaterThan(1700);
			expect($remaining)->toBeLessThan(1900);
		});

		it('returns negative seconds for expired cache', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false,
				time() - 3700 // Updated 61 minutes ago
			);

			$remaining = $this->validator->time_to_expiry($entry);
			expect($remaining)->toBeLessThan(0);
		});
	});

	describe('time_to_deletion', function () {
		it('returns positive seconds for entries within TTL + grace', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false,
				time() - 1800 // Updated 30 minutes ago
			);

			$remaining = $this->validator->time_to_deletion($entry);
			// Should be ~2400 seconds (3600 + 600 - 1800)
			expect($remaining)->toBeGreaterThan(2300);
			expect($remaining)->toBeLessThan(2500);
		});

		it('returns negative seconds for entries past TTL + grace', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false,
				time() - 4300 // Updated 71 minutes ago
			);

			$remaining = $this->validator->time_to_deletion($entry);
			expect($remaining)->toBeLessThan(0);
		});
	});

	describe('get_effective_ttl', function () {
		it('returns config TTL when no custom TTL', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false,
				time()
			);

			expect($this->validator->get_effective_ttl($entry))->toBe(3600);
		});

		it('returns custom TTL from entry', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false,
				time(),
				7200 // Custom TTL
			);

			expect($this->validator->get_effective_ttl($entry))->toBe(7200);
		});

		it('returns override TTL parameter', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false,
				time(),
				7200 // Custom TTL in entry
			);

			expect($this->validator->get_effective_ttl($entry, 1800))->toBe(1800);
		});
	});

	describe('get_effective_grace', function () {
		it('returns config grace when no custom grace', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false,
				time()
			);

			expect($this->validator->get_effective_grace($entry))->toBe(600);
		});

		it('returns custom grace from entry', function () {
			$entry = new Entry(
				'<html>',
				array(),
				200,
				false,
				time(),
				null,
				1200 // Custom grace
			);

			expect($this->validator->get_effective_grace($entry))->toBe(1200);
		});
	});

	describe('format_time_remaining', function () {
		it('formats positive time correctly', function () {
			$result = $this->validator->format_time_remaining(90061); // 1d 1h 1m 1s
			expect($result)->toBe('1d 01h 01m 01s');
		});

		it('formats negative time with prefix', function () {
			$result = $this->validator->format_time_remaining(-3661); // -1h 1m 1s
			expect($result)->toBe('-0d 01h 01m 01s');
		});

		it('formats zero correctly', function () {
			$result = $this->validator->format_time_remaining(0);
			expect($result)->toBe('0d 00h 00m 00s');
		});

		it('formats hours and minutes', function () {
			$result = $this->validator->format_time_remaining(3661); // 1h 1m 1s
			expect($result)->toBe('0d 01h 01m 01s');
		});
	});
});
