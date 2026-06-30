import { AppState } from '../appstate.js'

export const SearchSort = {
    search(query, method) {
        $(AppState.UI.ITEM_CONTAINER).each(function () {
            const $card = $(this);
            let textToSearch = "";

            switch (method) {
                case "writer":
                    textToSearch = $card.find(".schrijver-input").val() || "";
                    break;
                case "genre":
                    textToSearch = $card.find(".genre-input").val() || "";
                    break;
                case "title":
                default:
                    textToSearch = $card.find(".mn-main-col").text() || "";
            }

            if (textToSearch.toLowerCase().includes(query)) {
                $card.show();
            } else {
                $card.hide();
            }
        });
    },

    updatePlaceholder(method) {
        const labels = {
            title: "Zoek op titel …",
            writer: "Zoek op schrijver …",
            genre: "Zoek op genre …"
        };

        $(AppState.UI.SEARCH_INPUT)
            .attr("placeholder", labels[method] || labels.title);
    },

    sort(field, direction) {
        const $wrapper = $(AppState.UI.ITEMS_WRAPPER);
        const $cards = $wrapper.find(AppState.UI.ITEM_CONTAINER);

        const sorted = $cards.get().sort((a, b) => {
            const va = SearchSort.extract($(a), field);
            const vb = SearchSort.extract($(b), field);
            const cmp = va.localeCompare(vb, "nl", { sensitivity: "base" });
            return direction === "asc" ? cmp : -cmp;
        });

        $cards.detach();
        $wrapper.append(sorted);
    },

    extract($card, field) {
        switch (field) {
            case "writer":
                return ($card.find(".schrijver-input").val() || "").trim().toLowerCase();
            case "genre":
                return ($card.find(".genre-input").val() || "").trim().toLowerCase();
            case "title":
            default:
                return ($card.find(".mn-main-col").text() || "").trim().toLowerCase();
        }
    }
};