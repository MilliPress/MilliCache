<?php
/**
 * Logger class for MilliRules
 *
 * Provides intelligent logging with rate limiting, error aggregation, and environment detection.
 *
 * @package     MilliRules
 * @author      Philipp Wellmer
 * @since       0.2.0
 */

namespace MilliCache\Deps\MilliRules;

/**
 * Logger class with rate limiting and conditional output
 */
class Logger {
	/**
	 * Logging levels
	 */
	const ERROR   = 1;
	const WARNING = 2;
	const INFO    = 3;
	const DEBUG   = 4;

	/**
	 * Singleton instance
	 *
	 * @var Logger|null
	 */
	private static $instance = null;

	/**
	 * Cache of logged messages with timestamps (static for persistence across instances)
	 *
	 * @var array<string, array{count: int, first_logged: int, last_logged: int}>
	 */
	private static $message_cache = array();

	/**
	 * Rate limit window in seconds
	 *
	 * @var int
	 */
	private $rate_limit_window = 60;

	/**
	 * Minimum log level to output
	 *
	 * @var int
	 */
	private $min_level = self::ERROR;

	/**
	 * Whether debug mode is enabled
	 *
	 * @var bool
	 */
	private $debug_mode = false;

	/**
	 * Error aggregation storage (static for persistence across instances)
	 *
	 * @var array<string, array{count: int, messages: array<string>}>
	 */
	private static $aggregated_errors = array();

	/**
	 * Private constructor for singleton
	 */
	private function __construct() {
		$this->configure();
	}

