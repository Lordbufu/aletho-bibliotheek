import { Utility } from './utility.js';
import { Suggestions } from './suggestions.js';
import { AppState } from '../appstate.js';

let activeTagInput = null;

export const TagInput = {
    init(config) {
        AppState.tagConfigs[config.inputSelector] = config;
        AppState.tagInputSelectors.push(config.inputSelector);
        AppState.tagSuggestionSelectors.push(`.${config.suggestionClass}`);
        AppState.tagRemoveSelectors.push(`${config.containerSelector} .remove-${config.tagClass}`);
    },

    setActive($input) {
        activeTagInput = $input;
    },

    clearActiveDelayed() {
        setTimeout(() => { activeTagInput = null; }, 200);
    },

    handleInput($input) {
        const config = this.getConfigForInput($input);
        const query = $input.val().trim().toLowerCase();

        if (query.length < 2) {
            Suggestions.close();
            return;
        }

        this.loadOptions(config.endpoint, (options) => {
            this.showSuggestions($input, options, query, config.suggestionClass);
        });
    },

    handleEnter($input) {
        const config = this.getConfigForInput($input);
        const name = $input.val().trim();
        if (!name) return;

        const $container = this.getTagsContainer($input, config.containerSelector);

        this.loadOptions(config.endpoint, (options) => {
            const status = this.addTag(
                name,
                null,
                $input,
                $container,
                config.tagClass,
                config.hiddenInputName,
                config.maxTags,
                config.allowCustom,
                options
            );

            if (status) {
                Suggestions.close();
                $input.val('');
            }

            $input.focus();
        });
    },

    handleSuggestionClick($suggestion) {
        if (!activeTagInput) return;

        const $input = activeTagInput;
        const config = this.getConfigForInput($input);

        const name = $suggestion.data('name') || $suggestion.text().trim();
        const id = $suggestion.data('id') || null;

        const $container = this.getTagsContainer($input, config.containerSelector);

        this.loadOptions(config.endpoint, (options) => {
            const status = this.addTag(
                name,
                id,
                $input,
                $container,
                config.tagClass,
                config.hiddenInputName,
                config.maxTags,
                config.allowCustom,
                options
            );

            if (status) {
                Suggestions.close();
                $input.val('');
            }

            $input.focus();
        });
    },

    handleRemoveTag($removeButton) {
        const $tag   = $removeButton.closest('span');
        const $group = $tag.closest('.input-group');
        const $input = $group.find(AppState.UI.TAG_INPUTS.join(', ')).first();

        const config = this.getConfigForInput($input);

        const hiddenName = $tag.find('input[type="hidden"]').first().attr('name');
        $tag.remove();

        const $container = this.getTagsContainer($input, config.containerSelector);

        if ($container.find(`input[name="${hiddenName}"]`).length === 0) {
            $container.append(`<input type="hidden" name="${hiddenName}" value="" data-empty="1">`);
        }

        if ($input.data('context') !== 'popin') {
            Utility.markFieldChanged($input);
        }
    },

    loadOptions(endpoint, callback) {
        if (AppState.optionsCache[endpoint]) {
            callback(AppState.optionsCache[endpoint]);
            return;
        }

        if (AppState.pendingRequests[endpoint]) {
            AppState.pendingRequests[endpoint].push(callback);
            return;
        }

        AppState.pendingRequests[endpoint] = [callback];

        Utility.request({
            url: endpoint,
            success: data => {
                AppState.optionsCache[endpoint] = data;
                AppState.pendingRequests[endpoint].forEach(cb => cb(data));
                delete AppState.pendingRequests[endpoint];
            }
        });
    },

    showSuggestions($input, options, query, suggestionClass) {
        const filtered = options.filter(option => {
            const label = typeof option === 'string'
                ? option
                : (option.naam || option.name || '');
            return label.toLowerCase().includes(query);
        });

        if (filtered.length > 0) {
            Suggestions.show($input, filtered, suggestionClass);
        } else {
            Suggestions.close();
        }
    },

    addTag(naam, id, $input, $container, tagClass, hiddenInputName, maxTags, allowCustom = true, options = null) {
        if ($container.find(`.${tagClass}[data-name="${naam}"]`).length) {
            showTagLimitWarning($input, 1, `"${naam}" is al toegevoegd.`);
            return false;
        }

        if (maxTags && $container.find(`.${tagClass}`).length >= maxTags) {
            showTagLimitWarning($input, maxTags);
            return false;
        }

        if (!allowCustom && Array.isArray(options)) {
            const exists = options.some(opt => {
                if (typeof opt === 'string') return opt === naam;
                if (opt && typeof opt.naam === 'string') return opt.naam === naam;
                return false;
            });

            if (!exists) {
                showTagLimitWarning($input, 1, "Alleen bestaande locaties toegestaan.");
                return false;
            }
        }

        const $tag = $(`
            <span class="${tagClass} aletho-border" data-name="${naam}" data-id="${id || ''}">
                ${naam}
                <button type="button" class="remove-${tagClass}" aria-label="Remove">&times;</button>
                <input type="hidden" name="${hiddenInputName}" value="${naam}">
                ${id ? `<input type="hidden" name="${hiddenInputName.replace('[]', '_ids[]')}" value="${id}">` : ''}
            </span>
        `);

        $container.append($tag);
        $container.find(`input[data-empty="1"]`).remove();
        $input.val('');

        if ($input.data('context') !== 'popin') {
            Utility.markFieldChanged($input);
        }

        return true;
    },

    getTagsContainer($field, containerSelector) {
        const $group = $field.closest('.input-group');
        const $container = $group.find(containerSelector).first();
        if ($container.length) return $container;
        throw new Error(`Tag container not found for ${containerSelector}`);
    },

    getValuesFromContainer($container, hiddenInputName) {
        return $container.find(`input[name="${hiddenInputName}"]`)
            .map(function() { return $(this).val().trim(); })
            .get()
            .filter(Boolean)
            .sort();
    },

    restoreTagsFromInput($field, $container, tagClass, hiddenInputName) {
        const existing = $field.val();
        if (existing) {
            existing.split(',')
                .map(naam => naam.trim())
                .forEach(naam => {
                    if (naam) this.addTag(naam, null, $field, $container, tagClass, hiddenInputName);
                });
        }

        const origValues = this.getValuesFromContainer($container, hiddenInputName);
        $field.data('originalValue', Utility.normalizeValues(origValues));
    },

    getConfigForInput($input) {
        const classes = $input.attr('class').split(/\s+/);

        for (const selector in AppState.tagConfigs) {
            const className = selector.replace('.', '');
            if (classes.includes(className)) {
                return AppState.tagConfigs[selector];
            }
        }

        throw new Error("No TagInput config found for input: " + $input.attr('class'));
    }
};