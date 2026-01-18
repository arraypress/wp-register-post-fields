<?php
/**
 * Field Renderer Trait
 *
 * Handles rendering of all field types in the metabox.
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
 * Trait FieldRenderer
 *
 * Provides methods for rendering all supported field types.
 */
trait FieldRenderer {

    /**
     * Render a single field.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param mixed  $value    The current field value.
     * @param int    $post_id  The post ID.
     *
     * @return void
     */
    protected function render_field( string $meta_key, array $field, $value, int $post_id ): void {
        $id                = esc_attr( $meta_key );
        $type              = $field['type'];
        $base_class        = 'arraypress-field arraypress-field--' . $type;
        $conditional_class = $this->get_conditional_classes( $field, $post_id );
        $class             = trim( $base_class . ' ' . $conditional_class );
        $data_attrs        = $this->get_conditional_attributes( $field, $meta_key );

        echo '<div class="' . esc_attr( $class ) . '" data-field-key="' . $id . '"' . $data_attrs . '>';

        // Checkbox label is handled differently
        if ( $type !== 'checkbox' ) {
            echo '<label class="arraypress-field__label" for="' . $id . '">';
            echo esc_html( $field['label'] );
            echo '</label>';
        }

        echo '<div class="arraypress-field__input">';
        $this->render_field_input( $meta_key, $field, $value, $post_id );
        echo '</div>';

        if ( ! empty( $field['description'] ) && $type !== 'checkbox' ) {
            echo '<p class="arraypress-field__description">' . esc_html( $field['description'] ) . '</p>';
        }

        echo '</div>';
    }

    /**
     * Render the appropriate input element based on field type.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param mixed  $value    The current field value.
     * @param int    $post_id  The post ID.
     *
     * @return void
     */
    protected function render_field_input( string $meta_key, array $field, $value, int $post_id ): void {
        $type = $field['type'];

        switch ( $type ) {
            case 'textarea':
                $this->render_textarea( $meta_key, $field, $value );
                break;

            case 'wysiwyg':
                $this->render_wysiwyg( $meta_key, $field, $value );
                break;

            case 'select':
                $this->render_select( $meta_key, $field, $value );
                break;

            case 'checkbox':
                $this->render_checkbox( $meta_key, $field, $value );
                break;

            case 'number':
                $this->render_number( $meta_key, $field, $value );
                break;

            case 'color':
                $this->render_color( $meta_key, $field, $value );
                break;

            case 'date':
            case 'datetime':
            case 'time':
                $this->render_datetime( $meta_key, $field, $value, $type );
                break;

            case 'image':
                $this->render_image( $meta_key, $field, $value );
                break;

            case 'file':
                $this->render_file( $meta_key, $field, $value );
                break;

            case 'gallery':
                $this->render_gallery( $meta_key, $field, $value );
                break;

            case 'post':
                $this->render_post_select( $meta_key, $field, $value );
                break;

            case 'user':
                $this->render_user_select( $meta_key, $field, $value );
                break;

            case 'term':
                $this->render_term_select( $meta_key, $field, $value );
                break;

            case 'amount_type':
                $this->render_amount_type( $meta_key, $field, $value, $post_id );
                break;

            case 'group':
                $this->render_group( $meta_key, $field, $value, $post_id );
                break;

            case 'repeater':
                $this->render_repeater( $meta_key, $field, $value, $post_id );
                break;

            case 'ajax':
                $this->render_ajax_select( $meta_key, $field, $value );
                break;

            case 'radio':
                $this->render_radio( $meta_key, $field, $value );
                break;

            case 'button_group':
                $this->render_button_group( $meta_key, $field, $value );
                break;

            case 'range':
                $this->render_range( $meta_key, $field, $value );
                break;

            case 'tel':
                $this->render_tel( $meta_key, $field, $value );
                break;

            case 'url':
            case 'email':
            case 'text':
            default:
                $this->render_text( $meta_key, $field, $value, $type );
                break;
        }
    }

    /**
     * Render a text input field.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param mixed  $value    The current field value.
     * @param string $type     The input type (text, url, email).
     *
     * @return void
     */
    protected function render_text( string $meta_key, array $field, $value, string $type = 'text' ): void {
        $input_type  = in_array( $type, [ 'url', 'email' ], true ) ? $type : 'text';
        $placeholder = ! empty( $field['placeholder'] ) ? ' placeholder="' . esc_attr( $field['placeholder'] ) . '"' : '';
        ?>
        <input type="<?php echo esc_attr( $input_type ); ?>"
               id="<?php echo esc_attr( $meta_key ); ?>"
               name="<?php echo esc_attr( $meta_key ); ?>"
               value="<?php echo esc_attr( $value ); ?>"
               class="regular-text"<?php echo $placeholder; ?> />
        <?php
    }

