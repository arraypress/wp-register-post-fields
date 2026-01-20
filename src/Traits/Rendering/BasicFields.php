<?php
/**
 * Basic Fields Rendering Trait
 *
 * Handles rendering of basic form field types including text inputs,
 * textareas, numbers, colors, and date/time pickers.
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
 * Trait BasicFields
 *
 * Provides rendering methods for basic field types:
 * - text: Single-line text input
 * - url: URL input with validation
 * - email: Email input with validation
 * - textarea: Multi-line text input
 * - wysiwyg: WordPress rich text editor
 * - code: Code editor with syntax highlighting
 * - number: Numeric input with min/max/step
 * - color: WordPress color picker
 * - date: Date picker input
 * - datetime: Date and time picker
 * - time: Time picker input
 * - date_range: Start and end date pickers
 * - time_range: Start and end time pickers
 * - range: Range slider input
 * - tel: Telephone number input
 * - password: Password input with show/hide toggle
 * - toggle: Visual toggle switch
 * - dimensions: Width × height input
 *
 * @package ArrayPress\RegisterPostFields\Traits\Rendering
 */
trait BasicFields {

    /**
     * Render a text input field
     *
     * Handles text, url, and email input types with appropriate
     * HTML5 validation attributes.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
     * @param mixed  $value    The current field value.
     * @param string $type     The input type (text, url, email).
     *
     * @return void
     */
    protected function render_text( string $meta_key, array $field, $value, string $type = 'text' ): void {
        $input_type  = in_array( $type, [ 'url', 'email' ], true ) ? $type : 'text';
        $placeholder = $this->get_placeholder_attr( $field );
        ?>
        <input type="<?php echo esc_attr( $input_type ); ?>"
               id="<?php echo esc_attr( $meta_key ); ?>"
               name="<?php echo esc_attr( $meta_key ); ?>"
               value="<?php echo esc_attr( $value ); ?>"
               class="regular-text"
                <?php echo $placeholder; ?> />
        <?php
    }

    /**
     * Render a textarea field
     *
     * Multi-line text input with configurable rows.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
     * @param mixed  $value    The current field value.
     *
     * @return void
     */
    protected function render_textarea( string $meta_key, array $field, $value ): void {
        $rows        = absint( $field['rows'] );
        $placeholder = $this->get_placeholder_attr( $field );
        ?>
        <textarea id="<?php echo esc_attr( $meta_key ); ?>"
                  name="<?php echo esc_attr( $meta_key ); ?>"
                  rows="<?php echo $rows; ?>"
                  class="large-text"
            <?php echo $placeholder; ?>><?php echo esc_textarea( $value ); ?></textarea>
        <?php
    }

    /**
     * Render a WYSIWYG editor field
     *
     * WordPress rich text editor with TinyMCE.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
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
     * Render a code editor field
     *
     * Code editor with syntax highlighting using WordPress CodeMirror.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
     * @param mixed  $value    The current field value.
     *
     * @return void
     */
    protected function render_code( string $meta_key, array $field, $value ): void {
        $language     = $field['language'] ?? 'html';
        $line_numbers = $field['line_numbers'] ?? true;
        $rows         = $field['rows'] ?? 10;

        // Map language to CodeMirror mode
        $mode_map = [
                'html'       => 'htmlmixed',
                'css'        => 'css',
                'javascript' => 'javascript',
                'js'         => 'javascript',
                'json'       => 'application/json',
                'php'        => 'php',
                'sql'        => 'sql',
                'xml'        => 'xml',
                'markdown'   => 'markdown',
                'md'         => 'markdown',
        ];

        $mode = $mode_map[ $language ] ?? 'htmlmixed';
        ?>
        <div class="arraypress-code-field"
             data-language="<?php echo esc_attr( $language ); ?>"
             data-mode="<?php echo esc_attr( $mode ); ?>"
             data-line-numbers="<?php echo $line_numbers ? 'true' : 'false'; ?>">
			<textarea id="<?php echo esc_attr( $meta_key ); ?>"
                      name="<?php echo esc_attr( $meta_key ); ?>"
                      rows="<?php echo esc_attr( $rows ); ?>"
                      class="large-text arraypress-code-editor"><?php echo esc_textarea( $value ); ?></textarea>
        </div>
        <?php
    }

