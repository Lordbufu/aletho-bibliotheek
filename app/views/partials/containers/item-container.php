<?php
    $canEditOffice = isset($userType) && ($userType === 'global_admins' || ($userType === 'office_admins' && isset($currentOfficeId) && $book['office_id'] == $currentOfficeId));
    ?>
<!-- /* Item container example, with a dropdown for potentially editable details and user based content. */ -->
<div class="item-container container-sm p-1" id="item-container">

    <!-- Default item info (always visible) -->
    <div class="secundary-color-1 item-name-label d-flex flex-row border border-dark mn-main-col">
        <button class="secundary-color-4 item-button buttons d-flex justify-content-start mn-main-col border-top-0 border-bottom-0 border-start-0"
                id="itemButton-<?= $book['id'] ?>"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#customItemDropdown-<?= $book['id'] ?>"
                aria-expanded="false"
                aria-controls="customItemDropdown-<?= $book['id'] ?>">▼</button>
        <span class="dropdown-item d-flex flex-fill mn-main-col text-center"><?= htmlspecialchars($book['name']) ?></span>
        <span class="status-dot d-flex justify-content-end statusOne" id="status-dot-1"></span>
    </div>

    <!-- Detailed item info, based on user type -->
    <div class="item-details flex-column mn-main-col collapse" id="customItemDropdown-<?= $book['id'] ?>">
        <form class="book-edit-form" data-book-id="<?= $book['id'] ?>">

            <!-- Book name (visable and editable only for admins) -->
            <?php if(isset($userType) && ($userType === 'office_admins' || $userType === 'global_admins')): ?>
                <div class="secundary-color-1 dropdown-container border border-top-0 border-dark d-flex align-items-center">
                    <input class="dropdown-item flex-fill" id="book-name-<?= $book['id'] ?>" name="book_name" value="<?= htmlspecialchars($book['name']) ?>" disabled>
                    <button type="button" class="btn btn-sm btn-link edit-field-btn ms-2" data-target="#book-name-<?= $book['id'] ?>">✏️</button>
                </div>
            <?php endif; ?>

            <!-- Book writer (editable for admins) -->
            <div class="secundary-color-1 dropdown-container border border-top-0 border-dark d-flex align-items-center">
                <input class="dropdown-item flex-fill" id="book-writer-<?= $book['id'] ?>" name="book_writer" value="<?= htmlspecialchars($book['author'] ?? '') ?>" disabled>
                <?php if(isset($userType) && ($userType === 'office_admins' || $userType === 'global_admins')): ?>
                    <button type="button" class="btn btn-sm btn-link edit-field-btn ms-2" data-target="#book-writer-<?= $book['id'] ?>">✏️</button>
                <?php endif; ?>
            </div>

            <!-- Categorie (genre) -->
            <div class="secundary-color-1 dropdown-container border border-top-0 border-dark d-flex align-items-center">
                <!-- display input -->
                <input id="genre-display-<?= $book['id'] ?>" class="dropdown-item flex-fill" value="<?= htmlspecialchars($book['genre']) ?>" disabled>

                <?php if(isset($userType) && ($userType === 'office_admins' || $userType === 'global_admins')): ?>
                    <!-- hidden select -->
                    <select id="genre-input-<?= $book['id'] ?>" name="genre_id" class="dropdown-item dropdown-select text-center d-none" disabled>
                        <?php foreach($genres as $genre): ?>
                            <option value="<?= $genre['id'] ?>" <?= $book['genre'] == $genre['name'] ? 'selected' : '' ?> >
                                <?= htmlspecialchars($genre['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- edit button -->
                    <button type="button" class="btn btn-sm btn-link edit-field-btn ms-2" aria-label="Edit Categorie" data-swap-targets="#genre-display-<?= $book['id'] ?>, #genre-input-<?= $book['id'] ?>">✏️</button>
                <?php endif; ?>
            </div>

            <!-- Vestiging (office) -->
            <div class="secundary-color-1 dropdown-container border border-top-0 border-dark d-flex align-items-center">
                <!-- display input -->
                <input id="office-display-<?= $book['id'] ?>" class="dropdown-item flex-fill" value="<?= htmlspecialchars($book['office'] ?? '') ?>" disabled >
               
                <?php if($canEditOffice): ?>
                    <!-- hidden select -->
                    <select id="office-input-<?= $book['id'] ?>" name="office_id" class="dropdown-item dropdown-select text-center d-none" disabled>
                        <?php foreach($offices as $office): ?>
                            <option value="<?= $office['id'] ?>" <?= $book['office_id'] == $office['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($office['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- edit button -->
                    <button type="button" class="btn btn-sm btn-link edit-field-btn ms-2" aria-label="Edit Vestiging" data-swap-targets="#office-display-<?= $book['id'] ?>, #office-input-<?= $book['id'] ?>">✏️</button>
                <?php endif; ?>
            </div>

            <div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
                <input class="dropdown-item" value="Status" disabled>
            </div>
        
            <div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
                <input class="dropdown-item" value="Status verlopen" disabled>
            </div>

            <!-- Admin additions: -->
            <?php if(isset($userType) && ($userType === 'office_admins' || $userType === 'global_admins')): ?>
                <div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
                    <input class="dropdown-item" value="Laatste lener" disabled>
                </div>

                <div class="secundary-color-1 border border-top-0 border-dark">
                    <button
                        class="secundary-color-4 buttons text-black rounded-2 m-1 text-center"
                        id="boek-save-button-<?= $book['id'] ?>"
                        type="submit"
                        name="action"
                        value="save_changes">Wijzigingen Opslaan</button>
                </div>

                <div class="secundary-color-1 border border-top-0 border-dark">
                    <button
                        class="secundary-color-4 buttons text-black rounded-2 m-1 text-center"
                        id="boek-status-button-<?= $book['id'] ?>"
                        type="submit"
                        name="action"
                        value="status_update">Status Aanpassen</button>
                </div>

                <div class="secundary-color-1 border border-top-0 border-dark">
                    <button
                        class="secundary-color-4 buttons text-black rounded-2 m-1 text-center"
                        id="boek-delete-button-<?= $book['id'] ?>"
                        type="submit"
                        name="action"
                        value="delete">Boek Verwijderen</button>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
    $(document).on('click', '.edit-field-btn', function(){
        // grab the two selectors from data-swap-targets
        const [dispSel, editSel] = $(this).data('swap-targets').split(',').map(s=>s.trim());
        const $disp = $(dispSel);
        const $edit = $(editSel);

        // toggle visibility (d-none) and disabled state
        $disp.toggleClass('d-none').prop('disabled', !$disp.prop('disabled'));
        $edit.toggleClass('d-none').prop('disabled', !$edit.prop('disabled'));

        // focus the now-visible element
        ;(!$edit.prop('disabled') ? $edit : $disp).focus();
        console.log($dispSel);
        console.log($editSel);
        console.log($disp);
        console.log($edit);
    });
</script>