	/**
	 * Get singleton instance
	 *
	 * @return Logger
	 */
	public static function instance(): Logger {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Configure logger based on environment
	 *
	 * @return void
	 */
	private function configure(): void {
		// Detect WordPress debug mode
		$wp_debug = defined( 'WP_DEBUG' ) && WP_DEBUG;

		// Check for custom environment variable
		$env_debug = getenv( 'MILLIRULES_DEBUG' ) === '1' || getenv( 'MILLIRULES_DEBUG' ) === 'true';

		$this->debug_mode = $wp_debug || $env_debug;

		// Set minimum log level
		$env_level = getenv( 'MILLIRULES_LOG_LEVEL' );
		if ( $env_level !== false ) {
			$this->min_level = $this->parse_log_level( $env_level );
		} elseif ( $this->debug_mode ) {
			$this->min_level = self::DEBUG;
		} else {
			$this->min_level = self::ERROR;
		}

		// Set rate limit window
		$env_rate_limit = getenv( 'MILLIRULES_RATE_LIMIT' );
		if ( $env_rate_limit !== false && is_numeric( $env_rate_limit ) ) {
			$this->rate_limit_window = (int) $env_rate_limit;
		}
	}

	/**
	 * Parse log level string to constant
	 *
	 * @param string $level Level string.
	 * @return int
	 */
	private function parse_log_level( string $level ): int {
		$level = strtoupper( $level );
		switch ( $level ) {
			case 'ERROR':
				return self::ERROR;
			case 'WARNING':
				return self::WARNING;
			case 'INFO':
				return self::INFO;
			case 'DEBUG':
				return self::DEBUG;
			default:
				return self::ERROR;
		}
	}

	/**
	 * Get level name as string
	 *
	 * @param int $level Level constant.
	 * @return string
	 */
	private function get_level_name( int $level ): string {
		switch ( $level ) {
			case self::ERROR:
				return 'ERROR';
			case self::WARNING:
				return 'WARNING';
			case self::INFO:
				return 'INFO';
			case self::DEBUG:
				return 'DEBUG';
			default:
				return 'UNKNOWN';
		}
	}

	/**
	 * Check if a message should be logged based on rate limiting
	 *
	 * @param string $message_key Unique key for the message.
	 * @param int    $level       Log level.
	 * @return bool True if should log, false if rate limited.
	 */
	private function should_log( string $message_key, int $level ): bool {
		// Check if message exists in cache
		if ( ! isset( self::$message_cache[ $message_key ] ) ) {
			$this->track_message( $message_key );
			return true;
		}

		$cache_entry  = self::$message_cache[ $message_key ];
		$time_elapsed = time() - $cache_entry['last_logged'];

		// If rate limit window has passed, log again
		if ( $time_elapsed >= $this->rate_limit_window ) {
			// Log summary if message was repeated
			if ( $cache_entry['count'] > 1 ) {
				$this->log_summary( $cache_entry );
			}
			// Reset counter
			self::$message_cache[ $message_key ] = array(
				'count'        => 1,
				'first_logged' => time(),
				'last_logged'  => time(),
			);
			return true;
		}

		// Increment count but don't log
		self::$message_cache[ $message_key ]['count']++;
		return false;
	}

	/**
	 * Track message in cache
	 *
	 * @param string $message_key Message key.
	 * @return void
	 */
	private function track_message( string $message_key ): void {
		if ( ! isset( self::$message_cache[ $message_key ] ) ) {
			self::$message_cache[ $message_key ] = array(
				'count'        => 1,
				'first_logged' => time(),
				'last_logged'  => time(),
			);
		} else {
			self::$message_cache[ $message_key ]['last_logged'] = time();
		}
	}

	/**
	 * Log summary of repeated messages
	 *
	 * @param array{count: int, first_logged: int, last_logged: int} $cache_entry  Cache entry data.
	 * @return void
	 */
	private function log_summary( array $cache_entry ): void {
		$count        = $cache_entry['count'];
		$time_elapsed = $cache_entry['last_logged'] - $cache_entry['first_logged'];

		if ( $count > 1 ) {
			$summary = sprintf(
				'MilliRules [INFO]: Previous message repeated %d times over %d seconds',
				$count - 1,
				$time_elapsed
			);
			error_log( $summary );
		}
	}

	/**
	 * Core logging method
	 *
	 * @param int          $level   Log level.
	 * @param string       $message Message to log.
	 * @param array<mixed> $context Additional context data.
	 * @return void
	 */
	private function log( int $level, string $message, array $context = array() ): void {
		// Check if level should be logged
		if ( $level > $this->min_level ) {
			return;
		}

		// Create message key for rate limiting (exact match)
		$message_key = md5( $message );

		// Check rate limiting
		if ( ! $this->should_log( $message_key, $level ) ) {
			return;
		}

		// Format message
		$level_name     = $this->get_level_name( $level );
		$formatted_msg  = "MilliRules [{$level_name}]: {$message}";

		// Add context if provided
		if ( ! empty( $context ) ) {
			$context_str    = $this->format_context( $context );
			$formatted_msg .= ' | ' . $context_str;
		}

		// Log the message
		error_log( $formatted_msg );
	}

	/**
	 * Format context array for logging
	 *
	 * @param array<mixed> $context Context data.
	 * @return string
	 */
	private function format_context( array $context ): string {
		$parts = array();
		foreach ( $context as $key => $value ) {
			if ( is_scalar( $value ) || is_null( $value ) ) {
				$parts[] = $key . '=' . var_export( $value, true );
			} elseif ( is_array( $value ) ) {
				$parts[] = $key . '=' . json_encode( $value );
			} else {
				$parts[] = $key . '=' . gettype( $value );
			}
		}
		return implode( ', ', $parts );
	}

	/**
	 * Log error message
	 *
	 * @param string       $message Message to log.
	 * @param array<mixed> $context Additional context data.
	 * @return void
	 */
	public static function error( string $message, array $context = array() ): void {
		self::instance()->log( self::ERROR, $message, $context );
	}

	/**
	 * Log warning message
	 *
	 * @param string       $message Message to log.
	 * @param array<mixed> $context Additional context data.
	 * @return void
	 */
	public static function warning( string $message, array $context = array() ): void {
		self::instance()->log( self::WARNING, $message, $context );
	}

	/**
	 * Log info message
	 *
	 * @param string       $message Message to log.
	 * @param array<mixed> $context Additional context data.
	 * @return void
	 */
	public static function info( string $message, array $context = array() ): void {
		self::instance()->log( self::INFO, $message, $context );
	}

	/**
	 * Log debug message
	 *
	 * @param string       $message Message to log.
	 * @param array<mixed> $context Additional context data.
	 * @return void
	 */
	public static function debug( string $message, array $context = array() ): void {
		self::instance()->log( self::DEBUG, $message, $context );
	}

	/**
	 * Aggregate errors for batch logging
	 *
	 * @param string $category Error category.
	 * @param string $message  Error message.
	 * @return void
	 */
	public static function aggregate( string $category, string $message ): void {
		if ( ! isset( self::$aggregated_errors[ $category ] ) ) {
			self::$aggregated_errors[ $category ] = array(
				'count'    => 0,
				'messages' => array(),
			);
		}
		self::$aggregated_errors[ $category ]['count']++;
		self::$aggregated_errors[ $category ]['messages'][] = $message;
	}

	/**
	 * Flush aggregated errors
	 *
	 * @return void
	 */
	public static function flush_aggregated(): void {
		foreach ( self::$aggregated_errors as $category => $data ) {
			if ( $data['count'] > 0 ) {
				$summary = sprintf(
					'%d error(s) in category "%s"',
					$data['count'],
					$category
				);
				// Only show first few messages to avoid log bloat
				$sample_size = min( 3, count( $data['messages'] ) );
				if ( $sample_size > 0 ) {
					$summary .= ': ' . implode( '; ', array_slice( $data['messages'], 0, $sample_size ) );
					if ( count( $data['messages'] ) > $sample_size ) {
						$summary .= ' ... and ' . ( count( $data['messages'] ) - $sample_size ) . ' more';
					}
				}
				self::warning( $summary );
			}
		}
		// Clear aggregated errors
		self::$aggregated_errors = array();
	}

	/**
	 * Reset the logger (mainly for testing)
	 *
	 * @return void
	 */
	public static function reset(): void {
		self::$instance = null;
	}
}
