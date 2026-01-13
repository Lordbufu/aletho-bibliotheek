<?php
    $statusClassMap = [
        'aanwezig' => 'statusOne',
        'afwezig' => 'statusTwo',
        'overdatum' => 'statusTwo',
        'transport' => 'statusThree',
        'ligt klaar' => 'statusThree',
        'gereserveerd' => 'statusFour'
    ];

    $status = strtolower($book['status']);
    $statusClass = $statusClassMap[$status] ?? '';  // empty string fallback
?>

<div class="aletho-item-container" id="item-container-<?= $book['id'] ?>">
    <div class="aletho-item">
        <button class="aletho-dropdown-buttons"
                id="itemButton-<?= $book['id'] ?>"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#customItemDropdown-<?= $book['id'] ?>"
                aria-expanded="<?= (isset($_SESSION['_flashSingle']) && (int)$_SESSION['_flashSingle']['message'] === (int)$book['id']) ? 'true' : 'false' ?>"
                aria-controls="customItemDropdown-<?= $book['id'] ?>">
            ▼
        </button>
        <span class="dropdown-item flex-fill text-center mn-main-col"><?= htmlspecialchars($book['title']) ?></span>
        <span class="status-dot d-flex justify-content-end <?= $statusClass ?>" id="status-dot-<?= $book['id'] ?>"></span>
    </div>

    <div id="customItemDropdown-<?= $book['id'] ?>" class="collapse aletho-item-dropdown <?= (isset($_SESSION['_flashSingle']) && (int)$_SESSION['_flashSingle']['message'] === (int)$book['id']) ? ' show' : '' ?>">
        <form class="book-edit-form p-1" data-book-id="<?= $book['id'] ?>" method="post" action="/editBook">

            <?php if ($canEdit): // Title section ?>
                <input type="hidden" name="_method" value="PATCH">
                <input type="hidden" name="book_id" value="<?= $book['id'] ?>">

                <div class="input-group input-group-sm" data-context="details">
                    <input  type="text"
                            class="aletho-inputs extra-input-style title-input"
                            id="book-name-<?= $book['id'] ?>"
                            name="book_name"
                            value="<?= htmlspecialchars($book['title']) ?>"
                            data-context="details"
                            disabled>
                    <button type="button"
                            class="btn btn-link extra-button-style"
                            data-swap-targets="#book-name-<?= $book['id'] ?>"
                            aria-label="Edit Book Name">
                        ✏️
                    </button>
                </div>

                <?php if (!empty($errors['book_title'])): ?>
                    <div class="aletho-alert-inline-<?= $_SESSION['_flashInline']['type'] ?>"><?= htmlspecialchars($errors['book_title']) ?></div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($canEdit): // Writers section ?>
                <div class="input-group input-group-sm" data-context="details">
                    <div class="writer-tags-container" data-book-id="<?= $book['id'] ?>" data-context="details"></div>
                    <input  type="text"
                            class="aletho-inputs extra-input-style writer-input"
                            id="book-writer-<?= $book['id'] ?>"
                            value="<?= htmlspecialchars($book['writers'] ?? '') ?>"
                            placeholder="Type writer names and press Enter"
                            data-book-id="<?= $book['id'] ?>"
                            data-context="details"
                            autocomplete="off"
                            disabled>
                    <button type="button"
                            class="btn btn-link extra-button-style"
                            data-swap-targets="#book-writer-<?= $book['id'] ?>"
                            data-context="details"
                            aria-label="Edit Writer">
                        ✏️
                    </button>
                </div>

                <?php if (!empty($errors['book_writers'])): ?>
                    <div class="aletho-alert-inline"><?= htmlspecialchars($errors['book_writers']) ?></div>
                <?php endif; ?>
            <?php else: ?>
                <input type="text" class="aletho-inputs extra-input-style writer-input" value="<?= htmlspecialchars($book['writers'] ?? '') ?>" disabled>
            <?php endif; ?>

            <?php if ($canEdit): // Genre section ?>
                <div class="input-group input-group-sm" data-context="details">
                    <div class="genre-tags-container" data-book-id="<?= $book['id'] ?>" data-context="details"></div>
                    <input  type="text"
                            class="aletho-inputs extra-input-style genre-input"
                            id="book-genre-<?= $book['id'] ?>"
                            value="<?= htmlspecialchars($book['genres'] ?? 'Onbekend') ?>"
                            placeholder="Type een genre en druk op Enter"
                            data-book-id="<?= $book['id'] ?>"
                            data-context="details"
                            autocomplete="off"
                            disabled>
                    <button type="button"
                            class="btn btn-link extra-button-style"
                            data-swap-targets="#book-genre-<?= $book['id'] ?>"
                            data-context="details"
                            aria-label="Edit Genre">
                        ✏️
                    </button>
                </div>

                <?php if (!empty($errors['book_genres'])): ?>
                    <div class="aletho-alert-inline"><?= htmlspecialchars($errors['book_genres']) ?></div>
                <?php endif; ?>
            <?php else: ?>
                <input type="text" class="aletho-inputs extra-input-style genre-input" value="<?= htmlspecialchars($book['genres'] ?? 'Onbekend') ?>" disabled>
            <?php endif; ?>

            <?php if ($canEdit && $book['canEditOffice']): // Office section ?>
                <div class="input-group input-group-sm" data-context="details">
                    <div class="office-tags-container" data-book-id="<?= $book['id'] ?>" data-context="details"></div>
                    <input  type="text"
                            class="aletho-inputs extra-input-style office-input"
                            id="book-office-<?= $book['id'] ?>"
                            value="<?=htmlspecialchars($book['curOffice']) ?>"
                            placeholder="Type een locatie en druk op Enter"
                            data-book-id="<?= $book['id'] ?>"
                            data-context="details"
                            autocomplete="off"
                            disabled>
                    <button type="button"
                            class="btn btn-link extra-button-style"
                            data-swap-targets="#book-office-<?= $book['id'] ?>"
                            data-context="details"
                            aria-label="Edit Office">
                        ✏️
                    </button>
                </div>

                <?php if (!empty($errors['book_offices'])): ?>
                    <div class="aletho-alert-inline"><?= htmlspecialchars($errors['book_offices']) ?></div>
                <?php endif; ?>
            <?php else: ?>
                <input type="text" class="aletho-inputs extra-input-style" value="<?=htmlspecialchars($book['curOffice']) ?>" disabled>
            <?php endif; ?>

            <?php if ($canEdit): // Huidige status section ?>
                <div class="input-group input-group-sm" data-context="details">
                    <span class="aletho-labels extra-popin-style">Status</span>
                    <div type="button" class="extra-fake-button"></div>
                </div>

                <div class="input-group input-group-sm" data-context="details">
                    <input  type="text"
                            class="aletho-inputs extra-input-style"
                            id="book-status-<?= $book['id'] ?>"
                            name="book_status"
                            value="<?= htmlspecialchars($book['status']) ?>"
                            data-context="details"
                            disabled>
                    <button type="button"
                            class="btn btn-link extra-button-style boek-status-button"
                            data-book-id="<?= $book['id'] ?>"
                            aria-label="Edit Book status">
                        ✏️
                    </button>
                <?php if ($book['is_reserved']): ?>
                    <div class="reserved-wrapper">
                        🔒 <span class="status-badge reserved">Gereserveerd</span>
                    </div>
                <?php endif; ?>
                </div>

                <?php if (!empty($errors['book_status'])): ?>
                    <div class="aletho-alert-inline"><?= htmlspecialchars($errors['book_status']) ?></div>
                <?php endif; ?>
            <?php else : ?>
                <span class="aletho-labels extra-popin-style">Status</span>
                <input  type="text"
                        class="aletho-inputs extra-input-style"
                        id="book-status-<?= $book['id'] ?>"
                        name="book_status"
                        value="<?= htmlspecialchars($book['status']) ?>"
                        disabled>
                <?php if ($book['is_reserved']): ?>
                    <div class="reserved-wrapper">
                        🔒 <span class="status-badge reserved">Gereserveerd</span>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($book['dueDate']) && $canEdit): // Status expires section ?>
                <div class="input-group input-group-sm" data-context="details">
                    <span class="aletho-labels extra-popin-style">Verloopt</span>
                    <div type="button" class="extra-fake-button"></div>
                </div>

                <div class="input-group input-group-sm" data-context="details">
                    <input  type="date"
                            class="aletho-inputs extra-input-style"
                            id="book-status-expires-<?= $book['id'] ?>"
                            name="book_status_expires"
                            value="<?= htmlspecialchars($book['dueDate']) ? htmlspecialchars($book['dueDate']) : '' ?>"
                            data-context="details"
                            disabled>
                    <div type="button" class="extra-fake-button"></div>
                </div>
            <?php elseif (isset($book['dueDate'])) : ?>
                <span class="aletho-labels extra-popin-style">Verloopt</span>
                <input  type="date"
                        class="aletho-inputs extra-input-style"
                        id="book-status-expires-<?= $book['id'] ?>"
                        name="book_status_expires"
                        value="<?= htmlspecialchars($book['dueDate']) ? htmlspecialchars($book['dueDate']) : '' ?>"
                        disabled>
            <?php endif; ?>

            <?php if($canEdit): // Previous loaners section (W.I.P.) ?>
                <div class="input-group input-group-sm" data-context="details">
                    <span class="aletho-labels extra-popin-style">Huidige / Vorige Leners</span>
                    <div type="button" class="extra-fake-button"></div>
                </div>

                <div class="input-group input-group-sm" data-context="details">
                    <select class="aletho-inputs extra-input-style" data-context="details">
                        <?php   foreach ($book['curLoaner'] as $lName): ?>
                            <option selected disabled><?= htmlspecialchars($lName) ?></option>
                        <?php endforeach;
                                foreach ($book['prevLoaners'] as $lName): ?>
                            <option disabled><?= htmlspecialchars($lName) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div type="button" class="extra-fake-button"></div>
                </div>
            <?php endif; ?>

            <?php if($canEdit): // Form buttons section ?>
                <div class="input-group input-group-sm mt-1" data-context="details">
                    <button id="save-changes-<?= $book['id'] ?>"
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
                            data-book-id="<?= $book['id'] ?>"
                            data-context="details">
                        Boek Verwijderen
                    </button>
                    <div type="button" class="extra-fake-button"></div>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>