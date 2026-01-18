<?php
/**
 * Conditional Logic Trait
 *
 * Handles parsing and rendering of conditional field visibility (show_when).
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
 * Trait ConditionalLogic
 *
 * Provides methods for conditional field visibility based on other field values.
 */
trait ConditionalLogic {

	/**
	 * Normalize show_when configuration to a consistent array format.
	 *
	 * Supports three input formats:
	 * 1. Shorthand: ['field_name' => 'value']
	 * 2. Explicit: ['field' => 'name', 'operator' => '==', 'value' => 'x']
	 * 3. Multiple conditions (AND): [['field' => 'a', 'value' => 1], ['field' => 'b', 'value' => 2]]
	 *
	 * @param array $show_when Raw show_when configuration.
	 *
	 * @return array Normalized array of conditions.
	 */
	protected function normalize_show_when( array $show_when ): array {
		if ( empty( $show_when ) ) {
			return [];
		}

		// Check if it's already an array of conditions (multiple AND conditions)
		if ( isset( $show_when[0] ) && is_array( $show_when[0] ) ) {
			return array_map( [ $this, 'normalize_single_condition' ], $show_when );
		}

		// Check if it's explicit format (has 'field' key)
		if ( isset( $show_when['field'] ) ) {
			return [ $this->normalize_single_condition( $show_when ) ];
		}

		// Shorthand format: ['field_name' => 'value'] or ['field1' => 'value1', 'field2' => 'value2']
		$conditions = [];
		foreach ( $show_when as $field => $value ) {
			$conditions[] = [
				'field'    => $field,
				'operator' => '==',
				'value'    => $value,
			];
		}

		return $conditions;
	}

	/**
	 * Normalize a single condition to the standard format.
	 *
	 * @param array $condition Raw condition.
	 *
	 * @return array Normalized condition with field, operator, and value.
	 */
	protected function normalize_single_condition( array $condition ): array {
		return [
			'field'    => $condition['field'] ?? '',
			'operator' => $condition['operator'] ?? '==',
			'value'    => $condition['value'] ?? '',
		];
	}

	/**
	 * Get data attributes for conditional field visibility.
	 *
	 * @param array  $field    The field configuration.
	 * @param string $meta_key The field's meta key.
	 *
	 * @return string HTML data attributes string.
	 */
	protected function get_conditional_attributes( array $field, string $meta_key ): string {
		if ( empty( $field['show_when'] ) ) {
			return '';
		}

		$conditions_json = wp_json_encode( $field['show_when'] );

		return sprintf(
			' data-show-when="%s"',
			esc_attr( $conditions_json )
		);
	}

	/**
	 * Get all field conditions for JavaScript initialization.
	 *
	 * This collects all show_when conditions from all fields in the current metabox
	 * for passing to JavaScript via wp_localize_script.
	 *
	 * @return array Array of field conditions keyed by meta_key.
	 */
	protected function get_all_field_conditions(): array {
		$conditions = [];

		foreach ( $this->config['fields'] as $meta_key => $field ) {
			if ( ! empty( $field['show_when'] ) ) {
				$conditions[ $meta_key ] = $field['show_when'];
			}

			// Also check nested fields in groups and repeaters
			if ( in_array( $field['type'], [ 'group', 'repeater' ], true ) && ! empty( $field['fields'] ) ) {
				foreach ( $field['fields'] as $nested_key => $nested_field ) {
					if ( ! empty( $nested_field['show_when'] ) ) {
						$conditions[ $meta_key . '.' . $nested_key ] = $nested_field['show_when'];
					}
				}
			}
		}

		return $conditions;
	}

	/**
	 * Check if a field should be visible based on its conditions and current values.
	 *
	 * This is used for server-side initial rendering. JavaScript handles dynamic changes.
	 *
	 * @param array $field   The field configuration.
	 * @param int   $post_id The post ID.
	 *
	 * @return bool True if field should be visible.
	 */
	protected function should_show_field( array $field, int $post_id ): bool {
		if ( empty( $field['show_when'] ) ) {
			return true;
		}

		// All conditions must be met (AND logic)
		foreach ( $field['show_when'] as $condition ) {
			$controller_value = get_post_meta( $post_id, $condition['field'], true );

			if ( ! $this->evaluate_condition( $controller_value, $condition['operator'], $condition['value'] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Evaluate a single condition.
	 *
	 * @param mixed  $actual_value The actual value from the controller field.
	 * @param string $operator     The comparison operator.
	 * @param mixed  $expected     The expected value.
	 *
	 * @return bool True if condition is met.
	 */
	protected function evaluate_condition( $actual_value, string $operator, $expected ): bool {
		switch ( $operator ) {
			case '==':
			case '=':
				// Loose comparison to handle string/int differences
				return $actual_value == $expected;

			case '===':
				return $actual_value === $expected;

			case '!=':
			case '<>':
				return $actual_value != $expected;

			case '!==':
				return $actual_value !== $expected;

			case '>':
				return $actual_value > $expected;

			case '>=':
				return $actual_value >= $expected;

			case '<':
				return $actual_value < $expected;

			case '<=':
				return $actual_value <= $expected;

			case 'in':
				// Check if actual value is in array of expected values
				$expected_array = is_array( $expected ) ? $expected : [ $expected ];

				return in_array( $actual_value, $expected_array );

			case 'not_in':
				// Check if actual value is NOT in array of expected values
				$expected_array = is_array( $expected ) ? $expected : [ $expected ];

				return ! in_array( $actual_value, $expected_array );

			case 'contains':
				// Check if actual value contains expected string
				return is_string( $actual_value ) && str_contains( $actual_value, $expected );

			case 'not_contains':
				// Check if actual value does NOT contain expected string
				return is_string( $actual_value ) && ! str_contains( $actual_value, $expected );

			case 'empty':
				return empty( $actual_value );

			case 'not_empty':
				return ! empty( $actual_value );

			default:
				// Default to equality check
				return $actual_value == $expected;
		}
	}

	/**
	 * Get the CSS classes for a conditional field wrapper.
	 *
	 * @param array $field   The field configuration.
	 * @param int   $post_id The post ID.
	 *
	 * @return string CSS classes string.
	 */
	protected function get_conditional_classes( array $field, int $post_id ): string {
		$classes = [];

		if ( ! empty( $field['show_when'] ) ) {
			$classes[] = 'arraypress-conditional-field';

			if ( ! $this->should_show_field( $field, $post_id ) ) {
				$classes[] = 'arraypress-field--hidden';
			}
		}

		return implode( ' ', $classes );
	}

}
