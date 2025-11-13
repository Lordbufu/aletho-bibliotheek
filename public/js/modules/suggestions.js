const Suggestions = (() => {
    /*  Show a suggestion list below the given input. */
    function show($input, suggestions, suggestionClass) {
        close();
        if (!$input.length) return;

        const rect = $input[0].getBoundingClientRect();
        const rootFont = parseFloat(getComputedStyle(document.documentElement).fontSize) || 16;
        const topRem = (rect.bottom / rootFont) + 'rem';
        const leftRem = (rect.left / rootFont) + 'rem';
        const widthRem = (rect.width / rootFont) + 'rem';

        const $list = $(`<div class="suggestion-list ${suggestionClass}s"></div>`).css({
            position: 'fixed',
            top: topRem,
            left: leftRem,
            width: widthRem
        });

        suggestions.forEach(s => {
            $list.append(`<div class="suggestion ${suggestionClass}">${s}</div>`);
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

    return { show, close, bindCloseOnBlur };
})();

export { Suggestions };