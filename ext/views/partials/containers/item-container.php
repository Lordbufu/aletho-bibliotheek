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

    <div class="collapse aletho-item-dropdown" id="customItemDropdown-<?= $book['id'] ?>">
        <form class="book-edit-form p-1" data-book-id="<?= $book['id'] ?>" method="post" action="/editBook">

            <?php if ($_SESSION['user']['canEdit']): ?>
                <input type="hidden" name="_method" value="PATCH">
                <input type="hidden" name="book_id" value="<?= $book['id'] ?>">

                <!-- Book Name for editing -->
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
            <?php endif; ?>

            <?php if ($_SESSION['user']['canEdit']): ?>
                <!-- Writer name for editing -->
                <div class="input-group input-group-sm">
                    <!-- Writers input as a taggable field -->
                    <input type="text"
                        class="aletho-inputs extra-input-style writer-input"
                        id="book-writer-<?= $book['id'] ?>"
                        name="book_writers[]"
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
            <?php else: ?>
                <!-- Writer name for viewing -->
                <input type="text" class="aletho-inputs extra-input-style" value="<?= htmlspecialchars($book['writers'] ?? '') ?>" disabled>
            <?php endif; ?>

            <?php if ($_SESSION['user']['canEdit']): ?>
                <!-- Genre Name for editing -->
                <div class="input-group input-group-sm">
                    <input type="text"
                        class="aletho-inputs extra-input-style genre-input"
                        id="book-genre-<?= $book['id'] ?>"
                        value="<?= htmlspecialchars($book['genres'] ?? 'Onbekend') ?>"
                        placeholder="Type een genre en druk op Enter"
                        data-book-id="<?= $book['id'] ?>"
                        autocomplete="off"
                        disabled>
                    <button
                        type="button"
                        class="btn btn-link extra-button-style"
                        data-swap-targets="#genre-input-<?= $book['id'] ?>"
                        aria-label="Edit Genre">✏️</button>
                </div>
            <?php else: ?>
                <!-- Genre Name for viewing -->
                <input type="text" class="aletho-inputs extra-input-style" value="<?= htmlspecialchars($book['genres'] ?? 'Onbekend') ?>" disabled>
            <?php endif; ?>

            <?php if ($_SESSION['user']['canEdit'] && $book['canEditOffice']): ?>
                <!-- Office Name for editing -->
                <div class="input-group input-group-sm">
                    <input type="text" class="aletho-inputs extra-input-style" value="<?=htmlspecialchars($book['office']) ?>" disabled>
                    <button type="button" class="btn btn-link extra-button-style" data-swap-targets="#office-input-<?= $book['id'] ?>" aria-label="Edit Office">✏️</button>
                </div>
            <?php else: ?>
                <!-- Office Name for viewing -->
                <input type="text" class="aletho-inputs extra-input-style" value="<?=htmlspecialchars($book['office']) ?>" disabled>
            <?php endif; ?>

            <?php if ($_SESSION['user']['canEdit']): ?>
                <!-- Book Status for editing -->
                <div class="input-group input-group-sm">
                    <span class="aletho-labels extra-popin-style">Status</span>
                    <div type="button" class="extra-fake-button"></div>
                </div>                
                <div class="input-group input-group-sm">
                    <input type="text" class="aletho-inputs extra-input-style" id="book-status-<?= $book['id'] ?>" name="book_status" value="<?= htmlspecialchars($book['status']['status_name']) ?>" disabled>
                    <div type="button" class="extra-fake-button"></div>
                </div>
            <?php else : ?>
                <!-- Book Status for viewing -->
                <span class="aletho-labels extra-popin-style">Status</span>
                <input type="text" class="aletho-inputs extra-input-style" id="book-status-<?= $book['id'] ?>" name="book_status" value="<?= htmlspecialchars($book['status']['status_name']) ?>" disabled>
            <?php endif; ?>

            <?php if ($_SESSION['user']['canEdit'] && isset($book['status']['statusExp'])): ?>
                <!-- Book Status Verloopt Info-->
                <div class="input-group input-group-sm">
                    <span class="aletho-labels extra-popin-style">Verloopt</span>
                    <div type="button" class="extra-fake-button"></div>
                </div>                
                <div class="input-group input-group-sm">
                    <input type="date" class="aletho-inputs extra-input-style" id="book-status-expires-<?= $book['id'] ?>" name="book_status_expires" value="<?= htmlspecialchars($book['status']['statusExp']) ?>" disabled>
                    <div type="button" class="extra-fake-button"></div>
                </div>
            <?php elseif (isset($book['status']['statusExp'])) : ?>
                <!-- Book Status Verloopt Info -->
                <span class="aletho-labels extra-popin-style">Verloopt</span>
                <input type="date" class="aletho-inputs extra-input-style" id="book-status-expires-<?= $book['id'] ?>" name="book_status_expires" value="<?= htmlspecialchars($book['status']['statusExp']) ? htmlspecialchars($book['status']['statusExp']) : '' ?>" disabled>
            <?php endif; ?>

            <?php if($_SESSION['user']['canEdit']): ?>
                <!-- Previous Loaners Section (W.I.P.) -->
                <div class="input-group input-group-sm">
                    <span class="aletho-labels extra-popin-style">Vorige Leners</span>
                    <div type="button" class="extra-fake-button"></div>
                </div>

                <div class="input-group input-group-sm">
                    <!-- Need to refactor this, based on the new books array-->
                    <div type="button" class="extra-fake-button"></div>
                </div>

            <?php endif; ?>

            <?php if($_SESSION['user']['canEdit']): ?>
                <!-- Save Book Edits button (form submit) -->
                <div class="input-group input-group-sm mt-1">
                    <button id="save-changes-<?= $book['id'] ?>"
                        type="submit"
                        name="save-item"
                        class="aletho-buttons extra-popin-style">Wijzigingen Opslaan</button>
                    <div type="button" class="extra-fake-button"></div>
                </div>

                <!-- Status Edit pop-in button -->
                <div class="input-group input-group-sm mt-2">
                    <button id="boek-status-button"
                        type="button"
                        name="action"
                        class="aletho-buttons extra-popin-style">Status Aanpassen</button>
                    <div type="button" class="extra-fake-button"></div>
                </div>

                <!-- Delete Book button (form submmit ?) -->
                <div class="input-group input-group-sm mt-1">
                    <button id="remove-book-<?= $book['id'] ?>"
                        type="submit"
                        name="action"
                        class="aletho-buttons extra-popin-style">Boek Verwijderen</button>
                    <div type="button" class="extra-fake-button"></div>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>