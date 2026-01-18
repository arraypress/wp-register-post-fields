<?php
/**
 * Field Renderer Trait
 *
 * Main trait that composes all field rendering functionality.
 * This trait acts as the entry point for field rendering and delegates
 * to specialized sub-traits for different field type categories.
 *
 * @package     ArrayPress\RegisterPostFields\Traits
 * @subpackage  Rendering
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL2+
 * @version     2.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterPostFields\Traits;

use ArrayPress\RegisterPostFields\Traits\Rendering\BasicFields;
use ArrayPress\RegisterPostFields\Traits\Rendering\ChoiceFields;
use ArrayPress\RegisterPostFields\Traits\Rendering\MediaFields;
use ArrayPress\RegisterPostFields\Traits\Rendering\RelationalFields;
use ArrayPress\RegisterPostFields\Traits\Rendering\ComplexFields;
use ArrayPress\RegisterPostFields\Traits\Rendering\NestedFields;

/**
 * Trait FieldRenderer
 *
 * Provides the main field rendering interface and composes specialized
 * rendering traits for different field type categories.
 *
 * Field Type Categories:
 * - Basic: text, textarea, number, color, date/time, range, tel
 * - Choice: select, checkbox, radio, button_group
 * - Media: image, file, gallery
 * - Relational: post, user, term, ajax
 * - Complex: amount_type, group, repeater
 *
 * @package ArrayPress\RegisterPostFields\Traits
 */
trait FieldRenderer {

    use BasicFields;
    use ChoiceFields;
    use MediaFields;
    use RelationalFields;
    use ComplexFields;
    use NestedFields;

    /**
     * Render a single field with its wrapper
     *
     * This is the main entry point for rendering any field. It creates
     * the field wrapper with appropriate classes and attributes, renders
     * the label, input, and description.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
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

        // Checkbox label is handled differently (inline with input)
        if ( $type !== 'checkbox' ) {
            echo '<label class="arraypress-field__label" for="' . $id . '">';
            echo esc_html( $field['label'] );
            echo '</label>';
        }

        echo '<div class="arraypress-field__input">';
        $this->render_field_input( $meta_key, $field, $value, $post_id );
        echo '</div>';

        // Description is shown below field (checkbox handles its own)
        if ( ! empty( $field['description'] ) && $type !== 'checkbox' ) {
            echo '<p class="arraypress-field__description">' . esc_html( $field['description'] ) . '</p>';
        }

        echo '</div>';
    }

    /**
     * Render the appropriate input element based on field type
     *
     * Routes the rendering to the appropriate specialized method
     * based on the field type.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
     * @param mixed  $value    The current field value.
     * @param int    $post_id  The post ID.
     *
     * @return void
     */
    protected function render_field_input( string $meta_key, array $field, $value, int $post_id ): void {
        $type = $field['type'];

        switch ( $type ) {
            // Basic text fields
            case 'text':
            case 'url':
            case 'email':
                $this->render_text( $meta_key, $field, $value, $type );
                break;

            case 'textarea':
                $this->render_textarea( $meta_key, $field, $value );
                break;

            case 'wysiwyg':
                $this->render_wysiwyg( $meta_key, $field, $value );
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

            case 'range':
                $this->render_range( $meta_key, $field, $value );
                break;

            case 'tel':
                $this->render_tel( $meta_key, $field, $value );
                break;

            // Choice fields
            case 'select':
                $this->render_select( $meta_key, $field, $value );
                break;

            case 'checkbox':
                $this->render_checkbox( $meta_key, $field, $value );
                break;

            case 'radio':
                $this->render_radio( $meta_key, $field, $value );
                break;

            case 'button_group':
                $this->render_button_group( $meta_key, $field, $value );
                break;

            // Media fields
            case 'image':
                $this->render_image( $meta_key, $field, $value );
                break;

            case 'file':
                $this->render_file( $meta_key, $field, $value );
                break;

            case 'gallery':
                $this->render_gallery( $meta_key, $field, $value );
                break;

            // Relational fields
            case 'post':
                $this->render_post_select( $meta_key, $field, $value );
                break;

            case 'user':
                $this->render_user_select( $meta_key, $field, $value );
                break;

            case 'term':
                $this->render_term_select( $meta_key, $field, $value );
                break;

            case 'ajax':
                $this->render_ajax_select( $meta_key, $field, $value );
                break;

            // Complex fields
            case 'amount_type':
                $this->render_amount_type( $meta_key, $field, $value, $post_id );
                break;

            case 'group':
                $this->render_group( $meta_key, $field, $value, $post_id );
                break;

            case 'repeater':
                $this->render_repeater( $meta_key, $field, $value, $post_id );
                break;

            // Default fallback
            default:
                $this->render_text( $meta_key, $field, $value, 'text' );
                break;
        }
    }

}