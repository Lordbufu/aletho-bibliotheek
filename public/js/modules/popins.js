const Popins = (() => {
    // Centralized selectors for all popins
    const popinSelectors = [
        '#add-book-popin',
        '#status-period-popin',
        '#password-reset-popin',
        '#change-book-status-popin'
    ];
    let isOpen = false;
    let scrollY = 0;
    let padRight = 0;

    /*  Get all popin selectors as an array. */
    function getSelectors() {
        return popinSelectors;
    }

    /*  Lock body scroll and adjust padding when popin is open. */
    function lockBodyScroll() {
        scrollY = window.scrollY;
        padRight = window.innerWidth - document.body.clientWidth;
        $('body')
            .addClass('modal-open')
            .css({
                position: 'fixed',
                top: -scrollY,
                width: '100%',
                paddingRight: padRight
            });
    }

    /*  Unlock body scroll and reset padding when popin is closed. */
    function unlockBodyScroll() {
        $('body')
            .removeClass('modal-open')
            .css({
                position: '',
                top: '',
                width: '',
                paddingRight: ''
            });
        window.scrollTo(0, scrollY);
    }

    /*  Open a popin by selector, lock scroll, and set open state. */
    function open(selector) {
        if (isOpen) return;
        lockBodyScroll();
        $(selector).show();
        isOpen = true;
    }

    /*  Clears all input, select, and textarea fields, and empties tag containers within a popin. */
    function _clearFields($popin) {
        // Find and reset all input, select, and textarea fields
        $popin.find('input, select, textarea').each(function() {
            const $field = $(this);
            if ($field.is(':checkbox') || $field.is(':radio')) {
                $field.prop('checked', false);
            } else {
                $field.val('');
            }
        });

        // Find and empty all tag containers (divs with class containing "-tags-container")
        $popin.find('div[class*="-tags-container"]').empty();
    }

    /*  Close a popin by selector, unlock scroll, and reset open state. */
    function close(selector) {
        const $popin = $(selector);
        if ($popin.length === 0) {
            return;
        }

        // If the popin has the 'clear-on-close' class, clear its fields
        if ($popin.hasClass('clear-on-close')) {
            _clearFields($popin);
        }

        $popin.hide();
        unlockBodyScroll();
        isOpen = false;
        // Trigger a custom event when the popin is closed.
        $popin.trigger('popin:close');
    }

    /*  Setup open/close event handlers for a popin. */
    function setup(openBtn, popinId, closeBtn, beforeOpenCb) {
        $(document).on('click', openBtn, function () {
            const $btn = $(this);
            const context = $btn.data();

            if (typeof beforeOpenCb === 'function') {
                beforeOpenCb(popinId, context);
            }

            open(popinId);
        });

        $(closeBtn).on('click', () => close(popinId));

        const $popin = $(popinId);
        if ($popin.hasClass('backdrop-close')) {
            $popin.on('click', function (e) {
                if (e.target === this) {
                    close(popinId);
                }
            });
        }
    }

    /*  Open a popin if the URL hash matches a popin selector. */
    function initFromHash() {
        if (window.location.hash) {
            const popinId = window.location.hash;
            const $popin = $(popinId);
            if ($popin.length) {
                open(popinId);
                history.replaceState(null, '', window.location.pathname + window.location.search);
            }
        }
    }

    /*  Handle outside click: closes dropdowns if click is outside any open popin. */
    function handleOutsideClick(event, closeDropdownFn) {
        if (!isOpen) return;
        const ePopin = $(event.target).closest(popinSelectors.join(',') + ':visible');
        if (ePopin.length > 0) return;
        if (typeof closeDropdownFn === 'function') {
            closeDropdownFn(['#customHamburgerDropdown', '#customSearchDropdown']);
        }
    }

    // Exported API
    return { getSelectors, open, close, setup, initFromHash, handleOutsideClick };
})();

export { Popins };