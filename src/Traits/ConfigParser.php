<?php
/**
 * Config Parser Trait
 *
 * Handles parsing and validation of metabox and field configurations.
 *
 * @package     ArrayPress\RegisterPostFields\Traits
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL2+
 * @version     1.2.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterPostFields\Traits;

use Exception;

/**
 * Trait ConfigParser
 *
 * Provides methods for parsing and validating configurations.
 */
trait ConfigParser {

	/**
	 * Supported field types and their default sanitizers.
	 *
	 * @var array
	 */
	protected static array $field_types = [
		'text'          => 'sanitize_text_field',
		'textarea'      => 'sanitize_textarea_field',
		'number'        => null,
		'select'        => null,
		'checkbox'      => null,
		'toggle'        => null,
		'url'           => 'esc_url_raw',
		'email'         => 'sanitize_email',
		'color'         => 'sanitize_hex_color',
		'date'          => 'sanitize_text_field',
		'datetime'      => 'sanitize_text_field',
		'time'          => 'sanitize_text_field',
		'date_range'    => null,
		'time_range'    => null,
		'image'         => 'absint',
		'file'          => 'absint',
		'file_url'      => 'esc_url_raw',
		'gallery'       => null,
		'wysiwyg'       => 'wp_kses_post',
		'code'          => null,
		'radio'         => 'sanitize_text_field',
		'button_group'  => 'sanitize_text_field',
		'tel'           => 'sanitize_text_field',
		'password'      => null,
		'range'         => null,
		'post'          => null,
		'user'          => null,
		'term'          => null,
		'post_ajax'     => null,
		'taxonomy_ajax' => null,
		'user_ajax'     => null,
		'amount_type'   => null,
		'group'         => null,
		'repeater'      => null,
		'ajax'          => null,
		'link'          => null,
		'oembed'        => null,
		'dimensions'    => null,
	];

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
			'full_width' => false,
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
			'tooltip'           => '',
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
			'row_title'         => '',
			'row_title_field'   => '',
			// Code editor
			'language'          => 'html',
			'line_numbers'      => true,
			// Link field
			'show_title'        => true,
			'show_target'       => true,
			// Dimensions field
			'dimension_labels'  => [],
			'dimension_units'   => '',
			// Date/Time range
			'start_label'       => '',
			'end_label'         => '',
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

			// Validate taxonomy_ajax requirements
			if ( $type === 'taxonomy_ajax' && empty( $field['taxonomy'] ) ) {
				throw new Exception( sprintf( 'Field "%s" of type "taxonomy_ajax" requires "taxonomy" to be set.', $key ) );
			}

			// Validate nested fields
			if ( in_array( $type, [ 'group', 'repeater' ], true ) && ! empty( $field['fields'] ) ) {
				$this->validate_fields( $field['fields'] );
			}
		}
	}

	/**
	 * Get the list of supported field types.
	 *
	 * @return array Array of field type names.
	 */
	public static function get_supported_field_types(): array {
		return array_keys( self::$field_types );
	}

	/**
	 * Check if a field type is supported.
	 *
	 * @param string $type The field type to check.
	 *
	 * @return bool True if supported.
	 */
	public static function is_supported_field_type( string $type ): bool {
		return array_key_exists( $type, self::$field_types );
	}

}