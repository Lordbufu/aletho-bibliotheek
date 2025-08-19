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
                    <input type="text" class="aletho-inputs extra-popin-style" id="book-name" name="book_name" required>

                    <label for="writer" class="aletho-labels extra-popin-style">Auteur</label>
                    <input type="text" class="aletho-inputs extra-popin-style" id="writer" name="writer" required>

                    <label for="genre" class="aletho-labels extra-popin-style">Genre</label>
                    <input type="text" class="aletho-inputs extra-popin-style" id="genre" name="genre" required>

                    <label for="office" class="aletho-labels extra-popin-style">Locatie</label>
                    <select class="aletho-inputs extra-popin-style mb-2" id="office" name="office_id" >
                        <option value="dummy" selected>Maak een selectie ..</option>
                        <?php foreach ($offices as $office): ?>
                        <option value="<?= $office['id'] ?>"><?= htmlspecialchars($office['name']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" class="aletho-buttons extra-popin-style">Opslaan</button>
                </form>
            </div>

        </div>
    </div>
</div>