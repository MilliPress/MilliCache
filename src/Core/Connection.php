<?php
/**
 * The file that defines the Connection class.
 *
 * @link       https://www.millipress.com
 * @since      1.7.0
 *
 * @package    MilliCache
 * @subpackage Core
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Core;

use MilliBase\Logger;
use MilliBase\Settings as BaseSettings;
use Predis\Autoloader;
use Predis\Client;
use Predis\Connection\ConnectionException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the Predis client from storage settings.
 *
 * The mode is inferred from the shape of `MC_STORAGE_HOST`: a string is `single`,
 * a map with `master` is `replication`, a map with `service` is `sentinel`. An
 * invalid array yields `disabled`. Credentials and timeouts are shared across
 * every node.
 *
 * @since      1.7.0
 * @package    MilliCache
 * @subpackage Core
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Connection {

	/**
	 * The raw storage settings.
	 *
	 * @since 1.7.0
	 * @access private
	 *
	 * @var array<mixed> $settings
	 */
	private array $settings;

	/**
	 * The normalized connection definition.
	 *
	 * @since 1.7.0
	 * @access private
	 *
	 * @var array{mode: string, nodes: array<int, array<string, mixed>>, parameters: array<string, mixed>, service: string, reason: string} $normalized
	 */
	private array $normalized;

	/**
	 * Channel-prefixed logger for connection failures.
	 *
	 * @since 1.7.3
	 * @access private
	 *
	 * @var Logger $logger
	 */
	private Logger $logger;

	/**
	 * Initialize the connection from storage settings.
	 *
	 * @since 1.7.0
	 * @since 1.7.3 Accepts an injected logger.
	 * @access public
	 *
	 * @param array<mixed> $settings The storage settings.
	 * @param Logger|null  $logger   Shared logger instance; defaults to an own "MilliCache" channel.
	 */
	public function __construct( array $settings, ?Logger $logger = null ) {
		$this->logger     = $logger ?? new Logger( 'MilliCache' );
		$this->settings   = $settings;
		$this->normalized = $this->normalize();
	}

	/**
	 * Build the configured Predis client.
	 *
	 * Predis connects lazily, so a runtime {@see ConnectionException} surfaces on
	 * first use (handled at {@see Storage::execute()}), not here. The try/catch
	 * below only guards *synchronous* construction failures, e.g. malformed
	 * replication/sentinel options, so a bad constant can never fatal the request.
	 *
	 * @since 1.7.0
	 * @access public
	 *
	 * @return Client|null The Predis client, or null when disabled or unbuildable.
	 */
	public function client(): ?Client {
		if ( 'disabled' === $this->normalized['mode'] ) {
			return null;
		}

		if ( ! class_exists( '\Predis\Autoloader' ) ) {
			require_once dirname( __DIR__, 2 ) . '/deps/predis/predis/src/Autoloader.php';
		}

		Autoloader::register();

		try {
			if ( 'single' === $this->normalized['mode'] ) {
				return new Client( array_merge( $this->normalized['nodes'][0], $this->normalized['parameters'] ) );
			}

			$options = array( 'parameters' => $this->normalized['parameters'] );

			if ( 'sentinel' === $this->normalized['mode'] ) {
				$options['replication'] = 'sentinel';
				$options['service']     = $this->normalized['service'];
			} else {
				$options['replication'] = 'predis';
			}

			return new Client( $this->normalized['nodes'], $options );
		} catch ( \Throwable $e ) {
			$this->logger->error( 'Unable to build the storage client: ' . $e->getMessage() );
			return null;
		}
	}

	/**
	 * Return a render-safe topology summary for status/CLI/UI surfaces.
	 *
	 * @since 1.7.0
	 * @access public
	 *
	 * @return array<string, mixed> The topology summary.
	 */
	public function describe(): array {
		if ( 'disabled' === $this->normalized['mode'] ) {
			return array(
				'mode'   => 'disabled',
				'reason' => $this->normalized['reason'],
			);
		}

		$shared = $this->normalized['parameters'];

		$summary = array(
			'mode'       => $this->normalized['mode'],
			'database'   => $shared['database'] ?? 0,
			'persistent' => $shared['persistent'] ?? true,
		);

		if ( 'single' === $this->normalized['mode'] ) {
			$node = $this->normalized['nodes'][0];

			$summary['scheme'] = $node['scheme'] ?? 'tcp';
			$summary['host']   = $node['path'] ?? ( $node['host'] ?? '127.0.0.1' );
			$summary['port']   = $node['port'] ?? 6379;

			return $summary;
		}

		if ( 'sentinel' === $this->normalized['mode'] ) {
			$summary['service'] = $this->normalized['service'];
		}

		$summary['nodes'] = array_map(
			function ( array $node ): array {
				$entry = array(
					'scheme' => $node['scheme'] ?? 'tcp',
					'host'   => $node['path'] ?? ( $node['host'] ?? '127.0.0.1' ),
				);

				if ( ! isset( $node['path'] ) ) {
					$entry['port'] = $node['port'] ?? 6379;
				}

				if ( isset( $node['role'] ) ) {
					$entry['role'] = $node['role'];
				}

				return $entry;
			},
			$this->normalized['nodes']
		);

		return $summary;
	}

	/**
	 * Extract a node's display address and role from a {@see self::describe()} entry.
	 *
	 * Centralizes the type-guarded `host:port (role)` extraction shared by the
	 * CLI and Status surfaces; each caller applies its own presentation (plain
	 * text for WP-CLI, escaped `<code>` markup for the admin UI).
	 *
	 * @since 1.7.0
	 * @access public
	 *
	 * @param array<mixed> $node A node entry from {@see self::describe()}.
	 * @return array{address: string, role: string} The `host:port` address and role.
	 */
	public static function node_label( array $node ): array {
		$host = isset( $node['host'] ) && is_string( $node['host'] ) ? $node['host'] : '?';
		$port = isset( $node['port'] ) && is_numeric( $node['port'] ) ? ':' . (int) $node['port'] : '';
		$role = isset( $node['role'] ) && is_string( $node['role'] ) ? $node['role'] : '';

		return array(
			'address' => $host . $port,
			'role'    => $role,
		);
	}

	/**
	 * Normalize the storage settings into a connection definition.
	 *
	 * An ambiguous, incomplete, or unrecognized array yields the `disabled` mode
	 * rather than silently degrading to a single localhost connection.
	 *
	 * @since 1.7.0
	 * @access private
	 *
	 * @return array{mode: string, nodes: array<int, array<string, mixed>>, parameters: array<string, mixed>, service: string, reason: string}
	 */
	private function normalize(): array {
		$parameters = $this->shared_parameters();
		$host       = $this->settings['host'] ?? '127.0.0.1';

		if ( is_array( $host ) ) {
			$has_master  = isset( $host['master'] );
			$has_service = isset( $host['service'] );

			if ( $has_master && $has_service ) {
				return $this->disabled( $parameters, 'MC_STORAGE_HOST has both "master" and "service" keys. Specify exactly one.' );
			}

			// Map with a master: replication, with "replicas" as the read nodes.
			if ( $has_master ) {
				if ( ! is_string( $host['master'] ) && ! is_array( $host['master'] ) ) {
					return $this->disabled( $parameters, 'MC_STORAGE_HOST "master" must be a host string or a node array.' );
				}

				$master         = $this->parse_node( $host['master'] );
				$master['role'] = 'master';

				$nodes = array_merge(
					array( $master ),
					$this->parse_nodes( $this->as_node_list( $host['replicas'] ?? array() ) )
				);

				return array(
					'mode'       => 'replication',
					'nodes'      => $nodes,
					'parameters' => $parameters,
					'service'    => '',
					'reason'     => '',
				);
			}

			// Map with a service: sentinel, with "sentinels" as the sentinel nodes.
			if ( $has_service ) {
				$service   = is_string( $host['service'] ) ? $host['service'] : '';
				$sentinels = $this->parse_nodes( $this->as_node_list( $host['sentinels'] ?? array() ) );

				if ( '' === $service || empty( $sentinels ) ) {
					return $this->disabled( $parameters, 'MC_STORAGE_HOST sentinel mode requires a non-empty "service" and at least one "sentinels" entry.' );
				}

				return array(
					'mode'       => 'sentinel',
					'nodes'      => $sentinels,
					'parameters' => $parameters,
					'service'    => $service,
					'reason'     => '',
				);
			}

			// No recognized discriminator key.
			return $this->disabled( $parameters, 'MC_STORAGE_HOST array must contain a "master" (replication) or "service" (sentinel) key.' );
		}

		// Scalar host: single. Carry the top-level "port" setting onto the node.
		$single = array( 'host' => is_string( $host ) ? $host : '127.0.0.1' );
		if ( isset( $this->settings['port'] ) && is_numeric( $this->settings['port'] ) ) {
			$single['port'] = (int) $this->settings['port'];
		}

		return array(
			'mode'       => 'single',
			'nodes'      => array( $this->parse_node( $single ) ),
			'parameters' => $parameters,
			'service'    => '',
			'reason'     => '',
		);
	}

	/**
	 * Build the shared connection parameters applied to every node.
	 *
	 * @since 1.7.0
	 * @access private
	 *
	 * @return array<string, mixed> The shared Predis parameters.
	 */
	private function shared_parameters(): array {
		$parameters = array(
			'timeout'            => $this->timeout( 'timeout', 1.0 ),
			'read_write_timeout' => $this->timeout( 'read_timeout', 2.0 ),
		);

		$username = $this->settings['username'] ?? '';
		if ( is_string( $username ) && '' !== $username ) {
			$parameters['username'] = $username;
		}

		$password = $this->settings['enc_password'] ?? '';
		if ( is_string( $password ) && '' !== $password ) {
			$parameters['password'] = BaseSettings::decrypt_value( $password );
		}

		if ( isset( $this->settings['db'] ) && is_numeric( $this->settings['db'] ) ) {
			$parameters['database'] = (int) $this->settings['db'];
		}

		if ( isset( $this->settings['persistent'] ) ) {
			$parameters['persistent'] = (bool) $this->settings['persistent'];
		}

		return $parameters;
	}

	/**
	 * Parse a list of raw node definitions into Predis parameter arrays.
	 *
	 * @since 1.7.0
	 * @access private
	 *
	 * @param array<mixed> $nodes The raw node definitions.
	 * @return array<int, array<string, mixed>> The parsed node parameters.
	 */
	private function parse_nodes( array $nodes ): array {
		return array_values( array_map( array( $this, 'parse_node' ), $nodes ) );
	}

	/**
	 * Parse a single node definition (string or map) into Predis parameters.
	 *
	 * Accepts `host`, `host:port`, a `tls://`/`tcp://` prefix, a unix socket path,
	 * or a map with `host`/`port`/`scheme`/`role`/`alias`.
	 *
	 * @since 1.7.0
	 * @access private
	 *
	 * @param mixed $node The node definition.
	 * @return array<string, mixed> The parsed Predis parameters for the node.
	 */
	private function parse_node( $node ): array {
		if ( is_string( $node ) ) {
			$node = array( 'host' => $node );
		}

		if ( ! is_array( $node ) ) {
			$node = array();
		}

		$host   = isset( $node['host'] ) && is_string( $node['host'] ) ? $node['host'] : '127.0.0.1';
		$port   = isset( $node['port'] ) && is_numeric( $node['port'] ) ? (int) $node['port'] : 6379;
		$scheme = isset( $node['scheme'] ) && is_string( $node['scheme'] ) ? $node['scheme'] : 'tcp';

		// Extract a scheme prefix from the host (e.g. "tls://host").
		if ( preg_match( '#^(tls|tcp|unix)://#', $host, $matches ) ) {
			$scheme = $matches[1];
			$host   = (string) substr( $host, strlen( $matches[0] ) );
		}

		// Split an inline "host:port" (skip socket paths).
		if ( 0 !== strpos( $host, '/' ) && false !== strpos( $host, ':' ) ) {
			$parts = explode( ':', $host );
			if ( 2 === count( $parts ) && is_numeric( $parts[1] ) ) {
				$host = $parts[0];
				$port = (int) $parts[1];
			}
		}

		// A leading slash means a unix socket path.
		$is_socket = 0 === strpos( $host, '/' );
		if ( $is_socket ) {
			$scheme = 'unix';
		}

		$parameters = array( 'scheme' => $scheme );

		if ( $is_socket ) {
			$parameters['path'] = $host;
		} else {
			$parameters['host'] = $host;
			$parameters['port'] = $port;
		}

		if ( isset( $node['role'] ) && is_string( $node['role'] ) && '' !== $node['role'] ) {
			$parameters['role'] = $node['role'];
		}

		if ( isset( $node['alias'] ) && is_string( $node['alias'] ) && '' !== $node['alias'] ) {
			$parameters['alias'] = $node['alias'];
		}

		return $parameters;
	}

	/**
	 * Read a timeout setting (seconds) as a positive float, with a default.
	 *
	 * @since 1.7.0
	 * @access private
	 *
	 * @param string $key     The settings key (`timeout` or `read_timeout`).
	 * @param float  $default The default in seconds when absent/invalid.
	 * @return float The timeout in seconds.
	 */
	private function timeout( string $key, float $default ): float {
		return isset( $this->settings[ $key ] ) && is_numeric( $this->settings[ $key ] )
			? (float) $this->settings[ $key ]
			: $default;
	}

	/**
	 * Normalize a `nodes` value into a list of node definitions.
	 *
	 * Accepts a single host string, a single node map, or a list of either.
	 *
	 * @since 1.7.0
	 * @access private
	 *
	 * @param mixed $nodes The raw `nodes` value.
	 * @return array<int, mixed> The nodes as a list of definitions.
	 */
	private function as_node_list( $nodes ): array {
		if ( is_string( $nodes ) ) {
			return array( $nodes );
		}

		if ( ! is_array( $nodes ) ) {
			return array();
		}

		return $this->is_list( $nodes ) ? $nodes : array( $nodes );
	}

	/**
	 * Determine whether an array is a sequential list (PHP 7.4-safe).
	 *
	 * @since 1.7.0
	 * @access private
	 *
	 * @param array<mixed> $value The array to test.
	 * @return bool Whether the array is a list.
	 */
	private function is_list( array $value ): bool {
		if ( array() === $value ) {
			return true;
		}

		return array_keys( $value ) === range( 0, count( $value ) - 1 );
	}

	/**
	 * Build a "disabled" definition and log the misconfiguration.
	 *
	 * @since 1.7.0
	 * @access private
	 *
	 * @param array<string, mixed> $parameters The shared parameters.
	 * @param string               $reason     The misconfiguration message.
	 * @return array{mode: string, nodes: array<int, array<string, mixed>>, parameters: array<string, mixed>, service: string, reason: string}
	 */
	private function disabled( array $parameters, string $reason ): array {
		$this->logger->error( $reason . ' Cache disabled (running uncached).' );

		return array(
			'mode'       => 'disabled',
			'nodes'      => array(),
			'parameters' => $parameters,
			'service'    => '',
			'reason'     => $reason,
		);
	}
}
