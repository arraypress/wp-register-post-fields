<?php
/**
 * Post Metabox Class
 *
 * A lightweight class for registering custom metaboxes with fields on WordPress
 * post edit screens. Provides a simple API for common field types with automatic
 * saving, sanitization, and REST API integration.
 *
 * @package     ArrayPress\RegisterPostFields
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterPostFields;

use Exception;
use WP_Post;

/**
 * Class PostFields
 *
 * Manages custom metabox and field registration for posts in WordPress admin.
 *
 * @package ArrayPress\RegisterPostFields
 */
class PostFields {

    /**
     * Array of registered metabox configurations.
     *
     * @var array
     */
    protected static array $metaboxes = [];

    /**
     * Whether assets have been enqueued.
     *
     * @var bool
     */
    protected static bool $assets_enqueued = false;

    /**
     * The metabox ID.
     *
     * @var string
     */
    protected string $id;

    /**
     * The metabox configuration.
     *
     * @var array
     */
    protected array $config;

    /**
     * Supported field types and their default sanitizers.
     *
     * @var array
     */
    protected static array $field_types = [
            'text'        => 'sanitize_text_field',
            'textarea'    => 'sanitize_textarea_field',
            'number'      => null,
            'select'      => null,
            'checkbox'    => null,
            'url'         => 'esc_url_raw',
            'email'       => 'sanitize_email',
            'color'       => 'sanitize_hex_color',
            'date'        => 'sanitize_text_field',
            'datetime'    => 'sanitize_text_field',
            'time'        => 'sanitize_text_field',
            'image'       => 'absint',
            'file'        => 'absint',
            'gallery'     => null,
            'wysiwyg'     => 'wp_kses_post',
            'post'        => null,
            'user'        => null,
            'term'        => null,
            'amount_type' => null,
            'group'       => null,
            'repeater'    => null,
    ];

    /**
     * PostMetabox constructor.
     *
     * Initializes the metabox registration.
     *
     * @param string $id     Unique metabox identifier.
     * @param array  $config Metabox configuration including title, post_types, and fields.
     *
     * @throws Exception If configuration is invalid.
     */
    public function __construct( string $id, array $config ) {
        if ( empty( $id ) ) {
            throw new Exception( 'Metabox ID cannot be empty.' );
        }

        if ( empty( $config['fields'] ) || ! is_array( $config['fields'] ) ) {
            throw new Exception( 'Metabox must have at least one field defined.' );
        }

        $this->id     = $id;
        $this->config = $this->parse_config( $config );

        $this->validate_fields( $this->config['fields'] );

        self::$metaboxes[ $this->id ] = $this->config;

        // Load hooks immediately if already past init, otherwise wait
        if ( did_action( 'init' ) ) {
            $this->register_meta();
        } else {
            add_action( 'init', [ $this, 'register_meta' ] );
        }

        if ( did_action( 'admin_init' ) ) {
            $this->load_hooks();
        } else {
            add_action( 'admin_init', [ $this, 'load_hooks' ] );
        }
    }

    /**
     * Parse and merge configuration with defaults.
     *
     * @param array $config Raw configuration array.
     *
     * @return array Parsed configuration.
     */
    protected function parse_config( array $config ): array {
        $defaults = [
                'title'      => __( 'Additional Information', 'arraypress' ),
                'post_types' => [ 'post' ],
                'context'    => 'normal',
                'priority'   => 'high',
                'prefix'     => '',
                'capability' => 'edit_posts',
                'fields'     => [],
        ];

        $config = wp_parse_args( $config, $defaults );

        // Normalize post_types to array
        if ( is_string( $config['post_types'] ) ) {
            $config['post_types'] = [ $config['post_types'] ];
        }

        // Parse each field
        $config['fields'] = $this->parse_fields( $config['fields'], $config['prefix'] );

        return $config;
    }

    /**
     * Parse field configurations with defaults.
     *
     * @param array  $fields Raw field configurations.
     * @param string $prefix Optional prefix for meta keys.
     *
     * @return array Parsed field configurations.
     */
    protected function parse_fields( array $fields, string $prefix = '' ): array {
        $defaults = [
            'label'             => '',
            'type'              => 'text',
            'description'       => '',
            'default'           => '',
            'placeholder'       => '',
            'options'           => [],
            'min'               => null,
            'max'               => null,
            'step'              => null,
            'rows'              => 5,
            'sanitize_callback' => null,
            'capability'        => 'edit_posts',
            'show_in_rest'      => true,
            // Number/amount fields
            'type_options'      => [],
            'type_meta_key'     => '',
            'type_default'      => '',
            // Media fields
            'mime_types'        => [],
            'button_text'       => '',
            // Relational fields
            'post_type'         => 'post',
            'taxonomy'          => 'category',
            'role'              => [],
            'multiple'          => false,
            'display'           => 'select',
            // Group/Repeater
            'fields'            => [],
            'button_label'      => '',
            'max_items'         => 0,
            'min_items'         => 0,
            'collapsed'         => false,
        ];

        $parsed = [];

        foreach ( $fields as $key => $field ) {
            $meta_key = $prefix ? $prefix . $key : $key;

            $field                 = wp_parse_args( $field, $defaults );
            $field['original_key'] = $key;
            $parsed[ $meta_key ]   = $field;

            // Parse nested fields for groups and repeaters
            if ( in_array( $field['type'], [ 'group', 'repeater' ], true ) && ! empty( $field['fields'] ) ) {
                $parsed[ $meta_key ]['fields'] = $this->parse_fields( $field['fields'] );
            }
        }

        return $parsed;
    }

