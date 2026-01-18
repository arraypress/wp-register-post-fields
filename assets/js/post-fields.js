/**
 * Post Fields JavaScript
 *
 * Handles all interactive functionality for the WordPress Register Post Fields library.
 *
 * @package ArrayPress\RegisterPostFields
 * @version 2.0.0
 */

(function ($) {
    'use strict';

    // Configuration from PHP (localized)
    var config = window.arraypressPostFields || {};

    /**
     * Post Fields Controller
     */
    var PostFields = {

        /**
         * Initialize all functionality
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

        /**
         * Initialize color picker fields
         */
        initColorPickers: function () {
            $('.arraypress-color-picker').wpColorPicker();
        },

        /**
         * Initialize media (image/file) fields
         */
        initMediaFields: function () {
            var self = this;

            // Select media
            $(document).on('click', '.arraypress-media-select', function (e) {
                e.preventDefault();
                var $field = $(this).closest('.arraypress-media-field');
                self.openMediaFrame($field);
            });

            // Remove media
            $(document).on('click', '.arraypress-media-remove', function (e) {
                e.preventDefault();
                var $field = $(this).closest('.arraypress-media-field');
                self.removeMedia($field);
            });
        },

        /**
         * Open media library frame
         *
         * @param {jQuery} $field The media field container
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
                    var url = attachment.sizes && attachment.sizes.thumbnail
                        ? attachment.sizes.thumbnail.url
                        : attachment.url;
                    $field.find('.arraypress-media-preview').html('<img src="' + url + '" alt="" />');
                } else {
                    $field.find('.arraypress-file-preview').html(
                        '<a href="' + attachment.url + '" target="_blank">' + attachment.filename + '</a>'
                    );
                }

                $field.find('.arraypress-media-remove').show();
            });

            frame.open();
        },

        /**
         * Remove media from field
         *
         * @param {jQuery} $field The media field container
         */
        removeMedia: function ($field) {
            $field.find('.arraypress-media-input').val('').trigger('change');
            $field.find('.arraypress-media-preview, .arraypress-file-preview').empty();
            $field.find('.arraypress-media-remove').hide();
        },

        /**
         * Initialize gallery fields
         */
        initGalleryFields: function () {
            var self = this;

            // Add images
            $(document).on('click', '.arraypress-gallery-add', function (e) {
                e.preventDefault();
                var $field = $(this).closest('.arraypress-gallery-field');
                self.openGalleryFrame($field);
            });

            // Remove single image
            $(document).on('click', '.arraypress-gallery-remove', function (e) {
                e.preventDefault();
                var $item = $(this).closest('.arraypress-gallery-item');
                var $field = $item.closest('.arraypress-gallery-field');
                self.removeGalleryItem($item, $field);
            });

            // Make galleries sortable
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
         * Open gallery media frame
         *
         * @param {jQuery} $field The gallery field container
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

                $input.val(currentIds.join(',')).trigger('change');
            });

            frame.open();
        },

        /**
         * Remove a single gallery item
         *
         * @param {jQuery} $item  The gallery item to remove
         * @param {jQuery} $field The gallery field container
         */
        removeGalleryItem: function ($item, $field) {
            $item.remove();
            this.updateGalleryInput($field);
        },

        /**
         * Update gallery hidden input with current image IDs
         *
         * @param {jQuery} $field The gallery field container
         */
        updateGalleryInput: function ($field) {
            var $input = $field.find('.arraypress-gallery-input');
            var ids = [];

            $field.find('.arraypress-gallery-item').each(function () {
                ids.push($(this).data('id'));
            });

            $input.val(ids.join(',')).trigger('change');
        },

        /**
         * Initialize repeater fields
         */
        initRepeaterFields: function() {
            var self = this;

            // Add row
            $(document).on('click', '.arraypress-repeater__add', function(e) {
                e.preventDefault();
                var $repeater = $(this).closest('.arraypress-repeater');
                self.addRepeaterRow($repeater);
            });

            // Remove row
            $(document).on('click', '.arraypress-repeater__row-remove', function(e) {
                e.preventDefault();
                var $repeater = $(this).closest('.arraypress-repeater');
                var $row = $(this).closest('.arraypress-repeater__row');
                self.removeRepeaterRow($repeater, $row);
            });

            // Toggle row collapse (only for vertical layout)
            $(document).on('click', '.arraypress-repeater__row-toggle', function(e) {
                e.preventDefault();
                $(this).closest('.arraypress-repeater__row').toggleClass('is-collapsed');
            });

            // Make standard rows sortable
            $('.arraypress-repeater__rows').sortable({
                handle: '.arraypress-repeater__row-handle',
                items: '.arraypress-repeater__row',
                cursor: 'move',
                placeholder: 'arraypress-repeater__row ui-sortable-placeholder',
                update: function(event, ui) {
                    var $repeater = $(this).closest('.arraypress-repeater');
                    self.updateRepeaterIndexes($repeater);
                }
            });

            // Make table rows sortable
            $('.arraypress-repeater--table .arraypress-repeater__table tbody').sortable({
                handle: '.arraypress-repeater__row-handle',
                items: 'tr.arraypress-repeater__row',
                cursor: 'move',
                placeholder: 'ui-sortable-placeholder',
                helper: function(e, tr) {
                    var $originals = tr.children();
                    var $helper = tr.clone();
                    $helper.children().each(function(index) {
                        $(this).width($originals.eq(index).width());
                    });
                    return $helper;
                },
                update: function(event, ui) {
                    var $repeater = $(this).closest('.arraypress-repeater');
                    self.updateRepeaterIndexes($repeater);
                }
            });
        },

        /**
         * Add a new repeater row
         *
         * @param {jQuery} $repeater The repeater container
         */
        addRepeaterRow: function($repeater) {
            var self = this;
            var layout = $repeater.data('layout') || 'vertical';
            var $template = $repeater.find('.arraypress-repeater__template');
            var max = parseInt($repeater.data('max')) || 0;

            // Get rows container based on layout
            var $rows;
            if (layout === 'table') {
                $rows = $repeater.find('.arraypress-repeater__table tbody');
            } else {
                $rows = $repeater.find('.arraypress-repeater__rows');
            }

            var currentCount = $rows.find('.arraypress-repeater__row').length;

            if (max > 0 && currentCount >= max) {
                alert('Maximum items reached');
                return;
            }

            var newIndex = currentCount;

            // For table layout, get the row from inside the template's table
            var $newRow;
            if (layout === 'table') {
                $newRow = $($template.find('tr').prop('outerHTML'));
            } else {
                $newRow = $($template.children().first().prop('outerHTML'));
            }

            // Replace placeholder index with actual index
            $newRow.find('[name]').each(function() {
                var name = $(this).attr('name');
                $(this).attr('name', name.replace('__INDEX__', newIndex));
            });

            $newRow.attr('data-index', newIndex);

            // Update title for non-table layouts
            $newRow.find('.arraypress-repeater__row-title').text('Item ' + (newIndex + 1));

            $rows.append($newRow);
            this.updateRepeaterIndexes($repeater);

            // Initialize components in new row
            $newRow.find('.arraypress-color-picker').wpColorPicker();

            // Initialize ajax selects in new row
            $newRow.find('.arraypress-ajax-select').each(function() {
                self.initSingleAjaxSelect($(this));
            });

            // Initialize button groups state
            $newRow.find('.arraypress-button-group__input').each(function() {
                var $input = $(this);
                $input.closest('.arraypress-button-group__item').toggleClass('is-selected', $input.is(':checked'));
            });

            // Evaluate conditional fields in the new row
            this.evaluateRowConditions($newRow);
        },

        /**
         * Remove a repeater row
         *
         * @param {jQuery} $repeater The repeater container
         * @param {jQuery} $row      The row to remove
         */
        removeRepeaterRow: function ($repeater, $row) {
            var $rows = $repeater.find('.arraypress-repeater__rows');
            var min = parseInt($repeater.data('min')) || 0;
            var currentCount = $rows.find('.arraypress-repeater__row').length;

            if (min > 0 && currentCount <= min) {
                alert('Minimum items required');
                return;
            }

            // Destroy Select2 before removing
            $row.find('.select2-hidden-accessible').select2('destroy');

            $row.remove();
            this.updateRepeaterIndexes($repeater);
        },

        /**
         * Update repeater row indexes after add/remove/sort
         *
         * @param {jQuery} $repeater The repeater container
         */
        updateRepeaterIndexes: function($repeater) {
            var layout = $repeater.data('layout') || 'vertical';
            var metaKey = $repeater.data('meta-key');

            // Get rows based on layout
            var $rows;
            if (layout === 'table') {
                $rows = $repeater.find('.arraypress-repeater__table tbody .arraypress-repeater__row');
            } else {
                $rows = $repeater.find('.arraypress-repeater__rows .arraypress-repeater__row');
            }

            $rows.each(function(index) {
                var $row = $(this);
                $row.attr('data-index', index);
                $row.find('.arraypress-repeater__row-title').text('Item ' + (index + 1));

                $row.find('[name]').each(function() {
                    var name = $(this).attr('name');
                    var newName = name.replace(/\[\d+\]/, '[' + index + ']');
                    $(this).attr('name', newName);
                });
            });
        },

        /**
         * Initialize conditional field logic using event delegation
         */
        initConditionalLogic: function () {
            var self = this;

            // Use event delegation on the document for all form changes
            // This handles both existing and dynamically added elements
            $(document).on('change input', '.arraypress-metabox input, .arraypress-metabox select, .arraypress-metabox textarea', function () {
                var $input = $(this);

                // Ignore inputs in the template
                if ($input.closest('.arraypress-repeater__template').length) {
                    return;
                }

                // Check if we're in a repeater row
                var $row = $input.closest('.arraypress-repeater__row');

                if ($row.length) {
                    // We're in a repeater row - evaluate conditions within this row
                    self.evaluateRowConditions($row);
                } else {
                    // Top-level field or group - evaluate all conditions in the metabox
                    var $metabox = $input.closest('.arraypress-metabox');
                    self.evaluateMetaboxConditions($metabox);
                }
            });

            // Initial evaluation of all conditional fields
            this.evaluateAllConditions();
        },

        /**
         * Initialize button group fields
         */
        initButtonGroups: function() {
            // Handle button group clicks with event delegation
            $(document).on('change', '.arraypress-button-group__input', function() {
                var $input = $(this);
                var $group = $input.closest('.arraypress-button-group');
                var isMultiple = $group.hasClass('arraypress-button-group--multiple');

                if (isMultiple) {
                    // Toggle this item
                    $input.closest('.arraypress-button-group__item').toggleClass('is-selected', $input.is(':checked'));
                } else {
                    // Radio - only one selected
                    $group.find('.arraypress-button-group__item').removeClass('is-selected');
                    $input.closest('.arraypress-button-group__item').addClass('is-selected');
                }
            });
        },

        /**
         * Initialize range slider fields
         */
        initRangeSliders: function() {
            // Update output on input
            $(document).on('input', '.arraypress-range-input', function() {
                var $input = $(this);
                var $output = $input.siblings('.arraypress-range-output');
                var $field = $input.closest('.arraypress-range-field');
                var unit = $field.data('unit') || '';

                $output.text($input.val() + unit);
            });
        },

        /**
         * Evaluate all conditional fields on the page
         */
        evaluateAllConditions: function () {
            var self = this;

            $('.arraypress-metabox').each(function () {
                self.evaluateMetaboxConditions($(this));
            });

            // Evaluate fields in existing repeater rows (not template)
            $('.arraypress-repeater__rows .arraypress-repeater__row').each(function () {
                self.evaluateRowConditions($(this));
            });
        },

        /**
         * Evaluate all conditional fields in a metabox (excluding repeater rows)
         *
         * @param {jQuery} $metabox The metabox container
         */
        evaluateMetaboxConditions: function ($metabox) {
            var self = this;

            // Find conditional fields that are direct children or in groups (but not in repeaters)
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
         * @param {jQuery} $row The repeater row
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
         * @param {jQuery} $field   The field wrapper with data-show-when
         * @param {jQuery} $context Context for finding controller fields
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

            // Show or hide field
            if (allMet) {
                $field.removeClass('arraypress-field--hidden');
            } else {
                $field.addClass('arraypress-field--hidden');
            }
        },

        /**
         * Get the current value of a field
         *
         * @param {string} fieldKey The field key
         * @param {jQuery} $context The context element
         * @return {mixed} The field value
         */
        getFieldValue: function (fieldKey, $context) {
            var $input = null;

            // First, try to find by data-field-key within context
            var $fieldWrapper = $context.find('[data-field-key="' + fieldKey + '"]').first();

            if ($fieldWrapper.length) {
                $input = $fieldWrapper.find('input, select, textarea').first();
            }

            // If not found and we're in a repeater row, don't fall back to document level
            // This ensures row-scoped lookups stay within the row
            if ((!$input || !$input.length) && !$context.hasClass('arraypress-repeater__row')) {
                // Fall back to name-based search at document level
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
         * @param {mixed}  actualValue The actual field value
         * @param {string} operator    The comparison operator
         * @param {mixed}  expected    The expected value
         * @return {boolean} Whether the condition is met
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
                    return actual == expect;
            }
        },

        /**
         * Normalize a value for comparison
         *
         * @param {mixed} value The value to normalize
         * @return {mixed} Normalized value
         */
        normalizeValue: function (value) {
            if (value === null || value === undefined) {
                return '';
            }

            // Convert string numbers to actual numbers for comparison
            if (typeof value === 'string' && !isNaN(value) && value !== '') {
                return parseFloat(value);
            }

            return value;
        },

        /**
         * Initialize all ajax select fields
         */
        initAjaxSelects: function () {
            var self = this;

            // Initialize all ajax selects not in templates
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
         * Initialize a single ajax select field
         *
         * @param {jQuery} $select The select element
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
         * Hydrate ajax select with labels for existing values
         *
         * @param {jQuery} $select    The select element
         * @param {Array}  ids        Array of IDs to hydrate
         * @param {string} metaboxId  The metabox ID
         * @param {string} fieldKey   The field key
         * @param {string} restUrl    The REST URL
         * @param {string} nonce      The WP nonce
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

    /**
     * Initialize on document ready
     */
    $(document).ready(function () {
        PostFields.init();
    });

})(jQuery);