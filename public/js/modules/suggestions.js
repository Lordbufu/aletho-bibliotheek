const Suggestions = (() => {
    /*  Show a suggestion list below the given input. */
    function show($input, suggestions, suggestionClass) {
        close();
        if (!$input.length) return;

        const rect = $input[0].getBoundingClientRect();
        const $list = $(`<div class="suggestion-list ${suggestionClass}s"></div>`).css({
            position: 'fixed',
            top: rect.bottom + 'px',
            left: rect.left + 'px',
            width: rect.width + 'px',
            zIndex: 9999
        });

        suggestions.forEach(s => {
            $list.append(`<div class="${suggestionClass}">${s}</div>`);
        });

        $('body').append($list);
    }

    /*  Close all suggestion lists on the page. */
    function close() {
        $('.suggestion-list').remove();
    }

    /*  Need to close it when focus is lost. */
    function bindCloseOnBlur($input) {
        $input.on('blur', function() {
            setTimeout(() => {
                Suggestions.close();
            }, 150);
        });
    }

    return {
        show,
        close,
        bindCloseOnBlur
    };
})();

export { Suggestions };