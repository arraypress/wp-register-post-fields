<?php
/**
 * Post Fields Examples
 *
 * Practical examples of using the WP Register Post Fields library.
 * No need to wrap in admin_init - the library handles hook timing automatically.
 *
 * @package ArrayPress\WP\RegisterPostFields
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
register_post_fields( 'product_info', [
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
register_post_fields( 'seo_settings', [
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
 * Example 3: Conditional fields with show_when (shorthand syntax)
 *
 * Show shipping fields only when product is physical.
 */
register_post_fields( 'product_type_settings', [
	'title'      => __( 'Product Type', 'textdomain' ),
	'post_types' => 'product',
	'fields'     => [
		'is_physical'    => [
			'label'       => __( 'Physical Product', 'textdomain' ),
			'type'        => 'checkbox',
			'description' => __( 'Check if this product requires shipping.', 'textdomain' ),
		],
		'weight'         => [
			'label'     => __( 'Weight (kg)', 'textdomain' ),
			'type'      => 'number',
			'min'       => 0,
			'step'      => 0.01,
			'show_when' => [ 'is_physical' => 1 ],
		],
		'shipping_class' => [
			'label'     => __( 'Shipping Class', 'textdomain' ),
			'type'      => 'select',
			'options'   => [
				''         => __( '— Select —', 'textdomain' ),
				'standard' => __( 'Standard', 'textdomain' ),
				'express'  => __( 'Express', 'textdomain' ),
				'freight'  => __( 'Freight', 'textdomain' ),
			],
			'show_when' => [ 'is_physical' => 1 ],
		],
	],
] );

/**
 * Example 4: Conditional fields with select trigger
 *
 * Show different fields based on product type selection.
 */
register_post_fields( 'product_options', [
	'title'      => __( 'Product Options', 'textdomain' ),
	'post_types' => 'product',
	'fields'     => [
		'product_type'   => [
			'label'   => __( 'Product Type', 'textdomain' ),
			'type'    => 'select',
			'options' => [
				''         => __( '— Select Type —', 'textdomain' ),
				'physical' => __( 'Physical Product', 'textdomain' ),
				'digital'  => __( 'Digital Download', 'textdomain' ),
				'service'  => __( 'Service', 'textdomain' ),
			],
		],
		// Physical product fields
		'dimensions'     => [
			'label'     => __( 'Dimensions', 'textdomain' ),
			'type'      => 'group',
			'show_when' => [ 'product_type' => 'physical' ],
			'fields'    => [
				'length' => [
					'label' => __( 'Length (cm)', 'textdomain' ),
					'type'  => 'number',
					'min'   => 0,
				],
				'width'  => [
					'label' => __( 'Width (cm)', 'textdomain' ),
					'type'  => 'number',
					'min'   => 0,
				],
				'height' => [
					'label' => __( 'Height (cm)', 'textdomain' ),
					'type'  => 'number',
					'min'   => 0,
				],
			],
		],
		// Digital product fields
		'download_file'  => [
			'label'     => __( 'Download File', 'textdomain' ),
			'type'      => 'file',
			'show_when' => [ 'product_type' => 'digital' ],
		],
		'download_limit' => [
			'label'       => __( 'Download Limit', 'textdomain' ),
			'type'        => 'number',
			'min'         => 0,
			'default'     => 0,
			'description' => __( '0 = unlimited downloads', 'textdomain' ),
			'show_when'   => [ 'product_type' => 'digital' ],
		],
		// Service fields
		'duration'       => [
			'label'     => __( 'Duration (hours)', 'textdomain' ),
			'type'      => 'number',
			'min'       => 0.5,
			'step'      => 0.5,
			'show_when' => [ 'product_type' => 'service' ],
		],
		'booking_url'    => [
			'label'       => __( 'Booking URL', 'textdomain' ),
			'type'        => 'url',
			'placeholder' => 'https://calendly.com/...',
			'show_when'   => [ 'product_type' => 'service' ],
		],
	],
] );

/**
 * Example 5: Conditional fields with explicit operator syntax
 *
 * Show discount fields only when discount is greater than 0.
 */
