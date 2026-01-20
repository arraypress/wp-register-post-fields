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
 * @version     2.1.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterPostFields\Traits\Rendering;

/**
 * Trait RelationalFields
 *
 * Provides rendering methods for relational field types:
 * - post: Post/page/custom post type selector (static)
 * - user: WordPress user selector (static)
 * - term: Taxonomy term selector (static)
 * - post_ajax: AJAX-powered post selector
 * - taxonomy_ajax: AJAX-powered taxonomy term selector
 * - ajax: Custom AJAX-powered select with user-defined callback
 *
 * All relational fields support single and multiple selection modes,
 * and can display as either dropdowns or checkbox groups.
 *
 * @package ArrayPress\RegisterPostFields\Traits\Rendering
 */
trait RelationalFields {

    /**
     * Render a post selector field (static)
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
     * Render a term selector field (static)
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
     * Render an AJAX-powered post selector field
     *
     * Select2-powered post selector with remote data loading via REST API.
     * No custom callback required - uses built-in post search endpoint.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
     * @param mixed  $value    The current field value.
     *
     * @return void
     */
    protected function render_post_ajax( string $meta_key, array $field, $value ): void {
        $multiple    = ! empty( $field['multiple'] );
        $placeholder = $field['placeholder'] ?? __( 'Search posts...', 'arraypress' );
        $post_types  = (array) ( $field['post_type'] ?? 'post' );
        $name        = $multiple ? $meta_key . '[]' : $meta_key;
        $values      = $multiple ? (array) $value : ( $value ? [ $value ] : [] );
        $values      = array_filter( $values );

        // Get metabox ID from the instance property
        $metabox_id = $this->id;
        ?>
        <select class="arraypress-ajax-select arraypress-post-ajax<?php echo $multiple ? ' multiple' : ''; ?>"
                id="<?php echo esc_attr( $meta_key ); ?>"
                name="<?php echo esc_attr( $name ); ?>"
                <?php echo $multiple ? 'multiple' : ''; ?>
                data-metabox-id="<?php echo esc_attr( $metabox_id ); ?>"
                data-field-key="<?php echo esc_attr( $meta_key ); ?>"
                data-field-type="post_ajax"
                data-post-types="<?php echo esc_attr( implode( ',', $post_types ) ); ?>"
                data-placeholder="<?php echo esc_attr( $placeholder ); ?>">
            <?php
            // Pre-populate with existing values and their labels
            foreach ( $values as $post_id ) :
                $post_title = get_the_title( $post_id );
                if ( $post_title ) :
                    ?>
                    <option value="<?php echo esc_attr( $post_id ); ?>" selected>
                        <?php echo esc_html( $post_title ); ?>
                    </option>
                <?php endif;
            endforeach;
            ?>
        </select>
        <?php
    }

    /**
     * Render an AJAX-powered taxonomy term selector field
     *
     * Select2-powered taxonomy selector with remote data loading via REST API.
     * No custom callback required - uses built-in term search endpoint.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
     * @param mixed  $value    The current field value.
     *
     * @return void
     */
    protected function render_taxonomy_ajax( string $meta_key, array $field, $value ): void {
        $multiple    = ! empty( $field['multiple'] );
        $taxonomy    = $field['taxonomy'] ?? 'category';
        $placeholder = $field['placeholder'] ?? sprintf( __( 'Search %s...', 'arraypress' ), $taxonomy );
        $name        = $multiple ? $meta_key . '[]' : $meta_key;
        $values      = $multiple ? (array) $value : ( $value ? [ $value ] : [] );
        $values      = array_filter( $values );

        // Get metabox ID from the instance property
        $metabox_id = $this->id;
        ?>
        <select class="arraypress-ajax-select arraypress-taxonomy-ajax<?php echo $multiple ? ' multiple' : ''; ?>"
                id="<?php echo esc_attr( $meta_key ); ?>"
                name="<?php echo esc_attr( $name ); ?>"
                <?php echo $multiple ? 'multiple' : ''; ?>
                data-metabox-id="<?php echo esc_attr( $metabox_id ); ?>"
                data-field-key="<?php echo esc_attr( $meta_key ); ?>"
                data-field-type="taxonomy_ajax"
                data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>"
                data-placeholder="<?php echo esc_attr( $placeholder ); ?>">
            <?php
            // Pre-populate with existing values and their labels
            foreach ( $values as $term_id ) :
                $term = get_term( $term_id, $taxonomy );
                if ( $term && ! is_wp_error( $term ) ) :
                    ?>
                    <option value="<?php echo esc_attr( $term_id ); ?>" selected>
                        <?php echo esc_html( $term->name ); ?>
                    </option>
                <?php endif;
            endforeach;
            ?>
        </select>
        <?php
    }

    /**
     * Render a custom AJAX-powered select field
     *
     * Select2-powered select with remote data loading via REST API.
     * Requires an 'ajax_callback' in the configuration.
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
        $values      = array_filter( $values );

        // Get metabox ID from the instance property
        $metabox_id = $this->id;
        ?>
        <select class="arraypress-ajax-select<?php echo $multiple ? ' multiple' : ''; ?>"
                id="<?php echo esc_attr( $meta_key ); ?>"
                name="<?php echo esc_attr( $name ); ?>"
                <?php echo $multiple ? 'multiple' : ''; ?>
                data-metabox-id="<?php echo esc_attr( $metabox_id ); ?>"
                data-field-key="<?php echo esc_attr( $meta_key ); ?>"
                data-field-type="ajax"
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

    /**
     * Render an AJAX-powered user selector field
     *
     * Select2-powered user selector with remote data loading via REST API.
     * No custom callback required - uses built-in user search endpoint.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
     * @param mixed  $value    The current field value.
     *
     * @return void
     */
    protected function render_user_ajax( string $meta_key, array $field, $value ): void {
        $multiple    = ! empty( $field['multiple'] );
        $placeholder = $field['placeholder'] ?? __( 'Search users...', 'arraypress' );
        $roles       = (array) ( $field['role'] ?? [] );
        $name        = $multiple ? $meta_key . '[]' : $meta_key;
        $values      = $multiple ? (array) $value : ( $value ? [ $value ] : [] );
        $values      = array_filter( $values );

        // Get metabox ID from the instance property
        $metabox_id = $this->id;
        ?>
        <select class="arraypress-ajax-select arraypress-user-ajax<?php echo $multiple ? ' multiple' : ''; ?>"
                id="<?php echo esc_attr( $meta_key ); ?>"
                name="<?php echo esc_attr( $name ); ?>"
                <?php echo $multiple ? 'multiple' : ''; ?>
                data-metabox-id="<?php echo esc_attr( $metabox_id ); ?>"
                data-field-key="<?php echo esc_attr( $meta_key ); ?>"
                data-field-type="user_ajax"
                data-role="<?php echo esc_attr( implode( ',', $roles ) ); ?>"
                data-placeholder="<?php echo esc_attr( $placeholder ); ?>">
            <?php
            // Pre-populate with existing values and their labels
            foreach ( $values as $user_id ) :
                $user = get_userdata( $user_id );
                if ( $user ) :
                    ?>
                    <option value="<?php echo esc_attr( $user_id ); ?>" selected>
                        <?php echo esc_html( $user->display_name ); ?>
                    </option>
                <?php endif;
            endforeach;
            ?>
        </select>
        <?php
    }

}