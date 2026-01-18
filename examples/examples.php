<?php
/**
 * Post Metabox Examples
 *
 * Practical examples of using the WP Register Post Metabox library.
 * No need to wrap in admin_init - the library handles hook timing automatically.
 *
 * @package ArrayPress\WP\RegisterPostMetabox
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Example 1: Basic product information metabox
 *
 * Simple text and number fields for product data.
 */
register_post_metabox( 'product_info', [
	'title'      => __( 'Product Information', 'textdomain' ),
	'post_types' => 'product',
	'fields'     => [
		'sku'   => [
			'label'       => __( 'SKU', 'textdomain' ),
			'type'        => 'text',
			'placeholder' => 'PRD-0000',
			'description' => __( 'Unique product identifier.', 'textdomain' ),
		],
		'price' => [
			'label'       => __( 'Price', 'textdomain' ),
			'type'        => 'number',
			'min'         => 0,
			'step'        => 0.01,
			'placeholder' => '0.00',
		],
		'stock' => [
			'label'   => __( 'Stock Quantity', 'textdomain' ),
			'type'    => 'number',
			'min'     => 0,
			'default' => 0,
		],
	],
] );

/**
 * Example 2: Multiple post types
 *
 * Register the same metabox across multiple post types.
 */
register_post_metabox( 'seo_settings', [
	'title'      => __( 'SEO Settings', 'textdomain' ),
	'post_types' => [ 'post', 'page', 'product' ],
	'fields'     => [
		'meta_title'       => [
			'label'       => __( 'Meta Title', 'textdomain' ),
			'type'        => 'text',
			'placeholder' => __( 'Custom title for search engines', 'textdomain' ),
		],
		'meta_description' => [
			'label'       => __( 'Meta Description', 'textdomain' ),
			'type'        => 'textarea',
			'rows'        => 3,
			'placeholder' => __( 'Brief description for search results...', 'textdomain' ),
		],
		'noindex'          => [
			'label'       => __( 'Hide from Search Engines', 'textdomain' ),
			'type'        => 'checkbox',
			'description' => __( 'Prevent this page from being indexed.', 'textdomain' ),
		],
	],
] );

/**
 * Example 3: Select fields with static and dynamic options
 */
register_post_metabox( 'post_settings', [
	'title'      => __( 'Post Settings', 'textdomain' ),
	'post_types' => 'post',
	'context'    => 'side',
	'priority'   => 'high',
	'fields'     => [
		'layout'       => [
			'label'   => __( 'Layout', 'textdomain' ),
			'type'    => 'select',
			'default' => 'default',
			'options' => [
				'default'    => __( 'Default', 'textdomain' ),
				'full-width' => __( 'Full Width', 'textdomain' ),
				'sidebar'    => __( 'With Sidebar', 'textdomain' ),
			],
		],
		'reading_time' => [
			'label'       => __( 'Reading Time (minutes)', 'textdomain' ),
			'type'        => 'number',
			'min'         => 1,
			'max'         => 120,
			'default'     => 5,
			'description' => __( 'Estimated reading time.', 'textdomain' ),
		],
		'featured'     => [
			'label'       => __( 'Featured Post', 'textdomain' ),
			'type'        => 'checkbox',
			'description' => __( 'Show in featured section.', 'textdomain' ),
		],
	],
] );

/**
 * Example 4: Image and file fields
 */
register_post_metabox( 'media_attachments', [
	'title'      => __( 'Media Attachments', 'textdomain' ),
	'post_types' => 'product',
	'fields'     => [
		'product_image' => [
			'label'       => __( 'Product Image', 'textdomain' ),
			'type'        => 'image',
			'button_text' => __( 'Select Product Image', 'textdomain' ),
		],
		'product_pdf'   => [
			'label'       => __( 'Product Specification PDF', 'textdomain' ),
			'type'        => 'file',
			'description' => __( 'Upload a PDF specification sheet.', 'textdomain' ),
		],
	],
] );

/**
 * Example 5: Gallery field
 */
