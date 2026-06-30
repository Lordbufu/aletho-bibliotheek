/*  Events.js:
        This is file is intended to:
            - Bind all DOM element events
            - Call module function to resolve events
 */
import { AppState } from "./appstate.js";
import { Dropdowns } from "./modules/dropdowns.js";
import { Popins } from "./modules/popins.js";
import { SearchSort } from "./modules/searchSort.js";
import { TagInput } from "./modules/taginput.js";
import { Suggestions } from "./modules/suggestions.js";
import { Utility } from "./modules/utility.js";

export const Events = {
    /*  Init DOM bindings */
    init() {
        this.bindDropdownEvents();
        this.bindPopinEvents();
        this.bindSearchSortEvents();
        this.bindEditableFieldEvents();
        this.bindBookNameEvents();
        this.bindDeleteBookEvents();
        this.bindLoanerSuggestionEvents();
        this.bindTagInputEvents();
        /*  Singular suggestion event binding */
        $(document).on("blur", AppState.UI.TAG_INPUTS.join(', '), function () {
            setTimeout(() => Suggestions.close(), 150);
        });

        Popins.initFromHash();
        this.applyDefaultSort();
    },

    /*  DROPDOWNS */
    bindDropdownEvents() {
        $(document).on("click", AppState.UI.ITEM_BUTTON_SELECTOR, this.handleItemButtonClick);
        $(document).on("click", AppState.UI.HAMBURGER_BUTTON, this.handleHamburgerClick);
        $(document).on("click", (e) => {
            this.handleGlobalClick(e);
        });
    },

    /*  POPINS */
    bindPopinEvents() {
        // Open popins
        $(document).on("click", AppState.UI.BTN_ADD_BOOK, (e) => {
            Popins.open(AppState.UI.POPIN_ADD_BOOK);
            Popins.prepare(AppState.UI.POPIN_ADD_BOOK, e.currentTarget);
        });

        $(document).on("click", AppState.UI.BTN_STATUS_PERIOD, (e) => {
            Popins.open(AppState.UI.POPIN_STATUS_PERIOD);
            Popins.prepare(AppState.UI.POPIN_STATUS_PERIOD, e.currentTarget);
        });

        $(document).on("click", AppState.UI.BTN_PASSWORD_RESET, (e) => {
            Popins.open(AppState.UI.POPIN_PASSWORD_RESET);
            Popins.prepare(AppState.UI.POPIN_PASSWORD_RESET, e.currentTarget);
        });

        $(document).on("click", AppState.UI.BTN_CHANGE_STATUS, (e) => {
            Popins.open(AppState.UI.POPIN_CHANGE_STATUS);
            Popins.prepare(AppState.UI.POPIN_CHANGE_STATUS, e.currentTarget);
        });

        // Close popins
        $(document).on("click", AppState.UI.POPIN_CLOSE_ADD_BOOK, () => {
            Popins.close(AppState.UI.POPIN_ADD_BOOK);
        });

        $(document).on("click", AppState.UI.POPIN_CLOSE_STATUS_PERIOD, () => {
            Popins.close(AppState.UI.POPIN_STATUS_PERIOD);
        });

        $(document).on("click", AppState.UI.POPIN_CLOSE_PASSWORD_RESET, () => {
            Popins.close(AppState.UI.POPIN_PASSWORD_RESET);
        });

        $(document).on("click", AppState.UI.POPIN_CLOSE_CHANGE_STATUS, () => {
            Popins.close(AppState.UI.POPIN_CHANGE_STATUS);
        });

        // Backdrop close
        $(document).on("click", ".modal.backdrop-close", function (e) {
            if (e.target === this) {
                Popins.close("#" + this.id);
            }
        });
    },

    /*  SEARCH & SORT */
    bindSearchSortEvents() {
        $(document).on("input", AppState.UI.SEARCH_INPUT, (e) => {
            this.handleSearchInput(e);
        });

        $(document).on("change", AppState.UI.SEARCH_OPTIONS, (e) => {
            this.handleSearchModeChange(e);
        });

        $(document).on("click", AppState.UI.SEARCH_CLEAR_ICON, () => {
            this.handleClearSearch();
        });

        $(document).on("change", AppState.UI.SORT_OPTIONS, (e) => {
            this.handleSortChange(e);
        });
    },

    /*  EDITABLE FIELDS (BOOK DETAILS) */
    bindEditableFieldEvents() {
        // Make field editable
        $(document).on("click", ".extra-button-style", function (event) {
            event.stopPropagation();

            const selector = $(this).data("swapTargets");
            const $field = $(selector);

            if (!$field.prop("disabled")) return;

            if ($field.hasClass("locatie-input")) {
                Utility.initTomSelectSingle($field, {
                    endpoint: "locaties",
                    mode: "edit"
                });
                setTimeout(() => $field.focus(), 0);
                return;
            }

            const config = Utility.getFieldConfig($field);

            if (config.isTaggable) {
                Utility.markFieldChanged($field);
                const $container = TagInput.getTagsContainer($field, config.containerSelector);
                TagInput.restoreTagsFromInput($field, $container, config.tagClass, config.hiddenInputName);

                if (!$field.data("originalValue")) {
                    $field.data("originalValue", "");
                }
            } else {
                Utility.markFieldChanged($field);
                $field.data("originalValue", $field.val());
                $field.prop("disabled", false);
            }

            $field.prop("disabled", false).addClass("field-editable");
            setTimeout(() => $field.focus(), 0);
        });

        // Input/change listener
        $(document).on("input change", "input.field-editable, select.field-editable", function () {
            if (AppState.POPIN.open) return;

            const $field = $(this);
            const original = $field.data("originalValue");
            const current = $field.val();

            if (current !== original) {
                Utility.markFieldChanged($field);
            } else {
                Utility.clearFieldChanged($field);
            }
        });

        // Blur revert logic
        $(document).on("blur", "input.field-editable", function () {
            const $field = $(this);

            setTimeout(() => {
                const $group = $field.closest(".input-group");
                if ($group.find(":focus").length) return;

                const config = Utility.getFieldConfig($field);
                const original = $field.data("originalValue");
                let current;

                if (config.isTaggable) {
                    const $container = TagInput.getTagsContainer($field, config.containerSelector);
                    const currentValues = TagInput.getValuesFromContainer($container, config.hiddenInputName);
                    current = Utility.normalizeValues(currentValues);

                    if (current === original) {
                        $field.val(currentValues.join(", "));
                        $container.empty();
                        $field.prop("disabled", true)
                            .removeClass("field-editable field-changed")
                            .removeData("originalValue");
                        Utility.clearFieldChanged($field);
                    }
                } else {
                    current = $field.val();
                    if (current === original) {
                        $field.prop("disabled", true)
                            .removeClass("field-editable field-changed")
                            .removeData("originalValue");
                        Utility.clearFieldChanged($field);
                    }
                }
            }, 200);
        });
    },

    /*  BOOK NAME ENTER KEY */
    bindBookNameEvents() {
        $(document).on("keydown", '[id^="book-name-"]', function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                const $input = $(this);
                const value = $input.val().trim();
                if (value) $input.blur();
            }
        });
    },

    /*  DELETE BOOK BUTTON */
    bindDeleteBookEvents() {
        $(document).on("click", ".delete-book-btn", function () {
            const bookId = $(this).data("book-id");
            $("#delete-book-id").val(bookId);
            $("#shared-delete-form").trigger("submit");
        });
    },

    /*  LOANER NAME SUGGESTIONS */
    bindLoanerSuggestionEvents() {
        $(document).on("input", "#change-loaner-name", function () {
            const $input = $(this);
            const query = $input.val().trim();

            if (query.length < 2) {
                Suggestions.close();
                return;
            }

            Utility.request({
                url: "/requestLoaners",
                data: { query },
                success: function (list) {
                    if (!Array.isArray(list) || list.length === 0) {
                        Suggestions.close();
                        return;
                    }

                    Suggestions.show($input, list, "loaner-suggestion");
                    Suggestions.bindCloseOnBlur($input);

                    $(document)
                        .off("mousedown.loaner-suggestion")
                        .on("mousedown.loaner-suggestion", ".loaner-suggestion", function (e) {
                            e.preventDefault();
                            const id = $(this).data("id");
                            const selected = list.find(l => l.id === id);
                            const ts = $("#change-loaner-location")[0]?.tomselect;

                            if (selected) {
                                $input.val(selected.naam);
                                $("#change-loaner-email").val(selected.email || "");
                                if (ts) ts.setValue(selected.locatie || "", false);
                            }

                            Suggestions.close();
                        });
                }
            });
        });
    },

    /*  TAG INPUT EVENTS */
    bindTagInputEvents() {
        const inputs      = AppState.UI.TAG_INPUTS.join(', ');
        const suggestions = AppState.UI.TAG_SUGGESTIONS.join(', ');
        const removers    = AppState.UI.TAG_REMOVE_BUTTONS.join(', ');

        $(document).on("focus", inputs, e => TagInput.setActive($(e.currentTarget)));
        $(document).on("blur", inputs, e => TagInput.clearActiveDelayed());
        $(document).on("input", inputs, e => TagInput.handleInput($(e.currentTarget)));
        $(document).on("keydown", inputs, e => {
            if (e.key === "Enter") {
                e.preventDefault();
                TagInput.handleEnter($(e.currentTarget));
            }
        });

        $(document).on("click", suggestions, e => {
            e.preventDefault();
            TagInput.handleSuggestionClick($(e.currentTarget));
        });

        $(document).on("mousedown", suggestions, e => e.preventDefault());

        $(document).on("click", removers, e => {
            e.preventDefault();
            TagInput.handleRemoveTag($(e.currentTarget));
        });

        // Prevent blur when clicking remove buttons
        $(document).on("mousedown", removers, function (e) {
            e.preventDefault();
        });
    },

    /*  Dropdown click handlers */
    handleGlobalClick(event) {
        Popins.handleOutsideClick(event, (selectors) => {
            Dropdowns.close(selectors);
        });

        const isInsideMenu = $(event.target).closest(AppState.UI.MENU_DROPDOWN).length > 0;
        const isInsideItem = $(event.target).closest(AppState.UI.ITEM_DROPDOWN_SELECTOR).length > 0;

        if (!isInsideMenu && !isInsideItem) {
            Dropdowns.close(AppState.UI.MENU_DROPDOWN);

            if (AppState.RULES.CLOSE_ITEMS_ON_OUTSIDE_CLICK) {
                Dropdowns.closeAll(AppState.UI.ITEM_DROPDOWN_SELECTOR);
            }
        }
    },

    handleItemButtonClick(event) {
        event.stopPropagation();

        const targetId = $(event.currentTarget).attr("data-bs-target");

        Dropdowns.toggle(targetId, {
            closeGroup: AppState.RULES.SINGLE_ITEM_OPEN
                ? AppState.UI.ITEM_DROPDOWN_SELECTOR
                : null,
            closeMenu: AppState.RULES.CLOSE_MENU_WHEN_ITEM_OPENS
        });
    },

    handleHamburgerClick(event) {
        event.stopPropagation();

        Dropdowns.toggle(AppState.UI.MENU_DROPDOWN, {
            closeGroup: AppState.RULES.CLOSE_ITEMS_WHEN_MENU_OPENS
                ? AppState.UI.ITEM_DROPDOWN_SELECTOR
                : null
        });
    },

    /*  Search and sort handlers */
    handleSearchInput(event) {
        const query = $(event.currentTarget).val().toLowerCase().trim();
        const method = $(AppState.UI.SEARCH_OPTIONS).val();
        SearchSort.search(query, method);
        $(AppState.UI.SEARCH_CLEAR_ICON).css("display", query ? "block" : "none");
    },

    handleSearchModeChange(event) {
        const method = $(event.currentTarget).val();
        SearchSort.updatePlaceholder(method);
        $(AppState.UI.SEARCH_INPUT).val("");
        SearchSort.search("", method);
    },

    handleClearSearch() {
        $(AppState.UI.SEARCH_INPUT).val("").trigger("input");
        $(AppState.UI.SEARCH_CLEAR_ICON).hide();
        $(AppState.UI.SEARCH_INPUT).focus();
    },

    handleSortChange(event) {
        const [field, direction] = $(event.currentTarget).val().split("-");
        SearchSort.sort(field, direction);
    },

    /*  Set default sort orde for the page on init */
    applyDefaultSort() {
        const defaultValue = AppState.DEFAULTS.SORT;

        const $sort = $(AppState.UI.SORT_OPTIONS);
        $sort.val(defaultValue);

        const [field, direction] = defaultValue.split("-");
        SearchSort.sort(field, direction);
    }
};