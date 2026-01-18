<?php
/**
 * REST Schema Trait
 *
 * Handles REST API schema generation for fields.
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
 * Trait RestSchema
 *
 * Provides methods for generating REST API schemas.
 */
trait RestSchema {

	/**
	 * Get REST API schema for a field.
	 *
	 * @param array $field The field configuration.
	 *
	 * @return array The REST schema.
	 */
	protected function get_rest_schema( array $field ): array {
		$type = $field['type'];

		switch ( $type ) {
			case 'number':
			case 'amount_type':
				return $this->get_number_schema( $field );

			case 'checkbox':
				return [
					'type' => 'boolean',
				];

			case 'image':
			case 'file':
			case 'post':
			case 'term':
			case 'user':
				return $this->get_relational_schema( $field );

			case 'gallery':
				return [
					'type'  => 'array',
					'items' => [ 'type' => 'integer' ],
				];

			case 'repeater':
				return $this->get_repeater_schema( $field );

			case 'group':
				return $this->get_group_schema( $field );

			case 'select':
				return $this->get_select_schema( $field );

			default:
				return [ 'type' => 'string' ];
		}
	}

	/**
	 * Get REST schema for a number field.
	 *
	 * @param array $field The field configuration.
	 *
	 * @return array The REST schema.
	 */
	protected function get_number_schema( array $field ): array {
		$schema = [
			'type' => 'number',
		];

		if ( isset( $field['min'] ) ) {
			$schema['minimum'] = $field['min'];
		}

		if ( isset( $field['max'] ) ) {
			$schema['maximum'] = $field['max'];
		}

		return $schema;
	}

	/**
	 * Get REST schema for a relational field.
	 *
	 * @param array $field The field configuration.
	 *
	 * @return array The REST schema.
	 */
	protected function get_relational_schema( array $field ): array {
		if ( $field['multiple'] ) {
			return [
				'type'  => 'array',
				'items' => [ 'type' => 'integer' ],
			];
		}

		return [ 'type' => 'integer' ];
	}

	/**
	 * Get REST schema for a select field.
	 *
	 * @param array $field The field configuration.
	 *
	 * @return array The REST schema.
	 */
	protected function get_select_schema( array $field ): array {
		$options = $this->get_options( $field['options'] );
		$enum    = array_keys( $options );

		if ( $field['multiple'] ) {
			return [
				'type'  => 'array',
				'items' => [
					'type' => 'string',
					'enum' => $enum,
				],
			];
		}

		return [
			'type' => 'string',
			'enum' => $enum,
		];
	}

	/**
	 * Get REST schema for a repeater field.
	 *
	 * @param array $field The field configuration.
	 *
	 * @return array The REST schema.
	 */
	protected function get_repeater_schema( array $field ): array {
		return [
			'type'  => 'array',
			'items' => [
				'type'       => 'object',
				'properties' => $this->get_nested_schema_properties( $field['fields'] ),
			],
		];
	}

	/**
	 * Get REST schema for a group field.
	 *
	 * @param array $field The field configuration.
	 *
	 * @return array The REST schema.
	 */
	protected function get_group_schema( array $field ): array {
		return [
			'type'       => 'object',
			'properties' => $this->get_nested_schema_properties( $field['fields'] ),
		];
	}

	/**
	 * Get REST schema properties for nested fields (repeater/group).
	 *
	 * @param array $fields Nested field configurations.
	 *
	 * @return array Schema properties.
	 */
	protected function get_nested_schema_properties( array $fields ): array {

		return array_map( function ( $field ) {
			return $this->get_rest_schema( $field );
		}, $fields );
	}

}
