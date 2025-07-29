<!-- app/views/partials/popins/status-period-popin.php -->
<div id="status-period-popin" class="modal" tabindex="-1" style="display:none;">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-white text-center">
      <div class="modal-header">
        <h5 class="modal-title w-100 text-center">Status Periode Aanpassen</h5>
        <button type="button" class="btn-close" id="close-status-period-popin"></button>
      </div>
      <div class="modal-body">
        <form id="status-period-form">
          <div class="mb-3">
            <label for="status-type" class="form-label text-center w-100">Status</label>
            <select class="form-select text-center" id="status-type" name="status_type" required>
                <!-- Dynamically populate from DB in production -->
                <?php foreach ($statuses as $status): ?>
                    <option
                        value="<?= $status['id'] ?>"
                        data-periode_length="<?= $status['periode_length'] ?>"
                        data-reminder_day="<?= $status['reminder_day'] ?>"
                        data-overdue_day="<?= $status['overdue_day'] ?>" >
                        <?= htmlspecialchars($status['type']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="periode-length" class="form-label text-center w-100">Periode Lengte (dagen)</label>
            <input type="number" class="form-control text-center" id="periode-length" name="periode_length" min="1" required>
          </div>
          <div class="mb-3">
            <label for="reminder-day" class="form-label text-center w-100">Herinnering (dagen voor einde)</label>
            <input type="number" class="form-control text-center" id="reminder-day" name="reminder_day" min="0" required>
          </div>
          <div class="mb-3">
            <label for="overdue-day" class="form-label text-center w-100">Overdue (dagen na einde)</label>
            <input type="number" class="form-control text-center" id="overdue-day" name="overdue_day" min="0" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Opslaan</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
$(function() {
  $('#periode-wijz-button').on('click', function() {
    $('#status-period-popin').show();
  });
  $('#close-status-period-popin').on('click', function() {
    $('#status-period-popin').hide();
  });
  // Optional: Hide modal when clicking outside the modal-content
  $('#status-period-popin').on('click', function(e) {
    if (e.target === this) {
      $(this).hide();
    }
  });
});
</script>
