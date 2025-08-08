<div id="password-reset-popin" class="modal" tabindex="-1" style="display:none;">
    <div class="modal-dialog modal-dialog-centered" style="max-width:90vw; margin:auto;">
        <div class="modal-content aletho-modal-content">

            <div class="aletho-header modal-header pt-2 pb-2">
                <h5 class="modal-title w-100">Wachtwoord Resetten</h5>
                <button type="button" class="btn-close btn-close-white" id="close-password-reset-popin"></button>
            </div>

            <div class="aletho-modal-body p-1">
                <form id="password-reset-form mb-1">
                    <?php if (isset($userType) && $userType === 'global_admin'): ?>
                    <!-- account name field for global admins -->
                    <label for="reset-user" class="aletho-labels extra-popin-style">Account</label>
                    <select class="aletho-inputs extra-popin-style" id="reset-user" name="reset_user" required>
                        <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php else: ?>
                    <!-- current password field for office admins -->
                    <label for="current-password" class="aletho-labels extra-popin-style">Huidig Wachtwoord</label>
                    <input type="password" class="aletho-inputs extra-popin-style" id="current-password" name="current_password" required>
                    <?php endif; ?>

                    <!-- new password field -->
                    <label for="new-password" class="aletho-labels extra-popin-style">Nieuw Wachtwoord</label>
                    <input type="password" class="aletho-inputs extra-popin-style" id="new-password" name="new_password" required>

                    <!-- confirm password field -->
                    <label for="confirm-password" class="aletho-labels extra-popin-style">Bevestig Nieuw Wachtwoord</label>
                    <input type="password" class="aletho-inputs extra-popin-style mb-2" id="confirm-password" name="confirm_password" required>

                    <!-- submit -->
                    <button type="submit" class="aletho-buttons extra-popin-style">Resetten</button>
                </form>
            </div>
        </div>
    </div>
</div>