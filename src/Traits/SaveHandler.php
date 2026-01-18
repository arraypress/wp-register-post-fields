<?php
/**
 * Save Handler Trait
 *
 * Handles saving of field values when posts are saved.
 *
 * @package     ArrayPress\RegisterPostFields\Traits
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterPostFields\Traits;

use WP_Post;

/**
 * Trait SaveHandler
 *
 * Provides methods for saving field values to post meta.
 */
trait SaveHandler {

	/**
	 * Save field values when a post is saved.
	 *
	 * @param int     $post_id The post ID.
	 * @param WP_Post $post    The post object.
	 *
	 * @return void
	 */
	public function save_fields( int $post_id, WP_Post $post ): void {
		if ( ! $this->can_save( $post_id, $post ) ) {
			return;
		}

		foreach ( $this->config['fields'] as $meta_key => $field ) {
			if ( ! $this->check_permission( $field ) ) {
				continue;
			}

			$this->save_single_field( $post_id, $meta_key, $field );
		}
	}

	/**
	 * Check if we can save the fields.
	 *
	 * @param int     $post_id The post ID.
	 * @param WP_Post $post    The post object.
	 *
	 * @return bool True if saving is allowed.
	 */
	protected function can_save( int $post_id, WP_Post $post ): bool {
		// Verify nonce
		if ( ! isset( $_POST[ $this->id . '_nonce' ] ) ||
		     ! wp_verify_nonce( $_POST[ $this->id . '_nonce' ], 'save_' . $this->id ) ) {
			return false;
		}

		// Check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		// Check post type
		if ( ! in_array( $post->post_type, $this->config['post_types'], true ) ) {
			return false;
		}

		// Check permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Save a single field value.
	 *
	 * @param int    $post_id  The post ID.
	 * @param string $meta_key The meta key.
	 * @param array  $field    The field configuration.
	 *
	 * @return void
	 */
	protected function save_single_field( int $post_id, string $meta_key, array $field ): void {
		$type = $field['type'];

		// Handle checkbox (unchecked = not in POST)
		if ( $type === 'checkbox' ) {
			$value = isset( $_POST[ $meta_key ] ) ? 1 : 0;
			update_post_meta( $post_id, $meta_key, $value );

			return;
		}

		// Handle amount_type specially
		if ( $type === 'amount_type' ) {
			$this->save_amount_type_field( $post_id, $meta_key, $field );

			return;
		}

		// Handle missing values
		if ( ! isset( $_POST[ $meta_key ] ) ) {
			delete_post_meta( $post_id, $meta_key );

			return;
		}

		$value = $_POST[ $meta_key ];

		// Sanitize and save
		$value = $this->sanitize_value( $value, $field );

		if ( $this->is_empty_value( $value ) ) {
			delete_post_meta( $post_id, $meta_key );
		} else {
			update_post_meta( $post_id, $meta_key, $value );
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
	 * Check if a value is considered empty for storage purposes.
	 *
	 * @param mixed $value The value to check.
	 *
	 * @return bool True if the value is empty.
	 */
	protected function is_empty_value( $value ): bool {
		if ( '' === $value || null === $value ) {
			return true;
		}

		if ( is_array( $value ) && empty( $value ) ) {
			return true;
		}

		return false;
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

}