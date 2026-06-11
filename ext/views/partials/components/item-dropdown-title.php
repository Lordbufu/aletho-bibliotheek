<?php if (canEdit()): ?>
    <input type="hidden" name="_method" value="PATCH">
    <input type="hidden" name="book_id" value="<?= $book->id ?>">

    <div class="input-group input-group-sm" data-context="details">
        <input  type="text"
                class="aletho-inputs extra-input-style title-input"
                id="book-name-<?= $book->id ?>"
                name="book_name"
                value="<?= htmlspecialchars($book->title) ?>"
                data-context="details"
                disabled>
        <button type="button"
                class="btn btn-link extra-button-style"
                data-swap-targets="#book-name-<?= $book->id ?>"
                aria-label="Edit Book Name">
            ✏️
        </button>
    </div>

    <?php if (!empty($errors['title'])): ?>
        <div class="aletho-alert-inline"><?= htmlspecialchars($errors['title']) ?></div>
    <?php endif; ?>
<?php endif; ?>