<!-- /* Item container example, with a dropdown for potetially editable details and user based content. */ -->
<div class="item-container container-sm p-1" id="item-container">

    <!-- Default item info -->
    <div class="secundary-color-1 item-name-label d-flex flex-row border border-dark mn-main-col">
        <button class= "secundary-color-4
                        item-button
                        buttons
                        d-flex
                        justify-content-start
                        mn-main-col
                        border-top-0
                        border-bottom-0
                        border-start-0"
                id="itemButton-<?= $book['id'] ?>"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#customItemDropdown-<?= $book['id'] ?>"
                aria-expanded="false"
                aria-controls="customItemDropdown-<?= $book['id'] ?>">▼</button>
        <input  class= "dropdown-item
                        d-flex
                        flex-fill
                        mn-main-col
                        text-center"
                value="<?= $book['name'] ?? 'Boek Naam 1'; ?>" disabled>
        <span class="status-dot d-flex justify-content-end statusOne" id="status-dot-1"></span>
    </div>

    <!-- Detailed item info, based on user type -->
    <div class="item-details flex-column mn-main-col collapse" id="customItemDropdown-<?= $book['id'] ?>">
        <!-- /* For Admin users, i need to create edit events that make the inputs editable.
            And ill likely need a form around the enitre item, with JS code that add the potentially changed boek name when submitted.
            For the user these can all be labels or something else that simply displays the text from the database. */ -->
        <div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
            <input class="dropdown-item" value="Schrijver" disabled>
        </div>

        <div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
            <select class="dropdown-item dropdown-select text-center">
                <option>&nbsp;&nbsp;&nbsp;&nbsp;Categorie</option>
            </select>
        </div>

        <div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
            <select class="dropdown-item dropdown-select" disabled>
                <option>&nbsp;&nbsp;&nbsp;&nbsp;Vestiging</option>
            </select>
        </div>
        
        <div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
            <input class="dropdown-item" value="Status" disabled>
        </div>
        
        <div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
            <input class="dropdown-item" value="Status verlopen" disabled>
        </div>

        <!-- Admin additions: -->
         <?php if(isset($userType) && $userType === 'admin'): ?>
        <div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
            <input class="dropdown-item" value="Laatste lener" disabled>
        </div>

        <div class="secundary-color-1 border border-top-0 border-dark">
            <button class="secundary-color-4 buttons text-black rounded-2 m-1 text-center" id="boek-save-button" type="submit">Wijzigingen Opslaan</button>
        </div>

        <div class="secundary-color-1 border border-top-0 border-dark">
            <button class="secundary-color-4 buttons text-black rounded-2 m-1 text-center" id="boek-status-button" type="submit">Status Aanpassen</button>
        </div>

        <div class="secundary-color-1 border border-top-0 border-dark">
            <button class="secundary-color-4 buttons text-black rounded-2 m-1 text-center" id="boek-delete-button" type="submit">Boek Verwijderen</button>
        </div>
        <?php endif; ?>
    </div>
</div>