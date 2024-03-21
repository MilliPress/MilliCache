<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.milli.press
 * @since      1.0.0
 *
 * @package    Millicache
 * @subpackage Millicache/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Millicache
 * @subpackage Millicache/admin
 * @author     Philipp Wellmer <hello@milli.press>
 */
class Millicache_Admin {


	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 *
	 * @var      Millicache_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The notices to display in the admin area.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @var      array $notices The notices to display in the admin area.
	 */
	public static $notices = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param    Millicache_Loader $loader The loader class.
	 * @param    string            $plugin_name       The name of this plugin.
	 * @param    string            $version           The version of this plugin.
	 */
	public function __construct( $loader, $plugin_name, $version ) {

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

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-millicache-adminbar.php';

		new Millicache_Adminbar( $this->loader );
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
		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_scripts' );

		// Text Domain.
		$this->loader->add_action( 'plugins_loaded', $this, 'load_plugin_textdomain' );
		$this->loader->add_action( 'admin_init', $this, 'undefined_cache_notice' );

		// Notices.
		$this->loader->add_action( is_network_admin() ? 'network_admin_notices' : 'admin_notices', $this, 'display_notices' );

		// Cache Size.
		$this->loader->add_filter( 'dashboard_glance_items', $this, 'add_dashboard_glance_cache_size', 999 );
		$this->loader->add_action( 'millicache_before_page_cache_stored', $this, 'delete_dashboard_glance_cache_size' );
		$this->loader->add_action( 'millicache_after_page_cache_deleted', $this, 'delete_dashboard_glance_cache_size' );
	}

	/**
	 * Add a notice to the admin area.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param    string $message The message to display.
	 * @param    string $type    The type of notice to display.
	 */
	public static function add_notice( $message, $type = 'info' ) {
		self::$notices[] = array(
			'message' => $message,
			'type'    => $type,
		);
	}

	/**
	 * Display all registered notices in the admin area.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public function display_notices() {
		foreach ( self::$notices as $notice ) {
			printf(
				'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
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
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'millicache',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/millicache-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public function enqueue_scripts() {
		// phpcs:ignore -- While in beta, we don't want to enqueue any scripts.
		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/millicache-admin.js', array(), $this->version, false );
	}

	/**
	 * Add a notice to the admin area if the cache is not defined.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	public static function undefined_cache_notice() {
		if ( defined( 'WP_CACHE' ) && ! WP_CACHE ) {
			self::add_notice(
				__( 'The constant WP_CACHE in your wp-config.php is either not defined or set to false. Please set define( \'WP_CACHE\', true ); in your wp-config.php file to activate MilliCache caching.', 'millicache' ),
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
	public static function add_dashboard_glance_cache_size() {
		$size = self::get_cache_size( 'site:' . get_current_network_id() . ':' . get_current_blog_id() );

		if ( $size ) {
			printf(
				'<li class="cache-count"><a title="%s" href="%s">%s</a></li>',
				esc_attr__( 'Flush the site cache', 'millicache' ),
				esc_url(
					wp_nonce_url(
						add_query_arg( '_millicache', 'flush' ),
						'_millicache__flush_nonce'
					)
				),
				esc_html( self::get_cache_size_summary_string( $size ) )
			);
		}
	}

	/**
	 * Reset the cache size in the database.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public static function delete_dashboard_glance_cache_size() {
		$site_id = get_current_network_id();
		$blog_id = get_current_blog_id();
		delete_transient( "millicache_size_site:{$site_id}:{$blog_id}" );
	}

	/**
	 * Get a summary string for the cache size.
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @param array $size The size of the cache.
	 * @return string The summary string.
	 */
	public static function get_cache_size_summary_string( $size = null ) {
		if ( isset( $size['size'] ) && $size['size'] > 0 ) {
			$unit = $size['size'] > 1024 ? 'mb' : 'kb';
			$size['size'] /= 'mb' == $unit ? 1024 : 1;
			return sprintf(
				// translators: %1$s is the number of pages, %2$s is singular or plural "page", %3$s is the cache size, %4$s is the cache size unit.
				__( '%1$s %2$s (%3$s %4$s) cached', 'millicache' ),
				$size['index'],
				_n( 'page', 'pages', $size['index'], 'millicache' ),
				$size['size'],
				$unit
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
	 * @param bool   $reload Whether to reload the cache size from the Redis server.
	 * @return array The index and memory size of the cache.
	 */
	public static function get_cache_size( $flag = '', $reload = false ) {
		$size = get_transient( 'millicache_size_' . $flag );

		if ( ! $size || $reload ) {
			$redis = new Millicache_Redis();
			set_transient( 'millicache_size_' . $flag, $redis->get_cache_size( $flag ), DAY_IN_SECONDS );
		}

		return $size;
	}

	/**
	 * Get the version of a file.
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @param string $file_path The path to the file.
	 * @return string The version of the file.
	 */
	public static function get_file_version( $file_path ) {
		$version = get_file_data( $file_path, array( 'Version' => 'Version' ) );
		return isset( $version['Version'] ) ? $version['Version'] : null;
	}
}
