<?php
/**
 * REST API Handler for Ajax Fields
 *
 * Handles AJAX search and hydration requests for ajax field types.
 *
 * @package     ArrayPress\RegisterPostFields
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterPostFields;

use Exception;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class RestApi
 *
 * Manages REST API routes for ajax field types.
 */
class RestApi {

	/**
	 * The REST namespace.
	 *
	 * @var string
	 */
	private string $namespace = 'arraypress-post-fields/v1';

	/**
	 * Whether routes have been registered.
	 *
	 * @var bool
	 */
	private static bool $routes_registered = false;

	/**
	 * Register the REST API routes.
	 *
	 * Call this early (e.g., on 'init' or when registering fields).
	 *
	 * @return void
	 */
	public static function register(): void {
		if ( self::$routes_registered ) {
			return;
		}

		self::$routes_registered = true;

		add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );
	}

	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public static function register_routes(): void {
		$instance = new self();

		register_rest_route( $instance->namespace, '/ajax', [
			'methods'             => 'GET',
			'callback'            => [ $instance, 'handle_request' ],
			'permission_callback' => [ $instance, 'permission_check' ],
			'args'                => [
				'metabox_id' => [
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				],
				'field_key'  => [
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => [ __CLASS__, 'sanitize_field_key' ],
				],
				'search'     => [
					'type'              => 'string',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'include'    => [
					'type'              => 'string',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		] );
	}

	/**
	 * Sanitize field key that may contain dots for nested paths.
	 *
	 * @param string $value The field key value.
	 *
	 * @return string Sanitized field key.
	 */
	public static function sanitize_field_key( string $value ): string {
		// Allow dots for nested field paths (e.g., "resources.resource_person")
		// Sanitize each part separately
		$parts = explode( '.', $value );
		$parts = array_map( 'sanitize_key', $parts );

		return implode( '.', $parts );
	}

	/**
	 * Permission check for REST requests.
	 *
	 * @return bool
	 */
	public function permission_check(): bool {
		/**
		 * Filter the capability required for ajax field REST access.
		 *
		 * @param string $capability The required capability.
		 */
		$capability = apply_filters( 'arraypress_post_fields_rest_capability', 'edit_posts' );

		return current_user_can( $capability );
	}

	/**
	 * Handle the AJAX request.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function handle_request( WP_REST_Request $request ) {
		$metabox_id = $request->get_param( 'metabox_id' );
		$field_key  = $request->get_param( 'field_key' );
		$search     = $request->get_param( 'search' );
		$include    = $request->get_param( 'include' );

		// Get the field configuration - handle nested field paths
		$field = $this->get_field_config( $metabox_id, $field_key );

		if ( ! $field ) {
			return new WP_Error(
				'invalid_field',
				__( 'Invalid field configuration.', 'arraypress' ),
				[ 'status' => 400 ]
			);
		}

		// Ensure it's an ajax type with a callback
		if ( ( $field['type'] ?? '' ) !== 'ajax' ) {
			return new WP_Error(
				'invalid_field_type',
				__( 'Field is not an ajax type.', 'arraypress' ),
				[ 'status' => 400 ]
			);
		}

		$callback = $field['ajax_callback'] ?? null;

		if ( ! is_callable( $callback ) ) {
			return new WP_Error(
				'invalid_callback',
				__( 'Invalid ajax callback.', 'arraypress' ),
				[ 'status' => 500 ]
			);
		}

		// Parse include IDs if provided (for hydration)
		$ids = null;
		if ( ! empty( $include ) ) {
			$ids = array_map( 'trim', explode( ',', $include ) );
			$ids = array_filter( $ids );
		}

		try {
			$results = call_user_func( $callback, $search, $ids );

			if ( ! is_array( $results ) ) {
				return new WP_REST_Response( [], 200 );
			}

			// Normalize results to ensure value/label format
			$normalized = array_values( array_filter( array_map( function ( $item ) {
				if ( is_array( $item ) && isset( $item['value'] ) ) {
					return [
						'value' => $item['value'],
						'label' => $item['label'] ?? $item['value'],
					];
				}

				return null;
			}, $results ) ) );

			return new WP_REST_Response( $normalized, 200 );

		} catch ( Exception $e ) {
			return new WP_Error(
				'callback_error',
				$e->getMessage(),
				[ 'status' => 500 ]
			);
		}
	}

	/**
	 * Get field configuration, supporting nested field paths.
	 *
	 * Field key can be:
	 * - Simple: "field_name"
	 * - Nested: "parent_field.child_field" (for fields inside repeaters/groups)
	 *
	 * @param string $metabox_id The metabox ID.
	 * @param string $field_key  The field key (may include dot notation for nesting).
	 *
	 * @return array|null The field configuration or null if not found.
	 */
	protected function get_field_config( string $metabox_id, string $field_key ): ?array {
		// Check if this is a nested field path (contains a dot)
		if ( strpos( $field_key, '.' ) !== false ) {
			$parts      = explode( '.', $field_key );
			$parent_key = $parts[0];
			$child_key  = $parts[1];

			// Get the parent field (repeater or group)
			$parent_field = PostFields::get_field_config( $metabox_id, $parent_key );

			if ( ! $parent_field ) {
				return null;
			}

			// Check if parent has nested fields
			if ( ! isset( $parent_field['fields'] ) || ! is_array( $parent_field['fields'] ) ) {
				return null;
			}

			// Return the nested field config
			return $parent_field['fields'][ $child_key ] ?? null;
		}

		// Simple field path - use existing method
		return PostFields::get_field_config( $metabox_id, $field_key );
	}

	/**
	 * Get the REST namespace.
	 *
	 * @return string
	 */
	public function get_namespace(): string {
		return $this->namespace;
	}

	/**
	 * Get the full REST URL for ajax requests.
	 *
	 * @return string
	 */
	public function get_rest_url(): string {
		return rest_url( $this->namespace . '/ajax' );
	}

}