    /**
     * Render a number input field.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param mixed  $value    The current field value.
     *
     * @return void
     */
    protected function render_number( string $meta_key, array $field, $value ): void {
        $min         = isset( $field['min'] ) ? ' min="' . esc_attr( $field['min'] ) . '"' : '';
        $max         = isset( $field['max'] ) ? ' max="' . esc_attr( $field['max'] ) . '"' : '';
        $step        = isset( $field['step'] ) ? ' step="' . esc_attr( $field['step'] ) . '"' : '';
        $placeholder = ! empty( $field['placeholder'] ) ? ' placeholder="' . esc_attr( $field['placeholder'] ) . '"' : '';
        ?>
        <input type="number"
               id="<?php echo esc_attr( $meta_key ); ?>"
               name="<?php echo esc_attr( $meta_key ); ?>"
               value="<?php echo esc_attr( $value ); ?>"
               class="small-text"<?php echo $min . $max . $step . $placeholder; ?> />
        <?php
    }

    /**
     * Render a textarea field.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param mixed  $value    The current field value.
     *
     * @return void
     */
    protected function render_textarea( string $meta_key, array $field, $value ): void {
        $rows        = absint( $field['rows'] );
        $placeholder = ! empty( $field['placeholder'] ) ? ' placeholder="' . esc_attr( $field['placeholder'] ) . '"' : '';
        ?>
        <textarea id="<?php echo esc_attr( $meta_key ); ?>"
                  name="<?php echo esc_attr( $meta_key ); ?>"
                  rows="<?php echo $rows; ?>"
                  class="large-text"<?php echo $placeholder; ?>><?php echo esc_textarea( $value ); ?></textarea>
        <?php
    }

    /**
     * Render a WYSIWYG editor field.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param mixed  $value    The current field value.
     *
     * @return void
     */
    protected function render_wysiwyg( string $meta_key, array $field, $value ): void {
        $settings = [
                'textarea_name' => $meta_key,
                'textarea_rows' => $field['rows'],
                'media_buttons' => true,
                'teeny'         => false,
                'quicktags'     => true,
        ];

        wp_editor( $value, $meta_key, $settings );
    }

