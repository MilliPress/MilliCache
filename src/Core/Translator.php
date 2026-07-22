<?php
/**
 * Language-pack delivery for MilliPress text domains
 *
 * Injects available translation packs into WordPress's native update system
 * so language files install into wp-content/languages/plugins/ without being
 * bundled in the plugin ZIP, and translation fixes ship without a release.
 *
 * @link       https://www.millipress.com
 * @since      1.7.4
 *
 * @package    MilliCache
 * @subpackage Core
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Language-pack update injector.
 *
 * Fetches the per-product pack manifests from the millipress.com languages
 * API and maps them into the update_plugins transient's `translations` list,
 * where WordPress's Language_Pack_Upgrader picks them up like wordpress.org
 * packs. Covers every text domain this installation ships — including
 * vendored libraries (millibase) that are not installable plugins, which
 * WordPress would never fetch packs for on its own.
 *
 * @since      1.7.4
 * @package    MilliCache
 * @subpackage Core
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Translator {

	/**
	 * Languages-API URL template, %s = product slug (== text domain).
	 *
	 * @since 1.7.4
	 * @var   string
	 */
	private const API_URL_TEMPLATE = 'https://www.millipress.com/api/plugins/%s/languages';

	/**
	 * Transient key caching the fetched manifests (all products together).
	 *
	 * @since 1.7.4
	 * @var   string
	 */
	private const TRANSIENT_KEY = 'millicache_translation_manifests';

	/**
	 * Cache duration in seconds (12 hours).
	 *
	 * @since 1.7.4
	 * @var   int
	 */
	private const CACHE_DURATION = 43200;

	/**
	 * Register hooks.
	 *
	 * @since  1.7.4
	 * @access public
	 *
	 * @param Loader $loader The plugin hook loader.
	 */
	public function __construct( Loader $loader ) {
		$loader->add_filter( 'site_transient_update_plugins', $this, 'inject_translations' );
		$loader->add_action( 'delete_site_transient_update_plugins', $this, 'clear_manifest_cache' );
	}

	/**
	 * Append available, newer-than-installed packs to the update transient.
	 *
	 * Entries follow the shape wp_get_translation_updates() consumes; the
	 * Language_Pack_Upgrader downloads `package` and unzips it into
	 * wp-content/languages/plugins/.
	 *
	 * @since  1.7.4
	 * @access public
	 *
	 * @param  mixed $transient The update_plugins transient data.
	 * @return mixed The potentially modified transient data.
	 */
	public function inject_translations( $transient ) {
		if ( ! $transient instanceof \stdClass ) {
			return $transient;
		}

		$locales = $this->site_locales();

		if ( array() === $locales ) {
			return $transient;
		}

		if ( ! isset( $transient->translations ) || ! is_array( $transient->translations ) ) {
			$transient->translations = array();
		}

		$installed = function_exists( 'wp_get_installed_translations' )
			? wp_get_installed_translations( 'plugins' )
			: array();

		foreach ( $this->get_manifests() as $domain => $entries ) {
			foreach ( $entries as $entry ) {
				$language = $this->entry_string( $entry, 'language' );
				$updated  = $this->entry_string( $entry, 'updated' );
				$package  = $this->entry_string( $entry, 'package' );

				if ( '' === $language || '' === $updated || '' === $package ) {
					continue;
				}

				// Only locales this site actually uses — mirrors which locales
				// WordPress requests wordpress.org packs for.
				if ( ! in_array( $language, $locales, true ) ) {
					continue;
				}

				if ( $this->already_listed( $transient->translations, $domain, $language ) ) {
					continue;
				}

				// Freshness: the manifest's `updated` is the pack's
				// PO-Revision-Date; the pack ships its .po, so the installed
				// side carries the same clock. Equal means up to date.
				$current = (string) ( $installed[ $domain ][ $language ]['PO-Revision-Date'] ?? '' );

				if ( '' !== $current && (int) strtotime( $updated ) <= (int) strtotime( $current ) ) {
					continue;
				}

				$transient->translations[] = array(
					'type'       => 'plugin',
					'slug'       => $domain,
					'language'   => $language,
					'version'    => $this->entry_string( $entry, 'version' ),
					'updated'    => $updated,
					'package'    => $package,
					'autoupdate' => true,
				);
			}
		}

		return $transient;
	}

	/**
	 * A manifest entry's string field, empty when absent or non-scalar.
	 *
	 * @since  1.7.4
	 * @access private
	 *
	 * @param  array<mixed, mixed> $entry The manifest entry.
	 * @param  string              $key   The field to read.
	 * @return string The field value.
	 */
	private function entry_string( array $entry, string $key ): string {
		$value = $entry[ $key ] ?? '';

		return is_scalar( $value ) ? (string) $value : '';
	}

	/**
	 * Clear the cached manifests when WordPress force-checks for updates.
	 *
	 * @since  1.7.4
	 * @access public
	 *
	 * @return void
	 */
	public function clear_manifest_cache(): void {
		delete_site_transient( self::TRANSIENT_KEY );
	}

	/**
	 * The text domains to deliver packs for.
	 *
	 * @since  1.7.4
	 * @access private
	 *
	 * @return string[] Product slugs (each equals its text domain).
	 */
	private function products(): array {
		/**
		 * Filters the products the translation injector requests packs for.
		 *
		 * Each slug doubles as the text domain and as the path segment of the
		 * languages API (/api/plugins/{slug}/languages). A bundling host adds
		 * its own domain here — MilliCache Pro appends `millicache-pro` so the
		 * bundled core delivers all three domains from one injector.
		 *
		 * @since 1.7.4
		 *
		 * @param string[] $products Product slugs. Default millicache, millibase.
		 */
		$products = (array) apply_filters( 'millicache_translation_products', array( 'millicache', 'millibase' ) );

		return array_values( array_unique( array_filter( array_map( 'strval', $products ) ) ) );
	}

	/**
	 * Locales this site can use — the current locale plus every installed
	 * core language pack (matching what WordPress reports to wordpress.org).
	 *
	 * @since  1.7.4
	 * @access private
	 *
	 * @return string[] Locale codes, e.g. de_DE, de_DE_formal.
	 */
	private function site_locales(): array {
		$locales = function_exists( 'get_available_languages' ) ? get_available_languages() : array();

		if ( function_exists( 'get_locale' ) ) {
			$locales[] = get_locale();
		}

		// en_US ships with core; there are no packs for it.
		return array_values( array_diff( array_unique( array_filter( $locales ) ), array( 'en_US' ) ) );
	}

	/**
	 * Whether the transient already lists a pack for this domain + locale.
	 *
	 * @since  1.7.4
	 * @access private
	 *
	 * @param  array<int, mixed> $translations Existing translations entries.
	 * @param  string            $domain       Text domain.
	 * @param  string            $language     Locale code.
	 * @return bool True when an entry for the pair exists.
	 */
	private function already_listed( array $translations, string $domain, string $language ): bool {
		foreach ( $translations as $translation ) {
			$translation = (array) $translation;

			if ( ( $translation['slug'] ?? '' ) === $domain && ( $translation['language'] ?? '' ) === $language ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * The cached manifests for every configured product, fetching missing ones.
	 *
	 * Cached as one bundle so a site with three domains still makes at most
	 * three requests per 12 hours. A failed fetch caches as an empty list —
	 * same backoff as the plugin-update checker.
	 *
	 * @since  1.7.4
	 * @access private
	 *
	 * @return array<string, array<int, array<mixed, mixed>>> Manifest entries keyed by product slug.
	 */
	private function get_manifests(): array {
		$products  = $this->products();
		$cached    = get_site_transient( self::TRANSIENT_KEY );
		$manifests = array();

		if ( is_array( $cached ) ) {
			// A transient can hold anything — keep only well-shaped rows.
			foreach ( $cached as $domain => $entries ) {
				if ( is_string( $domain ) && is_array( $entries ) ) {
					$manifests[ $domain ] = array_values( array_filter( $entries, 'is_array' ) );
				}
			}
		}

		$missing = array_diff( $products, array_keys( $manifests ) );

		foreach ( $missing as $product ) {
			$manifests[ $product ] = $this->fetch_manifest( $product );
		}

		if ( array() !== $missing ) {
			set_site_transient( self::TRANSIENT_KEY, $manifests, self::CACHE_DURATION );
		}

		// Only the currently configured products — a stale cache may hold more.
		return array_intersect_key( $manifests, array_flip( $products ) );
	}

	/**
	 * Fetch one product's pack manifest from the languages API.
	 *
	 * @since  1.7.4
	 * @access private
	 *
	 * @param  string $product Product slug.
	 * @return array<int, array<mixed, mixed>> Manifest entries, empty on failure.
	 */
	private function fetch_manifest( string $product ): array {
		/**
		 * Filters the languages-API URL for a product.
		 *
		 * Lets a staging or development environment point the injector at a
		 * different manifest host.
		 *
		 * @since 1.7.4
		 *
		 * @param string $url     The manifest URL.
		 * @param string $product The product slug being fetched.
		 */
		$url = (string) apply_filters(
			'millicache_translation_api_url',
			sprintf( self::API_URL_TEMPLATE, $product ),
			$product
		);

		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 10,
				'headers' => array(
					'Accept' => 'application/json',
				),
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return array();
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $data ) || ! isset( $data['translations'] ) || ! is_array( $data['translations'] ) ) {
			return array();
		}

		return array_values( array_filter( $data['translations'], 'is_array' ) );
	}
}
