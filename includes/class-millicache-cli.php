<?php
/**
 * The WordPress CLI functionality of the plugin.
 *
 * @link       https://www.milli.press
 * @since      1.0.0
 *
 * @package    Millicache
 * @subpackage Millicache/admin
 */

! defined( 'ABSPATH' ) && exit;

/**
 * The WordPress CLI functionality of the plugin.
 *
 * @package    Millicache
 * @subpackage Millicache/admin
 * @author     Philipp Wellmer <hello@milli.press>
 */
class Millicache_CLI {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 *
	 * @var      Millicache_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected Millicache_Loader $loader;

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
	 * Initialize the class and set its
	 * properties.
	 *
	 * @since   1.0.0
	 * @access public
	 *
	 * @param Millicache_Loader $loader Maintains and registers all hooks for the plugin.
	 * @param string            $plugin_name The name of the plugin.
	 * @param string            $version The version of the plugin.
	 *
	 * @return void
	 */
	public function __construct( Millicache_Loader $loader, string $plugin_name, string $version ) {

		$this->loader = $loader;
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		if ( self::is_cli() ) {
			WP_CLI::add_command( $this->plugin_name, $this );
		}
	}

	/**
	 * Check if the current request is a CLI request.
	 *
	 * @return bool
	 */
	public static function is_cli(): bool {
		return defined( 'WP_CLI' ) && WP_CLI && class_exists( 'WP_CLI' );
	}

	/**
	 * Clear the cache.
	 *
	 * ## OPTIONS
	 *
	 * [--ids=<ids>]
	 * : Comma separated list of post IDs.
	 *
	 * [--urls=<urls>]
	 * : Comma separated list of URLs.
	 *
	 * [--flags=<flags>]
	 * : Comma separated list of flags.
	 *
	 * [--sites=<sites>]
	 * : Comma separated list of site IDs.
	 *
	 * [--networks=<networks>]
	 * : Comma separated list of network IDs.
	 *
	 * [--expire=<expire>]
	 * : Expire the cache. Default is false.
	 *
	 * ## EXAMPLES
	 *
	 *     wp millicache clear --ids=1,2,3
	 *
	 * @when after_wp_load
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array<string> $args The list of arguments.
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	public function clear( array $args, array $assoc_args ): void {
		$assoc_args = wp_parse_args(
			$assoc_args,
			array(
				'ids'       => '',
				'urls'      => '',
				'flags'     => '',
				'sites'     => '',
				'networks'  => '',
				'expire'    => false,
			)
		);

		$engine = new Millicache_Engine();

		$expire = $assoc_args['expire'];

		// Clear full cache if no arguments are given.
		if ( '' === $assoc_args['ids'] && '' === $assoc_args['urls'] && '' === $assoc_args['flags'] && '' === $assoc_args['sites'] && '' === $assoc_args['networks'] ) {
			$engine::clear_cache( $expire );
			WP_CLI::success( is_multisite() ? esc_html__( 'Network cache cleared.', 'millicache' ) : esc_html__( 'Site cache cleared.', 'millicache' ) );
		}

		// Clear network cache.
		if ( '' !== $assoc_args['networks'] ) {
			$network_ids = explode( ',', $assoc_args['networks'] );
			foreach ( $network_ids as $network_id ) {
				$engine::clear_cache_by_network_id( (int) $network_id, $expire );
			}
			WP_CLI::success( esc_html__( 'Network cache cleared.', 'millicache' ) );
		}

		// Clear site cache.
		if ( '' !== $assoc_args['sites'] ) {
			$site_ids = explode( ',', $assoc_args['sites'] );
			foreach ( $site_ids as $site_id ) {
				$engine::clear_cache_by_site_ids( (int) $site_id, null, $expire );
			}
			WP_CLI::success( esc_html__( 'Site cache cleared.', 'millicache' ) );
		}

		// Clear cache by post IDs.
		if ( '' !== $assoc_args['ids'] ) {
			$post_ids = explode( ',', $assoc_args['ids'] );
			foreach ( $post_ids as $post_id ) {
				$engine::clear_cache_by_post_ids( (int) $post_id, $expire );
			}
			WP_CLI::success( esc_html__( 'Post cache cleared.', 'millicache' ) );
		}

		// Clear cache by URLs.
		if ( '' !== $assoc_args['urls'] ) {
			$urls = explode( ',', $assoc_args['urls'] );
			foreach ( $urls as $url ) {
				$engine::clear_cache_by_urls( $url, $expire );
			}
			WP_CLI::success( esc_html__( 'URL cache cleared.', 'millicache' ) );
		}

		// Clear cache by flags.
		if ( '' !== $assoc_args['flags'] ) {
			$flags = explode( ',', $assoc_args['flags'] );
			foreach ( $flags as $flag ) {
				$engine::clear_cache_by_flags( $flag, $expire );
			}
			WP_CLI::success( esc_html__( 'Cache cleared for flags.', 'millicache' ) );
		}
	}

	/**
	 * Get the cache size.
	 *
	 * ## OPTIONS
	 *
	 * [--flag=<flag>]
	 * : The flag to search for. Wildcards are allowed.
	 *
	 * ## EXAMPLES
	 *
	 *     wp millicache stats --flag=site:1:*
	 *
	 * @when after_wp_load
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array<string> $args The list of arguments.
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	public function stats( array $args, array $assoc_args ): void {
		$flag = $assoc_args['flag'] ?? '*';
		$size = Millicache_Admin::get_cache_size( $flag, true );
		WP_CLI::line(
			sprintf(
				// translators: %1$s is the MilliCache version, %2$s is the cache size summary, %3$s is the flag.
				__( 'MilliCache (v%1$s): %2$s for flag "%3$s".', 'millicache' ),
				$this->version,
				Millicache_Admin::get_cache_size_summary_string( $size ),
				$flag
			)
		);
	}
}
