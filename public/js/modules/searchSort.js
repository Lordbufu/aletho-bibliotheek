const SearchSort = (() => {
    /*  Initialize search input and option change handlers. */
    function initSearch(inputSelector, optionsSelector) {
        $(inputSelector).on('input', function () {
            const query = $(this).val().toLowerCase().trim();
            const method = $(optionsSelector).val();

            $('.aletho-item-container').each(function () {
                const $card = $(this);
                let textToSearch = '';

                switch (method) {
                    case 'writer':
                        textToSearch = $card.find('.writer-input').val() || '';
                        break;
                    case 'genre':
                        textToSearch = $card.find('.genre-input').val() || '';
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

    /*  Initialize sort dropdown handler. */
    function initSort(sortSelector, callback) {
        $(sortSelector).on('change', function () {
            const [field, direction] = $(this).val().split('-');
            const $wrapper = $('.items-list');
            const $cards = $wrapper.find('.aletho-item-container');

            const sorted = $cards.get().sort((a, b) => {
                const va = extractFieldValue($(a), field);
                const vb = extractFieldValue($(b), field);
                const cmp = va.localeCompare(vb, 'nl', { sensitivity: 'base' });
                return direction === 'asc' ? cmp : -cmp;
            });

            // Remove all cards and re-append in sorted order
            $cards.detach();
            $wrapper.append(sorted);
        });
    }

    /*  Get current sort value from a dropdown. */
    function getSortValue(selector) {
        const $sortSelect = $(selector);

        return $sortSelect.length ? $sortSelect.val() : null;
    }

    /*  Extract a field value from a card for searching/sorting. */
    function extractFieldValue($card, field) {
        let value = '';

        switch (field) {
            case 'writer':
                value = $card.find('.writer-input').val() || '';
                break;
            case 'genre':
                value = $card.find('.genre-input').val() || '';
                break;
            case 'title':
            default:
                value = $card.find('.mn-main-col').text() || '';
        }

        return value.trim().toLowerCase();
    }

    return {
        initSearch,
        initSort,
        getSortValue,
        extractFieldValue
    };
})();

export { SearchSort };