<div id="change-book-status-popin" class="modal" tabindex="-1" style="display:none;">
    <div class="modal-dialog modal-dialog-centered" style="margin:auto;">
        <div class="modal-content aletho-modal-content">

            <div class="aletho-header modal-header pt-2 pb-2">
                <h5 class="modal-title w-100">Boekstatus Aanpassen</h5>
                <button type="button" class="btn-close btn-close-white" id="close-change-book-status-popin"></button>
            </div>

            <div class="aletho-modal-body p-1">
                <form id="change-book-status-form mb-1">
                    <label for="change-status-type" class="aletho-labels extra-popin-style">Status</label>
                    <select class="aletho-inputs extra-popin-style" id="change-status-type" name="change_status_type" required>
                        <option value="default" selected disabled hidden>Selecteer een status</option>
                        <option value="<?= $status['id'] ?>"><?= htmlspecialchars($status['type']) ?></option>
                    </select>

                    <label for="change-loaner-email" class="aletho-labels extra-popin-style">E-mail</label>
                    <input type="email" class="aletho-inputs extra-popin-style" id="change-loaner-email" name="change_loaner_email" required>

                    <label for="change-loaner-name" class="aletho-labels extra-popin-style">Lener Naam</label>
                    <input type="text" class="aletho-inputs extra-popin-style mb-2" id="change-loaner-name" name="change_loaner_name" required>

                    <button type="submit" class="aletho-buttons extra-popin-style">Opslaan</button>
                </form>
            </div>

        </div>
    </div>
</div>