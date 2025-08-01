<?php    // Should be evaluate via session data in the controlller
    $canEdit = $perm->canEdit();
    $canEditOffice = $perm->canEditOffice($book['office_id']);
    foreach($statuses as $status) {
        if($status['id'] === $book['status_id']) {
            $huidigeStatus = $status['type'];
        }
    }
    $statusVerl = date('25/01/1873');
?>

<!-- /* Item container example, with a dropdown for potentially editable details and user based content. */ -->
<div class="item-container container-sm p-1" id="item-container-<?= $book['id'] ?>">

    <!-- Top bar: collapse toggle, title, status dot (always visible) -->
    <div class="d-flex align-items-center secundary-color-1 border border-dark">
        <button class="btn btn-sm secundary-color-4 item-button border-0"
                id="itemButton-<?= $book['id'] ?>"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#customItemDropdown-<?= $book['id'] ?>"
                aria-expanded="false"
                aria-controls="customItemDropdown-<?= $book['id'] ?>"
        >▼</button>
        <span class="dropdown-item flex-fill text-center mn-main-col"> <?= htmlspecialchars($book['name']) ?> </span>
        <span class="status-dot d-flex justify-content-end statusOne" id="status-dot-<?= $book['id'] ?>"></span>
    </div>

    <!-- Detailed item info, based on user type -->
    <div class="collapse item-details secundary-color-1 border border-top-0 border-dark" id="customItemDropdown-<?= $book['id'] ?>">
        <form class="book-edit-form p-1" data-book-id="<?= $book['id'] ?>" method="get" action="/">
            <!-- Name and Author container -->
            <div class="row">
                <?php if ($canEdit): ?>
                <div class="col-12 col-md-6">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control text-center" id="book-name-<?= $book['id'] ?>" name="book-name" value="<?= htmlspecialchars($book['name']) ?>" disabled>
                        <button type="button" class="btn btn-link edit-field-btn" data-swap-targets="#book-name-<?= $book['id'] ?>" aria-label="Edit Book Name">✏️</button>
                    </div>
                </div>
                <?php endif; ?>
                <div class="col-12 col-md-6">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control text-center" id="book-writer-<?= $book['id'] ?>" name="book_writer" value="<?= htmlspecialchars($book['author'] ?? '') ?>" disabled>
                        <?php if ($canEdit): ?>
                        <button type="button" class="btn btn-link edit-field-btn" data-swap-targets="#book-writer-<?= $book['id'] ?>" aria-label="Edit Author">✏️</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Genre -->
            <div class="col-12 col-md-3">
                <?php if ($canEdit): ?>
                <div class="input-group input-group-sm">
                    <!-- Genre Select -->
                    <select class="form-select form-select-sm text-center" id="genre-input-<?= $book['id'] ?>" name="genre_id" disabled>
                        <?php foreach ($genres as $genre): ?>
                        <option value="<?= $genre['id'] ?>" <?= $book['genre'] === $genre['name'] ? 'selected' : '' ?>> <?= htmlspecialchars($genre['name']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Edit button -->
                    <button type="button" class="btn btn-link edit-field-btn" data-swap-targets="#genre-input-<?= $book['id'] ?>" aria-label="Edit Genre">✏️</button>
                </div>
                <?php else: ?>
                <div class="form-control form-control-sm bg-light text-muted"><?= htmlspecialchars($book['genre'] ?? 'Onbekend') ?></div>
                <?php endif; ?>
            </div>

            <!-- Office -->
            <div class="col-12 col-md-3">
                <?php if ($canEditOffice): ?>
                <div class="input-group input-group-sm">
                    <!-- Office Select -->
                    <select class="form-select form-select-sm text-center" id="office-input-<?= $book['id'] ?>" name="office_id" disabled>
                        <?php foreach ($offices as $office): ?>
                        <option value="<?= $office['id'] ?>"<?= $book['office_id'] == $office['id'] ? 'selected' : '' ?>><?= htmlspecialchars($office['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <!-- Edit button -->
                    <button type="button" class="btn btn-link edit-field-btn" data-swap-targets="#office-input-<?= $book['id'] ?>" aria-label="Edit Genre">✏️</button>
                </div>
                <?php else: ?>
                        <div class="form-control form-control-sm bg-light text-muted">
                        <?php
                            foreach ($offices as $office):
                                if($office['id'] === $book['office_id']) : 
                                    echo htmlspecialchars($office['name']);
                                endif;
                            endforeach; ?>
                        </div>
                <?php endif; ?>
            </div>

            <!-- Status, Status expires and Loaner history container -->
            <div class="row">
                <!-- Status Field -->
                <div class="col-12 col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text status-badge">Status</span>
                        <input type="text" class="form-control text-center" id="book-status-<?= $book['id'] ?>" name="book_status" value="<?= htmlspecialchars($huidigeStatus) ?>" disabled>
                    </div>
                </div>

                <!-- Expiry Field -->
                <div class="col-12 col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text status-badge">Verloopt</span>
                        <input type="text" class="form-control text-center" id="book-status-expires-<?= $book['id'] ?>" name="book_status_expires" value="<?= htmlspecialchars($statusVerl) ?>" disabled>
                    </div>
                </div>

                <?php if($canEdit): ?>
                <!-- Loaner history (Admin only) -->
                <div class="col-12 col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text status-badge">Vorige Leners</span>
                        <select class="form-select form-select-sm text-center" aria-label="Laatste lener">
                        <?php foreach($loanerHistory as $index => $loaner): ?>
                            <option value="<?= $loaner['id'] ?>" <?= $index === 0 ? 'selected' : 'disabled' ?>>
                                <?= htmlspecialchars($loaner['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Admin controlle buttons -->
            <div class="d-flex flex-column flex-md-row gap-1 mt-1">
                <?php if($canEdit): ?>
                <button type="submit" name="action" class="btn btn-sm secundary-color-4 text-black w-100 w-md-auto">Status Aanpassen</button>
                <button id="save-changes-<?= $book['id'] ?>" type="submit" name="save-item" class="btn btn-sm secundary-color-4 text-black w-100 w-md-auto">Wijzigingen Opslaan</button>
                <button type="submit" name="action" class="btn btn-sm secundary-color-4 text-black w-100 w-md-auto ms-md-auto">Boek Verwijderen</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>