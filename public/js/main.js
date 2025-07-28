/* Create a constant dropdown cache, so its only loaded at runtime. */
const DROPDOWN_CACHE = {
    searchToggle:   null,
    searchDropdown: null,
    hamToggle:      null,
    hamDropdown:    null,
    itemDropdowns:  null
}

$(function(){
    /* Populate the dropdown cache. */
    DROPDOWN_CACHE.searchToggle   = $('#search-button');
    DROPDOWN_CACHE.searchDropdown = $('#customSearchDropdown');
    DROPDOWN_CACHE.hamToggle      = $('#hamburger-button');
    DROPDOWN_CACHE.hamDropdown    = $('#customHamburgerDropdown');
    DROPDOWN_CACHE.itemDropdowns  = $('[id^="customItemDropdown-"]'); // expects unique IDs like customItemDropdown-<bookId>

    /* Build the cache mapping only once. */
    const mappings = getDropdownMappings(DROPDOWN_CACHE);

    // now bind handlers, etc.
    mappings.forEach(({toggle, dropdown}) => {
        toggle.on('click', function(event) {
            event.stopPropagation();
            const collapse = bootstrap.Collapse.getOrCreateInstance(dropdown[0], {toggle: false});
            // If this dropdown is open, close it and return
            if (dropdown.hasClass('show')) {
                collapse.hide();
                return;
            }
            // Otherwise, close all others and open this one
            mappings.forEach(({ dropdown: otherDropdown }) => {
                if (otherDropdown !== dropdown && otherDropdown.hasClass('show')) {
                    bootstrap.Collapse.getOrCreateInstance(otherDropdown[0], {toggle: false}).hide();
                }
            });
            collapse.show();
        });
    });

    // Close all dropdowns when the add-book popin is triggered
    $('#boek-toev-button').on('click', function() {
        getDropdownMappings(DROPDOWN_CACHE).forEach(({ dropdown }) => {
            closeCollapse(dropdown);
        });
    });

    /* Login input elements & events: */
    $('#login-name, #login-passw').on('input', inputCheck);

    /* 1. Document click event: Close dropdown if a click is outside both the dropdown and its toggle. */
    $(document).on('click', function(event) {
        getDropdownMappings(DROPDOWN_CACHE).forEach(({ toggle, dropdown }) => {
            if(dropdown.hasClass('show') && !dropdown.has(event.target).length && !(toggle && toggle.has(event.target).length)) {
                closeCollapse(dropdown);
            }
        });
    });

    /* 2. Global keydown event: On Escape, close all open dropdowns. */
    $(document).on('keydown', function(event) {
        if(event.key === 'Escape') {
            getDropdownMappings(DROPDOWN_CACHE).forEach(({ dropdown }) => {
                closeCollapse(dropdown);
            });
        }
    });

    /* 3. Attach focusout event for each dropdown to close it when focus leaves. (function defined outside this event) */
    attachFocusOutEvents();

    /* 4. Window blur event: Close all dropdowns when the window loses focus. */
    $(window).on('blur', function(event){
        getDropdownMappings(DROPDOWN_CACHE).forEach(({ dropdown }) => {
            closeCollapse(dropdown);
        });
    });

    // Concept code for the status lights, now using jQuery
    $('.status-dot').on('click', testLights);
});

/*  Helper - getDropdownMappings(): Returns an array of objects mapping a dropdown to its corresponding toggle. */
function getDropdownMappings(cache) {
    const maps = [];

    if(cache.searchToggle && cache.searchDropdown && cache.searchDropdown.length) {
        maps.push({
            toggle:  cache.searchToggle,
            dropdown: cache.searchDropdown
        });
    }

    if(cache.hamToggle && cache.hamDropdown && cache.hamDropdown.length) {
        maps.push({
            toggle: cache.hamToggle,
            dropdown: cache.hamDropdown
        });
    }

    /* Automatically add any dynamically generated item dropdowns. */
    if(cache.itemDropdowns && cache.itemDropdowns.length) {
        cache.itemDropdowns.each(function() {
            const $dropdown = $(this);
            const targetId  = $dropdown.attr('id');
            const $toggle   = $(`[data-bs-target="#${targetId}"]`);

            if($toggle.length) {
                maps.push({
                    toggle: $toggle,
                    dropdown: $dropdown
                });
            }
        });
    }

    return maps;
}

/*  Helper - closeCollapse: Closes the given dropdown if it is open. */
function closeCollapse(dropdown) {
    if (dropdown && dropdown.hasClass('show')) {
        bootstrap.Collapse.getOrCreateInstance(dropdown[0], {toggle: false}).hide();
    }
}

/*  attachFocusOutEvents(): Attach focusout event for each dropdown to close it when focus leaves. */
function attachFocusOutEvents() {
    getDropdownMappings(DROPDOWN_CACHE).forEach(({ dropdown }) => {
        dropdown.on('focusout', () => {
            /* Timeout ensures that the focus has shifted. */
            setTimeout(() => {
                if (!dropdown.is(':focus-within')) {
                    closeCollapse(dropdown);
                }
            }, 0);
        });
    });
}

/*  inputCheck(e):
        Check the input and add bootstrap styling if valid or invalid based on stringChecker().
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

// W.I.P.
/*  stringChecker($type, $value):
        This needs to be a global function with a all various string length checks.
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

/*  changeSearchText(e):
        Change the search placeholder text, of the search input below the dropdown menu.
 */
function changeSearchText(e) {
    // e is a native event or jQuery event
    let $input = $(e.target).next();
    $input.attr('placeholder', ' Zoek op ' + $(e.target).val() + ' ...');
}

/* Concept code of how to change the status light for each item, based on the current status style. */
function testLights(e) {
    var $el = $(e.target);
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