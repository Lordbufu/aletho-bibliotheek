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
    // Test code for user feedback notifications.
    const $alert = $('.alert-global-success, .alert-global-failure');
    if ($alert.length) {
        setTimeout(() => {
            $alert.addClass('alert-global-show');
        }, 100); // slight delay for transition

        setTimeout(() => {
            $alert.removeClass('alert-global-show');
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
    Popins.setup('#boek-status-button', '#change-book-status-popin', '#close-change-book-status-popin');

    // Writers: autocomplete/tagging
    TagInput.init({
        inputSelector: '.writer-input',
        containerSelector: '.writer-tags-container',
        endpoint: '/bookdata?data=writers',
        tagClass: 'writer-tag',
        suggestionClass: 'writer-suggestion',
        hiddenInputName: 'book_writers[]'
    });

    // Genres: autocomplete/tagging
    TagInput.init({
        inputSelector: '.genre-input',
        containerSelector: '.genre-tags-container',
        endpoint: '/bookdata?data=genres',
        tagClass: 'genre-tag',
        suggestionClass: 'genre-suggestion',
        hiddenInputName: 'book_genres[]'
    });

    // Offices: autocomplete/tagging
    TagInput.init({
        inputSelector: '.office-input',
        containerSelector: '.office-tags-container',
        endpoint: '/bookdata?data=offices',
        tagClass: 'office-tag',
        maxTags: 1,
        suggestionClass: 'office-suggestion',
        hiddenInputName: 'book_offices[]'
    });

    // Initialize writer input in add-book popin
    TagInput.init({
        inputSelector: '#book-writer-add',
        containerSelector: '.add-writer-tags-container',
        endpoint: '/bookdata?data=writers',
        tagClass: 'writer-tag',
        hiddenInputName: 'writers[]',
        suggestionClass: 'writer-suggestion',
        maxTags: 10
    });

    // Later you can do the same for genres and offices:
    TagInput.init({
        inputSelector: '#book-genre-add',
        containerSelector: '.add-genre-tags-container',
        endpoint: '/bookdata?data=genres',
        tagClass: 'genre-tag',
        hiddenInputName: 'genres[]',
        suggestionClass: 'genre-suggestion',
        maxTags: 5
    });

    // For offices, once you replace <select> with an input:
    TagInput.init({
        inputSelector: '#book-office-add',
        containerSelector: '.add-office-tags-container',
        endpoint: '/bookdata?data=offices',
        tagClass: 'office-tag',
        hiddenInputName: 'offices[]',
        suggestionClass: 'office-suggestion',
        maxTags: 3
    });

    // Search & Sort: event handlers
    SearchSort.initSearch('#search-inp', '#search-options');
    SearchSort.initSort('#sort-options');

    // Global click: popin outside click, dropdown close, book details edit
    $(document).on('click', function(event) {
        // Popins: close on outside click
        Popins.handleOutsideClick(event, Dropdowns.close);

        // Dropdowns: close when focus is lost
        const $targetDropdown = $(event.target).closest('#customHamburgerDropdown, #customSearchDropdown');
        if ($targetDropdown.length === 0) {
            Dropdowns.close(['#customHamburgerDropdown', '#customSearchDropdown']);
        }

        // Book details: close other dropdowns when details opened
        const $detailsBtn = $(event.target).closest('[id^="itemButton-"]');
        if ($detailsBtn.length) {
            const targetId = $detailsBtn.attr('data-bs-target');
            event.stopPropagation();
            if ($('.aletho-dropdown-body.show').length) {
                Dropdowns.close(['#customHamburgerDropdown', '#customSearchDropdown']);
            }
            $('.collapse.aletho-item-dropdown.show').each(function() {
                if ('#' + $detailsBtn.attr('id') !== targetId) {
                    bootstrap.Collapse.getOrCreateInstance(this, {toggle: false}).hide();
                }
            });
        }

        // Book details edit: make field editable and convert input to tags for all taggable fields
        const $detEditBtn = $(event.target).closest('.extra-button-style');
        if ($detEditBtn.length) {
            const selector = $detEditBtn.data('swapTargets');
            const $field = $(selector);
            if ($field.prop('disabled')) {
                $field.prop('disabled', false)
                    .addClass('field-editable')
                    .focus();
                // Writers
                if ($field.hasClass('writer-input')) {
                    Utility.markFieldChanged($field);
                    const $container = TagInput.getTagsContainer($field, 'writer-tags-container');
                    TagInput.restoreTagsFromInput($field, $container, 'writer-tag', 'book_writers[]');
                }
                // Genres
                else if ($field.hasClass('genre-input')) {
                    Utility.markFieldChanged($field);
                    const $container = TagInput.getTagsContainer($field, 'genre-tags-container');
                    TagInput.restoreTagsFromInput($field, $container, 'genre-tag', 'book_genres[]');
                }
                // Offices
                else if ($field.hasClass('office-input')) {
                    Utility.markFieldChanged($field);
                    const $container = TagInput.getTagsContainer($field, 'office-tags-container');
                    TagInput.restoreTagsFromInput($field, $container, 'office-tag', 'book_offices[]');
                }
                else {
                    $field.data('originalValue', $field.val());
                }
            }
        }
    });

    // Popins: status period popin input fill (could be refactored into Popins)
    $('#status-type').on('change', function() {
        const $selected = $(this).find('option:selected');
        $('#periode-length').val($selected.data('periode_length'));
        $('#reminder-day').val($selected.data('reminder_day'));
        $('#overdue-day').val($selected.data('overdue_day'));
    });

    // Dropdowns: button click handlers
    $('#hamburgerButton').on('click', function(e) {
        e.stopPropagation();
        Dropdowns.toggle(['#customHamburgerDropdown', '#customSearchDropdown']);
    });
    $('#searchButton').on('click', function(e) {
        e.stopPropagation();
        Dropdowns.toggle(['#customSearchDropdown', '#customHamburgerDropdown']);
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
            if (TagInput.isRemoving()) {
                return;
            }
            const original = $field.data('originalValue');
            let current;
            // Writers
            if ($field.hasClass('writer-input')) {
                const $container = TagInput.getTagsContainer($field, 'writer-tags-container');
                const currentValues = TagInput.getValuesFromContainer($container, 'book_writers[]');
                current = currentValues.join(',');
                if (current === original) {
                    const names = TagInput.getValuesFromContainer($container, 'book_writers[]');
                    $field.val(names.join(', '));
                    $container.empty();
                    $field.prop('disabled', true)
                        .removeClass('field-editable field-changed')
                        .removeData('originalValue');
                    Utility.clearFieldChanged($field);
                }
            }
            // Genres
            else if ($field.hasClass('genre-input')) {
                const $container = TagInput.getTagsContainer($field, 'genre-tags-container');
                const currentValues = TagInput.getValuesFromContainer($container, 'book_genres[]');
                current = currentValues.join(',');
                if (current === original) {
                    const names = TagInput.getValuesFromContainer($container, 'book_genres[]');
                    $field.val(names.join(', '));
                    $container.empty();
                    $field.prop('disabled', true)
                        .removeClass('field-editable field-changed')
                        .removeData('originalValue');
                    Utility.clearFieldChanged($field);
                }
            }
            // Offices
            else if ($field.hasClass('office-input')) {
                const $container = TagInput.getTagsContainer($field, 'office-tags-container');
                const currentValues = TagInput.getValuesFromContainer($container, 'book_offices[]');
                current = currentValues.join(',');
                if (current === original) {
                    const names = TagInput.getValuesFromContainer($container, 'book_offices[]');
                    $field.val(names.join(', '));
                    $container.empty();
                    $field.prop('disabled', true)
                        .removeClass('field-editable field-changed')
                        .removeData('originalValue');
                    Utility.clearFieldChanged($field);
                }
            }
            // Default
            else {
                current = $field.val();
                if (current === original) {
                    $field.prop('disabled', true)
                        .removeClass('field-editable field-changed')
                        .removeData('originalValue');
                    Utility.clearFieldChanged($field);
                }
            }
        }, 150);
    });

    /*  Book-name-[$id] input, keydown event:
     *      Stopping form submit when `Enter` is pressed, so its inline with other inputs.
     *      Normalizing and trimming the input, and triggering `on blur` or `on change` events.
     */
    $('[id^="book-name-"]').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const $input = $(this);
            const value = $input.val().trim();
            if (value) { $input.blur(); }
        }
    });

    // Trigger change on load to pre-fill with the first status
    $('#status-type').trigger('change');

    // W.I.P. Testing functions
    $('#login-name, #login-passw').on('input', inputCheck);
    $('.status-dot').on('click', testLights);
});

