import { Utility } from './utility.js';

/** TagInput module: Generic tag input for autocomplete, tag management, and suggestion filtering:
 *      - Fetches all options once on page load (writers, genres, offices, etc.)
 *      - Filters suggestions client-side
 *      - Manages tag add/remove and input state
 *      - Supports limiting the number of tags (maxTags)
 *      - Shows a tooltip if user tries to exceed maxTags
 *
 * Usage:
 *   TagInput.init({
 *     inputSelector: '.writer-input',
 *     containerSelector: '.writer-tags-container',
 *     endpoint: '/writers',
 *     tagClass: 'writer-tag',
 *     suggestionClass: 'writer-suggestion',
 *     hiddenInputName: 'book_writers[]',
 *     maxTags: 1 // Optional: limit to 1 tag (e.g. for offices)
 *   });
 */
const TagInput = (() => {
    let removingTag = false;
    let selectingSuggestion = false;

    /** Initialize tag input and tag container.
     *      @param {Object} config - Configuration object
     *      @param {string} config.inputSelector - Selector for the input field(s)
     *      @param {string} config.containerSelector - Selector for the tag container(s)
     *      @param {string} config.endpoint - API endpoint to fetch options
     *      @param {string} config.tagClass - CSS class for tags
     *      @param {string} config.suggestionClass - CSS class for suggestions
     *      @param {string} config.hiddenInputName - Name for hidden input(s)
     *      @param {number} [config.maxTags] - Optional: maximum number of tags allowed
     */
    function init(config) {
        const $input = $(config.inputSelector);
        const $container = $(config.containerSelector);
        let allOptions = [];
        const maxTags = config.maxTags || null;

        // Fetch all options once for autocomplete
        $.getJSON(config.endpoint, function(data) { allOptions = data; });

        // Input handler: filter suggestions with debounce for performance
        $input.on('input', debounce(function() {
            const query = $input.val().trim().toLowerCase();
            if (query.length < 2) {
                closeSuggestions($input, config.suggestionClass);
                return;
            }
            // Filter options by partial match
            const suggestions = allOptions.filter(name =>
                name.toLowerCase().includes(query)
            );
            if (suggestions.length > 0) {
                showSuggestions($input, suggestions, config.suggestionClass);
            } else {
                closeSuggestions($input, config.suggestionClass);
            }
        }, 300));

        // Mousedown on suggestion: add tag before blur closes
        $container.on('mousedown', `.${config.suggestionClass}`, function(e) {
            selectingSuggestion = true;
            e.preventDefault();
            const name = $(this).text().trim();
            addTag(name, $input, $container, config.tagClass, config.hiddenInputName, maxTags);
            closeSuggestions($input, config.suggestionClass);
            $input.focus();
            setTimeout(() => { selectingSuggestion = false; }, 0);
        });

        // Blur on input: keep suggestions open if selecting
        $input.on('blur', function() {
            setTimeout(() => {
                if (!selectingSuggestion) {
                    closeSuggestions($input, config.suggestionClass);
                }
            }, 150);
        });

        // Enter key: prevent form submit, add tag
        $input.on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const name = $input.val().trim();
                if (name) {
                    addTag(name, $input, $container, config.tagClass, config.hiddenInputName, maxTags);
                }
            }
        });

        // Remove tag (delegated): removes tag and hidden input
        $container.on('click', `.remove-${config.tagClass}`, function(e) {
            e.preventDefault();
            const $tag = $(this).closest(`.${config.tagClass}`);
            $tag.find(`input[type="hidden"][name="${config.hiddenInputName}"]`).remove();
            $tag.remove();
            Utility.markFieldChanged($input);
        });

        // Click handler for suggestion (global): add tag and close suggestions
        $(document).on('click', `.${config.suggestionClass}`, function() {
            const name = $(this).text();
            addTag(name, $input, $container, config.tagClass, config.hiddenInputName, maxTags);
            closeSuggestions($input, config.suggestionClass);
        });

        // Add removing tag/flag on mousedown (global)
        $(document).on('mousedown', `.remove-${config.tagClass}`, () => setRemoving(true));
    }

    /** Debounce helper: limits function execution rate.
     *      @param {Function} fn - Function to debounce
     *      @param {number} delay - Delay in ms
     *      @returns {Function}
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

    /** Add a tag to the container, if not already present and maxTags not exceeded.
     *  Shows a tooltip if maxTags is reached.
     *      @param {string} name - Tag value to add
     *      @param {jQuery} $input - Input field
     *      @param {jQuery} $container - Tag container
     *      @param {string} tagClass - CSS class for tags
     *      @param {string} hiddenInputName - Name for hidden input(s)
     *      @param {number} [maxTags] - Optional: maximum number of tags allowed
     */
    function addTag(name, $input, $container, tagClass, hiddenInputName, maxTags) {
        if ($container.find(`.${tagClass}[data-name="${name}"]`).length) {
            return;
        }
        if (maxTags && $container.find(`.${tagClass}`).length >= maxTags) {
            showTagLimitWarning($input, maxTags);
            return;
        }
        const $tag = $(`
            <span class="${tagClass}" data-name="${name}">
                ${name}
                <button type="button" class="remove-${tagClass}" aria-label="Remove">&times;</button>
                <input type="hidden" name="${hiddenInputName}" value="${name}">
            </span>
        `);
        $container.append($tag);
        $input.val('');
        Utility.markFieldChanged($input);
    }

    /** Show a tooltip near the input if user tries to add more than allowed tags.
     *  Tooltip auto-hides after 1.8 seconds.
     *      @param {jQuery} $input - Input field
     *      @param {number} maxTags - Maximum allowed tags
     */
    function showTagLimitWarning($input, maxTags) {
        // Simple tooltip implementation
        const msg = `Maximaal ${maxTags} locatie${maxTags > 1 ? 's' : ''} per boek toegestaan.`;
        let $tooltip = $('<div class="tag-limit-tooltip"></div>').text(msg);
        $tooltip.css({
            position: 'absolute',
            background: '#ffc',
            color: '#333',
            border: '1px solid #e0c06d',
            padding: '4px 8px',
            borderRadius: '4px',
            fontSize: '0.85em',
            zIndex: 2000,
            top: $input.offset().top + $input.outerHeight() + 2,
            left: $input.offset().left
        });
        $('body').append($tooltip);
        setTimeout(() => { $tooltip.fadeOut(300, () => $tooltip.remove()); }, 1800);
    }

    /** Get the tags container for a given input field.
     * 
     */
    function getTagsContainer($field, containerClass) {
        const bookId = $field.data('book-id');
        return $(`.${containerClass}[data-book-id="${bookId}"]`);
    }

    /** Get all tag values from a container, sorted and trimmed.
     * 
     */
    function getValuesFromContainer($container, hiddenInputName) {
        return $container.find(`input[name="${hiddenInputName}"]`).map(function() {
            return $(this).val().trim();
        }).get().filter(Boolean).sort();
    }

    /** Restore tags from input value (comma-separated string) for a given field/container.
     *  Used when enabling edit mode.
     * 
     */
    function restoreTagsFromInput($field, $container, tagClass, hiddenInputName) {
        const existing = $field.val();
        if (existing) {
            existing.split(',')
                .map(name => name.trim())
                .forEach(name => {
                    if (name) {
                        addTag(name, $field, $container, tagClass, hiddenInputName);
                    }
                });
        }
        const origValues = getValuesFromContainer($container, hiddenInputName);
        $field.data('originalValue', origValues.join(','));
    }

    /** Show suggestions below the input field.
     * 
     */
    function showSuggestions($input, suggestions, suggestionClass) {
        const offset = $input.offset();
        const $list = $(`<div class="${suggestionClass}s"></div>`).css({
            position: 'fixed',
            top: offset.top + $input.outerHeight(),
            left: offset.left,
            width: $input.outerWidth(),
            zIndex: 2000
        });
        suggestions.forEach(s => {
            $list.append(`<div class="${suggestionClass}">${s}</div>`);
        });
        closeSuggestions($input, suggestionClass);
        $input.after($list);
    }

    /** Remove the suggestions dropdown.
     * 
     */
    function closeSuggestions($input, suggestionClass) {
        $input.siblings(`.${suggestionClass}s`).remove();
    }

    // Exported API
    return {
        init,
        addTag,
        isRemoving,
        getTagsContainer,
        getValuesFromContainer,
        restoreTagsFromInput
    };
})();

export { TagInput };