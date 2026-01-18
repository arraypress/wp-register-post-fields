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

use ArrayPress\RegisterPostFields\Traits\ConditionalLogic;
use ArrayPress\RegisterPostFields\Traits\FieldRenderer;
use ArrayPress\RegisterPostFields\Traits\FieldSanitizer;
use ArrayPress\RegisterPostFields\Traits\MetaRegistration;
use ArrayPress\RegisterPostFields\Traits\RestSchema;
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

	use ConditionalLogic;
	use FieldRenderer;
	use FieldSanitizer;
	use MetaRegistration;
	use RestSchema;

	/**
	 * Array of registered metabox configurations.
	 *
	 * @var array
	 */
	protected static array $metaboxes = [];

	/**
	 * Whether assets have been enqueued.
	 *
	 * @var bool
	 */
	protected static bool $assets_enqueued = false;

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
	 * Supported field types and their default sanitizers.
	 *
	 * @var array
	 */
	protected static array $field_types = [
		'text'        => 'sanitize_text_field',
		'textarea'    => 'sanitize_textarea_field',
		'number'      => null,
		'select'      => null,
		'checkbox'    => null,
		'url'         => 'esc_url_raw',
		'email'       => 'sanitize_email',
		'color'       => 'sanitize_hex_color',
		'date'        => 'sanitize_text_field',
		'datetime'    => 'sanitize_text_field',
		'time'        => 'sanitize_text_field',
		'image'       => 'absint',
		'file'        => 'absint',
		'gallery'     => null,
		'wysiwyg'     => 'wp_kses_post',
		'post'        => null,
		'user'        => null,
		'term'        => null,
		'amount_type' => null,
		'group'       => null,
		'repeater'    => null,
		'ajax'        => null,
	];

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

		self::$metaboxes[ $this->id ] = $this->config;

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
	 * Parse and merge configuration with defaults.
	 *
	 * @param array $config Raw configuration array.
	 *
	 * @return array Parsed configuration.
	 */
	protected function parse_config( array $config ): array {
		$defaults = [
			'title'      => __( 'Additional Information', 'arraypress' ),
			'post_types' => [ 'post' ],
			'context'    => 'normal',
			'priority'   => 'high',
			'prefix'     => '',
			'capability' => 'edit_posts',
			'fields'     => [],
		];

		$config = wp_parse_args( $config, $defaults );

		// Normalize post_types to array
		if ( is_string( $config['post_types'] ) ) {
			$config['post_types'] = [ $config['post_types'] ];
		}

		// Parse each field
		$config['fields'] = $this->parse_fields( $config['fields'], $config['prefix'] );

		return $config;
	}

	/**
	 * Parse field configurations with defaults.
	 *
	 * @param array  $fields Raw field configurations.
	 * @param string $prefix Optional prefix for meta keys.
	 *
	 * @return array Parsed field configurations.
	 */
	protected function parse_fields( array $fields, string $prefix = '' ): array {
		$defaults = [
			'label'             => '',
			'type'              => 'text',
			'description'       => '',
			'default'           => '',
			'placeholder'       => '',
			'options'           => [],
			'min'               => null,
			'max'               => null,
			'step'              => null,
			'rows'              => 5,
			'sanitize_callback' => null,
			'capability'        => 'edit_posts',
			'show_in_rest'      => true,
			// Conditional logic
			'show_when'         => [],
			// Number/amount fields
			'type_options'      => [],
			'type_meta_key'     => '',
			'type_default'      => '',
			// Media fields
			'mime_types'        => [],
			'button_text'       => '',
			// Relational fields
			'post_type'         => 'post',
			'taxonomy'          => 'category',
			'role'              => [],
			'multiple'          => false,
			'display'           => 'select',
			// Group/Repeater
			'fields'            => [],
			'button_label'      => '',
			'max_items'         => 0,
			'min_items'         => 0,
			'collapsed'         => false,
		];

		$parsed = [];

		foreach ( $fields as $key => $field ) {
			$meta_key = $prefix ? $prefix . $key : $key;

			$field                 = wp_parse_args( $field, $defaults );
			$field['original_key'] = $key;

			// Normalize show_when conditions
			if ( ! empty( $field['show_when'] ) ) {
				$field['show_when'] = $this->normalize_show_when( $field['show_when'] );
			}

			$parsed[ $meta_key ] = $field;

			// Parse nested fields for groups and repeaters
			if ( in_array( $field['type'], [ 'group', 'repeater' ], true ) && ! empty( $field['fields'] ) ) {
				$parsed[ $meta_key ]['fields'] = $this->parse_fields( $field['fields'] );
			}
		}

		return $parsed;
	}

	/**
	 * Validate field configurations.
	 *
	 * @param array $fields Field configurations to validate.
	 *
	 * @return void
	 * @throws Exception If field configuration is invalid.
	 */
	protected function validate_fields( array $fields ): void {
		foreach ( $fields as $key => $field ) {
			if ( ! is_string( $key ) || empty( $key ) ) {
				throw new Exception( 'Invalid field key provided. It must be a non-empty string.' );
			}

			$type = $field['type'] ?? 'text';
			if ( ! array_key_exists( $type, self::$field_types ) ) {
				throw new Exception( sprintf( 'Invalid field type "%s" for field "%s".', $type, $key ) );
			}

			// Validate amount_type requirements
			if ( $type === 'amount_type' ) {
				if ( empty( $field['type_options'] ) ) {
					throw new Exception( sprintf( 'Field "%s" of type "amount_type" requires "type_options" to be set.', $key ) );
				}
				if ( empty( $field['type_meta_key'] ) ) {
					throw new Exception( sprintf( 'Field "%s" of type "amount_type" requires "type_meta_key" to be set.', $key ) );
				}
			}

			// Validate nested fields
			if ( in_array( $type, [ 'group', 'repeater' ], true ) && ! empty( $field['fields'] ) ) {
				$this->validate_fields( $field['fields'] );
			}
		}
	}

	/**
	 * Load WordPress hooks.
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
	 * Enqueue required assets.
	 *
	 * @param string $hook The current admin page hook.
	 *
	 * @return void
	 */
	public function enqueue_assets( string $hook ): void {
		if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
			return;
		}

		global $post_type;
		if ( ! in_array( $post_type, $this->config['post_types'], true ) ) {
			return;
		}

		// Enqueue media for image/file/gallery fields
		if ( $this->has_field_type( [ 'image', 'file', 'gallery' ] ) ) {
			wp_enqueue_media();
		}

		// Enqueue color picker
		if ( $this->has_field_type( 'color' ) ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
		}

		// Enqueue Select2 for ajax fields
		if ( $this->has_field_type( 'ajax' ) ) {
			$this->enqueue_select2();
		}

		// Enqueue custom assets only once
		if ( ! self::$assets_enqueued ) {
			$this->enqueue_library_assets();
			self::$assets_enqueued = true;
		}
	}

	/**
	 * Enqueue the library's CSS and JavaScript assets.
	 *
	 * @return void
	 */
	protected function enqueue_library_assets(): void {
		wp_enqueue_composer_style(
			'arraypress-post-fields',
			__FILE__,
			'css/post-fields.css'
		);

		wp_enqueue_composer_script(
			'arraypress-post-fields',
			__FILE__,
			'js/post-fields.js',
			[ 'jquery', 'jquery-ui-sortable', 'wp-color-picker', 'arraypress-select2' ]
		);

		// Initialize REST API if we have ajax fields
		if ( $this->has_field_type( 'ajax' ) ) {
			RestApi::instance();
		}

		// Localize script with configuration data
		wp_localize_script( 'arraypress-post-fields', 'arraypressPostFields', [
			'conditions' => $this->get_all_field_conditions(),
			'restUrl'    => rest_url( 'arraypress-post-fields/v1/ajax' ),
			'nonce'      => wp_create_nonce( 'wp_rest' ),
		] );
	}

	/**
	 * Enqueue Select2 library from composer assets.
	 *
	 * @return void
	 */
	protected function enqueue_select2(): void {
		wp_enqueue_composer_style(
			'arraypress-select2',
			__FILE__,
			'css/select2.min.css'
		);

		wp_enqueue_composer_script(
			'arraypress-select2',
			__FILE__,
			'js/select2.min.js',
			[ 'jquery' ]
		);
	}

	/**
	 * Check if metabox has a specific field type.
	 *
	 * @param string|array $types Field type(s) to check for.
	 *
	 * @return bool True if field type exists.
	 */
	protected function has_field_type( $types ): bool {
		$types = (array) $types;

		foreach ( $this->config['fields'] as $field ) {
			if ( in_array( $field['type'], $types, true ) ) {
				return true;
			}

			// Check nested fields in repeaters/groups
			if ( in_array( $field['type'], [ 'repeater', 'group' ], true ) ) {
				foreach ( $field['fields'] as $nested_field ) {
					if ( in_array( $nested_field['type'], $types, true ) ) {
						return true;
					}
				}
			}
		}

		return false;
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
	 * Save field values when a post is saved.
	 *
	 * @param int     $post_id The post ID.
	 * @param WP_Post $post    The post object.
	 *
	 * @return void
	 */
	public function save_fields( int $post_id, WP_Post $post ): void {
		// Verify nonce
		if ( ! isset( $_POST[ $this->id . '_nonce' ] ) ||
		     ! wp_verify_nonce( $_POST[ $this->id . '_nonce' ], 'save_' . $this->id ) ) {
			return;
		}

		// Check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check post type
		if ( ! in_array( $post->post_type, $this->config['post_types'], true ) ) {
			return;
		}

		// Check permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		foreach ( $this->config['fields'] as $meta_key => $field ) {
			if ( ! $this->check_permission( $field ) ) {
				continue;
			}

			$type = $field['type'];

			// Handle checkbox (unchecked = not in POST)
			if ( $type === 'checkbox' ) {
				$value = isset( $_POST[ $meta_key ] ) ? 1 : 0;
			} elseif ( $type === 'amount_type' ) {
				$this->save_amount_type_field( $post_id, $meta_key, $field );
				continue;
			} else {
				if ( ! isset( $_POST[ $meta_key ] ) ) {
					delete_post_meta( $post_id, $meta_key );
					continue;
				}
				$value = $_POST[ $meta_key ];
			}

			// Sanitize and save
			$value = $this->sanitize_value( $value, $field );

			if ( '' === $value || null === $value || ( is_array( $value ) && empty( $value ) ) ) {
				delete_post_meta( $post_id, $meta_key );
			} else {
				update_post_meta( $post_id, $meta_key, $value );
			}
		}
	}

	/**
	 * Save amount_type field values.
	 *
	 * @param int    $post_id  The post ID.
	 * @param string $meta_key The amount meta key.
	 * @param array  $field    The field configuration.
	 *
	 * @return void
	 */
	protected function save_amount_type_field( int $post_id, string $meta_key, array $field ): void {
		$type_meta_key = $field['type_meta_key'];

		$amount = $_POST[ $meta_key ] ?? '';
		$type   = $_POST[ $type_meta_key ] ?? '';

		// Sanitize amount
		$amount = $this->sanitize_amount( $amount, $field );

		// Validate type against options
		$type_options = $this->get_options( $field['type_options'] );
		if ( ! array_key_exists( $type, $type_options ) ) {
			$type = $field['type_default'] ?: array_key_first( $type_options );
		}

		// Save or delete
		if ( '' === $amount || null === $amount || $amount <= 0 ) {
			delete_post_meta( $post_id, $meta_key );
			delete_post_meta( $post_id, $type_meta_key );
		} else {
			update_post_meta( $post_id, $meta_key, $amount );
			update_post_meta( $post_id, $type_meta_key, $type );
		}
	}

	/**
	 * Check if the current user has permission to view/edit the field.
	 *
	 * @param array $field The field configuration.
	 *
	 * @return bool True if user has permission.
	 */
	protected function check_permission( array $field ): bool {
		return current_user_can( $field['capability'] );
	}

	/**
	 * Get options from array or callable.
	 *
	 * @param array|callable $options Options array or callable.
	 *
	 * @return array Options array.
	 */
	protected function get_options( $options ): array {
		if ( is_callable( $options ) ) {
			$options = call_user_func( $options );
		}

		return is_array( $options ) ? $options : [];
	}

	/**
	 * Get all registered metaboxes.
	 *
	 * @return array Array of metabox configurations.
	 */
	public static function get_all_metaboxes(): array {
		return self::$metaboxes;
	}

	/**
	 * Get a specific metabox configuration.
	 *
	 * @param string $id The metabox ID.
	 *
	 * @return array|null The metabox configuration or null if not found.
	 */
	public static function get_metabox( string $id ): ?array {
		return self::$metaboxes[ $id ] ?? null;
	}

	/**
	 * Get fields for a specific metabox.
	 *
	 * @param string $id The metabox ID.
	 *
	 * @return array Array of field configurations.
	 */
	public static function get_metabox_fields( string $id ): array {
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
	public static function get_field_config( string $metabox_id, string $meta_key ): ?array {
		return self::$metaboxes[ $metabox_id ]['fields'][ $meta_key ] ?? null;
	}

}
