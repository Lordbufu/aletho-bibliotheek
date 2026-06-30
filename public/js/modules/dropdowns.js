import { AppState } from "../appstate.js";

export const Dropdowns = {
    open(selector) {
        $(selector).addClass("show");
    },

    close(selectors) {
        const list = Array.isArray(selectors) ? selectors : [selectors];
        list.forEach(sel => $(sel).removeClass("show"));
    },

    closeAll(selector) {
        $(selector).removeClass("show");
    },

    closeOthers(targetSelector, groupSelector) {
        $(groupSelector)
            .filter(".show, .collapsing")
            .not(targetSelector)
            .each(function() {
                bootstrap.Collapse.getOrCreateInstance(this, { toggle: false }).hide();
            });
    },

    toggle(selector, { closeGroup = null, closeMenu = false } = {}) {
        const isItem = selector.startsWith("#customItemDropdown");

        if (isItem) {
            const currentlyOpen = AppState.dropdownState.openItem;

            if (currentlyOpen && currentlyOpen !== selector) {
                bootstrap.Collapse.getOrCreateInstance(currentlyOpen, { toggle: false }).hide();
            }

            bootstrap.Collapse.getOrCreateInstance(selector, { toggle: false }).show();
            AppState.dropdownState.openItem = selector;
            return;
        }

        const isMenuOpen = AppState.dropdownState.menuOpen;

        if (isMenuOpen) {
            bootstrap.Collapse.getOrCreateInstance(selector, { toggle: false }).hide();
            AppState.dropdownState.menuOpen = false;
        } else {
            bootstrap.Collapse.getOrCreateInstance(selector, { toggle: false }).show();
            AppState.dropdownState.menuOpen = true;
        }
    }
};