/*  AppState.js:
        This is file is intended to contain:
            - global datasets
            - cached XHR results
            - constatns used across modules
            - user selections
            - tag lists
            - anything that multople modules need
 */
export const AppState = {
    /*  UI related datasets */
    UI: {
        /*  Dropdown selectors */
        MENU_DROPDOWN: "#customHamburgerDropdown",
        ITEM_DROPDOWN_SELECTOR: ".collapse.aletho-item-dropdown",
        HAMBURGER_BUTTON: "#hamburgerButton",
        ITEM_BUTTON_SELECTOR: '[id^="itemButton-"]',
        
        /*  Search and sort selectors */
        SEARCH_INPUT: "#search-inp",
        SEARCH_CLEAR_ICON: "#clear-search-icon",
        SEARCH_OPTIONS: "#search-options",
        SORT_OPTIONS: "#sort-options",
        ITEM_CONTAINER: ".aletho-item-container",
        ITEMS_WRAPPER: ".items-list",
        SORT_OPTIONS: "#sort-options",

        /*  Popin related selectors */
        POPIN_ADD_BOOK: "#add-book-popin",
        POPIN_STATUS_PERIOD: "#status-period-popin",
        POPIN_PASSWORD_RESET: "#password-reset-popin",
        POPIN_CHANGE_STATUS: "#change-book-status-popin",

        /*  Popin action buttons selectors */
        BTN_ADD_BOOK: "#boek-add-button",
        BTN_STATUS_PERIOD: "#status-periode-button",
        BTN_PASSWORD_RESET: "#password-change-button",
        BTN_CHANGE_STATUS: ".boek-status-button",

        /*  Popin close buttons selectors */
        POPIN_CLOSE_ADD_BOOK: "#close-add-book-popin",
        POPIN_CLOSE_STATUS_PERIOD: "#close-status-period-popin",
        POPIN_CLOSE_PASSWORD_RESET: "#close-password-reset-popin",
        POPIN_CLOSE_CHANGE_STATUS: "#close-change-book-status-popin",

        /*  Tag related selectors */
        TAG_INPUTS: [ ".schrijver-input", ".genre-input", ".schrijver-input-pop", ".genre-input-pop" ],
        TAG_SUGGESTIONS: [ ".schrijver-suggestion", ".genre-suggestion", ".schrijver-suggestion-pop", ".genre-suggestion-pop" ],
        TAG_REMOVE_BUTTONS: [
            ".schrijver-tags-container .remove-schrijver-tag",
            ".genre-tags-container .remove-genre-tag",
            ".add-schrijver-tags-container .remove-schrijver-tag",
            ".add-genre-tags-container .remove-genre-tag"
        ],

        /* Suggestion related selectors */
        SUGGESTION_LIST: ".suggestion-list",
        SUGGESTION_ITEM: ".suggestion"
    },

    /*  UI rules to determine what to close when */
    RULES: {
        CLOSE_ITEMS_ON_OUTSIDE_CLICK: false,
        CLOSE_ITEMS_WHEN_MENU_OPENS: false,
        CLOSE_MENU_WHEN_ITEM_OPEN: true,
        SINGLE_ITEM_OPEN: true,
    },

    /*  Default app state */
    DEFAULTS: { SORT: "title-asc" },
    POPIN: { open: null },

    /*  Popin related datasets */
    popinState: { isOpen: false },
    dropdownState: { openItem: null, menuOpen: false },

    /*  Tag, Suggestion and XHR related data stores */
    pendingRequests: {},
    optionsCache: {},
    tagConfigs: {},
    tagInputSelectors: [],
    tagSuggestionSelectors: [],
    tagRemoveSelectors: []
};