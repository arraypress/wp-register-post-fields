<?php
/**
 * Post Fields Registry
 *
 * Static registry for managing all registered metabox configurations.
 * Provides lookup methods for retrieving metabox and field configurations.
 *
 * @package     ArrayPress\RegisterPostFields
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterPostFields;

/**
 * Class Registry
 *
 * Central registry for all registered post field metaboxes.
 *
 * @package ArrayPress\RegisterPostFields
 */
class Registry {

	/**
	 * Array of registered metabox configurations.
	 *
	 * @var array
	 */
	protected static array $metaboxes = [];

	/**
	 * Register a metabox configuration.
	 *
	 * @param string $id     The metabox ID.
	 * @param array  $config The metabox configuration.
	 *
	 * @return void
	 */
	public static function register( string $id, array $config ): void {
		self::$metaboxes[ $id ] = $config;
	}

	/**
	 * Check if a metabox is registered.
	 *
	 * @param string $id The metabox ID.
	 *
	 * @return bool True if registered.
	 */
	public static function has( string $id ): bool {
		return isset( self::$metaboxes[ $id ] );
	}

	/**
	 * Get all registered metaboxes.
	 *
	 * @return array Array of metabox configurations.
	 */
	public static function get_all(): array {
		return self::$metaboxes;
	}

	/**
	 * Get a specific metabox configuration.
	 *
	 * @param string $id The metabox ID.
	 *
	 * @return array|null The metabox configuration or null if not found.
	 */
	public static function get( string $id ): ?array {
		return self::$metaboxes[ $id ] ?? null;
	}

	/**
	 * Get fields for a specific metabox.
	 *
	 * @param string $id The metabox ID.
	 *
	 * @return array Array of field configurations.
	 */
	public static function get_fields( string $id ): array {
		return self::$metaboxes[ $id ]['fields'] ?? [];
	}

	/**
	 * Get a specific field configuration.
	 *
	 * @param string $metabox_id The metabox ID.
	 * @param string $meta_key   The field's meta key.
	 *
	 * @return array|null The field configuration or null if not found.
	 */
	public static function get_field( string $metabox_id, string $meta_key ): ?array {
		return self::$metaboxes[ $metabox_id ]['fields'][ $meta_key ] ?? null;
	}

	/**
	 * Find a field across all metaboxes.
	 *
	 * @param string $meta_key The field's meta key to search for.
	 *
	 * @return array|null Array with 'metabox_id' and 'field' keys, or null if not found.
	 */
	public static function find_field( string $meta_key ): ?array {
		foreach ( self::$metaboxes as $metabox_id => $metabox ) {
			if ( isset( $metabox['fields'][ $meta_key ] ) ) {
				return [
					'metabox_id' => $metabox_id,
					'field'      => $metabox['fields'][ $meta_key ],
				];
			}
		}

		return null;
	}

	/**
	 * Get metaboxes registered for a specific post type.
	 *
	 * @param string $post_type The post type to filter by.
	 *
	 * @return array Array of metabox configurations for the post type.
	 */
	public static function get_for_post_type( string $post_type ): array {
		return array_filter( self::$metaboxes, function ( $config ) use ( $post_type ) {
			return in_array( $post_type, (array) $config['post_types'], true );
		} );
	}

	/**
	 * Remove a metabox from the registry.
	 *
	 * @param string $id The metabox ID to remove.
	 *
	 * @return bool True if removed, false if not found.
	 */
	public static function remove( string $id ): bool {
		if ( isset( self::$metaboxes[ $id ] ) ) {
			unset( self::$metaboxes[ $id ] );

			return true;
		}

		return false;
	}

	/**
	 * Clear all registered metaboxes.
	 *
	 * Primarily useful for testing purposes.
	 *
	 * @return void
	 */
	public static function clear(): void {
		self::$metaboxes = [];
	}

	/**
	 * Get the count of registered metaboxes.
	 *
	 * @return int The number of registered metaboxes.
	 */
	public static function count(): int {
		return count( self::$metaboxes );
	}

}