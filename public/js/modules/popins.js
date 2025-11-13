import { Utility } from './utility.js';

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

        // Prefill status select for status-period-popin
        if (selector === '#status-period-popin') {
            Utility.request({
                url: '/requestStatus',
                success: function (statuses) {
                    const $select = $('#status-type');
                    $select.empty().append('<option disabled selected hidden>Selecteer een status</option>');
                    statuses.forEach(status => {
                        $select.append(`
                            <option value="${status.id}"
                                    data-periode_length="${status.periode_length ?? ''}"
                                    data-reminder_day="${status.reminder_day ?? ''}"
                                    data-overdue_day="${status.overdue_day ?? ''}">${status.type}
                            </option>
                        `);
                    });

                    // Pre-select status if present in flash
                    if (window.__appFlash && window.__appFlash.type === 'status_type') {
                        $select.val(window.__appFlash.message);
                    }

                    // Now trigger change to prefill fields
                    $select.trigger('change');
                }
            });
        }

        // status period popin input fill for #status-period-popin
        $('#status-type').on('change', function() {
            const $selected = $(this).find('option:selected');

            // Only set the value if the field is empty
            if (!$('#periode-length').val()) {
                $('#periode-length').val($selected.data('periode_length') || '');
            }
            if (!$('#reminder-day').val()) {
                $('#reminder-day').val($selected.data('reminder_day') || '');
            }
            if (!$('#overdue-day').val()) {
                $('#overdue-day').val($selected.data('overdue_day') || '');
            }
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
                        if (loaner && loaner.name) {
                            $('#change-loaner-name').val(loaner.name || '');
                            $('#change-loaner-email').val(loaner.email || '');
                            $('#change-loaner-location').val(loaner.location || '');
                        } else {
                            $('#change-loaner-name').val('');
                            $('#change-loaner-email').val('');
                            $('#change-loaner-location').val('');
                        }
                    }
                });

                Utility.request({
                    url: '/requestStatus',
                    data: { data: 'book', book_id: bookId },
                    success: function (statuses) {
                        const $select = $('#change-status-type');
                        $select.empty().append('<option disabled selected hidden>Selecteer een status</option>');
                        statuses.forEach(status => {
                            $select.append(`<option value="${status.id}">${status.type}</option>`);
                        });
                    }
                });

                Utility.request({
                    url: '/requestBookStatus',
                    data: { data: 'book', book_id: bookId },
                    success: function (status) {
                        const $select = $('#change-status-type');
                        const targetText = status[0].type;

                        const $match = $select.find('option').filter(function () {
                            return $(this).text().trim() === targetText;
                        });

                        if ($match.length) {
                            $select.val($match.val());
                        }
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

            // For change-book-status-popin, set book_id from triggering button
            if (popinId === '#change-book-status-popin') {
                // Try to get book_id from the clicked button
                let bookId = $(this).data('book-id');

                // Fallback: if not found, try to get from open dropdown (item-container)
                if (!bookId) {
                    const $openDropdown = $('.aletho-item-dropdown.show').closest('.aletho-item-container');
                    bookId = $openDropdown.length ? $openDropdown.attr('id')?.replace('item-container-', '') : null;
                }

                // Fallback: if still not found, try from window.__appFlash (for redirects)
                if (!bookId && window.__appFlash && window.__appFlash.single && window.__appFlash.single.book_id) {
                    bookId = window.__appFlash.single.book_id;
                }

                // Set the hidden input
                $('#change-book-id').val(bookId || '');

                // Now request status data as before
                Utility.request({
                    url: '/requestStatus',
                    success: function (statuses) {
                        const $select = $('#change-status-type');
                        $select.empty().append('<option disabled selected hidden>Selecteer een status</option>');
                        statuses.forEach(status => {
                            $select.append(`<option value="${status.id}">${status.type}</option>`);
                        });
                    }
                });

                Utility.request({
                    url: '/requestBookStatus',
                    data: { data: 'book', book_id: bookId },
                    success: function (status) {
                        const $select = $('#change-status-type');
                        const targetText = status[0].type;

                        const $match = $select.find('option').filter(function () {
                            return $(this).text().trim() === targetText;
                        });

                        if ($match.length) {
                            $select.val($match.val());
                        }
                    }
                });

                Utility.request({
                    url: '/requestLoanerForBook',
                    data: { data: 'book', book_id: bookId },
                    success: function(loaner) {
                        if (loaner && loaner.name) {
                            $('#change-loaner-name').val(loaner.name || '');
                            $('#change-loaner-email').val(loaner.email || '');
                            $('#change-loaner-location').val(loaner.location || '');
                        } else {
                            $('#change-loaner-name').val('');
                            $('#change-loaner-email').val('');
                            $('#change-loaner-location').val('');
                        }
                    }
                });
            }
            // You can add similar blocks for other popins here as needed
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
    return {
        getSelectors,
        open,
        close,
        setup,
        initFromHash,
        handleOutsideClick
    };
})();

export { Popins };