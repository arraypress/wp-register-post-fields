<?php
/**
 * Choice Fields Rendering Trait
 *
 * Handles rendering of choice-based form field types including
 * select dropdowns, checkboxes, radio buttons, and button groups.
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
 * Trait ChoiceFields
 *
 * Provides rendering methods for choice-based field types:
 * - select: Dropdown selection (single or multiple)
 * - checkbox: Boolean toggle checkbox
 * - radio: Radio button group
 * - button_group: Toggle button group (single or multiple)
 *
 * @package ArrayPress\RegisterPostFields\Traits\Rendering
 */
trait ChoiceFields {

	/**
	 * Render a select dropdown field
	 *
	 * Supports both single and multiple selection modes.
	 * Options can be provided as an array or callable.
	 *
	 * @param string $meta_key The field's meta key.
	 * @param array  $field    The field configuration array.
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
		        name="<?php echo esc_attr( $name ); ?>"
			<?php echo $multiple; ?>>
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
	 * Render a checkbox field
	 *
	 * Boolean checkbox with label positioned after the input.
	 * Includes its own description handling.
	 *
	 * @param string $meta_key The field's meta key.
	 * @param array  $field    The field configuration array.
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
			<p class="arraypress-field__description">
				<?php echo esc_html( $field['description'] ); ?>
			</p>
		<?php endif;
	}

	/**
	 * Render a radio button group field
	 *
	 * Group of radio buttons with vertical or horizontal layout.
	 *
	 * @param string $meta_key The field's meta key.
	 * @param array  $field    The field configuration array.
	 * @param mixed  $value    The current field value.
	 *
	 * @return void
	 */
	protected function render_radio( string $meta_key, array $field, $value ): void {
		$options = $this->get_options( $field['options'] );
		$layout  = $field['layout'] ?? 'vertical';
		?>
		<div class="arraypress-radio-group arraypress-radio-group--<?php echo esc_attr( $layout ); ?>">
			<?php foreach ( $options as $option_value => $option_label ) : ?>
				<label class="arraypress-radio-item">
					<input type="radio"
					       name="<?php echo esc_attr( $meta_key ); ?>"
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
	 * Render a button group field
	 *
	 * Toggle button group for single or multiple selection.
	 * Uses hidden radio/checkbox inputs with styled button labels.
	 *
	 * @param string $meta_key The field's meta key.
	 * @param array  $field    The field configuration array.
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
						   class="arraypress-button-group__input" />
					<span class="arraypress-button-group__label">
                        <?php echo esc_html( $option_label ); ?>
                    </span>
				</label>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render a group of checkboxes
	 *
	 * Multiple checkboxes in a scrollable container.
	 * Used by relational fields when display mode is 'checkbox'.
	 *
	 * @param string $meta_key The field's meta key.
	 * @param array  $field    The field configuration array.
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
				// Skip empty placeholder option
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
	 * Render either a select dropdown or checkboxes based on field configuration
	 *
	 * Helper method used by relational fields to choose the appropriate
	 * display format based on the 'display' setting.
	 *
	 * @param string $meta_key The field's meta key.
	 * @param array  $field    The field configuration array.
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

}