    /**
     * Validate field configurations.
     *
     * @param array $fields Field configurations to validate.
     *
     * @return void
     * @throws Exception If field configuration is invalid.
     */
    protected function validate_fields( array $fields ): void {
        foreach ( $fields as $key => $field ) {
            if ( ! is_string( $key ) || empty( $key ) ) {
                throw new Exception( 'Invalid field key provided. It must be a non-empty string.' );
            }

            $type = $field['type'] ?? 'text';
            if ( ! array_key_exists( $type, self::$field_types ) ) {
                throw new Exception( sprintf( 'Invalid field type "%s" for field "%s".', $type, $key ) );
            }

            // Validate amount_type requirements
            if ( $type === 'amount_type' ) {
                if ( empty( $field['type_options'] ) ) {
                    throw new Exception( sprintf( 'Field "%s" of type "amount_type" requires "type_options" to be set.', $key ) );
                }
                if ( empty( $field['type_meta_key'] ) ) {
                    throw new Exception( sprintf( 'Field "%s" of type "amount_type" requires "type_meta_key" to be set.', $key ) );
                }
            }

            // Validate nested fields
            if ( in_array( $type, [ 'group', 'repeater' ], true ) && ! empty( $field['fields'] ) ) {
                $this->validate_fields( $field['fields'] );
            }
        }
    }

    /**
     * Register meta keys with WordPress.
     *
     * @return void
     */
    public function register_meta(): void {
        foreach ( $this->config['post_types'] as $post_type ) {
            foreach ( $this->config['fields'] as $meta_key => $field ) {
                $this->register_field_meta( $post_type, $meta_key, $field );
            }
        }
    }

    /**
     * Register meta for a single field.
     *
     * @param string $post_type The post type.
     * @param string $meta_key  The meta key.
     * @param array  $field     The field configuration.
     *
     * @return void
     */
    protected function register_field_meta( string $post_type, string $meta_key, array $field ): void {
        $type   = $field['type'];
        $schema = $this->get_rest_schema( $field );

        $args = [
            'object_subtype'    => $post_type,
            'type'              => $schema['type'],
            'description'       => $field['description'],
            'single'            => true,
            'sanitize_callback' => $this->get_sanitize_callback( $field ),
            'auth_callback'     => function () use ( $field ) {
                return current_user_can( $field['capability'] );
            },
            'show_in_rest'      => $field['show_in_rest'] ? [ 'schema' => $schema ] : false,
            'default'           => $field['default'],
        ];

        // Handle array types (gallery, repeater, multiple selects)
        if ( in_array( $type, [ 'gallery', 'repeater' ], true ) ||
             ( $field['multiple'] && in_array( $type, [ 'post', 'user', 'term' ], true ) ) ) {
            $args['type']   = 'array';
            $args['single'] = true;
        }

        register_meta( 'post', $meta_key, $args );

        // Register secondary meta key for amount_type
        if ( $type === 'amount_type' && ! empty( $field['type_meta_key'] ) ) {
            register_meta( 'post', $field['type_meta_key'], [
                    'object_subtype'    => $post_type,
                    'type'              => 'string',
                    'single'            => true,
                    'sanitize_callback' => 'sanitize_text_field',
                    'show_in_rest'      => $field['show_in_rest'],
            ] );
        }
    }

    /**
     * Get REST API schema for a field.
     *
     * @param array $field The field configuration.
     *
     * @return array The REST schema.
     */
    protected function get_rest_schema( array $field ): array {
        $type = $field['type'];

        switch ( $type ) {
            case 'number':
            case 'amount_type':
                return [
                        'type' => 'number',
                ];

            case 'checkbox':
                return [
                        'type' => 'boolean',
                ];

            case 'image':
            case 'file':
            case 'post':
            case 'term':
            case 'user':
                if ( $field['multiple'] ) {
                    return [
                            'type'  => 'array',
                            'items' => [ 'type' => 'integer' ],
                    ];
                }

                return [ 'type' => 'integer' ];

            case 'gallery':
                return [
                        'type'  => 'array',
                        'items' => [ 'type' => 'integer' ],
                ];

            case 'repeater':
                return [
                        'type'  => 'array',
                        'items' => [
                                'type'       => 'object',
                                'properties' => $this->get_repeater_schema_properties( $field['fields'] ),
                        ],
                ];

            case 'group':
                return [
                        'type'       => 'object',
                        'properties' => $this->get_repeater_schema_properties( $field['fields'] ),
                ];

            default:
                return [ 'type' => 'string' ];
        }
    }

