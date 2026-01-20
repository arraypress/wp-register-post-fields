# WordPress Register Post Fields

A lightweight library for registering custom metaboxes with fields on WordPress post edit screens. This library provides
a clean, simple API for adding common field types to any post type without complex configuration.

## Features

- **Simple API**: Register custom metaboxes with minimal code
- **30+ Field Types**: Comprehensive field type support for any use case
- **Conditional Logic**: Show/hide fields based on other field values with `show_when`
- **Repeater Fields**: Dynamic repeatable field groups with drag-and-drop reordering
- **Group Fields**: Static groups of related fields
- **AJAX-Powered Selects**: Searchable selects for posts, terms, users, and custom data
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

---

## Field Types

### Basic Text Fields

#### Text

Standard single-line text input.

```php
'field_name' => [
    'label'       => __( 'Field Label', 'textdomain' ),
    'type'        => 'text',
    'placeholder' => __( 'Placeholder text...', 'textdomain' ),
    'default'     => '',
]
```

#### URL

URL input with HTML5 validation.

```php
'website' => [
    'label'       => __( 'Website', 'textdomain' ),
    'type'        => 'url',
    'placeholder' => 'https://example.com',
]
```

#### Email

Email input with HTML5 validation.

```php
'contact_email' => [
    'label'       => __( 'Contact Email', 'textdomain' ),
    'type'        => 'email',
    'placeholder' => 'name@example.com',
]
```

#### Telephone

Phone number input with optional pattern validation.

```php
'phone' => [
    'label'       => __( 'Phone Number', 'textdomain' ),
    'type'        => 'tel',
    'placeholder' => '+1 (555) 123-4567',
    'pattern'     => '[0-9]{3}-[0-9]{3}-[0-9]{4}', // Optional regex pattern
]
```

#### Password

Password input with show/hide toggle button.

```php
'api_key' => [
    'label'       => __( 'API Key', 'textdomain' ),
    'type'        => 'password',
    'placeholder' => __( 'Enter your API key', 'textdomain' ),
]
```

---

### Multi-line Text Fields

#### Textarea

Multi-line text input.

```php
'description' => [
    'label'       => __( 'Description', 'textdomain' ),
    'type'        => 'textarea',
    'rows'        => 5,
    'placeholder' => __( 'Enter a description...', 'textdomain' ),
]
```

#### WYSIWYG

WordPress rich text editor (TinyMCE).

```php
'content' => [
    'label' => __( 'Content', 'textdomain' ),
    'type'  => 'wysiwyg',
    'rows'  => 10,
]
```

#### Code

Code editor with syntax highlighting using WordPress CodeMirror.

```php
'custom_css' => [
    'label'        => __( 'Custom CSS', 'textdomain' ),
    'type'         => 'code',
    'language'     => 'css',      // html, css, javascript, js, json, php, sql, xml, markdown, md
    'line_numbers' => true,
    'rows'         => 15,
]
```

**Supported Languages:**

- `html` / `htmlmixed`
- `css`
- `javascript` / `js`
- `json`
- `php`
- `sql`
- `xml`
- `markdown` / `md`

---

### Numeric Fields

#### Number

Numeric input with optional constraints.

```php
'quantity' => [
    'label'       => __( 'Quantity', 'textdomain' ),
    'type'        => 'number',
    'min'         => 0,
    'max'         => 100,
    'step'        => 1,
    'default'     => 0,
    'placeholder' => '0',
]
```

#### Range

Range slider input with live value display.

```php
'volume' => [
    'label'   => __( 'Volume', 'textdomain' ),
    'type'    => 'range',
    'min'     => 0,
    'max'     => 100,
    'step'    => 5,
    'default' => 50,
    'unit'    => '%', // Optional unit suffix
]
```

#### Amount Type

Combined numeric input with type selector (e.g., discount amount + percent/flat).

```php
'discount' => [
    'label'         => __( 'Discount', 'textdomain' ),
    'type'          => 'amount_type',
    'type_meta_key' => 'discount_type', // Separate meta key for the type
    'type_options'  => [
        'percent' => '%',
        'flat'    => '$',
    ],
    'type_default'  => 'percent',
    'min'           => 0,
    'max'           => 100,
    'step'          => 0.01,
]
```

