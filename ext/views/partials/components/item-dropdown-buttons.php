<?php if(canEdit()): ?>
    <div class="input-group input-group-sm mt-1" data-context="details">
        <button id="save-changes-<?= $book->id ?>"
                type="submit"
                class="aletho-buttons extra-popin-style"
                data-context="details">
            Wijzigingen Opslaan
        </button>
        <div type="button" class="extra-fake-button"></div>
    </div>

    <div class="input-group input-group-sm mt-1" data-context="details">
        <button type="button"
                class="aletho-buttons extra-popin-style delete-book-btn"
                data-book-id="<?= $book->id ?>"
                data-context="details">
            Boek Verwijderen
        </button>
        <div type="button" class="extra-fake-button"></div>
    </div>
<?php endif; ?>