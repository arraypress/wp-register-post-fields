<?php
/**
 * Nested Fields Rendering Trait
 *
 * Handles rendering of field inputs within nested contexts such as
 * groups and repeaters. Provides simplified versions of field renders
 * that work with array-based name attributes.
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
 * Trait NestedFields
 *
 * Provides the render_nested_field_input method and supporting helpers
 * for rendering field inputs inside groups and repeaters.
 *
 * Supports most field types in nested contexts:
 * - text, url, email, tel
 * - textarea
 * - number
 * - select
 * - checkbox
 * - radio
 * - button_group
 * - range
 * - image, file, file_url
 * - ajax, post_ajax, taxonomy_ajax
 *
 * Note: WYSIWYG, gallery, and complex nested fields (group, repeater)
 * are not supported in nested contexts.
 *
 * @package ArrayPress\RegisterPostFields\Traits
 */
trait NestedFields {

    /**
     * The current parent field key when rendering nested fields.
     * Used to pass context for AJAX fields inside repeaters/groups.
     *
     * @var string|null
     */
    protected ?string $current_parent_field = null;

    /**
     * Set the current parent field context
     *
     * @param string|null $parent_key The parent field key or null to clear.
     *
     * @return void
     */
    protected function set_parent_field_context( ?string $parent_key ): void {
        $this->current_parent_field = $parent_key;
    }

    /**
     * Render a nested field input
     *
     * Renders the appropriate input element for a field within a group
     * or repeater context. Uses the full name path for proper form submission.
     *
     * @param string $name  The full input name attribute (e.g., "meta_key[0][sub_key]").
     * @param string $key   The field key (without parent path).
     * @param array  $field The field configuration array.
     * @param mixed  $value The current field value.
     *
     * @return void
     */
    protected function render_nested_field_input( string $name, string $key, array $field, $value ): void {
        $type = $field['type'];

        switch ( $type ) {
            case 'textarea':
                $this->render_nested_textarea( $name, $field, $value );
                break;

            case 'number':
                $this->render_nested_number( $name, $field, $value );
                break;

            case 'select':
                $this->render_nested_select( $name, $field, $value );
                break;

            case 'checkbox':
                $this->render_nested_checkbox( $name, $field, $value );
                break;

            case 'radio':
                $this->render_nested_radio( $name, $field, $value );
                break;

            case 'button_group':
                $this->render_nested_button_group( $name, $field, $value );
                break;

            case 'range':
                $this->render_nested_range( $name, $field, $value );
                break;

            case 'image':
                $this->render_nested_image( $name, $field, $value );
                break;

            case 'file':
                $this->render_nested_file( $name, $field, $value );
                break;

            case 'file_url':
                $this->render_nested_file_url( $name, $field, $value );
                break;

            case 'ajax':
                $this->render_nested_ajax( $name, $key, $field, $value );
                break;

            case 'post_ajax':
                $this->render_nested_post_ajax( $name, $key, $field, $value );
                break;

            case 'taxonomy_ajax':
                $this->render_nested_taxonomy_ajax( $name, $key, $field, $value );
                break;

            case 'user':
                $this->render_nested_user( $name, $field, $value );
                break;

            case 'tel':
                $this->render_nested_tel( $name, $field, $value );
                break;

            case 'user_ajax':
                $this->render_nested_user_ajax( $name, $key, $field, $value );
                break;

            case 'url':
            case 'email':
            case 'text':
            default:
                $this->render_nested_text( $name, $field, $value, $type );
                break;
        }
    }

    /**
     * Render a nested text input
     *
     * @param string $name  The input name attribute.
     * @param array  $field The field configuration array.
     * @param mixed  $value The current field value.
     * @param string $type  The input type (text, url, email).
     *
     * @return void
     */
    protected function render_nested_text( string $name, array $field, $value, string $type = 'text' ): void {
        $input_type  = in_array( $type, [ 'url', 'email' ], true ) ? $type : 'text';
        $placeholder = ! empty( $field['placeholder'] )
                ? ' placeholder="' . esc_attr( $field['placeholder'] ) . '"'
                : '';
        ?>
        <input type="<?php echo esc_attr( $input_type ); ?>"
               name="<?php echo esc_attr( $name ); ?>"
               value="<?php echo esc_attr( $value ); ?>"
               class="regular-text"
                <?php echo $placeholder; ?> />
        <?php
    }

