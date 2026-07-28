import { AppState } from '../appstate.js';

export const Utility = {
    applyEditModeLogic(ts, $field, self) {
        ts.focus();
        ts.on('blur', () => {
            const current = parseInt($field.val());
            const original = $field.data('originalValue');

            if (current === original) {
                $field.prop('disabled', true)
                    .removeClass('field-editable field-changed')
                    .removeData('originalValue');

                self.clearFieldChanged($field);
                ts.destroy();
            } else {
                self.markFieldChanged($field);
            }
        });
    },

    markFieldChanged($field) {
        const $form = $field.closest('form.book-edit-form');
        const $saveBtn = $form.find('button[id^="save-changes-"]');

        if ($field[0].tomselect) {
            $($field[0].tomselect.wrapper).addClass('field-changed');
        } else {
            $field.addClass('field-changed');
        }

        $saveBtn.addClass('needs-save');
    },

    clearFieldChanged($field) {
        const $form = $field.closest('form.book-edit-form');
        const $saveBtn = $form.find('button[id^="save-changes-"]');
        $field.removeClass('field-changed');

        const changedFields = $form.find('input.field-changed, select.field-changed');
        if (changedFields.length === 0) {
            $saveBtn.removeClass('needs-save');
        }
    },

    getFieldConfig($field) {
        for (const selector in AppState.tagConfigs) {
            const className = selector.replace('.', '');
            if ($field.hasClass(className)) {
                const cfg = AppState.tagConfigs[selector];
                return {
                    class: className,
                    tagClass: cfg.tagClass,
                    containerSelector: cfg.containerSelector,
                    hiddenInputName: cfg.hiddenInputName,
                    isTaggable: true
                };
            }
        }

        return { isTaggable: false };
    },

    normalizeValues(values) {
        return values.map(v => v.trim()).filter(Boolean).sort().join(',');
    },

    makeTagConfig(type, opts = {}) {
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
    },

    makePopTagConfig(type, opts = {}) {
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
    },

    initTomSelectSingle($field, options, callback) {
        const { endpoint, mode } = options;
        const selector = '#' + $field.attr('id');
        const self = this;

        let originalId = null;
        let originalName = null;

        if (mode === 'edit') {
            originalId = parseInt($field.val());
            originalName = $field.find('option:selected').text().trim();
            $field.data('originalValue', originalId);
        }

        $field.prop('disabled', false).addClass('field-editable');

        if ($field.children().length > 1) {
            const ts = new TomSelect(selector, {
                maxItems: 1,
                create: false,
                controlInput: null,
            });

            if (callback) callback(ts);

            if (mode === 'edit') {
                setTimeout(() => {
                    self.applyEditModeLogic(ts, $field, self);
                }, 0);
            }

            return;
        }

        this.request({
            url: '/bookData',
            data: { data: endpoint },
            success: function(list) {
                $field.empty();
                $field.append(`<option value="_placeholder" disabled hidden>Selecteer locatie...</option>`);
                list.forEach(l => {
                    l.id = parseInt(l.id, 10);
                    if (mode === 'edit' && l.id === originalId) {
                        $field.append(`<option value="${originalId}" selected>${originalName}</option>`);
                    } else {
                        $field.append(`<option value="${l.id}">${l.naam}</option>`);
                    }
                });

                const ts = new TomSelect(selector, {
                    maxItems: 1,
                    create: false,
                    controlInput: null,
                });

                if (callback) callback(ts);

                if (mode === 'edit') {
                    self.applyEditModeLogic(ts, $field, self);
                }
            }
        });
    },

    request({ url, method = 'GET', data = {}, success, error }) {
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
};