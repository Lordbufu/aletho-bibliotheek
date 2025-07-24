/* Create a constant dropdown cache, so its only loaded at runtime. */
const DROPDOWN_CACHE = {
    searchToggle:   null,
    searchDropdown: null,
    hamToggle:      null,
    hamDropdown:    null,
    itemDropdowns:  null
}

// document.addEventListener('DOMContentLoaded', () => {
$(function(){
    /* Populate the dropdown cache. */
    DROPDOWN_CACHE.searchToggle   = $('#search-button');
    DROPDOWN_CACHE.searchDropdown = $('#customSearchDropdown');
    DROPDOWN_CACHE.hamToggle      = $('#hamburger-button');
    DROPDOWN_CACHE.hamDropdown    = $('#customHamburgerDropdown');
    DROPDOWN_CACHE.itemDropdowns  = $('[id^="customItemDropdown-"]');

    /* Build the cache mapping only once. */
    const mappings = getDropdownMappings(DROPDOWN_CACHE);

    // now bind handlers, etc.
    mappings.forEach(({toggle, dropdown}) => {
        toggle.on('click', (event) => {
            mappings.forEach(({ dropdown: otherDropdown }) => {
            if (otherDropdown !== dropdown && otherDropdown.hasClass('show')) {
                closeCollapse(otherDropdown);
            }
            });
            
            // your toggle logic…
            dropdown.toggleClass('show'); 
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

    /* concept code for the status lights, currently just fake events so i can trigger the change. */
    const statusLights = document.querySelectorAll('.status-dot');
    const statusLightsArr = Array.from(statusLights);

    for(key in statusLightsArr) {
        statusLightsArr[key].addEventListener('click', testLights);
    }
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
    const el = dropdown && dropdown.get ? dropdown.get(0) : dropdown;

    if(!el || !el.classList.contains("show")) {
        return;
    }

    let bsCollapse = bootstrap.Collapse.getInstance(el) || new bootstrap.Collapse(el, { toggle: false });
    bsCollapse.hide();
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
    let sInput = e.target.nextElementSibling;
    sInput.placeholder = " Zoek op " + e.target.value + " ...";
}

/* Concept code of how to change the status light for each item, based on the current status style. */
function testLights(e) {
    if(e.target.classList.contains('statusOne')) {
        e.target.classList.remove('statusOne');
        return e.target.classList.toggle('statusTwo');
    }

    if(e.target.classList.contains('statusTwo')) {
        e.target.classList.remove('statusTwo');
        return e.target.classList.toggle('statusThree');
    }

    if(e.target.classList.contains('statusThree')) {
        e.target.classList.remove('statusThree');
        return e.target.classList.toggle('statusFour');
    }

    if(e.target.classList.contains('statusFour')) {
        e.target.classList.remove('statusFour');
        return e.target.classList.toggle('statusOne');
    }
}