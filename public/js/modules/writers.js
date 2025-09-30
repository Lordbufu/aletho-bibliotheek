
import { Utility } from './utility.js';

/**
 * Writers module: Handles writer input autocomplete, tag management, and suggestion filtering.
 * - Fetches all writers once on page load
 * - Filters suggestions client-side
 * - Manages tag add/remove and input state
 */
const Writers = (() => {
    let removingTag = false;
    let selectingSuggestion = false;
    let allWriters = [];

    /**
     * Initialize writer input and tag container.
     * @param {string} inputSelector - Selector for the writer input field(s)
     * @param {string} containerSelector - Selector for the tag container(s)
     */
    function init(inputSelector, containerSelector) {
        const $input = $(inputSelector);
        const $container = $(containerSelector);

        // Fetch all writers once for autocomplete
        $.getJSON('/writers', function(data) { allWriters = data; });

        // Input handler: filter suggestions with debounce for performance
        $input.on('input', debounce(function() {
            const query = $input.val().trim().toLowerCase();
            if (query.length < 2) {
                closeSuggestions($input);
                return;
            }
            // Filter writers by partial match
            const suggestions = allWriters.filter(name =>
                name.toLowerCase().includes(query)
            );
            if (suggestions.length > 0) {
                showSuggestions($input, suggestions);
            } else {
                closeSuggestions($input);
            }
        }, 300));

        // Mousedown on suggestion: add tag before blur closes
        $container.on('mousedown', '.writer-suggestion', function(e) {
            selectingSuggestion = true;
            e.preventDefault();
            const name = $(this).text().trim();
            addTag(name, $input, $container);
            closeSuggestions($input);
            $input.focus();
            setTimeout(() => { selectingSuggestion = false; }, 0);
        });

        // Blur on input: keep suggestions open if selecting
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

        // Remove tag (delegated): removes tag and hidden input
        $container.on('click', '.remove-writer', function(e) {
            e.preventDefault();
            const $tag = $(this).closest('.writer-tag');
            $tag.find('input[type="hidden"][name="book_writers[]"]').remove();
            $tag.remove();
            Utility.markFieldChanged($input);
        });

        // Click handler for suggestion (global): add tag and close suggestions
        $(document).on('click', '.writer-suggestion', function() {
            const name = $(this).text();
            addTag(name, $input, $container);
            closeSuggestions($input);
        });

        // Add removing tag/flag on mousedown (global)
        $(document).on('mousedown', '.remove-writer', () => setRemoving(true));
    }

    /**
     * Debounce helper: limits function execution rate.
     * @param {Function} fn - Function to debounce
     * @param {number} delay - Delay in ms
     * @returns {Function}
     */
    function debounce(fn, delay) {
        let timer;
        return function(...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    /**
     * Set removingTag flag (used for blur logic)
     * @param {boolean} val
     */
    function setRemoving(val) {
        removingTag = val;
    }

    /**
     * Returns true if a tag is being removed (used for blur logic)
     * @returns {boolean}
     */
    function isRemoving() {
        return removingTag;
    }

    /**
     * Add a writer tag to the container, if not already present.
     * @param {string} name - Writer name
     * @param {jQuery} $input - Input field
     * @param {jQuery} $container - Tag container
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
        Utility.markFieldChanged($input);
    }

    /**
     * Get the tags container for a given input field.
     * @param {jQuery} $field - Input field
     * @returns {jQuery}
     */
    function getTagsContainer($field) {
        const bookId = $field.data('book-id');
        return $(`.writer-tags-container[data-book-id="${bookId}"]`);
    }

    /**
     * Get all writer values from a container, sorted and trimmed.
     * @param {jQuery} $container - Tag container
     * @returns {string[]}
     */
    function getValuesFromContainer($container) {
        return $container.find('input[name="book_writers[]"]').map(function() {
            return $(this).val().trim();
        }).get().filter(Boolean).sort();
    }

    /**
     * Show suggestions below the input field.
     * @param {jQuery} $input - Input field
     * @param {string[]} suggestions - Array of writer names
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
     * Remove the suggestions dropdown.
     * @param {jQuery} $input - Input field
     */
    function closeSuggestions($input) {
        $input.siblings('.writer-suggestions').remove();
    }

    // Exported API
    return {
        init,
        addTag,
        isRemoving,
        getTagsContainer,
        getValuesFromContainer
    };
})();

export { Writers };