import { Utility } from './utility.js';

const Popins = (() => {
    // Centralized selectors for all popins
    const popinSelectors    = [
        '#add-book-popin',
        '#status-period-popin',
        '#password-reset-popin',
        '#change-book-status-popin'
    ];
    let isOpen              = false;
    let scrollY             = 0;
    let padRight            = 0;
    let officeSelect        = null;

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

        // Prefill status select for status-period-popin including the meta data for editing
        if (selector === '#status-period-popin') {
            Utility.request({
                url: '/requestPopinStatus',
                success: function (statuses) {
                    const $select = $('#status-type');
                    $select.empty().append('<option disabled selected hidden>Selecteer een status</option>');
                    statuses.forEach(status => {
                        $select.append(`
                            <option value="${status.id}"
                                    data-period_length="${status.lengte ?? ''}"
                                    data-reminder_day="${status.reminder ?? ''}"
                                    data-overdue_day="${status.overdatum ?? ''}">${status.naam}
                            </option>
                        `);
                    });

                    if (window.__appFlash && window.__appFlash.type === 'status_type') {
                        $select.val(window.__appFlash.message);
                    }

                    $select.trigger('change');
                }
            });
        }

        // onchange event for #status-period-popin taking the <option> metadata and prefill the <input>
        $('#status-type').on('change', function() {
            const $selected = $(this).find('option:selected');

            $('#period-length').val($selected.data('period_length') || '');
            $('#reminder-day').val($selected.data('reminder_day') || '');
            $('#overdue-day').val($selected.data('overdue_day') || '');
        });

        if (selector === '#change-book-status-popin') {
            const bookId = window.__appFlash && window.__appFlash.type === 'book_id' ? window.__appFlash.message : null;

            if (bookId) {
                $('#change-book-id').val(bookId);
                // Now request loaner data to populate the status select ?
                Utility.request({
                    url: '/requestLoanerForBook',
                    data: { data: 'book', book_id: bookId },
                    success: function(loaner) {
                        $('#change-loaner-name').val(loaner.naam || '');
                        $('#change-loaner-email').val(loaner.email || '');

                        if (loaner && loaner.location) {
                            officeSelect.setValue(loaner.locatie || '');
                        }
                    }
                });

                Utility.request({
                    url: '/requestStatus',
                    data: { data: 'book', book_id: bookId },
                    success: function (statuses) {
                        const $select = $('#change-status-type');
                        $select.empty().append('<option disabled hidden>Selecteer een status</option>');
                        statuses.forEach(status => {
                            $select.append(`<option value="${status.id}">${status.naam}</option>`);
                        });
                    }
                });
            }
        }
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
    function setup(openBtn, popinId, closeBtn) {
        $(document).on('click', openBtn, function () {
            open(popinId);

            if (popinId === '#change-book-status-popin') {
                Utility.request({
                    url: '/bookData',
                    data: { data: 'locaties' },
                    success: function(list) {
                        const $select = $('#change-loaner-location');

                        list.forEach(o => {
                            $select.append(`<option value="${o.naam}">${o.naam}</option>`);
                        });

                        if (!officeSelect) {
                            officeSelect = new TomSelect('#change-loaner-location', {
                                maxItems: 1,
                                create: false,
                                controlInput: null,
                            });
                        }
                    }
                });

                let bookId = $(this).data('book-id');

                if (!bookId) {
                    const $openDropdown = $('.aletho-item-dropdown.show').closest('.aletho-item-container');
                    bookId = $openDropdown.length ? $openDropdown.attr('id')?.replace('item-container-', '') : null;
                }

                if (!bookId && window.__appFlash && window.__appFlash.single && window.__appFlash.single.book_id) {
                    bookId = window.__appFlash.single.book_id;
                }

                $('#change-book-id').val(bookId || '');

                Utility.request({
                    url: '/requestStatus',
                    success: function (statuses) {
                        const $select = $('#change-status-type');
                        $select.empty().append('<option disabled selected hidden>Selecteer een status</option>');
                        statuses.forEach(status => {
                            $select.append(`<option value="${status.id}">${status.naam}</option>`);
                        });
                    }
                });

                Utility.request({
                    url: '/requestLoanerForBook',
                    data: { data: 'book', book_id: bookId },
                    success: function(loaner) {
                        if (loaner && loaner.locatie) {
                            officeSelect.setValue(loaner.locatie || '');
                        } else {
                            officeSelect.setValue('_placeholder');
                        }

                        $('#change-loaner-name').val(loaner.naam || '');
                        $('#change-loaner-email').val(loaner.email || '');
                    }
                });
            }
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

            if (popinId === '#change-book-status-popin') {
                Utility.request({
                    url: '/bookData',
                    data: { data: 'locaties' },
                    success: function(list) {
                        const $select = $('#change-loaner-location');

                        list.forEach(o => {
                            $select.append(`<option value="${o.naam}">${o.naam}</option>`);
                        });

                        if (!officeSelect) {
                            officeSelect = new TomSelect('#change-loaner-location', {
                                maxItems: 1,
                                create: false,
                                controlInput: null,
                            });
                        }
                    }
                });
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

    /** */
    function setOfficeLocation(value) {
        if (officeSelect) {
            officeSelect.setValue(value);
        }
    }

    // Exported API
    return {
        getSelectors,
        open,
        close,
        setup,
        initFromHash,
        handleOutsideClick,
        setOfficeLocation
    };
})();

export { Popins };