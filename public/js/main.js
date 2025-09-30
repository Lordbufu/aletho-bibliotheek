/**
 * main.js - Aletho Bibliotheek frontend orchestrator
 * Loads and wires up all modules, event handlers, and page-level logic.
 * All feature logic is delegated to modules for maintainability.
 */

// Import modules first (using this to reduce header clutter):
import { Popins } from './modules/popins.js';
import { Dropdowns } from './modules/dropdowns.js';
import { Writers } from './modules/writers.js';
import { SearchSort } from './modules/searchSort.js';
import { Utility } from './modules/utility.js';

// Document ready: wire up modules and event handlers
$(function() {
    // Popins: hash-based open and setup for all popins
    Popins.initFromHash();
    Popins.setup('#boek-add-button', '#add-book-popin', '#close-add-book-popin');
    Popins.setup('#status-periode-button', '#status-period-popin', '#close-status-period-popin');
    Popins.setup('#password-change-button', '#password-reset-popin', '#close-password-reset-popin');
    Popins.setup('#boek-status-button', '#change-book-status-popin', '#close-change-book-status-popin');

    // Writers: autocomplete/tagging
    Writers.init('.writer-input', '.writer-tags-container');

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

        // Book details edit: make field editable and store original value
        const $detEditBtn = $(event.target).closest('.extra-button-style');
        if ($detEditBtn.length) {
            const selector = $detEditBtn.data('swapTargets');
            const $field = $(selector);
            if ($field.prop('disabled')) {
                $field.prop('disabled', false)
                    .addClass('field-editable')
                    .focus();
                if ($field.hasClass('writer-input')) {
                    Utility.markFieldChanged($field);
                    const $container = Writers.getTagsContainer($field);
                    const existing = $field.val();
                    if (existing) {
                        existing.split(',')
                            .map(name => name.trim())
                            .forEach(name => {
                                if (name) {
                                    Writers.addTag(name, $field, $container)
                                }
                            });
                    }
                    const origValues = Writers.getValuesFromContainer($container);
                    $field.data('originalValue', origValues.join(','));
                } else {
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

    // Writers: blur event handler to revert input if unchanged
    $(document).on('blur', 'input.field-editable, select.field-editable', function() {
        const $field = $(this);
        setTimeout(() => {
            if (Writers.isRemoving()) {
                return;
            }
            const original = $field.data('originalValue');
            let current;
            if ($field.hasClass('writer-input')) {
                const $container = Writers.getTagsContainer($field);
                const currentValues = Writers.getValuesFromContainer($container);
                current = currentValues.join(',');
            } else {
                current = $field.val();
            }
            if (current === original) {
                if ($field.hasClass('writer-input')) {
                    const $container = Writers.getTagsContainer($field);
                    const names = Writers.getValuesFromContainer($container);
                    $field.val(names.join(', '));
                    $container.empty();
                }
                $field.prop('disabled', true)
                    .removeClass('field-editable field-changed')
                    .removeData('originalValue');
                Utility.clearFieldChanged($field);
            }
        }, 150);
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