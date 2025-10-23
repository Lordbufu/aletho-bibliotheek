import { Utility } from './utility.js';

const TagInput = (() => {
    let activeTagInput      = null;

    /*  Initialize tag input and tag container. */
    function init(config) {
        const $inputs   = $(config.inputSelector);
        const allowCustom = config.allowCustom !== false;
        const maxTags = config.maxTags || null;
        let allOptions = [];

        // Fetch all options once for autocomplete
        $.getJSON(config.endpoint, data => { allOptions = data; });

        // Input handler: filter suggestions with debounce for performance
        $inputs.on('input', function() {
            const $input = $(this);
            const query = $input.val().trim().toLowerCase();

            if (query.length < 2) {
                closeAllSuggestions();
                return;
            }

            const suggestions = allOptions.filter(name =>
                name.toLowerCase().includes(query)
            );

            if (suggestions.length > 0) {
                showSuggestions($input, suggestions, config.suggestionClass);
            } else {
                closeAllSuggestions();
            }
        });

        // Mousedown on suggestion: add tag before blur closes
        $(document).on('click', `.${config.suggestionClass}`, function(e) {
            e.preventDefault();

            if (!activeTagInput) return;

            const $input = activeTagInput;
            const name = $(this).text().trim();
            const $container = getTagsContainer($input, config.containerSelector);
            const status = addTag(name, $input, $container, config.tagClass, config.hiddenInputName, maxTags, allowCustom, allOptions);

            if (status) {
                closeAllSuggestions();
                $input.val('');
            }

            $input.focus();
        });

        // Prevent blur from closing suggestions while clicking
        $(document).on('mousedown', `.${config.suggestionClass}`, function(e) {
            e.preventDefault();
        });

        // Enter key: prevent form submit, add tag
        $inputs.on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();

                const $input = $(this);
                const name = $input.val().trim();
                if (!name) return;

                activeTagInput      = $input;
                const $container    = getTagsContainer($input, config.containerSelector);
                const status        = addTag(name, $input, $container, config.tagClass, config.hiddenInputName, maxTags, allowCustom, allOptions);

                if (status) {
                    closeAllSuggestions();
                    $input.focus();
                    $input.val('');
                } else {
                    $input.focus();
                }
            }
        });

        // Remove tag (delegated): removes tag and hidden input
        $(document).on('click', `.remove-${config.tagClass}`, function(e) {
            e.preventDefault();
            const $tag = $(this).closest(`.${config.tagClass}`);
            const $input = $tag.closest('form').find(config.inputSelector);
            
            $tag.find(`input[type="hidden"][name="${config.hiddenInputName}"]`).remove();
            $tag.remove();

            if ($input.data('context') !== 'popin') {
                Utility.markFieldChanged($input);
            }
        });
    }

    /*  Closes any open suggestion list on page and clears the active input reference. */
    function closeAllSuggestions() {
        $('.suggestion-list').remove();
    }

    /*  Add a tag to the container, if not already present and maxTags not exceeded. */
    function addTag(name, $input, $container, tagClass, hiddenInputName, maxTags, allowCustom = true, allOptions = []) {
        if ($container.find(`.${tagClass}[data-name="${name}"]`).length) {
            closeAllSuggestions();
            showTagLimitWarning($input, 1, `"${name}" is al toegevoegd.`);
            return false;
        }

        if (maxTags && $container.find(`.${tagClass}`).length >= maxTags) {
            closeAllSuggestions();
            showTagLimitWarning($input, maxTags);
            return false;
        }

        if (!allowCustom && !allOptions.includes(name)) {
            closeAllSuggestions();
            showTagLimitWarning($input, 1, "Alleen bestaande locaties toegestaan.");
            return false;
        }

        const $tag = $(`
            <span class="${tagClass} aletho-border" data-name="${name}">
                ${name}
                <button type="button" class="remove-${tagClass}" aria-label="Remove">&times;</button>
                <input type="hidden" name="${hiddenInputName}" value="${name}">
            </span>
        `);
        $container.append($tag);
        $input.val('');
        if ($input.data('context') !== 'popin') {
            Utility.markFieldChanged($input);
        }
        return true;
    }

    /*  Show a tooltip near the input if user tries to add more than allowed tags. */
    function showTagLimitWarning($input, maxTags, customMsg) {
        console.log("Tag limit function reached!");
        if (!$input || !$input.length) return;

        const msg = customMsg || `Maximaal ${maxTags} ${maxTags > 1 ? 'items' : 'item'} toegestaan.`;
        const offset = $input.offset();
        
        if (!offset) return;

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

    /*  Get the tags container for a given input field. */
    function getTagsContainer($field, containerSelector) {
        const $group = $field.closest('.input-group');
        const $container = $group.find(containerSelector).first();

        if ($container.length) {
            return $container;
        }

        throw new Error(`Tag container not found for ${containerSelector}`);
    }

    /*  Get all tag values from a container, sorted and trimmed. */
    function getValuesFromContainer($container, hiddenInputName) {
        return $container.find(`input[name="${hiddenInputName}"]`).map(function() {
            return $(this).val().trim();
        }).get().filter(Boolean).sort();
    }

    /*  Restore tags from input value (comma-separated string) for a given field/container (Used when enabling edit mode). */
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
        $field.data('originalValue', Utility.normalizeValues(origValues));
    }

    /*  Show suggestions below the input field. */
    function showSuggestions($input, suggestions, suggestionClass) {
        closeAllSuggestions();
        activeTagInput = $input;

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

    // Exported API
    return { init, addTag, getTagsContainer, getValuesFromContainer, restoreTagsFromInput };
})();

export { TagInput };