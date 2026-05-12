<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/admin
 */

namespace MilliCache\Admin;

use MilliCache\Core\Loader;
use MilliCache\Engine;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    MilliCache
 * @subpackage MilliCache/admin
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Admin {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 *
	 * @var      Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected Loader $loader;

	/**
	 * The Engine instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      Engine    $engine    The Engine instance.
	 */
	private Engine $engine;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	private string $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      string    $version    The current version of the plugin.
	 */
	private string $version;

	/**
	 * The notices to display in the admin area.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @var      array<array{message: string, type: string}> $notices The notices to display in the admin area.
	 */
	public static array $notices = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param    Loader $loader      The loader class.
	 * @param    Engine $engine      The Engine instance.
	 * @param    string $plugin_name The plugin slug.
	 * @param    string $version     The plugin version.
	 */
	public function __construct( Loader $loader, Engine $engine, string $plugin_name, string $version ) {
		$this->loader      = $loader;
		$this->engine      = $engine;
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->define_admin_hooks();
		$this->load_dependencies();
	}

	/**
	 * Load all the dependencies for the admin area.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	private function load_dependencies() {
		new Adminbar( $this->loader, $this->engine );
		new UI( $this->engine, $this->plugin_name, $this->version );
	}

	/**
	 * Register all the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	private function define_admin_hooks() {
		// Scripts & Styles.
		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_admin_assets' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_settings_assets' );

		// Text Domain.
		$this->loader->add_action( 'plugins_loaded', $this, 'load_plugin_textdomain' );
		$this->loader->add_action( 'admin_init', $this, 'undefined_cache_notice' );

		// Notices.
		$this->loader->add_action( 'millicache_admin_notice', $this, 'add_notice', 10, 2 );
		$this->loader->add_action( is_network_admin() ? 'network_admin_notices' : 'admin_notices', $this, 'display_notices' );

		// Cache Size.
		$this->loader->add_filter( 'dashboard_glance_items', $this, 'add_dashboard_glance_cache_size', 999 );
		$this->loader->add_action( 'millicache_cache_storing', $this, 'delete_cache_size_transient' );
		$this->loader->add_action( 'millicache_cache_deleted', $this, 'delete_cache_size_transient' );
	}

	/**
	 * Add a notice to the admin area.
	 *
	 * Can be called directly or triggered via:
	 *     do_action( 'millicache_admin_notice', 'Saved.', 'success' );
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param    string $message The message to display.
	 * @param    string $type    The type of notice to display.
	 * @return   void
	 */
	public static function add_notice( string $message, string $type = 'info' ): void {
		self::$notices[] = array(
			'message' => $message,
			'type'    => $type,
		);

		// Store the notice transient for 15 seconds.
		set_transient( 'millicache_admin_notices', self::$notices, 15 );
	}

	/**
	 * Display all registered notices in the admin area.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public function display_notices(): void {
		// Check if there are any saved notices in a transient.
		$saved_notices = get_transient( 'millicache_admin_notices' );

		// If there are saved notices, merge them with the current notices.
		if ( $saved_notices ) {
			// Push the saved notices to the current notices array.
			array_push( self::$notices, ...(array) $saved_notices );
			// Delete the transient as we don't need it anymore.
			delete_transient( 'millicache_admin_notices' );
		}

		foreach ( array_unique( self::$notices, SORT_REGULAR ) as $notice ) {
			printf(
				'<div class="notice notice-%s is-dismissible"><p><b>Page Cache: </b>%s</p></div>',
				esc_attr( $notice['type'] ),
				wp_kses(
					$notice['message'],
					array(
						'a'      => array(
							'href'   => array(),
							'target' => array(),
							'rel'    => array(),
						),
						'strong' => array(),
						'em'     => array(),
						'code'   => array(),
						'br'     => array(),
					)
				)
			);
		}
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public function load_plugin_textdomain(): void {
		load_plugin_textdomain(
			'millicache',
			false,
			MILLICACHE_DIR . '/languages/'
		);
	}

	/**
	 * Register the stylesheets & JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public function enqueue_admin_assets() {
		Utils::enqueue_assets( 'admin' );
	}

	/**
	 * Register the stylesheets & JavaScript for the settings page.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param    string $admin_page The current admin page.
	 *
	 * @return   void
	 */
	public function enqueue_settings_assets( string $admin_page ) {
		if ( false === strpos( $admin_page, 'page_millicache' ) ) {
			return;
		}

		// Enqueue custom MilliCache components.
		Utils::enqueue_assets( 'settings', array( 'millibase' ) );
	}

	/**
	 * Add a notice if WP_CACHE is not defined or set to false.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public function undefined_cache_notice(): void {
		if ( defined( 'WP_CACHE' ) && ! WP_CACHE ) {
			self::add_notice(
				__( 'The constant WP_CACHE is either not defined or set to false in your wp-config.php. Please add "define( \'WP_CACHE\', true );" to activate MilliCache caching.', 'millicache' ),
				'warning'
			);
		}
	}

	/**
	 * Add a glance item for the current site to the dashboard.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return  void
	 */
	public function add_dashboard_glance_cache_size(): void {
		printf(
			'<li class="cache-count">%s</li>',
			current_user_can( 'manage_options' )
				? sprintf(
					'<a title="%s" href="%s">%s</a>',
					esc_attr__( 'Cache Settings', 'millicache' ),
					esc_url( admin_url( 'options-general.php?page=millicache' ) ),
					esc_html( Utils::get_cache_size_summary_string() )
				)
				: sprintf(
					'<span>%s</span>',
					esc_html( Utils::get_cache_size_summary_string() )
				)
		);
	}

	/**
	 * Reset the cache size in the database.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public function delete_cache_size_transient(): void {
		// Delete single-site cache size transient.
		delete_site_transient( 'millicache_sizes_' . $this->engine->flags()->get_prefix() . '*' );

		if ( $this->engine->multisite()->is_enabled() ) {
			// Delete network-wide cache size transient.
			delete_site_transient( 'millicache_sizes_' . $this->engine->flags()->get_prefix( '*' ) . '*' );
		}
	}
}
