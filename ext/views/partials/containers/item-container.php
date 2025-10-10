<div class="aletho-item-container" id="item-container-<?= $book['id'] ?>">

    <div class="aletho-item">
        <button class="aletho-dropdown-buttons"
            id="itemButton-<?= $book['id'] ?>"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#customItemDropdown-<?= $book['id'] ?>"
            aria-expanded="false"
            aria-controls="customItemDropdown-<?= $book['id'] ?>">▼
        </button>
        <span class="dropdown-item flex-fill text-center mn-main-col"> <?= htmlspecialchars($book['title']) ?> </span>
        <span class="status-dot statusOne d-flex justify-content-end" id="status-dot-<?= $book['id'] ?>"></span>
    </div>

    <div id="customItemDropdown-<?= $book['id'] ?>"
        class="collapse aletho-item-dropdown <?= (isset($old['book_id']) && $old['book_id'] === $book['id']) ? ' show' : '' ?>">
        <form class="book-edit-form p-1" data-book-id="<?= $book['id'] ?>" method="post" action="/editBook">

            <?php if ($_SESSION['user']['canEdit']): // Title section ?>
                <input type="hidden" name="_method" value="PATCH">
                <input type="hidden" name="book_id" value="<?= $book['id'] ?>">

                <div class="input-group input-group-sm">
                    <input type="text"
                        class="aletho-inputs extra-input-style"
                        id="book-name-<?= $book['id'] ?>"
                        name="book_name"
                        value="<?= htmlspecialchars($book['title']) ?>"
                        disabled>
                    <button type="button"
                        class="btn btn-link extra-button-style"
                        data-swap-targets="#book-name-<?= $book['id'] ?>"
                        aria-label="Edit Book Name">✏️
                    </button>
                </div>
                <?php if (!empty($errors['book_title'])): ?>
                    <div class="aletho-alert-inline"><?= htmlspecialchars($errors['book_title']) ?></div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($_SESSION['user']['canEdit']): // Writers section ?>
                <div class="writer-tags-container" data-book-id="<?= $book['id'] ?>"></div>

                <div class="input-group input-group-sm">
                    <input type="text"
                        class="aletho-inputs extra-input-style writer-input"
                        id="book-writer-<?= $book['id'] ?>"
                        value="<?= htmlspecialchars($book['writers'] ?? '') ?>"
                        placeholder="Type writer names and press Enter"
                        data-book-id="<?= $book['id'] ?>"
                        autocomplete="off"
                        disabled>
                    <button type="button"
                            class="btn btn-link extra-button-style"
                            data-swap-targets="#book-writer-<?= $book['id'] ?>"
                            aria-label="Edit Writer">✏️</button>
                </div>
                <?php if (!empty($errors['book_writers'])): ?>
                    <div class="aletho-alert-inline"><?= htmlspecialchars($errors['book_writers']) ?></div>
                <?php endif; ?>
            <?php else: ?>
                <input type="text" class="aletho-inputs extra-input-style" value="<?= htmlspecialchars($book['writers'] ?? '') ?>" disabled>
            <?php endif; ?>

            <?php if ($_SESSION['user']['canEdit']): // Genre section ?>
                <div class="genre-tags-container" data-book-id="<?= $book['id'] ?>"></div>

                <div class="input-group input-group-sm">
                    <input type="text"
                        class="aletho-inputs extra-input-style genre-input"
                        id="book-genre-<?= $book['id'] ?>"
                        value="<?= htmlspecialchars($book['genres'] ?? 'Onbekend') ?>"
                        placeholder="Type een genre en druk op Enter"
                        data-book-id="<?= $book['id'] ?>"
                        autocomplete="off"
                        disabled>
                    <button type="button"
                        class="btn btn-link extra-button-style"
                        data-swap-targets="#book-genre-<?= $book['id'] ?>"
                        aria-label="Edit Genre">✏️</button>
                </div>
                <?php if (!empty($errors['book_genres'])): ?>
                    <div class="aletho-alert-inline"><?= htmlspecialchars($errors['book_genres']) ?></div>
                <?php endif; ?>
            <?php else: ?>
                <input type="text" class="aletho-inputs extra-input-style" value="<?= htmlspecialchars($book['genres'] ?? 'Onbekend') ?>" disabled>
            <?php endif; ?>

            <?php if ($_SESSION['user']['canEdit'] && $book['canEditOffice']): // Office section ?>
                <div class="office-tags-container" data-book-id="<?= $book['id'] ?>"></div>

                <div class="input-group input-group-sm">
                    <input type="text"
                        class="aletho-inputs extra-input-style office-input"
                        id="book-office-<?= $book['id'] ?>"
                        value="<?=htmlspecialchars($book['office']) ?>"
                        placeholder="Type een locatie en druk op Enter"
                        data-book-id="<?= $book['id'] ?>"
                        autocomplete="off"
                        disabled>
                    <button type="button"
                        class="btn btn-link extra-button-style"
                        data-swap-targets="#book-office-<?= $book['id'] ?>"
                        aria-label="Edit Office">✏️</button>
                </div>
                <?php if (!empty($errors['book_offices'])): ?>
                    <div class="aletho-alert-inline"><?= htmlspecialchars($errors['book_offices']) ?></div>
                <?php endif; ?>
            <?php else: ?>
                <input type="text" class="aletho-inputs extra-input-style" value="<?=htmlspecialchars($book['office']) ?>" disabled>
            <?php endif; ?>

            <?php if ($_SESSION['user']['canEdit']): // Huidige status section ?>
                <div class="input-group input-group-sm">
                    <span class="aletho-labels extra-popin-style">Status</span>
                    <div type="button" class="extra-fake-button"></div>
                </div>                
                <div class="input-group input-group-sm">
                    <input type="text" class="aletho-inputs extra-input-style" id="book-status-<?= $book['id'] ?>" name="book_status" value="<?= htmlspecialchars($book['status']['status_name']) ?>" disabled>
                    <div type="button" class="extra-fake-button"></div>
                </div>
                <?php if (!empty($errors['book_status'])): ?>
                    <div class="aletho-alert-inline"><?= htmlspecialchars($errors['book_status']) ?></div>
                <?php endif; ?>
            <?php else : ?>
                <span class="aletho-labels extra-popin-style">Status</span>
                <input type="text" class="aletho-inputs extra-input-style" id="book-status-<?= $book['id'] ?>" name="book_status" value="<?= htmlspecialchars($book['status']['status_name']) ?>" disabled>
            <?php endif; ?>

            <?php if (isset($book['status']['status_exp']) && $_SESSION['user']['canEdit']): // Status expires section ?>
                <div class="input-group input-group-sm">
                    <span class="aletho-labels extra-popin-style">Verloopt</span>
                    <div type="button" class="extra-fake-button"></div>
                </div>
                <div class="input-group input-group-sm">
                    <input type="date" class="aletho-inputs extra-input-style" id="book-status-expires-<?= $book['id'] ?>" name="book_status_expires" value="<?= htmlspecialchars($book['status']['status_exp']) ? htmlspecialchars($book['status']['status_exp']) : '' ?>" disabled>
                    <div type="button" class="extra-fake-button"></div>
                </div>
            <?php elseif (isset($book['status']['status_exp'])) : ?>
                <span class="aletho-labels extra-popin-style">Verloopt</span>
                <input type="date" class="aletho-inputs extra-input-style" id="book-status-expires-<?= $book['id'] ?>" name="book_status_expires" value="<?= htmlspecialchars($book['status']['status_exp']) ? htmlspecialchars($book['status']['status_exp']) : '' ?>" disabled>
            <?php endif; ?>

            <?php if($_SESSION['user']['canEdit']): // Previous loaners section ?>
                <div class="input-group input-group-sm">
                    <span class="aletho-labels extra-popin-style">Vorige Leners</span>
                    <div type="button" class="extra-fake-button"></div>
                </div>
                <div class="input-group input-group-sm">
                    <div type="button" class="extra-fake-button"></div>
                </div>
            <?php endif; ?>

            <?php if($_SESSION['user']['canEdit']): // Form buttons section ?>
                <div class="input-group input-group-sm mt-1">
                    <button id="save-changes-<?= $book['id'] ?>"
                        type="submit"
                        class="aletho-buttons extra-popin-style">Wijzigingen Opslaan</button>
                    <div type="button" class="extra-fake-button"></div>
                </div>
        </form>
                <div class="input-group input-group-sm mt-1">
                    <button id="boek-status-button"
                        type="button"
                        class="aletho-buttons extra-popin-style">Status Aanpassen</button>
                    <div type="button" class="extra-fake-button"></div>
                </div>

                <div class="input-group input-group-sm mt-1">
                    <form class="book-delete-form" data-book-id="<?= $book['id'] ?>" method="post" action="/delBook">
                        <input type="hidden" name="_method" value="DELETE">
                        <input type="hidden" name="book_id" value="<?= $book['id'] ?>">

                        <button id="remove-book-<?= $book['id'] ?>"
                            type="submit"
                            class="aletho-buttons extra-popin-style">Boek Verwijderen</button>
                        <div type="button" class="extra-fake-button"></div>
                    </form>
                </div>
            <?php endif; ?>
    </div>
</div>