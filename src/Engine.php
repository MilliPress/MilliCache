<?php
/**
 * The independent Cache-Engine to avoid an overhead.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

namespace MilliCache;

use MilliCache\Admin\Admin;
use MilliCache\Core\Settings;
use MilliCache\Core\Storage;
use MilliCache\Engine\Cache\Config;
use MilliCache\Engine\Cache\Entry;
use MilliCache\Engine\Cache\Handler as CacheHandler;
use MilliCache\Engine\Clearing\Handler as ClearingHandler;
use MilliCache\Engine\FlagManager;
use MilliCache\Engine\Multisite;
use MilliCache\Engine\Request\Handler as RequestHandler;
use MilliCache\Engine\Utilities\ServerVars;
use MilliCache\Rules\PHP;
use MilliCache\Rules\WP;
use MilliCache\Deps\MilliRules\MilliRules;
use MilliCache\Deps\MilliRules\Rules;

! defined( 'ABSPATH' ) && exit;

/**
 * Fired by advanced-cache.php
 *
 * This class defines all code necessary for caching.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Engine {

	/**
	 * If the cache engine has been started.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var bool If the cache engine has been started.
	 */
	private static bool $started = false;

	/**
	 * The Cache Storage object.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var Storage The Cache Storage object.
	 */
	private static Storage $storage;

	/**
	 * The Multisite helper instance.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var Multisite The Multisite helper instance.
	 */
	private static Multisite $multisite;

	/**
	 * The MilliCache Settings.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array<mixed> The MilliPress Settings.
	 */
	private static array $settings;

	/**
	 * Whether TTL has been manually set via set_ttl().
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var bool If TTL was manually overridden.
	 */
	private static bool $ttl_overridden = false;

	/**
	 * Custom TTL override value.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var int|null Custom TTL value when overridden.
	 */
	private static ?int $ttl_override = null;

	/**
	 * Whether a grace period has been manually set via set_grace().
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var bool If grace was manually overridden.
	 */
	private static bool $grace_overridden = false;

	/**
	 * Custom grace override value.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var int|null Custom grace value when overridden.
	 */
	private static ?int $grace_override = null;

	/**
	 * Request hash.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var string The request hash.
	 */
	private static string $request_hash;

	/**
	 * Request handler.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var RequestHandler|null The request handler instance.
	 */
	private static ?RequestHandler $request_handler = null;

	/**
	 * Cache handler.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var CacheHandler|null The cache handler instance.
	 */
	private static ?CacheHandler $cache_handler = null;

	/**
	 * Clearing handler.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var ClearingHandler|null The clearing handler instance.
	 */
	private static ?ClearingHandler $clearing_handler = null;

	/**
	 * Cache configuration.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var Config|null The cache configuration instance.
	 */
	private static ?Config $config = null;

	/**
	 * Flag manager.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var FlagManager|null The flag manager instance.
	 */
	private static ?FlagManager $flag_manager = null;

	/**
	 * Debug data.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array<string,mixed>|null Debug data.
	 */
	private static ?array $debug_data = null;

	/**
	 * FastCGI Regenerate.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var bool If we can regenerate the request in the background.
	 */
	private static bool $fcgi_regenerate = false;

	/**
	 * Cache decision override from rules.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array{decision: bool, reason: string}|null Cache decision with 'decision' and 'reason' keys.
	 */
	private static ?array $cache_decision = null;

	/**
	 * Whether autoloader has been initialized.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var bool
	 */
	private static bool $autoloader_initialized = false;

	/**
	 * If not running, start the cache engine.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public function __construct() {
		if ( ! self::$started ) {
			self::start();
		}
	}

	/**
	 * Start the cache engine.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public static function start() {
		self::init_autoloader();

		// Initialize multisite helper.
		self::$multisite = new Multisite();

		self::get_settings();
		self::setup_test_cookie();
		self::register_rules();
		self::get_storage();
		self::warmup();

		// Execute PHP rules.
		MilliRules::execute_rules( array( 'PHP' ) );

		// Proceed if the request is cachable.
		if ( self::check_cache_decision() ) {
			self::run();
		}

		self::$started = true;
	}

	/**
	 * Get the Settings.
	 *
	 * @since     1.0.0
	 * @access    public
	 *
	 * @param string|null $module The MilliCache Settings module.
	 * @return array<mixed> The MilliCache Settings.
	 */
	public static function get_settings( ?string $module = null ): array {
		if ( ! isset( self::$settings ) && class_exists( 'MilliCache\Core\Settings' ) ) {
			self::$settings = ( new Settings() )->get_settings( $module );
		}

		return self::$settings;
	}

	/**
	 * Initialize MilliRules and register MilliCache rules.
	 *
	 * Initializes the MilliRules package system with PHP package for early execution,
	 * registers namespaces and defers WP package loading until WordPress is ready.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private static function register_rules(): void {
		// Initialize MilliRules with the PHP package for early execution.
		MilliRules::init( array( 'PHP' ) );

		// Register action namespaces for auto-resolution.
		Rules::register_namespace( 'Actions', 'MilliCache\Rules\Actions\PHP', 'PHP' );
		Rules::register_namespace( 'Actions', 'MilliCache\Rules\Actions\WP', 'WP' );

		// Register MilliCache PHP rules (execute before WordPress loads).
		PHP::register();

		// Defer WP package and rules until WordPress is ready.
		add_action(
			'plugins_loaded',
			function () {
				// Load MilliRules WP package.
				MilliRules::load_packages( array( 'WP' ) );

				// Register MilliCache WP rules.
				WP::register();
			},
			1
		);
	}

	/**
	 * Returns the MilliCache Storage instance.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   Storage The MilliCache Storage instance.
	 */
	public static function get_storage(): Storage {
		if ( ! isset( self::$storage ) && class_exists( 'MilliCache\Core\Storage' ) ) {
			self::$storage = new Storage( (array) self::$settings['storage'] );
		}

		return self::$storage;
	}

	/**
	 * Get cache configuration instance.
	 *
	 * Provides access to the immutable cache configuration object.
	 * External code can use this to access all configuration properties.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   Config The cache configuration instance.
	 */
	public static function get_config(): Config {
		if ( ! self::$config ) {
			self::$config = self::init_config();
		}

		return self::$config;
	}

	/**
	 * Get WordPress test cookie name.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   string The test cookie name.
	 */
	private static function get_test_cookie_name(): string {
		return defined( 'TEST_COOKIE' ) ? TEST_COOKIE : 'wordpress_test_cookie';
	}

	/**
	 * Initialize cache configuration.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   Config The initialized configuration.
	 */
	private static function init_config(): Config {
		// Get cache settings.
		$cache_settings = self::$settings['cache'] ?? array();
		$settings = is_array( $cache_settings ) ? $cache_settings : array();

		// Add WordPress test cookie to ignore list.
		if ( ! isset( $settings['ignore_cookies'] ) || ! is_array( $settings['ignore_cookies'] ) ) {
			$settings['ignore_cookies'] = array();
		}
		$settings['ignore_cookies'][] = self::get_test_cookie_name();

		return Config::from_settings( $settings );
	}

	/**
	 * Setup WordPress test cookie for login requests.
	 *
	 * WordPress uses a test cookie to verify browser cookie support.
	 * This sets the cookie for wp-login.php POST requests.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	private static function setup_test_cookie(): void {
		if ( strpos( ServerVars::get( 'REQUEST_URI' ), '/wp-login.php' ) === 0
			&& strtoupper( ServerVars::get( 'REQUEST_METHOD' ) ) === 'POST' ) {
			$_COOKIE[ self::get_test_cookie_name() ] = 'WP Cookie check';
		}
	}

	/**
	 * Warm up the cache engine.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	private static function warmup() {
		// Initialize invalidation handler.
		self::get_clearing_handler();

		// Register the shutdown function to expire/delete cache flags.
		register_shutdown_function( array( __CLASS__, 'clear_cache_on_shutdown' ) );

		// Always set the initial header.
		self::set_header( 'Status', 'miss' );
	}

	/**
	 * Get clearing handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   ClearingHandler The clearing handler instance.
	 */
	private static function get_clearing_handler(): ClearingHandler {
		if ( ! self::$clearing_handler ) {
			$config = self::get_config();

			self::$clearing_handler = new ClearingHandler(
				self::$storage,
				self::get_request_handler(),
				self::$multisite,
				$config->ttl
			);
		}

		return self::$clearing_handler;
	}

	/**
	 * Get request handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   RequestHandler The request handler instance.
	 */
	private static function get_request_handler(): RequestHandler {
		if ( ! self::$request_handler ) {
			self::$request_handler = new RequestHandler( self::get_config() );
		}

		return self::$request_handler;
	}

	/**
	 * Get cache handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   CacheHandler The cache handler instance.
	 */
	private static function get_cache_handler(): CacheHandler {
		if ( ! self::$cache_handler ) {
			self::$cache_handler = new CacheHandler(
				self::get_config(),
				self::$storage
			);
		}

		return self::$cache_handler;
	}

	/**
	 * Get flag manager instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   FlagManager The flag manager instance.
	 */
	private static function get_flag_manager(): FlagManager {
		if ( ! self::$flag_manager ) {
			self::$flag_manager = new FlagManager( self::$multisite );
		}

		return self::$flag_manager;
	}

	/**
	 * Run the cache engine.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	private static function run() {
		// Generate the unique request hash.
		self::generate_request_hash();

		// Get and return cached content.
		self::get_cache();

		// Start the output buffer, if needed.
		add_action(
			'template_redirect',
			function () {
				// Start the buffer if WP rules pass.
				if ( self::check_cache_decision() ) {
					self::start_buffer();
				}
			},
			200
		);
	}

	/**
	 * Generate a unique request hash.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	private static function generate_request_hash(): void {
		// Process request (clean and generate hash).
		self::$request_hash = self::get_request_handler()->process();

		// Store debug data if enabled.
		if ( self::get_config()->debug ) {
			self::$debug_data = self::get_request_handler()->get_debug_data();
			self::set_header( 'Key', self::$request_hash );
		}
	}

	/**
	 * Get the cache for the request.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	private static function get_cache() {
		// Get and validate cache.
		$result = self::get_cache_handler()->get_and_validate(
			self::$request_hash,
			self::can_fcgi_regenerate()
		);

		// No cache to serve.
		if ( ! $result['serve'] ) {
			if ( $result['regenerate'] ) {
				self::$fcgi_regenerate = true;
			}
			return;
		}

		// Set regenerate flag if needed.
		self::$fcgi_regenerate = $result['regenerate'];

		// Get the cache entry (guaranteed to exist if serve is true).
		$entry = $result['entry'];
		assert( $entry instanceof Entry );

		// Debug headers.
		if ( self::get_config()->debug ) {
			self::set_header( 'Time', gmdate( 'D, d M Y H:i:s \G\M\T', $entry->updated ) );
			self::set_header( 'Flags', implode( ' ', $result['result']->flags ) );

			if ( $entry->gzip ) {
				self::set_header( 'Gzip', 'true' );
			}

			$validator = self::get_cache_handler()->get_validator();
			$time_left = $validator->time_to_expiry( $entry );
			self::set_header( 'Expires', $validator->format_time_remaining( $time_left ) );
		}

		// Set status header.
		self::set_header( 'Status', self::$fcgi_regenerate ? 'stale' : 'hit' );

		// Output the cache.
		self::get_cache_handler()->get_reader()->output( $entry, self::$fcgi_regenerate );
	}

	/**
	 * Start the output buffer.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	private static function start_buffer() {
		ob_start( array( __CLASS__, 'output_buffer' ) );
	}

	/**
	 * Output buffer callback.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param    string $output The output buffer.
	 * @return   string The output buffer.
	 */
	private static function output_buffer( string $output ): ?string {
		// Get the flags for this request.
		$flags = self::get_flag_manager()->get_all();
		$flags[] = 'url:' . self::get_request_handler()->get_url_hash();
		$flags = array_unique( $flags );

		// If no flags are set, use the fallback site flag.
		if ( count( $flags ) <= 1 ) {
			$flags[] = self::get_flag_key( 'flag' );
		}

		// Determine custom TTL/grace if overridden.
		$custom_ttl   = self::$ttl_overridden ? self::$ttl_override : null;
		$custom_grace = self::$grace_overridden ? self::$grace_override : null;
		$debug        = self::get_config()->debug ? self::$debug_data : null;

		// Cache the output.
		$result = self::get_cache_handler()->cache_output(
			self::$request_hash,
			$output,
			$flags,
			$custom_ttl,
			$custom_grace,
			$debug
		);

		// Set headers based on result.
		if ( ! $result['cached'] && ! self::$fcgi_regenerate ) {
			self::set_header( 'Status', 'bypass' );
		}

		// Add reason header if a reason message exists.
		if ( ! empty( $result['reason'] ) ) {
			self::set_reason( $result['reason'] );
		}

		// Return output, but not for the background task.
		return self::$fcgi_regenerate ? null : $output;
	}

	/**
	 * Clears items from the cache during shutdown.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	public static function clear_cache_on_shutdown() {
		self::get_clearing_handler()->flush_on_shutdown();
	}

	/**
	 * Clear cache by given Targets.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string|array<string> $targets The targets (Flags, Post-IDs or URLs) to clear the cache for.
	 * @param bool                 $expire Expire cache if set to true, or delete by default.
	 * @return void
	 */
	public static function clear_cache_by_targets( $targets, bool $expire = false ): void {
		self::get_clearing_handler()->clear_by_targets( $targets, $expire );
	}

	/**
	 * Clear cache by given URLs.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string|array<string> $urls A string or array of URLs to flush.
	 * @param bool                 $expire Expire cache if set to true, or delete by default.
	 */
	public static function clear_cache_by_urls( $urls, bool $expire = false ): void {
		self::get_clearing_handler()->clear_by_urls( $urls, $expire );
	}

	/**
	 * Expire caches by post id.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int|array<int> $post_ids The post-IDs to expire.
	 * @param bool           $expire Expire cache if set to true, or delete by default.
	 */
	public static function clear_cache_by_post_ids( $post_ids, bool $expire = false ): void {
		self::get_clearing_handler()->clear_by_post_ids( $post_ids, $expire );
	}

	/**
	 * Clears cache by given flags.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string|array<string> $flags A string or array of flags to expire.
	 * @param bool                 $expire Expire cache if set to true, or delete by default.
	 * @param bool                 $add_prefix Add the flag prefix to the flags.
	 * @return void
	 */
	public static function clear_cache_by_flags( $flags, bool $expire = false, bool $add_prefix = true ): void {
		self::get_clearing_handler()->clear_by_flags( $flags, $expire, $add_prefix );
	}

	/**
	 * Clear the full cache of a given website.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int|array<int> $site_ids The site IDs to clear.
	 * @param int|null       $network_id The network ID.
	 * @param bool           $expire Expire cache if set to true, or delete by default.
	 * @return void
	 */
	public static function clear_cache_by_site_ids( $site_ids = null, ?int $network_id = null, bool $expire = false ): void {
		self::get_clearing_handler()->clear_by_site_ids( $site_ids, $network_id, $expire );
	}

	/**
	 * Clear the full cache of each site in a given network.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int|null $network_id The network ID.
	 * @param bool     $expire Expire cache.
	 * @return void
	 */
	public static function clear_cache_by_network_id( ?int $network_id = null, bool $expire = false ): void {
		self::get_clearing_handler()->clear_by_network_id( $network_id, $expire );
	}

	/**
	 * Clear cache of each site in each network
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param bool $expire Expire cache if set to true, or delete by default.
	 * @return void
	 */
	public static function clear_cache( bool $expire = false ): void {
		self::get_clearing_handler()->clear_all( $expire );
	}

	/**
	 * If the site is a Multisite network
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool If the site is a Multisite network.
	 */
	public static function is_multisite(): bool {
		return self::$multisite->is_enabled();
	}

	/**
	 * Get all available site ids of a given network
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $network_id The network ID.
	 * @return array<int> The site IDs.
	 */
	public static function get_site_ids( int $network_id = 1 ): array {
		return self::$multisite->get_site_ids( $network_id );
	}

	/**
	 * Get all available network ids
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array<int>
	 */
	public static function get_network_ids(): array {
		return self::$multisite->get_network_ids();
	}

	/**
	 * Get the flag prefix with network and site namespace.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int|string|null $site_id The site ID.
	 * @param int|string|null $network_id The network ID.
	 *
	 * @return string The flag prefix.
	 */
	public static function get_flag_prefix( $site_id = null, $network_id = null ): string {
		return self::get_flag_manager()->get_prefix( $site_id, $network_id );
	}

	/**
	 * Prefix the flags with the current site and network ID.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array<string>|string $flags The flags to prefix.
	 * @param int|string|null      $site_id The site ID.
	 * @param int|string|null      $network_id The network ID.
	 *
	 * @return array<string> The prefixed flags.
	 */
	public static function prefix_flags( $flags = array(), $site_id = null, $network_id = null ): array {
		return self::get_flag_manager()->prefix( $flags, $site_id, $network_id );
	}

	/**
	 * Get the flag key.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string          $flag The flag name.
	 * @param int|string|null $site_id The site ID.
	 * @param int|string|null $network_id The network ID.
	 *
	 * @return string The flag key.
	 */
	public static function get_flag_key( string $flag, $site_id = null, $network_id = null ): string {
		return self::get_flag_manager()->get_key( $flag, $site_id, $network_id );
	}

	/**
	 * Add a flag to this request.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $flag Keep these short and unique, don't overuse.
	 */
	public static function add_flag( string $flag ): void {
		self::get_flag_manager()->add( $flag );
	}

	/**
	 * Remove a flag from the current request.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $flag The flag to remove.
	 */
	public static function remove_flag( string $flag ): void {
		self::get_flag_manager()->remove( $flag );
	}

	/**
	 * Set TTL for the current request.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $seconds TTL in seconds.
	 */
	public static function set_ttl( int $seconds ): void {
		self::$ttl_override = $seconds;
		self::$ttl_overridden = true;
	}

	/**
	 * Set grace period for the current request.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $seconds Grace period in seconds.
	 */
	public static function set_grace( int $seconds ): void {
		self::$grace_override = $seconds;
		self::$grace_overridden = true;
	}

	/**
	 * Override cache decision from rules.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param bool   $should_cache Whether to cache this request.
	 * @param string $reason       Reason for the decision.
	 */
	public static function set_cache_decision( bool $should_cache, string $reason = '' ): void {
		self::$cache_decision = array(
			'decision' => $should_cache,
			'reason'   => $reason,
		);
	}

	/**
	 * Get the cache decision if set by rules.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array{decision: bool, reason: string}|null Array with 'decision' and 'reason', or null if not set.
	 */
	public static function get_cache_decision(): ?array {
		return self::$cache_decision;
	}

	/**
	 * Check cache decision and set appropriate headers.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return bool True if caching should proceed, false to bypass.
	 */
	private static function check_cache_decision(): bool {
		$decision = self::get_cache_decision();

		if ( $decision && ! $decision['decision'] ) {
			self::set_header( 'Status', 'bypass' );
			self::set_reason( $decision['reason'] );
			return false;
		}

		return true;
	}

	/**
	 * Set HTTP header.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $key The header key.
	 * @param string $value The header value.
	 */
	private static function set_header( string $key, string $value ): void {
		if ( ! headers_sent() ) {
			header( "X-MilliCache-$key: $value" );
		}
	}

	/**
	 * Set HTTP reason header for debugging.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $value The reason header value.
	 * @return void
	 */
	private static function set_reason( string $value ): void {
		if ( self::get_config()->debug && ! empty( $value ) ) {
			self::set_header( 'Reason', $value );
		}
	}

	/**
	 * Whether we can regenerate the request in the background.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return bool
	 */
	private static function can_fcgi_regenerate(): bool {
		return function_exists( 'fastcgi_finish_request' );
	}


	/**
	 * Get meaningful Cache config and info.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param bool $network If the network is set to true, get the network cache status.
	 *
	 * @return array<mixed> The Cache status.
	 */
	public static function get_status( bool $network = false ): array {
		$config = self::get_config();

		$cache = array(
			'ttl' => $config->ttl,
			'grace' => $config->grace,
			'gzip' => $config->gzip,
			'debug' => $config->debug,
			'nocache_paths' => $config->nocache_paths,
			'ignore_cookies' => $config->ignore_cookies,
			'nocache_cookies' => $config->nocache_cookies,
			'ignore_request_keys' => $config->ignore_request_keys,
		);

		return array_merge( $cache, Admin::get_cache_size( $network ? self::get_flag_key( 'site', '*' ) : self::get_flag_key( '*' ), true ) );
	}

	/**
	 * Initialize autoloader for MilliCache classes.
	 *
	 * Only loads Composer autoloader once, and only when needed.
	 * Includes fallback PSR-4 autoloader if Composer is not available.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private static function init_autoloader(): void {
		if ( self::$autoloader_initialized ) {
			return;
		}

		// Attempt to load Composer autoloader.
		$autoloader = dirname( __DIR__ ) . '/vendor/autoload.php';
		if ( file_exists( $autoloader ) ) {
			require_once $autoloader;
		} else {
			// Fallback: Register a simple PSR-4 autoloader.
			spl_autoload_register(
				function ( $class ) {
					if ( strpos( $class, 'MilliCache\\' ) === 0 ) {
						$file = __DIR__ . '/' . str_replace( array( 'MilliCache\\', '\\' ), array( '', '/' ), $class ) . '.php';
						if ( file_exists( $file ) ) {
							require_once $file;
						}
					}
				}
			);
		}

		self::$autoloader_initialized = true;
	}
}
