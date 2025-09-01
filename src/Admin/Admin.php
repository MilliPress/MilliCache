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
class Admin {

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
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private string $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      string    $version    The current version of this plugin.
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
	 * @param    Loader $loader            The loader class.
	 * @param    string $plugin_name       The name of this plugin.
	 * @param    string $version           The version of this plugin.
	 */
	public function __construct( Loader $loader, string $plugin_name, string $version ) {

		$this->loader = $loader;
		$this->plugin_name = $plugin_name;
		$this->version = $version;

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
		new Adminbar( $this->loader );
		new RestAPI( $this->loader, $this->plugin_name, $this->version );
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
		$this->loader->add_action( 'admin_menu', $this, 'add_admin_menu' );

		// Scripts & Styles.
		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_admin_assets' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_settings_assets' );

		// Text Domain.
		$this->loader->add_action( 'plugins_loaded', $this, 'load_plugin_textdomain' );
		$this->loader->add_action( 'admin_init', $this, 'undefined_cache_notice' );

		// Notices.
		$this->loader->add_action( is_network_admin() ? 'network_admin_notices' : 'admin_notices', $this, 'display_notices' );

		// Cache Size.
		$this->loader->add_filter( 'dashboard_glance_items', $this, 'add_dashboard_glance_cache_size', 999 );
		$this->loader->add_action( 'millicache_before_page_cache_stored', $this, 'delete_cache_size_transient' );
		$this->loader->add_action( 'millicache_after_page_cache_deleted', $this, 'delete_cache_size_transient' );
	}

	/**
	 * Add the admin menu item for the plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public function add_admin_menu(): void {
		add_options_page(
			__( 'MilliCache', 'millicache' ),
			__( 'MilliCache', 'millicache' ),
			'manage_options',
			'millicache',
			function () {
				echo '<div class="wrap" id="millicache-settings"></div>';
			},
		);
	}

	/**
	 * Add a notice to the admin area.
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
				esc_html( $notice['message'] )
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
	 * Register the stylesheets for the admin area.
	 * Register the stylesheets & JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public function enqueue_admin_assets() {
		self::enqueue_assets( 'admin' );
	}

	/**
	 * Register the JavaScript for the admin area.
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
		if ( 'settings_page_millicache' !== $admin_page ) {
			return;
		}

		// Enqueue the settings assets.
		self::enqueue_assets( 'settings', array( 'wp-api-fetch' ) );

		// Enqueue the WordPress components.
		wp_enqueue_style( 'wp-components' );
	}

	/**
	 * Helper method to enqueue assets.
	 *
	 * @since    1.0.0
	 * @access   public static
	 *
	 * @param string        $asset_name      The asset name without extension.
	 * @param array<string> $js_deps         An array of JavaScript dependencies to include.
	 * @param array<string> $css_deps        An array of CSS dependencies to include.
	 *
	 * @return bool True if assets were successfully enqueued, false otherwise.
	 */
	public static function enqueue_assets( string $asset_name, array $js_deps = array(), array $css_deps = array() ): bool {
		if ( ! defined( 'MILLICACHE_BASENAME' ) ) {
			return false;
		}

		$asset_file = plugin_dir_path( WP_PLUGIN_DIR . '/' . MILLICACHE_BASENAME ) . '/build/' . $asset_name . '.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return false;
		}

		$asset = include $asset_file;

		// Enqueue the styles.
		wp_enqueue_style(
			"millicache-{$asset_name}",
			plugins_url( 'build/' . $asset_name . '.css', MILLICACHE_BASENAME ),
			$css_deps,
			$asset['version']
		);

		// Enqueue the script.
		wp_enqueue_script(
			"millicache-{$asset_name}",
			plugins_url( 'build/' . $asset_name . '.js', MILLICACHE_BASENAME ),
			array_merge( $asset['dependencies'], $js_deps ),
			$asset['version'],
			array( 'in_footer' => true )
		);

		return true;
	}

	/**
	 * Add a notice to the admin area if the cache is not defined.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	public static function undefined_cache_notice(): void {
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
	public static function add_dashboard_glance_cache_size(): void {
		printf(
			'<li class="cache-count"><a title="%s" href="%s">%s</a></li>',
			esc_attr__( 'Cache Settings', 'millicache' ),
			esc_url( admin_url( 'options-general.php?page=millicache' ) ),
			esc_html( self::get_cache_size_summary_string() )
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
	public static function delete_dashboard_glance_cache_size(): void {
		$site_id = get_current_network_id();
		$blog_id = get_current_blog_id();
		delete_transient( "millicache_size_site:$site_id:$blog_id" );
	}

	/**
	 * Get a summary string for the cache size.
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @param ?array{index: int, size: int, size_human: string} $size The size of the cache.
	 * @return string The summary string.
	 */
	public static function get_cache_size_summary_string( ?array $size = null ): string {
		if ( ! $size ) {
			$size = self::get_cache_size( Engine::get_flag_prefix( is_network_admin() ? '*' : null ) . '*' );
		}

		if ( $size['size'] > 0 ) {
			return sprintf(
				// translators: %1$s is the number of pages, %2$s is singular or plural "page", %3$s is the cache size, %4$s is the cache size unit.
				__( '%1$s %2$s (%3$s) cached', 'millicache' ),
				$size['index'],
				_n( 'page', 'pages', $size['index'], 'millicache' ),
				$size['size_human'],
			);
		} else {
			return __( 'Empty cache', 'millicache' );
		}
	}

	/**
	 * Get the size of the cache.
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @param string $flag The flag to search for. Wildcards are allowed.
	 * @param bool   $reload Whether to reload the cache size from the storage server.
	 * @return array{index: int, size: int, size_human: string} The index and memory size of the cache.
	 */
	public static function get_cache_size( string $flag = '', bool $reload = false ): array {
		$size = get_transient( 'millicache_size_' . $flag );

		if ( ! is_array( $size ) || $reload ) {
			$storage = Engine::get_storage();
			$size = $storage->get_cache_size( $flag );

			if ( $size ) {
				set_transient( 'millicache_size_' . $flag, $size, DAY_IN_SECONDS );
			}
		}

		return array(
			'index' => $size['index'] ?? 0,
			'size' => $size['size'] ?? 0,
			'size_human' => (string) size_format(
				$size['size'] ?? 0,
				( $size['size'] ?? 0 ) > 1024 ? 2 : 0
			),
		);
	}

	/**
	 * Get the version of a file.
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @param string $file_path The path to the file.
	 * @return null|string The version of the file.
	 */
	public static function get_file_version( string $file_path ): ?string {
		$version = get_file_data( $file_path, array( 'Version' => 'Version' ) );
		return $version['Version'] ?? null;
	}

	/**
	 * Validate the advanced-cache.php file.
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @return array<string, bool|string> The validation information or empty array if the file doesn’t exist.
	 */
	public static function validate_advanced_cache_file(): array {
		$info = array(
			'type' => 'file',
			'custom' => false,
			'outdated' => false,
		);

		$destination = WP_CONTENT_DIR . '/advanced-cache.php';

		if ( is_link( $destination ) ) {
			$info['type'] = 'symlink';
			$destination = readlink( $destination );
		}

		if ( ! file_exists( (string) $destination ) ) {
			return array();
		}

		if ( 'symlink' !== $info['type'] ) {
			$source = dirname( plugin_dir_path( __FILE__ ) ) . '/advanced-cache.php';

			// Compare the file with the plugin version.
			$source_version = self::get_file_version( $source );
			$destination_version = self::get_file_version( (string) $destination );

			// Compare versions.
			if ( $source_version && $destination_version ) {
				if ( version_compare( $source_version, $destination_version ) > 0 ) {
					$info['outdated'] = true;
				}
			}

			// Compare file content.
			$source_content = file_get_contents( $source );
			$destination_content = file_get_contents( (string) $destination );

			if ( $source_content && $destination_content ) {
				$info['custom'] = $source_content !== $destination_content;
			}
		}

		return $info;
	}
}