    /**
     * Render a nested textarea
     *
     * @param string $name  The input name attribute.
     * @param array  $field The field configuration array.
     * @param mixed  $value The current field value.
     *
     * @return void
     */
    protected function render_nested_textarea( string $name, array $field, $value ): void {
        $rows        = absint( $field['rows'] );
        $placeholder = ! empty( $field['placeholder'] )
                ? ' placeholder="' . esc_attr( $field['placeholder'] ) . '"'
                : '';
        ?>
        <textarea name="<?php echo esc_attr( $name ); ?>"
                  rows="<?php echo $rows; ?>"
                  class="large-text"
			<?php echo $placeholder; ?>><?php echo esc_textarea( $value ); ?></textarea>
        <?php
    }

    /**
     * Render a nested number input
     *
     * @param string $name  The input name attribute.
     * @param array  $field The field configuration array.
     * @param mixed  $value The current field value.
     *
     * @return void
     */
    protected function render_nested_number( string $name, array $field, $value ): void {
        $min         = isset( $field['min'] ) ? ' min="' . esc_attr( $field['min'] ) . '"' : '';
        $max         = isset( $field['max'] ) ? ' max="' . esc_attr( $field['max'] ) . '"' : '';
        $step        = isset( $field['step'] ) ? ' step="' . esc_attr( $field['step'] ) . '"' : '';
        $placeholder = ! empty( $field['placeholder'] )
                ? ' placeholder="' . esc_attr( $field['placeholder'] ) . '"'
                : '';
        ?>
        <input type="number"
               name="<?php echo esc_attr( $name ); ?>"
               value="<?php echo esc_attr( $value ); ?>"
               class="small-text"
                <?php echo $min . $max . $step . $placeholder; ?> />
        <?php
    }

