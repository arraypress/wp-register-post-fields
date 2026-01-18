/**
 * Post Fields JavaScript
 *
 * Handles all interactive functionality for the WordPress Register Post Fields library.
 * Provides support for media uploads, galleries, repeaters, conditional logic,
 * AJAX selects, button groups, range sliders, and more.
 *
 * @package     ArrayPress\RegisterPostFields
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL2+
 * @version     2.0.0
 * @author      David Sherlock
 *
 * Dependencies:
 * - jQuery
 * - jQuery UI Sortable
 * - WordPress Media Library (wp.media)
 * - WordPress Color Picker (wp-color-picker)
 * - Select2 (for AJAX selects)
 *
 * Table of Contents:
 * 1. Configuration & Initialization
 * 2. Color Pickers
 * 3. Media Fields (Image/File)
 * 4. Gallery Fields
 * 5. Repeater Fields
 * 6. Conditional Logic
 * 7. Button Groups
 * 8. Range Sliders
 * 9. AJAX Selects
 */

(function ($) {
    'use strict';

    /* =========================================================================
       1. Configuration & Initialization
       ========================================================================= */

    /**
     * Configuration object from PHP (localized via wp_localize_script)
     *
     * @type {Object}
     * @property {Object} conditions - Field conditional logic configurations
     * @property {string} restUrl    - REST API base URL for AJAX requests
     * @property {string} nonce      - WordPress REST API nonce
     */
    var config = window.arraypressPostFields || {};

    /**
     * Post Fields Controller
     *
     * Main controller object that manages all field interactions.
     *
     * @namespace PostFields
     */
    var PostFields = {

        /**
         * Initialize all functionality
         *
         * Called on document ready to set up all field types.
         *
         * @memberof PostFields
         * @return {void}
         */
        init: function () {
            this.initColorPickers();
            this.initMediaFields();
            this.initGalleryFields();
            this.initRepeaterFields();
            this.initConditionalLogic();
            this.initAjaxSelects();
            this.initButtonGroups();
            this.initRangeSliders();
        },

        /* =====================================================================
           2. Color Pickers
           ===================================================================== */

        /**
         * Initialize WordPress color picker fields
         *
         * Applies the wpColorPicker plugin to all color picker inputs.
         *
         * @memberof PostFields
         * @return {void}
         */
        initColorPickers: function () {
            $('.arraypress-color-picker').wpColorPicker();
        },

        /* =====================================================================
           3. Media Fields (Image/File)
           ===================================================================== */

        /**
         * Initialize media (image/file) field interactions
         *
         * Sets up event handlers for selecting and removing media.
         *
         * @memberof PostFields
         * @return {void}
         */
        initMediaFields: function () {
            var self = this;

            // Select media button click
            $(document).on('click', '.arraypress-media-select', function (e) {
                e.preventDefault();
                var $field = $(this).closest('.arraypress-media-field');
                self.openMediaFrame($field);
            });

            // Remove media button click
            $(document).on('click', '.arraypress-media-remove', function (e) {
                e.preventDefault();
                var $field = $(this).closest('.arraypress-media-field');
                self.removeMedia($field);
            });
        },

        /**
         * Open WordPress media library frame
         *
         * Opens the media library for selecting an image or file.
         *
         * @memberof PostFields
         * @param {jQuery} $field - The media field container element
         * @return {void}
         */
        openMediaFrame: function ($field) {
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
                $input.val(attachment.id).trigger('change');

                if (type === 'image') {
                    // Use thumbnail size if available, otherwise full URL
                    var url = attachment.sizes && attachment.sizes.thumbnail
                        ? attachment.sizes.thumbnail.url
                        : attachment.url;
                    $field.find('.arraypress-media-preview').html(
                        '<img src="' + url + '" alt="" />'
                    );
                } else {
                    // File type - show filename with link
                    $field.find('.arraypress-file-preview').html(
                        '<a href="' + attachment.url + '" target="_blank">' +
                        attachment.filename +
                        '</a>'
                    );
                }

                $field.find('.arraypress-media-remove').show();
            });

            frame.open();
        },

        /**
         * Remove media from a field
         *
         * Clears the input value and preview for a media field.
         *
         * @memberof PostFields
         * @param {jQuery} $field - The media field container element
         * @return {void}
         */
        removeMedia: function ($field) {
            $field.find('.arraypress-media-input').val('').trigger('change');
            $field.find('.arraypress-media-preview, .arraypress-file-preview').empty();
            $field.find('.arraypress-media-remove').hide();
        },

        /* =====================================================================
           4. Gallery Fields
           ===================================================================== */

        /**
         * Initialize gallery field interactions
         *
         * Sets up event handlers for adding/removing images and sortable functionality.
         *
         * @memberof PostFields
         * @return {void}
         */
        initGalleryFields: function () {
            var self = this;

            // Add images button click
            $(document).on('click', '.arraypress-gallery-add', function (e) {
                e.preventDefault();
                var $field = $(this).closest('.arraypress-gallery-field');
                self.openGalleryFrame($field);
            });

            // Remove single image button click
            $(document).on('click', '.arraypress-gallery-remove', function (e) {
                e.preventDefault();
                var $item = $(this).closest('.arraypress-gallery-item');
                var $field = $item.closest('.arraypress-gallery-field');
                self.removeGalleryItem($item, $field);
            });

            // Make galleries sortable via drag and drop
            $('.arraypress-gallery-preview').sortable({
                items: '.arraypress-gallery-item',
                cursor: 'move',
                update: function (event, ui) {
                    var $field = $(this).closest('.arraypress-gallery-field');
                    self.updateGalleryInput($field);
                }
            });
        },

        /**
         * Open media library for gallery selection
         *
         * Opens the media library in multiple selection mode for galleries.
         *
         * @memberof PostFields
         * @param {jQuery} $field - The gallery field container element
         * @return {void}
         */
        openGalleryFrame: function ($field) {
            var self = this;
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
                    // Check max items limit
                    if (max > 0 && currentIds.length >= max) {
                        return;
                    }

                    // Skip if already in gallery
                    if (currentIds.indexOf(String(attachment.id)) !== -1) {
                        return;
                    }

                    currentIds.push(attachment.id);

                    // Get thumbnail URL
                    var url = attachment.sizes && attachment.sizes.thumbnail
                        ? attachment.sizes.thumbnail.url
                        : attachment.url;

                    // Add to preview
                    $preview.append(
                        '<div class="arraypress-gallery-item" data-id="' + attachment.id + '">' +
                        '<img src="' + url + '" alt="" />' +
                        '<button type="button" class="arraypress-gallery-remove">&times;</button>' +
                        '</div>'
                    );
                });

                $input.val(currentIds.join(',')).trigger('change');
            });

            frame.open();
        },

        /**
         * Remove a single image from gallery
         *
         * @memberof PostFields
         * @param {jQuery} $item  - The gallery item to remove
         * @param {jQuery} $field - The gallery field container
         * @return {void}
         */
        removeGalleryItem: function ($item, $field) {
            $item.remove();
            this.updateGalleryInput($field);
        },

        /**
         * Update gallery hidden input with current image IDs
         *
         * Rebuilds the comma-separated list of attachment IDs from the current order.
         *
         * @memberof PostFields
         * @param {jQuery} $field - The gallery field container
         * @return {void}
         */
        updateGalleryInput: function ($field) {
            var $input = $field.find('.arraypress-gallery-input');
            var ids = [];

            $field.find('.arraypress-gallery-item').each(function () {
                ids.push($(this).data('id'));
            });

            $input.val(ids.join(',')).trigger('change');
        },

        /* =====================================================================
           5. Repeater Fields
           ===================================================================== */

        /**
         * Initialize repeater field interactions
         *
         * Sets up event handlers for adding/removing/reordering rows.
         *
         * @memberof PostFields
         * @return {void}
         */
        initRepeaterFields: function () {
            var self = this;

            // Add row button click
            $(document).on('click', '.arraypress-repeater__add', function (e) {
                e.preventDefault();
                var $repeater = $(this).closest('.arraypress-repeater');
                self.addRepeaterRow($repeater);
            });

            // Remove row button click
            $(document).on('click', '.arraypress-repeater__row-remove', function (e) {
                e.preventDefault();
                var $repeater = $(this).closest('.arraypress-repeater');
                var $row = $(this).closest('.arraypress-repeater__row');
                self.removeRepeaterRow($repeater, $row);
            });

            // Toggle row collapse (vertical layout only)
            $(document).on('click', '.arraypress-repeater__row-toggle', function (e) {
                e.preventDefault();
                $(this).closest('.arraypress-repeater__row').toggleClass('is-collapsed');
            });

            // Make standard repeater rows sortable
            $('.arraypress-repeater__rows').sortable({
                handle: '.arraypress-repeater__row-handle',
                items: '.arraypress-repeater__row',
                cursor: 'move',
                placeholder: 'arraypress-repeater__row ui-sortable-placeholder',
                update: function (event, ui) {
                    var $repeater = $(this).closest('.arraypress-repeater');
                    self.updateRepeaterIndexes($repeater);
                }
            });

            // Make table layout rows sortable
            $('.arraypress-repeater--table .arraypress-repeater__table tbody').sortable({
                handle: '.arraypress-repeater__row-handle',
                items: 'tr.arraypress-repeater__row',
                cursor: 'move',
                placeholder: 'ui-sortable-placeholder',
                helper: function (e, tr) {
                    // Preserve cell widths during drag
                    var $originals = tr.children();
                    var $helper = tr.clone();
                    $helper.children().each(function (index) {
                        $(this).width($originals.eq(index).width());
                    });
                    return $helper;
                },
                update: function (event, ui) {
                    var $repeater = $(this).closest('.arraypress-repeater');
                    self.updateRepeaterIndexes($repeater);
                }
            });
        },

        /**
         * Add a new row to a repeater field
         *
         * Clones the template row and initializes any nested components.
         *
         * @memberof PostFields
         * @param {jQuery} $repeater - The repeater container element
         * @return {void}
         */
        addRepeaterRow: function ($repeater) {
            var self = this;
            var layout = $repeater.data('layout') || 'vertical';
            var $template = $repeater.find('.arraypress-repeater__template');
            var max = parseInt($repeater.data('max')) || 0;

            // Get rows container based on layout type
            var $rows;
            if (layout === 'table') {
                $rows = $repeater.find('.arraypress-repeater__table tbody');
            } else {
                $rows = $repeater.find('.arraypress-repeater__rows');
            }

            var currentCount = $rows.find('.arraypress-repeater__row').length;

            // Check max items limit
            if (max > 0 && currentCount >= max) {
                alert('Maximum items reached');
                return;
            }

            var newIndex = currentCount;

            // Clone template row
            var $newRow;
            if (layout === 'table') {
                $newRow = $($template.find('tr').prop('outerHTML'));
            } else {
                $newRow = $($template.children().first().prop('outerHTML'));
            }

            // Replace placeholder index with actual index
            $newRow.find('[name]').each(function () {
                var name = $(this).attr('name');
                $(this).attr('name', name.replace('__INDEX__', newIndex));
            });

            $newRow.attr('data-index', newIndex);

            // Update title for non-table layouts
            $newRow.find('.arraypress-repeater__row-title').text('Item ' + (newIndex + 1));

            // Hide empty state row if present (table layout)
            $repeater.find('.arraypress-repeater__empty-row').hide();

            // Append new row
            $rows.append($newRow);
            this.updateRepeaterIndexes($repeater);

            // Initialize components in the new row
            $newRow.find('.arraypress-color-picker').wpColorPicker();

            // Initialize AJAX selects in new row
            $newRow.find('.arraypress-ajax-select').each(function () {
                self.initSingleAjaxSelect($(this));
            });

            // Initialize button groups state
            $newRow.find('.arraypress-button-group__input').each(function () {
                var $input = $(this);
                $input.closest('.arraypress-button-group__item')
                    .toggleClass('is-selected', $input.is(':checked'));
            });

            // Evaluate conditional fields in the new row
            this.evaluateRowConditions($newRow);
        },

        /**
         * Remove a row from a repeater field
         *
         * @memberof PostFields
         * @param {jQuery} $repeater - The repeater container element
         * @param {jQuery} $row      - The row to remove
         * @return {void}
         */
        removeRepeaterRow: function ($repeater, $row) {
            var layout = $repeater.data('layout') || 'vertical';
            var min = parseInt($repeater.data('min')) || 0;

            // Get rows container based on layout type
            var $rows;
            if (layout === 'table') {
                $rows = $repeater.find('.arraypress-repeater__table tbody');
            } else {
                $rows = $repeater.find('.arraypress-repeater__rows');
            }

            var currentCount = $rows.find('.arraypress-repeater__row').length;

            // Check min items limit
            if (min > 0 && currentCount <= min) {
                alert('Minimum items required');
                return;
            }

            // Destroy Select2 before removing to prevent memory leaks
            $row.find('.select2-hidden-accessible').select2('destroy');

            $row.remove();
            this.updateRepeaterIndexes($repeater);

            // Show empty state row if no rows remain (table layout)
            if ($rows.find('.arraypress-repeater__row').length === 0) {
                $repeater.find('.arraypress-repeater__empty-row').show();
            }
        },

        /**
         * Update repeater row indexes after add/remove/sort
         *
         * Re-indexes all rows and updates their input name attributes.
         *
         * @memberof PostFields
         * @param {jQuery} $repeater - The repeater container element
         * @return {void}
         */
        updateRepeaterIndexes: function ($repeater) {
            var layout = $repeater.data('layout') || 'vertical';
            var metaKey = $repeater.data('meta-key');

            // Get rows based on layout type
            var $rows;
            if (layout === 'table') {
                $rows = $repeater.find('.arraypress-repeater__table tbody .arraypress-repeater__row');
            } else {
                $rows = $repeater.find('.arraypress-repeater__rows .arraypress-repeater__row');
            }

            $rows.each(function (index) {
                var $row = $(this);
                $row.attr('data-index', index);
                $row.find('.arraypress-repeater__row-title').text('Item ' + (index + 1));

                // Update all input names with new index
                $row.find('[name]').each(function () {
                    var name = $(this).attr('name');
                    var newName = name.replace(/\[\d+\]/, '[' + index + ']');
                    $(this).attr('name', newName);
                });
            });
        },

        /* =====================================================================
           6. Conditional Logic
           ===================================================================== */

        /**
         * Initialize conditional field logic
         *
         * Sets up event delegation for handling show/hide based on field values.
         *
         * @memberof PostFields
         * @return {void}
         */
        initConditionalLogic: function () {
            var self = this;

            // Use event delegation for all form field changes
            $(document).on(
                'change input',
                '.arraypress-metabox input, .arraypress-metabox select, .arraypress-metabox textarea',
                function () {
                    var $input = $(this);

                    // Ignore inputs in the template (not yet added to DOM properly)
                    if ($input.closest('.arraypress-repeater__template').length) {
                        return;
                    }

                    // Check if we're inside a repeater row
                    var $row = $input.closest('.arraypress-repeater__row');

                    if ($row.length) {
                        // Evaluate conditions within this row only
                        self.evaluateRowConditions($row);
                    } else {
                        // Top-level field - evaluate all conditions in the metabox
                        var $metabox = $input.closest('.arraypress-metabox');
                        self.evaluateMetaboxConditions($metabox);
                    }
                }
            );

            // Initial evaluation of all conditional fields on page load
            this.evaluateAllConditions();
        },

        /**
         * Evaluate all conditional fields on the page
         *
         * Called on initialization to set initial visibility state.
         *
         * @memberof PostFields
         * @return {void}
         */
        evaluateAllConditions: function () {
            var self = this;

            // Evaluate top-level fields in each metabox
            $('.arraypress-metabox').each(function () {
                self.evaluateMetaboxConditions($(this));
            });

            // Evaluate fields in existing repeater rows (not templates)
            $('.arraypress-repeater__rows .arraypress-repeater__row').each(function () {
                self.evaluateRowConditions($(this));
            });
        },

        /**
         * Evaluate all conditional fields in a metabox
         *
         * Evaluates top-level fields and group fields, excluding repeater rows.
         *
         * @memberof PostFields
         * @param {jQuery} $metabox - The metabox container element
         * @return {void}
         */
        evaluateMetaboxConditions: function ($metabox) {
            var self = this;

            // Find conditional fields that are NOT inside repeaters
            $metabox.find('[data-show-when]').each(function () {
                var $field = $(this);

                // Skip if inside a repeater row or template
                if ($field.closest('.arraypress-repeater__row, .arraypress-repeater__template').length) {
                    return;
                }

                self.evaluateSingleField($field, $metabox);
            });
        },

        /**
         * Evaluate conditional fields within a repeater row
         *
         * @memberof PostFields
         * @param {jQuery} $row - The repeater row element
         * @return {void}
         */
        evaluateRowConditions: function ($row) {
            var self = this;

            $row.find('[data-show-when]').each(function () {
                self.evaluateSingleField($(this), $row);
            });
        },

        /**
         * Evaluate a single conditional field
         *
         * Checks all conditions and shows/hides the field accordingly.
         *
         * @memberof PostFields
         * @param {jQuery} $field   - The field wrapper with data-show-when attribute
         * @param {jQuery} $context - Context element for finding controller fields
         * @return {void}
         */
        evaluateSingleField: function ($field, $context) {
            var conditions = $field.data('show-when');

            if (!conditions || !Array.isArray(conditions) || !conditions.length) {
                return;
            }

            var allMet = true;

            // All conditions must be met (AND logic)
            for (var i = 0; i < conditions.length; i++) {
                var condition = conditions[i];
                var value = this.getFieldValue(condition.field, $context);
                var met = this.evaluateCondition(value, condition.operator, condition.value);

                if (!met) {
                    allMet = false;
                    break;
                }
            }

            // Show or hide field based on evaluation
            if (allMet) {
                $field.removeClass('arraypress-field--hidden');
            } else {
                $field.addClass('arraypress-field--hidden');
            }
        },

        /**
         * Get the current value of a field
         *
         * Looks up a field by its key within the given context.
         *
         * @memberof PostFields
         * @param {string} fieldKey - The field key to look up
         * @param {jQuery} $context - The context element to search within
         * @return {mixed} The field value
         */
        getFieldValue: function (fieldKey, $context) {
            var $input = null;

            // First, try to find by data-field-key within context
            var $fieldWrapper = $context.find('[data-field-key="' + fieldKey + '"]').first();

            if ($fieldWrapper.length) {
                $input = $fieldWrapper.find('input, select, textarea').first();
            }

            // If not found and we're NOT in a repeater row, fall back to document level
            if ((!$input || !$input.length) && !$context.hasClass('arraypress-repeater__row')) {
                $input = $('[name="' + fieldKey + '"], [name="' + fieldKey + '[]"]').first();
            }

            if (!$input || !$input.length) {
                return '';
            }

            // Handle different input types
            if ($input.is(':checkbox')) {
                return $input.is(':checked') ? 1 : 0;
            }

            if ($input.is(':radio')) {
                var radioName = $input.attr('name');
                return $('input[name="' + radioName + '"]:checked').val() || '';
            }

            return $input.val();
        },

        /**
         * Evaluate a single condition
         *
         * Compares actual value against expected value using the specified operator.
         *
         * @memberof PostFields
         * @param {mixed}  actualValue - The actual field value
         * @param {string} operator    - The comparison operator
         * @param {mixed}  expected    - The expected value
         * @return {boolean} True if condition is met
         */
        evaluateCondition: function (actualValue, operator, expected) {
            // Normalize values for comparison
            var actual = this.normalizeValue(actualValue);
            var expect = this.normalizeValue(expected);

            switch (operator) {
                case '==':
                case '=':
                    return actual == expect;

                case '===':
                    return actual === expect;

                case '!=':
                case '<>':
                    return actual != expect;

                case '!==':
                    return actual !== expect;

                case '>':
                    return parseFloat(actual) > parseFloat(expect);

                case '>=':
                    return parseFloat(actual) >= parseFloat(expect);

                case '<':
                    return parseFloat(actual) < parseFloat(expect);

                case '<=':
                    return parseFloat(actual) <= parseFloat(expect);

                case 'in':
                    var arr = Array.isArray(expect) ? expect : [expect];
                    return arr.indexOf(actual) !== -1;

                case 'not_in':
                    var arr2 = Array.isArray(expect) ? expect : [expect];
                    return arr2.indexOf(actual) === -1;

                case 'contains':
                    return String(actual).indexOf(String(expect)) !== -1;

                case 'not_contains':
                    return String(actual).indexOf(String(expect)) === -1;

                case 'empty':
                    return !actual || actual === '' || actual === '0' || actual === 0;

                case 'not_empty':
                    return actual && actual !== '' && actual !== '0' && actual !== 0;

                default:
                    // Default to equality check
                    return actual == expect;
            }
        },

        /**
         * Normalize a value for comparison
         *
         * Converts string numbers to actual numbers for proper comparison.
         *
         * @memberof PostFields
         * @param {mixed} value - The value to normalize
         * @return {mixed} Normalized value
         */
        normalizeValue: function (value) {
            if (value === null || value === undefined) {
                return '';
            }

            // Convert string numbers to actual numbers
            if (typeof value === 'string' && !isNaN(value) && value !== '') {
                return parseFloat(value);
            }

            return value;
        },

        /* =====================================================================
           7. Button Groups
           ===================================================================== */

        /**
         * Initialize button group fields
         *
         * Sets up visual state handling for toggle button groups.
         *
         * @memberof PostFields
         * @return {void}
         */
        initButtonGroups: function () {
            // Handle button group input changes with event delegation
            $(document).on('change', '.arraypress-button-group__input', function () {
                var $input = $(this);
                var $group = $input.closest('.arraypress-button-group');
                var isMultiple = $group.hasClass('arraypress-button-group--multiple');

                if (isMultiple) {
                    // Toggle this item's selected state
                    $input.closest('.arraypress-button-group__item')
                        .toggleClass('is-selected', $input.is(':checked'));
                } else {
                    // Radio - only one can be selected
                    $group.find('.arraypress-button-group__item').removeClass('is-selected');
                    $input.closest('.arraypress-button-group__item').addClass('is-selected');
                }
            });
        },

        /* =====================================================================
           8. Range Sliders
           ===================================================================== */

        /**
         * Initialize range slider fields
         *
         * Sets up live output updates for range inputs.
         *
         * @memberof PostFields
         * @return {void}
         */
        initRangeSliders: function () {
            // Update output display on input change
            $(document).on('input', '.arraypress-range-input', function () {
                var $input = $(this);
                var $output = $input.siblings('.arraypress-range-output');
                var $field = $input.closest('.arraypress-range-field');
                var unit = $field.data('unit') || '';

                $output.text($input.val() + unit);
            });
        },

        /* =====================================================================
           9. AJAX Selects
           ===================================================================== */

        /**
         * Initialize all AJAX select fields
         *
         * Finds and initializes Select2 on all AJAX-powered select fields.
         *
         * @memberof PostFields
         * @return {void}
         */
        initAjaxSelects: function () {
            var self = this;

            // Initialize all AJAX selects not in templates
            $('.arraypress-ajax-select').each(function () {
                var $select = $(this);

                // Skip if in a template
                if ($select.closest('.arraypress-repeater__template').length) {
                    return;
                }

                self.initSingleAjaxSelect($select);
            });
        },

        /**
         * Initialize a single AJAX select field
         *
         * Configures Select2 with AJAX data source and hydrates existing values.
         *
         * @memberof PostFields
         * @param {jQuery} $select - The select element to initialize
         * @return {void}
         */
        initSingleAjaxSelect: function ($select) {
            var self = this;

            // Skip if already initialized
            if ($select.hasClass('select2-hidden-accessible')) {
                return;
            }

            var metaboxId = $select.data('metabox-id');
            var fieldKey = $select.data('field-key');
            var isMultiple = $select.prop('multiple');
            var placeholder = $select.data('placeholder') || 'Search...';
            var restUrl = config.restUrl || '';
            var nonce = config.nonce || '';

            var select2Options = {
                width: '100%',
                allowClear: true,
                placeholder: placeholder,
                ajax: {
                    url: restUrl,
                    dataType: 'json',
                    delay: 250,
                    headers: {
                        'X-WP-Nonce': nonce
                    },
                    data: function (params) {
                        return {
                            metabox_id: metaboxId,
                            field_key: fieldKey,
                            search: params.term || ''
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.map(function (item) {
                                return {
                                    id: item.value,
                                    text: item.label
                                };
                            })
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0
            };

            // Get current values BEFORE initializing Select2
            var currentValues = $select.val();

            // Initialize Select2
            $select.select2(select2Options);

            // Hydrate existing values (fetch labels for saved IDs)
            if (currentValues && currentValues.length) {
                var ids = Array.isArray(currentValues) ? currentValues : [currentValues];
                ids = ids.filter(function (id) {
                    return id && id !== '';
                });

                if (ids.length > 0) {
                    self.hydrateAjaxSelect($select, ids, metaboxId, fieldKey, restUrl, nonce);
                }
            }
        },

        /**
         * Hydrate AJAX select with labels for existing values
         *
         * Fetches display labels for pre-selected IDs from the server.
         *
         * @memberof PostFields
         * @param {jQuery} $select   - The select element
         * @param {Array}  ids       - Array of IDs to hydrate
         * @param {string} metaboxId - The metabox ID
         * @param {string} fieldKey  - The field key
         * @param {string} restUrl   - The REST API URL
         * @param {string} nonce     - The WP REST nonce
         * @return {void}
         */
        hydrateAjaxSelect: function ($select, ids, metaboxId, fieldKey, restUrl, nonce) {
            $.ajax({
                url: restUrl,
                data: {
                    metabox_id: metaboxId,
                    field_key: fieldKey,
                    include: ids.join(',')
                },
                headers: {
                    'X-WP-Nonce': nonce
                }
            }).done(function (results) {
                // Clear existing options and add new ones with proper labels
                $select.empty();

                results.forEach(function (item) {
                    var option = new Option(item.label, item.value, true, true);
                    $select.append(option);
                });

                $select.trigger('change.select2');
            }).fail(function () {
                console.warn('Failed to hydrate ajax select:', fieldKey);
            });
        }
    };

    /* =========================================================================
       Document Ready
       ========================================================================= */

    /**
     * Initialize Post Fields on document ready
     */
    $(document).ready(function () {
        PostFields.init();
    });

})(jQuery);