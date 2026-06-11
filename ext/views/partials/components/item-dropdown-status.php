<?php if (canEdit()): ?>
    <div class="input-group input-group-sm" data-context="details">
        <span class="aletho-labels extra-popin-style">Status</span>
        <div type="button" class="extra-fake-button"></div>
    </div>

    <div class="input-group input-group-sm" data-context="details">
        <input  type="text"
                class="aletho-inputs extra-input-style"
                id="book-status-<?= $book->id ?>"
                name="book_status"
                value="<?= htmlspecialchars($book->status) ?>"
                data-context="details"
                disabled>
        <button type="button"
                class="btn btn-link extra-button-style boek-status-button"
                data-book-id="<?= $book->id ?>"
                aria-label="Edit Book status">
            ✏️
        </button>
    <?php if ($book->gereserveerd): ?>
        <div class="reserved-wrapper">
            🔒 <span class="status-badge reserved">Gereserveerd</span>
        </div>
    <?php endif; ?>
    </div>

    <?php if (!empty($errors['status'])): ?>
        <div class="aletho-alert-inline"><?= htmlspecialchars($errors['status']) ?></div>
    <?php endif; ?>
<?php else : ?>
    <span class="aletho-labels extra-popin-style">Status</span>
    <input  type="text"
            class="aletho-inputs extra-input-style"
            id="book-status-<?= $book->id ?>"
            name="book_status"
            value="<?= htmlspecialchars($book->status) ?>"
            disabled>
    <?php if ($book->gereserveerd): ?>
        <div class="reserved-wrapper">
            🔒 <span class="status-badge reserved">Gereserveerd</span>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if ($book->statusEinde): ?>
    <?php if (canEdit()): // Status expires section ?>
        <div class="input-group input-group-sm" data-context="details">
            <span class="aletho-labels extra-popin-style">Verloopt</span>
            <div type="button" class="extra-fake-button"></div>
        </div>

        <div class="input-group input-group-sm" data-context="details">
            <input  type="date"
                    class="aletho-inputs extra-input-style"
                    id="book-status-expires-<?= $book->id ?>"
                    name="book_status_expires"
                    value="<?= $book->statusEinde ?>"
                    data-context="details"
                    disabled>
            <div type="button" class="extra-fake-button"></div>
        </div>
    <?php else : ?>
        <span class="aletho-labels extra-popin-style">Verloopt</span>
        <input  type="date"
                class="aletho-inputs extra-input-style"
                id="book-status-expires-<?= $book->id ?>"
                name="book_status_expires"
                value="<?= $book->statusEinde ?>"
                disabled>
    <?php endif; ?>
<?php endif; ?>