<div id="password-reset-popin" class="modal" tabindex="-1" style="display:none;">
    <div class="modal-dialog modal-dialog-centered" style="margin:auto;">
        <div class="modal-content aletho-modal-content">

            <div class="aletho-header modal-header pt-2 pb-2">
                <h5 class="modal-title w-100">Wachtwoord Resetten</h5>
                <button type="button" class="btn-close btn-close-white" id="close-password-reset-popin"></button>
            </div>

            <div class="aletho-modal-body p-1">
                <form id="password-reset-form mb-1" method="POST" action="/resetPassword">
                    <input type="hidden" name="_method" value="PATCH">

                    <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'Global Admin'): ?>
                        <label for="user-name" class="aletho-labels extra-popin-style">Account</label>
                        <input  type="text"
                                class="aletho-inputs extra-popin-style"
                                id="user-name"
                                name="user_name"
                                value="<?= htmlspecialchars($old['user_name'] ?? '') ?>"
                                required>
                    <?php else: ?>
                        <label for="current-password" class="aletho-labels extra-popin-style">Huidig Wachtwoord</label>
                        <input  type="password"
                                class="aletho-inputs extra-popin-style"
                                id="current-password"
                                name="current_password"
                                required>
                    <?php endif; ?>

                    <label for="new-password" class="aletho-labels extra-popin-style">Nieuw Wachtwoord</label>
                    <input  type="password"
                            class="aletho-inputs extra-popin-style"
                            id="new-password"
                            name="new_password"
                            required>

                    <label for="confirm-password" class="aletho-labels extra-popin-style">Bevestig Nieuw Wachtwoord</label>
                    <input  type="password"
                            class="aletho-inputs extra-popin-style mb-2"
                            id="confirm-password"
                            name="confirm_password"
                            required>

                    <?php if (!empty($_SESSION['_flashInline'])): ?>
                        <div class="aletho-border mt-1 aletho-inline-<?= $_SESSION['_flashInline']['type'] ?>">
                            <?= htmlspecialchars($_SESSION['_flashInline']['message']) ?>
                        </div>
                        <?php unset($_SESSION['_flashInline']); ?>
                    <?php endif; ?>

                    <button type="submit" class="aletho-buttons extra-popin-style">Resetten</button>
                </form>
            </div>
        </div>
    </div>
</div>