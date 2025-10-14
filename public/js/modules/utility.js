
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

    // Exported API
    return { markFieldChanged, clearFieldChanged };
})();

export { Utility };