/**
 * Post Fields JavaScript
 *
 * Handles all interactive functionality for the WordPress Register Post Fields library.
 *
 * @package ArrayPress\RegisterPostFields
 * @version 2.0.0
 */

(function($) {
    'use strict';

    /**
     * Post Fields Controller
     */
    var PostFields = {

        /**
         * Initialize all functionality
         */
        init: function() {
            this.initColorPickers();
            this.initMediaFields();
            this.initGalleryFields();
            this.initRepeaterFields();
            this.initConditionalLogic();
        },

        /**
         * Initialize color picker fields
         */
        initColorPickers: function() {
            $('.arraypress-color-picker').wpColorPicker();
        },

        /**
         * Initialize media (image/file) fields
         */
        initMediaFields: function() {
            var self = this;

            // Select media
            $(document).on('click', '.arraypress-media-select', function(e) {
                e.preventDefault();
                var $field = $(this).closest('.arraypress-media-field');
                self.openMediaFrame($field);
            });

            // Remove media
            $(document).on('click', '.arraypress-media-remove', function(e) {
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
        openMediaFrame: function($field) {
            var $input = $field.find('.arraypress-media-input');
            var type = $field.data('type');

            var frame = wp.media({
                title: type === 'image' ? 'Select Image' : 'Select File',
                button: { text: 'Use this ' + type },
                multiple: false,
                library: type === 'image' ? { type: 'image' } : {}
            });

            frame.on('select', function() {
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
        removeMedia: function($field) {
            $field.find('.arraypress-media-input').val('').trigger('change');
            $field.find('.arraypress-media-preview, .arraypress-file-preview').empty();
            $field.find('.arraypress-media-remove').hide();
        },

        /**
         * Initialize gallery fields
         */
        initGalleryFields: function() {
            var self = this;

            // Add images
            $(document).on('click', '.arraypress-gallery-add', function(e) {
                e.preventDefault();
                var $field = $(this).closest('.arraypress-gallery-field');
                self.openGalleryFrame($field);
            });

            // Remove single image
            $(document).on('click', '.arraypress-gallery-remove', function(e) {
                e.preventDefault();
                var $item = $(this).closest('.arraypress-gallery-item');
                var $field = $item.closest('.arraypress-gallery-field');
                self.removeGalleryItem($item, $field);
            });

            // Make galleries sortable
            $('.arraypress-gallery-preview').sortable({
                items: '.arraypress-gallery-item',
                cursor: 'move',
                update: function(event, ui) {
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
        openGalleryFrame: function($field) {
            var self = this;
            var $input = $field.find('.arraypress-gallery-input');
            var $preview = $field.find('.arraypress-gallery-preview');
            var max = parseInt($field.data('max')) || 0;

            var frame = wp.media({
                title: 'Select Images',
                button: { text: 'Add to Gallery' },
                multiple: true,
                library: { type: 'image' }
            });

            frame.on('select', function() {
                var attachments = frame.state().get('selection').toJSON();
                var currentIds = $input.val() ? $input.val().split(',') : [];

                attachments.forEach(function(attachment) {
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
        removeGalleryItem: function($item, $field) {
            $item.remove();
            this.updateGalleryInput($field);
        },

        /**
         * Update gallery hidden input with current image IDs
         *
         * @param {jQuery} $field The gallery field container
         */
        updateGalleryInput: function($field) {
            var $input = $field.find('.arraypress-gallery-input');
            var ids = [];

            $field.find('.arraypress-gallery-item').each(function() {
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

            // Toggle row collapse
            $(document).on('click', '.arraypress-repeater__row-toggle', function(e) {
                e.preventDefault();
                $(this).closest('.arraypress-repeater__row').toggleClass('is-collapsed');
            });

            // Make rows sortable
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
        },

        /**
         * Add a new repeater row
         *
         * @param {jQuery} $repeater The repeater container
         */
        addRepeaterRow: function($repeater) {
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
            $newRow.find('[name]').each(function() {
                var name = $(this).attr('name');
                $(this).attr('name', name.replace('__INDEX__', newIndex));
            });

            $newRow.attr('data-index', newIndex);
            $newRow.find('.arraypress-repeater__row-title').text('Item ' + (newIndex + 1));

            $rows.append($newRow);
            this.updateRepeaterIndexes($repeater);

            // Initialize components in new row
            $newRow.find('.arraypress-color-picker').wpColorPicker();

            // Initialize conditional logic for new row (with event binding)
            this.initRowConditionalLogic($newRow);
        },

        /**
         * Remove a repeater row
         *
         * @param {jQuery} $repeater The repeater container
         * @param {jQuery} $row      The row to remove
         */
        removeRepeaterRow: function($repeater, $row) {
            var $rows = $repeater.find('.arraypress-repeater__rows');
            var min = parseInt($repeater.data('min')) || 0;
            var currentCount = $rows.find('.arraypress-repeater__row').length;

            if (min > 0 && currentCount <= min) {
                alert('Minimum items required');
                return;
            }

            $row.remove();
            this.updateRepeaterIndexes($repeater);
        },

        /**
         * Update repeater row indexes after add/remove/sort
         *
         * @param {jQuery} $repeater The repeater container
         */
        updateRepeaterIndexes: function($repeater) {
            var metaKey = $repeater.data('meta-key');

            $repeater.find('.arraypress-repeater__rows .arraypress-repeater__row').each(function(index) {
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
         * Initialize conditional field logic
         */
        initConditionalLogic: function() {
            var self = this;

            // Find all conditional fields and set up listeners
            $('.arraypress-metabox').find('[data-show-when]').each(function() {
                var $field = $(this);
                var $row = $field.closest('.arraypress-repeater__row');

                // Skip fields inside the template
                if ($field.closest('.arraypress-repeater__template').length) {
                    return;
                }

                // Skip fields inside repeater rows - they'll be initialized separately
                if ($row.length) {
                    return;
                }

                self.bindConditionalField($field);
            });

            // Initialize existing repeater rows
            $('.arraypress-repeater__rows .arraypress-repeater__row').each(function() {
                self.initRowConditionalLogic($(this));
            });
        },

        /**
         * Bind conditional logic for a single field
         *
         * @param {jQuery} $field The conditional field
         */
        bindConditionalField: function($field) {
            var self = this;
            var conditions = $field.data('show-when');

            if (!conditions || !conditions.length) return;

            // Set up change listeners for each controller field
            conditions.forEach(function(condition) {
                var $context = $field.closest('.arraypress-repeater__row, .arraypress-group, .arraypress-metabox');
                var $controller = self.findControllerField(condition.field, $context);

                if ($controller.length) {
                    // Bind change event directly to the controller element
                    $controller.on('change input', function() {
                        self.evaluateFieldVisibility($field, conditions);
                    });
                }
            });

            // Initial evaluation
            self.evaluateFieldVisibility($field, conditions);
        },

        /**
         * Initialize conditional logic for a specific row (used for new repeater rows)
         *
         * @param {jQuery} $row The repeater row
         */
        initRowConditionalLogic: function($row) {
            var self = this;

            $row.find('[data-show-when]').each(function() {
                var $field = $(this);
                var conditions = $field.data('show-when');

                if (!conditions || !conditions.length) return;

                // Bind change events to controller fields within this row
                conditions.forEach(function(condition) {
                    var $controller = self.findControllerField(condition.field, $row);

                    if ($controller.length) {
                        // Use .off().on() to prevent duplicate bindings
                        $controller.off('change.conditional input.conditional')
                            .on('change.conditional input.conditional', function() {
                                self.evaluateFieldVisibility($field, conditions);
                            });
                    }
                });

                // Initial evaluation within row context
                self.evaluateFieldVisibility($field, conditions);
            });
        },

        /**
         * Find the controller field element
         *
         * @param {string} fieldKey The field key
         * @param {jQuery} $context The context element
         * @return {jQuery} The controller field input element
         */
        findControllerField: function(fieldKey, $context) {
            var $input;

            // Try to find within context first (for repeater/group fields)
            $input = $context.find('[data-field-key="' + fieldKey + '"]').find('input, select, textarea').first();

            // Fall back to document-level search for top-level fields
            if (!$input.length) {
                $input = $('[name="' + fieldKey + '"], [name="' + fieldKey + '[]"]').first();
            }

            return $input;
        },

        /**
         * Get the selector for a controller field (deprecated - use findControllerField instead)
         *
         * @param {string} fieldKey The field key
         * @param {jQuery} $context The context element (for finding within groups/repeaters)
         * @return {string} jQuery selector
         */
        getFieldSelector: function(fieldKey, $context) {
            // Check if we're in a repeater or group context
            var $parent = $context.closest('.arraypress-repeater__row, .arraypress-group');

            if ($parent.length) {
                // Within repeater/group - look for field within same parent
                return '[data-field-key="' + fieldKey + '"] input, ' +
                    '[data-field-key="' + fieldKey + '"] select, ' +
                    '[data-field-key="' + fieldKey + '"] textarea';
            }

            // Top-level field
            return '[name="' + fieldKey + '"], [name="' + fieldKey + '[]"]';
        },

        /**
         * Evaluate and update field visibility based on conditions
         *
         * @param {jQuery} $field     The conditional field
         * @param {Array}  conditions Array of conditions to evaluate
         */
        evaluateFieldVisibility: function($field, conditions) {
            var self = this;
            var $context = $field.closest('.arraypress-repeater__row, .arraypress-group, .arraypress-metabox');
            var allMet = true;

            // All conditions must be met (AND logic)
            conditions.forEach(function(condition) {
                if (!allMet) return;

                var value = self.getFieldValue(condition.field, $context);
                var met = self.evaluateCondition(value, condition.operator, condition.value);

                if (!met) {
                    allMet = false;
                }
            });

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
        getFieldValue: function(fieldKey, $context) {
            var $input;

            // Try to find within context first
            $input = $context.find('[data-field-key="' + fieldKey + '"]').find('input, select, textarea').first();

            // Fall back to document-level search
            if (!$input.length) {
                $input = $('[name="' + fieldKey + '"], [name="' + fieldKey + '[]"]').first();
            }

            if (!$input.length) {
                return '';
            }

            // Handle different input types
            if ($input.is(':checkbox')) {
                return $input.is(':checked') ? 1 : 0;
            }

            if ($input.is(':radio')) {
                return $context.find('[name="' + $input.attr('name') + '"]:checked').val() || '';
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
        evaluateCondition: function(actualValue, operator, expected) {
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
        normalizeValue: function(value) {
            if (value === null || value === undefined) {
                return '';
            }

            // Convert string numbers to actual numbers for comparison
            if (typeof value === 'string' && !isNaN(value) && value !== '') {
                return parseFloat(value);
            }

            return value;
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        PostFields.init();
    });

})(jQuery);