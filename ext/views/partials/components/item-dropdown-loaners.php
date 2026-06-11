<?php if(canEdit()): ?>
    <div class="input-group input-group-sm" data-context="details">
        <span class="aletho-labels extra-popin-style">Huidige / Vorige Leners</span>
        <div type="button" class="extra-fake-button"></div>
    </div>

    <div class="input-group input-group-sm" data-context="details">
        <select class="aletho-inputs extra-input-style" data-context="details">
            <?php foreach ($book->loaners as $index => $loaner):
                    if ($index === 0) : ?>
                <option selected disabled><?= htmlspecialchars($loaner) ?></option>
            <?php else: ?>
                <option disabled><?= htmlspecialchars($loaner) ?></option>
            <?php endif; endforeach; ?>
        </select>
        <div type="button" class="extra-fake-button"></div>
    </div>
<?php endif; ?>