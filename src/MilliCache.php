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
 * @subpackage MilliCache/includes
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
 * @subpackage MilliCache/includes
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
		$this->engine = new Engine();

		new CLI( $this->get_loader(), $this->get_plugin_name(), $this->version );
		new Admin( $this->get_loader(), $this->get_plugin_name(), $this->version );
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
		// Caching hooks.
		$this->loader->add_action( 'template_redirect', $this, 'set_cache_flags', 100 );

		// Specific cache clearing hooks.
		$this->loader->add_action( 'clean_post_cache', $this, 'clean_post_cache' );
		$this->loader->add_action( 'transition_post_status', $this, 'transition_post_status', 10, 3 );

		// Register options that clear the full site cache.
		$this->loader->add_action( 'updated_option', $this, 'register_clear_site_cache_options', 10, 3 );

		// Register hooks that clear the full site cache.
		foreach ( $this->get_clear_site_cache_hooks() as $hook => $priority ) {
			$this->loader->add_action( $hook, $this->engine, 'clear_cache_by_site_ids', $priority, 0 );
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
	 * Flag the cache during template_redirect.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function set_cache_flags() {
		// Singular post/page/custom post type.
		if ( is_singular() ) {
			$post_id = get_queried_object_id();
			if ( $post_id ) {
				$this->engine->add_flag( "post:$post_id" );
			}
		}

		// Home and Front Page.
		if ( is_front_page() && is_home() ) {
			$this->engine->add_flag( 'home' );
			$this->engine->add_flag( 'archive:post' );
		} elseif ( is_front_page() ) {
			$this->engine->add_flag( 'home' );
		} elseif ( is_home() ) {
			$this->engine->add_flag( 'archive:post' );
		}

		// Archives.
		if ( is_archive() ) {
			if ( is_post_type_archive() ) {
				$post_types = get_query_var( 'post_type' );
				$post_types = ! is_array( $post_types ) ? array( $post_types ) : $post_types;
				foreach ( $post_types as $post_type ) {
					$this->engine->add_flag( "archive:$post_type" );
				}
			} elseif ( is_category() || is_tag() || is_tax() ) {
				$term = get_queried_object();
				if ( $term && isset( $term->taxonomy, $term->term_id ) ) {
					$this->engine->add_flag( "archive:{$term->taxonomy}:{$term->term_id}" );
				}
			} elseif ( is_author() ) {
				$author_id = get_query_var( 'author' );
				$author_id = is_numeric( $author_id ) ? (int) $author_id : 0;
				if ( $author_id > 0 ) {
					$this->engine->add_flag( "archive:author:$author_id" );
				}
			} elseif ( is_date() ) {
				$flag = '';

				foreach ( array( 'year', 'monthnum', 'day' ) as $key ) {
					$var = get_query_var( $key );
					if ( is_numeric( $var ) && $var > 0 ) {
						$flag .= ":$var";
					}
				}

				if ( ! empty( $flag ) ) {
					$flag = 'archive' . $flag;
					$this->engine->add_flag( $flag );
				}
			}
		}

		// Feeds (e.g., /feed/).
		if ( is_feed() ) {
			$this->engine->add_flag( 'feed' );
		}

		/**
		 * Filter for custom cache flags.
		 * Note: Don't use this too excessively, as it will increase the cache size.
		 *
		 * @since 1.0.0
		 * @param array $custom_flags The custom flags.
		 */
		$custom_flags = apply_filters( 'millicache_custom_flags', array() );
		if ( is_array( $custom_flags ) && ! empty( $custom_flags ) ) {
			foreach ( $custom_flags as $flag ) {
				$this->engine->add_flag( $flag );
			}
		}
	}

	/**
	 * Clear the cache for a post.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $post_id The post-ID.
	 * @return void
	 */
	public function clean_post_cache( int $post_id ): void {
		$post = get_post( $post_id );

		if ( $post && 'publish' === $post->post_status ) {
			$prefix = $this->engine->get_flag_prefix();
			$this->engine->clear_cache_by_flags(
				array(
					$prefix . "post:$post->ID",
					$prefix . "author:$post->post_author",
					$prefix . "archive:$post->post_type",
				)
			);
		}
	}

	/**
	 * Schedule expiry on transition of published posts.
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
		if ( 'publish' === $new_status || 'publish' === $old_status ) {
			$this->engine->clear_cache_by_post_ids( $post->ID );
		}

		if ( 'publish' === $new_status && 'publish' !== $old_status ) {
			$this->engine->clear_cache_by_urls( (string) get_permalink( $post->ID ) );
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
		$storage = Engine::get_storage();
		$storage->cleanup_expired_flags();
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
		 * @param array<string, int> $hooks The hooks and priority that clear the full cache.
		 */
		return apply_filters(
			'millicache_clear_site_hooks',
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
			'millicache_clear_site_options',
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
				$this->engine->clear_cache_by_flags( 'home:' . get_current_blog_id() );

				if ( is_numeric( $old_value ) && is_numeric( $value ) ) {
					$this->engine->clear_cache_by_post_ids( array( (int) $old_value, (int) $value ) );
				}
			} else {
				$this->engine->clear_cache_by_site_ids();
			}
		}
	}
}
