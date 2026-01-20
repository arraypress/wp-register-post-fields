<?php
/**
 * Media Fields Rendering Trait
 *
 * Handles rendering of media-related form field types including
 * image pickers, file uploads, and image galleries.
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
 * Trait MediaFields
 *
 * Provides rendering methods for media field types:
 * - image: Single image picker from WordPress media library
 * - file: Single file picker from WordPress media library (stores attachment ID)
 * - file_url: Text input with media library button (stores URL, editable)
 * - gallery: Multiple image picker with drag-and-drop reordering
 *
 * All media fields integrate with the WordPress Media Library.
 *
 * @package ArrayPress\RegisterPostFields\Traits\Rendering
 */
trait MediaFields {

    /**
     * Render an image picker field
     *
     * Single image selection from the WordPress media library.
     * Displays a thumbnail preview when an image is selected.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
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
                    class="button arraypress-media-remove"
                    <?php echo ! $value ? 'style="display:none;"' : ''; ?>>
                <?php esc_html_e( 'Remove', 'arraypress' ); ?>
            </button>
        </div>
        <?php
    }

    /**
     * Render a file picker field
     *
     * Single file selection from the WordPress media library.
     * Displays a linked filename when a file is selected.
     * Stores the attachment ID.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
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
                    <a href="<?php echo esc_url( $file_url ); ?>" target="_blank">
                        <?php echo esc_html( $file_name ); ?>
                    </a>
                <?php endif; ?>
            </div>

            <button type="button" class="button arraypress-media-select">
                <?php echo esc_html( $button_text ); ?>
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
     * Render a file URL field
     *
     * Text input with an integrated media library button.
     * Stores the file URL (not attachment ID) and allows manual editing.
     * Similar to EDD's file download field.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
     * @param mixed  $value    The current field value (URL string).
     *
     * @return void
     */
    protected function render_file_url( string $meta_key, array $field, $value ): void {
        $button_text = $field['button_text'] ?: __( 'Browse', 'arraypress' );
        $placeholder = $field['placeholder'] ?: __( 'Enter URL or select from media library', 'arraypress' );
        ?>
        <div class="arraypress-file-url-field">
            <input type="text"
                   id="<?php echo esc_attr( $meta_key ); ?>"
                   name="<?php echo esc_attr( $meta_key ); ?>"
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
     * Render a gallery field
     *
     * Multiple image selection from the WordPress media library.
     * Supports drag-and-drop reordering and configurable max items.
     *
     * @param string $meta_key The field's meta key.
     * @param array  $field    The field configuration array.
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
                    if ( $image_url ) :
                        ?>
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

}