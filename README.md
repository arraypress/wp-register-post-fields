# WordPress Register Post Metabox

A lightweight library for registering custom metaboxes with fields on WordPress post edit screens. This library provides a clean, simple API for adding common field types to any post type without complex configuration.

## Features

- **Simple API**: Register custom metaboxes with minimal code
- **Multiple Field Types**: Text, textarea, WYSIWYG, number, select, checkbox, URL, email, color, date/time, image, file, gallery, and relational fields
- **Repeater Fields**: Dynamic repeatable field groups with drag-and-drop reordering
- **Group Fields**: Static groups of related fields
- **Automatic Saving**: Fields are automatically saved to post meta
- **Smart Sanitization**: Each field type has appropriate default sanitization
- **REST API Integration**: Fields are automatically registered with `register_meta()` and exposed via REST API
- **Dynamic Options**: Select fields support callable options for dynamic data
- **Multiple Post Types**: Register the same metabox across multiple post types
- **Permission Control**: Control field visibility based on user capabilities
- **Meta Key Prefixing**: Optional automatic prefixing of meta keys
- **Media Library Integration**: Native WordPress media picker for image, file, and gallery fields
- **Lightweight**: Minimal JavaScript, leverages WordPress built-in functionality

## Requirements

- PHP 7.4 or higher
- WordPress 5.0 or higher

## Installation

Install via Composer:

```bash
composer require arraypress/wp-register-post-metabox
```

## Basic Usage

### Simple Metabox with Text Fields

```php
register_post_metabox( 'product_info', [
    'title'      => __( 'Product Information', 'textdomain' ),
    'post_types' => 'product',
    'fields'     => [
        'sku' => [
            'label'       => __( 'SKU', 'textdomain' ),
            'type'        => 'text',
            'placeholder' => 'PRD-0000',
        ],
        'price' => [
            'label' => __( 'Price', 'textdomain' ),
            'type'  => 'number',
            'min'   => 0,
            'step'  => 0.01,
        ],
    ],
] );
```

### Multiple Post Types

```php
register_post_metabox( 'seo_settings', [
    'title'      => __( 'SEO Settings', 'textdomain' ),
    'post_types' => [ 'post', 'page', 'product' ],
    'fields'     => [
        'meta_title' => [
            'label' => __( 'Meta Title', 'textdomain' ),
            'type'  => 'text',
        ],
        'meta_description' => [
            'label' => __( 'Meta Description', 'textdomain' ),
            'type'  => 'textarea',
            'rows'  => 3,
        ],
    ],
] );
```

### Sidebar Metabox

```php
register_post_metabox( 'post_settings', [
    'title'      => __( 'Post Settings', 'textdomain' ),
    'post_types' => 'post',
    'context'    => 'side',
    'priority'   => 'high',
    'fields'     => [
        'featured' => [
            'label' => __( 'Featured Post', 'textdomain' ),
            'type'  => 'checkbox',
        ],
    ],
] );
```

## Field Types

### Text

Standard single-line text input.

```php
'field_name' => [
    'label'       => __( 'Field Label', 'textdomain' ),
    'type'        => 'text',
    'placeholder' => __( 'Placeholder text...', 'textdomain' ),
    'default'     => '',
]
```

### Textarea

Multi-line text input.

```php
'description' => [
    'label'       => __( 'Description', 'textdomain' ),
    'type'        => 'textarea',
    'rows'        => 5,
    'placeholder' => __( 'Enter a description...', 'textdomain' ),
]
```

### WYSIWYG

WordPress rich text editor.

```php
'content' => [
    'label' => __( 'Content', 'textdomain' ),
    'type'  => 'wysiwyg',
    'rows'  => 10,
]
```

### Number

Numeric input with optional constraints.

```php
'quantity' => [
    'label'   => __( 'Quantity', 'textdomain' ),
    'type'    => 'number',
    'min'     => 0,
    'max'     => 100,
    'step'    => 1,
    'default' => 0,
]
```

For decimal values:

```php
'price' => [
    'label' => __( 'Price', 'textdomain' ),
    'type'  => 'number',
    'min'   => 0,
    'step'  => 0.01,
]
```

### Select

