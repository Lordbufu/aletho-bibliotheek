<!-- app/views/partials/popins/password-reset-popin.php -->
<div id="password-reset-popin" class="modal" tabindex="-1" style="display:none;">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-white text-center">
      <div class="modal-header">
        <h5 class="modal-title w-100 text-center">Wachtwoord Resetten</h5>
        <button type="button" class="btn-close" id="close-password-reset-popin"></button>
      </div>
      <div class="modal-body">
        <form id="password-reset-form">
          <?php if (isset($userType) && $userType === 'global_admin'): ?>
            <div class="mb-3">
              <label for="reset-user" class="form-label text-center w-100">Account</label>
              <select class="form-select text-center" id="reset-user" name="reset_user" required>
                <?php foreach ($users as $user): ?>
                  <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php else: ?>
            <div class="mb-3">
              <label for="current-password" class="form-label text-center w-100">Huidig Wachtwoord</label>
              <input type="password" class="form-control text-center" id="current-password" name="current_password" required>
            </div>
          <?php endif; ?>
          <div class="mb-3">
            <label for="new-password" class="form-label text-center w-100">Nieuw Wachtwoord</label>
            <input type="password" class="form-control text-center" id="new-password" name="new_password" required>
          </div>
          <div class="mb-3">
            <label for="confirm-password" class="form-label text-center w-100">Bevestig Nieuw Wachtwoord</label>
            <input type="password" class="form-control text-center" id="confirm-password" name="confirm_password" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Resetten</button>
        </form>
      </div>
    </div>
  </div>
</div>