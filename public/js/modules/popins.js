import { AppState } from "../appstate.js";
import { Dropdowns } from "./dropdowns.js";
import { Utility } from './utility.js';

export const Popins = {
    scrollY: 0,
    padRight: 0,

    lockBodyScroll() {
        this.scrollY = window.scrollY;
        this.padRight = window.innerWidth - document.body.clientWidth;

        $("body")
            .addClass("modal-open")
            .css({
                position: "fixed",
                top: -this.scrollY,
                width: "100%",
                paddingRight: this.padRight
            });
    },

    unlockBodyScroll() {
        $("body")
            .removeClass("modal-open")
            .css({
                position: "",
                top: "",
                width: "",
                paddingRight: ""
            });

        window.scrollTo(0, this.scrollY);
    },

    open(popinId) {;
        if (AppState.POPIN.open) return;
        Dropdowns.close(AppState.UI.MENU_DROPDOWN);
        this.lockBodyScroll();
        $(popinId).css("display", "").addClass("show");
        AppState.POPIN.open = popinId;
    },

    close(popinId) {
        const $popin = $(popinId);
        if (!$popin.length) return;

        if ($popin.hasClass("clear-on-close")) {
            this.clearFields($popin);
        }

        $popin.removeClass("show").css("display", "none");
        this.unlockBodyScroll();

        if (AppState.POPIN.open === popinId) {
            AppState.POPIN.open = null;
        }

        $popin.trigger("popin:close");
    },

    toggle(popinId) {
        const $popin = $(popinId);
        if ($popin.hasClass("show")) {
            this.close(popinId);
        } else {
            this.open(popinId);
        }
    },

    handleOutsideClick(event, closeDropdownFn) {
        if (AppState.POPIN.open) return;

        const insideDropdown = $(event.target).closest(AppState.UI.ITEM_DROPDOWN_SELECTOR).length > 0
                            || $(event.target).closest(AppState.UI.MENU_DROPDOWN).length > 0;

        if (insideDropdown) return;

        if (typeof closeDropdownFn === "function") {
            closeDropdownFn([
                AppState.UI.MENU_DROPDOWN,
                AppState.UI.SEARCH_DROPDOWN // if you have one
            ]);
        }
    },

    clearFields($popin) {
        $popin.find("select").each(function () {
            if (this.tomselect) {
                this.tomselect.destroy();
            }
        });

        $popin.find("input, select, textarea").each(function () {
            const $field = $(this);

            if ($field.is(":checkbox") || $field.is(":radio")) {
                $field.prop("checked", false);
            } else {
                $field.val("");
            }
        });

        $popin.find('div[class*="-tags-container"]').empty();
    },

    initFromHash() {
        const hash = window.location.hash;
        if (!hash) return;

        const $popin = $(hash);
        if (!$popin.length) return;

        this.open(hash);
        this.prepare(hash, null);

        history.replaceState(null, "", window.location.pathname + window.location.search);
    },

    prepare(popinId, triggerEl) {
        switch (popinId) {
            case AppState.UI.POPIN_STATUS_PERIOD:
                this.prepareStatusPeriodPopin();
                break;

            case AppState.UI.POPIN_CHANGE_STATUS:
                this.prepareChangeStatusPopin(triggerEl);
                break;

            case AppState.UI.POPIN_ADD_BOOK:
                this.prepareAddBookPopin();
                break;

            default:
                break;
        }
    },

    prepareStatusPeriodPopin() {
        Utility.request({
            url: "/requestPopinStatus",
            success: function (statuses) {
                const $select = $("#status-type");
                $select.empty().append(
                    '<option disabled selected hidden>Selecteer een status</option>'
                );

                statuses.forEach(status => {
                    $select.append(`
                        <option value="${status.id}"
                                data-period_length="${status.lengte ?? ''}"
                                data-reminder_day="${status.reminder ?? ''}"
                                data-overdue_day="${status.overdatum ?? ''}">
                            ${status.naam}
                        </option>
                    `);
                });

                if (window.__appFlash?.type === "status_type") {
                    $select.val(window.__appFlash.message);
                }

                $select.trigger("change");
            }
        });

        // Prefill inputs when selecting a status
        $("#status-type").off("change").on("change", function () {
            const $selected = $(this).find("option:selected");

            $("#period-length").val($selected.data("period_length") || "");
            $("#reminder-day").val($selected.data("reminder_day") || "");
            $("#overdue-day").val($selected.data("overdue_day") || "");
        });
    },

    prepareChangeStatusPopin(triggerEl) {
        const $field = $("#change-loaner-location");
        let bookId = $(triggerEl).data("book-id");

        if (!bookId) {
            const $openDropdown = $(".aletho-item-dropdown.show")
                .closest(".aletho-item-container");
            bookId = $openDropdown.length
                ? $openDropdown.attr("id")?.replace("item-container-", "")
                : null;
        }

        if (!bookId && window.__appFlash?.single?.book_id) {
            bookId = window.__appFlash.single.book_id;
        }

        $("#change-book-id").val(bookId || "");

        Utility.request({
            url: "/requestStatus",
            success: function (statuses) {
                const $select = $("#change-status-type");
                $select.empty().append(
                    '<option disabled selected hidden>Selecteer een status</option>'
                );
                statuses.forEach(status => {
                    $select.append(`<option value="${status.id}">${status.naam}</option>`);
                });
            }
        });

        Utility.request({
            url: "/requestLoanerForBook",
            data: { data: "book", book_id: bookId },
            success: function (loaner) {
                Utility.initTomSelectSingle($field, {
                    endpoint: "locaties",
                    mode: "status"
                }, function (ts) {
                    ts.setValue(loaner?.locatie || "_placeholder");
                });

                $("#change-loaner-name").val(loaner?.naam || "");
                $("#change-loaner-email").val(loaner?.email || "");
            }
        });
    },

    prepareAddBookPopin() {
        const $field = $("#book-office-add");
        Utility.initTomSelectSingle($field, {
            endpoint: "locaties",
            mode: "add"
        });
    }
};

