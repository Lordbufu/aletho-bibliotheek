// modules/writers.js
import { markFieldChanged, clearFieldChanged } from "../main.js";

const Writers = (() => {
    let removingTag = false;

    /**
     * 
     */
    function init(inputSelector, containerSelector) {
        const $input = $(inputSelector);
        const $container = $(containerSelector);
        let selectingSuggestion = false;

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

        // Mousedown on suggestion: add tag before blur closes
        $container.on('mousedown', '.writer-suggestion', function(e) {
            selectingSuggestion = true;
            e.preventDefault();
            const name = $(this).text().trim();
            addTag(name, $input, $container);
            closeSuggestions($input);
            $input.focus();
            setTimeout(() => {
                selectingSuggestion = false;
            }, 0);
        });

        // Blur on input: keep open if selecting suggestion
        $input.on('blur', function() {
            setTimeout(() => {
                if (!selectingSuggestion) {
                    closeSuggestions($input);
                }
            }, 150);
        });

        // Enter key: prevent form submit, add tag
        $input.on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();

                const name = $input.val().trim();
                if (name) {
                    addTag(name, $input, $container);
                }
            }
        });

        // Remove tag (delegated)
        $container.on('click', '.remove-writer', function(e) {
            e.preventDefault();
            const $tag = $(this).closest('.writer-tag');
            // Remove hidden input to avoid empty slots in POST
            $tag.find('input[type="hidden"][name="book_writers[]"]').remove();
            $tag.remove();
            markFieldChanged($input);
        });

        // click handler for suggestion
        $(document).on('click', '.writer-suggestion', function() {
            const name = $(this).text();
            addTag(name, $input, $container);
            closeSuggestions($input);
        });

        // Add removing tag/flag on mousedown
        $(document).on('mousedown', '.remove-writer', () => setRemoving(true));
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

    /**
     * 
     */
    function setRemoving(val) {
        removingTag = val;
    }

    /**
     * 
     */
    function isRemoving() {
        return removingTag;
    }

    /**
     * 
     */
    function addTag(name, $input, $container) {
        if ($container.find(`.writer-tag[data-name="${name}"]`).length) {
            return;
        }

        const $tag = $(`
            <span class="writer-tag" data-name="${name}">
                ${name}
                <button type="button" class="remove-writer" aria-label="Remove">&times;</button>
                <input type="hidden" name="book_writers[]" value="${name}">
            </span>
        `);

        
        $container.append($tag);
        $input.val('');
        markFieldChanged($input);
    }

    /**
     * 
     */
    function getTagsContainer($field) {
        const bookId = $field.data('book-id');
        return $(`.writer-tags-container[data-book-id="${bookId}"]`);
    }

    /**
     * 
     */
    function getValuesFromContainer($container) {
        return $container.find('input[name="book_writers[]"]').map(function() {
            return $(this).val().trim();
        }).get().filter(Boolean).sort();
    }

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

    /**
     * 
     */
    function closeSuggestions($input) {
        $input.siblings('.writer-suggestions').remove();
    }

    return { init, addTag, isRemoving, getTagsContainer, getValuesFromContainer };
})();

export { Writers };