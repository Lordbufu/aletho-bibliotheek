
/**
 * Dropdowns module: Handles opening, closing, and toggling of dropdown menus using Bootstrap Collapse.
 * - open: Opens a dropdown by selector
 * - close: Closes one or more dropdowns by selector(s)
 * - toggle: Toggles a dropdown, optionally closing others first
 */
const Dropdowns = (() => {
    /**
     * Open a dropdown menu by selector.
     * @param {string} selector - Selector for the dropdown
     */
    function open(selector) {
        const $dropdown = $(selector);
        bootstrap.Collapse.getOrCreateInstance($dropdown[0], { toggle: false }).show();
    }

    /**
     * Close one or more dropdown menus by selector(s).
     * @param {string|string[]} selectors - Selector or array of selectors
     */
    function close(selectors) {
        const selectorArray = Array.isArray(selectors) ? selectors : [selectors];
        selectorArray.forEach(sel => {
            const $dropdown = $(sel);
            if ($dropdown.hasClass('show')) {
                bootstrap.Collapse.getOrCreateInstance($dropdown[0], { toggle: false }).hide();
            }
        });
    }

    /**
     * Toggle a dropdown menu, optionally closing others first.
     * @param {string} selector - Selector for the dropdown to toggle
     * @param {string[]} [others=[]] - Array of selectors for other dropdowns to close
     */
    function toggle(selector, others = []) {
        const $dropdown = $(selector);
        if ($dropdown.hasClass('show')) {
            close(selector);
        } else {
            if (others.length) {
                close(others);
            }
            open(selector);
        }
    }

    // Exported API
    return {
        open,
        close,
        toggle
    };
})();

export { Dropdowns };