#### Dimensions

Combined width × height input.

```php
'image_size' => [
    'label'            => __( 'Image Size', 'textdomain' ),
    'type'             => 'dimensions',
    'dimension_labels' => [
        'width'  => __( 'Width', 'textdomain' ),
        'height' => __( 'Height', 'textdomain' ),
    ],
    'dimension_units'  => 'px', // Optional unit label
    'min'              => 0,
    'max'              => 4000,
    'step'             => 1,
]
```

---

### Date & Time Fields

#### Date

HTML5 date picker.

```php
'start_date' => [
    'label' => __( 'Start Date', 'textdomain' ),
    'type'  => 'date',
]
```

#### Time

HTML5 time picker.

```php
'opening_time' => [
    'label' => __( 'Opening Time', 'textdomain' ),
    'type'  => 'time',
]
```

#### DateTime

HTML5 date and time picker.

```php
'event_datetime' => [
    'label' => __( 'Event Date & Time', 'textdomain' ),
    'type'  => 'datetime',
]
```

#### Date Range

Two date inputs for start and end dates.

```php
'event_dates' => [
    'label'       => __( 'Event Dates', 'textdomain' ),
    'type'        => 'date_range',
    'start_label' => __( 'Start', 'textdomain' ),
    'end_label'   => __( 'End', 'textdomain' ),
]
```

**Value Structure:**

```php
[
    'start' => '2024-01-15',
    'end'   => '2024-01-20',
]
```

#### Time Range

Two time inputs for start and end times.

```php
'business_hours' => [
    'label'       => __( 'Business Hours', 'textdomain' ),
    'type'        => 'time_range',
    'start_label' => __( 'Opens', 'textdomain' ),
    'end_label'   => __( 'Closes', 'textdomain' ),
]
```

**Value Structure:**

```php
[
    'start' => '09:00',
    'end'   => '17:00',
]
```

---

### Choice Fields

#### Select

Dropdown selection with static or dynamic options.

```php
// Static options
'layout' => [
    'label'   => __( 'Layout', 'textdomain' ),
    'type'    => 'select',
    'default' => 'grid',
    'options' => [
        ''     => __( '— Select —', 'textdomain' ),
        'grid' => __( 'Grid', 'textdomain' ),
        'list' => __( 'List', 'textdomain' ),
    ],
]

// Multiple selection
'categories' => [
    'label'    => __( 'Categories', 'textdomain' ),
    'type'     => 'select',
    'multiple' => true,
    'options'  => [
        'tech'    => __( 'Technology', 'textdomain' ),
        'health'  => __( 'Health', 'textdomain' ),
        'finance' => __( 'Finance', 'textdomain' ),
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

#### Checkbox

Boolean toggle checkbox.

```php
'featured' => [
    'label'       => __( 'Featured', 'textdomain' ),
    'type'        => 'checkbox',
    'default'     => 0,
    'description' => __( 'Show in featured section.', 'textdomain' ),
]
```

#### Toggle

Visual toggle switch (alternative to checkbox).

```php
'is_active' => [
    'label'       => __( 'Active', 'textdomain' ),
    'type'        => 'toggle',
    'default'     => 0,
    'on_label'    => __( 'On', 'textdomain' ),  // Optional
    'off_label'   => __( 'Off', 'textdomain' ), // Optional
    'description' => __( 'Enable this feature.', 'textdomain' ),
]
```

#### Radio

Radio button group.

```php
'priority' => [
    'label'   => __( 'Priority', 'textdomain' ),
    'type'    => 'radio',
    'default' => 'medium',
    'layout'  => 'horizontal', // or 'vertical' (default)
    'options' => [
        'low'    => __( 'Low', 'textdomain' ),
        'medium' => __( 'Medium', 'textdomain' ),
        'high'   => __( 'High', 'textdomain' ),
    ],
]
```

#### Button Group

Toggle button group for single or multiple selection.

```php
// Single selection
'alignment' => [
    'label'   => __( 'Alignment', 'textdomain' ),
    'type'    => 'button_group',
    'default' => 'left',
    'options' => [
        'left'   => __( 'Left', 'textdomain' ),
        'center' => __( 'Center', 'textdomain' ),
        'right'  => __( 'Right', 'textdomain' ),
    ],
]

