<?php
    $canEditOffice = FALSE;

    // Set this books current status
    foreach($statuses as $status) {
        if($status['id'] === $book['status_id']) {
            $huidigeStatus = $status['type'];
        }
    }
?>

<!-- /* Item container example, with a dropdown for potentially editable details and user based content. */ -->
<div class="aletho-item-container container-sm" id="item-container-<?= $book['id'] ?>">

    <!-- Top bar (always visible) -->
    <div class="aletho-item">
        <button class="aletho-dropdown-buttons" id="itemButton-<?= $book['id'] ?>" type="button"
                data-bs-toggle="collapse" data-bs-target="#customItemDropdown-<?= $book['id'] ?>"
                aria-expanded="false" aria-controls="customItemDropdown-<?= $book['id'] ?>"
        >▼</button>
        <span class="dropdown-item flex-fill text-center mn-main-col"> <?= htmlspecialchars($book['name']) ?> </span>
        <span class="status-dot statusOne d-flex justify-content-end" id="status-dot-<?= $book['id'] ?>"></span>
    </div>

    <!-- Detailed item info, based on user type -->
    <div class="collapse aletho-dropdown-body" id="customItemDropdown-<?= $book['id'] ?>">
        <form class="book-edit-form p-1" data-book-id="<?= $book['id'] ?>" method="get" action="/">
            <?php if ($_SESSION['user']['canEdit']): ?>
            <div class="input-group input-group-sm">
                <input type="text" class="aletho-inputs extra-input-style" id="book-name-<?= $book['id'] ?>" name="book-name" value="<?= htmlspecialchars($book['name']) ?>" disabled>
                <button type="button" class="btn btn-link extra-button-style" data-swap-targets="#book-name-<?= $book['id'] ?>" aria-label="Edit Book Name">✏️</button>
            </div>
            <?php endif; ?>

            <?php if ($_SESSION['user']['canEdit']): ?>
            <div class="input-group input-group-sm">
                <input type="text" class="aletho-inputs extra-input-style" id="book-writer-<?= $book['id'] ?>" name="book_writer" value="<?= htmlspecialchars($book['author'] ?? '') ?>" disabled>
                <button type="button" class="btn btn-link extra-button-style" data-swap-targets="#book-writer-<?= $book['id'] ?>" aria-label="Edit Author">✏️</button>
            </div>
            <?php else: ?>
            <input type="text" class="aletho-inputs extra-input-style" value="<?= htmlspecialchars($book['author'] ?? '') ?>" disabled>
            <?php endif; ?>

            <?php if ($_SESSION['user']['canEdit']): ?>
            <div class="input-group input-group-sm">
                <!-- Genre Select -->
                <select class="aletho-inputs form-select-sm extra-input-style" id="genre-input-<?= $book['id'] ?>" name="genre_id" disabled>
                    <?php foreach ($genres as $genre): ?>
                    <option value="<?= $genre['id'] ?>" <?= $book['genre'] === $genre['name'] ? 'selected' : '' ?>> <?= htmlspecialchars($genre['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <!-- Edit button -->
                <button type="button" class="btn btn-link extra-button-style" data-swap-targets="#genre-input-<?= $book['id'] ?>" aria-label="Edit Genre">✏️</button>
            </div>
            <?php else: ?>
            <input type="text" class="aletho-inputs extra-input-style" value="<?= htmlspecialchars($book['genre'] ?? 'Onbekend') ?>" disabled>
            <?php endif; ?>

            <?php if ($canEditOffice): ?>
            <div class="input-group input-group-sm">
                <!-- Office Select -->
                <select class="aletho-inputs form-select-sm extra-input-style" id="office-input-<?= $book['id'] ?>" name="office_id" disabled>
                    <?php foreach ($offices as $office): ?>
                    <option value="<?= $office['id'] ?>"<?= $book['office_id'] == $office['id'] ? 'selected' : '' ?>><?= htmlspecialchars($office['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <!-- Edit button -->
                <button type="button" class="btn btn-link extra-button-style" data-swap-targets="#office-input-<?= $book['id'] ?>" aria-label="Edit Genre">✏️</button>
            </div>
            <?php else:
                $bOffice = "";
                foreach ($offices as $office) {
                    if($office['id'] === $book['office_id']) {
                        $bOffice = $office['name'];
                    }
                } ?>
            <input type="text" class="aletho-inputs extra-input-style" value="<?=htmlspecialchars($bOffice) ?>" disabled>
            <?php endif; ?>

            <?php if ($_SESSION['user']['canEdit']): ?>
            <div class="input-group input-group-sm">
                <span class="aletho-labels extra-popin-style">Status</span>
                <div type="button" class="extra-fake-button"></div>
                <input type="text" class="aletho-inputs extra-input-style" id="book-status-<?= $book['id'] ?>" name="book_status" value="<?= htmlspecialchars($huidigeStatus) ?>" disabled>
                <div type="button" class="extra-fake-button"></div>
            </div>
            <?php else : ?>
            <span class="aletho-labels extra-popin-style">Status</span>
            <input type="text" class="aletho-inputs extra-input-style" id="book-status-<?= $book['id'] ?>" name="book_status" value="<?= htmlspecialchars($huidigeStatus) ?>" disabled>
            <?php endif; ?>

            <?php if ($_SESSION['user']['canEdit']): ?>
            <div class="input-group input-group-sm">
                <span class="aletho-labels extra-popin-style">Verloopt</span>
                <div type="button" class="extra-fake-button"></div>
                <input type="date" class="aletho-inputs extra-input-style" id="book-status-expires-<?= $book['id'] ?>" name="book_status_expires" value="<?= htmlspecialchars($statusExp) ?>" disabled>
                <div type="button" class="extra-fake-button"></div>
            </div>
            <?php else : ?>
            <span class="aletho-labels extra-popin-style">Verloopt</span>
            <input type="date" class="aletho-inputs extra-input-style" id="book-status-expires-<?= $book['id'] ?>" name="book_status_expires" value="<?= htmlspecialchars($statusExp) ?>" disabled>
            <?php endif; ?>

            <?php if($_SESSION['user']['canEdit']): ?>
            <div class="input-group input-group-sm">
                <span class="aletho-labels extra-popin-style">Vorige Leners</span>
                <div type="button" class="extra-fake-button"></div>
                <select class="aletho-inputs extra-popin-style" aria-label="Laatste lener">
                <?php foreach($loanerHistory as $index => $loaner): ?>
                    <option value="<?= $loaner['id'] ?>" <?= $index === 0 ? 'selected' : 'disabled' ?>>
                        <?= htmlspecialchars($loaner['name'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
                </select>
                <div type="button" class="extra-fake-button"></div>
            </div>
            <?php endif; ?>

            <?php if($_SESSION['user']['canEdit']): ?>
            <div class="input-group input-group-sm mt-1">
                <button id="boek-status-button" type="button" name="action" class="aletho-buttons">Status Aanpassen</button>
                <div type="button" class="extra-fake-button"></div>
                <button id="save-changes-<?= $book['id'] ?>" type="submit" name="save-item" class="aletho-buttons">Wijzigingen Opslaan</button>
                <div type="button" class="extra-fake-button"></div>
                <button type="submit" name="action" class="aletho-buttons">Boek Verwijderen</button>
                <div type="button" class="extra-fake-button"></div>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>