let activeList = null;

export const Suggestions = {
    show($input, suggestions, suggestionClass) {
        this.close();
        if (!$input.length) return;

        const rect = $input[0].getBoundingClientRect();
        const rootFont = parseFloat(getComputedStyle(document.documentElement).fontSize) || 16;

        const topRem   = (rect.bottom / rootFont) + 'rem';
        const leftRem  = (rect.left   / rootFont) + 'rem';
        const widthRem = (rect.width  / rootFont) + 'rem';

        const $list = $(`<div class="suggestion-list ${suggestionClass}s"></div>`).css({
            position: 'fixed',
            top: topRem,
            left: leftRem,
            width: widthRem
        });

        suggestions.forEach(suggestion => {
            if (typeof suggestion === 'object' && suggestion !== null) {
                const id   = suggestion.id;
                const naam = suggestion.naam;

                $list.append(`
                    <div class="suggestion ${suggestionClass}"
                        data-id="${id}"
                        data-name="${naam}">
                        ${naam}
                    </div>
                `);
            } else {
                $list.append(`
                    <div class="suggestion ${suggestionClass}">
                        ${suggestion}
                    </div>
                `);
            }
        });

        $('body').append($list);
        activeList = $list;
    },

    close() {
        $('.suggestion-list').remove();
        activeList = null;
    }
};


// const Suggestions = (() => {
//     /*  Show a suggestion list below the given input. */
//     function show($input, suggestions, suggestionClass) {
//         close();
//         if (!$input.length) return;

//         const rect = $input[0].getBoundingClientRect();
//         const rootFont = parseFloat(getComputedStyle(document.documentElement).fontSize) || 16;
//         const topRem = (rect.bottom / rootFont) + 'rem';
//         const leftRem = (rect.left / rootFont) + 'rem';
//         const widthRem = (rect.width / rootFont) + 'rem';

//         const $list = $(`<div class="suggestion-list ${suggestionClass}s"></div>`).css({
//             position: 'fixed',
//             top: topRem,
//             left: leftRem,
//             width: widthRem
//         });

//         suggestions.forEach(suggestion => {
//             if (typeof suggestion === 'object' && suggestion !== null) {
//                 const id = suggestion.id;
//                 const naam = suggestion.naam;

//                 $list.append(`
//                     <div class="suggestion ${suggestionClass}"
//                         data-id="${id}"
//                         data-name="${naam}">
//                         ${naam}
//                     </div>
//                 `);
//             // TODO: Review if still relevant after the re-factor, most of the lookups are now ID based, and (should) require a ID in the suggestion list.
//             } else {
//                 $list.append(`
//                     <div class="suggestion ${suggestionClass}">
//                         ${suggestion}
//                     </div>
//                 `);
//             }
//         });

//         $('body').append($list);
//     }

//     /*  Close all suggestion lists on the page. */
//     function close() {
//         $('.suggestion-list').remove();
//     }

//     /*  Need to close it when focus is lost. */
//     function bindCloseOnBlur($input) {
//         $input.on('blur', function() {
//             setTimeout(() => {
//                 Suggestions.close();
//             }, 150);
//         });
//     }

//     return { show, close, bindCloseOnBlur };
// })();

// export { Suggestions };