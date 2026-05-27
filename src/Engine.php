<?php
/**
 * The independent Cache-Engine to avoid an overhead.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache;

use MilliCache\Base\Site;
use MilliCache\Base\Network;
use MilliCache\Core\Storage;
use MilliRules\MilliRules;
use MilliRules\Rules as RulesRegistry;
use MilliCache\Engine\Cache;
use MilliCache\Engine\Flags;
use MilliCache\Engine\Options;
use MilliCache\Engine\Request;
use MilliCache\Engine\Response;
use MilliCache\Engine\Utilities\Multisite;
use MilliCache\Rules;

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
	 * The singleton instance.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var Engine|null The singleton instance.
	 */
	private static ?Engine $instance = null;

	/**
	 * Cache configuration.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var Cache\Config|null The cache configuration instance.
	 */
	private ?Cache\Config $config;

	/**
	 * The Cache Storage object.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var Storage|null The Cache Storage object.
	 */
	private ?Storage $storage;

	/**
	 * The Multisite helper instance.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var Multisite|null The Multisite helper instance.
	 */
	private ?Multisite $multisite;

	/**
	 * Flags handler.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var Flags|null The flag handler instance.
	 */
	private ?Flags $flags;

	/**
	 * Lazy-loaded header manager instance.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var Response\Headers|null
	 */
	private ?Response\Headers $headers = null;

	/**
	 * State-Options for TTL, Grace-TTL and cache decision.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var Options|null The override manager instance.
	 */
	private ?Options $options = null;

	/**
	 * Rules manager for fluent API access.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var Rules\Manager|null The rules manager instance.
	 */
	private ?Rules\Manager $rules_manager = null;

	/**
	 * Cache handler.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var Cache\Manager|null The cache handler instance.
	 */
	private ?Cache\Manager $cache_manager;

	/**
	 * Clearing handler.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var Cache\Invalidation\Manager|null The clearing handler instance.
	 */
	private ?Cache\Invalidation\Manager $invalidation_manager;

	/**
	 * Request processor.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var Request\Processor|null The request handler instance.
	 */
	private ?Request\Processor $request_processor;

	/**
	 * Response processor.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var Response\Processor|null The response handler instance.
	 */
	private ?Response\Processor $response_processor = null;

	/**
	 * The MilliCache Settings.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array<mixed>
	 */
	private array $settings;

	/**
	 * Whether autoloader has been initialized.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var bool
	 */
	private bool $autoloaded = false;

	/**
	 * Constructor with dependency injection.
	 *
	 * @param Storage|null                    $storage           Storage.
	 * @param Multisite|null                  $multisite         Multisite.
	 * @param Cache\Config|null               $config            Cache configuration.
	 * @param Flags|null                      $flag_manager      Flag Processor.
	 * @param Request\Processor|null          $request_manager   Request Processor.
	 * @param Cache\Manager|null              $cache_manager     Cache Processor.
	 * @param Cache\Invalidation\Manager|null $clearing_manager  Clearing Processor.
	 *
	 * @return void
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct(
		?Storage $storage = null,
		?Multisite $multisite = null,
		?Cache\Config $config = null,
		?Flags $flag_manager = null,
		?Request\Processor $request_manager = null,
		?Cache\Manager $cache_manager = null,
		?Cache\Invalidation\Manager $clearing_manager = null
	) {
		// Initialize autoloader first.
		$this->autoload();

		// Register action namespaces.
		RulesRegistry::register_namespace( 'Actions', 'MilliCache\Rules\Actions\PHP', 'PHP' );
		RulesRegistry::register_namespace( 'Actions', 'MilliCache\Rules\Actions\WP', 'WP' );

		// Resolve settings array.
		$this->settings = $this->load_settings();

		// Cache injected dependencies (for testing).
		$this->storage = $storage;
		$this->multisite = $multisite;
		$this->config = $config;
		$this->flags = $flag_manager;
		$this->request_processor = $request_manager;
		$this->cache_manager = $cache_manager;
		$this->invalidation_manager = $clearing_manager;

		// Store singleton instance.
		self::$instance = $this;
	}

	/**
	 * Get the Engine instance.
	 *
	 * Creates a new instance with default dependencies if one doesn't exist.
	 * For testing with custom dependencies, create an Engine instance directly
	 * via constructor before calling instance().
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Engine The Engine instance.
	 */
	public static function instance(): Engine {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Start the cache engine.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public function start() {
		$this->register_rules();

		// Register the shutdown function to expire/delete cache flags.
		register_shutdown_function( fn() => $this->invalidation()->get_queue()->execute() );

		// Always set the initial header.
		$this->headers()->set_status( 'miss' );

		// Execute PHP rules.
		MilliRules::execute_rules( array( 'PHP' ) );

		// Proceed if the request is cachable.
		if ( $this->check_cache_decision() ) {
			$this->run();
		}
	}

	/**
	 * Run the cache engine.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	private function run() {
		// Clean request and generate hash.
		$hash = $this->request()->process();

		// Create State object.
		$context = Response\State::create( $hash );

		// Get and return cached content (options applied in ResponseManager).
		$context = $this->response()->retrieve_and_serve_cache( $context );

		// Start the output buffer.
		add_action(
			'template_redirect',
			function () use ( $context ) {
				if ( $this->check_cache_decision() ) {
					// Apply any options set by rules.
					$context = $this->options()->apply_to_state( $context );

					// Start the output buffer.
					$this->response()->start_output_buffer( $context );
				}
			},
			PHP_INT_MAX - 10
		);
	}

	/**
	 * Initialize MilliRules and register MilliCache rules.
	 *
	 * All rule registrations happen at this PHP-phase entry point. PHP-typed
	 * rules register directly (PHP package is loaded by {@see MilliRules::init()}).
	 * WP-typed rules are queued as pending by MilliRules (their conditions/actions
	 * reference the not-yet-loaded WP package) and finalize when
	 * {@see MilliRules::load_packages()} fires at plugins_loaded:1.
	 *
	 * Insertion order in the pending queue determines override precedence:
	 * {@see Rules\WordPress} queues its built-ins first; {@see Rules\Custom}
	 * queues settings rules after, so unlocked same-ID overrides win the
	 * last-write race on flush. Locked built-ins still reject overrides.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function register_rules(): void {
		// Initialize MilliRules with the PHP package for early execution.
		MilliRules::init( array( 'PHP' ) );

		// PHP-typed rules register directly. WP-typed rules queue as pending.
		Rules\Bootstrap::register();
		Rules\WordPress::register();
		Rules\Custom::register();
		Rules\RequestFlags::register();

		// Load the WP package once WordPress is up; this flushes the pending
		// queue in insertion order, finalizing every WP-typed rule above.
		add_action(
			'plugins_loaded',
			static function (): void {
				MilliRules::load_packages( array( 'WP' ) );
			},
			1
		);
	}

	/**
	 * Check cache decision and set appropriate headers.
	 *
	 * @since 1.0.0
	 * @return bool True if caching should proceed, false to bypass.
	 */
	public function check_cache_decision(): bool {
		$decision = $this->options()->get_cache_decision();

		if ( ! empty( $decision['reason'] ) ) {
			$this->headers()->set_reason( (string) $decision['reason'] );
		}

		if ( $decision && ! $decision['decision'] ) {
			$this->headers()->set_status( 'bypass' );
			return false;
		}

		return true;
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
	public function get_settings( ?string $module = null ): array {
		if ( $module ) {
			return is_array( $this->settings[ $module ] ) ? $this->settings[ $module ] : array();
		}

		return $this->settings;
	}

	/**
	 * Load the settings tree.
	 *
	 * On multisite, storage lives in its own network-scoped Settings instance.
	 *
	 * @since 1.7.0
	 *
	 * @return array<mixed> The merged settings tree.
	 */
	private function load_settings(): array {
		// Get site settings as the base.
		$settings = Site::settings()->get();

		if ( Multisite::is_enabled() ) {
			// Merge network settings.
			$settings['storage'] = (array) Network::settings()->get( 'storage' );
		}

		return $settings;
	}

	/**
	 * Get a config instance.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return Cache\Config The config instance.
	 */
	public function config(): Cache\Config {
		if ( ! $this->config ) {
			$cache_settings = $this->get_settings( 'cache' );

			// Ensure ignore_cookies array exists.
			if ( ! isset( $cache_settings['ignore_cookies'] ) || ! is_array( $cache_settings['ignore_cookies'] ) ) {
				$cache_settings['ignore_cookies'] = array();
			}

			// Add WordPress test cookie to ignore list.
			$cache_settings['ignore_cookies'][] = defined( 'TEST_COOKIE' ) ? TEST_COOKIE : 'wordpress_test_cookie';

			// Create Cache\Config from settings.
			$this->config = Cache\Config::from_settings( $cache_settings );
		}
		return $this->config;
	}

	/**
	 * Get a storage instance.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return Storage The storage instance.
	 */
	public function storage(): Storage {
		if ( ! $this->storage ) {
			$this->storage = new Storage( $this->get_settings( 'storage' ) );
		}
		return $this->storage;
	}

	/**
	 * Get the shared Multisite helper.
	 *
	 * @since    1.0.0
	 * @since    1.7.0 Made public.
	 * @access   public
	 *
	 * @return   Multisite The multisite instance.
	 */
	public function multisite(): Multisite {
		if ( ! $this->multisite ) {
			$this->multisite = new Multisite();
		}
		return $this->multisite;
	}

	/**
	 * Get a flag manager instance.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Flags The flag manager instance.
	 */
	public function flags(): Flags {
		if ( ! $this->flags ) {
			$this->flags = new Flags( $this->multisite() );
		}
		return $this->flags;
	}

	/**
	 * Get a headers manager instance.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return Response\Headers Response headers instance.
	 */
	private function headers(): Response\Headers {
		if ( ! $this->headers ) {
			$this->headers = new Response\Headers( $this->config() );
		}
		return $this->headers;
	}

	/**
	 * Get a override manager instance.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Options The Options instance.
	 */
	public function options(): Options {
		if ( ! $this->options ) {
			$this->options = new Options();
		}
		return $this->options;
	}

	/**
	 * Get rules manager for fluent API access.
	 *
	 * Provides access to the MilliRules API via a fluent interface.
	 *
	 * Example usage:
	 * ```php
	 * millicache()->rules()->create('my:custom-rule', 'wp')
	 *     ->order(10)
	 *     ->when()
	 *         ->is_singular('post')
	 *     ->then()
	 *         ->set_ttl(7200)
	 *     ->register();
	 * ```
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Rules\Manager The rules manager instance.
	 */
	public function rules(): Rules\Manager {
		if ( ! $this->rules_manager ) {
			$this->rules_manager = new Rules\Manager();
		}
		return $this->rules_manager;
	}

	/**
	 * Get a cache manager instance.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Cache\Manager The cache manager instance.
	 */
	public function cache(): Cache\Manager {
		if ( ! $this->cache_manager ) {
			$this->cache_manager = new Cache\Manager(
				$this->config(),
				$this->storage()
			);
		}
		return $this->cache_manager;
	}

	/**
	 * Get a invalidation manager instance.
	 *
	 * @return Cache\Invalidation\Manager The clearing manager instance.
	 * @since 1.0.0
	 * @access private
	 */
	private function invalidation(): Cache\Invalidation\Manager {
		if ( ! $this->invalidation_manager ) {
			$cache_settings = $this->get_settings( 'cache' );
			$ttl = is_numeric( $cache_settings['ttl'] ?? null ) ? (int) $cache_settings['ttl'] : 3600;

			$this->invalidation_manager = new Cache\Invalidation\Manager(
				$this->storage(),
				$this->request(),
				$this->multisite(),
				$ttl
			);
		}
		return $this->invalidation_manager;
	}

	/**
	 * Get a request manager instance.
	 *
	 * @return Request\Processor The request manager instance.
	 * @since 1.0.0
	 * @access public
	 */
	public function request(): Request\Processor {
		if ( ! $this->request_processor ) {
			$this->request_processor = new Request\Processor( $this->config() );
		}
		return $this->request_processor;
	}

	/**
	 * Get Response Processor instance.
	 *
	 * @return Response\Processor
	 * @since 1.0.0
	 * @access public
	 */
	public function response(): Response\Processor {
		if ( ! $this->response_processor ) {
			$this->response_processor = new Response\Processor(
				$this->config(),
				$this->flags(),
				$this->headers(),
				$this->cache(),
				$this->request()
			);
		}
		return $this->response_processor;
	}

	/**
	 * Get cache clearing interface.
	 *
	 * Provides a fluent API for cache invalidation operations.
	 * Example: Engine::instance()->clear()->by_targets($targets)
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Cache\Invalidation\Manager The cache clearing interface.
	 */
	public function clear(): Cache\Invalidation\Manager {
		return $this->invalidation();
	}

	/**
	 * Initialize autoloader for MilliCache classes.
	 *
	 * Only loads Composer autoloader once, and only when needed.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function autoload(): void {
		if ( $this->autoloaded ) {
			return;
		}

		// <plugin>/src/Engine.php (standalone) or
		// <host>/deps/<vendor>/<pkg>/src/Engine.php (Scoped).
		foreach ( array( 4, 1 ) as $levels ) {
			$autoloader = dirname( __DIR__, $levels ) . '/vendor/autoload.php';
			if ( file_exists( $autoloader ) ) {
				require_once $autoloader;
				break;
			}
		}

		$functions_file = dirname( __DIR__ ) . '/functions.php';
		if ( file_exists( $functions_file ) ) {
			require_once $functions_file;
		}

		$this->autoloaded = true;
	}
}
