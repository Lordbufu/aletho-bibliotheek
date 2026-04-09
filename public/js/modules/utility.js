/*  Utility module: Generic helpers for input field state management in book edit forms. */
const Utility = (() => {
    /*  Mark an input/select field as changed and enable the save button. */
    function markFieldChanged($field) {
        const $form = $field.closest('form.book-edit-form');
        const $saveBtn = $form.find('button[id^="save-changes-"]');
        $field.addClass('field-changed');
        $saveBtn.addClass('needs-save');
    }

    /*  Clear changed state from a field and disable save button if no fields are dirty. */
    function clearFieldChanged($field) {
        const $form = $field.closest('form.book-edit-form');
        const $saveBtn = $form.find('button[id^="save-changes-"]');
        $field.removeClass('field-changed');
        if ($form.find('.field-changed').length === 0) {
            $saveBtn.removeClass('needs-save');
        }
    }

    /*  Get configuration for taggable fields based on their class. */
    function getFieldConfig($field) {
        const configs = [
            { class: 'writer-input', type: 'writer', container: '.writer-tags-container', name: 'book_writers[]' },
            { class: 'genre-input', type: 'genre', container: '.genre-tags-container', name: 'book_genres[]' },
            { class: 'office-input', type: 'office', container: '.office-tags-container', name: 'book_offices[]' },
            { class: 'writer-input-pop', type: 'writer', container: '.add-writer-tags-container', name: 'book_writers[]' },
            { class: 'genre-input-pop', type: 'genre', container: '.add-genre-tags-container', name: 'book_genres[]' },
            { class: 'office-input-pop', type: 'office', container: '.add-office-tags-container', name: 'book_offices[]' }
        ];

        for (const config of configs) {
            if ($field.hasClass(config.class)) {
                return {
                    tagClass: `${config.type}-tag`,
                    containerSelector: config.container,
                    hiddenInputName: config.name,
                    isTaggable: true
                };
            }
        }

        return { isTaggable: false };
    }

    function normalizeValues(values) {
        return values.map(v => v.trim()).filter(Boolean).sort().join(',');
    }

    // Tag config helpers
    function makeTagConfig(type, opts = {}) {
        return {
            inputSelector: `.${type}-input`,
            containerSelector: `.${type}-tags-container`,
            endpoint: `/bookdata?data=${type}s`,
            tagClass: `${type}-tag`,
            suggestionClass: `${type}-suggestion`,
            hiddenInputName: `book_${type}s[]`,
            maxTags: 3,
            allowCustom: true,
            ...opts
        };
    }

    function makePopTagConfig(type, opts = {}) {
        return {
            inputSelector: `.${type}-input-pop`,
            containerSelector: `.add-${type}-tags-container`,
            endpoint: `/bookdata?data=${type}s`,
            tagClass: `${type}-tag`,
            suggestionClass: `${type}-suggestion-pop`,
            hiddenInputName: `book_${type}s[]`,
            maxTags: 3,
            allowCustom: true,
            ...opts
        };
    }

    // Request helper (GET, POST, etc.)
    function request({ url, method = 'GET', data = {}, success, error }) {
        $.ajax({
            url,
            method,
            dataType: 'json',
            data,
            success,
            error: error || function(xhr, status, err) {
                console.error('Request error:', status, err);
            }
        });
    }


    return { markFieldChanged, clearFieldChanged, getFieldConfig, normalizeValues, makeTagConfig, makePopTagConfig, request };
})();

export { Utility };