register_post_metabox( 'product_gallery', [
	'title'      => __( 'Product Gallery', 'textdomain' ),
	'post_types' => 'product',
	'fields'     => [
		'gallery' => [
			'label'       => __( 'Gallery Images', 'textdomain' ),
			'type'        => 'gallery',
			'max_items'   => 10,
			'button_text' => __( 'Add Gallery Images', 'textdomain' ),
			'description' => __( 'Add up to 10 product images. Drag to reorder.', 'textdomain' ),
		],
	],
] );

/**
 * Example 6: Color and date fields
 */
register_post_metabox( 'event_details', [
	'title'      => __( 'Event Details', 'textdomain' ),
	'post_types' => 'event',
	'fields'     => [
		'event_date'       => [
			'label' => __( 'Event Date', 'textdomain' ),
			'type'  => 'date',
		],
		'event_time'       => [
			'label' => __( 'Event Time', 'textdomain' ),
			'type'  => 'time',
		],
		'brand_color'      => [
			'label'   => __( 'Brand Color', 'textdomain' ),
			'type'    => 'color',
			'default' => '#0073aa',
		],
		'registration_end' => [
			'label'       => __( 'Registration Deadline', 'textdomain' ),
			'type'        => 'datetime',
			'description' => __( 'When registration closes.', 'textdomain' ),
		],
	],
] );

/**
 * Example 7: WYSIWYG editor
 */
register_post_metabox( 'product_details', [
	'title'      => __( 'Product Details', 'textdomain' ),
	'post_types' => 'product',
	'fields'     => [
		'specifications' => [
			'label'       => __( 'Specifications', 'textdomain' ),
			'type'        => 'wysiwyg',
			'rows'        => 10,
			'description' => __( 'Detailed product specifications.', 'textdomain' ),
		],
	],
] );

/**
 * Example 8: Relational fields - Post, User, Term selectors
 */
register_post_metabox( 'related_content', [
	'title'      => __( 'Related Content', 'textdomain' ),
	'post_types' => 'post',
	'fields'     => [
		'related_posts'  => [
			'label'     => __( 'Related Posts', 'textdomain' ),
			'type'      => 'post',
			'post_type' => 'post',
			'multiple'  => true,
			'display'   => 'checkbox',
		],
		'content_author' => [
			'label' => __( 'Content Author', 'textdomain' ),
			'type'  => 'user',
			'role'  => [ 'author', 'editor', 'administrator' ],
		],
		'categories'     => [
			'label'    => __( 'Related Categories', 'textdomain' ),
			'type'     => 'term',
			'taxonomy' => 'category',
			'multiple' => true,
			'display'  => 'checkbox',
		],
	],
] );

/**
 * Example 9: Amount type field (combined value + type)
 */
register_post_metabox( 'pricing_options', [
	'title'      => __( 'Pricing Options', 'textdomain' ),
	'post_types' => 'product',
	'fields'     => [
		'sale_amount' => [
			'label'         => __( 'Sale Discount', 'textdomain' ),
			'type'          => 'amount_type',
			'description'   => __( 'Enter a discount amount or percentage.', 'textdomain' ),
			'type_meta_key' => 'sale_type',
			'type_options'  => [
				'percent' => '%',
				'flat'    => '$',
			],
			'type_default'  => 'percent',
			'min'           => 0,
			'max'           => 100,
		],
	],
] );

/**
 * Example 10: Group field (static group of related fields)
 */
register_post_metabox( 'dimensions', [
	'title'      => __( 'Product Dimensions', 'textdomain' ),
	'post_types' => 'product',
	'context'    => 'side',
	'fields'     => [
		'dimensions' => [
			'label'  => __( 'Dimensions', 'textdomain' ),
			'type'   => 'group',
			'fields' => [
				'width'  => [
					'label'       => __( 'Width (cm)', 'textdomain' ),
					'type'        => 'number',
					'min'         => 0,
					'step'        => 0.1,
					'placeholder' => '0.0',
				],
				'height' => [
					'label'       => __( 'Height (cm)', 'textdomain' ),
					'type'        => 'number',
					'min'         => 0,
					'step'        => 0.1,
					'placeholder' => '0.0',
				],
				'depth'  => [
					'label'       => __( 'Depth (cm)', 'textdomain' ),
					'type'        => 'number',
					'min'         => 0,
					'step'        => 0.1,
					'placeholder' => '0.0',
				],
			],
		],
	],
] );

