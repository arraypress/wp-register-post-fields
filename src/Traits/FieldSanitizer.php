<?php
/**
 * Field Sanitizer Trait
 *
 * Handles sanitization of all field types.
 *
 * @package     ArrayPress\RegisterPostFields\Traits
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL2+
 * @version     1.1.0
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

			case 'range':
			case 'number':
				return $this->sanitize_number( $value, $field );

			case 'select':
				return $this->sanitize_select( $value, $field );

			case 'url':
			case 'file_url':
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
			case 'post_ajax':
			case 'taxonomy_ajax':
			case 'user_ajax':
				return $this->sanitize_relational( $value, $field );

			case 'ajax':
				return $this->sanitize_ajax( $value, $field );

			case 'group':
				return $this->sanitize_group( $value, $field );

			case 'repeater':
				return $this->sanitize_repeater( $value, $field );

			case 'button_group':
				return $this->sanitize_button_group( $value, $field );

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
	 * Sanitize a relational field value (post, user, term, post_ajax, taxonomy_ajax).
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
	 * Sanitize an ajax field value.
	 *
	 * Ajax fields can have string or integer values depending on the callback.
	 *
	 * @param mixed $value The value to sanitize.
	 * @param array $field The field configuration.
	 *
	 * @return string|array Sanitized value(s).
	 */
	protected function sanitize_ajax( $value, array $field ) {
		if ( $field['multiple'] ) {
			$values = (array) $value;

			return array_map( 'sanitize_text_field', array_filter( $values ) );
		}

		return sanitize_text_field( $value );
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
			// Skip the template row (it has __INDEX__ as key)
			if ( $index === '__INDEX__' || ! is_numeric( $index ) ) {
				continue;
			}

			if ( ! is_array( $row ) ) {
				continue;
			}

			$sanitized_row = [];

			foreach ( $field['fields'] as $sub_key => $sub_field ) {
				$sub_value                 = $row[ $sub_key ] ?? $sub_field['default'];
				$sanitized_row[ $sub_key ] = $this->sanitize_value( $sub_value, $sub_field );
			}

			// Only add row if it has meaningful content
			if ( $this->row_has_content( $sanitized_row, $field['fields'] ) ) {
				$sanitized[] = $sanitized_row;
			}
		}

		// Apply max items limit
		if ( $field['max_items'] > 0 && count( $sanitized ) > $field['max_items'] ) {
			$sanitized = array_slice( $sanitized, 0, $field['max_items'] );
		}

		return $sanitized;
	}

	/**
	 * Sanitize a button group value.
	 *
	 * @param mixed $value The value to sanitize.
	 * @param array $field The field configuration.
	 *
	 * @return string|array Sanitized value(s).
	 */
	protected function sanitize_button_group( $value, array $field ) {
		$options = $this->get_options( $field['options'] );

		if ( ! empty( $field['multiple'] ) ) {
			$values = (array) $value;

			return array_filter( $values, function ( $v ) use ( $options ) {
				return array_key_exists( $v, $options );
			} );
		}

		return array_key_exists( $value, $options ) ? $value : ( $field['default'] ?? '' );
	}

	/**
	 * Check if a repeater row has meaningful content.
	 *
	 * This method determines whether a row should be saved by checking if any
	 * field contains a value that differs from its default or empty state.
	 *
	 * @param array $row    The sanitized row values.
	 * @param array $fields The field configurations.
	 *
	 * @return bool True if the row has content worth saving.
	 */
	protected function row_has_content( array $row, array $fields ): bool {
		foreach ( $row as $key => $value ) {
			$field_config = $fields[ $key ] ?? [];
			$type         = $field_config['type'] ?? 'text';

			// Check based on field type
			switch ( $type ) {
				case 'checkbox':
					// Checkbox with value 1 (checked) is content
					if ( $value === 1 || $value === '1' || $value === true ) {
						return true;
					}
					break;

				case 'number':
					// Non-empty number is content (0 can be valid)
					if ( $value !== '' && $value !== null ) {
						return true;
					}
					break;

				case 'select':
				case 'ajax':
				case 'post_ajax':
				case 'taxonomy_ajax':
					// Non-empty select/ajax value
					if ( $value !== '' && $value !== null && ! empty( $value ) ) {
						return true;
					}
					break;

				case 'image':
				case 'file':
					// Valid attachment ID > 0 is content
					if ( ! empty( $value ) && $value > 0 ) {
						return true;
					}
					break;

				case 'file_url':
					// Non-empty URL is content
					if ( ! empty( $value ) ) {
						return true;
					}
					break;

				case 'gallery':
					// Non-empty array is content
					if ( is_array( $value ) && ! empty( $value ) ) {
						return true;
					}
					break;

				default:
					// For text, textarea, url, email, etc. - non-empty string is content
					if ( $value !== '' && $value !== null && ! is_array( $value ) ) {
						return true;
					}
					// For arrays (like product_features), check if not empty
					if ( is_array( $value ) && ! empty( $value ) ) {
						return true;
					}
					break;
			}
		}

		return false;
	}

}