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
	 * Singleton instance.
	 *
	 * @var RestApi|null
	 */
	private static ?RestApi $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return RestApi
	 */
	public static function instance(): RestApi {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route( $this->namespace, '/ajax', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'handle_request' ],
			'permission_callback' => [ $this, 'permission_check' ],
			'args'                => [
				'metabox_id' => [
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				],
				'field_key'  => [
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
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

		// Get the field configuration
		$field = PostFields::get_field_config( $metabox_id, $field_key );

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