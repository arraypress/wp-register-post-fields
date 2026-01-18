<?php
/**
 * Post Fields Class
 *
 * A lightweight class for registering custom metaboxes with fields on WordPress
 * post edit screens. Provides a simple API for common field types with automatic
 * saving, sanitization, REST API integration, and conditional field visibility.
 *
 * @package     ArrayPress\RegisterPostFields
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL2+
 * @version     2.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterPostFields;

use ArrayPress\RegisterPostFields\Traits\AssetManager;
use ArrayPress\RegisterPostFields\Traits\ConditionalLogic;
use ArrayPress\RegisterPostFields\Traits\ConfigParser;
use ArrayPress\RegisterPostFields\Traits\FieldRenderer;
use ArrayPress\RegisterPostFields\Traits\FieldSanitizer;
use ArrayPress\RegisterPostFields\Traits\MetaRegistration;
use ArrayPress\RegisterPostFields\Traits\RestSchema;
use ArrayPress\RegisterPostFields\Traits\SaveHandler;
use Exception;
use WP_Post;

/**
 * Class PostFields
 *
 * Manages custom metabox and field registration for posts in WordPress admin.
 *
 * @package ArrayPress\RegisterPostFields
 */
class PostFields {

	use AssetManager;
	use ConditionalLogic;
	use ConfigParser;
	use FieldRenderer;
	use FieldSanitizer;
	use MetaRegistration;
	use RestSchema;
	use SaveHandler;

	/**
	 * The metabox ID.
	 *
	 * @var string
	 */
	protected string $id;

	/**
	 * The metabox configuration.
	 *
	 * @var array
	 */
	protected array $config;

	/**
	 * PostFields constructor.
	 *
	 * Initializes the metabox registration.
	 *
	 * @param string $id     Unique metabox identifier.
	 * @param array  $config Metabox configuration including title, post_types, and fields.
	 *
	 * @throws Exception If configuration is invalid.
	 */
	public function __construct( string $id, array $config ) {
		if ( empty( $id ) ) {
			throw new Exception( 'Metabox ID cannot be empty.' );
		}

		if ( empty( $config['fields'] ) || ! is_array( $config['fields'] ) ) {
			throw new Exception( 'Metabox must have at least one field defined.' );
		}

		$this->id     = $id;
		$this->config = $this->parse_config( $config );

		$this->validate_fields( $this->config['fields'] );

		// Register with the central registry
		Registry::register( $this->id, $this->config );

		// Register REST API if needed
		if ( $this->has_field_type( 'ajax' ) ) {
			RestApi::register();
		}

		$this->initialize_hooks();
	}

	/**
	 * Initialize WordPress hooks.
	 *
	 * @return void
	 */
	protected function initialize_hooks(): void {
		// Load hooks immediately if already past init, otherwise wait
		if ( did_action( 'init' ) ) {
			$this->register_meta();
		} else {
			add_action( 'init', [ $this, 'register_meta' ] );
		}

		if ( did_action( 'admin_init' ) ) {
			$this->load_hooks();
		} else {
			add_action( 'admin_init', [ $this, 'load_hooks' ] );
		}
	}

	/**
	 * Load WordPress admin hooks.
	 *
	 * @return void
	 */
	public function load_hooks(): void {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
		add_action( 'save_post', [ $this, 'save_fields' ], 10, 2 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Register the metabox with WordPress.
	 *
	 * @return void
	 */
	public function add_meta_box(): void {
		foreach ( $this->config['post_types'] as $post_type ) {
			add_meta_box(
				$this->id,
				$this->config['title'],
				[ $this, 'render_metabox' ],
				$post_type,
				$this->config['context'],
				$this->config['priority']
			);
		}
	}

	/**
	 * Render the metabox content.
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @return void
	 */
	public function render_metabox( WP_Post $post ): void {
		// Security nonce
		wp_nonce_field( 'save_' . $this->id, $this->id . '_nonce' );

		echo '<div class="arraypress-metabox" data-metabox-id="' . esc_attr( $this->id ) . '">';

		foreach ( $this->config['fields'] as $meta_key => $field ) {
			if ( ! $this->check_permission( $field ) ) {
				continue;
			}

			$value = get_post_meta( $post->ID, $meta_key, true );

			// Use default if no value exists
			if ( '' === $value && '' !== $field['default'] ) {
				$value = $field['default'];
			}

			$this->render_field( $meta_key, $field, $value, $post->ID );
		}

		echo '</div>';
	}

	/**
	 * Get the metabox ID.
	 *
	 * @return string The metabox ID.
	 */
	public function get_id(): string {
		return $this->id;
	}

	/**
	 * Get the metabox configuration.
	 *
	 * @return array The metabox configuration.
	 */
	public function get_config(): array {
		return $this->config;
	}

	/**
	 * Get the fields configuration.
	 *
	 * @return array The fields configuration.
	 */
	public function get_fields(): array {
		return $this->config['fields'];
	}

	/* =========================================================================
	   Static Methods (Delegate to Registry for backward compatibility)
	   ========================================================================= */

	/**
	 * Get all registered metaboxes.
	 *
	 * @return array Array of metabox configurations.
	 */
	public static function get_all_metaboxes(): array {
		return Registry::get_all();
	}

	/**
	 * Get a specific metabox configuration.
	 *
	 * @param string $id The metabox ID.
	 *
	 * @return array|null The metabox configuration or null if not found.
	 */
	public static function get_metabox( string $id ): ?array {
		return Registry::get( $id );
	}

	/**
	 * Get fields for a specific metabox.
	 *
	 * @param string $id The metabox ID.
	 *
	 * @return array Array of field configurations.
	 */
	public static function get_metabox_fields( string $id ): array {
		return Registry::get_fields( $id );
	}

	/**
	 * Get a specific field configuration.
	 *
	 * @param string $metabox_id The metabox ID.
	 * @param string $meta_key   The field's meta key.
	 *
	 * @return array|null The field configuration or null if not found.
	 */
	public static function get_field_config( string $metabox_id, string $meta_key ): ?array {
		return Registry::get_field( $metabox_id, $meta_key );
	}

}