// import { Utility } from './utility.js';
// import { AppState } from "../appstate.js";

// const Popins = (() => {
//     // Centralized selectors for all popins
//     const popinSelectors    = [
//         '#add-book-popin',
//         '#status-period-popin',
//         '#password-reset-popin',
//         '#change-book-status-popin'
//     ];
//     let scrollY             = 0;
//     let padRight            = 0;
//     let officeSelect        = null;

//     /*  Get all popin selectors as an array. */
//     function getSelectors() {
//         return popinSelectors;
//     }

//     /*  Lock body scroll and adjust padding when popin is open. */
//     function lockBodyScroll() {
//         scrollY = window.scrollY;
//         padRight = window.innerWidth - document.body.clientWidth;
//         $('body')
//             .addClass('modal-open')
//             .css({
//                 position: 'fixed',
//                 top: -scrollY,
//                 width: '100%',
//                 paddingRight: padRight
//             });
//     }

//     /*  Unlock body scroll and reset padding when popin is closed. */
//     function unlockBodyScroll() {
//         $('body')
//             .removeClass('modal-open')
//             .css({
//                 position: '',
//                 top: '',
//                 width: '',
//                 paddingRight: ''
//             });
//         window.scrollTo(0, scrollY);
//     }

//     /*  Open a popin by selector, lock scroll, and set open state. */
//     function open(selector) {
//         if (isOpen) return;
//         lockBodyScroll();
//         $(selector).show();
//         isOpen = true;

//         // Prefill status select for status-period-popin including the meta data for editing
//         if (selector === '#status-period-popin') {
//             Utility.request({
//                 url: '/requestPopinStatus',
//                 success: function (statuses) {
//                     const $select = $('#status-type');
//                     $select.empty().append('<option disabled selected hidden>Selecteer een status</option>');
//                     statuses.forEach(status => {
//                         $select.append(`
//                             <option value="${status.id}"
//                                     data-period_length="${status.lengte ?? ''}"
//                                     data-reminder_day="${status.reminder ?? ''}"
//                                     data-overdue_day="${status.overdatum ?? ''}">${status.naam}
//                             </option>
//                         `);
//                     });

//                     if (window.__appFlash && window.__appFlash.type === 'status_type') {
//                         $select.val(window.__appFlash.message);
//                     }

//                     $select.trigger('change');
//                 }
//             });
//         }

//         // onchange event for #status-period-popin taking the <option> metadata and prefill the <input>
//         $('#status-type').on('change', function() {
//             const $selected = $(this).find('option:selected');

//             $('#period-length').val($selected.data('period_length') || '');
//             $('#reminder-day').val($selected.data('reminder_day') || '');
//             $('#overdue-day').val($selected.data('overdue_day') || '');
//         });

//         if (selector === '#change-book-status-popin') {
//             const bookId = window.__appFlash && window.__appFlash.type === 'book_id' ? window.__appFlash.message : null;

//             if (bookId) {
//                 $('#change-book-id').val(bookId);
//                 // Now request loaner data to populate the status select ?
//                 Utility.request({
//                     url: '/requestLoanerForBook',
//                     data: { data: 'book', book_id: bookId },
//                     success: function(loaner) {
//                         $('#change-loaner-name').val(loaner.naam || '');
//                         $('#change-loaner-email').val(loaner.email || '');

//                         if (loaner && loaner.location) {
//                             officeSelect.setValue(loaner.locatie || '');
//                         }
//                     }
//                 });

