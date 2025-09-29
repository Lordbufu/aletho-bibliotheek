/** This file is part of the Aletho Bibliotheek project.
 *      Loaded modules via the 'header.php' template:
 *          @Popins     => ..\modules\popin.js
 *              All popin related functions have been offloaded to this module, loaded as a Popins variable.
 *          @Dropdowns  => ..\modules\dorpdowns.js
 *              All dropdown related function have been offloaded to this module, loaded as a Dropdowns variable.
 *          @Writers    => ..\modules\writers.js
 *              All writer input related functions, that deal with converting and reseting its input/displayed tags.
 *          @SearchSort => ..\modules\searchSort.js
 *              All searching and sorting related function and events.
 */

// Import modules first (using this to reduce header clutter):
import { Popins } from './modules/popins.js';
import { Dropdowns } from './modules/dropdowns.js';
import { Writers } from './modules/writers.js';
import { SearchSort} from './modules/searchSort.js';

/* Document ready loop */
$(function() {
    // -- Popins: The `popin` array selector map
    const popinSel = [ '#add-book-popin', '#status-period-popin', '#password-reset-popin', '#change-book-status-popin' ];
    // -- Popins: Check if a hash was set via PhP, to route to a `popin`.
    Popins.initFromHash();
    // -- Popins: Add the event handles for all `popins` via a helper function.
    Popins.setup('#boek-add-button', '#add-book-popin', '#close-add-book-popin');
    Popins.setup('#status-periode-button', '#status-period-popin', '#close-status-period-popin');
    Popins.setup('#password-change-button', '#password-reset-popin', '#close-password-reset-popin');
    Popins.setup('#boek-status-button', '#change-book-status-popin', '#close-change-book-status-popin');
    // -- Writers: Init autocomplete/tagging
    Writers.init('.writer-input', '.writer-tags-container');
    // -- Search: Init the search events handlers
    SearchSort.initSearch("#search-inp", "#search-options");
    // -- Sort: Massive TODO here, there is no sort logic atm :P
    SearchSort.initSort("#sort-options", (value) => {
        // console.log("Sorting by:", value);
        // Insert your sorting logic here, e.g. reordering DOM elements or fetching new data
    });

    /* Global on-click event for the entire document. */
    $(document).on('click', function(event) {
        const $detailsBtn   = $(event.target).closest('[id^="itemButton-"]');
        const $detEditBtn   = $(event.target).closest('.extra-button-style');

        // -- Popins: Setup popin handler to close when clicking outside the element events.
        Popins.handleOutsideClick(event, popinSel, Dropdowns.close);

        // -- Dropdowns: Close dropdowns when focus is lost
        const $targetDropdown = $(event.target).closest('#customHamburgerDropdown, #customSearchDropdown');
        if ($targetDropdown.length === 0) {
            Dropdowns.close(['#customHamburgerDropdown', '#customSearchDropdown']);
        }

        // --- Dropdowns: close all other dropdowns when book details is opened.
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

        // --- Book details edit button: Make associated field editable, and store its original value.
        if ($detEditBtn.length) {
            const selector      = $detEditBtn.data('swapTargets');
            const $field        = $(selector);

            if ($field.prop('disabled')) {
                $field.prop('disabled', false)
                    .addClass('field-editable')
                    .focus();

                if ($field.hasClass('writer-input')) {
                    markFieldChanged($field);    
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

    // -- Popins: Event handlers for the change-status-period popin, filling in input data after a status has been selected.
    $('#status-type').on('change', function() {
        const $selected = $(this).find('option:selected');
        $('#periode-length').val($selected.data('periode_length'));
        $('#reminder-day').val($selected.data('reminder_day'));
        $('#overdue-day').val($selected.data('overdue_day'));
    });

    // -- Dropdowns: Button click handlers.
    $('#hamburgerButton').on('click', function(e) {
        e.stopPropagation();
        Dropdowns.toggle(['#customHamburgerDropdown', '#customSearchDropdown']);
    });
    $('#searchButton').on('click', function(e) {
        e.stopPropagation();
        Dropdowns.toggle(['#customSearchDropdown', '#customHamburgerDropdown']);
    });

    // --- Book details input and select logic: Input/change listener for editable fields, tracks changes, applies 'field-changed' class, and enables/disables the save button.
    $(document).on('input change', 'input.field-editable, select.field-editable', function() {
        const $field = $(this);
        const original = $field.data('originalValue');
        const current = $field.val();

        if (current !== original) {
            markFieldChanged($field);
        } else {
            clearFieldChanged($field);
        }
    });

    // -- Writers: On blur event handler, to revert the input back to normal if focus is lost and nothing has changed.
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

                clearFieldChanged($field);
            }
        }, 150);
    });

    // -- Search: Input handler.
    $('#search-inp').on('input', function () {
        const query  = $(this).val().toLowerCase().trim();
        const method = $('#search-options').val(); // title | writer | genre

        $('.aletho-item-container').each(function () {
            const $card = $(this);
            let textToSearch = '';

            switch (method) {
                case 'writer':
                    textToSearch = $card.find('input[name="book_writer"]').val() || '';
                    break;
                case 'genre':
                    textToSearch = $card.find('select[name="genre_id"] option:selected').text() || '';
                    break;
                case 'title':
                default:
                    textToSearch = $card.find('.mn-main-col').text() || '';
            }

            if (textToSearch.toLowerCase().includes(query)) {
                $card.show();
            } else {
                $card.hide();
            }
        });
    });

    // -- Search: Options change handler.
    $('#search-options').on('change', function () {
        const method = $(this).val();
        const labels = {
            title:  'Zoek op titel …',
            writer: 'Zoek op schrijver …',
            genre:  'Zoek op genre …'
        };

        $('#search-inp')
            .attr('placeholder', labels[method] || labels.title)
            .val('')
            .trigger('input');
    });

    // -- Sort: Sort handler.
    $('#sort-options').on('change', function () {
        const [field, direction] = $(this).val().split('-');
        const $wrapper = $('#view-container');
        const $cards = $wrapper.find('.item-container');

        const sorted = $cards.get().sort((a, b) => {
            const va = SearchSort.extractFieldValue($(a), field);
            const vb = SearchSort.extractFieldValue($(b), field);
            const cmp = va.localeCompare(vb, 'nl', { sensitivity: 'base' });
            return direction === 'asc' ? cmp : -cmp;
        });

        $wrapper.append(sorted);
    });

    /* Trigger change on load to pre-fill with the first status. */
    $('#status-type').trigger('change');
    /* W.I.P. Testing functions */
    $('#login-name, #login-passw').on('input', inputCheck);
    $('.status-dot').on('click', testLights);
});

