
/**
 * SearchSort module: Handles search and sort logic for book/item cards.
 * - initSearch: Sets up search input and option change handlers
 * - initSort: Sets up sort dropdown handler
 * - getSortValue: Utility to get current sort value
 * - extractFieldValue: Extracts a field value for searching/sorting
 */
const SearchSort = (() => {
    /**
     * Initialize search input and option change handlers.
     * Filters item cards based on input and selected method.
     * @param {string} inputSelector - Selector for the search input
     * @param {string} optionsSelector - Selector for the search options dropdown
     */
    function initSearch(inputSelector, optionsSelector) {
        $(inputSelector).on('input', function () {
            const query = $(this).val().toLowerCase().trim();
            const method = $(optionsSelector).val();
            $('.aletho-item-container').each(function () {
                const $card = $(this);
                let textToSearch = '';
                switch (method) {
                    case 'writer':
                        textToSearch = $card.find('input[name="book_writer"]').val() || '';
                        break;
                    case 'genre':
                        textToSearch = $card.find('select[name="genre_id"] option:selected').text() || '';
                        break;
                    case 'title':
                    default:
                        textToSearch = $card.find('.mn-main-col').text() || '';
                }
                if (textToSearch.toLowerCase().includes(query)) {
                    $card.show();
                } else {
                    $card.hide();
                }
            });
        });
        $(optionsSelector).on('change', function () {
            const method = $(this).val();
            const labels = {
                title:  'Zoek op titel …',
                writer: 'Zoek op schrijver …',
                genre:  'Zoek op genre …'
            };
            $(inputSelector)
                .attr('placeholder', labels[method] || labels.title)
                .val('')
                .trigger('input');
        });
    }

    /**
     * Initialize sort dropdown handler.
     * Sorts item cards in the view container based on selected field and direction.
     * @param {string} sortSelector - Selector for the sort dropdown
     * @param {Function} [callback] - Optional callback for custom sort logic
     */
    function initSort(sortSelector, callback) {
        $(sortSelector).on('change', function () {
            const [field, direction] = $(this).val().split('-');
            const $wrapper = $('#view-container');
            const $cards = $wrapper.find('.item-container');
            const sorted = $cards.get().sort((a, b) => {
                const va = extractFieldValue($(a), field);
                const vb = extractFieldValue($(b), field);
                const cmp = va.localeCompare(vb, 'nl', { sensitivity: 'base' });
                return direction === 'asc' ? cmp : -cmp;
            });
            $wrapper.append(sorted);
        });
    }

    /**
     * Utility: Get current sort value from a dropdown.
     * @param {string} selector - Selector for the sort dropdown
     * @returns {string|null}
     */
    function getSortValue(selector) {
        const $sortSelect = $(selector);
        return $sortSelect.length ? $sortSelect.val() : null;
    }

    /**
     * Extract a field value from a card for searching/sorting.
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

    // Exported API
    return {
        initSearch,
        initSort,
        getSortValue,
        extractFieldValue
    };
})();

export { SearchSort };