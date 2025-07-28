<!-- app/views/partials/popins/add-book-popin.php -->
<div id="add-book-popin" class="modal" tabindex="-1" style="display:none;">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-white text-center">
      <div class="modal-header">
        <h5 class="modal-title w-100 text-center">Boek Toevoegen</h5>
        <button type="button" class="btn-close" id="close-add-book-popin"></button>
      </div>
      <div class="modal-body">
        <form id="add-book-form">
          <div class="mb-3">
            <label for="book-name" class="form-label text-center w-100">Boeknaam</label>
            <input type="text" class="form-control text-center" id="book-name" name="book_name" required>
          </div>
          <div class="mb-3">
            <label for="writer" class="form-label text-center w-100">Auteur</label>
            <input type="text" class="form-control text-center" id="writer" name="writer" required>
          </div>
          <div class="mb-3">
            <label for="genre" class="form-label text-center w-100">Genre</label>
            <input type="text" class="form-control text-center" id="genre" name="genre" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Opslaan</button>
        </form>
      </div>
    </div>
  </div>
</div>