//                 Utility.request({
//                     url: '/requestStatus',
//                     data: { data: 'book', book_id: bookId },
//                     success: function (statuses) {
//                         const $select = $('#change-status-type');
//                         $select.empty().append('<option disabled hidden>Selecteer een status</option>');
//                         statuses.forEach(status => {
//                             $select.append(`<option value="${status.id}">${status.naam}</option>`);
//                         });
//                     }
//                 });
//             }
//         }
//     }

//     /*  Clears all input, select, and textarea fields, and empties tag containers within a popin. */
//     function _clearFields($popin) {
//         // 1. Destroy all TomSelect instances
//         $popin.find('select').each(function() {
//             const select = this;
//             if (select.tomselect) {
//                 select.tomselect.destroy();
//             }
//         });

//         // 2. Reset all input/select/textarea values
//         $popin.find('input, select, textarea').each(function() {
//             const $field = $(this);

//             if ($field.is(':checkbox') || $field.is(':radio')) {
//                 $field.prop('checked', false);
//             } else {
//                 $field.val('');
//             }
//         });

//         // 3. Clear tag containers (legacy)
//         $popin.find('div[class*="-tags-container"]').empty();
//     }

//     /*  Close a popin by selector, unlock scroll, and reset open state. */
//     function close(selector) {
//         const $popin = $(selector);
//         if ($popin.length === 0) {
//             return;
//         }

//         // If the popin has the 'clear-on-close' class, clear its fields
//         if ($popin.hasClass('clear-on-close')) {
//             _clearFields($popin);
//         }

//         $popin.hide();
//         unlockBodyScroll();
//         isOpen = false;
//         // Trigger a custom event when the popin is closed.
//         $popin.trigger('popin:close');
//     }

//     // Exported API
//     return {
//         getSelectors,
//         open,
//         close
//     };
// })();

// export { Popins };

    /*  Setup open/close event handlers for a popin. */
//     function setup(openBtn, popinId, closeBtn) {
//         $(document).on('click', openBtn, function () {
//             open(popinId);

//             if (popinId === '#change-book-status-popin') {
//                 const $field = $('#change-loaner-location');
//                 let bookId = $(this).data('book-id');

//                 if (!bookId) {
//                     const $openDropdown = $('.aletho-item-dropdown.show').closest('.aletho-item-container');
//                     bookId = $openDropdown.length ? $openDropdown.attr('id')?.replace('item-container-', '') : null;
//                 }

//                 if (!bookId && window.__appFlash && window.__appFlash.single && window.__appFlash.single.book_id) {
//                     bookId = window.__appFlash.single.book_id;
//                 }

//                 $('#change-book-id').val(bookId || '');

//                 Utility.request({
//                     url: '/requestStatus',
//                     success: function (statuses) {
//                         const $select = $('#change-status-type');
//                         $select.empty().append('<option disabled selected hidden>Selecteer een status</option>');
//                         statuses.forEach(status => {
//                             $select.append(`<option value="${status.id}">${status.naam}</option>`);
//                         });
//                     }
//                 });

//                 Utility.request({
//                     url: '/requestLoanerForBook',
//                     data: { data: 'book', book_id: bookId },
//                     success: function(loaner) {
//                         officeSelect = Utility.initTomSelectSingle($field, {
//                             endpoint: 'locaties',
//                             mode: 'status'
//                         }, function(ts) {
//                             if (loaner && loaner.locatie) {
//                                 ts.setValue(loaner.locatie);
//                             } else {
//                                 ts.setValue('_placeholder');
//                             }
//                         });

//                         $('#change-loaner-name').val(loaner.naam || '');
//                         $('#change-loaner-email').val(loaner.email || '');
//                     }
//                 });
//             }

//             if (popinId === '#add-book-popin') {
//                 const $field = $('#book-office-add');
//                 officeSelect = Utility.initTomSelectSingle($field, {
//                     endpoint: 'locaties',
//                     mode: 'add'
//                 });
//             }
//         });

//         $(closeBtn).on('click', () => close(popinId));

//         const $popin = $(popinId);

//         if ($popin.hasClass('backdrop-close')) {
//             $popin.on('click', function (e) {
//                 if (e.target === this) {
//                     close(popinId);
//                 }
//             });
//         }
//     }

    /*  Open a popin if the URL hash matches a popin selector. */
//     function initFromHash() {
//         if (window.location.hash) {
//             const popinId = window.location.hash;
//             const $popin = $(popinId);

//             if ($popin.length) {
//                 open(popinId);
//                 history.replaceState(null, '', window.location.pathname + window.location.search);
//             }

//             if (popinId === '#change-book-status-popin') {
//                 const $field = $('#change-loaner-location');
//                 officeSelect = Utility.initTomSelectSingle($field, {
//                     endpoint: 'locaties',
//                     mode: 'status'
//                 });
//             }
//         }
//     }