<?php if (canEdit()): ?>
    <div class="input-group input-group-sm" data-context="details">
        <div class="genre-tags-container" data-book-id="<?= $book->id ?>" data-context="details"></div>
        <input  type="text"
                class="aletho-inputs extra-input-style genre-input"
                id="book-genre-<?= $book->id ?>"
                value="<?= htmlspecialchars($book->genres) ?>"
                placeholder="Type een genre en druk op Enter"
                data-book-id="<?= $book->id ?>"
                data-context="details"
                autocomplete="off"
                disabled>
        <button type="button"
                class="btn btn-link extra-button-style"
                data-swap-targets="#book-genre-<?= $book->id ?>"
                data-context="details"
                aria-label="Edit Genre">
            ✏️
        </button>
    </div>

    <?php if (!empty($errors['genres'])): ?>
        <div class="aletho-alert-inline"><?= htmlspecialchars($errors['genres']) ?></div>
    <?php endif; ?>
<?php else: ?>
    <input type="text" class="aletho-inputs extra-input-style genre-input" value="<?= htmlspecialchars($book->genres) ?>" disabled>
<?php endif; ?>