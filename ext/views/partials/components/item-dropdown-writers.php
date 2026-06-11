<?php if (canEdit()): ?>
    <div class="input-group input-group-sm" data-context="details">
        <div class="schrijver-tags-container" data-book-id="<?= $book->id ?>" data-context="details"></div>
        <input  type="text"
                class="aletho-inputs extra-input-style schrijver-input"
                id="book-writer-<?= $book->id ?>"
                value="<?= htmlspecialchars($book->schrijvers) ?>"
                placeholder="Type writer names and press Enter"
                data-book-id="<?= $book->id ?>"
                data-context="details"
                autocomplete="off"
                disabled>
        <button type="button"
                class="btn btn-link extra-button-style"
                data-swap-targets="#book-writer-<?= $book->id ?>"
                data-context="details"
                aria-label="Edit Writer">
            ✏️
        </button>
    </div>

    <?php if (!empty($errors['writers'])): ?>
        <div class="aletho-alert-inline"><?= htmlspecialchars($errors['writers']) ?></div>
    <?php endif; ?>
<?php else: ?>
    <input type="text" class="aletho-inputs extra-input-style schrijver-input" value="<?= htmlspecialchars($book->schrijvers) ?>" disabled>
<?php endif; ?>