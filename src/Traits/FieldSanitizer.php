<?php
/**
 * Field Sanitizer Trait
 *
 * Handles sanitization of all field types.
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
 * Trait FieldSanitizer
 *
 * Provides methods for sanitizing all supported field types.
 */
trait FieldSanitizer {

	/**
	 * Sanitize a field value.
	 *
	 * @param mixed $value The value to sanitize.
	 * @param array $field The field configuration.
	 *
	 * @return mixed Sanitized value.
	 */
	protected function sanitize_value( $value, array $field ) {
		if ( is_callable( $field['sanitize_callback'] ) ) {
			return call_user_func( $field['sanitize_callback'], $value );
		}

		$type = $field['type'];

		switch ( $type ) {
			case 'checkbox':
				return $value ? 1 : 0;

			case 'number':
				return $this->sanitize_number( $value, $field );

			case 'select':
				return $this->sanitize_select( $value, $field );

			case 'url':
				return esc_url_raw( $value );

			case 'email':
				return sanitize_email( $value );

			case 'textarea':
				return sanitize_textarea_field( $value );

			case 'wysiwyg':
				return wp_kses_post( $value );

			case 'color':
				return sanitize_hex_color( $value );

			case 'image':
			case 'file':
				return absint( $value );

			case 'gallery':
				return $this->sanitize_gallery( $value );

			case 'post':
			case 'user':
			case 'term':
				return $this->sanitize_relational( $value, $field );

			case 'group':
				return $this->sanitize_group( $value, $field );

			case 'repeater':
				return $this->sanitize_repeater( $value, $field );

			case 'date':
			case 'datetime':
			case 'time':
			case 'text':
			default:
				return sanitize_text_field( $value );
		}
	}

	/**
	 * Sanitize a number value.
	 *
	 * @param mixed $value The value to sanitize.
	 * @param array $field The field configuration.
	 *
	 * @return int|float Sanitized number.
	 */
	protected function sanitize_number( $value, array $field ) {
		$step = $field['step'] ?? 1;

		// Determine if we should use float or int
		if ( is_numeric( $step ) && floor( $step ) != $step ) {
			$value = floatval( $value );
		} else {
			$value = intval( $value );
		}

		// Apply min/max constraints
		if ( isset( $field['min'] ) && $value < $field['min'] ) {
			$value = $field['min'];
		}
		if ( isset( $field['max'] ) && $value > $field['max'] ) {
			$value = $field['max'];
		}

		return $value;
	}

	/**
	 * Sanitize a select value.
	 *
	 * @param mixed $value The value to sanitize.
	 * @param array $field The field configuration.
	 *
	 * @return mixed Sanitized value(s).
	 */
	protected function sanitize_select( $value, array $field ) {
		$options = $this->get_options( $field['options'] );

		if ( $field['multiple'] ) {
			$values = (array) $value;

			return array_filter( $values, function ( $v ) use ( $options ) {
				return array_key_exists( $v, $options );
			} );
		}

		return array_key_exists( $value, $options ) ? $value : $field['default'];
	}

	/**
	 * Sanitize a gallery value.
	 *
	 * @param mixed $value The value to sanitize.
	 *
	 * @return array Sanitized array of attachment IDs.
	 */
	protected function sanitize_gallery( $value ): array {
		if ( is_string( $value ) ) {
			$value = array_filter( explode( ',', $value ) );
		}

		return array_map( 'absint', (array) $value );
	}

	/**
	 * Sanitize a relational field value (post, user, term).
	 *
	 * @param mixed $value The value to sanitize.
	 * @param array $field The field configuration.
	 *
	 * @return int|array Sanitized value(s).
	 */
	protected function sanitize_relational( $value, array $field ) {
		if ( $field['multiple'] ) {
			return array_map( 'absint', (array) $value );
		}

		return absint( $value );
	}

	/**
	 * Sanitize an amount value.
	 *
	 * @param mixed $value The value to sanitize.
	 * @param array $field The field configuration.
	 *
	 * @return float|string Sanitized value.
	 */
	protected function sanitize_amount( $value, array $field ) {
		if ( '' === $value || null === $value ) {
			return '';
		}

		$value = floatval( $value );

		if ( isset( $field['min'] ) && $value < $field['min'] ) {
			$value = $field['min'];
		}
		if ( isset( $field['max'] ) && $value > $field['max'] ) {
			$value = $field['max'];
		}

		return $value > 0 ? $value : '';
	}

	/**
	 * Sanitize a group field value.
	 *
	 * @param mixed $value The value to sanitize.
	 * @param array $field The field configuration.
	 *
	 * @return array Sanitized value.
	 */
	protected function sanitize_group( $value, array $field ): array {
		if ( ! is_array( $value ) ) {
			return [];
		}

		$sanitized = [];

		foreach ( $field['fields'] as $sub_key => $sub_field ) {
			$sub_value             = $value[ $sub_key ] ?? $sub_field['default'];
			$sanitized[ $sub_key ] = $this->sanitize_value( $sub_value, $sub_field );
		}

		return $sanitized;
	}

	/**
	 * Sanitize a repeater field value.
	 *
	 * @param mixed $value The value to sanitize.
	 * @param array $field The field configuration.
	 *
	 * @return array Sanitized value.
	 */
	protected function sanitize_repeater( $value, array $field ): array {
		if ( ! is_array( $value ) ) {
			return [];
		}

		$sanitized = [];

		foreach ( $value as $index => $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$sanitized_row = [];

			foreach ( $field['fields'] as $sub_key => $sub_field ) {
				$sub_value                 = $row[ $sub_key ] ?? $sub_field['default'];
				$sanitized_row[ $sub_key ] = $this->sanitize_value( $sub_value, $sub_field );
			}

			// Only add row if it has content
			$has_content = array_filter( $sanitized_row, function ( $v ) {
				return '' !== $v && null !== $v && [] !== $v;
			} );

			if ( ! empty( $has_content ) ) {
				$sanitized[] = $sanitized_row;
			}
		}

		// Apply max items limit
		if ( $field['max_items'] > 0 && count( $sanitized ) > $field['max_items'] ) {
			$sanitized = array_slice( $sanitized, 0, $field['max_items'] );
		}

		return $sanitized;
	}

}
