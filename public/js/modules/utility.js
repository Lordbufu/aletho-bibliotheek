const Utility = (() => {
    function markFieldChanged($field) {
        const $form = $field.closest('form.book-edit-form');
        const $saveBtn = $form.find('button[id^="save-changes-"]');
        $field.addClass('field-changed');
        $saveBtn.addClass('needs-save');
    }

    function clearFieldChanged($field) {
        const $form = $field.closest('form.book-edit-form');
        const $saveBtn = $form.find('button[id^="save-changes-"]');
        $field.removeClass('field-changed');
        if ($form.find('.field-changed').length === 0) {
            $saveBtn.removeClass('needs-save');
        }
    }

    function getFieldConfig($field) {
        const configs = [
            { class: 'schrijver-input', type: 'schrijver', container: '.schrijver-tags-container', name: 'book_schrijvers[]' },
            { class: 'genre-input', type: 'genre', container: '.genre-tags-container', name: 'book_genres[]' },
            { class: 'locatie-input', type: 'locatie', container: '.locatie-tags-container', name: 'book_offices[]' },
            { class: 'schrijver-input-pop', type: 'schrijver', container: '.add-schrijver-tags-container', name: 'book_schrijvers[]' },
            { class: 'genre-input-pop', type: 'genre', container: '.add-genre-tags-container', name: 'book_genres[]' },
            { class: 'locatie-input-pop', type: 'locatie', container: '.add-locatie-tags-container', name: 'book_offices[]' }
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

    function makeTagConfig(type, opts = {}) {
        return {
            inputSelector: `.${type}-input`,
            containerSelector: `.${type}-tags-container`,
            endpoint: `/bookData?data=${type}s`,
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
            endpoint: `/bookData?data=${type}s`,
            tagClass: `${type}-tag`,
            suggestionClass: `${type}-suggestion-pop`,
            hiddenInputName: `book_${type}s[]`,
            maxTags: 3,
            allowCustom: true,
            ...opts
        };
    }

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

    return {
        markFieldChanged,
        clearFieldChanged,
        getFieldConfig,
        normalizeValues,
        makeTagConfig,
        makePopTagConfig,
        request
    };
})();

export { Utility };