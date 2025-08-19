<div id="status-period-popin" class="modal" tabindex="-1" style="display:none;">
    <div class="modal-dialog modal-dialog-centered" style="margin:auto;">
        <div class="modal-content aletho-modal-content">

            <div class="aletho-header modal-header pt-2 pb-2">
                <h5 class="modal-title w-100">Status Periode Aanpassen</h5>
                <button type="button" class="btn-close btn-close-white" id="close-status-period-popin"></button>
            </div>

            <div class="aletho-modal-body p-1">
                <form id="status-period-form mb-1">
                    <label for="status-type" class="aletho-labels extra-popin-style">Status</label>
                    <select class="aletho-inputs extra-popin-style" id="status-type" name="status_type" required>
                        <?php foreach ($statuses as $status): ?>
                        <option value="<?= $status['id'] ?>" data-periode_length="<?= $status['periode_length'] ?>" data-reminder_day="<?= $status['reminder_day'] ?>" data-overdue_day="<?= $status['overdue_day'] ?>" >
                            <?= htmlspecialchars($status['type']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="periode-length" class="aletho-labels extra-popin-style">Periode Lengte (dagen)</label>
                    <input type="number" class="aletho-inputs extra-popin-style" id="periode-length" name="periode_length" min="1" required>

                    <label for="reminder-day" class="aletho-labels extra-popin-style">Herinnering (dagen voor einde)</label>
                    <input type="number" class="aletho-inputs extra-popin-style" id="reminder-day" name="reminder_day" min="0" required>

                    <label for="overdue-day" class="aletho-labels extra-popin-style">Overdue (dagen na einde)</label>
                    <input type="number" class="aletho-inputs extra-popin-style mb-2" id="overdue-day" name="overdue_day" min="0" required>

                    <button type="submit" class="aletho-buttons extra-popin-style">Opslaan</button>
                </form>
            </div>
        </div>
    </div>
</div>