// W.I.P. helpers (could be moved to Utility or modules)
function inputCheck(e) {
    let check;
    if(e.target.value !== '') {
        switch(e.target.name) {
            case 'userName':
                check = stringChecker('name', e.target.value);
                break;
            case 'userPw':
                check = stringChecker('pass', e.target.value);
                break;
        }
        if(check !== "" && check !== undefined && check) {
            if(e.target.classList.contains('is-invalid')) {
                e.target.classList.remove('is-invalid');
                e.target.style.outline = '';
            }
            return e.target.classList.add('is-valid');
        }
        if(check !== "" && check !== undefined && !check) {
            if(e.target.classList.contains('is-valid')) {
                e.target.classList.remove('is-valid');
                e.target.style.outline = '';
            }
            return e.target.classList.add('is-invalid');
        }
    }
    if(e.target.classList.contains('is-invalid')) {
        e.target.style.outline = '';
        return e.target.classList.remove('is-invalid');
    }
    if(e.target.classList.contains('is-valid')) {
        e.target.style.outline = '';
        return e.target.classList.remove('is-valid');
    }
}

function stringChecker(type, value) {
    switch(type) {
        case 'name':
            return value.length >= 7;
        case 'pass':
            return value.length >= 9;
        default:
            return false;
    }
}

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