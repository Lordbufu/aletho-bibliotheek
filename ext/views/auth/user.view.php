<div class="view-container container-fluid" id="view-container">
    <div class="view-stack" role="main">
        <?php
            require viewPath('partials/containers/search-container.php');
            require viewPath('partials/containers/hamburger-container.php');
            require viewPath('partials/containers/sort-container.php');
        ?>

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