    /**
     * Render a select dropdown field.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param mixed  $value    The current field value.
     *
     * @return void
     */
    protected function render_select( string $meta_key, array $field, $value ): void {
        $options  = $this->get_options( $field['options'] );
        $multiple = $field['multiple'] ? ' multiple' : '';
        $name     = $field['multiple'] ? $meta_key . '[]' : $meta_key;
        $values   = $field['multiple'] ? (array) $value : [ $value ];
        ?>
        <select id="<?php echo esc_attr( $meta_key ); ?>"
                name="<?php echo esc_attr( $name ); ?>"<?php echo $multiple; ?>>
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
     * Render a checkbox field.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param mixed  $value    The current field value.
     *
     * @return void
     */
    protected function render_checkbox( string $meta_key, array $field, $value ): void {
        $checked = ! empty( $value );
        ?>
        <label for="<?php echo esc_attr( $meta_key ); ?>">
            <input type="checkbox"
                   id="<?php echo esc_attr( $meta_key ); ?>"
                   name="<?php echo esc_attr( $meta_key ); ?>"
                   value="1"
                    <?php checked( $checked ); ?> />
            <?php echo esc_html( $field['label'] ); ?>
        </label>
        <?php if ( ! empty( $field['description'] ) ) : ?>
            <p class="arraypress-field__description"><?php echo esc_html( $field['description'] ); ?></p>
        <?php endif;
    }

    /**
     * Render a color picker field.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param mixed  $value    The current field value.
     *
     * @return void
     */
    protected function render_color( string $meta_key, array $field, $value ): void {
        ?>
        <input type="text"
               id="<?php echo esc_attr( $meta_key ); ?>"
               name="<?php echo esc_attr( $meta_key ); ?>"
               value="<?php echo esc_attr( $value ); ?>"
               class="arraypress-color-picker"
               data-default-color="<?php echo esc_attr( $field['default'] ); ?>"/>
        <?php
    }

    /**
     * Render a date/datetime/time input field.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param mixed  $value    The current field value.
     * @param string $type     The input type (date, datetime, time).
     *
     * @return void
     */
    protected function render_datetime( string $meta_key, array $field, $value, string $type ): void {
        $input_type = $type === 'datetime' ? 'datetime-local' : $type;
        ?>
        <input type="<?php echo esc_attr( $input_type ); ?>"
               id="<?php echo esc_attr( $meta_key ); ?>"
               name="<?php echo esc_attr( $meta_key ); ?>"
               value="<?php echo esc_attr( $value ); ?>"
               class="regular-text"/>
        <?php
    }

    /**
     * Render an image picker field.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param mixed  $value    The current field value (attachment ID).
     *
     * @return void
     */
    protected function render_image( string $meta_key, array $field, $value ): void {
        $image_url   = $value ? wp_get_attachment_image_url( $value, 'thumbnail' ) : '';
        $button_text = $field['button_text'] ?: __( 'Select Image', 'arraypress' );
        ?>
        <div class="arraypress-media-field arraypress-image-field" data-type="image">
            <input type="hidden"
                   id="<?php echo esc_attr( $meta_key ); ?>"
                   name="<?php echo esc_attr( $meta_key ); ?>"
                   value="<?php echo esc_attr( $value ); ?>"
                   class="arraypress-media-input"/>
            <div class="arraypress-media-preview">
                <?php if ( $image_url ) : ?>
                    <img src="<?php echo esc_url( $image_url ); ?>" alt=""/>
                <?php endif; ?>
            </div>
            <button type="button" class="button arraypress-media-select">
                <?php echo esc_html( $button_text ); ?>
            </button>
            <button type="button"
                    class="button arraypress-media-remove" <?php echo ! $value ? 'style="display:none;"' : ''; ?>>
                <?php esc_html_e( 'Remove', 'arraypress' ); ?>
            </button>
        </div>
        <?php
    }

    /**
     * Render a file picker field.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param mixed  $value    The current field value (attachment ID).
     *
     * @return void
     */
    protected function render_file( string $meta_key, array $field, $value ): void {
        $file_url    = $value ? wp_get_attachment_url( $value ) : '';
        $file_name   = $value ? basename( get_attached_file( $value ) ) : '';
        $button_text = $field['button_text'] ?: __( 'Select File', 'arraypress' );
        ?>
        <div class="arraypress-media-field arraypress-file-field" data-type="file">
            <input type="hidden"
                   id="<?php echo esc_attr( $meta_key ); ?>"
                   name="<?php echo esc_attr( $meta_key ); ?>"
                   value="<?php echo esc_attr( $value ); ?>"
                   class="arraypress-media-input"/>
            <div class="arraypress-file-preview">
                <?php if ( $file_name ) : ?>
                    <a href="<?php echo esc_url( $file_url ); ?>"
                       target="_blank"><?php echo esc_html( $file_name ); ?></a>
                <?php endif; ?>
            </div>
            <button type="button" class="button arraypress-media-select">
                <?php echo esc_html( $button_text ); ?>
            </button>
            <button type="button"
                    class="button arraypress-media-remove" <?php echo ! $value ? 'style="display:none;"' : ''; ?>>
                <?php esc_html_e( 'Remove', 'arraypress' ); ?>
            </button>
        </div>
        <?php
    }

    /**
     * Render a gallery field.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param mixed  $value    The current field value (array of attachment IDs).
     *
     * @return void
     */
    protected function render_gallery( string $meta_key, array $field, $value ): void {
        $value       = is_array( $value ) ? $value : [];
        $button_text = $field['button_text'] ?: __( 'Add Images', 'arraypress' );
        $max         = $field['max_items'] ?: 0;
        ?>
        <div class="arraypress-gallery-field" data-max="<?php echo esc_attr( $max ); ?>">
            <input type="hidden"
                   id="<?php echo esc_attr( $meta_key ); ?>"
                   name="<?php echo esc_attr( $meta_key ); ?>"
                   value="<?php echo esc_attr( implode( ',', $value ) ); ?>"
                   class="arraypress-gallery-input"/>
            <div class="arraypress-gallery-preview">
                <?php foreach ( $value as $attachment_id ) :
                    $image_url = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );
                    if ( $image_url ) : ?>
                        <div class="arraypress-gallery-item" data-id="<?php echo esc_attr( $attachment_id ); ?>">
                            <img src="<?php echo esc_url( $image_url ); ?>" alt=""/>
                            <button type="button" class="arraypress-gallery-remove">&times;</button>
                        </div>
                    <?php endif;
                endforeach; ?>
            </div>
            <button type="button" class="button arraypress-gallery-add">
                <?php echo esc_html( $button_text ); ?>
            </button>
        </div>
        <?php
    }