    /**
     * Render a number input field
     *
     * Numeric input with optional min, max, and step constraints.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
     * @param mixed  $value    The current field value.
     *
     * @return void
     */
    protected function render_number( string $meta_key, array $field, $value ): void {
        $min         = $this->get_number_attr( 'min', $field );
        $max         = $this->get_number_attr( 'max', $field );
        $step        = $this->get_number_attr( 'step', $field );
        $placeholder = $this->get_placeholder_attr( $field );
        ?>
        <input type="number"
               id="<?php echo esc_attr( $meta_key ); ?>"
               name="<?php echo esc_attr( $meta_key ); ?>"
               value="<?php echo esc_attr( $value ); ?>"
               class="small-text"
                <?php echo $min . $max . $step . $placeholder; ?> />
        <?php
    }

    /**
     * Render a color picker field
     *
     * WordPress color picker with optional default color.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
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
               data-default-color="<?php echo esc_attr( $field['default'] ); ?>" />
        <?php
    }

    /**
     * Render a date/datetime/time input field
     *
     * HTML5 date and time inputs.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
     * @param mixed  $value    The current field value.
     * @param string $type     The input type (date, datetime, time).
     *
     * @return void
     */
    protected function render_datetime( string $meta_key, array $field, $value, string $type ): void {
        // Convert 'datetime' to 'datetime-local' for HTML5
        $input_type = $type === 'datetime' ? 'datetime-local' : $type;
        ?>
        <input type="<?php echo esc_attr( $input_type ); ?>"
               id="<?php echo esc_attr( $meta_key ); ?>"
               name="<?php echo esc_attr( $meta_key ); ?>"
               value="<?php echo esc_attr( $value ); ?>"
               class="regular-text" />
        <?php
    }

    /**
     * Render a date range field
     *
     * Two date inputs for start and end dates.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
     * @param mixed  $value    The current field value (array with 'start' and 'end').
     *
     * @return void
     */
    protected function render_date_range( string $meta_key, array $field, $value ): void {
        $value       = is_array( $value ) ? $value : [];
        $start_value = $value['start'] ?? '';
        $end_value   = $value['end'] ?? '';
        $start_label = $field['start_label'] ?? __( 'Start', 'arraypress' );
        $end_label   = $field['end_label'] ?? __( 'End', 'arraypress' );
        ?>
        <div class="arraypress-range-picker arraypress-date-range">
            <div class="arraypress-range-picker__field">
                <label class="arraypress-range-picker__label">
                    <?php echo esc_html( $start_label ); ?>
                </label>
                <input type="date"
                       id="<?php echo esc_attr( $meta_key ); ?>_start"
                       name="<?php echo esc_attr( $meta_key ); ?>[start]"
                       value="<?php echo esc_attr( $start_value ); ?>"
                       class="arraypress-range-picker__input" />
            </div>
            <span class="arraypress-range-picker__separator">&mdash;</span>
            <div class="arraypress-range-picker__field">
                <label class="arraypress-range-picker__label">
                    <?php echo esc_html( $end_label ); ?>
                </label>
                <input type="date"
                       id="<?php echo esc_attr( $meta_key ); ?>_end"
                       name="<?php echo esc_attr( $meta_key ); ?>[end]"
                       value="<?php echo esc_attr( $end_value ); ?>"
                       class="arraypress-range-picker__input" />
            </div>
        </div>
        <?php
    }

