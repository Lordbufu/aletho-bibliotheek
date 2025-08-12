// This file is part of the Aletho Bibliotheek project.
let popinIsOpen = false;

$(function() {
    /* Login input elements & events: */
    $('#login-name, #login-passw').on('input', inputCheck);

    // Concept code for the status lights, now using jQuery
    $('.status-dot').on('click', testLights);

    // DRY popin setup
    function setupPopin(openBtn, popinId, closeBtn) {
        $(openBtn).on('click', function() { openPopin(popinId); });
        $(closeBtn).on('click', function() { closePopin(popinId); });
        $(popinId).on('click', function(e) { if (e.target === this) closePopin(popinId); });
    }

    // Add book pop-in
    setupPopin('#boek-add-button', '#add-book-popin', '#close-add-book-popin');

    // Change status periode pop-in
    setupPopin('#status-periode-button', '#status-period-popin', '#close-status-period-popin');

    // Password reset pop-in
    setupPopin('#password-change-button', '#password-reset-popin', '#close-password-reset-popin');

    // Change book status pop-in
    setupPopin('#boek-status-button', '#change-book-status-popin', '#close-change-book-status-popin');

    $(document).on('click', function(event) {
        // If a popin is open, only close the hamburger dropdown if click is outside any popin
        if (popinIsOpen) {
            if (
                $(event.target).closest('#add-book-popin:visible,#status-period-popin:visible, #password-reset-popin:visible, #change-book-status-popin:visible').length > 0
            ) {
                return; // Click was inside a popin, do nothing
            }
            closeHamburgerDropdown();
            return;
        }

        // Hamburger dropdown: close if open and click is outside
        if (! $(event.target).closest('#customHamburgerDropdown, #hamburgerButton').length) {
            closeHamburgerDropdown();
        }

        // Search dropdown: close if open and click is outside
        if (! $(event.target).closest('#customSearchDropdown, #searchButton').length) {
            closeSearchDropdown();
        }
    });

    $('#status-type').on('change', function() {
        let $selected = $(this).find('option:selected');
        $('#periode-length').val($selected.data('periode_length'));
        $('#reminder-day').val($selected.data('reminder_day'));
        $('#overdue-day').val($selected.data('overdue_day'));
    });

    // Trigger change on load to pre-fill with the first status
    $('#status-type').trigger('change');

    // When any popin is triggered, close the hamburger dropdown only
    $('#boek-toev-button, #periode-wijz-button, #wachtwoord-wijz-button, #boek-status-wijz-button').on('click', function() {
        closeHamburgerDropdown();
    });

    // Book details dropdown logic: only one open at a time
    $('[id^="itemButton-"]').on('click', function(e) {
        let targetId = $(this).attr('data-bs-target');

        e.stopPropagation();
        // Close hamburger and search dropdowns
        closeHamburgerDropdown();
        closeSearchDropdown();

        // Close all other open details
        $('.collapse.aletho-item-dropdown.show').each(function() {
            if ('#' + $(this).attr('id') !== targetId) {
                bootstrap.Collapse.getOrCreateInstance(this, {toggle: false}).hide();
            }
        });
    });

    /* Book details edit\submit logic events */
    // single click-handler for all edit buttons
    $(document).on('click', '.extra-button-style', function() {
        const selector   = $(this).data('swapTargets');
        const $field     = $(selector);

        /* Only act if the field is currently disabled, enable field, mark as editable and save org value, then set focus. */
        if ($field.prop('disabled')) {
            $field.prop('disabled', false);
            $field.addClass('field-editable').data('originalValue', $field.val());
            $field.focus();
        }
    });

    // Input/Change listener for editable fields
    $(document).on('input change', 'input.field-editable, select.field-editable', function() {
        const $field = $(this);
        const original = $field.data('originalValue');
        const current = $field.val();
        const $form = $field.closest('form.book-edit-form');
        const $saveBtn = $form.find('button[id^="save-changes-"]');

        if (current !== original) {
            $field.addClass('field-changed');
            $saveBtn.addClass('needs-save');
        } else {
            $field.removeClass('field-changed');

            // Check if ANY field is still dirty
            if ($form.find('.field-changed').length === 0) {
                $saveBtn.removeClass('needs-save');
            }
        }
    });

    // Book details 'click' event for the submit button 
    $(document).on('click', '[id^="save-changes-"]', function(e) {
        e.preventDefault();

        // 1) Find this button’s form
        const $btn  = $(this);
        const $form = $btn.closest('form.book-edit-form');

        // 2) Disable & cleanup only fields in *this* form
        $form.find('input.field-editable, select.field-editable').each(function() {
            const $fld = $(this);
            $fld.prop('disabled', true)
                .removeClass('field-editable field-changed')
                .removeData('originalValue');
        });

        $btn.removeClass('needs-save');

        // 3) (Re)submit or AJAX-post the form if needed:
        // $form.submit();
    });

    // 1) On each keystroke, filter item containers
    $('#search-inp').on('input', function() {
        const query  = $(this).val().toLowerCase().trim();
        const method = $('#search-options').val(); // title | writer | genre

        $('.item-container').each(function() {
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

    // Adjust search placeholder labels on change
    $('#search-options').on('change', function() {
        const method = $(this).val();
        const labels = {
            title:  'Zoek op titel …',
            writer: 'Zoek op schrijver …',
            genre:  'Zoek op genre …'
        };

        $('#search-inp').attr('placeholder', labels[method] || labels.title).val('').trigger('input');
    });

    // 2) On sort‐select change, reorder .item-container
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

    // status periode change events
    $('#periode-wijz-button').on('click', function() {
        $('#status-period-popin').show();
    });

    $('#close-status-period-popin').on('click', function() {
        $('#status-period-popin').hide();
    });
    
    // Optional: Hide modal when clicking outside the modal-content
    $('#status-period-popin').on('click', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });

    // Temp soulution 1: specific keydopwn to prevent a form submit.    // uncomment later
    // $('.book-edit-form input').on('keydown', function(e) {
    //     if (e.key === 'Enter') {
    //         e.preventDefault(); // Stops Enter from submitting the form
    //     }
    // });

    // Temp stealth solution: dont submit any form when enter is pressed // comment out later
    $('form').on('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
        }
    });
});

// Open popin function with popinIsOpen flag
function openPopin(selector) {
    $(selector).show();
    popinIsOpen = true;
    closeHamburgerDropdown();
}

// Close popin function with popinIsOpen flag
function closePopin(selector) {
    $(selector).hide();
    popinIsOpen = false;
}

// Helper to close the hamburger dropdown only, but still allowing custom logic.
function closeHamburgerDropdown() {
    closeDropdown('#customHamburgerDropdown');
}

// Helper to close the search dropdown only, but still allowing custom logic.
function closeSearchDropdown() {
    closeDropdown('#customSearchDropdown');
}

// Generic function to close any dropdown by selector, using the Bootstrap Collapse instance.
function closeDropdown(selector) {
    let $dropdown = $(selector);

    if ($dropdown.hasClass('show')) {
        bootstrap.Collapse.getOrCreateInstance($dropdown[0], {toggle: false}).hide();
    }
}

/* inputCheck(e): Check the input and add bootstrap styling if valid or invalid based on stringChecker(). */
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

// Utility: extract sort key from a card
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

// W.I.P.
/* stringChecker($type, $value): This needs to be a global function with a all various string length checks. */
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

/* changeSearchText(e): Change the search placeholder text, of the search input below the dropdown menu. */
function changeSearchText(e) {
    let $input = $(e.target).next();
    $input.attr('placeholder', ' Zoek op ' + $(e.target).val() + ' ...');
}

/* Concept code of how to change the status light for each item, based on the current status style. */
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