<?php
/**
 * Registration Functions
 *
 * Provides convenient helper functions for registering custom post fields in WordPress.
 * These functions are in the global namespace for easy use throughout your codebase.
 *
 * @package     ArrayPress\WP\RegisterPostFields
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL2+
 * @version     2.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

use ArrayPress\RegisterPostFields\PostFields;

if ( ! function_exists( 'register_post_fields' ) ) {
	/**
	 * Register custom fields for posts via a metabox.
	 *
	 * This function provides a simple API for adding custom fields to post edit
	 * screens. Fields are automatically saved to post meta with proper sanitization
	 * and REST API integration.
	 *
	 * Supported field types:
	 * - text: Single line text input
	 * - textarea: Multi-line text input
	 * - wysiwyg: WordPress rich text editor
	 * - number: Numeric input with optional min/max/step
	 * - select: Dropdown with options (supports multiple)
	 * - checkbox: Boolean checkbox
	 * - url: URL input with validation
	 * - email: Email input with validation
	 * - color: Color picker
	 * - date: Date picker
	 * - datetime: Date and time picker
	 * - time: Time picker
	 * - image: Single image picker from media library
	 * - file: Single file picker from media library
	 * - gallery: Multiple images picker
	 * - post: Post/page selector
	 * - user: User selector
	 * - term: Taxonomy term selector
	 * - amount_type: Combined numeric input with type selector
	 * - group: Static group of fields
	 * - repeater: Dynamic repeatable group of fields
	 *
	 * @param string $id     Unique metabox identifier.
	 * @param array  $config Metabox configuration.
	 *
	 * @return PostFields|null The PostFields instance, or null on error.
	 *
	 * @example
	 * // Register simple fields for a product
	 * register_post_fields( 'product_info', [
	 *     'title'      => 'Product Information',
	 *     'post_types' => 'product',
	 *     'fields'     => [
	 *         'sku' => [
	 *             'label'       => 'SKU',
	 *             'type'        => 'text',
	 *             'placeholder' => 'PRD-0000',
	 *         ],
	 *         'price' => [
	 *             'label' => 'Price',
	 *             'type'  => 'number',
	 *             'min'   => 0,
	 *             'step'  => 0.01,
	 *         ],
	 *     ],
	 * ] );
	 *
	 * @example
	 * // Register with conditional fields
	 * register_post_fields( 'shipping_options', [
	 *     'title'      => 'Shipping Options',
	 *     'post_types' => 'product',
	 *     'fields'     => [
	 *         'is_physical' => [
	 *             'label' => 'Physical Product',
	 *             'type'  => 'checkbox',
	 *         ],
	 *         'weight' => [
	 *             'label'     => 'Weight (kg)',
	 *             'type'      => 'number',
	 *             'show_when' => [ 'is_physical' => 1 ],
	 *         ],
	 *         'dimensions' => [
	 *             'label'     => 'Dimensions',
	 *             'type'      => 'text',
	 *             'show_when' => [ 'is_physical' => 1 ],
	 *         ],
	 *     ],
	 * ] );
	 */
	function register_post_fields( string $id, array $config ): ?PostFields {
		try {
			return new PostFields( $id, $config );
		} catch ( Exception $e ) {
			error_log( 'WP Register Post Fields Error: ' . $e->getMessage() );

			return null;
		}
	}
}

if ( ! function_exists( 'get_post_field_value' ) ) {
	/**
	 * Get a post meta field value with default fallback.
	 *
	 * This function retrieves a post meta value and falls back to the registered
	 * default if no value exists. Useful for ensuring consistent default values.
	 *
	 * @param int    $post_id    The post ID.
	 * @param string $meta_key   The meta key to retrieve.
	 * @param string $metabox_id Optional. The metabox ID to look up defaults from.
	 *
	 * @return mixed The field value or default.
	 *
	 * @example
	 * $price = get_post_field_value( $post_id, 'price', 'product_info' );
	 */
	function get_post_field_value( int $post_id, string $meta_key, string $metabox_id = '' ) {
		$value = get_post_meta( $post_id, $meta_key, true );

		// If we have a value, return it
		if ( '' !== $value && null !== $value ) {
			return $value;
		}

		// Try to find the default from registered fields
		if ( $metabox_id ) {
			$field = PostFields::get_field_config( $metabox_id, $meta_key );
			if ( $field && isset( $field['default'] ) ) {
				return $field['default'];
			}
		} else {
			// Search all metaboxes for this field
			foreach ( PostFields::get_all_metaboxes() as $id => $metabox ) {
				if ( isset( $metabox['fields'][ $meta_key ] ) ) {
					return $metabox['fields'][ $meta_key ]['default'] ?? '';
				}
			}
		}

		return $value;
	}
}

if ( ! function_exists( 'get_post_fields' ) ) {
	/**
	 * Get all registered fields for a metabox.
	 *
	 * @param string $metabox_id The metabox ID.
	 *
	 * @return array Array of field configurations.
	 *
	 * @example
	 * $fields = get_post_fields( 'product_info' );
	 * foreach ( $fields as $meta_key => $config ) {
	 *     echo $config['label'] . ': ' . get_post_meta( $post_id, $meta_key, true );
	 * }
	 */
	function get_post_fields( string $metabox_id ): array {
		return PostFields::get_metabox_fields( $metabox_id );
	}
}

if ( ! function_exists( 'get_post_field_config' ) ) {
	/**
	 * Get configuration for a specific field.
	 *
	 * @param string $metabox_id The metabox ID.
	 * @param string $meta_key   The field's meta key.
	 *
	 * @return array|null The field configuration or null if not found.
	 *
	 * @example
	 * $config = get_post_field_config( 'product_info', 'price' );
	 * if ( $config ) {
	 *     echo 'Label: ' . $config['label'];
	 *     echo 'Type: ' . $config['type'];
	 * }
	 */
	function get_post_field_config( string $metabox_id, string $meta_key ): ?array {
		return PostFields::get_field_config( $metabox_id, $meta_key );
	}
}

if ( ! function_exists( 'get_all_post_field_groups' ) ) {
	/**
	 * Get all registered post field groups (metaboxes).
	 *
	 * @return array Array of metabox configurations keyed by metabox ID.
	 *
	 * @example
	 * $groups = get_all_post_field_groups();
	 * foreach ( $groups as $id => $config ) {
	 *     echo $config['title'] . ' (' . count( $config['fields'] ) . ' fields)';
	 * }
	 */
	function get_all_post_field_groups(): array {
		return PostFields::get_all_metaboxes();
	}
}
