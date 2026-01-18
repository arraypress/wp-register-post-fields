<?php
/**
 * Meta Registration Trait
 *
 * Handles WordPress meta registration for fields.
 *
 * @package     ArrayPress\RegisterPostFields\Traits
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterPostFields\Traits;

/**
 * Trait MetaRegistration
 *
 * Provides methods for registering meta keys with WordPress.
 */
trait MetaRegistration {

	/**
	 * Register meta keys with WordPress.
	 *
	 * @return void
	 */
	public function register_meta(): void {
		foreach ( $this->config['post_types'] as $post_type ) {
			foreach ( $this->config['fields'] as $meta_key => $field ) {
				$this->register_field_meta( $post_type, $meta_key, $field );
			}
		}
	}

	/**
	 * Register meta for a single field.
	 *
	 * @param string $post_type The post type.
	 * @param string $meta_key  The meta key.
	 * @param array  $field     The field configuration.
	 *
	 * @return void
	 */
	protected function register_field_meta( string $post_type, string $meta_key, array $field ): void {
		$type   = $field['type'];
		$schema = $this->get_rest_schema( $field );

		$args = [
			'object_subtype'    => $post_type,
			'type'              => $schema['type'],
			'description'       => $field['description'],
			'single'            => true,
			'sanitize_callback' => $this->get_sanitize_callback( $field ),
			'auth_callback'     => function () use ( $field ) {
				return current_user_can( $field['capability'] );
			},
			'show_in_rest'      => $field['show_in_rest'] ? [ 'schema' => $schema ] : false,
			'default'           => $field['default'],
		];

		// Handle array types (gallery, repeater, multiple selects)
		if ( in_array( $type, [ 'gallery', 'repeater' ], true ) ||
		     ( $field['multiple'] && in_array( $type, [ 'post', 'user', 'term' ], true ) ) ) {
			$args['type']   = 'array';
			$args['single'] = true;
		}

		register_meta( 'post', $meta_key, $args );

		// Register secondary meta key for amount_type
		if ( $type === 'amount_type' && ! empty( $field['type_meta_key'] ) ) {
			register_meta( 'post', $field['type_meta_key'], [
				'object_subtype'    => $post_type,
				'type'              => 'string',
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => $field['show_in_rest'],
			] );
		}
	}

	/**
	 * Get sanitize callback for a field.
	 *
	 * @param array $field The field configuration.
	 *
	 * @return callable The sanitize callback.
	 */
	protected function get_sanitize_callback( array $field ): callable {
		if ( is_callable( $field['sanitize_callback'] ) ) {
			return $field['sanitize_callback'];
		}

		return function ( $value ) use ( $field ) {
			return $this->sanitize_value( $value, $field );
		};
	}

}
