<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.milli.press
 * @since      1.0.0
 *
 * @package    Millicache
 * @subpackage Millicache/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
 * @package    Millicache
 * @subpackage Millicache/includes
 * @author     Philipp Wellmer <hello@milli.press>
 */
final class Millicache {

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
	 * The Millicache engine.
	 *
	 * @since    1.0.0
	 * @access   protected
	 *
	 * @var      Millicache_Engine
	 */
	protected $engine;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 *
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 *
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

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
	 * - Millicache_Loader. Orchestrates the hooks of the plugin.
	 * - Millicache_Engine. The Millicache engine.
	 * - Millicache_Admin. Defines all hooks & methods for the admin area.
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

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-millicache-loader.php';

		/**
		 * The class responsible for the Millicache engine.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-millicache-engine.php';

		/**
		 * The class responsible for defining all actions for the CLI.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-millicache-cli.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-millicache-admin.php';

		$this->loader = new Millicache_Loader();
		$this->engine = new Millicache_Engine();

		new Millicache_CLI( $this->get_loader(), $this->get_plugin_name(), $this->version );
		new Millicache_Admin( $this->get_loader(), $this->get_plugin_name(), $this->version );
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

		// Register hooks that clear full site cache.
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
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since    1.0.0
	 * @access  public
	 *
	 * @return    Millicache_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @access   public
	 *
	 * @return    Millicache_Engine The Millicache engine.
	 */
	public function get_engine() {
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
	public function get_version() {
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
		$blog_id = get_current_blog_id();
		$network_id = get_current_network_id();

		$this->engine->add_flag( "site:{$network_id}:{$blog_id}" );

		if ( is_singular() ) {
			$post_id = get_queried_object_id();
			$this->engine->add_flag( "post:{$blog_id}:{$post_id}" );
		}

		if ( is_front_page() || is_home() ) {
			$this->engine->add_flag( "home:{$blog_id}" );
		}

		if ( is_feed() ) {
			$this->engine->add_flag( "feed:{$blog_id}" );
		}

		if ( is_archive() ) {
			if ( is_post_type_archive() ) {
				$post_type = get_query_var( 'post_type' );
				$this->engine->add_flag( "archive:{$blog_id}:{$post_type}" );
			} elseif ( is_author() ) {
				$author_id = get_query_var( 'author' );
				$this->engine->add_flag( "author:{$blog_id}:{$author_id}" );
			}
		}

		/**
		 * Filter to add additional cache flags.
		 * Note: Don't use this too excessive, as it will increase the cache size.
		 *
		 * @since 1.0.0
		 * @param array $add_flags The additional flags.
		 */
		$additional_flags = apply_filters( 'millicache_add_flags', array() );
		if ( is_array( $additional_flags ) && ! empty( $additional_flags ) ) {
			foreach ( $additional_flags as $flag ) {
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
	 * @param int $post_id The post ID.
	 * @return void
	 */
	public function clean_post_cache( $post_id ) {
		$post = get_post( $post_id );
		if ( $post && 'publish' === $post->post_status ) {
			$blog_id = get_current_blog_id();
			$this->engine->clear_cache_by_flags(
				array(
					"post:{$blog_id}:{$post->ID}",
					"author:{$blog_id}:{$post->post_author}",
					"archive:{$blog_id}:{$post->post_type}",
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
	 * @param string  $new_status The new post status.
	 * @param string  $old_status The old post status.
	 * @param WP_Post $post The post object.
	 * @return void
	 */
	public function transition_post_status( $new_status, $old_status, $post ) {
		if ( 'publish' === $new_status || 'publish' === $old_status ) {
			$this->engine->clear_cache_by_post_ids( $post->ID );
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
	function cleanup_expired_flags() {
		$millicache_redis = new Millicache_Redis();
		$millicache_redis->cleanup_expired_flags();
	}

	/**
	 * Hooks that clear the full site cache.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array The hooks & priority that clear the full cache of the site.
	 */
	public function get_clear_site_cache_hooks() {

		/**
		 * Filter the hooks that clear the full cache.
		 *
		 * @since 1.0.0
		 *
		 * @param array $hooks The hooks & priority that clear the full cache.
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
	public function register_clear_site_cache_options( $option, $old_value, $value ) {

		/**
		 * Filter the options that clear the full cache on change.
		 * Credit: Cache Enabler Plugin
		 *
		 * @since 1.0.0
		 *
		 * @param array $hooks The hooks & priority that clear the full cache.
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
				$this->engine->clear_cache_by_post_ids( array( $old_value, $value ) );
			} else {
				$this->engine->clear_cache_by_site_ids();
			}
		}
	}
}