    /**
     * Render a time range field
     *
     * Two time inputs for start and end times.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
     * @param mixed  $value    The current field value (array with 'start' and 'end').
     *
     * @return void
     */
    protected function render_time_range( string $meta_key, array $field, $value ): void {
        $value       = is_array( $value ) ? $value : [];
        $start_value = $value['start'] ?? '';
        $end_value   = $value['end'] ?? '';
        $start_label = $field['start_label'] ?? __( 'Start', 'arraypress' );
        $end_label   = $field['end_label'] ?? __( 'End', 'arraypress' );
        ?>
        <div class="arraypress-range-picker arraypress-time-range">
            <div class="arraypress-range-picker__field">
                <label class="arraypress-range-picker__label">
                    <?php echo esc_html( $start_label ); ?>
                </label>
                <input type="time"
                       id="<?php echo esc_attr( $meta_key ); ?>_start"
                       name="<?php echo esc_attr( $meta_key ); ?>[start]"
                       value="<?php echo esc_attr( $start_value ); ?>"
                       class="arraypress-range-picker__input" />
            </div>
            <span class="arraypress-range-picker__separator">&mdash;</span>
            <div class="arraypress-range-picker__field">
                <label class="arraypress-range-picker__label">
                    <?php echo esc_html( $end_label ); ?>
                </label>
                <input type="time"
                       id="<?php echo esc_attr( $meta_key ); ?>_end"
                       name="<?php echo esc_attr( $meta_key ); ?>[end]"
                       value="<?php echo esc_attr( $end_value ); ?>"
                       class="arraypress-range-picker__input" />
            </div>
        </div>
        <?php
    }

    /**
     * Render a range slider field
     *
     * Range input with live value display output.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
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
        <div class="arraypress-range-field" data-unit="<?php echo esc_attr( $unit ); ?>">
            <input type="range"
                   id="<?php echo esc_attr( $meta_key ); ?>"
                   name="<?php echo esc_attr( $meta_key ); ?>"
                   value="<?php echo esc_attr( $value ); ?>"
                   min="<?php echo esc_attr( $min ); ?>"
                   max="<?php echo esc_attr( $max ); ?>"
                   step="<?php echo esc_attr( $step ); ?>"
                   class="arraypress-range-input" />
            <output class="arraypress-range-output" for="<?php echo esc_attr( $meta_key ); ?>">
                <?php echo esc_html( $value . $unit ); ?>
            </output>
        </div>
        <?php
    }

    /**
     * Render a telephone input field
     *
     * Tel input with optional pattern validation.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
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

    /**
     * Render a password input field
     *
     * Password input with show/hide toggle button.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
     * @param mixed  $value    The current field value.
     *
     * @return void
     */
    protected function render_password( string $meta_key, array $field, $value ): void {
        $placeholder = $field['placeholder'] ?? '';
        ?>
        <div class="arraypress-password-field">
            <input type="password"
                   id="<?php echo esc_attr( $meta_key ); ?>"
                   name="<?php echo esc_attr( $meta_key ); ?>"
                   value="<?php echo esc_attr( $value ); ?>"
                   class="regular-text arraypress-password-input"
                   placeholder="<?php echo esc_attr( $placeholder ); ?>"
                   autocomplete="new-password" />
            <button type="button" class="button arraypress-password-toggle" aria-label="<?php esc_attr_e( 'Toggle password visibility', 'arraypress' ); ?>">
                <span class="dashicons dashicons-visibility"></span>
            </button>
        </div>
        <?php
    }

    /**
     * Render a toggle switch field
     *
     * Visual toggle switch (alternative to checkbox).
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
     * @param mixed  $value    The current field value.
     *
     * @return void
     */
    protected function render_toggle( string $meta_key, array $field, $value ): void {
        $checked    = ! empty( $value );
        $on_label   = $field['on_label'] ?? '';
        $off_label  = $field['off_label'] ?? '';
        $show_label = ! empty( $on_label ) || ! empty( $off_label );
        ?>
        <div class="arraypress-toggle-field">
            <label class="arraypress-toggle">
                <input type="checkbox"
                       id="<?php echo esc_attr( $meta_key ); ?>"
                       name="<?php echo esc_attr( $meta_key ); ?>"
                       value="1"
                       class="arraypress-toggle__input"
                        <?php checked( $checked ); ?> />
                <span class="arraypress-toggle__slider"></span>
            </label>
            <?php if ( $show_label ) : ?>
                <span class="arraypress-toggle__label">
					<span class="arraypress-toggle__label-off"><?php echo esc_html( $off_label ); ?></span>
					<span class="arraypress-toggle__label-on"><?php echo esc_html( $on_label ); ?></span>
				</span>
            <?php endif; ?>
            <?php if ( ! empty( $field['label'] ) ) : ?>
                <span class="arraypress-toggle__text"><?php echo esc_html( $field['label'] ); ?></span>
            <?php endif; ?>
        </div>
        <?php if ( ! empty( $field['description'] ) ) : ?>
            <p class="arraypress-field__description">
                <?php echo esc_html( $field['description'] ); ?>
            </p>
        <?php endif;
    }

