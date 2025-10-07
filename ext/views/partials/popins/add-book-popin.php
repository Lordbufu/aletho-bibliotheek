<div id="add-book-popin" class="modal" tabindex="-1" style="display:none;">
    <div class="modal-dialog modal-dialog-centered" style="margin:auto;">
        <div class="modal-content aletho-modal-content">

            <div class="aletho-header modal-header pt-2 pb-2">
                <h5 class="modal-title w-100">Boek Toevoegen</h5>
                <button type="button" class="btn-close btn-close-white" id="close-add-book-popin"></button>
            </div>

            <div class="aletho-modal-body">
                <form id="add-book-form">
                    <label for="book-name" class="aletho-labels extra-popin-style">Boeknaam</label>
                    <input type="text"
                        class="aletho-inputs extra-input-style"
                        id="book-name-add"
                        name="book_name"
                        placeholder="Type een boek naam en druk op Enter."
                        required>

                    <div class="add-writer-tags-container"></div>
                    <label for="writer" class="aletho-labels extra-popin-style">Schrijver</label>
                    <input type="text"
                        class="aletho-inputs extra-input-style writer-input"
                        id="book-writer-add"
                        name="book_writers"
                        placeholder="Type een schrijver naam, en druk op Enter"
                        autocomplete="off"
                        required>

                    <div class="add-genre-tags-container"></div>
                    <label for="genre" class="aletho-labels extra-popin-style">Genre</label>
                    <input type="text"
                        class="aletho-inputs extra-popin-style genre-input"
                        id="book-genre-add"
                        name="book_genre"
                        placeholder="Type een genre naam, en druk op Enter"
                        autocomplete="off"
                        required>

                    <div class="add-office-tags-container"></div>
                    <label for="office" class="aletho-labels extra-popin-style">Locatie</label>
                    <input type="text"
                        class="aletho-inputs extra-popin-style office-input"
                        id="book-office-add"
                        name="book_office"
                        placeholder="Type een locatie naam, en druk op Enter"
                        autocomplete="off"
                        required>

                    <button type="submit" class="aletho-buttons extra-popin-style">Opslaan</button>
                </form>
            </div>

        </div>
    </div>
</div>