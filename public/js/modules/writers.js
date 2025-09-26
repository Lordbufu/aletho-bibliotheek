// modules/writers.js
const Writers = (() => {
    let removingTag = false;

    /**
     * 
     */
    function init(inputSelector, containerSelector) {
        const $input = $(inputSelector);
        const $container = $(containerSelector);

        // typing handler with debounce
        $input.on('input', debounce(function() {
            const query = $input.val().trim();

            if (query.length < 2) {
                closeSuggestions($input);
                return;
            }

            $.getJSON('/writers', { query }, function(suggestions) {
                showSuggestions($input, suggestions);
            });
        }, 300));

        // click handler for suggestion
        $(document).on('click', '.writer-suggestion', function() {
            const name = $(this).text();
            addTag(name, $container, $input);
            closeSuggestions($input);
        });

        // removing tag flag ?
        $(document).on('mousedown', '.writer-tag .remove', () => setRemoving(true));

        // remove tag
        $container.on('click', '.writer-tag .remove', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $tag = $(e.currentTarget).closest('.writer-tag');
            const $input = $tag.closest('.input-group').find('.writer-input');

            $tag.remove();
            markFieldChanged($input);
            setRemoving(false);
        });
    }

    /**
     * 
     */
    function debounce(fn, delay) {
        let timer;

        return function(...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    function setRemoving(val) { removingTag = val; }
    function isRemoving() { return removingTag; }

    /**
     * 
     */
    function addTag(name, $input) {
        const $group = $input.closest('.input-group');

        // prevent duplicates
        if ($group.find(`.writer-tag[data-name="${name}"]`).length) {
            return;
        }

        const $tag = $(`
            <span class="writer-tag" data-name="${name}">
                ${name}
                <button type="button" class="remove" aria-label="Remove">&times;</button>
            </span>
        `);

        $group.before($tag);

        $input.val('');
    }

    // /**
    //  * 
    //  */
    // function removeTag($tag) {
    //     $tag.remove();
    // }

    /**
     * 
     */
    function showSuggestions($input, suggestions) {
        const $list = $('<div class="writer-suggestions"></div>');

        suggestions.forEach(s => {
            $list.append(`<div class="writer-suggestion">${s}</div>`);
        });

        closeSuggestions($input);
        $input.after($list);
    }

    function closeSuggestions($input) {
        $input.siblings('.writer-suggestions').remove();
    }

    return { init, addTag, isRemoving };
})();

// -- From the document on load loop.
    // /**
    //  * Get all stored writers from PhP, and give a autofill option if applicable
    //  * Using the 'debounce' helper to limit the amount of calls.
    //  */
    // $('.writer-input').on('input', debounce(function() {
    //     const $input = $(this);
    //     const query = $input.val().trim();

    //     if (query.length < 2) {
    //         closeSuggestions($input);
    //         return;
    //     }

    //     $.getJSON('/writers', { query: query }, function(suggestions) {
    //         showSuggestions($input, suggestions);
    //     });
    // }, 300));

    // /**
    //  * For the writer-input specifically, tags need to be added after Enter is pressed.
    //  */
    // $(document).on('keydown', '.writer-input', function(e) {
    //     if (e.key === 'Enter') {
    //         e.preventDefault();
    //         const $input = $(this);
    //         const value  = $input.val().trim();
            
    //         if (value) {
    //             addWriterTag($input, value);
    //             $input.val('');
    //         }
    //     }
    // });


// /** W.I.P.
//  * Generic helper: Create book writer/genre tags, above the input group.
//  */
// function addWriterTag($input, text, markChanged = true) {
//     const normalized = text.trim().toLowerCase();
//     let exists = false;

//     $input.siblings('.writer-tag').each(function() {
//         const existing = $(this).clone().children().remove().end().text().trim().toLowerCase();

//         if (existing === normalized) {
//             exists = true;
//             return false;
//         }
//     });

//     if (exists) {
//         $input.siblings('.writer-tag').filter(function() {
//             return $(this).clone().children().remove().end().text().trim().toLowerCase() === normalized;
//         }).addClass('duplicate-flash');

//         setTimeout(() => {
//             $input.siblings('.writer-tag')
//                 .removeClass('duplicate-flash');
//         }, 500);

//         return;
//     }

//     const $tag = $('<span class="writer-tag">').text(text);
//     const $remove = $('<button type="button">×</button>').on('click', function() {
//             $tag.remove();
//             markFieldChanged($input);
//         });

//     $tag.append($remove);

//     $input.closest('.input-group').before($tag);

//     const $hidden = $('<input type="hidden" name="book_writers[]">').val(text);
//     $tag.append($hidden);
//     if (markChanged) {
//         markFieldChanged($input);
//     }
// }

// /** W.I.P.
//  * Generic helper: Show autocomplete suggestions for book writers/genres
//  */
// function showSuggestions($input, suggestions) {
//     closeSuggestions($input);

//     const $list = $('<ul class="writer-suggestions">');
//     suggestions.forEach(name => {
//         const $item = $('<li>').text(name);

//         $item.on('click', function() {
//             addWriterTag($input, name);
//             $input.val('');
//             closeSuggestions($input);
//         });

//         $list.append($item);
//     });

//     $input.after($list);
// }

// /** W.I.P.
//  * Generic helper: Close autocomplete suggestions for book writers/genres.
//  */
// function closeSuggestions($input) {
//     $input.siblings('.writer-suggestions').remove();
// }

// /**
//  * Generic helper: Debouncer to no overstress JS with checking user inputs.
//  */
// function debounce(fn, delay) {
//     let timer;
//     return function(...args) {
//         clearTimeout(timer);
//         timer = setTimeout(() => fn.apply(this, args), delay);
//     };
// }