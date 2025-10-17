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
    const CONSTANTS = {
        SELECTORS: {
            globalAlert: '.aletho-global-success, .aletho-global-failure',
            hamburgerDropdown: '#customHamburgerDropdown',
            searchDropdown: '#customSearchDropdown',
            hamburgerButton: '#hamburgerButton',
            alethoDropdownBody: '.aletho-dropdown-body.show',
            alethoItemDropdown: '.collapse.aletho-item-dropdown.show',
            searchButton: '#searchButton',
            searchInp: '#search-inp',
            searchOptions: '#search-options',
            sortOptions: '#sort-options',
            bookDetailsButton: '[id^="itemButton-"]',
            bookAddButton: '#boek-add-button',
            bookNameInput: '[id^="book-name-"]',
            editButton: '.extra-button-style',
            editableField: 'input.field-editable, select.field-editable',
            statusType: '#status-type',
            periodeLength: '#periode-length',
            reminderDay: '#reminder-day',
            overdueDay: '#overdue-day',
            bookStatusButton: '#boek-status-button',
            addBookPopin: '#add-book-popin',
            closeAddBookPopin: '#close-add-book-popin',
            statusPeriodeButton: '#status-periode-button',
            statusPeriodPopin: '#status-period-popin',
            closeStatusPeriodPopin: '#close-status-period-popin',
            changeBookStatusPopin: '#change-book-status-popin',
            closeChangeBookStatusPopin: '#close-change-book-status-popin',
            statusDot: '.status-dot',
            passwordChangeButton: '#password-change-button',
            passwordResetPopin: '#password-reset-popin',
            closePasswordResetPopin: '#close-password-reset-popin',
        },
        CLASSES: {
            alertShow: 'aletho-global-show',
            fieldEditable: 'field-editable',
            fieldChanged: 'field-changed'
        }
    };

    const DROPDOWNS_TO_CLOSE = [CONSTANTS.SELECTORS.hamburgerDropdown, CONSTANTS.SELECTORS.searchDropdown];

    // User feedback notifications.
    const $alert = $(CONSTANTS.SELECTORS.globalAlert);
    if ($alert.length) {
        setTimeout(() => {
            $alert.addClass(CONSTANTS.CLASSES.alertShow);
        }, 100); // slight delay for transition

        setTimeout(() => {
            $alert.removeClass(CONSTANTS.CLASSES.alertShow);
        }, 3500); // show for 3.5 seconds

        setTimeout(() => {
            $alert.remove();
        }, 4000); // remove from DOM after hiding
    }

    // Popins: hash-based open and setup for all popins
    Popins.initFromHash();
    Popins.setup(CONSTANTS.SELECTORS.bookAddButton, CONSTANTS.SELECTORS.addBookPopin, CONSTANTS.SELECTORS.closeAddBookPopin);
    Popins.setup(CONSTANTS.SELECTORS.statusPeriodeButton, CONSTANTS.SELECTORS.statusPeriodPopin, CONSTANTS.SELECTORS.closeStatusPeriodPopin);
    Popins.setup(CONSTANTS.SELECTORS.passwordChangeButton, CONSTANTS.SELECTORS.passwordResetPopin, CONSTANTS.SELECTORS.closePasswordResetPopin);
    Popins.setup(CONSTANTS.SELECTORS.bookStatusButton, CONSTANTS.SELECTORS.changeBookStatusPopin, CONSTANTS.SELECTORS.closeChangeBookStatusPopin);

    // Setup for all TagInputs: autocomplete/tagging
    const tagInputConfigs = [
        {
            inputSelector: '.writer-input',
            containerSelector: '.writer-tags-container',
            endpoint: '/bookdata?data=writers',
            tagClass: 'writer-tag',
            suggestionClass: 'writer-suggestion',
            hiddenInputName: 'book_writers[]',
            maxTags: 3
        },
        {
            inputSelector: '.genre-input',
            containerSelector: '.genre-tags-container',
            endpoint: '/bookdata?data=genres',
            tagClass: 'genre-tag',
            suggestionClass: 'genre-suggestion',
            hiddenInputName: 'book_genres[]',
            maxTags: 3
        },
        {
            inputSelector: '.office-input',
            containerSelector: '.office-tags-container',
            endpoint: '/bookdata?data=offices',
            tagClass: 'office-tag',
            suggestionClass: 'office-suggestion',
            hiddenInputName: 'book_offices[]',
            maxTags: 1
        },
        {
            inputSelector: '.writer-input-pop',
            containerSelector: '.add-writer-tags-container',
            endpoint: '/bookdata?data=writers',
            tagClass: 'writer-tag',
            suggestionClass: 'writer-suggestion',
            hiddenInputName: 'book_writers[]',
            maxTags: 3
        },
        {
            inputSelector: '.genre-input-pop',
            containerSelector: '.add-genre-tags-container',
            endpoint: '/bookdata?data=genres',
            tagClass: 'genre-tag',
            suggestionClass: 'genre-suggestion',
            hiddenInputName: 'book_genres[]',
            maxTags: 3
        },
        {
            inputSelector: '.office-input-pop',
            containerSelector: '.add-office-tags-container',
            endpoint: '/bookdata?data=offices',
            tagClass: 'office-tag',
            suggestionClass: 'office-suggestion',
            hiddenInputName: 'book_offices[]',
            maxTags: 1
        }
    ];
    tagInputConfigs.forEach(config => TagInput.init(config));

    // Search & Sort: event handlers
    SearchSort.initSearch(CONSTANTS.SELECTORS.searchInp, CONSTANTS.SELECTORS.searchOptions);
    SearchSort.initSort(CONSTANTS.SELECTORS.sortOptions);

    // Global click: close popins and dropdowns when clicking outside
    $(document).on('click', function(event) {
        // Popins: close on outside click
        Popins.handleOutsideClick(event, Dropdowns.close);

        // Dropdowns: close when focus is lost
        const $targetDropdown = $(event.target).closest(DROPDOWNS_TO_CLOSE.join(','));
        if ($targetDropdown.length === 0) {
            Dropdowns.close(DROPDOWNS_TO_CLOSE);
        }
    });

    // Book details: close other dropdowns when details opened
    $(document).on('click', CONSTANTS.SELECTORS.bookDetailsButton, function(event) {
        event.stopPropagation(); // Prevent global click handler from firing
        const $detailsBtn = $(this);
        const targetId = $detailsBtn.attr('data-bs-target');

        if ($(CONSTANTS.SELECTORS.alethoDropdownBody).length) {
            Dropdowns.close(DROPDOWNS_TO_CLOSE);
        }

        // Close other open book details sections for accordion behavior
        $(CONSTANTS.SELECTORS.alethoItemDropdown).each(function() {
            if ('#' + this.id !== targetId) {
                bootstrap.Collapse.getOrCreateInstance(this, { toggle: false }).hide();
            }
        });
    });

    // Book details edit: make field editable and convert input to tags for all taggable fields
    $(document).on('click', CONSTANTS.SELECTORS.editButton, function(event) {
        event.stopPropagation();
        const selector = $(this).data('swapTargets');
        const $field = $(selector);

        if (!$field.prop('disabled')) return;

        const config = Utility.getFieldConfig($field);

        if (config.isTaggable) {
            Utility.markFieldChanged($field);
            const $container = TagInput.getTagsContainer($field, config.containerSelector);
            TagInput.restoreTagsFromInput($field, $container, config.tagClass, config.hiddenInputName);
        } else {
            Utility.markFieldChanged($field);
            $field.data('originalValue', $field.val());
            $field.prop('disabled', false);
        }

        $field.prop('disabled', false)
            .addClass(CONSTANTS.CLASSES.fieldEditable);
    });

    // Popins: status period popin input fill (could be refactored into Popins)
    $(CONSTANTS.SELECTORS.statusType).on('change', function() {
        const $selected = $(this).find('option:selected');
        $(CONSTANTS.SELECTORS.periodeLength).val($selected.data('periode_length'));
        $(CONSTANTS.SELECTORS.reminderDay).val($selected.data('reminder_day'));
        $(CONSTANTS.SELECTORS.overdueDay).val($selected.data('overdue_day'));
    });

    // Dropdowns: button click handlers
    $(CONSTANTS.SELECTORS.hamburgerButton).on('click', function(e) {
        e.stopPropagation();
        Dropdowns.toggle([CONSTANTS.SELECTORS.hamburgerDropdown, CONSTANTS.SELECTORS.searchDropdown]);
    });
    
    $(CONSTANTS.SELECTORS.searchButton).on('click', function(e) {
        e.stopPropagation();
        Dropdowns.toggle([CONSTANTS.SELECTORS.searchDropdown, CONSTANTS.SELECTORS.hamburgerDropdown]);
    });

    // Book details: input/change listener for editable fields
    $(document).on('input change', CONSTANTS.SELECTORS.editableField, function() {
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
    $(document).on('blur', CONSTANTS.SELECTORS.editableField, function() {
        const $field = $(this);
        setTimeout(() => {
            if (TagInput.isRemoving && TagInput.isRemoving()) {
                return;
            }

            const original = $field.data('originalValue');
            const config = Utility.getFieldConfig($field);
            let current;

            if (config.isTaggable) {
                const $container = TagInput.getTagsContainer($field, config.containerSelector);
                const currentValues = TagInput.getValuesFromContainer($container, config.hiddenInputName);
                current = currentValues.join(',');

                if (current === original) {
                    const names = TagInput.getValuesFromContainer($container, config.hiddenInputName);
                    $field.val(names.join(', '));
                    $container.empty();
                    $field.prop('disabled', true)
                        .removeClass(`${CONSTANTS.CLASSES.fieldEditable} ${CONSTANTS.CLASSES.fieldChanged}`)
                        .removeData('originalValue');
                    Utility.clearFieldChanged($field);
                }
            } else {
                current = $field.val();
                if (current === original) {
                    $field.prop('disabled', true)
                        .removeClass(`${CONSTANTS.CLASSES.fieldEditable} ${CONSTANTS.CLASSES.fieldChanged}`)
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
    $(CONSTANTS.SELECTORS.bookNameInput).on('keydown', function(e) {
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
    $(CONSTANTS.SELECTORS.statusType).trigger('change');

    // W.I.P. Testing functions
    $(CONSTANTS.SELECTORS.statusDot).on('click', testLights);
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

// function inputCheck(e) {
//     let check;
//     if(e.target.value !== '') {
//         switch(e.target.name) {
//             case 'userName':
//                 check = stringChecker('name', e.target.value);
//                 break;
//             case 'userPw':
//                 check = stringChecker('pass', e.target.value);
//                 break;
//         }
//         if(check !== "" && check !== undefined && check) {
//             if(e.target.classList.contains('is-invalid')) {
//                 e.target.classList.remove('is-invalid');
//                 e.target.style.outline = '';
//             }
//             return e.target.classList.add('is-valid');
//         }
//         if(check !== "" && check !== undefined && !check) {
//             if(e.target.classList.contains('is-valid')) {
//                 e.target.classList.remove('is-valid');
//                 e.target.style.outline = '';
//             }
//             return e.target.classList.add('is-invalid');
//         }
//     }
//     if(e.target.classList.contains('is-invalid')) {
//         e.target.style.outline = '';
//         return e.target.classList.remove('is-invalid');
//     }
//     if(e.target.classList.contains('is-valid')) {
//         e.target.style.outline = '';
//         return e.target.classList.remove('is-valid');
//     }
// }

// function stringChecker(type, value) {
//     switch(type) {
//         case 'name':
//             return value.length >= 7;
//         case 'pass':
//             return value.length >= 9;
//         default:
//             return false;
//     }
// }
