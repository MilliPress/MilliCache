<?php
/**
 * The WordPress CLI functionality of the plugin.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin
 */

namespace MilliCache\Admin;

use MilliCache\Admin\CLI\Clear;
use MilliCache\Admin\CLI\Config;
use MilliCache\Admin\CLI\Drop;
use MilliCache\Admin\CLI\Stats;
use MilliCache\Admin\CLI\Status;
use MilliCache\Admin\CLI\StorageCLI;
use MilliCache\Admin\CLI\Test;

! defined( 'ABSPATH' ) && exit;

/**
 * The WordPress CLI functionality of the plugin.
 *
 * Registers CLI commands with lazy-loading - command classes are only
 * instantiated when their specific subcommand is invoked.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class CLI {

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
	 * Initialize the class and set its
	 * properties.
	 *
	 * @since   1.0.0
	 * @access public
	 *
	 * @param string $plugin_name The name of the plugin.
	 *
	 * @return void
	 */
	public function __construct( string $plugin_name ) {
		$this->plugin_name = $plugin_name;

		if ( self::is_cli() ) {
			$this->register_commands();
		}
	}

	/**
	 * Check if the current request is a CLI request.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool
	 */
	public static function is_cli(): bool {
		return defined( 'WP_CLI' ) && WP_CLI && class_exists( '\WP_CLI' );
	}

	/**
	 * Register all CLI commands.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function register_commands(): void {
		\WP_CLI::add_command( "{$this->plugin_name} clear", Clear::class );
		\WP_CLI::add_command( "{$this->plugin_name} config", Config::class );
		\WP_CLI::add_command( "{$this->plugin_name} drop", Drop::class );
		\WP_CLI::add_command( "{$this->plugin_name} stats", Stats::class );
		\WP_CLI::add_command( "{$this->plugin_name} status", Status::class );
		\WP_CLI::add_command( "{$this->plugin_name} cli", StorageCLI::class );
		\WP_CLI::add_command( "{$this->plugin_name} test", Test::class );
	}
}