/**
 * Example 11: Repeater field
 *
 * A dynamic list of feature entries with drag-and-drop reordering.
 */
register_post_metabox( 'product_features', [
	'title'      => __( 'Product Features', 'textdomain' ),
	'post_types' => 'product',
	'fields'     => [
		'features' => [
			'label'        => __( 'Features', 'textdomain' ),
			'type'         => 'repeater',
			'button_label' => __( 'Add Feature', 'textdomain' ),
			'max_items'    => 10,
			'collapsed'    => true,
			'fields'       => [
				'icon'        => [
					'label'       => __( 'Icon Class', 'textdomain' ),
					'type'        => 'text',
					'placeholder' => 'dashicons-star-filled',
				],
				'title'       => [
					'label' => __( 'Feature Title', 'textdomain' ),
					'type'  => 'text',
				],
				'description' => [
					'label' => __( 'Description', 'textdomain' ),
					'type'  => 'textarea',
					'rows'  => 2,
				],
			],
		],
	],
] );

/**
 * Example 12: Repeater with image fields
 */
register_post_metabox( 'team_members', [
	'title'      => __( 'Team Members', 'textdomain' ),
	'post_types' => 'page',
	'fields'     => [
		'team' => [
			'label'        => __( 'Team Members', 'textdomain' ),
			'type'         => 'repeater',
			'button_label' => __( 'Add Team Member', 'textdomain' ),
			'max_items'    => 20,
			'fields'       => [
				'photo'    => [
					'label' => __( 'Photo', 'textdomain' ),
					'type'  => 'image',
				],
				'name'     => [
					'label' => __( 'Name', 'textdomain' ),
					'type'  => 'text',
				],
				'title'    => [
					'label' => __( 'Job Title', 'textdomain' ),
					'type'  => 'text',
				],
				'email'    => [
					'label' => __( 'Email', 'textdomain' ),
					'type'  => 'email',
				],
				'linkedin' => [
					'label'       => __( 'LinkedIn URL', 'textdomain' ),
					'type'        => 'url',
					'placeholder' => 'https://linkedin.com/in/username',
				],
			],
		],
	],
] );

/**
 * Example 13: Custom sanitization
 */
register_post_metabox( 'custom_content', [
	'title'      => __( 'Custom Content', 'textdomain' ),
	'post_types' => 'page',
	'fields'     => [
		'allowed_html' => [
			'label'             => __( 'Content with Limited HTML', 'textdomain' ),
			'type'              => 'textarea',
			'rows'              => 6,
			'description'       => __( 'Allows basic HTML tags only.', 'textdomain' ),
			'sanitize_callback' => function ( $value ) {
				return wp_kses( $value, [
					'p'      => [],
					'br'     => [],
					'strong' => [],
					'em'     => [],
					'a'      => [ 'href' => [], 'title' => [], 'target' => [] ],
					'ul'     => [],
					'ol'     => [],
					'li'     => [],
				] );
			},
		],
	],
] );

/**
 * Example 14: Permission-restricted fields
 */
register_post_metabox( 'admin_settings', [
	'title'      => __( 'Admin Settings', 'textdomain' ),
	'post_types' => 'post',
	'capability' => 'manage_options',
	'fields'     => [
		'internal_notes' => [
			'label'       => __( 'Internal Notes', 'textdomain' ),
			'type'        => 'textarea',
			'rows'        => 3,
			'description' => __( 'Private notes visible only to administrators.', 'textdomain' ),
		],
		'override_slug'  => [
			'label'       => __( 'Override Slug', 'textdomain' ),
			'type'        => 'text',
			'description' => __( 'Custom slug override (admin only).', 'textdomain' ),
		],
	],
] );

/**
 * Example 15: Meta key prefixing
 */