    /**
     * Get REST schema properties for repeater/group fields.
     *
     * @param array $fields Nested field configurations.
     *
     * @return array Schema properties.
     */
    protected function get_repeater_schema_properties( array $fields ): array {
        $properties = [];

        foreach ( $fields as $key => $field ) {
            $properties[ $key ] = $this->get_rest_schema( $field );
        }

        return $properties;
    }

    /**
     * Get sanitize callback for a field.
     *
     * @param array $field The field configuration.
     *
     * @return callable The sanitize callback.
     */
    protected function get_sanitize_callback( array $field ): callable {
        if ( is_callable( $field['sanitize_callback'] ) ) {
            return $field['sanitize_callback'];
        }

        return function ( $value ) use ( $field ) {
            return $this->sanitize_value( $value, $field );
        };
    }

    /**
     * Load WordPress hooks.
     *
     * @return void
     */
    public function load_hooks(): void {
        add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
        add_action( 'save_post', [ $this, 'save_fields' ], 10, 2 );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    /**
     * Register the metabox with WordPress.
     *
     * @return void
     */
    public function add_meta_box(): void {
        foreach ( $this->config['post_types'] as $post_type ) {
            add_meta_box(
                    $this->id,
                    $this->config['title'],
                    [ $this, 'render_metabox' ],
                    $post_type,
                    $this->config['context'],
                    $this->config['priority']
            );
        }
    }

    /**
     * Enqueue required assets.
     *
     * @param string $hook The current admin page hook.
     *
     * @return void
     */
    public function enqueue_assets( string $hook ): void {
        if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
            return;
        }

        global $post_type;
        if ( ! in_array( $post_type, $this->config['post_types'], true ) ) {
            return;
        }

        // Enqueue media for image/file/gallery fields
        if ( $this->has_field_type( [ 'image', 'file', 'gallery' ] ) ) {
            wp_enqueue_media();
        }

        // Enqueue color picker
        if ( $this->has_field_type( 'color' ) ) {
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'wp-color-picker' );
        }

        // Enqueue jQuery UI sortable for repeaters
        if ( $this->has_field_type( 'repeater' ) ) {
            wp_enqueue_script( 'jquery-ui-sortable' );
        }