// Form editing related helper functions:
/** Exported to used in modules
 * Generic helper: Mark input fields as changed, when a field is made editable.
 */
export function markFieldChanged($field) {
    const $form   = $field.closest('form.book-edit-form');
    const $saveBtn = $form.find('button[id^="save-changes-"]');

    $field.addClass('field-changed');
    $saveBtn.addClass('needs-save');
}

/** Exported to used in modules
 * Generic helper: Clear field-changed state if no fields are dirty.
 */
export function clearFieldChanged($field) {
    const $form   = $field.closest('form.book-edit-form');
    const $saveBtn = $form.find('button[id^="save-changes-"]');
    
    $field.removeClass('field-changed');

    if ($form.find('.field-changed').length === 0) {
        $saveBtn.removeClass('needs-save');
    }
}

// W.I.P.
/**
 * inputCheck(e)
 * Validates login form inputs and applies Bootstrap validation classes.
 * Uses stringChecker for custom validation logic.
 */
function inputCheck(e) {
    let check;

    /* If a input was set, store the validation in @check. */
    if(e.target.value !== '') {
        switch(e.target.name) {
            case 'userName':
                check = stringChecker('name', e.target.value);
            case 'userPw':
                check = stringChecker('pass', e.target.value);
        }

        /* If set and true, make outline green. */
        if(check !== "" && check !== undefined && check) {
            if(e.target.classList.contains('is-invalid')) {
                e.target.classList.remove('is-invalid');
                e.target.style.outline = '';
            }
            
            return e.target.classList.add('is-valid');
        }

        /* If set and false, make outline red. */
        if(check !== "" && check !== undefined && !check) {
            if(e.target.classList.contains('is-valid')) {
                e.target.classList.remove('is-valid');
                e.target.style.outline = '';
            }

            return e.target.classList.add('is-invalid');
        }
    }

    /* If the input is cleared, remove all bootstrap class tags. */
    if(e.target.classList.contains('is-invalid')) {
        e.target.style.outline = '';
        return e.target.classList.remove('is-invalid');
    }

    if(e.target.classList.contains('is-valid')) {
        e.target.style.outline = '';
        return e.target.classList.remove('is-valid');
    }
}

/**
 * stringChecker(type, value)
 * Checks string length for username ('name') and password ('pass').
 * Returns true if valid, false otherwise.
 */
function stringChecker($type, $value) {
    switch($type) {
        case 'name':
            if($value.length >= 7) {
                return true;
            } else {
                return false;
            }
        case 'pass':
            if($value.length >= 9) {
                return true;
            } else {
                return false;
            }
    }
}

/* Other W.I.P. functions */
/**
 * changeSearchText(e)
 * Updates the search input placeholder text (legacy, may be replaced by #search-options handler).
 */
function changeSearchText(e) {
    let $input = $(e.target).next();
    $input.attr('placeholder', ' Zoek op ' + $(e.target).val() + ' ...');
}

/**
 * testLights(e)
 * Cycles through status light classes for book items (demo/UX concept).
 */
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