Dropdown selection with static or dynamic options.

```php
// Static options
'layout' => [
    'label'   => __( 'Layout', 'textdomain' ),
    'type'    => 'select',
    'default' => 'grid',
    'options' => [
        'grid' => __( 'Grid', 'textdomain' ),
        'list' => __( 'List', 'textdomain' ),
    ],
]

// Dynamic options via callback
'parent_page' => [
    'label'   => __( 'Parent Page', 'textdomain' ),
    'type'    => 'select',
    'options' => function() {
        $pages = get_pages();
        $options = [ '' => __( '— None —', 'textdomain' ) ];
        
        foreach ( $pages as $page ) {
            $options[ $page->ID ] = $page->post_title;
        }
        
        return $options;
    },
]

// Multiple selection
'categories' => [
    'label'    => __( 'Categories', 'textdomain' ),
    'type'     => 'select',
    'multiple' => true,
    'options'  => [ /* ... */ ],
]
```

### Checkbox

Boolean toggle field.

```php
'featured' => [
    'label'       => __( 'Featured', 'textdomain' ),
    'type'        => 'checkbox',
    'default'     => 0,
    'description' => __( 'Show in featured section.', 'textdomain' ),
]
```

### URL

Text input with URL validation.

```php
'website' => [
    'label'       => __( 'Website', 'textdomain' ),
    'type'        => 'url',
    'placeholder' => 'https://example.com',
]
```

### Email

Text input with email validation.

```php
'email' => [
    'label'       => __( 'Email', 'textdomain' ),
    'type'        => 'email',
    'placeholder' => 'contact@example.com',
]
```

### Color

Color picker field.

```php
'brand_color' => [
    'label'   => __( 'Brand Color', 'textdomain' ),
    'type'    => 'color',
    'default' => '#0073aa',
]
```

### Date / DateTime / Time

Date and time picker fields.

```php
'event_date' => [
    'label' => __( 'Event Date', 'textdomain' ),
    'type'  => 'date',
]

'event_datetime' => [
    'label' => __( 'Event Date & Time', 'textdomain' ),
    'type'  => 'datetime',
]

'start_time' => [
    'label' => __( 'Start Time', 'textdomain' ),
    'type'  => 'time',
]
```

### Image

Single image picker from media library.

```php
'featured_image' => [
    'label'       => __( 'Featured Image', 'textdomain' ),
    'type'        => 'image',
    'button_text' => __( 'Select Image', 'textdomain' ),
]
```

### File

Single file picker from media library.

```php
'download_file' => [
    'label'       => __( 'Download File', 'textdomain' ),
    'type'        => 'file',
    'button_text' => __( 'Select File', 'textdomain' ),
]
```

### Gallery

Multiple image picker with drag-and-drop reordering.

```php
'gallery' => [
    'label'       => __( 'Gallery Images', 'textdomain' ),
    'type'        => 'gallery',
    'max_items'   => 10,
    'button_text' => __( 'Add Images', 'textdomain' ),
]
```

### Post Selector

Select posts by post type.

```php
'related_posts' => [
    'label'     => __( 'Related Posts', 'textdomain' ),
    'type'      => 'post',
    'post_type' => 'post',        // string or array
    'multiple'  => true,
    'display'   => 'checkbox',    // 'select' or 'checkbox'
]
```

### User Selector

Select users, optionally filtered by role.

```php
'author' => [
    'label'    => __( 'Author', 'textdomain' ),
    'type'     => 'user',
    'role'     => [ 'author', 'editor' ],  // optional filter
    'multiple' => false,
]
```

### Term Selector

Select taxonomy terms.

```php
'categories' => [
    'label'    => __( 'Categories', 'textdomain' ),
    'type'     => 'term',
    'taxonomy' => 'category',
    'multiple' => true,
    'display'  => 'checkbox',
]
```

### Amount Type

Combined numeric input with type selector.

```php
'discount' => [
    'label'         => __( 'Discount', 'textdomain' ),
    'type'          => 'amount_type',
    'type_meta_key' => 'discount_type',
    'type_options'  => [
        'percent' => '%',
        'flat'    => '$',
    ],
    'type_default'  => 'percent',
    'min'           => 0,
    'max'           => 100,
]
```