register_post_metabox( 'prefixed_fields', [
	'title'      => __( 'Prefixed Fields', 'textdomain' ),
	'post_types' => 'product',
	'prefix'     => '_product_',
	'fields'     => [
		// These will be saved as _product_weight, _product_material, etc.
		'weight'   => [
			'label' => __( 'Weight (kg)', 'textdomain' ),
			'type'  => 'number',
			'min'   => 0,
			'step'  => 0.01,
		],
		'material' => [
			'label'   => __( 'Material', 'textdomain' ),
			'type'    => 'select',
			'options' => [
				''        => __( '— Select —', 'textdomain' ),
				'wood'    => __( 'Wood', 'textdomain' ),
				'metal'   => __( 'Metal', 'textdomain' ),
				'plastic' => __( 'Plastic', 'textdomain' ),
				'fabric'  => __( 'Fabric', 'textdomain' ),
			],
		],
	],
] );

/**
 * Example 16: Dynamic select options via callback
 */
register_post_metabox( 'dynamic_options', [
	'title'      => __( 'Dynamic Options', 'textdomain' ),
	'post_types' => 'post',
	'fields'     => [
		'parent_page' => [
			'label'   => __( 'Parent Page', 'textdomain' ),
			'type'    => 'select',
			'options' => function () {
				$pages   = get_pages( [ 'post_status' => 'publish' ] );
				$options = [ '' => __( '— None —', 'textdomain' ) ];

				foreach ( $pages as $page ) {
					$options[ $page->ID ] = $page->post_title;
				}

				return $options;
			},
		],
	],
] );

/**
 * Example: Retrieving field values in a template
 */
function example_display_product_info( $post_id ) {
	// Standard WordPress function
	$sku   = get_post_meta( $post_id, 'sku', true );
	$price = get_post_meta( $post_id, 'price', true );

	// Helper with default fallback
	$stock = get_post_field_value( $post_id, 'stock', 'product_info' );

	// Display
	echo '<div class="product-info">';
	echo '<p>SKU: ' . esc_html( $sku ) . '</p>';
	echo '<p>Price: $' . number_format( (float) $price, 2 ) . '</p>';
	echo '<p>Stock: ' . intval( $stock ) . '</p>';
	echo '</div>';

	// Repeater field
	$features = get_post_meta( $post_id, 'features', true );

	if ( ! empty( $features ) && is_array( $features ) ) {
		echo '<ul class="product-features">';
		foreach ( $features as $feature ) {
			echo '<li>';
			echo '<span class="' . esc_attr( $feature['icon'] ) . '"></span>';
			echo '<strong>' . esc_html( $feature['title'] ) . '</strong>';
			echo '<p>' . esc_html( $feature['description'] ) . '</p>';
			echo '</li>';
		}
		echo '</ul>';
	}

	// Gallery field
	$gallery = get_post_meta( $post_id, 'gallery', true );

	if ( ! empty( $gallery ) && is_array( $gallery ) ) {
		echo '<div class="product-gallery">';
		foreach ( $gallery as $attachment_id ) {
			echo wp_get_attachment_image( $attachment_id, 'medium' );
		}
		echo '</div>';
	}

	// Group field
	$dimensions = get_post_meta( $post_id, 'dimensions', true );

	if ( ! empty( $dimensions ) && is_array( $dimensions ) ) {
		echo '<p>Dimensions: ';
		echo esc_html( $dimensions['width'] ) . ' x ';
		echo esc_html( $dimensions['height'] ) . ' x ';
		echo esc_html( $dimensions['depth'] ) . ' cm';
		echo '</p>';
	}
}

/**
 * Example: Getting all registered fields
 */
function example_list_registered_fields() {
	$fields = get_metabox_fields( 'product_info' );

	echo '<ul>';
	foreach ( $fields as $meta_key => $config ) {
		printf(
			'<li><strong>%s</strong> (%s): %s</li>',
			esc_html( $config['label'] ),
			esc_html( $config['type'] ),
			esc_html( $meta_key )
		);
	}
	echo '</ul>';
}

/**
 * Example: Working with REST API
 *
 * All registered fields are automatically available via REST API.
 * GET /wp-json/wp/v2/product/123
 *
 * Returns:
 * {
 *   "id": 123,
 *   "title": { "rendered": "Product Name" },
 *   "meta": {
 *     "sku": "PRD-001",
 *     "price": 29.99,
 *     "stock": 100,
 *     "features": [
 *       { "icon": "dashicons-star", "title": "Feature 1", "description": "..." }
 *     ]
 *   }
 * }
 */