// Multiple selection
'features' => [
    'label'    => __( 'Features', 'textdomain' ),
    'type'     => 'button_group',
    'multiple' => true,
    'options'  => [
        'bold'      => __( 'Bold', 'textdomain' ),
        'italic'    => __( 'Italic', 'textdomain' ),
        'underline' => __( 'Underline', 'textdomain' ),
    ],
]
```

---

### Color Field

#### Color

WordPress color picker with optional default.

```php
'brand_color' => [
    'label'   => __( 'Brand Color', 'textdomain' ),
    'type'    => 'color',
    'default' => '#3498db',
]
```

---

### Media Fields

#### Image

Single image picker from WordPress media library.

```php
'featured_image' => [
    'label'       => __( 'Featured Image', 'textdomain' ),
    'type'        => 'image',
    'button_text' => __( 'Select Image', 'textdomain' ),
]
```

**Stored Value:** Attachment ID (integer)

#### File

Single file picker from WordPress media library (stores attachment ID).

```php
'download_file' => [
    'label'       => __( 'Download File', 'textdomain' ),
    'type'        => 'file',
    'button_text' => __( 'Select File', 'textdomain' ),
]
```

**Stored Value:** Attachment ID (integer)

#### File URL

Text input with media library button (stores URL, editable).

```php
'external_file' => [
    'label'       => __( 'File URL', 'textdomain' ),
    'type'        => 'file_url',
    'button_text' => __( 'Browse', 'textdomain' ),
    'placeholder' => __( 'Enter URL or select from media library', 'textdomain' ),
]
```

**Stored Value:** URL string (editable)

#### Gallery

Multiple image picker with drag-and-drop reordering.

```php
'gallery' => [
    'label'       => __( 'Gallery Images', 'textdomain' ),
    'type'        => 'gallery',
    'max_items'   => 10,  // 0 = unlimited
    'button_text' => __( 'Add Images', 'textdomain' ),
]
```

**Stored Value:** Array of attachment IDs

#### Link

Combined URL, title, and target fields.

```php
'call_to_action' => [
    'label'       => __( 'Call to Action', 'textdomain' ),
    'type'        => 'link',
    'show_title'  => true,  // Show link text field
    'show_target' => true,  // Show "open in new tab" checkbox
]
```

**Value Structure:**

```php
[
    'url'    => 'https://example.com',
    'title'  => 'Click Here',
    'target' => '_blank', // or ''
]
```

#### oEmbed

URL input with embedded preview for supported providers.

```php
'video_embed' => [
    'label'       => __( 'Video URL', 'textdomain' ),
    'type'        => 'oembed',
    'placeholder' => __( 'Enter URL (YouTube, Vimeo, Twitter, etc.)', 'textdomain' ),
]
```

**Supports:** YouTube, Vimeo, Twitter, Instagram, Spotify, and other WordPress-supported oEmbed providers.

---

### Relational Fields (Static)

These fields query all available items on page load. Best for smaller datasets.

#### Post

Post/page/custom post type selector.

```php
// Single selection
'related_post' => [
    'label'     => __( 'Related Post', 'textdomain' ),
    'type'      => 'post',
    'post_type' => 'post', // Can be array: ['post', 'page']
]

// Multiple selection as checkboxes
'related_posts' => [
    'label'     => __( 'Related Posts', 'textdomain' ),
    'type'      => 'post',
    'post_type' => 'post',
    'multiple'  => true,
    'display'   => 'checkbox', // or 'select'
]
```

#### User

WordPress user selector.

```php
// All users
'author' => [
    'label' => __( 'Author', 'textdomain' ),
    'type'  => 'user',
]

// Filtered by role
'editor' => [
    'label'    => __( 'Editor', 'textdomain' ),
    'type'     => 'user',
    'role'     => ['editor', 'administrator'], // Can be string or array
    'multiple' => true,
]
```

#### Term

Taxonomy term selector.

```php
'category' => [
    'label'    => __( 'Category', 'textdomain' ),
    'type'     => 'term',
    'taxonomy' => 'category',
]

