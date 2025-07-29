<!-- app/views/partials/popins/change-book-status-popin.php -->
<div id="change-book-status-popin" class="modal" tabindex="-1" style="display:none;">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-white text-center">
      <div class="modal-header">
        <h5 class="modal-title w-100 text-center">Boekstatus Aanpassen</h5>
        <button type="button" class="btn-close" id="close-change-book-status-popin"></button>
      </div>
      <div class="modal-body">
        <form id="change-book-status-form">
          <div class="mb-3">
            <label for="change-status-type" class="form-label text-center w-100">Status</label>
            <select class="form-select text-center" id="change-status-type" name="change_status_type" required>
                <option value="default" selected disabled hidden>Selecteer een status</option>
              <?php foreach ($statusTypes as $status): ?>
                <option value="<?= $status['id'] ?>"><?= htmlspecialchars($status['type']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="change-loaner-email" class="form-label text-center w-100">E-mail</label>
            <input type="email" class="form-control text-center" id="change-loaner-email" name="change_loaner_email" required>
          </div>
          <div class="mb-3">
            <label for="change-loaner-name" class="form-label text-center w-100">Lener Naam</label>
            <input type="text" class="form-control text-center" id="change-loaner-name" name="change_loaner_name" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Opslaan</button>
        </form>
      </div>
    </div>
  </div>
</div>