    /**
     * Render a dimensions field
     *
     * Combined width × height input.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
     * @param mixed  $value    The current field value (array with 'width' and 'height').
     *
     * @return void
     */
    protected function render_dimensions( string $meta_key, array $field, $value ): void {
        $value        = is_array( $value ) ? $value : [];
        $width_value  = $value['width'] ?? '';
        $height_value = $value['height'] ?? '';
        $labels       = $field['dimension_labels'] ?? [];
        $width_label  = $labels['width'] ?? __( 'Width', 'arraypress' );
        $height_label = $labels['height'] ?? __( 'Height', 'arraypress' );
        $units        = $field['dimension_units'] ?? '';
        $min          = $field['min'] ?? 0;
        $max          = $field['max'] ?? null;
        $step         = $field['step'] ?? 1;
        ?>
        <div class="arraypress-dimensions-field">
            <div class="arraypress-dimensions__input-group">
                <label class="arraypress-dimensions__label" for="<?php echo esc_attr( $meta_key ); ?>_width">
                    <?php echo esc_html( $width_label ); ?>
                </label>
                <input type="number"
                       id="<?php echo esc_attr( $meta_key ); ?>_width"
                       name="<?php echo esc_attr( $meta_key ); ?>[width]"
                       value="<?php echo esc_attr( $width_value ); ?>"
                       class="small-text"
                       min="<?php echo esc_attr( $min ); ?>"
                        <?php echo $max !== null ? 'max="' . esc_attr( $max ) . '"' : ''; ?>
                       step="<?php echo esc_attr( $step ); ?>"
                       placeholder="<?php echo esc_attr( $width_label ); ?>" />
            </div>
            <span class="arraypress-dimensions__separator">&times;</span>
            <div class="arraypress-dimensions__input-group">
                <label class="arraypress-dimensions__label" for="<?php echo esc_attr( $meta_key ); ?>_height">
                    <?php echo esc_html( $height_label ); ?>
                </label>
                <input type="number"
                       id="<?php echo esc_attr( $meta_key ); ?>_height"
                       name="<?php echo esc_attr( $meta_key ); ?>[height]"
                       value="<?php echo esc_attr( $height_value ); ?>"
                       class="small-text"
                       min="<?php echo esc_attr( $min ); ?>"
                        <?php echo $max !== null ? 'max="' . esc_attr( $max ) . '"' : ''; ?>
                       step="<?php echo esc_attr( $step ); ?>"
                       placeholder="<?php echo esc_attr( $height_label ); ?>" />
            </div>
            <?php if ( $units ) : ?>
                <span class="arraypress-dimensions__units"><?php echo esc_html( $units ); ?></span>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Get placeholder attribute string
     *
     * @param array $field The field configuration array.
     *
     * @return string The placeholder attribute or empty string.
     */
    protected function get_placeholder_attr( array $field ): string {
        if ( ! empty( $field['placeholder'] ) ) {
            return ' placeholder="' . esc_attr( $field['placeholder'] ) . '"';
        }

        return '';
    }

    /**
     * Get number attribute string (min, max, or step)
     *
     * @param string $attr  The attribute name (min, max, step).
     * @param array  $field The field configuration array.
     *
     * @return string The attribute string or empty string.
     */
    protected function get_number_attr( string $attr, array $field ): string {
        if ( isset( $field[ $attr ] ) ) {
            return ' ' . $attr . '="' . esc_attr( $field[ $attr ] ) . '"';
        }

        return '';
    }

}