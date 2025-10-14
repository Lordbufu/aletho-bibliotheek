/**
 * Popins module: Handles modal popin logic for the app.
 * - Manages opening/closing popins and body scroll lock
 * - Centralizes popin selectors
 * - Handles outside click and hash-based popin opening
 */
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

    /** Get all popin selectors as an array.
     *      @returns {string[]}
     */
    function getSelectors() {
        return popinSelectors;
    }

    /* Lock body scroll and adjust padding when popin is open. */
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

    /* Unlock body scroll and reset padding when popin is closed. */
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

    /** Open a popin by selector, lock scroll, and set open state.
     *      @param {string} selector - Popin selector
     */
    function open(selector) {
        if (isOpen) return;
        lockBodyScroll();
        $(selector).show();
        isOpen = true;
    }

    /** Clears all input, select, and textarea fields, and empties tag containers within a popin.
     *      @param {jQuery} $popin The popin element whose fields need to be cleared.
     */
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

    /** Close a popin by selector, unlock scroll, and reset open state.
     *      @param {string} selector - Popin selector
     */
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

    /** Setup open/close event handlers for a popin.
     *      @param {string} openBtn - Selector for open button
     *      @param {string} popinId - Selector for popin
     *      @param {string} closeBtn - Selector for close button
     */
    function setup(openBtn, popinId, closeBtn) {
        $(openBtn).on('click', () => open(popinId));
        $(closeBtn).on('click', () => close(popinId));

        const $popin = $(popinId);

        // Only close on backdrop click if the popin has the 'backdrop-close' class
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

    /** Handle outside click: closes dropdowns if click is outside any open popin.
     *      @param {Event} event - Click event
     *      @param {Function} closeDropdownFn - Function to close dropdowns
     */
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