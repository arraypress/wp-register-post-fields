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
 * @version     2.0.0
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
 * - number: Numeric input with min/max/step
 * - color: WordPress color picker
 * - date: Date picker input
 * - datetime: Date and time picker
 * - time: Time picker input
 * - range: Range slider input
 * - tel: Telephone number input
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