    /**
     * Render a post selector field.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param mixed  $value    The current field value.
     *
     * @return void
     */
    protected function render_post_select( string $meta_key, array $field, $value ): void {
        $post_types = (array) $field['post_type'];
        $posts      = get_posts( [
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
     * Render a user selector field.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param mixed  $value    The current field value.
     *
     * @return void
     */
    protected function render_user_select( string $meta_key, array $field, $value ): void {
        $args = [
                'orderby' => 'display_name',
                'order'   => 'ASC',
        ];

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
     * Render a term selector field.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param mixed  $value    The current field value.
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
     * Render either a select dropdown or checkboxes based on field configuration.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param mixed  $value    The current field value.
     * @param array  $options  The options to render.
     *
     * @return void
     */
    protected function render_select_or_checkboxes( string $meta_key, array $field, $value, array $options ): void {
        if ( $field['multiple'] && $field['display'] === 'checkbox' ) {
            $this->render_checkbox_group( $meta_key, $field, $value, $options );
        } else {
            $field['options'] = $options;
            $this->render_select( $meta_key, $field, $value );
        }
    }

    /**
     * Render a group of checkboxes.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param mixed  $value    The current field value.
     * @param array  $options  The options to render.
     *
     * @return void
     */
    protected function render_checkbox_group( string $meta_key, array $field, $value, array $options ): void {
        $values = (array) $value;
        ?>
        <div class="arraypress-checkbox-group">
            <?php foreach ( $options as $option_value => $option_label ) :
                if ( $option_value === '' ) {
                    continue;
                }
                $checked = in_array( $option_value, $values, false );
                ?>
                <label class="arraypress-checkbox-item">
                    <input type="checkbox"
                           name="<?php echo esc_attr( $meta_key ); ?>[]"
                           value="<?php echo esc_attr( $option_value ); ?>"
                            <?php checked( $checked ); ?> />
                    <?php echo esc_html( $option_label ); ?>
                </label>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Render an amount type field (combined amount input + type selector).
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param mixed  $value    The current amount value.
     * @param int    $post_id  The post ID.
     *
     * @return void
     */
    protected function render_amount_type( string $meta_key, array $field, $value, int $post_id ): void {
        $type_options = $this->get_options( $field['type_options'] );
        $type_key     = $field['type_meta_key'];
        $type_value   = get_post_meta( $post_id, $type_key, true );

        if ( empty( $type_value ) && ! empty( $field['type_default'] ) ) {
            $type_value = $field['type_default'];
        }

        $min  = isset( $field['min'] ) ? ' min="' . esc_attr( $field['min'] ) . '"' : ' min="0"';
        $max  = isset( $field['max'] ) ? ' max="' . esc_attr( $field['max'] ) . '"' : '';
        $step = isset( $field['step'] ) ? ' step="' . esc_attr( $field['step'] ) . '"' : ' step="any"';
        ?>
        <div class="arraypress-amount-type">
            <input type="number"
                   id="<?php echo esc_attr( $meta_key ); ?>"
                   name="<?php echo esc_attr( $meta_key ); ?>"
                   value="<?php echo esc_attr( $value ); ?>"
                   class="small-text"
                    <?php echo $min . $max . $step; ?> />
            <select name="<?php echo esc_attr( $type_key ); ?>">
                <?php foreach ( $type_options as $option_value => $option_label ) : ?>
                    <option value="<?php echo esc_attr( $option_value ); ?>"
                            <?php selected( $type_value, $option_value ); ?>>
                        <?php echo esc_html( $option_label ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    /**
     * Render an AJAX-powered select field.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
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

        // Get metabox ID from the config
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
            // Pre-populate with existing values (labels will be hydrated via AJAX)
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
     * Render a group field.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param mixed  $value    The current field value.
     * @param int    $post_id  The post ID.
     *
     * @return void
     */
    protected function render_group( string $meta_key, array $field, $value, int $post_id ): void {
        $value = is_array( $value ) ? $value : [];
        ?>
        <div class="arraypress-group">
            <?php foreach ( $field['fields'] as $sub_key => $sub_field ) :
                $sub_value = $value[ $sub_key ] ?? $sub_field['default'];
                $sub_name = $meta_key . '[' . $sub_key . ']';

                // Get conditional attributes for nested field
                $conditional_class = '';
                $data_attrs        = '';
                if ( ! empty( $sub_field['show_when'] ) ) {
                    $conditional_class = ' arraypress-conditional-field';
                    $data_attrs        = $this->get_conditional_attributes( $sub_field, $sub_key );
                }
                ?>
                <div class="arraypress-group__field<?php echo $conditional_class; ?>"
                     data-field-key="<?php echo esc_attr( $sub_key ); ?>"<?php echo $data_attrs; ?>>
                    <label class="arraypress-group__label">
                        <?php echo esc_html( $sub_field['label'] ); ?>
                    </label>
                    <?php $this->render_nested_field_input( $sub_name, $sub_key, $sub_field, $sub_value ); ?>
                    <?php if ( ! empty( $sub_field['description'] ) ) : ?>
                        <p class="arraypress-field__description"><?php echo esc_html( $sub_field['description'] ); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Render a repeater field.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param mixed  $value    The current field value.
     * @param int    $post_id  The post ID.
     *
     * @return void
     */
    protected function render_repeater( string $meta_key, array $field, $value, int $post_id ): void {
        $value        = is_array( $value ) ? $value : [];
        $button_label = $field['button_label'] ?: __( 'Add Row', 'arraypress' );
        $max          = $field['max_items'] ?: 0;
        $min          = $field['min_items'] ?: 0;
        $layout       = $field['layout'] ?? 'vertical'; // vertical, horizontal, table

        $layout_class = 'arraypress-repeater--' . $layout;
        ?>
        <div class="arraypress-repeater <?php echo esc_attr( $layout_class ); ?>"
             data-meta-key="<?php echo esc_attr( $meta_key ); ?>"
             data-max="<?php echo esc_attr( $max ); ?>"
             data-min="<?php echo esc_attr( $min ); ?>"
             data-layout="<?php echo esc_attr( $layout ); ?>">

            <?php if ( $layout === 'table' ) : ?>
                <?php $this->render_repeater_table( $meta_key, $field, $value, $post_id ); ?>
            <?php else : ?>
                <?php $this->render_repeater_standard( $meta_key, $field, $value, $post_id, $layout ); ?>
            <?php endif; ?>

            <button type="button" class="button arraypress-repeater__add">
                <?php echo esc_html( $button_label ); ?>
            </button>
        </div>
        <?php
    }

    /**
     * Render standard repeater layout (vertical or horizontal).
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param array  $value    The current field values.
     * @param int    $post_id  The post ID.
     * @param string $layout   The layout type.
     *
     * @return void
     */
    protected function render_repeater_standard( string $meta_key, array $field, array $value, int $post_id, string $layout ): void {
        $collapsed = $field['collapsed'] ?? false;
        ?>
        <div class="arraypress-repeater__rows">
            <?php
            $index = 0;
            foreach ( $value as $row_value ) :
                $this->render_repeater_row( $meta_key, $field, $row_value, $index, $layout );
                $index ++;
            endforeach;
            ?>
        </div>

        <div class="arraypress-repeater__template" style="display:none;">
            <?php $this->render_repeater_row( $meta_key, $field, [], '__INDEX__', $layout ); ?>
        </div>
        <?php
    }

    /**
     * Render table layout repeater.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param array  $value    The current field values.
     * @param int    $post_id  The post ID.
     *
     * @return void
     */
    protected function render_repeater_table( string $meta_key, array $field, array $value, int $post_id ): void {
        $has_rows = ! empty( $value );
        ?>
        <table class="arraypress-repeater__table widefat">
            <thead>
            <tr>
                <th class="arraypress-repeater__table-handle"></th>
                <?php foreach ( $field['fields'] as $sub_key => $sub_field ) :
                    $width = isset( $sub_field['width'] ) ? 'style="width:' . esc_attr( $sub_field['width'] ) . '"' : '';
                    ?>
                    <th <?php echo $width; ?>><?php echo esc_html( $sub_field['label'] ); ?></th>
                <?php endforeach; ?>
                <th class="arraypress-repeater__table-actions"></th>
            </tr>
            </thead>
            <tbody class="arraypress-repeater__rows">
            <?php if ( ! $has_rows ) : ?>
                <tr class="arraypress-repeater__empty-row">
                    <td colspan="<?php echo esc_attr( count( $field['fields'] ) + 2 ); ?>">
                        <?php esc_html_e( 'No items yet. Click the button below to add one.', 'arraypress' ); ?>
                    </td>
                </tr>
            <?php endif; ?>
            <?php
            $index = 0;
            foreach ( $value as $row_value ) :
                $this->render_repeater_table_row( $meta_key, $field, $row_value, $index );
                $index ++;
            endforeach;
            ?>
            </tbody>
        </table>
        <!-- template unchanged -->
        <?php
    }

    /**
     * Render a single repeater row (vertical or horizontal layout).
     *
     * @param string     $meta_key The field's meta key.
     * @param array      $field    The field configuration.
     * @param array      $value    The row values.
     * @param int|string $index    The row index.
     * @param string     $layout   The layout type.
     *
     * @return void
     */
    protected function render_repeater_row( string $meta_key, array $field, array $value, $index, string $layout = 'vertical' ): void {
        $collapsed     = $field['collapsed'] ?? false;
        $is_horizontal = $layout === 'horizontal';
        ?>
        <div class="arraypress-repeater__row<?php echo $collapsed ? ' is-collapsed' : ''; ?><?php echo $is_horizontal ? ' arraypress-repeater__row--horizontal' : ''; ?>"
             data-index="<?php echo esc_attr( $index ); ?>">
            <div class="arraypress-repeater__row-header">
                <span class="arraypress-repeater__row-handle">☰</span>
                <span class="arraypress-repeater__row-title">
                <?php printf( __( 'Item %s', 'arraypress' ), is_numeric( $index ) ? $index + 1 : '#' ); ?>
            </span>
                <?php if ( ! $is_horizontal ) : ?>
                    <button type="button" class="arraypress-repeater__row-toggle">▼</button>
                <?php endif; ?>
                <button type="button" class="arraypress-repeater__row-remove">&times;</button>
            </div>
            <div class="arraypress-repeater__row-content">
                <?php foreach ( $field['fields'] as $sub_key => $sub_field ) :
                    $sub_value = $value[ $sub_key ] ?? $sub_field['default'];
                    $sub_name = $meta_key . '[' . $index . '][' . $sub_key . ']';
                    $width = isset( $sub_field['width'] ) ? 'style="width:' . esc_attr( $sub_field['width'] ) . '"' : '';

                    // Get conditional attributes for nested field
                    $conditional_class = '';
                    $data_attrs        = '';
                    if ( ! empty( $sub_field['show_when'] ) ) {
                        $conditional_class = ' arraypress-conditional-field';
                        $data_attrs        = $this->get_conditional_attributes( $sub_field, $sub_key );
                    }
                    ?>
                    <div class="arraypress-repeater__field<?php echo $conditional_class; ?>"
                         data-field-key="<?php echo esc_attr( $sub_key ); ?>"
                            <?php echo $width; ?>
                            <?php echo $data_attrs; ?>>
                        <?php if ( ! $is_horizontal ) : ?>
                            <label class="arraypress-repeater__field-label">
                                <?php echo esc_html( $sub_field['label'] ); ?>
                            </label>
                        <?php endif; ?>
                        <?php $this->render_nested_field_input( $sub_name, $sub_key, $sub_field, $sub_value ); ?>
                        <?php if ( ! empty( $sub_field['description'] ) && ! $is_horizontal ) : ?>
                            <p class="arraypress-field__description"><?php echo esc_html( $sub_field['description'] ); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render a single table row for repeater.
     *
     * @param string     $meta_key The field's meta key.
     * @param array      $field    The field configuration.
     * @param array      $value    The row values.
     * @param int|string $index    The row index.
     *
     * @return void
     */
    protected function render_repeater_table_row( string $meta_key, array $field, array $value, $index ): void {
        ?>
        <tr class="arraypress-repeater__row" data-index="<?php echo esc_attr( $index ); ?>">
            <td class="arraypress-repeater__table-handle">
                <span class="arraypress-repeater__row-handle">☰</span>
            </td>
            <?php foreach ( $field['fields'] as $sub_key => $sub_field ) :
                $sub_value = $value[ $sub_key ] ?? $sub_field['default'];
                $sub_name = $meta_key . '[' . $index . '][' . $sub_key . ']';

                // Get conditional attributes for nested field
                $conditional_class = '';
                $data_attrs        = '';
                if ( ! empty( $sub_field['show_when'] ) ) {
                    $conditional_class = ' arraypress-conditional-field';
                    $data_attrs        = $this->get_conditional_attributes( $sub_field, $sub_key );
                }
                ?>
                <td class="arraypress-repeater__field<?php echo $conditional_class; ?>"
                    data-field-key="<?php echo esc_attr( $sub_key ); ?>"
                        <?php echo $data_attrs; ?>>
                    <?php $this->render_nested_field_input( $sub_name, $sub_key, $sub_field, $sub_value ); ?>
                </td>
            <?php endforeach; ?>
            <td class="arraypress-repeater__table-actions">
                <button type="button" class="arraypress-repeater__row-remove">&times;</button>
            </td>
        </tr>
        <?php
    }

    /**
     * Render a nested field input (for groups and repeaters).
     *
     * @param string $name  The input name attribute.
     * @param string $key   The field key.
     * @param array  $field The field configuration.
     * @param mixed  $value The current field value.
     *
     * @return void
     */
    protected function render_nested_field_input( string $name, string $key, array $field, $value ): void {
        $type                = $field['type'];

        // Only support simple field types in nested contexts
        switch ( $type ) {
            case 'textarea':
                $rows = absint( $field['rows'] );
                $placeholder = ! empty( $field['placeholder'] ) ? ' placeholder="' . esc_attr( $field['placeholder'] ) . '"' : '';
                ?>
                <textarea name="<?php echo esc_attr( $name ); ?>"
                          rows="<?php echo $rows; ?>"
                          class="large-text"<?php echo $placeholder; ?>><?php echo esc_textarea( $value ); ?></textarea>
                <?php
                break;

            case 'number':
                $min = isset( $field['min'] ) ? ' min="' . esc_attr( $field['min'] ) . '"' : '';
                $max         = isset( $field['max'] ) ? ' max="' . esc_attr( $field['max'] ) . '"' : '';
                $step        = isset( $field['step'] ) ? ' step="' . esc_attr( $field['step'] ) . '"' : '';
                $placeholder = ! empty( $field['placeholder'] ) ? ' placeholder="' . esc_attr( $field['placeholder'] ) . '"' : '';
                ?>
                <input type="number"
                       name="<?php echo esc_attr( $name ); ?>"
                       value="<?php echo esc_attr( $value ); ?>"
                       class="small-text"<?php echo $min . $max . $step . $placeholder; ?> />
                <?php
                break;

            case 'select':
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
                break;

            case 'checkbox':
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
                break;

            case 'image':
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
                            class="button arraypress-media-remove" <?php echo ! $value ? 'style="display:none;"' : ''; ?>>
                        <?php esc_html_e( 'Remove', 'arraypress' ); ?>
                    </button>
                </div>
                <?php
                break;

            case 'radio':
                $options = $this->get_options( $field['options'] );
                $layout      = $field['layout'] ?? 'vertical';
                ?>
                <div class="arraypress-radio-group arraypress-radio-group--<?php echo esc_attr( $layout ); ?>">
                    <?php foreach ( $options as $option_value => $option_label ) : ?>
                        <label class="arraypress-radio-item">
                            <input type="radio"
                                   name="<?php echo esc_attr( $name ); ?>"
                                   value="<?php echo esc_attr( $option_value ); ?>"
                                    <?php checked( $value, $option_value ); ?> />
                            <span class="arraypress-radio-label"><?php echo esc_html( $option_label ); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?php
                break;

            case 'button_group':
                $options = $this->get_options( $field['options'] );
                $multiple    = ! empty( $field['multiple'] );
                $name_attr   = $multiple ? $name . '[]' : $name;
                $values      = $multiple ? (array) $value : [ $value ];
                $type        = $multiple ? 'checkbox' : 'radio';
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
                            <span class="arraypress-button-group__label"><?php echo esc_html( $option_label ); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?php
                break;

            case 'range':
                $min = $field['min'] ?? 0;
                $max         = $field['max'] ?? 100;
                $step        = $field['step'] ?? 1;
                $range_value = $value !== '' ? $value : ( $field['default'] ?? $min );
                $unit        = $field['unit'] ?? '';
                ?>
                <div class="arraypress-range-field">
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
                break;

            case 'tel':
                $placeholder = $field['placeholder'] ?? '';
                ?>
                <input type="tel"
                       name="<?php echo esc_attr( $name ); ?>"
                       value="<?php echo esc_attr( $value ); ?>"
                       class="regular-text"
                       placeholder="<?php echo esc_attr( $placeholder ); ?>"/>
                <?php
                break;

            case 'file':
                $file_url = $value ? wp_get_attachment_url( $value ) : '';
                $file_name   = $value ? basename( get_attached_file( $value ) ) : '';
                ?>
                <div class="arraypress-media-field arraypress-file-field" data-type="file">
                    <input type="hidden"
                           name="<?php echo esc_attr( $name ); ?>"
                           value="<?php echo esc_attr( $value ); ?>"
                           class="arraypress-media-input"/>
                    <div class="arraypress-file-preview">
                        <?php if ( $file_name ) : ?>
                            <a href="<?php echo esc_url( $file_url ); ?>"
                               target="_blank"><?php echo esc_html( $file_name ); ?></a>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="button arraypress-media-select">
                        <?php esc_html_e( 'Select File', 'arraypress' ); ?>
                    </button>
                    <button type="button"
                            class="button arraypress-media-remove" <?php echo ! $value ? 'style="display:none;"' : ''; ?>>
                        <?php esc_html_e( 'Remove', 'arraypress' ); ?>
                    </button>
                </div>
                <?php
                break;

            case 'ajax':
                $multiple = ! empty( $field['multiple'] );
                $placeholder = $field['placeholder'] ?? 'Search...';
                $name_attr   = $multiple ? $name . '[]' : $name;
                $values      = $multiple ? (array) $value : ( $value ? [ $value ] : [] );
                $values      = array_filter( $values );

                // Get metabox ID - need to pass this through or access from $this
                $metabox_id = $this->id;
                ?>
                <select class="arraypress-ajax-select<?php echo $multiple ? ' multiple' : ''; ?>"
                        name="<?php echo esc_attr( $name_attr ); ?>"
                        <?php echo $multiple ? 'multiple' : ''; ?>
                        data-metabox-id="<?php echo esc_attr( $metabox_id ); ?>"
                        data-field-key="<?php echo esc_attr( $key ); ?>"
                        data-placeholder="<?php echo esc_attr( $placeholder ); ?>">
                    <?php foreach ( $values as $val ) : ?>
                        <option value="<?php echo esc_attr( $val ); ?>" selected>
                            <?php echo esc_html( $val ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php
                break;

            case 'url':
            case 'email':
            case 'text':
            default:
                $input_type = in_array( $type, [ 'url', 'email' ], true ) ? $type : 'text';
                $placeholder = ! empty( $field['placeholder'] ) ? ' placeholder="' . esc_attr( $field['placeholder'] ) . '"' : '';
                ?>
                <input type="<?php echo esc_attr( $input_type ); ?>"
                       name="<?php echo esc_attr( $name ); ?>"
                       value="<?php echo esc_attr( $value ); ?>"
                       class="regular-text"<?php echo $placeholder; ?> />
                <?php
                break;
        }
    }

    /**
     * Render a radio button group field.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param mixed  $value    The current field value.
     *
     * @return void
     */
    protected function render_radio( string $meta_key, array $field, $value ): void {
        $options = $this->get_options( $field['options'] );
        $layout  = $field['layout'] ?? 'vertical'; // vertical or horizontal
        ?>
        <div class="arraypress-radio-group arraypress-radio-group--<?php echo esc_attr( $layout ); ?>">
            <?php foreach ( $options as $option_value => $option_label ) : ?>
                <label class="arraypress-radio-item">
                    <input type="radio"
                           name="<?php echo esc_attr( $meta_key ); ?>"
                           value="<?php echo esc_attr( $option_value ); ?>"
                            <?php checked( $value, $option_value ); ?> />
                    <span class="arraypress-radio-label"><?php echo esc_html( $option_label ); ?></span>
                </label>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Render a button group field (toggle buttons).
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param mixed  $value    The current field value.
     *
     * @return void
     */
    protected function render_button_group( string $meta_key, array $field, $value ): void {
        $options  = $this->get_options( $field['options'] );
        $multiple = ! empty( $field['multiple'] );
        $name     = $multiple ? $meta_key . '[]' : $meta_key;
        $values   = $multiple ? (array) $value : [ $value ];
        $type     = $multiple ? 'checkbox' : 'radio';
        ?>
        <div class="arraypress-button-group<?php echo $multiple ? ' arraypress-button-group--multiple' : ''; ?>">
            <?php foreach ( $options as $option_value => $option_label ) :
                $is_selected = in_array( $option_value, $values, false );
                ?>
                <label class="arraypress-button-group__item<?php echo $is_selected ? ' is-selected' : ''; ?>">
                    <input type="<?php echo esc_attr( $type ); ?>"
                           name="<?php echo esc_attr( $name ); ?>"
                           value="<?php echo esc_attr( $option_value ); ?>"
                            <?php checked( $is_selected ); ?>
                           class="arraypress-button-group__input"/>
                    <span class="arraypress-button-group__label"><?php echo esc_html( $option_label ); ?></span>
                </label>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Render a range slider field.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param mixed  $value    The current field value.
     *
     * @return void
     */
    protected function render_range( string $meta_key, array $field, $value ): void {
        $min   = $field['min'] ?? 0;
        $max   = $field['max'] ?? 100;
        $step  = $field['step'] ?? 1;
        $value = $value !== '' ? $value : ( $field['default'] ?? $min );
        $unit  = $field['unit'] ?? '';
        ?>
        <div class="arraypress-range-field">
            <input type="range"
                   id="<?php echo esc_attr( $meta_key ); ?>"
                   name="<?php echo esc_attr( $meta_key ); ?>"
                   value="<?php echo esc_attr( $value ); ?>"
                   min="<?php echo esc_attr( $min ); ?>"
                   max="<?php echo esc_attr( $max ); ?>"
                   step="<?php echo esc_attr( $step ); ?>"
                   class="arraypress-range-input"/>
            <output class="arraypress-range-output" for="<?php echo esc_attr( $meta_key ); ?>">
                <?php echo esc_html( $value . $unit ); ?>
            </output>
        </div>
        <?php
    }

    /**
     * Render a telephone input field.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration.
     * @param mixed  $value    The current field value.
     *
     * @return void
     */
    protected function render_tel( string $meta_key, array $field, $value ): void {
        $placeholder = $field['placeholder'] ?? '';
        $pattern     = $field['pattern'] ?? '';
        ?>
        <input type="tel"
               id="<?php echo esc_attr( $meta_key ); ?>"
               name="<?php echo esc_attr( $meta_key ); ?>"
               value="<?php echo esc_attr( $value ); ?>"
               class="regular-text"
               placeholder="<?php echo esc_attr( $placeholder ); ?>"
                <?php echo $pattern ? 'pattern="' . esc_attr( $pattern ) . '"' : ''; ?> />
        <?php
    }

}