    /**
     * Render a nested select dropdown
     *
     * @param string $name  The input name attribute.
     * @param array  $field The field configuration array.
     * @param mixed  $value The current field value.
     *
     * @return void
     */
    protected function render_nested_select( string $name, array $field, $value ): void {
        $options = $this->get_options( $field['options'] );
        ?>
        <select name="<?php echo esc_attr( $name ); ?>">
            <?php foreach ( $options as $option_value => $option_label ) : ?>
                <option value="<?php echo esc_attr( $option_value ); ?>"
                        <?php selected( $value, $option_value ); ?>>
                    <?php echo esc_html( $option_label ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Render a nested checkbox
     *
     * @param string $name  The input name attribute.
     * @param array  $field The field configuration array.
     * @param mixed  $value The current field value.
     *
     * @return void
     */
    protected function render_nested_checkbox( string $name, array $field, $value ): void {
        $checked = ! empty( $value );
        ?>
        <label>
            <input type="checkbox"
                   name="<?php echo esc_attr( $name ); ?>"
                   value="1"
                    <?php checked( $checked ); ?> />
            <?php echo esc_html( $field['label'] ); ?>
        </label>
        <?php
    }

    /**
     * Render a nested radio button group
     *
     * @param string $name  The input name attribute.
     * @param array  $field The field configuration array.
     * @param mixed  $value The current field value.
     *
     * @return void
     */
    protected function render_nested_radio( string $name, array $field, $value ): void {
        $options = $this->get_options( $field['options'] );
        $layout  = $field['layout'] ?? 'vertical';
        ?>
        <div class="arraypress-radio-group arraypress-radio-group--<?php echo esc_attr( $layout ); ?>">
            <?php foreach ( $options as $option_value => $option_label ) : ?>
                <label class="arraypress-radio-item">
                    <input type="radio"
                           name="<?php echo esc_attr( $name ); ?>"
                           value="<?php echo esc_attr( $option_value ); ?>"
                            <?php checked( $value, $option_value ); ?> />
                    <span class="arraypress-radio-label">
                        <?php echo esc_html( $option_label ); ?>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Render a nested button group
     *
     * @param string $name  The input name attribute.
     * @param array  $field The field configuration array.
     * @param mixed  $value The current field value.
     *
     * @return void
     */
    protected function render_nested_button_group( string $name, array $field, $value ): void {
        $options   = $this->get_options( $field['options'] );
        $multiple  = ! empty( $field['multiple'] );
        $name_attr = $multiple ? $name . '[]' : $name;
        $values    = $multiple ? (array) $value : [ $value ];
        $type      = $multiple ? 'checkbox' : 'radio';
        ?>
        <div class="arraypress-button-group<?php echo $multiple ? ' arraypress-button-group--multiple' : ''; ?>">
            <?php foreach ( $options as $option_value => $option_label ) :
                $is_selected = in_array( $option_value, $values, false );
                ?>
                <label class="arraypress-button-group__item<?php echo $is_selected ? ' is-selected' : ''; ?>">
                    <input type="<?php echo esc_attr( $type ); ?>"
                           name="<?php echo esc_attr( $name_attr ); ?>"
                           value="<?php echo esc_attr( $option_value ); ?>"
                            <?php checked( $is_selected ); ?>
                           class="arraypress-button-group__input"/>
                    <span class="arraypress-button-group__label">
                        <?php echo esc_html( $option_label ); ?>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Render a nested range slider
     *
     * @param string $name  The input name attribute.
     * @param array  $field The field configuration array.
     * @param mixed  $value The current field value.
     *
     * @return void
     */
    protected function render_nested_range( string $name, array $field, $value ): void {
        $min         = $field['min'] ?? 0;
        $max         = $field['max'] ?? 100;
        $step        = $field['step'] ?? 1;
        $range_value = $value !== '' ? $value : ( $field['default'] ?? $min );
        $unit        = $field['unit'] ?? '';
        ?>
        <div class="arraypress-range-field" data-unit="<?php echo esc_attr( $unit ); ?>">
            <input type="range"
                   name="<?php echo esc_attr( $name ); ?>"
                   value="<?php echo esc_attr( $range_value ); ?>"
                   min="<?php echo esc_attr( $min ); ?>"
                   max="<?php echo esc_attr( $max ); ?>"
                   step="<?php echo esc_attr( $step ); ?>"
                   class="arraypress-range-input"/>
            <output class="arraypress-range-output">
                <?php echo esc_html( $range_value . $unit ); ?>
            </output>
        </div>
        <?php
    }

    /**
     * Render a nested image picker
     *
     * @param string $name  The input name attribute.
     * @param array  $field The field configuration array.
     * @param mixed  $value The current field value (attachment ID).
     *
     * @return void
     */
    protected function render_nested_image( string $name, array $field, $value ): void {
        $image_url = $value ? wp_get_attachment_image_url( $value, 'thumbnail' ) : '';
        ?>
        <div class="arraypress-media-field arraypress-image-field" data-type="image">
            <input type="hidden"
                   name="<?php echo esc_attr( $name ); ?>"
                   value="<?php echo esc_attr( $value ); ?>"
                   class="arraypress-media-input"/>

            <div class="arraypress-media-preview">
                <?php if ( $image_url ) : ?>
                    <img src="<?php echo esc_url( $image_url ); ?>" alt=""/>
                <?php endif; ?>
            </div>

            <button type="button" class="button arraypress-media-select">
                <?php esc_html_e( 'Select Image', 'arraypress' ); ?>
            </button>

            <button type="button"
                    class="button arraypress-media-remove"
                    <?php echo ! $value ? 'style="display:none;"' : ''; ?>>
                <?php esc_html_e( 'Remove', 'arraypress' ); ?>
            </button>
        </div>
        <?php
    }

    /**
     * Render a nested file picker
     *
     * @param string $name  The input name attribute.
     * @param array  $field The field configuration array.
     * @param mixed  $value The current field value (attachment ID).
     *
     * @return void
     */
    protected function render_nested_file( string $name, array $field, $value ): void {
        $file_url   = $value ? wp_get_attachment_url( $value ) : '';
        $file_name  = $value ? basename( get_attached_file( $value ) ) : '';
        $auto_title = ! empty( $field['auto_title_field'] ) ? $field['auto_title_field'] : '';
        ?>
        <div class="arraypress-media-field arraypress-file-field"
             data-type="file"
                <?php echo $auto_title ? 'data-auto-title-field="' . esc_attr( $auto_title ) . '"' : ''; ?>>

            <div class="arraypress-file-preview">
                <?php if ( $file_name ) : ?>
                    <a href="<?php echo esc_url( $file_url ); ?>" target="_blank">
                        <?php echo esc_html( $file_name ); ?>
                    </a>
                <?php endif; ?>
            </div>

            <button type="button" class="button arraypress-media-select">
                <?php esc_html_e( 'Select File', 'arraypress' ); ?>
            </button>

            <button type="button"
                    class="button arraypress-media-remove"
                    <?php echo ! $value ? 'style="display:none;"' : ''; ?>>
                <?php esc_html_e( 'Remove', 'arraypress' ); ?>
            </button>
        </div>
        <?php
    }

    /**
     * Render a nested file URL field
     *
     * @param string $name  The input name attribute.
     * @param array  $field The field configuration array.
     * @param mixed  $value The current field value (URL string).
     *
     * @return void
     */
    protected function render_nested_file_url( string $name, array $field, $value ): void {
        $button_text = $field['button_text'] ?: __( 'Browse', 'arraypress' );
        $placeholder = $field['placeholder'] ?: __( 'Enter URL or select from media library', 'arraypress' );
        $auto_title  = ! empty( $field['auto_title_field'] ) ? $field['auto_title_field'] : '';
        ?>
        <div class="arraypress-file-url-field"
                <?php echo $auto_title ? 'data-auto-title-field="' . esc_attr( $auto_title ) . '"' : ''; ?>>
            <input type="text"
                   name="<?php echo esc_attr( $name ); ?>"
                   value="<?php echo esc_url( $value ); ?>"
                   class="regular-text arraypress-file-url-input"
                   placeholder="<?php echo esc_attr( $placeholder ); ?>"/>
            <button type="button" class="button arraypress-file-url-browse">
                <?php echo esc_html( $button_text ); ?>
            </button>
        </div>
        <?php
    }

    /**
     * Render a nested AJAX select (custom callback)
     *
     * @param string $name  The input name attribute.
     * @param string $key   The field key.
     * @param array  $field The field configuration array.
     * @param mixed  $value The current field value.
     *
     * @return void
     */
    protected function render_nested_ajax( string $name, string $key, array $field, $value ): void {
        $multiple    = ! empty( $field['multiple'] );
        $placeholder = $field['placeholder'] ?? __( 'Search...', 'arraypress' );
        $name_attr   = $multiple ? $name . '[]' : $name;
        $values      = $multiple ? (array) $value : ( $value ? [ $value ] : [] );
        $values      = array_filter( $values );

        // Get metabox ID from the instance property
        $metabox_id = $this->id;

        // Build the full field key path for nested fields
        $field_key_path = $key;
        if ( ! empty( $this->current_parent_field ) ) {
            $field_key_path = $this->current_parent_field . '.' . $key;
        }
        ?>
        <select class="arraypress-ajax-select<?php echo $multiple ? ' multiple' : ''; ?>"
                name="<?php echo esc_attr( $name_attr ); ?>"
                <?php echo $multiple ? 'multiple' : ''; ?>
                data-metabox-id="<?php echo esc_attr( $metabox_id ); ?>"
                data-field-key="<?php echo esc_attr( $field_key_path ); ?>"
                data-field-type="ajax"
                data-placeholder="<?php echo esc_attr( $placeholder ); ?>">
            <?php foreach ( $values as $val ) : ?>
                <option value="<?php echo esc_attr( $val ); ?>" selected>
                    <?php echo esc_html( $val ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Render a nested post AJAX select
     *
     * @param string $name  The input name attribute.
     * @param string $key   The field key.
     * @param array  $field The field configuration array.
     * @param mixed  $value The current field value.
     *
     * @return void
     */
    protected function render_nested_post_ajax( string $name, string $key, array $field, $value ): void {
        $multiple    = ! empty( $field['multiple'] );
        $placeholder = $field['placeholder'] ?? __( 'Search posts...', 'arraypress' );
        $post_types  = (array) ( $field['post_type'] ?? 'post' );
        $name_attr   = $multiple ? $name . '[]' : $name;
        $values      = $multiple ? (array) $value : ( $value ? [ $value ] : [] );
        $values      = array_filter( $values );

        $metabox_id = $this->id;

        $field_key_path = $key;
        if ( ! empty( $this->current_parent_field ) ) {
            $field_key_path = $this->current_parent_field . '.' . $key;
        }
        ?>
        <select class="arraypress-ajax-select arraypress-post-ajax<?php echo $multiple ? ' multiple' : ''; ?>"
                name="<?php echo esc_attr( $name_attr ); ?>"
                <?php echo $multiple ? 'multiple' : ''; ?>
                data-metabox-id="<?php echo esc_attr( $metabox_id ); ?>"
                data-field-key="<?php echo esc_attr( $field_key_path ); ?>"
                data-field-type="post_ajax"
                data-post-types="<?php echo esc_attr( implode( ',', $post_types ) ); ?>"
                data-placeholder="<?php echo esc_attr( $placeholder ); ?>">
            <?php
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
     * Render a nested taxonomy AJAX select
     *
     * @param string $name  The input name attribute.
     * @param string $key   The field key.
     * @param array  $field The field configuration array.
     * @param mixed  $value The current field value.
     *
     * @return void
     */
    protected function render_nested_taxonomy_ajax( string $name, string $key, array $field, $value ): void {
        $multiple    = ! empty( $field['multiple'] );
        $taxonomy    = $field['taxonomy'] ?? 'category';
        $placeholder = $field['placeholder'] ?? sprintf( __( 'Search %s...', 'arraypress' ), $taxonomy );
        $name_attr   = $multiple ? $name . '[]' : $name;
        $values      = $multiple ? (array) $value : ( $value ? [ $value ] : [] );
        $values      = array_filter( $values );

        $metabox_id = $this->id;

        $field_key_path = $key;
        if ( ! empty( $this->current_parent_field ) ) {
            $field_key_path = $this->current_parent_field . '.' . $key;
        }
        ?>
        <select class="arraypress-ajax-select arraypress-taxonomy-ajax<?php echo $multiple ? ' multiple' : ''; ?>"
                name="<?php echo esc_attr( $name_attr ); ?>"
                <?php echo $multiple ? 'multiple' : ''; ?>
                data-metabox-id="<?php echo esc_attr( $metabox_id ); ?>"
                data-field-key="<?php echo esc_attr( $field_key_path ); ?>"
                data-field-type="taxonomy_ajax"
                data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>"
                data-placeholder="<?php echo esc_attr( $placeholder ); ?>">
            <?php
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
     * Render a nested telephone input
     *
     * @param string $name  The input name attribute.
     * @param array  $field The field configuration array.
     * @param mixed  $value The current field value.
     *
     * @return void
     */
    protected function render_nested_tel( string $name, array $field, $value ): void {
        $placeholder = $field['placeholder'] ?? '';
        ?>
        <input type="tel"
               name="<?php echo esc_attr( $name ); ?>"
               value="<?php echo esc_attr( $value ); ?>"
               class="regular-text"
               placeholder="<?php echo esc_attr( $placeholder ); ?>"/>
        <?php
    }

    /**
     * Render a nested user selector
     *
     * @param string $name  The input name attribute.
     * @param array  $field The field configuration array.
     * @param mixed  $value The current field value (user ID or array of IDs).
     *
     * @return void
     */
    protected function render_nested_user( string $name, array $field, $value ): void {
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

        $multiple  = ! empty( $field['multiple'] );
        $name_attr = $multiple ? $name . '[]' : $name;
        $values    = $multiple ? (array) $value : [ $value ];
        ?>
        <select name="<?php echo esc_attr( $name_attr ); ?>"
                <?php echo $multiple ? 'multiple' : ''; ?>>
            <?php foreach ( $options as $option_value => $option_label ) : ?>
                <option value="<?php echo esc_attr( $option_value ); ?>"
                        <?php echo in_array( $option_value, $values, false ) ? 'selected' : ''; ?>>
                    <?php echo esc_html( $option_label ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Render a nested user AJAX select
     *
     * @param string $name  The input name attribute.
     * @param string $key   The field key.
     * @param array  $field The field configuration array.
     * @param mixed  $value The current field value.
     *
     * @return void
     */
    protected function render_nested_user_ajax( string $name, string $key, array $field, $value ): void {
        $multiple    = ! empty( $field['multiple'] );
        $placeholder = $field['placeholder'] ?? __( 'Search users...', 'arraypress' );
        $roles       = (array) ( $field['role'] ?? [] );
        $name_attr   = $multiple ? $name . '[]' : $name;
        $values      = $multiple ? (array) $value : ( $value ? [ $value ] : [] );
        $values      = array_filter( $values );

        $metabox_id = $this->id;

        $field_key_path = $key;
        if ( ! empty( $this->current_parent_field ) ) {
            $field_key_path = $this->current_parent_field . '.' . $key;
        }
        ?>
        <select class="arraypress-ajax-select arraypress-user-ajax<?php echo $multiple ? ' multiple' : ''; ?>"
                name="<?php echo esc_attr( $name_attr ); ?>"
                <?php echo $multiple ? 'multiple' : ''; ?>
                data-metabox-id="<?php echo esc_attr( $metabox_id ); ?>"
                data-field-key="<?php echo esc_attr( $field_key_path ); ?>"
                data-field-type="user_ajax"
                data-role="<?php echo esc_attr( implode( ',', $roles ) ); ?>"
                data-placeholder="<?php echo esc_attr( $placeholder ); ?>">
            <?php
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