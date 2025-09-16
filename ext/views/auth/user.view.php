<div class="view-container container-fluid" id="view-container">
    <div class="view-stack" role="main">
        <?php
            require viewPath('partials/containers/search-container.php');
            require viewPath('partials/containers/hamburger-container.php');
        ?>

        <div class="sort-container">
            <div class="sort-group">
                <label for="sort-options">Sorteren op:</label>
                <select id="sort-options" class="aletho-select">
                    <option value="title-asc">Titel oplopend</option>
                    <option value="title-desc">Titel aflopend</option>
                    <option value="writer-asc">Schrijver oplopend</option>
                    <option value="writer-desc">Schrijver aflopend</option>
                    <option value="genre-asc">Genre oplopend</option>
                    <option value="genre-desc">Genre aflopend</option>
                </select>
            </div>
        </div>

        <div class="items-list">
            <?php foreach ($books as $book) {
                // dd($book);
                require viewPath('partials/containers/item-container.php');
            } ?>
        </div>

        <?php
            require viewPath('partials/popins/add-book-popin.php');
            require viewPath('partials/popins/status-period-popin.php');
            require viewPath('partials/popins/password-reset-popin.php');
            require viewPath('partials/popins/change-book-status-popin.php');
        ?>
    </div>
</div>