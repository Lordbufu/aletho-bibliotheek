<div class="view-container container-fluid">
    <div class="view-stack" role="main">

        <?php
            require viewPath('partials\containers\search-container.php');
            require viewPath('partials\containers\hamburger-container.php');
            require viewPath('partials\containers\sort-container.php');
        ?>

        <div class="items-list">

            <?php
            foreach ($books as $book) {
                require viewPath('partials/containers/item-container.php');
            }
            
            unset($_SESSION['_flashSingle']);
            ?>
            
        </div>

        <form id="shared-delete-form" method="post" action="/delBook" style="display:none;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="book_id" id="delete-book-id">
        </form>

        <?php
            require viewPath('partials/popins/add-book-popin.php');
            require viewPath('partials/popins/status-period-popin.php');
            require viewPath('partials/popins/password-reset-popin.php');
            require viewPath('partials/popins/change-book-status-popin.php');
        ?>

    </div>
</div>