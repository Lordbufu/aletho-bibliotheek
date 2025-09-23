// This file is part of the Aletho Bibliotheek project.
/**
 * Global non-constant variables to help achieve frontend design goals
 */
let popinIsOpen = false;
let __bodyScrollY = 0;
let __bodyPadRight = '';

/**
 * Global constant variables, to reduce long-winded selectors
 */
const popins = [
  '#add-book-popin',
  '#status-period-popin',
  '#password-reset-popin',
  '#change-book-status-popin'
];

$(function() {
    /**
     * Global on-click event for the entire document.
     */
    $(document).on('click', function(event) {
        // console.log(event.target);       // temp debug line
        const detailsBtn = $(event.target).closest('[id^="itemButton-"]');
        const detEditBtn = $(event.target).closest('.extra-button-style');

        // Book details dropdown: close all dropdowns and open the new details dropdown.
        if (detailsBtn.length) {
            // console.log('inside the book details onclick loop');
            const targetId = detailsBtn.attr('data-bs-target');

            event.stopPropagation();
            closeDropdown('#customHamburgerDropdown');
            closeDropdown('#customSearchDropdown');

            $('.collapse.aletho-item-dropdown.show').each(function() {
                if ('#' + detailsBtn.attr('id') !== targetId) {
                    bootstrap.Collapse.getOrCreateInstance(this, {toggle: false}).hide();
                }
            });
        }

        // Custom dropdown logic: if open, close if click is outside element.
        $('.aletho-dropdown-body').each(function(){
            const dropdown = $(this);
            const toggleSelector = dropdown.data('toggle-button');

            console.log(dropdown);
            console.log(event.target);
            if (!(event.target).closest(dropdown).length &&
                !(event.target).closest(toggleSelector).length) {
                closeDropdown('#' + this.id);
            }
        });

        // // Hamburger dropdown: 
        // if ($('#customHamburgerDropdown').hasClass('show')) {
        //     let elements = $(event.target).closest('#customHamburgerDropdown, #hamburgerButton');
        //     if (! elements.length) {
        //         // console.log('inside the hamburger dropdown onclick loop');
        //         closeDropdown('#customHamburgerDropdown');
        //     }
        // }

        // // Search dropdown: close if open and click is outside.
        // if ($('#customSearchDropdown').hasClass('show')) {
        //     let elements = $(event.target).closest('#customSearchDropdown, #searchButton')
        //     if (! elements.length) {
        //         // console.log('inside the search dropdown onclick loop');
        //         closeDropdown('#customSearchDropdown');
        //     }
        // }

        // Popins: Close dropdowns if popin is open, unless click was inside a pop-in.
        if (popinIsOpen) {
            let ePopin = $(event.target).closest(popins.join(',') + ':visible');
            // console.log('inside the popinIsOpen onclick loop');
            if (ePopin.length > 0) {
                return;
            }
            closeDropdown('#customHamburgerDropdown');
            closeDropdown('#customSearchDropdown');
            return;
        }

        // Book details edit button: Make associated field editable, and store its original value.
        if (detEditBtn.length) {
            console.log('inside the details edit button onclick loop');
            const selector   = $(this).data('swapTargets');
            const $field     = $(selector);

            if ($field.prop('disabled')) {
                $field.prop('disabled', false)
                    .addClass('field-editable')
                    .focus();

                // ⬇️ This is where you store the original value
                if ($field.hasClass('writer-input')) {
                    // Collect current tags into an array
                    const tags = $field.siblings('.writer-tag').map(function() {
                        return $(this).clone().children().remove().end().text().trim();
                    }).get();

                    $field.data('originalValue', tags.sort().join(','));
                } else {
                    // For normal text inputs
                    $field.data('originalValue', $field.val());
                }
            }
        }
    });
    

    /**
     * Login pw and name string check event.
     */
    $('#login-name, #login-passw').on('input', inputCheck);

    /**
     * Add the event handles for all `popins` via a helper function.
     */
    setupPopin('#boek-add-button', '#add-book-popin', '#close-add-book-popin');
    setupPopin('#status-periode-button', '#status-period-popin', '#close-status-period-popin');
    setupPopin('#password-change-button', '#password-reset-popin', '#close-password-reset-popin');
    setupPopin('#boek-status-button', '#change-book-status-popin', '#close-change-book-status-popin');

    /**
     * Event handlers for the change-status-period popin, filling in input data after a status has been selected.
     */
    $('#status-type').on('change', function() {
        let $selected = $(this).find('option:selected');
        $('#periode-length').val($selected.data('periode_length'));
        $('#reminder-day').val($selected.data('reminder_day'));
        $('#overdue-day').val($selected.data('overdue_day'));
    });

    /**
     * Trigger change on load to pre-fill with the first status
     */
    $('#status-type').trigger('change');

    // --- Book details and Edit button logic ---
    /**
     * Edit button handler for book details
     * Enables the targeted input/select field for editing and stores its original value.
     */
    // $(document).on('click', '.extra-button-style', function() {
    //     const selector   = $(this).data('swapTargets');
    //     const $field     = $(selector);

    //     if ($field.prop('disabled')) {
    //         $field.prop('disabled', false)
    //             .addClass('field-editable')
    //             .focus();

    //         // ⬇️ This is where you store the original value
    //         if ($field.hasClass('writer-input')) {
    //             // Collect current tags into an array
    //             const tags = $field.siblings('.writer-tag').map(function() {
    //                 return $(this).clone().children().remove().end().text().trim();
    //             }).get();

    //             $field.data('originalValue', tags.sort().join(','));
    //         } else {
    //             // For normal text inputs
    //             $field.data('originalValue', $field.val());
    //         }
    //     }
    // });

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

    // Convert displayed 'writers' input into tags
    /**
     * Convert currently displayed 'writers', as above the input-group.
     */
    $('.writer-input').each(function() {
        const $input = $(this);
        const existing = $input.val();

        if (existing) {
            // Split by comma and trim
            existing.split(',').map(name => name.trim()).forEach(name => {
                if (name) {
                    addWriterTag($input, name, false);
                }
            });
            // Clear the raw string so only tags remain
            $input.val('');
        }
    });

    /**
     * Get all stored writers from PhP, and give a autofill option if applicable
     */
    $(document).on('input', '.writer-input', function() {
        const $input = $(this);
        const query = $input.val().trim();

        if (query.length < 2) {
            closeSuggestions($input);
            return;
        }

        $.getJSON('/writers', { query: query }, function(suggestions) {
            showSuggestions($input, suggestions);
        });
    });

    /**
     * For the writer-input specifically, tags need to be added after Enter is pressed.
     */
    $(document).on('keydown', '.writer-input', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const $input = $(this);
            const value  = $input.val().trim();
            
            if (value) {
                addWriterTag($input, value);
                $input.val('');
            }
        }
    });

    /**
     * On blur event to 'close' the input edit states, when focus is lost and nothing was changed.
     */
    $(document).on('blur', 'input.field-editable, select.field-editable, .writer-input', function() {
        const $field = $(this);
        const original = $field.data('originalValue');
        let current;

        if ($field.hasClass('writer-input')) {
            // Collect current tags
            current = $field.siblings('.writer-tag').map(function() {
                return $(this).clone().children().remove().end().text().trim();
            }).get().sort().join(',');
        } else {
            current = $field.val();
        }

        if (current === original) {
            // No change → reset
            $field.prop('disabled', true)
                .removeClass('field-editable field-changed')
                .removeData('originalValue');
            clearFieldChanged($field);
        }
    });

    /**
     * Save changes button handler
     * Disables edited fields, removes edit classes, and submits the form.
     */
    $(document).on('click', '[id^="save-changes-"]', function(e) {
        e.preventDefault();
        const $btn  = $(this);
        const $form = $btn.closest('form.book-edit-form');

        $form.off('submit._cleanup').on('submit._cleanup', function () {
            setTimeout(() => {
                $form.find('input.field-editable, select.field-editable, .writer-input').each(function() {
                    const $fld = $(this);
                    $fld.closest('.input-group').removeClass('writer-editable');
                    $fld.prop('disabled', true).removeClass('field-editable field-changed').removeData('originalValue');
                    $btn.removeClass('needs-save');
                });
            }, 0);
        });
        
        $form.trigger('submit');
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

    /* status periode change events */
    $('#periode-wijz-button').on('click', function() {
        $('#status-period-popin').show();
    });

    $('#close-status-period-popin').on('click', function() {
        $('#status-period-popin').hide();
    });

    /* Optional: Hide modal when clicking outside the modal-content */
    $('#status-period-popin').on('click', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });

    /* Temp stealth solution: dont submit any form when enter is pressed // comment out later */
    // $('form').on('keypress', function(e) { if (e.key === 'Enter') { e.preventDefault(); } });

    /**
     * Check if a URL hash was set during a PhP redirect, and open the popin associated with it.
     * As a bonus feature, we also clean up the URL again, so the adress bar stays nice and clean.
     */
    if (window.location.hash) {
        var popinId = window.location.hash;
        var $popin = $(popinId);

        if ($popin.length) {
            openPopin(popinId);
            history.replaceState(null, '', window.location.pathname + window.location.search);
        }
    }

    $('.status-dot').on('click', testLights);   // Concept code for the status lights, now using jQuery
});

