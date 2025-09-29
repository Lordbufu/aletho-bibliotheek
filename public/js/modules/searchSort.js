// searchSort.js
export const SearchSort = (() => {
    /**
     * Initialize search functionality
     * Filters the options inside the given container based on input value.
     *
     * @param {string} inputSelector - selector for the search input
     * @param {string} optionsSelector - selector for the search options container
     */
    function initSearch(inputSelector, optionsSelector) {
        const $searchInput   = $(inputSelector);
        const $searchOptions = $(optionsSelector);

        if ($searchInput.length === 0 || $searchOptions.length === 0) {
            return;
        }

        $searchInput.on('input', function () {
            const query = $(this).val().toLowerCase().trim();

            $searchOptions.children().each(function () {
                const $opt = $(this);
                const text = $opt.text().toLowerCase();
                $opt.toggle(text.includes(query));
            });
        });
    }
    

    /**
     * Initialize sort functionality
     * Calls the provided callback whenever the sort option changes.
     *
     * @param {string} selector - selector for the sort dropdown
     * @param {Function} onSort - callback to run when sort changes
     */
    function initSort(selector, onSort) {
        const $sortSelect = $(selector);
        if ($sortSelect.length === 0) {
            return;
        }

        $sortSelect.on('change', function () {
            const value = $(this).val();
            if (typeof onSort === 'function') {
                onSort(value);
            }
        });
    }

    /**
     * Utility: get current sort value
     *
     * @param {string} selector - selector for the sort dropdown
     * @returns {string|null}
     */
    function getSortValue(selector) {
        const $sortSelect = $(selector);
        return $sortSelect.length ? $sortSelect.val() : null;
    }

    /**
     * Extract a field value from a card for searching/sorting
     *
     * @param {jQuery} $card - jQuery wrapped card element
     * @param {string} field - 'writer' | 'genre' | 'title'
     * @returns {string}
     */
    function extractFieldValue($card, field) {
        let value = '';

        switch (field) {
            case 'writer':
                value = $card.find('input[name="book_writer"]').val() || '';
                break;
            case 'genre':
                value = $card.find('select[name="genre_id"] option:selected').text() || '';
                break;
            case 'title':
            default:
                value = $card.find('.mn-main-col').text() || '';
        }

        return value.trim().toLowerCase();
    }

    return { initSearch, initSort, getSortValue, extractFieldValue };
})();