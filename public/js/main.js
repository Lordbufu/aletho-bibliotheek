/** This file is part of the Aletho Bibliotheek project.
 *      Loaded modules via the 'header.php' template:
 *          @Popins     => ..\modules\popin.js
 *              All popin related functions have been offloaded to this module, loaded as a Popins variable.
 *          @Dropdowns  => ..\modules\dorpdowns.js
 *              All dropdown related function have been offloaded to this module, loaded as a Dropdowns variable.
 *          @Writers    => ..\modules\writers.js
 *              All writer input related functions, that deal with converting and reseting its input/displayed tags.
 */

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
    Writers.init('.writer-input', '#writer-tags-container');

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
            const selector   = $detEditBtn.data('swapTargets');
            const $field     = $(selector);

            if ($field.prop('disabled')) {
                $field.prop('disabled', false)
                    .addClass('field-editable')
                    .focus();

                if ($field.hasClass('writer-input')) {
                    const existing = $field.val();
                    if (existing) {
                        existing.split(',')
                            .map(name => name.trim())
                            .forEach(name => {
                                if (name) {
                                    Writers.addTag(name, $field);
                                }
                            });
                    }

                    const tags = $field.closest('.input-group').prevAll('.writer-tag').map(function() {
                        return $(this).clone().children().remove().end().text().trim();
                    }).get();

                    $field.data('originalValue', tags.sort().join(','));
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


    /* Trigger change on load to pre-fill with the first status. */
    $('#status-type').trigger('change');

    // --- Book details and Edit button logic ---
    /**
     * Input/change listener for editable fields
     * Tracks changes, applies 'field-changed' class, and enables/disables the save button.
     */
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

    /**
     * On blur event to 'close' the input edit states, when focus is lost and nothing was changed.
     */
    $(document).on('blur', 'input.field-editable, select.field-editable, .writer-input', function() {
        const $field = $(this);
        const original = $field.data('originalValue');
        let current;

        if (Writers.isRemoving()) {
            return;
        }

        if ($field.hasClass('writer-input')) {
            // Collect current tags
            const $tags = $field.closest('.input-group').prevAll('.writer-tag');
            current = $tags.map(function() {
                return $(this).clone().children().remove().end().text().trim();
            }).get().sort().join(',');
        } else {
            current = $field.val();
        }

        // No change → reset
        if (current === original) {
            if ($field.hasClass('writer-input')) {
                const $tags = $field.closest('.input-group').prevAll('.writer-tag');
                const tags = $tags.map(function() {
                    return $(this).clone().children().remove().end().text().trim();
                }).get();

                $tags.remove();
                $field.val(tags.join(', '));
            }

            $field.prop('disabled', true)
                .removeClass('field-editable field-changed')
                .removeData('originalValue');
            clearFieldChanged($field);
        }
    });

    // --- Search and Sort Logic ---
    /**
     * Search input handler
     * Filters book cards based on the selected search method (title, writer, genre).
     */
    $('#search-inp').on('input', function() {
        const query  = $(this).val().toLowerCase().trim();
        const method = $('#search-options').val(); // title | writer | genre

        $('.aletho-item-container').each(function() {
            const $card = $(this);
            let textToSearch = '';

            // pick the right field to search
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

            // show/hide based on match
            if (textToSearch.toLowerCase().includes(query)) {
                $card.show();
            } else {
                $card.hide();
            }
        });
    });

    /**
     * Search options change handler
     * Updates the search input placeholder text based on selected search method.
     */
    $('#search-options').on('change', function() {
        const method = $(this).val();
        const labels = {
            title:  'Zoek op titel …',
            writer: 'Zoek op schrijver …',
            genre:  'Zoek op genre …'
        };

        $('#search-inp').attr('placeholder', labels[method] || labels.title).val('').trigger('input');
    });

    /**
     * Sort options change handler
     * Sorts book cards in the container based on selected field and direction.
     */
    $('#sort-options').on('change', function() {
        const [field, direction] = $(this).val().split('-');  // e.g. ['title','asc']
        const $wrapper = $('#view-container');               // parent of .item-container
        // Pull cards into an array
        const cards = $wrapper.find('.item-container').get();

        // Sort
        cards.sort((a, b) => {
            const va = getSortValue($(a), field);
            const vb = getSortValue($(b), field);
            const cmp = va.localeCompare(vb, 'nl', { sensitivity: 'base' });
            return direction === 'asc' ? cmp : -cmp;
        });

        // Re-append in new order
        cards.forEach(card => $wrapper.append(card));
    });

    /* Temp stealth solution: dont submit any form when enter is pressed // comment out later */
    // $('form').on('keypress', function(e) { if (e.key === 'Enter') { e.preventDefault(); } });

    /* W.I.P. Testing functions */
    $('#login-name, #login-passw').on('input', inputCheck);     // input length checker
    $('.status-dot').on('click', testLights);                   // Concept code for the status lights, now using jQuery
});



// Form editing related helper functions:
/**
 * Generic helper: Mark input fields as changed, when a field is made editable.
 */
function markFieldChanged($field) {
    const $form   = $field.closest('form.book-edit-form');
    const $saveBtn = $form.find('button[id^="save-changes-"]');

    $field.addClass('field-changed');
    $saveBtn.addClass('needs-save');
}

/**
 * Generic helper: Clear field-changed state if no fields are dirty.
 */
function clearFieldChanged($field) {
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

// Utility: --- Search and Sort Logic ---
/**
 * getSortValue($card, field)
 * Utility to extract the sort key from a book card for sorting.
 */
function getSortValue($card, field) {
    switch (field) {
        case 'writer':
            return ($card.find('input[name="book_writer"]').val() || '').toLowerCase();
        case 'genre':
            return ($card.find('select[name="genre_id"] option:selected').text() || '').toLowerCase();

        case 'title':
        default:
            return ($card.find('.mn-main-col').text() || '').toLowerCase();
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