// Popin & dropdown related helper functions:
/**
 * Generic helper: Opens a modal popin, locks body scroll, and closes hamburger dropdown.
 */
function openPopin(selector) {
    $(selector).show();
    popinIsOpen = true;
    lockBodyScroll();
    closeDropdown('#customHamburgerDropdown');
}

/**
 * Generic helpler: Closes a modal popin and unlocks body scroll if no other popins are open.
 */
function closePopin(selector) {
    $(selector).hide();

    // If no other popins are visible, unlock the body
    if ($('.modal:visible').length === 0) {
        popinIsOpen = false;
        unlockBodyScroll();
    } else {
        popinIsOpen = true;
    }
}

/**
 * Generic helper: Setup event handlers for opening and closing the pop-ins.
 *  @param openBtn  -> Selector for the open popin button.
 *  @param popinId  -> Selector for the popin/modal element.
 *  @param closeBtn -> Selector for the close popin button.
 */
function setupPopin(openBtn, popinId, closeBtn) {
    $(openBtn).on('click', function() {
        openPopin(popinId);
    });

    $(closeBtn).on('click', function() {
        closePopin(popinId);
    });

    $(popinId).on('click', function(e) {
        if (e.target === this) {
            closePopin(popinId);
        }
    });
}

/**
 * Generic helper: Closes any dropdown (e.g., hamburger, search) by selector using Bootstrap's Collapse API.
 */