register_post_fields( 'pricing_settings', [
	'title'      => __( 'Pricing', 'textdomain' ),
	'post_types' => 'product',
	'fields'     => [
		'regular_price'  => [
			'label' => __( 'Regular Price', 'textdomain' ),
			'type'  => 'number',
			'min'   => 0,
			'step'  => 0.01,
		],
		'discount'       => [
			'label'   => __( 'Discount (%)', 'textdomain' ),
			'type'    => 'number',
			'min'     => 0,
			'max'     => 100,
			'default' => 0,
		],
		'sale_start'     => [
			'label'     => __( 'Sale Start Date', 'textdomain' ),
			'type'      => 'datetime',
			'show_when' => [
				'field'    => 'discount',
				'operator' => '>',
				'value'    => 0,
			],
		],
		'sale_end'       => [
			'label'     => __( 'Sale End Date', 'textdomain' ),
			'type'      => 'datetime',
			'show_when' => [
				'field'    => 'discount',
				'operator' => '>',
				'value'    => 0,
			],
		],
		'sale_badge'     => [
			'label'     => __( 'Sale Badge Text', 'textdomain' ),
			'type'      => 'text',
			'default'   => 'SALE!',
			'show_when' => [
				'field'    => 'discount',
				'operator' => '>=',
				'value'    => 10,
			],
		],
	],
] );

/**
 * Example 6: Multiple conditions (AND logic)
 *
 * Both conditions must be true for the field to show.
 */
register_post_fields( 'advanced_shipping', [
	'title'      => __( 'Advanced Shipping', 'textdomain' ),
	'post_types' => 'product',
	'fields'     => [
		'is_physical'       => [
			'label' => __( 'Physical Product', 'textdomain' ),
			'type'  => 'checkbox',
		],
		'requires_shipping' => [
			'label'     => __( 'Requires Shipping', 'textdomain' ),
			'type'      => 'checkbox',
			'show_when' => [ 'is_physical' => 1 ],
		],
		'shipping_notes'    => [
			'label'       => __( 'Shipping Notes', 'textdomain' ),
			'type'        => 'textarea',
			'rows'        => 3,
			'description' => __( 'Special shipping instructions.', 'textdomain' ),
			'show_when'   => [
				[ 'field' => 'is_physical', 'value' => 1 ],
				[ 'field' => 'requires_shipping', 'value' => 1 ],
			],
		],
		'fragile'           => [
			'label'     => __( 'Fragile Item', 'textdomain' ),
			'type'      => 'checkbox',
			'show_when' => [
				[ 'field' => 'is_physical', 'value' => 1 ],
				[ 'field' => 'requires_shipping', 'value' => 1 ],
			],
		],
	],
] );

/**
 * Example 7: Conditional fields inside a repeater
 *
 * Show different sub-fields based on reward type selection.
 */
register_post_fields( 'campaign_rewards', [
	'title'      => __( 'Campaign Rewards', 'textdomain' ),
	'post_types' => 'campaign',
	'fields'     => [
		'rewards' => [
			'label'        => __( 'Rewards', 'textdomain' ),
			'type'         => 'repeater',
			'button_label' => __( 'Add Reward', 'textdomain' ),
			'max_items'    => 10,
			'fields'       => [
				'reward_type'     => [
					'label'   => __( 'Reward Type', 'textdomain' ),
					'type'    => 'select',
					'options' => [
						''           => __( '— Select Reward —', 'textdomain' ),
						'send_email' => __( 'Send Email', 'textdomain' ),
						'discount'   => __( 'Offer Discount', 'textdomain' ),
						'assign_role'=> __( 'Assign User Role', 'textdomain' ),
						'add_points' => __( 'Add Points', 'textdomain' ),
					],
				],
				// Email reward fields
				'email_subject'   => [
					'label'     => __( 'Email Subject', 'textdomain' ),
					'type'      => 'text',
					'show_when' => [ 'reward_type' => 'send_email' ],
				],
				'email_body'      => [
					'label'     => __( 'Email Body', 'textdomain' ),
					'type'      => 'textarea',
					'rows'      => 4,
					'show_when' => [ 'reward_type' => 'send_email' ],
				],
				// Discount reward fields
				'discount_amount' => [
					'label'     => __( 'Discount Amount', 'textdomain' ),
					'type'      => 'number',
					'min'       => 0,
					'show_when' => [ 'reward_type' => 'discount' ],
				],
				'discount_type'   => [
					'label'     => __( 'Discount Type', 'textdomain' ),
					'type'      => 'select',
					'options'   => [
						'percent' => __( 'Percentage (%)', 'textdomain' ),
						'flat'    => __( 'Flat Amount ($)', 'textdomain' ),
					],
					'show_when' => [ 'reward_type' => 'discount' ],
				],
				// Role assignment fields
				'user_role'       => [
					'label'     => __( 'Role to Assign', 'textdomain' ),
					'type'      => 'select',
					'options'   => function () {
						return wp_roles()->get_names();
					},
					'show_when' => [ 'reward_type' => 'assign_role' ],
				],
				// Points reward fields
				'points'          => [
					'label'     => __( 'Points to Add', 'textdomain' ),
					'type'      => 'number',
					'min'       => 1,
					'show_when' => [ 'reward_type' => 'add_points' ],
				],
			],
		],
	],
] );

