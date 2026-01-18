<?php
/**
 * Relational Fields Rendering Trait
 *
 * Handles rendering of relational form field types that reference
 * other WordPress objects such as posts, users, terms, and AJAX-loaded data.
 *
 * @package     ArrayPress\RegisterPostFields\Traits\Rendering
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL2+
 * @version     2.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterPostFields\Traits\Rendering;

/**
 * Trait RelationalFields
 *
 * Provides rendering methods for relational field types:
 * - post: Post/page/custom post type selector
 * - user: WordPress user selector
 * - term: Taxonomy term selector
 * - ajax: AJAX-powered select with remote data source
 *
 * All relational fields support single and multiple selection modes,
 * and can display as either dropdowns or checkbox groups.
 *
 * @package ArrayPress\RegisterPostFields\Traits\Rendering
 */
trait RelationalFields {

	/**
	 * Render a post selector field
	 *
	 * Allows selection of posts, pages, or custom post types.
	 * Queries all published posts of the specified type(s).
	 *
	 * @param string $meta_key The field's meta key.
	 * @param array  $field    The field configuration array.
	 * @param mixed  $value    The current field value (post ID or array of IDs).
	 *
	 * @return void
	 */
	protected function render_post_select( string $meta_key, array $field, $value ): void {
		$post_types = (array) $field['post_type'];

		$posts = get_posts( [
			'post_type'      => $post_types,
			'posts_per_page' => - 1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		] );

		$options = [ '' => __( '— Select —', 'arraypress' ) ];

		foreach ( $posts as $post ) {
			$options[ $post->ID ] = $post->post_title;
		}

		$this->render_select_or_checkboxes( $meta_key, $field, $value, $options );
	}

	/**
	 * Render a user selector field
	 *
	 * Allows selection of WordPress users, optionally filtered by role.
	 *
	 * @param string $meta_key The field's meta key.
	 * @param array  $field    The field configuration array.
	 * @param mixed  $value    The current field value (user ID or array of IDs).
	 *
	 * @return void
	 */
	protected function render_user_select( string $meta_key, array $field, $value ): void {
		$args = [
			'orderby' => 'display_name',
			'order'   => 'ASC',
		];

		// Filter by role if specified
		if ( ! empty( $field['role'] ) ) {
			$args['role__in'] = (array) $field['role'];
		}

		$users   = get_users( $args );
		$options = [ '' => __( '— Select —', 'arraypress' ) ];

		foreach ( $users as $user ) {
			$options[ $user->ID ] = $user->display_name;
		}

		$this->render_select_or_checkboxes( $meta_key, $field, $value, $options );
	}

	/**
	 * Render a term selector field
	 *
	 * Allows selection of taxonomy terms from the specified taxonomy.
	 *
	 * @param string $meta_key The field's meta key.
	 * @param array  $field    The field configuration array.
	 * @param mixed  $value    The current field value (term ID or array of IDs).
	 *
	 * @return void
	 */
	protected function render_term_select( string $meta_key, array $field, $value ): void {
		$terms = get_terms( [
			'taxonomy'   => $field['taxonomy'],
			'hide_empty' => false,
		] );

		$options = [ '' => __( '— Select —', 'arraypress' ) ];

		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->term_id ] = $term->name;
			}
		}

		$this->render_select_or_checkboxes( $meta_key, $field, $value, $options );
	}

	/**
	 * Render an AJAX-powered select field
	 *
	 * Select2-powered select with remote data loading via REST API.
	 * Supports both single and multiple selection modes.
	 *
	 * The field requires an 'ajax_callback' in the configuration that
	 * returns options in the format: [['value' => 'id', 'label' => 'text'], ...]
	 *
	 * @param string $meta_key The field's meta key.
	 * @param array  $field    The field configuration array.
	 * @param mixed  $value    The current field value.
	 *
	 * @return void
	 */
	protected function render_ajax_select( string $meta_key, array $field, $value ): void {
		$multiple    = ! empty( $field['multiple'] );
		$placeholder = $field['placeholder'] ?? __( 'Search...', 'arraypress' );
		$name        = $multiple ? $meta_key . '[]' : $meta_key;
		$values      = $multiple ? (array) $value : ( $value ? [ $value ] : [] );
		$values      = array_filter( $values ); // Remove empty values

		// Get metabox ID from the instance property
		$metabox_id = $this->id;
		?>
		<select class="arraypress-ajax-select<?php echo $multiple ? ' multiple' : ''; ?>"
		        id="<?php echo esc_attr( $meta_key ); ?>"
		        name="<?php echo esc_attr( $name ); ?>"
			<?php echo $multiple ? 'multiple' : ''; ?>
			    data-metabox-id="<?php echo esc_attr( $metabox_id ); ?>"
			    data-field-key="<?php echo esc_attr( $meta_key ); ?>"
			    data-placeholder="<?php echo esc_attr( $placeholder ); ?>">
			<?php
			// Pre-populate with existing values
			// Labels will be hydrated via AJAX on page load
			foreach ( $values as $val ) :
				?>
				<option value="<?php echo esc_attr( $val ); ?>" selected>
					<?php echo esc_html( $val ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

}