<div id="status-period-popin" class="modal" tabindex="-1" style="display:none;">
    <div class="modal-dialog modal-dialog-centered" style="margin:auto;">
        <div class="modal-content aletho-modal-content">

            <div class="aletho-header modal-header pt-2 pb-2">
                <h5 class="modal-title w-100">Status Periode Aanpassen</h5>
                <button type="button" class="btn-close btn-close-white" id="close-status-period-popin"></button>
            </div>

            <div class="aletho-modal-body p-1">
                <form id="status-period-form" method="post" action="/setStatusPeriod">
                    <input type="hidden" name="_method" value="PATCH">

                    <div class="input-group input-group-sm">
                        <label for="status-type" class="aletho-labels extra-popin-style">Status</label>
                        <select class="aletho-inputs extra-popin-style" id="status-type" name="status_type" required>
                        </select>

                        <?php if (!empty($popErrors['status_type'])): ?>
                            <div class="aletho-alert-inline aletho-border"><?= htmlspecialchars($popErrors['status_type']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="input-group input-group-sm">
                        <label for="periode-length" class="aletho-labels extra-popin-style">Periode Lengte (dagen)</label>
                        <input  type="number"
                                class="aletho-inputs extra-popin-style"
                                id="periode-length"
                                name="periode_length"
                                value="<?= htmlspecialchars($old['periode_length'] ?? '') ?>">

                        <?php if (!empty($popErrors['periode_length'])): ?>
                            <div class="aletho-alert-inline aletho-border"><?= htmlspecialchars($popErrors['periode_length']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="input-group input-group-sm">
                        <label for="reminder-day" class="aletho-labels extra-popin-style">Herinnering (dagen voor einde)</label>
                        <input  type="number"
                                class="aletho-inputs extra-popin-style"
                                id="reminder-day"
                                name="reminder_day"
                                value="<?= htmlspecialchars($old['reminder_day'] ?? '') ?>">

                        <?php if (!empty($popErrors['reminder_day'])): ?>
                            <div class="aletho-alert-inline aletho-border"><?= htmlspecialchars($popErrors['reminder_day']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="input-group input-group-sm">
                        <label for="overdue-day" class="aletho-labels extra-popin-style">Overdue (dagen na einde)</label>
                        <input  type="number"
                                class="aletho-inputs extra-popin-style"
                                id="overdue-day"
                                name="overdue_day"
                                value="<?= htmlspecialchars($old['overdue_day'] ?? '') ?>">
                        
                        <?php if (!empty($popErrors['overdue_day'])): ?>
                            <div class="aletho-alert-inline aletho-border"><?= htmlspecialchars($popErrors['overdue_day']) ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="aletho-buttons extra-popin-style">Opslaan</button>
                </form>
            </div>
        </div>
    </div>
</div>