'tags' => [
    'label'    => __( 'Tags', 'textdomain' ),
    'type'     => 'term',
    'taxonomy' => 'post_tag',
    'multiple' => true,
    'display'  => 'checkbox',
]
```

---

### Relational Fields (AJAX-Powered)

These fields use Select2 with AJAX search. Best for large datasets.

#### Post AJAX

AJAX-powered post selector.

```php
'related_product' => [
    'label'       => __( 'Related Product', 'textdomain' ),
    'type'        => 'post_ajax',
    'post_type'   => 'product', // Can be array: ['product', 'variation']
    'multiple'    => true,
    'placeholder' => __( 'Search products...', 'textdomain' ),
]
```

#### Taxonomy AJAX

AJAX-powered taxonomy term selector.

```php
'product_categories' => [
    'label'       => __( 'Product Categories', 'textdomain' ),
    'type'        => 'taxonomy_ajax',
    'taxonomy'    => 'product_cat',
    'multiple'    => true,
    'placeholder' => __( 'Search categories...', 'textdomain' ),
]
```

#### User AJAX

AJAX-powered user selector.

```php
'team_members' => [
    'label'       => __( 'Team Members', 'textdomain' ),
    'type'        => 'user_ajax',
    'role'        => ['author', 'editor'], // Optional role filter
    'multiple'    => true,
    'placeholder' => __( 'Search users...', 'textdomain' ),
]
```

#### AJAX (Custom Callback)

Custom AJAX-powered select with your own data source.

```php
'country' => [
    'label'         => __( 'Country', 'textdomain' ),
    'type'          => 'ajax',
    'ajax_callback' => 'my_country_search_callback',
    'multiple'      => false,
    'placeholder'   => __( 'Search countries...', 'textdomain' ),
]

