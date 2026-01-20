<?php
/**
 * Complex Fields Rendering Trait
 *
 * Handles rendering of complex composite form field types including
 * amount/type combinations, field groups, and repeatable field sets.
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
 * Trait ComplexFields
 *
 * Provides rendering methods for complex field types:
 * - amount_type: Combined numeric input with type selector (e.g., discount amount + percent/flat)
 * - group: Static group of related fields stored as a single meta value
 * - repeater: Dynamic repeatable group of fields with add/remove/reorder
 *
 * These fields can contain nested sub-fields and require special
 * handling for saving and displaying their values.
 *
 * @package ArrayPress\RegisterPostFields\Traits\Rendering
 */
trait ComplexFields {

    /**
     * Render an amount type field
     *
     * Combined numeric input with a type selector dropdown.
     * Useful for values like discounts (amount + percent/flat).
     * Stores the amount and type in separate meta keys.
     *
     * @param string $meta_key The field's meta key for the amount value.
     * @param array  $field    The field configuration array.
     * @param mixed  $value    The current amount value.
     * @param int    $post_id  The post ID.
     *
     * @return void
     */
    protected function render_amount_type( string $meta_key, array $field, $value, int $post_id ): void {
        $type_options = $this->get_options( $field['type_options'] );
        $type_key     = $field['type_meta_key'];
        $type_value   = get_post_meta( $post_id, $type_key, true );

        // Use default type if none set
        if ( empty( $type_value ) && ! empty( $field['type_default'] ) ) {
            $type_value = $field['type_default'];
        }

        // Build number input attributes
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
     * Render a group field
     *
     * Static group of related fields stored as an associative array.
     * Useful for grouping related data like dimensions (width, height, depth).
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
     * @param mixed  $value    The current field value (associative array).
     * @param int    $post_id  The post ID.
     *
     * @return void
     */
    protected function render_group( string $meta_key, array $field, $value, int $post_id ): void {
        $value = is_array( $value ) ? $value : [];

        // Set parent field context for nested AJAX fields
        $this->set_parent_field_context( $meta_key );
        ?>
        <div class="arraypress-group">
            <?php foreach ( $field['fields'] as $sub_key => $sub_field ) :
                $sub_value = $value[ $sub_key ] ?? $sub_field['default'];
                $sub_name = $meta_key . '[' . $sub_key . ']';

                // Build conditional attributes for nested field
                $conditional_class = '';
                $data_attrs        = '';
                if ( ! empty( $sub_field['show_when'] ) ) {
                    $conditional_class = ' arraypress-conditional-field';
                    $data_attrs        = $this->get_conditional_attributes( $sub_field, $sub_key );
                }
                ?>
                <div class="arraypress-group__field<?php echo $conditional_class; ?>"
                     data-field-key="<?php echo esc_attr( $sub_key ); ?>"
                        <?php echo $data_attrs; ?>>

                    <label class="arraypress-group__label">
                        <?php echo esc_html( $sub_field['label'] ); ?>
                        <?php $this->render_tooltip( $sub_field ); ?>
                    </label>

                    <?php $this->render_nested_field_input( $sub_name, $sub_key, $sub_field, $sub_value ); ?>

                    <?php if ( ! empty( $sub_field['description'] ) ) : ?>
                        <p class="arraypress-field__description">
                            <?php echo esc_html( $sub_field['description'] ); ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        // Clear parent field context
        $this->set_parent_field_context( null );
    }

    /**
     * Render a repeater field
     *
     * Dynamic repeatable group of fields with add, remove, and
     * drag-and-drop reordering. Supports three layout modes:
     * vertical (default), horizontal, and table.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
     * @param mixed  $value    The current field value (array of row arrays).
     * @param int    $post_id  The post ID.
     *
     * @return void
     */
    protected function render_repeater( string $meta_key, array $field, $value, int $post_id ): void {
        $value        = is_array( $value ) ? $value : [];
        $button_label = $field['button_label'] ?: __( 'Add Row', 'arraypress' );
        $max          = $field['max_items'] ?: 0;
        $min          = $field['min_items'] ?: 0;
        $layout       = $field['layout'] ?? 'vertical';
        $layout_class = 'arraypress-repeater--' . $layout;
        $row_title    = $field['row_title'] ?? '';

        // Set parent field context for nested AJAX fields
        $this->set_parent_field_context( $meta_key );
        ?>
        <div class="arraypress-repeater <?php echo esc_attr( $layout_class ); ?><?php echo ! empty( $field['full_width'] ) ? ' arraypress-repeater--full-width' : ''; ?>"
             data-meta-key="<?php echo esc_attr( $meta_key ); ?>"
             data-max="<?php echo esc_attr( $max ); ?>"
             data-min="<?php echo esc_attr( $min ); ?>"
             data-layout="<?php echo esc_attr( $layout ); ?>"
             data-row-title="<?php echo esc_attr( $row_title ); ?>"
             data-row-title-field="<?php echo esc_attr( $field['row_title_field'] ?? '' ); ?>">

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
        // Clear parent field context
        $this->set_parent_field_context( null );
    }

    /**
     * Render standard repeater layout (vertical or horizontal)
     *
     * Renders rows as collapsible panels with header and content areas.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
     * @param array  $value    The current field values (array of rows).
     * @param int    $post_id  The post ID.
     * @param string $layout   The layout type (vertical or horizontal).
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

        <!-- Template for JavaScript to clone when adding new rows -->
        <div class="arraypress-repeater__template" style="display:none;">
            <?php $this->render_repeater_row( $meta_key, $field, [], '__INDEX__', $layout ); ?>
        </div>
        <?php
    }

    /**
     * Render table layout repeater
     *
     * Renders rows as table rows with column headers.
     * More compact display for simple repeater structures.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
     * @param array  $value    The current field values (array of rows).
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
                    $width = isset( $sub_field['width'] )
                            ? 'style="width:' . esc_attr( $sub_field['width'] ) . '"'
                            : '';
                    ?>
                    <th <?php echo $width; ?>>
                        <?php echo esc_html( $sub_field['label'] ); ?>
                        <?php $this->render_tooltip( $sub_field ); ?>
                    </th>
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

        <!-- Template for JavaScript to clone when adding new rows -->
        <div class="arraypress-repeater__template" style="display:none;">
            <table>
                <tbody>
                <?php $this->render_repeater_table_row( $meta_key, $field, [], '__INDEX__' ); ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render a single repeater row (vertical or horizontal layout)
     *
     * Creates a collapsible panel with drag handle, title, toggle,
     * remove button, and field content area.
     *
     * @param string     $meta_key The field's meta key.
     * @param array      $field    The field configuration array.
     * @param array      $value    The row values.
     * @param int|string $index    The row index (or '__INDEX__' for template).
     * @param string     $layout   The layout type (vertical or horizontal).
     *
     * @return void
     */
    protected function render_repeater_row( string $meta_key, array $field, array $value, $index, string $layout = 'vertical' ): void {
        $collapsed     = $field['collapsed'] ?? false;
        $is_horizontal = $layout === 'horizontal';
        $row_class     = 'arraypress-repeater__row';

        if ( $collapsed ) {
            $row_class .= ' is-collapsed';
        }
        if ( $is_horizontal ) {
            $row_class .= ' arraypress-repeater__row--horizontal';
        }

        // Generate row title
        $row_title = $this->get_repeater_row_title( $field, $value, $index );
        ?>
        <div class="<?php echo esc_attr( $row_class ); ?>"
             data-index="<?php echo esc_attr( $index ); ?>">

            <div class="arraypress-repeater__row-header">
                <span class="arraypress-repeater__row-handle">☰</span>
                <span class="arraypress-repeater__row-title">
					<?php echo esc_html( $row_title ); ?>
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
                    $width = isset( $sub_field['width'] )
                            ? 'style="width:' . esc_attr( $sub_field['width'] ) . '"'
                            : '';

                    // Build conditional attributes for nested field
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
                                <?php $this->render_tooltip( $sub_field ); ?>
                            </label>
                        <?php endif; ?>

                        <?php $this->render_nested_field_input( $sub_name, $sub_key, $sub_field, $sub_value ); ?>

                        <?php if ( ! empty( $sub_field['description'] ) && ! $is_horizontal ) : ?>
                            <p class="arraypress-field__description">
                                <?php echo esc_html( $sub_field['description'] ); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render a single table row for repeater
     *
     * Creates a table row with cells for each field plus
     * drag handle and remove button columns.
     *
     * @param string     $meta_key The field's meta key.
     * @param array      $field    The field configuration array.
     * @param array      $value    The row values.
     * @param int|string $index    The row index (or '__INDEX__' for template).
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

                // Build conditional attributes for nested field
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
     * Generate the row title for a repeater row
     *
     * Supports:
     * - Custom row_title with {index} placeholder: "File {index}" becomes "File 1", "File 2", etc.
     * - row_title_field: Use a specific field's value as the title
     * - Default: "Item 1", "Item 2", etc.
     *
     * @param array      $field The field configuration array.
     * @param array      $value The row values.
     * @param int|string $index The row index (or '__INDEX__' for template).
     *
     * @return string The row title.
     */
    protected function get_repeater_row_title( array $field, array $value, $index ): string {
        $display_index = is_numeric( $index ) ? (string) ( $index + 1 ) : '#';

        // Get field value if row_title_field is set
        $field_value = '';
        if ( ! empty( $field['row_title_field'] ) && ! empty( $value[ $field['row_title_field'] ] ) ) {
            $field_value = $value[ $field['row_title_field'] ];
        }

        // Check for custom row_title pattern
        if ( ! empty( $field['row_title'] ) ) {
            $title = str_replace( '{index}', $display_index, $field['row_title'] );

            // Replace {value} placeholder - remove it if empty
            if ( ! empty( $field_value ) ) {
                $title = str_replace( '{value}', $field_value, $title );
            } else {
                // Remove {value} and any preceding colon/space if value is empty
                $title = preg_replace( '/[:\s]*\{value}/', '', $title );
            }

            return trim( $title );
        }

        // If we have a field value but no row_title pattern, just use the value
        if ( ! empty( $field_value ) ) {
            return $field_value;
        }

        // Default title
        return sprintf( __( 'Item %s', 'arraypress' ), $display_index );
    }

    /**
     * Render tooltip icon and content if tooltip is set
     *
     * @param array $field The field configuration array.
     *
     * @return void
     */
    protected function render_tooltip( array $field ): void {
        if ( empty( $field['tooltip'] ) ) {
            return;
        }
        ?>
        <span class="arraypress-tooltip" data-tooltip="<?php echo esc_attr( $field['tooltip'] ); ?>">
			<span class="arraypress-tooltip__icon">?</span>
		</span>
        <?php
    }

}