### Group

Static group of related fields.

```php
'dimensions' => [
    'label'  => __( 'Dimensions', 'textdomain' ),
    'type'   => 'group',
    'fields' => [
        'width'  => [ 'type' => 'number', 'label' => 'Width' ],
        'height' => [ 'type' => 'number', 'label' => 'Height' ],
        'depth'  => [ 'type' => 'number', 'label' => 'Depth' ],
    ],
]
```

### Repeater

Dynamic repeatable field groups with drag-and-drop reordering.

```php
'features' => [
    'label'        => __( 'Features', 'textdomain' ),
    'type'         => 'repeater',
    'button_label' => __( 'Add Feature', 'textdomain' ),
    'max_items'    => 10,
    'min_items'    => 0,
    'collapsed'    => true,
    'fields'       => [
        'icon' => [
            'label' => __( 'Icon', 'textdomain' ),
            'type'  => 'text',
        ],
        'title' => [
            'label' => __( 'Title', 'textdomain' ),
            'type'  => 'text',
        ],
        'description' => [
            'label' => __( 'Description', 'textdomain' ),
            'type'  => 'textarea',
            'rows'  => 2,
        ],
    ],
]
```

Supported field types inside repeaters: text, textarea, number, select, checkbox, url, email, image, file.

## Metabox Configuration Options

| Option       | Type           | Default                    | Description                                     |
|--------------|----------------|----------------------------|-------------------------------------------------|
| `title`      | string         | `'Additional Information'` | Metabox title displayed in admin                |
| `post_types` | string\|array  | `['post']`                 | Post type(s) to register for                    |
| `context`    | string         | `'normal'`                 | Metabox position: normal, side, advanced        |
| `priority`   | string         | `'high'`                   | Metabox priority: high, core, default, low      |
| `prefix`     | string         | `''`                       | Prefix for all meta keys                        |
| `capability` | string         | `'edit_posts'`             | Required capability to view/edit                |
| `fields`     | array          | `[]`                       | Array of field configurations                   |

## Field Configuration Options

| Option              | Type            | Default        | Description                                     |
|---------------------|-----------------|----------------|-------------------------------------------------|
| `label`             | string          | `''`           | Field label text                                |
| `type`              | string          | `'text'`       | Field type                                      |
| `description`       | string          | `''`           | Help text displayed below the field             |
| `default`           | mixed           | `''`           | Default value                                   |
| `placeholder`       | string          | `''`           | Placeholder text for text inputs                |
| `options`           | array\|callable | `[]`           | Options for select fields                       |
| `min`               | int\|float      | `null`         | Minimum value for number fields                 |
| `max`               | int\|float      | `null`         | Maximum value for number fields                 |
| `step`              | int\|float      | `null`         | Step increment for number fields                |
| `rows`              | int             | `5`            | Number of rows for textarea/wysiwyg             |
| `sanitize_callback` | callable        | `null`         | Custom sanitization function                    |
| `capability`        | string          | `'edit_posts'` | Required capability to view/edit                |
| `show_in_rest`      | bool            | `true`         | Expose field via REST API                       |
| `multiple`          | bool            | `false`        | Allow multiple selections (select, post, etc.)  |
| `display`           | string          | `'select'`     | Display as 'select' or 'checkbox' for multiple  |
| `max_items`         | int             | `0`            | Maximum items for gallery/repeater (0=unlimited)|
| `min_items`         | int             | `0`            | Minimum items for repeater                      |
| `collapsed`         | bool            | `false`        | Start repeater rows collapsed                   |
| `button_text`       | string          | `''`           | Custom button text for media fields             |
| `button_label`      | string          | `''`           | Custom button text for repeater add button      |

## Retrieving Field Values

### Standard Method

Use WordPress's built-in function:

```php
$value = get_post_meta( $post_id, 'price', true );
```

### With Default Fallback

Use the helper function to automatically fall back to registered defaults:

```php
$value = get_post_field_value( $post_id, 'price', 'product_info' );
```

### Repeater Values

```php
$features = get_post_meta( $post_id, 'features', true );

if ( ! empty( $features ) && is_array( $features ) ) {
    foreach ( $features as $feature ) {
        echo $feature['icon'];
        echo $feature['title'];
        echo $feature['description'];
    }
}
```