function closeDropdown(selector) {
    console.log("Attempting to close the dropdown: " + selector);
    const $dropdown = $(selector);

    if ($dropdown.hasClass('show')) {
        bootstrap.Collapse.getOrCreateInstance($dropdown[0], {toggle: false}).hide();
    }
}

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

/** W.I.P.
 * Generic helper: Create book writer/genre tags, above the input group.
 */
function addWriterTag($input, text, markChanged = true) {
    // Normalize text (trim, case-insensitive)
    const normalized = text.trim().toLowerCase();

    // Check if this writer already exists in tags
    let exists = false;
    $input.siblings('.writer-tag').each(function() {
        const existing = $(this).clone().children().remove().end().text().trim().toLowerCase();

        if (existing === normalized) {
            exists = true;
            return false; // break loop
        }
    });

    if (exists) {
        // Optionally flash the existing tag to show it's already there
        $input.siblings('.writer-tag').filter(function() {
            return $(this).clone().children().remove().end().text().trim().toLowerCase() === normalized;
        }).addClass('duplicate-flash');

        setTimeout(() => {
            $input.siblings('.writer-tag')
                .removeClass('duplicate-flash');
        }, 500);

        return;
    }

    // Otherwise, create the tag
    const $tag = $('<span class="writer-tag">').text(text);
    const $remove = $('<button type="button">×</button>').on('click', function() {
            $tag.remove();
            markFieldChanged($input);
        });

    $tag.append($remove);

    // Insert before the input
    $input.closest('.input-group').before($tag);

    // Hidden input for form submission
    const $hidden = $('<input type="hidden" name="book_writers[]">').val(text);
    $tag.append($hidden);

    if (markChanged) {
        markFieldChanged($input);
    }
}

/** W.I.P.
 * Generic helper: Show autocomplete suggestions for book writers/genres
 */
function showSuggestions($input, suggestions) {
    closeSuggestions($input);

    const $list = $('<ul class="writer-suggestions">');
    suggestions.forEach(name => {
        const $item = $('<li>').text(name);

        $item.on('click', function() {
            addWriterTag($input, name);
            $input.val('');
            closeSuggestions($input);
        });

        $list.append($item);
    });

    $input.after($list);
}

/** W.I.P.
 * Generic helper: Close autocomplete suggestions for book writers/genres.
 */
function closeSuggestions($input) {
    $input.siblings('.writer-suggestions').remove();
}

/**
 * Generic helper: Locks the body scroll when a modal is open to prevent background scrolling.
 */
function lockBodyScroll() {
    // Save current scroll
    __bodyScrollY = window.scrollY || window.pageYOffset;

    // Compensate for scrollbar to avoid layout shift
    const scrollbarW = window.innerWidth - document.documentElement.clientWidth;
    __bodyPadRight = document.body.style.paddingRight;

    if (scrollbarW > 0) {
        document.body.style.paddingRight = `${scrollbarW}px`;
    }

    // Lock with fixed positioning (prevents content jump)
    document.body.style.position = 'fixed';
    document.body.style.top = `-${__bodyScrollY}px`;
    document.body.style.left = '0';
    document.body.style.right = '0';
    document.body.style.width = '100%';

    // For good measure and consistency with Bootstrap
    document.body.classList.add('modal-open');
}

/**
 * Generic helper: Unlocks the body scroll when all modals are closed.
 */
function unlockBodyScroll() {
    document.body.classList.remove('modal-open');

    // Restore body styles
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.left = '';
    document.body.style.right = '';
    document.body.style.width = '';
    document.body.style.paddingRight = __bodyPadRight;

    // Restore scroll position
    window.scrollTo(0, __bodyScrollY || 0);
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