// Callback function
function my_country_search_callback( $search = '', $ids = null ) {
    $countries = [
        'US' => 'United States',
        'UK' => 'United Kingdom',
        'CA' => 'Canada',
        // ... more countries
    ];
    
    $results = [];
    
    // Hydration: return specific items by ID
    if ( $ids ) {
        foreach ( $ids as $id ) {
            if ( isset( $countries[ $id ] ) ) {
                $results[] = [
                    'value' => $id,
                    'label' => $countries[ $id ],
                ];
            }
        }
        return $results;
    }
    
    // Search: filter by search term
    foreach ( $countries as $code => $name ) {
        if ( empty( $search ) || stripos( $name, $search ) !== false ) {
            $results[] = [
                'value' => $code,
                'label' => $name,
            ];
        }
    }
    
    return $results;
}
```

---

### Complex Fields

#### Group

Static group of related fields stored as a single meta value.

```php
'dimensions' => [
    'label'  => __( 'Dimensions', 'textdomain' ),
    'type'   => 'group',
    'fields' => [
        'width' => [
            'label' => __( 'Width', 'textdomain' ),
            'type'  => 'number',
            'min'   => 0,
        ],
        'height' => [
            'label' => __( 'Height', 'textdomain' ),
            'type'  => 'number',
            'min'   => 0,
        ],
        'depth' => [
            'label' => __( 'Depth', 'textdomain' ),
            'type'  => 'number',
            'min'   => 0,
        ],
    ],
]
```

**Value Structure:**

```php
[
    'width'  => 100,
    'height' => 50,
    'depth'  => 25,
]
```

#### Repeater

Dynamic repeatable field groups with drag-and-drop reordering.

```php
'features' => [
    'label'           => __( 'Features', 'textdomain' ),
    'type'            => 'repeater',
    'button_label'    => __( 'Add Feature', 'textdomain' ),
    'max_items'       => 10,    // 0 = unlimited
    'min_items'       => 0,
    'collapsed'       => true,  // Start rows collapsed
    'layout'          => 'vertical', // 'vertical', 'horizontal', or 'table'
    'row_title'       => __( 'Feature {index}', 'textdomain' ), // Dynamic title with {index}
    'row_title_field' => 'title', // Use field value as title
    'fields'          => [
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

**Repeater Layouts:**

- `vertical` (default): Fields stacked vertically in collapsible rows
- `horizontal`: Fields displayed horizontally in each row
- `table`: Compact table layout with column headers

**Row Title Placeholders:**

- `{index}`: Replaced with row number (1, 2, 3...)
- `{value}`: Replaced with value from `row_title_field`

**Value Structure:**

```php
[
    [
        'icon'        => 'star',
        'title'       => 'Feature 1',
        'description' => 'Description of feature 1',
    ],
    [
        'icon'        => 'heart',
        'title'       => 'Feature 2',
        'description' => 'Description of feature 2',
    ],
]
```

---

## Conditional Logic (show_when)

Fields can be shown or hidden based on the values of other fields. This is perfect for creating dynamic forms where
certain options only appear when relevant.

### Simple Shorthand Syntax

```php
'custom_url' => [
    'label'     => __( 'Custom URL', 'textdomain' ),
    'type'      => 'url',
    'show_when' => [ 'use_external_link' => 1 ],
]
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
]
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
]
```

### Available Operators

| Operator       | Description              |
|----------------|--------------------------|
| `==` or `=`    | Equal (loose comparison) |
| `===`          | Strictly equal           |
| `!=` or `<>`   | Not equal                |
| `!==`          | Strictly not equal       |
| `>`            | Greater than             |
| `>=`           | Greater than or equal    |
| `<`            | Less than                |
| `<=`           | Less than or equal       |
| `in`           | Value is in array        |
| `not_in`       | Value is not in array    |
| `contains`     | String contains          |
| `not_contains` | String does not contain  |
| `empty`        | Value is empty           |
| `not_empty`    | Value is not empty       |

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

---

## Metabox Configuration Options

| Option       | Type          | Default                    | Description                                        |
|--------------|---------------|----------------------------|----------------------------------------------------|
| `title`      | string        | `'Additional Information'` | Metabox title displayed in admin                   |
| `post_types` | string\|array | `['post']`                 | Post type(s) to register for                       |
| `context`    | string        | `'normal'`                 | Metabox position: `normal`, `side`, `advanced`     |
| `priority`   | string        | `'high'`                   | Metabox priority: `high`, `core`, `default`, `low` |
| `prefix`     | string        | `''`                       | Prefix for all meta keys                           |
| `capability` | string        | `'edit_posts'`             | Required capability to view/edit                   |
| `fields`     | array         | `[]`                       | Array of field configurations                      |
| `full_width` | bool          | `false`                    | Use full width layout                              |

---

## Field Configuration Options

### Common Options (All Fields)

| Option              | Type     | Default        | Description                         |
|---------------------|----------|----------------|-------------------------------------|
| `label`             | string   | `''`           | Field label text                    |
| `type`              | string   | `'text'`       | Field type                          |
| `description`       | string   | `''`           | Help text displayed below the field |
| `tooltip`           | string   | `''`           | Tooltip text shown on hover         |
| `default`           | mixed    | `''`           | Default value                       |
| `placeholder`       | string   | `''`           | Placeholder text for text inputs    |
| `show_when`         | array    | `[]`           | Conditional visibility rules        |
| `sanitize_callback` | callable | `null`         | Custom sanitization function        |
| `capability`        | string   | `'edit_posts'` | Required capability to view/edit    |
| `show_in_rest`      | bool     | `true`         | Expose field via REST API           |

### Choice Field Options

| Option     | Type            | Default      | Description                                        |
|------------|-----------------|--------------|----------------------------------------------------|
| `options`  | array\|callable | `[]`         | Options for select/radio/button_group              |
| `multiple` | bool            | `false`      | Allow multiple selections                          |
| `display`  | string          | `'select'`   | Display as `'select'` or `'checkbox'` for multiple |
| `layout`   | string          | `'vertical'` | Layout for radio: `'vertical'` or `'horizontal'`   |

### Number Field Options

| Option | Type       | Default | Description                  |
|--------|------------|---------|------------------------------|
| `min`  | int\|float | `null`  | Minimum value                |
| `max`  | int\|float | `null`  | Maximum value                |
| `step` | int\|float | `null`  | Step increment               |
| `unit` | string     | `''`    | Unit suffix for range fields |

### Text Area Options

| Option | Type | Default | Description                         |
|--------|------|---------|-------------------------------------|
| `rows` | int  | `5`     | Number of rows for textarea/wysiwyg |

### Code Editor Options

| Option         | Type   | Default  | Description                  |
|----------------|--------|----------|------------------------------|
| `language`     | string | `'html'` | Syntax highlighting language |
| `line_numbers` | bool   | `true`   | Show line numbers            |

### Media Field Options

| Option        | Type   | Default | Description                             |
|---------------|--------|---------|-----------------------------------------|
| `button_text` | string | `''`    | Custom button text                      |
| `max_items`   | int    | `0`     | Maximum items for gallery (0=unlimited) |
| `mime_types`  | array  | `[]`    | Allowed MIME types                      |

### Link Field Options

| Option        | Type | Default | Description                     |
|---------------|------|---------|---------------------------------|
| `show_title`  | bool | `true`  | Show link text field            |
| `show_target` | bool | `true`  | Show "open in new tab" checkbox |

### Relational Field Options

| Option      | Type          | Default      | Description                  |
|-------------|---------------|--------------|------------------------------|
| `post_type` | string\|array | `'post'`     | Post type(s) for post fields |
| `taxonomy`  | string        | `'category'` | Taxonomy for term fields     |
| `role`      | string\|array | `[]`         | User role(s) for user fields |

### Amount Type Options

| Option          | Type   | Default | Description                                              |
|-----------------|--------|---------|----------------------------------------------------------|
| `type_meta_key` | string | `''`    | Meta key for storing the type                            |
| `type_options`  | array  | `[]`    | Type options (e.g., `['percent' => '%', 'flat' => '$']`) |
| `type_default`  | string | `''`    | Default type value                                       |

### Dimensions Options

| Option             | Type   | Default | Description                                 |
|--------------------|--------|---------|---------------------------------------------|
| `dimension_labels` | array  | `[]`    | Labels: `['width' => 'W', 'height' => 'H']` |
| `dimension_units`  | string | `''`    | Unit label (e.g., `'px'`, `'cm'`)           |

### Date/Time Range Options

| Option        | Type   | Default   | Description           |
|---------------|--------|-----------|-----------------------|
| `start_label` | string | `'Start'` | Label for start field |
| `end_label`   | string | `'End'`   | Label for end field   |

### Group/Repeater Options

| Option            | Type   | Default      | Description                                     |
|-------------------|--------|--------------|-------------------------------------------------|
| `fields`          | array  | `[]`         | Nested field configurations                     |
| `button_label`    | string | `''`         | Add row button text (repeater)                  |
| `max_items`       | int    | `0`          | Maximum rows (0=unlimited)                      |
| `min_items`       | int    | `0`          | Minimum rows                                    |
| `collapsed`       | bool   | `false`      | Start rows collapsed                            |
| `layout`          | string | `'vertical'` | Layout: `'vertical'`, `'horizontal'`, `'table'` |
| `row_title`       | string | `''`         | Row title template with `{index}` placeholder   |
| `row_title_field` | string | `''`         | Field key to use as row title                   |
| `width`           | string | `''`         | Field width in repeater (e.g., `'25%'`)         |

---

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

### Group Values

```php
$dimensions = get_post_meta( $post_id, 'dimensions', true );

if ( ! empty( $dimensions ) ) {
    echo 'Width: ' . $dimensions['width'];
    echo 'Height: ' . $dimensions['height'];
    echo 'Depth: ' . $dimensions['depth'];
}
```

### Link Values

```php
$link = get_post_meta( $post_id, 'call_to_action', true );

if ( ! empty( $link['url'] ) ) {
    $target = $link['target'] === '_blank' ? ' target="_blank" rel="noopener"' : '';
    echo '<a href="' . esc_url( $link['url'] ) . '"' . $target . '>';
    echo esc_html( $link['title'] ?: 'Learn More' );
    echo '</a>';
}
```

### Amount Type Values

```php
$discount_amount = get_post_meta( $post_id, 'discount', true );
$discount_type   = get_post_meta( $post_id, 'discount_type', true );

if ( $discount_amount ) {
    if ( $discount_type === 'percent' ) {
        echo $discount_amount . '% off';
    } else {
        echo '$' . $discount_amount . ' off';
    }
}
```

---

## Helper Functions

### register_post_fields()

Register a new metabox with fields.

```php
$metabox = register_post_fields( 'metabox_id', $config );
```

### get_post_field_value()

Get a field value with default fallback.

```php
$value = get_post_field_value( $post_id, 'meta_key', 'metabox_id' );
```

### get_post_fields()

Get all field configurations for a metabox.

```php
$fields = get_post_fields( 'metabox_id' );
```

### get_post_field_config()

Get configuration for a specific field.

```php
$config = get_post_field_config( 'metabox_id', 'meta_key' );
```

### get_all_post_field_groups()

Get all registered metaboxes.

```php
$groups = get_all_post_field_groups();
```

---

## REST API

All registered fields are automatically available via the REST API:

```
GET /wp-json/wp/v2/product/123
```

Response includes:

```json
{
  "id": 123,
  "title": {
    "rendered": "Product Name"
  },
  "meta": {
    "sku": "PRD-001",
    "price": 29.99,
    "features": [
      {
        "icon": "dashicons-star",
        "title": "Feature 1",
        "description": "..."
      }
    ],
    "dimensions": {
      "width": 100,
      "height": 50,
      "depth": 25
    }
  }
}
```

---

## Field Types Quick Reference

| Type            | Description                          | Stored Value                 |
|-----------------|--------------------------------------|------------------------------|
| `text`          | Single-line text input               | string                       |
| `url`           | URL input with validation            | string                       |
| `email`         | Email input with validation          | string                       |
| `tel`           | Phone number input                   | string                       |
| `password`      | Password with show/hide toggle       | string                       |
| `textarea`      | Multi-line text input                | string                       |
| `wysiwyg`       | Rich text editor                     | string (HTML)                |
| `code`          | Code editor with syntax highlighting | string                       |
| `number`        | Numeric input                        | int\|float                   |
| `range`         | Range slider                         | int\|float                   |
| `amount_type`   | Number + type selector               | float (+ separate type meta) |
| `dimensions`    | Width × height                       | array                        |
| `date`          | Date picker                          | string (Y-m-d)               |
| `time`          | Time picker                          | string (H:i)                 |
| `datetime`      | Date and time picker                 | string                       |
| `date_range`    | Start and end dates                  | array                        |
| `time_range`    | Start and end times                  | array                        |
| `color`         | Color picker                         | string (hex)                 |
| `select`        | Dropdown selection                   | string\|array                |
| `checkbox`      | Boolean toggle                       | int (0\|1)                   |
| `toggle`        | Visual toggle switch                 | int (0\|1)                   |
| `radio`         | Radio button group                   | string                       |
| `button_group`  | Toggle button group                  | string\|array                |
| `image`         | Single image picker                  | int (attachment ID)          |
| `file`          | Single file picker                   | int (attachment ID)          |
| `file_url`      | File URL input                       | string (URL)                 |
| `gallery`       | Multiple images                      | array (attachment IDs)       |
| `link`          | URL + title + target                 | array                        |
| `oembed`        | Embeddable URL                       | string (URL)                 |
| `post`          | Post selector (static)               | int\|array                   |
| `user`          | User selector (static)               | int\|array                   |
| `term`          | Term selector (static)               | int\|array                   |
| `post_ajax`     | Post selector (AJAX)                 | int\|array                   |
| `taxonomy_ajax` | Term selector (AJAX)                 | int\|array                   |
| `user_ajax`     | User selector (AJAX)                 | int\|array                   |
| `ajax`          | Custom AJAX selector                 | mixed                        |
| `group`         | Static field group                   | array                        |
| `repeater`      | Dynamic field group                  | array                        |

---

## Nested Field Support

The following field types are supported inside **groups** and **repeaters**:

| Supported | Field Type            |
|-----------|-----------------------|
| ✅         | text, url, email, tel |
| ✅         | textarea              |
| ✅         | number                |
| ✅         | select                |
| ✅         | checkbox              |
| ✅         | radio                 |
| ✅         | button_group          |
| ✅         | range                 |
| ✅         | image                 |
| ✅         | file                  |
| ✅         | file_url              |
| ✅         | user                  |
| ✅         | ajax                  |
| ✅         | post_ajax             |
| ✅         | taxonomy_ajax         |
| ✅         | user_ajax             |
| ❌         | wysiwyg               |
| ❌         | gallery               |
| ❌         | group (no nesting)    |
| ❌         | repeater (no nesting) |

---

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

GPL-2.0-or-later

## Author

David Sherlock - [ArrayPress](https://arraypress.com/)