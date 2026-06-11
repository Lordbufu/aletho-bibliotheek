import { Utility } from './utility.js';
import { Suggestions } from './suggestions.js';

const TagInput = (() => {
    let activeTagInput = null;
    const optionsCache = {};

    /*  Initialize tag input and tag container. */
    function init(config) {
        const $inputs   = $(config.inputSelector);
        const allowCustom = config.allowCustom !== false;
        const maxTags = config.maxTags || null;
        let allOptions = [];

        $inputs.on('focus', function() {
            activeTagInput = $(this);

            if (optionsCache[config.endpoint]) {
                allOptions = optionsCache[config.endpoint];
                return;
            }

            Utility.request({
                url: config.endpoint,
                success: data => {
                    allOptions = data;
                    optionsCache[config.endpoint] = data;
                }
            });
        });

        $inputs.on('blur', function() {
            setTimeout(() => { activeTagInput = null; }, 200);
        });

        // Input handler: filter suggestions with debounce for performance
        $inputs.on('input', function() {
            const $input = $(this);
            activeTagInput = $input;
            const query = $input.val().trim().toLowerCase();

            if (query.length < 2) {
                Suggestions.close();
                return;
            }

            if (!allOptions.length && !optionsCache[config.endpoint]) {
                Utility.request({
                    url: config.endpoint,
                    success: data => {
                        allOptions = data;
                        optionsCache[config.endpoint] = data;
                        showSuggestions($input, allOptions, query, config.suggestionClass);
                    }
                });
                return;
            }

            showSuggestions($input, allOptions, query, config.suggestionClass);
        });

        // still needs review
        function showSuggestions($input, options, query, suggestionClass) {
            const filtered = options.filter(option => {
                const label = typeof option === 'string'
                    ? option
                    : (option.naam || option.name || '');
                return label.toLowerCase().includes(query);
            });

            // console.log(filtered);
            if (filtered.length > 0) {
                Suggestions.show($input, filtered, suggestionClass);
                Suggestions.bindCloseOnBlur($input);
            } else {
                Suggestions.close();
            }
        }

        // Mousedown on suggestion: add tag before blur closes
        $(document).on('click', `.${config.suggestionClass}`, function(e) {
            e.preventDefault();

            if (!activeTagInput) return;

            const $input        = activeTagInput;
            const name          = $(this).data('name') || $(this).text().trim();
            const id            = $(this).data('id') || null;
            const $container    = getTagsContainer($input, config.containerSelector);
            const status        = addTag(name, id, $input, $container, config.tagClass, config.hiddenInputName, maxTags, allowCustom, allOptions);

            if (status) {
                Suggestions.close();
                $input.val('');
            }

            $input.focus();
        });

        // Prevent blur from closing suggestions while clicking
        $(document).on('mousedown', `.${config.suggestionClass}`, function(e) {
            e.preventDefault();
        });

        // still needs review
        // Enter key: prevent form submit, add tag
        $inputs.on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();

                const $input = $(this);
                const name = $input.val().trim();
                if (!name) return;

                activeTagInput      = $input;
                const $container    = getTagsContainer($input, config.containerSelector);
                const status        = addTagLegacy(name, $input, $container, config.tagClass, config.hiddenInputName, maxTags, allowCustom, allOptions);

                if (status) {
                    Suggestions.close();
                    $input.focus();
                    $input.val('');
                } else {
                    $input.focus();
                }
            }
        });

        // Remove tag (delegated): removes tag and hidden input
        $(document).on('click', `${config.containerSelector} .remove-${config.tagClass}`, function(e) {
            // TODO: Figure out why the old line below, suddenly gives console errors, and why i dint have that on the current live version
            // Suggested actions: Add the console.log(config) to the live version, and see what it logs when removing a tag from either container.
        // $(document).on('click', `.remove-${config.tagClass}`, function(e) {
            // console.log(config);
            e.preventDefault();
            const $tag          = $(this).closest(`.${config.tagClass}`);
            const $input        = $tag.closest('form').find(config.inputSelector);
            
            $tag.find(`input[type="hidden"][name="${config.hiddenInputName}"]`).remove();
            $tag.remove();

            const $container    = TagInput.getTagsContainer($input, config.containerSelector);

            if ($container.find(`input[name="${config.hiddenInputName}"]`).length === 0) {
                $container.append(
                    `<input type="hidden" name="${config.hiddenInputName}" value="" data-empty="1">`
                );
            }

            if ($input.data('context') !== 'popin') {
                Utility.markFieldChanged($input);
            }
        });
    }

    /*  Add a tag to the container, if not already present and maxTags not exceeded. */
    function addTag(naam, id, $input, $container, tagClass, hiddenInputName, maxTags, allowCustom = true, allOptions = []) {
        if ($container.find(`.${tagClass}[data-name="${naam}"]`).length) {
            showTagLimitWarning($input, 1, `"${naam}" is al toegevoegd.`);
            return false;
        }

        if (maxTags && $container.find(`.${tagClass}`).length >= maxTags) {
            showTagLimitWarning($input, maxTags);
            return false;
        }

        if (!allowCustom) {
            const exists = Array.isArray(allOptions) && allOptions.some(opt => {
                if (typeof opt === 'string') return opt === naam;
                if (opt && typeof opt.naam === 'string') return opt.naam === naam;
                return false;
            });

            if (!exists) {
                showTagLimitWarning($input, 1, "Alleen bestaande locaties toegestaan.");
                return false;
            }
        }

        // TODO: Test re-factor for the new datasets (id+name)
        const $tag = $(`
            <span class="${tagClass} aletho-border" data-name="${naam}" data-id="${id || ''}">
                ${naam}
                <button type="button" class="remove-${tagClass}" aria-label="Remove">&times;</button>
                <input type="hidden" name="${hiddenInputName}" value="${naam}">
                ${id ? `<input type="hidden" name="${hiddenInputName.replace('[]', '_ids[]')}" value="${id}">` : ''}
            </span>
        `);

        console.log($tag);
        $container.append($tag);
        $container.find(`input[data-empty="1"]`).remove();
        $input.val('');

        if ($input.data('context') !== 'popin') {
            Utility.markFieldChanged($input);
        }

        return true;
    }

    /*  Show a tooltip near the input if user tries to add more than allowed tags. */
    function showTagLimitWarning($input, maxTags, customMsg) {
        if (!$input || !$input.length) return;

        const msg = customMsg || `Maximaal ${maxTags} ${maxTags > 1 ? 'items' : 'item'} toegestaan.`;
        const offset = $input.offset();
        
        if (!offset) return;

        let $tooltip = $('<div class="tag-limit-tooltip"></div>').text(msg);

        const rootFont = parseFloat(getComputedStyle(document.documentElement).fontSize) || 16;
        const topRem = ($input.offset().top + $input.outerHeight() + 2) / rootFont + 'rem';
        const leftRem = ($input.offset().left) / rootFont + 'rem';

        $tooltip.css({
            position: 'absolute',
            top: topRem,
            left: leftRem
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
                .map(naam => naam.trim())
                .forEach(naam => {
                    if (naam) {
                        addTagLegacy(naam, $field, $container, tagClass, hiddenInputName);
                    }
                });
        }

        const origValues = getValuesFromContainer($container, hiddenInputName);
        $field.data('originalValue', Utility.normalizeValues(origValues));
    }

    // Temp solution to support the previous non-id based system
    function addTagLegacy(name, $input, $container, tagClass, hiddenInputName, maxTags, allowCustom, allOptions) {
        return addTag(name, null, $input, $container, tagClass, hiddenInputName, maxTags, allowCustom, allOptions);
    }

    return {
        init,
        addTag,
        getTagsContainer,
        getValuesFromContainer,
        restoreTagsFromInput,
        addTagLegacy
    };
})();

export { TagInput };