/**
 * Example 8: Using 'in' operator for multiple value matching
 *
 * Show field when value matches any in a list.
 */
register_post_fields( 'content_settings', [
	'title'      => __( 'Content Settings', 'textdomain' ),
	'post_types' => 'post',
	'fields'     => [
		'content_type'   => [
			'label'   => __( 'Content Type', 'textdomain' ),
			'type'    => 'select',
			'options' => [
				'article'  => __( 'Article', 'textdomain' ),
				'video'    => __( 'Video', 'textdomain' ),
				'podcast'  => __( 'Podcast', 'textdomain' ),
				'gallery'  => __( 'Gallery', 'textdomain' ),
			],
		],
		// Show for video or podcast (media content)
		'media_duration' => [
			'label'       => __( 'Duration (minutes)', 'textdomain' ),
			'type'        => 'number',
			'min'         => 0,
			'description' => __( 'Length of the media content.', 'textdomain' ),
			'show_when'   => [
				'field'    => 'content_type',
				'operator' => 'in',
				'value'    => [ 'video', 'podcast' ],
			],
		],
		// Show for video only
		'video_url'      => [
			'label'       => __( 'Video URL', 'textdomain' ),
			'type'        => 'url',
			'placeholder' => 'https://youtube.com/watch?v=...',
			'show_when'   => [ 'content_type' => 'video' ],
		],
		// Show for podcast only
		'podcast_url'    => [
			'label'       => __( 'Podcast URL', 'textdomain' ),
			'type'        => 'url',
			'placeholder' => 'https://...',
			'show_when'   => [ 'content_type' => 'podcast' ],
		],
		// Show for gallery only
		'gallery_images' => [
			'label'     => __( 'Gallery Images', 'textdomain' ),
			'type'      => 'gallery',
			'max_items' => 20,
			'show_when' => [ 'content_type' => 'gallery' ],
		],
	],
] );

/**
 * Example 9: External link toggle pattern
 *
 * Common pattern: show URL field only when checkbox is checked.
 */
register_post_fields( 'link_settings', [
	'title'      => __( 'Link Settings', 'textdomain' ),
	'post_types' => [ 'post', 'page' ],
	'context'    => 'side',
	'fields'     => [
		'use_external_link' => [
			'label'       => __( 'Use External Link', 'textdomain' ),
			'type'        => 'checkbox',
			'description' => __( 'Link to external URL instead of this page.', 'textdomain' ),
		],
		'external_url'      => [
			'label'       => __( 'External URL', 'textdomain' ),
			'type'        => 'url',
			'placeholder' => 'https://...',
			'show_when'   => [ 'use_external_link' => 1 ],
		],
		'open_in_new_tab'   => [
			'label'     => __( 'Open in New Tab', 'textdomain' ),
			'type'      => 'checkbox',
			'show_when' => [ 'use_external_link' => 1 ],
		],
	],
] );

/**
 * Example 10: Select fields with static and dynamic options
 */
register_post_fields( 'post_settings', [
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
 * Example 11: Image and file fields
 */
register_post_fields( 'media_attachments', [
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
 * Example 12: Gallery field
 */
register_post_fields( 'product_gallery', [
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
 * Example 13: Color and date fields
 */
register_post_fields( 'event_details', [
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
 * Example 14: WYSIWYG editor
 */
register_post_fields( 'product_details', [
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
 * Example 15: Relational fields - Post, User, Term selectors
 */
register_post_fields( 'related_content', [
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
 * Example 16: Amount type field (combined value + type)
 */
register_post_fields( 'pricing_options', [
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
 * Example 17: Group field (static group of related fields)
 */
register_post_fields( 'dimensions', [
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
 * Example 18: Repeater field
 *
 * A dynamic list of feature entries with drag-and-drop reordering.
 */
register_post_fields( 'product_features', [
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
 * Example 19: Repeater with image fields
 */
register_post_fields( 'team_members', [
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
 * Example 20: Custom sanitization
 */
register_post_fields( 'custom_content', [
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
 * Example 21: Permission-restricted fields
 */
register_post_fields( 'admin_settings', [
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
 * Example 22: Meta key prefixing
 */
register_post_fields( 'prefixed_fields', [
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
 * Example 23: Dynamic select options via callback
 */
register_post_fields( 'dynamic_options', [
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
	$fields = get_post_fields( 'product_info' );

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