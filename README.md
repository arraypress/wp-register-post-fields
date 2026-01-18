# WordPress Register Post Fields

A lightweight library for registering custom metaboxes with fields on WordPress post edit screens. This library provides a clean, simple API for adding common field types to any post type without complex configuration.

## Features

- **Simple API**: Register custom metaboxes with minimal code
- **Conditional Logic**: Show/hide fields based on other field values with `show_when`
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
- **Lightweight**: External CSS/JS assets, leverages WordPress built-in functionality

## Requirements

- PHP 7.4 or higher
- WordPress 5.0 or higher

## Installation

Install via Composer:

```bash
composer require arraypress/wp-register-post-fields
```

## Basic Usage

### Simple Metabox with Text Fields

```php
register_post_fields( 'product_info', [
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

## Conditional Logic (show_when)

Fields can be shown or hidden based on the values of other fields. This is perfect for creating dynamic forms where certain options only appear when relevant.

### Simple Shorthand Syntax

```php
'custom_url' => [
    'label'     => __( 'Custom URL', 'textdomain' ),
    'type'      => 'url',
    'show_when' => [ 'use_external_link' => 1 ],
],
```

### Explicit Syntax with Operators

```php
'discount_code' => [
    'label'     => __( 'Discount Code', 'textdomain' ),
    'type'      => 'text',
    'show_when' => [
        'field'    => 'enable_discount',
        'operator' => '==',
        'value'    => 1,
    ],
],
```

### Multiple Conditions (AND Logic)

All conditions must be true for the field to show:

```php
'shipping_notes' => [
    'label'     => __( 'Shipping Notes', 'textdomain' ),
    'type'      => 'textarea',
    'show_when' => [
        [ 'field' => 'product_type', 'value' => 'physical' ],
        [ 'field' => 'requires_shipping', 'value' => 1 ],
    ],
],
```

### Available Operators

| Operator | Description |
|----------|-------------|
| `==` or `=` | Equal (loose comparison) |
| `===` | Strictly equal |
| `!=` or `<>` | Not equal |
| `!==` | Strictly not equal |
| `>` | Greater than |
| `>=` | Greater than or equal |
| `<` | Less than |
| `<=` | Less than or equal |
| `in` | Value is in array |
| `not_in` | Value is not in array |
| `contains` | String contains |
| `not_contains` | String does not contain |
| `empty` | Value is empty |
| `not_empty` | Value is not empty |

### Complete Conditional Example

```php
register_post_fields( 'product_options', [
    'title'      => __( 'Product Options', 'textdomain' ),
    'post_types' => 'product',
    'fields'     => [
        'product_type' => [
            'label'   => __( 'Product Type', 'textdomain' ),
            'type'    => 'select',
            'options' => [
                'physical' => __( 'Physical Product', 'textdomain' ),
                'digital'  => __( 'Digital Product', 'textdomain' ),
                'service'  => __( 'Service', 'textdomain' ),
            ],
        ],
        // Only show for physical products
        'weight' => [
            'label'     => __( 'Weight (kg)', 'textdomain' ),
            'type'      => 'number',
            'step'      => 0.01,
            'show_when' => [ 'product_type' => 'physical' ],
        ],
        'dimensions' => [
            'label'     => __( 'Dimensions', 'textdomain' ),
            'type'      => 'group',
            'show_when' => [ 'product_type' => 'physical' ],
            'fields'    => [
                'width'  => [ 'label' => 'Width', 'type' => 'number' ],
                'height' => [ 'label' => 'Height', 'type' => 'number' ],
                'depth'  => [ 'label' => 'Depth', 'type' => 'number' ],
            ],
        ],
        // Only show for digital products
        'download_file' => [
            'label'     => __( 'Download File', 'textdomain' ),
            'type'      => 'file',
            'show_when' => [ 'product_type' => 'digital' ],
        ],
        'download_limit' => [
            'label'     => __( 'Download Limit', 'textdomain' ),
            'type'      => 'number',
            'show_when' => [ 'product_type' => 'digital' ],
        ],
        // Only show for services
        'duration' => [
            'label'     => __( 'Duration (hours)', 'textdomain' ),
            'type'      => 'number',
            'show_when' => [ 'product_type' => 'service' ],
        ],
    ],
] );
```

### Conditional Fields in Repeaters

Conditional logic also works within repeater fields:

```php
register_post_fields( 'rewards', [
    'title'      => __( 'Rewards', 'textdomain' ),
    'post_types' => 'campaign',
    'fields'     => [
        'rewards' => [
            'label'        => __( 'Rewards', 'textdomain' ),
            'type'         => 'repeater',
            'button_label' => __( 'Add Reward', 'textdomain' ),
            'fields'       => [
                'reward_type' => [
                    'label'   => __( 'Reward Type', 'textdomain' ),
                    'type'    => 'select',
                    'options' => [
                        ''           => __( '— Select —', 'textdomain' ),
                        'send_email' => __( 'Send Email', 'textdomain' ),
                        'discount'   => __( 'Offer Discount', 'textdomain' ),
                        'add_points' => __( 'Add Points', 'textdomain' ),
                    ],
                ],
                'email_subject' => [
                    'label'     => __( 'Email Subject', 'textdomain' ),
                    'type'      => 'text',
                    'show_when' => [ 'reward_type' => 'send_email' ],
                ],
                'email_body' => [
                    'label'     => __( 'Email Body', 'textdomain' ),
                    'type'      => 'textarea',
                    'show_when' => [ 'reward_type' => 'send_email' ],
                ],
                'discount_amount' => [
                    'label'     => __( 'Discount Amount', 'textdomain' ),
                    'type'      => 'number',
                    'show_when' => [ 'reward_type' => 'discount' ],
                ],
                'points' => [
                    'label'     => __( 'Points to Add', 'textdomain' ),
                    'type'      => 'number',
                    'show_when' => [ 'reward_type' => 'add_points' ],
                ],
            ],
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

### Image

Single image picker from media library.

```php
'featured_image' => [
    'label'       => __( 'Featured Image', 'textdomain' ),
    'type'        => 'image',
    'button_text' => __( 'Select Image', 'textdomain' ),
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
| `show_when`         | array           | `[]`           | Conditional visibility rules                    |
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

## Retrieving Field Values

### Standard Method

```php
$value = get_post_meta( $post_id, 'price', true );
```

### With Default Fallback

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
    ]
  }
}
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

GPL-2.0-or-later

## Author

David Sherlock - [ArrayPress](https://arraypress.com/)
