// modules/dropdowns.js
const Dropdowns = (() => {
    /**
     * 
     */
    function open(selector) {
        const $dropdown = $(selector);
        bootstrap.Collapse.getOrCreateInstance($dropdown[0], { toggle: false }).show();
    }

    /**
     * 
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
     * 
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

    return { open, close, toggle };
})();