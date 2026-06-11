<?php if (canEdit()): ?>
    <div class="input-group input-group-sm" data-context="details">
        <div class="locatie-tags-container" data-book-id="<?= $book->id ?>" data-context="details"></div>
        <input  type="text"
                class="aletho-inputs extra-input-style locatie-input"
                id="book-office-<?= $book->id ?>"
                value="<?=htmlspecialchars($book->locatie) ?>"
                placeholder="Type een locatie en druk op Enter"
                data-book-id="<?= $book->id ?>"
                data-context="details"
                autocomplete="off"
                disabled>
        <button type="button"
                class="btn btn-link extra-button-style"
                data-swap-targets="#book-office-<?= $book->id ?>"
                data-context="details"
                aria-label="Edit Office">
            ✏️
        </button>
    </div>

    <?php if (!empty($errors['offices'])): ?>
        <div class="aletho-alert-inline"><?= htmlspecialchars($errors['offices']) ?></div>
    <?php endif; ?>
<?php else: ?>
    <input type="text" class="aletho-inputs extra-input-style" value="<?=htmlspecialchars($book->locatie) ?>" disabled>
<?php endif; ?>