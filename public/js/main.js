// This file is part of the Aletho Bibliotheek project.
let popinIsOpen = false;

$(function() {
    // DRY popin setup
    function setupPopin(openBtn, popinId, closeBtn) {
        $(openBtn).on('click', function() { openPopin(popinId); });
        $(closeBtn).on('click', function() { closePopin(popinId); });
        $(popinId).on('click', function(e) { if (e.target === this) closePopin(popinId); });
    }

    setupPopin('#boek-toev-button', '#add-book-popin', '#close-add-book-popin');
    setupPopin('#periode-wijz-button', '#status-period-popin', '#close-status-period-popin');
    setupPopin('#wachtwoord-wijz-button', '#password-reset-popin', '#close-password-reset-popin');
    setupPopin('#boek-status-button', '#change-book-status-popin', '#close-change-book-status-popin');

    $(document).on('click', function(event) {
        // If a popin is open, only close the hamburger dropdown if click is outside any popin
        if (popinIsOpen) {
            if (
                $(event.target).closest('#add-book-popin:visible, #status-period-popin:visible, #password-reset-popin:visible, #change-book-status-popin:visible').length > 0
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
        $('.item-details.collapse.show').each(function() {
            if ('#' + $(this).attr('id') !== targetId) {
                bootstrap.Collapse.getOrCreateInstance(this, {toggle: false}).hide();
            }
        });
    });

    /* Book details edit\submit logic events */
    // single click-handler for all edit buttons
    $(document).on('click', '.edit-field-btn', function() {
        const selector   = $(this).data('swapTargets');
        const $field     = $(selector);

        // only act if the field is currently disabled
        if($field.prop('disabled')) {
            // Enable field
            $field.prop('disabled', false);

            // Mark editable & save org value.
            $field.addClass('field-editable').data('originalValue', $field.val());

            // set focus
            $field.focus();
        }
    });

    // Book details on input change event for input and select
    $(document).on('input change', 'input, select', function() {
        let $this = $(this);

        // only mark if we previously unlocked it
        if ($this.hasClass('field-editable')) {
            $this.addClass('field-edited');
        }
    });

    // Book details 'click' event for the submit button 
    $(document).on('click', '[id^="save-change-"]', function(e) {
        e.preventDefault();

        // 1) Find this button’s form
        const $btn  = $(this);
        const $form = $btn.closest('form.book-edit-form');

        // 2) Disable & cleanup only fields in *this* form
        $form.find('input.field-editable, select.field-editable').each(function() {
            const $fld      = $(this);
            const original  = $fld.data('originalValue');
            const current   = $fld.val();

            // mark if changed
            if (current !== original) {
                $fld.addClass('edited');
            }

            // disable & reset markers
            $fld.prop('disabled', true).removeClass('field-editable').removeData('originalValue');
        });

        // 3) (Re)submit or AJAX-post the form if needed:
        // $form.submit();
    });

    /* Login input elements & events: */
    $('#login-name, #login-passw').on('input', inputCheck);

    // Concept code for the status lights, now using jQuery
    $('.status-dot').on('click', testLights);
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
    // e is a native event or jQuery event
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