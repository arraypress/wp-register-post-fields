# Practical Examples

Copy-paste ready metabox configurations for common use cases. Each example is self-contained and production-ready.

---

## Table of Contents

1. [E-commerce Product](#e-commerce-product)
2. [Real Estate Listing](#real-estate-listing)
3. [Event](#event)
4. [Restaurant Menu Item](#restaurant-menu-item)
5. [Job Listing](#job-listing)
6. [Team Member](#team-member)
7. [Testimonial](#testimonial)
8. [Portfolio Project](#portfolio-project)
9. [Podcast Episode](#podcast-episode)
10. [Recipe](#recipe)
11. [Course](#course)
12. [FAQ](#faq)
13. [Location/Branch](#locationbranch)
14. [Service](#service)
15. [Video](#video)
16. [Download/Resource](#downloadresource)
17. [Pricing Plan](#pricing-plan)
18. [Case Study](#case-study)
19. [Press Release](#press-release)
20. [Book](#book)

---

## E-commerce Product

Complete product metabox with pricing, inventory, variations, and shipping.

```php
register_post_fields( 'product_details', [
    'title'      => __( 'Product Details', 'theme' ),
    'post_types' => 'product',
    'fields'     => [
        'sku' => [
            'label'       => __( 'SKU', 'theme' ),
            'type'        => 'text',
            'placeholder' => 'PRD-0000',
        ],
        'regular_price' => [
            'label' => __( 'Regular Price', 'theme' ),
            'type'  => 'number',
            'min'   => 0,
            'step'  => 0.01,
        ],
        'sale_price' => [
            'label' => __( 'Sale Price', 'theme' ),
            'type'  => 'number',
            'min'   => 0,
            'step'  => 0.01,
        ],
        'stock_status' => [
            'label'   => __( 'Stock Status', 'theme' ),
            'type'    => 'select',
            'default' => 'instock',
            'options' => [
                'instock'     => __( 'In Stock', 'theme' ),
                'outofstock'  => __( 'Out of Stock', 'theme' ),
                'onbackorder' => __( 'On Backorder', 'theme' ),
            ],
        ],
        'stock_quantity' => [
            'label'     => __( 'Stock Quantity', 'theme' ),
            'type'      => 'number',
            'min'       => 0,
            'show_when' => [
                'field'    => 'stock_status',
                'operator' => '!=',
                'value'    => 'outofstock',
            ],
        ],
        'product_type' => [
            'label'   => __( 'Product Type', 'theme' ),
            'type'    => 'select',
            'options' => [
                'simple'   => __( 'Simple', 'theme' ),
                'variable' => __( 'Variable', 'theme' ),
                'digital'  => __( 'Digital', 'theme' ),
            ],
        ],
        'weight' => [
            'label'     => __( 'Weight (kg)', 'theme' ),
            'type'      => 'number',
            'min'       => 0,
            'step'      => 0.01,
            'show_when' => [
                'field'    => 'product_type',
                'operator' => '!=',
                'value'    => 'digital',
            ],
        ],
        'dimensions' => [
            'label'     => __( 'Dimensions (cm)', 'theme' ),
            'type'      => 'group',
            'show_when' => [
                'field'    => 'product_type',
                'operator' => '!=',
                'value'    => 'digital',
            ],
            'fields' => [
                'length' => [ 'label' => __( 'Length', 'theme' ), 'type' => 'number', 'min' => 0 ],
                'width'  => [ 'label' => __( 'Width', 'theme' ), 'type' => 'number', 'min' => 0 ],
                'height' => [ 'label' => __( 'Height', 'theme' ), 'type' => 'number', 'min' => 0 ],
            ],
        ],
        'download_file' => [
            'label'     => __( 'Download File', 'theme' ),
            'type'      => 'file',
            'show_when' => [ 'product_type' => 'digital' ],
        ],
        'gallery' => [
            'label'     => __( 'Product Gallery', 'theme' ),
            'type'      => 'gallery',
            'max_items' => 10,
        ],
        'features' => [
            'label'        => __( 'Key Features', 'theme' ),
            'type'         => 'repeater',
            'button_label' => __( 'Add Feature', 'theme' ),
            'max_items'    => 8,
            'layout'       => 'table',
            'fields'       => [
                'feature' => [ 'label' => __( 'Feature', 'theme' ), 'type' => 'text' ],
            ],
        ],
    ],
] );
```

---

## Real Estate Listing

Property listing with details, features, and agent info.

```php
register_post_fields( 'property_details', [
    'title'      => __( 'Property Details', 'theme' ),
    'post_types' => 'property',
    'fields'     => [
        'listing_type' => [
            'label'   => __( 'Listing Type', 'theme' ),
            'type'    => 'button_group',
            'default' => 'sale',
            'options' => [
                'sale' => __( 'For Sale', 'theme' ),
                'rent' => __( 'For Rent', 'theme' ),
            ],
        ],
        'price' => [
            'label' => __( 'Price', 'theme' ),
            'type'  => 'number',
            'min'   => 0,
        ],
        'price_suffix' => [
            'label'     => __( 'Price Suffix', 'theme' ),
            'type'      => 'select',
            'show_when' => [ 'listing_type' => 'rent' ],
            'options'   => [
                'month' => __( '/ month', 'theme' ),
                'week'  => __( '/ week', 'theme' ),
                'year'  => __( '/ year', 'theme' ),
            ],
        ],
        'property_type' => [
            'label'   => __( 'Property Type', 'theme' ),
            'type'    => 'select',
            'options' => [
                ''          => __( '— Select —', 'theme' ),
                'house'     => __( 'House', 'theme' ),
                'apartment' => __( 'Apartment', 'theme' ),
                'condo'     => __( 'Condo', 'theme' ),
                'townhouse' => __( 'Townhouse', 'theme' ),
                'land'      => __( 'Land', 'theme' ),
                'commercial'=> __( 'Commercial', 'theme' ),
            ],
        ],
        'status' => [
            'label'   => __( 'Status', 'theme' ),
            'type'    => 'select',
            'default' => 'available',
            'options' => [
                'available' => __( 'Available', 'theme' ),
                'pending'   => __( 'Pending', 'theme' ),
                'sold'      => __( 'Sold', 'theme' ),
                'rented'    => __( 'Rented', 'theme' ),
            ],
        ],
        'bedrooms'     => [ 'label' => __( 'Bedrooms', 'theme' ), 'type' => 'number', 'min' => 0, 'max' => 20 ],
        'bathrooms'    => [ 'label' => __( 'Bathrooms', 'theme' ), 'type' => 'number', 'min' => 0, 'max' => 20, 'step' => 0.5 ],
        'square_feet'  => [ 'label' => __( 'Square Feet', 'theme' ), 'type' => 'number', 'min' => 0 ],
        'lot_size'     => [ 'label' => __( 'Lot Size (acres)', 'theme' ), 'type' => 'number', 'min' => 0, 'step' => 0.01 ],
        'year_built'   => [ 'label' => __( 'Year Built', 'theme' ), 'type' => 'number', 'min' => 1800, 'max' => 2030 ],
        'garage_spaces'=> [ 'label' => __( 'Garage Spaces', 'theme' ), 'type' => 'number', 'min' => 0, 'max' => 10, 'default' => 0 ],
        'address' => [
            'label'  => __( 'Address', 'theme' ),
            'type'   => 'group',
            'fields' => [
                'street' => [ 'label' => __( 'Street', 'theme' ), 'type' => 'text' ],
                'city'   => [ 'label' => __( 'City', 'theme' ), 'type' => 'text' ],
                'state'  => [ 'label' => __( 'State', 'theme' ), 'type' => 'text' ],
                'zip'    => [ 'label' => __( 'ZIP', 'theme' ), 'type' => 'text' ],
            ],
        ],
        'features' => [
            'label'    => __( 'Features', 'theme' ),
            'type'     => 'select',
            'multiple' => true,
            'display'  => 'checkbox',
            'options'  => [
                'pool'       => __( 'Pool', 'theme' ),
                'fireplace'  => __( 'Fireplace', 'theme' ),
                'ac'         => __( 'A/C', 'theme' ),
                'basement'   => __( 'Basement', 'theme' ),
                'hardwood'   => __( 'Hardwood Floors', 'theme' ),
                'security'   => __( 'Security System', 'theme' ),
            ],
        ],
        'virtual_tour' => [ 'label' => __( 'Virtual Tour URL', 'theme' ), 'type' => 'url' ],
        'video_tour'   => [ 'label' => __( 'Video Tour', 'theme' ), 'type' => 'oembed' ],
        'gallery'      => [ 'label' => __( 'Photos', 'theme' ), 'type' => 'gallery', 'max_items' => 30 ],
        'floor_plan'   => [ 'label' => __( 'Floor Plan', 'theme' ), 'type' => 'image' ],
        'agent'        => [ 'label' => __( 'Agent', 'theme' ), 'type' => 'user_ajax', 'role' => 'agent' ],
    ],
] );
```

---

## Event

Event with scheduling, tickets, and registration.

```php
register_post_fields( 'event_details', [
    'title'      => __( 'Event Details', 'theme' ),
    'post_types' => 'event',
    'fields'     => [
        'start_date' => [ 'label' => __( 'Start', 'theme' ), 'type' => 'datetime' ],
        'end_date'   => [ 'label' => __( 'End', 'theme' ), 'type' => 'datetime' ],
        'all_day'    => [ 'label' => __( 'All Day', 'theme' ), 'type' => 'checkbox' ],
        'location_type' => [
            'label'   => __( 'Location', 'theme' ),
            'type'    => 'button_group',
            'default' => 'venue',
            'options' => [
                'venue'  => __( 'In Person', 'theme' ),
                'online' => __( 'Online', 'theme' ),
                'hybrid' => __( 'Hybrid', 'theme' ),
            ],
        ],
        'venue_name' => [
            'label'     => __( 'Venue Name', 'theme' ),
            'type'      => 'text',
            'show_when' => [ 'field' => 'location_type', 'operator' => 'in', 'value' => [ 'venue', 'hybrid' ] ],
        ],
        'venue_address' => [
            'label'     => __( 'Venue Address', 'theme' ),
            'type'      => 'textarea',
            'rows'      => 2,
            'show_when' => [ 'field' => 'location_type', 'operator' => 'in', 'value' => [ 'venue', 'hybrid' ] ],
        ],
        'online_url' => [
            'label'     => __( 'Online URL', 'theme' ),
            'type'      => 'url',
            'show_when' => [ 'field' => 'location_type', 'operator' => 'in', 'value' => [ 'online', 'hybrid' ] ],
        ],
        'is_paid'      => [ 'label' => __( 'Paid Event', 'theme' ), 'type' => 'checkbox' ],
        'ticket_price' => [ 'label' => __( 'Ticket Price', 'theme' ), 'type' => 'number', 'min' => 0, 'step' => 0.01, 'show_when' => [ 'is_paid' => 1 ] ],
        'capacity'     => [ 'label' => __( 'Capacity', 'theme' ), 'type' => 'number', 'min' => 0 ],
        'registration_url' => [ 'label' => __( 'Registration URL', 'theme' ), 'type' => 'url' ],
        'organizer' => [
            'label'  => __( 'Organizer', 'theme' ),
            'type'   => 'group',
            'fields' => [
                'name'  => [ 'label' => __( 'Name', 'theme' ), 'type' => 'text' ],
                'email' => [ 'label' => __( 'Email', 'theme' ), 'type' => 'email' ],
                'phone' => [ 'label' => __( 'Phone', 'theme' ), 'type' => 'tel' ],
            ],
        ],
        'speakers' => [
            'label'        => __( 'Speakers', 'theme' ),
            'type'         => 'repeater',
            'button_label' => __( 'Add Speaker', 'theme' ),
            'fields'       => [
                'photo' => [ 'label' => __( 'Photo', 'theme' ), 'type' => 'image' ],
                'name'  => [ 'label' => __( 'Name', 'theme' ), 'type' => 'text' ],
                'title' => [ 'label' => __( 'Title', 'theme' ), 'type' => 'text' ],
                'bio'   => [ 'label' => __( 'Bio', 'theme' ), 'type' => 'textarea', 'rows' => 2 ],
            ],
        ],
    ],
] );
```

---

## Restaurant Menu Item

Menu item with pricing and dietary info.

```php
register_post_fields( 'menu_item', [
    'title'      => __( 'Menu Item', 'theme' ),
    'post_types' => 'menu_item',
    'fields'     => [
        'price'    => [ 'label' => __( 'Price', 'theme' ), 'type' => 'number', 'min' => 0, 'step' => 0.01 ],
        'calories' => [ 'label' => __( 'Calories', 'theme' ), 'type' => 'number', 'min' => 0 ],
        'spice_level' => [
            'label'   => __( 'Spice Level', 'theme' ),
            'type'    => 'button_group',
            'options' => [ '0' => 'None', '1' => 'Mild', '2' => 'Medium', '3' => 'Hot' ],
        ],
        'dietary' => [
            'label'    => __( 'Dietary', 'theme' ),
            'type'     => 'select',
            'multiple' => true,
            'display'  => 'checkbox',
            'options'  => [
                'vegetarian'  => __( 'Vegetarian', 'theme' ),
                'vegan'       => __( 'Vegan', 'theme' ),
                'gluten_free' => __( 'Gluten-Free', 'theme' ),
                'dairy_free'  => __( 'Dairy-Free', 'theme' ),
                'nut_free'    => __( 'Nut-Free', 'theme' ),
            ],
        ],
        'allergens'   => [ 'label' => __( 'Allergens', 'theme' ), 'type' => 'text' ],
        'available'   => [ 'label' => __( 'Available', 'theme' ), 'type' => 'toggle', 'default' => 1 ],
        'featured'    => [ 'label' => __( 'Featured', 'theme' ), 'type' => 'checkbox' ],
        'sizes' => [
            'label'        => __( 'Sizes', 'theme' ),
            'type'         => 'repeater',
            'button_label' => __( 'Add Size', 'theme' ),
            'layout'       => 'table',
            'fields'       => [
                'name'  => [ 'label' => __( 'Size', 'theme' ), 'type' => 'text' ],
                'price' => [ 'label' => __( 'Price', 'theme' ), 'type' => 'number', 'min' => 0, 'step' => 0.01 ],
            ],
        ],
        'add_ons' => [
            'label'        => __( 'Add-ons', 'theme' ),
            'type'         => 'repeater',
            'button_label' => __( 'Add Option', 'theme' ),
            'layout'       => 'table',
            'fields'       => [
                'name'  => [ 'label' => __( 'Add-on', 'theme' ), 'type' => 'text' ],
                'price' => [ 'label' => __( 'Extra', 'theme' ), 'type' => 'number', 'min' => 0, 'step' => 0.01 ],
            ],
        ],
        'photo' => [ 'label' => __( 'Photo', 'theme' ), 'type' => 'image' ],
    ],
] );
```

---

## Job Listing

Job posting with requirements and benefits.

```php
register_post_fields( 'job_details', [
    'title'      => __( 'Job Details', 'theme' ),
    'post_types' => 'job',
    'fields'     => [
        'job_type' => [
            'label'   => __( 'Job Type', 'theme' ),
            'type'    => 'select',
            'options' => [
                'full_time'  => __( 'Full-time', 'theme' ),
                'part_time'  => __( 'Part-time', 'theme' ),
                'contract'   => __( 'Contract', 'theme' ),
                'freelance'  => __( 'Freelance', 'theme' ),
                'internship' => __( 'Internship', 'theme' ),
            ],
        ],
        'experience_level' => [
            'label'   => __( 'Experience', 'theme' ),
            'type'    => 'select',
            'options' => [
                'entry'  => __( 'Entry Level', 'theme' ),
                'mid'    => __( 'Mid Level', 'theme' ),
                'senior' => __( 'Senior', 'theme' ),
            ],
        ],
        'remote_option' => [
            'label'   => __( 'Remote', 'theme' ),
            'type'    => 'button_group',
            'default' => 'onsite',
            'options' => [
                'onsite' => __( 'On-site', 'theme' ),
                'hybrid' => __( 'Hybrid', 'theme' ),
                'remote' => __( 'Remote', 'theme' ),
            ],
        ],
        'location' => [
            'label'     => __( 'Location', 'theme' ),
            'type'      => 'text',
            'show_when' => [ 'field' => 'remote_option', 'operator' => '!=', 'value' => 'remote' ],
        ],
        'salary_range' => [
            'label'  => __( 'Salary Range', 'theme' ),
            'type'   => 'group',
            'fields' => [
                'min' => [ 'label' => __( 'Min', 'theme' ), 'type' => 'number', 'min' => 0 ],
                'max' => [ 'label' => __( 'Max', 'theme' ), 'type' => 'number', 'min' => 0 ],
            ],
        ],
        'application_deadline' => [ 'label' => __( 'Deadline', 'theme' ), 'type' => 'date' ],
        'apply_url'   => [ 'label' => __( 'Apply URL', 'theme' ), 'type' => 'url' ],
        'apply_email' => [ 'label' => __( 'Apply Email', 'theme' ), 'type' => 'email' ],
        'requirements' => [
            'label'        => __( 'Requirements', 'theme' ),
            'type'         => 'repeater',
            'button_label' => __( 'Add', 'theme' ),
            'layout'       => 'table',
            'fields'       => [
                'item' => [ 'label' => __( 'Requirement', 'theme' ), 'type' => 'text' ],
            ],
        ],
        'responsibilities' => [
            'label'        => __( 'Responsibilities', 'theme' ),
            'type'         => 'repeater',
            'button_label' => __( 'Add', 'theme' ),
            'layout'       => 'table',
            'fields'       => [
                'item' => [ 'label' => __( 'Responsibility', 'theme' ), 'type' => 'text' ],
            ],
        ],
        'benefits' => [
            'label'    => __( 'Benefits', 'theme' ),
            'type'     => 'select',
            'multiple' => true,
            'display'  => 'checkbox',
            'options'  => [
                'health'    => __( 'Health Insurance', 'theme' ),
                'dental'    => __( 'Dental', 'theme' ),
                '401k'      => __( '401(k)', 'theme' ),
                'pto'       => __( 'Paid Time Off', 'theme' ),
                'remote'    => __( 'Remote Work', 'theme' ),
                'education' => __( 'Education Assistance', 'theme' ),
            ],
        ],
    ],
] );
```

---

## Team Member

Staff profile with bio and social links.

```php
register_post_fields( 'team_member', [
    'title'      => __( 'Team Member', 'theme' ),
    'post_types' => 'team',
    'fields'     => [
        'job_title' => [ 'label' => __( 'Job Title', 'theme' ), 'type' => 'text' ],
        'department' => [
            'label'   => __( 'Department', 'theme' ),
            'type'    => 'select',
            'options' => [
                ''           => __( '— Select —', 'theme' ),
                'executive'  => __( 'Executive', 'theme' ),
                'sales'      => __( 'Sales', 'theme' ),
                'marketing'  => __( 'Marketing', 'theme' ),
                'engineering'=> __( 'Engineering', 'theme' ),
                'support'    => __( 'Support', 'theme' ),
            ],
        ],
        'email' => [ 'label' => __( 'Email', 'theme' ), 'type' => 'email' ],
        'phone' => [ 'label' => __( 'Phone', 'theme' ), 'type' => 'tel' ],
        'bio'   => [ 'label' => __( 'Bio', 'theme' ), 'type' => 'wysiwyg', 'rows' => 8 ],
        'photo' => [ 'label' => __( 'Photo', 'theme' ), 'type' => 'image' ],
        'social' => [
            'label'  => __( 'Social Links', 'theme' ),
            'type'   => 'group',
            'fields' => [
                'linkedin' => [ 'label' => 'LinkedIn', 'type' => 'url' ],
                'twitter'  => [ 'label' => 'Twitter', 'type' => 'url' ],
                'github'   => [ 'label' => 'GitHub', 'type' => 'url' ],
            ],
        ],
        'order'    => [ 'label' => __( 'Order', 'theme' ), 'type' => 'number', 'min' => 0, 'default' => 0 ],
        'featured' => [ 'label' => __( 'Featured', 'theme' ), 'type' => 'checkbox' ],
    ],
] );
```

---

## Testimonial

Customer testimonial with rating.

```php
register_post_fields( 'testimonial', [
    'title'      => __( 'Testimonial', 'theme' ),
    'post_types' => 'testimonial',
    'fields'     => [
        'author_name'  => [ 'label' => __( 'Author', 'theme' ), 'type' => 'text' ],
        'author_title' => [ 'label' => __( 'Title', 'theme' ), 'type' => 'text' ],
        'company'      => [ 'label' => __( 'Company', 'theme' ), 'type' => 'text' ],
        'author_photo' => [ 'label' => __( 'Photo', 'theme' ), 'type' => 'image' ],
        'company_logo' => [ 'label' => __( 'Logo', 'theme' ), 'type' => 'image' ],
        'rating' => [
            'label'   => __( 'Rating', 'theme' ),
            'type'    => 'button_group',
            'default' => '5',
            'options' => [ '1' => '★', '2' => '★★', '3' => '★★★', '4' => '★★★★', '5' => '★★★★★' ],
        ],
        'video_url'    => [ 'label' => __( 'Video', 'theme' ), 'type' => 'oembed' ],
        'website_url'  => [ 'label' => __( 'Website', 'theme' ), 'type' => 'url' ],
        'date'         => [ 'label' => __( 'Date', 'theme' ), 'type' => 'date' ],
        'featured'     => [ 'label' => __( 'Featured', 'theme' ), 'type' => 'checkbox' ],
    ],
] );
```

---

## Portfolio Project

Project showcase with client info and results.

```php
register_post_fields( 'portfolio', [
    'title'      => __( 'Project Details', 'theme' ),
    'post_types' => 'portfolio',
    'fields'     => [
        'client_name'     => [ 'label' => __( 'Client', 'theme' ), 'type' => 'text' ],
        'client_logo'     => [ 'label' => __( 'Logo', 'theme' ), 'type' => 'image' ],
        'project_url'     => [ 'label' => __( 'URL', 'theme' ), 'type' => 'url' ],
        'completion_date' => [ 'label' => __( 'Completed', 'theme' ), 'type' => 'date' ],
        'project_type' => [
            'label'   => __( 'Type', 'theme' ),
            'type'    => 'select',
            'options' => [
                ''         => __( '— Select —', 'theme' ),
                'website'  => __( 'Website', 'theme' ),
                'app'      => __( 'App', 'theme' ),
                'branding' => __( 'Branding', 'theme' ),
            ],
        ],
        'technologies' => [
            'label'    => __( 'Technologies', 'theme' ),
            'type'     => 'select',
            'multiple' => true,
            'options'  => [
                'wordpress' => 'WordPress',
                'react'     => 'React',
                'laravel'   => 'Laravel',
                'figma'     => 'Figma',
            ],
        ],
        'gallery'   => [ 'label' => __( 'Gallery', 'theme' ), 'type' => 'gallery', 'max_items' => 20 ],
        'challenge' => [ 'label' => __( 'Challenge', 'theme' ), 'type' => 'textarea', 'rows' => 4 ],
        'solution'  => [ 'label' => __( 'Solution', 'theme' ), 'type' => 'textarea', 'rows' => 4 ],
        'results' => [
            'label'        => __( 'Results', 'theme' ),
            'type'         => 'repeater',
            'button_label' => __( 'Add Result', 'theme' ),
            'layout'       => 'table',
            'fields'       => [
                'metric'      => [ 'label' => __( 'Metric', 'theme' ), 'type' => 'text' ],
                'description' => [ 'label' => __( 'Description', 'theme' ), 'type' => 'text' ],
            ],
        ],
        'testimonial' => [ 'label' => __( 'Testimonial', 'theme' ), 'type' => 'textarea', 'rows' => 3 ],
        'featured'    => [ 'label' => __( 'Featured', 'theme' ), 'type' => 'checkbox' ],
    ],
] );
```

---

## Podcast Episode

Podcast with audio, guests, and timestamps.

```php
register_post_fields( 'podcast_episode', [
    'title'      => __( 'Episode Details', 'theme' ),
    'post_types' => 'podcast',
    'fields'     => [
        'episode_number' => [ 'label' => __( 'Episode #', 'theme' ), 'type' => 'number', 'min' => 1 ],
        'season_number'  => [ 'label' => __( 'Season #', 'theme' ), 'type' => 'number', 'min' => 1 ],
        'duration'       => [ 'label' => __( 'Duration', 'theme' ), 'type' => 'text', 'placeholder' => '45:30' ],
        'audio_file'     => [ 'label' => __( 'Audio File', 'theme' ), 'type' => 'file' ],
        'audio_url'      => [ 'label' => __( 'Audio URL', 'theme' ), 'type' => 'url' ],
        'transcript'     => [ 'label' => __( 'Transcript', 'theme' ), 'type' => 'wysiwyg', 'rows' => 15 ],
        'show_notes'     => [ 'label' => __( 'Show Notes', 'theme' ), 'type' => 'wysiwyg', 'rows' => 10 ],
        'guests' => [
            'label'        => __( 'Guests', 'theme' ),
            'type'         => 'repeater',
            'button_label' => __( 'Add Guest', 'theme' ),
            'fields'       => [
                'photo'   => [ 'label' => __( 'Photo', 'theme' ), 'type' => 'image' ],
                'name'    => [ 'label' => __( 'Name', 'theme' ), 'type' => 'text' ],
                'title'   => [ 'label' => __( 'Title', 'theme' ), 'type' => 'text' ],
                'website' => [ 'label' => __( 'Website', 'theme' ), 'type' => 'url' ],
            ],
        ],
        'timestamps' => [
            'label'        => __( 'Timestamps', 'theme' ),
            'type'         => 'repeater',
            'button_label' => __( 'Add Timestamp', 'theme' ),
            'layout'       => 'table',
            'fields'       => [
                'time'  => [ 'label' => __( 'Time', 'theme' ), 'type' => 'text', 'placeholder' => '00:00' ],
                'topic' => [ 'label' => __( 'Topic', 'theme' ), 'type' => 'text' ],
            ],
        ],
        'explicit'    => [ 'label' => __( 'Explicit', 'theme' ), 'type' => 'checkbox' ],
        'spotify_url' => [ 'label' => 'Spotify', 'type' => 'url' ],
        'apple_url'   => [ 'label' => 'Apple Podcasts', 'type' => 'url' ],
    ],
] );
```

---

## Recipe

Recipe with ingredients and instructions.

```php
register_post_fields( 'recipe', [
    'title'      => __( 'Recipe Details', 'theme' ),
    'post_types' => 'recipe',
    'fields'     => [
        'prep_time' => [ 'label' => __( 'Prep (min)', 'theme' ), 'type' => 'number', 'min' => 0 ],
        'cook_time' => [ 'label' => __( 'Cook (min)', 'theme' ), 'type' => 'number', 'min' => 0 ],
        'servings'  => [ 'label' => __( 'Servings', 'theme' ), 'type' => 'number', 'min' => 1 ],
        'difficulty' => [
            'label'   => __( 'Difficulty', 'theme' ),
            'type'    => 'button_group',
            'default' => 'medium',
            'options' => [ 'easy' => __( 'Easy', 'theme' ), 'medium' => __( 'Medium', 'theme' ), 'hard' => __( 'Hard', 'theme' ) ],
        ],
        'dietary' => [
            'label'    => __( 'Dietary', 'theme' ),
            'type'     => 'select',
            'multiple' => true,
            'display'  => 'checkbox',
            'options'  => [
                'vegetarian'  => __( 'Vegetarian', 'theme' ),
                'vegan'       => __( 'Vegan', 'theme' ),
                'gluten_free' => __( 'Gluten-Free', 'theme' ),
                'keto'        => __( 'Keto', 'theme' ),
            ],
        ],
        'ingredients' => [
            'label'        => __( 'Ingredients', 'theme' ),
            'type'         => 'repeater',
            'button_label' => __( 'Add Ingredient', 'theme' ),
            'fields'       => [
                'amount'     => [ 'label' => __( 'Amount', 'theme' ), 'type' => 'text', 'placeholder' => '1 cup' ],
                'ingredient' => [ 'label' => __( 'Ingredient', 'theme' ), 'type' => 'text' ],
                'notes'      => [ 'label' => __( 'Notes', 'theme' ), 'type' => 'text', 'placeholder' => 'diced, optional' ],
            ],
        ],
        'instructions' => [
            'label'        => __( 'Instructions', 'theme' ),
            'type'         => 'repeater',
            'button_label' => __( 'Add Step', 'theme' ),
            'row_title'    => __( 'Step {index}', 'theme' ),
            'fields'       => [
                'instruction' => [ 'label' => __( 'Instruction', 'theme' ), 'type' => 'textarea', 'rows' => 2 ],
                'image'       => [ 'label' => __( 'Image', 'theme' ), 'type' => 'image' ],
            ],
        ],
        'nutrition' => [
            'label'  => __( 'Nutrition (per serving)', 'theme' ),
            'type'   => 'group',
            'fields' => [
                'calories' => [ 'label' => __( 'Calories', 'theme' ), 'type' => 'number', 'min' => 0 ],
                'protein'  => [ 'label' => __( 'Protein (g)', 'theme' ), 'type' => 'number', 'min' => 0 ],
                'carbs'    => [ 'label' => __( 'Carbs (g)', 'theme' ), 'type' => 'number', 'min' => 0 ],
                'fat'      => [ 'label' => __( 'Fat (g)', 'theme' ), 'type' => 'number', 'min' => 0 ],
            ],
        ],
        'video'   => [ 'label' => __( 'Video', 'theme' ), 'type' => 'oembed' ],
        'gallery' => [ 'label' => __( 'Photos', 'theme' ), 'type' => 'gallery', 'max_items' => 10 ],
    ],
] );
```

---

## Course

Online course with curriculum and pricing.

```php
register_post_fields( 'course', [
    'title'      => __( 'Course Details', 'theme' ),
    'post_types' => 'course',
    'fields'     => [
        'price'      => [ 'label' => __( 'Price', 'theme' ), 'type' => 'number', 'min' => 0, 'step' => 0.01 ],
        'sale_price' => [ 'label' => __( 'Sale Price', 'theme' ), 'type' => 'number', 'min' => 0, 'step' => 0.01 ],
        'duration'   => [ 'label' => __( 'Duration', 'theme' ), 'type' => 'text', 'placeholder' => '10 hours' ],
        'level' => [
            'label'   => __( 'Level', 'theme' ),
            'type'    => 'select',
            'options' => [
                'beginner'     => __( 'Beginner', 'theme' ),
                'intermediate' => __( 'Intermediate', 'theme' ),
                'advanced'     => __( 'Advanced', 'theme' ),
            ],
        ],
        'format' => [
            'label'   => __( 'Format', 'theme' ),
            'type'    => 'button_group',
            'default' => 'self_paced',
            'options' => [
                'self_paced' => __( 'Self-paced', 'theme' ),
                'live'       => __( 'Live', 'theme' ),
                'cohort'     => __( 'Cohort', 'theme' ),
            ],
        ],
        'start_date' => [
            'label'     => __( 'Start Date', 'theme' ),
            'type'      => 'date',
            'show_when' => [ 'field' => 'format', 'operator' => 'in', 'value' => [ 'live', 'cohort' ] ],
        ],
        'instructor'  => [ 'label' => __( 'Instructor', 'theme' ), 'type' => 'user_ajax', 'role' => 'instructor' ],
        'intro_video' => [ 'label' => __( 'Intro Video', 'theme' ), 'type' => 'oembed' ],
        'curriculum' => [
            'label'           => __( 'Curriculum', 'theme' ),
            'type'            => 'repeater',
            'button_label'    => __( 'Add Module', 'theme' ),
            'row_title'       => __( 'Module {index}: {value}', 'theme' ),
            'row_title_field' => 'title',
            'fields'          => [
                'title'       => [ 'label' => __( 'Title', 'theme' ), 'type' => 'text' ],
                'description' => [ 'label' => __( 'Description', 'theme' ), 'type' => 'textarea', 'rows' => 2 ],
                'duration'    => [ 'label' => __( 'Duration', 'theme' ), 'type' => 'text', 'placeholder' => '45 min' ],
            ],
        ],
        'what_youll_learn' => [
            'label'        => __( "What You'll Learn", 'theme' ),
            'type'         => 'repeater',
            'button_label' => __( 'Add', 'theme' ),
            'layout'       => 'table',
            'fields'       => [
                'item' => [ 'label' => __( 'Item', 'theme' ), 'type' => 'text' ],
            ],
        ],
        'includes' => [
            'label'    => __( 'Includes', 'theme' ),
            'type'     => 'select',
            'multiple' => true,
            'display'  => 'checkbox',
            'options'  => [
                'video'       => __( 'Video', 'theme' ),
                'downloads'   => __( 'Downloads', 'theme' ),
                'certificate' => __( 'Certificate', 'theme' ),
                'lifetime'    => __( 'Lifetime Access', 'theme' ),
            ],
        ],
        'featured' => [ 'label' => __( 'Featured', 'theme' ), 'type' => 'checkbox' ],
    ],
] );
```

---

## FAQ

FAQ item.

```php
register_post_fields( 'faq', [
    'title'      => __( 'FAQ Details', 'theme' ),
    'post_types' => 'faq',
    'context'    => 'side',
    'fields'     => [
        'order'        => [ 'label' => __( 'Order', 'theme' ), 'type' => 'number', 'min' => 0, 'default' => 0 ],
        'icon'         => [ 'label' => __( 'Icon', 'theme' ), 'type' => 'text', 'placeholder' => 'dashicons-editor-help' ],
        'related_page' => [ 'label' => __( 'Related Page', 'theme' ), 'type' => 'post_ajax', 'post_type' => 'page' ],
    ],
] );
```

---

## Location/Branch

Business location with hours.

```php
register_post_fields( 'location', [
    'title'      => __( 'Location Details', 'theme' ),
    'post_types' => 'location',
    'fields'     => [
        'address' => [
            'label'  => __( 'Address', 'theme' ),
            'type'   => 'group',
            'fields' => [
                'street'  => [ 'label' => __( 'Street', 'theme' ), 'type' => 'text' ],
                'city'    => [ 'label' => __( 'City', 'theme' ), 'type' => 'text' ],
                'state'   => [ 'label' => __( 'State', 'theme' ), 'type' => 'text' ],
                'zip'     => [ 'label' => __( 'ZIP', 'theme' ), 'type' => 'text' ],
                'country' => [ 'label' => __( 'Country', 'theme' ), 'type' => 'text' ],
            ],
        ],
        'coordinates' => [
            'label'  => __( 'Coordinates', 'theme' ),
            'type'   => 'group',
            'fields' => [
                'lat' => [ 'label' => __( 'Latitude', 'theme' ), 'type' => 'text' ],
                'lng' => [ 'label' => __( 'Longitude', 'theme' ), 'type' => 'text' ],
            ],
        ],
        'phone' => [ 'label' => __( 'Phone', 'theme' ), 'type' => 'tel' ],
        'email' => [ 'label' => __( 'Email', 'theme' ), 'type' => 'email' ],
        'hours' => [
            'label'        => __( 'Hours', 'theme' ),
            'type'         => 'repeater',
            'button_label' => __( 'Add', 'theme' ),
            'layout'       => 'table',
            'max_items'    => 7,
            'fields'       => [
                'day' => [
                    'label'   => __( 'Day', 'theme' ),
                    'type'    => 'select',
                    'options' => [
                        'monday' => 'Mon', 'tuesday' => 'Tue', 'wednesday' => 'Wed',
                        'thursday' => 'Thu', 'friday' => 'Fri', 'saturday' => 'Sat', 'sunday' => 'Sun',
                    ],
                ],
                'open'   => [ 'label' => __( 'Open', 'theme' ), 'type' => 'text' ],
                'close'  => [ 'label' => __( 'Close', 'theme' ), 'type' => 'text' ],
                'closed' => [ 'label' => __( 'Closed', 'theme' ), 'type' => 'checkbox' ],
            ],
        ],
        'photo'           => [ 'label' => __( 'Photo', 'theme' ), 'type' => 'image' ],
        'manager'         => [ 'label' => __( 'Manager', 'theme' ), 'type' => 'user_ajax' ],
        'is_headquarters' => [ 'label' => __( 'Headquarters', 'theme' ), 'type' => 'checkbox' ],
    ],
] );
```

---

## Service

Service with pricing and process.

```php
register_post_fields( 'service', [
    'title'      => __( 'Service Details', 'theme' ),
    'post_types' => 'service',
    'fields'     => [
        'tagline' => [ 'label' => __( 'Tagline', 'theme' ), 'type' => 'text' ],
        'pricing_type' => [
            'label'   => __( 'Pricing', 'theme' ),
            'type'    => 'select',
            'options' => [
                'fixed'  => __( 'Fixed', 'theme' ),
                'hourly' => __( 'Hourly', 'theme' ),
                'quote'  => __( 'Quote', 'theme' ),
                'free'   => __( 'Free', 'theme' ),
            ],
        ],
        'price' => [
            'label'     => __( 'Price', 'theme' ),
            'type'      => 'number',
            'min'       => 0,
            'step'      => 0.01,
            'show_when' => [ 'field' => 'pricing_type', 'operator' => 'in', 'value' => [ 'fixed', 'hourly' ] ],
        ],
        'duration' => [ 'label' => __( 'Duration', 'theme' ), 'type' => 'text' ],
        'features' => [
            'label'        => __( 'Features', 'theme' ),
            'type'         => 'repeater',
            'button_label' => __( 'Add', 'theme' ),
            'layout'       => 'table',
            'fields'       => [
                'feature' => [ 'label' => __( 'Feature', 'theme' ), 'type' => 'text' ],
            ],
        ],
        'process' => [
            'label'        => __( 'Process', 'theme' ),
            'type'         => 'repeater',
            'button_label' => __( 'Add Step', 'theme' ),
            'row_title'    => __( 'Step {index}', 'theme' ),
            'fields'       => [
                'title'       => [ 'label' => __( 'Title', 'theme' ), 'type' => 'text' ],
                'description' => [ 'label' => __( 'Description', 'theme' ), 'type' => 'textarea', 'rows' => 2 ],
            ],
        ],
        'booking_url'       => [ 'label' => __( 'Booking URL', 'theme' ), 'type' => 'url' ],
        'related_services'  => [ 'label' => __( 'Related', 'theme' ), 'type' => 'post_ajax', 'post_type' => 'service', 'multiple' => true ],
        'icon'              => [ 'label' => __( 'Icon', 'theme' ), 'type' => 'image' ],
        'featured'          => [ 'label' => __( 'Featured', 'theme' ), 'type' => 'checkbox' ],
        'order'             => [ 'label' => __( 'Order', 'theme' ), 'type' => 'number', 'min' => 0, 'default' => 0 ],
    ],
] );
```

---

## Video

Video with embed and chapters.

```php
register_post_fields( 'video', [
    'title'      => __( 'Video Details', 'theme' ),
    'post_types' => 'video',
    'fields'     => [
        'video_url'  => [ 'label' => __( 'Video URL', 'theme' ), 'type' => 'oembed' ],
        'video_file' => [ 'label' => __( 'Video File', 'theme' ), 'type' => 'file' ],
        'duration'   => [ 'label' => __( 'Duration', 'theme' ), 'type' => 'text', 'placeholder' => '10:30' ],
        'thumbnail'  => [ 'label' => __( 'Thumbnail', 'theme' ), 'type' => 'image' ],
        'chapters' => [
            'label'        => __( 'Chapters', 'theme' ),
            'type'         => 'repeater',
            'button_label' => __( 'Add Chapter', 'theme' ),
            'layout'       => 'table',
            'fields'       => [
                'time'  => [ 'label' => __( 'Time', 'theme' ), 'type' => 'text', 'placeholder' => '00:00' ],
                'title' => [ 'label' => __( 'Title', 'theme' ), 'type' => 'text' ],
            ],
        ],
        'transcript' => [ 'label' => __( 'Transcript', 'theme' ), 'type' => 'wysiwyg', 'rows' => 10 ],
        'downloads' => [
            'label'        => __( 'Downloads', 'theme' ),
            'type'         => 'repeater',
            'button_label' => __( 'Add', 'theme' ),
            'fields'       => [
                'title' => [ 'label' => __( 'Title', 'theme' ), 'type' => 'text' ],
                'file'  => [ 'label' => __( 'File', 'theme' ), 'type' => 'file' ],
            ],
        ],
        'related_videos' => [ 'label' => __( 'Related', 'theme' ), 'type' => 'post_ajax', 'post_type' => 'video', 'multiple' => true ],
        'featured'       => [ 'label' => __( 'Featured', 'theme' ), 'type' => 'checkbox' ],
    ],
] );
```

---

## Download/Resource

Downloadable resource.

```php
register_post_fields( 'download', [
    'title'      => __( 'Download Details', 'theme' ),
    'post_types' => 'download',
    'fields'     => [
        'file'     => [ 'label' => __( 'File', 'theme' ), 'type' => 'file' ],
        'file_url' => [ 'label' => __( 'External URL', 'theme' ), 'type' => 'url' ],
        'version'  => [ 'label' => __( 'Version', 'theme' ), 'type' => 'text', 'placeholder' => '1.0.0' ],
        'file_size'=> [ 'label' => __( 'File Size', 'theme' ), 'type' => 'text', 'placeholder' => '2.5 MB' ],
        'file_type' => [
            'label'   => __( 'Type', 'theme' ),
            'type'    => 'select',
            'options' => [
                ''      => __( '— Select —', 'theme' ),
                'pdf'   => 'PDF',
                'doc'   => 'Word',
                'xls'   => 'Excel',
                'zip'   => 'ZIP',
                'image' => 'Image',
                'video' => 'Video',
            ],
        ],
        'requires_email' => [ 'label' => __( 'Require Email', 'theme' ), 'type' => 'checkbox' ],
        'download_count' => [ 'label' => __( 'Downloads', 'theme' ), 'type' => 'number', 'min' => 0, 'default' => 0 ],
        'changelog'      => [ 'label' => __( 'Changelog', 'theme' ), 'type' => 'wysiwyg', 'rows' => 8 ],
        'featured'       => [ 'label' => __( 'Featured', 'theme' ), 'type' => 'checkbox' ],
    ],
] );
```

---

## Pricing Plan

Pricing table plan.

```php
register_post_fields( 'pricing_plan', [
    'title'      => __( 'Plan Details', 'theme' ),
    'post_types' => 'pricing',
    'fields'     => [
        'price' => [ 'label' => __( 'Price', 'theme' ), 'type' => 'number', 'min' => 0, 'step' => 0.01 ],
        'billing_period' => [
            'label'   => __( 'Period', 'theme' ),
            'type'    => 'select',
            'default' => 'month',
            'options' => [
                'month'    => __( '/ month', 'theme' ),
                'year'     => __( '/ year', 'theme' ),
                'one_time' => __( 'one-time', 'theme' ),
            ],
        ],
        'description' => [ 'label' => __( 'Description', 'theme' ), 'type' => 'textarea', 'rows' => 2 ],
        'features' => [
            'label'        => __( 'Features', 'theme' ),
            'type'         => 'repeater',
            'button_label' => __( 'Add', 'theme' ),
            'layout'       => 'table',
            'fields'       => [
                'feature'  => [ 'label' => __( 'Feature', 'theme' ), 'type' => 'text' ],
                'included' => [ 'label' => __( 'Included', 'theme' ), 'type' => 'checkbox', 'default' => 1 ],
            ],
        ],
        'cta_text' => [ 'label' => __( 'Button', 'theme' ), 'type' => 'text', 'default' => 'Get Started' ],
        'cta_url'  => [ 'label' => __( 'Button URL', 'theme' ), 'type' => 'url' ],
        'highlighted' => [ 'label' => __( 'Highlight', 'theme' ), 'type' => 'checkbox' ],
        'badge' => [
            'label'     => __( 'Badge', 'theme' ),
            'type'      => 'text',
            'placeholder' => 'Most Popular',
            'show_when' => [ 'highlighted' => 1 ],
        ],
        'order' => [ 'label' => __( 'Order', 'theme' ), 'type' => 'number', 'min' => 0, 'default' => 0 ],
    ],
] );
```

---

## Case Study

Client case study.

```php
register_post_fields( 'case_study', [
    'title'      => __( 'Case Study', 'theme' ),
    'post_types' => 'case_study',
    'fields'     => [
        'client_name'    => [ 'label' => __( 'Client', 'theme' ), 'type' => 'text' ],
        'client_logo'    => [ 'label' => __( 'Logo', 'theme' ), 'type' => 'image' ],
        'client_website' => [ 'label' => __( 'Website', 'theme' ), 'type' => 'url' ],
        'industry'       => [ 'label' => __( 'Industry', 'theme' ), 'type' => 'text' ],
        'project_date'   => [ 'label' => __( 'Date', 'theme' ), 'type' => 'date' ],
        'services'       => [ 'label' => __( 'Services', 'theme' ), 'type' => 'post_ajax', 'post_type' => 'service', 'multiple' => true ],
        'challenge'      => [ 'label' => __( 'Challenge', 'theme' ), 'type' => 'wysiwyg', 'rows' => 6 ],
        'solution'       => [ 'label' => __( 'Solution', 'theme' ), 'type' => 'wysiwyg', 'rows' => 6 ],
        'results' => [
            'label'        => __( 'Results', 'theme' ),
            'type'         => 'repeater',
            'button_label' => __( 'Add Result', 'theme' ),
            'fields'       => [
                'metric' => [ 'label' => __( 'Metric', 'theme' ), 'type' => 'text', 'placeholder' => '250%' ],
                'label'  => [ 'label' => __( 'Label', 'theme' ), 'type' => 'text' ],
            ],
        ],
        'testimonial'        => [ 'label' => __( 'Testimonial', 'theme' ), 'type' => 'textarea', 'rows' => 3 ],
        'testimonial_author' => [ 'label' => __( 'Author', 'theme' ), 'type' => 'text' ],
        'testimonial_title'  => [ 'label' => __( 'Author Title', 'theme' ), 'type' => 'text' ],
        'gallery'            => [ 'label' => __( 'Gallery', 'theme' ), 'type' => 'gallery', 'max_items' => 15 ],
        'featured'           => [ 'label' => __( 'Featured', 'theme' ), 'type' => 'checkbox' ],
    ],
] );
```

---

## Press Release

Press release with media assets.

```php
register_post_fields( 'press_release', [
    'title'      => __( 'Press Release', 'theme' ),
    'post_types' => 'press',
    'fields'     => [
        'release_date' => [ 'label' => __( 'Release Date', 'theme' ), 'type' => 'date' ],
        'location'     => [ 'label' => __( 'Location', 'theme' ), 'type' => 'text', 'placeholder' => 'New York, NY' ],
        'subtitle'     => [ 'label' => __( 'Subtitle', 'theme' ), 'type' => 'text' ],
        'boilerplate'  => [ 'label' => __( 'About', 'theme' ), 'type' => 'wysiwyg', 'rows' => 5 ],
        'media_contact' => [
            'label'  => __( 'Media Contact', 'theme' ),
            'type'   => 'group',
            'fields' => [
                'name'  => [ 'label' => __( 'Name', 'theme' ), 'type' => 'text' ],
                'email' => [ 'label' => __( 'Email', 'theme' ), 'type' => 'email' ],
                'phone' => [ 'label' => __( 'Phone', 'theme' ), 'type' => 'tel' ],
            ],
        ],
        'media_assets' => [
            'label'        => __( 'Media Assets', 'theme' ),
            'type'         => 'repeater',
            'button_label' => __( 'Add Asset', 'theme' ),
            'fields'       => [
                'title' => [ 'label' => __( 'Title', 'theme' ), 'type' => 'text' ],
                'file'  => [ 'label' => __( 'File', 'theme' ), 'type' => 'file' ],
                'type' => [
                    'label'   => __( 'Type', 'theme' ),
                    'type'    => 'select',
                    'options' => [ 'image' => 'Image', 'logo' => 'Logo', 'video' => 'Video', 'pdf' => 'PDF' ],
                ],
            ],
        ],
        'pdf_version' => [ 'label' => __( 'PDF Version', 'theme' ), 'type' => 'file' ],
    ],
] );
```

---

## Book

Book with purchase links and reviews.

```php
register_post_fields( 'book', [
    'title'      => __( 'Book Details', 'theme' ),
    'post_types' => 'book',
    'fields'     => [
        'subtitle'     => [ 'label' => __( 'Subtitle', 'theme' ), 'type' => 'text' ],
        'author'       => [ 'label' => __( 'Author', 'theme' ), 'type' => 'text' ],
        'isbn'         => [ 'label' => __( 'ISBN', 'theme' ), 'type' => 'text', 'placeholder' => '978-0-123456-78-9' ],
        'publisher'    => [ 'label' => __( 'Publisher', 'theme' ), 'type' => 'text' ],
        'publish_date' => [ 'label' => __( 'Published', 'theme' ), 'type' => 'date' ],
        'pages'        => [ 'label' => __( 'Pages', 'theme' ), 'type' => 'number', 'min' => 0 ],
        'formats' => [
            'label'    => __( 'Formats', 'theme' ),
            'type'     => 'select',
            'multiple' => true,
            'display'  => 'checkbox',
            'options'  => [
                'hardcover' => __( 'Hardcover', 'theme' ),
                'paperback' => __( 'Paperback', 'theme' ),
                'ebook'     => __( 'eBook', 'theme' ),
                'audiobook' => __( 'Audiobook', 'theme' ),
            ],
        ],
        'price' => [ 'label' => __( 'Price', 'theme' ), 'type' => 'number', 'min' => 0, 'step' => 0.01 ],
        'purchase_links' => [
            'label'        => __( 'Purchase Links', 'theme' ),
            'type'         => 'repeater',
            'button_label' => __( 'Add Link', 'theme' ),
            'layout'       => 'table',
            'fields'       => [
                'store' => [
                    'label'   => __( 'Store', 'theme' ),
                    'type'    => 'select',
                    'options' => [
                        'amazon'   => 'Amazon',
                        'bn'       => 'B&N',
                        'bookshop' => 'Bookshop',
                        'apple'    => 'Apple Books',
                        'audible'  => 'Audible',
                    ],
                ],
                'url' => [ 'label' => __( 'URL', 'theme' ), 'type' => 'url' ],
            ],
        ],
        'cover_image' => [ 'label' => __( 'Cover', 'theme' ), 'type' => 'image' ],
        'excerpt'     => [ 'label' => __( 'Excerpt', 'theme' ), 'type' => 'wysiwyg', 'rows' => 10 ],
        'reviews' => [
            'label'        => __( 'Reviews', 'theme' ),
            'type'         => 'repeater',
            'button_label' => __( 'Add Review', 'theme' ),
            'fields'       => [
                'quote'  => [ 'label' => __( 'Quote', 'theme' ), 'type' => 'textarea', 'rows' => 2 ],
                'source' => [ 'label' => __( 'Source', 'theme' ), 'type' => 'text' ],
            ],
        ],
        'related_books' => [ 'label' => __( 'Related', 'theme' ), 'type' => 'post_ajax', 'post_type' => 'book', 'multiple' => true ],
        'featured'      => [ 'label' => __( 'Featured', 'theme' ), 'type' => 'checkbox' ],
    ],
] );
```

---

## Quick Reference

### Sidebar Metabox

```php
register_post_fields( 'settings', [
    'title'      => __( 'Settings', 'theme' ),
    'post_types' => 'post',
    'context'    => 'side',
    'priority'   => 'high',
    'fields'     => [ /* ... */ ],
] );
```

### Multiple Post Types

```php
register_post_fields( 'shared', [
    'post_types' => [ 'post', 'page', 'product' ],
    'fields'     => [ /* ... */ ],
] );
```

### Meta Key Prefix

```php
register_post_fields( 'prefixed', [
    'prefix' => '_my_plugin_',  // Fields saved as _my_plugin_fieldname
    'fields' => [ /* ... */ ],
] );
```

### Admin-Only Fields

```php
register_post_fields( 'admin_only', [
    'capability' => 'manage_options',
    'fields'     => [ /* ... */ ],
] );
```

---

## Tips

1. **Start Simple** - Add complexity as needed
2. **Use Conditional Logic** - Hide irrelevant fields
3. **Group Related Fields** - Use `group` for related data, `repeater` for lists
4. **Choose Right Types**:
    - `post_ajax` for large datasets
    - `button_group` for 2-4 options
    - `toggle` for prominent booleans
5. **Set Defaults** - Reduce required input
6. **Add Descriptions** - Help editors understand fields
7. **Use Placeholders** - Show expected format

---

See [README.md](README.md) for complete field documentation.