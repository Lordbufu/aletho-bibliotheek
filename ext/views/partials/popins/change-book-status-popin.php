<div id="change-book-status-popin" class="modal" tabindex="-1" style="display:none;">
    <div class="modal-dialog modal-dialog-centered" style="margin:auto;">
        <div class="modal-content aletho-modal-content">
            <div class="aletho-header modal-header pt-2 pb-2">
                <h5 class="modal-title w-100">Boekstatus Aanpassen</h5>
                <button type="button" class="btn-close btn-close-white" id="close-change-book-status-popin"></button>
            </div>
            <div class="aletho-modal-body p-1">
                <form id="change-book-status-form mb-1" method="post" action="/changeStatus">
                    <input type="hidden" name="_method" value="PATCH">
                    <input type="hidden" name="book_id" id="change-book-id">
                    <div class="input-group input-group-sm">
                        <label for="change-status-type" class="aletho-labels extra-popin-style">Status</label>
                        <select class="aletho-inputs extra-popin-style" id="change-status-type" name="status_type" required>
                        </select>
                        <?php if (!empty($popErrors['status_type'])): ?>
                            <div class="aletho-alert-inline aletho-border"><?= htmlspecialchars($popErrors['status_type']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="input-group input-group-sm">
                        <label for="change-loaner-name" class="aletho-labels extra-popin-style">Lener Naam</label>
                        <input  type="text"
                                class="aletho-inputs extra-popin-style mb-2"
                                id="change-loaner-name"
                                name="loaner_name"
                                autocomplete="off">
                        <?php if (!empty($popErrors['loaner_name'])): ?>
                            <div class="aletho-alert-inline aletho-border"><?= htmlspecialchars($popErrors['loaner_name']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="input-group input-group-sm">
                        <label for="change-loaner-email" class="aletho-labels extra-popin-style">E-mail</label>
                        <input  type="email"
                                class="aletho-inputs extra-popin-style"
                                id="change-loaner-email"
                                name="loaner_email"
                                autocomplete="off">
                        <?php if (!empty($popErrors['loaner_email'])): ?>
                            <div class="aletho-alert-inline aletho-border"><?= htmlspecialchars($popErrors['loaner_email']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="input-group input-group-sm">
                        <label for="change-loaner-location" class="aletho-labels extra-popin-style">Lener Locatie</label>
                        <select id="change-loaner-location" name="loaner_location" class="aletho-inputs extra-popin-style mb-2"> </select>
                        <?php if (!empty($popErrors['loaner_location'])): ?>
                            <div class="aletho-alert-inline aletho-border"><?= htmlspecialchars($popErrors['loaner_location']) ?></div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="aletho-buttons extra-popin-style">Opslaan</button>
                </form>
            </div>
        </div>
    </div>
</div>