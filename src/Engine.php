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

use MilliCache\Core\Settings;
use MilliCache\Core\Storage;
use MilliCache\Deps\MilliRules\MilliRules;
use MilliCache\Deps\MilliRules\Rules;
use MilliCache\Engine\Cache\Config;
use MilliCache\Engine\Cache\Invalidation\Manager as InvalidationManager;
use MilliCache\Engine\Cache\Manager as CacheManager;
use MilliCache\Engine\Flags;
use MilliCache\Engine\Options;
use MilliCache\Engine\Request\Processor as RequestProcessor;
use MilliCache\Engine\Response\Headers;
use MilliCache\Engine\Response\Processor as ResponseProcessor;
use MilliCache\Engine\Response\State;
use MilliCache\Engine\Utilities\Multisite;
use MilliCache\Rules\Bootstrap as BootstrapRules;
use MilliCache\Rules\Manager as RulesManager;
use MilliCache\Rules\RequestFlags;
use MilliCache\Rules\WordPress as WordPressRules;

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
	 * @var Config|null The cache configuration instance.
	 */
	private ?Config $config;

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
	 * @var Headers|null
	 */
	private ?Headers $headers = null;

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
	 * @var RulesManager|null The rules manager instance.
	 */
	private ?RulesManager $rules_manager = null;

	/**
	 * Cache handler.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var CacheManager|null The cache handler instance.
	 */
	private ?CacheManager $cache_manager;

	/**
	 * Clearing handler.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var InvalidationManager|null The clearing handler instance.
	 */
	private ?InvalidationManager $invalidation_manager;

	/**
	 * Request processor.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var RequestProcessor|null The request handler instance.
	 */
	private ?RequestProcessor $request_processor;

	/**
	 * Response processor.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var ResponseProcessor|null The response handler instance.
	 */
	private ?ResponseProcessor $response_processor = null;

	/**
	 * The MilliCache Settings.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array<mixed> The MilliPress Settings.
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
	 * @param Settings|null            $settings          Settings Processor.
	 * @param Storage|null             $storage           Storage.
	 * @param Multisite|null           $multisite         Multisite.
	 * @param Config|null              $config            Config.
	 * @param Flags|null               $flag_manager      Flag Processor.
	 * @param RequestProcessor|null    $request_manager Request Processor.
	 * @param CacheManager|null        $cache_manager     Cache Processor.
	 * @param InvalidationManager|null $clearing_manager  Clearing Processor.
	 *
	 * @return void
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct(
		?Settings $settings = null,
		?Storage $storage = null,
		?Multisite $multisite = null,
		?Config $config = null,
		?Flags $flag_manager = null,
		?RequestProcessor $request_manager = null,
		?CacheManager $cache_manager = null,
		?InvalidationManager $clearing_manager = null
	) {
		// Initialize autoloader first.
		$this->autoload();

		// Store settings.
		$settings = $settings ?? new Settings();
		$this->settings = $settings->get_settings();

		// Store injected dependencies (for testing).
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
	 * For testing with custom dependencies, create Engine instance directly
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
		$context = State::create( $hash );

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
			200
		);
	}

	/**
	 * Initialize MilliRules and register MilliCache rules.
	 *
	 * Initializes the MilliRules package system with PHP package for early execution,
	 * registers namespaces, and defers WP package loading until WordPress is ready.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function register_rules(): void {
		// Initialize MilliRules with the PHP package for early execution.
		MilliRules::init( array( 'PHP' ) );

		// Register action namespaces for auto-resolution.
		Rules::register_namespace( 'Actions', 'MilliCache\Rules\Actions\PHP', 'PHP' );
		Rules::register_namespace( 'Actions', 'MilliCache\Rules\Actions\WP', 'WP' );

		// Rules that execute before WordPress loads.
		BootstrapRules::register();

		// Defer WP package and rules until WordPress is ready.
		add_action(
			'plugins_loaded',
			function () {
				// Load MilliRules WordPress package.
				MilliRules::load_packages( array( 'WP' ) );

				// Rules that execute after WordPress loaded.
				WordPressRules::register();

				// Register Request Flags rules.
				RequestFlags::register();
			},
			1
		);
	}

	/**
	 * Check cache decision and set appropriate headers.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return bool True if caching should proceed, false to bypass.
	 */
	private function check_cache_decision(): bool {
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
		if ( ! isset( $this->settings ) ) {
			$this->settings = ( new Settings() )->get_settings();
		}

		if ( $module ) {
			return is_array( $this->settings[ $module ] ) ? $this->settings[ $module ] : array();
		}

		return $this->settings;
	}

	/**
	 * Get a config instance.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return Config The config instance.
	 */
	public function config(): Config {
		if ( ! $this->config ) {
			$cache_settings = $this->get_settings( 'cache' );

			// Ensure ignore_cookies array exists.
			if ( ! isset( $cache_settings['ignore_cookies'] ) || ! is_array( $cache_settings['ignore_cookies'] ) ) {
				$cache_settings['ignore_cookies'] = array();
			}

			// Add WordPress test cookie to ignore list.
			$cache_settings['ignore_cookies'][] = defined( 'TEST_COOKIE' ) ? TEST_COOKIE : 'wordpress_test_cookie';

			// Create Config from settings.
			$this->config = Config::from_settings( $cache_settings );
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
	 * Get a multisite instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   Multisite The multisite instance.
	 */
	private function multisite(): Multisite {
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
	 * @return Headers Response headers instance.
	 */
	private function headers(): Headers {
		if ( ! $this->headers ) {
			$this->headers = new Headers( $this->config() );
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
	 * @return RulesManager The rules manager instance.
	 */
	public function rules(): RulesManager {
		if ( ! $this->rules_manager ) {
			$this->rules_manager = new RulesManager();
		}
		return $this->rules_manager;
	}

	/**
	 * Get a cache manager instance.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return CacheManager The cache manager instance.
	 */
	public function cache(): CacheManager {
		if ( ! $this->cache_manager ) {
			$this->cache_manager = new CacheManager(
				$this->config(),
				$this->storage()
			);
		}
		return $this->cache_manager;
	}

	/**
	 * Get a invalidation manager instance.
	 *
	 * @return InvalidationManager The clearing manager instance.
	 * @since 1.0.0
	 * @access private
	 */
	private function invalidation(): InvalidationManager {
		if ( ! $this->invalidation_manager ) {
			$cache_settings = $this->get_settings( 'cache' );
			$ttl = is_numeric( $cache_settings['ttl'] ?? null ) ? (int) $cache_settings['ttl'] : 3600;

			$this->invalidation_manager = new InvalidationManager(
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
	 * @return RequestProcessor The request manager instance.
	 * @since 1.0.0
	 * @access private
	 */
	private function request(): RequestProcessor {
		if ( ! $this->request_processor ) {
			$this->request_processor = new RequestProcessor( $this->config() );
		}
		return $this->request_processor;
	}

	/**
	 * Get Response Processor instance.
	 *
	 * @return ResponseProcessor
	 * @since 1.0.0
	 * @access public
	 */
	public function response(): ResponseProcessor {
		if ( ! $this->response_processor ) {
			$this->response_processor = new ResponseProcessor(
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
	 * @return InvalidationManager The cache clearing interface.
	 */
	public function clear(): InvalidationManager {
		return $this->invalidation();
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
	private function autoload(): void {
		if ( $this->autoloaded ) {
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

		$this->autoloaded = true;
	}
}
