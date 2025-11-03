/**
 * main.js - Aletho Bibliotheek frontend orchestrator
 * Loads and wires up all modules, event handlers, and page-level logic.
 * All feature logic is delegated to modules for maintainability.
 */

import { Popins } from './modules/popins.js';
import { Dropdowns } from './modules/dropdowns.js';
import { TagInput } from './modules/taginput.js';
import { SearchSort } from './modules/searchSort.js';
import { Utility } from './modules/utility.js';

// Document ready: wire up modules and event handlers
$(function() {
    // Inline selectors for single-use, variables for repeated selectors
    const alertShowClass = 'aletho-global-show';
    const fieldEditableClass = 'field-editable';
    const fieldChangedClass = 'field-changed';
    const hamburgerDropdown = '#customHamburgerDropdown';
    const searchDropdown = '#customSearchDropdown';
    const DROPDOWNS_TO_CLOSE = [hamburgerDropdown, searchDropdown];

    // User feedback notifications.
    const $alert = $('.aletho-global-success, .aletho-global-failure');
    if ($alert.length) {
        setTimeout(() => {
            $alert.addClass(alertShowClass);
        }, 100); // slight delay for transition

        setTimeout(() => {
            $alert.removeClass(alertShowClass);
        }, 3500); // show for 3.5 seconds

        setTimeout(() => {
            $alert.remove();
        }, 4000); // remove from DOM after hiding
    }

    // Popins: hash-based open and setup for all popins
    Popins.initFromHash();
    Popins.setup('#boek-add-button', '#add-book-popin', '#close-add-book-popin');
    Popins.setup('#status-periode-button', '#status-period-popin', '#close-status-period-popin');
    Popins.setup('#password-change-button', '#password-reset-popin', '#close-password-reset-popin');

    Popins.setup(
        '.boek-status-button',
        '#change-book-status-popin',
        '#close-change-book-status-popin',
        function (popinId, context) {
            const bookId = context.bookId;
            $('#change-book-id').val(bookId);

            $.getJSON('/requestStatus', function (statuses) {
                const $select = $('#change-status-type');
                $select.empty().append('<option disabled selected hidden>Selecteer een status</option>');
                statuses.forEach(status => {
                    $select.append(`<option value="${status.id}">${status.type}</option>`);
                });
            });
        }
    );

    // Setup for all TagInputs: autocomplete/tagging
    const tagInputConfigs = [
        makeTagConfig('writer'),
        makeTagConfig('genre'),
        makeTagConfig('office', { allowCustom: false, maxTags: 1 }),
        makePopTagConfig('writer'),
        makePopTagConfig('genre'),
        makePopTagConfig('office', { allowCustom: false, maxTags: 1 })
    ];
    tagInputConfigs.forEach(config => TagInput.init(config));

    // Search & Sort: event handlers
    SearchSort.initSearch('#search-inp', '#search-options');
    SearchSort.initSort('#sort-options');

    // Global click: close popins and dropdowns when clicking outside
    $(document).on('click', function(event) {
        Popins.handleOutsideClick(event, Dropdowns.close);

        const $targetDropdown = $(event.target).closest(DROPDOWNS_TO_CLOSE.join(','));
        if ($targetDropdown.length === 0) {
            Dropdowns.close(DROPDOWNS_TO_CLOSE);
        }
    });

    // Book details: close other dropdowns when details opened
    $(document).on('click', '[id^="itemButton-"]', function(event) {
        event.stopPropagation();
        const $detailsBtn = $(this);
        const targetId = $detailsBtn.attr('data-bs-target');

        if ($('.aletho-dropdown-body.show').length) {
            Dropdowns.close(DROPDOWNS_TO_CLOSE);
        }

        $('.collapse.aletho-item-dropdown.show').each(function() {
            if ('#' + this.id !== targetId) {
                bootstrap.Collapse.getOrCreateInstance(this, { toggle: false }).hide();
            }
        });
    });

    // Book details edit: make field editable and convert input to tags for all taggable fields
    $(document).on('click', '.extra-button-style', function(event) {
        event.stopPropagation();
        const selector = $(this).data('swapTargets');
        const $field = $(selector);

        if (!$field.prop('disabled')) return;

        const config = Utility.getFieldConfig($field);

        if (config.isTaggable) {
            Utility.markFieldChanged($field);
            const $container = TagInput.getTagsContainer($field, config.containerSelector);
            TagInput.restoreTagsFromInput($field, $container, config.tagClass, config.hiddenInputName);

            if (!$field.data('originalValue')) {
                $field.data('originalValue', '');
            }
        } else {
            Utility.markFieldChanged($field);
            $field.data('originalValue', $field.val());
            $field.prop('disabled', false);
        }

        $field.prop('disabled', false)
            .addClass(fieldEditableClass);

        setTimeout(() => $field.focus(), 0);
    });

    // Popins: status period popin input fill
    $('#status-type').on('change', function() {
        const $selected = $(this).find('option:selected');
        $('#periode-length').val($selected.data('periode_length'));
        $('#reminder-day').val($selected.data('reminder_day'));
        $('#overdue-day').val($selected.data('overdue_day'));
    });

    // Dropdowns: button click handlers
    $('#hamburgerButton').on('click', function(e) {
        e.stopPropagation();
        Dropdowns.toggle([hamburgerDropdown, searchDropdown]);
    });
    
    $('#searchButton').on('click', function(e) {
        e.stopPropagation();
        Dropdowns.toggle([searchDropdown, hamburgerDropdown]);
    });

    // Book details: input/change listener for editable fields
    $(document).on('input change', 'input.field-editable, select.field-editable', function() {
        const $field = $(this);
        const original = $field.data('originalValue');
        const current = $field.val();
        if (current !== original) {
            Utility.markFieldChanged($field);
        } else {
            Utility.clearFieldChanged($field);
        }
    });

    // Blur event handler to revert input if unchanged for all taggable fields
    $(document).on('blur', 'input.field-editable, select.field-editable', function() {
        const $field = $(this);

        setTimeout(() => {
            const $group = $field.closest('.input-group');
            if ($group.find(':focus').length) {
                return;
            }

            const config = Utility.getFieldConfig($field);
            const original = $field.data('originalValue');
            let current;

            if (config.isTaggable) {
                const $container = TagInput.getTagsContainer($field, config.containerSelector);
                const currentValues = TagInput.getValuesFromContainer($container, config.hiddenInputName);
                current = Utility.normalizeValues(currentValues);

                if (current === original) {
                    $field.val(currentValues.join(', '));
                    $container.empty();
                    $field.prop('disabled', true)
                        .removeClass(`${fieldEditableClass} ${fieldChangedClass}`)
                        .removeData('originalValue');
                    Utility.clearFieldChanged($field);
                }
            } else {
                current = $field.val();
                if (current === original) {
                    $field.prop('disabled', true)
                        .removeClass(`${fieldEditableClass} ${fieldChangedClass}`)
                        .removeData('originalValue');
                    Utility.clearFieldChanged($field);
                }
            }
        }, 200);
    });

    // Book-name-[$id] input, keydown event
    $('[id^="book-name-"]').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const $input = $(this);
            const value = $input.val().trim();
            if (value) { $input.blur(); }
        }
    });

    // Add book-id to a shared delete form, and submit said form.
    $(document).on('click', '.delete-book-btn', function() {
        const bookId = $(this).data('book-id');
        $('#delete-book-id').val(bookId);
        $('#shared-delete-form').trigger('submit');
    });

    // Trigger change on load to pre-fill with the first status
    $('#status-type').trigger('change');

    // W.I.P. Testing functions
    $('.status-dot').on('click', testLights);
});