### Gallery Values

```php
$gallery = get_post_meta( $post_id, 'gallery', true );

if ( ! empty( $gallery ) && is_array( $gallery ) ) {
    foreach ( $gallery as $attachment_id ) {
        echo wp_get_attachment_image( $attachment_id, 'medium' );
    }
}
```

### Group Values

```php
$dimensions = get_post_meta( $post_id, 'dimensions', true );

if ( ! empty( $dimensions ) && is_array( $dimensions ) ) {
    echo $dimensions['width'] . ' x ' . $dimensions['height'] . ' x ' . $dimensions['depth'];
}
```

### Get All Registered Fields

```php
$fields = get_metabox_fields( 'product_info' );

foreach ( $fields as $meta_key => $config ) {
    echo $config['label'] . ': ' . get_post_meta( $post_id, $meta_key, true );
}
```

## REST API

All registered fields are automatically available via the REST API:

```
GET /wp-json/wp/v2/product/123
```

Response includes:

```json
{
  "id": 123,
  "title": { "rendered": "Product Name" },
  "meta": {
    "sku": "PRD-001",
    "price": 29.99,
    "features": [
      { "icon": "dashicons-star", "title": "Feature 1", "description": "..." }
    ],
    "gallery": [45, 46, 47]
  }
}
```

To disable REST API exposure for a field:

```php
'internal_notes' => [
    'label'        => __( 'Internal Notes', 'textdomain' ),
    'type'         => 'textarea',
    'show_in_rest' => false,
]
```

## Custom Sanitization

Override the default sanitization for any field:

```php
'allowed_html' => [
    'label'             => __( 'Content', 'textdomain' ),
    'type'              => 'textarea',
    'sanitize_callback' => function( $value ) {
        return wp_kses( $value, [
            'p'      => [],
            'br'     => [],
            'strong' => [],
            'em'     => [],
            'a'      => [ 'href' => [], 'title' => [] ],
        ] );
    },
]
```

## Permission Control

Control field visibility based on user capabilities:

```php
register_post_metabox( 'admin_settings', [
    'title'      => __( 'Admin Settings', 'textdomain' ),
    'post_types' => 'post',
    'capability' => 'manage_options', // Only administrators
    'fields'     => [
        'internal_notes' => [
            'label' => __( 'Internal Notes', 'textdomain' ),
            'type'  => 'textarea',
        ],
    ],
] );
```

## Meta Key Prefixing

Automatically prefix all meta keys:

```php
register_post_metabox( 'product_data', [
    'title'      => __( 'Product Data', 'textdomain' ),
    'post_types' => 'product',
    'prefix'     => '_product_',
    'fields'     => [
        'weight' => [ /* saved as _product_weight */ ],
        'color'  => [ /* saved as _product_color */ ],
    ],
] );
```

## Integration with Register Columns

This library pairs well with [wp-register-columns](https://github.com/arraypress/wp-register-columns) to display field values in admin list tables:

```php
// Register the metabox
register_post_metabox( 'product_info', [
    'title'      => __( 'Product Info', 'textdomain' ),
    'post_types' => 'product',
    'fields'     => [
        'sku'   => [ 'label' => 'SKU', 'type' => 'text' ],
        'price' => [ 'label' => 'Price', 'type' => 'number', 'step' => 0.01 ],
    ],
] );

// Display in list table
register_post_columns( 'product', [
    'sku' => [
        'label'    => __( 'SKU', 'textdomain' ),
        'meta_key' => 'sku',
        'sortable' => true,
    ],
    'price' => [
        'label'            => __( 'Price', 'textdomain' ),
        'meta_key'         => 'price',
        'sortable'         => true,
        'display_callback' => function( $value ) {
            return $value ? '$' . number_format( $value, 2 ) : '—';
        },
    ],
] );
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

GPL-2.0-or-later

## Author

David Sherlock - [ArrayPress](https://arraypress.com/)

## Support

- [Documentation](https://github.com/arraypress/wp-register-post-metabox)
- [Issue Tracker](https://github.com/arraypress/wp-register-post-metabox/issues)