        // Enqueue custom assets only once
        if ( ! self::$assets_enqueued ) {
            $this->output_inline_styles();
            $this->output_inline_scripts();
            self::$assets_enqueued = true;
        }
    }

    /**
     * Check if metabox has a specific field type.
     *
     * @param string|array $types Field type(s) to check for.
     *
     * @return bool True if field type exists.
     */
    protected function has_field_type( $types ): bool {
        $types = (array) $types;

        foreach ( $this->config['fields'] as $field ) {
            if ( in_array( $field['type'], $types, true ) ) {
                return true;
            }

            // Check nested fields in repeaters/groups
            if ( in_array( $field['type'], [ 'repeater', 'group' ], true ) ) {
                foreach ( $field['fields'] as $nested_field ) {
                    if ( in_array( $nested_field['type'], $types, true ) ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Render the metabox content.
     *
     * @param WP_Post $post The post object.
     *
     * @return void
     */
    public function render_metabox( WP_Post $post ): void {
        // Security nonce
        wp_nonce_field( 'save_' . $this->id, $this->id . '_nonce' );

        echo '<div class="arraypress-metabox">';

        foreach ( $this->config['fields'] as $meta_key => $field ) {
            if ( ! $this->check_permission( $field ) ) {
                continue;
            }

            $value = get_post_meta( $post->ID, $meta_key, true );

            // Use default if no value exists
            if ( '' === $value && '' !== $field['default'] ) {
                $value = $field['default'];
            }

            $this->render_field( $meta_key, $field, $value, $post->ID );
        }

        echo '</div>';
    }

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
        $id    = esc_attr( $meta_key );
        $type  = $field['type'];
        $class = 'arraypress-field arraypress-field--' . $type;

        echo '<div class="' . esc_attr( $class ) . '">';

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
                ?>
                <div class="arraypress-group__field">
                    <label class="arraypress-group__label">
                        <?php echo esc_html( $sub_field['label'] ); ?>
                    </label>
                    <?php $this->render_nested_field_input( $sub_name, $sub_key, $sub_field, $sub_value ); ?>
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
        ?>
        <div class="arraypress-repeater"
             data-meta-key="<?php echo esc_attr( $meta_key ); ?>"
             data-max="<?php echo esc_attr( $max ); ?>"
             data-min="<?php echo esc_attr( $min ); ?>">

            <div class="arraypress-repeater__rows">
                <?php
                $index = 0;
                foreach ( $value as $row_value ) :
                    $this->render_repeater_row( $meta_key, $field, $row_value, $index );
                    $index ++;
                endforeach;
                ?>
            </div>

            <div class="arraypress-repeater__template" style="display:none;">
                <?php $this->render_repeater_row( $meta_key, $field, [], '__INDEX__' ); ?>
            </div>

            <button type="button" class="button arraypress-repeater__add">
                <?php echo esc_html( $button_label ); ?>
            </button>
        </div>
        <?php
    }

    /**
     * Render a single repeater row.
     *
     * @param string     $meta_key The field's meta key.
     * @param array      $field    The field configuration.
     * @param array      $value    The row values.
     * @param int|string $index    The row index.
     *
     * @return void
     */
    protected function render_repeater_row( string $meta_key, array $field, array $value, $index ): void {
        $collapsed = $field['collapsed'];
        ?>
        <div class="arraypress-repeater__row<?php echo $collapsed ? ' is-collapsed' : ''; ?>"
             data-index="<?php echo esc_attr( $index ); ?>">
            <div class="arraypress-repeater__row-header">
                <span class="arraypress-repeater__row-handle">☰</span>
                <span class="arraypress-repeater__row-title">
					<?php printf( __( 'Item %s', 'arraypress' ), is_numeric( $index ) ? $index + 1 : '#' ); ?>
				</span>
                <button type="button" class="arraypress-repeater__row-toggle">▼</button>
                <button type="button" class="arraypress-repeater__row-remove">&times;</button>
            </div>
            <div class="arraypress-repeater__row-content">
                <?php foreach ( $field['fields'] as $sub_key => $sub_field ) :
                    $sub_value = $value[ $sub_key ] ?? $sub_field['default'];
                    $sub_name = $meta_key . '[' . $index . '][' . $sub_key . ']';
                    ?>
                    <div class="arraypress-repeater__field">
                        <label class="arraypress-repeater__field-label">
                            <?php echo esc_html( $sub_field['label'] ); ?>
                        </label>
                        <?php $this->render_nested_field_input( $sub_name, $sub_key, $sub_field, $sub_value ); ?>
                        <?php if ( ! empty( $sub_field['description'] ) ) : ?>
                            <p class="arraypress-field__description"><?php echo esc_html( $sub_field['description'] ); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
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
     * Get options from array or callable.
     *
     * @param array|callable $options Options array or callable.
     *
     * @return array Options array.
     */
    protected function get_options( $options ): array {
        if ( is_callable( $options ) ) {
            $options = call_user_func( $options );
        }

        return is_array( $options ) ? $options : [];
    }

    /**
     * Save field values when a post is saved.
     *
     * @param int     $post_id The post ID.
     * @param WP_Post $post    The post object.
     *
     * @return void
     */
    public function save_fields( int $post_id, WP_Post $post ): void {
        // Verify nonce
        if ( ! isset( $_POST[ $this->id . '_nonce' ] ) ||
             ! wp_verify_nonce( $_POST[ $this->id . '_nonce' ], 'save_' . $this->id ) ) {
            return;
        }

        // Check autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check post type
        if ( ! in_array( $post->post_type, $this->config['post_types'], true ) ) {
            return;
        }

        // Check permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        foreach ( $this->config['fields'] as $meta_key => $field ) {
            if ( ! $this->check_permission( $field ) ) {
                continue;
            }

            $type = $field['type'];

            // Handle checkbox (unchecked = not in POST)
            if ( $type === 'checkbox' ) {
                $value = isset( $_POST[ $meta_key ] ) ? 1 : 0;
            } elseif ( $type === 'amount_type' ) {
                $this->save_amount_type_field( $post_id, $meta_key, $field );
                continue;
            } else {
                if ( ! isset( $_POST[ $meta_key ] ) ) {
                    delete_post_meta( $post_id, $meta_key );
                    continue;
                }
                $value = $_POST[ $meta_key ];
            }

            // Sanitize and save
            $value = $this->sanitize_value( $value, $field );

            if ( '' === $value || null === $value || ( is_array( $value ) && empty( $value ) ) ) {
                delete_post_meta( $post_id, $meta_key );
            } else {
                update_post_meta( $post_id, $meta_key, $value );
            }
        }
    }

    /**
     * Save amount_type field values.
     *
     * @param int    $post_id  The post ID.
     * @param string $meta_key The amount meta key.
     * @param array  $field    The field configuration.
     *
     * @return void
     */
    protected function save_amount_type_field( int $post_id, string $meta_key, array $field ): void {
        $type_meta_key = $field['type_meta_key'];

        $amount = isset( $_POST[ $meta_key ] ) ? $_POST[ $meta_key ] : '';
        $type   = isset( $_POST[ $type_meta_key ] ) ? $_POST[ $type_meta_key ] : '';

        // Sanitize amount
        $amount = $this->sanitize_amount( $amount, $field );

        // Validate type against options
        $type_options = $this->get_options( $field['type_options'] );
        if ( ! array_key_exists( $type, $type_options ) ) {
            $type = $field['type_default'] ?: array_key_first( $type_options );
        }

        // Save or delete
        if ( '' === $amount || null === $amount || $amount <= 0 ) {
            delete_post_meta( $post_id, $meta_key );
            delete_post_meta( $post_id, $type_meta_key );
        } else {
            update_post_meta( $post_id, $meta_key, $amount );
            update_post_meta( $post_id, $type_meta_key, $type );
        }
    }

    /**
     * Sanitize an amount value.
     *
     * @param mixed $value The value to sanitize.
     * @param array $field The field configuration.
     *
     * @return float|string Sanitized value.
     */
    protected function sanitize_amount( $value, array $field ) {
        if ( '' === $value || null === $value ) {
            return '';
        }

        $value = floatval( $value );

        if ( isset( $field['min'] ) && $value < $field['min'] ) {
            $value = $field['min'];
        }
        if ( isset( $field['max'] ) && $value > $field['max'] ) {
            $value = $field['max'];
        }

        return $value > 0 ? $value : '';
    }

    /**
     * Sanitize a field value.
     *
     * @param mixed $value The value to sanitize.
     * @param array $field The field configuration.
     *
     * @return mixed Sanitized value.
     */
    protected function sanitize_value( $value, array $field ) {
        if ( is_callable( $field['sanitize_callback'] ) ) {
            return call_user_func( $field['sanitize_callback'], $value );
        }

        $type = $field['type'];

        switch ( $type ) {
            case 'checkbox':
                return $value ? 1 : 0;

            case 'number':
                $step = $field['step'] ?? 1;
                if ( is_numeric( $step ) && floor( $step ) != $step ) {
                    $value = floatval( $value );
                } else {
                    $value = intval( $value );
                }

                if ( isset( $field['min'] ) && $value < $field['min'] ) {
                    $value = $field['min'];
                }
                if ( isset( $field['max'] ) && $value > $field['max'] ) {
                    $value = $field['max'];
                }

                return $value;

            case 'select':
                if ( $field['multiple'] ) {
                    $values  = (array) $value;
                    $options = $this->get_options( $field['options'] );

                    return array_filter( $values, function ( $v ) use ( $options ) {
                        return array_key_exists( $v, $options );
                    } );
                }

                $options = $this->get_options( $field['options'] );

                return array_key_exists( $value, $options ) ? $value : $field['default'];

            case 'url':
                return esc_url_raw( $value );

            case 'email':
                return sanitize_email( $value );

            case 'textarea':
                return sanitize_textarea_field( $value );

            case 'wysiwyg':
                return wp_kses_post( $value );

            case 'color':
                return sanitize_hex_color( $value );

            case 'image':
            case 'file':
                return absint( $value );

            case 'gallery':
                if ( is_string( $value ) ) {
                    $value = array_filter( explode( ',', $value ) );
                }

                return array_map( 'absint', (array) $value );

            case 'post':
            case 'user':
            case 'term':
                if ( $field['multiple'] ) {
                    return array_map( 'absint', (array) $value );
                }

                return absint( $value );

            case 'group':
                return $this->sanitize_group( $value, $field );

            case 'repeater':
                return $this->sanitize_repeater( $value, $field );

            case 'date':
            case 'datetime':
            case 'time':
            case 'text':
            default:
                return sanitize_text_field( $value );
        }
    }

    /**
     * Sanitize a group field value.
     *
     * @param mixed $value The value to sanitize.
     * @param array $field The field configuration.
     *
     * @return array Sanitized value.
     */
    protected function sanitize_group( $value, array $field ): array {
        if ( ! is_array( $value ) ) {
            return [];
        }

        $sanitized = [];

        foreach ( $field['fields'] as $sub_key => $sub_field ) {
            $sub_value             = $value[ $sub_key ] ?? $sub_field['default'];
            $sanitized[ $sub_key ] = $this->sanitize_value( $sub_value, $sub_field );
        }

        return $sanitized;
    }

    /**
     * Sanitize a repeater field value.
     *
     * @param mixed $value The value to sanitize.
     * @param array $field The field configuration.
     *
     * @return array Sanitized value.
     */
    protected function sanitize_repeater( $value, array $field ): array {
        if ( ! is_array( $value ) ) {
            return [];
        }

        $sanitized = [];

        foreach ( $value as $index => $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $sanitized_row = [];

            foreach ( $field['fields'] as $sub_key => $sub_field ) {
                $sub_value                 = $row[ $sub_key ] ?? $sub_field['default'];
                $sanitized_row[ $sub_key ] = $this->sanitize_value( $sub_value, $sub_field );
            }

            // Only add row if it has content
            $has_content = array_filter( $sanitized_row, function ( $v ) {
                return '' !== $v && null !== $v && [] !== $v;
            } );

            if ( ! empty( $has_content ) ) {
                $sanitized[] = $sanitized_row;
            }
        }

        // Apply max items limit
        if ( $field['max_items'] > 0 && count( $sanitized ) > $field['max_items'] ) {
            $sanitized = array_slice( $sanitized, 0, $field['max_items'] );
        }

        return $sanitized;
    }

    /**
     * Check if the current user has permission to view/edit the field.
     *
     * @param array $field The field configuration.
     *
     * @return bool True if user has permission.
     */
    protected function check_permission( array $field ): bool {
        return current_user_can( $field['capability'] );
    }

    /**
     * Output inline CSS styles.
     *
     * @return void
     */
    protected function output_inline_styles(): void {
        ?>
        <style>
            .arraypress-metabox {
                margin: -6px -12px -12px;
            }

            .arraypress-field {
                padding: 12px;
                border-bottom: 1px solid #f0f0f0;
            }

            .arraypress-field:last-child {
                border-bottom: none;
            }

            .arraypress-field__label {
                display: block;
                font-weight: 600;
                margin-bottom: 8px;
            }

            .arraypress-field__description {
                margin: 8px 0 0;
                color: #646970;
                font-style: italic;
            }

            /* Amount Type */
            .arraypress-amount-type {
                display: inline-flex;
                align-items: stretch;
            }

            .arraypress-amount-type input[type="number"] {
                border-top-right-radius: 0;
                border-bottom-right-radius: 0;
                margin-right: -1px;
            }

            .arraypress-amount-type select {
                border-top-left-radius: 0;
                border-bottom-left-radius: 0;
            }

            /* Media Fields */
            .arraypress-media-field {
                display: flex;
                align-items: center;
                gap: 10px;
                flex-wrap: wrap;
            }

            .arraypress-media-preview img {
                max-width: 150px;
                max-height: 150px;
                border-radius: 4px;
            }

            /* Gallery */
            .arraypress-gallery-preview {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-bottom: 10px;
            }

            .arraypress-gallery-item {
                position: relative;
                display: inline-block;
            }

            .arraypress-gallery-item img {
                width: 100px;
                height: 100px;
                object-fit: cover;
                border-radius: 4px;
            }

            .arraypress-gallery-remove {
                position: absolute;
                top: -8px;
                right: -8px;
                width: 20px;
                height: 20px;
                border-radius: 50%;
                background: #d63638;
                color: #fff;
                border: none;
                cursor: pointer;
                font-size: 14px;
                line-height: 1;
                padding: 0;
            }

            /* Checkbox Group */
            .arraypress-checkbox-group {
                max-height: 200px;
                overflow-y: auto;
                padding: 10px;
                border: 1px solid #dcdcde;
                border-radius: 4px;
                background: #fff;
            }

            .arraypress-checkbox-item {
                display: block;
                margin-bottom: 5px;
            }

            /* Group */
            .arraypress-group {
                background: #f9f9f9;
                padding: 12px;
                border-radius: 4px;
                border: 1px solid #dcdcde;
            }

            .arraypress-group__field {
                margin-bottom: 12px;
            }

            .arraypress-group__field:last-child {
                margin-bottom: 0;
            }

            .arraypress-group__label {
                display: block;
                font-weight: 600;
                margin-bottom: 5px;
            }

            /* Repeater */
            .arraypress-repeater__rows {
                margin-bottom: 10px;
            }

            .arraypress-repeater__row {
                background: #f9f9f9;
                border: 1px solid #dcdcde;
                border-radius: 4px;
                margin-bottom: 10px;
            }

            .arraypress-repeater__row-header {
                display: flex;
                align-items: center;
                padding: 10px 12px;
                background: #fff;
                border-bottom: 1px solid #dcdcde;
                border-radius: 4px 4px 0 0;
                cursor: move;
            }

            .arraypress-repeater__row.is-collapsed .arraypress-repeater__row-header {
                border-bottom: none;
                border-radius: 4px;
            }

            .arraypress-repeater__row-handle {
                margin-right: 10px;
                color: #c3c4c7;
                cursor: move;
            }

            .arraypress-repeater__row-title {
                flex: 1;
                font-weight: 600;
            }

            .arraypress-repeater__row-toggle,
            .arraypress-repeater__row-remove {
                background: none;
                border: none;
                cursor: pointer;
                padding: 5px 10px;
                color: #646970;
            }

            .arraypress-repeater__row-remove:hover {
                color: #d63638;
            }

            .arraypress-repeater__row-content {
                padding: 12px;
            }

            .arraypress-repeater__row.is-collapsed .arraypress-repeater__row-content {
                display: none;
            }

            .arraypress-repeater__field {
                margin-bottom: 12px;
            }

            .arraypress-repeater__field:last-child {
                margin-bottom: 0;
            }

            .arraypress-repeater__field-label {
                display: block;
                font-weight: 600;
                margin-bottom: 5px;
            }

            /* Sortable placeholder */
            .arraypress-repeater__row.ui-sortable-placeholder {
                visibility: visible !important;
                background: #f0f6fc;
                border: 2px dashed #2271b1;
            }

            .arraypress-repeater__row.ui-sortable-helper {
                box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
            }
        </style>
        <?php
    }

    /**
     * Output inline JavaScript.
     *
     * @return void
     */
    protected function output_inline_scripts(): void {
        ?>
        <script>
            jQuery(document).ready(function ($) {
                'use strict';

                // Initialize color pickers
                $('.arraypress-color-picker').wpColorPicker();

                // Media library handling
                $(document).on('click', '.arraypress-media-select', function (e) {
                    e.preventDefault();
                    var $field = $(this).closest('.arraypress-media-field');
                    var $input = $field.find('.arraypress-media-input');
                    var type = $field.data('type');

                    var frame = wp.media({
                        title: type === 'image' ? 'Select Image' : 'Select File',
                        button: {text: 'Use this ' + type},
                        multiple: false,
                        library: type === 'image' ? {type: 'image'} : {}
                    });

                    frame.on('select', function () {
                        var attachment = frame.state().get('selection').first().toJSON();
                        $input.val(attachment.id);

                        if (type === 'image') {
                            var url = attachment.sizes && attachment.sizes.thumbnail
                                ? attachment.sizes.thumbnail.url
                                : attachment.url;
                            $field.find('.arraypress-media-preview').html('<img src="' + url + '" alt="" />');
                        } else {
                            $field.find('.arraypress-file-preview').html('<a href="' + attachment.url + '" target="_blank">' + attachment.filename + '</a>');
                        }

                        $field.find('.arraypress-media-remove').show();
                    });

                    frame.open();
                });

                $(document).on('click', '.arraypress-media-remove', function (e) {
                    e.preventDefault();
                    var $field = $(this).closest('.arraypress-media-field');
                    $field.find('.arraypress-media-input').val('');
                    $field.find('.arraypress-media-preview, .arraypress-file-preview').empty();
                    $(this).hide();
                });

                // Gallery handling
                $(document).on('click', '.arraypress-gallery-add', function (e) {
                    e.preventDefault();
                    var $field = $(this).closest('.arraypress-gallery-field');
                    var $input = $field.find('.arraypress-gallery-input');
                    var $preview = $field.find('.arraypress-gallery-preview');
                    var max = parseInt($field.data('max')) || 0;

                    var frame = wp.media({
                        title: 'Select Images',
                        button: {text: 'Add to Gallery'},
                        multiple: true,
                        library: {type: 'image'}
                    });

                    frame.on('select', function () {
                        var attachments = frame.state().get('selection').toJSON();
                        var currentIds = $input.val() ? $input.val().split(',') : [];

                        attachments.forEach(function (attachment) {
                            if (max > 0 && currentIds.length >= max) return;
                            if (currentIds.indexOf(String(attachment.id)) !== -1) return;

                            currentIds.push(attachment.id);
                            var url = attachment.sizes && attachment.sizes.thumbnail
                                ? attachment.sizes.thumbnail.url
                                : attachment.url;
                            $preview.append(
                                '<div class="arraypress-gallery-item" data-id="' + attachment.id + '">' +
                                '<img src="' + url + '" alt="" />' +
                                '<button type="button" class="arraypress-gallery-remove">&times;</button>' +
                                '</div>'
                            );
                        });

                        $input.val(currentIds.join(','));
                    });

                    frame.open();
                });

                $(document).on('click', '.arraypress-gallery-remove', function (e) {
                    e.preventDefault();
                    var $item = $(this).closest('.arraypress-gallery-item');
                    var $field = $item.closest('.arraypress-gallery-field');
                    var $input = $field.find('.arraypress-gallery-input');
                    var id = String($item.data('id'));
                    var ids = $input.val() ? $input.val().split(',') : [];

                    ids = ids.filter(function (i) {
                        return i !== id;
                    });
                    $input.val(ids.join(','));
                    $item.remove();
                });

                // Make gallery sortable
                $('.arraypress-gallery-preview').sortable({
                    items: '.arraypress-gallery-item',
                    cursor: 'move',
                    update: function (event, ui) {
                        var $field = $(this).closest('.arraypress-gallery-field');
                        var $input = $field.find('.arraypress-gallery-input');
                        var ids = [];

                        $(this).find('.arraypress-gallery-item').each(function () {
                            ids.push($(this).data('id'));
                        });

                        $input.val(ids.join(','));
                    }
                });

                // Repeater handling
                $(document).on('click', '.arraypress-repeater__add', function (e) {
                    e.preventDefault();
                    var $repeater = $(this).closest('.arraypress-repeater');
                    var $rows = $repeater.find('.arraypress-repeater__rows');
                    var $template = $repeater.find('.arraypress-repeater__template');
                    var max = parseInt($repeater.data('max')) || 0;
                    var currentCount = $rows.find('.arraypress-repeater__row').length;

                    if (max > 0 && currentCount >= max) {
                        alert('Maximum items reached');
                        return;
                    }

                    var newIndex = currentCount;
                    var $newRow = $($template.html());

                    // Replace placeholder index with actual index
                    $newRow.find('[name]').each(function () {
                        var name = $(this).attr('name');
                        $(this).attr('name', name.replace('__INDEX__', newIndex));
                    });

                    $newRow.attr('data-index', newIndex);
                    $newRow.find('.arraypress-repeater__row-title').text('Item ' + (newIndex + 1));

                    $rows.append($newRow);
                    updateRepeaterIndexes($repeater);

                    // Initialize any color pickers in new row
                    $newRow.find('.arraypress-color-picker').wpColorPicker();
                });

                $(document).on('click', '.arraypress-repeater__row-remove', function (e) {
                    e.preventDefault();
                    var $repeater = $(this).closest('.arraypress-repeater');
                    var $rows = $repeater.find('.arraypress-repeater__rows');
                    var min = parseInt($repeater.data('min')) || 0;
                    var currentCount = $rows.find('.arraypress-repeater__row').length;

                    if (min > 0 && currentCount <= min) {
                        alert('Minimum items required');
                        return;
                    }

                    $(this).closest('.arraypress-repeater__row').remove();
                    updateRepeaterIndexes($repeater);
                });

                $(document).on('click', '.arraypress-repeater__row-toggle', function (e) {
                    e.preventDefault();
                    $(this).closest('.arraypress-repeater__row').toggleClass('is-collapsed');
                });

                // Make repeater rows sortable
                $('.arraypress-repeater__rows').sortable({
                    handle: '.arraypress-repeater__row-handle',
                    items: '.arraypress-repeater__row',
                    cursor: 'move',
                    placeholder: 'arraypress-repeater__row ui-sortable-placeholder',
                    update: function (event, ui) {
                        updateRepeaterIndexes($(this).closest('.arraypress-repeater'));
                    }
                });

                function updateRepeaterIndexes($repeater) {
                    var metaKey = $repeater.data('meta-key');

                    $repeater.find('.arraypress-repeater__rows .arraypress-repeater__row').each(function (index) {
                        var $row = $(this);
                        $row.attr('data-index', index);
                        $row.find('.arraypress-repeater__row-title').text('Item ' + (index + 1));

                        $row.find('[name]').each(function () {
                            var name = $(this).attr('name');
                            var newName = name.replace(/\[\d+\]/, '[' + index + ']');
                            $(this).attr('name', newName);
                        });
                    });
                }
            });
        </script>
        <?php
    }

    /**
     * Get all registered metaboxes.
     *
     * @return array Array of metabox configurations.
     */
    public static function get_all_metaboxes(): array {
        return self::$metaboxes;
    }

    /**
     * Get a specific metabox configuration.
     *
     * @param string $id The metabox ID.
     *
     * @return array|null The metabox configuration or null if not found.
     */
    public static function get_metabox( string $id ): ?array {
        return self::$metaboxes[ $id ] ?? null;
    }

    /**
     * Get fields for a specific metabox.
     *
     * @param string $id The metabox ID.
     *
     * @return array Array of field configurations.
     */
    public static function get_metabox_fields( string $id ): array {
        return self::$metaboxes[ $id ]['fields'] ?? [];
    }

    /**
     * Get a specific field configuration.
     *
     * @param string $metabox_id The metabox ID.
     * @param string $meta_key   The field's meta key.
     *
     * @return array|null The field configuration or null if not found.
     */
    public static function get_field_config( string $metabox_id, string $meta_key ): ?array {
        return self::$metaboxes[ $metabox_id ]['fields'][ $meta_key ] ?? null;
    }

}