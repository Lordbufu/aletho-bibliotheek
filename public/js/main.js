/*  main.js - Aletho Bibliotheek frontend orchestrator. */
import { Popins } from './modules/popins.js';
import { Dropdowns } from './modules/dropdowns.js';
import { TagInput } from './modules/taginput.js';
import { SearchSort } from './modules/searchSort.js';
import { Utility } from './modules/utility.js';
import { Suggestions } from './modules/suggestions.js';

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
        setTimeout(() => { $alert.addClass(alertShowClass); }, 100);
        setTimeout(() => { $alert.removeClass(alertShowClass); }, 3500);
        setTimeout(() => { $alert.remove(); }, 4000);
    }

    // Popins: hash-based open and setup for all popins
    Popins.initFromHash();
    Popins.setup('#boek-add-button', '#add-book-popin', '#close-add-book-popin');
    Popins.setup('#status-periode-button', '#status-period-popin', '#close-status-period-popin');
    Popins.setup('#password-change-button', '#password-reset-popin', '#close-password-reset-popin');
    Popins.setup('.boek-status-button', '#change-book-status-popin', '#close-change-book-status-popin');

    // Setup for all TagInputs: autocomplete/tagging
    const tagInputConfigs = [
        Utility.makeTagConfig('writer'),
        Utility.makeTagConfig('genre'),
        Utility.makeTagConfig('office', { allowCustom: false, maxTags: 1 }),
        Utility.makePopTagConfig('writer'),
        Utility.makePopTagConfig('genre'),
        Utility.makePopTagConfig('office', { allowCustom: false, maxTags: 1 })
    ];
    tagInputConfigs.forEach(config => TagInput.init(config));

    // Search & Sort: event handlers
    SearchSort.initSearch('#search-inp', '#search-options');
    SearchSort.initSort('#sort-options');

    // Search input clear icon: show/hide and clear behavior (jQuery)
    const $searchInput = $('#search-inp');
    const $clearIcon = $('#clear-search-icon');

    // initialize clear icon visibility based on current input value
    $clearIcon.css('display', $searchInput.val() ? 'block' : 'none');

    // show/hide the clear icon as the user types
    $searchInput.on('input', function () {
        $clearIcon.css('display', $(this).val() ? 'block' : 'none');
    });

    // clear the input and trigger input event so SearchSort resets the results
    $clearIcon.on('click', function () {
        $searchInput.val('').trigger('input');
        $clearIcon.hide();
        $searchInput.focus();
    });

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

    // Loaner name suggestion logic for change-book-status-popin
    $('#change-loaner-name').on('input', function() {
        const $input = $(this);
        const query = $input.val().trim();
        
        if (query.length < 2) {
            Suggestions.close();
            return;
        }

        Utility.request({
            url: '/requestLoaners',
            data: { query },
            success: function(list) {
                if (!Array.isArray(list) || list.length === 0) {
                    Suggestions.close();
                    return;
                }

                // Show name suggestions
                Suggestions.show($input, list.map(l => l.name), 'loaner-suggestion');
                Suggestions.bindCloseOnBlur($input);

                // Click handler for suggestion selection
                $(document).off('mousedown.loaner-suggestion').on('mousedown.loaner-suggestion', '.loaner-suggestion', function(e) {
                    e.preventDefault();
                    const name = $(this).text().trim();
                    const selected = list.find(l => l.name === name);
                    if (selected) {
                        $input.val(selected.name);
                        $('#change-loaner-email').val(selected.email || '');
                        $('#change-loaner-location').val(selected.location || '');
                        // Optionally store office id for later use if needed: $input.data('office-id', selected.office_id);
                    }
                    Suggestions.close();
                });
            }
        });
    });

    // Office location suggestion logic for change-book-status-popin
    $('#change-loaner-location').on('input', function() {
        const $input = $(this);
        const query = $input.val().trim().toLowerCase();

        if (query.length < 1) {
            Suggestions.close();
            return;
        }

        Utility.request({
            url: '/bookdata',
            data: { data: 'offices' },
            success: function(list) {
                if (!Array.isArray(list) || list.length === 0) {
                    Suggestions.close();
                    return;
                }

                // Filter office names by query
                const filtered = list.filter(o => o.name.toLowerCase().includes(query));
                Suggestions.show($input, filtered.map(o => o.name), 'office-suggestion');
                Suggestions.bindCloseOnBlur($input);

                // Click handler for suggestion selection
                $(document).off('mousedown.office-suggestion').on('mousedown.office-suggestion', '.office-suggestion', function(e) {
                    e.preventDefault();
                    const name = $(this).text().trim();
                    $input.val(name);
                    Suggestions.close();
                });
            }
        });
    });

    // Trigger change on load to pre-fill with the first status
    $('#status-type').trigger('change');

    // W.I.P. Testing functions
    $('.status-dot').on('click', testLights);
});

$(document).ajaxSuccess(function (event, xhr, settings, data) {
    console.log('AJAX SUCCESS:', settings.url, data);
});

$(document).ajaxError(function (event, xhr, settings, error) {
    console.error('AJAX ERROR:', settings.url, xhr.responseText);
});

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