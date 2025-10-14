
/**
 * Utility module: Generic helpers for input field state management in book edit forms.
 * - markFieldChanged: Marks a field as changed and enables the save button
 * - clearFieldChanged: Clears changed state and disables save button if no fields are dirty
 */
const Utility = (() => {
    /** Mark an input/select field as changed and enable the save button.
     *      @param {jQuery} $field - The field that was edited
     */
    function markFieldChanged($field) {
        const $form = $field.closest('form.book-edit-form');
        const $saveBtn = $form.find('button[id^="save-changes-"]');
        $field.addClass('field-changed');
        $saveBtn.addClass('needs-save');
    }

    /** Clear changed state from a field and disable save button if no fields are dirty.
     *      @param {jQuery} $field - The field to clear
     */
    function clearFieldChanged($field) {
        const $form = $field.closest('form.book-edit-form');
        const $saveBtn = $form.find('button[id^="save-changes-"]');
        $field.removeClass('field-changed');
        if ($form.find('.field-changed').length === 0) {
            $saveBtn.removeClass('needs-save');
        }
    }

    /**
     * Get configuration for taggable fields based on their class.
     * @param {jQuery} $field - The field to get config for.
     * @returns {object} Configuration object.
     */
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

    // Exported API
    return { markFieldChanged, clearFieldChanged, getFieldConfig };
})();

export { Utility };