<?php
/**
 * Is Archive Condition
 *
 * Checks if current view is an archive.
 *
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millicache.com>
 */

namespace MilliCache\Rules\Conditions;

/**
 * Class IsArchiveCondition
 *
 * Checks if the current view is an archive page.
 * Supports checking for specific archive types and values.
 *
 * Usage examples:
 * - ->isArchive() - Any archive
 * - ->isArchive('category') - Any category archive
 * - ->isArchive('category', 'news') - Specific category by slug
 * - ->isArchive('category', 5) - Specific category by ID
 * - ->isArchive('category', [5, 10, 15], 'IN') - Multiple categories
 * - ->isArchive('post_type', 'product') - Product post type archive
 * - ->isArchive('taxonomy', 'product_cat') - Product category taxonomy
 * - ->isArchive('date', 'year') - Year archive
 * - ->isArchive('author', 3) - Specific author
 *
 * @since 1.0.0
 */
class IsArchive extends BaseCondition {
	/**
	 * Get the condition type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The condition type identifier.
	 */
	public function get_type(): string {
		return 'is_archive';
	}

	/**
	 * Check if the condition matches.
	 *
	 * Override matches() to handle special archive logic before delegating to parent.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $context The execution context.
	 * @return bool True if the condition matches, false otherwise.
	 */
	public function matches( array $context ): bool {
		if ( ! function_exists( 'is_archive' ) ) {
			return false;
		}

		$archive_type = $this->config['name'] ?? null;

		// If no archive type specified, check if it's any archive.
		if ( empty( $archive_type ) ) {
			$is_archive = is_archive();

			// Handle operators for a simple archive check.
			if ( 'IS NOT' === $this->operator || '!=' === $this->operator ) {
				return ! $is_archive;
			}

			return $is_archive;
		}

		// For specific archive types, use parent's comparison logic.
		return parent::matches( $context );
	}

	/**
	 * Get the actual value from context.
	 *
	 * Returns the current archive identifier based on the archive type.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $context The execution context.
	 * @return mixed The current archive value (ID, slug, type, etc.) or null if not an archive.
	 */
	protected function get_actual_value( array $context ) {
		if ( ! function_exists( 'is_archive' ) ) {
			return null;
		}

		$archive_type = $this->config['name'] ?? null;

		// If no specific type, just return boolean.
		if ( empty( $archive_type ) || ! is_string( $archive_type ) ) {
			return is_archive();
		}

		return $this->get_current_archive_value( $archive_type );
	}

	/**
	 * Get the current archive value based on the archive type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $archive_type The archive to check.
	 * @return mixed The current archive value or null if not that archive type.
	 */
	protected function get_current_archive_value( string $archive_type ) {
		$archive_type = strtolower( $archive_type );

		switch ( $archive_type ) {
			case 'post_type':
			case 'post':
			case 'product':
				// Return the current post-type if on a post-type archive.
				if ( function_exists( 'is_post_type_archive' ) && is_post_type_archive() ) {
					$queried_object = get_queried_object();
					if ( $queried_object instanceof \WP_Post_Type ) {
						return $queried_object->name;
					}
					// Fallback to get_query_var.
					return get_query_var( 'post_type' );
				}
				return null;

			case 'category':
				// Return the current category ID or slug.
				if ( function_exists( 'is_category' ) && is_category() ) {
					$category = get_queried_object();
					if ( $category instanceof \WP_Term ) {
						// Return both ID and slug for flexible matching.
						return array(
							'id'   => $category->term_id,
							'slug' => $category->slug,
							'name' => $category->name,
						);
					}
				}
				return null;

			case 'tag':
				// Return the current tag ID or slug.
				if ( function_exists( 'is_tag' ) && is_tag() ) {
					$tag = get_queried_object();
					if ( $tag instanceof \WP_Term ) {
						return array(
							'id'   => $tag->term_id,
							'slug' => $tag->slug,
							'name' => $tag->name,
						);
					}
				}
				return null;

			case 'taxonomy':
			case 'tax':
				// Return the current taxonomy name and term.
				if ( function_exists( 'is_tax' ) && is_tax() ) {
					$term = get_queried_object();
					if ( $term instanceof \WP_Term ) {
						return array(
							'taxonomy' => $term->taxonomy,
							'term_id'  => $term->term_id,
							'slug'     => $term->slug,
							'name'     => $term->name,
						);
					}
				}
				return null;

			case 'author':
				// Return the current author ID.
				if ( function_exists( 'is_author' ) && is_author() ) {
					$author = get_queried_object();
					if ( $author instanceof \WP_User ) {
						return array(
							'id'    => $author->ID,
							'login' => $author->user_login,
							'slug'  => $author->user_nicename,
						);
					}
				}
				return null;

			case 'date':
				// Return the specific date type.
				if ( function_exists( 'is_date' ) && is_date() ) {
					if ( function_exists( 'is_year' ) && is_year() ) {
						return 'year';
					} elseif ( function_exists( 'is_month' ) && is_month() ) {
						return 'month';
					} elseif ( function_exists( 'is_day' ) && is_day() ) {
						return 'day';
					} elseif ( function_exists( 'is_time' ) && is_time() ) {
						return 'time';
					}
					return 'date';
				}
				return null;

			case 'year':
				return ( function_exists( 'is_year' ) && is_year() ) ? 'year' : null;

			case 'month':
				return ( function_exists( 'is_month' ) && is_month() ) ? 'month' : null;

			case 'day':
				return ( function_exists( 'is_day' ) && is_day() ) ? 'day' : null;

			case 'time':
				return ( function_exists( 'is_time' ) && is_time() ) ? 'time' : null;

			default:
				// Try as a custom post-type archive.
				if ( function_exists( 'is_post_type_archive' ) && is_post_type_archive( $archive_type ) ) {
					return $archive_type;
				}
				return null;
		}
	}

	/**
	 * Compare values with special handling for archive data.
	 *
	 * Override to handle comparison when the actual value is an array (for category, tag, taxonomy, author).
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $actual   The actual value from context.
	 * @param mixed $expected The expected value from config.
	 * @return bool Whether the values match, according to the operator.
	 */
	protected function compare( $actual, $expected ): bool {
		// If the actual value is an array (category, tag, taxonomy, author), check against ID, slug, or name.
		if ( is_array( $actual ) && ! empty( $actual ) ) {
			// For taxonomy, if expected is an array, match taxonomy + term.
			if ( isset( $actual['taxonomy'] ) && is_array( $expected ) ) {
				$expected_taxonomy = $expected[0] ?? $expected['taxonomy'] ?? null;
				$expected_term     = $expected[1] ?? $expected['term'] ?? null;

				if ( $actual['taxonomy'] !== $expected_taxonomy ) {
					return 'IS NOT' === $this->operator || '!=' === $this->operator;
				}

				if ( null === $expected_term ) {
					return 'IS' === $this->operator || '=' === $this->operator;
				}

				$expected = $expected_term;
			}

			// Check if the expected value matches any of the actual values (id, slug, name).
			foreach ( $actual as $key => $value ) {
				if ( parent::compare( $value, $expected ) ) {
					return true;
				}
			}

			return false;
		}

		// Standard comparison for simple values.
		return parent::compare( $actual, $expected );
	}
}
