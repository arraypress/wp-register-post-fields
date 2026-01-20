<?php
/**
 * Asset Manager Trait
 *
 * Handles enqueueing of CSS and JavaScript assets for the post fields library.
 *
 * @package     ArrayPress\RegisterPostFields\Traits
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL2+
 * @version     1.2.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterPostFields\Traits;

/**
 * Trait AssetManager
 *
 * Provides methods for managing and enqueueing required assets.
 */
trait AssetManager {

	/**
	 * Whether assets have been enqueued.
	 *
	 * @var bool
	 */
	protected static bool $assets_enqueued = false;

	/**
	 * Enqueue required assets.
	 *
	 * @param string $hook The current admin page hook.
	 *
	 * @return void
	 */
	public function enqueue_assets( string $hook ): void {
		if ( ! $this->should_enqueue_assets( $hook ) ) {
			return;
		}

		$this->enqueue_wordpress_dependencies();
		$this->enqueue_library_assets();
	}

	/**
	 * Determine if assets should be enqueued for the current page.
	 *
	 * @param string $hook The current admin page hook.
	 *
	 * @return bool True if assets should be enqueued.
	 */
	protected function should_enqueue_assets( string $hook ): bool {
		// Only on post edit screens
		if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
			return false;
		}

		// Only for registered post types
		global $post_type;

		return in_array( $post_type, $this->config['post_types'], true );
	}

	/**
	 * Enqueue WordPress core dependencies based on field types.
	 *
	 * @return void
	 */
	protected function enqueue_wordpress_dependencies(): void {
		// Enqueue media for image/file/gallery/file_url/link fields
		if ( $this->has_field_type( [ 'image', 'file', 'gallery', 'file_url', 'link' ] ) ) {
			wp_enqueue_media();
		}

		// Enqueue color picker
		if ( $this->has_field_type( 'color' ) ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
		}

		// Enqueue CodeMirror for code fields
		if ( $this->has_field_type( 'code' ) ) {
			$this->enqueue_codemirror();
		}

		// Enqueue Select2 for all ajax-powered fields
		if ( $this->has_field_type( [ 'ajax', 'post_ajax', 'taxonomy_ajax', 'user_ajax' ] ) ) {
			$this->enqueue_select2();
		}
	}

	/**
	 * Enqueue the library's CSS and JavaScript assets.
	 *
	 * @return void
	 */
	/**
	 * Enqueue the library's CSS and JavaScript assets.
	 *
	 * @return void
	 */
	protected function enqueue_library_assets(): void {
		// Only enqueue once across all metabox instances
		if ( self::$assets_enqueued ) {
			return;
		}

		wp_enqueue_composer_style(
			'arraypress-post-fields',
			__FILE__,
			'css/post-fields.css'
		);

		// Build script dependencies - only add dependencies that are actually enqueued
		$script_deps = [ 'jquery', 'jquery-ui-sortable' ];

		// Add color picker dependency if color fields exist
		if ( $this->has_field_type( 'color' ) ) {
			$script_deps[] = 'wp-color-picker';
		}

		// Add Select2 dependency if ajax fields exist
		if ( $this->has_field_type( [ 'ajax', 'post_ajax', 'taxonomy_ajax', 'user_ajax' ] ) ) {
			$script_deps[] = 'arraypress-select2';
		}

		// Add CodeMirror dependency if code fields exist
		if ( $this->has_field_type( 'code' ) ) {
			$script_deps[] = 'wp-codemirror';
		}

		wp_enqueue_composer_script(
			'arraypress-post-fields',
			__FILE__,
			'js/post-fields.js',
			$script_deps
		);

		// Localize script with configuration data
		wp_localize_script( 'arraypress-post-fields', 'arraypressPostFields', [
			'conditions' => $this->get_all_field_conditions(),
			'restUrl'    => rest_url( 'arraypress-post-fields/v1/ajax' ),
			'nonce'      => wp_create_nonce( 'wp_rest' ),
			'i18n'       => [
				'showPassword' => __( 'Show password', 'arraypress' ),
				'hidePassword' => __( 'Hide password', 'arraypress' ),
				'loadingEmbed' => __( 'Loading preview...', 'arraypress' ),
				'embedError'   => __( 'Could not load preview for this URL.', 'arraypress' ),
			],
		] );

		self::$assets_enqueued = true;
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
	 * Enqueue CodeMirror for code editor fields.
	 *
	 * WordPress bundles CodeMirror, so we use the built-in version.
	 *
	 * @return void
	 */
	protected function enqueue_codemirror(): void {
		// WordPress includes CodeMirror - we just need to enqueue it
		$settings = wp_enqueue_code_editor( [
			'type' => 'text/html',
		] );

		// If code editor was disabled by user settings, $settings will be false
		if ( false === $settings ) {
			return;
		}

		// Enqueue additional modes we might need
		wp_enqueue_script( 'wp-codemirror' );
		wp_enqueue_style( 'wp-codemirror' );

		// Enqueue common modes
		$modes = [ 'css', 'javascript', 'htmlmixed', 'xml', 'php', 'sql', 'markdown' ];
		foreach ( $modes as $mode ) {
			wp_enqueue_script( "codemirror-mode-{$mode}" );
		}
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
			if ( in_array( $field['type'], [ 'repeater', 'group' ], true ) && ! empty( $field['fields'] ) ) {
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
	 * Reset the assets enqueued flag.
	 *
	 * Primarily useful for testing purposes.
	 *
	 * @return void
	 */
	public static function reset_assets_enqueued(): void {
		self::$assets_enqueued = false;
	}

}