<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache;

use MilliCache\Admin\Admin;
use MilliCache\Admin\CLI;
use MilliCache\Core\Loader;

! defined( 'ABSPATH' ) && exit;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class MilliCache {

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
	 * The MilliCache engine.
	 *
	 * @since    1.0.0
	 * @access   protected
	 *
	 * @var      Engine
	 */
	protected Engine $engine;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 *
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected string $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 *
	 * @var      string    $version    The current version of the plugin.
	 */
	protected string $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public function __construct() {
		if ( defined( 'MILLICACHE_VERSION' ) ) {
			$this->version = MILLICACHE_VERSION;
		} else {
			$this->version = '1.0.0';
		}

		$this->plugin_name = 'millicache';

		$this->load_dependencies();
		$this->define_cache_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Loader. Orchestrates the hooks of the plugin.
	 * - Engine. The MilliCache engine.
	 * - Admin. Defines all hooks & methods for the admin area.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	private function load_dependencies() {
		$this->loader = new Loader();
		$this->engine = Engine::instance();

		new CLI( $this->loader, $this->engine, $this->plugin_name, $this->version );
		new Admin( $this->loader, $this->engine, $this->plugin_name, $this->version );
	}

	/**
	 * Register all the hooks related to the cache functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	private function define_cache_hooks() {
		// Fundamental cache clearing hooks.
		$this->loader->add_action( 'clean_post_cache', $this, 'clear_post_cache' );
		$this->loader->add_action( 'before_delete_post', $this, 'clear_post_cache' );
		$this->loader->add_action( 'transition_post_status', $this, 'transition_post_status', 10, 3 );

		// Register options that clear the full site cache.
		$this->loader->add_action( 'updated_option', $this, 'register_clear_site_cache_options', 10, 3 );

		// Register hooks that clear the full site cache.
		foreach ( $this->get_clear_site_cache_hooks() as $hook => $priority ) {
			$this->loader->add_action( $hook, $this->engine->clear(), 'sites', $priority, 0 );
		}

		// Cron events.
		$this->loader->add_action( 'millipress_nightly', $this, 'cleanup_expired_flags' );
	}

	/**
	 * Run the loader to execute all the hooks with WordPress.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @access    public
	 *
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name(): string {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since    1.0.0
	 * @access  public
	 *
	 * @return    Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader(): Loader {
		return $this->loader;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @access   public
	 *
	 * @return    Engine The MilliCache engine.
	 */
	public function get_engine(): Engine {
		return $this->engine;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @access    public
	 *
	 * @return    string    The version number of the plugin.
	 */
	public function get_version(): string {
		return $this->version;
	}

	/**
	 * Get related flags for a post.
	 *
	 * This method generates cache flags IDs related to a specific post, including
	 * singular views, archives, author archives, taxonomy term archives, and date archives.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param \WP_Post|null $post The post-object.
	 *
	 * @return array<string> An array of related cache flags for the post.
	 */
	public static function get_post_related_flags( ?\WP_Post $post ): array {
		if ( ! $post ) {
			return array();
		}

		$flags = array();

		$post_id   = $post->ID;
		$post_type = $post->post_type;

		if ( $post_id && $post_type ) {
			// Singular post.
			$flags[] = "post:$post_id";

			// Post-Type archive.
			$flags[] = "archive:$post_type";

			// Author archive.
			if ( $post->post_author ) {
				$flags[] = 'archive:author:' . (int) $post->post_author;
			}

			// Taxonomy term archives.
			$taxonomies = get_object_taxonomies( $post_type );
			foreach ( $taxonomies as $taxonomy ) {
				$terms = get_the_terms( $post, $taxonomy );
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					foreach ( $terms as $term ) {
						$flags[] = "archive:{$taxonomy}:{$term->term_id}";
					}
				}
			}

			// Date archives.
			$timestamp = strtotime( $post->post_date );
			if ( $timestamp ) {
				$year  = gmdate( 'Y', $timestamp );
				$month = gmdate( 'm', $timestamp );
				$day   = gmdate( 'd', $timestamp );

				$flags[] = "archive:$year";
				$flags[] = "archive:$year:$month";
				$flags[] = "archive:$year:$month:$day";
			}
		}

		/**
		 * Filters the list of cache flags that are considered related to a specific post.
		 *
		 * This hook is used during invalidation (e.g., when a post is updated or deleted) to determine which cache entries should be cleared.
		 * The returned flags are not saved, only used for lookups during cache clearing operations.
		 *
		 * @since 1.0.0
		 *
		 * @param array    $flags The generated cache flags.
		 * @param \WP_Post  $post The post-object.
		 */
		return array_unique( apply_filters( 'millicache_flags_related_to_post', $flags, $post ) );
	}

	/**
	 * Clear post-cache when post is updated, comment count changes, etc.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param \WP_Post|int $post $post The post-ID or object.
	 *
	 * @return void
	 */
	public function clear_post_cache( $post ): void {
		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}

		if ( ! $post instanceof \WP_Post ) {
			return;
		}

		if ( ! in_array( $post->post_status, array( 'publish', 'trash' ), true ) ) {
			return;
		}

		$this->engine->clear()->flags( $this->get_post_related_flags( $post ) );
	}

	/**
	 * Clear URL cache on transition of newly published posts.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string   $new_status The new post-status.
	 * @param string   $old_status The old post-status.
	 * @param \WP_Post $post The post-object.
	 *
	 * @return void
	 */
	public function transition_post_status( string $new_status, string $old_status, \WP_Post $post ) {
		if ( 'publish' === $new_status && 'publish' !== $old_status ) {
			// Clear URL cache for any existing entry.
			$this->engine->clear()->urls( (string) get_permalink( $post->ID ) );

			// Clear the cache for related archives, author, taxonomies, etc.
			$this->engine->clear()->flags( $this->get_post_related_flags( $post ) );
		}
	}

	/**
	 * Hooks that clear the full site cache.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array<string, int> The hooks and priority that clear the full cache of the site.
	 */
	public function get_clear_site_cache_hooks(): array {

		/**
		 * Filter the hooks that clear the full cache.
		 *
		 * @since 1.0.0
		 *
		 * @hook millicache_settings_clear_site_hooks - Filter for hooks that clear site cache.
		 * @param array<string, int> $hooks The hooks and priority that clear the full cache.
		 */
		return apply_filters(
			'millicache_settings_clear_site_hooks',
			array(
				'save_post_wp_template_part' => 10,
				'save_post_wp_global_styles' => 10,
				'customize_save_after' => 10,
				'wp_update_nav_menu' => 10,
				'switch_theme' => 10,
			)
		);
	}

	/**
	 * Register options to clear the full site cache.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $option The option name.
	 * @param mixed  $old_value The old option value.
	 * @param mixed  $value The new option value.
	 * @return void
	 */
	public function register_clear_site_cache_options( string $option, $old_value, $value ): void {

		/**
		 * Filter the options that clear the full cache on change.
		 * Credit: Cache Enabler MilliCache
		 *
		 * @since 1.0.0
		 *
		 * @param array $hooks The hooks and priority that clear the full cache.
		 */
		$options = apply_filters(
			'millicache_settings_clear_site_options',
			array(
				// wp-admin/options-general.php.
				'blogname',
				'blogdescription',
				'WPLANG',
				'timezone_string',
				'gmt_offset',
				'date_format',
				'time_format',
				'start_of_week',

				// wp-admin/options-reading.php.
				'page_on_front',
				'page_for_posts',
				'posts_per_page',
				'blog_public',

				// wp-admin/options-discussion.php.
				'require_name_email',
				'comment_registration',
				'close_comments_for_old_posts',
				'show_comments_cookies_opt_in',
				'thread_comments',
				'thread_comments_depth',
				'page_comments',
				'comments_per_page',
				'default_comments_page',
				'comment_order',
				'show_avatars',
				'avatar_rating',
				'avatar_default',

				// wp-admin/options-permalink.php.
				'permalink_structure',
				'category_base',
				'tag_base',

				// wp-admin/themes.php.
				'template',
				'stylesheet',

				// wp-admin/widgets.php.
				'sidebars_widgets',
				'widget_*',

				// wp-admin/customize.php.
				'site_icon',
			)
		);

		if ( in_array( $option, $options, true ) ) {
			if ( 'page_on_front' === $option || 'page_for_posts' === $option ) {
				$this->engine->clear()->flags( array( 'home', 'archive:post' ) );

				if ( is_numeric( $old_value ) && is_numeric( $value ) ) {
					$this->engine->clear()->posts( array( (int) $old_value, (int) $value ) );
				}
			} else {
				$this->engine->clear()->sites();
			}
		}
	}

	/**
	 * Cleanup expired flags.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function cleanup_expired_flags() {
		Engine::instance()->storage()->cleanup_expired_flags();
	}

	/**
	 * Get the capability required for cache clearing.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The capability required to clear the cache.
	 */
	public static function get_clear_cache_capability(): string {

		/**
		 * Filters the capability required to clear the cache.
		 *
		 * @since 1.0.0
		 *
		 * @param string $capability The capability required to clear the cache. Default 'publish_pages'.
		 */
		return apply_filters( 'millicache_clear_cache_capability', 'publish_pages' );
	}
}
