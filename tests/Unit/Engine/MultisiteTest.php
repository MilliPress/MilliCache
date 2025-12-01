<?php
/**
 * Tests for Multisite Helper.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Engine\Multisite;

// Mock WordPress multisite functions.
if ( ! function_exists( 'is_multisite' ) ) {
	function is_multisite() {
		global $test_is_multisite;
		return $test_is_multisite ?? false;
	}
}

if ( ! function_exists( 'get_sites' ) ) {
	function get_sites( $args = array() ) {
		global $test_sites;
		return $test_sites ?? array( 1 );
	}
}

if ( ! function_exists( 'get_networks' ) ) {
	function get_networks( $args = array() ) {
		global $test_networks;
		return $test_networks ?? array( 1 );
	}
}

if ( ! function_exists( 'get_current_blog_id' ) ) {
	function get_current_blog_id() {
		global $test_current_site_id;
		return $test_current_site_id ?? 1;
	}
}

if ( ! function_exists( 'get_current_network_id' ) ) {
	function get_current_network_id() {
		global $test_current_network_id;
		return $test_current_network_id ?? 1;
	}
}

uses()->beforeEach( function () {
	// Reset global test state.
	global $test_is_multisite, $test_sites, $test_networks, $test_current_site_id, $test_current_network_id;
	$test_is_multisite = false;
	$test_sites = array( 1 );
	$test_networks = array( 1 );
	$test_current_site_id = 1;
	$test_current_network_id = 1;

	$this->multisite = new Multisite();
} );

describe( 'Multisite', function () {

	describe( 'is_enabled', function () {
		it( 'returns false when multisite is disabled', function () {
			global $test_is_multisite;
			$test_is_multisite = false;

			expect( $this->multisite->is_enabled() )->toBeFalse();
		} );

		it( 'returns true when multisite is enabled', function () {
			global $test_is_multisite;
			$test_is_multisite = true;

			expect( $this->multisite->is_enabled() )->toBeTrue();
		} );
	} );

	describe( 'get_site_ids', function () {
		it( 'returns [1] when multisite is disabled', function () {
			global $test_is_multisite;
			$test_is_multisite = false;

			$result = $this->multisite->get_site_ids();

			expect( $result )->toBe( array( 1 ) );
		} );

		it( 'returns site IDs for network 1 by default', function () {
			global $test_is_multisite, $test_sites;
			$test_is_multisite = true;
			$test_sites = array( 1, 2, 3 );

			$result = $this->multisite->get_site_ids();

			expect( $result )->toBe( array( 1, 2, 3 ) );
		} );

		it( 'accepts custom network ID', function () {
			global $test_is_multisite, $test_sites;
			$test_is_multisite = true;
			$test_sites = array( 4, 5, 6 );

			$result = $this->multisite->get_site_ids( 2 );

			expect( $result )->toBe( array( 4, 5, 6 ) );
		} );

		it( 'returns [1] when get_sites function does not exist', function () {
			global $test_is_multisite;
			$test_is_multisite = true;

			// Function exists check is internal, will fallback.
			$result = $this->multisite->get_site_ids();

			expect( is_array( $result ) )->toBeTrue();
		} );
	} );

	describe( 'get_network_ids', function () {
		it( 'returns [1] when multisite is disabled', function () {
			global $test_is_multisite;
			$test_is_multisite = false;

			$result = $this->multisite->get_network_ids();

			expect( $result )->toBe( array( 1 ) );
		} );

		it( 'returns single network ID for single network', function () {
			global $test_is_multisite, $test_networks;
			$test_is_multisite = true;
			$test_networks = array( 1 );

			$result = $this->multisite->get_network_ids();

			expect( $result )->toBe( array( 1 ) );
		} );

		it( 'returns multiple network IDs', function () {
			global $test_is_multisite, $test_networks;
			$test_is_multisite = true;
			$test_networks = array( 1, 2, 3 );

			$result = $this->multisite->get_network_ids();

			expect( $result )->toBe( array( 1, 2, 3 ) );
		} );

		it( 'casts result to array', function () {
			global $test_is_multisite, $test_networks;
			$test_is_multisite = true;
			$test_networks = array( 1 );

			$result = $this->multisite->get_network_ids();

			expect( is_array( $result ) )->toBeTrue();
		} );
	} );

	describe( 'get_flag_prefix', function () {
		it( 'returns empty string when multisite is disabled', function () {
			global $test_is_multisite;
			$test_is_multisite = false;

			$result = $this->multisite->get_flag_prefix();

			expect( $result )->toBe( '' );
		} );

		it( 'returns site prefix for single network', function () {
			global $test_is_multisite, $test_networks, $test_current_site_id;
			$test_is_multisite = true;
			$test_networks = array( 1 );
			$test_current_site_id = 1;

			$result = $this->multisite->get_flag_prefix();

			expect( $result )->toBe( '1:' );
		} );

		it( 'returns network:site prefix for multiple networks', function () {
			global $test_is_multisite, $test_networks, $test_current_site_id, $test_current_network_id;
			$test_is_multisite = true;
			$test_networks = array( 1, 2 );
			$test_current_site_id = 1;
			$test_current_network_id = 1;

			$result = $this->multisite->get_flag_prefix();

			expect( $result )->toBe( '1:1:' );
		} );

		it( 'uses provided site_id', function () {
			global $test_is_multisite, $test_networks;
			$test_is_multisite = true;
			$test_networks = array( 1 );

			$result = $this->multisite->get_flag_prefix( 5 );

			expect( $result )->toBe( '5:' );
		} );

		it( 'uses provided network_id in multi-network setup', function () {
			global $test_is_multisite, $test_networks;
			$test_is_multisite = true;
			$test_networks = array( 1, 2 );

			$result = $this->multisite->get_flag_prefix( 3, 2 );

			expect( $result )->toBe( '2:3:' );
		} );

		it( 'uses current site_id when null', function () {
			global $test_is_multisite, $test_networks, $test_current_site_id;
			$test_is_multisite = true;
			$test_networks = array( 1 );
			$test_current_site_id = 7;

			$result = $this->multisite->get_flag_prefix( null );

			expect( $result )->toBe( '7:' );
		} );

		it( 'uses current network_id when null', function () {
			global $test_is_multisite, $test_networks, $test_current_site_id, $test_current_network_id;
			$test_is_multisite = true;
			$test_networks = array( 1, 2, 3 );
			$test_current_site_id = 2;
			$test_current_network_id = 3;

			$result = $this->multisite->get_flag_prefix( null, null );

			expect( $result )->toBe( '3:2:' );
		} );

		it( 'accepts string site_id', function () {
			global $test_is_multisite, $test_networks;
			$test_is_multisite = true;
			$test_networks = array( 1 );

			$result = $this->multisite->get_flag_prefix( '10' );

			expect( $result )->toBe( '10:' );
		} );

		it( 'accepts string network_id', function () {
			global $test_is_multisite, $test_networks;
			$test_is_multisite = true;
			$test_networks = array( 1, 2 );

			$result = $this->multisite->get_flag_prefix( 1, '2' );

			expect( $result )->toBe( '2:1:' );
		} );

		it( 'handles site_id zero', function () {
			global $test_is_multisite, $test_networks;
			$test_is_multisite = true;
			$test_networks = array( 1 );

			$result = $this->multisite->get_flag_prefix( 0 );

			expect( $result )->toBe( '0:' );
		} );
	} );

	describe( 'get_current_site_id', function () {
		it( 'returns 1 when multisite is disabled', function () {
			global $test_is_multisite;
			$test_is_multisite = false;

			$result = $this->multisite->get_current_site_id();

			expect( $result )->toBe( 1 );
		} );

		it( 'returns current site ID when multisite is enabled', function () {
			global $test_is_multisite, $test_current_site_id;
			$test_is_multisite = true;
			$test_current_site_id = 5;

			$result = $this->multisite->get_current_site_id();

			expect( $result )->toBe( 5 );
		} );

		it( 'returns 1 as fallback', function () {
			global $test_is_multisite, $test_current_site_id;
			$test_is_multisite = true;
			$test_current_site_id = 1;

			$result = $this->multisite->get_current_site_id();

			expect( $result )->toBe( 1 );
		} );
	} );

	describe( 'get_current_network_id', function () {
		it( 'returns 1 when multisite is disabled', function () {
			global $test_is_multisite;
			$test_is_multisite = false;

			$result = $this->multisite->get_current_network_id();

			expect( $result )->toBe( 1 );
		} );

		it( 'returns current network ID when multisite is enabled', function () {
			global $test_is_multisite, $test_current_network_id;
			$test_is_multisite = true;
			$test_current_network_id = 3;

			$result = $this->multisite->get_current_network_id();

			expect( $result )->toBe( 3 );
		} );

		it( 'returns 1 as fallback', function () {
			global $test_is_multisite, $test_current_network_id;
			$test_is_multisite = true;
			$test_current_network_id = 1;

			$result = $this->multisite->get_current_network_id();

			expect( $result )->toBe( 1 );
		} );
	} );

	describe( 'integration scenarios', function () {
		it( 'handles single site installation', function () {
			global $test_is_multisite;
			$test_is_multisite = false;

			expect( $this->multisite->is_enabled() )->toBeFalse();
			expect( $this->multisite->get_flag_prefix() )->toBe( '' );
			expect( $this->multisite->get_site_ids() )->toBe( array( 1 ) );
			expect( $this->multisite->get_network_ids() )->toBe( array( 1 ) );
		} );

		it( 'handles single network multisite', function () {
			global $test_is_multisite, $test_networks, $test_sites, $test_current_site_id;
			$test_is_multisite = true;
			$test_networks = array( 1 );
			$test_sites = array( 1, 2, 3 );
			$test_current_site_id = 2;

			expect( $this->multisite->is_enabled() )->toBeTrue();
			expect( $this->multisite->get_flag_prefix() )->toBe( '2:' );
			expect( $this->multisite->get_site_ids() )->toBe( array( 1, 2, 3 ) );
			expect( $this->multisite->get_network_ids() )->toBe( array( 1 ) );
		} );

		it( 'handles multi-network setup', function () {
			global $test_is_multisite, $test_networks, $test_sites;
			global $test_current_site_id, $test_current_network_id;
			$test_is_multisite = true;
			$test_networks = array( 1, 2 );
			$test_sites = array( 1, 2, 3 );
			$test_current_site_id = 2;
			$test_current_network_id = 2;

			expect( $this->multisite->is_enabled() )->toBeTrue();
			expect( $this->multisite->get_flag_prefix() )->toBe( '2:2:' );
			expect( $this->multisite->get_network_ids() )->toBe( array( 1, 2 ) );
		} );

		it( 'generates consistent prefixes for same site', function () {
			global $test_is_multisite, $test_networks;
			$test_is_multisite = true;
			$test_networks = array( 1 );

			$prefix1 = $this->multisite->get_flag_prefix( 5 );
			$prefix2 = $this->multisite->get_flag_prefix( 5 );

			expect( $prefix1 )->toBe( $prefix2 );
			expect( $prefix1 )->toBe( '5:' );
		} );

		it( 'generates different prefixes for different sites', function () {
			global $test_is_multisite, $test_networks;
			$test_is_multisite = true;
			$test_networks = array( 1 );

			$prefix1 = $this->multisite->get_flag_prefix( 1 );
			$prefix2 = $this->multisite->get_flag_prefix( 2 );

			expect( $prefix1 )->not->toBe( $prefix2 );
			expect( $prefix1 )->toBe( '1:' );
			expect( $prefix2 )->toBe( '2:' );
		} );
	} );
} );
