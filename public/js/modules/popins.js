// modules/popins.js
export const Popins = (() => {
    let isOpen = false;
    let scrollY = 0;
    let padRight = 0;

    /**
     * 
     */
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

    /**
     * 
     */
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

    /**
     * 
     */
    function open(selector) {
        if (isOpen) {
            return;
        }

        lockBodyScroll();
        $(selector).show();
        isOpen = true;
    }

    /**
     * 
     */
    function close(selector) {
        $(selector).hide();
        unlockBodyScroll();
        isOpen = false;
    }

    /**
     * 
     */
    function setup(openBtn, popinId, closeBtn) {
        $(openBtn).on('click', () => open(popinId));
        $(closeBtn).on('click', () => close(popinId));

        $(popinId).on('click', function (e) {
            if (e.target === this) {
                close(popinId);
            }
        });
    }

    /**
     * 
     */
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

    /**
     * 
     */
    function handleOutsideClick(event, popinSelectors, closeDropdownFn) {
        if (!isOpen) {
            return;
        }

        const ePopin = $(event.target).closest(popinSelectors.join(',') + ':visible');
        if (ePopin.length > 0) {
            return;
        }

        if (typeof closeDropdownFn === 'function') {
            closeDropdownFn(['#customHamburgerDropdown', '#customSearchDropdown']);
        }
    }

    return { open, close, setup, initFromHash, handleOutsideClick };
})();
