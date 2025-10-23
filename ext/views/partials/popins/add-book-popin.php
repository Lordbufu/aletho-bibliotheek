<div id="add-book-popin" class="modal clear-on-close" tabindex="-1" style="display:none;">
    <div class="modal-dialog modal-dialog-centered" style="margin:auto;">
        <div class="modal-content aletho-modal-content">
            <div class="aletho-header modal-header pt-2 pb-2">
                <h5 class="modal-title w-100">Boek Toevoegen</h5>
                <button type="button" class="btn-close btn-close-white" id="close-add-book-popin"></button>
            </div>
            <div class="aletho-modal-body">
                <form id="add-book-form" method="post" action="/addBook">
                    <div class="input-group input-group-sm">
                        <label for="book-name" class="aletho-labels extra-popin-style">Boeknaam</label>
                        <input  type="text"
                                class="aletho-inputs extra-input-style"
                                id="book-name-add"
                                name="book_name"
                                placeholder="Type een boek naam.">
                        <?php if (!empty($popErrors['book_name'])): ?>
                            <div class="aletho-alert-inline aletho-border"><?= htmlspecialchars($popErrors['book_name']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="input-group input-group-sm">
                        <label for="writer" class="aletho-labels extra-popin-style" data-context="popin">Schrijver</label>
                        <div class="add-writer-tags-container" data-context="popin"></div>
                        <input  type="text"
                                class="aletho-inputs extra-input-style writer-input-pop"
                                id="book-writer-add"
                                placeholder="Type een schrijver naam, en druk op Enter"
                                autocomplete="off"
                                data-context="popin">
                        <?php if (!empty($popErrors['book_writers'])): ?>
                            <div class="aletho-alert-inline aletho-border"><?= htmlspecialchars($popErrors['book_writers']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="input-group input-group-sm">
                        <label for="genre" class="aletho-labels extra-popin-style" data-context="popin">Genre</label>
                        <div class="add-genre-tags-container" data-context="popin"></div>
                        <input  type="text"
                                class="aletho-inputs extra-input-style genre-input-pop"
                                id="book-genre-add"
                                placeholder="Type een genre naam, en druk op Enter"
                                autocomplete="off"
                                data-context="popin">
                        <?php if (!empty($popErrors['book_genres'])): ?>
                            <div class="aletho-alert-inline aletho-border"><?= htmlspecialchars($popErrors['book_genres']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="input-group input-group-sm">
                        <label for="office" class="aletho-labels extra-popin-style" data-context="popin">Locatie</label>
                        <div class="add-office-tags-container" data-context="popin"></div>
                        <input  type="text"
                                class="aletho-inputs extra-input-style office-input-pop"
                                id="book-office-add"
                                placeholder="Type een locatie naam, en druk op Enter"
                                autocomplete="off"
                                data-context="popin">
                        <?php if (!empty($popErrors['book_offices'])): ?>
                            <div class="aletho-alert-inline aletho-border"><?= htmlspecialchars($popErrors['book_offices']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="aletho-buttons extra-input-style">Opslaan</button>
                </form>
            </div>
        </div>
    </div>
</div>