function makeTagConfig(type, opts = {}) {
    return {
        inputSelector: `.${type}-input`,
        containerSelector: `.${type}-tags-container`,
        endpoint: `/bookdata?data=${type}s`,
        tagClass: `${type}-tag`,
        suggestionClass: `${type}-suggestion`,
        hiddenInputName: `book_${type}s[]`,
        maxTags: 3,
        allowCustom: true,
        ...opts
    };
}

function makePopTagConfig(type, opts = {}) {
    return {
        inputSelector: `.${type}-input-pop`,
        containerSelector: `.add-${type}-tags-container`,
        endpoint: `/bookdata?data=${type}s`,
        tagClass: `${type}-tag`,
        suggestionClass: `${type}-suggestion-pop`,
        hiddenInputName: `book_${type}s[]`,
        maxTags: 3,
        allowCustom: true,
        ...opts
    };
}

// W.I.P. helper, to review the basic status light colors via a simple click to change/rotate.
function testLights(e) {
    let $el = $(e.target);

    if ($el.hasClass('statusOne')) {
        $el.removeClass('statusOne').addClass('statusTwo');
        return;
    }

    if ($el.hasClass('statusTwo')) {
        $el.removeClass('statusTwo').addClass('statusThree');
        return;
    }

    if ($el.hasClass('statusThree')) {
        $el.removeClass('statusThree').addClass('statusFour');
        return;
    }

    if ($el.hasClass('statusFour')) {
        $el.removeClass('statusFour').addClass('statusOne');
        return;
    }
}